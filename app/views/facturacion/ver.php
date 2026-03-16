<?php
/**
 * Vista: Detalles de Factura
 * Muestra detalles completos, líneas, pagos y estado SRI.
 */
$estadoFe = $fe['fac_estado_sri'] ?? null;
$puedeEnviarSRI  = in_array($factura['fac_estado'] ?? '', ['EMITIDA', 'PAGADA'])
    && (!$estadoFe || in_array($estadoFe, ['GENERADA', 'FIRMADA', 'ERROR']));
$puedeVerificar  = ($estadoFe === 'ENVIADA');
?>

<div class="container-fluid my-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">
                <i class="fas fa-file-invoice-dollar"></i>
                Factura <?= htmlspecialchars($factura['fac_numero'] ?? '') ?>
            </h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?= url('facturacion', 'factura', 'index') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Información General -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h5 class="mb-0">Información General</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong>Número Factura:</strong><br>
                        <?= htmlspecialchars($factura['fac_numero'] ?? '') ?>
                    </p>
                    <p class="mb-2">
                        <strong>Estado:</strong><br>
                        <?php
                        $estadoColor = [
                            'BORRADOR' => 'secondary',
                            'EMITIDA'  => 'warning',
                            'PAGADA'   => 'success',
                            'ANULADA'  => 'danger',
                        ][$factura['fac_estado'] ?? ''] ?? 'secondary';
                        ?>
                        <span class="badge badge-<?= $estadoColor ?>">
                            <?= htmlspecialchars($factura['fac_estado'] ?? '') ?>
                        </span>
                    </p>
                    <p class="mb-0">
                        <strong>Cliente:</strong><br>
                        <?= htmlspecialchars($factura['nombre_cliente'] ?? 'Cliente no especificado') ?>
                    </p>
                </div>
                <div class="col-md-6 text-right">
                    <p class="mb-2">
                        <strong>Fecha Emisión:</strong><br>
                        <?= !empty($factura['fac_fecha_emision']) ? date('d/m/Y H:i', strtotime($factura['fac_fecha_emision'])) : 'N/A' ?>
                    </p>
                    <p class="mb-2">
                        <strong>Fecha Vencimiento:</strong><br>
                        <?= !empty($factura['fac_fecha_vencimiento']) ? date('d/m/Y', strtotime($factura['fac_fecha_vencimiento'])) : 'N/A' ?>
                    </p>
                    <p class="mb-0">
                        <strong>Forma de Pago:</strong><br>
                        <?= htmlspecialchars($factura['forma_pago_nombre'] ?? 'N/A') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Líneas de Factura -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h5 class="mb-0">Líneas de Factura</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Descripción</th>
                        <th class="text-right" style="width:100px">Cantidad</th>
                        <th class="text-right" style="width:120px">Precio Unit.</th>
                        <th class="text-right" style="width:120px">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lineas as $linea): ?>
                        <tr>
                            <td><?= htmlspecialchars($linea['lin_descripcion']) ?></td>
                            <td class="text-right"><?= htmlspecialchars($linea['lin_cantidad']) ?></td>
                            <td class="text-right">$<?= number_format($linea['lin_precio_unitario'], 2) ?></td>
                            <td class="text-right"><strong>$<?= number_format($linea['lin_total'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Totales -->
    <div class="row mb-3">
        <div class="col-md-5 ml-auto">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>$<?= number_format($factura['fac_subtotal'] ?? 0, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>IVA (15%):</span>
                        <span>$<?= number_format($factura['fac_iva'] ?? 0, 2) ?></span>
                    </div>
                    <?php if (($factura['fac_descuento'] ?? 0) > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Descuento:</span>
                        <span>-$<?= number_format($factura['fac_descuento'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong class="text-primary" style="font-size:1.25rem">
                            $<?= number_format($factura['fac_total'] ?? 0, 2) ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagos Registrados -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pagos Registrados</h5>
                <?php if (!in_array($factura['fac_estado'] ?? '', ['PAGADA', 'ANULADA'])): ?>
                    <a href="<?= url('facturacion', 'pago', 'crear', ['factura_id' => $factura['fac_id']]) ?>"
                       class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Nuevo Pago
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Forma de Pago</th>
                        <th>Referencia</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pagos)): ?>
                        <tr><td colspan="5" class="text-center py-3 text-muted">Sin pagos registrados</td></tr>
                    <?php else: ?>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($pago['pag_fecha'])) ?></td>
                                <td>$<?= number_format($pago['pag_monto'], 2) ?></td>
                                <td><?= htmlspecialchars($pago['forma_pago_nombre'] ?? 'N/A') ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($pago['pag_referencia'] ?? '') ?></small></td>
                                <td>
                                    <span class="badge badge-<?= ($pago['pag_estado'] ?? '') === 'CONFIRMADO' ? 'success' : 'danger' ?>">
                                        <?= htmlspecialchars($pago['pag_estado'] ?? '') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php $total_pagado = array_sum(array_column($pagos ?? [], 'pag_monto')); ?>
        <div class="card-footer bg-light d-flex justify-content-between">
            <strong>Total Pagado:</strong>
            <strong>$<?= number_format($total_pagado, 2) ?></strong>
        </div>
    </div>

    <!-- Factura Electrónica SRI -->
    <?php if (in_array($factura['fac_estado'] ?? '', ['EMITIDA', 'PAGADA'])): ?>
    <div class="card mb-3" id="card-sri">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-receipt mr-1"></i> Factura Electrónica SRI</h5>
            <?php if ($puedeEnviarSRI): ?>
                <button type="button" class="btn btn-sm btn-primary" id="btnEnviarSRI">
                    <i class="fas fa-paper-plane mr-1"></i> Enviar al SRI
                </button>
            <?php elseif ($puedeVerificar): ?>
                <button type="button" class="btn btn-sm btn-warning" id="btnVerificarEstado">
                    <i class="fas fa-sync-alt mr-1"></i> Verificar estado
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body" id="fe-status-body">
            <?php if (!$fe): ?>
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    Factura aún no enviada al SRI. Haga clic en <strong>Enviar al SRI</strong>.
                </p>
            <?php else: ?>
                <?php
                $coloresFe = [
                    'GENERADA'      => 'secondary',
                    'FIRMADA'       => 'info',
                    'ENVIADA'       => 'warning',
                    'AUTORIZADO'    => 'success',
                    'ERROR'         => 'danger',
                    'NO AUTORIZADO' => 'danger',
                ];
                $colorFe = $coloresFe[$estadoFe] ?? 'secondary';
                ?>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Estado SRI:</strong><br>
                            <span class="badge badge-<?= $colorFe ?>"><?= htmlspecialchars($estadoFe) ?></span>
                        </p>
                        <?php if (!empty($fe['fac_numero_autorizacion'])): ?>
                        <p class="mb-2">
                            <strong>Nro. Autorización:</strong><br>
                            <code><?= htmlspecialchars($fe['fac_numero_autorizacion']) ?></code>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php if ($estadoFe === 'AUTORIZADO'): ?>
                    <div class="col-md-6 text-right">
                        <a href="<?= url('facturacion', 'factura_electronica', 'descargarXML', ['id' => $fe['fac_id']]) ?>"
                           class="btn btn-sm btn-outline-secondary mr-1">
                            <i class="fas fa-code mr-1"></i> Descargar XML
                        </a>
                        <a href="<?= url('facturacion', 'factura_electronica', 'descargarRIDE', ['id' => $fe['fac_id']]) ?>"
                           class="btn btn-sm btn-outline-danger" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i> Ver RIDE
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($estadoFe === 'ENVIADA'): ?>
                <div class="alert alert-warning mb-0 mt-2">
                    <i class="fas fa-clock mr-1"></i>
                    <strong>En procesamiento:</strong> El SRI recibió el comprobante y está procesando la autorización.
                    Haga clic en <strong>Verificar estado</strong> en unos minutos.
                </div>
                <?php endif; ?>
                <?php if ($estadoFe === 'ERROR' && !empty($fe['fac_mensaje_error'])): ?>
                <div class="alert alert-danger mb-0 mt-2">
                    <strong>Error:</strong> <?= htmlspecialchars($fe['fac_mensaje_error']) ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Acciones -->
    <div class="row mb-4">
        <div class="col-12">
            <?php if (($factura['fac_estado'] ?? '') === 'BORRADOR'): ?>
                <a href="<?= url('facturacion', 'factura', 'emitir', ['id' => $factura['fac_id']]) ?>"
                   class="btn btn-success mr-1"
                   onclick="return confirm('¿Confirma emitir esta factura?')">
                    <i class="fas fa-check mr-1"></i> Emitir
                </a>
            <?php endif; ?>

            <?php if (!in_array($factura['fac_estado'] ?? '', ['ANULADA', 'PAGADA'])): ?>
                <button type="button"
                        id="btn-anular-ver"
                        class="btn btn-danger"
                        data-numero="<?= htmlspecialchars($factura['fac_numero'] ?? '') ?>"
                        data-url="<?= htmlspecialchars(url('facturacion', 'factura', 'anular', ['id' => $factura['fac_id']])) ?>">
                    <i class="fas fa-times mr-1"></i> Anular
                </button>
            <?php endif; ?>
            <?php if (($factura['fac_estado'] ?? '') === 'ANULADA'): ?>
                <button type="button"
                        id="btn-reactivar-ver"
                        class="btn btn-outline-success"
                        data-numero="<?= htmlspecialchars($factura['fac_numero'] ?? '') ?>"
                        data-url="<?= htmlspecialchars(url('facturacion', 'factura', 'reactivar', ['id' => $factura['fac_id']])) ?>">
                    <i class="fas fa-redo mr-1"></i> Reactivar
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Form oculto para envío al SRI -->
<?php if ($puedeEnviarSRI): ?>
<form id="formEnviarSRI" style="display:none">
    <input type="hidden" name="factura_id"  value="<?= (int)$factura['fac_id'] ?>">
    <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="hidden" name="forma_pago"  value="<?= htmlspecialchars($factura['forma_pago_codigo'] ?? '01') ?>">
</form>
<?php endif; ?>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
(function ($) {
    'use strict';

    var urlEmitirSRI       = <?= json_encode(url('facturacion', 'factura_electronica', 'emitir')) ?>;
    var urlVerificarEstado = <?= json_encode(url('facturacion', 'factura_electronica', 'verificarEstado', ['factura_id' => $factura['fac_id']])) ?>;
    var csrfToken          = <?= json_encode($csrf_token ?? '') ?>;

    var Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });

    function mostrarAutorizado(nroAutorizacion) {
        $('#fe-status-body').html(
            '<div class="row">' +
            '<div class="col-md-8">' +
            '<p class="mb-2"><strong>Estado SRI:</strong><br>' +
            '<span class="badge badge-success">AUTORIZADO</span></p>' +
            '<p class="mb-0"><strong>Nro. Autorización:</strong><br>' +
            '<code>' + $('<div>').text(nroAutorizacion || '').html() + '</code></p>' +
            '</div></div>'
        );
        $('#card-sri .card-header button').remove();
    }

    function mostrarEnProcesamiento() {
        $('#fe-status-body').html(
            '<div class="row"><div class="col-12">' +
            '<p class="mb-2"><strong>Estado SRI:</strong><br>' +
            '<span class="badge badge-warning">ENVIADA</span></p>' +
            '<div class="alert alert-warning mb-0 mt-2">' +
            '<i class="fas fa-clock mr-1"></i>' +
            '<strong>En procesamiento:</strong> El SRI recibió el comprobante y está procesando la autorización. ' +
            'Haga clic en <strong>Verificar estado</strong> en unos minutos.' +
            '</div></div></div>'
        );
        // Reemplazar botón por "Verificar estado"
        $('#card-sri .card-header button')
            .attr('id', 'btnVerificarEstado')
            .removeClass('btn-primary')
            .addClass('btn-warning')
            .html('<i class="fas fa-sync-alt mr-1"></i> Verificar estado');
        bindVerificar();
    }

    // Enviar al SRI
    $('#btnEnviarSRI').on('click', function () {
        var $btn = $(this);

        Swal.fire({
            title: '¿Enviar al SRI?',
            html: 'Se enviará esta factura al SRI para su autorización electrónica.<br>' +
                  '<small class="text-muted">Este proceso puede tardar unos segundos.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-paper-plane"></i>&nbsp;Sí, enviar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...');
            $('#fe-status-body').html(
                '<div class="text-center py-3"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i>' +
                '<p class="mt-2 text-muted">Enviando al SRI, por favor espere...</p></div>'
            );

            $.ajax({
                url: urlEmitirSRI,
                method: 'POST',
                data: $('#formEnviarSRI').serialize(),
                dataType: 'json',
                timeout: 120000,
                success: function (res) {
                    if (res.success) {
                        var d = res.data || {};
                        if (d.estado === 'EN_PROCESAMIENTO') {
                            mostrarEnProcesamiento();
                            Toast.fire({ icon: 'warning', title: res.message || 'Factura enviada. En procesamiento en el SRI.' });
                        } else {
                            mostrarAutorizado(d.numero_autorizacion);
                            Toast.fire({ icon: 'success', title: res.message || 'Factura autorizada por el SRI.' });
                        }
                    } else {
                        $('#fe-status-body').html(
                            '<div class="alert alert-danger mb-0">' +
                            '<strong>Error SRI:</strong> ' + $('<div>').text(res.message || 'Error desconocido').html() +
                            '</div>'
                        );
                        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Reintentar');
                        Toast.fire({ icon: 'error', title: res.message || 'Error al enviar al SRI.' });
                    }
                },
                error: function (xhr) {
                    var msg = 'Error de comunicación con el servidor';
                    try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
                    $('#fe-status-body').html(
                        '<div class="alert alert-danger mb-0"><strong>Error:</strong> ' +
                        $('<div>').text(msg).html() + '</div>'
                    );
                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Reintentar');
                    Toast.fire({ icon: 'error', title: msg });
                }
            });
        });
    });

    // Verificar estado (para ENVIADA)
    function bindVerificar() {
        $('#btnVerificarEstado').off('click').on('click', function () {
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Consultando...');

            $.ajax({
                url: urlVerificarEstado,
                method: 'GET',
                dataType: 'json',
                timeout: 30000,
                success: function (res) {
                    if (res.success) {
                        var d = res.data || {};
                        if (d.estado === 'AUTORIZADO') {
                            mostrarAutorizado(d.numero_autorizacion);
                            Toast.fire({ icon: 'success', title: res.message || 'Factura autorizada por el SRI.' });
                        } else {
                            // Sigue en procesamiento
                            $btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Verificar estado');
                            Toast.fire({ icon: 'warning', title: res.message || 'Aún en procesamiento. Intente en unos minutos.' });
                        }
                    } else {
                        $btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Verificar estado');
                        Toast.fire({ icon: 'error', title: res.message || 'Error al consultar estado.' });
                    }
                },
                error: function () {
                    $btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Verificar estado');
                    Toast.fire({ icon: 'error', title: 'Error de comunicación al verificar estado.' });
                }
            });
        });
    }

    bindVerificar();

    // ── ANULAR ──────────────────────────────────────────────────────────────
    $('#btn-anular-ver').on('click', function () {
        var facNum  = $(this).data('numero');
        var urlPost = $(this).data('url');

        Swal.fire({
            title: 'Anular factura',
            html: '<p>¿Está seguro de anular la factura</p>' +
                  '<p><strong>' + $('<div>').text(facNum).html() + '</strong>?</p>' +
                  '<p class="text-muted small mb-2">El estado cambiará a ' +
                  '<span class="badge badge-danger">ANULADA</span></p>' +
                  '<input type="text" id="swal-motivo" class="swal2-input" ' +
                  'placeholder="Motivo de anulación (opcional)" maxlength="200">',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-times mr-1"></i> Sí, anular',
            cancelButtonText: 'Cancelar',
            focusCancel: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: urlPost,
                method: 'POST',
                data: { csrf_token: csrfToken, motivo: $('#swal-motivo').val() || '' },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        Toast.fire({ icon: 'success', title: res.message || 'Factura anulada' });
                        setTimeout(function () { location.reload(); }, 1600);
                    } else {
                        Toast.fire({ icon: 'error', title: res.message || 'Error al anular' });
                    }
                },
                error: function () {
                    Toast.fire({ icon: 'error', title: 'Error de comunicación con el servidor' });
                }
            });
        });
    });

    // ── REACTIVAR ────────────────────────────────────────────────────────────
    $('#btn-reactivar-ver').on('click', function () {
        var facNum  = $(this).data('numero');
        var urlPost = $(this).data('url');

        Swal.fire({
            title: 'Reactivar factura',
            html: '<p>¿Está seguro de reactivar la factura</p>' +
                  '<p><strong>' + $('<div>').text(facNum).html() + '</strong>?</p>' +
                  '<p class="text-muted small">El estado volverá a ' +
                  '<span class="badge badge-secondary">BORRADOR</span></p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-redo mr-1"></i> Sí, reactivar',
            cancelButtonText: 'Cancelar',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: urlPost,
                method: 'POST',
                data: { csrf_token: csrfToken },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        Toast.fire({ icon: 'success', title: res.message || 'Factura reactivada' });
                        setTimeout(function () { location.reload(); }, 1600);
                    } else {
                        Toast.fire({ icon: 'error', title: res.message || 'Error al reactivar' });
                    }
                },
                error: function () {
                    Toast.fire({ icon: 'error', title: 'Error de comunicación con el servidor' });
                }
            });
        });
    });

}(jQuery));
</script>
<?php $scripts = ob_get_clean(); ?>
