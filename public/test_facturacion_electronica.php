<?php
/**
 * DigiSports - Script de Prueba de Facturación Electrónica
 * Verifica que todos los componentes estén funcionando
 * 
 * Ejecutar: http://localhost/digisports/public/test_facturacion_electronica.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración base
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/digisports/public/');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Facturación Electrónica SRI</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
</head>
<body class='bg-light'>
<div class='container py-5'>
    <h1 class='mb-4'><i class='fas fa-file-invoice-dollar text-primary'></i> Test Facturación Electrónica SRI</h1>
    <div class='card'>
        <div class='card-body'>";

$tests = [];
$allPassed = true;

// ============================================
// 1. Verificar archivos de configuración
// ============================================
echo "<h5 class='mt-3'><i class='fas fa-cog'></i> 1. Configuración</h5>";

$configFile = BASE_PATH . '/config/sri.php';
if (file_exists($configFile)) {
    $config = require $configFile;
    $tests['config'] = true;
    echo "<div class='alert alert-success'><i class='fas fa-check'></i> Archivo config/sri.php encontrado</div>";
    
    // Verificar datos del emisor
    if (!empty($config['emisor']['ruc'])) {
        echo "<div class='ms-4 text-muted'>RUC Emisor: <code>{$config['emisor']['ruc']}</code></div>";
    }
    if (!empty($config['ambiente'])) {
        $ambiente = $config['ambiente'] == '1' ? 'PRUEBAS' : 'PRODUCCIÓN';
        echo "<div class='ms-4 text-muted'>Ambiente: <span class='badge bg-info'>$ambiente</span></div>";
    }
} else {
    $tests['config'] = false;
    $allPassed = false;
    echo "<div class='alert alert-danger'><i class='fas fa-times'></i> Archivo config/sri.php NO encontrado</div>";
}

// ============================================
// 2. Verificar servicios
// ============================================
echo "<h5 class='mt-4'><i class='fas fa-code'></i> 2. Servicios SRI</h5>";

$services = [
    'FacturaElectronicaService' => '/app/services/SRI/FacturaElectronicaService.php',
    'FirmaElectronicaService' => '/app/services/SRI/FirmaElectronicaService.php',
    'WebServiceSRIService' => '/app/services/SRI/WebServiceSRIService.php',
    'RIDEService' => '/app/services/SRI/RIDEService.php',
];

foreach ($services as $name => $path) {
    $fullPath = BASE_PATH . $path;
    if (file_exists($fullPath)) {
        $tests[$name] = true;
        echo "<div class='alert alert-success py-2'><i class='fas fa-check'></i> $name</div>";
    } else {
        $tests[$name] = false;
        $allPassed = false;
        echo "<div class='alert alert-danger py-2'><i class='fas fa-times'></i> $name - NO encontrado</div>";
    }
}

// ============================================
// 3. Verificar modelo
// ============================================
echo "<h5 class='mt-4'><i class='fas fa-database'></i> 3. Modelo</h5>";

$modelFile = BASE_PATH . '/app/models/FacturaElectronica.php';
if (file_exists($modelFile)) {
    $tests['model'] = true;
    echo "<div class='alert alert-success'><i class='fas fa-check'></i> FacturaElectronica.php encontrado</div>";
} else {
    $tests['model'] = false;
    $allPassed = false;
    echo "<div class='alert alert-danger'><i class='fas fa-times'></i> FacturaElectronica.php NO encontrado</div>";
}

// ============================================
// 4. Verificar controlador
// ============================================
echo "<h5 class='mt-4'><i class='fas fa-sitemap'></i> 4. Controlador</h5>";

$controllerFile = BASE_PATH . '/app/controllers/facturacion/FacturaElectronicaController.php';
if (file_exists($controllerFile)) {
    $tests['controller'] = true;
    echo "<div class='alert alert-success'><i class='fas fa-check'></i> FacturaElectronicaController.php encontrado</div>";
} else {
    $tests['controller'] = false;
    $allPassed = false;
    echo "<div class='alert alert-danger'><i class='fas fa-times'></i> FacturaElectronicaController.php NO encontrado</div>";
}

// ============================================
// 5. Verificar vistas
// ============================================
echo "<h5 class='mt-4'><i class='fas fa-desktop'></i> 5. Vistas</h5>";

$views = [
    'index.php' => '/app/views/facturacion/facturas_electronicas/index.php',
    'ver.php' => '/app/views/facturacion/facturas_electronicas/ver.php',
];

foreach ($views as $name => $path) {
    $fullPath = BASE_PATH . $path;
    if (file_exists($fullPath)) {
        $tests['view_' . $name] = true;
        echo "<div class='alert alert-success py-2'><i class='fas fa-check'></i> Vista: $name</div>";
    } else {
        $tests['view_' . $name] = false;
        $allPassed = false;
        echo "<div class='alert alert-danger py-2'><i class='fas fa-times'></i> Vista: $name - NO encontrada</div>";
    }
}

// ============================================
// 6. Verificar directorios de storage
// ============================================
echo "<h5 class='mt-4'><i class='fas fa-folder'></i> 6. Directorios Storage</h5>";

$storageDirectories = [
    'XML Generados' => '/storage/sri/xml/generados',
    'XML Firmados' => '/storage/sri/xml/firmados',
    'XML Autorizados' => '/storage/sri/xml/autorizados',
    'RIDE' => '/storage/sri/ride',
    'Logs SRI' => '/storage/sri/logs',
    'Certificados' => '/storage/certificados',
];

foreach ($storageDirectories as $name => $path) {
    $fullPath = BASE_PATH . $path;
    if (is_dir($fullPath)) {
        $writable = is_writable($fullPath);
        $tests['dir_' . $name] = $writable;
        if ($writable) {
            echo "<div class='alert alert-success py-2'><i class='fas fa-check'></i> $name <span class='badge bg-success'>Escribible</span></div>";
        } else {
            $allPassed = false;
            echo "<div class='alert alert-warning py-2'><i class='fas fa-exclamation'></i> $name <span class='badge bg-warning'>Sin permisos de escritura</span></div>";
        }
    } else {
        $tests['dir_' . $name] = false;
        $allPassed = false;
        echo "<div class='alert alert-danger py-2'><i class='fas fa-times'></i> $name - NO existe</div>";
    }
}

// ============================================
// 7. Verificar tablas en base de datos
// ============================================
echo "<h5 class='mt-4'><i class='fas fa-table'></i> 7. Tablas en Base de Datos</h5>";

try {
    require_once BASE_PATH . '/config/database.php';
    $db = Database::getInstance()->getConnection();
    
    $tables = [
        'facturas_electronicas',
        'facturas_electronicas_detalle',
        'facturas_electronicas_detalle_impuestos',
        'facturas_electronicas_pagos',
        'facturas_electronicas_info_adicional',
        'facturas_electronicas_log',
        'facturas_electronicas_secuenciales',
    ];
    
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT 1 FROM $table LIMIT 1");
            $tests['table_' . $table] = true;
            echo "<div class='alert alert-success py-2'><i class='fas fa-check'></i> $table</div>";
        } catch (Exception $e) {
            $tests['table_' . $table] = false;
            $allPassed = false;
            echo "<div class='alert alert-danger py-2'><i class='fas fa-times'></i> $table - No existe o error</div>";
        }
    }
    
    // Verificar vista
    try {
        $stmt = $db->query("SELECT 1 FROM v_facturas_electronicas_resumen LIMIT 1");
        echo "<div class='alert alert-success py-2'><i class='fas fa-check'></i> Vista: v_facturas_electronicas_resumen</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-warning py-2'><i class='fas fa-exclamation'></i> Vista: v_facturas_electronicas_resumen - No existe</div>";
    }
    
    // Verificar secuencial inicial
    $stmt = $db->query("SELECT COUNT(*) as total FROM facturas_electronicas_secuenciales");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['total'] > 0) {
        echo "<div class='alert alert-info py-2'><i class='fas fa-info-circle'></i> Secuenciales configurados: {$result['total']}</div>";
    }
    
} catch (Exception $e) {
    $allPassed = false;
    echo "<div class='alert alert-danger'><i class='fas fa-times'></i> Error de conexión a BD: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// ============================================
// 8. Verificar extensiones PHP requeridas
// ============================================
echo "<h5 class='mt-4'><i class='fas fa-puzzle-piece'></i> 8. Extensiones PHP</h5>";

$extensions = ['openssl', 'soap', 'dom', 'curl', 'json'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        $tests['ext_' . $ext] = true;
        echo "<div class='alert alert-success py-2'><i class='fas fa-check'></i> $ext</div>";
    } else {
        $tests['ext_' . $ext] = false;
        $allPassed = false;
        echo "<div class='alert alert-danger py-2'><i class='fas fa-times'></i> $ext - NO instalada</div>";
    }
}

// ============================================
// 9. Test de generación de clave de acceso
// ============================================
echo "<h5 class='mt-4'><i class='fas fa-key'></i> 9. Test Clave de Acceso</h5>";

try {
    require_once BASE_PATH . '/app/services/SRI/FacturaElectronicaService.php';
    $facturaService = new \App\Services\SRI\FacturaElectronicaService();
    
    $claveAcceso = $facturaService->generarClaveAcceso(
        date('dmY'),      // Fecha emisión
        '01',             // Tipo comprobante (factura)
        '1792146739001',  // RUC ejemplo
        '1',              // Ambiente pruebas
        '001001',         // Serie
        '000000001',      // Secuencial
        '12345678',       // Código numérico
        '1'               // Tipo emisión
    );
    
    if (strlen($claveAcceso) === 49) {
        $tests['clave_acceso'] = true;
        echo "<div class='alert alert-success'><i class='fas fa-check'></i> Clave de acceso generada correctamente</div>";
        echo "<div class='ms-4'><code style='font-size: 0.9rem;'>$claveAcceso</code></div>";
        echo "<div class='ms-4 text-muted'>Longitud: " . strlen($claveAcceso) . " dígitos ✓</div>";
    } else {
        $tests['clave_acceso'] = false;
        $allPassed = false;
        echo "<div class='alert alert-danger'><i class='fas fa-times'></i> Error en generación de clave de acceso</div>";
    }
} catch (Exception $e) {
    $tests['clave_acceso'] = false;
    $allPassed = false;
    echo "<div class='alert alert-danger'><i class='fas fa-times'></i> Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// ============================================
// RESUMEN FINAL
// ============================================
echo "</div></div>";

$passed = count(array_filter($tests));
$total = count($tests);
$percentage = round(($passed / $total) * 100);

echo "<div class='card mt-4'>
    <div class='card-header " . ($allPassed ? 'bg-success' : 'bg-warning') . " text-white'>
        <h5 class='mb-0'><i class='fas fa-chart-pie'></i> Resumen de Pruebas</h5>
    </div>
    <div class='card-body'>
        <div class='row'>
            <div class='col-md-4 text-center'>
                <h2 class='display-4'>$passed/$total</h2>
                <p class='text-muted'>Tests pasados</p>
            </div>
            <div class='col-md-4 text-center'>
                <h2 class='display-4'>$percentage%</h2>
                <p class='text-muted'>Completado</p>
            </div>
            <div class='col-md-4 text-center'>
                <h2 class='display-4'>" . ($allPassed ? '<i class=\"fas fa-check-circle text-success\"></i>' : '<i class=\"fas fa-exclamation-triangle text-warning\"></i>') . "</h2>
                <p class='text-muted'>" . ($allPassed ? 'Todo OK' : 'Requiere atención') . "</p>
            </div>
        </div>
    </div>
</div>";

// Links de navegación
echo "<div class='card mt-4'>
    <div class='card-header bg-primary text-white'>
        <h5 class='mb-0'><i class='fas fa-link'></i> Enlaces Rápidos</h5>
    </div>
    <div class='card-body'>
        <div class='row'>
            <div class='col-md-6'>
                <a href='" . BASE_URL . "?module=facturacion&controller=facturaelectronica&action=index' class='btn btn-outline-primary btn-lg w-100 mb-2'>
                    <i class='fas fa-file-invoice-dollar'></i> Ir a Facturación Electrónica
                </a>
            </div>
            <div class='col-md-6'>
                <a href='" . BASE_URL . "' class='btn btn-outline-secondary btn-lg w-100 mb-2'>
                    <i class='fas fa-home'></i> Ir al Dashboard
                </a>
            </div>
        </div>
    </div>
</div>";

echo "</div>
</body>
</html>";
