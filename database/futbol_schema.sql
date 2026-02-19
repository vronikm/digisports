-- ╔═══════════════════════════════════════════════════════════════════╗
-- ║  DigiSports Fútbol — Schema Completo                           ║
-- ║  Fecha: 2026-02-09                                              ║
-- ║  Escuela de Fútbol: Categorías, Entrenadores, Grupos,           ║
-- ║  Inscripciones, Asistencia, Evaluaciones, Pagos, Becas,         ║
-- ║  Torneos, Mora, Comprobantes, Notificaciones, Egresos           ║
-- ║  Usa tabla compartida: alumnos + instalaciones_sedes +          ║
-- ║  instalaciones_canchas + clientes                               ║
-- ╚═══════════════════════════════════════════════════════════════════╝

SET FOREIGN_KEY_CHECKS = 0;

-- ══════════════════════════════════════════════════════════════
-- 1. futbol_configuracion — Config general del módulo por tenant
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_configuracion` (
    `fcg_config_id`     INT AUTO_INCREMENT PRIMARY KEY,
    `fcg_tenant_id`     INT NOT NULL,
    `fcg_clave`         VARCHAR(80) NOT NULL,
    `fcg_valor`         TEXT DEFAULT NULL,
    `fcg_tipo`          ENUM('TEXT','NUMBER','BOOLEAN','JSON','SELECT') DEFAULT 'TEXT',
    `fcg_descripcion`   VARCHAR(200) DEFAULT NULL,
    `fcg_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fcg_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_fcg_tenant_clave` (`fcg_tenant_id`, `fcg_clave`),
    CONSTRAINT `fk_fcg_tenant` FOREIGN KEY (`fcg_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 2. futbol_campos_ficha — Campos personalizables del alumno
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_campos_ficha` (
    `fcf_campo_id`      INT AUTO_INCREMENT PRIMARY KEY,
    `fcf_tenant_id`     INT NOT NULL,
    `fcf_clave`         VARCHAR(50) NOT NULL,
    `fcf_etiqueta`      VARCHAR(100) NOT NULL,
    `fcf_tipo`          ENUM('TEXT','TEXTAREA','SELECT','NUMBER','DATE','CHECKBOX','RADIO','EMAIL','TEL') DEFAULT 'TEXT',
    `fcf_opciones`      JSON DEFAULT NULL,
    `fcf_placeholder`   VARCHAR(150) DEFAULT NULL,
    `fcf_requerido`     TINYINT(1) DEFAULT 0,
    `fcf_grupo`         VARCHAR(50) DEFAULT 'general',
    `fcf_orden`         INT DEFAULT 0,
    `fcf_activo`        TINYINT(1) DEFAULT 1,
    `fcf_validacion`    JSON DEFAULT NULL,
    `fcf_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fcf_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_fcf_tenant_clave` (`fcf_tenant_id`, `fcf_clave`),
    INDEX `idx_fcf_orden` (`fcf_tenant_id`, `fcf_grupo`, `fcf_orden`),
    CONSTRAINT `fk_fcf_tenant` FOREIGN KEY (`fcf_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 3. futbol_ficha_alumno — Extensión fútbol del alumno
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_ficha_alumno` (
    `ffa_ficha_id`              INT AUTO_INCREMENT PRIMARY KEY,
    `ffa_tenant_id`             INT NOT NULL,
    `ffa_alumno_id`             INT NOT NULL COMMENT 'FK → alumnos',
    
    -- Datos específicos de fútbol
    `ffa_categoria_id`          INT DEFAULT NULL COMMENT 'FK → futbol_categorias',
    `ffa_posicion_preferida`    VARCHAR(50) DEFAULT NULL COMMENT 'Portero, Defensa, Mediocampista, Delantero',
    `ffa_pie_dominante`         ENUM('DERECHO','IZQUIERDO','AMBIDIESTRO') DEFAULT 'DERECHO',
    `ffa_experiencia_previa`    TEXT DEFAULT NULL,
    `ffa_club_anterior`         VARCHAR(100) DEFAULT NULL,
    `ffa_objetivo`              ENUM('RECREATIVO','FORMATIVO','COMPETITIVO') DEFAULT 'FORMATIVO',
    `ffa_talla_camiseta`        VARCHAR(10) DEFAULT NULL COMMENT 'XS, S, M, L, XL',
    `ffa_talla_short`           VARCHAR(10) DEFAULT NULL,
    `ffa_talla_zapato`          VARCHAR(10) DEFAULT NULL,
    `ffa_numero_camiseta`       INT DEFAULT NULL,
    `ffa_autorizacion_medica`   TINYINT(1) DEFAULT 0,
    `ffa_seguro_medico`         VARCHAR(100) DEFAULT NULL,
    `ffa_fecha_ingreso`         DATE DEFAULT NULL,
    `ffa_fecha_ultimo_avance`   DATE DEFAULT NULL,
    
    -- Datos personalizados por tenant
    `ffa_datos_custom`          JSON DEFAULT NULL,
    `ffa_documentos`            JSON DEFAULT NULL,
    
    -- Estado
    `ffa_activo`                TINYINT(1) DEFAULT 1,
    `ffa_notas`                 TEXT DEFAULT NULL,
    `ffa_created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ffa_updated_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_ffa_tenant_alumno` (`ffa_tenant_id`, `ffa_alumno_id`),
    INDEX `idx_ffa_categoria` (`ffa_categoria_id`),
    CONSTRAINT `fk_ffa_tenant` FOREIGN KEY (`ffa_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ffa_alumno` FOREIGN KEY (`ffa_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 4. futbol_periodos — Temporadas/períodos de la escuela
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_periodos` (
    `fpe_periodo_id`    INT AUTO_INCREMENT PRIMARY KEY,
    `fpe_tenant_id`     INT NOT NULL,
    `fpe_nombre`        VARCHAR(100) NOT NULL COMMENT 'Ej: Temporada 2026-A',
    `fpe_fecha_inicio`  DATE NOT NULL,
    `fpe_fecha_fin`     DATE NOT NULL,
    `fpe_estado`        ENUM('PLANIFICADO','ACTIVO','FINALIZADO') DEFAULT 'PLANIFICADO',
    `fpe_notas`         TEXT DEFAULT NULL,
    `fpe_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fpe_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_fpe_tenant_estado` (`fpe_tenant_id`, `fpe_estado`),
    CONSTRAINT `fk_fpe_tenant` FOREIGN KEY (`fpe_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 5. futbol_categorias — Categorías por edad (Sub-6 a Sub-18+)
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_categorias` (
    `fct_categoria_id`  INT AUTO_INCREMENT PRIMARY KEY,
    `fct_tenant_id`     INT NOT NULL,
    `fct_nombre`        VARCHAR(80) NOT NULL COMMENT 'Sub-6, Sub-8, Sub-10, etc.',
    `fct_codigo`        VARCHAR(20) NOT NULL COMMENT 'U6, U8, U10, etc.',
    `fct_descripcion`   TEXT DEFAULT NULL,
    `fct_color`         VARCHAR(7) DEFAULT '#22C55E',
    `fct_orden`         INT DEFAULT 0,
    `fct_edad_min`      INT DEFAULT NULL,
    `fct_edad_max`      INT DEFAULT NULL,
    `fct_activo`        TINYINT(1) DEFAULT 1,
    `fct_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_fct_tenant_codigo` (`fct_tenant_id`, `fct_codigo`),
    INDEX `idx_fct_orden` (`fct_tenant_id`, `fct_orden`),
    CONSTRAINT `fk_fct_tenant` FOREIGN KEY (`fct_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- FK de ficha_alumno → categorias
