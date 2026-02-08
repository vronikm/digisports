-- ═══════════════════════════════════════════════════════════════════════
-- DigiSports — Migración: Unificar seguridad_modulos_sistema → seguridad_modulos
-- Fecha: 2026-02-07
-- Propósito: Eliminar duplicación de tablas de módulos. La tabla ganadora
--            es seguridad_modulos (más completa, usada por Hub/Auth/CRUD).
-- ═══════════════════════════════════════════════════════════════════════

-- ─── PASO 1: Agregar columna exclusiva de _sistema que no existe en seguridad_modulos ───
ALTER TABLE seguridad_modulos
  ADD COLUMN IF NOT EXISTS mod_base_datos_externa VARCHAR(100) DEFAULT NULL
  AFTER mod_url_externa;

-- ─── PASO 2: Sincronizar datos de branding (icono/color) de _sistema → modulos ───
-- Solo actualiza los módulos que existen en ambas tablas (por código coincidente)
UPDATE seguridad_modulos m
INNER JOIN seguridad_modulos_sistema s ON UPPER(m.mod_codigo) = UPPER(s.sis_codigo)
SET 
    m.mod_base_datos_externa = s.sis_base_datos_externa
WHERE s.sis_base_datos_externa IS NOT NULL AND s.sis_base_datos_externa != '';

-- ─── PASO 3: Insertar módulos que solo existen en _sistema ───
-- Estos son los módulos deportivos y administrativos extra que no tienen
-- equivalente en seguridad_modulos. Se insertan CON el mismo ID para
-- mantener la integridad con seguridad_tenant_modulos.

-- Primero verificamos y insertamos cada uno individualmente para evitar conflictos
INSERT INTO seguridad_modulos (mod_id, mod_codigo, mod_nombre, mod_descripcion, mod_icono, mod_color_fondo, mod_orden, mod_ruta_modulo, mod_ruta_controller, mod_ruta_action, mod_es_externo, mod_url_externa, mod_requiere_licencia, mod_activo, mod_base_datos_externa)
SELECT 
    s.sis_modulo_id,
    s.sis_codigo,
    s.sis_nombre,
    s.sis_descripcion,
    CASE 
        WHEN s.sis_icono IS NULL OR s.sis_icono = '' THEN 'fas fa-cube'
        WHEN s.sis_icono LIKE 'fas %' OR s.sis_icono LIKE 'far %' OR s.sis_icono LIKE 'fab %' THEN s.sis_icono
        ELSE CONCAT('fas ', s.sis_icono)
    END,
    CASE WHEN s.sis_color IS NULL OR s.sis_color = '' THEN '#3B82F6' ELSE s.sis_color END,
    s.sis_orden_visualizacion,
    LOWER(s.sis_codigo),
    'dashboard',
    'index',
    CASE WHEN s.sis_es_externo = 'S' THEN 1 ELSE 0 END,
    s.sis_url_base,
    CASE WHEN s.sis_requiere_suscripcion = 'S' THEN 1 ELSE 0 END,
    CASE WHEN s.sis_estado = 'A' THEN 1 ELSE 0 END,
    s.sis_base_datos_externa
FROM seguridad_modulos_sistema s
WHERE NOT EXISTS (
    SELECT 1 FROM seguridad_modulos m 
    WHERE UPPER(m.mod_codigo) = UPPER(s.sis_codigo)
)
AND NOT EXISTS (
    SELECT 1 FROM seguridad_modulos m2 
    WHERE m2.mod_id = s.sis_modulo_id
);

-- ─── PASO 4: Migrar FK de seguridad_tenant_modulos ───
-- Primero eliminar la FK antigua que apunta a _sistema
SET @fk_name = NULL;
SELECT CONSTRAINT_NAME INTO @fk_name 
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_NAME = 'seguridad_tenant_modulos' 
  AND COLUMN_NAME = 'tmo_modulo_id'
  AND REFERENCED_TABLE_NAME = 'seguridad_modulos_sistema'
  AND TABLE_SCHEMA = DATABASE()
LIMIT 1;

SET @sql_drop = IF(@fk_name IS NOT NULL, 
    CONCAT('ALTER TABLE seguridad_tenant_modulos DROP FOREIGN KEY ', @fk_name),
    'SELECT 1');
PREPARE stmt FROM @sql_drop;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar nueva FK apuntando a seguridad_modulos
-- Solo si no existe ya una FK hacia esa tabla
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_NAME = 'seguridad_tenant_modulos' 
  AND COLUMN_NAME = 'tmo_modulo_id'
  AND REFERENCED_TABLE_NAME = 'seguridad_modulos'
  AND TABLE_SCHEMA = DATABASE();

SET @sql_add = IF(@fk_exists = 0,
    'ALTER TABLE seguridad_tenant_modulos ADD CONSTRAINT fk_tenant_modulos_modulo FOREIGN KEY (tmo_modulo_id) REFERENCES seguridad_modulos (mod_id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql_add;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ─── PASO 5: Verificación ───
-- Mostrar módulos unificados
SELECT mod_id, mod_codigo, mod_nombre, mod_activo, mod_ruta_modulo, mod_base_datos_externa
FROM seguridad_modulos
ORDER BY mod_id;

-- Verificar que no haya IDs huérfanos en tenant_modulos
SELECT tm.tmo_modulo_id, COUNT(*) as registros
FROM seguridad_tenant_modulos tm
LEFT JOIN seguridad_modulos m ON tm.tmo_modulo_id = m.mod_id
WHERE m.mod_id IS NULL
GROUP BY tm.tmo_modulo_id;

-- ─── PASO 6: Renombrar tabla antigua (no eliminar aún, por seguridad) ───
-- RENAME TABLE seguridad_modulos_sistema TO seguridad_modulos_sistema_DEPRECATED;
-- Descomenta la línea anterior cuando confirmes que todo funciona correctamente.
