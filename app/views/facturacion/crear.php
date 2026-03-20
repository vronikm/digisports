<?php
/**
 * Vista: Nueva Factura
 * AdminLTE 3.2 / Bootstrap 4 — estilo consistente con el resto del sistema.
 */

$cfg           = $config ?? [];
$emisorNombre  = htmlspecialchars($cfg['cfg_razon_social']              ?? 'Sin configurar');
$emisorRuc     = htmlspecialchars($cfg['cfg_ruc']                       ?? '—');
$emisorDir     = htmlspecialchars(($cfg['cfg_direccion_establecimiento'] ?? '') ?: ($cfg['cfg_direccion_matriz'] ?? '—'));
$emisorObl     = $cfg['cfg_obligado_contabilidad']   ?? 'SI';
$emisorAgt     = $cfg['cfg_agente_retencion']        ?? '';
$ambiente      = (int)($cfg['cfg_ambiente']          ?? 1);
$ambienteLabel = $ambiente === 2 ? 'PRODUCCIÓN'                    : 'PRUEBAS';
$ambienteBadge = $ambiente === 2 ? 'badge-success'                 : 'badge-warning';
// $moduloColor viene de ModuleController via extract() — usar CSS var como fallback
$moduloColor ??= 'var(--module-color)';
?>

<!-- ── Cabecera de página ──────────────────────────────────────── -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-file-invoice mr-2" style="color:var(--module-color)"></i>
                    Nueva Factura<?= $origen_modulo !== 'libre' ? ' desde ' . ucfirst(htmlspecialchars($origen_modulo)) : '' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('facturacion','factura','index') ?>"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Contenido principal ────────────────────────────────────── -->
<div class="content">
<div class="container-fluid">

