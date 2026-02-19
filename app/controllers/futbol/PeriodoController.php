<?php
/**
 * DigiSports Fútbol — Controlador de Períodos
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class PeriodoController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    public function index() {
        try {
            $this->setupModule();

            $stm = $this->db->prepare("
                SELECT p.*,
                       (SELECT COUNT(*) FROM futbol_grupos g WHERE g.fgr_periodo_id = p.fpe_periodo_id) AS total_grupos,
                       (SELECT COUNT(*) FROM futbol_inscripciones i JOIN futbol_grupos g2 ON i.fin_grupo_id = g2.fgr_grupo_id WHERE g2.fgr_periodo_id = p.fpe_periodo_id AND i.fin_estado = 'ACTIVA') AS total_inscripciones
                FROM futbol_periodos p WHERE p.fpe_tenant_id = ? ORDER BY p.fpe_fecha_inicio DESC
            ");
            $stm->execute([$this->tenantId]);

            $this->viewData['periodos']   = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title']      = 'Períodos de Fútbol';
            $this->renderModule('futbol/periodos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando períodos: " . $e->getMessage());
            $this->error('Error al cargar períodos');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombre = trim($this->post('nombre') ?? '');
            $inicio = $this->post('fecha_inicio') ?? '';
            $fin    = $this->post('fecha_fin') ?? '';
            if (empty($nombre) || empty($inicio) || empty($fin)) return $this->jsonResponse(['success' => false, 'message' => 'Nombre y fechas son obligatorios']);

            $stm = $this->db->prepare("INSERT INTO futbol_periodos (fpe_tenant_id, fpe_nombre, fpe_fecha_inicio, fpe_fecha_fin, fpe_estado, fpe_notas) VALUES (?,?,?,?,?,?)");
            $stm->execute([$this->tenantId, $nombre, $inicio, $fin, $this->post('estado') ?: 'PLANIFICADO', $this->post('notas') ?: null]);

            return $this->jsonResponse(['success' => true, 'message' => 'Período creado']);

        } catch (\Exception $e) {
            $this->logError("Error creando período: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear período']);
        }
    }

    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $stm = $this->db->prepare("UPDATE futbol_periodos SET fpe_nombre=?, fpe_fecha_inicio=?, fpe_fecha_fin=?, fpe_estado=?, fpe_notas=? WHERE fpe_periodo_id=? AND fpe_tenant_id=?");
            $stm->execute([
                trim($this->post('nombre') ?? ''),
                $this->post('fecha_inicio'), $this->post('fecha_fin'),
                $this->post('estado') ?: 'PLANIFICADO',
                $this->post('notas') ?: null,
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Período actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando período: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            // Verificar que no tenga grupos activos
            $stm = $this->db->prepare("SELECT COUNT(*) FROM futbol_grupos WHERE fgr_periodo_id = ? AND fgr_estado IN ('ABIERTO','EN_CURSO')");
            $stm->execute([$id]);
            if ((int)$stm->fetchColumn() > 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'No se puede eliminar: tiene grupos activos']);
            }

            $this->db->prepare("UPDATE futbol_periodos SET fpe_estado = 'FINALIZADO' WHERE fpe_periodo_id = ? AND fpe_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Período finalizado']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando período: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
