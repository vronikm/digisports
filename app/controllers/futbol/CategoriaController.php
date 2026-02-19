<?php
/**
 * DigiSports Fútbol — Controlador de Categorías + Habilidades
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class CategoriaController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    public function index() {
        try {
            $this->setupModule();

            $stm = $this->db->prepare("
                SELECT c.*,
                       (SELECT COUNT(*) FROM futbol_categoria_habilidades h WHERE h.fch_categoria_id = c.fct_categoria_id) AS total_habilidades,
                       (SELECT COUNT(*) FROM futbol_ficha_alumno f WHERE f.ffa_categoria_id = c.fct_categoria_id AND f.ffa_activo = 1) AS total_alumnos
                FROM futbol_categorias c WHERE c.fct_tenant_id = ? ORDER BY c.fct_orden
            ");
            $stm->execute([$this->tenantId]);

            $this->viewData['categorias'] = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title']      = 'Categorías de Fútbol';
            $this->renderModule('futbol/categorias/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando categorías: " . $e->getMessage());
            $this->error('Error al cargar categorías');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombre = trim($this->post('nombre') ?? '');
            $codigo = trim($this->post('codigo') ?? '');
            if (empty($nombre) || empty($codigo)) return $this->jsonResponse(['success' => false, 'message' => 'Nombre y código son obligatorios']);

            $stm = $this->db->prepare("INSERT INTO futbol_categorias (fct_tenant_id, fct_nombre, fct_codigo, fct_descripcion, fct_color, fct_orden, fct_edad_min, fct_edad_max) VALUES (?,?,?,?,?,?,?,?)");
            $stm->execute([
                $this->tenantId, $nombre, strtoupper($codigo),
                $this->post('descripcion') ?: null,
                $this->post('color') ?: '#22C55E',
                (int)($this->post('orden') ?? 0),
                $this->post('edad_min') ?: null,
                $this->post('edad_max') ?: null,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Categoría creada']);

        } catch (\Exception $e) {
            $this->logError("Error creando categoría: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear categoría']);
        }
    }

    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $stm = $this->db->prepare("UPDATE futbol_categorias SET fct_nombre=?, fct_codigo=?, fct_descripcion=?, fct_color=?, fct_orden=?, fct_edad_min=?, fct_edad_max=?, fct_activo=? WHERE fct_categoria_id=? AND fct_tenant_id=?");
            $stm->execute([
                trim($this->post('nombre') ?? ''), strtoupper(trim($this->post('codigo') ?? '')),
                $this->post('descripcion') ?: null, $this->post('color') ?: '#22C55E',
                (int)($this->post('orden') ?? 0),
                $this->post('edad_min') ?: null, $this->post('edad_max') ?: null,
                (int)($this->post('activo') ?? 1),
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Categoría actualizada']);

        } catch (\Exception $e) {
            $this->logError("Error editando categoría: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    // ── Habilidades por Categoría ──

    public function habilidades() {
        try {
            $categoriaId = (int)($this->get('id') ?? $this->get('categoria_id') ?? 0);
            if (!$categoriaId) return $this->jsonResponse(['success' => false, 'message' => 'Categoría requerida']);

            $stm = $this->db->prepare("SELECT * FROM futbol_categoria_habilidades WHERE fch_categoria_id = ? AND fch_tenant_id = ? ORDER BY fch_orden");
            $stm->execute([$categoriaId, $this->tenantId]);

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

            $categoriaId = (int)($this->post('categoria_id') ?? 0);
            $nombre = trim($this->post('nombre') ?? '');
            if (!$categoriaId || empty($nombre)) return $this->jsonResponse(['success' => false, 'message' => 'Categoría y nombre son obligatorios']);

            $stm = $this->db->prepare("INSERT INTO futbol_categoria_habilidades (fch_tenant_id, fch_categoria_id, fch_nombre, fch_descripcion, fch_orden) VALUES (?,?,?,?,?)");
            $stm->execute([$this->tenantId, $categoriaId, $nombre, $this->post('descripcion') ?: null, (int)($this->post('orden') ?? 0)]);

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

            $this->db->prepare("UPDATE futbol_categorias SET fct_activo = 0 WHERE fct_categoria_id = ? AND fct_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Categoría desactivada']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando categoría: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
