<?php
/**
 * DigiSports - Controlador de Bienvenida
 * Punto de entrada principal del sistema
 * 
 * @package DigiSports\Controllers\Core
 * @version 1.0.0
 */

namespace App\Controllers\Core;

require_once BASE_PATH . '/app/controllers/BaseController.php';

class WelcomeController extends \BaseController {
    
    /**
     * Página de bienvenida / entrada principal
     * Redirige al dashboard si está autenticado, o muestra login
     */
    public function index() {
        // Si el usuario está autenticado, ir al dashboard
        if (isAuthenticated()) {
            header('Location: ' . url('core', 'dashboard'));
            exit;
        }
        
        // Cargar módulos del sistema desde la BD
        $modulos = $this->getModulosSistema();
        
        // Si no está autenticado, mostrar página de bienvenida/login
        $this->viewData['csrf_token'] = \Security::generateCsrfToken();
        $this->viewData['layout'] = 'welcome';
        $this->viewData['title'] = 'Bienvenido';
        $this->viewData['modulos'] = $modulos;
        
        $this->render('welcome/index', $this->viewData);
    }
    
    /**
     * Obtener módulos del sistema activos desde la BD
     */
    private function getModulosSistema(): array {
        try {
            $stmt = $this->db->query("
                SELECT codigo, nombre, descripcion, icono, color, url_base, orden_visualizacion
                FROM modulos_sistema 
                WHERE estado = 'A' 
                ORDER BY orden_visualizacion ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * API de estado del sistema (para health checks)
     */
    public function status() {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => '¡DigiSports está operativo!',
            'status' => 'online',
            'version' => \Config::APP['version'] ?? '1.0.0',
            'environment' => \Config::isDebug() ? 'development' : 'production',
            'php_version' => phpversion(),
            'database' => $this->checkDatabaseConnection() ? 'connected' : 'disconnected',
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Verificar conexión a base de datos
     */
    private function checkDatabaseConnection(): bool {
        try {
            $this->db->query("SELECT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
