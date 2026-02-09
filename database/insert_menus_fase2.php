<?php
$pdo = new PDO('mysql:host=localhost;dbname=digisports_core','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Info actual
$r = $pdo->query('SELECT MAX(men_id) as mx FROM seguridad_menu');
echo 'Max ID: ' . $r->fetch(PDO::FETCH_ASSOC)['mx'] . PHP_EOL;

$r = $pdo->query("SELECT men_id, men_nombre FROM seguridad_menu WHERE men_nombre IN ('Pagos','Entradas','Tarifas de Entrada','Control de Acceso')");
$existing = $r->fetchAll(PDO::FETCH_ASSOC);
if (count($existing) > 0) {
    echo 'Ya existen: ';
    foreach($existing as $e) echo $e['men_id'].'='.$e['men_nombre'].' ';
    echo PHP_EOL;
    echo "Saltando insert de menus\n";
} else {
    // Buscar padres
    $r = $pdo->query("SELECT men_id, men_nombre FROM seguridad_menu WHERE men_nombre IN ('Reservas','Instalaciones') AND men_padre_id IS NULL");
    $padres = $r->fetchAll(PDO::FETCH_ASSOC);
    $padreReservas = null;
    $padreInstalaciones = null;
    foreach($padres as $p) {
        echo 'Padre: ' . $p['men_nombre'] . ' ID=' . $p['men_id'] . PHP_EOL;
        if ($p['men_nombre'] === 'Reservas') $padreReservas = $p['men_id'];
        if ($p['men_nombre'] === 'Instalaciones') $padreInstalaciones = $p['men_id'];
    }

    // Si no hay padres exactos, buscar con LIKE
    if (!$padreReservas) {
        $r = $pdo->query("SELECT men_id, men_nombre FROM seguridad_menu WHERE men_nombre LIKE '%reserva%' AND men_padre_id IS NULL LIMIT 1");
        $p = $r->fetch(PDO::FETCH_ASSOC);
        if ($p) { $padreReservas = $p['men_id']; echo "Padre reservas por LIKE: {$p['men_id']}={$p['men_nombre']}\n"; }
    }
    if (!$padreInstalaciones) {
        $r = $pdo->query("SELECT men_id, men_nombre FROM seguridad_menu WHERE men_nombre LIKE '%instalaci%' AND men_padre_id IS NULL LIMIT 1");
        $p = $r->fetch(PDO::FETCH_ASSOC);
        if ($p) { $padreInstalaciones = $p['men_id']; echo "Padre instalaciones por LIKE: {$p['men_id']}={$p['men_nombre']}\n"; }
    }

    // Mostrar estructura para debug
    $r = $pdo->query("SELECT men_id, men_nombre, men_padre_id, men_modulo FROM seguridad_menu WHERE men_modulo = 'ARENA' OR men_modulo IS NULL ORDER BY men_id");
    $all = $r->fetchAll(PDO::FETCH_ASSOC);
    echo "\nMenus ARENA:\n";
    foreach($all as $m) {
        if ($m['men_modulo'] === 'ARENA') {
            echo "  ID={$m['men_id']} padre={$m['men_padre_id']} nombre={$m['men_nombre']}\n";
        }
    }

    // Insertar menus nuevos
    $nextId = (int)$pdo->query('SELECT MAX(men_id)+1 FROM seguridad_menu')->fetchColumn();
    echo "\nInsertando desde ID=$nextId...\n";

    // Pagos (bajo Reservas)
    $pagoMenuId = $nextId;
    $pdo->exec("INSERT INTO seguridad_menu (men_id, men_nombre, men_descripcion, men_icono, men_url, men_padre_id, men_orden, men_modulo, men_estado) VALUES ($pagoMenuId, 'Pagos', 'Historial de cobros y pagos', 'fas fa-cash-register', 'reservas/pago/index', " . ($padreReservas ?: 'NULL') . ", 50, 'ARENA', 'ACTIVO')");
    echo "  Pagos ID=$pagoMenuId OK\n";

    // Entradas (bajo Instalaciones)
    $entradaMenuId = $nextId + 1;
    $pdo->exec("INSERT INTO seguridad_menu (men_id, men_nombre, men_descripcion, men_icono, men_url, men_padre_id, men_orden, men_modulo, men_estado) VALUES ($entradaMenuId, 'Entradas', 'Venta y control de entradas', 'fas fa-ticket-alt', 'instalaciones/entrada/index', " . ($padreInstalaciones ?: 'NULL') . ", 40, 'ARENA', 'ACTIVO')");
    echo "  Entradas ID=$entradaMenuId OK\n";

    // Tarifas Entrada (bajo Instalaciones)
    $tarifaMenuId = $nextId + 2;
    $pdo->exec("INSERT INTO seguridad_menu (men_id, men_nombre, men_descripcion, men_icono, men_url, men_padre_id, men_orden, men_modulo, men_estado) VALUES ($tarifaMenuId, 'Tarifas de Entrada', 'Configuración de precios de entrada', 'fas fa-tags', 'instalaciones/entrada/tarifas', " . ($padreInstalaciones ?: 'NULL') . ", 41, 'ARENA', 'ACTIVO')");
    echo "  Tarifas ID=$tarifaMenuId OK\n";

    // Control de Acceso (bajo Instalaciones)
    $accesoMenuId = $nextId + 3;
    $pdo->exec("INSERT INTO seguridad_menu (men_id, men_nombre, men_descripcion, men_icono, men_url, men_padre_id, men_orden, men_modulo, men_estado) VALUES ($accesoMenuId, 'Control de Acceso', 'Escaneo y validación de entradas', 'fas fa-qrcode', 'instalaciones/entrada/escanear', " . ($padreInstalaciones ?: 'NULL') . ", 42, 'ARENA', 'ACTIVO')");
    echo "  Control Acceso ID=$accesoMenuId OK\n";

    // Permisos para rol_id=1 (admin)
    $newIds = [$pagoMenuId, $entradaMenuId, $tarifaMenuId, $accesoMenuId];
    foreach ($newIds as $mid) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM seguridad_rol_menu WHERE rol_id = 1 AND men_id = ?");
        $check->execute([$mid]);
        if ((int)$check->fetchColumn() === 0) {
            $pdo->exec("INSERT INTO seguridad_rol_menu (rol_id, men_id, permisos) VALUES (1, $mid, 'CRUD')");
            echo "  Permiso rol=1 menu=$mid OK\n";
        }
    }

    echo "\n=== MENUS INSERTADOS EXITOSAMENTE ===\n";
}

// Verificación final
echo "\nVerificación:\n";
$r = $pdo->query("SELECT men_id, men_nombre, men_padre_id, men_url FROM seguridad_menu WHERE men_modulo = 'ARENA' ORDER BY men_id");
foreach ($r->fetchAll(PDO::FETCH_ASSOC) as $m) {
    echo "  [{$m['men_id']}] {$m['men_nombre']} → {$m['men_url']} (padre={$m['men_padre_id']})\n";
}
