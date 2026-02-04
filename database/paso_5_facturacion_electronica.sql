-- =====================================================
-- DigiSports - Migración: Facturación Electrónica SRI
-- Sistema de comprobantes electrónicos Ecuador
-- =====================================================

-- Tabla principal de facturas electrónicas
CREATE TABLE IF NOT EXISTS facturas_electronicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    
    -- Referencia a factura original (si existe)
    factura_id INT DEFAULT NULL,
    
    -- Datos del comprobante SRI
    clave_acceso VARCHAR(49) NOT NULL UNIQUE,
    tipo_comprobante CHAR(2) NOT NULL DEFAULT '01' COMMENT '01=Factura, 04=Nota Crédito, 05=Nota Débito, 06=Guía Remisión, 07=Retención',
    establecimiento CHAR(3) NOT NULL,
    punto_emision CHAR(3) NOT NULL,
    secuencial CHAR(9) NOT NULL,
    fecha_emision DATE NOT NULL,
    
    -- Datos del cliente/receptor
    cliente_id INT DEFAULT NULL,
    cliente_tipo_identificacion CHAR(2) NOT NULL COMMENT '04=RUC, 05=Cédula, 06=Pasaporte, 07=Cons.Final',
    cliente_identificacion VARCHAR(20) NOT NULL,
    cliente_razon_social VARCHAR(300) NOT NULL,
    cliente_direccion VARCHAR(300) DEFAULT NULL,
    cliente_email VARCHAR(200) DEFAULT NULL,
    cliente_telefono VARCHAR(50) DEFAULT NULL,
    
    -- Valores
    subtotal_iva DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal con IVA',
    subtotal_0 DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal 0%',
    subtotal_no_objeto DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal no objeto de IVA',
    subtotal_exento DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal exento',
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    descuento DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    iva DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    ice DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    irbpnr DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    propina DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    
    -- Estado y respuesta SRI
    estado_sri ENUM('PENDIENTE', 'GENERADA', 'FIRMADA', 'ENVIADA', 'RECIBIDA', 'DEVUELTA', 'AUTORIZADO', 'NO_AUTORIZADO', 'ERROR', 'ANULADA') NOT NULL DEFAULT 'PENDIENTE',
    ambiente CHAR(1) NOT NULL DEFAULT '1' COMMENT '1=Pruebas, 2=Producción',
    tipo_emision CHAR(1) NOT NULL DEFAULT '1' COMMENT '1=Normal, 2=Contingencia',
    
    -- Archivos XML
    xml_generado TEXT DEFAULT NULL COMMENT 'Ruta al archivo XML generado',
    xml_firmado TEXT DEFAULT NULL COMMENT 'Ruta al archivo XML firmado',
    xml_autorizado TEXT DEFAULT NULL COMMENT 'Ruta al archivo XML autorizado',
    
    -- Datos de autorización
    numero_autorizacion VARCHAR(49) DEFAULT NULL,
    fecha_autorizacion DATETIME DEFAULT NULL,
    
    -- Errores y mensajes
    mensaje_error TEXT DEFAULT NULL,
    intentos_envio INT NOT NULL DEFAULT 0,
    ultimo_intento DATETIME DEFAULT NULL,
    
    -- Metadatos
    observaciones TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT DEFAULT NULL COMMENT 'Referencia a usuarios.usuario_id',
    
    -- Índices
    INDEX idx_tenant (tenant_id),
    INDEX idx_factura (factura_id),
    INDEX idx_clave_acceso (clave_acceso),
    INDEX idx_fecha (fecha_emision),
    INDEX idx_estado (estado_sri),
    INDEX idx_cliente_identificacion (cliente_identificacion),
    INDEX idx_numero_completo (establecimiento, punto_emision, secuencial),
    INDEX idx_autorizacion (numero_autorizacion),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Facturas electrónicas emitidas al SRI';

-- Tabla de detalles de factura electrónica
CREATE TABLE IF NOT EXISTS facturas_electronicas_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factura_electronica_id INT NOT NULL,
    
    -- Datos del producto/servicio
    codigo_principal VARCHAR(25) DEFAULT NULL COMMENT 'Código interno',
    codigo_auxiliar VARCHAR(25) DEFAULT NULL COMMENT 'Código barras, etc.',
    descripcion VARCHAR(300) NOT NULL,
    cantidad DECIMAL(14,6) NOT NULL,
    precio_unitario DECIMAL(14,6) NOT NULL,
    descuento DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    precio_total_sin_impuesto DECIMAL(14,2) NOT NULL,
    
    -- Referencia a producto/servicio (si aplica)
    producto_id INT DEFAULT NULL,
    servicio_id INT DEFAULT NULL,
    instalacion_id INT DEFAULT NULL,
    reserva_id INT DEFAULT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_factura (factura_electronica_id),
    FOREIGN KEY (factura_electronica_id) REFERENCES facturas_electronicas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Detalles de facturas electrónicas';

