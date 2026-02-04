<?php
/**
 * DigiSports Basket - Controlador Dashboard
 * 
 * @package DigiSports\Controllers\Basket
 */

namespace App\Controllers\Basket;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DashboardController extends \App\Controllers\ModuleController {
    
    protected $moduloCodigo = 'BASKET';
    
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'BASKET';
    }
    
    public function index() {
        $this->setupModule();
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $this->viewData['title'] = $this->moduloNombre;
        $this->viewData['kpis'] = [
            ['label' => 'Partidos Hoy', 'value' => 4, 'icon' => 'fas fa-basketball-ball', 'color' => $this->moduloColor, 'trend' => '+10%', 'trend_type' => 'up'],
            ['label' => 'Canchas', 'value' => 3, 'icon' => 'fas fa-map-marker-alt', 'color' => '#3B82F6', 'trend' => null, 'trend_type' => null],
            ['label' => 'Equipos Activos', 'value' => 12, 'icon' => 'fas fa-users', 'color' => '#8B5CF6', 'trend' => '+2', 'trend_type' => 'up'],
            ['label' => 'Ingresos Mes', 'value' => '$2,450', 'icon' => 'fas fa-dollar-sign', 'color' => '#22C55E', 'trend' => '+15%', 'trend_type' => 'up'],
            ['label' => 'Torneos', 'value' => 2, 'icon' => 'fas fa-trophy', 'color' => '#EAB308', 'trend' => null, 'trend_type' => null],
            ['label' => 'Escuelas', 'value' => 1, 'icon' => 'fas fa-graduation-cap', 'color' => '#EC4899', 'trend' => null, 'trend_type' => null],
        ];
        $this->renderModule('basket/dashboard/index');
    }
    
    protected function getMenuItems() {
        return [
            ['header' => 'Principal'],
            ['items' => [
                ['label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => url('basket', 'dashboard', 'index'), 'active' => true],
                ['label' => 'Calendario', 'icon' => 'fas fa-calendar-alt', 'url' => url('basket', 'calendario', 'index')]
            ]],
            ['header' => 'Gestión'],
            ['items' => [
                ['label' => 'Canchas', 'icon' => 'fas fa-basketball-ball', 'url' => '#', 'submenu' => [
                    ['label' => 'Listado', 'url' => url('basket', 'cancha', 'index')],
                    ['label' => 'Tarifas', 'url' => url('basket', 'tarifa', 'index')]
                ]],
                ['label' => 'Reservas', 'icon' => 'fas fa-calendar-check', 'url' => url('basket', 'reserva', 'index')],
                ['label' => 'Equipos', 'icon' => 'fas fa-users', 'url' => url('basket', 'equipo', 'index')]
            ]],
            ['header' => 'Competencias'],
            ['items' => [
                ['label' => 'Torneos', 'icon' => 'fas fa-trophy', 'url' => url('basket', 'torneo', 'index')],
                ['label' => 'Ligas', 'icon' => 'fas fa-list-ol', 'url' => url('basket', 'liga', 'index')],
                ['label' => 'Estadísticas', 'icon' => 'fas fa-chart-bar', 'url' => url('basket', 'estadistica', 'index')]
            ]],
            ['header' => 'Academia'],
            ['items' => [
                ['label' => 'Escuelas', 'icon' => 'fas fa-graduation-cap', 'url' => url('basket', 'escuela', 'index')],
                ['label' => 'Alumnos', 'icon' => 'fas fa-user-graduate', 'url' => url('basket', 'alumno', 'index')]
            ]]
        ];
    }
}
