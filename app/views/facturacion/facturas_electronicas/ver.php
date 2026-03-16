<?php
/**
 * DigiSports - Vista: Detalle de Factura Electrónica
 *
 * @package DigiSports\Views\Facturacion
 */

$factura = $factura ?? null;
$title = $title ?? 'Detalle Factura Electrónica';

if (!$factura) {
    echo '<div class="alert alert-danger">Factura no encontrada</div>';
    return;
}

// Número completo de factura
$numeroFactura = $factura['fac_establecimiento'] . '-' . $factura['fac_punto_emision'] . '-' . $factura['fac_secuencial'];

// Mapeo de estados
$estadoBadges = [
    'PENDIENTE'     => 'warning',
    'GENERADA'      => 'info',
    'FIRMADA'       => 'info',
    'ENVIADA'       => 'primary',
    'RECIBIDA'      => 'primary',
    'DEVUELTA'      => 'danger',
    'AUTORIZADO'    => 'success',
    'NO_AUTORIZADO' => 'danger',
    'ERROR'         => 'danger',
    'ANULADA'       => 'secondary',
];

$estadoIconos = [
    'PENDIENTE'     => 'fa-clock',
    'GENERADA'      => 'fa-file-code',
    'FIRMADA'       => 'fa-signature',
    'ENVIADA'       => 'fa-paper-plane',
    'RECIBIDA'      => 'fa-inbox',
    'DEVUELTA'      => 'fa-undo',
    'AUTORIZADO'    => 'fa-check-circle',
    'NO_AUTORIZADO' => 'fa-times-circle',
    'ERROR'         => 'fa-exclamation-triangle',
    'ANULADA'       => 'fa-ban',
];

$tiposIdentificacion = [
    '04' => 'RUC',
    '05' => 'Cédula',
    '06' => 'Pasaporte',
    '07' => 'Consumidor Final',
    '08' => 'Identificación del Exterior',
];

$estadoSRI = $factura['fac_estado_sri'] ?? 'GENERADA';
$colorCard  = $estadoBadges[$estadoSRI] ?? 'secondary';
?>

