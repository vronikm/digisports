<?php
/**
 * DigiSports - Vista: Lista de Facturas Electrónicas
 * 
 * @package DigiSports\Views\Facturacion
 */

$title = $title ?? 'Facturas Electrónicas';
$facturas = $facturas ?? [];
$filtros = $filtros ?? [];
$paginacion = $paginacion ?? [];
$resumen = $resumen_estados ?? [];

// Mapeo de estados a badges
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

// Iconos de estados
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
?>

<!-- Encabezado -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                    <?= htmlspecialchars($title) ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?module=facturacion&controller=comprobante&action=index">Facturación</a></li>
                    <li class="breadcrumb-item active">Electrónicas</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Contenido principal -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Alertas de mensajes -->
        <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
        <?php endif; ?>
        
        <!-- Cards de resumen de estados -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format($resumen['AUTORIZADO'] ?? 0) ?></h3>
                        <p>Autorizadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="?module=facturacion&controller=facturaelectronica&action=index&estado=AUTORIZADO" class="small-box-footer">
                        Ver detalles <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format($resumen['PENDIENTE'] ?? 0) ?></h3>
                        <p>Pendientes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <a href="?module=facturacion&controller=facturaelectronica&action=index&estado=PENDIENTE" class="small-box-footer">
                        Ver detalles <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= number_format(($resumen['ERROR'] ?? 0) + ($resumen['NO_AUTORIZADO'] ?? 0) + ($resumen['DEVUELTA'] ?? 0)) ?></h3>
                        <p>Con Errores</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <a href="?module=facturacion&controller=facturaelectronica&action=index&estado=ERROR" class="small-box-footer">
                        Ver detalles <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format($paginacion['total_registros'] ?? 0) ?></h3>
                        <p>Total Facturas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <a href="?module=facturacion&controller=facturaelectronica&action=index" class="small-box-footer">
                        Ver todas <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-2"></i>Filtros
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="get" action="" id="form-filtros">
                    <input type="hidden" name="module" value="facturacion">
                    <input type="hidden" name="controller" value="facturaelectronica">
                    <input type="hidden" name="action" value="index">
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Estado SRI</label>
                                <select name="estado" class="form-control select2">
                                    <option value="">Todos los estados</option>
                                    <option value="PENDIENTE" <?= ($filtros['estado_sri'] ?? '') === 'PENDIENTE' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="GENERADA" <?= ($filtros['estado_sri'] ?? '') === 'GENERADA' ? 'selected' : '' ?>>Generada</option>
                                    <option value="FIRMADA" <?= ($filtros['estado_sri'] ?? '') === 'FIRMADA' ? 'selected' : '' ?>>Firmada</option>
                                    <option value="ENVIADA" <?= ($filtros['estado_sri'] ?? '') === 'ENVIADA' ? 'selected' : '' ?>>Enviada</option>
                                    <option value="AUTORIZADO" <?= ($filtros['estado_sri'] ?? '') === 'AUTORIZADO' ? 'selected' : '' ?>>Autorizada</option>
                                    <option value="NO_AUTORIZADO" <?= ($filtros['estado_sri'] ?? '') === 'NO_AUTORIZADO' ? 'selected' : '' ?>>No Autorizada</option>
                                    <option value="ERROR" <?= ($filtros['estado_sri'] ?? '') === 'ERROR' ? 'selected' : '' ?>>Error</option>
                                    <option value="ANULADA" <?= ($filtros['estado_sri'] ?? '') === 'ANULADA' ? 'selected' : '' ?>>Anulada</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Desde</label>
                                <input type="date" name="fecha_desde" class="form-control" value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Hasta</label>
                                <input type="date" name="fecha_hasta" class="form-control" value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Buscar</label>
                                <input type="text" name="q" class="form-control" placeholder="Clave acceso, RUC, nombre..." value="<?= htmlspecialchars($filtros['busqueda'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                    <a href="?module=facturacion&controller=facturaelectronica&action=index" class="btn btn-secondary">
                                        <i class="fas fa-eraser"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tabla de facturas -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>Lista de Facturas Electrónicas
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" id="btn-verificar-conexion" title="Verificar conexión con SRI">
                        <i class="fas fa-plug"></i> Verificar SRI
                    </button>
                    <button type="button" class="btn btn-sm btn-info" id="btn-info-certificado" title="Ver información del certificado">
                        <i class="fas fa-certificate"></i> Certificado
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <?php if (empty($facturas)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay facturas electrónicas</h5>
                    <p class="text-muted">Las facturas electrónicas aparecerán aquí cuando se emitan</p>
                </div>
                <?php else: ?>
                <table class="table table-hover table-striped">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Estado</th>
                            <th>Autorización</th>
                            <th class="text-center" style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($facturas as $f): ?>
                        <tr>
                            <td><?= $f['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($f['establecimiento'] . '-' . $f['punto_emision'] . '-' . $f['secuencial']) ?></strong>
                                <br>
                                <small class="text-muted" title="Clave de acceso">
                                    <?= substr($f['clave_acceso'], 0, 15) ?>...
                                </small>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($f['fecha_emision'])) ?>
                                <?php if ($f['ambiente'] == '1'): ?>
                                <br><span class="badge badge-secondary">PRUEBAS</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($f['cliente_razon_social']) ?></strong>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($f['cliente_identificacion']) ?></small>
                            </td>
                            <td class="text-right">
                                <strong>$<?= number_format($f['total'], 2) ?></strong>
                                <br>
                                <small class="text-muted">IVA: $<?= number_format($f['iva'], 2) ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?= $estadoBadges[$f['estado_sri']] ?? 'secondary' ?>">
                                    <i class="fas <?= $estadoIconos[$f['estado_sri']] ?? 'fa-question' ?> mr-1"></i>
                                    <?= $f['estado_sri'] ?>
                                </span>
                                <?php if (!empty($f['mensaje_error']) && in_array($f['estado_sri'], ['ERROR', 'NO_AUTORIZADO', 'DEVUELTA'])): ?>
                                <br>
                                <small class="text-danger" title="<?= htmlspecialchars($f['mensaje_error']) ?>">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?= htmlspecialchars(substr($f['mensaje_error'], 0, 30)) ?>...
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($f['numero_autorizacion']): ?>
                                <small class="text-success">
                                    <i class="fas fa-check-circle"></i>
                                    <?= date('d/m/Y H:i', strtotime($f['fecha_autorizacion'])) ?>
                                </small>
                                <?php else: ?>
                                <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="?module=facturacion&controller=facturaelectronica&action=ver&id=<?= $f['id'] ?>" class="btn btn-sm btn-info" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if ($f['estado_sri'] === 'AUTORIZADO'): ?>
                                    <a href="?module=facturacion&controller=facturaelectronica&action=descargarRIDE&id=<?= $f['id'] ?>" class="btn btn-sm btn-success" title="Descargar RIDE" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <a href="?module=facturacion&controller=facturaelectronica&action=descargarXML&id=<?= $f['id'] ?>&tipo=autorizado" class="btn btn-sm btn-secondary" title="Descargar XML">
                                        <i class="fas fa-file-code"></i>
                                    </a>
                                    <?php elseif (in_array($f['estado_sri'], ['ERROR', 'NO_AUTORIZADO', 'DEVUELTA'])): ?>
                                    <button type="button" class="btn btn-sm btn-warning btn-reenviar" data-id="<?= $f['id'] ?>" title="Reenviar al SRI">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-consultar" data-clave="<?= $f['clave_acceso'] ?>" title="Consultar estado en SRI">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($facturas) && ($paginacion['total_paginas'] ?? 1) > 1): ?>
            <div class="card-footer clearfix">
                <div class="float-left">
                    <span class="text-muted">
                        Mostrando <?= count($facturas) ?> de <?= $paginacion['total_registros'] ?> registros
                    </span>
                </div>
                <ul class="pagination pagination-sm m-0 float-right">
                    <?php if ($paginacion['pagina'] > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?module=facturacion&controller=facturaelectronica&action=index&pagina=<?= $paginacion['pagina'] - 1 ?>&estado=<?= $filtros['estado_sri'] ?? '' ?>&fecha_desde=<?= $filtros['fecha_desde'] ?? '' ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?? '' ?>&q=<?= $filtros['busqueda'] ?? '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php 
                    $inicio = max(1, $paginacion['pagina'] - 2);
                    $fin = min($paginacion['total_paginas'], $paginacion['pagina'] + 2);
                    for ($i = $inicio; $i <= $fin; $i++): 
                    ?>
                    <li class="page-item <?= $i == $paginacion['pagina'] ? 'active' : '' ?>">
                        <a class="page-link" href="?module=facturacion&controller=facturaelectronica&action=index&pagina=<?= $i ?>&estado=<?= $filtros['estado_sri'] ?? '' ?>&fecha_desde=<?= $filtros['fecha_desde'] ?? '' ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?? '' ?>&q=<?= $filtros['busqueda'] ?? '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($paginacion['pagina'] < $paginacion['total_paginas']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?module=facturacion&controller=facturaelectronica&action=index&pagina=<?= $paginacion['pagina'] + 1 ?>&estado=<?= $filtros['estado_sri'] ?? '' ?>&fecha_desde=<?= $filtros['fecha_desde'] ?? '' ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?? '' ?>&q=<?= $filtros['busqueda'] ?? '' ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        
    </div>
</section>

<!-- Modal para consultar estado -->
<div class="modal fade" id="modal-estado-sri" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-search mr-2"></i>Consulta Estado SRI
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="resultado-consulta">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2">Consultando al SRI...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal información certificado -->
<div class="modal fade" id="modal-certificado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white">
                    <i class="fas fa-certificate mr-2"></i>Certificado Digital
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="info-certificado">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
                    <p class="mt-2">Cargando información...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?= BASE_URL ?>';
    const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    
    // Consultar estado en SRI
    document.querySelectorAll('.btn-consultar').forEach(btn => {
        btn.addEventListener('click', function() {
            const claveAcceso = this.dataset.clave;
            $('#modal-estado-sri').modal('show');
            
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
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>Error de conexión: ${error.message}</div>
                    `;
                });
        });
    });
    
    // Reenviar al SRI
    document.querySelectorAll('.btn-reenviar').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            if (!confirm('¿Desea reenviar esta factura al SRI?')) return;
            
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            fetch(`${baseUrl}?module=facturacion&controller=facturaelectronica&action=reenviar&id=${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csrf_token=${csrfToken}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    toastr.success(data.mensaje || 'Factura autorizada');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(data.mensaje || 'Error al reenviar');
                    this.innerHTML = '<i class="fas fa-sync"></i>';
                    this.disabled = false;
                }
            })
            .catch(error => {
                toastr.error('Error de conexión');
                this.innerHTML = '<i class="fas fa-sync"></i>';
                this.disabled = false;
            });
        });
    });
    
    // Verificar conexión SRI
    document.getElementById('btn-verificar-conexion').addEventListener('click', function() {
        const btn = this;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
        btn.disabled = true;
        
        fetch(`${baseUrl}?module=facturacion&controller=facturaelectronica&action=verificarConexion`)
            .then(response => response.json())
            .then(data => {
                if (data.exito && data.datos.conectividad.recepcion && data.datos.conectividad.autorizacion) {
                    toastr.success(`Conexión exitosa - Ambiente: ${data.datos.ambiente}`);
                } else {
                    toastr.warning('Hay problemas de conectividad con el SRI');
                }
                btn.innerHTML = '<i class="fas fa-plug"></i> Verificar SRI';
                btn.disabled = false;
            })
            .catch(error => {
                toastr.error('Error al verificar conexión');
                btn.innerHTML = '<i class="fas fa-plug"></i> Verificar SRI';
                btn.disabled = false;
            });
    });
    
    // Info certificado
    document.getElementById('btn-info-certificado').addEventListener('click', function() {
        $('#modal-certificado').modal('show');
        
        fetch(`${baseUrl}?module=facturacion&controller=facturaelectronica&action=infoCertificado`)
            .then(response => response.json())
            .then(data => {
                let html = '';
                if (data.exito) {
                    const cert = data.datos;
                    const vigente = cert.vigente ? 'success' : 'danger';
                    html = `
                        <div class="alert alert-${vigente}">
                            <i class="fas fa-${cert.vigente ? 'check-circle' : 'exclamation-triangle'} mr-2"></i>
                            ${cert.vigente ? 'Certificado vigente' : 'Certificado expirado o próximo a vencer'}
                        </div>
                        <table class="table table-sm">
                            <tr><th>Propietario</th><td>${cert.propietario || '-'}</td></tr>
                            <tr><th>Emisor</th><td>${cert.emisor || '-'}</td></tr>
                            <tr><th>Serial</th><td><code>${cert.serial || '-'}</code></td></tr>
                            <tr><th>Válido desde</th><td>${cert.valido_desde || '-'}</td></tr>
                            <tr><th>Válido hasta</th><td>${cert.valido_hasta || '-'}</td></tr>
                            <tr><th>Días restantes</th><td><strong class="text-${cert.dias_restantes < 30 ? 'danger' : 'success'}">${cert.dias_restantes || '?'} días</strong></td></tr>
                        </table>
                    `;
                } else {
                    html = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>${data.mensaje || 'Error al leer certificado'}</div>`;
                }
                document.getElementById('info-certificado').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('info-certificado').innerHTML = `
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>Error: ${error.message}</div>
                `;
            });
    });
});
</script>
