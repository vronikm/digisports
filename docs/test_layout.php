<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', 'c:/wamp64/www/digiSports');
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');

require_once CONFIG_PATH . '/app.php';
require_once CONFIG_PATH . '/security.php';
require_once APP_PATH . '/helpers/functions.php';

$title = 'Test';
$content = '<p>Contenido de prueba</p>';
$tenant = [];
$user = ['nombres' => 'Test', 'apellidos' => 'User'];
$modules = [];
$notifications = [];
$notificationCount = 0;
$currentController = 'Test';
$pageTitle = 'Página de Prueba';

echo "Intentando cargar layout...\n";

include APP_PATH . '/views/layouts/main.php';

echo "\nLayout cargado exitosamente.\n";