<!-- Encabezado -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                    Factura Electrónica <?= htmlspecialchars($numeroFactura) ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('facturacion', 'dashboard', 'index') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('facturacion', 'factura_electronica', 'index') ?>">Facturas Electrónicas</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($numeroFactura) ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Contenido principal -->
<section class="content">
    <div class="container-fluid">

        <!-- Estado actual -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-<?= $colorCard ?>">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="mr-4">
                                        <span class="badge badge-<?= $colorCard ?> badge-lg p-3" style="font-size: 1.5rem;">
                                            <i class="fas <?= $estadoIconos[$estadoSRI] ?? 'fa-question' ?> mr-2"></i>
                                            <?= htmlspecialchars($estadoSRI) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="mb-0">$<?= number_format($factura['fac_total'] ?? 0, 2) ?></h4>
                                        <small class="text-muted">Emitida: <?= date('d/m/Y', strtotime($factura['fac_fecha_emision'])) ?></small>
                                        <?php if (($factura['fac_ambiente'] ?? '') == '1'): ?>
                                        <span class="badge badge-secondary ml-2">AMBIENTE PRUEBAS</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!empty($factura['fac_mensaje_error'])): ?>
                                <div class="alert alert-danger mt-3 mb-0">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <strong>Error:</strong> <?= htmlspecialchars($factura['fac_mensaje_error']) ?>
                                </div>
                                <?php endif; ?>

                                <?php if ($estadoSRI === 'ENVIADA'): ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-clock mr-2"></i>
                                    El SRI está procesando el comprobante. Use <strong>Consultar SRI</strong> para verificar la autorización.
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="btn-group">
                                    <?php if ($estadoSRI === 'AUTORIZADO'): ?>
                                    <a href="<?= url('facturacion', 'factura_electronica', 'descargarRIDE', ['id' => $factura['fac_id']]) ?>"
                                       class="btn btn-success" target="_blank">
                                        <i class="fas fa-file-pdf mr-2"></i>Descargar RIDE
                                    </a>
                                    <a href="<?= url('facturacion', 'factura_electronica', 'descargarXML', ['id' => $factura['fac_id'], 'tipo' => 'autorizado']) ?>"
                                       class="btn btn-secondary">
                                        <i class="fas fa-file-code mr-2"></i>Descargar XML
                                    </a>
                                    <?php elseif (in_array($estadoSRI, ['ERROR', 'NO_AUTORIZADO', 'DEVUELTA'])): ?>
                                    <button type="button" class="btn btn-warning" id="btn-reenviar">
                                        <i class="fas fa-sync mr-2"></i>Reenviar al SRI
                                    </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-info" id="btn-consultar">
                                        <i class="fas fa-search mr-2"></i>Consultar SRI
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Información del comprobante -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle mr-2"></i>Información del Comprobante
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <tr>
                                <th width="40%">Número de Factura</th>
                                <td><strong><?= htmlspecialchars($numeroFactura) ?></strong></td>
                            </tr>
                            <tr>
                                <th>Clave de Acceso</th>
                                <td>
                                    <code style="font-size: 0.75rem; word-break: break-all;"><?= htmlspecialchars($factura['fac_clave_acceso']) ?></code>
                                    <button class="btn btn-xs btn-outline-secondary ml-2"
                                            onclick="navigator.clipboard.writeText(<?= json_encode($factura['fac_clave_acceso']) ?>)"
                                            title="Copiar">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha de Emisión</th>
                                <td><?= date('d/m/Y', strtotime($factura['fac_fecha_emision'])) ?></td>
                            </tr>
                            <tr>
                                <th>Ambiente</th>
                                <td>
                                    <?= ($factura['fac_ambiente'] ?? '') == '1'
                                        ? '<span class="badge badge-secondary">PRUEBAS</span>'
                                        : '<span class="badge badge-success">PRODUCCIÓN</span>' ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Tipo de Emisión</th>
                                <td><?= ($factura['fac_tipo_emision'] ?? '') == '1' ? 'Normal' : 'Contingencia' ?></td>
                            </tr>
                            <?php if (!empty($factura['fac_numero_autorizacion'])): ?>
                            <tr>
                                <th>N° Autorización</th>
                                <td><code style="word-break: break-all;"><?= htmlspecialchars($factura['fac_numero_autorizacion']) ?></code></td>
                            </tr>
                            <tr>
                                <th>Fecha Autorización</th>
                                <td class="text-success">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <?= date('d/m/Y H:i:s', strtotime($factura['fac_fecha_autorizacion'])) ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Intentos de Envío</th>
                                <td><?= (int) ($factura['fac_intentos_envio'] ?? 0) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Información del cliente -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info">
                        <h3 class="card-title">
                            <i class="fas fa-user mr-2"></i>Información del Cliente
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <tr>
                                <th width="40%">Tipo Identificación</th>
                                <td><?= $tiposIdentificacion[$factura['fac_cliente_tipo_identificacion'] ?? ''] ?? ($factura['fac_cliente_tipo_identificacion'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Identificación</th>
                                <td><strong><?= htmlspecialchars($factura['fac_cliente_identificacion'] ?? '') ?></strong></td>
                            </tr>
                            <tr>
                                <th>Razón Social</th>
                                <td><?= htmlspecialchars($factura['fac_cliente_razon_social'] ?? '') ?></td>
                            </tr>
                            <?php if (!empty($factura['fac_cliente_direccion'])): ?>
                            <tr>
                                <th>Dirección</th>
                                <td><?= htmlspecialchars($factura['fac_cliente_direccion']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($factura['fac_cliente_email'])): ?>
                            <tr>
                                <th>Email</th>
                                <td><a href="mailto:<?= htmlspecialchars($factura['fac_cliente_email']) ?>"><?= htmlspecialchars($factura['fac_cliente_email']) ?></a></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Valores -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success">
                        <h3 class="card-title">
                            <i class="fas fa-dollar-sign mr-2"></i>Valores del Comprobante
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <?php if (($factura['fac_subtotal_iva'] ?? 0) > 0): ?>
                            <tr><th>Subtotal IVA</th><td class="text-right">$<?= number_format($factura['fac_subtotal_iva'], 2) ?></td></tr>
                            <?php endif; ?>
                            <?php if (($factura['fac_subtotal_0'] ?? 0) > 0): ?>
                            <tr><th>Subtotal 0%</th><td class="text-right">$<?= number_format($factura['fac_subtotal_0'], 2) ?></td></tr>
                            <?php endif; ?>
                            <tr><th>Subtotal sin Impuestos</th><td class="text-right">$<?= number_format($factura['fac_subtotal'] ?? 0, 2) ?></td></tr>
                            <?php if (($factura['fac_descuento'] ?? 0) > 0): ?>
                            <tr><th>Descuento</th><td class="text-right text-danger">-$<?= number_format($factura['fac_descuento'], 2) ?></td></tr>
                            <?php endif; ?>
                            <tr><th>IVA</th><td class="text-right">$<?= number_format($factura['fac_iva'] ?? 0, 2) ?></td></tr>
                            <tr class="bg-success text-white">
                                <th>TOTAL</th>
                                <td class="text-right"><strong>$<?= number_format($factura['fac_total'] ?? 0, 2) ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Archivos XML -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-secondary">
                        <h3 class="card-title">
                            <i class="fas fa-file-code mr-2"></i>Archivos XML
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php if (!empty($factura['fac_xml_generado'])): ?>
                            <a href="<?= url('facturacion', 'factura_electronica', 'descargarXML', ['id' => $factura['fac_id'], 'tipo' => 'generado']) ?>"
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-file-code text-info mr-2"></i>XML Generado</span>
                                <span class="badge badge-info"><i class="fas fa-download"></i></span>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($factura['fac_xml_firmado'])): ?>
                            <a href="<?= url('facturacion', 'factura_electronica', 'descargarXML', ['id' => $factura['fac_id'], 'tipo' => 'firmado']) ?>"
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-signature text-primary mr-2"></i>XML Firmado</span>
                                <span class="badge badge-primary"><i class="fas fa-download"></i></span>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($factura['fac_xml_autorizado'])): ?>
                            <a href="<?= url('facturacion', 'factura_electronica', 'descargarXML', ['id' => $factura['fac_id'], 'tipo' => 'autorizado']) ?>"
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-check-circle text-success mr-2"></i>XML Autorizado</span>
                                <span class="badge badge-success"><i class="fas fa-download"></i></span>
                            </a>
                            <?php endif; ?>
                            <?php if ($estadoSRI === 'AUTORIZADO'): ?>
                            <a href="<?= url('facturacion', 'factura_electronica', 'descargarRIDE', ['id' => $factura['fac_id']]) ?>"
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center bg-success text-white"
                               target="_blank">
                                <span><i class="fas fa-file-pdf mr-2"></i>RIDE (Representación Impresa)</span>
                                <span class="badge badge-light"><i class="fas fa-external-link-alt"></i></span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history mr-2"></i>Línea de Tiempo</h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline timeline-inverse">
                            <div class="time-label">
                                <span class="bg-info"><?= date('d/m/Y', strtotime($factura['fac_created_at'])) ?></span>
                            </div>
                            <div>
                                <i class="fas fa-plus bg-info"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> <?= date('H:i:s', strtotime($factura['fac_created_at'])) ?></span>
                                    <h3 class="timeline-header">Factura generada</h3>
                                    <div class="timeline-body">Clave de acceso: <?= htmlspecialchars($factura['fac_clave_acceso']) ?></div>
                                </div>
                            </div>

                            <?php if ($estadoSRI === 'AUTORIZADO' && !empty($factura['fac_fecha_autorizacion'])): ?>
                            <div class="time-label">
                                <span class="bg-success"><?= date('d/m/Y', strtotime($factura['fac_fecha_autorizacion'])) ?></span>
                            </div>
                            <div>
                                <i class="fas fa-check bg-success"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> <?= date('H:i:s', strtotime($factura['fac_fecha_autorizacion'])) ?></span>
                                    <h3 class="timeline-header">Autorizada por el SRI</h3>
                                    <div class="timeline-body">N° Autorización: <?= htmlspecialchars($factura['fac_numero_autorizacion']) ?></div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (in_array($estadoSRI, ['ERROR', 'NO_AUTORIZADO', 'DEVUELTA'])): ?>
                            <div>
                                <i class="fas fa-exclamation-triangle bg-danger"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> <?= date('H:i:s', strtotime($factura['fac_updated_at'])) ?></span>
                                    <h3 class="timeline-header bg-danger text-white">Error en el proceso</h3>
                                    <div class="timeline-body"><?= htmlspecialchars($factura['fac_mensaje_error'] ?? 'Sin mensaje de error') ?></div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div><i class="fas fa-clock bg-gray"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón volver -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="<?= url('facturacion', 'factura_electronica', 'index') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al listado
                </a>
            </div>
        </div>

    </div>
