<?php
/**
 * Vista de Asistencia - Módulo Fútbol
 * @vars $grupos, $asistencias, $fecha_actual, $grupo_seleccionado, $sedes, $sede_activa, $csrf_token, $modulo_actual
 */
$moduloColor = '#22C55E';
$moduloIcon = 'fas fa-futbol';
$fecha_actual = $fecha ?? date('Y-m-d');
$alumnos = $alumnos ?? [];
$grupoId = $grupoId ?? 0;
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcon ?>" style="color: <?= $moduloColor ?>"></i>
                    Control de Asistencia
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Asistencia</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- Controles superiores -->
        <div class="card">
            <div class="card-header" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtroSede"><i class="fas fa-building"></i> Sede</label>
                            <select class="form-control" id="filtroSede" disabled>
                                <option value="">Sede actual</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fechaAsistencia"><i class="fas fa-calendar-alt"></i> Fecha</label>
                            <input type="date" class="form-control" id="fechaAsistencia" value="<?= $fecha_actual ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="grupoAsistencia"><i class="fas fa-users"></i> Grupo</label>
                            <select class="form-control" id="grupoAsistencia">
                                <option value="">Seleccionar grupo</option>
                                <?php if (!empty($grupos)): ?>
                                    <?php foreach ($grupos as $grupo): ?>
                                        <option value="<?= $grupo['fgr_grupo_id'] ?>" <?= (isset($grupoId) && $grupoId == $grupo['fgr_grupo_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($grupo['fgr_nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="button" class="btn btn-success btn-block" onclick="cargarAsistencia()">
                                <i class="fas fa-search"></i> Cargar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones masivas -->
        <div class="row mb-3" id="accionesMasivas" style="display: none;">
            <div class="col-12">
                <button type="button" class="btn btn-outline-success btn-sm" onclick="marcarTodos('PRESENTE')">
                    <i class="fas fa-check-double"></i> Marcar todos presente
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm ml-2" onclick="marcarTodos('AUSENTE')">
                    <i class="fas fa-times"></i> Marcar todos ausente
                </button>
                <button type="button" class="btn btn-primary btn-sm float-right" onclick="guardarAsistencia()">
                    <i class="fas fa-save"></i> Guardar Asistencia
                </button>
            </div>
        </div>

        <!-- Tabla de Asistencia -->
        <div class="card">
            <div class="card-header" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Registro de Asistencia</h3>
            </div>
            <div class="card-body" id="contenedorAsistencia">
                <?php if (!empty($alumnos)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="tablaAsistencia">
                            <thead>
                                <tr>
                                    <th width="40">#</th>
                                    <th>Alumno</th>
                                    <th width="180">Estado</th>
                                    <th>Observaciones</th>
                                    <th width="100">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alumnos as $i => $asist): ?>
                                    <tr data-alumno-id="<?= $asist['alu_alumno_id'] ?? '' ?>" data-inscripcion-id="<?= $asist['fin_inscripcion_id'] ?? '' ?>">
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($asist['nombre'] ?? '') ?></td>
                                        <td>
                                            <select class="form-control form-control-sm estado-asistencia"
                                                    data-inscripcion-id="<?= $asist['fin_inscripcion_id'] ?? '' ?>">
                                                <?php
                                                $estados = ['PRESENTE' => 'Presente', 'AUSENTE' => 'Ausente', 'TARDANZA' => 'Tardanza', 'JUSTIFICADO' => 'Justificado'];
                                                foreach ($estados as $val => $label):
                                                ?>
                                                    <option value="<?= $val ?>" <?= (isset($asist['estado_asistencia']) && $asist['estado_asistencia'] == $val) ? 'selected' : '' ?>>
                                                        <?= $label ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm obs-asistencia"
                                                   data-inscripcion-id="<?= $asist['fin_inscripcion_id'] ?? '' ?>"
                                                   value="<?= htmlspecialchars($asist['fas_observacion'] ?? '') ?>"
                                                   placeholder="Observaciones...">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" title="Editar detalle"
                                                    onclick="editarAsistenciaDetalle({alumno_id:'<?= $asist['alu_alumno_id'] ?? '' ?>', alumno_nombre:'<?= htmlspecialchars(addslashes($asist['nombre'] ?? '')) ?>', estado:'<?= $asist['estado_asistencia'] ?? 'PRESENTE' ?>', observaciones:'<?= htmlspecialchars(addslashes($asist['fas_observacion'] ?? '')) ?>'})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-check fa-3x opacity-50 text-muted mb-3"></i>
                        <p class="text-muted">Seleccione una fecha y grupo para cargar la asistencia.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- Modal Asistencia Detalle -->
<div class="modal fade" id="modalAsistencia" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: <?= $moduloColor ?>; color: #fff;">
                <h5 class="modal-title">
                    <i class="<?= $moduloIcon ?>"></i> Detalle de Asistencia
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAsistenciaDetalle">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                <input type="hidden" name="alumno_id" id="det_alumno_id">
                <input type="hidden" name="grupo_id" id="det_grupo_id">
                <input type="hidden" name="fecha" id="det_fecha">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Alumno</label>
                        <input type="text" class="form-control" id="det_alumno_nombre" readonly>
                    </div>
                    <div class="form-group">
                        <label for="det_estado">Estado <span class="text-danger">*</span></label>
                        <select class="form-control" id="det_estado" name="estado" required>
                            <option value="PRESENTE">Presente</option>
                            <option value="AUSENTE">Ausente</option>
                            <option value="TARDANZA">Tardanza</option>
                            <option value="JUSTIFICADO">Justificado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="det_observaciones">Observaciones</label>
                        <textarea class="form-control" id="det_observaciones" name="observaciones" rows="3" placeholder="Motivo de ausencia, tardanza, etc."></textarea>
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
    // Mostrar acciones masivas si hay datos
    if ($('#tablaAsistencia tbody tr').length > 0) {
        $('#accionesMasivas').show();
    }

    // Colorear selects de estado
    $('.estado-asistencia').each(function() { colorearEstado($(this)); });
    $('.estado-asistencia').on('change', function() { colorearEstado($(this)); });

    // Submit detalle
    $('#formAsistenciaDetalle').on('submit', function(e) {
        e.preventDefault();
        var alumnoId = $('#det_alumno_id').val();
        var estado = $('#det_estado').val();
        var obs = $('#det_observaciones').val();
        // Actualizar en la tabla
        $('select.estado-asistencia[data-alumno-id="' + alumnoId + '"]').val(estado).trigger('change');
        $('input.obs-asistencia[data-alumno-id="' + alumnoId + '"]').val(obs);
        $('#modalAsistencia').modal('hide');
    });
});

