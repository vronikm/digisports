-- =====================================================
-- DigiSports - Migración a estándar de nombres de tablas y campos
-- Fecha: 2026-02-04
-- NOTA: Este script es una propuesta. Revisa y ajusta antes de ejecutar en producción.
-- =====================================================

-- Ejemplo de conversión para tablas principales (core y seguridad)
RENAME	TABLA	PARA	ESQUEMA		CONSULTA
RENAME TABLE	abonos	TO	instalaciones	 | 	RENAME TABLE abonos TO instalaciones_abonos;
RENAME TABLE	auditoria	TO	seguridad	 | 	RENAME TABLE auditoria TO seguridad_auditoria;
RENAME TABLE	auditoria_logs	TO	seguridad	 | 	RENAME TABLE auditoria_logs TO seguridad_auditoria_logs;
RENAME TABLE	canchas	TO	instalaciones	 | 	RENAME TABLE canchas TO instalaciones_canchas;
RENAME TABLE	configuracion_sistema	TO	seguridad	 | 	RENAME TABLE configuracion_sistema TO seguridad_configuracion_sistema;
RENAME TABLE	disponibilidad_canchas	TO	instalaciones	 | 	RENAME TABLE disponibilidad_canchas TO instalaciones_disponibilidad_canchas;
RENAME TABLE	eventos_canchas	TO	instalaciones	 | 	RENAME TABLE eventos_canchas TO instalaciones_eventos_canchas;
RENAME TABLE	instalacion_bloqueos	TO	instalaciones	 | 	RENAME TABLE instalacion_bloqueos TO instalaciones_instalacion_bloqueos;
RENAME TABLE	instalacion_horarios	TO	instalaciones	 | 	RENAME TABLE instalacion_horarios TO instalaciones_instalacion_horarios;
RENAME TABLE	instalacion_tarifas	TO	instalaciones	 | 	RENAME TABLE instalacion_tarifas TO instalaciones_instalacion_tarifas;
RENAME TABLE	log_accesos	TO	seguridad	 | 	RENAME TABLE log_accesos TO seguridad_log_accesos;
RENAME TABLE	mantenimientos	TO	instalaciones	 | 	RENAME TABLE mantenimientos TO instalaciones_mantenimientos;
RENAME TABLE	menu_config	TO	seguridad	 | 	RENAME TABLE menu_config TO seguridad_menu_config;
RENAME TABLE	modulos	TO	seguridad	 | 	RENAME TABLE modulos TO seguridad_modulos;
RENAME TABLE	modulos_sistema	TO	seguridad	 | 	RENAME TABLE modulos_sistema TO seguridad_modulos_sistema;
RENAME TABLE	notificaciones	TO	seguridad	 | 	RENAME TABLE notificaciones TO seguridad_notificaciones;
RENAME TABLE	notificaciones_log	TO	seguridad	 | 	RENAME TABLE notificaciones_log TO seguridad_notificaciones_log;
RENAME TABLE	planes_suscripcion	TO	seguridad	 | 	RENAME TABLE planes_suscripcion TO seguridad_planes_suscripcion;
RENAME TABLE	reserva_pagos	TO	instalaciones	 | 	RENAME TABLE reserva_pagos TO instalaciones_reserva_pagos;
RENAME TABLE	reservas	TO	instalaciones	 | 	RENAME TABLE reservas TO instalaciones_reservas;
RENAME TABLE	rol_modulos	TO	seguridad	 | 	RENAME TABLE rol_modulos TO seguridad_rol_modulos;
RENAME TABLE	roles	TO	seguridad	 | 	RENAME TABLE roles TO seguridad_roles;
RENAME TABLE	sedes	TO	instalaciones	 | 	RENAME TABLE sedes TO instalaciones_sedes;
RENAME TABLE	tarifas	TO	seguridad	 | 	RENAME TABLE tarifas TO seguridad_tarifas;
RENAME TABLE	tenant_configuraciones	TO	seguridad	 | 	RENAME TABLE tenant_configuraciones TO seguridad_tenant_configuraciones;
RENAME TABLE	tenant_modulos	TO	seguridad	 | 	RENAME TABLE tenant_modulos TO seguridad_tenant_modulos;
RENAME TABLE	tenants	TO	seguridad	 | 	RENAME TABLE tenants TO seguridad_tenants;
RENAME TABLE	tipos_instalacion	TO	instalaciones	 | 	RENAME TABLE tipos_instalacion TO instalaciones_tipos_instalacion;
RENAME TABLE	usuarios	TO	seguridad	 | 	RENAME TABLE usuarios TO seguridad_usuarios;

-- =============================
-- Tablas
ALTER TABLE facturas_electronicas 
CHANGE id fac_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id fac_tenant_id INT NOT NULL,
CHANGE factura_id fac_factura_id INT,
CHANGE clave_acceso fac_clave_acceso VARCHAR(49) NOT NULL,
CHANGE tipo_comprobante fac_tipo_comprobante CHAR(2) NOT NULL DEFAULT '01'
    COMMENT '01=Factura, 04=Nota Crédito, 05=Nota Débito, 06=Guía Remisión, 07=Retención',
CHANGE establecimiento fac_establecimiento CHAR(3) NOT NULL,
CHANGE punto_emision fac_punto_emision CHAR(3) NOT NULL,
CHANGE secuencial fac_secuencial CHAR(9) NOT NULL,
CHANGE fecha_emision fac_fecha_emision DATE NOT NULL,
CHANGE cliente_id fac_cliente_id INT,
CHANGE cliente_tipo_identificacion fac_cliente_tipo_identificacion CHAR(2) NOT NULL
    COMMENT '04=RUC, 05=Cédula, 06=Pasaporte, 07=Cons.Final',