</section>

<!-- Modal consulta SRI -->
<div class="modal fade" id="modal-consulta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white">
                    <i class="fas fa-search mr-2"></i>Consulta al SRI
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="resultado-consulta">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
                    <p class="mt-2">Consultando al SRI...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
(function () {
    'use strict';

    var urlConsultar = <?= json_encode(url('facturacion', 'factura_electronica', 'consultarEstado')) ?>;
    var urlReenviar  = <?= json_encode(url('facturacion', 'factura_electronica', 'reenviar')) ?>;
    var claveAcceso  = <?= json_encode($factura['fac_clave_acceso']) ?>;
    var facturaId    = <?= (int) $factura['fac_id'] ?>;
    var csrfToken    = <?= json_encode(\Security::generateCsrfToken()) ?>;

    // Consultar SRI
    document.getElementById('btn-consultar').addEventListener('click', function () {
        $('#modal-consulta').modal('show');
        document.getElementById('resultado-consulta').innerHTML =
            '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><p class="mt-2">Consultando al SRI...</p></div>';

        fetch(urlConsultar + '&clave_acceso=' + encodeURIComponent(claveAcceso))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var html = '';
                if (data.success) {
                    var auths = (data.data && data.data.autorizaciones) ? data.data.autorizaciones : [];
                    if (auths.length > 0) {
                        var a = auths[0];
                        html = '<div class="alert alert-' + (a.estado === 'AUTORIZADO' ? 'success' : 'warning') + '">' +
                            '<strong>Estado:</strong> ' + (a.estado || 'Sin información') + '</div>' +
                            '<table class="table table-sm table-bordered">' +
                            '<tr><th>Clave de Acceso</th><td><code>' + claveAcceso + '</code></td></tr>' +
                            '<tr><th>Nro. Autorización</th><td>' + (a.numero_autorizacion || '-') + '</td></tr>' +
                            '<tr><th>Fecha Autorización</th><td>' + (a.fecha_autorizacion || '-') + '</td></tr>' +
                            '<tr><th>Ambiente</th><td>' + (a.ambiente === '1' ? 'PRUEBAS' : 'PRODUCCIÓN') + '</td></tr>' +
                            '</table>';
                        if (a.mensajes && a.mensajes.length > 0) {
                            html += '<h6 class="mt-3">Mensajes del SRI:</h6><ul class="list-group">';
                            a.mensajes.forEach(function (msg) {
                                html += '<li class="list-group-item list-group-item-' + (msg.tipo === 'ERROR' ? 'danger' : 'info') + '">' +
                                    '<strong>' + msg.identificador + ':</strong> ' + msg.mensaje +
                                    (msg.informacion_adicional ? '<br><small>' + msg.informacion_adicional + '</small>' : '') +
                                    '</li>';
                            });
                            html += '</ul>';
                        }
                    } else {
                        html = '<div class="alert alert-warning">' +
                            '<i class="fas fa-clock mr-2"></i>' +
                            '<strong>En procesamiento:</strong> El SRI no devolvió autorización todavía. ' +
                            'El comprobante puede estar siendo procesado. Intente nuevamente en unos minutos.' +
                            '</div>' +
                            '<table class="table table-sm table-bordered mt-2">' +
                            '<tr><th>Clave de Acceso</th><td><code>' + claveAcceso + '</code></td></tr>' +
                            '</table>';
                    }
                } else {
                    html = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>' + (data.message || 'Error al consultar') + '</div>';
                }
                document.getElementById('resultado-consulta').innerHTML = html;
            })
            .catch(function (err) {
                document.getElementById('resultado-consulta').innerHTML =
                    '<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>Error: ' + err.message + '</div>';
            });
    });

    // Reenviar al SRI
    var btnReenviar = document.getElementById('btn-reenviar');
    if (btnReenviar) {
        btnReenviar.addEventListener('click', function () {
            var self = this;
            if (!confirm('¿Desea reenviar esta factura al SRI?')) return;

            self.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Reenviando...';
            self.disabled  = true;

            fetch(urlReenviar + '&id=' + facturaId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'csrf_token=' + encodeURIComponent(csrfToken)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    toastr.success(data.message || 'Factura procesada');
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    toastr.error(data.message || 'Error al reenviar');
                    self.innerHTML = '<i class="fas fa-sync mr-2"></i>Reenviar al SRI';
                    self.disabled  = false;
                }
            })
            .catch(function () {
                toastr.error('Error de conexión');
                self.innerHTML = '<i class="fas fa-sync mr-2"></i>Reenviar al SRI';
                self.disabled  = false;
            });
        });
    }
}());
</script>
<?php $scripts = ob_get_clean(); ?>
