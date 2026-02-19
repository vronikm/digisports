<?php
/**
 * DigiSports Store - Reporte de Ventas
 */
$resumen            = $resumen ?? [];
$ventasPorPeriodo   = $ventasPorPeriodo ?? [];
$topProductos       = $topProductos ?? [];
$ventasPorPago      = $ventasPorPago ?? [];
$ventasPorCategoria = $ventasPorCategoria ?? [];
$fechaDesde         = $fechaDesde ?? date('Y-m-01');
$fechaHasta         = $fechaHasta ?? date('Y-m-d');
$agrupacion         = $agrupacion ?? 'dia';
$moduloColor        = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-chart-line mr-2" style="color:<?= $moduloColor ?>"></i>Reporte de Ventas</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><a href="<?= url('store', 'dashboard', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Dashboard</a></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('store', 'reporte', 'ventas') ?>" class="row align-items-end">
                    <div class="col-md-2"><label class="small mb-1">Desde</label><input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= $fechaDesde ?>"></div>
                    <div class="col-md-2"><label class="small mb-1">Hasta</label><input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= $fechaHasta ?>"></div>
                    <div class="col-md-2"><label class="small mb-1">Agrupación</label>
                        <select name="agrupacion" class="form-control form-control-sm">
                            <option value="dia" <?= $agrupacion === 'dia' ? 'selected' : '' ?>>Diario</option>
                            <option value="semana" <?= $agrupacion === 'semana' ? 'selected' : '' ?>>Semanal</option>
                            <option value="mes" <?= $agrupacion === 'mes' ? 'selected' : '' ?>>Mensual</option>
                        </select>
                    </div>
                    <div class="col-md-3"><button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-filter mr-1"></i> Aplicar</button></div>
                </form>
            </div>
        </div>

        <!-- KPIs -->
        <div class="row mb-3">
            <div class="col-md-2">
                <div class="small-box bg-success"><div class="inner"><h3>$<?= number_format($resumen['total_vendido'] ?? 0, 0) ?></h3><p>Total Vendido</p></div><div class="icon"><i class="fas fa-dollar-sign"></i></div></div>
            </div>
            <div class="col-md-2">
                <div class="small-box bg-info"><div class="inner"><h3><?= intval($resumen['total_ventas'] ?? 0) ?></h3><p>Transacciones</p></div><div class="icon"><i class="fas fa-receipt"></i></div></div>
            </div>
            <div class="col-md-2">
                <div class="small-box bg-primary"><div class="inner"><h3>$<?= number_format($resumen['ticket_promedio'] ?? 0, 2) ?></h3><p>Ticket Promedio</p></div><div class="icon"><i class="fas fa-ticket-alt"></i></div></div>
            </div>
            <div class="col-md-2">
                <div class="small-box bg-warning"><div class="inner"><h3>$<?= number_format($resumen['total_iva'] ?? 0, 0) ?></h3><p>IVA Cobrado</p></div><div class="icon"><i class="fas fa-percentage"></i></div></div>
            </div>
            <div class="col-md-2">
                <div class="small-box bg-secondary"><div class="inner"><h3>$<?= number_format($resumen['total_descuentos'] ?? 0, 0) ?></h3><p>Descuentos</p></div><div class="icon"><i class="fas fa-tags"></i></div></div>
            </div>
            <div class="col-md-2">
                <div class="small-box bg-danger"><div class="inner"><h3><?= intval($resumen['ventas_anuladas'] ?? 0) ?></h3><p>Anuladas</p></div><div class="icon"><i class="fas fa-ban"></i></div></div>
            </div>
        </div>

        <div class="row">
            <!-- Gráfico de Ventas -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-chart-bar mr-1"></i> Ventas por Período</h6></div>
                    <div class="card-body"><canvas id="chartVentas" height="280"></canvas></div>
                </div>
            </div>
            <!-- Formas de Pago -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-chart-pie mr-1"></i> Formas de Pago</h6></div>
                    <div class="card-body"><canvas id="chartPagos" height="280"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top productos -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-trophy mr-1"></i> Top Productos Vendidos</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light"><tr><th>#</th><th>Producto</th><th class="text-center">Uds.</th><th class="text-right">Total</th></tr></thead>
                                <tbody>
                                    <?php foreach ($topProductos as $i => $tp): ?>
                                    <tr><td><?= $i + 1 ?></td><td><?= htmlspecialchars($tp['producto']) ?></td><td class="text-center"><?= intval($tp['cantidad']) ?></td><td class="text-right">$<?= number_format($tp['total'], 2) ?></td></tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($topProductos)): ?><tr><td colspan="4" class="text-center text-muted py-3">Sin datos</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Ventas por categoría -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-sitemap mr-1"></i> Ventas por Categoría</h6></div>
                    <div class="card-body">
                        <?php
                        $maxCat = !empty($ventasPorCategoria) ? max(array_column($ventasPorCategoria, 'total')) : 1;
                        foreach ($ventasPorCategoria as $vc):
                            $pct = $maxCat > 0 ? round(($vc['total'] / $maxCat) * 100) : 0;
                        ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between"><small><?= htmlspecialchars($vc['categoria']) ?></small><small class="font-weight-bold">$<?= number_format($vc['total'], 2) ?></small></div>
                            <div class="progress" style="height:8px"><div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $moduloColor ?>"></div></div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($ventasPorCategoria)): ?><div class="text-center text-muted py-3">Sin datos</div><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<script>
// Gráfico de ventas
var ctxV = document.getElementById('chartVentas').getContext('2d');
new Chart(ctxV, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($ventasPorPeriodo, 'periodo')) ?>,
        datasets: [{
            label: 'Ventas ($)',
            data: <?= json_encode(array_map(function($v) { return (float)$v['total']; }, $ventasPorPeriodo)) ?>,
            backgroundColor: '<?= $moduloColor ?>88',
            borderColor: '<?= $moduloColor ?>',
            borderWidth: 1
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
});

// Gráfico de pagos
var ctxP = document.getElementById('chartPagos').getContext('2d');
var pagoColors = { 'EFECTIVO': '#28a745', 'TARJETA': '#007bff', 'TRANSFERENCIA': '#17a2b8', 'MIXTO': '#ffc107' };
var pagoData = <?= json_encode($ventasPorPago) ?>;
new Chart(ctxP, {
    type: 'doughnut',
    data: {
        labels: pagoData.map(function(p) { return p.forma; }),
        datasets: [{ data: pagoData.map(function(p) { return parseFloat(p.total); }), backgroundColor: pagoData.map(function(p) { return pagoColors[p.forma] || '#6c757d'; }) }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});
</script>
<?php $scripts = ob_get_clean(); ?>
