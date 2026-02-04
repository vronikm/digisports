<?php
/**
 * DigiSports Multideporte - Controlador Dashboard
 * 
 * @package DigiSports\Controllers\Multideporte
 */

namespace App\Controllers\Multideporte;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DashboardController extends \App\Controllers\ModuleController {
    
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'MULTIDEPORTE';
    }
    
    protected $moduloNombre = 'DigiSports Multideporte';
    protected $moduloIcono = 'fas fa-running';
    protected $moduloColor = '#7C3AED';
    
    public function index() {
        $this->setupModule();
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $this->viewData['title'] = $this->moduloNombre;
        $this->viewData['kpis'] = [
            ['label' => 'Actividades Hoy', 'value' => 15, 'icon' => 'fas fa-running', 'color' => $this->moduloColor, 'trend' => '+12%', 'trend_type' => 'up'],
            ['label' => 'Disciplinas', 'value' => 8, 'icon' => 'fas fa-layer-group', 'color' => '#3B82F6', 'trend' => null, 'trend_type' => null],
            ['label' => 'Alumnos Total', 'value' => 245, 'icon' => 'fas fa-users', 'color' => '#22C55E', 'trend' => '+18', 'trend_type' => 'up'],
            ['label' => 'Ingresos Mes', 'value' => '$12,850', 'icon' => 'fas fa-dollar-sign', 'color' => '#F59E0B', 'trend' => '+22%', 'trend_type' => 'up'],
            ['label' => 'Instructores', 'value' => 12, 'icon' => 'fas fa-chalkboard-teacher', 'color' => '#EC4899', 'trend' => null, 'trend_type' => null],
            ['label' => 'Instalaciones', 'value' => 6, 'icon' => 'fas fa-building', 'color' => '#0EA5E9', 'trend' => null, 'trend_type' => null],
        ];
        $this->renderModule('multideporte/dashboard/index');
    }
    
    protected function getMenuItems() {
        return [
            ['header' => 'Principal'],
            ['items' => [
                ['label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => url('multideporte', 'dashboard', 'index'), 'active' => true],
                ['label' => 'Calendario General', 'icon' => 'fas fa-calendar-alt', 'url' => url('multideporte', 'calendario', 'index')]
            ]],
            ['header' => 'Disciplinas'],
            ['items' => [
                ['label' => 'Todas las Disciplinas', 'icon' => 'fas fa-layer-group', 'url' => url('multideporte', 'disciplina', 'index')],
                ['label' => 'Fútbol', 'icon' => 'fas fa-futbol', 'url' => url('multideporte', 'disciplina', 'ver', ['tipo' => 'futbol'])],
                ['label' => 'Natación', 'icon' => 'fas fa-swimmer', 'url' => url('multideporte', 'disciplina', 'ver', ['tipo' => 'natacion'])],
                ['label' => 'Basketball', 'icon' => 'fas fa-basketball-ball', 'url' => url('multideporte', 'disciplina', 'ver', ['tipo' => 'basket'])],
                ['label' => 'Otras...', 'icon' => 'fas fa-ellipsis-h', 'url' => url('multideporte', 'disciplina', 'index')]
            ]],
            ['header' => 'Academia'],
            ['items' => [
                ['label' => 'Alumnos', 'icon' => 'fas fa-users', 'url' => '#', 'submenu' => [
                    ['label' => 'Todos', 'url' => url('multideporte', 'alumno', 'index')],
                    ['label' => 'Por Disciplina', 'url' => url('multideporte', 'alumno', 'porDisciplina')],
                    ['label' => 'Inscripciones', 'url' => url('multideporte', 'inscripcion', 'index')]
                ]],
                ['label' => 'Instructores', 'icon' => 'fas fa-chalkboard-teacher', 'url' => url('multideporte', 'instructor', 'index')],
                ['label' => 'Horarios', 'icon' => 'fas fa-clock', 'url' => url('multideporte', 'horario', 'index')]
            ]],
            ['header' => 'Instalaciones'],
            ['items' => [
                ['label' => 'Espacios', 'icon' => 'fas fa-building', 'url' => url('multideporte', 'espacio', 'index')],
                ['label' => 'Reservas', 'icon' => 'fas fa-calendar-check', 'url' => url('multideporte', 'reserva', 'index')]
            ]],
            ['header' => 'Reportes'],
            ['items' => [
                ['label' => 'Asistencia', 'icon' => 'fas fa-clipboard-list', 'url' => url('multideporte', 'reporte', 'asistencia')],
                ['label' => 'Ingresos', 'icon' => 'fas fa-chart-bar', 'url' => url('multideporte', 'reporte', 'ingresos')]
            ]]
        ];
    }
}
