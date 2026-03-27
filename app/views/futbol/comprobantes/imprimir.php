<?php
/**
 * Vista de Impresión de Recibo de Pago — Módulo Fútbol
 * @var array  $comprobante    Registro raw de futbol_comprobantes
 * @var array  $recibo_datos   Datos normalizados para ReciboService
 * @var string $url_pdf        URL para descargar PDF
 * @var string $url_enviar     URL para enviar por email (POST AJAX)
 * @var string $csrf_token
 * @var array  $modulo_actual
 */
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
$c  = $comprobante  ?? [];
$d  = $recibo_datos ?? [];
$ex = $c['datos_extra'] ?? [];

// Datos de presentación
$numero    = $c['fcm_numero']        ?? '';
$estado    = $c['fcm_estado']        ?? 'EMITIDO';
$anulado   = $estado === 'ANULADO';
$enviado   = !empty($c['fcm_enviado_email']);
$tipo      = $c['fcm_tipo']          ?? 'RECIBO';
$fecha     = !empty($c['fcm_fecha_emision']) ? date('d/m/Y H:i', strtotime($c['fcm_fecha_emision'])) : date('d/m/Y H:i');
$empresa   = $d['empresa_nombre']    ?? $ex['empresa_nombre']  ?? ($_SESSION['tenant_nombre'] ?? 'Escuela de Fútbol');
$sede      = $d['sede_nombre']       ?? $ex['sede_nombre']     ?? '';
$ruc       = $d['empresa_ruc']       ?? $ex['empresa_ruc']     ?? '';
$tel       = $d['empresa_telefono']  ?? $ex['sede_telefono']   ?? '';
$emailEmp  = $d['empresa_email']     ?? $ex['sede_email']      ?? '';
$dir       = $d['empresa_direccion'] ?? $ex['sede_direccion']  ?? '';

$alumno    = $d['alumno_nombre']     ?? $ex['alumno_nombre']   ?? '';
$alumnoCI  = $d['alumno_ci']         ?? $ex['alumno_identificacion'] ?? '';
$catNombre = $d['alumno_categoria']  ?? $ex['alumno_categoria'] ?? '';
$grpNombre = $d['alumno_grupo']      ?? $ex['alumno_grupo']    ?? '';

$repNombre = $d['rep_nombre']        ?? $ex['rep_nombre']      ?? '';
$repCI     = $d['rep_ci']            ?? $ex['rep_ci']          ?? '';
$repTel    = $d['rep_telefono']      ?? $ex['rep_telefono']    ?? '';
$repEmail  = $d['rep_email']         ?? $ex['rep_email']       ?? '';
$repDir    = $d['rep_direccion']     ?? $ex['rep_direccion']   ?? '';

$concepto  = $c['fcm_concepto']      ?? '';
$mesRef    = $d['mes_referencia']    ?? $ex['mes_referencia']  ?? '';
$metodo    = $d['metodo_pago']       ?? $ex['metodo_pago']     ?? $ex['pago_metodo'] ?? '';
$refPago   = $d['referencia']        ?? $ex['referencia']      ?? $ex['pago_referencia'] ?? '';

$monto     = (float)($d['monto']     ?? $ex['monto_base']      ?? 0);
$beca      = (float)($d['beca']      ?? $ex['beca']            ?? 0);
$descuento = (float)($d['descuento'] ?? $ex['descuento']       ?? 0);
$total     = (float)($d['total']     ?? $c['fcm_total']        ?? 0);
$saldo     = (float)($d['saldo']     ?? $c['fcm_saldo']        ?? 0);
$esAbono   = !empty($c['fcm_abono_id']);
$montoAbono = (float)($d['monto_abono'] ?? $ex['monto_abono'] ?? 0);

// QR data
$qrData = rawurlencode($d['qr_url'] ?? ('Recibo:' . $numero . ' Total:$' . number_format($total, 2)));
$qrUrl  = 'https://chart.googleapis.com/chart?chs=130x130&cht=qr&choe=UTF-8&chl=' . $qrData;

// Logo
$logoHtml = '';
if (!empty($d['logo_path']) && file_exists($d['logo_path'])) {
    $mime = mime_content_type($d['logo_path']);
    $logoHtml = '<img src="data:' . $mime . ';base64,' . base64_encode(file_get_contents($d['logo_path'])) . '" alt="Logo" style="max-height:60px;max-width:160px;object-fit:contain;">';
}

