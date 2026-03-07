<?php
/**
 * Vista de Inscripciones - Módulo Fútbol
 * @vars $inscripciones, $grupos, $periodos, $sedes, $sede_activa, $csrf_token, $modulo_actual
 */
$moduloColor = '#22C55E';
$moduloIcon  = 'fas fa-futbol';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcon ?>" style="color: <?= $moduloColor ?>"></i>
                    Inscripciones de Fútbol
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Inscripciones</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Filtro y botón -->
        <div class="row mb-3">
            <div class="col-md-4">
                <form method="POST" action="<?= url('futbol', 'inscripcion', 'index') ?>" id="formFiltroEstado" style="display: inline;">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-filter"></i></span>
                        </div>
                        <select class="form-control" name="estado" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="ACTIVA"     <?= (isset($estadoFiltro) && $estadoFiltro == 'ACTIVA')     ? 'selected' : '' ?>>Activa</option>
                            <option value="SUSPENDIDA" <?= (isset($estadoFiltro) && $estadoFiltro == 'SUSPENDIDA') ? 'selected' : '' ?>>Suspendida</option>
                            <option value="CANCELADA"  <?= (isset($estadoFiltro) && $estadoFiltro == 'CANCELADA')  ? 'selected' : '' ?>>Cancelada</option>
                            <option value="COMPLETADA" <?= (isset($estadoFiltro) && $estadoFiltro == 'COMPLETADA') ? 'selected' : '' ?>>Completada</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="col-md-8 text-right">
                <button type="button" class="btn btn-success" id="btnNuevaInscripcion">
                    <i class="fas fa-plus"></i> Nueva Inscripción
                </button>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card">
            <div class="card-header" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-list"></i> Listado de Inscripciones</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($inscripciones)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tablaInscripciones">
                        <thead>
                            <tr>
                                <th width="40">#</th>
                                <th>Alumno</th>
                                <th>Grupo</th>
                                <th>Entrenador</th>
                                <th>Notas</th>
                                <th>Fecha Inscripción</th>
                                <th>Estado</th>
                                <th>Monto</th>
                                <th width="150">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscripciones as $i => $insc): ?>
                            <tr data-id="<?= $insc['fin_inscripcion_id'] ?? 0 ?>">
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($insc['alumno'] ?? '') ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?= htmlspecialchars($insc['fgr_color'] ?? $moduloColor) ?>; color: #fff;">
                                        <?= htmlspecialchars($insc['grupo'] ?? '') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($insc['entrenador'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($insc['fin_notas'] ?? '') ?></td>
                                <td><?= isset($insc['fin_fecha_inscripcion']) ? date('d/m/Y', strtotime($insc['fin_fecha_inscripcion'])) : '' ?></td>
                                <td>
                                    <?php
                                    $estadoClass = ['ACTIVA'=>'success','SUSPENDIDA'=>'warning','CANCELADA'=>'danger','COMPLETADA'=>'info'];
                                    $estado = $insc['fin_estado'] ?? 'ACTIVA';
                                    $clase  = $estadoClass[$estado] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?= $clase ?>"><?= $estado ?></span>
                                </td>
                                <td>$<?= number_format($insc['fin_monto'] ?? 0, 2) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-info js-editar-inscripcion" title="Editar"
                                            data-insc="<?= htmlspecialchars(json_encode($insc, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" title="Cambiar Estado">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item js-cambiar-estado" href="#" data-id="<?= $insc['fin_inscripcion_id'] ?? 0 ?>" data-estado="ACTIVA"><i class="fas fa-check text-success"></i> Activa</a>
                                                <a class="dropdown-item js-cambiar-estado" href="#" data-id="<?= $insc['fin_inscripcion_id'] ?? 0 ?>" data-estado="SUSPENDIDA"><i class="fas fa-pause text-warning"></i> Suspendida</a>
                                                <a class="dropdown-item js-cambiar-estado" href="#" data-id="<?= $insc['fin_inscripcion_id'] ?? 0 ?>" data-estado="CANCELADA"><i class="fas fa-times text-danger"></i> Cancelada</a>
                                                <a class="dropdown-item js-cambiar-estado" href="#" data-id="<?= $insc['fin_inscripcion_id'] ?? 0 ?>" data-estado="COMPLETADA"><i class="fas fa-flag-checkered text-info"></i> Completada</a>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-danger js-eliminar-inscripcion" title="Eliminar"
                                            data-id="<?= $insc['fin_inscripcion_id'] ?? 0 ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-futbol fa-3x opacity-50 text-muted mb-3"></i>
                    <p class="text-muted">No hay inscripciones registradas.</p>
                    <button class="btn btn-success btn-sm" id="btnNuevaInscripcionEmpty">
                        <i class="fas fa-plus"></i> Crear primera inscripción
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- Modal Inscripción -->
<div class="modal fade" id="modalInscripcion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: <?= $moduloColor ?>; color: #fff;">
                <h5 class="modal-title" id="modalInscripcionTitle">
                    <i class="<?= $moduloIcon ?>"></i> Nueva Inscripción
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formInscripcion" method="POST"
                data-url-crear="<?= url('futbol', 'inscripcion', 'crear') ?>"
                data-url-editar="<?= url('futbol', 'inscripcion', 'editar') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="fin_id" id="fin_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fin_alumno_id">Alumno <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="fin_alumno_id" name="alumno_id" required style="width: 100%;">
                                    <option value="">Buscar alumno...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fin_grupo_id">Grupo <span class="text-danger">*</span></label>
                                <select class="form-control" id="fin_grupo_id" name="grupo_id" required>
                                    <option value="">Seleccionar grupo</option>
                                    <?php foreach ($grupos as $grupo): ?>
                                    <option value="<?= $grupo['fgr_grupo_id'] ?>"><?= htmlspecialchars($grupo['fgr_nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fin_fecha_inscripcion">Fecha Inscripción <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fin_fecha_inscripcion" name="fin_fecha_inscripcion" required value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fin_monto">Monto ($) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="fin_monto" name="monto_inscripcion" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fin_estado">Estado</label>
                                <select class="form-control" id="fin_estado" name="fin_estado">
                                    <option value="ACTIVA">Activa</option>
                                    <option value="SUSPENDIDA">Suspendida</option>
                                    <option value="CANCELADA">Cancelada</option>
                                    <option value="COMPLETADA">Completada</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="fin_notas">Notas</label>
                        <textarea class="form-control" id="fin_notas" name="notas" rows="3" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
$(function() {
    var Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    var csrfToken = '<?= addslashes($csrf_token ?? '') ?>';
    var hoy = '<?= date("Y-m-d") ?>';
    var moduloIcon = '<?= $moduloIcon ?>';

    // Mover modal al body (AdminLTE z-index)
    $('body').removeClass('hold-transition');
    $('#modalInscripcion').appendTo('body');

    // DataTable
    try {
        if ($.fn.DataTable && $('#tablaInscripciones tbody tr').length > 0) {
            $('#tablaInscripciones').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                order: [[5, 'desc']],
                responsive: true
            });
        }
    } catch(e) { console.warn('DataTable:', e); }

    // Select2 búsqueda de alumnos
    try {
        if ($.fn.select2) {
            $('#fin_alumno_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Buscar alumno...',
                allowClear: true,
                dropdownParent: $('#modalInscripcion'),
                ajax: {
                    url: '<?= url("futbol", "inscripcion", "buscarAlumno") ?>',
                    dataType: 'json',
                    delay: 250,
                    data: function(p) { return { q: p.term }; },
                    processResults: function(d) { return { results: d }; },
                    cache: true
                },
                minimumInputLength: 2
            });
        }
    } catch(e) { console.warn('Select2:', e); }

    // Filtro por estado
    $('#filtroEstado').on('change', function() {
        $('#formFiltroEstado').submit();
    });

    // Helper: limpiar formulario
    function limpiarFormulario() {
        $('#formInscripcion')[0].reset();
        $('#fin_id').val('');
        try { $('#fin_alumno_id').val(null).trigger('change'); } catch(e) {}
        $('#fin_fecha_inscripcion').val(hoy);
        $('#fin_estado').val('ACTIVA');
        $('#modalInscripcionTitle').html('<i class="' + moduloIcon + '"></i> Nueva Inscripción');
        $('#formInscripcion').data('mode', 'crear');
    }

    // Abrir modal nueva
    function abrirModal() {
        limpiarFormulario();
        $('#modalInscripcion').modal('show');
    }

    $('#btnNuevaInscripcion').on('click', abrirModal);
    $('#btnNuevaInscripcionEmpty').on('click', abrirModal);

    // Pre-selección desde botón "Inscribir" de lista de alumnos
    var preAlumno = <?= json_encode($preAlumno ?? null, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    if (preAlumno && preAlumno.alu_alumno_id) {
        limpiarFormulario();
        $('#fin_alumno_id').append(new Option(preAlumno.nombre, preAlumno.alu_alumno_id, true, true)).trigger('change');
        $('#modalInscripcion').modal('show');
    }

    // Editar inscripción
    $(document).on('click', '.js-editar-inscripcion', function() {
        var obj = JSON.parse($(this).attr('data-insc'));
        limpiarFormulario();
        $('#fin_id').val(obj.fin_inscripcion_id);
        if (obj.fin_alumno_id && obj.alumno) {
            $('#fin_alumno_id').append(new Option(obj.alumno, obj.fin_alumno_id, true, true)).trigger('change');
        }
        $('#fin_grupo_id').val(obj.fin_grupo_id || '');
        $('#fin_fecha_inscripcion').val(obj.fin_fecha_inscripcion || hoy);
        $('#fin_monto').val(obj.fin_monto || '');
        $('#fin_estado').val(obj.fin_estado || 'ACTIVA');
        $('#fin_notas').val(obj.fin_notas || '');
        $('#modalInscripcionTitle').html('<i class="' + moduloIcon + '"></i> Editar Inscripción');
        $('#formInscripcion').data('mode', 'editar');
        $('#modalInscripcion').modal('show');
    });

    // Submit crear/editar
    $('#formInscripcion').on('submit', function(e) {
        e.preventDefault();
        var mode = $(this).data('mode') || 'crear';
        var action = $(this).attr(mode === 'editar' ? 'data-url-editar' : 'data-url-crear');
        var $btn = $(this).find('[type=submit]').prop('disabled', true);
        $.post(action, $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalInscripcion').modal('hide');
                Toast.fire({ icon: 'success', title: res.message });
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                Toast.fire({ icon: 'error', title: res.message });
            }
        }, 'json').fail(function() {
            Toast.fire({ icon: 'error', title: 'Error de conexión con el servidor.' });
        }).always(function() { $btn.prop('disabled', false); });
    });

    // Cambiar estado
    $(document).on('click', '.js-cambiar-estado', function(e) {
        e.preventDefault();
        var id     = $(this).data('id');
        var estado = $(this).data('estado');
        Swal.fire({
            title: '¿Cambiar estado?',
            text: 'La inscripción pasará a estado: ' + estado,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '<?= $moduloColor ?>',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $.post('<?= url("futbol", "inscripcion", "editar") ?>', {
                csrf_token: csrfToken, fin_id: id, fin_estado: estado
            }, function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: 'Estado cambiado correctamente.' });
                    setTimeout(function() { location.reload(); }, 1200);
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            }, 'json').fail(function() {
                Toast.fire({ icon: 'error', title: 'Error de conexión.' });
            });
        });
    });

    // Eliminar inscripción
    $(document).on('click', '.js-eliminar-inscripcion', function() {
        var id   = $(this).data('id');
        var $row = $(this).closest('tr');
        Swal.fire({
            title: '¿Eliminar inscripción?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $.post('<?= url("futbol", "inscripcion", "eliminar") ?>', {
                csrf_token: csrfToken, fin_id: id
            }, function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message || 'Inscripción eliminada.' });
                    $row.fadeOut(400, function() { location.reload(); });
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            }, 'json').fail(function() {
                Toast.fire({ icon: 'error', title: 'Error de conexión.' });
            });
        });
    });
});
</script>
<?php $scripts = ob_get_clean(); ?>
