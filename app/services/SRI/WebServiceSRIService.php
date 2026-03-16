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

        // Intentar encontrar un CA bundle válido en el sistema
        $caBundle   = null;
        $candidates = [
            // PHP php.ini curl.cainfo si está configurado
            ini_get('curl.cainfo'),
            // CA bundle del composer (phpMyAdmin u otras apps)
            'C:/wamp64/apps/phpmyadmin5.2.3/vendor/composer/ca-bundle/res/cacert.pem',
            // Rutas estándar Linux/Mac por si se despliega fuera de WAMP
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
        ];
        foreach ($candidates as $candidate) {
            if (!empty($candidate) && file_exists($candidate)) {
                $caBundle = $candidate;
                break;
            }
        }

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $soapRequest,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => $caBundle !== null,
            CURLOPT_SSL_VERIFYHOST => $caBundle !== null ? 2 : 0,
            CURLOPT_CAINFO         => $caBundle ?? '',
            CURLOPT_HTTPHEADER     => [
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
    public function procesarComprobante($xmlFirmado, $claveAcceso, $intentos = 5, $espera = 5) {
        // 1. Enviar comprobante
        $recepcion = $this->enviarComprobante($xmlFirmado);

        $this->registrarLog('recepcion', $claveAcceso, $recepcion);

        if (!$recepcion['exito']) {
            // Si el SRI rechaza porque la clave ya está en procesamiento,
            // no abortar: saltar directamente al polling de autorización
            $enProcesamiento = false;
            foreach ($recepcion['comprobantes'] ?? [] as $comp) {
                foreach ($comp['mensajes'] ?? [] as $msg) {
                    if (stripos($msg['mensaje'] ?? '', 'PROCESAMIENTO') !== false) {
                        $enProcesamiento = true;
                        break 2;
                    }
                }
            }

            if (!$enProcesamiento) {
                return [
                    'exito'     => false,
                    'etapa'     => 'recepcion',
                    'resultado' => $recepcion,
                ];
            }
            // Continuar al polling de autorización
        }

        // 2. Consultar autorización con reintentos
        $ultimaAutorizacion = null;
        $ultimoEstado       = null;

        for ($i = 0; $i < $intentos; $i++) {
            sleep($espera);

            $autorizacion = $this->consultarAutorizacion($claveAcceso);
            $this->registrarLog('autorizacion', $claveAcceso, $autorizacion);

            if ($autorizacion['exito']) {
                return [
                    'exito'     => true,
                    'etapa'     => 'autorizado',
                    'resultado' => $autorizacion,
                ];
            }

            $ultimaAutorizacion = $autorizacion;
            if (!empty($autorizacion['autorizaciones'])) {
                $ultimoEstado = $autorizacion['autorizaciones'][0]['estado'] ?? '';
                // Error definitivo — no reintentar
                if ($ultimoEstado === 'NO AUTORIZADO') {
                    return [
                        'exito'     => false,
                        'etapa'     => 'no_autorizado',
                        'resultado' => $autorizacion,
                    ];
                }
            }

            // Si $ultimoEstado ya indica procesamiento, marcar para detectarlo tras el loop
            if ($ultimoEstado === null && !empty($autorizacion['mensaje'])
                && stripos($autorizacion['mensaje'], 'PROCESAMIENTO') !== false) {
                $ultimoEstado = 'EN PROCESAMIENTO';
            }
        }

        // Timeout: si el último estado es "EN PROCESAMIENTO", el SRI recibió el doc
        // pero aún no terminó de procesar → guardar como ENVIADA, no como ERROR
        if ($ultimoEstado !== null && stripos($ultimoEstado, 'PROCESAMIENTO') !== false) {
            return [
                'exito'     => false,
                'etapa'     => 'en_procesamiento',
                'mensaje'   => 'La factura fue recibida por el SRI y está en procesamiento. Consulte el estado en unos minutos.',
                'resultado' => $ultimaAutorizacion,
            ];
        }

        return [
            'exito'   => false,
            'etapa'   => 'timeout',
            'mensaje' => 'No se pudo obtener autorización del SRI después de ' . $intentos . ' intentos.',
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

        foreach (['recepcion' => $this->urlRecepcion, 'autorizacion' => $this->urlAutorizacion] as $key => $url) {
            try {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    // GET en lugar de HEAD: los WSDL del SRI no responden a HEAD
                    CURLOPT_HTTPGET        => true,
                    // Solo verificar que el host responde; no validar SSL en test de conectividad
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                ]);
                $body    = curl_exec($ch);
                $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);

                if ($curlErr) {
                    error_log("[WebServiceSRI] conectividad $key error: $curlErr");
                }
                // SRI devuelve 200 con el WSDL o 500 si falta el header SOAPAction
                // Cualquier respuesta HTTP (incluso 4xx/5xx) indica que el host está alcanzable
                $resultado[$key] = $code > 0;
            } catch (\Exception $e) {
                error_log("[WebServiceSRI] conectividad $key excepción: " . $e->getMessage());
                $resultado[$key] = false;
            }
        }

        return $resultado;
    }
}
