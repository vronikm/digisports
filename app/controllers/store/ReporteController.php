<?php
/**
 * DigiSports Store — Controlador de Reportes
 * KPIs, gráficos y reportes operativos
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class ReporteController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono  = 'fas fa-store';
    protected $moduloColor  = '#F59E0B';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }

    /** Reporte de Ventas */
    public function ventas() {
        try {
            $fechaDesde = $this->get('fecha_desde') ?? date('Y-m-01');
            $fechaHasta = $this->get('fecha_hasta') ?? date('Y-m-d');
            $agrupacion = $this->get('agrupacion') ?? 'dia'; // dia, semana, mes

            // Resumen general
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS total_ventas,
                       COALESCE(SUM(CASE WHEN ven_estado='COMPLETADA' THEN ven_total ELSE 0 END), 0) AS total_vendido,
                       COALESCE(SUM(CASE WHEN ven_estado='COMPLETADA' THEN ven_impuesto ELSE 0 END), 0) AS total_iva,
                       COALESCE(SUM(CASE WHEN ven_estado='COMPLETADA' THEN ven_descuento ELSE 0 END), 0) AS total_descuentos,
                       COALESCE(AVG(CASE WHEN ven_estado='COMPLETADA' THEN ven_total END), 0) AS ticket_promedio,
                       SUM(CASE WHEN ven_estado='ANULADA' THEN 1 ELSE 0 END) AS ventas_anuladas
                FROM store_ventas WHERE ven_tenant_id = ? AND DATE(ven_fecha) BETWEEN ? AND ?
            ");
            $stmt->execute([$this->tenantId, $fechaDesde, $fechaHasta]);
            $resumen = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Ventas por día (para gráfico)
            $groupBy = 'DATE(ven_fecha)';
            $dateFormat = '%Y-%m-%d';
            if ($agrupacion === 'semana') { $groupBy = 'YEARWEEK(ven_fecha, 1)'; $dateFormat = '%x-W%v'; }
            if ($agrupacion === 'mes') { $groupBy = 'DATE_FORMAT(ven_fecha, "%Y-%m")'; $dateFormat = '%Y-%m'; }

            $stmt = $this->db->prepare("
                SELECT DATE_FORMAT(ven_fecha, '{$dateFormat}') AS periodo,
                       COUNT(*) AS num_ventas,
                       COALESCE(SUM(ven_total), 0) AS total
                FROM store_ventas 
                WHERE ven_tenant_id = ? AND ven_estado = 'COMPLETADA' AND DATE(ven_fecha) BETWEEN ? AND ?
                GROUP BY {$groupBy}
                ORDER BY MIN(ven_fecha)
            ");
            $stmt->execute([$this->tenantId, $fechaDesde, $fechaHasta]);
            $ventasPorPeriodo = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Top productos vendidos
            $stmt = $this->db->prepare("
                SELECT vi.vit_descripcion AS producto, 
                       SUM(vi.vit_cantidad) AS cantidad,
                       SUM(vi.vit_subtotal) AS total
                FROM store_venta_items vi
                JOIN store_ventas v ON v.ven_venta_id = vi.vit_venta_id AND v.ven_estado = 'COMPLETADA'
                WHERE vi.vit_tenant_id = ? AND DATE(v.ven_fecha) BETWEEN ? AND ?
                GROUP BY vi.vit_producto_id, vi.vit_descripcion
                ORDER BY total DESC LIMIT 15
            ");
            $stmt->execute([$this->tenantId, $fechaDesde, $fechaHasta]);
            $topProductos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Ventas por forma de pago
            $stmt = $this->db->prepare("
                SELECT vpg.vpg_forma_pago AS forma, COALESCE(SUM(vpg.vpg_monto), 0) AS total
                FROM store_venta_pagos vpg
                JOIN store_ventas v ON v.ven_venta_id = vpg.vpg_venta_id AND v.ven_estado = 'COMPLETADA'
                WHERE vpg.vpg_tenant_id = ? AND DATE(v.ven_fecha) BETWEEN ? AND ?
                GROUP BY vpg.vpg_forma_pago
            ");
            $stmt->execute([$this->tenantId, $fechaDesde, $fechaHasta]);
            $ventasPorPago = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Ventas por categoría
            $stmt = $this->db->prepare("
                SELECT COALESCE(c.cat_nombre, 'Sin Categoría') AS categoria, 
                       COALESCE(SUM(vi.vit_subtotal), 0) AS total
                FROM store_venta_items vi
                JOIN store_ventas v ON v.ven_venta_id = vi.vit_venta_id AND v.ven_estado = 'COMPLETADA'
                LEFT JOIN store_productos p ON p.pro_producto_id = vi.vit_producto_id
                LEFT JOIN store_categorias c ON c.cat_categoria_id = p.pro_categoria_id
                WHERE vi.vit_tenant_id = ? AND DATE(v.ven_fecha) BETWEEN ? AND ?
                GROUP BY p.pro_categoria_id, c.cat_nombre
                ORDER BY total DESC
            ");
            $stmt->execute([$this->tenantId, $fechaDesde, $fechaHasta]);
            $ventasPorCategoria = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['resumen']             = $resumen;
            $this->viewData['ventasPorPeriodo']    = $ventasPorPeriodo;
            $this->viewData['topProductos']        = $topProductos;
            $this->viewData['ventasPorPago']       = $ventasPorPago;
            $this->viewData['ventasPorCategoria']  = $ventasPorCategoria;
            $this->viewData['fechaDesde']          = $fechaDesde;
            $this->viewData['fechaHasta']          = $fechaHasta;
            $this->viewData['agrupacion']          = $agrupacion;
            $this->viewData['title']               = 'Reporte de Ventas';

            $this->renderModule('store/reportes/ventas', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error reporte ventas: " . $e->getMessage());
            $this->error('Error al generar reporte');
        }
    }

    /** Reporte de Inventario / Valorización */
    public function inventario() {
        try {
            // Valorización por categoría
            $stmt = $this->db->prepare("
                SELECT COALESCE(c.cat_nombre, 'Sin Categoría') AS categoria,
                       COUNT(DISTINCT p.pro_producto_id) AS num_productos,
                       COALESCE(SUM(s.stk_cantidad), 0) AS unidades,
                       COALESCE(SUM(s.stk_cantidad * p.pro_precio_compra), 0) AS valor_costo,
                       COALESCE(SUM(s.stk_cantidad * p.pro_precio_venta), 0) AS valor_venta
                FROM store_productos p
                LEFT JOIN store_categorias c ON c.cat_categoria_id = p.pro_categoria_id
                LEFT JOIN store_stock s ON s.stk_producto_id = p.pro_producto_id AND s.stk_variante_id IS NULL AND s.stk_tenant_id = p.pro_tenant_id
                WHERE p.pro_tenant_id = ? AND p.pro_estado != 'DESCONTINUADO'
                GROUP BY p.pro_categoria_id, c.cat_nombre
                ORDER BY valor_venta DESC
            ");
            $stmt->execute([$this->tenantId]);
            $inventarioPorCategoria = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Productos con más rotación (últimos 30 días)
            $stmt = $this->db->prepare("
                SELECT p.pro_nombre, SUM(ABS(m.mov_cantidad)) AS movimientos
                FROM store_stock_movimientos m
                JOIN store_productos p ON p.pro_producto_id = m.mov_producto_id
                WHERE m.mov_tenant_id = ? AND m.mov_fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY m.mov_producto_id, p.pro_nombre
                ORDER BY movimientos DESC LIMIT 15
            ");
            $stmt->execute([$this->tenantId]);
            $masRotacion = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Productos sin movimiento (30 días)
            $stmt = $this->db->prepare("
                SELECT p.pro_nombre, p.pro_codigo, COALESCE(s.stk_cantidad, 0) AS stock,
                       MAX(m.mov_fecha_registro) AS ultimo_movimiento
                FROM store_productos p
                LEFT JOIN store_stock s ON s.stk_producto_id = p.pro_producto_id AND s.stk_variante_id IS NULL AND s.stk_tenant_id = p.pro_tenant_id
                LEFT JOIN store_stock_movimientos m ON m.mov_producto_id = p.pro_producto_id AND m.mov_tenant_id = p.pro_tenant_id
                WHERE p.pro_tenant_id = ? AND p.pro_estado = 'ACTIVO'
                GROUP BY p.pro_producto_id
                HAVING ultimo_movimiento IS NULL OR ultimo_movimiento < DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY ultimo_movimiento ASC
                LIMIT 20
            ");
            $stmt->execute([$this->tenantId]);
            $sinMovimiento = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Alertas pendientes
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM store_stock_alertas WHERE ale_tenant_id = ? AND ale_estado = 'PENDIENTE'");
            $stmt->execute([$this->tenantId]);
            $alertasPendientes = (int)$stmt->fetchColumn();

            $this->viewData['inventarioPorCategoria'] = $inventarioPorCategoria;
            $this->viewData['masRotacion']            = $masRotacion;
            $this->viewData['sinMovimiento']          = $sinMovimiento;
            $this->viewData['alertasPendientes']      = $alertasPendientes;
            $this->viewData['title']                  = 'Reporte de Inventario';

            $this->renderModule('store/reportes/inventario', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error reporte inventario: " . $e->getMessage());
            $this->error('Error al generar reporte');
        }
    }

    /** Reporte de Caja */
    public function caja() {
        try {
            $fechaDesde = $this->get('fecha_desde') ?? date('Y-m-01');
            $fechaHasta = $this->get('fecha_hasta') ?? date('Y-m-d');

            $stmt = $this->db->prepare("
                SELECT t.*, c.caj_nombre
                FROM store_caja_turnos t
                JOIN store_cajas c ON c.caj_caja_id = t.tur_caja_id
                WHERE t.tur_tenant_id = ? AND t.tur_estado = 'CERRADO' AND DATE(t.tur_fecha_apertura) BETWEEN ? AND ?
                ORDER BY t.tur_fecha_apertura DESC
            ");
            $stmt->execute([$this->tenantId, $fechaDesde, $fechaHasta]);
            $turnos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Resumen
            $totalVentas = 0; $totalDiferencia = 0; $totalDevoluciones = 0;
            foreach ($turnos as $t) {
                $totalVentas += (float)$t['tur_total_ventas'];
                $totalDiferencia += (float)$t['tur_diferencia'];
                $totalDevoluciones += (float)$t['tur_total_devoluciones'];
            }

            $this->viewData['turnos']            = $turnos;
            $this->viewData['totalVentas']       = $totalVentas;
            $this->viewData['totalDiferencia']   = $totalDiferencia;
            $this->viewData['totalDevoluciones'] = $totalDevoluciones;
            $this->viewData['fechaDesde']        = $fechaDesde;
            $this->viewData['fechaHasta']        = $fechaHasta;
            $this->viewData['title']             = 'Reporte de Caja';

            $this->renderModule('store/reportes/caja', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error reporte caja: " . $e->getMessage());
            $this->error('Error al generar reporte');
        }
    }

    /** Reporte de Clientes */
    public function clientes() {
        try {
            // Top clientes por compras (datos compartidos + extensión Store)
            $stmt = $this->db->prepare("
                SELECT c.cli_nombres, c.cli_apellidos, c.cli_identificacion,
                       COALESCE(sc.scl_categoria, 'NUEVO') AS scl_categoria,
                       COALESCE(sc.scl_total_compras, 0) AS scl_total_compras,
                       COALESCE(sc.scl_num_compras, 0) AS scl_num_compras,
                       COALESCE(sc.scl_puntos_disponibles, 0) AS scl_puntos_disponibles,
                       sc.scl_ultima_compra
                FROM clientes c
                LEFT JOIN store_clientes sc ON sc.scl_cliente_id = c.cli_cliente_id AND sc.scl_tenant_id = c.cli_tenant_id
                WHERE c.cli_tenant_id = ? AND c.cli_estado = 'A'
                ORDER BY COALESCE(sc.scl_total_compras, 0) DESC LIMIT 25
            ");
            $stmt->execute([$this->tenantId]);
            $topClientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            // Descifrar identificación
            foreach ($topClientes as &$tc) {
                $tc['cli_identificacion'] = \DataProtection::decrypt($tc['cli_identificacion'] ?? null);
            }
            unset($tc);

            // Distribución por categoría
            $stmt = $this->db->prepare("
                SELECT COALESCE(sc.scl_categoria, 'NUEVO') AS categoria, COUNT(*) AS total
                FROM clientes c
                LEFT JOIN store_clientes sc ON sc.scl_cliente_id = c.cli_cliente_id AND sc.scl_tenant_id = c.cli_tenant_id
                WHERE c.cli_tenant_id = ? AND c.cli_estado = 'A' GROUP BY categoria
            ");
            $stmt->execute([$this->tenantId]);
            $distribucion = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Clientes nuevos últimos 30 días
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM clientes WHERE cli_tenant_id = ? AND cli_fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute([$this->tenantId]);
            $nuevos30d = (int)$stmt->fetchColumn();

            $this->viewData['topClientes']   = $topClientes;
            $this->viewData['distribucion']  = $distribucion;
            $this->viewData['nuevos30d']     = $nuevos30d;
            $this->viewData['title']         = 'Reporte de Clientes';

            $this->renderModule('store/reportes/clientes', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error reporte clientes: " . $e->getMessage());
            $this->error('Error al generar reporte');
        }
    }

    /** Reporte de Utilidad */
    public function utilidad() {
        try {
            $fechaDesde = $this->get('fecha_desde') ?? date('Y-m-01');
            $fechaHasta = $this->get('fecha_hasta') ?? date('Y-m-d');

            $stmt = $this->db->prepare("
                SELECT vi.vit_descripcion AS producto,
                       SUM(vi.vit_cantidad) AS unidades,
                       SUM(vi.vit_cantidad * vi.vit_precio_unitario) AS ingreso_bruto,
                       SUM(vi.vit_cantidad * COALESCE(vi.vit_costo_unitario, 0)) AS costo_total,
                       SUM(vi.vit_cantidad * vi.vit_precio_unitario) - SUM(vi.vit_cantidad * COALESCE(vi.vit_costo_unitario, 0)) AS utilidad,
                       CASE WHEN SUM(vi.vit_cantidad * vi.vit_precio_unitario) > 0 
                            THEN ROUND(((SUM(vi.vit_cantidad * vi.vit_precio_unitario) - SUM(vi.vit_cantidad * COALESCE(vi.vit_costo_unitario, 0))) / SUM(vi.vit_cantidad * vi.vit_precio_unitario)) * 100, 2)
                            ELSE 0 END AS margen_pct
                FROM store_venta_items vi
                JOIN store_ventas v ON v.ven_venta_id = vi.vit_venta_id AND v.ven_estado = 'COMPLETADA'
                WHERE vi.vit_tenant_id = ? AND DATE(v.ven_fecha) BETWEEN ? AND ?
                GROUP BY vi.vit_producto_id, vi.vit_descripcion
                ORDER BY utilidad DESC
            ");
            $stmt->execute([$this->tenantId, $fechaDesde, $fechaHasta]);
            $utilidades = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Totales
            $totalIngreso = 0; $totalCosto = 0;
            foreach ($utilidades as $u) {
                $totalIngreso += (float)$u['ingreso_bruto'];
                $totalCosto += (float)$u['costo_total'];
            }

            $this->viewData['utilidades']    = $utilidades;
            $this->viewData['totalIngreso']  = $totalIngreso;
            $this->viewData['totalCosto']    = $totalCosto;
            $this->viewData['totalUtilidad'] = $totalIngreso - $totalCosto;
            $this->viewData['fechaDesde']    = $fechaDesde;
            $this->viewData['fechaHasta']    = $fechaHasta;
            $this->viewData['title']         = 'Reporte de Utilidad';

            $this->renderModule('store/reportes/utilidad', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error reporte utilidad: " . $e->getMessage());
            $this->error('Error al generar reporte');
        }
    }
}
