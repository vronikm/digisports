<?php
// Endpoint para limpiar OPcache
header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'OPcache no disponible'
];

if (function_exists('opcache_reset')) {
    try {
        opcache_reset();
        $response['success'] = true;
        $response['message'] = 'OPcache limpiado exitosamente';
    } catch (Exception $e) {
        $response['message'] = 'Error al limpiar OPcache: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'OPcache_reset() no disponible. OPcache puede estar deshabilitado.';
    $response['success'] = true; // No es error crítico
}

echo json_encode($response);
?>
