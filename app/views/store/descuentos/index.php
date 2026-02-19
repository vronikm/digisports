<?php
/**
 * DigiSports Store - Descuentos y Promociones
 */
$descuentos  = $descuentos ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-tags mr-2" style="color:<?= $moduloColor ?>"></i>Descuentos y Promociones</h1></div>
            <div class="col-sm-6"><div class="float-sm-right">
                <button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i> Nuevo Descuento</button>
            </div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($descuentos)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-tags fa-3x mb-3 opacity-50"></i><p>No hay descuentos configurados</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th>Nombre</th><th>Código</th><th class="text-center">Tipo</th><th class="text-center">Valor</th><th>Vigencia</th><th class="text-center">Usos</th><th>Aplica a</th><th class="text-center">Estado</th><th width="120" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($descuentos as $d):
                                $activo = ($d['dsc_activo'] ?? 1);
                                $hoy = date('Y-m-d');
                                $vigente = true;
                                if ($d['dsc_fecha_fin'] && $hoy > $d['dsc_fecha_fin']) $vigente = false;
                                if ($d['dsc_usos_maximos'] && $d['dsc_usos_actuales'] >= $d['dsc_usos_maximos']) $vigente = false;
                            ?>
                            <tr class="<?= !$activo ? 'text-muted' : '' ?>">
                                <td><strong><?= htmlspecialchars($d['dsc_nombre']) ?></strong></td>
                                <td><?= $d['dsc_codigo'] ? '<code>' . htmlspecialchars($d['dsc_codigo']) . '</code>' : '<small class="text-muted">—</small>' ?></td>
                                <td class="text-center"><span class="badge badge-<?= $d['dsc_tipo'] === 'PORCENTAJE' ? 'info' : 'success' ?>"><?= $d['dsc_tipo'] ?></span></td>
                                <td class="text-center font-weight-bold">
                                    <?= $d['dsc_tipo'] === 'PORCENTAJE' ? $d['dsc_valor'] . '%' : '$' . number_format($d['dsc_valor'], 2) ?>
                                </td>
                                <td>
                                    <small>
                                        <?= $d['dsc_fecha_inicio'] ? date('d/m/Y', strtotime($d['dsc_fecha_inicio'])) : '—' ?>
                                        →
                                        <?= $d['dsc_fecha_fin'] ? date('d/m/Y', strtotime($d['dsc_fecha_fin'])) : '∞' ?>
                                    </small>
                                    <?php if (!$vigente && $activo): ?><br><small class="text-danger"><i class="fas fa-exclamation-circle"></i> Expirado</small><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light"><?= intval($d['dsc_usos_actuales'] ?? 0) ?><?= $d['dsc_usos_maximos'] ? '/' . $d['dsc_usos_maximos'] : '' ?></span>
                                </td>
                                <td><small><?= htmlspecialchars($d['dsc_aplica_a'] ?? 'TODOS') ?></small></td>
                                <td class="text-center"><?= $activo ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>' ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='editarDescuento(<?= json_encode($d) ?>)'><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-danger btn-del-dsc" data-id="<?= $d['dsc_descuento_id'] ?>" data-nombre="<?= htmlspecialchars($d['dsc_nombre']) ?>"><i class="fas fa-trash"></i></button>
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
<div class="modal fade" id="modalDescuento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:<?= $moduloColor ?>;color:white">
                <h5 class="modal-title" id="modalTitulo"><i class="fas fa-tags mr-1"></i> Nuevo Descuento</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formDescuento">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="dscId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label class="small">Nombre *</label><input type="text" name="nombre" id="dscNombre" class="form-control form-control-sm" required></div>
                            <div class="form-group"><label class="small">Código / Cupón</label><input type="text" name="codigo" id="dscCodigo" class="form-control form-control-sm" placeholder="Ej: VERANO25"></div>
                            <div class="row">
                                <div class="col-6"><div class="form-group"><label class="small">Tipo *</label>
                                    <select name="tipo" id="dscTipo" class="form-control form-control-sm">
                                        <option value="PORCENTAJE">Porcentaje (%)</option>
                                        <option value="MONTO_FIJO">Monto Fijo ($)</option>
                                    </select>
                                </div></div>
                                <div class="col-6"><div class="form-group"><label class="small">Valor *</label><input type="number" name="valor" id="dscValor" class="form-control form-control-sm" step="0.01" min="0" required></div></div>
                            </div>
                            <div class="row">
                                <div class="col-6"><div class="form-group"><label class="small">Compra Mínima</label><input type="number" name="minimo_compra" id="dscMinimo" class="form-control form-control-sm" step="0.01" min="0"></div></div>
                                <div class="col-6"><div class="form-group"><label class="small">Máx. Descuento</label><input type="number" name="maximo_descuento" id="dscMaximo" class="form-control form-control-sm" step="0.01" min="0"></div></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-6"><div class="form-group"><label class="small">Inicio</label><input type="date" name="fecha_inicio" id="dscInicio" class="form-control form-control-sm"></div></div>
                                <div class="col-6"><div class="form-group"><label class="small">Fin</label><input type="date" name="fecha_fin" id="dscFin" class="form-control form-control-sm"></div></div>
                            </div>
                            <div class="form-group"><label class="small">Usos Máximos</label><input type="number" name="usos_maximos" id="dscUsos" class="form-control form-control-sm" min="0" placeholder="0 = ilimitado"></div>
                            <div class="row">
                                <div class="col-6"><div class="form-group"><label class="small">Aplica a</label>
                                    <select name="aplica_a" id="dscAplicaA" class="form-control form-control-sm">
                                        <option value="TODOS">Todos</option>
                                        <option value="CATEGORIA">Categoría</option>
                                        <option value="PRODUCTO">Producto</option>
                                        <option value="MARCA">Marca</option>
                                    </select>
                                </div></div>
                                <div class="col-6"><div class="form-group"><label class="small">ID Aplica</label><input type="number" name="aplica_id" id="dscAplicaId" class="form-control form-control-sm" min="0"></div></div>
                            </div>
                            <div id="divActivoDsc" style="display:none">
                                <div class="form-group"><label class="small">Estado</label>
                                    <select name="activo" id="dscActivo" class="form-control form-control-sm"><option value="1">Activo</option><option value="0">Inactivo</option></select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
