<?php
/**
 * DigiSports - Diagnóstico de Dashboard
 */

session_start();

define('BASE_PATH', dirname(dirname(__FILE__)));

echo "=== DIAGNÓSTICO DASHBOARD ===\n\n";

// 1. Verificar sesión
echo "1. SESIÓN:\n";
if (isset($_SESSION['user_id'])) {
    echo "   ✓ Autenticado\n";
    echo "   - User ID: {$_SESSION['user_id']}\n";
    echo "   - Username: {$_SESSION['username']}\n";
    echo "   - Tenant: {$_SESSION['tenant_id']}\n";
} else {
    echo "   ❌ No autenticado\n";
    exit;
}

// 2. Verificar archivos
echo "\n2. ARCHIVOS NECESARIOS:\n";
$files = [
    'config/app.php',
    'config/database.php',
    'app/helpers/functions.php',
    'app/controllers/BaseController.php',
];

foreach ($files as $file) {
    $path = BASE_PATH . '/' . $file;
    echo "   " . (file_exists($path) ? "✓" : "❌") . " $file\n";
}

// 3. Incluir archivos
echo "\n3. INCLUYENDO ARCHIVOS:\n";
try {
    require_once BASE_PATH . '/config/app.php';
    echo "   ✓ app.php\n";
} catch (Exception $e) {
    echo "   ❌ app.php: " . $e->getMessage() . "\n";
}

try {
    require_once BASE_PATH . '/config/database.php';
    echo "   ✓ database.php\n";
} catch (Exception $e) {
    echo "   ❌ database.php: " . $e->getMessage() . "\n";
}

try {
    require_once BASE_PATH . '/app/helpers/functions.php';
    echo "   ✓ functions.php\n";
} catch (Exception $e) {
    echo "   ❌ functions.php: " . $e->getMessage() . "\n";
}

try {
    require_once BASE_PATH . '/app/controllers/BaseController.php';
    echo "   ✓ BaseController.php\n";
} catch (Exception $e) {
    echo "   ❌ BaseController.php: " . $e->getMessage() . "\n";
}

// 4. Verificar base de datos
echo "\n4. BASE DE DATOS:\n";
try {
    global $db;
    if ($db && $db->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
        echo "   ✓ Conectado\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 5. Intentar crear controlador
echo "\n5. CREAR CONTROLADOR:\n";
try {
    require_once BASE_PATH . '/app/controllers/core/DashboardController.php';
    echo "   ✓ DashboardController cargado\n";
    
    $controller = new \App\Controllers\Core\DashboardController();
    echo "   ✓ Instancia creada\n";
    
    // Intentar llamar index
    echo "   ✓ Llamando index()...\n";
    $controller->index();
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

?>
