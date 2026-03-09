<?php
/**
 * DigiSports Fútbol — Controlador de Asistencia
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class AsistenciaController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    public function index() {
        try {
            $this->setupModule();
            $grupoId = (int)($this->post('grupo') ?? $this->get('grupo') ?? $this->post('grupo_id') ?? $this->get('grupo_id') ?? 0);
            $fecha   = $this->post('fecha') ?? $this->get('fecha') ?? date('Y-m-d');

            // Grupos para selector
            $stmG = $this->db->prepare("SELECT fgr_grupo_id, fgr_nombre, fgr_color FROM futbol_grupos WHERE fgr_tenant_id = ? AND fgr_estado IN ('ABIERTO','EN_CURSO') ORDER BY fgr_nombre");
            $stmG->execute([$this->tenantId]);
            $grupos = $stmG->fetchAll(\PDO::FETCH_ASSOC);

            $alumnos = [];
            if ($grupoId) {
                // Alumnos inscritos en el grupo con su asistencia del día
                $stm = $this->db->prepare("
                    SELECT i.fin_inscripcion_id, a.alu_alumno_id,
                           CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS nombre,
                           COALESCE(att.fas_estado, '') AS estado_asistencia,
                           att.fas_observacion
                    FROM futbol_inscripciones i
                    JOIN alumnos a ON i.fin_alumno_id = a.alu_alumno_id
                    LEFT JOIN futbol_asistencia att ON att.fas_alumno_id = a.alu_alumno_id AND att.fas_grupo_id = i.fin_grupo_id AND att.fas_fecha = ?
                    WHERE i.fin_grupo_id = ? AND i.fin_tenant_id = ? AND i.fin_estado = 'ACTIVA'
                    ORDER BY a.alu_apellidos, a.alu_nombres
                ");
                $stm->execute([$fecha, $grupoId, $this->tenantId]);
                $alumnos = $stm->fetchAll(\PDO::FETCH_ASSOC);
            }

            $this->viewData['grupos']     = $grupos;
            $this->viewData['alumnos']    = $alumnos;
            $this->viewData['grupoId']    = $grupoId;
            $this->viewData['fecha']      = $fecha;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title']      = 'Registro de Asistencia';
            $this->renderModule('futbol/asistencia/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error en asistencia: " . $e->getMessage());
            $this->error('Error al cargar asistencia');
        }
    }

    /**
     * Guardar asistencia masiva (POST AJAX)
     * Recibe array de inscripcion_id => estado
     */
    public function guardar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $grupoId = (int)($this->post('grupo_id') ?? 0);
            $fecha   = $this->post('fecha') ?? date('Y-m-d');
            $asistencias = $this->post('asistencia') ?? [];

            if (!$grupoId || empty($asistencias)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
            }

            $this->db->beginTransaction();

            $stm = $this->db->prepare("
                INSERT INTO futbol_asistencia (fas_tenant_id, fas_alumno_id, fas_grupo_id, fas_fecha, fas_estado, fas_observacion)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE fas_estado = VALUES(fas_estado), fas_observacion = VALUES(fas_observacion)
            ");

            $count = 0;
            foreach ($asistencias as $inscripcionId => $estado) {
                if (!in_array($estado, ['PRESENTE','AUSENTE','TARDANZA','JUSTIFICADO'])) continue;

                // Obtener alumno_id de la inscripción
                $stmA = $this->db->prepare("SELECT fin_alumno_id FROM futbol_inscripciones WHERE fin_inscripcion_id = ? AND fin_tenant_id = ?");
                $stmA->execute([(int)$inscripcionId, $this->tenantId]);
                $alumnoId = (int)$stmA->fetchColumn();
                if (!$alumnoId) continue;

                $observacion = $this->post("obs_{$inscripcionId}") ?: null;

                $stm->execute([
                    $this->tenantId,
                    $alumnoId,
                    $grupoId,
                    $fecha,
                    $estado,
                    $observacion,
                ]);
                $count++;
            }

            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => "Asistencia guardada ({$count} registros)"]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error guardando asistencia: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al guardar asistencia']);
        }
    }

    /**
     * Reporte de asistencia por alumno
     */
    public function reporte() {
        try {
            $this->setupModule();
            $alumnoId = (int)($this->post('alumno_id') ?? $this->get('alumno_id') ?? 0);
            $grupoId  = (int)($this->post('grupo_id')  ?? $this->get('grupo_id')  ?? 0);
            $desde    = $this->post('desde') ?? $this->get('desde') ?? date('Y-m-01');
            $hasta    = $this->post('hasta') ?? $this->get('hasta') ?? date('Y-m-d');

            $where = " WHERE fas_tenant_id = ? AND fas_fecha BETWEEN ? AND ?";
            $params = [$this->tenantId, $desde, $hasta];

            if ($alumnoId) { $where .= " AND fas_alumno_id = ?"; $params[] = $alumnoId; }
            if ($grupoId)  { $where .= " AND fas_grupo_id = ?"; $params[] = $grupoId; }

            $stm = $this->db->prepare("
                SELECT fas_estado, COUNT(*) AS total FROM futbol_asistencia {$where} GROUP BY fas_estado
            ");
            $stm->execute($params);

            $this->viewData['resumen'] = $stm->fetchAll(\PDO::FETCH_KEY_PAIR);
            $this->viewData['desde']   = $desde;
            $this->viewData['hasta']   = $hasta;
            $this->viewData['title']   = 'Reporte de Asistencia';
            $this->renderModule('futbol/asistencia/reporte', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error en reporte asistencia: " . $e->getMessage());
            $this->error('Error al generar reporte');
        }
    }

    /**
     * Guardar asistencia de un alumno individual — endpoint AJAX one-click
     * POST: csrf_token, inscripcion_id, alumno_id, grupo_id, fecha, estado
     */
    public function marcarUno() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'POST requerido']);
            exit;
        }

        if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
            echo json_encode(['success' => false, 'message' => 'Token inválido']);
            exit;
        }

        $inscripcionId = (int)($this->post('inscripcion_id') ?? 0);
        $alumnoId      = (int)($this->post('alumno_id')      ?? 0);
        $grupoId       = (int)($this->post('grupo_id')       ?? 0);
        $fecha         = $this->post('fecha')  ?? '';
        $estado        = $this->post('estado') ?? '';

        if (!$inscripcionId || !$alumnoId || !$grupoId || !$fecha) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            echo json_encode(['success' => false, 'message' => 'Fecha inválida']);
            exit;
        }

        if (!in_array($estado, ['PRESENTE', 'AUSENTE', 'TARDANZA', 'JUSTIFICADO'])) {
            echo json_encode(['success' => false, 'message' => 'Estado inválido']);
            exit;
        }

        try {
            // Verificar que la inscripción pertenece al tenant
            $chk = $this->db->prepare("
                SELECT fin_alumno_id FROM futbol_inscripciones
                WHERE fin_inscripcion_id = ? AND fin_tenant_id = ? AND fin_grupo_id = ?
                LIMIT 1
            ");
            $chk->execute([$inscripcionId, $this->tenantId, $grupoId]);
            if (!$chk->fetchColumn()) {
                echo json_encode(['success' => false, 'message' => 'Inscripción no válida']);
                exit;
            }

            $this->db->prepare("
                INSERT INTO futbol_asistencia
                    (fas_tenant_id, fas_inscripcion_id, fas_alumno_id, fas_grupo_id, fas_fecha, fas_estado, fas_registrado_por)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    fas_estado         = VALUES(fas_estado),
                    fas_registrado_por = VALUES(fas_registrado_por)
            ")->execute([
                $this->tenantId,
                $inscripcionId,
                $alumnoId,
                $grupoId,
                $fecha,
                $estado,
                $_SESSION['user_id'] ?? null,
            ]);

            echo json_encode(['success' => true, 'estado' => $estado]);

        } catch (\Exception $e) {
            $this->logError('marcarUno: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al guardar']);
        }
        exit;
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
