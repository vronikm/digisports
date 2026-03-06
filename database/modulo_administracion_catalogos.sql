-- =============================================================================
-- MÓDULO: Administración de Catálogos del Sistema
-- Descripción: Gestión completa de tablas y catálogos de seguridad
-- Fecha: 2024
-- Versión: 1.0.0
-- =============================================================================

-- =============================================================================
-- 1. VERIFICACIÓN DE TABLAS EXISTENTES
-- =============================================================================
-- Estas tablas ya deberían existir en la base de datos
-- Si no existen, descomentar las líneas de CREATE TABLE

/*
DROP TABLE IF EXISTS seguridad_tabla_catalogo;
DROP TABLE IF EXISTS seguridad_tabla;

CREATE TABLE seguridad_tabla (
    st_id INT AUTO_INCREMENT PRIMARY KEY,
    st_nombre VARCHAR(255) NOT NULL UNIQUE,
    st_descripcion TEXT,
    st_activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_st_nombre (st_nombre),
    INDEX idx_st_activo (st_activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE seguridad_tabla_catalogo (
    stc_id INT AUTO_INCREMENT PRIMARY KEY,
    stc_tabla_id INT NOT NULL,
    stc_codigo VARCHAR(100) NOT NULL,
    stc_valor VARCHAR(255) NOT NULL,
    stc_etiqueta VARCHAR(255) NOT NULL,
    stc_orden INT DEFAULT 0,
    stc_activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_stc_tabla_id FOREIGN KEY (stc_tabla_id)
        REFERENCES seguridad_tabla(st_id) ON DELETE CASCADE,
    
    UNIQUE KEY uk_stc_codigo_tabla (stc_tabla_id, stc_codigo),
    INDEX idx_stc_tabla_id (stc_tabla_id),
    INDEX idx_stc_codigo (stc_codigo),
    INDEX idx_stc_orden (stc_orden),
    INDEX idx_stc_activo (stc_activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/

-- =============================================================================
-- 2. DATOS DE EJEMPLO (CATÁLOGOS INICIALES)
-- =============================================================================

-- Insertar catálogos de ejemplo si no existen
INSERT IGNORE INTO seguridad_tabla (st_id, st_nombre, st_descripcion, st_activo)
VALUES
(1, 'Tipos de Documento', 'Tipos de identificación válidos (Cédula, Pasaporte, etc)', 1),
(2, 'Estados de Pago', 'Estados posibles de una transacción de pago', 1),
(3, 'Estados de Reserva', 'Estados en el ciclo de vida de una reserva', 1),
(4, 'Tipos de Instalación', 'Categorías de espacios deportivos', 1),
(5, 'Estados de Mantenimiento', 'Estados de tareas de mantenimiento', 1);

-- Tipos de Documento
INSERT IGNORE INTO seguridad_tabla_catalogo (stc_tabla_id, stc_codigo, stc_valor, stc_etiqueta, stc_orden, stc_activo)
VALUES
(1, 'CEDULA', '1', 'Cédula de Ciudadanía', 10, 1),
(1, 'PASAPORTE', '2', 'Pasaporte', 20, 1),
(1, 'RUC', '3', 'RUC', 30, 1),
(1, 'OTROS', '4', 'Otros', 40, 1);

-- Estados de Pago
INSERT IGNORE INTO seguridad_tabla_catalogo (stc_tabla_id, stc_codigo, stc_valor, stc_etiqueta, stc_orden, stc_activo)
VALUES
(2, 'PENDIENTE', 'PENDIENTE', 'Pendiente de Pago', 10, 1),
(2, 'PAGADO', 'PAGADO', 'Pagado', 20, 1),
(2, 'CANCELADO', 'CANCELADO', 'Cancelado', 30, 1),
(2, 'RECHAZADO', 'RECHAZADO', 'Rechazado', 40, 1);

-- Estados de Reserva
INSERT IGNORE INTO seguridad_tabla_catalogo (stc_tabla_id, stc_codigo, stc_valor, stc_etiqueta, stc_orden, stc_activo)
VALUES
(3, 'SOLICITADA', 'SOLICITADA', 'Solicitada', 10, 1),
(3, 'CONFIRMADA', 'CONFIRMADA', 'Confirmada', 20, 1),
(3, 'EN_PROGRESO', 'EN_PROGRESO', 'En Progreso', 30, 1),
(3, 'COMPLETADA', 'COMPLETADA', 'Completada', 40, 1),
(3, 'CANCELADA', 'CANCELADA', 'Cancelada', 50, 1);

-- Tipos de Instalación
INSERT IGNORE INTO seguridad_tabla_catalogo (stc_tabla_id, stc_codigo, stc_valor, stc_etiqueta, stc_orden, stc_activo)
VALUES
(4, 'CANCHA_FUTBOL', 'CANCHA_FUTBOL', 'Cancha de Fútbol', 10, 1),
(4, 'CANCHA_BASKET', 'CANCHA_BASKET', 'Cancha de Básquetbol', 20, 1),
(4, 'PISCINA', 'PISCINA', 'Piscina', 30, 1),
(4, 'GIMNASIO', 'GIMNASIO', 'Gimnasio', 40, 1),
(4, 'SALON_ARTES', 'SALON_ARTES', 'Salón de Artes Marciales', 50, 1);

-- Estados de Mantenimiento
INSERT IGNORE INTO seguridad_tabla_catalogo (stc_tabla_id, stc_codigo, stc_valor, stc_etiqueta, stc_orden, stc_activo)
VALUES
(5, 'PROGRAMADO', 'PROGRAMADO', 'Programado', 10, 1),
(5, 'EN_EJECUCION', 'EN_EJECUCION', 'En Ejecución', 20, 1),
(5, 'COMPLETADO', 'COMPLETADO', 'Completado', 30, 1),
(5, 'CANCELADO', 'CANCELADO', 'Cancelado', 40, 1),
(5, 'REPROGRAMADO', 'REPROGRAMADO', 'Reprogramado', 50, 1);

-- =============================================================================
-- 3. ENTRADAS DE MENÚ (OPCIONAL)
-- =============================================================================

-- Si existe la tabla seguridad_menu_config, agregar entrada:
-- INSERT IGNORE INTO seguridad_menu_config 
-- (modulo_codigo, opcion, icono, color, permiso_requerido, orden)
-- VALUES ('seguridad', 'Administración de Catálogos', 'fas fa-list-check', '#ef4444', 'seguridad.catalogos', 10);

-- =============================================================================
-- 4. VERIFICACIÓN FINAL
-- =============================================================================

SELECT 'Catálogos creados correctamente' as estado;
SELECT st_id, st_nombre, st_descripcion, 
       (SELECT COUNT(*) FROM seguridad_tabla_catalogo WHERE stc_tabla_id = st_id) as total_items
FROM seguridad_tabla
ORDER BY st_id;
