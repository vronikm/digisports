<?php
/**
 * DigiSports Store — Controlador de Órdenes de Compra a Proveedores
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class OrdenCompraController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono  = 'fas fa-store';
    protected $moduloColor  = '#F59E0B';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }

    public function index() {
        try {
            $estado     = $this->get('estado') ?? '';
            $fechaDesde = $this->get('fecha_desde') ?? date('Y-m-01');
            $fechaHasta = $this->get('fecha_hasta') ?? date('Y-m-d');

            $sql = "SELECT o.*, p.prv_razon_social, p.prv_nombre_comercial
                    FROM store_ordenes_compra o
                    JOIN store_proveedores p ON p.prv_proveedor_id = o.orc_proveedor_id
                    WHERE o.orc_tenant_id = ? AND DATE(o.orc_fecha_orden) BETWEEN ? AND ?";
            $params = [$this->tenantId, $fechaDesde, $fechaHasta];

            if (!empty($estado)) {
                $sql .= " AND o.orc_estado = ?";
                $params[] = $estado;
            }

            $sql .= " ORDER BY o.orc_fecha_orden DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $ordenes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Proveedores para formulario
            $stmt = $this->db->prepare("SELECT prv_proveedor_id, prv_razon_social FROM store_proveedores WHERE prv_tenant_id = ? AND prv_activo = 1 ORDER BY prv_razon_social");
            $stmt->execute([$this->tenantId]);
            $proveedores = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['ordenes']      = $ordenes;
            $this->viewData['proveedores']  = $proveedores;
            $this->viewData['estadoFiltro'] = $estado;
            $this->viewData['fechaDesde']   = $fechaDesde;
            $this->viewData['fechaHasta']   = $fechaHasta;
            $this->viewData['csrf_token']   = \Security::generateCsrfToken();
            $this->viewData['title']        = 'Órdenes de Compra';

            $this->renderModule('store/ordenes_compra/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando órdenes: " . $e->getMessage());
            $this->error('Error al cargar órdenes');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                // Proveedores y productos para formulario
                $stmt = $this->db->prepare("SELECT prv_proveedor_id, prv_razon_social FROM store_proveedores WHERE prv_tenant_id = ? AND prv_activo = 1 ORDER BY prv_razon_social");
                $stmt->execute([$this->tenantId]);
                $proveedores = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                $this->viewData['proveedores'] = $proveedores;
                $this->viewData['csrf_token']  = \Security::generateCsrfToken();
                $this->viewData['title']       = 'Nueva Orden de Compra';
                return $this->renderModule('store/ordenes_compra/crear', $this->viewData);
            }

            // POST
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $proveedorId = (int)($this->post('proveedor_id') ?? 0);
            if (!$proveedorId) return $this->jsonResponse(['success' => false, 'message' => 'Seleccione un proveedor']);

            $itemsJson = $this->post('items') ?? '[]';
            $items = json_decode($itemsJson, true);
            if (empty($items)) return $this->jsonResponse(['success' => false, 'message' => 'Agregue al menos un producto']);

            $this->db->beginTransaction();

            // Generar número
            $config = $this->getConfig();
            $prefijo = $config['prefijo_orden_compra'] ?? 'OC-';
            $stmt = $this->db->prepare("SELECT MAX(CAST(SUBSTRING(orc_numero, LENGTH(?) + 1) AS UNSIGNED)) FROM store_ordenes_compra WHERE orc_tenant_id = ?");
            $stmt->execute([$prefijo, $this->tenantId]);
            $ultimo = (int)$stmt->fetchColumn();
            $numero = $prefijo . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            foreach ($items as $it) {
                $subtotal += ((int)($it['cantidad'] ?? 0)) * ((float)($it['costo_unitario'] ?? 0));
            }

            // Impuesto (15% IVA)
            $ivaPct = (float)($config['iva_porcentaje'] ?? 15);
            $impuesto = round($subtotal * ($ivaPct / 100), 2);
            $total = round($subtotal + $impuesto, 2);

            $stmt = $this->db->prepare("INSERT INTO store_ordenes_compra (
                orc_tenant_id, orc_proveedor_id, orc_numero, orc_fecha_orden, orc_fecha_entrega_esperada,
                orc_subtotal, orc_impuesto, orc_total, orc_notas, orc_estado, orc_usuario_id
            ) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, 'BORRADOR', ?)");

            $stmt->execute([
                $this->tenantId, $proveedorId, $numero,
                $this->post('fecha_entrega') ?: null,
                round($subtotal, 2), $impuesto, $total,
                trim($this->post('notas') ?? '') ?: null,
                $this->userId
            ]);
            $ordenId = (int)$this->db->lastInsertId();

            // Insertar detalle
            foreach ($items as $it) {
                $prodId = (int)($it['producto_id'] ?? 0);
                $varId  = (int)($it['variante_id'] ?? 0) ?: null;
                $cant   = (int)($it['cantidad'] ?? 0);
                $costo  = (float)($it['costo_unitario'] ?? 0);
                if (!$prodId || $cant <= 0) continue;

                $this->db->prepare("INSERT INTO store_ordenes_compra_detalle (ocd_tenant_id, ocd_orden_id, ocd_producto_id, ocd_variante_id, ocd_cantidad_pedida, ocd_costo_unitario) VALUES (?, ?, ?, ?, ?, ?)")
                    ->execute([$this->tenantId, $ordenId, $prodId, $varId, $cant, $costo]);
            }

            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => "Orden {$numero} creada", 'orden_id' => $ordenId]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error creando orden: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear orden']);
        }
    }

    /** Recibir productos de una orden */
    public function recibir() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $ordenId = (int)($this->post('orden_id') ?? 0);
            $itemsJson = $this->post('items') ?? '[]';
            $items = json_decode($itemsJson, true);

            $stmt = $this->db->prepare("SELECT * FROM store_ordenes_compra WHERE orc_orden_id = ? AND orc_tenant_id = ? AND orc_estado IN ('BORRADOR','ENVIADA','PARCIAL')");
            $stmt->execute([$ordenId, $this->tenantId]);
            $orden = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$orden) return $this->jsonResponse(['success' => false, 'message' => 'Orden no válida']);

            $this->db->beginTransaction();

            $todosRecibidos = true;
            foreach ($items as $it) {
                $detalleId = (int)($it['detalle_id'] ?? 0);
                $cantRecibida = (int)($it['cantidad_recibida'] ?? 0);
                if (!$detalleId || $cantRecibida <= 0) continue;

                // Actualizar detalle
                $this->db->prepare("UPDATE store_ordenes_compra_detalle SET ocd_cantidad_recibida = ocd_cantidad_recibida + ? WHERE ocd_detalle_id = ? AND ocd_tenant_id = ?")
                    ->execute([$cantRecibida, $detalleId, $this->tenantId]);

                // Obtener producto
                $stmtD = $this->db->prepare("SELECT * FROM store_ordenes_compra_detalle WHERE ocd_detalle_id = ? AND ocd_tenant_id = ?");
                $stmtD->execute([$detalleId, $this->tenantId]);
                $det = $stmtD->fetch(\PDO::FETCH_ASSOC);

                if ($det['ocd_cantidad_recibida'] < $det['ocd_cantidad_pedida']) {
                    $todosRecibidos = false;
                }

                // Agregar stock
                $this->agregarStock($det['ocd_producto_id'], $det['ocd_variante_id'], $cantRecibida, $ordenId, (float)$det['ocd_costo_unitario']);
            }

            // Actualizar estado de la orden
            $nuevoEstado = $todosRecibidos ? 'RECIBIDA' : 'PARCIAL';
            $this->db->prepare("UPDATE store_ordenes_compra SET orc_estado = ?, orc_fecha_recibido = CASE WHEN ? = 'RECIBIDA' THEN CURDATE() ELSE orc_fecha_recibido END WHERE orc_orden_id = ? AND orc_tenant_id = ?")
                ->execute([$nuevoEstado, $nuevoEstado, $ordenId, $this->tenantId]);

            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => 'Productos recibidos y stock actualizado']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error recibiendo orden: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al recibir productos']);
        }
    }

    private function agregarStock($productoId, $varianteId, $cantidad, $ordenId, $costoUnit) {
        $sql = "SELECT stk_stock_id, stk_cantidad FROM store_stock WHERE stk_producto_id = ? AND stk_tenant_id = ?";
        $params = [$productoId, $this->tenantId];
        if ($varianteId) { $sql .= " AND stk_variante_id = ?"; $params[] = $varianteId; }
        else { $sql .= " AND stk_variante_id IS NULL"; }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stock = $stmt->fetch(\PDO::FETCH_ASSOC);
        $anterior = $stock ? (int)$stock['stk_cantidad'] : 0;
        $posterior = $anterior + $cantidad;

        if ($stock) {
            $this->db->prepare("UPDATE store_stock SET stk_cantidad = ? WHERE stk_stock_id = ?")->execute([$posterior, $stock['stk_stock_id']]);
        } else {
            $this->db->prepare("INSERT INTO store_stock (stk_tenant_id, stk_producto_id, stk_variante_id, stk_cantidad) VALUES (?, ?, ?, ?)")->execute([$this->tenantId, $productoId, $varianteId, $posterior]);
        }

        // Movimiento
        $this->db->prepare("INSERT INTO store_stock_movimientos (mov_tenant_id, mov_producto_id, mov_variante_id, mov_tipo, mov_cantidad, mov_stock_anterior, mov_stock_posterior, mov_costo_unitario, mov_referencia_tipo, mov_referencia_id, mov_motivo, mov_usuario_id) VALUES (?, ?, ?, 'COMPRA', ?, ?, ?, ?, 'ORDEN_COMPRA', ?, 'Recepción de orden de compra', ?)")
            ->execute([$this->tenantId, $productoId, $varianteId, $cantidad, $anterior, $posterior, $costoUnit, $ordenId, $this->userId]);

        // Resolver alertas
        $this->db->prepare("UPDATE store_stock_alertas SET ale_estado = 'RESUELTA', ale_fecha_resuelta = NOW() WHERE ale_producto_id = ? AND ale_tenant_id = ? AND ale_estado IN ('PENDIENTE','NOTIFICADA')")
            ->execute([$productoId, $this->tenantId]);
    }

    private function getConfig() {
        $stmt = $this->db->prepare("SELECT cfg_clave, cfg_valor FROM store_configuracion WHERE cfg_tenant_id = ?");
        $stmt->execute([$this->tenantId]);
        $c = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) $c[$r['cfg_clave']] = $r['cfg_valor'];
        return $c;
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data); exit; }
}
