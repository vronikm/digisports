<?php
/**
 * DigiSports Fútbol — Controlador de Evaluaciones
 * Evaluaciones por alumno y grupo (no por habilidad individual)
 * 
 * Columnas reales:
 *   futbol_evaluaciones: fev_evaluacion_id, fev_tenant_id, fev_alumno_id, fev_grupo_id, fev_periodo_id,
 *     fev_evaluador_id, fev_fecha, fev_calificacion, fev_observacion, fev_created_at, fev_updated_at
 *   NO existen: fev_habilidad_id, fev_categoria_id, fev_aprobado, fev_observacion (singular)
 *   Categoría se obtiene via JOIN: evaluaciones → grupo (fgr_categoria_id) → categorias
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class EvaluacionController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'FUTBOL';
    }

    /**
     * Listar evaluaciones con filtros por grupo/periodo
     */
    public function index() {
        try {
            $this->setupModule();
            $grupoId   = (int)($this->get('grupo_id') ?? 0);
            $periodoId = (int)($this->get('periodo_id') ?? 0);

            // Grupos para filtro
            $stmG = $this->db->prepare("
                SELECT g.fgr_grupo_id, g.fgr_nombre, g.fgr_color,
                       c.fct_nombre AS categoria_nombre
                FROM futbol_grupos g
                LEFT JOIN futbol_categorias c ON g.fgr_categoria_id = c.fct_categoria_id
                WHERE g.fgr_tenant_id = ? AND g.fgr_estado IN ('ABIERTO','EN_CURSO')
                ORDER BY g.fgr_nombre
            ");
            $stmG->execute([$this->tenantId]);
            $this->viewData['grupos'] = $stmG->fetchAll(\PDO::FETCH_ASSOC);

            // Periodos para filtro
            $stmP = $this->db->prepare("SELECT fpe_periodo_id, fpe_nombre FROM futbol_periodos WHERE fpe_tenant_id = ? ORDER BY fpe_fecha_inicio DESC");
            $stmP->execute([$this->tenantId]);
            $this->viewData['periodos'] = $stmP->fetchAll(\PDO::FETCH_ASSOC);

            // Alumnos del grupo seleccionado (via inscripciones activas)
            $alumnos = [];
            if ($grupoId) {
                $stmA = $this->db->prepare("
                    SELECT a.alu_alumno_id, CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS nombre
                    FROM alumnos a
                    JOIN futbol_inscripciones fi ON fi.fin_alumno_id = a.alu_alumno_id AND fi.fin_tenant_id = a.alu_tenant_id
                    WHERE a.alu_tenant_id = ? AND fi.fin_grupo_id = ? AND fi.fin_estado = 'ACTIVA'
                    ORDER BY a.alu_apellidos, a.alu_nombres
                ");
                $stmA->execute([$this->tenantId, $grupoId]);
                $alumnos = $stmA->fetchAll(\PDO::FETCH_ASSOC);
            }
            $this->viewData['alumnos'] = $alumnos;

            // Entrenadores para evaluador
            $stmE = $this->db->prepare("SELECT fen_entrenador_id, CONCAT(fen_nombres, ' ', fen_apellidos) AS nombre FROM futbol_entrenadores WHERE fen_tenant_id = ? AND fen_activo = 1 ORDER BY fen_apellidos");
            $stmE->execute([$this->tenantId]);
            $this->viewData['entrenadores'] = $stmE->fetchAll(\PDO::FETCH_ASSOC);

            // Evaluaciones recientes — join grupo → categoria
            $where = 'e.fev_tenant_id = ?';
            $params = [$this->tenantId];
            if ($grupoId)   { $where .= ' AND e.fev_grupo_id = ?';   $params[] = $grupoId; }
            if ($periodoId) { $where .= ' AND e.fev_periodo_id = ?'; $params[] = $periodoId; }

            $stm = $this->db->prepare("
                SELECT e.*,
                       CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS alumno,
                       g.fgr_nombre AS grupo_nombre,
                       c.fct_nombre AS categoria_nombre, c.fct_color,
                       p.fpe_nombre AS periodo_nombre,
                       CONCAT(ent.fen_nombres, ' ', ent.fen_apellidos) AS evaluador_nombre
                FROM futbol_evaluaciones e
                JOIN alumnos a ON e.fev_alumno_id = a.alu_alumno_id
                LEFT JOIN futbol_grupos g ON e.fev_grupo_id = g.fgr_grupo_id
                LEFT JOIN futbol_categorias c ON g.fgr_categoria_id = c.fct_categoria_id
                LEFT JOIN futbol_periodos p ON e.fev_periodo_id = p.fpe_periodo_id
                LEFT JOIN futbol_entrenadores ent ON e.fev_evaluador_id = ent.fen_entrenador_id
                WHERE {$where}
                ORDER BY e.fev_fecha DESC
                LIMIT 50
            ");
            $stm->execute($params);
            $this->viewData['evaluaciones'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['grupoFiltro']   = $grupoId;
            $this->viewData['periodoFiltro'] = $periodoId;
            $this->viewData['csrf_token']    = \Security::generateCsrfToken();
            $this->viewData['title']         = 'Evaluaciones';
            $this->renderModule('futbol/evaluaciones/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error en evaluaciones: " . $e->getMessage());
            $this->error('Error al cargar evaluaciones');
        }
    }

    /**
     * Guardar evaluación de un alumno en un grupo
     */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $alumnoId      = (int)($this->post('alumno_id') ?? 0);
            $grupoId       = (int)($this->post('grupo_id') ?? 0);
            $periodoId     = (int)($this->post('periodo_id') ?? 0) ?: null;
            $evaluadorId   = (int)($this->post('evaluador_id') ?? 0) ?: null;
            $fecha         = $this->post('fecha') ?: date('Y-m-d');
            $calificacion  = (float)($this->post('calificacion') ?? 0);
            $observaciones = trim($this->post('observaciones') ?? '');

            if (!$alumnoId || !$grupoId) {
                return $this->jsonResponse(['success' => false, 'message' => 'Alumno y grupo son obligatorios']);
            }

            $stm = $this->db->prepare("
                INSERT INTO futbol_evaluaciones (fev_tenant_id, fev_alumno_id, fev_grupo_id, fev_periodo_id,
                    fev_evaluador_id, fev_fecha, fev_calificacion, fev_observacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stm->execute([
                $this->tenantId,
                $alumnoId,
                $grupoId,
                $periodoId,
                $evaluadorId,
                $fecha,
                $calificacion,
                $observaciones ?: null,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Evaluación guardada correctamente']);

        } catch (\Exception $e) {
            $this->logError("Error guardando evaluación: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al guardar evaluación']);
        }
    }

    /**
     * Alias para crear (compatibilidad)
     */
    public function guardar() {
        return $this->crear();
    }

    /**
     * Eliminar evaluación
     */
    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("DELETE FROM futbol_evaluaciones WHERE fev_evaluacion_id = ? AND fev_tenant_id = ?")
                ->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Evaluación eliminada']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando evaluación: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar evaluación']);
        }
    }

    /**
     * Ver detalle de una evaluación (JSON)
     */
    public function detalle() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $stm = $this->db->prepare("
                SELECT e.*,
                       CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS alumno,
                       g.fgr_nombre AS grupo_nombre,
                       c.fct_nombre AS categoria_nombre,
                       p.fpe_nombre AS periodo_nombre,
                       CONCAT(ent.fen_nombres, ' ', ent.fen_apellidos) AS evaluador_nombre
                FROM futbol_evaluaciones e
                JOIN alumnos a ON e.fev_alumno_id = a.alu_alumno_id
                LEFT JOIN futbol_grupos g ON e.fev_grupo_id = g.fgr_grupo_id
                LEFT JOIN futbol_categorias c ON g.fgr_categoria_id = c.fct_categoria_id
                LEFT JOIN futbol_periodos p ON e.fev_periodo_id = p.fpe_periodo_id
                LEFT JOIN futbol_entrenadores ent ON e.fev_evaluador_id = ent.fen_entrenador_id
                WHERE e.fev_evaluacion_id = ? AND e.fev_tenant_id = ?
            ");
            $stm->execute([$id, $this->tenantId]);
            $evaluacion = $stm->fetch(\PDO::FETCH_ASSOC);

            if (!$evaluacion) return $this->jsonResponse(['success' => false, 'message' => 'Evaluación no encontrada']);

            return $this->jsonResponse(['success' => true, 'data' => $evaluacion]);

        } catch (\Exception $e) {
            $this->logError("Error obteniendo detalle de evaluación: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al obtener evaluación']);
        }
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Editar evaluación existente (POST AJAX)
     */
    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('fev_id') ?? $this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $stm = $this->db->prepare("
                UPDATE futbol_evaluaciones SET fev_alumno_id=?, fev_grupo_id=?, fev_periodo_id=?,
                    fev_evaluador_id=?, fev_fecha=?, fev_calificacion=?, fev_observacion=?, fev_updated_at=NOW()
                WHERE fev_evaluacion_id=? AND fev_tenant_id=?
            ");
            $stm->execute([
                (int)($this->post('fev_alumno_id') ?? $this->post('alumno_id') ?? 0),
                (int)($this->post('fev_grupo_id') ?? $this->post('grupo_id') ?? 0),
                (int)($this->post('fev_periodo_id') ?? $this->post('periodo_id') ?? 0) ?: null,
                (int)($this->post('evaluador_id') ?? 0) ?: null,
                $this->post('fev_fecha') ?? $this->post('fecha') ?? date('Y-m-d'),
                (float)($this->post('fev_calificacion') ?? $this->post('calificacion') ?? 0),
                trim($this->post('fev_observacion') ?? $this->post('observaciones') ?? ''),
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Evaluación actualizada']);

        } catch (\Exception $e) {
            $this->logError("Error editando evaluación: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar evaluación']);
        }
    }

    /**
     * Buscar alumnos para Select2 AJAX
     */
    public function buscarAlumno() {
        try {
            $q = trim($this->get('q') ?? '');
            if (strlen($q) < 2) return $this->jsonResponse([]);

            $grupoId = (int)($this->get('grupo_id') ?? 0);

            $sql = "
                SELECT a.alu_alumno_id AS id, CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS text
                FROM alumnos a
                JOIN futbol_ficha_alumno ffa ON ffa.ffa_alumno_id = a.alu_alumno_id AND ffa.ffa_tenant_id = a.alu_tenant_id
                WHERE a.alu_tenant_id = ? AND a.alu_estado = 'ACTIVO' AND ffa.ffa_activo = 1
                  AND (a.alu_nombres LIKE ? OR a.alu_apellidos LIKE ? OR a.alu_identificacion LIKE ?)
            ";
            $params = [$this->tenantId, "%{$q}%", "%{$q}%", "%{$q}%"];

            if ($grupoId) {
                $sql .= " AND EXISTS (SELECT 1 FROM futbol_inscripciones fi WHERE fi.fin_alumno_id = a.alu_alumno_id AND fi.fin_grupo_id = ? AND fi.fin_estado = 'ACTIVA')";
                $params[] = $grupoId;
            }

            $sql .= " ORDER BY a.alu_apellidos LIMIT 20";
            $stm = $this->db->prepare($sql);
            $stm->execute($params);

            $this->jsonResponse($stm->fetchAll(\PDO::FETCH_ASSOC));

        } catch (\Exception $e) {
            $this->logError("Error buscando alumno: " . $e->getMessage());
            $this->jsonResponse([]);
        }
    }
}
