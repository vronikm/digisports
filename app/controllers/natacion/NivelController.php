<?php
/**
 * DigiSports Natación — Controlador de Niveles + Habilidades
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class NivelController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'NATACION'; }

    public function index() {
        try {
            $this->setupModule();

            $stm = $this->db->prepare("
                SELECT n.*,
                       (SELECT COUNT(*) FROM natacion_nivel_habilidades h WHERE h.nnh_nivel_id = n.nnv_nivel_id AND h.nnh_activo = 1) AS total_habilidades,
                       (SELECT COUNT(*) FROM natacion_ficha_alumno f WHERE f.nfa_nivel_actual_id = n.nnv_nivel_id AND f.nfa_activo = 1) AS total_alumnos
                FROM natacion_niveles n WHERE n.nnv_tenant_id = ? ORDER BY n.nnv_orden
            ");
            $stm->execute([$this->tenantId]);

            $this->viewData['niveles']    = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title']      = 'Niveles de Natación';
            $this->renderModule('natacion/niveles/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando niveles: " . $e->getMessage());
            $this->error('Error al cargar niveles');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombre = trim($this->post('nombre') ?? '');
            $codigo = trim($this->post('codigo') ?? '');
            if (empty($nombre) || empty($codigo)) return $this->jsonResponse(['success' => false, 'message' => 'Nombre y código son obligatorios']);

            $stm = $this->db->prepare("INSERT INTO natacion_niveles (nnv_tenant_id, nnv_nombre, nnv_codigo, nnv_descripcion, nnv_color, nnv_orden, nnv_edad_min, nnv_edad_max) VALUES (?,?,?,?,?,?,?,?)");
            $stm->execute([
                $this->tenantId, $nombre, strtoupper($codigo),
                $this->post('descripcion') ?: null,
                $this->post('color') ?: '#3B82F6',
                (int)($this->post('orden') ?? 0),
                $this->post('edad_min') ?: null,
                $this->post('edad_max') ?: null,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Nivel creado']);

        } catch (\Exception $e) {
            $this->logError("Error creando nivel: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear nivel']);
        }
    }

    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $stm = $this->db->prepare("UPDATE natacion_niveles SET nnv_nombre=?, nnv_codigo=?, nnv_descripcion=?, nnv_color=?, nnv_orden=?, nnv_edad_min=?, nnv_edad_max=?, nnv_activo=? WHERE nnv_nivel_id=? AND nnv_tenant_id=?");
            $stm->execute([
                trim($this->post('nombre') ?? ''), strtoupper(trim($this->post('codigo') ?? '')),
                $this->post('descripcion') ?: null, $this->post('color') ?: '#3B82F6',
                (int)($this->post('orden') ?? 0),
                $this->post('edad_min') ?: null, $this->post('edad_max') ?: null,
                (int)($this->post('activo') ?? 1),
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Nivel actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando nivel: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    // ── Habilidades por Nivel ──

    public function habilidades() {
        try {
            $nivelId = (int)($this->get('nivel_id') ?? 0);
            if (!$nivelId) return $this->jsonResponse(['success' => false, 'message' => 'Nivel requerido']);

            $stm = $this->db->prepare("SELECT * FROM natacion_nivel_habilidades WHERE nnh_nivel_id = ? AND nnh_tenant_id = ? ORDER BY nnh_orden");
            $stm->execute([$nivelId, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_ASSOC)]);

        } catch (\Exception $e) {
            $this->logError("Error listando habilidades: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al cargar habilidades']);
        }
    }

    public function crearHabilidad() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nivelId = (int)($this->post('nivel_id') ?? 0);
            $nombre = trim($this->post('nombre') ?? '');
            if (!$nivelId || empty($nombre)) return $this->jsonResponse(['success' => false, 'message' => 'Nivel y nombre son obligatorios']);

            $stm = $this->db->prepare("INSERT INTO natacion_nivel_habilidades (nnh_tenant_id, nnh_nivel_id, nnh_nombre, nnh_descripcion, nnh_orden) VALUES (?,?,?,?,?)");
            $stm->execute([$this->tenantId, $nivelId, $nombre, $this->post('descripcion') ?: null, (int)($this->post('orden') ?? 0)]);

            return $this->jsonResponse(['success' => true, 'message' => 'Habilidad creada']);

        } catch (\Exception $e) {
            $this->logError("Error creando habilidad: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear habilidad']);
        }
    }

    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE natacion_niveles SET nnv_activo = 0 WHERE nnv_nivel_id = ? AND nnv_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Nivel desactivado']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando nivel: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
