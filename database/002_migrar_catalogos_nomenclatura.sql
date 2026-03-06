-- ============================================================
-- DigiSports - Migración 002: Catálogos - Actualización de Nomenclatura
--
-- Actualiza seguridad_tabla y seguridad_tabla_catalogo de la
-- nomenclatura legacy (tabla_*, catalogo_*) a la convención
-- estándar del proyecto (prefijo 2-3 letras: st_*, stc_*).
--
-- Modelo anterior:
--   seguridad_tabla:         tabla_id, tabla_nombre, tabla_estado
--   seguridad_tabla_catalogo: catalogo_valor(PK varchar), catalogo_tablaid,
--                             catalogo_descripcion, catalogo_estado
--
-- Modelo nuevo (coincide con SeguridadTablaModel.php y SeguridadTablaCatalogoModel.php):
--   seguridad_tabla:          st_id, st_nombre, st_descripcion,
--                             st_activo, st_created_at, st_updated_at
--   seguridad_tabla_catalogo: stc_id(PK int), stc_tabla_id, stc_codigo,
--                             stc_valor, stc_etiqueta, stc_orden,
--                             stc_activo, stc_created_at, stc_updated_at
--
-- Requiere: MySQL 8.0+ (RENAME COLUMN)
-- Base de datos: digisports_core
--
-- Ejecutar en phpMyAdmin o con:
--   mysql -u root digisports_core < database/002_migrar_catalogos_nomenclatura.sql
-- ============================================================

START TRANSACTION;

-- ============================================================
-- PARTE 1: seguridad_tabla (grupos de catálogos)
-- ============================================================

-- 1.1 Cambiar motor a InnoDB, charset moderno, renombrar columnas existentes
ALTER TABLE `seguridad_tabla`
    RENAME COLUMN `tabla_id`     TO `st_id`,
    RENAME COLUMN `tabla_nombre` TO `st_nombre`,
    RENAME COLUMN `tabla_estado` TO `st_estado_old`,
    ENGINE = InnoDB,
    DEFAULT CHARSET = utf8mb4,
    COLLATE = utf8mb4_unicode_ci;

-- 1.2 Agregar nuevas columnas
ALTER TABLE `seguridad_tabla`
    ADD COLUMN `st_descripcion` VARCHAR(500)  NULL                              AFTER `st_nombre`,
    ADD COLUMN `st_activo`      TINYINT(1)    NOT NULL DEFAULT 1                AFTER `st_descripcion`,
    ADD COLUMN `st_created_at`  DATETIME      NULL DEFAULT CURRENT_TIMESTAMP    AFTER `st_activo`,
    ADD COLUMN `st_updated_at`  DATETIME      NULL DEFAULT CURRENT_TIMESTAMP
                                              ON UPDATE CURRENT_TIMESTAMP       AFTER `st_created_at`;

-- 1.3 Migrar estado: 'A' → 1 (activo), cualquier otro → 0 (inactivo)
UPDATE `seguridad_tabla`
SET `st_activo` = CASE WHEN `st_estado_old` = 'A' THEN 1 ELSE 0 END;

-- 1.4 Eliminar columna temporal
ALTER TABLE `seguridad_tabla` DROP COLUMN `st_estado_old`;

-- ============================================================
-- PARTE 2: seguridad_tabla_catalogo (ítems de catálogos)
-- ============================================================

-- 2.1 Agregar nueva PK entera, eliminar PK varchar, cambiar motor
--     (En un solo ALTER TABLE para evitar reconstrucciones múltiples)
ALTER TABLE `seguridad_tabla_catalogo`
    ADD COLUMN `stc_id` INT NOT NULL AUTO_INCREMENT FIRST,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`stc_id`),
    DROP INDEX `catalogo_tablaid`,
    ENGINE = InnoDB,
    DEFAULT CHARSET = utf8mb4,
    COLLATE = utf8mb4_unicode_ci;

-- 2.2 Renombrar columnas existentes a la nueva nomenclatura
ALTER TABLE `seguridad_tabla_catalogo`
    RENAME COLUMN `catalogo_valor`       TO `stc_codigo`,
    RENAME COLUMN `catalogo_tablaid`     TO `stc_tabla_id`,
    RENAME COLUMN `catalogo_descripcion` TO `stc_valor`,
    RENAME COLUMN `catalogo_estado`      TO `stc_estado_old`;

-- 2.3 Agregar nuevas columnas
ALTER TABLE `seguridad_tabla_catalogo`
    ADD COLUMN `stc_etiqueta`   VARCHAR(255) NULL                               AFTER `stc_valor`,
    ADD COLUMN `stc_orden`      INT          NOT NULL DEFAULT 10                 AFTER `stc_etiqueta`,
    ADD COLUMN `stc_activo`     TINYINT(1)   NOT NULL DEFAULT 1                  AFTER `stc_orden`,
    ADD COLUMN `stc_created_at` DATETIME     NULL DEFAULT CURRENT_TIMESTAMP      AFTER `stc_activo`,
    ADD COLUMN `stc_updated_at` DATETIME     NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP        AFTER `stc_created_at`;

-- 2.4 Migrar datos
--   stc_etiqueta = copia de stc_valor (la descripción original es el valor legible)
UPDATE `seguridad_tabla_catalogo` SET `stc_etiqueta` = `stc_valor`;

--   stc_activo: 'A' → 1, cualquier otro → 0
UPDATE `seguridad_tabla_catalogo`
SET `stc_activo` = CASE WHEN `stc_estado_old` = 'A' THEN 1 ELSE 0 END;

--   stc_orden: asignar en pasos de 10 según el stc_id generado
UPDATE `seguridad_tabla_catalogo` SET `stc_orden` = `stc_id` * 10;

-- 2.5 Eliminar columna temporal
ALTER TABLE `seguridad_tabla_catalogo` DROP COLUMN `stc_estado_old`;

-- 2.6 Índices: unicidad por (grupo, código) e índice de orden
ALTER TABLE `seguridad_tabla_catalogo`
    ADD UNIQUE INDEX `idx_stc_grupo_codigo` (`stc_tabla_id`, `stc_codigo`),
    ADD INDEX        `idx_stc_orden`        (`stc_orden`);

COMMIT;

-- ============================================================
-- VERIFICACIÓN POST-MIGRACIÓN
-- ============================================================

SELECT '=== seguridad_tabla: estructura ===' AS verificacion;
DESCRIBE `seguridad_tabla`;

SELECT '=== seguridad_tabla: datos ===' AS verificacion;
SELECT `st_id`, `st_nombre`, `st_activo`, `st_created_at`
FROM `seguridad_tabla`
ORDER BY `st_nombre`;

SELECT '=== seguridad_tabla_catalogo: estructura ===' AS verificacion;
DESCRIBE `seguridad_tabla_catalogo`;

SELECT '=== seguridad_tabla_catalogo: muestra (primeros 10) ===' AS verificacion;
SELECT `stc_id`, `stc_tabla_id`, `stc_codigo`, `stc_valor`, `stc_etiqueta`, `stc_orden`, `stc_activo`
FROM `seguridad_tabla_catalogo`
ORDER BY `stc_tabla_id`, `stc_orden`
LIMIT 10;
