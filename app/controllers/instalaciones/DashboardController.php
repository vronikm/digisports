<?php
/**
 * DigiSports - Dashboard de Instalaciones
 * Controlador para vista principal de instalaciones (branding dinámico)
 * @package DigiSports\Controllers\Instalaciones
 * @version 1.0.0
 */

namespace App\Controllers\Instalaciones;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DashboardController extends \App\Controllers\ModuleController {
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'INSTALACIONES'; // Debe coincidir con el campo 'codigo' en modulos_sistema
    }

    protected function getMenuItems() {
        // Menú lateral dinámico: todos los módulos activos
        $stmt = $this->db->query("SELECT nombre, icono, color, url_base, codigo FROM modulos_sistema WHERE estado = 'A' ORDER BY orden_visualizacion ASC");
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $menu = [];
        foreach ($items as $item) {
            $menu[] = [
                'label' => $item['nombre'],
                'icon' => (strpos($item['icono'], 'fa-') === 0 ? 'fas ' : '') . $item['icono'],
                'color' => $item['color'],
                'url' => !empty($item['url_base']) ? $item['url_base'] : '#'
            ];
        }
        return $menu;
    }

    public function index() {
        // Puedes cargar KPIs o datos generales aquí si lo deseas
        $this->renderModule('instalaciones/dashboard/index');
    }
}
