-- ============================================================
-- Migración 019: corregir ENUM fin_estado en futbol_inscripciones
-- Reemplaza 'FINALIZADA' por 'COMPLETADA' (alineación con código)
-- ============================================================

-- 1. Si existen registros con 'FINALIZADA' (inconsistencia previa), migrarlos
UPDATE `futbol_inscripciones` SET `fin_estado` = 'COMPLETADA' WHERE `fin_estado` = 'FINALIZADA';

-- 2. Redefinir el ENUM con el valor correcto
ALTER TABLE `futbol_inscripciones`
    MODIFY COLUMN `fin_estado`
        enum('ACTIVA','SUSPENDIDA','COMPLETADA','CANCELADA')
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVA';
