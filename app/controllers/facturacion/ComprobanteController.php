<?php
/**
 * DigiSports - Controlador de Comprobantes
 * Gestión de comprobantes electrónicos
 *
 * @package DigiSports\Controllers\Facturacion
 * @version 1.0.0
 */

namespace App\Controllers\Facturacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';
require_once BASE_PATH . '/app/models/FacturaElectronica.php';

class ComprobanteController extends \App\Controllers\ModuleController {

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'facturacion';
    }

    /**
     * Listar comprobantes electrónicos
     */
    public function index() {
        try {
            $estado = trim($this->post('estado') ?? $this->get('estado') ?? '');
            $tipo   = trim($this->post('tipo')   ?? $this->get('tipo')   ?? '');
            $busqueda = trim($this->post('busqueda') ?? $this->get('busqueda') ?? '');
            $pagina = max(1, (int)($this->post('pagina') ?? $this->get('pagina') ?? 1));
            $perPage = 20;
            $offset  = ($pagina - 1) * $perPage;

            $tenantId = $_SESSION['tenant_id'] ?? 0;

            $filtros = [];
            if ($estado !== '')   $filtros['estado_sri'] = $estado;
            if ($tipo   !== '')   $filtros['tipo_comprobante'] = $tipo;
            if ($busqueda !== '') $filtros['busqueda'] = $busqueda;

            $model = new \App\Models\FacturaElectronica();
            $comprobantes  = $model->listar($tenantId, $filtros, $perPage, $offset);
            $totalRegistros = $model->contar($tenantId, $filtros);
            $totalPaginas  = $totalRegistros > 0 ? ceil($totalRegistros / $perPage) : 1;

            // Resumen de estados para las tarjetas
            $resumen = $model->obtenerResumenEstados($tenantId);
            $stats = ['AUTORIZADO' => 0, 'PENDIENTE' => 0, 'RECHAZADO' => 0, 'ENVIADA' => 0];
            foreach ($resumen as $r) {
                $stats[$r['estado_sri']] = (int)$r['cantidad'];
            }
            // "Este mes"
            $mesInicio = date('Y-m-01');
            $mesFin    = date('Y-m-t');
            $resumenMes = $model->obtenerResumenEstados($tenantId, $mesInicio, $mesFin);
            $totalMes = 0;
            foreach ($resumenMes as $r) $totalMes += (int)$r['cantidad'];

            $this->viewData['comprobantes']   = $comprobantes;
            $this->viewData['totalRegistros'] = $totalRegistros;
            $this->viewData['pagina']         = $pagina;
            $this->viewData['totalPaginas']   = $totalPaginas;
            $this->viewData['estado']         = $estado;
            $this->viewData['tipo']           = $tipo;
            $this->viewData['busqueda']       = $busqueda;
            $this->viewData['stats']          = $stats;
            $this->viewData['totalMes']       = $totalMes;
            $this->viewData['csrf_token']     = \Security::generateCsrfToken();
            $this->viewData['title']          = 'Comprobantes Electrónicos';
            $this->renderModule('facturacion/comprobantes/index', $this->viewData);

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
        $this->renderModule('facturacion/comprobantes/ver', $this->viewData);
    }
    
    /**
     * Crear nuevo comprobante
     */
    public function crear() {
        $this->viewData['title'] = 'Nuevo Comprobante';
        $this->renderModule('facturacion/comprobantes/crear', $this->viewData);
    }
}