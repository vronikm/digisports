<?php
/**
 * DigiSports - Bootstrap para PHPUnit
 *
 * Inicializa el entorno mínimo necesario para correr tests sin un servidor web.
 * Corre automáticamente antes de cualquier test suite.
 */

// ── Constantes base ───────────────────────────────────────────────────────────
define('BASE_PATH',   dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('APP_PATH',    BASE_PATH . '/app');

// ── Variables de entorno ──────────────────────────────────────────────────────
// phpunit.xml puede sobreescribir DB_NAME a 'digisports_test'
if (file_exists(BASE_PATH . '/.env')) {
    require_once CONFIG_PATH . '/env.php';
}

// ── Autoloader de Composer ────────────────────────────────────────────────────
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// ── Configuración principal (sin efectos secundarios HTTP) ────────────────────
require_once CONFIG_PATH . '/app.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/security.php';

// ── Helpers ───────────────────────────────────────────────────────────────────
require_once APP_PATH . '/helpers/functions.php';

// ── Sesión PHP (modo test) ────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    // Usar sesión en memoria para tests (sin fichero)
    ini_set('session.save_handler', 'files');
    session_start();
}
