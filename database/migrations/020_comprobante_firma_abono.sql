-- ============================================================
-- Migración 020: Ampliar tablas para sistema de recibos
-- ============================================================

-- 1. Agregar logo y firma digital a sedes
ALTER TABLE `instalaciones_sedes`
    ADD COLUMN IF NOT EXISTS `sed_logo_id`  INT DEFAULT NULL COMMENT 'arc_id del logo (core_archivos)',
    ADD COLUMN IF NOT EXISTS `sed_firma_id` INT DEFAULT NULL COMMENT 'arc_id de la firma digital (core_archivos)';

-- 2. Ampliar futbol_comprobantes para abonos y saldo
ALTER TABLE `futbol_comprobantes`
    ADD COLUMN IF NOT EXISTS `fcm_abono_id` INT     DEFAULT NULL COMMENT 'fab_abono_id si el comprobante es de un abono',
    ADD COLUMN IF NOT EXISTS `fcm_saldo`    DECIMAL(10,2) DEFAULT NULL COMMENT 'Saldo pendiente al momento de emitir';
