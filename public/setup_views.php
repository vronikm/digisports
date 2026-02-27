<?php
/**
 * Script de inicialización - Crea vistas de compatibilidad
 * Acceso: http://localhost/digisports/public/setup_views.php
 * IMPORTANTE: ELIMINAR este archivo después de ejecutar exitosamente
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once dirname(__DIR__) . '/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "<h2 style='color: #333;'>Inicializando vistas de compatibilidad...</h2>";
    echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px; font-family: monospace;'>";
    
    // Vista usuarios
    echo "1. Creando vista 'usuarios'...\n";
    $pdo->exec("DROP VIEW IF EXISTS usuarios");
    $pdo->exec("
        CREATE VIEW usuarios AS
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
    echo "   ✓ Vista 'usuarios' creada\n\n";
    
    // Vista roles
    echo "2. Creando vista 'roles'...\n";
    $pdo->exec("DROP VIEW IF EXISTS roles");
    $pdo->exec("
        CREATE VIEW roles AS
        SELECT
            rol_rol_id          AS rol_id,
            rol_tenant_id       AS tenant_id,
            rol_codigo          AS codigo,
            rol_nombre          AS nombre,
            rol_descripcion     AS descripcion,
            rol_permisos        AS permisos,
            rol_estado          AS estado,
            rol_fecha_registro  AS fecha_registro
        FROM seguridad_roles
    ");
    echo "   ✓ Vista 'roles' creada\n\n";
    
    // Vista mantenimientos (por seguridad)
    echo "3. Verificando vista 'mantenimientos'...\n";
    $pdo->exec("DROP VIEW IF EXISTS mantenimientos");
    $pdo->exec("
        CREATE VIEW mantenimientos AS
        SELECT
            man_mantenimiento_id AS mantenimiento_id,
            man_tenant_id        AS tenant_id,
            man_cancha_id        AS cancha_id,
            man_tipo             AS tipo,
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
        FROM instalaciones_mantenimientos
    ");
    echo "   ✓ Vista 'mantenimientos' creada/actualizada\n\n";
    
    echo "</pre>";
    echo "<p style='color: green; font-weight: bold; font-size: 16px;'>✓ Todas las vistas se crearon correctamente.</p>";
    echo "<p style='color: #666; margin-top: 20px;'>";
    echo "<strong>Próximos pasos:</strong><br>";
    echo "1. Prueba acceder al módulo de Mantenimientos<br>";
    echo "2. Si todo funciona, elimina este archivo: <code>public/setup_views.php</code><br>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<pre style='color: red; background: #fee; padding: 15px; border-radius: 5px;'>";
    echo "ERROR: " . $e->getMessage() . "\n\n";
    echo "Trace:\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}
?>