CHANGE cliente_identificacion fac_cliente_identificacion VARCHAR(20) NOT NULL,
CHANGE cliente_razon_social fac_cliente_razon_social VARCHAR(300) NOT NULL,
CHANGE cliente_direccion fac_cliente_direccion VARCHAR(300),
CHANGE cliente_email fac_cliente_email VARCHAR(200),
CHANGE cliente_telefono fac_cliente_telefono VARCHAR(50),
CHANGE subtotal_iva fac_subtotal_iva DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal con IVA',
CHANGE subtotal_0 fac_subtotal_0 DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal 0%',
CHANGE subtotal_no_objeto fac_subtotal_no_objeto DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal no objeto de IVA',
CHANGE subtotal_exento fac_subtotal_exento DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal exento',
CHANGE subtotal fac_subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
CHANGE descuento fac_descuento DECIMAL(14,2) NOT NULL DEFAULT 0.00,
CHANGE iva fac_iva DECIMAL(14,2) NOT NULL DEFAULT 0.00,
CHANGE ice fac_ice DECIMAL(14,2) NOT NULL DEFAULT 0.00,
CHANGE irbpnr fac_irbpnr DECIMAL(14,2) NOT NULL DEFAULT 0.00,
CHANGE propina fac_propina DECIMAL(14,2) NOT NULL DEFAULT 0.00,
CHANGE total fac_total DECIMAL(14,2) NOT NULL DEFAULT 0.00,
CHANGE estado_sri fac_estado_sri ENUM(
    'PENDIENTE','GENERADA','FIRMADA','ENVIADA','RECIBIDA',
    'DEVUELTA','AUTORIZADO','NO_AUTORIZADO','ERROR','ANULADA'
) NOT NULL DEFAULT 'PENDIENTE',
CHANGE ambiente fac_ambiente CHAR(1) NOT NULL DEFAULT '1'
    COMMENT '1=Pruebas, 2=Producción',
CHANGE tipo_emision fac_tipo_emision CHAR(1) NOT NULL DEFAULT '1'
    COMMENT '1=Normal, 2=Contingencia',
CHANGE xml_generado fac_xml_generado TEXT COMMENT 'Ruta al archivo XML generado',
CHANGE xml_firmado fac_xml_firmado TEXT COMMENT 'Ruta al archivo XML firmado',
CHANGE xml_autorizado fac_xml_autorizado TEXT COMMENT 'Ruta al archivo XML autorizado',
CHANGE numero_autorizacion fac_numero_autorizacion VARCHAR(49),
CHANGE fecha_autorizacion fac_fecha_autorizacion DATETIME,
CHANGE mensaje_error fac_mensaje_error TEXT,
CHANGE intentos_envio fac_intentos_envio INT NOT NULL DEFAULT 0,
CHANGE ultimo_intento fac_ultimo_intento DATETIME,
CHANGE observaciones fac_observaciones TEXT,
CHANGE created_at fac_created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
CHANGE updated_at fac_updated_at TIMESTAMP 
    DEFAULT CURRENT_TIMESTAMP 
    ON UPDATE CURRENT_TIMESTAMP;


-- ---------------------------------------------------------------------------
ALTER TABLE facturas_electronicas_detalle 
CHANGE id det_id INT NOT NULL AUTO_INCREMENT,
CHANGE factura_electronica_id det_factura_electronica_id INT NOT NULL,
CHANGE codigo_principal det_codigo_principal VARCHAR(25) COMMENT 'Código interno',
CHANGE codigo_auxiliar det_codigo_auxiliar VARCHAR(25) COMMENT 'Código barras, etc.',
CHANGE descripcion det_descripcion VARCHAR(300) NOT NULL,
CHANGE cantidad det_cantidad DECIMAL(14,6) NOT NULL,
CHANGE precio_unitario det_precio_unitario DECIMAL(14,6) NOT NULL,
CHANGE descuento det_descuento DECIMAL(14,2) DEFAULT 0.00 NOT NULL,
CHANGE precio_total_sin_impuesto det_precio_total_sin_impuesto DECIMAL(14,2) NOT NULL,
CHANGE producto_id det_producto_id INT,
CHANGE servicio_id det_servicio_id INT,
CHANGE instalacion_id det_instalacion_id INT,
CHANGE reserva_id det_reserva_id INT,
CHANGE created_at det_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;

