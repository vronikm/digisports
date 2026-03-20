-- ============================================================
-- Migration 012: Tabla de Notificaciones del Sistema
-- Usada por BaseController::getUnreadNotifications()
-- Ejecutar en: digisports_core
-- ============================================================

CREATE TABLE IF NOT EXISTS `notificaciones` (
  `notificacion_id`   int            NOT NULL AUTO_INCREMENT,
  `usuario_id`        int            NOT NULL            COMMENT 'Usuario destinatario',
  `tenant_id`         int            DEFAULT NULL        COMMENT 'Tenant del usuario (NULL = global)',
  `tipo`              varchar(50)    COLLATE utf8mb4_unicode_ci DEFAULT 'INFO'
                          COMMENT 'Tipo: INFO, WARNING, ERROR, SUCCESS',
  `titulo`            varchar(150)   COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje`           text           COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_accion`        varchar(500)   COLLATE utf8mb4_unicode_ci DEFAULT NULL
                          COMMENT 'URL de destino al hacer clic',
  `icono`             varchar(80)    COLLATE utf8mb4_unicode_ci DEFAULT 'fas fa-bell'
                          COMMENT 'Clase Font Awesome',
  `color`             varchar(20)    COLLATE utf8mb4_unicode_ci DEFAULT '#3B82F6'
                          COMMENT 'Color hex del icono',
  `leida`             enum('S','N')  COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `fecha_creacion`    timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_lectura`     timestamp      NULL DEFAULT NULL,
  `fecha_expiracion`  timestamp      NULL DEFAULT NULL
                          COMMENT 'NULL = no expira',
  PRIMARY KEY (`notificacion_id`),
  KEY `idx_noti_usuario`        (`usuario_id`),
  KEY `idx_noti_usuario_leida`  (`usuario_id`, `leida`),
  KEY `idx_noti_tenant`         (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Notificaciones del sistema por usuario';
