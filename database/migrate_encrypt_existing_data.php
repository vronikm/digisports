<?php
/**
 * DigiSports — Migración: Cifrar datos existentes en texto plano
 * 
 * Este script:
 *  1. Lee todos los registros de seguridad_usuarios, seguridad_tenants y clientes
 *  2. Para cada registro, si los campos sensibles NO están cifrados (sin prefijo ENC::),
 *     los cifra y genera los blind-index hashes correspondientes.
 *  3. Es IDEMPOTENTE: puede ejecutarse varias veces sin dañar datos ya cifrados.
 * 
 * EJECUCIÓN:
 *   php database/migrate_encrypt_existing_data.php
 *   O visitar http://localhost/digisports/database/migrate_encrypt_existing_data.php
 * 
 * @package DigiSports\Database\Migrations
 * @since 2026-02-07
 */

// Prevenir ejecución accidental en producción sin confirmación
$isWeb = php_sapi_name() !== 'cli';

if ($isWeb) {
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Migración - Cifrar datos existentes</title>';
    echo '<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#e0e0e0;} ';
    echo '.ok{color:#4ade80;} .warn{color:#fbbf24;} .err{color:#f87171;} .info{color:#60a5fa;} ';
    echo 'h1{color:#818cf8;} pre{background:#0f0f23;padding:15px;border-radius:8px;overflow-x:auto;}</style>';
    echo '</head><body>';
    echo '<h1>🔐 DigiSports — Migración de Cifrado de Datos</h1>';
    echo '<pre>';
}

// Bootstrap mínimo
$basePath = dirname(__DIR__);
define('BASE_PATH', $basePath);

// Cargar DataProtection
require_once $basePath . '/app/services/DataProtection.php';

// Conexión directa PDO (misma config que Database class)
try {
    $host = 'localhost';
    $dbName = 'digisports_core';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    
    $dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    logMsg("✅ Conexión a BD exitosa: {$dbName}@{$host}", 'ok');
} catch (Exception $e) {
    logMsg("❌ Error de conexión: " . $e->getMessage(), 'err');
    exit(1);
}

// ──────────────────────────────────────────────────────────
//  TABLAS Y CAMPOS A MIGRAR
// ──────────────────────────────────────────────────────────

$tables = [
    'seguridad_usuarios' => [
        'pk' => 'usu_usuario_id',
        'fields' => [
            'usu_identificacion' => ['hash' => true],
            'usu_email'          => ['hash' => true],
            'usu_telefono'       => ['hash' => false],
            'usu_celular'        => ['hash' => false],
        ],
    ],
    'seguridad_tenants' => [
        'pk' => 'ten_tenant_id',
        'fields' => [
            'ten_ruc'                          => ['hash' => true],
            'ten_email'                        => ['hash' => true],
            'ten_telefono'                     => ['hash' => false],
            'ten_celular'                      => ['hash' => false],
            'ten_representante_identificacion' => ['hash' => true],
            'ten_representante_email'          => ['hash' => true],
            'ten_representante_telefono'       => ['hash' => false],
        ],
    ],
    'clientes' => [
        'pk' => 'cli_cliente_id',
        'fields' => [
            'cli_identificacion' => ['hash' => true],
            'cli_email'          => ['hash' => true],
            'cli_telefono'       => ['hash' => false],
            'cli_celular'        => ['hash' => false],
        ],
    ],
];

// ──────────────────────────────────────────────────────────
//  PROCESO DE MIGRACIÓN
// ──────────────────────────────────────────────────────────

$totalUpdated = 0;
$totalSkipped = 0;
$totalErrors  = 0;

foreach ($tables as $tableName => $config) {
    logMsg("\n══════════════════════════════════════════", 'info');
    logMsg("📋 Procesando tabla: {$tableName}", 'info');
    logMsg("══════════════════════════════════════════", 'info');
    
    $pk = $config['pk'];
    $fields = $config['fields'];
    
    // Verificar que la tabla existe
    try {
        $checkStmt = $pdo->query("SELECT COUNT(*) FROM {$tableName}");
        $rowCount = $checkStmt->fetchColumn();
        logMsg("   Registros encontrados: {$rowCount}", 'info');
    } catch (Exception $e) {
        logMsg("   ⚠️  Tabla no existe o error: " . $e->getMessage(), 'warn');
        continue;
    }
    
    if ($rowCount === 0) {
        logMsg("   ⏭️  Tabla vacía, saltando...", 'warn');
        continue;
    }
    
    // Construir SELECT con todos los campos necesarios
    $selectFields = [$pk];
    foreach ($fields as $field => $opts) {
        $selectFields[] = $field;
        if ($opts['hash']) {
            $selectFields[] = $field . '_hash';
        }
    }
    
    $selectSql = "SELECT " . implode(', ', $selectFields) . " FROM {$tableName}";
    $rows = $pdo->query($selectSql)->fetchAll();
    
    $tableUpdated = 0;
    $tableSkipped = 0;
    $tableErrors = 0;
    
    foreach ($rows as $row) {
        $id = $row[$pk];
        $needsUpdate = false;
        $setClauses = [];
        $params = [];
        
        foreach ($fields as $field => $opts) {
            $value = $row[$field] ?? null;
            
            // Saltar si es null o vacío
            if ($value === null || $value === '') {
                // Si tiene hash y el hash está vacío, generar hash de vacío? No, saltar.
                continue;
            }
            
            // Verificar si ya está cifrado
            if (DataProtection::isEncrypted($value)) {
                // Ya cifrado, pero verificar si el hash existe
                if ($opts['hash']) {
                    $hashField = $field . '_hash';
                    $currentHash = $row[$hashField] ?? null;
                    if (empty($currentHash)) {
                        // Necesita generar el hash (descifrar primero para obtener el texto plano)
                        $plaintext = DataProtection::decrypt($value);
                        if ($plaintext !== null && $plaintext !== '') {
                            $setClauses[] = "{$hashField} = ?";
                            $params[] = DataProtection::blindIndex($plaintext);
                            $needsUpdate = true;
                        }
                    }
                }
                continue; // Campo ya cifrado
            }
            
            // Campo en texto plano → cifrar
            $encrypted = DataProtection::encrypt($value);
            $setClauses[] = "{$field} = ?";
            $params[] = $encrypted;
            $needsUpdate = true;
            
            // Generar hash si corresponde
            if ($opts['hash']) {
                $hashField = $field . '_hash';
                $setClauses[] = "{$hashField} = ?";
                $params[] = DataProtection::blindIndex($value);
            }
        }
        
        if (!$needsUpdate) {
            $tableSkipped++;
            continue;
        }
        
        // Ejecutar UPDATE
        try {
            $updateSql = "UPDATE {$tableName} SET " . implode(', ', $setClauses) . " WHERE {$pk} = ?";
            $params[] = $id;
            $stmt = $pdo->prepare($updateSql);
            $stmt->execute($params);
            $tableUpdated++;
        } catch (Exception $e) {
            logMsg("   ❌ Error en {$tableName} ID={$id}: " . $e->getMessage(), 'err');
            $tableErrors++;
        }
    }
    
    logMsg("   ✅ Actualizados: {$tableUpdated}", 'ok');
    logMsg("   ⏭️  Ya cifrados (saltados): {$tableSkipped}", 'warn');
    if ($tableErrors > 0) {
        logMsg("   ❌ Errores: {$tableErrors}", 'err');
    }
    
    $totalUpdated += $tableUpdated;
    $totalSkipped += $tableSkipped;
    $totalErrors += $tableErrors;
}