ALTER TABLE `futbol_ficha_alumno`
    ADD CONSTRAINT `fk_ffa_categoria` FOREIGN KEY (`ffa_categoria_id`) REFERENCES `futbol_categorias`(`fct_categoria_id`) ON DELETE SET NULL;


-- ══════════════════════════════════════════════════════════════
-- 6. futbol_categoria_habilidades — Habilidades evaluables
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_categoria_habilidades` (
    `fch_habilidad_id`  INT AUTO_INCREMENT PRIMARY KEY,
    `fch_tenant_id`     INT NOT NULL,
    `fch_categoria_id`  INT NOT NULL,
    `fch_nombre`        VARCHAR(100) NOT NULL COMMENT 'Conducción, Pase corto, Tiro a puerta, etc.',
    `fch_descripcion`   TEXT DEFAULT NULL,
    `fch_orden`         INT DEFAULT 0,
    `fch_activo`        TINYINT(1) DEFAULT 1,
    `fch_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_fch_categoria` (`fch_categoria_id`, `fch_orden`),
    CONSTRAINT `fk_fch_tenant` FOREIGN KEY (`fch_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fch_categoria` FOREIGN KEY (`fch_categoria_id`) REFERENCES `futbol_categorias`(`fct_categoria_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 7. futbol_entrenadores — Staff técnico
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_entrenadores` (
    `fen_entrenador_id` INT AUTO_INCREMENT PRIMARY KEY,
    `fen_tenant_id`     INT NOT NULL,
    `fen_sede_id`       INT DEFAULT NULL COMMENT 'FK → instalaciones_sedes',
    `fen_usuario_id`    INT DEFAULT NULL COMMENT 'FK → seguridad_usuarios (si tiene login)',
    `fen_nombres`       VARCHAR(150) NOT NULL,
    `fen_apellidos`     VARCHAR(150) NOT NULL,
    `fen_identificacion` VARCHAR(20) DEFAULT NULL,
    `fen_email`         VARCHAR(200) DEFAULT NULL,
    `fen_telefono`      VARCHAR(20) DEFAULT NULL,
    `fen_rol`           ENUM('DIRECTOR_TECNICO','ENTRENADOR','ASISTENTE','PREPARADOR_FISICO','PORTEROS') DEFAULT 'ENTRENADOR',
    `fen_especialidad`  VARCHAR(100) DEFAULT NULL COMMENT 'Formativas, Alto rendimiento, etc.',
    `fen_certificaciones` TEXT DEFAULT NULL,
    `fen_foto`          VARCHAR(255) DEFAULT NULL,
    `fen_color`         VARCHAR(7) DEFAULT '#22C55E',
    `fen_activo`        TINYINT(1) DEFAULT 1,
    `fen_notas`         TEXT DEFAULT NULL,
    `fen_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fen_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_fen_tenant` (`fen_tenant_id`, `fen_activo`),
    INDEX `idx_fen_sede` (`fen_sede_id`),
    CONSTRAINT `fk_fen_tenant` FOREIGN KEY (`fen_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fen_sede` FOREIGN KEY (`fen_sede_id`) REFERENCES `instalaciones_sedes`(`sed_sede_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 8. futbol_grupos — Equipos/grupos de entrenamiento
