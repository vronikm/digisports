<?php
/**
 * DigiSports Natación - Gestión de Instructores
 */
$instructores = $instructores ?? [];
$sedes        = $sedes ?? [];
$sedeActiva   = $sede_activa ?? null;
$moduloColor  = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-chalkboard-teacher mr-2" style="color:<?= $moduloColor ?>"></i>Instructores</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Nuevo Instructor</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($instructores)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-chalkboard-teacher fa-3x mb-3 opacity-50"></i><p>No hay instructores registrados</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th width="40">#</th><th>Instructor</th><?php if (!$sedeActiva): ?><th>Sede</th><?php endif; ?><th>Especialidad</th><th>Teléfono</th><th>Email</th><th class="text-center">Grupos</th><th class="text-center">Estado</th><th width="130" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($instructores as $i => $ins): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($ins['nin_nombres'] . ' ' . $ins['nin_apellidos']) ?></strong></td>
                                <?php if (!$sedeActiva): ?><td><?= htmlspecialchars($ins['sed_nombre'] ?? '—') ?></td><?php endif; ?>
                                <td><?= htmlspecialchars($ins['nin_especialidad'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($ins['nin_telefono'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($ins['nin_email'] ?? '—') ?></td>
                                <td class="text-center"><span class="badge badge-info"><?= (int)($ins['total_grupos'] ?? 0) ?></span></td>
                                <td class="text-center"><?= $ins['nin_activo'] ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>' ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='editarInstructor(<?= json_encode($ins) ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-danger" onclick="eliminarInstructor(<?= $ins['nin_instructor_id'] ?>,'<?= htmlspecialchars($ins['nin_nombres']) ?>')" title="Desactivar"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="modalInstructor" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formInstructor" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="ins_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-chalkboard-teacher mr-2"></i>Nuevo Instructor</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Nombres <span class="text-danger">*</span></label><input type="text" name="nombres" id="ins_nombres" class="form-control" required></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Apellidos <span class="text-danger">*</span></label><input type="text" name="apellidos" id="ins_apellidos" class="form-control" required></div></div>
                    </div>
                    <div class="form-group">
                        <label>Sede</label>
                        <select name="sede_id" id="ins_sede" class="form-control">
                            <option value="">— Sin sede —</option>
                            <?php foreach ($sedes as $s): ?>
                            <option value="<?= $s['sed_sede_id'] ?>" <?= $sedeActiva == $s['sed_sede_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['sed_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Identificación</label><input type="text" name="identificacion" id="ins_identif" class="form-control" maxlength="20"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Especialidad</label><input type="text" name="especialidad" id="ins_especialidad" class="form-control" placeholder="Natación infantil, competitiva..."></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Teléfono</label><input type="text" name="telefono" id="ins_telefono" class="form-control"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Email</label><input type="email" name="email" id="ins_email" class="form-control"></div></div>
                    </div>
                    <div class="form-group"><label>Certificaciones</label><textarea name="certificaciones" id="ins_certificaciones" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-save mr-1"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
var urlCrear = '<?= url('natacion', 'instructor', 'crear') ?>';
var urlEditar = '<?= url('natacion', 'instructor', 'editar') ?>';
function abrirModal() {
    document.getElementById('formInstructor').reset(); document.getElementById('ins_id').value = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-chalkboard-teacher mr-2"></i>Nuevo Instructor';
    document.getElementById('formInstructor').action = urlCrear; $('#modalInstructor').modal('show');
}
function editarInstructor(ins) {
    document.getElementById('ins_id').value = ins.nin_instructor_id;
    document.getElementById('ins_nombres').value = ins.nin_nombres || '';
    document.getElementById('ins_apellidos').value = ins.nin_apellidos || '';
    document.getElementById('ins_sede').value = ins.nin_sede_id || '';
    document.getElementById('ins_identif').value = ins.nin_identificacion || '';
    document.getElementById('ins_especialidad').value = ins.nin_especialidad || '';
    document.getElementById('ins_telefono').value = ins.nin_telefono || '';
    document.getElementById('ins_email').value = ins.nin_email || '';
    document.getElementById('ins_certificaciones').value = ins.nin_certificaciones || '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Instructor';
    document.getElementById('formInstructor').action = urlEditar; $('#modalInstructor').modal('show');
}
function eliminarInstructor(id, nombre) {
    Swal.fire({ title: '¿Desactivar instructor?', html: 'Se desactivará a <strong>' + nombre + '</strong>', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, desactivar', cancelButtonText: 'Cancelar'
    }).then(function(r) { if (r.isConfirmed) window.location.href = '<?= url('natacion', 'instructor', 'eliminar') ?>&id=' + id; });
}
</script>
<?php $scripts = ob_get_clean(); ?>
