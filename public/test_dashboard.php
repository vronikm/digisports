<?php
/**
 * Test simple para verificar el dashboard
 * Acceder: http://localhost/digiSports/public/test_dashboard.php
 */

// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir constantes
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', __DIR__);
define('STORAGE_PATH', BASE_PATH . '/storage');

echo "<h1>Test Dashboard DigiSports</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
    h1 { color: #2563eb; }
    h2 { color: #1e40af; margin-top: 30px; }
    .success { color: #16a34a; }
    .error { color: #dc2626; }
    pre { background: #f1f5f9; padding: 15px; border-radius: 8px; overflow-x: auto; }
    .card { background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 10px 0; }
    .btn { display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
    .btn:hover { background: #1e40af; }
</style>";

// Cargar archivos de configuración
try {
    require_once CONFIG_PATH . '/app.php';
    echo "<p class='success'>✅ app.php cargado</p>";
} catch (Throwable $e) {
    echo "<p class='error'>❌ Error app.php: " . $e->getMessage() . "</p>";
    exit;
}

try {
    require_once CONFIG_PATH . '/database.php';
    echo "<p class='success'>✅ database.php cargado</p>";
} catch (Throwable $e) {
    echo "<p class='error'>❌ Error database.php: " . $e->getMessage() . "</p>";
    exit;
}

try {
    require_once CONFIG_PATH . '/security.php';
    echo "<p class='success'>✅ security.php cargado</p>";
} catch (Throwable $e) {
    echo "<p class='error'>❌ Error security.php: " . $e->getMessage() . "</p>";
    exit;
}

try {
    require_once APP_PATH . '/helpers/functions.php';
    echo "<p class='success'>✅ functions.php cargado</p>";
} catch (Throwable $e) {
    echo "<p class='error'>❌ Error functions.php: " . $e->getMessage() . "</p>";
    exit;
}

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_name(Config::SESSION['name']);
    session_start();
}

echo "<h2>Estado de Sesión</h2>";
echo "<div class='card'>";
echo "<pre>";
echo "isAuthenticated(): " . (isAuthenticated() ? 'SI' : 'NO') . "\n";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NO DEFINIDO') . "\n";
echo "tenant_id: " . ($_SESSION['tenant_id'] ?? 'NO DEFINIDO') . "\n";
echo "username: " . ($_SESSION['username'] ?? 'NO DEFINIDO') . "\n";
echo "nombres: " . ($_SESSION['nombres'] ?? 'NO DEFINIDO') . "\n";
echo "role: " . ($_SESSION['role'] ?? 'NO DEFINIDO') . "\n";
echo "</pre>";
echo "</div>";

// Cargar controladores
echo "<h2>Cargando Controladores</h2>";

try {
    require_once APP_PATH . '/controllers/BaseController.php';
    echo "<p class='success'>✅ BaseController cargado</p>";
} catch (Throwable $e) {
    echo "<p class='error'>❌ Error BaseController: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

try {
    require_once APP_PATH . '/controllers/core/DashboardController.php';
    echo "<p class='success'>✅ DashboardController cargado</p>";
} catch (Throwable $e) {
    echo "<p class='error'>❌ Error DashboardController: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Si está autenticado, intentar crear instancia
if (isAuthenticated()) {
    echo "<h2>Instanciando DashboardController</h2>";
    
    try {
        $className = 'App\\Controllers\\Core\\DashboardController';
        
        if (!class_exists($className)) {
            echo "<p class='error'>❌ La clase {$className} no existe</p>";
        } else {
            echo "<p class='success'>✅ Clase {$className} existe</p>";
            
            echo "<p>Creando instancia...</p>";
            $dashboard = new $className();
            echo "<p class='success'>✅ Instancia creada correctamente</p>";
            
            // Intentar llamar al método index
            echo "<h2>Ejecutando index()...</h2>";
            echo "<div class='card' style='max-height: 600px; overflow: auto;'>";
            $dashboard->index();
            echo "</div>";
        }
    } catch (Throwable $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
} else {
    echo "<h2>⚠️ No autenticado</h2>";
    echo "<div class='card'>";
    echo "<p>Por favor inicie sesión primero en la aplicación.</p>";
    echo "<a href='/digiSports/public/' class='btn'>Ir a la aplicación</a>";
    echo "</div>";
}

// Acciones rápidas
echo "<h2>Acciones Rápidas</h2>";
echo "<div class='card'>";
echo "<a href='/digiSports/public/' class='btn'>🏠 Ir a Home</a>";
echo "<a href='/digiSports/public/test_dashboard.php' class='btn'>🔄 Recargar Test</a>";
echo "<a href='/digiSports/public/diagnostico.php' class='btn'>🔍 Diagnóstico</a>";
echo "</div>";