--    Usa instalaciones_canchas como recurso (can_tipo='futbol')
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_grupos` (
    `fgr_grupo_id`      INT AUTO_INCREMENT PRIMARY KEY,
    `fgr_tenant_id`     INT NOT NULL,
    `fgr_sede_id`       INT DEFAULT NULL COMMENT 'FK → instalaciones_sedes',
    `fgr_periodo_id`    INT DEFAULT NULL COMMENT 'FK → futbol_periodos',
    `fgr_categoria_id`  INT DEFAULT NULL COMMENT 'FK → futbol_categorias',
    `fgr_cancha_id`     INT DEFAULT NULL COMMENT 'FK → instalaciones_canchas',
    `fgr_entrenador_id` INT DEFAULT NULL COMMENT 'FK → futbol_entrenadores',
    `fgr_nombre`        VARCHAR(100) NOT NULL COMMENT 'Ej: Sub-12 A — Competitivo',
    `fgr_descripcion`   TEXT DEFAULT NULL,
    `fgr_cupo_maximo`   INT DEFAULT 25,
    `fgr_cupo_actual`   INT DEFAULT 0,
    `fgr_edad_min`      INT DEFAULT NULL,
    `fgr_edad_max`      INT DEFAULT NULL,
    `fgr_precio`        DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Precio mensualidad',
    `fgr_estado`        ENUM('ABIERTO','CERRADO','EN_CURSO','FINALIZADO') DEFAULT 'ABIERTO',
    `fgr_color`         VARCHAR(7) DEFAULT '#22C55E',
    `fgr_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fgr_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_fgr_tenant_estado` (`fgr_tenant_id`, `fgr_estado`),
    INDEX `idx_fgr_periodo` (`fgr_periodo_id`),
    INDEX `idx_fgr_categoria` (`fgr_categoria_id`),
    INDEX `idx_fgr_sede` (`fgr_sede_id`),
    CONSTRAINT `fk_fgr_tenant` FOREIGN KEY (`fgr_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fgr_periodo` FOREIGN KEY (`fgr_periodo_id`) REFERENCES `futbol_periodos`(`fpe_periodo_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_fgr_categoria` FOREIGN KEY (`fgr_categoria_id`) REFERENCES `futbol_categorias`(`fct_categoria_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_fgr_cancha` FOREIGN KEY (`fgr_cancha_id`) REFERENCES `instalaciones_canchas`(`can_cancha_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_fgr_entrenador` FOREIGN KEY (`fgr_entrenador_id`) REFERENCES `futbol_entrenadores`(`fen_entrenador_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_fgr_sede` FOREIGN KEY (`fgr_sede_id`) REFERENCES `instalaciones_sedes`(`sed_sede_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 9. futbol_grupo_horarios — Horarios de entrenamiento
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_grupo_horarios` (
    `fgh_horario_id`    INT AUTO_INCREMENT PRIMARY KEY,
    `fgh_tenant_id`     INT NOT NULL,
    `fgh_grupo_id`      INT NOT NULL,
    `fgh_dia_semana`    ENUM('LUN','MAR','MIE','JUE','VIE','SAB','DOM') NOT NULL,
    `fgh_hora_inicio`   TIME NOT NULL,
    `fgh_hora_fin`      TIME NOT NULL,
    `fgh_cancha_id`     INT DEFAULT NULL COMMENT 'FK → instalaciones_canchas (opcional override)',
    `fgh_activo`        TINYINT(1) DEFAULT 1,
    
    INDEX `idx_fgh_grupo` (`fgh_grupo_id`),
    INDEX `idx_fgh_dia` (`fgh_tenant_id`, `fgh_dia_semana`, `fgh_hora_inicio`),
    CONSTRAINT `fk_fgh_tenant` FOREIGN KEY (`fgh_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fgh_grupo` FOREIGN KEY (`fgh_grupo_id`) REFERENCES `futbol_grupos`(`fgr_grupo_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fgh_cancha` FOREIGN KEY (`fgh_cancha_id`) REFERENCES `instalaciones_canchas`(`can_cancha_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 10. futbol_inscripciones — Alumno inscrito en grupo
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_inscripciones` (
    `fin_inscripcion_id` INT AUTO_INCREMENT PRIMARY KEY,
    `fin_tenant_id`      INT NOT NULL,
    `fin_alumno_id`      INT NOT NULL COMMENT 'FK → alumnos',
    `fin_grupo_id`       INT NOT NULL COMMENT 'FK → futbol_grupos',
    `fin_periodo_id`     INT DEFAULT NULL,
    `fin_fecha_inscripcion` DATE NOT NULL,
    `fin_fecha_baja`     DATE DEFAULT NULL,
    `fin_monto`          DECIMAL(10,2) DEFAULT 0.00,
    `fin_descuento`      DECIMAL(10,2) DEFAULT 0.00,
    `fin_monto_final`    DECIMAL(10,2) DEFAULT 0.00,
    `fin_beca_id`        INT DEFAULT NULL COMMENT 'FK → futbol_becas si aplica',
    `fin_estado`         ENUM('ACTIVA','SUSPENDIDA','FINALIZADA','CANCELADA') DEFAULT 'ACTIVA',
    `fin_notas`          TEXT DEFAULT NULL,
    `fin_created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fin_updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_fin_alumno_grupo` (`fin_alumno_id`, `fin_grupo_id`),
    INDEX `idx_fin_tenant_estado` (`fin_tenant_id`, `fin_estado`),
    INDEX `idx_fin_grupo` (`fin_grupo_id`),
    CONSTRAINT `fk_fin_tenant` FOREIGN KEY (`fin_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fin_alumno` FOREIGN KEY (`fin_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fin_grupo` FOREIGN KEY (`fin_grupo_id`) REFERENCES `futbol_grupos`(`fgr_grupo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 11. futbol_asistencia — Registro de asistencia
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_asistencia` (
    `fas_asistencia_id` INT AUTO_INCREMENT PRIMARY KEY,
    `fas_tenant_id`     INT NOT NULL,
    `fas_inscripcion_id` INT NOT NULL,
    `fas_grupo_id`      INT NOT NULL,
    `fas_alumno_id`     INT NOT NULL,
    `fas_fecha`         DATE NOT NULL,
    `fas_estado`        ENUM('PRESENTE','AUSENTE','TARDANZA','JUSTIFICADO') DEFAULT 'PRESENTE',
    `fas_observacion`   VARCHAR(255) DEFAULT NULL,
    `fas_registrado_por` INT DEFAULT NULL,
    `fas_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_fas_inscripcion_fecha` (`fas_inscripcion_id`, `fas_fecha`),
    INDEX `idx_fas_grupo_fecha` (`fas_grupo_id`, `fas_fecha`),
    INDEX `idx_fas_alumno` (`fas_alumno_id`, `fas_fecha`),
    CONSTRAINT `fk_fas_tenant` FOREIGN KEY (`fas_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fas_inscripcion` FOREIGN KEY (`fas_inscripcion_id`) REFERENCES `futbol_inscripciones`(`fin_inscripcion_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fas_grupo` FOREIGN KEY (`fas_grupo_id`) REFERENCES `futbol_grupos`(`fgr_grupo_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fas_alumno` FOREIGN KEY (`fas_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 12. futbol_evaluaciones — Evaluación de habilidades
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_evaluaciones` (
    `fev_evaluacion_id` INT AUTO_INCREMENT PRIMARY KEY,
    `fev_tenant_id`     INT NOT NULL,
    `fev_alumno_id`     INT NOT NULL,
    `fev_habilidad_id`  INT NOT NULL COMMENT 'FK → futbol_categoria_habilidades',
    `fev_categoria_id`  INT NOT NULL COMMENT 'FK → futbol_categorias',
    `fev_calificacion`  TINYINT UNSIGNED DEFAULT 0 COMMENT '0-5 estrellas',
    `fev_aprobado`      TINYINT(1) DEFAULT 0,
    `fev_fecha`         DATE NOT NULL,
    `fev_evaluador_id`  INT DEFAULT NULL COMMENT 'FK → futbol_entrenadores',
    `fev_observacion`   TEXT DEFAULT NULL,
    `fev_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_fev_alumno_habilidad_fecha` (`fev_alumno_id`, `fev_habilidad_id`, `fev_fecha`),
    INDEX `idx_fev_alumno` (`fev_alumno_id`, `fev_categoria_id`),
    CONSTRAINT `fk_fev_tenant` FOREIGN KEY (`fev_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fev_alumno` FOREIGN KEY (`fev_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fev_habilidad` FOREIGN KEY (`fev_habilidad_id`) REFERENCES `futbol_categoria_habilidades`(`fch_habilidad_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fev_categoria` FOREIGN KEY (`fev_categoria_id`) REFERENCES `futbol_categorias`(`fct_categoria_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 13. futbol_lista_espera — Waitlist por grupo
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_lista_espera` (
    `fle_espera_id`     INT AUTO_INCREMENT PRIMARY KEY,
    `fle_tenant_id`     INT NOT NULL,
    `fle_alumno_id`     INT NOT NULL,
    `fle_grupo_id`      INT NOT NULL,
    `fle_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fle_posicion`      INT DEFAULT 0,
    `fle_estado`        ENUM('ESPERANDO','NOTIFICADO','INSCRITO','CANCELADO') DEFAULT 'ESPERANDO',
    `fle_notas`         VARCHAR(255) DEFAULT NULL,
    
    UNIQUE KEY `uk_fle_alumno_grupo` (`fle_alumno_id`, `fle_grupo_id`),
    INDEX `idx_fle_grupo_estado` (`fle_grupo_id`, `fle_estado`, `fle_posicion`),
    CONSTRAINT `fk_fle_tenant` FOREIGN KEY (`fle_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fle_alumno` FOREIGN KEY (`fle_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fle_grupo` FOREIGN KEY (`fle_grupo_id`) REFERENCES `futbol_grupos`(`fgr_grupo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 14. futbol_becas — Becas y descuentos recurrentes
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_becas` (
    `fbe_beca_id`       INT AUTO_INCREMENT PRIMARY KEY,
    `fbe_tenant_id`     INT NOT NULL,
    `fbe_nombre`        VARCHAR(100) NOT NULL COMMENT 'Beca Deportiva, Hermanos, Referido, etc.',
    `fbe_tipo`          ENUM('PORCENTAJE','MONTO_FIJO','EXONERACION') DEFAULT 'PORCENTAJE',
    `fbe_valor`         DECIMAL(10,2) NOT NULL COMMENT '% o monto fijo',
    `fbe_descripcion`   TEXT DEFAULT NULL,
    `fbe_requisitos`    TEXT DEFAULT NULL,
    `fbe_cupo_maximo`   INT DEFAULT NULL COMMENT 'Null = sin límite',
    `fbe_cupo_usado`    INT DEFAULT 0,
    `fbe_vigencia_inicio` DATE DEFAULT NULL,
    `fbe_vigencia_fin`  DATE DEFAULT NULL,
    `fbe_aplica_matricula` TINYINT(1) DEFAULT 0,
    `fbe_aplica_mensualidad` TINYINT(1) DEFAULT 1,
    `fbe_activo`        TINYINT(1) DEFAULT 1,
    `fbe_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fbe_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_fbe_tenant` (`fbe_tenant_id`, `fbe_activo`),
    CONSTRAINT `fk_fbe_tenant` FOREIGN KEY (`fbe_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 15. futbol_beca_asignaciones — Beca asignada a un alumno
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_beca_asignaciones` (
    `fba_asignacion_id` INT AUTO_INCREMENT PRIMARY KEY,
    `fba_tenant_id`     INT NOT NULL,
    `fba_beca_id`       INT NOT NULL,
    `fba_alumno_id`     INT NOT NULL,
    `fba_inscripcion_id` INT DEFAULT NULL,
    `fba_fecha_asignacion` DATE NOT NULL,
    `fba_fecha_vencimiento` DATE DEFAULT NULL,
    `fba_motivo`        TEXT DEFAULT NULL,
    `fba_aprobado_por`  INT DEFAULT NULL COMMENT 'ID usuario que aprobó',
    `fba_estado`        ENUM('ACTIVA','SUSPENDIDA','VENCIDA','REVOCADA') DEFAULT 'ACTIVA',
    `fba_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_fba_alumno` (`fba_alumno_id`, `fba_estado`),
    INDEX `idx_fba_beca` (`fba_beca_id`),
    CONSTRAINT `fk_fba_tenant` FOREIGN KEY (`fba_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fba_beca` FOREIGN KEY (`fba_beca_id`) REFERENCES `futbol_becas`(`fbe_beca_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fba_alumno` FOREIGN KEY (`fba_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- FK inscripcion → beca
