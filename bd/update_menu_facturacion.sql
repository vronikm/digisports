-- Script para insertar los elementos del menú lateral del módulo de Facturación
-- El módulo de Facturación tiene mod_id = 3 en la tabla seguridad_modulos

USE `digisports_core`;

-- Borrar menú anterior del módulo 3 si existe por si acaso
DELETE FROM `seguridad_menu` WHERE `men_modulo_id` = 3;

-- 1. HEADER: Principal
INSERT INTO `seguridad_menu` (`men_modulo_id`, `men_tipo`, `men_label`, `men_orden`, `men_activo`) VALUES (3, 'HEADER', 'Principal', 1, 1);
SET @h_principal = LAST_INSERT_ID();

-- ITEMS para Principal
INSERT INTO `seguridad_menu` (`men_modulo_id`, `men_padre_id`, `men_tipo`, `men_label`, `men_icono`, `men_ruta_modulo`, `men_ruta_controller`, `men_ruta_action`, `men_orden`, `men_activo`) VALUES
(3, @h_principal, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'facturacion', 'dashboard', 'index', 2, 1);

-- 2. HEADER: Facturación
INSERT INTO `seguridad_menu` (`men_modulo_id`, `men_tipo`, `men_label`, `men_orden`, `men_activo`) VALUES (3, 'HEADER', 'Facturación', 3, 1);
SET @h_facturacion = LAST_INSERT_ID();

-- ITEMS para Facturación
INSERT INTO `seguridad_menu` (`men_modulo_id`, `men_padre_id`, `men_tipo`, `men_label`, `men_icono`, `men_ruta_modulo`, `men_ruta_controller`, `men_ruta_action`, `men_orden`, `men_activo`) VALUES
(3, @h_facturacion, 'ITEM', 'Crear Factura', 'fas fa-plus-circle', 'facturacion', 'factura', 'crear', 4, 1),
(3, @h_facturacion, 'ITEM', 'Comprobantes', 'fas fa-file-invoice-dollar', 'facturacion', 'comprobante', 'index', 5, 1),
(3, @h_facturacion, 'ITEM', 'Facturas Emitidas', 'fas fa-receipt', 'facturacion', 'factura', 'index', 6, 1),
(3, @h_facturacion, 'ITEM', 'Facturación SRI', 'fas fa-globe-americas', 'facturacion', 'factura_electronica', 'index', 7, 1);

-- 3. HEADER: Cobros y Pagos
INSERT INTO `seguridad_menu` (`men_modulo_id`, `men_tipo`, `men_label`, `men_orden`, `men_activo`) VALUES (3, 'HEADER', 'Cobros y Pagos', 8, 1);
SET @h_cobros = LAST_INSERT_ID();

-- ITEMS para Cobros y Pagos
INSERT INTO `seguridad_menu` (`men_modulo_id`, `men_padre_id`, `men_tipo`, `men_label`, `men_icono`, `men_ruta_modulo`, `men_ruta_controller`, `men_ruta_action`, `men_orden`, `men_activo`) VALUES
(3, @h_cobros, 'ITEM', 'Registrar Pago', 'fas fa-cash-register', 'facturacion', 'pago', 'crear', 9, 1),
(3, @h_cobros, 'ITEM', 'Listado de Pagos', 'fas fa-money-check-alt', 'facturacion', 'pago', 'index', 10, 1);

-- Dar permisos a los roles activos para ver estos menús recién creados
INSERT IGNORE INTO `seguridad_rol_menu` (`rme_rol_id`, `rme_menu_id`, `rme_puede_ver`, `rme_puede_acceder`)
SELECT r.rol_rol_id, m.men_id, 1, 1
FROM `seguridad_roles` r
CROSS JOIN `seguridad_menu` m
WHERE m.men_modulo_id = 3 AND r.rol_estado = 'A';
