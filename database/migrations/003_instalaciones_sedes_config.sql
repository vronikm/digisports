-- ============================================================
-- Migration 003: Configuración financiera y logo en sedes
-- Aplica: ALTER TABLE instalaciones_sedes
-- Ejecutar: una sola vez en la BD digisports_core
-- ============================================================

ALTER TABLE `instalaciones_sedes`
    ADD COLUMN `sed_monto_mensualidad`  DECIMAL(10,2) NOT NULL DEFAULT 0.00
        COMMENT 'Valor de mensualidad configurado para esta sede'
        AFTER `sed_tienda`,

    ADD COLUMN `sed_monto_matricula`    DECIMAL(10,2) NOT NULL DEFAULT 0.00
        COMMENT 'Valor de matrícula configurado para esta sede'
        AFTER `sed_monto_mensualidad`,

    ADD COLUMN `sed_comprobante_inicio` INT UNSIGNED  NOT NULL DEFAULT 1
        COMMENT 'Número de comprobante/recibo inicial para la secuencia de esta sede'
        AFTER `sed_monto_matricula`;

-- El logo se almacena en core_archivos con:
--   arc_entidad = 'instalaciones_sedes'
--   arc_entidad_id = sed_sede_id
--   arc_categoria = 'logos'
--   arc_es_principal = 1
-- No requiere columna FK adicional en esta tabla.
