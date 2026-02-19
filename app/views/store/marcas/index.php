<?php
/**
 * DigiSports Store - Gestión de Marcas
 */
$marcas = $marcas ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-award mr-2" style="color:<?= $moduloColor ?>"></i>Marcas</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()">
                        <i class="fas fa-plus mr-1"></i> Nueva Marca
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($marcas)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-award fa-3x mb-3 opacity-50"></i>
                    <p>No hay marcas registradas</p>
                    <button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()">
                        <i class="fas fa-plus mr-1"></i> Crear primera marca
                    </button>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Marca</th>
                                <th>Descripción</th>
                                <th class="text-center">Productos</th>
                                <th class="text-center">Estado</th>
                                <th width="130" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($marcas as $i => $m): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <?php if (!empty($m['mar_logo_url'])): ?>
                                    <img src="<?= htmlspecialchars($m['mar_logo_url']) ?>" alt="" style="width:24px;height:24px;object-fit:contain;" class="mr-2">
                                    <?php endif; ?>
                                    <strong><?= htmlspecialchars($m['mar_nombre']) ?></strong>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars(substr($m['mar_descripcion'] ?? '', 0, 80)) ?></small></td>
                                <td class="text-center"><span class="badge badge-light"><?= intval($m['total_productos'] ?? 0) ?></span></td>
                                <td class="text-center">
                                    <span class="badge badge-<?= ($m['mar_estado'] ?? 'ACTIVO') === 'ACTIVO' ? 'success' : 'secondary' ?>">
                                        <?= $m['mar_estado'] ?? 'ACTIVO' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="editarMarca(<?= htmlspecialchars(json_encode($m)) ?>)"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-danger" onclick="eliminarMarca(<?= $m['mar_marca_id'] ?>, '<?= htmlspecialchars($m['mar_nombre']) ?>')"><i class="fas fa-trash"></i></button>
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
<div class="modal fade" id="modalMarca" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formMarca" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="mar_marca_id" id="mar_marca_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-award mr-2"></i>Nueva Marca</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="mar_nombre" class="form-control" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" id="mar_descripcion" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="form-group">
                        <label>URL Logo</label>
                        <input type="url" name="logo_url" id="mar_logo_url" class="form-control" placeholder="https://..." maxlength="255">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" id="mar_estado" class="form-control">
                            <option value="ACTIVO">Activo</option>
                            <option value="INACTIVO">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
var urlCrear = '<?= url('store', 'marca', 'crear') ?>';
var urlEditar = '<?= url('store', 'marca', 'editar') ?>';
var urlEliminar = '<?= url('store', 'marca', 'eliminar') ?>';

function abrirModal() {
    document.getElementById('formMarca').reset();
    document.getElementById('mar_marca_id').value = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-award mr-2"></i>Nueva Marca';
    document.getElementById('formMarca').action = urlCrear;
    $('#modalMarca').modal('show');
}

function editarMarca(m) {
    document.getElementById('mar_marca_id').value = m.mar_marca_id;
    document.getElementById('mar_nombre').value = m.mar_nombre || '';
    document.getElementById('mar_descripcion').value = m.mar_descripcion || '';
    document.getElementById('mar_logo_url').value = m.mar_logo_url || '';
    document.getElementById('mar_estado').value = m.mar_estado || 'ACTIVO';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Marca';
    document.getElementById('formMarca').action = urlEditar;
    $('#modalMarca').modal('show');
}

function eliminarMarca(id, nombre) {
    Swal.fire({
        title: '¿Eliminar marca?',
        html: 'Se eliminará <strong>' + nombre + '</strong>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function(r) {
        if (r.isConfirmed) window.location.href = urlEliminar + '&id=' + id;
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>
