<?php
/**
 * Migración: Agregar columnas hash y ampliar columnas para cifrado
 * Compatible con MySQL 5.7+ y 8.0+
 */
require_once __DIR__ . '/config/app.php';
require_once BASE_PATH . '/config/database.php';

$db = Database::getInstance()->getConnection();

function columnExists($db, $table, $column) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $column]);
    return $stmt->fetchColumn() > 0;
}

function indexExists($db, $table, $index) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?");
    $stmt->execute([$table, $index]);
    return $stmt->fetchColumn() > 0;
}

$changes = [
    ['seguridad_usuarios', 'usu_identificacion_hash', 'VARCHAR(32) NULL DEFAULT NULL', 'usu_identificacion'],
    ['seguridad_usuarios', 'usu_email_hash', 'VARCHAR(32) NULL DEFAULT NULL', 'usu_email'],
    ['seguridad_tenants', 'ten_ruc_hash', 'VARCHAR(32) NULL DEFAULT NULL', 'ten_ruc'],
    ['seguridad_tenants', 'ten_email_hash', 'VARCHAR(32) NULL DEFAULT NULL', 'ten_email'],
    ['seguridad_tenants', 'ten_representante_identificacion_hash', 'VARCHAR(32) NULL DEFAULT NULL', 'ten_representante_identificacion'],
    ['seguridad_tenants', 'ten_representante_email_hash', 'VARCHAR(32) NULL DEFAULT NULL', 'ten_representante_email'],
    ['clientes', 'cli_identificacion_hash', 'VARCHAR(32) NULL DEFAULT NULL', 'cli_identificacion'],
    ['clientes', 'cli_email_hash', 'VARCHAR(32) NULL DEFAULT NULL', 'cli_email'],
];

$indexes = [
    ['seguridad_usuarios', 'idx_usu_identificacion_hash', 'usu_identificacion_hash'],
    ['seguridad_usuarios', 'idx_usu_email_hash', 'usu_email_hash'],
    ['seguridad_tenants', 'idx_ten_ruc_hash', 'ten_ruc_hash'],
    ['seguridad_tenants', 'idx_ten_email_hash', 'ten_email_hash'],
    ['seguridad_tenants', 'idx_ten_rep_id_hash', 'ten_representante_identificacion_hash'],
    ['seguridad_tenants', 'idx_ten_rep_email_hash', 'ten_representante_email_hash'],
    ['clientes', 'idx_cli_identificacion_hash', 'cli_identificacion_hash'],
    ['clientes', 'idx_cli_email_hash', 'cli_email_hash'],
];

$modifyCols = [
    ['seguridad_usuarios', 'usu_identificacion', 'VARCHAR(255) NULL'],
    ['seguridad_usuarios', 'usu_email', 'VARCHAR(500) NULL'],
    ['seguridad_usuarios', 'usu_telefono', 'VARCHAR(255) NULL'],
    ['seguridad_usuarios', 'usu_celular', 'VARCHAR(255) NULL'],
    ['seguridad_tenants', 'ten_ruc', 'VARCHAR(255) NULL'],
    ['seguridad_tenants', 'ten_email', 'VARCHAR(500) NULL'],
    ['seguridad_tenants', 'ten_telefono', 'VARCHAR(255) NULL'],
    ['seguridad_tenants', 'ten_celular', 'VARCHAR(255) NULL'],
    ['seguridad_tenants', 'ten_representante_identificacion', 'VARCHAR(255) NULL'],
    ['seguridad_tenants', 'ten_representante_email', 'VARCHAR(500) NULL'],
    ['seguridad_tenants', 'ten_representante_telefono', 'VARCHAR(255) NULL'],
    ['clientes', 'cli_identificacion', 'VARCHAR(255) NULL'],
    ['clientes', 'cli_email', 'VARCHAR(500) NULL'],
    ['clientes', 'cli_telefono', 'VARCHAR(255) NULL'],
    ['clientes', 'cli_celular', 'VARCHAR(255) NULL'],
];

echo "=== Migracion: Proteccion de Datos ===" . PHP_EOL . PHP_EOL;

echo "-- Agregando columnas _hash --" . PHP_EOL;
foreach ($changes as $c) {
    $table = $c[0]; $col = $c[1]; $type = $c[2]; $after = $c[3];
    if (columnExists($db, $table, $col)) {
        echo "  SKIP: {$table}.{$col} ya existe" . PHP_EOL;
    } else {
        try {
            $db->exec("ALTER TABLE {$table} ADD COLUMN {$col} {$type} AFTER {$after}");
            echo "  OK:   {$table}.{$col} creada" . PHP_EOL;
        } catch (Exception $e) {
            echo "  ERR:  {$table}.{$col} - " . $e->getMessage() . PHP_EOL;
        }
    }
}

echo PHP_EOL . "-- Creando indices --" . PHP_EOL;
foreach ($indexes as $ix) {
    $table = $ix[0]; $idxName = $ix[1]; $col = $ix[2];
    if (indexExists($db, $table, $idxName)) {
        echo "  SKIP: {$idxName} ya existe" . PHP_EOL;
    } else {
        try {
            $db->exec("CREATE INDEX {$idxName} ON {$table} ({$col})");
            echo "  OK:   {$idxName} creado" . PHP_EOL;
        } catch (Exception $e) {
            echo "  ERR:  {$idxName} - " . $e->getMessage() . PHP_EOL;
        }
    }
}

echo PHP_EOL . "-- Ampliando columnas para cifrado --" . PHP_EOL;
foreach ($modifyCols as $mc) {
    $table = $mc[0]; $col = $mc[1]; $type = $mc[2];
    try {
        $db->exec("ALTER TABLE {$table} MODIFY COLUMN {$col} {$type}");
        echo "  OK:   {$table}.{$col} -> {$type}" . PHP_EOL;
    } catch (Exception $e) {
        echo "  ERR:  {$table}.{$col} - " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "=== Migracion completada ===" . PHP_EOL;
