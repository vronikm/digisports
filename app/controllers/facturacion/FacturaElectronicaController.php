<?php
/**
 * DigiSports - Controlador de Facturación Electrónica SRI
 * Emisión de comprobantes electrónicos
 *
 * @package DigiSports\Controllers\Facturacion
 * @version 1.1.0
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

    /** Ruta al .p12 del tenant (null = usar config/sri.php) */
    private $tenantCertPath  = null;
    /** Contraseña descifrada del .p12 del tenant (null = usar config/sri.php) */
    private $tenantCertClave = null;

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo    = 'facturacion';
        $this->facturaService  = new FacturaElectronicaService();
        $this->firmaService    = new FirmaElectronicaService();
        $this->webServiceSRI   = new WebServiceSRIService();
        $this->rideService     = new RIDEService();
        $this->facturaModel    = new FacturaElectronica();
        $this->configSRI       = require BASE_PATH . '/config/sri.php';

        // Superponer configuración del tenant (facturacion_configuracion)
        $this->aplicarConfigTenant();
        // Sincronizar la config del tenant con el servicio de generación XML
        $this->facturaService->setConfig($this->configSRI);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LISTADO
    // ─────────────────────────────────────────────────────────────────────────

    public function index() {
        $this->authorize('ver', 'facturacion');

        $filtros = [
            'estado_sri'  => $this->get('estado')      ?? '',
            'fecha_desde' => $this->get('fecha_desde') ?? '',
            'fecha_hasta' => $this->get('fecha_hasta') ?? '',
            'busqueda'    => $this->get('q')            ?? '',
        ];

        $pagina      = max(1, (int) ($this->get('pagina') ?? 1));
        $limite      = 20;
        $offset      = ($pagina - 1) * $limite;

        $facturas       = $this->facturaModel->listar($this->tenantId, $filtros, $limite, $offset);
        $total          = $this->facturaModel->contar($this->tenantId, $filtros);
        $rawResumen = $this->facturaModel->obtenerResumenEstados(
            $this->tenantId,
            $filtros['fecha_desde'] ?: null,
            $filtros['fecha_hasta'] ?: null
        );
        // Transform to flat map keyed by estado_sri → cantidad (as expected by the view)
        $resumenEstados = [];
        foreach ($rawResumen as $row) {
            $resumenEstados[$row['estado_sri']] = (int) $row['cantidad'];
        }

        $this->viewData['facturas']         = $facturas;
        $this->viewData['filtros']          = $filtros;
        $this->viewData['paginacion']       = [
            'pagina'           => $pagina,
            'total_paginas'    => ceil($total / $limite),
            'total_registros'  => $total,
        ];
        $this->viewData['resumen_estados']  = $resumenEstados;
        $this->viewData['title']            = 'Facturas Electrónicas';

        $this->renderModule('facturacion/facturas_electronicas/index', $this->viewData);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EMITIR
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Emite una factura electrónica al SRI.
     * Flujo: generar XML → firmar → guardar BD → enviar → consultar autorización.
     * Espera POST con: factura_id, csrf_token, [forma_pago='01']
     */
    public function emitir() {
        $this->authorize('crear', 'facturacion');

        if (!$this->isPost()) {
            return $this->jsonError('Método no permitido', 405);
        }
        if (!$this->validateCsrf()) {
            return $this->jsonError('Token de seguridad inválido', 403);
        }

        $facturaId = (int) $this->post('factura_id');
        $formaPago = $this->post('forma_pago') ?? '01';

        if ($facturaId < 1) {
            return $this->jsonError('Factura no especificada', 400);
        }

        try {
            // 1. Cargar datos de la factura origen
            $datosFactura = $this->obtenerDatosFactura($facturaId, $formaPago);
            if (!$datosFactura) {
                return $this->jsonError('Factura no encontrada', 404);
            }

            // Verificar que no exista una FE autorizada para esta factura
            $existente = $this->buscarFEPorFacturaId($facturaId);
            if ($existente && $existente['fac_estado_sri'] === 'AUTORIZADO') {
                return $this->jsonError('Esta factura ya tiene un comprobante electrónico autorizado', 409);
            }

            // 2. Generar secuencial y clave de acceso
            $establecimiento = $this->configSRI['emisor']['codigo_establecimiento'];
            $puntoEmision    = $this->configSRI['emisor']['punto_emision'];
            $tipoComprobante = '01';
            $ambiente        = (string) $this->configSRI['ambiente'];

            $secuencial      = $this->facturaService->obtenerSecuencial(
                $this->tenantId, $establecimiento, $puntoEmision, $tipoComprobante
            );
            $codigoNumerico  = $this->facturaService->generarCodigoNumerico();
            $fechaEmisionFmt = date('dmY');

            $claveAcceso = $this->facturaService->generarClaveAcceso(
                $fechaEmisionFmt,
                $tipoComprobante,
                $this->configSRI['emisor']['ruc'],
                $ambiente,
                $establecimiento . $puntoEmision,
                $secuencial,
                $codigoNumerico,
                (string) $this->configSRI['tipo_emision']
            );

            // Completar datos para el XML
            $datosFactura['clave_acceso']    = $claveAcceso;
            $datosFactura['secuencial']      = $secuencial;
            $datosFactura['establecimiento'] = $establecimiento;
            $datosFactura['punto_emision']   = $puntoEmision;
            $datosFactura['fecha_emision']   = date('d/m/Y');
            $datosFactura['numero_completo'] = $establecimiento . '-' . $puntoEmision . '-' . $secuencial;

            // 3. Generar XML
            $xml         = $this->facturaService->generarXMLFactura($datosFactura);
            $archivoXML  = $this->facturaService->guardarXML($xml, $claveAcceso, 'generados');

            // 4. Firmar XML
            $this->firmaService->cargarCertificado($this->tenantCertPath, $this->tenantCertClave);
            $xmlFirmado     = $this->firmaService->firmarXML($xml);
            $archivoFirmado = $this->facturaService->guardarXML($xmlFirmado, $claveAcceso, 'firmados');

            // 5. Guardar cabecera en BD
            $idFE = $this->facturaModel->crear([
                'tenant_id'                  => $this->tenantId,
                'factura_id'                 => $facturaId,
                'clave_acceso'               => $claveAcceso,
                'tipo_comprobante'           => $tipoComprobante,
                'establecimiento'            => $establecimiento,
                'punto_emision'              => $puntoEmision,
                'secuencial'                 => $secuencial,
                'fecha_emision'              => date('Y-m-d'),
                'ambiente'                   => $ambiente,
                'tipo_emision'               => (string) $this->configSRI['tipo_emision'],
                'cliente_id'                 => $datosFactura['cliente']['id'] ?? null,
                'cliente_tipo_identificacion'=> $datosFactura['cliente']['tipo_identificacion'],
                'cliente_identificacion'     => $datosFactura['cliente']['identificacion'],
                'cliente_razon_social'       => $datosFactura['cliente']['razon_social'],
                'cliente_direccion'          => $datosFactura['cliente']['direccion'] ?? null,
                'cliente_email'              => $datosFactura['cliente']['email'] ?? null,
                'subtotal_iva'               => $datosFactura['totales']['subtotal_iva'] ?? $datosFactura['totales']['subtotal'],
                'subtotal_0'                 => $datosFactura['totales']['subtotal_0'] ?? 0,
                'subtotal'                   => $datosFactura['totales']['subtotal'],
                'descuento'                  => $datosFactura['totales']['descuento'] ?? 0,
                'iva'                        => $datosFactura['totales']['iva'],
                'total'                      => $datosFactura['totales']['total'],
                'estado_sri'                 => 'GENERADA',
                'xml_generado'               => $archivoXML,
                'xml_firmado'                => $archivoFirmado,
                'created_by'                 => (int) ($_SESSION['user_id'] ?? 0),
            ]);

            if (!$idFE) {
                throw new \Exception('No se pudo guardar la factura electrónica en la base de datos');
            }

            // 6. Guardar detalles en tablas auxiliares
            $this->facturaModel->guardarDetalles(
                $idFE,
                $datosFactura['detalles'],
                $datosFactura['pagos'],
                $datosFactura['info_adicional'] ?? []
            );

            // Marcar como firmada
            $this->facturaModel->actualizarEstado($idFE, 'FIRMADA', [
                'xml_firmado' => $archivoFirmado,
            ]);

            // 7. Enviar al SRI (recepción + consulta autorización)
            $resultado = $this->webServiceSRI->procesarComprobante(
                $xmlFirmado, $claveAcceso
            );

            if ($resultado['exito']) {
                $autorizacion   = $resultado['resultado']['autorizaciones'][0] ?? [];
                $archivoAutorizado = null;

                if (!empty($autorizacion['comprobante'])) {
                    $archivoAutorizado = $this->facturaService->guardarXML(
                        $autorizacion['comprobante'],
                        $claveAcceso,
                        'autorizados'
                    );
                }

                $this->facturaModel->actualizarEstado($idFE, 'AUTORIZADO', [
                    'numero_autorizacion' => $autorizacion['numero_autorizacion'] ?? $claveAcceso,
                    'fecha_autorizacion'  => $autorizacion['fecha_autorizacion']  ?? date('Y-m-d H:i:s'),
                    'xml_autorizado'      => $archivoAutorizado,
                ]);

                // Generar RIDE
                $rideHtml = $this->rideService->generarRIDEHtml($datosFactura, $autorizacion);
                $this->rideService->guardarRIDE($rideHtml, $claveAcceso);

                return $this->jsonSuccess([
                    'id_fe'               => $idFE,
                    'clave_acceso'        => $claveAcceso,
                    'numero_autorizacion' => $autorizacion['numero_autorizacion'] ?? $claveAcceso,
                    'numero_factura'      => $datosFactura['numero_completo'],
                ], 'Factura electrónica emitida y autorizada exitosamente');

            } elseif (in_array($resultado['etapa'] ?? '', ['en_procesamiento', 'timeout'])) {
                // SRI recibió el comprobante pero aún no terminó de procesar
                $this->facturaModel->actualizarEstado($idFE, 'ENVIADA', []);

                return $this->jsonSuccess([
                    'id_fe'        => $idFE,
                    'clave_acceso' => $claveAcceso,
                    'estado'       => 'EN_PROCESAMIENTO',
                ], $resultado['mensaje'] ?? 'Factura enviada al SRI. Está en procesamiento, verifique el estado en unos minutos.');

            } else {
                $mensajeError = $this->extraerMensajeError($resultado);
                $this->facturaModel->actualizarEstado($idFE, 'ERROR', [
                    'mensaje_error' => $mensajeError,
                ]);

                return $this->jsonError('Error al autorizar: ' . $mensajeError, 400);
            }

        } catch (\Exception $e) {
            error_log('[FE] Error emitir: ' . $e->getMessage());
            return $this->jsonError('Error al emitir la factura electrónica: ' . $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VER DETALLE
    // ─────────────────────────────────────────────────────────────────────────

    public function ver() {
        $this->authorize('ver', 'facturacion');

        $id = (int) ($this->get('id') ?? 0);
        if ($id < 1) {
            return $this->error('ID no proporcionado', 400);
        }

        $factura = $this->facturaModel->obtenerPorId($id, $this->tenantId);
        if (!$factura) {
            return $this->error('Factura no encontrada', 404);
        }

        // Descifrar campos sensibles almacenados encriptados
        if (!empty($factura['fac_cliente_email'])) {
            $factura['fac_cliente_email'] = \DataProtection::decrypt($factura['fac_cliente_email'])
                ?? $factura['fac_cliente_email'];
        }

        $this->viewData['factura'] = $factura;
        $this->viewData['title']   = 'Detalle Factura Electrónica';
        $this->renderModule('facturacion/facturas_electronicas/ver', $this->viewData);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DESCARGAR XML
    // ─────────────────────────────────────────────────────────────────────────

    public function descargarXML() {
        $this->authorize('ver', 'facturacion');

        $id   = (int) ($this->get('id') ?? 0);
        $tipo = $this->get('tipo') ?? 'autorizado';

        $factura = $this->facturaModel->obtenerPorId($id, $this->tenantId);
        if (!$factura) {
            return $this->error('Factura no encontrada', 404);
        }

        $archivo = match ($tipo) {
            'generado' => $factura['fac_xml_generado'],
            'firmado'  => $factura['fac_xml_firmado'],
            default    => $factura['fac_xml_autorizado'] ?? $factura['fac_xml_firmado'],
        };

        if (!$archivo || !file_exists($archivo)) {
            return $this->error('Archivo XML no encontrado', 404);
        }

        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $factura['fac_clave_acceso'] . '_' . $tipo . '.xml"');
        readfile($archivo);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DESCARGAR RIDE
    // ─────────────────────────────────────────────────────────────────────────

    public function descargarRIDE() {
        $this->authorize('ver', 'facturacion');

        $id      = (int) ($this->get('id') ?? 0);
        $factura = $this->facturaModel->obtenerPorId($id, $this->tenantId);
        if (!$factura) {
            return $this->error('Factura no encontrada', 404);
        }

        $base        = $this->configSRI['storage']['ride'] . $factura['fac_clave_acceso'];
        $archivoPdf  = $base . '.pdf';
        $archivoHtml = $base . '.html';

        if (file_exists($archivoPdf)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="RIDE_' . $factura['fac_clave_acceso'] . '.pdf"');
            readfile($archivoPdf);
            exit;
        }

        if (file_exists($archivoHtml)) {
            header('Content-Type: text/html; charset=UTF-8');
            readfile($archivoHtml);
            exit;
        }

        return $this->error('RIDE no generado aún', 404);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REENVIAR AL SRI
    // ─────────────────────────────────────────────────────────────────────────

    public function reenviar() {
        $this->authorize('crear', 'facturacion');

        if (!$this->isPost()) {
            return $this->jsonError('Método no permitido', 405);
        }

        $id      = (int) ($this->get('id') ?? $this->post('id') ?? 0);
        $factura = $this->facturaModel->obtenerPorId($id, $this->tenantId);
        if (!$factura) {
            return $this->jsonError('Factura no encontrada', 404);
        }
        if ($factura['fac_estado_sri'] === 'AUTORIZADO') {
            return $this->jsonError('La factura ya está autorizada', 400);
        }
        if (empty($factura['fac_xml_firmado']) || !file_exists($factura['fac_xml_firmado'])) {
            return $this->jsonError('No se encontró el XML firmado para reenviar', 400);
        }

        try {
            $xmlFirmado = file_get_contents($factura['fac_xml_firmado']);
            $resultado  = $this->webServiceSRI->procesarComprobante(
                $xmlFirmado, $factura['fac_clave_acceso']
            );

            if ($resultado['exito']) {
                $autorizacion = $resultado['resultado']['autorizaciones'][0] ?? [];
                $this->facturaModel->actualizarEstado($id, 'AUTORIZADO', [
                    'numero_autorizacion' => $autorizacion['numero_autorizacion'] ?? $factura['fac_clave_acceso'],
                    'fecha_autorizacion'  => $autorizacion['fecha_autorizacion']  ?? date('Y-m-d H:i:s'),
                ]);
                return $this->jsonSuccess([
                    'numero_autorizacion' => $autorizacion['numero_autorizacion'] ?? $factura['fac_clave_acceso'],
                    'estado' => 'AUTORIZADO',
                ], 'Factura autorizada exitosamente');

            } elseif (in_array($resultado['etapa'] ?? '', ['en_procesamiento', 'timeout'])) {
                $this->facturaModel->actualizarEstado($id, 'ENVIADA', []);
                return $this->jsonSuccess([
                    'estado' => 'EN_PROCESAMIENTO',
                ], $resultado['mensaje'] ?? 'Aún en procesamiento en el SRI. Intente nuevamente en unos minutos.');

            } else {
                $mensajeError = $this->extraerMensajeError($resultado);
                $this->facturaModel->actualizarEstado($id, 'ERROR', [
                    'mensaje_error' => $mensajeError,
                ]);
                return $this->jsonError('Error SRI: ' . $mensajeError, 400);
            }

        } catch (\Exception $e) {
            return $this->jsonError('Error al reenviar: ' . $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSULTAR ESTADO EN SRI
    // ─────────────────────────────────────────────────────────────────────────

    public function consultarEstado() {
        $this->authorize('ver', 'facturacion');

        $claveAcceso = $this->get('clave_acceso') ?? '';
        if (!$claveAcceso) {
            return $this->jsonError('Clave de acceso no proporcionada', 400);
        }

        try {
            $resultado = $this->webServiceSRI->consultarAutorizacion($claveAcceso);
            return $this->jsonSuccess($resultado);
        } catch (\Exception $e) {
            return $this->jsonError('Error al consultar: ' . $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VERIFICAR Y ACTUALIZAR ESTADO DE FE ENVIADA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Consulta al SRI el estado de una FE en estado ENVIADA y actualiza la BD.
     * GET: ?factura_id=X  (ID de la factura origen, no de la FE)
     */
    public function verificarEstado() {
        $this->authorize('ver', 'facturacion');

        $facturaId = (int) ($this->get('factura_id') ?? 0);
        if ($facturaId < 1) {
            return $this->jsonError('Factura no especificada', 400);
        }

        $fe = $this->buscarFEPorFacturaId($facturaId);
        if (!$fe) {
            return $this->jsonError('No existe comprobante electrónico para esta factura', 404);
        }

        // Obtener clave de acceso
        $stmt = $this->db->prepare("
            SELECT fac_id, fac_clave_acceso, fac_estado_sri, fac_xml_firmado
            FROM facturas_electronicas WHERE fac_id = ? AND fac_tenant_id = ?
        ");
        $stmt->execute([$fe['fac_id'], $this->tenantId]);
        $feData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$feData) {
            return $this->jsonError('Registro de FE no encontrado', 404);
        }

        if ($feData['fac_estado_sri'] === 'AUTORIZADO') {
            return $this->jsonSuccess(['estado' => 'AUTORIZADO'], 'La factura ya está autorizada');
        }

        try {
            $autorizacion = $this->webServiceSRI->consultarAutorizacion($feData['fac_clave_acceso']);

            if ($autorizacion['exito']) {
                $auth = $autorizacion['autorizaciones'][0] ?? [];
                $archivoAutorizado = null;

                if (!empty($auth['comprobante'])) {
                    $archivoAutorizado = $this->facturaService->guardarXML(
                        $auth['comprobante'], $feData['fac_clave_acceso'], 'autorizados'
                    );
                }

                $this->facturaModel->actualizarEstado($feData['fac_id'], 'AUTORIZADO', [
                    'numero_autorizacion' => $auth['numero_autorizacion'] ?? $feData['fac_clave_acceso'],
                    'fecha_autorizacion'  => $auth['fecha_autorizacion']  ?? date('Y-m-d H:i:s'),
                    'xml_autorizado'      => $archivoAutorizado,
                ]);

                return $this->jsonSuccess([
                    'estado'              => 'AUTORIZADO',
                    'numero_autorizacion' => $auth['numero_autorizacion'] ?? $feData['fac_clave_acceso'],
                ], 'Factura autorizada por el SRI');
            }

            // Revisar si sigue en procesamiento o hay error definitivo
            $estadoSRI = $autorizacion['autorizaciones'][0]['estado'] ?? '';

            if ($estadoSRI === 'NO AUTORIZADO') {
                $mensajeError = $this->extraerMensajeError(['resultado' => $autorizacion]);
                $this->facturaModel->actualizarEstado($feData['fac_id'], 'ERROR', [
                    'mensaje_error' => $mensajeError,
                ]);
                return $this->jsonError('No autorizado por el SRI: ' . $mensajeError, 400);
            }

            // Sigue en procesamiento
            return $this->jsonSuccess([
                'estado' => 'EN_PROCESAMIENTO',
            ], 'El SRI aún está procesando el comprobante. Intente en unos minutos.');

        } catch (\Exception $e) {
            return $this->jsonError('Error al consultar estado: ' . $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VERIFICAR CONEXIÓN SRI
    // ─────────────────────────────────────────────────────────────────────────

    public function verificarConexion() {
        $this->authorize('ver', 'facturacion');

        $resultado = $this->webServiceSRI->verificarConectividad();
        return $this->jsonSuccess([
            'conectividad' => $resultado,
            'ambiente'     => $this->configSRI['ambiente'] == 1 ? 'PRUEBAS' : 'PRODUCCIÓN',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INFO CERTIFICADO
    // ─────────────────────────────────────────────────────────────────────────

    public function infoCertificado() {
        $this->authorize('ver', 'facturacion');

        try {
            $this->firmaService->cargarCertificado($this->tenantCertPath, $this->tenantCertClave);
            $info = $this->firmaService->obtenerInfoCertificado();
            return $this->jsonSuccess($info);
        } catch (\Exception $e) {
            return $this->jsonError('Error al leer certificado: ' . $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Construye la estructura de datos necesaria para generar el XML
     *
     * @param int    $facturaId  ID en facturacion_facturas
     * @param string $formaPago  Código SRI de forma de pago (ej. '01')
     * @return array|null
     */
    private function obtenerDatosFactura($facturaId, $formaPago = '01') {
        try {
            $stmt = $this->db->prepare("
                SELECT f.*,
                       c.cli_tipo_identificacion  AS tipo_identificacion,
                       c.cli_identificacion        AS identificacion,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS razon_social,
                       c.cli_email                AS email,
                       c.cli_direccion            AS direccion,
                       c.cli_telefono             AS telefono
                FROM facturacion_facturas f
                LEFT JOIN clientes c ON f.fac_cliente_id = c.cli_cliente_id
                WHERE f.fac_id = ? AND f.fac_tenant_id = ?
            ");
            $stmt->execute([$facturaId, $this->tenantId]);
            $factura = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$factura) {
                return null;
            }

            // Líneas
            $stmt = $this->db->prepare("
                SELECT * FROM facturacion_lineas WHERE lin_factura_id = ?
            ");
            $stmt->execute([$facturaId]);
            $lineas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Mapear tipo de identificación a código SRI
            $tipoIdentMap = [
                'RUC'             => '04',
                'CEDULA'          => '05',
                'PASAPORTE'       => '06',
                'CONSUMIDOR_FINAL'=> '07',
            ];
            $tipoIdent = $tipoIdentMap[$factura['tipo_identificacion'] ?? ''] ?? '05';

            // Construir detalles
            $detalles = [];
            $subtotalIva = 0;
            $subtotal0   = 0;
            $totalIva    = 0;

            foreach ($lineas as $lin) {
                $porcentajeIva = (float) ($lin['lin_porcentaje_iva'] ?? 15);
                $subtotalLin   = (float) $lin['lin_cantidad'] * (float) $lin['lin_precio_unitario'] - (float) ($lin['lin_descuento'] ?? 0);
                $ivaLin        = $subtotalLin * $porcentajeIva / 100;

                if ($porcentajeIva > 0) {
                    $subtotalIva += $subtotalLin;
                    $totalIva    += $ivaLin;
                } else {
                    $subtotal0   += $subtotalLin;
                }

                $detalles[] = [
                    'codigo'                  => $lin['lin_codigo'] ?? 'SERV001',
                    'descripcion'             => $lin['lin_descripcion'],
                    'cantidad'                => (float) $lin['lin_cantidad'],
                    'precio_unitario'         => (float) $lin['lin_precio_unitario'],
                    'descuento'               => (float) ($lin['lin_descuento'] ?? 0),
                    'precio_total_sin_impuesto' => $subtotalLin,
                    'impuestos'               => [
                        [
                            'codigo'            => '2',
                            'codigo_porcentaje'  => $this->codigoIva($porcentajeIva),
                            'tarifa'            => $porcentajeIva,
                            'base_imponible'    => $subtotalLin,
                            'valor'             => $ivaLin,
                        ],
                    ],
                ];
            }

            $totalGeneral = $subtotalIva + $subtotal0 + $totalIva;

            return [
                'cliente' => [
                    'id'                  => $factura['fac_cliente_id'],
                    'tipo_identificacion' => $tipoIdent,
                    'identificacion'      => \DataProtection::decrypt($factura['identificacion'] ?? null) ?? 'CONSUMIDOR FINAL',
                    'razon_social'        => trim($factura['razon_social'] ?? 'Consumidor Final'),
                    'direccion'           => $factura['direccion'] ?? 'N/A',
                    'email'               => \DataProtection::decrypt($factura['email'] ?? null) ?? ($factura['email'] ?? ''),
                    'telefono'            => \DataProtection::decrypt($factura['telefono'] ?? null) ?? ($factura['telefono'] ?? ''),
                ],
                'detalles' => $detalles,
                'totales'  => [
                    'subtotal_iva'      => $subtotalIva,
                    'subtotal_0'        => $subtotal0,
                    'subtotal'          => $subtotalIva + $subtotal0,
                    'descuento'         => (float) ($factura['fac_descuento'] ?? 0),
                    'iva'               => $totalIva,
                    'porcentaje_iva'    => $porcentajeIva,
                    'total'             => $totalGeneral,
                    'impuestos'         => array_values(array_filter([
                        $subtotalIva > 0 ? [
                            'codigo'            => '2',
                            'codigo_porcentaje' => $this->codigoIva($porcentajeIva),
                            'tarifa'            => $porcentajeIva,
                            'base_imponible'    => $subtotalIva,
                            'valor'             => $totalIva,
                        ] : null,
                        $subtotal0 > 0 ? [
                            'codigo'            => '2',
                            'codigo_porcentaje' => '0',
                            'tarifa'            => 0,
                            'base_imponible'    => $subtotal0,
                            'valor'             => 0,
                        ] : null,
                    ])),
                ],
                'pagos' => [
                    [
                        'forma_pago'   => $formaPago,
                        'total'        => $totalGeneral,
                        'plazo'        => null,
                        'unidad_tiempo'=> 'dias',
                    ],
                ],
                'info_adicional' => array_filter([
                    'Email'    => \DataProtection::decrypt($factura['email']    ?? null) ?? ($factura['email']    ?? ''),
                    'Teléfono' => \DataProtection::decrypt($factura['telefono'] ?? null) ?? ($factura['telefono'] ?? ''),
                ]),
            ];

        } catch (\Exception $e) {
            error_log('[FE] obtenerDatosFactura: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mapea porcentaje de IVA a código SRI (Tabla 16)
     * 0% → '0'  | 12% → '2' | 14% → '3' | 15% → '4'
     */
    private function codigoIva(float $porcentaje): string {
        $mapa = ['0' => '0', '12' => '2', '14' => '3', '15' => '4'];
        return $mapa[(string)(int)$porcentaje] ?? '4';
    }

    /**
     * Busca una FE previa para una factura origen
     */
    private function buscarFEPorFacturaId($facturaId) {
        try {
            $stmt = $this->db->prepare("
                SELECT fac_id, fac_estado_sri FROM facturas_electronicas
                WHERE fac_factura_id = ? AND fac_tenant_id = ?
                ORDER BY fac_id DESC LIMIT 1
            ");
            $stmt->execute([$facturaId, $this->tenantId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            error_log('[FE] buscarFEPorFacturaId: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extrae mensaje de error legible de la respuesta del SRI
     */
    private function extraerMensajeError(array $resultado): string {
        if (!empty($resultado['resultado']['autorizaciones'][0]['mensajes'])) {
            $mensajes = $resultado['resultado']['autorizaciones'][0]['mensajes'];
            return implode('. ', array_map(
                fn($m) => $m['mensaje'] . (isset($m['informacion_adicional']) ? ': ' . $m['informacion_adicional'] : ''),
                $mensajes
            ));
        }
        if (!empty($resultado['resultado']['comprobantes'][0]['mensajes'])) {
            $mensajes = $resultado['resultado']['comprobantes'][0]['mensajes'];
            return implode('. ', array_map(
                fn($m) => $m['mensaje'] . (isset($m['informacion_adicional']) ? ': ' . $m['informacion_adicional'] : ''),
                $mensajes
            ));
        }
        return $resultado['mensaje'] ?? 'Error desconocido del SRI';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MÉTODOS DE RESPUESTA JSON (compatibles con BaseController)
    // ─────────────────────────────────────────────────────────────────────────

    private function jsonSuccess($data = [], $mensaje = 'OK') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $mensaje, 'data' => $data]);
        exit;
    }

    private function jsonError($mensaje, $codigo = 400) {
        header('Content-Type: application/json');
        http_response_code($codigo);
        echo json_encode(['success' => false, 'message' => $mensaje]);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONFIGURACIÓN POR TENANT (facturacion_configuracion)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Lee facturacion_configuracion para el tenant activo y superpone los
     * valores sobre $this->configSRI, que actúa como fallback.
     * También almacena la ruta y clave del certificado tenant.
     */
    private function aplicarConfigTenant(): void {
        if (empty($this->tenantId)) {
            return;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM facturacion_configuracion
                WHERE cfg_tenant_id = ? AND cfg_estado = 'A'
                LIMIT 1
            ");
            $stmt->execute([$this->tenantId]);
            $cfg = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('[FE] aplicarConfigTenant: ' . $e->getMessage());
            return;
        }

        if (!$cfg) {
            return;
        }

        // Superponer datos del emisor
        $emisor = &$this->configSRI['emisor'];
        if (!empty($cfg['cfg_ruc']))                        $emisor['ruc']                      = $cfg['cfg_ruc'];
        if (!empty($cfg['cfg_razon_social']))               $emisor['razon_social']              = $cfg['cfg_razon_social'];
        if (!empty($cfg['cfg_nombre_comercial']))           $emisor['nombre_comercial']          = $cfg['cfg_nombre_comercial'];
        if (!empty($cfg['cfg_direccion_matriz']))           $emisor['direccion_matriz']          = $cfg['cfg_direccion_matriz'];
        if (!empty($cfg['cfg_direccion_establecimiento'])) $emisor['direccion_establecimiento'] = $cfg['cfg_direccion_establecimiento'];
        if (!empty($cfg['cfg_codigo_establecimiento']))     $emisor['codigo_establecimiento']    = $cfg['cfg_codigo_establecimiento'];
        if (!empty($cfg['cfg_punto_emision']))              $emisor['punto_emision']             = $cfg['cfg_punto_emision'];
        if (!empty($cfg['cfg_obligado_contabilidad']))      $emisor['obligado_contabilidad']     = $cfg['cfg_obligado_contabilidad'];
        if (!empty($cfg['cfg_contribuyente_especial']))     $emisor['contribuyente_especial']    = $cfg['cfg_contribuyente_especial'];
        if (!empty($cfg['cfg_agente_retencion']))           $emisor['agente_retencion']          = $cfg['cfg_agente_retencion'];
        if (isset($cfg['cfg_regimen_microempresas']))       $emisor['regimen_microempresas']     = $cfg['cfg_regimen_microempresas'];
        if (isset($cfg['cfg_regimen_rimpe']))               $emisor['regimen_rimpe']             = $cfg['cfg_regimen_rimpe'];

        // Superponer ambiente
        if (!empty($cfg['cfg_ambiente'])) {
            $this->configSRI['ambiente'] = (int) $cfg['cfg_ambiente'];
        }

        // Guardar datos del certificado para cargarCertificado()
        if (!empty($cfg['cfg_certificado_ruta']) && file_exists($cfg['cfg_certificado_ruta'])) {
            $this->tenantCertPath = $cfg['cfg_certificado_ruta'];

            if (!empty($cfg['cfg_certificado_clave'])) {
                try {
                    $this->tenantCertClave = \Security::decryptSensitiveData($cfg['cfg_certificado_clave']);
                } catch (\Exception $e) {
                    error_log('[FE] No se pudo descifrar clave certificado tenant: ' . $e->getMessage());
                }
            }
        }
    }
}
