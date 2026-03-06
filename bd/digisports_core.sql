-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 04-03-2026 a las 19:52:54
-- Versión del servidor: 8.4.7
-- Versión de PHP: 8.3.28

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
-- Estructura de tabla para la tabla `alumnos`
--

DROP TABLE IF EXISTS `alumnos`;
CREATE TABLE IF NOT EXISTS `alumnos` (
  `alu_alumno_id` int NOT NULL AUTO_INCREMENT,
  `alu_tenant_id` int NOT NULL,
  `alu_sede_id` int DEFAULT NULL COMMENT 'FK → instalaciones_sedes',
  `alu_representante_id` int DEFAULT NULL COMMENT 'FK ??? clientes (padre/madre/tutor)',
  `alu_parentesco` enum('PADRE','MADRE','TUTOR','ABUELO','OTRO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PADRE',
  `alu_tipo_identificacion` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'CED, PAS, OTR',
  `alu_identificacion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cifrado ENC::...',
  `alu_identificacion_hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Blind index para b??squedas',
  `alu_nombres` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alu_apellidos` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alu_fecha_nacimiento` date DEFAULT NULL,
  `alu_genero` enum('M','F','O') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alu_foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alu_tipo_sangre` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A+, A-, B+, B-, AB+, AB-, O+, O-',
  `alu_alergias` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `alu_condiciones_medicas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `alu_medicamentos` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `alu_contacto_emergencia` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alu_telefono_emergencia` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alu_estado` enum('ACTIVO','INACTIVO','SUSPENDIDO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `alu_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `alu_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `alu_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `alu_observaciones_medicas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`alu_alumno_id`),
  KEY `idx_alu_tenant` (`alu_tenant_id`),
  KEY `idx_alu_representante` (`alu_representante_id`),
  KEY `idx_alu_identificacion_hash` (`alu_identificacion_hash`),
  KEY `idx_alu_estado` (`alu_tenant_id`,`alu_estado`),
  KEY `idx_alu_nombres` (`alu_tenant_id`,`alu_apellidos`,`alu_nombres`),
  KEY `idx_alu_sede` (`alu_sede_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla compartida de alumnos ??? todos los subsistemas deportivos';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `cli_cliente_id` int NOT NULL AUTO_INCREMENT,
  `cli_tenant_id` int NOT NULL,
  `cli_tipo_identificacion` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cli_identificacion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_identificacion_hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_nombres` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cli_apellidos` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cli_email` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_email_hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_telefono` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_celular` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_direccion` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cli_fecha_nacimiento` date DEFAULT NULL,
  `cli_tipo_cliente` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PUBLICO',
  `cli_saldo_abono` decimal(10,2) DEFAULT '0.00',
  `cli_estado` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `cli_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `cli_consentimiento_datos` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Consentimiento tratamiento datos del representado',
  `cli_consentimiento_fecha` datetime DEFAULT NULL COMMENT 'Fecha/hora del consentimiento',
  `cli_consentimiento_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP del consentimiento',
  PRIMARY KEY (`cli_cliente_id`),
  UNIQUE KEY `uk_tenant_identificacion` (`cli_tenant_id`,`cli_identificacion`),
  KEY `idx_email` (`cli_email`),
  KEY `idx_cli_identificacion_hash` (`cli_identificacion_hash`),
  KEY `idx_cli_email_hash` (`cli_email_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`cli_cliente_id`, `cli_tenant_id`, `cli_tipo_identificacion`, `cli_identificacion`, `cli_identificacion_hash`, `cli_nombres`, `cli_apellidos`, `cli_email`, `cli_email_hash`, `cli_telefono`, `cli_celular`, `cli_direccion`, `cli_fecha_nacimiento`, `cli_tipo_cliente`, `cli_saldo_abono`, `cli_estado`, `cli_fecha_registro`, `cli_consentimiento_datos`, `cli_consentimiento_fecha`, `cli_consentimiento_ip`) VALUES
(1, 1, 'PAS', 'ENC::Qo0Dd1Lj+4SN464X3IGcqBn2jMJ8ttNnT9HrBgVnwck=', 'ff072a883770e15764c0f56479a16a78', 'Freddy', 'Bolivar Pinzon Olmedo', 'ENC::MFlXozVYpNPtRpW9vIygO8bfSLp70oh6DOeFOzqbPspTYsMMrS7cAif3H6LLOLyR', 'fa2536059c2cfc78fe680f0629a1859d', 'ENC::oUiyk7GlCyDtL6DvBvkKOY2s+tGROo505vHHGzZdP4k=', NULL, NULL, NULL, 'PUBLICO', 37.75, 'A', '2026-01-26 00:36:19', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_electronicas`
--

DROP TABLE IF EXISTS `facturas_electronicas`;
CREATE TABLE IF NOT EXISTS `facturas_electronicas` (
  `fac_id` int NOT NULL AUTO_INCREMENT,
  `fac_tenant_id` int NOT NULL,
  `fac_factura_id` int DEFAULT NULL,
  `fac_clave_acceso` varchar(49) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fac_tipo_comprobante` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '01' COMMENT '01=Factura, 04=Nota Crédito, 05=Nota Débito, 06=Guía Remisión, 07=Retención',
  `fac_establecimiento` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fac_punto_emision` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fac_secuencial` char(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fac_fecha_emision` date NOT NULL,
  `fac_cliente_id` int DEFAULT NULL,
  `fac_cliente_tipo_identificacion` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '04=RUC, 05=Cédula, 06=Pasaporte, 07=Cons.Final',
  `fac_cliente_identificacion` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fac_cliente_razon_social` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fac_cliente_direccion` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fac_cliente_email` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fac_cliente_telefono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `fac_estado_sri` enum('PENDIENTE','GENERADA','FIRMADA','ENVIADA','RECIBIDA','DEVUELTA','AUTORIZADO','NO_AUTORIZADO','ERROR','ANULADA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `fac_ambiente` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1=Pruebas, 2=Producción',
  `fac_tipo_emision` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1=Normal, 2=Contingencia',
  `fac_xml_generado` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Ruta al archivo XML generado',
  `fac_xml_firmado` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Ruta al archivo XML firmado',
  `fac_xml_autorizado` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Ruta al archivo XML autorizado',
  `fac_numero_autorizacion` varchar(49) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fac_fecha_autorizacion` datetime DEFAULT NULL,
  `fac_mensaje_error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fac_intentos_envio` int NOT NULL DEFAULT '0',
  `fac_ultimo_intento` datetime DEFAULT NULL,
  `fac_observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  `det_codigo_principal` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código interno',
  `det_codigo_auxiliar` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código barras, etc.',
  `det_descripcion` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `imp_codigo` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '2=IVA, 3=ICE, 5=IRBPNR',
  `imp_codigo_porcentaje` char(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código tarifa: 0, 2, 3, 4, 6, 7, 8',
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
  `adi_nombre` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `adi_valor` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `log_clave_acceso` varchar(49) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_accion` enum('GENERAR','FIRMAR','ENVIAR','CONSULTAR','REENVIAR','ANULAR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_endpoint` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_request_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `log_response_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `log_estado_respuesta` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_codigo_error` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_mensaje_error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `log_duracion_ms` int DEFAULT NULL COMMENT 'Tiempo de respuesta en milisegundos',
  `log_ip_origen` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `pag_forma_pago` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '01=Efectivo, 16=Tarjeta Débito, etc.',
  `pag_total` decimal(14,2) NOT NULL,
  `pag_plazo` int DEFAULT NULL COMMENT 'Plazo en días/meses',
  `pag_unidad_tiempo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'dias',
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
  `sec_tipo_comprobante` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '01',
  `sec_establecimiento` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sec_punto_emision` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `sus_periodo` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sus_tipo_factura` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'MENSUAL',
  `sus_subtotal` decimal(10,2) NOT NULL,
  `sus_descuento` decimal(10,2) DEFAULT '0.00',
  `sus_iva` decimal(10,2) NOT NULL,
  `sus_total` decimal(10,2) NOT NULL,
  `sus_plan_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sus_usuarios_cobrados` int DEFAULT NULL,
  `sus_sedes_cobradas` int DEFAULT NULL,
  `sus_modulos_adicionales` json DEFAULT NULL,
  `sus_fecha_emision` date NOT NULL,
  `sus_fecha_vencimiento` date NOT NULL,
  `sus_fecha_pago` date DEFAULT NULL,
  `sus_metodo_pago` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sus_referencia_pago` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sus_comprobante_pago` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sus_numero_autorizacion` varchar(49) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sus_clave_acceso` varchar(49) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sus_xml_firmado` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sus_estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `sus_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sus_factura_id`),
  KEY `idx_tenant_periodo` (`sus_tenant_id`,`sus_periodo`),
  KEY `idx_estado` (`sus_estado`),
  KEY `idx_vencimiento` (`sus_fecha_vencimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_asistencia`
--

DROP TABLE IF EXISTS `futbol_asistencia`;
CREATE TABLE IF NOT EXISTS `futbol_asistencia` (
  `fas_asistencia_id` int NOT NULL AUTO_INCREMENT,
  `fas_tenant_id` int NOT NULL,
  `fas_inscripcion_id` int NOT NULL,
  `fas_grupo_id` int NOT NULL,
  `fas_alumno_id` int NOT NULL,
  `fas_fecha` date NOT NULL,
  `fas_estado` enum('PRESENTE','AUSENTE','TARDANZA','JUSTIFICADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PRESENTE',
  `fas_observacion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fas_registrado_por` int DEFAULT NULL,
  `fas_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fas_asistencia_id`),
  UNIQUE KEY `uk_fas_inscripcion_fecha` (`fas_inscripcion_id`,`fas_fecha`),
  KEY `idx_fas_grupo_fecha` (`fas_grupo_id`,`fas_fecha`),
  KEY `idx_fas_alumno` (`fas_alumno_id`,`fas_fecha`),
  KEY `fk_fas_tenant` (`fas_tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_becas`
--

DROP TABLE IF EXISTS `futbol_becas`;
CREATE TABLE IF NOT EXISTS `futbol_becas` (
  `fbe_beca_id` int NOT NULL AUTO_INCREMENT,
  `fbe_tenant_id` int NOT NULL,
  `fbe_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Beca Deportiva, Hermanos, Referido, etc.',
  `fbe_tipo` enum('PORCENTAJE','MONTO_FIJO','EXONERACION') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PORCENTAJE',
  `fbe_valor` decimal(10,2) NOT NULL COMMENT '% o monto fijo',
  `fbe_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fbe_requisitos` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fbe_cupo_maximo` int DEFAULT NULL COMMENT 'Null = sin l??mite',
  `fbe_cupo_usado` int DEFAULT '0',
  `fbe_vigencia_inicio` date DEFAULT NULL,
  `fbe_vigencia_fin` date DEFAULT NULL,
  `fbe_aplica_matricula` tinyint(1) DEFAULT '0',
  `fbe_aplica_mensualidad` tinyint(1) DEFAULT '1',
  `fbe_activo` tinyint(1) DEFAULT '1',
  `fbe_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fbe_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fbe_beca_id`),
  KEY `idx_fbe_tenant` (`fbe_tenant_id`,`fbe_activo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_becas`
--

INSERT INTO `futbol_becas` (`fbe_beca_id`, `fbe_tenant_id`, `fbe_nombre`, `fbe_tipo`, `fbe_valor`, `fbe_descripcion`, `fbe_requisitos`, `fbe_cupo_maximo`, `fbe_cupo_usado`, `fbe_vigencia_inicio`, `fbe_vigencia_fin`, `fbe_aplica_matricula`, `fbe_aplica_mensualidad`, `fbe_activo`, `fbe_created_at`, `fbe_updated_at`) VALUES
(1, 1, 'Beca Deportiva 100%', 'EXONERACION', 100.00, 'Exoneraci??n total por talento deportivo destacado', NULL, NULL, 0, NULL, NULL, 0, 1, 1, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(2, 1, 'Beca Deportiva 50%', 'PORCENTAJE', 50.00, 'Media beca por rendimiento deportivo', NULL, NULL, 0, NULL, NULL, 0, 1, 1, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(3, 1, 'Descuento Hermanos', 'PORCENTAJE', 15.00, '15% de descuento por hermano inscrito', NULL, NULL, 0, NULL, NULL, 0, 1, 1, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(4, 1, 'Beca Socioecon??mica', 'PORCENTAJE', 30.00, '30% de descuento por situaci??n socioecon??mica comprobada', NULL, NULL, 0, NULL, NULL, 0, 1, 1, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(5, 1, 'Descuento Referido', 'MONTO_FIJO', 10.00, '$10 de descuento mensual por referir nuevo alumno', NULL, NULL, 0, NULL, NULL, 0, 1, 1, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(6, 1, 'Descuento Pronto Pago', 'PORCENTAJE', 5.00, '5% por pago anticipado antes del d??a 5', NULL, NULL, 0, NULL, NULL, 0, 1, 1, '2026-02-09 20:03:14', '2026-02-09 20:03:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_beca_asignaciones`
--

DROP TABLE IF EXISTS `futbol_beca_asignaciones`;
CREATE TABLE IF NOT EXISTS `futbol_beca_asignaciones` (
  `fba_asignacion_id` int NOT NULL AUTO_INCREMENT,
  `fba_tenant_id` int NOT NULL,
  `fba_beca_id` int NOT NULL,
  `fba_alumno_id` int NOT NULL,
  `fba_inscripcion_id` int DEFAULT NULL,
  `fba_fecha_asignacion` date NOT NULL,
  `fba_fecha_vencimiento` date DEFAULT NULL,
  `fba_motivo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fba_aprobado_por` int DEFAULT NULL COMMENT 'ID usuario que aprob??',
  `fba_estado` enum('ACTIVA','SUSPENDIDA','VENCIDA','REVOCADA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVA',
  `fba_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fba_asignacion_id`),
  KEY `idx_fba_alumno` (`fba_alumno_id`,`fba_estado`),
  KEY `idx_fba_beca` (`fba_beca_id`),
  KEY `fk_fba_tenant` (`fba_tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_campos_ficha`
--

DROP TABLE IF EXISTS `futbol_campos_ficha`;
CREATE TABLE IF NOT EXISTS `futbol_campos_ficha` (
  `fcf_campo_id` int NOT NULL AUTO_INCREMENT,
  `fcf_tenant_id` int NOT NULL,
  `fcf_clave` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fcf_etiqueta` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fcf_tipo` enum('TEXT','TEXTAREA','SELECT','NUMBER','DATE','CHECKBOX','RADIO','EMAIL','TEL') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'TEXT',
  `fcf_opciones` json DEFAULT NULL,
  `fcf_placeholder` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcf_requerido` tinyint(1) DEFAULT '0',
  `fcf_grupo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `fcf_orden` int DEFAULT '0',
  `fcf_activo` tinyint(1) DEFAULT '1',
  `fcf_validacion` json DEFAULT NULL,
  `fcf_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fcf_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fcf_campo_id`),
  UNIQUE KEY `uk_fcf_tenant_clave` (`fcf_tenant_id`,`fcf_clave`),
  KEY `idx_fcf_orden` (`fcf_tenant_id`,`fcf_grupo`,`fcf_orden`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_campos_ficha`
--

INSERT INTO `futbol_campos_ficha` (`fcf_campo_id`, `fcf_tenant_id`, `fcf_clave`, `fcf_etiqueta`, `fcf_tipo`, `fcf_opciones`, `fcf_placeholder`, `fcf_requerido`, `fcf_grupo`, `fcf_orden`, `fcf_activo`, `fcf_validacion`, `fcf_created_at`, `fcf_updated_at`) VALUES
(1, 1, 'posicion_secundaria', 'Posici??n Secundaria', 'SELECT', '[\"Portero\", \"Defensa Central\", \"Lateral\", \"Mediocampista\", \"Extremo\", \"Delantero\"]', NULL, 0, 'deportivo', 1, 1, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(2, 1, 'pie_habil', 'Pie m??s H??bil', 'SELECT', '[\"Derecho\", \"Izquierdo\", \"Ambidiestro\"]', NULL, 1, 'deportivo', 2, 1, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(3, 1, 'club_favorito', 'Club Favorito', 'TEXT', NULL, 'Ej: Barcelona SC, Liga de Quito...', 0, 'personal', 1, 1, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(4, 1, 'jugador_favorito', 'Jugador Favorito', 'TEXT', NULL, 'Ej: Messi, Cristiano...', 0, 'personal', 2, 1, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(5, 1, 'como_nos_conocio', '??C??mo nos conoci???', 'SELECT', '[\"Redes sociales\", \"Recomendaci??n\", \"Publicidad\", \"Escuela/Colegio\", \"Otro\"]', NULL, 0, 'general', 1, 1, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(6, 1, 'autoriza_fotos', 'Autoriza publicaci??n de fotos/videos', 'CHECKBOX', NULL, NULL, 1, 'legal', 1, 1, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(7, 1, 'acepta_reglamento', 'Acepta reglamento interno', 'CHECKBOX', NULL, NULL, 1, 'legal', 2, 1, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(8, 1, 'obs_medicas_extra', 'Observaciones M??dicas Adicionales', 'TEXTAREA', NULL, 'Lesiones previas, limitaciones f??sicas...', 0, 'medico', 1, 1, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_categorias`
--

DROP TABLE IF EXISTS `futbol_categorias`;
CREATE TABLE IF NOT EXISTS `futbol_categorias` (
  `fct_categoria_id` int NOT NULL AUTO_INCREMENT,
  `fct_tenant_id` int NOT NULL,
  `fct_nombre` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Sub-6, Sub-8, Sub-10, etc.',
  `fct_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'U6, U8, U10, etc.',
  `fct_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fct_color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#22C55E',
  `fct_orden` int DEFAULT '0',
  `fct_edad_min` int DEFAULT NULL,
  `fct_edad_max` int DEFAULT NULL,
  `fct_activo` tinyint(1) DEFAULT '1',
  `fct_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fct_categoria_id`),
  UNIQUE KEY `uk_fct_tenant_codigo` (`fct_tenant_id`,`fct_codigo`),
  KEY `idx_fct_orden` (`fct_tenant_id`,`fct_orden`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_categorias`
--

INSERT INTO `futbol_categorias` (`fct_categoria_id`, `fct_tenant_id`, `fct_nombre`, `fct_codigo`, `fct_descripcion`, `fct_color`, `fct_orden`, `fct_edad_min`, `fct_edad_max`, `fct_activo`, `fct_created_at`) VALUES
(1, 1, 'Sub-6 (Baby Fútbol)', 'U6', 'Iniciación al fútbol. Juegos lúdicos y motricidad básica.', '#94A3B8', 1, 4, 6, 1, '2026-02-09 20:03:14'),
(2, 1, 'Sub-8', 'U8', 'Fundamentos básicos: conducción, pase y tiro. Juegos reducidos.', '#22C55E', 2, 7, 8, 1, '2026-02-09 20:03:14'),
(3, 1, 'Sub-10', 'U10', 'T??cnica individual, conceptos técticos básicos y juego 7v7.', '#3B82F6', 3, 9, 10, 1, '2026-02-09 20:03:14'),
(4, 1, 'Sub-12', 'U12', 'Desarrollo téctico, transiciones y juego 9v9.', '#8B5CF6', 4, 11, 12, 1, '2026-02-09 20:03:14'),
(5, 1, 'Sub-14', 'U14', 'F??tbol 11, sistemas de juego y preparación competitiva.', '#F59E0B', 5, 13, 14, 1, '2026-02-09 20:03:14'),
(6, 1, 'Sub-16', 'U16', 'Alto rendimiento juvenil. Especialización por posición.', '#EF4444', 6, 15, 16, 1, '2026-02-09 20:03:14'),
(7, 1, 'Sub-18', 'U18', 'Competitivo senior juvenil. Preparación para fútbol amateur.', '#EC4899', 7, 17, 18, 1, '2026-02-09 20:03:14'),
(8, 1, 'Adultos', 'ADU', 'Categoría libre para mayores de 18.', '#06B6D4', 8, 18, 99, 1, '2026-02-09 20:03:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_categoria_habilidades`
--

DROP TABLE IF EXISTS `futbol_categoria_habilidades`;
CREATE TABLE IF NOT EXISTS `futbol_categoria_habilidades` (
  `fch_habilidad_id` int NOT NULL AUTO_INCREMENT,
  `fch_tenant_id` int NOT NULL,
  `fch_categoria_id` int NOT NULL,
  `fch_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Conducci??n, Pase corto, Tiro a puerta, etc.',
  `fch_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fch_orden` int DEFAULT '0',
  `fch_activo` tinyint(1) DEFAULT '1',
  `fch_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fch_habilidad_id`),
  KEY `idx_fch_categoria` (`fch_categoria_id`,`fch_orden`),
  KEY `fk_fch_tenant` (`fch_tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_categoria_habilidades`
--

INSERT INTO `futbol_categoria_habilidades` (`fch_habilidad_id`, `fch_tenant_id`, `fch_categoria_id`, `fch_nombre`, `fch_descripcion`, `fch_orden`, `fch_activo`, `fch_created_at`) VALUES
(1, 1, 2, 'Conducci??n con ambos pies', 'Conducir el bal??n 15m alternando pie derecho e izquierdo', 1, 1, '2026-02-09 20:03:14'),
(2, 1, 2, 'Pase corto', 'Pase con interior del pie a compa??ero a 5-8 metros', 2, 1, '2026-02-09 20:03:14'),
(3, 1, 2, 'Control orientado', 'Recepci??n y control del bal??n con cambio de direcci??n', 3, 1, '2026-02-09 20:03:14'),
(4, 1, 2, 'Tiro a puerta', 'Tiro con empeine desde 10 metros al arco', 4, 1, '2026-02-09 20:03:14'),
(5, 1, 2, 'Regate simple', 'Superar rival con amague y cambio de direcci??n', 5, 1, '2026-02-09 20:03:14'),
(6, 1, 3, 'Pase largo', 'Pase con empeine a 15-20 metros de distancia', 1, 1, '2026-02-09 20:03:14'),
(7, 1, 3, 'Cabeceo', 'Cabeceo ofensivo y defensivo con t??cnica correcta', 2, 1, '2026-02-09 20:03:14'),
(8, 1, 3, 'Regate compuesto', 'Superar rival con secuencia de 2+ amagues', 3, 1, '2026-02-09 20:03:14'),
(9, 1, 3, 'Tiro de media distancia', 'Tiro con potencia desde fuera del ??rea', 4, 1, '2026-02-09 20:03:14'),
(10, 1, 3, 'Posicionamiento t??ctico', 'Ubicaci??n correcta en sistema 3-3-1 (7v7)', 5, 1, '2026-02-09 20:03:14'),
(11, 1, 3, 'Marca individual', 'Seguimiento al rival directo y recuperaci??n', 6, 1, '2026-02-09 20:03:14'),
(12, 1, 4, 'Pase al espacio', 'Anticipar el movimiento del compa??ero y pasar al espacio', 1, 1, '2026-02-09 20:03:14'),
(13, 1, 4, 'Centro al ??rea', 'Centro preciso desde banda al ??rea de penal', 2, 1, '2026-02-09 20:03:14'),
(14, 1, 4, 'Tiro libre', 'Ejecuci??n de tiro libre con efecto', 3, 1, '2026-02-09 20:03:14'),
(15, 1, 4, 'Juego de transici??n', 'Cambiar de defensa a ataque y viceversa con velocidad', 4, 1, '2026-02-09 20:03:14'),
(16, 1, 4, 'Juego a??reo', 'Disputa de balones a??reos en ataque y defensa', 5, 1, '2026-02-09 20:03:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_comprobantes`
--

DROP TABLE IF EXISTS `futbol_comprobantes`;
CREATE TABLE IF NOT EXISTS `futbol_comprobantes` (
  `fcm_comprobante_id` int NOT NULL AUTO_INCREMENT,
  `fcm_tenant_id` int NOT NULL,
  `fcm_pago_id` int NOT NULL COMMENT 'FK ??? futbol_pagos',
  `fcm_numero` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nro. secuencial: ESC-0001',
  `fcm_tipo` enum('RECIBO','FACTURA','NOTA_VENTA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'RECIBO',
  `fcm_cliente_id` int DEFAULT NULL,
  `fcm_alumno_id` int NOT NULL,
  `fcm_concepto` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fcm_subtotal` decimal(10,2) NOT NULL,
  `fcm_descuento` decimal(10,2) DEFAULT '0.00',
  `fcm_iva` decimal(10,2) DEFAULT '0.00',
  `fcm_total` decimal(10,2) NOT NULL,
  `fcm_metodo_pago` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_fecha_emision` date NOT NULL,
  `fcm_estado` enum('EMITIDO','ANULADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'EMITIDO',
  `fcm_pdf_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_enviado_email` tinyint(1) DEFAULT '0',
  `fcm_enviado_whatsapp` tinyint(1) DEFAULT '0',
  `fcm_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fcm_datos_json` json DEFAULT NULL,
  `fcm_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fcm_comprobante_id`),
  UNIQUE KEY `uk_fcm_tenant_numero` (`fcm_tenant_id`,`fcm_numero`),
  KEY `idx_fcm_pago` (`fcm_pago_id`),
  KEY `fk_fcm_cliente` (`fcm_cliente_id`),
  KEY `fk_fcm_alumno` (`fcm_alumno_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_configuracion`
--

DROP TABLE IF EXISTS `futbol_configuracion`;
CREATE TABLE IF NOT EXISTS `futbol_configuracion` (
  `fcg_config_id` int NOT NULL AUTO_INCREMENT,
  `fcg_tenant_id` int NOT NULL,
  `fcg_clave` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fcg_valor` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fcg_tipo` enum('TEXT','NUMBER','BOOLEAN','JSON','SELECT') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'TEXT',
  `fcg_descripcion` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcg_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fcg_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fcg_config_id`),
  UNIQUE KEY `uk_fcg_tenant_clave` (`fcg_tenant_id`,`fcg_clave`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_configuracion`
--

INSERT INTO `futbol_configuracion` (`fcg_config_id`, `fcg_tenant_id`, `fcg_clave`, `fcg_valor`, `fcg_tipo`, `fcg_descripcion`, `fcg_created_at`, `fcg_updated_at`) VALUES
(1, 1, 'nombre_modulo', 'Escuela de F??tbol', 'TEXT', 'Nombre personalizado del m??dulo', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(2, 1, 'moneda', 'USD', 'TEXT', 'Moneda para precios', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(3, 1, 'max_alumnos_grupo', '25', 'NUMBER', 'M??ximo de alumnos por grupo', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(4, 1, 'requiere_certificado_medico', 'true', 'BOOLEAN', 'Exigir certificado m??dico', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(5, 1, 'edad_minima_inscripcion', '4', 'NUMBER', 'Edad m??nima para inscribir', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(6, 1, 'permite_lista_espera', 'true', 'BOOLEAN', 'Activar lista de espera', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(7, 1, 'dias_prueba_gratis', '3', 'NUMBER', 'D??as de clase de prueba gratis', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(8, 1, 'porcentaje_asistencia_min', '70', 'NUMBER', 'Porcentaje m??nimo de asistencia', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(9, 1, 'escala_evaluacion', '5', 'NUMBER', 'Escala de evaluaci??n (1-5 estrellas)', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(10, 1, 'dia_pago_limite', '10', 'NUMBER', 'D??a del mes l??mite para pago sin mora', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(11, 1, 'porcentaje_mora', '5', 'NUMBER', 'Porcentaje de recargo por mora mensual', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(12, 1, 'dias_gracia_mora', '5', 'NUMBER', 'D??as de gracia antes de aplicar mora', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(13, 1, 'comprobante_prefijo', 'ESC', 'TEXT', 'Prefijo para n??meros de comprobante', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(14, 1, 'whatsapp_activo', 'false', 'BOOLEAN', 'Activar notificaciones WhatsApp', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(15, 1, 'email_activo', 'true', 'BOOLEAN', 'Activar notificaciones por email', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(16, 1, 'sms_activo', 'false', 'BOOLEAN', 'Activar notificaciones SMS', '2026-02-09 20:03:14', '2026-02-09 20:03:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_egresos`
--

DROP TABLE IF EXISTS `futbol_egresos`;
CREATE TABLE IF NOT EXISTS `futbol_egresos` (
  `feg_egreso_id` int NOT NULL AUTO_INCREMENT,
  `feg_tenant_id` int NOT NULL,
  `feg_sede_id` int DEFAULT NULL,
  `feg_categoria` enum('UNIFORMES','BALONES','CONOS_MATERIAL','ARBITRAJE','TRANSPORTE','CANCHAS','PERSONAL','TORNEOS','SEGUROS','MARKETING','SERVICIOS','OTROS') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'OTROS',
  `feg_concepto` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `feg_monto` decimal(10,2) NOT NULL,
  `feg_fecha` date NOT NULL,
  `feg_proveedor` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feg_factura_ref` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feg_metodo_pago` enum('EFECTIVO','TRANSFERENCIA','TARJETA','CHEQUE') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'EFECTIVO',
  `feg_referencia_pago` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feg_estado` enum('REGISTRADO','PAGADO','ANULADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'REGISTRADO',
  `feg_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `feg_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `feg_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`feg_egreso_id`),
  KEY `idx_feg_tenant_fecha` (`feg_tenant_id`,`feg_fecha`),
  KEY `idx_feg_sede` (`feg_sede_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_entrenadores`
--

DROP TABLE IF EXISTS `futbol_entrenadores`;
CREATE TABLE IF NOT EXISTS `futbol_entrenadores` (
  `fen_entrenador_id` int NOT NULL AUTO_INCREMENT,
  `fen_tenant_id` int NOT NULL,
  `fen_sede_id` int DEFAULT NULL COMMENT 'FK ??? instalaciones_sedes',
  `fen_usuario_id` int DEFAULT NULL COMMENT 'FK ??? seguridad_usuarios (si tiene login)',
  `fen_nombres` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fen_apellidos` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fen_identificacion` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fen_email` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fen_telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fen_rol` enum('DIRECTOR_TECNICO','ENTRENADOR','ASISTENTE','PREPARADOR_FISICO','PORTEROS') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ENTRENADOR',
  `fen_especialidad` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Formativas, Alto rendimiento, etc.',
  `fen_certificaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fen_foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fen_color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#22C55E',
  `fen_activo` tinyint(1) DEFAULT '1',
  `fen_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fen_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fen_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fen_entrenador_id`),
  KEY `idx_fen_tenant` (`fen_tenant_id`,`fen_activo`),
  KEY `idx_fen_sede` (`fen_sede_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_evaluaciones`
--

DROP TABLE IF EXISTS `futbol_evaluaciones`;
CREATE TABLE IF NOT EXISTS `futbol_evaluaciones` (
  `fev_evaluacion_id` int NOT NULL AUTO_INCREMENT,
  `fev_tenant_id` int NOT NULL,
  `fev_alumno_id` int NOT NULL,
  `fev_grupo_id` int DEFAULT NULL,
  `fev_periodo_id` int DEFAULT NULL,
  `fev_habilidad_id` int NOT NULL COMMENT 'FK ??? futbol_categoria_habilidades',
  `fev_categoria_id` int NOT NULL COMMENT 'FK ??? futbol_categorias',
  `fev_calificacion` tinyint UNSIGNED DEFAULT '0' COMMENT '0-5 estrellas',
  `fev_aprobado` tinyint(1) DEFAULT '0',
  `fev_fecha` date NOT NULL,
  `fev_evaluador_id` int DEFAULT NULL COMMENT 'FK ??? futbol_entrenadores',
  `fev_observacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fev_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fev_updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fev_evaluacion_id`),
  UNIQUE KEY `uk_fev_alumno_habilidad_fecha` (`fev_alumno_id`,`fev_habilidad_id`,`fev_fecha`),
  KEY `idx_fev_alumno` (`fev_alumno_id`,`fev_categoria_id`),
  KEY `fk_fev_tenant` (`fev_tenant_id`),
  KEY `fk_fev_habilidad` (`fev_habilidad_id`),
  KEY `fk_fev_categoria` (`fev_categoria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_ficha_alumno`
--

DROP TABLE IF EXISTS `futbol_ficha_alumno`;
CREATE TABLE IF NOT EXISTS `futbol_ficha_alumno` (
  `ffa_ficha_id` int NOT NULL AUTO_INCREMENT,
  `ffa_tenant_id` int NOT NULL,
  `ffa_alumno_id` int NOT NULL COMMENT 'FK ??? alumnos',
  `ffa_categoria_id` int DEFAULT NULL COMMENT 'FK ??? futbol_categorias',
  `ffa_posicion_preferida` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Portero, Defensa, Mediocampista, Delantero',
  `ffa_pie_dominante` enum('DERECHO','IZQUIERDO','AMBIDIESTRO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'DERECHO',
  `ffa_experiencia_previa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ffa_club_anterior` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ffa_objetivo` enum('RECREATIVO','FORMATIVO','COMPETITIVO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'FORMATIVO',
  `ffa_talla_camiseta` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'XS, S, M, L, XL',
  `ffa_talla_short` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ffa_talla_zapato` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ffa_numero_camiseta` int DEFAULT NULL,
  `ffa_autorizacion_medica` tinyint(1) DEFAULT '0',
  `ffa_seguro_medico` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ffa_fecha_ingreso` date DEFAULT NULL,
  `ffa_fecha_ultimo_avance` date DEFAULT NULL,
  `ffa_datos_custom` json DEFAULT NULL,
  `ffa_documentos` json DEFAULT NULL,
  `ffa_activo` tinyint(1) DEFAULT '1',
  `ffa_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ffa_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ffa_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ffa_ficha_id`),
  UNIQUE KEY `uk_ffa_tenant_alumno` (`ffa_tenant_id`,`ffa_alumno_id`),
  KEY `idx_ffa_categoria` (`ffa_categoria_id`),
  KEY `fk_ffa_alumno` (`ffa_alumno_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_grupos`
--

DROP TABLE IF EXISTS `futbol_grupos`;
CREATE TABLE IF NOT EXISTS `futbol_grupos` (
  `fgr_grupo_id` int NOT NULL AUTO_INCREMENT,
  `fgr_tenant_id` int NOT NULL,
  `fgr_sede_id` int DEFAULT NULL COMMENT 'FK ??? instalaciones_sedes',
  `fgr_periodo_id` int DEFAULT NULL COMMENT 'FK ??? futbol_periodos',
  `fgr_categoria_id` int DEFAULT NULL COMMENT 'FK ??? futbol_categorias',
  `fgr_cancha_id` int DEFAULT NULL COMMENT 'FK ??? instalaciones_canchas',
  `fgr_entrenador_id` int DEFAULT NULL COMMENT 'FK ??? futbol_entrenadores',
  `fgr_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: Sub-12 A ??? Competitivo',
  `fgr_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fgr_cupo_maximo` int DEFAULT '25',
  `fgr_cupo_actual` int DEFAULT '0',
  `fgr_edad_min` int DEFAULT NULL,
  `fgr_edad_max` int DEFAULT NULL,
  `fgr_precio` decimal(10,2) DEFAULT '0.00' COMMENT 'Precio mensualidad',
  `fgr_estado` enum('ABIERTO','CERRADO','EN_CURSO','FINALIZADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ABIERTO',
  `fgr_color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#22C55E',
  `fgr_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fgr_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fgr_grupo_id`),
  KEY `idx_fgr_tenant_estado` (`fgr_tenant_id`,`fgr_estado`),
  KEY `idx_fgr_periodo` (`fgr_periodo_id`),
  KEY `idx_fgr_categoria` (`fgr_categoria_id`),
  KEY `idx_fgr_sede` (`fgr_sede_id`),
  KEY `fk_fgr_cancha` (`fgr_cancha_id`),
  KEY `fk_fgr_entrenador` (`fgr_entrenador_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_grupo_horarios`
--

DROP TABLE IF EXISTS `futbol_grupo_horarios`;
CREATE TABLE IF NOT EXISTS `futbol_grupo_horarios` (
  `fgh_horario_id` int NOT NULL AUTO_INCREMENT,
  `fgh_tenant_id` int NOT NULL,
  `fgh_grupo_id` int NOT NULL,
  `fgh_dia_semana` enum('LUN','MAR','MIE','JUE','VIE','SAB','DOM') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fgh_hora_inicio` time NOT NULL,
  `fgh_hora_fin` time NOT NULL,
  `fgh_cancha_id` int DEFAULT NULL COMMENT 'FK ??? instalaciones_canchas (opcional override)',
  `fgh_activo` tinyint(1) DEFAULT '1',
  `fgh_notas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`fgh_horario_id`),
  KEY `idx_fgh_grupo` (`fgh_grupo_id`),
  KEY `idx_fgh_dia` (`fgh_tenant_id`,`fgh_dia_semana`,`fgh_hora_inicio`),
  KEY `fk_fgh_cancha` (`fgh_cancha_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_inscripciones`
--

DROP TABLE IF EXISTS `futbol_inscripciones`;
CREATE TABLE IF NOT EXISTS `futbol_inscripciones` (
  `fin_inscripcion_id` int NOT NULL AUTO_INCREMENT,
  `fin_tenant_id` int NOT NULL,
  `fin_alumno_id` int NOT NULL COMMENT 'FK ??? alumnos',
  `fin_grupo_id` int NOT NULL COMMENT 'FK ??? futbol_grupos',
  `fin_periodo_id` int DEFAULT NULL,
  `fin_fecha_inscripcion` date NOT NULL,
  `fin_fecha_baja` date DEFAULT NULL,
  `fin_monto` decimal(10,2) DEFAULT '0.00',
  `fin_descuento` decimal(10,2) DEFAULT '0.00',
  `fin_monto_final` decimal(10,2) DEFAULT '0.00',
  `fin_beca_id` int DEFAULT NULL COMMENT 'FK ??? futbol_becas si aplica',
  `fin_estado` enum('ACTIVA','SUSPENDIDA','FINALIZADA','CANCELADA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVA',
  `fin_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fin_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fin_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fin_inscripcion_id`),
  UNIQUE KEY `uk_fin_alumno_grupo` (`fin_alumno_id`,`fin_grupo_id`),
  KEY `idx_fin_tenant_estado` (`fin_tenant_id`,`fin_estado`),
  KEY `idx_fin_grupo` (`fin_grupo_id`),
  KEY `fk_fin_beca` (`fin_beca_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_lista_espera`
--

DROP TABLE IF EXISTS `futbol_lista_espera`;
CREATE TABLE IF NOT EXISTS `futbol_lista_espera` (
  `fle_espera_id` int NOT NULL AUTO_INCREMENT,
  `fle_tenant_id` int NOT NULL,
  `fle_alumno_id` int NOT NULL,
  `fle_grupo_id` int NOT NULL,
  `fle_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fle_posicion` int DEFAULT '0',
  `fle_estado` enum('ESPERANDO','NOTIFICADO','INSCRITO','CANCELADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ESPERANDO',
  `fle_notas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`fle_espera_id`),
  UNIQUE KEY `uk_fle_alumno_grupo` (`fle_alumno_id`,`fle_grupo_id`),
  KEY `idx_fle_grupo_estado` (`fle_grupo_id`,`fle_estado`,`fle_posicion`),
  KEY `fk_fle_tenant` (`fle_tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_notificaciones`
--

DROP TABLE IF EXISTS `futbol_notificaciones`;
CREATE TABLE IF NOT EXISTS `futbol_notificaciones` (
  `fno_notificacion_id` int NOT NULL AUTO_INCREMENT,
  `fno_tenant_id` int NOT NULL,
  `fno_alumno_id` int DEFAULT NULL,
  `fno_cliente_id` int DEFAULT NULL COMMENT 'FK ??? clientes (representante)',
  `fno_tipo` enum('PAGO_PENDIENTE','MORA','BIENVENIDA','RECORDATORIO','TORNEO','ASISTENCIA','GENERAL') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fno_canal` enum('EMAIL','WHATSAPP','SMS','PUSH','SISTEMA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fno_asunto` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fno_mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fno_destinatario` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email, tel??fono, etc.',
  `fno_estado` enum('PENDIENTE','ENVIADO','FALLIDO','LEIDO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `fno_intentos` int DEFAULT '0',
  `fno_error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fno_referencia_id` int DEFAULT NULL COMMENT 'ID del pago, inscripci??n, etc.',
  `fno_referencia_tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'pago, inscripcion, torneo, etc.',
  `fno_fecha_programada` datetime DEFAULT NULL,
  `fno_fecha_envio` datetime DEFAULT NULL,
  `fno_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fno_notificacion_id`),
  KEY `idx_fno_tenant_tipo` (`fno_tenant_id`,`fno_tipo`),
  KEY `idx_fno_estado` (`fno_estado`,`fno_fecha_programada`),
  KEY `idx_fno_alumno` (`fno_alumno_id`),
  KEY `fk_fno_cliente` (`fno_cliente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_pagos`
--

DROP TABLE IF EXISTS `futbol_pagos`;
CREATE TABLE IF NOT EXISTS `futbol_pagos` (
  `fpg_pago_id` int NOT NULL AUTO_INCREMENT,
  `fpg_tenant_id` int NOT NULL,
  `fpg_sede_id` int DEFAULT NULL,
  `fpg_inscripcion_id` int DEFAULT NULL,
  `fpg_alumno_id` int NOT NULL,
  `fpg_grupo_id` int DEFAULT NULL,
  `fpg_cliente_id` int DEFAULT NULL COMMENT 'FK ??? clientes (quien paga)',
  `fpg_concepto` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mensualidad Febrero, Matr??cula, Uniforme, etc.',
  `fpg_tipo` enum('MATRICULA','MENSUALIDAD','UNIFORME','TORNEO','OTRO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'MENSUALIDAD',
  `fpg_mes_correspondiente` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'YYYY-MM del mes que cubre',
  `fpg_monto` decimal(10,2) NOT NULL,
  `fpg_descuento` decimal(10,2) DEFAULT '0.00',
  `fpg_beca_descuento` decimal(10,2) DEFAULT '0.00' COMMENT 'Descuento aplicado por beca',
  `fpg_total` decimal(10,2) NOT NULL,
  `fpg_metodo_pago` enum('EFECTIVO','TARJETA','TRANSFERENCIA','DEPOSITO','ABONO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'EFECTIVO',
  `fpg_referencia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fpg_fecha` date NOT NULL,
  `fpg_fecha_vencimiento` date DEFAULT NULL COMMENT 'Fecha l??mite de pago',
  `fpg_dias_mora` int DEFAULT '0',
  `fpg_recargo_mora` decimal(10,2) DEFAULT '0.00',
  `fpg_estado` enum('PENDIENTE','PAGADO','VENCIDO','ANULADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `fpg_comprobante_num` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'N??mero de comprobante emitido',
  `fpg_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fpg_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fpg_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fpg_pago_id`),
  KEY `idx_fpg_tenant_estado` (`fpg_tenant_id`,`fpg_estado`),
  KEY `idx_fpg_alumno` (`fpg_alumno_id`),
  KEY `idx_fpg_cliente` (`fpg_cliente_id`),
  KEY `idx_fpg_sede` (`fpg_sede_id`),
  KEY `idx_fpg_vencimiento` (`fpg_tenant_id`,`fpg_fecha_vencimiento`,`fpg_estado`),
  KEY `idx_fpg_mora` (`fpg_tenant_id`,`fpg_dias_mora`),
  KEY `fk_fpg_inscripcion` (`fpg_inscripcion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_periodos`
--

DROP TABLE IF EXISTS `futbol_periodos`;
CREATE TABLE IF NOT EXISTS `futbol_periodos` (
  `fpe_periodo_id` int NOT NULL AUTO_INCREMENT,
  `fpe_tenant_id` int NOT NULL,
  `fpe_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: Temporada 2026-A',
  `fpe_fecha_inicio` date NOT NULL,
  `fpe_fecha_fin` date NOT NULL,
  `fpe_estado` enum('PLANIFICADO','ACTIVO','FINALIZADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PLANIFICADO',
  `fpe_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fpe_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fpe_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fpe_periodo_id`),
  KEY `idx_fpe_tenant_estado` (`fpe_tenant_id`,`fpe_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_torneos`
--

DROP TABLE IF EXISTS `futbol_torneos`;
CREATE TABLE IF NOT EXISTS `futbol_torneos` (
  `fto_torneo_id` int NOT NULL AUTO_INCREMENT,
  `fto_tenant_id` int NOT NULL,
  `fto_sede_id` int DEFAULT NULL,
  `fto_nombre` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fto_organizador` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fto_sede_torneo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ubicaci??n del torneo',
  `fto_fecha_inicio` date NOT NULL,
  `fto_fecha_fin` date DEFAULT NULL,
  `fto_categoria_id` int DEFAULT NULL,
  `fto_tipo` enum('LIGA','COPA','AMISTOSO','INTERESCUELAS','CAMPEONATO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'COPA',
  `fto_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fto_costo_inscripcion` decimal(10,2) DEFAULT '0.00',
  `fto_estado` enum('PROXIMO','EN_CURSO','FINALIZADO','CANCELADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PROXIMO',
  `fto_resultado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Campe??n, Sub-Campe??n, 3er lugar, etc.',
  `fto_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fto_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fto_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fto_torneo_id`),
  KEY `idx_fto_tenant` (`fto_tenant_id`,`fto_estado`),
  KEY `fk_fto_categoria` (`fto_categoria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `futbol_torneo_jugadores`
--

DROP TABLE IF EXISTS `futbol_torneo_jugadores`;
CREATE TABLE IF NOT EXISTS `futbol_torneo_jugadores` (
  `ftj_id` int NOT NULL AUTO_INCREMENT,
  `ftj_tenant_id` int NOT NULL,
  `ftj_torneo_id` int NOT NULL,
  `ftj_alumno_id` int NOT NULL,
  `ftj_posicion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ftj_numero` int DEFAULT NULL,
  `ftj_es_capitan` tinyint(1) DEFAULT '0',
  `ftj_estado` enum('CONVOCADO','CONFIRMADO','BAJA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'CONVOCADO',
  `ftj_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ftj_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ftj_id`),
  UNIQUE KEY `uk_ftj_torneo_alumno` (`ftj_torneo_id`,`ftj_alumno_id`),
  KEY `fk_ftj_tenant` (`ftj_tenant_id`),
  KEY `fk_ftj_alumno` (`ftj_alumno_id`)
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
  `ins_codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ins_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ins_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ins_superficie` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ins_dimensiones` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ins_capacidad_personas` int DEFAULT NULL,
  `ins_tiene_iluminacion` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `ins_tiene_graderias` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `ins_tiene_vestuarios` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `ins_tiene_duchas` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `ins_duracion_minima_minutos` int DEFAULT '60',
  `ins_duracion_maxima_minutos` int DEFAULT '120',
  `ins_tiempo_anticipacion_dias` int DEFAULT '30',
  `ins_permite_reserva_recurrente` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `ins_foto_principal` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ins_galeria_fotos` json DEFAULT NULL,
  `ins_estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `ins_motivo_inactivacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  `abo_forma_pago` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `abo_estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `abo_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`abo_abono_id`),
  KEY `tenant_id` (`abo_tenant_id`),
  KEY `idx_cliente` (`abo_cliente_id`),
  KEY `idx_vencimiento` (`abo_fecha_vencimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `instalaciones_abonos`
--

INSERT INTO `instalaciones_abonos` (`abo_abono_id`, `abo_tenant_id`, `abo_cliente_id`, `abo_monto_total`, `abo_monto_utilizado`, `abo_saldo_disponible`, `abo_fecha_compra`, `abo_fecha_vencimiento`, `abo_forma_pago`, `abo_estado`, `abo_fecha_registro`) VALUES
(1, 1, 1, 42.75, 5.00, 37.75, '2026-02-08', '2027-02-08', 'EFECTIVO', 'ACTIVO', '2026-02-09 02:48:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_abono_movimientos`
--

DROP TABLE IF EXISTS `instalaciones_abono_movimientos`;
CREATE TABLE IF NOT EXISTS `instalaciones_abono_movimientos` (
  `mov_movimiento_id` int NOT NULL AUTO_INCREMENT,
  `mov_tenant_id` int NOT NULL,
  `mov_abono_id` int NOT NULL,
  `mov_cliente_id` int NOT NULL,
  `mov_tipo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'RECARGA, CONSUMO, DEVOLUCION, AJUSTE, VENCIMIENTO',
  `mov_monto` decimal(10,2) NOT NULL,
  `mov_saldo_anterior` decimal(10,2) NOT NULL,
  `mov_saldo_posterior` decimal(10,2) NOT NULL,
  `mov_descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mov_referencia_tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'RESERVA, PAGO, MANUAL',
  `mov_referencia_id` int DEFAULT NULL,
  `mov_forma_pago` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mov_usuario_registro` int DEFAULT NULL,
  `mov_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mov_movimiento_id`),
  KEY `idx_tenant` (`mov_tenant_id`),
  KEY `idx_cliente` (`mov_cliente_id`),
  KEY `idx_abono` (`mov_abono_id`),
  KEY `idx_fecha` (`mov_fecha_registro`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `instalaciones_abono_movimientos`
--

INSERT INTO `instalaciones_abono_movimientos` (`mov_movimiento_id`, `mov_tenant_id`, `mov_abono_id`, `mov_cliente_id`, `mov_tipo`, `mov_monto`, `mov_saldo_anterior`, `mov_saldo_posterior`, `mov_descripcion`, `mov_referencia_tipo`, `mov_referencia_id`, `mov_forma_pago`, `mov_usuario_registro`, `mov_fecha_registro`) VALUES
(1, 1, 1, 1, 'RECARGA', 10.00, 0.00, 10.00, 'Recarga inicial al crear monedero', 'MANUAL', NULL, 'EFECTIVO', 1, '2026-02-09 02:48:32'),
(2, 1, 1, 1, 'CONSUMO', 5.00, 10.00, 5.00, 'Pago reserva #3', 'PAGO_RESERVA', 2, 'MONEDERO', 1, '2026-02-09 03:56:44'),
(3, 1, 1, 1, 'RECARGA', 10.00, 5.00, 15.00, 'Recarga de monedero: Saldos', 'MANUAL', NULL, 'EFECTIVO', 1, '2026-02-09 04:55:20'),
(4, 1, 1, 1, 'RECARGA', 12.75, 15.00, 27.75, 'Recarga de monedero: Saldos', 'MANUAL', NULL, 'EFECTIVO', 1, '2026-02-09 04:55:49'),
(5, 1, 1, 1, 'RECARGA', 10.00, 27.75, 37.75, 'Recarga de monedero: Vuelto', 'MANUAL', NULL, 'TRANSFERENCIA', 1, '2026-02-19 17:28:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_canchas`
--

DROP TABLE IF EXISTS `instalaciones_canchas`;
CREATE TABLE IF NOT EXISTS `instalaciones_canchas` (
  `can_cancha_id` int NOT NULL AUTO_INCREMENT,
  `can_tenant_id` int NOT NULL,
  `can_instalacion_id` int NOT NULL,
  `can_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `can_tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'futbol, tenis, padel, voleibol, basquetbol, piscina, gimnasio, otro',
  `can_superficie` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `can_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `can_capacidad_maxima` int NOT NULL DEFAULT '0',
  `can_ancho` decimal(8,2) DEFAULT NULL COMMENT 'Ancho en metros',
  `can_largo` decimal(8,2) DEFAULT NULL COMMENT 'Largo en metros',
  `can_dimensiones` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `can_iluminacion` tinyint(1) DEFAULT '0',
  `can_techada` tinyint(1) DEFAULT '0',
  `can_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `can_estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO' COMMENT 'ACTIVO, INACTIVO, ELIMINADA',
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

INSERT INTO `instalaciones_canchas` (`can_cancha_id`, `can_tenant_id`, `can_instalacion_id`, `can_nombre`, `can_tipo`, `can_superficie`, `can_descripcion`, `can_capacidad_maxima`, `can_ancho`, `can_largo`, `can_dimensiones`, `can_iluminacion`, `can_techada`, `can_notas`, `can_estado`, `can_fecha_creacion`, `can_fecha_actualizacion`, `can_usuario_creacion`, `can_usuario_actualizacion`) VALUES
(1, 1, 2, 'Cancha Fútbol 1 - Complejo Norte', 'futbol', NULL, 'Cancha de fútbol profesional', 22, 25.00, 50.00, NULL, 0, 0, NULL, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-26 00:26:29', 1, NULL),
(2, 1, 2, 'Cancha Básquet - Complejo Norte', 'BASQUET', NULL, 'Cancha de baloncesto', 10, 25.00, 50.00, NULL, 0, 0, NULL, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1, NULL),
(3, 1, 2, 'Cancha Tenis - Complejo Norte', 'TENIS', NULL, 'Cancha de tenis individual', 4, 25.00, 50.00, NULL, 0, 0, NULL, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1, NULL),
(4, 1, 3, 'Cancha Fútbol 1 - Complejo Sur', 'FUTBOL', NULL, 'Cancha de fútbol profesional', 22, 25.00, 50.00, NULL, 0, 0, NULL, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1, NULL),
(5, 1, 3, 'Cancha Básquet - Complejo Sur', 'basquetbol', NULL, 'Cancha de baloncesto', 10, 25.00, 50.00, NULL, 0, 0, NULL, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-26 00:26:12', 1, NULL),
(6, 1, 3, 'Cancha Tenis - Complejo Sur', 'TENIS', NULL, 'Cancha de tenis individual', 4, 25.00, 50.00, NULL, 0, 0, NULL, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1, NULL),
(7, 1, 4, 'Cancha Fútbol 1 - Cancha Central', 'FUTBOL', NULL, 'Cancha de fútbol profesional', 22, 25.00, 50.00, NULL, 0, 0, NULL, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1, NULL),
(8, 1, 4, 'Cancha de Básquet - Coliseo Ciudad de Loja', 'basquetbol', NULL, 'Cancha de baloncesto', 10, 25.00, 50.00, NULL, 0, 0, NULL, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:56:42', 1, NULL),
(9, 1, 4, 'Cancha Tenis - Cancha Central', 'TENIS', NULL, 'Cancha de tenis individual', 4, 25.00, 50.00, NULL, 0, 0, NULL, 'ACTIVO', '2026-01-25 23:07:00', '2026-01-25 23:07:00', 1, NULL);

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
  `dis_disponible` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S' COMMENT 'S=Disponible, N=No disponible',
  `dis_motivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mantenimiento, Reservada, Evento, etc',
  `dis_fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dis_disponibilidad_id`),
  UNIQUE KEY `uk_disp_unica` (`dis_cancha_id`,`dis_fecha`,`dis_hora_inicio`,`dis_hora_fin`),
  KEY `idx_cancha_fecha` (`dis_cancha_id`,`dis_fecha`),
  KEY `idx_disponible` (`dis_disponible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cache de disponibilidad para bÃºsquedas rÃ¡pidas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_entradas`
--

DROP TABLE IF EXISTS `instalaciones_entradas`;
CREATE TABLE IF NOT EXISTS `instalaciones_entradas` (
  `ent_entrada_id` int NOT NULL AUTO_INCREMENT,
  `ent_tenant_id` int NOT NULL,
  `ent_instalacion_id` int NOT NULL,
  `ent_cliente_id` int DEFAULT NULL,
  `ent_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ent_tipo` enum('GENERAL','VIP','CORTESIA','ABONADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GENERAL',
  `ent_precio` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ent_descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ent_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ent_forma_pago` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ent_monto_monedero` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ent_monto_efectivo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ent_estado` enum('VENDIDA','USADA','ANULADA','VENCIDA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VENDIDA',
  `ent_fecha_entrada` date NOT NULL,
  `ent_hora_entrada` time DEFAULT NULL,
  `ent_hora_salida` time DEFAULT NULL,
  `ent_observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ent_usuario_registro` int DEFAULT NULL,
  `ent_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ent_entrada_id`),
  KEY `idx_ent_tenant` (`ent_tenant_id`),
  KEY `idx_ent_fecha` (`ent_fecha_entrada`),
  KEY `idx_ent_codigo` (`ent_codigo`),
  KEY `idx_ent_cliente` (`ent_cliente_id`),
  KEY `idx_ent_estado` (`ent_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_entradas_tarifas`
--

DROP TABLE IF EXISTS `instalaciones_entradas_tarifas`;
CREATE TABLE IF NOT EXISTS `instalaciones_entradas_tarifas` (
  `ent_tar_id` int NOT NULL AUTO_INCREMENT,
  `ent_tar_tenant_id` int NOT NULL,
  `ent_tar_instalacion_id` int NOT NULL,
  `ent_tar_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ent_tar_tipo` enum('GENERAL','VIP','CORTESIA','ABONADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GENERAL',
  `ent_tar_precio` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ent_tar_dia_semana` tinyint DEFAULT NULL,
  `ent_tar_hora_inicio` time DEFAULT NULL,
  `ent_tar_hora_fin` time DEFAULT NULL,
  `ent_tar_estado` enum('ACTIVO','INACTIVO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `ent_tar_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ent_tar_id`),
  KEY `idx_etf_tenant` (`ent_tar_tenant_id`),
  KEY `idx_etf_inst` (`ent_tar_instalacion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_eventos_canchas`
--

DROP TABLE IF EXISTS `instalaciones_eventos_canchas`;
CREATE TABLE IF NOT EXISTS `instalaciones_eventos_canchas` (
  `eve_evento_id` int NOT NULL AUTO_INCREMENT,
  `eve_cancha_id` int NOT NULL,
  `eve_tipo_evento` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'MANTENIMIENTO, RESERVA, EVENTO, BLOQUEO, ESTADO_CAMBIO',
  `eve_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  `blo_tipo_bloqueo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `blo_fecha_inicio` datetime NOT NULL,
  `blo_fecha_fin` datetime NOT NULL,
  `blo_motivo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `blo_es_recurrente` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
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
  `hor_estado` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'A',
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
  `tar_nombre_tarifa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tar_tipo_cliente` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tar_aplica_dia` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tar_hora_inicio` time DEFAULT NULL,
  `tar_hora_fin` time DEFAULT NULL,
  `tar_precio_por_hora` decimal(10,2) NOT NULL,
  `tar_precio_minimo` decimal(10,2) DEFAULT NULL,
  `tar_descuento_porcentaje` decimal(5,2) DEFAULT '0.00',
  `tar_fecha_inicio_vigencia` date NOT NULL,
  `tar_fecha_fin_vigencia` date DEFAULT NULL,
  `tar_prioridad` int DEFAULT '0',
  `tar_estado` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'A',
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
  `man_tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'preventivo, correctivo, limpieza, reparacion, inspeccion, otra',
  `man_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `man_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `man_fecha_inicio` datetime NOT NULL,
  `man_fecha_fin` datetime NOT NULL,
  `man_responsable_id` int DEFAULT NULL,
  `man_recurrir` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'NO' COMMENT 'SI o NO',
  `man_cadencia_recurrencia` int DEFAULT NULL COMMENT 'Cada cuÃ¡ntos dÃ­as repetir',
  `man_estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PROGRAMADO' COMMENT 'PROGRAMADO, EN_PROGRESO, COMPLETADO, CANCELADO',
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
-- Estructura de tabla para la tabla `instalaciones_paquetes`
--

DROP TABLE IF EXISTS `instalaciones_paquetes`;
CREATE TABLE IF NOT EXISTS `instalaciones_paquetes` (
  `paq_paquete_id` int NOT NULL AUTO_INCREMENT,
  `paq_tenant_id` int NOT NULL,
  `paq_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `paq_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `paq_horas_incluidas` int NOT NULL DEFAULT '10',
  `paq_precio_normal` decimal(10,2) NOT NULL COMMENT 'Precio sin descuento',
  `paq_precio_paquete` decimal(10,2) NOT NULL COMMENT 'Precio con descuento',
  `paq_descuento_pct` decimal(5,2) DEFAULT '0.00',
  `paq_dias_vigencia` int NOT NULL DEFAULT '90',
  `paq_estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `paq_fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`paq_paquete_id`),
  KEY `idx_tenant` (`paq_tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `res_es_recurrente` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `res_reserva_padre_id` int DEFAULT NULL,
  `res_recurrencia_config` json DEFAULT NULL,
  `res_tarifa_aplicada_id` int DEFAULT NULL,
  `res_precio_base` decimal(10,2) NOT NULL,
  `res_descuento_monto` decimal(10,2) DEFAULT '0.00',
  `res_precio_total` decimal(10,2) NOT NULL,
  `res_abono_utilizado` decimal(10,2) DEFAULT '0.00',
  `res_estado_pago` enum('PENDIENTE','PARCIAL','PAGADO','REEMBOLSADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `res_monto_pagado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `res_saldo_pendiente` decimal(10,2) NOT NULL DEFAULT '0.00',
  `res_estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `res_requiere_confirmacion` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `res_fecha_confirmacion` datetime DEFAULT NULL,
  `res_observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `res_motivo_cancelacion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `res_fecha_cancelacion` datetime DEFAULT NULL,
  `res_fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
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

INSERT INTO `instalaciones_reservas` (`res_reserva_id`, `res_tenant_id`, `res_instalacion_id`, `res_cliente_id`, `res_fecha_reserva`, `res_hora_inicio`, `res_hora_fin`, `res_duracion_minutos`, `res_es_recurrente`, `res_reserva_padre_id`, `res_recurrencia_config`, `res_tarifa_aplicada_id`, `res_precio_base`, `res_descuento_monto`, `res_precio_total`, `res_abono_utilizado`, `res_estado_pago`, `res_monto_pagado`, `res_saldo_pendiente`, `res_estado`, `res_requiere_confirmacion`, `res_fecha_confirmacion`, `res_observaciones`, `res_motivo_cancelacion`, `res_fecha_cancelacion`, `res_fecha_actualizacion`, `res_fecha_registro`, `res_usuario_registro`) VALUES
(1, 1, 4, 1, '2026-01-26', '06:00:00', '07:00:00', 60, 'N', NULL, NULL, 3, 15.00, 0.00, 15.00, 0.00, 'PENDIENTE', 0.00, 15.00, 'PENDIENTE', 'S', NULL, '', NULL, NULL, NULL, '2026-01-26 00:36:19', 1),
(2, 1, 4, 1, '2026-01-26', '12:00:00', '13:00:00', 60, 'N', NULL, NULL, 4, 16.00, 0.00, 16.00, 0.00, 'PAGADO', 16.00, 0.00, 'CONFIRMADA', 'S', '2026-02-08 21:27:40', 'ok', NULL, NULL, NULL, '2026-01-26 00:40:15', 1),
(3, 1, 4, 1, '2026-02-09', '17:00:00', '18:00:00', 60, 'N', NULL, NULL, 1, 21.00, 0.00, 21.00, 5.00, 'PAGADO', 16.00, 0.00, 'CONFIRMADA', 'S', '2026-02-27 10:34:06', 'ok', NULL, NULL, '2026-02-27 15:34:06', '2026-01-26 03:23:02', 1);

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
  `pag_tipo_pago` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pag_forma_pago` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pag_referencia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pag_pasarela` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pag_transaction_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pag_estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'COMPLETADO',
  `pag_fecha_pago` datetime DEFAULT CURRENT_TIMESTAMP,
  `pag_usuario_registro` int DEFAULT NULL,
  PRIMARY KEY (`pag_pago_id`),
  KEY `tenant_id` (`pag_tenant_id`),
  KEY `idx_reserva` (`pag_reserva_id`),
  KEY `idx_fecha` (`pag_fecha_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `instalaciones_reserva_pagos`
--

INSERT INTO `instalaciones_reserva_pagos` (`pag_pago_id`, `pag_tenant_id`, `pag_reserva_id`, `pag_monto`, `pag_tipo_pago`, `pag_forma_pago`, `pag_referencia`, `pag_pasarela`, `pag_transaction_id`, `pag_estado`, `pag_fecha_pago`, `pag_usuario_registro`) VALUES
(1, 1, 2, 16.00, 'EFECTIVO', 'EFECTIVO', NULL, NULL, NULL, 'COMPLETADO', '2026-02-08 21:27:40', 1),
(2, 1, 3, 10.00, 'MIXTO', 'EFECTIVO', NULL, NULL, NULL, 'COMPLETADO', '2026-02-08 22:56:44', 1),
(3, 1, 3, 6.00, 'EFECTIVO', 'EFECTIVO', NULL, NULL, NULL, 'COMPLETADO', '2026-02-27 10:34:06', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones_sedes`
--

DROP TABLE IF EXISTS `instalaciones_sedes`;
CREATE TABLE IF NOT EXISTS `instalaciones_sedes` (
  `sed_sede_id` int NOT NULL AUTO_INCREMENT,
  `sed_tenant_id` int NOT NULL,
  `sed_codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sed_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sed_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sed_direccion` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sed_ciudad` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sed_provincia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sed_pais` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Ecuador',
  `sed_latitud` decimal(10,8) DEFAULT NULL,
  `sed_longitud` decimal(11,8) DEFAULT NULL,
  `sed_telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sed_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sed_horario_apertura` time DEFAULT NULL,
  `sed_horario_cierre` time DEFAULT NULL,
  `sed_dias_atencion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'LUNES-DOMINGO',
  `sed_superficie_total` decimal(10,2) DEFAULT NULL,
  `sed_capacidad_total` int DEFAULT NULL,
  `sed_estacionamiento` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `sed_cafeteria` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `sed_tienda` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `sed_foto_principal` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sed_galeria` json DEFAULT NULL,
  `sed_es_principal` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `sed_estado` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'A',
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
  `tip_codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tip_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tip_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tip_icono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fa-futbol',
  `tip_color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#28a745',
  `tip_requiere_equipamiento` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `tip_permite_reserva_online` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `tip_estado` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'A',
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
-- Estructura Stand-in para la vista `mantenimientos`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `mantenimientos`;
CREATE TABLE IF NOT EXISTS `mantenimientos` (
`cadencia_recurrencia` int
,`cancha_id` int
,`descripcion` text
,`estado` varchar(20)
,`fecha_actualizacion` timestamp
,`fecha_creacion` timestamp
,`fecha_fin` datetime
,`fecha_inicio` datetime
,`mantenimiento_id` int
,`notas` text
,`recurrir` varchar(2)
,`responsable_id` int
,`tenant_id` int
,`tipo` varchar(50)
,`usuario_actualizacion` int
,`usuario_creacion` int
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_asistencia`
--

DROP TABLE IF EXISTS `natacion_asistencia`;
CREATE TABLE IF NOT EXISTS `natacion_asistencia` (
  `nas_asistencia_id` int NOT NULL AUTO_INCREMENT,
  `nas_tenant_id` int NOT NULL,
  `nas_inscripcion_id` int NOT NULL COMMENT 'FK ??? natacion_inscripciones',
  `nas_grupo_id` int NOT NULL,
  `nas_alumno_id` int NOT NULL,
  `nas_fecha` date NOT NULL,
  `nas_estado` enum('PRESENTE','AUSENTE','TARDANZA','JUSTIFICADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PRESENTE',
  `nas_observacion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nas_registrado_por` int DEFAULT NULL COMMENT 'Usuario que registr??',
  `nas_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`nas_asistencia_id`),
  UNIQUE KEY `uk_nas_inscripcion_fecha` (`nas_inscripcion_id`,`nas_fecha`),
  KEY `idx_nas_grupo_fecha` (`nas_grupo_id`,`nas_fecha`),
  KEY `idx_nas_alumno` (`nas_alumno_id`,`nas_fecha`),
  KEY `fk_nas_tenant` (`nas_tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_campos_ficha`
--

DROP TABLE IF EXISTS `natacion_campos_ficha`;
CREATE TABLE IF NOT EXISTS `natacion_campos_ficha` (
  `ncf_campo_id` int NOT NULL AUTO_INCREMENT,
  `ncf_tenant_id` int NOT NULL,
  `ncf_clave` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Key en JSON: talla_traje, objetivo, etc.',
  `ncf_etiqueta` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Label visible: Talla de Traje de Ba??o',
  `ncf_tipo` enum('TEXT','TEXTAREA','SELECT','NUMBER','DATE','CHECKBOX','RADIO','EMAIL','TEL') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'TEXT',
  `ncf_opciones` json DEFAULT NULL COMMENT 'Para SELECT/RADIO: ["S","M","L","XL"]',
  `ncf_placeholder` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ncf_requerido` tinyint(1) DEFAULT '0',
  `ncf_grupo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'general' COMMENT 'medico, deportivo, personal, legal',
  `ncf_orden` int DEFAULT '0',
  `ncf_activo` tinyint(1) DEFAULT '1',
  `ncf_validacion` json DEFAULT NULL COMMENT '{"min":1,"max":100} o {"regex":"^[0-9]+$"}',
  `ncf_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ncf_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ncf_campo_id`),
  UNIQUE KEY `uk_ncf_tenant_clave` (`ncf_tenant_id`,`ncf_clave`),
  KEY `idx_ncf_orden` (`ncf_tenant_id`,`ncf_grupo`,`ncf_orden`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuraci??n de campos personalizables de ficha de alumno por tenant';

--
-- Volcado de datos para la tabla `natacion_campos_ficha`
--

INSERT INTO `natacion_campos_ficha` (`ncf_campo_id`, `ncf_tenant_id`, `ncf_clave`, `ncf_etiqueta`, `ncf_tipo`, `ncf_opciones`, `ncf_placeholder`, `ncf_requerido`, `ncf_grupo`, `ncf_orden`, `ncf_activo`, `ncf_validacion`, `ncf_created_at`, `ncf_updated_at`) VALUES
(1, 1, 'talla_traje', 'Talla de Traje de Baño', 'SELECT', '[\"4\", \"6\", \"8\", \"10\", \"12\", \"14\", \"S\", \"M\", \"L\", \"XL\"]', NULL, 0, 'personal', 1, 1, NULL, '2026-02-09 16:44:22', '2026-02-27 17:35:46'),
(2, 1, 'usa_gorra', '¿Usa Gorra de Natación?', 'CHECKBOX', NULL, NULL, 0, 'personal', 2, 1, NULL, '2026-02-09 16:44:22', '2026-02-27 17:36:41'),
(3, 1, 'usa_lentes', '¿Usa Lentes de Natación?', 'CHECKBOX', NULL, NULL, 0, 'personal', 3, 1, NULL, '2026-02-09 16:44:22', '2026-02-27 17:36:27'),
(4, 1, 'objetivo_natacion', 'Objetivo Principal', 'SELECT', '[\"Aprender a nadar\", \"Mejorar técnica\", \"Competir\", \"Terapéutico\", \"Recreativo\"]', NULL, 1, 'deportivo', 1, 0, NULL, '2026-02-09 16:44:22', '2026-03-03 17:54:02'),
(5, 1, 'como_nos_conocio', '¿Cómo nos conoció?', 'SELECT', '[\"Redes sociales\", \"Recomendaci??n\", \"Publicidad\", \"Otro\"]', NULL, 0, 'general', 1, 0, NULL, '2026-02-09 16:44:22', '2026-03-03 19:46:12'),
(6, 1, 'autoriza_fotos', 'Autoriza publicación de fotos/videos', 'CHECKBOX', NULL, NULL, 1, 'legal', 1, 0, NULL, '2026-02-09 16:44:22', '2026-03-03 19:32:15'),
(7, 1, 'observaciones_medicas_adicionales', 'Observaciones Médicas Adicionales', 'TEXTAREA', NULL, 'Ingrese cualquier informaci??n m??dica relevante...', 0, 'medico', 1, 0, NULL, '2026-02-09 16:44:22', '2026-03-03 19:46:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_carriles`
--

DROP TABLE IF EXISTS `natacion_carriles`;
CREATE TABLE IF NOT EXISTS `natacion_carriles` (
  `nca_carril_id` int NOT NULL AUTO_INCREMENT,
  `nca_tenant_id` int NOT NULL,
  `nca_piscina_id` int NOT NULL,
  `nca_numero` int NOT NULL COMMENT 'Carril 1, 2, 3...',
  `nca_nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre descriptivo opcional',
  `nca_activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`nca_carril_id`),
  UNIQUE KEY `uk_nca_piscina_numero` (`nca_piscina_id`,`nca_numero`),
  KEY `fk_nca_tenant` (`nca_tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_configuracion`
--

DROP TABLE IF EXISTS `natacion_configuracion`;
CREATE TABLE IF NOT EXISTS `natacion_configuracion` (
  `ncg_config_id` int NOT NULL AUTO_INCREMENT,
  `ncg_tenant_id` int NOT NULL,
  `ncg_clave` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ncg_valor` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ncg_tipo` enum('TEXT','NUMBER','BOOLEAN','JSON','SELECT') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'TEXT',
  `ncg_descripcion` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ncg_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ncg_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ncg_config_id`),
  UNIQUE KEY `uk_ncg_tenant_clave` (`ncg_tenant_id`,`ncg_clave`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `natacion_configuracion`
--

INSERT INTO `natacion_configuracion` (`ncg_config_id`, `ncg_tenant_id`, `ncg_clave`, `ncg_valor`, `ncg_tipo`, `ncg_descripcion`, `ncg_created_at`, `ncg_updated_at`) VALUES
(1, 1, 'nombre_modulo', 'Escuela de Natación - Test 2026-03-04 16:29:02', 'TEXT', 'Nombre personalizado del módulo', '2026-02-09 16:44:22', '2026-03-04 16:29:02'),
(2, 1, 'moneda', 'USD', 'TEXT', 'Moneda para precios', '2026-02-09 16:44:22', '2026-02-09 16:44:22'),
(3, 1, 'max_alumnos_carril', '8', 'NUMBER', 'Máximo de alumnos por carril', '2026-02-09 16:44:22', '2026-02-27 17:38:49'),
(4, 1, 'requiere_certificado_medico', 'true', 'BOOLEAN', 'Exigir certificado médico', '2026-02-09 16:44:22', '2026-02-27 17:38:16'),
(5, 1, 'edad_minima_inscripcion', '3', 'NUMBER', 'Edad mínima para inscribir', '2026-02-09 16:44:22', '2026-02-27 17:38:16'),
(6, 1, 'permite_lista_espera', 'true', 'BOOLEAN', 'Activar lista de espera', '2026-02-09 16:44:22', '2026-02-09 16:44:22'),
(7, 1, 'dias_prueba_gratis', '3', 'NUMBER', 'Días de clase de prueba gratis', '2026-02-09 16:44:22', '2026-03-04 17:04:24'),
(8, 1, 'porcentaje_asistencia_min', '70', 'NUMBER', 'Porcentaje mínimo de asistencia', '2026-02-09 16:44:22', '2026-02-27 17:38:27'),
(9, 1, 'escala_evaluacion', '5', 'NUMBER', 'Escala de evaluación (1-5 estrellas o 1-10)', '2026-02-09 16:44:22', '2026-02-27 17:38:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_egresos`
--

DROP TABLE IF EXISTS `natacion_egresos`;
CREATE TABLE IF NOT EXISTS `natacion_egresos` (
  `neg_egreso_id` int NOT NULL AUTO_INCREMENT,
  `neg_tenant_id` int NOT NULL,
  `neg_sede_id` int DEFAULT NULL COMMENT 'FK → instalaciones_sedes',
  `neg_categoria` enum('MANTENIMIENTO','INSUMOS','QUIMICOS','SERVICIOS','PERSONAL','EQUIPAMIENTO','SEGUROS','MARKETING','OTROS') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'OTROS',
  `neg_concepto` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Descripción del gasto',
  `neg_monto` decimal(10,2) NOT NULL,
  `neg_fecha` date NOT NULL,
  `neg_proveedor` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `neg_factura_ref` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nro factura o documento soporte',
  `neg_metodo_pago` enum('EFECTIVO','TARJETA','TRANSFERENCIA','CHEQUE','OTRO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'EFECTIVO',
  `neg_referencia_pago` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `neg_estado` enum('REGISTRADO','PAGADO','ANULADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'REGISTRADO',
  `neg_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `neg_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `neg_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`neg_egreso_id`),
  KEY `idx_neg_tenant_sede` (`neg_tenant_id`,`neg_sede_id`),
  KEY `idx_neg_fecha` (`neg_fecha`),
  KEY `idx_neg_categoria` (`neg_categoria`),
  KEY `fk_neg_sede` (`neg_sede_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Egresos/gastos operativos de natación por sede';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_evaluaciones`
--

DROP TABLE IF EXISTS `natacion_evaluaciones`;
CREATE TABLE IF NOT EXISTS `natacion_evaluaciones` (
  `nev_evaluacion_id` int NOT NULL AUTO_INCREMENT,
  `nev_tenant_id` int NOT NULL,
  `nev_alumno_id` int NOT NULL,
  `nev_habilidad_id` int NOT NULL COMMENT 'FK ??? natacion_nivel_habilidades',
  `nev_nivel_id` int NOT NULL COMMENT 'FK ??? natacion_niveles (nivel evaluado)',
  `nev_calificacion` tinyint UNSIGNED DEFAULT '0' COMMENT '0-5 estrellas o 0-100',
  `nev_aprobado` tinyint(1) DEFAULT '0',
  `nev_fecha` date NOT NULL,
  `nev_evaluador_id` int DEFAULT NULL COMMENT 'FK ??? natacion_instructores',
  `nev_observacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nev_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`nev_evaluacion_id`),
  UNIQUE KEY `uk_nev_alumno_habilidad_fecha` (`nev_alumno_id`,`nev_habilidad_id`,`nev_fecha`),
  KEY `idx_nev_alumno` (`nev_alumno_id`,`nev_nivel_id`),
  KEY `fk_nev_tenant` (`nev_tenant_id`),
  KEY `fk_nev_habilidad` (`nev_habilidad_id`),
  KEY `fk_nev_nivel` (`nev_nivel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_ficha_alumno`
--

DROP TABLE IF EXISTS `natacion_ficha_alumno`;
CREATE TABLE IF NOT EXISTS `natacion_ficha_alumno` (
  `nfa_ficha_id` int NOT NULL AUTO_INCREMENT,
  `nfa_tenant_id` int NOT NULL,
  `nfa_alumno_id` int NOT NULL COMMENT 'FK ??? alumnos',
  `nfa_nivel_actual_id` int DEFAULT NULL COMMENT 'FK ??? natacion_niveles',
  `nfa_sabe_nadar` tinyint(1) DEFAULT '0',
  `nfa_experiencia_previa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nfa_objetivo` enum('RECREATIVO','FORMATIVO','COMPETITIVO','TERAPEUTICO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'RECREATIVO',
  `nfa_autorizacion_medica` tinyint(1) DEFAULT '0' COMMENT 'Certificado m??dico entregado',
  `nfa_seguro_medico` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfa_fecha_ingreso` date DEFAULT NULL,
  `nfa_fecha_ultimo_avance` date DEFAULT NULL,
  `nfa_datos_custom` json DEFAULT NULL COMMENT 'Valores de campos definidos en natacion_campos_ficha',
  `nfa_documentos` json DEFAULT NULL COMMENT '[{"tipo":"certificado_medico","url":"...","fecha":"..."}]',
  `nfa_activo` tinyint(1) DEFAULT '1',
  `nfa_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nfa_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `nfa_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`nfa_ficha_id`),
  UNIQUE KEY `uk_nfa_tenant_alumno` (`nfa_tenant_id`,`nfa_alumno_id`),
  KEY `idx_nfa_nivel` (`nfa_nivel_actual_id`),
  KEY `fk_nfa_alumno` (`nfa_alumno_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ficha de nataci??n del alumno ??? extensi??n con datos custom JSON';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_grupos`
--

DROP TABLE IF EXISTS `natacion_grupos`;
CREATE TABLE IF NOT EXISTS `natacion_grupos` (
  `ngr_grupo_id` int NOT NULL AUTO_INCREMENT,
  `ngr_tenant_id` int NOT NULL,
  `ngr_sede_id` int DEFAULT NULL COMMENT 'FK → instalaciones_sedes',
  `ngr_periodo_id` int DEFAULT NULL COMMENT 'FK ??? natacion_periodos',
  `ngr_nivel_id` int DEFAULT NULL COMMENT 'FK ??? natacion_niveles',
  `ngr_piscina_id` int DEFAULT NULL COMMENT 'FK ??? natacion_piscinas',
  `ngr_instructor_id` int DEFAULT NULL COMMENT 'FK ??? natacion_instructores',
  `ngr_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: Nivel 1 - Grupo A Ma??ana',
  `ngr_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ngr_cupo_maximo` int DEFAULT '10',
  `ngr_cupo_actual` int DEFAULT '0',
  `ngr_edad_min` int DEFAULT NULL,
  `ngr_edad_max` int DEFAULT NULL,
  `ngr_precio` decimal(10,2) DEFAULT '0.00' COMMENT 'Precio mensual/per??odo',
  `ngr_estado` enum('ABIERTO','CERRADO','EN_CURSO','FINALIZADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ABIERTO',
  `ngr_color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#0EA5E9',
  `ngr_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ngr_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ngr_grupo_id`),
  KEY `idx_ngr_tenant_estado` (`ngr_tenant_id`,`ngr_estado`),
  KEY `idx_ngr_periodo` (`ngr_periodo_id`),
  KEY `idx_ngr_nivel` (`ngr_nivel_id`),
  KEY `fk_ngr_piscina` (`ngr_piscina_id`),
  KEY `fk_ngr_instructor` (`ngr_instructor_id`),
  KEY `idx_ngr_sede` (`ngr_sede_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_grupo_horarios`
--

DROP TABLE IF EXISTS `natacion_grupo_horarios`;
CREATE TABLE IF NOT EXISTS `natacion_grupo_horarios` (
  `ngh_horario_id` int NOT NULL AUTO_INCREMENT,
  `ngh_tenant_id` int NOT NULL,
  `ngh_grupo_id` int NOT NULL,
  `ngh_dia_semana` enum('LUN','MAR','MIE','JUE','VIE','SAB','DOM') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ngh_hora_inicio` time NOT NULL,
  `ngh_hora_fin` time NOT NULL,
  `ngh_carril_id` int DEFAULT NULL COMMENT 'FK ??? natacion_carriles (opcional)',
  `ngh_activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`ngh_horario_id`),
  KEY `idx_ngh_grupo` (`ngh_grupo_id`),
  KEY `idx_ngh_dia` (`ngh_tenant_id`,`ngh_dia_semana`,`ngh_hora_inicio`),
  KEY `fk_ngh_carril` (`ngh_carril_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_inscripciones`
--

DROP TABLE IF EXISTS `natacion_inscripciones`;
CREATE TABLE IF NOT EXISTS `natacion_inscripciones` (
  `nis_inscripcion_id` int NOT NULL AUTO_INCREMENT,
  `nis_tenant_id` int NOT NULL,
  `nis_alumno_id` int NOT NULL COMMENT 'FK ??? alumnos',
  `nis_grupo_id` int NOT NULL COMMENT 'FK ??? natacion_grupos',
  `nis_periodo_id` int DEFAULT NULL,
  `nis_fecha_inscripcion` date NOT NULL,
  `nis_fecha_baja` date DEFAULT NULL,
  `nis_monto` decimal(10,2) DEFAULT '0.00',
  `nis_descuento` decimal(10,2) DEFAULT '0.00',
  `nis_monto_final` decimal(10,2) DEFAULT '0.00',
  `nis_estado` enum('ACTIVA','SUSPENDIDA','FINALIZADA','CANCELADA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVA',
  `nis_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nis_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `nis_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`nis_inscripcion_id`),
  UNIQUE KEY `uk_nis_alumno_grupo` (`nis_alumno_id`,`nis_grupo_id`),
  KEY `idx_nis_tenant_estado` (`nis_tenant_id`,`nis_estado`),
  KEY `idx_nis_grupo` (`nis_grupo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_instructores`
--

DROP TABLE IF EXISTS `natacion_instructores`;
CREATE TABLE IF NOT EXISTS `natacion_instructores` (
  `nin_instructor_id` int NOT NULL AUTO_INCREMENT,
  `nin_tenant_id` int NOT NULL,
  `nin_sede_id` int DEFAULT NULL COMMENT 'FK → instalaciones_sedes',
  `nin_usuario_id` int DEFAULT NULL COMMENT 'FK opcional ??? seguridad_usuarios (si tiene login)',
  `nin_nombres` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nin_apellidos` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nin_identificacion` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nin_email` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nin_telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nin_especialidad` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Beb??s, Competitivo, Adultos, etc.',
  `nin_certificaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nin_foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nin_color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#3B82F6' COMMENT 'Color en calendario',
  `nin_activo` tinyint(1) DEFAULT '1',
  `nin_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nin_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `nin_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`nin_instructor_id`),
  KEY `idx_nin_tenant` (`nin_tenant_id`,`nin_activo`),
  KEY `idx_nin_sede` (`nin_sede_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_lista_espera`
--

DROP TABLE IF EXISTS `natacion_lista_espera`;
CREATE TABLE IF NOT EXISTS `natacion_lista_espera` (
  `nle_espera_id` int NOT NULL AUTO_INCREMENT,
  `nle_tenant_id` int NOT NULL,
  `nle_alumno_id` int NOT NULL,
  `nle_grupo_id` int NOT NULL,
  `nle_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `nle_posicion` int DEFAULT '0',
  `nle_estado` enum('ESPERANDO','NOTIFICADO','INSCRITO','CANCELADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ESPERANDO',
  `nle_notas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`nle_espera_id`),
  UNIQUE KEY `uk_nle_alumno_grupo` (`nle_alumno_id`,`nle_grupo_id`),
  KEY `idx_nle_grupo_estado` (`nle_grupo_id`,`nle_estado`,`nle_posicion`),
  KEY `fk_nle_tenant` (`nle_tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_niveles`
--

DROP TABLE IF EXISTS `natacion_niveles`;
CREATE TABLE IF NOT EXISTS `natacion_niveles` (
  `nnv_nivel_id` int NOT NULL AUTO_INCREMENT,
  `nnv_tenant_id` int NOT NULL,
  `nnv_nombre` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Principiante, Intermedio, Avanzado',
  `nnv_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'N1, N2, N3',
  `nnv_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nnv_color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#3B82F6' COMMENT 'Color identificativo',
  `nnv_orden` int DEFAULT '0',
  `nnv_edad_min` int DEFAULT NULL COMMENT 'Edad m??nima sugerida',
  `nnv_edad_max` int DEFAULT NULL COMMENT 'Edad m??xima sugerida',
  `nnv_activo` tinyint(1) DEFAULT '1',
  `nnv_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`nnv_nivel_id`),
  UNIQUE KEY `uk_nnv_tenant_codigo` (`nnv_tenant_id`,`nnv_codigo`),
  KEY `idx_nnv_orden` (`nnv_tenant_id`,`nnv_orden`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `natacion_niveles`
--

INSERT INTO `natacion_niveles` (`nnv_nivel_id`, `nnv_tenant_id`, `nnv_nombre`, `nnv_codigo`, `nnv_descripcion`, `nnv_color`, `nnv_orden`, `nnv_edad_min`, `nnv_edad_max`, `nnv_activo`, `nnv_created_at`) VALUES
(1, 1, 'Adaptación al Agua', 'N0', 'Familiarización con el medio acuático. Pérdida del miedo, flotación asistida.', '#94A3B8', 1, 3, 5, 1, '2026-02-09 16:44:22'),
(2, 1, 'Principiante', 'N1', 'Flotación, patada básica, desplazamiento con tabla, inmersiones cortas.', '#22C55E', 2, 4, 99, 1, '2026-02-09 16:44:22'),
(3, 1, 'Básico', 'N2', 'Crol básico, espalda básica, respiración lateral, zambullidas.', '#3B82F6', 3, 5, 99, 1, '2026-02-09 16:44:22'),
(4, 1, 'Intermedio', 'N3', 'Crol y espalda completos, introducción a pecho, virajes simples.', '#8B5CF6', 4, 6, 99, 1, '2026-02-09 16:44:22'),
(5, 1, 'Avanzado', 'N4', 'Los 4 estilos completos, virajes, salidas desde bloque.', '#F59E0B', 5, 7, 99, 1, '2026-02-09 16:44:22'),
(6, 1, 'Competitivo', 'N5', 'Perfeccionamiento de estilos, entrenamiento de velocidad y resistencia.', '#EF4444', 6, 8, 99, 1, '2026-02-09 16:44:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_nivel_habilidades`
--

DROP TABLE IF EXISTS `natacion_nivel_habilidades`;
CREATE TABLE IF NOT EXISTS `natacion_nivel_habilidades` (
  `nnh_habilidad_id` int NOT NULL AUTO_INCREMENT,
  `nnh_tenant_id` int NOT NULL,
  `nnh_nivel_id` int NOT NULL,
  `nnh_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Patada flutter, Respiraci??n lateral, Crol 25m',
  `nnh_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nnh_orden` int DEFAULT '0',
  `nnh_activo` tinyint(1) DEFAULT '1',
  `nnh_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`nnh_habilidad_id`),
  KEY `idx_nnh_nivel` (`nnh_nivel_id`,`nnh_orden`),
  KEY `fk_nnh_tenant` (`nnh_tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `natacion_nivel_habilidades`
--

INSERT INTO `natacion_nivel_habilidades` (`nnh_habilidad_id`, `nnh_tenant_id`, `nnh_nivel_id`, `nnh_nombre`, `nnh_descripcion`, `nnh_orden`, `nnh_activo`, `nnh_created_at`) VALUES
(1, 1, 2, 'Flotación dorsal', 'Flotación boca arriba sin asistencia por 10 segundos', 1, 1, '2026-02-09 16:44:22'),
(2, 1, 2, 'Flotación ventral', 'Flotacón boca abajo con cara en el agua por 10 segundos', 2, 1, '2026-02-09 16:44:22'),
(3, 1, 2, 'Patada con tabla', 'Desplazamiento de 15m con tabla usando patada flutter', 3, 1, '2026-02-09 16:44:22'),
(4, 1, 2, 'Inmersión', 'Sumergirse completamente y recoger objeto del fondo', 4, 1, '2026-02-09 16:44:22'),
(5, 1, 2, 'Respiración rítmica', 'Inspiración fuera, exhalación dentro del agua (10 repeticiones)', 5, 1, '2026-02-09 16:44:22'),
(6, 1, 3, 'Crol 25m', 'Nado crol completo 25 metros con respiración lateral', 1, 1, '2026-02-09 16:44:22'),
(7, 1, 3, 'Espalda 25m', 'Nado espalda completo 25 metros', 2, 1, '2026-02-09 16:44:22'),
(8, 1, 3, 'Respiración bilateral', 'Respiración por ambos lados en crol', 3, 1, '2026-02-09 16:44:22'),
(9, 1, 3, 'Zambullida de pie', 'Entrada al agua de pie desde el borde', 4, 1, '2026-02-09 16:44:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_pagos`
--

DROP TABLE IF EXISTS `natacion_pagos`;
CREATE TABLE IF NOT EXISTS `natacion_pagos` (
  `npg_pago_id` int NOT NULL AUTO_INCREMENT,
  `npg_tenant_id` int NOT NULL,
  `npg_sede_id` int DEFAULT NULL COMMENT 'FK → instalaciones_sedes',
  `npg_inscripcion_id` int DEFAULT NULL,
  `npg_alumno_id` int NOT NULL,
  `npg_cliente_id` int DEFAULT NULL COMMENT 'FK ??? clientes (quien paga = representante)',
  `npg_concepto` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mensualidad Febrero, Matr??cula, etc.',
  `npg_monto` decimal(10,2) NOT NULL,
  `npg_descuento` decimal(10,2) DEFAULT '0.00',
  `npg_total` decimal(10,2) NOT NULL,
  `npg_metodo_pago` enum('EFECTIVO','TARJETA','TRANSFERENCIA','DEPOSITO','ABONO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'EFECTIVO',
  `npg_referencia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nro transferencia, voucher, etc.',
  `npg_fecha` date NOT NULL,
  `npg_estado` enum('PENDIENTE','PAGADO','ANULADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `npg_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `npg_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `npg_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`npg_pago_id`),
  KEY `idx_npg_tenant_estado` (`npg_tenant_id`,`npg_estado`),
  KEY `idx_npg_alumno` (`npg_alumno_id`),
  KEY `idx_npg_cliente` (`npg_cliente_id`),
  KEY `fk_npg_inscripcion` (`npg_inscripcion_id`),
  KEY `idx_npg_sede` (`npg_sede_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_periodos`
--

DROP TABLE IF EXISTS `natacion_periodos`;
CREATE TABLE IF NOT EXISTS `natacion_periodos` (
  `npe_periodo_id` int NOT NULL AUTO_INCREMENT,
  `npe_tenant_id` int NOT NULL,
  `npe_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: Enero-Marzo 2026',
  `npe_fecha_inicio` date NOT NULL,
  `npe_fecha_fin` date NOT NULL,
  `npe_estado` enum('PLANIFICADO','ACTIVO','FINALIZADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PLANIFICADO',
  `npe_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `npe_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `npe_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`npe_periodo_id`),
  KEY `idx_npe_tenant_estado` (`npe_tenant_id`,`npe_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `natacion_piscinas`
--

DROP TABLE IF EXISTS `natacion_piscinas`;
CREATE TABLE IF NOT EXISTS `natacion_piscinas` (
  `npi_piscina_id` int NOT NULL AUTO_INCREMENT,
  `npi_tenant_id` int NOT NULL,
  `npi_sede_id` int DEFAULT NULL COMMENT 'FK → instalaciones_sedes',
  `npi_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Piscina Ol??mpica, Piscina Ni??os',
  `npi_tipo` enum('OLIMPICA','SEMI_OLIMPICA','RECREATIVA','TERAPEUTICA','INFANTIL') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'SEMI_OLIMPICA',
  `npi_largo` decimal(5,2) DEFAULT NULL COMMENT 'Metros',
  `npi_ancho` decimal(5,2) DEFAULT NULL,
  `npi_profundidad_min` decimal(3,2) DEFAULT NULL,
  `npi_profundidad_max` decimal(3,2) DEFAULT NULL,
  `npi_num_carriles` int DEFAULT '6',
  `npi_temperatura` decimal(4,1) DEFAULT NULL COMMENT 'Temperatura del agua ??C',
  `npi_ubicacion` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npi_foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npi_activo` tinyint(1) DEFAULT '1',
  `npi_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `npi_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`npi_piscina_id`),
  KEY `idx_npi_tenant` (`npi_tenant_id`,`npi_activo`),
  KEY `idx_npi_sede` (`npi_sede_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `roles`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
`codigo` varchar(50)
,`descripcion` text
,`estado` char(1)
,`fecha_registro` timestamp
,`nombre` varchar(100)
,`permisos` json
,`rol_id` int
,`tenant_id` int
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_auditoria`
--

DROP TABLE IF EXISTS `seguridad_auditoria`;
CREATE TABLE IF NOT EXISTS `seguridad_auditoria` (
  `aud_auditoria_id` bigint NOT NULL AUTO_INCREMENT,
  `aud_tenant_id` int DEFAULT NULL,
  `aud_usuario_id` int DEFAULT NULL,
  `aud_modulo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aud_tabla` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aud_registro_id` int DEFAULT NULL,
  `aud_operacion` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aud_valores_anteriores` json DEFAULT NULL,
  `aud_valores_nuevos` json DEFAULT NULL,
  `aud_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aud_user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `aud_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aud_metodo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aud_fecha_operacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`aud_auditoria_id`),
  KEY `idx_tenant` (`aud_tenant_id`),
  KEY `idx_usuario` (`aud_usuario_id`),
  KEY `idx_tabla` (`aud_tabla`),
  KEY `idx_fecha` (`aud_fecha_operacion`),
  KEY `idx_operacion` (`aud_operacion`)
) ENGINE=InnoDB AUTO_INCREMENT=244 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(178, NULL, NULL, 'seguridad', 'seguridad_modulos', 7, 'editar_modulo', '{\"mod_id\": 7, \"mod_icono\": \"fas fa-wallet\", \"mod_orden\": 7, \"mod_activo\": 1, \"mod_codigo\": \"abonos\", \"mod_nombre\": \"Abonos\", \"mod_created_at\": \"2026-01-26 00:37:36\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-01-26 00:37:36\", \"mod_color_fondo\": \"#F472B6\", \"mod_descripcion\": \"Sistema de prepagos y saldos a favor para tus clientes frecuentes.\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"reservas\", \"mod_url_externa\": null, \"mod_ruta_controller\": \"abon\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-wallet\", \"mod_orden\": 7, \"mod_activo\": 0, \"mod_codigo\": \"ABONOS\", \"mod_nombre\": \"Abonos\", \"mod_es_externo\": 0, \"mod_color_fondo\": \"#F472B6\", \"mod_descripcion\": \"Sistema de prepagos y saldos a favor para tus clientes frecuentes.\", \"mod_url_externa\": \"\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=hk7i5ut9tMSCS7ZnKWdhlg2IWhxQ-jAgnYS3nlpOuqDdbPCGYC-dlurB5oOmN6EgzJppdK-WUVVtdtSRsNLrvPwQLaCI5N8GnvBwmk2gAYAtuOZP4DTpYLtokTVg23Wkww,,', 'POST', '2026-02-08 02:34:53'),
(179, NULL, NULL, 'seguridad', 'seguridad_modulos', 1, 'editar_modulo', '{\"mod_id\": 1, \"mod_icono\": \"fas fa-building\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"ARENA\", \"mod_nombre\": \"DigiSports Arena\", \"mod_created_at\": \"2026-01-26 00:37:36\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-02-07 20:31:06\", \"mod_color_fondo\": \"#3B82F6\", \"mod_descripcion\": \"Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"instalaciones\", \"mod_url_externa\": null, \"mod_ruta_controller\": \"dashboard\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-building\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"ARENA\", \"mod_nombre\": \"DigiSports Arena\", \"mod_es_externo\": 0, \"mod_color_fondo\": \"#FF7E70\", \"mod_descripcion\": \"Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.\", \"mod_url_externa\": \"\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=jlntXTWnN6TfpCFZVr68JZNxkLqyQr5b64-MZInuiArFyFDINUUpzu2Xs_7mcgZ6AXIXVrjtnYGmBXJ-5A4CTrYk-0beBKxBmNy3xyOvDmMwVBxiLUDTQin7p7-7q-F47Q,,', 'POST', '2026-02-08 04:36:56'),
(180, NULL, NULL, 'seguridad', 'usuario', 3, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::S9lLJ/U8PQ3Zn9c5+MQE8mQk/Ygez9E8OKvqqiFbOPmua10CTkRrecsGrWyLFxFt\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Freddy\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$dVNpUFJ1cTlDMFJieGVtUQ$6Yemzxhz01i9O3cwvnZLj7QP21uCrqtjoBFejtvYjhw\", \"usu_telefono\": \"ENC::U3UCWjTRKGSRSdBZzdVMZsRyvRxb/G8afb971ejNLSQ=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": null, \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::NEmIjD+VPQrPLjlfM+csqySSsHoLvBESfDOXVI7FIEw=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": null, \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-07 23:32:07\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::4fcCHpy/DqB36mt8CehWkhhA1AvQrqn3EnSkKypEhguLhNnLMqrj8OfJLWl9Dw1T\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Freddy\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$L1R5WUNKSFluNW5GOHBHcQ$4R1wc00hUfEvo+qH2ndv/mB3nH9QErMLPUyOjVhA5FA\", \"usu_telefono\": \"ENC::2oSsVBFSKO40Vdd7uoKb+GlynV28gyJlndAwpqpf4Tc=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": null, \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::NFC5Wr/jbvRGLxs7srfvX6NbSKDcoEumraEDWrAJM6I=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": null, \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-19 08:49:21\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=xrclJxYbD1O3-Bd-RZ8mNvfzlL_Z2dqzdehGb2nCgHR0hYfDk2gbMk2koNFJ5M3W5ix9j5cjWFsq4OWnsTaXE28Y1id1Q0Y43EfBab0Tdl0zwjETgXzQEJjngPv7yIIPoT-c1kMWzlU,', 'POST', '2026-02-19 13:49:21'),
(181, NULL, NULL, 'seguridad', 'seguridad_modulos', 22, 'editar_modulo', '{\"mod_id\": 22, \"mod_icono\": \"fas fa-shield-alt\", \"mod_orden\": 98, \"mod_activo\": 1, \"mod_codigo\": \"SEGURIDAD\", \"mod_nombre\": \"Seguridad\", \"mod_created_at\": \"2026-02-02 15:52:19\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-02-07 18:51:03\", \"mod_color_fondo\": \"#EF4444\", \"mod_descripcion\": \"\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"seguridad\", \"mod_url_externa\": null, \"mod_ruta_controller\": \"dashboard\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-shield-alt\", \"mod_orden\": 98, \"mod_activo\": 1, \"mod_codigo\": \"SEGURIDAD\", \"mod_nombre\": \"Seguridad\", \"mod_es_externo\": 0, \"mod_color_fondo\": \"#EF4444\", \"mod_descripcion\": \"2FA, encriptación AES-256, auditoría completa y protección avanzada.\", \"mod_url_externa\": \"\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=A4tTsyC4k6AqMbRKs8OAEGG-v9hLrKvn04J3jODPEDuC_bzd7pNRoduW0cy7OyIWcd8EaQ8oWCh2vPUuiQulbh4MSxkTW-cHYLA5jDaevlLqXiAUBRyguI-ymvPZ-YqnLg,,', 'POST', '2026-02-19 13:53:05'),
(182, 1, 1, 'Reservas', 'abonos', 1, 'RECARGA', '{\"saldo\": 5}', '{\"saldo\": 15, \"monto_recarga\": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=Y-TnsXdxYbktCYwLX_v5EGc_jSyFpZYNx2vARvhQMUo0I0Smma07FVuSSZ4oX5WWnI-ADuCh6_IgZ4p_G5VQqLU_-c74JMMEHNeTj1n3t14BXI_rcWHpFsQejTBGBZai', 'POST', '2026-02-09 04:55:20'),
(183, 1, 1, 'Reservas', 'abonos', 1, 'RECARGA', '{\"saldo\": 15}', '{\"saldo\": 27.75, \"monto_recarga\": 12.75}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=9lUNZ6peNCLIVlUIqfLr4WNjyfxF9gd5PMX_V0YNV885xSWHrnqtwC4Ec93hrNh6GXCfle-sBuw0flOjS2L3v_sbaUgCtqIeh8FiAQIYhEPlrdeOR4XQ5D-ee1yyWcto', 'POST', '2026-02-09 04:55:49'),
(184, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=lTcQD7wHkyDyFna6yKqCqjL9j9untRuGSCU-Xfmk-bKN5X4eeSx-Yv2wFhfFhr9LbnwU2j3EMaNn8jStegZXdnPhEXAK_aT5bl6Dr5m5XdqCaZp5L5ynrWs0', 'GET', '2026-02-19 13:49:57'),
(185, 2, 3, 'Core', 'usuarios', 3, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=1NAJQMGvadzJIbqtD74H6sDMCp0NWJvckaJhe4U6_QYw-GAoqTpWj14SrlNaWuleNDZIe1xCfbyjNjmJb-YE2hFbDQ6l721Xl1HFxm3dchTHyQtgxR66SKLy', 'GET', '2026-02-19 13:50:48'),
(186, 2, 3, 'Core', 'usuarios', 3, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=3JQR6aCrAJL62ya-TlsyDxNBv4JrcETriHcrv2uMFXPzmxK7TOpnEx1tdPJmYK_jgHpc86sl_F2ul2rrCvCEoq1-t_oJD8SyhL-AsFQyKB37P5GcUh98UXRo', 'GET', '2026-02-19 15:01:41'),
(187, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=o3KuStlPRs6A9qvbdO-vAY5wSkQ8aUIuU_NrIG0YA3FFP_tyPfF2C59eLmVhEFRuj05djyweUW5tc8yGl7St8Mmeu9NIE1v8V7TrnGmofMGMFfiBCIMaADSC', 'GET', '2026-02-19 17:11:02'),
(188, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=Hp6OzlYKHAHsD2GoQJhrJk_1e9iiNdsRa_V__NMYSBuobWQA9CSYUN0qOT3JUE-IWeOCktR4o1kEyqAblV_OUBIknCjS2OhaJT42T5cih1MMxXKKCpjrPWcj', 'GET', '2026-02-19 17:15:08'),
(189, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=hO98WWynS5xqfxDhWsbq2AtM5l22vA08pKwix44_IC_CSt23N3z4ObmNm1D-KDKfClLz78-IhVKdfBliCypGXcPVu6FRHHXuADXemy7VzEot_QFtPzQII35Z', 'GET', '2026-02-19 17:20:26'),
(190, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=hO98WWynS5xqfxDhWsbq2AtM5l22vA08pKwix44_IC_CSt23N3z4ObmNm1D-KDKfClLz78-IhVKdfBliCypGXcPVu6FRHHXuADXemy7VzEot_QFtPzQII35Z', 'GET', '2026-02-19 17:20:46'),
(191, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=hO98WWynS5xqfxDhWsbq2AtM5l22vA08pKwix44_IC_CSt23N3z4ObmNm1D-KDKfClLz78-IhVKdfBliCypGXcPVu6FRHHXuADXemy7VzEot_QFtPzQII35Z', 'GET', '2026-02-19 17:21:02'),
(192, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=50oTpttdHGRp2eREYt92ymrrnMGy4LeogbE1CV1IJkpc3F86GLRU3CmVK1xMj3W3eAQy21Fx-ooDkC5ukikO9ZUu-owereXSXKX2yVC5gMNiw51qrv1s5OQy', 'GET', '2026-02-19 17:21:30'),
(193, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=50oTpttdHGRp2eREYt92ymrrnMGy4LeogbE1CV1IJkpc3F86GLRU3CmVK1xMj3W3eAQy21Fx-ooDkC5ukikO9ZUu-owereXSXKX2yVC5gMNiw51qrv1s5OQy', 'GET', '2026-02-19 17:21:47'),
(194, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=2qdMYMRgb2A0DrZSrzIhMRcr8DqFiJac5oaReAKtry32cBfqllD0STO5kbUzm0WkHZAvWroPTSY_-3x_PGP7DHczoqp4tDxEOAjhYrawsJqLFjvF7tpX-aJN', 'GET', '2026-02-19 17:27:16'),
(195, 1, 1, 'Reservas', 'abonos', 1, 'RECARGA', '{\"saldo\": 27.75}', '{\"saldo\": 37.75, \"monto_recarga\": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=zomB3hppUcXjFoiXnrEb0MKM0IlXgmp4-9dYcpLoubjInLf4aZLApkhZPJU4HqCO0GOyNg5R2oDB-gZh6JWj1xpj11VLiGmuzRTh9nlq9Lq5oWdB6LvXMtnvaHDL3ZM_', 'POST', '2026-02-19 17:28:37'),
(197, NULL, NULL, 'Core', 'usuarios', NULL, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=0nHoQqsyytRNr9ak8iQBiA-dAYo0pxZExnU6cU6wQRMiKotk1G7R2jT8dsVWPW29eCkGluspsH4vRmHMHGycMNui5mV1_CC0XckXLjjkya27TwCfP6AMgBA7NBEZ3lUs', 'POST', '2026-02-19 20:01:16'),
(198, 1, 1, 'Core', 'usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=_nif1PwWVh3nHtU8yuW0j-Wy1UP04E-dPkfmNV8yZbnptiJSdR_L6x3K2c11Q3YLFnIDXcaQsXKdm5cnBzmd96FLJCcVqCRhV11AbdwdYe7CAXotkZisIesA', 'GET', '2026-02-19 20:05:25'),
(199, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '/digisports/public/index.php?r=M3kRiX1lyZBhIfqafwDCEkAq-9osUuLiYOgjN4zgfOfNUvgEWC8eVYoznf7KjWlit5gToKw8II35M4kMBHvub6PuXkawHzJRvLpIzoUOCQ7WfjySkumlQTCPmfO2HW4w', 'POST', '2026-02-19 20:13:47'),
(200, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=JQvQP3v_Egy-n6ANe0ZMH3L1usYDjG5GNAWsEXRsfIHtAFJyNk7gQ7kOJoG15y9W0e__Hnp8nmeRRw--f2D5Wsmqp1UQRpJHF35T1-s0ItHVVyHsGrymzD5C5Igpywbv', 'POST', '2026-02-24 19:50:10'),
(201, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=f6YA-cHQCfAvpT6r5QWRImxGBl3ipR3I7hTvMjiDgqjzz7s9btjQv6pyK0M6l_kZ3zvb9XYU7ye_pgJT5Yz6EssSL8xhxYCQO3F1o94tGdkP3J6uxLQtKsAUKe4IacIf', 'POST', '2026-02-25 17:18:35'),
(202, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=BUWIxi8Iah3NRWBLiRS96W7cJdUYyEWm9VONj9yaY8hF4RonaljiT9j2ZkDkahhV-9OjeOvtw6LV-0VXgxtKLCtOCnBtnqXPfw1Vw6oYWwnk82gQnGxYYdtN0oDhpA4N', 'POST', '2026-02-25 19:20:13'),
(203, NULL, NULL, 'seguridad', 'usuario', 3, 'eliminar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::4fcCHpy/DqB36mt8CehWkhhA1AvQrqn3EnSkKypEhguLhNnLMqrj8OfJLWl9Dw1T\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Freddy\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$aXVaaTRSTFZCWWpoOElheA$WfA639tX4FghuHN1ELLTeTXlQ2m5ISy3BHPx9HZVyIM\", \"usu_telefono\": \"ENC::2oSsVBFSKO40Vdd7uoKb+GlynV28gyJlndAwpqpf4Tc=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-19 10:01:30\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::NFC5Wr/jbvRGLxs7srfvX6NbSKDcoEumraEDWrAJM6I=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 14:35:50\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"S\", \"usu_token_recuperacion_expira\": null}', NULL, '::1', NULL, '/digisports/public/index.php?r=COXj5lo7NFY2154OQEfjikSnYK5Q-k_IOTPWz2kJs8AR2E4yAmNhYL0M62kWNjgpqCoQ--z_FNWW4QItj2DFqaxbT6YZGUcXZosEwJ--i_0OJ6ghKcXdFwGxTCYzC9z5eDvmgf8BLV2IaA,,&ajax=1', 'POST', '2026-02-25 19:46:52'),
(204, NULL, NULL, 'seguridad', 'usuario', 3, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::4fcCHpy/DqB36mt8CehWkhhA1AvQrqn3EnSkKypEhguLhNnLMqrj8OfJLWl9Dw1T\", \"usu_avatar\": null, \"usu_estado\": \"E\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Freddy\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$aXVaaTRSTFZCWWpoOElheA$WfA639tX4FghuHN1ELLTeTXlQ2m5ISy3BHPx9HZVyIM\", \"usu_telefono\": \"ENC::2oSsVBFSKO40Vdd7uoKb+GlynV28gyJlndAwpqpf4Tc=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-19 10:01:30\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::NFC5Wr/jbvRGLxs7srfvX6NbSKDcoEumraEDWrAJM6I=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 14:46:52\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"S\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::5AmeZheimJQZYQtfcgd+htYRHVl+P5K0sFeUectB2ue4Is3C3ZnhaRhvNW98XmE2\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Freddy\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$aXVaaTRSTFZCWWpoOElheA$WfA639tX4FghuHN1ELLTeTXlQ2m5ISy3BHPx9HZVyIM\", \"usu_telefono\": \"ENC::/EzkMUFgFy7QybsBa8sruiJTWpxi/nKtcvPtH3yta4Q=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-19 10:01:30\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::RRp4Q8jLFUi9lsIm7GwNAUytnRr4uVGl1pVQ6MPaiUk=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 14:52:00\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"S\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=OXEjZCHJqbqCzLtWSmIA4ea7aVNS6kEhUzywzvqRDpzYLRoW6hwtyADoZMfRth-jwPWqKQqtbB-xwPZ24xHSikMrfxATk0fMaNVBG4LRxxqtNNnTloQphNJE8nc2urDgOoGyjqQiYLU,', 'POST', '2026-02-25 19:52:00'),
(205, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=-zSBbleHEJeVH-Dmy0mWOIfT0Qs12OQOMwK6XXv2v096GjOrMYt_WUVgsDyVQsCWD2s7OLVCoDvCqF3Sk064Vl-VBdQOwRMx_GNrBbG7fkpIC94W0Vq8n-S8B4GsHD9k', 'POST', '2026-02-25 20:03:01'),
(206, NULL, NULL, 'seguridad', 'usuario', 3, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::5AmeZheimJQZYQtfcgd+htYRHVl+P5K0sFeUectB2ue4Is3C3ZnhaRhvNW98XmE2\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Freddy\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$aXVaaTRSTFZCWWpoOElheA$WfA639tX4FghuHN1ELLTeTXlQ2m5ISy3BHPx9HZVyIM\", \"usu_telefono\": \"ENC::/EzkMUFgFy7QybsBa8sruiJTWpxi/nKtcvPtH3yta4Q=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-19 10:01:30\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::RRp4Q8jLFUi9lsIm7GwNAUytnRr4uVGl1pVQ6MPaiUk=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 14:52:00\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"S\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::7gQoIcm46687B3JM4PbDKx/29n3Nk1oVq+aW/Oac8koak9A6BZjzNQuLKMqGoyLh\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$aXVaaTRSTFZCWWpoOElheA$WfA639tX4FghuHN1ELLTeTXlQ2m5ISy3BHPx9HZVyIM\", \"usu_telefono\": \"ENC::xiXvnVUOniUK3fb8a2vmeCUsSUqGSs/HjvMhEqA+e7s=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-19 10:01:30\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::f9EJFxRBXrSCcXKaasleXmQeKuLGLivpRanqORCnr0I=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:03:46\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"S\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=kJcwrYyhsq1180Bi2n3iJk7suGW30xfHnKJCZi2z3AdzNLAnqJBO9RZsaxSU8Hn4_O50bq_29qK-pmlg-sjvsp9aiunoSeLWKoLI6RjG7fkg2Mb_zuThcznuLQWnoTdYMrlEkCJdqMw,', 'POST', '2026-02-25 20:03:46'),
(207, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ibo9ug5sckd3E_beE4qEl6rPbbEEhh9bU0ywDRKVsgO6HT0yhrGlh2swV3dnAQ7cOgfUle1OhBV164MvMf3onhqDv84p1ClD3Pm-MQlQCo_ADOmhmNim_GrT', 'GET', '2026-02-25 20:07:43'),
(208, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=_Nn_OdzR9e-wsre2B4H_4Ku5c2OOyg9PMHKdOdXFziliBd-J-4zb1ofdkNEPg2Wj9_NgeV60Q0OSL9wXy5QAf8IFOSwlByDQxQlcdPf1MUED-MEMO8aNClcw4s_pW-nk', 'POST', '2026-02-25 20:08:24'),
(209, NULL, NULL, 'seguridad', 'usuario', 3, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::7gQoIcm46687B3JM4PbDKx/29n3Nk1oVq+aW/Oac8koak9A6BZjzNQuLKMqGoyLh\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$MlR1SVZLV1Z1azNWSER3Tw$xOkYjhq+u7b0vaH2zNa8ZmN3Ba5iDg2Lo/LBCz+ctAQ\", \"usu_telefono\": \"ENC::xiXvnVUOniUK3fb8a2vmeCUsSUqGSs/HjvMhEqA+e7s=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-19 10:01:30\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::f9EJFxRBXrSCcXKaasleXmQeKuLGLivpRanqORCnr0I=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:17:47\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"S\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::ACGF4jDBLcCj3v8rxSx9NoNpt4DKtWrwfRPZEXEdv7MiGk4pdddVEDDA1bHL/VD/\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$Ui5weXkxdUFLbmpXT0k1cw$gqL9HZ1dY4r0W65sglwTM4RWXeK2NLDrfHkuAT9D7tE\", \"usu_telefono\": \"ENC::oshq6vuoFsR062BupiTckqmEHe+irY2RRSudyn1tsw8=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-19 10:01:30\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::9UdlsH3PaWNcNqwFxioHCjF2544bk1cT9anghnnFF68=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:18:15\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"S\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=dClVHHtNg8CXdOdAwg1vLtr26gIHRs_DrRqD_WKj3Qb_wKc8V_ZFJeuD16lgOAC8R9cn9W3SfQgGPNnGQVHmhKcacbrztH04hapOT-eSb5jcWrpGUEcMI1yJCXGZiS0FbnYgI6_PqQY,', 'POST', '2026-02-25 20:18:15'),
(210, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=JuavAuLMbeXFvLXKEzopqzTdf6Vbiv9_bM2EOxYuOfdR31kRJgMwlFJpLlzRuXHgXL8Vril0v391ht_tBCj5drps324pn_Z47yk71HYt0GnKXi4GlTZOvqGZ', 'GET', '2026-02-25 20:18:34'),
(211, NULL, NULL, 'Core', 'seguridad_usuarios', 3, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=kSiOUCNbFhDkQCAseaqfEY5cWx0qWA-j8DOvWmpddsjEDEd8pMvPOp8hMURwoM2cz0Dt6xn12LJdkJYq2rQD1yvo9LE8WEDOsna4JOREl-tPOByL2z7EJ21M6mzO9le2', 'POST', '2026-02-25 20:18:49'),
(212, NULL, NULL, 'seguridad', 'usuario', 3, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::ACGF4jDBLcCj3v8rxSx9NoNpt4DKtWrwfRPZEXEdv7MiGk4pdddVEDDA1bHL/VD/\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$Ui5weXkxdUFLbmpXT0k1cw$gqL9HZ1dY4r0W65sglwTM4RWXeK2NLDrfHkuAT9D7tE\", \"usu_telefono\": \"ENC::oshq6vuoFsR062BupiTckqmEHe+irY2RRSudyn1tsw8=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:18:49\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::9UdlsH3PaWNcNqwFxioHCjF2544bk1cT9anghnnFF68=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:18:49\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::2sH/ZGuEEjdRpAXzCl5LqeNEVnZU5+Xdlaf4VyxIVFKNHpcma03E0hiFDxcfTd1a\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$Ui5weXkxdUFLbmpXT0k1cw$gqL9HZ1dY4r0W65sglwTM4RWXeK2NLDrfHkuAT9D7tE\", \"usu_telefono\": \"ENC::YeZ4vg+x5gpbvBiq3LdGpQU8x0c+QgDGkQViSY12p7g=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:18:49\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::E2QlaqVBrVP/v08kQlqM7XXW+A3XNzKv2+stncBTULU=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:19:36\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=YRBQYZSgzKM3N62CnVNPj464kbIn3zWTg1E4mYb-cCdYZUa_wb8YxkwsteoyBgJOD9ketWnXWiPCy1SATuBs-KcxMttDyFN93WPqWGsa0thyq2YHlktNe538QjLAQY7wrL9S-VzKUyY,', 'POST', '2026-02-25 20:19:36'),
(213, NULL, NULL, 'seguridad', 'usuario', 3, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::2sH/ZGuEEjdRpAXzCl5LqeNEVnZU5+Xdlaf4VyxIVFKNHpcma03E0hiFDxcfTd1a\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$Ui5weXkxdUFLbmpXT0k1cw$gqL9HZ1dY4r0W65sglwTM4RWXeK2NLDrfHkuAT9D7tE\", \"usu_telefono\": \"ENC::YeZ4vg+x5gpbvBiq3LdGpQU8x0c+QgDGkQViSY12p7g=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:18:49\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::E2QlaqVBrVP/v08kQlqM7XXW+A3XNzKv2+stncBTULU=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:19:36\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::EG3azfPwcnAusnD04mvdjCfi8bBdikaD44IJ5dSbLavZXW39UQaX95S8O/xJeu25\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$Ui5weXkxdUFLbmpXT0k1cw$gqL9HZ1dY4r0W65sglwTM4RWXeK2NLDrfHkuAT9D7tE\", \"usu_telefono\": \"ENC::gFOhxGIaSmtJ879yosJqH8FSw6vFSVLVr9cZjmodyro=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:18:49\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::E+9CHT+BXbePE3zu87cb53ykM8oKX1vIPBbz/zAgTJc=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:19:49\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=7w0b9xpwkAERApPRqKQaCgKy6Crv-p-W61O5sVd6geRbhVgG96tc0LbXoYWiXf2KwEBp8es513FE1hRhkq0ft96m0JGB50i9LZZ2Y9lj39JEtc4oaGUT8UaM6Q1NGWGv0luwBSNQh6M,', 'POST', '2026-02-25 20:19:49'),
(214, NULL, NULL, 'seguridad', 'usuario', 3, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::EG3azfPwcnAusnD04mvdjCfi8bBdikaD44IJ5dSbLavZXW39UQaX95S8O/xJeu25\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$Ui5weXkxdUFLbmpXT0k1cw$gqL9HZ1dY4r0W65sglwTM4RWXeK2NLDrfHkuAT9D7tE\", \"usu_telefono\": \"ENC::gFOhxGIaSmtJ879yosJqH8FSw6vFSVLVr9cZjmodyro=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:18:49\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::E+9CHT+BXbePE3zu87cb53ykM8oKX1vIPBbz/zAgTJc=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:19:49\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::Eztgqxx0owJiZSzc7Y1NGgWptGeDuAbLS3NA8RiaYYLPhH8gJWzCeBFeoC4heZ/q\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 15, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$Ui5weXkxdUFLbmpXT0k1cw$gqL9HZ1dY4r0W65sglwTM4RWXeK2NLDrfHkuAT9D7tE\", \"usu_telefono\": \"ENC::cuFQQpHq8yLHlztSueh0+WlioGJpkQJ2Did2ForSgww=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:18:49\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::1W1yJDLOF3tCDJfFudTxdrgLajwo1KBzcHWdhyI2FM4=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:20:08\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=IhpS_SSNBKxMW2X5KTotc73D3Vl6X5bCUtfrwB3obCGwgZgqXKOOytm3eMlAVAFyKBRBPFhwfqJePt9pTgcOvwbeE6mGDJ_35RzvFNhC1jnf7ks4a6ONWr4FU96jRp6Hpl-t8GauV8M,', 'POST', '2026-02-25 20:20:08'),
(215, 2, 3, 'Core', 'seguridad_usuarios', 3, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=dZtnxxISYiFpvzQ0RXotehqmBicVt69O9CqyW1WIjVnnKGlLcU9t08GMSvclGAmE0_dlZCPRz1RzMD-3oNyKFjqQhR0KVv-CBrgr9oqSayHwfeuHew2o2pH2', 'GET', '2026-02-25 20:21:08'),
(216, NULL, NULL, 'Core', 'seguridad_usuarios', 3, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=MHxihtt215fYo9fASN5AjM_o08fW5jxpLD519Yr8y8qCRkeDVX5vvuKzWUarsqynn2oTrpvr1jGI5HFyr5aUg4kxC8b3uccWvGfHAMXEaC43rFDL6Y_DznlJ98TdSyqm', 'POST', '2026-02-25 20:21:17'),
(217, NULL, NULL, 'seguridad', 'usuario', 3, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::Eztgqxx0owJiZSzc7Y1NGgWptGeDuAbLS3NA8RiaYYLPhH8gJWzCeBFeoC4heZ/q\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 15, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$Ui5weXkxdUFLbmpXT0k1cw$gqL9HZ1dY4r0W65sglwTM4RWXeK2NLDrfHkuAT9D7tE\", \"usu_telefono\": \"ENC::cuFQQpHq8yLHlztSueh0+WlioGJpkQJ2Did2ForSgww=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:21:17\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::1W1yJDLOF3tCDJfFudTxdrgLajwo1KBzcHWdhyI2FM4=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:21:17\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::kOnITXSXqkvByDGXbceFvB5g4eRGxMYpHEbZDVulKpHolQ2qSKrBc3DmY6jKydbo\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 15, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$Ui5weXkxdUFLbmpXT0k1cw$gqL9HZ1dY4r0W65sglwTM4RWXeK2NLDrfHkuAT9D7tE\", \"usu_telefono\": \"ENC::r6Luc0W0ta2K7xT3c1FJ01eEnxbhao804DNTs0gPAT0=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:21:17\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::qnCDBv5/qTSXgpKO8vny6S0pGlZZUx161G8yQVmEytU=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:21:48\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=-Fc8Grkoi3eVCvxX9lLqxgotLSgN66y4xJb0kf1XtVIVvMKWi5GZAqEnL3qhZhJJ5jGO5zneJMkO2GDYTFJ1g0bvkVCb2Hpfx7iDr1kpumPmqRxLlAACQ_DMP8wF6kUVokaDSP5e14E,', 'POST', '2026-02-25 20:21:48'),
(218, NULL, NULL, 'seguridad', 'usuario', 3, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::kOnITXSXqkvByDGXbceFvB5g4eRGxMYpHEbZDVulKpHolQ2qSKrBc3DmY6jKydbo\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 15, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$Ui5weXkxdUFLbmpXT0k1cw$gqL9HZ1dY4r0W65sglwTM4RWXeK2NLDrfHkuAT9D7tE\", \"usu_telefono\": \"ENC::r6Luc0W0ta2K7xT3c1FJ01eEnxbhao804DNTs0gPAT0=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:21:17\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::qnCDBv5/qTSXgpKO8vny6S0pGlZZUx161G8yQVmEytU=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:21:48\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::TCiEcLm6kLfgEXtAPU+E48ITdeQzMjEHdqhjqzAlTJGb/YjPorxZmPL8pn5ZFZVT\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$Ui5weXkxdUFLbmpXT0k1cw$gqL9HZ1dY4r0W65sglwTM4RWXeK2NLDrfHkuAT9D7tE\", \"usu_telefono\": \"ENC::QuO1sAaHcvWzLbcjsmCMxnpynDAaZ+vEbLvmj3lVTBI=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:21:17\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::7RZpMQ4htDxA+h6ngUlb9qy1zaitmrgP4VnSmng6ESc=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:22:04\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=gD3QaXRQc0AgiqTD_vl20oj07O3_tfGaEOQeZSLS-uPHOXX0aTB_1Ant_TTuW7j8wekVNI0ZP-r4U1HCGb-yj1A-4Mzn06XfhDc0um7SMSujlUyU_G3hJyINxh7w4mDfJkROAjW68hA,', 'POST', '2026-02-25 20:22:04');
INSERT INTO `seguridad_auditoria` (`aud_auditoria_id`, `aud_tenant_id`, `aud_usuario_id`, `aud_modulo`, `aud_tabla`, `aud_registro_id`, `aud_operacion`, `aud_valores_anteriores`, `aud_valores_nuevos`, `aud_ip`, `aud_user_agent`, `aud_url`, `aud_metodo`, `aud_fecha_operacion`) VALUES
(219, NULL, NULL, 'seguridad', 'usuario', 3, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::TCiEcLm6kLfgEXtAPU+E48ITdeQzMjEHdqhjqzAlTJGb/YjPorxZmPL8pn5ZFZVT\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 8, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$Ui5weXkxdUFLbmpXT0k1cw$gqL9HZ1dY4r0W65sglwTM4RWXeK2NLDrfHkuAT9D7tE\", \"usu_telefono\": \"ENC::QuO1sAaHcvWzLbcjsmCMxnpynDAaZ+vEbLvmj3lVTBI=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:21:17\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::7RZpMQ4htDxA+h6ngUlb9qy1zaitmrgP4VnSmng6ESc=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:22:04\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::aG7Jk4au40KYzfi95x20TBEOCTM6/H4YHvEeXnXrT0qOSFaMN92H8Jom6nvoEcsc\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$Ui5weXkxdUFLbmpXT0k1cw$gqL9HZ1dY4r0W65sglwTM4RWXeK2NLDrfHkuAT9D7tE\", \"usu_telefono\": \"ENC::O1kxV+oUWhj3YETC2fl1HYNagPPFEvuyaQYmn7lJElo=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:21:17\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::4i0U6033Xu31fjUOOi2ZzeXGZr+5UNQcImt6cx6XtGY=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:23:03\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=Up_WWUgradCRb2Pkv05CLLR3eWBssult9_RvlD8GO6d2aP_B758Qv-JOO7osW2pol9UBAGyNHYH8Y9uAluKy6Ub6ki91gk-ugXXkaSD0rD428zoaL5nRVq5bbt2Dd1xUDc50Im3YXqs,', 'POST', '2026-02-25 20:23:03'),
(220, NULL, NULL, 'seguridad', 'usuario', 1, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::P3TZT+ynj4Y3Jpz7t6BrbVWjBPgfo9gro1LAHNsLbB48vK8wOCvtfs6wEL4mrW5f\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"ENC::n01yWqx96WnAVGhBY8p817XGQFM1VohOwQSam2q32AQ=\", \"usu_nombres\": \"Freddy\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=3$Qm9TcVczOThmQkh4N0hDTg$gdL3FkFtDnw+MnyyE+VIxagpRB019YMCK0w+fOpeydg\", \"usu_telefono\": \"ENC::5HEOIQGBvHxAXygn1RA8ic7eiYBwDfhO1Hc2RmlaKcs=\", \"usu_username\": \"superadmin\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 1, \"usu_codigo_2fa\": \"798279\", \"usu_email_hash\": \"c0e957495bb43b36f0ff7dea96030260\", \"usu_usuario_id\": 1, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": \"d46c1a54c78b28260bf588612ead286bf1e0d7218452375938c70b356bcff026\", \"usu_ultimo_login\": \"2026-02-25 15:08:24\", \"usu_fecha_registro\": \"2026-01-24 19:35:10\", \"usu_identificacion\": \"ENC::XZ6LWkPwRRv69pixY0UccejUqpP6kvofJL40uL7P7yc=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": \"2027-01-01\", \"usu_codigo_2fa_expira\": \"2026-01-24 20:21:48\", \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:08:24\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": \"2026-02-24 17:56:18\", \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::N8M5PUSsUol/X8uKN/PMBHj5PJ5em+R7qqqImEVO3qiGtF07Q21IhWHHlC/S6Qsa\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"ENC::tYpnIfZI5bK0QOhffMikZeAxzkzK4bcvtvdEChSNhxA=\", \"usu_nombres\": \"Freddy\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=3$Qm9TcVczOThmQkh4N0hDTg$gdL3FkFtDnw+MnyyE+VIxagpRB019YMCK0w+fOpeydg\", \"usu_telefono\": \"ENC::yeNJ1lTl5ufG6m0IduQTxMBubjp17a3yFRRgPbnBpdk=\", \"usu_username\": \"superadmin\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 1, \"usu_codigo_2fa\": \"798279\", \"usu_email_hash\": \"c0e957495bb43b36f0ff7dea96030260\", \"usu_usuario_id\": 1, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": \"d46c1a54c78b28260bf588612ead286bf1e0d7218452375938c70b356bcff026\", \"usu_ultimo_login\": \"2026-02-25 15:08:24\", \"usu_fecha_registro\": \"2026-01-24 19:35:10\", \"usu_identificacion\": \"ENC::1IeSjwyCnY1tTslc8FJ7WUme8b1HuxmAhl0cjF78U/c=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": \"2027-01-01\", \"usu_codigo_2fa_expira\": \"2026-01-24 20:21:48\", \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 15:24:01\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": \"2026-02-24 17:56:18\", \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=tgABOBwFPDAyV1OeefbmJpEifmWK6frW26Nid3v5LY-wuAJt385t6OA9GwVTDR0tm0CiLfc8uX07yniVy8pD3idMj_3jG-WW0thm2a89pbbk3Bzle9lRtmAiArFHNm8w7zpLsVcGtpw,', 'POST', '2026-02-25 20:24:01'),
(221, 2, 3, 'Core', 'seguridad_usuarios', 3, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=0AIyzJ2wDSKeCxcW1wef6cT4Vkee_ZVLzF2ANC2XFuhJgyuJqjqi1Kja-ASNa5BiimDW92PsulgoEOnOk3MC8R8ePepBUPzP4P98xLZXK91OkIDP7xgbUkv_', 'GET', '2026-02-25 20:28:15'),
(222, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=2SiwOeigc43H8obWQnju0DbDg7KicRrCqbNRvTPHmxL8Cdkmikd2-CWh4Ww-BnY_i3j7WD-OjlbFEQh8XFQtYkXzy1AbVBQ031KSWAw7Iv3ezZq7rKb7s3uRHcaM3rAq', 'POST', '2026-02-26 04:57:57'),
(223, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=F7XPoQ9lIcB-44TotgMKAHAv1KKGuhe-WZsCim1Vi-7RRxGQazXkIRP4H952EFVVfpi1NcNLG9CivjdqKH8nWSH07MaxsGpYGBg0xeGIwu-PXPLCmIVb85N7', 'GET', '2026-02-26 05:01:03'),
(224, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=0aB2_qBSU8iNhmQQJXO7Ua4Dn66eoKr5HJoLHcsLvzz7_l64F7ZoLN8MxtNXd1Hxyq9NOggIS8zg9-WZa6L7JfnUp8kyvXFmIFnCVTofVAFtGKuuNU00PvPAkfzPu-5F', 'POST', '2026-02-26 05:30:24'),
(225, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=cBl8dZeBMhUugKKiG6FlnoVsUPl6isEm746AXHAKsm8DJwIRLRJgNImJCB86J_9-sjwG3FPApcHZJioVoNrKqzhXb85ZTKFSJEwCDagxli5AMpbjS9z_BWHz', 'GET', '2026-02-26 05:30:48'),
(226, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=KKBT2tQ54LtaA1ARVHhnKbIoR27QEUEBtwWoql-viVfhM6gM-4eQQT-QQO04QZ2mhz4Ir3pakGxSnaVB2zuA1-496cDPs6e6HKGKtZLBIPy0Gdix__TWjemL-X_9dEQ6', 'POST', '2026-02-26 15:20:35'),
(227, NULL, NULL, 'seguridad', 'seguridad_modulos', 30, 'editar_modulo', '{\"mod_id\": 30, \"mod_icono\": \"fas fa-apple-alt\", \"mod_orden\": 0, \"mod_activo\": 1, \"mod_codigo\": \"NUTRICION\", \"mod_nombre\": \"Planes Nutricionales\", \"mod_created_at\": \"2026-02-07 17:50:38\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-02-07 17:50:38\", \"mod_color_fondo\": \"#fd7e14\", \"mod_descripcion\": \"Seguimiento nutricional de deportistas\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"nutricion\", \"mod_url_externa\": \"/nutricion/\", \"mod_ruta_controller\": \"dashboard\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-apple-alt\", \"mod_orden\": 0, \"mod_activo\": 0, \"mod_codigo\": \"NUTRICION\", \"mod_nombre\": \"Planes Nutricionales\", \"mod_es_externo\": 0, \"mod_color_fondo\": \"#fd7e14\", \"mod_descripcion\": \"Seguimiento nutricional de deportistas\", \"mod_url_externa\": \"/nutricion/\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=m271p1YSRo5zYlIqcXl7vv3RfElzGutUnsi1VbuvaXQhj9SnCywFFFxAM25HpI7cx3bt9H8AQ4PPhTKy_ruQpGAlWTlbGXt_E0UsCi7pHJxFYAOtmPPHq6V4bZ4WKf1DLw,,', 'POST', '2026-02-26 15:26:25'),
(228, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=__hq_vBrkizxgO92_TSEt1iYnDDezbx3FFt2i9ExFik0q-H9WrjMIG0425Fh3ISNatYHri8c7vzmB_SuyoTHU3fI-k4FgK2lgepm-pV0F2Ip6XF4Zof_zr0bxGO_49Xk', 'POST', '2026-02-26 16:10:50'),
(229, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=VlBtUKyvZ8kyHdYnL3O9baZTiQuW_OOyuHIlc3d2xYIXkYJI0UTGN8CEXYA3eEwZog_L49Guu1u60JIdkzW1ho7fO-9OWryWhdTj15rg07Z-tQIl1-wg4jsvXWg6hd9l', 'POST', '2026-02-26 22:28:35'),
(230, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=KezXAlZneun1_4S9EKS2pIy3CGXcqGb9gKxQ4eyMkwXglUeJtuCIeG94FVrWAhMEhEbhXEen1sHQLnWHrenhM5EvyRLDpRdVYUYOJYCLMFlt_KCpxZ_izcjcrBJlqr4a', 'POST', '2026-02-27 00:46:22'),
(231, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=9HQRUQXnJNjBIuLlHuk1mY0ygAtcgWvLWBYn9NOLdEl5St1N8aVt-l-p0_g3iuFWK3m0yymB7EtusCWyAqeGWfp0SuNerntT1qZhtIajdOn2hwB0yVMM5GXDk8YoqW1Y', 'POST', '2026-02-27 03:28:05'),
(232, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=C2SwMankLcGDQYofa686G0BF2_0KXmpR3vBJ_-tU7j0gxXUa-5D7W1R2Y3yeAkb5bJsxsr3wQ2DUerLXTjkpyk9lzz7AyNriwYrTfa8-FjrwuR7FG2qfEcFlRNXfhLtG', 'POST', '2026-02-27 04:21:55'),
(233, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ToJDTfPIbMQhN1v1d8Kspz5rzlv0m0iFs2vhMI9qmxOJbPOlshKyD8BkfcwhzOIJjqKRWg0nEoNdGWHlfL5lSiaa1w5xgD7vlG3N-XoYjRQ9_BexJazyjXNdZc66wJ1J', 'POST', '2026-02-27 13:19:52'),
(234, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=vmB27c0m2aRmSvczMG285yxu0NWeugWBq5dxrGcMjp9x0W8CRrhGhV-StgbNs1nawQvFXbcG_SOcE39xl6S8HNoUo5tpHJY8rhVbhboi_HayCsWCJVDz7PDtRyaRfTnv', 'POST', '2026-02-27 14:35:03'),
(235, 1, 1, 'Reservas', 'reserva_pagos', 3, 'PAGO_REGISTRADO', 'null', '{\"monto\": 6, \"forma_pago\": \"EFECTIVO\", \"reserva_id\": 3, \"estado_pago\": \"PAGADO\", \"monto_efectivo\": 6, \"monto_monedero\": 0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=_AJV8ZMYKGdoojzAVcJy6bMkgR5djKc7StnRB0gdgsUHf7uYXE98zS2s7KxT8m_eCr6yIUedg03Xli-0uyjbLqVaXBCGqE23InkwGLGD-h5DN4e028no7LOHJgKjBhLMhX9RmQ,,', 'POST', '2026-02-27 15:34:06'),
(236, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=wUVBuGUXvP1ziE_SlscbflMggM4b9f2Ok0KajtCtcHXQ9jR8tOwFvEGnKH8FgKD8fXuWdEJ1jDXuxtg4h0bwmqtqwHt9dozpDmWKqCOccX719lkaDBdihz_WVBIX_q2h', 'POST', '2026-02-27 17:34:58'),
(237, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=4XXhjfGJbxulUuPJpMWC_YfFvz_ClDjR0LHs1UzQjgXG47Mc_bgEJuwMlboQwdoxZuQ4ZuIAOlIKyemsbCYkq8PpPjday9hghfPWyl1U-37S4PIMmiW91Rt9MijDb2nr', 'POST', '2026-03-02 21:27:37'),
(238, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=A_dFf0mKJvX6fehMnZ4M7YcdhRY1n4CHJsayKRq960Oc5HiN4lirSLzDdmyA19PajNBlvTMzSXRloprmdZVvkZzA3jII170gv7AoI3vKeElj7zAeCpT6Ux1XjrM_zglp', 'POST', '2026-03-02 21:28:29'),
(239, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=4bpIRP1itHKWh4uzms_NBv6mJYWWlrFiIJCV_v7Kk6icb4Vr-Si7qk0thkwZ1fT2IrE-UuKHuTJahaF0zVP15jtdiROOlDndMrWho7Xq90RIxqcv4oCyOpPNHJnSwKRn', 'POST', '2026-03-03 17:07:55'),
(240, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=hVDQqfsxr7hAaWUeB7QeX1SWTDhcKGcNNbl0JGcHBkfX0FwDlb6_blgT0LvlfQLTe6anuOGMMrI5DtY3EkhROgsaliOKq-LHO0Dqlbj_YVSesAF1mf2KhRhPmtsONOVr', 'POST', '2026-03-03 19:31:52'),
(241, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=xdUUPV3gzX5muLkj5feXQxhRIm7JdmHpL5wPWJqxqxbeDQJaRMPAjOAPGkqcOzaCfqeQZIiB_chyvkWL61-ZKEE2n46dsyotTC71dO3OgfmwrAiUX5YWHzckCD7JKPn5', 'POST', '2026-03-03 21:06:40'),
(242, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=Doir_HshHIcOoUqFZp0liKQBRShZh1oILzqQeHHM5e3y_d0iLpe2L9Y6snnOxPijURiariGa-_7Ghhj6Znhr84WQmP4eTfY5WPE-er5Gjg3LHPAHPRf4F0Zv9QRX9j9O', 'POST', '2026-03-04 14:14:01'),
(243, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=yo1VGQDZmIymLo1UvW1GQQOHMeGdnKohKOf5Dj4jnvb6y_h1YRRcZ5XlAe4xgFCDbnEQkuKjVXRDnRVEUdFDNTDIw5Cs5qyFvu7l5jU4yHUj4e_KT-2EGQGihwVvGDKj', 'POST', '2026-03-04 15:25:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_configuracion_sistema`
--

DROP TABLE IF EXISTS `seguridad_configuracion_sistema`;
CREATE TABLE IF NOT EXISTS `seguridad_configuracion_sistema` (
  `sis_config_id` int NOT NULL AUTO_INCREMENT,
  `sis_tenant_id` int DEFAULT NULL,
  `sis_clave` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sis_valor` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sis_tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'STRING',
  `sis_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sis_es_editable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `sis_requiere_reinicio` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
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
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `seguridad_log_accesos`
--

INSERT INTO `seguridad_log_accesos` (`acc_log_id`, `acc_usuario_id`, `acc_tenant_id`, `acc_fecha`, `acc_tipo`, `acc_ip`, `acc_user_agent`, `acc_exito`, `acc_mensaje`) VALUES
(1, 1, 1, '2026-01-29 17:16:37', 'LOGIN_OK', '127.0.0.1', 'Mozilla/5.0', 'S', 'Acceso correcto'),
(2, 1, 1, '2026-01-29 17:16:37', 'LOGIN_FAILED', '127.0.0.1', 'Mozilla/5.0', 'N', 'Contraseña incorrecta'),
(3, 1, 1, '2026-01-29 17:16:37', 'LOGIN_OK', '127.0.0.1', 'Mozilla/5.0', 'S', 'Acceso correcto'),
(4, 1, 1, '2026-01-29 17:16:37', 'LOGIN_FAILED', '127.0.0.1', 'Mozilla/5.0', 'N', 'Usuario bloqueado'),
(5, 1, 1, '2026-01-29 17:16:37', 'LOGOUT', '127.0.0.1', 'Mozilla/5.0', 'S', 'Cierre de sesión'),
(6, 1, 1, '2026-01-29 17:16:37', 'LOGIN_OK', '127.0.0.1', 'Mozilla/5.0', 'S', 'Acceso correcto'),
(7, NULL, NULL, '2026-02-19 15:09:03', 'LOGIN_FAILED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'N', 'Usuario: admin - Usuario o email no encontrado o inactivo'),
(8, 1, 1, '2026-02-19 15:13:47', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(9, 1, 1, '2026-02-24 14:50:10', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(10, 1, 1, '2026-02-25 12:18:35', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(11, 1, 1, '2026-02-25 14:20:13', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(12, 1, NULL, '2026-02-25 15:02:37', 'LOGIN_FAILED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'N', 'Usuario: superadmin - Contraseña incorrecta'),
(13, 1, 1, '2026-02-25 15:03:01', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(14, 1, 1, '2026-02-25 15:07:43', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(15, 1, 1, '2026-02-25 15:08:24', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(16, 1, 1, '2026-02-25 15:18:34', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(17, 3, 2, '2026-02-25 15:18:49', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(18, 3, 2, '2026-02-25 15:21:08', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(19, 3, 2, '2026-02-25 15:28:15', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(20, 1, 1, '2026-02-25 23:57:57', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(21, 1, 1, '2026-02-26 00:01:03', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(22, 1, 1, '2026-02-26 00:30:24', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(23, 1, 1, '2026-02-26 00:30:48', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(24, NULL, NULL, '2026-02-26 00:30:52', 'LOGIN_FAILED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'N', 'Usuario: admin - Usuario o email no encontrado o inactivo'),
(25, 1, 1, '2026-02-26 10:20:35', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(26, 1, 1, '2026-02-26 11:10:50', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(27, 1, 1, '2026-02-26 17:28:35', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(28, 1, 1, '2026-02-26 19:46:22', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(29, 1, 1, '2026-02-26 22:28:05', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(30, 1, 1, '2026-02-26 23:21:55', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(31, 1, 1, '2026-02-27 08:19:52', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(32, 1, 1, '2026-02-27 09:35:03', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(33, 1, 1, '2026-02-27 12:34:58', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(34, 1, 1, '2026-03-02 16:27:37', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(35, 1, 1, '2026-03-02 16:28:29', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(36, 1, 1, '2026-03-03 12:07:55', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(37, 1, 1, '2026-03-03 14:31:52', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(38, 1, 1, '2026-03-03 16:06:40', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(39, 1, 1, '2026-03-04 09:14:01', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(40, 1, 1, '2026-03-04 10:25:29', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_menu`
--

DROP TABLE IF EXISTS `seguridad_menu`;
CREATE TABLE IF NOT EXISTS `seguridad_menu` (
  `men_id` int NOT NULL AUTO_INCREMENT COMMENT 'PK autoincremental',
  `men_modulo_id` int NOT NULL COMMENT 'FK a seguridad_modulos.mod_id',
  `men_padre_id` int DEFAULT NULL COMMENT 'FK recursiva a seguridad_menu.men_id',
  `men_tipo` enum('HEADER','ITEM','SUBMENU') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ITEM' COMMENT 'HEADER=separador, ITEM=enlace, SUBMENU=sub-enlace',
  `men_label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Texto visible',
  `men_icono` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Clase FontAwesome',
  `men_ruta_modulo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Módulo destino',
  `men_ruta_controller` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Controlador destino',
  `men_ruta_action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Acción destino',
  `men_url_custom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL personalizada',
  `men_badge` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Texto del badge',
  `men_badge_tipo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo Bootstrap del badge',
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
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Menús laterales dinámicos por aplicativo/módulo';

--
-- Volcado de datos para la tabla `seguridad_menu`
--

INSERT INTO `seguridad_menu` (`men_id`, `men_modulo_id`, `men_padre_id`, `men_tipo`, `men_label`, `men_icono`, `men_ruta_modulo`, `men_ruta_controller`, `men_ruta_action`, `men_url_custom`, `men_badge`, `men_badge_tipo`, `men_orden`, `men_activo`, `men_visible_rol`, `men_tenant_id`, `men_created_at`, `men_updated_at`) VALUES
(1, 1, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:00', '2026-02-08 02:21:00'),
(2, 1, 1, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'instalaciones', 'dashboard', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:21:00', '2026-02-08 02:21:00'),
(3, 1, 1, 'ITEM', 'Canchas', 'fas fa-futbol', 'instalaciones', 'cancha', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-08 02:21:00', '2026-02-08 02:21:00'),
(4, 1, 1, 'ITEM', 'Mantenimientos', 'fas fa-tools', 'instalaciones', 'mantenimiento', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-08 02:21:00', '2026-02-08 02:21:00'),
(5, 1, 1, 'ITEM', 'Reservas', 'fas fa-calendar-check', 'reservas', 'reserva', 'index', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-08 02:21:00', '2026-02-08 02:21:00'),
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
(95, 22, 94, 'ITEM', 'Sistemas Core', 'fas fa-puzzle-piece', NULL, NULL, 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 04:28:48'),
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
(109, 22, 108, 'ITEM', 'Sistema', 'fas fa-cogs', 'seguridad', 'modulo', 'configuracion', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-08 02:22:40', '2026-02-08 02:54:07'),
(110, 1, 1, 'ITEM', 'Calendario', 'fas fa-calendar-alt', 'instalaciones', 'calendario', 'index', NULL, NULL, NULL, 5, 1, NULL, NULL, '2026-02-08 05:47:16', '2026-02-08 05:47:16'),
(111, 1, 1, 'ITEM', 'Monedero / Abonos', 'fas fa-wallet', 'reservas', 'abon', 'index', NULL, NULL, NULL, 6, 1, NULL, NULL, '2026-02-08 05:47:16', '2026-02-08 05:47:16'),
(112, 1, 1, 'ITEM', 'Paquetes de Horas', 'fas fa-box-open', 'reservas', 'abon', 'paquetes', NULL, NULL, NULL, 7, 1, NULL, NULL, '2026-02-08 05:47:16', '2026-02-08 05:47:16'),
(113, 1, 1, 'ITEM', 'Pagos', 'fas fa-cash-register', 'reservas', 'pago', 'index', NULL, NULL, NULL, 10, 1, NULL, NULL, '2026-02-08 06:16:14', '2026-02-08 06:16:14'),
(114, 1, 1, 'ITEM', 'Entradas', 'fas fa-ticket-alt', 'instalaciones', 'entrada', 'index', NULL, NULL, NULL, 11, 1, NULL, NULL, '2026-02-08 06:16:14', '2026-02-08 06:16:14'),
(115, 1, 1, 'ITEM', 'Tarifas de Entrada', 'fas fa-tags', 'instalaciones', 'entrada', 'tarifas', NULL, NULL, NULL, 12, 1, NULL, NULL, '2026-02-08 06:16:14', '2026-02-08 06:16:14'),
(116, 1, 1, 'ITEM', 'Control de Acceso', 'fas fa-qrcode', 'instalaciones', 'entrada', 'escanear', NULL, NULL, NULL, 13, 1, NULL, NULL, '2026-02-08 06:16:14', '2026-02-08 06:16:14'),
(117, 1, NULL, 'ITEM', 'Reportes Arena', 'fas fa-chart-line', 'instalaciones', 'reporteArena', 'index', NULL, NULL, NULL, 14, 1, NULL, NULL, '2026-02-08 06:37:56', '2026-02-08 08:00:55'),
(119, 1, NULL, 'ITEM', 'Clientes', 'fas fa-users', 'clientes', 'cliente', 'index', NULL, NULL, NULL, 15, 1, NULL, NULL, '2026-02-08 08:00:12', '2026-02-08 08:00:12'),
(120, 21, NULL, 'HEADER', 'Caja', NULL, 'store', NULL, NULL, NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-09 14:03:03', '2026-02-09 14:03:03'),
(121, 21, NULL, 'HEADER', 'Ventas', NULL, 'store', NULL, NULL, NULL, NULL, NULL, 5, 1, NULL, NULL, '2026-02-09 14:03:03', '2026-02-09 14:03:03'),
(122, 21, NULL, 'HEADER', 'Clientes', NULL, 'store', NULL, NULL, NULL, NULL, NULL, 6, 1, NULL, NULL, '2026-02-09 14:03:03', '2026-02-09 14:03:03'),
(123, 21, NULL, 'HEADER', 'Reportes', NULL, 'store', NULL, NULL, NULL, NULL, NULL, 7, 1, NULL, NULL, '2026-02-09 14:03:03', '2026-02-09 14:03:03'),
(124, 21, 120, 'ITEM', 'Apertura/Cierre', 'fas fa-cash-register', 'store', 'caja', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(125, 21, 120, 'ITEM', 'Turnos', 'fas fa-clock', 'store', 'caja', 'turnos', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(126, 21, 120, 'ITEM', 'Movimientos', 'fas fa-exchange-alt', 'store', 'caja', 'movimientos', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(127, 21, 120, 'ITEM', 'Arqueo', 'fas fa-calculator', 'store', 'caja', 'arqueo', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(128, 21, 121, 'ITEM', 'Historial Ventas', 'fas fa-receipt', 'store', 'venta', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(129, 21, 121, 'ITEM', 'Cotizaciones', 'fas fa-file-invoice', 'store', 'cotizacion', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(130, 21, 121, 'ITEM', 'Devoluciones', 'fas fa-undo-alt', 'store', 'devolucion', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(131, 21, 121, 'ITEM', 'Descuentos', 'fas fa-percentage', 'store', 'descuento', 'index', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(132, 21, 122, 'ITEM', 'Directorio', 'fas fa-address-book', 'store', 'cliente', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(133, 21, 122, 'ITEM', 'Fidelización', 'fas fa-star', 'store', 'cliente', 'fidelizacion', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(134, 21, 123, 'ITEM', 'Ventas', 'fas fa-chart-bar', 'store', 'reporte', 'ventas', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(135, 21, 123, 'ITEM', 'Inventario', 'fas fa-chart-pie', 'store', 'reporte', 'inventario', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(136, 21, 123, 'ITEM', 'Caja', 'fas fa-chart-line', 'store', 'reporte', 'caja', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(137, 21, 123, 'ITEM', 'Clientes', 'fas fa-users', 'store', 'reporte', 'clientes', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(138, 21, 74, 'ITEM', 'Proveedores', 'fas fa-truck', 'store', 'proveedor', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(139, 21, 74, 'ITEM', 'Órdenes de Compra', 'fas fa-file-alt', 'store', 'orden_compra', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-09 14:03:20', '2026-02-09 14:03:20'),
(140, 8, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(141, 8, 140, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'natacion', 'dashboard', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(142, 8, 140, 'ITEM', 'Horario Semanal', 'fas fa-calendar-alt', 'natacion', 'horario', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(143, 8, NULL, 'HEADER', 'Gestión Académica', NULL, NULL, NULL, 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-03-04 15:52:15'),
(144, 8, 143, 'ITEM', 'Alumnos', 'fas fa-user-graduate', 'natacion', 'alumno', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(145, 8, 143, 'ITEM', 'Inscripciones', 'fas fa-clipboard-list', 'natacion', 'inscripcion', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(146, 8, 143, 'ITEM', 'Asistencia', 'fas fa-check-double', 'natacion', 'asistencia', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(147, 8, 143, 'ITEM', 'Evaluaciones', 'fas fa-star-half-alt', 'natacion', 'evaluacion', 'index', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(148, 8, 143, 'ITEM', 'Niveles', 'fas fa-layer-group', 'natacion', 'nivel', 'index', NULL, NULL, NULL, 5, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(149, 8, NULL, 'HEADER', 'Infraestructura', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(150, 8, 149, 'ITEM', 'Piscinas', 'fas fa-swimming-pool', 'natacion', 'piscina', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(151, 8, 149, 'ITEM', 'Instructores', 'fas fa-chalkboard-teacher', 'natacion', 'instructor', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(152, 8, 149, 'ITEM', 'Grupos/Clases', 'fas fa-users-class', 'natacion', 'grupo', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(153, 8, 149, 'ITEM', 'Períodos', 'fas fa-calendar-check', 'natacion', 'periodo', 'index', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-03-04 15:51:57'),
(154, 8, NULL, 'HEADER', 'Financiero', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(155, 8, 154, 'ITEM', 'Pagos', 'fas fa-money-bill-wave', 'natacion', 'pago', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(156, 8, 154, 'ITEM', 'Reportes', 'fas fa-chart-bar', 'natacion', 'reporte', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(157, 8, NULL, 'HEADER', 'Configuración', NULL, NULL, NULL, 'index', NULL, NULL, NULL, 5, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-03-04 15:50:48'),
(158, 8, 157, 'ITEM', 'Campos de Ficha', 'fas fa-sliders-h', 'natacion', 'campoficha', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-02-09 16:44:23'),
(159, 8, 157, 'ITEM', 'Configuración', 'fas fa-cog', 'natacion', 'configuracion', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 16:44:23', '2026-03-04 15:51:26'),
(160, 8, 149, 'ITEM', 'Sedes', 'fas fa-building', 'natacion', 'sede', 'index', NULL, NULL, NULL, 5, 1, NULL, NULL, '2026-02-09 17:42:56', '2026-02-09 17:42:56'),
(161, 8, 154, 'ITEM', 'Egresos', 'fas fa-file-invoice-dollar', 'natacion', 'egreso', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-09 17:42:56', '2026-02-09 17:42:56'),
(162, 15, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(163, 15, 162, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'futbol', 'dashboard', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(164, 15, 162, 'ITEM', 'Horario Semanal', 'fas fa-calendar-alt', 'futbol', 'horario', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(165, 15, NULL, 'HEADER', 'Gestión Académica', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 21:24:55'),
(166, 15, 165, 'ITEM', 'Jugadores', 'fas fa-user-graduate', 'futbol', 'alumno', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(167, 15, 165, 'ITEM', 'Inscripciones', 'fas fa-clipboard-list', 'futbol', 'inscripcion', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(168, 15, 165, 'ITEM', 'Asistencia', 'fas fa-check-double', 'futbol', 'asistencia', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(169, 15, 165, 'ITEM', 'Evaluaciones', 'fas fa-star-half-alt', 'futbol', 'evaluacion', 'index', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(170, 15, 165, 'ITEM', 'Categorías', 'fas fa-layer-group', 'futbol', 'categoria', 'index', NULL, NULL, NULL, 5, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 21:24:55'),
(171, 15, NULL, 'HEADER', 'Infraestructura', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(172, 15, 171, 'ITEM', 'Canchas', 'fas fa-futbol', 'futbol', 'cancha', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(173, 15, 171, 'ITEM', 'Entrenadores', 'fas fa-chalkboard-teacher', 'futbol', 'entrenador', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(174, 15, 171, 'ITEM', 'Grupos/Equipos', 'fas fa-users', 'futbol', 'grupo', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(175, 15, 171, 'ITEM', 'Períodos', 'fas fa-calendar-check', 'futbol', 'periodo', 'index', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 21:24:55'),
(176, 15, NULL, 'HEADER', 'Competencias', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(177, 15, 176, 'ITEM', 'Torneos', 'fas fa-trophy', 'futbol', 'torneo', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(178, 15, 176, 'ITEM', 'Convocatorias', 'fas fa-bullhorn', 'futbol', 'torneo', 'convocatoria', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 22:32:58'),
(179, 15, NULL, 'HEADER', 'Financiero', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(180, 15, 179, 'ITEM', 'Pagos', 'fas fa-money-bill-wave', 'futbol', 'pago', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(181, 15, 179, 'ITEM', 'Becas/Descuentos', 'fas fa-gift', 'futbol', 'beca', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(182, 15, 179, 'ITEM', 'Control de Mora', 'fas fa-exclamation-triangle', 'futbol', 'mora', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(183, 15, 179, 'ITEM', 'Comprobantes', 'fas fa-file-invoice-dollar', 'futbol', 'comprobante', 'index', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(184, 15, 179, 'ITEM', 'Egresos', 'fas fa-receipt', 'futbol', 'egreso', 'index', NULL, NULL, NULL, 5, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(185, 15, 179, 'ITEM', 'Reportes', 'fas fa-chart-bar', 'futbol', 'reporte', 'index', NULL, NULL, NULL, 6, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(186, 15, NULL, 'HEADER', 'Comunicaciones', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(187, 15, 186, 'ITEM', 'Notificaciones', 'fas fa-bell', 'futbol', 'notificacion', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(188, 15, NULL, 'HEADER', 'Configuración', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 21:24:55'),
(189, 15, 188, 'ITEM', 'Sedes', 'fas fa-building', 'futbol', 'sede', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(190, 15, 188, 'ITEM', 'Campos de Ficha', 'fas fa-sliders-h', 'futbol', 'campoficha', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(191, 15, 188, 'ITEM', 'Configuración', 'fas fa-cog', 'futbol', 'configuracion', 'index', NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-02-09 21:24:55'),
(192, 22, 104, 'ITEM', 'IPs Bloqueadas', 'fas fa-ban', 'seguridad', 'auditoria', 'ipsBloqueadas', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-19 20:30:15', '2026-02-19 20:30:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_menu_config`
--

DROP TABLE IF EXISTS `seguridad_menu_config`;
CREATE TABLE IF NOT EXISTS `seguridad_menu_config` (
  `con_id` int NOT NULL AUTO_INCREMENT,
  `con_modulo_codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `con_opcion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `con_icono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `con_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `con_permiso_requerido` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `mod_codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código único del módulo',
  `mod_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mod_descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mod_icono` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fas fa-cube' COMMENT 'Clase Font Awesome',
  `mod_color_fondo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#3B82F6' COMMENT 'Color del icono en hex',
  `mod_orden` int DEFAULT '0' COMMENT 'Orden de visualización',
  `mod_ruta_modulo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'module para el router',
  `mod_ruta_controller` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'controller para el router',
  `mod_ruta_action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'index' COMMENT 'action para el router',
  `mod_es_externo` tinyint(1) DEFAULT '0' COMMENT '1=Sistema externo con su propia BD',
  `mod_url_externa` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL si es sistema externo',
  `mod_base_datos_externa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
(1, 'ARENA', 'DigiSports Arena', 'Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.', 'fas fa-building', '#FF7E70', 1, 'instalaciones', 'dashboard', 'index', 0, NULL, NULL, 1, 1, '2026-01-26 05:37:36', '2026-02-08 04:36:56'),
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
(22, 'SEGURIDAD', 'Seguridad', '2FA, encriptación AES-256, auditoría completa y protección avanzada.', 'fas fa-shield-alt', '#EF4444', 98, 'seguridad', 'dashboard', 'index', 0, NULL, NULL, 1, 1, '2026-02-02 20:52:19', '2026-02-19 13:53:05'),
(27, 'TENANTS', 'Tenants', 'Gestión de empresas/tenants', 'fas fa-building', '#10B981', 3, 'tenants', 'dashboard', 'index', 0, '/instalaciones/', NULL, 1, 0, '2026-02-07 22:50:38', '2026-02-08 02:34:29'),
(30, 'NUTRICION', 'Planes Nutricionales', 'Seguimiento nutricional de deportistas', 'fas fa-apple-alt', '#fd7e14', 0, 'nutricion', 'dashboard', 'index', 0, '/nutricion/', NULL, 1, 0, '2026-02-07 22:50:38', '2026-02-26 15:26:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_modulos_sistema_deprecated`
--

DROP TABLE IF EXISTS `seguridad_modulos_sistema_deprecated`;
CREATE TABLE IF NOT EXISTS `seguridad_modulos_sistema_deprecated` (
  `sis_modulo_id` int NOT NULL AUTO_INCREMENT,
  `sis_codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sis_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sis_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sis_icono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fa-puzzle-piece',
  `sis_color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#007bff',
  `sis_url_base` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL si es sistema externo',
  `sis_es_externo` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'S si apunta a otro sistema',
  `sis_base_datos_externa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre de BD si es sistema legacy',
  `sis_orden_visualizacion` int DEFAULT '0',
  `sis_requiere_suscripcion` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `sis_estado` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'A',
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
  `not_tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `not_titulo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `not_mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `not_url_accion` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `not_icono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `not_color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `not_leida` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
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
  `log_destinatario_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_tipo_notificacion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_asunto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_enviado` tinyint(1) DEFAULT '0',
  `log_error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  `sus_codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sus_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sus_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sus_precio_mensual` decimal(10,2) NOT NULL,
  `sus_precio_anual` decimal(10,2) DEFAULT NULL,
  `sus_descuento_anual` decimal(5,2) DEFAULT '0.00',
  `sus_usuarios_incluidos` int DEFAULT '5',
  `sus_sedes_incluidas` int DEFAULT '1',
  `sus_almacenamiento_gb` int DEFAULT '10',
  `sus_modulos_incluidos` json DEFAULT NULL,
  `sus_caracteristicas` json DEFAULT NULL,
  `sus_es_destacado` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `sus_es_personalizado` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `sus_color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#007bff',
  `sus_orden_visualizacion` int DEFAULT '0',
  `sus_estado` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `sus_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sus_plan_id`),
  UNIQUE KEY `codigo` (`sus_codigo`),
  KEY `idx_codigo` (`sus_codigo`),
  KEY `idx_estado` (`sus_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_planes_suscripcion`
--

INSERT INTO `seguridad_planes_suscripcion` (`sus_plan_id`, `sus_codigo`, `sus_nombre`, `sus_descripcion`, `sus_precio_mensual`, `sus_precio_anual`, `sus_descuento_anual`, `sus_usuarios_incluidos`, `sus_sedes_incluidas`, `sus_almacenamiento_gb`, `sus_modulos_incluidos`, `sus_caracteristicas`, `sus_es_destacado`, `sus_es_personalizado`, `sus_color`, `sus_orden_visualizacion`, `sus_estado`, `sus_fecha_registro`) VALUES
(1, 'BASICO', 'Plan Basico', 'Ideal para pequenos centros deportivos', 49.99, 539.89, 0.00, 3, 1, 5, '[\"CORE\", \"INSTALACIONES\"]', NULL, 'N', 'N', '#007bff', 0, 'A', '2026-01-25 00:35:09'),
(2, 'PROFESIONAL', 'Profesional', 'Perfecto para centros en crecimiento', 99.99, 1079.89, 0.00, 10, 3, 25, '[]', '[\"hola\"]', 'S', 'N', '#007bff', 0, 'A', '2026-01-25 00:35:09'),
(3, 'EMPRESARIAL', 'Plan Empresarial', 'Para cadenas y complejos deportivos', 199.99, 2159.89, 0.00, 50, 10, 100, '[\"CORE\", \"INSTALACIONES\", \"ESCUELAS\", \"TORNEOS\", \"INVENTARIO\", \"NUTRICION\", \"REPORTES\"]', NULL, 'N', 'N', '#007bff', 0, 'A', '2026-01-25 00:35:09'),
(4, 'PERSONALIZADO', 'Plan Personalizado', 'Solucion a medida segun tus necesidades', 28.00, 100.00, 0.00, 4, 20, 500, '[]', '[\"Soporte\", \"Soporte\", \"Soporte\"]', 'S', 'N', '#22C55E', 0, 'A', '2026-01-25 00:35:09'),
(9, 'starter', 'Starter', 'Ideal para comenzar', 29.99, 299.99, 0.00, 3, 1, 1, NULL, '[\"Soporte por email\", \"Actualizaciones mensuales\", \"1 m├│dulo deportivo\"]', 'N', 'N', '#6B7280', 0, 'A', '2026-01-26 15:38:20'),
(10, 'ENTERPRISE', 'Enterprise', 'Solución completa para grandes organizaciones', 199.99, 1999.99, 0.00, 50, 1, 50, '[]', '[\"Soporte 24/7 telefónico\", \"Actualizaciones prioritarias\", \"Todos los módulos\", \"API personalizada\", \"Capacitación incluida\"]', 'N', 'N', '#F97316', 0, 'A', '2026-01-26 15:38:20'),
(11, '', 'DigiSports Store', '', 0.00, 0.00, 0.00, 5, 1, 1, '[]', '[]', 'N', 'N', '#3B82F6', 0, 'A', '2026-02-19 21:40:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_roles`
--

DROP TABLE IF EXISTS `seguridad_roles`;
CREATE TABLE IF NOT EXISTS `seguridad_roles` (
  `rol_rol_id` int NOT NULL AUTO_INCREMENT,
  `rol_tenant_id` int DEFAULT NULL,
  `rol_codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `rol_permisos` json DEFAULT NULL,
  `rol_es_super_admin` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `rol_es_admin_tenant` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `rol_puede_modificar_permisos` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `rol_nivel_acceso` int DEFAULT '1',
  `rol_estado` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'A',
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
(9, NULL, 'ADMIN', 'Administrador', 'Administrador de tenant', '[\"usuarios.*\", \"sedes.*\", \"configuracion.*\"]', 'N', 'N', 'N', 5, 'A', '2026-01-25 00:35:19'),
(10, NULL, 'RECEPCION', 'Recepcionista', 'Gestion de reservas y clientes', '[\"reservas.*\", \"clientes.ver\", \"clientes.crear\", \"pagos.crear\"]', 'N', 'N', 'N', 3, 'A', '2026-01-25 00:35:19'),
(11, NULL, 'CLIENTE', 'Cliente', 'Usuario final con acceso limitado', '[\"reservas.ver\", \"reservas.crear\", \"perfil.*\"]', 'N', 'N', 'N', 1, 'A', '2026-01-25 00:35:19'),
(16, NULL, 'admin', 'Administrador', 'Gestión completa del tenant', '[\"dashboard.*\", \"clientes.*\", \"instalaciones.*\", \"reservas.*\", \"facturacion.*\", \"reportes.*\", \"usuarios.ver\", \"usuarios.crear\", \"usuarios.editar\"]', 'N', 'S', 'N', 4, 'A', '2026-01-26 15:38:20'),
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
) ENGINE=InnoDB AUTO_INCREMENT=243 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos de visibilidad de menú por rol';

--
-- Volcado de datos para la tabla `seguridad_rol_menu`
--

INSERT INTO `seguridad_rol_menu` (`rme_id`, `rme_rol_id`, `rme_menu_id`, `rme_puede_ver`, `rme_puede_acceder`, `rme_created_at`, `rme_updated_at`) VALUES
(1, 1, 2, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(2, 1, 3, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(3, 1, 4, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(4, 1, 5, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
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
(84, 1, 109, 1, 1, '2026-02-08 02:22:46', '2026-02-08 02:22:46'),
(129, 1, 110, 1, 1, '2026-02-08 05:47:16', '2026-02-08 05:47:16'),
(130, 1, 111, 1, 1, '2026-02-08 05:47:16', '2026-02-08 05:47:16'),
(131, 1, 112, 1, 1, '2026-02-08 05:47:16', '2026-02-08 05:47:16'),
(132, 1, 113, 1, 1, '2026-02-08 06:16:14', '2026-02-08 06:16:14'),
(133, 1, 114, 1, 1, '2026-02-08 06:16:14', '2026-02-08 06:16:14'),
(134, 1, 115, 1, 1, '2026-02-08 06:16:14', '2026-02-08 06:16:14'),
(135, 1, 116, 1, 1, '2026-02-08 06:16:14', '2026-02-08 06:16:14'),
(136, 1, 117, 1, 1, '2026-02-08 06:37:56', '2026-02-08 06:37:56'),
(137, 2, 117, 1, 1, '2026-02-08 06:37:56', '2026-02-08 06:37:56'),
(138, 1, 124, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(139, 1, 125, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(140, 1, 126, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(141, 1, 127, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(142, 1, 128, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(143, 1, 129, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(144, 1, 130, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(145, 1, 131, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(146, 1, 132, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(147, 1, 133, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(148, 1, 134, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(149, 1, 135, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(150, 1, 136, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(151, 1, 137, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(152, 1, 138, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(153, 1, 139, 1, 1, '2026-02-09 14:03:41', '2026-02-09 14:03:41'),
(154, 1, 140, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(155, 1, 141, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(156, 1, 142, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(157, 1, 143, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(158, 1, 144, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(159, 1, 145, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(160, 1, 146, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(161, 1, 147, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(162, 1, 148, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(163, 1, 149, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(164, 1, 150, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(165, 1, 151, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(166, 1, 152, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(167, 1, 153, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(168, 1, 154, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(169, 1, 155, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(170, 1, 156, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(171, 1, 157, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(172, 1, 158, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(173, 1, 159, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(174, 2, 140, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(175, 2, 141, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(176, 2, 142, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(177, 2, 143, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(178, 2, 144, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(179, 2, 145, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(180, 2, 146, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(181, 2, 147, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(182, 2, 148, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(183, 2, 149, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(184, 2, 150, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(185, 2, 151, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(186, 2, 152, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(187, 2, 153, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(188, 2, 154, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(189, 2, 155, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(190, 2, 156, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(191, 2, 157, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(192, 2, 158, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(193, 2, 159, 1, 1, '2026-02-09 17:22:38', '2026-02-09 17:22:38'),
(194, 1, 160, 1, 1, '2026-02-09 17:43:14', '2026-02-09 17:43:14'),
(195, 1, 161, 1, 1, '2026-02-09 17:43:15', '2026-02-09 17:43:15'),
(196, 1, 163, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(197, 1, 164, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(198, 1, 166, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(199, 1, 167, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(200, 1, 168, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(201, 1, 169, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(202, 1, 170, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(203, 1, 172, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(204, 1, 173, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(205, 1, 174, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(206, 1, 175, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(207, 1, 177, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(208, 1, 178, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(209, 1, 180, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(210, 1, 181, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(211, 1, 182, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(212, 1, 183, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(213, 1, 184, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(214, 1, 185, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(215, 1, 187, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(216, 1, 189, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(217, 1, 190, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(218, 1, 191, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(219, 2, 163, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(220, 2, 164, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(221, 2, 166, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(222, 2, 167, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(223, 2, 168, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(224, 2, 169, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(225, 2, 170, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(226, 2, 172, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(227, 2, 173, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(228, 2, 174, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(229, 2, 175, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(230, 2, 177, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(231, 2, 178, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(232, 2, 180, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(233, 2, 181, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(234, 2, 182, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(235, 2, 183, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(236, 2, 184, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(237, 2, 185, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(238, 2, 187, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(239, 2, 189, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(240, 2, 190, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(241, 2, 191, 1, 1, '2026-02-09 21:23:14', '2026-02-09 21:23:14'),
(242, 1, 192, 1, 1, '2026-02-19 20:39:15', '2026-02-19 20:39:15');

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
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos de roles sobre módulos';

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
-- Estructura de tabla para la tabla `seguridad_tabla`
--

DROP TABLE IF EXISTS `seguridad_tabla`;
CREATE TABLE IF NOT EXISTS `seguridad_tabla` (
  `st_id`          int           NOT NULL AUTO_INCREMENT,
  `st_nombre`      varchar(100)  NOT NULL,
  `st_descripcion` varchar(500)  NULL,
  `st_activo`      tinyint(1)    NOT NULL DEFAULT 1,
  `st_created_at`  datetime      NULL DEFAULT CURRENT_TIMESTAMP,
  `st_updated_at`  datetime      NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`st_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_tabla`
--

INSERT INTO `seguridad_tabla` (`st_id`, `st_nombre`, `st_descripcion`, `st_activo`) VALUES
(1,  'tipo_documento',        NULL, 1),
(2,  'nacionalidad',          NULL, 1),
(3,  'posicion_juego',        NULL, 1),
(4,  'parentesco',            NULL, 1),
(5,  'rubros',                NULL, 1),
(6,  'forma_pago',            NULL, 1),
(7,  'descuento',             NULL, 1),
(8,  'especialidad_empleado', NULL, 1),
(9,  'tipo_participacion',    NULL, 1),
(10, 'tallas',                NULL, 1),
(11, 'tipo_ingreso',          NULL, 1),
(12, 'tipo_personal',         NULL, 1),
(13, 'tipo_egreso',           NULL, 1),
(14, 'egreso_dscto',          NULL, 1),
(15, 'periodicidad',          NULL, 1),
(16, 'forma_entregaingreso',  NULL, 1),
(17, 'balance_ingreso',       NULL, 1),
(18, 'balance_egreso',        NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_tabla_catalogo`
--

DROP TABLE IF EXISTS `seguridad_tabla_catalogo`;
CREATE TABLE IF NOT EXISTS `seguridad_tabla_catalogo` (
  `stc_id`         int           NOT NULL AUTO_INCREMENT,
  `stc_tabla_id`   int           NOT NULL,
  `stc_codigo`     varchar(10)   NOT NULL,
  `stc_valor`      varchar(255)  NOT NULL,
  `stc_etiqueta`   varchar(255)  NULL,
  `stc_orden`      int           NOT NULL DEFAULT 10,
  `stc_activo`     tinyint(1)    NOT NULL DEFAULT 1,
  `stc_created_at` datetime      NULL DEFAULT CURRENT_TIMESTAMP,
  `stc_updated_at` datetime      NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stc_id`),
  UNIQUE KEY `idx_stc_grupo_codigo` (`stc_tabla_id`, `stc_codigo`),
  KEY `idx_stc_orden` (`stc_orden`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_tabla_catalogo`
-- stc_valor  = descripción (valor almacenado en los registros)
-- stc_etiqueta = etiqueta para mostrar en UI (igual a stc_valor por defecto)
-- stc_orden  = stc_id * 10 (permite insertar entre valores existentes)
--

INSERT INTO `seguridad_tabla_catalogo` (`stc_id`, `stc_tabla_id`, `stc_codigo`, `stc_valor`, `stc_etiqueta`, `stc_orden`, `stc_activo`) VALUES
(1,   1,  'CED', 'CÉDULA',                        'CÉDULA',                        10,  1),
(2,   1,  'PAS', 'PASAPORTE',                     'PASAPORTE',                     20,  1),
(3,   1,  'DNI', 'DNI',                           'DNI',                           30,  1),
(4,   2,  'ECU', 'ECUATORIANA',                   'ECUATORIANA',                   40,  1),
(5,   2,  'PER', 'PERUANA',                       'PERUANA',                       50,  1),
(6,   2,  'COL', 'COLOMBIANA',                    'COLOMBIANA',                    60,  1),
(7,   2,  'VEN', 'VENEZOLANA',                    'VENEZOLANA',                    70,  1),
(8,   2,  'USA', 'ESTADOUNIDENSE',                'ESTADOUNIDENSE',                80,  1),
(9,   3,  '3DE', 'Delantero',                     'Delantero',                     90,  1),
(10,  3,  '3AR', 'Portero',                       'Portero',                       100, 1),
(11,  3,  '3CE', 'Centrocampista',                'Centrocampista',                110, 1),
(12,  3,  '3DF', 'Defensa',                       'Defensa',                       120, 1),
(13,  4,  '4MA', 'Madre',                         'Madre',                         130, 1),
(14,  4,  '4PA', 'Padre',                         'Padre',                         140, 1),
(15,  4,  '4HE', 'Hermano/a',                     'Hermano/a',                     150, 1),
(16,  4,  '4TI', 'Tio/a',                         'Tio/a',                         160, 1),
(17,  4,  '4AB', 'Abuelo/a',                      'Abuelo/a',                      170, 1),
(18,  5,  'ROT', 'Otros',                         'Otros',                         180, 1),
(19,  5,  'RKE', 'Kit entrenamiento',             'Kit entrenamiento',             190, 1),
(20,  5,  'RIN', 'Inscripción',                   'Inscripción',                   200, 1),
(21,  5,  'RPE', 'Pensión',                       'Pensión',                       210, 1),
(22,  5,  'RNU', 'Nuevo Uniforme',                'Nuevo Uniforme',                220, 1),
(23,  6,  'FEF', 'Efectivo',                      'Efectivo',                      230, 1),
(24,  6,  'FTR', 'Transferencia',                 'Transferencia',                 240, 1),
(25,  6,  'FTC', 'Tarjeta',                       'Tarjeta',                       250, 1),
(26,  6,  'FJU', 'Justificado',                   'Justificado',                   260, 1),
(27,  7,  'DBC', 'Beca',                          'Beca',                          270, 1),
(28,  7,  'DDS', 'Descuento',                     'Descuento',                     280, 1),
(29,  6,  'FNA', 'No aplica',                     'No aplica',                     290, 1),
(30,  8,  'EAT', 'Asistente técnico de arqueros', 'Asistente técnico de arqueros', 300, 1),
(31,  8,  'EED', 'Entrenador delanteros',         'Entrenador delanteros',         310, 1),
(32,  9,  'PJP', 'Jugador Principal',             'Jugador Principal',             320, 1),
(33,  9,  'PJS', 'Jugador Suplente',              'Jugador Suplente',              330, 1),
(34,  8,  'EEG', 'Entrenador general',            'Entrenador general',            340, 1),
(35,  6,  'FTL', 'Transferencia Banco de Loja',   'Transferencia Banco de Loja',   350, 1),
(36,  6,  'FTP', 'Transferencia Banco Pichincha', 'Transferencia Banco Pichincha', 360, 1),
(37,  10, 'T28', '28',                            '28',                            370, 1),
(38,  10, 'T30', '30',                            '30',                            380, 1),
(39,  10, 'T32', '32',                            '32',                            390, 1),
(40,  10, 'T34', '34',                            '34',                            400, 1),
(41,  10, 'T36', '36',                            '36',                            410, 1),
(42,  10, 'TS',  'S',                             'S',                             420, 1),
(43,  10, 'TM',  'M',                             'M',                             430, 1),
(44,  10, 'TL',  'L',                             'L',                             440, 1),
(45,  11, 'TIH', 'Pago honorarios',               'Pago honorarios',               450, 1),
(46,  11, 'TIX', 'Horas Extras',                  'Horas Extras',                  460, 1),
(47,  11, 'TIR', 'Reconocimiento',                'Reconocimiento',                470, 1),
(48,  12, 'TPE', 'Empleado',                      'Empleado',                      480, 1),
(49,  12, 'TPS', 'Secretaria',                    'Secretaria',                    490, 1),
(50,  12, 'TPP', 'Profesor',                      'Profesor',                      500, 1),
(51,  12, 'TPT', 'Asistente',                     'Asistente',                     510, 1),
(52,  13, 'TEA', 'Anticipo',                      'Anticipo',                      520, 1),
(53,  13, 'TEM', 'Multa',                         'Multa',                         530, 1),
(54,  15, 'PEM', 'Mensual',                       'Mensual',                       540, 1),
(55,  15, 'PEQ', 'Quincenal',                     'Quincenal',                     550, 1),
(56,  15, 'PES', 'Semanal',                       'Semanal',                       560, 1),
(57,  16, 'EIT', 'Transferencia',                 'Transferencia',                 570, 1),
(58,  16, 'EIE', 'Efectivo',                      'Efectivo',                      580, 1),
(59,  16, 'EIC', 'Cheque',                        'Cheque',                        590, 1),
(60,  16, 'EID', 'Descuento',                     'Descuento',                     600, 1),
(61,  17, 'BIA', 'Auspicio',                      'Auspicio',                      610, 1),
(62,  17, 'BID', 'Donación',                      'Donación',                      620, 1),
(63,  17, 'BIO', 'Otros ingresos',                'Otros ingresos',                630, 1),
(64,  18, 'BEA', 'Arriendos',                     'Arriendos',                     640, 1),
(65,  18, 'BEP', 'Publicidad',                    'Publicidad',                    650, 1),
(66,  18, 'BEO', 'Otros Egresos',                 'Otros Egresos',                 660, 1),
(67,  5,  'RPC', 'Campeonato',                    'Campeonato',                    670, 1);

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
  `tar_estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO' COMMENT 'ACTIVO, INACTIVO',
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
  `ten_ruc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_ruc_hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_razon_social` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_nombre_comercial` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_tipo_empresa` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_direccion` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_telefono` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_celular` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_email` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_email_hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_sitio_web` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_representante_nombre` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_representante_identificacion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_representante_identificacion_hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_representante_email` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_representante_email_hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_representante_telefono` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_plan_id` int NOT NULL,
  `ten_fecha_inicio` date NOT NULL,
  `ten_fecha_vencimiento` date NOT NULL,
  `ten_estado_suscripcion` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVA',
  `ten_usuarios_permitidos` int DEFAULT '5',
  `ten_sedes_permitidas` int DEFAULT '1',
  `ten_almacenamiento_gb` int DEFAULT '10',
  `ten_logo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_favicon` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_color_primario` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#007bff',
  `ten_color_secundario` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#6c757d',
  `ten_color_acento` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#28a745',
  `ten_tiene_sistema_antiguo` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `ten_bd_antigua` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_tenant_id_antiguo` int DEFAULT NULL,
  `ten_monto_mensual` decimal(10,2) NOT NULL,
  `ten_dia_corte` int DEFAULT '1',
  `ten_metodo_pago_preferido` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_timezone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'America/Guayaquil',
  `ten_idioma` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'es',
  `ten_moneda` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `ten_estado` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `ten_motivo_suspension` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  KEY `idx_fecha_vencimiento` (`ten_fecha_vencimiento`),
  KEY `idx_ten_ruc_hash` (`ten_ruc_hash`),
  KEY `idx_ten_email_hash` (`ten_email_hash`),
  KEY `idx_ten_rep_id_hash` (`ten_representante_identificacion_hash`),
  KEY `idx_ten_rep_email_hash` (`ten_representante_email_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_tenants`
--

INSERT INTO `seguridad_tenants` (`ten_tenant_id`, `ten_ruc`, `ten_ruc_hash`, `ten_razon_social`, `ten_nombre_comercial`, `ten_tipo_empresa`, `ten_direccion`, `ten_telefono`, `ten_celular`, `ten_email`, `ten_email_hash`, `ten_sitio_web`, `ten_representante_nombre`, `ten_representante_identificacion`, `ten_representante_identificacion_hash`, `ten_representante_email`, `ten_representante_email_hash`, `ten_representante_telefono`, `ten_plan_id`, `ten_fecha_inicio`, `ten_fecha_vencimiento`, `ten_estado_suscripcion`, `ten_usuarios_permitidos`, `ten_sedes_permitidas`, `ten_almacenamiento_gb`, `ten_logo`, `ten_favicon`, `ten_color_primario`, `ten_color_secundario`, `ten_color_acento`, `ten_tiene_sistema_antiguo`, `ten_bd_antigua`, `ten_tenant_id_antiguo`, `ten_monto_mensual`, `ten_dia_corte`, `ten_metodo_pago_preferido`, `ten_timezone`, `ten_idioma`, `ten_moneda`, `ten_estado`, `ten_motivo_suspension`, `ten_fecha_suspension`, `ten_fecha_registro`, `ten_fecha_actualizacion`, `ten_usuario_registro`, `ten_usuario_actualizacion`) VALUES
(1, 'ENC::7SuLBbk0pbyKTZEkVoprbVcSvLxBhAG4/GT90gcocPo=', '61fdfa41b29b7fd7e6e97822c91fe2ce', 'DigiSports Administracion', 'DigiSports Admin', '', 'Rey david y los Olivos', 'ENC::5WcG1kzwHpNpzRXO0wV1uJ9E/6PznVdS2dQ1Ep33kHA=', '', 'ENC::gBJ0k7Pn9HF0ZLpfx4ZVa/QTIg6Mhb0YeSR0YDHtSMAF41yVt/S2defgKJp2FW2m', 'fa2536059c2cfc78fe680f0629a1859d', '', '', '', NULL, '', NULL, '', 4, '2026-01-24', '2028-01-24', 'ACTIVA', 5, 0, 10, NULL, NULL, '', '', '#28a745', 'N', NULL, NULL, 0.00, 1, NULL, 'America/Guayaquil', 'es', 'USD', 'A', NULL, NULL, '2026-01-25 00:35:10', '2026-02-08 04:32:07', NULL, NULL),
(2, 'ENC::IRuyMXryh/re4HRZoFBPRxRWFZO9bEjkAVEjSDsdW6M=', '426abbcb34966d2c6650f86367f4f54d', 'Champions', 'Champios CF 2013', '', '', 'ENC::uRb97yrqjUQEYQV/SCiwdC/S3QtW+LfKfa2u9+sWI7I=', '', 'ENC::KgWA/bE3BuMncDklgyqCsA0wWzR8UiOmxJfPp+dBC53y74VuUaXiC18/nFaEM7PM', 'fa2536059c2cfc78fe680f0629a1859d', '', '', '', NULL, '', NULL, '', 2, '2026-01-27', '2029-03-27', 'ACTIVA', 6, 0, 10, NULL, NULL, '', '', '#28a745', 'N', NULL, NULL, 0.00, 1, NULL, 'America/Guayaquil', 'es', 'USD', 'A', NULL, NULL, '2026-01-27 05:27:48', '2026-03-03 19:41:13', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_tenant_configuraciones`
--

DROP TABLE IF EXISTS `seguridad_tenant_configuraciones`;
CREATE TABLE IF NOT EXISTS `seguridad_tenant_configuraciones` (
  `con_config_id` int NOT NULL AUTO_INCREMENT,
  `con_tenant_id` int NOT NULL,
  `con_clave` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `con_valor` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `con_tipo` enum('string','int','bool','json') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `con_descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `tmo_nombre_personalizado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tmo_icono_personalizado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tmo_color_personalizado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tmo_orden_visualizacion` int DEFAULT '0',
  `tmo_activo` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `tmo_fecha_inicio` date NOT NULL,
  `tmo_fecha_fin` date DEFAULT NULL COMMENT 'NULL = sin vencimiento',
  `tmo_estado` enum('ACTIVO','SUSPENDIDO','VENCIDO','CANCELADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `tmo_tipo_licencia` enum('PRUEBA','MENSUAL','ANUAL','PERPETUA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'MENSUAL',
  `tmo_max_usuarios` int DEFAULT NULL COMMENT 'NULL = ilimitado',
  `tmo_observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tmo_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tmo_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tmo_id`),
  UNIQUE KEY `uk_tenant_modulo` (`tmo_tenant_id`,`tmo_modulo_id`),
  KEY `idx_tenant` (`tmo_tenant_id`),
  KEY `idx_modulo` (`tmo_modulo_id`),
  KEY `idx_estado` (`tmo_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=526 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Suscripciones de tenants a módulos';

--
-- Volcado de datos para la tabla `seguridad_tenant_modulos`
--

INSERT INTO `seguridad_tenant_modulos` (`tmo_id`, `tmo_tenant_id`, `tmo_modulo_id`, `tmo_nombre_personalizado`, `tmo_icono_personalizado`, `tmo_color_personalizado`, `tmo_orden_visualizacion`, `tmo_activo`, `tmo_fecha_inicio`, `tmo_fecha_fin`, `tmo_estado`, `tmo_tipo_licencia`, `tmo_max_usuarios`, `tmo_observaciones`, `tmo_created_at`, `tmo_updated_at`) VALUES
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
(495, 1, 30, NULL, NULL, NULL, 0, 'S', '2026-02-07', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-07 23:59:19', '2026-02-07 23:59:19'),
(511, 0, 0, NULL, NULL, NULL, 0, 'S', '2026-02-25', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-25 17:23:43', '2026-02-25 17:23:43'),
(513, 2, 1, NULL, NULL, NULL, 0, 'N', '2026-03-03', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-03 19:41:13', '2026-03-03 19:41:13'),
(514, 2, 3, NULL, NULL, NULL, 0, 'N', '2026-03-03', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-03 19:41:13', '2026-03-03 19:41:13'),
(515, 2, 4, NULL, NULL, NULL, 0, 'N', '2026-03-03', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-03 19:41:13', '2026-03-03 19:41:13'),
(516, 2, 5, NULL, NULL, NULL, 0, 'N', '2026-03-03', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-03 19:41:13', '2026-03-03 19:41:13'),
(517, 2, 6, NULL, NULL, NULL, 0, 'N', '2026-03-03', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-03 19:41:13', '2026-03-03 19:41:13'),
(518, 2, 8, NULL, NULL, NULL, 0, 'N', '2026-03-03', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-03 19:41:13', '2026-03-03 19:41:13'),
(519, 2, 15, NULL, NULL, NULL, 0, 'N', '2026-03-03', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-03 19:41:13', '2026-03-03 19:41:13'),
(520, 2, 16, NULL, NULL, NULL, 0, 'N', '2026-03-03', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-03 19:41:13', '2026-03-03 19:41:13'),
(521, 2, 18, NULL, NULL, NULL, 0, 'N', '2026-03-03', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-03 19:41:13', '2026-03-03 19:41:13'),
(522, 2, 19, NULL, NULL, NULL, 0, 'N', '2026-03-03', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-03 19:41:13', '2026-03-03 19:41:13'),
(523, 2, 20, NULL, NULL, NULL, 0, 'N', '2026-03-03', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-03 19:41:13', '2026-03-03 19:41:13'),
(524, 2, 21, NULL, NULL, NULL, 0, 'N', '2026-03-03', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-03 19:41:13', '2026-03-03 19:41:13'),
(525, 2, 22, NULL, NULL, NULL, 0, 'N', '2026-03-03', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-03 19:41:13', '2026-03-03 19:41:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_usuarios`
--

DROP TABLE IF EXISTS `seguridad_usuarios`;
CREATE TABLE IF NOT EXISTS `seguridad_usuarios` (
  `usu_usuario_id` int NOT NULL AUTO_INCREMENT,
  `usu_tenant_id` int NOT NULL,
  `usu_identificacion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_identificacion_hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_nombres` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usu_apellidos` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usu_email` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_email_hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_telefono` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_celular` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usu_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usu_requiere_2fa` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `usu_codigo_2fa` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_codigo_2fa_expira` datetime DEFAULT NULL,
  `usu_intentos_2fa` int DEFAULT '0',
  `usu_token_recuperacion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_token_recuperacion_expira` datetime DEFAULT NULL,
  `usu_rol_id` int NOT NULL,
  `usu_permisos_especiales` json DEFAULT NULL,
  `usu_ultimo_login` datetime DEFAULT NULL,
  `usu_ip_ultimo_login` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_token_sesion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_token_sesion_expira` datetime DEFAULT NULL,
  `usu_sedes_acceso` json DEFAULT NULL,
  `usu_sede_principal_id` int DEFAULT NULL,
  `usu_avatar` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usu_tema` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'light',
  `usu_idioma` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'es',
  `usu_notificaciones_email` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `usu_notificaciones_push` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `usu_debe_cambiar_password` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `usu_password_expira` date DEFAULT NULL,
  `usu_intentos_fallidos` int DEFAULT '0',
  `usu_bloqueado_hasta` datetime DEFAULT NULL,
  `usu_estado` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  `usu_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usu_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`usu_usuario_id`),
  UNIQUE KEY `username` (`usu_username`),
  UNIQUE KEY `uk_tenant_email` (`usu_tenant_id`,`usu_email`),
  KEY `rol_id` (`usu_rol_id`),
  KEY `idx_username` (`usu_username`),
  KEY `idx_email` (`usu_email`),
  KEY `idx_estado` (`usu_estado`),
  KEY `idx_tenant` (`usu_tenant_id`),
  KEY `idx_usu_identificacion_hash` (`usu_identificacion_hash`),
  KEY `idx_usu_email_hash` (`usu_email_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_usuarios`
--

INSERT INTO `seguridad_usuarios` (`usu_usuario_id`, `usu_tenant_id`, `usu_identificacion`, `usu_identificacion_hash`, `usu_nombres`, `usu_apellidos`, `usu_email`, `usu_email_hash`, `usu_telefono`, `usu_celular`, `usu_username`, `usu_password`, `usu_requiere_2fa`, `usu_codigo_2fa`, `usu_codigo_2fa_expira`, `usu_intentos_2fa`, `usu_token_recuperacion`, `usu_token_recuperacion_expira`, `usu_rol_id`, `usu_permisos_especiales`, `usu_ultimo_login`, `usu_ip_ultimo_login`, `usu_token_sesion`, `usu_token_sesion_expira`, `usu_sedes_acceso`, `usu_sede_principal_id`, `usu_avatar`, `usu_tema`, `usu_idioma`, `usu_notificaciones_email`, `usu_notificaciones_push`, `usu_debe_cambiar_password`, `usu_password_expira`, `usu_intentos_fallidos`, `usu_bloqueado_hasta`, `usu_estado`, `usu_fecha_registro`, `usu_fecha_actualizacion`) VALUES
(1, 1, 'ENC::1IeSjwyCnY1tTslc8FJ7WUme8b1HuxmAhl0cjF78U/c=', '46e867782d4667050ad7bf37c46a7107', 'Freddy', 'Pinzón', 'ENC::N8M5PUSsUol/X8uKN/PMBHj5PJ5em+R7qqqImEVO3qiGtF07Q21IhWHHlC/S6Qsa', 'c0e957495bb43b36f0ff7dea96030260', 'ENC::yeNJ1lTl5ufG6m0IduQTxMBubjp17a3yFRRgPbnBpdk=', 'ENC::tYpnIfZI5bK0QOhffMikZeAxzkzK4bcvtvdEChSNhxA=', 'superadmin', '$argon2id$v=19$m=65536,t=4,p=3$Qm9TcVczOThmQkh4N0hDTg$gdL3FkFtDnw+MnyyE+VIxagpRB019YMCK0w+fOpeydg', 'N', '798279', '2026-01-24 20:21:48', 0, NULL, NULL, 1, NULL, '2026-03-04 10:25:29', '::1', 'd46c1a54c78b28260bf588612ead286bf1e0d7218452375938c70b356bcff026', '2026-02-24 17:56:18', NULL, NULL, NULL, 'light', 'es', 'S', 'S', 'N', '2027-01-01', 0, NULL, 'A', '2026-01-25 00:35:10', '2026-03-04 15:25:29'),
(3, 2, 'ENC::4i0U6033Xu31fjUOOi2ZzeXGZr+5UNQcImt6cx6XtGY=', '46e867782d4667050ad7bf37c46a7107', 'Bolívar', 'Pinzón', 'ENC::aG7Jk4au40KYzfi95x20TBEOCTM6/H4YHvEeXnXrT0qOSFaMN92H8Jom6nvoEcsc', 'fa2536059c2cfc78fe680f0629a1859d', 'ENC::O1kxV+oUWhj3YETC2fl1HYNagPPFEvuyaQYmn7lJElo=', '', 'fbpinzon', '$argon2id$v=19$m=65536,t=4,p=1$ek8zOE9QbEtsZU5heWNGSw$gCwhDpX6hVMCYRs0VCbryQ9j1+8XL38U5XnkUmMkOyo', 'N', NULL, NULL, 0, NULL, NULL, 1, NULL, '2026-02-25 15:21:17', '::1', NULL, NULL, NULL, NULL, NULL, 'light', 'es', 'S', 'S', 'S', NULL, 0, NULL, 'A', '2026-01-29 22:22:55', '2026-02-26 04:58:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_cajas`
--

DROP TABLE IF EXISTS `store_cajas`;
CREATE TABLE IF NOT EXISTS `store_cajas` (
  `caj_caja_id` int NOT NULL AUTO_INCREMENT,
  `caj_tenant_id` int NOT NULL,
  `caj_nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Caja 1, Caja Principal, etc.',
  `caj_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caj_ubicacion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caj_impresora` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre de impresora POS',
  `caj_activa` tinyint(1) DEFAULT '1',
  `caj_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`caj_caja_id`),
  KEY `idx_caj_tenant` (`caj_tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `store_cajas`
--

INSERT INTO `store_cajas` (`caj_caja_id`, `caj_tenant_id`, `caj_nombre`, `caj_codigo`, `caj_ubicacion`, `caj_impresora`, `caj_activa`, `caj_fecha_registro`) VALUES
(1, 1, 'Caja Principal', 'CAJA-001', 'Mostrador principal', NULL, 1, '2026-02-09 14:02:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_caja_arqueo`
--

DROP TABLE IF EXISTS `store_caja_arqueo`;
CREATE TABLE IF NOT EXISTS `store_caja_arqueo` (
  `arq_arqueo_id` int NOT NULL AUTO_INCREMENT,
  `arq_tenant_id` int NOT NULL,
  `arq_turno_id` int NOT NULL,
  `arq_denominacion` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '$100, $50, $20, $10, $5, $1, $0.50, $0.25, $0.10, $0.05, $0.01',
  `arq_cantidad` int NOT NULL DEFAULT '0',
  `arq_subtotal` decimal(12,2) GENERATED ALWAYS AS ((`arq_cantidad` * cast(replace(`arq_denominacion`,_utf8mb4'$',_cp850'') as decimal(12,2)))) STORED,
  `arq_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`arq_arqueo_id`),
  KEY `idx_arq_turno` (`arq_turno_id`),
  KEY `fk_arq_tenant` (`arq_tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_caja_movimientos`
--

DROP TABLE IF EXISTS `store_caja_movimientos`;
CREATE TABLE IF NOT EXISTS `store_caja_movimientos` (
  `cmv_movimiento_id` int NOT NULL AUTO_INCREMENT,
  `cmv_tenant_id` int NOT NULL,
  `cmv_turno_id` int NOT NULL,
  `cmv_tipo` enum('ENTRADA','SALIDA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cmv_monto` decimal(12,2) NOT NULL,
  `cmv_motivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Cambio, pago proveedor, retiro parcial...',
  `cmv_autorizado_por` int DEFAULT NULL COMMENT 'Supervisor que autoriz??',
  `cmv_usuario_id` int NOT NULL,
  `cmv_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cmv_movimiento_id`),
  KEY `idx_cmv_turno` (`cmv_turno_id`),
  KEY `fk_cmv_tenant` (`cmv_tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_caja_turnos`
--

DROP TABLE IF EXISTS `store_caja_turnos`;
CREATE TABLE IF NOT EXISTS `store_caja_turnos` (
  `tur_turno_id` int NOT NULL AUTO_INCREMENT,
  `tur_tenant_id` int NOT NULL,
  `tur_caja_id` int NOT NULL,
  `tur_usuario_id` int NOT NULL COMMENT 'Cajero que abri??',
  `tur_monto_apertura` decimal(12,2) NOT NULL COMMENT 'Fondo inicial de caja',
  `tur_fecha_apertura` datetime NOT NULL,
  `tur_fecha_cierre` datetime DEFAULT NULL,
  `tur_monto_cierre_esperado` decimal(12,2) DEFAULT NULL COMMENT 'Calculado por el sistema',
  `tur_monto_cierre_real` decimal(12,2) DEFAULT NULL COMMENT 'Conteo f??sico del cajero',
  `tur_diferencia` decimal(12,2) DEFAULT NULL COMMENT 'Real - Esperado (sobrante/faltante)',
  `tur_total_ventas` decimal(12,2) DEFAULT '0.00',
  `tur_total_efectivo` decimal(12,2) DEFAULT '0.00',
  `tur_total_tarjeta` decimal(12,2) DEFAULT '0.00',
  `tur_total_transferencia` decimal(12,2) DEFAULT '0.00',
  `tur_total_otros` decimal(12,2) DEFAULT '0.00',
  `tur_num_ventas` int DEFAULT '0',
  `tur_num_devoluciones` int DEFAULT '0',
  `tur_total_devoluciones` decimal(12,2) DEFAULT '0.00',
  `tur_notas_apertura` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tur_notas_cierre` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tur_usuario_cierre` int DEFAULT NULL COMMENT 'Supervisor que autoriz?? cierre',
  `tur_estado` enum('ABIERTO','CERRADO','ANULADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ABIERTO',
  `tur_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tur_turno_id`),
  KEY `idx_tur_tenant` (`tur_tenant_id`),
  KEY `idx_tur_caja` (`tur_tenant_id`,`tur_caja_id`),
  KEY `idx_tur_estado` (`tur_tenant_id`,`tur_estado`),
  KEY `idx_tur_usuario` (`tur_tenant_id`,`tur_usuario_id`),
  KEY `idx_tur_fecha` (`tur_tenant_id`,`tur_fecha_apertura`),
  KEY `fk_tur_caja` (`tur_caja_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `store_caja_turnos`
--

INSERT INTO `store_caja_turnos` (`tur_turno_id`, `tur_tenant_id`, `tur_caja_id`, `tur_usuario_id`, `tur_monto_apertura`, `tur_fecha_apertura`, `tur_fecha_cierre`, `tur_monto_cierre_esperado`, `tur_monto_cierre_real`, `tur_diferencia`, `tur_total_ventas`, `tur_total_efectivo`, `tur_total_tarjeta`, `tur_total_transferencia`, `tur_total_otros`, `tur_num_ventas`, `tur_num_devoluciones`, `tur_total_devoluciones`, `tur_notas_apertura`, `tur_notas_cierre`, `tur_usuario_cierre`, `tur_estado`, `tur_fecha_registro`) VALUES
(1, 1, 1, 1, 0.00, '2026-02-09 23:21:23', NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0.00, NULL, NULL, NULL, 'ABIERTO', '2026-02-10 04:21:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_categorias`
--

DROP TABLE IF EXISTS `store_categorias`;
CREATE TABLE IF NOT EXISTS `store_categorias` (
  `cat_categoria_id` int NOT NULL AUTO_INCREMENT,
  `cat_tenant_id` int NOT NULL,
  `cat_padre_id` int DEFAULT NULL COMMENT 'Subcategor??a de...',
  `cat_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cat_slug` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cat_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cat_icono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fas fa-folder',
  `cat_imagen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cat_orden` int DEFAULT '0',
  `cat_activo` tinyint(1) DEFAULT '1',
  `cat_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `cat_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cat_categoria_id`),
  KEY `idx_cat_tenant` (`cat_tenant_id`),
  KEY `idx_cat_padre` (`cat_padre_id`),
  KEY `idx_cat_slug` (`cat_tenant_id`,`cat_slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `store_categorias`
--

INSERT INTO `store_categorias` (`cat_categoria_id`, `cat_tenant_id`, `cat_padre_id`, `cat_nombre`, `cat_slug`, `cat_descripcion`, `cat_icono`, `cat_imagen`, `cat_orden`, `cat_activo`, `cat_fecha_registro`, `cat_fecha_actualizacion`) VALUES
(1, 1, NULL, 'Balones', 'balones', NULL, 'fas fa-futbol', NULL, 1, 1, '2026-02-09 14:02:35', '2026-02-09 14:02:35'),
(2, 1, NULL, 'Ropa Deportiva', 'ropa-deportiva', NULL, 'fas fa-tshirt', NULL, 2, 1, '2026-02-09 14:02:35', '2026-02-09 14:02:35'),
(3, 1, NULL, 'Calzado Deportivo', 'calzado-deportivo', NULL, 'fas fa-shoe-prints', NULL, 3, 1, '2026-02-09 14:02:35', '2026-02-09 14:02:35'),
(4, 1, NULL, 'Accesorios', 'accesorios', NULL, 'fas fa-glasses', NULL, 4, 1, '2026-02-09 14:02:35', '2026-02-09 14:02:35'),
(5, 1, NULL, 'Equipamiento', 'equipamiento', NULL, 'fas fa-dumbbell', NULL, 5, 1, '2026-02-09 14:02:35', '2026-02-09 14:02:35'),
(6, 1, NULL, 'Suplementos', 'suplementos', NULL, 'fas fa-capsules', NULL, 6, 1, '2026-02-09 14:02:35', '2026-02-09 14:02:35'),
(7, 1, NULL, 'Tecnolog??a Deportiva', 'tecnologia-deportiva', NULL, 'fas fa-headset', NULL, 7, 1, '2026-02-09 14:02:35', '2026-02-09 14:02:35'),
(8, 1, NULL, 'Hidrataci??n', 'hidratacion', NULL, 'fas fa-tint', NULL, 8, 1, '2026-02-09 14:02:35', '2026-02-09 14:02:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_clientes`
--

DROP TABLE IF EXISTS `store_clientes`;
CREATE TABLE IF NOT EXISTS `store_clientes` (
  `scl_id` int NOT NULL AUTO_INCREMENT,
  `scl_tenant_id` int NOT NULL,
  `scl_cliente_id` int NOT NULL COMMENT 'FK a clientes.cli_cliente_id (tabla compartida)',
  `scl_categoria` enum('NUEVO','REGULAR','FRECUENTE','VIP') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'NUEVO',
  `scl_puntos_acumulados` int DEFAULT '0' COMMENT 'Total historico de puntos ganados',
  `scl_puntos_canjeados` int DEFAULT '0',
  `scl_puntos_disponibles` int GENERATED ALWAYS AS ((`scl_puntos_acumulados` - `scl_puntos_canjeados`)) STORED,
  `scl_total_compras` decimal(12,2) DEFAULT '0.00' COMMENT 'Lifetime value en Store',
  `scl_num_compras` int DEFAULT '0',
  `scl_ultima_compra` date DEFAULT NULL,
  `scl_acepta_marketing` tinyint(1) DEFAULT '0',
  `scl_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Notas especificas de tienda',
  `scl_activo` tinyint(1) DEFAULT '1',
  `scl_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `scl_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`scl_id`),
  UNIQUE KEY `uk_scl_tenant_cliente` (`scl_tenant_id`,`scl_cliente_id`),
  KEY `idx_scl_categoria` (`scl_tenant_id`,`scl_categoria`),
  KEY `fk_scl_cliente` (`scl_cliente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_cliente_puntos_log`
--

DROP TABLE IF EXISTS `store_cliente_puntos_log`;
CREATE TABLE IF NOT EXISTS `store_cliente_puntos_log` (
  `cpl_log_id` int NOT NULL AUTO_INCREMENT,
  `cpl_tenant_id` int NOT NULL,
  `cpl_scl_id` int NOT NULL COMMENT 'FK a store_clientes.scl_id',
  `cpl_cliente_id` int NOT NULL COMMENT 'FK a clientes.cli_cliente_id (consultas rapidas)',
  `cpl_tipo` enum('ACUMULACION','CANJE','AJUSTE','EXPIRACION') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpl_puntos` int NOT NULL,
  `cpl_saldo_anterior` int DEFAULT '0',
  `cpl_saldo_nuevo` int DEFAULT '0',
  `cpl_descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpl_referencia_tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'VENTA, DEVOLUCION, MANUAL',
  `cpl_referencia_id` int DEFAULT NULL,
  `cpl_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cpl_log_id`),
  KEY `idx_cpl_scl` (`cpl_scl_id`),
  KEY `idx_cpl_cliente` (`cpl_tenant_id`,`cpl_cliente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_configuracion`
--

DROP TABLE IF EXISTS `store_configuracion`;
CREATE TABLE IF NOT EXISTS `store_configuracion` (
  `cfg_config_id` int NOT NULL AUTO_INCREMENT,
  `cfg_tenant_id` int NOT NULL,
  `cfg_clave` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cfg_valor` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cfg_tipo` enum('STRING','INT','DECIMAL','BOOL','JSON') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'STRING',
  `cfg_grupo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `cfg_descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cfg_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cfg_config_id`),
  UNIQUE KEY `uk_cfg_tenant_clave` (`cfg_tenant_id`,`cfg_clave`),
  KEY `idx_cfg_grupo` (`cfg_tenant_id`,`cfg_grupo`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `store_configuracion`
--

INSERT INTO `store_configuracion` (`cfg_config_id`, `cfg_tenant_id`, `cfg_clave`, `cfg_valor`, `cfg_tipo`, `cfg_grupo`, `cfg_descripcion`, `cfg_fecha_actualizacion`) VALUES
(1, 1, 'moneda', 'USD', 'STRING', 'general', 'Moneda del sistema', '2026-02-09 14:02:35'),
(2, 1, 'simbolo_moneda', '$', 'STRING', 'general', 'S??mbolo de moneda', '2026-02-09 14:02:35'),
(3, 1, 'decimales_precio', '2', 'INT', 'general', 'Decimales en precios', '2026-02-09 14:02:35'),
(4, 1, 'iva_porcentaje', '15', 'DECIMAL', 'impuestos', 'Porcentaje IVA vigente', '2026-02-09 14:02:35'),
(5, 1, 'precio_incluye_iva', '1', 'BOOL', 'impuestos', 'Los precios incluyen IVA', '2026-02-09 14:02:35'),
(6, 1, 'nombre_tienda', 'DigiSports Store', 'STRING', 'tienda', 'Nombre de la tienda', '2026-02-09 14:02:35'),
(7, 1, 'direccion_tienda', '', 'STRING', 'tienda', 'Direcci??n de la tienda', '2026-02-09 14:02:35'),
(8, 1, 'telefono_tienda', '', 'STRING', 'tienda', 'Tel??fono de la tienda', '2026-02-09 14:02:35'),
(9, 1, 'ruc_tienda', '', 'STRING', 'tienda', 'RUC de la tienda', '2026-02-09 14:02:35'),
(10, 1, 'prefijo_venta', 'V-', 'STRING', 'pos', 'Prefijo para n??mero de venta', '2026-02-09 14:02:35'),
(11, 1, 'prefijo_cotizacion', 'COT-', 'STRING', 'pos', 'Prefijo para cotizaciones', '2026-02-09 14:02:35'),
(12, 1, 'prefijo_devolucion', 'DEV-', 'STRING', 'pos', 'Prefijo para devoluciones', '2026-02-09 14:02:35'),
(13, 1, 'prefijo_orden_compra', 'OC-', 'STRING', 'pos', 'Prefijo para ??rdenes de compra', '2026-02-09 14:02:35'),
(14, 1, 'monto_apertura_default', '100.00', 'DECIMAL', 'caja', 'Monto sugerido para apertura de caja', '2026-02-09 14:02:35'),
(15, 1, 'requiere_cierre_caja', '1', 'BOOL', 'caja', 'Requiere cierre de caja obligatorio', '2026-02-09 14:02:35'),
(16, 1, 'requiere_arqueo', '1', 'BOOL', 'caja', 'Requiere arqueo al cerrar caja', '2026-02-09 14:02:35'),
(17, 1, 'puntos_por_dolar', '1', 'INT', 'fidelizacion', 'Puntos ganados por cada d??lar de compra', '2026-02-09 14:02:35'),
(18, 1, 'valor_punto', '0.01', 'DECIMAL', 'fidelizacion', 'Valor en d??lares de cada punto', '2026-02-09 14:02:35'),
(19, 1, 'puntos_minimos_canje', '100', 'INT', 'fidelizacion', 'Puntos m??nimos para canjear', '2026-02-09 14:02:35'),
(20, 1, 'stock_alerta_dias', '7', 'INT', 'inventario', 'D??as para revisar alertas de stock', '2026-02-09 14:02:35'),
(21, 1, 'permitir_venta_sin_stock', '0', 'BOOL', 'inventario', 'Permitir vender productos sin stock', '2026-02-09 14:02:35'),
(22, 1, 'ticket_header', 'Gracias por su compra', 'STRING', 'ticket', 'Encabezado del ticket', '2026-02-09 14:02:35'),
(23, 1, 'ticket_footer', 'Cambios con ticket dentro de 15 d??as', 'STRING', 'ticket', 'Pie del ticket', '2026-02-09 14:02:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_cotizaciones`
--

DROP TABLE IF EXISTS `store_cotizaciones`;
CREATE TABLE IF NOT EXISTS `store_cotizaciones` (
  `cot_cotizacion_id` int NOT NULL AUTO_INCREMENT,
  `cot_tenant_id` int NOT NULL,
  `cot_numero` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cot_cliente_id` int DEFAULT NULL,
  `cot_fecha` date NOT NULL,
  `cot_vigencia_dias` int DEFAULT '15',
  `cot_subtotal` decimal(12,2) DEFAULT '0.00',
  `cot_descuento` decimal(12,2) DEFAULT '0.00',
  `cot_impuesto` decimal(12,2) DEFAULT '0.00',
  `cot_total` decimal(12,2) DEFAULT '0.00',
  `cot_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cot_estado` enum('BORRADOR','ENVIADA','ACEPTADA','RECHAZADA','VENCIDA','CONVERTIDA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'BORRADOR',
  `cot_venta_id` int DEFAULT NULL COMMENT 'Si fue convertida a venta',
  `cot_usuario_id` int DEFAULT NULL,
  `cot_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `cot_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cot_cotizacion_id`),
  KEY `idx_cot_tenant` (`cot_tenant_id`),
  KEY `idx_cot_cliente` (`cot_tenant_id`,`cot_cliente_id`),
  KEY `idx_cot_estado` (`cot_tenant_id`,`cot_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_cotizacion_items`
--

DROP TABLE IF EXISTS `store_cotizacion_items`;
CREATE TABLE IF NOT EXISTS `store_cotizacion_items` (
  `coi_item_id` int NOT NULL AUTO_INCREMENT,
  `coi_tenant_id` int NOT NULL,
  `coi_cotizacion_id` int NOT NULL,
  `coi_producto_id` int NOT NULL,
  `coi_variante_id` int DEFAULT NULL,
  `coi_descripcion` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `coi_cantidad` decimal(10,3) NOT NULL DEFAULT '1.000',
  `coi_precio_unitario` decimal(12,4) NOT NULL,
  `coi_descuento_linea` decimal(12,2) DEFAULT '0.00',
  `coi_impuesto_linea` decimal(12,2) DEFAULT '0.00',
  `coi_subtotal` decimal(12,2) DEFAULT '0.00',
  PRIMARY KEY (`coi_item_id`),
  KEY `idx_coi_cotizacion` (`coi_cotizacion_id`),
  KEY `fk_coi_tenant` (`coi_tenant_id`),
  KEY `fk_coi_producto` (`coi_producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_descuentos`
--

DROP TABLE IF EXISTS `store_descuentos`;
CREATE TABLE IF NOT EXISTS `store_descuentos` (
  `dsc_descuento_id` int NOT NULL AUTO_INCREMENT,
  `dsc_tenant_id` int NOT NULL,
  `dsc_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dsc_codigo` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'C??digo cup??n (opcional)',
  `dsc_tipo` enum('PORCENTAJE','MONTO_FIJO','COMPRA_X_LLEVA_Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dsc_valor` decimal(12,4) NOT NULL COMMENT '% o monto fijo',
  `dsc_minimo_compra` decimal(12,2) DEFAULT NULL COMMENT 'Compra m??nima para aplicar',
  `dsc_maximo_descuento` decimal(12,2) DEFAULT NULL COMMENT 'Tope m??ximo del descuento',
  `dsc_aplica_a` enum('TODOS','CATEGORIA','PRODUCTO','MARCA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'TODOS',
  `dsc_aplica_id` int DEFAULT NULL COMMENT 'ID de categor??a, producto o marca',
  `dsc_fecha_inicio` date DEFAULT NULL,
  `dsc_fecha_fin` date DEFAULT NULL,
  `dsc_usos_maximos` int DEFAULT NULL COMMENT 'NULL = ilimitado',
  `dsc_usos_actuales` int DEFAULT '0',
  `dsc_activo` tinyint(1) DEFAULT '1',
  `dsc_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dsc_descuento_id`),
  KEY `idx_dsc_tenant` (`dsc_tenant_id`),
  KEY `idx_dsc_codigo` (`dsc_tenant_id`,`dsc_codigo`),
  KEY `idx_dsc_fechas` (`dsc_tenant_id`,`dsc_fecha_inicio`,`dsc_fecha_fin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_devoluciones`
--

DROP TABLE IF EXISTS `store_devoluciones`;
CREATE TABLE IF NOT EXISTS `store_devoluciones` (
  `dev_devolucion_id` int NOT NULL AUTO_INCREMENT,
  `dev_tenant_id` int NOT NULL,
  `dev_venta_id` int NOT NULL,
  `dev_turno_id` int DEFAULT NULL,
  `dev_numero` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'DEV-000001',
  `dev_fecha` datetime NOT NULL,
  `dev_motivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dev_subtotal` decimal(12,2) DEFAULT '0.00',
  `dev_impuesto` decimal(12,2) DEFAULT '0.00',
  `dev_total` decimal(12,2) DEFAULT '0.00',
  `dev_tipo_reembolso` enum('EFECTIVO','CREDITO_TIENDA','MONEDERO','TARJETA','OTRO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'EFECTIVO',
  `dev_estado` enum('PENDIENTE','APROBADA','RECHAZADA','COMPLETADA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `dev_usuario_id` int DEFAULT NULL,
  `dev_aprobado_por` int DEFAULT NULL COMMENT 'Supervisor que aprob??',
  `dev_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dev_devolucion_id`),
  KEY `idx_dev_tenant` (`dev_tenant_id`),
  KEY `idx_dev_venta` (`dev_venta_id`),
  KEY `idx_dev_estado` (`dev_tenant_id`,`dev_estado`),
  KEY `idx_dev_fecha` (`dev_tenant_id`,`dev_fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_devolucion_items`
--

DROP TABLE IF EXISTS `store_devolucion_items`;
CREATE TABLE IF NOT EXISTS `store_devolucion_items` (
  `dvi_item_id` int NOT NULL AUTO_INCREMENT,
  `dvi_tenant_id` int NOT NULL,
  `dvi_devolucion_id` int NOT NULL,
  `dvi_venta_item_id` int NOT NULL COMMENT 'Referencia al ??tem original vendido',
  `dvi_producto_id` int NOT NULL,
  `dvi_variante_id` int DEFAULT NULL,
  `dvi_cantidad` decimal(10,3) NOT NULL,
  `dvi_precio_unitario` decimal(12,4) NOT NULL,
  `dvi_subtotal` decimal(12,2) DEFAULT '0.00',
  `dvi_motivo_detalle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dvi_devolver_stock` tinyint(1) DEFAULT '1' COMMENT '1=reintegrar al inventario',
  PRIMARY KEY (`dvi_item_id`),
  KEY `idx_dvi_devolucion` (`dvi_devolucion_id`),
  KEY `fk_dvi_tenant` (`dvi_tenant_id`),
  KEY `fk_dvi_venta_item` (`dvi_venta_item_id`),
  KEY `fk_dvi_producto` (`dvi_producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_impuestos`
--

DROP TABLE IF EXISTS `store_impuestos`;
CREATE TABLE IF NOT EXISTS `store_impuestos` (
  `imp_impuesto_id` int NOT NULL AUTO_INCREMENT,
  `imp_tenant_id` int NOT NULL,
  `imp_codigo_sri` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'C??digo SRI: 2=IVA, 3=ICE',
  `imp_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `imp_porcentaje` decimal(5,2) NOT NULL,
  `imp_tipo` enum('IVA','ICE','IRBPNR','OTRO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'IVA',
  `imp_aplica_a` enum('TODOS','BIENES','SERVICIOS') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'TODOS',
  `imp_es_default` tinyint(1) DEFAULT '0',
  `imp_activo` tinyint(1) DEFAULT '1',
  `imp_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`imp_impuesto_id`),
  KEY `idx_imp_tenant` (`imp_tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `store_impuestos`
--

INSERT INTO `store_impuestos` (`imp_impuesto_id`, `imp_tenant_id`, `imp_codigo_sri`, `imp_nombre`, `imp_porcentaje`, `imp_tipo`, `imp_aplica_a`, `imp_es_default`, `imp_activo`, `imp_fecha_registro`) VALUES
(1, 1, '2', 'IVA 15%', 15.00, 'IVA', 'TODOS', 1, 1, '2026-02-09 14:02:35'),
(2, 1, '0', 'IVA 0%', 0.00, 'IVA', 'TODOS', 0, 1, '2026-02-09 14:02:35'),
(3, 1, '6', 'No Objeto de Impuesto', 0.00, 'IVA', 'TODOS', 0, 1, '2026-02-09 14:02:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_marcas`
--

DROP TABLE IF EXISTS `store_marcas`;
CREATE TABLE IF NOT EXISTS `store_marcas` (
  `mar_marca_id` int NOT NULL AUTO_INCREMENT,
  `mar_tenant_id` int NOT NULL,
  `mar_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mar_slug` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mar_logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mar_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mar_activo` tinyint(1) DEFAULT '1',
  `mar_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `mar_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`mar_marca_id`),
  KEY `idx_mar_tenant` (`mar_tenant_id`),
  KEY `idx_mar_slug` (`mar_tenant_id`,`mar_slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `store_marcas`
--

INSERT INTO `store_marcas` (`mar_marca_id`, `mar_tenant_id`, `mar_nombre`, `mar_slug`, `mar_logo`, `mar_descripcion`, `mar_activo`, `mar_fecha_registro`, `mar_fecha_actualizacion`) VALUES
(1, 1, 'Nike', 'nike', NULL, NULL, 1, '2026-02-09 14:02:36', '2026-02-09 14:02:36'),
(2, 1, 'Adidas', 'adidas', NULL, NULL, 1, '2026-02-09 14:02:36', '2026-02-09 14:02:36'),
(3, 1, 'Puma', 'puma', NULL, NULL, 1, '2026-02-09 14:02:36', '2026-02-09 14:02:36'),
(4, 1, 'Under Armour', 'under-armour', NULL, NULL, 1, '2026-02-09 14:02:36', '2026-02-09 14:02:36'),
(5, 1, 'New Balance', 'new-balance', NULL, NULL, 1, '2026-02-09 14:02:36', '2026-02-09 14:02:36'),
(6, 1, 'Reebok', 'reebok', NULL, NULL, 1, '2026-02-09 14:02:36', '2026-02-09 14:02:36'),
(7, 1, 'Gen??rica', 'generica', NULL, NULL, 1, '2026-02-09 14:02:36', '2026-02-09 14:02:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_ordenes_compra`
--

DROP TABLE IF EXISTS `store_ordenes_compra`;
CREATE TABLE IF NOT EXISTS `store_ordenes_compra` (
  `orc_orden_id` int NOT NULL AUTO_INCREMENT,
  `orc_tenant_id` int NOT NULL,
  `orc_proveedor_id` int NOT NULL,
  `orc_numero` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'N??mero secuencial OC-0001',
  `orc_fecha_orden` date NOT NULL,
  `orc_fecha_entrega_esperada` date DEFAULT NULL,
  `orc_fecha_recibido` date DEFAULT NULL,
  `orc_subtotal` decimal(12,2) DEFAULT '0.00',
  `orc_descuento` decimal(12,2) DEFAULT '0.00',
  `orc_impuesto` decimal(12,2) DEFAULT '0.00',
  `orc_total` decimal(12,2) DEFAULT '0.00',
  `orc_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `orc_estado` enum('BORRADOR','ENVIADA','PARCIAL','RECIBIDA','ANULADA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'BORRADOR',
  `orc_usuario_id` int DEFAULT NULL,
  `orc_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `orc_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`orc_orden_id`),
  KEY `idx_orc_tenant` (`orc_tenant_id`),
  KEY `idx_orc_proveedor` (`orc_tenant_id`,`orc_proveedor_id`),
  KEY `idx_orc_estado` (`orc_tenant_id`,`orc_estado`),
  KEY `idx_orc_fecha` (`orc_tenant_id`,`orc_fecha_orden`),
  KEY `fk_orc_proveedor` (`orc_proveedor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_ordenes_compra_detalle`
--

DROP TABLE IF EXISTS `store_ordenes_compra_detalle`;
CREATE TABLE IF NOT EXISTS `store_ordenes_compra_detalle` (
  `ocd_detalle_id` int NOT NULL AUTO_INCREMENT,
  `ocd_tenant_id` int NOT NULL,
  `ocd_orden_id` int NOT NULL,
  `ocd_producto_id` int NOT NULL,
  `ocd_variante_id` int DEFAULT NULL,
  `ocd_cantidad_pedida` int NOT NULL,
  `ocd_cantidad_recibida` int DEFAULT '0',
  `ocd_costo_unitario` decimal(12,4) NOT NULL,
  `ocd_subtotal` decimal(12,2) GENERATED ALWAYS AS ((`ocd_cantidad_pedida` * `ocd_costo_unitario`)) STORED,
  `ocd_notas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ocd_detalle_id`),
  KEY `idx_ocd_orden` (`ocd_orden_id`),
  KEY `fk_ocd_tenant` (`ocd_tenant_id`),
  KEY `fk_ocd_producto` (`ocd_producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_productos`
--

DROP TABLE IF EXISTS `store_productos`;
CREATE TABLE IF NOT EXISTS `store_productos` (
  `pro_producto_id` int NOT NULL AUTO_INCREMENT,
  `pro_tenant_id` int NOT NULL,
  `pro_categoria_id` int DEFAULT NULL,
  `pro_marca_id` int DEFAULT NULL,
  `pro_codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'C??digo interno',
  `pro_codigo_barras` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'EAN-13 / UPC',
  `pro_sku` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pro_nombre` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pro_slug` varchar(220) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pro_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pro_descripcion_corta` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pro_precio_compra` decimal(12,4) DEFAULT '0.0000' COMMENT 'Costo de adquisici??n',
  `pro_precio_venta` decimal(12,4) NOT NULL COMMENT 'PVP sin impuesto',
  `pro_precio_mayoreo` decimal(12,4) DEFAULT NULL COMMENT 'Precio para mayoristas',
  `pro_impuesto_id` int DEFAULT NULL COMMENT 'Tipo de impuesto aplicable',
  `pro_tipo` enum('SIMPLE','VARIABLE','SERVICIO','KIT') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'SIMPLE',
  `pro_unidad_medida` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'UNIDAD' COMMENT 'UNIDAD, KG, LITRO, METRO, PAR',
  `pro_peso_kg` decimal(8,3) DEFAULT NULL,
  `pro_largo_cm` decimal(8,2) DEFAULT NULL,
  `pro_ancho_cm` decimal(8,2) DEFAULT NULL,
  `pro_alto_cm` decimal(8,2) DEFAULT NULL,
  `pro_stock_minimo` int DEFAULT '5' COMMENT 'Alerta cuando baje de este nivel',
  `pro_stock_maximo` int DEFAULT NULL,
  `pro_permite_venta_sin_stock` tinyint(1) DEFAULT '0',
  `pro_destacado` tinyint(1) DEFAULT '0',
  `pro_visible_pos` tinyint(1) DEFAULT '1' COMMENT 'Visible en punto de venta',
  `pro_visible_web` tinyint(1) DEFAULT '0' COMMENT 'Visible en tienda online',
  `pro_notas_internas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pro_imagen_principal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pro_tags` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Etiquetas separadas por coma',
  `pro_estado` enum('ACTIVO','INACTIVO','DESCONTINUADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `pro_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `pro_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pro_producto_id`),
  KEY `idx_pro_tenant` (`pro_tenant_id`),
  KEY `idx_pro_categoria` (`pro_tenant_id`,`pro_categoria_id`),
  KEY `idx_pro_marca` (`pro_tenant_id`,`pro_marca_id`),
  KEY `idx_pro_codigo` (`pro_tenant_id`,`pro_codigo`),
  KEY `idx_pro_barras` (`pro_tenant_id`,`pro_codigo_barras`),
  KEY `idx_pro_sku` (`pro_tenant_id`,`pro_sku`),
  KEY `idx_pro_nombre` (`pro_tenant_id`,`pro_nombre`),
  KEY `idx_pro_estado` (`pro_tenant_id`,`pro_estado`),
  KEY `fk_pro_categoria` (`pro_categoria_id`),
  KEY `fk_pro_marca` (`pro_marca_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_producto_imagenes`
--

DROP TABLE IF EXISTS `store_producto_imagenes`;
CREATE TABLE IF NOT EXISTS `store_producto_imagenes` (
  `img_imagen_id` int NOT NULL AUTO_INCREMENT,
  `img_tenant_id` int NOT NULL,
  `img_producto_id` int NOT NULL,
  `img_variante_id` int DEFAULT NULL,
  `img_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `img_alt` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `img_orden` int DEFAULT '0',
  `img_es_principal` tinyint(1) DEFAULT '0',
  `img_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`img_imagen_id`),
  KEY `idx_img_producto` (`img_producto_id`),
  KEY `fk_img_tenant` (`img_tenant_id`),
  KEY `fk_img_variante` (`img_variante_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_producto_variantes`
--

DROP TABLE IF EXISTS `store_producto_variantes`;
CREATE TABLE IF NOT EXISTS `store_producto_variantes` (
  `var_variante_id` int NOT NULL AUTO_INCREMENT,
  `var_tenant_id` int NOT NULL,
  `var_producto_id` int NOT NULL,
  `var_sku` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `var_codigo_barras` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `var_talla` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'XS, S, M, L, XL, 36, 37...',
  `var_color` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `var_material` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `var_otro_atributo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Atributo adicional libre',
  `var_precio_adicional` decimal(12,4) DEFAULT '0.0000' COMMENT 'Suma al precio base',
  `var_precio_override` decimal(12,4) DEFAULT NULL COMMENT 'Si se define, reemplaza precio base',
  `var_costo_override` decimal(12,4) DEFAULT NULL,
  `var_imagen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `var_peso_kg` decimal(8,3) DEFAULT NULL,
  `var_activo` tinyint(1) DEFAULT '1',
  `var_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`var_variante_id`),
  KEY `idx_var_tenant` (`var_tenant_id`),
  KEY `idx_var_producto` (`var_producto_id`),
  KEY `idx_var_sku` (`var_tenant_id`,`var_sku`),
  KEY `idx_var_barras` (`var_tenant_id`,`var_codigo_barras`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_proveedores`
--

DROP TABLE IF EXISTS `store_proveedores`;
CREATE TABLE IF NOT EXISTS `store_proveedores` (
  `prv_proveedor_id` int NOT NULL AUTO_INCREMENT,
  `prv_tenant_id` int NOT NULL,
  `prv_ruc_ci` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_razon_social` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prv_nombre_comercial` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_contacto_nombre` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_email` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_celular` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_direccion` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_ciudad` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prv_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `prv_dias_credito` int DEFAULT '0',
  `prv_activo` tinyint(1) DEFAULT '1',
  `prv_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `prv_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`prv_proveedor_id`),
  KEY `idx_prv_tenant` (`prv_tenant_id`),
  KEY `idx_prv_ruc` (`prv_tenant_id`,`prv_ruc_ci`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_stock`
--

DROP TABLE IF EXISTS `store_stock`;
CREATE TABLE IF NOT EXISTS `store_stock` (
  `stk_stock_id` int NOT NULL AUTO_INCREMENT,
  `stk_tenant_id` int NOT NULL,
  `stk_producto_id` int NOT NULL,
  `stk_variante_id` int DEFAULT NULL COMMENT 'NULL = producto simple sin variantes',
  `stk_cantidad` int DEFAULT '0' COMMENT 'Stock total',
  `stk_reservado` int DEFAULT '0' COMMENT 'Apartados/reservados',
  `stk_disponible` int GENERATED ALWAYS AS ((`stk_cantidad` - `stk_reservado`)) STORED,
  `stk_ubicacion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Pasillo, estante, etc.',
  `stk_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stk_stock_id`),
  UNIQUE KEY `uk_stk_producto_variante` (`stk_tenant_id`,`stk_producto_id`,`stk_variante_id`),
  KEY `idx_stk_disponible` (`stk_tenant_id`,`stk_disponible`),
  KEY `fk_stk_producto` (`stk_producto_id`),
  KEY `fk_stk_variante` (`stk_variante_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_stock_alertas`
--

DROP TABLE IF EXISTS `store_stock_alertas`;
CREATE TABLE IF NOT EXISTS `store_stock_alertas` (
  `ale_alerta_id` int NOT NULL AUTO_INCREMENT,
  `ale_tenant_id` int NOT NULL,
  `ale_producto_id` int NOT NULL,
  `ale_variante_id` int DEFAULT NULL,
  `ale_stock_actual` int NOT NULL,
  `ale_stock_minimo` int NOT NULL,
  `ale_estado` enum('PENDIENTE','NOTIFICADA','RESUELTA','IGNORADA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `ale_fecha_generada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ale_fecha_resuelta` datetime DEFAULT NULL,
  PRIMARY KEY (`ale_alerta_id`),
  KEY `idx_ale_tenant_estado` (`ale_tenant_id`,`ale_estado`),
  KEY `idx_ale_producto` (`ale_producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_stock_movimientos`
--

DROP TABLE IF EXISTS `store_stock_movimientos`;
CREATE TABLE IF NOT EXISTS `store_stock_movimientos` (
  `mov_movimiento_id` int NOT NULL AUTO_INCREMENT,
  `mov_tenant_id` int NOT NULL,
  `mov_producto_id` int NOT NULL,
  `mov_variante_id` int DEFAULT NULL,
  `mov_tipo` enum('ENTRADA','SALIDA','AJUSTE','TRANSFERENCIA','DEVOLUCION','VENTA','COMPRA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mov_cantidad` int NOT NULL COMMENT 'Positivo=entrada, negativo=salida',
  `mov_stock_anterior` int NOT NULL,
  `mov_stock_posterior` int NOT NULL,
  `mov_costo_unitario` decimal(12,4) DEFAULT NULL,
  `mov_referencia_tipo` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'VENTA, ORDEN_COMPRA, AJUSTE_MANUAL, DEVOLUCION',
  `mov_referencia_id` int DEFAULT NULL COMMENT 'ID de la venta, orden, etc.',
  `mov_motivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mov_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mov_usuario_id` int DEFAULT NULL,
  `mov_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mov_movimiento_id`),
  KEY `idx_mov_tenant` (`mov_tenant_id`),
  KEY `idx_mov_producto` (`mov_tenant_id`,`mov_producto_id`),
  KEY `idx_mov_tipo` (`mov_tenant_id`,`mov_tipo`),
  KEY `idx_mov_fecha` (`mov_tenant_id`,`mov_fecha_registro`),
  KEY `idx_mov_referencia` (`mov_referencia_tipo`,`mov_referencia_id`),
  KEY `fk_mov_producto` (`mov_producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_ventas`
--

DROP TABLE IF EXISTS `store_ventas`;
CREATE TABLE IF NOT EXISTS `store_ventas` (
  `ven_venta_id` int NOT NULL AUTO_INCREMENT,
  `ven_tenant_id` int NOT NULL,
  `ven_turno_id` int DEFAULT NULL COMMENT 'Turno de caja en que se realiz??',
  `ven_numero` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'N??mero secuencial V-000001',
  `ven_cliente_id` int DEFAULT NULL COMMENT 'FK a store_clientes (NULL = consumidor final)',
  `ven_tipo_documento` enum('TICKET','FACTURA','NOTA_VENTA','PROFORMA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'TICKET',
  `ven_fecha` datetime NOT NULL,
  `ven_subtotal_sin_impuesto` decimal(12,2) DEFAULT '0.00' COMMENT 'Base imponible 0%',
  `ven_subtotal_con_impuesto` decimal(12,2) DEFAULT '0.00' COMMENT 'Base imponible IVA',
  `ven_subtotal` decimal(12,2) DEFAULT '0.00',
  `ven_descuento` decimal(12,2) DEFAULT '0.00',
  `ven_impuesto` decimal(12,2) DEFAULT '0.00' COMMENT 'IVA calculado',
  `ven_total` decimal(12,2) DEFAULT '0.00',
  `ven_descuento_id` int DEFAULT NULL COMMENT 'Descuento/cup??n aplicado',
  `ven_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ven_vendedor_id` int DEFAULT NULL COMMENT 'Empleado que vendi?? (comisiones)',
  `ven_factura_electronica_id` int DEFAULT NULL COMMENT 'FK si se gener?? factura SRI',
  `ven_puntos_ganados` int DEFAULT '0',
  `ven_estado` enum('PENDIENTE','COMPLETADA','ANULADA','DEVUELTA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `ven_usuario_id` int DEFAULT NULL,
  `ven_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ven_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ven_venta_id`),
  KEY `idx_ven_tenant` (`ven_tenant_id`),
  KEY `idx_ven_turno` (`ven_tenant_id`,`ven_turno_id`),
  KEY `idx_ven_cliente` (`ven_tenant_id`,`ven_cliente_id`),
  KEY `idx_ven_numero` (`ven_tenant_id`,`ven_numero`),
  KEY `idx_ven_fecha` (`ven_tenant_id`,`ven_fecha`),
  KEY `idx_ven_estado` (`ven_tenant_id`,`ven_estado`),
  KEY `idx_ven_vendedor` (`ven_tenant_id`,`ven_vendedor_id`),
  KEY `fk_ven_turno` (`ven_turno_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_venta_items`
--

DROP TABLE IF EXISTS `store_venta_items`;
CREATE TABLE IF NOT EXISTS `store_venta_items` (
  `vit_item_id` int NOT NULL AUTO_INCREMENT,
  `vit_tenant_id` int NOT NULL,
  `vit_venta_id` int NOT NULL,
  `vit_producto_id` int NOT NULL,
  `vit_variante_id` int DEFAULT NULL,
  `vit_descripcion` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Snapshot del nombre al momento de venta',
  `vit_cantidad` decimal(10,3) NOT NULL DEFAULT '1.000',
  `vit_precio_unitario` decimal(12,4) NOT NULL,
  `vit_costo_unitario` decimal(12,4) DEFAULT NULL COMMENT 'Costo al momento de venta (para utilidad)',
  `vit_descuento_linea` decimal(12,2) DEFAULT '0.00',
  `vit_porcentaje_impuesto` decimal(5,2) DEFAULT '15.00',
  `vit_impuesto_linea` decimal(12,2) DEFAULT '0.00',
  `vit_subtotal` decimal(12,2) GENERATED ALWAYS AS (((`vit_cantidad` * `vit_precio_unitario`) - `vit_descuento_linea`)) STORED,
  `vit_total` decimal(12,2) GENERATED ALWAYS AS ((((`vit_cantidad` * `vit_precio_unitario`) - `vit_descuento_linea`) + `vit_impuesto_linea`)) STORED,
  `vit_notas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`vit_item_id`),
  KEY `idx_vit_venta` (`vit_venta_id`),
  KEY `idx_vit_producto` (`vit_producto_id`),
  KEY `fk_vit_tenant` (`vit_tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `store_venta_pagos`
--

DROP TABLE IF EXISTS `store_venta_pagos`;
CREATE TABLE IF NOT EXISTS `store_venta_pagos` (
  `vpg_pago_id` int NOT NULL AUTO_INCREMENT,
  `vpg_tenant_id` int NOT NULL,
  `vpg_venta_id` int NOT NULL,
  `vpg_forma_pago` enum('EFECTIVO','TARJETA_DEBITO','TARJETA_CREDITO','TRANSFERENCIA','MONEDERO','CREDITO','CHEQUE','OTRO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vpg_monto` decimal(12,2) NOT NULL,
  `vpg_referencia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Num. transacci??n, num. cheque, etc.',
  `vpg_cambio` decimal(12,2) DEFAULT '0.00' COMMENT 'Vuelto (solo para efectivo)',
  `vpg_fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`vpg_pago_id`),
  KEY `idx_vpg_venta` (`vpg_venta_id`),
  KEY `idx_vpg_forma` (`vpg_tenant_id`,`vpg_forma_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `usuarios`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
`apellidos` varchar(150)
,`celular` varchar(255)
,`email` varchar(500)
,`estado` char(1)
,`fecha_actualizacion` timestamp
,`fecha_registro` timestamp
,`identificacion` varchar(255)
,`nombres` varchar(150)
,`password` varchar(255)
,`requiere_2fa` char(1)
,`rol_id` int
,`telefono` varchar(255)
,`tenant_id` int
,`username` varchar(50)
,`usuario_id` int
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vw_estadisticas_canchas`
--

DROP TABLE IF EXISTS `vw_estadisticas_canchas`;
CREATE TABLE IF NOT EXISTS `vw_estadisticas_canchas` (
  `cancha_id` int DEFAULT NULL,
  `tenant_id` int DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `tipo` varchar(50) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `total_tarifas` bigint DEFAULT NULL,
  `total_mantenimientos` bigint DEFAULT NULL,
  `mantenimientos_completados` bigint DEFAULT NULL,
  `mantenimientos_pendientes` bigint DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf16 COLLATE=utf16_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vw_tarifas_por_dia`
--

DROP TABLE IF EXISTS `vw_tarifas_por_dia`;
CREATE TABLE IF NOT EXISTS `vw_tarifas_por_dia` (
  `tarifa_id` int DEFAULT NULL,
  `cancha_id` int DEFAULT NULL,
  `cancha_nombre` varchar(100) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `cancha_tipo` varchar(50) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `dia_semana` tinyint DEFAULT NULL,
  `dia_nombre` varchar(10) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `estado` varchar(20) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `tenant_id` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf16 COLLATE=utf16_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura para la vista `mantenimientos`
--
DROP TABLE IF EXISTS `mantenimientos`;

DROP VIEW IF EXISTS `mantenimientos`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `mantenimientos`  AS SELECT `instalaciones_mantenimientos`.`man_mantenimiento_id` AS `mantenimiento_id`, `instalaciones_mantenimientos`.`man_tenant_id` AS `tenant_id`, `instalaciones_mantenimientos`.`man_cancha_id` AS `cancha_id`, `instalaciones_mantenimientos`.`man_tipo` AS `tipo`, `instalaciones_mantenimientos`.`man_descripcion` AS `descripcion`, `instalaciones_mantenimientos`.`man_notas` AS `notas`, `instalaciones_mantenimientos`.`man_fecha_inicio` AS `fecha_inicio`, `instalaciones_mantenimientos`.`man_fecha_fin` AS `fecha_fin`, `instalaciones_mantenimientos`.`man_responsable_id` AS `responsable_id`, `instalaciones_mantenimientos`.`man_recurrir` AS `recurrir`, `instalaciones_mantenimientos`.`man_cadencia_recurrencia` AS `cadencia_recurrencia`, `instalaciones_mantenimientos`.`man_estado` AS `estado`, `instalaciones_mantenimientos`.`man_fecha_creacion` AS `fecha_creacion`, `instalaciones_mantenimientos`.`man_fecha_actualizacion` AS `fecha_actualizacion`, `instalaciones_mantenimientos`.`man_usuario_creacion` AS `usuario_creacion`, `instalaciones_mantenimientos`.`man_usuario_actualizacion` AS `usuario_actualizacion` FROM `instalaciones_mantenimientos` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `roles`
--
DROP TABLE IF EXISTS `roles`;

DROP VIEW IF EXISTS `roles`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `roles`  AS SELECT `seguridad_roles`.`rol_rol_id` AS `rol_id`, `seguridad_roles`.`rol_tenant_id` AS `tenant_id`, `seguridad_roles`.`rol_codigo` AS `codigo`, `seguridad_roles`.`rol_nombre` AS `nombre`, `seguridad_roles`.`rol_descripcion` AS `descripcion`, `seguridad_roles`.`rol_permisos` AS `permisos`, `seguridad_roles`.`rol_estado` AS `estado`, `seguridad_roles`.`rol_fecha_registro` AS `fecha_registro` FROM `seguridad_roles` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `usuarios`
--
DROP TABLE IF EXISTS `usuarios`;

DROP VIEW IF EXISTS `usuarios`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `usuarios`  AS SELECT `seguridad_usuarios`.`usu_usuario_id` AS `usuario_id`, `seguridad_usuarios`.`usu_tenant_id` AS `tenant_id`, `seguridad_usuarios`.`usu_identificacion` AS `identificacion`, `seguridad_usuarios`.`usu_nombres` AS `nombres`, `seguridad_usuarios`.`usu_apellidos` AS `apellidos`, `seguridad_usuarios`.`usu_email` AS `email`, `seguridad_usuarios`.`usu_telefono` AS `telefono`, `seguridad_usuarios`.`usu_celular` AS `celular`, `seguridad_usuarios`.`usu_username` AS `username`, `seguridad_usuarios`.`usu_password` AS `password`, `seguridad_usuarios`.`usu_requiere_2fa` AS `requiere_2fa`, `seguridad_usuarios`.`usu_rol_id` AS `rol_id`, `seguridad_usuarios`.`usu_estado` AS `estado`, `seguridad_usuarios`.`usu_fecha_registro` AS `fecha_registro`, `seguridad_usuarios`.`usu_fecha_actualizacion` AS `fecha_actualizacion` FROM `seguridad_usuarios` ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