// Firma
$firmaHtml = '';
if (!empty($d['firma_path']) && file_exists($d['firma_path'])) {
    $mime = mime_content_type($d['firma_path']);
    $firmaHtml = '<img src="data:' . $mime . ';base64,' . base64_encode(file_get_contents($d['firma_path'])) . '" alt="Firma" style="max-height:55px;max-width:140px;object-fit:contain;">';
}

// Monto en letras
$entero = (int)floor(abs($total));
$centavos = round((abs($total) - $entero) * 100);
function _letras(int $n): string {
    if ($n === 0) return 'CERO';
    $u = ['','UNO','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE','DIEZ','ONCE','DOCE','TRECE','CATORCE','QUINCE','DIECISÉIS','DIECISIETE','DIECIOCHO','DIECINUEVE','VEINTE'];
    $d = ['','','VEINTI','TREINTA','CUARENTA','CINCUENTA','SESENTA','SETENTA','OCHENTA','NOVENTA'];
    $c = ['','CIENTO','DOSCIENTOS','TRESCIENTOS','CUATROCIENTOS','QUINIENTOS','SEISCIENTOS','SETECIENTOS','OCHOCIENTOS','NOVECIENTOS'];
    if ($n <= 20) return $u[$n];
    if ($n === 100) return 'CIEN';
    if ($n < 100) { $dd = intdiv($n,10); $uu = $n%10; return ($dd===2&&$uu>0?'VEINTI'.$u[$uu]:$d[$dd].($uu>0?' Y '.$u[$uu]:'')); }
    if ($n < 1000) { $cc = intdiv($n,100); $r = $n%100; return $c[$cc].($r>0?' '._letras($r):''); }
    if ($n < 2000) { $r = $n%1000; return 'MIL'.($r>0?' '._letras($r):''); }
    if ($n < 1000000) { $m = intdiv($n,1000); $r = $n%1000; return _letras($m).' MIL'.($r>0?' '._letras($r):''); }
    return (string)$n;
}
$enLetras = _letras($entero) . ' DÓLARES' . ($centavos > 0 ? ' CON ' . str_pad($centavos,2,'0',STR_PAD_LEFT) . '/100' : ' EXACTOS');
?>

<!-- Content Header (pantalla solamente) -->
<div class="content-header d-print-none">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-7">
                <h1 class="m-0">
                    <i class="fas fa-receipt mr-2" style="color:<?= $moduloColor ?>"></i>
                    Recibo <?= htmlspecialchars($numero) ?>
                    <?php if ($anulado): ?>
                    <span class="badge badge-danger ml-2">ANULADO</span>
                    <?php endif; ?>
                </h1>
            </div>
            <div class="col-sm-5 text-right">
                <button id="btnImprimir" class="btn btn-sm btn-secondary">
                    <i class="fas fa-print mr-1"></i>Imprimir
                </button>
                <a href="<?= htmlspecialchars($url_pdf ?? '#') ?>" id="btnPdf" class="btn btn-sm btn-danger ml-1" title="Descargar PDF">
                    <i class="fas fa-file-pdf mr-1"></i>PDF
                </a>
                <?php if (!$anulado): ?>
                <button class="btn btn-sm btn-primary ml-1 js-enviar-email"
                        data-id="<?= (int)$c['fcm_comprobante_id'] ?>"
                        data-numero="<?= htmlspecialchars($numero) ?>"
                        title="<?= $enviado ? 'Reenviar por email' : 'Enviar por email' ?>">
                    <i class="fas fa-envelope mr-1"></i><?= $enviado ? 'Reenviar' : 'Enviar email' ?>
                </button>
                <?php endif; ?>
                <button id="btnVolver" class="btn btn-sm btn-outline-secondary ml-1">
                    <i class="fas fa-arrow-left mr-1"></i>Volver
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="row justify-content-center">
<div class="col-lg-8 col-xl-7">

