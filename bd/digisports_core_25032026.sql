-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 25-03-2026 a las 16:36:17
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla compartida de alumnos ??? todos los subsistemas deportivos';

--
-- Volcado de datos para la tabla `alumnos`
--

INSERT INTO `alumnos` (`alu_alumno_id`, `alu_tenant_id`, `alu_sede_id`, `alu_representante_id`, `alu_parentesco`, `alu_tipo_identificacion`, `alu_identificacion`, `alu_identificacion_hash`, `alu_nombres`, `alu_apellidos`, `alu_fecha_nacimiento`, `alu_genero`, `alu_foto`, `alu_tipo_sangre`, `alu_alergias`, `alu_condiciones_medicas`, `alu_medicamentos`, `alu_contacto_emergencia`, `alu_telefono_emergencia`, `alu_estado`, `alu_notas`, `alu_fecha_registro`, `alu_fecha_actualizacion`, `alu_observaciones_medicas`) VALUES
(1, 1, 1, 4, 'PADRE', 'CED', 'ENC::+SifHMdnZGOXVc35ndIVWH166MNtvBf0F94MOOhTNhQ=', '7951383d5135e4bf27e53dc7600a7e40', 'Matias', 'Pinzon', '2014-02-20', 'M', 'storage/tenants/1/alumnos/fotos/1_ea4e40c2-df5e-45d6-83da-4bb1c7e2aef4.jpg', NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVO', NULL, '2026-03-06 23:00:05', '2026-03-19 17:28:35', NULL),
(2, 1, 1, 4, 'PADRE', 'CED', 'ENC::6rVyYCCVvFgBS/XnRYsqQlJlAo2zrKgyn4KfAXAdfi0=', '46e867782d4667050ad7bf37c46a7107', 'Emma Sofia', 'Pinzón Quinde', '2022-05-25', 'F', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVO', NULL, '2026-03-07 04:00:30', '2026-03-25 15:32:40', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`cli_cliente_id`, `cli_tenant_id`, `cli_tipo_identificacion`, `cli_identificacion`, `cli_identificacion_hash`, `cli_nombres`, `cli_apellidos`, `cli_email`, `cli_email_hash`, `cli_telefono`, `cli_celular`, `cli_direccion`, `cli_fecha_nacimiento`, `cli_tipo_cliente`, `cli_saldo_abono`, `cli_estado`, `cli_fecha_registro`, `cli_consentimiento_datos`, `cli_consentimiento_fecha`, `cli_consentimiento_ip`) VALUES
(1, 1, 'PAS', 'ENC::Qo0Dd1Lj+4SN464X3IGcqBn2jMJ8ttNnT9HrBgVnwck=', 'ff072a883770e15764c0f56479a16a78', 'Freddy', 'Bolivar Pinzon Olmedo', 'ENC::MFlXozVYpNPtRpW9vIygO8bfSLp70oh6DOeFOzqbPspTYsMMrS7cAif3H6LLOLyR', 'fa2536059c2cfc78fe680f0629a1859d', 'ENC::oUiyk7GlCyDtL6DvBvkKOY2s+tGROo505vHHGzZdP4k=', NULL, NULL, NULL, 'PUBLICO', 37.75, 'A', '2026-01-26 00:36:19', 0, NULL, NULL),
(2, 2, 'CED', 'ENC::TkQoa+DY7JkP5Hs04r0QMTUIwBWqGQ3M6U8Z7D4a85o=', '46e867782d4667050ad7bf37c46a7107', 'Freddy Bolívar', 'Pinzón Olmedo', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut', 'fa2536059c2cfc78fe680f0629a1859d', 'ENC::Wi4TBafq7H4H83WK3AYmhk2iJMoPnx86TbVZij7QFTM=', NULL, 'Loja, Rey david y los Olivos -336', NULL, 'REPRESENTANTE', 0.00, 'A', '2026-03-06 21:20:04', 1, '2026-03-06 16:20:04', '::1'),
(3, 2, 'CED', 'ENC::HMzA/Hn+zZS7CsLIbyIwgqjL/hZIUW6AI8QIUc32F4c=', '46e867782d4667050ad7bf37c46a7107', 'Freddy Bolívar', 'Pinzón Olmedo', 'ENC::mVx4qkeUZE1TvROMumX495NRle9sMCruj/WcFeP55PyFidKkjThtygXF+t+JfWOt', 'fa2536059c2cfc78fe680f0629a1859d', 'ENC::oaWGQUuJTnusMnjS68/ccIXjLBp0W9Bty6qTNJQ2hvw=', NULL, 'ENC::myiP0tHIkgzacgk2pfKcO2SC/bzPqdp/voXepdbdpY9rzmyVyRqov/CbGXFgenbJ0PSG3G85gb8NR38LKJDD5A==', NULL, 'PUBLICO', 0.00, 'A', '2026-03-17 16:45:18', 0, NULL, NULL),
(4, 1, 'CED', 'ENC::9rBd4GXXQv5zK3p7ko3/zNDaMNyjp9ewT0vdMFZ43MA=', '46e867782d4667050ad7bf37c46a7107', 'Freddy Bolívar', 'Pinzón Olmedo', 'ENC::QYxUw8Mkn70DtNJb22u7GTDilTWDv75KQ+HeuFLoxNVAZTk4JvLJL8LA4d5mssFi', 'fa2536059c2cfc78fe680f0629a1859d', 'ENC::5OGwFrVMR0zI8UNWe7DP2pQWKYgSpsXBcM5D7uyDiVY=', NULL, 'ENC::raQ7+TcSfbMkLdGV6O3PhfmhF0W8RFFxfK3x2pwIT3n/NyJ5s4+XNp0srywrKWrjVwh2Wkwgu3CBjGzWdplPeQ==', NULL, 'PUBLICO', 0.00, 'A', '2026-03-17 17:19:22', 1, '2026-03-24 17:14:57', '::1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `core_archivos`
--

DROP TABLE IF EXISTS `core_archivos`;
CREATE TABLE IF NOT EXISTS `core_archivos` (
  `arc_id` int NOT NULL AUTO_INCREMENT,
  `arc_tenant_id` int NOT NULL COMMENT 'Tenant propietario del archivo',
  `arc_entidad` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'M├│dulo/entidad: alumnos, entrenadores, academias, pagos, documentos',
  `arc_entidad_id` int NOT NULL COMMENT 'PK del registro al que pertenece',
  `arc_categoria` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de archivo: fotos, documentos, logos, comprobantes',
  `arc_nombre_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre original del archivo subido por el usuario',
  `arc_nombre_almacenado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre ├║nico con UUID en disco',
  `arc_ruta_relativa` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ruta relativa desde storage/: tenants/{id}/alumnos/fotos/...',
  `arc_mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'MIME type validado por finfo (no $_FILES["type"])',
  `arc_extension` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Extensi├│n en min├║sculas: jpg, png, pdf, etc.',
  `arc_tamanio_bytes` int NOT NULL DEFAULT '0' COMMENT 'Tama├▒o del archivo en bytes',
  `arc_ancho_px` smallint DEFAULT NULL COMMENT 'Ancho en p├¡xeles (solo im├ígenes)',
  `arc_alto_px` smallint DEFAULT NULL COMMENT 'Alto en p├¡xeles (solo im├ígenes)',
  `arc_storage_driver` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local' COMMENT 'Driver de almacenamiento: local, s3, r2',
  `arc_storage_key` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Clave/path en el storage remoto (para S3/R2)',
  `arc_es_principal` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = archivo principal (ej: foto de perfil activa)',
  `arc_estado` enum('activo','eliminado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `arc_subido_por` int NOT NULL COMMENT 'usu_usuario_id del usuario que subi├│ el archivo',
  `arc_fecha_subida` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `arc_fecha_eliminacion` timestamp NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
  PRIMARY KEY (`arc_id`),
  KEY `idx_arc_tenant` (`arc_tenant_id`),
  KEY `idx_arc_entidad` (`arc_entidad`,`arc_entidad_id`),
  KEY `idx_arc_tenant_entidad` (`arc_tenant_id`,`arc_entidad`,`arc_entidad_id`),
  KEY `idx_arc_estado` (`arc_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Repositorio central de archivos multimedia del sistema';

--
-- Volcado de datos para la tabla `core_archivos`
--

INSERT INTO `core_archivos` (`arc_id`, `arc_tenant_id`, `arc_entidad`, `arc_entidad_id`, `arc_categoria`, `arc_nombre_original`, `arc_nombre_almacenado`, `arc_ruta_relativa`, `arc_mime_type`, `arc_extension`, `arc_tamanio_bytes`, `arc_ancho_px`, `arc_alto_px`, `arc_storage_driver`, `arc_storage_key`, `arc_es_principal`, `arc_estado`, `arc_subido_por`, `arc_fecha_subida`, `arc_fecha_eliminacion`) VALUES
(1, 1, 'alumnos', 1, 'fotos', '00.jpg', '1_ea4e40c2-df5e-45d6-83da-4bb1c7e2aef4.jpg', 'storage/tenants/1/alumnos/fotos/1_ea4e40c2-df5e-45d6-83da-4bb1c7e2aef4.jpg', 'image/jpeg', 'jpg', 27684, 240, 300, 'local', NULL, 1, 'activo', 1, '2026-03-08 00:48:30', NULL),
(2, 1, 'alumnos', 2, 'fotos', '08.jpg', '2_dab6e6f3-c5ca-4ca3-b1b7-a52fabe07ded.jpg', 'storage/tenants/1/alumnos/fotos/2_dab6e6f3-c5ca-4ca3-b1b7-a52fabe07ded.jpg', 'image/jpeg', 'jpg', 29128, 240, 300, 'local', NULL, 0, 'activo', 1, '2026-03-08 02:06:20', NULL),
(3, 1, 'alumnos', 2, 'fotos', '06.jpg', '2_a63c0e34-c9c4-4e3d-9df1-17f46aee722e.jpg', 'storage/tenants/1/alumnos/fotos/2_a63c0e34-c9c4-4e3d-9df1-17f46aee722e.jpg', 'image/jpeg', 'jpg', 24258, 225, 300, 'local', NULL, 1, 'eliminado', 1, '2026-03-08 02:06:51', '2026-03-08 02:07:14'),
(4, 1, 'facturacion_configuracion', 1, 'logos', 'logo.png', '1_28670c32-bb80-4806-ad80-d2f9cde3680b.png', 'storage/tenants/1/facturacion_configuracion/logos/1_28670c32-bb80-4806-ad80-d2f9cde3680b.png', 'image/png', 'png', 50720, 300, 164, 'local', NULL, 0, 'activo', 1, '2026-03-13 22:02:40', NULL),
(5, 1, 'facturacion_configuracion', 1, 'logos', 'logo.png', '1_bdef1d23-3ec0-41f5-b173-a5d92358add5.png', 'storage/tenants/1/facturacion_configuracion/logos/1_bdef1d23-3ec0-41f5-b173-a5d92358add5.png', 'image/png', 'png', 50720, 300, 164, 'local', NULL, 0, 'activo', 1, '2026-03-13 22:03:04', NULL),
(6, 1, 'facturacion_configuracion', 1, 'logos', 'logo.png', '1_1cdd1091-4b96-46b2-98fe-82ba304ea48f.png', 'storage/tenants/1/facturacion_configuracion/logos/1_1cdd1091-4b96-46b2-98fe-82ba304ea48f.png', 'image/png', 'png', 50720, 300, 164, 'local', NULL, 0, 'activo', 1, '2026-03-13 22:03:14', NULL),
(7, 1, 'facturacion_configuracion', 1, 'logos', 'logo.png', '1_7eb744f8-5856-4257-8a98-93a928e4c420.png', 'storage/tenants/1/facturacion_configuracion/logos/1_7eb744f8-5856-4257-8a98-93a928e4c420.png', 'image/png', 'png', 50720, 300, 164, 'local', NULL, 0, 'activo', 1, '2026-03-14 16:37:18', NULL),
(8, 1, 'facturacion_configuracion', 1, 'logos', 'logo.png', '1_6697d6ff-e63f-4657-9c6b-8e5aacb31f86.png', 'storage/tenants/1/facturacion_configuracion/logos/1_6697d6ff-e63f-4657-9c6b-8e5aacb31f86.png', 'image/png', 'png', 50720, 300, 164, 'local', NULL, 0, 'activo', 1, '2026-03-16 03:29:56', NULL),
(9, 1, 'facturacion_configuracion', 1, 'logos', 'logo.png', '1_937e0fdc-0409-4491-ac67-e4cce00a9ec4.png', 'storage/tenants/1/facturacion_configuracion/logos/1_937e0fdc-0409-4491-ac67-e4cce00a9ec4.png', 'image/png', 'png', 50720, 300, 164, 'local', NULL, 0, 'activo', 1, '2026-03-16 04:25:43', NULL),
(10, 1, 'facturacion_configuracion', 1, 'logos', 'logo.png', '1_cb0b9449-abef-4524-b846-bf9ea274e2e9.png', 'storage/tenants/1/facturacion_configuracion/logos/1_cb0b9449-abef-4524-b846-bf9ea274e2e9.png', 'image/png', 'png', 50720, 300, 164, 'local', NULL, 1, 'activo', 1, '2026-03-16 04:30:17', NULL),
(11, 1, 'instalaciones_sedes', 1, 'logos', 'logo.png', '1_6fd7d41f-9ff4-406e-8502-352353882170.png', 'storage/tenants/1/instalaciones_sedes/logos/1_6fd7d41f-9ff4-406e-8502-352353882170.png', 'image/png', 'png', 50720, 300, 164, 'local', NULL, 1, 'activo', 1, '2026-03-24 20:24:14', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturacion_comprobantes_ext`
--

DROP TABLE IF EXISTS `facturacion_comprobantes_ext`;
CREATE TABLE IF NOT EXISTS `facturacion_comprobantes_ext` (
  `fce_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `fce_tenant_id` int UNSIGNED NOT NULL,
  `fce_fac_id` int UNSIGNED NOT NULL COMMENT 'FK a facturacion_facturas.fac_id',
  `fce_ext_db` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre de la BD externa (sin prefijo ext_)',
  `fce_ext_pago_id` int UNSIGNED NOT NULL COMMENT 'pago_id en la BD externa',
  `fce_created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fce_id`),
  UNIQUE KEY `uk_ext_pago` (`fce_tenant_id`,`fce_ext_db`,`fce_ext_pago_id`),
  KEY `idx_fac_id` (`fce_fac_id`),
  KEY `idx_tenant` (`fce_tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de pagos externos ya incluidos en facturas DigiSports';

--
-- Volcado de datos para la tabla `facturacion_comprobantes_ext`
--

INSERT INTO `facturacion_comprobantes_ext` (`fce_id`, `fce_tenant_id`, `fce_fac_id`, `fce_ext_db`, `fce_ext_pago_id`, `fce_created_at`) VALUES
(1, 1, 17, 'digitech_soccereasy', 7842, '2026-03-23 02:08:17'),
(2, 1, 18, 'digitech_soccereasy', 7772, '2026-03-24 20:08:39'),
(3, 1, 18, 'digitech_soccereasy', 7660, '2026-03-24 20:08:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturacion_configuracion`
--

DROP TABLE IF EXISTS `facturacion_configuracion`;
CREATE TABLE IF NOT EXISTS `facturacion_configuracion` (
  `cfg_id` int NOT NULL AUTO_INCREMENT,
  `cfg_tenant_id` int NOT NULL,
  `cfg_ruc` varchar(13) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'RUC de 13 d├¡gitos del emisor',
  `cfg_razon_social` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Raz├│n social seg├║n SRI (may├║sculas)',
  `cfg_nombre_comercial` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Nombre comercial impreso en el RIDE',
  `cfg_direccion_matriz` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Direcci├│n de la matriz',
  `cfg_direccion_establecimiento` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Direcci├│n del establecimiento emisor',
  `cfg_codigo_establecimiento` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '001',
  `cfg_punto_emision` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '001',
  `cfg_obligado_contabilidad` enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SI',
  `cfg_contribuyente_especial` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'N├║mero de resoluci├│n, vac├¡o si no aplica',
  `cfg_agente_retencion` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'N├║mero de resoluci├│n agente retenci├│n',
  `cfg_regimen_microempresas` enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NO',
  `cfg_regimen_rimpe` enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NO',
  `cfg_ambiente` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Pruebas, 2=Producci├│n',
  `cfg_secuencial_inicio` int UNSIGNED NOT NULL DEFAULT '1' COMMENT 'N├║mero desde el que inicia la secuencia',
  `cfg_logo_arc_id` int DEFAULT NULL COMMENT 'FK a core_archivos',
  `cfg_certificado_ruta` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta al archivo .p12 en el filesystem',
  `cfg_certificado_clave` text COLLATE utf8mb4_unicode_ci COMMENT 'Contrase├▒a del .p12 cifrada con AES-256-GCM',
  `cfg_certificado_vigencia` date DEFAULT NULL COMMENT 'Fecha de vencimiento del certificado',
  `cfg_email_notificaciones` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email para notificaciones de FE',
  `cfg_estado` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  `cfg_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `cfg_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cfg_created_by` int DEFAULT NULL,
  `cfg_updated_by` int DEFAULT NULL,
  PRIMARY KEY (`cfg_id`),
  UNIQUE KEY `uk_tenant` (`cfg_tenant_id`),
  KEY `idx_tenant_estado` (`cfg_tenant_id`,`cfg_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuraci├│n de Facturaci├│n Electr├│nica por Tenant';

--
-- Volcado de datos para la tabla `facturacion_configuracion`
--

INSERT INTO `facturacion_configuracion` (`cfg_id`, `cfg_tenant_id`, `cfg_ruc`, `cfg_razon_social`, `cfg_nombre_comercial`, `cfg_direccion_matriz`, `cfg_direccion_establecimiento`, `cfg_codigo_establecimiento`, `cfg_punto_emision`, `cfg_obligado_contabilidad`, `cfg_contribuyente_especial`, `cfg_agente_retencion`, `cfg_regimen_microempresas`, `cfg_regimen_rimpe`, `cfg_ambiente`, `cfg_secuencial_inicio`, `cfg_logo_arc_id`, `cfg_certificado_ruta`, `cfg_certificado_clave`, `cfg_certificado_vigencia`, `cfg_email_notificaciones`, `cfg_estado`, `cfg_created_at`, `cfg_updated_at`, `cfg_created_by`, `cfg_updated_by`) VALUES
(1, 1, '1104015282001', 'QUINDE ESPAÑA VERONICA MAGALI', 'DIGITECH', 'REY DAVID 410-34 Y JUAN EL BAUTISTA', 'REY DAVID 410-34 Y JUAN EL BAUTISTA', '002', '001', 'NO', '', '', 'SI', 'NO', 1, 1, 10, 'C:\\wamp64\\www\\digisports/storage/certificados/1/firma.p12', 'W6KOVmirX55JQb9LQb1gQDkyV01SeDVwaFIvNkp6NVZVY01yYnc9PQ==', '2026-08-13', 'fbpinzon@gmail.com', 'A', '2026-03-12 21:08:15', '2026-03-16 14:17:03', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturacion_facturas`
--

DROP TABLE IF EXISTS `facturacion_facturas`;
CREATE TABLE IF NOT EXISTS `facturacion_facturas` (
  `fac_id` int NOT NULL AUTO_INCREMENT,
  `fac_tenant_id` int NOT NULL,
  `fac_numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fac_cliente_id` int DEFAULT NULL,
  `fac_origen_modulo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'libre',
  `fac_origen_id` int DEFAULT NULL,
  `fac_subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fac_descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fac_iva` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fac_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fac_estado` enum('BORRADOR','EMITIDA','PAGADA','ANULADA') COLLATE utf8mb4_unicode_ci DEFAULT 'BORRADOR',
  `fac_fecha_emision` datetime DEFAULT NULL,
  `fac_fecha_vencimiento` date DEFAULT NULL,
  `fac_fecha_pago` datetime DEFAULT NULL,
  `fac_forma_pago_id` int DEFAULT NULL,
  `fac_usuario_id` int NOT NULL,
  `fac_observaciones` text COLLATE utf8mb4_unicode_ci,
  `fac_fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fac_fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fac_id`),
  KEY `idx_fac_tenant` (`fac_tenant_id`),
  KEY `idx_fac_cliente` (`fac_cliente_id`),
  KEY `idx_fac_origen` (`fac_origen_modulo`,`fac_origen_id`),
  KEY `fk_fac_forma_pago` (`fac_forma_pago_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `facturacion_facturas`
--

INSERT INTO `facturacion_facturas` (`fac_id`, `fac_tenant_id`, `fac_numero`, `fac_cliente_id`, `fac_origen_modulo`, `fac_origen_id`, `fac_subtotal`, `fac_descuento`, `fac_iva`, `fac_total`, `fac_estado`, `fac_fecha_emision`, `fac_fecha_vencimiento`, `fac_fecha_pago`, `fac_forma_pago_id`, `fac_usuario_id`, `fac_observaciones`, `fac_fecha_creacion`, `fac_fecha_actualizacion`) VALUES
(1, 1, '001-001-000000002', 2, 'libre', NULL, 70.90, 0.00, 10.63, 81.53, 'PAGADA', '2026-03-15 20:57:17', '2026-04-11', '2026-03-15 21:01:09', 7, 1, 'Puebas\nAnulada: ', '2026-03-12 22:37:12', '2026-03-16 02:01:09'),
(2, 1, '001-001-000000003', 2, 'libre', NULL, 30.00, 0.00, 4.50, 34.50, 'PAGADA', '2026-03-15 20:57:07', '2026-04-13', '2026-03-15 21:02:09', 1, 1, 'Pago de mensualidad mes de marzo\nAnulada: ', '2026-03-14 16:53:26', '2026-03-16 02:02:09'),
(3, 1, '001-001-000000004', 2, 'libre', NULL, 45.78, 0.00, 6.87, 52.65, 'PAGADA', '2026-03-14 13:27:45', '2026-04-13', '2026-03-15 21:13:57', 4, 1, '', '2026-03-14 18:27:37', '2026-03-16 02:13:57'),
(4, 1, '001-001-000000005', 2, 'libre', NULL, 145.00, 0.00, 21.75, 166.75, 'PAGADA', '2026-03-15 20:56:53', '2026-04-13', '2026-03-15 21:13:18', 1, 1, '\nAnulada: ', '2026-03-15 01:07:24', '2026-03-16 02:13:18'),
(5, 1, '001-001-000000006', 2, 'libre', NULL, 7.00, 0.00, 1.05, 8.05, 'PAGADA', '2026-03-15 23:31:30', '2026-04-14', '2026-03-15 23:36:04', 1, 1, '', '2026-03-16 04:31:19', '2026-03-16 04:36:04'),
(6, 1, '001-001-000000007', 2, 'libre', NULL, 25.00, 0.00, 3.75, 28.75, 'PAGADA', '2026-03-16 08:53:00', '2026-04-15', '2026-03-16 08:54:15', 1, 1, '', '2026-03-16 13:54:15', '2026-03-16 13:54:15'),
(7, 1, '001-001-000000008', 2, 'libre', NULL, 40.48, 0.00, 6.07, 46.55, 'PAGADA', '2026-03-16 08:56:00', '2026-04-15', '2026-03-16 08:57:52', 1, 1, '', '2026-03-16 13:57:52', '2026-03-16 13:57:52'),
(8, 1, '002-001-000000002', 2, 'libre', NULL, 35.00, 0.00, 5.25, 40.25, 'PAGADA', '2026-03-16 09:17:00', '2026-04-15', '2026-03-16 09:18:31', 1, 1, '', '2026-03-16 14:18:31', '2026-03-16 14:18:31'),
(9, 1, '002-001-000000003', 2, 'libre', NULL, 30.00, 0.00, 4.50, 34.50, 'PAGADA', '2026-03-16 09:27:00', '2026-04-15', '2026-03-16 09:28:29', 1, 1, '', '2026-03-16 14:28:29', '2026-03-16 14:28:29'),
(10, 1, '002-001-000000004', 2, 'libre', NULL, 7.50, 0.00, 1.13, 8.63, 'PAGADA', '2026-03-16 10:08:00', '2026-04-15', '2026-03-16 10:10:02', 1, 1, '', '2026-03-16 15:10:02', '2026-03-16 15:10:02'),
(11, 1, '002-001-000000005', 2, 'libre', NULL, 35.00, 0.00, 5.25, 40.25, 'PAGADA', '2026-03-16 16:33:00', '2026-04-15', '2026-03-16 16:35:05', 1, 1, '', '2026-03-16 21:35:05', '2026-03-16 21:35:05'),
(12, 1, '002-001-000000006', 2, 'libre', NULL, 22.00, 0.00, 3.30, 25.30, 'PAGADA', '2026-03-16 21:20:00', '2026-04-15', '2026-03-16 21:21:21', 7, 1, '', '2026-03-17 02:21:21', '2026-03-17 02:21:21'),
(13, 1, '002-001-000000007', 2, 'libre', NULL, 35.00, 0.00, 5.25, 40.25, 'PAGADA', '2026-03-16 22:39:00', '2026-04-15', '2026-03-16 22:40:32', 1, 1, '', '2026-03-17 03:40:32', '2026-03-17 03:40:32'),
(14, 1, '002-001-000000008', 4, 'libre', NULL, 110.78, 0.00, 3.87, 114.65, 'PAGADA', '2026-03-19 15:12:00', '2026-04-18', '2026-03-19 15:14:03', 7, 1, '', '2026-03-19 20:14:03', '2026-03-19 20:14:03'),
(15, 1, '002-001-000000009', 4, 'libre', NULL, 70.00, 0.00, 4.50, 74.50, 'PAGADA', '2026-03-19 15:24:00', '2026-04-18', '2026-03-19 15:31:24', 1, 1, '', '2026-03-19 20:31:24', '2026-03-19 20:31:24'),
(16, 1, '002-001-000000010', 4, 'libre', NULL, 159.00, 0.00, 0.00, 159.00, 'PAGADA', '2026-03-21 21:36:00', '2026-04-20', '2026-03-21 21:51:58', 7, 1, '', '2026-03-22 02:51:58', '2026-03-22 02:51:58'),
(17, 1, '002-001-000000011', 4, 'libre', NULL, 34.00, 0.00, 1.05, 35.05, 'PAGADA', '2026-03-22 21:05:00', '2026-04-21', '2026-03-22 21:08:17', 1, 1, '', '2026-03-23 02:08:16', '2026-03-23 02:08:17'),
(18, 1, '002-001-000000012', 4, 'libre', NULL, 17.00, 0.00, 0.00, 17.00, 'PAGADA', '2026-03-24 15:08:00', '2026-04-23', '2026-03-24 15:08:39', 7, 1, '', '2026-03-24 20:08:39', '2026-03-24 20:08:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturacion_formas_pago`
--

DROP TABLE IF EXISTS `facturacion_formas_pago`;
CREATE TABLE IF NOT EXISTS `facturacion_formas_pago` (
  `fpa_id` int NOT NULL AUTO_INCREMENT,
  `fpa_tenant_id` int NOT NULL,
  `fpa_nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fpa_codigo_sri` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fpa_estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `fpa_fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fpa_id`),
  KEY `idx_fpa_tenant` (`fpa_tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `facturacion_formas_pago`
--

INSERT INTO `facturacion_formas_pago` (`fpa_id`, `fpa_tenant_id`, `fpa_nombre`, `fpa_codigo_sri`, `fpa_estado`, `fpa_fecha_creacion`) VALUES
(1, 1, 'SIN UTILIZACION DEL SISTEMA FINANCIERO', '01', 'ACTIVO', '2026-03-11 04:42:25'),
(2, 1, 'COMPENSACIÓN DE DEUDAS', '15', 'INACTIVO', '2026-03-11 04:42:25'),
(3, 1, 'TARJETA DE DÉBITO', '16', 'ACTIVO', '2026-03-11 04:42:25'),
(4, 1, 'DINERO ELECTRÓNICO', '17', 'INACTIVO', '2026-03-11 04:42:25'),
(5, 1, 'TARJETA PREPAGO', '18', 'ACTIVO', '2026-03-11 04:42:25'),
(6, 1, 'TARJETA DE CRÉDITO', '19', 'ACTIVO', '2026-03-11 04:42:25'),
(7, 1, 'OTROS CON UTILIZACION DEL SISTEMA FINANCIERO', '20', 'ACTIVO', '2026-03-11 04:42:25'),
(8, 1, 'ENDOSO DE TÍTULOS', '21', 'INACTIVO', '2026-03-11 04:42:25'),
(9, 2, 'SIN UTILIZACION DEL SISTEMA FINANCIERO', '01', 'ACTIVO', '2026-03-16 03:24:15'),
(10, 2, 'COMPENSACIÓN DE DEUDAS', '15', 'ACTIVO', '2026-03-16 03:24:15'),
(11, 2, 'TARJETA DE DÉBITO', '16', 'ACTIVO', '2026-03-16 03:24:15'),
(12, 2, 'DINERO ELECTRÓNICO', '17', 'ACTIVO', '2026-03-16 03:24:15'),
(13, 2, 'TARJETA PREPAGO', '18', 'ACTIVO', '2026-03-16 03:24:15'),
(14, 2, 'TARJETA DE CRÉDITO', '19', 'ACTIVO', '2026-03-16 03:24:15'),
(15, 2, 'OTROS CON UTILIZACION DEL SISTEMA FINANCIERO', '20', 'ACTIVO', '2026-03-16 03:24:15'),
(16, 2, 'ENDOSO DE TÍTULOS', '21', 'ACTIVO', '2026-03-16 03:24:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturacion_lineas`
--

DROP TABLE IF EXISTS `facturacion_lineas`;
CREATE TABLE IF NOT EXISTS `facturacion_lineas` (
  `lin_id` int NOT NULL AUTO_INCREMENT,
  `lin_factura_id` int NOT NULL,
  `lin_codigo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lin_descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lin_cantidad` decimal(10,2) NOT NULL DEFAULT '1.00',
  `lin_precio_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  `lin_descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `lin_porcentaje_iva` decimal(5,2) NOT NULL DEFAULT '15.00',
  `lin_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `lin_fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`lin_id`),
  KEY `idx_lin_factura` (`lin_factura_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `facturacion_lineas`
--

INSERT INTO `facturacion_lineas` (`lin_id`, `lin_factura_id`, `lin_codigo`, `lin_descripcion`, `lin_cantidad`, `lin_precio_unitario`, `lin_descuento`, `lin_porcentaje_iva`, `lin_total`, `lin_fecha_creacion`) VALUES
(1, 1, 'SER01', 'PENSION MES DE MARZO | MAR-26', 1.00, 30.00, 5.00, 15.00, 28.75, '2026-03-12 22:37:12'),
(2, 1, 'SER02', 'uNIFORMES 2026 | MAR-26', 1.00, 45.90, 0.00, 15.00, 52.78, '2026-03-12 22:37:12'),
(3, 2, 'SERV001', 'Pago de mensualidad | MAR26', 1.00, 30.00, 0.00, 15.00, 34.50, '2026-03-14 16:53:26'),
(4, 3, 'SERV02', 'Pago de uniformes | MAR-26', 1.00, 50.78, 5.00, 15.00, 52.65, '2026-03-14 18:27:37'),
(5, 4, 'SERV02', 'Torneo | MAR-26', 1.00, 145.00, 0.00, 15.00, 166.75, '2026-03-15 01:07:24'),
(6, 5, 'SER01', 'Pago torneo Ecuador cup-kid | MAR-26', 1.00, 7.00, 0.00, 15.00, 8.05, '2026-03-16 04:31:19'),
(7, 6, 'SER01', 'Pagon mensualidad mes de marzo | MAR-26', 1.00, 30.00, 5.00, 15.00, 28.75, '2026-03-16 13:54:15'),
(8, 7, 'SER03', 'Pago de nuevo uniforme de competencia | MAR-26', 1.00, 50.48, 10.00, 15.00, 46.55, '2026-03-16 13:57:52'),
(9, 8, '001', 'pAGO MENSUALIDAD | MAR-26', 1.00, 35.00, 0.00, 15.00, 40.25, '2026-03-16 14:18:31'),
(10, 9, '01', 'Pago mensualidad, mes de marzo | MAR-26', 1.00, 30.00, 0.00, 15.00, 34.50, '2026-03-16 14:28:29'),
(11, 10, '002', 'Inscripción torneo Ecuador  Cup Kids | MAR-26', 1.00, 7.50, 0.00, 15.00, 8.63, '2026-03-16 15:10:02'),
(12, 11, 'S001', 'Pago de Piscina | MAR-26', 1.00, 35.00, 0.00, 15.00, 40.25, '2026-03-16 21:35:05'),
(13, 12, 'S01', 'Pago copa cup kids | MAR26', 1.00, 22.00, 0.00, 15.00, 25.30, '2026-03-17 02:21:21'),
(14, 13, 'SR1', 'Pago pension febrero | MAR-26', 1.00, 35.00, 0.00, 15.00, 40.25, '2026-03-17 03:40:32'),
(15, 14, 'PG-3', 'MENSUALIDAD 2026-03 (2026-03) — Emma Sofia Pinzón Quinde', 1.00, 30.00, 0.00, 0.00, 30.00, '2026-03-19 20:14:03'),
(16, 14, 'PG-2', '(2026-01) — Matias Pinzon', 1.00, 30.00, 0.00, 0.00, 30.00, '2026-03-19 20:14:03'),
(17, 14, 'PG-1', '(2026-03) — Emma Sofia Pinzón Quinde', 1.00, 25.00, 0.00, 0.00, 25.00, '2026-03-19 20:14:03'),
(18, 14, 'PCA', 'Pago de Cancha | MAR-26', 1.00, 25.78, 0.00, 15.00, 29.65, '2026-03-19 20:14:03'),
(19, 15, 'RCT', 'Certificado / Diploma', 1.00, 10.00, 0.00, 0.00, 10.00, '2026-03-19 20:31:24'),
(20, 15, 'PG-3', 'MENSUALIDAD 2026-03 (2026-03) — Emma Sofia Pinzón Quinde', 1.00, 30.00, 0.00, 0.00, 30.00, '2026-03-19 20:31:24'),
(21, 15, 'S01', 'Pago de alquiler e cancha', 1.00, 30.00, 0.00, 15.00, 34.50, '2026-03-19 20:31:24'),
(22, 16, 'PG-2', '(2026-01) — Matias Pinzon', 1.00, 30.00, 0.00, 0.00, 30.00, '2026-03-22 02:51:58'),
(23, 16, 'PG-1', '(2026-03) — Emma Sofia Pinzón Quinde', 1.00, 25.00, 0.00, 0.00, 25.00, '2026-03-22 02:51:58'),
(24, 16, 'EXT-7842', 'Kit entrenamiento 2026-2028, Pago partido Nacional — Matias Ezequiel Pinzon Quinde', 1.00, 27.00, 0.00, 0.00, 27.00, '2026-03-22 02:51:58'),
(25, 16, 'EXT-7772', 'Pensión Febrero / 2026, Pago Febrero — Matias Ezequiel Pinzon Quinde', 1.00, 10.00, 0.00, 0.00, 10.00, '2026-03-22 02:51:58'),
(26, 16, 'EXT-7660', '— — Matias Ezequiel Pinzon Quinde', 1.00, 7.00, 0.00, 0.00, 7.00, '2026-03-22 02:51:58'),
(27, 16, 'EXT-7570', 'Pensión Enero / 2026, Pago Enero — Matias Ezequiel Pinzon Quinde', 1.00, 30.00, 0.00, 0.00, 30.00, '2026-03-22 02:51:58'),
(28, 16, 'EXT-7241', 'Pensión Diciembre / 2025, Pago Diciembre — Matias Ezequiel Pinzon Quinde', 1.00, 30.00, 0.00, 0.00, 30.00, '2026-03-22 02:51:58'),
(29, 17, 'EXT-7842', 'Kit entrenamiento 2026-2028, Pago partido Nacional — Matias Ezequiel Pinzon Quinde', 1.00, 27.00, 0.00, 0.00, 27.00, '2026-03-23 02:08:17'),
(30, 17, 'EXT-7660', 'Pago inscripción torneo Cup Kids Matias Ezequiel Pinzon Quinde', 1.00, 7.00, 0.00, 15.00, 8.05, '2026-03-23 02:08:17'),
(31, 18, 'EXT-7772', 'Pensión Febrero / 2026, Pago Febrero — Matias Ezequiel Pinzon Quinde', 1.00, 10.00, 0.00, 0.00, 10.00, '2026-03-24 20:08:39'),
(32, 18, 'EXT-7660', '— — Matias Ezequiel Pinzon Quinde', 1.00, 7.00, 0.00, 0.00, 7.00, '2026-03-24 20:08:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturacion_pagos`
--

DROP TABLE IF EXISTS `facturacion_pagos`;
CREATE TABLE IF NOT EXISTS `facturacion_pagos` (
  `pag_id` int NOT NULL AUTO_INCREMENT,
  `pag_factura_id` int NOT NULL,
  `pag_usuario_id` int NOT NULL,
  `pag_monto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pag_forma_pago_id` int NOT NULL,
  `pag_referencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pag_fecha` date NOT NULL,
  `pag_estado` enum('CONFIRMADO','ANULADO') COLLATE utf8mb4_unicode_ci DEFAULT 'CONFIRMADO',
  `pag_observaciones` text COLLATE utf8mb4_unicode_ci,
  `pag_fecha_anulacion` datetime DEFAULT NULL,
  `pag_fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pag_id`),
  KEY `idx_pag_factura` (`pag_factura_id`),
  KEY `fk_pag_forma_pago` (`pag_forma_pago_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `facturacion_pagos`
--

INSERT INTO `facturacion_pagos` (`pag_id`, `pag_factura_id`, `pag_usuario_id`, `pag_monto`, `pag_forma_pago_id`, `pag_referencia`, `pag_fecha`, `pag_estado`, `pag_observaciones`, `pag_fecha_anulacion`, `pag_fecha_creacion`) VALUES
(1, 1, 1, 81.53, 1, '', '2026-03-15', 'CONFIRMADO', '', NULL, '2026-03-16 02:01:09'),
(2, 2, 1, 34.50, 1, '', '2026-03-15', 'CONFIRMADO', '', NULL, '2026-03-16 02:02:09'),
(3, 4, 1, 166.75, 1, '', '2026-03-15', 'CONFIRMADO', '', NULL, '2026-03-16 02:13:18'),
(4, 3, 1, 52.65, 1, '', '2026-03-15', 'CONFIRMADO', '', NULL, '2026-03-16 02:13:57'),
(5, 5, 1, 8.05, 1, '', '2026-03-15', 'CONFIRMADO', '', NULL, '2026-03-16 04:36:04'),
(6, 6, 1, 28.75, 1, NULL, '2026-03-16', 'CONFIRMADO', NULL, NULL, '2026-03-16 13:54:15'),
(7, 7, 1, 46.55, 1, NULL, '2026-03-16', 'CONFIRMADO', NULL, NULL, '2026-03-16 13:57:52'),
(8, 8, 1, 40.25, 1, NULL, '2026-03-16', 'CONFIRMADO', NULL, NULL, '2026-03-16 14:18:31'),
(9, 9, 1, 34.50, 1, NULL, '2026-03-16', 'CONFIRMADO', NULL, NULL, '2026-03-16 14:28:29'),
(10, 10, 1, 8.63, 1, NULL, '2026-03-16', 'CONFIRMADO', NULL, NULL, '2026-03-16 15:10:02'),
(11, 11, 1, 40.25, 1, NULL, '2026-03-16', 'CONFIRMADO', NULL, NULL, '2026-03-16 21:35:05'),
(12, 12, 1, 25.30, 7, NULL, '2026-03-16', 'CONFIRMADO', NULL, NULL, '2026-03-17 02:21:21'),
(13, 13, 1, 40.25, 1, NULL, '2026-03-16', 'CONFIRMADO', NULL, NULL, '2026-03-17 03:40:32'),
(14, 14, 1, 114.65, 7, NULL, '2026-03-19', 'CONFIRMADO', NULL, NULL, '2026-03-19 20:14:03'),
(15, 15, 1, 74.50, 1, NULL, '2026-03-19', 'CONFIRMADO', NULL, NULL, '2026-03-19 20:31:24'),
(16, 16, 1, 159.00, 7, NULL, '2026-03-21', 'CONFIRMADO', NULL, NULL, '2026-03-22 02:51:58'),
(17, 17, 1, 35.05, 1, NULL, '2026-03-22', 'CONFIRMADO', NULL, NULL, '2026-03-23 02:08:17'),
(18, 18, 1, 17.00, 7, NULL, '2026-03-24', 'CONFIRMADO', NULL, NULL, '2026-03-24 20:08:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturacion_rubros`
--

DROP TABLE IF EXISTS `facturacion_rubros`;
CREATE TABLE IF NOT EXISTS `facturacion_rubros` (
  `rub_id` int NOT NULL AUTO_INCREMENT,
  `rub_tenant_id` int NOT NULL,
  `rub_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'C├│digo corto para la l├¡nea de factura (ej: MENS, MAT)',
  `rub_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre del rubro/concepto',
  `rub_descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripci├│n ampliada opcional',
  `rub_aplica_iva` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = aplica IVA, 0 = exento de IVA',
  `rub_porcentaje_iva` decimal(5,2) NOT NULL DEFAULT '15.00' COMMENT 'Porcentaje IVA vigente: 0, 5, 12, 15',
  `rub_estado` enum('ACTIVO','INACTIVO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `rub_fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rub_id`),
  KEY `idx_rub_tenant` (`rub_tenant_id`),
  KEY `idx_rub_tenant_estado` (`rub_tenant_id`,`rub_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rubros de facturaci├│n con configuraci├│n de IVA por tenant';

--
-- Volcado de datos para la tabla `facturacion_rubros`
--

INSERT INTO `facturacion_rubros` (`rub_id`, `rub_tenant_id`, `rub_codigo`, `rub_nombre`, `rub_descripcion`, `rub_aplica_iva`, `rub_porcentaje_iva`, `rub_estado`, `rub_fecha_creacion`) VALUES
(1, 2, 'MENS', 'Mensualidad', NULL, 1, 15.00, 'ACTIVO', '2026-03-19 14:09:11'),
(2, 1, 'RPE', 'Mensualidad', NULL, 1, 0.00, 'ACTIVO', '2026-03-19 14:09:11'),
(3, 2, 'MAT', 'Matr├¡cula', NULL, 1, 15.00, 'ACTIVO', '2026-03-19 14:09:11'),
(4, 1, 'RMA', 'Matrícula', NULL, 0, 0.00, 'INACTIVO', '2026-03-19 14:09:11'),
(5, 2, 'INSCR', 'Inscripci├│n', NULL, 1, 15.00, 'ACTIVO', '2026-03-19 14:09:11'),
(6, 1, 'RIN', 'Inscripción', NULL, 0, 0.00, 'ACTIVO', '2026-03-19 14:09:11'),
(7, 2, 'UNIF', 'Uniforme / Equipamiento', NULL, 1, 15.00, 'ACTIVO', '2026-03-19 14:09:11'),
(8, 1, 'RNU', 'Uniforme / Equipamiento', '2026', 1, 15.00, 'ACTIVO', '2026-03-19 14:09:11'),
(9, 2, 'CERT', 'Certificado / Diploma', NULL, 0, 0.00, 'ACTIVO', '2026-03-19 14:09:11'),
(10, 1, 'RCT', 'Certificado / Diploma', NULL, 0, 0.00, 'INACTIVO', '2026-03-19 14:09:11'),
(11, 2, 'EVENTO', 'Evento / Torneo', NULL, 1, 15.00, 'ACTIVO', '2026-03-19 14:09:11'),
(12, 1, 'RPC', 'Evento / Torneo', NULL, 0, 0.00, 'ACTIVO', '2026-03-19 14:09:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturacion_secuenciales`
--

DROP TABLE IF EXISTS `facturacion_secuenciales`;
CREATE TABLE IF NOT EXISTS `facturacion_secuenciales` (
  `sec_id` int NOT NULL AUTO_INCREMENT,
  `sec_tenant_id` int NOT NULL,
  `sec_tipo_comprobante` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sec_establecimiento` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sec_punto_emision` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sec_siguiente` int NOT NULL DEFAULT '1',
  `sec_fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sec_id`),
  UNIQUE KEY `idx_secuencial` (`sec_tenant_id`,`sec_tipo_comprobante`,`sec_establecimiento`,`sec_punto_emision`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `facturacion_secuenciales`
--

INSERT INTO `facturacion_secuenciales` (`sec_id`, `sec_tenant_id`, `sec_tipo_comprobante`, `sec_establecimiento`, `sec_punto_emision`, `sec_siguiente`, `sec_fecha_creacion`) VALUES
(1, 1, '01', '001', '001', 8, '2026-03-12 22:37:12'),
(8, 1, '01', '002', '001', 12, '2026-03-16 14:18:31');

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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Facturas electrónicas emitidas al SRI';

--
-- Volcado de datos para la tabla `facturas_electronicas`
--

INSERT INTO `facturas_electronicas` (`fac_id`, `fac_tenant_id`, `fac_factura_id`, `fac_clave_acceso`, `fac_tipo_comprobante`, `fac_establecimiento`, `fac_punto_emision`, `fac_secuencial`, `fac_fecha_emision`, `fac_cliente_id`, `fac_cliente_tipo_identificacion`, `fac_cliente_identificacion`, `fac_cliente_razon_social`, `fac_cliente_direccion`, `fac_cliente_email`, `fac_cliente_telefono`, `fac_subtotal_iva`, `fac_subtotal_0`, `fac_subtotal_no_objeto`, `fac_subtotal_exento`, `fac_subtotal`, `fac_descuento`, `fac_iva`, `fac_ice`, `fac_irbpnr`, `fac_propina`, `fac_total`, `fac_estado_sri`, `fac_ambiente`, `fac_tipo_emision`, `fac_xml_generado`, `fac_xml_firmado`, `fac_xml_autorizado`, `fac_numero_autorizacion`, `fac_fecha_autorizacion`, `fac_mensaje_error`, `fac_intentos_envio`, `fac_ultimo_intento`, `fac_observaciones`, `fac_created_at`, `fac_updated_at`, `created_by`) VALUES
(1, 1, 2, '1403202601110401528200110010010000000014537153714', '01', '001', '001', '000000001', '2026-03-14', 2, '05', 'ENC::vvqBFoPR6E2Vwsl', 'Freddy Bolívar Pinzón Olmedo', 'ENC::QyuRs8cnpSsOx6n2XysYGKHn8+a5PuWZaDuoXSg+d08oS1WFwXfeLRxHtf1B0lYdWkTVNYABmlWuBeTFUhPuaA==', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut', NULL, 0.00, 30.00, 0.00, 0.00, 30.00, 0.00, 0.00, 0.00, 0.00, 0.00, 30.00, 'ERROR', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1403202601110401528200110010010000000014537153714.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1403202601110401528200110010010000000014537153714.xml', NULL, NULL, NULL, 'Error desconocido del SRI', 0, NULL, NULL, '2026-03-14 16:54:10', '2026-03-16 21:10:57', 1),
(2, 1, 2, '1403202601110401528200110010010000000028916258818', '01', '001', '001', '000000002', '2026-03-14', 2, '05', 'ENC::LJw+MubpKJmiI7o', 'Freddy Bolívar Pinzón Olmedo', 'ENC::pmqCBFOibdw1z1wv5zA3bA+RzG8KHdTPYCaPSOSAI4g1x6ciLqnZVJS2N8Ka/rMqFatQnvtzTdpT4PySQ3VuGg==', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut', NULL, 0.00, 30.00, 0.00, 0.00, 30.00, 0.00, 0.00, 0.00, 0.00, 0.00, 30.00, 'ERROR', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1403202601110401528200110010010000000028916258818.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1403202601110401528200110010010000000028916258818.xml', NULL, NULL, NULL, 'Error desconocido del SRI', 0, NULL, NULL, '2026-03-14 16:54:21', '2026-03-16 21:10:57', 1),
(3, 1, 2, '1403202601110401528200110010010000000030946771212', '01', '001', '001', '000000003', '2026-03-14', 2, '05', 'ENC::SZzMAcf2RAQRJwU', 'Freddy Bolívar Pinzón Olmedo', 'ENC::Fj6dIVxUoN1WLMsUQKmMe1o3icXShv4XaN7c3FeeFXlOSXCF3wP0qVrhW0EkymTtG5am4pZ0lnt/gWNO8FKkmw==', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut', NULL, 30.00, 0.00, 0.00, 0.00, 30.00, 0.00, 4.50, 0.00, 0.00, 0.00, 34.50, 'ERROR', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1403202601110401528200110010010000000030946771212.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1403202601110401528200110010010000000030946771212.xml', NULL, NULL, NULL, 'Error desconocido del SRI', 0, NULL, NULL, '2026-03-14 16:57:59', '2026-03-16 21:10:57', 1),
(4, 1, 2, '1403202601110401528200110010010000000043758937213', '01', '001', '001', '000000004', '2026-03-14', 2, '05', 'ENC::rrr3jT+U/NP0jY1', 'Freddy Bolívar Pinzón Olmedo', 'ENC::W38Zcf1A06FMByfufYm/Noa9rB10oDfVEtthehosD0gjnJ5KGqBVgkVeJBi9O4NevXpcFJaKBP2nwnng2+w67A==', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut', NULL, 30.00, 0.00, 0.00, 0.00, 30.00, 0.00, 4.50, 0.00, 0.00, 0.00, 34.50, 'ERROR', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1403202601110401528200110010010000000043758937213.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1403202601110401528200110010010000000043758937213.xml', NULL, NULL, NULL, 'Error desconocido del SRI', 0, NULL, NULL, '2026-03-14 16:58:45', '2026-03-16 21:10:57', 1),
(5, 1, 2, '1403202601110401528200110010010000000058125280311', '01', '001', '001', '000000005', '2026-03-14', 2, '05', 'ENC::UOfaThq31kzGkXN', 'Freddy Bolívar Pinzón Olmedo', 'ENC::ZOrzNA0dy9g80Ysx9SmFi6bVLsmSuHKq+BHPpE/aG4zdB4IzEP4TtXXAnXkBkv4cZWDUsM9A/f4jH2eBoPICdQ==', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut', NULL, 30.00, 0.00, 0.00, 0.00, 30.00, 0.00, 4.50, 0.00, 0.00, 0.00, 34.50, 'ERROR', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1403202601110401528200110010010000000058125280311.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1403202601110401528200110010010000000058125280311.xml', NULL, NULL, NULL, 'ARCHIVO NO CUMPLE ESTRUCTURA XML: No existe un contribuyente registrado con el RUC 0990000000001. : ', 0, NULL, NULL, '2026-03-14 17:01:56', '2026-03-16 21:10:57', 1),
(6, 1, 2, '1403202601110401528200110010010000000066287730915', '01', '001', '001', '000000006', '2026-03-14', 2, '05', 'ENC::pCR1Zz+7Bge8lCf', 'Freddy Bolívar Pinzón Olmedo', 'ENC::pBf4Rz2lZfoFd0LqaHYbEM+pmnfZELCBUBPC2m1yqgqSZJwOfiDC16otQLY2ZE9Oqk3rWp59+Lyforn0TqZdOQ==', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut', NULL, 30.00, 0.00, 0.00, 0.00, 30.00, 0.00, 4.50, 0.00, 0.00, 0.00, 34.50, 'ERROR', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1403202601110401528200110010010000000066287730915.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1403202601110401528200110010010000000066287730915.xml', NULL, NULL, NULL, 'CLAVE DE ACCESO EN PROCESAMIENTO : La clave de acceso 1403202601110401528200110010010000000066287730915  esta en procesamiento VALOR DEVUELTO POR EL PROCEDIMIENTO: SI. : ', 0, NULL, NULL, '2026-03-14 17:05:46', '2026-03-16 21:10:57', 1),
(7, 1, 2, '1403202601110401528200110010010000000075449402517', '01', '001', '001', '000000007', '2026-03-14', 2, '05', 'ENC::zDqBaaULiVtDZoD', 'Freddy Bolívar Pinzón Olmedo', 'ENC::7qXRHOBlTqvte6hd+zBjyyak6f7oIZ2RniDxJQt4gVMUODNNhB3Dfosdqlv9Xga1Q06A0WN9WVScuxsMJirIyg==', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut', NULL, 30.00, 0.00, 0.00, 0.00, 30.00, 0.00, 4.50, 0.00, 0.00, 0.00, 34.50, 'ENVIADA', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1403202601110401528200110010010000000075449402517.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1403202601110401528200110010010000000075449402517.xml', NULL, NULL, NULL, 'CLAVE DE ACCESO EN PROCESAMIENTO : La clave de acceso 1403202601110401528200110010010000000075449402517  esta en procesamiento VALOR DEVUELTO POR EL PROCEDIMIENTO: SI. : ', 0, NULL, NULL, '2026-03-14 17:19:42', '2026-03-16 21:10:57', 1),
(8, 1, 2, '1403202601110401528200110010010000000083330340915', '01', '001', '001', '000000008', '2026-03-14', 2, '05', 'ENC::v44dYf2Vnwxy+zN', 'Freddy Bolívar Pinzón Olmedo', 'ENC::94AWO8wkxgOjXLbS1TUoO+nCYU0f7tW1Ndt655ZrME0r/NP+QEnt7WuXpb91HyMiQ9xzh4yVFPGhZZpOj4RhPg==', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut', NULL, 30.00, 0.00, 0.00, 0.00, 30.00, 0.00, 4.50, 0.00, 0.00, 0.00, 34.50, 'ENVIADA', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1403202601110401528200110010010000000083330340915.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1403202601110401528200110010010000000083330340915.xml', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-14 17:22:59', '2026-03-16 21:10:57', 1),
(9, 1, 3, '1403202601110401528200110010010000000095964965711', '01', '001', '001', '000000009', '2026-03-14', 2, '05', 'ENC::2DzgojcLliaE2d9', 'Freddy Bolívar Pinzón Olmedo', 'ENC::6gKezCFuxVD0iT5WXQ2IvWK3RokzS7jhDwUcZrayZgBkjWtiQPC1iii3dUBPDRbkU7xkYfBf7IcZzNr5fN5jxg==', 'ENC::rHtZbCDxac0B9U8MdlPgvRYGwQlZyM+VrzMob55z5P2+s3aJJMeKyAkUw0tMK4bB', NULL, 45.78, 0.00, 0.00, 0.00, 45.78, 0.00, 6.87, 0.00, 0.00, 0.00, 52.65, 'ENVIADA', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1403202601110401528200110010010000000095964965711.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1403202601110401528200110010010000000095964965711.xml', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-14 18:27:57', '2026-03-16 21:10:57', 1),
(10, 1, 4, '1403202601110401528200110010010000000108661786010', '01', '001', '001', '000000010', '2026-03-14', 2, '05', 'ENC::Iq+QQ6ufq4H8ur6', 'Freddy Bolívar Pinzón Olmedo', 'ENC::V3P7itlJjeyNLRVwVHToQYP0S27cfRI7CnnYFR5tMegrFw20twT9p/qUc7dAT0lQpQmf9/pb9RvppxvQmn+Q9w==', 'ENC::8lGWFiNb8/wsAF2YBWaF9M27cTYgwltQt1FcQgPcRIHNkxyw1q3szwPDLzfQqHP2', NULL, 145.00, 0.00, 0.00, 0.00, 145.00, 0.00, 21.75, 0.00, 0.00, 0.00, 166.75, 'ENVIADA', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1403202601110401528200110010010000000108661786010.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1403202601110401528200110010010000000108661786010.xml', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-15 01:07:49', '2026-03-16 21:10:57', 1),
(11, 1, 1, '1503202601110401528200110010010000000117979286819', '01', '001', '001', '000000011', '2026-03-15', 2, '05', 'ENC::QTx6ZVQJOsIWzEZ', 'Freddy Bolívar Pinzón Olmedo', 'ENC::a3Ucl4LrE+Jxh1RdfyAhpraE/5eKjMsXpndYp5zjcRCIZOr4Csc0a8RH0TJz/cC6Y7rZbp4BWBE9mSP8ErSDqw==', 'ENC::OjJxtHSxQKDC/eEfLdEcIvm8OCIfWmwMymnQo2uLZvUqSB1LsZoRnosE6MQmzV0H', NULL, 70.90, 0.00, 0.00, 0.00, 70.90, 0.00, 10.64, 0.00, 0.00, 0.00, 81.54, 'ENVIADA', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1503202601110401528200110010010000000117979286819.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1503202601110401528200110010010000000117979286819.xml', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-16 01:57:29', '2026-03-16 21:10:57', 1),
(12, 1, 5, '1503202601110401528200110010010000000122052429012', '01', '001', '001', '000000012', '2026-03-15', 2, '05', 'ENC::ujPBDvRel8P1JHP', 'Freddy Bolívar Pinzón Olmedo', 'ENC::7xcSW1330u8UIZHjl3aHBKydXgL3Zlm5hVJSvr1LGUywk9zmUTMQdz7HbH9QI78iqpGuM9rN9OWZaS2awAf9Tw==', 'ENC::38RvPAcprSHYhk/+2ItWeBgU00GxLHjRDA6RCqYhTucCwZP9Jgp7oOoCv4u1kfVa', NULL, 7.00, 0.00, 0.00, 0.00, 7.00, 0.00, 1.05, 0.00, 0.00, 0.00, 8.05, 'ENVIADA', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1503202601110401528200110010010000000122052429012.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1503202601110401528200110010010000000122052429012.xml', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-16 04:32:02', '2026-03-16 21:10:57', 1),
(13, 1, 7, '1603202601110401528200110010010000000138929002013', '01', '001', '001', '000000013', '2026-03-16', 2, '05', 'ENC::huDMJojUwC7B6tN', 'Freddy Bolívar Pinzón Olmedo', 'ENC::s8HRt7VgBY6FARXjQDd5b7MPefw5W3LILhEFzF2aRGToFbAzmOOnaF1TPKSw2AIsMSsmIhKLLJpVkEuMrvAsog==', 'ENC::/3GmDmziGSkmV0amxB39MeNwvVORCjoETX+LVSVrrK9O8EWhQWBg50DSFq6N+Ewc', NULL, 40.48, 0.00, 0.00, 0.00, 40.48, 0.00, 6.07, 0.00, 0.00, 0.00, 46.55, 'ERROR', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1603202601110401528200110010010000000138929002013.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1603202601110401528200110010010000000138929002013.xml', NULL, NULL, NULL, 'ERROR ESTABLECIMIENTO CERRADO: El establecimiento 001 está cerrado. : ', 0, NULL, NULL, '2026-03-16 14:04:03', '2026-03-16 21:10:57', 1),
(14, 1, 7, '1603202601110401528200110020010000000012568964917', '01', '002', '001', '000000001', '2026-03-16', 2, '05', 'ENC::IARmnOT3pTtyglu', 'Freddy Bolívar Pinzón Olmedo', 'ENC::g3N2cVIKdLCsCNpJisPsfOvqKfJB9x2kRYElnHUYJ+SVfjdS2Eb6CuNDPnccXj0b6f8XFLHm1BUUlHKf1vwSdA==', 'ENC::zkPRwKTnetz/wCQGudvlbWSbHHLNGJdjpO6F77wZmSjdJQgm5HzjB5PKqGW3USUl', NULL, 40.48, 0.00, 0.00, 0.00, 40.48, 0.00, 6.07, 0.00, 0.00, 0.00, 46.55, 'ERROR', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1603202601110401528200110020010000000012568964917.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1603202601110401528200110020010000000012568964917.xml', NULL, NULL, NULL, 'FIRMA INVALIDA: La firma es invalida [Firma inválida (firma y/o certificados alterados)]. : ', 0, NULL, NULL, '2026-03-16 14:17:35', '2026-03-16 21:10:57', 1),
(15, 1, 8, '1603202601110401528200110020010000000022588018215', '01', '002', '001', '000000002', '2026-03-16', 2, '05', 'ENC::UPfXAOtnYvZQATE', 'Freddy Bolívar Pinzón Olmedo', 'ENC::60HwAVvAbaAmbDyDBcH164f20iAFe6p8adXVcPeGSTLBMHi/ITP7KQV5gI6TN2tTaeD2tiG/X9Ay8EBkjP4MlA==', 'ENC::wZcrZjmiHubiNVZvpGEDw9kY6t9XQOhEM0qjEKbm3YTS2Tgr6cr7WrwxF0QNqWdJ', NULL, 35.00, 0.00, 0.00, 0.00, 35.00, 0.00, 5.25, 0.00, 0.00, 0.00, 40.25, 'ERROR', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1603202601110401528200110020010000000022588018215.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1603202601110401528200110020010000000022588018215.xml', NULL, NULL, NULL, 'FIRMA INVALIDA: La firma es invalida [Firma inválida (firma y/o certificados alterados)]. : ', 0, NULL, NULL, '2026-03-16 14:18:39', '2026-03-16 21:10:57', 1),
(16, 1, 9, '1603202601110401528200110020010000000038162736319', '01', '002', '001', '000000003', '2026-03-16', 2, '05', 'ENC::d5OHS1TVR32KraJ', 'Freddy Bolívar Pinzón Olmedo', 'ENC::cT3BYMPUOVnfmxt7l5yJJNUn2akW12PYJoD2d+FHL7ddl70esoI3rm3Js9UuAZN+tdSjh/ObdT/gWWGH2sE1iA==', 'ENC::2lwX6SlI8R6LlEPcZnb3U+iLV5QYc3X2oIgtU2fpkuPuf2TVcj+iJJGn2DddEot8', NULL, 30.00, 0.00, 0.00, 0.00, 30.00, 0.00, 4.50, 0.00, 0.00, 0.00, 34.50, 'AUTORIZADO', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1603202601110401528200110020010000000038162736319.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1603202601110401528200110020010000000038162736319.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/autorizados/1603202601110401528200110020010000000038162736319.xml', '1603202601110401528200110020010000000038162736319', '2026-03-16 09:30:43', NULL, 0, NULL, NULL, '2026-03-16 14:28:43', '2026-03-16 21:10:57', 1),
(17, 1, 10, '1603202601110401528200110020010000000047416651016', '01', '002', '001', '000000004', '2026-03-16', 2, '05', 'ENC::sdjVyTUNylPXM/6', 'Freddy Bolívar Pinzón Olmedo', 'ENC::eqDpXnHGiz7OXrQA52XkgAiTsSl6ASgDgK299K88/iIN+y08FsaDTTnb8zG8Tpttz3r2C644KAvOrrnxpKAQSw==', 'ENC::MpzhDd0WDBCEDdE36s5GRdKT4thwItBTDKtCVdsPpT7Bh4gPzh1Sfpzydkevx7bs', NULL, 7.50, 0.00, 0.00, 0.00, 7.50, 0.00, 1.13, 0.00, 0.00, 0.00, 8.63, 'AUTORIZADO', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1603202601110401528200110020010000000047416651016.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1603202601110401528200110020010000000047416651016.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/autorizados/1603202601110401528200110020010000000047416651016.xml', '1603202601110401528200110020010000000047416651016', '2026-03-16 10:12:08', NULL, 0, NULL, NULL, '2026-03-16 15:10:08', '2026-03-16 21:10:57', 1),
(18, 1, 11, '1603202601110401528200110020010000000050853803713', '01', '002', '001', '000000005', '2026-03-16', 2, '05', 'ENC::IDq1CSxA4+KHe1Z', 'Freddy Bolívar Pinzón Olmedo', 'ENC::Ef8qwy8WxBAc5HIL1Q/hvAbAKsl8DC99OK49PU+j1R88ApFbvSbdn8/s/V1/pOd5d/YHzCWLsDMVryt7kG6pUA==', 'ENC::WR2xxIXnnw+M+rAb9tIAlp/tazzkKNhZep2s8CBxs87ATF80JsWObhJG78MYkQ+1', 'ENC::EDukDXfcS2jjWpgCku8Cnc7F8GX00W5OB/lqoEP31NA=', 35.00, 0.00, 0.00, 0.00, 35.00, 0.00, 5.25, 0.00, 0.00, 0.00, 40.25, 'AUTORIZADO', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1603202601110401528200110020010000000050853803713.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1603202601110401528200110020010000000050853803713.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/autorizados/1603202601110401528200110020010000000050853803713.xml', '1603202601110401528200110020010000000050853803713', '2026-03-16 16:37:12', NULL, 0, NULL, NULL, '2026-03-16 21:35:12', '2026-03-16 21:35:19', 1),
(19, 1, 12, '1603202601110401528200110020010000000063264854817', '01', '002', '001', '000000006', '2026-03-16', 2, '05', 'ENC::1PYZJmpeig/cgr1', 'Freddy Bolívar Pinzón Olmedo', 'ENC::23aGO1L4T/I9h5QO0p0nhi8Qe4nMOgnMmEgFj/yhD/oVR378cNtHKCApsOtcd8SS+pN/Rw+A5xcvLzo/l/bbNQ==', 'ENC::Dyue1M7XWWfCKoYLe1GitcaEoFUbqBYPxbNpbdFpKpW6BYro2/1fHPh32H5KNnLW', 'ENC::A5SfIMR4v1htLp7p3dPNKOTuGaGm1jbTj2njwiILlco=', 22.00, 0.00, 0.00, 0.00, 22.00, 0.00, 3.30, 0.00, 0.00, 0.00, 25.30, 'AUTORIZADO', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1603202601110401528200110020010000000063264854817.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1603202601110401528200110020010000000063264854817.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/autorizados/1603202601110401528200110020010000000063264854817.xml', '1603202601110401528200110020010000000063264854817', '2026-03-16 21:23:26', NULL, 0, NULL, NULL, '2026-03-17 02:21:26', '2026-03-17 02:21:32', 1),
(20, 1, 13, '1603202601110401528200110020010000000077061574811', '01', '002', '001', '000000007', '2026-03-16', 2, '05', 'ENC::FvpY1rsoNavuzz4', 'Freddy Bolívar Pinzón Olmedo', 'ENC::JfA77P/RZa1rvcBHYrl0eoX1DAYQUnLR1nUiw+4Dj286eHh6TNeSXwvJhcyfiEu9W6nZBNYCRBnM+lWs6gT3rg==', 'ENC::LO+/DhJVtC4iOLpCEd63ST1sbq/92eyowJYfpERhxdm/on2Ib3IUG+KFbOuW9KS+', 'ENC::JE25B22+GoQgiQPvRQBzK6XYP/uElnutQ95oajSvls0=', 35.00, 0.00, 0.00, 0.00, 35.00, 0.00, 5.25, 0.00, 0.00, 0.00, 40.25, 'AUTORIZADO', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1603202601110401528200110020010000000077061574811.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1603202601110401528200110020010000000077061574811.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/autorizados/1603202601110401528200110020010000000077061574811.xml', '1603202601110401528200110020010000000077061574811', '2026-03-16 22:42:39', NULL, 0, NULL, NULL, '2026-03-17 03:40:37', '2026-03-17 03:40:44', 1),
(21, 1, 14, '1903202601110401528200110020010000000085578690417', '01', '002', '001', '000000008', '2026-03-19', 4, '05', 'ENC::gePENjlXlM7fSTo', 'Freddy Bolívar Pinzón Olmedo', 'ENC::0zZULC21FwgfznSSy4vSxhUaF4ukZ5UNYnETLM9mR000NVCEkzn5TPHyT5O3on9dtSFinAveLppew874/sR1aA==', 'ENC::9+yn9rojk0ioTzebuXQA5npVVvUKThIwHsSXFwA1uv4eDjgoxfWZnNAPAJxKuF0+', 'ENC::zPmF8QccDrhGHiwhKaBfJu+Ch1Sba5UmEIHS9EI4yXc=', 25.78, 85.00, 0.00, 0.00, 110.78, 0.00, 3.87, 0.00, 0.00, 0.00, 114.65, 'AUTORIZADO', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1903202601110401528200110020010000000085578690417.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1903202601110401528200110020010000000085578690417.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/autorizados/1903202601110401528200110020010000000085578690417.xml', '1903202601110401528200110020010000000085578690417', '2026-03-19 15:16:24', NULL, 0, NULL, NULL, '2026-03-19 20:14:21', '2026-03-19 20:14:27', 1),
(22, 1, 15, '1903202601110401528200110020010000000095086392416', '01', '002', '001', '000000009', '2026-03-19', 4, '05', 'ENC::ztdYSf7RfrHS6Uc', 'Freddy Bolívar Pinzón Olmedo', 'ENC::pjYBXp/yU6nphvMWQoJwWE8DdV04BQS+rCqwBLD8v32XkH/53en1wHCilG3WayyZzYJ4cUQb1S3Q90ELIuCj6Q==', 'ENC::txGufLXpSYHpUWGPKKj3qk51ZiPqYsv26YranY7x/RJkeFxxNMO91L/j0w8EAh96', 'ENC::QA+Lcu/psyDiYbiONz2RL7ditj4jIP05+YY8mDT0miQ=', 30.00, 40.00, 0.00, 0.00, 70.00, 0.00, 4.50, 0.00, 0.00, 0.00, 74.50, 'AUTORIZADO', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/1903202601110401528200110020010000000095086392416.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/1903202601110401528200110020010000000095086392416.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/autorizados/1903202601110401528200110020010000000095086392416.xml', '1903202601110401528200110020010000000095086392416', '2026-03-19 15:33:32', NULL, 0, NULL, NULL, '2026-03-19 20:31:30', '2026-03-19 20:31:35', 1),
(23, 1, 16, '2103202601110401528200110020010000000102490261310', '01', '002', '001', '000000010', '2026-03-21', 4, '05', 'ENC::NUMJxu/T6NKUgiB', 'Freddy Bolívar Pinzón Olmedo', 'ENC::hpOe/aBcyDstEo8sgoU+QjBMR5/vYJbodkIscwroyQMr98nL6zUFcR6xh8z+UwZ4ikA6B4pnYtgoqYF96HpRJQ==', 'ENC::LGjjRFSrR1Ef9gyzq7sTsSYbjdE8T4r0xzVCGKNY+Akcci2oFEOaNUAJERiLcdjG', 'ENC::h6+DOmKOBLUkl/eKO5j0PskwoGWEZI4e8TNP8L0qVYY=', 0.00, 159.00, 0.00, 0.00, 159.00, 0.00, 0.00, 0.00, 0.00, 0.00, 159.00, 'AUTORIZADO', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/2103202601110401528200110020010000000102490261310.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/2103202601110401528200110020010000000102490261310.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/autorizados/2103202601110401528200110020010000000102490261310.xml', '2103202601110401528200110020010000000102490261310', '2026-03-21 21:54:12', NULL, 0, NULL, NULL, '2026-03-22 02:52:07', '2026-03-22 02:52:13', 1),
(24, 1, 17, '2203202601110401528200110020010000000119757026817', '01', '002', '001', '000000011', '2026-03-22', 4, '05', 'ENC::uD5a3wCV1RYcUcf', 'Freddy Bolívar Pinzón Olmedo', 'ENC::hlAU8eKuyIKktx5fcdGfpI8i8Cfzz8k6/SsVGMu6+T2Xkrtr8hQJvA2GlEfyypGcGJY75cqAicYVn1HytIdXxg==', 'ENC::Q5uoSAVcmax2bq2Ie7K+I7zQ0H2CY9Buz1NbBs3OPSekPOzTHbm5PvCoVwMOA1nV', 'ENC::vLOrJ2H0SOKp3CrDDaYwwnOVZMjAtmrT2B7yyzNE1k4=', 7.00, 27.00, 0.00, 0.00, 34.00, 0.00, 1.05, 0.00, 0.00, 0.00, 35.05, 'AUTORIZADO', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/2203202601110401528200110020010000000119757026817.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/2203202601110401528200110020010000000119757026817.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/autorizados/2203202601110401528200110020010000000119757026817.xml', '2203202601110401528200110020010000000119757026817', '2026-03-22 21:11:21', NULL, 0, NULL, NULL, '2026-03-23 02:09:15', '2026-03-23 02:09:21', 1),
(25, 1, 18, '2403202601110401528200110020010000000125041059810', '01', '002', '001', '000000012', '2026-03-24', 4, '05', 'ENC::HX8aQ0hfU3R+R6E', 'Freddy Bolívar Pinzón Olmedo', 'ENC::QFkooMrcg24Chind6ZLO9SA5ApoSHbA+/Bpr56KWFRPeUgwqNMDq0O/LJwfpaaxlvaWmnXnXH6HKY0zsLkVlaQ==', 'ENC::87jiqrOn0AOZt9GH6iLO7qah/Nir5Hppd+2ntfIVbWGNTHCCyHt+HVd5mNJ5wssv', 'ENC::TEHUPncJ2pBM3jbeYrNbkacDnTjOlU9GWw1rW2E2VAU=', 0.00, 17.00, 0.00, 0.00, 17.00, 0.00, 0.00, 0.00, 0.00, 0.00, 17.00, 'ENVIADA', '1', '1', 'C:\\wamp64\\www\\digisports/storage/sri/xml/generados/2403202601110401528200110020010000000125041059810.xml', 'C:\\wamp64\\www\\digisports/storage/sri/xml/firmados/2403202601110401528200110020010000000125041059810.xml', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-24 20:08:54', '2026-03-24 20:09:21', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalles de facturas electrónicas';

--
-- Volcado de datos para la tabla `facturas_electronicas_detalle`
--

INSERT INTO `facturas_electronicas_detalle` (`det_id`, `det_factura_electronica_id`, `det_codigo_principal`, `det_codigo_auxiliar`, `det_descripcion`, `det_cantidad`, `det_precio_unitario`, `det_descuento`, `det_precio_total_sin_impuesto`, `det_producto_id`, `det_servicio_id`, `det_instalacion_id`, `det_reserva_id`, `det_created_at`) VALUES
(1, 1, 'SERV001', NULL, 'Pago de mensualidad | MAR26', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-14 16:54:10'),
(2, 2, 'SERV001', NULL, 'Pago de mensualidad | MAR26', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-14 16:54:21'),
(3, 3, 'SERV001', NULL, 'Pago de mensualidad | MAR26', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-14 16:57:59'),
(4, 4, 'SERV001', NULL, 'Pago de mensualidad | MAR26', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-14 16:58:45'),
(5, 5, 'SERV001', NULL, 'Pago de mensualidad | MAR26', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-14 17:01:56'),
(6, 6, 'SERV001', NULL, 'Pago de mensualidad | MAR26', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-14 17:05:46'),
(7, 7, 'SERV001', NULL, 'Pago de mensualidad | MAR26', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-14 17:19:42'),
(8, 8, 'SERV001', NULL, 'Pago de mensualidad | MAR26', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-14 17:22:59'),
(9, 9, 'SERV02', NULL, 'Pago de uniformes | MAR-26', 1.000000, 50.780000, 5.00, 45.78, NULL, NULL, NULL, NULL, '2026-03-14 18:27:57'),
(10, 10, 'SERV02', NULL, 'Torneo | MAR-26', 1.000000, 145.000000, 0.00, 145.00, NULL, NULL, NULL, NULL, '2026-03-15 01:07:49'),
(11, 11, 'SER01', NULL, 'PENSION MES DE MARZO | MAR-26', 1.000000, 30.000000, 5.00, 25.00, NULL, NULL, NULL, NULL, '2026-03-16 01:57:29'),
(12, 11, 'SER02', NULL, 'uNIFORMES 2026 | MAR-26', 1.000000, 45.900000, 0.00, 45.90, NULL, NULL, NULL, NULL, '2026-03-16 01:57:29'),
(13, 12, 'SER01', NULL, 'Pago torneo Ecuador cup-kid | MAR-26', 1.000000, 7.000000, 0.00, 7.00, NULL, NULL, NULL, NULL, '2026-03-16 04:32:02'),
(14, 13, 'SER03', NULL, 'Pago de nuevo uniforme de competencia | MAR-26', 1.000000, 50.480000, 10.00, 40.48, NULL, NULL, NULL, NULL, '2026-03-16 14:04:03'),
(15, 14, 'SER03', NULL, 'Pago de nuevo uniforme de competencia | MAR-26', 1.000000, 50.480000, 10.00, 40.48, NULL, NULL, NULL, NULL, '2026-03-16 14:17:35'),
(16, 15, '001', NULL, 'pAGO MENSUALIDAD | MAR-26', 1.000000, 35.000000, 0.00, 35.00, NULL, NULL, NULL, NULL, '2026-03-16 14:18:39'),
(17, 16, '01', NULL, 'Pago mensualidad, mes de marzo | MAR-26', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-16 14:28:43'),
(18, 17, '002', NULL, 'Inscripción torneo Ecuador  Cup Kids | MAR-26', 1.000000, 7.500000, 0.00, 7.50, NULL, NULL, NULL, NULL, '2026-03-16 15:10:08'),
(19, 18, 'S001', NULL, 'Pago de Piscina | MAR-26', 1.000000, 35.000000, 0.00, 35.00, NULL, NULL, NULL, NULL, '2026-03-16 21:35:12'),
(20, 19, 'S01', NULL, 'Pago copa cup kids | MAR26', 1.000000, 22.000000, 0.00, 22.00, NULL, NULL, NULL, NULL, '2026-03-17 02:21:26'),
(21, 20, 'SR1', NULL, 'Pago pension febrero | MAR-26', 1.000000, 35.000000, 0.00, 35.00, NULL, NULL, NULL, NULL, '2026-03-17 03:40:37'),
(22, 21, 'PG-3', NULL, 'MENSUALIDAD 2026-03 (2026-03) — Emma Sofia Pinzón Quinde', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-19 20:14:21'),
(23, 21, 'PG-2', NULL, '(2026-01) — Matias Pinzon', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-19 20:14:21'),
(24, 21, 'PG-1', NULL, '(2026-03) — Emma Sofia Pinzón Quinde', 1.000000, 25.000000, 0.00, 25.00, NULL, NULL, NULL, NULL, '2026-03-19 20:14:21'),
(25, 21, 'PCA', NULL, 'Pago de Cancha | MAR-26', 1.000000, 25.780000, 0.00, 25.78, NULL, NULL, NULL, NULL, '2026-03-19 20:14:21'),
(26, 22, 'RCT', NULL, 'Certificado / Diploma', 1.000000, 10.000000, 0.00, 10.00, NULL, NULL, NULL, NULL, '2026-03-19 20:31:30'),
(27, 22, 'PG-3', NULL, 'MENSUALIDAD 2026-03 (2026-03) — Emma Sofia Pinzón Quinde', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-19 20:31:30'),
(28, 22, 'S01', NULL, 'Pago de alquiler e cancha', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-19 20:31:30'),
(29, 23, 'PG-2', NULL, '(2026-01) — Matias Pinzon', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-22 02:52:07'),
(30, 23, 'PG-1', NULL, '(2026-03) — Emma Sofia Pinzón Quinde', 1.000000, 25.000000, 0.00, 25.00, NULL, NULL, NULL, NULL, '2026-03-22 02:52:07'),
(31, 23, 'EXT-7842', NULL, 'Kit entrenamiento 2026-2028, Pago partido Nacional — Matias Ezequiel Pinzon Quinde', 1.000000, 27.000000, 0.00, 27.00, NULL, NULL, NULL, NULL, '2026-03-22 02:52:07'),
(32, 23, 'EXT-7772', NULL, 'Pensión Febrero / 2026, Pago Febrero — Matias Ezequiel Pinzon Quinde', 1.000000, 10.000000, 0.00, 10.00, NULL, NULL, NULL, NULL, '2026-03-22 02:52:07'),
(33, 23, 'EXT-7660', NULL, '— — Matias Ezequiel Pinzon Quinde', 1.000000, 7.000000, 0.00, 7.00, NULL, NULL, NULL, NULL, '2026-03-22 02:52:07'),
(34, 23, 'EXT-7570', NULL, 'Pensión Enero / 2026, Pago Enero — Matias Ezequiel Pinzon Quinde', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-22 02:52:07'),
(35, 23, 'EXT-7241', NULL, 'Pensión Diciembre / 2025, Pago Diciembre — Matias Ezequiel Pinzon Quinde', 1.000000, 30.000000, 0.00, 30.00, NULL, NULL, NULL, NULL, '2026-03-22 02:52:07'),
(36, 24, 'EXT-7842', NULL, 'Kit entrenamiento 2026-2028, Pago partido Nacional — Matias Ezequiel Pinzon Quinde', 1.000000, 27.000000, 0.00, 27.00, NULL, NULL, NULL, NULL, '2026-03-23 02:09:15'),
(37, 24, 'EXT-7660', NULL, 'Pago inscripción torneo Cup Kids Matias Ezequiel Pinzon Quinde', 1.000000, 7.000000, 0.00, 7.00, NULL, NULL, NULL, NULL, '2026-03-23 02:09:15'),
(38, 25, 'EXT-7772', NULL, 'Pensión Febrero / 2026, Pago Febrero — Matias Ezequiel Pinzon Quinde', 1.000000, 10.000000, 0.00, 10.00, NULL, NULL, NULL, NULL, '2026-03-24 20:08:54'),
(39, 25, 'EXT-7660', NULL, '— — Matias Ezequiel Pinzon Quinde', 1.000000, 7.000000, 0.00, 7.00, NULL, NULL, NULL, NULL, '2026-03-24 20:08:54');

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
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Impuestos por detalle de factura electrónica';

--
-- Volcado de datos para la tabla `facturas_electronicas_detalle_impuestos`
--

INSERT INTO `facturas_electronicas_detalle_impuestos` (`imp_id`, `imp_detalle_id`, `imp_codigo`, `imp_codigo_porcentaje`, `imp_tarifa`, `imp_base_imponible`, `imp_valor`) VALUES
(1, 1, '2', '0', 0.00, 30.00, 0.00),
(2, 2, '2', '0', 0.00, 30.00, 0.00),
(3, 3, '2', '4', 15.00, 30.00, 4.50),
(4, 4, '2', '4', 15.00, 30.00, 4.50),
(5, 5, '2', '4', 15.00, 30.00, 4.50),
(6, 6, '2', '4', 15.00, 30.00, 4.50),
(7, 7, '2', '4', 15.00, 30.00, 4.50),
(8, 8, '2', '4', 15.00, 30.00, 4.50),
(9, 9, '2', '4', 15.00, 45.78, 6.87),
(10, 10, '2', '4', 15.00, 145.00, 21.75),
(11, 11, '2', '4', 15.00, 25.00, 3.75),
(12, 12, '2', '4', 15.00, 45.90, 6.89),
(13, 13, '2', '4', 15.00, 7.00, 1.05),
(14, 14, '2', '4', 15.00, 40.48, 6.07),
(15, 15, '2', '4', 15.00, 40.48, 6.07),
(16, 16, '2', '4', 15.00, 35.00, 5.25),
(17, 17, '2', '4', 15.00, 30.00, 4.50),
(18, 18, '2', '4', 15.00, 7.50, 1.13),
(19, 19, '2', '4', 15.00, 35.00, 5.25),
(20, 20, '2', '4', 15.00, 22.00, 3.30),
(21, 21, '2', '4', 15.00, 35.00, 5.25),
(22, 22, '2', '0', 0.00, 30.00, 0.00),
(23, 23, '2', '0', 0.00, 30.00, 0.00),
(24, 24, '2', '0', 0.00, 25.00, 0.00),
(25, 25, '2', '4', 15.00, 25.78, 3.87),
(26, 26, '2', '0', 0.00, 10.00, 0.00),
(27, 27, '2', '0', 0.00, 30.00, 0.00),
(28, 28, '2', '4', 15.00, 30.00, 4.50),
(29, 29, '2', '0', 0.00, 30.00, 0.00),
(30, 30, '2', '0', 0.00, 25.00, 0.00),
(31, 31, '2', '0', 0.00, 27.00, 0.00),
(32, 32, '2', '0', 0.00, 10.00, 0.00),
(33, 33, '2', '0', 0.00, 7.00, 0.00),
(34, 34, '2', '0', 0.00, 30.00, 0.00),
(35, 35, '2', '0', 0.00, 30.00, 0.00),
(36, 36, '2', '0', 0.00, 27.00, 0.00),
(37, 37, '2', '4', 15.00, 7.00, 1.05),
(38, 38, '2', '0', 0.00, 10.00, 0.00),
(39, 39, '2', '0', 0.00, 7.00, 0.00);

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
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Información adicional de facturas electrónicas';

--
-- Volcado de datos para la tabla `facturas_electronicas_info_adicional`
--

INSERT INTO `facturas_electronicas_info_adicional` (`adi_id`, `adi_factura_electronica_id`, `adi_nombre`, `adi_valor`) VALUES
(1, 1, 'Email', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut'),
(2, 1, 'Teléfono', 'ENC::Wi4TBafq7H4H83WK3AYmhk2iJMoPnx86TbVZij7QFTM='),
(3, 2, 'Email', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut'),
(4, 2, 'Teléfono', 'ENC::Wi4TBafq7H4H83WK3AYmhk2iJMoPnx86TbVZij7QFTM='),
(5, 3, 'Email', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut'),
(6, 3, 'Teléfono', 'ENC::Wi4TBafq7H4H83WK3AYmhk2iJMoPnx86TbVZij7QFTM='),
(7, 4, 'Email', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut'),
(8, 4, 'Teléfono', 'ENC::Wi4TBafq7H4H83WK3AYmhk2iJMoPnx86TbVZij7QFTM='),
(9, 5, 'Email', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut'),
(10, 5, 'Teléfono', 'ENC::Wi4TBafq7H4H83WK3AYmhk2iJMoPnx86TbVZij7QFTM='),
(11, 6, 'Email', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut'),
(12, 6, 'Teléfono', 'ENC::Wi4TBafq7H4H83WK3AYmhk2iJMoPnx86TbVZij7QFTM='),
(13, 7, 'Email', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut'),
(14, 7, 'Teléfono', 'ENC::Wi4TBafq7H4H83WK3AYmhk2iJMoPnx86TbVZij7QFTM='),
(15, 8, 'Email', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut'),
(16, 8, 'Teléfono', 'ENC::Wi4TBafq7H4H83WK3AYmhk2iJMoPnx86TbVZij7QFTM='),
(17, 9, 'Email', 'ENC::SJIp5vZVmLPnefVHtBt2rbd5GjyfAONmjzW3MyEeoSzfeRrCgi/2dCF0RbIhB5ut'),
(18, 9, 'Teléfono', 'ENC::Wi4TBafq7H4H83WK3AYmhk2iJMoPnx86TbVZij7QFTM='),
(19, 10, 'Email', 'fbpinzon@gmail.com'),
(20, 10, 'Teléfono', 'ENC::Wi4TBafq7H4H83WK3AYmhk2iJMoPnx86TbVZij7QFTM='),
(21, 11, 'Email', 'fbpinzon@gmail.com'),
(22, 11, 'Teléfono', '0993120984'),
(23, 12, 'Email', 'fbpinzon@gmail.com'),
(24, 12, 'Teléfono', '0993120984'),
(25, 13, 'Email', 'fbpinzon@gmail.com'),
(26, 13, 'Teléfono', '0993120984'),
(27, 14, 'Email', 'fbpinzon@gmail.com'),
(28, 14, 'Teléfono', '0993120984'),
(29, 15, 'Email', 'fbpinzon@gmail.com'),
(30, 15, 'Teléfono', '0993120984'),
(31, 16, 'Email', 'fbpinzon@gmail.com'),
(32, 16, 'Teléfono', '0993120984'),
(33, 17, 'Email', 'fbpinzon@gmail.com'),
(34, 17, 'Teléfono', '0993120984'),
(35, 18, 'Email', 'fbpinzon@gmail.com'),
(36, 18, 'Teléfono', '0993120984'),
(37, 19, 'Email', 'fbpinzon@gmail.com'),
(38, 19, 'Teléfono', '0993120984'),
(39, 20, 'Email', 'fbpinzon@gmail.com'),
(40, 20, 'Teléfono', '0993120984'),
(41, 21, 'Email', 'fbpinzon@gmail.com'),
(42, 21, 'Teléfono', '0993120984'),
(43, 22, 'Email', 'fbpinzon@gmail.com'),
(44, 22, 'Teléfono', '0993120984'),
(45, 23, 'Email', 'fbpinzon@gmail.com'),
(46, 23, 'Teléfono', '0993120984'),
(47, 24, 'Email', 'fbpinzon@gmail.com'),
(48, 24, 'Teléfono', '0993120984'),
(49, 25, 'Email', 'fbpinzon@gmail.com'),
(50, 25, 'Teléfono', '0993120984');

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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Formas de pago de facturas electrónicas';

--
-- Volcado de datos para la tabla `facturas_electronicas_pagos`
--

INSERT INTO `facturas_electronicas_pagos` (`pag_id`, `pag_factura_electronica_id`, `pag_forma_pago`, `pag_total`, `pag_plazo`, `pag_unidad_tiempo`) VALUES
(1, 1, '01', 30.00, NULL, 'dias'),
(2, 2, '01', 30.00, NULL, 'dias'),
(3, 3, '01', 34.50, NULL, 'dias'),
(4, 4, '01', 34.50, NULL, 'dias'),
(5, 5, '01', 34.50, NULL, 'dias'),
(6, 6, '01', 34.50, NULL, 'dias'),
(7, 7, '01', 34.50, NULL, 'dias'),
(8, 8, '01', 34.50, NULL, 'dias'),
(9, 9, '17', 52.65, NULL, 'dias'),
(10, 10, '01', 166.75, NULL, 'dias'),
(11, 11, '20', 81.54, NULL, 'dias'),
(12, 12, '01', 8.05, NULL, 'dias'),
(13, 13, '01', 46.55, NULL, 'dias'),
(14, 14, '01', 46.55, NULL, 'dias'),
(15, 15, '01', 40.25, NULL, 'dias'),
(16, 16, '01', 34.50, NULL, 'dias'),
(17, 17, '01', 8.63, NULL, 'dias'),
(18, 18, '01', 40.25, NULL, 'dias'),
(19, 19, '20', 25.30, NULL, 'dias'),
(20, 20, '01', 40.25, NULL, 'dias'),
(21, 21, '20', 114.65, NULL, 'dias'),
(22, 22, '01', 74.50, NULL, 'dias'),
(23, 23, '20', 159.00, NULL, 'dias'),
(24, 24, '01', 35.05, NULL, 'dias'),
(25, 25, '20', 17.00, NULL, 'dias');

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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Control de secuenciales por establecimiento';

--
-- Volcado de datos para la tabla `facturas_electronicas_secuenciales`
--

INSERT INTO `facturas_electronicas_secuenciales` (`sec_id`, `sec_tenant_id`, `sec_tipo_comprobante`, `sec_establecimiento`, `sec_punto_emision`, `sec_secuencial_actual`, `sec_created_at`, `sec_updated_at`) VALUES
(1, 1, '01', '001', '001', 13, '2026-01-26 03:47:23', '2026-03-16 14:04:03'),
(17, 1, '01', '002', '001', 12, '2026-03-16 14:17:35', '2026-03-24 20:08:54');

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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_asistencia`
--

INSERT INTO `futbol_asistencia` (`fas_asistencia_id`, `fas_tenant_id`, `fas_inscripcion_id`, `fas_grupo_id`, `fas_alumno_id`, `fas_fecha`, `fas_estado`, `fas_observacion`, `fas_registrado_por`, `fas_created_at`) VALUES
(1, 1, 0, 1, 1, '2026-03-06', 'PRESENTE', NULL, NULL, '2026-03-07 02:24:47'),
(2, 1, 0, 1, 1, '2026-03-05', 'TARDANZA', NULL, NULL, '2026-03-07 02:27:27'),
(3, 1, 0, 1, 1, '2026-03-04', 'TARDANZA', NULL, NULL, '2026-03-07 04:20:49'),
(5, 1, 1, 1, 1, '2026-03-09', 'PRESENTE', NULL, 1, '2026-03-09 16:21:31'),
(6, 1, 2, 1, 2, '2026-03-09', 'TARDANZA', NULL, 1, '2026-03-09 16:21:32'),
(20, 1, 1, 1, 1, '2026-03-04', 'PRESENTE', NULL, 1, '2026-03-09 16:23:40'),
(24, 1, 2, 1, 2, '2026-03-04', 'PRESENTE', NULL, 1, '2026-03-09 16:25:39'),
(26, 1, 1, 1, 1, '2026-03-24', 'PRESENTE', NULL, 1, '2026-03-25 03:22:16'),
(30, 1, 2, 1, 2, '2026-03-24', 'TARDANZA', NULL, 1, '2026-03-25 03:22:22');

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
  `fbe_rubro_id` int DEFAULT NULL COMMENT 'FK a facturacion_rubros',
  PRIMARY KEY (`fbe_beca_id`),
  KEY `idx_fbe_tenant` (`fbe_tenant_id`,`fbe_activo`),
  KEY `idx_fbe_rubro` (`fbe_rubro_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_becas`
--

INSERT INTO `futbol_becas` (`fbe_beca_id`, `fbe_tenant_id`, `fbe_nombre`, `fbe_tipo`, `fbe_valor`, `fbe_descripcion`, `fbe_requisitos`, `fbe_cupo_maximo`, `fbe_cupo_usado`, `fbe_vigencia_inicio`, `fbe_vigencia_fin`, `fbe_aplica_matricula`, `fbe_aplica_mensualidad`, `fbe_activo`, `fbe_created_at`, `fbe_updated_at`, `fbe_rubro_id`) VALUES
(1, 1, 'Beca Deportiva 100%', 'EXONERACION', 100.00, 'Exoneración total por talento deportivo destacado', NULL, NULL, 0, NULL, NULL, 0, 1, 1, '2026-02-09 20:03:14', '2026-03-19 21:46:35', NULL),
(2, 1, 'Beca Deportiva 50%', 'PORCENTAJE', 50.00, 'Media beca por rendimiento deportivo', NULL, NULL, 0, NULL, NULL, 0, 1, 1, '2026-02-09 20:03:14', '2026-02-09 20:03:14', NULL),
(3, 1, 'Descuento Hermanos', 'PORCENTAJE', 5.00, '15% de descuento por hermano inscrito', NULL, NULL, 0, NULL, NULL, 0, 1, 1, '2026-02-09 20:03:14', '2026-03-19 22:21:46', 2),
(4, 1, 'Beca Socioeconómica', 'PORCENTAJE', 30.00, '30% de descuento por situación socioeconómica comprobada', NULL, NULL, 0, NULL, NULL, 0, 1, 1, '2026-02-09 20:03:14', '2026-03-06 03:55:14', NULL),
(5, 1, 'Descuento Referido', 'MONTO_FIJO', 10.00, '$10 de descuento mensual por referir nuevo alumno', NULL, NULL, 0, NULL, NULL, 0, 1, 1, '2026-02-09 20:03:14', '2026-02-09 20:03:14', NULL),
(6, 1, 'Descuento Pronto Pago', 'PORCENTAJE', 5.00, '5% por pago anticipado antes del día 5', NULL, NULL, 0, NULL, NULL, 0, 1, 1, '2026-02-09 20:03:14', '2026-03-06 03:56:03', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_beca_asignaciones`
--

INSERT INTO `futbol_beca_asignaciones` (`fba_asignacion_id`, `fba_tenant_id`, `fba_beca_id`, `fba_alumno_id`, `fba_inscripcion_id`, `fba_fecha_asignacion`, `fba_fecha_vencimiento`, `fba_motivo`, `fba_aprobado_por`, `fba_estado`, `fba_created_at`) VALUES
(1, 1, 3, 2, NULL, '2026-03-06', '2027-01-01', 'Descuento por Hermano', NULL, 'ACTIVA', '2026-03-07 04:02:35'),
(2, 1, 3, 1, NULL, '2026-03-25', '2026-12-31', 'DEscuento por hermano', NULL, 'ACTIVA', '2026-03-25 14:30:46');

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
(2, 1, 'pie_habil', 'Pie más Hábil', 'SELECT', '[\"Derecho\", \"Izquierdo\", \"Ambidiestro\"]', NULL, 0, 'deportivo', 2, 1, NULL, '2026-02-09 20:03:14', '2026-03-24 20:33:15'),
(3, 1, 'club_favorito', 'Club Favorito', 'TEXT', NULL, 'Ej: Barcelona SC, Liga de Quito...', 0, 'personal', 1, 1, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(4, 1, 'jugador_favorito', 'Jugador Favorito', 'TEXT', NULL, 'Ej: Messi, Cristiano...', 0, 'personal', 2, 1, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(5, 1, 'como_nos_conocio', 'Cómo nos conoció', 'SELECT', '[\"Redes sociales\", \"Recomendación\", \"Publicidad\", \"Escuela/Colegio\", \"Otro\"]', NULL, 0, 'general', 1, 1, NULL, '2026-02-09 20:03:14', '2026-03-06 17:57:24'),
(6, 1, 'autoriza_fotos', 'Autoriza publicación de fotos/videos', '', NULL, NULL, 0, 'legal', 1, 1, NULL, '2026-02-09 20:03:14', '2026-03-06 04:36:52'),
(7, 1, 'acepta_reglamento', 'Acepta reglamento interno', 'CHECKBOX', NULL, NULL, 1, 'legal', 2, 1, NULL, '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(8, 1, 'obs_medicas_extra', 'Observaciones Médicas Adicionales', 'TEXTAREA', NULL, 'Lesiones previas, limitaciones f??sicas...', 0, 'medico', 1, 1, NULL, '2026-02-09 20:03:14', '2026-03-06 21:12:36');

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
(4, 1, 'Sub-12', 'U12', 'Desarrollo téctico, transiciones y juego 9v9.', '#6021f2', 4, 11, 12, 0, '2026-02-09 20:03:14'),
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_comprobantes`
--

INSERT INTO `futbol_comprobantes` (`fcm_comprobante_id`, `fcm_tenant_id`, `fcm_pago_id`, `fcm_numero`, `fcm_tipo`, `fcm_cliente_id`, `fcm_alumno_id`, `fcm_concepto`, `fcm_subtotal`, `fcm_descuento`, `fcm_iva`, `fcm_total`, `fcm_metodo_pago`, `fcm_fecha_emision`, `fcm_estado`, `fcm_pdf_path`, `fcm_enviado_email`, `fcm_enviado_whatsapp`, `fcm_notas`, `fcm_datos_json`, `fcm_created_at`) VALUES
(1, 1, 1, 'ESC-0001', 'RECIBO', NULL, 0, 'Pago MENSUALIDAD', 0.00, 0.00, 0.00, 25.00, NULL, '2026-03-06', 'EMITIDO', NULL, 0, 0, NULL, '{\"descuento\": \"5.00\", \"pago_tipo\": \"MENSUALIDAD\", \"pago_metodo\": \"EFECTIVO\", \"recargo_mora\": \"0.00\", \"alumno_nombre\": \"Emma Sofia Pinzón Quinde\", \"monto_original\": \"30.00\", \"pago_referencia\": null, \"alumno_identificacion\": \"ENC::nS0ub0LsvGN4wMjGfeH+GERenSgYe5OyTmCDMzszYVQ=\"}', '2026-03-07 04:26:06'),
(2, 1, 2, 'ESC-0002', 'RECIBO', NULL, 0, 'Pago MENSUALIDAD', 0.00, 0.00, 0.00, 30.00, NULL, '2026-03-07', 'EMITIDO', NULL, 0, 0, NULL, '{\"descuento\": \"0.00\", \"pago_tipo\": \"MENSUALIDAD\", \"pago_metodo\": \"EFECTIVO\", \"recargo_mora\": \"0.00\", \"alumno_nombre\": \"Matias Pinzon\", \"monto_original\": \"30.00\", \"pago_referencia\": \"235\", \"alumno_identificacion\": \"ENC::Sg3hlAFdckl9hUWBT5L0HloxeUOhAajL5WjcyaQGyuk=\"}', '2026-03-07 13:09:19'),
(3, 1, 3, 'ESC-0003', 'RECIBO', NULL, 0, 'Pago MENSUALIDAD', 0.00, 0.00, 0.00, 30.00, NULL, '2026-03-24', 'EMITIDO', NULL, 0, 0, NULL, '{\"descuento\": \"0.00\", \"pago_tipo\": \"MENSUALIDAD\", \"pago_metodo\": \"EFECTIVO\", \"recargo_mora\": \"0.00\", \"alumno_nombre\": \"Emma Sofia Pinzón Quinde\", \"monto_original\": \"30.00\", \"pago_referencia\": null, \"alumno_identificacion\": \"ENC::ZIx8WRtnipwh8vtQ0DdThpcPHRv+VVQY6nT07TyB4BE=\"}', '2026-03-25 01:37:35');

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
(1, 1, 'nombre_modulo', 'Escuela de Fútbol', 'TEXT', 'Nombre personalizado del m??dulo', '2026-02-09 20:03:14', '2026-03-07 02:20:42'),
(2, 1, 'moneda', 'USD', 'TEXT', 'Moneda para precios', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(3, 1, 'max_alumnos_grupo', '25', 'NUMBER', 'M??ximo de alumnos por grupo', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(4, 1, 'requiere_certificado_medico', '0', 'BOOLEAN', 'Exigir certificado m??dico', '2026-02-09 20:03:14', '2026-03-19 22:32:11'),
(5, 1, 'edad_minima_inscripcion', '4', 'NUMBER', 'Edad m??nima para inscribir', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(6, 1, 'permite_lista_espera', '1', 'BOOLEAN', 'Activar lista de espera', '2026-02-09 20:03:14', '2026-03-07 02:21:25'),
(7, 1, 'dias_prueba_gratis', '3', 'NUMBER', 'D??as de clase de prueba gratis', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(8, 1, 'porcentaje_asistencia_min', '70', 'NUMBER', 'Porcentaje m??nimo de asistencia', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(9, 1, 'escala_evaluacion', '5', 'NUMBER', 'Escala de evaluaci??n (1-5 estrellas)', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(10, 1, 'dia_pago_limite', '10', 'NUMBER', 'D??a del mes l??mite para pago sin mora', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(11, 1, 'porcentaje_mora', '5', 'NUMBER', 'Porcentaje de recargo por mora mensual', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(12, 1, 'dias_gracia_mora', '5', 'NUMBER', 'D??as de gracia antes de aplicar mora', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(13, 1, 'comprobante_prefijo', 'ESC', 'TEXT', 'Prefijo para n??meros de comprobante', '2026-02-09 20:03:14', '2026-02-09 20:03:14'),
(14, 1, 'whatsapp_activo', '0', 'BOOLEAN', 'Activar notificaciones WhatsApp', '2026-02-09 20:03:14', '2026-03-07 02:21:25'),
(15, 1, 'email_activo', '1', 'BOOLEAN', 'Activar notificaciones por email', '2026-02-09 20:03:14', '2026-03-07 02:21:25'),
(16, 1, 'sms_activo', '0', 'BOOLEAN', 'Activar notificaciones SMS', '2026-02-09 20:03:14', '2026-03-07 02:21:25');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_entrenadores`
--

INSERT INTO `futbol_entrenadores` (`fen_entrenador_id`, `fen_tenant_id`, `fen_sede_id`, `fen_usuario_id`, `fen_nombres`, `fen_apellidos`, `fen_identificacion`, `fen_email`, `fen_telefono`, `fen_rol`, `fen_especialidad`, `fen_certificaciones`, `fen_foto`, `fen_color`, `fen_activo`, `fen_notas`, `fen_created_at`, `fen_updated_at`) VALUES
(1, 1, 1, NULL, 'Veronica M.', 'Quinde España', '1104015283', 'vmquine@gmail.com', '0993120984', 'ENTRENADOR', 'Futbol Formativo', NULL, NULL, '#f10eb4', 1, NULL, '2026-03-07 03:36:23', '2026-03-07 03:36:23');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_evaluaciones`
--

INSERT INTO `futbol_evaluaciones` (`fev_evaluacion_id`, `fev_tenant_id`, `fev_alumno_id`, `fev_grupo_id`, `fev_periodo_id`, `fev_habilidad_id`, `fev_categoria_id`, `fev_calificacion`, `fev_aprobado`, `fev_fecha`, `fev_evaluador_id`, `fev_observacion`, `fev_created_at`, `fev_updated_at`) VALUES
(1, 1, 1, 1, 1, 0, 0, 85, 0, '2026-03-06', NULL, 'SAque de arcvo', '2026-03-07 01:53:01', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_ficha_alumno`
--

INSERT INTO `futbol_ficha_alumno` (`ffa_ficha_id`, `ffa_tenant_id`, `ffa_alumno_id`, `ffa_categoria_id`, `ffa_posicion_preferida`, `ffa_pie_dominante`, `ffa_experiencia_previa`, `ffa_club_anterior`, `ffa_objetivo`, `ffa_talla_camiseta`, `ffa_talla_short`, `ffa_talla_zapato`, `ffa_numero_camiseta`, `ffa_autorizacion_medica`, `ffa_seguro_medico`, `ffa_fecha_ingreso`, `ffa_fecha_ultimo_avance`, `ffa_datos_custom`, `ffa_documentos`, `ffa_activo`, `ffa_notas`, `ffa_created_at`, `ffa_updated_at`) VALUES
(1, 1, 1, NULL, 'Portero', 'DERECHO', NULL, NULL, 'RECREATIVO', 'S', NULL, NULL, 1, 0, NULL, '2026-03-06', NULL, '{\"pie_habil\": \"Derecho\", \"club_favorito\": \"\", \"autoriza_fotos\": \"\", \"como_nos_conocio\": \"Redes sociales\", \"jugador_favorito\": \"Messi\", \"obs_medicas_extra\": \"Ninguna\", \"posicion_secundaria\": \"\"}', NULL, 1, NULL, '2026-03-06 23:00:05', '2026-03-07 03:56:01'),
(2, 1, 2, NULL, NULL, NULL, NULL, NULL, 'RECREATIVO', NULL, NULL, NULL, NULL, 0, NULL, '2026-03-06', NULL, '{\"pie_habil\": \"Derecho\", \"club_favorito\": \"\", \"autoriza_fotos\": \"\", \"como_nos_conocio\": \"\", \"jugador_favorito\": \"\", \"obs_medicas_extra\": \"\", \"posicion_secundaria\": \"\"}', NULL, 1, NULL, '2026-03-07 04:00:30', '2026-03-25 15:32:40');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_grupos`
--

INSERT INTO `futbol_grupos` (`fgr_grupo_id`, `fgr_tenant_id`, `fgr_sede_id`, `fgr_periodo_id`, `fgr_categoria_id`, `fgr_cancha_id`, `fgr_entrenador_id`, `fgr_nombre`, `fgr_descripcion`, `fgr_cupo_maximo`, `fgr_cupo_actual`, `fgr_edad_min`, `fgr_edad_max`, `fgr_precio`, `fgr_estado`, `fgr_color`, `fgr_created_at`, `fgr_updated_at`) VALUES
(1, 1, 1, 1, 4, 4, 1, 'Equipo 2014-2013', NULL, 20, 2, NULL, NULL, 35.00, 'ABIERTO', '#ea1ae3', '2026-03-06 04:12:27', '2026-03-07 04:18:21');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_grupo_horarios`
--

INSERT INTO `futbol_grupo_horarios` (`fgh_horario_id`, `fgh_tenant_id`, `fgh_grupo_id`, `fgh_dia_semana`, `fgh_hora_inicio`, `fgh_hora_fin`, `fgh_cancha_id`, `fgh_activo`, `fgh_notas`) VALUES
(1, 1, 1, 'LUN', '15:00:00', '16:00:00', NULL, 1, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_inscripciones`
--

INSERT INTO `futbol_inscripciones` (`fin_inscripcion_id`, `fin_tenant_id`, `fin_alumno_id`, `fin_grupo_id`, `fin_periodo_id`, `fin_fecha_inscripcion`, `fin_fecha_baja`, `fin_monto`, `fin_descuento`, `fin_monto_final`, `fin_beca_id`, `fin_estado`, `fin_notas`, `fin_created_at`, `fin_updated_at`) VALUES
(1, 1, 1, 1, NULL, '2026-03-06', NULL, 10.00, 0.00, 0.00, NULL, 'ACTIVA', NULL, '2026-03-07 01:24:28', '2026-03-25 14:31:14'),
(2, 1, 2, 1, NULL, '2026-03-06', NULL, 30.00, 0.00, 0.00, NULL, 'ACTIVA', NULL, '2026-03-07 04:18:21', '2026-03-07 04:18:49');

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
  `fpg_factura_id` int DEFAULT NULL COMMENT 'FK vincula facturacion_facturas',
  PRIMARY KEY (`fpg_pago_id`),
  KEY `idx_fpg_tenant_estado` (`fpg_tenant_id`,`fpg_estado`),
  KEY `idx_fpg_alumno` (`fpg_alumno_id`),
  KEY `idx_fpg_cliente` (`fpg_cliente_id`),
  KEY `idx_fpg_sede` (`fpg_sede_id`),
  KEY `idx_fpg_vencimiento` (`fpg_tenant_id`,`fpg_fecha_vencimiento`,`fpg_estado`),
  KEY `idx_fpg_mora` (`fpg_tenant_id`,`fpg_dias_mora`),
  KEY `fk_fpg_inscripcion` (`fpg_inscripcion_id`),
  KEY `idx_fpg_factura` (`fpg_factura_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_pagos`
--

INSERT INTO `futbol_pagos` (`fpg_pago_id`, `fpg_tenant_id`, `fpg_sede_id`, `fpg_inscripcion_id`, `fpg_alumno_id`, `fpg_grupo_id`, `fpg_cliente_id`, `fpg_concepto`, `fpg_tipo`, `fpg_mes_correspondiente`, `fpg_monto`, `fpg_descuento`, `fpg_beca_descuento`, `fpg_total`, `fpg_metodo_pago`, `fpg_referencia`, `fpg_fecha`, `fpg_fecha_vencimiento`, `fpg_dias_mora`, `fpg_recargo_mora`, `fpg_estado`, `fpg_comprobante_num`, `fpg_notas`, `fpg_created_at`, `fpg_updated_at`, `fpg_factura_id`) VALUES
(1, 1, NULL, NULL, 2, 1, 4, '', 'MENSUALIDAD', '2026-03', 30.00, 5.00, 0.00, 25.00, 'EFECTIVO', NULL, '2026-03-06', NULL, 0, 0.00, 'PAGADO', NULL, NULL, '2026-03-07 04:23:03', '2026-03-22 02:51:58', 16),
(2, 1, NULL, NULL, 1, 1, 4, '', 'MENSUALIDAD', '2026-01', 30.00, 0.00, 0.00, 30.00, 'EFECTIVO', '235', '2026-03-07', NULL, 0, 0.00, 'PAGADO', NULL, NULL, '2026-03-07 13:05:19', '2026-03-22 02:51:58', 16),
(3, 1, NULL, NULL, 2, 1, 4, 'MENSUALIDAD 2026-03', 'MENSUALIDAD', '2026-03', 30.00, 0.00, 0.00, 30.00, 'EFECTIVO', NULL, '2026-03-19', NULL, 0, 0.00, 'PAGADO', NULL, NULL, '2026-03-19 17:07:54', '2026-03-19 20:31:24', 15);

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_periodos`
--

INSERT INTO `futbol_periodos` (`fpe_periodo_id`, `fpe_tenant_id`, `fpe_nombre`, `fpe_fecha_inicio`, `fpe_fecha_fin`, `fpe_estado`, `fpe_notas`, `fpe_created_at`, `fpe_updated_at`) VALUES
(1, 1, 'Periodo 2026', '2026-01-01', '2026-12-31', 'ACTIVO', NULL, '2026-03-07 01:48:20', '2026-03-07 01:54:03');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_torneos`
--

INSERT INTO `futbol_torneos` (`fto_torneo_id`, `fto_tenant_id`, `fto_sede_id`, `fto_nombre`, `fto_organizador`, `fto_sede_torneo`, `fto_fecha_inicio`, `fto_fecha_fin`, `fto_categoria_id`, `fto_tipo`, `fto_descripcion`, `fto_costo_inscripcion`, `fto_estado`, `fto_resultado`, `fto_notas`, `fto_created_at`, `fto_updated_at`) VALUES
(1, 1, NULL, 'Los operadores', NULL, 'Loja, Colegio Militar', '2025-11-06', '2026-03-07', NULL, 'AMISTOSO', NULL, 0.00, '', NULL, NULL, '2026-03-06 16:57:08', '2026-03-07 01:27:29');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `futbol_torneo_jugadores`
--

INSERT INTO `futbol_torneo_jugadores` (`ftj_id`, `ftj_tenant_id`, `ftj_torneo_id`, `ftj_alumno_id`, `ftj_posicion`, `ftj_numero`, `ftj_es_capitan`, `ftj_estado`, `ftj_notas`, `ftj_created_at`) VALUES
(1, 1, 1, 1, 'PORTERO', 1, 0, 'CONVOCADO', NULL, '2026-03-07 01:25:48');

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
(7, 1, 4, 'Cancha Fútbol 1 - Cancha Central', 'FUTBOL', NULL, 'Cancha de fútbol profesional', 22, 25.00, 50.00, NULL, 0, 0, NULL, 'DISPONIBLE', '2026-01-25 23:07:00', '2026-03-06 20:53:33', 1, NULL),
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
  `sed_monto_mensualidad` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Valor de mensualidad configurado para esta sede',
  `sed_monto_matricula` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Valor de matr├¡cula configurado para esta sede',
  `sed_comprobante_inicio` int UNSIGNED NOT NULL DEFAULT '1' COMMENT 'N├║mero de comprobante/recibo inicial para la secuencia de esta sede',
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

INSERT INTO `instalaciones_sedes` (`sed_sede_id`, `sed_tenant_id`, `sed_codigo`, `sed_nombre`, `sed_descripcion`, `sed_direccion`, `sed_ciudad`, `sed_provincia`, `sed_pais`, `sed_latitud`, `sed_longitud`, `sed_telefono`, `sed_email`, `sed_horario_apertura`, `sed_horario_cierre`, `sed_dias_atencion`, `sed_superficie_total`, `sed_capacidad_total`, `sed_estacionamiento`, `sed_cafeteria`, `sed_tienda`, `sed_monto_mensualidad`, `sed_monto_matricula`, `sed_comprobante_inicio`, `sed_foto_principal`, `sed_galeria`, `sed_es_principal`, `sed_estado`, `sed_fecha_registro`, `sed_fecha_actualizacion`) VALUES
(1, 1, 'CENTRAL', 'Sede Central', NULL, 'Av. Principal 123', 'Loja', NULL, 'Ecuador', NULL, NULL, '0993120984', 'fbpinzon@gmail.com', NULL, NULL, 'LUNES-DOMINGO', NULL, NULL, 'S', 'N', 'N', 30.00, 25.00, 1, NULL, NULL, 'S', 'A', '2026-01-25 00:35:10', '2026-03-24 20:24:14');

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
-- Estructura de tabla para la tabla `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `notificacion_id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL COMMENT 'Usuario destinatario',
  `tenant_id` int DEFAULT NULL COMMENT 'Tenant del usuario (NULL = global)',
  `tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'INFO' COMMENT 'Tipo: INFO, WARNING, ERROR, SUCCESS',
  `titulo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `url_accion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL de destino al hacer clic',
  `icono` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fas fa-bell' COMMENT 'Clase Font Awesome',
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#3B82F6' COMMENT 'Color hex del icono',
  `leida` enum('S','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_lectura` timestamp NULL DEFAULT NULL,
  `fecha_expiracion` timestamp NULL DEFAULT NULL COMMENT 'NULL = no expira',
  PRIMARY KEY (`notificacion_id`),
  KEY `idx_noti_usuario` (`usuario_id`),
  KEY `idx_noti_usuario_leida` (`usuario_id`,`leida`),
  KEY `idx_noti_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notificaciones del sistema por usuario';

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
) ENGINE=InnoDB AUTO_INCREMENT=446 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(243, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=yo1VGQDZmIymLo1UvW1GQQOHMeGdnKohKOf5Dj4jnvb6y_h1YRRcZ5XlAe4xgFCDbnEQkuKjVXRDnRVEUdFDNTDIw5Cs5qyFvu7l5jU4yHUj4e_KT-2EGQGihwVvGDKj', 'POST', '2026-03-04 15:25:29'),
(244, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=nRF6BV8be1Q1PLA-eeqJLgN6_lz0h1WNpMdOVaTMXiXcSMkTC_RvKRpqHG5df-YKjHc1NMyAheMIh8S5XAgdkFZ11OVfP6BzfObggUsF3WW0MIBeprPvXcTFzWkAGPDz', 'POST', '2026-03-04 20:08:32'),
(245, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=0yv41lr-rFKAfpoePyyfIMMgQcsmUbA2rmojCau3sD8X_mLrkjLnjixmBlehf-uS8N0N3zRtGW9K1H3JQlC4we155UPs4Zyngi-NGT_YszmyB_JjidtudpFk', 'GET', '2026-03-04 20:41:24'),
(246, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=jKnkWdBnGKCGHLAG1lt3z1fh2qbqpsY0VrrKVGBGNdWGO9FFlXcCqwhYHEsYhUQiMdLQEgYxo8q_lY9aA9ZCX3MuDkSix2AAvV8Rdp1RA-E8eZZp-uTsiwRjGoizE6-x', 'POST', '2026-03-04 20:43:06'),
(247, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=0K_r9LEmwAerRUWPVfNPK4uyagXOHEw491j1ZbQZXd37liK9cEnUSeOmHnvMa13VoNarnNlEkCgadm1GqAqBJ6y3ZMHvhvccmBhhWdjRHIktS6bFhrsC55NP', 'GET', '2026-03-04 20:46:56'),
(248, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=3R2NZh5ncq3YJvNC5aJhEu-2bp5_eEjhg70lXY5USK9I5LxqpLRMnnU0PI6YecGQaF11lt4WcpSj_17_Of8ouSLEsFzeXriSRrUqfo2PqP0MWJxdC8_D5X6-XEUgGJJ7', 'POST', '2026-03-04 20:48:24'),
(249, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=7p1UyZ9r1szKsHAOq6D5DMxAXXy1rf3lidQWvtHhySy6V8WCE9Izjs57D8FE0SGi1WFseUrEAgLMQABgpsIt8sVSXsF3ZQkfF2kRubPr0h7Q3QDQHp3NWY7_8xpVFcFR', 'POST', '2026-03-04 21:24:18'),
(250, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=prqudhqiDhSHF1kaEes-AUkFGxzzs-s5gkFwqicYPSFoxtxL91bI_4d6cjkH1WqMMXLsAUaxXzgx5JWNzE4z-l6qziW5wT8fZ06RgCB4rWN3k55jOP_luLW0', 'GET', '2026-03-04 21:42:04'),
(251, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=xvU96taXmyaOlXG98CIwcaBRROWMf3IvC1GXwLjEqv8M6VDnHlmNMuZ4I8NcCWbyG_gZKWErTAEa4LRzGvtGG8db54cd1XJVdx_LAUxYXJVX1nXxOrw7VmJH4MP3fnbi', 'POST', '2026-03-04 21:43:04'),
(252, 1, 1, 'Seguridad', 'seguridad', 0, '0', '\"DESBLOQUEO_IP\"', '{\"ip\": \"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=KYN1j0vIfgL4q2tXBaym4JqTCGXJryK3lztFEYQg8V_J9JYVrjS74bzptnafXf79ruJeaYTP1EazNM9LLY6k_t_dQZrWu6Y46etOJlQPCPK3hEYqCUf0OZrgnUJtE2nXO8xLAHB7rOWQ86Q~', 'POST', '2026-03-04 22:34:20'),
(253, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=d76qTq7zDgDEl5DG9nKhU6973ZtDhlv7nTskF--RSnpQIQKm26z9nrFP4RAOI94ozH-SvC1TFy82xXIDRWqywHTobcK37YjZuvk5zdcih1JWq7Q5rjAVXGuwivN3V9g9', 'POST', '2026-03-05 01:32:00'),
(254, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=oBdmS7dtDp8sZYep-Oy5vbk7uxxP14Dx3yQ5ZW75_ay1T1AZ1Rfme6WARx1INQkQsL7Kd7EMqw9nokkKoSRHOD9FWk6OpOaRnYkA4rLs_5tsBSjJ09xyt5p-Ku-5aKYA', 'POST', '2026-03-05 03:18:30'),
(255, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=OhFFYr73WnPOT7NArPAN28fpT_C_ypvLsYztP4QOry3SZ5plmAn3oYSaO_dfqP8C5_SwHEWpMReqQ9OvEke3wETHFIje34BIJRp3ZatSOPYBCMEIAdMsm-Ud1YvlAdf8', 'POST', '2026-03-05 05:02:00'),
(256, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=aCPc0CJYL9ETikthrXuDOUVPqQYwJXSA91JtskFpfyQYGR4SiMZx8uhikjAydwWVFfh9sjZ-CbSwvKXvqxWgRq8nzZpsDuAdN-MCcD1qd9v2-X7NpaeQ4EO7', 'GET', '2026-03-05 05:04:57'),
(257, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=bu_tGaxSG64B2qMUD7Hh_dkFRfxZA3qjJVa7DbhplPP6IJm6xw8ORK3S7avJ73ISUHynrrY4U1xzmnM1XC8n0vgi3fEYgTuWZVDP1r9aFj4VFrgnLa_wPhfEh3chgul5', 'POST', '2026-03-05 05:08:03'),
(258, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=iNutNpoQzmQ7rVxC01p0ePHa_aO67JDkNF3ukUbDlwDF_W_NY9mUfTGqqEa-WsXV0I5xyYthh4c8FjO_3j7Df-WM1u-0mUO4nrRkaBvDanKYR_R0mKsW-ud_J5wb4zpR', 'POST', '2026-03-05 13:29:40'),
(259, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ntk731vIKxsqSqm6vE7rLC4ygEknnGUZOv4GxheqF1c5r-MtL3r5ygteSlgefgNh91r9w47FhaBhlGtqoTUCqoSL4ixjXyO7Bl3m9N9-BWlYzcI-PoeVS0yt5Gmn-XiO', 'POST', '2026-03-05 15:15:18'),
(260, 1, 1, 'Seguridad', 'seguridad', 0, '0', '\"LIMPIEZA_INTENTOS\"', '{\"ip\": \"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=kEofMDr0OiaQhJzl9sATrwZzKgXQps4EA-IHsGao-9Fr6EDZjNsHR-4ubeFx8SEiavgIR3QJsfXSIl3E_O7XJ3yV9-YUfmmiAAuyhM7i4_L9jMV8LVRK9tu22K4jT6OrQ-ZajP7nwgyzaGRtOQ~~', 'POST', '2026-03-05 15:29:46'),
(261, NULL, NULL, 'seguridad', 'seguridad_modulos', 31, 'crear_modulo', NULL, '{\"mod_codigo\": \"SOCCEREASY\", \"mod_nombre\": \"SOCCEREASY\"}', '::1', NULL, '/digisports/public/index.php?r=FlXQ9N4pTrjeYI3HTsg337cASk6Sh_hf6tG6na16Dfm2ijoFT3UrnZibWNYry7PmfVC-8_6eF9UEPtEJRl0z3P_6GSquD0YnjXzijV0FznEjuD6YDToo7DGw9tlX0BOB', 'POST', '2026-03-05 16:37:36'),
(262, NULL, NULL, 'seguridad', 'seguridad_modulos', 1, 'editar_modulo', '{\"mod_id\": 1, \"mod_icono\": \"fas fa-building\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"ARENA\", \"mod_nombre\": \"DigiSports Arena\", \"mod_created_at\": \"2026-01-26 00:37:36\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-02-07 23:36:56\", \"mod_color_fondo\": \"#FF7E70\", \"mod_descripcion\": \"Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"instalaciones\", \"mod_url_externa\": null, \"mod_ruta_controller\": \"dashboard\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-building\", \"mod_orden\": 2, \"mod_activo\": 1, \"mod_codigo\": \"ARENA\", \"mod_nombre\": \"DigiSports Arena\", \"mod_es_externo\": 0, \"mod_color_fondo\": \"#FF7E70\", \"mod_descripcion\": \"Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.\", \"mod_url_externa\": \"\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=8ED5qm6FUwUJU5Vv_TEHSSR3v44FJ6UvM34LepQ8elBROZw_1loZCBLQxtWm3fOS0-B2Zf7o1ewZB9mBe5wgCLCXVKGZBmiek6ts2FH_ecLqvlpD1jImpNmgrXAS_oV8hg~~', 'POST', '2026-03-05 16:38:00'),
(263, NULL, NULL, 'seguridad', 'seguridad_modulos', 30, 'editar_modulo', '{\"mod_id\": 30, \"mod_icono\": \"fas fa-apple-alt\", \"mod_orden\": 0, \"mod_activo\": 0, \"mod_codigo\": \"NUTRICION\", \"mod_nombre\": \"Planes Nutricionales\", \"mod_created_at\": \"2026-02-07 17:50:38\", \"mod_es_externo\": 0, \"mod_updated_at\": \"2026-02-26 10:26:25\", \"mod_color_fondo\": \"#fd7e14\", \"mod_descripcion\": \"Seguimiento nutricional de deportistas\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": \"nutricion\", \"mod_url_externa\": \"/nutricion/\", \"mod_ruta_controller\": \"dashboard\", \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-apple-alt\", \"mod_orden\": 2, \"mod_activo\": 0, \"mod_codigo\": \"NUTRICION\", \"mod_nombre\": \"Planes Nutricionales\", \"mod_es_externo\": 0, \"mod_color_fondo\": \"#fd7e14\", \"mod_descripcion\": \"Seguimiento nutricional de deportistas\", \"mod_url_externa\": \"/nutricion/\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=CFQWse5decimoIypzRVoRpHmHRktQpofnEIeOmOpD7WmcKnOfPzpFQgx9T1SJZUq7OoAk6ElyqEkVqVmv7tU_S6suG9UaQVHE2HkupqttlFDq5zlhBlNuzWjruQ9uuK0dQ~~', 'POST', '2026-03-05 16:38:33'),
(264, NULL, NULL, 'seguridad', 'seguridad_modulos', 31, 'editar_modulo', '{\"mod_id\": 31, \"mod_icono\": \"fas fa-puzzle-piece\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"SOCCEREASY\", \"mod_nombre\": \"SOCCEREASY\", \"mod_created_at\": \"2026-03-05 11:37:36\", \"mod_es_externo\": 1, \"mod_updated_at\": \"2026-03-05 11:37:36\", \"mod_color_fondo\": \"#007bff\", \"mod_descripcion\": \"Sistema de administración de de la Escuela Champios\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": null, \"mod_url_externa\": null, \"mod_ruta_controller\": null, \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-puzzle-piece\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"SOCCEREASY\", \"mod_nombre\": \"SOCCEREASY\", \"mod_es_externo\": 1, \"mod_color_fondo\": \"#007bff\", \"mod_descripcion\": \"Sistema de administración de de la Escuela Champios\", \"mod_url_externa\": \"\", \"mod_requiere_licencia\": 1}', '::1', NULL, '/digisports/public/index.php?r=vAqq0e-f_hWpysTOD6kNlu_vALJY-YX9D-uAVRBjOQPUURMqGfWFwXO08IMIgTHviXGSUSJ4qH2AYRG0GrSwv5T9mQI0cNSYoo6Xl5JTafPUSDSWyKyCSt4TxPCJXBr7gg~~', 'POST', '2026-03-05 16:39:30'),
(265, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=I7Et8uoN3EeSr_it6LDDQ-l5FKb1QEuJmHTD5PuH0nPyawxB26WxRNgkN2WaOxMgMMaHxeZpdX4xB9Jk9JworCbHsPksHEkfg6S6vseCTVOafr0PEIkspLkd7gWZBbV_', 'POST', '2026-03-05 17:29:17'),
(266, NULL, NULL, 'seguridad', 'seguridad_modulos', 31, 'editar_modulo', '{\"mod_id\": 31, \"mod_icono\": \"fas fa-puzzle-piece\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"SOCCEREASY\", \"mod_nombre\": \"SOCCEREASY\", \"mod_created_at\": \"2026-03-05 11:37:36\", \"mod_es_externo\": 1, \"mod_updated_at\": \"2026-03-05 11:39:30\", \"mod_color_fondo\": \"#007bff\", \"mod_descripcion\": \"Sistema de administración de de la Escuela Champios\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": null, \"mod_url_externa\": null, \"mod_ruta_controller\": null, \"mod_requiere_licencia\": 1, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-puzzle-piece\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"SOCCEREASY\", \"mod_nombre\": \"SOCCEREASY\", \"mod_es_externo\": 1, \"mod_color_fondo\": \"#007bff\", \"mod_descripcion\": \"Sistema de administración de de la Escuela Champios\", \"mod_url_externa\": \"\", \"mod_requiere_licencia\": 0}', '::1', NULL, '/digisports/public/index.php?r=5B7UDmuAgEjFtnN2VOaAIVX-yIIQafQxentHnAGixfEdhxOYHU-5hp911uWR7DCMg0NJUtESX8n5LWM8tH0zHkWunQTqaNpDHvzV9VSzzQ7a0wBYBgKOHIDz4dKNn4EDTw~~', 'POST', '2026-03-05 17:30:14'),
(267, NULL, NULL, 'seguridad', 'seguridad_modulos', 31, 'editar_modulo', '{\"mod_id\": 31, \"mod_icono\": \"fas fa-puzzle-piece\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"SOCCEREASY\", \"mod_nombre\": \"SOCCEREASY\", \"mod_created_at\": \"2026-03-05 11:37:36\", \"mod_es_externo\": 1, \"mod_updated_at\": \"2026-03-05 12:30:14\", \"mod_color_fondo\": \"#007bff\", \"mod_descripcion\": \"Sistema de administración de de la Escuela Champios\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": null, \"mod_url_externa\": null, \"mod_ruta_controller\": null, \"mod_requiere_licencia\": 0, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-puzzle-piece\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"SOCCEREASY\", \"mod_nombre\": \"SOCCEREASY\", \"mod_es_externo\": 1, \"mod_color_fondo\": \"#007bff\", \"mod_descripcion\": \"Sistema de administración de de la Escuela Champions\", \"mod_url_externa\": \"\", \"mod_requiere_licencia\": 0}', '::1', NULL, '/digisports/public/index.php?r=5B7UDmuAgEjFtnN2VOaAIVX-yIIQafQxentHnAGixfEdhxOYHU-5hp911uWR7DCMg0NJUtESX8n5LWM8tH0zHkWunQTqaNpDHvzV9VSzzQ7a0wBYBgKOHIDz4dKNn4EDTw~~', 'POST', '2026-03-05 17:31:12'),
(268, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=7O-Hzp_BPCWZTEVR4t_PlfcOhXx3ZQNGQT69WM-Y8MPfj6um5HFe68pLnouPNrxvbTqlISp6iatGgo-zC5vMsAZnbshnToocjVaEbA2ycmzJR8sLvG7vVETgzD9WohjQ', 'POST', '2026-03-05 19:49:27'),
(269, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=2wWVRK5XAwXL05JAcnZ04j0QUQMVWR2d41iMvx6hAjLUdccD9fExxBtGiP7-xEMzjq7wvhiyZsE0lzg1vPtZ-HRzaAik1Qo_mgcHSE77d9XRxIHcG-DwJutmpk7mSwC8', 'POST', '2026-03-05 20:27:15'),
(270, NULL, NULL, 'seguridad', 'seguridad_modulos', 31, 'editar_modulo', '{\"mod_id\": 31, \"mod_icono\": \"fas fa-puzzle-piece\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"SOCCEREASY\", \"mod_nombre\": \"SOCCEREASY\", \"mod_created_at\": \"2026-03-05 11:37:36\", \"mod_es_externo\": 1, \"mod_updated_at\": \"2026-03-05 15:22:10\", \"mod_color_fondo\": \"#007bff\", \"mod_descripcion\": \"Sistema de administración de de la Escuela Champions\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": null, \"mod_url_externa\": \"http://localhost/soccereasy/sso.php\", \"mod_ruta_controller\": null, \"mod_requiere_licencia\": 0, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fa-basketball-ball\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"SOCCEREASY\", \"mod_nombre\": \"SOCCEREASY\", \"mod_es_externo\": 1, \"mod_color_fondo\": \"#89F336\", \"mod_descripcion\": \"Sistema de administración de de la Escuela Champions\", \"mod_url_externa\": \"http://localhost/soccereasy/sso.php\", \"mod_requiere_licencia\": 0}', '::1', NULL, '/digisports/public/index.php?r=8TgYdCDPztTdQKdQGF71X3ygZadfbt8ivzzZrIi13amnNMKx_jtUUYZF1nDqk_fjXq5N8yK0y3NMfRVnJeb_lr20sB2ji6eT11l0shmOwmsUU_7dgewi2JeqLaUZW-PqeA~~', 'POST', '2026-03-05 21:08:26'),
(271, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=mnP1OupqVKoqXWk3kAVnPv8gBZpOkYGZFYBLyTzDCsKgnabEUTqf7Gvt_qjfHW93LKWmyi-VCiaypg-ZAC5on9z5ccvxC56DDDZfXfk-TPcmH1QqJJvdZ2TabckENv4x', 'POST', '2026-03-05 21:56:22'),
(272, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=QQl6evf3OkYKW76qq1B4YybMepUEnlkuD-21pw9QxpfNOCVQY0-Q7Vfvg65tpvt1MNGuknmZz63oWQRv1j_d8xSoERP4-PzSl5VkLyYYF8VcFn2BXAqdceL8', 'GET', '2026-03-05 22:48:15'),
(273, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=bbEFw0P2pKJDextc4Mt-1kEulRWgsovy-nOnmnydKNTsAKevGQ8ThpYLH8r0WLLXmOeBE97k7YR1l_46ANHYe9MPLy9vd-7uWuL0BT90OzC4l7CHikZKyrqQpG3BQ93U', 'POST', '2026-03-05 22:48:33'),
(274, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=2TDvoxJxgXQWAEEvngDoi_5zgNfyiWV0_QCZeIZW862kxls_HNynRuuV9c3f6KK2dW0BEW2pcmlXaw3Sqx2_t6cJ58B1mYENTR1ktOeVsS0__oaL9d1rz5oho3xHuIix', 'POST', '2026-03-06 01:04:33'),
(275, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=kP2_d3fzmeJVG6mh--1x8ank0DmyCj1XrHOSjJz96PlfX72Ol0iNkcjdDltKZmEO2WMCHy2H_u9KC4cUE3Zbm9xxu2oK8CXoFaFUS99Tn2Y4dtzQfgSCPjs_', 'GET', '2026-03-06 01:23:45'),
(276, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=3BHr3aTdOdVd7_eQ-usHnmIhWoWzo__zqtpe_UhxJsR7Q_REkddQ5aqVWn9weQELKb_tjuDd4tvyg6aJ0NYnhFgg5Kir0YBLvJ6Z1tSMLBLZbREd0dqx2XiFpITQ3say', 'POST', '2026-03-06 03:23:27'),
(277, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=6tfwqgfVC92_a_HBqzx3GnhgmolKG9TzY1kcJANrHExMCkPmqM9impb-Tkxt-NGfmM_b-WQqlLFKDl9_ELEY_SsvGnSUV3xNbQbXbt7SWU4RRUnfPWY6RsUp6FlbHfWr', 'POST', '2026-03-06 15:46:47'),
(278, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=u_P-j7JrI9mcA_u4MAam-ENuu9YBhHqWBr5UzNjLdv4T32X2iLT28lCVphC9ItRHfg_TL5O4c3RLDysaafo5PSoRQ3BohWtgmT6xg0wWtMX0VzZij5PWag-_DUe2mRzF', 'POST', '2026-03-06 16:49:40'),
(279, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=Hgi8O9n-AX9_5xCoZ24z3--WbZrerqSOCP4COM1H5k-832hgwRC5FeJTBv6FEWX4nYBwKqhDYt0jW6fgw0Dk9G3PqOjo9cmio0LBeaHIMK_MQ4k1fXQ3tmYptViMEWcH', 'POST', '2026-03-06 17:52:30'),
(280, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=kG1XUq6SKglLei8yXxaNkz8SOj0idBbhTbFh7Nu5ZxUrsszl8GMcq_yO_BLG8jTHcQu3vKlI8tthR-NSpeco5NI016NmmDJLN3u0NMYM6mXPEcaEiFyJkXtwwNv5lpNd', 'POST', '2026-03-06 19:28:49'),
(281, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=TZe7fnljQ94NGznmzfLi-He-kmyQ6y3vJZU5z9jWbKMr64hJCLV0eI2r1hu5PDQwS6eBuL4pakHPrsHS3ay9j6_flSqmwiH1J54hdsVwS4ssGfVs-C4yua-mJh0s7UGq', 'POST', '2026-03-06 20:51:36'),
(282, 1, 1, 'Seguridad', 'seguridad', 0, '0', '\"LIMPIEZA_INTENTOS\"', '{\"ip\": \"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=T77VoHYDlFX3hVkDQBvQQfic1LTEPwFh-aO8FqlWzvXiOxJtqytIV20I3pIVFpjjH1CEfI0ZIXeKfMZVd8X9Ls1eNxYu8pFT4d_QxnoMq_o1MKUKmTbjP8hWYJlVxdfOr-JwqxcOmyrzhTL9hA~~', 'POST', '2026-03-06 21:15:57'),
(283, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=Tj6FHbYSbwXxn_fEwN3d57VoXUetVbhuoAKJq2v3bDJgZFMimr9LXtoelstuz_kZMkh0qcTf5bcAlKo__jlZJNnSKQpjWu_3P3W1RToD51GBVIjVVwpxDyLgQMoMfc7B', 'POST', '2026-03-06 23:58:37'),
(284, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=JuvLtlHtnGboTPQkPpM-YsXbQeo0_ld1PkPpFsryKrBTx8hg_rAHkjKnDacZeYStnhi5lV9Q6a1UuKwjBxykWkb7FC7fJb-Qsj0xdFCprase81NAfpP5OWHYxrW6T0zv', 'POST', '2026-03-07 01:19:27'),
(285, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=Ig_pf5Y98hLfDBdL_Zs-UiUZEx1Ah2IYmKFeDccSM--NwWKh6uh1c6obax_b2pncXXyEHKAUv2E4MjKSY9zqHt0ATmxdzLFY-uMkA0ubxb039dI_ys3GupuAz2StXCXW', 'POST', '2026-03-07 12:30:50'),
(286, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=hWJY4XYEDi9k8cjnLbmD6CmCoaddNSlLKCs6uuqxCsy_MGWnskyBLMbgvqVDwKeyXTUIaqxmtVUqwLY48tTF_YjcTVC1j6OJVZs2YYmTN8G0PHT1thWUoLfkjDUx-qJg', 'POST', '2026-03-08 00:46:11'),
(287, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=9dgdPi89Gvm0g8dJ0h3yFdffRiziS-GYlJfT2P8MFO3O748O1HGDGUemb1wonqA14T5ct73WOZ5LYxbyvy2jXIa0r-osdXf_-rkNWfM_6EbjWZpwi_veZ7zqSwnp-L9m', 'POST', '2026-03-08 02:05:48'),
(288, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ckmVPlYAc3Oh7etpN9QNGMaPGY4KFJy1fLzv8JCDAsA_3y_X9uMRW2GsHUS_3yUF4BFHBdje5RiJUsXfkaq7qv0RvmPUStpwGGTaM_y0dVLKmU3E4g9EN1snYZcPnoj5', 'POST', '2026-03-08 03:17:01'),
(289, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=xp4QtmKTCLcqvdAoPRWpoSGRiWw5q88rLyyyyXQ0WNMCi6095QswXodIiaOV4FbNJ4k9ozyCl8zOVuv4ABrfP7sRaxpf_7YX5CLpXO5gSaiUV4324JJuA3wmiy-3GeEE', 'POST', '2026-03-08 04:53:44'),
(290, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=1A57C9jK6cNjuuQ2hTNLxLSfWBmKNMQGmltk-EmpNaKV5wKPqWqGjvK21YeM-ZPCMnFtZbDGKZiQnqE_Cypv4UgBMY26P88jOVSB_Ur2o9oj9j9LgVLiKRnR_YGnGPZo', 'POST', '2026-03-09 14:44:23');
INSERT INTO `seguridad_auditoria` (`aud_auditoria_id`, `aud_tenant_id`, `aud_usuario_id`, `aud_modulo`, `aud_tabla`, `aud_registro_id`, `aud_operacion`, `aud_valores_anteriores`, `aud_valores_nuevos`, `aud_ip`, `aud_user_agent`, `aud_url`, `aud_metodo`, `aud_fecha_operacion`) VALUES
(291, NULL, NULL, 'seguridad', 'usuario', 3, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::aG7Jk4au40KYzfi95x20TBEOCTM6/H4YHvEeXnXrT0qOSFaMN92H8Jom6nvoEcsc\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$ek8zOE9QbEtsZU5heWNGSw$gCwhDpX6hVMCYRs0VCbryQ9j1+8XL38U5XnkUmMkOyo\", \"usu_telefono\": \"ENC::O1kxV+oUWhj3YETC2fl1HYNagPPFEvuyaQYmn7lJElo=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:21:17\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::4i0U6033Xu31fjUOOi2ZzeXGZr+5UNQcImt6cx6XtGY=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-02-25 23:58:40\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"S\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::NA08Ec7QeL8W1ku2W01Rd7S5FRXFeqWE4dhFRXceQ7YlrluwIecCkUzrmeDQjeCR\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$S2wzZFc0cGFpaUouNXRCcw$CyDE4Ij59TlCNt5rs9Srt1Z5My2VtSdk7NmlO8yiq30\", \"usu_telefono\": \"ENC::dEzreJmwkraQ5+gftOagZKC+fikdtpv4tUl+UhNp0hs=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-02-25 15:21:17\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::LHfp7PrKGCNHTJTxZv9q54J0HIxp9FMomG9D/Sh5r2o=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 0, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-03-09 09:47:38\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"S\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=bJjZLwI4SVbhFGZqQpOKpr5jmbeoizNePPtFwbHbqhdnyZAcC3i1WqMsnAacIqD88HNuhipKXF-c_UzfPqb9jXn6duVeAfYlJ4tFHgu668Rf1nJhdWEIKx5DEjlrLpfnbAaYLb3GXWw~', 'POST', '2026-03-09 14:47:38'),
(292, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=91YQX08gn2_IY7YVK5ni6Ub3tFQz2bke_xRgzRGHaXFNeAxIlcxKuoN276pDtxCU585V5g-zD3qlzO56UoZeBDGws137-rFYkc-cbMyhrmLPRvplHp1roOnc', 'GET', '2026-03-09 14:47:54'),
(293, NULL, NULL, 'Core', 'seguridad_usuarios', 3, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=wdSGx0fwjd5rxHm31K1Q0Tly9Art6XUAItWIiXanmKabz1Fka4iTWxA2h25BwuKJGh2tnZ6KFMmQVp94FkbyV1ltzNHN5rHzUKe6iJ_fwa8P0MKY4N3FQ7XELfxRyMq5', 'POST', '2026-03-09 14:48:08'),
(294, 2, 3, 'Core', 'seguridad_usuarios', 3, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=wmOyGkreF1dH6ea7vud889Y6wkLxm_B15GzPxldVvnguAHH3PlqU9KDcdaZShRbkuMNkGBkVFkFgUhLf1YedEqEceg4IyevEdy_J3LSs2SjxxEjykdpUgGsA', 'GET', '2026-03-09 14:48:34'),
(295, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=rtThgHE23d0cxaPbZrHafu-jplg6BisGujT9s1yX4qf6WwgXKh977UUf3I2tdMIgZ3hdGQ9e0DS20YSjw2Lf37TUTg6oNXYS1nhkM2Yb8sPctGso5r-eKx-3hSbw8MiN', 'POST', '2026-03-09 14:48:50'),
(296, NULL, NULL, 'Core', 'seguridad_usuarios', 3, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=yB24tKB7sHja4G29WTV_QcmDql-XrRABNjRjj7xJuhLs19utT2wVr_9y-HRPWQ6jYs2PUWPESRFfvlQPbfMsQKswWs_A50buEGMJZM5mSHUr4KrsUN4U57riS4s3_5wE', 'POST', '2026-03-09 15:13:57'),
(297, 2, 3, 'Core', 'seguridad_usuarios', 3, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=RVn59YVgOfRlqLOVZqaBpt8-XsWLCoLDqMi0zx61HhKENDGolGrsGuPAr7kCo69zTSnc8tuyGzdB3Ss8q9nZDGDR7yy_lF6T5mr64IZQLJ1-G1QrezNIgwQX', 'GET', '2026-03-09 15:14:20'),
(298, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=CgreG5kVyaqDceqg_yx6K0CLM4DpoekV1HGKi9KWPb0s7MsJiAfr4BdkwAq4SW3vShi7cFo2gTuamcObMmM8OlzOC4JJhqdSSlMW0ZuAr7xoAvY-IU7fm_YsFz4x9dtx', 'POST', '2026-03-09 15:21:02'),
(299, NULL, NULL, 'seguridad', 'usuario', 3, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::NA08Ec7QeL8W1ku2W01Rd7S5FRXFeqWE4dhFRXceQ7YlrluwIecCkUzrmeDQjeCR\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$S2wzZFc0cGFpaUouNXRCcw$CyDE4Ij59TlCNt5rs9Srt1Z5My2VtSdk7NmlO8yiq30\", \"usu_telefono\": \"ENC::dEzreJmwkraQ5+gftOagZKC+fikdtpv4tUl+UhNp0hs=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-03-09 10:13:57\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::LHfp7PrKGCNHTJTxZv9q54J0HIxp9FMomG9D/Sh5r2o=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 1, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-03-09 10:14:25\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::OuTanmFKzVH5LVMTG9MWE8sZvWbnMYRgodpoM31cKSTvNTm2YwKpFoz4H9gtfpGD\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$S2wzZFc0cGFpaUouNXRCcw$CyDE4Ij59TlCNt5rs9Srt1Z5My2VtSdk7NmlO8yiq30\", \"usu_telefono\": \"ENC::Q4D4c/9Gy8HEFSZ2owLDEHnb2H7o3ysJEQZbJua1C9c=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-03-09 10:13:57\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::ZmDa3G2PGfypPPh084pETrQou18KVHJ/SDYl2EOmzcQ=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 1, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-03-09 10:26:20\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=Lav0UT4xOPD6TJjhUZxQSUf8t7gowFJDp4uInJuuZG4z52cLrHlXTMe9Qx7zikzoz-HGsoDTHr9CW9Dx8rbUI1RciFPu72ZE_m6VjkqXNbfukfGKB7f-5h87yHYl4YRnynEwBennRQ8~', 'POST', '2026-03-09 15:26:20'),
(300, NULL, NULL, 'seguridad', 'usuario', 3, 'editar_usuario', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::OuTanmFKzVH5LVMTG9MWE8sZvWbnMYRgodpoM31cKSTvNTm2YwKpFoz4H9gtfpGD\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$S2wzZFc0cGFpaUouNXRCcw$CyDE4Ij59TlCNt5rs9Srt1Z5My2VtSdk7NmlO8yiq30\", \"usu_telefono\": \"ENC::Q4D4c/9Gy8HEFSZ2owLDEHnb2H7o3ysJEQZbJua1C9c=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-03-09 10:13:57\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::ZmDa3G2PGfypPPh084pETrQou18KVHJ/SDYl2EOmzcQ=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 1, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-03-09 10:26:20\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '{\"usu_tema\": \"light\", \"usu_email\": \"ENC::lPjwchXnjfYtM3x/w6LV1c4ZJOi2zQD+2GxptRITiLuHCAA/eUz0mP/qygGoHNDI\", \"usu_avatar\": null, \"usu_estado\": \"A\", \"usu_idioma\": \"es\", \"usu_rol_id\": 1, \"usu_celular\": \"\", \"usu_nombres\": \"Bolívar\", \"usu_password\": \"$argon2id$v=19$m=65536,t=4,p=1$S2wzZFc0cGFpaUouNXRCcw$CyDE4Ij59TlCNt5rs9Srt1Z5My2VtSdk7NmlO8yiq30\", \"usu_telefono\": \"ENC::kQxrCU/qWLYGnTzgNPzpRAIznHq+39WCeEQruprq2Cw=\", \"usu_username\": \"fbpinzon\", \"usu_apellidos\": \"Pinzón\", \"usu_tenant_id\": 2, \"usu_codigo_2fa\": null, \"usu_email_hash\": \"fa2536059c2cfc78fe680f0629a1859d\", \"usu_usuario_id\": 3, \"usu_intentos_2fa\": 0, \"usu_requiere_2fa\": \"N\", \"usu_sedes_acceso\": null, \"usu_token_sesion\": null, \"usu_ultimo_login\": \"2026-03-09 10:13:57\", \"usu_fecha_registro\": \"2026-01-29 17:22:55\", \"usu_identificacion\": \"ENC::0U+LS3T2LOMHX2l/jl/qL4nVQLTEjcKeon2MYNgcpGc=\", \"usu_bloqueado_hasta\": null, \"usu_ip_ultimo_login\": \"::1\", \"usu_password_expira\": null, \"usu_codigo_2fa_expira\": null, \"usu_intentos_fallidos\": 1, \"usu_sede_principal_id\": null, \"usu_token_recuperacion\": null, \"usu_fecha_actualizacion\": \"2026-03-09 10:38:10\", \"usu_identificacion_hash\": \"46e867782d4667050ad7bf37c46a7107\", \"usu_notificaciones_push\": \"S\", \"usu_permisos_especiales\": null, \"usu_token_sesion_expira\": null, \"usu_notificaciones_email\": \"S\", \"usu_debe_cambiar_password\": \"N\", \"usu_token_recuperacion_expira\": null}', '::1', NULL, '/digisports/public/index.php?r=Zhu_45ZlBDNQXbi1_0FjqT3ytoCN_DuzwQn3YH-KNas58LvNM1QLZySabZLAaj6TFnXD-d4P8UuB3O3emMDv9-MrGWUNdxYRMfRC163I9mlpDOQh1kfOh6uA6FULoms6cDP04d1Nyac~', 'POST', '2026-03-09 15:38:10'),
(301, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=xzDqzXBDgjSlhXKuEuwHM_ZVmZ9z3XbOmiVIznDfoVw7Kg8-zTfonYt7atqbSE07VlmQJ94p_8jnlP5SGuQnzY8QjWNjp7iMSqS_RZQWQ824hPZHC0SsNUT9VTT7VEFG', 'POST', '2026-03-09 17:27:14'),
(302, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=n0IdMh9UdboEADvJwnx2Px1-MTXw31NZ0_YLejjr_OFqWJazKsIWDcG4Wog74TJqsn9MbhHv96OKLE3fQeXSw9189Exf7n9soxDAH3IZsICdkLQ6kH_YY90wdBtq-QjP', 'POST', '2026-03-09 20:22:07'),
(303, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=2zhV7sFGeAWHy-XKml8NhGqdZWcDTRH6LDF_XFP9hKOay_DvE_31J9fpBZmq_YOVC90NF2jHbnMhlvrhgyVyVR4wkEKQ9lSDy_QYckOtUp2AqV--23xLhRqRDpiZgPAC', 'POST', '2026-03-09 22:52:27'),
(304, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=05gt8P_y6i12aCflxPfT2folQyIE4SsLQ83jUmQSKEukva4C2_s4CBqISzH437C7Ihif3dKdpR5-4f8XWZJSQ4lJ1Gj66_pOOvZVTc__D9CaBmroRsMsE7-5PDH29ppd', 'POST', '2026-03-10 00:40:26'),
(305, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=GsnISbPmorywY_Q8QHF2LcZzwo2zLZ28TXi1qVEs_QOSBiNBB8vC8gpz0y0T0Rdr8YEL-q36fEJ0OJhd7BxYl3XlCTGxs0cox_adBFUQx-2X4CaFdJwH05NO3mCjcs5k', 'POST', '2026-03-10 04:56:30'),
(306, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=pLhNkJUXP63MmoAt6dJ_cmjICdVcb19xWFlxHlHKKYSEUvUx7rIrCpEzlBNzh7a3IGU3FKLHRNa6qEPcEwpEhR29x5V0GHxccp7n_ie2Sj7Pt_6owMoknBrOIiS1yVcF', 'POST', '2026-03-10 15:14:10'),
(307, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=tpoWVe25-lKE8YGvFnLzW4MPN2DWZhRM5xfRw-rBnsCDTb4OlWzQ3UqpNALByIkmQhQ3KH1_SxAfunBdQk0GDYQ_tjWRCkfqowy88FAiafVHV-B484xBOKV1OBThVCCP', 'POST', '2026-03-10 16:03:16'),
(308, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=RQcZ-2ZS-cMLbQsiRSBFw3cq3ts_sNLFboAuLALmhOPHq5IdoyANbOxUGV7S25rOdm-AbqYhwltmYRXIyAhmR-QLEdJ_xumgSNCEwazo115rvKTdkbfIDvV8Yl5OA7CI', 'POST', '2026-03-11 04:06:27'),
(309, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=uwyIKSu1Eoht7GG6Obra5hhVB0hU123UZKq8Cv-Qgo1ZZ3OQCYm8oKIDd3oJNPxRmeMmfArPWoZVfyUwnYxAgExgV-PpkCdFWfS2Rl4lIyvisMQ6C75HSukIt1s_AH7H', 'POST', '2026-03-11 05:26:31'),
(310, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=f4_r31YH52SaWAsW7jU81eQkV_QWCtKrr2HyXqkQEnhYoUJLgRjLirL_MVwYqcoMPyDXtpLuhpTBk7E-IqFyicKdWWPP4W7vQVpGsM7zxM3vj17kyz6Dz4Ys', 'GET', '2026-03-11 05:31:14'),
(311, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=n1WZksIBhuAt9EqAy9lNtkTgOfB3w1aZpjB94iIQTORdOHL91C-HfyGn4MNJ0k_QLb_AoCvPu0IHXyIUaQDDAfLYJtqhfZ7RQ0C6KwzhSYWS09cQU3_R9SFJ8jgSWrsf', 'POST', '2026-03-11 05:31:25'),
(312, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=mM1h23NfouVX2YaqCMxAAQ1QJ1DEYUME1xPB8sRxLMzC8KhUPCBN-r0Opam9MfmIRRh8jD7160ZfqqK1oBFb_9uVjm2UCvSGBksQja00y1Yk-LrD0mFWxxAYaVIWOfPU', 'POST', '2026-03-11 13:46:01'),
(313, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=EZ-s_GP3xN2DBPgNFubV7wOplodYvTQnvZsNFfjXJkBGN5JvwI9diLHnD86WX0E2r82lrhX5rX-QgH64KP7b3Wvg15fhf1av9iOG7yXEyfwff7YwigK0i1W9RPHqhuvF', 'POST', '2026-03-12 04:13:01'),
(314, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=Ot-VV_QfWhKA5XbqfnkCWwAjyzyIrdV18Di3gBGhSjQqdH64BGMNXIK-3SYD3KJ6wZmwDurkEkp7XI-ctt7-xraNgyKgF0cvK-beaWoie1lr4h7w3scA5nwM_-whhrpg', 'POST', '2026-03-12 13:45:13'),
(315, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=A3SLW-tqwHkLXr7vODM0PZnWSEmRBuJT-3vaNtOCLNY0QmjbBMDCGZMULcpqxnObRVNlq8UNbHqmo9gEi9IjmrOQX1mYLnvoT6XtzUP-3wD1LdtLTaYD-CmSzc7eS5sE', 'POST', '2026-03-12 19:25:43'),
(316, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=kWiDlGBRLrX9h_i_ibFSHxiMWFwpGPrw-HKSY2ZTVAmLgwCxeKGjgNdZg0Q2H7bwEKFLze6rzrm8wnvSEP_MObKwsZTUO7G-GXjlWMuK0FYi0zeqoEz6QnQL6vxBv8LA', 'POST', '2026-03-12 20:21:54'),
(317, 1, 1, 'Facturacion', 'facturacion_configuracion', 1, 'UPSERT', '[]', '{\"cfg_ruc\": \"1103345292001\", \"cfg_ambiente\": 1, \"cfg_razon_social\": \"CHAMPIONS\", \"cfg_punto_emision\": \"001\", \"cfg_regimen_rimpe\": \"NO\", \"cfg_agente_retencion\": \"\", \"cfg_direccion_matriz\": \"Av. Principal 123\", \"cfg_nombre_comercial\": \"Champios CF 2013\", \"cfg_secuencial_inicio\": 1, \"cfg_email_notificaciones\": \"fbpinzon@gmail.com\", \"cfg_obligado_contabilidad\": \"SI\", \"cfg_regimen_microempresas\": \"NO\", \"cfg_codigo_establecimiento\": \"001\", \"cfg_contribuyente_especial\": \"\", \"cfg_direccion_establecimiento\": \"Av. Principal 123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=IV8DuvL-9_sEgBc_7P576tlPSm9WUo7ITTvFyEN1_eQN_SYaC0o1u6fgGdYFe6HUpsKqjm2S3dABiINF4c_6IoxkRCmJRM3h7VFV44yUrbECMTH1p-4Rf_EGv6vRb1JeOE-taoMZfVZdKyQ~', 'POST', '2026-03-12 21:08:15'),
(318, 1, 1, 'Facturacion', 'facturacion_facturas', 1, 'INSERT', '[]', '{\"total\": 81.53385, \"numero\": \"001-001-000000002\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=osynXrHBa31uUJS9ATB0IXfZYAKBwFfQqqT4svCJT56N8F5qrMNm3QUT11l_w-GqyG6oyMcVP2Qom5Ex778TjQ1sB1NsyqT5J_DeleUCYRAvAzst_dYb2r3uGXp6XyP7BHomj0Q~', 'POST', '2026-03-12 22:37:12'),
(319, 1, 1, 'Facturacion', 'facturacion_facturas', 1, 'EMITTED', '{\"estado\": \"BORRADOR\"}', '{\"estado\": \"EMITIDA\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '/digisports/public/index.php?r=0BH1_zVa-zIB1ccaLL5ipu5T_vY_tnZyqAnzDCT9LiewJbhpIPReO7adrUhKU6AOjGzY1sYxN2VcfwZs4Bp3RzrakSi3B3FEZmXPO0R0sSt1I-_2z1qoBo2EHoGoGGaecJ_7GHC87IWgNg~~', 'GET', '2026-03-12 22:55:33'),
(320, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=UDYBNS8lNL2OykkkPgWXrNPgenzLCo6Xv3PI8A4n1OeNYpYbCXx8QicbAt3khy6pzxvwyS7vIs0sZsnhMD45W2iKrVR-XiREmmctcLbE3qkneGCGtA3ua9cMX1_Rxj1s', 'POST', '2026-03-13 16:09:12'),
(321, 1, 1, 'Facturacion', 'facturacion_facturas', 1, 'VOIDED', '{\"estado\": \"EMITIDA\"}', '{\"estado\": \"ANULADA\", \"motivo\": \"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=A-uY2kBxdGqy16ISkLV66H8IUEPbTbqyavxGp-XPclC_psYxNuOBv4ZGDRyjTkLj_zvExn1yqBadEMWv2urXiIWZLgBHRt1-rsH43-5Utv9lHIUWDOtYYvPekTBGNFr28CyXgMo-SGlAwQ~~', 'GET', '2026-03-13 16:11:31'),
(322, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=_6k5hUil_PYgza9kb-vwOC3SN2FvT11zL-NsNbBK0ES_R4ApqWP5A_7ctRpUQKZ8V77vKGkvEal8owxkkrBYreBDfhLT34eZ3Va3PHgQCIJfRcN7JGu4aDDS0R4Q_Vhd', 'POST', '2026-03-13 22:00:17'),
(323, 1, 1, 'Facturacion', 'facturacion_configuracion', 1, 'UPSERT', '[]', '{\"cfg_ruc\": \"1104015282001\", \"cfg_ambiente\": 1, \"cfg_razon_social\": \"CHAMPIONS\", \"cfg_punto_emision\": \"001\", \"cfg_regimen_rimpe\": \"NO\", \"cfg_agente_retencion\": \"\", \"cfg_direccion_matriz\": \"Av. Principal 123\", \"cfg_nombre_comercial\": \"Champios CF 2013\", \"cfg_secuencial_inicio\": 1, \"cfg_email_notificaciones\": \"fbpinzon@gmail.com\", \"cfg_obligado_contabilidad\": \"SI\", \"cfg_regimen_microempresas\": \"NO\", \"cfg_codigo_establecimiento\": \"001\", \"cfg_contribuyente_especial\": \"\", \"cfg_direccion_establecimiento\": \"Av. Principal 123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=R1ZOW1Zns5EHspiDF0ZJHFM5JuOR-VlmkwtbfepR7yFlxpxETY1K-IEW1MCXpX1VZDtPaSVVcRcxkvEKwd0yDW6WRZ7d9BL25V8o0DFfVg875X7Wpd3ofti4AQmYKd6NlhsJKUJhsWAlLhA~', 'POST', '2026-03-13 22:02:40'),
(324, 1, 1, 'Facturacion', 'facturacion_configuracion', 1, 'UPSERT', '[]', '{\"cfg_ruc\": \"1104015282001\", \"cfg_ambiente\": 1, \"cfg_razon_social\": \"CHAMPIONS\", \"cfg_punto_emision\": \"001\", \"cfg_regimen_rimpe\": \"NO\", \"cfg_agente_retencion\": \"\", \"cfg_direccion_matriz\": \"Av. Principal 123\", \"cfg_nombre_comercial\": \"Champios CF 2013\", \"cfg_secuencial_inicio\": 1, \"cfg_email_notificaciones\": \"fbpinzon@gmail.com\", \"cfg_obligado_contabilidad\": \"SI\", \"cfg_regimen_microempresas\": \"NO\", \"cfg_codigo_establecimiento\": \"001\", \"cfg_contribuyente_especial\": \"\", \"cfg_direccion_establecimiento\": \"Av. Principal 123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=R1ZOW1Zns5EHspiDF0ZJHFM5JuOR-VlmkwtbfepR7yFlxpxETY1K-IEW1MCXpX1VZDtPaSVVcRcxkvEKwd0yDW6WRZ7d9BL25V8o0DFfVg875X7Wpd3ofti4AQmYKd6NlhsJKUJhsWAlLhA~', 'POST', '2026-03-13 22:03:04'),
(325, 1, 1, 'Facturacion', 'facturacion_configuracion', 1, 'UPSERT', '[]', '{\"cfg_ruc\": \"1104015282001\", \"cfg_ambiente\": 1, \"cfg_razon_social\": \"CHAMPIONS\", \"cfg_punto_emision\": \"001\", \"cfg_regimen_rimpe\": \"NO\", \"cfg_agente_retencion\": \"\", \"cfg_direccion_matriz\": \"Av. Principal 123\", \"cfg_nombre_comercial\": \"Champios CF 2013\", \"cfg_secuencial_inicio\": 1, \"cfg_email_notificaciones\": \"fbpinzon@gmail.com\", \"cfg_obligado_contabilidad\": \"SI\", \"cfg_regimen_microempresas\": \"NO\", \"cfg_codigo_establecimiento\": \"001\", \"cfg_contribuyente_especial\": \"\", \"cfg_direccion_establecimiento\": \"Av. Principal 123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=R1ZOW1Zns5EHspiDF0ZJHFM5JuOR-VlmkwtbfepR7yFlxpxETY1K-IEW1MCXpX1VZDtPaSVVcRcxkvEKwd0yDW6WRZ7d9BL25V8o0DFfVg875X7Wpd3ofti4AQmYKd6NlhsJKUJhsWAlLhA~', 'POST', '2026-03-13 22:03:14'),
(326, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=dNuCFB9WJaEAs-CcmlVta5sxwckG48CrhHVqO3sHved1v8n9v8zPSKmLH5negYa9xhUGbo0MG1fImbCG_FA3pGU03EUld67ffza7QYqk-axyEN8nHSFCIf7enUN4cGjv', 'POST', '2026-03-14 01:13:39'),
(327, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=HCpxD5HNM5qj8XAlMlZONKpUuuxW0rTl1WwBQ2zrByQBNusiX3qMc0BgVWR89HSKZc1P-XusULxzcxRWMsvdfiFzJsvq1OewFLhGE5wwEl808smquB1PoWABj3HxQCCK', 'POST', '2026-03-14 16:08:45'),
(328, 1, 1, 'Facturacion', 'facturacion_configuracion', 1, 'UPSERT', '[]', '{\"cfg_ruc\": \"1104015282001\", \"cfg_ambiente\": 1, \"cfg_razon_social\": \"CHAMPIONS\", \"cfg_punto_emision\": \"001\", \"cfg_regimen_rimpe\": \"NO\", \"cfg_agente_retencion\": \"\", \"cfg_direccion_matriz\": \"Av. Principal 123\", \"cfg_nombre_comercial\": \"Champios CF 2013\", \"cfg_secuencial_inicio\": 1, \"cfg_email_notificaciones\": \"fbpinzon@gmail.com\", \"cfg_obligado_contabilidad\": \"SI\", \"cfg_regimen_microempresas\": \"NO\", \"cfg_codigo_establecimiento\": \"001\", \"cfg_contribuyente_especial\": \"\", \"cfg_direccion_establecimiento\": \"Av. Principal 123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=L2NRjXAjGS8vvjNChEnw4oiD1DcOFBKhwDLYw0SxDzqNzGF5ajmEer-6DGEUJL1wZV2jSc60lTkCsPUnjl8l2qtQ3CdZ4PJmG3VF5y0XlL4NSAYo5PgdVVML__ExdkteG95eAycF9DZts34~', 'POST', '2026-03-14 16:37:18'),
(329, 1, 1, 'Facturacion', 'facturacion_facturas', 2, 'INSERT', '[]', '{\"total\": 34.5, \"numero\": \"001-001-000000003\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=n-FZfm0lmtPUyq5YCd5XN0-vevleCQLl0IjR8pAylYoHb69TrWhZpKkYALF9a0f2osjVgb3YdPjpsZnjyPvUeNQb4vZk5jG_nWvSK0ErDBxiYf7wVr3x6mlGFy2O5KvP9303UrY~', 'POST', '2026-03-14 16:53:26'),
(330, 1, 1, 'Facturacion', 'facturacion_facturas', 2, 'EMITTED', '{\"estado\": \"BORRADOR\"}', '{\"estado\": \"EMITIDA\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=oXFRJMx6pllccmgOPaV-hcvgriQibXBC4iQ3xUSRVSy4vKOWC04l1eZ4koxZb-QqQf8QAWrXqoBW4-RIS6J8u8QDYKR5uPW5gT5oc4f5wQzx7qsUf1aSaMajJZg-L3JRwpGbBmoFFSbvcQ~~', 'GET', '2026-03-14 16:53:49'),
(331, 1, 1, 'Facturacion', 'facturacion_facturas', 3, 'INSERT', '[]', '{\"total\": 52.647, \"numero\": \"001-001-000000004\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=3KE-ecxlL39MlHAUfZfWJkrQSF_7vBis-MEZKMlkb6kTcvzpltwv37r5-s_7gWiosk7nph2ygnSjEfdT_BahVmq7oCdq7eR3wwTo6PnJq8jUOPNlpqiDm2hGXzwjxa2hthAUVtY~', 'POST', '2026-03-14 18:27:37'),
(332, 1, 1, 'Facturacion', 'facturacion_facturas', 3, 'EMITTED', '{\"estado\": \"BORRADOR\"}', '{\"estado\": \"EMITIDA\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=4LTHvWPBvmqMUF9Iau0DsGgqtUgwZ9RR5HfIyAZ97V1aHnDjd62n5N4HHe8qVUbbKK9BQezOadrIF-ANxrSJ5V74kT01mlq73Ktl7tbufra4Pgzms0nJqRlk4PqXKu1kNNaBAogtGqo0BQ~~', 'GET', '2026-03-14 18:27:45'),
(333, 1, 1, 'Facturacion', 'facturacion_configuracion', 1, 'UPSERT', '[]', '{\"cfg_ruc\": \"1104015282001\", \"cfg_ambiente\": 1, \"cfg_razon_social\": \"CHAMPIONS\", \"cfg_punto_emision\": \"001\", \"cfg_regimen_rimpe\": \"NO\", \"cfg_agente_retencion\": \"\", \"cfg_direccion_matriz\": \"Av. Principal 123\", \"cfg_nombre_comercial\": \"Champios CF 2013\", \"cfg_secuencial_inicio\": 1, \"cfg_email_notificaciones\": \"fbpinzon@gmail.com\", \"cfg_obligado_contabilidad\": \"SI\", \"cfg_regimen_microempresas\": \"NO\", \"cfg_codigo_establecimiento\": \"001\", \"cfg_contribuyente_especial\": \"\", \"cfg_direccion_establecimiento\": \"Av. Principal 123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=KIuUcZuIOjhRPergjMbuomS4THW6ArS39sI1XWfYLD1bjTeBx1hPj9a0GbHImY6FvayNMBl0bXjhKOjM5IOaoSxCybNCXRsSbnijPjGVwvLNsZrbIkoClxnKevJAqGiTX0gB8nLA97qOI9Q~', 'POST', '2026-03-14 18:47:26'),
(334, 1, 1, 'Facturacion', 'facturacion_configuracion', 1, 'UPSERT', '[]', '{\"cfg_ruc\": \"1104015282001\", \"cfg_ambiente\": 1, \"cfg_razon_social\": \"CHAMPIONS\", \"cfg_punto_emision\": \"001\", \"cfg_regimen_rimpe\": \"NO\", \"cfg_agente_retencion\": \"\", \"cfg_direccion_matriz\": \"Rey David\", \"cfg_nombre_comercial\": \"Champios CF 2013\", \"cfg_secuencial_inicio\": 1, \"cfg_email_notificaciones\": \"fbpinzon@gmail.com\", \"cfg_obligado_contabilidad\": \"SI\", \"cfg_regimen_microempresas\": \"NO\", \"cfg_codigo_establecimiento\": \"001\", \"cfg_contribuyente_especial\": \"\", \"cfg_direccion_establecimiento\": \"Av. Principal 123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=O2Ut4NVjk8Vno0vYtYKZIYaG88lBqyZ2_SRjFSlMeYG1NCuMCerCcTWpCXLITTQ9r1Y3-K1b3etGBtS1dGtcJhLoxzbEeRxjva75KwWGfKIG_RDDJOxa3ceNTn5xRERqED0IlIjZxMBzxeU~', 'POST', '2026-03-14 18:48:40'),
(335, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=huIdt0wlydo6SScBEacwuu1NyM8ZxkeSjBpcmwphoOLScgVLf-G2Dmywp9eWVcYV5DdWtNflANRdTdn1iHYVVXAOBwIOHn1zIn5NgO7MW5XQWL2Hu56HtBnQMjuWQiSS', 'POST', '2026-03-14 23:55:25'),
(336, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=qJgvfGFGF7UtCiX0LG-2-MuIb81SnZuKQehulvZgNHGlxBjqYDwL3pVGUW_mvbrK8UqOGUQSz8TOI6lTyQQqxzTZSdt1L1iooC-_ivP1iHxe3oM2df8NJ_bkssfaD32B', 'POST', '2026-03-15 01:06:15'),
(337, 1, 1, 'Facturacion', 'facturacion_facturas', 4, 'INSERT', '[]', '{\"total\": 166.75, \"numero\": \"001-001-000000005\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=7N-Jt5bDezdaBK1Lm67phIFgKp9zgRmd1tVYUn-eNyQvDNMrAOMbPspHynB3SQ7aatIZORNiE_vEPpZzkPVRW24BBqyTApQgphrjadcr6U4aC8K7DQvHNoDLWzmAP_MgxvbJm50~', 'POST', '2026-03-15 01:07:24'),
(338, 1, 1, 'Facturacion', 'facturacion_facturas', 4, 'EMITTED', '{\"estado\": \"BORRADOR\"}', '{\"estado\": \"EMITIDA\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=CL4szEvfA8oOfLrVU8No7vdTtC9FNKEX3eNguBzC66oAmbB-jUSpHGsn92OE7MZTJhw_J5gytPNNLT99UHmxzB_aOzV37K5lXW2HKi4MP_U2hr2DQI1jBRe6WTkJ-ble5cptEChfxWn5wg~~', 'GET', '2026-03-15 01:07:38'),
(339, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=cbHZ9Vnxdrzv-hUHPQGATwPRCibhY2Ijr4ULwNFKsbJPG5lPu48wRog9Lt9s_PF3wVLSyorZJfhkp7oOar5xoiFLz2tBXCW1XS_KA_VLUDAFeu9LTCTfHn0HBwhPw93H', 'POST', '2026-03-15 01:50:02'),
(340, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=r3Mn95Kf_yxRX3OZxQqsCWM1CDYeNTj-EEGT93X10qJL-w21u25H5JD3ZxLCRp1V9Flfb9dsGE5gken7DoFA7kfLOwRAErv193DcNkjQPhC0y6tzRDqiJaWIUsr6p7Yf', 'POST', '2026-03-15 04:37:46'),
(341, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=5D1jlidlRf2YV5jLIlzHZzLc75HPa305bDIofnmJvuTH1CAZqi8kjh6mvacBBzwgyEmwZUtvcsFgHiLiER29DheQUyfCXUdCL4lB_QjJzHu8LeKvRvxrfvvGCaeeWCxw', 'POST', '2026-03-16 00:40:31'),
(342, 1, 1, 'Facturacion', 'facturacion_facturas', 4, 'VOIDED', '{\"estado\": \"EMITIDA\"}', '{\"estado\": \"ANULADA\", \"motivo\": \"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=rLnzmH_KYnKwgG1BRA8kT2HZn2dQWcec5BpXzwkJ7ykQiY_PcAbOlj4X5zANS9IBIk3r-Q2LTitKfQGnaeS-N1e26UVysiyDVRGaYyLLqpJ7C3l6FKttYs3cE1R9C3XDCtHZ5pGcz4qbWQ~~', 'GET', '2026-03-16 00:42:16'),
(343, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=6qmgYkMh9nlOiuzcC7SMBDbszgAYh0tcF1pWCgRcgR34vFx916rYcLNwvqa1NSb13nrWwnFDiColqtAyF3R5Qhv_9MYQiKmUVer4eb02932zsQdGYNr2esXoyyKodqP3', 'POST', '2026-03-16 01:36:46'),
(344, 1, 1, 'Facturacion', 'facturacion_facturas', 2, 'VOIDED', '{\"estado\": \"EMITIDA\"}', '{\"estado\": \"ANULADA\", \"motivo\": \"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=TFuE4jh62fxh7h3vAubYmejG4HCt6SXyJ4M72ssuK47RBkCR3AcpTwzV0lSf67EjZF4tJlj80o60YdZu0MVjiNskGvaIV9MuZELH_TXMtNIuMAe3IrHHURl6SuK_NeouRTp1kErFx_U2Ug~~', 'GET', '2026-03-16 01:38:53'),
(345, 1, 1, 'Facturacion', 'facturacion_facturas', 4, 'REACTIVATED', '{\"estado\": \"ANULADA\"}', '{\"estado\": \"BORRADOR\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=eTHqAccj6be-82PnkqUm19ykMdYgKUCjDpRpiU6YpZXLVG3wr_XKmkATWUsmqcxnN8vqhsjY55Ntc-SisbsY00BO-OknoF3OawWrM7iHLm5lJoy3g8OYCTy_BntndI85zIbqCqqPfErG6PJeQg~~', 'POST', '2026-03-16 01:56:30'),
(346, 1, 1, 'Facturacion', 'facturacion_facturas', 2, 'REACTIVATED', '{\"estado\": \"ANULADA\"}', '{\"estado\": \"BORRADOR\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=tqpxppxQZgh21h1l2d1RHY9SIQXLLXFTjdE-CdD01VYSg8z3QsWVs-NT6tQ7fCVT7_qvvnjUS4JPei26RQ8LJeFDSmNAG0udGQL3Ibxxm-GVtT7A1XxTaG3TmndRb4e7w6CRS-1KxKw3qodCOA~~', 'POST', '2026-03-16 01:56:36'),
(347, 1, 1, 'Facturacion', 'facturacion_facturas', 1, 'REACTIVATED', '{\"estado\": \"ANULADA\"}', '{\"estado\": \"BORRADOR\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=Jzr_fuC-praSfDZ2lzg5NvYm6q2JxYk-rFD76h0ztB-G53BN3sTbtYQ_QAfT-EsNW6J2_JUEPziaWmN-H1k0EXCdDVYQHi3R1-_EMIurjskTF1q2c2U_UFZuRkc19R6FQnaWr32LlfWKAq-FFA~~', 'POST', '2026-03-16 01:56:41'),
(348, 1, 1, 'Facturacion', 'facturacion_facturas', 4, 'EMITTED', '{\"estado\": \"BORRADOR\"}', '{\"estado\": \"EMITIDA\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=xMatEbH8VWzgFZWgxkhe7Du51UPHTBOYZiAn-42I025dfU7opG5_QV-K440hAOUBaFk25QzHqdrtE5Mhvc0Ul8bXleSUrpYlOKFr4V8jlOXKCfXwCjkgPB2oI_P6CGE-AqSuC9-PbVfjvw~~', 'GET', '2026-03-16 01:56:53'),
(349, 1, 1, 'Facturacion', 'facturacion_facturas', 2, 'EMITTED', '{\"estado\": \"BORRADOR\"}', '{\"estado\": \"EMITIDA\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=BBYR1IfaeHnh9UshpKFXQLNHWii0TfdgtAmf0ZXdc4etc_E3PAFeatDR7SSnpRzVfH0ECira67lzF1w4Rmrd4z14djIaHSJjGCo_R56xOO4zjJWgDqQ2Pg2z0eFrrU6HEiRlno8g4MTxRA~~', 'GET', '2026-03-16 01:57:07'),
(350, 1, 1, 'Facturacion', 'facturacion_facturas', 1, 'EMITTED', '{\"estado\": \"BORRADOR\"}', '{\"estado\": \"EMITIDA\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=BZdjA8kaWYABgaY0RB5Km1o8HGbxbdVUFkjD--ZTqmxwkfmqnM9xv8rg1x0HLcN5srCpQlI2fUbWSz3rTYb-276uioCSVNJj7q0PpGU2ESYiPYDSXgBm96xiR7cIHdqBBE3sDTUfiP4T9Q~~', 'GET', '2026-03-16 01:57:17'),
(351, 1, 1, 'Facturacion', 'facturacion_pagos', 1, 'INSERT', '[]', '{\"monto\": 81.53, \"factura_id\": 1, \"forma_pago_id\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=_RsrpuEIhq9rmeRP1HHKAFKV4xorRZ79SAzdCDUEtaWptM_JUz2y_BJVFrEjI-MEgpnzTZYR1AdmHB_7yXaWqgFfumQjtTxvESWXnoKcERA1mF0nA7FfLs-Cma-xiRpyy1U~', 'POST', '2026-03-16 02:01:09'),
(352, 1, 1, 'Facturacion', 'facturacion_pagos', 2, 'INSERT', '[]', '{\"monto\": 34.5, \"factura_id\": 2, \"forma_pago_id\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=j-nAyEEoatc1prJQd2G_ayY08e7reWI8pi9bXvrTIhoS7QAF_oVl0PsASqIycmA5cbD2bjz5ilOhyegFVq9p5Pn8mKXJIMu_1ytpKPFtxvHlzu-Jv-rRbj-cDDxUZuWziwU~', 'POST', '2026-03-16 02:02:09'),
(353, 1, 1, 'Facturacion', 'facturacion_pagos', 3, 'INSERT', '[]', '{\"monto\": 166.75, \"factura_id\": 4, \"forma_pago_id\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=AjAhabXNWSNyKhZej63YeSt9TrgA_XH_a1fBFQk4l73sHrO8ShKg376hhhj_eNgHY-BbnfTH2-kl527a6fazENl5B5Cv6DM5Nz3VxmPiTWGe56gw5RshhNe0yGq6U63os74~', 'POST', '2026-03-16 02:13:18'),
(354, 1, 1, 'Facturacion', 'facturacion_pagos', 4, 'INSERT', '[]', '{\"monto\": 52.65, \"factura_id\": 3, \"forma_pago_id\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=-RrIok2EQswYwNur1WqusdkyX4ciwdVdwwPktH_XP8Ymy7vIqNLYaDA4JRde-gqn12ZZ4NclLyVd-wJVT8mqKDluXodEZK_rtlipam5Q0878A_X6HGNHH3WoPordgnE9Er0~', 'POST', '2026-03-16 02:13:57'),
(355, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=cWA2zwLg76Cs5mwmcMk9En2LspNpc3pESsYfl4EQHsdNXvfn_oAW6nSmsLF63NgHfXrSpAnFEGl2mCGllcmjl6jxRHZIPKOAt_U3s9DX2Vinb5itJb-I5LBj83Jqp4ne', 'POST', '2026-03-16 02:47:47'),
(356, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=D86aaQA6QE5PaAxjgV7sv5J1oy5J8kKDRiM1Yh1bVcTed4Ybid95Gbc5Xq5T1ocLIjM2LVXUP7Hj0ijvL8vLIVpom46igXWmPEbDDacRcCD9hWn6onzPhoL9', 'GET', '2026-03-16 03:28:03'),
(357, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=lIfb2IMxsWob3KaFysJ1G4eDYhc5qLgtj_OU2l8bB5eW5jxC--mFPUgqCfd0qJM2Y7J4GRhg9jCAaDG4V4W7uwprxIMU_fhoLQXhm36keSnfI0bj9wY_ZiwI9ap_wfZ-', 'POST', '2026-03-16 03:28:14'),
(358, 1, 1, 'Facturacion', 'facturacion_configuracion', 1, 'UPSERT', '[]', '{\"cfg_ruc\": \"1104015282001\", \"cfg_ambiente\": 1, \"cfg_razon_social\": \"CHAMPIONS\", \"cfg_punto_emision\": \"001\", \"cfg_regimen_rimpe\": \"NO\", \"cfg_agente_retencion\": \"\", \"cfg_direccion_matriz\": \"Rey David\", \"cfg_nombre_comercial\": \"Champios CF 2013\", \"cfg_secuencial_inicio\": 1, \"cfg_email_notificaciones\": \"fbpinzon@gmail.com\", \"cfg_obligado_contabilidad\": \"SI\", \"cfg_regimen_microempresas\": \"NO\", \"cfg_codigo_establecimiento\": \"001\", \"cfg_contribuyente_especial\": \"\", \"cfg_direccion_establecimiento\": \"Av. Principal 123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=Ahcw6sLe5L0DwGax-64MLzAabZzElDdYD4ZDYdCfrZMYPNU10CeUymvm-4gbFRzW0n2ODd6DzlLqZC-TOZ5fKarZSiOMuDtBslnQxKDLm1jpbV2H5rca3RxLIEgdOytymlClcJDGo3Ky1Vo~', 'POST', '2026-03-16 03:29:34'),
(359, 1, 1, 'Facturacion', 'facturacion_configuracion', 1, 'UPSERT', '[]', '{\"cfg_ruc\": \"1104015282001\", \"cfg_ambiente\": 1, \"cfg_razon_social\": \"CHAMPIONS\", \"cfg_punto_emision\": \"001\", \"cfg_regimen_rimpe\": \"NO\", \"cfg_agente_retencion\": \"\", \"cfg_direccion_matriz\": \"Rey David\", \"cfg_nombre_comercial\": \"Champios CF 2013\", \"cfg_secuencial_inicio\": 1, \"cfg_email_notificaciones\": \"fbpinzon@gmail.com\", \"cfg_obligado_contabilidad\": \"SI\", \"cfg_regimen_microempresas\": \"NO\", \"cfg_codigo_establecimiento\": \"001\", \"cfg_contribuyente_especial\": \"\", \"cfg_direccion_establecimiento\": \"Av. Principal 123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=RL7dp8C59uLenNgMaPCmaoWivxrIpFv8jRmqr1XqsDjzQDD3vc5fUEC9hnxih6Eg-l16hnjzJT1WCH04deijdmwMmQHw3GbL8lKawUyNYKwyRu87ocpGE5NuOb6KJ7uf1OD7ohTw_23XHLU~', 'POST', '2026-03-16 03:29:56'),
(360, 1, 1, 'Facturacion', 'facturacion_formas_pago', 8, 'UPDATE', '{\"estado\": \"ACTIVO\"}', '{\"estado\": \"INACTIVO\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ScsByKaNGe9NdET4z6cDDJzLW11D_dJukJmj2myonGF8wtBdpdXKWBwcXOoy1ZQMnYHNqiGkMNHfkDNwRfcAMm2QOIn0llYFszT_93SgSiv1i2avOBSDiuUMKUl_zm3sHLIWNxk2VPJhK9L7', 'POST', '2026-03-16 03:57:50'),
(361, 1, 1, 'Facturacion', 'facturacion_formas_pago', 2, 'UPDATE', '{\"estado\": \"ACTIVO\"}', '{\"estado\": \"INACTIVO\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=wFiUJt3P26gOubDZsJBUTn4V3tx-_8wPBIzNbSYDxahItF0Ue_VSLdanze7QfAhngiikOno4c7pLia5gMdPzKzlltkn5Rn2ikSBBjJJXCvqiPKYheOVMDnRx_V0vhKvsTgqEOVz1iboCbVdA', 'POST', '2026-03-16 03:58:00'),
(362, 1, 1, 'Facturacion', 'facturacion_configuracion', 1, 'UPSERT', '[]', '{\"cfg_ruc\": \"1104015282001\", \"cfg_ambiente\": 1, \"cfg_razon_social\": \"CHAMPIONS\", \"cfg_punto_emision\": \"001\", \"cfg_regimen_rimpe\": \"NO\", \"cfg_agente_retencion\": \"\", \"cfg_direccion_matriz\": \"Rey David\", \"cfg_nombre_comercial\": \"Champios CF 2013\", \"cfg_secuencial_inicio\": 1, \"cfg_email_notificaciones\": \"fbpinzon@gmail.com\", \"cfg_obligado_contabilidad\": \"SI\", \"cfg_regimen_microempresas\": \"NO\", \"cfg_codigo_establecimiento\": \"001\", \"cfg_contribuyente_especial\": \"\", \"cfg_direccion_establecimiento\": \"Av. Principal 123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=_y6KAmUn7xOzZLWALf9mBzipsDccSrYsq9HLyaNl-J23GP5h9tqhieXbadhZRPZ2aTSUTekDcEIoyjUkDfZwmMNEpZ6TmD1w80h5DNtJfg6JnrlJdCkcIiEHvVm-YDqdTJerxLConfsnato~', 'POST', '2026-03-16 04:25:43'),
(363, 1, 1, 'Facturacion', 'facturacion_configuracion', 1, 'UPSERT', '[]', '{\"cfg_ruc\": \"1104015282001\", \"cfg_ambiente\": 1, \"cfg_razon_social\": \"CHAMPIONS\", \"cfg_punto_emision\": \"001\", \"cfg_regimen_rimpe\": \"NO\", \"cfg_agente_retencion\": \"\", \"cfg_direccion_matriz\": \"Rey David\", \"cfg_nombre_comercial\": \"Champios CF 2013\", \"cfg_secuencial_inicio\": 1, \"cfg_email_notificaciones\": \"fbpinzon@gmail.com\", \"cfg_obligado_contabilidad\": \"SI\", \"cfg_regimen_microempresas\": \"NO\", \"cfg_codigo_establecimiento\": \"001\", \"cfg_contribuyente_especial\": \"\", \"cfg_direccion_establecimiento\": \"Av. Principal 123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=WtaCxdyt53H2wrmQ8NRvPy033fBryf3Nikq0uTcnNnAdxcydoyA8hiQlycxgaBTS7SMsRpZD7uvkXjBkC8sRwppx08hDVI67lq3OGMsUZcjYl6ucRuWZn6LQOcAPqyEva15nH7klbgCPpmQ~', 'POST', '2026-03-16 04:30:17');
INSERT INTO `seguridad_auditoria` (`aud_auditoria_id`, `aud_tenant_id`, `aud_usuario_id`, `aud_modulo`, `aud_tabla`, `aud_registro_id`, `aud_operacion`, `aud_valores_anteriores`, `aud_valores_nuevos`, `aud_ip`, `aud_user_agent`, `aud_url`, `aud_metodo`, `aud_fecha_operacion`) VALUES
(364, 1, 1, 'Facturacion', 'facturacion_facturas', 5, 'INSERT', '[]', '{\"total\": 8.05, \"numero\": \"001-001-000000006\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=M9gAipjWhGi07nxpnXOUGygZVMuMzQRwhOqNnoLczwAp4j-cd5aLyNSvZxup8n0SHgoMbbt1Ta--crGqunaay8FpS8jXdTp-TI-vdq16GxuJ6QW7tdIy5Sio3HT6Ew0-kHVZ3jQ~', 'POST', '2026-03-16 04:31:19'),
(365, 1, 1, 'Facturacion', 'facturacion_facturas', 5, 'EMITTED', '{\"estado\": \"BORRADOR\"}', '{\"estado\": \"EMITIDA\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=B15ZXXbraBhBu6DVGEYPG9hbktjmU0p_VazKVOHG1E7ftIyO_lDxDXx-Q9lMDtoq00ySXaaSb7OHARqt6m22Y_gB64DEh8PhBtaPfkMfFaU2y_oSQNF45ymYENdTuUM0fyz7yu1ejAqq_A~~', 'GET', '2026-03-16 04:31:30'),
(366, 1, 1, 'Facturacion', 'facturacion_pagos', 5, 'INSERT', '[]', '{\"monto\": 8.05, \"factura_id\": 5, \"forma_pago_id\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=_drq0r2VuGkp3R5UplZRAvFu1cc1f6QXgAW01zc-jY34XnoCD_KIB_jSsq7zYx-u9N1kqQRSgU9zjfFm0w7cMrVLt9leyR7Z8shjfhx0T3nIe6ZzLON8HyKn_M64cYC9Eco~', 'POST', '2026-03-16 04:36:04'),
(367, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=HGWfOSyNYSdkVifDBd4OH355e_FxIrJ5vVvUTcrt7RQ6txZ6HWHtV0imNRq4AfzzTwokSTFNrl-T9KeHMJZ0LCImHreKIFxeNA2CIxhMwfjhf7nkiho_DL5DZNis4mR2', 'POST', '2026-03-16 13:51:51'),
(368, 1, 1, 'Facturacion', 'facturacion_facturas', 6, 'INSERT', '[]', '{\"total\": 28.75, \"numero\": \"001-001-000000007\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ypWJfdA9FeKG3ah_rz9le0qRR3-HW1r8yTZZiDLacJAbxBIhzqHJklp82NzpmGWTgO4E-uwKq4V_giGgLp-iXcDU6vCPxKtzMzdjki4ZxYDfkVHV_whx55ttHSvZ4Dc3nFXKP1k~', 'POST', '2026-03-16 13:54:15'),
(369, 1, 1, 'Facturacion', 'facturacion_configuracion', 1, 'UPSERT', '[]', '{\"cfg_ruc\": \"1104015282001\", \"cfg_ambiente\": 1, \"cfg_razon_social\": \"CHAMPIONS\", \"cfg_punto_emision\": \"001\", \"cfg_regimen_rimpe\": \"NO\", \"cfg_agente_retencion\": \"\", \"cfg_direccion_matriz\": \"Rey David\", \"cfg_nombre_comercial\": \"Champios CF 2013\", \"cfg_secuencial_inicio\": 1, \"cfg_email_notificaciones\": \"fbpinzon@gmail.com\", \"cfg_obligado_contabilidad\": \"SI\", \"cfg_regimen_microempresas\": \"NO\", \"cfg_codigo_establecimiento\": \"001\", \"cfg_contribuyente_especial\": \"\", \"cfg_direccion_establecimiento\": \"Av. Principal 123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=TucYFjEN_nnStPHDoNRmo12y_qDtl6CJevpsX-YfjW2OMooao0f0nMWhOhwNC2ZyKEYsmv_Ul2uJ0kvWS5l7q15appZwO5ziBp5twXnOYcnxHAbR1WbsAC1txXTAwuLy1TONSdSv5kRcHvQ~', 'POST', '2026-03-16 13:56:06'),
(370, 1, 1, 'Facturacion', 'facturacion_facturas', 7, 'INSERT', '[]', '{\"total\": 46.55199999999999, \"numero\": \"001-001-000000008\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=RfL9OcKjok00ramZ4IK8Bqln3I4sRr1NZv6DmMuq1iKobiP1pHdUEhw_kCbkLlb6tTmphY-lPHiJfI8iCyY8GXNeIM2fS7yJyw9SX3Q1Le3TuKmFixaADy6Rk1rjmL-qYvnQDsY~', 'POST', '2026-03-16 13:57:52'),
(371, 1, 1, 'Facturacion', 'facturacion_configuracion', 1, 'UPSERT', '[]', '{\"cfg_ruc\": \"1104015282001\", \"cfg_ambiente\": 1, \"cfg_razon_social\": \"QUINDE ESPAÑA VERONICA MAGALI\", \"cfg_punto_emision\": \"001\", \"cfg_regimen_rimpe\": \"NO\", \"cfg_agente_retencion\": \"\", \"cfg_direccion_matriz\": \"REY DAVID 410-34 Y JUAN EL BAUTISTA\", \"cfg_nombre_comercial\": \"DIGITECH\", \"cfg_secuencial_inicio\": 1, \"cfg_email_notificaciones\": \"fbpinzon@gmail.com\", \"cfg_obligado_contabilidad\": \"NO\", \"cfg_regimen_microempresas\": \"SI\", \"cfg_codigo_establecimiento\": \"002\", \"cfg_contribuyente_especial\": \"\", \"cfg_direccion_establecimiento\": \"REY DAVID 410-34 Y JUAN EL BAUTISTA\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=9JMl59xO36utVUH6P0CYJKn0PZwGCtAr8zoJAw2x6Ie7DKz7cv_QHKF5y9UdiqZdpFwTrxygX_5Ko8jq09nGxZhaO_ApYit_8Zz29Q-hJnosUGQgLsi8oNJnGsAUx-gs1FVFtK4UW9aGZA0~', 'POST', '2026-03-16 14:17:03'),
(372, 1, 1, 'Facturacion', 'facturacion_facturas', 8, 'INSERT', '[]', '{\"total\": 40.25, \"numero\": \"002-001-000000002\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=Boi9OpfrzROJH9zKq5xEZdGj2gAzMuKYJTXc0DHcrzg9Yb1c_ssgzVmouL0gxCydgikitk7E7Ap4imnIWp8oCP2QHa8byPfiq-Z2IS2dijbKEXdzsw0SMrWsA-sIYW_LoEvUAGo~', 'POST', '2026-03-16 14:18:31'),
(373, 1, 1, 'Facturacion', 'facturacion_facturas', 9, 'INSERT', '[]', '{\"total\": 34.5, \"numero\": \"002-001-000000003\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ETdITtqIjJrrhL0-A2nWZVJd4ubIU7Jj9042b391niId4Te3sakekl94tx-tMucOAz1vPv3oEEzlQVHgkPHnP6z77DNCZTTN90iarZXxF6vqtWOO5tWduitRLt4JRisSf5CeWAs~', 'POST', '2026-03-16 14:28:29'),
(374, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=S6xRfLHknGw71rfABjoSBkb406mVw3EJhJlxrV2aWId2MF9phvcytG_4rPsiZHIswwRst2ZSN-jIgI1HNWaFa1tGMLomi5UzME1M2toQweNfABSJ6hScgUQAz-F2x5x1', 'POST', '2026-03-16 15:08:15'),
(375, 1, 1, 'Facturacion', 'facturacion_facturas', 10, 'INSERT', '[]', '{\"total\": 8.625, \"numero\": \"002-001-000000004\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=nNsSceifD4POBeWQwVNJeCWkscrv4tYlGqh1R2i8jGiRXTXVPNBk5hplsxmOocx1GNipxcS9wV2mA6erUF-l6PcMUhVjJlyFcZZ851TqVqS6hBXgjBEeXxYtvvH3CxpbpTOQ8Eg~', 'POST', '2026-03-16 15:10:02'),
(376, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=6cHCbKluZYjVNFWphgBKwxqwcIyEZJ9iwfBuzcVxfRoU63E-Z2RP_yxJoI9V8robXnu7nDPN0In8HgJQKpFFRKG7-BoKXzUxSjCNkotNYk0NWyhhIsJhJO-SGIk_VCW4', 'POST', '2026-03-16 16:03:03'),
(377, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=35iitHKGZdJ_8zZaM26HBhBJCWe4yTMgFebBk7_KlohRdgytQBORFnDJ3335FEleYbZ4pzSVW_kGKdnEWTj_lLAFrzBLK-eo7wq58PrTq3erkR90Ha03SD7kxO_tFHfF', 'POST', '2026-03-16 16:41:48'),
(378, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=nq7oNdVXNq0l0zIRRamsolfhj_OBXkgl6Ibt48TfKdM_Z51scluBpW037T4IkajPTGloabCUGymiD5lyXHjMC70QHqnH5CtD0hg5SepMwkfTeY5g4dvRL1xa3TZ0PNUl', 'POST', '2026-03-16 19:57:13'),
(379, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=shZl0c-sLHoEZDfiBqorquzutBjOxkDxoeH3YXXIhKaZGbmFfH84w12_WRcr-EPLK4miGfgaYyW_6kMpByClvBDB7cAlOvl97-Q74J-c9a9vD08HKRg-3Grp9EciUrQK', 'POST', '2026-03-16 20:57:54'),
(380, 1, 1, 'Facturacion', 'facturacion_facturas', 11, 'INSERT', '[]', '{\"total\": 40.25, \"numero\": \"002-001-000000005\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=nLD1tXoh7FQKdvINeLtXwLrcVBdtiVd5xMOOqqkwMaoBjjq2OPcpdySQXHm22LHentEzAaKWB_fdqPYAE5zx0pjI_YLsFCSrwI1z4Ii8zUZYTTOYiptNAG3lTbrClIFFvfoAxqQ~', 'POST', '2026-03-16 21:35:05'),
(381, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=Gr0RCKDWjp_5sYVESc2wvUHpOWhK7ynOv4HhZU8PQh8IuktYT5h-MPVRtsuns0ACtLQ7kQZghFIbOwpmY7aAVp06RgjoZKBRBTLIdskGr0zUK89kaZTIslGKf6012b6m', 'POST', '2026-03-17 02:17:09'),
(382, 1, 1, 'Facturacion', 'facturacion_facturas', 12, 'INSERT', '[]', '{\"total\": 25.3, \"numero\": \"002-001-000000006\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=H37eulOuAP8fY6d-TM10ro8vc3h_aIxSYJvqqKzRom2zqA8oXBqWVwiUHGIq5DXdnchQHi3arAkCGaDx0wb6FEepVVgZoofZTlJO06pwgpTEWBq3-_Z99t9HL3tuGDqYs-JU5h8~', 'POST', '2026-03-17 02:21:21'),
(383, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=fQOrbhfNgmdNedAPkixxw6RBx-uQ0SrCGyE_Gi3zMHd__eKu0ocgxwDupZAvhYOFre-PULXiKmM41YyNo41kJgyzOMillioilFqf9-EUDAc6DXK6AXxQdU1bexWoIIKw', 'POST', '2026-03-17 03:39:36'),
(384, 1, 1, 'Facturacion', 'facturacion_facturas', 13, 'INSERT', '[]', '{\"total\": 40.25, \"numero\": \"002-001-000000007\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=-HKzjzX0MNx7IXhOyHJrzglmu5CuyR9zXJ3Lf595BGsmkh15p-Un-wsVdLpJMA5G2qUXi6Gx44e2pi8k8OOr8vSlWCPOz38VaHU50syBo9M1osQO9LoNft8VHEDtxeNWqS80XDQ~', 'POST', '2026-03-17 03:40:32'),
(385, 1, 1, 'Facturacion', 'facturacion_formas_pago', 4, 'UPDATE', '{\"estado\": \"ACTIVO\"}', '{\"estado\": \"INACTIVO\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=fy9Thk3Y0J55pa6dza3fzta1jdVrn6_lE9Xu67LQ2d2MNjsoXdcW9FLhhJkT940W6nBGQ75QtyWsNqQVakAZKedQT2mumODa-h9ebv15RfPWAzY_AuhN9hCP5zjPfZGCqJHMt35G34n6sYPm', 'POST', '2026-03-17 04:14:38'),
(386, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=J-X9VPY4KnpMgqWFyOhT_YdHrKbU3fkg5elyiOiX1PmTA-03q-LrTxs4l_CMIaWxtF5O5YFuM4F7yj_6liSjssOZ4kut5pnlXr48SKG6dZV4UtVWp6J60Fp6l-5jY3-3', 'POST', '2026-03-17 13:37:55'),
(387, NULL, NULL, 'seguridad', 'seguridad_modulos', 31, 'editar_modulo', '{\"mod_id\": 31, \"mod_icono\": \"fa-basketball-ball\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"SOCCEREASY\", \"mod_nombre\": \"SOCCEREASY\", \"mod_created_at\": \"2026-03-05 11:37:36\", \"mod_es_externo\": 1, \"mod_updated_at\": \"2026-03-05 16:08:26\", \"mod_color_fondo\": \"#89F336\", \"mod_descripcion\": \"Sistema de administración de de la Escuela Champions\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": null, \"mod_url_externa\": \"http://localhost/soccereasy/sso.php\", \"mod_ruta_controller\": null, \"mod_requiere_licencia\": 0, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fa-futbol\", \"mod_orden\": 1, \"mod_activo\": 1, \"mod_codigo\": \"SOCCEREASY\", \"mod_nombre\": \"SOCCEREASY\", \"mod_es_externo\": 1, \"mod_color_fondo\": \"#89F336\", \"mod_descripcion\": \"Sistema de administración de de la Escuela Champions\", \"mod_url_externa\": \"http://localhost/soccereasy/sso.php\", \"mod_requiere_licencia\": 0}', '::1', NULL, '/digisports/public/index.php?r=JxwGLV8HxdzVok1FsaymgPAOsCJoSVU28rLyrWOYkI4ADt4_V5z0AqR9CENAR84b6br8v3r2n5iAvX4541E8Cq4Sw5Ccp1AuwoDwIGTe9F1hUQUTX064ZXr4nbc7Q1S7aQ~~', 'POST', '2026-03-17 13:38:57'),
(388, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=hq1Zq4qZiVrns4ihRguuddZHwwsvCziX3sb6P3IO1n1AcPkIa7J2LDp66hStaNdITBL4avLd8lM3_EUgg0mhWWfFarAix3LanebYXY3SiEwh5X9qqPNIuUXydouGb4PX', 'POST', '2026-03-17 15:42:48'),
(389, 1, 1, 'Facturacion', 'clientes', 3, 'INSERT', '[]', '{\"identificacion\": \"1103345292\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=3huonW5he7KuqKLf_EcWtF-Bgc2xZEqsh2_L7ngIxJIlmP5_ux3poEryPDAVNm1iKs5fMc6WLnCvflwm3O-uz5RsqKmCJX4CLo61C5W01ujCsXukgKs0WWeF2McgXr732MitWxbx9cf_WA5vYgta3w~~', 'POST', '2026-03-17 16:45:18'),
(390, 1, 1, 'Facturacion', 'clientes', 4, 'INSERT', '[]', '{\"identificacion\": \"1103345292\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=3jgXCBBQrxAHUH_JXlGBudLALYxBmOc9d0aEEmx_uF3AWQJYf_DMhTkp_SavZ8o5xtc24DuRrG6Z9Lz9JVtiv_vQeEVtOdlakhaB4oTrrqGbt02Iqlo8LC8OcGPKONSK5-lTEDYYv3x6dmKcSlAdBw~~', 'POST', '2026-03-17 17:19:22'),
(391, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=jIbUoeTY9rxsRzCy7wDA4DBWW77ICgPmGv8XC06hkvCW_R6Hd_M0O3I2kHLKN7hQ6rXO77PA9O_xUMq71zaOpJ_pnaTEz26BeyXGlakArLuSOM4ms0TDAnlX28ocPzoi', 'POST', '2026-03-19 13:36:01'),
(392, 1, 1, 'Facturacion', 'facturacion_rubros', 6, 'UPDATE', '[]', '{\"nombre\": \"Inscripción\", \"pct_iva\": 15, \"aplica_iva\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=C3UmTRGICO6iCjnRJYbVGdxsHtA5oehNm0eiTrn9v0zAjv5ZqqrCkDlT3bwXpYJH79_2QrsE3zViMoBzCq3pPoSvFZy-U1uuKyWkVdZrWn7jvWfB3YWJu59pbB3EIsJl0LCY', 'POST', '2026-03-19 14:17:27'),
(393, 1, 1, 'Facturacion', 'facturacion_rubros', 6, 'UPDATE', '[]', '{\"nombre\": \"Inscripción\", \"pct_iva\": 0, \"aplica_iva\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=MsiP8YA7lKJhEps7IJbSXxlV1GeiN0bKdm0Qu6O88bihvQfJRFC47LxoQrIbjhgz44N05lGZX1mU8p3ai7jAk6PkRNAx6GVa1dXER1LIwFE1moSjfqswBJOJ_OUz-p3rpBwh', 'POST', '2026-03-19 14:17:43'),
(394, 1, 1, 'Facturacion', 'facturacion_rubros', 6, 'UPDATE', '[]', '{\"nombre\": \"Inscripción\", \"pct_iva\": 0, \"aplica_iva\": 0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=dGB7_dExRLddlMJvjSqa1H8nz5U40aqNWGcIsCW7VOVaD15z5MALXQ1bHx10KPJI1xwKWSEFUMqofggd-RQ-lDrK0Vj95A8M74vXovisu7XSPqalSz5pISMrqZgDOMBm0ohm', 'POST', '2026-03-19 14:17:57'),
(395, 1, 1, 'Facturacion', 'facturacion_rubros', 4, 'UPDATE', '[]', '{\"nombre\": \"Matrícula\", \"pct_iva\": 0, \"aplica_iva\": 0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=cLZcEQm2pBDt3nj0BUMAcqBAIxvSiJ5OCtoLvAKRN8OufjYJ11OCxw4JEUVKiSQH8SgQmrBr2r7yKYa7wAiL5kQ6FCBef3btIV-zkwr2So-pBKx87fUwNih1_u4f0NfZKTiQ', 'POST', '2026-03-19 14:18:11'),
(396, 1, 1, 'Facturacion', 'facturacion_rubros', 12, 'UPDATE', '[]', '{\"nombre\": \"Evento / Torneo\", \"pct_iva\": 0, \"aplica_iva\": 0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=GM_Ed6ylS-80QxMcahBn5HPrAfkeWcESEAGC9cYD4_imRhVViJ9Jepl81M1p7nA0uGNsqr_iTwusYNoIlySknj_LKCboLCJl8LP2xGj9bGgD9QHVmrT2KEVx579XG8HZcXVH', 'POST', '2026-03-19 14:18:24'),
(397, 1, 1, 'Facturacion', 'facturacion_rubros', 6, 'UPDATE', '[]', '{\"nombre\": \"Inscripción\", \"pct_iva\": 0, \"aplica_iva\": 0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=F_r3x_OCfxfeapah1l_lCg-0pEG9ULjQVte3XJZdXH09ljkY8lZE-Ya4kGPhjsKHHd_m28ONUxsNmzAYNdvqq_4AIm0lCqnXi0G7-XHmMCmnOIxCDGOe9VMrYtcsPk5SF8UX', 'POST', '2026-03-19 14:18:40'),
(398, 1, 1, 'Facturacion', 'facturacion_rubros', 4, 'UPDATE', '[]', '{\"nombre\": \"Matrícula\", \"pct_iva\": 0, \"aplica_iva\": 0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=L5zO3vtCKxOpdH5jACui7suTRgGahBO4HiI3wET90Vbyayb79Rlue7WyrmHm2rI9Q2Jl7Dz0C8JbBiUgc3zxI4qoMX3TmGGB_fRN6JYtXFJs-KpXDtFBjqslfEbuyLGK80mC', 'POST', '2026-03-19 14:18:55'),
(399, 1, 1, 'Facturacion', 'facturacion_rubros', 4, 'UPDATE', '{\"estado\": \"ACTIVO\"}', '{\"estado\": \"INACTIVO\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=f5GAG8d2g1b4fysIpbRYce1_fDujkEDThmQ9XzmSPshPKemndY74u8E9euVnMt8wRTZDqm4lDAodhdpWT312VO8ilYB-2XqafZHfC_6NMCGx-Y1uO9J6YJZ8jyKsFoUh8QFTvO1w30A~', 'POST', '2026-03-19 14:19:15'),
(400, 1, 1, 'Facturacion', 'facturacion_rubros', 2, 'UPDATE', '[]', '{\"nombre\": \"Mensualidad\", \"pct_iva\": 0, \"aplica_iva\": 0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ZkzEU9JatmnTQKNmhubxpQI0glE5k2dxFnbwK9QO-d4TJJBW-qmuvtBt_va7YQMrAbudir1UoKZom2QpZHGYWMCfPHZbE4cM95BVnR7rnq0cPdufpNp1Hq8r78EOxbfGqCMP', 'POST', '2026-03-19 14:19:36'),
(401, 1, 1, 'Facturacion', 'facturacion_rubros', 12, 'UPDATE', '[]', '{\"nombre\": \"Evento / Torneo\", \"pct_iva\": 0, \"aplica_iva\": 0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=vhETCb1xNMpzd9D888e28VZ0Mag2qxwDBP40sNkw6baanSob2Ol3cwgwox9O58QQnRtfxVXR1SoT3YZnIK60rS0PJZk6TRY_thaoDGVjn0ybVcEUNiaLwR6Lw83sT3WK2-1E', 'POST', '2026-03-19 14:25:31'),
(402, 1, 1, 'Facturacion', 'facturacion_rubros', 10, 'UPDATE', '[]', '{\"nombre\": \"Certificado / Diploma\", \"pct_iva\": 0, \"aplica_iva\": 0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=2JvUuvU7fgp7aTRebhu4RXObMnq5l94kn2pfZUYvrk5zmvZ439JE9JVgg1Rkae1eJ0s30QXUs2YDljN90aoh-PzAuD6sBER60a_4hSCBs6CqiVdo5fCRsTTIz9wiJGMIDEvD', 'POST', '2026-03-19 14:25:53'),
(403, 1, 1, 'Facturacion', 'facturacion_rubros', 8, 'UPDATE', '[]', '{\"nombre\": \"Uniforme / Equipamiento\", \"pct_iva\": 15, \"aplica_iva\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=q8__YGpbL3xITYL9wE7IEet2g_kCZ0AdPoEyf71SGEmSCjK2-GUDzIUo6BZd5x-LFLph7fqWK--zFhdK6iEdxerGkE-ONVaLxJqwD3YPEkjLK6KOmzZMZObmDRTqeNf_wW7D', 'POST', '2026-03-19 14:27:01'),
(404, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=TGE64WWVk2kUsIX1fg8QrEYKX5xWukTm-KQWnDC-Bh_V_H3cI90w8GtK_4VzcCdWenpOYKRn4D2fATQpM3DbVQ-u4srz8B3OQF-QnXoKCNHa8YNTg9cpL35FhGbI6OT1', 'POST', '2026-03-19 15:25:26'),
(405, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=WDN3yQC4y8SgHamuOFc-WzX--Axu5spmNAzQEubyWizVIcBtdXelNEc5WUD4JYEHkeA3rZpwHyf2eNndbseyqePzKDlkLYbR5Uj_vQYAg7QVIfadqVZTXcC2', 'GET', '2026-03-19 16:48:27'),
(406, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=8onkJxzSFLEBRdV21TiZMfxKgOxiQAF9SXKXpim5JplV9PU55iyFAI2LbDWzdYeZBLT3Ku5HzsT6_sUsr-6Db0GL8kvNrPaNcjiYLZS42z3XSatccbofiT07GgcwVNmd', 'POST', '2026-03-19 16:48:49'),
(407, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=T00ibrD-AJFKYn1UuZAODi_tjZ4jSVw9I45sykAe8JzlFcLLxUrE1eBXzPItvMxNijdlnSTMX_HRmyS0T6N9w-dvQRwM0Xc08ac7Qbo_HOWR46Otb4H7o1TC', 'GET', '2026-03-19 16:52:11'),
(408, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=t-jCnAHTozrvtj6yDGh4Qki0lb4STIAXSP7bMsnMCQ_XCyClT7EN_4C19C71uJsGJmvDJR09bUSwcNDf3rd4cjsbmCr0mnDhW8l7dIcivrlM5yzTeEr0XrXgWMsoVAXv', 'POST', '2026-03-19 16:52:28'),
(409, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=W6NjbDUIY5I2wx6N1DhMXD3C2WVZV8txxeyjtpRMxXkHY978CMj3q-vT0CF-u447R8EBXp1-hovaJQubylZitqWLQgY7C9GQaHkhj3mKUeKmrbj05IkTn24CBTv9QP2R', 'POST', '2026-03-19 19:18:57'),
(410, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=9vAj74QhPDDvkeNqQFcx8irAFiJyX5dQL3AbZ5LwXCchSMuH3PK6TmAaQK0Qd-xHt_iRCS0QKp4KouIuS_xHje2H8ugOf29hdL31qyI0ICQQIlumV8PKbYnA', 'GET', '2026-03-19 19:30:58'),
(411, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=KWj6h-wpiRxAfQjW2Rkay5RCO7cxhAFIlHssicRMzHCrY68zJi1rXfbgERXti0ySsT5pzz6kSTFntC8ndbPULDtogouUNbyq-swCucuL67Z_x5hIjyi0UUvy3aGTz2ms', 'POST', '2026-03-19 19:31:08'),
(412, 1, 1, 'Facturacion', 'facturacion_facturas', 14, 'INSERT', '[]', '{\"total\": 114.647, \"numero\": \"002-001-000000008\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=7rzGJ1SSDaAjp3uqrac2X7vzb7uxSbjWYO9hIqykr4MwgMD72NUQ5pPAEboqhI3YIxkdGIJ1WcnU2DSo_OAt97Gn3tIBWPn0fWn6LBA7qM-4Pyz8vZpHfwVLLqRQwtpbyBaR9qg~', 'POST', '2026-03-19 20:14:03'),
(413, 1, 1, 'Facturacion', 'facturacion_facturas', 15, 'INSERT', '[]', '{\"total\": 74.5, \"numero\": \"002-001-000000009\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=lia7qhUVf8z_Ro8HSIkuKABAzWjdNGt8Dueza88MEYIPYyTZ12HAhyyb5vj5itYC5pXsfE-tikijSrSqBiFjet9bMPNQ6jdAejvllrrz2XwSRoioBih6ao1K6_PSZNAZa59wRUI~', 'POST', '2026-03-19 20:31:24'),
(414, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=ApHP7WiR0epDQHZfqxLBITEnNO1QkKBF1CvvzeoLANQcRSZucGwbsm6PDDypT5DgDC-LbcOUPuXHzxeZYw6sp5PtJUtm0NWvZqpS6zLlClz9jjKmi5BPxMAnoYu-b6pF', 'POST', '2026-03-19 21:06:43'),
(415, 1, 1, 'Facturacion', 'facturacion_rubros', 8, 'UPDATE', '[]', '{\"nombre\": \"Uniforme / Equipamiento\", \"pct_iva\": 15, \"aplica_iva\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=f29TrzF-htqXvdGxLLyPYzgxDk9TZT1rvGU7oX4JXEqhGG7LOEU9nCBqIldpv9HUUgyeZeflvQuQKoaK-cgru2S15PSdImOaXMgEkfO1yq9gX5c4SiWI4CYDGSuldc1KQxXk', 'POST', '2026-03-19 21:13:07'),
(416, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=QuO-BDOu9rw0O2jBoThQ6mrK-M5EdBZ8VXbFGvy0FiPFQUx1_IVQmspGDUfLs-WgARbBl8ayc7An62ueawTvBH3J7VNgckq8c2sd_yppM6C_Mu-HXm8xJ8l3Ao5pap0c', 'POST', '2026-03-19 22:20:46'),
(417, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=QNKDSDt-pLoNvR_ICCOEg0TQ-YFoQQiR5IjTrJ3H99O3b_YL0aOgEJTV5A3CtpoI_9oSRWsbv94q68983Dg8dsOcArmDBkxKgwcLTMbx3QSl2tV4ebxEOWdtTLGaVMzM', 'POST', '2026-03-20 00:36:43'),
(418, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=cooMugj2x9w_HQJSKZ_Cg__P5hQNUDNKQQZo-Q5iiWtJC0yhN9vDWNhmYudnMOHiuKUtlzEKiI0Q1JIgvUImQzNkrEJZeoq0vj2PI-oNBJ_n56A17rs3DbLGxrzAIX-N', 'POST', '2026-03-20 01:35:34'),
(419, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=SNlLXhY6F1jspPg1JXRh9R22i9RWpuVAfmvZ2U7bwtHhydrPTH6u5Vy_9-dwt0b1_zUXNxUzMEcOkfQiudvis_vyx-cKx3ST8ZrvlKK0BY23DnnOI1Yhbgl1E58UvWWg', 'POST', '2026-03-20 19:21:14'),
(420, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=HTjwVO7DBVebPYDDZkDIf514KCJqWLeoK0kEjJK6oSYtFnShOJx8-Dpoasz9JAxRDK14hJdM40u-Lch6ozw5KisWIerC5FBEopqtbsf1lIIlgndBMknNW1R7oPTe4KZC', 'POST', '2026-03-20 19:55:25'),
(421, NULL, NULL, 'seguridad', 'seguridad_modulos', 32, 'crear_modulo', NULL, '{\"mod_codigo\": \"CDJG\", \"mod_nombre\": \"Jorge Guzman CD\"}', '::1', NULL, '/digisports/public/index.php?r=KEnNQUXO2v1wre5BflQgwPV9uI7fXSOqW8aquaglYp98kofkuBjkOiGgN0DVJOhlHEJOPraQjTLqh4_tkbR1DlC_AILFGfkll9-UvMja7b9e8DccXJXTU9JinKd28qDi', 'POST', '2026-03-20 20:00:41'),
(422, NULL, NULL, 'seguridad', 'seguridad_modulos', 32, 'editar_modulo', '{\"mod_id\": 32, \"mod_icono\": \"fas fa-basketball-ball\", \"mod_orden\": 0, \"mod_activo\": 1, \"mod_codigo\": \"CDJG\", \"mod_nombre\": \"Jorge Guzman CD\", \"mod_created_at\": \"2026-03-20 15:00:41\", \"mod_es_externo\": 1, \"mod_updated_at\": \"2026-03-20 15:00:41\", \"mod_color_fondo\": \"#fd7e14\", \"mod_descripcion\": \"Sistema Jorge Guzmán Club Deportivo\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": null, \"mod_url_externa\": \"http://localhost/soccereasy/sso.php\", \"mod_ruta_controller\": null, \"mod_requiere_licencia\": 0, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-basketball-ball\", \"mod_orden\": 0, \"mod_activo\": 1, \"mod_codigo\": \"CDJG\", \"mod_nombre\": \"Jorge Guzman CD\", \"mod_es_externo\": 1, \"mod_color_fondo\": \"#fd7e14\", \"mod_descripcion\": \"Sistema Jorge Guzmán Club Deportivo\", \"mod_url_externa\": \"http://localhost/cdjg/sso.php\", \"mod_requiere_licencia\": 0}', '::1', NULL, '/digisports/public/index.php?r=f3jj7wTOU1UZsKdX6PMzrryZrv5NRirJFOAVsJPYiJRropm8IKICixbp4M_l2lWR95svIlUFv7JTrYoF7c6nU-zt3iLU_Rly5JgKrG-gxVNam8NM51SW92Kxs40vb_yJRA~~', 'POST', '2026-03-20 20:02:14'),
(423, NULL, NULL, 'seguridad', 'seguridad_modulos', 33, 'duplicar_modulo', NULL, '{\"mod_codigo\": \"CDJG_COPIA\", \"original_id\": 32}', '::1', NULL, '/digisports/public/index.php?r=M2QWC8wfHLFjBsTX9L9xeOyqW67_nEYlmfoBTz_sWcgQbr_5RUNUPHwbjmB3Uu88nJDYalQsJp7oEIirVr8_mKMw8nYkpi10jkQIe9q-v6oIrtVvaEodXeM-u_hpAVBcx947WcQRN3cbXw~~', 'GET', '2026-03-20 21:16:00'),
(424, NULL, NULL, 'seguridad', 'seguridad_modulos', 33, 'editar_modulo', '{\"mod_id\": 33, \"mod_icono\": \"fas fa-basketball-ball\", \"mod_orden\": 0, \"mod_activo\": 0, \"mod_codigo\": \"CDJG_COPIA\", \"mod_nombre\": \"Copia de Jorge Guzman CD\", \"mod_created_at\": \"2026-03-20 16:16:00\", \"mod_es_externo\": 1, \"mod_updated_at\": \"2026-03-20 16:16:00\", \"mod_color_fondo\": \"#fd7e14\", \"mod_descripcion\": \"Sistema Jorge Guzmán Club Deportivo\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": null, \"mod_url_externa\": \"http://localhost/cdjg/sso.php\", \"mod_ruta_controller\": null, \"mod_requiere_licencia\": 0, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fas fa-basketball-ball\", \"mod_orden\": 0, \"mod_activo\": 0, \"mod_codigo\": \"CDJG_COPIA\", \"mod_nombre\": \"Copia de Jorge Guzman CD\", \"mod_es_externo\": 1, \"mod_color_fondo\": \"#fd7e14\", \"mod_descripcion\": \"Sistema Jorge Guzmán Club Deportivo\", \"mod_url_externa\": \"http://localhost/cdjg/sso.php\", \"mod_requiere_licencia\": 0}', '::1', NULL, '/digisports/public/index.php?r=u2Hs-WkmSCLmvAMotkW_Ays0qayHpnAGa9tsdo5JUynKTaokPMQgAqTPAW1OGeAnxpDtTsO0rhSe6I73kgh0BxYKffrL0EEsIF00JTBpNOrO2ujVCYjeoO0w1sj7sysJGw~~', 'POST', '2026-03-20 21:16:17'),
(425, NULL, NULL, 'seguridad', 'seguridad_modulos', 34, 'duplicar_modulo', NULL, '{\"mod_codigo\": \"CDJG_COPIA_COPIA\", \"original_id\": 33}', '::1', NULL, '/digisports/public/index.php?r=bLnbevYRMRUnw4JJ_Qscnt3URDC4NsUcL2rfOebBcg8yYnDRkWijkh5QcvmPiY3LCnLUEJgubai1mXh_bq5JduH4WugI5CGWWwDPQU-XcWJKsQmmGvOzdsfu7OXEddloZTD9xLdhP-Dk6Q~~', 'GET', '2026-03-20 21:45:43'),
(426, NULL, NULL, 'seguridad', 'seguridad_modulos', 34, 'editar_modulo', '{\"mod_id\": 34, \"mod_icono\": \"fas fa-basketball-ball\", \"mod_orden\": 0, \"mod_activo\": 0, \"mod_codigo\": \"CDJG_COPIA_COPIA\", \"mod_nombre\": \"Copia de Copia de Jorge Guzman CD\", \"mod_created_at\": \"2026-03-20 16:45:43\", \"mod_es_externo\": 1, \"mod_updated_at\": \"2026-03-20 16:45:43\", \"mod_color_fondo\": \"#fd7e14\", \"mod_descripcion\": \"Sistema Jorge Guzmán Club Deportivo\", \"mod_ruta_action\": \"index\", \"mod_ruta_modulo\": null, \"mod_url_externa\": \"http://localhost/cdjg/sso.php\", \"mod_ruta_controller\": null, \"mod_requiere_licencia\": 0, \"mod_base_datos_externa\": null}', '{\"mod_icono\": \"fa-futbol\", \"mod_orden\": 0, \"mod_activo\": 1, \"mod_codigo\": \"PEDRO_LARREA\", \"mod_nombre\": \"Escuela Pedro Larrea\", \"mod_es_externo\": 1, \"mod_color_fondo\": \"#DE3163\", \"mod_descripcion\": \"Sistema Escuela de fútbol Pedro Larrea\", \"mod_url_externa\": \"http://localhost/adfpedrolarrea/sso.php\", \"mod_requiere_licencia\": 0}', '::1', NULL, '/digisports/public/index.php?r=zOodxvfYloDaWEQzc7G_MVxXJ__GqNVZIWhGYOGGuaf6WxJV4qusele_hMqNbxd4Ohf2DxRCdad7PpFImWniRSk7DqM3i7XTNNyFGKqPeXGvWMa-wguGq3xaBwh4SPHU_Q~~', 'POST', '2026-03-20 21:47:02'),
(427, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=VqD8bZpCW_mjqrsJdXjgHp-0b_MpMRUPm8Key7W6g9SMKyiCq6P5hoOElZo4lXUAus26d8ZC-hlN2_8CJONrbWCpynh2gWJexnXBjxSkWYbsF9wYTI2VexvO3TN_ONxu', 'POST', '2026-03-20 22:25:27'),
(428, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=tTeR-wJD58aU5wKVXS2zWo1fV_4QsvT96IaC5uw8Usja0onDlYO3i728OCy2EBpuARaWwC3-gwJwK0Z3hIkT7kAyIvxTq5RnyrwTaAw9OOC-1Yk_LV58VTDmD43w_DKf', 'POST', '2026-03-21 00:08:57'),
(429, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=fCywB5GxdtHV8fCQTIXQUGhxqPGUDoJ5PHuEnAjqIBpNEo2rGM-fzgqrmYHOl1Q3IP60p3OZJdWG6xRcbQzI1gAPkR53NZTXLYoma6McvczjRME3Df139picPsJDjiwu', 'POST', '2026-03-21 00:46:59'),
(430, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=pK7vp8GxpfX21lfF8zJTB-TDQM2eaNY17IiHFpoyTTT-lBtDsut8UonbQSW-ywfl5HQJCOLBV2ujjfdfLSnd9GrXMgAmaBUtBv1pN9EYmEa_gjQn8GaE5fVCnzxj61w9', 'POST', '2026-03-22 01:49:48'),
(431, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=FpEya5xr7wCw2Wq0sFn-I0jHAJmjsxTSxM6MU3ArXs7LsI-eDBmdq1fEL2e93sgEzEfAM4BjwUzfQq9EPlQ_gcUrBe9EfEMLZRxcCjf7ufRB9yTMs_A0aD3Jfv7-WwjH', 'POST', '2026-03-22 02:26:22'),
(432, 1, 1, 'Facturacion', 'facturacion_facturas', 16, 'INSERT', '[]', '{\"total\": 159, \"numero\": \"002-001-000000010\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=X4FxQ1rdo52r1S4fwmCD0lanjohMhxLgm3odaB8EUwODION4TSWJmwgeqITSQZGCytX8mZ2hewqwfW-nX56oAUtkSgPYrSygxOHG9LaonDSNiOxpJr_xsLymkiA_nb9GuTXmxXg~', 'POST', '2026-03-22 02:51:58'),
(433, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=TYDjWSYa5_TtqRMpJSkrqDEQbQt8WAxMUT33grHHO3w1QSlpNCiEZRjceILdih2fGYOZtNgnNrlkIl1ahmKY_8BItdb3xjnR58eaxIg-EH_lmUx06TqHZi_MWh_FXtDp', 'POST', '2026-03-23 00:42:06'),
(434, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=JTIK6Fs2VMAN1Jf_mqwIPq-BUxxmmepJVxLFCY02g_heWg-lkkC1q7E69tdH6zc7FpYMz2yt37-JpXwLofhsxaib8BSaxktJeUJHtgz9C9ANILu80tPqns3uzsVpqIEu', 'POST', '2026-03-23 02:05:38'),
(435, 1, 1, 'Facturacion', 'facturacion_facturas', 17, 'INSERT', '[]', '{\"total\": 35.05, \"numero\": \"002-001-000000011\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=BtJ0wWeFe15WsA0wk2-6wUhuvkg7-JaDzomBIIOaT-IcFyhMjrGQrwjjSez4E-to5cIANeUJvM-_9SM8CHjeQL7x5Oe8N00vh4zD0imrjanxsDWXN0D5lpzNU_v1qpRkRYmGxpQ~', 'POST', '2026-03-23 02:08:17'),
(436, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=-xjh_17-0U6aBGjUl8YnJfH-fPYp7imc6aWRyYBAjHLR9t1EmNfU-T6zYakVC4nZL2oKk9nnTcd0h1sHuUSJ8OKHhD5xQMmbinQ8TxLCSGZpfUBfVWg95wHDwS_zQE6n', 'POST', '2026-03-24 20:07:57'),
(437, 1, 1, 'Facturacion', 'facturacion_facturas', 18, 'INSERT', '[]', '{\"total\": 17, \"numero\": \"002-001-000000012\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=XhUG2whZjX8k0znZ0CKG-zhMScNqOG64Ub9K5D-3ccJkIQoScz89ZO2aZGYmuieMSpexgedSgoEx1QhW8FYLC17JEJvKmxucr10TIhzA4jery1LqjtkMYoPcKF2nULU8zjw6P4s~', 'POST', '2026-03-24 20:08:39'),
(438, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=bA9u5j-RKd896JJDrgHi1Ot7ZlvpsOCiOSfs_sINePYBKlxspADyzcOj9rOk47m3rvAToTMm-JOvo21U8lZSrJjX0OfL2i19AIQGHNleyqFmGM9Oto4THVQnpowEmaAa', 'POST', '2026-03-25 00:25:37'),
(439, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=5NyCTdCeq13nyT0pxSXk3A0qpvk1Lamwf9YSQMe7pqzHvZo5OjcpR3HggQzgb-O6wl47MrgSxfxmudyJKaLxEv_5oQsvmFr5kCwxZ9WhlXYjDqL6Cf5KWXZ-4TELp8jf', 'POST', '2026-03-25 03:21:46'),
(440, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=Kf1rtNVF3QpMnfWgS15YbYYs3Gl9VwuRixizjugvubqmDI9K4RTZvUAYZzYg9ErUoQXEQOh4tlWm_6PyxSqtXhdgz3zZtcBW-LUDKX5gO0WY9lZWmU4XvPg3LGG0zLCE', 'POST', '2026-03-25 13:27:36'),
(441, 1, 1, 'Core', 'seguridad_usuarios', 1, 'LOGOUT', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=WmGbjw4wH0ie126IGLV4lO6d2Yu-X1o8HUdXiqaIrrPAiRo3806fQcJNh2OI7WkNmkePhhqKABUINVPL0ySFt5BP_uqJT5kVP6ddZAW2JjzMVoJsF0HXuJpO', 'GET', '2026-03-25 14:04:01'),
(442, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=Z6Rf1SMUp6blKN9sIgAuPmXN4Pv0RxgQwryup7kDdQXCCnBdfKCVFSk7y8SpFM9ma-0CL3Osqlf2duU9XH9uNiAzKVRaGkJoZuSiR8WDf4lsGgMoty7EvUTTidg8-e6y', 'POST', '2026-03-25 14:04:23'),
(443, 1, 1, 'Facturacion', 'facturacion_rubros', 10, 'UPDATE', '{\"estado\": \"ACTIVO\"}', '{\"estado\": \"INACTIVO\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=bbkDycc4KpEIPH8XvJTOnwYurFPTW2TOPbzRvk19HYN6TPDWIH22y-7BfjZFT373OTRITw4_PkVwpm4l0yrZP3N7GVmbEtXUN3dShlbEa5-Kx_BnbFKvTHddN81kbhLGOjcufwoNy2M~', 'POST', '2026-03-25 14:32:15'),
(444, 1, 1, 'Facturacion', 'facturacion_rubros', 2, 'UPDATE', '[]', '{\"nombre\": \"Mensualidad\", \"pct_iva\": 0, \"aplica_iva\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=0sap_vjlr_Qd6YKPKQksLgyMPm_sHxNkfQPdy9Pf3M8C32xyePn4RMZM5drweOfBlgV8JJh2BYZZ5LjNPwx7KJWxukRwA9TXwfB2qpGFX_Ctvtzco-8TrlNLoJOCYLPOJfPz', 'POST', '2026-03-25 14:34:56'),
(445, NULL, NULL, 'Core', 'seguridad_usuarios', 1, 'LOGIN', '[]', '{\"ip\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '/digisports/public/index.php?r=hSr4vu3_EP8-harOwkC5lQ4HSsDRu75Jw0HJtODV-NWadcnQeWYz-GQFlwdwD3Wg7NdYNJS-wSQI_Gzt3mUfkyYB9ODbHD_63kU3Z8o8l3-wfmnL22TWsV1s8oFqx-CK', 'POST', '2026-03-25 15:31:49');

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
-- Estructura de tabla para la tabla `seguridad_ips_bloqueadas`
--

DROP TABLE IF EXISTS `seguridad_ips_bloqueadas`;
CREATE TABLE IF NOT EXISTS `seguridad_ips_bloqueadas` (
  `ib_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ib_ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'IPv4 o IPv6',
  `ib_bloqueado_hasta` datetime NOT NULL COMMENT 'Timestamp de expiraci├│n del bloqueo',
  `ib_intentos` smallint NOT NULL DEFAULT '0' COMMENT 'Intentos fallidos que generaron el bloqueo',
  `ib_razon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'M├║ltiples intentos fallidos de login',
  `ib_desbloqueado` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = desbloqueado manualmente',
  `ib_desbloqueado_por` int UNSIGNED DEFAULT NULL COMMENT 'Usuario que desbloque├│',
  `ib_creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `ib_actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ib_id`),
  UNIQUE KEY `uk_ip` (`ib_ip`),
  KEY `idx_expiry` (`ib_bloqueado_hasta`),
  KEY `idx_activo` (`ib_desbloqueado`,`ib_bloqueado_hasta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='IPs bloqueadas por exceso de intentos fallidos de login';

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
  KEY `idx_tipo` (`acc_tipo`),
  KEY `idx_brute_force` (`acc_ip`,`acc_exito`,`acc_tipo`,`acc_fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(8, 1, 1, '2026-02-19 15:13:47', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(9, 1, 1, '2026-02-24 14:50:10', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(10, 1, 1, '2026-02-25 12:18:35', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(11, 1, 1, '2026-02-25 14:20:13', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
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
(40, 1, 1, '2026-03-04 10:25:29', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(41, 1, 1, '2026-03-04 15:08:32', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(42, 1, 1, '2026-03-04 15:41:24', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(43, 1, 1, '2026-03-04 15:43:06', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(44, 1, 1, '2026-03-04 15:46:56', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(45, 1, 1, '2026-03-04 15:48:24', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(46, 1, 1, '2026-03-04 16:24:18', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(47, 1, 1, '2026-03-04 16:42:04', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(48, 1, 1, '2026-03-04 16:43:04', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(49, 1, 1, '2026-03-04 20:32:00', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(50, 1, 1, '2026-03-04 22:18:30', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(51, 1, 1, '2026-03-05 00:02:00', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(52, 1, 1, '2026-03-05 00:04:57', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(53, 1, 1, '2026-03-05 00:08:03', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(54, 1, 1, '2026-03-05 08:29:40', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(55, 1, 1, '2026-03-05 10:15:18', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(56, 1, 1, '2026-03-05 12:29:17', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(57, 1, 1, '2026-03-05 14:49:27', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(58, 1, 1, '2026-03-05 15:27:15', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(59, 1, 1, '2026-03-05 16:56:22', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(60, 1, 1, '2026-03-05 17:48:15', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(61, 1, 1, '2026-03-05 17:48:33', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(62, 1, 1, '2026-03-05 20:04:33', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(63, 1, 1, '2026-03-05 20:23:45', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(64, 1, 1, '2026-03-05 22:23:27', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(65, 1, 1, '2026-03-06 10:46:47', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(66, 1, 1, '2026-03-06 11:49:40', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(67, 1, 1, '2026-03-06 12:52:30', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(68, 1, 1, '2026-03-06 14:28:49', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(69, 1, 1, '2026-03-06 15:51:36', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(70, 1, 1, '2026-03-06 18:58:37', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(71, 1, 1, '2026-03-06 20:19:27', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(72, 1, 1, '2026-03-07 07:30:51', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(73, 1, 1, '2026-03-07 19:46:11', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(74, 1, 1, '2026-03-07 21:05:48', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(75, 1, 1, '2026-03-07 22:17:01', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(76, 1, 1, '2026-03-07 23:53:44', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(77, 1, 1, '2026-03-09 09:44:23', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(78, 1, 1, '2026-03-09 09:47:54', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(79, 3, 2, '2026-03-09 09:48:08', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(80, 3, 2, '2026-03-09 09:48:34', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(81, 1, 1, '2026-03-09 09:48:50', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(82, 3, 2, '2026-03-09 10:13:57', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(83, 3, 2, '2026-03-09 10:14:20', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(84, 3, NULL, '2026-03-09 10:14:25', 'LOGIN_FAILED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'N', 'Usuario: fbpinzon - Contraseña incorrecta'),
(85, 1, 1, '2026-03-09 10:21:02', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(86, 1, 1, '2026-03-09 12:27:14', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(87, 1, 1, '2026-03-09 15:22:07', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(88, 1, 1, '2026-03-09 17:52:27', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(89, 1, 1, '2026-03-09 19:40:26', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(90, 1, 1, '2026-03-09 23:56:30', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(91, 1, 1, '2026-03-10 10:14:10', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(92, 1, 1, '2026-03-10 11:03:16', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(93, NULL, NULL, '2026-03-10 23:06:10', 'LOGIN_FAILED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'N', 'Usuario: supoeradmin - Usuario o email no encontrado o inactivo'),
(94, 1, 1, '2026-03-10 23:06:27', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(95, 1, 1, '2026-03-11 00:26:31', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(96, 1, 1, '2026-03-11 00:31:14', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(97, 1, 1, '2026-03-11 00:31:25', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(98, NULL, NULL, '2026-03-11 08:45:45', 'LOGIN_FAILED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'N', 'Usuario: superadmim - Usuario o email no encontrado o inactivo'),
(99, 1, 1, '2026-03-11 08:46:01', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(100, 1, 1, '2026-03-11 23:13:01', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(101, 1, 1, '2026-03-12 08:45:13', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(102, 1, 1, '2026-03-12 14:25:43', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(103, 1, 1, '2026-03-12 15:21:54', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(104, 1, 1, '2026-03-13 11:09:12', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(105, 1, 1, '2026-03-13 17:00:17', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(106, 1, 1, '2026-03-13 20:13:39', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(107, 1, NULL, '2026-03-14 11:08:31', 'LOGIN_FAILED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'N', 'Usuario: superadmin - Contraseña incorrecta'),
(108, 1, 1, '2026-03-14 11:08:45', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(109, 1, 1, '2026-03-14 18:55:25', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(110, 1, 1, '2026-03-14 20:06:15', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(111, 1, 1, '2026-03-14 20:50:02', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(112, 1, 1, '2026-03-14 23:37:46', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(113, 1, 1, '2026-03-15 19:40:31', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(114, 1, 1, '2026-03-15 20:36:46', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(115, 1, 1, '2026-03-15 21:47:47', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(116, 1, 1, '2026-03-15 22:28:03', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(117, 1, 1, '2026-03-15 22:28:14', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(118, 1, 1, '2026-03-16 08:51:51', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(119, 1, NULL, '2026-03-16 10:04:03', 'LOGIN_FAILED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'N', 'Usuario: superadmin - Contraseña incorrecta'),
(120, 1, 1, '2026-03-16 10:08:15', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(121, 1, 1, '2026-03-16 11:03:03', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(122, 1, 1, '2026-03-16 11:41:48', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(123, NULL, NULL, '2026-03-16 14:56:57', 'LOGIN_FAILED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'N', 'Usuario: admin - Usuario o email no encontrado o inactivo'),
(124, 1, 1, '2026-03-16 14:57:13', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(125, 1, 1, '2026-03-16 15:57:54', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(126, 1, 1, '2026-03-16 21:17:09', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(127, 1, 1, '2026-03-16 22:39:36', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(128, 1, 1, '2026-03-17 08:37:55', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(129, 1, 1, '2026-03-17 10:42:48', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(130, 1, 1, '2026-03-19 08:36:01', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(131, 1, NULL, '2026-03-19 10:25:16', 'LOGIN_FAILED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'N', 'Usuario: superadmin - Contraseña incorrecta'),
(132, 1, 1, '2026-03-19 10:25:26', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(133, 1, 1, '2026-03-19 11:48:27', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(134, 1, 1, '2026-03-19 11:48:49', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(135, 1, 1, '2026-03-19 11:52:11', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(136, 1, 1, '2026-03-19 11:52:28', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(137, 1, 1, '2026-03-19 14:18:57', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(138, 1, 1, '2026-03-19 14:30:58', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(139, 1, 1, '2026-03-19 14:31:08', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(140, 1, 1, '2026-03-19 16:06:43', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(141, 1, 1, '2026-03-19 17:20:46', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(142, 1, 1, '2026-03-19 19:36:43', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(143, 1, 1, '2026-03-19 20:35:34', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(144, 1, 1, '2026-03-20 14:21:14', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(145, 1, 1, '2026-03-20 14:55:25', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(146, 1, 1, '2026-03-20 17:25:27', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(147, 1, NULL, '2026-03-20 19:08:27', 'LOGIN_FAILED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'N', 'Usuario: superadmin - Contraseña incorrecta'),
(148, 1, 1, '2026-03-20 19:08:57', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(149, 1, NULL, '2026-03-20 19:46:46', 'LOGIN_FAILED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'N', 'Usuario: superadmin - Contraseña incorrecta'),
(150, 1, 1, '2026-03-20 19:46:59', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(151, 1, 1, '2026-03-21 20:49:48', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(152, 1, 1, '2026-03-21 21:26:22', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(153, 1, 1, '2026-03-22 19:42:06', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(154, 1, 1, '2026-03-22 21:05:38', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(155, 1, 1, '2026-03-24 15:07:57', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(156, 3, NULL, '2026-03-24 19:25:26', 'LOGIN_FAILED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'N', 'Usuario: fbpinzon - Contraseña incorrecta'),
(157, 1, 1, '2026-03-24 19:25:37', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(158, 1, 1, '2026-03-24 22:21:46', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(159, 1, 1, '2026-03-25 08:27:36', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(160, 1, 1, '2026-03-25 09:04:01', 'LOGOUT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Cierre de sesión'),
(161, 1, 1, '2026-03-25 09:04:23', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso'),
(162, 1, 1, '2026-03-25 10:31:49', 'LOGIN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'S', 'Login exitoso');

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
) ENGINE=InnoDB AUTO_INCREMENT=258 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Menús laterales dinámicos por aplicativo/módulo';

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
(165, 15, NULL, 'HEADER', 'Gestión Deportiva', NULL, NULL, NULL, 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-02-09 20:03:14', '2026-03-07 00:15:57'),
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
(192, 22, 104, 'ITEM', 'IPs Bloqueadas', 'fas fa-ban', 'seguridad', 'auditoria', 'ipsBloqueadas', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-02-19 20:30:15', '2026-02-19 20:30:15'),
(193, 22, NULL, 'HEADER', 'Administración de Catálogos', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10, 1, NULL, NULL, '2026-03-04 20:33:39', '2026-03-04 20:33:39'),
(194, 22, 193, 'ITEM', 'Catálogos', 'fas fa-list-check', 'seguridad', 'tabla', 'index', NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-03-04 20:33:39', '2026-03-05 03:35:48'),
(245, 3, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-03-11 06:09:13', '2026-03-11 06:09:13'),
(246, 3, 245, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'facturacion', 'dashboard', 'index', NULL, NULL, NULL, 2, 1, NULL, NULL, '2026-03-11 06:09:13', '2026-03-11 06:09:13'),
(247, 3, NULL, 'HEADER', 'Facturación', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 1, NULL, NULL, '2026-03-11 06:09:13', '2026-03-11 06:09:13'),
(248, 3, 247, 'ITEM', 'Crear Factura', 'fas fa-plus-circle', 'facturacion', 'factura', 'crear', NULL, NULL, NULL, 4, 1, NULL, NULL, '2026-03-11 06:09:13', '2026-03-11 06:09:13'),
(249, 3, 247, 'ITEM', 'Comprobantes', 'fas fa-file-invoice-dollar', 'facturacion', 'comprobante', 'index', NULL, NULL, NULL, 5, 1, NULL, NULL, '2026-03-11 06:09:13', '2026-03-11 06:09:13'),
(250, 3, 247, 'ITEM', 'Facturas Emitidas', 'fas fa-receipt', 'facturacion', 'factura', 'index', NULL, NULL, NULL, 6, 1, NULL, NULL, '2026-03-11 06:09:13', '2026-03-11 06:09:13'),
(251, 3, 247, 'ITEM', 'Facturación SRI', 'fas fa-globe-americas', 'facturacion', 'factura_electronica', 'index', NULL, NULL, NULL, 7, 1, NULL, NULL, '2026-03-11 06:09:13', '2026-03-11 06:09:13'),
(252, 3, NULL, 'HEADER', 'Cobros y Pagos', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, 1, NULL, NULL, '2026-03-11 06:09:13', '2026-03-11 06:09:13'),
(253, 3, 252, 'ITEM', 'Registrar Pago', 'fas fa-cash-register', 'facturacion', 'pago', 'crear', NULL, NULL, NULL, 9, 1, NULL, NULL, '2026-03-11 06:09:13', '2026-03-11 06:09:13'),
(254, 3, 252, 'ITEM', 'Listado de Pagos', 'fas fa-money-check-alt', 'facturacion', 'pago', 'index', NULL, NULL, NULL, 10, 1, NULL, NULL, '2026-03-11 06:09:13', '2026-03-11 06:09:13'),
(255, 3, 247, 'ITEM', 'Configuración FE', 'fas fa-sliders-h', 'facturacion', 'configuracion', 'index', NULL, NULL, NULL, 8, 1, NULL, NULL, '2026-03-12 05:01:04', '2026-03-12 13:45:59'),
(256, 3, 247, 'ITEM', 'Formas de Pago', 'fas fa-credit-card', 'facturacion', 'formaPago', 'index', NULL, NULL, NULL, 9, 1, NULL, NULL, '2026-03-16 03:53:41', '2026-03-16 03:53:41'),
(257, 3, 247, 'ITEM', 'Rubros', 'fas fa-tags', 'facturacion', 'rubro', 'index', NULL, NULL, NULL, 10, 1, NULL, NULL, '2026-03-19 14:09:11', '2026-03-19 14:09:11');

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
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_menu_config`
--

INSERT INTO `seguridad_menu_config` (`con_id`, `con_modulo_codigo`, `con_opcion`, `con_icono`, `con_color`, `con_permiso_requerido`, `con_orden`) VALUES
(1, 'instalaciones', 'Instalaciones', 'fas fa-building', '#2563eb', 'instalaciones.ver', 1),
(2, 'reservas', 'Reservas', 'fas fa-calendar-alt', '#22c55e', 'reservas.ver', 2),
(3, 'facturacion', 'Facturación', 'fas fa-file-invoice', '#f59e0b', 'facturacion.ver', 3),
(4, 'reportes', 'Reportes', 'fas fa-chart-bar', '#a21caf', 'reportes.ver', 4),
(5, 'seguridad', 'Seguridad', 'fas fa-shield-alt', '#ef4444', 'seguridad.ver', 5),
(6, 'facturacion', 'Dashboard', 'fas fa-tachometer-alt', '#F59E0B', 'facturacion.ver', 1),
(7, 'facturacion', 'Crear Factura', 'fas fa-plus-circle', '#F59E0B', 'facturacion.crear', 2),
(8, 'facturacion', 'Comprobantes', 'fas fa-file-invoice-dollar', '#F59E0B', 'facturacion.ver', 3),
(9, 'facturacion', 'Facturas Emitidas', 'fas fa-receipt', '#F59E0B', 'facturacion.ver', 4),
(10, 'facturaci├│n SRI', 'Facturaci├│n SRI', 'fas fa-globe-americas', '#F59E0B', 'facturacion.ver', 5),
(11, 'facturacion', 'Registrar Pago', 'fas fa-cash-register', '#F59E0B', 'facturacion.crear', 6),
(12, 'facturacion', 'Listado de Pagos', 'fas fa-money-check-alt', '#F59E0B', 'facturacion.ver', 7),
(13, 'facturacion', 'Configuraci├│n FE', 'fas fa-sliders-h', '#F59E0B', 'facturacion.configurar', 8),
(14, 'facturacion', 'Formas de Pago', 'fas fa-credit-card', '#F59E0B', 'facturacion.ver', 9),
(15, 'facturacion', 'Formas de Pago', 'fas fa-credit-card', '#F59E0B', 'facturacion.ver', 9),
(16, 'instalaciones', 'Dashboard Arena', 'fas fa-tachometer-alt', '#2563eb', 'instalaciones.ver', 1),
(17, 'instalaciones', 'Canchas', 'fas fa-futbol', '#2563eb', 'instalaciones.ver', 2),
(18, 'instalaciones', 'Mantenimientos', 'fas fa-tools', '#2563eb', 'instalaciones.editar', 3),
(19, 'instalaciones', 'Reservas Arena', 'fas fa-calendar-check', '#2563eb', 'reservas.ver', 4),
(20, 'facturacion', 'Rubros', 'fas fa-tags', '#8B5CF6', 'facturacion.ver', 10);

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
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de módulos/aplicaciones disponibles';

--
-- Volcado de datos para la tabla `seguridad_modulos`
--

INSERT INTO `seguridad_modulos` (`mod_id`, `mod_codigo`, `mod_nombre`, `mod_descripcion`, `mod_icono`, `mod_color_fondo`, `mod_orden`, `mod_ruta_modulo`, `mod_ruta_controller`, `mod_ruta_action`, `mod_es_externo`, `mod_url_externa`, `mod_base_datos_externa`, `mod_requiere_licencia`, `mod_activo`, `mod_created_at`, `mod_updated_at`) VALUES
(1, 'ARENA', 'DigiSports Arena', 'Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.', 'fas fa-building', '#FF7E70', 2, 'instalaciones', 'dashboard', 'index', 0, NULL, NULL, 1, 1, '2026-01-26 05:37:36', '2026-03-05 16:38:00'),
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
(30, 'NUTRICION', 'Planes Nutricionales', 'Seguimiento nutricional de deportistas', 'fas fa-apple-alt', '#fd7e14', 2, 'nutricion', 'dashboard', 'index', 0, '/nutricion/', NULL, 1, 0, '2026-02-07 22:50:38', '2026-03-05 16:38:33'),
(31, 'SOCCEREASY', 'SOCCEREASY', 'Sistema de administración de de la Escuela Champions', 'fa-futbol', '#89F336', 1, NULL, NULL, 'index', 1, 'http://localhost/soccereasy/sso.php', NULL, 0, 1, '2026-03-05 16:37:36', '2026-03-17 13:38:57'),
(32, 'CDJG', 'Jorge Guzman CD', 'Sistema Jorge Guzmán Club Deportivo', 'fas fa-basketball-ball', '#fd7e14', 0, NULL, NULL, 'index', 1, 'http://localhost/cdjg/sso.php', NULL, 0, 1, '2026-03-20 20:00:41', '2026-03-20 20:02:14'),
(33, 'CDJG_COPIA', 'Copia de Jorge Guzman CD', 'Sistema Jorge Guzmán Club Deportivo', 'fas fa-basketball-ball', '#fd7e14', 0, NULL, NULL, 'index', 1, 'http://localhost/cdjg/sso.php', NULL, 0, 0, '2026-03-20 21:16:00', '2026-03-20 21:16:17'),
(34, 'PEDRO_LARREA', 'Escuela Pedro Larrea', 'Sistema Escuela de fútbol Pedro Larrea', 'fa-futbol', '#DE3163', 0, NULL, NULL, 'index', 1, 'http://localhost/adfpedrolarrea/sso.php', NULL, 0, 1, '2026-03-20 21:45:43', '2026-03-20 21:47:02');

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
-- Estructura de tabla para la tabla `seguridad_rate_limit`
--

DROP TABLE IF EXISTS `seguridad_rate_limit`;
CREATE TABLE IF NOT EXISTS `seguridad_rate_limit` (
  `srl_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `srl_ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'IPv4 o IPv6',
  `srl_action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador de la acci├│n limitada',
  `srl_fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp del request',
  PRIMARY KEY (`srl_id`),
  KEY `idx_lookup` (`srl_ip`,`srl_action`,`srl_fecha`),
  KEY `idx_purge` (`srl_fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de requests para rate limiting por IP y acci├│n';

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
(5, 1, 'ADMIN', 'Administrador Tenant', 'Administrador del tenant', '[\"usuarios.*\", \"sedes.*\", \"configuracion.*\", \"facturacion.*\"]', 'N', 'S', 'N', 5, 'A', '2026-01-25 00:35:10'),
(6, 1, 'RECEPCION', 'Recepcionista', 'Gestion de reservas y clientes', '[\"reservas.*\", \"clientes.ver\", \"clientes.crear\", \"pagos.crear\"]', 'N', 'N', 'N', 3, 'A', '2026-01-25 00:35:10'),
(7, 1, 'CLIENTE', 'Cliente', 'Usuario final con acceso limitado', '[\"reservas.ver\", \"reservas.crear\", \"perfil.*\"]', 'N', 'N', 'N', 1, 'A', '2026-01-25 00:35:10'),
(9, NULL, 'ADMIN', 'Administrador', 'Administrador de tenant', '[\"usuarios.*\", \"sedes.*\", \"configuracion.*\", \"facturacion.*\"]', 'N', 'N', 'N', 5, 'A', '2026-01-25 00:35:19'),
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
) ENGINE=InnoDB AUTO_INCREMENT=901 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos de visibilidad de menú por rol';

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
(242, 1, 192, 1, 1, '2026-02-19 20:39:15', '2026-02-19 20:39:15'),
(243, 1, 194, 1, 1, '2026-03-04 20:33:39', '2026-03-04 20:33:39'),
(244, 1, 215, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(245, 1, 216, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(246, 1, 217, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(247, 1, 218, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(248, 1, 219, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(249, 1, 220, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(250, 1, 221, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(251, 1, 222, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(252, 1, 223, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(253, 1, 224, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(254, 3, 215, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(255, 3, 216, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(256, 3, 217, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(257, 3, 218, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(258, 3, 219, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(259, 3, 220, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(260, 3, 221, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(261, 3, 222, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(262, 3, 223, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(263, 3, 224, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(264, 4, 215, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(265, 4, 216, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(266, 4, 217, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(267, 4, 218, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(268, 4, 219, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(269, 4, 220, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(270, 4, 221, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(271, 4, 222, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(272, 4, 223, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(273, 4, 224, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(274, 5, 215, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(275, 5, 216, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(276, 5, 217, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(277, 5, 218, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(278, 5, 219, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(279, 5, 220, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(280, 5, 221, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(281, 5, 222, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(282, 5, 223, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(283, 5, 224, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(284, 6, 215, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(285, 6, 216, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(286, 6, 217, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(287, 6, 218, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(288, 6, 219, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(289, 6, 220, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(290, 6, 221, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(291, 6, 222, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(292, 6, 223, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(293, 6, 224, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(294, 7, 215, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(295, 7, 216, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(296, 7, 217, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(297, 7, 218, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(298, 7, 219, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(299, 7, 220, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(300, 7, 221, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(301, 7, 222, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(302, 7, 223, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(303, 7, 224, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(304, 9, 215, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(305, 9, 216, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(306, 9, 217, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(307, 9, 218, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(308, 9, 219, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(309, 9, 220, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(310, 9, 221, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(311, 9, 222, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(312, 9, 223, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(313, 9, 224, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(314, 10, 215, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(315, 10, 216, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(316, 10, 217, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(317, 10, 218, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(318, 10, 219, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(319, 10, 220, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(320, 10, 221, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(321, 10, 222, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(322, 10, 223, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(323, 10, 224, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(324, 11, 215, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(325, 11, 216, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(326, 11, 217, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(327, 11, 218, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(328, 11, 219, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(329, 11, 220, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(330, 11, 221, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(331, 11, 222, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(332, 11, 223, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(333, 11, 224, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(334, 16, 215, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(335, 16, 216, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(336, 16, 217, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(337, 16, 218, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(338, 16, 219, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(339, 16, 220, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(340, 16, 221, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(341, 16, 222, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(342, 16, 223, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(343, 16, 224, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(344, 17, 215, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(345, 17, 216, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(346, 17, 217, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(347, 17, 218, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(348, 17, 219, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(349, 17, 220, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(350, 17, 221, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(351, 17, 222, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(352, 17, 223, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(353, 17, 224, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(354, 18, 215, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(355, 18, 216, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(356, 18, 217, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(357, 18, 218, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(358, 18, 219, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(359, 18, 220, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(360, 18, 221, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(361, 18, 222, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(362, 18, 223, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(363, 18, 224, 1, 1, '2026-03-11 05:29:41', '2026-03-11 05:29:41'),
(371, 1, 225, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(372, 1, 226, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(373, 1, 227, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(374, 1, 228, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(375, 1, 229, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(376, 1, 230, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(377, 1, 231, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(378, 1, 232, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(379, 1, 233, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(380, 1, 234, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(381, 3, 225, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(382, 3, 226, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(383, 3, 227, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(384, 3, 228, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(385, 3, 229, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(386, 3, 230, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(387, 3, 231, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(388, 3, 232, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(389, 3, 233, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(390, 3, 234, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(391, 4, 225, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(392, 4, 226, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(393, 4, 227, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(394, 4, 228, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(395, 4, 229, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(396, 4, 230, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(397, 4, 231, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(398, 4, 232, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(399, 4, 233, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(400, 4, 234, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(401, 5, 225, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(402, 5, 226, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(403, 5, 227, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(404, 5, 228, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(405, 5, 229, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(406, 5, 230, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(407, 5, 231, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(408, 5, 232, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(409, 5, 233, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(410, 5, 234, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(411, 6, 225, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(412, 6, 226, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(413, 6, 227, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(414, 6, 228, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(415, 6, 229, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(416, 6, 230, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(417, 6, 231, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(418, 6, 232, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(419, 6, 233, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(420, 6, 234, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(421, 7, 225, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(422, 7, 226, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(423, 7, 227, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(424, 7, 228, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(425, 7, 229, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(426, 7, 230, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(427, 7, 231, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(428, 7, 232, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(429, 7, 233, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(430, 7, 234, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(431, 9, 225, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(432, 9, 226, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(433, 9, 227, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(434, 9, 228, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(435, 9, 229, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(436, 9, 230, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(437, 9, 231, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(438, 9, 232, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(439, 9, 233, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(440, 9, 234, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(441, 10, 225, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(442, 10, 226, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(443, 10, 227, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(444, 10, 228, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(445, 10, 229, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(446, 10, 230, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(447, 10, 231, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(448, 10, 232, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(449, 10, 233, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(450, 10, 234, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(451, 11, 225, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(452, 11, 226, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(453, 11, 227, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(454, 11, 228, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(455, 11, 229, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(456, 11, 230, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(457, 11, 231, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(458, 11, 232, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(459, 11, 233, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(460, 11, 234, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(461, 16, 225, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(462, 16, 226, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(463, 16, 227, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(464, 16, 228, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(465, 16, 229, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(466, 16, 230, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(467, 16, 231, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(468, 16, 232, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(469, 16, 233, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(470, 16, 234, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(471, 17, 225, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(472, 17, 226, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(473, 17, 227, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(474, 17, 228, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(475, 17, 229, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(476, 17, 230, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(477, 17, 231, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(478, 17, 232, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(479, 17, 233, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(480, 17, 234, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(481, 18, 225, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(482, 18, 226, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(483, 18, 227, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(484, 18, 228, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(485, 18, 229, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(486, 18, 230, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(487, 18, 231, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(488, 18, 232, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(489, 18, 233, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(490, 18, 234, 1, 1, '2026-03-11 05:32:52', '2026-03-11 05:32:52'),
(498, 1, 235, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(499, 1, 236, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(500, 1, 237, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(501, 1, 238, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(502, 1, 239, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(503, 1, 240, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(504, 1, 241, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(505, 1, 242, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(506, 1, 243, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(507, 1, 244, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(508, 3, 235, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(509, 3, 236, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(510, 3, 237, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(511, 3, 238, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(512, 3, 239, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(513, 3, 240, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(514, 3, 241, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(515, 3, 242, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(516, 3, 243, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(517, 3, 244, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(518, 4, 235, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(519, 4, 236, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(520, 4, 237, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(521, 4, 238, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(522, 4, 239, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(523, 4, 240, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(524, 4, 241, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(525, 4, 242, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(526, 4, 243, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(527, 4, 244, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(528, 5, 235, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(529, 5, 236, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(530, 5, 237, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(531, 5, 238, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(532, 5, 239, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(533, 5, 240, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(534, 5, 241, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(535, 5, 242, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(536, 5, 243, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(537, 5, 244, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(538, 6, 235, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(539, 6, 236, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(540, 6, 237, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(541, 6, 238, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(542, 6, 239, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(543, 6, 240, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(544, 6, 241, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(545, 6, 242, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(546, 6, 243, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(547, 6, 244, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(548, 7, 235, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(549, 7, 236, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(550, 7, 237, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(551, 7, 238, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(552, 7, 239, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(553, 7, 240, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(554, 7, 241, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(555, 7, 242, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(556, 7, 243, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(557, 7, 244, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(558, 9, 235, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(559, 9, 236, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(560, 9, 237, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(561, 9, 238, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(562, 9, 239, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(563, 9, 240, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(564, 9, 241, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(565, 9, 242, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(566, 9, 243, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(567, 9, 244, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(568, 10, 235, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(569, 10, 236, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(570, 10, 237, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(571, 10, 238, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(572, 10, 239, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(573, 10, 240, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(574, 10, 241, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(575, 10, 242, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(576, 10, 243, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(577, 10, 244, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(578, 11, 235, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(579, 11, 236, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(580, 11, 237, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(581, 11, 238, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(582, 11, 239, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(583, 11, 240, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(584, 11, 241, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(585, 11, 242, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(586, 11, 243, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(587, 11, 244, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(588, 16, 235, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(589, 16, 236, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(590, 16, 237, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(591, 16, 238, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(592, 16, 239, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(593, 16, 240, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(594, 16, 241, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(595, 16, 242, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(596, 16, 243, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(597, 16, 244, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(598, 17, 235, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(599, 17, 236, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(600, 17, 237, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(601, 17, 238, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(602, 17, 239, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(603, 17, 240, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(604, 17, 241, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(605, 17, 242, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(606, 17, 243, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(607, 17, 244, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(608, 18, 235, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(609, 18, 236, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(610, 18, 237, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(611, 18, 238, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(612, 18, 239, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(613, 18, 240, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(614, 18, 241, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(615, 18, 242, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(616, 18, 243, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(617, 18, 244, 1, 1, '2026-03-11 05:33:35', '2026-03-11 05:33:35'),
(625, 1, 245, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(626, 1, 246, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(627, 1, 247, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(628, 1, 248, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(629, 1, 249, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(630, 1, 250, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(631, 1, 251, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(632, 1, 252, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(633, 1, 253, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(634, 1, 254, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(635, 3, 245, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(636, 3, 246, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(637, 3, 247, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(638, 3, 248, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(639, 3, 249, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(640, 3, 250, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(641, 3, 251, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(642, 3, 252, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(643, 3, 253, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(644, 3, 254, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(645, 4, 245, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(646, 4, 246, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(647, 4, 247, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(648, 4, 248, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(649, 4, 249, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(650, 4, 250, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(651, 4, 251, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(652, 4, 252, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(653, 4, 253, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(654, 4, 254, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(655, 5, 245, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(656, 5, 246, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(657, 5, 247, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(658, 5, 248, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(659, 5, 249, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(660, 5, 250, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(661, 5, 251, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(662, 5, 252, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(663, 5, 253, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(664, 5, 254, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(665, 6, 245, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(666, 6, 246, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(667, 6, 247, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(668, 6, 248, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(669, 6, 249, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(670, 6, 250, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(671, 6, 251, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(672, 6, 252, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(673, 6, 253, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(674, 6, 254, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(675, 7, 245, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(676, 7, 246, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(677, 7, 247, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(678, 7, 248, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(679, 7, 249, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(680, 7, 250, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(681, 7, 251, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(682, 7, 252, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(683, 7, 253, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(684, 7, 254, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(685, 9, 245, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(686, 9, 246, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(687, 9, 247, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(688, 9, 248, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(689, 9, 249, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(690, 9, 250, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(691, 9, 251, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(692, 9, 252, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(693, 9, 253, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(694, 9, 254, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(695, 10, 245, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(696, 10, 246, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(697, 10, 247, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(698, 10, 248, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(699, 10, 249, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(700, 10, 250, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(701, 10, 251, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(702, 10, 252, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(703, 10, 253, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(704, 10, 254, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(705, 11, 245, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(706, 11, 246, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(707, 11, 247, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(708, 11, 248, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(709, 11, 249, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(710, 11, 250, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(711, 11, 251, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(712, 11, 252, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(713, 11, 253, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(714, 11, 254, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(715, 16, 245, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(716, 16, 246, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(717, 16, 247, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(718, 16, 248, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(719, 16, 249, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(720, 16, 250, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(721, 16, 251, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(722, 16, 252, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(723, 16, 253, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(724, 16, 254, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(725, 17, 245, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(726, 17, 246, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(727, 17, 247, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(728, 17, 248, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(729, 17, 249, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(730, 17, 250, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(731, 17, 251, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(732, 17, 252, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(733, 17, 253, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(734, 17, 254, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(735, 18, 245, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(736, 18, 246, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(737, 18, 247, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(738, 18, 248, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(739, 18, 249, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(740, 18, 250, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(741, 18, 251, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(742, 18, 252, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(743, 18, 253, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(744, 18, 254, 1, 1, '2026-03-11 06:09:14', '2026-03-11 06:09:14'),
(752, 1, 255, 1, 1, '2026-03-12 05:01:04', '2026-03-12 05:01:04'),
(753, 5, 255, 1, 1, '2026-03-12 05:01:04', '2026-03-12 05:01:04'),
(754, 9, 255, 1, 1, '2026-03-12 05:01:04', '2026-03-12 05:01:04'),
(755, 16, 255, 1, 1, '2026-03-12 05:01:04', '2026-03-12 05:01:04'),
(760, 16, 256, 1, 1, '2026-03-16 03:53:41', '2026-03-16 03:53:41'),
(761, 10, 256, 1, 1, '2026-03-16 03:53:41', '2026-03-16 03:53:41'),
(762, 9, 256, 1, 1, '2026-03-16 03:53:41', '2026-03-16 03:53:41'),
(763, 6, 256, 1, 1, '2026-03-16 03:53:41', '2026-03-16 03:53:41'),
(764, 5, 256, 1, 1, '2026-03-16 03:53:41', '2026-03-16 03:53:41'),
(765, 3, 256, 1, 1, '2026-03-16 03:53:41', '2026-03-16 03:53:41'),
(766, 1, 256, 1, 1, '2026-03-16 03:53:41', '2026-03-16 03:53:41'),
(767, 16, 1, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(768, 10, 1, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(769, 9, 1, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(770, 6, 1, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(771, 5, 1, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(772, 3, 1, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(773, 1, 1, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(774, 16, 2, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(775, 10, 2, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(776, 9, 2, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(777, 6, 2, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(778, 5, 2, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(779, 3, 2, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(780, 16, 3, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(781, 10, 3, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(782, 9, 3, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(783, 6, 3, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(784, 5, 3, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(785, 3, 3, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(786, 16, 4, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(787, 10, 4, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(788, 9, 4, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(789, 6, 4, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(790, 5, 4, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(791, 3, 4, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(792, 16, 5, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(793, 10, 5, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(794, 9, 5, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(795, 6, 5, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(796, 5, 5, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(797, 3, 5, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(798, 16, 110, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(799, 10, 110, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(800, 9, 110, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(801, 6, 110, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(802, 5, 110, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(803, 3, 110, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(804, 16, 111, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(805, 10, 111, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(806, 9, 111, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(807, 6, 111, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(808, 5, 111, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(809, 3, 111, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(810, 16, 112, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(811, 10, 112, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(812, 9, 112, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(813, 6, 112, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(814, 5, 112, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(815, 3, 112, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(816, 16, 113, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(817, 10, 113, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(818, 9, 113, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(819, 6, 113, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(820, 5, 113, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(821, 3, 113, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(822, 16, 114, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(823, 10, 114, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(824, 9, 114, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(825, 6, 114, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(826, 5, 114, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(827, 3, 114, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(828, 16, 115, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(829, 10, 115, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(830, 9, 115, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(831, 6, 115, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(832, 5, 115, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(833, 3, 115, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(834, 16, 116, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(835, 10, 116, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(836, 9, 116, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(837, 6, 116, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(838, 5, 116, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(839, 3, 116, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(840, 16, 117, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(841, 10, 117, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(842, 9, 117, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(843, 6, 117, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(844, 5, 117, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(845, 3, 117, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(846, 16, 119, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(847, 10, 119, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(848, 9, 119, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(849, 6, 119, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(850, 5, 119, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(851, 3, 119, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(852, 1, 119, 1, 1, '2026-03-16 05:22:48', '2026-03-16 05:22:48'),
(894, 16, 257, 1, 1, '2026-03-19 14:09:11', '2026-03-19 14:09:11'),
(895, 10, 257, 1, 1, '2026-03-19 14:09:11', '2026-03-19 14:09:11'),
(896, 9, 257, 1, 1, '2026-03-19 14:09:11', '2026-03-19 14:09:11');
INSERT INTO `seguridad_rol_menu` (`rme_id`, `rme_rol_id`, `rme_menu_id`, `rme_puede_ver`, `rme_puede_acceder`, `rme_created_at`, `rme_updated_at`) VALUES
(897, 6, 257, 1, 1, '2026-03-19 14:09:11', '2026-03-19 14:09:11'),
(898, 5, 257, 1, 1, '2026-03-19 14:09:11', '2026-03-19 14:09:11'),
(899, 3, 257, 1, 1, '2026-03-19 14:09:11', '2026-03-19 14:09:11'),
(900, 1, 257, 1, 1, '2026-03-19 14:09:11', '2026-03-19 14:09:11');

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
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos de roles sobre módulos';

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
(145, 1, 21, 1, 1, 1, 1, NULL, '2026-02-08 01:33:42'),
(146, 5, 3, 1, 1, 1, 0, '{\"configurar\": 1}', '2026-03-12 19:20:59'),
(147, 9, 3, 1, 1, 1, 0, '{\"configurar\": 1}', '2026-03-12 19:20:59'),
(148, 16, 3, 1, 1, 1, 0, '{\"configurar\": 1}', '2026-03-12 19:20:59'),
(149, 3, 1, 1, 1, 1, 0, '{}', '2026-03-16 05:22:48'),
(150, 5, 1, 1, 1, 1, 0, '{}', '2026-03-16 05:22:48'),
(151, 6, 1, 1, 1, 1, 0, '{}', '2026-03-16 05:22:48'),
(152, 9, 1, 1, 1, 1, 0, '{}', '2026-03-16 05:22:48'),
(153, 10, 1, 1, 1, 1, 0, '{}', '2026-03-16 05:22:48'),
(154, 16, 1, 1, 1, 1, 0, '{}', '2026-03-16 05:22:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_tabla`
--

DROP TABLE IF EXISTS `seguridad_tabla`;
CREATE TABLE IF NOT EXISTS `seguridad_tabla` (
  `st_id` int NOT NULL AUTO_INCREMENT,
  `st_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `st_descripcion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_activo` tinyint(1) NOT NULL DEFAULT '1',
  `st_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `st_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`st_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_tabla`
--

INSERT INTO `seguridad_tabla` (`st_id`, `st_nombre`, `st_descripcion`, `st_activo`, `st_created_at`, `st_updated_at`) VALUES
(1, 'tipo_documento', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(2, 'nacionalidad', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(3, 'posicion_juego', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(4, 'parentesco', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(5, 'rubros', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(6, 'forma_pago', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(7, 'descuento', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(8, 'especialidad_empleado', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(9, 'tipo_participacion', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(10, 'tallas', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(11, 'tipo_ingreso', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(12, 'tipo_personal', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(13, 'tipo_egreso', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(14, 'egreso_dscto', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(15, 'periodicidad', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(16, 'forma_entregaingreso', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(17, 'balance_ingreso', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(18, 'balance_egreso', NULL, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad_tabla_catalogo`
--

DROP TABLE IF EXISTS `seguridad_tabla_catalogo`;
CREATE TABLE IF NOT EXISTS `seguridad_tabla_catalogo` (
  `stc_id` int NOT NULL AUTO_INCREMENT,
  `stc_codigo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stc_tabla_id` int NOT NULL,
  `stc_valor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stc_etiqueta` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stc_orden` int NOT NULL DEFAULT '10',
  `stc_activo` tinyint(1) NOT NULL DEFAULT '1',
  `stc_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `stc_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stc_id`),
  UNIQUE KEY `idx_stc_grupo_codigo` (`stc_tabla_id`,`stc_codigo`),
  KEY `idx_stc_orden` (`stc_orden`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguridad_tabla_catalogo`
--

INSERT INTO `seguridad_tabla_catalogo` (`stc_id`, `stc_codigo`, `stc_tabla_id`, `stc_valor`, `stc_etiqueta`, `stc_orden`, `stc_activo`, `stc_created_at`, `stc_updated_at`) VALUES
(1, 'CED', 1, 'CÉDULA', 'CÉDULA', 10, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(2, 'PAS', 1, 'PASAPORTE', 'PASAPORTE', 20, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(3, 'DNI', 1, 'DNI', 'DNI', 30, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(4, 'ECU', 2, 'ECUATORIANA', 'ECUATORIANA', 40, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(5, 'PER', 2, 'PERUANA', 'PERUANA', 50, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(6, 'COL', 2, 'COLOMBIANA', 'COLOMBIANA', 60, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(7, 'VEN', 2, 'VENEZOLANA', 'VENEZOLANA', 70, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(8, 'USA', 2, 'ESTADOUNIDENSE', 'ESTADOUNIDENSE', 80, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(9, '3DE', 3, 'Delantero', 'Delantero', 90, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(10, '3AR', 3, 'Portero', 'Portero', 100, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(11, '3CE', 3, 'Centrocampista', 'Centrocampista', 110, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(12, '3DF', 3, 'Defensa', 'Defensa', 120, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(13, '4MA', 4, 'Madre', 'Madre', 130, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(14, '4PA', 4, 'Padre', 'Padre', 140, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(15, '4HE', 4, 'Hermano/a', 'Hermano/a', 150, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(16, '4TI', 4, 'Tio/a', 'Tio/a', 160, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(17, '4AB', 4, 'Abuelo/a', 'Abuelo/a', 170, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(18, 'ROT', 5, 'Otros', 'Otros', 180, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(19, 'RKE', 5, 'Kit entrenamiento', 'Kit entrenamiento', 190, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(20, 'RIN', 5, 'Inscripción', 'Inscripción', 200, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(21, 'RPE', 5, 'Pensión', 'Pensión', 210, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(22, 'RNU', 5, 'Nuevo Uniforme', 'Nuevo Uniforme', 220, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(23, 'FEF', 6, 'Efectivo', 'Efectivo', 230, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(24, 'FTR', 6, 'Transferencia', 'Transferencia', 240, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(25, 'FTC', 6, 'Tarjeta', 'Tarjeta', 250, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(26, 'FJU', 6, 'Justificado', 'Justificado', 260, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(27, 'DBC', 7, 'Beca', 'Beca', 270, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(28, 'DDS', 7, 'Descuento', 'Descuento', 280, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(29, 'FNA', 6, 'No aplica', 'No aplica', 290, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(30, 'EAT', 8, 'Asistente técnico de arqueros', 'Asistente técnico de arqueros', 300, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(31, 'EED', 8, 'Entrenador delanteros', 'Entrenador delanteros', 310, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(32, 'PJP', 9, 'Jugador Principal', 'Jugador Principal', 320, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(33, 'PJS', 9, 'Jugador Suplente', 'Jugador Suplente', 330, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(34, 'EEG', 8, 'Entrenador general', 'Entrenador general', 340, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(35, 'FTL', 6, 'Transferencia Banco de Loja', 'Transferencia Banco de Loja', 350, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(36, 'FTP', 6, 'Transferencia Banco Pichincha', 'Transferencia Banco Pichincha', 360, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(37, 'T28', 10, '28', '28', 370, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(38, 'T30', 10, '30', '30', 380, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(39, 'T32', 10, '32', '32', 390, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(40, 'T34', 10, '34', '34', 400, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(41, 'T36', 10, '36', '36', 410, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(42, 'TS', 10, 'S', 'S', 420, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(43, 'TM', 10, 'M', 'M', 430, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(44, 'TL', 10, 'L', 'L', 440, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(45, 'TIH', 11, 'Pago honorarios', 'Pago honorarios', 450, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(46, 'TIX', 11, 'Horas Extras', 'Horas Extras', 460, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(47, 'TIR', 11, 'Reconocimiento', 'Reconocimiento', 470, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(48, 'TPE', 12, 'Empleado', 'Empleado', 480, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(49, 'TPS', 12, 'Secretaria', 'Secretaria', 490, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(50, 'TPP', 12, 'Profesor', 'Profesor', 500, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(51, 'TPT', 12, 'Asistente', 'Asistente', 510, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(52, 'TEA', 13, 'Anticipo', 'Anticipo', 520, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(53, 'TEM', 13, 'Multa', 'Multa', 530, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(54, 'PEM', 15, 'Mensual', 'Mensual', 540, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(55, 'PEQ', 15, 'Quincenal', 'Quincenal', 550, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(56, 'PES', 15, 'Semanal', 'Semanal', 560, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(57, 'EIT', 16, 'Transferencia', 'Transferencia', 570, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(58, 'EIE', 16, 'Efectivo', 'Efectivo', 580, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(59, 'EIC', 16, 'Cheque', 'Cheque', 590, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(60, 'EID', 16, 'Descuento', 'Descuento', 600, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(61, 'BIA', 17, 'Auspicio', 'Auspicio', 610, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(62, 'BID', 17, 'Donación', 'Donación', 620, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(63, 'BIO', 17, 'Otros ingresos', 'Otros ingresos', 630, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(64, 'BEA', 18, 'Arriendos', 'Arriendos', 640, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(65, 'BEP', 18, 'Publicidad', 'Publicidad', 650, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(66, 'BEO', 18, 'Otros Egresos', 'Otros Egresos', 660, 1, '2026-03-05 09:00:56', '2026-03-05 10:39:05'),
(67, 'RPC', 5, 'Campeonato', 'Campeonato', 670, 1, '2026-03-05 09:00:56', '2026-03-05 09:00:56'),
(69, 'RUC', 1, 'RUC', 'RUC', 40, 1, '2026-03-17 11:47:55', '2026-03-17 11:47:55');

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
(1, 'ENC::PdWMhasvwaZMVa+BsxR6+A+04lXjYx0S0vi8idlO8sU=', '61fdfa41b29b7fd7e6e97822c91fe2ce', 'DigiSports Administracion', 'DigiSports Admin', '', 'Rey david y los Olivos', 'ENC::TbmV+Bs3g+VC9MOOfxo2IaUBe6P138oaDaLjt6fOv9I=', '', 'ENC::HhE29JKT+1xfnywKteBPNssU1Hcp4/FeHy4/+QyktBtsqG9h11r3yataqcjZnMg7', 'fa2536059c2cfc78fe680f0629a1859d', '', '', '', NULL, '', NULL, '', 4, '2026-01-24', '2028-01-24', 'ACTIVA', 5, 0, 10, NULL, NULL, '', '', '#28a745', 'N', NULL, NULL, 0.00, 1, NULL, 'America/Guayaquil', 'es', 'USD', 'A', NULL, NULL, '2026-01-25 00:35:10', '2026-03-20 20:01:09', NULL, NULL),
(2, 'ENC::fuOJ+1d1b8CkLBIUCvOyD1kor4Z9j8NRHLsRPbIBLEw=', '426abbcb34966d2c6650f86367f4f54d', 'Champions', 'Champios CF 2013', '', '', 'ENC::mzb0V4c8tN0ooCxH9XtnRFJIWkqP7XDyC98Tbr7Rkzw=', '', 'ENC::Hv4ubAWljIyMHw8zf+GHSJD/8fy/N2RFlIXbn68azsMNIdAGRk6MKh+1gcsNB+4D', 'fa2536059c2cfc78fe680f0629a1859d', '', '', '', NULL, '', NULL, '', 2, '2026-01-27', '2029-04-27', 'ACTIVA', 6, 0, 10, NULL, NULL, '', '', '#28a745', 'N', NULL, NULL, 0.00, 1, NULL, 'America/Guayaquil', 'es', 'USD', 'A', NULL, NULL, '2026-01-27 05:27:48', '2026-03-16 21:00:31', NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=719 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Suscripciones de tenants a módulos';

--
-- Volcado de datos para la tabla `seguridad_tenant_modulos`
--

INSERT INTO `seguridad_tenant_modulos` (`tmo_id`, `tmo_tenant_id`, `tmo_modulo_id`, `tmo_nombre_personalizado`, `tmo_icono_personalizado`, `tmo_color_personalizado`, `tmo_orden_visualizacion`, `tmo_activo`, `tmo_fecha_inicio`, `tmo_fecha_fin`, `tmo_estado`, `tmo_tipo_licencia`, `tmo_max_usuarios`, `tmo_observaciones`, `tmo_created_at`, `tmo_updated_at`) VALUES
(511, 0, 0, NULL, NULL, NULL, 0, 'S', '2026-02-25', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-02-25 17:23:43', '2026-02-25 17:23:43'),
(675, 2, 1, NULL, NULL, NULL, 0, 'S', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(676, 2, 3, NULL, NULL, NULL, 0, 'S', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(677, 2, 4, NULL, NULL, NULL, 0, 'S', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(678, 2, 5, NULL, NULL, NULL, 0, 'N', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(679, 2, 6, NULL, NULL, NULL, 0, 'N', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(680, 2, 8, NULL, NULL, NULL, 0, 'N', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(681, 2, 15, NULL, NULL, NULL, 0, 'N', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(682, 2, 16, NULL, NULL, NULL, 0, 'N', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(683, 2, 18, NULL, NULL, NULL, 0, 'N', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(684, 2, 19, NULL, NULL, NULL, 0, 'N', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(685, 2, 20, NULL, NULL, NULL, 0, 'N', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(686, 2, 21, NULL, NULL, NULL, 0, 'S', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(687, 2, 22, NULL, NULL, NULL, 0, 'N', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(688, 2, 31, NULL, NULL, NULL, 0, 'S', '2026-03-16', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-16 21:00:31', '2026-03-16 21:00:31'),
(704, 1, 1, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(705, 1, 3, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(706, 1, 4, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(707, 1, 5, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(708, 1, 6, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(709, 1, 8, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(710, 1, 15, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(711, 1, 16, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(712, 1, 18, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(713, 1, 19, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(714, 1, 20, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(715, 1, 21, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(716, 1, 22, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(717, 1, 31, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09'),
(718, 1, 32, NULL, NULL, NULL, 0, 'S', '2026-03-20', NULL, 'ACTIVO', 'MENSUAL', NULL, NULL, '2026-03-20 20:01:09', '2026-03-20 20:01:09');

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
(1, 1, 'ENC::1IeSjwyCnY1tTslc8FJ7WUme8b1HuxmAhl0cjF78U/c=', '46e867782d4667050ad7bf37c46a7107', 'Freddy', 'Pinzón', 'ENC::N8M5PUSsUol/X8uKN/PMBHj5PJ5em+R7qqqImEVO3qiGtF07Q21IhWHHlC/S6Qsa', 'c0e957495bb43b36f0ff7dea96030260', 'ENC::yeNJ1lTl5ufG6m0IduQTxMBubjp17a3yFRRgPbnBpdk=', 'ENC::tYpnIfZI5bK0QOhffMikZeAxzkzK4bcvtvdEChSNhxA=', 'superadmin', '$argon2id$v=19$m=65536,t=4,p=3$Qm9TcVczOThmQkh4N0hDTg$gdL3FkFtDnw+MnyyE+VIxagpRB019YMCK0w+fOpeydg', 'N', '798279', '2026-01-24 20:21:48', 0, NULL, NULL, 1, NULL, '2026-03-25 10:31:49', '::1', 'd46c1a54c78b28260bf588612ead286bf1e0d7218452375938c70b356bcff026', '2026-02-24 17:56:18', NULL, NULL, NULL, 'light', 'es', 'S', 'S', 'N', '2027-01-01', 0, NULL, 'A', '2026-01-25 00:35:10', '2026-03-25 15:31:49'),
(3, 2, 'ENC::0U+LS3T2LOMHX2l/jl/qL4nVQLTEjcKeon2MYNgcpGc=', '46e867782d4667050ad7bf37c46a7107', 'Bolívar', 'Pinzón', 'ENC::lPjwchXnjfYtM3x/w6LV1c4ZJOi2zQD+2GxptRITiLuHCAA/eUz0mP/qygGoHNDI', 'fa2536059c2cfc78fe680f0629a1859d', 'ENC::kQxrCU/qWLYGnTzgNPzpRAIznHq+39WCeEQruprq2Cw=', '', 'fbpinzon', '$argon2id$v=19$m=65536,t=4,p=1$S2wzZFc0cGFpaUouNXRCcw$CyDE4Ij59TlCNt5rs9Srt1Z5My2VtSdk7NmlO8yiq30', 'N', NULL, NULL, 0, NULL, NULL, 1, NULL, '2026-03-09 10:13:57', '::1', NULL, NULL, NULL, NULL, NULL, 'light', 'es', 'S', 'S', 'N', NULL, 2, NULL, 'A', '2026-01-29 22:22:55', '2026-03-25 00:25:26');

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

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `facturacion_facturas`
--
ALTER TABLE `facturacion_facturas`
  ADD CONSTRAINT `fk_fac_forma_pago` FOREIGN KEY (`fac_forma_pago_id`) REFERENCES `facturacion_formas_pago` (`fpa_id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `facturacion_lineas`
--
ALTER TABLE `facturacion_lineas`
  ADD CONSTRAINT `fk_lin_factura` FOREIGN KEY (`lin_factura_id`) REFERENCES `facturacion_facturas` (`fac_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `facturacion_pagos`
--
ALTER TABLE `facturacion_pagos`
  ADD CONSTRAINT `fk_pag_factura` FOREIGN KEY (`pag_factura_id`) REFERENCES `facturacion_facturas` (`fac_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pag_forma_pago` FOREIGN KEY (`pag_forma_pago_id`) REFERENCES `facturacion_formas_pago` (`fpa_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
