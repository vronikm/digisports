<?php
// Script para crear las vistas de compatibilidad usuarios y roles
require_once 'config/database.php';

try {
    $db = Database::getInstance();
    
    // Vista usuarios
    $db->getPDO()->exec("DROP VIEW IF EXISTS usuarios");
    $db->getPDO()->exec("CREATE VIEW usuarios AS
        SELECT
            usu_usuario_id      AS usuario_id,
            usu_tenant_id       AS tenant_id,
            usu_identificacion  AS identificacion,
            usu_nombres         AS nombres,
            usu_apellidos       AS apellidos,
            usu_email           AS email,
            usu_telefono        AS telefono,
            usu_celular         AS celular,
            usu_username        AS username,
            usu_password        AS password,
            usu_requiere_2fa    AS requiere_2fa,
            usu_rol_id          AS rol_id,
            usu_estado          AS estado,
            usu_fecha_registro  AS fecha_registro,
            usu_fecha_actualizacion AS fecha_actualizacion
        FROM seguridad_usuarios
    ");
    
    // Vista roles
    $db->getPDO()->exec("DROP VIEW IF EXISTS roles");
    $db->getPDO()->exec("CREATE VIEW roles AS
        SELECT
            rol_rol_id          AS rol_id,
            rol_tenant_id       AS tenant_id,
            rol_codigo          AS codigo,
            rol_nombre          AS nombre,
            rol_descripcion     AS descripcion,
            rol_estado          AS estado,
            rol_fecha_registro  AS fecha_registro
        FROM seguridad_roles
    ");
    
    echo json_encode([
        'success' => true,
        'message' => 'Vistas de compatibilidad creadas exitosamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear las vistas: ' . $e->getMessage()
    ]);
}
?>
