<?php
/**
 * Script de migración: Unificar seguridad_modulos_sistema → seguridad_modulos
 * Ejecutar una sola vez
 */
require __DIR__ . '/../config/database.php';

try {
    $db = new PDO(
        'mysql:host=localhost;dbname=digisports_core;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Conectado a la base de datos\n\n";

    // ─── PASO 1: Agregar columna mod_base_datos_externa ───
    echo "PASO 1: Agregar columna mod_base_datos_externa...\n";
    try {
        $db->exec("ALTER TABLE seguridad_modulos ADD COLUMN mod_base_datos_externa VARCHAR(100) DEFAULT NULL AFTER mod_url_externa");
        echo "  → Columna agregada\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "  → Columna ya existe (OK)\n";
        } else {
            throw $e;
        }
    }

    // ─── PASO 2: Sincronizar base_datos_externa desde _sistema ───
    echo "\nPASO 2: Sincronizar base_datos_externa...\n";
    $r = $db->exec("
        UPDATE seguridad_modulos m 
        INNER JOIN seguridad_modulos_sistema s ON UPPER(m.mod_codigo) = UPPER(s.sis_codigo)
        SET m.mod_base_datos_externa = s.sis_base_datos_externa 
        WHERE s.sis_base_datos_externa IS NOT NULL AND s.sis_base_datos_externa != ''
    ");
    echo "  → $r registros sincronizados\n";

    // ─── PASO 3: Insertar módulos que solo existen en _sistema ───
    echo "\nPASO 3: Insertar módulos faltantes...\n";
    
    // Primero verificar qué va a insertarse
    $pendientes = $db->query("
        SELECT s.sis_modulo_id, s.sis_codigo, s.sis_nombre
        FROM seguridad_modulos_sistema s
        WHERE NOT EXISTS (SELECT 1 FROM seguridad_modulos m WHERE UPPER(m.mod_codigo) = UPPER(s.sis_codigo))
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  → Módulos a insertar: " . count($pendientes) . "\n";
    foreach ($pendientes as $p) {
        echo "    - ID:{$p['sis_modulo_id']} {$p['sis_codigo']} ({$p['sis_nombre']})\n";
    }
    
    if (count($pendientes) > 0) {
        // Insertar uno por uno para manejar conflictos de ID
        foreach ($pendientes as $p) {
            $sid = $p['sis_modulo_id'];
            
            // Verificar si el ID ya está ocupado
            $idOcupado = $db->query("SELECT mod_id, mod_codigo FROM seguridad_modulos WHERE mod_id = $sid")->fetch(PDO::FETCH_ASSOC);
            
            if ($idOcupado) {
                echo "    ⚠️ ID $sid ya ocupado por '{$idOcupado['mod_codigo']}', insertando sin ID fijo...\n";
                $sql = "INSERT INTO seguridad_modulos (mod_codigo, mod_nombre, mod_descripcion, mod_icono, mod_color_fondo, mod_orden, mod_ruta_modulo, mod_ruta_controller, mod_ruta_action, mod_es_externo, mod_url_externa, mod_requiere_licencia, mod_activo, mod_base_datos_externa)
                SELECT s.sis_codigo, s.sis_nombre, s.sis_descripcion,
                    CASE WHEN s.sis_icono LIKE 'fas %' OR s.sis_icono LIKE 'far %' OR s.sis_icono LIKE 'fab %' THEN s.sis_icono ELSE CONCAT('fas ', COALESCE(s.sis_icono, 'fa-cube')) END,
                    COALESCE(NULLIF(s.sis_color, ''), '#3B82F6'),
                    s.sis_orden_visualizacion,
                    LOWER(s.sis_codigo), 'dashboard', 'index',
                    IF(s.sis_es_externo = 'S', 1, 0), s.sis_url_base, 
                    IF(s.sis_requiere_suscripcion = 'S', 1, 0),
                    IF(s.sis_estado = 'A', 1, 0), s.sis_base_datos_externa
                FROM seguridad_modulos_sistema s WHERE s.sis_modulo_id = $sid";
            } else {
                $sql = "INSERT INTO seguridad_modulos (mod_id, mod_codigo, mod_nombre, mod_descripcion, mod_icono, mod_color_fondo, mod_orden, mod_ruta_modulo, mod_ruta_controller, mod_ruta_action, mod_es_externo, mod_url_externa, mod_requiere_licencia, mod_activo, mod_base_datos_externa)
                SELECT s.sis_modulo_id, s.sis_codigo, s.sis_nombre, s.sis_descripcion,
                    CASE WHEN s.sis_icono LIKE 'fas %' OR s.sis_icono LIKE 'far %' OR s.sis_icono LIKE 'fab %' THEN s.sis_icono ELSE CONCAT('fas ', COALESCE(s.sis_icono, 'fa-cube')) END,
                    COALESCE(NULLIF(s.sis_color, ''), '#3B82F6'),
                    s.sis_orden_visualizacion,
                    LOWER(s.sis_codigo), 'dashboard', 'index',
                    IF(s.sis_es_externo = 'S', 1, 0), s.sis_url_base, 
                    IF(s.sis_requiere_suscripcion = 'S', 1, 0),
                    IF(s.sis_estado = 'A', 1, 0), s.sis_base_datos_externa
                FROM seguridad_modulos_sistema s WHERE s.sis_modulo_id = $sid";
            }
            $db->exec($sql);
            echo "    ✅ Insertado: {$p['sis_codigo']}\n";
        }
    }

    // ─── PASO 4: Migrar FK de seguridad_tenant_modulos ───
    echo "\nPASO 4: Migrar FK de seguridad_tenant_modulos...\n";
    
    // Primero actualizar tmo_modulo_id que apunten a IDs de _sistema que cambiaron
    // (módulos insertados sin ID fijo por conflicto)
    $db->exec("
        UPDATE seguridad_tenant_modulos tm
        INNER JOIN seguridad_modulos_sistema s ON tm.tmo_modulo_id = s.sis_modulo_id
        INNER JOIN seguridad_modulos m ON UPPER(s.sis_codigo) = UPPER(m.mod_codigo)
        SET tm.tmo_modulo_id = m.mod_id
        WHERE tm.tmo_modulo_id != m.mod_id
    ");
    
    // Buscar y eliminar FK antigua
    $fks = $db->query("
        SELECT CONSTRAINT_NAME 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_NAME = 'seguridad_tenant_modulos' 
          AND COLUMN_NAME = 'tmo_modulo_id'
          AND REFERENCED_TABLE_NAME = 'seguridad_modulos_sistema'
          AND TABLE_SCHEMA = DATABASE()
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($fks as $fk) {
        $name = $fk['CONSTRAINT_NAME'];
        echo "  → Eliminando FK antigua: $name\n";
        $db->exec("ALTER TABLE seguridad_tenant_modulos DROP FOREIGN KEY `$name`");
    }
    
    // Verificar si ya existe FK hacia seguridad_modulos
    $fkExiste = $db->query("
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_NAME = 'seguridad_tenant_modulos' 
          AND COLUMN_NAME = 'tmo_modulo_id'
          AND REFERENCED_TABLE_NAME = 'seguridad_modulos'
          AND TABLE_SCHEMA = DATABASE()
    ")->fetchColumn();
    
    if (!$fkExiste) {
        // Verificar huérfanos antes de crear FK
        $huerfanos = $db->query("
            SELECT tm.tmo_modulo_id, COUNT(*) as c
            FROM seguridad_tenant_modulos tm
            LEFT JOIN seguridad_modulos m ON tm.tmo_modulo_id = m.mod_id
            WHERE m.mod_id IS NULL
            GROUP BY tm.tmo_modulo_id
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($huerfanos) > 0) {
            echo "  ⚠️ Huérfanos encontrados, limpiando:\n";
            foreach ($huerfanos as $h) {
                echo "    - tmo_modulo_id={$h['tmo_modulo_id']} ({$h['c']} registros)\n";
            }
            $db->exec("DELETE FROM seguridad_tenant_modulos WHERE tmo_modulo_id NOT IN (SELECT mod_id FROM seguridad_modulos)");
        }
        
        $db->exec("ALTER TABLE seguridad_tenant_modulos ADD CONSTRAINT fk_tenant_modulos_modulo FOREIGN KEY (tmo_modulo_id) REFERENCES seguridad_modulos (mod_id) ON DELETE CASCADE");
        echo "  ✅ Nueva FK creada: tmo_modulo_id → seguridad_modulos.mod_id\n";
    } else {
        echo "  → FK ya existe hacia seguridad_modulos (OK)\n";
    }

    // ─── PASO 5: Verificación ───
    echo "\n═══════════════════════════════════════════\n";
    echo "RESULTADO FINAL — MÓDULOS UNIFICADOS:\n";
    echo "═══════════════════════════════════════════\n";
    
    $rows = $db->query("SELECT mod_id, mod_codigo, mod_nombre, mod_activo, mod_ruta_modulo FROM seguridad_modulos ORDER BY mod_id")->fetchAll(PDO::FETCH_ASSOC);
    echo "Total: " . count($rows) . " módulos\n\n";
    
    printf("%-5s %-20s %-30s %-8s %-15s\n", "ID", "CÓDIGO", "NOMBRE", "ESTADO", "RUTA");
    echo str_repeat("─", 80) . "\n";
    foreach ($rows as $r) {
        printf("%-5s %-20s %-30s %-8s %-15s\n", 
            $r['mod_id'], $r['mod_codigo'], $r['mod_nombre'],
            $r['mod_activo'] ? 'ACTIVO' : 'INACT.', $r['mod_ruta_modulo'] ?? '-'
        );
    }
    
    // Verificar integridad
    $huerfanos = $db->query("
        SELECT COUNT(*) FROM seguridad_tenant_modulos tm
        LEFT JOIN seguridad_modulos m ON tm.tmo_modulo_id = m.mod_id
        WHERE m.mod_id IS NULL
    ")->fetchColumn();
    
    echo "\n✅ Verificación de integridad:\n";
    echo "  Registros huérfanos en tenant_modulos: $huerfanos\n";
    echo "  " . ($huerfanos == 0 ? "✅ Todo OK" : "⚠️ HAY PROBLEMAS") . "\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
