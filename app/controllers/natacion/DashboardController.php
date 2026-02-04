<?php
/**
 * DigiSports Natación - Controlador Dashboard
 * 
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DashboardController extends \App\Controllers\ModuleController {
    
    protected $moduloCodigo = 'NATACION';
    
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'NATACION';
    }
    
    public function index() {
        $this->setupModule();
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $this->viewData['title'] = $this->moduloNombre;
        $this->viewData['kpis'] = [
            ['label' => 'Clases Hoy', 'value' => 8, 'icon' => 'fas fa-swimmer', 'color' => $this->moduloColor, 'trend' => '+5%', 'trend_type' => 'up'],
            ['label' => 'Piscinas', 'value' => 2, 'icon' => 'fas fa-water', 'color' => '#3B82F6', 'trend' => null, 'trend_type' => null],
            ['label' => 'Alumnos Activos', 'value' => 156, 'icon' => 'fas fa-users', 'color' => '#8B5CF6', 'trend' => '+12', 'trend_type' => 'up'],
            ['label' => 'Ingresos Mes', 'value' => '$8,920', 'icon' => 'fas fa-dollar-sign', 'color' => '#22C55E', 'trend' => '+18%', 'trend_type' => 'up'],
            ['label' => 'Instructores', 'value' => 6, 'icon' => 'fas fa-chalkboard-teacher', 'color' => '#F97316', 'trend' => null, 'trend_type' => null],
            ['label' => 'Competencias', 'value' => 1, 'icon' => 'fas fa-medal', 'color' => '#EAB308', 'trend' => null, 'trend_type' => null],
        ];
        $this->renderModule('natacion/dashboard/index');
    }
    
    protected function getMenuItems() {
        return [
            ['header' => 'Principal'],
            ['items' => [
                ['label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => url('natacion', 'dashboard', 'index'), 'active' => true],
                ['label' => 'Horarios', 'icon' => 'fas fa-clock', 'url' => url('natacion', 'horario', 'index')]
            ]],
            ['header' => 'Instalaciones'],
            ['items' => [
                ['label' => 'Piscinas', 'icon' => 'fas fa-water', 'url' => '#', 'submenu' => [
                    ['label' => 'Ver Piscinas', 'url' => url('natacion', 'piscina', 'index')],
                    ['label' => 'Carriles', 'url' => url('natacion', 'carril', 'index')],
                    ['label' => 'Mantenimiento', 'url' => url('natacion', 'mantenimiento', 'index')]
                ]],
                ['label' => 'Reservas Libre', 'icon' => 'fas fa-calendar-check', 'url' => url('natacion', 'reserva', 'index')]
            ]],
            ['header' => 'Academia'],
            ['items' => [
                ['label' => 'Escuelas', 'icon' => 'fas fa-graduation-cap', 'url' => '#', 'submenu' => [
                    ['label' => 'Mis Escuelas', 'url' => url('natacion', 'escuela', 'index')],
                    ['label' => 'Niveles', 'url' => url('natacion', 'nivel', 'index')],
                    ['label' => 'Grupos', 'url' => url('natacion', 'grupo', 'index')]
                ]],
                ['label' => 'Alumnos', 'icon' => 'fas fa-user-graduate', 'url' => url('natacion', 'alumno', 'index')],
                ['label' => 'Instructores', 'icon' => 'fas fa-chalkboard-teacher', 'url' => url('natacion', 'instructor', 'index')]
            ]],
            ['header' => 'Competencias'],
            ['items' => [
                ['label' => 'Eventos', 'icon' => 'fas fa-medal', 'url' => url('natacion', 'evento', 'index')],
                ['label' => 'Marcas/Tiempos', 'icon' => 'fas fa-stopwatch', 'url' => url('natacion', 'marca', 'index')]
            ]]
        ];
    }
}
