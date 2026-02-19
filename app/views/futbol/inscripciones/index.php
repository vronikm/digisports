<?php
/**
 * Vista de Inscripciones - Módulo Fútbol
 * @vars $inscripciones, $grupos, $periodos, $sedes, $sede_activa, $csrf_token, $modulo_actual
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

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- Filtro de Estado y botón -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-filter"></i></span>
                    </div>
                    <select class="form-control" id="filtroEstado" onchange="filtrarPorEstado(this.value)">
                        <option value="">Todos los estados</option>
                        <option value="ACTIVA" <?= (isset($estadoFiltro) && $estadoFiltro == 'ACTIVA') ? 'selected' : '' ?>>Activa</option>
                        <option value="SUSPENDIDA" <?= (isset($estadoFiltro) && $estadoFiltro == 'SUSPENDIDA') ? 'selected' : '' ?>>Suspendida</option>
                        <option value="CANCELADA" <?= (isset($estadoFiltro) && $estadoFiltro == 'CANCELADA') ? 'selected' : '' ?>>Cancelada</option>
                        <option value="COMPLETADA" <?= (isset($estadoFiltro) && $estadoFiltro == 'COMPLETADA') ? 'selected' : '' ?>>Completada</option>
                    </select>
                </div>
            </div>
            <div class="col-md-8 text-right">
                <button type="button" class="btn btn-success" onclick="abrirNuevaInscripcion()">
                    <i class="fas fa-plus"></i> Nueva Inscripción
                </button>
            </div>
        </div>

        <!-- Tabla de Inscripciones -->
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
                                    <tr>
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
                                            $estadoClass = [
                                                'ACTIVA' => 'success',
                                                'SUSPENDIDA' => 'warning',
                                                'CANCELADA' => 'danger',
                                                'COMPLETADA' => 'info'
                                            ];
                                            $estado = $insc['fin_estado'] ?? 'ACTIVA';
                                            $clase = $estadoClass[$estado] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?= $clase ?>"><?= $estado ?></span>
                                        </td>
                                        <td>$<?= number_format($insc['fin_monto'] ?? 0, 2) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-info" title="Editar"
                                                    onclick='editarInscripcion(<?= json_encode($insc) ?>)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" title="Cambiar Estado">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="#" onclick="cambiarEstado(<?= $insc['fin_inscripcion_id'] ?? 0 ?>, 'ACTIVA')"><i class="fas fa-check text-success"></i> Activa</a>
                                                        <a class="dropdown-item" href="#" onclick="cambiarEstado(<?= $insc['fin_inscripcion_id'] ?? 0 ?>, 'SUSPENDIDA')"><i class="fas fa-pause text-warning"></i> Suspendida</a>
                                                        <a class="dropdown-item" href="#" onclick="cambiarEstado(<?= $insc['fin_inscripcion_id'] ?? 0 ?>, 'CANCELADA')"><i class="fas fa-times text-danger"></i> Cancelada</a>
                                                        <a class="dropdown-item" href="#" onclick="cambiarEstado(<?= $insc['fin_inscripcion_id'] ?? 0 ?>, 'COMPLETADA')"><i class="fas fa-flag-checkered text-info"></i> Completada</a>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-danger" title="Eliminar"
                                                    onclick="eliminarInscripcion(<?= $insc['fin_inscripcion_id'] ?? 0 ?>)">
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
                        <button class="btn btn-success btn-sm" onclick="abrirNuevaInscripcion()">
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
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" onclick="cerrarModalInscripcion()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formInscripcion" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="cerrarModalInscripcion()">Cancelar</button>
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
    // Asegurar que hold-transition se remueva (AdminLTE lo necesita para modales)
    $('body').removeClass('hold-transition');

    // IMPORTANTE: Mover el modal al body para evitar problemas de z-index/overflow con AdminLTE
    if ($('#modalInscripcion').length) {
        $('#modalInscripcion').appendTo('body');
    }

    // Inicializar DataTable
    try {
        if ($.fn.DataTable && $('#tablaInscripciones').length && $('#tablaInscripciones tbody tr').length > 0 && !$('#tablaInscripciones tbody .text-center').length) {
            $('#tablaInscripciones').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                order: [[5, 'desc']],
                responsive: true
            });
        }
    } catch(e) { console.warn('DataTable init error:', e); }

    // Inicializar Select2 para búsqueda de alumnos
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
                    data: function(params) {
                        return { q: params.term };
                    },
                    processResults: function(data) {
                        return { results: data };
                    },
                    cache: true
                },
                minimumInputLength: 2
            });
        }
    } catch(e) { console.warn('Select2 init error:', e); }

    // Submit del formulario
    $('#formInscripcion').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var id = $('#fin_id').val();
        var urlAction = id
            ? '<?= url("futbol", "inscripcion", "editar") ?>'
            : '<?= url("futbol", "inscripcion", "crear") ?>';

        $.post(urlAction, formData, function(response) {
            if (response.success) {
                Swal.fire('¡Éxito!', response.message || 'Inscripción guardada correctamente.', 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', response.message || 'No se pudo guardar la inscripción.', 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
        });
    });
});

// Función para abrir el modal de nueva inscripción con fallback robusto
function abrirNuevaInscripcion() {
    limpiarFormulario();
    var modal = document.getElementById('modalInscripcion');
    if (!modal) { alert('Error: no se encontró el formulario de inscripción.'); return; }
    
    // Remover hold-transition que bloquea animaciones de modales
    document.body.classList.remove('hold-transition');
    
    // Mover modal al body si aún está dentro de content-wrapper
    if (modal.closest('.content-wrapper')) {
        document.body.appendChild(modal);
    }
    
    // Método 1: Intentar con jQuery/Bootstrap
    try {
        if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.modal) {
            jQuery('#modalInscripcion').modal('show');
            // Verificar si se abrió después de 300ms, si no, usar fallback
            setTimeout(function() {
                if (modal.style.display !== 'block' && !modal.classList.contains('show')) {
                    abrirModalManual(modal);
                }
            }, 300);
            return;
        }
    } catch(e) {
        console.warn('Bootstrap modal failed, using fallback:', e);
    }
    // Método 2: Fallback manual sin Bootstrap
    abrirModalManual(modal);
}

function abrirModalManual(modal) {
    if (!modal) modal = document.getElementById('modalInscripcion');
    if (!modal) return;
    modal.style.display = 'block';
    modal.classList.add('show');
    modal.setAttribute('aria-modal', 'true');
    modal.removeAttribute('aria-hidden');
    modal.style.paddingRight = '17px';
    // Crear backdrop
    if (!document.getElementById('modalBackdropFallback')) {
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'modalBackdropFallback';
        backdrop.onclick = function() { cerrarModalInscripcion(); };
        document.body.appendChild(backdrop);
    }
    document.body.classList.add('modal-open');
    document.body.style.overflow = 'hidden';
    document.body.style.paddingRight = '17px';
}

function limpiarFormulario() {
    var form = document.getElementById('formInscripcion');
    if (form) form.reset();
    var finId = document.getElementById('fin_id');
    if (finId) finId.value = '';
    try { if (typeof jQuery !== 'undefined') jQuery('#fin_alumno_id').val(null).trigger('change'); } catch(e) {}
    var fecha = document.getElementById('fin_fecha_inscripcion');
    if (fecha) fecha.value = '<?= date("Y-m-d") ?>';
    var estado = document.getElementById('fin_estado');
    if (estado) estado.value = 'ACTIVA';
    var titulo = document.getElementById('modalInscripcionTitle');
    if (titulo) titulo.innerHTML = '<i class="<?= $moduloIcon ?>"></i> Nueva Inscripción';
}

function editarInscripcion(obj) {
    limpiarFormulario();
    $('#fin_id').val(obj.fin_inscripcion_id);
    // Setear alumno en Select2
    if (obj.fin_alumno_id && obj.alumno) {
        var option = new Option(obj.alumno, obj.fin_alumno_id, true, true);
        $('#fin_alumno_id').append(option).trigger('change');
    }
    $('#fin_grupo_id').val(obj.fin_grupo_id || obj.grupo_id || '');
    $('#fin_fecha_inscripcion').val(obj.fin_fecha_inscripcion || '');
    $('#fin_monto').val(obj.fin_monto || '');
    $('#fin_estado').val(obj.fin_estado || 'ACTIVA');
    $('#fin_notas').val(obj.fin_notas || '');
    $('#modalInscripcionTitle').html('<i class="<?= $moduloIcon ?>"></i> Editar Inscripción');
    abrirModalInscripcion();
}

function cerrarModalInscripcion() {
    var modal = document.getElementById('modalInscripcion');
    if (!modal) return;
    try {
        if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
            jQuery('#modalInscripcion').modal('hide');
        }
    } catch(e) {}
    // Fallback manual
    modal.classList.remove('show');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    modal.removeAttribute('aria-modal');
    var backdrop = document.getElementById('modalBackdropFallback');
    if (backdrop) backdrop.remove();
    document.body.classList.remove('modal-open');
    document.body.style.paddingRight = '';
}

// Alias para abrir modal desde editarInscripcion
function abrirModalInscripcion() {
    var modal = document.getElementById('modalInscripcion');
    if (!modal) return;
    document.body.classList.remove('hold-transition');
    if (modal.closest('.content-wrapper')) {
        document.body.appendChild(modal);
    }
    try {
        if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.modal) {
            jQuery('#modalInscripcion').modal('show');
            setTimeout(function() {
                if (modal.style.display !== 'block' && !modal.classList.contains('show')) {
                    abrirModalManual(modal);
                }
            }, 300);
            return;
        }
    } catch(e) {}
    abrirModalManual(modal);
}

function eliminarInscripcion(id) {
    Swal.fire({
        title: '¿Eliminar inscripción?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= url("futbol", "inscripcion", "eliminar") ?>', {
                csrf_token: '<?= $csrf_token ?? "" ?>',
                fin_id: id
            }, function(response) {
                if (response.success) {
                    Swal.fire('Eliminada', response.message || 'Inscripción eliminada.', 'success')
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

function cambiarEstado(id, estado) {
    Swal.fire({
        title: '¿Cambiar estado?',
        text: 'La inscripción pasará a estado: ' + estado,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '<?= $moduloColor ?>',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= url("futbol", "inscripcion", "editar") ?>', {
                csrf_token: '<?= $csrf_token ?? "" ?>',
                fin_id: id,
                fin_estado: estado
            }, function(response) {
                if (response.success) {
                    Swal.fire('¡Actualizado!', 'Estado cambiado correctamente.', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message || 'No se pudo cambiar el estado.', 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error', 'Error de conexión.', 'error');
            });
        }
    });
}

function filtrarPorSede(sedeId) {
    $.post('<?= url("futbol", "sede", "seleccionar") ?>', { sede_id: sedeId, csrf_token: '<?= $csrf_token ?? "" ?>' }, function() { location.reload(); }, 'json');
}

function filtrarPorEstado(estado) {
    var url = '<?= url("futbol", "inscripcion", "index") ?>';
    if (estado) url += '&estado=' + estado;
    window.location.href = url;
}
</script>
<?php $scripts = ob_get_clean(); ?>
