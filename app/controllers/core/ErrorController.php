<?php
/**
 * DigiSports - Controlador de Errores
 * Maneja la visualización de errores del sistema
 * 
 * @package DigiSports\Controllers\Core
 * @version 1.0.0
 */

namespace App\Controllers\Core;

require_once BASE_PATH . '/app/controllers/BaseController.php';

class ErrorController extends \BaseController {
    
    /**
     * Mostrar página de error
     */
    public function show() {
        $errorMessage = $_SESSION['error_message'] ?? 'Ha ocurrido un error inesperado.';
        unset($_SESSION['error_message']);
        
        $this->viewData['error_message'] = $errorMessage;
        $this->viewData['title'] = 'Error';
        $this->viewData['layout'] = 'error';
        
        $this->render('errors/show', $this->viewData);
    }
    
    /**
     * Página 404 - No encontrado
     */
    public function notFound() {
        http_response_code(404);
        
        $this->viewData['title'] = 'Página no encontrada';
        $this->viewData['layout'] = 'error';
        
        $this->render('errors/404', $this->viewData);
    }
    
    /**
     * Página 403 - Acceso denegado
     */
    public function forbidden() {
        http_response_code(403);
        
        $this->viewData['title'] = 'Acceso denegado';
        $this->viewData['layout'] = 'error';
        
        $this->render('errors/403', $this->viewData);
    }
    
    /**
     * Página 500 - Error del servidor
     */
    public function serverError() {
        http_response_code(500);
        
        $this->viewData['title'] = 'Error del servidor';
        $this->viewData['layout'] = 'error';
        
        $this->render('errors/500', $this->viewData);
    }
}
