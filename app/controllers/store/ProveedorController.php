<?php
/**
 * DigiSports Store — Controlador de Proveedores
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class ProveedorController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono  = 'fas fa-store';
    protected $moduloColor  = '#F59E0B';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }

    public function index() {
        try {
            $buscar = trim($this->get('buscar') ?? '');

            $sql = "SELECT p.*, (SELECT COUNT(*) FROM store_ordenes_compra o WHERE o.orc_proveedor_id = p.prv_proveedor_id AND o.orc_tenant_id = p.prv_tenant_id) AS total_ordenes
                    FROM store_proveedores p WHERE p.prv_tenant_id = ?";
            $params = [$this->tenantId];

            if (!empty($buscar)) {
                $sql .= " AND (p.prv_razon_social LIKE ? OR p.prv_nombre_comercial LIKE ? OR p.prv_ruc_ci LIKE ? OR p.prv_contacto_nombre LIKE ?)";
                $like = "%{$buscar}%";
                $params = array_merge($params, [$like, $like, $like, $like]);
            }
            $sql .= " ORDER BY p.prv_razon_social";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $proveedores = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['proveedores'] = $proveedores;
            $this->viewData['buscar']      = $buscar;
            $this->viewData['csrf_token']  = \Security::generateCsrfToken();
            $this->viewData['title']       = 'Proveedores';

            $this->renderModule('store/proveedores/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando proveedores: " . $e->getMessage());
            $this->error('Error al cargar proveedores');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $razonSocial = trim($this->post('razon_social') ?? '');
            if (empty($razonSocial)) return $this->jsonResponse(['success' => false, 'message' => 'La razón social es obligatoria']);

            $stmt = $this->db->prepare("INSERT INTO store_proveedores (
                prv_tenant_id, prv_ruc_ci, prv_razon_social, prv_nombre_comercial, prv_contacto_nombre,
                prv_email, prv_telefono, prv_celular, prv_direccion, prv_ciudad, prv_notas, prv_dias_credito
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $this->tenantId,
                trim($this->post('ruc_ci') ?? '') ?: null,
                $razonSocial,
                trim($this->post('nombre_comercial') ?? '') ?: null,
                trim($this->post('contacto_nombre') ?? '') ?: null,
                trim($this->post('email') ?? '') ?: null,
                trim($this->post('telefono') ?? '') ?: null,
                trim($this->post('celular') ?? '') ?: null,
                trim($this->post('direccion') ?? '') ?: null,
                trim($this->post('ciudad') ?? '') ?: null,
                trim($this->post('notas') ?? '') ?: null,
                (int)($this->post('dias_credito') ?? 0)
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Proveedor registrado']);

        } catch (\Exception $e) {
            $this->logError("Error creando proveedor: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar proveedor']);
        }
    }

    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            $razonSocial = trim($this->post('razon_social') ?? '');
            if (!$id || empty($razonSocial)) return $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos']);

            $stmt = $this->db->prepare("UPDATE store_proveedores SET
                prv_ruc_ci = ?, prv_razon_social = ?, prv_nombre_comercial = ?, prv_contacto_nombre = ?,
                prv_email = ?, prv_telefono = ?, prv_celular = ?, prv_direccion = ?, prv_ciudad = ?,
                prv_notas = ?, prv_dias_credito = ?, prv_activo = ?
                WHERE prv_proveedor_id = ? AND prv_tenant_id = ?");

            $stmt->execute([
                trim($this->post('ruc_ci') ?? '') ?: null, $razonSocial,
                trim($this->post('nombre_comercial') ?? '') ?: null, trim($this->post('contacto_nombre') ?? '') ?: null,
                trim($this->post('email') ?? '') ?: null, trim($this->post('telefono') ?? '') ?: null,
                trim($this->post('celular') ?? '') ?: null, trim($this->post('direccion') ?? '') ?: null,
                trim($this->post('ciudad') ?? '') ?: null, trim($this->post('notas') ?? '') ?: null,
                (int)($this->post('dias_credito') ?? 0), (int)($this->post('activo') ?? 1),
                $id, $this->tenantId
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Proveedor actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando proveedor: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    public function eliminar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM store_ordenes_compra WHERE orc_proveedor_id = ? AND orc_tenant_id = ?");
            $stmt->execute([$id, $this->tenantId]);
            if ((int)$stmt->fetchColumn() > 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'No se puede eliminar: tiene órdenes de compra. Puede desactivarlo.']);
            }

            $this->db->prepare("DELETE FROM store_proveedores WHERE prv_proveedor_id = ? AND prv_tenant_id = ?")->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Proveedor eliminado']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando proveedor: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data); exit; }
}
