-- ============================================================
-- DigiSports Arena — Fase 1: Vistas de compatibilidad
-- Resuelve el desajuste entre nombres de tabla sin prefijo
-- (usados en controladores) y las tablas reales con prefijo.
-- Ejecutar: mysql -u root digisports_core < database/fase1_vistas_compatibilidad.sql
-- ============================================================

-- Vista: canchas → instalaciones_canchas
DROP VIEW IF EXISTS canchas;
CREATE VIEW canchas AS
SELECT
    can_cancha_id        AS cancha_id,
    can_tenant_id        AS tenant_id,
    can_instalacion_id   AS instalacion_id,
    can_nombre           AS nombre,
    can_tipo             AS tipo,
    can_descripcion      AS descripcion,
    can_capacidad_maxima AS capacidad_maxima,
    can_ancho            AS ancho,
    can_largo            AS largo,
    can_estado           AS estado,
    can_fecha_creacion   AS fecha_creacion,
    can_fecha_actualizacion AS fecha_actualizacion,
    can_usuario_creacion AS usuario_creacion,
    can_usuario_actualizacion AS usuario_actualizacion
FROM instalaciones_canchas;

-- Vista: tarifas → seguridad_tarifas
DROP VIEW IF EXISTS tarifas;
CREATE VIEW tarifas AS
SELECT
    tar_tarifa_id     AS tarifa_id,
    tar_cancha_id     AS cancha_id,
    tar_dia_semana    AS dia_semana,
    tar_hora_inicio   AS hora_inicio,
    tar_hora_fin      AS hora_fin,
    tar_precio        AS precio,
    tar_estado        AS estado,
    tar_fecha_creacion AS fecha_creacion,
    tar_fecha_actualizacion AS fecha_actualizacion
FROM seguridad_tarifas;

-- Vista: reservas → instalaciones_reservas
DROP VIEW IF EXISTS reservas;
CREATE VIEW reservas AS
SELECT
    res_reserva_id          AS reserva_id,
    res_tenant_id           AS tenant_id,
    res_instalacion_id      AS instalacion_id,
    res_cliente_id          AS cliente_id,
    res_fecha_reserva       AS fecha_reserva,
    res_hora_inicio         AS hora_inicio,
    res_hora_fin            AS hora_fin,
    res_duracion_minutos    AS duracion_minutos,
    res_es_recurrente       AS es_recurrente,
    res_reserva_padre_id    AS reserva_padre_id,
    res_recurrencia_config  AS recurrencia_config,
    res_tarifa_aplicada_id  AS tarifa_aplicada_id,
    res_precio_base         AS precio_base,
    res_descuento_monto     AS descuento_monto,
    res_precio_total        AS precio_total,
    res_precio_total        AS total,
    res_abono_utilizado     AS abono_utilizado,
    res_estado              AS estado,
    res_requiere_confirmacion AS requiere_confirmacion,
    res_fecha_confirmacion  AS fecha_confirmacion,
    res_observaciones       AS observaciones,
    res_fecha_registro      AS fecha_registro,
    res_usuario_registro    AS usuario_registro
FROM instalaciones_reservas;

-- Vista: mantenimientos → instalaciones_mantenimientos
DROP VIEW IF EXISTS mantenimientos;
CREATE VIEW mantenimientos AS
SELECT
    man_mantenimiento_id AS mantenimiento_id,
    man_tenant_id        AS tenant_id,
    man_cancha_id        AS cancha_id,
    man_tipo             AS tipo,
    man_tipo             AS tipo_bloqueo,
    man_descripcion      AS descripcion,
    man_notas            AS notas,
    man_fecha_inicio     AS fecha_inicio,
    man_fecha_fin        AS fecha_fin,
    man_responsable_id   AS responsable_id,
    man_recurrir         AS recurrir,
    man_cadencia_recurrencia AS cadencia_recurrencia,
    man_estado           AS estado,
    man_fecha_creacion   AS fecha_creacion,
    man_fecha_actualizacion AS fecha_actualizacion,
    man_usuario_creacion AS usuario_creacion,
    man_usuario_actualizacion AS usuario_actualizacion
FROM instalaciones_mantenimientos;

-- Vista: abonos → instalaciones_abonos
DROP VIEW IF EXISTS abonos;
CREATE VIEW abonos AS
SELECT
    abo_abono_id         AS abono_id,
    abo_tenant_id        AS tenant_id,
    abo_cliente_id       AS cliente_id,
    abo_monto_total      AS monto_total,
    abo_monto_utilizado  AS monto_utilizado,
    abo_saldo_disponible AS saldo_disponible,
    abo_fecha_compra     AS fecha_compra,
    abo_fecha_vencimiento AS fecha_vencimiento,
    abo_forma_pago       AS forma_pago,
    abo_estado           AS estado,
    abo_fecha_registro   AS fecha_registro
