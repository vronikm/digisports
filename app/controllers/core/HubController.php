<?php
/**
 * DigiSports - Controlador del Hub de Aplicaciones
 * Muestra los módulos disponibles según suscripción y permisos
 * 
 * @package DigiSports\Controllers\Core
 * @version 1.0.0
 */

namespace App\Controllers\Core;

require_once BASE_PATH . '/app/controllers/BaseController.php';

class HubController extends \BaseController {
    
    /**
     * Vista principal del Hub - Selección de módulos
     */
    public function index() {
        $rolId = $_SESSION['usu_rol_id'] ?? null;
        if ($rolId === 1 || $rolId === '1') {
            // Super admin: acceso a todos los módulos activos
            $stmt = $this->db->query("SELECT mod_id, mod_codigo, mod_nombre, mod_descripcion, mod_icono, mod_color_fondo, mod_orden, mod_ruta_modulo, mod_ruta_controller, mod_ruta_action, mod_es_externo, mod_url_externa, mod_activo FROM seguridad_modulos WHERE mod_activo = 1 ORDER BY mod_orden ASC");
            $modulosRaw = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            // Resto de usuarios: lógica de permisos (puedes mejorar aquí si tienes reglas más complejas)
            $tenantId = $_SESSION['usu_tenant_id'] ?? 1;
            $modulosRaw = $this->obtenerModulosAccesibles($tenantId, $rolId);
        }
        // Mapear campos a los esperados por la vista
        $modulos = array_map(function($m) {
            return [
                'codigo' => isset($m['mod_codigo']) ? $m['mod_codigo'] : '',
                'nombre' => isset($m['mod_nombre']) ? $m['mod_nombre'] : '',
                'descripcion' => isset($m['mod_descripcion']) ? $m['mod_descripcion'] : '',
                'icono' => isset($m['mod_icono']) ? $m['mod_icono'] : '',
                'color_fondo' => isset($m['mod_color_fondo']) && $m['mod_color_fondo'] !== null ? $m['mod_color_fondo'] : '#1e40af',
                'ruta_modulo' => isset($m['mod_ruta_modulo']) ? $m['mod_ruta_modulo'] : '',
                'ruta_controller' => isset($m['mod_ruta_controller']) ? $m['mod_ruta_controller'] : '',
                'ruta_action' => isset($m['mod_ruta_action']) ? $m['mod_ruta_action'] : '',
                'es_externo' => isset($m['mod_es_externo']) ? $m['mod_es_externo'] : 0,
                'url_externa' => isset($m['mod_url_externa']) ? $m['mod_url_externa'] : null,
                'activo' => isset($m['mod_activo']) ? $m['mod_activo'] : 1,
            ];
        }, $modulosRaw);
        $this->viewData['modulos'] = $modulos;
        $this->viewData['modulos_organizados'] = array_chunk($modulos, 4);
        $this->viewData['usuario'] = $_SESSION['usu_nombres'] ?? 'Usuario';
        $this->viewData['tenant_nombre'] = $_SESSION['tenant_nombre'] ?? 'DigiSports';
        $this->viewData['title'] = 'DigiSports - Selecciona tu módulo';
        $this->renderHub('core/hub/index', $this->viewData);
    }
    
    /**
     * Obtener módulos accesibles según tenant y rol
     */
    private function obtenerModulosAccesibles($tenantId, $rolId) {
        try {
            $sql = "
                SELECT DISTINCT
                    m.mod_id,
                    m.mod_codigo,
                    m.mod_nombre,
                    m.mod_descripcion,
                    m.mod_icono,
                    m.mod_color_fondo,
                    m.mod_orden,
                    m.mod_ruta_modulo,
                    m.mod_ruta_controller,
                    m.mod_ruta_action,
                    m.mod_es_externo,
                    m.mod_url_externa,
                    m.mod_activo,
                    COALESCE(rm.rm_puede_ver, 0) AS puede_ver,
                    COALESCE(rm.rm_puede_crear, 0) AS puede_crear,
                    COALESCE(rm.rm_puede_editar, 0) AS puede_editar,
                    COALESCE(rm.rm_puede_eliminar, 0) AS puede_eliminar
                FROM seguridad_modulos m
                INNER JOIN seguridad_tenant_modulos tm ON m.mod_id = tm.tm_modulo_id 
                    AND tm.tm_tenant_id = ? 
                    AND tm.tm_estado = 'ACTIVO'
                    AND (tm.tm_fecha_fin IS NULL OR tm.tm_fecha_fin >= CURDATE())
                LEFT JOIN seguridad_rol_modulos rm ON m.mod_id = rm.rm_modulo_id AND rm.rm_rol_id = ?
                WHERE m.mod_activo = 1
                    AND (rm.rm_puede_ver = 1 OR ? = 1)
                ORDER BY m.mod_orden ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $rolId, $rolId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error al obtener módulos: " . $e->getMessage());
            return $this->obtenerModulosPorDefecto();
        }
    }
    
