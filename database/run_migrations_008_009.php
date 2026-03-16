<?php
/**
 * Ejecuta las migraciones 008 y 009 del módulo Formas de Pago
 * Uso: php database/run_migrations_008_009.php
 */
define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/env.php';
require_once BASE_PATH . '/config/database.php';

$db = Database::getInstance()->getConnection();

function runSql(PDO $db, string $file, string $label): void {
    if (!file_exists($file)) {
        echo "❌ No existe: $file\n";
        return;
    }

    $sql        = file_get_contents($file);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $ok = 0; $skip = 0; $err = 0;

    foreach ($statements as $st) {
        // Eliminar líneas de comentario (-- ...) del inicio del statement
        $lines    = explode("\n", $st);
        $sqlLines = array_filter($lines, fn($l) => !preg_match('/^\s*--/', $l));
        $clean    = trim(implode("\n", $sqlLines));

        if (empty($clean)) {
            $skip++;
            continue;
        }
        try {
            $db->exec($clean);
            $ok++;
        } catch (PDOException $e) {
            echo "  ⚠️  $label: " . $e->getMessage() . "\n";
            $err++;
        }
    }
    $icon = $err === 0 ? '✅' : '⚠️ ';
    echo "$icon $label: $ok ejecutados, $skip omitidos, $err errores\n";
}

echo "\n── Ejecutando migraciones Formas de Pago ──────────────────\n";
runSql($db, BASE_PATH . '/database/migrations/008_menu_formas_pago.sql',        'Migración 008 (menú)');
runSql($db, BASE_PATH . '/database/migrations/009_datos_iniciales_formas_pago.sql', 'Migración 009 (datos)');
echo "── Listo ──────────────────────────────────────────────────\n\n";
