<?php
/**
 * Vista: Gestión de Rubros de Facturación
 * CRUD: crear, editar, activar/inactivar, eliminar
 */
$rubros     = $rubros     ?? [];
$csrf_token = $csrf_token ?? '';
$title      = $title      ?? 'Rubros de Facturación';

$porcentajesIva = [0, 5, 12, 14, 15];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-tags" style="color:var(--module-color)"></i>
                    <?= htmlspecialchars($title) ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="<?= url('facturacion', 'dashboard', 'index') ?>">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Rubros</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="card card-outline" style="border-top-color:var(--module-color)">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>Rubros Registrados
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm" id="btn-nuevo-rubro"
                            style="background:var(--module-color);color:#fff;">
                        <i class="fas fa-plus mr-1"></i> Nuevo Rubro
                    </button>
                </div>
            </div>

            <div class="card-body table-responsive p-0">
                <?php if (empty($rubros)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-tags fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay rubros registrados</h5>
                        <p class="text-muted">Haga clic en <strong>Nuevo Rubro</strong> para comenzar</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover table-striped mb-0" id="tabla-rubros">
                        <thead class="bg-light">
                            <tr>
                                <th style="width:60px">#</th>
                                <th style="width:100px">Código</th>
                                <th>Nombre / Concepto</th>
                                <th>Descripción</th>
                                <th style="width:110px" class="text-center">Aplica IVA</th>
                                <th style="width:90px"  class="text-center">% IVA</th>
                                <th style="width:100px" class="text-center">Estado</th>
                                <th style="width:130px" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rubros as $r): ?>
                            <tr id="fila-<?= (int)$r['rub_id'] ?>">
                                <td class="text-muted small"><?= (int)$r['rub_id'] ?></td>
                                <td>
                                    <?php if (!empty($r['rub_codigo'])): ?>
                                        <code class="text-primary"><?= htmlspecialchars($r['rub_codigo']) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($r['rub_nombre']) ?></strong>
                                </td>
                                <td class="text-muted small">
                                    <?= htmlspecialchars($r['rub_descripcion'] ?? '—') ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($r['rub_aplica_iva']): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check mr-1"></i>Sí
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-times mr-1"></i>Exento
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($r['rub_aplica_iva']): ?>
                                        <span class="badge badge-info">
                                            <?= number_format((float)$r['rub_porcentaje_iva'], 0) ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-light text-muted">0%</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $r['rub_estado'] === 'ACTIVO' ? 'success' : 'secondary' ?> estado-badge">
                                        <?= $r['rub_estado'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <!-- Editar -->
                                        <button type="button"
                                                class="btn btn-outline-primary btn-editar"
                                                title="Editar"
                                                data-id="<?= (int)$r['rub_id'] ?>"
                                                data-codigo="<?= htmlspecialchars($r['rub_codigo'] ?? '', ENT_QUOTES) ?>"
                                                data-nombre="<?= htmlspecialchars($r['rub_nombre'], ENT_QUOTES) ?>"
                                                data-descripcion="<?= htmlspecialchars($r['rub_descripcion'] ?? '', ENT_QUOTES) ?>"
                                                data-aplica-iva="<?= (int)$r['rub_aplica_iva'] ?>"
                                                data-pct-iva="<?= number_format((float)$r['rub_porcentaje_iva'], 0) ?>">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <!-- Activar / Inactivar -->
                                        <button type="button"
                                                class="btn btn-outline-<?= $r['rub_estado'] === 'ACTIVO' ? 'warning' : 'success' ?> btn-toggle"
                                                title="<?= $r['rub_estado'] === 'ACTIVO' ? 'Inactivar' : 'Activar' ?>"
                                                data-id="<?= (int)$r['rub_id'] ?>"
                                                data-nombre="<?= htmlspecialchars($r['rub_nombre'], ENT_QUOTES) ?>"
                                                data-estado="<?= $r['rub_estado'] ?>">
                                            <i class="fas fa-<?= $r['rub_estado'] === 'ACTIVO' ? 'ban' : 'check' ?>"></i>
                                        </button>
                                        <!-- Eliminar -->
                                        <button type="button"
                                                class="btn btn-outline-danger btn-eliminar"
                                                title="Eliminar"
                                                data-id="<?= (int)$r['rub_id'] ?>"
                                                data-nombre="<?= htmlspecialchars($r['rub_nombre'], ENT_QUOTES) ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="card-footer text-muted small">
                Total: <strong><?= count($rubros) ?></strong> rubro(s) &nbsp;|&nbsp;
                <?= count(array_filter($rubros, fn($r) => $r['rub_aplica_iva'])) ?> con IVA &nbsp;|&nbsp;
                <?= count(array_filter($rubros, fn($r) => !$r['rub_aplica_iva'])) ?> exentos
            </div>
        </div>

    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- MODAL: Crear / Editar Rubro                                             -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalRubro" tabindex="-1" aria-labelledby="modalRubroLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header text-white" style="background:var(--module-color)">
                <h5 class="modal-title" id="modalRubroLabel">
                    <i class="fas fa-tags mr-2"></i>
                    <span id="modal-titulo">Nuevo Rubro</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="form-rubro" novalidate>
                <div class="modal-body">

                    <input type="hidden" id="rub_id"    name="rub_id"    value="0">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <!-- Código -->
                    <div class="form-group">
                        <label for="rub_codigo">Código <span class="text-muted">(opcional)</span></label>
                        <input type="text"
                               class="form-control text-uppercase"
                               id="rub_codigo"
                               name="rub_codigo"
                               maxlength="20"
                               placeholder="Ej: MENS, MAT, INSCR"
                               autocomplete="off">
                        <small class="text-muted">
                            Código corto para identificar el rubro en la línea de factura.
                        </small>
                    </div>

                    <!-- Nombre -->
                    <div class="form-group">
                        <label for="rub_nombre">
                            Nombre / Concepto <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="rub_nombre"
                               name="rub_nombre"
                               maxlength="100"
                               placeholder="Ej: Mensualidad, Matrícula, Inscripción"
                               required
                               autocomplete="off">
                        <small class="text-muted">Máximo 100 caracteres</small>
                    </div>

                    <!-- Descripción -->
                    <div class="form-group">
                        <label for="rub_descripcion">Descripción <span class="text-muted">(opcional)</span></label>
                        <input type="text"
                               class="form-control"
                               id="rub_descripcion"
                               name="rub_descripcion"
                               maxlength="255"
                               placeholder="Descripción ampliada del concepto"
                               autocomplete="off">
                    </div>

                    <!-- Aplica IVA toggle -->
                    <div class="form-group mb-2">
                        <label class="d-block">Configuración de IVA <span class="text-danger">*</span></label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="rub_aplica_iva"
                                   name="rub_aplica_iva"
                                   value="1"
                                   checked>
                            <label class="custom-control-label font-weight-bold" for="rub_aplica_iva"
                                   id="label-aplica-iva">
                                Aplica IVA
                            </label>
                        </div>
                        <small class="text-muted" id="help-aplica-iva">
                            Este rubro estará gravado con IVA en la factura.
                        </small>
                    </div>

                    <!-- Porcentaje IVA (visible solo cuando aplica IVA) -->
                    <div id="panel-porcentaje-iva" class="form-group pl-3 border-left border-info">
                        <label for="rub_porcentaje_iva">Porcentaje de IVA <span class="text-danger">*</span></label>
                        <select class="form-control" id="rub_porcentaje_iva" name="rub_porcentaje_iva">
                            <?php foreach ($porcentajesIva as $pct): ?>
                            <option value="<?= $pct ?>" <?= $pct === 15 ? 'selected' : '' ?>>
                                <?= $pct ?>% <?php if ($pct === 15): ?>(Tarifa general vigente)<?php elseif ($pct === 0): ?>(Exento / Tarifa 0)<?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            Tarifa de IVA según normativa SRI vigente.
                        </small>
                    </div>

                    <!-- Alerta cuando exento -->
                    <div id="panel-exento" class="alert alert-secondary py-2 px-3 mb-0" style="display:none;font-size:.85rem;">
                        <i class="fas fa-info-circle mr-1"></i>
                        Este rubro estará marcado como <strong>exento de IVA (0%)</strong>
                        y no generará cargo de impuesto en la factura.
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="btn-guardar-modal">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
(function ($) {
    'use strict';

    var urlGuardar  = <?= json_encode(url('facturacion', 'rubro', 'guardar')) ?>;
    var urlToggle   = <?= json_encode(url('facturacion', 'rubro', 'toggleEstado')) ?>;
    var urlEliminar = <?= json_encode(url('facturacion', 'rubro', 'eliminar')) ?>;
    var csrfToken   = <?= json_encode($csrf_token) ?>;

    var Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });

    // ── Toggle visibilidad del panel de porcentaje ───────────────────────────
    function actualizarPanelIva() {
        var aplica = $('#rub_aplica_iva').is(':checked');
        if (aplica) {
            $('#panel-porcentaje-iva').show();
            $('#panel-exento').hide();
            $('#label-aplica-iva').text('Aplica IVA');
            $('#help-aplica-iva').text('Este rubro estará gravado con IVA en la factura.');
        } else {
            $('#panel-porcentaje-iva').hide();
            $('#panel-exento').show();
            $('#label-aplica-iva').text('Exento de IVA');
            $('#help-aplica-iva').text('Este rubro NO generará cargo de IVA en la factura.');
        }
    }

    $('#rub_aplica_iva').on('change', actualizarPanelIva);

    // ── Resetear formulario ──────────────────────────────────────────────────
    function resetForm() {
        $('#rub_id').val(0);
        $('#rub_codigo').val('');
        $('#rub_nombre').val('');
        $('#rub_descripcion').val('');
        $('#rub_aplica_iva').prop('checked', true);
        $('#rub_porcentaje_iva').val(15);
        $('#form-rubro').removeClass('was-validated');
        actualizarPanelIva();
    }

    // ── Abrir modal CREAR ────────────────────────────────────────────────────
    $('#btn-nuevo-rubro').on('click', function () {
        $('#modal-titulo').text('Nuevo Rubro');
        resetForm();
        $('#modalRubro').modal('show');
        setTimeout(function () { $('#rub_nombre').focus(); }, 350);
    });

    // ── Abrir modal EDITAR ───────────────────────────────────────────────────
    $(document).on('click', '.btn-editar', function () {
        var $btn     = $(this);
        var aplicaIva = parseInt($btn.data('aplica-iva')) === 1;

        $('#modal-titulo').text('Editar Rubro');
        $('#rub_id').val($btn.data('id'));
        $('#rub_codigo').val($btn.data('codigo'));
        $('#rub_nombre').val($btn.data('nombre'));
        $('#rub_descripcion').val($btn.data('descripcion'));
        $('#rub_aplica_iva').prop('checked', aplicaIva);
        $('#rub_porcentaje_iva').val(String($btn.data('pct-iva')));
        $('#form-rubro').removeClass('was-validated');
        actualizarPanelIva();

        $('#modalRubro').modal('show');
        setTimeout(function () { $('#rub_nombre').focus(); }, 350);
    });

    // ── Guardar (crear / editar) ─────────────────────────────────────────────
    $('#form-rubro').on('submit', function (e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            $(this).addClass('was-validated');
            return;
        }

        var $btn = $('#btn-guardar-modal').prop('disabled', true)
                      .html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

        // Asegurarse de enviar el valor 0 cuando el checkbox no está marcado
        var formData = $(this).serializeArray();
        if (!$('#rub_aplica_iva').is(':checked')) {
            formData.push({ name: 'rub_aplica_iva', value: '0' });
            formData.push({ name: 'rub_porcentaje_iva', value: '0' });
        }

        $.ajax({
            url:    urlGuardar,
            method: 'POST',
            data:   formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    $('#modalRubro').modal('hide');
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    Toast.fire({ icon: 'error', title: res.message || 'Error al guardar' });
                }
            },
            error: function (xhr) {
                var msg = 'Error de comunicación con el servidor';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
                Toast.fire({ icon: 'error', title: msg });
            },
            complete: function () {
                $btn.prop('disabled', false)
                    .html('<i class="fas fa-save mr-1"></i> Guardar');
            }
        });
    });

    // ── Activar / Inactivar ──────────────────────────────────────────────────
    $(document).on('click', '.btn-toggle', function () {
        var rubId  = $(this).data('id');
        var nombre = $(this).data('nombre');
        var estado = $(this).data('estado');
        var accion = estado === 'ACTIVO' ? 'inactivar' : 'activar';

        Swal.fire({
            title: (accion === 'inactivar' ? 'Inactivar' : 'Activar') + ' rubro',
            html: '<p>¿Desea <strong>' + accion + '</strong> el rubro:</p>' +
                  '<p><strong>' + $('<span>').text(nombre).html() + '</strong>?</p>',
            icon: accion === 'inactivar' ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: accion === 'inactivar' ? '#ffc107' : '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: accion === 'inactivar'
                ? '<i class="fas fa-ban mr-1"></i> Sí, inactivar'
                : '<i class="fas fa-check mr-1"></i> Sí, activar',
            cancelButtonText: 'Cancelar',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url:    urlToggle,
                method: 'POST',
                data:   { rub_id: rubId, csrf_token: csrfToken },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        Toast.fire({ icon: 'success', title: res.message });
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        Toast.fire({ icon: 'error', title: res.message || 'Error al cambiar estado' });
                    }
                },
                error: function () {
                    Toast.fire({ icon: 'error', title: 'Error de comunicación con el servidor' });
                }
            });
        });
    });

    // ── Eliminar ─────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-eliminar', function () {
        var rubId  = $(this).data('id');
        var nombre = $(this).data('nombre');

        Swal.fire({
            title: 'Eliminar rubro',
            html: '<p>¿Está seguro de eliminar:</p>' +
                  '<p><strong>' + $('<span>').text(nombre).html() + '</strong>?</p>' +
                  '<p class="text-muted small">Esta acción no se puede deshacer.</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor:  '#6c757d',
            confirmButtonText:  '<i class="fas fa-trash mr-1"></i> Sí, eliminar',
            cancelButtonText:   'Cancelar',
            focusCancel: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url:    urlEliminar,
                method: 'POST',
                data:   { rub_id: rubId, csrf_token: csrfToken },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        Toast.fire({ icon: 'success', title: res.message });
                        $('#fila-' + rubId).fadeOut(400, function () { $(this).remove(); });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se puede eliminar',
                            text: res.message,
                            confirmButtonColor: '#3085d6',
                        });
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
