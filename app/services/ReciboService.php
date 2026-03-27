<?php
/**
 * DigiSports — Servicio de Generación de Recibos de Pago
 *
 * Genera HTML y PDF de recibos usando wkhtmltopdf (mismo patrón que RIDEService).
 * El PDF se genera en archivo temporal, se adjunta al email y se elimina.
 *
 * @package DigiSports\Services
 */

namespace App\Services;

class ReciboService {

    /**
     * Genera el HTML completo del recibo listo para imprimir o convertir a PDF.
     *
     * @param array $d  Datos del recibo (ver keys en la docblock de generarPdf)
     * @return string HTML
     */
    public function generarHtml(array $d): string {
        $empresa     = htmlspecialchars($d['empresa_nombre']    ?? 'Escuela de Fútbol');
        $ruc         = htmlspecialchars($d['empresa_ruc']       ?? '');
        $direccion   = htmlspecialchars($d['empresa_direccion'] ?? '');
        $telefono    = htmlspecialchars($d['empresa_telefono']  ?? '');
        $email       = htmlspecialchars($d['empresa_email']     ?? '');
        $sede        = htmlspecialchars($d['sede_nombre']       ?? '');
        $numero      = htmlspecialchars($d['numero']            ?? '');
        $fecha       = !empty($d['fecha']) ? date('d/m/Y', strtotime($d['fecha'])) : date('d/m/Y');
        $hora        = !empty($d['fecha']) ? date('H:i', strtotime($d['fecha'])) : date('H:i');
        $tipo        = htmlspecialchars($d['tipo']              ?? 'RECIBO');
        $concepto    = htmlspecialchars($d['concepto']          ?? 'Pago de mensualidad');
        $metodoPago  = htmlspecialchars($d['metodo_pago']       ?? 'EFECTIVO');
        $referencia  = htmlspecialchars($d['referencia']        ?? '');
        $mesRef      = htmlspecialchars($d['mes_referencia']    ?? '');

        // Alumno
        $alumnoNombre   = htmlspecialchars($d['alumno_nombre']    ?? '');
        $alumnoCI       = htmlspecialchars($d['alumno_ci']        ?? '');
        $alumnoCategoria = htmlspecialchars($d['alumno_categoria'] ?? '');
        $alumnoGrupo    = htmlspecialchars($d['alumno_grupo']     ?? '');

        // Representante
        $repNombre  = htmlspecialchars($d['rep_nombre']  ?? '');
        $repCI      = htmlspecialchars($d['rep_ci']      ?? '');
        $repTel     = htmlspecialchars($d['rep_telefono'] ?? '');
        $repEmail   = htmlspecialchars($d['rep_email']   ?? '');
        $repDir     = htmlspecialchars($d['rep_direccion'] ?? '');

        // Montos
        $monto    = (float)($d['monto']    ?? 0);
        $beca     = (float)($d['beca']     ?? 0);
        $descuento = (float)($d['descuento'] ?? 0);
        $total    = (float)($d['total']    ?? $monto - $beca - $descuento);
        $saldo    = (float)($d['saldo']    ?? 0);
        $esAbono  = isset($d['abono_id']) && $d['abono_id'];
        $montoAbono = (float)($d['monto_abono'] ?? 0);

        // QR: URL de verificación
        $qrData   = rawurlencode($d['qr_url'] ?? ('Recibo:' . $numero . ' Total:$' . number_format($total, 2)));
        $qrUrl    = 'https://chart.googleapis.com/chart?chs=130x130&cht=qr&choe=UTF-8&chl=' . $qrData;

        // Logo (base64 o vacío)
        $logoHtml = '';
        if (!empty($d['logo_base64'])) {
            $logoHtml = '<img src="' . $d['logo_base64'] . '" alt="Logo" style="max-height:60px;max-width:160px;object-fit:contain;">';
        } elseif (!empty($d['logo_path']) && file_exists($d['logo_path'])) {
            $mime = mime_content_type($d['logo_path']);
            $logoHtml = '<img src="data:' . $mime . ';base64,' . base64_encode(file_get_contents($d['logo_path'])) . '" alt="Logo" style="max-height:60px;max-width:160px;object-fit:contain;">';
        }

        // Firma (base64 o vacío)
        $firmaHtml = '';
        if (!empty($d['firma_base64'])) {
            $firmaHtml = '<img src="' . $d['firma_base64'] . '" alt="Firma" style="max-height:55px;max-width:140px;object-fit:contain;">';
        } elseif (!empty($d['firma_path']) && file_exists($d['firma_path'])) {
            $mime = mime_content_type($d['firma_path']);
            $firmaHtml = '<img src="data:' . $mime . ';base64,' . base64_encode(file_get_contents($d['firma_path'])) . '" alt="Firma" style="max-height:55px;max-width:140px;object-fit:contain;">';
        }

        // Montos en palabras (simple)
        $totalEnLetras = $this->numeroEnLetras($total);

        // Estado del recibo
        $anulado = isset($d['anulado']) && $d['anulado'];

        $html = '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recibo ' . $numero . '</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #1a1a1a; background:#fff; }
.recibo { width:100%; max-width:680px; margin:0 auto; border:1px solid #222; }
/* Encabezado */
.hdr { display:flex; justify-content:space-between; align-items:stretch; border-bottom:2px solid #222; }
.hdr-logo { width:28%; padding:10px 12px; display:flex; align-items:center; border-right:1px solid #bbb; }
.hdr-empresa { width:44%; padding:8px 10px; text-align:center; border-right:1px solid #bbb; }
.hdr-empresa .nombre { font-size:13px; font-weight:bold; text-transform:uppercase; margin-bottom:4px; }
.hdr-empresa .detalle { font-size:9px; line-height:1.6; color:#444; }
.hdr-num { width:28%; padding:8px 10px; font-size:9px; }
.hdr-num .tipo-doc { font-size:11px; font-weight:bold; text-align:center; border:1px solid #222; padding:4px; margin-bottom:6px; }
.hdr-num .campo { margin-bottom:3px; }
.hdr-num .label { font-weight:bold; }
/* QR section */
.qr-wrap { text-align:center; margin-top:4px; }
.qr-wrap img { width:80px; height:80px; }
/* Fecha box */
.fecha-box { background:#f0f0f0; border-bottom:1px solid #ccc; padding:5px 14px; font-size:9.5px; display:flex; justify-content:space-between; }
/* Datos */
.seccion { border-bottom:1px solid #ccc; padding:7px 14px; }
.seccion-titulo { font-size:9px; font-weight:bold; text-transform:uppercase; color:#555; margin-bottom:5px; border-bottom:1px solid #e0e0e0; padding-bottom:3px; }
.fila { display:flex; margin-bottom:3px; font-size:9.5px; }
.fila .etq { color:#666; width:130px; flex-shrink:0; }
.fila .val { font-weight:bold; flex:1; }
/* Montos */
.montos-box { border-bottom:1px solid #ccc; padding:8px 14px; }
.montos-titulo { font-size:9px; font-weight:bold; text-transform:uppercase; color:#555; margin-bottom:6px; }
.monto-fila { display:flex; justify-content:space-between; font-size:10px; padding:2px 0; border-bottom:1px dashed #e5e5e5; }
.monto-fila:last-child { border:none; }
.monto-fila .mf-label { color:#444; }
.monto-fila .mf-val { font-weight:bold; text-align:right; min-width:80px; }
.monto-fila.descuento .mf-val { color:#16a34a; }
.monto-fila.saldo .mf-val { color:#dc2626; }
.total-row { margin-top:6px; display:flex; justify-content:space-between; align-items:center; background:#1a1a1a; color:#fff; padding:5px 10px; border-radius:3px; }
.total-row .t-label { font-size:10px; font-weight:bold; }
.total-row .t-monto { font-size:14px; font-weight:bold; }
/* En letras */
.en-letras { padding:5px 14px; font-size:9px; color:#444; border-bottom:1px solid #ccc; }
/* Método */
.metodo-box { padding:6px 14px; border-bottom:1px solid #ccc; display:flex; justify-content:space-between; font-size:9.5px; }
/* Firma */
.firma-section { display:flex; justify-content:space-between; padding:10px 14px; border-bottom:1px solid #ccc; }
.firma-box { text-align:center; min-width:150px; }
.firma-box .firma-img { min-height:50px; margin-bottom:4px; display:flex; align-items:flex-end; justify-content:center; }
.firma-linea { border-top:1px solid #333; margin:0 10px; padding-top:3px; font-size:8.5px; text-transform:uppercase; color:#555; }
/* Pie */
.pie { background:#f5f5f5; padding:5px 14px; text-align:center; font-size:8px; color:#777; }
/* ANULADO */
.sello-anulado { position:fixed; top:40%; left:50%; transform:translate(-50%,-50%) rotate(-25deg); font-size:60px; font-weight:bold; color:rgba(220,38,38,0.18); border:8px solid rgba(220,38,38,0.18); padding:5px 25px; white-space:nowrap; pointer-events:none; }
@media print {
  body { padding:0; margin:0; }
  .recibo { border:1px solid #222; }
}
</style>
</head>
<body>
<div class="recibo">
  <!-- ENCABEZADO -->
  <div class="hdr">
    <div class="hdr-logo">';

        if ($logoHtml) {
            $html .= $logoHtml;
        } else {
            $html .= '<div style="font-size:11px;font-weight:bold;color:#444;">' . $empresa . '</div>';
        }

        $html .= '</div>
    <div class="hdr-empresa">
      <div class="nombre">' . $empresa . '</div>
      <div class="detalle">';
        if ($ruc)        $html .= 'RUC: ' . $ruc . '<br>';
        if ($direccion)  $html .= $direccion . '<br>';
        if ($telefono)   $html .= 'Tel: ' . $telefono . '<br>';
        if ($email)      $html .= $email . '<br>';
        if ($sede)       $html .= '<strong>Sede: ' . $sede . '</strong>';
        $html .= '</div>
    </div>
    <div class="hdr-num">
      <div class="tipo-doc">' . $tipo . ' DE PAGO</div>
      <div class="campo"><span class="label">N°: </span>' . $numero . '</div>
      <div class="campo"><span class="label">Fecha: </span>' . $fecha . '</div>
      <div class="campo"><span class="label">Hora: </span>' . $hora . '</div>
      <div class="qr-wrap">
        <img src="' . $qrUrl . '" alt="QR">
      </div>
    </div>
  </div>

  <!-- DATOS DEL REPRESENTANTE/ALUMNO -->
  <div class="seccion">
    <div class="seccion-titulo">Datos del Representante</div>';
        if ($repNombre) $html .= '<div class="fila"><span class="etq">Representante:</span><span class="val">' . $repNombre . '</span></div>';
        if ($repCI)     $html .= '<div class="fila"><span class="etq">CI/RUC:</span><span class="val">' . $repCI . '</span></div>';
        if ($repTel)    $html .= '<div class="fila"><span class="etq">Teléfono:</span><span class="val">' . $repTel . '</span></div>';
        if ($repDir)    $html .= '<div class="fila"><span class="etq">Dirección:</span><span class="val">' . $repDir . '</span></div>';
        $html .= '</div>

  <div class="seccion">
    <div class="seccion-titulo">Datos del Alumno</div>';
        if ($alumnoNombre)    $html .= '<div class="fila"><span class="etq">Alumno:</span><span class="val">' . $alumnoNombre . '</span></div>';
        if ($alumnoCI)        $html .= '<div class="fila"><span class="etq">Identificación:</span><span class="val">' . $alumnoCI . '</span></div>';
        if ($alumnoCategoria) $html .= '<div class="fila"><span class="etq">Categoría:</span><span class="val">' . $alumnoCategoria . '</span></div>';
        if ($alumnoGrupo)     $html .= '<div class="fila"><span class="etq">Grupo:</span><span class="val">' . $alumnoGrupo . '</span></div>';
        $html .= '</div>

  <!-- CONCEPTO -->
  <div class="seccion">
    <div class="seccion-titulo">Concepto</div>
    <div class="fila"><span class="etq">Descripción:</span><span class="val">' . $concepto . '</span></div>';
        if ($mesRef) $html .= '<div class="fila"><span class="etq">Período:</span><span class="val">' . $mesRef . '</span></div>';
        $html .= '</div>

  <!-- MONTOS -->
  <div class="montos-box">
    <div class="montos-titulo">Detalle del Pago</div>';
        $html .= '<div class="monto-fila"><span class="mf-label">Valor del servicio</span><span class="mf-val">$ ' . number_format($monto, 2) . '</span></div>';
        if ($beca > 0) {
            $html .= '<div class="monto-fila descuento"><span class="mf-label">Beca / Descuento automático</span><span class="mf-val">- $ ' . number_format($beca, 2) . '</span></div>';
        }
        if ($descuento > 0) {
            $html .= '<div class="monto-fila descuento"><span class="mf-label">Descuento adicional</span><span class="mf-val">- $ ' . number_format($descuento, 2) . '</span></div>';
        }
        if ($esAbono && $montoAbono > 0) {
            $html .= '<div class="monto-fila"><span class="mf-label">Abono registrado</span><span class="mf-val">$ ' . number_format($montoAbono, 2) . '</span></div>';
            if ($saldo > 0) {
                $html .= '<div class="monto-fila saldo"><span class="mf-label">Saldo pendiente</span><span class="mf-val">$ ' . number_format($saldo, 2) . '</span></div>';
            }
        }
        $html .= '<div class="total-row">
      <span class="t-label">TOTAL PAGADO</span>
      <span class="t-monto">$ ' . number_format($total, 2) . '</span>
    </div>
  </div>

  <!-- EN LETRAS -->
  <div class="en-letras">
    <strong>Son:</strong> ' . strtoupper($totalEnLetras) . ' DÓLARES AMERICANOS ' . (($total - floor($total)) > 0 ? 'CON ' . str_pad(round(($total - floor($total)) * 100), 2, '0', STR_PAD_LEFT) . '/100' : 'EXACTOS') . '
  </div>

  <!-- MÉTODO DE PAGO -->
  <div class="metodo-box">
    <div><strong>Forma de pago:</strong> ' . $metodoPago . ($referencia ? ' — Ref: ' . $referencia : '') . '</div>
    <div style="color:#555;font-size:8.5px;">Emitido el ' . $fecha . ' a las ' . $hora . '</div>
  </div>

  <!-- FIRMA -->
  <div class="firma-section">
    <div></div>
    <div class="firma-box">
      <div class="firma-img">' . ($firmaHtml ?: '<div style="height:50px;"></div>') . '</div>
      <div class="firma-linea">Firma autorizada</div>
      <div style="font-size:8px;color:#777;margin-top:3px;">' . $empresa . '</div>
    </div>
  </div>

  <!-- PIE -->
  <div class="pie">
    Este documento es un comprobante de pago interno. No tiene validez tributaria. &nbsp;|&nbsp; ' . $empresa . ($sede ? ' — Sede: ' . $sede : '') . '
  </div>
</div>';

        if ($anulado) {
            $html .= '<div class="sello-anulado">ANULADO</div>';
        }

        $html .= '
</body>
</html>';

        return $html;
    }

    /**
     * Genera el PDF del recibo en un archivo temporal.
     * El llamador es responsable de eliminar el archivo después de usarlo.
     *
     * @param array $d Datos del recibo
     * @return string|null Ruta del PDF temporal, o null si wkhtmltopdf no está disponible
     */
    public function generarPdf(array $d): ?string {
        $html    = $this->generarHtml($d);
        $binario = $this->resolverWkhtmltopdf();
        if (!$binario) return null;

        $tmpDir  = sys_get_temp_dir();
        $tmpHtml = $tmpDir . '/recibo_' . uniqid() . '.html';
        $tmpPdf  = $tmpDir . '/recibo_' . uniqid() . '.pdf';

        file_put_contents($tmpHtml, $html);

        $cmd = '"' . $binario . '"'
             . ' --page-size A5 --orientation Portrait'
             . ' --margin-top 5 --margin-bottom 5 --margin-left 5 --margin-right 5'
             . ' --enable-local-file-access'
             . ' --load-error-handling ignore'
             . ' "' . $tmpHtml . '" "' . $tmpPdf . '" 2>&1';

        if (!function_exists('exec')) {
            error_log('[ReciboService] exec() no disponible en este servidor');
            @unlink($tmpHtml);
            return null;
        }

        exec($cmd, $output, $code);
        @unlink($tmpHtml);

        if ($code === 0 && file_exists($tmpPdf) && filesize($tmpPdf) > 0) {
            return $tmpPdf;
        }

        error_log('[ReciboService] wkhtmltopdf falló — code=' . $code . ' — ' . implode(' | ', $output));
        @unlink($tmpPdf);
        return null;
    }

    /**
     * Convierte un número a palabras en español (hasta 999.99).
     */
    public function numeroEnLetras(float $n): string {
        $n = abs($n);
        $entero = (int)floor($n);
        return $this->enteroEnLetras($entero);
    }

    private function enteroEnLetras(int $n): string {
        if ($n === 0) return 'cero';

        $unidades   = ['','uno','dos','tres','cuatro','cinco','seis','siete','ocho','nueve',
                       'diez','once','doce','trece','catorce','quince','dieciséis','diecisiete',
                       'dieciocho','diecinueve','veinte'];
        $decenas    = ['','','veinti','treinta','cuarenta','cincuenta','sesenta','setenta','ochenta','noventa'];
        $centenas   = ['','ciento','doscientos','trescientos','cuatrocientos','quinientos',
                       'seiscientos','setecientos','ochocientos','novecientos'];

        if ($n <= 20) return $unidades[$n];
        if ($n === 100) return 'cien';
        if ($n < 100) {
            $d = intdiv($n, 10);
            $u = $n % 10;
            if ($d === 2 && $u > 0) return 'veinti' . $unidades[$u];
            return $decenas[$d] . ($u > 0 ? ' y ' . $unidades[$u] : '');
        }
        if ($n < 1000) {
            $c = intdiv($n, 100);
            $r = $n % 100;
            return $centenas[$c] . ($r > 0 ? ' ' . $this->enteroEnLetras($r) : '');
        }
        if ($n < 2000) {
            $r = $n % 1000;
            return 'mil' . ($r > 0 ? ' ' . $this->enteroEnLetras($r) : '');
        }
        if ($n < 1000000) {
            $m = intdiv($n, 1000);
            $r = $n % 1000;
            return $this->enteroEnLetras($m) . ' mil' . ($r > 0 ? ' ' . $this->enteroEnLetras($r) : '');
        }
        return (string)$n; // fallback para números muy grandes
    }

    private function resolverWkhtmltopdf(): ?string {
        $candidatos = [
            'C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe',
            'C:\\Program Files (x86)\\wkhtmltopdf\\bin\\wkhtmltopdf.exe',
            '/usr/local/bin/wkhtmltopdf',
            '/usr/bin/wkhtmltopdf',
        ];
        foreach ($candidatos as $ruta) {
            if (file_exists($ruta)) return $ruta;
        }
        $cmd    = PHP_OS_FAMILY === 'Windows' ? 'where wkhtmltopdf 2>nul' : 'which wkhtmltopdf 2>/dev/null';
        $result = trim((string)shell_exec($cmd));
        if ($result && file_exists(strtok($result, PHP_EOL))) {
            return strtok($result, PHP_EOL);
        }
        return null;
    }
}
