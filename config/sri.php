<?php
/**
 * DigiSports - Configuración del SRI Ecuador
 * Facturación Electrónica según normativa vigente
 *
 * Todos los valores sensibles y específicos del emisor
 * se leen desde variables de entorno (.env) para soportar
 * múltiples tenants / ambientes sin cambiar este archivo.
 *
 * @package DigiSports\Config
 * @version 1.1.0
 */

return [

    // ── Ambiente ─────────────────────────────────────────────────────────────
    // 1 = Pruebas (celcer.sri.gob.ec) | 2 = Producción (cel.sri.gob.ec)
    'ambiente'      => (int) env('SRI_AMBIENTE', 1),

    // Tipo de emisión: 1 = Normal
    'tipo_emision'  => 1,

    // ── URLs de Web Services del SRI ─────────────────────────────────────────
    'webservices' => [
        'pruebas' => [
            'recepcion'    => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
            'autorizacion' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
        ],
        'produccion' => [
            'recepcion'    => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
            'autorizacion' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
        ],
    ],

    // ── Datos del Emisor ──────────────────────────────────────────────────────
    // NOTA: en producción multi-tenant estos valores se sobrescriben
    // con los de la tabla facturacion_configuracion del tenant activo.
    'emisor' => [
        'ruc'                       => env('SRI_RUC',                    '0990000000001'),
        'razon_social'              => env('SRI_RAZON_SOCIAL',           'EMPRESA S.A.'),
        'nombre_comercial'          => env('SRI_NOMBRE_COMERCIAL',       'Nombre Comercial'),
        'direccion_matriz'          => env('SRI_DIRECCION_MATRIZ',       'Dirección Matriz'),
        'direccion_establecimiento' => env('SRI_DIRECCION_ESTABLECIMIENTO', 'Dirección Establecimiento'),
        'codigo_establecimiento'    => env('SRI_CODIGO_ESTABLECIMIENTO', '001'),
        'punto_emision'             => env('SRI_PUNTO_EMISION',          '001'),
        'obligado_contabilidad'     => env('SRI_OBLIGADO_CONTABILIDAD',  'SI'),   // SI | NO
        'contribuyente_especial'    => env('SRI_CONTRIBUYENTE_ESPECIAL', ''),
        'agente_retencion'          => env('SRI_AGENTE_RETENCION',       ''),
        'regimen_microempresas'     => env('SRI_REGIMEN_MICROEMPRESAS',  'NO'),   // SI | NO
        'regimen_rimpe'             => env('SRI_REGIMEN_RIMPE',          'NO'),   // SI | NO
    ],

    // ── Firma Electrónica ─────────────────────────────────────────────────────
    'firma' => [
        // Ruta al archivo .p12 del certificado de firma electrónica
        'archivo' => BASE_PATH . '/storage/certificados/firma.p12',
        // Contraseña del .p12 — SIEMPRE desde .env, nunca hardcodeada
        'clave'   => env('SRI_FIRMA_CLAVE', ''),
    ],

    // ── Tipos de Comprobantes (Tabla 4 SRI) ──────────────────────────────────
    'tipos_comprobante' => [
        '01' => 'FACTURA',
        '04' => 'NOTA DE CRÉDITO',
        '05' => 'NOTA DE DÉBITO',
        '06' => 'GUÍA DE REMISIÓN',
        '07' => 'COMPROBANTE DE RETENCIÓN',
    ],

    // ── Tipos de Identificación (Tabla 6 SRI) ────────────────────────────────
    'tipos_identificacion' => [
        '04' => 'RUC',
        '05' => 'CÉDULA',
        '06' => 'PASAPORTE',
        '07' => 'CONSUMIDOR FINAL',
        '08' => 'IDENTIFICACIÓN EXTERIOR',
    ],

    // ── Formas de Pago (Tabla 24 SRI) ────────────────────────────────────────
    'formas_pago' => [
        '01' => 'SIN UTILIZACIÓN DEL SISTEMA FINANCIERO',
        '15' => 'COMPENSACIÓN DE DEUDAS',
        '16' => 'TARJETA DE DÉBITO',
        '17' => 'DINERO ELECTRÓNICO',
        '18' => 'TARJETA PREPAGO',
        '19' => 'TARJETA DE CRÉDITO',
        '20' => 'OTROS CON UTILIZACIÓN DEL SISTEMA FINANCIERO',
        '21' => 'ENDOSO DE TÍTULOS',
    ],

    // ── Códigos de Impuestos ──────────────────────────────────────────────────
    // Código IVA = 2 | Código porcentaje: 0=0%, 2=12%, 3=14%, 4=15%, 6=NO_OBJETO, 7=EXENTO
    'impuestos' => [
        'IVA' => [
            'codigo'  => '2',
            'tarifas' => [
                '0'         => ['codigo' => '0', 'porcentaje' => 0],
                '12'        => ['codigo' => '2', 'porcentaje' => 12],
                '14'        => ['codigo' => '3', 'porcentaje' => 14],
                '15'        => ['codigo' => '4', 'porcentaje' => 15],
                'NO_OBJETO' => ['codigo' => '6', 'porcentaje' => 0],
                'EXENTO'    => ['codigo' => '7', 'porcentaje' => 0],
            ],
        ],
        'ICE'    => ['codigo' => '3'],
        'IRBPNR' => ['codigo' => '5'],
    ],

    // ── Almacenamiento ────────────────────────────────────────────────────────
    'storage' => [
        'xml_generados'  => BASE_PATH . '/storage/sri/xml/generados/',
        'xml_firmados'   => BASE_PATH . '/storage/sri/xml/firmados/',
        'xml_autorizados'=> BASE_PATH . '/storage/sri/xml/autorizados/',
        'ride'           => BASE_PATH . '/storage/sri/ride/',
        'logs'           => BASE_PATH . '/storage/sri/logs/',
        'certificados'   => BASE_PATH . '/storage/certificados/',
    ],

    // ── Secuenciales ─────────────────────────────────────────────────────────
    'secuencial' => [
        'longitud' => 9,
        'padding'  => '0',
    ],

    // ── Versión del esquema XML ───────────────────────────────────────────────
    'version_xml'     => '1.0.0',
    'version_factura' => '2.1.0',
];
