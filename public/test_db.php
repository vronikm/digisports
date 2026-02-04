<?php
// test_db.php: Prueba directa de conexión y consulta a tenants desde PHP
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo '<h2>Base de datos actual:</h2>';
    $stmt = $db->query('SELECT DATABASE()');
    echo '<b>' . htmlspecialchars($stmt->fetchColumn()) . '</b>';
    echo '<hr>'; // Separador visual
    $sql = "SELECT * FROM tenants LIMIT 10";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo '<h2>Resultado de SELECT * FROM tenants:</h2>';
    if ($rows) {
        echo '<pre>' . htmlspecialchars(print_r($rows, true)) . '</pre>';
    } else {
        echo '<b>No se encontraron registros.</b>';
    }
} catch (Exception $e) {
    echo '<b>Error de conexión o consulta:</b> ' . htmlspecialchars($e->getMessage());
}
?>