-- =========================================================================
-- DigiSports - Script de Actualización - Subsistema de Facturación
-- Descripción: Crea las tablas con la nomenclatura unificada (módulo_entidad)
-- =========================================================================

-- 1. Formas de Pago
CREATE TABLE IF NOT EXISTS `facturacion_formas_pago` (
    `fpa_id` INT AUTO_INCREMENT PRIMARY KEY,
    `fpa_tenant_id` INT NOT NULL,
    `fpa_nombre` VARCHAR(50) NOT NULL,
    `fpa_codigo_sri` VARCHAR(2) NOT NULL,
    `fpa_estado` ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    `fpa_fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_fpa_tenant` (`fpa_tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar formas de pago básicas por defecto
INSERT IGNORE INTO `facturacion_formas_pago` (`fpa_id`, `fpa_tenant_id`, `fpa_nombre`, `fpa_codigo_sri`) VALUES
(1, 1, 'SIN UTILIZACION DEL SISTEMA FINANCIERO', '01'),
(2, 1, 'COMPENSACIÓN DE DEUDAS', '15'),
(3, 1, 'TARJETA DE DÉBITO', '16'),
(4, 1, 'DINERO ELECTRÓNICO', '17'),
(5, 1, 'TARJETA PREPAGO', '18'),
(6, 1, 'TARJETA DE CRÉDITO', '19'),
(7, 1, 'OTROS CON UTILIZACION DEL SISTEMA FINANCIERO', '20'),
(8, 1, 'ENDOSO DE TÍTULOS', '21');

-- 2. Facturas
CREATE TABLE IF NOT EXISTS `facturacion_facturas` (
    `fac_id` INT AUTO_INCREMENT PRIMARY KEY,
    `fac_tenant_id` INT NOT NULL,
    `fac_numero` VARCHAR(20) NOT NULL,
    `fac_cliente_id` INT NULL,
    `fac_origen_modulo` VARCHAR(50) NOT NULL DEFAULT 'libre', -- 'reservas', 'tienda', 'suscripciones', 'libre'
    `fac_origen_id` INT NULL,
    `fac_subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `fac_descuento` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `fac_iva` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `fac_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `fac_estado` ENUM('BORRADOR', 'EMITIDA', 'PAGADA', 'ANULADA') DEFAULT 'BORRADOR',
    `fac_fecha_emision` DATETIME NULL,
    `fac_fecha_vencimiento` DATE NULL,
    `fac_fecha_pago` DATETIME NULL,
    `fac_forma_pago_id` INT NULL,
    `fac_usuario_id` INT NOT NULL,
    `fac_observaciones` TEXT NULL,
    `fac_fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fac_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_fac_tenant` (`fac_tenant_id`),
    KEY `idx_fac_cliente` (`fac_cliente_id`),
    KEY `idx_fac_origen` (`fac_origen_modulo`, `fac_origen_id`),
    CONSTRAINT `fk_fac_forma_pago` FOREIGN KEY (`fac_forma_pago_id`) REFERENCES `facturacion_formas_pago` (`fpa_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Líneas de Factura (Detalles)
CREATE TABLE IF NOT EXISTS `facturacion_lineas` (
    `lin_id` INT AUTO_INCREMENT PRIMARY KEY,
    `lin_factura_id` INT NOT NULL,
    `lin_codigo` VARCHAR(20) NULL,
    `lin_descripcion` VARCHAR(255) NOT NULL,
    `lin_cantidad` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    `lin_precio_unitario` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `lin_descuento` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `lin_porcentaje_iva` DECIMAL(5,2) NOT NULL DEFAULT 15.00,
    `lin_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `lin_fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_lin_factura` (`lin_factura_id`),
    CONSTRAINT `fk_lin_factura` FOREIGN KEY (`lin_factura_id`) REFERENCES `facturacion_facturas` (`fac_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Pagos
CREATE TABLE IF NOT EXISTS `facturacion_pagos` (
    `pag_id` INT AUTO_INCREMENT PRIMARY KEY,
    `pag_factura_id` INT NOT NULL,
    `pag_usuario_id` INT NOT NULL,
    `pag_monto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `pag_forma_pago_id` INT NOT NULL,
    `pag_referencia` VARCHAR(100) NULL,
    `pag_fecha` DATE NOT NULL,
    `pag_estado` ENUM('CONFIRMADO', 'ANULADO') DEFAULT 'CONFIRMADO',
    `pag_observaciones` TEXT NULL,
    `pag_fecha_anulacion` DATETIME NULL,
    `pag_fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_pag_factura` (`pag_factura_id`),
    CONSTRAINT `fk_pag_factura` FOREIGN KEY (`pag_factura_id`) REFERENCES `facturacion_facturas` (`fac_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pag_forma_pago` FOREIGN KEY (`pag_forma_pago_id`) REFERENCES `facturacion_formas_pago` (`fpa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Comprobantes SRI (Para FacturaElectronicaModel)
CREATE TABLE IF NOT EXISTS `facturas_electronicas` (
    `fac_id` INT AUTO_INCREMENT PRIMARY KEY,
    `fac_ten_id` INT NOT NULL,
    `fac_factura_id` INT NULL,
    `fac_clave_acceso` VARCHAR(49) NOT NULL,
    `fac_tipo_comprobante` VARCHAR(2) NOT NULL DEFAULT '01',
    `fac_establecimiento` VARCHAR(3) NOT NULL DEFAULT '001',
    `fac_punto_emision` VARCHAR(3) NOT NULL DEFAULT '001',
    `fac_secuencial` VARCHAR(9) NOT NULL,
    `fac_fecha_emision` DATE NOT NULL,
    `fac_cli_cliente_id` INT NULL,
    `fac_cli_identificacion` VARCHAR(20) NOT NULL,
    `fac_cli_razon_social` VARCHAR(100) NOT NULL,
    `fac_subtotal` DECIMAL(10,2) NOT NULL,
    `fac_iva` DECIMAL(10,2) NOT NULL,
    `fac_total` DECIMAL(10,2) NOT NULL,
    `fac_estado_sri` VARCHAR(20) DEFAULT 'PENDIENTE',
    `fac_xml_generado` VARCHAR(255) NULL,
    `fac_xml_firmado` VARCHAR(255) NULL,
    `fac_xml_autorizado` VARCHAR(255) NULL,
    `fac_numero_autorizacion` VARCHAR(49) NULL,
    `fac_fecha_autorizacion` DATETIME NULL,
    `fac_mensaje_error` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_fac_clave_acceso` (`fac_clave_acceso`),
    KEY `idx_fac_ten_id` (`fac_ten_id`),
    CONSTRAINT `fk_sri_facturacion` FOREIGN KEY (`fac_factura_id`) REFERENCES `facturacion_facturas` (`fac_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Secuenciales SRI
CREATE TABLE IF NOT EXISTS `facturacion_secuenciales` (
    `sec_id` INT AUTO_INCREMENT PRIMARY KEY,
    `sec_tenant_id` INT NOT NULL,
    `sec_tipo_comprobante` VARCHAR(2) NOT NULL,
    `sec_establecimiento` VARCHAR(3) NOT NULL,
    `sec_punto_emision` VARCHAR(3) NOT NULL,
    `sec_siguiente` INT NOT NULL DEFAULT 1,
    `sec_fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_secuencial` (`sec_tenant_id`, `sec_tipo_comprobante`, `sec_establecimiento`, `sec_punto_emision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
