<?php
$moduloColor  = $modulo_actual['color']  ?? '#22C55E';
$moduloIcono  = $modulo_actual['icono']  ?? 'fas fa-futbol';
$torneoId     = $torneo['fto_torneo_id'] ?? 0;
$torneos_selector = $torneos_selector ?? [];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?>" style="color: <?= $moduloColor ?>"></i>
                    <?= $torneo ? 'Convocatoria de Torneo' : 'Convocatorias' ?>
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

<section class="content">
    <div class="container-fluid">

        <div class="mb-3">
            <a href="<?= url('futbol', 'torneo', 'index') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Volver a Torneos
            </a>
        </div>

        <?php if ($torneo === null): ?>
        <!-- Selector de Torneo -->
        <div class="card">
            <div class="card-header" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-trophy mr-2" style="color: <?= $moduloColor ?>"></i>Seleccionar Torneo</h3>
            </div>
            <div class="card-body">
                <?php if (empty($torneos_selector)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-trophy fa-3x mb-3 opacity-50"></i>
                    <p>No hay torneos registrados.</p>
                    <a href="<?= url('futbol', 'torneo', 'index') ?>" class="btn btn-sm text-white" style="background-color: <?= $moduloColor ?>">
                        <i class="fas fa-plus mr-1"></i> Crear Torneo
                    </a>
                </div>
                <?php else: ?>
                <p class="text-muted mb-3">Selecciona un torneo para gestionar su convocatoria.</p>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead style="background-color: <?= $moduloColor ?>; color: #fff;">
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Fechas</th>
                                <th>Estado</th>
                                <th class="text-center">Convocados</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($torneos_selector as $t): ?>
                            <?php
                            $tipoBadge = match($t['fto_tipo']) {
                                'INTERNO' => 'primary', 'EXTERNO' => 'warning', 'AMISTOSO' => 'info',
                                'LIGA' => 'success', 'COPA' => 'danger', default => 'secondary'
                            };
                            $estadoBadge = match($t['fto_estado']) {
                                'PLANIFICADO' => 'info', 'EN_CURSO' => 'success',
                                'FINALIZADO' => 'secondary', 'CANCELADO' => 'danger', default => 'secondary'
                            };
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['fto_nombre']) ?></strong></td>
                                <td><span class="badge badge-<?= $tipoBadge ?>"><?= $t['fto_tipo'] ?></span></td>
                                <td>
                                    <?= date('d/m/Y', strtotime($t['fto_fecha_inicio'])) ?>
                                    <?php if (!empty($t['fto_fecha_fin'])): ?>
                                        → <?= date('d/m/Y', strtotime($t['fto_fecha_fin'])) ?>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-<?= $estadoBadge ?>"><?= str_replace('_', ' ', $t['fto_estado']) ?></span></td>
                                <td class="text-center">
                                    <span class="badge badge-pill" style="background-color: <?= $moduloColor ?>; color: #fff;">
                                        <?= (int)($t['total_jugadores'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= url('futbol', 'torneo', 'convocatoria') ?>&id=<?= $t['fto_torneo_id'] ?>"
                                       class="btn btn-sm text-white" style="background-color: <?= $moduloColor ?>">
                                        <i class="fas fa-users mr-1"></i> Ver convocatoria
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>

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
                        <span class="badge badge-<?= $estadoBadge ?>"><?= str_replace('_', ' ', $torneo['fto_estado']) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabla de Convocados -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-users mr-2" style="color: <?= $moduloColor ?>"></i>Jugadores Convocados</h3>
                <button class="btn btn-sm text-white" id="btnAgregarJugador" style="background-color: <?= $moduloColor ?>">
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
                            <tr data-id="<?= $jugador['ftj_id'] ?>">
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
                                    <?php else: ?>-<?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $estConvBadge = match($jugador['ftj_estado']) {
                                        'CONVOCADO'  => 'info',
                                        'CONFIRMADO' => 'success',
                                        'BAJA'       => 'danger',
                                        default      => 'secondary'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $estConvBadge ?>"><?= $jugador['ftj_estado'] ?></span>
                                </td>
                                <td>
                                    <?php if ($jugador['ftj_estado'] === 'CONVOCADO'): ?>
                                    <button class="btn btn-xs btn-outline-success js-confirmar-jugador"
                                        data-id="<?= $jugador['ftj_id'] ?>" title="Confirmar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-xs btn-outline-primary js-editar-convocado" title="Editar"
                                        data-convocado="<?= htmlspecialchars(json_encode($jugador, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-xs btn-outline-danger js-quitar-jugador"
                                        data-id="<?= $jugador['ftj_id'] ?>" title="Quitar">
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
                    <button class="btn btn-sm text-white" id="btnAgregarJugadorEmpty" style="background-color: <?= $moduloColor ?>">
                        <i class="fas fa-user-plus mr-1"></i> Agregar primer jugador
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php endif; /* end else (torneo !== null) */ ?>

    </div>
</section>

<!-- Modal Convocado -->
<div class="modal fade" id="modalConvocado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: <?= $moduloColor ?>">
                <h5 class="modal-title" id="modalConvocadoTitle"><i class="fas fa-user-plus mr-2"></i>Agregar Jugador</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formConvocado" method="POST"
                data-url="<?= url('futbol', 'torneo', 'agregarJugador') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="ftj_id" id="convocado_id">
                <input type="hidden" name="fto_torneo_id" value="<?= $torneoId ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Jugador <span class="text-danger">*</span></label>
                        <select name="alumno_id" id="convocado_alumno" class="form-control" required>
                            <option value="">Seleccione jugador...</option>
                            <?php foreach ($alumnos_disponibles as $alumno): ?>
                            <option value="<?= $alumno['id'] ?>"><?= htmlspecialchars($alumno['nombre']) ?></option>
                            <?php endforeach; ?>
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
                            <option value="BAJA">Baja</option>
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

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
$(function() {
    var Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    var csrfToken = '<?= addslashes($csrf_token ?? '') ?>';

    try {
        if ($('#tblConvocados tbody tr').length > 0) {
            $('#tblConvocados').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                responsive: true,
                order: [[4, 'asc'], [5, 'asc']]
            });
        }
    } catch(e) { console.warn('DataTable:', e); }

    function abrirModal() {
        $('#formConvocado')[0].reset();
        $('#convocado_id').val('');
        $('#convocado_estado').val('CONVOCADO');
        $('#convocado_alumno').prop('disabled', false);
        $('#modalConvocadoTitle').html('<i class="fas fa-user-plus mr-2"></i>Agregar Jugador');
        $('#modalConvocado').modal('show');
    }

    $('#btnAgregarJugador, #btnAgregarJugadorEmpty').on('click', abrirModal);

    // Editar convocado
    $(document).on('click', '.js-editar-convocado', function() {
        var obj = JSON.parse($(this).attr('data-convocado'));
        $('#formConvocado')[0].reset();
        $('#convocado_id').val(obj.ftj_id);
        $('#convocado_alumno').val(obj.alumno_id || obj.ftj_alumno_id || '').prop('disabled', true);
        $('#convocado_posicion').val(obj.ftj_posicion || '');
        $('#convocado_dorsal').val(obj.ftj_numero || '');
        $('#convocado_estado').val(obj.ftj_estado || 'CONVOCADO');
        $('#modalConvocadoTitle').html('<i class="fas fa-edit mr-2"></i>Editar Convocado');
        $('#modalConvocado').modal('show');
    });

    // Submit agregar/editar
    $('#formConvocado').on('submit', function(e) {
        e.preventDefault();
        var action = $(this).attr('data-url');
        var $btn   = $(this).find('[type=submit]').prop('disabled', true);
        // Re-habilitar el select deshabilitado para que se incluya en serialize
        var $alumno = $('#convocado_alumno');
        var wasDisabled = $alumno.prop('disabled');
        $alumno.prop('disabled', false);
        $.post(action, $(this).serialize(), function(res) {
            $alumno.prop('disabled', wasDisabled);
            if (res.success) {
                $('#modalConvocado').modal('hide');
                Toast.fire({ icon: 'success', title: res.message });
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                Toast.fire({ icon: 'error', title: res.message });
            }
        }, 'json').fail(function() {
            $alumno.prop('disabled', wasDisabled);
            Toast.fire({ icon: 'error', title: 'Error de comunicación' });
        }).always(function() { $btn.prop('disabled', false); });
    });

    // Confirmar jugador
    $(document).on('click', '.js-confirmar-jugador', function() {
        var id   = $(this).data('id');
        var $row = $(this).closest('tr');
        Swal.fire({
            title: '¿Confirmar jugador?',
            text: 'El jugador pasará a estado CONFIRMADO.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '<?= $moduloColor ?>',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $.post('<?= url('futbol', 'torneo', 'confirmarJugador') ?>', { id: id, csrf_token: csrfToken }, function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    setTimeout(function() { location.reload(); }, 1200);
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            }, 'json').fail(function() {
                Toast.fire({ icon: 'error', title: 'Error de comunicación' });
            });
        });
    });

    // Quitar jugador
    $(document).on('click', '.js-quitar-jugador', function() {
        var id   = $(this).data('id');
        var $row = $(this).closest('tr');
        Swal.fire({
            title: '¿Quitar jugador de la convocatoria?',
            text: 'El jugador será removido de este torneo.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, quitar',
            cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $.post('<?= url('futbol', 'torneo', 'quitarJugador') ?>', { id: id, csrf_token: csrfToken }, function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    $row.fadeOut(400, function() { location.reload(); });
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            }, 'json').fail(function() {
                Toast.fire({ icon: 'error', title: 'Error de comunicación' });
            });
        });
    });
});
</script>
<?php $scripts = ob_get_clean(); ?>