ALTER TABLE `futbol_inscripciones`
    ADD CONSTRAINT `fk_fin_beca` FOREIGN KEY (`fin_beca_id`) REFERENCES `futbol_becas`(`fbe_beca_id`) ON DELETE SET NULL;


-- ══════════════════════════════════════════════════════════════
-- 16. futbol_pagos — Pagos de mensualidades/matrículas
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_pagos` (
    `fpg_pago_id`       INT AUTO_INCREMENT PRIMARY KEY,
    `fpg_tenant_id`     INT NOT NULL,
    `fpg_sede_id`       INT DEFAULT NULL,
    `fpg_inscripcion_id` INT DEFAULT NULL,
    `fpg_alumno_id`     INT NOT NULL,
    `fpg_cliente_id`    INT DEFAULT NULL COMMENT 'FK → clientes (quien paga)',
    `fpg_concepto`      VARCHAR(200) NOT NULL COMMENT 'Mensualidad Febrero, Matrícula, Uniforme, etc.',
    `fpg_tipo`          ENUM('MATRICULA','MENSUALIDAD','UNIFORME','TORNEO','OTRO') DEFAULT 'MENSUALIDAD',
    `fpg_mes_correspondiente` VARCHAR(7) DEFAULT NULL COMMENT 'YYYY-MM del mes que cubre',
    `fpg_monto`         DECIMAL(10,2) NOT NULL,
    `fpg_descuento`     DECIMAL(10,2) DEFAULT 0.00,
    `fpg_beca_descuento` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Descuento aplicado por beca',
    `fpg_total`         DECIMAL(10,2) NOT NULL,
    `fpg_metodo_pago`   ENUM('EFECTIVO','TARJETA','TRANSFERENCIA','DEPOSITO','ABONO') DEFAULT 'EFECTIVO',
    `fpg_referencia`    VARCHAR(100) DEFAULT NULL,
    `fpg_fecha`         DATE NOT NULL,
    `fpg_fecha_vencimiento` DATE DEFAULT NULL COMMENT 'Fecha límite de pago',
    `fpg_dias_mora`     INT DEFAULT 0,
    `fpg_recargo_mora`  DECIMAL(10,2) DEFAULT 0.00,
    `fpg_estado`        ENUM('PENDIENTE','PAGADO','VENCIDO','ANULADO') DEFAULT 'PENDIENTE',
    `fpg_comprobante_num` VARCHAR(30) DEFAULT NULL COMMENT 'Número de comprobante emitido',
    `fpg_notas`         TEXT DEFAULT NULL,
    `fpg_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fpg_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_fpg_tenant_estado` (`fpg_tenant_id`, `fpg_estado`),
    INDEX `idx_fpg_alumno` (`fpg_alumno_id`),
    INDEX `idx_fpg_cliente` (`fpg_cliente_id`),
    INDEX `idx_fpg_sede` (`fpg_sede_id`),
    INDEX `idx_fpg_vencimiento` (`fpg_tenant_id`, `fpg_fecha_vencimiento`, `fpg_estado`),
    INDEX `idx_fpg_mora` (`fpg_tenant_id`, `fpg_dias_mora`),
    CONSTRAINT `fk_fpg_tenant` FOREIGN KEY (`fpg_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fpg_inscripcion` FOREIGN KEY (`fpg_inscripcion_id`) REFERENCES `futbol_inscripciones`(`fin_inscripcion_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_fpg_alumno` FOREIGN KEY (`fpg_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fpg_cliente` FOREIGN KEY (`fpg_cliente_id`) REFERENCES `clientes`(`cli_cliente_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_fpg_sede` FOREIGN KEY (`fpg_sede_id`) REFERENCES `instalaciones_sedes`(`sed_sede_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 17. futbol_comprobantes — Comprobantes de pago emitidos
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_comprobantes` (
    `fcm_comprobante_id` INT AUTO_INCREMENT PRIMARY KEY,
    `fcm_tenant_id`      INT NOT NULL,
    `fcm_pago_id`        INT NOT NULL COMMENT 'FK → futbol_pagos',
    `fcm_numero`         VARCHAR(30) NOT NULL COMMENT 'Nro. secuencial: ESC-0001',
    `fcm_tipo`           ENUM('RECIBO','FACTURA','NOTA_VENTA') DEFAULT 'RECIBO',
    `fcm_cliente_id`     INT DEFAULT NULL,
    `fcm_alumno_id`      INT NOT NULL,
    `fcm_concepto`       VARCHAR(200) NOT NULL,
    `fcm_subtotal`       DECIMAL(10,2) NOT NULL,
    `fcm_descuento`      DECIMAL(10,2) DEFAULT 0.00,
    `fcm_iva`            DECIMAL(10,2) DEFAULT 0.00,
    `fcm_total`          DECIMAL(10,2) NOT NULL,
    `fcm_metodo_pago`    VARCHAR(50) DEFAULT NULL,
    `fcm_fecha_emision`  DATE NOT NULL,
    `fcm_estado`         ENUM('EMITIDO','ANULADO') DEFAULT 'EMITIDO',
    `fcm_pdf_path`       VARCHAR(255) DEFAULT NULL,
    `fcm_enviado_email`  TINYINT(1) DEFAULT 0,
    `fcm_enviado_whatsapp` TINYINT(1) DEFAULT 0,
    `fcm_notas`          TEXT DEFAULT NULL,
    `fcm_created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_fcm_tenant_numero` (`fcm_tenant_id`, `fcm_numero`),
    INDEX `idx_fcm_pago` (`fcm_pago_id`),
    CONSTRAINT `fk_fcm_tenant` FOREIGN KEY (`fcm_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fcm_pago` FOREIGN KEY (`fcm_pago_id`) REFERENCES `futbol_pagos`(`fpg_pago_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fcm_cliente` FOREIGN KEY (`fcm_cliente_id`) REFERENCES `clientes`(`cli_cliente_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_fcm_alumno` FOREIGN KEY (`fcm_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 18. futbol_torneos — Torneos en los que participa la escuela
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_torneos` (
    `fto_torneo_id`     INT AUTO_INCREMENT PRIMARY KEY,
    `fto_tenant_id`     INT NOT NULL,
    `fto_nombre`        VARCHAR(150) NOT NULL,
    `fto_organizador`   VARCHAR(150) DEFAULT NULL,
    `fto_sede_torneo`   VARCHAR(200) DEFAULT NULL COMMENT 'Ubicación del torneo',
    `fto_fecha_inicio`  DATE NOT NULL,
    `fto_fecha_fin`     DATE DEFAULT NULL,
    `fto_categoria_id`  INT DEFAULT NULL,
    `fto_tipo`          ENUM('LIGA','COPA','AMISTOSO','INTERESCUELAS','CAMPEONATO') DEFAULT 'COPA',
    `fto_descripcion`   TEXT DEFAULT NULL,
    `fto_costo_inscripcion` DECIMAL(10,2) DEFAULT 0.00,
    `fto_estado`        ENUM('PROXIMO','EN_CURSO','FINALIZADO','CANCELADO') DEFAULT 'PROXIMO',
    `fto_resultado`     VARCHAR(100) DEFAULT NULL COMMENT 'Campeón, Sub-Campeón, 3er lugar, etc.',
    `fto_notas`         TEXT DEFAULT NULL,
    `fto_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fto_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_fto_tenant` (`fto_tenant_id`, `fto_estado`),
    CONSTRAINT `fk_fto_tenant` FOREIGN KEY (`fto_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fto_categoria` FOREIGN KEY (`fto_categoria_id`) REFERENCES `futbol_categorias`(`fct_categoria_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 19. futbol_torneo_jugadores — Jugadores convocados
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_torneo_jugadores` (
    `ftj_id`            INT AUTO_INCREMENT PRIMARY KEY,
    `ftj_tenant_id`     INT NOT NULL,
    `ftj_torneo_id`     INT NOT NULL,
    `ftj_alumno_id`     INT NOT NULL,
    `ftj_posicion`      VARCHAR(50) DEFAULT NULL,
    `ftj_numero`        INT DEFAULT NULL,
    `ftj_es_capitan`    TINYINT(1) DEFAULT 0,
    `ftj_estado`        ENUM('CONVOCADO','CONFIRMADO','BAJA') DEFAULT 'CONVOCADO',
    `ftj_notas`         TEXT DEFAULT NULL,
    `ftj_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_ftj_torneo_alumno` (`ftj_torneo_id`, `ftj_alumno_id`),
    CONSTRAINT `fk_ftj_tenant` FOREIGN KEY (`ftj_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ftj_torneo` FOREIGN KEY (`ftj_torneo_id`) REFERENCES `futbol_torneos`(`fto_torneo_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ftj_alumno` FOREIGN KEY (`ftj_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 20. futbol_notificaciones — Log de notificaciones enviadas
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_notificaciones` (
    `fno_notificacion_id` INT AUTO_INCREMENT PRIMARY KEY,
    `fno_tenant_id`      INT NOT NULL,
    `fno_alumno_id`      INT DEFAULT NULL,
    `fno_cliente_id`     INT DEFAULT NULL COMMENT 'FK → clientes (representante)',
    `fno_tipo`           ENUM('PAGO_PENDIENTE','MORA','BIENVENIDA','RECORDATORIO','TORNEO','ASISTENCIA','GENERAL') NOT NULL,
    `fno_canal`          ENUM('EMAIL','WHATSAPP','SMS','PUSH','SISTEMA') NOT NULL,
    `fno_asunto`         VARCHAR(200) DEFAULT NULL,
    `fno_mensaje`        TEXT NOT NULL,
    `fno_destinatario`   VARCHAR(200) DEFAULT NULL COMMENT 'Email, teléfono, etc.',
    `fno_estado`         ENUM('PENDIENTE','ENVIADO','FALLIDO','LEIDO') DEFAULT 'PENDIENTE',
    `fno_intentos`       INT DEFAULT 0,
    `fno_error`          TEXT DEFAULT NULL,
    `fno_referencia_id`  INT DEFAULT NULL COMMENT 'ID del pago, inscripción, etc.',
    `fno_referencia_tipo` VARCHAR(50) DEFAULT NULL COMMENT 'pago, inscripcion, torneo, etc.',
    `fno_fecha_programada` DATETIME DEFAULT NULL,
    `fno_fecha_envio`    DATETIME DEFAULT NULL,
    `fno_created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_fno_tenant_tipo` (`fno_tenant_id`, `fno_tipo`),
    INDEX `idx_fno_estado` (`fno_estado`, `fno_fecha_programada`),
    INDEX `idx_fno_alumno` (`fno_alumno_id`),
    CONSTRAINT `fk_fno_tenant` FOREIGN KEY (`fno_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fno_alumno` FOREIGN KEY (`fno_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_fno_cliente` FOREIGN KEY (`fno_cliente_id`) REFERENCES `clientes`(`cli_cliente_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 21. futbol_egresos — Gastos operativos por sede
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `futbol_egresos` (
    `feg_egreso_id`     INT AUTO_INCREMENT PRIMARY KEY,
    `feg_tenant_id`     INT NOT NULL,
    `feg_sede_id`       INT DEFAULT NULL,
    `feg_categoria`     ENUM('UNIFORMES','BALONES','CONOS_MATERIAL','ARBITRAJE','TRANSPORTE','CANCHAS','PERSONAL','TORNEOS','SEGUROS','MARKETING','SERVICIOS','OTROS') DEFAULT 'OTROS',
    `feg_concepto`      VARCHAR(200) NOT NULL,
    `feg_monto`         DECIMAL(10,2) NOT NULL,
    `feg_fecha`         DATE NOT NULL,
    `feg_proveedor`     VARCHAR(150) DEFAULT NULL,
    `feg_factura_ref`   VARCHAR(50) DEFAULT NULL,
    `feg_metodo_pago`   ENUM('EFECTIVO','TRANSFERENCIA','TARJETA','CHEQUE') DEFAULT 'EFECTIVO',
    `feg_referencia_pago` VARCHAR(100) DEFAULT NULL,
    `feg_estado`        ENUM('REGISTRADO','PAGADO','ANULADO') DEFAULT 'REGISTRADO',
    `feg_notas`         TEXT DEFAULT NULL,
    `feg_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `feg_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_feg_tenant_fecha` (`feg_tenant_id`, `feg_fecha`),
    INDEX `idx_feg_sede` (`feg_sede_id`),
    CONSTRAINT `fk_feg_tenant` FOREIGN KEY (`feg_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_feg_sede` FOREIGN KEY (`feg_sede_id`) REFERENCES `instalaciones_sedes`(`sed_sede_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;


-- ══════════════════════════════════════════════════════════════
-- DATOS INICIALES
-- ══════════════════════════════════════════════════════════════

-- Categorías estándar (tenant_id = 1)
INSERT INTO `futbol_categorias` (`fct_tenant_id`, `fct_nombre`, `fct_codigo`, `fct_descripcion`, `fct_color`, `fct_orden`, `fct_edad_min`, `fct_edad_max`) VALUES
(1, 'Sub-6 (Baby Fútbol)',   'U6',  'Iniciación al fútbol. Juegos lúdicos y motricidad básica.',    '#94A3B8', 1, 4, 6),
(1, 'Sub-8',                 'U8',  'Fundamentos básicos: conducción, pase y tiro. Juegos reducidos.', '#22C55E', 2, 7, 8),
(1, 'Sub-10',                'U10', 'Técnica individual, conceptos tácticos básicos y juego 7v7.',   '#3B82F6', 3, 9, 10),
(1, 'Sub-12',                'U12', 'Desarrollo táctico, transiciones y juego 9v9.',                 '#8B5CF6', 4, 11, 12),
(1, 'Sub-14',                'U14', 'Fútbol 11, sistemas de juego y preparación competitiva.',       '#F59E0B', 5, 13, 14),
(1, 'Sub-16',                'U16', 'Alto rendimiento juvenil. Especialización por posición.',       '#EF4444', 6, 15, 16),
(1, 'Sub-18',                'U18', 'Competitivo senior juvenil. Preparación para fútbol amateur.',  '#EC4899', 7, 17, 18),
(1, 'Adultos',               'ADU', 'Categoría libre para mayores de 18.',                          '#06B6D4', 8, 18, 99);

-- Habilidades de Sub-8 (U8)
INSERT INTO `futbol_categoria_habilidades` (`fch_tenant_id`, `fch_categoria_id`, `fch_nombre`, `fch_descripcion`, `fch_orden`) VALUES
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U8' AND fct_tenant_id=1), 'Conducción con ambos pies', 'Conducir el balón 15m alternando pie derecho e izquierdo', 1),
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U8' AND fct_tenant_id=1), 'Pase corto', 'Pase con interior del pie a compañero a 5-8 metros', 2),
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U8' AND fct_tenant_id=1), 'Control orientado', 'Recepción y control del balón con cambio de dirección', 3),
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U8' AND fct_tenant_id=1), 'Tiro a puerta', 'Tiro con empeine desde 10 metros al arco', 4),
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U8' AND fct_tenant_id=1), 'Regate simple', 'Superar rival con amague y cambio de dirección', 5);

-- Habilidades de Sub-10 (U10)
INSERT INTO `futbol_categoria_habilidades` (`fch_tenant_id`, `fch_categoria_id`, `fch_nombre`, `fch_descripcion`, `fch_orden`) VALUES
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U10' AND fct_tenant_id=1), 'Pase largo', 'Pase con empeine a 15-20 metros de distancia', 1),
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U10' AND fct_tenant_id=1), 'Cabeceo', 'Cabeceo ofensivo y defensivo con técnica correcta', 2),
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U10' AND fct_tenant_id=1), 'Regate compuesto', 'Superar rival con secuencia de 2+ amagues', 3),
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U10' AND fct_tenant_id=1), 'Tiro de media distancia', 'Tiro con potencia desde fuera del área', 4),
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U10' AND fct_tenant_id=1), 'Posicionamiento táctico', 'Ubicación correcta en sistema 3-3-1 (7v7)', 5),
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U10' AND fct_tenant_id=1), 'Marca individual', 'Seguimiento al rival directo y recuperación', 6);

-- Habilidades de Sub-12 (U12)
INSERT INTO `futbol_categoria_habilidades` (`fch_tenant_id`, `fch_categoria_id`, `fch_nombre`, `fch_descripcion`, `fch_orden`) VALUES
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U12' AND fct_tenant_id=1), 'Pase al espacio', 'Anticipar el movimiento del compañero y pasar al espacio', 1),
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U12' AND fct_tenant_id=1), 'Centro al área', 'Centro preciso desde banda al área de penal', 2),
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U12' AND fct_tenant_id=1), 'Tiro libre', 'Ejecución de tiro libre con efecto', 3),
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U12' AND fct_tenant_id=1), 'Juego de transición', 'Cambiar de defensa a ataque y viceversa con velocidad', 4),
(1, (SELECT fct_categoria_id FROM futbol_categorias WHERE fct_codigo='U12' AND fct_tenant_id=1), 'Juego aéreo', 'Disputa de balones aéreos en ataque y defensa', 5);

-- Becas por defecto
INSERT INTO `futbol_becas` (`fbe_tenant_id`, `fbe_nombre`, `fbe_tipo`, `fbe_valor`, `fbe_descripcion`, `fbe_aplica_mensualidad`) VALUES
(1, 'Beca Deportiva 100%', 'EXONERACION', 100.00, 'Exoneración total por talento deportivo destacado', 1),
(1, 'Beca Deportiva 50%',  'PORCENTAJE',  50.00,  'Media beca por rendimiento deportivo', 1),
(1, 'Descuento Hermanos',  'PORCENTAJE',  15.00,  '15% de descuento por hermano inscrito', 1),
(1, 'Beca Socioeconómica', 'PORCENTAJE',  30.00,  '30% de descuento por situación socioeconómica comprobada', 1),
(1, 'Descuento Referido',  'MONTO_FIJO',  10.00,  '$10 de descuento mensual por referir nuevo alumno', 1),
(1, 'Descuento Pronto Pago','PORCENTAJE',  5.00,   '5% por pago anticipado antes del día 5', 1);

-- Campos de ficha personalizables
INSERT INTO `futbol_campos_ficha` (`fcf_tenant_id`, `fcf_clave`, `fcf_etiqueta`, `fcf_tipo`, `fcf_opciones`, `fcf_placeholder`, `fcf_requerido`, `fcf_grupo`, `fcf_orden`) VALUES
(1, 'posicion_secundaria', 'Posición Secundaria',     'SELECT',   '["Portero","Defensa Central","Lateral","Mediocampista","Extremo","Delantero"]', NULL, 0, 'deportivo', 1),
(1, 'pie_habil',           'Pie más Hábil',           'SELECT',   '["Derecho","Izquierdo","Ambidiestro"]', NULL, 1, 'deportivo', 2),
(1, 'club_favorito',       'Club Favorito',           'TEXT',     NULL, 'Ej: Barcelona SC, Liga de Quito...', 0, 'personal', 1),
(1, 'jugador_favorito',    'Jugador Favorito',        'TEXT',     NULL, 'Ej: Messi, Cristiano...', 0, 'personal', 2),
(1, 'como_nos_conocio',    '¿Cómo nos conoció?',     'SELECT',   '["Redes sociales","Recomendación","Publicidad","Escuela/Colegio","Otro"]', NULL, 0, 'general', 1),
(1, 'autoriza_fotos',      'Autoriza publicación de fotos/videos', 'CHECKBOX', NULL, NULL, 1, 'legal', 1),
(1, 'acepta_reglamento',   'Acepta reglamento interno', 'CHECKBOX', NULL, NULL, 1, 'legal', 2),
(1, 'obs_medicas_extra',   'Observaciones Médicas Adicionales', 'TEXTAREA', NULL, 'Lesiones previas, limitaciones físicas...', 0, 'medico', 1);

-- Configuración por defecto
INSERT INTO `futbol_configuracion` (`fcg_tenant_id`, `fcg_clave`, `fcg_valor`, `fcg_tipo`, `fcg_descripcion`) VALUES
(1, 'nombre_modulo',             'Escuela de Fútbol',       'TEXT',    'Nombre personalizado del módulo'),
(1, 'moneda',                    'USD',                     'TEXT',    'Moneda para precios'),
(1, 'max_alumnos_grupo',         '25',                      'NUMBER',  'Máximo de alumnos por grupo'),
(1, 'requiere_certificado_medico','true',                   'BOOLEAN', 'Exigir certificado médico'),
(1, 'edad_minima_inscripcion',   '4',                       'NUMBER',  'Edad mínima para inscribir'),
(1, 'permite_lista_espera',      'true',                    'BOOLEAN', 'Activar lista de espera'),
(1, 'dias_prueba_gratis',        '3',                       'NUMBER',  'Días de clase de prueba gratis'),
(1, 'porcentaje_asistencia_min', '70',                      'NUMBER',  'Porcentaje mínimo de asistencia'),
(1, 'escala_evaluacion',         '5',                       'NUMBER',  'Escala de evaluación (1-5 estrellas)'),
(1, 'dia_pago_limite',           '10',                      'NUMBER',  'Día del mes límite para pago sin mora'),
(1, 'porcentaje_mora',           '5',                       'NUMBER',  'Porcentaje de recargo por mora mensual'),
(1, 'dias_gracia_mora',          '5',                       'NUMBER',  'Días de gracia antes de aplicar mora'),
(1, 'comprobante_prefijo',       'ESC',                     'TEXT',    'Prefijo para números de comprobante'),
(1, 'whatsapp_activo',           'false',                   'BOOLEAN', 'Activar notificaciones WhatsApp'),
(1, 'email_activo',              'true',                    'BOOLEAN', 'Activar notificaciones por email'),
(1, 'sms_activo',                'false',                   'BOOLEAN', 'Activar notificaciones SMS');


-- ══════════════════════════════════════════════════════════════
-- MENÚ — Actualizar ítems del módulo FUTBOL
-- ══════════════════════════════════════════════════════════════

-- Eliminar menú existente de fútbol para recrear completo
DELETE FROM seguridad_menu WHERE men_modulo_id = (SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = 'FUTBOL');

SET @fut_mod_id = (SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = 'FUTBOL');

-- HEADER: Principal
INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, 1, 1);
SET @h1 = LAST_INSERT_ID();

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, @h1, 'ITEM', 'Dashboard',       'fas fa-tachometer-alt', 'futbol', 'dashboard',  'index', 1, 1),
(@fut_mod_id, @h1, 'ITEM', 'Horario Semanal', 'fas fa-calendar-alt',   'futbol', 'horario',    'index', 2, 1);

