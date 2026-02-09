<?php
/**
 * Insertar menús de Calendario y Monedero para módulo ARENA
 * y asignar permisos a los roles existentes
 */
$pdo = new PDO('mysql:host=localhost;dbname=digisports_core', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

try {
    $pdo->beginTransaction();

    // MAX men_id actual = 109, insertamos a partir de 110
    $newId1 = 110; // Calendario
    $newId2 = 111; // Monedero/Abonos
    $newId3 = 112; // Paquetes de Horas (sub del Monedero)
    
    // Módulo ARENA = mod_id 1, padre = men_id 1

    // Verificar que no existan ya
    $check = $pdo->prepare("SELECT COUNT(*) FROM seguridad_menu WHERE men_id IN (?, ?, ?)");
    $check->execute([$newId1, $newId2, $newId3]);
    if ($check->fetchColumn() > 0) {
        echo "Los menús ya existen (IDs 110-112). Saltando inserción.\n";
    } else {
        // INSERT Calendario (después de Reservas, orden 5)
        $stmt = $pdo->prepare("
            INSERT INTO seguridad_menu 
            (men_id, men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, 
             men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        
        $stmt->execute([
            $newId1, 1, 1, 'ITEM', 'Calendario', 'fas fa-calendar-alt',
            'instalaciones', 'calendario', 'index', 5
        ]);
        echo "✅ Menú 'Calendario' insertado (ID: $newId1)\n";
        
        // INSERT Monedero / Abonos (orden 6)
        $stmt->execute([
            $newId2, 1, 1, 'ITEM', 'Monedero / Abonos', 'fas fa-wallet',
            'reservas', 'abon', 'index', 6
        ]);
        echo "✅ Menú 'Monedero / Abonos' insertado (ID: $newId2)\n";
        
        // INSERT Paquetes de Horas (sub del padre Principal, orden 7)
        $stmt->execute([
            $newId3, 1, 1, 'ITEM', 'Paquetes de Horas', 'fas fa-box-open',
            'reservas', 'abon', 'paquetes', 7
        ]);
        echo "✅ Menú 'Paquetes de Horas' insertado (ID: $newId3)\n";
    }
    
    // Asignar permisos en seguridad_rol_menu
    echo "\n=== Asignando permisos a roles ===\n";
    
    // Ver estructura de seguridad_rol_menu
    $cols = $pdo->query("SHOW COLUMNS FROM seguridad_rol_menu");
    echo "Columnas seguridad_rol_menu: ";
    $colNames = [];
    while ($c = $cols->fetch(PDO::FETCH_ASSOC)) {
        $colNames[] = $c['Field'];
    }
    echo implode(', ', $colNames) . "\n";
    
    // Ver roles existentes
    $roles = $pdo->query("SELECT DISTINCT rm_rol_id FROM seguridad_rol_menu ORDER BY rm_rol_id")->fetchAll(PDO::FETCH_COLUMN);
    echo "Roles con permisos: " . implode(', ', $roles) . "\n";
    
    // Ver qué roles tienen acceso al menú ARENA actual (men_id=1 ó cualquier hijo)
    $rolesArena = $pdo->query("
        SELECT DISTINCT rm_rol_id FROM seguridad_rol_menu 
        WHERE rm_menu_id IN (1, 2, 3, 4, 5)
    ")->fetchAll(PDO::FETCH_COLUMN);
    echo "Roles con acceso a ARENA: " . implode(', ', $rolesArena) . "\n";
    
    // Insertar permisos para cada rol que tiene ARENA
    $insertPerm = $pdo->prepare("
        INSERT IGNORE INTO seguridad_rol_menu (rm_rol_id, rm_menu_id)
        VALUES (?, ?)
    ");
    
    $newMenuIds = [$newId1, $newId2, $newId3];
    $count = 0;
    foreach ($rolesArena as $rolId) {
        foreach ($newMenuIds as $menuId) {
            $insertPerm->execute([$rolId, $menuId]);
            $count++;
        }
    }
    echo "✅ $count permisos de menú asignados\n";
    
    $pdo->commit();
    echo "\n🎉 Todos los menús y permisos insertados correctamente.\n";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
}
