-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 08-02-2026 a las 03:57:48
-- Versión del servidor: 8.2.0
-- Versión de PHP: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `digisports_core`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `cli_cliente_id` int NOT NULL AUTO_INCREMENT,
  `cli_tenant_id` int NOT NULL,
  `cli_tipo_identificacion` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cli_identificacion` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cli_nombres` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cli_apellidos` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cli_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_celular` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_direccion` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_fecha_nacimiento` date DEFAULT NULL,
  `cli_tipo_cliente` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'PUBLICO',
  `cli_saldo_abono` decimal(10,2) DEFAULT '0.00',
  `cli_estado` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `cli_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cli_cliente_id`),
  UNIQUE KEY `uk_tenant_identificacion` (`cli_tenant_id`,`cli_identificacion`),
  KEY `idx_email` (`cli_email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`cli_cliente_id`, `cli_tenant_id`, `cli_tipo_identificacion`, `cli_identificacion`, `cli_nombres`, `cli_apellidos`, `cli_email`, `cli_telefono`, `cli_celular`, `cli_direccion`, `cli_fecha_nacimiento`, `cli_tipo_cliente`, `cli_saldo_abono`, `cli_estado`, `cli_fecha_registro`) VALUES
(1, 1, 'PAS', 'TMP1769387779', 'Freddy', 'Bolivar Pinzon Olmedo', 'fbpinzon@gmail.com', '0993120984', NULL, NULL, NULL, 'PUBLICO', 0.00, 'A', '2026-01-26 00:36:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_electronicas`
--

DROP TABLE IF EXISTS `facturas_electronicas`;
CREATE TABLE IF NOT EXISTS `facturas_electronicas` (
  `fac_id` int NOT NULL AUTO_INCREMENT,
  `fac_tenant_id` int NOT NULL,
  `fac_factura_id` int DEFAULT NULL,
  `fac_clave_acceso` varchar(49) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fac_tipo_comprobante` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '01' COMMENT '01=Factura, 04=Nota Crédito, 05=Nota Débito, 06=Guía Remisión, 07=Retención',
  `fac_establecimiento` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fac_punto_emision` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fac_secuencial` char(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fac_fecha_emision` date NOT NULL,
  `fac_cliente_id` int DEFAULT NULL,
  `fac_cliente_tipo_identificacion` char(2) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '04=RUC, 05=Cédula, 06=Pasaporte, 07=Cons.Final',
  `fac_cliente_identificacion` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fac_cliente_razon_social` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fac_cliente_direccion` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fac_cliente_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fac_cliente_telefono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fac_subtotal_iva` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'Subtotal con IVA',
  `fac_subtotal_0` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'Subtotal 0%',
  `fac_subtotal_no_objeto` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'Subtotal no objeto de IVA',
  `fac_subtotal_exento` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'Subtotal exento',
  `fac_subtotal` decimal(14,2) NOT NULL DEFAULT '0.00',
  `fac_descuento` decimal(14,2) NOT NULL DEFAULT '0.00',
  `fac_iva` decimal(14,2) NOT NULL DEFAULT '0.00',
  `fac_ice` decimal(14,2) NOT NULL DEFAULT '0.00',
  `fac_irbpnr` decimal(14,2) NOT NULL DEFAULT '0.00',
  `fac_propina` decimal(14,2) NOT NULL DEFAULT '0.00',
  `fac_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `fac_estado_sri` enum('PENDIENTE','GENERADA','FIRMADA','ENVIADA','RECIBIDA','DEVUELTA','AUTORIZADO','NO_AUTORIZADO','ERROR','ANULADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `fac_ambiente` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1=Pruebas, 2=Producción',
  `fac_tipo_emision` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1=Normal, 2=Contingencia',
  `fac_xml_generado` text COLLATE utf8mb4_unicode_ci COMMENT 'Ruta al archivo XML generado',
  `fac_xml_firmado` text COLLATE utf8mb4_unicode_ci COMMENT 'Ruta al archivo XML firmado',
  `fac_xml_autorizado` text COLLATE utf8mb4_unicode_ci COMMENT 'Ruta al archivo XML autorizado',
  `fac_numero_autorizacion` varchar(49) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fac_fecha_autorizacion` datetime DEFAULT NULL,
  `fac_mensaje_error` text COLLATE utf8mb4_unicode_ci,
  `fac_intentos_envio` int NOT NULL DEFAULT '0',
  `fac_ultimo_intento` datetime DEFAULT NULL,
  `fac_observaciones` text COLLATE utf8mb4_unicode_ci,
  `fac_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fac_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL COMMENT 'Referencia a usuarios.usuario_id',
  PRIMARY KEY (`fac_id`),
  UNIQUE KEY `clave_acceso` (`fac_clave_acceso`),
  KEY `idx_tenant` (`fac_tenant_id`),
  KEY `idx_factura` (`fac_factura_id`),
  KEY `idx_clave_acceso` (`fac_clave_acceso`),
  KEY `idx_fecha` (`fac_fecha_emision`),
  KEY `idx_estado` (`fac_estado_sri`),
  KEY `idx_cliente_identificacion` (`fac_cliente_identificacion`),
  KEY `idx_numero_completo` (`fac_establecimiento`,`fac_punto_emision`,`fac_secuencial`),
  KEY `idx_autorizacion` (`fac_numero_autorizacion`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_fe_tenant_fecha` (`fac_tenant_id`,`fac_fecha_emision`),
  KEY `idx_fe_tenant_estado_fecha` (`fac_tenant_id`,`fac_estado_sri`,`fac_fecha_emision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Facturas electrónicas emitidas al SRI';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_electronicas_detalle`
--

DROP TABLE IF EXISTS `facturas_electronicas_detalle`;
CREATE TABLE IF NOT EXISTS `facturas_electronicas_detalle` (
  `det_id` int NOT NULL AUTO_INCREMENT,
  `det_factura_electronica_id` int NOT NULL,
  `det_codigo_principal` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código interno',
  `det_codigo_auxiliar` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código barras, etc.',
  `det_descripcion` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `det_cantidad` decimal(14,6) NOT NULL,
  `det_precio_unitario` decimal(14,6) NOT NULL,
  `det_descuento` decimal(14,2) NOT NULL DEFAULT '0.00',
  `det_precio_total_sin_impuesto` decimal(14,2) NOT NULL,
  `det_producto_id` int DEFAULT NULL,
  `det_servicio_id` int DEFAULT NULL,
  `det_instalacion_id` int DEFAULT NULL,
  `det_reserva_id` int DEFAULT NULL,
  `det_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`det_id`),
  KEY `idx_factura` (`det_factura_electronica_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalles de facturas electrónicas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_electronicas_detalle_impuestos`
--

DROP TABLE IF EXISTS `facturas_electronicas_detalle_impuestos`;
CREATE TABLE IF NOT EXISTS `facturas_electronicas_detalle_impuestos` (
  `imp_id` int NOT NULL AUTO_INCREMENT,
  `imp_detalle_id` int NOT NULL,
  `imp_codigo` char(1) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '2=IVA, 3=ICE, 5=IRBPNR',
  `imp_codigo_porcentaje` char(4) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código tarifa: 0, 2, 3, 4, 6, 7, 8',
  `imp_tarifa` decimal(5,2) NOT NULL COMMENT 'Porcentaje: 0, 12, 14, 15, etc.',
  `imp_base_imponible` decimal(14,2) NOT NULL,
  `imp_valor` decimal(14,2) NOT NULL,
  PRIMARY KEY (`imp_id`),
  KEY `idx_detalle` (`imp_detalle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Impuestos por detalle de factura electrónica';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_electronicas_info_adicional`
--

DROP TABLE IF EXISTS `facturas_electronicas_info_adicional`;
CREATE TABLE IF NOT EXISTS `facturas_electronicas_info_adicional` (
  `adi_id` int NOT NULL AUTO_INCREMENT,
  `adi_factura_electronica_id` int NOT NULL,
  `adi_nombre` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adi_valor` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`adi_id`),
  KEY `idx_factura` (`adi_factura_electronica_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Información adicional de facturas electrónicas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_electronicas_log`
--

DROP TABLE IF EXISTS `facturas_electronicas_log`;
CREATE TABLE IF NOT EXISTS `facturas_electronicas_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `log_factura_electronica_id` int DEFAULT NULL,
  `log_clave_acceso` varchar(49) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_accion` enum('GENERAR','FIRMAR','ENVIAR','CONSULTAR','REENVIAR','ANULAR') COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_endpoint` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_request_data` longtext COLLATE utf8mb4_unicode_ci,
  `log_response_data` longtext COLLATE utf8mb4_unicode_ci,
  `log_estado_respuesta` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_codigo_error` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_mensaje_error` text COLLATE utf8mb4_unicode_ci,
  `log_duracion_ms` int DEFAULT NULL COMMENT 'Tiempo de respuesta en milisegundos',
  `log_ip_origen` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `log_created_by` int DEFAULT NULL COMMENT 'Referencia a usuarios.usuario_id',
  PRIMARY KEY (`log_id`),
  KEY `idx_factura` (`log_factura_electronica_id`),
  KEY `idx_clave_acceso` (`log_clave_acceso`),
  KEY `idx_accion` (`log_accion`),
  KEY `idx_fecha` (`log_created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de comunicaciones con SRI';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_electronicas_pagos`
--

DROP TABLE IF EXISTS `facturas_electronicas_pagos`;
CREATE TABLE IF NOT EXISTS `facturas_electronicas_pagos` (
  `pag_id` int NOT NULL AUTO_INCREMENT,
  `pag_factura_electronica_id` int NOT NULL,
  `pag_forma_pago` char(2) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '01=Efectivo, 16=Tarjeta Débito, etc.',
  `pag_total` decimal(14,2) NOT NULL,
  `pag_plazo` int DEFAULT NULL COMMENT 'Plazo en días/meses',
  `pag_unidad_tiempo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'dias',
  PRIMARY KEY (`pag_id`),
  KEY `idx_factura` (`pag_factura_electronica_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Formas de pago de facturas electrónicas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_electronicas_secuenciales`
--

DROP TABLE IF EXISTS `facturas_electronicas_secuenciales`;
CREATE TABLE IF NOT EXISTS `facturas_electronicas_secuenciales` (
  `sec_id` int NOT NULL AUTO_INCREMENT,
  `sec_tenant_id` int NOT NULL,
  `sec_tipo_comprobante` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '01',
  `sec_establecimiento` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sec_punto_emision` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sec_secuencial_actual` int NOT NULL DEFAULT '0',
  `sec_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sec_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sec_id`),
  UNIQUE KEY `uk_secuencial` (`sec_tenant_id`,`sec_tipo_comprobante`,`sec_establecimiento`,`sec_punto_emision`),
  KEY `idx_tenant` (`sec_tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Control de secuenciales por establecimiento';

--
-- Volcado de datos para la tabla `facturas_electronicas_secuenciales`
--

INSERT INTO `facturas_electronicas_secuenciales` (`sec_id`, `sec_tenant_id`, `sec_tipo_comprobante`, `sec_establecimiento`, `sec_punto_emision`, `sec_secuencial_actual`, `sec_created_at`, `sec_updated_at`) VALUES
(1, 1, '01', '001', '001', 0, '2026-01-26 03:47:23', '2026-01-26 03:47:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_suscripcion`
--

DROP TABLE IF EXISTS `facturas_suscripcion`;
CREATE TABLE IF NOT EXISTS `facturas_suscripcion` (
  `sus_factura_id` int NOT NULL AUTO_INCREMENT,
  `sus_tenant_id` int NOT NULL,
  `sus_periodo` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sus_tipo_factura` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'MENSUAL',
  `sus_subtotal` decimal(10,2) NOT NULL,
  `sus_descuento` decimal(10,2) DEFAULT '0.00',
  `sus_iva` decimal(10,2) NOT NULL,
  `sus_total` decimal(10,2) NOT NULL,
  `sus_plan_nombre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sus_usuarios_cobrados` int DEFAULT NULL,
  `sus_sedes_cobradas` int DEFAULT NULL,
  `sus_modulos_adicionales` json DEFAULT NULL,
  `sus_fecha_emision` date NOT NULL,
  `sus_fecha_vencimiento` date NOT NULL,
  `sus_fecha_pago` date DEFAULT NULL,
  `sus_metodo_pago` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sus_referencia_pago` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sus_comprobante_pago` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sus_numero_autorizacion` varchar(49) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sus_clave_acceso` varchar(49) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sus_xml_firmado` text COLLATE utf8mb4_unicode_ci,
  `sus_estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `sus_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sus_factura_id`),
  KEY `idx_tenant_periodo` (`sus_tenant_id`,`sus_periodo`),
  KEY `idx_estado` (`sus_estado`),
  KEY `idx_vencimiento` (`sus_fecha_vencimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones`
--

DROP TABLE IF EXISTS `instalaciones`;
CREATE TABLE IF NOT EXISTS `instalaciones` (
  `ins_instalacion_id` int NOT NULL AUTO_INCREMENT,
  `ins_tenant_id` int NOT NULL,
  `ins_sede_id` int NOT NULL,
  `ins_tipo_instalacion_id` int NOT NULL,
  `ins_codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ins_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ins_descripcion` text COLLATE utf8mb4_unicode_ci,
  `ins_superficie` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ins_dimensiones` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ins_capacidad_personas` int DEFAULT NULL,
  `ins_tiene_iluminacion` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `ins_tiene_graderias` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `ins_tiene_vestuarios` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `ins_tiene_duchas` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `ins_duracion_minima_minutos` int DEFAULT '60',
  `ins_duracion_maxima_minutos` int DEFAULT '120',
  `ins_tiempo_anticipacion_dias` int DEFAULT '30',
  `ins_permite_reserva_recurrente` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `ins_foto_principal` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ins_galeria_fotos` json DEFAULT NULL,
  `ins_estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `ins_motivo_inactivacion` text COLLATE utf8mb4_unicode_ci,
  `ins_fecha_inicio_inactivacion` datetime DEFAULT NULL,
  `ins_fecha_fin_inactivacion` datetime DEFAULT NULL,
  `ins_orden_visualizacion` int DEFAULT '0',
  `ins_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ins_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ins_usuario_registro` int DEFAULT NULL,
  PRIMARY KEY (`ins_instalacion_id`),
  UNIQUE KEY `uk_tenant_codigo` (`ins_tenant_id`,`ins_codigo`),
  KEY `sede_id` (`ins_sede_id`),
  KEY `tipo_instalacion_id` (`ins_tipo_instalacion_id`),
  KEY `idx_tenant_sede` (`ins_tenant_id`,`ins_sede_id`),
  KEY `idx_estado` (`ins_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `instalaciones`
--

INSERT INTO `instalaciones` (`ins_instalacion_id`, `ins_tenant_id`, `ins_sede_id`, `ins_tipo_instalacion_id`, `ins_codigo`, `ins_nombre`, `ins_descripcion`, `ins_superficie`, `ins_dimensiones`, `ins_capacidad_personas`, `ins_tiene_iluminacion`, `ins_tiene_graderias`, `ins_tiene_vestuarios`, `ins_tiene_duchas`, `ins_duracion_minima_minutos`, `ins_duracion_maxima_minutos`, `ins_tiempo_anticipacion_dias`, `ins_permite_reserva_recurrente`, `ins_foto_principal`, `ins_galeria_fotos`, `ins_estado`, `ins_motivo_inactivacion`, `ins_fecha_inicio_inactivacion`, `ins_fecha_fin_inactivacion`, `ins_orden_visualizacion`, `ins_fecha_registro`, `ins_fecha_actualizacion`, `ins_usuario_registro`) VALUES
(2, 1, 1, 2, 'INS001', 'Complejo Norte', 'Complejo deportivo zona norte', 'Césped sintético', '100x60', 200, 'S', 'S', 'S', 'S', 60, 180, 7, 'S', NULL, NULL, 'ACTIVO', NULL, NULL, NULL, 0, '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1),
(3, 1, 1, 2, 'INS002', 'Complejo Sur', 'Complejo deportivo zona sur', 'Césped natural', '90x50', 150, 'S', 'S', 'S', 'S', 60, 180, 7, 'S', NULL, NULL, 'ACTIVO', NULL, NULL, NULL, 0, '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1),
(4, 1, 1, 2, 'INS003', 'Cancha Central', 'Cancha principal techada', 'Indoor', '40x20', 100, 'S', 'S', 'S', 'S', 60, 180, 7, 'S', NULL, NULL, 'ACTIVO', NULL, NULL, NULL, 0, '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_abonos`
--

DROP TABLE IF EXISTS `instalaciones_abonos`;
CREATE TABLE IF NOT EXISTS `instalaciones_abonos` (
  `abo_abono_id` int NOT NULL AUTO_INCREMENT,
  `abo_tenant_id` int NOT NULL,
  `abo_cliente_id` int NOT NULL,
  `abo_monto_total` decimal(10,2) NOT NULL,
  `abo_monto_utilizado` decimal(10,2) DEFAULT '0.00',
  `abo_saldo_disponible` decimal(10,2) NOT NULL,
  `abo_fecha_compra` date NOT NULL,
  `abo_fecha_vencimiento` date NOT NULL,
  `abo_forma_pago` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `abo_estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `abo_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`abo_abono_id`),
  KEY `tenant_id` (`abo_tenant_id`),
  KEY `idx_cliente` (`abo_cliente_id`),
  KEY `idx_vencimiento` (`abo_fecha_vencimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_canchas`
--

DROP TABLE IF EXISTS `instalaciones_canchas`;
CREATE TABLE IF NOT EXISTS `instalaciones_canchas` (
  `can_cancha_id` int NOT NULL AUTO_INCREMENT,
  `can_tenant_id` int NOT NULL,
  `can_instalacion_id` int NOT NULL,
  `can_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `can_tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'futbol, tenis, padel, voleibol, basquetbol, piscina, gimnasio, otro',
  `can_descripcion` text COLLATE utf8mb4_unicode_ci,
  `can_capacidad_maxima` int NOT NULL DEFAULT '0',
  `can_ancho` decimal(8,2) DEFAULT NULL COMMENT 'Ancho en metros',
  `can_largo` decimal(8,2) DEFAULT NULL COMMENT 'Largo en metros',
  `can_estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO' COMMENT 'ACTIVO, INACTIVO, ELIMINADA',
  `can_fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `can_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `can_usuario_creacion` int DEFAULT NULL,
  `can_usuario_actualizacion` int DEFAULT NULL,
  PRIMARY KEY (`can_cancha_id`),
  UNIQUE KEY `uk_tenant_nombre` (`can_tenant_id`,`can_nombre`),
  KEY `idx_tenant` (`can_tenant_id`),
  KEY `idx_instalacion` (`can_instalacion_id`),
  KEY `idx_tipo` (`can_tipo`),
  KEY `idx_estado` (`can_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Canchas/espacios deportivos especÃ­ficos dentro de una instalaciÃ³n';

--
-- Volcado de datos para la tabla `instalaciones_canchas`
--

INSERT INTO `instalaciones_canchas` (`can_cancha_id`, `can_tenant_id`, `can_instalacion_id`, `can_nombre`, `can_tipo`, `can_descripcion`, `can_capacidad_maxima`, `can_ancho`, `can_largo`, `can_estado`, `can_fecha_creacion`, `can_fecha_actualizacion`, `can_usuario_creacion`, `can_usuario_actualizacion`) VALUES
(1, 1, 2, 'Cancha Fútbol 1 - Complejo Norte', 'futbol', 'Cancha de fútbol profesional', 22, 25.00, 50.00, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-26 00:26:29', 1, NULL),
(2, 1, 2, 'Cancha Básquet - Complejo Norte', 'BASQUET', 'Cancha de baloncesto', 10, 25.00, 50.00, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1, NULL),
(3, 1, 2, 'Cancha Tenis - Complejo Norte', 'TENIS', 'Cancha de tenis individual', 4, 25.00, 50.00, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1, NULL),
(4, 1, 3, 'Cancha Fútbol 1 - Complejo Sur', 'FUTBOL', 'Cancha de fútbol profesional', 22, 25.00, 50.00, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1, NULL),
(5, 1, 3, 'Cancha Básquet - Complejo Sur', 'basquetbol', 'Cancha de baloncesto', 10, 25.00, 50.00, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-26 00:26:12', 1, NULL),
(6, 1, 3, 'Cancha Tenis - Complejo Sur', 'TENIS', 'Cancha de tenis individual', 4, 25.00, 50.00, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1, NULL),
(7, 1, 4, 'Cancha Fútbol 1 - Cancha Central', 'FUTBOL', 'Cancha de fútbol profesional', 22, 25.00, 50.00, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1, NULL),
(8, 1, 4, 'Cancha de Básquet - Coliseo Ciudad de Loja', 'basquetbol', 'Cancha de baloncesto', 10, 25.00, 50.00, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:56:42', 1, NULL),
(9, 1, 4, 'Cancha Tenis - Cancha Central', 'TENIS', 'Cancha de tenis individual', 4, 25.00, 50.00, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_disponibilidad_canchas`
--

DROP TABLE IF EXISTS `instalaciones_disponibilidad_canchas`;
CREATE TABLE IF NOT EXISTS `instalaciones_disponibilidad_canchas` (
  `dis_disponibilidad_id` int NOT NULL AUTO_INCREMENT,
  `dis_cancha_id` int NOT NULL,
  `dis_fecha` date NOT NULL,
  `dis_hora_inicio` time NOT NULL,
  `dis_hora_fin` time NOT NULL,
  `dis_disponible` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S' COMMENT 'S=Disponible, N=No disponible',
  `dis_motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mantenimiento, Reservada, Evento, etc',
  `dis_fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dis_disponibilidad_id`),
  UNIQUE KEY `uk_disp_unica` (`dis_cancha_id`,`dis_fecha`,`dis_hora_inicio`,`dis_hora_fin`),
  KEY `idx_cancha_fecha` (`dis_cancha_id`,`dis_fecha`),
  KEY `idx_disponible` (`dis_disponible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cache de disponibilidad para bÃºsquedas rÃ¡pidas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_eventos_canchas`
--

DROP TABLE IF EXISTS `instalaciones_eventos_canchas`;
CREATE TABLE IF NOT EXISTS `instalaciones_eventos_canchas` (
  `eve_evento_id` int NOT NULL AUTO_INCREMENT,
  `eve_cancha_id` int NOT NULL,
  `eve_tipo_evento` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'MANTENIMIENTO, RESERVA, EVENTO, BLOQUEO, ESTADO_CAMBIO',
  `eve_descripcion` text COLLATE utf8mb4_unicode_ci,
  `eve_referencia_id` int DEFAULT NULL COMMENT 'ID de mantenimiento, reserva, etc',
  `eve_usuario_id` int DEFAULT NULL,
  `eve_fecha_evento` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`eve_evento_id`),
  KEY `fk_evento_usuario` (`eve_usuario_id`),
  KEY `idx_cancha` (`eve_cancha_id`),
  KEY `idx_tipo_evento` (`eve_tipo_evento`),
  KEY `idx_fecha_evento` (`eve_fecha_evento`),
  KEY `idx_referencia` (`eve_referencia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de auditorÃ­a de eventos en canchas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_instalacion_bloqueos`
--

DROP TABLE IF EXISTS `instalaciones_instalacion_bloqueos`;
CREATE TABLE IF NOT EXISTS `instalaciones_instalacion_bloqueos` (
  `blo_bloqueo_id` int NOT NULL AUTO_INCREMENT,
  `blo_instalacion_id` int NOT NULL,
  `blo_tipo_bloqueo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `blo_fecha_inicio` datetime NOT NULL,
  `blo_fecha_fin` datetime NOT NULL,
  `blo_motivo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `blo_es_recurrente` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `blo_recurrencia_config` json DEFAULT NULL,
  `blo_usuario_registro` int DEFAULT NULL,
  `blo_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`blo_bloqueo_id`),
  KEY `instalacion_id` (`blo_instalacion_id`),
  KEY `idx_fechas` (`blo_fecha_inicio`,`blo_fecha_fin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_instalacion_horarios`
--

DROP TABLE IF EXISTS `instalaciones_instalacion_horarios`;
CREATE TABLE IF NOT EXISTS `instalaciones_instalacion_horarios` (
  `hor_horario_id` int NOT NULL AUTO_INCREMENT,
  `hor_instalacion_id` int NOT NULL,
  `hor_dia_semana` tinyint NOT NULL,
  `hor_hora_apertura` time NOT NULL,
  `hor_hora_cierre` time NOT NULL,
  `hor_estado` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  PRIMARY KEY (`hor_horario_id`),
  UNIQUE KEY `uk_instalacion_dia` (`hor_instalacion_id`,`hor_dia_semana`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_instalacion_tarifas`
--

DROP TABLE IF EXISTS `instalaciones_instalacion_tarifas`;
CREATE TABLE IF NOT EXISTS `instalaciones_instalacion_tarifas` (
  `tar_tarifa_id` int NOT NULL AUTO_INCREMENT,
  `tar_instalacion_id` int NOT NULL,
  `tar_nombre_tarifa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tar_tipo_cliente` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tar_aplica_dia` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tar_hora_inicio` time DEFAULT NULL,
  `tar_hora_fin` time DEFAULT NULL,
  `tar_precio_por_hora` decimal(10,2) NOT NULL,
  `tar_precio_minimo` decimal(10,2) DEFAULT NULL,
  `tar_descuento_porcentaje` decimal(5,2) DEFAULT '0.00',
  `tar_fecha_inicio_vigencia` date NOT NULL,
  `tar_fecha_fin_vigencia` date DEFAULT NULL,
  `tar_prioridad` int DEFAULT '0',
  `tar_estado` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `tar_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tar_tarifa_id`),
  KEY `instalacion_id` (`tar_instalacion_id`),
  KEY `idx_vigencia` (`tar_fecha_inicio_vigencia`,`tar_fecha_fin_vigencia`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `instalaciones_instalacion_tarifas`
--

INSERT INTO `instalaciones_instalacion_tarifas` (`tar_tarifa_id`, `tar_instalacion_id`, `tar_nombre_tarifa`, `tar_tipo_cliente`, `tar_aplica_dia`, `tar_hora_inicio`, `tar_hora_fin`, `tar_precio_por_hora`, `tar_precio_minimo`, `tar_descuento_porcentaje`, `tar_fecha_inicio_vigencia`, `tar_fecha_fin_vigencia`, `tar_prioridad`, `tar_estado`, `tar_fecha_registro`) VALUES
(1, 2, 'Tarifa Normal', 'PUBLICO', 'LUNES-VIERNES', '06:00:00', '18:00:00', 25.00, NULL, 0.00, '2026-01-25', NULL, 0, 'A', '2026-01-25 23:07:00'),
(2, 2, 'Tarifa Nocturna', 'PUBLICO', 'LUNES-VIERNES', '18:00:00', '22:00:00', 35.00, NULL, 0.00, '2026-01-25', NULL, 0, 'A', '2026-01-25 23:07:00'),
(3, 2, 'Tarifa Fin Semana', 'PUBLICO', 'SABADO-DOMINGO', '06:00:00', '22:00:00', 40.00, NULL, 0.00, '2026-01-25', NULL, 0, 'A', '2026-01-25 23:07:00'),
(4, 3, 'Tarifa Normal', 'PUBLICO', 'LUNES-VIERNES', '06:00:00', '18:00:00', 25.00, NULL, 0.00, '2026-01-25', NULL, 0, 'A', '2026-01-25 23:07:00'),
(5, 3, 'Tarifa Nocturna', 'PUBLICO', 'LUNES-VIERNES', '18:00:00', '22:00:00', 35.00, NULL, 0.00, '2026-01-25', NULL, 0, 'A', '2026-01-25 23:07:00'),
(6, 3, 'Tarifa Fin Semana', 'PUBLICO', 'SABADO-DOMINGO', '06:00:00', '22:00:00', 40.00, NULL, 0.00, '2026-01-25', NULL, 0, 'A', '2026-01-25 23:07:00'),
(7, 4, 'Tarifa Normal', 'PUBLICO', 'LUNES-VIERNES', '06:00:00', '18:00:00', 25.00, NULL, 0.00, '2026-01-25', NULL, 0, 'A', '2026-01-25 23:07:00'),
(8, 4, 'Tarifa Nocturna', 'PUBLICO', 'LUNES-VIERNES', '18:00:00', '22:00:00', 35.00, NULL, 0.00, '2026-01-25', NULL, 0, 'A', '2026-01-25 23:07:00'),
(9, 4, 'Tarifa Fin Semana', 'PUBLICO', 'SABADO-DOMINGO', '06:00:00', '22:00:00', 40.00, NULL, 0.00, '2026-01-25', NULL, 0, 'A', '2026-01-25 23:07:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_mantenimientos`
--

DROP TABLE IF EXISTS `instalaciones_mantenimientos`;
CREATE TABLE IF NOT EXISTS `instalaciones_mantenimientos` (
  `man_mantenimiento_id` int NOT NULL AUTO_INCREMENT,
  `man_tenant_id` int NOT NULL,
  `man_cancha_id` int NOT NULL,
  `man_tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'preventivo, correctivo, limpieza, reparacion, inspeccion, otra',
  `man_descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `man_notas` text COLLATE utf8mb4_unicode_ci,
  `man_fecha_inicio` datetime NOT NULL,
  `man_fecha_fin` datetime NOT NULL,
  `man_responsable_id` int DEFAULT NULL,
  `man_recurrir` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT 'NO' COMMENT 'SI o NO',
  `man_cadencia_recurrencia` int DEFAULT NULL COMMENT 'Cada cuÃ¡ntos dÃ­as repetir',
  `man_estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'PROGRAMADO' COMMENT 'PROGRAMADO, EN_PROGRESO, COMPLETADO, CANCELADO',
  `man_fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `man_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `man_usuario_creacion` int DEFAULT NULL,
  `man_usuario_actualizacion` int DEFAULT NULL,
  PRIMARY KEY (`man_mantenimiento_id`),
  KEY `idx_tenant` (`man_tenant_id`),
  KEY `idx_cancha` (`man_cancha_id`),
  KEY `idx_fechas` (`man_fecha_inicio`,`man_fecha_fin`),
  KEY `idx_estado` (`man_estado`),
  KEY `idx_tipo` (`man_tipo`),
  KEY `idx_responsable` (`man_responsable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ProgramaciÃ³n de mantenimiento preventivo y correctivo de canchas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_reservas`
--

DROP TABLE IF EXISTS `instalaciones_reservas`;
CREATE TABLE IF NOT EXISTS `instalaciones_reservas` (
  `res_reserva_id` int NOT NULL AUTO_INCREMENT,
  `res_tenant_id` int NOT NULL,
  `res_instalacion_id` int NOT NULL,
  `res_cliente_id` int NOT NULL,
  `res_fecha_reserva` date NOT NULL,
  `res_hora_inicio` time NOT NULL,
  `res_hora_fin` time NOT NULL,
  `res_duracion_minutos` int NOT NULL,
  `res_es_recurrente` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `res_reserva_padre_id` int DEFAULT NULL,
  `res_recurrencia_config` json DEFAULT NULL,
  `res_tarifa_aplicada_id` int DEFAULT NULL,
  `res_precio_base` decimal(10,2) NOT NULL,
  `res_descuento_monto` decimal(10,2) DEFAULT '0.00',
  `res_precio_total` decimal(10,2) NOT NULL,
  `res_abono_utilizado` decimal(10,2) DEFAULT '0.00',
  `res_estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `res_requiere_confirmacion` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `res_fecha_confirmacion` datetime DEFAULT NULL,
  `res_observaciones` text COLLATE utf8mb4_unicode_ci,
  `res_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `res_usuario_registro` int DEFAULT NULL,
  PRIMARY KEY (`res_reserva_id`),
  KEY `instalacion_id` (`res_instalacion_id`),
  KEY `cliente_id` (`res_cliente_id`),
  KEY `reserva_padre_id` (`res_reserva_padre_id`),
  KEY `idx_tenant_instalacion` (`res_tenant_id`,`res_instalacion_id`),
  KEY `idx_fecha_reserva` (`res_fecha_reserva`),
  KEY `idx_estado` (`res_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `instalaciones_reservas`
--

INSERT INTO `instalaciones_reservas` (`res_reserva_id`, `res_tenant_id`, `res_instalacion_id`, `res_cliente_id`, `res_fecha_reserva`, `res_hora_inicio`, `res_hora_fin`, `res_duracion_minutos`, `res_es_recurrente`, `res_reserva_padre_id`, `res_recurrencia_config`, `res_tarifa_aplicada_id`, `res_precio_base`, `res_descuento_monto`, `res_precio_total`, `res_abono_utilizado`, `res_estado`, `res_requiere_confirmacion`, `res_fecha_confirmacion`, `res_observaciones`, `res_fecha_registro`, `res_usuario_registro`) VALUES
(1, 1, 4, 1, '2026-01-26', '06:00:00', '07:00:00', 60, 'N', NULL, NULL, 3, 15.00, 0.00, 15.00, 0.00, 'PENDIENTE', 'S', NULL, '', '2026-01-26 00:36:19', 1),
(2, 1, 4, 1, '2026-01-26', '12:00:00', '13:00:00', 60, 'N', NULL, NULL, 4, 16.00, 0.00, 16.00, 0.00, 'PENDIENTE', 'S', NULL, 'ok', '2026-01-26 00:40:15', 1),
(3, 1, 4, 1, '2026-01-26', '07:00:00', '08:00:00', 60, 'N', NULL, NULL, 3, 15.00, 0.00, 15.00, 0.00, 'PENDIENTE', 'S', NULL, 'ok', '2026-01-26 03:23:02', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_reserva_pagos`
--

DROP TABLE IF EXISTS `instalaciones_reserva_pagos`;
CREATE TABLE IF NOT EXISTS `instalaciones_reserva_pagos` (
  `pag_pago_id` int NOT NULL AUTO_INCREMENT,
  `pag_tenant_id` int NOT NULL,
  `pag_reserva_id` int NOT NULL,
  `pag_monto` decimal(10,2) NOT NULL,
  `pag_tipo_pago` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pag_forma_pago` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pag_referencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pag_pasarela` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pag_transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pag_estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'COMPLETADO',
  `pag_fecha_pago` datetime DEFAULT CURRENT_TIMESTAMP,
  `pag_usuario_registro` int DEFAULT NULL,
  PRIMARY KEY (`pag_pago_id`),
  KEY `tenant_id` (`pag_tenant_id`),
  KEY `idx_reserva` (`pag_reserva_id`),
  KEY `idx_fecha` (`pag_fecha_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_sedes`
--

DROP TABLE IF EXISTS `instalaciones_sedes`;
CREATE TABLE IF NOT EXISTS `instalaciones_sedes` (
  `sed_sede_id` int NOT NULL AUTO_INCREMENT,
  `sed_tenant_id` int NOT NULL,
  `sed_codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sed_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sed_descripcion` text COLLATE utf8mb4_unicode_ci,
  `sed_direccion` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sed_ciudad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sed_provincia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sed_pais` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Ecuador',
  `sed_latitud` decimal(10,8) DEFAULT NULL,
  `sed_longitud` decimal(11,8) DEFAULT NULL,
  `sed_telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sed_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sed_horario_apertura` time DEFAULT NULL,
  `sed_horario_cierre` time DEFAULT NULL,
  `sed_dias_atencion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'LUNES-DOMINGO',
  `sed_superficie_total` decimal(10,2) DEFAULT NULL,
  `sed_capacidad_total` int DEFAULT NULL,
  `sed_estacionamiento` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `sed_cafeteria` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `sed_tienda` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `sed_foto_principal` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sed_galeria` json DEFAULT NULL,
  `sed_es_principal` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `sed_estado` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `sed_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sed_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sed_sede_id`),
  UNIQUE KEY `uk_tenant_codigo` (`sed_tenant_id`,`sed_codigo`),
  KEY `idx_tenant` (`sed_tenant_id`),
  KEY `idx_ciudad` (`sed_ciudad`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `instalaciones_sedes`
--

INSERT INTO `instalaciones_sedes` (`sed_sede_id`, `sed_tenant_id`, `sed_codigo`, `sed_nombre`, `sed_descripcion`, `sed_direccion`, `sed_ciudad`, `sed_provincia`, `sed_pais`, `sed_latitud`, `sed_longitud`, `sed_telefono`, `sed_email`, `sed_horario_apertura`, `sed_horario_cierre`, `sed_dias_atencion`, `sed_superficie_total`, `sed_capacidad_total`, `sed_estacionamiento`, `sed_cafeteria`, `sed_tienda`, `sed_foto_principal`, `sed_galeria`, `sed_es_principal`, `sed_estado`, `sed_fecha_registro`, `sed_fecha_actualizacion`) VALUES
(1, 1, 'CENTRAL', 'Sede Central', NULL, 'Av. Principal 123', 'Quito', 'Pichincha', 'Ecuador', NULL, NULL, NULL, NULL, NULL, NULL, 'LUNES-DOMINGO', NULL, NULL, 'S', 'N', 'N', NULL, NULL, 'S', 'A', '2026-01-25 00:35:10', '2026-01-25 00:35:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_tipos_instalacion`
--

DROP TABLE IF EXISTS `instalaciones_tipos_instalacion`;
CREATE TABLE IF NOT EXISTS `instalaciones_tipos_instalacion` (
  `tip_tipo_id` int NOT NULL AUTO_INCREMENT,
  `tip_tenant_id` int NOT NULL,
  `tip_codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tip_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tip_descripcion` text COLLATE utf8mb4_unicode_ci,
  `tip_icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'fa-futbol',
  `tip_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#28a745',
  `tip_requiere_equipamiento` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `tip_permite_reserva_online` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `tip_estado` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `tip_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tip_tipo_id`),
  UNIQUE KEY `uk_tenant_codigo` (`tip_tenant_id`,`tip_codigo`),
  KEY `idx_estado` (`tip_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `instalaciones_tipos_instalacion`
--

INSERT INTO `instalaciones_tipos_instalacion` (`tip_tipo_id`, `tip_tenant_id`, `tip_codigo`, `tip_nombre`, `tip_descripcion`, `tip_icono`, `tip_color`, `tip_requiere_equipamiento`, `tip_permite_reserva_online`, `tip_estado`, `tip_fecha_registro`) VALUES
(1, 1, 'FUTBOL', 'Cancha de Fútbol', 'Canchas para fútbol', 'fa-futbol', '#28a745', 'N', 'S', 'A', '2026-01-25 23:07:00'),
(2, 1, 'BASQUET', 'Cancha de Básquet', 'Canchas para baloncesto', 'fa-basketball-ball', '#fd7e14', 'N', 'S', 'A', '2026-01-25 23:07:00'),
(3, 1, 'TENIS', 'Cancha de Tenis', 'Canchas para tenis', 'fa-table-tennis', '#17a2b8', 'N', 'S', 'A', '2026-01-25 23:07:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_auditoria`
--

DROP TABLE IF EXISTS `seguridad_auditoria`;
CREATE TABLE IF NOT EXISTS `seguridad_auditoria` (
  `aud_auditoria_id` bigint NOT NULL AUTO_INCREMENT,
  `aud_tenant_id` int DEFAULT NULL,
  `aud_usuario_id` int DEFAULT NULL,
  `aud_modulo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aud_tabla` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aud_registro_id` int DEFAULT NULL,
  `aud_operacion` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aud_valores_anteriores` json DEFAULT NULL,
  `aud_valores_nuevos` json DEFAULT NULL,
  `aud_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aud_user_agent` text COLLATE utf8mb4_unicode_ci,
  `aud_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aud_metodo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aud_fecha_operacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`aud_auditoria_id`),
  KEY `idx_tenant` (`aud_tenant_id`),
  KEY `idx_usuario` (`aud_usuario_id`),
  KEY `idx_tabla` (`aud_tabla`),
  KEY `idx_fecha` (`aud_fecha_operacion`),
  KEY `idx_operacion` (`aud_operacion`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_auditoria`
--

INSERT INTO `seguridad_auditoria` (`aud_auditoria_id`, `aud_tenant_id`, `aud_usuario_id`, `aud_modulo`, `aud_tabla`, `aud_registro_id`, `aud_operacion`, `aud_valores_anteriores`, `aud_valores_nuevos`, `aud_ip`, `aud_user_agent`, `aud_url`, `aud_metodo`, `aud_fecha_operacion`) VALUES
(1, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=hfQleOU5GNG8muNZbKE-GAJElCCUR_03r8t9NA6sHdO4N7OGxctcFa3wpQAmSBNNPOPU-VJI3UlBqi_ts3qQDQXZI_BKY29Jp-yxdzwFUOxMyQ1yPF8DYYbOybFWp5kS', 'POST', '2026-01-25 01:14:51'),
(2, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digiSports/public/auth/logout', 'GET', '2026-01-25 01:25:07'),
(3, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=8T3pysIhVovxP0AF46qCQ-RntDa0F2TlxCF0FcTC8_MlU_7pDhIXIR7rpcQiIAM0aFgNyUUvVe0Acr7KosgnPAqbT_UzH-kddx7RsqeKcyTrh2ruZtM9NWPp7fkpYnxT', 'POST', '2026-01-25 01:26:07'),
(4, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digiSports/public/auth/logout', 'GET', '2026-01-25 01:39:31'),
(5, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=1eybvyKB53v7uz3wb9IXErCNXg6o-jIN-_VwvhFY6ScP1WfLhIbyIYcP-W31WKSHA2KNgE2K9xqLG3o_M4kP3-XFsVhKN8H0HDaFzOL8JtqjoPBLqjtCjAWGU9mR8vBP', 'POST', '2026-01-25 01:39:55'),
(6, 1, 1, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=1eybvyKB53v7uz3wb9IXErCNXg6o-jIN-_VwvhFY6ScP1WfLhIbyIYcP-W31WKSHA2KNgE2K9xqLG3o_M4kP3-XFsVhKN8H0HDaFzOL8JtqjoPBLqjtCjAWGU9mR8vBP', 'POST', '2026-01-25 01:48:34'),
(7, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=_4yUIH7AuPYFSOSmmDdcF4PQ-LTU5j3iBx5pbUKrcBKVGh9YdYVEidOaphRlmCny-eN3K0gjTBVraU60oG6lgkhNS7tkY8sXWnmDVhTS1cyUJWR8mX0B3TXqlnWjR0_H', 'POST', '2026-01-25 20:27:39'),
(8, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=bnrgs_d9InBCP3o6_5Fp1P5Sa0q7ShnrQtyWr4tYohLW2Y8kUyn7P7_0Qc9q5LQhwO8z1R6ztRTA7dBh-olflNeOwGo_3n4pokVNsLyhQ-hc-x75ij_QPLvw', 'GET', '2026-01-25 21:52:45'),
(9, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=wOWovaEWJU9zJw8xETmwhJ4PLPgvPqPHlqztJ-fhWTQdkODXF7f_skGnlWYN3O4cz7rz-Qfufe85xBPF6WrV-P-Lj6kB0MaYy8BB0DloSFxd7OYSUy8NcFkYIVhIQ-2i', 'POST', '2026-01-25 21:53:07'),
(10, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=yFPgmsUvCUk6cryaDuMH73mjo400pHfYYbCmdYfxuyt8AdeJQRYSszxPnFrayEwL0aF_lOkigVizg9ltnMNXW_VzSYfnbgV0T7TRrdfQ4TeT4Xd9Wx185HKl', 'GET', '2026-01-25 22:03:45'),
(11, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=TYbX0Nk7zGbtGBEVrCntXsaRvJDYhSQamj6syekUnPuWNzSAgsipCXnAoQ4YRYNt4uZyAwzcP4QvoaE2wXSaL08uo6VjUcqEvzCBKGg2veP3wT5lJ5DqfqCskcv0l686', 'POST', '2026-01-25 22:21:48'),
(12, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=TqanCHP5bSMI5SKHvE5qOzFfT6RXixNlid1RrzLBdCExxFKgX0QoVIO005ahujLRwoGj_pZMVg6gI4q6nBoQlbTSG5wwWt0m527bnGwr_X7AXcF17VtOsQrI', 'GET', '2026-01-25 22:26:20'),
(13, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=yzjNvgJjTpVH_GnJJyztuleUykRvG1LVAG8OdBUh-YjeJfwfDr-zbjF03kfE_x6KddjIIKvMfYVVr1JUPn9lxbnrt8WwQkk-O0VF6kFr2j6B3AL4b89Lz1E0POJik5Ls', 'POST', '2026-01-25 22:27:00'),
(14, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=nhlhA5giN6c0SZfVrXYA_jD61G9PhTJAD20t7GWM3PW-jkoS_e82CTRd23BOqz3B_UcXZuft3089ufp3ieEc8IoT27Nk9zR_YUdRpV7DqKLtGlyGJydTRidU', 'GET', '2026-01-25 22:31:42'),
(15, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=nhlhA5giN6c0SZfVrXYA_jD61G9PhTJAD20t7GWM3PW-jkoS_e82CTRd23BOqz3B_UcXZuft3089ufp3ieEc8IoT27Nk9zR_YUdRpV7DqKLtGlyGJydTRidU', 'GET', '2026-01-25 22:31:59'),
(16, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=nhlhA5giN6c0SZfVrXYA_jD61G9PhTJAD20t7GWM3PW-jkoS_e82CTRd23BOqz3B_UcXZuft3089ufp3ieEc8IoT27Nk9zR_YUdRpV7DqKLtGlyGJydTRidU', 'GET', '2026-01-25 22:32:16'),
(17, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=nhlhA5giN6c0SZfVrXYA_jD61G9PhTJAD20t7GWM3PW-jkoS_e82CTRd23BOqz3B_UcXZuft3089ufp3ieEc8IoT27Nk9zR_YUdRpV7DqKLtGlyGJydTRidU', 'GET', '2026-01-25 22:32:36'),
(18, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=nhlhA5giN6c0SZfVrXYA_jD61G9PhTJAD20t7GWM3PW-jkoS_e82CTRd23BOqz3B_UcXZuft3089ufp3ieEc8IoT27Nk9zR_YUdRpV7DqKLtGlyGJydTRidU', 'GET', '2026-01-25 22:32:51'),
(19, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=nhlhA5giN6c0SZfVrXYA_jD61G9PhTJAD20t7GWM3PW-jkoS_e82CTRd23BOqz3B_UcXZuft3089ufp3ieEc8IoT27Nk9zR_YUdRpV7DqKLtGlyGJydTRidU', 'GET', '2026-01-25 22:33:05'),
(20, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=VXoQyCMx-r8PABnS8tE3Xai1S5_SXMIFKwSlFKEv0VxESqT-AOszRLW1RSri4Sa3uHTnIz_4_sZD8ndz-MtJXo34bRD55v75Djfktcx8R6d6oQHLQ2GYFe3m', 'GET', '2026-01-25 22:33:43'),
(21, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=VXoQyCMx-r8PABnS8tE3Xai1S5_SXMIFKwSlFKEv0VxESqT-AOszRLW1RSri4Sa3uHTnIz_4_sZD8ndz-MtJXo34bRD55v75Djfktcx8R6d6oQHLQ2GYFe3m', 'GET', '2026-01-25 22:33:59'),
(22, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=VXoQyCMx-r8PABnS8tE3Xai1S5_SXMIFKwSlFKEv0VxESqT-AOszRLW1RSri4Sa3uHTnIz_4_sZD8ndz-MtJXo34bRD55v75Djfktcx8R6d6oQHLQ2GYFe3m', 'GET', '2026-01-25 22:34:15'),
(23, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ow_TWL4REgOIEezFjZd0xrPunhoeuuZntagE2tV_ROEKu0YoeKnLevsjEr_HPl0fVbE3ywgt-w4rDmRvxXuNm5MpZlMo7XSr34sTujJiLuhgSSaUzIlPD6Qf6m1Wj31e', 'POST', '2026-01-25 22:40:54'),
(24, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=wL6_b5L3d7LWTrAOW3G63mpj1hyyOyPm32d67i-f1drtjwm1qkChmJM9BsVAbupPgPfz8OayQiSH3_I5FTuR4io0Zlm_6pok3_4HkwTIMzBYSD76Hf3077q3', 'GET', '2026-01-25 22:47:57'),
(25, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=YL0NGlLRAsbjSUR5SwyoEozZUM6OAWDglEwLo6MQzOEfoRmuAONmqsSNwAirMfciXEH2wuU6IEEK10OgiN1vx_ABUB5Xx3fA4_fZPD1PCu6Qd4jZBsvgMbubP2kcyk9O', 'POST', '2026-01-25 22:49:46'),
(26, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=C_Xuy3XEJMp-Hb_7T7T53h_xWJVtKgybPpvYEkCjItcU9_Eec-3hraQKYASag48MCieXDEW-yAGPUG2_NaJzX0D43vQ3XvDblTeF18aFF9Relt6sSFj3UiUC', 'GET', '2026-01-25 22:51:43'),
(27, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=xMVqpJDsUyr5OGpAe5EH567LQtnvUA_5jx6aBTthRSHvrHG9py7W0fko6SpL6cVB2_vVnGFZOHAhsXWMiYzS3VHkJ7Uf8RzXGuYJnd0PUFBCUyFCCuqm4q4a1qncgoYK', 'POST', '2026-01-25 22:53:43'),
(28, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=nqoWs0X2US3usaMWBFEM94gGyUt2L8shQ1WNBRFECP7viUvC0ed82dEzHZBGbfc38DK9JWjvKKF1Vfb60xT2Yzh2DKiZ7u1SLgoy3PIa2yrJjTX3yMIZrah8NwQ6ncWY', 'POST', '2026-01-25 22:56:18'),
(29, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=9guI0APuIfJTg6_V7mmdeDBl2CLmAVdFE08WYaV3rObpr5yg6lkSoT5OpOe508Hhbp_QWgdLm2hk9G4gB1NNY94Dd8QNHUZsh4k7lZns9qbnCS7nmb4icivf', 'GET', '2026-01-25 23:28:50'),
(30, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=gFxchTWKRexJZ3XxiursPsFwx6ItsxhZyWudPh0M8I5SQQxr5n2CywyWZIU6ki9qL7TU7TX37WUElDteC6ePG_zO2Pg5jXXuaVn94MVkHP1lJcg04zsB3bWjv4-BiI5h', 'POST', '2026-01-25 23:29:18'),
(31, 1, 1, 'Instalaciones', 'canchas', 8, 'UPDATE', '{\"tipo\": \"BASQUET\", \"ancho\": \"25.00\", \"largo\": \"50.00\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet - Cancha Central\", \"cancha_id\": 8, \"tenant_id\": 1, \"descripcion\": \"Cancha de baloncesto\", \"fecha_creacion\": \"2026-01-25 18:07:00\", \"instalacion_id\": 4, \"capacidad_maxima\": 10, \"usuario_creacion\": 1, \"fecha_actualizacion\": \"2026-01-25 18:07:00\", \"usuario_actualizacion\": null}', '{\"tipo\": \"basquetbol\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet - Cancha Central\", \"capacidad\": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/instalaciones/cancha/actualizar', 'POST', '2026-01-25 23:37:53'),
(32, 1, 1, 'Instalaciones', 'canchas', 8, 'UPDATE', '{\"tipo\": \"basquetbol\", \"ancho\": \"25.00\", \"largo\": \"50.00\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet - Cancha Central\", \"cancha_id\": 8, \"tenant_id\": 1, \"descripcion\": \"Cancha de baloncesto\", \"fecha_creacion\": \"2026-01-25 18:07:00\", \"instalacion_id\": 4, \"capacidad_maxima\": 10, \"usuario_creacion\": 1, \"fecha_actualizacion\": \"2026-01-25 18:37:53\", \"usuario_actualizacion\": null}', '{\"tipo\": \"basquetbol\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet - Cancha Central\", \"capacidad\": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/instalaciones/cancha/actualizar', 'POST', '2026-01-25 23:38:56'),
(33, 1, 1, 'Instalaciones', 'canchas', 8, 'UPDATE', '{\"tipo\": \"basquetbol\", \"ancho\": \"25.00\", \"largo\": \"50.00\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet - Cancha Central\", \"cancha_id\": 8, \"tenant_id\": 1, \"descripcion\": \"Cancha de baloncesto\", \"fecha_creacion\": \"2026-01-25 18:07:00\", \"instalacion_id\": 4, \"capacidad_maxima\": 10, \"usuario_creacion\": 1, \"fecha_actualizacion\": \"2026-01-25 18:38:56\", \"usuario_actualizacion\": null}', '{\"tipo\": \"basquetbol\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet - Cancha Central\", \"capacidad\": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/instalaciones/cancha/actualizar', 'POST', '2026-01-25 23:39:27'),
(34, 1, 1, 'Instalaciones', 'canchas', 8, 'UPDATE', '{\"tipo\": \"basquetbol\", \"ancho\": \"25.00\", \"largo\": \"50.00\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet - Cancha Central\", \"cancha_id\": 8, \"tenant_id\": 1, \"descripcion\": \"Cancha de baloncesto\", \"fecha_creacion\": \"2026-01-25 18:07:00\", \"instalacion_id\": 4, \"capacidad_maxima\": 10, \"usuario_creacion\": 1, \"fecha_actualizacion\": \"2026-01-25 18:39:27\", \"usuario_actualizacion\": null}', '{\"tipo\": \"basquetbol\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet - Cancha Central\", \"capacidad\": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/instalaciones/cancha/actualizar', 'POST', '2026-01-25 23:39:45'),
(35, 1, 1, 'Instalaciones', 'canchas', 8, 'UPDATE', '{\"tipo\": \"basquetbol\", \"ancho\": \"25.00\", \"largo\": \"50.00\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet - Cancha Central\", \"cancha_id\": 8, \"tenant_id\": 1, \"descripcion\": \"Cancha de baloncesto\", \"fecha_creacion\": \"2026-01-25 18:07:00\", \"instalacion_id\": 4, \"capacidad_maxima\": 10, \"usuario_creacion\": 1, \"fecha_actualizacion\": \"2026-01-25 18:39:45\", \"usuario_actualizacion\": null}', '{\"tipo\": \"basquetbol\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet -Coliseo Ciudad de Lojaa\", \"capacidad\": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/instalaciones/cancha/actualizar', 'POST', '2026-01-25 23:40:52'),
(36, 1, 1, 'Instalaciones', 'canchas', 8, 'UPDATE', '{\"tipo\": \"basquetbol\", \"ancho\": \"25.00\", \"largo\": \"50.00\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet -Coliseo Ciudad de Lojaa\", \"cancha_id\": 8, \"tenant_id\": 1, \"descripcion\": \"Cancha de baloncesto\", \"fecha_creacion\": \"2026-01-25 18:07:00\", \"instalacion_id\": 4, \"capacidad_maxima\": 10, \"usuario_creacion\": 1, \"fecha_actualizacion\": \"2026-01-25 18:40:52\", \"usuario_actualizacion\": null}', '{\"tipo\": \"basquetbol\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet -Coliseo Ciudad de Lojaa\", \"capacidad\": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/instalaciones/cancha/actualizar', 'POST', '2026-01-25 23:41:09'),
(37, 1, 1, 'Instalaciones', 'canchas', 8, 'UPDATE', '{\"tipo\": \"basquetbol\", \"ancho\": \"25.00\", \"largo\": \"50.00\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet -Coliseo Ciudad de Lojaa\", \"cancha_id\": 8, \"tenant_id\": 1, \"descripcion\": \"Cancha de baloncesto\", \"fecha_creacion\": \"2026-01-25 18:07:00\", \"instalacion_id\": 4, \"capacidad_maxima\": 10, \"usuario_creacion\": 1, \"fecha_actualizacion\": \"2026-01-25 18:41:09\", \"usuario_actualizacion\": null}', '{\"tipo\": \"basquetbol\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet -Coliseo Ciudad de Lojaa\", \"capacidad\": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=YxH1xJ1mWqH8c3uX8do8Wjmp0OnrIfsgNTxPv5j3iu4fSFEu--vjahhm857fGOgqMmiFe5KHcdXlh8XFkUIXrZRda83N21OBOTvnHXJboI3UFDiiKD7CQU8BeRJ6QFSbpEqJxldgpd2A', 'POST', '2026-01-25 23:46:13'),
(38, 1, 1, 'Instalaciones', 'canchas', 8, 'UPDATE', '{\"tipo\": \"basquetbol\", \"ancho\": \"25.00\", \"largo\": \"50.00\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet -Coliseo Ciudad de Lojaa\", \"cancha_id\": 8, \"tenant_id\": 1, \"descripcion\": \"Cancha de baloncesto\", \"fecha_creacion\": \"2026-01-25 18:07:00\", \"instalacion_id\": 4, \"capacidad_maxima\": 10, \"usuario_creacion\": 1, \"fecha_actualizacion\": \"2026-01-25 18:46:13\", \"usuario_actualizacion\": null}', '{\"tipo\": \"basquetbol\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet -Coliseo Ciudad de Loja\", \"capacidad\": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=W5xuj-aeJ7mhn6CFsJinE6ZBG1vLrngIq9JmqOQKx6LyNYGnlRzcbXxbjHEkUTDrIfBpCm6kmcF8QuntgIO1VVQql7TK2NxWAgPAtkyuQej1N4nwMPFn8_dyvpkU-CUPoI4IHyvNy_sO', 'POST', '2026-01-25 23:46:24'),
(39, 1, 1, 'Instalaciones', 'tarifas', 1, 'UPDATE', '[]', '{\"precio\": 20, \"cancha_id\": 8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/instalaciones/cancha/guardarTarifa', 'POST', '2026-01-25 23:47:38'),
(40, 1, 1, 'Instalaciones', 'tarifas', 1, 'UPDATE', '[]', '{\"precio\": 21, \"cancha_id\": 8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/instalaciones/cancha/guardarTarifa', 'POST', '2026-01-25 23:48:11'),
(41, 1, 1, 'Instalaciones', 'tarifas', 1, 'UPDATE', '[]', '{\"precio\": 21, \"cancha_id\": 8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=SMzLr3Z7ToQJO-5jt8RqtXuDwsnTu5nrsFatq286UzuoMhKS7Wx_m7G9JOIoTUdukGc8Yqf0sBX-3Rz5VgAvj6_qXr3rgxjWF2M1ksmKQgcKIp5ngEWOr3YczytsB6ZwWmL0TFo-8wa03VMC', 'POST', '2026-01-25 23:50:05'),
(42, 1, 1, 'Instalaciones', 'tarifas', 2, 'UPDATE', '[]', '{\"precio\": 18, \"cancha_id\": 8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=uIgOzDDpj8-hIVF5gF5JtdM5nIEsWKFWGGeoQ5S6AWG9GxYTl7HkD1IospamUEbnQct3VktEcaK4uAJ5R1P3X_Zt2YmYAdD3ogsOaF9R5QtOD6orfeEXd_xskBQUC9pifwDTGcPIoLGcJsGw', 'POST', '2026-01-25 23:51:00'),
(43, 1, 1, 'Instalaciones', 'tarifas', 2, 'DELETE', '{\"estado\": \"ACTIVO\", \"precio\": \"18.00\", \"hora_fin\": \"12:00:00\", \"cancha_id\": 8, \"tarifa_id\": 2, \"dia_semana\": 2, \"hora_inicio\": \"06:00:00\", \"fecha_creacion\": \"2026-01-25 18:51:00\", \"fecha_actualizacion\": \"2026-01-25 18:51:00\"}', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=77Kb4YPEAsyEq43qwUoDdOENCpaAAhWbjHecfXv1PJ7mOY73B_up0p2kF8UjNJZzozNUZeHtH2MjSV1YKQUMcUlZps9yenUcRAZGcg4G6bP_hhI_M9wVXz28WgzilV_6goj0V3Eag_bSSFVx6Oxmre3rUCWiz91dla3RqBoC5Gel', 'GET', '2026-01-25 23:51:15'),
(44, 1, 1, 'Instalaciones', 'tarifas', 3, 'UPDATE', '[]', '{\"precio\": 18, \"cancha_id\": 8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=0tskjgmB7YTMJZuyUJKDZeK0JtYR4i2ej1lj0PBJFf09s_lBhwkIrF7-oKd7MPgwq0pyOhoKQyRVzP6QOzJCGBErTkJRLftHOXI4Yn2DZmF7e1fNbjmCL9NigjLzxyRbpzy3KAULd2T5Y_Mx', 'POST', '2026-01-25 23:53:12'),
(45, 1, 1, 'Instalaciones', 'tarifas', 3, 'UPDATE', '[]', '{\"precio\": 17, \"cancha_id\": 8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=8kwreK4sZF4wwFmINnt2ajIkdiySkhSK6vFLB7gH5IZQUqzEs56o4i2RQtOGoIkH3iKpJu-1u5_XPdvOY0nn8kpV8TxwY4NtgjArCVXVAKd9V3JEUkJ8SpfX3VOSUP3FgygOG5KpkaY7Apum', 'POST', '2026-01-25 23:54:07'),
(46, 1, 1, 'Instalaciones', 'tarifas', 3, 'UPDATE', '[]', '{\"precio\": 15, \"cancha_id\": 8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=eqw7dopCRUfACp-9-7S2-4KJHSWZJA_Ikq3yvyZ6AVmOp6EdiJQ47j4IesAxm37GyenTK7meXPFG3xjSN6z0hdI0vN02YDgle5-rjEz5q5M-HYRwoaRHSQtAmTQhERamVAUV2Pt9iiwH38nC', 'POST', '2026-01-25 23:54:18'),
(47, 1, 1, 'Instalaciones', 'tarifas', 1, 'UPDATE', '[]', '{\"precio\": 21, \"cancha_id\": 8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=qN0v_lQZ3-XhPfef6ajRGyVJ1yTziBDT7b5u-3iVf7j1AXkTwyS0UlnFy10q9TXDv5xsSBfWpJ7rfVQvtAiut-rEDoRxhP0jchAMcdJeojfNJfjLPfBJZKavPRQnqzalHSBd6IdUWaR_CFqQ', 'POST', '2026-01-25 23:55:22'),
(48, 1, 1, 'Instalaciones', 'canchas', 8, 'UPDATE', '{\"tipo\": \"basquetbol\", \"ancho\": \"25.00\", \"largo\": \"50.00\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet -Coliseo Ciudad de Loja\", \"cancha_id\": 8, \"tenant_id\": 1, \"descripcion\": \"Cancha de baloncesto\", \"fecha_creacion\": \"2026-01-25 18:07:00\", \"instalacion_id\": 4, \"capacidad_maxima\": 10, \"usuario_creacion\": 1, \"fecha_actualizacion\": \"2026-01-25 18:46:24\", \"usuario_actualizacion\": null}', '{\"tipo\": \"basquetbol\", \"estado\": \"ACTIVO\", \"nombre\": \"Coliseo Ciudad de Loja\", \"capacidad\": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=b8089Op9praqJQ01QQ3sxhge1mViY4aRyy74xivfoNztIl8yxRb3xqxhSD3u_0CiZgrK5eV4r2ZkUta5v4osJpuA00H65wkBQEx3-QSzktvklUNtnObbuIL9w2kGSgDeb9ytbCu363i7', 'POST', '2026-01-25 23:56:18'),
(49, 1, 1, 'Instalaciones', 'canchas', 8, 'UPDATE', '{\"tipo\": \"basquetbol\", \"ancho\": \"25.00\", \"largo\": \"50.00\", \"estado\": \"ACTIVO\", \"nombre\": \"Coliseo Ciudad de Loja\", \"cancha_id\": 8, \"tenant_id\": 1, \"descripcion\": \"Cancha de baloncesto\", \"fecha_creacion\": \"2026-01-25 18:07:00\", \"instalacion_id\": 4, \"capacidad_maxima\": 10, \"usuario_creacion\": 1, \"fecha_actualizacion\": \"2026-01-25 18:56:18\", \"usuario_actualizacion\": null}', '{\"tipo\": \"basquetbol\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha de Básquet - Coliseo Ciudad de Loja\", \"capacidad\": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=nvKilz9zLFCWRMq2QaWJed2oHVujT7IsR1DwBsxGGTszMUqpvU7LqZKRrgGT3pYITd3l6A7nGo-74NvHiDDCGqbRuw5Q-GHWT-AJWKW9XtqIm1ZDuhBQ3ND6giB4eQIb8Z4Hxafm15OP', 'POST', '2026-01-25 23:56:43'),
(50, 1, 1, 'Instalaciones', 'canchas', 5, 'UPDATE', '{\"tipo\": \"BASQUET\", \"ancho\": \"25.00\", \"largo\": \"50.00\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet - Complejo Sur\", \"cancha_id\": 5, \"tenant_id\": 1, \"descripcion\": \"Cancha de baloncesto\", \"fecha_creacion\": \"2026-01-25 18:07:00\", \"instalacion_id\": 3, \"capacidad_maxima\": 10, \"usuario_creacion\": 1, \"fecha_actualizacion\": \"2026-01-25 18:07:00\", \"usuario_actualizacion\": null}', '{\"tipo\": \"basquetbol\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Básquet - Complejo Sur\", \"capacidad\": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=CNc1jJvlM_TCQyEm3WiNTsPtw_qGXCj66clIRbK3tSotPsnHH_D7AEDdtoBOE_R304MhfNHnGeMxGyL7lhswyGZZ39GRh9YJAF8R4k4rGr3EsZbKh_m8AbP3HCV6cizKyi9kCIubGTZn', 'POST', '2026-01-26 00:26:12'),
(51, 1, 1, 'Instalaciones', 'canchas', 1, 'UPDATE', '{\"tipo\": \"FUTBOL\", \"ancho\": \"25.00\", \"largo\": \"50.00\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Fútbol 1 - Complejo Norte\", \"cancha_id\": 1, \"tenant_id\": 1, \"descripcion\": \"Cancha de fútbol profesional\", \"fecha_creacion\": \"2026-01-25 18:07:00\", \"instalacion_id\": 2, \"capacidad_maxima\": 22, \"usuario_creacion\": 1, \"fecha_actualizacion\": \"2026-01-25 18:07:00\", \"usuario_actualizacion\": null}', '{\"tipo\": \"futbol\", \"estado\": \"ACTIVO\", \"nombre\": \"Cancha Fútbol 1 - Complejo Norte\", \"capacidad\": 22}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=a9YqlZJMvkuqfTc7IZD0hcwZfthsIBbw74NUjgjPvWdDmhOE6Kk-jgzvs5quU9QNgwjwr_3jrEqjQkeWsOXx7TqhSKTYgPQKC0hFgHhYDwLPyAJCzLXPJIt2Ru-5A4_-f4G3gb63Akuc', 'POST', '2026-01-26 00:26:29'),
(52, 1, 1, 'Instalaciones', 'tarifas', 3, 'UPDATE', '[]', '{\"precio\": 15, \"cancha_id\": 8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=XpQE5t7pH5ofthU5GwGSlum6bpmbGStPBqofRUEu90L4kdWIhFgBy8bOaOkP7H_r19D4Wh3ATLZJmTna6MiOczfnIX5OULYvDEl7iX3WLz7_sdvSnxHasQRg5_krUdiRuIAuh2K220C2bZz-', 'POST', '2026-01-26 00:27:30'),
(53, 1, 1, 'Instalaciones', 'tarifas', 1, 'UPDATE', '[]', '{\"precio\": 21, \"cancha_id\": 8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=wdLArTl8YpprArqPINeiBVC63vomNoi6ShoHv7rEbawcVb6zhKtVqc0K-RlmqdmmPlpP9LmlJRzcjC_U5fdDi6g1yEy5IT5T02LUM2amiBzsv7bEMCTSLpxlmVmGWElbGnrAA8-eW2jh_yfl', 'POST', '2026-01-26 00:27:40'),
(54, 1, 1, 'Instalaciones', 'tarifas', 1, 'UPDATE', '[]', '{\"precio\": 21, \"cancha_id\": 8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=P5w9kGFCXqpJL0ylKxxRIX277B2-K5_UPVNjkhlliFfZDiov15VKFCGwQFYoce_N4NZA5TQlsU8t2aIcKFWJObAfbq69Qp6AB6SE4_pWrA10hZMfVWEMpWuU3acwRvoBXFupYPH2LcsFJfML', 'POST', '2026-01-26 00:27:53'),
(55, 1, 1, 'Instalaciones', 'tarifas', 4, 'UPDATE', '[]', '{\"precio\": 16, \"cancha_id\": 8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=-ceygxNccspl2qysKDk5Bwp9pN4g1CCTomBMUK6jQwXKibSmCjgPuTqu73Bhy72aTg0F25GQQv3JWbZvmgKYtBRaYY1vZNs2gfSZMGmZDQAREGC5Rb7vR7BgeL4ChC3BEVEhMB_gSBCbMdiY', 'POST', '2026-01-26 00:28:19'),
(56, 1, 1, 'Reservas', 'reservas', 1, 'INSERT', '[]', '{\"precio\": \"15.00\", \"cliente\": \"Freddy Bolivar Pinzon Olmedo\", \"cliente_id\": \"1\", \"instalacion_id\": 4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=7z1CVPMDUDp75CtDmhCBClwgI7vlpDv0TLlY9f-SJJ-5gXf0MqOXEMaO8-9hccKZWgD-sOAvotje64OrsJynrSuMBLz935MJLoQu5_as7LUEQEfpmlfnTbgaL7lR4pQ4', 'POST', '2026-01-26 00:36:19'),
(57, 1, 1, 'Reservas', 'reservas', 2, 'INSERT', '[]', '{\"precio\": \"16.00\", \"cliente\": \"Freddy Bolivar Pinzon Olmedo\", \"cliente_id\": 1, \"instalacion_id\": 4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=TsNt_F2pNgy9Qsxh5SEcGCVoEg7FQis4d2ZFvi5EU1K0GWqfjfwdcenAfbVb4-NivvkRyiMxYYY9TG7VmtG7uX6xV-f2B3DyJccEN-gBCEvajtvmkBS2MQ7GvwUfGnbb', 'POST', '2026-01-26 00:40:15'),
(58, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=AjfOHV7Da4sWZAjngBa18vs9K7N0qcPHYaBNPDN9JfqwFVP9f8jOvc0tp9fpw4Lx40Xzf36EE4h9MCoNnwXQqfaJ3LzHM-uxcDL4AJuy-clmmJHvLV49QTqC4ZlwUloM', 'POST', '2026-01-26 02:39:32'),
(59, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=2_L8ZVzATsk7UuZCv2npXpExW2ryrn-s5z0iSige2eM2zEIYZbs9P9yl8hcsw1QwLepmaGj-xszAsPB1j2_6zVTV3CnYPcKs7fRhpCUxr-Tay__5wHTZcN4fF-n68Lj8', 'POST', '2026-01-26 03:18:53'),
(60, 1, 1, 'Instalaciones', 'tarifas', 3, 'UPDATE', '[]', '{\"precio\": 15, \"cancha_id\": 8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=j0o_7ttnPp86_K0wjc_AVv80vs5XbNTbqTZl8NM_Yq5cKxjyoeXgznAnorbyzFQXhf4_Jz6yrOKyY8CusNQ-dn9DEUybCTFtcsJr-8EAxDJDns7lI1VcEIeVtX4ROk7z8rtkEXjwJ_fpCus-', 'POST', '2026-01-26 03:20:41'),
(61, 1, 1, 'Reservas', 'reservas', 3, 'INSERT', '[]', '{\"precio\": \"15.00\", \"cliente\": \"Freddy Bolivar Pinzon Olmedo\", \"cliente_id\": 1, \"instalacion_id\": 4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=TwrHIwk0WpQWepZ-zNTjYGQRLOQYCSuR85-K_a0-siLIYT1KWx2oQffdGirEbAuyJ4PZirEvc2OowRHhhY4N7TN49aFqPTpk6CVX0QEYeiDz1VaGa-d64t_AjXlJZONG', 'POST', '2026-01-26 03:23:02'),
(62, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=3iTlgyW3TX4x4J92RXdRmns8qaavX8Ptg4cxmwOOs21XbkrhY7hMwJhL8yYOXcj0gVVvlU3340i5-8PLa6fN8SgR1sJSAI5llprmL79VYDK9rJ1u6a-egdFjkBYT6eyW', 'POST', '2026-01-26 04:05:27'),
(63, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=82CnZhAQtkBiFHPxOEoakv3GtUTejW0K6RqDeg_zwohyfgnA17S0vyAgPw0SDLqrbaBtImmg9jjm0Pijp2F5IJphk555jCcSFClF-7rEzeMQlTnQvPlP2AmAps7Vz4p-', 'POST', '2026-01-26 05:30:51'),
(64, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?module=core&controller=auth&action=authenticate', 'POST', '2026-01-26 05:55:58'),
(65, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=qSeIQRlViv8PJqEO9_hDtA3SpfizONi44s4Uzzc0xLiJRttMwFKquK-hoPqy_ZH4aVYzA-OTFbsxqJlnZeOvPLPLp66HBoZ4KyOlz5OiYSuQ-BP2B0fd-53sbi4_8vji', 'POST', '2026-01-26 06:04:38'),
(66, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=VSDMeRA484kCZj4ib9meWix77INP-1BvPUmD0_LcYnx-lMDq9Fqk8znAYpnTajg4SV3B9ToSSQpn-Fm37Bk1b5UCUExDDZiFXbRz7fifMYFTbU6gWBK1raq9', 'GET', '2026-01-26 06:24:52'),
(67, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=GacYgar1QyBjhldg7-mRP0-5QGlk-tDJzejFVNvh4jlPRAsLa--iT2aImdjpdtm-od18q9lGY_87PNVImJlNIJMHJfL5qHDe-MXc7fhsxgSIGd2QRgB_c3VNkvC98Op3', 'POST', '2026-01-26 06:25:12'),
(68, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=P8QLJENWzOEUAhK6vtOCftUwD4X-8J9JAFvIpIQOH7aYRzhFHq2KotLPIhRi_q74ukQYH-Gykw-p_gLatxaQMnH6-JTkhwZHfdbB41ukhFR-sGSrksBpJPr9bR7v2TZr', 'POST', '2026-01-26 13:08:01'),
(69, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=_6MesIHOoToRqaXuAjzueXxxP85_5c0bPez5Iqpv24Fm2m37ZpPc1Ero5EDjHfQ8BRPKtfxnCdLPu85DoYwZcFntLDl7hX-q8bdc32arKCxXGnz2TpurmFua', 'GET', '2026-01-26 13:16:48'),
(70, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=juxF0cxYT1MQs5aHKIJ1LbtkPz50R3a4GBPPRsIuQh0pN5Ji1A49hJ3oY3sRhygpeBSNLKI0gFqUtKPO1EWZDf3n3yq5LJBAfNmmugYxKx3njs6SDoiEfe0VsFjTN52t', 'POST', '2026-01-26 13:18:11'),
(71, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=vHcw5RMmxuumjuL-VYlnYV3Uv77YsB4PvU43xZ2Ta6mDT_ujG0ygub9qvSL2TZlOL6_vJ6K3VOlknBoF3fdEh3zD72-JGyAZyKwezBmcP6Sx8EemwLZ5_6fHn-VhIk9_', 'POST', '2026-01-26 14:40:56'),
(72, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=7dvUId6nBhEQYrIRUEwdTlCRSNz2YlvPQvYYtoHbF_bxaOMla_4ftvDSzMcB9AXrMt9p5DVa9QHSFivFpX3drvcpunjbGGTdMBHXDj29R25AX_QPA9RZ8uw_', 'GET', '2026-01-26 16:23:15'),
(73, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=nuG-cu5kuQaS9vUBk7xn4MT7IBjo0bloifoMn5exo3rGkmmfzQFPQKQCqjc0x-N3XVyE_73J4iSwkFvnIe701nvfoN4EcX6yetII7ky1h4Clr1hkojrYoUFwgK6li68L', 'POST', '2026-01-26 16:26:24'),
(74, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=WKqL1egLv9VCD4zbnLLbEAFCdnz9ql5QTsy1sw9OhT_NLnzeO9d7KJBtEy827PYuR9NIh1aQqQL1_fI8PVGmQ2TZY_yyfeGREnH75cXPw0iH8mhv-EtAePzi0FK6nqjk', 'POST', '2026-01-26 17:38:59'),
(75, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=mf9sQ-HDXr_fOKHjfGpj77bPr3fMeoGjwkFuGQaJQkjKCnMP3A1IgOakV0vw4J2AGCRBv0-cjepEDR1aNtY5Tbcd_V3cnQ6KrlTeMuFvMdSM_2uf2x4ZmnSF', 'GET', '2026-01-26 18:00:09'),
(76, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=XzeFIejHVUjbFRlUhN284hR8MhqciHoupB18ocV8RvcfkgBtw2UsgRiLVHnl8GsN16bcJL65cyprfJkyDmCvUhqcLIKpvr60AKrJc6Fq6Dh40_5stx4R3GchkDsZG67R', 'POST', '2026-01-26 18:00:21'),
(77, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=PSrBtICuTsaVbs1JIG3Ud-1wck1SBUkJIjN9Ep2tGbT7tnw04GaBKdwdKxuBK_-zJckQXhUowsiqQk-oLtvVXWbDIbQ9UZlcMYGwglQ7LqZe1gElQTAvyLtK-o5pKY9N', 'POST', '2026-01-26 19:22:41'),
(78, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=JMxRkrKF_Fctnzz4Z-aQThghIK2fBMLw4E9KZopo-JFWUa5PIEc7S3QbbeAld_j4bTC8wDAsMj1kmw8QRUBGEYgwroD-MCqiXXeHSSxJ98zRP0KNkLMOugrr', 'GET', '2026-01-26 19:27:42'),
(79, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=XcWwdI0hba0UWawLb52ZEi9Sp3RF1E1bYpikaYPLdV5Z4z6Lt5J16cT5rh8703IzLR00kRccmEggTN-CYd_6DYhNpm8k5rWLcJD0Ts-A4Rca7bK-rAHQQhjGB5sW7EXQ', 'POST', '2026-01-26 19:27:56'),
(80, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=iYOAHrskw5bDF578RiBKGMx-Dyyyg0K2gCCO-RW40ktP6spwNh5Mlw0DkeLNZHlPY2GwYwPj8Fm3JT4r9tdHKv2g0XAQ9Cdr619lVKOWorvWUAJNGAMGvFOl', 'GET', '2026-01-26 19:38:18'),
(81, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=-0JPn_XoHrPt1DOvXAgF_pGlHpxauXSTZsL66dJYLOSW0KoQUUHRYFJZkzBE5dALc-bO7M6ZsSIJlMvjibq7B1ubHDFyoNUsOV0elzr5oDL6fDPGeIoNLU9-Jl4fbii_', 'POST', '2026-01-26 19:38:32'),
(82, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=tO-ze04H79I739mYT9TO5jOaDYWNzaFGgbUpnjGz6YGhLgtFN5livR0rk7X7bngEoYmCEGkEWBJjFu8InQp4t3UIudgG1_4x8VZQ0sFsAA9chzGIrxzxCJu7', 'GET', '2026-01-26 19:54:01'),
(83, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=kF6p1ulRtvzEi8IJLlzgMRdvwZnc5abdc-t_Prf_hW4_8rx4oh5skOREPDu_gBxRFRm0VwV5JKwUTpeKTeItMdYlIMDMRLF0vQqaV5B2_M5-zbhMxTYwP1VGBV_zMGB8', 'POST', '2026-01-26 19:54:16'),
(84, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=JoVDKgcJEf9_ORC4TFR3xXEV3qh2S8H5zpD9tYNjHbb2SQeqB-foNANJdAHZ45ugXtwgdZhSKF51MbsUtu8p_zRy2o_Tx6jLzyxdw4IDLqnIJ8ZMZgqZDnst', 'GET', '2026-01-26 20:35:08'),
(85, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=4Xx8ypQWNVKv4nF2ic4n3nd9vgmoAnbXGQA1cLsNMLkzNUGU49wVAuUk4Hk9n0oPAmPJj2FljTsZbvBPU_tZzxEFsMRI5JO84MWAVxKQjuuv1A9uni_cEyWGunekisuJ', 'POST', '2026-01-26 20:35:25'),
(86, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=IP2MPUsL1o9LyKS_LWzDGKFZnZj_sB-x6aQk32gqauu9Y7cq7K9w1fhuqCCuSriZGzoNyHQCMDgBWmpW96dmdu098nibNEwPCUp4HMfxLWeL4xz0Unw8vtyE', 'GET', '2026-01-26 21:56:34'),
(87, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=osCpVBuSyj0CJwxuaNQILF34w7VFAQ_KOoKHS6BgbVVsBb3xSs7qOD-PVZQDfz2-o_LnVkXyceXUhpDBFfRqJY5Gvpj5W2rg_Mf-NvSQJCoQemTpkz-s822xDcE9wzK6', 'POST', '2026-01-26 21:57:10'),
(88, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ftm6EdabIhth9upfqW1xTH_q3oyxLeING6Qefvr_AKjQ1Erw5xLaWFTqtUnA-ANHJeLqjUu8uw5fxNYQxUvDF9ggiHpllQsIDz6KT5YnNMzBIr_abZr3B1oItj56SaMs', 'POST', '2026-01-27 01:00:15'),
(89, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=CLyacyvkHJ115g9yr7yjX5lmKXplRAQczhLB5YOkMOVoidxCqgYJGACK-E2kplipjl2uIiChcRHEZpw8B8tdeAySMexZCWgHkAqqxsEele3MeLun08e2lk54', 'GET', '2026-01-27 02:11:45'),
(90, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=hKR8Xh_hNsA8pS2loqaff8CPSJ5yt8P1FiWfGdXLuVv4pImsWhtR5vCxvL-rRdNZC9I2MBELVCOLJQFrdZK0f-PIlF-BR_UsyTzboPjDBzW8SnwTnHFq7Xa9Flf-yvQA', 'POST', '2026-01-27 02:12:33'),
(91, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=PbzhPWHtTMM3sHttvAhStlIjD3NZwSgl0WtfA6-U_3oGb3nrGJt-Gudo1C-hlfLqvEUjAggVGOqCDZ2lLIuIv6w5MBRdetcpmrUQnpIeNDWY-7K5Enah1yQS', 'GET', '2026-01-27 02:31:10'),
(92, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=DsxXHs_7sLTpCPfP_Zotsh8ie8ZWYtaloKuPbWbEKp3D6YkYNkaY6Jro4j78vTdqePi_bt6vzsE5O-Xv7V_Pvu7khx5BP_C9FluElW93RrchITvveobF2LtI7lzCBl17', 'POST', '2026-01-27 02:31:26'),
(93, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=VJYGeGVHMgpvLE3PE8RCm4ncN0XDpKkeyq9eWOkdt5I0qw3s-og53a-b-1Dsl_LZh07Da-Ofkvbfca_0doaabYb0xvp8T0Be_0vCMRVvgGY_SvhLxyd1qyEesu1VBcbG', 'POST', '2026-01-27 13:19:52'),
(94, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=5u_1yMiZ5JoFzmxsJGN63sxrD7zLd0sOZ5OhaO3TZQI07LJVCiH0yC8RNtlta3YAt1G7miJErZ6o-SoPmhHEYzqkIUQy8NltyDaQK_APH-Ltff0ZHxT6da03rdOFtc0N', 'POST', '2026-01-27 15:58:18'),
(95, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ThLgF0wl8F-d7YNgWw0xjuHihIWpTSesVTZTEtrgaiLQx9GbAAxbAH70RtUttEhQj5EsC6yCYNG0uWRZ6udum9CekhK2BzuzC96q1qRtHh4bb__9N2HQDFgeLzqxey4-', 'POST', '2026-01-27 16:07:43'),
(96, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=CFwKkDhIRhw_Scpz8pcZbcHbQC6yLD72jac3xgqaiuVWu3dYwAmjm6-JQ2o62Vlvqe0pq3ypsifxw9ln2xCmo6i2NdmwV_GBrcJkum6lVwJCPiyBqRWgfxDthQ-hPwQf', 'POST', '2026-01-27 19:20:21'),
(97, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=hKVP0a4hi7INZdClOzyRlIXj3W0ovWHp0LEd_u5EY_dETASeoafNObrdYphC4aqLj_3gIfbNV7fcg3v7Q6C9t6YOh0X5GQWLh4khrybLyWtqzxay7xHBd_IkwvXQHmlN', 'POST', '2026-01-27 20:42:41'),
(98, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=N-LWVEMj2z8CyhDK-nst85fDeiZWe4oVRZlddFhN5AD8w1z--YhaTLDC2Bbwv0nRaQwi2RJc56FdE2ChCzY2NA2ORauTlbuSajkJtmGp-1I92BUI0ON_p9N17lfcMLGV', 'POST', '2026-01-27 21:53:03'),
(99, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=gMwfaLQa0TUdMlOz_JWbMTRB8eme7OK2bSaQBOuHcA_7ur06QNsEEMWPmL-UCl1CEQyu8BGsOp3LVeKEEeHfG1-xTdSRAkpX9E23b7Ria-avMho7GlPeWEvM', 'GET', '2026-01-27 22:37:11');
INSERT INTO `seguridad_auditoria` (`aud_auditoria_id`, `aud_tenant_id`, `aud_usuario_id`, `aud_modulo`, `aud_tabla`, `aud_registro_id`, `aud_operacion`, `aud_valores_anteriores`, `aud_valores_nuevos`, `aud_ip`, `aud_user_agent`, `aud_url`, `aud_metodo`, `aud_fecha_operacion`) VALUES
(100, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=zGpwS3MbRV1iB6YsnqMj3_LDgy6TKMhyXXbKEXQxID7JTm-VxnNis1kRJEKR_WrLKeR5wUKEJJ9UbORICiWbjc85_qSSmWH2QFZof8CgnRWcOgj0Bq0E0T06Hjc53HoT', 'POST', '2026-01-27 22:39:13'),
(101, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=pcGUBer9VCArkzHFnP-nQvGpfWzdgqGTICfPeH_3rt-wVi5ChgoFuD6VLcLQdoJFNEULE-KGs7zAXfVXAErd5NHCwJk3H4zLR-Pbm9zsu-Sk0IA_3K8F4REy0xL3Ke8l', 'POST', '2026-01-28 00:57:27'),
(102, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=MW6zeugEIGw1QllO_WkHTq_ISheId6M-dK---ShAaTBiYWQi_IR8IGmqnGbD4HOhWztSlBMDVXUZzWOErI30FdlwNJXbIhZoD94Y9yonNrrqk6dObRrDCUO3t0Qus3h2', 'POST', '2026-01-28 01:43:00'),
(103, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=JSTU8MYEGmJ3jQhcslKxNrqHMZi68ZpcnLoPABDjb0GIzua5N9QMbmAB0EF-nwPphcsmJ7XxPcyEARpp0zhxmxRSx4fwNkfC6tqCQT1z42lLGMO8TckGzFNj', 'GET', '2026-01-28 02:45:41'),
(104, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=468F1ugrsdGTX1vllMj_CXLsvYwnSAKatXjQbySlpiyL7Sr1DtwY3XeBpfIKNt3T3VeI5qmPfT6GiMqiA87a1LlW0zOsOeuUUDVYmUMqR7wHX6ga8A03UHkAvTI4e3yK', 'POST', '2026-01-28 02:46:19'),
(105, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=qHaQbYAwnna377kNxyY-555wNi6ur8kG5wh8HN35NrKUIyAbBxVmpjZ3Roe0iXDXdb1PLGUQ2jyJAFuoYNoTwDNY23cA6FmjfNVprjm7YbSD-6OUj8B3X4IIbDAukWOC', 'POST', '2026-01-28 02:59:28'),
(106, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=dK2oQnwLIGsOSXBEKRjFFPuLkhPoIqO67C9RxeWAYBzJJuskHaReL1e0Yc9Rhgh2mJz_wTVFlrF0HZDLyzDeI1VdlO8JMVP8fm6kMS54aD0v6VAvM7q1yFok8cNrrqrI', 'POST', '2026-01-28 13:53:45'),
(107, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=aHPjuzoEJCx-fmEoBc_Ar7-QOXamHgIexZg6Yq4pRcysDJsmWbNnk0_Bt3X83cxuR5pGpyEMv1bIpdNOTEB7HVbIPWpo4gB2S3xPdcmKrucMlLE7z4Upd9wMfldpo8fA', 'POST', '2026-01-28 19:28:20'),
(108, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=OMZL6ZggCeY6mhEjO87uAcxPVg-tKZfCiB5OU9Ry8zXZjkAtYlHlk1ZemhkciMlLcqK3WsN5YXHgCLtwKpg15xLUR-DR79D3CFkK93Kqy-KLHpp79qY-78sEqJPPuKv3', 'POST', '2026-01-28 21:25:42'),
(109, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=1_D0DvZJtamQcLKEocX4QkP_mQV_oPAW2hzkv27XIMZhQ6uM60Awzs9Q78DTPVj11X_OCrkNnvLm7_khX0ARDfpoMvtX3bnb6sX9Ea_fLbFvStINW9jhhk-Vk7EPaT_Z', 'POST', '2026-01-29 01:36:25'),
(110, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=v6GN2z0HSpaRoSceXOVWmxyZ0CJOgEs3LQ5KiPRX-B8vsM4H2EWTN535pykToayY1BMugBBMpf4G0299Uv47jP1Nzj-uJ_Hygdppra8YA0_F6sxC517ggACj9JPoCZGM', 'POST', '2026-01-29 14:42:37'),
(111, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=B9imEEqqGpN_0t30mFYPMPq14w9-x7uXmClgNdYvXCpLnFkXHwwje92G5BsdzJxXg8CuEcFmGbFIBRculWAnAEV7M1PZOLfDMQNrnj-lg12yOpeo4LItm8NNjE65eTon', 'POST', '2026-01-29 15:01:27'),
(112, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=vJxE0WP1sl-G3-DiuNf3HTgSUpJaNYrhje3A6VcaIl50NGLlcKVAxJLNuJ1Ng3PYov3PlOmMNQMdx655d7Oh7uwAV-1ECuHU7cYHNm8RlCizAGVY-z2tB6YZudrcKVt0', 'POST', '2026-01-29 15:08:43'),
(113, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=iAt0X_4Cg-0LLxEDoXaPxpPglcl8Z7vmQm2nqXkLlc8tXRfZubWD-f2IVE4lhEtZRsTT3jiHbbgwYcze7EaKCJKZjpz-Zo21eZR86gq36IKMf2duneOdoE34421VOvgl', 'POST', '2026-01-29 16:10:34'),
(114, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=AAjhKqQAU5v-fdwzyo1WTg15Uoe4DwJu4Ubs0gOdmgCM6Xo3JjGgAurxqcRMI_k9vdFlDU3XojR8d-_wTsdr-1wKo_n6r4WsAZ1JcOdBIdgeuqBxOyYK-6tO35dRT3LG', 'POST', '2026-01-29 19:31:02'),
(115, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=FPyrkR9uJRQ5bGTmXRpxrSJYnP3bEs3q3zlxsctikXBaUMhuo6iLTP_KKnXiClRfneqFnUXoJ73J0EUafdTCwctcYQBQWhs5n0G6sdpQKeApAj7_zE3o6AiPgmKDqQTv', 'POST', '2026-01-29 21:38:24'),
(116, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=BI_AvyiKjffKEHeUMYAO0zOVG-MbufSZalA8w1I84PMcoO4qj_rCmZBV4LW0nXSUYkf1HPSecnfnzBZWmfe89tVPmVnXxdOqSAuAiLrw3__pj1jKICCRp5J0', 'GET', '2026-01-29 22:20:28'),
(117, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=lxeR-VPN0frFvyS7FAhlRI8vvL-nMX47Qw-z_cfafreLjrdVa-2xlkGyA6Vh4EiGzBeMx_N8YeNzekO1h5tfxekVYW51Q_hmXJZrefzsqZQ-3Nj2Uu9382-8iM25KFDF', 'POST', '2026-01-29 22:21:06'),
(118, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=HjvLgjNnIapsRDiHZqSIZ29IKqb1kz9mMkR9Ha6z6zu36z6j0wqWdG3ZSwVAj5wv3KkUgWqMtimvLVefnKiwEqUmkcEjTKNN8AollAI5FAbXi5J8Es201bKU9_ivxCKE', 'POST', '2026-01-30 00:54:53'),
(119, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=B8Eeu1nhvmWcvGFnVp0vE0cB1FnGqj0cMcFkikxgBw9KnhTLlK_vjG908b8hRB7dVwfjvO6nOguKVk_xbVBeEO3uTtVp5A9c9cduMbNFdyfgGTLORZvbcZe0Z_7vh0Q5', 'POST', '2026-01-30 02:42:50'),
(120, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=bImQEvDLT7NpwoKFk9GR4X3iNoJBLCeIdUhBNOyVggvDelglZb3nQdU1sPf--426gbNWZjNFAAdbttY4AVVyHEnMJohmwCE9x4kY7dKgfya7Em9vZ31R3ucF', 'GET', '2026-01-30 04:26:25'),
(121, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=8cqKh2Q4kPzOKTuUu8VdS7ddyhYlRwCuPaRkpNpclnE3DuDzZWkwm2n6WxQgtvV1rWHwUZoWVbb3ZvmP1D2lsUTNEgBeK8l427scvTp7E9g46W2lf14i6Hxq_L56dSJL', 'POST', '2026-01-30 04:27:36'),
(122, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=8PZg1zT8aHg4AWM8T41_9YCczT92GKdFqf4G0mltYu8AJ_jFYDtqtHm83HXBRnylkuVVEhNIhwKFTbAUQHBa_N7JSBN5RHALvoESh-6fECwXRAqCcRZdC4Hs5MFQbfCi', 'POST', '2026-01-30 17:29:24'),
(123, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ycdDeqNti00Exl-x5Qqkm8rpSs3kyNSZdvNxUVpvGWJT08h5I7G1ujTNpf9_9rMHqmb-ktDTF8tA6MWuTkP4882ClojbzcABg6r0FSM8tF8EQQFVHZ1hS9kj1DFScW6b', 'POST', '2026-01-30 19:20:37'),
(124, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=yxStuBYrflbDq8kYdYtN3VJnTT9IpfJHKiILp-66dwCx9sES3dFJJEHc4iuV35b1L9-Pwfc1nEkNbHu6MVqXZO4kuEPNGL7BFSRiuslq4sjFPPNsqiA-kLp4K8k4q-n_', 'POST', '2026-01-30 22:32:51'),
(125, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=IItdCld81xeaDSy0fyh8pzNK2gscZvOmg46TKRvC5GGNqJbeb4pQdxTjSfj7tEIwlIqpRf6_ldw1hD5ixsu4N3nIjzQAKfx3yy3k7oo-bjcjEySlZJWb5Ugi91b-dYWr', 'POST', '2026-01-31 02:03:17'),
(126, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=aJtvSLgGLiAMoJAEDFPd0L7S4G7Vvf-sDDbwtIX7OEMJnp0yj2ivgC6naQlHMer01BWjaWaO8ICTswClxg2GVDmVnsRnP1x5ykCRF7sosDJ5HG-Jf1ly3gI7aKgzFwdE', 'POST', '2026-01-31 23:34:20'),
(127, 1, 1, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=HIlMY3A_Al6B9wVIROO9kiBJu_cO109G2v-oCdXOpfvx6MLJfFy_Ou42NCHqmzq7w-wehiegAO-P_Ab548n_o1W73T9gpYIMWTjbAxDHQGpo-J3cFCSq_EptltupuGzz', 'POST', '2026-01-31 23:36:13'),
(128, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=C6KgZeUmbwQwiRcL1cSHKTXrk61cpeMgwu5DCcQXI9tLB15qU7gNjdx65ZToXUfbmLN7fQUSg1aBUcEvHo4JNh7RveXpi1U45MxumdTsDxGRgSiOtt5bRYcZFZMMtger', 'POST', '2026-02-01 00:29:51'),
(129, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=FAtTLuzsHPNSYSbXxVmZVOdexynu7L1H-F-zZzb2Z2y156Pnv0sbU_Wdvq_DW9oQYi4UFCJDBDMxEPzsEZ36IACRIyEWriMTExqPQhCb-vvfPCmKnE4qD51KaNu4KVRT', 'POST', '2026-02-01 01:41:51'),
(130, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=SJbIlOGGg5DXKvQ1K7MgTBWSlSabada0-OgMdWtc4dzFXEAScaFzoMU0_VHJgYTJ78zj7ztUtDgpfRt_5gcd9vy-b_oxpuZNRLbuA_B1uJQ_rJGVymx8oFrZ', 'GET', '2026-02-01 03:09:24'),
(131, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=6xcWJiZVeK7YyGWczPMI65E_Yi8edxO7faVyycVaT95nsRaZJZ-wCKrJ6AU8Q-iQWjzYTmjsio_D0SBhq2I5YB6Shj8-dVSfu5TyYWzDmBSanO4QQNaV_hCq7rqWsJtt', 'POST', '2026-02-01 03:09:43'),
(132, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=RPCvbzN0PfDdg4a2cthHh6pMgV3LQ0o-QvtlcF-Hva-Z_Ei8_JhsQ29qODYqyROMPGUJsfwSQZOHsVWuJq40HWXibUcpTS_GWdzNTerYkEl_-ZfB6AIYqZy9P7UYLB2t', 'POST', '2026-02-01 05:29:39'),
(133, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=KI_TiCyhkWrj_v7B9pmHHpQ32qlvDc9kQNN_waxPqWRJsGpLnLCl8RHkLcrCG3QuzxR-KZOqnMNBIrnz3hJlJLhQ6omoiQLJZgGr3pE22ftX98Nbc-_YgUlL_9I9QQ7r', 'POST', '2026-02-02 02:04:12'),
(134, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=OQ0hOO2h4ZPalcFX0bCfmxcxZWB6phrCAIAikbxAzkWm-50JPiU-MsDqdDyn59Hjq8gl3NG7LW95N11oWUWKaC7cQ_T4JQLUHRIVhOX1si8m-gURz-A0bF_6IJnOpSDM', 'POST', '2026-02-02 03:30:45'),
(135, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=h4XHZ-PhiYJFZ_87TDD9jje7b0mNkoEMeY8y6sLBZCJFcflRye3G0VdG6Zp6GhwaAeN9bCQvHCviNdCLjNgw8Hp8Pn3Zlf27BnHE386n5JVJPd0wHhWsm17GzUS2OwM4', 'POST', '2026-02-02 14:37:37'),
(136, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=G4uhnbONyNL2rurD-tmIF0nD_pVYF6ul_RxVI8XZBBpEs1kSAG8nrgt_rvkrITNVzHw8HJBce-6LpuAXGiKbL_fPUtCJKtisc74TlKO0Wjr4hM-AP5-60JS4oMTBIiMf', 'POST', '2026-02-02 15:22:15'),
(137, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=jwShmKChgG93XsPFZdYJKpQvzPX4XRtzh_iDd4ci1bFTLat7sLOKdaEs1wMXx4PgvruNdS8YTnXJgjVKN6fVqnvCoWAW1lY87qJr8byuzVCgFDVA_fchT2VFfsBiTxAW', 'POST', '2026-02-02 19:31:56'),
(138, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=P3n-Z2hi2cxXoM81bc5ogURJt17dVa1webPvVU1OSVtJ5_agEQFEPVlxAVdJK-ohiDmd6Mg93GXW4w0lSaJby9Ak4udmQlNGLZaF6eEk11FJqY3BeHtp91Ye', 'GET', '2026-02-02 19:38:30'),
(139, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=DZHXif8StL5MTpUJ-5Bz_PIUYuFdL_HK_RINnOxwqMFEt2QNU-EiAe9gRJgO58Y6vd4r1OiCqwc3-EL0QU4_ap47RyFurzNVWmK2Mqf88rJyrm3vsWz0myv6wtpY0b0v', 'POST', '2026-02-02 19:38:42'),
(140, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=1-sCeq-D-v7phVQ5RO7GkzWazN9j1qS4RMBwMH-TGKd8zTIt0R-JMyxDDeuZo7oBoEcne8yz3iOXI_0RvrBW2fttvNjU8BwT0n6ekpkHxA0gyVmJOwe8k3zY', 'GET', '2026-02-02 19:45:27'),
(141, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=YI77FdzFNmkZ9h3yDElkrDY53HP0af1YtvGZ1bnghWUDzXSDNpZFsAEENAd072smpmlggIwaivgZ2GrGPLe0_W7Cq-ZLesLIOmFH9_T-3aMjrUfimtc2nOMGVbmB5rVr', 'POST', '2026-02-02 19:45:38'),
(142, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=pR6YxVabFpZ3COwaOPuFxNM_ibJ5flPKFNVsH5ZOZzHkSI4Zl1iaPzVnlp4dK8ax3xX28Lzh7p3ZH3HmgtsHaskRb_e6fSKML6VE8jJ4M1B42xRZH5krAKK3', 'GET', '2026-02-02 19:51:54'),
(143, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=yQV7qxcinTmufPXjGtfjvAyRjUfB6c3czeYEu6ocSl65JVNloAEp2H8_VpIi2w5Mkum2lxTQmSrNs8ydfjTs5rOpoKqjXwIgpUe-yfU8gIxpqw80pO7EYU6bUnTdS2Id', 'POST', '2026-02-02 19:52:07'),
(144, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=hDlfOVfNUMEXKSCSd6T5RDXk6ztpFqw5ys9zRBX7cH4DDwd_GA-GIESEAgadsYyeUxSdQJMpm7symHpN_Y4LqPbdEaS4CoHNFASUFKN8j83EmUBUfVNOKAhZJl5Vd0u3', 'POST', '2026-02-02 19:55:01'),
(145, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=T9rlOv8-pJqAS5dkHdwG0De3-JSgQwRkQhDsYz2DBiGVtfmVcoaRJ-TyUCItgX497CtGoWrTtfDBDvmjbYI-ZR-UhjJIrGCR2xlCfGaeeGYtoaV8Tfkn1ksE', 'GET', '2026-02-02 20:11:25'),
(146, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=og3bEX7ScOhwa7mmiWL37QyFr9DB4tQX5TVRpkto-2fWaaPEAAb9Ybx0L7Agdyb2IDhCW4UCQ6DGH40SX335tRDYgCKD9DiurvrtEzULFyCR31kxsxJQf_mbk8UNMb6_', 'POST', '2026-02-02 20:11:39'),
(147, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=6fn1iiG7VIAX5TQMC7ww5Dl0ppLk9aQELjx-QjXZCBzjAl7vvsqInlpRKYTj14kIh8JBo2vQ5MydJw6eIzC-90LDsYOp_N-9JtIFvNqq04i7ASUUNqj3afpw', 'GET', '2026-02-02 20:16:48'),
(148, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=-0sHvXvVKsvhVTZeP4wEnjwndScuLjbYUIVHC1ciT2FJ9_Yfv8hvTy8_2i0Ehl3ybtnh3Q8WLa4IYc7a0WAd6vd-JKyZH-5jbvF5UdGCZjHzKCGUwqd596z87D3S2zJ0', 'POST', '2026-02-02 20:17:00'),
(149, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=c9r_Be-mOvaAMmoR6Hd5nnNibvpemEmLmvYxkpde7YS8qgtBbz04fQliC_rFNNUtj9UxbC9KIthd6-_TjToSnEgQsNgjd0vWuEo-96gb-bWwroI-ubjh1mGE', 'GET', '2026-02-02 20:19:23'),
(150, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=_tfn0qeeOcdPYEaHWpfZNiZJTSzEV0oNrZ1roocPV4yw3xE2vOYjMKso7AKo05Di32JVZADz3WPFu2kLc2cyjeaz-HMU5l8b18wGVzUvWCvavXyYP0U29kdRf7CzezkW', 'POST', '2026-02-02 20:19:34'),
(151, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=P7EhjxD3rcKQ5YNn8yWN6mSW7BP2bE4Ms_hKt6MqUgUBwBqac19oh-F3jYD5TPBLCTSope-HIBp-M8-uuZ0hT5zfiXtYqg5XGtO5maKKb5HSyjXiRpxfJlGJM_xY2Pq7', 'POST', '2026-02-02 20:23:42'),
(152, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=kkhaJuICMXU-RMyRZfkS26k1SK5pYkgnVYU3NpBD6Sq5ELhSc3u6BG0MXA4fgX6LBX6RDWI6f--ZKVh5jJQf0sHh8o9-JXJhxUVQg3xcI-toZoA3TitfVzNc', 'GET', '2026-02-02 20:30:47'),
(153, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ESjTnIaWXyMu62_PbPRx180qtKsDl0aoRk1fXrqS9Bs7IbzBRHCKNz9emAYUGRL6g2xUOexwsZ1PwBiKJPJLLwbSFKpIHovI8WrtxB0plODkN8NxvGI6O38gwvpPKlEL', 'POST', '2026-02-02 20:31:00'),
(154, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=cT90zwl4HH8WDlAKCi4yjImgIhpoRgfwvF9BSll64xxiCt3bKXD_inR8UEVH1CXiUqgGuj24Pmp8mpJR9xDe-9G58ZGXuURDBnpNImWYqVgw0pWYFWhT1ORE9bWbm227', 'POST', '2026-02-02 22:18:01'),
(155, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=uNQVgj5D94epIlnGydom97mjVDt7-pHfldgsFCMpdCjz4wcoTUsqvbXjOOvZAmGuRxPXH7nz-4Fa2El-o_lVnbH1XQSctk5fhbgvF2XxwQEnK8P0uFHllrAIdhJsWV-o', 'POST', '2026-02-03 01:13:22'),
(156, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=tvnHzakDNRWTKKCL6eUQQP8oUijhmV6O01jikBruiCF3CzOrqxc0XCsSwErjszWM3xHg-wQlq1bHsMUCFnESJFaeryfmTD4hx5XBMI9xd0Rrwpcbb34tAnXJNJ6QL8TJ', 'POST', '2026-02-03 04:08:48'),
(157, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=tHNLsPcoyX1R3gz1qXfSTuWLkyklJxhpQYR40buW9B6Sk4wxZEv1fsbPsYwelU0CJEM8mSddiapQc0rjKbuagtXx3YLrr8YlFVEF3LwO9bLCn_qX5psDYAIO', 'GET', '2026-02-03 04:09:22'),
(158, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=NOZNB1j4LbE-vjIkCiCzKj0m-QvroHBzXupuejJZgmjb5fZdvUFIscw6DJp5ZbWFu-JL-lhlNvIVPVss4pK3y3_YkEOmEPq5-XfRSUs7cVktOqDGWg_M7TihRj4YT40D', 'POST', '2026-02-03 04:38:36'),
(159, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=TxlJAiojgTmTtSTi6wBpcYzmcVIZAhJmx1wh7bWI3g29aOBh9Qf0ryDWX3JXHi3SC0nuLsKY86GQH4c5Wq25m4FtvB_DfcaB-hELoTVpMPmSlYBSXRf9l2xywNxEa4Tc', 'POST', '2026-02-03 13:29:19'),
(160, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=0w-kj93msUclLr2unb7KLJ0V2UMom5Y9W6u8r6Ht4KVMvGciaVicw60-aDbCW6khUnLbmDxnK3RZqvls1UhFSSEVeljQxYztlqUytw4tzmzx-TifUs7TTG_N', 'GET', '2026-02-03 15:26:06'),
(161, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=D8CDQDcU8J3JwtP7C6F2Jgcf65WQfKYbD8_wD8Zv-Jhdg-SHEnrJaWxxGUiKSGVhLnWFulIWO2fIQeL9nNQSjBXRFzTJ1cI-zB2_ITl54R-1QlPH4d6IWjfTP5O5YH_G', 'POST', '2026-02-03 15:56:42'),
(162, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=A8qx0Uqro1YPSER5TdaEDInAngYigH8PG7ex3tO-FU6vuypPM3XZpvGIesZznAfHx9QF8uNhrsIR9c0sZFByctJUPwJzFWWk5P0GmTa6k3xbE6sxYw9IEOlpXv8TsTyl', 'POST', '2026-02-03 20:33:46'),
(163, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=I5gJPMpT-qC0Bz-U-VnDWuVch2ldaKK1T6p0A9xUwBG9Vz9vQM1eBLXVfzyGhOlcNLtYmK4dmEFk-_LLZgRzCRhBF5VISoMDkEbuEncXrHwlu9v5r5LBSjza-bw7FiQH', 'POST', '2026-02-04 01:55:27'),
(164, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=d1OQ4TeMaFKQ_i_ErxmYOpSx3MF0lOmpH15CY8rDDP4BHpcjaKTGCitg0RGoLPZaX_bOw6YvfxPWBnhrI9EjD_2dCEyzBOSwHMJm624iMaml6iuLQQg7rVw7XoMY5c36', 'POST', '2026-02-04 17:04:43'),
(165, NULL, NULL, 'Core', 'usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=is-8K9s50opTNFSmWykfOCzhpctTHxct7cGYKa0tXIMkujJAYP5VjzsEG0DxMTfc5CbQzCoi6zEFrXCwoM_pOQEsjBmvvwnhB3lqG5FsoWgAJnFK09pMZxodzgSH-MiC', 'POST', '2026-02-04 19:24:56'),
(166, NULL, NULL, 'seguridad', 'usuario', 1, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"fbpinzon@gmail.com\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"0993120984\", \"usu_nombres\": \"Super\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=3$TWJUWG5IRkNQQW5SbjN3Rg$XJtT5D+TcGtMRzBd8mRESuX+a4LVTVtrK5yM+J6eTW4\", \"usu_telefono\": \"0993120984\", \"usu_username\": \"superadmin\", \"usu_apellidos\": \"Administrador\", \"usu_tenant_id\": 1, \"usu_codigo_2fa\": \"798279\", \"usu_usuario_id\": 1, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": \"d46c1a54c78b28260bf588612ead286bf1e0d7218452375938c70b356bcff026\", \"usu_ultimo_login\": \"2026-02-07 11:48:36\", \"usu_fecha_registro\": \"2026-01-24 19:35:10\", \"usu_identificacion\": \"1103345292\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": \"2026-01-24 20:21:48\", \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-07 11:48:36\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": \"2026-02-24 17:56:18\", \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"fbpinzon@gmail.com\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"0993120984\", \"usu_nombres\": \"Super\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=3$TWJUWG5IRkNQQW5SbjN3Rg$XJtT5D+TcGtMRzBd8mRESuX+a4LVTVtrK5yM+J6eTW4\", \"usu_telefono\": \"0993120984\", \"usu_username\": \"superadmin\", \"usu_apellidos\": \"Administrador\", \"usu_tenant_id\": 1, \"usu_codigo_2fa\": \"798279\", \"usu_usuario_id\": 1, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": \"d46c1a54c78b28260bf588612ead286bf1e0d7218452375938c70b356bcff026\", \"usu_ultimo_login\": \"2026-02-07 11:48:36\", \"usu_fecha_registro\": \"2026-01-24 19:35:10\", \"usu_identificacion\": \"1103345292\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": \"2026-01-24 20:21:48\", \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-07 15:41:25\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": \"2026-02-24 17:56:18\", \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=Z7JAey9piUwhmFZaB_gbkh6Ntn6CQAQH7S0h3nhy6DORU3RbJN_WXKup-RY7Hhvwx6S1dqXhJceUQJjrZUb2AqQm2rkohaJUnryL42t7WZryLwXGqL9mjQ6qEe_Mo4AwcIqlHTRLxQE,', 'POST', '2026-02-07 20:41:25'),
(167, NULL, NULL, 'seguridad', 'usuario', 1, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"fbpinzon@gmail.com\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"0993120984\", \"usu_nombres\": \"Super\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=3$TWJUWG5IRkNQQW5SbjN3Rg$XJtT5D+TcGtMRzBd8mRESuX+a4LVTVtrK5yM+J6eTW4\", \"usu_telefono\": \"0993120984\", \"usu_username\": \"superadmin\", \"usu_apellidos\": \"Administrador\", \"usu_tenant_id\": 1, \"usu_codigo_2fa\": \"798279\", \"usu_usuario_id\": 1, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": \"d46c1a54c78b28260bf588612ead286bf1e0d7218452375938c70b356bcff026\", \"usu_ultimo_login\": \"2026-02-07 11:48:36\", \"usu_fecha_registro\": \"2026-01-24 19:35:10\", \"usu_identificacion\": \"1103345292\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": \"2026-01-24 20:21:48\", \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-07 15:41:25\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": \"2026-02-24 17:56:18\", \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"fbpinzon@gmail.com\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"09931209\", \"usu_nombres\": \"Super\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=3$TWJUWG5IRkNQQW5SbjN3Rg$XJtT5D+TcGtMRzBd8mRESuX+a4LVTVtrK5yM+J6eTW4\", \"usu_telefono\": \"0993120984\", \"usu_username\": \"superadmin\", \"usu_apellidos\": \"Administrador\", \"usu_tenant_id\": 1, \"usu_codigo_2fa\": \"798279\", \"usu_usuario_id\": 1, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": \"d46c1a54c78b28260bf588612ead286bf1e0d7218452375938c70b356bcff026\", \"usu_ultimo_login\": \"2026-02-07 11:48:36\", \"usu_fecha_registro\": \"2026-01-24 19:35:10\", \"usu_identificacion\": \"1103345292\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": \"2026-01-24 20:21:48\", \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-07 15:41:32\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": \"2026-02-24 17:56:18\", \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=_GQwuAX-55FBu-688D21kxcN1ZjbKhNT1jJJ_YUT-yx022eJebXZOXEzwMk-A-B3l9ZKecfNHmFpA7ripMeRNgHStuFOQWNHMmSIiAe__Cyw1RcGTtz0FOiFyLdpTstUuM3Jh4855EU,', 'POST', '2026-02-07 20:41:32'),
(168, NULL, NULL, 'seguridad', 'seguridad_modulos', 22, 'editar_modulo', '{\"mod_id\": 22, \"mod_icono\": \"fas fa-shield-alt\", \"mod_orden\": 98, \"mod_activo\": 1, \"mod_codigo\": \"SEGURIDAD\", \"mod_nombre\": \"Seguridad\", \"mod_created_at\": \"2026-02-02 15:52:19\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-02-02 22:41:52\", \"mod_color_fondo\": \"#EF4444\", \"mod_descripcion\": null, \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"seguridad\", \"mod_url_externa\": null, \"mod_ruta_controller\": \"dashboard\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fa-user-shield\", \"mod_orden\": 98, \"mod_activo\": 1, \"mod_codigo\": \"SEGURIDAD\", \"mod_nombre\": \"Seguridad\", \"mod_es_externo\": 0, \"mod_color_fondo\": \"#F59E42\", \"mod_descripcion\": \"\", \"mod_url_externa\": \"\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=wR5L_Bfe3seq5uXKk0WSUN4hVrsS1nb_2YIW2AocsTEB0riJY9VwbZXyq0SmVUcozXGVlnHErza1VHhhwUFVEY_Ll1fAeEa3zoEZeEcAIc4nzGhYIASGnB8aBOIGJSb36g,,', 'POST', '2026-02-07 23:29:24'),
(169, NULL, NULL, 'seguridad', 'seguridad_modulos', 22, 'editar_modulo', '{\"mod_id\": 22, \"mod_icono\": \"fa-user-shield\", \"mod_orden\": 98, \"mod_activo\": 1, \"mod_codigo\": \"SEGURIDAD\", \"mod_nombre\": \"Seguridad\", \"mod_created_at\": \"2026-02-02 15:52:19\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-02-07 18:29:24\", \"mod_color_fondo\": \"#F59E42\", \"mod_descripcion\": \"\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"seguridad\", \"mod_url_externa\": null, \"mod_ruta_controller\": \"dashboard\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fa-shield-alt\", \"mod_orden\": 98, \"mod_activo\": 1, \"mod_codigo\": \"SEGURIDAD\", \"mod_nombre\": \"Seguridad\", \"mod_es_externo\": 0, \"mod_color_fondo\": \"#EF4444\", \"mod_descripcion\": \"\", \"mod_url_externa\": \"\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=d6VMVME5L-iRRDF-GS0sAwJ8hGQSeNavjx5kJ3MC68Tk74wiZeTOPKXw4O9NtlM0mZlTRTTGfOHeS7XPyVZEOUy2SS5iP-NbOzBpS0AmqrZIVdRbKqjB9B0HZHuA-8-MwQ,,', 'POST', '2026-02-07 23:29:47'),
(170, NULL, NULL, 'seguridad', 'seguridad_modulos', 1, 'editar_modulo', '{\"mod_id\": 1, \"mod_icono\": \"fas fa-building\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"instalaciones\", \"mod_nombre\": \"Instalaciones\", \"mod_created_at\": \"2026-01-26 00:37:36\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-01-26 00:37:36\", \"mod_color_fondo\": \"#3B82F6\", \"mod_descripcion\": \"Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"instalaciones\", \"mod_url_externa\": null, \"mod_ruta_controller\": \"cancha\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-building\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"ARENA\", \"mod_nombre\": \"Arena\", \"mod_es_externo\": 0, \"mod_color_fondo\": \"#3B82F6\", \"mod_descripcion\": \"Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.\", \"mod_url_externa\": \"\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=Mot9L8fAu8V_43yuiUVGBChsV63Pu18DGQiUnKhOAmRt-cnC32wXV08PmLb6gwQfB2X7V5y1L2A9UkLroE9PhZs5of-lC5IZ2xBq1-hH6rg2SJq1OCk31Ki5q1oq_scwvg,,', 'POST', '2026-02-07 23:36:40'),
(171, NULL, NULL, 'seguridad', 'seguridad_modulos', 25, 'eliminar_modulo', '{\"mod_id\": 25, \"mod_icono\": \"fas fa-users\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"USUARIOS\", \"mod_nombre\": \"Usuarios\", \"mod_created_at\": \"2026-02-07 17:50:38\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-02-07 17:50:38\", \"mod_color_fondo\": \"#6366F1\", \"mod_descripcion\": \"Gestión de usuarios del sistema\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"usuarios\", \"mod_url_externa\": null, \"mod_ruta_controller\": \"dashboard\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', NULL, '::1', NULL, '/digisports/public/index.php?r=AlHlbvuuuxeBhivRQmlY1scRIDtc4Ao9GUYf1WgCUcWzjlQQeUwrCcRVlDeiWzrVnBP66bvZvxkqnJr-QkYQ12DliUaEu9HMLsAu-0tInwg4kUMNqHc6ukRvdWFEsYkXVzWT', 'POST', '2026-02-07 23:59:39'),
(172, NULL, NULL, 'seguridad', 'seguridad_modulos', 26, 'eliminar_modulo', '{\"mod_id\": 26, \"mod_icono\": \"fas fa-user-shield\", \"mod_orden\": 2, \"mod_activo\": 1, \"mod_codigo\": \"ROLES\", \"mod_nombre\": \"Roles\", \"mod_created_at\": \"2026-02-07 17:50:38\", \"mod_es_externo\": 1, \"mod_updated_at\": \"2026-02-07 17:50:38\", \"mod_color_fondo\": \"#3B82F6\", \"mod_descripcion\": \"Gestión de roles y permisos\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"roles\", \"mod_url_externa\": \"/escuelas/\", \"mod_ruta_controller\": \"dashboard\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": \"digisports\"}', NULL, '::1', NULL, '/digisports/public/index.php?r=AlHlbvuuuxeBhivRQmlY1scRIDtc4Ao9GUYf1WgCUcWzjlQQeUwrCcRVlDeiWzrVnBP66bvZvxkqnJr-QkYQ12DliUaEu9HMLsAu-0tInwg4kUMNqHc6ukRvdWFEsYkXVzWT', 'POST', '2026-02-08 00:00:00'),
(173, NULL, NULL, 'seguridad', 'seguridad_modulos', 28, 'eliminar_modulo', '{\"mod_id\": 28, \"mod_icono\": \"fas fa-th-large\", \"mod_orden\": 4, \"mod_activo\": 1, \"mod_codigo\": \"MODULOS\", \"mod_nombre\": \"Módulos\", \"mod_created_at\": \"2026-02-07 17:50:38\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-02-07 17:50:38\", \"mod_color_fondo\": \"#F59E42\", \"mod_descripcion\": \"Gestión de módulos del sistema\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"modulos\", \"mod_url_externa\": \"/torneos/\", \"mod_ruta_controller\": \"dashboard\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', NULL, '::1', NULL, '/digisports/public/index.php?r=AlHlbvuuuxeBhivRQmlY1scRIDtc4Ao9GUYf1WgCUcWzjlQQeUwrCcRVlDeiWzrVnBP66bvZvxkqnJr-QkYQ12DliUaEu9HMLsAu-0tInwg4kUMNqHc6ukRvdWFEsYkXVzWT', 'POST', '2026-02-08 00:00:05'),
(174, NULL, NULL, 'seguridad', 'seguridad_modulos', 29, 'eliminar_modulo', '{\"mod_id\": 29, \"mod_icono\": \"fas fa-puzzle-piece\", \"mod_orden\": 5, \"mod_activo\": 1, \"mod_codigo\": \"ASIGNACION\", \"mod_nombre\": \"Asignación\", \"mod_created_at\": \"2026-02-07 17:50:38\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-02-07 17:50:38\", \"mod_color_fondo\": \"#EF4444\", \"mod_descripcion\": \"Asignación de módulos a tenants\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"asignacion\", \"mod_url_externa\": \"/inventario/\", \"mod_ruta_controller\": \"dashboard\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', NULL, '::1', NULL, '/digisports/public/index.php?r=AlHlbvuuuxeBhivRQmlY1scRIDtc4Ao9GUYf1WgCUcWzjlQQeUwrCcRVlDeiWzrVnBP66bvZvxkqnJr-QkYQ12DliUaEu9HMLsAu-0tInwg4kUMNqHc6ukRvdWFEsYkXVzWT', 'POST', '2026-02-08 00:00:11'),
(175, NULL, NULL, 'seguridad', 'seguridad_modulos', 1, 'editar_modulo', '{\"mod_id\": 1, \"mod_icono\": \"fas fa-building\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"ARENA\", \"mod_nombre\": \"Arena\", \"mod_created_at\": \"2026-01-26 00:37:36\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-02-07 18:36:40\", \"mod_color_fondo\": \"#3B82F6\", \"mod_descripcion\": \"Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"instalaciones\", \"mod_url_externa\": null, \"mod_ruta_controller\": \"cancha\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-building\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"ARENA\", \"mod_nombre\": \"DigiSports Arena\", \"mod_es_externo\": 0, \"mod_color_fondo\": \"#3B82F6\", \"mod_descripcion\": \"Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.\", \"mod_url_externa\": \"\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=As3wh-B2IW6weFLfEnLzVsMMidWs1IW5HO85DOIFYrYj8VmYF_ShSu5uXW-9yjdSNTdgteiX8XMiqv1SyHZm_N7DfU_AM2Sc54qnkNVvOeGGgPYXvsVJs47YIefB1O_LRA,,', 'POST', '2026-02-08 00:27:26'),
(176, NULL, NULL, 'seguridad', 'seguridad_modulos', 2, 'editar_modulo', '{\"mod_id\": 2, \"mod_icono\": \"fas fa-calendar-check\", \"mod_orden\": 2, \"mod_activo\": 1, \"mod_codigo\": \"reservas\", \"mod_nombre\": \"Reservas\", \"mod_created_at\": \"2026-01-26 00:37:36\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-01-26 00:37:36\", \"mod_color_fondo\": \"#10B981\", \"mod_descripcion\": \"Sistema de reservas por bloques horarios con confirmación automática y recurrencias.\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"reservas\", \"mod_url_externa\": null, \"mod_ruta_controller\": \"reserva\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-calendar-check\", \"mod_orden\": 2, \"mod_activo\": 0, \"mod_codigo\": \"RESERVAS\", \"mod_nombre\": \"Reservas\", \"mod_es_externo\": 0, \"mod_color_fondo\": \"#10B981\", \"mod_descripcion\": \"Sistema de reservas por bloques horarios con confirmación automática y recurrencias.\", \"mod_url_externa\": \"\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=VuDQFI5-BTOT1vAdDg0Cs9ntGpEsRj3LimiR1Ohrl1ZIQvNs-kciMpeWZt-H7hLtvRLgJBheqcQlx4e7_QXjzOu08THrht0g77ahPcTViP0MBLBsQ6sEvhPgUNWkQij7fQ,,', 'POST', '2026-02-08 02:34:00'),
(177, NULL, NULL, 'seguridad', 'seguridad_modulos', 27, 'editar_modulo', '{\"mod_id\": 27, \"mod_icono\": \"fas fa-building\", \"mod_orden\": 3, \"mod_activo\": 1, \"mod_codigo\": \"TENANTS\", \"mod_nombre\": \"Tenants\", \"mod_created_at\": \"2026-02-07 17:50:38\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-02-07 17:50:38\", \"mod_color_fondo\": \"#10B981\", \"mod_descripcion\": \"Gestión de empresas/tenants\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"tenants\", \"mod_url_externa\": \"/instalaciones/\", \"mod_ruta_controller\": \"dashboard\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-building\", \"mod_orden\": 3, \"mod_activo\": 0, \"mod_codigo\": \"TENANTS\", \"mod_nombre\": \"Tenants\", \"mod_es_externo\": 0, \"mod_color_fondo\": \"#10B981\", \"mod_descripcion\": \"Gestión de empresas/tenants\", \"mod_url_externa\": \"/instalaciones/\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=3buc_mzLoaY-JjsLKgwNlu1LKE456AqSUua943ohuKEpYa_VLpWT9RsoxwKUSMQteYBufBwYifqtB2QJR6TmEKC2a4cv3Y-XOvpRGD7AAEGJNgWzbiDvzLc34wpNuTL7WA,,', 'POST', '2026-02-08 02:34:29');
INSERT INTO `seguridad_auditoria` (`aud_auditoria_id`, `aud_tenant_id`, `aud_usuario_id`, `aud_modulo`, `aud_tabla`, `aud_registro_id`, `aud_operacion`, `aud_valores_anteriores`, `aud_valores_nuevos`, `aud_ip`, `aud_user_agent`, `aud_url`, `aud_metodo`, `aud_fecha_operacion`) VALUES
(178, NULL, NULL, 'seguridad', 'seguridad_modulos', 7, 'editar_modulo', '{\"mod_id\": 7, \"mod_icono\": \"fas fa-wallet\", \"mod_orden\": 7, \"mod_activo\": 1, \"mod_codigo\": \"abonos\", \"mod_nombre\": \"Abonos\", \"mod_created_at\": \"2026-01-26 00:37:36\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-01-26 00:37:36\", \"mod_color_fondo\": \"#F472B6\", \"mod_descripcion\": \"Sistema de prepagos y saldos a favor para tus clientes frecuentes.\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"reservas\", \"mod_url_externa\": null, \"mod_ruta_controller\": \"abon\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-wallet\", \"mod_orden\": 7, \"mod_activo\": 0, \"mod_codigo\": \"ABONOS\", \"mod_nombre\": \"Abonos\", \"mod_es_externo\": 0, \"mod_color_fondo\": \"#F472B6\", \"mod_descripcion\": \"Sistema de prepagos y saldos a favor para tus clientes frecuentes.\", \"mod_url_externa\": \"\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=hk7i5ut9tMSCS7ZnKWdhlg2IWhxQ-jAgnYS3nlpOuqDdbPCGYC-dlurB5oOmN6EgzJppdK-WUVVtdtSRsNLrvPwQLaCI5N8GnvBwmk2gAYAtuOZP4DTpYLtokTVg23Wkww,,', 'POST', '2026-02-08 02:34:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_auditoria_logs`
--

DROP TABLE IF EXISTS `seguridad_auditoria_logs`;
CREATE TABLE IF NOT EXISTS `seguridad_auditoria_logs` (
  `log_log_id` bigint NOT NULL AUTO_INCREMENT,
  `log_tenant_id` int DEFAULT NULL,
  `log_usuario_id` int DEFAULT NULL,
  `log_accion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_tabla` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_registro_id` int DEFAULT NULL,
  `log_datos_anteriores` json DEFAULT NULL,
  `log_datos_nuevos` json DEFAULT NULL,
  `log_ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_log_id`),
  KEY `idx_tenant` (`log_tenant_id`),
  KEY `idx_usuario` (`log_usuario_id`),
  KEY `idx_accion` (`log_accion`),
  KEY `idx_fecha` (`log_created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_configuracion_sistema`
--

DROP TABLE IF EXISTS `seguridad_configuracion_sistema`;
CREATE TABLE IF NOT EXISTS `seguridad_configuracion_sistema` (
  `sis_config_id` int NOT NULL AUTO_INCREMENT,
  `sis_tenant_id` int DEFAULT NULL,
  `sis_clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sis_valor` text COLLATE utf8mb4_unicode_ci,
  `sis_tipo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'STRING',
  `sis_descripcion` text COLLATE utf8mb4_unicode_ci,
  `sis_es_editable` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `sis_requiere_reinicio` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `sis_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sis_config_id`),
  UNIQUE KEY `uk_tenant_clave` (`sis_tenant_id`,`sis_clave`),
  KEY `idx_clave` (`sis_clave`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_configuracion_sistema`
--

INSERT INTO `seguridad_configuracion_sistema` (`sis_config_id`, `sis_tenant_id`, `sis_clave`, `sis_valor`, `sis_tipo`, `sis_descripcion`, `sis_es_editable`, `sis_requiere_reinicio`, `sis_fecha_actualizacion`) VALUES
(1, NULL, 'NOMBRE_SISTEMA', 'DigiSports', 'STRING', 'Nombre del sistema', 'S', 'N', '2026-01-25 00:35:10'),
(2, NULL, 'VERSION', '1.0.0', 'STRING', 'Version actual del sistema', 'S', 'N', '2026-01-25 00:35:10'),
(3, NULL, 'EMAIL_SOPORTE', 'soporte@digisports.com', 'STRING', 'Email de soporte', 'S', 'N', '2026-01-25 00:35:10'),
(4, NULL, 'MANTENIMIENTO', 'N', 'BOOLEAN', 'Modo mantenimiento', 'S', 'N', '2026-01-25 00:35:10'),
(5, NULL, 'PERMITIR_REGISTRO', 'S', 'BOOLEAN', 'Permitir auto-registro de tenants', 'S', 'N', '2026-01-25 00:35:10'),
(6, NULL, 'DIAS_PRUEBA', '30', 'INT', 'Dias de prueba gratis', 'S', 'N', '2026-01-25 00:35:10'),
(7, NULL, 'SESSION_TIMEOUT', '1800', 'INT', 'Timeout de sesion en segundos', 'S', 'N', '2026-01-25 00:35:10'),
(8, NULL, 'NOMBRE_SISTEMA', 'DigiSports', 'STRING', 'Nombre del sistema', 'S', 'N', '2026-01-25 00:35:19'),
(9, NULL, 'VERSION', '1.0.0', 'STRING', 'Version actual del sistema', 'S', 'N', '2026-01-25 00:35:19'),
(10, NULL, 'EMAIL_SOPORTE', 'soporte@digisports.com', 'STRING', 'Email de soporte', 'S', 'N', '2026-01-25 00:35:19'),
(11, NULL, 'MANTENIMIENTO', 'N', 'BOOLEAN', 'Modo mantenimiento', 'S', 'N', '2026-01-25 00:35:19'),
(12, NULL, 'PERMITIR_REGISTRO', 'S', 'BOOLEAN', 'Permitir auto-registro de tenants', 'S', 'N', '2026-01-25 00:35:19'),
(13, NULL, 'DIAS_PRUEBA', '30', 'INT', 'Dias de prueba gratis', 'S', 'N', '2026-01-25 00:35:19'),
(14, NULL, 'SESSION_TIMEOUT', '1800', 'INT', 'Timeout de sesion en segundos', 'S', 'N', '2026-01-25 00:35:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_log_accesos`
--

DROP TABLE IF EXISTS `seguridad_log_accesos`;
CREATE TABLE IF NOT EXISTS `seguridad_log_accesos` (
  `acc_log_id` int NOT NULL AUTO_INCREMENT,
  `acc_usuario_id` int DEFAULT NULL,
  `acc_tenant_id` int DEFAULT NULL,
  `acc_fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `acc_tipo` varchar(32) NOT NULL,
  `acc_ip` varchar(45) DEFAULT NULL,
  `acc_user_agent` varchar(255) DEFAULT NULL,
  `acc_exito` char(1) DEFAULT 'S',
  `acc_mensaje` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`acc_log_id`),
  KEY `idx_usuario` (`acc_usuario_id`),
  KEY `idx_tenant` (`acc_tenant_id`),
  KEY `idx_fecha` (`acc_fecha`),
  KEY `idx_tipo` (`acc_tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `seguridad_log_accesos`
--

INSERT INTO `seguridad_log_accesos` (`acc_log_id`, `acc_usuario_id`, `acc_tenant_id`, `acc_fecha`, `acc_tipo`, `acc_ip`, `acc_user_agent`, `acc_exito`, `acc_mensaje`) VALUES
(1, 1, 1, '2026-01-29 17:16:37', 'LOGIN_OK', '127.0.0.1', 'Mozilla/5.0', 'S', 'Acceso correcto'),
(2, 1, 1, '2026-01-29 17:16:37', 'LOGIN_FAILED', '127.0.0.1', 'Mozilla/5.0', 'N', 'Contraseña incorrecta'),
(3, 1, 1, '2026-01-29 17:16:37', 'LOGIN_OK', '127.0.0.1', 'Mozilla/5.0', 'S', 'Acceso correcto'),
(4, 1, 1, '2026-01-29 17:16:37', 'LOGIN_FAILED', '127.0.0.1', 'Mozilla/5.0', 'N', 'Usuario bloqueado'),
(5, 1, 1, '2026-01-29 17:16:37', 'LOGOUT', '127.0.0.1', 'Mozilla/5.0', 'S', 'Cierre de sesión'),
(6, 1, 1, '2026-01-29 17:16:37', 'LOGIN_OK', '127.0.0.1', 'Mozilla/5.0', 'S', 'Acceso correcto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_menu`
--

DROP TABLE IF EXISTS `seguridad_menu`;
CREATE TABLE IF NOT EXISTS `seguridad_menu` (
  `men_id` int NOT NULL AUTO_INCREMENT COMMENT 'PK autoincremental',
  `men_modulo_id` int NOT NULL COMMENT 'FK a seguridad_modulos.mod_id',
  `men_padre_id` int DEFAULT NULL COMMENT 'FK recursiva a seguridad_menu.men_id',
  `men_tipo` enum('HEADER','ITEM','SUBMENU') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ITEM' COMMENT 'HEADER=separador, ITEM=enlace, SUBMENU=sub-enlace',
  `men_label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Texto visible',
  `men_icono` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Clase FontAwesome',
  `men_ruta_modulo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Módulo destino',
  `men_ruta_controller` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Controlador destino',
  `men_ruta_action` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Acción destino',
  `men_url_custom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL personalizada',
  `men_badge` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Texto del badge',
  `men_badge_tipo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo Bootstrap del badge',
  `men_orden` int NOT NULL DEFAULT '0' COMMENT 'Orden dentro de su nivel',
  `men_activo` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=visible, 0=oculto',
  `men_visible_rol` json DEFAULT NULL COMMENT 'Array de rol_ids, NULL=todos',
  `men_tenant_id` int DEFAULT NULL COMMENT 'FK a tenants, NULL=global',
  `men_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `men_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`men_id`),
  KEY `idx_men_modulo` (`men_modulo_id`),
  KEY `idx_men_padre` (`men_padre_id`),
  KEY `idx_men_orden` (`men_modulo_id`,`men_orden`),
  KEY `idx_men_tenant` (`men_tenant_id`),
  KEY `idx_men_activo` (`men_activo`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Menús laterales dinámicos por aplicativo/módulo';

--
-- Volcado de datos para la tabla `seguridad_menu`
--

INSERT INTO `seguridad_menu` (`men_id`, `men_modulo_id`, `men_padre_id`, `men_tipo`, `men_label`, `men_icono`, `men_ruta_modulo`, `men_ruta_controller`, `men_ruta_action`, `men_url_custom`, `men_badge`, `men_badge_tipo`, `men_orden`, `men_activo`, `men_visible_rol`, `men_tenant_id`, `men_created_at`, `men_updated_at`) VALUES
(1, 1, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:00', '2026-02-08 02:21:00'),
(2, 1, 1, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'instalaciones', 'dashboard', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:00', '2026-02-08 02:21:00'),
(3, 1, 1, 'ITEM', 'Canchas', 'fas fa-futbol', 'instalaciones', 'cancha', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:00', '2026-02-08 02:21:00'),
(4, 1, 1, 'ITEM', 'Mantenimientos', 'fas fa-tools', 'instalaciones', 'mantenimiento', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:21:00', '2026-02-08 02:21:00'),
(5, 1, 1, 'ITEM', 'Reservas', 'fas fa-calendar-check', 'reservas', 'reserva', 'index', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-08 02:21:00', '2026-02-08 02:21:00'),
(6, 15, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:09', '2026-02-08 02:21:09'),
(7, 15, 6, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'futbol', 'dashboard', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:09', '2026-02-08 02:21:09'),
(8, 15, 6, 'ITEM', 'Calendario', 'fas fa-calendar-alt', 'futbol', 'calendario', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:09', '2026-02-08 02:21:09'),
(9, 15, NULL, 'HEADER', 'Gestión', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:09', '2026-02-08 02:21:09'),
(10, 15, 9, 'ITEM', 'Canchas', 'fas fa-futbol', 'futbol', 'cancha', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:09', '2026-02-08 02:21:09'),
(11, 15, 9, 'ITEM', 'Reservas', 'fas fa-calendar-check', 'futbol', 'reserva', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:09', '2026-02-08 02:21:09'),
(12, 15, 9, 'ITEM', 'Tarifas', 'fas fa-dollar-sign', 'futbol', 'tarifa', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:21:09', '2026-02-08 02:21:09'),
(13, 15, NULL, 'HEADER', 'Competencias', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:21:09', '2026-02-08 02:21:09'),
(14, 15, 13, 'ITEM', 'Torneos', 'fas fa-trophy', 'futbol', 'torneo', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:09', '2026-02-08 02:21:09'),
(15, 15, 13, 'ITEM', 'Estadísticas', 'fas fa-chart-bar', 'futbol', 'estadistica', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:09', '2026-02-08 02:21:09'),
(16, 16, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(17, 16, 16, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'basket', 'dashboard', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(18, 16, 16, 'ITEM', 'Calendario', 'fas fa-calendar-alt', 'basket', 'calendario', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(19, 16, NULL, 'HEADER', 'Gestión', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(20, 16, 19, 'ITEM', 'Canchas', 'fas fa-basketball-ball', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(21, 16, 20, 'SUBMENU', 'Listado', NULL, 'basket', 'cancha', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(22, 16, 20, 'SUBMENU', 'Tarifas', NULL, 'basket', 'tarifa', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(23, 16, 19, 'ITEM', 'Reservas', 'fas fa-calendar-check', 'basket', 'reserva', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(24, 16, 19, 'ITEM', 'Equipos', 'fas fa-users', 'basket', 'equipo', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(25, 16, NULL, 'HEADER', 'Competencias', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(26, 16, 25, 'ITEM', 'Torneos', 'fas fa-trophy', 'basket', 'torneo', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(27, 16, 25, 'ITEM', 'Ligas', 'fas fa-list-ol', 'basket', 'liga', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(28, 16, 25, 'ITEM', 'Estadísticas', 'fas fa-chart-bar', 'basket', 'estadistica', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(29, 16, NULL, 'HEADER', 'Academia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(30, 16, 29, 'ITEM', 'Escuelas', 'fas fa-graduation-cap', 'basket', 'escuela', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(31, 16, 29, 'ITEM', 'Alumnos', 'fas fa-user-graduate', 'basket', 'alumno', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:22', '2026-02-08 02:21:22'),
(32, 8, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:31', '2026-02-08 02:21:31'),
(33, 8, 32, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'natacion', 'dashboard', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:31', '2026-02-08 02:21:31'),
(34, 8, 32, 'ITEM', 'Horarios', 'fas fa-clock', 'natacion', 'horario', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:31', '2026-02-08 02:21:31'),
(35, 8, NULL, 'HEADER', 'Gestión', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:31', '2026-02-08 02:21:31'),
(36, 8, 35, 'ITEM', 'Piscinas', 'fas fa-swimming-pool', 'natacion', 'piscina', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:31', '2026-02-08 02:21:31'),
(37, 8, 35, 'ITEM', 'Alumnos', 'fas fa-user-graduate', 'natacion', 'alumno', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:31', '2026-02-08 02:21:31'),
(38, 8, 35, 'ITEM', 'Instructores', 'fas fa-chalkboard-teacher', 'natacion', 'instructor', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:21:31', '2026-02-08 02:21:31'),
(39, 8, 35, 'ITEM', 'Niveles', 'fas fa-layer-group', 'natacion', 'nivel', 'index', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-08 02:21:31', '2026-02-08 02:21:31'),
(40, 18, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:38', '2026-02-08 02:21:38'),
(41, 18, 40, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'artes_marciales', 'dashboard', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:38', '2026-02-08 02:21:38'),
(42, 18, NULL, 'HEADER', 'Gestión', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:38', '2026-02-08 02:21:38'),
(43, 18, 42, 'ITEM', 'Alumnos', 'fas fa-user-graduate', 'artes_marciales', 'alumno', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:38', '2026-02-08 02:21:38'),
(44, 18, 42, 'ITEM', 'Instructores', 'fas fa-chalkboard-teacher', 'artes_marciales', 'instructor', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:38', '2026-02-08 02:21:38'),
(45, 18, 42, 'ITEM', 'Cinturones', 'fas fa-ribbon', 'artes_marciales', 'cinturon', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:21:38', '2026-02-08 02:21:38'),
(46, 18, 42, 'ITEM', 'Exámenes', 'fas fa-clipboard-check', 'artes_marciales', 'examen', 'index', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-08 02:21:38', '2026-02-08 02:21:38'),
(47, 19, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:47', '2026-02-08 02:21:47'),
(48, 19, 47, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'ajedrez', 'dashboard', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:47', '2026-02-08 02:21:47'),
(49, 19, NULL, 'HEADER', 'Gestión', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:47', '2026-02-08 02:21:47'),
(50, 19, 49, 'ITEM', 'Jugadores', 'fas fa-chess-king', 'ajedrez', 'jugador', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:47', '2026-02-08 02:21:47'),
(51, 19, 49, 'ITEM', 'Partidas', 'fas fa-chess-board', 'ajedrez', 'partida', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:47', '2026-02-08 02:21:47'),
(52, 19, 49, 'ITEM', 'Rankings', 'fas fa-sort-amount-up', 'ajedrez', 'ranking', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:21:47', '2026-02-08 02:21:47'),
(53, 19, NULL, 'HEADER', 'Competencias', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:21:47', '2026-02-08 02:21:47'),
(54, 19, 53, 'ITEM', 'Torneos', 'fas fa-trophy', 'ajedrez', 'torneo', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:48', '2026-02-08 02:21:48'),
(55, 19, 53, 'ITEM', 'Simultáneas', 'fas fa-chess', 'ajedrez', 'simultanea', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:48', '2026-02-08 02:21:48'),
(56, 20, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:56', '2026-02-08 02:21:56'),
(57, 20, 56, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'multideporte', 'dashboard', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:56', '2026-02-08 02:21:56'),
(58, 20, 56, 'ITEM', 'Calendario', 'fas fa-calendar-alt', 'multideporte', 'calendario', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:56', '2026-02-08 02:21:56'),
(59, 20, NULL, 'HEADER', 'Gestión', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:56', '2026-02-08 02:21:56'),
(60, 20, 59, 'ITEM', 'Deportes', 'fas fa-running', 'multideporte', 'deporte', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:56', '2026-02-08 02:21:56'),
(61, 20, 59, 'ITEM', 'Instalaciones', 'fas fa-building', 'multideporte', 'instalacion', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:56', '2026-02-08 02:21:56'),
(62, 20, 59, 'ITEM', 'Reservas', 'fas fa-calendar-check', 'multideporte', 'reserva', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:21:56', '2026-02-08 02:21:56'),
(63, 20, 59, 'ITEM', 'Alumnos', 'fas fa-user-graduate', 'multideporte', 'alumno', 'index', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-08 02:21:56', '2026-02-08 02:21:56'),
(64, 21, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(65, 21, 64, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'store', 'dashboard', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(66, 21, 64, 'ITEM', 'Punto de Venta', 'fas fa-cash-register', 'store', 'pos', 'index', NULL, 'POS', 'success', 2, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(67, 21, NULL, 'HEADER', 'Catálogo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(68, 21, 67, 'ITEM', 'Productos', 'fas fa-box', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(69, 21, 68, 'SUBMENU', 'Listado', NULL, 'store', 'producto', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(70, 21, 68, 'SUBMENU', 'Nuevo Producto', NULL, 'store', 'producto', 'crear', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(71, 21, 68, 'SUBMENU', 'Importar', NULL, 'store', 'producto', 'importar', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(72, 21, 67, 'ITEM', 'Categorías', 'fas fa-tags', 'store', 'categoria', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(73, 21, 67, 'ITEM', 'Marcas', 'fas fa-trademark', 'store', 'marca', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(74, 21, NULL, 'HEADER', 'Inventario', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(75, 21, 74, 'ITEM', 'Stock', 'fas fa-warehouse', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(76, 21, 75, 'SUBMENU', 'Ver Stock', NULL, 'store', 'stock', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(77, 21, 75, 'SUBMENU', 'Alertas', NULL, 'store', 'stock', 'alertas', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(78, 21, 75, 'SUBMENU', 'Movimientos', NULL, 'store', 'stock', 'movimientos', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:22:09', '2026-02-08 02:22:09'),
(79, 22, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(80, 22, 79, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'seguridad', 'dashboard', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(81, 22, NULL, 'HEADER', 'Administración', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(82, 22, 81, 'ITEM', 'Tenants', 'fas fa-building', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(83, 22, 82, 'SUBMENU', 'Lista de Tenants', NULL, 'seguridad', 'tenant', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(84, 22, 82, 'SUBMENU', 'Nuevo Tenant', NULL, 'seguridad', 'tenant', 'crear', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(85, 22, 82, 'SUBMENU', 'Suscripciones', NULL, 'seguridad', 'tenant', 'suscripciones', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(86, 22, 81, 'ITEM', 'Usuarios', 'fas fa-users', NULL, NULL, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(87, 22, 86, 'SUBMENU', 'Lista de Usuarios', NULL, 'seguridad', 'usuario', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(88, 22, 86, 'SUBMENU', 'Nuevo Usuario', NULL, 'seguridad', 'usuario', 'crear', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(89, 22, 86, 'SUBMENU', 'Usuarios Bloqueados', NULL, 'seguridad', 'usuario', 'bloqueados', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(90, 22, 81, 'ITEM', 'Roles y Permisos', 'fas fa-user-shield', NULL, NULL, NULL, NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(91, 22, 90, 'SUBMENU', 'Lista de Roles', NULL, 'seguridad', 'rol', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(92, 22, 90, 'SUBMENU', 'Nuevo Rol', NULL, 'seguridad', 'rol', 'crear', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(93, 22, 90, 'SUBMENU', 'Matriz de Permisos', NULL, 'seguridad', 'rol', 'permisos', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:22:22', '2026-02-08 02:22:22'),
(94, 22, NULL, 'HEADER', 'Módulos y Apps', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:22:39', '2026-02-08 02:22:39'),
(95, 22, 94, 'ITEM', 'Subsistemas del Core', 'fas fa-puzzle-piece', NULL, NULL, 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:53:31'),
(96, 22, 95, 'SUBMENU', 'Lista de Módulos', NULL, 'seguridad', 'modulo', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:22:40'),
(97, 22, 95, 'SUBMENU', 'Nuevo Módulo', NULL, 'seguridad', 'modulo', 'crear', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:22:40'),
(98, 22, 95, 'SUBMENU', 'Iconos y Colores', NULL, 'seguridad', 'modulo', 'iconos', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:22:40'),
(99, 22, 94, 'ITEM', 'Menús por Aplicativo', 'fas fa-bars', 'seguridad', 'menu', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:22:40'),
(100, 22, 94, 'ITEM', 'Asignación', 'fas fa-link', NULL, NULL, NULL, NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:22:40'),
(101, 22, 100, 'SUBMENU', 'Módulos por Tenant', NULL, 'seguridad', 'asignacion', 'modulos', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:22:40'),
(102, 22, 100, 'SUBMENU', 'Asignación Masiva', NULL, 'seguridad', 'asignacion', 'masiva', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:22:40'),
(103, 22, 100, 'SUBMENU', 'Planes', NULL, 'seguridad', 'plan', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:22:40'),
(104, 22, NULL, 'HEADER', 'Auditoría', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:22:40'),
(105, 22, 104, 'ITEM', 'Logs de Acceso', 'fas fa-history', 'seguridad', 'auditoria', 'accesos', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:22:40'),
(106, 22, 104, 'ITEM', 'Logs de Cambios', 'fas fa-file-alt', 'seguridad', 'auditoria', 'cambios', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:22:40'),
(107, 22, 104, 'ITEM', 'Alertas', 'fas fa-bell', 'seguridad', 'auditoria', 'alertas', NULL, '!', 'danger', 3, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:22:40'),
(108, 22, NULL, 'HEADER', 'Configuración', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:22:40'),
(109, 22, 108, 'ITEM', 'Sistema', 'fas fa-cogs', 'seguridad', 'modulo', 'configuracion', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:54:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_menu_config`
--

DROP TABLE IF EXISTS `seguridad_menu_config`;
CREATE TABLE IF NOT EXISTS `seguridad_menu_config` (
  `con_id` int NOT NULL AUTO_INCREMENT,
  `con_modulo_codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `con_opcion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `con_icono` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `con_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `con_permiso_requerido` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `con_orden` int DEFAULT '0',
  PRIMARY KEY (`con_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_menu_config`
--

INSERT INTO `seguridad_menu_config` (`con_id`, `con_modulo_codigo`, `con_opcion`, `con_icono`, `con_color`, `con_permiso_requerido`, `con_orden`) VALUES
(1, 'instalaciones', 'Instalaciones', 'fas fa-building', '#2563eb', 'instalaciones.ver', 1),
(2, 'reservas', 'Reservas', 'fas fa-calendar-alt', '#22c55e', 'reservas.ver', 2),
(3, 'facturacion', 'Facturación', 'fas fa-file-invoice', '#f59e0b', 'facturacion.ver', 3),
(4, 'reportes', 'Reportes', 'fas fa-chart-bar', '#a21caf', 'reportes.ver', 4),
(5, 'seguridad', 'Seguridad', 'fas fa-shield-alt', '#ef4444', 'seguridad.ver', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_modulos`
--

DROP TABLE IF EXISTS `seguridad_modulos`;
CREATE TABLE IF NOT EXISTS `seguridad_modulos` (
  `mod_id` int NOT NULL AUTO_INCREMENT,
  `mod_codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código único del módulo',
  `mod_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mod_descripcion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mod_icono` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'fas fa-cube' COMMENT 'Clase Font Awesome',
  `mod_color_fondo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#3B82F6' COMMENT 'Color del icono en hex',
  `mod_orden` int DEFAULT '0' COMMENT 'Orden de visualización',
  `mod_ruta_modulo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'module para el router',
  `mod_ruta_controller` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'controller para el router',
  `mod_ruta_action` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'index' COMMENT 'action para el router',
  `mod_es_externo` tinyint(1) DEFAULT '0' COMMENT '1=Sistema externo con su propia BD',
  `mod_url_externa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL si es sistema externo',
  `mod_base_datos_externa` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mod_requiere_licencia` tinyint(1) DEFAULT '1' COMMENT '1=Requiere suscripción',
  `mod_activo` tinyint(1) DEFAULT '1',
  `mod_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `mod_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`mod_id`),
  UNIQUE KEY `codigo` (`mod_codigo`),
  KEY `idx_codigo` (`mod_codigo`),
  KEY `idx_orden` (`mod_orden`),
  KEY `idx_activo` (`mod_activo`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de módulos/aplicaciones disponibles';

--
-- Volcado de datos para la tabla `seguridad_modulos`
--

INSERT INTO `seguridad_modulos` (`mod_id`, `mod_codigo`, `mod_nombre`, `mod_descripcion`, `mod_icono`, `mod_color_fondo`, `mod_orden`, `mod_ruta_modulo`, `mod_ruta_controller`, `mod_ruta_action`, `mod_es_externo`, `mod_url_externa`, `mod_base_datos_externa`, `mod_requiere_licencia`, `mod_activo`, `mod_created_at`, `mod_updated_at`) VALUES
(1, 'ARENA', 'DigiSports Arena', 'Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.', 'fas fa-building', '#3B82F6', 1, 'instalaciones', 'dashboard', 'index', 0, NULL, NULL, 1, 1, '2026-01-26 05:37:36', '2026-02-08 01:31:06'),
(2, 'RESERVAS', 'Reservas', 'Sistema de reservas por bloques horarios con confirmación automática y recurrencias.', 'fas fa-calendar-check', '#10B981', 2, 'reservas', 'reserva', 'index', 0, NULL, NULL, 1, 0, '2026-01-26 05:37:36', '2026-02-08 02:34:00'),
(3, 'facturacion', 'Facturación', 'Comprobantes electrónicos SRI, múltiples formas de pago y pasarelas online.', 'fas fa-file-invoice-dollar', '#F59E0B', 3, 'facturacion', 'comprobante', 'index', 0, NULL, NULL, 1, 1, '2026-01-26 05:37:36', '2026-01-26 05:37:36'),
(4, 'reportes', 'Reportes', 'KPIs, ocupación, ingresos por período y análisis detallado de tu negocio.', 'fas fa-chart-bar', '#8B5CF6', 4, 'reportes', 'kpi', 'index', 0, NULL, NULL, 1, 1, '2026-01-26 05:37:36', '2026-01-26 05:37:36'),
(5, 'escuelas', 'Escuelas', 'Administración completa de escuelas de fútbol, básquet y natación.', 'fas fa-graduation-cap', '#14B8A6', 5, 'escuelas', 'escuela', 'index', 0, NULL, NULL, 1, 1, '2026-01-26 05:37:36', '2026-01-26 05:37:36'),
(6, 'clientes', 'Clientes', 'Registro de socios, público general y empresas con diferentes tarifas.', 'fas fa-users', '#06B6D4', 6, 'clientes', 'cliente', 'index', 0, NULL, NULL, 1, 1, '2026-01-26 05:37:36', '2026-01-26 05:37:36'),
(7, 'ABONOS', 'Abonos', 'Sistema de prepagos y saldos a favor para tus clientes frecuentes.', 'fas fa-wallet', '#F472B6', 7, 'reservas', 'abon', 'index', 0, NULL, NULL, 1, 0, '2026-01-26 05:37:36', '2026-02-08 02:34:53'),
(8, 'NATACION', 'DigiSports Natación', 'estión de piscinas, clases y competencias', 'fas fa-swimmer', '#17a2b8', 8, 'natacion', 'dashboard', 'index', 0, NULL, NULL, 1, 1, '2026-02-07 22:16:13', '2026-02-08 01:12:11'),
(15, 'FUTBOL', 'DigiSports Fútbol', 'Tienda de artículos deportivos, equipamiento y merchandising', 'fas fa-futbol', '#22C55E', 16, 'futbol', 'dashboard', 'index', 0, NULL, NULL, 1, 1, '2026-01-26 06:23:30', '2026-02-03 03:41:52'),
(16, 'BASKET', 'DigiSports Basket', 'Gestión de canchas de basketball y torneos', 'fas fa-basketball-ball', '#fd7e14', 11, 'basket', 'dashboard', 'index', 0, NULL, NULL, 1, 1, '2026-02-02 21:29:27', '2026-02-08 01:42:38'),
(18, 'ARTES_MARCIALES', 'DigiSports Artes Marciales', 'Academias de karate, taekwondo, judo y más', 'fas fa-medal', '#EF4444', 13, 'artes_marciales', 'dashboard', 'index', 0, NULL, '', 1, 1, '2026-02-07 22:50:38', '2026-02-08 01:42:38'),
(19, 'AJEDREZ', 'DigiSports Ajedrez', 'Clubes de ajedrez, torneos y rankings', 'fas fa-chess', '#343a40', 14, 'ajedrez', 'dashboard', 'index', 0, NULL, NULL, 1, 1, '2026-02-07 22:50:38', '2026-02-08 01:42:38'),
(20, 'MULTIDEPORTE', 'DigiSports Multideporte', 'Academias mixtas con múltiples disciplinas', 'fas fa-running', '#6f42c1', 15, 'multideporte', 'dashboard', 'index', 0, NULL, NULL, 1, 1, '2026-02-07 22:50:38', '2026-02-08 01:42:38'),
(21, 'STORE', 'DigiSports Store', 'Tienda de artículos deportivos', 'fas fa-store', '#F59E0B', 16, 'store', 'dashboard', 'index', 0, NULL, '', 1, 1, '2026-02-07 22:50:38', '2026-02-08 01:42:38'),
(22, 'SEGURIDAD', 'Seguridad', '', 'fas fa-shield-alt', '#EF4444', 98, 'seguridad', 'dashboard', 'index', 0, NULL, NULL, 1, 1, '2026-02-02 20:52:19', '2026-02-07 23:51:03'),
(27, 'TENANTS', 'Tenants', 'Gestión de empresas/tenants', 'fas fa-building', '#10B981', 3, 'tenants', 'dashboard', 'index', 0, '/instalaciones/', NULL, 1, 0, '2026-02-07 22:50:38', '2026-02-08 02:34:29'),
(30, 'NUTRICION', 'Planes Nutricionales', 'Seguimiento nutricional de deportistas', 'fas fa-apple-alt', '#fd7e14', 0, 'nutricion', 'dashboard', 'index', 0, '/nutricion/', NULL, 1, 1, '2026-02-07 22:50:38', '2026-02-07 22:50:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_modulos_sistema_deprecated`
--

DROP TABLE IF EXISTS `seguridad_modulos_sistema_deprecated`;
CREATE TABLE IF NOT EXISTS `seguridad_modulos_sistema_deprecated` (
  `sis_modulo_id` int NOT NULL AUTO_INCREMENT,
  `sis_codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sis_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sis_descripcion` text COLLATE utf8mb4_unicode_ci,
  `sis_icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'fa-puzzle-piece',
  `sis_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#007bff',
  `sis_url_base` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL si es sistema externo',
  `sis_es_externo` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'S si apunta a otro sistema',
  `sis_base_datos_externa` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre de BD si es sistema legacy',
  `sis_orden_visualizacion` int DEFAULT '0',
  `sis_requiere_suscripcion` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `sis_estado` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `sis_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sis_modulo_id`),
  UNIQUE KEY `codigo` (`sis_codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_modulos_sistema_deprecated`
--

INSERT INTO `seguridad_modulos_sistema_deprecated` (`sis_modulo_id`, `sis_codigo`, `sis_nombre`, `sis_descripcion`, `sis_icono`, `sis_color`, `sis_url_base`, `sis_es_externo`, `sis_base_datos_externa`, `sis_orden_visualizacion`, `sis_requiere_suscripcion`, `sis_estado`, `sis_fecha_registro`) VALUES
(1, 'USUARIOS', 'Usuarios', 'Gestión de usuarios del sistema', 'fas fa-users', '#6366F1', NULL, 'N', NULL, 1, 'S', 'A', '2026-01-25 00:35:08'),
(2, 'ROLES', 'Roles', 'Gestión de roles y permisos', 'fas fa-user-shield', '#3B82F6', '/escuelas/', 'S', 'digisports', 2, 'S', 'A', '2026-01-25 00:35:08'),
(3, 'TENANTS', 'Tenants', 'Gestión de empresas/tenants', 'fas fa-building', '#10B981', '/instalaciones/', 'N', NULL, 3, 'S', 'A', '2026-01-25 00:35:08'),
(4, 'MODULOS', 'Módulos', 'Gestión de módulos del sistema', 'fas fa-th-large', '#F59E42', '/torneos/', 'N', NULL, 4, 'S', 'A', '2026-01-25 00:35:08'),
(5, 'ASIGNACION', 'Asignación', 'Asignación de módulos a tenants', 'fas fa-puzzle-piece', '#EF4444', '/inventario/', 'N', NULL, 5, 'S', 'A', '2026-01-25 00:35:08'),
(6, 'NUTRICION', 'Planes Nutricionales', 'Seguimiento nutricional de deportistas', 'fas fa-apple-alt', '#fd7e14', '/nutricion/', 'N', NULL, 0, 'S', 'A', '2026-01-25 00:35:08'),
(7, 'REPORTES', 'Reportes y Estadí­sticas', 'KPIs, ocupación, ingresos por período y análisis detallado de tu negocio.', 'fas fa-chart-line', '#6610f2', '/reportes/', 'N', '', 0, 'S', 'A', '2026-01-25 00:35:08'),
(15, 'FUTBOL', 'DigiSports Fútbol', 'Gestión de canchas de fútbol, ligas y torneos', 'fas fa-futbol', '#22C55E', '/digifutbol/', 'N', '', 10, 'S', 'A', '2026-01-26 06:21:29'),
(16, 'BASKET', 'DigiSports Basket', 'Gestión de canchas de basketball y torneos', 'fas fa-basketball-ball', '#fd7e14', '/digibasket/', 'S', NULL, 11, 'S', 'A', '2026-01-26 06:21:29'),
(17, 'NATACION', 'DigiSports Natación', 'Gestión de piscinas, clases y competencias', 'fas fa-swimmer', '#17a2b8', '/diginatacion/', 'S', NULL, 12, 'S', 'A', '2026-01-26 06:21:29'),
(18, 'ARTES_MARCIALES', 'DigiSports Artes Marciales', 'Academias de karate, taekwondo, judo y más', 'fas fa-medal', '#EF4444', '/digimarciales/', 'N', '', 13, 'S', 'A', '2026-01-26 06:21:29'),
(19, 'AJEDREZ', 'DigiSports Ajedrez', 'Clubes de ajedrez, torneos y rankings', 'fas fa-chess', '#343a40', '/digiajedrez/', 'S', NULL, 14, 'S', 'A', '2026-01-26 06:21:29'),
(20, 'MULTIDEPORTE', 'DigiSports Multideporte', 'Academias mixtas con múltiples disciplinas', 'fas fa-running', '#6f42c1', '/digimulti/', 'S', NULL, 15, 'S', 'A', '2026-01-26 06:21:29'),
(21, 'STORE', 'DigiSports Store', 'Tienda de artículos deportivos', 'fas fa-store', '#F59E0B', '/digistore/', 'N', '', 16, 'S', 'A', '2026-01-26 06:21:29'),
(22, 'SEGURIDAD', 'Seguridad', 'Administración del sistema: usuarios, roles, módulos, tenants, asignación y planes', 'fas fa-shield-alt', '#EF4444', '/seguridad/', 'N', '', 98, 'S', 'A', '2026-01-26 15:38:20'),
(23, 'INSTALACIONES', 'DigiSports Instalaciones', 'Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.', 'fas fa-building', '#007bff', '/instalaciones/', 'N', '', 0, 'S', 'A', '2026-01-30 21:27:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_notificaciones`
--

DROP TABLE IF EXISTS `seguridad_notificaciones`;
CREATE TABLE IF NOT EXISTS `seguridad_notificaciones` (
  `not_notificacion_id` int NOT NULL AUTO_INCREMENT,
  `not_tenant_id` int DEFAULT NULL,
  `not_usuario_id` int DEFAULT NULL,
  `not_tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `not_titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `not_mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `not_url_accion` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `not_icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `not_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `not_leida` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `not_fecha_lectura` datetime DEFAULT NULL,
  `not_fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `not_fecha_expiracion` datetime DEFAULT NULL,
  PRIMARY KEY (`not_notificacion_id`),
  KEY `tenant_id` (`not_tenant_id`),
  KEY `idx_usuario` (`not_usuario_id`),
  KEY `idx_leida` (`not_leida`),
  KEY `idx_fecha` (`not_fecha_creacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_notificaciones_log`
--

DROP TABLE IF EXISTS `seguridad_notificaciones_log`;
CREATE TABLE IF NOT EXISTS `seguridad_notificaciones_log` (
  `log_log_id` int NOT NULL AUTO_INCREMENT,
  `log_usuario_id` int DEFAULT NULL,
  `log_tenant_id` int DEFAULT NULL,
  `log_destinatario_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_tipo_notificacion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_asunto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_enviado` tinyint(1) DEFAULT '0',
  `log_error` text COLLATE utf8mb4_unicode_ci,
  `log_fecha_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_log_id`),
  KEY `usuario_id` (`log_usuario_id`),
  KEY `tenant_id` (`log_tenant_id`),
  KEY `destinatario_email` (`log_destinatario_email`(100))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_planes_suscripcion`
--

DROP TABLE IF EXISTS `seguridad_planes_suscripcion`;
CREATE TABLE IF NOT EXISTS `seguridad_planes_suscripcion` (
  `sus_plan_id` int NOT NULL AUTO_INCREMENT,
  `sus_codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sus_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sus_descripcion` text COLLATE utf8mb4_unicode_ci,
  `sus_precio_mensual` decimal(10,2) NOT NULL,
  `sus_precio_anual` decimal(10,2) DEFAULT NULL,
  `sus_descuento_anual` decimal(5,2) DEFAULT '0.00',
  `sus_usuarios_incluidos` int DEFAULT '5',
  `sus_sedes_incluidas` int DEFAULT '1',
  `sus_almacenamiento_gb` int DEFAULT '10',
  `sus_modulos_incluidos` json DEFAULT NULL,
  `sus_caracteristicas` json DEFAULT NULL,
  `sus_es_destacado` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `sus_es_personalizado` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `sus_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#007bff',
  `sus_orden_visualizacion` int DEFAULT '0',
  `sus_estado` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `sus_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sus_plan_id`),
  UNIQUE KEY `codigo` (`sus_codigo`),
  KEY `idx_codigo` (`sus_codigo`),
  KEY `idx_estado` (`sus_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_planes_suscripcion`
--

INSERT INTO `seguridad_planes_suscripcion` (`sus_plan_id`, `sus_codigo`, `sus_nombre`, `sus_descripcion`, `sus_precio_mensual`, `sus_precio_anual`, `sus_descuento_anual`, `sus_usuarios_incluidos`, `sus_sedes_incluidas`, `sus_almacenamiento_gb`, `sus_modulos_incluidos`, `sus_caracteristicas`, `sus_es_destacado`, `sus_es_personalizado`, `sus_color`, `sus_orden_visualizacion`, `sus_estado`, `sus_fecha_registro`) VALUES
(1, 'BASICO', 'Plan Basico', 'Ideal para pequenos centros deportivos', 49.99, 539.89, 0.00, 3, 1, 5, '[\"CORE\", \"INSTALACIONES\"]', NULL, 'N', 'N', '#007bff', 0, 'A', '2026-01-25 00:35:09'),
(2, 'PROFESIONAL', 'Profesional', 'Perfecto para centros en crecimiento', 99.99, 1079.89, 0.00, 10, 3, 25, '[\"CORE\", \"INSTALACIONES\", \"ESCUELAS\", \"TORNEOS\"]', NULL, 'S', 'N', '#007bff', 0, 'A', '2026-01-25 00:35:09'),
(3, 'EMPRESARIAL', 'Plan Empresarial', 'Para cadenas y complejos deportivos', 199.99, 2159.89, 0.00, 50, 10, 100, '[\"CORE\", \"INSTALACIONES\", \"ESCUELAS\", \"TORNEOS\", \"INVENTARIO\", \"NUTRICION\", \"REPORTES\"]', NULL, 'N', 'N', '#007bff', 0, 'A', '2026-01-25 00:35:09'),
(4, 'PERSONALIZADO', 'Plan Personalizado', 'Solucion a medida segun tus necesidades', 0.00, 0.00, 0.00, 100, 20, 500, '[]', NULL, 'N', 'N', '#007bff', 0, 'A', '2026-01-25 00:35:09'),
(9, 'starter', 'Starter', 'Ideal para comenzar', 29.99, 299.99, 0.00, 3, 1, 1, NULL, '[\"Soporte por email\", \"Actualizaciones mensuales\", \"1 m├│dulo deportivo\"]', 'N', 'N', '#6B7280', 0, 'A', '2026-01-26 15:38:20'),
(10, 'enterprise', 'Enterprise', 'Soluci├│n completa para grandes organizaciones', 199.99, 1999.99, 0.00, 50, 1, 50, NULL, '[\"Soporte 24/7 telef├│nico\", \"Actualizaciones prioritarias\", \"Todos los m├│dulos\", \"API personalizada\", \"Capacitaci├│n incluida\"]', 'N', 'N', '#8B5CF6', 0, 'A', '2026-01-26 15:38:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_roles`
--

DROP TABLE IF EXISTS `seguridad_roles`;
CREATE TABLE IF NOT EXISTS `seguridad_roles` (
  `rol_rol_id` int NOT NULL AUTO_INCREMENT,
  `rol_tenant_id` int DEFAULT NULL,
  `rol_codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_descripcion` text COLLATE utf8mb4_unicode_ci,
  `rol_permisos` json DEFAULT NULL,
  `rol_es_super_admin` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `rol_es_admin_tenant` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `rol_puede_modificar_permisos` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `rol_nivel_acceso` int DEFAULT '1',
  `rol_estado` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `rol_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rol_rol_id`),
  UNIQUE KEY `uk_tenant_codigo` (`rol_tenant_id`,`rol_codigo`),
  KEY `idx_codigo` (`rol_codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_roles`
--

INSERT INTO `seguridad_roles` (`rol_rol_id`, `rol_tenant_id`, `rol_codigo`, `rol_nombre`, `rol_descripcion`, `rol_permisos`, `rol_es_super_admin`, `rol_es_admin_tenant`, `rol_puede_modificar_permisos`, `rol_nivel_acceso`, `rol_estado`, `rol_fecha_registro`) VALUES
(1, NULL, 'SUPERADMIN', 'Super Administrador', 'Acceso total al sistema', '[\"*\"]', 'S', 'N', 'N', 10, 'A', '2026-01-25 00:35:09'),
(3, NULL, 'RECEPCION', 'Recepcionista', 'Gestion de reservas y clientes', '[\"reservas.*\", \"clientes.ver\", \"clientes.crear\", \"pagos.crear\"]', 'N', 'N', 'N', 3, 'A', '2026-01-25 00:35:09'),
(4, NULL, 'CLIENTE', 'Cliente', 'Usuario final con acceso limitado', '[\"reservas.ver\", \"reservas.crear\", \"perfil.*\"]', 'N', 'N', 'N', 1, 'A', '2026-01-25 00:35:09'),
(5, 1, 'ADMIN', 'Administrador Tenant', 'Administrador del tenant', '[\"usuarios.*\", \"sedes.*\", \"configuracion.*\"]', 'N', 'S', 'N', 5, 'A', '2026-01-25 00:35:10'),
(6, 1, 'RECEPCION', 'Recepcionista', 'Gestion de reservas y clientes', '[\"reservas.*\", \"clientes.ver\", \"clientes.crear\", \"pagos.crear\"]', 'N', 'N', 'N', 3, 'A', '2026-01-25 00:35:10'),
(7, 1, 'CLIENTE', 'Cliente', 'Usuario final con acceso limitado', '[\"reservas.ver\", \"reservas.crear\", \"perfil.*\"]', 'N', 'N', 'N', 1, 'A', '2026-01-25 00:35:10'),
(8, NULL, 'SUPERADMIN', 'Super Administrador', 'Acceso total al sistema', '[\"*\"]', 'S', 'N', 'N', 10, 'A', '2026-01-25 00:35:19'),
(9, NULL, 'ADMIN', 'Administrador', 'Administrador de tenant', '[\"usuarios.*\", \"sedes.*\", \"configuracion.*\"]', 'N', 'N', 'N', 5, 'A', '2026-01-25 00:35:19'),
(10, NULL, 'RECEPCION', 'Recepcionista', 'Gestion de reservas y clientes', '[\"reservas.*\", \"clientes.ver\", \"clientes.crear\", \"pagos.crear\"]', 'N', 'N', 'N', 3, 'A', '2026-01-25 00:35:19'),
(11, NULL, 'CLIENTE', 'Cliente', 'Usuario final con acceso limitado', '[\"reservas.ver\", \"reservas.crear\", \"perfil.*\"]', 'N', 'N', 'N', 1, 'A', '2026-01-25 00:35:19'),
(15, NULL, 'superadmin', 'Super Administrador', 'Acceso total al sistema', '[\"*\"]', 'S', 'S', 'N', 5, 'A', '2026-01-26 15:38:20'),
(16, NULL, 'admin', 'Administrador', 'Gesti├│n completa del tenant', '[\"dashboard.*\", \"clientes.*\", \"instalaciones.*\", \"reservas.*\", \"facturacion.*\", \"reportes.*\", \"usuarios.ver\", \"usuarios.crear\", \"usuarios.editar\"]', 'N', 'S', 'N', 4, 'A', '2026-01-26 15:38:20'),
(17, NULL, 'operador', 'Operador', 'Operaciones diarias', '[\"dashboard.ver\", \"clientes.ver\", \"clientes.crear\", \"clientes.editar\", \"reservas.*\", \"facturacion.ver\", \"facturacion.crear\"]', 'N', 'N', 'N', 2, 'A', '2026-01-26 15:38:20'),
(18, NULL, 'consulta', 'Consulta', 'Solo lectura', '[\"dashboard.ver\", \"clientes.ver\", \"instalaciones.ver\", \"reservas.ver\", \"facturacion.ver\", \"reportes.ver\"]', 'N', 'N', 'N', 1, 'A', '2026-01-26 15:38:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_rol_menu`
--

DROP TABLE IF EXISTS `seguridad_rol_menu`;
CREATE TABLE IF NOT EXISTS `seguridad_rol_menu` (
  `rme_id` int NOT NULL AUTO_INCREMENT COMMENT 'PK autoincremental',
  `rme_rol_id` int NOT NULL COMMENT 'FK a seguridad_roles.rol_id',
  `rme_menu_id` int NOT NULL COMMENT 'FK a seguridad_menu.men_id',
  `rme_puede_ver` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=visible, 0=oculto para este rol',
  `rme_puede_acceder` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=puede navegar, 0=solo visual sin clic',
  `rme_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `rme_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rme_id`),
  UNIQUE KEY `uk_rol_menu` (`rme_rol_id`,`rme_menu_id`),
  KEY `idx_rme_rol` (`rme_rol_id`),
  KEY `idx_rme_menu` (`rme_menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos de visibilidad de menú por rol';

--
-- Volcado de datos para la tabla `seguridad_rol_menu`
--

INSERT INTO `seguridad_rol_menu` (`rme_id`, `rme_rol_id`, `rme_menu_id`, `rme_puede_ver`, `rme_puede_acceder`, `rme_created_at`, `rme_updated_at`) VALUES
(1, 1, 2, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(2, 1, 3, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(3, 1, 4, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(4, 1, 5, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(5, 1, 7, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(6, 1, 8, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(7, 1, 10, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(8, 1, 11, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(9, 1, 12, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(10, 1, 14, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(11, 1, 15, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(12, 1, 17, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(13, 1, 18, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(14, 1, 20, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(15, 1, 21, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(16, 1, 22, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(17, 1, 23, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(18, 1, 24, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(19, 1, 26, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(20, 1, 27, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(21, 1, 28, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(22, 1, 30, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(23, 1, 31, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(24, 1, 33, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(25, 1, 34, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(26, 1, 36, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(27, 1, 37, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(28, 1, 38, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(29, 1, 39, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(30, 1, 41, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(31, 1, 43, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(32, 1, 44, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(33, 1, 45, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(34, 1, 46, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(35, 1, 48, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(36, 1, 50, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(37, 1, 51, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(38, 1, 52, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(39, 1, 54, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(40, 1, 55, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(41, 1, 57, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(42, 1, 58, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(43, 1, 60, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(44, 1, 61, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(45, 1, 62, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(46, 1, 63, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(47, 1, 65, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(48, 1, 66, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(49, 1, 68, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(50, 1, 69, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(51, 1, 70, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(52, 1, 71, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(53, 1, 72, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(54, 1, 73, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(55, 1, 75, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(56, 1, 76, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(57, 1, 77, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(58, 1, 78, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(59, 1, 80, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(60, 1, 82, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(61, 1, 83, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(62, 1, 84, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(63, 1, 85, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(64, 1, 86, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(65, 1, 87, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(66, 1, 88, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(67, 1, 89, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(68, 1, 90, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(69, 1, 91, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(70, 1, 92, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(71, 1, 93, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(72, 1, 95, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(73, 1, 96, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(74, 1, 97, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(75, 1, 98, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(76, 1, 99, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(77, 1, 100, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(78, 1, 101, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(79, 1, 102, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(80, 1, 103, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(81, 1, 105, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(82, 1, 106, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(83, 1, 107, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(84, 1, 109, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_rol_modulos`
--

DROP TABLE IF EXISTS `seguridad_rol_modulos`;
CREATE TABLE IF NOT EXISTS `seguridad_rol_modulos` (
  `rmo_rol_id` int NOT NULL AUTO_INCREMENT,
  `rmo_rol_rol_id` int NOT NULL,
  `rmo_rol_modulo_id` int NOT NULL,
  `rmo_rol_puede_ver` tinyint(1) DEFAULT '1',
  `rmo_rol_puede_crear` tinyint(1) DEFAULT '0',
  `rmo_rol_puede_editar` tinyint(1) DEFAULT '0',
  `rmo_rol_puede_eliminar` tinyint(1) DEFAULT '0',
  `rmo_rol_permisos_especiales` json DEFAULT NULL COMMENT 'Permisos específicos del módulo',
  `rmo_rol_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rmo_rol_id`),
  UNIQUE KEY `uk_rol_modulo` (`rmo_rol_rol_id`,`rmo_rol_modulo_id`),
  KEY `idx_rol` (`rmo_rol_rol_id`),
  KEY `idx_modulo` (`rmo_rol_modulo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos de roles sobre módulos';

--
-- Volcado de datos para la tabla `seguridad_rol_modulos`
--

INSERT INTO `seguridad_rol_modulos` (`rmo_rol_id`, `rmo_rol_rol_id`, `rmo_rol_modulo_id`, `rmo_rol_puede_ver`, `rmo_rol_puede_crear`, `rmo_rol_puede_editar`, `rmo_rol_puede_eliminar`, `rmo_rol_permisos_especiales`, `rmo_rol_created_at`) VALUES
(1, 1, 1, 1, 1, 1, 1, NULL, '2026-01-26 05:37:36'),
(2, 1, 2, 1, 1, 1, 1, NULL, '2026-01-26 05:37:36'),
(3, 1, 3, 1, 1, 1, 1, NULL, '2026-01-26 05:37:36'),
(4, 1, 4, 1, 1, 1, 1, NULL, '2026-01-26 05:37:36'),
(5, 1, 5, 1, 1, 1, 1, NULL, '2026-01-26 05:37:36'),
(6, 1, 6, 1, 1, 1, 1, NULL, '2026-01-26 05:37:36'),
(7, 1, 7, 1, 1, 1, 1, NULL, '2026-01-26 05:37:36'),
(16, 2, 7, 1, 1, 0, 0, NULL, '2026-01-26 05:37:36'),
(17, 2, 6, 1, 1, 0, 0, NULL, '2026-01-26 05:37:36'),
(18, 2, 2, 1, 1, 0, 0, NULL, '2026-01-26 05:37:36'),
(19, 3, 6, 1, 0, 0, 0, NULL, '2026-01-26 05:37:36'),
(20, 3, 5, 1, 0, 0, 0, NULL, '2026-01-26 05:37:36'),
(23, 1, 15, 1, 1, 1, 1, NULL, '2026-01-26 06:23:48'),
(121, 2, 1, 1, 0, 0, 0, NULL, '2026-02-02 20:08:24'),
(122, 2, 3, 1, 0, 0, 0, NULL, '2026-02-02 20:08:24'),
(123, 2, 4, 1, 0, 0, 0, NULL, '2026-02-02 20:08:24'),
(124, 2, 5, 1, 0, 0, 0, NULL, '2026-02-02 20:08:24'),
(125, 2, 15, 1, 0, 0, 0, NULL, '2026-02-02 20:08:24'),
(137, 2, 22, 1, 0, 0, 0, NULL, '2026-02-02 20:52:24'),
(140, 1, 16, 1, 1, 1, 1, NULL, '2026-02-02 21:29:33'),
(141, 1, 19, 1, 1, 1, 1, NULL, '2026-02-08 01:33:42'),
(142, 1, 18, 1, 1, 1, 1, NULL, '2026-02-08 01:33:42'),
(143, 1, 20, 1, 1, 1, 1, NULL, '2026-02-08 01:33:42'),
(144, 1, 8, 1, 1, 1, 1, NULL, '2026-02-08 01:33:42'),
(145, 1, 21, 1, 1, 1, 1, NULL, '2026-02-08 01:33:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_tarifas`
--

DROP TABLE IF EXISTS `seguridad_tarifas`;
CREATE TABLE IF NOT EXISTS `seguridad_tarifas` (
  `tar_tarifa_id` int NOT NULL AUTO_INCREMENT,
  `tar_cancha_id` int NOT NULL,
  `tar_dia_semana` tinyint NOT NULL COMMENT '0=Domingo, 1=Lunes...6=SÃ¡bado',
  `tar_hora_inicio` time NOT NULL,
  `tar_hora_fin` time NOT NULL,
  `tar_precio` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tar_estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO' COMMENT 'ACTIVO, INACTIVO',
  `tar_fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tar_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tar_tarifa_id`),
  UNIQUE KEY `uk_tarifa_unica` (`tar_cancha_id`,`tar_dia_semana`,`tar_hora_inicio`,`tar_hora_fin`),
  KEY `idx_cancha` (`tar_cancha_id`),
  KEY `idx_dia_semana` (`tar_dia_semana`),
  KEY `idx_horario` (`tar_hora_inicio`,`tar_hora_fin`),
  KEY `idx_estado` (`tar_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tarifas de reservas por cancha, dÃ­a y horario';

--
-- Volcado de datos para la tabla `seguridad_tarifas`
--

INSERT INTO `seguridad_tarifas` (`tar_tarifa_id`, `tar_cancha_id`, `tar_dia_semana`, `tar_hora_inicio`, `tar_hora_fin`, `tar_precio`, `tar_estado`, `tar_fecha_creacion`, `tar_fecha_actualizacion`) VALUES
(1, 8, 1, '17:00:00', '18:00:00', 21.00, 'ACTIVO', '2026-01-25 23:47:38', '2026-01-26 00:27:53'),
(3, 8, 1, '07:00:00', '08:00:00', 15.00, 'ACTIVO', '2026-01-25 23:53:12', '2026-01-26 03:20:41'),
(4, 8, 1, '12:00:00', '13:00:00', 16.00, 'ACTIVO', '2026-01-26 00:28:19', '2026-01-26 00:28:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_tenants`
--

DROP TABLE IF EXISTS `seguridad_tenants`;
CREATE TABLE IF NOT EXISTS `seguridad_tenants` (
  `ten_tenant_id` int NOT NULL AUTO_INCREMENT,
  `ten_ruc` varchar(13) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_razon_social` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_nombre_comercial` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_tipo_empresa` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_direccion` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_celular` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_sitio_web` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_representante_nombre` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_representante_identificacion` varchar(13) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_representante_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_representante_telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_plan_id` int NOT NULL,
  `ten_fecha_inicio` date NOT NULL,
  `ten_fecha_vencimiento` date NOT NULL,
  `ten_estado_suscripcion` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVA',
  `ten_usuarios_permitidos` int DEFAULT '5',
  `ten_sedes_permitidas` int DEFAULT '1',
  `ten_almacenamiento_gb` int DEFAULT '10',
  `ten_logo` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_favicon` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_color_primario` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#007bff',
  `ten_color_secundario` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#6c757d',
  `ten_color_acento` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#28a745',
  `ten_tiene_sistema_antiguo` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `ten_bd_antigua` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_tenant_id_antiguo` int DEFAULT NULL,
  `ten_monto_mensual` decimal(10,2) NOT NULL,
  `ten_dia_corte` int DEFAULT '1',
  `ten_metodo_pago_preferido` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_timezone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'America/Guayaquil',
  `ten_idioma` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'es',
  `ten_moneda` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `ten_estado` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `ten_motivo_suspension` text COLLATE utf8mb4_unicode_ci,
  `ten_fecha_suspension` datetime DEFAULT NULL,
  `ten_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ten_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ten_usuario_registro` int DEFAULT NULL,
  `ten_usuario_actualizacion` int DEFAULT NULL,
  PRIMARY KEY (`ten_tenant_id`),
  UNIQUE KEY `ruc` (`ten_ruc`),
  KEY `plan_id` (`ten_plan_id`),
  KEY `idx_ruc` (`ten_ruc`),
  KEY `idx_estado` (`ten_estado`),
  KEY `idx_email` (`ten_email`),
  KEY `idx_fecha_vencimiento` (`ten_fecha_vencimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_tenants`
--

INSERT INTO `seguridad_tenants` (`ten_tenant_id`, `ten_ruc`, `ten_razon_social`, `ten_nombre_comercial`, `ten_tipo_empresa`, `ten_direccion`, `ten_telefono`, `ten_celular`, `ten_email`, `ten_sitio_web`, `ten_representante_nombre`, `ten_representante_identificacion`, `ten_representante_email`, `ten_representante_telefono`, `ten_plan_id`, `ten_fecha_inicio`, `ten_fecha_vencimiento`, `ten_estado_suscripcion`, `ten_usuarios_permitidos`, `ten_sedes_permitidas`, `ten_almacenamiento_gb`, `ten_logo`, `ten_favicon`, `ten_color_primario`, `ten_color_secundario`, `ten_color_acento`, `ten_tiene_sistema_antiguo`, `ten_bd_antigua`, `ten_tenant_id_antiguo`, `ten_monto_mensual`, `ten_dia_corte`, `ten_metodo_pago_preferido`, `ten_timezone`, `ten_idioma`, `ten_moneda`, `ten_estado`, `ten_motivo_suspension`, `ten_fecha_suspension`, `ten_fecha_registro`, `ten_fecha_actualizacion`, `ten_usuario_registro`, `ten_usuario_actualizacion`) VALUES
(1, '1792261104001', 'DigiSports Administracion', 'DigiSports Admin', '', 'Rey david y los Olivos', '0993120984', '', 'fbpinzon@gmail.com', '', '', '', '', '', 4, '2026-01-24', '2028-01-24', 'ACTIVA', 5, 0, 10, NULL, NULL, '', '', '#28a745', 'N', NULL, NULL, 0.00, 1, NULL, 'America/Guayaquil', 'es', 'USD', 'A', NULL, NULL, '2026-01-25 00:35:10', '2026-02-07 19:45:49', NULL, NULL),
(2, '1104015282001', 'Champions', 'Champios CF 2013', '', '', '0993120984', '', 'fbpinzon@gmail.com', '', '', '', '', '', 2, '2026-01-27', '2029-03-27', 'ACTIVA', 5, 0, 10, NULL, NULL, '', '', '#28a745', 'N', NULL, NULL, 0.00, 1, NULL, 'America/Guayaquil', 'es', 'USD', 'A', NULL, NULL, '2026-01-27 05:27:48', '2026-02-07 21:26:59', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_tenant_configuraciones`
--

DROP TABLE IF EXISTS `seguridad_tenant_configuraciones`;
CREATE TABLE IF NOT EXISTS `seguridad_tenant_configuraciones` (
  `con_config_id` int NOT NULL AUTO_INCREMENT,
  `con_tenant_id` int NOT NULL,
  `con_clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `con_valor` text COLLATE utf8mb4_unicode_ci,
  `con_tipo` enum('string','int','bool','json') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `con_descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `con_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `con_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`con_config_id`),
  UNIQUE KEY `uk_tenant_clave` (`con_tenant_id`,`con_clave`),
  KEY `idx_tenant` (`con_tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_tenant_modulos`
--

DROP TABLE IF EXISTS `seguridad_tenant_modulos`;
CREATE TABLE IF NOT EXISTS `seguridad_tenant_modulos` (
  `tmo_id` int NOT NULL AUTO_INCREMENT,
  `tmo_tenant_id` int NOT NULL,
  `tmo_modulo_id` int NOT NULL,
  `tmo_nombre_personalizado` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tmo_icono_personalizado` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tmo_color_personalizado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tmo_orden_visualizacion` int DEFAULT '0',
  `tmo_activo` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `tmo_fecha_inicio` date NOT NULL,
  `tmo_fecha_fin` date DEFAULT NULL COMMENT 'NULL = sin vencimiento',
  `tmo_estado` enum('ACTIVO','SUSPENDIDO','VENCIDO','CANCELADO') COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `tmo_tipo_licencia` enum('PRUEBA','MENSUAL','ANUAL','PERPETUA') COLLATE utf8mb4_unicode_ci DEFAULT 'MENSUAL',
  `tmo_max_usuarios` int DEFAULT NULL COMMENT 'NULL = ilimitado',
  `tmo_observaciones` text COLLATE utf8mb4_unicode_ci,
  `tmo_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tmo_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tmo_id`),
  UNIQUE KEY `uk_tenant_modulo` (`tmo_tenant_id`,`tmo_modulo_id`),
  KEY `idx_tenant` (`tmo_tenant_id`),
  KEY `idx_modulo` (`tmo_modulo_id`),
  KEY `idx_estado` (`tmo_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=497 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Suscripciones de tenants a módulos';

--
-- Volcado de datos para la tabla `seguridad_tenant_modulos`
--

INSERT INTO `seguridad_tenant_modulos` (`tmo_id`, `tmo_tenant_id`, `tmo_modulo_id`, `tmo_nombre_personalizado`, `tmo_icono_personalizado`, `tmo_color_personalizado`, `tmo_orden_visualizacion`, `tmo_activo`, `tmo_fecha_inicio`, `tmo_fecha_fin`, `tmo_estado`, `tmo_tipo_licencia`, `tmo_max_usuarios`, `tmo_observaciones`, `tmo_created_at`, `tmo_updated_at`) VALUES
(163, 2, 27, NULL, NULL, NULL, 0, 'N', '2026-02-02', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-02 17:31:51', '2026-02-07 22:54:40'),
(166, 2, 30, NULL, NULL, NULL, 0, 'N', '2026-02-02', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-02 17:31:51', '2026-02-07 22:54:40'),
(167, 2, 4, NULL, NULL, NULL, 0, 'N', '2026-02-02', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-02 17:31:51', '2026-02-07 22:54:40'),
(168, 2, 15, NULL, NULL, NULL, 0, 'S', '2026-02-02', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-02 17:31:51', '2026-02-02 17:31:51'),
(475, 1, 1, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-07 23:59:18'),
(476, 1, 2, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-08 01:33:36'),
(477, 1, 3, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-07 23:59:18'),
(478, 1, 4, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-08 01:33:36'),
(479, 1, 5, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-07 23:59:18'),
(480, 1, 6, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-07 23:59:18'),
(481, 1, 7, NULL, NULL, NULL, 0, 'N', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-07 23:59:18'),
(482, 1, 8, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-07 23:59:18'),
(483, 1, 15, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-07 23:59:18'),
(484, 1, 16, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-07 23:59:18'),
(485, 1, 18, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-07 23:59:18'),
(486, 1, 19, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-07 23:59:18'),
(487, 1, 20, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-07 23:59:18'),
(488, 1, 21, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:18', '2026-02-07 23:59:18'),
(489, 1, 22, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:19', '2026-02-07 23:59:19'),
(492, 1, 27, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:19', '2026-02-07 23:59:19'),
(495, 1, 30, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:19', '2026-02-07 23:59:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_usuarios`
--

DROP TABLE IF EXISTS `seguridad_usuarios`;
CREATE TABLE IF NOT EXISTS `seguridad_usuarios` (
  `usu_usuario_id` int NOT NULL AUTO_INCREMENT,
  `usu_tenant_id` int NOT NULL,
  `usu_identificacion` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_nombres` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usu_apellidos` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usu_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usu_telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_celular` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usu_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usu_requiere_2fa` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `usu_codigo_2fa` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_codigo_2fa_expira` datetime DEFAULT NULL,
  `usu_intentos_2fa` int DEFAULT '0',
  `usu_token_recuperacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_token_recuperacion_expira` datetime DEFAULT NULL,
  `usu_rol_id` int NOT NULL,
  `usu_permisos_especiales` json DEFAULT NULL,
  `usu_ultimo_login` datetime DEFAULT NULL,
  `usu_ip_ultimo_login` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_token_sesion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_token_sesion_expira` datetime DEFAULT NULL,
  `usu_sedes_acceso` json DEFAULT NULL,
  `usu_sede_principal_id` int DEFAULT NULL,
  `usu_avatar` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_tema` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'light',
  `usu_idioma` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'es',
  `usu_notificaciones_email` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `usu_notificaciones_push` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `usu_debe_cambiar_password` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `usu_password_expira` date DEFAULT NULL,
  `usu_intentos_fallidos` int DEFAULT '0',
  `usu_bloqueado_hasta` datetime DEFAULT NULL,
  `usu_estado` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `usu_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usu_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`usu_usuario_id`),
  UNIQUE KEY `username` (`usu_username`),
  UNIQUE KEY `uk_tenant_email` (`usu_tenant_id`,`usu_email`),
  KEY `rol_id` (`usu_rol_id`),
  KEY `idx_username` (`usu_username`),
  KEY `idx_email` (`usu_email`),
  KEY `idx_estado` (`usu_estado`),
  KEY `idx_tenant` (`usu_tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_usuarios`
--

INSERT INTO `seguridad_usuarios` (`usu_usuario_id`, `usu_tenant_id`, `usu_identificacion`, `usu_nombres`, `usu_apellidos`, `usu_email`, `usu_telefono`, `usu_celular`, `usu_username`, `usu_password`, `usu_requiere_2fa`, `usu_codigo_2fa`, `usu_codigo_2fa_expira`, `usu_intentos_2fa`, `usu_token_recuperacion`, `usu_token_recuperacion_expira`, `usu_rol_id`, `usu_permisos_especiales`, `usu_ultimo_login`, `usu_ip_ultimo_login`, `usu_token_sesion`, `usu_token_sesion_expira`, `usu_sedes_acceso`, `usu_sede_principal_id`, `usu_avatar`, `usu_tema`, `usu_idioma`, `usu_notificaciones_email`, `usu_notificaciones_push`, `usu_debe_cambiar_password`, `usu_password_expira`, `usu_intentos_fallidos`, `usu_bloqueado_hasta`, `usu_estado`, `usu_fecha_registro`, `usu_fecha_actualizacion`) VALUES
(1, 1, '1103345292', 'Super', 'Administrador', 'fbpinzon@gmail.com', '0993120984', '09931209', 'superadmin', '$argon2id$v=19$m=65536,t=4,p=3$TWJUWG5IRkNQQW5SbjN3Rg$XJtT5D+TcGtMRzBd8mRESuX+a4LVTVtrK5yM+J6eTW4', 'N', '798279', '2026-01-24 20:21:48', 0, NULL, NULL, 1, NULL, '2026-02-07 18:17:29', '::1', 'd46c1a54c78b28260bf588612ead286bf1e0d7218452375938c70b356bcff026', '2026-02-24 17:56:18', NULL, NULL, NULL, 'light', 'es', 'S', 'S', 'N', NULL, 0, NULL, 'A', '2026-01-25 00:35:10', '2026-02-07 23:17:29'),
(3, 2, '1103345292', 'Freddy', 'Pinzón', 'fbpinzon@gmail.com', '0993120984', '', 'fbpinzon', '$argon2id$v=19$m=65536,t=4,p=1$dVNpUFJ1cTlDMFJieGVtUQ$6Yemzxhz01i9O3cwvnZLj7QP21uCrqtjoBFejtvYjhw', 'N', NULL, NULL, 0, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', 'es', 'S', 'S', 'N', NULL, 0, NULL, 'A', '2026-01-29 22:22:55', '2026-02-07 20:21:49');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_estadisticas_canchas`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vw_estadisticas_canchas`;
CREATE TABLE IF NOT EXISTS `vw_estadisticas_canchas` (
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_tarifas_por_dia`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vw_tarifas_por_dia`;
CREATE TABLE IF NOT EXISTS `vw_tarifas_por_dia` (
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_facturas_electronicas_resumen`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `v_facturas_electronicas_resumen`;
CREATE TABLE IF NOT EXISTS `v_facturas_electronicas_resumen` (
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_modulos_usuario`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `v_modulos_usuario`;
CREATE TABLE IF NOT EXISTS `v_modulos_usuario` (
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_estadisticas_canchas`
--
DROP TABLE IF EXISTS `vw_estadisticas_canchas`;

DROP VIEW IF EXISTS `vw_estadisticas_canchas`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_estadisticas_canchas`  AS SELECT `c`.`cancha_id` AS `cancha_id`, `c`.`tenant_id` AS `tenant_id`, `c`.`nombre` AS `nombre`, `c`.`tipo` AS `tipo`, count(distinct `t`.`tarifa_id`) AS `total_tarifas`, count(distinct `m`.`mantenimiento_id`) AS `total_mantenimientos`, count(distinct (case when (`m`.`estado` = 'COMPLETADO') then `m`.`mantenimiento_id` end)) AS `mantenimientos_completados`, count(distinct (case when (`m`.`estado` in ('PROGRAMADO','EN_PROGRESO')) then `m`.`mantenimiento_id` end)) AS `mantenimientos_pendientes` FROM ((`canchas` `c` left join `tarifas` `t` on((`c`.`cancha_id` = `t`.`cancha_id`))) left join `mantenimientos` `m` on((`c`.`cancha_id` = `m`.`cancha_id`))) GROUP BY `c`.`cancha_id`, `c`.`tenant_id`, `c`.`nombre`, `c`.`tipo` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_tarifas_por_dia`
--
DROP TABLE IF EXISTS `vw_tarifas_por_dia`;

DROP VIEW IF EXISTS `vw_tarifas_por_dia`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_tarifas_por_dia`  AS SELECT `t`.`tarifa_id` AS `tarifa_id`, `c`.`cancha_id` AS `cancha_id`, `c`.`nombre` AS `cancha_nombre`, `c`.`tipo` AS `cancha_tipo`, `t`.`dia_semana` AS `dia_semana`, (case `t`.`dia_semana` when 0 then 'Domingo' when 1 then 'Lunes' when 2 then 'Martes' when 3 then 'MiÃ©rcoles' when 4 then 'Jueves' when 5 then 'Viernes' when 6 then 'SÃ¡bado' end) AS `dia_nombre`, `t`.`hora_inicio` AS `hora_inicio`, `t`.`hora_fin` AS `hora_fin`, `t`.`precio` AS `precio`, `t`.`estado` AS `estado`, `c`.`tenant_id` AS `tenant_id` FROM (`tarifas` `t` join `canchas` `c` on((`t`.`cancha_id` = `c`.`cancha_id`))) ORDER BY `c`.`tenant_id` ASC, `c`.`nombre` ASC, `t`.`dia_semana` ASC, `t`.`hora_inicio` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_facturas_electronicas_resumen`
--
DROP TABLE IF EXISTS `v_facturas_electronicas_resumen`;

DROP VIEW IF EXISTS `v_facturas_electronicas_resumen`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_facturas_electronicas_resumen`  AS SELECT `fe`.`id` AS `id`, `fe`.`tenant_id` AS `tenant_id`, `fe`.`clave_acceso` AS `clave_acceso`, concat(`fe`.`establecimiento`,'-',`fe`.`punto_emision`,'-',`fe`.`secuencial`) AS `numero_factura`, `fe`.`fecha_emision` AS `fecha_emision`, `fe`.`cliente_identificacion` AS `cliente_identificacion`, `fe`.`cliente_razon_social` AS `cliente_razon_social`, `fe`.`subtotal` AS `subtotal`, `fe`.`iva` AS `iva`, `fe`.`total` AS `total`, `fe`.`estado_sri` AS `estado_sri`, (case `fe`.`estado_sri` when 'AUTORIZADO' then 'success' when 'PENDIENTE' then 'warning' when 'GENERADA' then 'info' when 'FIRMADA' then 'info' when 'ENVIADA' then 'primary' when 'RECIBIDA' then 'primary' when 'DEVUELTA' then 'danger' when 'NO_AUTORIZADO' then 'danger' when 'ERROR' then 'danger' when 'ANULADA' then 'secondary' else 'secondary' end) AS `badge_class`, `fe`.`numero_autorizacion` AS `numero_autorizacion`, `fe`.`fecha_autorizacion` AS `fecha_autorizacion`, `fe`.`ambiente` AS `ambiente`, (case `fe`.`ambiente` when '1' then 'PRUEBAS' when '2' then 'PRODUCCIÓN' end) AS `ambiente_texto`, `fe`.`intentos_envio` AS `intentos_envio`, `fe`.`mensaje_error` AS `mensaje_error`, `fe`.`created_at` AS `created_at` FROM `facturas_electronicas` AS `fe` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_modulos_usuario`
--
DROP TABLE IF EXISTS `v_modulos_usuario`;

DROP VIEW IF EXISTS `v_modulos_usuario`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_modulos_usuario`  AS SELECT `m`.`id` AS `modulo_id`, `m`.`codigo` AS `codigo`, `m`.`nombre` AS `nombre`, `m`.`descripcion` AS `descripcion`, `m`.`icono` AS `icono`, `m`.`color_fondo` AS `color_fondo`, `m`.`orden` AS `orden`, `m`.`ruta_modulo` AS `ruta_modulo`, `m`.`ruta_controller` AS `ruta_controller`, `m`.`ruta_action` AS `ruta_action`, `m`.`es_externo` AS `es_externo`, `m`.`url_externa` AS `url_externa`, `tm`.`tenant_id` AS `tenant_id`, `tm`.`estado` AS `estado_suscripcion`, `tm`.`fecha_fin` AS `fecha_fin`, `rm`.`rol_id` AS `rol_id`, `rm`.`puede_ver` AS `puede_ver`, `rm`.`puede_crear` AS `puede_crear`, `rm`.`puede_editar` AS `puede_editar`, `rm`.`puede_eliminar` AS `puede_eliminar` FROM ((`modulos` `m` join `tenant_modulos` `tm` on(((`m`.`id` = `tm`.`modulo_id`) and (`tm`.`estado` = 'ACTIVO')))) join `rol_modulos` `rm` on(((`m`.`id` = `rm`.`modulo_id`) and (`rm`.`puede_ver` = 1)))) WHERE (`m`.`activo` = 1) ORDER BY `m`.`orden` ASC ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `instalaciones_canchas`
--
ALTER TABLE `instalaciones_canchas` ADD FULLTEXT KEY `ft_nombre` (`can_nombre`,`can_descripcion`);

--
-- Indices de la tabla `instalaciones_mantenimientos`
--
ALTER TABLE `instalaciones_mantenimientos` ADD FULLTEXT KEY `ft_descripcion` (`man_descripcion`);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`cli_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `facturas_electronicas_detalle`
--
ALTER TABLE `facturas_electronicas_detalle`
  ADD CONSTRAINT `facturas_electronicas_detalle_ibfk_1` FOREIGN KEY (`det_factura_electronica_id`) REFERENCES `facturas_electronicas` (`fac_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `facturas_electronicas_detalle_impuestos`
--
ALTER TABLE `facturas_electronicas_detalle_impuestos`
  ADD CONSTRAINT `facturas_electronicas_detalle_impuestos_ibfk_1` FOREIGN KEY (`imp_detalle_id`) REFERENCES `facturas_electronicas_detalle` (`det_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `facturas_electronicas_info_adicional`
--
ALTER TABLE `facturas_electronicas_info_adicional`
  ADD CONSTRAINT `facturas_electronicas_info_adicional_ibfk_1` FOREIGN KEY (`adi_factura_electronica_id`) REFERENCES `facturas_electronicas` (`fac_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `facturas_electronicas_log`
--
ALTER TABLE `facturas_electronicas_log`
  ADD CONSTRAINT `facturas_electronicas_log_ibfk_1` FOREIGN KEY (`log_factura_electronica_id`) REFERENCES `facturas_electronicas` (`fac_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `facturas_electronicas_pagos`
--
ALTER TABLE `facturas_electronicas_pagos`
  ADD CONSTRAINT `facturas_electronicas_pagos_ibfk_1` FOREIGN KEY (`pag_factura_electronica_id`) REFERENCES `facturas_electronicas` (`fac_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `facturas_suscripcion`
--
ALTER TABLE `facturas_suscripcion`
  ADD CONSTRAINT `facturas_suscripcion_ibfk_1` FOREIGN KEY (`sus_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `instalaciones`
--
ALTER TABLE `instalaciones`
  ADD CONSTRAINT `instalaciones_ibfk_1` FOREIGN KEY (`ins_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instalaciones_ibfk_2` FOREIGN KEY (`ins_sede_id`) REFERENCES `instalaciones_sedes` (`sed_sede_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instalaciones_ibfk_3` FOREIGN KEY (`ins_tipo_instalacion_id`) REFERENCES `instalaciones_tipos_instalacion` (`tip_tipo_id`);

--
-- Filtros para la tabla `instalaciones_abonos`
--
ALTER TABLE `instalaciones_abonos`
  ADD CONSTRAINT `instalaciones_abonos_ibfk_1` FOREIGN KEY (`abo_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instalaciones_abonos_ibfk_2` FOREIGN KEY (`abo_cliente_id`) REFERENCES `clientes` (`cli_cliente_id`);

--
-- Filtros para la tabla `instalaciones_canchas`
--
ALTER TABLE `instalaciones_canchas`
  ADD CONSTRAINT `fk_cancha_instalacion` FOREIGN KEY (`can_instalacion_id`) REFERENCES `instalaciones` (`ins_instalacion_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cancha_tenant` FOREIGN KEY (`can_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `instalaciones_disponibilidad_canchas`
--
ALTER TABLE `instalaciones_disponibilidad_canchas`
  ADD CONSTRAINT `fk_disp_cancha` FOREIGN KEY (`dis_cancha_id`) REFERENCES `instalaciones_canchas` (`can_cancha_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `instalaciones_eventos_canchas`
--
ALTER TABLE `instalaciones_eventos_canchas`
  ADD CONSTRAINT `fk_evento_cancha` FOREIGN KEY (`eve_cancha_id`) REFERENCES `instalaciones_canchas` (`can_cancha_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_evento_usuario` FOREIGN KEY (`eve_usuario_id`) REFERENCES `seguridad_usuarios` (`usu_usuario_id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `instalaciones_instalacion_bloqueos`
--
ALTER TABLE `instalaciones_instalacion_bloqueos`
  ADD CONSTRAINT `instalaciones_instalacion_bloqueos_ibfk_1` FOREIGN KEY (`blo_instalacion_id`) REFERENCES `instalaciones` (`ins_instalacion_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `instalaciones_instalacion_horarios`
--
ALTER TABLE `instalaciones_instalacion_horarios`
  ADD CONSTRAINT `instalaciones_instalacion_horarios_ibfk_1` FOREIGN KEY (`hor_instalacion_id`) REFERENCES `instalaciones` (`ins_instalacion_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `instalaciones_instalacion_tarifas`
--
ALTER TABLE `instalaciones_instalacion_tarifas`
  ADD CONSTRAINT `instalaciones_instalacion_tarifas_ibfk_1` FOREIGN KEY (`tar_instalacion_id`) REFERENCES `instalaciones` (`ins_instalacion_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `instalaciones_mantenimientos`
--
ALTER TABLE `instalaciones_mantenimientos`
  ADD CONSTRAINT `fk_mnt_cancha` FOREIGN KEY (`man_cancha_id`) REFERENCES `instalaciones_canchas` (`can_cancha_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mnt_responsable` FOREIGN KEY (`man_responsable_id`) REFERENCES `seguridad_usuarios` (`usu_usuario_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mnt_tenant` FOREIGN KEY (`man_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `instalaciones_reservas`
--
ALTER TABLE `instalaciones_reservas`
  ADD CONSTRAINT `instalaciones_reservas_ibfk_1` FOREIGN KEY (`res_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instalaciones_reservas_ibfk_2` FOREIGN KEY (`res_instalacion_id`) REFERENCES `instalaciones` (`ins_instalacion_id`),
  ADD CONSTRAINT `instalaciones_reservas_ibfk_3` FOREIGN KEY (`res_cliente_id`) REFERENCES `clientes` (`cli_cliente_id`),
  ADD CONSTRAINT `instalaciones_reservas_ibfk_4` FOREIGN KEY (`res_reserva_padre_id`) REFERENCES `instalaciones_reservas` (`res_reserva_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `instalaciones_reserva_pagos`
--
ALTER TABLE `instalaciones_reserva_pagos`
  ADD CONSTRAINT `instalaciones_reserva_pagos_ibfk_1` FOREIGN KEY (`pag_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instalaciones_reserva_pagos_ibfk_2` FOREIGN KEY (`pag_reserva_id`) REFERENCES `instalaciones_reservas` (`res_reserva_id`);

--
-- Filtros para la tabla `instalaciones_sedes`
--
ALTER TABLE `instalaciones_sedes`
  ADD CONSTRAINT `instalaciones_sedes_ibfk_1` FOREIGN KEY (`sed_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `instalaciones_tipos_instalacion`
--
ALTER TABLE `instalaciones_tipos_instalacion`
  ADD CONSTRAINT `instalaciones_tipos_instalacion_ibfk_1` FOREIGN KEY (`tip_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seguridad_auditoria`
--
ALTER TABLE `seguridad_auditoria`
  ADD CONSTRAINT `seguridad_auditoria_ibfk_1` FOREIGN KEY (`aud_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seguridad_auditoria_ibfk_2` FOREIGN KEY (`aud_usuario_id`) REFERENCES `seguridad_usuarios` (`usu_usuario_id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `seguridad_configuracion_sistema`
--
ALTER TABLE `seguridad_configuracion_sistema`
  ADD CONSTRAINT `seguridad_configuracion_sistema_ibfk_1` FOREIGN KEY (`sis_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seguridad_menu`
--
ALTER TABLE `seguridad_menu`
  ADD CONSTRAINT `fk_men_modulo` FOREIGN KEY (`men_modulo_id`) REFERENCES `seguridad_modulos` (`mod_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_men_padre` FOREIGN KEY (`men_padre_id`) REFERENCES `seguridad_menu` (`men_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `seguridad_notificaciones`
--
ALTER TABLE `seguridad_notificaciones`
  ADD CONSTRAINT `seguridad_notificaciones_ibfk_1` FOREIGN KEY (`not_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seguridad_notificaciones_ibfk_2` FOREIGN KEY (`not_usuario_id`) REFERENCES `seguridad_usuarios` (`usu_usuario_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seguridad_roles`
--
ALTER TABLE `seguridad_roles`
  ADD CONSTRAINT `seguridad_roles_ibfk_1` FOREIGN KEY (`rol_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seguridad_rol_menu`
--
ALTER TABLE `seguridad_rol_menu`
  ADD CONSTRAINT `fk_rme_menu` FOREIGN KEY (`rme_menu_id`) REFERENCES `seguridad_menu` (`men_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `seguridad_rol_modulos`
--
ALTER TABLE `seguridad_rol_modulos`
  ADD CONSTRAINT `seguridad_rol_modulos_ibfk_1` FOREIGN KEY (`rmo_rol_modulo_id`) REFERENCES `seguridad_modulos` (`mod_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seguridad_tarifas`
--
ALTER TABLE `seguridad_tarifas`
  ADD CONSTRAINT `fk_tarifa_cancha` FOREIGN KEY (`tar_cancha_id`) REFERENCES `instalaciones_canchas` (`can_cancha_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seguridad_tenants`
--
ALTER TABLE `seguridad_tenants`
  ADD CONSTRAINT `seguridad_tenants_ibfk_1` FOREIGN KEY (`ten_plan_id`) REFERENCES `seguridad_planes_suscripcion` (`sus_plan_id`);

--
-- Filtros para la tabla `seguridad_tenant_modulos`
--
ALTER TABLE `seguridad_tenant_modulos`
  ADD CONSTRAINT `fk_tenant_modulos_modulo_id` FOREIGN KEY (`tmo_modulo_id`) REFERENCES `seguridad_modulos` (`mod_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `seguridad_usuarios`
--
ALTER TABLE `seguridad_usuarios`
  ADD CONSTRAINT `seguridad_usuarios_ibfk_1` FOREIGN KEY (`usu_tenant_id`) REFERENCES `seguridad_tenants` (`ten_tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seguridad_usuarios_ibfk_2` FOREIGN KEY (`usu_rol_id`) REFERENCES `seguridad_roles` (`rol_rol_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