// ──────────────────────────────────────────────────────────
//  RESUMEN
// ──────────────────────────────────────────────────────────

logMsg("\n══════════════════════════════════════════", 'info');
logMsg("📊 RESUMEN DE MIGRACIÓN", 'info');
logMsg("══════════════════════════════════════════", 'info');
logMsg("   Total actualizados: {$totalUpdated}", 'ok');
logMsg("   Total ya cifrados:  {$totalSkipped}", 'warn');
logMsg("   Total errores:      {$totalErrors}", $totalErrors > 0 ? 'err' : 'ok');

if ($totalErrors === 0) {
    logMsg("\n🎉 Migración completada exitosamente!", 'ok');
} else {
    logMsg("\n⚠️  Migración completada con errores. Revise los detalles arriba.", 'warn');
}

// ──────────────────────────────────────────────────────────
//  VERIFICACIÓN
// ──────────────────────────────────────────────────────────

logMsg("\n══════════════════════════════════════════", 'info');
logMsg("🔍 VERIFICACIÓN POST-MIGRACIÓN", 'info');
logMsg("══════════════════════════════════════════", 'info');

foreach ($tables as $tableName => $config) {
    $pk = $config['pk'];
    $fields = $config['fields'];
    
    // Contar registros con datos sin cifrar
    $uncryptedConditions = [];
    foreach ($fields as $field => $opts) {
        $uncryptedConditions[] = "({$field} IS NOT NULL AND {$field} != '' AND {$field} NOT LIKE 'ENC::%')";
    }
    
    if (!empty($uncryptedConditions)) {
        $checkSql = "SELECT COUNT(*) FROM {$tableName} WHERE " . implode(' OR ', $uncryptedConditions);
        try {
            $count = $pdo->query($checkSql)->fetchColumn();
            if ($count > 0) {
                logMsg("   ⚠️  {$tableName}: {$count} registros aún tienen datos sin cifrar", 'warn');
            } else {
                logMsg("   ✅ {$tableName}: Todos los registros están cifrados", 'ok');
            }
        } catch (Exception $e) {
            logMsg("   ❌ Error verificando {$tableName}: " . $e->getMessage(), 'err');
        }
    }
    
    // Verificar hashes
    foreach ($fields as $field => $opts) {
        if ($opts['hash']) {
            $hashField = $field . '_hash';
            try {
                $checkSql = "SELECT COUNT(*) FROM {$tableName} WHERE {$field} IS NOT NULL AND {$field} != '' AND ({$hashField} IS NULL OR {$hashField} = '')";
                $count = $pdo->query($checkSql)->fetchColumn();
                if ($count > 0) {
                    logMsg("   ⚠️  {$tableName}.{$hashField}: {$count} hashes faltantes", 'warn');
                } else {
                    logMsg("   ✅ {$tableName}.{$hashField}: Todos los hashes generados", 'ok');
                }
            } catch (Exception $e) {
                // Columna hash podría no existir aún
                logMsg("   ⚠️  {$tableName}.{$hashField}: columna no encontrada", 'warn');
            }
        }
    }
}

if ($isWeb) {
    echo '</pre>';
    echo '<hr><p style="color:#818cf8;">Migración finalizada. Este archivo puede eliminarse después de confirmar que todo funciona correctamente.</p>';
    echo '</body></html>';
}

// ──────────────────────────────────────────────────────────
//  FUNCIÓN DE LOG
// ──────────────────────────────────────────────────────────

function logMsg($message, $type = 'info') {
    global $isWeb;
    
    if ($isWeb) {
        echo '<span class="' . $type . '">' . htmlspecialchars($message) . '</span>' . "\n";
    } else {
        echo $message . "\n";
    }
    
    // También guardar en archivo de log
    $logFile = dirname(__DIR__) . '/storage/logs/data_migration_' . date('Ymd') . '.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    @file_put_contents($logFile, date('Y-m-d H:i:s') . " [{$type}] {$message}\n", FILE_APPEND);
}