-- ---------------------------------------------------------------------------
ALTER TABLE facturas_electronicas_detalle_impuestos 
CHANGE id imp_id INT NOT NULL AUTO_INCREMENT,
CHANGE detalle_id imp_detalle_id INT NOT NULL,
CHANGE codigo imp_codigo CHAR(1) NOT NULL COMMENT '2=IVA, 3=ICE, 5=IRBPNR',
CHANGE codigo_porcentaje imp_codigo_porcentaje CHAR(4) NOT NULL COMMENT 'Código tarifa: 0, 2, 3, 4, 6, 7, 8',
CHANGE tarifa imp_tarifa DECIMAL(5,2) NOT NULL COMMENT 'Porcentaje: 0, 12, 14, 15, etc.',
CHANGE base_imponible imp_base_imponible DECIMAL(14,2) NOT NULL,
CHANGE valor imp_valor DECIMAL(14,2) NOT NULL;
-- -------------------------------------------------------------------------
ALTER TABLE facturas_electronicas_info_adicional 
CHANGE id adi_id INT NOT NULL AUTO_INCREMENT,
CHANGE factura_electronica_id adi_factura_electronica_id INT NOT NULL,
CHANGE nombre adi_nombre VARCHAR(300) NOT NULL,
CHANGE valor adi_valor VARCHAR(300) NOT NULL;
-- =============================
ALTER TABLE facturas_electronicas_log 
CHANGE id log_id INT NOT NULL AUTO_INCREMENT,
CHANGE factura_electronica_id log_factura_electronica_id INT,
CHANGE clave_acceso log_clave_acceso VARCHAR(49),
CHANGE accion log_accion enum('GENERAR','FIRMAR','ENVIAR','CONSULTAR','REENVIAR','ANULAR') NOT NULL,
CHANGE endpoint log_endpoint VARCHAR(500),
CHANGE request_data log_request_data LONGTEXT,
CHANGE response_data log_response_data LONGTEXT,
CHANGE estado_respuesta log_estado_respuesta VARCHAR(50),
CHANGE codigo_error log_codigo_error VARCHAR(10),
CHANGE mensaje_error log_mensaje_error text,
CHANGE duracion_ms log_duracion_ms INT COMMENT 'Tiempo de respuesta en milisegundos',
CHANGE ip_origen log_ip_origen VARCHAR(45),
CHANGE created_at log_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE created_by log_created_by INT COMMENT 'Referencia a usuarios.usuario_id';
-- =============================
ALTER TABLE  facturas_electronicas_pagos 
CHANGE id pag_id INT NOT NULL AUTO_INCREMENT,
CHANGE inc_factura_electronica_id pag_factura_electronica_id INT NOT NULL,
CHANGE inc_forma_pago pag_forma_pago CHAR(2) NOT NULL COMMENT '01=Efectivo, 16=Tarjeta Débito, etc.',
CHANGE inc_total pag_total DECIMAL(14,2) NOT NULL,
CHANGE inc_plazo pag_plazo INT COMMENT 'Plazo en días/meses',
CHANGE inc_unidad_tiempo pag_unidad_tiempo VARCHAR(20) DEFAULT 'dias';
-- =============================
ALTER TABLE facturas_electronicas_secuenciales 
CHANGE id sec_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id sec_tenant_id INT NOT NULL,
CHANGE tipo_comprobante sec_tipo_comprobante CHAR(2) DEFAULT '01' NOT NULL,
CHANGE establecimiento sec_establecimiento CHAR(3) NOT NULL,
CHANGE punto_emision sec_punto_emision CHAR(3) NOT NULL,
CHANGE secuencial_actual sec_secuencial_actual INT DEFAULT 0 NOT NULL,
CHANGE created_at sec_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE updated_at sec_updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
-- =============================
ALTER TABLE facturas_suscripcion 
CHANGE factura_id sus_factura_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id sus_tenant_id INT NOT NULL,
CHANGE periodo sus_periodo VARCHAR(7) NOT NULL,
CHANGE tipo_factura sus_tipo_factura VARCHAR(20) DEFAULT 'MENSUAL',
CHANGE subtotal sus_subtotal DECIMAL(10,2) NOT NULL,
CHANGE descuento sus_descuento DECIMAL(10,2) DEFAULT 0.00,
CHANGE iva sus_iva DECIMAL(10,2) NOT NULL,
CHANGE total sus_total DECIMAL(10,2) NOT NULL,
CHANGE plan_nombre sus_plan_nombre VARCHAR(100),
CHANGE usuarios_cobrados sus_usuarios_cobrados INT,
CHANGE sedes_cobradas sus_sedes_cobradas INT,
CHANGE modulos_adicionales sus_modulos_adicionales json,
CHANGE fecha_emision sus_fecha_emision DATE NOT NULL,
CHANGE fecha_vencimiento sus_fecha_vencimiento DATE NOT NULL,
CHANGE fecha_pago sus_fecha_pago DATE,
CHANGE metodo_pago sus_metodo_pago VARCHAR(50),
CHANGE referencia_pago sus_referencia_pago VARCHAR(100),
CHANGE comprobante_pago sus_comprobante_pago VARCHAR(200),
CHANGE numero_autorizacion sus_numero_autorizacion VARCHAR(49),
CHANGE clave_acceso sus_clave_acceso VARCHAR(49),
CHANGE xml_firmado sus_xml_firmado text,
CHANGE estado sus_estado VARCHAR(20) DEFAULT 'PENDIENTE',
CHANGE fecha_registro sus_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;

-- =============================
ALTER TABLE instalaciones 
CHANGE instalacion_id ins_instalacion_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id ins_tenant_id INT NOT NULL,
CHANGE sede_id ins_sede_id INT NOT NULL,
CHANGE tipo_instalacion_id ins_tipo_instalacion_id INT NOT NULL,
CHANGE codigo ins_codigo VARCHAR(50) NOT NULL,
CHANGE nombre ins_nombre VARCHAR(100) NOT NULL,
CHANGE descripcion ins_descripcion text,
CHANGE superficie ins_superficie VARCHAR(50),
CHANGE dimensiones ins_dimensiones VARCHAR(50),
CHANGE capacidad_personas ins_capacidad_personas INT,
CHANGE tiene_iluminacion ins_tiene_iluminacion CHAR(1) DEFAULT 'S',
CHANGE tiene_graderias ins_tiene_graderias CHAR(1) DEFAULT 'N',
CHANGE tiene_vestuarios ins_tiene_vestuarios CHAR(1) DEFAULT 'N',
CHANGE tiene_duchas ins_tiene_duchas CHAR(1) DEFAULT 'N',
CHANGE duracion_minima_minutos ins_duracion_minima_minutos INT DEFAULT 60,
CHANGE duracion_maxima_minutos ins_duracion_maxima_minutos INT DEFAULT 120,
CHANGE tiempo_anticipacion_dias ins_tiempo_anticipacion_dias INT DEFAULT 30,
CHANGE permite_reserva_recurrente ins_permite_reserva_recurrente CHAR(1) DEFAULT 'S',
CHANGE foto_principal ins_foto_principal VARCHAR(200),
CHANGE galeria_fotos ins_galeria_fotos json,
CHANGE estado ins_estado VARCHAR(20) DEFAULT 'ACTIVO',
CHANGE motivo_inactivacion ins_motivo_inactivacion text,
CHANGE fecha_inicio_inactivacion ins_fecha_inicio_inactivacion DATETIME,
CHANGE fecha_fin_inactivacion ins_fecha_fin_inactivacion DATETIME,
CHANGE orden_visualizacion ins_orden_visualizacion INT DEFAULT 0,
CHANGE fecha_registro ins_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE fecha_actualizacion ins_fecha_actualizacion timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
CHANGE usuario_registro ins_usuario_registro INT;
-- =============================
ALTER TABLE instalaciones_abonos 
CHANGE abono_id abo_abono_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id abo_tenant_id INT NOT NULL,
CHANGE cliente_id abo_cliente_id INT NOT NULL,
CHANGE monto_total abo_monto_total DECIMAL(10,2) NOT NULL,
CHANGE monto_utilizado abo_monto_utilizado DECIMAL(10,2) DEFAULT 0.00,
CHANGE saldo_disponible abo_saldo_disponible DECIMAL(10,2) NOT NULL,
CHANGE fecha_compra abo_fecha_compra DATE NOT NULL,
CHANGE fecha_vencimiento abo_fecha_vencimiento DATE NOT NULL,
CHANGE forma_pago abo_forma_pago VARCHAR(50),
CHANGE estado abo_estado VARCHAR(20) DEFAULT 'ACTIVO',
CHANGE fecha_registro abo_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;

