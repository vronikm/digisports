-- ============================================================================
-- DigiSports Store: Migración de store_clientes → extensión de clientes
-- Fecha: 2026-02-09
-- 
-- store_clientes deja de duplicar datos personales y pasa a ser una tabla
-- de extensión que referencia la tabla compartida `clientes`.
-- Los datos personales se consultan siempre desde `clientes`.
-- store_clientes solo almacena datos específicos del módulo Store.
-- ============================================================================

-- 1. Eliminar la tabla store_clientes antigua (está vacía, no hay datos que migrar)
--    Primero eliminar dependencias (store_cliente_puntos_log FK)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS store_cliente_puntos_log;
DROP TABLE IF EXISTS store_clientes;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. Crear la nueva store_clientes como tabla de EXTENSIÓN
CREATE TABLE store_clientes (
    scl_id              INT NOT NULL AUTO_INCREMENT,
    scl_tenant_id       INT NOT NULL,
    scl_cliente_id      INT NOT NULL COMMENT 'FK a clientes.cli_cliente_id (tabla compartida)',
    
    -- Datos específicos de Store (fidelización/CRM)
    scl_categoria       ENUM('NUEVO','REGULAR','FRECUENTE','VIP') DEFAULT 'NUEVO',
    scl_puntos_acumulados  INT DEFAULT 0 COMMENT 'Total histórico de puntos ganados',
    scl_puntos_canjeados   INT DEFAULT 0,
    scl_puntos_disponibles INT GENERATED ALWAYS AS (scl_puntos_acumulados - scl_puntos_canjeados) STORED,
    scl_total_compras   DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Lifetime value en Store',
    scl_num_compras     INT DEFAULT 0,
    scl_ultima_compra   DATE DEFAULT NULL,
    scl_acepta_marketing TINYINT(1) DEFAULT 0,
    scl_notas           TEXT DEFAULT NULL COMMENT 'Notas específicas de tienda',
    scl_activo          TINYINT(1) DEFAULT 1,
    scl_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    scl_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (scl_id),
    UNIQUE KEY uk_scl_tenant_cliente (scl_tenant_id, scl_cliente_id),
    KEY idx_scl_categoria (scl_tenant_id, scl_categoria),
    
    CONSTRAINT fk_scl_tenant  FOREIGN KEY (scl_tenant_id)  REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_scl_cliente FOREIGN KEY (scl_cliente_id)  REFERENCES clientes(cli_cliente_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Recrear store_cliente_puntos_log apuntando a la nueva tabla
CREATE TABLE store_cliente_puntos_log (
    cpl_log_id          INT NOT NULL AUTO_INCREMENT,
    cpl_tenant_id       INT NOT NULL,
    cpl_scl_id          INT NOT NULL COMMENT 'FK a store_clientes.scl_id',
    cpl_cliente_id      INT NOT NULL COMMENT 'FK a clientes.cli_cliente_id (para consultas rápidas)',
    cpl_tipo            ENUM('ACUMULACION','CANJE','AJUSTE','EXPIRACION') NOT NULL,
    cpl_puntos          INT NOT NULL,
    cpl_saldo_anterior  INT DEFAULT 0,
    cpl_saldo_nuevo     INT DEFAULT 0,
    cpl_descripcion     VARCHAR(255) DEFAULT NULL,
    cpl_referencia_tipo VARCHAR(50) DEFAULT NULL COMMENT 'VENTA, DEVOLUCION, MANUAL',
    cpl_referencia_id   INT DEFAULT NULL,
    cpl_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (cpl_log_id),
    KEY idx_cpl_scl (cpl_scl_id),
    KEY idx_cpl_cliente (cpl_tenant_id, cpl_cliente_id),
    
    CONSTRAINT fk_cpl_tenant FOREIGN KEY (cpl_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_cpl_scl    FOREIGN KEY (cpl_scl_id)     REFERENCES store_clientes(scl_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. store_ventas.ven_cliente_id ahora apunta a clientes.cli_cliente_id
--    (La columna ya existe, solo necesitamos saber que ahora referencia la tabla compartida)
--    No necesita FK obligatoria porque ventas puede ser sin cliente (consumidor final)
