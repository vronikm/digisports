<?php
/**
 * Vista de Evaluaciones - Módulo Fútbol
 * @vars $evaluaciones, $grupos, $categorias, $periodos, $sedes, $sede_activa, $csrf_token, $modulo_actual
 */
$moduloColor = '#22C55E';
$moduloIcon = 'fas fa-futbol';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcon ?>" style="color: <?= $moduloColor ?>"></i>
                    Evaluaciones
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Evaluaciones</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-md-3">
                <select class="form-control" id="filtroGrupo" onchange="aplicarFiltros()">
                    <option value="">Todos los grupos</option>
                    <?php if (!empty($grupos)): ?>
                        <?php foreach ($grupos as $grupo): ?>
                            <option value="<?= $grupo['id'] ?>"><?= htmlspecialchars($grupo['nombre']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control" id="filtroPeriodo" onchange="aplicarFiltros()">
                    <option value="">Todos los periodos</option>
                    <?php if (!empty($periodos)): ?>
                        <?php foreach ($periodos as $periodo): ?>
                            <option value="<?= $periodo['id'] ?>"><?= htmlspecialchars($periodo['nombre']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3 text-right">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalEvaluacion" onclick="limpiarFormulario()">
                    <i class="fas fa-plus"></i> Nueva Evaluación
                </button>
            </div>
        </div>

        <!-- Tabla de Evaluaciones -->
        <div class="card">
            <div class="card-header" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Listado de Evaluaciones</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($evaluaciones)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="tablaEvaluaciones">
                            <thead>
                                <tr>
                                    <th width="40">#</th>
                                    <th>Alumno</th>
                                    <th>Grupo</th>
                                    <th>Categoría</th>
                                    <th>Periodo</th>
                                    <th>Fecha</th>
                                    <th width="200">Calificación</th>
                                    <th>Observaciones</th>
                                    <th>Evaluador</th>
                                    <th width="130">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluaciones as $i => $eval): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($eval['alumno'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($eval['grupo_nombre'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($eval['categoria_nombre'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($eval['periodo_nombre'] ?? '') ?></td>
                                        <td><?= isset($eval['fev_fecha']) ? date('d/m/Y', strtotime($eval['fev_fecha'])) : '' ?></td>
                                        <td>
                                            <?php
                                            $calif = intval($eval['fev_calificacion'] ?? 0);
                                            if ($calif >= 80) {
                                                $barColor = 'bg-success';
                                            } elseif ($calif >= 60) {
                                                $barColor = 'bg-warning';
                                            } else {
                                                $barColor = 'bg-danger';
                                            }
                                            ?>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 mr-2" style="height: 20px;">
                                                    <div class="progress-bar <?= $barColor ?>" role="progressbar"
                                                         style="width: <?= $calif ?>%"
                                                         aria-valuenow="<?= $calif ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?= $calif ?>/100
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span title="<?= htmlspecialchars($eval['fev_observacion'] ?? '') ?>">
                                                <?= htmlspecialchars(mb_strimwidth($eval['fev_observacion'] ?? '', 0, 40, '...')) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($eval['evaluador_nombre'] ?? '') ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-primary" title="Ver Detalle"
                                                    onclick="verDetalle(<?= $eval['fev_evaluacion_id'] ?? 0 ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-info" title="Editar"
                                                    onclick='editarEvaluacion(<?= json_encode($eval) ?>)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" title="Eliminar"
                                                    onclick="eliminarEvaluacion(<?= $eval['fev_evaluacion_id'] ?? 0 ?>)">
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
                        <i class="fas fa-chart-line fa-3x opacity-50 text-muted mb-3"></i>
                        <p class="text-muted">No hay evaluaciones registradas.</p>
                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalEvaluacion" onclick="limpiarFormulario()">
                            <i class="fas fa-plus"></i> Crear primera evaluación
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- Modal Evaluación -->
<div class="modal fade" id="modalEvaluacion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: <?= $moduloColor ?>; color: #fff;">
                <h5 class="modal-title" id="modalEvaluacionTitle">
                    <i class="<?= $moduloIcon ?>"></i> Nueva Evaluación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEvaluacion" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                <input type="hidden" name="fev_id" id="fev_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fev_alumno_id">Alumno <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="fev_alumno_id" name="alumno_id" required style="width: 100%;">
                                    <option value="">Seleccionar alumno...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fev_grupo_id">Grupo <span class="text-danger">*</span></label>
                                <select class="form-control" id="fev_grupo_id" name="grupo_id" required>
                                    <option value="">Seleccionar grupo</option>
                                    <?php if (!empty($grupos)): ?>
                                        <?php foreach ($grupos as $grupo): ?>
                                            <option value="<?= $grupo['fgr_grupo_id'] ?>"><?= htmlspecialchars($grupo['fgr_nombre']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fev_periodo_id">Periodo <span class="text-danger">*</span></label>
                                <select class="form-control" id="fev_periodo_id" name="periodo_id" required>
                                    <option value="">Seleccionar periodo</option>
                                    <?php if (!empty($periodos)): ?>
                                        <?php foreach ($periodos as $periodo): ?>
                                            <option value="<?= $periodo['fpe_periodo_id'] ?>"><?= htmlspecialchars($periodo['fpe_nombre']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fev_fecha">Fecha <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fev_fecha" name="fecha" required value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fev_calificacion">Calificación (0-100) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="fev_calificacion" name="calificacion" min="0" max="100" required>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" id="barraCalificacion" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="fev_observacion">Observaciones</label>
                        <textarea class="form-control" id="fev_observacion" name="observaciones" rows="3" placeholder="Desempeño, áreas de mejora, fortalezas..."></textarea>
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
<script>
$(document).ready(function() {
    // DataTable
    if ($('#tablaEvaluaciones tbody tr').length > 0 && !$('#tablaEvaluaciones tbody .text-center').length) {
        $('#tablaEvaluaciones').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            order: [[5, 'desc']],
            responsive: true
        });
    }

    // Select2 para alumno
    $('#fev_alumno_id').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccionar alumno...',
        allowClear: true,
        dropdownParent: $('#modalEvaluacion'),
        ajax: {
            url: '<?= url("futbol", "evaluacion", "buscarAlumno") ?>',
            dataType: 'json',
            delay: 250,
            data: function(params) { return { q: params.term }; },
            processResults: function(data) { return { results: data }; },
            cache: true
        },
        minimumInputLength: 2
    });

    // Barra de calificación en tiempo real
    $('#fev_calificacion').on('input', function() {
        var val = parseInt($(this).val()) || 0;
        val = Math.min(100, Math.max(0, val));
        var color = val >= 80 ? 'bg-success' : (val >= 60 ? 'bg-warning' : 'bg-danger');
        $('#barraCalificacion').css('width', val + '%').removeClass('bg-success bg-warning bg-danger').addClass(color);
    });

    // Submit
    $('#formEvaluacion').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var id = $('#fev_id').val();
        var urlAction = id
            ? '<?= url("futbol", "evaluacion", "editar") ?>'
            : '<?= url("futbol", "evaluacion", "crear") ?>';

        $.post(urlAction, formData, function(response) {
            if (response.success) {
                Swal.fire('¡Éxito!', response.message || 'Evaluación guardada correctamente.', 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', response.message || 'No se pudo guardar la evaluación.', 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
        });
    });
});

function limpiarFormulario() {
    $('#formEvaluacion')[0].reset();
    $('#fev_id').val('');
    $('#fev_alumno_id').val(null).trigger('change');
    $('#fev_fecha').val('<?= date("Y-m-d") ?>');
    $('#barraCalificacion').css('width', '0%');
    $('#modalEvaluacionTitle').html('<i class="<?= $moduloIcon ?>"></i> Nueva Evaluación');
}

function editarEvaluacion(obj) {
    limpiarFormulario();
    $('#fev_id').val(obj.fev_evaluacion_id);
    if (obj.fev_alumno_id && obj.alumno) {
        var option = new Option(obj.alumno, obj.fev_alumno_id, true, true);
        $('#fev_alumno_id').append(option).trigger('change');
    }
    $('#fev_grupo_id').val(obj.fev_grupo_id || obj.grupo_id || '');
    $('#fev_periodo_id').val(obj.fev_periodo_id || obj.periodo_id || '');
    $('#fev_fecha').val(obj.fev_fecha || '');
    $('#fev_calificacion').val(obj.fev_calificacion || '').trigger('input');
    $('#fev_observacion').val(obj.fev_observacion || '');
    $('#modalEvaluacionTitle').html('<i class="<?= $moduloIcon ?>"></i> Editar Evaluación');
    $('#modalEvaluacion').modal('show');
}

function eliminarEvaluacion(id) {
    Swal.fire({
        title: '¿Eliminar evaluación?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= url("futbol", "evaluacion", "eliminar") ?>', {
                csrf_token: '<?= $csrf_token ?? "" ?>',
                fev_id: id
            }, function(response) {
                if (response.success) {
                    Swal.fire('Eliminada', response.message || 'Evaluación eliminada.', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message || 'No se pudo eliminar.', 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error', 'Error de conexión.', 'error');
            });
        }
    });
}

function verDetalle(id) {
    $.getJSON('<?= url("futbol", "evaluacion", "detalle") ?>&id=' + id, function(r) {
        if (r.success && r.data) {
            var d = r.data;
            Swal.fire({
                title: 'Evaluación de ' + (d.alumno || ''),
                html: '<div class="text-left">' +
                    '<p><strong>Grupo:</strong> ' + (d.grupo_nombre || '—') + '</p>' +
                    '<p><strong>Categoría:</strong> ' + (d.categoria_nombre || '—') + '</p>' +
                    '<p><strong>Periodo:</strong> ' + (d.periodo_nombre || '—') + '</p>' +
                    '<p><strong>Fecha:</strong> ' + (d.fev_fecha || '—') + '</p>' +
                    '<p><strong>Calificación:</strong> ' + (d.fev_calificacion || 0) + '/100</p>' +
                    '<p><strong>Evaluador:</strong> ' + (d.evaluador_nombre || '—') + '</p>' +
                    '<p><strong>Observaciones:</strong> ' + (d.fev_observacion || '—') + '</p>' +
                    '</div>',
                icon: 'info',
                confirmButtonColor: '<?= $moduloColor ?>'
            });
        } else {
            Swal.fire('Error', r.message || 'No encontrada', 'error');
        }
    }).fail(function() { Swal.fire('Error', 'Error de conexión', 'error'); });
}

function aplicarFiltros() {
    var url = '<?= url("futbol", "evaluacion", "index") ?>&';
    var sede = $('#filtroSede').val();
    var grupo = $('#filtroGrupo').val();
    var periodo = $('#filtroPeriodo').val();
    if (sede) url += 'sede=' + sede + '&';
    if (grupo) url += 'grupo_id=' + grupo + '&';
    if (periodo) url += 'periodo_id=' + periodo + '&';
    window.location.href = url.replace(/[&?]$/, '');
}

function filtrarPorSede(sedeId) {
    aplicarFiltros();
}
</script>
<?php $scripts = ob_get_clean(); ?>
