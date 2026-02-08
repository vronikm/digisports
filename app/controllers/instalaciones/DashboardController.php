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

        $this->renderModule('instalaciones/dashboard/index', [
            'kpis'           => $kpis,
            'canchas'        => $canchas,
            'reservas_hoy'   => $reservasHoy,
            'chart_reservas' => $chartReservas,
            'pageTitle'      => 'DigiSports Arena',
        ]);
    }

    /* ─────────── helpers privados ─────────── */

    private function getKPIs($tenantId) {
        $hoy = date('Y-m-d');
        $inicioMes = date('Y-m-01');

        $default = [
            ['label' => 'Canchas',          'value' => 0, 'icon' => 'fas fa-futbol',           'color' => $this->moduloColor, 'trend' => '', 'trend_type' => ''],
            ['label' => 'Reservas Hoy',     'value' => 0, 'icon' => 'fas fa-calendar-day',     'color' => '#10B981',          'trend' => '', 'trend_type' => ''],
            ['label' => 'Ingresos Mes',     'value' => '$0', 'icon' => 'fas fa-dollar-sign',   'color' => '#F59E0B',          'trend' => '', 'trend_type' => ''],
            ['label' => 'Ocupación Hoy',    'value' => '0%', 'icon' => 'fas fa-chart-pie',     'color' => '#8B5CF6',          'trend' => '', 'trend_type' => ''],
            ['label' => 'Mantenimientos',   'value' => 0, 'icon' => 'fas fa-tools',            'color' => '#EF4444',          'trend' => '', 'trend_type' => ''],
            ['label' => 'Clientes Mes',     'value' => 0, 'icon' => 'fas fa-users',            'color' => '#06B6D4',          'trend' => '', 'trend_type' => ''],
        ];

        try {
            // Total canchas activas
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM instalaciones WHERE tenant_id = ? AND estado = 'A'");
            $stmt->execute([$tenantId]);
            $default[0]['value'] = (int) $stmt->fetchColumn();

            // Reservas de hoy
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservas WHERE tenant_id = ? AND fecha_reserva = ? AND estado IN ('CONFIRMADA','PENDIENTE')");
            $stmt->execute([$tenantId, $hoy]);
            $default[1]['value'] = (int) $stmt->fetchColumn();

            // Ingresos del mes
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(total), 0) FROM reservas WHERE tenant_id = ? AND fecha_reserva >= ? AND estado = 'CONFIRMADA'");
            $stmt->execute([$tenantId, $inicioMes]);
            $ingresos = (float) $stmt->fetchColumn();
            $default[2]['value'] = '$' . number_format($ingresos, 2);

            // Ocupación hoy  (reservas hoy / (canchas * bloques))
            $totalCanchas = max($default[0]['value'], 1);
            $bloquesEstimados = $totalCanchas * 12; // ~12 bloques de 1h por cancha
            $ocupacion = $bloquesEstimados > 0 ? round(($default[1]['value'] / $bloquesEstimados) * 100) : 0;
            $default[3]['value'] = $ocupacion . '%';

            // Mantenimientos pendientes
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM mantenimientos WHERE tenant_id = ? AND estado = 'PENDIENTE'");
            $stmt->execute([$tenantId]);
            $default[4]['value'] = (int) $stmt->fetchColumn();

            // Clientes únicos del mes
            $stmt = $this->db->prepare("SELECT COUNT(DISTINCT cliente_id) FROM reservas WHERE tenant_id = ? AND fecha_reserva >= ?");
            $stmt->execute([$tenantId, $inicioMes]);
            $default[5]['value'] = (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("Arena KPI error: " . $e->getMessage());
        }

        return $default;
    }

    private function getCanchasResumen($tenantId) {
        try {
            $stmt = $this->db->prepare("
                SELECT i.instalacion_id, i.nombre, i.tipo_instalacion, i.precio_hora, i.estado,
                       COALESCE((SELECT COUNT(*) FROM reservas r WHERE r.instalacion_id = i.instalacion_id AND r.fecha_reserva = CURDATE() AND r.estado IN ('CONFIRMADA','PENDIENTE')), 0) as reservas_hoy
                FROM instalaciones i
                WHERE i.tenant_id = ? AND i.estado = 'A'
                ORDER BY i.nombre
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
                       i.nombre AS cancha_nombre,
                       COALESCE(c.nombres, c.razon_social, 'Cliente') AS cliente_nombre
                FROM reservas r
                LEFT JOIN instalaciones i ON r.instalacion_id = i.instalacion_id
                LEFT JOIN clientes c ON r.cliente_id = c.cliente_id
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
}
