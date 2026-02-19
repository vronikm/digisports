<?php
/**
 * DigiSports Store - Reporte de Utilidad / Márgenes
 */
$utilidades    = $utilidades ?? [];
$totalIngreso  = $totalIngreso ?? 0;
$totalCosto    = $totalCosto ?? 0;
$totalUtilidad = $totalUtilidad ?? 0;
$fechaDesde    = $fechaDesde ?? date('Y-m-01');
$fechaHasta    = $fechaHasta ?? date('Y-m-d');
$moduloColor   = $modulo_actual['color'] ?? '#F59E0B';
$margenGlobal  = $totalIngreso > 0 ? round(($totalUtilidad / $totalIngreso) * 100, 1) : 0;
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-chart-pie mr-2" style="color:<?= $moduloColor ?>"></i>Reporte de Utilidad</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><a href="<?= url('store', 'dashboard', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Dashboard</a></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('store', 'reporte', 'utilidad') ?>" class="row align-items-end">
                    <div class="col-md-2"><label class="small mb-1">Desde</label><input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= $fechaDesde ?>"></div>
                    <div class="col-md-2"><label class="small mb-1">Hasta</label><input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= $fechaHasta ?>"></div>
                    <div class="col-md-3"><button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-filter mr-1"></i> Aplicar</button></div>
                </form>
            </div>
        </div>

        <!-- KPIs -->
        <div class="row mb-3">
            <div class="col-md-3"><div class="small-box bg-info"><div class="inner"><h3>$<?= number_format($totalIngreso, 0) ?></h3><p>Ingreso Bruto</p></div><div class="icon"><i class="fas fa-arrow-up"></i></div></div></div>
            <div class="col-md-3"><div class="small-box bg-danger"><div class="inner"><h3>$<?= number_format($totalCosto, 0) ?></h3><p>Costo Total</p></div><div class="icon"><i class="fas fa-arrow-down"></i></div></div></div>
            <div class="col-md-3"><div class="small-box bg-success"><div class="inner"><h3>$<?= number_format($totalUtilidad, 0) ?></h3><p>Utilidad Bruta</p></div><div class="icon"><i class="fas fa-dollar-sign"></i></div></div></div>
            <div class="col-md-3"><div class="small-box bg-<?= $margenGlobal >= 30 ? 'success' : ($margenGlobal >= 15 ? 'warning' : 'danger') ?>"><div class="inner"><h3><?= $margenGlobal ?>%</h3><p>Margen Global</p></div><div class="icon"><i class="fas fa-percentage"></i></div></div></div>
        </div>

        <!-- Tabla por producto -->
        <div class="card shadow-sm">
            <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-list mr-1"></i> Utilidad por Producto</h6></div>
            <div class="card-body p-0">
                <?php if (empty($utilidades)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-chart-pie fa-3x mb-3 opacity-50"></i><p>Sin ventas en el período</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-light">
                            <tr><th>#</th><th>Producto</th><th class="text-center">Uds.</th><th class="text-right">Ingreso</th><th class="text-right">Costo</th><th class="text-right">Utilidad</th><th class="text-center">Margen</th><th width="150">Barra</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($utilidades as $i => $u):
                                $margen = (float)($u['margen_pct'] ?? 0);
                                $utilidad = (float)($u['utilidad'] ?? 0);
                                $margenClass = $margen >= 30 ? 'success' : ($margen >= 15 ? 'warning' : 'danger');
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($u['producto']) ?></td>
                                <td class="text-center"><?= intval($u['unidades']) ?></td>
                                <td class="text-right">$<?= number_format($u['ingreso_bruto'] ?? 0, 2) ?></td>
                                <td class="text-right">$<?= number_format($u['costo_total'] ?? 0, 2) ?></td>
                                <td class="text-right font-weight-bold <?= $utilidad >= 0 ? 'text-success' : 'text-danger' ?>">$<?= number_format($utilidad, 2) ?></td>
                                <td class="text-center"><span class="badge badge-<?= $margenClass ?>"><?= $margen ?>%</span></td>
                                <td>
                                    <div class="progress" style="height:10px">
                                        <div class="progress-bar bg-<?= $margenClass ?>" style="width:<?= min(max($margen, 0), 100) ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="thead-light">
                            <tr>
                                <td colspan="3"><strong>TOTALES</strong></td>
                                <td class="text-right"><strong>$<?= number_format($totalIngreso, 2) ?></strong></td>
                                <td class="text-right"><strong>$<?= number_format($totalCosto, 2) ?></strong></td>
                                <td class="text-right font-weight-bold text-success">$<?= number_format($totalUtilidad, 2) ?></td>
                                <td class="text-center"><span class="badge badge-<?= $margenGlobal >= 30 ? 'success' : ($margenGlobal >= 15 ? 'warning' : 'danger') ?>"><?= $margenGlobal ?>%</span></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
