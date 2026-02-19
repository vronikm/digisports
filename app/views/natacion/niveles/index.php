<?php
/**
 * DigiSports Natación - Gestión de Niveles
 */
$niveles     = $niveles ?? [];
$moduloColor = $modulo_actual['color'] ?? '#0EA5E9';
$colores     = ['#22C55E','#3B82F6','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316'];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-layer-group mr-2" style="color:<?= $moduloColor ?>"></i>Niveles</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Nuevo Nivel</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if (empty($niveles)): ?>
        <div class="card"><div class="card-body text-center py-5 text-muted"><i class="fas fa-layer-group fa-3x mb-3 opacity-50"></i><p>No hay niveles</p></div></div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($niveles as $nv): ?>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100" style="border-left: 4px solid <?= htmlspecialchars($nv['nnv_color'] ?? '#6c757d') ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1" style="color:<?= htmlspecialchars($nv['nnv_color'] ?? '#333') ?>"><?= htmlspecialchars($nv['nnv_nombre']) ?></h5>
                                <small class="text-muted">Código: <?= htmlspecialchars($nv['nnv_codigo'] ?? '—') ?> · Orden: <?= $nv['nnv_orden'] ?></small>
                            </div>
                            <span class="badge badge-<?= $nv['nnv_activo'] ? 'success' : 'secondary' ?>"><?= $nv['nnv_activo'] ? 'Activo' : 'Inactivo' ?></span>
                        </div>
                        <?php if (!empty($nv['nnv_descripcion'])): ?>
                        <p class="mt-2 mb-2 text-muted small"><?= htmlspecialchars($nv['nnv_descripcion']) ?></p>
                        <?php endif; ?>
                        <div class="d-flex gap-3 mt-2">
                            <span class="badge badge-light mr-2"><i class="fas fa-users mr-1"></i><?= (int)($nv['total_alumnos'] ?? 0) ?> alumnos</span>
                            <span class="badge badge-light"><i class="fas fa-tasks mr-1"></i><?= (int)($nv['total_habilidades'] ?? 0) ?> habilidades</span>
                        </div>
                        <?php if (!empty($nv['nnv_edad_min']) || !empty($nv['nnv_edad_max'])): ?>
                        <div class="mt-1"><small class="text-muted">Edad: <?= $nv['nnv_edad_min'] ?? '—' ?> - <?= $nv['nnv_edad_max'] ?? '—' ?> años</small></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white py-2">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick='editarNivel(<?= json_encode($nv) ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                            <a href="<?= url('natacion', 'nivel', 'habilidades') ?>&id=<?= $nv['nnv_nivel_id'] ?>" class="btn btn-outline-info" title="Habilidades"><i class="fas fa-tasks"></i></a>
                            <button class="btn btn-outline-danger" onclick="eliminarNivel(<?= $nv['nnv_nivel_id'] ?>,'<?= htmlspecialchars($nv['nnv_nombre']) ?>')" title="Desactivar"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="modalNivel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formNivel" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="nv_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-layer-group mr-2"></i>Nuevo Nivel</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8"><div class="form-group"><label>Nombre <span class="text-danger">*</span></label><input type="text" name="nombre" id="nv_nombre" class="form-control" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Código</label><input type="text" name="codigo" id="nv_codigo" class="form-control" maxlength="10"></div></div>
                    </div>
                    <div class="form-group"><label>Descripción</label><textarea name="descripcion" id="nv_desc" class="form-control" rows="2"></textarea></div>
                    <div class="row">
                        <div class="col-md-3"><div class="form-group"><label>Orden</label><input type="number" name="orden" id="nv_orden" class="form-control" min="0" value="0"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Edad Min</label><input type="number" name="edad_min" id="nv_emin" class="form-control" min="0"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Edad Max</label><input type="number" name="edad_max" id="nv_emax" class="form-control" min="0"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Color</label><input type="color" name="color" id="nv_color" class="form-control" value="#3B82F6"></div></div>
                    </div>
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
var urlCrear = '<?= url('natacion', 'nivel', 'crear') ?>';
var urlEditar = '<?= url('natacion', 'nivel', 'editar') ?>';
function abrirModal() {
    document.getElementById('formNivel').reset(); document.getElementById('nv_id').value = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-layer-group mr-2"></i>Nuevo Nivel';
    document.getElementById('formNivel').action = urlCrear; $('#modalNivel').modal('show');
}
function editarNivel(nv) {
    document.getElementById('nv_id').value = nv.nnv_nivel_id;
    document.getElementById('nv_nombre').value = nv.nnv_nombre || '';
    document.getElementById('nv_codigo').value = nv.nnv_codigo || '';
    document.getElementById('nv_desc').value = nv.nnv_descripcion || '';
    document.getElementById('nv_orden').value = nv.nnv_orden || 0;
    document.getElementById('nv_emin').value = nv.nnv_edad_min || '';
    document.getElementById('nv_emax').value = nv.nnv_edad_max || '';
    document.getElementById('nv_color').value = nv.nnv_color || '#3B82F6';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Nivel';
    document.getElementById('formNivel').action = urlEditar; $('#modalNivel').modal('show');
}
function eliminarNivel(id, nombre) {
    Swal.fire({ title: '¿Desactivar nivel?', html: '<strong>' + nombre + '</strong>', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, desactivar', cancelButtonText: 'Cancelar'
    }).then(function(r) { if (r.isConfirmed) window.location.href = '<?= url('natacion', 'nivel', 'eliminar') ?>&id=' + id; });
}
</script>
<?php $scripts = ob_get_clean(); ?>
