<?php
/**
 * Ejecutar migración Fase 2 — Pagos y Entradas
 */
$pdo = new PDO('mysql:host=localhost;dbname=digisports_core', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$sql = file_get_contents(__DIR__ . '/fase2_pagos_entradas.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "=== Ejecutando migración Fase 2 ===\n\n";
$ok = 0;
$err = 0;

foreach ($statements as $stmt) {
    if (empty($stmt) || strpos($stmt, '--') === 0) continue;
    
    // Extraer nombre de tabla/vista
    preg_match('/(?:CREATE TABLE|CREATE OR REPLACE VIEW)\s+(?:IF NOT EXISTS\s+)?(\w+)/i', $stmt, $m);
    $nombre = $m[1] ?? 'sentencia';
    
    try {
        $pdo->exec($stmt);
        echo "  ✅ $nombre\n";
        $ok++;
    } catch (PDOException $e) {
        echo "  ❌ $nombre: " . $e->getMessage() . "\n";
        $err++;
    }
}

echo "\n🎉 Completado: $ok exitosos, $err errores\n";

// Verificar tablas creadas
echo "\n=== Verificación ===\n";
foreach (['instalaciones_entradas', 'instalaciones_entradas_tarifas'] as $t) {
    try {
        $cnt = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo "  ✅ $t ($cnt filas)\n";
    } catch (Exception $e) {
        echo "  ❌ $t no existe\n";
    }
}

try {
    $pdo->query("SELECT * FROM entradas LIMIT 1");
    echo "  ✅ Vista 'entradas' funcional\n";
} catch (Exception $e) {
    echo "  ❌ Vista 'entradas': " . $e->getMessage() . "\n";
}
