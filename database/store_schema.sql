-- =====================================================================
-- DigiSports Store — Esquema completo de Base de Datos
-- Tienda deportiva con POS, inventario, caja y fidelización
-- MySQL 8+ | Multi-tenant | Convención: store_ + prefijo columnas
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ─────────────────────────────────────────────────────
-- 1. CATÁLOGO
-- ─────────────────────────────────────────────────────

-- Categorías jerárquicas
CREATE TABLE IF NOT EXISTS store_categorias (
    cat_categoria_id    INT AUTO_INCREMENT PRIMARY KEY,
    cat_tenant_id       INT NOT NULL,
    cat_padre_id        INT NULL COMMENT 'Subcategoría de...',
    cat_nombre          VARCHAR(100) NOT NULL,
    cat_slug            VARCHAR(120) NULL,
    cat_descripcion     TEXT NULL,
    cat_icono           VARCHAR(50) NULL DEFAULT 'fas fa-folder',
    cat_imagen          VARCHAR(255) NULL,
    cat_orden           INT DEFAULT 0,
    cat_activo          TINYINT(1) DEFAULT 1,
    cat_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cat_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_cat_tenant (cat_tenant_id),
    INDEX idx_cat_padre (cat_padre_id),
    INDEX idx_cat_slug (cat_tenant_id, cat_slug),
    CONSTRAINT fk_cat_tenant FOREIGN KEY (cat_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_cat_padre FOREIGN KEY (cat_padre_id) REFERENCES store_categorias(cat_categoria_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Marcas
CREATE TABLE IF NOT EXISTS store_marcas (
    mar_marca_id        INT AUTO_INCREMENT PRIMARY KEY,
    mar_tenant_id       INT NOT NULL,
    mar_nombre          VARCHAR(100) NOT NULL,
    mar_slug            VARCHAR(120) NULL,
    mar_logo            VARCHAR(255) NULL,
    mar_descripcion     TEXT NULL,
    mar_activo          TINYINT(1) DEFAULT 1,
    mar_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    mar_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_mar_tenant (mar_tenant_id),
    INDEX idx_mar_slug (mar_tenant_id, mar_slug),
    CONSTRAINT fk_mar_tenant FOREIGN KEY (mar_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Productos
CREATE TABLE IF NOT EXISTS store_productos (
    pro_producto_id     INT AUTO_INCREMENT PRIMARY KEY,
    pro_tenant_id       INT NOT NULL,
    pro_categoria_id    INT NULL,
    pro_marca_id        INT NULL,
    pro_codigo          VARCHAR(50) NULL COMMENT 'Código interno',
    pro_codigo_barras   VARCHAR(50) NULL COMMENT 'EAN-13 / UPC',
    pro_sku             VARCHAR(50) NULL,
    pro_nombre          VARCHAR(200) NOT NULL,
    pro_slug            VARCHAR(220) NULL,
    pro_descripcion     TEXT NULL,
    pro_descripcion_corta VARCHAR(500) NULL,
    pro_precio_compra   DECIMAL(12,4) DEFAULT 0.0000 COMMENT 'Costo de adquisición',
    pro_precio_venta    DECIMAL(12,4) NOT NULL COMMENT 'PVP sin impuesto',
    pro_precio_mayoreo  DECIMAL(12,4) NULL COMMENT 'Precio para mayoristas',
    pro_impuesto_id     INT NULL COMMENT 'Tipo de impuesto aplicable',
    pro_tipo            ENUM('SIMPLE','VARIABLE','SERVICIO','KIT') DEFAULT 'SIMPLE',
    pro_unidad_medida   VARCHAR(20) DEFAULT 'UNIDAD' COMMENT 'UNIDAD, KG, LITRO, METRO, PAR',
    pro_peso_kg         DECIMAL(8,3) NULL,
    pro_largo_cm        DECIMAL(8,2) NULL,
    pro_ancho_cm        DECIMAL(8,2) NULL,
    pro_alto_cm         DECIMAL(8,2) NULL,
    pro_stock_minimo    INT DEFAULT 5 COMMENT 'Alerta cuando baje de este nivel',
    pro_stock_maximo    INT NULL,
    pro_permite_venta_sin_stock TINYINT(1) DEFAULT 0,
    pro_destacado       TINYINT(1) DEFAULT 0,
    pro_visible_pos     TINYINT(1) DEFAULT 1 COMMENT 'Visible en punto de venta',
    pro_visible_web     TINYINT(1) DEFAULT 0 COMMENT 'Visible en tienda online',
    pro_notas_internas  TEXT NULL,
    pro_imagen_principal VARCHAR(255) NULL,
    pro_tags            VARCHAR(500) NULL COMMENT 'Etiquetas separadas por coma',
    pro_estado          ENUM('ACTIVO','INACTIVO','DESCONTINUADO') DEFAULT 'ACTIVO',
    pro_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pro_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_pro_tenant (pro_tenant_id),
    INDEX idx_pro_categoria (pro_tenant_id, pro_categoria_id),
    INDEX idx_pro_marca (pro_tenant_id, pro_marca_id),
    INDEX idx_pro_codigo (pro_tenant_id, pro_codigo),
    INDEX idx_pro_barras (pro_tenant_id, pro_codigo_barras),
    INDEX idx_pro_sku (pro_tenant_id, pro_sku),
    INDEX idx_pro_nombre (pro_tenant_id, pro_nombre),
    INDEX idx_pro_estado (pro_tenant_id, pro_estado),
    FULLTEXT idx_pro_busqueda (pro_nombre, pro_descripcion_corta, pro_tags),

    CONSTRAINT fk_pro_tenant FOREIGN KEY (pro_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_pro_categoria FOREIGN KEY (pro_categoria_id) REFERENCES store_categorias(cat_categoria_id) ON DELETE SET NULL,
    CONSTRAINT fk_pro_marca FOREIGN KEY (pro_marca_id) REFERENCES store_marcas(mar_marca_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Variantes de producto (talla, color, material)
CREATE TABLE IF NOT EXISTS store_producto_variantes (
    var_variante_id     INT AUTO_INCREMENT PRIMARY KEY,
    var_tenant_id       INT NOT NULL,
    var_producto_id     INT NOT NULL,
    var_sku             VARCHAR(50) NULL,
    var_codigo_barras   VARCHAR(50) NULL,
    var_talla           VARCHAR(20) NULL COMMENT 'XS, S, M, L, XL, 36, 37...',
    var_color           VARCHAR(30) NULL,
    var_material        VARCHAR(50) NULL,
    var_otro_atributo   VARCHAR(100) NULL COMMENT 'Atributo adicional libre',
    var_precio_adicional DECIMAL(12,4) DEFAULT 0.0000 COMMENT 'Suma al precio base',
    var_precio_override DECIMAL(12,4) NULL COMMENT 'Si se define, reemplaza precio base',
    var_costo_override  DECIMAL(12,4) NULL,
    var_imagen          VARCHAR(255) NULL,
    var_peso_kg         DECIMAL(8,3) NULL,
    var_activo          TINYINT(1) DEFAULT 1,
    var_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_var_tenant (var_tenant_id),
    INDEX idx_var_producto (var_producto_id),
    INDEX idx_var_sku (var_tenant_id, var_sku),
    INDEX idx_var_barras (var_tenant_id, var_codigo_barras),

    CONSTRAINT fk_var_tenant FOREIGN KEY (var_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_var_producto FOREIGN KEY (var_producto_id) REFERENCES store_productos(pro_producto_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Imágenes de producto
CREATE TABLE IF NOT EXISTS store_producto_imagenes (
    img_imagen_id       INT AUTO_INCREMENT PRIMARY KEY,
    img_tenant_id       INT NOT NULL,
    img_producto_id     INT NOT NULL,
    img_variante_id     INT NULL,
    img_url             VARCHAR(500) NOT NULL,
    img_alt             VARCHAR(200) NULL,
    img_orden           INT DEFAULT 0,
    img_es_principal    TINYINT(1) DEFAULT 0,
    img_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_img_producto (img_producto_id),
    CONSTRAINT fk_img_tenant FOREIGN KEY (img_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_img_producto FOREIGN KEY (img_producto_id) REFERENCES store_productos(pro_producto_id) ON DELETE CASCADE,
    CONSTRAINT fk_img_variante FOREIGN KEY (img_variante_id) REFERENCES store_producto_variantes(var_variante_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────
-- 2. INVENTARIO / STOCK
-- ─────────────────────────────────────────────────────

-- Proveedores
CREATE TABLE IF NOT EXISTS store_proveedores (
    prv_proveedor_id    INT AUTO_INCREMENT PRIMARY KEY,
    prv_tenant_id       INT NOT NULL,
    prv_ruc_ci          VARCHAR(20) NULL,
    prv_razon_social    VARCHAR(200) NOT NULL,
    prv_nombre_comercial VARCHAR(200) NULL,
    prv_contacto_nombre VARCHAR(150) NULL,
    prv_email           VARCHAR(200) NULL,
    prv_telefono        VARCHAR(20) NULL,
    prv_celular         VARCHAR(20) NULL,
    prv_direccion       VARCHAR(400) NULL,
    prv_ciudad          VARCHAR(100) NULL,
    prv_notas           TEXT NULL,
    prv_dias_credito    INT DEFAULT 0,
    prv_activo          TINYINT(1) DEFAULT 1,
    prv_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    prv_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_prv_tenant (prv_tenant_id),
    INDEX idx_prv_ruc (prv_tenant_id, prv_ruc_ci),
    CONSTRAINT fk_prv_tenant FOREIGN KEY (prv_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock actual por producto/variante
CREATE TABLE IF NOT EXISTS store_stock (
    stk_stock_id        INT AUTO_INCREMENT PRIMARY KEY,
    stk_tenant_id       INT NOT NULL,
    stk_producto_id     INT NOT NULL,
    stk_variante_id     INT NULL COMMENT 'NULL = producto simple sin variantes',
    stk_cantidad        INT DEFAULT 0 COMMENT 'Stock total',
    stk_reservado       INT DEFAULT 0 COMMENT 'Apartados/reservados',
    stk_disponible      INT GENERATED ALWAYS AS (stk_cantidad - stk_reservado) STORED,
    stk_ubicacion       VARCHAR(50) NULL COMMENT 'Pasillo, estante, etc.',
    stk_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_stk_producto_variante (stk_tenant_id, stk_producto_id, stk_variante_id),
    INDEX idx_stk_disponible (stk_tenant_id, stk_disponible),

    CONSTRAINT fk_stk_tenant FOREIGN KEY (stk_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_stk_producto FOREIGN KEY (stk_producto_id) REFERENCES store_productos(pro_producto_id) ON DELETE CASCADE,
    CONSTRAINT fk_stk_variante FOREIGN KEY (stk_variante_id) REFERENCES store_producto_variantes(var_variante_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Movimientos de stock (trazabilidad completa)
CREATE TABLE IF NOT EXISTS store_stock_movimientos (
    mov_movimiento_id   INT AUTO_INCREMENT PRIMARY KEY,
    mov_tenant_id       INT NOT NULL,
    mov_producto_id     INT NOT NULL,
    mov_variante_id     INT NULL,
    mov_tipo            ENUM('ENTRADA','SALIDA','AJUSTE','TRANSFERENCIA','DEVOLUCION','VENTA','COMPRA') NOT NULL,
    mov_cantidad        INT NOT NULL COMMENT 'Positivo=entrada, negativo=salida',
    mov_stock_anterior  INT NOT NULL,
    mov_stock_posterior INT NOT NULL,
    mov_costo_unitario  DECIMAL(12,4) NULL,
    mov_referencia_tipo VARCHAR(30) NULL COMMENT 'VENTA, ORDEN_COMPRA, AJUSTE_MANUAL, DEVOLUCION',
    mov_referencia_id   INT NULL COMMENT 'ID de la venta, orden, etc.',
    mov_motivo          VARCHAR(255) NULL,
    mov_notas           TEXT NULL,
    mov_usuario_id      INT NULL,
    mov_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_mov_tenant (mov_tenant_id),
    INDEX idx_mov_producto (mov_tenant_id, mov_producto_id),
    INDEX idx_mov_tipo (mov_tenant_id, mov_tipo),
    INDEX idx_mov_fecha (mov_tenant_id, mov_fecha_registro),
    INDEX idx_mov_referencia (mov_referencia_tipo, mov_referencia_id),

    CONSTRAINT fk_mov_tenant FOREIGN KEY (mov_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_mov_producto FOREIGN KEY (mov_producto_id) REFERENCES store_productos(pro_producto_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alertas de stock bajo
CREATE TABLE IF NOT EXISTS store_stock_alertas (
    ale_alerta_id       INT AUTO_INCREMENT PRIMARY KEY,
    ale_tenant_id       INT NOT NULL,
    ale_producto_id     INT NOT NULL,
    ale_variante_id     INT NULL,
    ale_stock_actual    INT NOT NULL,
    ale_stock_minimo    INT NOT NULL,
    ale_estado          ENUM('PENDIENTE','NOTIFICADA','RESUELTA','IGNORADA') DEFAULT 'PENDIENTE',
    ale_fecha_generada  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ale_fecha_resuelta  DATETIME NULL,

    INDEX idx_ale_tenant_estado (ale_tenant_id, ale_estado),
    INDEX idx_ale_producto (ale_producto_id),

    CONSTRAINT fk_ale_tenant FOREIGN KEY (ale_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_ale_producto FOREIGN KEY (ale_producto_id) REFERENCES store_productos(pro_producto_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Órdenes de compra a proveedores
CREATE TABLE IF NOT EXISTS store_ordenes_compra (
    orc_orden_id        INT AUTO_INCREMENT PRIMARY KEY,
    orc_tenant_id       INT NOT NULL,
    orc_proveedor_id    INT NOT NULL,
    orc_numero          VARCHAR(30) NOT NULL COMMENT 'Número secuencial OC-0001',
    orc_fecha_orden     DATE NOT NULL,
    orc_fecha_entrega_esperada DATE NULL,
    orc_fecha_recibido  DATE NULL,
    orc_subtotal        DECIMAL(12,2) DEFAULT 0.00,
    orc_descuento       DECIMAL(12,2) DEFAULT 0.00,
    orc_impuesto        DECIMAL(12,2) DEFAULT 0.00,
    orc_total           DECIMAL(12,2) DEFAULT 0.00,
    orc_notas           TEXT NULL,
    orc_estado          ENUM('BORRADOR','ENVIADA','PARCIAL','RECIBIDA','ANULADA') DEFAULT 'BORRADOR',
    orc_usuario_id      INT NULL,
    orc_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    orc_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_orc_tenant (orc_tenant_id),
    INDEX idx_orc_proveedor (orc_tenant_id, orc_proveedor_id),
    INDEX idx_orc_estado (orc_tenant_id, orc_estado),
    INDEX idx_orc_fecha (orc_tenant_id, orc_fecha_orden),

    CONSTRAINT fk_orc_tenant FOREIGN KEY (orc_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_orc_proveedor FOREIGN KEY (orc_proveedor_id) REFERENCES store_proveedores(prv_proveedor_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detalle de orden de compra
CREATE TABLE IF NOT EXISTS store_ordenes_compra_detalle (
    ocd_detalle_id      INT AUTO_INCREMENT PRIMARY KEY,
    ocd_tenant_id       INT NOT NULL,
    ocd_orden_id        INT NOT NULL,
    ocd_producto_id     INT NOT NULL,
    ocd_variante_id     INT NULL,
    ocd_cantidad_pedida INT NOT NULL,
    ocd_cantidad_recibida INT DEFAULT 0,
    ocd_costo_unitario  DECIMAL(12,4) NOT NULL,
    ocd_subtotal        DECIMAL(12,2) GENERATED ALWAYS AS (ocd_cantidad_pedida * ocd_costo_unitario) STORED,
    ocd_notas           VARCHAR(255) NULL,

    INDEX idx_ocd_orden (ocd_orden_id),

    CONSTRAINT fk_ocd_tenant FOREIGN KEY (ocd_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_ocd_orden FOREIGN KEY (ocd_orden_id) REFERENCES store_ordenes_compra(orc_orden_id) ON DELETE CASCADE,
    CONSTRAINT fk_ocd_producto FOREIGN KEY (ocd_producto_id) REFERENCES store_productos(pro_producto_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────
-- 3. IMPUESTOS Y CONFIGURACIÓN
-- ─────────────────────────────────────────────────────

-- Impuestos configurables (IVA Ecuador 15%)
CREATE TABLE IF NOT EXISTS store_impuestos (
    imp_impuesto_id     INT AUTO_INCREMENT PRIMARY KEY,
    imp_tenant_id       INT NOT NULL,
    imp_codigo_sri      VARCHAR(10) NULL COMMENT 'Código SRI: 2=IVA, 3=ICE',
    imp_nombre          VARCHAR(100) NOT NULL,
    imp_porcentaje      DECIMAL(5,2) NOT NULL,
    imp_tipo            ENUM('IVA','ICE','IRBPNR','OTRO') DEFAULT 'IVA',
    imp_aplica_a        ENUM('TODOS','BIENES','SERVICIOS') DEFAULT 'TODOS',
    imp_es_default      TINYINT(1) DEFAULT 0,
    imp_activo          TINYINT(1) DEFAULT 1,
    imp_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_imp_tenant (imp_tenant_id),
    CONSTRAINT fk_imp_tenant FOREIGN KEY (imp_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configuración del módulo Store (key-value por tenant)
CREATE TABLE IF NOT EXISTS store_configuracion (
    cfg_config_id       INT AUTO_INCREMENT PRIMARY KEY,
    cfg_tenant_id       INT NOT NULL,
    cfg_clave           VARCHAR(100) NOT NULL,
    cfg_valor           TEXT NULL,
    cfg_tipo            ENUM('STRING','INT','DECIMAL','BOOL','JSON') DEFAULT 'STRING',
    cfg_grupo           VARCHAR(50) DEFAULT 'general',
    cfg_descripcion     VARCHAR(255) NULL,
    cfg_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_cfg_tenant_clave (cfg_tenant_id, cfg_clave),
    INDEX idx_cfg_grupo (cfg_tenant_id, cfg_grupo),

    CONSTRAINT fk_cfg_tenant FOREIGN KEY (cfg_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────
-- 4. DESCUENTOS Y PROMOCIONES
-- ─────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS store_descuentos (
    dsc_descuento_id    INT AUTO_INCREMENT PRIMARY KEY,
    dsc_tenant_id       INT NOT NULL,
    dsc_nombre          VARCHAR(100) NOT NULL,
    dsc_codigo          VARCHAR(30) NULL COMMENT 'Código cupón (opcional)',
    dsc_tipo            ENUM('PORCENTAJE','MONTO_FIJO','COMPRA_X_LLEVA_Y') NOT NULL,
    dsc_valor           DECIMAL(12,4) NOT NULL COMMENT '% o monto fijo',
    dsc_minimo_compra   DECIMAL(12,2) NULL COMMENT 'Compra mínima para aplicar',
    dsc_maximo_descuento DECIMAL(12,2) NULL COMMENT 'Tope máximo del descuento',
    dsc_aplica_a        ENUM('TODOS','CATEGORIA','PRODUCTO','MARCA') DEFAULT 'TODOS',
    dsc_aplica_id       INT NULL COMMENT 'ID de categoría, producto o marca',
    dsc_fecha_inicio    DATE NULL,
    dsc_fecha_fin       DATE NULL,
    dsc_usos_maximos    INT NULL COMMENT 'NULL = ilimitado',
    dsc_usos_actuales   INT DEFAULT 0,
    dsc_activo          TINYINT(1) DEFAULT 1,
    dsc_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_dsc_tenant (dsc_tenant_id),
    INDEX idx_dsc_codigo (dsc_tenant_id, dsc_codigo),
    INDEX idx_dsc_fechas (dsc_tenant_id, dsc_fecha_inicio, dsc_fecha_fin),

    CONSTRAINT fk_dsc_tenant FOREIGN KEY (dsc_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────
-- 5. GESTIÓN DE CAJA
-- ─────────────────────────────────────────────────────

-- Cajas registradoras / terminales
CREATE TABLE IF NOT EXISTS store_cajas (
    caj_caja_id         INT AUTO_INCREMENT PRIMARY KEY,
    caj_tenant_id       INT NOT NULL,
    caj_nombre          VARCHAR(50) NOT NULL COMMENT 'Caja 1, Caja Principal, etc.',
    caj_codigo          VARCHAR(20) NULL,
    caj_ubicacion       VARCHAR(100) NULL,
    caj_impresora       VARCHAR(100) NULL COMMENT 'Nombre de impresora POS',
    caj_activa          TINYINT(1) DEFAULT 1,
    caj_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_caj_tenant (caj_tenant_id),
    CONSTRAINT fk_caj_tenant FOREIGN KEY (caj_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Turnos de caja (apertura/cierre)
CREATE TABLE IF NOT EXISTS store_caja_turnos (
    tur_turno_id        INT AUTO_INCREMENT PRIMARY KEY,
    tur_tenant_id       INT NOT NULL,
    tur_caja_id         INT NOT NULL,
    tur_usuario_id      INT NOT NULL COMMENT 'Cajero que abrió',
    tur_monto_apertura  DECIMAL(12,2) NOT NULL COMMENT 'Fondo inicial de caja',
    tur_fecha_apertura  DATETIME NOT NULL,
    tur_fecha_cierre    DATETIME NULL,
    tur_monto_cierre_esperado DECIMAL(12,2) NULL COMMENT 'Calculado por el sistema',
    tur_monto_cierre_real DECIMAL(12,2) NULL COMMENT 'Conteo físico del cajero',
    tur_diferencia      DECIMAL(12,2) NULL COMMENT 'Real - Esperado (sobrante/faltante)',
    tur_total_ventas    DECIMAL(12,2) DEFAULT 0.00,
    tur_total_efectivo  DECIMAL(12,2) DEFAULT 0.00,
    tur_total_tarjeta   DECIMAL(12,2) DEFAULT 0.00,
    tur_total_transferencia DECIMAL(12,2) DEFAULT 0.00,
    tur_total_otros     DECIMAL(12,2) DEFAULT 0.00,
    tur_num_ventas      INT DEFAULT 0,
    tur_num_devoluciones INT DEFAULT 0,
    tur_total_devoluciones DECIMAL(12,2) DEFAULT 0.00,
    tur_notas_apertura  TEXT NULL,
    tur_notas_cierre    TEXT NULL,
    tur_usuario_cierre  INT NULL COMMENT 'Supervisor que autorizó cierre',
    tur_estado          ENUM('ABIERTO','CERRADO','ANULADO') DEFAULT 'ABIERTO',
    tur_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_tur_tenant (tur_tenant_id),
    INDEX idx_tur_caja (tur_tenant_id, tur_caja_id),
    INDEX idx_tur_estado (tur_tenant_id, tur_estado),
    INDEX idx_tur_usuario (tur_tenant_id, tur_usuario_id),
    INDEX idx_tur_fecha (tur_tenant_id, tur_fecha_apertura),

    CONSTRAINT fk_tur_tenant FOREIGN KEY (tur_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_tur_caja FOREIGN KEY (tur_caja_id) REFERENCES store_cajas(caj_caja_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Movimientos manuales de caja (entradas/salidas de efectivo)
CREATE TABLE IF NOT EXISTS store_caja_movimientos (
    cmv_movimiento_id   INT AUTO_INCREMENT PRIMARY KEY,
    cmv_tenant_id       INT NOT NULL,
    cmv_turno_id        INT NOT NULL,
    cmv_tipo            ENUM('ENTRADA','SALIDA') NOT NULL,
    cmv_monto           DECIMAL(12,2) NOT NULL,
    cmv_motivo          VARCHAR(255) NOT NULL COMMENT 'Cambio, pago proveedor, retiro parcial...',
    cmv_autorizado_por  INT NULL COMMENT 'Supervisor que autorizó',
    cmv_usuario_id      INT NOT NULL,
    cmv_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_cmv_turno (cmv_turno_id),

    CONSTRAINT fk_cmv_tenant FOREIGN KEY (cmv_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_cmv_turno FOREIGN KEY (cmv_turno_id) REFERENCES store_caja_turnos(tur_turno_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Arqueo de caja (conteo de billetes/monedas al cierre)
CREATE TABLE IF NOT EXISTS store_caja_arqueo (
    arq_arqueo_id       INT AUTO_INCREMENT PRIMARY KEY,
    arq_tenant_id       INT NOT NULL,
    arq_turno_id        INT NOT NULL,
    arq_denominacion    VARCHAR(20) NOT NULL COMMENT '$100, $50, $20, $10, $5, $1, $0.50, $0.25, $0.10, $0.05, $0.01',
    arq_cantidad        INT NOT NULL DEFAULT 0,
    arq_subtotal        DECIMAL(12,2) GENERATED ALWAYS AS (
        arq_cantidad * CAST(REPLACE(arq_denominacion, '$', '') AS DECIMAL(12,2))
    ) STORED,
    arq_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_arq_turno (arq_turno_id),

    CONSTRAINT fk_arq_tenant FOREIGN KEY (arq_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_arq_turno FOREIGN KEY (arq_turno_id) REFERENCES store_caja_turnos(tur_turno_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────
-- 6. PUNTO DE VENTA — VENTAS
-- ─────────────────────────────────────────────────────

-- Ventas (ticket/factura)
CREATE TABLE IF NOT EXISTS store_ventas (
    ven_venta_id        INT AUTO_INCREMENT PRIMARY KEY,
    ven_tenant_id       INT NOT NULL,
    ven_turno_id        INT NULL COMMENT 'Turno de caja en que se realizó',
    ven_numero          VARCHAR(30) NOT NULL COMMENT 'Número secuencial V-000001',
    ven_cliente_id      INT NULL COMMENT 'FK a store_clientes (NULL = consumidor final)',
    ven_tipo_documento  ENUM('TICKET','FACTURA','NOTA_VENTA','PROFORMA') DEFAULT 'TICKET',
    ven_fecha           DATETIME NOT NULL,
    ven_subtotal_sin_impuesto DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Base imponible 0%',
    ven_subtotal_con_impuesto DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Base imponible IVA',
    ven_subtotal        DECIMAL(12,2) DEFAULT 0.00,
    ven_descuento       DECIMAL(12,2) DEFAULT 0.00,
    ven_impuesto        DECIMAL(12,2) DEFAULT 0.00 COMMENT 'IVA calculado',
    ven_total           DECIMAL(12,2) DEFAULT 0.00,
    ven_descuento_id    INT NULL COMMENT 'Descuento/cupón aplicado',
    ven_notas           TEXT NULL,
    ven_vendedor_id     INT NULL COMMENT 'Empleado que vendió (comisiones)',
    ven_factura_electronica_id INT NULL COMMENT 'FK si se generó factura SRI',
    ven_puntos_ganados  INT DEFAULT 0,
    ven_estado          ENUM('PENDIENTE','COMPLETADA','ANULADA','DEVUELTA') DEFAULT 'PENDIENTE',
    ven_usuario_id      INT NULL,
    ven_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ven_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_ven_tenant (ven_tenant_id),
    INDEX idx_ven_turno (ven_tenant_id, ven_turno_id),
    INDEX idx_ven_cliente (ven_tenant_id, ven_cliente_id),
    INDEX idx_ven_numero (ven_tenant_id, ven_numero),
    INDEX idx_ven_fecha (ven_tenant_id, ven_fecha),
    INDEX idx_ven_estado (ven_tenant_id, ven_estado),
    INDEX idx_ven_vendedor (ven_tenant_id, ven_vendedor_id),

    CONSTRAINT fk_ven_tenant FOREIGN KEY (ven_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_ven_turno FOREIGN KEY (ven_turno_id) REFERENCES store_caja_turnos(tur_turno_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ítems de la venta
CREATE TABLE IF NOT EXISTS store_venta_items (
    vit_item_id         INT AUTO_INCREMENT PRIMARY KEY,
    vit_tenant_id       INT NOT NULL,
    vit_venta_id        INT NOT NULL,
    vit_producto_id     INT NOT NULL,
    vit_variante_id     INT NULL,
    vit_descripcion     VARCHAR(250) NOT NULL COMMENT 'Snapshot del nombre al momento de venta',
    vit_cantidad        DECIMAL(10,3) NOT NULL DEFAULT 1,
    vit_precio_unitario DECIMAL(12,4) NOT NULL,
    vit_costo_unitario  DECIMAL(12,4) NULL COMMENT 'Costo al momento de venta (para utilidad)',
    vit_descuento_linea DECIMAL(12,2) DEFAULT 0.00,
    vit_porcentaje_impuesto DECIMAL(5,2) DEFAULT 15.00,
    vit_impuesto_linea  DECIMAL(12,2) DEFAULT 0.00,
    vit_subtotal        DECIMAL(12,2) GENERATED ALWAYS AS (
        (vit_cantidad * vit_precio_unitario) - vit_descuento_linea
    ) STORED,
    vit_total           DECIMAL(12,2) GENERATED ALWAYS AS (
        ((vit_cantidad * vit_precio_unitario) - vit_descuento_linea) + vit_impuesto_linea
    ) STORED,
    vit_notas           VARCHAR(255) NULL,

    INDEX idx_vit_venta (vit_venta_id),
    INDEX idx_vit_producto (vit_producto_id),

    CONSTRAINT fk_vit_tenant FOREIGN KEY (vit_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_vit_venta FOREIGN KEY (vit_venta_id) REFERENCES store_ventas(ven_venta_id) ON DELETE CASCADE,
    CONSTRAINT fk_vit_producto FOREIGN KEY (vit_producto_id) REFERENCES store_productos(pro_producto_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pagos de la venta (split payment: múltiples formas de pago por venta)
CREATE TABLE IF NOT EXISTS store_venta_pagos (
    vpg_pago_id         INT AUTO_INCREMENT PRIMARY KEY,
    vpg_tenant_id       INT NOT NULL,
    vpg_venta_id        INT NOT NULL,
    vpg_forma_pago      ENUM('EFECTIVO','TARJETA_DEBITO','TARJETA_CREDITO','TRANSFERENCIA','MONEDERO','CREDITO','CHEQUE','OTRO') NOT NULL,
    vpg_monto           DECIMAL(12,2) NOT NULL,
    vpg_referencia      VARCHAR(100) NULL COMMENT 'Num. transacción, num. cheque, etc.',
    vpg_cambio          DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Vuelto (solo para efectivo)',
    vpg_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_vpg_venta (vpg_venta_id),
    INDEX idx_vpg_forma (vpg_tenant_id, vpg_forma_pago),

    CONSTRAINT fk_vpg_tenant FOREIGN KEY (vpg_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_vpg_venta FOREIGN KEY (vpg_venta_id) REFERENCES store_ventas(ven_venta_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────
-- 7. COTIZACIONES / PROFORMAS
-- ─────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS store_cotizaciones (
    cot_cotizacion_id   INT AUTO_INCREMENT PRIMARY KEY,
    cot_tenant_id       INT NOT NULL,
    cot_numero          VARCHAR(30) NOT NULL,
    cot_cliente_id      INT NULL,
    cot_fecha           DATE NOT NULL,
    cot_vigencia_dias   INT DEFAULT 15,
    cot_subtotal        DECIMAL(12,2) DEFAULT 0.00,
    cot_descuento       DECIMAL(12,2) DEFAULT 0.00,
    cot_impuesto        DECIMAL(12,2) DEFAULT 0.00,
    cot_total           DECIMAL(12,2) DEFAULT 0.00,
    cot_notas           TEXT NULL,
    cot_estado          ENUM('BORRADOR','ENVIADA','ACEPTADA','RECHAZADA','VENCIDA','CONVERTIDA') DEFAULT 'BORRADOR',
    cot_venta_id        INT NULL COMMENT 'Si fue convertida a venta',
    cot_usuario_id      INT NULL,
    cot_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cot_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_cot_tenant (cot_tenant_id),
    INDEX idx_cot_cliente (cot_tenant_id, cot_cliente_id),
    INDEX idx_cot_estado (cot_tenant_id, cot_estado),

    CONSTRAINT fk_cot_tenant FOREIGN KEY (cot_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_cotizacion_items (
    coi_item_id         INT AUTO_INCREMENT PRIMARY KEY,
    coi_tenant_id       INT NOT NULL,
    coi_cotizacion_id   INT NOT NULL,
    coi_producto_id     INT NOT NULL,
    coi_variante_id     INT NULL,
    coi_descripcion     VARCHAR(250) NOT NULL,
    coi_cantidad        DECIMAL(10,3) NOT NULL DEFAULT 1,
    coi_precio_unitario DECIMAL(12,4) NOT NULL,
    coi_descuento_linea DECIMAL(12,2) DEFAULT 0.00,
    coi_impuesto_linea  DECIMAL(12,2) DEFAULT 0.00,
    coi_subtotal        DECIMAL(12,2) DEFAULT 0.00,

    INDEX idx_coi_cotizacion (coi_cotizacion_id),

    CONSTRAINT fk_coi_tenant FOREIGN KEY (coi_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_coi_cotizacion FOREIGN KEY (coi_cotizacion_id) REFERENCES store_cotizaciones(cot_cotizacion_id) ON DELETE CASCADE,
    CONSTRAINT fk_coi_producto FOREIGN KEY (coi_producto_id) REFERENCES store_productos(pro_producto_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────
-- 8. DEVOLUCIONES
-- ─────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS store_devoluciones (
    dev_devolucion_id   INT AUTO_INCREMENT PRIMARY KEY,
    dev_tenant_id       INT NOT NULL,
    dev_venta_id        INT NOT NULL,
    dev_turno_id        INT NULL,
    dev_numero          VARCHAR(30) NOT NULL COMMENT 'DEV-000001',
    dev_fecha           DATETIME NOT NULL,
    dev_motivo          VARCHAR(255) NOT NULL,
    dev_subtotal        DECIMAL(12,2) DEFAULT 0.00,
    dev_impuesto        DECIMAL(12,2) DEFAULT 0.00,
    dev_total           DECIMAL(12,2) DEFAULT 0.00,
    dev_tipo_reembolso  ENUM('EFECTIVO','CREDITO_TIENDA','MONEDERO','TARJETA','OTRO') DEFAULT 'EFECTIVO',
    dev_estado          ENUM('PENDIENTE','APROBADA','RECHAZADA','COMPLETADA') DEFAULT 'PENDIENTE',
    dev_usuario_id      INT NULL,
    dev_aprobado_por    INT NULL COMMENT 'Supervisor que aprobó',
    dev_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_dev_tenant (dev_tenant_id),
    INDEX idx_dev_venta (dev_venta_id),
    INDEX idx_dev_estado (dev_tenant_id, dev_estado),
    INDEX idx_dev_fecha (dev_tenant_id, dev_fecha),

    CONSTRAINT fk_dev_tenant FOREIGN KEY (dev_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_dev_venta FOREIGN KEY (dev_venta_id) REFERENCES store_ventas(ven_venta_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_devolucion_items (
    dvi_item_id         INT AUTO_INCREMENT PRIMARY KEY,
    dvi_tenant_id       INT NOT NULL,
    dvi_devolucion_id   INT NOT NULL,
    dvi_venta_item_id   INT NOT NULL COMMENT 'Referencia al ítem original vendido',
    dvi_producto_id     INT NOT NULL,
    dvi_variante_id     INT NULL,
    dvi_cantidad        DECIMAL(10,3) NOT NULL,
    dvi_precio_unitario DECIMAL(12,4) NOT NULL,
    dvi_subtotal        DECIMAL(12,2) DEFAULT 0.00,
    dvi_motivo_detalle  VARCHAR(255) NULL,
    dvi_devolver_stock  TINYINT(1) DEFAULT 1 COMMENT '1=reintegrar al inventario',

    INDEX idx_dvi_devolucion (dvi_devolucion_id),

    CONSTRAINT fk_dvi_tenant FOREIGN KEY (dvi_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_dvi_devolucion FOREIGN KEY (dvi_devolucion_id) REFERENCES store_devoluciones(dev_devolucion_id) ON DELETE CASCADE,
    CONSTRAINT fk_dvi_venta_item FOREIGN KEY (dvi_venta_item_id) REFERENCES store_venta_items(vit_item_id) ON DELETE RESTRICT,
    CONSTRAINT fk_dvi_producto FOREIGN KEY (dvi_producto_id) REFERENCES store_productos(pro_producto_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────
-- 9. CLIENTES STORE + FIDELIZACIÓN
-- ─────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS store_clientes (
    cli_cliente_id      INT AUTO_INCREMENT PRIMARY KEY,
    cli_tenant_id       INT NOT NULL,
    cli_tipo_id         ENUM('CEDULA','RUC','PASAPORTE') DEFAULT 'CEDULA',
    cli_identificacion  VARCHAR(20) NULL,
    cli_nombres         VARCHAR(150) NOT NULL,
    cli_apellidos       VARCHAR(150) NULL,
    cli_email           VARCHAR(200) NULL,
    cli_telefono        VARCHAR(20) NULL,
    cli_celular         VARCHAR(20) NULL,
    cli_direccion       VARCHAR(400) NULL,
    cli_ciudad          VARCHAR(100) NULL,
    cli_fecha_nacimiento DATE NULL,
    cli_genero          ENUM('M','F','OTRO') NULL,
    cli_notas           TEXT NULL,
    cli_puntos_acumulados INT DEFAULT 0 COMMENT 'Total histórico de puntos ganados',
    cli_puntos_canjeados INT DEFAULT 0,
    cli_puntos_disponibles INT GENERATED ALWAYS AS (cli_puntos_acumulados - cli_puntos_canjeados) STORED,
    cli_total_compras   DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Lifetime value',
    cli_num_compras     INT DEFAULT 0,
    cli_ultima_compra   DATE NULL,
    cli_categoria       ENUM('NUEVO','REGULAR','FRECUENTE','VIP') DEFAULT 'NUEVO',
    cli_acepta_marketing TINYINT(1) DEFAULT 0,
    cli_activo          TINYINT(1) DEFAULT 1,
    cli_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cli_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_cli_tenant (cli_tenant_id),
    INDEX idx_cli_identificacion (cli_tenant_id, cli_identificacion),
    INDEX idx_cli_nombre (cli_tenant_id, cli_nombres, cli_apellidos),
    INDEX idx_cli_email (cli_tenant_id, cli_email),
    INDEX idx_cli_categoria (cli_tenant_id, cli_categoria),

    CONSTRAINT fk_scli_tenant FOREIGN KEY (cli_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historial de puntos de fidelización
CREATE TABLE IF NOT EXISTS store_cliente_puntos_log (
    cpl_log_id          INT AUTO_INCREMENT PRIMARY KEY,
    cpl_tenant_id       INT NOT NULL,
    cpl_cliente_id      INT NOT NULL,
    cpl_tipo            ENUM('GANADO','CANJEADO','AJUSTE','VENCIDO') NOT NULL,
    cpl_puntos          INT NOT NULL,
    cpl_referencia_tipo VARCHAR(30) NULL COMMENT 'VENTA, CANJE, PROMOCION, AJUSTE',
    cpl_referencia_id   INT NULL,
    cpl_descripcion     VARCHAR(255) NULL,
    cpl_usuario_id      INT NULL,
    cpl_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_cpl_cliente (cpl_cliente_id),
    INDEX idx_cpl_tipo (cpl_tenant_id, cpl_tipo),

    CONSTRAINT fk_cpl_tenant FOREIGN KEY (cpl_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_cpl_cliente FOREIGN KEY (cpl_cliente_id) REFERENCES store_clientes(cli_cliente_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────
-- 10. DATOS SEMILLA
-- ─────────────────────────────────────────────────────

-- Impuestos Ecuador SRI
INSERT INTO store_impuestos (imp_tenant_id, imp_codigo_sri, imp_nombre, imp_porcentaje, imp_tipo, imp_aplica_a, imp_es_default) VALUES
(1, '2', 'IVA 15%', 15.00, 'IVA', 'TODOS', 1),
(1, '0', 'IVA 0%', 0.00, 'IVA', 'TODOS', 0),
(1, '6', 'No Objeto de Impuesto', 0.00, 'IVA', 'TODOS', 0);

-- Configuración por defecto
INSERT INTO store_configuracion (cfg_tenant_id, cfg_clave, cfg_valor, cfg_tipo, cfg_grupo, cfg_descripcion) VALUES
(1, 'moneda', 'USD', 'STRING', 'general', 'Moneda del sistema'),
(1, 'simbolo_moneda', '$', 'STRING', 'general', 'Símbolo de moneda'),
(1, 'decimales_precio', '2', 'INT', 'general', 'Decimales en precios'),
(1, 'iva_porcentaje', '15', 'DECIMAL', 'impuestos', 'Porcentaje IVA vigente'),
(1, 'precio_incluye_iva', '1', 'BOOL', 'impuestos', 'Los precios incluyen IVA'),
(1, 'nombre_tienda', 'DigiSports Store', 'STRING', 'tienda', 'Nombre de la tienda'),
(1, 'direccion_tienda', '', 'STRING', 'tienda', 'Dirección de la tienda'),
(1, 'telefono_tienda', '', 'STRING', 'tienda', 'Teléfono de la tienda'),
(1, 'ruc_tienda', '', 'STRING', 'tienda', 'RUC de la tienda'),
(1, 'prefijo_venta', 'V-', 'STRING', 'pos', 'Prefijo para número de venta'),
(1, 'prefijo_cotizacion', 'COT-', 'STRING', 'pos', 'Prefijo para cotizaciones'),
(1, 'prefijo_devolucion', 'DEV-', 'STRING', 'pos', 'Prefijo para devoluciones'),
(1, 'prefijo_orden_compra', 'OC-', 'STRING', 'pos', 'Prefijo para órdenes de compra'),
(1, 'monto_apertura_default', '100.00', 'DECIMAL', 'caja', 'Monto sugerido para apertura de caja'),
(1, 'requiere_cierre_caja', '1', 'BOOL', 'caja', 'Requiere cierre de caja obligatorio'),
(1, 'requiere_arqueo', '1', 'BOOL', 'caja', 'Requiere arqueo al cerrar caja'),
(1, 'puntos_por_dolar', '1', 'INT', 'fidelizacion', 'Puntos ganados por cada dólar de compra'),
(1, 'valor_punto', '0.01', 'DECIMAL', 'fidelizacion', 'Valor en dólares de cada punto'),
(1, 'puntos_minimos_canje', '100', 'INT', 'fidelizacion', 'Puntos mínimos para canjear'),
(1, 'stock_alerta_dias', '7', 'INT', 'inventario', 'Días para revisar alertas de stock'),
(1, 'permitir_venta_sin_stock', '0', 'BOOL', 'inventario', 'Permitir vender productos sin stock'),
(1, 'ticket_header', 'Gracias por su compra', 'STRING', 'ticket', 'Encabezado del ticket'),
(1, 'ticket_footer', 'Cambios con ticket dentro de 15 días', 'STRING', 'ticket', 'Pie del ticket');

-- Caja por defecto
INSERT INTO store_cajas (caj_tenant_id, caj_nombre, caj_codigo, caj_ubicacion) VALUES
(1, 'Caja Principal', 'CAJA-001', 'Mostrador principal');

-- Menú del módulo Store (completar ítems faltantes)
-- Los IDs de menú 64-78 ya existen, agregar los que falten para caja y devoluciones

-- Categorías deportivas de ejemplo
INSERT INTO store_categorias (cat_tenant_id, cat_nombre, cat_slug, cat_icono, cat_orden) VALUES
(1, 'Balones', 'balones', 'fas fa-futbol', 1),
(1, 'Ropa Deportiva', 'ropa-deportiva', 'fas fa-tshirt', 2),
(1, 'Calzado Deportivo', 'calzado-deportivo', 'fas fa-shoe-prints', 3),
(1, 'Accesorios', 'accesorios', 'fas fa-glasses', 4),
(1, 'Equipamiento', 'equipamiento', 'fas fa-dumbbell', 5),
(1, 'Suplementos', 'suplementos', 'fas fa-capsules', 6),
(1, 'Tecnología Deportiva', 'tecnologia-deportiva', 'fas fa-headset', 7),
(1, 'Hidratación', 'hidratacion', 'fas fa-tint', 8);

-- Marcas deportivas de ejemplo
INSERT INTO store_marcas (mar_tenant_id, mar_nombre, mar_slug) VALUES
(1, 'Nike', 'nike'),
(1, 'Adidas', 'adidas'),
(1, 'Puma', 'puma'),
(1, 'Under Armour', 'under-armour'),
(1, 'New Balance', 'new-balance'),
(1, 'Reebok', 'reebok'),
(1, 'Genérica', 'generica');

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- FIN DEL ESQUEMA — DigiSports Store
-- 28 tablas | Multi-tenant | MySQL 8+
-- =====================================================================
