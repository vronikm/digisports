<?php
/**
 * DigiSports - Sistema de Enrutamiento
 * Maneja URLs encriptadas y enrutamiento MVC
 * 
 * @package DigiSports
 * @version 1.0.0
 */

class Router {
    
    private $controller = 'Dashboard';
    private $action = 'index';
    private $params = [];
    private $module = 'core';
    
    /**
     * Constructor - Procesa la URL
     */
    public function __construct() {
        $this->parseUrl();
    }
    
    /**
     * Parsear y procesar URL
     */
    private function parseUrl() {
        // Verificar si es URL encriptada
        if (isset($_GET['r'])) {
            $this->parseEncryptedUrl($_GET['r']);
        } else {
            $this->parseStandardUrl();
        }
    }
    
    /**
     * Procesar URL encriptada
     * 
     * @param string $encrypted URL encriptada
     */
    private function parseEncryptedUrl($encrypted) {
        try {
            $data = Security::decodeSecureUrl($encrypted);
            if (!$data || !is_array($data)) {
                throw new Exception('URL inválida o expirada');
            }
            // Validar módulo, controlador y acción
            $mod = $data['m'] ?? 'core';
            $ctrl = $data['c'] ?? 'Dashboard';
            $act = $data['a'] ?? 'index';
            if (!$this->isValidModule($mod)) {
                Security::logSecurityEvent('INVALID_MODULE', $mod);
                throw new Exception('Módulo no permitido');
            }
            $controllerPath = Config::MODULES[$mod]['path'] . ucfirst($ctrl) . 'Controller.php';
            if (!file_exists(BASE_PATH . $controllerPath)) {
                Security::logSecurityEvent('INVALID_CONTROLLER', $ctrl);
                throw new Exception('Controlador no permitido');
            }
            // Validar acción (solo letras y guiones bajos)
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $act)) {
                Security::logSecurityEvent('INVALID_ACTION', $act);
                throw new Exception('Acción no permitida');
            }
            $this->module = $mod;
            $this->controller = $ctrl;
            $this->action = $act;
            $this->params = $data['p'] ?? [];
            if (is_array($this->params)) {
                foreach ($this->params as $key => $value) {
                    $_GET[$key] = $value;
                }
            }
            $this->logAccess('ENCRYPTED_URL');
        } catch (Exception $e) {
            Security::logSecurityEvent('INVALID_URL', $e->getMessage());
            $this->redirectToError('URL inválida o expirada');
        }
    }
    
    /**
     * Procesar URL estándar (solo para desarrollo si está habilitado)
     */
    private function parseStandardUrl() {
        // Primero intentar parámetros GET directos (para desarrollo/debug)
        if (isset($_GET['module']) || isset($_GET['controller']) || isset($_GET['action'])) {
            $this->module = isset($_GET['module']) ? strtolower($_GET['module']) : 'core';
            $this->controller = isset($_GET['controller']) ? ucfirst(strtolower($_GET['controller'])) : 'Dashboard';
            $this->action = isset($_GET['action']) ? strtolower($_GET['action']) : 'index';
            $this->logAccess('GET_PARAMS');
            return;
        }
        
        $url = isset($_GET['url']) ? $_GET['url'] : '';
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $url = explode('/', trim($url, '/'));
        
        // Si no hay URL, usar controlador por defecto
        if (empty($url[0])) {
            $this->controller = ucfirst(Config::ROUTES['default_controller']);
            $this->action = Config::ROUTES['default_action'];
            return;
        }
        
        // Módulo (opcional)
        if (isset($url[0]) && $url[0] && $this->isValidModule($url[0])) {
            $this->module = strtolower(array_shift($url));
        }
        
        // Controlador
        if (isset($url[0]) && $url[0]) {
            $this->controller = ucfirst(strtolower(array_shift($url)));
        }
        
        // Acción
        if (isset($url[0]) && $url[0]) {
            $this->action = strtolower(array_shift($url));
        }
        
        // Parámetros restantes
        $this->params = $url ? array_values($url) : [];
        
        $this->logAccess('STANDARD_URL');
    }
    
    /**
     * Verificar si el módulo es válido
     * 
     * @param string $module Nombre del módulo
     * @return bool
     */
    private function isValidModule($module) {
        $modules = Config::MODULES;
        return isset($modules[$module]) && $modules[$module]['enabled'];
    }
    
    /**
     * Despachar la petición al controlador
     */
    public function dispatch() {
        try {
            // Verificar IP bloqueada (desactivado en desarrollo)
            // if (Security::isIPBlocked()) {
            //     $this->redirectToError('Su IP ha sido bloqueada temporalmente');
            //     return;
            // }
            
            // Verificar sesión si no es login (desactivado en desarrollo)
            // if ($this->controller !== 'Auth' && !$this->isPublicRoute()) {
            //     $this->checkAuthentication();
            // }
            
            // Obtener ruta del controlador
            $controllerPath = $this->getControllerPath();
            
            if (!file_exists($controllerPath)) {
                throw new Exception("Controlador no encontrado: {$this->controller}");
            }
            
            require_once $controllerPath;
            
            // Crear instancia del controlador
            $controllerClass = $this->getControllerClass();
            
            if (!class_exists($controllerClass)) {
                throw new Exception("Clase del controlador no encontrada: {$controllerClass}");
            }
            
            $controllerInstance = new $controllerClass();
            
            // Verificar que el método existe
            if (!method_exists($controllerInstance, $this->action)) {
                throw new Exception("Método no encontrado: {$this->action}");
            }
            
            // Ejecutar el método (los parámetros ya están en $_GET)
            $controllerInstance->{$this->action}();
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Obtener ruta completa del controlador
     * 
     * @return string Path del archivo del controlador
     */
    private function getControllerPath() {
        $modulePath = Config::MODULES[$this->module]['path'] ?? '/app/controllers/';
        return BASE_PATH . $modulePath . ucfirst($this->controller) . 'Controller.php';
    }
    
    /**
     * Obtener nombre de clase del controlador
     * 
     * @return string Nombre de la clase
     */
    private function getControllerClass() {
        $namespace = Config::MODULES[$this->module]['namespace'] ?? '';
        $class = ucfirst($this->controller) . 'Controller';
        return $namespace ? $namespace . '\\' . $class : $class;
    }
    
    /**
     * Verificar autenticación
     */
    private function checkAuthentication() {
        session_start();
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['tenant_id'])) {
            // Guardar URL solicitada para redirigir después del login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            
            header('Location: ' . $this->generateUrl('auth', 'login'));
            exit;
        }
        
        // Verificar que el tenant esté activo
        $this->checkTenantStatus();
    }
    
    /**
     * Verificar estado del tenant
     */
    private function checkTenantStatus() {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT estado_suscripcion, fecha_vencimiento 
            FROM tenants 
            WHERE tenant_id = ?
        ");
        
        $stmt->execute([$_SESSION['tenant_id']]);
        $tenant = $stmt->fetch();
        
        if (!$tenant) {
            $this->redirectToError('Tenant no encontrado');
            return;
        }
        
        // Verificar estado de suscripción
        if ($tenant['estado_suscripcion'] === 'SUSPENDIDA') {
            $this->redirectToError('Su suscripción ha sido suspendida. Contacte a soporte.');
            return;
        }
        
        if ($tenant['estado_suscripcion'] === 'VENCIDA') {
            // Permitir solo acceso a renovación
            if ($this->controller !== 'Suscripcion') {
                header('Location: ' . $this->generateUrl('core', 'suscripcion', 'renovar'));
                exit;
            }
        }
    }
    
    /**
     * Verificar si es una ruta pública
     * 
     * @return bool
     */
    private function isPublicRoute() {
        $publicRoutes = [
            'auth' => ['login', 'logout', '2fa', 'recuperar', 'reset', 'authenticate'],
            'registro' => ['index', 'crear'],
            'welcome' => ['index', 'status'],
            'error' => ['show', 'notFound', 'forbidden', 'serverError']
        ];
        
        $controller = strtolower($this->controller);
        
        return isset($publicRoutes[$controller]) && 
               in_array($this->action, $publicRoutes[$controller]);
    }
    
    /**
     * Generar URL encriptada
     * 
     * @param string $module Módulo
     * @param string $controller Controlador
     * @param string $action Acción
     * @param array $params Parámetros
     * @return string URL completa
     */
    public function generateUrl($module, $controller, $action = 'index', $params = []) {
        $data = [
            'm' => $module,
            'c' => $controller,
            'a' => $action,
            'p' => $params,
            't' => time()
        ];
        
        $encrypted = Security::encryptUrl(json_encode($data));
        return Config::baseUrl('index.php?r=' . $encrypted);
    }
    
    /**
     * Generar URL para módulo legacy (escuelas)
     * 
     * @param string $path Path en el sistema antiguo
     * @return string URL completa
     */
    public function generateLegacyUrl($path) {
        // Generar token de acceso SSO
        $token = $this->generateSSOToken();
        
        $legacyBase = Config::baseUrl('/escuelas/');
        return $legacyBase . $path . '?sso_token=' . $token;
    }
    
    /**
     * Generar token SSO para integración con sistema legacy
     * 
     * @return string Token encriptado
     */
    private function generateSSOToken() {
        $data = [
            'user_id' => $_SESSION['user_id'] ?? 0,
            'tenant_id' => $_SESSION['tenant_id'] ?? 0,
            'username' => $_SESSION['username'] ?? '',
            'timestamp' => time(),
            'expires' => time() + 300 // 5 minutos
        ];
        
        return Security::encryptUrl(json_encode($data));
    }
    
    /**
     * Manejar errores
     * 
     * @param Exception $e Excepción
     */
    private function handleError($e) {
        // Log del error
        $this->logError($e);
        
        if (Config::isDebug()) {
            // En desarrollo, mostrar error detallado
            echo "<h1>Error del Sistema</h1>";
            echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
            echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
            echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        } else {
            // En producción, redirigir a página de error
            $this->redirectToError('Ha ocurrido un error. Por favor, intente nuevamente.');
        }
    }
    
    /**
     * Redirigir a página de error
     * 
     * @param string $message Mensaje de error
     */
    private function redirectToError($message) {
        $_SESSION['error_message'] = $message;
        header('Location: ' . $this->generateUrl('core', 'error', 'show'));
        exit;
    }
    
    /**
     * Registrar acceso
     * 
     * @param string $type Tipo de URL
     */
    private function logAccess($type) {
        if (!Config::LOGS['enabled']) {
            return;
        }
        
        $logFile = Config::LOGS['path'] . 'access_' . date('Y-m-d') . '.log';
        
        $logEntry = sprintf(
            "[%s] %s - Module: %s, Controller: %s, Action: %s - IP: %s - User: %s\n",
            date('Y-m-d H:i:s'),
            $type,
            $this->module,
            $this->controller,
            $this->action,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SESSION['username'] ?? 'Guest'
        );
        
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Registrar error
     * 
     * @param Exception $e Excepción
     */
    private function logError($e) {
        if (!Config::LOGS['enabled']) {
            return;
        }
        
        $logFile = Config::LOGS['path'] . 'errors_' . date('Y-m-d') . '.log';
        
        $logEntry = sprintf(
            "[%s] ERROR - %s in %s:%d\nStack trace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Obtener información de la ruta actual
     * 
     * @return array
     */
    public function getCurrentRoute() {
        return [
            'module' => $this->module,
            'controller' => $this->controller,
            'action' => $this->action,
            'params' => $this->params
        ];
    }
}