<?php
/**
 * Prueba rápida: verificar descifrado de datos
 * Ejecutar: php database/test_decrypt.php
 */
require_once dirname(__DIR__) . '/app/services/DataProtection.php';

$pdo = new PDO('mysql:host=localhost;dbname=digisports_core;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "=== USUARIOS ===\n";
$rows = $pdo->query('SELECT usu_usuario_id, usu_username, usu_email, usu_identificacion, usu_email_hash, usu_identificacion_hash FROM seguridad_usuarios')->fetchAll();
foreach ($rows as $r) {
    $d = DataProtection::decryptRow('seguridad_usuarios', $r);
    echo "ID={$d['usu_usuario_id']} user={$d['usu_username']} email={$d['usu_email']} ident={$d['usu_identificacion']}\n";
    echo "  email_hash={$r['usu_email_hash']}\n";
    
    // Verificar blind index funciona para login
    $testHash = DataProtection::blindIndex($d['usu_email']);
    echo "  blind_index_match: " . ($testHash === $r['usu_email_hash'] ? 'SI ✅' : 'NO ❌') . "\n";
}

echo "\n=== TENANTS ===\n";
$rows = $pdo->query('SELECT ten_tenant_id, ten_ruc, ten_email, ten_ruc_hash, ten_email_hash FROM seguridad_tenants')->fetchAll();
foreach ($rows as $r) {
    $d = DataProtection::decryptRow('seguridad_tenants', $r);
    echo "ID={$d['ten_tenant_id']} ruc={$d['ten_ruc']} email={$d['ten_email']}\n";
    
    $testHash = DataProtection::blindIndex($d['ten_ruc']);
    echo "  ruc_hash_match: " . ($testHash === $r['ten_ruc_hash'] ? 'SI ✅' : 'NO ❌') . "\n";
}

echo "\n=== CLIENTES ===\n";
$rows = $pdo->query('SELECT cli_cliente_id, cli_identificacion, cli_email, cli_identificacion_hash, cli_email_hash FROM clientes')->fetchAll();
foreach ($rows as $r) {
    $d = DataProtection::decryptRow('clientes', $r);
    echo "ID={$d['cli_cliente_id']} ident={$d['cli_identificacion']} email={$d['cli_email']}\n";
}

echo "\n=== TEST LOGIN: buscar por email hash ===\n";
// Simular búsqueda de login por email
$testEmail = $rows ? DataProtection::decrypt($rows[0]['cli_email']) : 'test@test.com';
// Probemos con el primer usuario
$firstUser = $pdo->query('SELECT usu_email FROM seguridad_usuarios LIMIT 1')->fetch();
$decryptedEmail = DataProtection::decrypt($firstUser['usu_email']);
echo "Email descifrado del primer usuario: {$decryptedEmail}\n";

$emailHash = DataProtection::blindIndex($decryptedEmail);
$stmt = $pdo->prepare("SELECT usu_usuario_id, usu_username FROM seguridad_usuarios WHERE usu_email_hash = ?");
$stmt->execute([$emailHash]);
$found = $stmt->fetch();
echo "Búsqueda por hash: " . ($found ? "ENCONTRADO ✅ (ID={$found['usu_usuario_id']}, user={$found['usu_username']})" : "NO ENCONTRADO ❌") . "\n";

echo "\n🏁 Todas las pruebas completadas.\n";