-- =============================
ALTER TABLE instalaciones_canchas 
CHANGE cancha_id can_cancha_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id can_tenant_id INT NOT NULL,
CHANGE instalacion_id can_instalacion_id INT NOT NULL,
CHANGE nombre can_nombre VARCHAR(100) NOT NULL,
CHANGE tipo can_tipo VARCHAR(50) NOT NULL COMMENT 'futbol, tenis, padel, voleibol, basquetbol, piscina, gimnasio, otro',
CHANGE descripcion can_descripcion text,
CHANGE capacidad_maxima can_capacidad_maxima INT DEFAULT 0 NOT NULL,
CHANGE ancho can_ancho DECIMAL(8,2) COMMENT 'Ancho en metros',
CHANGE largo can_largo DECIMAL(8,2) COMMENT 'Largo en metros',
CHANGE estado can_estado VARCHAR(20) DEFAULT 'ACTIVO' COMMENT 'ACTIVO, INACTIVO, ELIMINADA',
CHANGE fecha_creacion can_fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE fecha_actualizacion can_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL,
CHANGE usuario_creacion can_usuario_creacion INT,
CHANGE usuario_actualizacion can_usuario_actualizacion INT;

-- =============================
ALTER TABLE instalaciones_disponibilidad_canchas 
CHANGE disponibilidad_id dis_disponibilidad_id INT NOT NULL AUTO_INCREMENT,
CHANGE cancha_id dis_cancha_id INT NOT NULL,
CHANGE fecha dis_fecha DATE NOT NULL,
CHANGE hora_inicio dis_hora_inicio TIME NOT NULL,
CHANGE hora_fin dis_hora_fin TIME NOT NULL,
CHANGE disponible dis_disponible CHAR(1) DEFAULT 'S' COMMENT 'S=Disponible, N=No disponible',
CHANGE motivo dis_motivo VARCHAR(255) COMMENT 'Mantenimiento, Reservada, Evento, etc',
CHANGE fecha_creacion dis_fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE instalaciones_eventos_canchas 
CHANGE evento_id eve_evento_id INT NOT NULL AUTO_INCREMENT,
CHANGE cancha_id eve_cancha_id INT NOT NULL,
CHANGE tipo_evento eve_tipo_evento VARCHAR(50) NOT NULL COMMENT 'MANTENIMIENTO, RESERVA, EVENTO, BLOQUEO, ESTADO_CAMBIO',
CHANGE descripcion eve_descripcion text,
CHANGE referencia_id eve_referencia_id INT COMMENT 'ID de mantenimiento, reserva, etc',
CHANGE usuario_id eve_usuario_id INT,
CHANGE fecha_evento eve_fecha_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE instalaciones_instalacion_bloqueos 
CHANGE bloqueo_id blo_bloqueo_id INT NOT NULL AUTO_INCREMENT,
CHANGE instalacion_id blo_instalacion_id INT NOT NULL,
CHANGE tipo_bloqueo blo_tipo_bloqueo VARCHAR(50) NOT NULL,
CHANGE fecha_inicio blo_fecha_inicio DATETIME NOT NULL,
CHANGE fecha_fin blo_fecha_fin DATETIME NOT NULL,
CHANGE motivo blo_motivo text NOT NULL,
CHANGE es_recurrente blo_es_recurrente CHAR(1) DEFAULT 'N',
CHANGE recurrencia_config blo_recurrencia_config json,
CHANGE usuario_registro blo_usuario_registro INT,
CHANGE fecha_registro blo_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE instalaciones_instalacion_horarios 
CHANGE horario_id hor_horario_id INT NOT NULL AUTO_INCREMENT,
CHANGE instalacion_id hor_instalacion_id INT NOT NULL,
CHANGE dia_semana hor_dia_semana TINYINT NOT NULL,
CHANGE hora_apertura hor_hora_apertura TIME NOT NULL,
CHANGE hora_cierre hor_hora_cierre TIME NOT NULL,
CHANGE estado hor_estado CHAR(1) DEFAULT 'A';
-- =============================
ALTER TABLE instalaciones_instalacion_tarifas 
CHANGE tarifa_id tar_tarifa_id INT NOT NULL AUTO_INCREMENT,
CHANGE instalacion_id tar_instalacion_id INT NOT NULL,
CHANGE nombre_tarifa tar_nombre_tarifa VARCHAR(100) NOT NULL,
CHANGE tipo_cliente tar_tipo_cliente VARCHAR(50) NOT NULL,
CHANGE aplica_dia tar_aplica_dia VARCHAR(50),
CHANGE hora_inicio tar_hora_inicio TIME,
CHANGE hora_fin tar_hora_fin TIME,
CHANGE precio_por_hora tar_precio_por_hora DECIMAL(10,2) NOT NULL,
CHANGE precio_minimo tar_precio_minimo DECIMAL(10,2),
CHANGE descuento_porcentaje tar_descuento_porcentaje DECIMAL(5,2) DEFAULT 0.00,
CHANGE fecha_inicio_vigencia tar_fecha_inicio_vigencia DATE NOT NULL,
CHANGE fecha_fin_vigencia tar_fecha_fin_vigencia DATE,
CHANGE prioridad tar_prioridad INT DEFAULT 0,
CHANGE estado tar_estado CHAR(1) DEFAULT 'A',
CHANGE fecha_registro tar_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE  instalaciones_mantenimientos 
CHANGE mantenimiento_id man_mantenimiento_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id man_tenant_id INT NOT NULL,
CHANGE cancha_id man_cancha_id INT NOT NULL,
CHANGE tipo man_tipo VARCHAR(50) NOT NULL COMMENT 'preventivo, correctivo, limpieza, reparacion, inspeccion, otra',
CHANGE descripcion man_descripcion text NOT NULL,
CHANGE notas man_notas text,
CHANGE fecha_inicio man_fecha_inicio DATETIME NOT NULL,
CHANGE fecha_fin man_fecha_fin DATETIME NOT NULL,
CHANGE responsable_id man_responsable_id INT,
CHANGE recurrir man_recurrir VARCHAR(2) DEFAULT 'NO' COMMENT 'SI o NO',
CHANGE cadencia_recurrencia man_cadencia_recurrencia INT COMMENT 'Cada cuÃ¡ntos dÃ­as repetir',
CHANGE estado man_estado VARCHAR(20) DEFAULT 'PROGRAMADO' COMMENT 'PROGRAMADO, EN_PROGRESO, COMPLETADO, CANCELADO',
CHANGE fecha_creacion man_fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE fecha_actualizacion man_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL,
CHANGE usuario_creacion man_usuario_creacion INT,
CHANGE usuario_actualizacion man_usuario_actualizacion INT;
-- =============================
ALTER TABLE instalaciones_reserva_pagos 
CHANGE pago_id pag_pago_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id pag_tenant_id INT NOT NULL,
CHANGE reserva_id pag_reserva_id INT NOT NULL,
CHANGE monto pag_monto DECIMAL(10,2) NOT NULL,
CHANGE tipo_pago pag_tipo_pago VARCHAR(50) NOT NULL,
CHANGE forma_pago pag_forma_pago VARCHAR(50),
CHANGE referencia pag_referencia VARCHAR(100),
CHANGE pasarela pag_pasarela VARCHAR(50),
CHANGE transaction_id pag_transaction_id VARCHAR(100),
CHANGE estado pag_estado VARCHAR(20) DEFAULT 'COMPLETADO',
CHANGE fecha_pago pag_fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
CHANGE usuario_registro pag_usuario_registro INT;
-- =============================
ALTER TABLE instalaciones_reservas 
CHANGE reserva_id res_reserva_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id res_tenant_id INT NOT NULL,
CHANGE instalacion_id res_instalacion_id INT NOT NULL,
CHANGE cliente_id res_cliente_id INT NOT NULL,
CHANGE fecha_reserva res_fecha_reserva DATE NOT NULL,
CHANGE hora_inicio res_hora_inicio TIME NOT NULL,
CHANGE hora_fin res_hora_fin TIME NOT NULL,
CHANGE duracion_minutos res_duracion_minutos INT NOT NULL,
CHANGE es_recurrente res_es_recurrente CHAR(1) DEFAULT 'N',
CHANGE reserva_padre_id res_reserva_padre_id INT,
CHANGE recurrencia_config res_recurrencia_config json,
CHANGE tarifa_aplicada_id res_tarifa_aplicada_id INT,
CHANGE precio_base res_precio_base DECIMAL(10,2) NOT NULL,
CHANGE descuento_monto res_descuento_monto DECIMAL(10,2) DEFAULT 0.00,
CHANGE precio_total res_precio_total DECIMAL(10,2) NOT NULL,
CHANGE abono_utilizado res_abono_utilizado DECIMAL(10,2) DEFAULT 0.00,
CHANGE estado res_estado VARCHAR(20) DEFAULT 'PENDIENTE',
CHANGE requiere_confirmacion res_requiere_confirmacion CHAR(1) DEFAULT 'S',
CHANGE fecha_confirmacion res_fecha_confirmacion DATETIME,
CHANGE observaciones res_observaciones text,
CHANGE fecha_registro res_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE usuario_registro res_usuario_registro INT;
-- =============================
ALTER TABLE instalaciones_sedes 
CHANGE sede_id sed_sede_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id sed_tenant_id INT NOT NULL,
CHANGE codigo sed_codigo VARCHAR(50) NOT NULL,
CHANGE nombre sed_nombre VARCHAR(100) NOT NULL,
CHANGE descripcion sed_descripcion text,
CHANGE direccion sed_direccion VARCHAR(400) NOT NULL,
CHANGE ciudad sed_ciudad VARCHAR(100),
CHANGE provincia sed_provincia VARCHAR(100),
CHANGE pais sed_pais VARCHAR(50) DEFAULT 'Ecuador',
CHANGE latitud sed_latitud DECIMAL(10,8),
CHANGE longitud sed_longitud DECIMAL(11,8),
CHANGE telefono sed_telefono VARCHAR(20),
CHANGE email sed_email VARCHAR(100),
CHANGE horario_apertura sed_horario_apertura TIME,
CHANGE horario_cierre sed_horario_cierre TIME,
CHANGE dias_atencion sed_dias_atencion VARCHAR(50) DEFAULT 'LUNES-DOMINGO',
CHANGE superficie_total sed_superficie_total DECIMAL(10,2),
CHANGE capacidad_total sed_capacidad_total INT,
CHANGE estacionamiento sed_estacionamiento CHAR(1) DEFAULT 'S',
CHANGE cafeteria sed_cafeteria CHAR(1) DEFAULT 'N',
CHANGE tienda sed_tienda CHAR(1) DEFAULT 'N',
CHANGE foto_principal sed_foto_principal VARCHAR(200),
CHANGE galeria sed_galeria json,
CHANGE es_principal sed_es_principal CHAR(1) DEFAULT 'N',
CHANGE estado sed_estado CHAR(1) DEFAULT 'A',
CHANGE fecha_registro sed_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE fecha_actualizacion sed_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE instalaciones_tipos_instalacion 
CHANGE tipo_id tip_tipo_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id tip_tenant_id INT NOT NULL,
CHANGE codigo tip_codigo VARCHAR(50) NOT NULL,
CHANGE nombre tip_nombre VARCHAR(100) NOT NULL,
CHANGE descripcion tip_descripcion text,
CHANGE icono tip_icono VARCHAR(50) DEFAULT 'fa-futbol',
CHANGE color tip_color VARCHAR(7) DEFAULT '#28a745',
CHANGE requiere_equipamiento tip_requiere_equipamiento CHAR(1) DEFAULT 'N',
CHANGE permite_reserva_online tip_permite_reserva_online CHAR(1) DEFAULT 'S',
CHANGE estado tip_estado CHAR(1) DEFAULT 'A',
CHANGE fecha_registro tip_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;

