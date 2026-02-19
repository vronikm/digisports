-- ╔═══════════════════════════════════════════════════════════════════╗
-- ║  DigiSports Natación — Integración de Sedes                    ║
-- ║  Fecha: 2026-02-09                                              ║
-- ║  Agrega sede_id a tablas clave + tabla de egresos por sede     ║
-- ╚═══════════════════════════════════════════════════════════════════╝

SET FOREIGN_KEY_CHECKS = 0;

-- ══════════════════════════════════════════════════════════════
-- 1. Agregar sede_id a la tabla compartida alumnos
-- ══════════════════════════════════════════════════════════════

ALTER TABLE `alumnos`
    ADD COLUMN `alu_sede_id` INT DEFAULT NULL COMMENT 'FK → instalaciones_sedes' AFTER `alu_tenant_id`,
    ADD INDEX `idx_alu_sede` (`alu_sede_id`),
    ADD CONSTRAINT `fk_alu_sede` FOREIGN KEY (`alu_sede_id`) REFERENCES `instalaciones_sedes`(`sed_sede_id`) ON DELETE SET NULL;


-- ══════════════════════════════════════════════════════════════
-- 2. Agregar sede_id a natacion_piscinas
-- ══════════════════════════════════════════════════════════════

ALTER TABLE `natacion_piscinas`
    ADD COLUMN `npi_sede_id` INT DEFAULT NULL COMMENT 'FK → instalaciones_sedes' AFTER `npi_tenant_id`,
    ADD INDEX `idx_npi_sede` (`npi_sede_id`),
    ADD CONSTRAINT `fk_npi_sede` FOREIGN KEY (`npi_sede_id`) REFERENCES `instalaciones_sedes`(`sed_sede_id`) ON DELETE SET NULL;


-- ══════════════════════════════════════════════════════════════
-- 3. Agregar sede_id a natacion_instructores
-- ══════════════════════════════════════════════════════════════

ALTER TABLE `natacion_instructores`
    ADD COLUMN `nin_sede_id` INT DEFAULT NULL COMMENT 'FK → instalaciones_sedes' AFTER `nin_tenant_id`,
    ADD INDEX `idx_nin_sede` (`nin_sede_id`),
    ADD CONSTRAINT `fk_nin_sede` FOREIGN KEY (`nin_sede_id`) REFERENCES `instalaciones_sedes`(`sed_sede_id`) ON DELETE SET NULL;


-- ══════════════════════════════════════════════════════════════
-- 4. Agregar sede_id a natacion_grupos
-- ══════════════════════════════════════════════════════════════

ALTER TABLE `natacion_grupos`
    ADD COLUMN `ngr_sede_id` INT DEFAULT NULL COMMENT 'FK → instalaciones_sedes' AFTER `ngr_tenant_id`,
    ADD INDEX `idx_ngr_sede` (`ngr_sede_id`),
    ADD CONSTRAINT `fk_ngr_sede` FOREIGN KEY (`ngr_sede_id`) REFERENCES `instalaciones_sedes`(`sed_sede_id`) ON DELETE SET NULL;


-- ══════════════════════════════════════════════════════════════
-- 5. Agregar sede_id a natacion_pagos
-- ══════════════════════════════════════════════════════════════

ALTER TABLE `natacion_pagos`
    ADD COLUMN `npg_sede_id` INT DEFAULT NULL COMMENT 'FK → instalaciones_sedes' AFTER `npg_tenant_id`,
    ADD INDEX `idx_npg_sede` (`npg_sede_id`),
    ADD CONSTRAINT `fk_npg_sede` FOREIGN KEY (`npg_sede_id`) REFERENCES `instalaciones_sedes`(`sed_sede_id`) ON DELETE SET NULL;


