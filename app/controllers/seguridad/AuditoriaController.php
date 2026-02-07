<?php
/**
 * DigiSports - Controlador Auditoría de Seguridad
 * Permite visualizar logs de accesos, cambios y alertas
 * @package DigiSports\Controllers\Seguridad
 */

namespace App\Controllers\Seguridad;

require_once BASE_PATH . '/app/controllers/ModuleController.php';
require_once BASE_PATH . '/app/controllers/seguridad/DashboardController.php';

class AuditoriaController extends \App\Controllers\ModuleController {
    // Métodos y propiedades válidos aquí
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'seguridad';
        $this->moduloNombre = 'Seguridad';
        $this->moduloIcono = 'fas fa-shield-alt';
        $this->moduloColor = '#F59E0B';
    }
    public function accesos() {
        $stmt = $this->db->query("SELECT * FROM seguridad_auditoria_acciones WHERE aud_accion = 'acceso' ORDER BY aud_fecha DESC LIMIT 100");
        $logs = $stmt->fetchAll();
        $this->renderModule('seguridad/auditoria/accesos', [
            'logs' => $logs,
            'pageTitle' => 'Logs de Acceso'
        ]);
    }

    public function cambios() {
        $stmt = $this->db->query("SELECT * FROM seguridad_auditoria_acciones WHERE aud_accion = 'cambio' ORDER BY aud_fecha DESC LIMIT 100");
        $logs = $stmt->fetchAll();
        $this->renderModule('seguridad/auditoria/cambios', [
            'logs' => $logs,
            'pageTitle' => 'Logs de Cambios'
        ]);
    }

    public function alertas() {
        $stmt = $this->db->query("SELECT * FROM seguridad_auditoria_acciones WHERE aud_accion = 'alerta' ORDER BY aud_fecha DESC LIMIT 100");
        $alertas = $stmt->fetchAll();
        $this->renderModule('seguridad/auditoria/alertas', [
            'alertas' => $alertas,
            'pageTitle' => 'Alertas de Seguridad'
        ]);
    }

    protected function getMenuItems() {
        return (new \App\Controllers\Seguridad\DashboardController())->getMenuItems();
    }
}
