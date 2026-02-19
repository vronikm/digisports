-- ============================================================================
-- DigiSports Store - Esquema POS Tienda Deportiva
-- Base de datos: digisports_core (MySQL 8+)
-- Arquitectura: Multi-Tenant
-- Fecha: 2026-02-09
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- ============================================================================
-- 1. MÓDULO CATÁLOGO
-- ============================================================================

-- --------------------------------------------------------
-- store_categorias - Categorías jerárquicas de productos
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_categorias`;
CREATE TABLE IF NOT EXISTS `store_categorias` (
  `cat_categoria_id` INT NOT NULL AUTO_INCREMENT,
  `cat_tenant_id` INT NOT NULL,
  `cat_padre_id` INT DEFAULT NULL COMMENT 'Categoría padre para jerarquía',
  `cat_nombre` VARCHAR(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cat_slug` VARCHAR(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cat_descripcion` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cat_imagen` VARCHAR(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cat_orden` INT DEFAULT 0,
  `cat_nivel` TINYINT DEFAULT 1 COMMENT 'Nivel de profundidad en árbol',
  `cat_estado` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  `cat_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `cat_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cat_usuario_registro` INT DEFAULT NULL,
  `cat_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`cat_categoria_id`),
  KEY `idx_cat_tenant` (`cat_tenant_id`),
  KEY `idx_cat_padre` (`cat_padre_id`),
  KEY `idx_cat_slug` (`cat_tenant_id`, `cat_slug`),
  KEY `idx_cat_estado` (`cat_tenant_id`, `cat_estado`),
  CONSTRAINT `fk_cat_tenant` FOREIGN KEY (`cat_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cat_padre` FOREIGN KEY (`cat_padre_id`) REFERENCES `store_categorias` (`cat_categoria_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorías jerárquicas de productos';

-- --------------------------------------------------------
-- store_marcas - Marcas de productos deportivos
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_marcas`;
CREATE TABLE IF NOT EXISTS `store_marcas` (
  `mar_marca_id` INT NOT NULL AUTO_INCREMENT,
  `mar_tenant_id` INT NOT NULL,
  `mar_nombre` VARCHAR(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mar_slug` VARCHAR(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mar_logo` VARCHAR(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mar_descripcion` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mar_estado` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `mar_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `mar_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `mar_usuario_registro` INT DEFAULT NULL,
  `mar_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`mar_marca_id`),
  KEY `idx_mar_tenant` (`mar_tenant_id`),
  KEY `idx_mar_nombre` (`mar_tenant_id`, `mar_nombre`),
  KEY `idx_mar_estado` (`mar_tenant_id`, `mar_estado`),
  CONSTRAINT `fk_mar_tenant` FOREIGN KEY (`mar_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Marcas de productos deportivos';

-- --------------------------------------------------------
-- store_productos - Catálogo principal de productos
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_productos`;
CREATE TABLE IF NOT EXISTS `store_productos` (
  `pro_producto_id` INT NOT NULL AUTO_INCREMENT,
  `pro_tenant_id` INT NOT NULL,
  `pro_categoria_id` INT DEFAULT NULL,
  `pro_marca_id` INT DEFAULT NULL,
  `pro_sku` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código interno único',
  `pro_codigo_barras` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'EAN/UPC',
  `pro_nombre` VARCHAR(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pro_slug` VARCHAR(280) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pro_descripcion` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pro_descripcion_corta` VARCHAR(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pro_tipo` ENUM('SIMPLE','VARIABLE','SERVICIO','KIT') COLLATE utf8mb4_unicode_ci DEFAULT 'SIMPLE',
  `pro_precio_compra` DECIMAL(12,4) DEFAULT 0.0000 COMMENT 'Costo de adquisición',
  `pro_precio_venta` DECIMAL(12,4) NOT NULL COMMENT 'PVP base',
  `pro_precio_mayorista` DECIMAL(12,4) DEFAULT NULL,
  `pro_impuesto_id` INT DEFAULT NULL,
  `pro_aplica_iva` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S' COMMENT 'S=Sí, N=No',
  `pro_peso_kg` DECIMAL(8,3) DEFAULT NULL,
  `pro_largo_cm` DECIMAL(8,2) DEFAULT NULL,
  `pro_ancho_cm` DECIMAL(8,2) DEFAULT NULL,
  `pro_alto_cm` DECIMAL(8,2) DEFAULT NULL,
  `pro_talla_aplica` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Producto maneja tallas',
  `pro_color_aplica` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Producto maneja colores',
  `pro_stock_minimo` INT DEFAULT 0,
  `pro_stock_maximo` INT DEFAULT NULL,
  `pro_garantia_meses` TINYINT DEFAULT NULL,
  `pro_modelo` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pro_genero` ENUM('HOMBRE','MUJER','UNISEX','NIÑO','NIÑA','INFANTIL') COLLATE utf8mb4_unicode_ci DEFAULT 'UNISEX',
  `pro_temporada` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ej: VERANO_2026',
  `pro_destacado` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `pro_vendible` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `pro_estado` ENUM('ACTIVO','INACTIVO','DESCONTINUADO','BORRADOR') COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `pro_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `pro_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pro_usuario_registro` INT DEFAULT NULL,
  `pro_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`pro_producto_id`),
  UNIQUE KEY `uq_pro_sku_tenant` (`pro_tenant_id`, `pro_sku`),
  KEY `idx_pro_tenant` (`pro_tenant_id`),
  KEY `idx_pro_categoria` (`pro_categoria_id`),
  KEY `idx_pro_marca` (`pro_marca_id`),
  KEY `idx_pro_codigo_barras` (`pro_tenant_id`, `pro_codigo_barras`),
  KEY `idx_pro_nombre` (`pro_tenant_id`, `pro_nombre`(100)),
  KEY `idx_pro_estado` (`pro_tenant_id`, `pro_estado`),
  KEY `idx_pro_tipo` (`pro_tenant_id`, `pro_tipo`),
  KEY `idx_pro_impuesto` (`pro_impuesto_id`),
  CONSTRAINT `fk_pro_tenant` FOREIGN KEY (`pro_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pro_categoria` FOREIGN KEY (`pro_categoria_id`) REFERENCES `store_categorias` (`cat_categoria_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pro_marca` FOREIGN KEY (`pro_marca_id`) REFERENCES `store_marcas` (`mar_marca_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pro_impuesto` FOREIGN KEY (`pro_impuesto_id`) REFERENCES `store_impuestos` (`imp_impuesto_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo principal de productos';

-- --------------------------------------------------------
-- store_producto_variantes - Variantes por talla/color/material
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_producto_variantes`;
CREATE TABLE IF NOT EXISTS `store_producto_variantes` (
  `var_variante_id` INT NOT NULL AUTO_INCREMENT,
  `var_tenant_id` INT NOT NULL,
  `var_producto_id` INT NOT NULL,
  `var_sku` VARCHAR(60) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SKU específico de variante',
  `var_codigo_barras` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `var_talla` VARCHAR(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ej: S, M, L, XL, 38, 42',
  `var_color` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `var_color_hex` VARCHAR(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código hex del color',
  `var_material` VARCHAR(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `var_precio_compra` DECIMAL(12,4) DEFAULT NULL COMMENT 'NULL = hereda del producto',
  `var_precio_venta` DECIMAL(12,4) DEFAULT NULL COMMENT 'NULL = hereda del producto',
  `var_precio_mayorista` DECIMAL(12,4) DEFAULT NULL,
  `var_peso_kg` DECIMAL(8,3) DEFAULT NULL,
  `var_imagen` VARCHAR(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `var_orden` INT DEFAULT 0,
  `var_estado` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  `var_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `var_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `var_usuario_registro` INT DEFAULT NULL,
  `var_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`var_variante_id`),
  UNIQUE KEY `uq_var_sku_tenant` (`var_tenant_id`, `var_sku`),
  KEY `idx_var_tenant` (`var_tenant_id`),
  KEY `idx_var_producto` (`var_producto_id`),
  KEY `idx_var_talla_color` (`var_tenant_id`, `var_talla`, `var_color`),
  KEY `idx_var_codigo_barras` (`var_tenant_id`, `var_codigo_barras`),
  CONSTRAINT `fk_var_tenant` FOREIGN KEY (`var_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_var_producto` FOREIGN KEY (`var_producto_id`) REFERENCES `store_productos` (`pro_producto_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Variantes de producto (talla/color/material)';

-- --------------------------------------------------------
-- store_producto_imagenes - Galería de imágenes por producto
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_producto_imagenes`;
CREATE TABLE IF NOT EXISTS `store_producto_imagenes` (
  `img_imagen_id` INT NOT NULL AUTO_INCREMENT,
  `img_tenant_id` INT NOT NULL,
  `img_producto_id` INT NOT NULL,
  `img_variante_id` INT DEFAULT NULL COMMENT 'Imagen específica de variante',
  `img_url` VARCHAR(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `img_alt_text` VARCHAR(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `img_es_principal` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `img_orden` INT DEFAULT 0,
  `img_estado` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `img_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `img_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`img_imagen_id`),
  KEY `idx_img_tenant` (`img_tenant_id`),
  KEY `idx_img_producto` (`img_producto_id`),
  KEY `idx_img_variante` (`img_variante_id`),
  CONSTRAINT `fk_img_tenant` FOREIGN KEY (`img_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_img_producto` FOREIGN KEY (`img_producto_id`) REFERENCES `store_productos` (`pro_producto_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_img_variante` FOREIGN KEY (`img_variante_id`) REFERENCES `store_producto_variantes` (`var_variante_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Galería de imágenes de productos';


-- ============================================================================
-- 2. MÓDULO INVENTARIO / STOCK
-- ============================================================================

-- --------------------------------------------------------
-- store_proveedores - Proveedores de mercadería
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_proveedores`;
CREATE TABLE IF NOT EXISTS `store_proveedores` (
  `prv_proveedor_id` INT NOT NULL AUTO_INCREMENT,
  `prv_tenant_id` INT NOT NULL,
  `prv_tipo_identificacion` ENUM('RUC','CEDULA','PASAPORTE') COLLATE utf8mb4_unicode_ci DEFAULT 'RUC',
  `prv_identificacion` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prv_razon_social` VARCHAR(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prv_nombre_comercial` VARCHAR(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_direccion` VARCHAR(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_telefono` VARCHAR(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_celular` VARCHAR(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_email` VARCHAR(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_contacto_nombre` VARCHAR(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_contacto_telefono` VARCHAR(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_contacto_email` VARCHAR(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_plazo_pago_dias` INT DEFAULT 0,
  `prv_descuento_defecto` DECIMAL(5,2) DEFAULT 0.00,
  `prv_notas` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_estado` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `prv_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `prv_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `prv_usuario_registro` INT DEFAULT NULL,
  `prv_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`prv_proveedor_id`),
  UNIQUE KEY `uq_prv_ident_tenant` (`prv_tenant_id`, `prv_identificacion`),
  KEY `idx_prv_tenant` (`prv_tenant_id`),
  KEY `idx_prv_razon_social` (`prv_tenant_id`, `prv_razon_social`(100)),
  KEY `idx_prv_estado` (`prv_tenant_id`, `prv_estado`),
  CONSTRAINT `fk_prv_tenant` FOREIGN KEY (`prv_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Proveedores de mercadería';

-- --------------------------------------------------------
-- store_stock - Stock actual por variante y ubicación
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_stock`;
CREATE TABLE IF NOT EXISTS `store_stock` (
  `stk_stock_id` INT NOT NULL AUTO_INCREMENT,
  `stk_tenant_id` INT NOT NULL,
  `stk_producto_id` INT NOT NULL,
  `stk_variante_id` INT DEFAULT NULL COMMENT 'NULL para producto simple',
  `stk_ubicacion` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT 'PRINCIPAL' COMMENT 'Bodega/sucursal',
  `stk_cantidad` INT NOT NULL DEFAULT 0,
  `stk_cantidad_reservada` INT DEFAULT 0 COMMENT 'Unidades comprometidas en pedidos',
  `stk_cantidad_disponible` INT GENERATED ALWAYS AS (`stk_cantidad` - `stk_cantidad_reservada`) STORED,
  `stk_costo_promedio` DECIMAL(12,4) DEFAULT 0.0000 COMMENT 'Costo promedio ponderado',
  `stk_ultimo_costo` DECIMAL(12,4) DEFAULT 0.0000,
  `stk_fecha_ultimo_ingreso` DATETIME DEFAULT NULL,
  `stk_fecha_ultimo_egreso` DATETIME DEFAULT NULL,
  `stk_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `stk_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stk_stock_id`),
  UNIQUE KEY `uq_stk_prod_var_ubic` (`stk_tenant_id`, `stk_producto_id`, `stk_variante_id`, `stk_ubicacion`),
  KEY `idx_stk_tenant` (`stk_tenant_id`),
  KEY `idx_stk_producto` (`stk_producto_id`),
  KEY `idx_stk_variante` (`stk_variante_id`),
  KEY `idx_stk_ubicacion` (`stk_tenant_id`, `stk_ubicacion`),
  CONSTRAINT `fk_stk_tenant` FOREIGN KEY (`stk_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_stk_producto` FOREIGN KEY (`stk_producto_id`) REFERENCES `store_productos` (`pro_producto_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_stk_variante` FOREIGN KEY (`stk_variante_id`) REFERENCES `store_producto_variantes` (`var_variante_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stock actual por producto/variante/ubicación';

-- --------------------------------------------------------
-- store_stock_movimientos - Trazabilidad de entradas/salidas
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_stock_movimientos`;
CREATE TABLE IF NOT EXISTS `store_stock_movimientos` (
  `mov_movimiento_id` INT NOT NULL AUTO_INCREMENT,
  `mov_tenant_id` INT NOT NULL,
  `mov_producto_id` INT NOT NULL,
  `mov_variante_id` INT DEFAULT NULL,
  `mov_tipo` ENUM('ENTRADA','SALIDA','AJUSTE_POSITIVO','AJUSTE_NEGATIVO','TRANSFERENCIA_IN','TRANSFERENCIA_OUT','DEVOLUCION') COLLATE utf8mb4_unicode_ci NOT NULL,
  `mov_motivo` ENUM('COMPRA','VENTA','AJUSTE_INVENTARIO','DEVOLUCION_CLIENTE','DEVOLUCION_PROVEEDOR','MERMA','ROBO','TRANSFERENCIA','INICIAL','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `mov_cantidad` INT NOT NULL,
  `mov_stock_anterior` INT DEFAULT NULL,
  `mov_stock_posterior` INT DEFAULT NULL,
  `mov_costo_unitario` DECIMAL(12,4) DEFAULT 0.0000,
  `mov_costo_total` DECIMAL(12,2) DEFAULT 0.00,
  `mov_ubicacion_origen` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mov_ubicacion_destino` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mov_referencia_tipo` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'VENTA, ORDEN_COMPRA, DEVOLUCION, etc.',
  `mov_referencia_id` INT DEFAULT NULL COMMENT 'ID del documento relacionado',
  `mov_nota` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mov_fecha_movimiento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mov_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `mov_usuario_registro` INT DEFAULT NULL,
  PRIMARY KEY (`mov_movimiento_id`),
  KEY `idx_mov_tenant` (`mov_tenant_id`),
  KEY `idx_mov_producto` (`mov_producto_id`),
  KEY `idx_mov_variante` (`mov_variante_id`),
  KEY `idx_mov_tipo` (`mov_tenant_id`, `mov_tipo`),
  KEY `idx_mov_fecha` (`mov_tenant_id`, `mov_fecha_movimiento`),
  KEY `idx_mov_referencia` (`mov_referencia_tipo`, `mov_referencia_id`),
  CONSTRAINT `fk_mov_tenant` FOREIGN KEY (`mov_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mov_producto` FOREIGN KEY (`mov_producto_id`) REFERENCES `store_productos` (`pro_producto_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mov_variante` FOREIGN KEY (`mov_variante_id`) REFERENCES `store_producto_variantes` (`var_variante_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de movimientos de inventario';

-- --------------------------------------------------------
-- store_stock_alertas - Niveles mínimos y alertas de stock
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_stock_alertas`;
CREATE TABLE IF NOT EXISTS `store_stock_alertas` (
  `ale_alerta_id` INT NOT NULL AUTO_INCREMENT,
  `ale_tenant_id` INT NOT NULL,
  `ale_producto_id` INT NOT NULL,
  `ale_variante_id` INT DEFAULT NULL,
  `ale_ubicacion` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT 'PRINCIPAL',
  `ale_stock_minimo` INT NOT NULL DEFAULT 5,
  `ale_stock_reorden` INT DEFAULT 10 COMMENT 'Nivel para disparar reorden',
  `ale_stock_maximo` INT DEFAULT NULL,
  `ale_tipo_alerta` ENUM('BAJO_MINIMO','REORDEN','SOBRE_MAXIMO','AGOTADO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ale_notificada` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `ale_fecha_alerta` DATETIME DEFAULT NULL,
  `ale_estado` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `ale_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ale_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ale_alerta_id`),
  UNIQUE KEY `uq_ale_prod_var_ubic` (`ale_tenant_id`, `ale_producto_id`, `ale_variante_id`, `ale_ubicacion`),
  KEY `idx_ale_tenant` (`ale_tenant_id`),
  KEY `idx_ale_producto` (`ale_producto_id`),
  KEY `idx_ale_tipo` (`ale_tenant_id`, `ale_tipo_alerta`),
  CONSTRAINT `fk_ale_tenant` FOREIGN KEY (`ale_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ale_producto` FOREIGN KEY (`ale_producto_id`) REFERENCES `store_productos` (`pro_producto_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ale_variante` FOREIGN KEY (`ale_variante_id`) REFERENCES `store_producto_variantes` (`var_variante_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuración de alertas de stock';

-- --------------------------------------------------------
-- store_ordenes_compra - Pedidos a proveedores
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_ordenes_compra`;
CREATE TABLE IF NOT EXISTS `store_ordenes_compra` (
  `orc_orden_id` INT NOT NULL AUTO_INCREMENT,
  `orc_tenant_id` INT NOT NULL,
  `orc_proveedor_id` INT NOT NULL,
  `orc_numero` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Número secuencial: OC-XXXX',
  `orc_fecha_orden` DATE NOT NULL,
  `orc_fecha_entrega_esperada` DATE DEFAULT NULL,
  `orc_fecha_recepcion` DATE DEFAULT NULL,
  `orc_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `orc_descuento` DECIMAL(12,2) DEFAULT 0.00,
  `orc_impuesto` DECIMAL(12,2) DEFAULT 0.00,
  `orc_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `orc_estado` ENUM('BORRADOR','ENVIADA','PARCIAL','RECIBIDA','CANCELADA') COLLATE utf8mb4_unicode_ci DEFAULT 'BORRADOR',
  `orc_notas` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orc_ubicacion_destino` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT 'PRINCIPAL',
  `orc_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `orc_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `orc_usuario_registro` INT DEFAULT NULL,
  `orc_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`orc_orden_id`),
  UNIQUE KEY `uq_orc_numero_tenant` (`orc_tenant_id`, `orc_numero`),
  KEY `idx_orc_tenant` (`orc_tenant_id`),
  KEY `idx_orc_proveedor` (`orc_proveedor_id`),
  KEY `idx_orc_estado` (`orc_tenant_id`, `orc_estado`),
  KEY `idx_orc_fecha` (`orc_tenant_id`, `orc_fecha_orden`),
  CONSTRAINT `fk_orc_tenant` FOREIGN KEY (`orc_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_orc_proveedor` FOREIGN KEY (`orc_proveedor_id`) REFERENCES `store_proveedores` (`prv_proveedor_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Órdenes de compra a proveedores';

-- --------------------------------------------------------
-- store_ordenes_compra_detalle - Líneas de la orden de compra
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_ordenes_compra_detalle`;
CREATE TABLE IF NOT EXISTS `store_ordenes_compra_detalle` (
  `ocd_detalle_id` INT NOT NULL AUTO_INCREMENT,
  `ocd_tenant_id` INT NOT NULL,
  `ocd_orden_id` INT NOT NULL,
  `ocd_producto_id` INT NOT NULL,
  `ocd_variante_id` INT DEFAULT NULL,
  `ocd_cantidad_pedida` INT NOT NULL,
  `ocd_cantidad_recibida` INT DEFAULT 0,
  `ocd_precio_unitario` DECIMAL(12,4) NOT NULL,
  `ocd_descuento_porcentaje` DECIMAL(5,2) DEFAULT 0.00,
  `ocd_descuento_valor` DECIMAL(12,2) DEFAULT 0.00,
  `ocd_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `ocd_notas` VARCHAR(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ocd_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ocd_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ocd_detalle_id`),
  KEY `idx_ocd_tenant` (`ocd_tenant_id`),
  KEY `idx_ocd_orden` (`ocd_orden_id`),
  KEY `idx_ocd_producto` (`ocd_producto_id`),
  KEY `idx_ocd_variante` (`ocd_variante_id`),
  CONSTRAINT `fk_ocd_tenant` FOREIGN KEY (`ocd_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ocd_orden` FOREIGN KEY (`ocd_orden_id`) REFERENCES `store_ordenes_compra` (`orc_orden_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ocd_producto` FOREIGN KEY (`ocd_producto_id`) REFERENCES `store_productos` (`pro_producto_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_ocd_variante` FOREIGN KEY (`ocd_variante_id`) REFERENCES `store_producto_variantes` (`var_variante_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de órdenes de compra';


-- ============================================================================
-- 3. MÓDULO PUNTO DE VENTA
-- ============================================================================

-- --------------------------------------------------------
-- store_descuentos - Configuración de descuentos y promociones
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_descuentos`;
CREATE TABLE IF NOT EXISTS `store_descuentos` (
  `dsc_descuento_id` INT NOT NULL AUTO_INCREMENT,
  `dsc_tenant_id` INT NOT NULL,
  `dsc_nombre` VARCHAR(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dsc_descripcion` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dsc_tipo` ENUM('PORCENTAJE','MONTO_FIJO','2X1','NXM') COLLATE utf8mb4_unicode_ci DEFAULT 'PORCENTAJE',
  `dsc_valor` DECIMAL(12,4) NOT NULL COMMENT '% o monto según tipo',
  `dsc_aplica_a` ENUM('PRODUCTO','CATEGORIA','MARCA','VENTA_TOTAL','CLIENTE') COLLATE utf8mb4_unicode_ci DEFAULT 'PRODUCTO',
  `dsc_referencia_id` INT DEFAULT NULL COMMENT 'ID de producto/categoría/marca según aplica_a',
  `dsc_monto_minimo_compra` DECIMAL(12,2) DEFAULT NULL,
  `dsc_cantidad_minima` INT DEFAULT NULL,
  `dsc_fecha_inicio` DATETIME DEFAULT NULL,
  `dsc_fecha_fin` DATETIME DEFAULT NULL,
  `dsc_uso_maximo` INT DEFAULT NULL COMMENT 'Máximo de usos totales',
  `dsc_uso_actual` INT DEFAULT 0,
  `dsc_codigo_promo` VARCHAR(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dsc_acumulable` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `dsc_estado` ENUM('ACTIVO','INACTIVO','EXPIRADO') COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `dsc_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `dsc_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dsc_usuario_registro` INT DEFAULT NULL,
  `dsc_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`dsc_descuento_id`),
  KEY `idx_dsc_tenant` (`dsc_tenant_id`),
  KEY `idx_dsc_estado` (`dsc_tenant_id`, `dsc_estado`),
  KEY `idx_dsc_fechas` (`dsc_tenant_id`, `dsc_fecha_inicio`, `dsc_fecha_fin`),
  KEY `idx_dsc_codigo` (`dsc_tenant_id`, `dsc_codigo_promo`),
  CONSTRAINT `fk_dsc_tenant` FOREIGN KEY (`dsc_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Descuentos y promociones configurables';

-- --------------------------------------------------------
-- store_ventas - Ticket/Factura completa de venta
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_ventas`;
CREATE TABLE IF NOT EXISTS `store_ventas` (
  `ven_venta_id` INT NOT NULL AUTO_INCREMENT,
  `ven_tenant_id` INT NOT NULL,
  `ven_numero` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Número secuencial: V-XXXX',
  `ven_tipo_documento` ENUM('TICKET','FACTURA','NOTA_VENTA') COLLATE utf8mb4_unicode_ci DEFAULT 'TICKET',
  `ven_cliente_id` INT DEFAULT NULL COMMENT 'NULL = consumidor final',
  `ven_caja_id` INT DEFAULT NULL,
  `ven_turno_id` INT DEFAULT NULL,
  `ven_fecha_venta` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ven_subtotal_sin_imp` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal sin impuestos',
  `ven_subtotal_con_imp` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal gravado con IVA',
  `ven_descuento_porcentaje` DECIMAL(5,2) DEFAULT 0.00,
  `ven_descuento_valor` DECIMAL(12,2) DEFAULT 0.00,
  `ven_descuento_id` INT DEFAULT NULL,
  `ven_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `ven_impuesto_porcentaje` DECIMAL(5,2) DEFAULT 15.00 COMMENT 'IVA Ecuador 15%',
  `ven_impuesto_valor` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `ven_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `ven_total_items` INT DEFAULT 0,
  `ven_total_unidades` INT DEFAULT 0,
  `ven_estado` ENUM('PENDIENTE','COMPLETADA','ANULADA') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `ven_vendedor_id` INT DEFAULT NULL COMMENT 'Usuario vendedor',
  `ven_notas` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ven_factura_electronica_id` INT DEFAULT NULL COMMENT 'Ref a factura SRI si aplica',
  `ven_cotizacion_origen_id` INT DEFAULT NULL COMMENT 'Cotización que originó la venta',
  `ven_ip_registro` VARCHAR(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ven_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ven_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ven_usuario_registro` INT DEFAULT NULL,
  `ven_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`ven_venta_id`),
  UNIQUE KEY `uq_ven_numero_tenant` (`ven_tenant_id`, `ven_numero`),
  KEY `idx_ven_tenant` (`ven_tenant_id`),
  KEY `idx_ven_cliente` (`ven_cliente_id`),
  KEY `idx_ven_caja` (`ven_caja_id`),
  KEY `idx_ven_turno` (`ven_turno_id`),
  KEY `idx_ven_fecha` (`ven_tenant_id`, `ven_fecha_venta`),
  KEY `idx_ven_estado` (`ven_tenant_id`, `ven_estado`),
  KEY `idx_ven_tipo_doc` (`ven_tenant_id`, `ven_tipo_documento`),
  KEY `idx_ven_vendedor` (`ven_vendedor_id`),
  KEY `idx_ven_descuento` (`ven_descuento_id`),
  CONSTRAINT `fk_ven_tenant` FOREIGN KEY (`ven_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ven_cliente` FOREIGN KEY (`ven_cliente_id`) REFERENCES `store_clientes` (`cli_cliente_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ven_caja` FOREIGN KEY (`ven_caja_id`) REFERENCES `store_cajas` (`caj_caja_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ven_turno` FOREIGN KEY (`ven_turno_id`) REFERENCES `store_caja_turnos` (`tur_turno_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ven_descuento` FOREIGN KEY (`ven_descuento_id`) REFERENCES `store_descuentos` (`dsc_descuento_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ventas/Tickets del POS';

-- --------------------------------------------------------
-- store_venta_items - Líneas/ítems de cada venta
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_venta_items`;
CREATE TABLE IF NOT EXISTS `store_venta_items` (
  `vit_item_id` INT NOT NULL AUTO_INCREMENT,
  `vit_tenant_id` INT NOT NULL,
  `vit_venta_id` INT NOT NULL,
  `vit_producto_id` INT NOT NULL,
  `vit_variante_id` INT DEFAULT NULL,
  `vit_sku` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vit_nombre_producto` VARCHAR(250) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Snapshot del nombre al momento de venta',
  `vit_descripcion_variante` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ej: Talla M / Rojo',
  `vit_cantidad` INT NOT NULL DEFAULT 1,
  `vit_precio_unitario` DECIMAL(12,4) NOT NULL,
  `vit_precio_original` DECIMAL(12,4) DEFAULT NULL COMMENT 'Precio antes de descuento',
  `vit_descuento_porcentaje` DECIMAL(5,2) DEFAULT 0.00,
  `vit_descuento_valor` DECIMAL(12,2) DEFAULT 0.00,
  `vit_aplica_iva` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `vit_impuesto_porcentaje` DECIMAL(5,2) DEFAULT 15.00,
  `vit_impuesto_valor` DECIMAL(12,2) DEFAULT 0.00,
  `vit_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `vit_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `vit_costo_unitario` DECIMAL(12,4) DEFAULT 0.0000 COMMENT 'Costo al momento de venta para reportes',
  `vit_notas` VARCHAR(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vit_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`vit_item_id`),
  KEY `idx_vit_tenant` (`vit_tenant_id`),
  KEY `idx_vit_venta` (`vit_venta_id`),
  KEY `idx_vit_producto` (`vit_producto_id`),
  KEY `idx_vit_variante` (`vit_variante_id`),
  CONSTRAINT `fk_vit_tenant` FOREIGN KEY (`vit_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vit_venta` FOREIGN KEY (`vit_venta_id`) REFERENCES `store_ventas` (`ven_venta_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vit_producto` FOREIGN KEY (`vit_producto_id`) REFERENCES `store_productos` (`pro_producto_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_vit_variante` FOREIGN KEY (`vit_variante_id`) REFERENCES `store_producto_variantes` (`var_variante_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Líneas de venta';

-- --------------------------------------------------------
-- store_venta_pagos - Múltiples formas de pago por venta
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_venta_pagos`;
CREATE TABLE IF NOT EXISTS `store_venta_pagos` (
  `vpg_pago_id` INT NOT NULL AUTO_INCREMENT,
  `vpg_tenant_id` INT NOT NULL,
  `vpg_venta_id` INT NOT NULL,
  `vpg_metodo_pago` ENUM('EFECTIVO','TARJETA_CREDITO','TARJETA_DEBITO','TRANSFERENCIA','CHEQUE','CREDITO_CLIENTE','PUNTOS','MIXTO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `vpg_monto` DECIMAL(12,2) NOT NULL,
  `vpg_monto_recibido` DECIMAL(12,2) DEFAULT NULL COMMENT 'Solo para efectivo',
  `vpg_cambio` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Vuelto en efectivo',
  `vpg_referencia` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nro aprobación, nro cheque, etc.',
  `vpg_banco` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vpg_ultimos_4_digitos` VARCHAR(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vpg_cuotas` INT DEFAULT NULL COMMENT 'Cuotas si aplica',
  `vpg_estado` ENUM('APROBADO','PENDIENTE','RECHAZADO','ANULADO') COLLATE utf8mb4_unicode_ci DEFAULT 'APROBADO',
  `vpg_fecha_pago` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vpg_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `vpg_usuario_registro` INT DEFAULT NULL,
  PRIMARY KEY (`vpg_pago_id`),
  KEY `idx_vpg_tenant` (`vpg_tenant_id`),
  KEY `idx_vpg_venta` (`vpg_venta_id`),
  KEY `idx_vpg_metodo` (`vpg_tenant_id`, `vpg_metodo_pago`),
  KEY `idx_vpg_estado` (`vpg_tenant_id`, `vpg_estado`),
  CONSTRAINT `fk_vpg_tenant` FOREIGN KEY (`vpg_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vpg_venta` FOREIGN KEY (`vpg_venta_id`) REFERENCES `store_ventas` (`ven_venta_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pagos asociados a ventas (multi-pago)';

-- --------------------------------------------------------
-- store_cotizaciones - Presupuestos/Proformas
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_cotizaciones`;
CREATE TABLE IF NOT EXISTS `store_cotizaciones` (
  `cot_cotizacion_id` INT NOT NULL AUTO_INCREMENT,
  `cot_tenant_id` INT NOT NULL,
  `cot_numero` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'COT-XXXX',
  `cot_cliente_id` INT DEFAULT NULL,
  `cot_fecha_cotizacion` DATE NOT NULL,
  `cot_fecha_validez` DATE DEFAULT NULL COMMENT 'Fecha hasta la cual es válida',
  `cot_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `cot_descuento_valor` DECIMAL(12,2) DEFAULT 0.00,
  `cot_impuesto_valor` DECIMAL(12,2) DEFAULT 0.00,
  `cot_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `cot_notas` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cot_condiciones` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Términos y condiciones',
  `cot_estado` ENUM('BORRADOR','ENVIADA','ACEPTADA','RECHAZADA','EXPIRADA','CONVERTIDA') COLLATE utf8mb4_unicode_ci DEFAULT 'BORRADOR',
  `cot_venta_id` INT DEFAULT NULL COMMENT 'Venta generada si fue aceptada',
  `cot_vendedor_id` INT DEFAULT NULL,
  `cot_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `cot_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cot_usuario_registro` INT DEFAULT NULL,
  `cot_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`cot_cotizacion_id`),
  UNIQUE KEY `uq_cot_numero_tenant` (`cot_tenant_id`, `cot_numero`),
  KEY `idx_cot_tenant` (`cot_tenant_id`),
  KEY `idx_cot_cliente` (`cot_cliente_id`),
  KEY `idx_cot_estado` (`cot_tenant_id`, `cot_estado`),
  KEY `idx_cot_fecha` (`cot_tenant_id`, `cot_fecha_cotizacion`),
  CONSTRAINT `fk_cot_tenant` FOREIGN KEY (`cot_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cot_cliente` FOREIGN KEY (`cot_cliente_id`) REFERENCES `store_clientes` (`cli_cliente_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cotizaciones/Proformas';

-- --------------------------------------------------------
-- store_cotizacion_items - Líneas de cotización
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_cotizacion_items`;
CREATE TABLE IF NOT EXISTS `store_cotizacion_items` (
  `coi_item_id` INT NOT NULL AUTO_INCREMENT,
  `coi_tenant_id` INT NOT NULL,
  `coi_cotizacion_id` INT NOT NULL,
  `coi_producto_id` INT NOT NULL,
  `coi_variante_id` INT DEFAULT NULL,
  `coi_nombre_producto` VARCHAR(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coi_descripcion_variante` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coi_cantidad` INT NOT NULL DEFAULT 1,
  `coi_precio_unitario` DECIMAL(12,4) NOT NULL,
  `coi_descuento_porcentaje` DECIMAL(5,2) DEFAULT 0.00,
  `coi_descuento_valor` DECIMAL(12,2) DEFAULT 0.00,
  `coi_aplica_iva` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `coi_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `coi_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`coi_item_id`),
  KEY `idx_coi_tenant` (`coi_tenant_id`),
  KEY `idx_coi_cotizacion` (`coi_cotizacion_id`),
  KEY `idx_coi_producto` (`coi_producto_id`),
  CONSTRAINT `fk_coi_tenant` FOREIGN KEY (`coi_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_coi_cotizacion` FOREIGN KEY (`coi_cotizacion_id`) REFERENCES `store_cotizaciones` (`cot_cotizacion_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_coi_producto` FOREIGN KEY (`coi_producto_id`) REFERENCES `store_productos` (`pro_producto_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_coi_variante` FOREIGN KEY (`coi_variante_id`) REFERENCES `store_producto_variantes` (`var_variante_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de cotizaciones';

-- --------------------------------------------------------
-- store_devoluciones - Devoluciones de venta
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_devoluciones`;
CREATE TABLE IF NOT EXISTS `store_devoluciones` (
  `dev_devolucion_id` INT NOT NULL AUTO_INCREMENT,
  `dev_tenant_id` INT NOT NULL,
  `dev_numero` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'DEV-XXXX',
  `dev_venta_id` INT NOT NULL COMMENT 'Venta original',
  `dev_cliente_id` INT DEFAULT NULL,
  `dev_fecha_devolucion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dev_motivo` ENUM('DEFECTO','TALLA_INCORRECTA','CAMBIO_OPINION','GARANTIA','DUPLICADO','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `dev_motivo_detalle` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dev_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `dev_impuesto_valor` DECIMAL(12,2) DEFAULT 0.00,
  `dev_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `dev_tipo_reembolso` ENUM('EFECTIVO','CREDITO_NOTA','CAMBIO_PRODUCTO','TARJETA') COLLATE utf8mb4_unicode_ci DEFAULT 'EFECTIVO',
  `dev_reembolso_referencia` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dev_estado` ENUM('PENDIENTE','APROBADA','RECHAZADA','PROCESADA','ANULADA') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `dev_aprobado_por` INT DEFAULT NULL,
  `dev_caja_id` INT DEFAULT NULL,
  `dev_notas` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dev_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `dev_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dev_usuario_registro` INT DEFAULT NULL,
  `dev_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`dev_devolucion_id`),
  UNIQUE KEY `uq_dev_numero_tenant` (`dev_tenant_id`, `dev_numero`),
  KEY `idx_dev_tenant` (`dev_tenant_id`),
  KEY `idx_dev_venta` (`dev_venta_id`),
  KEY `idx_dev_cliente` (`dev_cliente_id`),
  KEY `idx_dev_estado` (`dev_tenant_id`, `dev_estado`),
  KEY `idx_dev_fecha` (`dev_tenant_id`, `dev_fecha_devolucion`),
  CONSTRAINT `fk_dev_tenant` FOREIGN KEY (`dev_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dev_venta` FOREIGN KEY (`dev_venta_id`) REFERENCES `store_ventas` (`ven_venta_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_dev_cliente` FOREIGN KEY (`dev_cliente_id`) REFERENCES `store_clientes` (`cli_cliente_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dev_caja` FOREIGN KEY (`dev_caja_id`) REFERENCES `store_cajas` (`caj_caja_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Devoluciones de venta';

-- --------------------------------------------------------
-- store_devolucion_items - Líneas de devolución
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_devolucion_items`;
CREATE TABLE IF NOT EXISTS `store_devolucion_items` (
  `dvi_item_id` INT NOT NULL AUTO_INCREMENT,
  `dvi_tenant_id` INT NOT NULL,
  `dvi_devolucion_id` INT NOT NULL,
  `dvi_venta_item_id` INT NOT NULL COMMENT 'Línea original de la venta',
  `dvi_producto_id` INT NOT NULL,
  `dvi_variante_id` INT DEFAULT NULL,
  `dvi_cantidad` INT NOT NULL DEFAULT 1,
  `dvi_precio_unitario` DECIMAL(12,4) NOT NULL,
  `dvi_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `dvi_impuesto_valor` DECIMAL(12,2) DEFAULT 0.00,
  `dvi_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `dvi_estado_producto` ENUM('NUEVO','BUEN_ESTADO','DAÑADO','DEFECTUOSO') COLLATE utf8mb4_unicode_ci DEFAULT 'BUEN_ESTADO',
  `dvi_reingresa_stock` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `dvi_notas` VARCHAR(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dvi_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dvi_item_id`),
  KEY `idx_dvi_tenant` (`dvi_tenant_id`),
  KEY `idx_dvi_devolucion` (`dvi_devolucion_id`),
  KEY `idx_dvi_venta_item` (`dvi_venta_item_id`),
  KEY `idx_dvi_producto` (`dvi_producto_id`),
  CONSTRAINT `fk_dvi_tenant` FOREIGN KEY (`dvi_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dvi_devolucion` FOREIGN KEY (`dvi_devolucion_id`) REFERENCES `store_devoluciones` (`dev_devolucion_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dvi_venta_item` FOREIGN KEY (`dvi_venta_item_id`) REFERENCES `store_venta_items` (`vit_item_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_dvi_producto` FOREIGN KEY (`dvi_producto_id`) REFERENCES `store_productos` (`pro_producto_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_dvi_variante` FOREIGN KEY (`dvi_variante_id`) REFERENCES `store_producto_variantes` (`var_variante_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de ítems devueltos';


-- ============================================================================
-- 4. MÓDULO GESTIÓN DE CAJA
-- ============================================================================

-- --------------------------------------------------------
-- store_cajas - Registradoras/Terminales POS
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_cajas`;
CREATE TABLE IF NOT EXISTS `store_cajas` (
  `caj_caja_id` INT NOT NULL AUTO_INCREMENT,
  `caj_tenant_id` INT NOT NULL,
  `caj_nombre` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: Caja 1, Terminal A',
  `caj_codigo` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caj_ubicacion` VARCHAR(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Sucursal/Piso',
  `caj_serie_impresora` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caj_tipo` ENUM('PRINCIPAL','SECUNDARIA','MOVIL') COLLATE utf8mb4_unicode_ci DEFAULT 'PRINCIPAL',
  `caj_estado` ENUM('DISPONIBLE','EN_USO','FUERA_SERVICIO','INACTIVA') COLLATE utf8mb4_unicode_ci DEFAULT 'DISPONIBLE',
  `caj_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `caj_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `caj_usuario_registro` INT DEFAULT NULL,
  `caj_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`caj_caja_id`),
  UNIQUE KEY `uq_caj_codigo_tenant` (`caj_tenant_id`, `caj_codigo`),
  KEY `idx_caj_tenant` (`caj_tenant_id`),
  KEY `idx_caj_estado` (`caj_tenant_id`, `caj_estado`),
  CONSTRAINT `fk_caj_tenant` FOREIGN KEY (`caj_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cajas registradoras/Terminales POS';

-- --------------------------------------------------------
-- store_caja_turnos - Apertura y cierre de turno por caja
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_caja_turnos`;
CREATE TABLE IF NOT EXISTS `store_caja_turnos` (
  `tur_turno_id` INT NOT NULL AUTO_INCREMENT,
  `tur_tenant_id` INT NOT NULL,
  `tur_caja_id` INT NOT NULL,
  `tur_usuario_id` INT NOT NULL COMMENT 'Cajero que abre el turno',
  `tur_fecha_apertura` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tur_fecha_cierre` DATETIME DEFAULT NULL,
  `tur_monto_apertura` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Fondo de caja inicial',
  `tur_monto_ventas_efectivo` DECIMAL(12,2) DEFAULT 0.00,
  `tur_monto_ventas_tarjeta` DECIMAL(12,2) DEFAULT 0.00,
  `tur_monto_ventas_transferencia` DECIMAL(12,2) DEFAULT 0.00,
  `tur_monto_ventas_otros` DECIMAL(12,2) DEFAULT 0.00,
  `tur_monto_devoluciones` DECIMAL(12,2) DEFAULT 0.00,
  `tur_monto_entradas_manual` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Ingresos manuales',
  `tur_monto_salidas_manual` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Retiros manuales',
  `tur_monto_esperado` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Total esperado al cierre',
  `tur_monto_real_cierre` DECIMAL(12,2) DEFAULT NULL COMMENT 'Total contado al cierre',
  `tur_diferencia` DECIMAL(12,2) DEFAULT NULL COMMENT 'Sobrante(+) o faltante(-)',
  `tur_total_ventas` INT DEFAULT 0,
  `tur_total_devoluciones` INT DEFAULT 0,
  `tur_notas_apertura` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tur_notas_cierre` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tur_estado` ENUM('ABIERTO','CERRADO','CERRADO_FORZADO') COLLATE utf8mb4_unicode_ci DEFAULT 'ABIERTO',
  `tur_usuario_cierre` INT DEFAULT NULL,
  `tur_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `tur_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tur_turno_id`),
  KEY `idx_tur_tenant` (`tur_tenant_id`),
  KEY `idx_tur_caja` (`tur_caja_id`),
  KEY `idx_tur_usuario` (`tur_usuario_id`),
  KEY `idx_tur_estado` (`tur_tenant_id`, `tur_estado`),
  KEY `idx_tur_fecha_apertura` (`tur_tenant_id`, `tur_fecha_apertura`),
  CONSTRAINT `fk_tur_tenant` FOREIGN KEY (`tur_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tur_caja` FOREIGN KEY (`tur_caja_id`) REFERENCES `store_cajas` (`caj_caja_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Turnos de caja (apertura/cierre)';

-- --------------------------------------------------------
-- store_caja_movimientos - Entradas/salidas manuales de efectivo
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_caja_movimientos`;
CREATE TABLE IF NOT EXISTS `store_caja_movimientos` (
  `cmv_movimiento_id` INT NOT NULL AUTO_INCREMENT,
  `cmv_tenant_id` INT NOT NULL,
  `cmv_turno_id` INT NOT NULL,
  `cmv_tipo` ENUM('ENTRADA','SALIDA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cmv_motivo` ENUM('CAMBIO_MONEDA','PAGO_PROVEEDOR','GASTOS_MENORES','DEPOSITO_BANCO','RETIRO_SEGURIDAD','FONDO_ADICIONAL','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cmv_monto` DECIMAL(12,2) NOT NULL,
  `cmv_descripcion` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cmv_comprobante` VARCHAR(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Foto/doc de respaldo',
  `cmv_autorizado_por` INT DEFAULT NULL,
  `cmv_fecha_movimiento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cmv_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `cmv_usuario_registro` INT DEFAULT NULL,
  PRIMARY KEY (`cmv_movimiento_id`),
  KEY `idx_cmv_tenant` (`cmv_tenant_id`),
  KEY `idx_cmv_turno` (`cmv_turno_id`),
  KEY `idx_cmv_tipo` (`cmv_tenant_id`, `cmv_tipo`),
  KEY `idx_cmv_fecha` (`cmv_tenant_id`, `cmv_fecha_movimiento`),
  CONSTRAINT `fk_cmv_tenant` FOREIGN KEY (`cmv_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cmv_turno` FOREIGN KEY (`cmv_turno_id`) REFERENCES `store_caja_turnos` (`tur_turno_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Movimientos manuales de efectivo en caja';

-- --------------------------------------------------------
-- store_caja_arqueo - Conteo físico de dinero al cierre
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_caja_arqueo`;
CREATE TABLE IF NOT EXISTS `store_caja_arqueo` (
  `arq_arqueo_id` INT NOT NULL AUTO_INCREMENT,
  `arq_tenant_id` INT NOT NULL,
  `arq_turno_id` INT NOT NULL,
  `arq_denominacion` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: 100.00, 50.00, 20.00, 0.25',
  `arq_tipo_denominacion` ENUM('BILLETE','MONEDA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `arq_cantidad` INT NOT NULL DEFAULT 0,
  `arq_valor_unitario` DECIMAL(8,2) NOT NULL,
  `arq_subtotal` DECIMAL(12,2) GENERATED ALWAYS AS (`arq_cantidad` * `arq_valor_unitario`) STORED,
  `arq_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `arq_usuario_registro` INT DEFAULT NULL,
  PRIMARY KEY (`arq_arqueo_id`),
  KEY `idx_arq_tenant` (`arq_tenant_id`),
  KEY `idx_arq_turno` (`arq_turno_id`),
  CONSTRAINT `fk_arq_tenant` FOREIGN KEY (`arq_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_arq_turno` FOREIGN KEY (`arq_turno_id`) REFERENCES `store_caja_turnos` (`tur_turno_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Arqueo/conteo físico de caja al cierre';


-- ============================================================================
-- 5. MÓDULO CLIENTES STORE
-- ============================================================================

-- --------------------------------------------------------
-- store_clientes - Perfil del comprador con fidelización
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_clientes`;
CREATE TABLE IF NOT EXISTS `store_clientes` (
  `cli_cliente_id` INT NOT NULL AUTO_INCREMENT,
  `cli_tenant_id` INT NOT NULL,
  `cli_tipo_identificacion` ENUM('CEDULA','RUC','PASAPORTE','CONSUMIDOR_FINAL') COLLATE utf8mb4_unicode_ci DEFAULT 'CEDULA',
  `cli_identificacion` VARCHAR(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '9999999999999 para CF',
  `cli_nombres` VARCHAR(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cli_apellidos` VARCHAR(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_razon_social` VARCHAR(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Si es empresa',
  `cli_email` VARCHAR(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_telefono` VARCHAR(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_celular` VARCHAR(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_direccion` VARCHAR(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_ciudad` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_provincia` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_fecha_nacimiento` DATE DEFAULT NULL,
  `cli_genero` ENUM('M','F','O') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_tipo_cliente` ENUM('REGULAR','MAYORISTA','VIP','EMPLEADO') COLLATE utf8mb4_unicode_ci DEFAULT 'REGULAR',
  `cli_puntos_acumulados` INT DEFAULT 0,
  `cli_puntos_canjeados` INT DEFAULT 0,
  `cli_puntos_disponibles` INT GENERATED ALWAYS AS (`cli_puntos_acumulados` - `cli_puntos_canjeados`) STORED,
  `cli_total_compras` DECIMAL(12,2) DEFAULT 0.00,
  `cli_cantidad_compras` INT DEFAULT 0,
  `cli_credito_limite` DECIMAL(12,2) DEFAULT 0.00,
  `cli_credito_disponible` DECIMAL(12,2) DEFAULT 0.00,
  `cli_notas` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_acepta_marketing` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `cli_estado` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `cli_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `cli_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cli_usuario_registro` INT DEFAULT NULL,
  `cli_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`cli_cliente_id`),
  UNIQUE KEY `uq_cli_ident_tenant` (`cli_tenant_id`, `cli_identificacion`),
  KEY `idx_cli_tenant` (`cli_tenant_id`),
  KEY `idx_cli_nombres` (`cli_tenant_id`, `cli_nombres`(80)),
  KEY `idx_cli_email` (`cli_tenant_id`, `cli_email`),
  KEY `idx_cli_tipo` (`cli_tenant_id`, `cli_tipo_cliente`),
  KEY `idx_cli_estado` (`cli_tenant_id`, `cli_estado`),
  CONSTRAINT `fk_cli_tenant` FOREIGN KEY (`cli_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Clientes de la tienda con programa de fidelización';

-- --------------------------------------------------------
-- store_cliente_puntos_log - Historial de puntos ganados/canjeados
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_cliente_puntos_log`;
CREATE TABLE IF NOT EXISTS `store_cliente_puntos_log` (
  `cpl_log_id` INT NOT NULL AUTO_INCREMENT,
  `cpl_tenant_id` INT NOT NULL,
  `cpl_cliente_id` INT NOT NULL,
  `cpl_tipo` ENUM('GANADO','CANJEADO','AJUSTE_POSITIVO','AJUSTE_NEGATIVO','EXPIRADO','BONO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpl_puntos` INT NOT NULL COMMENT 'Positivo=ganados, negativo=usados',
  `cpl_saldo_anterior` INT DEFAULT 0,
  `cpl_saldo_posterior` INT DEFAULT 0,
  `cpl_referencia_tipo` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'VENTA, DEVOLUCION, PROMO, etc.',
  `cpl_referencia_id` INT DEFAULT NULL,
  `cpl_descripcion` VARCHAR(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpl_fecha_expiracion` DATE DEFAULT NULL COMMENT 'Fecha en que expiran los puntos',
  `cpl_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `cpl_usuario_registro` INT DEFAULT NULL,
  PRIMARY KEY (`cpl_log_id`),
  KEY `idx_cpl_tenant` (`cpl_tenant_id`),
  KEY `idx_cpl_cliente` (`cpl_cliente_id`),
  KEY `idx_cpl_tipo` (`cpl_tenant_id`, `cpl_tipo`),
  KEY `idx_cpl_fecha` (`cpl_tenant_id`, `cpl_fecha_registro`),
  KEY `idx_cpl_referencia` (`cpl_referencia_tipo`, `cpl_referencia_id`),
  CONSTRAINT `fk_cpl_tenant` FOREIGN KEY (`cpl_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cpl_cliente` FOREIGN KEY (`cpl_cliente_id`) REFERENCES `store_clientes` (`cli_cliente_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de movimientos de puntos de fidelización';


-- ============================================================================
-- 6. MÓDULO CONFIGURACIÓN
-- ============================================================================

-- --------------------------------------------------------
-- store_configuracion - Settings key/value por tenant
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_configuracion`;
CREATE TABLE IF NOT EXISTS `store_configuracion` (
  `cfg_config_id` INT NOT NULL AUTO_INCREMENT,
  `cfg_tenant_id` INT NOT NULL,
  `cfg_clave` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: MONEDA, IVA_PORCENTAJE, TICKET_HEADER',
  `cfg_valor` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cfg_tipo_dato` ENUM('STRING','INT','DECIMAL','BOOLEAN','JSON','TEXT') COLLATE utf8mb4_unicode_ci DEFAULT 'STRING',
  `cfg_descripcion` VARCHAR(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cfg_grupo` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT 'GENERAL' COMMENT 'Agrupación: GENERAL, POS, INVENTARIO, FIDELIZACION',
  `cfg_es_publica` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `cfg_estado` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `cfg_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `cfg_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cfg_usuario_registro` INT DEFAULT NULL,
  `cfg_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`cfg_config_id`),
  UNIQUE KEY `uq_cfg_clave_tenant` (`cfg_tenant_id`, `cfg_clave`),
  KEY `idx_cfg_tenant` (`cfg_tenant_id`),
  KEY `idx_cfg_grupo` (`cfg_tenant_id`, `cfg_grupo`),
  CONSTRAINT `fk_cfg_tenant` FOREIGN KEY (`cfg_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuraciones key/value del módulo Store';

-- --------------------------------------------------------
-- store_impuestos - Tipos de impuesto configurables
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_impuestos`;
CREATE TABLE IF NOT EXISTS `store_impuestos` (
  `imp_impuesto_id` INT NOT NULL AUTO_INCREMENT,
  `imp_tenant_id` INT NOT NULL,
  `imp_codigo_sri` VARCHAR(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código SRI Ecuador',
  `imp_nombre` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: IVA 15%, IVA 0%, ICE',
  `imp_porcentaje` DECIMAL(5,2) NOT NULL COMMENT 'Ej: 15.00, 0.00',
  `imp_tipo` ENUM('IVA','ICE','IRBPNR','OTRO') COLLATE utf8mb4_unicode_ci DEFAULT 'IVA',
  `imp_aplica_por_defecto` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `imp_estado` CHAR(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `imp_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `imp_fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `imp_usuario_registro` INT DEFAULT NULL,
  `imp_usuario_actualizacion` INT DEFAULT NULL,
  PRIMARY KEY (`imp_impuesto_id`),
  KEY `idx_imp_tenant` (`imp_tenant_id`),
  KEY `idx_imp_tipo` (`imp_tenant_id`, `imp_tipo`),
  KEY `idx_imp_estado` (`imp_tenant_id`, `imp_estado`),
  CONSTRAINT `fk_imp_tenant` FOREIGN KEY (`imp_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Impuestos configurables (IVA, ICE, etc.)';


-- ============================================================================
-- DATOS INICIALES DE CONFIGURACIÓN
-- ============================================================================

-- Impuestos por defecto Ecuador (se insertan para tenant 1 como ejemplo)
INSERT INTO `store_impuestos` (`imp_tenant_id`, `imp_codigo_sri`, `imp_nombre`, `imp_porcentaje`, `imp_tipo`, `imp_aplica_por_defecto`) VALUES
(1, '2', 'IVA 15%', 15.00, 'IVA', 'S'),
(1, '0', 'IVA 0%', 0.00, 'IVA', 'N'),
(1, '6', 'No Objeto de Impuesto', 0.00, 'IVA', 'N'),
(1, '7', 'Exento de IVA', 0.00, 'IVA', 'N');

-- Configuraciones iniciales POS
INSERT INTO `store_configuracion` (`cfg_tenant_id`, `cfg_clave`, `cfg_valor`, `cfg_tipo_dato`, `cfg_descripcion`, `cfg_grupo`) VALUES
(1, 'MONEDA', 'USD', 'STRING', 'Moneda principal del sistema', 'GENERAL'),
(1, 'MONEDA_SIMBOLO', '$', 'STRING', 'Símbolo de la moneda', 'GENERAL'),
(1, 'IVA_PORCENTAJE', '15.00', 'DECIMAL', 'Porcentaje de IVA vigente', 'GENERAL'),
(1, 'DECIMALES_PRECIO', '2', 'INT', 'Decimales a mostrar en precios', 'GENERAL'),
(1, 'TICKET_HEADER', 'DigiSports Store', 'STRING', 'Encabezado del ticket de venta', 'POS'),
(1, 'TICKET_FOOTER', 'Gracias por su compra', 'STRING', 'Pie de página del ticket', 'POS'),
(1, 'TICKET_MOSTRAR_IVA', 'S', 'BOOLEAN', 'Mostrar desglose IVA en ticket', 'POS'),
(1, 'STOCK_PERMITIR_NEGATIVO', 'N', 'BOOLEAN', 'Permitir vender sin stock', 'INVENTARIO'),
(1, 'STOCK_ALERTA_MINIMO', '5', 'INT', 'Stock mínimo por defecto para alertas', 'INVENTARIO'),
(1, 'PUNTOS_POR_DOLAR', '1', 'INT', 'Puntos de fidelización por cada dólar', 'FIDELIZACION'),
(1, 'PUNTOS_VALOR_CANJE', '0.01', 'DECIMAL', 'Valor en USD de cada punto canjeado', 'FIDELIZACION'),
(1, 'PUNTOS_EXPIRACION_DIAS', '365', 'INT', 'Días para expiración de puntos', 'FIDELIZACION');


SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- ============================================================================
-- FIN DEL ESQUEMA DigiSports Store POS
-- Total: 28 tablas | 6 módulos
-- ============================================================================
