<?php
/**
 * DigiSports Natación - Campos de Ficha Personalizados
 */
$campos       = $campos ?? [];
$tipos_campo  = $tipos_campo ?? ['TEXT','NUMBER','DATE','SELECT','CHECKBOX','TEXTAREA'];
$moduloColor  = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-sliders-h mr-2" style="color:<?= $moduloColor ?>"></i>Campos de Ficha</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Nuevo Campo</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>Configure los campos adicionales que aparecerán en la ficha de cada alumno. Estos campos son personalizables por empresa.</div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($campos)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-sliders-h fa-3x mb-3 opacity-50"></i><p>No hay campos personalizados</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th width="50">Orden</th><th>Clave</th><th>Nombre</th><th>Tipo</th><th class="text-center">Obligatorio</th><th class="text-center">Activo</th><th width="130" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campos as $c): ?>
                            <tr class="<?= !$c['ncf_activo'] ? 'table-secondary' : '' ?>">
                                <td class="text-center"><?= $c['ncf_orden'] ?></td>
                                <td><code><?= htmlspecialchars($c['ncf_clave']) ?></code></td>
                                <td><strong><?= htmlspecialchars($c['ncf_etiqueta']) ?></strong></td>
                                <td><span class="badge badge-light"><?= $c['ncf_tipo'] ?></span></td>
                                <td class="text-center"><?= $c['ncf_requerido'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-minus text-muted"></i>' ?></td>
                                <td class="text-center"><?= $c['ncf_activo'] ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-secondary">No</span>' ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='editarCampo(<?= json_encode($c) ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-danger" onclick="desactivarCampo(<?= $c['ncf_campo_id'] ?>)" title="Desactivar"><i class="fas fa-trash"></i></button>
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
            <form id="formCampo" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="cf_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-sliders-h mr-2"></i>Nuevo Campo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Clave <span class="text-danger">*</span></label><input type="text" name="clave" id="cf_clave" class="form-control" required pattern="[a-z0-9_]+" title="Solo letras minúsculas, números y _"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Nombre <span class="text-danger">*</span></label><input type="text" name="nombre" id="cf_nombre" class="form-control" required></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group"><label>Tipo</label>
                                <select name="tipo" id="cf_tipo" class="form-control" onchange="toggleOpciones()">
                                    <?php foreach ($tipos_campo as $t): ?>
                                    <option value="<?= $t ?>"><?= $t ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4"><div class="form-group"><label>Orden</label><input type="number" name="orden" id="cf_orden" class="form-control" min="0" value="0"></div></div>
                        <div class="col-md-4">
                            <div class="form-group"><label>&nbsp;</label>
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="cf_obligatorio" name="obligatorio" value="1">
                                    <label class="custom-control-label" for="cf_obligatorio">Obligatorio</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="divOpciones" style="display:none;">
                        <label>Opciones (separadas por coma)</label>
                        <input type="text" name="opciones" id="cf_opciones" class="form-control" placeholder="Opción 1, Opción 2, Opción 3">
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
var urlCrear = '<?= url('natacion', 'campoficha', 'crear') ?>';
var urlEditar = '<?= url('natacion', 'campoficha', 'editar') ?>';
function toggleOpciones() {
    document.getElementById('divOpciones').style.display = document.getElementById('cf_tipo').value === 'SELECT' ? '' : 'none';
}
function abrirModal() {
    document.getElementById('formCampo').reset(); document.getElementById('cf_id').value = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-sliders-h mr-2"></i>Nuevo Campo';
    document.getElementById('formCampo').action = urlCrear; toggleOpciones(); $('#modalCampo').modal('show');
}
function editarCampo(c) {
    document.getElementById('cf_id').value = c.ncf_campo_id;
    document.getElementById('cf_clave').value = c.ncf_clave || '';
    document.getElementById('cf_nombre').value = c.ncf_etiqueta || '';
    document.getElementById('cf_tipo').value = c.ncf_tipo || 'TEXT';
    document.getElementById('cf_orden').value = c.ncf_orden || 0;
    document.getElementById('cf_obligatorio').checked = !!parseInt(c.ncf_requerido);
    var ops = c.ncf_opciones ? JSON.parse(c.ncf_opciones) : [];
    document.getElementById('cf_opciones').value = ops.join(', ');
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Campo';
    document.getElementById('formCampo').action = urlEditar; toggleOpciones(); $('#modalCampo').modal('show');
}
function desactivarCampo(id) {
    Swal.fire({ title: '¿Desactivar campo?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí', cancelButtonText: 'No'
    }).then(function(r) { if (r.isConfirmed) window.location.href = '<?= url('natacion', 'campoficha', 'eliminar') ?>&id=' + id; });
}
</script>
<?php $scripts = ob_get_clean(); ?>
