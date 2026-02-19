<?php
/**
 * DigiSports Natación — Controlador de Evaluaciones
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class EvaluacionController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'NATACION'; }

    public function index() {
        try {
            $this->setupModule();
            $nivelId  = (int)($this->get('nivel_id') ?? 0);
            $alumnoId = (int)($this->get('alumno_id') ?? 0);

            // Niveles
            $stmN = $this->db->prepare("SELECT nnv_nivel_id, nnv_nombre, nnv_color, nnv_codigo FROM natacion_niveles WHERE nnv_tenant_id = ? AND nnv_activo = 1 ORDER BY nnv_orden");
            $stmN->execute([$this->tenantId]);
            $niveles = $stmN->fetchAll(\PDO::FETCH_ASSOC);

            // Si se seleccionó un nivel, obtener habilidades
            $habilidades = [];
            if ($nivelId) {
                $stm = $this->db->prepare("SELECT * FROM natacion_nivel_habilidades WHERE nnh_nivel_id = ? AND nnh_tenant_id = ? AND nnh_activo = 1 ORDER BY nnh_orden");
                $stm->execute([$nivelId, $this->tenantId]);
                $habilidades = $stm->fetchAll(\PDO::FETCH_ASSOC);
            }

            // Alumnos del nivel (que tengan ficha con ese nivel)
            $alumnos = [];
            if ($nivelId) {
                $stm = $this->db->prepare("
                    SELECT a.alu_alumno_id, CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS nombre
                    FROM alumnos a
                    JOIN natacion_ficha_alumno nf ON nf.nfa_alumno_id = a.alu_alumno_id AND nf.nfa_tenant_id = a.alu_tenant_id
                    WHERE a.alu_tenant_id = ? AND nf.nfa_nivel_actual_id = ? AND nf.nfa_activo = 1
                    ORDER BY a.alu_apellidos
                ");
                $stm->execute([$this->tenantId, $nivelId]);
                $alumnos = $stm->fetchAll(\PDO::FETCH_ASSOC);
            }

            // Evaluaciones recientes
            $stm = $this->db->prepare("
                SELECT e.*, h.nnh_nombre AS habilidad, n.nnv_nombre AS nivel_nombre, n.nnv_color,
                       CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS alumno
                FROM natacion_evaluaciones e
                JOIN natacion_nivel_habilidades h ON e.nev_habilidad_id = h.nnh_habilidad_id
                JOIN natacion_niveles n ON e.nev_nivel_id = n.nnv_nivel_id
                JOIN alumnos a ON e.nev_alumno_id = a.alu_alumno_id
                WHERE e.nev_tenant_id = ?
                ORDER BY e.nev_fecha DESC LIMIT 50
            ");
            $stm->execute([$this->tenantId]);

            $this->viewData['niveles']       = $niveles;
            $this->viewData['habilidades']   = $habilidades;
            $this->viewData['alumnos']       = $alumnos;
            $this->viewData['evaluaciones']  = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['nivelFiltro']   = $nivelId;
            $this->viewData['csrf_token']    = \Security::generateCsrfToken();
            $this->viewData['title']         = 'Evaluaciones';
            $this->renderModule('natacion/evaluaciones/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error en evaluaciones: " . $e->getMessage());
            $this->error('Error al cargar evaluaciones');
        }
    }

    /**
     * Guardar evaluación masiva (múltiples habilidades de un alumno)
     */
    public function guardar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $alumnoId = (int)($this->post('alumno_id') ?? 0);
            $nivelId  = (int)($this->post('nivel_id') ?? 0);
            $fecha    = $this->post('fecha') ?? date('Y-m-d');
            $calificaciones = $this->post('calificacion') ?? [];

            if (!$alumnoId || !$nivelId || empty($calificaciones)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
            }

            $this->db->beginTransaction();

            $stm = $this->db->prepare("
                INSERT INTO natacion_evaluaciones (nev_tenant_id, nev_alumno_id, nev_habilidad_id, nev_nivel_id, nev_calificacion, nev_aprobado, nev_fecha, nev_evaluador_id, nev_observacion)
                VALUES (?,?,?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE nev_calificacion = VALUES(nev_calificacion), nev_aprobado = VALUES(nev_aprobado), nev_observacion = VALUES(nev_observacion)
            ");

            $escala = 5; // Escala por defecto
            $count = 0;

            foreach ($calificaciones as $habilidadId => $calificacion) {
                $cal = (int)$calificacion;
                $aprobado = ($cal >= ceil($escala * 0.6)) ? 1 : 0; // 60% para aprobar
                $obs = $this->post("obs_{$habilidadId}") ?: null;

                $stm->execute([
                    $this->tenantId, $alumnoId, (int)$habilidadId, $nivelId,
                    $cal, $aprobado, $fecha,
                    $this->post('evaluador_id') ?: null,
                    $obs,
                ]);
                $count++;
            }

            // Verificar si todas las habilidades del nivel están aprobadas → promover
            $stmCheck = $this->db->prepare("
                SELECT COUNT(*) AS total_habilidades,
                       SUM(CASE WHEN e.nev_aprobado = 1 THEN 1 ELSE 0 END) AS aprobadas
                FROM natacion_nivel_habilidades h
                LEFT JOIN natacion_evaluaciones e ON e.nev_habilidad_id = h.nnh_habilidad_id AND e.nev_alumno_id = ? AND e.nev_tenant_id = h.nnh_tenant_id
                WHERE h.nnh_nivel_id = ? AND h.nnh_tenant_id = ? AND h.nnh_activo = 1
            ");
            $stmCheck->execute([$alumnoId, $nivelId, $this->tenantId]);
            $check = $stmCheck->fetch(\PDO::FETCH_ASSOC);

            $promocion = '';
            if ($check && $check['total_habilidades'] > 0 && $check['aprobadas'] >= $check['total_habilidades']) {
                // Buscar siguiente nivel
                $stmNext = $this->db->prepare("SELECT nnv_nivel_id, nnv_nombre FROM natacion_niveles WHERE nnv_tenant_id = ? AND nnv_orden > (SELECT nnv_orden FROM natacion_niveles WHERE nnv_nivel_id = ?) AND nnv_activo = 1 ORDER BY nnv_orden LIMIT 1");
                $stmNext->execute([$this->tenantId, $nivelId]);
                $next = $stmNext->fetch(\PDO::FETCH_ASSOC);
                if ($next) {
                    $this->db->prepare("UPDATE natacion_ficha_alumno SET nfa_nivel_actual_id = ?, nfa_fecha_ultimo_avance = CURDATE() WHERE nfa_alumno_id = ? AND nfa_tenant_id = ?")->execute([$next['nnv_nivel_id'], $alumnoId, $this->tenantId]);
                    $promocion = " 🎉 ¡Alumno promovido a {$next['nnv_nombre']}!";
                }
            }

            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => "Evaluación guardada ({$count} habilidades).{$promocion}"]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error guardando evaluación: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al guardar evaluación']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
