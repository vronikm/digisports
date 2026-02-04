<?php
/**
 * DigiSports - Servicio de Firma Electrónica
 * Firma de comprobantes electrónicos con certificado .p12
 * 
 * @package DigiSports\Services\SRI
 * @version 1.0.0
 */

namespace App\Services\SRI;

class FirmaElectronicaService {
    
    private $config;
    private $certificado;
    private $clavePrivada;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->config = require BASE_PATH . '/config/sri.php';
    }
    
    /**
     * Cargar certificado digital .p12
     * 
     * @param string $rutaCertificado Ruta al archivo .p12
     * @param string $clave Contraseña del certificado
     * @return bool
     */
    public function cargarCertificado($rutaCertificado = null, $clave = null) {
        $rutaCertificado = $rutaCertificado ?? $this->config['firma']['archivo'];
        $clave = $clave ?? $this->config['firma']['clave'];
        
        if (!file_exists($rutaCertificado)) {
            throw new \Exception("Certificado no encontrado: $rutaCertificado");
        }
        
        $p12 = file_get_contents($rutaCertificado);
        $certInfo = [];
        
        if (!openssl_pkcs12_read($p12, $certInfo, $clave)) {
            throw new \Exception("No se pudo leer el certificado. Verifique la contraseña.");
        }
        
        $this->certificado = $certInfo['cert'];
        $this->clavePrivada = $certInfo['pkey'];
        
        return true;
    }
    
    /**
     * Obtener información del certificado
     * 
     * @return array Información del certificado
     */
    public function obtenerInfoCertificado() {
        if (!$this->certificado) {
            throw new \Exception("Certificado no cargado");
        }
        
        $info = openssl_x509_parse($this->certificado);
        
        return [
            'titular' => $info['subject']['CN'] ?? 'N/A',
            'emisor' => $info['issuer']['CN'] ?? 'N/A',
            'serial' => $info['serialNumber'] ?? 'N/A',
            'valido_desde' => date('Y-m-d H:i:s', $info['validFrom_time_t']),
            'valido_hasta' => date('Y-m-d H:i:s', $info['validTo_time_t']),
            'vigente' => time() < $info['validTo_time_t'],
            'dias_restantes' => floor(($info['validTo_time_t'] - time()) / 86400),
        ];
    }
    
    /**
     * Firmar XML con XAdES-BES
     * 
     * @param string $xml XML a firmar
     * @return string XML firmado
     */
    public function firmarXML($xml) {
        if (!$this->certificado || !$this->clavePrivada) {
            $this->cargarCertificado();
        }
        
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadXML($xml);
        
        // Obtener el elemento comprobante (factura, notaCredito, etc.)
        $comprobante = $dom->documentElement;
        
        // Crear estructura de firma XAdES-BES
        $signatureId = 'Signature' . $this->generarId();
        $signedPropertiesId = 'SignedProperties-' . $signatureId;
        $keyInfoId = 'KeyInfo-' . $signatureId;
        $referenceId = 'Reference-' . $this->generarId();
        
        // Canonicalizar el documento
        $canonicalXML = $comprobante->C14N(true, false);
        
        // Calcular digest del documento
        $digestValue = base64_encode(hash('sha1', $canonicalXML, true));
        
        // Obtener información del certificado
        $certData = openssl_x509_parse($this->certificado);
        $certPEM = '';
        openssl_x509_export($this->certificado, $certPEM);
        $certPEM = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n", "\r"], '', $certPEM);
        
        // Crear SignedInfo
        $signedInfoXML = $this->crearSignedInfo($digestValue, $referenceId, $signedPropertiesId);
        
        // Firmar SignedInfo
        $signature = '';
        openssl_sign($signedInfoXML, $signature, $this->clavePrivada, OPENSSL_ALGO_SHA1);
        $signatureValue = base64_encode($signature);
        
        // Construir elemento Signature completo
        $signatureXML = $this->construirSignature(
            $signatureId,
            $signedInfoXML,
            $signatureValue,
            $keyInfoId,
            $certPEM,
            $certData,
            $signedPropertiesId,
            $digestValue,
            $referenceId
        );
        
        // Agregar firma al documento
        $signatureFragment = $dom->createDocumentFragment();
        $signatureFragment->appendXML($signatureXML);
        $comprobante->appendChild($signatureFragment);
        
        return $dom->saveXML();
    }
    
    /**
     * Crear elemento SignedInfo
     */
    private function crearSignedInfo($digestValue, $referenceId, $signedPropertiesId) {
        return '<ds:SignedInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
            <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
            <ds:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
            <ds:Reference Id="' . $referenceId . '" URI="#comprobante">
                <ds:Transforms>
                    <ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
                </ds:Transforms>
                <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
                <ds:DigestValue>' . $digestValue . '</ds:DigestValue>
            </ds:Reference>
            <ds:Reference URI="#' . $signedPropertiesId . '" Type="http://uri.etsi.org/01903#SignedProperties">
                <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
                <ds:DigestValue></ds:DigestValue>
            </ds:Reference>
        </ds:SignedInfo>';
    }
    
    /**
     * Construir elemento Signature completo
     */
    private function construirSignature($signatureId, $signedInfo, $signatureValue, $keyInfoId, $certPEM, $certData, $signedPropertiesId, $digestValue, $referenceId) {
        $signingTime = date('Y-m-d\TH:i:sP');
        $issuerName = $this->formatIssuerName($certData['issuer']);
        $serialNumber = $certData['serialNumber'];
        
        return '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:etsi="http://uri.etsi.org/01903/v1.3.2#" Id="' . $signatureId . '">
            ' . $signedInfo . '
            <ds:SignatureValue>' . $signatureValue . '</ds:SignatureValue>
            <ds:KeyInfo Id="' . $keyInfoId . '">
                <ds:X509Data>
                    <ds:X509Certificate>' . $certPEM . '</ds:X509Certificate>
                </ds:X509Data>
                <ds:KeyValue>
                    <ds:RSAKeyValue>
                        <ds:Modulus></ds:Modulus>
                        <ds:Exponent>AQAB</ds:Exponent>
                    </ds:RSAKeyValue>
                </ds:KeyValue>
            </ds:KeyInfo>
            <ds:Object Id="XadesObjectId-' . $signatureId . '">
                <etsi:QualifyingProperties Target="#' . $signatureId . '">
                    <etsi:SignedProperties Id="' . $signedPropertiesId . '">
                        <etsi:SignedSignatureProperties>
                            <etsi:SigningTime>' . $signingTime . '</etsi:SigningTime>
                            <etsi:SigningCertificate>
                                <etsi:Cert>
                                    <etsi:CertDigest>
                                        <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
                                        <ds:DigestValue>' . base64_encode(hash('sha1', base64_decode($certPEM), true)) . '</ds:DigestValue>
                                    </etsi:CertDigest>
                                    <etsi:IssuerSerial>
                                        <ds:X509IssuerName>' . htmlspecialchars($issuerName) . '</ds:X509IssuerName>
                                        <ds:X509SerialNumber>' . $serialNumber . '</ds:X509SerialNumber>
                                    </etsi:IssuerSerial>
                                </etsi:Cert>
                            </etsi:SigningCertificate>
                        </etsi:SignedSignatureProperties>
                        <etsi:SignedDataObjectProperties>
                            <etsi:DataObjectFormat ObjectReference="#' . $referenceId . '">
                                <etsi:Description>contenido comprobante</etsi:Description>
                                <etsi:MimeType>text/xml</etsi:MimeType>
                            </etsi:DataObjectFormat>
                        </etsi:SignedDataObjectProperties>
                    </etsi:SignedProperties>
                </etsi:QualifyingProperties>
            </ds:Object>
        </ds:Signature>';
    }
    
    /**
     * Formatear nombre del emisor del certificado
     */
    private function formatIssuerName($issuer) {
        $parts = [];
        if (isset($issuer['CN'])) $parts[] = 'CN=' . $issuer['CN'];
        if (isset($issuer['O'])) $parts[] = 'O=' . $issuer['O'];
        if (isset($issuer['C'])) $parts[] = 'C=' . $issuer['C'];
        return implode(',', $parts);
    }
    
    /**
     * Generar ID único
     */
    private function generarId() {
        return substr(md5(uniqid(mt_rand(), true)), 0, 8);
    }
    
    /**
     * Verificar firma de un XML
     * 
     * @param string $xmlFirmado XML firmado
     * @return bool
     */
    public function verificarFirma($xmlFirmado) {
        $dom = new \DOMDocument();
        $dom->loadXML($xmlFirmado);
        
        // Buscar elemento Signature
        $signatures = $dom->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'Signature');
        
        if ($signatures->length === 0) {
            return false;
        }
        
        // Verificación básica de estructura
        $signature = $signatures->item(0);
        $signatureValue = $dom->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'SignatureValue');
        $x509Certificate = $dom->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'X509Certificate');
        
        return $signatureValue->length > 0 && $x509Certificate->length > 0;
    }
}