FROM instalaciones_abonos;

-- Vista: reserva_pagos → instalaciones_reserva_pagos
DROP VIEW IF EXISTS reserva_pagos;
CREATE VIEW reserva_pagos AS
SELECT
    pag_pago_id         AS pago_id,
    pag_tenant_id       AS tenant_id,
    pag_reserva_id      AS reserva_id,
    pag_monto           AS monto,
    pag_tipo_pago       AS tipo_pago,
    pag_forma_pago      AS forma_pago,
    pag_referencia      AS referencia,
    pag_pasarela        AS pasarela,
    pag_transaction_id  AS transaction_id,
    pag_estado          AS estado,
    pag_fecha_pago      AS fecha_pago,
    pag_usuario_registro AS usuario_registro
FROM instalaciones_reserva_pagos;

-- ============================================================
-- Tabla nueva: movimientos de monedero (historial detallado)
-- ============================================================
CREATE TABLE IF NOT EXISTS instalaciones_abono_movimientos (
    mov_movimiento_id    INT AUTO_INCREMENT PRIMARY KEY,
    mov_tenant_id        INT NOT NULL,
    mov_abono_id         INT NOT NULL,
    mov_cliente_id       INT NOT NULL,
    mov_tipo             VARCHAR(20) NOT NULL COMMENT 'RECARGA, CONSUMO, DEVOLUCION, AJUSTE, VENCIMIENTO',
    mov_monto            DECIMAL(10,2) NOT NULL,
    mov_saldo_anterior   DECIMAL(10,2) NOT NULL,
    mov_saldo_posterior  DECIMAL(10,2) NOT NULL,
    mov_descripcion      VARCHAR(255) DEFAULT NULL,
    mov_referencia_tipo  VARCHAR(50)  DEFAULT NULL COMMENT 'RESERVA, PAGO, MANUAL',
    mov_referencia_id    INT          DEFAULT NULL,
    mov_forma_pago       VARCHAR(50)  DEFAULT NULL,
    mov_usuario_registro INT          DEFAULT NULL,
    mov_fecha_registro   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant  (mov_tenant_id),
    INDEX idx_cliente (mov_cliente_id),
    INDEX idx_abono   (mov_abono_id),
    INDEX idx_fecha   (mov_fecha_registro),
    FOREIGN KEY (mov_tenant_id)  REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (mov_abono_id)   REFERENCES instalaciones_abonos(abo_abono_id) ON DELETE CASCADE,
    FOREIGN KEY (mov_cliente_id) REFERENCES clientes(cli_cliente_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Historial de movimientos del monedero/abonos';

-- Vista alias para los movimientos
DROP VIEW IF EXISTS abono_movimientos;
CREATE VIEW abono_movimientos AS
SELECT
    mov_movimiento_id   AS movimiento_id,
    mov_tenant_id       AS tenant_id,
    mov_abono_id        AS abono_id,
    mov_cliente_id      AS cliente_id,
    mov_tipo            AS tipo,
    mov_monto           AS monto,
    mov_saldo_anterior  AS saldo_anterior,
    mov_saldo_posterior AS saldo_posterior,
    mov_descripcion     AS descripcion,
    mov_referencia_tipo AS referencia_tipo,
    mov_referencia_id   AS referencia_id,
    mov_forma_pago      AS forma_pago,
    mov_usuario_registro AS usuario_registro,
    mov_fecha_registro  AS fecha_registro
FROM instalaciones_abono_movimientos;

-- ============================================================
-- Tabla nueva: paquetes/bonos (compra 10 horas paga 8)
-- ============================================================
CREATE TABLE IF NOT EXISTS instalaciones_paquetes (
    paq_paquete_id       INT AUTO_INCREMENT PRIMARY KEY,
    paq_tenant_id        INT NOT NULL,
    paq_nombre           VARCHAR(100) NOT NULL,
    paq_descripcion      TEXT,
    paq_horas_incluidas  INT NOT NULL DEFAULT 10,
    paq_precio_normal    DECIMAL(10,2) NOT NULL COMMENT 'Precio sin descuento',
    paq_precio_paquete   DECIMAL(10,2) NOT NULL COMMENT 'Precio con descuento',
    paq_descuento_pct    DECIMAL(5,2)  DEFAULT 0,
    paq_dias_vigencia    INT NOT NULL DEFAULT 90,
    paq_estado           VARCHAR(20) DEFAULT 'ACTIVO',
    paq_fecha_creacion   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (paq_tenant_id),
    FOREIGN KEY (paq_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Paquetes/bonos de horas prepagadas';
