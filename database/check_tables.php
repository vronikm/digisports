<?php
$pdo = new PDO('mysql:host=localhost;dbname=digisports_core;charset=utf8mb4', 'root', '');
$tables = ['instalaciones_mantenimientos'];
foreach ($tables as $t) {
    echo "=== $t ===\n";
    try {
        $r = $pdo->query("SHOW CREATE TABLE $t");
        $d = $r->fetch(PDO::FETCH_ASSOC);
        echo $d['Create Table'] . "\n\n";
    } catch (Exception $e) {
        echo "NOT FOUND: " . $e->getMessage() . "\n\n";
    }
}
