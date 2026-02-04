-- =====================================================
-- DigiSports - Módulo de Seguridad
-- Script de instalación
-- =====================================================

USE digisports_core;

-- ===========================================
-- AGREGAR MÓDULO DE SEGURIDAD AL SISTEMA
-- ===========================================

INSERT INTO modulos_sistema (
    codigo, 
    nombre, 
    descripcion, 
    icono, 
    color, 
    url_base, 
    orden_visualizacion, 
    estado
) VALUES (
    'seguridad',
    'Seguridad',
    'Administración del sistema: usuarios, roles, módulos, tenants y planes',
    'fas fa-shield-alt',
    '#EF4444',
    '/seguridad',
    99,
    'A'
) ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    icono = VALUES(icono),
    color = VALUES(color);

-- ===========================================
-- NOTA: Las tablas planes_suscripcion y roles ya existen
-- Solo insertamos datos predefinidos
-- ===========================================

-- ===========================================
-- PLANES PREDEFINIDOS
-- ===========================================

INSERT INTO planes_suscripcion (codigo, nombre, descripcion, precio_mensual, precio_anual, usuarios_incluidos, almacenamiento_gb, caracteristicas, color, es_destacado) VALUES
('starter', 'Starter', 'Ideal para comenzar', 29.99, 299.99, 3, 1, '["Soporte por email", "Actualizaciones mensuales", "1 módulo deportivo"]', '#6B7280', 'N'),
('profesional', 'Profesional', 'Para negocios en crecimiento', 79.99, 799.99, 10, 5, '["Soporte por email y chat", "Actualizaciones semanales", "5 módulos deportivos", "Reportes avanzados"]', '#3B82F6', 'S'),
('enterprise', 'Enterprise', 'Solución completa para grandes organizaciones', 199.99, 1999.99, 50, 50, '["Soporte 24/7 telefónico", "Actualizaciones prioritarias", "Todos los módulos", "API personalizada", "Capacitación incluida"]', '#8B5CF6', 'N')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- ===========================================
-- ROLES PREDEFINIDOS
-- ===========================================

INSERT INTO roles (codigo, nombre, descripcion, nivel_acceso, permisos, es_super_admin, es_admin_tenant) VALUES
('superadmin', 'Super Administrador', 'Acceso total al sistema', 5, '["*"]', 'S', 'S'),
('admin', 'Administrador', 'Gestión completa del tenant', 4, '["dashboard.*", "clientes.*", "instalaciones.*", "reservas.*", "facturacion.*", "reportes.*", "usuarios.ver", "usuarios.crear", "usuarios.editar"]', 'N', 'S'),
('operador', 'Operador', 'Operaciones diarias', 2, '["dashboard.ver", "clientes.ver", "clientes.crear", "clientes.editar", "reservas.*", "facturacion.ver", "facturacion.crear"]', 'N', 'N'),
('consulta', 'Consulta', 'Solo lectura', 1, '["dashboard.ver", "clientes.ver", "instalaciones.ver", "reservas.ver", "facturacion.ver", "reportes.ver"]', 'N', 'N')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- ===========================================
-- TABLA DE LOGS DE AUDITORÍA
-- ===========================================

CREATE TABLE IF NOT EXISTS auditoria_logs (
    log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NULL,
    usuario_id INT NULL,
    accion VARCHAR(100) NOT NULL,
    tabla VARCHAR(100),
    registro_id INT,
    datos_anteriores JSON,
    datos_nuevos JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- TABLA DE CONFIGURACIONES POR TENANT
-- ===========================================

CREATE TABLE IF NOT EXISTS tenant_configuraciones (
    config_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    clave VARCHAR(100) NOT NULL,
    valor TEXT,
    tipo ENUM('string', 'int', 'bool', 'json') DEFAULT 'string',
    descripcion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant_clave (tenant_id, clave),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- VERIFICACIÓN
-- ===========================================

SELECT 'Módulo de Seguridad instalado correctamente' AS resultado;

SELECT * FROM modulos_sistema WHERE codigo = 'seguridad';
SELECT * FROM planes_suscripcion;
SELECT * FROM roles WHERE es_super_admin = 'S' OR es_admin_tenant = 'S';
