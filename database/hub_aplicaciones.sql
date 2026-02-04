-- =====================================================
-- DigiSports - Hub de Aplicaciones
-- Sistema Multi-tenant con Módulos por Suscripción
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar tablas existentes para recrearlas correctamente
DROP TABLE IF EXISTS rol_modulos;
DROP TABLE IF EXISTS tenant_modulos;
DROP TABLE IF EXISTS modulos;

SET FOREIGN_KEY_CHECKS = 1;

-- Tabla de Módulos/Aplicaciones disponibles en la plataforma
CREATE TABLE modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE COMMENT 'Código único del módulo',
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(500) DEFAULT NULL,
    icono VARCHAR(100) DEFAULT 'fas fa-cube' COMMENT 'Clase Font Awesome',
    color_fondo VARCHAR(20) DEFAULT '#3B82F6' COMMENT 'Color del icono en hex',
    orden INT DEFAULT 0 COMMENT 'Orden de visualización',
    ruta_modulo VARCHAR(100) DEFAULT NULL COMMENT 'module para el router',
    ruta_controller VARCHAR(100) DEFAULT NULL COMMENT 'controller para el router',
    ruta_action VARCHAR(100) DEFAULT 'index' COMMENT 'action para el router',
    es_externo TINYINT(1) DEFAULT 0 COMMENT '1=Sistema externo con su propia BD',
    url_externa VARCHAR(500) DEFAULT NULL COMMENT 'URL si es sistema externo',
    requiere_licencia TINYINT(1) DEFAULT 1 COMMENT '1=Requiere suscripción',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_orden (orden),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de módulos/aplicaciones disponibles';

-- Insertar módulos según la imagen
INSERT INTO modulos (codigo, nombre, descripcion, icono, color_fondo, orden, ruta_modulo, ruta_controller, es_externo, requiere_licencia) VALUES
-- Fila 1
('instalaciones', 'Instalaciones', 'Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.', 'fas fa-building', '#3B82F6', 1, 'instalaciones', 'cancha', 0, 1),
('reservas', 'Reservas', 'Sistema de reservas por bloques horarios con confirmación automática y recurrencias.', 'fas fa-calendar-check', '#10B981', 2, 'reservas', 'reserva', 0, 1),
('facturacion', 'Facturación', 'Comprobantes electrónicos SRI, múltiples formas de pago y pasarelas online.', 'fas fa-file-invoice-dollar', '#F59E0B', 3, 'facturacion', 'comprobante', 0, 1),
('reportes', 'Reportes', 'KPIs, ocupación, ingresos por período y análisis detallado de tu negocio.', 'fas fa-chart-bar', '#8B5CF6', 4, 'reportes', 'kpi', 0, 1),
-- Fila 2
('escuelas', 'Escuelas', 'Administración completa de escuelas de fútbol, básquet y natación.', 'fas fa-graduation-cap', '#14B8A6', 5, 'escuelas', 'escuela', 0, 1),
('clientes', 'Clientes', 'Registro de socios, público general y empresas con diferentes tarifas.', 'fas fa-users', '#06B6D4', 6, 'clientes', 'cliente', 0, 1),
('abonos', 'Abonos', 'Sistema de prepagos y saldos a favor para tus clientes frecuentes.', 'fas fa-wallet', '#F472B6', 7, 'reservas', 'abon', 0, 1),
('seguridad', 'Seguridad', '2FA, encriptación AES-256, auditoría completa y protección avanzada.', 'fas fa-shield-alt', '#EF4444', 8, 'core', 'seguridad', 0, 0),
-- Sistemas deportivos específicos (para desarrollo futuro)
('digisports_futbol', 'DigiSports Fútbol', 'Sistema integral para escuelas de fútbol formativo.', 'fas fa-futbol', '#22C55E', 10, 'futbol', 'dashboard', 0, 1),
('digisports_basket', 'DigiSports Basket', 'Gestión de academias y escuelas de baloncesto.', 'fas fa-basketball-ball', '#F97316', 11, 'basket', 'dashboard', 0, 1),
('digisports_natacion', 'DigiSports Natación', 'Control de escuelas de natación y actividades acuáticas.', 'fas fa-swimmer', '#0EA5E9', 12, 'natacion', 'dashboard', 0, 1),
('digisports_artes_marciales', 'DigiSports Artes Marciales', 'Administración de dojos y academias de artes marciales.', 'fas fa-hand-rock', '#DC2626', 13, 'artes_marciales', 'dashboard', 0, 1),
('digisports_ajedrez', 'DigiSports Ajedrez', 'Gestión de clubes y escuelas de ajedrez.', 'fas fa-chess', '#1F2937', 14, 'ajedrez', 'dashboard', 0, 1),
('digisports_multideporte', 'DigiSports Multideporte', 'Para academias con múltiples disciplinas deportivas.', 'fas fa-running', '#7C3AED', 15, 'multideporte', 'dashboard', 0, 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion);

