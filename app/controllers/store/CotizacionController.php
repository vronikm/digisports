<?php
/**
 * DigiSports Store — Controlador de Cotizaciones / Proformas
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class CotizacionController extends \App\Controllers\ModuleController {

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

            $sql = "SELECT c.*, cl.cli_nombres, cl.cli_apellidos
                    FROM store_cotizaciones c
                    LEFT JOIN clientes cl ON cl.cli_cliente_id = c.cot_cliente_id AND cl.cli_tenant_id = c.cot_tenant_id
                    WHERE c.cot_tenant_id = ? AND DATE(c.cot_fecha) BETWEEN ? AND ?";
            $params = [$this->tenantId, $fechaDesde, $fechaHasta];

            if (!empty($estado)) { $sql .= " AND c.cot_estado = ?"; $params[] = $estado; }

            $sql .= " ORDER BY c.cot_fecha DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $cotizaciones = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['cotizaciones'] = $cotizaciones;
            $this->viewData['estadoFiltro'] = $estado;
            $this->viewData['fechaDesde']   = $fechaDesde;
            $this->viewData['fechaHasta']   = $fechaHasta;
            $this->viewData['csrf_token']   = \Security::generateCsrfToken();
            $this->viewData['title']        = 'Cotizaciones';

            $this->renderModule('store/cotizaciones/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando cotizaciones: " . $e->getMessage());
            $this->error('Error al cargar cotizaciones');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $clienteId = (int)($this->post('cliente_id') ?? 0) ?: null;
            $itemsJson = $this->post('items') ?? '[]';
            $items = json_decode($itemsJson, true);
            if (empty($items)) return $this->jsonResponse(['success' => false, 'message' => 'Agregue al menos un producto']);

            $this->db->beginTransaction();

            // Generar número
            $config = $this->getConfig();
            $prefijo = $config['prefijo_cotizacion'] ?? 'COT-';
            $stmt = $this->db->prepare("SELECT MAX(CAST(SUBSTRING(cot_numero, LENGTH(?) + 1) AS UNSIGNED)) FROM store_cotizaciones WHERE cot_tenant_id = ?");
            $stmt->execute([$prefijo, $this->tenantId]);
            $ultimo = (int)$stmt->fetchColumn();
            $numero = $prefijo . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);

            $subtotal = 0; $impuesto = 0;
            foreach ($items as $it) {
                $sub = ((float)($it['cantidad'] ?? 1)) * ((float)($it['precio_unitario'] ?? 0)) - ((float)($it['descuento'] ?? 0));
                $subtotal += $sub;
                $impPct = (float)($it['impuesto_porcentaje'] ?? 15);
                $impuesto += round($sub * ($impPct / 100), 2);
            }
            $total = round($subtotal + $impuesto, 2);

            $vigencia = (int)($this->post('vigencia_dias') ?? 15);

            $stmt = $this->db->prepare("INSERT INTO store_cotizaciones (
                cot_tenant_id, cot_numero, cot_cliente_id, cot_fecha, cot_vigencia_dias,
                cot_subtotal, cot_impuesto, cot_total, cot_notas, cot_estado, cot_usuario_id
            ) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, 'BORRADOR', ?)");

            $stmt->execute([
                $this->tenantId, $numero, $clienteId, $vigencia,
                round($subtotal, 2), round($impuesto, 2), $total,
                trim($this->post('notas') ?? '') ?: null, $this->userId
            ]);
            $cotId = (int)$this->db->lastInsertId();

            foreach ($items as $it) {
                $prodId = (int)($it['producto_id'] ?? 0);
                $varId  = (int)($it['variante_id'] ?? 0) ?: null;
                $cant   = (float)($it['cantidad'] ?? 1);
                $precio = (float)($it['precio_unitario'] ?? 0);
                $desc   = (float)($it['descuento'] ?? 0);
                $impLin = round((($cant * $precio) - $desc) * (((float)($it['impuesto_porcentaje'] ?? 15)) / 100), 2);
                $subLin = round(($cant * $precio) - $desc, 2);

                $this->db->prepare("INSERT INTO store_cotizacion_items (
                    coi_tenant_id, coi_cotizacion_id, coi_producto_id, coi_variante_id,
                    coi_descripcion, coi_cantidad, coi_precio_unitario, coi_descuento_linea, coi_impuesto_linea, coi_subtotal
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$this->tenantId, $cotId, $prodId, $varId, $it['descripcion'] ?? 'Producto', $cant, $precio, $desc, $impLin, $subLin]);
            }

            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => "Cotización {$numero} creada", 'cotizacion_id' => $cotId]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error creando cotización: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear cotización']);
        }
    }

    /** Cambiar estado de cotización */
    public function cambiarEstado() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            $estado = $this->post('estado') ?? '';
            $validos = ['ENVIADA', 'ACEPTADA', 'RECHAZADA', 'VENCIDA'];

            if (!$id || !in_array($estado, $validos)) return $this->jsonResponse(['success' => false, 'message' => 'Datos inválidos']);

            $this->db->prepare("UPDATE store_cotizaciones SET cot_estado = ? WHERE cot_cotizacion_id = ? AND cot_tenant_id = ?")->execute([$estado, $id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Estado actualizado']);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error']);
        }
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
