<?php
/**
 * DigiSports - Punto de Entrada Principal
 * 
 * @package DigiSports
 * @version 1.0.0
 */

// Limpiar cualquier output previo y establecer encoding
ob_start();
header('Content-Type: text/html; charset=UTF-8');

// Definir constantes principales
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', __DIR__);
define('STORAGE_PATH', BASE_PATH . '/storage');

// Cargar autoloader si existe Composer
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Cargar configuración
require_once CONFIG_PATH . '/app.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/security.php';
require_once CONFIG_PATH . '/Router.php';

// Cargar funciones helper
require_once APP_PATH . '/helpers/functions.php';

// Cargar servicio de protección de datos (LOPDP Ecuador)
require_once APP_PATH . '/services/DataProtection.php';

// Iniciar sesión con configuración segura y forzar consistencia
if (session_status() === PHP_SESSION_NONE) {
    $sessionName = Config::SESSION['name'] ?? 'DIGISPORTS_SESSION';
    $sessionLifetime = Config::SESSION['lifetime'] ?? 1800;
    $sessionPath = Config::SESSION['path'] ?? '/';
    $sessionDomain = Config::SESSION['domain'] ?? '';
    $sessionSecure = Config::SESSION['secure'] ?? false;
    $sessionHttpOnly = Config::SESSION['httponly'] ?? true;
    $sessionSameSite = Config::SESSION['samesite'] ?? 'Strict';

    session_name($sessionName);
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => $sessionPath,
        'domain' => $sessionDomain,
        'secure' => $sessionSecure,
        'httponly' => $sessionHttpOnly,
        'samesite' => $sessionSameSite
    ]);
    ini_set('session.cookie_samesite', $sessionSameSite);
    ini_set('session.cookie_secure', $sessionSecure ? '1' : '0');
    ini_set('session.cookie_httponly', $sessionHttpOnly ? '1' : '0');
    session_start();
}

// Verificar modo mantenimiento
if (Config::get('CONFIGURACION.MANTENIMIENTO') === 'S' && !isAdmin()) {
    require_once APP_PATH . '/views/maintenance.php';
    exit;
}

// Inicializar router y despachar
try {
    $router = new Router();
    $router->dispatch();
    
} catch (Exception $e) {
    // Log del error fatal
    error_log(sprintf(
        "[%s] FATAL ERROR: %s in %s:%d\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));
    
    if (Config::isDebug()) {
        die("Error fatal: " . $e->getMessage());
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        require_once APP_PATH . '/views/errors/500.php';
    }
}

// Enviar output buffer
ob_end_flush();