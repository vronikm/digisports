<?php
require dirname(__DIR__) . '/config/database.php';
$db = Database::getInstance()->getConnection();

echo "=== SEGURIDAD_MODULOS ===\n";
$cols = $db->query("SHOW COLUMNS FROM seguridad_modulos")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";

echo "\n=== SEGURIDAD_TENANTS ===\n";
$cols = $db->query("SHOW COLUMNS FROM seguridad_tenants")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";

echo "\n=== RESERVAS (tipo) ===\n";
$r = $db->query("SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='reservas'")->fetch();
echo "  reservas = " . ($r ? $r['TABLE_TYPE'] : 'NO EXISTE') . "\n";
$r2 = $db->query("SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='instalaciones_reservas'")->fetch();
echo "  instalaciones_reservas = " . ($r2 ? $r2['TABLE_TYPE'] : 'NO EXISTE') . "\n";

echo "\n=== RESERVAS_TARIFAS ===\n";
$r3 = $db->query("SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='reservas_tarifas'")->fetch();
echo "  reservas_tarifas = " . ($r3 ? $r3['TABLE_TYPE'] : 'NO EXISTE') . "\n";
// Buscar nombre correcto
$tables = $db->query("SHOW TABLES LIKE '%tarifa%'")->fetchAll(PDO::FETCH_COLUMN);
echo "  Tablas con 'tarifa': " . implode(', ', $tables) . "\n";

echo "\n=== ABONOS ===\n";
$r4 = $db->query("SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='abonos'")->fetch();
echo "  abonos = " . ($r4 ? $r4['TABLE_TYPE'] : 'NO EXISTE') . "\n";
$cols = $db->query("SHOW COLUMNS FROM abonos")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";

echo "\n=== AUDITORIA ===\n";
$tables = $db->query("SHOW TABLES LIKE '%audit%'")->fetchAll(PDO::FETCH_COLUMN);
echo "  Tablas con 'audit': " . implode(', ', $tables) . "\n";

echo "\n=== TABLAS TODAS ===\n";
$all = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($all as $t) echo "  $t\n";

echo "\n=== REPORTES CONTROLLER ===\n";
$path = dirname(__DIR__) . '/app/controllers/reportes';
if (is_dir($path)) {
    $files = scandir($path);
    foreach ($files as $f) if ($f !== '.' && $f !== '..') echo "  $f\n";
} else {
    echo "  DIRECTORIO NO EXISTE: $path\n";
}

echo "\n=== USUARIOS CONTROLLER RUTAS ===\n";
$content = file_get_contents(dirname(__DIR__) . '/app/controllers/seguridad/UsuarioController.php');
preg_match_all("/renderModule\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $m);
foreach ($m[1] as $v) echo "  renderModule('$v')\n";

echo "\n=== MODULO CONTROLLER RUTAS ===\n";
$content = file_get_contents(dirname(__DIR__) . '/app/controllers/seguridad/ModuloController.php');
preg_match_all("/renderModule\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $m);
foreach ($m[1] as $v) echo "  renderModule('$v')\n";

echo "\n=== MENU ITEMS ARENA ===\n";
$stmt = $db->query("SELECT men_id, men_label, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_activo FROM seguridad_menu WHERE men_modulo_id = 1 ORDER BY men_orden");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($items as $i) {
    $active = $i['men_activo'] ? '✓' : '✗';
    echo "  [$active] #{$i['men_id']} {$i['men_label']} → {$i['men_ruta_modulo']}/{$i['men_ruta_controller']}/{$i['men_ruta_action']}\n";
}

echo "\n=== MENU ITEMS REPORTES (menú apunta a reportes/reporteArena/index) ===\n";
$stmt = $db->query("SELECT * FROM seguridad_menu WHERE men_ruta_modulo = 'reportes' OR men_ruta_controller LIKE '%reporte%'");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($items as $i) {
    echo "  #{$i['men_id']} mod={$i['men_modulo_id']} {$i['men_label']} → {$i['men_ruta_modulo']}/{$i['men_ruta_controller']}/{$i['men_ruta_action']} activo={$i['men_activo']}\n";
}

echo "\n=== TENANTS ===\n";
$stmt = $db->query("SELECT ten_id, ten_nombre, ten_estado FROM seguridad_tenants");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $t) {
    echo "  #{$t['ten_id']} {$t['ten_nombre']} — {$t['ten_estado']}\n";
}
