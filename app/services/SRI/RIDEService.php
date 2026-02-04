<?php
/**
 * DigiSports - Servicio para Generación de RIDE
 * Representación Impresa del Documento Electrónico
 * 
 * @package DigiSports\Services\SRI
 * @version 1.0.0
 */

namespace App\Services\SRI;

class RIDEService {
    
    private $config;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->config = require BASE_PATH . '/config/sri.php';
    }
    
    /**
     * Generar RIDE en HTML
     * 
     * @param array $datosFactura Datos de la factura
     * @param array $autorizacion Datos de autorización del SRI
     * @return string HTML del RIDE
     */
    public function generarRIDEHtml($datosFactura, $autorizacion = null) {
        $numeroAutorizacion = $autorizacion['numero_autorizacion'] ?? $datosFactura['clave_acceso'];
        $fechaAutorizacion = $autorizacion['fecha_autorizacion'] ?? date('Y-m-d H:i:s');
        $ambiente = $this->config['ambiente'] == 1 ? 'PRUEBAS' : 'PRODUCCIÓN';
        
        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>RIDE - Factura ' . $datosFactura['numero_completo'] . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; line-height: 1.4; padding: 20px; max-width: 800px; margin: 0 auto; }
        .ride-container { border: 1px solid #000; padding: 10px; }
        .header { display: flex; justify-content: space-between; border-bottom: 1px solid #000; padding-bottom: 10px; margin-bottom: 10px; }
        .logo-section { width: 30%; }
        .logo-section img { max-width: 100%; max-height: 80px; }
        .empresa-section { width: 35%; text-align: center; }
        .documento-section { width: 35%; border: 1px solid #000; padding: 5px; font-size: 9px; }
        .empresa-nombre { font-size: 14px; font-weight: bold; margin-bottom: 5px; }
        .documento-titulo { font-size: 12px; font-weight: bold; text-align: center; margin-bottom: 5px; }
        .info-row { display: flex; margin-bottom: 3px; }
        .info-label { font-weight: bold; width: 100px; }
        .info-value { flex: 1; }
        .cliente-section { border: 1px solid #000; padding: 8px; margin: 10px 0; }
        .cliente-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; }
        .detalle-section { margin: 10px 0; }
        .detalle-table { width: 100%; border-collapse: collapse; font-size: 9px; }
        .detalle-table th, .detalle-table td { border: 1px solid #000; padding: 4px; text-align: left; }
        .detalle-table th { background: #f0f0f0; font-weight: bold; }
        .detalle-table .numero { text-align: right; }
        .totales-section { display: flex; justify-content: flex-end; margin-top: 10px; }
        .totales-table { width: 300px; font-size: 10px; }
        .totales-table td { padding: 3px 5px; }
        .totales-table .label { text-align: right; font-weight: bold; }
        .totales-table .total-final { font-size: 12px; font-weight: bold; background: #f0f0f0; }
        .info-adicional { border: 1px solid #000; padding: 8px; margin-top: 10px; font-size: 9px; }
        .autorizacion-section { background: #f9f9f9; border: 1px solid #000; padding: 8px; margin-top: 10px; }
        .clave-acceso { font-family: monospace; font-size: 11px; word-break: break-all; }
        .barcode { text-align: center; margin: 10px 0; }
        .ambiente-pruebas { background: #fff3cd; color: #856404; padding: 5px; text-align: center; font-weight: bold; margin-bottom: 10px; }
        @media print {
            body { padding: 0; }
            .ride-container { border: none; }
        }
    </style>
</head>
<body>
    <div class="ride-container">';
        
        // Banner de ambiente de pruebas
        if ($this->config['ambiente'] == 1) {
            $html .= '<div class="ambiente-pruebas">⚠️ AMBIENTE DE PRUEBAS - SIN VALIDEZ TRIBUTARIA</div>';
        }
        
        // Header
        $html .= '
        <div class="header">
            <div class="logo-section">
                <div class="empresa-nombre">' . htmlspecialchars($this->config['emisor']['nombre_comercial']) . '</div>
                <div>' . htmlspecialchars($this->config['emisor']['razon_social']) . '</div>
                <div>RUC: ' . $this->config['emisor']['ruc'] . '</div>
                <div>' . htmlspecialchars($this->config['emisor']['direccion_matriz']) . '</div>
                <div>Obligado a llevar contabilidad: ' . $this->config['emisor']['obligado_contabilidad'] . '</div>
            </div>
            <div class="documento-section">
                <div class="documento-titulo">FACTURA</div>
                <div class="info-row">
                    <span class="info-label">No.:</span>
                    <span class="info-value">' . $datosFactura['numero_completo'] . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">AMBIENTE:</span>
                    <span class="info-value">' . $ambiente . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">EMISIÓN:</span>
                    <span class="info-value">NORMAL</span>
                </div>
                <div style="margin-top: 5px; font-weight: bold;">CLAVE DE ACCESO:</div>
                <div class="clave-acceso">' . $datosFactura['clave_acceso'] . '</div>
                <div class="barcode">
                    <img src="' . $this->generarCodigoBarras128($datosFactura['clave_acceso']) . '" alt="Código de barras" style="max-width: 100%; height: 40px;">
                </div>
                <div style="font-weight: bold;">Nro. AUTORIZACIÓN:</div>
                <div style="font-size: 8px; word-break: break-all;">' . $numeroAutorizacion . '</div>
                <div style="margin-top: 3px;">FECHA Y HORA: ' . $fechaAutorizacion . '</div>
            </div>
        </div>';
        
        // Datos del cliente
        $html .= '
        <div class="cliente-section">
            <div class="cliente-grid">
                <div class="info-row">
                    <span class="info-label">Razón Social:</span>
                    <span class="info-value">' . htmlspecialchars($datosFactura['cliente']['razon_social']) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha Emisión:</span>
                    <span class="info-value">' . $datosFactura['fecha_emision'] . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Identificación:</span>
                    <span class="info-value">' . $datosFactura['cliente']['identificacion'] . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dirección:</span>
                    <span class="info-value">' . htmlspecialchars($datosFactura['cliente']['direccion'] ?? 'N/A') . '</span>
                </div>
            </div>
        </div>';
        
        // Detalle de productos/servicios
        $html .= '
        <div class="detalle-section">
            <table class="detalle-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Cod. Principal</th>
                        <th>Descripción</th>
                        <th style="width: 50px;" class="numero">Cant.</th>
                        <th style="width: 70px;" class="numero">P. Unitario</th>
                        <th style="width: 60px;" class="numero">Dcto.</th>
                        <th style="width: 70px;" class="numero">Subtotal</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($datosFactura['detalles'] as $detalle) {
            $html .= '
                    <tr>
                        <td>' . htmlspecialchars($detalle['codigo']) . '</td>
                        <td>' . htmlspecialchars($detalle['descripcion']) . '</td>
                        <td class="numero">' . number_format($detalle['cantidad'], 2) . '</td>
                        <td class="numero">$' . number_format($detalle['precio_unitario'], 2) . '</td>
                        <td class="numero">$' . number_format($detalle['descuento'] ?? 0, 2) . '</td>
                        <td class="numero">$' . number_format($detalle['precio_total_sin_impuesto'], 2) . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
        </div>';
        
        // Totales
        $html .= '
        <div class="totales-section">
            <table class="totales-table">
                <tr>
                    <td class="label">SUBTOTAL SIN IMPUESTOS:</td>
                    <td class="numero">$' . number_format($datosFactura['totales']['subtotal'], 2) . '</td>
                </tr>
                <tr>
                    <td class="label">SUBTOTAL 0%:</td>
                    <td class="numero">$' . number_format($datosFactura['totales']['subtotal_0'] ?? 0, 2) . '</td>
                </tr>
                <tr>
                    <td class="label">SUBTOTAL ' . ($datosFactura['totales']['porcentaje_iva'] ?? 15) . '%:</td>
                    <td class="numero">$' . number_format($datosFactura['totales']['subtotal_iva'] ?? $datosFactura['totales']['subtotal'], 2) . '</td>
                </tr>
                <tr>
                    <td class="label">DESCUENTO:</td>
                    <td class="numero">$' . number_format($datosFactura['totales']['descuento'] ?? 0, 2) . '</td>
                </tr>
                <tr>
                    <td class="label">IVA ' . ($datosFactura['totales']['porcentaje_iva'] ?? 15) . '%:</td>
                    <td class="numero">$' . number_format($datosFactura['totales']['iva'], 2) . '</td>
                </tr>
                <tr class="total-final">
                    <td class="label">TOTAL:</td>
                    <td class="numero">$' . number_format($datosFactura['totales']['total'], 2) . '</td>
                </tr>
            </table>
        </div>';
        
        // Forma de pago
        $html .= '
        <div class="info-adicional">
            <strong>FORMA DE PAGO:</strong><br>
            <table class="detalle-table" style="margin-top: 5px;">
                <thead>
                    <tr>
                        <th>Forma de Pago</th>
                        <th class="numero">Valor</th>
                        <th>Plazo</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($datosFactura['pagos'] as $pago) {
            $formaPagoNombre = $this->config['formas_pago'][$pago['forma_pago']] ?? 'OTRO';
            $html .= '
                    <tr>
                        <td>' . $formaPagoNombre . '</td>
                        <td class="numero">$' . number_format($pago['total'], 2) . '</td>
                        <td>' . ($pago['plazo'] ?? '-') . ' ' . ($pago['unidad_tiempo'] ?? '') . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
        </div>';
        
        // Información adicional
        if (!empty($datosFactura['info_adicional'])) {
            $html .= '
        <div class="info-adicional">
            <strong>INFORMACIÓN ADICIONAL:</strong><br>';
            foreach ($datosFactura['info_adicional'] as $nombre => $valor) {
                $html .= '<div><strong>' . htmlspecialchars($nombre) . ':</strong> ' . htmlspecialchars($valor) . '</div>';
            }
            $html .= '</div>';
        }
        
        $html .= '
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Generar código de barras Code128 como imagen base64
     * 
     * @param string $codigo Código a representar
     * @return string Data URI de la imagen
     */
    private function generarCodigoBarras128($codigo) {
        // Implementación simplificada - en producción usar librería como picqer/php-barcode-generator
        // Por ahora retornamos un placeholder
        return 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 50">
                <rect width="400" height="50" fill="white"/>
                <text x="200" y="35" text-anchor="middle" font-family="monospace" font-size="8">' . substr($codigo, 0, 49) . '</text>
            </svg>'
        );
    }
    
    /**
     * Guardar RIDE como archivo HTML
     * 
     * @param string $html HTML del RIDE
     * @param string $claveAcceso Clave de acceso
     * @return string Ruta del archivo guardado
     */
    public function guardarRIDE($html, $claveAcceso) {
        $directorio = $this->config['storage']['ride'];
        
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        $archivo = $directorio . $claveAcceso . '.html';
        file_put_contents($archivo, $html);
        
        return $archivo;
    }
    
    /**
     * Convertir RIDE a PDF usando wkhtmltopdf o similar
     * Requiere wkhtmltopdf instalado en el servidor
     * 
     * @param string $html HTML del RIDE
     * @param string $claveAcceso Clave de acceso
     * @return string|null Ruta del PDF generado o null si falla
     */
    public function generarPDF($html, $claveAcceso) {
        $directorio = $this->config['storage']['ride'];
        
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        $archivoHtml = $directorio . $claveAcceso . '_temp.html';
        $archivoPdf = $directorio . $claveAcceso . '.pdf';
        
        // Guardar HTML temporal
        file_put_contents($archivoHtml, $html);
        
        // Intentar convertir con wkhtmltopdf
        $comando = 'wkhtmltopdf --page-size A4 --margin-top 10 --margin-bottom 10 --margin-left 10 --margin-right 10 "' . $archivoHtml . '" "' . $archivoPdf . '" 2>&1';
        
        exec($comando, $output, $returnCode);
        
        // Eliminar HTML temporal
        @unlink($archivoHtml);
        
        if ($returnCode === 0 && file_exists($archivoPdf)) {
            return $archivoPdf;
        }
        
        return null;
    }
}
