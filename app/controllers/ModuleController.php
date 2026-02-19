<?php
/**
 * DigiSports - Controlador Base para Módulos Deportivos
 * Proporciona funcionalidad común para todos los módulos deportivos
 * 
 * @package DigiSports\Controllers
 */

namespace App\Controllers;

require_once BASE_PATH . '/app/controllers/BaseController.php';

abstract class ModuleController extends \BaseController {
    
    protected $moduloCodigo = '';
    protected $moduloNombre = '';
    protected $moduloIcono = 'fas fa-cube';
    protected $moduloColor = '#3B82F6';

    /**
     * Leer color e icono definidos en la base de datos para el módulo
     */
    protected function loadModuleBranding() {
        if (!empty($this->moduloCodigo) && isset($this->db)) {
            $stmt = $this->db->prepare("SELECT mod_nombre, mod_color_fondo, mod_icono FROM seguridad_modulos WHERE mod_codigo = ? AND mod_activo = 1 LIMIT 1");
            $stmt->execute([$this->moduloCodigo]);
            $branding = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($branding) {
                if (!empty($branding['mod_nombre'])) $this->moduloNombre = $branding['mod_nombre'];
                if (!empty($branding['mod_color_fondo'])) $this->moduloColor = $branding['mod_color_fondo'];
                if (!empty($branding['mod_icono'])) $this->moduloIcono = $branding['mod_icono'];
            }
        }
    }
    protected $menuItems = [];
    
    /**
     * Configurar datos comunes del módulo
     */
    protected function setupModule() {
        $this->loadModuleBranding();
        $this->viewData['modulo_actual'] = [
            'codigo' => $this->moduloCodigo,
            'nombre' => $this->moduloNombre,
            'icono' => $this->moduloIcono,
            'color' => $this->moduloColor
        ];
        $this->viewData['menu_items'] = $this->getMenuItems();
        $this->viewData['usuario'] = $_SESSION['user_name'] ?? 'Usuario';
        $this->viewData['tenant_nombre'] = $_SESSION['tenant_name'] ?? 'DigiSports';
    }
    
    /**
     * Obtener items del menú — carga dinámica desde seguridad_menu
     * Los controladores hijos pueden sobreescribir si necesitan menú estático
     */
    protected function getMenuItems() {
        return $this->loadDynamicMenu();
    }

