-- ============================================================
-- Migration 010: Permisos de menú DigiSports Arena para todos los roles
-- El módulo Arena (mod_id=1) solo tenía entradas en seguridad_rol_menu
-- para el rol SUPERADMIN (rol_id=1). Esta migración agrega permisos
-- para todos los roles activos con nivel_acceso >= 3.
-- Menu items de Arena: 2 (Dashboard), 3 (Canchas), 4 (Mantenimientos), 5 (Reservas)
-- Ejecutar en: digisports_core
-- ============================================================

-- 1. Registrar acceso al módulo Arena en seguridad_rol_modulos
--    para todos los roles activos (si no existe ya)
INSERT INTO `seguridad_rol_modulos`
    (`rmo_rol_rol_id`, `rmo_rol_modulo_id`,
     `rmo_rol_puede_ver`, `rmo_rol_puede_crear`, `rmo_rol_puede_editar`, `rmo_rol_puede_eliminar`,
     `rmo_rol_permisos_especiales`)
SELECT
    r.rol_rol_id,
    1,   -- mod_id de ARENA
    1, 1, 1, 0,
    JSON_OBJECT()
FROM `seguridad_roles` r
WHERE r.rol_estado = 'A'
  AND (r.rol_es_super_admin = 'S' OR r.rol_es_admin_tenant = 'S' OR r.rol_nivel_acceso >= 3)
  AND NOT EXISTS (
      SELECT 1 FROM `seguridad_rol_modulos` x
      WHERE x.rmo_rol_rol_id = r.rol_rol_id
        AND x.rmo_rol_modulo_id = 1
  );

-- 2. Agregar permisos de menú para los ítems de Arena
--    para todos los roles activos que aún no los tengan
INSERT INTO `seguridad_rol_menu`
    (`rme_rol_id`, `rme_menu_id`, `rme_puede_ver`, `rme_puede_acceder`)
SELECT
    r.rol_rol_id,
    m.men_id,
    1,
    1
FROM `seguridad_roles` r
CROSS JOIN `seguridad_menu` m
WHERE r.rol_estado = 'A'
  AND (r.rol_es_super_admin = 'S' OR r.rol_es_admin_tenant = 'S' OR r.rol_nivel_acceso >= 3)
  AND m.men_modulo_id = 1        -- módulo ARENA
  AND m.men_activo = 1
  AND NOT EXISTS (
      SELECT 1 FROM `seguridad_rol_menu` x
      WHERE x.rme_rol_id = r.rol_rol_id
        AND x.rme_menu_id = m.men_id
  );

-- 3. Registrar Arena en seguridad_menu_config (para el módulo Seguridad)
INSERT INTO `seguridad_menu_config`
    (`con_modulo_codigo`, `con_opcion`, `con_icono`, `con_color`, `con_permiso_requerido`, `con_orden`)
VALUES
    ('instalaciones', 'Dashboard Arena',   'fas fa-tachometer-alt',  '#2563eb', 'instalaciones.ver',    1),
    ('instalaciones', 'Canchas',           'fas fa-futbol',          '#2563eb', 'instalaciones.ver',    2),
    ('instalaciones', 'Mantenimientos',    'fas fa-tools',           '#2563eb', 'instalaciones.editar', 3),
    ('instalaciones', 'Reservas Arena',    'fas fa-calendar-check',  '#2563eb', 'reservas.ver',         4)
ON DUPLICATE KEY UPDATE
    con_icono = VALUES(con_icono),
    con_permiso_requerido = VALUES(con_permiso_requerido);
