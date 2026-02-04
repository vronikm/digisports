<?php
/**
 * Debug: Ver estructura de tabla reservas
 */
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Estructura de tabla RESERVAS</h2>";
echo "<pre>";

try {
    $stmt = $db->query("DESCRIBE reservas");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columnas encontradas:\n";
    echo str_pad("FIELD", 25) . str_pad("TYPE", 40) . str_pad("NULL", 6) . "DEFAULT\n";
    echo str_repeat("-", 90) . "\n";
    
    foreach ($columns as $col) {
        echo str_pad($col['Field'], 25) . str_pad($col['Type'], 40) . str_pad($col['Null'], 6) . ($col['Default'] ?? 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "</pre>";
