<?php
/**
 * DigiSports Natación - Gestión de Egresos
 */
$egresos     = $egresos ?? [];
$sedes       = $sedes ?? [];
$totales     = $totales ?? [];
$moduloColor = $modulo_actual['color'] ?? '#0EA5E9';
$sedeActiva  = $sede_activa ?? null;

$categorias = ['MANTENIMIENTO','INSUMOS','QUIMICOS','SERVICIOS','PERSONAL','EQUIPAMIENTO','SEGUROS','MARKETING','OTROS'];
$catIcons   = ['MANTENIMIENTO'=>'fas fa-tools','INSUMOS'=>'fas fa-box','QUIMICOS'=>'fas fa-flask','SERVICIOS'=>'fas fa-concierge-bell','PERSONAL'=>'fas fa-users','EQUIPAMIENTO'=>'fas fa-dumbbell','SEGUROS'=>'fas fa-shield-alt','MARKETING'=>'fas fa-bullhorn','OTROS'=>'fas fa-ellipsis-h'];
$catColors  = ['MANTENIMIENTO'=>'#3B82F6','INSUMOS'=>'#10B981','QUIMICOS'=>'#8B5CF6','SERVICIOS'=>'#F59E0B','PERSONAL'=>'#EF4444','EQUIPAMIENTO'=>'#06B6D4','SEGUROS'=>'#6366F1','MARKETING'=>'#EC4899','OTROS'=>'#6B7280'];

$totalRegistrado = 0; $totalPagado = 0; $totalAnulado = 0;
foreach ($totales as $t) {
    if (($t['estado'] ?? '') === 'REGISTRADO') $totalRegistrado = (float)$t['total'];
    if (($t['estado'] ?? '') === 'PAGADO')     $totalPagado     = (float)$t['total'];
    if (($t['estado'] ?? '') === 'ANULADO')    $totalAnulado    = (float)$t['total'];
}
$totalActivo = $totalRegistrado + $totalPagado;

