<?php
/**
 * DigiSports Fútbol - Gestión de Categorías
 */
$categorias  = $categorias ?? [];
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-layer-group mr-2" style="color:<?= $moduloColor ?>"></i>Categorías</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" id="btnNuevaCategoria" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-plus mr-1"></i>Nueva Categoría</button></div></div>
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
                            <button class="btn btn-outline-primary js-editar-categoria"
                                data-cat="<?= htmlspecialchars(json_encode($cat, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>"
                                title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-outline-info js-ver-habilidades"
                                data-id="<?= $cat['fct_categoria_id'] ?>"
                                data-nombre="<?= htmlspecialchars($cat['fct_nombre'], ENT_QUOTES) ?>"
                                title="Habilidades"><i class="fas fa-tasks"></i></button>
                            <button class="btn btn-outline-danger js-eliminar-categoria"
                                data-id="<?= $cat['fct_categoria_id'] ?>"
                                data-nombre="<?= htmlspecialchars($cat['fct_nombre'], ENT_QUOTES) ?>"
                                title="Desactivar"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal Categoría -->
<div class="modal fade" id="modalCategoria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCategoria" method="POST"
                data-url-crear="<?= url('futbol', 'categoria', 'crear') ?>"
                data-url-editar="<?= url('futbol', 'categoria', 'editar') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="cat_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-layer-group mr-2"></i>Nueva Categoría</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8"><div class="form-group"><label>Nombre <span class="text-danger">*</span></label><input type="text" name="nombre" id="cat_nombre" class="form-control" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Código <span class="text-danger">*</span></label><input type="text" name="codigo" id="cat_codigo" class="form-control" maxlength="10" required></div></div>
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
                <form id="formHabilidad" class="form-inline">
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
<script nonce="<?= cspNonce() ?>">
$(function() {
    var Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    var csrfToken = '<?= addslashes($csrf_token ?? '') ?>';
    var urlHabilidades = '<?= url('futbol', 'categoria', 'habilidades') ?>';
    var urlCrearHab    = '<?= url('futbol', 'categoria', 'crearHabilidad') ?>';

    // Nueva categoría
    $('#btnNuevaCategoria').on('click', function() {
        $('#formCategoria')[0].reset();
        $('#cat_id').val('');
        $('#cat_color').val('#22C55E');
        $('#modalTitulo').html('<i class="fas fa-layer-group mr-2"></i>Nueva Categoría');
        $('#formCategoria').data('mode', 'crear');
        $('#modalCategoria').modal('show');
    });

    // Editar categoría
    $(document).on('click', '.js-editar-categoria', function() {
        var cat = JSON.parse($(this).attr('data-cat'));
        $('#cat_id').val(cat.fct_categoria_id);
        $('#cat_nombre').val(cat.fct_nombre || '');
        $('#cat_codigo').val(cat.fct_codigo || '');
        $('#cat_desc').val(cat.fct_descripcion || '');
        $('#cat_orden').val(cat.fct_orden || 0);
        $('#cat_emin').val(cat.fct_edad_min || '');
        $('#cat_emax').val(cat.fct_edad_max || '');
        $('#cat_color').val(cat.fct_color || '#22C55E');
        $('#modalTitulo').html('<i class="fas fa-edit mr-2"></i>Editar Categoría');
        $('#formCategoria').data('mode', 'editar');
        $('#modalCategoria').modal('show');
    });

    // Submit crear/editar
    $('#formCategoria').on('submit', function(e) {
        e.preventDefault();
        var mode = $(this).data('mode') || 'crear';
        var action = $(this).attr(mode === 'editar' ? 'data-url-editar' : 'data-url-crear');
        var $btn = $(this).find('[type=submit]').prop('disabled', true);
        $.post(action, $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalCategoria').modal('hide');
                Toast.fire({ icon: 'success', title: res.message });
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                Toast.fire({ icon: 'error', title: res.message });
            }
        }, 'json').fail(function() {
            Toast.fire({ icon: 'error', title: 'Error de comunicación' });
        }).always(function() { $btn.prop('disabled', false); });
    });

    // Desactivar categoría
    $(document).on('click', '.js-eliminar-categoria', function() {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        var $card = $(this).closest('.col-lg-4, .col-md-6');
        Swal.fire({
            title: '¿Desactivar categoría?',
            html: '<strong>' + nombre + '</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $.post('<?= url('futbol', 'categoria', 'eliminar') ?>', { id: id, csrf_token: csrfToken }, function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    $card.fadeOut(400, function() { location.reload(); });
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            }, 'json').fail(function() {
                Toast.fire({ icon: 'error', title: 'Error de comunicación' });
            });
        });
    });

    // Ver habilidades
    $(document).on('click', '.js-ver-habilidades', function() {
        var catId = $(this).data('id');
        var catNombre = $(this).data('nombre');
        $('#habTitulo').html('<i class="fas fa-tasks mr-2"></i>Habilidades: ' + catNombre);
        $('#hab_cat_id').val(catId);
        $('#habLoading').show();
        $('#habContent').hide();
        $('#modalHabilidades').modal('show');

        $.getJSON(urlHabilidades + '&id=' + catId, function(res) {
            $('#habLoading').hide();
            $('#habContent').show();
            var $body = $('#habBody').empty();
            if (res.success && res.data && res.data.length > 0) {
                $('#tablaHabilidades').show();
                $('#habEmpty').hide();
                $.each(res.data, function(i, h) {
                    $body.append('<tr><td>' + (h.fch_nombre || '') + '</td><td>' + (h.fch_descripcion || '—') + '</td><td>' + (h.fch_orden || 0) + '</td></tr>');
                });
            } else {
                $('#tablaHabilidades').hide();
                $('#habEmpty').show();
            }
        }).fail(function() {
            $('#habLoading').hide();
            Swal.fire('Error', 'No se pudieron cargar las habilidades', 'error');
        });
    });

    // Agregar habilidad
    $('#formHabilidad').on('submit', function(e) {
        e.preventDefault();
        var catId = $('#hab_cat_id').val();
        var catNombre = $('#habTitulo').text().replace('Habilidades: ', '');
        var $btn = $(this).find('[type=submit]').prop('disabled', true);
        $.post(urlCrearHab, $(this).serialize(), function(res) {
            if (res.success) {
                Toast.fire({ icon: 'success', title: res.message });
                $('#hab_nombre').val('');
                // Recargar lista de habilidades
                $('#habLoading').show();
                $('#habContent').hide();
                $.getJSON(urlHabilidades + '&id=' + catId, function(res2) {
                    $('#habLoading').hide();
                    $('#habContent').show();
                    var $body = $('#habBody').empty();
                    if (res2.success && res2.data && res2.data.length > 0) {
                        $('#tablaHabilidades').show();
                        $('#habEmpty').hide();
                        $.each(res2.data, function(i, h) {
                            $body.append('<tr><td>' + (h.fch_nombre || '') + '</td><td>' + (h.fch_descripcion || '—') + '</td><td>' + (h.fch_orden || 0) + '</td></tr>');
                        });
                    } else {
                        $('#tablaHabilidades').hide();
                        $('#habEmpty').show();
                    }
                });
            } else {
                Toast.fire({ icon: 'error', title: res.message });
            }
        }, 'json').fail(function() {
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
        }).always(function() { $btn.prop('disabled', false); });
    });
});
</script>
<?php $scripts = ob_get_clean(); ?>
