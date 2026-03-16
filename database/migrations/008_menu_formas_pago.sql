-- ============================================================
-- Migration 008: Menu - Formas de Pago en modulo Facturacion
-- Agrega item de menu para FormaPagoController
-- Ejecutar en: digisports_core
-- ============================================================

-- Paso 1: Determinar el men_padre_id correcto usando variables de sesion.
-- Se busca el padre del item "Configuracion FE" (migration 006),
-- con fallback al padre de "factura/index", y ultimo recurso NULL.

SET @padre_id = NULL;

SET @padre_id = (
    SELECT men_padre_id
    FROM seguridad_menu
    WHERE men_ruta_modulo = 'facturacion'
      AND men_ruta_controller = 'configuracion'
      AND men_ruta_action = 'index'
    LIMIT 1
);

SET @padre_id = IFNULL(@padre_id, (
    SELECT men_padre_id
    FROM seguridad_menu
    WHERE men_ruta_modulo = 'facturacion'
      AND men_ruta_controller = 'factura'
      AND men_ruta_action = 'index'
    LIMIT 1
));

SET @mod_id = (
    SELECT mod_id FROM seguridad_modulos
    WHERE mod_codigo = 'facturacion'
    LIMIT 1
);

-- Paso 2: Insertar el item de menu (si no existe ya)
INSERT INTO seguridad_menu
    (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono,
     men_ruta_modulo, men_ruta_controller, men_ruta_action,
     men_orden, men_activo)
SELECT
    @mod_id,
    @padre_id,
    'ITEM',
    'Formas de Pago',
    'fas fa-credit-card',
    'facturacion',
    'formaPago',
    'index',
    9,
    1
FROM DUAL
WHERE @mod_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM seguridad_menu
      WHERE men_ruta_modulo = 'facturacion'
        AND men_ruta_controller = 'formaPago'
        AND men_ruta_action = 'index'
  );

-- Paso 3: Asignar a roles con acceso a facturacion (nivel >= 3)
INSERT INTO seguridad_rol_menu (rme_rol_id, rme_menu_id, rme_puede_ver, rme_puede_acceder)
SELECT r.rol_rol_id, m.men_id, 1, 1
FROM seguridad_roles r
CROSS JOIN seguridad_menu m
WHERE m.men_ruta_modulo = 'facturacion'
  AND m.men_ruta_controller = 'formaPago'
  AND m.men_ruta_action = 'index'
  AND m.men_activo = 1
  AND r.rol_estado = 'A'
  AND (
      r.rol_es_super_admin = 'S'
      OR r.rol_es_admin_tenant = 'S'
      OR r.rol_nivel_acceso >= 3
  )
ON DUPLICATE KEY UPDATE rme_puede_ver = 1, rme_puede_acceder = 1;

-- Paso 4: Registrar en seguridad_menu_config
INSERT INTO seguridad_menu_config
    (con_modulo_codigo, con_opcion, con_icono, con_color, con_permiso_requerido, con_orden)
VALUES
    ('facturacion', 'Formas de Pago', 'fas fa-credit-card', '#F59E0B', 'facturacion.ver', 9)
ON DUPLICATE KEY UPDATE
    con_icono = VALUES(con_icono),
    con_permiso_requerido = VALUES(con_permiso_requerido),
    con_orden = VALUES(con_orden);
