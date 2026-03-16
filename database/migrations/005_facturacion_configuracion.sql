-- ============================================================
-- Migration 005: Configuración de Facturación Electrónica por Tenant
-- Aplica: CREATE TABLE facturacion_configuracion
-- Ejecutar: una sola vez en la BD digisports_core
-- ============================================================

CREATE TABLE IF NOT EXISTS `facturacion_configuracion` (

    -- ── Identificación ───────────────────────────────────────
    `cfg_id`                        INT            NOT NULL AUTO_INCREMENT,
    `cfg_tenant_id`                 INT            NOT NULL,

    -- ── Datos del Emisor ─────────────────────────────────────
    `cfg_ruc`                       VARCHAR(13)    NOT NULL         COMMENT 'RUC de 13 dígitos del emisor',
    `cfg_razon_social`              VARCHAR(300)   NOT NULL         COMMENT 'Razón social según SRI (mayúsculas)',
    `cfg_nombre_comercial`          VARCHAR(300)   NOT NULL DEFAULT '' COMMENT 'Nombre comercial impreso en el RIDE',
    `cfg_direccion_matriz`          VARCHAR(300)   NOT NULL DEFAULT '' COMMENT 'Dirección de la matriz',
    `cfg_direccion_establecimiento` VARCHAR(300)   NOT NULL DEFAULT '' COMMENT 'Dirección del establecimiento emisor',
    `cfg_codigo_establecimiento`    CHAR(3)        NOT NULL DEFAULT '001',
    `cfg_punto_emision`             CHAR(3)        NOT NULL DEFAULT '001',
    `cfg_obligado_contabilidad`     ENUM('SI','NO') NOT NULL DEFAULT 'SI',
    `cfg_contribuyente_especial`    VARCHAR(10)    NOT NULL DEFAULT '' COMMENT 'Número de resolución, vacío si no aplica',
    `cfg_agente_retencion`          VARCHAR(10)    NOT NULL DEFAULT '' COMMENT 'Número de resolución agente retención',
    `cfg_regimen_microempresas`     ENUM('SI','NO') NOT NULL DEFAULT 'NO',
    `cfg_regimen_rimpe`             ENUM('SI','NO') NOT NULL DEFAULT 'NO',

    -- ── Ambiente SRI ─────────────────────────────────────────
    `cfg_ambiente`                  TINYINT(1)     NOT NULL DEFAULT 1  COMMENT '1=Pruebas, 2=Producción',
    `cfg_secuencial_inicio`         INT UNSIGNED   NOT NULL DEFAULT 1  COMMENT 'Número desde el que inicia la secuencia',

    -- ── Logo ─────────────────────────────────────────────────
    -- El logo se almacena en core_archivos (arc_entidad=facturacion_configuracion,
    -- arc_categoria=logos, arc_es_principal=1). Solo guardamos el arc_id aquí.
    `cfg_logo_arc_id`               INT            DEFAULT NULL       COMMENT 'FK a core_archivos',

    -- ── Firma Electrónica ─────────────────────────────────────
    `cfg_certificado_ruta`          VARCHAR(500)   DEFAULT NULL       COMMENT 'Ruta al archivo .p12 en el filesystem',
    `cfg_certificado_clave`         TEXT           DEFAULT NULL       COMMENT 'Contraseña del .p12 cifrada con AES-256-GCM',
    `cfg_certificado_vigencia`      DATE           DEFAULT NULL       COMMENT 'Fecha de vencimiento del certificado',

    -- ── Notificaciones ────────────────────────────────────────
    `cfg_email_notificaciones`      VARCHAR(200)   DEFAULT NULL       COMMENT 'Email para notificaciones de FE',

    -- ── Auditoría ─────────────────────────────────────────────
    `cfg_estado`                    CHAR(1)        NOT NULL DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
    `cfg_created_at`                TIMESTAMP      NULL DEFAULT CURRENT_TIMESTAMP,
    `cfg_updated_at`                TIMESTAMP      NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `cfg_created_by`                INT            DEFAULT NULL,
    `cfg_updated_by`                INT            DEFAULT NULL,

    PRIMARY KEY (`cfg_id`),
    UNIQUE KEY `uk_tenant` (`cfg_tenant_id`),
    KEY `idx_tenant_estado` (`cfg_tenant_id`, `cfg_estado`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Configuración de Facturación Electrónica por Tenant';
