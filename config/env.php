<?php
/**
 * DigiSports - Cargador de Variables de Entorno
 * Parsea el archivo .env y carga los valores en $_ENV
 * Debe ser el primer archivo cargado en el bootstrap
 */

defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__));

$envFile = BASE_PATH . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Ignorar comentarios y líneas vacías
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        // Parsear KEY=VALUE
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Eliminar comillas opcionales del valor
            if (strlen($value) >= 2 &&
                (($value[0] === '"' && $value[-1] === '"') ||
                 ($value[0] === "'" && $value[-1] === "'"))) {
                $value = substr($value, 1, -1);
            }
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

/**
 * Obtener valor de variable de entorno con fallback
 * @param string $key Nombre de la variable
 * @param mixed $default Valor por defecto
 * @return mixed
 */
function env(string $key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key);
    return ($value === false || $value === null || $value === '') ? $default : $value;
}
