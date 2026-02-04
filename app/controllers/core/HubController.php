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
        $rolId = $_SESSION['rol_id'] ?? null;
        if ($rolId === 1 || $rolId === '1') {
            // Super admin: acceso a todos los módulos activos
            $stmt = $this->db->query("SELECT codigo, nombre, descripcion, icono, color as color_fondo, url_base FROM modulos_sistema WHERE estado = 'A' ORDER BY orden_visualizacion ASC");
            $modulos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            // Resto de usuarios: lógica de permisos (puedes mejorar aquí si tienes reglas más complejas)
            $tenantId = $_SESSION['tenant_id'] ?? 1;
            $modulos = $this->obtenerModulosAccesibles($tenantId, $rolId);
        }
        $this->viewData['modulos'] = $modulos;
        $this->viewData['modulos_organizados'] = array_chunk($modulos, 4);
        $this->viewData['usuario'] = $_SESSION['user_name'] ?? 'Usuario';
        $this->viewData['tenant_nombre'] = $_SESSION['tenant_name'] ?? 'DigiSports';
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
                    m.id,
                    m.codigo,
                    m.nombre,
                    m.descripcion,
                    m.icono,
                    m.color_fondo,
                    m.orden,
                    m.ruta_modulo,
                    m.ruta_controller,
                    m.ruta_action,
                    m.es_externo,
                    m.url_externa,
                    COALESCE(rm.puede_ver, 0) AS puede_ver,
                    COALESCE(rm.puede_crear, 0) AS puede_crear,
                    COALESCE(rm.puede_editar, 0) AS puede_editar,
                    COALESCE(rm.puede_eliminar, 0) AS puede_eliminar
                FROM modulos m
                INNER JOIN tenant_modulos tm ON m.id = tm.modulo_id 
                    AND tm.tenant_id = ? 
                    AND tm.estado = 'ACTIVO'
                    AND (tm.fecha_fin IS NULL OR tm.fecha_fin >= CURDATE())
                LEFT JOIN rol_modulos rm ON m.id = rm.modulo_id AND rm.rol_id = ?
                WHERE m.activo = 1
                    AND (rm.puede_ver = 1 OR ? = 1)
                ORDER BY m.orden ASC
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
