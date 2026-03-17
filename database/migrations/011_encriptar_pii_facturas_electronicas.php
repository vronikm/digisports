<?php
/**
 * Migración 011 — Encriptar PII en facturas_electronicas (LOPDP Ecuador)
 *
 * Encripta los campos de datos personales que quedaron en texto plano
 * en registros anteriores a esta corrección:
 *   - fac_cliente_identificacion  (RUC / Cédula)
 *   - fac_cliente_email
 *   - fac_cliente_telefono
 *   - fac_cliente_direccion
 *
 * Uso (CLI):
 *   php database/migrations/011_encriptar_pii_facturas_electronicas.php
 *
 * Idempotente: omite valores que ya tienen prefijo ENC::
 */

// ── Bootstrap mínimo ───────────────────────────────────────────
$basePath = realpath(__DIR__ . '/../../');
if (!$basePath) {
    fwrite(STDERR, "No se pudo determinar BASE_PATH.\n");
    exit(1);
}

define('BASE_PATH', $basePath);

// Cargar .env y helpers básicos
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
    }
}

function env(string $key, $default = null) {
    return $_ENV[$key] ?? $default;
}

// Cargar DataProtection
$dpFile = BASE_PATH . '/app/services/DataProtection.php';
if (!file_exists($dpFile)) {
    fwrite(STDERR, "No se encontró DataProtection.php en $dpFile\n");
    exit(1);
}
require_once $dpFile;

// Conexión a BD
$dsn  = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    env('DB_HOST', '127.0.0.1'),
    env('DB_PORT', '3306'),
    env('DB_NAME', 'digisports_core')
);
try {
    $pdo = new PDO($dsn, env('DB_USER', 'root'), env('DB_PASS', ''), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "Error de conexión: " . $e->getMessage() . "\n");
    exit(1);
}

// ── Campos a encriptar ─────────────────────────────────────────
$campos = [
    'fac_cliente_identificacion',
    'fac_cliente_email',
    'fac_cliente_telefono',
    'fac_cliente_direccion',
];

echo "=== Migración 011: Encriptar PII en facturas_electronicas ===\n\n";

// ── Obtener todos los registros ────────────────────────────────
$stmt = $pdo->query("SELECT fac_id, " . implode(', ', $campos) . " FROM facturas_electronicas");
$rows = $stmt->fetchAll();

$total      = count($rows);
$actualizados = 0;
$omitidos     = 0;
$errores      = 0;

echo "Registros encontrados: $total\n\n";

$updateSql = "UPDATE facturas_electronicas SET " . implode(', ', array_map(fn($c) => "$c = ?", $campos)) . " WHERE fac_id = ?";
$updateStmt = $pdo->prepare($updateSql);

foreach ($rows as $row) {
    $id          = $row['fac_id'];
    $necesitaUpdate = false;
    $valores     = [];

    foreach ($campos as $campo) {
        $valor = $row[$campo];

        if ($valor === null || $valor === '') {
            // NULL o vacío: se puede cifrar null igualmente
            $valores[] = DataProtection::encrypt($valor);
            // Solo marcamos update si hay cambio real
        } elseif (DataProtection::isEncrypted($valor)) {
            // Ya cifrado: no tocar
            $valores[] = $valor;
        } else {
            // Texto plano: cifrar
            $encriptado = DataProtection::encrypt($valor);
            $valores[] = $encriptado;
            $necesitaUpdate = true;
        }
    }

    if (!$necesitaUpdate) {
        $omitidos++;
        continue;
    }

    $valores[] = $id;

    try {
        $updateStmt->execute($valores);
        $actualizados++;
        echo "  [OK] fac_id=$id — campos encriptados\n";
    } catch (PDOException $e) {
        $errores++;
        fwrite(STDERR, "  [ERROR] fac_id=$id — " . $e->getMessage() . "\n");
    }
}

echo "\n=== Resumen ===\n";
echo "Total procesados : $total\n";
echo "Actualizados     : $actualizados\n";
echo "Ya encriptados   : $omitidos\n";
echo "Errores          : $errores\n";
echo "\nMigración " . ($errores === 0 ? "COMPLETADA EXITOSAMENTE" : "COMPLETADA CON ERRORES") . ".\n";
exit($errores > 0 ? 1 : 0);
