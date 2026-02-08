-- ═══════════════════════════════════════════════════════════
-- DigiSports — Migración: Columnas de Blind Index para Protección de Datos
-- LOPDP Ecuador — Fecha: 2026-02-07
-- 
-- Agrega columnas _hash (VARCHAR(32)) para búsquedas exactas
-- sobre campos sensibles que serán cifrados.
-- ═══════════════════════════════════════════════════════════

-- ── seguridad_usuarios ──
ALTER TABLE seguridad_usuarios
    ADD COLUMN IF NOT EXISTS usu_identificacion_hash VARCHAR(32) NULL DEFAULT NULL AFTER usu_identificacion,
    ADD COLUMN IF NOT EXISTS usu_email_hash VARCHAR(32) NULL DEFAULT NULL AFTER usu_email;

-- Índices para búsqueda por hash
CREATE INDEX IF NOT EXISTS idx_usu_identificacion_hash ON seguridad_usuarios (usu_identificacion_hash);
CREATE INDEX IF NOT EXISTS idx_usu_email_hash ON seguridad_usuarios (usu_email_hash);

-- ── seguridad_tenants ──
ALTER TABLE seguridad_tenants
    ADD COLUMN IF NOT EXISTS ten_ruc_hash VARCHAR(32) NULL DEFAULT NULL AFTER ten_ruc,
    ADD COLUMN IF NOT EXISTS ten_email_hash VARCHAR(32) NULL DEFAULT NULL AFTER ten_email,
    ADD COLUMN IF NOT EXISTS ten_representante_identificacion_hash VARCHAR(32) NULL DEFAULT NULL AFTER ten_representante_identificacion,
    ADD COLUMN IF NOT EXISTS ten_representante_email_hash VARCHAR(32) NULL DEFAULT NULL AFTER ten_representante_email;

CREATE INDEX IF NOT EXISTS idx_ten_ruc_hash ON seguridad_tenants (ten_ruc_hash);
CREATE INDEX IF NOT EXISTS idx_ten_email_hash ON seguridad_tenants (ten_email_hash);
CREATE INDEX IF NOT EXISTS idx_ten_rep_id_hash ON seguridad_tenants (ten_representante_identificacion_hash);
CREATE INDEX IF NOT EXISTS idx_ten_rep_email_hash ON seguridad_tenants (ten_representante_email_hash);

-- ── clientes ──
ALTER TABLE clientes
    ADD COLUMN IF NOT EXISTS cli_identificacion_hash VARCHAR(32) NULL DEFAULT NULL AFTER cli_identificacion,
    ADD COLUMN IF NOT EXISTS cli_email_hash VARCHAR(32) NULL DEFAULT NULL AFTER cli_email;

CREATE INDEX IF NOT EXISTS idx_cli_identificacion_hash ON clientes (cli_identificacion_hash);
CREATE INDEX IF NOT EXISTS idx_cli_email_hash ON clientes (cli_email_hash);

-- Ampliar columnas de datos sensibles para almacenar datos cifrados (base64 necesita más espacio)
-- El cifrado AES-256-CBC + IV + prefijo "ENC::" genera ~120 chars para un email típico
ALTER TABLE seguridad_usuarios
    MODIFY COLUMN usu_identificacion VARCHAR(255) NULL,
    MODIFY COLUMN usu_email VARCHAR(500) NULL,
    MODIFY COLUMN usu_telefono VARCHAR(255) NULL,
    MODIFY COLUMN usu_celular VARCHAR(255) NULL;

ALTER TABLE seguridad_tenants
    MODIFY COLUMN ten_ruc VARCHAR(255) NULL,
    MODIFY COLUMN ten_email VARCHAR(500) NULL,
    MODIFY COLUMN ten_telefono VARCHAR(255) NULL,
    MODIFY COLUMN ten_celular VARCHAR(255) NULL,
    MODIFY COLUMN ten_representante_identificacion VARCHAR(255) NULL,
    MODIFY COLUMN ten_representante_email VARCHAR(500) NULL,
    MODIFY COLUMN ten_representante_telefono VARCHAR(255) NULL;

ALTER TABLE clientes
    MODIFY COLUMN cli_identificacion VARCHAR(255) NULL,
    MODIFY COLUMN cli_email VARCHAR(500) NULL,
    MODIFY COLUMN cli_telefono VARCHAR(255) NULL,
    MODIFY COLUMN cli_celular VARCHAR(255) NULL;
