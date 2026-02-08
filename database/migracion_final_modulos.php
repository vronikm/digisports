<?php
/**
 * DigiSports — Migración Final: Remapear FKs y completar unificación
 * 
 * Estado previo (ya hecho):
 *   - PASO 1: columna mod_base_datos_externa añadida ✅
 *   - PASO 2: datos sincronizados ✅
 *   - PASO 3: 10 módulos insertados en seguridad_modulos ✅
 * 
 * Este script hace:
 *   - PASO 4: Desactivar FK checks, remapear tenant_modulos, crear nueva FK → seguridad_modulos
 *   - PASO 5: Renombrar tabla vieja a _DEPRECATED
 *   - PASO 6: Verificación final
 */

$db = new PDO('mysql:host=localhost;dbname=digisports_core;charset=utf8mb4', 'root', '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== MIGRACIÓN FINAL: Unificar módulos ===\n\n";

// ── Construir mapeo sis_modulo_id → mod_id ──────────────────────────
echo "── Construyendo mapeo de IDs ──\n";
$mapping = $db->query("
    SELECT s.sis_modulo_id, m.mod_id, s.sis_codigo
    FROM seguridad_modulos_sistema s
    INNER JOIN seguridad_modulos m ON UPPER(m.mod_codigo) = UPPER(s.sis_codigo)
    ORDER BY s.sis_modulo_id
")->fetchAll(PDO::FETCH_ASSOC);

$idMap = [];
foreach ($mapping as $row) {
    $idMap[(int)$row['sis_modulo_id']] = (int)$row['mod_id'];
    $flag = ($row['sis_modulo_id'] == $row['mod_id']) ? '✅' : '⚠️';
    echo "  sis_ID={$row['sis_modulo_id']} ({$row['sis_codigo']}) → mod_ID={$row['mod_id']} $flag\n";
}
echo "  Total mapeados: " . count($idMap) . "\n\n";

if (empty($idMap)) {
    die("ERROR: No se encontró ningún mapeo. Abortando.\n");
}

// ── PASO 4: Remapear tenant_modulos + FK ────────────────────────────
echo "── PASO 4: Remapear seguridad_tenant_modulos ──\n";

try {
    $db->beginTransaction();
    
    // 4a. Desactivar FK checks temporalmente
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "  [4a] FK checks desactivados\n";
    
    // 4b. Eliminar la FK vieja que apunta a seguridad_modulos_sistema
    $fks = $db->query("
        SELECT CONSTRAINT_NAME 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = 'seguridad_tenant_modulos' 
          AND COLUMN_NAME = 'tmo_modulo_id'
          AND REFERENCED_TABLE_NAME = 'seguridad_modulos_sistema'
    ")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($fks as $fk) {
        $db->exec("ALTER TABLE seguridad_tenant_modulos DROP FOREIGN KEY `$fk`");
        echo "  [4b] FK eliminada: $fk\n";
    }
    
    // 4c. Remapear IDs en tenant_modulos
    $totalUpdated = 0;
    foreach ($idMap as $oldId => $newId) {
        if ($oldId === $newId) continue; // No necesita cambio
        
        $stmt = $db->prepare("UPDATE seguridad_tenant_modulos SET tmo_modulo_id = ? WHERE tmo_modulo_id = ?");
        $stmt->execute([$newId, $oldId]);
        $affected = $stmt->rowCount();
        if ($affected > 0) {
            echo "  [4c] Remapeado: $oldId → $newId ($affected registros)\n";
            $totalUpdated += $affected;
        }
    }
    echo "  [4c] Total registros actualizados: $totalUpdated\n";
    
    // 4d. Verificar que no queden huérfanos
    $orphans = $db->query("
        SELECT tm.tmo_modulo_id, COUNT(*) as cnt
        FROM seguridad_tenant_modulos tm
        LEFT JOIN seguridad_modulos m ON tm.tmo_modulo_id = m.mod_id
        WHERE m.mod_id IS NULL
        GROUP BY tm.tmo_modulo_id
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($orphans)) {
        echo "  ⚠️ HUÉRFANOS detectados (se eliminarán):\n";
        foreach ($orphans as $o) {
            echo "    tmo_modulo_id={$o['tmo_modulo_id']} ({$o['cnt']} registros)\n";
            $db->exec("DELETE FROM seguridad_tenant_modulos WHERE tmo_modulo_id = {$o['tmo_modulo_id']}");
        }
    } else {
        echo "  ✅ Sin huérfanos\n";
    }
    
    // 4e. Crear nueva FK apuntando a seguridad_modulos
    $db->exec("
        ALTER TABLE seguridad_tenant_modulos 
        ADD CONSTRAINT fk_tenant_modulos_modulo_id 
        FOREIGN KEY (tmo_modulo_id) REFERENCES seguridad_modulos(mod_id)
        ON DELETE CASCADE ON UPDATE CASCADE
    ");
    echo "  [4e] ✅ Nueva FK creada → seguridad_modulos\n";
    
    // 4f. Reactivar FK checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "  [4f] FK checks reactivados\n";
    
    $db->commit();
    echo "  ✅ PASO 4 completado\n\n";
    
} catch (Exception $e) {
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    $db->rollBack();
    die("  ❌ PASO 4 FALLÓ: " . $e->getMessage() . "\n");
}

// ── PASO 5: Renombrar tabla vieja ───────────────────────────────────
echo "── PASO 5: Deprecar tabla vieja ──\n";
try {
    // Verificar que no queden otras FKs referenciando la tabla vieja
    $remainingFks = $db->query("
        SELECT TABLE_NAME, CONSTRAINT_NAME 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND REFERENCED_TABLE_NAME = 'seguridad_modulos_sistema'
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($remainingFks)) {
        echo "  ⚠️ Otras FKs referencian la tabla vieja:\n";
        foreach ($remainingFks as $fk) {
            echo "    {$fk['TABLE_NAME']}.{$fk['CONSTRAINT_NAME']}\n";
            // Eliminarlas
            $db->exec("SET FOREIGN_KEY_CHECKS = 0");
            $db->exec("ALTER TABLE `{$fk['TABLE_NAME']}` DROP FOREIGN KEY `{$fk['CONSTRAINT_NAME']}`");
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
            echo "    → Eliminada\n";
        }
    }
    
    $db->exec("RENAME TABLE seguridad_modulos_sistema TO seguridad_modulos_sistema_DEPRECATED");
    echo "  ✅ Tabla renombrada a seguridad_modulos_sistema_DEPRECATED\n\n";
} catch (Exception $e) {
    echo "  ❌ Error renombrando tabla: " . $e->getMessage() . "\n\n";
}

// ── PASO 6: Verificación final ──────────────────────────────────────
echo "── PASO 6: Verificación final ──\n";

// 6a. Contar módulos
$count = $db->query("SELECT COUNT(*) FROM seguridad_modulos")->fetchColumn();
echo "  Módulos en seguridad_modulos: $count\n";

// 6b. Listar módulos
$mods = $db->query("SELECT mod_id, mod_codigo, mod_nombre, mod_activo FROM seguridad_modulos ORDER BY mod_id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($mods as $m) {
    $estado = $m['mod_activo'] ? '🟢' : '🔴';
    echo "  $estado ID={$m['mod_id']} [{$m['mod_codigo']}] {$m['mod_nombre']}\n";
}

// 6c. Verificar FK actual
$fkInfo = $db->query("
    SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'seguridad_tenant_modulos' 
      AND COLUMN_NAME = 'tmo_modulo_id'
      AND REFERENCED_TABLE_NAME IS NOT NULL
")->fetchAll(PDO::FETCH_ASSOC);
echo "\n  FK en tenant_modulos:\n";
foreach ($fkInfo as $fk) {
    $ok = ($fk['REFERENCED_TABLE_NAME'] === 'seguridad_modulos') ? '✅' : '❌';
    echo "    $ok {$fk['CONSTRAINT_NAME']} → {$fk['REFERENCED_TABLE_NAME']}\n";
}

// 6d. Verificar integridad tenant_modulos
$integridad = $db->query("
    SELECT COUNT(*) as total,
           SUM(CASE WHEN m.mod_id IS NOT NULL THEN 1 ELSE 0 END) as con_modulo,
           SUM(CASE WHEN m.mod_id IS NULL THEN 1 ELSE 0 END) as huerfanos
    FROM seguridad_tenant_modulos tm
    LEFT JOIN seguridad_modulos m ON tm.tmo_modulo_id = m.mod_id
")->fetch(PDO::FETCH_ASSOC);
echo "\n  Integridad tenant_modulos:\n";
echo "    Total: {$integridad['total']}, Con módulo: {$integridad['con_modulo']}, Huérfanos: {$integridad['huerfanos']}\n";

// 6e. Verificar tabla vieja ya no existe
try {
    $db->query("SELECT 1 FROM seguridad_modulos_sistema LIMIT 1");
    echo "\n  ⚠️ La tabla seguridad_modulos_sistema aún existe (no debería)\n";
} catch (Exception $e) {
    echo "\n  ✅ seguridad_modulos_sistema ya no existe (correcto)\n";
}

try {
    $db->query("SELECT 1 FROM seguridad_modulos_sistema_DEPRECATED LIMIT 1");
    echo "  ✅ seguridad_modulos_sistema_DEPRECATED existe como respaldo\n";
} catch (Exception $e) {
    echo "  ℹ️ seguridad_modulos_sistema_DEPRECATED no existe\n";
}

echo "\n=== MIGRACIÓN COMPLETADA ===\n";
echo "Ahora debe actualizar las referencias PHP a seguridad_modulos_sistema.\n";
