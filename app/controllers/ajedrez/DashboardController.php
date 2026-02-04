<?php
/**
 * DigiSports Ajedrez - Controlador Dashboard
 * 
 * @package DigiSports\Controllers\Ajedrez
 */

namespace App\Controllers\Ajedrez;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DashboardController extends \App\Controllers\ModuleController {
    
    protected $moduloCodigo = 'AJEDREZ';
    
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'AJEDREZ';
    }
    
    public function index() {
        $this->setupModule();
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $this->viewData['title'] = $this->moduloNombre;
        $this->viewData['kpis'] = [
            ['label' => 'Partidas Hoy', 'value' => 12, 'icon' => 'fas fa-chess-board', 'color' => $this->moduloColor, 'trend' => '+15%', 'trend_type' => 'up'],
            ['label' => 'Miembros Club', 'value' => 67, 'icon' => 'fas fa-users', 'color' => '#3B82F6', 'trend' => '+5', 'trend_type' => 'up'],
            ['label' => 'Rating Promedio', 'value' => 1450, 'icon' => 'fas fa-chart-line', 'color' => '#8B5CF6', 'trend' => '+25', 'trend_type' => 'up'],
            ['label' => 'Torneos Activos', 'value' => 2, 'icon' => 'fas fa-trophy', 'color' => '#EAB308', 'trend' => null, 'trend_type' => null],
            ['label' => 'Clases Semana', 'value' => 8, 'icon' => 'fas fa-chalkboard', 'color' => '#22C55E', 'trend' => null, 'trend_type' => null],
            ['label' => 'Simultáneas', 'value' => 1, 'icon' => 'fas fa-chess-king', 'color' => '#DC2626', 'trend' => null, 'trend_type' => null],
        ];
        $this->renderModule('ajedrez/dashboard/index');
    }
    
    protected function getMenuItems() {
        return [
            ['header' => 'Principal'],
            ['items' => [
                ['label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => url('ajedrez', 'dashboard', 'index'), 'active' => true],
                ['label' => 'Calendario', 'icon' => 'fas fa-calendar-alt', 'url' => url('ajedrez', 'calendario', 'index')]
            ]],
            ['header' => 'Club'],
            ['items' => [
                ['label' => 'Miembros', 'icon' => 'fas fa-users', 'url' => '#', 'submenu' => [
                    ['label' => 'Listado', 'url' => url('ajedrez', 'miembro', 'index')],
                    ['label' => 'Rankings', 'url' => url('ajedrez', 'ranking', 'index')],
                    ['label' => 'Categorías', 'url' => url('ajedrez', 'categoria', 'index')]
                ]],
                ['label' => 'Partidas', 'icon' => 'fas fa-chess-board', 'url' => url('ajedrez', 'partida', 'index')]
            ]],
            ['header' => 'Competencias'],
            ['items' => [
                ['label' => 'Torneos', 'icon' => 'fas fa-trophy', 'url' => '#', 'submenu' => [
                    ['label' => 'Activos', 'url' => url('ajedrez', 'torneo', 'index')],
                    ['label' => 'Crear Torneo', 'url' => url('ajedrez', 'torneo', 'crear')],
                    ['label' => 'Sistemas de Juego', 'url' => url('ajedrez', 'sistema', 'index')]
                ]],
                ['label' => 'Simultáneas', 'icon' => 'fas fa-chess-king', 'url' => url('ajedrez', 'simultanea', 'index')]
            ]],
            ['header' => 'Formación'],
            ['items' => [
                ['label' => 'Clases', 'icon' => 'fas fa-chalkboard', 'url' => url('ajedrez', 'clase', 'index')],
                ['label' => 'Problemas', 'icon' => 'fas fa-puzzle-piece', 'url' => url('ajedrez', 'problema', 'index')],
                ['label' => 'Aperturas', 'icon' => 'fas fa-book', 'url' => url('ajedrez', 'apertura', 'index')]
            ]]
        ];
    }
}