-- =============================
ALTER TABLE seguridad_auditoria 
CHANGE auditoria_id aud_auditoria_id bigint NOT NULL AUTO_INCREMENT,
CHANGE tenant_id aud_tenant_id INT,
CHANGE usuario_id aud_usuario_id INT,
CHANGE modulo aud_modulo VARCHAR(50),
CHANGE tabla aud_tabla VARCHAR(100),
CHANGE registro_id aud_registro_id INT,
CHANGE operacion aud_operacion VARCHAR(20),
CHANGE valores_anteriores aud_valores_anteriores json,
CHANGE valores_nuevos aud_valores_nuevos json,
CHANGE ip aud_ip VARCHAR(45),
CHANGE user_agent aud_user_agent text,
CHANGE url aud_url VARCHAR(500),
CHANGE metodo aud_metodo VARCHAR(10),
CHANGE fecha_operacion aud_fecha_operacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE seguridad_auditoria_logs 
CHANGE log_id log_log_id bigint NOT NULL AUTO_INCREMENT,
CHANGE tenant_id log_tenant_id INT,
CHANGE usuario_id log_usuario_id INT,
CHANGE accion log_accion VARCHAR(100) NOT NULL,
CHANGE tabla log_tabla VARCHAR(100),
CHANGE registro_id log_registro_id INT,
CHANGE datos_anteriores log_datos_anteriores json,
CHANGE datos_nuevos log_datos_nuevos json,
CHANGE ip_address log_ip_address VARCHAR(45),
CHANGE user_agent log_user_agent VARCHAR(500),
CHANGE created_at log_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;
-- ============================
ALTER TABLE seguridad_log_accesos 
CHANGE log_id acc_log_id INT NOT NULL AUTO_INCREMENT,
CHANGE usuario_id acc_usuario_id INT,
CHANGE tenant_id acc_tenant_id INT,
CHANGE fecha acc_fecha DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
CHANGE tipo acc_tipo VARCHAR(32) NOT NULL,
CHANGE ip acc_ip VARCHAR(45),
CHANGE user_agent acc_user_agent VARCHAR(255),
CHANGE exito acc_exito CHAR(1) DEFAULT 'S',
CHANGE mensaje acc_mensaje VARCHAR(255);
-- =============================
ALTER TABLE seguridad_menu_config 
CHANGE id con_id INT NOT NULL AUTO_INCREMENT,
CHANGE modulo_codigo con_modulo_codigo VARCHAR(50) NOT NULL,
CHANGE opcion con_opcion VARCHAR(100) NOT NULL,
CHANGE icono con_icono VARCHAR(50) NOT NULL,
CHANGE color con_color VARCHAR(20),
CHANGE permiso_requerido con_permiso_requerido VARCHAR(100),
CHANGE orden con_orden INT DEFAULT 0;
-- =============================
ALTER TABLE seguridad_modulos 
CHANGE id mod_id INT NOT NULL AUTO_INCREMENT,
CHANGE codigo mod_codigo VARCHAR(50) NOT NULL COMMENT 'Código único del módulo',
CHANGE nombre mod_nombre VARCHAR(100) NOT NULL,
CHANGE descripcion mod_descripcion VARCHAR(500),
CHANGE icono mod_icono VARCHAR(100) DEFAULT 'fas fa-cube' COMMENT 'Clase Font Awesome',
CHANGE color_fondo mod_color_fondo VARCHAR(20) DEFAULT '#3B82F6' COMMENT 'Color del icono en hex',
CHANGE orden mod_orden INT DEFAULT 0 COMMENT 'Orden de visualización',
CHANGE ruta_modulo mod_ruta_modulo VARCHAR(100) COMMENT 'module para el router',
CHANGE ruta_controller mod_ruta_controller VARCHAR(100) COMMENT 'controller para el router',
CHANGE ruta_action mod_ruta_action VARCHAR(100) DEFAULT 'index' COMMENT 'action para el router',
CHANGE es_externo mod_es_externo TINYINT(1) DEFAULT 0 COMMENT '1=Sistema externo con su propia BD',
CHANGE url_externa mod_url_externa VARCHAR(500) COMMENT 'URL si es sistema externo',
CHANGE requiere_licencia mod_requiere_licencia TINYINT(1) DEFAULT 1 COMMENT '1=Requiere suscripción',
CHANGE activo mod_activo TINYINT(1) DEFAULT 1,
CHANGE created_at mod_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE updated_at mod_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE seguridad_modulos_sistema 
CHANGE modulo_id sis_modulo_id INT NOT NULL AUTO_INCREMENT,
CHANGE codigo sis_codigo VARCHAR(50) NOT NULL,
CHANGE nombre sis_nombre VARCHAR(100) NOT NULL,
CHANGE descripcion sis_descripcion text,
CHANGE icono sis_icono VARCHAR(50) DEFAULT 'fa-puzzle-piece',
CHANGE color sis_color VARCHAR(7) DEFAULT '#007bff',
CHANGE url_base sis_url_base VARCHAR(200) COMMENT 'URL si es sistema externo',
CHANGE es_externo sis_es_externo CHAR(1) DEFAULT 'N' COMMENT 'S si apunta a otro sistema',
CHANGE base_datos_externa sis_base_datos_externa VARCHAR(100) COMMENT 'Nombre de BD si es sistema legacy',
CHANGE orden_visualizacion sis_orden_visualizacion INT DEFAULT 0,
CHANGE requiere_suscripcion sis_requiere_suscripcion CHAR(1) DEFAULT 'S',
CHANGE estado sis_estado CHAR(1) DEFAULT 'A',
CHANGE fecha_registro sis_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE seguridad_notificaciones 
CHANGE notificacion_id not_notificacion_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id not_tenant_id INT,
CHANGE usuario_id not_usuario_id INT,
CHANGE tipo not_tipo VARCHAR(50) NOT NULL,
CHANGE titulo not_titulo VARCHAR(200) NOT NULL,
CHANGE mensaje not_mensaje text NOT NULL,
CHANGE url_accion not_url_accion VARCHAR(300),
CHANGE icono not_icono VARCHAR(50),
CHANGE color not_color VARCHAR(7),
CHANGE leida not_leida CHAR(1) DEFAULT 'N',
CHANGE fecha_lectura not_fecha_lectura DATETIME,
CHANGE fecha_creacion not_fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE fecha_expiracion not_fecha_expiracion DATETIME;
-- =============================
ALTER TABLE seguridad_notificaciones_log 
CHANGE log_id log_log_id INT NOT NULL AUTO_INCREMENT,
CHANGE usuario_id log_usuario_id INT,
CHANGE tenant_id log_tenant_id INT,
CHANGE destinatario_email log_destinatario_email VARCHAR(255) NOT NULL,
CHANGE tipo_notificacion log_tipo_notificacion VARCHAR(50) NOT NULL,
CHANGE asunto log_asunto VARCHAR(255) NOT NULL,
CHANGE mensaje log_mensaje text NOT NULL,
CHANGE enviado log_enviado TINYINT(1) DEFAULT 0,
CHANGE error log_error text,
CHANGE fecha_envio log_fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP;
-- =============================
ALTER TABLE seguridad_planes_suscripcion 
CHANGE plan_id sus_plan_id INT NOT NULL AUTO_INCREMENT,
CHANGE codigo sus_codigo VARCHAR(50) NOT NULL,
CHANGE nombre sus_nombre VARCHAR(100) NOT NULL,
CHANGE descripcion sus_descripcion text,
CHANGE precio_mensual sus_precio_mensual DECIMAL(10,2) NOT NULL,
CHANGE precio_anual sus_precio_anual DECIMAL(10,2),
CHANGE descuento_anual sus_descuento_anual DECIMAL(5,2) DEFAULT 0.00,
CHANGE usuarios_incluidos sus_usuarios_incluidos INT DEFAULT 5,
CHANGE sedes_incluidas sus_sedes_incluidas INT DEFAULT 1,
CHANGE almacenamiento_gb sus_almacenamiento_gb INT DEFAULT 10,
CHANGE modulos_incluidos sus_modulos_incluidos json,
CHANGE caracteristicas sus_caracteristicas json,
CHANGE es_destacado sus_es_destacado CHAR(1) DEFAULT 'N',
CHANGE es_personalizado sus_es_personalizado CHAR(1) DEFAULT 'N',
CHANGE color sus_color VARCHAR(7) DEFAULT '#007bff',
CHANGE orden_visualizacion sus_orden_visualizacion INT DEFAULT 0,
CHANGE estado sus_estado CHAR(1) DEFAULT 'A',
CHANGE fecha_registro sus_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE seguridad_rol_modulos 
CHANGE rol_id rmo_rol_id INT NOT NULL AUTO_INCREMENT,
CHANGE rol_rol_id rmo_rol_rol_id INT NOT NULL,
CHANGE rol_modulo_id rmo_rol_modulo_id INT NOT NULL,
CHANGE rol_puede_ver rmo_rol_puede_ver TINYINT(1) DEFAULT 1,
CHANGE rol_puede_crear rmo_rol_puede_crear TINYINT(1) DEFAULT 0,
CHANGE rol_puede_editar rmo_rol_puede_editar TINYINT(1) DEFAULT 0,
CHANGE rol_puede_eliminar rmo_rol_puede_eliminar TINYINT(1) DEFAULT 0,
CHANGE rol_permisos_especiales rmo_rol_permisos_especiales json COMMENT 'Permisos específicos del módulo',
CHANGE rol_created_at rmo_rol_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE seguridad_roles 
CHANGE rol_id rol_rol_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id rol_tenant_id INT,
CHANGE codigo rol_codigo VARCHAR(50) NOT NULL,
CHANGE nombre rol_nombre VARCHAR(100) NOT NULL,
CHANGE descripcion rol_descripcion text,
CHANGE permisos rol_permisos json,
CHANGE es_super_admin rol_es_super_admin CHAR(1) DEFAULT 'N',
CHANGE es_admin_tenant rol_es_admin_tenant CHAR(1) DEFAULT 'N',
CHANGE puede_modificar_permisos rol_puede_modificar_permisos CHAR(1) DEFAULT 'N',
CHANGE nivel_acceso rol_nivel_acceso INT DEFAULT 1,
CHANGE estado rol_estado CHAR(1) DEFAULT 'A',
CHANGE fecha_registro rol_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE seguridad_tarifas 
CHANGE tarifa_id tar_tarifa_id INT NOT NULL AUTO_INCREMENT,
CHANGE cancha_id tar_cancha_id INT NOT NULL,
CHANGE dia_semana tar_dia_semana TINYINT NOT NULL COMMENT '0=Domingo, 1=Lunes...6=SÃ¡bado',
CHANGE hora_inicio tar_hora_inicio TIME NOT NULL,
CHANGE hora_fin tar_hora_fin TIME NOT NULL,
CHANGE precio tar_precio DECIMAL(10,2) DEFAULT 0.00 NOT NULL,
CHANGE estado tar_estado VARCHAR(20) DEFAULT 'ACTIVO' COMMENT 'ACTIVO, INACTIVO',
CHANGE fecha_creacion tar_fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE fecha_actualizacion tar_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE seguridad_tenant_configuraciones 
CHANGE config_id con_config_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id con_tenant_id INT NOT NULL,
CHANGE clave con_clave VARCHAR(100) NOT NULL,
CHANGE valor con_valor text,
CHANGE tipo con_tipo enum('string','int','bool','json') DEFAULT 'string',
CHANGE descripcion con_descripcion VARCHAR(255),
CHANGE created_at con_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE updated_at con_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE  seguridad_tenant_modulos 
CHANGE id tmo_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id tmo_tenant_id INT NOT NULL,
CHANGE modulo_id tmo_modulo_id INT NOT NULL,
CHANGE nombre_personalizado tmo_nombre_personalizado VARCHAR(100),
CHANGE icono_personalizado tmo_icono_personalizado VARCHAR(100),
CHANGE color_personalizado tmo_color_personalizado VARCHAR(20),
CHANGE orden_visualizacion tmo_orden_visualizacion INT DEFAULT 0,
CHANGE activo tmo_activo CHAR(1) DEFAULT 'S',
CHANGE fecha_inicio tmo_fecha_inicio DATE NOT NULL,
CHANGE fecha_fin tmo_fecha_fin DATE COMMENT 'NULL = sin vencimiento',
CHANGE estado tmo_estado enum('ACTIVO','SUSPENDIDO','VENCIDO','CANCELADO') DEFAULT 'ACTIVO',
CHANGE tipo_licencia tmo_tipo_licencia enum('PRUEBA','MENSUAL','ANUAL','PERPETUA') DEFAULT 'MENSUAL',
CHANGE max_usuarios tmo_max_usuarios INT COMMENT 'NULL = ilimitado',
CHANGE observaciones tmo_observaciones text,
CHANGE created_at tmo_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE updated_at tmo_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL;
-- =============================
ALTER TABLE  seguridad_tenants 
CHANGE tenant_id ten_tenant_id INT NOT NULL AUTO_INCREMENT,
CHANGE ruc ten_ruc VARCHAR(13) NOT NULL,
CHANGE razon_social ten_razon_social VARCHAR(300) NOT NULL,
CHANGE nombre_comercial ten_nombre_comercial VARCHAR(300),
CHANGE tipo_empresa ten_tipo_empresa VARCHAR(50),
CHANGE direccion ten_direccion VARCHAR(400),
CHANGE telefono ten_telefono VARCHAR(20),
CHANGE celular ten_celular VARCHAR(15),
CHANGE email ten_email VARCHAR(100) NOT NULL,
CHANGE sitio_web ten_sitio_web VARCHAR(200),
CHANGE representante_nombre ten_representante_nombre VARCHAR(200),
CHANGE representante_identificacion ten_representante_identificacion VARCHAR(13),
CHANGE representante_email ten_representante_email VARCHAR(100),
CHANGE representante_telefono ten_representante_telefono VARCHAR(15),
CHANGE plan_id ten_plan_id INT NOT NULL,
CHANGE fecha_inicio ten_fecha_inicio DATE NOT NULL,
CHANGE fecha_vencimiento ten_fecha_vencimiento DATE NOT NULL,
CHANGE estado_suscripcion ten_estado_suscripcion VARCHAR(20) DEFAULT 'ACTIVA',
CHANGE usuarios_permitidos ten_usuarios_permitidos INT DEFAULT 5,
CHANGE sedes_permitidas ten_sedes_permitidas INT DEFAULT 1,
CHANGE almacenamiento_gb ten_almacenamiento_gb INT DEFAULT 10,
CHANGE logo ten_logo VARCHAR(200),
CHANGE favicon ten_favicon VARCHAR(200),
CHANGE color_primario ten_color_primario VARCHAR(7) DEFAULT '#007bff',
CHANGE color_secundario ten_color_secundario VARCHAR(7) DEFAULT '#6c757d',
CHANGE color_acento ten_color_acento VARCHAR(7) DEFAULT '#28a745',
CHANGE tiene_sistema_antiguo ten_tiene_sistema_antiguo CHAR(1) DEFAULT 'N',
CHANGE bd_antigua ten_bd_antigua VARCHAR(100),
CHANGE tenant_id_antiguo ten_tenant_id_antiguo INT,
CHANGE monto_mensual ten_monto_mensual DECIMAL(10,2) NOT NULL,
CHANGE dia_corte ten_dia_corte INT DEFAULT 1,
CHANGE metodo_pago_preferido ten_metodo_pago_preferido VARCHAR(50),
CHANGE timezone ten_timezone VARCHAR(50) DEFAULT 'America/Guayaquil',
CHANGE idioma ten_idioma VARCHAR(5) DEFAULT 'es',
CHANGE moneda ten_moneda VARCHAR(3) DEFAULT 'USD',
CHANGE estado ten_estado CHAR(1) DEFAULT 'A',
CHANGE motivo_suspension ten_motivo_suspension text,
CHANGE fecha_suspension ten_fecha_suspension DATETIME,
CHANGE fecha_registro ten_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE fecha_actualizacion ten_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL,
CHANGE usuario_registro ten_usuario_registro INT,
CHANGE usuario_actualizacion ten_usuario_actualizacion INT;
-- =============================
ALTER TABLE seguridad_usuarios 
CHANGE usuario_id usu_usuario_id INT NOT NULL AUTO_INCREMENT,
CHANGE tenant_id usu_tenant_id INT NOT NULL,
CHANGE identificacion usu_identificacion VARCHAR(20),
CHANGE nombres usu_nombres VARCHAR(150) NOT NULL,
CHANGE apellidos usu_apellidos VARCHAR(150) NOT NULL,
CHANGE email usu_email VARCHAR(100) NOT NULL,
CHANGE telefono usu_telefono VARCHAR(15),
CHANGE celular usu_celular VARCHAR(15),
CHANGE username usu_username VARCHAR(50) NOT NULL,
CHANGE password usu_password VARCHAR(255) NOT NULL,
CHANGE requiere_2fa usu_requiere_2fa CHAR(1) DEFAULT 'S',
CHANGE codigo_2fa usu_codigo_2fa VARCHAR(6),
CHANGE codigo_2fa_expira usu_codigo_2fa_expira DATETIME,
CHANGE intentos_2fa usu_intentos_2fa INT DEFAULT 0,
CHANGE token_recuperacion usu_token_recuperacion VARCHAR(100),
CHANGE token_recuperacion_expira usu_token_recuperacion_expira DATETIME,
CHANGE rol_id usu_rol_id INT NOT NULL,
CHANGE permisos_especiales usu_permisos_especiales json,
CHANGE ultimo_login usu_ultimo_login DATETIME,
CHANGE ip_ultimo_login usu_ip_ultimo_login VARCHAR(45),
CHANGE token_sesion usu_token_sesion VARCHAR(255),
CHANGE token_sesion_expira usu_token_sesion_expira DATETIME,
CHANGE sedes_acceso usu_sedes_acceso json,
CHANGE sede_principal_id usu_sede_principal_id INT,
CHANGE avatar usu_avatar VARCHAR(200),
CHANGE tema usu_tema VARCHAR(20) DEFAULT 'light',
CHANGE idioma usu_idioma VARCHAR(5) DEFAULT 'es',
CHANGE notificaciones_email usu_notificaciones_email CHAR(1) DEFAULT 'S',
CHANGE notificaciones_push usu_notificaciones_push CHAR(1) DEFAULT 'S',
CHANGE debe_cambiar_password usu_debe_cambiar_password CHAR(1) DEFAULT 'N',
CHANGE password_expira usu_password_expira DATE,
CHANGE intentos_fallidos usu_intentos_fallidos INT DEFAULT 0,
CHANGE bloqueado_hasta usu_bloqueado_hasta DATETIME,
CHANGE estado usu_estado CHAR(1) DEFAULT 'A',
CHANGE fecha_registro usu_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
CHANGE fecha_actualizacion usu_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL;

-- =============================