-- ══════════════════════════════════════════════════════════════
-- 6. Tabla de Egresos por Sede (gastos operativos)
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_egresos` (
    `neg_egreso_id`         INT AUTO_INCREMENT PRIMARY KEY,
    `neg_tenant_id`         INT NOT NULL,
    `neg_sede_id`           INT DEFAULT NULL COMMENT 'FK → instalaciones_sedes',
    `neg_categoria`         ENUM('MANTENIMIENTO','INSUMOS','QUIMICOS','SERVICIOS','PERSONAL','EQUIPAMIENTO','SEGUROS','MARKETING','OTROS') DEFAULT 'OTROS',
    `neg_concepto`          VARCHAR(200) NOT NULL COMMENT 'Descripción del gasto',
    `neg_monto`             DECIMAL(10,2) NOT NULL,
    `neg_fecha`             DATE NOT NULL,
    `neg_proveedor`         VARCHAR(150) DEFAULT NULL,
    `neg_factura_ref`       VARCHAR(50) DEFAULT NULL COMMENT 'Nro factura o documento soporte',
    `neg_metodo_pago`       ENUM('EFECTIVO','TARJETA','TRANSFERENCIA','CHEQUE','OTRO') DEFAULT 'EFECTIVO',
    `neg_referencia_pago`   VARCHAR(100) DEFAULT NULL,
    `neg_estado`            ENUM('REGISTRADO','PAGADO','ANULADO') DEFAULT 'REGISTRADO',
    `neg_notas`             TEXT DEFAULT NULL,
    `neg_created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `neg_updated_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_neg_tenant_sede` (`neg_tenant_id`, `neg_sede_id`),
    INDEX `idx_neg_fecha` (`neg_fecha`),
    INDEX `idx_neg_categoria` (`neg_categoria`),
    CONSTRAINT `fk_neg_tenant` FOREIGN KEY (`neg_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_neg_sede` FOREIGN KEY (`neg_sede_id`) REFERENCES `instalaciones_sedes`(`sed_sede_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Egresos/gastos operativos de natación por sede';


-- ══════════════════════════════════════════════════════════════
-- 7. Actualizar datos existentes: Asignar sede principal
-- ══════════════════════════════════════════════════════════════

-- Asignar la sede principal del tenant a los registros existentes sin sede
UPDATE `alumnos` a
    JOIN `instalaciones_sedes` s ON s.sed_tenant_id = a.alu_tenant_id AND s.sed_es_principal = 'S'
    SET a.alu_sede_id = s.sed_sede_id
    WHERE a.alu_sede_id IS NULL;

UPDATE `natacion_piscinas` p
    JOIN `instalaciones_sedes` s ON s.sed_tenant_id = p.npi_tenant_id AND s.sed_es_principal = 'S'
    SET p.npi_sede_id = s.sed_sede_id
    WHERE p.npi_sede_id IS NULL;

UPDATE `natacion_instructores` i
    JOIN `instalaciones_sedes` s ON s.sed_tenant_id = i.nin_tenant_id AND s.sed_es_principal = 'S'
    SET i.nin_sede_id = s.sed_sede_id
    WHERE i.nin_sede_id IS NULL;

UPDATE `natacion_grupos` g
    JOIN `instalaciones_sedes` s ON s.sed_tenant_id = g.ngr_tenant_id AND s.sed_es_principal = 'S'
    SET g.ngr_sede_id = s.sed_sede_id
    WHERE g.ngr_sede_id IS NULL;

UPDATE `natacion_pagos` p
    JOIN `instalaciones_sedes` s ON s.sed_tenant_id = p.npg_tenant_id AND s.sed_es_principal = 'S'
    SET p.npg_sede_id = s.sed_sede_id
    WHERE p.npg_sede_id IS NULL;


-- ══════════════════════════════════════════════════════════════
-- 8. Menú: Agregar ítems Sedes y Egresos al módulo Natación
-- ══════════════════════════════════════════════════════════════

SET @nat_mod_id = (SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = 'NATACION');

-- Agregar Sedes al header Infraestructura (después de Períodos)
SET @h3_id = (SELECT men_id FROM seguridad_menu WHERE men_modulo_id = @nat_mod_id AND men_tipo = 'HEADER' AND men_label = 'Infraestructura' LIMIT 1);

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@nat_mod_id, @h3_id, 'ITEM', 'Sedes', 'fas fa-building', 'natacion', 'sede', 'index', 5, 1);

-- Agregar Egresos al header Financiero (después de Reportes)
SET @h4_id = (SELECT men_id FROM seguridad_menu WHERE men_modulo_id = @nat_mod_id AND men_tipo = 'HEADER' AND men_label = 'Financiero' LIMIT 1);

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@nat_mod_id, @h4_id, 'ITEM', 'Egresos', 'fas fa-file-invoice-dollar', 'natacion', 'egreso', 'index', 3, 1);


SET FOREIGN_KEY_CHECKS = 1;
