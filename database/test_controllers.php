<?php
/**
 * Test funcional rápido — verificar que los controladores se instancian correctamente
 */
define('BASE_PATH', dirname(__DIR__));

// Bootstrap mínimo
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/security.php';
require_once BASE_PATH . '/app/helpers/functions.php';

// Simular sesión
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['tenant_id'] = 1;
$_SESSION['usu_tenant_id'] = 1;
$_SESSION['usu_rol_id'] = 1;
$_SESSION['usu_nombres'] = 'Test';
$_SESSION['usu_apellidos'] = 'User';

echo "=== Test de instanciación de controladores ===\n\n";

// Test 1: CalendarioController
echo "1. CalendarioController... ";
try {
    require_once BASE_PATH . '/app/controllers/ModuleController.php';
    require_once BASE_PATH . '/app/controllers/instalaciones/CalendarioController.php';
    $cal = new \App\Controllers\Instalaciones\CalendarioController();
    echo "✅ Instanciado OK\n";
    echo "   Clase: " . get_class($cal) . "\n";
    echo "   Padre: " . get_parent_class($cal) . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ Fatal: " . $e->getMessage() . "\n";
}

// Test 2: AbonController
echo "\n2. AbonController... ";
try {
    require_once BASE_PATH . '/app/controllers/reservas/AbonController.php';
    $abon = new \App\Controllers\Reservas\AbonController();
    echo "✅ Instanciado OK\n";
    echo "   Clase: " . get_class($abon) . "\n";
    echo "   Padre: " . get_parent_class($abon) . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ Fatal: " . $e->getMessage() . "\n";
}

// Test 3: Verificar helper url()
echo "\n3. URLs generadas:\n";
if (function_exists('url')) {
    echo "   Calendario: " . url('instalaciones', 'calendario', 'index') . "\n";
    echo "   Abonos:     " . url('reservas', 'abon', 'index') . "\n";
    echo "   Paquetes:   " . url('reservas', 'abon', 'paquetes') . "\n";
    echo "   Historial:  " . url('reservas', 'abon', 'historial') . "\n";
} else {
    echo "   (función url() no disponible en CLI)\n";
}

echo "\n🎉 Test completado.\n";