// Totales por categoría
$totalesCat = [];
foreach ($totales as $t) {
    $cat = $t['categoria'] ?? '';
    if (!isset($totalesCat[$cat])) $totalesCat[$cat] = 0;
    if (($t['estado'] ?? '') !== 'ANULADO') $totalesCat[$cat] += (float)$t['total'];
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-money-bill-wave mr-2" style="color:<?= $moduloColor ?>"></i>Egresos</h1></div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <?php if ($sedeActiva): ?>
                    <span class="badge badge-info mr-2"><i class="fas fa-building mr-1"></i>Filtrado por sede</span>
                    <?php endif; ?>
                    <button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Nuevo Egreso</button>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Resumen -->
        <div class="row mb-3">
            <div class="col-md-3"><div class="small-box bg-info"><div class="inner"><h4>$<?= number_format($totalActivo, 2) ?></h4><p>Total Activo</p></div><div class="icon"><i class="fas fa-money-bill-wave"></i></div></div></div>
            <div class="col-md-3"><div class="small-box bg-warning"><div class="inner"><h4>$<?= number_format($totalRegistrado, 2) ?></h4><p>Registrado</p></div><div class="icon"><i class="fas fa-file-invoice"></i></div></div></div>
            <div class="col-md-3"><div class="small-box bg-success"><div class="inner"><h4>$<?= number_format($totalPagado, 2) ?></h4><p>Pagado</p></div><div class="icon"><i class="fas fa-check-circle"></i></div></div></div>
            <div class="col-md-3"><div class="small-box bg-danger"><div class="inner"><h4>$<?= number_format($totalAnulado, 2) ?></h4><p>Anulado</p></div><div class="icon"><i class="fas fa-ban"></i></div></div></div>
        </div>

        <!-- Filtros -->
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('natacion', 'egreso', 'index') ?>" class="row align-items-end">
                    <div class="col-md-2">
                        <label class="small mb-0">Sede</label>
                        <select name="sede_id" class="form-control form-control-sm">
                            <option value="">— Todas —</option>
                            <?php foreach ($sedes as $s): ?>
                            <option value="<?= $s['sed_sede_id'] ?>" <?= ($_GET['sede_id'] ?? '') == $s['sed_sede_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['sed_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-0">Categoría</label>
                        <select name="categoria" class="form-control form-control-sm">
                            <option value="">— Todas —</option>
                            <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c ?>" <?= ($_GET['categoria'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-0">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">— Todos —</option>
                            <option value="REGISTRADO" <?= ($_GET['estado'] ?? '') === 'REGISTRADO' ? 'selected' : '' ?>>Registrado</option>
                            <option value="PAGADO" <?= ($_GET['estado'] ?? '') === 'PAGADO' ? 'selected' : '' ?>>Pagado</option>
                            <option value="ANULADO" <?= ($_GET['estado'] ?? '') === 'ANULADO' ? 'selected' : '' ?>>Anulado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-0">Mes</label>
                        <input type="month" name="mes" class="form-control form-control-sm" value="<?= $_GET['mes'] ?? date('Y-m') ?>">
                    </div>
                    <div class="col-md-2 pt-3">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i>Filtrar</button>
                        <a href="<?= url('natacion', 'egreso', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card shadow-sm">
            <div class="card-header py-2">
                <span class="badge badge-secondary"><?= count($egresos) ?> egreso(s)</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($egresos)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-money-bill-wave fa-3x mb-3 opacity-50"></i><p>No hay egresos registrados</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Concepto</th>
                                <th>Categoría</th>
                                <?php if (!$sedeActiva): ?><th>Sede</th><?php endif; ?>
                                <th class="text-right">Monto</th>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Método</th>
                                <th class="text-center">Estado</th>
                                <th width="120" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($egresos as $i => $e): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($e['neg_concepto']) ?></strong>
                                    <?php if (!empty($e['neg_factura_ref'])): ?><br><small class="text-muted"><i class="fas fa-file-invoice mr-1"></i><?= htmlspecialchars($e['neg_factura_ref']) ?></small><?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge" style="background:<?= $catColors[$e['neg_categoria']] ?? '#6B7280' ?>;color:white;">
                                        <i class="<?= $catIcons[$e['neg_categoria']] ?? 'fas fa-tag' ?> mr-1"></i><?= $e['neg_categoria'] ?>
                                    </span>
                                </td>
                                <?php if (!$sedeActiva): ?>
                                <td><?= htmlspecialchars($e['sede_nombre'] ?? '—') ?></td>
                                <?php endif; ?>
                                <td class="text-right font-weight-bold">$<?= number_format((float)$e['neg_monto'], 2) ?></td>
                                <td><?= date('d/m/Y', strtotime($e['neg_fecha'])) ?></td>
                                <td><?= htmlspecialchars($e['neg_proveedor'] ?? '—') ?></td>
                                <td><span class="badge badge-light"><?= htmlspecialchars($e['neg_metodo_pago'] ?? '—') ?></span></td>
                                <td class="text-center">
                                    <?php $eb = ['REGISTRADO'=>'warning','PAGADO'=>'success','ANULADO'=>'danger'][$e['neg_estado']] ?? 'secondary'; ?>
                                    <span class="badge badge-<?= $eb ?>"><?= $e['neg_estado'] ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='editarEgreso(<?= json_encode($e) ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                                        <?php if ($e['neg_estado'] !== 'ANULADO'): ?>
                                        <button class="btn btn-outline-danger" onclick="anularEgreso(<?= $e['neg_egreso_id'] ?>)" title="Anular"><i class="fas fa-ban"></i></button>
                                        <?php endif; ?>
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

<!-- Modal Egreso -->
<div class="modal fade" id="modalEgreso" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEgreso" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="egr_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-money-bill-wave mr-2"></i>Nuevo Egreso</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8"><div class="form-group"><label>Concepto <span class="text-danger">*</span></label><input type="text" name="concepto" id="egr_concepto" class="form-control" required></div></div>
                        <div class="col-md-4">
                            <div class="form-group"><label>Categoría <span class="text-danger">*</span></label>
                                <select name="categoria" id="egr_categoria" class="form-control" required>
                                    <?php foreach ($categorias as $c): ?>
                                    <option value="<?= $c ?>"><?= $c ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>Monto <span class="text-danger">*</span></label><input type="number" name="monto" id="egr_monto" class="form-control" step="0.01" min="0.01" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Fecha</label><input type="date" name="fecha" id="egr_fecha" class="form-control" value="<?= date('Y-m-d') ?>"></div></div>
                        <div class="col-md-4">
                            <div class="form-group"><label>Sede</label>
                                <select name="sede_id" id="egr_sede" class="form-control">
                                    <option value="">— Sin sede —</option>
                                    <?php foreach ($sedes as $s): ?>
                                    <option value="<?= $s['sed_sede_id'] ?>" <?= $sedeActiva == $s['sed_sede_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['sed_nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Proveedor</label><input type="text" name="proveedor" id="egr_proveedor" class="form-control"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Ref. Factura</label><input type="text" name="factura_ref" id="egr_factura" class="form-control" placeholder="Nro. factura"></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group"><label>Método Pago</label>
                                <select name="metodo_pago" id="egr_metodo" class="form-control">
                                    <option value="EFECTIVO">Efectivo</option>
                                    <option value="TRANSFERENCIA">Transferencia</option>
                                    <option value="TARJETA">Tarjeta</option>
                                    <option value="CHEQUE">Cheque</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4"><div class="form-group"><label>Referencia Pago</label><input type="text" name="referencia_pago" id="egr_referencia" class="form-control"></div></div>
                        <div class="col-md-4">
                            <div class="form-group"><label>Estado</label>
                                <select name="estado" id="egr_estado" class="form-control">
                                    <option value="REGISTRADO">Registrado</option>
                                    <option value="PAGADO">Pagado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group"><label>Notas</label><textarea name="notas" id="egr_notas" class="form-control" rows="2"></textarea></div>
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
var urlCrear  = '<?= url('natacion', 'egreso', 'crear') ?>';
var urlEditar = '<?= url('natacion', 'egreso', 'editar') ?>';

function abrirModal() {
    document.getElementById('formEgreso').reset();
    document.getElementById('egr_id').value = '';
    document.getElementById('egr_fecha').value = '<?= date('Y-m-d') ?>';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-money-bill-wave mr-2"></i>Nuevo Egreso';
    document.getElementById('formEgreso').action = urlCrear;
    $('#modalEgreso').modal('show');
}

function editarEgreso(e) {
    document.getElementById('egr_id').value = e.neg_egreso_id;
    document.getElementById('egr_concepto').value = e.neg_concepto || '';
    document.getElementById('egr_categoria').value = e.neg_categoria || 'OTROS';
    document.getElementById('egr_monto').value = e.neg_monto || '';
    document.getElementById('egr_fecha').value = e.neg_fecha || '';
    document.getElementById('egr_sede').value = e.neg_sede_id || '';
    document.getElementById('egr_proveedor').value = e.neg_proveedor || '';
    document.getElementById('egr_factura').value = e.neg_factura_ref || '';
    document.getElementById('egr_metodo').value = e.neg_metodo_pago || 'EFECTIVO';
    document.getElementById('egr_referencia').value = e.neg_referencia_pago || '';
    document.getElementById('egr_estado').value = e.neg_estado || 'REGISTRADO';
    document.getElementById('egr_notas').value = e.neg_notas || '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Egreso';
    document.getElementById('formEgreso').action = urlEditar;
    $('#modalEgreso').modal('show');
}

function anularEgreso(id) {
    Swal.fire({
        title: '¿Anular egreso?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#d33', confirmButtonText: 'Sí, anular', cancelButtonText: 'No'
    }).then(function(r) {
        if (r.isConfirmed) window.location.href = '<?= url('natacion', 'egreso', 'anular') ?>&id=' + id;
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>
