-- ===================================================================
-- DigiSports CORE - Base de Datos Principal
-- Sistema Multi-tenant de Gestión Deportiva Integral
-- Versión: 1.0.0
-- MySQL 8.0+
-- ===================================================================

CREATE DATABASE IF NOT EXISTS digisports_core 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE digisports_core;

-- -------------------------------------------------------------------
-- MÓDULOS DEL SISTEMA
-- -------------------------------------------------------------------

CREATE TABLE modulos_sistema (
    modulo_id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(50) DEFAULT 'fa-puzzle-piece',
    color VARCHAR(7) DEFAULT '#007bff',
    url_base VARCHAR(200) COMMENT 'URL si es sistema externo',
    es_externo CHAR(1) DEFAULT 'N' COMMENT 'S si apunta a otro sistema',
    base_datos_externa VARCHAR(100) COMMENT 'Nombre de BD si es sistema legacy',
    orden_visualizacion INT DEFAULT 0,
    requiere_suscripcion CHAR(1) DEFAULT 'S',
    estado CHAR(1) DEFAULT 'A',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO modulos_sistema (codigo, nombre, descripcion, icono, color, url_base, es_externo, base_datos_externa) VALUES
('CORE', 'Dashboard Principal', 'Panel de control y estadísticas generales', 'fa-home', '#6c757d', NULL, 'N', NULL),
('ESCUELAS', 'Escuelas Deportivas', 'Gestión de escuelas de fútbol, básquet, etc.', 'fa-graduation-cap', '#28a745', '/escuelas/', 'S', 'digisports'),
('INSTALACIONES', 'Gestión de Instalaciones', 'Reservas de canchas, piscinas, etc.', 'fa-building', '#007bff', '/instalaciones/', 'N', NULL),
('TORNEOS', 'Gestión de Torneos', 'Organización de campeonatos y competencias', 'fa-trophy', '#ffc107', '/torneos/', 'N', NULL),
('INVENTARIO', 'Control de Inventario', 'Gestión de equipamiento deportivo', 'fa-boxes', '#17a2b8', '/inventario/', 'N', NULL),
('NUTRICION', 'Planes Nutricionales', 'Seguimiento nutricional de deportistas', 'fa-apple-alt', '#fd7e14', '/nutricion/', 'N', NULL),
('REPORTES', 'Reportes y Estadísticas', 'Análisis y reportería avanzada', 'fa-chart-line', '#6610f2', '/reportes/', 'N', NULL);

-- -------------------------------------------------------------------
-- PLANES DE SUSCRIPCIÓN
-- -------------------------------------------------------------------

CREATE TABLE planes_suscripcion (
    plan_id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_mensual DECIMAL(10,2) NOT NULL,
    precio_anual DECIMAL(10,2),
    descuento_anual DECIMAL(5,2) DEFAULT 0,
    usuarios_incluidos INT DEFAULT 5,
    sedes_incluidas INT DEFAULT 1,
    almacenamiento_gb INT DEFAULT 10,
    modulos_incluidos JSON,
    caracteristicas JSON,
    es_destacado CHAR(1) DEFAULT 'N',
    es_personalizado CHAR(1) DEFAULT 'N',
    color VARCHAR(7) DEFAULT '#007bff',
    orden_visualizacion INT DEFAULT 0,
    estado CHAR(1) DEFAULT 'A',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO planes_suscripcion (codigo, nombre, descripcion, precio_mensual, precio_anual, usuarios_incluidos, sedes_incluidas, almacenamiento_gb, modulos_incluidos, es_destacado) VALUES
('BASICO', 'Plan Basico', 'Ideal para pequenos centros deportivos', 49.99, 539.89, 3, 1, 5, '["CORE","INSTALACIONES"]', 'N'),
('PROFESIONAL', 'Plan Profesional', 'Perfecto para centros en crecimiento', 99.99, 1079.89, 10, 3, 25, '["CORE","INSTALACIONES","ESCUELAS","TORNEOS"]', 'S'),
('EMPRESARIAL', 'Plan Empresarial', 'Para cadenas y complejos deportivos', 199.99, 2159.89, 50, 10, 100, '["CORE","INSTALACIONES","ESCUELAS","TORNEOS","INVENTARIO","NUTRICION","REPORTES"]', 'N'),
('PERSONALIZADO', 'Plan Personalizado', 'Solucion a medida segun tus necesidades', 0.00, 0.00, 100, 20, 500, '[]', 'N');

-- -------------------------------------------------------------------
-- TENANTS (CLIENTES/EMPRESAS)
-- -------------------------------------------------------------------

CREATE TABLE tenants (
    tenant_id INT PRIMARY KEY AUTO_INCREMENT,
    ruc VARCHAR(13) NOT NULL UNIQUE,
    razon_social VARCHAR(300) NOT NULL,
    nombre_comercial VARCHAR(300),
    tipo_empresa VARCHAR(50),
    direccion VARCHAR(400),
    telefono VARCHAR(20),
    celular VARCHAR(15),
    email VARCHAR(100) NOT NULL,
    sitio_web VARCHAR(200),
    representante_nombre VARCHAR(200),
    representante_identificacion VARCHAR(13),
    representante_email VARCHAR(100),
    representante_telefono VARCHAR(15),
    plan_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    estado_suscripcion VARCHAR(20) DEFAULT 'ACTIVA',
    usuarios_permitidos INT DEFAULT 5,
    sedes_permitidas INT DEFAULT 1,
    almacenamiento_gb INT DEFAULT 10,
    logo VARCHAR(200),
    favicon VARCHAR(200),
    color_primario VARCHAR(7) DEFAULT '#007bff',
    color_secundario VARCHAR(7) DEFAULT '#6c757d',
    color_acento VARCHAR(7) DEFAULT '#28a745',
    tiene_sistema_antiguo CHAR(1) DEFAULT 'N',
    bd_antigua VARCHAR(100),
    tenant_id_antiguo INT,
    monto_mensual DECIMAL(10,2) NOT NULL,
    dia_corte INT DEFAULT 1,
    metodo_pago_preferido VARCHAR(50),
    timezone VARCHAR(50) DEFAULT 'America/Guayaquil',
    idioma VARCHAR(5) DEFAULT 'es',
    moneda VARCHAR(3) DEFAULT 'USD',
    estado CHAR(1) DEFAULT 'A',
    motivo_suspension TEXT,
    fecha_suspension DATETIME,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    usuario_registro INT,
    usuario_actualizacion INT,
    FOREIGN KEY (plan_id) REFERENCES planes_suscripcion(plan_id),
    INDEX idx_ruc (ruc),
    INDEX idx_estado (estado),
    INDEX idx_email (email),
    INDEX idx_fecha_vencimiento (fecha_vencimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- ROLES Y PERMISOS
-- -------------------------------------------------------------------

CREATE TABLE roles (
    rol_id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT,
    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    permisos JSON,
    es_super_admin CHAR(1) DEFAULT 'N',
    es_admin_tenant CHAR(1) DEFAULT 'N',
    puede_modificar_permisos CHAR(1) DEFAULT 'N',
    nivel_acceso INT DEFAULT 1,
    estado CHAR(1) DEFAULT 'A',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    UNIQUE KEY uk_tenant_codigo (tenant_id, codigo),
    INDEX idx_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (tenant_id, codigo, nombre, descripcion, es_super_admin, nivel_acceso, permisos) VALUES
(NULL, 'SUPERADMIN', 'Super Administrador', 'Acceso total al sistema', 'S', 10, '["*"]'),
(NULL, 'ADMIN', 'Administrador', 'Administrador de tenant', 'N', 5, '["usuarios.*","sedes.*","configuracion.*"]'),
(NULL, 'RECEPCION', 'Recepcionista', 'Gestion de reservas y clientes', 'N', 3, '["reservas.*","clientes.ver","clientes.crear","pagos.crear"]'),
(NULL, 'CLIENTE', 'Cliente', 'Usuario final con acceso limitado', 'N', 1, '["reservas.ver","reservas.crear","perfil.*"]');

-- -------------------------------------------------------------------
-- USUARIOS DEL SISTEMA
-- -------------------------------------------------------------------

CREATE TABLE usuarios (
    usuario_id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    identificacion VARCHAR(20),
    nombres VARCHAR(150) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(15),
    celular VARCHAR(15),
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    requiere_2fa CHAR(1) DEFAULT 'S',
    codigo_2fa VARCHAR(6),
    codigo_2fa_expira DATETIME,
    intentos_2fa INT DEFAULT 0,
    token_recuperacion VARCHAR(100),
    token_recuperacion_expira DATETIME,
    rol_id INT NOT NULL,
    permisos_especiales JSON,
    ultimo_login DATETIME,
    ip_ultimo_login VARCHAR(45),
    token_sesion VARCHAR(255),
    token_sesion_expira DATETIME,
    sedes_acceso JSON,
    sede_principal_id INT,
    avatar VARCHAR(200),
    tema VARCHAR(20) DEFAULT 'light',
    idioma VARCHAR(5) DEFAULT 'es',
    notificaciones_email CHAR(1) DEFAULT 'S',
    notificaciones_push CHAR(1) DEFAULT 'S',
    debe_cambiar_password CHAR(1) DEFAULT 'N',
    password_expira DATE,
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta DATETIME,
    estado CHAR(1) DEFAULT 'A',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (rol_id) REFERENCES roles(rol_id),
    UNIQUE KEY uk_tenant_email (tenant_id, email),
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_estado (estado),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- MÓDULOS ACTIVOS POR TENANT
-- -------------------------------------------------------------------

CREATE TABLE tenant_modulos (
    tenant_modulo_id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    modulo_id INT NOT NULL,
    activo CHAR(1) DEFAULT 'S',
    fecha_activacion DATE,
    fecha_desactivacion DATE,
    nombre_personalizado VARCHAR(100),
    icono_personalizado VARCHAR(50),
    orden_visualizacion INT DEFAULT 0,
    permisos_especiales JSON,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (modulo_id) REFERENCES modulos_sistema(modulo_id),
    UNIQUE KEY uk_tenant_modulo (tenant_id, modulo_id),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- SEDES (Vinculadas a tenant)
-- -------------------------------------------------------------------

CREATE TABLE sedes (
    sede_id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    direccion VARCHAR(400) NOT NULL,
    ciudad VARCHAR(100),
    provincia VARCHAR(100),
    pais VARCHAR(50) DEFAULT 'Ecuador',
    latitud DECIMAL(10,8),
    longitud DECIMAL(11,8),
    telefono VARCHAR(20),
    email VARCHAR(100),
    horario_apertura TIME,
    horario_cierre TIME,
    dias_atencion VARCHAR(50) DEFAULT 'LUNES-DOMINGO',
    superficie_total DECIMAL(10,2),
    capacidad_total INT,
    estacionamiento CHAR(1) DEFAULT 'S',
    cafeteria CHAR(1) DEFAULT 'N',
    tienda CHAR(1) DEFAULT 'N',
    foto_principal VARCHAR(200),
    galeria JSON,
    es_principal CHAR(1) DEFAULT 'N',
    estado CHAR(1) DEFAULT 'A',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    UNIQUE KEY uk_tenant_codigo (tenant_id, codigo),
    INDEX idx_tenant (tenant_id),
    INDEX idx_ciudad (ciudad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- TIPOS DE INSTALACIONES
-- -------------------------------------------------------------------

CREATE TABLE tipos_instalacion (
    tipo_id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(50) DEFAULT 'fa-futbol',
    color VARCHAR(7) DEFAULT '#28a745',
    requiere_equipamiento CHAR(1) DEFAULT 'N',
    permite_reserva_online CHAR(1) DEFAULT 'S',
    estado CHAR(1) DEFAULT 'A',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    UNIQUE KEY uk_tenant_codigo (tenant_id, codigo),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- INSTALACIONES/CANCHAS
-- -------------------------------------------------------------------

CREATE TABLE instalaciones (
    instalacion_id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    sede_id INT NOT NULL,
    tipo_instalacion_id INT NOT NULL,
    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    superficie VARCHAR(50),
    dimensiones VARCHAR(50),
    capacidad_personas INT,
    tiene_iluminacion CHAR(1) DEFAULT 'S',
    tiene_graderias CHAR(1) DEFAULT 'N',
    tiene_vestuarios CHAR(1) DEFAULT 'N',
    tiene_duchas CHAR(1) DEFAULT 'N',
    duracion_minima_minutos INT DEFAULT 60,
    duracion_maxima_minutos INT DEFAULT 120,
    tiempo_anticipacion_dias INT DEFAULT 30,
    permite_reserva_recurrente CHAR(1) DEFAULT 'S',
    foto_principal VARCHAR(200),
    galeria_fotos JSON,
    estado VARCHAR(20) DEFAULT 'ACTIVO',
    motivo_inactivacion TEXT,
    fecha_inicio_inactivacion DATETIME,
    fecha_fin_inactivacion DATETIME,
    orden_visualizacion INT DEFAULT 0,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    usuario_registro INT,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (sede_id) REFERENCES sedes(sede_id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_instalacion_id) REFERENCES tipos_instalacion(tipo_id),
    UNIQUE KEY uk_tenant_codigo (tenant_id, codigo),
    INDEX idx_tenant_sede (tenant_id, sede_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- HORARIOS DE INSTALACIONES
-- -------------------------------------------------------------------

CREATE TABLE instalacion_horarios (
    horario_id INT PRIMARY KEY AUTO_INCREMENT,
    instalacion_id INT NOT NULL,
    dia_semana TINYINT NOT NULL,
    hora_apertura TIME NOT NULL,
    hora_cierre TIME NOT NULL,
    estado CHAR(1) DEFAULT 'A',
    FOREIGN KEY (instalacion_id) REFERENCES instalaciones(instalacion_id) ON DELETE CASCADE,
    UNIQUE KEY uk_instalacion_dia (instalacion_id, dia_semana)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- BLOQUEOS/MANTENIMIENTOS
-- -------------------------------------------------------------------

CREATE TABLE instalacion_bloqueos (
    bloqueo_id INT PRIMARY KEY AUTO_INCREMENT,
    instalacion_id INT NOT NULL,
    tipo_bloqueo VARCHAR(50) NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    motivo TEXT NOT NULL,
    es_recurrente CHAR(1) DEFAULT 'N',
    recurrencia_config JSON,
    usuario_registro INT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instalacion_id) REFERENCES instalaciones(instalacion_id) ON DELETE CASCADE,
    INDEX idx_fechas (fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- TARIFAS
-- -------------------------------------------------------------------

CREATE TABLE instalacion_tarifas (
    tarifa_id INT PRIMARY KEY AUTO_INCREMENT,
    instalacion_id INT NOT NULL,
    nombre_tarifa VARCHAR(100) NOT NULL,
    tipo_cliente VARCHAR(50) NOT NULL,
    aplica_dia VARCHAR(50),
    hora_inicio TIME,
    hora_fin TIME,
    precio_por_hora DECIMAL(10,2) NOT NULL,
    precio_minimo DECIMAL(10,2),
    descuento_porcentaje DECIMAL(5,2) DEFAULT 0,
    fecha_inicio_vigencia DATE NOT NULL,
    fecha_fin_vigencia DATE,
    prioridad INT DEFAULT 0,
    estado CHAR(1) DEFAULT 'A',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instalacion_id) REFERENCES instalaciones(instalacion_id) ON DELETE CASCADE,
    INDEX idx_vigencia (fecha_inicio_vigencia, fecha_fin_vigencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- CLIENTES
-- -------------------------------------------------------------------

CREATE TABLE clientes (
    cliente_id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    tipo_identificacion VARCHAR(3) NOT NULL,
    identificacion VARCHAR(20) NOT NULL,
    nombres VARCHAR(150) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(15),
    celular VARCHAR(15),
    direccion VARCHAR(400),
    fecha_nacimiento DATE,
    tipo_cliente VARCHAR(50) DEFAULT 'PUBLICO',
    saldo_abono DECIMAL(10,2) DEFAULT 0.00,
    estado CHAR(1) DEFAULT 'A',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    UNIQUE KEY uk_tenant_identificacion (tenant_id, identificacion),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- RESERVAS
-- -------------------------------------------------------------------

CREATE TABLE reservas (
    reserva_id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    instalacion_id INT NOT NULL,
    cliente_id INT NOT NULL,
    fecha_reserva DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    duracion_minutos INT NOT NULL,
    es_recurrente CHAR(1) DEFAULT 'N',
    reserva_padre_id INT,
    recurrencia_config JSON,
    tarifa_aplicada_id INT,
    precio_base DECIMAL(10,2) NOT NULL,
    descuento_monto DECIMAL(10,2) DEFAULT 0,
    precio_total DECIMAL(10,2) NOT NULL,
    abono_utilizado DECIMAL(10,2) DEFAULT 0,
    estado VARCHAR(20) DEFAULT 'PENDIENTE',
    requiere_confirmacion CHAR(1) DEFAULT 'S',
    fecha_confirmacion DATETIME,
    observaciones TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_registro INT,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (instalacion_id) REFERENCES instalaciones(instalacion_id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(cliente_id),
    FOREIGN KEY (reserva_padre_id) REFERENCES reservas(reserva_id) ON DELETE CASCADE,
    INDEX idx_tenant_instalacion (tenant_id, instalacion_id),
    INDEX idx_fecha_reserva (fecha_reserva),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- ABONOS
-- -------------------------------------------------------------------

CREATE TABLE abonos (
    abono_id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    cliente_id INT NOT NULL,
    monto_total DECIMAL(10,2) NOT NULL,
    monto_utilizado DECIMAL(10,2) DEFAULT 0,
    saldo_disponible DECIMAL(10,2) NOT NULL,
    fecha_compra DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    forma_pago VARCHAR(50),
    estado VARCHAR(20) DEFAULT 'ACTIVO',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(cliente_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_vencimiento (fecha_vencimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- PAGOS
-- -------------------------------------------------------------------

CREATE TABLE reserva_pagos (
    pago_id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    reserva_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    tipo_pago VARCHAR(50) NOT NULL,
    forma_pago VARCHAR(50),
    referencia VARCHAR(100),
    pasarela VARCHAR(50),
    transaction_id VARCHAR(100),
    estado VARCHAR(20) DEFAULT 'COMPLETADO',
    fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
    usuario_registro INT,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (reserva_id) REFERENCES reservas(reserva_id),
    INDEX idx_reserva (reserva_id),
    INDEX idx_fecha (fecha_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- FACTURACIÓN DE SUSCRIPCIONES
-- -------------------------------------------------------------------

CREATE TABLE facturas_suscripcion (
    factura_id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    periodo VARCHAR(7) NOT NULL,
    tipo_factura VARCHAR(20) DEFAULT 'MENSUAL',
    subtotal DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0,
    iva DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    plan_nombre VARCHAR(100),
    usuarios_cobrados INT,
    sedes_cobradas INT,
    modulos_adicionales JSON,
    fecha_emision DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    fecha_pago DATE,
    metodo_pago VARCHAR(50),
    referencia_pago VARCHAR(100),
    comprobante_pago VARCHAR(200),
    numero_autorizacion VARCHAR(49),
    clave_acceso VARCHAR(49),
    xml_firmado TEXT,
    estado VARCHAR(20) DEFAULT 'PENDIENTE',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    INDEX idx_tenant_periodo (tenant_id, periodo),
    INDEX idx_estado (estado),
    INDEX idx_vencimiento (fecha_vencimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- NOTIFICACIONES DEL SISTEMA
-- -------------------------------------------------------------------

CREATE TABLE notificaciones (
    notificacion_id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT,
    usuario_id INT,
    tipo VARCHAR(50) NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    url_accion VARCHAR(300),
    icono VARCHAR(50),
    color VARCHAR(7),
    leida CHAR(1) DEFAULT 'N',
    fecha_lectura DATETIME,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion DATETIME,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_leida (leida),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- AUDITORÍA GLOBAL
-- -------------------------------------------------------------------

CREATE TABLE auditoria (
    auditoria_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT,
    usuario_id INT,
    modulo VARCHAR(50),
    tabla VARCHAR(100),
    registro_id INT,
    operacion VARCHAR(20),
    valores_anteriores JSON,
    valores_nuevos JSON,
    ip VARCHAR(45),
    user_agent TEXT,
    url VARCHAR(500),
    metodo VARCHAR(10),
    fecha_operacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_tabla (tabla),
    INDEX idx_fecha (fecha_operacion),
    INDEX idx_operacion (operacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- CONFIGURACIÓN GLOBAL DEL SISTEMA
-- -------------------------------------------------------------------

CREATE TABLE configuracion_sistema (
    config_id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT,
    clave VARCHAR(100) NOT NULL,
    valor TEXT,
    tipo VARCHAR(50) DEFAULT 'STRING',
    descripcion TEXT,
    es_editable CHAR(1) DEFAULT 'S',
    requiere_reinicio CHAR(1) DEFAULT 'N',
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    UNIQUE KEY uk_tenant_clave (tenant_id, clave),
    INDEX idx_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO configuracion_sistema (tenant_id, clave, valor, tipo, descripcion) VALUES
(NULL, 'NOMBRE_SISTEMA', 'DigiSports', 'STRING', 'Nombre del sistema'),
(NULL, 'VERSION', '1.0.0', 'STRING', 'Version actual del sistema'),
(NULL, 'EMAIL_SOPORTE', 'soporte@digisports.com', 'STRING', 'Email de soporte'),
(NULL, 'MANTENIMIENTO', 'N', 'BOOLEAN', 'Modo mantenimiento'),
(NULL, 'PERMITIR_REGISTRO', 'S', 'BOOLEAN', 'Permitir auto-registro de tenants'),
(NULL, 'DIAS_PRUEBA', '30', 'INT', 'Dias de prueba gratis'),
(NULL, 'SESSION_TIMEOUT', '1800', 'INT', 'Timeout de sesion en segundos');

-- ===================================================================
-- DATOS INICIALES - EN ORDEN CORRECTO
-- ===================================================================

-- 1. Tenant principal
INSERT INTO tenants (tenant_id, ruc, razon_social, nombre_comercial, email, plan_id, fecha_inicio, fecha_vencimiento, monto_mensual, estado) VALUES
(1, '1792261104001', 'DigiSports Administracion', 'DigiSports Admin', 'admin@digisports.com', 4, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 0.00, 'A');

-- 2. Roles del tenant
INSERT INTO roles (tenant_id, codigo, nombre, descripcion, es_admin_tenant, nivel_acceso, permisos) VALUES
(1, 'ADMIN', 'Administrador Tenant', 'Administrador del tenant', 'S', 5, '["usuarios.*","sedes.*","configuracion.*"]'),
(1, 'RECEPCION', 'Recepcionista', 'Gestion de reservas y clientes', 'N', 3, '["reservas.*","clientes.ver","clientes.crear","pagos.crear"]'),
(1, 'CLIENTE', 'Cliente', 'Usuario final con acceso limitado', 'N', 1, '["reservas.ver","reservas.crear","perfil.*"]');

-- 3. Usuario Super Admin
INSERT INTO usuarios (tenant_id, nombres, apellidos, email, username, password, requiere_2fa, rol_id, estado) VALUES
(1, 'Super', 'Administrador', 'admin@digisports.com', 'superadmin', '$argon2id$v=19$m=65536,t=4,p=3$dGVzdHNhbHQxMjM0NTY$X8rlZGXB8M7Cs9GvE7Wj7ZN0H0N8V+vI+0n8F8n8F8n', 'S', 1, 'A');

-- 4. Modulos activos
INSERT INTO tenant_modulos (tenant_id, modulo_id, activo, fecha_activacion)
SELECT 1, modulo_id, 'S', CURDATE() FROM modulos_sistema;

-- 5. Sede principal
INSERT INTO sedes (tenant_id, codigo, nombre, direccion, ciudad, provincia, es_principal, estado) VALUES
(1, 'CENTRAL', 'Sede Central', 'Av. Principal 123', 'Quito', 'Pichincha', 'S', 'A');

-- ===================================================================
-- FIN DE CREACIÓN DE BASE DE DATOS
-- ===================================================================
