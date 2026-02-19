<?php
/**
 * DigiSports Store - Controlador Dashboard
 * Panel principal con KPIs reales desde la base de datos
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DashboardController extends \App\Controllers\ModuleController {
    
    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono  = 'fas fa-store';
    protected $moduloColor  = '#F59E0B';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }
    
    public function index() {
        $this->setupModule();
        $tid = $this->tenantId;

        // ── KPI 1: Ventas Hoy ──
        $hoy = date('Y-m-d');
        $stm = $this->db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(ven_total),0) as monto
                                   FROM store_ventas WHERE ven_tenant_id=? AND ven_estado='COMPLETADA' AND DATE(ven_fecha)=?");
        $stm->execute([$tid, $hoy]);
        $ventasHoy = $stm->fetch(\PDO::FETCH_ASSOC);

        // Ventas ayer para tendencia
        $ayer = date('Y-m-d', strtotime('-1 day'));
        $stm = $this->db->prepare("SELECT COUNT(*) as total FROM store_ventas WHERE ven_tenant_id=? AND ven_estado='COMPLETADA' AND DATE(ven_fecha)=?");
        $stm->execute([$tid, $ayer]);
        $ventasAyer = $stm->fetch(\PDO::FETCH_ASSOC);
        $trendVentas = $this->calcularTrend($ventasHoy['total'], $ventasAyer['total']);

        // ── KPI 2: Productos Activos ──
        $stm = $this->db->prepare("SELECT COUNT(*) as total FROM store_productos WHERE pro_tenant_id=? AND pro_estado='ACTIVO'");
        $stm->execute([$tid]);
        $totalProductos = $stm->fetchColumn();

        // ── KPI 3: Ingresos Hoy ──
        $trendIngresos = $this->calcularTrendMonto($tid, $hoy, $ayer);

        // ── KPI 4: Stock Bajo ──
        $stm = $this->db->prepare("SELECT COUNT(*) FROM store_stock s
                                   JOIN store_productos p ON s.stk_producto_id=p.pro_producto_id AND p.pro_tenant_id=s.stk_tenant_id
                                   WHERE s.stk_tenant_id=? AND s.stk_disponible <= p.pro_stock_minimo AND s.stk_disponible > 0 AND p.pro_estado='ACTIVO'");
        $stm->execute([$tid]);
        $stockBajo = $stm->fetchColumn();

        // ── KPI 5: Clientes Este Mes ──
        $mesActual = date('Y-m');
        $stm = $this->db->prepare("SELECT COUNT(*) FROM clientes WHERE cli_tenant_id=? AND DATE_FORMAT(cli_fecha_registro,'%Y-%m')=?");
        $stm->execute([$tid, $mesActual]);
        $clientesMes = $stm->fetchColumn();

        // ── KPI 6: Turno Abierto (Caja Activa) ──
        $stm = $this->db->prepare("SELECT COUNT(*) FROM store_caja_turnos WHERE tur_tenant_id=? AND tur_estado='ABIERTO'");
        $stm->execute([$tid]);
        $turnosAbiertos = $stm->fetchColumn();

        $this->viewData['kpis'] = [
            ['label' => 'Ventas Hoy',   'value' => $ventasHoy['total'],                         'icon' => 'fas fa-shopping-cart',        'color' => $this->moduloColor, 'trend' => $trendVentas['text'],    'trend_type' => $trendVentas['type']],
            ['label' => 'Productos',    'value' => $totalProductos,                              'icon' => 'fas fa-box',                  'color' => '#3B82F6',          'trend' => null,                    'trend_type' => null],
            ['label' => 'Ingresos Hoy', 'value' => '$' . number_format($ventasHoy['monto'], 2), 'icon' => 'fas fa-dollar-sign',          'color' => '#22C55E',          'trend' => $trendIngresos['text'],  'trend_type' => $trendIngresos['type']],
            ['label' => 'Stock Bajo',   'value' => $stockBajo,                                  'icon' => 'fas fa-exclamation-triangle',  'color' => '#EF4444',          'trend' => null,                    'trend_type' => null],
            ['label' => 'Clientes Mes', 'value' => $clientesMes,                                'icon' => 'fas fa-users',                'color' => '#8B5CF6',          'trend' => null,                    'trend_type' => null],
            ['label' => 'Cajas Activas','value' => $turnosAbiertos,                              'icon' => 'fas fa-cash-register',        'color' => '#0EA5E9',          'trend' => null,                    'trend_type' => null],
        ];

        // ── Últimas Ventas ──
        $stm = $this->db->prepare("SELECT v.*, 
                                       c.cli_nombres, c.cli_apellidos,
                                       (SELECT COUNT(*) FROM store_venta_items WHERE vit_venta_id=v.ven_venta_id AND vit_tenant_id=v.ven_tenant_id) as total_items,
                                       (SELECT GROUP_CONCAT(p.pro_nombre SEPARATOR ', ')
                                        FROM store_venta_items vi
                                        JOIN store_productos p ON vi.vit_producto_id=p.pro_producto_id AND p.pro_tenant_id=vi.vit_tenant_id
                                        WHERE vi.vit_venta_id=v.ven_venta_id AND vi.vit_tenant_id=v.ven_tenant_id LIMIT 3) as productos_nombres
                                   FROM store_ventas v
                                   LEFT JOIN clientes c ON v.ven_cliente_id=c.cli_cliente_id AND c.cli_tenant_id=v.ven_tenant_id
                                   WHERE v.ven_tenant_id=?
                                   ORDER BY v.ven_fecha DESC LIMIT 10");
        $stm->execute([$tid]);
        $this->viewData['ultimas_ventas'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

        // ── Top Productos (últimos 30 días) ──
        $stm = $this->db->prepare("SELECT p.pro_nombre, p.pro_precio_venta,
                                       SUM(vi.vit_cantidad) as total_vendido,
                                       COALESCE(s.stk_disponible, 0) as stock_actual
                                   FROM store_venta_items vi
                                   JOIN store_ventas v ON vi.vit_venta_id=v.ven_venta_id AND v.ven_tenant_id=vi.vit_tenant_id
                                   JOIN store_productos p ON vi.vit_producto_id=p.pro_producto_id AND p.pro_tenant_id=vi.vit_tenant_id
                                   LEFT JOIN store_stock s ON s.stk_producto_id=p.pro_producto_id AND s.stk_tenant_id=p.pro_tenant_id AND s.stk_variante_id IS NULL
                                   WHERE vi.vit_tenant_id=? AND v.ven_estado='COMPLETADA' AND v.ven_fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                   GROUP BY p.pro_producto_id, p.pro_nombre, p.pro_precio_venta, s.stk_disponible
                                   ORDER BY total_vendido DESC LIMIT 5");
        $stm->execute([$tid]);
        $this->viewData['productos_top'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

        // ── Stock Bajo (alertas) ──
        $stm = $this->db->prepare("SELECT p.pro_nombre, s.stk_disponible, p.pro_stock_minimo
                                   FROM store_stock s
                                   JOIN store_productos p ON s.stk_producto_id=p.pro_producto_id AND p.pro_tenant_id=s.stk_tenant_id
                                   WHERE s.stk_tenant_id=? AND s.stk_disponible <= p.pro_stock_minimo AND p.pro_estado='ACTIVO'
                                   ORDER BY s.stk_disponible ASC LIMIT 8");
        $stm->execute([$tid]);
        $this->viewData['stock_bajo'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

        // ── Ventas últimos 7 días (para gráfico) ──
        $stm = $this->db->prepare("SELECT DATE(ven_fecha) as dia, COUNT(*) as cantidad, COALESCE(SUM(ven_total),0) as monto
                                   FROM store_ventas
                                   WHERE ven_tenant_id=? AND ven_estado='COMPLETADA' AND ven_fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                   GROUP BY DATE(ven_fecha)
                                   ORDER BY dia ASC");
        $stm->execute([$tid]);
        $ventasSemana = $stm->fetchAll(\PDO::FETCH_ASSOC);
        
        // Rellenar días sin ventas
        $chartLabels = [];
        $chartData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = date('Y-m-d', strtotime("-{$i} days"));
            $chartLabels[] = $this->nombreDia($dia);
            $found = false;
            foreach ($ventasSemana as $v) {
                if ($v['dia'] === $dia) {
                    $chartData[] = floatval($v['monto']);
                    $found = true;
                    break;
                }
            }
            if (!$found) $chartData[] = 0;
        }
        $this->viewData['chart_labels'] = json_encode($chartLabels);
        $this->viewData['chart_data']   = json_encode($chartData);

        // ── Ventas por Categoría ──
        $stm = $this->db->prepare("SELECT cat.cat_nombre, COALESCE(SUM(vi.vit_total),0) as total_ventas
                                   FROM store_categorias cat
                                   LEFT JOIN store_productos p ON p.pro_categoria_id=cat.cat_categoria_id AND p.pro_tenant_id=cat.cat_tenant_id
                                   LEFT JOIN store_venta_items vi ON vi.vit_producto_id=p.pro_producto_id AND vi.vit_tenant_id=p.pro_tenant_id
                                   LEFT JOIN store_ventas v ON v.ven_venta_id=vi.vit_venta_id AND v.ven_tenant_id=vi.vit_tenant_id AND v.ven_estado='COMPLETADA'
                                   WHERE cat.cat_tenant_id=? AND cat.cat_padre_id IS NULL
                                   GROUP BY cat.cat_categoria_id, cat.cat_nombre
                                   ORDER BY total_ventas DESC LIMIT 6");
        $stm->execute([$tid]);
        $this->viewData['ventas_categoria'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

        $this->viewData['title'] = 'Dashboard - ' . $this->moduloNombre;
        $this->renderModule('store/dashboard/index');
    }

    /**
     * Calcular tendencia entre dos valores numéricos
     */
    private function calcularTrend($actual, $anterior) {
        if ($anterior == 0) {
            return $actual > 0 ? ['text' => '+' . $actual, 'type' => 'up'] : ['text' => null, 'type' => null];
        }
        $diff = $actual - $anterior;
        $pct  = round(($diff / $anterior) * 100);
        if ($diff > 0)  return ['text' => '+' . $pct . '%', 'type' => 'up'];
        if ($diff < 0)  return ['text' => $pct . '%',       'type' => 'down'];
        return ['text' => '0%', 'type' => null];
    }

    /**
     * Calcular tendencia de monto de ingresos
     */
    private function calcularTrendMonto($tid, $hoy, $ayer) {
        $stm = $this->db->prepare("SELECT COALESCE(SUM(ven_total),0) as monto FROM store_ventas WHERE ven_tenant_id=? AND ven_estado='COMPLETADA' AND DATE(ven_fecha)=?");
        $stm->execute([$tid, $hoy]);
        $montoHoy = $stm->fetchColumn();
        $stm->execute([$tid, $ayer]);
        $montoAyer = $stm->fetchColumn();
        return $this->calcularTrend($montoHoy, $montoAyer);
    }

    /**
     * Nombre del día en español
     */
    private function nombreDia($fecha) {
        $dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
        return $dias[date('w', strtotime($fecha))];
    }
}
