<?php
/**
 * Debug completo del flujo de login
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/security.php';
require_once BASE_PATH . '/app/helpers/functions.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>Debug Completo del Login</h1>";
echo "<style>body{font-family: Arial, sans-serif; padding: 20px;} .success{color:green;} .error{color:red;} pre{background:#f5f5f5;padding:10px;border-radius:4px;overflow-x:auto;}</style>";

$db = Database::getInstance()->getConnection();

// Simular datos de POST
$username = 'superadmin';
$password = 'Admin@2024';

echo "<h2>1. Datos de entrada</h2>";
echo "<pre>Username: $username\nPassword: $password</pre>";

// Paso 1: Buscar usuario
echo "<h2>2. Buscar usuario en BD</h2>";
try {
    $stmt = $db->prepare("
        SELECT 
            u.*,
            t.ten_estado_suscripcion,
            t.ten_fecha_vencimiento,
            r.rol_codigo,
            r.rol_permisos,
            r.rol_nivel_acceso
        FROM seguridad_usuarios u
        INNER JOIN seguridad_tenants t ON u.usu_tenant_id = t.ten_tenant_id
        INNER JOIN seguridad_roles r ON u.usu_rol_id = r.rol_rol_id
        WHERE (u.usu_username = ? OR u.usu_email = ?)
        AND u.usu_estado = 'A'
        AND t.ten_estado IN ('A', 'ACTIVO', 'PRUEBA')
    ");
    
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<p class='success'>✓ Usuario encontrado</p>";
    } else {
        echo "<p class='error'>✗ Usuario NO encontrado</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error en consulta: " . $e->getMessage() . "</p>";
    exit;
}

// Paso 2: Verificar bloqueo
echo "<h2>3. Verificar bloqueo</h2>";
if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time()) {
    echo "<p class='error'>✗ Usuario bloqueado hasta: " . $user['bloqueado_hasta'] . "</p>";
} else {
    echo "<p class='success'>✓ Usuario NO está bloqueado</p>";
}

// Paso 3: Verificar contraseña
echo "<h2>4. Verificar contraseña</h2>";
$passwordValid = Security::verifyPassword($password, $user['password']);
if ($passwordValid) {
    echo "<p class='success'>✓ Contraseña correcta</p>";
} else {
    echo "<p class='error'>✗ Contraseña incorrecta</p>";
    echo "<pre>Hash almacenado: " . $user['password'] . "</pre>";
    exit;
}

// Paso 4: Verificar si requiere 2FA
echo "<h2>5. Verificar 2FA</h2>";
if ($user['requiere_2fa'] === 'S') {
    echo "<p class='error'>⚠ Requiere 2FA - esto puede causar el problema</p>";
    echo "<p>El sistema intentaría enviar un código por email y redirigir a 2FA</p>";
    
    // Desactivar 2FA para pruebas
    if (isset($_GET['disable_2fa'])) {
        $stmt = $db->prepare("UPDATE usuarios SET requiere_2fa = 'N' WHERE usuario_id = ?");
        $stmt->execute([$user['usuario_id']]);
        echo "<p class='success'>✓ 2FA desactivado para este usuario</p>";
        echo "<p><a href='?'>Recargar página</a></p>";
    } else {
        echo "<p><a href='?disable_2fa=1' style='background:#dc3545;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;'>Desactivar 2FA para este usuario</a></p>";
    }
} else {
    echo "<p class='success'>✓ NO requiere 2FA</p>";
}

// Paso 5: Simular completeLogin
echo "<h2>6. Simular creación de sesión (completeLogin)</h2>";

try {
    // Regenerar ID de sesión
    session_regenerate_id(true);
    
    // Establecer datos de sesión
    $_SESSION['user_id'] = $user['usuario_id'];
    $_SESSION['tenant_id'] = $user['tenant_id'];
    $_SESSION['rol_id'] = $user['rol_id'];
    $_SESSION['rol_codigo'] = $user['rol_codigo'];
    $_SESSION['user_name'] = $user['nombres'] . ' ' . $user['apellidos'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['nivel_acceso'] = $user['nivel_acceso'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    echo "<p class='success'>✓ Sesión creada correctamente</p>";
    echo "<pre>Datos de sesión:\n";
    print_r($_SESSION);
    echo "</pre>";
    
    // Actualizar último login
    $stmt = $db->prepare("
        UPDATE usuarios 
        SET ultimo_login = NOW(),
            ip_ultimo_login = ?,
            intentos_fallidos = 0,
            bloqueado_hasta = NULL
        WHERE usuario_id = ?
    ");
    $stmt->execute([$_SESSION['ip_address'], $user['usuario_id']]);
    echo "<p class='success'>✓ Último login actualizado en BD</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error al crear sesión: " . $e->getMessage() . "</p>";
}

// Paso 6: Verificar función isAuthenticated
echo "<h2>7. Verificar isAuthenticated()</h2>";
if (function_exists('isAuthenticated')) {
    if (isAuthenticated()) {
        echo "<p class='success'>✓ isAuthenticated() retorna TRUE</p>";
    } else {
        echo "<p class='error'>✗ isAuthenticated() retorna FALSE</p>";
        
        // Debug de la función
        echo "<h3>Debug de isAuthenticated:</h3>";
        echo "<pre>";
        echo "isset(\$_SESSION['user_id']): " . (isset($_SESSION['user_id']) ? 'true' : 'false') . "\n";
        echo "isset(\$_SESSION['tenant_id']): " . (isset($_SESSION['tenant_id']) ? 'true' : 'false') . "\n";
        echo "</pre>";
    }
} else {
    echo "<p class='error'>✗ Función isAuthenticated() NO existe</p>";
}

// Paso 7: Verificar redirección
echo "<h2>8. URL de redirección</h2>";
$redirectUrl = url('core', 'hub', 'index');
echo "<pre>Redirigir a: $redirectUrl</pre>";

// Botón para probar acceso directo al Hub
echo "<h2>9. Probar acceso</h2>";
echo "<p><a href='$redirectUrl' style='background:#28a745;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;margin-right:10px;'>Ir al Hub</a>";
echo "<a href='index.php?module=core&controller=auth&action=login' style='background:#007bff;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;'>Ir al Login</a></p>";

// Ver logs recientes
echo "<h2>10. Logs recientes de seguridad</h2>";
$logFile = BASE_PATH . '/storage/logs/security_' . date('Y-m-d') . '.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = array_slice(explode("\n", $logs), -20);
    echo "<pre style='max-height:300px;overflow-y:auto;'>" . htmlspecialchars(implode("\n", $lines)) . "</pre>";
} else {
    echo "<p>No hay logs de seguridad para hoy</p>";
}
?>
