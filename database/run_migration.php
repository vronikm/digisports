<?php
/**
 * Runner para ejecutar SQL de migración fase 1
 * Uso: php database/run_migration.php
 */
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=digisports_core;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $sqlFile = __DIR__ . '/fase1_vistas_compatibilidad.sql';
    $sql = file_get_contents($sqlFile);
    
    // Separar por punto y coma, ignorar líneas vacías y comentarios
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $ok = 0;
    $err = 0;
    
    foreach ($statements as $stmt) {
        // Ignorar vacíos y comentarios puros
        if (empty($stmt) || preg_match('/^--/', $stmt)) continue;
        // Limpiar comentarios al inicio
        $clean = preg_replace('/^--.*$/m', '', $stmt);
        $clean = trim($clean);
        if (empty($clean)) continue;
        
        try {
            $pdo->exec($clean);
            $ok++;
            echo "OK: " . substr(preg_replace('/\s+/', ' ', $clean), 0, 80) . "\n";
        } catch (Exception $e) {
            $err++;
            echo "ERR: " . $e->getMessage() . "\n";
            echo "  SQL: " . substr(preg_replace('/\s+/', ' ', $clean), 0, 100) . "\n";
        }
    }
    
    echo "\n=== Resultado: OK=$ok, ERR=$err ===\n";
    
} catch (Exception $e) {
    echo "ERROR de conexión: " . $e->getMessage() . "\n";
}
