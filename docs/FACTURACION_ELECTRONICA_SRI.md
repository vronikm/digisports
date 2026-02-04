# DigiSports - Sistema de Facturación Electrónica SRI Ecuador

## 📋 Descripción General

Sistema completo de facturación electrónica para Ecuador, cumpliendo con las especificaciones técnicas del Servicio de Rentas Internas (SRI). Permite generar, firmar digitalmente, enviar y autorizar comprobantes electrónicos.

## 🏗️ Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────────────┐
│                    FACTURACIÓN ELECTRÓNICA                          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────┐    ┌──────────────┐    ┌─────────────────┐        │
│  │  Controlador│    │   Servicios  │    │    Web Service  │        │
│  │  (entrada)  │───▶│   (proceso)  │───▶│      SRI        │        │
│  └─────────────┘    └──────────────┘    └─────────────────┘        │
│         │                  │                     │                  │
│         │           ┌──────┴──────┐              │                  │
│         │           │             │              │                  │
│         ▼           ▼             ▼              ▼                  │
│  ┌───────────┐ ┌─────────┐ ┌──────────┐ ┌──────────────┐           │
│  │   Vista   │ │Generar  │ │ Firmar   │ │ Autorización │           │
│  │  (RIDE)   │ │  XML    │ │  XML     │ │   (49 dígitos│           │
│  └───────────┘ └─────────┘ └──────────┘ └──────────────┘           │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

## 📁 Estructura de Archivos

```
config/
└── sri.php                         # Configuración del SRI

app/
├── controllers/facturacion/
│   └── FacturaElectronicaController.php
│
├── models/
│   └── FacturaElectronica.php
│
├── services/SRI/
│   ├── FacturaElectronicaService.php    # Generación XML
│   ├── FirmaElectronicaService.php      # Firma digital XAdES-BES
│   ├── WebServiceSRIService.php         # Comunicación SOAP
│   └── RIDEService.php                  # Representación impresa
│
└── views/facturacion/facturas_electronicas/
    ├── index.php                        # Listado
    └── ver.php                          # Detalle

database/
└── paso_5_facturacion_electronica.sql   # Migración DB

storage/
├── sri/
│   ├── xml/
│   │   ├── generados/               # XML sin firma
│   │   ├── firmados/                # XML con firma
│   │   └── autorizados/             # XML autorizado
│   ├── ride/                        # RIDE HTML/PDF
│   └── logs/                        # Logs comunicación
└── certificados/                    # Certificados .p12
```

## ⚙️ Configuración

### 1. Archivo de Configuración (`config/sri.php`)

```php
return [
    'ambiente' => '1',                    // 1=Pruebas, 2=Producción
    'tipo_emision' => '1',                // 1=Normal, 2=Contingencia
    
    'webservices' => [
        'pruebas' => [
            'recepcion' => 'https://celcer.sri.gob.ec/...',
            'autorizacion' => 'https://celcer.sri.gob.ec/...',
        ],
        'produccion' => [
            'recepcion' => 'https://cel.sri.gob.ec/...',
            'autorizacion' => 'https://cel.sri.gob.ec/...',
        ],
    ],
    
    'emisor' => [
        'ruc' => '1792XXXXXXX001',
        'razon_social' => 'Mi Empresa S.A.',
        'nombre_comercial' => 'DigiSports',
        // ... más datos del emisor
    ],
    
    'certificado' => [
        'ruta' => '/storage/certificados/firma.p12',
        'clave' => 'tu_clave_certificado',
    ],
];
```

### 2. Base de Datos

Ejecutar la migración:

```sql
-- En MySQL/MariaDB
source database/paso_5_facturacion_electronica.sql;
```

### 3. Certificado Digital

1. Obtener certificado .p12 del Banco Central o entidad autorizada
2. Colocar en `storage/certificados/`
3. Configurar ruta y clave en `config/sri.php`

## 🔧 Componentes del Sistema

### 1. FacturaElectronicaService

Genera XML conforme al esquema XSD del SRI:

```php
$service = new FacturaElectronicaService();

// Generar clave de acceso (49 dígitos)
$claveAcceso = $service->generarClaveAcceso(
    $fechaEmision,    // ddmmaaaa
    $tipoComprobante, // 01=Factura
    $rucEmisor,
    $ambiente,
    $serie,           // 001001
    $secuencial,      // 000000001
    $codigoNumerico,  // 8 dígitos
    $tipoEmision      // 1=Normal
);

// Generar XML completo
$xml = $service->generarXMLFactura($datosFactura);
```

### 2. FirmaElectronicaService

Firma digital con estándar XAdES-BES:

```php
$firma = new FirmaElectronicaService();
$firma->cargarCertificado();

// Firmar XML
$xmlFirmado = $firma->firmarXML($xml);

// Verificar firma
$valido = $firma->verificarFirma($xmlFirmado);

// Info del certificado
$info = $firma->obtenerInfoCertificado();
```

### 3. WebServiceSRIService

Comunicación SOAP con el SRI:

```php
$ws = new WebServiceSRIService();

// Proceso completo (enviar + consultar)
$resultado = $ws->procesarComprobante($xmlFirmado, $claveAcceso);

// Solo enviar
$respuesta = $ws->enviarComprobante($xmlFirmado);

// Solo consultar
$autorizacion = $ws->consultarAutorizacion($claveAcceso);

// Verificar conectividad
$status = $ws->verificarConectividad();
```

### 4. RIDEService

Generación del RIDE (Representación Impresa):

```php
$ride = new RIDEService();

// Generar HTML
$html = $ride->generarRIDEHtml($datosFactura, $autorizacion);

// Guardar archivo
$ride->guardarRIDE($html, $claveAcceso);

// Generar PDF (requiere wkhtmltopdf)
$pdfPath = $ride->generarPDF($html, $claveAcceso);
```

## 📊 Estados de Factura

| Estado | Descripción | Siguiente Paso |
|--------|-------------|----------------|
| PENDIENTE | Factura creada pero no procesada | Generar XML |
| GENERADA | XML generado sin firma | Firmar |
| FIRMADA | XML firmado digitalmente | Enviar al SRI |
| ENVIADA | Enviada al SRI, esperando respuesta | Consultar |
| RECIBIDA | SRI recibió el comprobante | Consultar autorización |
| DEVUELTA | SRI devolvió con errores | Corregir y reenviar |
| AUTORIZADO | Autorizada exitosamente | ✓ Proceso completado |
| NO_AUTORIZADO | Rechazada por el SRI | Revisar errores |
| ERROR | Error técnico en el proceso | Reintentar |
| ANULADA | Factura anulada | - |

## 🔐 Estructura de Clave de Acceso

La clave de acceso de 49 dígitos se compone de:

```
[8 dígitos]  Fecha emisión (ddmmaaaa)
[2 dígitos]  Tipo comprobante (01=Factura)
[13 dígitos] RUC emisor
[1 dígito]   Tipo ambiente (1=Pruebas, 2=Prod)
[6 dígitos]  Serie (establecimiento+punto emisión)
[9 dígitos]  Secuencial
[8 dígitos]  Código numérico aleatorio
[1 dígito]   Tipo emisión (1=Normal)
[1 dígito]   Dígito verificador (Módulo 11)
```

## 📝 Códigos del SRI

### Tipos de Identificación

| Código | Descripción |
|--------|-------------|
| 04 | RUC |
| 05 | Cédula |
| 06 | Pasaporte |
| 07 | Consumidor Final |
| 08 | Identificación del Exterior |

### Tipos de Comprobante

| Código | Descripción |
|--------|-------------|
| 01 | Factura |
| 04 | Nota de Crédito |
| 05 | Nota de Débito |
| 06 | Guía de Remisión |
| 07 | Comprobante de Retención |

### Tarifas IVA

| Código | Descripción |
|--------|-------------|
| 0 | 0% |
| 2 | 12% |
| 3 | 14% |
| 4 | 15% |
| 6 | No Objeto de Impuesto |
| 7 | Exento de IVA |
| 8 | IVA diferenciado |

### Formas de Pago

| Código | Descripción |
|--------|-------------|
| 01 | Sin utilización del sistema financiero |
| 15 | Compensación de deudas |
| 16 | Tarjeta de débito |
| 17 | Dinero electrónico |
| 18 | Tarjeta prepago |
| 19 | Tarjeta de crédito |
| 20 | Otros con utilización del sistema financiero |
| 21 | Endoso de títulos |

## 🚀 Uso del Sistema

### Emitir Factura Electrónica

```php
// Desde el controlador
POST /digisports/public/?module=facturacion&controller=facturaelectronica&action=emitir

// Parámetros
factura_id: ID de la factura a emitir
```

### Consultar Estado

```php
GET /digisports/public/?module=facturacion&controller=facturaelectronica&action=consultarEstado&clave_acceso=XXXXX
```

### Reenviar Factura

```php
POST /digisports/public/?module=facturacion&controller=facturaelectronica&action=reenviar&id=X
```

### Descargar RIDE

```php
GET /digisports/public/?module=facturacion&controller=facturaelectronica&action=descargarRIDE&id=X
```

### Descargar XML

```php
GET /digisports/public/?module=facturacion&controller=facturaelectronica&action=descargarXML&id=X&tipo=autorizado
// tipo: generado, firmado, autorizado
```

## 🧪 Ambiente de Pruebas

1. Configurar `ambiente => '1'` en `config/sri.php`
2. Usar URLs de pruebas (celcer.sri.gob.ec)
3. Obtener certificado de pruebas del SRI

**RUC de pruebas del SRI:**
- RUC: 1792146739001
- Emisor: SRI PRUEBAS

## ⚠️ Notas Importantes

1. **Certificado Digital**: Debe ser emitido por entidad autorizada (Banco Central, Security Data, etc.)
2. **Extensión PHP**: Requiere `openssl`, `soap`, `dom`
3. **Secuenciales**: El sistema mantiene control automático de secuenciales
4. **Reintentos**: El sistema reintenta automáticamente en caso de errores de conectividad
5. **Logs**: Todas las comunicaciones se registran en `storage/sri/logs/`

## 📞 Soporte

- **SRI Ecuador**: https://www.sri.gob.ec
- **Documentación técnica**: https://www.sri.gob.ec/facturacion-electronica
- **Validador en línea**: https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline

---

**Versión:** 1.0.0  
**Fecha:** Enero 2025  
**Compatible con:** PHP 8.0+, MySQL 8.0+