-- HEADER: Gestión Académica
INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, NULL, 'HEADER', 'Gestión Académica', NULL, NULL, NULL, NULL, 2, 1);
SET @h2 = LAST_INSERT_ID();

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, @h2, 'ITEM', 'Jugadores',      'fas fa-user-graduate',  'futbol', 'alumno',       'index', 1, 1),
(@fut_mod_id, @h2, 'ITEM', 'Inscripciones',  'fas fa-clipboard-list', 'futbol', 'inscripcion',  'index', 2, 1),
(@fut_mod_id, @h2, 'ITEM', 'Asistencia',     'fas fa-check-double',   'futbol', 'asistencia',   'index', 3, 1),
(@fut_mod_id, @h2, 'ITEM', 'Evaluaciones',   'fas fa-star-half-alt',  'futbol', 'evaluacion',   'index', 4, 1),
(@fut_mod_id, @h2, 'ITEM', 'Categorías',     'fas fa-layer-group',    'futbol', 'categoria',    'index', 5, 1);

-- HEADER: Infraestructura
INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, NULL, 'HEADER', 'Infraestructura', NULL, NULL, NULL, NULL, 3, 1);
SET @h3 = LAST_INSERT_ID();

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, @h3, 'ITEM', 'Canchas',          'fas fa-futbol',             'futbol', 'cancha',       'index', 1, 1),
(@fut_mod_id, @h3, 'ITEM', 'Entrenadores',     'fas fa-chalkboard-teacher', 'futbol', 'entrenador',   'index', 2, 1),
(@fut_mod_id, @h3, 'ITEM', 'Grupos/Equipos',   'fas fa-users',              'futbol', 'grupo',        'index', 3, 1),
(@fut_mod_id, @h3, 'ITEM', 'Períodos',         'fas fa-calendar-check',     'futbol', 'periodo',      'index', 4, 1);

