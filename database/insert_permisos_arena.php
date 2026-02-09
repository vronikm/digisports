<?php
/**
 * Asignar permisos de menú para los 3 nuevos ítems (110, 111, 112)
 */
$pdo = new PDO('mysql:host=localhost;dbname=digisports_core', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

try {
    $pdo->beginTransaction();

    // Roles que ya tienen acceso a ARENA (menús 1-5)
    $rolesArena = $pdo->query("
        SELECT DISTINCT rme_rol_id FROM seguridad_rol_menu 
        WHERE rme_menu_id IN (1, 2, 3, 4, 5)
    ")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Roles con acceso a ARENA: " . implode(', ', $rolesArena) . "\n";

    $insertPerm = $pdo->prepare("
        INSERT INTO seguridad_rol_menu (rme_rol_id, rme_menu_id, rme_puede_ver, rme_puede_acceder)
        VALUES (?, ?, 1, 1)
    ");

    $newMenuIds = [110, 111, 112];
    $count = 0;
    foreach ($rolesArena as $rolId) {
        foreach ($newMenuIds as $menuId) {
            // Verificar si ya existe
            $check = $pdo->prepare("SELECT COUNT(*) FROM seguridad_rol_menu WHERE rme_rol_id = ? AND rme_menu_id = ?");
            $check->execute([$rolId, $menuId]);
            if ($check->fetchColumn() == 0) {
                $insertPerm->execute([$rolId, $menuId]);
                echo "  ✅ Rol $rolId → Menú $menuId\n";
                $count++;
            } else {
                echo "  ⏩ Rol $rolId → Menú $menuId (ya existe)\n";
            }
        }
    }

    $pdo->commit();
    echo "\n🎉 $count permisos asignados correctamente.\n";

    // Verificación
    echo "\n=== Menús ARENA actualizados ===\n";
    $stmt = $pdo->query("
        SELECT m.men_id, m.men_label, m.men_ruta_modulo, m.men_ruta_controller, m.men_ruta_action, m.men_orden
        FROM seguridad_menu m
        WHERE m.men_modulo_id = 1 AND m.men_padre_id = 1
        ORDER BY m.men_orden
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  [{$row['men_id']}] {$row['men_label']} → {$row['men_ruta_modulo']}/{$row['men_ruta_controller']}/{$row['men_ruta_action']} (orden:{$row['men_orden']})\n";
    }

} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
}
