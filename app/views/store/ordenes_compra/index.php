<?php
/**
 * DigiSports Store - Órdenes de Compra
 */
$ordenes      = $ordenes ?? [];
$proveedores  = $proveedores ?? [];
$estadoFiltro = $estadoFiltro ?? '';
$fechaDesde   = $fechaDesde ?? date('Y-m-01');
$fechaHasta   = $fechaHasta ?? date('Y-m-d');
$moduloColor  = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-file-invoice mr-2" style="color:<?= $moduloColor ?>"></i>Órdenes de Compra</h1></div>
            <div class="col-sm-6"><div class="float-sm-right">
                <a href="<?= url('store', 'ordenCompra', 'crear') ?>" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white"><i class="fas fa-plus mr-1"></i> Nueva Orden</a>
            </div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('store', 'ordenCompra', 'index') ?>" class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <?php foreach (['BORRADOR','ENVIADA','PARCIAL','RECIBIDA','CANCELADA'] as $e): ?>
                            <option value="<?= $e ?>" <?= $estadoFiltro === $e ? 'selected' : '' ?>><?= $e ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2"><label class="small mb-1">Desde</label><input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= $fechaDesde ?>"></div>
                    <div class="col-md-2"><label class="small mb-1">Hasta</label><input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= $fechaHasta ?>"></div>
                    <div class="col-md-5">
                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-search mr-1"></i> Filtrar</button>
                        <a href="<?= url('store', 'ordenCompra', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times mr-1"></i> Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($ordenes)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-file-invoice fa-3x mb-3 opacity-50"></i><p>No hay órdenes de compra</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th>Nº</th><th>Proveedor</th><th>Fecha</th><th>Entrega Esp.</th><th class="text-center">Estado</th><th class="text-right">Subtotal</th><th class="text-right">IVA</th><th class="text-right">Total</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordenes as $o):
                                $estO = ['BORRADOR'=>'secondary','ENVIADA'=>'primary','PARCIAL'=>'warning','RECIBIDA'=>'success','CANCELADA'=>'danger'];
                                $estado = $o['orc_estado'] ?? 'BORRADOR';
                            ?>
                            <tr>
                                <td><code><?= htmlspecialchars($o['orc_numero'] ?? $o['orc_orden_id']) ?></code></td>
                                <td>
                                    <strong><?= htmlspecialchars($o['prv_razon_social'] ?? '—') ?></strong>
                                    <?php if (!empty($o['prv_nombre_comercial'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($o['prv_nombre_comercial']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= date('d/m/Y', strtotime($o['orc_fecha_orden'])) ?></small></td>
                                <td><small><?= $o['orc_fecha_entrega_esperada'] ? date('d/m/Y', strtotime($o['orc_fecha_entrega_esperada'])) : '—' ?></small></td>
                                <td class="text-center"><span class="badge badge-<?= $estO[$estado] ?? 'light' ?>"><?= $estado ?></span></td>
                                <td class="text-right">$<?= number_format($o['orc_subtotal'] ?? 0, 2) ?></td>
                                <td class="text-right"><small>$<?= number_format($o['orc_impuesto'] ?? 0, 2) ?></small></td>
                                <td class="text-right font-weight-bold">$<?= number_format($o['orc_total'] ?? 0, 2) ?></td>
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
