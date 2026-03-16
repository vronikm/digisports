<?php
/**
 * DigiSports - Servicio de Firma Electrónica XAdES-BES
 * Firma de comprobantes electrónicos para el SRI Ecuador
 *
 * Implementa XAdES-BES con las siguientes correcciones respecto a la versión anterior:
 *   1. DigestValue de SignedProperties calculado correctamente (SHA1 canónico)
 *   2. Modulus y Exponent del RSA extraídos del certificado real
 *   3. SignedInfo canonicalizado (C14N inclusivo) antes de firmar
 *   4. Digest del certificado calculado sobre el DER binario (no sobre PEM)
 *
 * @package DigiSports\Services\SRI
 * @version 1.1.0
 */

namespace App\Services\SRI;

class FirmaElectronicaService {

    private $config;
    private $certificado;   // resource X.509
    private $clavePrivada;  // resource private key

    public function __construct() {
        $this->config = require BASE_PATH . '/config/sri.php';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CARGAR CERTIFICADO .p12
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Carga el certificado PKCS#12 (.p12) desde disco.
     *
     * @param string|null $rutaCertificado Ruta al .p12 (usa config/sri.php si null)
     * @param string|null $clave           Contraseña del .p12 (usa config/sri.php si null)
     * @throws \Exception
     */
    public function cargarCertificado($rutaCertificado = null, $clave = null) {
        $rutaCertificado = $rutaCertificado ?? $this->config['firma']['archivo'];
        $clave           = $clave           ?? $this->config['firma']['clave'];

        if (!\file_exists($rutaCertificado)) {
            throw new \Exception("Certificado no encontrado: {$rutaCertificado}");
        }

        $p12      = \file_get_contents($rutaCertificado);
        $certInfo = [];

        if (!\openssl_pkcs12_read($p12, $certInfo, $clave)) {
            throw new \Exception("No se pudo leer el certificado .p12. Verifique la contraseña.");
        }

        $this->certificado  = $certInfo['cert'];
        $this->clavePrivada = $certInfo['pkey'];

        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INFORMACIÓN DEL CERTIFICADO
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Devuelve información básica del certificado cargado.
     *
     * @return array
     * @throws \Exception
     */
    public function obtenerInfoCertificado() {
        if (!$this->certificado) {
            throw new \Exception("Certificado no cargado. Llame a cargarCertificado() primero.");
        }

        $info = \openssl_x509_parse($this->certificado);

        return [
            'titular'        => $info['subject']['CN']    ?? 'N/A',
            'emisor'         => $info['issuer']['CN']     ?? 'N/A',
            'serial'         => $info['serialNumber']     ?? 'N/A',
            'valido_desde'   => \date('Y-m-d H:i:s', $info['validFrom_time_t']),
            'valido_hasta'   => \date('Y-m-d H:i:s', $info['validTo_time_t']),
            'vigente'        => \time() < $info['validTo_time_t'],
            'dias_restantes' => (int) \floor(($info['validTo_time_t'] - \time()) / 86400),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FIRMA XAdES-BES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Firma el XML del comprobante con XAdES-BES (enveloped signature).
     *
     * El documento debe tener id="comprobante" en el elemento raíz.
     * La firma se agrega como hijo del elemento raíz.
     *
     * Algoritmos usados (según ficha técnica SRI):
     *   - Canonicalización : C14N 1.0 inclusivo (http://www.w3.org/TR/2001/REC-xml-c14n-20010315)
     *   - Firma            : RSA-SHA1
     *   - Digest           : SHA1
     *
     * @param  string $xml  XML sin firmar (ya formateado por FacturaElectronicaService)
     * @return string XML firmado
     * @throws \Exception
     */
    public function firmarXML(string $xml): string {
        if (!$this->certificado || !$this->clavePrivada) {
            $this->cargarCertificado();
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadXML($xml);

        // ── IDs únicos ───────────────────────────────────────────────────────
        $uid        = $this->uid();
        $sigId      = "Signature-{$uid}";
        $sigPropId  = "SignedProperties-{$sigId}";
        $keyInfoId  = "KeyInfo-{$sigId}";
        $refDocId   = "Reference-{$this->uid()}";

        // ── Datos del certificado ────────────────────────────────────────────
        [$certB64, $certDigest, $issuerName, $serialNumber] = $this->extraerDatosCert();
        [$modulus, $exponent] = $this->extraerClavePublica();

        // ── 1. DigestValue del documento (C14N del elemento raíz) ────────────
        $canonDoc  = $dom->documentElement->C14N(false, false);
        $digestDoc = \base64_encode(\hash('sha1', $canonDoc, true));

        // ── 2. Construir SignedProperties (con todos sus datos fijos) ─────────
        //    Se construye con namespaces explícitos para que el C14N standalone
        //    produzca el mismo resultado que en el contexto del documento completo.
        $signingTime      = \date('Y-m-d\TH:i:sP');
        $signedPropsXML   = $this->buildSignedProperties(
            $sigPropId, $refDocId,
            $signingTime, $certDigest, $issuerName, $serialNumber
        );

        // ── 3. DigestValue de SignedProperties (C14N del elemento) ───────────
        $spDom = new \DOMDocument();
        $spDom->loadXML($signedPropsXML);
        $canonSP  = $spDom->documentElement->C14N(false, false);
        $digestSP = \base64_encode(\hash('sha1', $canonSP, true));

        // ── 4. Construir SignedInfo con ambos DigestValues ya calculados ──────
        $signedInfoXML = $this->buildSignedInfo($refDocId, $digestDoc, $sigPropId, $digestSP);

        // ── 5. Canonicalizar SignedInfo y firmar con RSA-SHA1 ─────────────────
        $siDom = new \DOMDocument();
        $siDom->loadXML($signedInfoXML);
        $canonSI    = $siDom->documentElement->C14N(false, false);
        $sigRaw     = '';
        \openssl_sign($canonSI, $sigRaw, $this->clavePrivada, OPENSSL_ALGO_SHA1);
        $sigValue = \base64_encode($sigRaw);

        // ── 6. Ensamblar la estructura Signature completa ─────────────────────
        $signatureXML = $this->buildSignature(
            $sigId, $signedInfoXML, $sigValue,
            $keyInfoId, $certB64, $modulus, $exponent,
            $sigId, $signedPropsXML
        );

        // ── 7. Agregar la firma al documento ──────────────────────────────────
        $fragment = $dom->createDocumentFragment();
        $fragment->appendXML($signatureXML);
        $dom->documentElement->appendChild($fragment);

        return $dom->saveXML();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VERIFICAR FIRMA (verificación estructural básica)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Verifica que el XML contiene los elementos mínimos de firma XAdES.
     * No realiza verificación criptográfica completa.
     *
     * @param  string $xmlFirmado
     * @return bool
     */
    public function verificarFirma(string $xmlFirmado): bool {
        $dom = new \DOMDocument();
        @$dom->loadXML($xmlFirmado);

        $ds  = 'http://www.w3.org/2000/09/xmldsig#';
        $sig = $dom->getElementsByTagNameNS($ds, 'Signature');
        if ($sig->length === 0) return false;

        $sigVal  = $dom->getElementsByTagNameNS($ds, 'SignatureValue');
        $x509    = $dom->getElementsByTagNameNS($ds, 'X509Certificate');
        $sigInfo = $dom->getElementsByTagNameNS($ds, 'SignedInfo');

        return $sigVal->length > 0 && $x509->length > 0 && $sigInfo->length > 0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BUILDERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    private function buildSignedProperties(
        string $sigPropId, string $refDocId,
        string $signingTime, string $certDigest,
        string $issuerName, string $serialNumber
    ): string {
        // Namespaces explícitos en SignedProperties para que el C14N standalone
        // incluya todos los prefijos necesarios sin diferir del contexto de documento.
        return '<etsi:SignedProperties'
            . ' xmlns:etsi="http://uri.etsi.org/01903/v1.3.2#"'
            . ' xmlns:ds="http://www.w3.org/2000/09/xmldsig#"'
            . ' Id="' . $sigPropId . '">'
                . '<etsi:SignedSignatureProperties>'
                    . '<etsi:SigningTime>' . $signingTime . '</etsi:SigningTime>'
                    . '<etsi:SigningCertificate>'
                        . '<etsi:Cert>'
                            . '<etsi:CertDigest>'
                                . '<ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>'
                                . '<ds:DigestValue>' . $certDigest . '</ds:DigestValue>'
                            . '</etsi:CertDigest>'
                            . '<etsi:IssuerSerial>'
                                . '<ds:X509IssuerName>' . \htmlspecialchars($issuerName, ENT_XML1) . '</ds:X509IssuerName>'
                                . '<ds:X509SerialNumber>' . $serialNumber . '</ds:X509SerialNumber>'
                            . '</etsi:IssuerSerial>'
                        . '</etsi:Cert>'
                    . '</etsi:SigningCertificate>'
                . '</etsi:SignedSignatureProperties>'
                . '<etsi:SignedDataObjectProperties>'
                    . '<etsi:DataObjectFormat ObjectReference="#' . $refDocId . '">'
                        . '<etsi:Description>contenido comprobante</etsi:Description>'
                        . '<etsi:MimeType>text/xml</etsi:MimeType>'
                    . '</etsi:DataObjectFormat>'
                . '</etsi:SignedDataObjectProperties>'
            . '</etsi:SignedProperties>';
    }

    private function buildSignedInfo(
        string $refDocId, string $digestDoc,
        string $sigPropId, string $digestSP
    ): string {
        return '<ds:SignedInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">'
            . '<ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>'
            . '<ds:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>'
            // Referencia al documento (enveloped)
            . '<ds:Reference Id="' . $refDocId . '" URI="#comprobante">'
                . '<ds:Transforms>'
                    . '<ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>'
                . '</ds:Transforms>'
                . '<ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>'
                . '<ds:DigestValue>' . $digestDoc . '</ds:DigestValue>'
            . '</ds:Reference>'
            // Referencia a SignedProperties
            . '<ds:Reference URI="#' . $sigPropId . '" Type="http://uri.etsi.org/01903#SignedProperties">'
                . '<ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>'
                . '<ds:DigestValue>' . $digestSP . '</ds:DigestValue>'
            . '</ds:Reference>'
        . '</ds:SignedInfo>';
    }

    private function buildSignature(
        string $sigId, string $signedInfoXML, string $sigValue,
        string $keyInfoId, string $certB64, string $modulus, string $exponent,
        string $qualifyingTarget, string $signedPropsXML
    ): string {
        return '<ds:Signature'
                . ' xmlns:ds="http://www.w3.org/2000/09/xmldsig#"'
                . ' xmlns:etsi="http://uri.etsi.org/01903/v1.3.2#"'
                . ' Id="' . $sigId . '">'
            . $signedInfoXML
            . '<ds:SignatureValue Id="SignatureValue-' . $sigId . '">' . $sigValue . '</ds:SignatureValue>'
            . '<ds:KeyInfo Id="' . $keyInfoId . '">'
                . '<ds:X509Data>'
                    . '<ds:X509Certificate>' . $certB64 . '</ds:X509Certificate>'
                . '</ds:X509Data>'
                . '<ds:KeyValue>'
                    . '<ds:RSAKeyValue>'
                        . '<ds:Modulus>' . $modulus . '</ds:Modulus>'
                        . '<ds:Exponent>' . $exponent . '</ds:Exponent>'
                    . '</ds:RSAKeyValue>'
                . '</ds:KeyValue>'
            . '</ds:KeyInfo>'
            . '<ds:Object Id="XadesObjectId-' . $sigId . '">'
                . '<etsi:QualifyingProperties Target="#' . $qualifyingTarget . '">'
                    . $signedPropsXML
                . '</etsi:QualifyingProperties>'
            . '</ds:Object>'
        . '</ds:Signature>';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Extrae los datos necesarios del certificado X.509.
     *
     * @return array [certB64, certDER, certDigest(base64-sha1-DER), issuerName, serialNumber]
     * @throws \Exception
     */
    private function extraerDatosCert(): array {
        $certPEM = '';
        \openssl_x509_export($this->certificado, $certPEM);

        // Cert en Base64 sin cabeceras PEM (= DER en base64) → va en <X509Certificate>
        $certB64 = \str_replace(
            ['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\r", "\n"],
            '',
            $certPEM
        );

        // DER binario para calcular el digest del certificado
        $certDER    = \base64_decode($certB64);
        $certDigest = \base64_encode(\hash('sha1', $certDER, true));

        // Datos del emisor y número de serie
        $parsed     = \openssl_x509_parse($this->certificado);
        $issuerName = $this->formatearEmisor($parsed['issuer'] ?? []);
        $serial     = $parsed['serialNumber'] ?? '0';

        // El SRI espera el serial en decimal; si viene en hex (0x...) convertir
        if (\strpos($serial, '0x') === 0 || \strpos($serial, '0X') === 0) {
            $serial = \base_convert(\ltrim($serial, '0x'), 16, 10);
        }

        return [$certB64, $certDigest, $issuerName, $serial];
    }

    /**
     * Extrae Modulus y Exponent de la clave pública RSA del certificado.
     *
     * @return array [modulus(base64), exponent(base64)]
     * @throws \Exception
     */
    private function extraerClavePublica(): array {
        $pubKeyRes  = \openssl_pkey_get_public($this->certificado);
        $keyDetails = \openssl_pkey_get_details($pubKeyRes);

        if (empty($keyDetails['rsa'])) {
            throw new \Exception("El certificado no contiene una clave RSA.");
        }

        return [
            \base64_encode($keyDetails['rsa']['n']),
            \base64_encode($keyDetails['rsa']['e']),
        ];
    }

    /**
     * Formatea el DN del emisor del certificado al formato esperado por el SRI.
     * Acepta tanto array ['CN'=>...] como string DN compacto (ambas formas que
     * puede devolver openssl_x509_parse() según versión de PHP).
     * Orden: CN, O, OU, L, ST, C
     */
    private function formatearEmisor($issuer): string {
        // Si ya es string (DN compacto), devolverlo directamente
        if (\is_string($issuer)) {
            return $issuer;
        }

        $partes = [];
        foreach (['CN', 'O', 'OU', 'L', 'ST', 'C'] as $attr) {
            if (!empty($issuer[$attr])) {
                $partes[] = $attr . '=' . $issuer[$attr];
            }
        }
        return \implode(',', $partes);
    }

    /**
     * Genera un ID corto único alfanumérico.
     */
    private function uid(): string {
        return \bin2hex(\random_bytes(4));
    }
}
