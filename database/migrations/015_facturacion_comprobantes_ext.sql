-- ============================================================
-- Migración 015: Tabla de seguimiento de comprobantes externos
-- Evita facturar dos veces el mismo pago de BD externas
-- (digitech_soccereasy, digitech_cdjg, digitech_adfpl)
-- ============================================================

CREATE TABLE IF NOT EXISTS facturacion_comprobantes_ext (
    fce_id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    fce_tenant_id   INT UNSIGNED    NOT NULL,
    fce_fac_id      INT UNSIGNED    NOT NULL COMMENT 'FK a facturacion_facturas.fac_id',
    fce_ext_db      VARCHAR(100)    NOT NULL COMMENT 'Nombre de la BD externa (sin prefijo ext_)',
    fce_ext_pago_id INT UNSIGNED    NOT NULL COMMENT 'pago_id en la BD externa',
    fce_created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (fce_id),

    -- Garantiza unicidad: un pago externo no puede aparecer en dos facturas activas
    UNIQUE KEY uk_ext_pago (fce_tenant_id, fce_ext_db, fce_ext_pago_id),

    KEY idx_fac_id   (fce_fac_id),
    KEY idx_tenant   (fce_tenant_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro de pagos externos ya incluidos en facturas DigiSports';
