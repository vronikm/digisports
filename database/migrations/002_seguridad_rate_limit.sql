-- ============================================================
-- DigiSports - Migration 002
-- Tabla: seguridad_rate_limit
-- Reemplaza el archivo JSON storage/cache/rate_limit.json
-- Ejecutar: mysql -u root digisports_core < 002_seguridad_rate_limit.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS seguridad_rate_limit (
    srl_id      BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    srl_ip      VARCHAR(45)      NOT NULL COMMENT 'IPv4 o IPv6',
    srl_action  VARCHAR(100)     NOT NULL COMMENT 'Identificador de la acción limitada',
    srl_fecha   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp del request',
    PRIMARY KEY (srl_id),
    INDEX idx_lookup (srl_ip, srl_action, srl_fecha),
    INDEX idx_purge  (srl_fecha)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro de requests para rate limiting por IP y acción';
