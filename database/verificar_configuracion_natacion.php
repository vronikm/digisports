<?php
/**
 * Script de verificación de la tabla natacion_configuracion
 * Uso: php verificar_configuracion_natacion.php
 */

// Cargar configuración
require_once dirname(dirname(__FILE__)) . '/config/database.php';

try {
    // Obtener conexión a la BD
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "\n✓ Conexión a base de datos exitosa.\n\n";
    
    // Verificar estructura de la tabla
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "VERIFICACIÓN DE TABLA: natacion_configuracion\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Verificar existencia de tabla
    $stm = $pdo->prepare("SHOW TABLES LIKE 'natacion_configuracion'");
    $stm->execute();
    $tableExists = $stm->fetchColumn() !== false;
    
    if (!$tableExists) {
        echo "✗ ERROR: La tabla 'natacion_configuracion' NO existe!\n";
        exit(1);
    }
    echo "✓ Tabla 'natacion_configuracion' existe.\n\n";
    
    // Verificar estructura de columnas
    $stm = $pdo->prepare("DESCRIBE natacion_configuracion");
    $stm->execute();
    $columns = $stm->fetchAll();
    
    echo "Columnas encontradas:\n";
    $expectedColumns = ['ncg_config_id', 'ncg_tenant_id', 'ncg_clave', 'ncg_valor', 'ncg_tipo', 'ncg_descripcion', 'ncg_created_at', 'ncg_updated_at'];
    $foundColumns = [];
    
    foreach ($columns as $col) {
        $colName = $col['Field'];
        $foundColumns[] = $colName;
        $isExpected = in_array($colName, $expectedColumns);
        $marker = $isExpected ? '✓' : '⚠';
        echo "  {$marker} {$colName} ({$col['Type']})\n";
    }
    echo "\n";
    
    // Verificar columnas faltantes
    $missingColumns = array_diff($expectedColumns, $foundColumns);
    if (!empty($missingColumns)) {
        echo "✗ COLUMNAS FALTANTES:\n";
        foreach ($missingColumns as $col) {
            echo "  - {$col}\n";
        }
        echo "\n";
    }
    
    // Verificar columnas innecesarias
    $extraColumns = array_diff($foundColumns, $expectedColumns);
    if (!empty($extraColumns)) {
        echo "⚠ COLUMNAS ADICIONALES (no esperadas):\n";
        foreach ($extraColumns as $col) {
            echo "  - {$col}\n";
        }
        echo "\n";
    }
    
    // Contar registros
    $stm = $pdo->prepare("SELECT COUNT(*) FROM natacion_configuracion");
    $stm->execute();
    $count = (int)$stm->fetchColumn();
    echo "Registros en tabla: {$count}\n\n";
    
    // Mostrar algunos registros
    if ($count > 0) {
        echo "Primeros registros:\n";
        $stm = $pdo->prepare("SELECT * FROM natacion_configuracion LIMIT 5");
        $stm->execute();
        $records = $stm->fetchAll();
        
        foreach ($records as $rec) {
            echo "  ID {$rec['ncg_config_id']}: {$rec['ncg_clave']} = {$rec['ncg_valor']} ({$rec['ncg_tipo']})\n";
        }
    } else {
        echo "⚠ No hay registros en la tabla. Considera ejecutar los INSERTs desde el SQL.\n";
    }
    
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✓ VERIFICACIÓN COMPLETADA\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
} catch (\Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
