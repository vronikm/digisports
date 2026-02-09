<?php
/**
 * Script para agregar ítems de menú para Calendario y Abonos/Monedero
 */
$pdo = new PDO('mysql:host=localhost;dbname=digisports_core', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// 1. Buscar módulos relevantes
echo "=== Módulos disponibles ===\n";
$stmt = $pdo->query("SELECT mod_id, mod_codigo, mod_nombre FROM seguridad_modulos ORDER BY mod_id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  ID: {$row['mod_id']} | Código: {$row['mod_codigo']} | Nombre: {$row['mod_nombre']}\n";
}

// 2. Ver estructura de seguridad_menu
echo "\n=== Columnas de seguridad_menu ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM seguridad_menu");
while ($c = $cols->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$c['Field']} ({$c['Type']}) {$c['Null']} | Default: {$c['Default']}\n";
}

// 3. Ver menús existentes del módulo Arena/Instalaciones/Reservas
echo "\n=== Menús existentes (módulos Arena/Reservas/ABONOS) ===\n";
$stmt = $pdo->query("
    SELECT m.men_id, m.men_modulo_id, m.men_padre_id, m.men_label, m.men_ruta_modulo, m.men_ruta_controller, m.men_ruta_action, m.men_icono, m.men_orden, m.men_tipo, m.men_activo,
           md.mod_codigo
    FROM seguridad_menu m
    LEFT JOIN seguridad_modulos md ON m.men_modulo_id = md.mod_id
    WHERE md.mod_codigo IN ('ARENA', 'RESERVAS', 'ABONOS')
       OR m.men_label LIKE '%Calendar%'
       OR m.men_label LIKE '%Abon%'
       OR m.men_label LIKE '%Monedero%'
    ORDER BY m.men_modulo_id, m.men_orden
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $padre = $row['men_padre_id'] ? "(padre:{$row['men_padre_id']})" : "(raíz)";
    echo "  ID:{$row['men_id']} Mod:{$row['mod_codigo']}({$row['men_modulo_id']}) {$padre} | {$row['men_label']} | {$row['men_ruta_modulo']}/{$row['men_ruta_controller']}/{$row['men_ruta_action']} | Orden:{$row['men_orden']} | Activo:{$row['men_activo']}\n";
}

// 4. Ver MAX men_id
$max = $pdo->query("SELECT MAX(men_id) as max_id, MAX(men_orden) as max_orden FROM seguridad_menu")->fetch(PDO::FETCH_ASSOC);
echo "\n=== MAX men_id: {$max['max_id']}, MAX men_orden: {$max['max_orden']} ===\n";

echo "\nDiagnóstico completado.\n";
