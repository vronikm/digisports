<?php
/**
 * Debug del CanchaController
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

echo "<h2>Debug CanchaController</h2>";

$tenantId = $_SESSION['tenant_id'] ?? 1;
echo "<p>Tenant ID: <strong>{$tenantId}</strong></p>";

// Query de conteo (la que funciona)
echo "<h3>1. Query de Conteo</h3>";
$countQuery = "
    SELECT COUNT(DISTINCT c.cancha_id) as total
    FROM canchas c
    INNER JOIN instalaciones i ON c.instalacion_id = i.instalacion_id
    WHERE c.tenant_id = ?
";
$stmt = $db->prepare($countQuery);
$stmt->execute([$tenantId]);
$total = $stmt->fetch()['total'];
echo "<p>Total: <strong>{$total}</strong></p>";

// Query principal (la que no funciona)
echo "<h3>2. Query Principal</h3>";
$query = "
    SELECT 
        c.*,
        i.nombre as instalacion_nombre,
        0 as total_reservas_hoy
    FROM canchas c
    INNER JOIN instalaciones i ON c.instalacion_id = i.instalacion_id
    WHERE c.tenant_id = ?
    ORDER BY i.nombre, c.nombre
    LIMIT 15 OFFSET 0
";

echo "<pre>Query: " . htmlspecialchars($query) . "</pre>";
echo "<p>Params: [{$tenantId}, 15, 0]</p>";

$stmt = $db->prepare($query);
$stmt->execute([$tenantId]);
$canchas = $stmt->fetchAll();

echo "<p>Resultados: <strong>" . count($canchas) . "</strong></p>";

if (count($canchas) > 0) {
    echo "<h3>3. Datos</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Estado</th><th>Instalación</th></tr>";
    foreach ($canchas as $c) {
        echo "<tr>";
        echo "<td>{$c['cancha_id']}</td>";
        echo "<td>{$c['nombre']}</td>";
        echo "<td>{$c['tipo']}</td>";
        echo "<td>{$c['estado']}</td>";
        echo "<td>{$c['instalacion_nombre']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Verificar datos directamente
echo "<h3>4. Datos directos de canchas</h3>";
$stmt = $db->query("SELECT * FROM canchas LIMIT 5");
$directos = $stmt->fetchAll();
echo "<p>Canchas en BD: <strong>" . count($directos) . "</strong></p>";
if (count($directos) > 0) {
    echo "<pre>" . print_r($directos[0], true) . "</pre>";
}
