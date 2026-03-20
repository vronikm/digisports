-- ============================================================
-- Migration 013: Vincular futbol_pagos con facturacion_facturas
-- Permite saber si un pago ya fue incluido en una factura
-- Ejecutar en: digisports_core
-- ============================================================

ALTER TABLE `futbol_pagos`
    ADD COLUMN `fpg_factura_id` INT DEFAULT NULL
        COMMENT 'FK → facturacion_facturas (NULL = sin facturar)',
    ADD KEY `idx_fpg_factura` (`fpg_factura_id`);
