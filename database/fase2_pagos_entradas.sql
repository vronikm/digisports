-- ===================================================================
-- DigiSports Arena — Fase 2: Sistema de Pagos y Entradas
-- Fecha: 2026-02-08
-- ===================================================================

-- ─────────────────────────────────────────────────
-- 1. Tabla de entradas / tickets de acceso
-- ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS instalaciones_entradas (
    ent_entrada_id      INT AUTO_INCREMENT PRIMARY KEY,
    ent_tenant_id       INT NOT NULL,
    ent_instalacion_id  INT NOT NULL,
    ent_cliente_id      INT NULL,
    ent_codigo          VARCHAR(20) NOT NULL COMMENT 'Código alfanumérico único del ticket',
    ent_tipo            ENUM('GENERAL','VIP','CORTESIA','ABONADO') NOT NULL DEFAULT 'GENERAL',
    ent_precio          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ent_descuento       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ent_total           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ent_forma_pago      VARCHAR(50) NULL COMMENT 'EFECTIVO, TARJETA, MONEDERO, MIXTO',
    ent_monto_monedero  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ent_monto_efectivo  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ent_estado          ENUM('VENDIDA','USADA','ANULADA','VENCIDA') NOT NULL DEFAULT 'VENDIDA',
    ent_fecha_entrada   DATE NOT NULL,
    ent_hora_entrada     TIME NULL COMMENT 'Hora de ingreso real',
    ent_hora_salida     TIME NULL COMMENT 'Hora de salida',
    ent_observaciones   TEXT NULL,
    ent_usuario_registro INT NULL,
    ent_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_ent_tenant (ent_tenant_id),
    INDEX idx_ent_fecha (ent_fecha_entrada),
    INDEX idx_ent_codigo (ent_codigo),
    INDEX idx_ent_cliente (ent_cliente_id),
    INDEX idx_ent_estado (ent_estado),

    CONSTRAINT fk_ent_tenant FOREIGN KEY (ent_tenant_id) 
        REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_ent_instalacion FOREIGN KEY (ent_instalacion_id) 
        REFERENCES instalaciones(ins_instalacion_id) ON DELETE CASCADE,
    CONSTRAINT fk_ent_cliente FOREIGN KEY (ent_cliente_id) 
        REFERENCES clientes(cli_cliente_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────
-- 2. Tabla de tarifas de entrada por instalación
-- ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS instalaciones_entradas_tarifas (
    ent_tar_id          INT AUTO_INCREMENT PRIMARY KEY,
    ent_tar_tenant_id   INT NOT NULL,
    ent_tar_instalacion_id INT NOT NULL,
    ent_tar_nombre      VARCHAR(100) NOT NULL,
    ent_tar_tipo        ENUM('GENERAL','VIP','CORTESIA','ABONADO') NOT NULL DEFAULT 'GENERAL',
    ent_tar_precio      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ent_tar_dia_semana  TINYINT NULL COMMENT '0=Dom, 1=Lun... NULL=todos',
    ent_tar_hora_inicio TIME NULL,
    ent_tar_hora_fin    TIME NULL,
    ent_tar_estado      ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    ent_tar_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_etf_tenant (ent_tar_tenant_id),
    INDEX idx_etf_inst (ent_tar_instalacion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────
-- 3. Vista de compatibilidad: entradas
-- ─────────────────────────────────────────────────
CREATE OR REPLACE VIEW entradas AS
SELECT
    ent_entrada_id      AS entrada_id,
    ent_tenant_id       AS tenant_id,
    ent_instalacion_id  AS instalacion_id,
    ent_cliente_id      AS cliente_id,
    ent_codigo          AS codigo,
    ent_tipo            AS tipo,
    ent_precio          AS precio,
    ent_descuento       AS descuento,
    ent_total           AS total,
    ent_forma_pago      AS forma_pago,
    ent_monto_monedero  AS monto_monedero,
    ent_monto_efectivo  AS monto_efectivo,
    ent_estado          AS estado,
    ent_fecha_entrada   AS fecha_entrada,
    ent_hora_entrada    AS hora_entrada,
    ent_hora_salida     AS hora_salida,
    ent_observaciones   AS observaciones,
    ent_usuario_registro AS usuario_registro,
    ent_fecha_registro  AS fecha_registro
FROM instalaciones_entradas;
