<?php
/**
 * DigiSports Arena - Dashboard de Instalaciones
 * KPIs, gráficos y resumen operativo
 * 
 * @package DigiSports\Controllers\Instalaciones
 * @version 2.0.0
 */

namespace App\Controllers\Instalaciones;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DashboardController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Arena';
    protected $moduloIcono = 'fas fa-building';
    protected $moduloColor = '#3B82F6';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'ARENA';
    }

    public function index() {
        $tenantId = $_SESSION['tenant_id'] ?? $_SESSION['usu_tenant_id'] ?? 1;

        // ── KPIs ──
        $kpis = $this->getKPIs($tenantId);

        // ── Canchas recientes ──
        $canchas = $this->getCanchasResumen($tenantId);

        // ── Reservas de hoy ──
        $reservasHoy = $this->getReservasHoy($tenantId);

        // ── Chart últimos 7 días ──
        $chartReservas = $this->getChartReservas7d($tenantId);

        // ── Últimos pagos ──
        $ultimosPagos = $this->getUltimosPagos($tenantId);

        // ── Chart pagos por método ──
        $chartMetodosPago = $this->getChartMetodosPago($tenantId);

        $this->renderModule('instalaciones/dashboard/index', [
            'kpis'               => $kpis,
            'canchas'            => $canchas,
            'reservas_hoy'       => $reservasHoy,
            'chart_reservas'     => $chartReservas,
            'ultimos_pagos'      => $ultimosPagos,
            'chart_metodos_pago' => $chartMetodosPago,
            'pageTitle'          => 'DigiSports Arena',
        ]);
    }

    /* ─────────── helpers privados ─────────── */

    private function getKPIs($tenantId) {
        $hoy = date('Y-m-d');
        $inicioMes = date('Y-m-01');

        $default = [
            ['label' => 'Canchas',          'value' => 0,    'icon' => 'fas fa-futbol',           'color' => $this->moduloColor, 'trend' => '', 'trend_type' => ''],
            ['label' => 'Reservas Hoy',     'value' => 0,    'icon' => 'fas fa-calendar-day',     'color' => '#10B981',          'trend' => '', 'trend_type' => ''],
            ['label' => 'Ingresos Mes',     'value' => '$0', 'icon' => 'fas fa-dollar-sign',      'color' => '#F59E0B',          'trend' => '', 'trend_type' => ''],
            ['label' => 'Ocupación Hoy',    'value' => '0%', 'icon' => 'fas fa-chart-pie',        'color' => '#8B5CF6',          'trend' => '', 'trend_type' => ''],
            ['label' => 'Entradas Hoy',     'value' => 0,    'icon' => 'fas fa-ticket-alt',       'color' => '#EC4899',          'trend' => '', 'trend_type' => ''],
            ['label' => 'Monedero Total',   'value' => '$0', 'icon' => 'fas fa-wallet',           'color' => '#06B6D4',          'trend' => '', 'trend_type' => ''],
        ];

        try {
            // Total canchas activas
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM canchas WHERE tenant_id = ? AND estado = 'ACTIVO'");
            $stmt->execute([$tenantId]);
            $default[0]['value'] = (int) $stmt->fetchColumn();

            // Reservas de hoy
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservas WHERE tenant_id = ? AND fecha_reserva = ? AND estado IN ('CONFIRMADA','PENDIENTE')");
            $stmt->execute([$tenantId, $hoy]);
            $default[1]['value'] = (int) $stmt->fetchColumn();

            // Ingresos reales del mes (pagos completados)
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(rpa_monto), 0) 
                FROM instalaciones_reserva_pagos 
                WHERE rpa_tenant_id = ? AND rpa_fecha >= ? AND rpa_estado = 'COMPLETADO'
            ");
            $stmt->execute([$tenantId, $inicioMes]);
            $ingresosReservas = (float) $stmt->fetchColumn();
            
            // + Ingresos por entradas del mes
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(ent_monto_total), 0)
                FROM instalaciones_entradas
                WHERE ent_tenant_id = ? AND ent_fecha >= ? AND ent_estado = 'ACTIVA'
            ");
            $stmt->execute([$tenantId, $inicioMes]);
            $ingresosEntradas = (float) $stmt->fetchColumn();
            
            $ingresosTotales = $ingresosReservas + $ingresosEntradas;
            $default[2]['value'] = '$' . number_format($ingresosTotales, 2);
            
            // Ingresos mes anterior para tendencia
            $inicioMesAnterior = date('Y-m-01', strtotime('-1 month'));
            $finMesAnterior = date('Y-m-t', strtotime('-1 month'));
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(rpa_monto), 0) 
                FROM instalaciones_reserva_pagos 
                WHERE rpa_tenant_id = ? AND rpa_fecha >= ? AND rpa_fecha <= ? AND rpa_estado = 'COMPLETADO'
            ");
            $stmt->execute([$tenantId, $inicioMesAnterior, $finMesAnterior]);
            $ingresosAnterior = (float) $stmt->fetchColumn();
            if ($ingresosAnterior > 0) {
                $pctCambio = round((($ingresosTotales - $ingresosAnterior) / $ingresosAnterior) * 100);
                $default[2]['trend'] = ($pctCambio >= 0 ? '+' : '') . $pctCambio . '%';
                $default[2]['trend_type'] = $pctCambio >= 0 ? 'up' : 'down';
            }

            // Ocupación hoy
            $totalCanchas = max($default[0]['value'], 1);
            $bloquesEstimados = $totalCanchas * 12;
            $ocupacion = $bloquesEstimados > 0 ? round(($default[1]['value'] / $bloquesEstimados) * 100) : 0;
            $default[3]['value'] = $ocupacion . '%';

            // Entradas vendidas hoy
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(ent_cantidad), 0) 
                FROM instalaciones_entradas 
                WHERE ent_tenant_id = ? AND ent_fecha = ? AND ent_estado = 'ACTIVA'
            ");
            $stmt->execute([$tenantId, $hoy]);
            $default[4]['value'] = (int) $stmt->fetchColumn();

            // Saldo total en monederos activos
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(abo_saldo), 0)
                FROM instalaciones_abonos
                WHERE abo_tenant_id = ? AND abo_estado = 'ACTIVO'
            ");
            $stmt->execute([$tenantId]);
            $saldoMonedero = (float) $stmt->fetchColumn();
            $default[5]['value'] = '$' . number_format($saldoMonedero, 2);
            
        } catch (\Exception $e) {
            error_log("Arena KPI error: " . $e->getMessage());
        }

        return $default;
    }

    private function getCanchasResumen($tenantId) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.cancha_id, c.nombre, c.tipo, c.estado,
                       COALESCE((SELECT COUNT(*) FROM reservas r WHERE r.instalacion_id = c.instalacion_id AND r.fecha_reserva = CURDATE() AND r.estado IN ('CONFIRMADA','PENDIENTE')), 0) as reservas_hoy
                FROM canchas c
                WHERE c.tenant_id = ? AND c.estado = 'ACTIVO'
                ORDER BY c.nombre
                LIMIT 8
            ");
            $stmt->execute([$tenantId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Arena canchas error: " . $e->getMessage());
            return [];
        }
    }

    private function getReservasHoy($tenantId) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.reserva_id, r.hora_inicio, r.hora_fin, r.estado, r.total,
                       c.nombre AS cancha_nombre,
                       CONCAT(cl.cli_nombres, ' ', cl.cli_apellidos) AS cliente_nombre
                FROM reservas r
                LEFT JOIN canchas c ON r.instalacion_id = c.instalacion_id
                LEFT JOIN clientes cl ON r.cliente_id = cl.cli_cliente_id
                WHERE r.tenant_id = ? AND r.fecha_reserva = CURDATE()
                ORDER BY r.hora_inicio ASC
                LIMIT 10
            ");
            $stmt->execute([$tenantId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Arena reservas error: " . $e->getMessage());
            return [];
        }
    }

    private function getChartReservas7d($tenantId) {
        $labels = [];
        $data   = [];
        $dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = $dias[(int) date('w', strtotime($fecha))] . ' ' . date('d', strtotime($fecha));
            try {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservas WHERE tenant_id = ? AND fecha_reserva = ?");
                $stmt->execute([$tenantId, $fecha]);
                $data[] = (int) $stmt->fetchColumn();
            } catch (\Exception $e) {
                $data[] = 0;
            }
        }
        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Últimos pagos recibidos
     */
    private function getUltimosPagos($tenantId) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.rpa_pago_id, p.rpa_monto, p.rpa_metodo_pago, p.rpa_fecha, p.rpa_estado,
                       CONCAT(cl.cli_nombres, ' ', cl.cli_apellidos) AS cliente_nombre,
                       p.rpa_reserva_id
                FROM instalaciones_reserva_pagos p
                LEFT JOIN instalaciones_reservas r ON p.rpa_reserva_id = r.res_reserva_id
                LEFT JOIN clientes cl ON r.res_cliente_id = cl.cli_cliente_id
                WHERE p.rpa_tenant_id = ? AND p.rpa_estado = 'COMPLETADO'
                ORDER BY p.rpa_fecha DESC
                LIMIT 5
            ");
            $stmt->execute([$tenantId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Arena últimos pagos error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Distribución de métodos de pago del mes
     */
    private function getChartMetodosPago($tenantId) {
        $inicioMes = date('Y-m-01');
        $metodos = ['EFECTIVO' => 0, 'TARJETA' => 0, 'TRANSFERENCIA' => 0, 'MONEDERO' => 0];
        
        try {
            $stmt = $this->db->prepare("
                SELECT rpa_metodo_pago, COALESCE(SUM(rpa_monto), 0) as total
                FROM instalaciones_reserva_pagos
                WHERE rpa_tenant_id = ? AND rpa_fecha >= ? AND rpa_estado = 'COMPLETADO'
                GROUP BY rpa_metodo_pago
            ");
            $stmt->execute([$tenantId, $inicioMes]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($rows as $row) {
                $m = $row['rpa_metodo_pago'];
                if (isset($metodos[$m])) {
                    $metodos[$m] = (float)$row['total'];
                }
            }
        } catch (\Exception $e) {
            error_log("Arena chart métodos error: " . $e->getMessage());
        }
        
        return [
            'labels' => array_keys($metodos),
            'data'   => array_values($metodos),
            'colors' => ['#10B981', '#3B82F6', '#8B5CF6', '#F59E0B']
        ];
    }
}