    /**
     * Módulos por defecto si hay error en BD
     */
    private function obtenerModulosPorDefecto() {
        return [
            [
                'codigo' => 'instalaciones',
                'nombre' => 'Instalaciones',
                'descripcion' => 'Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.',
                'icono' => 'fas fa-building',
                'color_fondo' => '#3B82F6',
                'ruta_modulo' => 'instalaciones',
                'ruta_controller' => 'cancha',
                'ruta_action' => 'index',
            ],
            [
                'codigo' => 'reservas',
                'nombre' => 'Reservas',
                'descripcion' => 'Sistema de reservas por bloques horarios con confirmación automática y recurrencias.',
                'icono' => 'fas fa-calendar-check',
                'color_fondo' => '#10B981',
                'ruta_modulo' => 'reservas',
                'ruta_controller' => 'reserva',
                'ruta_action' => 'index',
            ],
            [
                'codigo' => 'facturacion',
                'nombre' => 'Facturación',
                'descripcion' => 'Comprobantes electrónicos SRI, múltiples formas de pago y pasarelas online.',
                'icono' => 'fas fa-file-invoice-dollar',
                'color_fondo' => '#F59E0B',
                'ruta_modulo' => 'facturacion',
                'ruta_controller' => 'comprobante',
                'ruta_action' => 'index',
            ],
            [
                'codigo' => 'reportes',
                'nombre' => 'Reportes',
                'descripcion' => 'KPIs, ocupación, ingresos por período y análisis detallado de tu negocio.',
                'icono' => 'fas fa-chart-bar',
                'color_fondo' => '#8B5CF6',
                'ruta_modulo' => 'reportes',
                'ruta_controller' => 'kpi',
                'ruta_action' => 'index',
            ],
        ];
    }
    
    /**
     * Acceder a un módulo específico
     */
    public function acceder() {
        $moduloCodigo = $_GET['modulo'] ?? null;
        
        if (!$moduloCodigo) {
            redirect('core', 'hub', 'index');
            return;
        }
        
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $rolId = $_SESSION['rol_id'] ?? 1;
        
        // Verificar acceso al módulo
        $modulo = $this->verificarAccesoModulo($moduloCodigo, $tenantId, $rolId);
        
        if (!$modulo) {
            $_SESSION['error'] = 'No tienes acceso a este módulo o no está disponible en tu suscripción.';
            redirect('core', 'hub', 'index');
            return;
        }
        
        // Guardar módulo activo en sesión
        $_SESSION['modulo_activo'] = [
            'codigo' => $modulo['codigo'],
            'nombre' => $modulo['nombre'],
            'icono' => $modulo['icono'],
            'color' => $modulo['color_fondo'],
            'permisos' => [
                'ver' => $modulo['puede_ver'] ?? 1,
                'crear' => $modulo['puede_crear'] ?? 0,
                'editar' => $modulo['puede_editar'] ?? 0,
                'eliminar' => $modulo['puede_eliminar'] ?? 0,
            ],
        ];
        
        // Si es sistema externo, redirigir con token
        if ($modulo['es_externo'] && !empty($modulo['url_externa'])) {
            $token = $this->generarTokenExterno();
            header('Location: ' . $modulo['url_externa'] . '?token=' . $token);
            exit;
        }
        
        // Redirigir al módulo interno
        redirect(
            $modulo['ruta_modulo'], 
            $modulo['ruta_controller'], 
            $modulo['ruta_action']
        );
    }
    
    /**
     * Verificar si el usuario tiene acceso al módulo
     */
    private function verificarAccesoModulo($codigo, $tenantId, $rolId) {
        try {
            $sql = "
                SELECT 
                    m.*,
                    COALESCE(rm.puede_ver, 0) AS puede_ver,
                    COALESCE(rm.puede_crear, 0) AS puede_crear,
                    COALESCE(rm.puede_editar, 0) AS puede_editar,
                    COALESCE(rm.puede_eliminar, 0) AS puede_eliminar
                FROM modulos m
                INNER JOIN tenant_modulos tm ON m.id = tm.modulo_id 
                    AND tm.tenant_id = ? 
                    AND tm.estado = 'ACTIVO'
                LEFT JOIN rol_modulos rm ON m.id = rm.modulo_id AND rm.rol_id = ?
                WHERE m.codigo = ? 
                    AND m.activo = 1
                    AND (rm.puede_ver = 1 OR ? = 1)
                LIMIT 1
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $rolId, $codigo, $rolId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Error verificando acceso: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generar token para sistemas externos
     */
    private function generarTokenExterno() {
        $datos = [
            'user_id' => $_SESSION['user_id'],
            'tenant_id' => $_SESSION['tenant_id'],
            'rol_id' => $_SESSION['rol_id'],
            'timestamp' => time(),
            'expires' => time() + 300, // 5 minutos
        ];
        
        $token = base64_encode(json_encode($datos));
        $signature = hash_hmac('sha256', $token, ENCRYPTION_KEY ?? 'digisports_secret');
        
        return $token . '.' . $signature;
    }
    
    /**
     * Volver al hub desde cualquier módulo
     */
    public function volver() {
        // Limpiar módulo activo
        unset($_SESSION['modulo_activo']);
        
        redirect('core', 'hub', 'index');
    }
}