var urlCrear = '<?= url('store', 'descuento', 'crear') ?>';
var urlEditar = '<?= url('store', 'descuento', 'editar') ?>';
var urlEliminar = '<?= url('store', 'descuento', 'eliminar') ?>';
var esEdicion = false;

function abrirModal() {
    esEdicion = false;
    document.getElementById('formDescuento').reset();
    document.getElementById('dscId').value = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-tags mr-1"></i> Nuevo Descuento';
    document.getElementById('divActivoDsc').style.display = 'none';
    $('#modalDescuento').modal('show');
}

function editarDescuento(d) {
    esEdicion = true;
    document.getElementById('dscId').value = d.dsc_descuento_id;
    document.getElementById('dscNombre').value = d.dsc_nombre || '';
    document.getElementById('dscCodigo').value = d.dsc_codigo || '';
    document.getElementById('dscTipo').value = d.dsc_tipo || 'PORCENTAJE';
    document.getElementById('dscValor').value = d.dsc_valor || 0;
    document.getElementById('dscMinimo').value = d.dsc_minimo_compra || '';
    document.getElementById('dscMaximo').value = d.dsc_maximo_descuento || '';
    document.getElementById('dscInicio').value = d.dsc_fecha_inicio || '';
    document.getElementById('dscFin').value = d.dsc_fecha_fin || '';
    document.getElementById('dscUsos').value = d.dsc_usos_maximos || '';
    document.getElementById('dscAplicaA').value = d.dsc_aplica_a || 'TODOS';
    document.getElementById('dscAplicaId').value = d.dsc_aplica_id || '';
    document.getElementById('dscActivo').value = d.dsc_activo ?? 1;
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-1"></i> Editar Descuento';
    document.getElementById('divActivoDsc').style.display = 'block';
    $('#modalDescuento').modal('show');
}

document.getElementById('formDescuento').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    fetch(esEdicion ? urlEditar : urlCrear, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) { Swal.fire({ icon: 'success', title: d.message, timer: 1500, showConfirmButton: false }).then(function() { location.reload(); }); }
        else { Swal.fire({ icon: 'error', title: 'Error', text: d.message }); }
    }).catch(function() { Swal.fire({ icon: 'error', title: 'Error de conexión' }); });
});

document.querySelectorAll('.btn-del-dsc').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id, nombre = this.dataset.nombre;
        Swal.fire({
            title: '¿Eliminar descuento?', html: '<strong>' + nombre + '</strong>', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (r.isConfirmed) {
                var fd = new FormData();
                fd.append('csrf_token', '<?= htmlspecialchars($csrf_token ?? '') ?>');
                fd.append('id', id);
                fetch(urlEliminar, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) { Swal.fire({ icon: 'success', title: d.message, timer: 1500, showConfirmButton: false }).then(function() { location.reload(); }); }
                    else { Swal.fire({ icon: 'error', title: 'Error', text: d.message }); }
                });
            }
        });
    });
});
</script>
<?php $scripts = ob_get_clean(); ?>
