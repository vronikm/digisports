<?php
$db = new PDO('mysql:host=localhost;dbname=digisports_core;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "IDs en tenant_modulos que NO existen en seguridad_modulos:\n";
$rows = $db->query("SELECT tm.tmo_modulo_id, COUNT(*) as c FROM seguridad_tenant_modulos tm LEFT JOIN seguridad_modulos m ON tm.tmo_modulo_id = m.mod_id WHERE m.mod_id IS NULL GROUP BY tm.tmo_modulo_id")->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows)) echo "  (ninguno)\n";
foreach ($rows as $r) echo "  tmo_modulo_id={$r['tmo_modulo_id']} ({$r['c']} registros)\n";

echo "\nMapeo completo _sistema.sis_modulo_id -> _modulos.mod_id:\n";
$rows = $db->query("SELECT s.sis_modulo_id, s.sis_codigo, m.mod_id, m.mod_codigo FROM seguridad_modulos_sistema s LEFT JOIN seguridad_modulos m ON UPPER(m.mod_codigo) = UPPER(s.sis_codigo) ORDER BY s.sis_modulo_id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $match = ($r['sis_modulo_id'] == $r['mod_id']) ? '✅ SAME' : '⚠️ DIFF';
    echo "  sis_ID={$r['sis_modulo_id']} ({$r['sis_codigo']}) → mod_ID=" . ($r['mod_id'] ?? 'NULL') . " ({$r['mod_codigo']}) $match\n";
}

echo "\nFK actual sobre tmo_modulo_id:\n";
$rows = $db->query("SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'seguridad_tenant_modulos' AND COLUMN_NAME = 'tmo_modulo_id' AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = DATABASE()")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  {$r['CONSTRAINT_NAME']} → {$r['REFERENCED_TABLE_NAME']}\n";

echo "\nTodos los registros en seguridad_tenant_modulos:\n";
$rows = $db->query("SELECT tmo_id, tmo_tenant_id, tmo_modulo_id, tmo_activo FROM seguridad_tenant_modulos ORDER BY tmo_tenant_id, tmo_modulo_id")->fetchAll(PDO::FETCH_ASSOC);
echo "  Total: " . count($rows) . "\n";
foreach ($rows as $r) echo "  tenant={$r['tmo_tenant_id']} modulo={$r['tmo_modulo_id']} activo={$r['tmo_activo']}\n";
