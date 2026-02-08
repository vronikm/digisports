<?php
/**
 * DigiSports - Controlador Dashboard Principal
 * Panel de control con estadísticas y accesos rápidos
 * 
 * @package DigiSports\Controllers\Core
 * @version 1.0.0
 */

namespace App\Controllers\Core;

require_once BASE_PATH . '/app/controllers/BaseController.php';

class DashboardController extends \BaseController {
    
    /**
     * Dashboard principal
     */
    public function index() {
        // Verificar autenticación
        if (!isAuthenticated()) {
            setFlashMessage('warning', 'Debe iniciar sesión para acceder al dashboard');
            redirect('core', 'auth', 'login');
            return;
        }
        
        // Cargar estadísticas generales (con manejo de errores)
        $stats = $this->getGeneralStats();
        
        // Cargar gráficas
        $charts = $this->getChartsData();
        
        // Cargar actividad reciente
        $recentActivity = $this->getRecentActivity();
        
        // Cargar alertas
        $alerts = $this->getAlerts();
        
        // Cargar módulos del sistema para accesos rápidos
        $this->viewData['modules'] = $this->getModulosSistema();

        $this->viewData['stats'] = $stats;
        $this->viewData['charts'] = $charts;
        $this->viewData['recentActivity'] = $recentActivity;
        $this->viewData['alerts'] = $alerts;
        $this->viewData['layout'] = 'main';
        $this->viewData['title'] = 'Dashboard';
        $this->viewData['pageTitle'] = 'Panel de Control';
        $this->viewData['currentController'] = 'Dashboard';
        
        $this->render('dashboard/index', $this->viewData);

    }

