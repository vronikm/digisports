<?php
abstract class BaseController {

    /**
     * Middleware de autorización centralizado
     * Valida permisos antes de ejecutar acciones sensibles.
     * En peticiones AJAX devuelve JSON 403. En navegación redirige al Hub.
     *
     * @param string      $action   Acción (crear, editar, eliminar, ver)
     * @param string|null $resource Recurso (ej: 'usuarios')
     */
    protected function authorize($action, $resource = null) {
        $perm = $resource ? "$resource.$action" : $action;
        if (!hasPermission($perm)) {
            $this->logSecurityEvent("Acceso denegado: {$perm}");
            if ($this->isAjax()) {
                $this->error('No tienes permiso para realizar esta acción.', 403);
            }
            setFlashMessage('error', 'No tienes permiso para realizar esta acción.');
            redirect('core', 'hub', 'index');
            exit;
        }
    }
    /**
     * DigiSports - Controlador Base
     * Todos los controladores heredan de esta clase
     * 
     * @package DigiSports
     * @version 1.0.0
     */
    protected $db;
    protected $tenantId;
    protected $userId;
    protected $user;
    protected $viewData = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        // Inicializar base de datos
        $this->db = Database::getInstance()->getConnection();
        
        // Cargar información de usuario
        if (isAuthenticated()) {
            $this->tenantId = getTenantId();
            $this->userId = getUserId();
            $this->user = getCurrentUser();
            
            // Cargar datos comunes para todas las vistas
            $this->loadCommonViewData();
        }
    }
    
    /**
     * Cargar datos comunes para todas las vistas
     */
    private function loadCommonViewData() {
        $this->viewData['user'] = $this->user;
        $this->viewData['tenant'] = $this->getTenantInfo();
        $this->viewData['modules'] = $this->getUserModules();
        $this->viewData['notifications'] = $this->getUnreadNotifications();
        $this->viewData['notificationCount'] = count($this->viewData['notifications']);
        
        // Detectar módulo activo basándose en el controlador
        $className = get_class($this);
        $this->viewData['currentController'] = $className;
        
        // Mapear controladores a módulos
        if (strpos($className, 'Dashboard') !== false) {
            $this->viewData['currentModule'] = 'dashboard';
        } elseif (strpos($className, 'Cancha') !== false || strpos($className, 'Instalacion') !== false || strpos($className, 'Tarifa') !== false) {
            $this->viewData['currentModule'] = 'instalaciones';
        } elseif (strpos($className, 'Reserva') !== false) {
            $this->viewData['currentModule'] = 'reservas';
        } elseif (strpos($className, 'Factura') !== false || strpos($className, 'Comprobante') !== false
               || strpos($className, 'FormaPago') !== false) {
            $this->viewData['currentModule'] = 'facturacion';
        } elseif (strpos($className, 'Reporte') !== false || strpos($className, 'Kpi') !== false) {
            $this->viewData['currentModule'] = 'reportes';
        } else {
            $this->viewData['currentModule'] = '';
        }
    }
    
    /**
     * Obtener información del tenant con caché de sesión (TTL: 5 minutos)
     * Evita queries repetidas en cada request para datos que rara vez cambian.
     *
     * @return array|null
     */
    private function getTenantInfo() {
        $cacheKey = 'sess_tenant_' . $this->tenantId;
        $cacheTsKey = 'sess_tenant_ts_' . $this->tenantId;

        if (isset($_SESSION[$cacheKey]) && (time() - ($_SESSION[$cacheTsKey] ?? 0)) < 300) {
            return $_SESSION[$cacheKey];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT
                    t.*,
                    p.sus_nombre as plan_nombre
                FROM seguridad_tenants t
                LEFT JOIN seguridad_planes_suscripcion p ON t.ten_plan_id = p.sus_plan_id
                WHERE t.ten_tenant_id = ?
            ");
            $stmt->execute([$this->tenantId]);
            $result = $stmt->fetch();

            $_SESSION[$cacheKey]   = $result;
            $_SESSION[$cacheTsKey] = time();
            return $result;

        } catch (Exception $e) {
            $this->logError("Error al obtener tenant: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener módulos activos del tenant con caché de sesión (TTL: 5 minutos)
     * Usa la misma consulta que AuthController::getUserModules() (seguridad_tenant_modulos).
     *
     * @return array
     */
    private function getUserModules() {
        $cacheKey = 'sess_modules_' . $this->tenantId;
        $cacheTsKey = 'sess_modules_ts_' . $this->tenantId;

        if (isset($_SESSION[$cacheKey]) && (time() - ($_SESSION[$cacheTsKey] ?? 0)) < 300) {
            return $_SESSION[$cacheKey];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT
                    m.mod_id       AS modulo_id,
                    m.mod_codigo   AS codigo,
                    m.mod_nombre   AS nombre,
                    m.mod_descripcion AS descripcion
                FROM seguridad_modulos m
                INNER JOIN seguridad_tenant_modulos tm ON m.mod_id = tm.tmo_modulo_id
                WHERE tm.tmo_tenant_id = ? AND tm.tmo_activo = 'S' AND m.mod_activo = 1
                ORDER BY m.mod_orden ASC
            ");
            $stmt->execute([$this->tenantId]);
            $result = $stmt->fetchAll();

            $_SESSION[$cacheKey]   = $result;
            $_SESSION[$cacheTsKey] = time();
            return $result;

        } catch (Exception $e) {
            $this->logError("Error al obtener módulos: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Obtener notificaciones no leídas
     * 
     * @param int $limit Límite de notificaciones
     * @return array
     */
    private function getUnreadNotifications($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    notificacion_id,
                    tipo,
                    titulo,
                    mensaje,
                    url_accion,
                    icono,
                    color,
                    fecha_creacion
                FROM notificaciones
                WHERE usuario_id = ?
                AND leida = 'N'
                AND (fecha_expiracion IS NULL OR fecha_expiracion > NOW())
                ORDER BY fecha_creacion DESC
                LIMIT ?
            ");
            
            $stmt->execute([$this->userId, $limit]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            $this->logError("Error al obtener notificaciones: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Renderizar vista
     * 
     * @param string $view Nombre de la vista
     * @param array $data Datos adicionales
     */
    protected function render($view, $data = []) {
        // Combinar datos
        $data = array_merge($this->viewData, $data);
        
        // Buffer de salida para la vista
        ob_start();
        
        // Extraer variables para la vista
        extract($data);
        
        // Incluir vista
        $viewPath = APP_PATH . '/views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("Vista no encontrada: {$view}");
        }
        
        require $viewPath;
        
        // Obtener contenido de la vista
        $content = ob_get_clean();
        
        // Si tiene layout, cargarlo
        if (isset($data['layout']) && $data['layout']) {
            $layoutPath = APP_PATH . '/views/layouts/' . $data['layout'] . '.php';
            
            if (file_exists($layoutPath)) {
                // Agregar $content a $data y extraer todo para el layout
                $data['content'] = $content;
                extract($data);
                require $layoutPath;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }
    
    /**
     * Renderizar vista del Hub (sin layout, página completa independiente)
     * 
     * @param string $view Nombre de la vista
     * @param array $data Datos para la vista
     */
    protected function renderHub($view, $data = []) {
        // Combinar datos
        $data = array_merge($this->viewData, $data);
        
        // Extraer variables para la vista
        extract($data);
        
        // Incluir vista directamente (el Hub tiene su propio HTML completo)
        $viewPath = APP_PATH . '/views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("Vista del Hub no encontrada: {$view}");
        }
        
        require $viewPath;
    }
    
    /**
     * Renderizar JSON
     * 
     * @param array $data Datos
     * @param int $statusCode Código HTTP
     */
    protected function renderJson($data, $statusCode = 200) {
        // Limpiar cualquier output previo (warnings/notices con display_errors=On)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Retornar respuesta de éxito JSON
     * 
     * @param mixed $data Datos
     * @param string $message Mensaje
     */
    protected function success($data = null, $message = 'Operación exitosa') {
        $this->renderJson([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Retornar respuesta de error JSON
     * 
     * @param string $message Mensaje de error
     * @param int $code Código de error
     */
    protected function error($message = 'Ha ocurrido un error', $code = 400) {
        $this->renderJson([
            'success' => false,
            'message' => $message,
            'error_code' => $code
        ], $code);
    }
    
    /**
     * Validar petición AJAX
     * 
     * @return bool
     */
    protected function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Validar método POST
     * 
     * @return bool
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Validar método GET
     * 
     * @return bool
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Obtener datos POST
     * 
     * @param string $key Clave
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        
        return Security::sanitizeInput($_POST[$key] ?? $default);
    }
    
    /**
     * Obtener datos GET
     * 
     * @param string $key Clave
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        
        return Security::sanitizeInput($_GET[$key] ?? $default);
    }
    
    /**
     * Validar token CSRF
     * 
     * @return bool
     */
    protected function validateCsrf() {
        $token = $this->post('csrf_token');
        
        if (!Security::validateCsrfToken($token)) {
            Security::logSecurityEvent('CSRF_VALIDATION_FAILED');
            return false;
        }
        
        return true;
    }
    
    /**
     * Verificar permiso
     * 
     * @param string $permission Permiso requerido
     * @throws Exception Si no tiene permiso
     */
    protected function requirePermission($permission) {
        if (!hasPermission($permission)) {
            $this->logSecurityEvent("Acceso denegado a: {$permission}");
            
            if ($this->isAjax()) {
                $this->error('No tiene permisos para realizar esta acción', 403);
            } else {
                setFlashMessage('error', 'No tiene permisos para acceder a esta sección');
                redirect('core', 'dashboard');
            }
        }
    }
    
    /**
     * Verificar que sea administrador
     * 
     * @throws Exception Si no es administrador
     */
    protected function requireAdmin() {
        if (!isAdmin()) {
            $this->logSecurityEvent("Intento de acceso no autorizado a área de administración");
            
            if ($this->isAjax()) {
                $this->error('Acceso denegado', 403);
            } else {
                setFlashMessage('error', 'Acceso denegado');
                redirect('core', 'dashboard');
            }
        }
    }
    
    /**
     * Registrar en auditoría
     * 
     * @param string $tabla Tabla afectada
     * @param int $registroId ID del registro
     * @param string $operacion Tipo de operación
     * @param array $valoresAnteriores Valores anteriores
     * @param array $valoresNuevos Valores nuevos
     */
    protected function audit($tabla, $registroId, $operacion, $valoresAnteriores = [], $valoresNuevos = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO seguridad_auditoria 
                (aud_tenant_id, aud_usuario_id, aud_modulo, aud_tabla, aud_registro_id, 
                 aud_operacion, aud_valores_anteriores, aud_valores_nuevos, 
                 aud_ip, aud_user_agent, aud_url, aud_metodo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $this->tenantId,
                $this->userId,
                $this->getModuleName(),
                $tabla,
                $registroId,
                $operacion,
                json_encode($valoresAnteriores),
                json_encode($valoresNuevos),
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $_SERVER['REQUEST_URI'] ?? '',
                $_SERVER['REQUEST_METHOD'] ?? ''
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en auditoría: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener nombre del módulo actual
     * 
     * @return string
     */
    protected function getModuleName() {
        $class = get_class($this);
        $parts = explode('\\', $class);
        return $parts[2] ?? 'Core';
    }
    
    /**
     * Registrar error
     * 
     * @param string $message Mensaje de error
     */
    protected function logError($message) {
        error_log(sprintf(
            "[%s] [%s] %s - User: %s, Tenant: %s",
            date('Y-m-d H:i:s'),
            get_class($this),
            $message,
            $this->userId ?? 'Guest',
            $this->tenantId ?? 'N/A'
        ));
    }
    
    /**
     * Registrar evento de seguridad
     * 
     * @param string $event Evento
     */
    protected function logSecurityEvent($event) {
        Security::logSecurityEvent($event, get_class($this));
    }
    
    /**
     * Iniciar transacción
     */
    protected function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Confirmar transacción
     */
    protected function commit() {
        return $this->db->commit();
    }
    
    /**
     * Revertir transacción
     */
    protected function rollback() {
        return $this->db->rollback();
    }
}