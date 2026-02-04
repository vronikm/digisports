<?php
/**
 * DigiSports - Servicio de Comunicación con Web Services del SRI
 * Envío y consulta de comprobantes electrónicos
 * 
 * @package DigiSports\Services\SRI
 * @version 1.0.0
 */

namespace App\Services\SRI;

class WebServiceSRIService {
    
    private $config;
    private $urlRecepcion;
    private $urlAutorizacion;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->config = require BASE_PATH . '/config/sri.php';
        $this->configurarUrls();
    }
    
    /**
     * Configurar URLs según ambiente
     */
    private function configurarUrls() {
        $ambiente = $this->config['ambiente'] == 1 ? 'pruebas' : 'produccion';
        $this->urlRecepcion = $this->config['webservices'][$ambiente]['recepcion'];
        $this->urlAutorizacion = $this->config['webservices'][$ambiente]['autorizacion'];
    }
    
    /**
     * Enviar comprobante al SRI
     * 
     * @param string $xmlFirmado XML firmado en base64
     * @return array Respuesta del SRI
     */
    public function enviarComprobante($xmlFirmado) {
        try {
            $xmlBase64 = base64_encode($xmlFirmado);
            
            $soapRequest = '<?xml version="1.0" encoding="UTF-8"?>
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ec="http://ec.gob.sri.ws.recepcion">
                <soapenv:Header/>
                <soapenv:Body>
                    <ec:validarComprobante>
                        <xml>' . $xmlBase64 . '</xml>
                    </ec:validarComprobante>
                </soapenv:Body>
            </soapenv:Envelope>';
            
            $response = $this->ejecutarSoapRequest($this->urlRecepcion, $soapRequest);
            
            return $this->parsearRespuestaRecepcion($response);
            
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'estado' => 'ERROR',
                'mensaje' => 'Error al enviar comprobante: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Consultar autorización de comprobante
     * 
     * @param string $claveAcceso Clave de acceso de 49 dígitos
     * @return array Respuesta de autorización
     */
    public function consultarAutorizacion($claveAcceso) {
        try {
            $soapRequest = '<?xml version="1.0" encoding="UTF-8"?>
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ec="http://ec.gob.sri.ws.autorizacion">
                <soapenv:Header/>
                <soapenv:Body>
                    <ec:autorizacionComprobante>
                        <claveAccesoComprobante>' . $claveAcceso . '</claveAccesoComprobante>
                    </ec:autorizacionComprobante>
                </soapenv:Body>
            </soapenv:Envelope>';
            
            $response = $this->ejecutarSoapRequest($this->urlAutorizacion, $soapRequest);
            
            return $this->parsearRespuestaAutorizacion($response);
            
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'estado' => 'ERROR',
                'mensaje' => 'Error al consultar autorización: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Ejecutar petición SOAP
     */
    private function ejecutarSoapRequest($url, $soapRequest) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $soapRequest,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: ""',
                'Content-Length: ' . strlen($soapRequest),
            ],
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("Error CURL: $error");
        }
        
        if ($httpCode !== 200) {
            throw new \Exception("HTTP Error: $httpCode");
        }
        
        return $response;
    }
    
    /**
     * Parsear respuesta de recepción
     */
    private function parsearRespuestaRecepcion($response) {
        $xml = simplexml_load_string($response);
        
        if ($xml === false) {
            return [
                'exito' => false,
                'estado' => 'ERROR',
                'mensaje' => 'Error al parsear respuesta del SRI',
            ];
        }
        
        // Registrar namespaces
        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        
        // Extraer estado
        $estado = (string) $xml->xpath('//estado')[0] ?? 'DESCONOCIDO';
        
        $resultado = [
            'exito' => $estado === 'RECIBIDA',
            'estado' => $estado,
            'comprobantes' => [],
        ];
        
        // Extraer mensajes de comprobantes
        $comprobantes = $xml->xpath('//comprobante');
        foreach ($comprobantes as $comprobante) {
            $comp = [
                'clave_acceso' => (string) $comprobante->claveAcceso,
                'mensajes' => [],
            ];
            
            $mensajes = $comprobante->xpath('.//mensaje');
            foreach ($mensajes as $mensaje) {
                $comp['mensajes'][] = [
                    'identificador' => (string) $mensaje->identificador,
                    'mensaje' => (string) $mensaje->mensaje,
                    'informacion_adicional' => (string) $mensaje->informacionAdicional,
                    'tipo' => (string) $mensaje->tipo,
                ];
            }
            
            $resultado['comprobantes'][] = $comp;
        }
        
        return $resultado;
    }
    
    /**
     * Parsear respuesta de autorización
     */
    private function parsearRespuestaAutorizacion($response) {
        $xml = simplexml_load_string($response);
        
        if ($xml === false) {
            return [
                'exito' => false,
                'estado' => 'ERROR',
                'mensaje' => 'Error al parsear respuesta del SRI',
            ];
        }
        
        $resultado = [
            'exito' => false,
            'autorizaciones' => [],
        ];
        
        // Buscar autorizaciones
        $autorizaciones = $xml->xpath('//autorizacion');
        
        foreach ($autorizaciones as $autorizacion) {
            $estado = (string) $autorizacion->estado;
            
            $auth = [
                'estado' => $estado,
                'numero_autorizacion' => (string) $autorizacion->numeroAutorizacion,
                'fecha_autorizacion' => (string) $autorizacion->fechaAutorizacion,
                'ambiente' => (string) $autorizacion->ambiente,
                'comprobante' => (string) $autorizacion->comprobante,
                'mensajes' => [],
            ];
            
            // Extraer mensajes
            $mensajes = $autorizacion->xpath('.//mensaje');
            foreach ($mensajes as $mensaje) {
                $auth['mensajes'][] = [
                    'identificador' => (string) $mensaje->identificador,
                    'mensaje' => (string) $mensaje->mensaje,
                    'informacion_adicional' => (string) $mensaje->informacionAdicional,
                    'tipo' => (string) $mensaje->tipo,
                ];
            }
            
            if ($estado === 'AUTORIZADO') {
                $resultado['exito'] = true;
            }
            
            $resultado['autorizaciones'][] = $auth;
        }
        
        return $resultado;
    }
    
    /**
     * Procesar comprobante completo (enviar y consultar)
     * 
     * @param string $xmlFirmado XML firmado
     * @param string $claveAcceso Clave de acceso
     * @param int $intentos Número máximo de intentos
     * @param int $espera Segundos entre intentos
     * @return array Resultado del proceso
     */
    public function procesarComprobante($xmlFirmado, $claveAcceso, $intentos = 3, $espera = 5) {
        // 1. Enviar comprobante
        $recepcion = $this->enviarComprobante($xmlFirmado);
        
        $this->registrarLog('recepcion', $claveAcceso, $recepcion);
        
        if (!$recepcion['exito']) {
            return [
                'exito' => false,
                'etapa' => 'recepcion',
                'resultado' => $recepcion,
            ];
        }
        
        // 2. Consultar autorización con reintentos
        for ($i = 0; $i < $intentos; $i++) {
            sleep($espera);
            
            $autorizacion = $this->consultarAutorizacion($claveAcceso);
            
            $this->registrarLog('autorizacion', $claveAcceso, $autorizacion);
            
            if ($autorizacion['exito']) {
                return [
                    'exito' => true,
                    'etapa' => 'autorizado',
                    'resultado' => $autorizacion,
                ];
            }
            
            // Si hay error definitivo, no reintentar
            if (!empty($autorizacion['autorizaciones'])) {
                $estado = $autorizacion['autorizaciones'][0]['estado'] ?? '';
                if ($estado === 'NO AUTORIZADO') {
                    return [
                        'exito' => false,
                        'etapa' => 'no_autorizado',
                        'resultado' => $autorizacion,
                    ];
                }
            }
        }
        
        return [
            'exito' => false,
            'etapa' => 'timeout',
            'mensaje' => 'No se pudo obtener autorización después de ' . $intentos . ' intentos',
        ];
    }
    
    /**
     * Registrar log de comunicación con SRI
     */
    private function registrarLog($tipo, $claveAcceso, $datos) {
        $directorio = $this->config['storage']['logs'];
        
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        $archivo = $directorio . date('Y-m-d') . '_' . $tipo . '.log';
        $linea = date('Y-m-d H:i:s') . ' | ' . $claveAcceso . ' | ' . json_encode($datos) . PHP_EOL;
        
        file_put_contents($archivo, $linea, FILE_APPEND);
    }
    
    /**
     * Verificar conectividad con SRI
     * 
     * @return array Estado de conexión
     */
    public function verificarConectividad() {
        $resultado = [
            'recepcion' => false,
            'autorizacion' => false,
        ];
        
        // Verificar servicio de recepción
        try {
            $ch = curl_init($this->urlRecepcion);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_NOBODY => true,
            ]);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $resultado['recepcion'] = $httpCode > 0;
        } catch (\Exception $e) {
            $resultado['recepcion'] = false;
        }
        
        // Verificar servicio de autorización
        try {
            $ch = curl_init($this->urlAutorizacion);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_NOBODY => true,
            ]);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $resultado['autorizacion'] = $httpCode > 0;
        } catch (\Exception $e) {
            $resultado['autorizacion'] = false;
        }
        
        return $resultado;
    }
}
