<?php
/**
 * DigiSports Store - Reporte de Clientes
 */
$topClientes  = $topClientes ?? [];
$distribucion = $distribucion ?? [];
$nuevos30d    = $nuevos30d ?? 0;
$moduloColor  = $modulo_actual['color'] ?? '#F59E0B';

$totalClientes = array_sum(array_column($distribucion, 'total'));
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-users mr-2" style="color:<?= $moduloColor ?>"></i>Reporte de Clientes</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><a href="<?= url('store', 'dashboard', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Dashboard</a></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- KPIs -->
        <div class="row mb-3">
            <div class="col-md-4"><div class="small-box bg-info"><div class="inner"><h3><?= $totalClientes ?></h3><p>Clientes Activos</p></div><div class="icon"><i class="fas fa-users"></i></div></div></div>
            <div class="col-md-4"><div class="small-box bg-success"><div class="inner"><h3><?= $nuevos30d ?></h3><p>Nuevos (30 días)</p></div><div class="icon"><i class="fas fa-user-plus"></i></div></div></div>
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <?php
                        $totalVIP = 0;
                        foreach ($distribucion as $d) { if (($d['categoria'] ?? $d['scl_categoria'] ?? '') === 'VIP') $totalVIP = $d['total']; }
                        ?>
                        <h3><?= $totalVIP ?></h3><p>Clientes VIP</p>
                    </div>
                    <div class="icon"><i class="fas fa-crown"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Clientes -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-trophy mr-1"></i> Top Clientes por Compras</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="thead-light"><tr><th>#</th><th>Cliente</th><th>Identificación</th><th class="text-center">Categoría</th><th class="text-center">Compras</th><th class="text-right">Total</th><th class="text-center">Puntos</th><th>Última Compra</th></tr></thead>
                                <tbody>
                                    <?php foreach ($topClientes as $i => $tc):
                                        $catC = ['VIP'=>'warning','FRECUENTE'=>'info','REGULAR'=>'success','NUEVO'=>'secondary'];
                                        $cat = $tc['scl_categoria'] ?? 'NUEVO';
                                    ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><strong><?= htmlspecialchars(($tc['cli_nombres'] ?? '') . ' ' . ($tc['cli_apellidos'] ?? '')) ?></strong></td>
                                        <td><code><?= htmlspecialchars($tc['cli_identificacion'] ?? '—') ?></code></td>
                                        <td class="text-center"><span class="badge badge-<?= $catC[$cat] ?? 'secondary' ?>"><?= $cat ?></span></td>
                                        <td class="text-center"><?= intval($tc['scl_num_compras'] ?? 0) ?></td>
                                        <td class="text-right font-weight-bold">$<?= number_format($tc['scl_total_compras'] ?? 0, 2) ?></td>
                                        <td class="text-center"><i class="fas fa-star text-warning mr-1"></i><?= intval($tc['scl_puntos_disponibles'] ?? 0) ?></td>
                                        <td><small class="text-muted"><?= !empty($tc['scl_ultima_compra']) ? date('d/m/Y', strtotime($tc['scl_ultima_compra'])) : '—' ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($topClientes)): ?><tr><td colspan="8" class="text-center text-muted py-3">Sin datos</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribución -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-chart-pie mr-1"></i> Distribución por Categoría</h6></div>
                    <div class="card-body"><canvas id="chartDistribucion" height="250"></canvas></div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-list mr-1"></i> Detalle</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <?php
                            $catIcons = ['VIP'=>'fa-crown text-warning','FRECUENTE'=>'fa-star text-info','REGULAR'=>'fa-user text-success','NUEVO'=>'fa-user-plus text-secondary'];
                            foreach ($distribucion as $d):
                                $pct = $totalClientes > 0 ? round(($d['total'] / $totalClientes) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td><i class="fas <?= $catIcons[$d['categoria'] ?? ''] ?? 'fa-user' ?> mr-1"></i> <?= $d['categoria'] ?? 'NUEVO' ?></td>
                                <td class="text-center"><strong><?= $d['total'] ?></strong></td>
                                <td class="text-right"><small class="text-muted"><?= $pct ?>%</small></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<script>
var ctxDist = document.getElementById('chartDistribucion').getContext('2d');
var distData = <?= json_encode($distribucion) ?>;
var catColores = { 'VIP': '#F59E0B', 'FRECUENTE': '#3B82F6', 'REGULAR': '#10B981', 'NUEVO': '#6B7280' };
new Chart(ctxDist, {
    type: 'doughnut',
    data: {
        labels: distData.map(function(d) { return d.categoria || 'NUEVO'; }),
        datasets: [{ data: distData.map(function(d) { return parseInt(d.total); }), backgroundColor: distData.map(function(d) { return catColores[d.categoria] || '#ccc'; }) }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});
</script>
<?php $scripts = ob_get_clean(); ?>
