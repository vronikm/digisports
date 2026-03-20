-- ============================================================
-- Migration 014: Vincular futbol_becas con facturacion_rubros
-- Permite indicar a qué rubro de facturación aplica el descuento
-- Ejecutar en: digisports_core
-- ============================================================

ALTER TABLE `futbol_becas`
    ADD COLUMN `fbe_rubro_id` INT DEFAULT NULL
        COMMENT 'FK → facturacion_rubros (NULL = aplica a todos los rubros)',
    ADD KEY `idx_fbe_rubro` (`fbe_rubro_id`);