-- HEADER: Competencias
INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, NULL, 'HEADER', 'Competencias', NULL, NULL, NULL, NULL, 4, 1);
SET @h4 = LAST_INSERT_ID();

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, @h4, 'ITEM', 'Torneos',          'fas fa-trophy',         'futbol', 'torneo',      'index', 1, 1),
(@fut_mod_id, @h4, 'ITEM', 'Convocatorias',    'fas fa-bullhorn',       'futbol', 'convocatoria','index', 2, 1);

-- HEADER: Financiero
INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, NULL, 'HEADER', 'Financiero', NULL, NULL, NULL, NULL, 5, 1);
SET @h5 = LAST_INSERT_ID();

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, @h5, 'ITEM', 'Pagos',             'fas fa-money-bill-wave',    'futbol', 'pago',         'index', 1, 1),
(@fut_mod_id, @h5, 'ITEM', 'Becas/Descuentos',  'fas fa-gift',               'futbol', 'beca',         'index', 2, 1),
(@fut_mod_id, @h5, 'ITEM', 'Control de Mora',   'fas fa-exclamation-triangle','futbol', 'mora',         'index', 3, 1),
(@fut_mod_id, @h5, 'ITEM', 'Comprobantes',      'fas fa-file-invoice-dollar', 'futbol', 'comprobante',  'index', 4, 1),
(@fut_mod_id, @h5, 'ITEM', 'Egresos',           'fas fa-receipt',             'futbol', 'egreso',       'index', 5, 1),
(@fut_mod_id, @h5, 'ITEM', 'Reportes',          'fas fa-chart-bar',           'futbol', 'reporte',      'index', 6, 1);

-- HEADER: Comunicaciones
INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, NULL, 'HEADER', 'Comunicaciones', NULL, NULL, NULL, NULL, 6, 1);
SET @h6 = LAST_INSERT_ID();

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, @h6, 'ITEM', 'Notificaciones',   'fas fa-bell',           'futbol', 'notificacion', 'index', 1, 1);

-- HEADER: Configuración
INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, NULL, 'HEADER', 'Configuración', NULL, NULL, NULL, NULL, 7, 1);
SET @h7 = LAST_INSERT_ID();

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@fut_mod_id, @h7, 'ITEM', 'Sedes',             'fas fa-building',      'futbol', 'sede',          'index', 1, 1),
(@fut_mod_id, @h7, 'ITEM', 'Campos de Ficha',   'fas fa-sliders-h',     'futbol', 'campoficha',    'index', 2, 1),
(@fut_mod_id, @h7, 'ITEM', 'Configuración',     'fas fa-cog',           'futbol', 'configuracion', 'index', 3, 1);
