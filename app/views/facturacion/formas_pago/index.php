<?php
/**
 * Vista: Gestión de Formas de Pago
 * CRUD: crear, editar, activar/inactivar, eliminar
 */
$formas     = $formas     ?? [];
$codigosSri = $codigos_sri ?? [];
$csrf_token = $csrf_token  ?? '';
$title      = $title       ?? 'Formas de Pago';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-credit-card text-primary"></i>
                    <?= htmlspecialchars($title) ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="<?= url('facturacion', 'dashboard', 'index') ?>">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Formas de Pago</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>Formas de Pago Registradas
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" id="btn-nueva-forma">
                        <i class="fas fa-plus mr-1"></i> Nueva Forma de Pago
                    </button>
                </div>
            </div>

            <div class="card-body table-responsive p-0">
                <?php if (empty($formas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-credit-card fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay formas de pago registradas</h5>
                        <p class="text-muted">Haga clic en <strong>Nueva Forma de Pago</strong> para comenzar</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover table-striped mb-0" id="tabla-formas">
                        <thead class="bg-light">
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Nombre</th>
                                <th style="width:120px" class="text-center">Código SRI</th>
                                <th>Descripción SRI</th>
                                <th style="width:110px" class="text-center">Estado</th>
                                <th style="width:130px" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($formas as $f): ?>
                            <tr id="fila-<?= (int)$f['fpa_id'] ?>">
                                <td class="text-muted small"><?= (int)$f['fpa_id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($f['fpa_nombre']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <code><?= htmlspecialchars($f['fpa_codigo_sri']) ?></code>
                                </td>
                                <td class="text-muted small">
                                    <?= htmlspecialchars($codigosSri[$f['fpa_codigo_sri']] ?? '—') ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $f['fpa_estado'] === 'ACTIVO' ? 'success' : 'secondary' ?> estado-badge">
                                        <?= $f['fpa_estado'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <!-- Editar -->
                                        <button type="button"
                                                class="btn btn-outline-primary btn-editar"
                                                title="Editar"
                                                data-id="<?= (int)$f['fpa_id'] ?>"
                                                data-nombre="<?= htmlspecialchars($f['fpa_nombre'], ENT_QUOTES) ?>"
                                                data-codigo="<?= htmlspecialchars($f['fpa_codigo_sri'], ENT_QUOTES) ?>">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <!-- Activar / Inactivar -->
                                        <button type="button"
                                                class="btn btn-outline-<?= $f['fpa_estado'] === 'ACTIVO' ? 'warning' : 'success' ?> btn-toggle"
                                                title="<?= $f['fpa_estado'] === 'ACTIVO' ? 'Inactivar' : 'Activar' ?>"
                                                data-id="<?= (int)$f['fpa_id'] ?>"
                                                data-nombre="<?= htmlspecialchars($f['fpa_nombre'], ENT_QUOTES) ?>"
                                                data-estado="<?= $f['fpa_estado'] ?>"
                                                data-url="<?= htmlspecialchars(url('facturacion', 'formaPago', 'toggleEstado')) ?>">
                                            <i class="fas fa-<?= $f['fpa_estado'] === 'ACTIVO' ? 'ban' : 'check' ?>"></i>
                                        </button>
                                        <!-- Eliminar -->
                                        <button type="button"
                                                class="btn btn-outline-danger btn-eliminar"
                                                title="Eliminar"
                                                data-id="<?= (int)$f['fpa_id'] ?>"
                                                data-nombre="<?= htmlspecialchars($f['fpa_nombre'], ENT_QUOTES) ?>"
                                                data-url="<?= htmlspecialchars(url('facturacion', 'formaPago', 'eliminar')) ?>">
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
                Total: <strong><?= count($formas) ?></strong> forma(s) de pago
            </div>
        </div>

    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- MODAL: Crear / Editar Forma de Pago                                    -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalFormaPago" tabindex="-1" aria-labelledby="modalFormaPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalFormaPagoLabel">
                    <i class="fas fa-credit-card mr-2"></i>
                    <span id="modal-titulo">Nueva Forma de Pago</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="form-forma-pago" novalidate>
                <div class="modal-body">

                    <input type="hidden" id="fpa_id" name="fpa_id" value="0">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <!-- Nombre -->
                    <div class="form-group">
                        <label for="fpa_nombre">
                            <strong>Nombre <span class="text-danger">*</span></strong>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="fpa_nombre"
                               name="fpa_nombre"
                               maxlength="50"
                               placeholder="Ej: Transferencia Bancaria"
                               required
                               autocomplete="off">
                        <small class="text-muted">Máximo 50 caracteres</small>
                    </div>

                    <!-- Código SRI -->
                    <div class="form-group">
                        <label for="fpa_codigo_sri">
                            <strong>Código SRI (Tabla 24) <span class="text-danger">*</span></strong>
                        </label>
                        <select class="form-control" id="fpa_codigo_sri" name="fpa_codigo_sri" required>
                            <option value="">-- Seleccionar código --</option>
                            <?php foreach ($codigosSri as $codigo => $descripcion): ?>
                                <option value="<?= htmlspecialchars($codigo) ?>">
                                    <?= htmlspecialchars($codigo) ?> — <?= htmlspecialchars($descripcion) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            Código requerido para la factura electrónica SRI
                        </small>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn-guardar-modal">
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

    var urlGuardar      = <?= json_encode(url('facturacion', 'formaPago', 'guardar')) ?>;
    var urlToggle       = <?= json_encode(url('facturacion', 'formaPago', 'toggleEstado')) ?>;
    var urlEliminar     = <?= json_encode(url('facturacion', 'formaPago', 'eliminar')) ?>;
    var csrfToken       = <?= json_encode($csrf_token) ?>;

    var Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });

    // ── Abrir modal CREAR ────────────────────────────────────────────────────
    $('#btn-nueva-forma').on('click', function () {
        $('#modal-titulo').text('Nueva Forma de Pago');
        $('#fpa_id').val(0);
        $('#fpa_nombre').val('');
        $('#fpa_codigo_sri').val('');
        $('#form-forma-pago').removeClass('was-validated');
        $('#modalFormaPago').modal('show');
        setTimeout(function () { $('#fpa_nombre').focus(); }, 350);
    });

    // ── Abrir modal EDITAR ───────────────────────────────────────────────────
    $(document).on('click', '.btn-editar', function () {
        var id     = $(this).data('id');
        var nombre = $(this).data('nombre');
        var codigo = String($(this).data('codigo'));

        $('#modal-titulo').text('Editar Forma de Pago');
        $('#fpa_id').val(id);
        $('#fpa_nombre').val(nombre);
        $('#fpa_codigo_sri').val(codigo);
        $('#form-forma-pago').removeClass('was-validated');
        $('#modalFormaPago').modal('show');
        setTimeout(function () { $('#fpa_nombre').focus(); }, 350);
    });

    // ── Guardar (crear / editar) ─────────────────────────────────────────────
    $('#form-forma-pago').on('submit', function (e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            $(this).addClass('was-validated');
            return;
        }

        var $btn = $('#btn-guardar-modal').prop('disabled', true)
                      .html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

        $.ajax({
            url: urlGuardar,
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    $('#modalFormaPago').modal('hide');
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    Toast.fire({ icon: 'error', title: res.message || 'Error al guardar' });
                }
            },
            error: function () {
                Toast.fire({ icon: 'error', title: 'Error de comunicación con el servidor' });
            },
            complete: function () {
                $btn.prop('disabled', false)
                    .html('<i class="fas fa-save mr-1"></i> Guardar');
            }
        });
    });

    // ── Activar / Inactivar ──────────────────────────────────────────────────
    $(document).on('click', '.btn-toggle', function () {
        var fpaId   = $(this).data('id');
        var nombre  = $(this).data('nombre');
        var estado  = $(this).data('estado');
        var accion  = estado === 'ACTIVO' ? 'inactivar' : 'activar';
        var $btn    = $(this);

        Swal.fire({
            title: (accion === 'inactivar' ? 'Inactivar' : 'Activar') + ' forma de pago',
            html: '<p>¿Desea <strong>' + accion + '</strong> la forma de pago:</p>' +
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
                url: urlToggle,
                method: 'POST',
                data: { fpa_id: fpaId, csrf_token: csrfToken },
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
        var fpaId  = $(this).data('id');
        var nombre = $(this).data('nombre');

        Swal.fire({
            title: 'Eliminar forma de pago',
            html: '<p>¿Está seguro de eliminar:</p>' +
                  '<p><strong>' + $('<span>').text(nombre).html() + '</strong>?</p>' +
                  '<p class="text-muted small">Esta acción no se puede deshacer.</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Sí, eliminar',
            cancelButtonText: 'Cancelar',
            focusCancel: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: urlEliminar,
                method: 'POST',
                data: { fpa_id: fpaId, csrf_token: csrfToken },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        Toast.fire({ icon: 'success', title: res.message });
                        $('#fila-' + fpaId).fadeOut(400, function () { $(this).remove(); });
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
