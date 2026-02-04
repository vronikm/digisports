<?php
/**
 * DigiSports - Helper de Menú Dinámico
 * Genera el menú lateral según permisos, módulos y configuración visual
 */

require_once BASE_PATH . '/app/helpers/functions.php';

function getDynamicMenu($db, $user) {
    $tenantId = $user['tenant_id'] ?? null;
    $role = $user['role'] ?? null;
    $modules = $_SESSION['modules'] ?? [];
    $permissions = $_SESSION['permissions'] ?? [];
    
    // Consultar configuración de menú
    $stmt = $db->prepare("SELECT * FROM menu_config ORDER BY orden ASC");
    $stmt->execute();
    $menuConfig = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $menu = [];
    foreach ($menuConfig as $item) {
        // Filtrar por módulos activos del usuario
        if (!in_array($item['modulo_codigo'], $modules)) continue;
        // Filtrar por permisos requeridos
        if ($item['permiso_requerido'] && !hasPermission($item['permiso_requerido'])) continue;
        $menu[] = [
            'opcion' => $item['opcion'],
            'icono' => $item['icono'],
            'color' => $item['color'],
            'modulo_codigo' => $item['modulo_codigo'],
            'orden' => $item['orden'],
        ];
    }
    return $menu;
}