-- Tabla de Suscripciones: qué módulos tiene contratado cada tenant
CREATE TABLE IF NOT EXISTS tenant_modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    modulo_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE DEFAULT NULL COMMENT 'NULL = sin vencimiento',
    estado ENUM('ACTIVO', 'SUSPENDIDO', 'VENCIDO', 'CANCELADO') DEFAULT 'ACTIVO',
    tipo_licencia ENUM('PRUEBA', 'MENSUAL', 'ANUAL', 'PERPETUA') DEFAULT 'MENSUAL',
    max_usuarios INT DEFAULT NULL COMMENT 'NULL = ilimitado',
    observaciones TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_tenant_modulo (tenant_id, modulo_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_modulo (modulo_id),
    INDEX idx_estado (estado),
    FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Suscripciones de tenants a módulos';

-- Insertar suscripciones para tenant 1 (los módulos ya desarrollados)
INSERT INTO tenant_modulos (tenant_id, modulo_id, fecha_inicio, estado, tipo_licencia) 
SELECT 1, id, CURDATE(), 'ACTIVO', 'PERPETUA' 
FROM modulos 
WHERE codigo IN ('instalaciones', 'reservas', 'facturacion', 'reportes', 'clientes', 'abonos', 'seguridad')
ON DUPLICATE KEY UPDATE estado = 'ACTIVO';

-- Tabla de Permisos de Rol a Módulos
CREATE TABLE IF NOT EXISTS rol_modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol_id INT NOT NULL,
    modulo_id INT NOT NULL,
    puede_ver TINYINT(1) DEFAULT 1,
    puede_crear TINYINT(1) DEFAULT 0,
    puede_editar TINYINT(1) DEFAULT 0,
    puede_eliminar TINYINT(1) DEFAULT 0,
    permisos_especiales JSON DEFAULT NULL COMMENT 'Permisos específicos del módulo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_rol_modulo (rol_id, modulo_id),
    INDEX idx_rol (rol_id),
    INDEX idx_modulo (modulo_id),
    FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Permisos de roles sobre módulos';

-- Permisos para rol Admin (rol_id = 1) - acceso total a todo
INSERT INTO rol_modulos (rol_id, modulo_id, puede_ver, puede_crear, puede_editar, puede_eliminar)
SELECT 1, id, 1, 1, 1, 1 FROM modulos
ON DUPLICATE KEY UPDATE puede_ver = 1, puede_crear = 1, puede_editar = 1, puede_eliminar = 1;

-- Permisos para rol Recepcionista (rol_id = 2) - acceso limitado
INSERT INTO rol_modulos (rol_id, modulo_id, puede_ver, puede_crear, puede_editar, puede_eliminar)
SELECT 2, id, 1, 1, 0, 0 FROM modulos WHERE codigo IN ('reservas', 'clientes', 'abonos')
ON DUPLICATE KEY UPDATE puede_ver = 1, puede_crear = 1;

-- Permisos para rol Instructor (rol_id = 3) - solo ver escuelas y clientes
INSERT INTO rol_modulos (rol_id, modulo_id, puede_ver, puede_crear, puede_editar, puede_eliminar)
SELECT 3, id, 1, 0, 0, 0 FROM modulos WHERE codigo IN ('escuelas', 'clientes')
ON DUPLICATE KEY UPDATE puede_ver = 1;

-- Vista para obtener módulos accesibles por usuario
CREATE OR REPLACE VIEW v_modulos_usuario AS
SELECT 
    m.id AS modulo_id,
    m.codigo,
    m.nombre,
    m.descripcion,
    m.icono,
    m.color_fondo,
    m.orden,
    m.ruta_modulo,
    m.ruta_controller,
    m.ruta_action,
    m.es_externo,
    m.url_externa,
    tm.tenant_id,
    tm.estado AS estado_suscripcion,
    tm.fecha_fin,
    rm.rol_id,
    rm.puede_ver,
    rm.puede_crear,
    rm.puede_editar,
    rm.puede_eliminar
FROM modulos m
INNER JOIN tenant_modulos tm ON m.id = tm.modulo_id AND tm.estado = 'ACTIVO'
INNER JOIN rol_modulos rm ON m.id = rm.modulo_id AND rm.puede_ver = 1
WHERE m.activo = 1
ORDER BY m.orden;

-- =====================================================
-- FIN DE MIGRACIÓN HUB
-- =====================================================
