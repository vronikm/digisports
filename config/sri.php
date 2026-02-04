<?php
/**
 * DigiSports - Configuración del SRI Ecuador
 * Facturación Electrónica según normativa vigente
 * 
 * @package DigiSports\Config
 * @version 1.0.0
 */

return [
    // ============================================
    // CONFIGURACIÓN DEL AMBIENTE
    // ============================================
    
    // Ambiente: 1 = Pruebas, 2 = Producción
    'ambiente' => 1,
    
    // Tipo de emisión: 1 = Normal
    'tipo_emision' => 1,
    
    // ============================================
    // URLs DE WEB SERVICES DEL SRI
    // ============================================
    'webservices' => [
        // Ambiente de Pruebas
        'pruebas' => [
            'recepcion' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
            'autorizacion' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
        ],
        // Ambiente de Producción
        'produccion' => [
            'recepcion' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
            'autorizacion' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
        ],
    ],
    
    // ============================================
    // DATOS DEL EMISOR (EMPRESA)
    // ============================================
    'emisor' => [
        'ruc' => '0990000000001', // RUC de la empresa
        'razon_social' => 'DIGISPORTS S.A.',
        'nombre_comercial' => 'DigiSports',
        'direccion_matriz' => 'Av. Principal 123 y Calle Secundaria',
        'direccion_establecimiento' => 'Av. Principal 123 y Calle Secundaria',
        'codigo_establecimiento' => '001',
        'punto_emision' => '001',
        'contribuyente_especial' => '', // Número si aplica
        'obligado_contabilidad' => 'SI', // SI o NO
        'agente_retencion' => '', // Número de resolución si aplica
        'regimen_microempresas' => 'NO', // SI o NO
        'regimen_rimpe' => 'NO', // CONTRIBUYENTE RÉGIMEN RIMPE si aplica
    ],
    
    // ============================================
    // CONFIGURACIÓN DE FIRMA ELECTRÓNICA
    // ============================================
    'firma' => [
        'archivo' => BASE_PATH . '/storage/certificados/firma.p12',
        'clave' => '', // Contraseña del certificado (obtener de variable de entorno)
    ],
    
    // ============================================
    // TIPOS DE COMPROBANTES
    // ============================================
    'tipos_comprobante' => [
        '01' => 'FACTURA',
        '04' => 'NOTA DE CRÉDITO',
        '05' => 'NOTA DE DÉBITO',
        '06' => 'GUÍA DE REMISIÓN',
        '07' => 'COMPROBANTE DE RETENCIÓN',
    ],
    
    // ============================================
    // TIPOS DE IDENTIFICACIÓN
    // ============================================
    'tipos_identificacion' => [
        '04' => 'RUC',
        '05' => 'CÉDULA',
        '06' => 'PASAPORTE',
        '07' => 'CONSUMIDOR FINAL',
        '08' => 'IDENTIFICACIÓN EXTERIOR',
    ],
    
    // ============================================
    // FORMAS DE PAGO (Tabla 24 SRI)
    // ============================================
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
    
    // ============================================
    // CÓDIGOS DE IMPUESTOS
    // ============================================
    'impuestos' => [
        'IVA' => [
            'codigo' => '2',
            'tarifas' => [
                '0' => ['codigo' => '0', 'porcentaje' => 0],
                '12' => ['codigo' => '2', 'porcentaje' => 12],
                '14' => ['codigo' => '3', 'porcentaje' => 14],
                '15' => ['codigo' => '4', 'porcentaje' => 15],
                'NO_OBJETO' => ['codigo' => '6', 'porcentaje' => 0],
                'EXENTO' => ['codigo' => '7', 'porcentaje' => 0],
            ],
        ],
        'ICE' => [
            'codigo' => '3',
        ],
        'IRBPNR' => [
            'codigo' => '5',
        ],
    ],
    
    // ============================================
    // CONFIGURACIÓN DE ALMACENAMIENTO
    // ============================================
    'storage' => [
        'xml_generados' => BASE_PATH . '/storage/sri/xml/generados/',
        'xml_firmados' => BASE_PATH . '/storage/sri/xml/firmados/',
        'xml_autorizados' => BASE_PATH . '/storage/sri/xml/autorizados/',
        'ride' => BASE_PATH . '/storage/sri/ride/',
        'logs' => BASE_PATH . '/storage/sri/logs/',
    ],
    
    // ============================================
    // CONFIGURACIÓN DE SECUENCIALES
    // ============================================
    'secuencial' => [
        'longitud' => 9,
        'padding' => '0',
    ],
    
    // ============================================
    // VERSIÓN DEL ESQUEMA XML
    // ============================================
    'version_xml' => '1.0.0',
    'version_factura' => '2.1.0',
];
