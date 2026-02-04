<?php
/**
 * Test de diagnóstico para el Dashboard
 * Eliminar después de depurar
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir constantes principales
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', __DIR__);
define('STORAGE_PATH', BASE_PATH . '/storage');

// Cargar configuración
require_once CONFIG_PATH . '/app.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/security.php';
require_once CONFIG_PATH . '/Router.php';
require_once APP_PATH . '/helpers/functions.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_name(Config::SESSION['name']);
    session_start();
}

echo "<h1>Diagnóstico del Dashboard</h1>";

echo "<h2>1. Estado de Sesión</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NO DEFINIDO') . "\n";
echo "tenant_id: " . ($_SESSION['tenant_id'] ?? 'NO DEFINIDO') . "\n";
echo "username: " . ($_SESSION['username'] ?? 'NO DEFINIDO') . "\n";
echo "isAuthenticated(): " . (isAuthenticated() ? 'SI' : 'NO') . "\n";
echo "</pre>";

echo "<h2>2. Información del Router</h2>";
$router = new Router();
echo "<pre>";
print_r($_GET);
echo "</pre>";

echo "<h2>3. Test de URL generada para Dashboard</h2>";
$dashboardUrl = url('core', 'dashboard');
echo "<pre>URL Dashboard: " . htmlspecialchars($dashboardUrl) . "</pre>";

echo "<h2>4. Test de carga del DashboardController</h2>";
$controllerPath = BASE_PATH . '/app/controllers/core/DashboardController.php';
echo "<pre>";
echo "Path: " . $controllerPath . "\n";
echo "Existe: " . (file_exists($controllerPath) ? 'SI' : 'NO') . "\n";
echo "</pre>";

if (file_exists($controllerPath)) {
    echo "<h3>Intentando cargar el controlador...</h3>";
    
    try {
        require_once BASE_PATH . '/app/controllers/BaseController.php';
        require_once $controllerPath;
        
        echo "<pre>Controlador cargado correctamente.</pre>";
        
        // Verificar clase
        $className = 'App\\Controllers\\Core\\DashboardController';
        echo "<pre>Clase existe: " . (class_exists($className) ? 'SI' : 'NO') . "</pre>";
        
        // Intentar instanciar si hay sesión
        if (isAuthenticated()) {
            echo "<h3>Intentando instanciar...</h3>";
            try {
                $dashboard = new $className();
                echo "<pre>Instancia creada correctamente.</pre>";
            } catch (Exception $e) {
                echo "<pre style='color:red'>ERROR al instanciar: " . $e->getMessage() . "</pre>";
                echo "<pre>" . $e->getTraceAsString() . "</pre>";
            }
        } else {
            echo "<pre style='color:orange'>No autenticado - no se puede probar la instancia</pre>";
        }
        
    } catch (Exception $e) {
        echo "<pre style='color:red'>ERROR: " . $e->getMessage() . "</pre>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}

echo "<h2>5. Test de Base de Datos</h2>";
try {
    $db = Database::getInstance()->getConnection();
    echo "<pre>Conexión: OK</pre>";
    
    // Verificar tablas existentes
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<pre>Tablas en BD:\n" . implode("\n", $tables) . "</pre>";
    
} catch (Exception $e) {
    echo "<pre style='color:red'>ERROR BD: " . $e->getMessage() . "</pre>";
}

echo "<h2>6. Test de función getTenantId()</h2>";
echo "<pre>";
$tid = getTenantId();
echo "getTenantId(): " . ($tid ?? 'NULL') . "\n";
echo "Tipo: " . gettype($tid) . "\n";
echo "</pre>";

echo "<hr><p><a href='" . htmlspecialchars($dashboardUrl) . "'>Ir al Dashboard</a></p>";
