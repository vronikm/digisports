<?php
/**
 * Insertar menú de Reportes Arena (Fase 3) — men_id = 117
 * Ejecutar: php database/insert_menus_fase3.php
 */

$host = 'localhost';
$dbname = 'digisports_core';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== Inserción de Menús Fase 3 ===\n\n";

    // Verificar estructura de la tabla
    $cols = $pdo->query("SHOW COLUMNS FROM seguridad_menu")->fetchAll(PDO::FETCH_COLUMN);
    echo "Columnas: " . implode(', ', $cols) . "\n\n";

    // Verificar si ya existe el menú 117
    $stmt = $pdo->prepare("SELECT men_id FROM seguridad_menu WHERE men_id = 117");
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "⚠ Menú 117 ya existe, eliminando para reinsertar...\n";
        $pdo->exec("DELETE FROM seguridad_menu WHERE men_id = 117");
        $pdo->exec("DELETE FROM seguridad_rol_menu WHERE rme_menu_id = 117");
    }

    // Insertar menú Reportes Arena (bajo módulo ARENA = 4, padre = 1 que es header ARENA)
    $sql = "INSERT INTO seguridad_menu (
        men_id, men_modulo_id, men_padre_id, men_tipo, men_label, men_icono,
        men_ruta_modulo, men_ruta_controller, men_ruta_action,
        men_url_custom, men_badge, men_badge_tipo,
        men_orden, men_activo, men_visible_rol, men_tenant_id
    ) VALUES (
        117, 4, 1, 'ITEM', 'Reportes Arena', 'fas fa-chart-line',
        'instalaciones', 'reporteArena', 'index',
        NULL, NULL, NULL,
        90, 1, NULL, NULL
    )";
    $pdo->exec($sql);
    echo "✅ Menú 117 insertado: Reportes Arena\n";

    // Insertar permisos para roles 1 (SuperAdmin) y 2 (Admin)
    $roles = [1, 2];
    foreach ($roles as $rolId) {
        $stmt = $pdo->prepare("SELECT rme_id FROM seguridad_rol_menu WHERE rme_rol_id = ? AND rme_menu_id = 117");
        $stmt->execute([$rolId]);
        if (!$stmt->fetch()) {
            $pdo->prepare("INSERT INTO seguridad_rol_menu (rme_rol_id, rme_menu_id, rme_puede_ver, rme_puede_acceder) VALUES (?, 117, 1, 1)")
                ->execute([$rolId]);
            echo "✅ Permiso rol {$rolId} → menú 117\n";
        } else {
            echo "⚠ Permiso rol {$rolId} → menú 117 ya existe\n";
        }
    }

    echo "\n=== Menús Arena actuales ===\n";
    $stmt = $pdo->query("SELECT men_id, men_label, men_ruta_controller, men_ruta_action, men_orden FROM seguridad_menu WHERE men_modulo_id = 4 ORDER BY men_orden");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo "  [{$row['men_id']}] {$row['men_label']} → {$row['men_ruta_controller']}/{$row['men_ruta_action']} (orden: {$row['men_orden']})\n";
    }

    echo "\n✅ Fase 3 menús completados\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
