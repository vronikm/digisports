<?php
/**
 * Test: Exactamente como debug_render pero con configuración del sistema
 */
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', __DIR__);
define('STORAGE_PATH', BASE_PATH . '/storage');

// Cargar configuración (igual que index.php)
require_once CONFIG_PATH . '/app.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/security.php';
require_once APP_PATH . '/helpers/functions.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_name(Config::SESSION['name']);
    session_start();
}

// Variables para el layout
$title = 'Dashboard';
$pageTitle = 'Panel de Control';
$currentController = 'Dashboard';
$user = ['nombres' => 'Super', 'apellidos' => 'Admin', 'avatar' => null];
$tenant = ['nombre_comercial' => 'DigiSports Admin'];
$notifications = [];
$notificationCount = 0;
$modules = [];

// Contenido
$content = '
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>5</h3>
                <p>Instalaciones</p>
            </div>
            <div class="icon">
                <i class="fas fa-building"></i>
            </div>
            <a href="#" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>120</h3>
                <p>Reservas</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <a href="#" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>
';

// Cargar layout
include APP_PATH . '/views/layouts/main.php';
