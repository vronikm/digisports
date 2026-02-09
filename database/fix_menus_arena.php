<?php
/**
 * Verificar estado actual y reinsertar todo si es necesario
 */
$pdo = new PDO('mysql:host=localhost;dbname=digisports_core', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// 1. Verificar si los menús 110-112 existen
$check = $pdo->query("SELECT men_id, men_label FROM seguridad_menu WHERE men_id IN (110, 111, 112)");
$existing = $check->fetchAll(PDO::FETCH_ASSOC);
echo "Menús existentes (110-112): " . count($existing) . "\n";
foreach ($existing as $m) {
    echo "  ID:{$m['men_id']} → {$m['men_label']}\n";
}

// 2. Si no existen, insertar
if (count($existing) == 0) {
    echo "\nInsertando menús...\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO seguridad_menu 
        (men_id, men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, 
         men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    
    $stmt->execute([110, 1, 1, 'ITEM', 'Calendario', 'fas fa-calendar-alt', 'instalaciones', 'calendario', 'index', 5]);
    echo "  ✅ Calendario (110)\n";
    
    $stmt->execute([111, 1, 1, 'ITEM', 'Monedero / Abonos', 'fas fa-wallet', 'reservas', 'abon', 'index', 6]);
    echo "  ✅ Monedero / Abonos (111)\n";
    
    $stmt->execute([112, 1, 1, 'ITEM', 'Paquetes de Horas', 'fas fa-box-open', 'reservas', 'abon', 'paquetes', 7]);
    echo "  ✅ Paquetes de Horas (112)\n";
}

// 3. Ahora verificar que existen
$check2 = $pdo->query("SELECT men_id FROM seguridad_menu WHERE men_id IN (110, 111, 112)")->fetchAll(PDO::FETCH_COLUMN);
echo "\nMenús confirmados: " . implode(', ', $check2) . "\n";

// 4. Insertar permisos
echo "\nInsertando permisos...\n";
$rolesArena = $pdo->query("
    SELECT DISTINCT rme_rol_id FROM seguridad_rol_menu 
    WHERE rme_menu_id IN (1, 2, 3, 4, 5)
")->fetchAll(PDO::FETCH_COLUMN);

echo "Roles ARENA: " . implode(', ', $rolesArena) . "\n";

$insertPerm = $pdo->prepare("
    INSERT INTO seguridad_rol_menu (rme_rol_id, rme_menu_id, rme_puede_ver, rme_puede_acceder)
    VALUES (?, ?, 1, 1)
");

foreach ($rolesArena as $rolId) {
    foreach ([110, 111, 112] as $menuId) {
        $dup = $pdo->prepare("SELECT COUNT(*) FROM seguridad_rol_menu WHERE rme_rol_id = ? AND rme_menu_id = ?");
        $dup->execute([$rolId, $menuId]);
        if ($dup->fetchColumn() == 0) {
            $insertPerm->execute([$rolId, $menuId]);
            echo "  ✅ Rol $rolId → Menú $menuId\n";
        } else {
            echo "  ⏩ Rol $rolId → Menú $menuId (ya existe)\n";
        }
    }
}

// Resultado final
echo "\n=== Menú ARENA completo ===\n";
$stmt = $pdo->query("
    SELECT m.men_id, m.men_label, m.men_icono, m.men_ruta_modulo, m.men_ruta_controller, m.men_ruta_action, m.men_orden
    FROM seguridad_menu m
    WHERE m.men_modulo_id = 1 AND m.men_padre_id = 1
    ORDER BY m.men_orden
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  [{$row['men_id']}] {$row['men_icono']} {$row['men_label']} → {$row['men_ruta_modulo']}/{$row['men_ruta_controller']}/{$row['men_ruta_action']}\n";
}

echo "\n🎉 Completado.\n";
