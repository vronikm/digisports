-- ============================================================
-- DigiSports - Migration 001
-- Tabla: seguridad_ips_bloqueadas
-- Reemplaza el archivo JSON storage/cache/blocked_ips.json
-- Ejecutar: mysql -u root digisports_core < 001_seguridad_ips_bloqueadas.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS seguridad_ips_bloqueadas (
    ib_id            INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    ib_ip            VARCHAR(45)     NOT NULL               COMMENT 'IPv4 o IPv6',
    ib_bloqueado_hasta DATETIME      NOT NULL               COMMENT 'Timestamp de expiración del bloqueo',
    ib_intentos      SMALLINT        NOT NULL DEFAULT 0     COMMENT 'Intentos fallidos que generaron el bloqueo',
    ib_razon         VARCHAR(255)    DEFAULT 'Múltiples intentos fallidos de login',
    ib_desbloqueado  TINYINT(1)      NOT NULL DEFAULT 0     COMMENT '1 = desbloqueado manualmente',
    ib_desbloqueado_por INT UNSIGNED DEFAULT NULL           COMMENT 'Usuario que desbloqueó',
    ib_creado_en     DATETIME        DEFAULT CURRENT_TIMESTAMP,
    ib_actualizado_en DATETIME       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_ip          (ib_ip),
    INDEX      idx_expiry     (ib_bloqueado_hasta),
    INDEX      idx_activo     (ib_desbloqueado, ib_bloqueado_hasta)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='IPs bloqueadas por exceso de intentos fallidos de login';


-- ============================================================
-- Migration 002 (incluida aquí para no crear archivo separado)
-- Índices complementarios en seguridad_log_accesos para
-- acelerar las consultas de brute-force detection
-- ============================================================

-- Índice compuesto para la consulta de detección de brute force:
-- WHERE acc_ip = ? AND acc_exito = 'N' AND acc_tipo = 'LOGIN_FAILED'
--   AND acc_fecha_hora > ...
-- Nota: ADD INDEX IF NOT EXISTS no está soportado en MySQL 8.0
-- Ejecutar manualmente si el índice no existe:
ALTER TABLE seguridad_log_accesos
    ADD INDEX idx_brute_force (acc_ip, acc_exito, acc_tipo, acc_fecha);

-- Índice para limpiezas periódicas de logs antiguos
ALTER TABLE seguridad_log_accesos
    ADD INDEX idx_fecha (acc_fecha);
