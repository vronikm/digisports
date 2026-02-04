<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();
$activeDb = $db->query('SELECT DATABASE()')->fetchColumn();
echo "<h2>Base de datos activa: <span style='color: #2563eb;'>$activeDb</span></h2>";
