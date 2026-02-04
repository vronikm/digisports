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
            $stmt = $this->db->prepare("SELECT nombre, color, icono FROM modulos_sistema WHERE codigo = ? AND estado = 'A' LIMIT 1");
            $stmt->execute([$this->moduloCodigo]);
            $branding = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($branding) {
                if (!empty($branding['nombre'])) $this->moduloNombre = $branding['nombre'];
                if (!empty($branding['color'])) $this->moduloColor = $branding['color'];
                if (!empty($branding['icono'])) $this->moduloIcono = $branding['icono'];
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
     * Obtener items del menú - Debe ser implementado por cada módulo
     */
    abstract protected function getMenuItems();
    
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
        if ($this->moduloCodigo === 'seguridad') {
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

        // LOG antes de incluir el layout
        error_log('DEBUG: ModuleController - antes de include module.php, moduloCodigo=' . $this->moduloCodigo);
        extract($data);
        include BASE_PATH . '/app/views/layouts/module.php';
        // LOG después de incluir el layout
        error_log('DEBUG: ModuleController - después de include module.php, moduloCodigo=' . $this->moduloCodigo);
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
