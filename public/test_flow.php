<?php
/**
 * Test: Simular exactamente el flujo del DashboardController
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir constantes como en index.php
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', __DIR__);
define('STORAGE_PATH', BASE_PATH . '/storage');

// Cargar configuración
require_once CONFIG_PATH . '/app.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/security.php';
require_once APP_PATH . '/helpers/functions.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_name(Config::SESSION['name']);
    session_start();
}

echo "<h2>Test del flujo de renderizado</h2>";
echo "<p>Simulando DashboardController::render()</p>";

// Simular datos del viewData
$data = [
    'user' => ['nombres' => 'Super', 'apellidos' => 'Admin'],
    'tenant' => ['nombre_comercial' => 'DigiSports Admin'],
    'modules' => [],
    'notifications' => [],
    'notificationCount' => 0,
    'stats' => ['total_instalaciones' => 5],
    'charts' => [],
    'recentActivity' => [],
    'alerts' => [],
    'layout' => 'main',
    'title' => 'Dashboard',
    'pageTitle' => 'Panel de Control',
    'currentController' => 'Dashboard'
];

// SIMULAR EL MÉTODO render() de BaseController
echo "<hr><h3>Paso 1: Capturar contenido de la vista</h3>";

ob_start();
extract($data);

// Incluir la vista del dashboard (simplificada para test)
?>
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo $stats['total_instalaciones'] ?? 0 ?></h3>
                <p>Instalaciones</p>
            </div>
            <div class="icon"><i class="fas fa-building"></i></div>
            <a href="#" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>
<?php

$content = ob_get_clean();

echo "<p>✅ Contenido capturado: " . strlen($content) . " bytes</p>";
echo "<pre>" . htmlspecialchars(substr($content, 0, 200)) . "...</pre>";

echo "<hr><h3>Paso 2: Cargar layout con \$content</h3>";

// Agregar $content a $data
$data['content'] = $content;

// Verificar que el layout existe
$layoutPath = APP_PATH . '/views/layouts/' . $data['layout'] . '.php';
echo "<p>Layout path: " . $layoutPath . "</p>";
echo "<p>Existe: " . (file_exists($layoutPath) ? 'SI' : 'NO') . "</p>";

if (file_exists($layoutPath)) {
    echo "<hr><h3>Paso 3: Renderizar layout</h3>";
    
    // Extraer todas las variables incluyendo $content
    extract($data);
    
    // Verificar que $content existe
    echo "<p>\$content está definido: " . (isset($content) ? 'SI (' . strlen($content) . ' bytes)' : 'NO') . "</p>";
    
    echo "<hr><h3>Resultado Final:</h3>";
    
    // Incluir el layout
    require $layoutPath;
}
