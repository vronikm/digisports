<?php
/**
 * Test funcional: Verificar que las queries migradas funcionan correctamente
 */
$db = new PDO('mysql:host=localhost;dbname=digisports_core;charset=utf8mb4', 'root', '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== TEST FUNCIONAL POST-MIGRACIÓN ===\n\n";
$errores = 0;

// Test 1: loadModuleBranding() — ModuleController
echo "1. loadModuleBranding('seguridad')...\n";
try {
    $stmt = $db->prepare("SELECT mod_nombre, mod_color_fondo, mod_icono FROM seguridad_modulos WHERE mod_codigo = ? AND mod_activo = 1 LIMIT 1");
    $stmt->execute(['seguridad']);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        echo "   ✅ {$r['mod_nombre']} | {$r['mod_color_fondo']} | {$r['mod_icono']}\n";
    } else {
        echo "   ⚠️ No encontró módulo 'seguridad'\n";
    }
} catch (Exception $e) { echo "   ❌ " . $e->getMessage() . "\n"; $errores++; }

// Test 2: getSystemStats() — DashboardController
echo "2. Contar módulos activos...\n";
try {
    $stmt = $db->query("SELECT COUNT(*) FROM seguridad_modulos WHERE mod_activo = 1");
    echo "   ✅ Módulos activos: " . $stmt->fetchColumn() . "\n";
} catch (Exception $e) { echo "   ❌ " . $e->getMessage() . "\n"; $errores++; }

// Test 3: getChartData() — DashboardController
echo "3. Top módulos más asignados...\n";
try {
    $stmt = $db->query("
        SELECT m.mod_nombre as nombre, COUNT(tm.tmo_tenant_id) as asignaciones
        FROM seguridad_modulos m
        LEFT JOIN seguridad_tenant_modulos tm ON m.mod_id = tm.tmo_modulo_id AND tm.tmo_activo = 1
        WHERE m.mod_activo = 1
        GROUP BY m.mod_id, m.mod_nombre
        ORDER BY asignaciones DESC
        LIMIT 8
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   ✅ " . count($rows) . " módulos:\n";
    foreach ($rows as $r) {
        echo "      - {$r['nombre']}: {$r['asignaciones']} tenants\n";
    }
} catch (Exception $e) { echo "   ❌ " . $e->getMessage() . "\n"; $errores++; }

// Test 4: TenantController — obtener módulos para asignar
echo "4. Módulos disponibles para asignar a tenants...\n";
try {
    $mods = $db->query("SELECT mod_id FROM seguridad_modulos WHERE mod_activo = 1")->fetchAll(PDO::FETCH_COLUMN);
    echo "   ✅ " . count($mods) . " módulos: [" . implode(', ', $mods) . "]\n";
} catch (Exception $e) { echo "   ❌ " . $e->getMessage() . "\n"; $errores++; }

// Test 5: FK integridad
echo "5. Integridad referencial tenant_modulos...\n";
try {
    $huerfanos = $db->query("
        SELECT COUNT(*) FROM seguridad_tenant_modulos tm
        LEFT JOIN seguridad_modulos m ON tm.tmo_modulo_id = m.mod_id
        WHERE m.mod_id IS NULL
    ")->fetchColumn();
    if ($huerfanos == 0) {
        echo "   ✅ 0 huérfanos — integridad perfecta\n";
    } else {
        echo "   ❌ $huerfanos registros huérfanos\n"; $errores++;
    }
} catch (Exception $e) { echo "   ❌ " . $e->getMessage() . "\n"; $errores++; }

// Test 6: Tabla vieja NO existe
echo "6. Tabla vieja eliminada...\n";
try {
    $db->query("SELECT 1 FROM seguridad_modulos_sistema LIMIT 1");
    echo "   ❌ seguridad_modulos_sistema SIGUE existiendo\n"; $errores++;
} catch (Exception $e) {
    echo "   ✅ seguridad_modulos_sistema ya no existe\n";
}

echo "\n=== RESULTADO: " . ($errores === 0 ? "✅ TODOS LOS TESTS PASARON" : "❌ $errores ERRORES") . " ===\n";
