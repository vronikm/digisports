-- ============================================================
-- Migración 016: futbol_pagos — campo saldo + métodos de pago
-- ============================================================

-- 1. Agregar columna fpg_saldo (diferencia entre total calculado y lo pagado)
ALTER TABLE `futbol_pagos`
    ADD COLUMN `fpg_saldo` decimal(10,2) NOT NULL DEFAULT '0.00'
        COMMENT 'Saldo pendiente = (monto - beca - descuento) - total_pagado'
    AFTER `fpg_total`;

-- 2. Ampliar enum fpg_metodo_pago con los nuevos canales de pago
ALTER TABLE `futbol_pagos`
    MODIFY COLUMN `fpg_metodo_pago`
        enum('EFECTIVO','TARJETA','TRANSFERENCIA','DEPOSITO','ABONO','CHEQUE','PAYPHONE','OTRO')
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'EFECTIVO';

-- 3. Calcular saldo para registros existentes
UPDATE `futbol_pagos`
SET `fpg_saldo` = GREATEST(0, (`fpg_monto` - `fpg_beca_descuento` - `fpg_descuento`) - `fpg_total`);
