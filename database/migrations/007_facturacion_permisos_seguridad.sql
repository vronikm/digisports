-- ============================================================
-- Migration 007: Permisos de Facturación en módulo de Seguridad
-- Registra los permisos de Facturación para que el módulo de
-- Seguridad (Roles) los pueda gestionar correctamente.
-- Ejecutar en: digisports_core
-- ============================================================

-- 1. Registrar opciones de menú de Facturación en seguridad_menu_config
--    (permite que el módulo de Seguridad muestre/configure estos permisos)
INSERT INTO `seguridad_menu_config`
    (`con_modulo_codigo`, `con_opcion`, `con_icono`, `con_color`, `con_permiso_requerido`, `con_orden`)
VALUES
    ('facturacion', 'Dashboard',          'fas fa-tachometer-alt',      '#F59E0B', 'facturacion.ver',        1),
    ('facturacion', 'Crear Factura',      'fas fa-plus-circle',         '#F59E0B', 'facturacion.crear',      2),
    ('facturacion', 'Comprobantes',       'fas fa-file-invoice-dollar', '#F59E0B', 'facturacion.ver',        3),
    ('facturacion', 'Facturas Emitidas',  'fas fa-receipt',             '#F59E0B', 'facturacion.ver',        4),
    ('facturación SRI', 'Facturación SRI','fas fa-globe-americas',      '#F59E0B', 'facturacion.ver',        5),
    ('facturacion', 'Registrar Pago',     'fas fa-cash-register',       '#F59E0B', 'facturacion.crear',      6),
    ('facturacion', 'Listado de Pagos',   'fas fa-money-check-alt',     '#F59E0B', 'facturacion.ver',        7),
    ('facturacion', 'Configuración FE',   'fas fa-sliders-h',           '#F59E0B', 'facturacion.configurar', 8)
ON DUPLICATE KEY UPDATE
    con_icono = VALUES(con_icono),
    con_permiso_requerido = VALUES(con_permiso_requerido),
    con_orden = VALUES(con_orden);

-- 2. Registrar acceso al módulo Facturación en seguridad_rol_modulos
--    para los roles administradores (si no existe ya)
INSERT INTO `seguridad_rol_modulos`
    (`rmo_rol_rol_id`, `rmo_rol_modulo_id`,
     `rmo_rol_puede_ver`, `rmo_rol_puede_crear`, `rmo_rol_puede_editar`, `rmo_rol_puede_eliminar`,
     `rmo_rol_permisos_especiales`)
SELECT
    r.rol_rol_id,
    3,   -- mod_id de facturacion
    1, 1, 1, 0,
    JSON_OBJECT('configurar', 1)
FROM `seguridad_roles` r
WHERE r.rol_estado = 'A'
  AND (r.rol_es_super_admin = 'S' OR r.rol_es_admin_tenant = 'S' OR r.rol_nivel_acceso >= 4)
  AND NOT EXISTS (
      SELECT 1 FROM `seguridad_rol_modulos`
      WHERE rmo_rol_rol_id = r.rol_rol_id AND rmo_rol_modulo_id = 3
  );

-- 3. Asegurar que el ítem de menú "Configuración FE" (men_id dinámico)
--    esté en seguridad_rol_menu para todos los roles admin que no lo tengan
INSERT INTO `seguridad_rol_menu` (`rme_rol_id`, `rme_menu_id`, `rme_puede_ver`, `rme_puede_acceder`)
SELECT r.rol_rol_id, m.men_id, 1, 1
FROM `seguridad_roles` r
CROSS JOIN `seguridad_menu` m
WHERE m.men_ruta_controller = 'configuracion'
  AND m.men_ruta_modulo     = 'facturacion'
  AND m.men_activo          = 1
  AND r.rol_estado          = 'A'
  AND (r.rol_es_super_admin = 'S' OR r.rol_es_admin_tenant = 'S' OR r.rol_nivel_acceso >= 4)
ON DUPLICATE KEY UPDATE rme_puede_ver = 1, rme_puede_acceder = 1;
