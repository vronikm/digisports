<?php
/**
 * Verificar estructura de base de datos
 */
define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');

require_once CONFIG_PATH . '/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Verificación de Tablas</h2>";

// Verificar tablas principales
$tablas = ['canchas', 'instalaciones', 'reservas', 'tarifas', 'mantenimientos'];

foreach ($tablas as $tabla) {
    $stmt = $db->query("SHOW TABLES LIKE '{$tabla}'");
    $existe = $stmt->fetch();
    echo "<p><strong>{$tabla}</strong>: " . ($existe ? "✅ Existe" : "❌ No existe") . "</p>";
}

echo "<hr><h3>Estructura de canchas (si existe)</h3>";
try {
    $stmt = $db->query("DESCRIBE canchas");
    $columnas = $stmt->fetchAll();
    echo "<pre>";
    foreach ($columnas as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "<hr><h3>Estructura de instalaciones (si existe)</h3>";
try {
    $stmt = $db->query("DESCRIBE instalaciones");
    $columnas = $stmt->fetchAll();
    echo "<pre>";
    foreach ($columnas as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "<hr><h3>Estructura de reservas (si existe)</h3>";
try {
    $stmt = $db->query("DESCRIBE reservas");
    $columnas = $stmt->fetchAll();
    echo "<pre>";
    foreach ($columnas as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
