<?php
/**
 * DigiSports Store - Proveedores
 */
$proveedores = $proveedores ?? [];
$buscar      = $buscar ?? '';
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-truck mr-2" style="color:<?= $moduloColor ?>"></i>Proveedores <small class="text-muted">(<?= count($proveedores) ?>)</small></h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i> Nuevo Proveedor</button>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtro -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <form method="POST" action="<?= url('store', 'proveedor', 'index') ?>" class="row align-items-end">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <div class="col-md-5">
                        <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Razón social, RUC, contacto..." value="<?= htmlspecialchars($buscar) ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-search mr-1"></i> Buscar</button>
                        <a href="<?= url('store', 'proveedor', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times mr-1"></i> Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($proveedores)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-truck fa-3x mb-3 opacity-50"></i><p>No se encontraron proveedores</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th>Razón Social</th><th>RUC/CI</th><th>Contacto</th><th>Teléfono</th><th>Email</th><th class="text-center">Crédito</th><th class="text-center">Órdenes</th><th class="text-center">Estado</th><th width="120" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proveedores as $p): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($p['prv_razon_social']) ?></strong>
                                    <?php if (!empty($p['prv_nombre_comercial'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($p['prv_nombre_comercial']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= htmlspecialchars($p['prv_ruc_ci'] ?? '—') ?></code></td>
                                <td><small><?= htmlspecialchars($p['prv_contacto_nombre'] ?? '—') ?></small></td>
                                <td><small><?= htmlspecialchars($p['prv_telefono'] ?? $p['prv_celular'] ?? '—') ?></small></td>
                                <td><small><?= htmlspecialchars($p['prv_email'] ?? '—') ?></small></td>
                                <td class="text-center"><span class="badge badge-light"><?= intval($p['prv_dias_credito'] ?? 0) ?> días</span></td>
                                <td class="text-center"><span class="badge badge-info"><?= intval($p['total_ordenes'] ?? 0) ?></span></td>
                                <td class="text-center"><?= ($p['prv_activo'] ?? 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>' ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='editarProveedor(<?= json_encode($p) ?>)'><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-danger btn-del-prv" data-id="<?= $p['prv_proveedor_id'] ?>" data-nombre="<?= htmlspecialchars($p['prv_razon_social']) ?>"><i class="fas fa-trash"></i></button>
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
<div class="modal fade" id="modalProveedor" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:<?= $moduloColor ?>;color:white">
                <h5 class="modal-title" id="modalTitulo"><i class="fas fa-truck mr-1"></i> Nuevo Proveedor</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formProveedor">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="prvId" value="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label class="small">Razón Social *</label><input type="text" name="razon_social" id="prvRazonSocial" class="form-control form-control-sm" required></div>
                            <div class="form-group"><label class="small">Nombre Comercial</label><input type="text" name="nombre_comercial" id="prvNombreComercial" class="form-control form-control-sm"></div>
                            <div class="form-group"><label class="small">RUC / CI</label><input type="text" name="ruc_ci" id="prvRucCi" class="form-control form-control-sm"></div>
                            <div class="form-group"><label class="small">Contacto</label><input type="text" name="contacto_nombre" id="prvContacto" class="form-control form-control-sm"></div>
                            <div class="form-group"><label class="small">Días de Crédito</label><input type="number" name="dias_credito" id="prvDiasCredito" class="form-control form-control-sm" value="0" min="0"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group"><label class="small">Email</label><input type="email" name="email" id="prvEmail" class="form-control form-control-sm"></div>
                            <div class="form-group"><label class="small">Teléfono</label><input type="text" name="telefono" id="prvTelefono" class="form-control form-control-sm"></div>
                            <div class="form-group"><label class="small">Celular</label><input type="text" name="celular" id="prvCelular" class="form-control form-control-sm"></div>
                            <div class="form-group"><label class="small">Dirección</label><input type="text" name="direccion" id="prvDireccion" class="form-control form-control-sm"></div>
                            <div class="form-group"><label class="small">Ciudad</label><input type="text" name="ciudad" id="prvCiudad" class="form-control form-control-sm"></div>
                        </div>
                        <div class="col-12">
                            <div class="form-group"><label class="small">Notas</label><textarea name="notas" id="prvNotas" rows="2" class="form-control form-control-sm"></textarea></div>
                        </div>
                        <div class="col-12" id="divActivo" style="display:none">
                            <div class="form-group"><label class="small">Estado</label>
                                <select name="activo" id="prvActivo" class="form-control form-control-sm"><option value="1">Activo</option><option value="0">Inactivo</option></select>
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
var urlCrear   = '<?= url('store', 'proveedor', 'crear') ?>';
var urlEditar  = '<?= url('store', 'proveedor', 'editar') ?>';
var urlEliminar = '<?= url('store', 'proveedor', 'eliminar') ?>';
var esEdicion  = false;

function abrirModal() {
    esEdicion = false;
    document.getElementById('formProveedor').reset();
    document.getElementById('prvId').value = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-truck mr-1"></i> Nuevo Proveedor';
    document.getElementById('divActivo').style.display = 'none';
    $('#modalProveedor').modal('show');
}

function editarProveedor(p) {
    esEdicion = true;
    document.getElementById('prvId').value = p.prv_proveedor_id;
    document.getElementById('prvRazonSocial').value = p.prv_razon_social || '';
    document.getElementById('prvNombreComercial').value = p.prv_nombre_comercial || '';
    document.getElementById('prvRucCi').value = p.prv_ruc_ci || '';
    document.getElementById('prvContacto').value = p.prv_contacto_nombre || '';
    document.getElementById('prvEmail').value = p.prv_email || '';
    document.getElementById('prvTelefono').value = p.prv_telefono || '';
    document.getElementById('prvCelular').value = p.prv_celular || '';
    document.getElementById('prvDireccion').value = p.prv_direccion || '';
    document.getElementById('prvCiudad').value = p.prv_ciudad || '';
    document.getElementById('prvDiasCredito').value = p.prv_dias_credito || 0;
    document.getElementById('prvNotas').value = p.prv_notas || '';
    document.getElementById('prvActivo').value = p.prv_activo ?? 1;
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-1"></i> Editar Proveedor';
    document.getElementById('divActivo').style.display = 'block';
    $('#modalProveedor').modal('show');
}

document.getElementById('formProveedor').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    fetch(esEdicion ? urlEditar : urlCrear, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            Swal.fire({ icon: 'success', title: d.message, timer: 1500, showConfirmButton: false }).then(function() { location.reload(); });
        } else { Swal.fire({ icon: 'error', title: 'Error', text: d.message }); }
    }).catch(function() { Swal.fire({ icon: 'error', title: 'Error de conexión' }); });
});

document.querySelectorAll('.btn-del-prv').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id, nombre = this.dataset.nombre;
        Swal.fire({
            title: '¿Eliminar proveedor?', html: '<strong>' + nombre + '</strong>', icon: 'warning',
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
