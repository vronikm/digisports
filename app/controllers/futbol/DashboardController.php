<?php
/**
 * DigiSports Fútbol - Controlador Dashboard
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DashboardController extends \App\Controllers\ModuleController {
    
    protected $moduloCodigo = 'FUTBOL';
    protected $moduloNombre = 'DigiSports Fútbol';
    protected $moduloIcono = 'fas fa-futbol';
    protected $moduloColor = '#22C55E';
    
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'FUTBOL';
    }
    
    /**
     * Dashboard principal de Fútbol
     */
    public function index() {
        $this->setupModule();
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        // KPIs específicos de fútbol
        $kpis = $this->getKPIs($tenantId);
        
        // Datos para gráficos
        $chartReservas = $this->getChartData('reservas', 7, 'fecha');
        $chartIngresos = $this->getIngresosChart($tenantId);
        
        // Próximas reservas
        $proximasReservas = $this->getProximasReservas($tenantId);
        
        // Canchas más populares
        $canchasPopulares = $this->getCanchasPopulares($tenantId);
        
        // Torneos activos
        $torneosActivos = $this->getTorneosActivos($tenantId);
        
        $this->viewData['title'] = $this->moduloNombre;
        $this->viewData['kpis'] = $kpis;
        $this->viewData['chart_reservas'] = $chartReservas;
        $this->viewData['chart_ingresos'] = $chartIngresos;
        $this->viewData['proximas_reservas'] = $proximasReservas;
        $this->viewData['canchas_populares'] = $canchasPopulares;
        $this->viewData['torneos_activos'] = $torneosActivos;
        
        $this->renderModule('futbol/dashboard/index');
    }
    
    /**
     * Obtener KPIs de fútbol
     */
    private function getKPIs($tenantId) {
        $hoy = date('Y-m-d');
        $inicioMes = date('Y-m-01');
        
        // Reservas hoy
        try {
            $reservasHoy = $this->db->prepare("
                SELECT COUNT(*) FROM reservas r
                WHERE r.tenant_id = ? AND r.fecha_reserva = ? AND r.estado IN ('CONFIRMADA', 'PENDIENTE')
            ");
            $reservasHoy->execute([$tenantId, $hoy]);
            $reservasHoyVal = $reservasHoy->fetchColumn() ?: 0;
        } catch (\Exception $e) {
            $reservasHoyVal = 0;
        }
        
        // Instalaciones (canchas) activas
        try {
            $canchas = $this->db->prepare("
                SELECT COUNT(*) FROM instalaciones WHERE tenant_id = ? AND estado = 'A'
            ");
            $canchas->execute([$tenantId]);
            $canchasVal = $canchas->fetchColumn() ?: 0;
        } catch (\Exception $e) {
            $canchasVal = 0;
        }
        
        // Ingresos del mes
        try {
            $ingresos = $this->db->prepare("
                SELECT COALESCE(SUM(precio_total), 0) FROM reservas 
                WHERE tenant_id = ? AND fecha_reserva >= ? AND estado = 'COMPLETADA'
            ");
            $ingresos->execute([$tenantId, $inicioMes]);
            $ingresosVal = $ingresos->fetchColumn() ?: 0;
        } catch (\Exception $e) {
            $ingresosVal = 0;
        }
        
        // Clientes activos (con reservas este mes)
        try {
            $clientes = $this->db->prepare("
                SELECT COUNT(DISTINCT cliente_id) FROM reservas 
                WHERE tenant_id = ? AND fecha_reserva >= ?
            ");
            $clientes->execute([$tenantId, $inicioMes]);
            $clientesVal = $clientes->fetchColumn() ?: 0;
        } catch (\Exception $e) {
            $clientesVal = 0;
        }
        
        // Ocupación promedio
        $ocupacion = $this->calcularOcupacion($tenantId);
        
        // Escuelas activas (puede no existir la tabla)
        try {
            $escuelas = $this->db->prepare("
                SELECT COUNT(*) FROM escuelas WHERE tenant_id = ? AND estado = 'A'
            ");
            $escuelas->execute([$tenantId]);
            $escuelasVal = $escuelas->fetchColumn() ?: 0;
        } catch (\Exception $e) {
            $escuelasVal = 0;
        }
        
        return [
            [
                'label' => 'Reservas Hoy',
                'value' => $reservasHoyVal,
                'icon' => 'fas fa-calendar-check',
                'color' => '#22C55E',
                'trend' => '+12%',
                'trend_type' => 'up'
            ],
            [
                'label' => 'Canchas Activas',
                'value' => $canchasVal,
                'icon' => 'fas fa-futbol',
                'color' => '#3B82F6',
                'trend' => null,
                'trend_type' => null
            ],
            [
                'label' => 'Ingresos del Mes',
                'value' => '$' . number_format($ingresosVal, 2),
                'icon' => 'fas fa-dollar-sign',
                'color' => '#F59E0B',
                'trend' => '+8%',
                'trend_type' => 'up'
            ],
            [
                'label' => 'Clientes Activos',
                'value' => $clientesVal,
                'icon' => 'fas fa-users',
                'color' => '#8B5CF6',
                'trend' => '+5%',
                'trend_type' => 'up'
            ],
            [
                'label' => 'Ocupación',
                'value' => $ocupacion . '%',
                'icon' => 'fas fa-chart-pie',
                'color' => '#EC4899',
                'trend' => null,
                'trend_type' => null
            ],
            [
                'label' => 'Escuelas',
                'value' => $escuelasVal,
                'icon' => 'fas fa-graduation-cap',
                'color' => '#14B8A6',
                'trend' => null,
                'trend_type' => null
            ]
        ];
    }
    
    /**
     * Calcular ocupación promedio
     */
    private function calcularOcupacion($tenantId) {
        // Simplificado: porcentaje de horas reservadas vs disponibles hoy
        try {
            $hoy = date('Y-m-d');
            $reservas = $this->db->prepare("
                SELECT COUNT(*) * 1.0 FROM reservas 
                WHERE tenant_id = ? AND fecha_reserva = ? AND estado IN ('CONFIRMADA', 'COMPLETADA')
            ");
            $reservas->execute([$tenantId, $hoy]);
            $horasReservadas = $reservas->fetchColumn() ?: 0;
            
            $instalaciones = $this->db->prepare("SELECT COUNT(*) FROM instalaciones WHERE tenant_id = ? AND estado = 'A'");
            $instalaciones->execute([$tenantId]);
            $totalInstalaciones = $instalaciones->fetchColumn() ?: 1;
            
            // Asumiendo 12 horas operativas por instalación
            $horasDisponibles = $totalInstalaciones * 12;
            
            return $horasDisponibles > 0 ? round(($horasReservadas / $horasDisponibles) * 100) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Obtener gráfico de ingresos
     */
    private function getIngresosChart($tenantId) {
        $labels = [];
        $valores = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('D', strtotime($fecha));
            
            try {
                $stmt = $this->db->prepare("
                    SELECT COALESCE(SUM(precio_total), 0) FROM reservas 
                    WHERE tenant_id = ? AND fecha_reserva = ? AND estado = 'COMPLETADA'
                ");
                $stmt->execute([$tenantId, $fecha]);
                $valores[] = (float)$stmt->fetchColumn();
            } catch (\Exception $e) {
                $valores[] = 0;
            }
        }
        
        return ['labels' => $labels, 'data' => $valores];
    }
    
    /**
     * Obtener próximas reservas
     */
    private function getProximasReservas($tenantId, $limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, i.nombre as cancha_nombre, 
                       CONCAT(cl.nombres, ' ', cl.apellidos) as cliente_nombre
                FROM reservas r
                LEFT JOIN instalaciones i ON r.instalacion_id = i.instalacion_id
                LEFT JOIN clientes cl ON r.cliente_id = cl.cliente_id
                WHERE r.tenant_id = ? 
                AND r.fecha_reserva >= CURDATE()
                AND r.estado IN ('CONFIRMADA', 'PENDIENTE')
                ORDER BY r.fecha_reserva ASC, r.hora_inicio ASC
                LIMIT ?
            ");
            $stmt->execute([$tenantId, $limit]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener canchas más populares
     */
    private function getCanchasPopulares($tenantId, $limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT i.nombre, i.tipo_instalacion as tipo, COUNT(r.reserva_id) as total_reservas,
                       COALESCE(SUM(r.precio_total), 0) as ingresos
                FROM instalaciones i
                LEFT JOIN reservas r ON i.instalacion_id = r.instalacion_id 
                    AND MONTH(r.fecha_reserva) = MONTH(CURDATE())
                WHERE i.tenant_id = ? AND i.estado = 'A'
                GROUP BY i.instalacion_id
                ORDER BY total_reservas DESC
                LIMIT ?
            ");
            $stmt->execute([$tenantId, $limit]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener torneos activos
     */
    private function getTorneosActivos($tenantId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM torneos 
                WHERE tenant_id = ? AND estado = 'ACTIVO'
                ORDER BY fecha_inicio ASC
                LIMIT 3
            ");
            $stmt->execute([$tenantId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
}
