<?php
/**
 * DigiSports Fútbol - Gestión de Categorías
 */
$categorias  = $categorias ?? [];
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
$colores     = ['#22C55E','#3B82F6','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316'];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-layer-group mr-2" style="color:<?= $moduloColor ?>"></i>Categorías</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Nueva Categoría</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if (empty($categorias)): ?>
        <div class="card"><div class="card-body text-center py-5 text-muted"><i class="fas fa-layer-group fa-3x mb-3 opacity-50"></i><p>No hay categorías</p></div></div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($categorias as $cat): ?>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100" style="border-left: 4px solid <?= htmlspecialchars($cat['fct_color'] ?? '#6c757d') ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1" style="color:<?= htmlspecialchars($cat['fct_color'] ?? '#333') ?>"><?= htmlspecialchars($cat['fct_nombre']) ?></h5>
                                <small class="text-muted">Código: <?= htmlspecialchars($cat['fct_codigo'] ?? '—') ?> · Orden: <?= $cat['fct_orden'] ?></small>
                            </div>
                            <span class="badge badge-<?= $cat['fct_activo'] ? 'success' : 'secondary' ?>"><?= $cat['fct_activo'] ? 'Activo' : 'Inactivo' ?></span>
                        </div>
                        <?php if (!empty($cat['fct_descripcion'])): ?>
                        <p class="mt-2 mb-2 text-muted small"><?= htmlspecialchars($cat['fct_descripcion']) ?></p>
                        <?php endif; ?>
                        <div class="d-flex gap-3 mt-2">
                            <span class="badge badge-light mr-2"><i class="fas fa-users mr-1"></i><?= (int)($cat['total_alumnos'] ?? 0) ?> jugadores</span>
                            <span class="badge badge-light"><i class="fas fa-tasks mr-1"></i><?= (int)($cat['total_habilidades'] ?? 0) ?> habilidades</span>
                        </div>
                        <?php if (!empty($cat['fct_edad_min']) || !empty($cat['fct_edad_max'])): ?>
                        <div class="mt-1"><small class="text-muted">Edad: <?= $cat['fct_edad_min'] ?? '—' ?> - <?= $cat['fct_edad_max'] ?? '—' ?> años</small></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white py-2">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick='editarCategoria(<?= json_encode($cat) ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-outline-info" onclick="verHabilidades(<?= $cat['fct_categoria_id'] ?>,'<?= htmlspecialchars($cat['fct_nombre']) ?>')" title="Habilidades"><i class="fas fa-tasks"></i></button>
                            <button class="btn btn-outline-danger" onclick="eliminarCategoria(<?= $cat['fct_categoria_id'] ?>,'<?= htmlspecialchars($cat['fct_nombre']) ?>')" title="Desactivar"><i class="fas fa-trash"></i></button>
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
<div class="modal fade" id="modalCategoria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCategoria" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="cat_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-layer-group mr-2"></i>Nueva Categoría</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8"><div class="form-group"><label>Nombre <span class="text-danger">*</span></label><input type="text" name="nombre" id="cat_nombre" class="form-control" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Código</label><input type="text" name="codigo" id="cat_codigo" class="form-control" maxlength="10"></div></div>
                    </div>
                    <div class="form-group"><label>Descripción</label><textarea name="descripcion" id="cat_desc" class="form-control" rows="2"></textarea></div>
                    <div class="row">
                        <div class="col-md-3"><div class="form-group"><label>Orden</label><input type="number" name="orden" id="cat_orden" class="form-control" min="0" value="0"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Edad Min</label><input type="number" name="edad_min" id="cat_emin" class="form-control" min="0"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Edad Max</label><input type="number" name="edad_max" id="cat_emax" class="form-control" min="0"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Color</label><input type="color" name="color" id="cat_color" class="form-control" value="#22C55E"></div></div>
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

