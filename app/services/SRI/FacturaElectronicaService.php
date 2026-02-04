<?php
/**
 * DigiSports - Servicio de Facturación Electrónica SRI
 * Generación de XML según esquema del SRI Ecuador
 * 
 * @package DigiSports\Services\SRI
 * @version 1.0.0
 */

namespace App\Services\SRI;

class FacturaElectronicaService {
    
    private $config;
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->config = require BASE_PATH . '/config/sri.php';
        $this->db = \Database::getInstance()->getConnection();
    }
    
    /**
     * Generar clave de acceso de 49 dígitos
     * 
     * @param string $fechaEmision Fecha de emisión (ddmmaaaa)
     * @param string $tipoComprobante Código del tipo de comprobante
     * @param string $ruc RUC del emisor
     * @param string $ambiente Ambiente (1=Pruebas, 2=Producción)
     * @param string $serie Código establecimiento + Punto emisión
     * @param string $secuencial Número secuencial
     * @param string $codigoNumerico Código numérico aleatorio de 8 dígitos
     * @param string $tipoEmision Tipo de emisión
     * @return string Clave de acceso de 49 dígitos
     */
    public function generarClaveAcceso($fechaEmision, $tipoComprobante, $ruc, $ambiente, $serie, $secuencial, $codigoNumerico, $tipoEmision) {
        // Construir clave sin dígito verificador (48 dígitos)
        $clave = $fechaEmision . $tipoComprobante . $ruc . $ambiente . $serie . $secuencial . $codigoNumerico . $tipoEmision;
        
        // Calcular dígito verificador módulo 11
        $digitoVerificador = $this->calcularModulo11($clave);
        
        return $clave . $digitoVerificador;
    }
    
    /**
     * Calcular dígito verificador módulo 11
     * 
     * @param string $cadena Cadena de 48 dígitos
     * @return int Dígito verificador
     */
    private function calcularModulo11($cadena) {
        $coeficientes = [2, 3, 4, 5, 6, 7];
        $suma = 0;
        $j = 0;
        
        // Recorrer de derecha a izquierda
        for ($i = strlen($cadena) - 1; $i >= 0; $i--) {
            $suma += intval($cadena[$i]) * $coeficientes[$j];
            $j = ($j + 1) % 6;
        }
        
        $residuo = $suma % 11;
        $digito = 11 - $residuo;
        
        if ($digito == 11) {
            return 0;
        } elseif ($digito == 10) {
            return 1;
        }
        
        return $digito;
    }
    
    /**
     * Generar código numérico aleatorio de 8 dígitos
     * 
     * @return string Código de 8 dígitos
     */
    public function generarCodigoNumerico() {
        return str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
    }
    
    /**
     * Obtener siguiente secuencial para factura
     * 
     * @param int $tenantId ID del tenant
     * @param string $establecimiento Código establecimiento
     * @param string $puntoEmision Código punto de emisión
     * @return string Secuencial formateado
     */
    public function obtenerSecuencial($tenantId, $establecimiento = '001', $puntoEmision = '001') {
        try {
            // Buscar último secuencial
            $stmt = $this->db->prepare("
                SELECT MAX(secuencial) as ultimo
                FROM facturas_electronicas
                WHERE tenant_id = ?
                AND establecimiento = ?
                AND punto_emision = ?
                AND YEAR(fecha_emision) = YEAR(CURDATE())
            ");
            $stmt->execute([$tenantId, $establecimiento, $puntoEmision]);
            $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $siguiente = ($resultado['ultimo'] ?? 0) + 1;
            
            return str_pad($siguiente, $this->config['secuencial']['longitud'], $this->config['secuencial']['padding'], STR_PAD_LEFT);
        } catch (\Exception $e) {
            throw new \Exception("Error al obtener secuencial: " . $e->getMessage());
        }
    }
    
    /**
     * Generar XML de factura electrónica
     * 
     * @param array $datosFactura Datos de la factura
     * @return string XML generado
     */
    public function generarXMLFactura($datosFactura) {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Elemento raíz: factura
        $factura = $xml->createElement('factura');
        $factura->setAttribute('id', 'comprobante');
        $factura->setAttribute('version', $this->config['version_factura']);
        $xml->appendChild($factura);
        
        // Información Tributaria
        $infoTributaria = $this->crearInfoTributaria($xml, $datosFactura);
        $factura->appendChild($infoTributaria);
        
        // Información de la Factura
        $infoFactura = $this->crearInfoFactura($xml, $datosFactura);
        $factura->appendChild($infoFactura);
        
        // Detalles
        $detalles = $this->crearDetalles($xml, $datosFactura['detalles']);
        $factura->appendChild($detalles);
        
        // Información Adicional (opcional)
        if (!empty($datosFactura['info_adicional'])) {
            $infoAdicional = $this->crearInfoAdicional($xml, $datosFactura['info_adicional']);
            $factura->appendChild($infoAdicional);
        }
        
        return $xml->saveXML();
    }
    
    /**
     * Crear sección infoTributaria
     */
    private function crearInfoTributaria($xml, $datosFactura) {
        $infoTributaria = $xml->createElement('infoTributaria');
        
        $elementos = [
            'ambiente' => $this->config['ambiente'],
            'tipoEmision' => $this->config['tipo_emision'],
            'razonSocial' => $this->config['emisor']['razon_social'],
            'nombreComercial' => $this->config['emisor']['nombre_comercial'],
            'ruc' => $this->config['emisor']['ruc'],
            'claveAcceso' => $datosFactura['clave_acceso'],
            'codDoc' => '01', // Factura
            'estab' => $datosFactura['establecimiento'] ?? $this->config['emisor']['codigo_establecimiento'],
            'ptoEmi' => $datosFactura['punto_emision'] ?? $this->config['emisor']['punto_emision'],
            'secuencial' => $datosFactura['secuencial'],
            'dirMatriz' => $this->config['emisor']['direccion_matriz'],
        ];
        
        // Agregar agente de retención si aplica
        if (!empty($this->config['emisor']['agente_retencion'])) {
            $elementos['agenteRetencion'] = $this->config['emisor']['agente_retencion'];
        }
        
        // Agregar régimen microempresas si aplica
        if ($this->config['emisor']['regimen_microempresas'] === 'SI') {
            $elementos['contribuyenteRimpe'] = 'CONTRIBUYENTE NEGOCIO POPULAR - RÉGIMEN RIMPE';
        } elseif ($this->config['emisor']['regimen_rimpe'] === 'SI') {
            $elementos['contribuyenteRimpe'] = 'CONTRIBUYENTE RÉGIMEN RIMPE';
        }
        
        foreach ($elementos as $nombre => $valor) {
            $elemento = $xml->createElement($nombre, htmlspecialchars($valor));
            $infoTributaria->appendChild($elemento);
        }
        
        return $infoTributaria;
    }
    
    /**
     * Crear sección infoFactura
     */
    private function crearInfoFactura($xml, $datosFactura) {
        $infoFactura = $xml->createElement('infoFactura');
        
        $elementos = [
            'fechaEmision' => $datosFactura['fecha_emision'], // dd/mm/aaaa
            'dirEstablecimiento' => $this->config['emisor']['direccion_establecimiento'],
            'obligadoContabilidad' => $this->config['emisor']['obligado_contabilidad'],
            'tipoIdentificacionComprador' => $datosFactura['cliente']['tipo_identificacion'],
            'razonSocialComprador' => $datosFactura['cliente']['razon_social'],
            'identificacionComprador' => $datosFactura['cliente']['identificacion'],
            'direccionComprador' => $datosFactura['cliente']['direccion'] ?? 'N/A',
            'totalSinImpuestos' => number_format($datosFactura['totales']['subtotal'], 2, '.', ''),
            'totalDescuento' => number_format($datosFactura['totales']['descuento'] ?? 0, 2, '.', ''),
        ];
        
        // Contribuyente especial si aplica
        if (!empty($this->config['emisor']['contribuyente_especial'])) {
            $elementos['contribuyenteEspecial'] = $this->config['emisor']['contribuyente_especial'];
        }
        
        foreach ($elementos as $nombre => $valor) {
            $elemento = $xml->createElement($nombre, htmlspecialchars($valor));
            $infoFactura->appendChild($elemento);
        }
        
        // Total con impuestos
        $totalConImpuestos = $xml->createElement('totalConImpuestos');
        
        foreach ($datosFactura['totales']['impuestos'] as $impuesto) {
            $totalImpuesto = $xml->createElement('totalImpuesto');
            
            $totalImpuesto->appendChild($xml->createElement('codigo', $impuesto['codigo']));
            $totalImpuesto->appendChild($xml->createElement('codigoPorcentaje', $impuesto['codigo_porcentaje']));
            $totalImpuesto->appendChild($xml->createElement('baseImponible', number_format($impuesto['base_imponible'], 2, '.', '')));
            $totalImpuesto->appendChild($xml->createElement('valor', number_format($impuesto['valor'], 2, '.', '')));
            
            $totalConImpuestos->appendChild($totalImpuesto);
        }
        
        $infoFactura->appendChild($totalConImpuestos);
        
        // Propina (opcional)
        $infoFactura->appendChild($xml->createElement('propina', '0.00'));
        
        // Importe total
        $infoFactura->appendChild($xml->createElement('importeTotal', number_format($datosFactura['totales']['total'], 2, '.', '')));
        
        // Moneda
        $infoFactura->appendChild($xml->createElement('moneda', 'DOLAR'));
        
        // Pagos
        $pagos = $xml->createElement('pagos');
        foreach ($datosFactura['pagos'] as $pago) {
            $pagoElement = $xml->createElement('pago');
            $pagoElement->appendChild($xml->createElement('formaPago', $pago['forma_pago']));
            $pagoElement->appendChild($xml->createElement('total', number_format($pago['total'], 2, '.', '')));
            if (!empty($pago['plazo'])) {
                $pagoElement->appendChild($xml->createElement('plazo', $pago['plazo']));
                $pagoElement->appendChild($xml->createElement('unidadTiempo', $pago['unidad_tiempo'] ?? 'dias'));
            }
            $pagos->appendChild($pagoElement);
        }
        $infoFactura->appendChild($pagos);
        
        return $infoFactura;
    }
    
    /**
     * Crear sección detalles
     */
    private function crearDetalles($xml, $detalles) {
        $detallesElement = $xml->createElement('detalles');
        
        foreach ($detalles as $item) {
            $detalle = $xml->createElement('detalle');
            
            $detalle->appendChild($xml->createElement('codigoPrincipal', htmlspecialchars($item['codigo'])));
            
            if (!empty($item['codigo_auxiliar'])) {
                $detalle->appendChild($xml->createElement('codigoAuxiliar', htmlspecialchars($item['codigo_auxiliar'])));
            }
            
            $detalle->appendChild($xml->createElement('descripcion', htmlspecialchars($item['descripcion'])));
            $detalle->appendChild($xml->createElement('cantidad', number_format($item['cantidad'], 2, '.', '')));
            $detalle->appendChild($xml->createElement('precioUnitario', number_format($item['precio_unitario'], 2, '.', '')));
            $detalle->appendChild($xml->createElement('descuento', number_format($item['descuento'] ?? 0, 2, '.', '')));
            $detalle->appendChild($xml->createElement('precioTotalSinImpuesto', number_format($item['precio_total_sin_impuesto'], 2, '.', '')));
            
            // Impuestos del detalle
            $impuestos = $xml->createElement('impuestos');
            foreach ($item['impuestos'] as $impuesto) {
                $impuestoElement = $xml->createElement('impuesto');
                $impuestoElement->appendChild($xml->createElement('codigo', $impuesto['codigo']));
                $impuestoElement->appendChild($xml->createElement('codigoPorcentaje', $impuesto['codigo_porcentaje']));
                $impuestoElement->appendChild($xml->createElement('tarifa', number_format($impuesto['tarifa'], 2, '.', '')));
                $impuestoElement->appendChild($xml->createElement('baseImponible', number_format($impuesto['base_imponible'], 2, '.', '')));
                $impuestoElement->appendChild($xml->createElement('valor', number_format($impuesto['valor'], 2, '.', '')));
                $impuestos->appendChild($impuestoElement);
            }
            $detalle->appendChild($impuestos);
            
            $detallesElement->appendChild($detalle);
        }
        
        return $detallesElement;
    }
    
    /**
     * Crear sección infoAdicional
     */
    private function crearInfoAdicional($xml, $infoAdicional) {
        $infoAdicionalElement = $xml->createElement('infoAdicional');
        
        foreach ($infoAdicional as $nombre => $valor) {
            $campoAdicional = $xml->createElement('campoAdicional', htmlspecialchars($valor));
            $campoAdicional->setAttribute('nombre', $nombre);
            $infoAdicionalElement->appendChild($campoAdicional);
        }
        
        return $infoAdicionalElement;
    }
    
    /**
     * Guardar XML generado
     * 
     * @param string $xml XML a guardar
     * @param string $claveAcceso Clave de acceso
     * @param string $tipo Tipo: generados, firmados, autorizados
     * @return string Ruta del archivo guardado
     */
    public function guardarXML($xml, $claveAcceso, $tipo = 'generados') {
        $directorio = $this->config['storage']['xml_' . $tipo];
        
        // Crear directorio si no existe
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        $archivo = $directorio . $claveAcceso . '.xml';
        file_put_contents($archivo, $xml);
        
        return $archivo;
    }
    
    /**
     * Validar estructura del XML
     * 
     * @param string $xml XML a validar
     * @return array Resultado de validación
     */
    public function validarXML($xml) {
        $errores = [];
        
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            $errores[] = [
                'linea' => $error->line,
                'mensaje' => trim($error->message),
            ];
        }
        libxml_clear_errors();
        
        return [
            'valido' => empty($errores),
            'errores' => $errores,
        ];
    }
}
