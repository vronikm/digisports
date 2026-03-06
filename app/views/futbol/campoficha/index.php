<?php
/**
 * DigiSports Fútbol - Campos de Ficha Personalizados
 * @vars $campos, $csrf_token, $modulo_actual
 */
$campos       = $campos ?? [];
$tipos_campo  = ['TEXT','NUMBER','SELECT','DATE','BOOLEAN','TEXTAREA'];
$moduloColor  = $modulo_actual['color'] ?? '#22C55E';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-sliders-h mr-2" style="color:<?= $moduloColor ?>"></i>Campos de Ficha</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" id="btnNuevoCampo" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-plus mr-1"></i>Nuevo Campo</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>Configure los campos adicionales que aparecerán en la ficha de cada jugador. Estos campos son personalizables por empresa.</div>

        <div class="card shadow-sm">
            <div class="card-header py-2">
                <span class="badge badge-secondary"><?= count($campos) ?> campo(s)</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($campos)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-sliders-h fa-3x mb-3 opacity-50"></i>
                    <p>No hay campos personalizados</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaCampos">
                        <thead class="thead-light">
                            <tr>
                                <th width="60">Orden</th>
                                <th>Nombre (slug)</th>
                                <th>Etiqueta</th>
                                <th>Tipo</th>
                                <th>Opciones</th>
                                <th class="text-center">Requerido</th>
                                <th class="text-center">Activo</th>
                                <th width="130" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCampos">
                            <?php foreach ($campos as $c): ?>
                            <tr class="<?= !($c['fcf_activo'] ?? 1) ? 'table-secondary' : '' ?>" data-id="<?= $c['fcf_campo_id'] ?>">
                                <td class="text-center">
                                    <span class="badge badge-light"><?= (int)($c['fcf_orden'] ?? 0) ?></span>
                                </td>
                                <td><code><?= htmlspecialchars($c['fcf_clave'] ?? '') ?></code></td>
                                <td><strong><?= htmlspecialchars($c['fcf_etiqueta'] ?? '') ?></strong></td>
                                <td><span class="badge badge-light"><?= htmlspecialchars($c['fcf_tipo'] ?? 'TEXT') ?></span></td>
                                <td>
                                    <?php
                                    $opciones = $c['fcf_opciones'] ?? '';
                                    if (!empty($opciones)) {
                                        $decoded = json_decode($opciones, true);
                                        $listOps = is_array($decoded) ? $decoded : explode(',', $opciones);
                                        foreach ($listOps as $op) {
                                            echo '<span class="badge badge-outline-secondary mr-1">' . htmlspecialchars(trim($op)) . '</span>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">—</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?= ($c['fcf_requerido'] ?? 0) ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-minus text-muted"></i>' ?>
                                </td>
                                <td class="text-center">
                                    <?= ($c['fcf_activo'] ?? 1) ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-secondary">No</span>' ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary js-editar-campo" title="Editar"
                                            data-campo="<?= htmlspecialchars(json_encode($c, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger js-eliminar-campo" title="Desactivar"
                                            data-id="<?= $c['fcf_campo_id'] ?>"
                                            data-nombre="<?= htmlspecialchars($c['fcf_etiqueta'] ?? $c['fcf_clave'] ?? '', ENT_QUOTES) ?>">
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

<!-- Modal -->
<div class="modal fade" id="modalCampo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCampo" method="POST"
                data-url-crear="<?= url('futbol', 'campoficha', 'crear') ?>"
                data-url-editar="<?= url('futbol', 'campoficha', 'editar') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="cf_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-sliders-h mr-2"></i>Nuevo Campo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombre (slug) <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="cf_nombre" class="form-control" required pattern="[a-z0-9_]+" title="Solo letras minúsculas, números y _">
                                <small class="text-muted">Identificador único, ej: posicion_juego</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Etiqueta <span class="text-danger">*</span></label>
                                <input type="text" name="etiqueta" id="cf_etiqueta" class="form-control" required>
                                <small class="text-muted">Nombre visible, ej: Posición de Juego</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select name="tipo" id="cf_tipo" class="form-control">
                                    <?php foreach ($tipos_campo as $t): ?>
                                    <option value="<?= $t ?>"><?= $t ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Orden</label>
                                <input type="number" name="orden" id="cf_orden" class="form-control" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group pt-4">
                                <div class="custom-control custom-checkbox mt-1">
                                    <input type="checkbox" class="custom-control-input" id="cf_requerido" name="requerido" value="1">
                                    <label class="custom-control-label" for="cf_requerido">Requerido</label>
                                </div>
                                <div class="custom-control custom-checkbox mt-1">
                                    <input type="checkbox" class="custom-control-input" id="cf_activo" name="activo" value="1" checked>
                                    <label class="custom-control-label" for="cf_activo">Activo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="divOpciones" style="display:none;">
                        <label>Opciones (separadas por coma)</label>
                        <textarea name="opciones" id="cf_opciones" class="form-control" rows="2" placeholder="Portero, Defensa, Mediocampista, Delantero"></textarea>
                        <small class="text-muted">Solo aplica cuando el tipo es SELECT.</small>
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
<script nonce="<?= cspNonce() ?>">
$(function() {
    var Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    var csrfToken = '<?= addslashes($csrf_token ?? '') ?>';

    function toggleTipo() {
        $('#divOpciones').toggle($('#cf_tipo').val() === 'SELECT');
    }

    // Tipo change
    $('#cf_tipo').on('change', toggleTipo);

    // Nuevo campo
    $('#btnNuevoCampo').on('click', function() {
        $('#formCampo')[0].reset();
        $('#cf_id').val('');
        $('#cf_activo').prop('checked', true);
        $('#modalTitulo').html('<i class="fas fa-sliders-h mr-2"></i>Nuevo Campo');
        $('#formCampo').data('mode', 'crear');
        toggleTipo();
        $('#modalCampo').modal('show');
    });

    // Editar campo
    $(document).on('click', '.js-editar-campo', function() {
        var c = JSON.parse($(this).attr('data-campo'));
        $('#cf_id').val(c.fcf_campo_id);
        $('#cf_nombre').val(c.fcf_clave || '');
        $('#cf_etiqueta').val(c.fcf_etiqueta || '');
        $('#cf_tipo').val(c.fcf_tipo || 'TEXT');
        $('#cf_orden').val(c.fcf_orden || 0);
        $('#cf_requerido').prop('checked', !!parseInt(c.fcf_requerido));
        $('#cf_activo').prop('checked', !!parseInt(c.fcf_activo));

        var ops = c.fcf_opciones || '';
        if (ops) {
            try {
                var parsed = JSON.parse(ops);
                ops = Array.isArray(parsed) ? parsed.join(', ') : ops;
            } catch(e) {}
        }
        $('#cf_opciones').val(ops);

        $('#modalTitulo').html('<i class="fas fa-edit mr-2"></i>Editar Campo');
        $('#formCampo').data('mode', 'editar');
        toggleTipo();
        $('#modalCampo').modal('show');
    });

    // Submit crear/editar (con confirmación)
    $('#formCampo').on('submit', function(e) {
        e.preventDefault();
        var mode    = $(this).data('mode') || 'crear';
        var action  = $(this).attr(mode === 'editar' ? 'data-url-editar' : 'data-url-crear');
        var isEdit  = mode === 'editar';
        var $form   = $(this);
        var $btn    = $form.find('[type=submit]');

        Swal.fire({
            title: isEdit ? '¿Guardar cambios?' : '¿Crear nuevo campo?',
            text:  isEdit ? '¿Deseas confirmar la actualización?' : '¿Deseas crear este campo?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '<?= $moduloColor ?>',
            cancelButtonColor: '#6c757d',
            confirmButtonText: isEdit ? 'Sí, guardar' : 'Sí, crear',
            cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $btn.prop('disabled', true);
            $.post(action, $form.serialize(), function(res) {
                if (res.success) {
                    $('#modalCampo').modal('hide');
                    Toast.fire({ icon: 'success', title: res.message });
                    setTimeout(function() { location.reload(); }, 1200);
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            }, 'json').fail(function() {
                Toast.fire({ icon: 'error', title: 'Error de comunicación' });
            }).always(function() { $btn.prop('disabled', false); });
        });
    });

    // Desactivar campo
    $(document).on('click', '.js-eliminar-campo', function() {
        var id     = $(this).data('id');
        var nombre = $(this).data('nombre');
        var $row   = $(this).closest('tr');
        Swal.fire({
            title: '¿Desactivar campo?',
            html: 'Se desactivará el campo <strong>' + nombre + '</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $.post('<?= url('futbol', 'campoficha', 'eliminar') ?>', { id: id, csrf_token: csrfToken }, function(res) {
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

    // Drag-to-reorder
    if (typeof Sortable !== 'undefined' && $('#tbodyCampos').length) {
        new Sortable(document.getElementById('tbodyCampos'), {
            animation: 150,
            handle: 'tr',
            onEnd: function() {
                var orden = [];
                $('#tbodyCampos tr').each(function(i) {
                    orden.push({ id: $(this).data('id'), orden: i + 1 });
                });
                $.post('<?= url('futbol', 'campoficha', 'reordenar') ?>', {
                    csrf_token: csrfToken,
                    orden: JSON.stringify(orden)
                }, function(res) {
                    if (res.success) Toast.fire({ icon: 'success', title: 'Orden actualizado' });
                }, 'json');
            }
        });
    }
});
</script>
<?php $scripts = ob_get_clean(); ?>