<!-- Modal Habilidades -->
<div class="modal fade" id="modalHabilidades" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                <h5 class="modal-title" id="habTitulo"><i class="fas fa-tasks mr-2"></i>Habilidades</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="habLoading" class="text-center py-3"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
                <div id="habContent" style="display:none;">
                    <table class="table table-sm table-bordered" id="tablaHabilidades">
                        <thead><tr><th>Nombre</th><th>Descripción</th><th>Orden</th></tr></thead>
                        <tbody id="habBody"></tbody>
                    </table>
                    <div id="habEmpty" class="text-center text-muted py-3" style="display:none;">
                        <p>No hay habilidades registradas para esta categoría.</p>
                    </div>
                </div>
                <hr>
                <form id="formHabilidad" class="form-inline" onsubmit="return agregarHabilidad(event)">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="categoria_id" id="hab_cat_id">
                    <input type="text" name="nombre" id="hab_nombre" class="form-control form-control-sm mr-2" placeholder="Nombre habilidad" required>
                    <input type="number" name="orden" id="hab_orden" class="form-control form-control-sm mr-2" placeholder="Orden" style="width:80px" min="0" value="0">
                    <button type="submit" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-plus mr-1"></i>Agregar</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
var urlCrear = '<?= url('futbol', 'categoria', 'crear') ?>';
var urlEditar = '<?= url('futbol', 'categoria', 'editar') ?>';
function abrirModal() {
    document.getElementById('formCategoria').reset(); document.getElementById('cat_id').value = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-layer-group mr-2"></i>Nueva Categoría';
    document.getElementById('formCategoria').action = urlCrear; $('#modalCategoria').modal('show');
}
function editarCategoria(cat) {
    document.getElementById('cat_id').value = cat.fct_categoria_id;
    document.getElementById('cat_nombre').value = cat.fct_nombre || '';
    document.getElementById('cat_codigo').value = cat.fct_codigo || '';
    document.getElementById('cat_desc').value = cat.fct_descripcion || '';
    document.getElementById('cat_orden').value = cat.fct_orden || 0;
    document.getElementById('cat_emin').value = cat.fct_edad_min || '';
    document.getElementById('cat_emax').value = cat.fct_edad_max || '';
    document.getElementById('cat_color').value = cat.fct_color || '#22C55E';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Categoría';
    document.getElementById('formCategoria').action = urlEditar; $('#modalCategoria').modal('show');
}
function eliminarCategoria(id, nombre) {
    Swal.fire({ title: '¿Desactivar categoría?', html: '<strong>' + nombre + '</strong>', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, desactivar', cancelButtonText: 'Cancelar'
    }).then(function(r) { if (r.isConfirmed) window.location.href = '<?= url('futbol', 'categoria', 'eliminar') ?>&id=' + id; });
}

var urlHabilidades = '<?= url('futbol', 'categoria', 'habilidades') ?>';
var urlCrearHab    = '<?= url('futbol', 'categoria', 'crearHabilidad') ?>';

function verHabilidades(catId, catNombre) {
    document.getElementById('habTitulo').innerHTML = '<i class="fas fa-tasks mr-2"></i>Habilidades: ' + catNombre;
    document.getElementById('hab_cat_id').value = catId;
    document.getElementById('habLoading').style.display = 'block';
    document.getElementById('habContent').style.display = 'none';
    $('#modalHabilidades').modal('show');

    $.getJSON(urlHabilidades + '&id=' + catId, function(res) {
        document.getElementById('habLoading').style.display = 'none';
        document.getElementById('habContent').style.display = 'block';
        var body = document.getElementById('habBody');
        body.innerHTML = '';
        if (res.success && res.data && res.data.length > 0) {
            document.getElementById('tablaHabilidades').style.display = '';
            document.getElementById('habEmpty').style.display = 'none';
            res.data.forEach(function(h) {
                body.innerHTML += '<tr><td>' + (h.fch_nombre||'') + '</td><td>' + (h.fch_descripcion||'—') + '</td><td>' + (h.fch_orden||0) + '</td></tr>';
            });
        } else {
            document.getElementById('tablaHabilidades').style.display = 'none';
            document.getElementById('habEmpty').style.display = 'block';
        }
    }).fail(function() {
        document.getElementById('habLoading').style.display = 'none';
        Swal.fire('Error', 'No se pudieron cargar las habilidades', 'error');
    });
}

function agregarHabilidad(e) {
    e.preventDefault();
    var form = document.getElementById('formHabilidad');
    $.post(urlCrearHab, $(form).serialize(), function(res) {
        if (res.success) {
            Swal.fire('Éxito', res.message, 'success');
            document.getElementById('hab_nombre').value = '';
            verHabilidades(document.getElementById('hab_cat_id').value, document.getElementById('habTitulo').textContent.replace('Habilidades: ', ''));
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    }, 'json').fail(function() { Swal.fire('Error', 'Error de conexión', 'error'); });
    return false;
}
</script>
<?php $scripts = ob_get_clean(); ?>
