<?php
// Script para generar hash correcto y actualizar usuario

$password = 'Admin@2024';

// Generar hash correcto con password_hash (usa bcrypt por defecto)
// pero podemos usar Argon2id si está disponible
if (defined('PASSWORD_ARGON2ID')) {
    $hash = password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3,
    ]);
} else {
    // Fallback a bcrypt
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

echo "=== GENERADOR DE HASH DE CONTRASEÑA ===\n\n";
echo "Contraseña: Admin@2024\n";
echo "Hash generado:\n$hash\n\n";

// Verificar que el hash funciona
$verify = password_verify($password, $hash);
echo "Verificación de hash: " . ($verify ? "✓ CORRECTO" : "✗ INCORRECTO") . "\n\n";

// Conectar y actualizar
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'digisports_core';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== ACTUALIZAR BASE DE DATOS ===\n\n";
    
    // Actualizar el usuario
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE username = 'superadmin'");
    $result = $stmt->execute([$hash]);
    
    if ($result) {
        echo "✓ Contraseña actualizada para usuario: superadmin\n";
        echo "✓ Ahora puedes loguearte con:\n";
        echo "  - Usuario: superadmin\n";
        echo "  - Contraseña: Admin@2024\n\n";
    } else {
        echo "✗ Error al actualizar\n";
    }
    
    // Verificar que se actualizó
    $stmt = $pdo->query("SELECT usuario_id, username, password FROM usuarios WHERE username = 'superadmin'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "=== VERIFICACIÓN ===\n";
        echo "Usuario ID: {$user['usuario_id']}\n";
        echo "Username: {$user['username']}\n";
        echo "Password hash (primeros 50 chars): " . substr($user['password'], 0, 50) . "...\n";
        
        // Verificar que funciona
        $verify2 = password_verify('Admin@2024', $user['password']);
        echo "Verificación de password: " . ($verify2 ? "✓ OK" : "✗ FALLO") . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error de BD: " . $e->getMessage() . "\n";
}
?>
