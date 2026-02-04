<?php
/**
 * Diagnóstico del controlador de Instalaciones
 */
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', __DIR__);
define('STORAGE_PATH', BASE_PATH . '/storage');

require_once CONFIG_PATH . '/app.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/security.php';
require_once APP_PATH . '/helpers/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(Config::SESSION['name']);
    session_start();
}

$db = Database::getInstance()->getConnection();

echo "<h2>Diagnóstico de Instalaciones</h2>";

// Verificar sesión y tenant
$tenantId = $_SESSION['tenant_id'] ?? 'NO DEFINIDO';
$userId = $_SESSION['usuario_id'] ?? 'NO DEFINIDO';

echo "<h3>1. Sesión</h3>";
echo "<p>tenant_id en sesión: <strong>{$tenantId}</strong></p>";
echo "<p>usuario_id en sesión: <strong>{$userId}</strong></p>";

echo "<h3>2. Datos en tablas</h3>";

// Contar instalaciones
$stmt = $db->query("SELECT COUNT(*) as total FROM instalaciones");
$total = $stmt->fetch()['total'];
echo "<p>Total instalaciones (todas): <strong>{$total}</strong></p>";

if ($tenantId !== 'NO DEFINIDO') {
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM instalaciones WHERE tenant_id = ?");
    $stmt->execute([$tenantId]);
    $total = $stmt->fetch()['total'];
    echo "<p>Instalaciones del tenant {$tenantId}: <strong>{$total}</strong></p>";
}

// Contar canchas
$stmt = $db->query("SELECT COUNT(*) as total FROM canchas");
$total = $stmt->fetch()['total'];
echo "<p>Total canchas (todas): <strong>{$total}</strong></p>";

if ($tenantId !== 'NO DEFINIDO') {
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM canchas WHERE tenant_id = ?");
    $stmt->execute([$tenantId]);
    $total = $stmt->fetch()['total'];
    echo "<p>Canchas del tenant {$tenantId}: <strong>{$total}</strong></p>";
}

echo "<h3>3. Query del CanchaController</h3>";

try {
    $query = "
        SELECT 
            c.*,
            i.nombre as instalacion_nombre,
            COUNT(DISTINCT r.reserva_id) as total_reservas_hoy
        FROM canchas c
        INNER JOIN instalaciones i ON c.instalacion_id = i.instalacion_id
        LEFT JOIN reservas r ON c.cancha_id = r.cancha_id 
            AND DATE(r.fecha_reserva) = CURDATE()
            AND r.estado != 'CANCELADA'
        WHERE c.tenant_id = ?
        GROUP BY c.cancha_id 
        ORDER BY i.nombre, c.nombre
        LIMIT 15 OFFSET 0
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$tenantId !== 'NO DEFINIDO' ? $tenantId : 1]);
    $canchas = $stmt->fetchAll();
    
    echo "<p>✅ Query ejecutada correctamente</p>";
    echo "<p>Resultados: <strong>" . count($canchas) . "</strong> canchas</p>";
    
    if (count($canchas) > 0) {
        echo "<pre>";
        print_r($canchas[0]);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en query: " . $e->getMessage() . "</p>";
}

echo "<h3>4. Vista existe?</h3>";
$viewPath = APP_PATH . '/views/instalaciones/canchas/index.php';
echo "<p>Path: {$viewPath}</p>";
echo "<p>Existe: " . (file_exists($viewPath) ? "✅ SI" : "❌ NO") . "</p>";

// Listar contenido del directorio instalaciones
echo "<h3>5. Contenido de views/instalaciones/</h3>";
$dir = APP_PATH . '/views/instalaciones/';
if (is_dir($dir)) {
    echo "<pre>";
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            echo str_replace($dir, '', $file->getPathname()) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>❌ Directorio no existe</p>";
}
