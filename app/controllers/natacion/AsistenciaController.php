<?php
/**
 * DigiSports Natación — Controlador de Asistencia
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class AsistenciaController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'NATACION'; }

    public function index() {
        try {
            $this->setupModule();
            $grupoId = (int)($this->get('grupo_id') ?? 0);
            $fecha   = $this->get('fecha') ?? date('Y-m-d');

            // Grupos para selector
            $stmG = $this->db->prepare("SELECT ngr_grupo_id, ngr_nombre, ngr_color FROM natacion_grupos WHERE ngr_tenant_id = ? AND ngr_estado IN ('ABIERTO','EN_CURSO') ORDER BY ngr_nombre");
            $stmG->execute([$this->tenantId]);
            $grupos = $stmG->fetchAll(\PDO::FETCH_ASSOC);

            $alumnos = [];
            if ($grupoId) {
                // Alumnos inscritos en el grupo con su asistencia del día
                $stm = $this->db->prepare("
                    SELECT i.nis_inscripcion_id, a.alu_alumno_id,
                           CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS nombre,
                           COALESCE(att.nas_estado, '') AS estado_asistencia,
                           att.nas_observacion
                    FROM natacion_inscripciones i
                    JOIN alumnos a ON i.nis_alumno_id = a.alu_alumno_id
                    LEFT JOIN natacion_asistencia att ON att.nas_inscripcion_id = i.nis_inscripcion_id AND att.nas_fecha = ?
                    WHERE i.nis_grupo_id = ? AND i.nis_tenant_id = ? AND i.nis_estado = 'ACTIVA'
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
            $this->renderModule('natacion/asistencia/index', $this->viewData);

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

            // Usar REPLACE para simplificar upsert
            $stm = $this->db->prepare("
                INSERT INTO natacion_asistencia (nas_tenant_id, nas_inscripcion_id, nas_grupo_id, nas_alumno_id, nas_fecha, nas_estado, nas_observacion, nas_registrado_por)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE nas_estado = VALUES(nas_estado), nas_observacion = VALUES(nas_observacion)
            ");

            $count = 0;
            foreach ($asistencias as $inscripcionId => $estado) {
                if (!in_array($estado, ['PRESENTE','AUSENTE','TARDANZA','JUSTIFICADO'])) continue;

                // Obtener alumno_id de la inscripción
                $stmA = $this->db->prepare("SELECT nis_alumno_id FROM natacion_inscripciones WHERE nis_inscripcion_id = ? AND nis_tenant_id = ?");
                $stmA->execute([(int)$inscripcionId, $this->tenantId]);
                $alumnoId = (int)$stmA->fetchColumn();
                if (!$alumnoId) continue;

                $observacion = $this->post("obs_{$inscripcionId}") ?: null;

                $stm->execute([
                    $this->tenantId,
                    (int)$inscripcionId,
                    $grupoId,
                    $alumnoId,
                    $fecha,
                    $estado,
                    $observacion,
                    $this->userId,
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
            $alumnoId = (int)($this->get('alumno_id') ?? 0);
            $grupoId  = (int)($this->get('grupo_id') ?? 0);
            $desde    = $this->get('desde') ?? date('Y-m-01');
            $hasta    = $this->get('hasta') ?? date('Y-m-d');

            $where = " WHERE nas_tenant_id = ? AND nas_fecha BETWEEN ? AND ?";
            $params = [$this->tenantId, $desde, $hasta];

            if ($alumnoId) { $where .= " AND nas_alumno_id = ?"; $params[] = $alumnoId; }
            if ($grupoId)  { $where .= " AND nas_grupo_id = ?"; $params[] = $grupoId; }

            $stm = $this->db->prepare("
                SELECT nas_estado, COUNT(*) AS total FROM natacion_asistencia {$where} GROUP BY nas_estado
            ");
            $stm->execute($params);

            $this->viewData['resumen'] = $stm->fetchAll(\PDO::FETCH_KEY_PAIR);
            $this->viewData['desde']   = $desde;
            $this->viewData['hasta']   = $hasta;
            $this->viewData['title']   = 'Reporte de Asistencia';
            $this->renderModule('natacion/asistencia/reporte', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error en reporte asistencia: " . $e->getMessage());
            $this->error('Error al generar reporte');
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
