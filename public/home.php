<?php
/**
 * DigiSports - Dashboard Simple
 * Acceso directo sin encriptación de rutas
 */

session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: /digiSports/public/auth/login');
    exit;
}

// Incluir configuración y base de datos
define('BASE_PATH', dirname(dirname(__FILE__)));
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/helpers/functions.php';
require_once BASE_PATH . '/app/controllers/BaseController.php';
require_once BASE_PATH . '/app/controllers/core/DashboardController.php';

// Crear instancia y ejecutar
try {
    $controller = new \App\Controllers\Core\DashboardController();
    $controller->index();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
