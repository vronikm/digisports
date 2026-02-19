<?php
/**
 * DigiSports Store — Controlador de Devoluciones
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DevolucionController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono  = 'fas fa-store';
    protected $moduloColor  = '#F59E0B';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }

    public function index() {
        try {
            $buscar     = trim($this->get('buscar') ?? '');
            $estado     = $this->get('estado') ?? '';
            $fechaDesde = $this->get('fecha_desde') ?? date('Y-m-01');
            $fechaHasta = $this->get('fecha_hasta') ?? date('Y-m-d');

            $sql = "SELECT d.*, v.ven_numero
                    FROM store_devoluciones d
                    JOIN store_ventas v ON v.ven_venta_id = d.dev_venta_id
                    WHERE d.dev_tenant_id = ? AND DATE(d.dev_fecha) BETWEEN ? AND ?";
            $params = [$this->tenantId, $fechaDesde, $fechaHasta];

            if (!empty($buscar)) {
                $sql .= " AND (d.dev_numero LIKE ? OR v.ven_numero LIKE ?)";
                $like = "%{$buscar}%";
                $params = array_merge($params, [$like, $like]);
            }
            if (!empty($estado)) {
                $sql .= " AND d.dev_estado = ?";
                $params[] = $estado;
            }

            $sql .= " ORDER BY d.dev_fecha DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $devoluciones = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['devoluciones'] = $devoluciones;
            $this->viewData['buscar']       = $buscar;
            $this->viewData['estadoFiltro'] = $estado;
            $this->viewData['fechaDesde']   = $fechaDesde;
            $this->viewData['fechaHasta']   = $fechaHasta;
            $this->viewData['csrf_token']   = \Security::generateCsrfToken();
            $this->viewData['title']        = 'Devoluciones';

            $this->renderModule('store/devoluciones/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando devoluciones: " . $e->getMessage());
            $this->error('Error al cargar devoluciones');
        }
    }

    /** Crear devolución desde una venta */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $ventaId = (int)($this->post('venta_id') ?? 0);
            $motivo  = trim($this->post('motivo') ?? '');
            $tipoReembolso = $this->post('tipo_reembolso') ?? 'EFECTIVO';
            $itemsJson = $this->post('items') ?? '[]';
            $items = json_decode($itemsJson, true);

            if (!$ventaId || empty($motivo) || empty($items)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
            }

            // Verificar venta
            $stmt = $this->db->prepare("SELECT * FROM store_ventas WHERE ven_venta_id = ? AND ven_tenant_id = ? AND ven_estado = 'COMPLETADA'");
            $stmt->execute([$ventaId, $this->tenantId]);
            $venta = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$venta) return $this->jsonResponse(['success' => false, 'message' => 'Venta no válida para devolución']);

            $this->db->beginTransaction();

            // Generar número
            $config = $this->getConfig();
            $prefijo = $config['prefijo_devolucion'] ?? 'DEV-';
            $stmt = $this->db->prepare("SELECT MAX(CAST(SUBSTRING(dev_numero, LENGTH(?) + 1) AS UNSIGNED)) FROM store_devoluciones WHERE dev_tenant_id = ?");
            $stmt->execute([$prefijo, $this->tenantId]);
            $ultimo = (int)$stmt->fetchColumn();
            $numero = $prefijo . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);

            // Turno abierto
            $turnoId = null;
            $stmtTurno = $this->db->prepare("SELECT tur_turno_id FROM store_caja_turnos WHERE tur_usuario_id = ? AND tur_tenant_id = ? AND tur_estado = 'ABIERTO' LIMIT 1");
            $stmtTurno->execute([$this->userId, $this->tenantId]);
            $turnoRow = $stmtTurno->fetch(\PDO::FETCH_ASSOC);
            if ($turnoRow) $turnoId = $turnoRow['tur_turno_id'];

            // Calcular totales
            $subtotal = 0;
            $impuesto = 0;

            foreach ($items as $it) {
                $cantidad = (float)($it['cantidad'] ?? 0);
                $precio   = (float)($it['precio_unitario'] ?? 0);
                $sub = $cantidad * $precio;
                $subtotal += $sub;
                // Asumimos IVA del item original
                $impPct = (float)($it['impuesto_porcentaje'] ?? 15);
                $impuesto += round($sub * ($impPct / 100), 2);
            }
            $total = round($subtotal + $impuesto, 2);

            // Insertar devolución
            $stmt = $this->db->prepare("INSERT INTO store_devoluciones (
                dev_tenant_id, dev_venta_id, dev_turno_id, dev_numero, dev_fecha, dev_motivo,
                dev_subtotal, dev_impuesto, dev_total, dev_tipo_reembolso, dev_estado, dev_usuario_id
            ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, 'COMPLETADA', ?)");
            $stmt->execute([
                $this->tenantId, $ventaId, $turnoId, $numero, $motivo,
                round($subtotal, 2), round($impuesto, 2), $total,
                $tipoReembolso, $this->userId
            ]);
            $devolucionId = (int)$this->db->lastInsertId();

            // Items de devolución + restaurar stock
            foreach ($items as $it) {
                $ventaItemId = (int)($it['venta_item_id'] ?? 0);
                $productoId  = (int)($it['producto_id'] ?? 0);
                $varianteId  = (int)($it['variante_id'] ?? 0) ?: null;
                $cantidad    = (float)($it['cantidad'] ?? 0);
                $precioUnit  = (float)($it['precio_unitario'] ?? 0);
                $devolverStock = (int)($it['devolver_stock'] ?? 1);

                $this->db->prepare("INSERT INTO store_devolucion_items (
                    dvi_tenant_id, dvi_devolucion_id, dvi_venta_item_id, dvi_producto_id,
                    dvi_variante_id, dvi_cantidad, dvi_precio_unitario, dvi_subtotal, dvi_devolver_stock
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$this->tenantId, $devolucionId, $ventaItemId, $productoId, $varianteId, $cantidad, $precioUnit, round($cantidad * $precioUnit, 2), $devolverStock]);

                if ($devolverStock) {
                    $this->restaurarStock($productoId, $varianteId, $cantidad, $devolucionId);
                }
            }

            $this->db->commit();

            return $this->jsonResponse(['success' => true, 'message' => "Devolución {$numero} registrada", 'devolucion_id' => $devolucionId]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error creando devolución: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar devolución']);
        }
    }

    private function restaurarStock($productoId, $varianteId, $cantidad, $refId) {
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
        }

        $this->db->prepare("INSERT INTO store_stock_movimientos (mov_tenant_id, mov_producto_id, mov_variante_id, mov_tipo, mov_cantidad, mov_stock_anterior, mov_stock_posterior, mov_referencia_tipo, mov_referencia_id, mov_motivo, mov_usuario_id) VALUES (?, ?, ?, 'DEVOLUCION', ?, ?, ?, 'DEVOLUCION', ?, 'Devolución', ?)")
            ->execute([$this->tenantId, $productoId, $varianteId, $cantidad, $anterior, $posterior, $refId, $this->userId]);
    }

    private function getConfig() {
        $stmt = $this->db->prepare("SELECT cfg_clave, cfg_valor FROM store_configuracion WHERE cfg_tenant_id = ?");
        $stmt->execute([$this->tenantId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $c = [];
        foreach ($rows as $r) $c[$r['cfg_clave']] = $r['cfg_valor'];
        return $c;
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data); exit; }
}