<!-- RECIBO -->
<div id="recibo-print" style="background:#fff;border:1px solid #ccc;font-family:Arial,Helvetica,sans-serif;font-size:10px;color:#1a1a1a;position:relative;">

    <?php if ($anulado): ?>
    <div style="position:absolute;top:38%;left:50%;transform:translate(-50%,-50%) rotate(-25deg);font-size:62px;font-weight:bold;color:rgba(220,38,38,0.15);border:8px solid rgba(220,38,38,0.15);padding:6px 28px;white-space:nowrap;z-index:10;pointer-events:none;border-radius:8px;">ANULADO</div>
    <?php endif; ?>

    <!-- ══ ENCABEZADO ══ -->
    <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom:2px solid #222;">
        <tr>
            <!-- Logo -->
            <td width="28%" style="padding:10px 12px;vertical-align:middle;border-right:1px solid #ccc;">
                <?php if ($logoHtml): ?>
                    <?= $logoHtml ?>
                <?php else: ?>
                    <div style="font-size:13px;font-weight:bold;color:#333;text-transform:uppercase;"><?= htmlspecialchars($empresa) ?></div>
                <?php endif; ?>
            </td>
            <!-- Datos empresa -->
            <td width="44%" style="padding:8px 10px;text-align:center;vertical-align:middle;border-right:1px solid #ccc;">
                <div style="font-size:13px;font-weight:bold;text-transform:uppercase;margin-bottom:4px;"><?= htmlspecialchars($empresa) ?></div>
                <div style="font-size:9px;line-height:1.7;color:#555;">
                    <?php if ($ruc): ?><span>RUC: <?= htmlspecialchars($ruc) ?></span><br><?php endif; ?>
                    <?php if ($dir): ?><?= htmlspecialchars($dir) ?><br><?php endif; ?>
                    <?php if ($tel): ?>Tel: <?= htmlspecialchars($tel) ?><br><?php endif; ?>
                    <?php if ($emailEmp): ?><?= htmlspecialchars($emailEmp) ?><br><?php endif; ?>
                    <?php if ($sede): ?><strong>Sede: <?= htmlspecialchars($sede) ?></strong><?php endif; ?>
                </div>
            </td>
            <!-- Número y QR -->
            <td width="28%" style="padding:8px 10px;vertical-align:top;">
                <div style="border:1px solid #222;padding:4px;text-align:center;font-size:11px;font-weight:bold;margin-bottom:6px;text-transform:uppercase;"><?= htmlspecialchars($tipo) ?> DE PAGO</div>
                <div style="font-size:9px;margin-bottom:2px;"><strong>N°:</strong> <?= htmlspecialchars($numero) ?></div>
                <div style="font-size:9px;margin-bottom:2px;"><strong>Fecha:</strong> <?= $fecha ?></div>
                <div style="text-align:center;margin-top:5px;">
                    <div id="qrcode-print"></div>
                    <img class="d-none d-print-block" src="<?= $qrUrl ?>" alt="QR" style="width:80px;height:80px;">
                </div>
            </td>
        </tr>
    </table>

    <!-- ══ REPRESENTANTE ══ -->
    <div style="border-bottom:1px solid #ddd;padding:7px 14px;">
        <div style="font-size:9px;font-weight:bold;text-transform:uppercase;color:#666;border-bottom:1px solid #eee;padding-bottom:3px;margin-bottom:5px;">Datos del Representante</div>
        <table width="100%" cellpadding="0" cellspacing="0" style="font-size:9.5px;">
            <?php if ($repNombre): ?><tr><td style="color:#666;width:130px;">Representante:</td><td style="font-weight:bold;"><?= htmlspecialchars($repNombre) ?></td></tr><?php endif; ?>
            <?php if ($repCI):     ?><tr><td style="color:#666;">CI / RUC:</td><td><?= htmlspecialchars($repCI) ?></td></tr><?php endif; ?>
            <?php if ($repTel):    ?><tr><td style="color:#666;">Teléfono:</td><td><?= htmlspecialchars($repTel) ?></td></tr><?php endif; ?>
            <?php if ($repDir):    ?><tr><td style="color:#666;">Dirección:</td><td><?= htmlspecialchars($repDir) ?></td></tr><?php endif; ?>
        </table>
    </div>

    <!-- ══ ALUMNO ══ -->
    <div style="border-bottom:1px solid #ddd;padding:7px 14px;">
        <div style="font-size:9px;font-weight:bold;text-transform:uppercase;color:#666;border-bottom:1px solid #eee;padding-bottom:3px;margin-bottom:5px;">Datos del Alumno</div>
        <table width="100%" cellpadding="0" cellspacing="0" style="font-size:9.5px;">
            <?php if ($alumno):    ?><tr><td style="color:#666;width:130px;">Alumno:</td><td style="font-weight:bold;"><?= htmlspecialchars($alumno) ?></td></tr><?php endif; ?>
            <?php if ($alumnoCI):  ?><tr><td style="color:#666;">Identificación:</td><td><?= htmlspecialchars($alumnoCI) ?></td></tr><?php endif; ?>
            <?php if ($catNombre): ?><tr><td style="color:#666;">Categoría:</td><td><?= htmlspecialchars($catNombre) ?></td></tr><?php endif; ?>
            <?php if ($grpNombre): ?><tr><td style="color:#666;">Grupo:</td><td><?= htmlspecialchars($grpNombre) ?></td></tr><?php endif; ?>
        </table>
    </div>

    <!-- ══ CONCEPTO ══ -->
    <div style="border-bottom:1px solid #ddd;padding:7px 14px;">
        <div style="font-size:9px;font-weight:bold;text-transform:uppercase;color:#666;border-bottom:1px solid #eee;padding-bottom:3px;margin-bottom:5px;">Concepto</div>
        <table width="100%" cellpadding="0" cellspacing="0" style="font-size:9.5px;">
            <tr><td style="color:#666;width:130px;">Descripción:</td><td style="font-weight:bold;"><?= htmlspecialchars($concepto) ?></td></tr>
            <?php if ($mesRef):   ?><tr><td style="color:#666;">Período:</td><td><?= htmlspecialchars(ucfirst($mesRef)) ?></td></tr><?php endif; ?>
            <?php if ($metodo):   ?><tr><td style="color:#666;">Forma de pago:</td><td><?= htmlspecialchars($metodo) ?><?= $refPago ? ' — Ref: ' . htmlspecialchars($refPago) : '' ?></td></tr><?php endif; ?>
        </table>
    </div>

    <!-- ══ MONTOS ══ -->
    <div style="padding:8px 14px;border-bottom:1px solid #ddd;">
        <div style="font-size:9px;font-weight:bold;text-transform:uppercase;color:#666;margin-bottom:6px;">Detalle del Pago</div>
        <table width="100%" cellpadding="0" cellspacing="2" style="font-size:9.5px;">
            <tr>
                <td style="color:#444;">Valor del servicio</td>
                <td style="text-align:right;font-weight:bold;width:100px;">$ <?= number_format($monto, 2) ?></td>
            </tr>
            <?php if ($beca > 0): ?>
            <tr>
                <td style="color:#16a34a;">Beca / Descuento automático</td>
                <td style="text-align:right;color:#16a34a;font-weight:bold;">- $ <?= number_format($beca, 2) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($descuento > 0): ?>
            <tr>
                <td style="color:#16a34a;">Descuento adicional</td>
                <td style="text-align:right;color:#16a34a;font-weight:bold;">- $ <?= number_format($descuento, 2) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($esAbono && $montoAbono > 0): ?>
            <tr>
                <td style="color:#1d4ed8;">Abono registrado</td>
                <td style="text-align:right;color:#1d4ed8;font-weight:bold;">$ <?= number_format($montoAbono, 2) ?></td>
            </tr>
            <?php if ($saldo > 0): ?>
            <tr>
                <td style="color:#dc2626;">Saldo pendiente</td>
                <td style="text-align:right;color:#dc2626;font-weight:bold;">$ <?= number_format($saldo, 2) ?></td>
            </tr>
            <?php endif; ?>
            <?php endif; ?>
        </table>
        <!-- Total box -->
        <table width="100%" cellpadding="5" cellspacing="0" style="margin-top:6px;background:#1a1a1a;color:#fff;border-radius:3px;font-size:11px;">
            <tr>
                <td style="font-weight:bold;padding-left:10px;">TOTAL PAGADO</td>
                <td style="text-align:right;font-size:15px;font-weight:bold;padding-right:10px;">$ <?= number_format($total, 2) ?></td>
            </tr>
        </table>
    </div>

    <!-- ══ EN LETRAS ══ -->
    <div style="padding:5px 14px;border-bottom:1px solid #ddd;font-size:9px;color:#444;">
        <strong>Son:</strong> <?= $enLetras ?>
    </div>

    <!-- ══ FIRMA ══ -->
    <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom:1px solid #ddd;">
        <tr>
            <td style="padding:12px 14px;vertical-align:bottom;">
                <div style="font-size:8.5px;color:#888;">Este documento es un comprobante de pago interno.<br>No tiene validez tributaria.</div>
            </td>
            <td width="160" style="padding:10px 14px;text-align:center;vertical-align:bottom;">
                <?php if ($firmaHtml): ?>
                    <?= $firmaHtml ?>
                <?php else: ?>
                    <div style="height:50px;"></div>
                <?php endif; ?>
                <div style="border-top:1px solid #333;margin:0 10px;padding-top:3px;font-size:8.5px;text-transform:uppercase;color:#555;">Firma Autorizada</div>
                <div style="font-size:8px;color:#999;margin-top:2px;"><?= htmlspecialchars($empresa) ?></div>
            </td>
        </tr>
    </table>

    <!-- ══ PIE ══ -->
    <div style="background:#f5f5f5;padding:4px 14px;text-align:center;font-size:8px;color:#888;">
        <?= htmlspecialchars($empresa) ?><?= $sede ? ' — Sede: ' . htmlspecialchars($sede) : '' ?> &nbsp;·&nbsp; Recibo generado el <?= date('d/m/Y H:i') ?>
    </div>

