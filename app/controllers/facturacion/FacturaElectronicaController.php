<?php
/**
 * DigiSports - Controlador de Facturación Electrónica SRI
 * Emisión de comprobantes electrónicos
 * 
 * @package DigiSports\Controllers\Facturacion
 * @version 1.0.0
 */

namespace App\Controllers\Facturacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';
require_once BASE_PATH . '/app/services/SRI/FacturaElectronicaService.php';
require_once BASE_PATH . '/app/services/SRI/FirmaElectronicaService.php';
require_once BASE_PATH . '/app/services/SRI/WebServiceSRIService.php';
require_once BASE_PATH . '/app/services/SRI/RIDEService.php';
require_once BASE_PATH . '/app/models/FacturaElectronica.php';

use App\Services\SRI\FacturaElectronicaService;
use App\Services\SRI\FirmaElectronicaService;
use App\Services\SRI\WebServiceSRIService;
use App\Services\SRI\RIDEService;
use App\Models\FacturaElectronica;

class FacturaElectronicaController extends \App\Controllers\ModuleController {
    protected $facturaService;
    protected $firmaService;
    protected $webServiceSRI;
    protected $rideService;
    protected $facturaModel;
    protected $configSRI;
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'facturacion';
        
        $this->facturaService = new FacturaElectronicaService();
        $this->firmaService = new FirmaElectronicaService();
        $this->webServiceSRI = new WebServiceSRIService();
        $this->rideService = new RIDEService();
        $this->facturaModel = new FacturaElectronica();
        $this->configSRI = require BASE_PATH . '/config/sri.php';
    }
    
    /**
     * Listado de facturas electrónicas
     */
    public function index() {
        $this->authorize('ver', 'facturacion');
        $tenantId = $this->tenantId;
        
        $filtros = [
            'estado_sri' => $this->get('estado'),
            'fecha_desde' => $this->get('fecha_desde'),
            'fecha_hasta' => $this->get('fecha_hasta'),
            'busqueda' => $this->get('q'),
        ];
        
        $pagina = (int) ($this->get('pagina') ?? 1);
        if ($pagina < 1) $pagina = 1;
        
        $limite = 20;
        $offset = ($pagina - 1) * $limite;
        
        $facturas = $this->facturaModel->listar($tenantId, $filtros, $limite, $offset);
        $total = $this->facturaModel->contar($tenantId, $filtros);
        $totalPaginas = ceil($total / $limite);
        
        // Obtener resumen de estados
        $resumenEstados = $this->facturaModel->obtenerResumenEstados($tenantId, $filtros['fecha_desde'], $filtros['fecha_hasta']);
        
        $this->viewData['facturas'] = $facturas;
        $this->viewData['filtros'] = $filtros;
        $this->viewData['paginacion'] = [
            'pagina' => $pagina,
            'total_paginas' => $totalPaginas,
            'total_registros' => $total,
        ];
        $this->viewData['resumen_estados'] = $resumenEstados;
        $this->viewData['title'] = 'Facturas Electrónicas';
        
        $this->renderModule('facturacion/facturas_electronicas/index', $this->viewData);
    }
    
    /**
     * Emitir nueva factura electrónica
     */
    public function emitir() {
        $this->authorize('crear', 'facturacion');
        
        if (!$this->isPost()) {
            return $this->error('Método no permitido', 405);
        }
        
        if (!$this->validateCsrf()) {
            return $this->error('Token de seguridad inválido', 403);
        }
        
        try {
            $tenantId = $this->tenantId;
            $facturaId = $this->post('factura_id');
            
            // Obtener datos de la factura desde la base de datos
            $datosFactura = $this->obtenerDatosFactura($facturaId);
            
            if (!$datosFactura) {
                return $this->error('Factura no encontrada', 404);
            }
            
            // 1. Generar secuencial y clave de acceso
            $establecimiento = $this->configSRI['emisor']['codigo_establecimiento'];
            $puntoEmision = $this->configSRI['emisor']['punto_emision'];
            $secuencial = $this->facturaService->obtenerSecuencial($tenantId, $establecimiento, $puntoEmision);
            $codigoNumerico = $this->facturaService->generarCodigoNumerico();
            
            $fechaEmision = date('dmY');
            $claveAcceso = $this->facturaService->generarClaveAcceso(
                $fechaEmision,
                '01', // Factura
                $this->configSRI['emisor']['ruc'],
                $this->configSRI['ambiente'],
                $establecimiento . $puntoEmision,
                $secuencial,
                $codigoNumerico,
                $this->configSRI['tipo_emision']
            );
            
            // Preparar datos completos
            $datosFactura['clave_acceso'] = $claveAcceso;
            $datosFactura['secuencial'] = $secuencial;
            $datosFactura['establecimiento'] = $establecimiento;
            $datosFactura['punto_emision'] = $puntoEmision;
            $datosFactura['fecha_emision'] = date('d/m/Y');
            $datosFactura['numero_completo'] = $establecimiento . '-' . $puntoEmision . '-' . $secuencial;
            
            // 2. Generar XML
            $xml = $this->facturaService->generarXMLFactura($datosFactura);
            $archivoXML = $this->facturaService->guardarXML($xml, $claveAcceso, 'generados');
            
            // 3. Firmar XML
            $this->firmaService->cargarCertificado();
            $xmlFirmado = $this->firmaService->firmarXML($xml);
            $archivoFirmado = $this->facturaService->guardarXML($xmlFirmado, $claveAcceso, 'firmados');
            
            // 4. Guardar en base de datos
            $idFacturaElectronica = $this->facturaModel->crear([
                'tenant_id' => $tenantId,
                'factura_id' => $facturaId,
                'clave_acceso' => $claveAcceso,
                'tipo_comprobante' => '01',
                'establecimiento' => $establecimiento,
                'punto_emision' => $puntoEmision,
                'secuencial' => $secuencial,
                'fecha_emision' => date('Y-m-d'),
                'cliente_id' => $datosFactura['cliente']['id'] ?? null,
                'cliente_identificacion' => $datosFactura['cliente']['identificacion'],
                'cliente_razon_social' => $datosFactura['cliente']['razon_social'],
                'subtotal' => $datosFactura['totales']['subtotal'],
                'iva' => $datosFactura['totales']['iva'],
                'total' => $datosFactura['totales']['total'],
                'estado_sri' => 'GENERADA',
                'xml_generado' => $archivoXML,
                'xml_firmado' => $archivoFirmado,
            ]);
            
            // 5. Enviar al SRI
            $resultado = $this->webServiceSRI->procesarComprobante($xmlFirmado, $claveAcceso);
            
            if ($resultado['exito']) {
                $autorizacion = $resultado['resultado']['autorizaciones'][0] ?? [];
                
                // Guardar XML autorizado
                if (!empty($autorizacion['comprobante'])) {
                    $archivoAutorizado = $this->facturaService->guardarXML(
                        $autorizacion['comprobante'],
                        $claveAcceso,
                        'autorizados'
                    );
                }
                
                // Actualizar estado
                $this->facturaModel->actualizarEstado($idFacturaElectronica, 'AUTORIZADO', [
                    'numero_autorizacion' => $autorizacion['numero_autorizacion'] ?? $claveAcceso,
                    'fecha_autorizacion' => $autorizacion['fecha_autorizacion'] ?? date('Y-m-d H:i:s'),
                    'xml_autorizado' => $archivoAutorizado ?? null,
                ]);
                
                // Generar RIDE
                $rideHtml = $this->rideService->generarRIDEHtml($datosFactura, $autorizacion);
                $this->rideService->guardarRIDE($rideHtml, $claveAcceso);
                
                return $this->success([
                    'mensaje' => 'Factura electrónica emitida y autorizada exitosamente',
                    'clave_acceso' => $claveAcceso,
                    'numero_autorizacion' => $autorizacion['numero_autorizacion'] ?? $claveAcceso,
                    'numero_factura' => $datosFactura['numero_completo'],
                ]);
                
            } else {
                // Actualizar con error
                $mensajeError = $this->extraerMensajeError($resultado);
                $this->facturaModel->actualizarEstado($idFacturaElectronica, 'ERROR', [
                    'mensaje_error' => $mensajeError,
                ]);
                
                return $this->error('Error al autorizar: ' . $mensajeError, 400);
            }
            
        } catch (\Exception $e) {
            return $this->error('Error al emitir factura: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Ver detalle de factura electrónica
     */
    public function ver() {
        $this->authorize('ver', 'facturacion');
        $id = $this->getParam('id');
        
        if (!$id) {
            return $this->error('ID no proporcionado', 400);
        }
        
        $factura = $this->facturaModel->obtenerPorId($id);
        
        if (!$factura) {
            return $this->error('Factura no encontrada', 404);
        }
        
        $this->viewData['factura'] = $factura;
        $this->viewData['title'] = 'Detalle Factura Electrónica';
        
        $this->renderModule('facturacion/facturas_electronicas/ver', $this->viewData);
    }
    
    /**
     * Descargar XML de factura
     */
    public function descargarXML() {
        $this->authorize('ver', 'facturacion');
        $id = $this->getParam('id');
        $tipo = $this->getParam('tipo') ?? 'autorizado';
        
        $factura = $this->facturaModel->obtenerPorId($id);
        
        if (!$factura) {
            return $this->error('Factura no encontrada', 404);
        }
        
        $archivo = null;
        switch ($tipo) {
            case 'generado':
                $archivo = $factura['xml_generado'];
                break;
            case 'firmado':
                $archivo = $factura['xml_firmado'];
                break;
            case 'autorizado':
            default:
                $archivo = $factura['xml_autorizado'] ?? $factura['xml_firmado'];
                break;
        }
        
        if (!$archivo || !file_exists($archivo)) {
            return $this->error('Archivo XML no encontrado', 404);
        }
        
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $factura['clave_acceso'] . '_' . $tipo . '.xml"');
        readfile($archivo);
        exit;
    }
    
    /**
     * Descargar RIDE (PDF/HTML)
     */
    public function descargarRIDE() {
        $this->authorize('ver', 'facturacion');
        $id = $this->getParam('id');
        
        $factura = $this->facturaModel->obtenerPorId($id);
        
        if (!$factura) {
            return $this->error('Factura no encontrada', 404);
        }
        
        $archivoHtml = $this->configSRI['storage']['ride'] . $factura['clave_acceso'] . '.html';
        $archivoPdf = $this->configSRI['storage']['ride'] . $factura['clave_acceso'] . '.pdf';
        
        // Intentar PDF primero
        if (file_exists($archivoPdf)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="RIDE_' . $factura['clave_acceso'] . '.pdf"');
            readfile($archivoPdf);
            exit;
        }
        
        // Si no hay PDF, mostrar HTML
        if (file_exists($archivoHtml)) {
            header('Content-Type: text/html; charset=UTF-8');
            readfile($archivoHtml);
            exit;
        }
        
        return $this->error('RIDE no encontrado', 404);
    }
    
    /**
     * Reenviar factura al SRI
     */
    public function reenviar() {
        $this->authorize('crear', 'facturacion');
        
        if (!$this->isPost()) {
            return $this->error('Método no permitido', 405);
        }
        
        $id = $this->getParam('id');
        $factura = $this->facturaModel->obtenerPorId($id);
        
        if (!$factura) {
            return $this->error('Factura no encontrada', 404);
        }
        
        if ($factura['estado_sri'] === 'AUTORIZADO') {
            return $this->error('La factura ya está autorizada', 400);
        }
        
        try {
            $xmlFirmado = file_get_contents($factura['xml_firmado']);
            $resultado = $this->webServiceSRI->procesarComprobante($xmlFirmado, $factura['clave_acceso']);
            
            if ($resultado['exito']) {
                $autorizacion = $resultado['resultado']['autorizaciones'][0] ?? [];
                
                $this->facturaModel->actualizarEstado($id, 'AUTORIZADO', [
                    'numero_autorizacion' => $autorizacion['numero_autorizacion'] ?? $factura['clave_acceso'],
                    'fecha_autorizacion' => $autorizacion['fecha_autorizacion'] ?? date('Y-m-d H:i:s'),
                ]);
                
                return $this->success(['mensaje' => 'Factura autorizada exitosamente']);
            } else {
                $mensajeError = $this->extraerMensajeError($resultado);
                return $this->error('Error: ' . $mensajeError, 400);
            }
            
        } catch (\Exception $e) {
            return $this->error('Error al reenviar: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Consultar estado en SRI
     */
    public function consultarEstado() {
        $this->authorize('ver', 'facturacion');
        $claveAcceso = $this->getParam('clave_acceso');
        
        if (!$claveAcceso) {
            return $this->error('Clave de acceso no proporcionada', 400);
        }
        
        try {
            $resultado = $this->webServiceSRI->consultarAutorizacion($claveAcceso);
            return $this->success($resultado);
        } catch (\Exception $e) {
            return $this->error('Error al consultar: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Verificar conectividad con SRI
     */
    public function verificarConexion() {
        $this->authorize('ver', 'facturacion');
        $resultado = $this->webServiceSRI->verificarConectividad();
        return $this->success([
            'conectividad' => $resultado,
            'ambiente' => $this->configSRI['ambiente'] == 1 ? 'PRUEBAS' : 'PRODUCCIÓN',
        ]);
    }
    
    /**
     * Obtener información del certificado
     */
    public function infoCertificado() {
        $this->authorize('ver', 'facturacion');
        try {
            $this->firmaService->cargarCertificado();
            $info = $this->firmaService->obtenerInfoCertificado();
            return $this->success($info);
        } catch (\Exception $e) {
            return $this->error('Error al leer certificado: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener datos de factura para emisión
     * 
     * @param int $facturaId ID de la factura
     * @return array|null
     */
    private function obtenerDatosFactura($facturaId) {
        try {
            $stmt = $this->db->prepare("
                SELECT f.*, c.cli_identificacion as identificacion, CONCAT(c.cli_nombres, ' ', c.cli_apellidos) as razon_social, 
                       c.cli_email as email, c.cli_direccion as direccion,
                       c.cli_tipo_identificacion as tipo_identificacion
                FROM facturacion_facturas f
                LEFT JOIN clientes c ON f.fac_cliente_id = c.cli_cliente_id
                WHERE f.fac_id = ?
            ");
            $stmt->execute([$facturaId]);
            $factura = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$factura) {
                return null;
            }
            
            // Obtener detalles
            $stmt = $this->db->prepare("
                SELECT * FROM facturacion_lineas WHERE lin_factura_id = ?
            ");
            $stmt->execute([$facturaId]);
            $detalles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Mapear tipo de identificación
            $tipoIdentificacion = match($factura['tipo_identificacion']) {
                'RUC' => '04',
                'CEDULA' => '05',
                'PASAPORTE' => '06',
                'CONSUMIDOR_FINAL' => '07',
                default => '05',
            };
            
            // Construir estructura de datos
            return [
                'cliente' => [
                    'id' => $factura['fac_cliente_id'],
                    'tipo_identificacion' => $tipoIdentificacion,
                    'identificacion' => $factura['identificacion'],
                    'razon_social' => trim($factura['razon_social'] ?? 'Consumidor Final'),
                    'direccion' => $factura['direccion'] ?? 'N/A',
                    'email' => $factura['email'] ?? '',
                ],
                'detalles' => array_map(function($d) {
                    $subtotal = $d['lin_cantidad'] * $d['lin_precio_unitario'] - ($d['lin_descuento'] ?? 0);
                    $ivaValor = $subtotal * ($d['lin_porcentaje_iva'] ?? 15) / 100;
                    
                    return [
                        'codigo' => $d['lin_codigo'] ?? 'SERV001',
                        'descripcion' => $d['lin_descripcion'],
                        'cantidad' => $d['lin_cantidad'],
                        'precio_unitario' => $d['lin_precio_unitario'],
                        'descuento' => $d['lin_descuento'] ?? 0,
                        'precio_total_sin_impuesto' => $subtotal,
                        'impuestos' => [
                            [
                                'codigo' => '2', // IVA
                                'codigo_porcentaje' => ($d['lin_porcentaje_iva'] == 0) ? '0' : '4', // 15%
                                'tarifa' => $d['lin_porcentaje_iva'] ?? 15,
                                'base_imponible' => $subtotal,
                                'valor' => $ivaValor,
                            ],
                        ],
                    ];
                }, $detalles),
                'totales' => [
                    'subtotal' => $factura['fac_subtotal'],
                    'subtotal_iva' => $factura['fac_subtotal'],
                    'subtotal_0' => 0,
                    'descuento' => $factura['fac_descuento'] ?? 0,
                    'iva' => $factura['fac_iva'],
                    'porcentaje_iva' => 15,
                    'total' => $factura['fac_total'],
                    'impuestos' => [
                        [
                            'codigo' => '2',
                            'codigo_porcentaje' => '4',
                            'base_imponible' => $factura['fac_subtotal'],
                            'valor' => $factura['fac_iva'],
                        ],
                    ],
                ],
                'pagos' => [
                    [
                        'forma_pago' => '01', // Sin utilización del sistema financiero por defecto
                        'total' => $factura['fac_total'],
                        'plazo' => null,
                        'unidad_tiempo' => 'dias',
                    ],
                ],
                'info_adicional' => [
                    'Email' => $factura['email'] ?? '',
                    'Teléfono' => '',
                ],
            ];
            
        } catch (\Exception $e) {
            error_log("Error al obtener datos de factura: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Extraer mensaje de error de respuesta del SRI
     */
    private function extraerMensajeError($resultado) {
        if (!empty($resultado['resultado']['autorizaciones'][0]['mensajes'])) {
            $mensajes = $resultado['resultado']['autorizaciones'][0]['mensajes'];
            return implode('. ', array_map(function($m) {
                return $m['mensaje'] . (isset($m['informacion_adicional']) ? ': ' . $m['informacion_adicional'] : '');
            }, $mensajes));
        }
        
        if (!empty($resultado['resultado']['comprobantes'][0]['mensajes'])) {
            $mensajes = $resultado['resultado']['comprobantes'][0]['mensajes'];
            return implode('. ', array_map(function($m) {
                return $m['mensaje'] . (isset($m['informacion_adicional']) ? ': ' . $m['informacion_adicional'] : '');
            }, $mensajes));
        }
        
        return $resultado['mensaje'] ?? 'Error desconocido';
    }
}
