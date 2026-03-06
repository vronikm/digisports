<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/app.php';
require_once '../config/database.php';
require_once '../config/security.php';

// Permitir limpiar caché
$action = $_GET['action'] ?? '';

if ($action === 'clear-menu-cache') {
    $files_to_delete = [
        '../storage/cache/menu_cache.json',
        '../storage/cache/menu_urls.json',
        '../storage/cache/menu_*.json'
    ];
    
    $deleted = 0;
    foreach (glob('../storage/cache/menu_*.json') as $file) {
        if (unlink($file)) {
            $deleted++;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Caché limpiado',
        'files_deleted' => $deleted
    ]);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
?>
