<?php
/**
 * DigiSports Store - Reporte de Caja
 */
$turnos            = $turnos ?? [];
$totalVentas       = $totalVentas ?? 0;
$totalDiferencia   = $totalDiferencia ?? 0;
$totalDevoluciones = $totalDevoluciones ?? 0;
$fechaDesde        = $fechaDesde ?? date('Y-m-01');
$fechaHasta        = $fechaHasta ?? date('Y-m-d');
$moduloColor       = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-cash-register mr-2" style="color:<?= $moduloColor ?>"></i>Reporte de Caja</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><a href="<?= url('store', 'dashboard', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Dashboard</a></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('store', 'reporte', 'caja') ?>" class="row align-items-end">
                    <div class="col-md-2"><label class="small mb-1">Desde</label><input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= $fechaDesde ?>"></div>
                    <div class="col-md-2"><label class="small mb-1">Hasta</label><input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= $fechaHasta ?>"></div>
                    <div class="col-md-3"><button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-filter mr-1"></i> Aplicar</button></div>
                </form>
            </div>
        </div>

        <!-- KPIs -->
        <div class="row mb-3">
            <div class="col-md-3"><div class="small-box bg-info"><div class="inner"><h3><?= count($turnos) ?></h3><p>Turnos Cerrados</p></div><div class="icon"><i class="fas fa-clock"></i></div></div></div>
            <div class="col-md-3"><div class="small-box bg-success"><div class="inner"><h3>$<?= number_format($totalVentas, 0) ?></h3><p>Total Ventas</p></div><div class="icon"><i class="fas fa-dollar-sign"></i></div></div></div>
            <div class="col-md-3"><div class="small-box bg-<?= $totalDiferencia >= 0 ? 'primary' : 'danger' ?>"><div class="inner"><h3>$<?= number_format(abs($totalDiferencia), 2) ?></h3><p>Diferencia Total <?= $totalDiferencia < 0 ? '(Faltante)' : '(Sobrante)' ?></p></div><div class="icon"><i class="fas fa-balance-scale"></i></div></div></div>
            <div class="col-md-3"><div class="small-box bg-warning"><div class="inner"><h3>$<?= number_format($totalDevoluciones, 0) ?></h3><p>Devoluciones</p></div><div class="icon"><i class="fas fa-undo"></i></div></div></div>
        </div>

        <!-- Tabla de turnos -->
        <div class="card shadow-sm">
            <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-list mr-1"></i> Turnos Cerrados</h6></div>
            <div class="card-body p-0">
                <?php if (empty($turnos)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-cash-register fa-3x mb-3 opacity-50"></i><p>No hay turnos cerrados en el rango seleccionado</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-light">
                            <tr><th>Caja</th><th>Apertura</th><th>Cierre</th><th class="text-right">M. Apertura</th>
                                <th class="text-right">Ventas</th><th class="text-right">Efectivo</th><th class="text-right">Tarjeta</th>
                                <th class="text-right">Transf.</th><th class="text-right">Entradas</th><th class="text-right">Salidas</th>
                                <th class="text-right">Devol.</th><th class="text-right">Esperado</th><th class="text-right">Contado</th>
                                <th class="text-right">Diferencia</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($turnos as $t):
                                $diff = (float)($t['tur_diferencia'] ?? 0);
                                $diffClass = $diff == 0 ? '' : ($diff > 0 ? 'text-success' : 'text-danger font-weight-bold');
                            ?>
                            <tr>
                                <td><small><?= htmlspecialchars($t['caj_nombre'] ?? '—') ?></small></td>
                                <td><small><?= date('d/m H:i', strtotime($t['tur_fecha_apertura'])) ?></small></td>
                                <td><small><?= $t['tur_fecha_cierre'] ? date('d/m H:i', strtotime($t['tur_fecha_cierre'])) : '—' ?></small></td>
                                <td class="text-right"><small>$<?= number_format($t['tur_monto_apertura'] ?? 0, 2) ?></small></td>
                                <td class="text-right">$<?= number_format($t['tur_total_ventas'] ?? 0, 2) ?></td>
                                <td class="text-right"><small>$<?= number_format($t['tur_total_efectivo'] ?? 0, 2) ?></small></td>
                                <td class="text-right"><small>$<?= number_format($t['tur_total_tarjeta'] ?? 0, 2) ?></small></td>
                                <td class="text-right"><small>$<?= number_format($t['tur_total_transferencia'] ?? 0, 2) ?></small></td>
                                <td class="text-right"><small class="text-success">$<?= number_format($t['tur_total_entradas'] ?? 0, 2) ?></small></td>
                                <td class="text-right"><small class="text-danger">$<?= number_format($t['tur_total_salidas'] ?? 0, 2) ?></small></td>
                                <td class="text-right"><small>$<?= number_format($t['tur_total_devoluciones'] ?? 0, 2) ?></small></td>
                                <td class="text-right"><small>$<?= number_format($t['tur_monto_cierre_esperado'] ?? 0, 2) ?></small></td>
                                <td class="text-right"><small>$<?= number_format($t['tur_monto_cierre_real'] ?? 0, 2) ?></small></td>
                                <td class="text-right <?= $diffClass ?>">$<?= number_format($diff, 2) ?></td>
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