<form id="formFactura">
    <input type="hidden" id="csrf_token"    value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="hidden" id="origen_modulo" value="<?= htmlspecialchars($origen_modulo) ?>">
    <input type="hidden" id="origen_id"     value="<?= (int)$origen_id ?>">

    <div class="row">

        <!-- ══════════════════════════════════════════════
             COLUMNA PRINCIPAL (izquierda)
        ══════════════════════════════════════════════ -->
        <div class="col-lg-8">

            <!-- ── CABECERA TIPO RIDE ──────────────────────── -->
            <div class="card card-outline" style="border-top-color:var(--module-color)">
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" style="font-size:.85rem;">
                        <tbody>
                            <tr>
                                <!-- Emisor -->
                                <td style="width:55%;vertical-align:top;padding:12px 14px;">
                                    <?php if (!empty($cfg['cfg_logo_arc_id'])): ?>
                                    <img src="<?= \Config::baseUrl('archivo.php?id='.(int)$cfg['cfg_logo_arc_id']) ?>"
                                         alt="Logo" style="max-height:55px;max-width:160px;object-fit:contain;display:block;margin-bottom:6px;">
                                    <?php endif; ?>
                                    <strong><?= $emisorNombre ?></strong><br>
                                    <span class="text-muted">RUC:</span> <strong><?= $emisorRuc ?></strong><br>
                                    <span class="text-muted">Dir:</span> <?= $emisorDir ?><br>
                                    <small class="text-muted">
                                        Obligado a llevar contabilidad: <strong><?= $emisorObl ?></strong>
                                        <?php if ($emisorAgt): ?>
                                        &nbsp;|&nbsp; Agente Ret. Res.: <strong><?= htmlspecialchars($emisorAgt) ?></strong>
                                        <?php endif; ?>
                                    </small>
                                    <?php if (empty($cfg['cfg_ruc'])): ?>
                                    <div class="alert alert-warning p-2 mt-2 mb-0" style="font-size:.78rem;">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Configure los datos del emisor en
                                        <a href="<?= url('facturacion','configuracion','index') ?>">Configuración FE</a>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <!-- Identificación de la factura -->
                                <td style="width:45%;vertical-align:top;padding:12px 14px;background:#f8f9fa;">
                                    <div class="text-center text-uppercase font-weight-bold mb-1"
                                         style="font-size:.75rem;color:#555;letter-spacing:.05em;">
                                        Factura
                                    </div>
                                    <div class="text-center mb-2">
                                        <span class="text-primary font-weight-bold"
                                              style="font-size:1.1rem;letter-spacing:.04em;"
                                              id="numFacturaDisplay"><?= htmlspecialchars($num_preview) ?></span>
                                    </div>
                                    <table class="table table-sm table-borderless mb-0" style="font-size:.8rem;">
                                        <tr>
                                            <td class="text-muted py-0" style="width:50%;">Fecha emisión:</td>
                                            <td class="py-0">
                                                <input type="datetime-local" id="fecha_emision"
                                                       class="form-control form-control-sm"
                                                       value="<?= date('Y-m-d\TH:i') ?>" required>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-0">Vencimiento:</td>
                                            <td class="py-0">
                                                <input type="date" id="fecha_vencimiento"
                                                       class="form-control form-control-sm"
                                                       value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-0">Ambiente:</td>
                                            <td class="py-0">
                                                <span class="badge <?= $ambienteBadge ?>"><?= $ambienteLabel ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-0">Emisión:</td>
                                            <td class="py-0 font-weight-bold" style="font-size:.75rem;">NORMAL</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── DATOS DEL ADQUIRENTE ────────────────────── -->
            <div class="card card-outline" style="border-top-color:var(--module-color)">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2" style="color:var(--module-color)"></i>
                        Datos del Adquirente
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Selector de cliente -->
                        <div class="col-md-6">
                            <?php if ($origen_modulo === 'libre'): ?>
                            <!-- Búsqueda por identificación -->
                            <div class="form-group">
                                <label>Identificación / RUC <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" id="buscarIdent" class="form-control"
                                           placeholder="Cédula o RUC del cliente" maxlength="20"
                                           autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary"
                                                id="btnBuscarCliente" title="Buscar cliente">
                                            <i class="fas fa-search" id="iconBuscar"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="buscarStatus" class="mt-1" style="font-size:.82rem;min-height:1.2em;"></div>
                            </div>
                            <!-- ID del cliente seleccionado -->
                            <input type="hidden" id="cliente_id" value="">
                            <!-- Panel de datos: cliente nuevo o cross-tenant -->
                            <div id="panelNuevoCliente" style="display:none;">
                                <div class="border rounded p-2" style="background:#f8f9fa;font-size:.83rem;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong id="panelNuevoClienteTitle">
                                            <i class="fas fa-user-plus mr-1"></i>Datos del cliente
                                        </strong>
                                        <button type="button" class="btn btn-xs btn-outline-secondary"
                                                id="btnCerrarPanel" title="Cerrar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="mb-0">Tipo identificación</label>
                                        <select id="nc_tipo" class="form-control form-control-sm">
                                            <?php foreach ($tipos_identificacion as $t): ?>
                                            <option value="<?= htmlspecialchars($t['stc_codigo']) ?>">
                                                <?= htmlspecialchars($t['stc_etiqueta'] ?: $t['stc_codigo']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-1">
                                                <label class="mb-0">Nombres <span class="text-danger">*</span></label>
                                                <input type="text" id="nc_nombres" class="form-control form-control-sm"
                                                       placeholder="Nombres / Razón Social">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-1">
                                                <label class="mb-0">Apellidos</label>
                                                <input type="text" id="nc_apellidos" class="form-control form-control-sm"
                                                       placeholder="Apellidos">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-1">
                                                <label class="mb-0">Email</label>
                                                <input type="email" id="nc_email" class="form-control form-control-sm"
                                                       placeholder="correo@ejemplo.com">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-1">
                                                <label class="mb-0">Teléfono</label>
                                                <input type="text" id="nc_telefono" class="form-control form-control-sm"
                                                       placeholder="0999999999">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="mb-0">Dirección</label>
                                        <input type="text" id="nc_direccion" class="form-control form-control-sm"
                                               placeholder="Dirección">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-success btn-block"
                                            id="btnGuardarCliente">
                                        <i class="fas fa-user-check mr-1"></i>
                                        <span id="btnGuardarClienteText">Registrar y usar este cliente</span>
                                    </button>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="form-group">
                                <label>Cliente</label>
                                <input type="text" class="form-control" readonly
                                       value="<?= htmlspecialchars($cliente['cli_nombre_comercial'] ?? 'N/A') ?>">
                                <input type="hidden" id="cliente_id" value="<?= htmlspecialchars($cliente_id ?? '') ?>">
                            </div>
                            <?php endif; ?>
                        </div>
                        <!-- Guía de remisión -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Guía de Remisión <span class="text-muted">(opcional)</span></label>
                                <input type="text" id="guia_remision" class="form-control"
                                       placeholder="001-001-000000001">
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta info cliente (se rellena al seleccionar) -->
                    <div id="clienteInfoCard" class="alert alert-success border p-2 mb-0" style="display:none;font-size:.82rem;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="row flex-fill mr-2">
                                <div class="col-sm-6">
                                    <div><span class="text-muted">Cliente:</span> <strong id="ci_nombre"></strong></div>
                                    <div><span class="text-muted">Tipo ID:</span> <strong id="ci_tipo"></strong></div>
                                    <div><span class="text-muted">CI / RUC:</span> <strong id="ci_ident"></strong></div>
                                    <div><span class="text-muted">Email:</span> <span id="ci_email"></span></div>
                                </div>
                                <div class="col-sm-6">
                                    <div><span class="text-muted">Dirección:</span> <span id="ci_dir"></span></div>
                                    <div><span class="text-muted">Teléfono:</span> <span id="ci_tel"></span></div>
                                </div>
                            </div>
                            <a href="#" id="linkCambiarCliente"
                               class="btn btn-xs btn-outline-secondary" title="Cambiar cliente"
                               style="white-space:nowrap;">
                                <i class="fas fa-times"></i> Cambiar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── DETALLES DE LA FACTURA ──────────────────── -->
            <div class="card card-outline" style="border-top-color:var(--module-color)">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2" style="color:var(--module-color)"></i>
                        Detalles
                    </h3>
                    <div class="card-tools d-flex align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-primary mr-2"
                                id="btnCargarItems" title="Cargar rubros o comprobantes de otros subsistemas">
                            <i class="fas fa-layer-group mr-1"></i>Cargar comprobantes / Rubros
                        </button>
                        <span class="badge badge-secondary" id="contadorLineas">0 ítems</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" id="tablaLineas">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width:80px;">Cód.</th>
                                    <th>Descripción</th>
                                    <th style="width:75px;">Período</th>
                                    <th style="width:65px;" class="text-right">Cant.</th>
                                    <th style="width:90px;" class="text-right">P. Unit.</th>
                                    <th style="width:80px;" class="text-right">Desc.</th>
                                    <th style="width:50px;" class="text-center">IVA</th>
                                    <th style="width:90px;" class="text-right">Subtotal</th>
                                    <th style="width:60px;"></th>
                                </tr>
                            </thead>
                            <tbody id="lineasBody">
                                <tr id="trVacio">
                                    <td colspan="9" class="text-center text-muted py-3">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Agregue ítems usando el formulario de abajo
                                    </td>
                                </tr>
                            </tbody>
                            <!-- Fila de ingreso / edición -->
                            <tfoot id="tfootLinea">
                                <tr id="trModoLabel" style="display:none;">
                                    <td colspan="9" class="py-1 px-2" id="modoEditLabel"
                                        style="background:#fff3cd;font-size:.78rem;">
                                        <i class="fas fa-pencil-alt mr-1 text-warning"></i>
                                        <strong>Editando ítem</strong> — modifique los campos y presione
                                        <kbd>Enter</kbd> o haga clic en <strong>Actualizar</strong>.
                                        <button type="button" id="btnCancelarEdicion"
                                                class="btn btn-xs btn-outline-secondary float-right">
                                            <i class="fas fa-times mr-1"></i>Cancelar
                                        </button>
                                    </td>
                                </tr>
                                <tr id="trInputs" class="bg-light">
                                    <td><input type="text" id="nCod" class="form-control form-control-sm text-uppercase"
                                               placeholder="SERV01" maxlength="20" style="min-width:70px;"></td>
                                    <td><input type="text" id="nDesc" class="form-control form-control-sm"
                                               placeholder="Descripción del producto o servicio"></td>
                                    <td><input type="text" id="nPer" class="form-control form-control-sm"
                                               placeholder="Mar-26" style="min-width:65px;"></td>
                                    <td><input type="number" id="nCant" class="form-control form-control-sm text-right"
                                               value="1" min="0.01" step="0.01" style="min-width:55px;"></td>
                                    <td><input type="number" id="nPrecio" class="form-control form-control-sm text-right"
                                               value="0.00" min="0" step="0.01" style="min-width:75px;"></td>
                                    <td><input type="number" id="nDescLin" class="form-control form-control-sm text-right"
                                               value="0.00" min="0" step="0.01" style="min-width:65px;"></td>
                                    <td>
                                        <select id="nIva" class="form-control form-control-sm">
                                            <option value="15">15%</option>
                                            <option value="0">0%</option>
                                            <option value="12">12%</option>
                                        </select>
                                    </td>
                                    <td class="text-right align-middle" id="nPreview"
                                        style="font-weight:600;color:#333;">$0.00</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-success"
                                                id="btnAgregar" title="Agregar (Enter)">
                                            <i class="fas fa-plus" id="btnAgregarIcon"></i>
                                            <span id="btnAgregarText" class="d-none d-xl-inline ml-1">Agregar</span>
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-muted" style="font-size:.78rem;">
                    <i class="fas fa-keyboard mr-1"></i>
                    Presione <kbd>Enter</kbd> en la descripción para agregar el ítem rápidamente &nbsp;|&nbsp;
                    <i class="fas fa-pencil-alt mr-1"></i>Haga clic en <i class="fas fa-pencil-alt"></i> para editar un ítem &nbsp;|&nbsp;
                    <kbd>Esc</kbd> cancela la edición
                </div>
            </div>

            <!-- ── INFORMACIÓN ADICIONAL ───────────────────── -->
            <div class="card card-outline" style="border-top-color:var(--module-color)">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-comment-alt mr-2" style="color:var(--module-color)"></i>
                        Información Adicional / Observaciones
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <textarea id="observaciones" class="form-control" rows="3"
                                  placeholder="Ej: Curso: Inicial 2 | Alumno: Juan Pérez | Código: 20250001"></textarea>
                        <small class="form-text text-muted">
                            Esta información aparece en la sección "Información Adicional" del RIDE.
                        </small>
                    </div>
                </div>
            </div>

        </div><!-- /col-lg-8 -->

        <!-- ══════════════════════════════════════════════
             SIDEBAR DERECHO
        ══════════════════════════════════════════════ -->
        <div class="col-lg-4">

            <!-- ── TOTALES ────────────────────────────────── -->
            <div class="card card-outline" style="border-top-color:var(--module-color)">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calculator mr-2" style="color:var(--module-color)"></i>
                        Valores
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" style="font-size:.85rem;">
                        <tbody>
                            <tr>
                                <td class="text-muted">SUBTOTAL 0%</td>
                                <td class="text-right font-weight-bold" id="subtotal0">$0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted" id="labelSubIva">SUBTOTAL IVA</td>
                                <td class="text-right font-weight-bold" id="subtotalIva">$0.00</td>
                            </tr>
                            <tr class="table-light">
                                <td class="text-muted">SUBTOTAL</td>
                                <td class="text-right font-weight-bold" id="subtotalTotal">$0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted">DESCUENTO</td>
                                <td class="text-right">
                                    <div class="input-group input-group-sm" style="width:110px;margin-left:auto;">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" id="descuento" class="form-control text-right"
                                               value="0.00" min="0" step="0.01" style="width:65px;">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted" id="labelIva">IVA 15%</td>
                                <td class="text-right font-weight-bold" id="ivaDisplay">$0.00</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background:var(--module-color);color:#fff;">
                                <td class="font-weight-bold">VALOR TOTAL</td>
                                <td class="text-right font-weight-bold" id="totalDisplay"
                                    style="font-size:1.05rem;">$0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- ── FORMA DE PAGO ──────────────────────────── -->
            <div class="card card-outline" style="border-top-color:var(--module-color)">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-money-bill-wave mr-2" style="color:var(--module-color)"></i>
                        Forma de Pago
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Medio de Pago <span class="text-danger">*</span></label>
                        <select id="forma_pago_id" class="form-control" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($formas_pago as $fp): ?>
                            <option value="<?= (int)$fp['fpa_id'] ?>"
                                    data-codigo="<?= htmlspecialchars($fp['fpa_codigo_sri']) ?>">
                                <?= htmlspecialchars($fp['fpa_nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>Total a Cobrar</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="text" id="totalPagoDisplay" class="form-control text-right font-weight-bold"
                                   value="0.00" readonly style="background:#f8f9fa;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── ACCIONES ────────────────────────────────── -->
            <div class="card">
                <div class="card-body">
                    <button type="button" id="btnGuardar"
                            class="btn btn-block btn-lg"
                            style="background:var(--module-color);color:white;"
                            disabled>
                        <i class="fas fa-save mr-2"></i>Guardar como Borrador
                    </button>
                    <a href="<?= url('facturacion','factura','index') ?>"
                       class="btn btn-outline-secondary btn-block mt-2">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </a>
                    <small class="form-text text-muted text-center mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        La factura se guarda en estado <strong>BORRADOR</strong>.<br>
                        Desde el detalle podrá emitirla al SRI.
                    </small>
                </div>
            </div>

        </div><!-- /col-lg-4 -->
    </div><!-- /row -->
</form>

</div><!-- /container-fluid -->
</div><!-- /content -->

<!-- ══════════════════════════════════════════════════════════
     MODAL — CARGAR COMPROBANTES / RUBROS
══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalCargarItems" tabindex="-1" role="dialog"
     aria-labelledby="modalCargarItemsLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header" style="background:var(--module-color);color:#fff;">
                <h5 class="modal-title" id="modalCargarItemsLabel">
                    <i class="fas fa-layer-group mr-2"></i>Cargar comprobantes / Rubros
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-0">
                <!-- Tabs -->
                <ul class="nav nav-tabs px-3 pt-2" id="tabsCargar" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-rubros-tab" data-toggle="tab"
                           href="#tab-rubros" role="tab">
                            <i class="fas fa-tags mr-1"></i>Rubros
                            <span class="badge badge-secondary ml-1" id="badgeRubros">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-comprobantes-tab" data-toggle="tab"
                           href="#tab-comprobantes" role="tab">
                            <i class="fas fa-receipt mr-1"></i>Comprobantes pendientes
                            <span class="badge badge-secondary ml-1" id="badgeComprobantes">0</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content px-3 pb-3 pt-2" id="tabsCargarContent">

                    <!-- ── TAB RUBROS ───────────────────────────── -->
                    <div class="tab-pane fade show active" id="tab-rubros" role="tabpanel">
                        <p class="text-muted small mb-2">
                            Seleccione un rubro para agregarlo a la factura. Ajuste cantidad y precio antes de confirmar.
                        </p>
                        <div id="loadingRubros" class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x text-secondary"></i>
                        </div>
                        <div id="listaRubros" style="display:none;">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" id="tblRubros">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width:30px;"></th>
                                            <th style="width:80px;">Código</th>
                                            <th>Nombre</th>
                                            <th style="width:80px;" class="text-center">IVA</th>
                                            <th style="width:90px;" class="text-right">Precio unit.</th>
                                            <th style="width:70px;" class="text-right">Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyRubros"></tbody>
                                </table>
                            </div>
                            <div class="alert alert-warning mt-2 mb-0 py-1 small" id="alertSinRubros" style="display:none;">
                                <i class="fas fa-info-circle mr-1"></i>No hay rubros activos configurados.
                                <a href="<?= url('facturacion','rubro','index') ?>">Ir a Rubros</a>
                            </div>
                        </div>
                    </div>

                    <!-- ── TAB COMPROBANTES ─────────────────────── -->
                    <div class="tab-pane fade" id="tab-comprobantes" role="tabpanel">
                        <p class="text-muted small mb-2">
                            Comprobantes / recibos emitidos desde otros módulos que aún no han sido facturados electrónicamente.
                        </p>
                        <div id="alertSinClienteComp" class="alert alert-warning py-2 small" style="display:none;">
                            <i class="fas fa-user-slash mr-1"></i>
                            Ingrese y seleccione un cliente en el campo <strong>Identificación / RUC</strong> para ver sus comprobantes pendientes.
                        </div>
                        <div id="loadingComprobantes" class="text-center py-4" style="display:none;">
                            <i class="fas fa-spinner fa-spin fa-2x text-secondary"></i>
                        </div>
                        <div id="listaComprobantes" style="display:none;">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" id="tblComprobantes">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width:30px;"></th>
                                            <th style="width:80px;">Módulo</th>
                                            <th style="width:100px;">Número</th>
                                            <th>Concepto</th>
                                            <th>Alumno / Persona</th>
                                            <th style="width:70px;" class="text-center">IVA</th>
                                            <th style="width:90px;" class="text-right">Total</th>
                                            <th style="width:90px;" class="text-right">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyComprobantes"></tbody>
                                </table>
                            </div>
                            <div class="alert alert-info mt-2 mb-0 py-1 small" id="alertSinComprobantes" style="display:none;">
                                <i class="fas fa-info-circle mr-1"></i>No hay comprobantes pendientes de facturar.
                            </div>
                        </div>
                    </div>

                </div><!-- /tab-content -->
            </div><!-- /modal-body -->

            <div class="modal-footer">
                <span class="text-muted small mr-auto" id="infoSeleccion">Ningún ítem seleccionado</span>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnAgregarSeleccionados" disabled>
                    <i class="fas fa-plus mr-1"></i>Agregar seleccionados a la factura
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════ -->
<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
(function ($) {
    'use strict';

    /* ── Estado ─────────────────────────────────────────── */
    var lineas   = [];
    var editIdx  = null;   // null = modo agregar; número = modo editar

    /* ── Helpers ─────────────────────────────────────────── */
    function esc(str) {
        if (!str) return '';
        return $('<div>').text(str).html();
    }

    function fmt(n) {
        return '$' + parseFloat(n || 0).toFixed(2);
    }

    function actualizarPreview() {
        var c = parseFloat($('#nCant').val())    || 0;
        var p = parseFloat($('#nPrecio').val())  || 0;
        var d = parseFloat($('#nDescLin').val()) || 0;
        var sub = Math.max(0, c * p - d);
        $('#nPreview').text(fmt(sub));
    }

    /* ── Toast global ───────────────────────────────────── */
    function toast(msg, tipo) {
        if (typeof Swal !== 'undefined') {
            Swal.mixin({
                toast: true, position: 'top-end',
                showConfirmButton: false, timer: 3500, timerProgressBar: true
            }).fire({ icon: tipo || 'info', title: msg });
        } else {
            alert(msg);
        }
    }

    /* ── Cambiar visual del tfoot según modo ────────────── */
    function setModoEdicion(idx) {
        editIdx = idx;
        if (idx !== null) {
            $('#trModoLabel').show();
            $('#trInputs').css('background', '#fff8e1');
            $('#btnAgregarIcon').removeClass('fa-plus').addClass('fa-check');
            $('#btnAgregar').removeClass('btn-success').addClass('btn-warning')
                .attr('title', 'Actualizar ítem (Enter)');
            $('#btnAgregarText').text('Actualizar').removeClass('d-none');
        } else {
            $('#trModoLabel').hide();
            $('#trInputs').css('background', '');
            $('#btnAgregarIcon').removeClass('fa-check').addClass('fa-plus');
            $('#btnAgregar').removeClass('btn-warning').addClass('btn-success')
                .attr('title', 'Agregar (Enter)');
            $('#btnAgregarText').text('Agregar');
        }
    }

    /* ── Limpiar inputs del tfoot ───────────────────────── */
    function limpiarInputs() {
        $('#nCod').val('');
        $('#nDesc').val('').removeClass('is-invalid');
        $('#nPer').val('');
        $('#nCant').val('1');
        $('#nPrecio').val('0.00');
        $('#nDescLin').val('0.00');
        $('#nIva').val('15');
        $('#nPreview').text('$0.00');
        setModoEdicion(null);
        $('#nDesc').focus();
    }

    /* ── Cargar ítem en tfoot para editar ───────────────── */
    function cargarParaEdicion(idx) {
        var l = lineas[idx];
        if (!l) return;

        // Resaltar la fila editada en el tbody
        $('#lineasBody tr').removeClass('table-warning');
        $('#lineasBody tr[data-idx="' + idx + '"]').addClass('table-warning');

        $('#nCod').val(l.codigo);
        $('#nDesc').val(l.descripcion);
        $('#nPer').val(l.periodo);
        $('#nCant').val(parseFloat(l.cantidad).toFixed(2));
        $('#nPrecio').val(parseFloat(l.precio).toFixed(2));
        $('#nDescLin').val(parseFloat(l.descuento_lin).toFixed(2));
        $('#nIva').val(String(l.pct_iva));

        actualizarPreview();
        setModoEdicion(idx);

        // Scroll al tfoot y foco en descripción
        var $tfoot = $('#tfootLinea');
        $('html, body').animate({ scrollTop: $tfoot.offset().top - 120 }, 200);
        $('#nDesc').focus();
    }

    /* ── Renderizar filas de líneas ─────────────────────── */
    function renderLineas() {
        var $tbody = $('#lineasBody');
        $tbody.empty();

        if (lineas.length === 0) {
            $tbody.append(
                '<tr id="trVacio"><td colspan="9" class="text-center text-muted py-3">' +
                '<i class="fas fa-info-circle mr-1"></i>Agregue ítems usando el formulario de abajo</td></tr>'
            );
            $('#btnGuardar').prop('disabled', true);
            $('#contadorLineas').text('0 ítems');
        } else {
            lineas.forEach(function (l, i) {
                var base    = (l.cantidad * l.precio) - l.descuento_lin;
                var sub     = Math.max(0, base);
                var esEdit  = (editIdx === i);

                var $tr = $('<tr>').attr('data-idx', i);
                if (esEdit) { $tr.addClass('table-warning'); }

                $tr.html(
                    '<td><span class="badge badge-secondary" style="font-size:.72rem;">' +
                        esc(l.codigo || '—') + '</span></td>' +
                    '<td>' + esc(l.descripcion) +
                        (l.periodo ? ' <small class="text-muted">| ' + esc(l.periodo) + '</small>' : '') +
                    '</td>' +
                    '<td class="text-center text-muted" style="font-size:.78rem;">' + esc(l.periodo) + '</td>' +
                    '<td class="text-right">' + parseFloat(l.cantidad).toFixed(2) + '</td>' +
                    '<td class="text-right">' + fmt(l.precio) + '</td>' +
                    '<td class="text-right">' + (l.descuento_lin > 0 ? fmt(l.descuento_lin) : '<span class="text-muted">—</span>') + '</td>' +
                    '<td class="text-center"><span class="badge badge-info">' + l.pct_iva + '%</span></td>' +
                    '<td class="text-right font-weight-bold">' + fmt(sub) + '</td>' +
                    '<td class="text-center" style="white-space:nowrap;">' +
                        '<button type="button" class="btn btn-xs btn-outline-primary btn-editar mr-1" ' +
                            'data-idx="' + i + '" title="Editar ítem">' +
                            '<i class="fas fa-pencil-alt" style="font-size:.7rem;"></i></button>' +
                        '<button type="button" class="btn btn-xs btn-outline-danger btn-eliminar" ' +
                            'data-idx="' + i + '" title="Eliminar ítem">' +
                            '<i class="fas fa-trash" style="font-size:.7rem;"></i></button>' +
                    '</td>'
                );
                $tbody.append($tr);
            });
            $('#btnGuardar').prop('disabled', false);
            $('#contadorLineas').text(lineas.length + (lineas.length === 1 ? ' ítem' : ' ítems'));
        }

        calcularTotales();
    }

    /* ── Agregar o actualizar línea ─────────────────────── */
    function confirmarLinea() {
        var cod   = $('#nCod').val().trim().toUpperCase();
        var desc  = $('#nDesc').val().trim();
        var per   = $('#nPer').val().trim();
        var cant  = Math.max(0.01, parseFloat($('#nCant').val()) || 1);
        var prec  = Math.max(0,    parseFloat($('#nPrecio').val()) || 0);
        var dlin  = Math.max(0,    parseFloat($('#nDescLin').val()) || 0);
        var iva   = parseInt($('#nIva').val()) || 15;

        if (!desc) {
            toast('Ingrese una descripción para el ítem', 'warning');
            $('#nDesc').focus().addClass('is-invalid');
            return;
        }
        $('#nDesc').removeClass('is-invalid');

        var item = { codigo: cod, descripcion: desc, periodo: per,
                     cantidad: cant, precio: prec, descuento_lin: dlin, pct_iva: iva };

        if (editIdx !== null) {
            lineas[editIdx] = item;
            toast('Ítem actualizado', 'success');
        } else {
            lineas.push(item);
        }

        limpiarInputs();
        renderLineas();
    }

    /* ── Calcular totales ───────────────────────────────── */
    function calcularTotales() {
        var sub0 = 0, subIva = 0, ivaTotal = 0, pctIvaMax = 0;

        lineas.forEach(function (l) {
            var base = Math.max(0, l.cantidad * l.precio - l.descuento_lin);
            if (l.pct_iva > 0) {
                subIva   += base;
                ivaTotal += base * l.pct_iva / 100;
                if (l.pct_iva > pctIvaMax) pctIvaMax = l.pct_iva;
            } else {
                sub0 += base;
            }
        });

        var descuento = Math.max(0, parseFloat($('#descuento').val()) || 0);
        var subtotal  = sub0 + subIva;
        var total     = subtotal + ivaTotal - descuento;

        $('#subtotal0').text(fmt(sub0));
        $('#subtotalIva').text(fmt(subIva));
        $('#subtotalTotal').text(fmt(subtotal));
        $('#ivaDisplay').text(fmt(ivaTotal));
        $('#totalDisplay').text(fmt(total));
        $('#totalPagoDisplay').val(total.toFixed(2));
        $('#labelIva').text('IVA ' + (pctIvaMax || 15) + '%');
    }

    /* ── Enviar formulario AJAX ─────────────────────────── */
    function enviarFormulario() {
        var $btn = $('#btnGuardar');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...');

        var payload = {
            csrf_token:        $('#csrf_token').val(),
            origen_modulo:     $('#origen_modulo').val(),
            origen_id:         $('#origen_id').val(),
            cliente_id:        $('#cliente_id').val(),
            fecha_emision:     $('#fecha_emision').val(),
            fecha_vencimiento: $('#fecha_vencimiento').val(),
            forma_pago_id:     $('#forma_pago_id').val(),
            descuento:         $('#descuento').val(),
            observaciones:     $('#observaciones').val(),
            guia_remision:     $('#guia_remision').val(),
            lineas_json:       JSON.stringify(lineas)
        };

        $.ajax({
            url: '<?= url('facturacion', 'factura', 'guardar') ?>',
            type: 'POST',
            data: payload,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    toast(res.message || 'Factura creada exitosamente', 'success');
                    setTimeout(function () {
                        window.location.href = (res.data && res.data.redirect)
                            ? res.data.redirect
                            : '<?= url('facturacion', 'factura', 'index') ?>';
                    }, 1200);
                } else {
                    toast(res.message || 'Error al guardar la factura', 'error');
                    $btn.prop('disabled', false)
                        .html('<i class="fas fa-save mr-2"></i>Guardar como Borrador');
                }
            },
            error: function (xhr) {
                var msg = 'Error de comunicación con el servidor';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
                toast(msg, 'error');
                $btn.prop('disabled', false)
                    .html('<i class="fas fa-save mr-2"></i>Guardar como Borrador');
            }
        });
    }

    /* ── Init + bindings ────────────────────────────────── */
    $(function () {
        renderLineas();
        $('#nDesc').focus();

        /* ── Editar línea ──────────────────────────────── */
        $(document).on('click', '.btn-editar', function () {
            var idx = parseInt($(this).data('idx'));
            if (editIdx === idx) {
                // Segundo clic en el mismo → cancela
                limpiarInputs();
                renderLineas();
            } else {
                cargarParaEdicion(idx);
            }
        });

        /* ── Eliminar línea ─────────────────────────────── */
        $(document).on('click', '.btn-eliminar', function () {
            var idx = parseInt($(this).data('idx'));
            if (editIdx === idx) { limpiarInputs(); }
            if (editIdx !== null && idx < editIdx) { editIdx--; }
            lineas.splice(idx, 1);
            renderLineas();
        });

        /* ── Cancelar edición ──────────────────────────── */
        $('#btnCancelarEdicion').on('click', function () {
            limpiarInputs();
            renderLineas();
        });

        /* ── Preview en tiempo real ─────────────────────── */
        $('#nCant, #nPrecio, #nDescLin').on('input', actualizarPreview);
        $('#nIva').on('change', actualizarPreview);

        /* ── Enter en descripción ────────────────────────── */
        $('#nDesc').on('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); confirmarLinea(); }
        });

        /* ── Escape cancela edición ─────────────────────── */
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && editIdx !== null) {
                limpiarInputs();
                renderLineas();
            }
        });

        /* ── Botón agregar / actualizar ──────────────────── */
        $('#btnAgregar').on('click', confirmarLinea);

        /* ── Descuento global recalcula totales ─────────── */
        $('#descuento').on('input', calcularTotales);

        /* ── Lookup de cliente por identificación ─────────────────────── */
        function limpiarSeleccionCliente() {
            $('#cliente_id').val('');
            $('#clienteInfoCard').hide();
            $('#panelNuevoCliente').hide();
            $('#buscarStatus').html('');
        }

        function mostrarInfoCliente(data) {
            var nombre = $.trim((data.nombres || '') + ' ' + (data.apellidos || ''));
            $('#ci_nombre').text(nombre || '—');
            $('#ci_tipo').text(data.tipo      || '—');
            $('#ci_ident').text($('#buscarIdent').val() || '—');
            $('#ci_email').text(data.email    || '—');
            $('#ci_tel').text(data.telefono   || '—');
            $('#ci_dir').text(data.direccion  || '—');
            $('#clienteInfoCard').show();
        }

        function buscarCliente() {
            var ident = $.trim($('#buscarIdent').val());
            if (ident.length < 5) {
                toast('Ingrese al menos 5 dígitos', 'warning');
                return;
            }
            limpiarSeleccionCliente();
            var $icon = $('#iconBuscar');
            $icon.removeClass('fa-search').addClass('fa-spinner fa-spin');
            $('#btnBuscarCliente').prop('disabled', true);

            $.ajax({
                url: '<?= url('facturacion', 'factura', 'buscarClientePorIdentificacion') ?>',
                type: 'GET',
                data: { identificacion: ident },
                dataType: 'json',
                success: function (res) {
                    $icon.removeClass('fa-spinner fa-spin').addClass('fa-search');
                    $('#btnBuscarCliente').prop('disabled', false);

                    if (res.found && res.local) {
                        $('#cliente_id').val(res.id);
                        $('#buscarStatus').html(
                            '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>' +
                            esc($.trim(res.nombres + ' ' + res.apellidos)) + '</span>'
                        );
                        mostrarInfoCliente(res);

                    } else if (res.found && !res.local) {
                        $('#panelNuevoClienteTitle').html(
                            '<i class="fas fa-user-clock mr-1 text-info"></i>' +
                            'Datos encontrados — confirme y registre en su cuenta'
                        );
                        $('#nc_tipo').val(res.tipo      || 'CC');
                        $('#nc_nombres').val(res.nombres   || '');
                        $('#nc_apellidos').val(res.apellidos || '');
                        $('#nc_email').val(res.email     || '');
                        $('#nc_telefono').val(res.telefono  || '');
                        $('#nc_direccion').val(res.direccion || '');
                        $('#panelNuevoCliente').show();
                        $('#buscarStatus').html(
                            '<span class="text-info"><i class="fas fa-info-circle mr-1"></i>' +
                            'Encontrado en el sistema — revise y registre</span>'
                        );

                    } else {
                        $('#panelNuevoClienteTitle').html(
                            '<i class="fas fa-user-plus mr-1 text-warning"></i>' +
                            'Cliente no registrado — complete para agregar'
                        );
                        $('#nc_tipo').val('CC');
                        $('#nc_nombres').val('');
                        $('#nc_apellidos').val('');
                        $('#nc_email').val('');
                        $('#nc_telefono').val('');
                        $('#nc_direccion').val('');
                        $('#panelNuevoCliente').show();
                        $('#buscarStatus').html(
                            '<span class="text-muted"><i class="fas fa-user-slash mr-1"></i>' +
                            'No encontrado — complete los datos para registrar</span>'
                        );
                        setTimeout(function () { $('#nc_nombres').focus(); }, 80);
                    }
                },
                error: function () {
                    $icon.removeClass('fa-spinner fa-spin').addClass('fa-search');
                    $('#btnBuscarCliente').prop('disabled', false);
                    toast('Error al buscar el cliente', 'error');
                }
            });
        }

        function guardarNuevoCliente() {
            var nombres = $.trim($('#nc_nombres').val());
            if (!nombres) {
                toast('Ingrese el nombre del cliente', 'warning');
                $('#nc_nombres').focus().addClass('is-invalid');
                return;
            }
            $('#nc_nombres').removeClass('is-invalid');

            var $btn = $('#btnGuardarCliente');
            $btn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-1"></i>Registrando...');

            $.ajax({
                url: '<?= url('facturacion', 'factura', 'crearClienteRapido') ?>',
                type: 'POST',
                data: {
                    csrf_token:          $('#csrf_token').val(),
                    tipo_identificacion: $('#nc_tipo').val(),
                    identificacion:      $.trim($('#buscarIdent').val()),
                    nombres:             nombres,
                    apellidos:           $.trim($('#nc_apellidos').val()),
                    email:               $.trim($('#nc_email').val()),
                    telefono:            $.trim($('#nc_telefono').val()),
                    direccion:           $.trim($('#nc_direccion').val()),
                },
                dataType: 'json',
                success: function (res) {
                    $btn.prop('disabled', false)
                        .html('<i class="fas fa-user-check mr-1"></i>' +
                              '<span id="btnGuardarClienteText">Registrar y usar este cliente</span>');
                    if (res.success) {
                        $('#cliente_id').val(res.data.cliente_id);
                        $('#panelNuevoCliente').hide();
                        $('#buscarStatus').html(
                            '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>' +
                            esc(res.data.nombre) + ' — registrado</span>'
                        );
                        mostrarInfoCliente({
                            nombres:   $.trim($('#nc_nombres').val()),
                            apellidos: $.trim($('#nc_apellidos').val()),
                            tipo:      $('#nc_tipo').val(),
                            email:     $.trim($('#nc_email').val()),
                            telefono:  $.trim($('#nc_telefono').val()),
                            direccion: $.trim($('#nc_direccion').val()),
                        });
                        toast('Cliente registrado', 'success');
                    } else {
                        toast(res.message || 'Error al registrar el cliente', 'error');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false)
                        .html('<i class="fas fa-user-check mr-1"></i>' +
                              '<span>Registrar y usar este cliente</span>');
                    var msg = 'Error de comunicación';
                    try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
                    toast(msg, 'error');
                }
            });
        }

        if ($('#buscarIdent').length) {
            $('#btnBuscarCliente').on('click', buscarCliente);
            $('#buscarIdent').on('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); buscarCliente(); }
            });
            $('#btnGuardarCliente').on('click', guardarNuevoCliente);
            $('#btnCerrarPanel').on('click', function () {
                $('#panelNuevoCliente').hide();
                $('#buscarStatus').html('');
            });
            $('#linkCambiarCliente').on('click', function (e) {
                e.preventDefault();
                limpiarSeleccionCliente();
                $('#buscarIdent').val('').focus();
            });
        }

        /* ══════════════════════════════════════════════════════════
           MODAL: CARGAR COMPROBANTES / RUBROS
        ══════════════════════════════════════════════════════════ */

        var itemsCache         = null; // cache de la respuesta del servidor
        var itemsCacheClientId = null; // cliente para el que se cachearon los items

        function actualizarInfoSeleccion() {
            var total = $('.chk-rubro:checked').length + $('.chk-comp:checked').length;
            if (total === 0) {
                $('#infoSeleccion').text('Ningún ítem seleccionado');
                $('#btnAgregarSeleccionados').prop('disabled', true);
            } else {
                $('#infoSeleccion').text(total + (total === 1 ? ' ítem seleccionado' : ' ítems seleccionados'));
                $('#btnAgregarSeleccionados').prop('disabled', false);
            }
        }

        function renderRubros(rubros) {
            var $tbody = $('#tbodyRubros').empty();
            if (!rubros || rubros.length === 0) {
                $('#alertSinRubros').show();
                return;
            }
            $('#alertSinRubros').hide();
            $('#badgeRubros').text(rubros.length);

            $.each(rubros, function (i, r) {
                var ivaBadge = r.rub_aplica_iva == 1
                    ? '<span class="badge badge-warning">IVA ' + r.rub_porcentaje_iva + '%</span>'
                    : '<span class="badge badge-secondary">Exento</span>';
                var ivaPct = r.rub_aplica_iva == 1 ? parseFloat(r.rub_porcentaje_iva) : 0;

                $tbody.append(
                    '<tr>' +
                    '<td class="text-center"><input type="checkbox" class="chk-rubro" ' +
                        'data-codigo="' + esc(r.rub_codigo) + '" ' +
                        'data-nombre="' + esc(r.rub_nombre) + '" ' +
                        'data-iva="' + ivaPct + '"></td>' +
                    '<td><small class="text-muted">' + esc(r.rub_codigo || '—') + '</small></td>' +
                    '<td>' + esc(r.rub_nombre) +
                        (r.rub_descripcion ? '<br><small class="text-muted">' + esc(r.rub_descripcion) + '</small>' : '') +
                    '</td>' +
                    '<td class="text-center">' + ivaBadge + '</td>' +
                    '<td><input type="number" class="form-control form-control-sm text-right inp-precio-rubro" ' +
                        'value="0.00" min="0" step="0.01" style="min-width:80px;"></td>' +
                    '<td><input type="number" class="form-control form-control-sm text-right inp-cant-rubro" ' +
                        'value="1" min="0.01" step="0.01" style="min-width:55px;"></td>' +
                    '</tr>'
                );
            });

            $('#tblRubros').off('change', '.chk-rubro').on('change', '.chk-rubro', actualizarInfoSeleccion);
        }

        function renderComprobantes(comprobantes) {
            var $tbody = $('#tbodyComprobantes').empty();
            if (!comprobantes || comprobantes.length === 0) {
                $('#alertSinComprobantes').show();
                return;
            }
            $('#alertSinComprobantes').hide();
            $('#badgeComprobantes').text(comprobantes.length);

            var fuenteIconos = { futbol: 'fas fa-futbol', futbol_pago: 'fas fa-futbol', basket: 'fas fa-basketball-ball', natacion: 'fas fa-swimmer' };

            $.each(comprobantes, function (i, c) {
                var ivaBadge = c.pct_iva > 0
                    ? '<span class="badge badge-warning">' + c.pct_iva + '%</span>'
                    : '<span class="badge badge-secondary">0%</span>';
                var icono = fuenteIconos[c.fuente] || 'fas fa-receipt';

                $tbody.append(
                    '<tr>' +
                    '<td class="text-center"><input type="checkbox" class="chk-comp" ' +
                        'data-fuente="' + esc(c.fuente) + '" ' +
                        'data-id="' + c.id + '" ' +
                        'data-numero="' + esc(c.numero) + '" ' +
                        'data-desc="' + esc(c.descripcion) + '" ' +
                        'data-alumno="' + esc(c.alumno) + '" ' +
                        'data-precio="' + c.precio + '" ' +
                        'data-iva="' + c.pct_iva + '"></td>' +
                    '<td><small><i class="' + icono + ' mr-1"></i>' + esc(c.fuente_label) + '</small></td>' +
                    '<td><small class="font-weight-bold">' + esc(c.numero) + '</small></td>' +
                    '<td>' + esc(c.descripcion) + '</td>' +
                    '<td><small class="text-muted">' + esc(c.alumno) + '</small></td>' +
                    '<td class="text-center">' + ivaBadge + '</td>' +
                    '<td class="text-right font-weight-bold">$' + parseFloat(c.total).toFixed(2) + '</td>' +
                    '<td class="text-right"><small>' + esc(c.fecha) + '</small></td>' +
                    '</tr>'
                );
            });

            $('#tblComprobantes').off('change', '.chk-comp').on('change', '.chk-comp', actualizarInfoSeleccion);
        }

        function cargarItemsModal() {
            var clienteId = $('#cliente_id').val() || '';

            // Si no hay cliente: limpiar y ocultar comprobantes, mostrar aviso
            if (!clienteId) {
                $('#alertSinClienteComp').show();
                $('#loadingComprobantes').hide();
                $('#listaComprobantes').hide();
                $('#tbodyComprobantes').empty();
                $('#badgeComprobantes').text('0');
            } else {
                $('#alertSinClienteComp').hide();
            }

            // Usar caché solo si el cliente no cambió
            if (itemsCache && itemsCacheClientId === clienteId) {
                renderRubros(itemsCache.rubros);
                if (clienteId) renderComprobantes(itemsCache.comprobantes);
                $('#loadingRubros').hide();
                $('#listaRubros').show();
                return;
            }

            $('#loadingRubros').show();
            $('#listaRubros').hide();
            // Limpiar comprobantes del cliente anterior antes de cargar los nuevos
            $('#tbodyComprobantes').empty();
            $('#listaComprobantes').hide();
            $('#badgeComprobantes').text('0');

            $.ajax({
                url: '<?= url('facturacion', 'factura', 'obtenerItemsParaFacturar') ?>',
                type: 'GET',
                data: { cliente_id: clienteId },
                dataType: 'json',
                success: function (res) {
                    $('#loadingRubros').hide();
                    $('#listaRubros').show();
                    if (res.success) {
                        itemsCache         = res;
                        itemsCacheClientId = clienteId;
                        renderRubros(res.rubros);
                        if (clienteId) {
                            $('#loadingComprobantes').hide();
                            $('#listaComprobantes').show();
                            renderComprobantes(res.comprobantes);
                        }
                    } else {
                        toast(res.message || 'Error al cargar ítems', 'error');
                    }
                },
                error: function () {
                    $('#loadingRubros').hide();
                    toast('Error de comunicación al cargar ítems', 'error');
                }
            });
        }

        $('#btnCargarItems').on('click', function () {
            // Resetear selección
            actualizarInfoSeleccion();
            $('#modalCargarItems').modal('show');
        });

        $('#modalCargarItems').on('shown.bs.modal', function () {
            cargarItemsModal();
        });

        // Al cambiar de tab, mostrar spinner de comprobantes si aún no cargaron
        $('a[href="#tab-comprobantes"]').on('shown.bs.tab', function () {
            if (itemsCache) {
                $('#loadingComprobantes').hide();
                $('#listaComprobantes').show();
            } else {
                $('#loadingComprobantes').show();
                $('#listaComprobantes').hide();
            }
        });

        $('#btnAgregarSeleccionados').on('click', function () {
            var agregados = 0;

            // ── Rubros seleccionados ────────────────────────────────────────
            $('.chk-rubro:checked').each(function () {
                var $chk   = $(this);
                var $row   = $chk.closest('tr');
                var precio = Math.max(0, parseFloat($row.find('.inp-precio-rubro').val()) || 0);
                var cant   = Math.max(0.01, parseFloat($row.find('.inp-cant-rubro').val()) || 1);

                if (precio <= 0) {
                    toast('El rubro "' + $chk.data('nombre') + '" requiere precio mayor a 0', 'warning');
                    $row.find('.inp-precio-rubro').focus();
                    return false; // break
                }

                lineas.push({
                    codigo:        $chk.data('codigo') || '',
                    descripcion:   $chk.data('nombre'),
                    periodo:       '',
                    cantidad:      cant,
                    precio:        precio,
                    descuento_lin: 0,
                    pct_iva:       parseInt($chk.data('iva')) || 0,
                });
                agregados++;
            });

            // ── Comprobantes seleccionados ──────────────────────────────────
            $('.chk-comp:checked').each(function () {
                var $chk = $(this);
                lineas.push({
                    codigo:        $chk.data('numero'),
                    descripcion:   $chk.data('desc') + ' — ' + $chk.data('alumno'),
                    periodo:       '',
                    cantidad:      1,
                    precio:        parseFloat($chk.data('precio')) || 0,
                    descuento_lin: 0,
                    pct_iva:       parseInt($chk.data('iva')) || 0,
                    ref_fuente:    $chk.data('fuente'),
                    ref_id:        parseInt($chk.data('id')) || 0,
                });
                agregados++;
            });

            if (agregados > 0) {
                renderLineas();
                $('#modalCargarItems').modal('hide');
                toast(agregados + (agregados === 1 ? ' ítem agregado' : ' ítems agregados') + ' a la factura', 'success');
                // Invalidar caché para que recargue en la próxima apertura
                itemsCache         = null;
                itemsCacheClientId = null;
            }
        });

        /* ── Guardar (AJAX) ─────────────────────────────── */
        $('#btnGuardar').on('click', function () {
            var clienteId   = $('#cliente_id').val();
            var formaPagoId = $('#forma_pago_id').val();
            if (!clienteId)        { toast('Debe seleccionar un cliente',           'warning'); $('#buscarIdent, #cliente_id').first().filter(':visible').focus();    return; }
            if (!formaPagoId)      { toast('Seleccione una forma de pago',          'warning'); $('#forma_pago_id').focus(); return; }
            if (lineas.length < 1) { toast('Agregue al menos un ítem a la factura', 'warning'); $('#nDesc').focus();         return; }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Guardar Factura?',
                    html: 'Se guardará como <strong>BORRADOR</strong>.<br>Podrá editarla y emitirla al SRI desde el detalle.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-save mr-1"></i>Guardar',
                    cancelButtonText:  '<i class="fas fa-times mr-1"></i>Cancelar',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor:  '#6c757d'
                }).then(function (r) { if (r.isConfirmed) enviarFormulario(); });
            } else {
                if (confirm('¿Guardar factura como borrador?')) enviarFormulario();
            }
        });
    });

}(jQuery));
</script>
<?php $scripts = ob_get_clean(); ?>
