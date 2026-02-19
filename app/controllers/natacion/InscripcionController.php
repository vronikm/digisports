<?php
/**
 * DigiSports Natación — Controlador de Inscripciones
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class InscripcionController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'NATACION'; }

    public function index() {
        try {
            $this->setupModule();
            $estado = $this->get('estado') ?? '';
            $grupo  = $this->get('grupo') ?? '';
            $pagina = max(1, (int)($this->get('pagina') ?? 1));
            $porPagina = 25;

            $where = " WHERE i.nis_tenant_id = ?";
            $params = [$this->tenantId];
            if (!empty($estado)) { $where .= " AND i.nis_estado = ?"; $params[] = $estado; }
            if (!empty($grupo))  { $where .= " AND i.nis_grupo_id = ?"; $params[] = (int)$grupo; }

            $countSQL = "SELECT COUNT(*) FROM natacion_inscripciones i {$where}";
            $stm = $this->db->prepare($countSQL);
            $stm->execute($params);
            $total = (int)$stm->fetchColumn();
            $totalPaginas = max(1, ceil($total / $porPagina));
            $offset = ($pagina - 1) * $porPagina;

            $sql = "SELECT i.*, CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS alumno,
                           g.ngr_nombre AS grupo, g.ngr_color,
                           CONCAT(ins.nin_nombres, ' ', ins.nin_apellidos) AS instructor
                    FROM natacion_inscripciones i
                    JOIN alumnos a ON i.nis_alumno_id = a.alu_alumno_id
                    JOIN natacion_grupos g ON i.nis_grupo_id = g.ngr_grupo_id
                    LEFT JOIN natacion_instructores ins ON g.ngr_instructor_id = ins.nin_instructor_id
                    {$where}
                    ORDER BY i.nis_fecha_inscripcion DESC
                    LIMIT {$porPagina} OFFSET {$offset}";
            $stm = $this->db->prepare($sql);
            $stm->execute($params);

            // Grupos para filtro
            $stmG = $this->db->prepare("SELECT ngr_grupo_id, ngr_nombre FROM natacion_grupos WHERE ngr_tenant_id = ? AND ngr_estado IN ('ABIERTO','EN_CURSO') ORDER BY ngr_nombre");
            $stmG->execute([$this->tenantId]);

            // Alumnos para select
            $stmA = $this->db->prepare("
                SELECT a.alu_alumno_id, CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS nombre
                FROM alumnos a
                JOIN natacion_ficha_alumno nf ON nf.nfa_alumno_id = a.alu_alumno_id AND nf.nfa_tenant_id = a.alu_tenant_id
                WHERE a.alu_tenant_id = ? AND a.alu_estado = 'ACTIVO' AND nf.nfa_activo = 1
                ORDER BY a.alu_apellidos
            ");
            $stmA->execute([$this->tenantId]);

            $this->viewData['inscripciones'] = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['grupos']        = $stmG->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['alumnos']       = $stmA->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['estadoFiltro']  = $estado;
            $this->viewData['grupoFiltro']   = $grupo;
            $this->viewData['pagina']        = $pagina;
            $this->viewData['totalPaginas']  = $totalPaginas;
            $this->viewData['total']         = $total;
            $this->viewData['csrf_token']    = \Security::generateCsrfToken();
            $this->viewData['title']         = 'Inscripciones';
            $this->renderModule('natacion/inscripciones/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando inscripciones: " . $e->getMessage());
            $this->error('Error al cargar inscripciones');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $alumnoId = (int)($this->post('alumno_id') ?? 0);
            $grupoId  = (int)($this->post('grupo_id') ?? 0);
            if (!$alumnoId || !$grupoId) return $this->jsonResponse(['success' => false, 'message' => 'Alumno y grupo son obligatorios']);

            // Verificar cupo
            $stm = $this->db->prepare("SELECT ngr_cupo_maximo, ngr_cupo_actual FROM natacion_grupos WHERE ngr_grupo_id = ? AND ngr_tenant_id = ?");
            $stm->execute([$grupoId, $this->tenantId]);
            $grupo = $stm->fetch(\PDO::FETCH_ASSOC);
            if (!$grupo) return $this->jsonResponse(['success' => false, 'message' => 'Grupo no encontrado']);
            if ($grupo['ngr_cupo_actual'] >= $grupo['ngr_cupo_maximo']) {
                return $this->jsonResponse(['success' => false, 'message' => 'El grupo está lleno. Cupo máximo alcanzado.']);
            }

            // Verificar duplicado
            $stm = $this->db->prepare("SELECT nis_inscripcion_id FROM natacion_inscripciones WHERE nis_alumno_id = ? AND nis_grupo_id = ? AND nis_tenant_id = ?");
            $stm->execute([$alumnoId, $grupoId, $this->tenantId]);
            if ($stm->fetchColumn()) return $this->jsonResponse(['success' => false, 'message' => 'El alumno ya está inscrito en este grupo']);

            $monto = (float)($this->post('monto') ?? 0);
            $descuento = (float)($this->post('descuento') ?? 0);

            $this->db->beginTransaction();

            $stm = $this->db->prepare("INSERT INTO natacion_inscripciones (nis_tenant_id, nis_alumno_id, nis_grupo_id, nis_periodo_id, nis_fecha_inscripcion, nis_monto, nis_descuento, nis_monto_final, nis_notas) VALUES (?,?,?,?,?,?,?,?,?)");
            $stm->execute([
                $this->tenantId, $alumnoId, $grupoId,
                $this->post('periodo_id') ?: null,
                date('Y-m-d'),
                $monto, $descuento, $monto - $descuento,
                $this->post('notas') ?: null,
            ]);

            // Incrementar cupo actual
            $this->db->prepare("UPDATE natacion_grupos SET ngr_cupo_actual = ngr_cupo_actual + 1 WHERE ngr_grupo_id = ? AND ngr_tenant_id = ?")->execute([$grupoId, $this->tenantId]);

            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => 'Inscripción registrada']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error creando inscripción: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar inscripción']);
        }
    }

    public function cancelar() {
        try {
            $id = (int)($this->post('id') ?? $this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->beginTransaction();

            // Obtener grupo para decrementar cupo
            $stm = $this->db->prepare("SELECT nis_grupo_id FROM natacion_inscripciones WHERE nis_inscripcion_id = ? AND nis_tenant_id = ? AND nis_estado = 'ACTIVA'");
            $stm->execute([$id, $this->tenantId]);
            $grupoId = $stm->fetchColumn();

            $this->db->prepare("UPDATE natacion_inscripciones SET nis_estado = 'CANCELADA', nis_fecha_baja = CURDATE() WHERE nis_inscripcion_id = ? AND nis_tenant_id = ?")->execute([$id, $this->tenantId]);

            if ($grupoId) {
                $this->db->prepare("UPDATE natacion_grupos SET ngr_cupo_actual = GREATEST(0, ngr_cupo_actual - 1) WHERE ngr_grupo_id = ? AND ngr_tenant_id = ?")->execute([$grupoId, $this->tenantId]);
            }

            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => 'Inscripción cancelada']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error cancelando inscripción: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al cancelar']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