-- Tabla de impuestos por detalle
CREATE TABLE IF NOT EXISTS facturas_electronicas_detalle_impuestos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    detalle_id INT NOT NULL,
    
    codigo CHAR(1) NOT NULL COMMENT '2=IVA, 3=ICE, 5=IRBPNR',
    codigo_porcentaje CHAR(4) NOT NULL COMMENT 'Código tarifa: 0, 2, 3, 4, 6, 7, 8',
    tarifa DECIMAL(5,2) NOT NULL COMMENT 'Porcentaje: 0, 12, 14, 15, etc.',
    base_imponible DECIMAL(14,2) NOT NULL,
    valor DECIMAL(14,2) NOT NULL,
    
    INDEX idx_detalle (detalle_id),
    FOREIGN KEY (detalle_id) REFERENCES facturas_electronicas_detalle(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Impuestos por detalle de factura electrónica';

-- Tabla de formas de pago
CREATE TABLE IF NOT EXISTS facturas_electronicas_pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factura_electronica_id INT NOT NULL,
    
    forma_pago CHAR(2) NOT NULL COMMENT '01=Efectivo, 16=Tarjeta Débito, etc.',
    total DECIMAL(14,2) NOT NULL,
    plazo INT DEFAULT NULL COMMENT 'Plazo en días/meses',
    unidad_tiempo VARCHAR(20) DEFAULT 'dias',
    
    INDEX idx_factura (factura_electronica_id),
    FOREIGN KEY (factura_electronica_id) REFERENCES facturas_electronicas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Formas de pago de facturas electrónicas';

-- Tabla de información adicional
CREATE TABLE IF NOT EXISTS facturas_electronicas_info_adicional (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factura_electronica_id INT NOT NULL,
    
    nombre VARCHAR(300) NOT NULL,
    valor VARCHAR(300) NOT NULL,
    
    INDEX idx_factura (factura_electronica_id),
    FOREIGN KEY (factura_electronica_id) REFERENCES facturas_electronicas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Información adicional de facturas electrónicas';

-- Tabla de log de comunicaciones con SRI
CREATE TABLE IF NOT EXISTS facturas_electronicas_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factura_electronica_id INT DEFAULT NULL,
    clave_acceso VARCHAR(49) DEFAULT NULL,
    
    accion ENUM('GENERAR', 'FIRMAR', 'ENVIAR', 'CONSULTAR', 'REENVIAR', 'ANULAR') NOT NULL,
    endpoint VARCHAR(500) DEFAULT NULL,
    request_data LONGTEXT DEFAULT NULL,
    response_data LONGTEXT DEFAULT NULL,
    estado_respuesta VARCHAR(50) DEFAULT NULL,
    codigo_error VARCHAR(10) DEFAULT NULL,
    mensaje_error TEXT DEFAULT NULL,
    duracion_ms INT DEFAULT NULL COMMENT 'Tiempo de respuesta en milisegundos',
    ip_origen VARCHAR(45) DEFAULT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT DEFAULT NULL COMMENT 'Referencia a usuarios.usuario_id',
    
    INDEX idx_factura (factura_electronica_id),
    INDEX idx_clave_acceso (clave_acceso),
    INDEX idx_accion (accion),
    INDEX idx_fecha (created_at),
    FOREIGN KEY (factura_electronica_id) REFERENCES facturas_electronicas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Log de comunicaciones con SRI';

-- Tabla de secuenciales por establecimiento/punto de emisión
CREATE TABLE IF NOT EXISTS facturas_electronicas_secuenciales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    
    tipo_comprobante CHAR(2) NOT NULL DEFAULT '01',
    establecimiento CHAR(3) NOT NULL,
    punto_emision CHAR(3) NOT NULL,
    secuencial_actual INT NOT NULL DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_secuencial (tenant_id, tipo_comprobante, establecimiento, punto_emision),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Control de secuenciales por establecimiento';

-- Insertar secuencial inicial
INSERT INTO facturas_electronicas_secuenciales (tenant_id, tipo_comprobante, establecimiento, punto_emision, secuencial_actual)
VALUES (1, '01', '001', '001', 0)
ON DUPLICATE KEY UPDATE secuencial_actual = secuencial_actual;

-- Vista para resumen de facturas electrónicas
CREATE OR REPLACE VIEW v_facturas_electronicas_resumen AS
SELECT 
    fe.id,
    fe.tenant_id,
    fe.clave_acceso,
    CONCAT(fe.establecimiento, '-', fe.punto_emision, '-', fe.secuencial) AS numero_factura,
    fe.fecha_emision,
    fe.cliente_identificacion,
    fe.cliente_razon_social,
    fe.subtotal,
    fe.iva,
    fe.total,
    fe.estado_sri,
    CASE fe.estado_sri
        WHEN 'AUTORIZADO' THEN 'success'
        WHEN 'PENDIENTE' THEN 'warning'
        WHEN 'GENERADA' THEN 'info'
        WHEN 'FIRMADA' THEN 'info'
        WHEN 'ENVIADA' THEN 'primary'
        WHEN 'RECIBIDA' THEN 'primary'
        WHEN 'DEVUELTA' THEN 'danger'
        WHEN 'NO_AUTORIZADO' THEN 'danger'
        WHEN 'ERROR' THEN 'danger'
        WHEN 'ANULADA' THEN 'secondary'
        ELSE 'secondary'
    END AS badge_class,
    fe.numero_autorizacion,
    fe.fecha_autorizacion,
    fe.ambiente,
    CASE fe.ambiente
        WHEN '1' THEN 'PRUEBAS'
        WHEN '2' THEN 'PRODUCCIÓN'
    END AS ambiente_texto,
    fe.intentos_envio,
    fe.mensaje_error,
    fe.created_at
FROM facturas_electronicas fe;

-- Índices adicionales para mejorar rendimiento de reportes
CREATE INDEX idx_fe_tenant_fecha ON facturas_electronicas(tenant_id, fecha_emision);
CREATE INDEX idx_fe_tenant_estado_fecha ON facturas_electronicas(tenant_id, estado_sri, fecha_emision);

-- =====================================================
-- FIN DE MIGRACIÓN
-- =====================================================
