<?php
/**
 * DigiSports Store - Gestión de Categorías
 * CRUD con tabla + modales
 */
$categorias = $categorias ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-tags mr-2" style="color:<?= $moduloColor ?>"></i>Categorías</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()">
                        <i class="fas fa-plus mr-1"></i> Nueva Categoría
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
                <?php if (empty($categorias)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-tags fa-3x mb-3 opacity-50"></i>
                    <p>No hay categorías registradas</p>
                    <button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()">
                        <i class="fas fa-plus mr-1"></i> Crear primera categoría
                    </button>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Nombre</th>
                                <th>Categoría Padre</th>
                                <th class="text-center">Productos</th>
                                <th class="text-center">Estado</th>
                                <th width="150" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $i => $cat): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <i class="<?= htmlspecialchars($cat['cat_icono'] ?? 'fas fa-tag') ?> mr-2" style="color:<?= $moduloColor ?>"></i>
                                    <strong><?= htmlspecialchars($cat['cat_nombre']) ?></strong>
                                    <?php if (!empty($cat['cat_descripcion'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars(substr($cat['cat_descripcion'], 0, 60)) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= !empty($cat['padre_nombre']) ? htmlspecialchars($cat['padre_nombre']) : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light"><?= intval($cat['total_productos'] ?? 0) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if (($cat['cat_estado'] ?? 'ACTIVO') === 'ACTIVO'): ?>
                                    <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                    <span class="badge badge-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="editarCategoria(<?= htmlspecialchars(json_encode($cat)) ?>)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="eliminarCategoria(<?= $cat['cat_categoria_id'] ?>, '<?= htmlspecialchars($cat['cat_nombre']) ?>')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<!-- Modal Crear/Editar -->
<div class="modal fade" id="modalCategoria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCategoria" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="cat_categoria_id" id="cat_categoria_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-tag mr-2"></i>Nueva Categoría</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="cat_nombre" class="form-control" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" id="cat_descripcion" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Categoría Padre</label>
                                <select name="padre_id" id="cat_padre_id" class="form-control">
                                    <option value="">— Ninguna (Principal) —</option>
                                    <?php foreach ($categorias as $c):
                                        if (empty($c['cat_padre_id'])):
                                    ?>
                                    <option value="<?= $c['cat_categoria_id'] ?>"><?= htmlspecialchars($c['cat_nombre']) ?></option>
                                    <?php endif; endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ícono (FontAwesome)</label>
                                <input type="text" name="icono" id="cat_icono" class="form-control" placeholder="fas fa-tag" maxlength="50">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" id="cat_estado" class="form-control">
                            <option value="ACTIVO">Activo</option>
                            <option value="INACTIVO">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background:<?= $moduloColor ?>;color:white;" id="btnGuardar">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
var urlCrear = '<?= url('store', 'categoria', 'crear') ?>';
var urlEditar = '<?= url('store', 'categoria', 'editar') ?>';
var urlEliminar = '<?= url('store', 'categoria', 'eliminar') ?>';

function abrirModal() {
    document.getElementById('formCategoria').reset();
    document.getElementById('cat_categoria_id').value = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-tag mr-2"></i>Nueva Categoría';
    document.getElementById('formCategoria').action = urlCrear;
    $('#modalCategoria').modal('show');
}

function editarCategoria(cat) {
    document.getElementById('cat_categoria_id').value = cat.cat_categoria_id;
    document.getElementById('cat_nombre').value = cat.cat_nombre || '';
    document.getElementById('cat_descripcion').value = cat.cat_descripcion || '';
    document.getElementById('cat_padre_id').value = cat.cat_padre_id || '';
    document.getElementById('cat_icono').value = cat.cat_icono || '';
    document.getElementById('cat_estado').value = cat.cat_estado || 'ACTIVO';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Categoría';
    document.getElementById('formCategoria').action = urlEditar;
    $('#modalCategoria').modal('show');
}

function eliminarCategoria(id, nombre) {
    Swal.fire({
        title: '¿Eliminar categoría?',
        html: 'Se eliminará <strong>' + nombre + '</strong>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.isConfirmed) {
            window.location.href = urlEliminar + '&id=' + id;
        }
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>
