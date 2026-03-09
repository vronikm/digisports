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
        // Verificar si es URL encriptada (primero GET, luego POST para URLs largas)
        if (isset($_GET['r'])) {
            $this->parseEncryptedUrl($_GET['r']);
        } elseif (isset($_POST['r'])) {
            // Soportar POST para URLs muy largas que excedan límite de GET
            $this->parseEncryptedUrl($_POST['r']);
        } elseif (isset($_POST['token'])) {
            // Soportar tokens cortos almacenados en sesión
            $encrypted = Security::getClientTokenData($_POST['token']);
            if ($encrypted) {
                $this->parseEncryptedUrl($encrypted);
            } else {
                $this->redirectToError('Token inválido o expirado');
            }
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
            // Limpiar la URL encriptada
            $encrypted = trim($encrypted);
            if (empty($encrypted)) {
                throw new Exception('URL encriptada vacía');
            }

            // DETECCIÓN DE URL TRUNCADA (DESHABILITADA - Ya no usamos comas)
            // El sistema sólo usa comas si realmente hay error de desencriptación
            // if (Security::isUrlTruncated($encrypted)) {
            //     throw new Exception('URL truncada...');
            // }
            
            $data = Security::decodeSecureUrl($encrypted);
            if (!$data || !is_array($data)) {
                throw new Exception('No se pudo desencriptar o URL expirada (> 8 horas)');
            }
            
            // Validar que tenga los campos mínimos requeridos
            if (!isset($data['c']) || !isset($data['a'])) {
                throw new Exception('Estructura de URL inválida (falta controlador o acción)');
            }
            
            // Validar módulo, controlador y acción
            $mod = $data['m'] ?? 'core';
            $ctrl = $data['c'] ?? 'Dashboard';
            $act = $data['a'] ?? 'index';
            
            if (!$this->isValidModule($mod)) {
                Security::logSecurityEvent('INVALID_MODULE', "Módulo: $mod");
                throw new Exception("Módulo '$mod' no permitido");
            }
            
            $controllerPath = Config::MODULES[$mod]['path'] . $this->toPascalCase($ctrl) . 'Controller.php';
            if (!file_exists(BASE_PATH . $controllerPath)) {
                Security::logSecurityEvent('INVALID_CONTROLLER', "Controlador: $ctrl en módulo: $mod");
                throw new Exception("Controlador '$ctrl' no encontrado en módulo '$mod'");
            }
            
            // Validar acción (solo letras y guiones bajos)
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $act)) {
                Security::logSecurityEvent('INVALID_ACTION', "Acción: $act");
                throw new Exception("Acción '$act' no permitida (caracteres inválidos)");
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
            $errorMsg = $e->getMessage();
            Security::logSecurityEvent('INVALID_URL', $errorMsg);
            
            // Mostrar mensaje más informativo
            $_SESSION['error_message'] = "Error de navegación: $errorMsg";
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
     * Convertir snake_case a PascalCase para nombres de controlador.
     * Permite que la función url() reciba 'seguridad_tabla' y el Router
     * encuentre el archivo 'SeguridadTablaController.php'.
     *
     * Ejemplos:
     *   'dashboard'              → 'Dashboard'
     *   'seguridad_tabla'        → 'SeguridadTabla'
     *   'seguridad_tabla_catalogo' → 'SeguridadTablaCatalogo'
     *
     * @param string $name Nombre en snake_case o PascalCase
     * @return string PascalCase
     */
    private function toPascalCase(string $name): string {
        return str_replace('_', '', ucwords($name, '_'));
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
            // Verificar autenticación para rutas protegidas
            if (!$this->isPublicRoute()) {
                $this->checkAuthentication();
            }
            
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
        return BASE_PATH . $modulePath . $this->toPascalCase($this->controller) . 'Controller.php';
    }
    
    /**
     * Obtener nombre de clase del controlador
     * 
     * @return string Nombre de la clase
     */
    private function getControllerClass() {
        $namespace = Config::MODULES[$this->module]['namespace'] ?? '';
        $class = $this->toPascalCase($this->controller) . 'Controller';
        return $namespace ? $namespace . '\\' . $class : $class;
    }
    
    /**
     * Verificar autenticación
     * La sesión ya está iniciada por Security::init() en index.php
     */
    private function checkAuthentication() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['tenant_id'])) {
            // Guardar URL solicitada para redirigir después del login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            header('Location: ' . $this->generateUrl('core', 'auth', 'login'));
            exit;
        }

        // Verificar que el tenant esté activo
        $this->checkTenantStatus();
    }
    
    /**
     * Verificar estado del tenant y que tenga el módulo solicitado contratado.
     */
    private function checkTenantStatus() {
        $db       = Database::getInstance()->getConnection();
        $tenantId = (int)$_SESSION['tenant_id'];

        $stmt = $db->prepare("
            SELECT ten_estado_suscripcion, ten_fecha_vencimiento
            FROM seguridad_tenants
            WHERE ten_tenant_id = ?
        ");
        $stmt->execute([$tenantId]);
        $tenant = $stmt->fetch();

        if (!$tenant) {
            $this->redirectToError('Tenant no encontrado');
            return;
        }

        // ── Estado de suscripción ─────────────────────────────────────
        if ($tenant['ten_estado_suscripcion'] === 'SUSPENDIDA') {
            $this->redirectToError('Su suscripción ha sido suspendida. Contacte a soporte.');
            return;
        }

        if ($tenant['ten_estado_suscripcion'] === 'VENCIDA') {
            if ($this->controller !== 'Suscripcion') {
                header('Location: ' . $this->generateUrl('core', 'suscripcion', 'renovar'));
                exit;
            }
        }

        // ── Verificar módulo contratado ───────────────────────────────
        // El módulo 'core' siempre es accesible (login, hub, perfil, etc.)
        if ($this->module === 'core') {
            return;
        }

        try {
            $stmt = $db->prepare("
                SELECT stm.tmo_id
                FROM seguridad_tenant_modulos stm
                JOIN seguridad_modulos sm ON sm.mod_id = stm.tmo_modulo_id
                WHERE stm.tmo_tenant_id = ?
                  AND sm.mod_ruta_modulo = ?
                  AND stm.tmo_activo = 'S'
                  AND stm.tmo_estado = 'ACTIVO'
                  AND sm.mod_requiere_licencia = 1
                LIMIT 1
            ");
            $stmt->execute([$tenantId, $this->module]);

            if (!$stmt->fetch()) {
                // El módulo existe en la plataforma pero el tenant no lo tiene contratado
                $_SESSION['flash_error'] = 'No tiene acceso a este módulo. Contacte a soporte para contratarlo.';
                header('Location: ' . $this->generateUrl('core', 'hub', 'index'));
                exit;
            }
        } catch (\Throwable $e) {
            // Si la BD falla en este punto, permitir acceso (fail open)
            error_log('[Router] checkTenantStatus modulo: BD error — ' . $e->getMessage());
        }
    }
    
    /**
     * Verificar si es una ruta pública (no requiere autenticación)
     * La comparación es case-insensitive para soportar URLs encriptadas y estándar
     * @return bool
     */
    private function isPublicRoute() {
        $publicRoutes = [
            'auth' => [
                'login', 'authenticate',
                'twofactorauth', 'validate2fa', 'resend2fa',
                'recuperar', 'enviarrecuperacion',
                'reset', 'procesarreset',
                'register', 'creartenant',
                'logout',
            ],
            'welcome' => ['index', 'status'],
            'error'   => ['show', 'notfound', 'forbidden', 'servererror'],
        ];

        $controller = strtolower($this->controller);
        $action     = strtolower($this->action);

        return isset($publicRoutes[$controller]) &&
               in_array($action, $publicRoutes[$controller]);
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