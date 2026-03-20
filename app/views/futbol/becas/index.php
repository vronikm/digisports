<?php
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
$moduloIcono = $modulo_actual['icono'] ?? 'fas fa-futbol';
$moduloNombre = $modulo_actual['nombre'] ?? 'Fútbol';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?>" style="color: <?= $moduloColor ?>"></i>
                    Gestión de Becas
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Becas</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">

        <!-- ============================================================= -->
        <!-- SECCIÓN 1: TIPOS DE BECA -->
        <!-- ============================================================= -->
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-graduation-cap mr-2" style="color: <?= $moduloColor ?>"></i>Tipos de Beca</h4>
                <button class="btn btn-sm text-white js-abrir-modal-beca" style="background-color: <?= $moduloColor ?>">
                    <i class="fas fa-plus mr-1"></i> Nuevo Tipo de Beca
                </button>
            </div>
        </div>

        <?php if (!empty($becas)): ?>
        <div class="row" id="rowBecas">
            <?php foreach ($becas as $beca): ?>
            <div class="col-md-4 col-lg-3">
                <div class="card card-outline<?= $beca['fbe_activo'] ? '' : ' bg-light' ?>"
                     style="border-top-color: <?= $beca['fbe_activo'] ? $moduloColor : '#adb5bd' ?>; <?= $beca['fbe_activo'] ? '' : 'opacity:.75;' ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?= htmlspecialchars($beca['fbe_nombre']) ?></h5>
                            <span class="badge badge-<?= $beca['fbe_activo'] ? 'success' : 'secondary' ?>">
                                <?= $beca['fbe_activo'] ? 'Activa' : 'Inactiva' ?>
                            </span>
                        </div>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($beca['fbe_descripcion'] ?? 'Sin descripción') ?></p>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge badge-info">
                                <?php
                                $tipoBadge = match($beca['fbe_tipo']) {
                                    'PORCENTAJE'  => 'Porcentaje',
                                    'MONTO_FIJO'  => 'Monto Fijo',
                                    'EXONERACION' => 'Exoneración',
                                    default       => $beca['fbe_tipo']
                                };
                                ?>
                                <?= $tipoBadge ?>
                            </span>
                            <span class="font-weight-bold" style="color: <?= $moduloColor ?>; font-size: 1.2em;">
                                <?php if ($beca['fbe_tipo'] === 'EXONERACION'): ?>
                                    100%
                                <?php elseif ($beca['fbe_tipo'] === 'PORCENTAJE'): ?>
                                    <?= number_format($beca['fbe_valor'], 0) ?>%
                                <?php else: ?>
                                    $<?= number_format($beca['fbe_valor'], 2) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="text-muted small mb-3">
                            <i class="fas fa-users mr-1"></i> <?= $beca['total_asignaciones'] ?? 0 ?> asignados
                        </div>
                        <div class="d-flex justify-content-end">
                            <?php if ($beca['fbe_activo']): ?>
                            <button class="btn btn-xs btn-outline-primary mr-1 js-editar-beca"
                                    data-beca="<?= htmlspecialchars(json_encode($beca, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>"
                                    title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-xs btn-outline-danger js-eliminar-beca"
                                    data-id="<?= (int)$beca['fbe_beca_id'] ?>"
                                    title="Desactivar">
                                <i class="fas fa-ban"></i>
                            </button>
                            <?php else: ?>
                            <button class="btn btn-xs btn-outline-success js-activar-beca"
                                    data-id="<?= (int)$beca['fbe_beca_id'] ?>"
                                    title="Activar beca">
                                <i class="fas fa-check-circle mr-1"></i> Activar
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-graduation-cap fa-3x opacity-50 text-muted mb-3"></i>
                        <p class="text-muted">No hay tipos de beca registrados.</p>
                        <button class="btn btn-sm text-white js-abrir-modal-beca" style="background-color: <?= $moduloColor ?>">
                            <i class="fas fa-plus mr-1"></i> Crear primer tipo de beca
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ============================================================= -->
        <!-- SECCIÓN 2: ASIGNACIONES DE BECAS -->
        <!-- ============================================================= -->
        <div class="row mt-4 mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-user-graduate mr-2" style="color: <?= $moduloColor ?>"></i>Asignaciones de Becas</h4>
                <button class="btn btn-sm text-white" id="btnNuevaAsignacion" style="background-color: <?= $moduloColor ?>">
                    <i class="fas fa-plus mr-1"></i> Nueva Asignación
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if (!empty($asignaciones)): ?>
                <div class="table-responsive">
                    <table id="tblAsignaciones" class="table table-bordered table-hover table-striped">
                        <thead style="background-color: <?= $moduloColor ?>; color: #fff;">
                            <tr>
                                <th>#</th>
                                <th>Alumno</th>
                                <th>Beca</th>
                                <th>Tipo</th>
                                <th>Descuento</th>
                                <th>Rubro</th>
                                <th>Fecha Asignación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($asignaciones as $i => $asig): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars(($asig['alu_nombres'] ?? '') . ' ' . ($asig['alu_apellidos'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($asig['fbe_nombre'] ?? '') ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?= ($asig['fbe_tipo'] ?? '') === 'PORCENTAJE' ? 'Porcentaje' : (($asig['fbe_tipo'] ?? '') === 'EXONERACION' ? 'Exoneración' : 'Monto Fijo') ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <?php if (($asig['fbe_tipo'] ?? '') === 'PORCENTAJE' || ($asig['fbe_tipo'] ?? '') === 'EXONERACION'): ?>
                                        <?= number_format($asig['fbe_valor'] ?? 0, 0) ?>%
                                    <?php else: ?>
                                        $<?= number_format($asig['fbe_valor'] ?? 0, 2) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($asig['rub_nombre'])): ?>
                                        <span class="badge badge-light border">
                                            <?= htmlspecialchars($asig['rub_nombre']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">Todos</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($asig['fba_fecha_asignacion'])) ?></td>
                                <td>
                                    <?php
                                    $estadoBadge = match($asig['fba_estado']) {
                                        'ACTIVA'     => 'success',
                                        'SUSPENDIDA' => 'warning',
                                        'VENCIDA'    => 'danger',
                                        'REVOCADA'   => 'dark',
                                        default      => 'secondary'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $estadoBadge ?>"><?= $asig['fba_estado'] ?></span>
                                </td>
                                <td>
                                    <?php if (in_array($asig['fba_estado'], ['ACTIVA', 'SUSPENDIDA'])): ?>
                                    <button class="btn btn-xs btn-outline-primary mr-1 js-editar-asignacion"
                                            data-id="<?= (int)$asig['fba_asignacion_id'] ?>"
                                            data-beca-id="<?= (int)$asig['fba_beca_id'] ?>"
                                            data-fecha-fin="<?= htmlspecialchars($asig['fba_fecha_vencimiento'] ?? '') ?>"
                                            data-motivo="<?= htmlspecialchars($asig['fba_motivo'] ?? '') ?>"
                                            data-estado="<?= htmlspecialchars($asig['fba_estado']) ?>"
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($asig['fba_estado'] === 'ACTIVA'): ?>
                                    <button class="btn btn-xs btn-outline-danger js-revocar"
                                            data-id="<?= (int)$asig['fba_asignacion_id'] ?>"
                                            title="Revocar">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-graduate fa-3x opacity-50 text-muted mb-3"></i>
                    <p class="text-muted">No hay asignaciones de becas registradas.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- ============================================================= -->
<!-- MODAL: TIPO DE BECA -->
<!-- ============================================================= -->
<div class="modal fade" id="modalBeca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: <?= $moduloColor ?>">
                <h5 class="modal-title" id="modalBecaTitle"><i class="fas fa-graduation-cap mr-2"></i>Nuevo Tipo de Beca</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formBeca" method="POST"
                  data-url-crear="<?= url('futbol', 'beca', 'crear') ?>"
                  data-url-editar="<?= url('futbol', 'beca', 'editar') ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="id" id="beca_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="beca_nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" id="beca_tipo" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="PORCENTAJE">Porcentaje</option>
                            <option value="MONTO_FIJO">Monto Fijo</option>
                            <option value="EXONERACION">Exoneración Total</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Valor <span class="text-danger">*</span></label>
                        <input type="number" name="valor" id="beca_valor" class="form-control" step="0.01" min="0" required>
                        <small class="text-muted">Porcentaje: 0–100. Monto fijo: valor en USD. Exoneración: dejar en 100.</small>
                    </div>
                    <div class="form-group">
                        <label>Rubro de Facturación</label>
                        <select name="rubro_id" id="beca_rubro_id" class="form-control">
                            <option value="">— Todos los rubros —</option>
                            <?php if (!empty($rubros)): ?>
                                <?php foreach ($rubros as $rubro): ?>
                                <option value="<?= $rubro['rub_id'] ?>">
                                    <?= htmlspecialchars(($rubro['rub_codigo'] ? '[' . $rubro['rub_codigo'] . '] ' : '') . $rubro['rub_nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">Si se selecciona un rubro, el descuento aplica solo a ese concepto de facturación.</small>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" id="beca_descripcion" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: <?= $moduloColor ?>">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: ASIGNACIÓN DE BECA -->
<!-- ============================================================= -->
<div class="modal fade" id="modalAsignacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: <?= $moduloColor ?>">
                <h5 class="modal-title"><i class="fas fa-user-graduate mr-2"></i>Nueva Asignación</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formAsignacion" method="POST"
                  data-url="<?= url('futbol', 'beca', 'asignar') ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Alumno <span class="text-danger">*</span></label>
                        <select name="alumno_id" id="asignacion_alumno" class="form-control select2" required>
                            <option value="">Seleccione alumno...</option>
                            <?php if (!empty($alumnos)): ?>
                                <?php foreach ($alumnos as $alumno): ?>
                                <option value="<?= $alumno['alu_alumno_id'] ?>"><?= htmlspecialchars(($alumno['alu_nombres'] ?? '') . ' ' . ($alumno['alu_apellidos'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Beca <span class="text-danger">*</span></label>
                        <select name="beca_id" id="asignacion_beca" class="form-control" required>
                            <option value="">Seleccione beca...</option>
                            <?php if (!empty($becas)): ?>
                                <?php foreach ($becas as $beca): ?>
                                    <?php if ($beca['fbe_activo']): ?>
                                    <option value="<?= $beca['fbe_beca_id'] ?>"><?= htmlspecialchars($beca['fbe_nombre']) ?> (<?= $beca['fbe_tipo'] === 'PORCENTAJE' || $beca['fbe_tipo'] === 'EXONERACION' ? $beca['fbe_valor'] . '%' : '$' . number_format($beca['fbe_valor'], 2) ?>)</option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha Fin (opcional)</label>
                        <input type="date" name="fecha_fin" id="asignacion_fecha_fin" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Motivo</label>
                        <textarea name="motivo" id="asignacion_motivo" class="form-control" rows="2" placeholder="Motivo de la asignación..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: <?= $moduloColor ?>">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: EDITAR ASIGNACIÓN -->
<!-- ============================================================= -->
<div class="modal fade" id="modalEditarAsignacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: <?= $moduloColor ?>">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Editar Asignación de Beca</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formEditarAsignacion" method="POST"
                  data-url="<?= url('futbol', 'beca', 'editarAsignacion') ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="asignacion_id" id="edit_asignacion_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Beca <span class="text-danger">*</span></label>
                        <select name="beca_id" id="edit_beca_id" class="form-control" required>
                            <option value="">Seleccione beca...</option>
                            <?php if (!empty($becas)): ?>
                                <?php foreach ($becas as $beca): ?>
                                    <?php if ($beca['fbe_activo']): ?>
                                    <option value="<?= $beca['fbe_beca_id'] ?>"><?= htmlspecialchars($beca['fbe_nombre']) ?> (<?= $beca['fbe_tipo'] === 'PORCENTAJE' || $beca['fbe_tipo'] === 'EXONERACION' ? $beca['fbe_valor'] . '%' : '$' . number_format($beca['fbe_valor'], 2) ?>)</option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" id="edit_estado" class="form-control">
                            <option value="ACTIVA">Activa</option>
                            <option value="SUSPENDIDA">Suspendida</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha Fin (opcional)</label>
                        <input type="date" name="fecha_fin" id="edit_fecha_fin" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Motivo</label>
                        <textarea name="motivo" id="edit_motivo" class="form-control" rows="2" placeholder="Motivo de la asignación..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: <?= $moduloColor ?>">
                        <i class="fas fa-save mr-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- SCRIPTS — sin inline onclick: todo vía jQuery event binding   -->
<!-- ============================================================= -->
<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
$(function() {

    /* ── Toast helper ── */
    var Toast = Swal.mixin({
        toast: true, position: 'top-end',
        showConfirmButton: false, timer: 3000, timerProgressBar: true
    });

    /* ── DataTable asignaciones ── */
    if ($('#tblAsignaciones tbody tr').length) {
        $('#tblAsignaciones').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            responsive: true, order: [[5, 'desc']]
        });
    }

    /* ── Select2 ── */
    if ($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
    }

    /* ── Abrir modal NUEVO tipo de beca ── */
    $(document).on('click', '.js-abrir-modal-beca', function() {
        $('#formBeca')[0].reset();
        $('#beca_id').val('');
        $('#modalBecaTitle').html('<i class="fas fa-graduation-cap mr-2"></i>Nuevo Tipo de Beca');
        $('#formBeca').data('mode', 'crear');
        $('#modalBeca').modal('show');
    });

    /* ── Abrir modal EDITAR beca ── */
    $(document).on('click', '.js-editar-beca', function() {
        var obj = JSON.parse($(this).attr('data-beca'));
        $('#formBeca')[0].reset();
        $('#beca_id').val(obj.fbe_beca_id);
        $('#beca_nombre').val(obj.fbe_nombre);
        $('#beca_tipo').val(obj.fbe_tipo);
        $('#beca_valor').val(obj.fbe_valor);
        $('#beca_rubro_id').val(obj.fbe_rubro_id || '');
        $('#beca_descripcion').val(obj.fbe_descripcion || '');
        $('#modalBecaTitle').html('<i class="fas fa-edit mr-2"></i>Editar Tipo de Beca');
        $('#formBeca').data('mode', 'editar');
        $('#modalBeca').modal('show');
    });

    /* ── SUBMIT: guardar beca (crear / editar) ── */
    $('#formBeca').on('submit', function(e) {
        e.preventDefault();
        var mode   = $(this).data('mode') || 'crear';
        var urlKey = mode === 'editar' ? 'data-url-editar' : 'data-url-crear';
        var action = $(this).attr(urlKey);
        var $btn   = $(this).find('[type=submit]').prop('disabled', true);

        $.post(action, $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalBeca').modal('hide');
                Toast.fire({ icon: 'success', title: res.message });
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('Error', 'Error de conexión al guardar.', 'error');
        }).always(function() { $btn.prop('disabled', false); });
    });

    /* ── Desactivar beca ── */
    $(document).on('click', '.js-eliminar-beca', function() {
        var id    = $(this).data('id');
        var $card = $(this).closest('.col-md-4, .col-lg-3');
        var url   = '<?= url('futbol', 'beca', 'eliminar') ?>';
        var csrf  = '<?= $csrf_token ?>';

        Swal.fire({
            title: '¿Desactivar tipo de beca?',
            text: 'La beca será desactivada y no aparecerá en nuevas asignaciones.',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, desactivar', cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.post(url, { csrf_token: csrf, id: id }, function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    $card.fadeOut(400, function() { $(this).remove(); });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error', 'Error de conexión al desactivar.', 'error');
            });
        });
    });

    /* ── Activar beca inactiva ── */
    $(document).on('click', '.js-activar-beca', function() {
        var id    = $(this).data('id');
        var $card = $(this).closest('.col-md-4, .col-lg-3');
        var url   = '<?= url('futbol', 'beca', 'activar') ?>';
        var csrf  = '<?= $csrf_token ?>';

        Swal.fire({
            title: '¿Activar esta beca?',
            text: 'La beca volverá a estar disponible para nuevas asignaciones.',
            icon: 'question', showCancelButton: true,
            confirmButtonColor: '#22C55E', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, activar', cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.post(url, { csrf_token: csrf, id: id }, function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error', 'Error de conexión al activar.', 'error');
            });
        });
    });

    /* ── Abrir modal NUEVA asignación ── */
    $('#btnNuevaAsignacion').on('click', function() {
        $('#formAsignacion')[0].reset();
        if ($.fn.select2) { $('.select2').val(null).trigger('change'); }
        $('#modalAsignacion').modal('show');
    });

    /* ── SUBMIT: guardar asignación ── */
    $('#formAsignacion').on('submit', function(e) {
        e.preventDefault();
        var action = $(this).attr('data-url');
        var $btn   = $(this).find('[type=submit]').prop('disabled', true);

        $.post(action, $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalAsignacion').modal('hide');
                Toast.fire({ icon: 'success', title: res.message });
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('Error', 'Error de conexión al asignar.', 'error');
        }).always(function() { $btn.prop('disabled', false); });
    });

    /* ── Abrir modal EDITAR asignación ── */
    $(document).on('click', '.js-editar-asignacion', function() {
        var $btn = $(this);
        $('#edit_asignacion_id').val($btn.data('id'));
        $('#edit_beca_id').val($btn.data('beca-id'));
        $('#edit_estado').val($btn.data('estado'));
        var fechaFin = $btn.data('fecha-fin') || '';
        $('#edit_fecha_fin').val(fechaFin ? fechaFin.substring(0, 10) : '');
        $('#edit_motivo').val($btn.data('motivo') || '');
        $('#modalEditarAsignacion').modal('show');
    });

    /* ── SUBMIT: editar asignación ── */
    $('#formEditarAsignacion').on('submit', function(e) {
        e.preventDefault();
        var action = $(this).attr('data-url');
        var $btn   = $(this).find('[type=submit]').prop('disabled', true);

        $.post(action, $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalEditarAsignacion').modal('hide');
                Toast.fire({ icon: 'success', title: res.message });
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('Error', 'Error de conexión al guardar.', 'error');
        }).always(function() { $btn.prop('disabled', false); });
    });

    /* ── Revocar asignación ── */
    $(document).on('click', '.js-revocar', function() {
        var id   = $(this).data('id');
        var $btn = $(this);
        var url  = '<?= url('futbol', 'beca', 'revocar') ?>';
        var csrf = '<?= $csrf_token ?>';

        Swal.fire({
            title: '¿Revocar asignación?',
            text: 'Se revocará la beca asignada al alumno.',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, revocar', cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.post(url, { csrf_token: csrf, asignacion_id: id }, function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    var $row = $btn.closest('tr');
                    $row.find('td:nth-child(7) .badge')
                        .removeClass('badge-success badge-warning badge-danger badge-dark badge-secondary')
                        .addClass('badge-dark').text('REVOCADA');
                    $btn.remove();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error', 'Error de conexión al revocar.', 'error');
            });
        });
    });

});
</script>
<?php $scripts = ob_get_clean(); ?>
