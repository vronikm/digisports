<?php
/**
 * DigiSports Store - Perfil del Cliente
 */
$cliente     = $cliente ?? [];
$compras     = $compras ?? [];
$puntos      = $puntos ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
$nombreCompleto = htmlspecialchars(($cliente['cli_nombres'] ?? '') . ' ' . ($cliente['cli_apellidos'] ?? ''));
$catColors   = ['VIP'=>'warning','FRECUENTE'=>'info','REGULAR'=>'success','NUEVO'=>'secondary'];
$cat         = $cliente['scl_categoria'] ?? 'NUEVO';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-user mr-2" style="color:<?= $moduloColor ?>"></i><?= $nombreCompleto ?></h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <button class="btn btn-sm btn-outline-primary" onclick="abrirEdicion()"><i class="fas fa-edit mr-1"></i> Editar</button>
                    <a href="<?= url('store', 'cliente', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Info lateral -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-5x text-muted"></i>
                        </div>
                        <h5 class="mb-1"><?= $nombreCompleto ?></h5>
                        <p><span class="badge badge-<?= $catColors[$cat] ?? 'secondary' ?> px-3"><?= $cat ?></span></p>
                        <div class="row text-center mt-3">
                            <div class="col-4 border-right">
                                <h4 class="mb-0" style="color:<?= $moduloColor ?>"><?= intval($cliente['scl_puntos_disponibles'] ?? 0) ?></h4>
                                <small class="text-muted">Puntos</small>
                            </div>
                            <div class="col-4 border-right">
                                <h4 class="mb-0" style="color:<?= $moduloColor ?>"><?= intval($cliente['scl_num_compras'] ?? count($compras)) ?></h4>
                                <small class="text-muted">Compras</small>
                            </div>
                            <div class="col-4">
                                <h4 class="mb-0" style="color:<?= $moduloColor ?>">$<?= number_format($cliente['scl_total_compras'] ?? 0, 2) ?></h4>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Datos -->
                <div class="card shadow-sm">
                    <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-id-card mr-1"></i> Información</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted" width="40%">Tipo ID</td><td><?= htmlspecialchars($cliente['cli_tipo_identificacion'] ?? '—') ?></td></tr>
                            <tr><td class="text-muted">Identificación</td><td><code><?= htmlspecialchars($cliente['cli_identificacion'] ?? '—') ?></code></td></tr>
                            <tr><td class="text-muted">Email</td><td><?= htmlspecialchars($cliente['cli_email'] ?? '—') ?></td></tr>
                            <tr><td class="text-muted">Teléfono</td><td><?= htmlspecialchars($cliente['cli_telefono'] ?? '—') ?></td></tr>
                            <tr><td class="text-muted">Celular</td><td><?= htmlspecialchars($cliente['cli_celular'] ?? '—') ?></td></tr>
                            <tr><td class="text-muted">Dirección</td><td><?= htmlspecialchars($cliente['cli_direccion'] ?? '—') ?></td></tr>
                            <tr><td class="text-muted">Nacimiento</td><td><?= $cliente['cli_fecha_nacimiento'] ? date('d/m/Y', strtotime($cliente['cli_fecha_nacimiento'])) : '—' ?></td></tr>
                            <tr><td class="text-muted">Marketing</td><td><?= ($cliente['scl_acepta_marketing'] ?? 0) ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-light">No</span>' ?></td></tr>
                            <tr><td class="text-muted">Estado</td><td><?= ($cliente['scl_activo'] ?? 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>' ?></td></tr>
                            <tr><td class="text-muted">Registro</td><td><small><?= isset($cliente['cli_fecha_registro']) ? date('d/m/Y H:i', strtotime($cliente['cli_fecha_registro'])) : '—' ?></small></td></tr>
                        </table>
                    </div>
                </div>
                <?php if (!empty($cliente['scl_notas'])): ?>
                <div class="card shadow-sm">
                    <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-sticky-note mr-1"></i> Notas</h6></div>
                    <div class="card-body"><small><?= nl2br(htmlspecialchars($cliente['scl_notas'])) ?></small></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Historial -->
            <div class="col-md-8">
                <!-- Compras -->
                <div class="card shadow-sm">
                    <div class="card-header py-2">
                        <h6 class="mb-0"><i class="fas fa-shopping-bag mr-1"></i> Últimas Compras <span class="badge badge-light ml-1"><?= count($compras) ?></span></h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($compras)): ?>
                        <div class="text-center py-4 text-muted"><i class="fas fa-receipt fa-2x mb-2 opacity-50"></i><p>Sin compras registradas</p></div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="thead-light"><tr><th>Nº</th><th>Fecha</th><th>Tipo</th><th class="text-center">Estado</th><th class="text-right">Total</th><th></th></tr></thead>
                                <tbody>
                                    <?php foreach ($compras as $c): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($c['ven_numero'] ?? $c['ven_venta_id']) ?></code></td>
                                        <td><small><?= date('d/m/Y H:i', strtotime($c['ven_fecha'])) ?></small></td>
                                        <td><small><?= htmlspecialchars($c['ven_tipo_documento'] ?? 'FACTURA') ?></small></td>
                                        <td class="text-center">
                                            <?php
                                            $estC = ['COMPLETADA'=>'success','ANULADA'=>'danger','PENDIENTE'=>'warning'];
                                            $est = $c['ven_estado'] ?? 'COMPLETADA';
                                            ?>
                                            <span class="badge badge-<?= $estC[$est] ?? 'light' ?>"><?= $est ?></span>
                                        </td>
                                        <td class="text-right font-weight-bold">$<?= number_format($c['ven_total'] ?? 0, 2) ?></td>
                                        <td><a href="<?= url('store', 'venta', 'ver', ['id' => $c['ven_venta_id']]) ?>" class="btn btn-xs btn-outline-info"><i class="fas fa-eye"></i></a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Historial de Puntos -->
                <div class="card shadow-sm">
                    <div class="card-header py-2">
                        <h6 class="mb-0"><i class="fas fa-star text-warning mr-1"></i> Historial de Puntos</h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($puntos)): ?>
                        <div class="text-center py-4 text-muted"><i class="fas fa-star fa-2x mb-2 opacity-50"></i><p>Sin movimientos de puntos</p></div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="thead-light"><tr><th>Fecha</th><th>Tipo</th><th class="text-right">Puntos</th><th>Descripción</th></tr></thead>
                                <tbody>
                                    <?php foreach ($puntos as $pt): ?>
                                    <tr>
                                        <td><small><?= date('d/m/Y H:i', strtotime($pt['cpl_fecha_registro'])) ?></small></td>
                                        <td>
                                            <?php if (($pt['cpl_tipo'] ?? '') === 'ACUMULACION'): ?>
                                            <span class="badge badge-success"><i class="fas fa-plus mr-1"></i>Acumulación</span>
                                            <?php elseif (($pt['cpl_tipo'] ?? '') === 'CANJE'): ?>
                                            <span class="badge badge-info"><i class="fas fa-gift mr-1"></i>Canje</span>
                                            <?php else: ?>
                                            <span class="badge badge-warning"><?= htmlspecialchars($pt['cpl_tipo'] ?? '—') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right font-weight-bold <?= ($pt['cpl_puntos'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= ($pt['cpl_puntos'] ?? 0) >= 0 ? '+' : '' ?><?= intval($pt['cpl_puntos'] ?? 0) ?>
                                        </td>
                                        <td><small class="text-muted"><?= htmlspecialchars($pt['cpl_descripcion'] ?? '') ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Edición -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:<?= $moduloColor ?>;color:white">
                <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Editar Cliente</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formEditarCliente">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" value="<?= $cliente['cli_cliente_id'] ?? 0 ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label class="small">Tipo ID</label>
                                <select name="tipo_id" class="form-control form-control-sm">
                                    <?php foreach (['CED'=>'Cédula','RUC'=>'RUC','PAS'=>'Pasaporte'] as $tk=>$tv): ?>
                                    <option value="<?= $tk ?>" <?= ($cliente['cli_tipo_identificacion'] ?? '') === $tk ? 'selected' : '' ?>><?= $tv ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group"><label class="small">Identificación</label><input type="text" name="identificacion" class="form-control form-control-sm" value="<?= htmlspecialchars($cliente['cli_identificacion'] ?? '') ?>"></div>
                            <div class="form-group"><label class="small">Nombres *</label><input type="text" name="nombres" class="form-control form-control-sm" value="<?= htmlspecialchars($cliente['cli_nombres'] ?? '') ?>" required></div>
                            <div class="form-group"><label class="small">Apellidos</label><input type="text" name="apellidos" class="form-control form-control-sm" value="<?= htmlspecialchars($cliente['cli_apellidos'] ?? '') ?>"></div>
                            <div class="form-group"><label class="small">Email</label><input type="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($cliente['cli_email'] ?? '') ?>"></div>
                            <div class="form-group"><label class="small">Teléfono</label><input type="text" name="telefono" class="form-control form-control-sm" value="<?= htmlspecialchars($cliente['cli_telefono'] ?? '') ?>"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group"><label class="small">Celular</label><input type="text" name="celular" class="form-control form-control-sm" value="<?= htmlspecialchars($cliente['cli_celular'] ?? '') ?>"></div>
                            <div class="form-group"><label class="small">Dirección</label><input type="text" name="direccion" class="form-control form-control-sm" value="<?= htmlspecialchars($cliente['cli_direccion'] ?? '') ?>"></div>
                            <div class="form-group"><label class="small">Fecha Nacimiento</label><input type="date" name="fecha_nacimiento" class="form-control form-control-sm" value="<?= $cliente['cli_fecha_nacimiento'] ?? '' ?>"></div>
                            <div class="form-group"><label class="small">Estado</label>
                                <select name="activo" class="form-control form-control-sm">
                                    <option value="1" <?= ($cliente['scl_activo'] ?? 1) == 1 ? 'selected' : '' ?>>Activo</option>
                                    <option value="0" <?= ($cliente['scl_activo'] ?? 1) == 0 ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                            <div class="custom-control custom-switch mt-3">
                                <input type="checkbox" class="custom-control-input" id="swMarketing" name="acepta_marketing" value="1" <?= ($cliente['scl_acepta_marketing'] ?? 0) ? 'checked' : '' ?>>
                                <label class="custom-control-label small" for="swMarketing">Acepta marketing</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group"><label class="small">Notas (Store)</label><textarea name="notas" rows="2" class="form-control form-control-sm"><?= htmlspecialchars($cliente['scl_notas'] ?? '') ?></textarea></div>
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
function abrirEdicion() { $('#modalEditarCliente').modal('show'); }

document.getElementById('formEditarCliente').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    if (!fd.get('acepta_marketing')) fd.set('acepta_marketing', '0');
    fetch('<?= url('store', 'cliente', 'editar') ?>', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            Swal.fire({ icon: 'success', title: d.message, timer: 1500, showConfirmButton: false }).then(function() { location.reload(); });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: d.message });
        }
    }).catch(function() { Swal.fire({ icon: 'error', title: 'Error de conexión' }); });
});
</script>
<?php $scripts = ob_get_clean(); ?>
