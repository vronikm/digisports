<?php
/**
 * Vista de Impresión de Recibo de Pago — Módulo Fútbol
 * @var array  $comprobante    Registro raw de futbol_comprobantes
 * @var array  $recibo_datos   Datos normalizados para ReciboService
 * @var string $url_enviar     URL para enviar por email (POST AJAX)
 * @var string $csrf_token
 * @var array  $modulo_actual
 */
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
$c  = $comprobante  ?? [];
$d  = $recibo_datos ?? [];
$ex = $c['datos_extra'] ?? [];

$numero    = $c['fcm_numero']        ?? '';
$estado    = $c['fcm_estado']        ?? 'EMITIDO';
$anulado   = $estado === 'ANULADO';
$enviado   = !empty($c['fcm_enviado_email']);
$total     = (float)($d['total']     ?? $c['fcm_total']        ?? 0);
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
                <button id="btnPdf" class="btn btn-sm btn-danger ml-1" title="Descargar PDF">
                    <i class="fas fa-file-pdf mr-1"></i>PDF
                </button>
                <?php if (!$anulado): ?>
                <button class="btn btn-sm btn-primary ml-1 js-enviar-email"
                        data-id="<?= (int)$c['fcm_comprobante_id'] ?>"
                        data-numero="<?= htmlspecialchars($numero) ?>"
                        title="<?= $enviado ? 'Reenviar por email' : 'Enviar por email' ?>">
                    <i class="fas fa-envelope mr-1"></i><?= $enviado ? 'Reenviar' : 'Enviar email' ?>
                </button>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($url_volver ?? '#') ?>" id="btnVolver" class="btn btn-sm btn-outline-secondary ml-1">
                    <i class="fas fa-arrow-left mr-1"></i>Volver
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="row justify-content-center">
<div class="col-lg-8 col-xl-7">

<?php include __DIR__ . '/_recibo_body.php'; ?>

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
    .qr-img-fallback { display:inline !important; }
}
</style>
<script nonce="<?= cspNonce() ?>">
$(function () {
    // Imprimir
    document.getElementById('btnImprimir').addEventListener('click', function () {
        window.print();
    });

    // Descargar PDF (html2pdf.js — captura #recibo-print y descarga)
    document.getElementById('btnPdf').addEventListener('click', function () {
        var btn = this;
        var el  = document.getElementById('recibo-print');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Generando...';

        var opt = {
            margin:       [5, 5, 5, 5],
            filename:     'Recibo_<?= preg_replace('/[^A-Za-z0-9_\-]/', '_', $numero) ?>.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, logging: false },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(el).save().then(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-file-pdf mr-1"></i>PDF';
        }).catch(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-file-pdf mr-1"></i>PDF';
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', 'No se pudo generar el PDF.', 'error');
            } else {
                alert('Error al generar el PDF.');
            }
        });
    });

    // Render QR en pantalla con QRCode.js y ocultar fallback
    var qrEl = document.getElementById('qrcode-print');
    if (qrEl) {
        var qrData = <?= json_encode($d['qr_url'] ?? ('Recibo:' . $numero . ' Total:$' . number_format($total, 2))) ?>;
        if (typeof QRCode !== 'undefined') {
            new QRCode(qrEl, {
                text: qrData,
                width: 80,
                height: 80,
                correctLevel: QRCode.CorrectLevel.M
            });
            // Ocultar imagen fallback ya que QRCode.js renderizó
            var fallback = qrEl.parentNode.querySelector('.qr-img-fallback');
            if (fallback) fallback.style.display = 'none';
        } else {
            // Sin QRCode.js, la imagen fallback ya muestra el QR
            qrEl.style.display = 'none';
        }
    }

    // Enviar por email
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-enviar-email');
        if (!btn) return;
        var compId = btn.dataset.id;
        var numero = btn.dataset.numero;

        var doEnviar = function() {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Generando PDF...';

            var el  = document.getElementById('recibo-print');
            var opt = {
                margin:       [5, 5, 5, 5],
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(el).outputPdf('blob').then(function(pdfBlob) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Enviando...';
                var fd = new FormData();
                fd.append('csrf_token', '<?= $csrf_token ?? '' ?>');
                fd.append('id', compId);
                fd.append('pdf_file', pdfBlob, 'Recibo_' + numero + '.pdf');
                return fetch('<?= $url_enviar ?? '' ?>', { method: 'POST', body: fd });
            })
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
<!-- html2pdf.js para descarga de PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.2/html2pdf.bundle.min.js" nonce="<?= cspNonce() ?>"></script>
<?php $scripts = ob_get_clean(); ?>
