<?php
/**
 * DigiSports Store - Controlador Dashboard
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DashboardController extends \App\Controllers\ModuleController {
    
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }
    
    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono = 'fas fa-store';
    
    public function index() {
        $this->setupModule();
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $this->viewData['title'] = $this->moduloNombre;
        $this->viewData['kpis'] = [
            ['label' => 'Ventas Hoy', 'value' => 23, 'icon' => 'fas fa-shopping-cart', 'color' => $this->moduloColor, 'trend' => '+18%', 'trend_type' => 'up'],
            ['label' => 'Productos', 'value' => 156, 'icon' => 'fas fa-box', 'color' => '#3B82F6', 'trend' => null, 'trend_type' => null],
            ['label' => 'Ingresos Hoy', 'value' => '$1,250', 'icon' => 'fas fa-dollar-sign', 'color' => '#22C55E', 'trend' => '+25%', 'trend_type' => 'up'],
            ['label' => 'Stock Bajo', 'value' => 8, 'icon' => 'fas fa-exclamation-triangle', 'color' => '#F59E0B', 'trend' => null, 'trend_type' => null],
            ['label' => 'Clientes Mes', 'value' => 89, 'icon' => 'fas fa-users', 'color' => '#8B5CF6', 'trend' => '+12', 'trend_type' => 'up'],
            ['label' => 'Pedidos Pend.', 'value' => 5, 'icon' => 'fas fa-clock', 'color' => '#0EA5E9', 'trend' => null, 'trend_type' => null],
        ];
        $this->viewData['categorias'] = [
            ['nombre' => 'Balones', 'icono' => 'fas fa-futbol', 'productos' => 24, 'ventas' => 156],
            ['nombre' => 'Ropa Deportiva', 'icono' => 'fas fa-tshirt', 'productos' => 48, 'ventas' => 234],
            ['nombre' => 'Calzado', 'icono' => 'fas fa-shoe-prints', 'productos' => 32, 'ventas' => 89],
            ['nombre' => 'Accesorios', 'icono' => 'fas fa-glasses', 'productos' => 52, 'ventas' => 178]
        ];
        $this->viewData['productos_top'] = [
            ['nombre' => 'Balón Nike Premier', 'ventas' => 45, 'stock' => 12],
            ['nombre' => 'Camiseta Dry-Fit', 'ventas' => 38, 'stock' => 25],
            ['nombre' => 'Zapatillas Running', 'ventas' => 29, 'stock' => 8],
            ['nombre' => 'Gorra Deportiva', 'ventas' => 24, 'stock' => 35],
            ['nombre' => 'Termo 1L', 'ventas' => 21, 'stock' => 42]
        ];
        $this->renderModule('store/dashboard/index');
    }
    
}
