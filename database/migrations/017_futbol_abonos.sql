-- ============================================================
-- Migración 017: tabla futbol_abonos (pagos parciales)
-- ============================================================

CREATE TABLE IF NOT EXISTS `futbol_abonos` (
  `fab_abono_id`    int NOT NULL AUTO_INCREMENT,
  `fab_tenant_id`   int NOT NULL,
  `fab_pago_id`     int NOT NULL COMMENT 'FK → futbol_pagos',
  `fab_alumno_id`   int NOT NULL,
  `fab_monto`       decimal(10,2) NOT NULL COMMENT 'Monto de este abono',
  `fab_metodo_pago` enum('EFECTIVO','TRANSFERENCIA','DEPOSITO','TARJETA','CHEQUE','PAYPHONE','OTRO')
                    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'EFECTIVO',
  `fab_referencia`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fab_fecha`       date NOT NULL,
  `fab_notas`       text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fab_usuario_id`  int DEFAULT NULL COMMENT 'Usuario que registró el abono',
  `fab_created_at`  timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fab_abono_id`),
  KEY `idx_fab_pago`   (`fab_pago_id`),
  KEY `idx_fab_alumno` (`fab_tenant_id`, `fab_alumno_id`),
  KEY `idx_fab_fecha`  (`fab_tenant_id`, `fab_fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
