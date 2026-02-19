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
                <button class="btn btn-sm text-white" style="background-color: <?= $moduloColor ?>" onclick="abrirModalBeca()">
                    <i class="fas fa-plus mr-1"></i> Nuevo Tipo de Beca
                </button>
            </div>
        </div>

        <?php if (!empty($becas)): ?>
        <div class="row">
            <?php foreach ($becas as $beca): ?>
            <div class="col-md-4 col-lg-3">
                <div class="card card-outline" style="border-top-color: <?= $moduloColor ?>">
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
                                <?= $beca['fbe_tipo'] === 'PORCENTAJE' ? 'Porcentaje' : 'Monto Fijo' ?>
                            </span>
                            <span class="font-weight-bold" style="color: <?= $moduloColor ?>; font-size: 1.2em;">
                                <?php if ($beca['fbe_tipo'] === 'PORCENTAJE'): ?>
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
                            <button class="btn btn-xs btn-outline-primary mr-1" onclick="editarBeca(<?= htmlspecialchars(json_encode($beca)) ?>)" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-xs btn-outline-danger" onclick="eliminarBeca(<?= $beca['fbe_beca_id'] ?>)" title="Desactivar">
                                <i class="fas fa-ban"></i>
                            </button>
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
                        <button class="btn btn-sm text-white" style="background-color: <?= $moduloColor ?>" onclick="abrirModalBeca()">
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
                <button class="btn btn-sm text-white" style="background-color: <?= $moduloColor ?>" onclick="abrirModalAsignacion()">
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
                                        <?= ($asig['fbe_tipo'] ?? '') === 'PORCENTAJE' ? 'Porcentaje' : 'Monto Fijo' ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <?php if (($asig['fbe_tipo'] ?? '') === 'PORCENTAJE'): ?>
                                        <?= number_format($asig['fbe_valor'] ?? 0, 0) ?>%
                                    <?php else: ?>
                                        $<?= number_format($asig['fbe_valor'] ?? 0, 2) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($asig['fba_fecha_asignacion'])) ?></td>
                                <td>
                                    <?php
                                    $estadoBadge = match($asig['fba_estado']) {
                                        'ACTIVA' => 'success',
                                        'SUSPENDIDA' => 'warning',
                                        'FINALIZADA' => 'secondary',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $estadoBadge ?>"><?= $asig['fba_estado'] ?></span>
                                </td>
                                <td>
                                    <?php if ($asig['fba_estado'] === 'ACTIVA'): ?>
                                    <button class="btn btn-xs btn-outline-danger" onclick="revocarAsignacion(<?= $asig['fba_asignacion_id'] ?>)" title="Revocar">
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
            <form id="formBeca" method="POST" action="<?= url('futbol', 'beca', 'crear') ?>">
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
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Valor <span class="text-danger">*</span></label>
                        <input type="number" name="valor" id="beca_valor" class="form-control" step="0.01" min="0" required>
                        <small class="text-muted">Para porcentaje ingrese un valor entre 0 y 100. Para monto fijo ingrese el valor en dólares.</small>
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
                <h5 class="modal-title" id="modalAsignacionTitle"><i class="fas fa-user-graduate mr-2"></i>Nueva Asignación</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formAsignacion" method="POST" action="<?= url('futbol', 'beca', 'asignar') ?>">
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
                                    <option value="<?= $beca['fbe_beca_id'] ?>"><?= htmlspecialchars($beca['fbe_nombre']) ?> (<?= $beca['fbe_tipo'] === 'PORCENTAJE' ? $beca['fbe_valor'] . '%' : '$' . number_format($beca['fbe_valor'], 2) ?>)</option>
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
<!-- SCRIPTS -->
<!-- ============================================================= -->
<?php ob_start(); ?>
<script>
$(document).ready(function() {
    $('#tblAsignaciones').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        responsive: true,
        order: [[5, 'desc']]
    });

    if ($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
    }
});

function abrirModalBeca() {
    $('#formBeca')[0].reset();
    $('#beca_id').val('');
    $('#modalBecaTitle').html('<i class="fas fa-graduation-cap mr-2"></i>Nuevo Tipo de Beca');
    $('#formBeca').attr('action', '<?= url('futbol', 'beca', 'crear') ?>');
    $('#modalBeca').modal('show');
}

function editarBeca(obj) {
    $('#formBeca')[0].reset();
    $('#beca_id').val(obj.fbe_beca_id);
    $('#beca_nombre').val(obj.fbe_nombre);
    $('#beca_tipo').val(obj.fbe_tipo);
    $('#beca_valor').val(obj.fbe_valor);
    $('#beca_descripcion').val(obj.fbe_descripcion || '');
    $('#modalBecaTitle').html('<i class="fas fa-edit mr-2"></i>Editar Tipo de Beca');
    $('#formBeca').attr('action', '<?= url('futbol', 'beca', 'editar') ?>');
    $('#modalBeca').modal('show');
}

function eliminarBeca(id) {
    Swal.fire({
        title: '¿Desactivar tipo de beca?',
        text: 'La beca será desactivada.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url('futbol', 'beca', 'eliminar') ?>&id=' + id;
        }
    });
}

function abrirModalAsignacion() {
    $('#formAsignacion')[0].reset();
    $('#modalAsignacionTitle').html('<i class="fas fa-user-graduate mr-2"></i>Nueva Asignación');
    $('#formAsignacion').attr('action', '<?= url('futbol', 'beca', 'asignar') ?>');
    if ($.fn.select2) { $('.select2').val('').trigger('change'); }
    $('#modalAsignacion').modal('show');
}

function revocarAsignacion(id) {
    Swal.fire({
        title: '¿Revocar asignación?',
        text: 'Se finalizará la beca asignada al alumno.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, revocar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= url('futbol', 'beca', 'revocar') ?>', {
                csrf_token: '<?= $csrf_token ?>',
                asignacion_id: id
            }, function(response) {
                if (response.success) {
                    Swal.fire('Revocada', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error', 'Error de conexión.', 'error');
            });
        }
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>
