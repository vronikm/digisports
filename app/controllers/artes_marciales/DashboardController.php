<?php
/**
 * DigiSports Artes Marciales - Controlador Dashboard
 * 
 * @package DigiSports\Controllers\ArtesMarciales
 */

namespace App\Controllers\ArtesMarciales;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DashboardController extends \App\Controllers\ModuleController {
    
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'ARTES_MARCIALES';
    }
    
    protected $moduloNombre = 'DigiSports Artes Marciales';
    protected $moduloIcono = 'fas fa-hand-rock';
    protected $moduloColor = '#DC2626';
    
    public function index() {
        $this->setupModule();
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $this->viewData['title'] = $this->moduloNombre;
        $this->viewData['kpis'] = [
            ['label' => 'Clases Hoy', 'value' => 6, 'icon' => 'fas fa-hand-rock', 'color' => $this->moduloColor, 'trend' => '+8%', 'trend_type' => 'up'],
            ['label' => 'Disciplinas', 'value' => 4, 'icon' => 'fas fa-yin-yang', 'color' => '#1F2937', 'trend' => null, 'trend_type' => null],
            ['label' => 'Alumnos', 'value' => 89, 'icon' => 'fas fa-users', 'color' => '#8B5CF6', 'trend' => '+7', 'trend_type' => 'up'],
            ['label' => 'Ingresos Mes', 'value' => '$4,350', 'icon' => 'fas fa-dollar-sign', 'color' => '#22C55E', 'trend' => '+12%', 'trend_type' => 'up'],
            ['label' => 'Instructores', 'value' => 5, 'icon' => 'fas fa-user-ninja', 'color' => '#F97316', 'trend' => null, 'trend_type' => null],
            ['label' => 'Exámenes Grado', 'value' => 3, 'icon' => 'fas fa-award', 'color' => '#EAB308', 'trend' => null, 'trend_type' => null],
        ];
        
        $this->renderModule('artes_marciales/dashboard/index');
    }
    
}
