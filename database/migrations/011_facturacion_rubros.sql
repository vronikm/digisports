-- ============================================================
-- Migration 011: Rubros de Facturación
-- Tabla para administrar rubros/conceptos con su configuración de IVA
-- Ejecutar en: digisports_core
-- ============================================================

-- ── 1. Crear tabla ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `facturacion_rubros` (
  `rub_id`            int            NOT NULL AUTO_INCREMENT,
  `rub_tenant_id`     int            NOT NULL,
  `rub_codigo`        varchar(20)    COLLATE utf8mb4_unicode_ci DEFAULT NULL
                          COMMENT 'Código corto para la línea de factura (ej: MENS, MAT)',
  `rub_nombre`        varchar(100)   COLLATE utf8mb4_unicode_ci NOT NULL
                          COMMENT 'Nombre del rubro/concepto',
  `rub_descripcion`   varchar(255)   COLLATE utf8mb4_unicode_ci DEFAULT NULL
                          COMMENT 'Descripción ampliada opcional',
  `rub_aplica_iva`    tinyint(1)     NOT NULL DEFAULT 1
                          COMMENT '1 = aplica IVA, 0 = exento de IVA',
  `rub_porcentaje_iva` decimal(5,2)  NOT NULL DEFAULT 15.00
                          COMMENT 'Porcentaje IVA vigente: 0, 5, 12, 15',
  `rub_estado`        enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `rub_fecha_creacion` timestamp     NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rub_id`),
  KEY `idx_rub_tenant`        (`rub_tenant_id`),
  KEY `idx_rub_tenant_estado` (`rub_tenant_id`, `rub_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Rubros de facturación con configuración de IVA por tenant';

-- ── 2. Insertar rubros iniciales para cada tenant activo ──────────────────────

INSERT INTO facturacion_rubros
    (rub_tenant_id, rub_codigo, rub_nombre, rub_aplica_iva, rub_porcentaje_iva, rub_estado)
SELECT
    t.ten_tenant_id,
    r.codigo,
    r.nombre,
    r.aplica_iva,
    r.pct_iva,
    'ACTIVO'
FROM seguridad_tenants t
CROSS JOIN (
    SELECT 'MENS'   AS codigo, 'Mensualidad'               AS nombre, 1 AS aplica_iva, 15.00 AS pct_iva
    UNION ALL SELECT 'MAT',    'Matrícula',                             1,              15.00
    UNION ALL SELECT 'INSCR',  'Inscripción',                           1,              15.00
    UNION ALL SELECT 'UNIF',   'Uniforme / Equipamiento',               1,              15.00
    UNION ALL SELECT 'CERT',   'Certificado / Diploma',                 0,               0.00
    UNION ALL SELECT 'EVENTO', 'Evento / Torneo',                       1,              15.00
) r
WHERE t.ten_estado_suscripcion != 'CANCELADA'
  AND NOT EXISTS (
      SELECT 1 FROM facturacion_rubros
      WHERE rub_tenant_id = t.ten_tenant_id
        AND rub_codigo = r.codigo
  );

-- ── 3. Insertar item de menú ──────────────────────────────────────────────────

SET @mod_id = (
    SELECT mod_id FROM seguridad_modulos
    WHERE mod_codigo = 'facturacion'
    LIMIT 1
);

SET @padre_id = (
    SELECT men_padre_id
    FROM seguridad_menu
    WHERE men_ruta_modulo = 'facturacion'
      AND men_ruta_controller = 'formaPago'
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

INSERT INTO seguridad_menu
    (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono,
     men_ruta_modulo, men_ruta_controller, men_ruta_action,
     men_orden, men_activo)
SELECT
    @mod_id, @padre_id, 'ITEM',
    'Rubros', 'fas fa-tags',
    'facturacion', 'rubro', 'index',
    10, 1
FROM DUAL
WHERE @mod_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM seguridad_menu
      WHERE men_ruta_modulo      = 'facturacion'
        AND men_ruta_controller  = 'rubro'
        AND men_ruta_action      = 'index'
  );

-- ── 4. Asignar el menú a roles con acceso a facturación ──────────────────────

INSERT INTO seguridad_rol_menu (rme_rol_id, rme_menu_id, rme_puede_ver, rme_puede_acceder)
SELECT r.rol_rol_id, m.men_id, 1, 1
FROM seguridad_roles r
CROSS JOIN seguridad_menu m
WHERE m.men_ruta_modulo     = 'facturacion'
  AND m.men_ruta_controller = 'rubro'
  AND m.men_ruta_action     = 'index'
  AND m.men_activo          = 1
  AND r.rol_estado          = 'A'
  AND (
      r.rol_es_super_admin    = 'S'
   OR r.rol_es_admin_tenant   = 'S'
   OR r.rol_nivel_acceso     >= 3
  )
ON DUPLICATE KEY UPDATE rme_puede_ver = 1, rme_puede_acceder = 1;

-- ── 5. Registrar en seguridad_menu_config ────────────────────────────────────

INSERT INTO seguridad_menu_config
    (con_modulo_codigo, con_opcion, con_icono, con_color, con_permiso_requerido, con_orden)
VALUES
    ('facturacion', 'Rubros', 'fas fa-tags', '#8B5CF6', 'facturacion.ver', 10)
ON DUPLICATE KEY UPDATE
    con_icono             = VALUES(con_icono),
    con_permiso_requerido = VALUES(con_permiso_requerido),
    con_orden             = VALUES(con_orden);

-- ── 6. Verificación post-migración ───────────────────────────────────────────

SELECT
    t.ten_nombre_comercial  AS tenant,
    COUNT(r.rub_id)         AS rubros_total,
    SUM(r.rub_aplica_iva)   AS con_iva,
    COUNT(r.rub_id) - SUM(r.rub_aplica_iva) AS sin_iva
FROM seguridad_tenants t
LEFT JOIN facturacion_rubros r ON r.rub_tenant_id = t.ten_tenant_id
WHERE t.ten_estado_suscripcion != 'CANCELADA'
GROUP BY t.ten_tenant_id, t.ten_nombre_comercial
ORDER BY t.ten_tenant_id;
