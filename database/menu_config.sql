
-- Menú dinámico y configuración visual

DROP TABLE IF EXISTS menu_config;
CREATE TABLE menu_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modulo_codigo VARCHAR(50) NOT NULL,
    opcion VARCHAR(100) NOT NULL,
    icono VARCHAR(50) NOT NULL,
    color VARCHAR(20) DEFAULT NULL,
    permiso_requerido VARCHAR(100) DEFAULT NULL,
    orden INT DEFAULT 0
);

-- Ejemplo de inserción de opciones de menú
INSERT INTO menu_config (modulo_codigo, opcion, icono, color, permiso_requerido, orden) VALUES
('instalaciones', 'Instalaciones', 'fas fa-building', '#2563eb', 'instalaciones.ver', 1),
('reservas', 'Reservas', 'fas fa-calendar-alt', '#22c55e', 'reservas.ver', 2),
('facturacion', 'Facturación', 'fas fa-file-invoice', '#f59e0b', 'facturacion.ver', 3),
('reportes', 'Reportes', 'fas fa-chart-bar', '#a21caf', 'reportes.ver', 4),
('seguridad', 'Seguridad', 'fas fa-shield-alt', '#ef4444', 'seguridad.ver', 5);

-- El menú lateral se debe construir consultando esta tabla según los módulos y permisos del usuario.
