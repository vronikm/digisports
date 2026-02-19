<?php
/**
 * DigiSports Store — Controlador de Categorías
 * CRUD completo para categorías de productos con jerarquía
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class CategoriaController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono  = 'fas fa-store';
    protected $moduloColor  = '#F59E0B';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }

    /* ═══════════════════════════════════════
     * LISTADO
     * ═══════════════════════════════════════ */
    public function index() {
        try {
            $buscar = $this->get('buscar') ?? $this->post('buscar') ?? '';

            $sql = "SELECT c.*, cp.cat_nombre AS padre_nombre,
                           (SELECT COUNT(*) FROM store_productos p WHERE p.pro_categoria_id = c.cat_categoria_id AND p.pro_estado = 'ACTIVO') AS total_productos
                    FROM store_categorias c
                    LEFT JOIN store_categorias cp ON c.cat_padre_id = cp.cat_categoria_id
                    WHERE c.cat_tenant_id = ?";
            $params = [$this->tenantId];

            if (!empty($buscar)) {
                $sql .= " AND c.cat_nombre LIKE ?";
                $params[] = "%{$buscar}%";
            }

            $sql .= " ORDER BY c.cat_orden, c.cat_nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $categorias = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Categorías padre para el select
            $stmtPadres = $this->db->prepare("SELECT cat_categoria_id, cat_nombre FROM store_categorias WHERE cat_tenant_id = ? AND cat_padre_id IS NULL AND cat_activo = 1 ORDER BY cat_nombre");
            $stmtPadres->execute([$this->tenantId]);
            $padres = $stmtPadres->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['categorias'] = $categorias;
            $this->viewData['padres']     = $padres;
            $this->viewData['buscar']     = $buscar;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title']      = 'Categorías';

            $this->renderModule('store/categorias/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando categorías: " . $e->getMessage());
            $this->error('Error al cargar categorías');
        }
    }

    /* ═══════════════════════════════════════
     * CREAR (POST AJAX)
     * ═══════════════════════════════════════ */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $nombre = trim($this->post('nombre') ?? '');
            if (empty($nombre)) {
                return $this->jsonResponse(['success' => false, 'message' => 'El nombre es obligatorio']);
            }

            $padreId = $this->post('padre_id') ?: null;
            $icono   = trim($this->post('icono') ?? 'fas fa-folder');
            $orden   = (int)($this->post('orden') ?? 0);

            $stmt = $this->db->prepare("INSERT INTO store_categorias (cat_tenant_id, cat_padre_id, cat_nombre, cat_slug, cat_icono, cat_orden) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $this->tenantId,
                $padreId,
                $nombre,
                $this->slugify($nombre),
                $icono,
                $orden
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Categoría creada exitosamente']);

        } catch (\Exception $e) {
            $this->logError("Error creando categoría: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear la categoría']);
        }
    }

    /* ═══════════════════════════════════════
     * EDITAR (POST AJAX)
     * ═══════════════════════════════════════ */
    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $id     = (int)($this->post('id') ?? 0);
            $nombre = trim($this->post('nombre') ?? '');
            if (!$id || empty($nombre)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
            }

            $padreId = $this->post('padre_id') ?: null;
            $icono   = trim($this->post('icono') ?? 'fas fa-folder');
            $orden   = (int)($this->post('orden') ?? 0);
            $activo  = (int)($this->post('activo') ?? 1);

            $stmt = $this->db->prepare("UPDATE store_categorias SET cat_nombre = ?, cat_slug = ?, cat_padre_id = ?, cat_icono = ?, cat_orden = ?, cat_activo = ? WHERE cat_categoria_id = ? AND cat_tenant_id = ?");
            $stmt->execute([$nombre, $this->slugify($nombre), $padreId, $icono, $orden, $activo, $id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Categoría actualizada']);

        } catch (\Exception $e) {
            $this->logError("Error editando categoría: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    /* ═══════════════════════════════════════
     * ELIMINAR (POST AJAX)
     * ═══════════════════════════════════════ */
    public function eliminar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $id = (int)($this->post('id') ?? 0);

            // Verificar que no tenga productos
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM store_productos WHERE pro_categoria_id = ? AND pro_tenant_id = ?");
            $stmt->execute([$id, $this->tenantId]);
            if ((int)$stmt->fetchColumn() > 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'No se puede eliminar: tiene productos asociados']);
            }

            // Verificar que no tenga subcategorías
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM store_categorias WHERE cat_padre_id = ? AND cat_tenant_id = ?");
            $stmt->execute([$id, $this->tenantId]);
            if ((int)$stmt->fetchColumn() > 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'No se puede eliminar: tiene subcategorías']);
            }

            $stmt = $this->db->prepare("DELETE FROM store_categorias WHERE cat_categoria_id = ? AND cat_tenant_id = ?");
            $stmt->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Categoría eliminada']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando categoría: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    /* ═══════════════════════════════════════
     * HELPERS
     * ═══════════════════════════════════════ */
    private function slugify($text) {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
