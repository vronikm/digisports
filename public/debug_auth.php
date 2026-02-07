<?php
/**
 * Debug de autenticación
 */
require_once __DIR__ . '/../config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/security.php';

echo "<h1>Debug de Autenticación</h1>";
echo "<style>body{font-family: Arial, sans-serif; padding: 20px;} .success{color:green;} .error{color:red;} pre{background:#f5f5f5;padding:10px;border-radius:4px;}</style>";

try {
    $db = Database::getInstance()->getConnection();
    echo "<p class='success'>✓ Conexión a BD exitosa</p>";
    
    // 1. Verificar que el usuario existe
    echo "<h2>1. Buscar usuario 'superadmin'</h2>";
    
    $username = 'superadmin';
    
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
        echo "<pre>";
        echo "usu_usuario_id: {$user['usu_usuario_id']}\n";
        echo "usu_username: {$user['usu_username']}\n";
        echo "usu_email: {$user['usu_email']}\n";
        echo "usu_estado: {$user['usu_estado']}\n";
        echo "usu_tenant_id: {$user['usu_tenant_id']}\n";
        echo "usu_rol_id: {$user['usu_rol_id']}\n";
        echo "rol_codigo: {$user['rol_codigo']}\n";
        echo "ten_estado_suscripcion: {$user['ten_estado_suscripcion']}\n";
        echo "rol_nivel_acceso: {$user['rol_nivel_acceso']}\n";
        echo "usu_bloqueado_hasta: " . ($user['usu_bloqueado_hasta'] ?? 'NULL') . "\n";
        echo "usu_password (hash): " . substr($user['usu_password'], 0, 20) . "...\n";
        echo "</pre>";
        
        // 2. Verificar contraseña
        echo "<h2>2. Test de verificación de contraseña</h2>";
        
        $testPassword = 'admin123'; // Contraseña de prueba
        
        // Ver si Security tiene el método verifyPassword
        if (method_exists('Security', 'verifyPassword')) {
            echo "<p class='success'>✓ Método Security::verifyPassword existe</p>";
            
            $isValid = Security::verifyPassword($testPassword, $user['password']);
            if ($isValid) {
                echo "<p class='success'>✓ Contraseña '$testPassword' es correcta</p>";
            } else {
                echo "<p class='error'>✗ Contraseña '$testPassword' NO es correcta</p>";
                
                // Intentar con password_verify directamente
                echo "<h3>Intentando password_verify nativo:</h3>";
                if (password_verify($testPassword, $user['password'])) {
                    echo "<p class='success'>✓ password_verify nativo funciona</p>";
                } else {
                    echo "<p class='error'>✗ password_verify nativo también falla</p>";
                }
            }
        } else {
            echo "<p class='error'>✗ Método Security::verifyPassword NO existe</p>";
        }
        
        // 3. Mostrar hash actual y generar uno nuevo
        echo "<h2>3. Información del Hash</h2>";
        echo "<pre>";
        echo "Hash actual completo:\n{$user['password']}\n\n";
        
        // Verificar si el hash parece ser BCrypt o Argon2
        if (strpos($user['password'], '$2y$') === 0) {
            echo "Tipo de hash: BCrypt\n";
        } elseif (strpos($user['password'], '$argon2') === 0) {
            echo "Tipo de hash: Argon2\n";
        } else {
            echo "Tipo de hash: Desconocido o MD5/SHA\n";
        }
        
        // Generar nuevo hash para referencia
        $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
        echo "\nNuevo hash para '$testPassword':\n$newHash\n";
        echo "</pre>";
        
        // 4. Actualizar la contraseña si se solicita
        if (isset($_GET['fix']) && $_GET['fix'] === '1') {
            echo "<h2>4. Actualizando contraseña...</h2>";
            
            $newPassword = 'admin123';
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE usuario_id = ?");
            $stmt->execute([$newHash, $user['usuario_id']]);
            
            echo "<p class='success'>✓ Contraseña actualizada a: $newPassword</p>";
            echo "<p>Nuevo hash: $newHash</p>";
        } else {
            echo "<h2>4. Actualizar contraseña</h2>";
            echo "<p><a href='?fix=1' style='background:#007bff;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;'>Actualizar contraseña a 'admin123'</a></p>";
        }
        
    } else {
        echo "<p class='error'>✗ Usuario NO encontrado</p>";
        
        // Ver qué usuarios existen
        echo "<h3>Usuarios existentes:</h3>";
        $stmt = $db->query("SELECT usuario_id, username, email, estado FROM usuarios LIMIT 10");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($usuarios, true) . "</pre>";
        
        // Ver tenants
        echo "<h3>Tenants existentes:</h3>";
        $stmt = $db->query("SELECT tenant_id, razon_social, estado FROM tenants LIMIT 10");
        $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($tenants, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// 5. Verificar método verifyPassword en Security
echo "<h2>5. Verificar clase Security</h2>";
$securityFile = BASE_PATH . '/config/security.php';
if (file_exists($securityFile)) {
    echo "<p class='success'>✓ Archivo security.php existe</p>";
    
    $content = file_get_contents($securityFile);
    if (strpos($content, 'verifyPassword') !== false) {
        echo "<p class='success'>✓ Contiene método verifyPassword</p>";
        
        // Mostrar el método
        preg_match('/function verifyPassword\([^)]*\)[^{]*\{[^}]+\}/s', $content, $matches);
        if (!empty($matches[0])) {
            echo "<pre>" . htmlspecialchars(substr($matches[0], 0, 500)) . "</pre>";
        }
    } else {
        echo "<p class='error'>✗ NO contiene método verifyPassword</p>";
    }
} else {
    echo "<p class='error'>✗ Archivo security.php NO existe</p>";
}
?>
