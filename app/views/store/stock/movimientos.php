<?php
/**
 * DigiSports Store - Movimientos de Stock
 */
$movimientos = $movimientos ?? [];
$productoId  = $productoId ?? 0;
$tipoFiltro  = $tipoFiltro ?? '';
$fechaDesde  = $fechaDesde ?? date('Y-m-01');
$fechaHasta  = $fechaHasta ?? date('Y-m-d');
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-exchange-alt mr-2" style="color:<?= $moduloColor ?>"></i>Movimientos de Stock</h1></div>
            <div class="col-sm-6"><div class="float-sm-right">
                <a href="<?= url('store', 'stock', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Inventario</a>
            </div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('store', 'stock', 'movimientos') ?>" class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small mb-1">Tipo</label>
                        <select name="tipo" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <?php foreach (['ENTRADA'=>'Entrada','SALIDA'=>'Salida','AJUSTE'=>'Ajuste','VENTA'=>'Venta','DEVOLUCION'=>'Devolución','COMPRA'=>'Compra'] as $tk=>$tv): ?>
                            <option value="<?= $tk ?>" <?= $tipoFiltro === $tk ? 'selected' : '' ?>><?= $tv ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2"><label class="small mb-1">Desde</label><input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= $fechaDesde ?>"></div>
                    <div class="col-md-2"><label class="small mb-1">Hasta</label><input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= $fechaHasta ?>"></div>
                    <div class="col-md-5">
                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-search mr-1"></i> Filtrar</button>
                        <a href="<?= url('store', 'stock', 'movimientos') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times mr-1"></i> Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card shadow-sm">
            <div class="card-header py-2"><h6 class="mb-0">Últimos <?= count($movimientos) ?> movimientos</h6></div>
            <div class="card-body p-0">
                <?php if (empty($movimientos)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-exchange-alt fa-3x mb-3 opacity-50"></i><p>Sin movimientos en el rango seleccionado</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-light">
                            <tr><th>Fecha</th><th>Producto</th><th class="text-center">Tipo</th><th class="text-center">Cantidad</th><th class="text-center">Stock Ant.</th><th class="text-center">Stock Post.</th><th>Referencia</th><th>Observación</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $m):
                                $tipoColors = ['ENTRADA'=>'success','COMPRA'=>'success','DEVOLUCION'=>'info','SALIDA'=>'danger','VENTA'=>'warning','AJUSTE'=>'secondary'];
                                $tipo = $m['mov_tipo'] ?? '';
                                $badgeColor = $tipoColors[$tipo] ?? 'light';
                                $signo = in_array($tipo, ['ENTRADA','COMPRA','DEVOLUCION']) ? '+' : '-';
                            ?>
                            <tr>
                                <td><small><?= date('d/m/Y H:i', strtotime($m['mov_fecha_registro'])) ?></small></td>
                                <td><?= htmlspecialchars($m['pro_nombre'] ?? '—') ?></td>
                                <td class="text-center"><span class="badge badge-<?= $badgeColor ?>"><?= $tipo ?></span></td>
                                <td class="text-center font-weight-bold text-<?= in_array($tipo, ['ENTRADA','COMPRA','DEVOLUCION']) ? 'success' : 'danger' ?>">
                                    <?= $signo ?><?= intval($m['mov_cantidad'] ?? 0) ?>
                                </td>
                                <td class="text-center"><small class="text-muted"><?= intval($m['mov_stock_anterior'] ?? 0) ?></small></td>
                                <td class="text-center"><small class="text-muted"><?= intval($m['mov_stock_posterior'] ?? 0) ?></small></td>
                                <td><small><code><?= htmlspecialchars($m['mov_referencia'] ?? '—') ?></code></small></td>
                                <td><small class="text-muted"><?= htmlspecialchars($m['mov_observacion'] ?? '') ?></small></td>
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
