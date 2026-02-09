<?php
$pdo = new PDO('mysql:host=localhost;dbname=digisports_core;charset=utf8mb4','root','');
$cols = $pdo->query("SHOW COLUMNS FROM instalaciones_reservas")->fetchAll(PDO::FETCH_COLUMN);
echo "instalaciones_reservas columns:\n" . implode(', ', $cols) . "\n\n";

// Check if reservas view has cancha_id
$stmt = $pdo->query("SELECT * FROM reservas LIMIT 0");
$numCols = $stmt->columnCount();
echo "reservas view columns:\n";
for ($i = 0; $i < $numCols; $i++) {
    $meta = $stmt->getColumnMeta($i);
    echo $meta['name'] . ', ';
}
echo "\n\n";

// Check eliminaciones query
echo "Testing: SELECT COUNT(*) FROM reservas WHERE estado != 'CANCELADA'\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado != 'CANCELADA'");
    echo "OK: " . $stmt->fetchColumn() . " rows\n";
} catch (Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
