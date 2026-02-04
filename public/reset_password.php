<?php
/**
 * DigiSports - Script de Reset de Contraseña
 * 
 * USO: Ejecuta este script desde el navegador o línea de comandos
 *      para resetear la contraseña del superadmin
 * 
 * URL: http://tu-dominio.com/digiSports/public/reset_password.php
 * 
 * ⚠️ IMPORTANTE: Elimina este archivo después de usarlo por seguridad
 */

// Configuración - MODIFICA ESTOS VALORES SI ES NECESARIO
$dbHost = 'localhost';
$dbName = 'digisports_core';
$dbUser = 'root';
$dbPass = '';

$nuevaPassword = 'admin123';  // Cambia esto por la contraseña que desees
$username = 'superadmin';      // Usuario a resetear

// Detectar ambiente y mostrar información
echo "<h2>🔧 DigiSports - Reset de Contraseña</h2>";
echo "<hr>";

// Información del servidor
echo "<h3>Información del Servidor:</h3>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
echo "<li><strong>Sistema:</strong> " . php_uname() . "</li>";
echo "<li><strong>Extensiones cargadas:</strong></li>";
echo "<ul>";

// Verificar extensiones críticas
$extensiones = ['pdo', 'pdo_mysql', 'openssl', 'mbstring', 'json'];
foreach ($extensiones as $ext) {
    $estado = extension_loaded($ext) ? '✅' : '❌';
    echo "<li>{$estado} {$ext}</li>";
}

// Verificar soporte Argon2
$tieneArgon2 = defined('PASSWORD_ARGON2ID');
echo "<li>" . ($tieneArgon2 ? '✅' : '❌') . " Argon2id</li>";
echo "</ul></ul>";

// Conectar a la base de datos
try {
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "<p>✅ Conexión a base de datos exitosa</p>";
} catch (PDOException $e) {
    die("<p>❌ Error de conexión: " . $e->getMessage() . "</p>");
}

// Generar hash de contraseña
echo "<h3>Generando Hash de Contraseña:</h3>";

// Intentar Argon2id primero, si no BCrypt
if ($tieneArgon2) {
    $hash = password_hash($nuevaPassword, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
    $algoritmo = 'Argon2id';
} else {
    $hash = password_hash($nuevaPassword, PASSWORD_BCRYPT, [
        'cost' => 12
    ]);
    $algoritmo = 'BCrypt';
    
    echo "<p>⚠️ <strong>IMPORTANTE:</strong> Tu servidor no soporta Argon2id. Se usará BCrypt.</p>";
    echo "<p>Debes actualizar la función hashPassword en config/security.php</p>";
}

echo "<p><strong>Algoritmo usado:</strong> {$algoritmo}</p>";
echo "<p><strong>Hash generado:</strong> <code style='word-break:break-all;'>{$hash}</code></p>";

// Actualizar en base de datos
try {
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ?, intentos_fallidos = 0, bloqueado_hasta = NULL WHERE username = ?");
    $resultado = $stmt->execute([$hash, $username]);
    
    if ($stmt->rowCount() > 0) {
        echo "<h3>✅ Contraseña actualizada exitosamente</h3>";
        echo "<p><strong>Usuario:</strong> {$username}</p>";
        echo "<p><strong>Nueva contraseña:</strong> {$nuevaPassword}</p>";
    } else {
        // Verificar si el usuario existe
        $check = $pdo->prepare("SELECT usuario_id, username FROM usuarios WHERE username = ?");
        $check->execute([$username]);
        $user = $check->fetch();
        
        if (!$user) {
            echo "<h3>⚠️ Usuario '{$username}' no encontrado</h3>";
            echo "<p>Usuarios disponibles:</p><ul>";
            $todos = $pdo->query("SELECT username, email FROM usuarios LIMIT 10")->fetchAll();
            foreach ($todos as $u) {
                echo "<li>{$u['username']} ({$u['email']})</li>";
            }
            echo "</ul>";
        } else {
            echo "<h3>⚠️ La contraseña ya tenía ese valor o hubo un problema</h3>";
        }
    }
} catch (PDOException $e) {
    echo "<h3>❌ Error al actualizar:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}

// Si no soporta Argon2, mostrar código para modificar
if (!$tieneArgon2) {
    echo "<hr>";
    echo "<h3>🔧 Modificación requerida en config/security.php</h3>";
    echo "<p>Tu servidor no soporta Argon2id. Modifica la función <code>hashPassword</code>:</p>";
    echo "<pre style='background:#f5f5f5;padding:15px;border-radius:5px;'>";
    echo htmlspecialchars('
public static function hashPassword($password) {
    // Usar BCrypt como alternativa a Argon2id
    return password_hash($password, PASSWORD_BCRYPT, [
        \'cost\' => 12
    ]);
}
');
    echo "</pre>";
}

echo "<hr>";
echo "<p style='color:red;'><strong>⚠️ IMPORTANTE: Elimina este archivo (reset_password.php) después de usarlo por seguridad.</strong></p>";
?>
