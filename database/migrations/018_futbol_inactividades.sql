-- ============================================================
-- Migración 018: tabla futbol_inactividades (licencias/ausencias)
-- ============================================================

CREATE TABLE IF NOT EXISTS `futbol_inactividades` (
  `fin_id`          int NOT NULL AUTO_INCREMENT,
  `fin_tenant_id`   int NOT NULL,
  `fin_alumno_id`   int NOT NULL,
  `fin_tipo`        enum('VACACIONES','VIAJE','ENFERMEDAD','ECONOMICO','OTRO')
                    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OTRO',
  `fin_fecha_desde` date NOT NULL,
  `fin_fecha_hasta` date DEFAULT NULL COMMENT 'NULL = sin fecha fin definida',
  `fin_motivo`      varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fin_usuario_id`  int DEFAULT NULL COMMENT 'Usuario que registró la inactividad',
  `fin_created_at`  timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fin_id`),
  KEY `idx_fin_alumno`  (`fin_tenant_id`, `fin_alumno_id`),
  KEY `idx_fin_fechas`  (`fin_tenant_id`, `fin_fecha_desde`, `fin_fecha_hasta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
