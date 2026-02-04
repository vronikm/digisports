<?php
// Verificar datos en la base de datos

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'digisports_core';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== VERIFICACIÓN DE DATOS ===\n\n";
    
    // Verificar tenants
    echo "1. TENANTS:\n";
    $result = $pdo->query("SELECT * FROM tenants");
    $tenants = $result->fetchAll(PDO::FETCH_ASSOC);
    if (empty($tenants)) {
        echo "   ❌ No hay tenants\n";
    } else {
        foreach ($tenants as $t) {
            echo "   ✓ Tenant ID: {$t['tenant_id']}, RUC: {$t['ruc']}, Estado: {$t['estado']}\n";
        }
    }
    
    echo "\n2. ROLES:\n";
    $result = $pdo->query("SELECT * FROM roles LIMIT 5");
    $roles = $result->fetchAll(PDO::FETCH_ASSOC);
    if (empty($roles)) {
        echo "   ❌ No hay roles\n";
    } else {
        foreach ($roles as $r) {
            echo "   ✓ Role ID: {$r['rol_id']}, Código: {$r['codigo']}, Tenant: {$r['tenant_id']}\n";
        }
    }
    
    echo "\n3. USUARIOS:\n";
    $result = $pdo->query("SELECT usuario_id, tenant_id, username, email, estado FROM usuarios");
    $users = $result->fetchAll(PDO::FETCH_ASSOC);
    if (empty($users)) {
        echo "   ❌ No hay usuarios\n";
    } else {
        foreach ($users as $u) {
            echo "   ✓ User ID: {$u['usuario_id']}, Username: {$u['username']}, Email: {$u['email']}, Tenant: {$u['tenant_id']}, Estado: {$u['estado']}\n";
        }
    }
    
    echo "\n4. PLANES:\n";
    $result = $pdo->query("SELECT * FROM planes_suscripcion");
    $planes = $result->fetchAll(PDO::FETCH_ASSOC);
    if (empty($planes)) {
        echo "   ❌ No hay planes\n";
    } else {
        foreach ($planes as $p) {
            echo "   ✓ Plan ID: {$p['plan_id']}, Código: {$p['codigo']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}
?>