    /**
     * Obtener módulos del sistema activos desde la BD
     */
    private function getModulosSistema(): array {
        try {
            $stmt = $this->db->query("
                SELECT mod_codigo as codigo, 
                       mod_nombre as nombre, 
                       mod_descripcion as descripcion, 
                       mod_icono as icono, 
                       mod_color_fondo as color, 
                       mod_ruta_modulo as ruta_modulo,
                       mod_orden as orden,
                       mod_es_externo as es_externo,
                       mod_requiere_licencia as requiere_licencia
                FROM seguridad_modulos 
                WHERE mod_activo = 1 
                ORDER BY mod_orden ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error cargando módulos sistema: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas generales
     */
    private function getGeneralStats() {
        $stats = [
            'total_usuarios' => 0,
            'total_sedes' => 0,
            'total_instalaciones' => 0,
            'total_clientes' => 0,
            'total_reservas' => 0,
            'ingresos_mes' => 0,
            'reservas_mes' => 0,
            'clientes_mes' => 0,
            'crecimiento_reservas' => 0,
            'crecimiento_ingresos' => 0,
            'reservas_hoy_confirmadas' => 0,
            'reservas_hoy_pendientes' => 0,
            'reservas_hoy_canceladas' => 0
        ];
        
        try {
            // Contar usuarios
            if ($this->tableExists('usuarios')) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE tenant_id = ? AND estado = 'A'");
                $stmt->execute([$this->tenantId]);
                $result = $stmt->fetch();
                $stats['total_usuarios'] = $result['total'] ?? 0;
            }
            
            // Contar sedes
            if ($this->tableExists('sedes')) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM sedes WHERE tenant_id = ? AND estado = 'A'");
                $stmt->execute([$this->tenantId]);
                $result = $stmt->fetch();
                $stats['total_sedes'] = $result['total'] ?? 0;
            }
            
            // Contar instalaciones
            if ($this->tableExists('instalaciones')) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM instalaciones WHERE tenant_id = ? AND estado = 'ACTIVO'");
                $stmt->execute([$this->tenantId]);
                $result = $stmt->fetch();
                $stats['total_instalaciones'] = $result['total'] ?? 0;
            }
            
            // Contar clientes
            if ($this->tableExists('clientes')) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM clientes WHERE tenant_id = ? AND estado = 'A'");
                $stmt->execute([$this->tenantId]);
                $result = $stmt->fetch();
                $stats['total_clientes'] = $result['total'] ?? 0;
                
                // Clientes del mes
                $mesActual = date('Y-m');
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM clientes WHERE tenant_id = ? AND DATE_FORMAT(fecha_registro, '%Y-%m') = ?");
                $stmt->execute([$this->tenantId, $mesActual]);
                $result = $stmt->fetch();
                $stats['clientes_mes'] = $result['total'] ?? 0;
            }
            
            // Contar reservas
            if ($this->tableExists('reservas')) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM reservas WHERE tenant_id = ?");
                $stmt->execute([$this->tenantId]);
                $result = $stmt->fetch();
                $stats['total_reservas'] = $result['total'] ?? 0;
                
                // Reservas del mes
                $mesActual = date('Y-m');
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM reservas WHERE tenant_id = ? AND DATE_FORMAT(fecha_reserva, '%Y-%m') = ?");
                $stmt->execute([$this->tenantId, $mesActual]);
                $result = $stmt->fetch();
                $stats['reservas_mes'] = $result['total'] ?? 0;
                
                // Reservas de hoy por estado
                $stmt = $this->db->prepare("
                    SELECT estado, COUNT(*) as total 
                    FROM reservas 
                    WHERE tenant_id = ? AND fecha_reserva = CURDATE()
                    GROUP BY estado
                ");
                $stmt->execute([$this->tenantId]);
                $reservasHoy = $stmt->fetchAll();
                
                foreach ($reservasHoy as $r) {
                    if ($r['estado'] === 'CONFIRMADA') $stats['reservas_hoy_confirmadas'] = $r['total'];
                    if ($r['estado'] === 'PENDIENTE') $stats['reservas_hoy_pendientes'] = $r['total'];
                    if ($r['estado'] === 'CANCELADA') $stats['reservas_hoy_canceladas'] = $r['total'];
                }
            }
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener estadísticas: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Verificar si una tabla existe
     */
    private function tableExists($tableName) {
        try {
            $stmt = $this->db->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$tableName]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Calcular porcentaje de crecimiento
     */
    private function calcularCrecimiento($actual, $anterior) {
        if ($anterior == 0) {
            return $actual > 0 ? 100 : 0;
        }
        
        return round((($actual - $anterior) / $anterior) * 100, 1);
    }
    
    /**
     * Obtener datos para gráficas
     */
    private function getChartsData() {
        $charts = [
            'ingresos_mensuales' => [],
            'reservas_instalacion' => [],
            'reservas_estado' => []
        ];
        
        try {
            // Solo intentar si las tablas existen
            if ($this->tableExists('reservas')) {
                // Estados de reservas mes actual
                $stmt = $this->db->prepare("
                    SELECT estado, COUNT(*) as total
                    FROM reservas
                    WHERE tenant_id = ?
                    AND DATE_FORMAT(fecha_reserva, '%Y-%m') = ?
                    GROUP BY estado
                ");
                $stmt->execute([$this->tenantId, date('Y-m')]);
                $charts['reservas_estado'] = $stmt->fetchAll();
                
                // Verificar si existe la tabla de pagos
                if ($this->tableExists('reserva_pagos')) {
                    // Ingresos últimos 12 meses
                    $stmt = $this->db->prepare("
                        SELECT 
                            DATE_FORMAT(r.fecha_reserva, '%Y-%m') as mes,
                            DATE_FORMAT(r.fecha_reserva, '%b %Y') as mes_label,
                            COALESCE(SUM(rp.monto), 0) as ingresos
                        FROM reservas r
                        LEFT JOIN reserva_pagos rp ON r.reserva_id = rp.reserva_id AND rp.estado = 'COMPLETADO'
                        WHERE r.tenant_id = ?
                        AND r.fecha_reserva >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                        GROUP BY DATE_FORMAT(r.fecha_reserva, '%Y-%m')
                        ORDER BY mes ASC
                    ");
                    $stmt->execute([$this->tenantId]);
                    $charts['ingresos_mensuales'] = $stmt->fetchAll();
                }
                
                // Verificar si existe la tabla de instalaciones
                if ($this->tableExists('instalaciones')) {
                    // Reservas por instalación (top 5)
                    $stmt = $this->db->prepare("
                        SELECT 
                            i.nombre,
                            COUNT(*) as total_reservas
                        FROM reservas r
                        INNER JOIN instalaciones i ON r.instalacion_id = i.instalacion_id
                        WHERE r.tenant_id = ?
                        AND r.fecha_reserva >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                        GROUP BY i.instalacion_id
                        ORDER BY total_reservas DESC
                        LIMIT 5
                    ");
                    $stmt->execute([$this->tenantId]);
                    $charts['reservas_instalacion'] = $stmt->fetchAll();
                }
            }
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener datos de gráficas: " . $e->getMessage());
        }
        
        return $charts;
    }
    
    /**
     * Obtener actividad reciente
     */
    private function getRecentActivity($limit = 10) {
        $activity = [];
        
        try {
            if ($this->tableExists('auditoria')) {
                $stmt = $this->db->prepare("
                    SELECT 
                        a.operacion,
                        a.tabla,
                        a.fecha_operacion as fecha,
                        CONCAT(u.nombres, ' ', u.apellidos) as usuario,
                        CASE a.operacion
                            WHEN 'INSERT' THEN 'fas fa-plus-circle'
                            WHEN 'UPDATE' THEN 'fas fa-edit'
                            WHEN 'DELETE' THEN 'fas fa-trash'
                            WHEN 'LOGIN' THEN 'fas fa-sign-in-alt'
                            WHEN 'LOGOUT' THEN 'fas fa-sign-out-alt'
                            ELSE 'fas fa-circle'
                        END as icono,
                        CASE a.operacion
                            WHEN 'INSERT' THEN '#28a745'
                            WHEN 'UPDATE' THEN '#ffc107'
                            WHEN 'DELETE' THEN '#dc3545'
                            WHEN 'LOGIN' THEN '#17a2b8'
                            WHEN 'LOGOUT' THEN '#6c757d'
                            ELSE '#6c757d'
                        END as color
                    FROM auditoria a
                    LEFT JOIN usuarios u ON a.usuario_id = u.usuario_id
                    WHERE a.tenant_id = ?
                    ORDER BY a.fecha_operacion DESC
                    LIMIT ?
                ");
                $stmt->execute([$this->tenantId, $limit]);
                $rows = $stmt->fetchAll();
                
                foreach ($rows as $row) {
                    $activity[] = [
                        'descripcion' => $this->formatActivityDescription($row),
                        'fecha' => $row['fecha'],
                        'icono' => $row['icono'],
                        'color' => $row['color']
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logError("Error al obtener actividad reciente: " . $e->getMessage());
        }
        
        return $activity;
    }
    
    /**
     * Formatear descripción de actividad
     */
    private function formatActivityDescription($row) {
        $operaciones = [
            'INSERT' => 'creó',
            'UPDATE' => 'actualizó',
            'DELETE' => 'eliminó',
            'LOGIN' => 'inició sesión',
            'LOGOUT' => 'cerró sesión'
        ];
        
        $tablas = [
            'usuarios' => 'usuario',
            'clientes' => 'cliente',
            'reservas' => 'reserva',
            'instalaciones' => 'instalación',
            'pagos' => 'pago'
        ];
        
        $operacion = $operaciones[$row['operacion']] ?? $row['operacion'];
        $tabla = $tablas[$row['tabla']] ?? $row['tabla'];
        $usuario = $row['usuario'] ?? 'Sistema';
        
        if ($row['operacion'] === 'LOGIN' || $row['operacion'] === 'LOGOUT') {
            return "{$usuario} {$operacion}";
        }
        
        return "{$usuario} {$operacion} un {$tabla}";
    }
    
    /**
     * Obtener alertas del sistema
     */
    private function getAlerts() {
        $alerts = [];
        
        try {
            // Verificar suscripción próxima a vencer
            $tenant = $this->viewData['tenant'] ?? null;
            
            if ($tenant && !empty($tenant['fecha_vencimiento'])) {
                $diasRestantes = (strtotime($tenant['fecha_vencimiento']) - time()) / 86400;
                
                if ($diasRestantes <= 7 && $diasRestantes > 0) {
                    $alerts[] = [
                        'type' => 'warning',
                        'icon' => 'fa-exclamation-triangle',
                        'message' => "Su suscripción vence en " . ceil($diasRestantes) . " días.",
                        'url' => url('core', 'suscripcion', 'renovar')
                    ];
                }
            }
            
            // Verificar instalaciones bloqueadas hoy
            if ($this->tableExists('instalacion_bloqueos') && $this->tableExists('instalaciones')) {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total
                    FROM instalacion_bloqueos ib
                    INNER JOIN instalaciones i ON ib.instalacion_id = i.instalacion_id
                    WHERE i.tenant_id = ?
                    AND CURDATE() BETWEEN DATE(ib.fecha_inicio) AND DATE(ib.fecha_fin)
                ");
                $stmt->execute([$this->tenantId]);
                $bloqueosHoy = $stmt->fetch();
                
                if ($bloqueosHoy['total'] > 0) {
                    $alerts[] = [
                        'type' => 'info',
                        'icon' => 'fa-tools',
                        'message' => "Hay {$bloqueosHoy['total']} instalación(es) en mantenimiento hoy.",
                        'url' => url('instalaciones', 'mantenimiento')
                    ];
                }
            }
            
            // Verificar reservas pendientes de confirmación
            if ($this->tableExists('reservas')) {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total
                    FROM reservas
                    WHERE tenant_id = ?
                    AND estado = 'PENDIENTE'
                    AND requiere_confirmacion = 'S'
                ");
                $stmt->execute([$this->tenantId]);
                $reservasPendientes = $stmt->fetch();
                
                if ($reservasPendientes['total'] > 0) {
                    $alerts[] = [
                        'type' => 'warning',
                        'icon' => 'fa-clock',
                        'message' => "Tienes {$reservasPendientes['total']} reserva(s) por confirmar.",
                        'url' => url('reservas', 'reserva')
                    ];
                }
            }
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener alertas: " . $e->getMessage());
        }
        
        return $alerts;
    }
    
    /**
     * Obtener datos del widget de reservas del día (AJAX)
     */
    public function reservasHoy() {
        if (!$this->isAjax()) {
            $this->error('Solicitud inválida');
        }
        
        try {
            if (!$this->tableExists('reservas')) {
                $this->success([]);
                return;
            }
            
            $stmt = $this->db->prepare("
                SELECT 
                    r.*,
                    i.nombre as instalacion,
                    i.foto_principal,
                    c.nombres as cliente_nombres,
                    c.apellidos as cliente_apellidos
                FROM reservas r
                INNER JOIN instalaciones i ON r.instalacion_id = i.instalacion_id
                INNER JOIN clientes c ON r.cliente_id = c.cliente_id
                WHERE r.tenant_id = ?
                AND r.fecha_reserva = CURDATE()
                ORDER BY r.hora_inicio ASC
            ");
            
            $stmt->execute([$this->tenantId]);
            $reservas = $stmt->fetchAll();
            
            $this->success($reservas);
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener reservas del día: " . $e->getMessage());
            $this->error('Error al cargar reservas');
        }
    }
    
    /**
     * Obtener estadísticas en tiempo real (AJAX)
     */
    public function statsRealTime() {
        if (!$this->isAjax()) {
            $this->error('Solicitud inválida');
        }
        
        try {
            $stats = $this->getGeneralStats();
            $this->success($stats);
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener stats: " . $e->getMessage());
            $this->error('Error al cargar estadísticas');
        }
    }
}
