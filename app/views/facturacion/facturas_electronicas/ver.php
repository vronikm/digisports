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
$numeroFactura = $factura['establecimiento'] . '-' . $factura['punto_emision'] . '-' . $factura['secuencial'];

// Mapeo de estados
$estadoBadges = [
    'PENDIENTE' => 'warning',
    'GENERADA' => 'info',
    'FIRMADA' => 'info',
    'ENVIADA' => 'primary',
    'RECIBIDA' => 'primary',
    'DEVUELTA' => 'danger',
    'AUTORIZADO' => 'success',
    'NO_AUTORIZADO' => 'danger',
    'ERROR' => 'danger',
    'ANULADA' => 'secondary',
];

$estadoIconos = [
    'PENDIENTE' => 'fa-clock',
    'GENERADA' => 'fa-file-code',
    'FIRMADA' => 'fa-signature',
    'ENVIADA' => 'fa-paper-plane',
    'RECIBIDA' => 'fa-inbox',
    'DEVUELTA' => 'fa-undo',
    'AUTORIZADO' => 'fa-check-circle',
    'NO_AUTORIZADO' => 'fa-times-circle',
    'ERROR' => 'fa-exclamation-triangle',
    'ANULADA' => 'fa-ban',
];

// Mapeo de tipos de identificación
$tiposIdentificacion = [
    '04' => 'RUC',
    '05' => 'Cédula',
    '06' => 'Pasaporte',
    '07' => 'Consumidor Final',
    '08' => 'Identificación del Exterior',
];
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
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?module=facturacion&controller=facturaelectronica&action=index">Facturas Electrónicas</a></li>
                    <li class="breadcrumb-item active"><?= $numeroFactura ?></li>
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
                <div class="card card-outline card-<?= $estadoBadges[$factura['estado_sri']] ?? 'secondary' ?>">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="mr-4">
                                        <span class="badge badge-<?= $estadoBadges[$factura['estado_sri']] ?? 'secondary' ?> badge-lg p-3" style="font-size: 1.5rem;">
                                            <i class="fas <?= $estadoIconos[$factura['estado_sri']] ?? 'fa-question' ?> mr-2"></i>
                                            <?= $factura['estado_sri'] ?>
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="mb-0">$<?= number_format($factura['total'], 2) ?></h4>
                                        <small class="text-muted">Emitida: <?= date('d/m/Y', strtotime($factura['fecha_emision'])) ?></small>
                                        <?php if ($factura['ambiente'] == '1'): ?>
                                        <span class="badge badge-secondary ml-2">AMBIENTE PRUEBAS</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($factura['mensaje_error'])): ?>
                                <div class="alert alert-danger mt-3 mb-0">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <strong>Error:</strong> <?= htmlspecialchars($factura['mensaje_error']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="btn-group">
                                    <?php if ($factura['estado_sri'] === 'AUTORIZADO'): ?>
                                    <a href="?module=facturacion&controller=facturaelectronica&action=descargarRIDE&id=<?= $factura['id'] ?>" 
                                       class="btn btn-success" target="_blank">
                                        <i class="fas fa-file-pdf mr-2"></i>Descargar RIDE
                                    </a>
                                    <a href="?module=facturacion&controller=facturaelectronica&action=descargarXML&id=<?= $factura['id'] ?>&tipo=autorizado" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-file-code mr-2"></i>Descargar XML
                                    </a>
                                    <?php elseif (in_array($factura['estado_sri'], ['ERROR', 'NO_AUTORIZADO', 'DEVUELTA'])): ?>
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
            <!-- Información de la factura -->
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
                                    <code style="font-size: 0.75rem; word-break: break-all;"><?= htmlspecialchars($factura['clave_acceso']) ?></code>
                                    <button class="btn btn-xs btn-outline-secondary ml-2" onclick="navigator.clipboard.writeText('<?= $factura['clave_acceso'] ?>')" title="Copiar">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha de Emisión</th>
                                <td><?= date('d/m/Y', strtotime($factura['fecha_emision'])) ?></td>
                            </tr>
                            <tr>
                                <th>Ambiente</th>
                                <td>
                                    <?= $factura['ambiente'] == '1' ? '<span class="badge badge-secondary">PRUEBAS</span>' : '<span class="badge badge-success">PRODUCCIÓN</span>' ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Tipo de Emisión</th>
                                <td>
                                    <?= $factura['tipo_emision'] == '1' ? 'Normal' : 'Contingencia' ?>
                                </td>
                            </tr>
                            <?php if ($factura['numero_autorizacion']): ?>
                            <tr>
                                <th>N° Autorización</th>
                                <td>
                                    <code style="word-break: break-all;"><?= htmlspecialchars($factura['numero_autorizacion']) ?></code>
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha Autorización</th>
                                <td class="text-success">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <?= date('d/m/Y H:i:s', strtotime($factura['fecha_autorizacion'])) ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Intentos de Envío</th>
                                <td><?= $factura['intentos_envio'] ?></td>
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
                                <td><?= $tiposIdentificacion[$factura['cliente_tipo_identificacion']] ?? $factura['cliente_tipo_identificacion'] ?></td>
                            </tr>
                            <tr>
                                <th>Identificación</th>
                                <td><strong><?= htmlspecialchars($factura['cliente_identificacion']) ?></strong></td>
                            </tr>
                            <tr>
                                <th>Razón Social</th>
                                <td><?= htmlspecialchars($factura['cliente_razon_social']) ?></td>
                            </tr>
                            <?php if (!empty($factura['cliente_direccion'])): ?>
                            <tr>
                                <th>Dirección</th>
                                <td><?= htmlspecialchars($factura['cliente_direccion']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($factura['cliente_email'])): ?>
                            <tr>
                                <th>Email</th>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($factura['cliente_email']) ?>">
                                        <?= htmlspecialchars($factura['cliente_email']) ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($factura['cliente_telefono'])): ?>
                            <tr>
                                <th>Teléfono</th>
                                <td><?= htmlspecialchars($factura['cliente_telefono']) ?></td>
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
                            <?php if ($factura['subtotal_iva'] > 0): ?>
                            <tr>
                                <th>Subtotal IVA</th>
                                <td class="text-right">$<?= number_format($factura['subtotal_iva'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($factura['subtotal_0'] > 0): ?>
                            <tr>
                                <th>Subtotal 0%</th>
                                <td class="text-right">$<?= number_format($factura['subtotal_0'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($factura['subtotal_no_objeto'] > 0): ?>
                            <tr>
                                <th>Subtotal No Objeto IVA</th>
                                <td class="text-right">$<?= number_format($factura['subtotal_no_objeto'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($factura['subtotal_exento'] > 0): ?>
                            <tr>
                                <th>Subtotal Exento</th>
                                <td class="text-right">$<?= number_format($factura['subtotal_exento'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Subtotal sin Impuestos</th>
                                <td class="text-right">$<?= number_format($factura['subtotal'], 2) ?></td>
                            </tr>
                            <?php if ($factura['descuento'] > 0): ?>
                            <tr>
                                <th>Descuento</th>
                                <td class="text-right text-danger">-$<?= number_format($factura['descuento'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>IVA</th>
                                <td class="text-right">$<?= number_format($factura['iva'], 2) ?></td>
                            </tr>
                            <?php if ($factura['ice'] > 0): ?>
                            <tr>
                                <th>ICE</th>
                                <td class="text-right">$<?= number_format($factura['ice'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($factura['irbpnr'] > 0): ?>
                            <tr>
                                <th>IRBPNR</th>
                                <td class="text-right">$<?= number_format($factura['irbpnr'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($factura['propina'] > 0): ?>
                            <tr>
                                <th>Propina</th>
                                <td class="text-right">$<?= number_format($factura['propina'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="bg-success text-white">
                                <th>TOTAL</th>
                                <td class="text-right"><strong>$<?= number_format($factura['total'], 2) ?></strong></td>
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
                            <?php if (!empty($factura['xml_generado'])): ?>
                            <a href="?module=facturacion&controller=facturaelectronica&action=descargarXML&id=<?= $factura['id'] ?>&tipo=generado" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-file-code text-info mr-2"></i>
                                    XML Generado
                                </span>
                                <span class="badge badge-info">
                                    <i class="fas fa-download"></i>
                                </span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($factura['xml_firmado'])): ?>
                            <a href="?module=facturacion&controller=facturaelectronica&action=descargarXML&id=<?= $factura['id'] ?>&tipo=firmado" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-signature text-primary mr-2"></i>
                                    XML Firmado
                                </span>
                                <span class="badge badge-primary">
                                    <i class="fas fa-download"></i>
                                </span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($factura['xml_autorizado'])): ?>
                            <a href="?module=facturacion&controller=facturaelectronica&action=descargarXML&id=<?= $factura['id'] ?>&tipo=autorizado" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                    XML Autorizado
                                </span>
                                <span class="badge badge-success">
                                    <i class="fas fa-download"></i>
                                </span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($factura['estado_sri'] === 'AUTORIZADO'): ?>
                            <a href="?module=facturacion&controller=facturaelectronica&action=descargarRIDE&id=<?= $factura['id'] ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center bg-success text-white"
                               target="_blank">
                                <span>
                                    <i class="fas fa-file-pdf mr-2"></i>
                                    RIDE (Representación Impresa)
                                </span>
                                <span class="badge badge-light">
                                    <i class="fas fa-external-link-alt"></i>
                                </span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Timeline de estados -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history mr-2"></i>Línea de Tiempo
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline timeline-inverse">
                            <!-- Creación -->
                            <div class="time-label">
                                <span class="bg-info"><?= date('d/m/Y', strtotime($factura['created_at'])) ?></span>
                            </div>
                            <div>
                                <i class="fas fa-plus bg-info"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> <?= date('H:i:s', strtotime($factura['created_at'])) ?></span>
                                    <h3 class="timeline-header">Factura generada</h3>
                                    <div class="timeline-body">
                                        Clave de acceso: <?= $factura['clave_acceso'] ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($factura['estado_sri'] === 'AUTORIZADO' && $factura['fecha_autorizacion']): ?>
                            <div class="time-label">
                                <span class="bg-success"><?= date('d/m/Y', strtotime($factura['fecha_autorizacion'])) ?></span>
                            </div>
                            <div>
                                <i class="fas fa-check bg-success"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> <?= date('H:i:s', strtotime($factura['fecha_autorizacion'])) ?></span>
                                    <h3 class="timeline-header">Autorizada por el SRI</h3>
                                    <div class="timeline-body">
                                        N° Autorización: <?= $factura['numero_autorizacion'] ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (in_array($factura['estado_sri'], ['ERROR', 'NO_AUTORIZADO', 'DEVUELTA'])): ?>
                            <div>
                                <i class="fas fa-exclamation-triangle bg-danger"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> <?= date('H:i:s', strtotime($factura['updated_at'])) ?></span>
                                    <h3 class="timeline-header bg-danger text-white">Error en el proceso</h3>
                                    <div class="timeline-body">
                                        <?= htmlspecialchars($factura['mensaje_error'] ?? 'Sin mensaje de error') ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div>
                                <i class="fas fa-clock bg-gray"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botón volver -->
        <div class="row">
            <div class="col-12">
                <a href="?module=facturacion&controller=facturaelectronica&action=index" class="btn btn-secondary">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?= BASE_URL ?>';
    const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    const claveAcceso = '<?= $factura['clave_acceso'] ?>';
    const facturaId = '<?= $factura['id'] ?>';
    
    // Consultar SRI
    document.getElementById('btn-consultar').addEventListener('click', function() {
        $('#modal-consulta').modal('show');
        
        fetch(`${baseUrl}?module=facturacion&controller=facturaelectronica&action=consultarEstado&clave_acceso=${claveAcceso}`)
            .then(response => response.json())
            .then(data => {
                let html = '';
                if (data.exito) {
                    const resultado = data.datos;
                    html = `
                        <div class="alert alert-${resultado.estado === 'AUTORIZADO' ? 'success' : 'warning'}">
                            <strong>Estado:</strong> ${resultado.estado || 'Sin información'}
                        </div>
                        <table class="table table-sm table-bordered">
                            <tr><th>Clave de Acceso</th><td><code>${claveAcceso}</code></td></tr>
                            <tr><th>Número Autorización</th><td>${resultado.numero_autorizacion || '-'}</td></tr>
                            <tr><th>Fecha Autorización</th><td>${resultado.fecha_autorizacion || '-'}</td></tr>
                            <tr><th>Ambiente</th><td>${resultado.ambiente === '1' ? 'PRUEBAS' : 'PRODUCCIÓN'}</td></tr>
                        </table>
                    `;
                    
                    if (resultado.mensajes && resultado.mensajes.length > 0) {
                        html += '<h6 class="mt-3">Mensajes del SRI:</h6><ul class="list-group">';
                        resultado.mensajes.forEach(msg => {
                            html += `<li class="list-group-item list-group-item-${msg.tipo === 'ERROR' ? 'danger' : 'info'}">
                                <strong>${msg.identificador}:</strong> ${msg.mensaje}
                                ${msg.informacion_adicional ? '<br><small>' + msg.informacion_adicional + '</small>' : ''}
                            </li>`;
                        });
                        html += '</ul>';
                    }
                } else {
                    html = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>${data.mensaje || 'Error al consultar'}</div>`;
                }
                document.getElementById('resultado-consulta').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('resultado-consulta').innerHTML = `
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>Error: ${error.message}</div>
                `;
            });
    });
    
    // Reenviar al SRI
    const btnReenviar = document.getElementById('btn-reenviar');
    if (btnReenviar) {
        btnReenviar.addEventListener('click', function() {
            if (!confirm('¿Desea reenviar esta factura al SRI?')) return;
            
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Reenviando...';
            this.disabled = true;
            
            fetch(`${baseUrl}?module=facturacion&controller=facturaelectronica&action=reenviar&id=${facturaId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csrf_token=${csrfToken}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    toastr.success(data.mensaje || 'Factura procesada');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(data.mensaje || 'Error al reenviar');
                    this.innerHTML = '<i class="fas fa-sync mr-2"></i>Reenviar al SRI';
                    this.disabled = false;
                }
            })
            .catch(error => {
                toastr.error('Error de conexión');
                this.innerHTML = '<i class="fas fa-sync mr-2"></i>Reenviar al SRI';
                this.disabled = false;
            });
        });
    }
});
</script>
