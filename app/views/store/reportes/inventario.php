<?php
/**
 * DigiSports Store - Reporte de Inventario
 */
$inventarioPorCategoria = $inventarioPorCategoria ?? [];
$masRotacion            = $masRotacion ?? [];
$sinMovimiento          = $sinMovimiento ?? [];
$alertasPendientes      = $alertasPendientes ?? 0;
$moduloColor            = $modulo_actual['color'] ?? '#F59E0B';

$totalUnidades  = array_sum(array_column($inventarioPorCategoria, 'unidades'));
$totalValorCost = array_sum(array_column($inventarioPorCategoria, 'valor_costo'));
$totalValorVent = array_sum(array_column($inventarioPorCategoria, 'valor_venta'));
$totalProductos = array_sum(array_column($inventarioPorCategoria, 'num_productos'));
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-warehouse mr-2" style="color:<?= $moduloColor ?>"></i>Reporte de Inventario</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><a href="<?= url('store', 'dashboard', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Dashboard</a></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- KPIs -->
        <div class="row mb-3">
            <div class="col-md-3"><div class="small-box bg-info"><div class="inner"><h3><?= $totalProductos ?></h3><p>Productos Activos</p></div><div class="icon"><i class="fas fa-box"></i></div></div></div>
            <div class="col-md-3"><div class="small-box bg-success"><div class="inner"><h3><?= number_format($totalUnidades) ?></h3><p>Unidades en Stock</p></div><div class="icon"><i class="fas fa-cubes"></i></div></div></div>
            <div class="col-md-3"><div class="small-box bg-primary"><div class="inner"><h3>$<?= number_format($totalValorCost, 0) ?></h3><p>Valor al Costo</p></div><div class="icon"><i class="fas fa-dollar-sign"></i></div></div></div>
            <div class="col-md-3"><div class="small-box bg-warning"><div class="inner"><h3><?= $alertasPendientes ?></h3><p>Alertas Pendientes</p></div><div class="icon"><i class="fas fa-bell"></i></div></div></div>
        </div>

        <div class="row">
            <!-- Valorización por Categoría -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-sitemap mr-1"></i> Valorización por Categoría</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="thead-light"><tr><th>Categoría</th><th class="text-center">Productos</th><th class="text-center">Unidades</th><th class="text-right">Valor Costo</th><th class="text-right">Valor Venta</th><th class="text-right">Margen</th></tr></thead>
                                <tbody>
                                    <?php foreach ($inventarioPorCategoria as $ic):
                                        $margen = ($ic['valor_venta'] > 0) ? round((($ic['valor_venta'] - $ic['valor_costo']) / $ic['valor_venta']) * 100, 1) : 0;
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($ic['categoria']) ?></strong></td>
                                        <td class="text-center"><?= intval($ic['num_productos']) ?></td>
                                        <td class="text-center"><?= number_format(intval($ic['unidades'])) ?></td>
                                        <td class="text-right">$<?= number_format($ic['valor_costo'], 2) ?></td>
                                        <td class="text-right">$<?= number_format($ic['valor_venta'], 2) ?></td>
                                        <td class="text-right"><span class="badge badge-<?= $margen >= 30 ? 'success' : ($margen >= 15 ? 'warning' : 'danger') ?>"><?= $margen ?>%</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="thead-light">
                                    <tr>
                                        <td><strong>TOTAL</strong></td>
                                        <td class="text-center"><strong><?= $totalProductos ?></strong></td>
                                        <td class="text-center"><strong><?= number_format($totalUnidades) ?></strong></td>
                                        <td class="text-right"><strong>$<?= number_format($totalValorCost, 2) ?></strong></td>
                                        <td class="text-right"><strong>$<?= number_format($totalValorVent, 2) ?></strong></td>
                                        <td class="text-right"><strong class="text-success">$<?= number_format($totalValorVent - $totalValorCost, 2) ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sin movimiento -->
                <div class="card shadow-sm">
                    <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-pause-circle text-warning mr-1"></i> Productos Sin Movimiento (30 días)</h6></div>
                    <div class="card-body p-0">
                        <?php if (empty($sinMovimiento)): ?>
                        <div class="text-center py-4 text-muted">Todos los productos tuvieron movimiento</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="thead-light"><tr><th>Producto</th><th>Código</th><th class="text-center">Stock</th><th>Último Movimiento</th></tr></thead>
                                <tbody>
                                    <?php foreach ($sinMovimiento as $sm): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($sm['pro_nombre']) ?></td>
                                        <td><code><?= htmlspecialchars($sm['pro_codigo'] ?? '') ?></code></td>
                                        <td class="text-center"><?= intval($sm['stock']) ?></td>
                                        <td><small class="text-muted"><?= $sm['ultimo_movimiento'] ? date('d/m/Y', strtotime($sm['ultimo_movimiento'])) : 'Nunca' ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Mayor rotación -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-fire text-danger mr-1"></i> Mayor Rotación (30 días)</h6></div>
                    <div class="card-body">
                        <?php if (empty($masRotacion)): ?>
                        <div class="text-center text-muted py-3">Sin datos</div>
                        <?php else:
                            $maxRot = max(array_column($masRotacion, 'movimientos'));
                            foreach ($masRotacion as $i => $mr):
                                $pct = $maxRot > 0 ? round(($mr['movimientos'] / $maxRot) * 100) : 0;
                        ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between"><small><?= ($i+1) . '. ' . htmlspecialchars($mr['pro_nombre']) ?></small><small class="font-weight-bold"><?= intval($mr['movimientos']) ?></small></div>
                            <div class="progress" style="height:6px"><div class="progress-bar bg-danger" style="width:<?= $pct ?>%"></div></div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header py-2" style="background:<?= $moduloColor ?>;color:white"><h6 class="mb-0"><i class="fas fa-chart-pie mr-1"></i> Distribución por Categoría</h6></div>
                    <div class="card-body"><canvas id="chartCategorias" height="250"></canvas></div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<script>
var ctxCat = document.getElementById('chartCategorias').getContext('2d');
var catData = <?= json_encode($inventarioPorCategoria) ?>;
var colores = ['#F59E0B','#3B82F6','#10B981','#EF4444','#8B5CF6','#F97316','#06B6D4','#EC4899','#6366F1','#14B8A6'];
new Chart(ctxCat, {
    type: 'doughnut',
    data: {
        labels: catData.map(function(c) { return c.categoria; }),
        datasets: [{ data: catData.map(function(c) { return parseFloat(c.valor_venta); }), backgroundColor: colores }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } } }
});
</script>
<?php $scripts = ob_get_clean(); ?>
