<?php
/**
 * DigiSports - Controlador de Comprobantes
 * Gestión de comprobantes electrónicos
 * 
 * @package DigiSports\Controllers\Facturacion
 * @version 1.0.0
 */

namespace App\Controllers\Facturacion;

require_once BASE_PATH . '/app/controllers/BaseController.php';

class ComprobanteController extends \BaseController {
    
    /**
     * Listar comprobantes (redirecciona a facturas por ahora)
     */
    public function index() {
        try {
            $estado = $this->post('estado') ?? $this->get('estado') ?? '';
            $tipo = $this->post('tipo') ?? $this->get('tipo') ?? '';
            $pagina = max(1, (int)($this->post('pagina') ?? $this->get('pagina') ?? 1));
            $perPage = 15;
            $offset = ($pagina - 1) * $perPage;
            
            // Por ahora mostrar vista de comprobantes en construcción
            // o redirigir a facturas
            
            $this->viewData['comprobantes'] = [];
            $this->viewData['totalRegistros'] = 0;
            $this->viewData['pagina'] = $pagina;
            $this->viewData['totalPaginas'] = 0;
            $this->viewData['estado'] = $estado;
            $this->viewData['tipo'] = $tipo;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Comprobantes Electrónicos';
            $this->viewData['layout'] = 'main';
            
            $this->render('facturacion/comprobantes/index', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al listar comprobantes: " . $e->getMessage());
            $this->error('Error al cargar los comprobantes');
        }
    }
    
    /**
     * Ver detalle de comprobante
     */
    public function ver() {
        $id = (int)$this->get('id');
        
        if ($id < 1) {
            $this->error('Comprobante no válido');
        }
        
        $this->viewData['title'] = 'Detalle de Comprobante';
        $this->viewData['layout'] = 'main';
        
        $this->render('facturacion/comprobantes/ver', $this->viewData);
    }
    
    /**
     * Crear nuevo comprobante
     */
    public function crear() {
        $this->viewData['title'] = 'Nuevo Comprobante';
        $this->viewData['layout'] = 'main';
        $this->viewData['csrf_token'] = \Security::generateCsrfToken();
        
        $this->render('facturacion/comprobantes/crear', $this->viewData);
    }
}