-- ╔═══════════════════════════════════════════════════════════════════╗
-- ║  DigiSports Natación — Schema Completo                         ║
-- ║  Fecha: 2026-02-09                                              ║
-- ║  Incluye: tabla compartida alumnos + 15 tablas natacion_*       ║
-- ╚═══════════════════════════════════════════════════════════════════╝

SET FOREIGN_KEY_CHECKS = 0;

-- ══════════════════════════════════════════════════════════════
-- TABLA COMPARTIDA: alumnos (usada por todos los subsistemas)
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `alumnos` (
    `alu_alumno_id`             INT AUTO_INCREMENT PRIMARY KEY,
    `alu_tenant_id`             INT NOT NULL,
    `alu_representante_id`      INT DEFAULT NULL COMMENT 'FK → clientes (padre/madre/tutor)',
    `alu_parentesco`            ENUM('PADRE','MADRE','TUTOR','ABUELO','OTRO') DEFAULT 'PADRE',
    
    -- Identificación (opcional, cifrado con DataProtection)
    `alu_tipo_identificacion`   VARCHAR(3) DEFAULT NULL COMMENT 'CED, PAS, OTR',
    `alu_identificacion`        VARCHAR(255) DEFAULT NULL COMMENT 'Cifrado ENC::...',
    `alu_identificacion_hash`   VARCHAR(32) DEFAULT NULL COMMENT 'Blind index para búsquedas',
    
    -- Datos personales
    `alu_nombres`               VARCHAR(150) NOT NULL,
    `alu_apellidos`             VARCHAR(150) NOT NULL,
    `alu_fecha_nacimiento`      DATE DEFAULT NULL,
    `alu_genero`                ENUM('M','F','O') DEFAULT NULL,
    `alu_foto`                  VARCHAR(255) DEFAULT NULL,
    
    -- Datos médicos universales
    `alu_tipo_sangre`           VARCHAR(5) DEFAULT NULL COMMENT 'A+, A-, B+, B-, AB+, AB-, O+, O-',
    `alu_alergias`              TEXT DEFAULT NULL,
    `alu_condiciones_medicas`   TEXT DEFAULT NULL,
    `alu_medicamentos`          TEXT DEFAULT NULL,
    
    -- Contacto de emergencia
    `alu_contacto_emergencia`   VARCHAR(200) DEFAULT NULL,
    `alu_telefono_emergencia`   VARCHAR(20) DEFAULT NULL,
    
    -- Estado
    `alu_estado`                ENUM('ACTIVO','INACTIVO','SUSPENDIDO') DEFAULT 'ACTIVO',
    `alu_notas`                 TEXT DEFAULT NULL,
    `alu_fecha_registro`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `alu_fecha_actualizacion`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX `idx_alu_tenant` (`alu_tenant_id`),
    INDEX `idx_alu_representante` (`alu_representante_id`),
    INDEX `idx_alu_identificacion_hash` (`alu_identificacion_hash`),
    INDEX `idx_alu_estado` (`alu_tenant_id`, `alu_estado`),
    INDEX `idx_alu_nombres` (`alu_tenant_id`, `alu_apellidos`, `alu_nombres`),
    
    CONSTRAINT `fk_alu_tenant` FOREIGN KEY (`alu_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_alu_representante` FOREIGN KEY (`alu_representante_id`) REFERENCES `clientes`(`cli_cliente_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla compartida de alumnos — todos los subsistemas deportivos';


-- ══════════════════════════════════════════════════════════════
-- 1. natacion_configuracion — Config general del módulo por tenant
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_configuracion` (
    `ncg_config_id`     INT AUTO_INCREMENT PRIMARY KEY,
    `ncg_tenant_id`     INT NOT NULL,
    `ncg_clave`         VARCHAR(80) NOT NULL,
    `ncg_valor`         TEXT DEFAULT NULL,
    `ncg_tipo`          ENUM('TEXT','NUMBER','BOOLEAN','JSON','SELECT') DEFAULT 'TEXT',
    `ncg_descripcion`   VARCHAR(200) DEFAULT NULL,
    `ncg_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ncg_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_ncg_tenant_clave` (`ncg_tenant_id`, `ncg_clave`),
    CONSTRAINT `fk_ncg_tenant` FOREIGN KEY (`ncg_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 2. natacion_campos_ficha — Campos personalizables por tenant
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_campos_ficha` (
    `ncf_campo_id`      INT AUTO_INCREMENT PRIMARY KEY,
    `ncf_tenant_id`     INT NOT NULL,
    `ncf_clave`         VARCHAR(50) NOT NULL COMMENT 'Key en JSON: talla_traje, objetivo, etc.',
    `ncf_etiqueta`      VARCHAR(100) NOT NULL COMMENT 'Label visible: Talla de Traje de Baño',
    `ncf_tipo`          ENUM('TEXT','TEXTAREA','SELECT','NUMBER','DATE','CHECKBOX','RADIO','EMAIL','TEL') DEFAULT 'TEXT',
    `ncf_opciones`      JSON DEFAULT NULL COMMENT 'Para SELECT/RADIO: ["S","M","L","XL"]',
    `ncf_placeholder`   VARCHAR(150) DEFAULT NULL,
    `ncf_requerido`     TINYINT(1) DEFAULT 0,
    `ncf_grupo`         VARCHAR(50) DEFAULT 'general' COMMENT 'medico, deportivo, personal, legal',
    `ncf_orden`         INT DEFAULT 0,
    `ncf_activo`        TINYINT(1) DEFAULT 1,
    `ncf_validacion`    JSON DEFAULT NULL COMMENT '{"min":1,"max":100} o {"regex":"^[0-9]+$"}',
    `ncf_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ncf_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_ncf_tenant_clave` (`ncf_tenant_id`, `ncf_clave`),
    INDEX `idx_ncf_orden` (`ncf_tenant_id`, `ncf_grupo`, `ncf_orden`),
    CONSTRAINT `fk_ncf_tenant` FOREIGN KEY (`ncf_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuración de campos personalizables de ficha de alumno por tenant';


-- ══════════════════════════════════════════════════════════════
-- 3. natacion_ficha_alumno — Extensión natación del alumno
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_ficha_alumno` (
    `nfa_ficha_id`              INT AUTO_INCREMENT PRIMARY KEY,
    `nfa_tenant_id`             INT NOT NULL,
    `nfa_alumno_id`             INT NOT NULL COMMENT 'FK → alumnos',
    
    -- Datos específicos de natación (columnas fijas)
    `nfa_nivel_actual_id`       INT DEFAULT NULL COMMENT 'FK → natacion_niveles',
    `nfa_sabe_nadar`            TINYINT(1) DEFAULT 0,
    `nfa_experiencia_previa`    TEXT DEFAULT NULL,
    `nfa_objetivo`              ENUM('RECREATIVO','FORMATIVO','COMPETITIVO','TERAPEUTICO') DEFAULT 'RECREATIVO',
    `nfa_autorizacion_medica`   TINYINT(1) DEFAULT 0 COMMENT 'Certificado médico entregado',
    `nfa_seguro_medico`         VARCHAR(100) DEFAULT NULL,
    `nfa_fecha_ingreso`         DATE DEFAULT NULL,
    `nfa_fecha_ultimo_avance`   DATE DEFAULT NULL,
    
    -- Datos personalizados por tenant (JSON flexible)
    `nfa_datos_custom`          JSON DEFAULT NULL COMMENT 'Valores de campos definidos en natacion_campos_ficha',
    
    -- Documentos adjuntos
    `nfa_documentos`            JSON DEFAULT NULL COMMENT '[{"tipo":"certificado_medico","url":"...","fecha":"..."}]',
    
    -- Estado en natación
    `nfa_activo`                TINYINT(1) DEFAULT 1,
    `nfa_notas`                 TEXT DEFAULT NULL,
    `nfa_created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `nfa_updated_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_nfa_tenant_alumno` (`nfa_tenant_id`, `nfa_alumno_id`),
    INDEX `idx_nfa_nivel` (`nfa_nivel_actual_id`),
    CONSTRAINT `fk_nfa_tenant` FOREIGN KEY (`nfa_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nfa_alumno` FOREIGN KEY (`nfa_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ficha de natación del alumno — extensión con datos custom JSON';


-- ══════════════════════════════════════════════════════════════
-- 4. natacion_periodos — Ciclos/períodos académicos
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_periodos` (
    `npe_periodo_id`    INT AUTO_INCREMENT PRIMARY KEY,
    `npe_tenant_id`     INT NOT NULL,
    `npe_nombre`        VARCHAR(100) NOT NULL COMMENT 'Ej: Enero-Marzo 2026',
    `npe_fecha_inicio`  DATE NOT NULL,
    `npe_fecha_fin`     DATE NOT NULL,
    `npe_estado`        ENUM('PLANIFICADO','ACTIVO','FINALIZADO') DEFAULT 'PLANIFICADO',
    `npe_notas`         TEXT DEFAULT NULL,
    `npe_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `npe_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_npe_tenant_estado` (`npe_tenant_id`, `npe_estado`),
    CONSTRAINT `fk_npe_tenant` FOREIGN KEY (`npe_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 5. natacion_niveles — Niveles parametrizables por tenant
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_niveles` (
    `nnv_nivel_id`      INT AUTO_INCREMENT PRIMARY KEY,
    `nnv_tenant_id`     INT NOT NULL,
    `nnv_nombre`        VARCHAR(80) NOT NULL COMMENT 'Principiante, Intermedio, Avanzado',
    `nnv_codigo`        VARCHAR(20) NOT NULL COMMENT 'N1, N2, N3',
    `nnv_descripcion`   TEXT DEFAULT NULL,
    `nnv_color`         VARCHAR(7) DEFAULT '#3B82F6' COMMENT 'Color identificativo',
    `nnv_orden`         INT DEFAULT 0,
    `nnv_edad_min`      INT DEFAULT NULL COMMENT 'Edad mínima sugerida',
    `nnv_edad_max`      INT DEFAULT NULL COMMENT 'Edad máxima sugerida',
    `nnv_activo`        TINYINT(1) DEFAULT 1,
    `nnv_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_nnv_tenant_codigo` (`nnv_tenant_id`, `nnv_codigo`),
    INDEX `idx_nnv_orden` (`nnv_tenant_id`, `nnv_orden`),
    CONSTRAINT `fk_nnv_tenant` FOREIGN KEY (`nnv_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar FK en natacion_ficha_alumno → natacion_niveles
ALTER TABLE `natacion_ficha_alumno`
    ADD CONSTRAINT `fk_nfa_nivel` FOREIGN KEY (`nfa_nivel_actual_id`) REFERENCES `natacion_niveles`(`nnv_nivel_id`) ON DELETE SET NULL;


-- ══════════════════════════════════════════════════════════════
-- 6. natacion_nivel_habilidades — Skills evaluables por nivel
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_nivel_habilidades` (
    `nnh_habilidad_id`  INT AUTO_INCREMENT PRIMARY KEY,
    `nnh_tenant_id`     INT NOT NULL,
    `nnh_nivel_id`      INT NOT NULL,
    `nnh_nombre`        VARCHAR(100) NOT NULL COMMENT 'Patada flutter, Respiración lateral, Crol 25m',
    `nnh_descripcion`   TEXT DEFAULT NULL,
    `nnh_orden`         INT DEFAULT 0,
    `nnh_activo`        TINYINT(1) DEFAULT 1,
    `nnh_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_nnh_nivel` (`nnh_nivel_id`, `nnh_orden`),
    CONSTRAINT `fk_nnh_tenant` FOREIGN KEY (`nnh_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nnh_nivel` FOREIGN KEY (`nnh_nivel_id`) REFERENCES `natacion_niveles`(`nnv_nivel_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 7. natacion_piscinas — Piscinas del complejo
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_piscinas` (
    `npi_piscina_id`    INT AUTO_INCREMENT PRIMARY KEY,
    `npi_tenant_id`     INT NOT NULL,
    `npi_nombre`        VARCHAR(100) NOT NULL COMMENT 'Piscina Olímpica, Piscina Niños',
    `npi_tipo`          ENUM('OLIMPICA','SEMI_OLIMPICA','RECREATIVA','TERAPEUTICA','INFANTIL') DEFAULT 'SEMI_OLIMPICA',
    `npi_largo`         DECIMAL(5,2) DEFAULT NULL COMMENT 'Metros',
    `npi_ancho`         DECIMAL(5,2) DEFAULT NULL,
    `npi_profundidad_min` DECIMAL(3,2) DEFAULT NULL,
    `npi_profundidad_max` DECIMAL(3,2) DEFAULT NULL,
    `npi_num_carriles`  INT DEFAULT 6,
    `npi_temperatura`   DECIMAL(4,1) DEFAULT NULL COMMENT 'Temperatura del agua °C',
    `npi_ubicacion`     VARCHAR(200) DEFAULT NULL,
    `npi_foto`          VARCHAR(255) DEFAULT NULL,
    `npi_activo`        TINYINT(1) DEFAULT 1,
    `npi_notas`         TEXT DEFAULT NULL,
    `npi_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_npi_tenant` (`npi_tenant_id`, `npi_activo`),
    CONSTRAINT `fk_npi_tenant` FOREIGN KEY (`npi_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 8. natacion_carriles — Carriles por piscina
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_carriles` (
    `nca_carril_id`     INT AUTO_INCREMENT PRIMARY KEY,
    `nca_tenant_id`     INT NOT NULL,
    `nca_piscina_id`    INT NOT NULL,
    `nca_numero`        INT NOT NULL COMMENT 'Carril 1, 2, 3...',
    `nca_nombre`        VARCHAR(50) DEFAULT NULL COMMENT 'Nombre descriptivo opcional',
    `nca_activo`        TINYINT(1) DEFAULT 1,
    
    UNIQUE KEY `uk_nca_piscina_numero` (`nca_piscina_id`, `nca_numero`),
    CONSTRAINT `fk_nca_tenant` FOREIGN KEY (`nca_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nca_piscina` FOREIGN KEY (`nca_piscina_id`) REFERENCES `natacion_piscinas`(`npi_piscina_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 9. natacion_instructores — Instructores/Profesores
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_instructores` (
    `nin_instructor_id` INT AUTO_INCREMENT PRIMARY KEY,
    `nin_tenant_id`     INT NOT NULL,
    `nin_usuario_id`    INT DEFAULT NULL COMMENT 'FK opcional → seguridad_usuarios (si tiene login)',
    `nin_nombres`       VARCHAR(150) NOT NULL,
    `nin_apellidos`     VARCHAR(150) NOT NULL,
    `nin_identificacion` VARCHAR(20) DEFAULT NULL,
    `nin_email`         VARCHAR(200) DEFAULT NULL,
    `nin_telefono`      VARCHAR(20) DEFAULT NULL,
    `nin_especialidad`  VARCHAR(100) DEFAULT NULL COMMENT 'Bebés, Competitivo, Adultos, etc.',
    `nin_certificaciones` TEXT DEFAULT NULL,
    `nin_foto`          VARCHAR(255) DEFAULT NULL,
    `nin_color`         VARCHAR(7) DEFAULT '#3B82F6' COMMENT 'Color en calendario',
    `nin_activo`        TINYINT(1) DEFAULT 1,
    `nin_notas`         TEXT DEFAULT NULL,
    `nin_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `nin_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_nin_tenant` (`nin_tenant_id`, `nin_activo`),
    CONSTRAINT `fk_nin_tenant` FOREIGN KEY (`nin_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 10. natacion_grupos — Clases/Grupos de natación
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_grupos` (
    `ngr_grupo_id`      INT AUTO_INCREMENT PRIMARY KEY,
    `ngr_tenant_id`     INT NOT NULL,
    `ngr_periodo_id`    INT DEFAULT NULL COMMENT 'FK → natacion_periodos',
    `ngr_nivel_id`      INT DEFAULT NULL COMMENT 'FK → natacion_niveles',
    `ngr_piscina_id`    INT DEFAULT NULL COMMENT 'FK → natacion_piscinas',
    `ngr_instructor_id` INT DEFAULT NULL COMMENT 'FK → natacion_instructores',
    `ngr_nombre`        VARCHAR(100) NOT NULL COMMENT 'Ej: Nivel 1 - Grupo A Mañana',
    `ngr_descripcion`   TEXT DEFAULT NULL,
    `ngr_cupo_maximo`   INT DEFAULT 10,
    `ngr_cupo_actual`   INT DEFAULT 0,
    `ngr_edad_min`      INT DEFAULT NULL,
    `ngr_edad_max`      INT DEFAULT NULL,
    `ngr_precio`        DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Precio mensual/período',
    `ngr_estado`        ENUM('ABIERTO','CERRADO','EN_CURSO','FINALIZADO') DEFAULT 'ABIERTO',
    `ngr_color`         VARCHAR(7) DEFAULT '#0EA5E9',
    `ngr_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ngr_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_ngr_tenant_estado` (`ngr_tenant_id`, `ngr_estado`),
    INDEX `idx_ngr_periodo` (`ngr_periodo_id`),
    INDEX `idx_ngr_nivel` (`ngr_nivel_id`),
    CONSTRAINT `fk_ngr_tenant` FOREIGN KEY (`ngr_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ngr_periodo` FOREIGN KEY (`ngr_periodo_id`) REFERENCES `natacion_periodos`(`npe_periodo_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ngr_nivel` FOREIGN KEY (`ngr_nivel_id`) REFERENCES `natacion_niveles`(`nnv_nivel_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ngr_piscina` FOREIGN KEY (`ngr_piscina_id`) REFERENCES `natacion_piscinas`(`npi_piscina_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ngr_instructor` FOREIGN KEY (`ngr_instructor_id`) REFERENCES `natacion_instructores`(`nin_instructor_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 11. natacion_grupo_horarios — Horarios de cada grupo
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_grupo_horarios` (
    `ngh_horario_id`    INT AUTO_INCREMENT PRIMARY KEY,
    `ngh_tenant_id`     INT NOT NULL,
    `ngh_grupo_id`      INT NOT NULL,
    `ngh_dia_semana`    ENUM('LUN','MAR','MIE','JUE','VIE','SAB','DOM') NOT NULL,
    `ngh_hora_inicio`   TIME NOT NULL,
    `ngh_hora_fin`      TIME NOT NULL,
    `ngh_carril_id`     INT DEFAULT NULL COMMENT 'FK → natacion_carriles (opcional)',
    `ngh_activo`        TINYINT(1) DEFAULT 1,
    
    INDEX `idx_ngh_grupo` (`ngh_grupo_id`),
    INDEX `idx_ngh_dia` (`ngh_tenant_id`, `ngh_dia_semana`, `ngh_hora_inicio`),
    CONSTRAINT `fk_ngh_tenant` FOREIGN KEY (`ngh_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ngh_grupo` FOREIGN KEY (`ngh_grupo_id`) REFERENCES `natacion_grupos`(`ngr_grupo_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ngh_carril` FOREIGN KEY (`ngh_carril_id`) REFERENCES `natacion_carriles`(`nca_carril_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 12. natacion_inscripciones — Alumno inscrito en grupo
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_inscripciones` (
    `nis_inscripcion_id` INT AUTO_INCREMENT PRIMARY KEY,
    `nis_tenant_id`      INT NOT NULL,
    `nis_alumno_id`      INT NOT NULL COMMENT 'FK → alumnos',
    `nis_grupo_id`       INT NOT NULL COMMENT 'FK → natacion_grupos',
    `nis_periodo_id`     INT DEFAULT NULL,
    `nis_fecha_inscripcion` DATE NOT NULL,
    `nis_fecha_baja`     DATE DEFAULT NULL,
    `nis_monto`          DECIMAL(10,2) DEFAULT 0.00,
    `nis_descuento`      DECIMAL(10,2) DEFAULT 0.00,
    `nis_monto_final`    DECIMAL(10,2) DEFAULT 0.00,
    `nis_estado`         ENUM('ACTIVA','SUSPENDIDA','FINALIZADA','CANCELADA') DEFAULT 'ACTIVA',
    `nis_notas`          TEXT DEFAULT NULL,
    `nis_created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `nis_updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_nis_alumno_grupo` (`nis_alumno_id`, `nis_grupo_id`),
    INDEX `idx_nis_tenant_estado` (`nis_tenant_id`, `nis_estado`),
    INDEX `idx_nis_grupo` (`nis_grupo_id`),
    CONSTRAINT `fk_nis_tenant` FOREIGN KEY (`nis_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nis_alumno` FOREIGN KEY (`nis_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nis_grupo` FOREIGN KEY (`nis_grupo_id`) REFERENCES `natacion_grupos`(`ngr_grupo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 13. natacion_asistencia — Registro de asistencia
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_asistencia` (
    `nas_asistencia_id` INT AUTO_INCREMENT PRIMARY KEY,
    `nas_tenant_id`     INT NOT NULL,
    `nas_inscripcion_id` INT NOT NULL COMMENT 'FK → natacion_inscripciones',
    `nas_grupo_id`      INT NOT NULL,
    `nas_alumno_id`     INT NOT NULL,
    `nas_fecha`         DATE NOT NULL,
    `nas_estado`        ENUM('PRESENTE','AUSENTE','TARDANZA','JUSTIFICADO') DEFAULT 'PRESENTE',
    `nas_observacion`   VARCHAR(255) DEFAULT NULL,
    `nas_registrado_por` INT DEFAULT NULL COMMENT 'Usuario que registró',
    `nas_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_nas_inscripcion_fecha` (`nas_inscripcion_id`, `nas_fecha`),
    INDEX `idx_nas_grupo_fecha` (`nas_grupo_id`, `nas_fecha`),
    INDEX `idx_nas_alumno` (`nas_alumno_id`, `nas_fecha`),
    CONSTRAINT `fk_nas_tenant` FOREIGN KEY (`nas_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nas_inscripcion` FOREIGN KEY (`nas_inscripcion_id`) REFERENCES `natacion_inscripciones`(`nis_inscripcion_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nas_grupo` FOREIGN KEY (`nas_grupo_id`) REFERENCES `natacion_grupos`(`ngr_grupo_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nas_alumno` FOREIGN KEY (`nas_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 14. natacion_evaluaciones — Evaluación de habilidades
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_evaluaciones` (
    `nev_evaluacion_id` INT AUTO_INCREMENT PRIMARY KEY,
    `nev_tenant_id`     INT NOT NULL,
    `nev_alumno_id`     INT NOT NULL,
    `nev_habilidad_id`  INT NOT NULL COMMENT 'FK → natacion_nivel_habilidades',
    `nev_nivel_id`      INT NOT NULL COMMENT 'FK → natacion_niveles (nivel evaluado)',
    `nev_calificacion`  TINYINT UNSIGNED DEFAULT 0 COMMENT '0-5 estrellas o 0-100',
    `nev_aprobado`      TINYINT(1) DEFAULT 0,
    `nev_fecha`         DATE NOT NULL,
    `nev_evaluador_id`  INT DEFAULT NULL COMMENT 'FK → natacion_instructores',
    `nev_observacion`   TEXT DEFAULT NULL,
    `nev_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_nev_alumno_habilidad_fecha` (`nev_alumno_id`, `nev_habilidad_id`, `nev_fecha`),
    INDEX `idx_nev_alumno` (`nev_alumno_id`, `nev_nivel_id`),
    CONSTRAINT `fk_nev_tenant` FOREIGN KEY (`nev_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nev_alumno` FOREIGN KEY (`nev_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nev_habilidad` FOREIGN KEY (`nev_habilidad_id`) REFERENCES `natacion_nivel_habilidades`(`nnh_habilidad_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nev_nivel` FOREIGN KEY (`nev_nivel_id`) REFERENCES `natacion_niveles`(`nnv_nivel_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 15. natacion_lista_espera — Waitlist por grupo
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_lista_espera` (
    `nle_espera_id`     INT AUTO_INCREMENT PRIMARY KEY,
    `nle_tenant_id`     INT NOT NULL,
    `nle_alumno_id`     INT NOT NULL,
    `nle_grupo_id`      INT NOT NULL,
    `nle_fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `nle_posicion`      INT DEFAULT 0,
    `nle_estado`        ENUM('ESPERANDO','NOTIFICADO','INSCRITO','CANCELADO') DEFAULT 'ESPERANDO',
    `nle_notas`         VARCHAR(255) DEFAULT NULL,
    
    UNIQUE KEY `uk_nle_alumno_grupo` (`nle_alumno_id`, `nle_grupo_id`),
    INDEX `idx_nle_grupo_estado` (`nle_grupo_id`, `nle_estado`, `nle_posicion`),
    CONSTRAINT `fk_nle_tenant` FOREIGN KEY (`nle_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nle_alumno` FOREIGN KEY (`nle_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nle_grupo` FOREIGN KEY (`nle_grupo_id`) REFERENCES `natacion_grupos`(`ngr_grupo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- 16. natacion_pagos — Pagos de inscripciones/mensualidades
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `natacion_pagos` (
    `npg_pago_id`       INT AUTO_INCREMENT PRIMARY KEY,
    `npg_tenant_id`     INT NOT NULL,
    `npg_inscripcion_id` INT DEFAULT NULL,
    `npg_alumno_id`     INT NOT NULL,
    `npg_cliente_id`    INT DEFAULT NULL COMMENT 'FK → clientes (quien paga = representante)',
    `npg_concepto`      VARCHAR(200) NOT NULL COMMENT 'Mensualidad Febrero, Matrícula, etc.',
    `npg_monto`         DECIMAL(10,2) NOT NULL,
    `npg_descuento`     DECIMAL(10,2) DEFAULT 0.00,
    `npg_total`         DECIMAL(10,2) NOT NULL,
    `npg_metodo_pago`   ENUM('EFECTIVO','TARJETA','TRANSFERENCIA','DEPOSITO','ABONO') DEFAULT 'EFECTIVO',
    `npg_referencia`    VARCHAR(100) DEFAULT NULL COMMENT 'Nro transferencia, voucher, etc.',
    `npg_fecha`         DATE NOT NULL,
    `npg_estado`        ENUM('PENDIENTE','PAGADO','ANULADO') DEFAULT 'PENDIENTE',
    `npg_notas`         TEXT DEFAULT NULL,
    `npg_created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `npg_updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_npg_tenant_estado` (`npg_tenant_id`, `npg_estado`),
    INDEX `idx_npg_alumno` (`npg_alumno_id`),
    INDEX `idx_npg_cliente` (`npg_cliente_id`),
    CONSTRAINT `fk_npg_tenant` FOREIGN KEY (`npg_tenant_id`) REFERENCES `seguridad_tenants`(`ten_tenant_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_npg_inscripcion` FOREIGN KEY (`npg_inscripcion_id`) REFERENCES `natacion_inscripciones`(`nis_inscripcion_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_npg_alumno` FOREIGN KEY (`npg_alumno_id`) REFERENCES `alumnos`(`alu_alumno_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_npg_cliente` FOREIGN KEY (`npg_cliente_id`) REFERENCES `clientes`(`cli_cliente_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;


-- ══════════════════════════════════════════════════════════════
-- DATOS INICIALES — Niveles por defecto + Campos ficha ejemplo
-- ══════════════════════════════════════════════════════════════

-- Niveles estándar (tenant_id = 1)
INSERT INTO `natacion_niveles` (`nnv_tenant_id`, `nnv_nombre`, `nnv_codigo`, `nnv_descripcion`, `nnv_color`, `nnv_orden`, `nnv_edad_min`, `nnv_edad_max`) VALUES
(1, 'Adaptación al Agua', 'N0', 'Familiarización con el medio acuático. Pérdida del miedo, flotación asistida.', '#94A3B8', 1, 3, 5),
(1, 'Principiante',       'N1', 'Flotación, patada básica, desplazamiento con tabla, inmersiones cortas.', '#22C55E', 2, 4, 99),
(1, 'Básico',             'N2', 'Crol básico, espalda básica, respiración lateral, zambullidas.', '#3B82F6', 3, 5, 99),
(1, 'Intermedio',         'N3', 'Crol y espalda completos, introducción a pecho, virajes simples.', '#8B5CF6', 4, 6, 99),
(1, 'Avanzado',           'N4', 'Los 4 estilos completos, virajes, salidas desde bloque.', '#F59E0B', 5, 7, 99),
(1, 'Competitivo',        'N5', 'Perfeccionamiento de estilos, entrenamiento de velocidad y resistencia.', '#EF4444', 6, 8, 99);

-- Habilidades del Nivel Principiante (N1)
INSERT INTO `natacion_nivel_habilidades` (`nnh_tenant_id`, `nnh_nivel_id`, `nnh_nombre`, `nnh_descripcion`, `nnh_orden`) VALUES
(1, (SELECT nnv_nivel_id FROM natacion_niveles WHERE nnv_codigo='N1' AND nnv_tenant_id=1), 'Flotación dorsal', 'Flotación boca arriba sin asistencia por 10 segundos', 1),
(1, (SELECT nnv_nivel_id FROM natacion_niveles WHERE nnv_codigo='N1' AND nnv_tenant_id=1), 'Flotación ventral', 'Flotación boca abajo con cara en el agua por 10 segundos', 2),
(1, (SELECT nnv_nivel_id FROM natacion_niveles WHERE nnv_codigo='N1' AND nnv_tenant_id=1), 'Patada con tabla', 'Desplazamiento de 15m con tabla usando patada flutter', 3),
(1, (SELECT nnv_nivel_id FROM natacion_niveles WHERE nnv_codigo='N1' AND nnv_tenant_id=1), 'Inmersión', 'Sumergirse completamente y recoger objeto del fondo', 4),
(1, (SELECT nnv_nivel_id FROM natacion_niveles WHERE nnv_codigo='N1' AND nnv_tenant_id=1), 'Respiración rítmica', 'Inspiración fuera, exhalación dentro del agua (10 repeticiones)', 5);

-- Habilidades del Nivel Básico (N2)
INSERT INTO `natacion_nivel_habilidades` (`nnh_tenant_id`, `nnh_nivel_id`, `nnh_nombre`, `nnh_descripcion`, `nnh_orden`) VALUES
(1, (SELECT nnv_nivel_id FROM natacion_niveles WHERE nnv_codigo='N2' AND nnv_tenant_id=1), 'Crol 25m', 'Nado crol completo 25 metros con respiración lateral', 1),
(1, (SELECT nnv_nivel_id FROM natacion_niveles WHERE nnv_codigo='N2' AND nnv_tenant_id=1), 'Espalda 25m', 'Nado espalda completo 25 metros', 2),
(1, (SELECT nnv_nivel_id FROM natacion_niveles WHERE nnv_codigo='N2' AND nnv_tenant_id=1), 'Respiración bilateral', 'Respiración por ambos lados en crol', 3),
(1, (SELECT nnv_nivel_id FROM natacion_niveles WHERE nnv_codigo='N2' AND nnv_tenant_id=1), 'Zambullida de pie', 'Entrada al agua de pie desde el borde', 4);

-- Campos de ficha personalizables ejemplo (tenant_id = 1)
INSERT INTO `natacion_campos_ficha` (`ncf_tenant_id`, `ncf_clave`, `ncf_etiqueta`, `ncf_tipo`, `ncf_opciones`, `ncf_placeholder`, `ncf_requerido`, `ncf_grupo`, `ncf_orden`) VALUES
(1, 'talla_traje',       'Talla de Traje de Baño',     'SELECT', '["4","6","8","10","12","14","S","M","L","XL"]', NULL, 0, 'personal',  1),
(1, 'usa_gorra',         '¿Usa Gorra de Natación?',    'CHECKBOX', NULL, NULL, 0, 'personal', 2),
(1, 'usa_lentes',        '¿Usa Lentes de Natación?',   'CHECKBOX', NULL, NULL, 0, 'personal', 3),
(1, 'objetivo_natacion', 'Objetivo Principal',          'SELECT', '["Aprender a nadar","Mejorar técnica","Competir","Terapéutico","Recreativo"]', NULL, 1, 'deportivo', 1),
(1, 'como_nos_conocio',  '¿Cómo nos conoció?',         'SELECT', '["Redes sociales","Recomendación","Publicidad","Otro"]', NULL, 0, 'general', 1),
(1, 'autoriza_fotos',    'Autoriza publicación de fotos/videos', 'CHECKBOX', NULL, NULL, 1, 'legal', 1),
(1, 'observaciones_medicas_adicionales', 'Observaciones Médicas Adicionales', 'TEXTAREA', NULL, 'Ingrese cualquier información médica relevante...', 0, 'medico', 1);

-- Configuración por defecto
INSERT INTO `natacion_configuracion` (`ncg_tenant_id`, `ncg_clave`, `ncg_valor`, `ncg_tipo`, `ncg_descripcion`) VALUES
(1, 'nombre_modulo',           'Escuela de Natación',       'TEXT',    'Nombre personalizado del módulo'),
(1, 'moneda',                  'USD',                       'TEXT',    'Moneda para precios'),
(1, 'max_alumnos_carril',      '8',                         'NUMBER',  'Máximo de alumnos por carril'),
(1, 'requiere_certificado_medico', 'true',                  'BOOLEAN', 'Exigir certificado médico'),
(1, 'edad_minima_inscripcion', '3',                         'NUMBER',  'Edad mínima para inscribir'),
(1, 'permite_lista_espera',    'true',                      'BOOLEAN', 'Activar lista de espera'),
(1, 'dias_prueba_gratis',      '0',                         'NUMBER',  'Días de clase de prueba gratis'),
(1, 'porcentaje_asistencia_min','70',                       'NUMBER',  'Porcentaje mínimo de asistencia'),
(1, 'escala_evaluacion',       '5',                         'NUMBER',  'Escala de evaluación (1-5 estrellas o 1-10)');


-- ══════════════════════════════════════════════════════════════
-- MENÚ — Actualizar ítems del módulo natación
-- ══════════════════════════════════════════════════════════════

-- Eliminar menú existente de natación para recrear completo
DELETE FROM seguridad_menu WHERE men_modulo_id = (SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = 'NATACION');

-- Insertar menú completo
SET @nat_mod_id = (SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = 'NATACION');

-- HEADER: Principal
INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@nat_mod_id, NULL, 'HEADER', 'Principal', NULL, NULL, NULL, NULL, 1, 1);
SET @h1 = LAST_INSERT_ID();

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@nat_mod_id, @h1, 'ITEM', 'Dashboard', 'fas fa-tachometer-alt', 'natacion', 'dashboard', 'index', 1, 1),
(@nat_mod_id, @h1, 'ITEM', 'Horario Semanal', 'fas fa-calendar-alt', 'natacion', 'horario', 'index', 2, 1);

-- HEADER: Gestión Académica
INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@nat_mod_id, NULL, 'HEADER', 'Gestión Académica', NULL, NULL, NULL, NULL, 2, 1);
SET @h2 = LAST_INSERT_ID();

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@nat_mod_id, @h2, 'ITEM', 'Alumnos',       'fas fa-user-graduate',      'natacion', 'alumno',       'index', 1, 1),
(@nat_mod_id, @h2, 'ITEM', 'Inscripciones', 'fas fa-clipboard-list',     'natacion', 'inscripcion',  'index', 2, 1),
(@nat_mod_id, @h2, 'ITEM', 'Asistencia',    'fas fa-check-double',       'natacion', 'asistencia',   'index', 3, 1),
(@nat_mod_id, @h2, 'ITEM', 'Evaluaciones',  'fas fa-star-half-alt',      'natacion', 'evaluacion',   'index', 4, 1),
(@nat_mod_id, @h2, 'ITEM', 'Niveles',       'fas fa-layer-group',        'natacion', 'nivel',        'index', 5, 1);

-- HEADER: Infraestructura
INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@nat_mod_id, NULL, 'HEADER', 'Infraestructura', NULL, NULL, NULL, NULL, 3, 1);
SET @h3 = LAST_INSERT_ID();

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@nat_mod_id, @h3, 'ITEM', 'Piscinas',      'fas fa-swimming-pool',     'natacion', 'piscina',      'index', 1, 1),
(@nat_mod_id, @h3, 'ITEM', 'Instructores',  'fas fa-chalkboard-teacher','natacion', 'instructor',   'index', 2, 1),
(@nat_mod_id, @h3, 'ITEM', 'Grupos/Clases', 'fas fa-users-class',       'natacion', 'grupo',        'index', 3, 1),
(@nat_mod_id, @h3, 'ITEM', 'Períodos',      'fas fa-calendar-check',    'natacion', 'periodo',      'index', 4, 1);

-- HEADER: Financiero
INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@nat_mod_id, NULL, 'HEADER', 'Financiero', NULL, NULL, NULL, NULL, 4, 1);
SET @h4 = LAST_INSERT_ID();

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@nat_mod_id, @h4, 'ITEM', 'Pagos',         'fas fa-money-bill-wave',   'natacion', 'pago',         'index', 1, 1),
(@nat_mod_id, @h4, 'ITEM', 'Reportes',      'fas fa-chart-bar',         'natacion', 'reporte',      'index', 2, 1);

-- HEADER: Configuración
INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@nat_mod_id, NULL, 'HEADER', 'Configuración', NULL, NULL, NULL, NULL, 5, 1);
SET @h5 = LAST_INSERT_ID();

INSERT INTO seguridad_menu (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, men_ruta_modulo, men_ruta_controller, men_ruta_action, men_orden, men_activo) VALUES
(@nat_mod_id, @h5, 'ITEM', 'Campos de Ficha',  'fas fa-sliders-h',     'natacion', 'campoficha',   'index', 1, 1),
(@nat_mod_id, @h5, 'ITEM', 'Configuración',     'fas fa-cog',          'natacion', 'configuracion','index', 2, 1);
