<?php
/**
 * Debug: Ver estructura de tabla clientes
 */
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Estructura de tabla CLIENTES</h2>";
echo "<pre>";

try {
    $stmt = $db->query("DESCRIBE clientes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columnas encontradas:\n";
    echo str_pad("FIELD", 30) . str_pad("TYPE", 40) . str_pad("NULL", 6) . "DEFAULT\n";
    echo str_repeat("-", 100) . "\n";
    
    foreach ($columns as $col) {
        echo str_pad($col['Field'], 30) . str_pad($col['Type'], 40) . str_pad($col['Null'], 6) . ($col['Default'] ?? 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "</pre>";
