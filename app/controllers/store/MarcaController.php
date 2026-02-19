<?php
/**
 * DigiSports Store — Controlador de Marcas
 * CRUD completo para marcas deportivas
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class MarcaController extends \App\Controllers\ModuleController {

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

            $sql = "SELECT m.*,
                           (SELECT COUNT(*) FROM store_productos p WHERE p.pro_marca_id = m.mar_marca_id AND p.pro_estado = 'ACTIVO') AS total_productos
                    FROM store_marcas m
                    WHERE m.mar_tenant_id = ?";
            $params = [$this->tenantId];

            if (!empty($buscar)) {
                $sql .= " AND m.mar_nombre LIKE ?";
                $params[] = "%{$buscar}%";
            }

            $sql .= " ORDER BY m.mar_nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $marcas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['marcas']     = $marcas;
            $this->viewData['buscar']     = $buscar;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title']      = 'Marcas';

            $this->renderModule('store/marcas/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando marcas: " . $e->getMessage());
            $this->error('Error al cargar marcas');
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

            // Verificar duplicado
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM store_marcas WHERE mar_nombre = ? AND mar_tenant_id = ?");
            $stmt->execute([$nombre, $this->tenantId]);
            if ((int)$stmt->fetchColumn() > 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'Ya existe una marca con ese nombre']);
            }

            $descripcion = trim($this->post('descripcion') ?? '');

            $stmt = $this->db->prepare("INSERT INTO store_marcas (mar_tenant_id, mar_nombre, mar_slug, mar_descripcion) VALUES (?, ?, ?, ?)");
            $stmt->execute([$this->tenantId, $nombre, $this->slugify($nombre), $descripcion]);

            return $this->jsonResponse(['success' => true, 'message' => 'Marca creada exitosamente']);

        } catch (\Exception $e) {
            $this->logError("Error creando marca: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear la marca']);
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

            $descripcion = trim($this->post('descripcion') ?? '');
            $activo = (int)($this->post('activo') ?? 1);

            $stmt = $this->db->prepare("UPDATE store_marcas SET mar_nombre = ?, mar_slug = ?, mar_descripcion = ?, mar_activo = ? WHERE mar_marca_id = ? AND mar_tenant_id = ?");
            $stmt->execute([$nombre, $this->slugify($nombre), $descripcion, $activo, $id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Marca actualizada']);

        } catch (\Exception $e) {
            $this->logError("Error editando marca: " . $e->getMessage());
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

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM store_productos WHERE pro_marca_id = ? AND pro_tenant_id = ?");
            $stmt->execute([$id, $this->tenantId]);
            if ((int)$stmt->fetchColumn() > 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'No se puede eliminar: tiene productos asociados']);
            }

            $stmt = $this->db->prepare("DELETE FROM store_marcas WHERE mar_marca_id = ? AND mar_tenant_id = ?");
            $stmt->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Marca eliminada']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando marca: " . $e->getMessage());
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
