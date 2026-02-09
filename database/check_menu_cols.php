<?php
$pdo = new PDO('mysql:host=localhost;dbname=digisports_core','root','');
$r = $pdo->query('DESCRIBE seguridad_menu');
echo "=== COLUMNAS seguridad_menu ===\n";
foreach($r->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo $c['Field'] . ' (' . $c['Type'] . ') ' . $c['Key'] . "\n";
}

echo "\n=== COLUMNAS seguridad_rol_menu ===\n";
$r = $pdo->query('DESCRIBE seguridad_rol_menu');
foreach($r->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo $c['Field'] . ' (' . $c['Type'] . ') ' . $c['Key'] . "\n";
}

echo "\n=== MUESTRA seguridad_menu (5 filas) ===\n";
$r = $pdo->query('SELECT * FROM seguridad_menu LIMIT 5');
$rows = $r->fetchAll(PDO::FETCH_ASSOC);
if (!empty($rows)) {
    echo implode(' | ', array_keys($rows[0])) . "\n";
    foreach($rows as $row) {
        echo implode(' | ', $row) . "\n";
    }
}
