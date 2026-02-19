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
                    Gestión de Torneos
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Torneos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-trophy mr-2" style="color: <?= $moduloColor ?>"></i>Torneos</h3>
                <button class="btn btn-sm text-white" style="background-color: <?= $moduloColor ?>" onclick="abrirModalTorneo()">
                    <i class="fas fa-plus mr-1"></i> Nuevo Torneo
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($torneos)): ?>
                <div class="table-responsive">
                    <table id="tblTorneos" class="table table-bordered table-hover table-striped">
                        <thead style="background-color: <?= $moduloColor ?>; color: #fff;">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Sede</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Estado</th>
                                <th>Inscritos</th>
                                <th>Presupuesto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($torneos as $i => $torneo): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($torneo['fto_nombre']) ?></td>
                                <td>
                                    <?php
                                    $tipoBadge = match($torneo['fto_tipo']) {
                                        'INTERNO'  => 'primary',
                                        'EXTERNO'  => 'warning',
                                        'AMISTOSO' => 'info',
                                        'LIGA'     => 'success',
                                        'COPA'     => 'danger',
                                        default    => 'secondary'
                                    };
                                    $tipoIcon = match($torneo['fto_tipo']) {
                                        'INTERNO'  => 'fas fa-home',
                                        'EXTERNO'  => 'fas fa-external-link-alt',
                                        'AMISTOSO' => 'fas fa-handshake',
                                        'LIGA'     => 'fas fa-list-ol',
                                        'COPA'     => 'fas fa-trophy',
                                        default    => 'fas fa-tag'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $tipoBadge ?>">
                                        <i class="<?= $tipoIcon ?> mr-1"></i><?= $torneo['fto_tipo'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($torneo['sede_nombre'] ?? 'Sin sede') ?></td>
                                <td><?= date('d/m/Y', strtotime($torneo['fto_fecha_inicio'])) ?></td>
                                <td><?= $torneo['fto_fecha_fin'] ? date('d/m/Y', strtotime($torneo['fto_fecha_fin'])) : '-' ?></td>
                                <td>
                                    <?php
                                    $estadoBadge = match($torneo['fto_estado']) {
                                        'PLANIFICADO' => 'info',
                                        'EN_CURSO'    => 'success',
                                        'FINALIZADO'  => 'secondary',
                                        'CANCELADO'   => 'danger',
                                        default       => 'secondary'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $estadoBadge ?>"><?= str_replace('_', ' ', $torneo['fto_estado']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill" style="background-color: <?= $moduloColor ?>; color: #fff;">
                                        <?= $torneo['total_jugadores'] ?? 0 ?>
                                    </span>
                                </td>
                                <td class="text-right">$<?= number_format($torneo['fto_costo_inscripcion'] ?? 0, 2) ?></td>
                                <td>
                                    <a href="<?= url('futbol', 'torneo', 'convocatoria') ?>&id=<?= $torneo['fto_torneo_id'] ?>" class="btn btn-xs btn-outline-success" title="Convocatoria">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <button class="btn btn-xs btn-outline-primary" onclick="editarTorneo(<?= htmlspecialchars(json_encode($torneo)) ?>)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-xs btn-outline-danger" onclick="eliminarTorneo(<?= $torneo['fto_torneo_id'] ?>)" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-trophy fa-3x opacity-50 text-muted mb-3"></i>
                    <p class="text-muted">No hay torneos registrados.</p>
                    <button class="btn btn-sm text-white" style="background-color: <?= $moduloColor ?>" onclick="abrirModalTorneo()">
                        <i class="fas fa-plus mr-1"></i> Crear primer torneo
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- ============================================================= -->
<!-- MODAL: TORNEO -->
<!-- ============================================================= -->
<div class="modal fade" id="modalTorneo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: <?= $moduloColor ?>">
                <h5 class="modal-title" id="modalTorneoTitle"><i class="fas fa-trophy mr-2"></i>Nuevo Torneo</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formTorneo" method="POST" action="<?= url('futbol', 'torneo', 'crear') ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="id" id="torneo_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="torneo_nombre" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo <span class="text-danger">*</span></label>
                                <select name="tipo" id="torneo_tipo" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <option value="INTERNO">Interno</option>
                                    <option value="EXTERNO">Externo</option>
                                    <option value="AMISTOSO">Amistoso</option>
                                    <option value="LIGA">Liga</option>
                                    <option value="COPA">Copa</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Estado <span class="text-danger">*</span></label>
                                <select name="estado" id="torneo_estado" class="form-control" required>
                                    <option value="PLANIFICADO">Planificado</option>
                                    <option value="EN_CURSO">En Curso</option>
                                    <option value="FINALIZADO">Finalizado</option>
                                    <option value="CANCELADO">Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sede</label>
                                <select name="sede_id" id="torneo_sede" class="form-control">
                                    <option value="">Sin sede asignada</option>
                                    <?php if (!empty($sedes)): ?>
                                        <?php foreach ($sedes as $sede): ?>
                                        <option value="<?= $sede['sed_sede_id'] ?>"><?= htmlspecialchars($sede['sed_nombre']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lugar</label>
                                <input type="text" name="lugar" id="torneo_lugar" class="form-control" placeholder="Dirección o nombre del lugar">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha Inicio <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_inicio" id="torneo_fecha_inicio" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha Fin</label>
                                <input type="date" name="fecha_fin" id="torneo_fecha_fin" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Presupuesto</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                    <input type="number" name="presupuesto" id="torneo_presupuesto" class="form-control" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" id="torneo_descripcion" class="form-control" rows="3" placeholder="Descripción del torneo..."></textarea>
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
    $('#tblTorneos').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        responsive: true,
        order: [[4, 'desc']]
    });
});

