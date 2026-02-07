<?php
/**
 * DigiSports - Módulo Seguridad
 * Dashboard Controller
 * 
 * Panel principal de administración del sistema
 * 
 * @package DigiSports\Security
 * @version 1.0.0
 */


namespace App\Controllers\Seguridad;

require_once BASE_PATH . '/app/controllers/ModuleController.php';
require_once BASE_PATH . '/app/helpers/functions.php';


class DashboardController extends \App\Controllers\ModuleController {
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'seguridad';
        $this->moduloNombre = 'Seguridad';
        $this->moduloIcono = 'fas fa-shield-alt';
        $this->moduloColor = '#F59E0B';
    }

    /**
     * Visualización de logs de auditoría
     */
    public function auditoria() {
        $filtros = [
            'usuario_id' => $_GET['usuario_id'] ?? null,
            'tenant_id' => $_GET['tenant_id'] ?? ($_SESSION['tenant_id'] ?? null),
            'accion' => $_GET['accion'] ?? null,
            'entidad' => $_GET['entidad'] ?? null,
            'fecha_desde' => $_GET['fecha_desde'] ?? null,
            'fecha_hasta' => $_GET['fecha_hasta'] ?? null
        ];
        $where = [];
        $params = [];
        if ($filtros['usuario_id']) { $where[] = 'a.aud_usr_id = ?'; $params[] = $filtros['usuario_id']; }
        if ($filtros['tenant_id']) { $where[] = 'a.aud_ten_id = ?'; $params[] = $filtros['tenant_id']; }
        if ($filtros['accion']) { $where[] = 'a.aud_accion = ?'; $params[] = $filtros['accion']; }
        if ($filtros['entidad']) { $where[] = 'a.aud_entidad = ?'; $params[] = $filtros['entidad']; }
        if ($filtros['fecha_desde']) { $where[] = 'a.aud_fecha >= ?'; $params[] = $filtros['fecha_desde']; }
        if ($filtros['fecha_hasta']) { $where[] = 'a.aud_fecha <= ?'; $params[] = $filtros['fecha_hasta']; }
        $sql = "SELECT a.*, u.usr_nombres, u.usr_apellidos, t.ten_nombre_comercial FROM seguridad_auditoria_acciones a
            LEFT JOIN seguridad_usuarios u ON a.aud_usr_id = u.usr_id
            LEFT JOIN core_tenants t ON a.aud_ten_id = t.ten_id
            " . (count($where) ? "WHERE " . implode(' AND ', $where) : '') . "
            ORDER BY a.aud_fecha DESC LIMIT 100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->renderModule('seguridad/dashboard/auditoria', [
            'logs' => $logs,
            'filtros' => $filtros,
            'pageTitle' => 'Auditoría de Seguridad'
        ]);
    }
    
    /**
     * Dashboard principal de seguridad
     */
    public function index() {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userRol = $_SESSION['rol_codigo'] ?? '';
        
        // Solo superadmin puede acceder (VALIDACIÓN DESACTIVADA PARA PRUEBA DE LAYOUT)
        // if ($userRol !== 'SUPERADMIN') {
        //     redirect('core', 'dashboard', 'index');
        //     return;
        // }
        
        // Obtener estadísticas
        $stats = $this->getSystemStats();
        $kpis = $this->getKPIs();
        $recentActivity = $this->getRecentActivity();
        $securityAlerts = $this->getSecurityAlerts();
        
        // Forzar menú de seguridad manualmente
        $this->viewData['menu_items'] = $this->getMenuItems();
        $this->renderModule('seguridad/dashboard/index', [
            'stats' => $stats,
            'kpis' => $kpis,
            'recentActivity' => $recentActivity,
            'securityAlerts' => $securityAlerts,
            'pageTitle' => 'Panel de Seguridad'
        ]);
    }
    
    /**
     * Obtener estadísticas del sistema
     */
    private function getSystemStats() {
        $stats = [];
        
        try {
            // Total tenants
            $stmt = $this->db->query("SELECT COUNT(*) FROM tenants WHERE estado = 'A'");
            $stmt = $this->db->query("SELECT COUNT(*) FROM seguridad_tenants WHERE ten_estado = 'A'");
            $stats['tenants_activos'] = $stmt->fetchColumn();
            
            // Total usuarios
            $stmt = $this->db->query("SELECT COUNT(*) FROM usuarios WHERE estado = 'A'");
            $stmt = $this->db->query("SELECT COUNT(*) FROM seguridad_usuarios WHERE usr_estado = 'A'");
            $stats['usuarios_activos'] = $stmt->fetchColumn();
            
            // Total módulos
            $stmt = $this->db->query("SELECT COUNT(*) FROM modulos_sistema WHERE estado = 'A'");
            $stmt = $this->db->query("SELECT COUNT(*) FROM seguridad_modulos_sistema WHERE sis_estado = 'A'");
            $stats['modulos_activos'] = $stmt->fetchColumn();
            
            // Usuarios online (último login < 30 min)
            $stmt = $this->db->query("SELECT COUNT(*) FROM usuarios WHERE ultimo_login >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
            $stmt = $this->db->query("SELECT COUNT(*) FROM seguridad_usuarios WHERE usr_ultimo_login >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
            $stats['usuarios_online'] = $stmt->fetchColumn();
            
            // Intentos de login fallidos hoy
            $stmt = $this->db->query("SELECT COUNT(*) FROM log_accesos WHERE fecha >= CURDATE() AND tipo = 'LOGIN_FAILED'");
            $stmt = $this->db->query("SELECT COUNT(*) FROM seguridad_log_accesos WHERE acc_fecha >= CURDATE() AND acc_tipo = 'LOGIN_FAILED'");
            $stats['login_fallidos_hoy'] = $stmt->fetchColumn() ?: 0;
            
        } catch (\Exception $e) {
            $stats = [
                'tenants_activos' => 0,
                'usuarios_activos' => 0,
                'modulos_activos' => 0,
                'usuarios_online' => 0,
                'login_fallidos_hoy' => 0
            ];
        }
        
        return $stats;
    }
    
    /**
     * Obtener KPIs del módulo
     */
    private function getKPIs() {
        try {
            // Tenants activos
            $stmt = $this->db->query("SELECT COUNT(*) FROM tenants WHERE estado = 'A'");
            $stmt = $this->db->query("SELECT COUNT(*) FROM seguridad_tenants WHERE ten_estado = 'A'");
            $tenants = $stmt->fetchColumn();
            // Usuarios totales
            $stmt = $this->db->query("SELECT COUNT(*) FROM usuarios WHERE estado = 'A'");
            $stmt = $this->db->query("SELECT COUNT(*) FROM seguridad_usuarios WHERE usr_estado = 'A'");
            $usuarios = $stmt->fetchColumn();
            // Módulos del sistema
            $stmt = $this->db->query("SELECT COUNT(*) FROM modulos_sistema WHERE estado = 'A'");
            $stmt = $this->db->query("SELECT COUNT(*) FROM seguridad_modulos_sistema WHERE sis_estado = 'A'");
            $modulos = $stmt->fetchColumn();
            // Roles definidos
            $stmt = $this->db->query("SELECT COUNT(*) FROM roles WHERE estado = 'A'");
            $stmt = $this->db->query("SELECT COUNT(*) FROM seguridad_roles WHERE rol_estado = 'A'");
            $roles = $stmt->fetchColumn();
            // Suscripciones por vencer (30 días)
            $stmt = $this->db->query("SELECT COUNT(*) FROM tenants WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
            $porVencer = $stmt->fetchColumn();
            // Logs de hoy
            $stmt = $this->db->query("SELECT COUNT(*) FROM log_accesos WHERE fecha >= CURDATE()");
            $logsHoy = $stmt->fetchColumn() ?: 0;
            // Logins fallidos hoy
            $stmt = $this->db->query("SELECT COUNT(*) FROM log_accesos WHERE fecha >= CURDATE() AND tipo = 'LOGIN_FAILED'");
            $loginFallidosHoy = $stmt->fetchColumn() ?: 0;
        } catch (\Exception $e) {
            $tenants = $usuarios = $modulos = $roles = $porVencer = $logsHoy = $loginFallidosHoy = 0;
        }
        return [
            [
                'label' => 'Tenants Activos',
                'value' => $tenants,
                'icon' => 'fas fa-building',
                'color' => '#22C55E',
                'trend' => null,
                'trend_type' => null
            ],
            [
                'label' => 'Usuarios',
                'value' => $usuarios,
                'icon' => 'fas fa-users',
                'color' => '#3B82F6',
                'trend' => null,
                'trend_type' => null
            ],
            [
                'label' => 'Módulos',
                'value' => $modulos,
                'icon' => 'fas fa-puzzle-piece',
                'color' => '#8B5CF6',
                'trend' => null,
                'trend_type' => null
            ],
            [
                'label' => 'Roles',
                'value' => $roles,
                'icon' => 'fas fa-user-shield',
                'color' => '#F59E0B',
                'trend' => null,
                'trend_type' => null
            ],
            [
                'label' => 'Por Vencer',
                'value' => $porVencer,
                'icon' => 'fas fa-exclamation-triangle',
                'color' => '#EF4444',
                'trend' => null,
                'trend_type' => null
            ],
            [
                'label' => 'Logs Hoy',
                'value' => $logsHoy,
                'icon' => 'fas fa-history',
                'color' => '#06B6D4',
                'trend' => null,
                'trend_type' => null
            ],
            [
                'label' => 'Logins Fallidos Hoy',
                'value' => $loginFallidosHoy,
                'icon' => 'fas fa-user-lock',
                'color' => '#EF4444',
                'trend' => null,
                'trend_type' => null
            ],
        ];
    }
    
    /**
     * Obtener actividad reciente
     */
    private function getRecentActivity() {
        try {
            $stmt = $this->db->query("
                SELECT l.*, u.nombres, u.apellidos, u.username
                FROM log_accesos l
                LEFT JOIN usuarios u ON l.usuario_id = u.usuario_id
                ORDER BY l.fecha DESC
                LIMIT 10
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener alertas de seguridad
     */
    private function getSecurityAlerts() {
        $alerts = [];
        
        try {
            // Usuarios con muchos intentos fallidos
            $stmt = $this->db->query("SELECT COUNT(*) FROM usuarios WHERE intentos_fallidos >= 3");
            $bloqueados = $stmt->fetchColumn();
            if ($bloqueados > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'fas fa-user-lock',
                    'message' => "$bloqueados usuario(s) con intentos fallidos"
                ];
            }
            
            // Suscripciones vencidas
            $stmt = $this->db->query("SELECT COUNT(*) FROM tenants WHERE fecha_vencimiento < CURDATE() AND estado = 'A'");
            $vencidas = $stmt->fetchColumn();
            if ($vencidas > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'icon' => 'fas fa-calendar-times',
                    'message' => "$vencidas suscripción(es) vencida(s)"
                ];
            }
            
            // Certificados SRI por vencer
            $stmt = $this->db->query("
                SELECT COUNT(*) FROM tenants 
                WHERE estado = 'A' 
                AND tenant_id IN (SELECT tenant_id FROM configuracion_sri WHERE certificado_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY))
            ");
            $certVencer = $stmt->fetchColumn() ?: 0;
            if ($certVencer > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'fas fa-certificate',
                    'message' => "$certVencer certificado(s) por vencer"
                ];
            }
            
        } catch (\Exception $e) {
            // Ignorar errores si las tablas no existen
        }
        
        if (empty($alerts)) {
            $alerts[] = [
                'type' => 'success',
                'icon' => 'fas fa-check-circle',
                'message' => 'Sin alertas de seguridad'
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Obtener items del menú
     */
    public function getMenuItems() {
        // Detectar controlador y acción actual compatible con URLs encriptadas
        $controller = '';
        $action = '';
        if (isset($_GET['r'])) {
            $data = null;
            if (class_exists('Security') && method_exists('Security', 'decodeSecureUrl')) {
                $data = \Security::decodeSecureUrl($_GET['r']);
            } else if (function_exists('decodeSecureUrl')) {
                $data = decodeSecureUrl($_GET['r']);
            }
            if (is_array($data)) {
                $controller = isset($data['c']) ? strtolower($data['c']) : '';
                $action = isset($data['a']) ? strtolower($data['a']) : '';
            }
        } else {
            $controller = isset($_GET['controller']) ? strtolower($_GET['controller']) : (isset($_GET['c']) ? strtolower($_GET['c']) : '');
            $action = isset($_GET['action']) ? strtolower($_GET['action']) : (isset($_GET['a']) ? strtolower($_GET['a']) : '');
        }
        // Forzar activo en 'Lista de Tenants' si acción es 'ver' o 'editar'
        $isTenantListActive = ($controller === 'tenant' && in_array($action, ['index', 'ver', 'editar']));
        $menu = [
            [ 'header' => 'Principal' ],
            [ 'items' => [
                [ 'label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => url('seguridad', 'dashboard', 'index'), 'active' => ($controller === 'dashboard' && $action === 'index') ]
            ]],
            [ 'header' => 'Administración' ],
            [ 'items' => [
                [ 'label' => 'Tenants', 'icon' => 'fas fa-building', 'url' => '#', 'submenu' => [
                    [ 'label' => 'Lista de Tenants', 'url' => url('seguridad', 'tenant', 'index'), 'active' => $isTenantListActive ],
                    [ 'label' => 'Nuevo Tenant', 'url' => url('seguridad', 'tenant', 'crear'), 'active' => ($controller === 'tenant' && $action === 'crear') ],
                    [ 'label' => 'Suspender Tenant', 'url' => url('seguridad', 'tenant', 'suspender'), 'active' => ($controller === 'tenant' && $action === 'suspender') ],
                    [ 'label' => 'Reactivar Tenant', 'url' => url('seguridad', 'tenant', 'reactivar'), 'active' => ($controller === 'tenant' && $action === 'reactivar') ],
                    [ 'label' => 'Renovar Tenant', 'url' => url('seguridad', 'tenant', 'renovar'), 'active' => ($controller === 'tenant' && $action === 'renovar') ],
                    [ 'label' => 'Suscripciones', 'url' => url('seguridad', 'tenant', 'suscripciones'), 'active' => ($controller === 'tenant' && $action === 'suscripciones') ]
                ]],
                [ 'label' => 'Usuarios', 'icon' => 'fas fa-users', 'url' => '#', 'submenu' => [
                    [ 'label' => 'Lista de Usuarios', 'url' => url('seguridad', 'usuario', 'index'), 'active' => ($controller === 'usuario' && $action === 'index') ],
                    [ 'label' => 'Nuevo Usuario', 'url' => url('seguridad', 'usuario', 'crear'), 'active' => ($controller === 'usuario' && $action === 'crear') ],
                    [ 'label' => 'Editar Usuario', 'url' => url('seguridad', 'usuario', 'editar'), 'active' => ($controller === 'usuario' && $action === 'editar') ],
                    [ 'label' => 'Eliminar Usuario', 'url' => url('seguridad', 'usuario', 'eliminar'), 'active' => ($controller === 'usuario' && $action === 'eliminar') ],
                    [ 'label' => 'Desbloquear Usuario', 'url' => url('seguridad', 'usuario', 'desbloquear'), 'active' => ($controller === 'usuario' && $action === 'desbloquear') ],
                    [ 'label' => 'Usuarios Bloqueados', 'url' => url('seguridad', 'usuario', 'bloqueados'), 'active' => ($controller === 'usuario' && $action === 'bloqueados') ],
                    [ 'label' => 'Resetear Contraseña', 'url' => url('seguridad', 'usuario', 'resetPassword'), 'active' => ($controller === 'usuario' && ($action === 'resetpassword' || $action === 'resetPassword')) ]
                ]],
                [ 'label' => 'Roles y Permisos', 'icon' => 'fas fa-user-shield', 'url' => '#', 'submenu' => [
                    [ 'label' => 'Lista de Roles', 'url' => url('seguridad', 'rol', 'index'), 'active' => ($controller === 'rol' && $action === 'index') ],
                    [ 'label' => 'Nuevo Rol', 'url' => url('seguridad', 'rol', 'crear'), 'active' => ($controller === 'rol' && $action === 'crear') ],
                    [ 'label' => 'Editar Rol', 'url' => url('seguridad', 'rol', 'editar'), 'active' => ($controller === 'rol' && $action === 'editar') ],
                    [ 'label' => 'Matriz de Permisos', 'url' => url('seguridad', 'rol', 'permisos'), 'active' => ($controller === 'rol' && $action === 'permisos') ]
                ]]
            ]],
            [ 'header' => 'Módulos y Apps' ],
            [ 'items' => [
                [ 'label' => 'Módulos del Sistema', 'icon' => 'fas fa-puzzle-piece', 'url' => '#', 'submenu' => [
                    [ 'label' => 'Lista de Módulos', 'url' => url('seguridad', 'modulo', 'index'), 'active' => ($controller === 'modulo' && $action === 'index') ],
                    [ 'label' => 'Nuevo Módulo', 'url' => url('seguridad', 'modulo', 'crear'), 'active' => ($controller === 'modulo' && $action === 'crear') ],
                    [ 'label' => 'Editar Módulo', 'url' => url('seguridad', 'modulo', 'editar'), 'active' => ($controller === 'modulo' && $action === 'editar') ],
                    [ 'label' => 'Duplicar Módulo', 'url' => url('seguridad', 'modulo', 'duplicar'), 'active' => ($controller === 'modulo' && $action === 'duplicar') ],
                    [ 'label' => 'Iconos y Colores', 'url' => url('seguridad', 'modulo', 'iconos'), 'active' => ($controller === 'modulo' && $action === 'iconos') ],
                    [ 'label' => 'Actualizar Icono', 'url' => url('seguridad', 'modulo', 'actualizarIcono'), 'active' => ($controller === 'modulo' && $action === 'actualizaricono') ]
                ]],
                [ 'label' => 'Asignación', 'icon' => 'fas fa-link', 'url' => '#', 'submenu' => [
                    [ 'label' => 'Módulos por Tenant', 'url' => url('seguridad', 'asignacion', 'modulos'), 'active' => ($controller === 'asignacion' && $action === 'modulos') ],
                    [ 'label' => 'Guardar Módulos', 'url' => url('seguridad', 'asignacion', 'guardarmodulos'), 'active' => ($controller === 'asignacion' && $action === 'guardarmodulos') ],
                    [ 'label' => 'Asignación Masiva', 'url' => url('seguridad', 'asignacion', 'masiva'), 'active' => ($controller === 'asignacion' && $action === 'masiva') ],
                    [ 'label' => 'Guardar Masiva', 'url' => url('seguridad', 'asignacion', 'guardarmasiva'), 'active' => ($controller === 'asignacion' && $action === 'guardarmasiva') ],
                    [ 'label' => 'Planes', 'url' => url('seguridad', 'plan', 'index'), 'active' => ($controller === 'plan' && $action === 'index') ],
                    [ 'label' => 'Nuevo Plan', 'url' => url('seguridad', 'plan', 'crear'), 'active' => ($controller === 'plan' && $action === 'crear') ],
                    [ 'label' => 'Editar Plan', 'url' => url('seguridad', 'plan', 'editar'), 'active' => ($controller === 'plan' && $action === 'editar') ],
                    [ 'label' => 'Eliminar Plan', 'url' => url('seguridad', 'plan', 'eliminar'), 'active' => ($controller === 'plan' && $action === 'eliminar') ],
                    [ 'label' => 'Comparativa', 'url' => url('seguridad', 'plan', 'comparativa'), 'active' => ($controller === 'plan' && $action === 'comparativa') ]
                ]]
            ]],
            [ 'header' => 'Auditoría' ],
            [ 'items' => [
                [ 'label' => 'Logs de Acceso', 'icon' => 'fas fa-history', 'url' => url('seguridad', 'auditoria', 'accesos'), 'active' => ($controller === 'auditoria' && $action === 'accesos') ],
                [ 'label' => 'Logs de Cambios', 'icon' => 'fas fa-file-alt', 'url' => url('seguridad', 'auditoria', 'cambios'), 'active' => ($controller === 'auditoria' && $action === 'cambios') ],
                [ 'label' => 'Alertas', 'icon' => 'fas fa-bell', 'url' => url('seguridad', 'auditoria', 'alertas'), 'active' => ($controller === 'auditoria' && $action === 'alertas'), 'badge' => '!', 'badge_type' => 'danger' ]
            ]],
            [ 'header' => 'Configuración' ],
            [ 'items' => [
                [ 'label' => 'Sistema', 'icon' => 'fas fa-cogs', 'url' => url('seguridad', 'modulo', 'configuracion'), 'active' => ($controller === 'modulo' && $action === 'configuracion') ],
                // Puedes agregar más opciones aquí si tienes más acciones de configuración
            ]]
        ];
        return $menu;
    }
}
