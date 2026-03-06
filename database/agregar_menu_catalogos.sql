-- =============================================================================
-- MENÚ: Agregar opciones de Administración de Catálogos al módulo SEGURIDAD
-- =============================================================================
-- Script para agregar las opciones en el menú del módulo de Seguridad
-- Fecha: 2024
-- Descripción: Agrega dos opciones principales en el menú de Seguridad:
--              1. Administración de Catálogos (Grupos)
--              2. Ítems de Catálogos (Submenu)

-- =============================================================================
-- 1. Obtener el mod_id del módulo SEGURIDAD
-- =============================================================================
SET @seg_mod_id = (SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = 'SEGURIDAD' LIMIT 1);

-- Si no existe, mostrar error
-- SELECT IF(@seg_mod_id IS NULL, 'ERROR: Módulo SEGURIDAD no encontrado', 'OK: Módulo encontrado') as status;

-- =============================================================================
-- 2. Insertar Header "Administración de Catálogos"
-- =============================================================================
INSERT IGNORE INTO seguridad_menu 
(men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo)
VALUES
(@seg_mod_id, NULL, 'HEADER', 'Administración de Catálogos', NULL, NULL, NULL, NULL, 10, 1);

SET @header_catalogos = LAST_INSERT_ID();

-- =============================================================================
-- 3. Insertar Items bajo el Header
-- =============================================================================

-- Item 1: Catálogos (Grupos)
INSERT IGNORE INTO seguridad_menu 
(men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo)
VALUES
(@seg_mod_id, @header_catalogos, 'ITEM', 'Catálogos', 'fas fa-list-check', 'seguridad', 'seguridad_tabla', 'index', 1, 1);

SET @menu_catalogos = LAST_INSERT_ID();

-- Item 2: Gestionar Ítems de Catálogos (como subelementos, si se desea)
-- Nota: Esto es opcional, ya que los ítems se manejan dentro de los catálogos
-- Si deseas un acceso directo separado, descomenta las líneas abajo:

-- INSERT IGNORE INTO seguridad_menu 
-- (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo)
-- VALUES
-- (@seg_mod_id, @header_catalogos, 'ITEM', 'Ítems de Catálogos', 'fas fa-tags', 'seguridad', 'seguridad_tabla_catalogo', 'listar', 2, 1);

-- =============================================================================
-- 4. Asignar permisos de menú al rol ADMIN (rol_id = 1)
-- =============================================================================
-- Los usuarios con rol ADMIN verán y podrán acceder a las opciones

-- Dar acceso al header (si se requiere, aunque normalmente los headers no necesitan permisos)
-- INSERT IGNORE INTO seguridad_rol_menu (rme_rol_id, rme_menu_id, rme_puede_ver, rme_puede_acceder)
-- VALUES (1, @header_catalogos, 1, 0);  -- Ver pero no acceder directo (es un header)

-- Dar acceso al item "Catálogos"
INSERT IGNORE INTO seguridad_rol_menu (rme_rol_id, rme_menu_id, rme_puede_ver, rme_puede_acceder)
VALUES (1, @menu_catalogos, 1, 1);  -- Ver y acceder

-- =============================================================================
-- 5. Verificación final
-- =============================================================================
-- Ver los menús creados
SELECT 'Menús de Seguridad creados:' as Info;
SELECT 
    men_id,
    men_tipo,
    men_label,
    men_icono,
    men_ruta_controller,
    men_ruta_action,
    men_orden,
    men_activo
FROM seguridad_menu 
WHERE men_modulo_id = @seg_mod_id
ORDER BY men_orden, men_id;

-- Ver permisos asignados
SELECT 'Permisos de Rol ADMIN:' as Info;
SELECT 
    srm.rme_menu_id,
    m.men_label,
    srm.rme_puede_ver,
    srm.rme_puede_acceder
FROM seguridad_rol_menu srm
LEFT JOIN seguridad_menu m ON srm.rme_menu_id = m.men_id
WHERE srm.rme_rol_id = 1
  AND m.men_modulo_id = @seg_mod_id
ORDER BY m.men_orden;