function colorearEstado(select) {
    var colores = { PRESENTE: '#28a745', AUSENTE: '#dc3545', TARDANZA: '#ffc107', JUSTIFICADO: '#17a2b8' };
    select.css('border-left', '4px solid ' + (colores[select.val()] || '#ccc'));
}

function cargarAsistencia() {
    var fecha = $('#fechaAsistencia').val();
    var grupoId = $('#grupoAsistencia').val();
    if (!fecha || !grupoId) {
        Swal.fire('Atención', 'Seleccione fecha y grupo.', 'warning');
        return;
    }
    var url = '<?= url("futbol", "asistencia", "index") ?>&fecha=' + fecha + '&grupo_id=' + grupoId;
    window.location.href = url;
}

function guardarAsistencia() {
    var fecha = $('#fechaAsistencia').val();
    var grupoId = $('#grupoAsistencia').val();
    if (!fecha || !grupoId) {
        Swal.fire('Atención', 'Seleccione fecha y grupo.', 'warning');
        return;
    }

    var asistencia = {};
    var obsData = {};
    $('#tablaAsistencia tbody tr').each(function() {
        var inscId = $(this).data('inscripcion-id');
        var estado = $(this).find('.estado-asistencia').val();
        var obs = $(this).find('.obs-asistencia').val();
        asistencia[inscId] = estado;
        if (obs) obsData['obs_' + inscId] = obs;
    });

    var postData = {
        csrf_token: '<?= $csrf_token ?? "" ?>',
        fecha: fecha,
        grupo_id: grupoId,
        asistencia: asistencia
    };
    $.extend(postData, obsData);

    $.ajax({
        url: '<?= url("futbol", "asistencia", "guardar") ?>',
        method: 'POST',
        data: postData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('¡Guardado!', response.message || 'Asistencia registrada correctamente.', 'success');
            } else {
                Swal.fire('Error', response.message || 'No se pudo guardar.', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
        }
    });
}

function marcarTodos(estado) {
    $('.estado-asistencia').val(estado).trigger('change');
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: 'Todos marcados como ' + estado,
        showConfirmButton: false,
        timer: 1500
    });
}

function editarAsistenciaDetalle(obj) {
    $('#det_alumno_id').val(obj.alumno_id || '');
    $('#det_grupo_id').val($('#grupoAsistencia').val());
    $('#det_fecha').val($('#fechaAsistencia').val());
    $('#det_alumno_nombre').val(obj.alumno_nombre || '');
    $('#det_estado').val(obj.estado || 'PRESENTE');
    $('#det_observaciones').val(obj.observaciones || '');
    $('#modalAsistencia').modal('show');
}
</script>
<?php $scripts = ob_get_clean(); ?>
