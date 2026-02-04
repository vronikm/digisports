-- Tabla para registrar logs de notificaciones masivas de correo
CREATE TABLE IF NOT EXISTS notificaciones_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    tenant_id INT NULL,
    destinatario_email VARCHAR(255) NOT NULL,
    tipo_notificacion VARCHAR(50) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    enviado TINYINT(1) DEFAULT 0,
    error TEXT NULL,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (usuario_id),
    INDEX (tenant_id),
    INDEX (destinatario_email(100))
);