function abrirModalTorneo() {
    $('#formTorneo')[0].reset();
    $('#torneo_id').val('');
    $('#torneo_estado').val('PLANIFICADO');
    $('#modalTorneoTitle').html('<i class="fas fa-trophy mr-2"></i>Nuevo Torneo');
    $('#formTorneo').attr('action', '<?= url('futbol', 'torneo', 'crear') ?>');
    $('#modalTorneo').modal('show');
}

function editarTorneo(obj) {
    $('#formTorneo')[0].reset();
    $('#torneo_id').val(obj.fto_torneo_id);
    $('#torneo_nombre').val(obj.fto_nombre);
    $('#torneo_tipo').val(obj.fto_tipo);
    $('#torneo_estado').val(obj.fto_estado);
    $('#torneo_sede').val(obj.fto_sede_id || '');
    $('#torneo_lugar').val(obj.fto_sede_torneo || '');
    $('#torneo_fecha_inicio').val(obj.fto_fecha_inicio);
    $('#torneo_fecha_fin').val(obj.fto_fecha_fin || '');
    $('#torneo_presupuesto').val(obj.fto_costo_inscripcion || '');
    $('#torneo_descripcion').val(obj.fto_descripcion || '');
    $('#modalTorneoTitle').html('<i class="fas fa-edit mr-2"></i>Editar Torneo');
    $('#formTorneo').attr('action', '<?= url('futbol', 'torneo', 'editar') ?>');
    $('#modalTorneo').modal('show');
}

function eliminarTorneo(id) {
    Swal.fire({
        title: '¿Eliminar torneo?',
        text: 'Esta acción eliminará el torneo y todos sus datos asociados.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url('futbol', 'torneo', 'eliminar') ?>&id=' + id + '&csrf_token=<?= $csrf_token ?>';
        }
    });
}

function verConvocatoria(id) {
    window.location.href = '<?= url('futbol', 'torneo', 'convocatoria') ?>&id=' + id;
}
</script>
<?php $scripts = ob_get_clean(); ?>