</div><!-- /#recibo-print -->

<!-- Alerta de anulado -->
<?php if ($anulado): ?>
<div class="alert alert-danger mt-3 d-print-none">
    <i class="fas fa-ban mr-1"></i> Este comprobante ha sido <strong>ANULADO</strong> y no tiene validez.
</div>
<?php endif; ?>

<!-- Alerta enviado por email -->
<?php if ($enviado && !$anulado): ?>
<div class="alert alert-success mt-3 d-print-none" style="font-size:.88rem;">
    <i class="fas fa-check-circle mr-1"></i> Comprobante ya enviado por email al representante.
</div>
<?php endif; ?>

</div><!-- /col -->
</div><!-- /row -->
</div><!-- /container -->
</section>

<?php ob_start(); ?>
<style>
@media print {
    .main-header, .main-sidebar, .main-footer, .content-header, nav.navbar { display:none !important; }
    .content-wrapper { margin:0 !important; padding:5px !important; background:#fff !important; }
    #recibo-print { border:1px solid #222 !important; box-shadow:none !important; }
    .d-print-none { display:none !important; }
    .d-none.d-print-block { display:block !important; }
    #qrcode-print { display:none !important; }
}
</style>
<script nonce="<?= cspNonce() ?>">
$(function () {
    // Imprimir y Volver (handlers movidos aquí por CSP — sin unsafe-inline)
    document.getElementById('btnImprimir').addEventListener('click', function () {
        window.print();
    });
    document.getElementById('btnVolver').addEventListener('click', function () {
        history.back();
    });

    // Render QR en pantalla (solo visible en pantalla, no en impresión)
    var qrData = <?= json_encode($d['qr_url'] ?? ('Recibo:' . $numero . ' Total:$' . number_format($total, 2))) ?>;
    if (typeof QRCode !== 'undefined') {
        new QRCode(document.getElementById('qrcode-print'), {
            text: qrData,
            width: 80,
            height: 80,
            correctLevel: QRCode.CorrectLevel.M
        });
    } else {
        // Fallback: mostrar imagen Google Charts
        var img = document.createElement('img');
        img.src = 'https://chart.googleapis.com/chart?chs=80x80&cht=qr&choe=UTF-8&chl=' + encodeURIComponent(qrData);
        img.alt = 'QR';
        img.style.cssText = 'width:80px;height:80px;';
        document.getElementById('qrcode-print').appendChild(img);
    }

    // Enviar por email
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-enviar-email');
        if (!btn) return;
        var compId = btn.dataset.id;
        var numero = btn.dataset.numero;

        var doEnviar = function() {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Enviando...';
            var fd = new FormData();
            fd.append('csrf_token', '<?= $csrf_token ?? '' ?>');
            fd.append('id', compId);
            fetch('<?= $url_enviar ?? '' ?>', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'success', title: '¡Enviado!', text: res.message, timer: 2500, showConfirmButton: false });
                        } else { alert(res.message); }
                        btn.innerHTML = '<i class="fas fa-check mr-1"></i>Enviado';
                    } else {
                        if (typeof Swal !== 'undefined') Swal.fire('Error', res.message, 'error');
                        else alert('Error: ' + res.message);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-envelope mr-1"></i>Enviar email';
                    }
                })
                .catch(function() {
                    if (typeof Swal !== 'undefined') Swal.fire('Error', 'Error de conexión.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-envelope mr-1"></i>Enviar email';
                });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Enviar comprobante',
                html: 'Se enviará el recibo <strong>' + numero + '</strong> con PDF adjunto al representante.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-paper-plane mr-1"></i> Enviar',
                cancelButtonText: 'Cancelar'
            }).then(function(r) { if (r.isConfirmed) doEnviar(); });
        } else {
            if (confirm('¿Enviar recibo ' + numero + ' por email?')) doEnviar();
        }
    });
});
</script>
<!-- QRCode.js para pantalla -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" nonce="<?= cspNonce() ?>"></script>
<?php $scripts = ob_get_clean(); ?>
