-- =====================================================
-- DigiSports Arena - Fase 5: Vista 'usuarios'
-- Mapea seguridad_usuarios → usuarios (nombres sin prefijo)
-- ALGORITHM=MERGE permite INSERT/UPDATE/DELETE transparente
-- =====================================================

DROP VIEW IF EXISTS `usuarios`;

CREATE ALGORITHM=MERGE SQL SECURITY DEFINER VIEW `usuarios` AS
SELECT
    usu_usuario_id           AS usuario_id,
    usu_tenant_id            AS tenant_id,
    usu_identificacion       AS identificacion,
    usu_identificacion_hash  AS identificacion_hash,
    usu_nombres              AS nombres,
    usu_apellidos            AS apellidos,
    usu_email                AS email,
    usu_email_hash           AS email_hash,
    usu_telefono             AS telefono,
    usu_celular              AS celular,
    usu_username             AS username,
    usu_password             AS password,
    usu_requiere_2fa         AS requiere_2fa,
    usu_codigo_2fa           AS codigo_2fa,
    usu_codigo_2fa_expira    AS codigo_2fa_expira,
    usu_intentos_2fa         AS intentos_2fa,
    usu_token_recuperacion   AS token_recuperacion,
    usu_token_recuperacion_expira AS token_recuperacion_expira,
    usu_rol_id               AS rol_id,
    usu_permisos_especiales  AS permisos_especiales,
    usu_ultimo_login         AS ultimo_login,
    usu_ip_ultimo_login      AS ip_ultimo_login,
    usu_token_sesion         AS token_sesion,
    usu_token_sesion_expira  AS token_sesion_expira,
    usu_sedes_acceso         AS sedes_acceso,
    usu_sede_principal_id    AS sede_principal_id,
    usu_avatar               AS avatar,
    usu_tema                 AS tema,
    usu_idioma               AS idioma,
    usu_notificaciones_email AS notificaciones_email,
    usu_notificaciones_push  AS notificaciones_push,
    usu_debe_cambiar_password AS debe_cambiar_password,
    usu_password_expira      AS password_expira,
    usu_intentos_fallidos    AS intentos_fallidos,
    usu_bloqueado_hasta      AS bloqueado_hasta,
    usu_estado               AS estado,
    usu_fecha_registro       AS fecha_registro,
    usu_fecha_actualizacion  AS fecha_actualizacion
FROM seguridad_usuarios;

-- =====================================================
-- Vista 'roles' → mapea seguridad_roles
-- =====================================================
DROP VIEW IF EXISTS `roles`;

CREATE ALGORITHM=MERGE SQL SECURITY DEFINER VIEW `roles` AS
SELECT
    rol_rol_id               AS rol_id,
    rol_tenant_id            AS tenant_id,
    rol_codigo               AS codigo,
    rol_nombre               AS nombre,
    rol_descripcion          AS descripcion,
    rol_permisos             AS permisos,
    rol_es_super_admin       AS es_super_admin,
    rol_es_admin_tenant      AS es_admin_tenant,
    rol_puede_modificar_permisos AS puede_modificar_permisos,
    rol_nivel_acceso         AS nivel_acceso,
    rol_estado               AS estado,
    rol_fecha_registro       AS fecha_registro
FROM seguridad_roles;

-- =====================================================
-- Vista 'tenants' → mapea seguridad_tenants
-- =====================================================
DROP VIEW IF EXISTS `tenants`;

CREATE ALGORITHM=MERGE SQL SECURITY DEFINER VIEW `tenants` AS
SELECT
    ten_tenant_id            AS tenant_id,
    ten_ruc                  AS ruc,
    ten_ruc_hash             AS ruc_hash,
    ten_razon_social         AS razon_social,
    ten_nombre_comercial     AS nombre_empresa,
    ten_tipo_empresa         AS tipo_empresa,
    ten_direccion            AS direccion,
    ten_telefono             AS telefono,
    ten_celular              AS celular,
    ten_email                AS email_contacto,
    ten_email_hash           AS email_hash,
    ten_sitio_web            AS sitio_web,
    ten_plan_id              AS plan_id,
    ten_fecha_inicio         AS plan_inicio,
    ten_fecha_vencimiento    AS plan_fin,
    ten_estado_suscripcion   AS estado_suscripcion,
    ten_logo                 AS logo,
    ten_estado               AS estado,
    ten_fecha_registro       AS fecha_registro
FROM seguridad_tenants;
