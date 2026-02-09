<?php
$pdo = new PDO('mysql:host=localhost;dbname=digisports_core','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Verificar si ya existen
$r = $pdo->query("SELECT men_id, men_label FROM seguridad_menu WHERE men_label IN ('Pagos','Entradas','Tarifas de Entrada','Control de Acceso')");
$existing = $r->fetchAll(PDO::FETCH_ASSOC);
if (count($existing) > 0) {
    echo "Ya existen:\n";
    foreach($existing as $e) echo "  [{$e['men_id']}] {$e['men_label']}\n";
    echo "Saltando...\n";
} else {
    // modulo_id = 1 (ARENA module)
    $moduloId = 1;

    // Buscar IDs de headers/padres existentes
    $r = $pdo->query("SELECT men_id, men_label FROM seguridad_menu WHERE men_modulo_id = 1 ORDER BY men_id");
    $menus = $r->fetchAll(PDO::FETCH_ASSOC);
    echo "Menus existentes ARENA:\n";
    foreach($menus as $m) echo "  [{$m['men_id']}] {$m['men_label']}\n";

    // El padre es ID=1 (HEADER Principal)
    $padreId = 1;

    $nextId = (int)$pdo->query('SELECT MAX(men_id)+1 FROM seguridad_menu')->fetchColumn();
    echo "\nInsertando desde ID=$nextId...\n";

    // Menu: Pagos
    $pagosId = $nextId;
    $stmt = $pdo->prepare("INSERT INTO seguridad_menu 
        (men_id, men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, 
         men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo)
        VALUES (?, ?, ?, 'ITEM', ?, ?, ?, ?, ?, ?, 1)");
    $stmt->execute([$pagosId, $moduloId, $padreId, 'Pagos', 'fas fa-cash-register', 'reservas', 'pago', 'index', 10]);
    echo "  [{$pagosId}] Pagos OK\n";

    // Menu: Entradas
    $entradasId = $nextId + 1;
    $stmt->execute([$entradasId, $moduloId, $padreId, 'Entradas', 'fas fa-ticket-alt', 'instalaciones', 'entrada', 'index', 11]);
    echo "  [{$entradasId}] Entradas OK\n";

    // Menu: Tarifas de Entrada
    $tarifasId = $nextId + 2;
    $stmt->execute([$tarifasId, $moduloId, $padreId, 'Tarifas de Entrada', 'fas fa-tags', 'instalaciones', 'entrada', 'tarifas', 12]);
    echo "  [{$tarifasId}] Tarifas de Entrada OK\n";

    // Menu: Control de Acceso
    $accesoId = $nextId + 3;
    $stmt->execute([$accesoId, $moduloId, $padreId, 'Control de Acceso', 'fas fa-qrcode', 'instalaciones', 'entrada', 'escanear', 13]);
    echo "  [{$accesoId}] Control de Acceso OK\n";

    // Permisos para rol_id=1
    $stmtPerm = $pdo->prepare("INSERT INTO seguridad_rol_menu (rme_rol_id, rme_menu_id, rme_puede_ver, rme_puede_acceder) VALUES (1, ?, 1, 1)");
    foreach ([$pagosId, $entradasId, $tarifasId, $accesoId] as $mid) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM seguridad_rol_menu WHERE rme_rol_id = 1 AND rme_menu_id = ?");
        $check->execute([$mid]);
        if ((int)$check->fetchColumn() === 0) {
            $stmtPerm->execute([$mid]);
            echo "  Permiso rol=1 menu=$mid OK\n";
        }
    }

    echo "\n=== MENUS FASE 2 INSERTADOS ===\n";
}

// Verificación final
echo "\n=== TODOS LOS MENUS ARENA ===\n";
$r = $pdo->query("SELECT men_id, men_label, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_padre_id, men_orden FROM seguridad_menu WHERE men_modulo_id = 1 ORDER BY men_orden, men_id");
foreach ($r->fetchAll(PDO::FETCH_ASSOC) as $m) {
    echo "  [{$m['men_id']}] {$m['men_label']} → {$m['men_ruta_modulo']}/{$m['men_ruta_controller']}/{$m['men_ruta_action']} (padre={$m['men_padre_id']}, orden={$m['men_orden']})\n";
}
