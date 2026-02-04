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
    
    protected function getMenuItems() {
        return [
            ['header' => 'Principal'],
            ['items' => [
                ['label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => url('artes_marciales', 'dashboard', 'index'), 'active' => true],
                ['label' => 'Calendario', 'icon' => 'fas fa-calendar-alt', 'url' => url('artes_marciales', 'calendario', 'index')]
            ]],
            ['header' => 'Academia'],
            ['items' => [
                ['label' => 'Disciplinas', 'icon' => 'fas fa-yin-yang', 'url' => '#', 'submenu' => [
                    ['label' => 'Karate', 'url' => url('artes_marciales', 'disciplina', 'ver', ['tipo' => 'karate'])],
                    ['label' => 'Taekwondo', 'url' => url('artes_marciales', 'disciplina', 'ver', ['tipo' => 'taekwondo'])],
                    ['label' => 'Judo', 'url' => url('artes_marciales', 'disciplina', 'ver', ['tipo' => 'judo'])],
                    ['label' => 'Jiu-Jitsu', 'url' => url('artes_marciales', 'disciplina', 'ver', ['tipo' => 'jiujitsu'])]
                ]],
                ['label' => 'Alumnos', 'icon' => 'fas fa-users', 'url' => url('artes_marciales', 'alumno', 'index')],
                ['label' => 'Instructores', 'icon' => 'fas fa-user-ninja', 'url' => url('artes_marciales', 'instructor', 'index')]
            ]],
            ['header' => 'Grados y Exámenes'],
            ['items' => [
                ['label' => 'Cinturones/Grados', 'icon' => 'fas fa-award', 'url' => url('artes_marciales', 'grado', 'index')],
                ['label' => 'Exámenes', 'icon' => 'fas fa-clipboard-check', 'url' => url('artes_marciales', 'examen', 'index')],
                ['label' => 'Promociones', 'icon' => 'fas fa-level-up-alt', 'url' => url('artes_marciales', 'promocion', 'index')]
            ]],
            ['header' => 'Competencias'],
            ['items' => [
                ['label' => 'Torneos', 'icon' => 'fas fa-trophy', 'url' => url('artes_marciales', 'torneo', 'index')],
                ['label' => 'Kata/Formas', 'icon' => 'fas fa-fist-raised', 'url' => url('artes_marciales', 'kata', 'index')]
            ]]
        ];
    }
}
