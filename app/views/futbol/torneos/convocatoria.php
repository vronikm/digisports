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
                    Convocatoria de Torneo
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'torneo', 'index') ?>">Torneos</a></li>
                    <li class="breadcrumb-item active">Convocatoria</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">

        <!-- Botón Volver -->
        <div class="mb-3">
            <a href="<?= url('futbol', 'torneo', 'index') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Volver a Torneos
            </a>
        </div>

        <!-- Info del Torneo -->
        <?php if (!empty($torneo)): ?>
        <div class="card card-outline" style="border-top-color: <?= $moduloColor ?>">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h4 class="mb-1" style="color: <?= $moduloColor ?>">
                            <i class="fas fa-trophy mr-2"></i><?= htmlspecialchars($torneo['fto_nombre']) ?>
                        </h4>
                        <p class="text-muted mb-0"><?= htmlspecialchars($torneo['fto_descripcion'] ?? '') ?></p>
                    </div>
                    <div class="col-md-2 text-center">
                        <?php
                        $tipoBadge = match($torneo['fto_tipo']) {
                            'INTERNO' => 'primary', 'EXTERNO' => 'warning', 'AMISTOSO' => 'info',
                            'LIGA' => 'success', 'COPA' => 'danger', default => 'secondary'
                        };
                        ?>
                        <small class="text-muted d-block">Tipo</small>
                        <span class="badge badge-<?= $tipoBadge ?>"><?= $torneo['fto_tipo'] ?></span>
                    </div>
                    <div class="col-md-3 text-center">
                        <small class="text-muted d-block">Fechas</small>
                        <span><?= date('d/m/Y', strtotime($torneo['fto_fecha_inicio'])) ?></span>
                        <?php if (!empty($torneo['fto_fecha_fin'])): ?>
                            <span class="mx-1">→</span>
                            <span><?= date('d/m/Y', strtotime($torneo['fto_fecha_fin'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3 text-center">
                        <?php
                        $estadoBadge = match($torneo['fto_estado']) {
                            'PLANIFICADO' => 'info', 'EN_CURSO' => 'success',
                            'FINALIZADO' => 'secondary', 'CANCELADO' => 'danger', default => 'secondary'
                        };
                        ?>
                        <small class="text-muted d-block">Estado</small>
                        <span class="badge badge-<?= $estadoBadge ?> badge-lg"><?= str_replace('_', ' ', $torneo['fto_estado']) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabla de Convocados -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-users mr-2" style="color: <?= $moduloColor ?>"></i>Jugadores Convocados</h3>
                <button class="btn btn-sm text-white" style="background-color: <?= $moduloColor ?>" onclick="abrirModalConvocado()">
                    <i class="fas fa-user-plus mr-1"></i> Agregar Jugador
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($jugadores)): ?>
                <div class="table-responsive">
                    <table id="tblConvocados" class="table table-bordered table-hover table-striped">
                        <thead style="background-color: <?= $moduloColor ?>; color: #fff;">
                            <tr>
                                <th>#</th>
                                <th>Jugador</th>
                                <th>Categoría</th>
                                <th>Grupo</th>
                                <th>Posición</th>
                                <th>Dorsal</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jugadores as $i => $jugador): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($jugador['alumno_nombre'] ?? '') ?></td>
                                <td><?= htmlspecialchars($jugador['categoria_nombre'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($jugador['grupo_nombre'] ?? '-') ?></td>
                                <td>
                                    <?php
                                    $posIcono = match($jugador['ftj_posicion'] ?? '') {
                                        'PORTERO'       => 'fas fa-hands',
                                        'DEFENSA'       => 'fas fa-shield-alt',
                                        'MEDIOCAMPISTA' => 'fas fa-running',
                                        'DELANTERO'     => 'fas fa-crosshairs',
                                        default         => 'fas fa-user'
                                    };
                                    ?>
                                    <i class="<?= $posIcono ?> mr-1" style="color: <?= $moduloColor ?>"></i>
                                    <?= $jugador['ftj_posicion'] ?? '-' ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($jugador['ftj_numero'])): ?>
                                        <span class="badge badge-dark"><?= $jugador['ftj_numero'] ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $estConvBadge = match($jugador['ftj_estado']) {
                                        'CONVOCADO'  => 'info',
                                        'CONFIRMADO' => 'success',
                                        'DESCARTADO' => 'secondary',
                                        'LESIONADO'  => 'danger',
                                        default      => 'secondary'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $estConvBadge ?>"><?= $jugador['ftj_estado'] ?></span>
                                </td>
                                <td>
                                    <?php if ($jugador['ftj_estado'] === 'CONVOCADO'): ?>
                                    <button class="btn btn-xs btn-outline-success" onclick="confirmarJugador(<?= $jugador['ftj_id'] ?>)" title="Confirmar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-xs btn-outline-primary" onclick="editarConvocado(<?= htmlspecialchars(json_encode($jugador)) ?>)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-xs btn-outline-danger" onclick="eliminarConvocado(<?= $jugador['ftj_id'] ?>)" title="Quitar">
                                        <i class="fas fa-user-minus"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x opacity-50 text-muted mb-3"></i>
                    <p class="text-muted">No hay jugadores convocados para este torneo.</p>
                    <button class="btn btn-sm text-white" style="background-color: <?= $moduloColor ?>" onclick="abrirModalConvocado()">
                        <i class="fas fa-user-plus mr-1"></i> Agregar primer jugador
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- ============================================================= -->
<!-- MODAL: AGREGAR / EDITAR JUGADOR CONVOCADO -->
<!-- ============================================================= -->
<div class="modal fade" id="modalConvocado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: <?= $moduloColor ?>">
                <h5 class="modal-title" id="modalConvocadoTitle"><i class="fas fa-user-plus mr-2"></i>Agregar Jugador</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formConvocado" method="POST" action="<?= url('futbol', 'torneo', 'agregarJugador') ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="ftj_id" id="convocado_id">
                <input type="hidden" name="fto_torneo_id" value="<?= $torneo['fto_torneo_id'] ?? '' ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Jugador <span class="text-danger">*</span></label>
                        <select name="alumno_id" id="convocado_alumno" class="form-control select2" required>
                            <option value="">Seleccione jugador...</option>
                            <?php if (!empty($alumnos_disponibles)): ?>
                                <?php foreach ($alumnos_disponibles as $alumno): ?>
                                <option value="<?= $alumno['id'] ?>"><?= htmlspecialchars($alumno['nombre_completo'] ?? $alumno['nombre']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Posición <span class="text-danger">*</span></label>
                        <select name="ftj_posicion" id="convocado_posicion" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="PORTERO">Portero</option>
                            <option value="DEFENSA">Defensa</option>
                            <option value="MEDIOCAMPISTA">Mediocampista</option>
                            <option value="DELANTERO">Delantero</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Dorsal</label>
                        <input type="number" name="ftj_numero" id="convocado_dorsal" class="form-control" min="1" max="99" placeholder="Número de camiseta">
                    </div>
                    <div class="form-group">
                        <label>Estado <span class="text-danger">*</span></label>
                        <select name="ftj_estado" id="convocado_estado" class="form-control" required>
                            <option value="CONVOCADO">Convocado</option>
                            <option value="CONFIRMADO">Confirmado</option>
                            <option value="DESCARTADO">Descartado</option>
                            <option value="LESIONADO">Lesionado</option>
                        </select>
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
<!-- SCRIPTS -->
<!-- ============================================================= -->
<?php ob_start(); ?>
<script>
$(document).ready(function() {
    $('#tblConvocados').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        responsive: true,
        order: [[4, 'asc'], [5, 'asc']]
    });

    if ($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#modalConvocado') });
    }
});

function abrirModalConvocado() {
    $('#formConvocado')[0].reset();
    $('#convocado_id').val('');
    $('#convocado_estado').val('CONVOCADO');
    $('#convocado_alumno').prop('disabled', false);
    $('#modalConvocadoTitle').html('<i class="fas fa-user-plus mr-2"></i>Agregar Jugador');
    $('#formConvocado').attr('action', '<?= url('futbol', 'torneo', 'agregarJugador') ?>');
    if ($.fn.select2) { $('.select2').val('').trigger('change'); }
    $('#modalConvocado').modal('show');
}

function editarConvocado(obj) {
    $('#formConvocado')[0].reset();
    $('#convocado_id').val(obj.ftj_id);
    $('#convocado_alumno').val(obj.alumno_id);
    if ($.fn.select2) { $('#convocado_alumno').trigger('change'); }
    $('#convocado_alumno').prop('disabled', true);
    $('#convocado_posicion').val(obj.ftj_posicion);
    $('#convocado_dorsal').val(obj.ftj_numero || '');
    $('#convocado_estado').val(obj.ftj_estado);
    $('#modalConvocadoTitle').html('<i class="fas fa-edit mr-2"></i>Editar Convocado');
    $('#formConvocado').attr('action', '<?= url('futbol', 'torneo', 'agregarJugador') ?>');
    $('#modalConvocado').modal('show');
}

function eliminarConvocado(id) {
    Swal.fire({
        title: '¿Quitar jugador de la convocatoria?',
        text: 'El jugador será removido de este torneo.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, quitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url('futbol', 'torneo', 'quitarJugador') ?>&id=' + id + '&fto_torneo_id=<?= $torneo['fto_torneo_id'] ?? '' ?>&csrf_token=<?= $csrf_token ?>';
        }
    });
}

function confirmarJugador(id) {
    Swal.fire({
        title: '¿Confirmar jugador?',
        text: 'El jugador pasará a estado CONFIRMADO.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '<?= $moduloColor ?>',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url('futbol', 'torneo', 'confirmarJugador') ?>&id=' + id + '&fto_torneo_id=<?= $torneo['fto_torneo_id'] ?? '' ?>&csrf_token=<?= $csrf_token ?>';
        }
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>
