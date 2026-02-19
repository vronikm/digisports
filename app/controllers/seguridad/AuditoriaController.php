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
        $this->moduloCodigo = 'SEGURIDAD';
        $this->moduloNombre = 'Seguridad';
        $this->moduloIcono = 'fas fa-shield-alt';
        $this->moduloColor = '#F59E0B';
    }
    public function accesos() {
        try {
            $stmt = $this->db->query("
                SELECT l.*, u.usu_nombres, u.usu_apellidos, u.usu_username
                FROM seguridad_log_accesos l
                LEFT JOIN seguridad_usuarios u ON l.acc_usuario_id = u.usu_usuario_id
                ORDER BY l.acc_fecha DESC LIMIT 100
            ");
            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $logs = [];
        }
        $this->renderModule('seguridad/auditoria/accesos', [
            'logs' => $logs,
            'pageTitle' => 'Logs de Acceso'
        ]);
    }

    public function cambios() {
        try {
            $stmt = $this->db->query("
                SELECT a.*, u.usu_nombres, u.usu_apellidos, u.usu_username
                FROM seguridad_auditoria a
                LEFT JOIN seguridad_usuarios u ON a.aud_usuario_id = u.usu_usuario_id
                ORDER BY a.aud_fecha_operacion DESC LIMIT 100
            ");
            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $logs = [];
        }
        $this->renderModule('seguridad/auditoria/cambios', [
            'logs' => $logs,
            'pageTitle' => 'Logs de Cambios'
        ]);
    }

    public function alertas() {
        try {
            $stmt = $this->db->query("
                SELECT l.*, u.usu_nombres, u.usu_apellidos, u.usu_username
                FROM seguridad_log_accesos l
                LEFT JOIN seguridad_usuarios u ON l.acc_usuario_id = u.usu_usuario_id
                WHERE l.acc_tipo = 'LOGIN_FAILED' OR l.acc_exito = 'N'
                ORDER BY l.acc_fecha DESC LIMIT 100
            ");
            $alertas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $alertas = [];
        }
        $this->renderModule('seguridad/auditoria/alertas', [
            'alertas' => $alertas,
            'pageTitle' => 'Alertas de Seguridad'
        ]);
    }

    /**
     * Vista de administración de IPs bloqueadas e intentos fallidos
     */
    public function ipsBloqueadas() {
        $blockedIPs = \Security::getBlockedIPs();
        $failedAttempts = \Security::getFailedAttempts();
        
        $config = [
            'max_login_attempts' => \Config::SECURITY['max_login_attempts'] ?? 5,
            'brute_force_window' => \Config::SECURITY['brute_force_window'] ?? 900,
            'ip_block_duration' => \Config::SECURITY['ip_block_duration'] ?? 3600,
        ];
        
        $this->renderModule('seguridad/auditoria/ips_bloqueadas', [
            'blockedIPs' => $blockedIPs,
            'failedAttempts' => $failedAttempts,
            'config' => $config,
            'pageTitle' => 'IPs Bloqueadas'
        ]);
    }
    
    /**
     * AJAX: Desbloquear una IP específica
     */
    public function desbloquearIp() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $ip = $_POST['ip'] ?? '';
        if (empty($ip)) {
            echo json_encode(['success' => false, 'message' => 'IP no proporcionada']);
            return;
        }
        
        $result = \Security::unblockIP($ip);
        
        if ($result) {
            // Registrar en auditoría
            $this->audit('seguridad', 'blocked_ips', 0, 'DESBLOQUEO_IP', ['ip' => $ip], null);
            echo json_encode(['success' => true, 'message' => "IP {$ip} desbloqueada exitosamente"]);
        } else {
            echo json_encode(['success' => false, 'message' => "La IP {$ip} no estaba bloqueada"]);
        }
    }
    
    /**
     * AJAX: Limpiar intentos fallidos de una IP
     */
    public function limpiarIntentos() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $ip = $_POST['ip'] ?? '';
        if (empty($ip)) {
            echo json_encode(['success' => false, 'message' => 'IP no proporcionada']);
            return;
        }
        
        $result = \Security::clearFailedAttempts($ip);
        
        if ($result) {
            $this->audit('seguridad', 'failed_attempts', 0, 'LIMPIEZA_INTENTOS', ['ip' => $ip], null);
            echo json_encode(['success' => true, 'message' => "Intentos de {$ip} limpiados"]);
        } else {
            echo json_encode(['success' => false, 'message' => "No había intentos para {$ip}"]);
        }
    }

}

