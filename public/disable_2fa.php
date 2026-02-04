<?php
// Deshabilitar 2FA temporalmente para pruebas

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'digisports_core';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== DESHABILITAR 2FA ===\n\n";
    
    // Deshabilitar 2FA para superadmin
    $stmt = $pdo->prepare("UPDATE usuarios SET requiere_2fa = 'N' WHERE username = 'superadmin'");
    $result = $stmt->execute();
    
    if ($result) {
        echo "✓ 2FA deshabilitado para usuario: superadmin\n\n";
        echo "Ahora puedes loguearte sin código 2FA:\n";
        echo "  - Usuario: superadmin\n";
        echo "  - Contraseña: Admin@2024\n\n";
        
        // Verificar
        $stmt = $pdo->query("SELECT usuario_id, username, requiere_2fa FROM usuarios WHERE username = 'superadmin'");
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Verificación:\n";
        echo "  Requiere 2FA: " . ($u['requiere_2fa'] === 'S' ? 'Sí' : 'No') . "\n";
    } else {
        echo "✗ Error al actualizar\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