    /**
     * Cargar menú dinámico desde la tabla seguridad_menu
     * Filtra por módulo y por permisos del rol del usuario actual
     *
     * @return array Menú en formato compatible con el layout module.php
     */
    protected function loadDynamicMenu() {
        try {
            if (empty($this->moduloCodigo) || !isset($this->db)) {
                return [];
            }

            $rolId = $_SESSION['rol_id'] ?? $_SESSION['usu_rol_id'] ?? null;

            // Obtener mod_id del módulo actual
            $stmt = $this->db->prepare("SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = ? AND mod_activo = 1 LIMIT 1");
            $stmt->execute([$this->moduloCodigo]);
            $modId = $stmt->fetchColumn();

            if (!$modId) return [];

            // Consultar menús activos de este módulo
            // Si hay rol, filtrar por seguridad_rol_menu (LEFT JOIN: si no hay registro de permiso, no se muestra)
            if ($rolId) {
                $sql = "SELECT m.men_id, m.men_padre_id, m.men_tipo, m.men_label, m.men_icono,
                               m.men_ruta_modulo, m.men_ruta_controller, m.men_ruta_action,
                               m.men_url_custom, m.men_badge, m.men_badge_tipo, m.men_orden
                        FROM seguridad_menu m
                        LEFT JOIN seguridad_rol_menu srm ON m.men_id = srm.rme_menu_id AND srm.rme_rol_id = ?
                        WHERE m.men_modulo_id = ? AND m.men_activo = 1
                          AND (m.men_tipo = 'HEADER' OR (srm.rme_puede_ver = 1))
                        ORDER BY m.men_orden, m.men_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$rolId, $modId]);
            } else {
                $sql = "SELECT men_id, men_padre_id, men_tipo, men_label, men_icono,
                               men_ruta_modulo, men_ruta_controller, men_ruta_action,
                               men_url_custom, men_badge, men_badge_tipo, men_orden
                        FROM seguridad_menu
                        WHERE men_modulo_id = ? AND men_activo = 1
                        ORDER BY men_orden, men_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$modId]);
            }

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($rows)) return [];

            // Detectar controlador y acción actual para marcar 'active'
            list($currentController, $currentAction) = $this->detectCurrentRoute();

            // Transformar filas planas al formato esperado por el layout
            return $this->buildMenuArray($rows, $currentController, $currentAction);

        } catch (\Exception $e) {
            error_log("ModuleController::loadDynamicMenu error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Detectar controlador y acción actual desde la URL
     * Soporta URLs encriptadas y estándar
     *
     * @return array [controller, action]
     */
    protected function detectCurrentRoute() {
        $controller = '';
        $action = '';

        if (isset($_GET['r'])) {
            $data = null;
            if (class_exists('Security') && method_exists('Security', 'decodeSecureUrl')) {
                $data = \Security::decodeSecureUrl($_GET['r']);
            } elseif (function_exists('decodeSecureUrl')) {
                $data = decodeSecureUrl($_GET['r']);
            }
            if (is_array($data)) {
                $controller = strtolower($data['c'] ?? '');
                $action     = strtolower($data['a'] ?? '');
            }
        } else {
            $controller = strtolower($_GET['controller'] ?? $_GET['c'] ?? '');
            $action     = strtolower($_GET['action'] ?? $_GET['a'] ?? '');
        }

        return [$controller, $action];
    }

    /**
     * Construir array de menú en el formato esperado por module.php layout
     * Formato: [ {header: 'X'}, {items: [{label, icon, url, active, submenu?, badge?}]}, ... ]
     *
     * @param array  $rows              Filas de BD ordenadas
     * @param string $currentController Controlador actual para active
     * @param string $currentAction     Acción actual para active
     * @return array
     */
    protected function buildMenuArray($rows, $currentController, $currentAction) {
        // Indexar por ID para acceso rápido
        $byId = [];
        foreach ($rows as $row) {
            $byId[(int)$row['men_id']] = $row;
        }

        // Separar HEADERs (raíz)
        $headers = array_filter($rows, fn($r) => $r['men_tipo'] === 'HEADER');
        $items   = array_filter($rows, fn($r) => $r['men_tipo'] === 'ITEM');
        $subs    = array_filter($rows, fn($r) => $r['men_tipo'] === 'SUBMENU');

        // Agrupar items por padre (header)
        $itemsByParent = [];
        foreach ($items as $item) {
            $pid = (int)($item['men_padre_id'] ?? 0);
            $itemsByParent[$pid][] = $item;
        }

        // Agrupar submenús por padre (item)
        $subsByParent = [];
        foreach ($subs as $sub) {
            $pid = (int)($sub['men_padre_id'] ?? 0);
            $subsByParent[$pid][] = $sub;
        }

        $menu = [];

        foreach ($headers as $header) {
            $hid = (int)$header['men_id'];

            // Agregar header
            $menu[] = ['header' => $header['men_label']];

            // Obtener items de este header
            $headerItems = $itemsByParent[$hid] ?? [];
            if (empty($headerItems)) continue;

            $menuItems = [];
            foreach ($headerItems as $item) {
                $iid = (int)$item['men_id'];
                $itemSubs = $subsByParent[$iid] ?? [];

                // Determinar si este item tiene submenús
                $hasSubmenu = !empty($itemSubs);

                // Construir URL
                $itemUrl = '#';
                if (!$hasSubmenu) {
                    $itemUrl = $this->buildMenuUrl($item);
                }

                // Determinar estado activo
                $isActive = $this->isMenuItemActive($item, $currentController, $currentAction);

                $menuItem = [
                    'label' => $item['men_label'],
                    'icon'  => $item['men_icono'] ?? 'fas fa-circle',
                    'url'   => $itemUrl,
                    'active' => $isActive
                ];

                // Badge
                if (!empty($item['men_badge'])) {
                    $menuItem['badge'] = $item['men_badge'];
                    $menuItem['badge_type'] = $item['men_badge_tipo'] ?? 'info';
                }

                // Submenús
                if ($hasSubmenu) {
                    $submenuArr = [];
                    foreach ($itemSubs as $sub) {
                        $subActive = $this->isMenuItemActive($sub, $currentController, $currentAction);
                        $submenuArr[] = [
                            'label'  => $sub['men_label'],
                            'url'    => $this->buildMenuUrl($sub),
                            'active' => $subActive
                        ];
                        if ($subActive) $isActive = true;
                    }
                    $menuItem['submenu'] = $submenuArr;
                    $menuItem['active'] = $isActive;
                }

                $menuItems[] = $menuItem;
            }

            if (!empty($menuItems)) {
                $menu[] = ['items' => $menuItems];
            }
        }

        return $menu;
    }

    /**
     * Construir URL encriptada para un item de menú
     */
    protected function buildMenuUrl($row) {
        if (!empty($row['men_url_custom'])) {
            return $row['men_url_custom'];
        }
        if (!empty($row['men_ruta_modulo']) && !empty($row['men_ruta_controller'])) {
            return url($row['men_ruta_modulo'], $row['men_ruta_controller'], $row['men_ruta_action'] ?? 'index');
        }
        return '#';
    }

    /**
     * Determinar si un item de menú está activo según la ruta actual
     */
    protected function isMenuItemActive($row, $currentController, $currentAction) {
        if (empty($currentController)) return false;
        $rowCtrl   = strtolower($row['men_ruta_controller'] ?? '');
        $rowAction = strtolower($row['men_ruta_action'] ?? '');

        if ($rowCtrl && $rowCtrl === $currentController) {
            // Si tiene acción definida, comparar también
            if ($rowAction && $rowAction !== $currentAction) return false;
            return true;
        }
        return false;
    }
    
    /**
     * Renderizar vista con layout de módulo
     */
    protected function renderModule($view, $data = []) {
        $this->setupModule();
        // Forzar paso de variables clave al layout
        $data = array_merge($this->viewData, $data);
        // Alias directos para máxima compatibilidad con el layout (mayúsculas y minúsculas)
        $data['modulo_actual'] = $this->viewData['modulo_actual'];
        $data['menu_items'] = $this->viewData['menu_items'];
        $data['moduloNombre'] = $this->moduloNombre;
        $data['modulonombre'] = $this->moduloNombre;
        $data['moduloIcono'] = $this->moduloIcono;
        $data['moduloicono'] = $this->moduloIcono;
        $data['moduloColor'] = $this->moduloColor;
        $data['modulocolor'] = $this->moduloColor;

        // Forzar prefijo de módulo para todas las vistas del módulo Seguridad
        // Si el controlador es de seguridad y la vista no empieza con 'seguridad/', anteponer 'seguridad/'
        if (strtoupper($this->moduloCodigo) === 'SEGURIDAD') {
            if (strpos($view, 'seguridad/') !== 0) {
                // Si la vista ya tiene un subdirectorio (ej: modulo/iconos), anteponer 'seguridad/'
                $view = 'seguridad/' . ltrim($view, '/');
            }
        }

        // Capturar contenido de la vista
        ob_start();
        extract($data);
        include BASE_PATH . "/app/views/{$view}.php";
        $content = ob_get_clean();

        // Pasar contenido al layout
        $data['content'] = $content;

        extract($data);
        include BASE_PATH . '/app/views/layouts/module.php';
    }
    
    /**
     * Obtener estadísticas base para KPIs
     */
    protected function getBaseStats($tabla, $campoFecha = 'created_at') {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $hoy = date('Y-m-d');
        $inicioMes = date('Y-m-01');
        $inicioSemana = date('Y-m-d', strtotime('monday this week'));
        
        try {
            // Total registros
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$tabla} WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);
            $total = $stmt->fetchColumn();
            
            // Este mes
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$tabla} WHERE tenant_id = ? AND DATE({$campoFecha}) >= ?");
            $stmt->execute([$tenantId, $inicioMes]);
            $esteMes = $stmt->fetchColumn();
            
            // Esta semana
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$tabla} WHERE tenant_id = ? AND DATE({$campoFecha}) >= ?");
            $stmt->execute([$tenantId, $inicioSemana]);
            $estaSemana = $stmt->fetchColumn();
            
            // Hoy
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$tabla} WHERE tenant_id = ? AND DATE({$campoFecha}) = ?");
            $stmt->execute([$tenantId, $hoy]);
            $hoyCount = $stmt->fetchColumn();
            
            return [
                'total' => $total,
                'este_mes' => $esteMes,
                'esta_semana' => $estaSemana,
                'hoy' => $hoyCount
            ];
        } catch (\Exception $e) {
            return ['total' => 0, 'este_mes' => 0, 'esta_semana' => 0, 'hoy' => 0];
        }
    }
    
    /**
     * Obtener datos para gráfico de líneas (últimos N días)
     */
    protected function getChartData($tabla, $dias = 7, $campoFecha = 'created_at') {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $labels = [];
        $valores = [];
        
        for ($i = $dias - 1; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('d/m', strtotime($fecha));
            
            try {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$tabla} WHERE tenant_id = ? AND DATE({$campoFecha}) = ?");
                $stmt->execute([$tenantId, $fecha]);
                $valores[] = (int)$stmt->fetchColumn();
            } catch (\Exception $e) {
                $valores[] = 0;
            }
        }
        
        return ['labels' => $labels, 'data' => $valores];
    }
}
