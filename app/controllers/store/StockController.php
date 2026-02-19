<?php
/**
 * DigiSports Store — Controlador de Stock / Inventario
 * Consulta de stock, movimientos y alertas
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class StockController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono  = 'fas fa-store';
    protected $moduloColor  = '#F59E0B';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }

    /** Vista de inventario actual */
    public function index() {
        try {
            $buscar = trim($this->get('buscar') ?? '');
            $filtro = $this->get('filtro') ?? ''; // bajo, agotado, normal

            $sql = "SELECT p.pro_producto_id, p.pro_nombre, p.pro_codigo, p.pro_sku, p.pro_precio_venta,
                           p.pro_precio_compra, p.pro_stock_minimo, p.pro_estado,
                           c.cat_nombre, m.mar_nombre,
                           COALESCE(s.stk_cantidad, 0) AS stock_total,
                           COALESCE(s.stk_reservado, 0) AS stock_reservado,
                           COALESCE(s.stk_disponible, 0) AS stock_disponible
                    FROM store_productos p
                    LEFT JOIN store_categorias c ON c.cat_categoria_id = p.pro_categoria_id
                    LEFT JOIN store_marcas m ON m.mar_marca_id = p.pro_marca_id
                    LEFT JOIN store_stock s ON s.stk_producto_id = p.pro_producto_id 
                                           AND s.stk_variante_id IS NULL
                                           AND s.stk_tenant_id = p.pro_tenant_id
                    WHERE p.pro_tenant_id = ? AND p.pro_estado != 'DESCONTINUADO'";
            $params = [$this->tenantId];

            if (!empty($buscar)) {
                $sql .= " AND (p.pro_nombre LIKE ? OR p.pro_codigo LIKE ? OR p.pro_sku LIKE ?)";
                $like = "%{$buscar}%";
                $params = array_merge($params, [$like, $like, $like]);
            }

            if ($filtro === 'bajo') {
                $sql .= " AND COALESCE(s.stk_disponible, 0) <= p.pro_stock_minimo AND COALESCE(s.stk_disponible, 0) > 0";
            } elseif ($filtro === 'agotado') {
                $sql .= " AND COALESCE(s.stk_disponible, 0) <= 0";
            }

            $sql .= " ORDER BY p.pro_nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $inventario = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Resumen
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) AS total_productos,
                    SUM(CASE WHEN COALESCE(s.stk_disponible, 0) <= 0 THEN 1 ELSE 0 END) AS agotados,
                    SUM(CASE WHEN COALESCE(s.stk_disponible, 0) > 0 AND COALESCE(s.stk_disponible, 0) <= p.pro_stock_minimo THEN 1 ELSE 0 END) AS stock_bajo,
                    COALESCE(SUM(COALESCE(s.stk_cantidad, 0) * p.pro_precio_compra), 0) AS valor_inventario
                FROM store_productos p
                LEFT JOIN store_stock s ON s.stk_producto_id = p.pro_producto_id 
                                       AND s.stk_variante_id IS NULL AND s.stk_tenant_id = p.pro_tenant_id
                WHERE p.pro_tenant_id = ? AND p.pro_estado != 'DESCONTINUADO'
            ");
            $stmt->execute([$this->tenantId]);
            $resumen = $stmt->fetch(\PDO::FETCH_ASSOC);

            $this->viewData['inventario']  = $inventario;
            $this->viewData['resumen']     = $resumen;
            $this->viewData['buscar']      = $buscar;
            $this->viewData['filtro']      = $filtro;
            $this->viewData['csrf_token']  = \Security::generateCsrfToken();
            $this->viewData['title']       = 'Inventario';

            $this->renderModule('store/stock/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error inventario: " . $e->getMessage());
            $this->error('Error al cargar inventario');
        }
    }

    /** Historial de movimientos de stock */
    public function movimientos() {
        try {
            $productoId = (int)($this->get('producto_id') ?? 0);
            $tipo       = $this->get('tipo') ?? '';
            $fechaDesde = $this->get('fecha_desde') ?? date('Y-m-01');
            $fechaHasta = $this->get('fecha_hasta') ?? date('Y-m-d');

            $sql = "SELECT m.*, p.pro_nombre
                    FROM store_stock_movimientos m
                    JOIN store_productos p ON p.pro_producto_id = m.mov_producto_id
                    WHERE m.mov_tenant_id = ? AND DATE(m.mov_fecha_registro) BETWEEN ? AND ?";
            $params = [$this->tenantId, $fechaDesde, $fechaHasta];

            if ($productoId > 0) {
                $sql .= " AND m.mov_producto_id = ?";
                $params[] = $productoId;
            }
            if (!empty($tipo)) {
                $sql .= " AND m.mov_tipo = ?";
                $params[] = $tipo;
            }

            $sql .= " ORDER BY m.mov_fecha_registro DESC LIMIT 200";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $movimientos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['movimientos'] = $movimientos;
            $this->viewData['productoId']  = $productoId;
            $this->viewData['tipoFiltro']  = $tipo;
            $this->viewData['fechaDesde']  = $fechaDesde;
            $this->viewData['fechaHasta']  = $fechaHasta;
            $this->viewData['title']       = 'Movimientos de Stock';

            $this->renderModule('store/stock/movimientos', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error movimientos stock: " . $e->getMessage());
            $this->error('Error al cargar movimientos');
        }
    }

    /** Alertas de stock bajo */
    public function alertas() {
        try {
            $estado = $this->get('estado') ?? 'PENDIENTE';

            $sql = "SELECT a.*, p.pro_nombre, p.pro_codigo
                    FROM store_stock_alertas a
                    JOIN store_productos p ON p.pro_producto_id = a.ale_producto_id
                    WHERE a.ale_tenant_id = ? AND a.ale_estado = ?
                    ORDER BY a.ale_fecha_generada DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->tenantId, $estado]);
            $alertas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['alertas']      = $alertas;
            $this->viewData['estadoFiltro'] = $estado;
            $this->viewData['csrf_token']   = \Security::generateCsrfToken();
            $this->viewData['title']        = 'Alertas de Stock';

            $this->renderModule('store/stock/alertas', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error alertas stock: " . $e->getMessage());
            $this->error('Error al cargar alertas');
        }
    }

    /** Resolver/ignorar alerta */
    public function resolverAlerta() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $alertaId = (int)($this->post('alerta_id') ?? 0);
            $accion   = $this->post('accion') ?? 'RESUELTA'; // RESUELTA o IGNORADA

            $this->db->prepare("UPDATE store_stock_alertas SET ale_estado = ?, ale_fecha_resuelta = NOW() WHERE ale_alerta_id = ? AND ale_tenant_id = ?")
                ->execute([$accion, $alertaId, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Alerta actualizada']);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data); exit; }
}
