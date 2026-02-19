<?php
/**
 * DigiSports Store - Vista Dashboard
 * Panel principal con KPIs reales, ventas recientes, productos top y alertas
 */

$kpis             = $kpis ?? [];
$ultimas_ventas   = $ultimas_ventas ?? [];
$productos_top    = $productos_top ?? [];
$stock_bajo       = $stock_bajo ?? [];
$ventas_categoria = $ventas_categoria ?? [];
$chart_labels     = $chart_labels ?? '[]';
$chart_data       = $chart_data ?? '[]';
$moduloColor      = $modulo_actual['color'] ?? '#F59E0B';
$moduloIcono      = $modulo_actual['icono'] ?? 'fas fa-store';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?> mr-2" style="color: <?= $moduloColor ?>"></i>
                    <?= $modulo_actual['nombre_personalizado'] ?? $modulo_actual['nombre'] ?? 'Dashboard Store' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('store', 'pos', 'index') ?>" class="btn btn-sm" style="background: <?= $moduloColor ?>; color: white;">
                        <i class="fas fa-cash-register mr-1"></i> Punto de Venta
                    </a>
                    <a href="<?= url('store', 'producto', 'crear') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-plus mr-1"></i> Nuevo Producto
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- KPI Cards -->
        <div class="row">
            <?php foreach ($kpis as $kpi): ?>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:40px;height:40px;background:<?= $kpi['color'] ?>20;color:<?= $kpi['color'] ?>;">
                                <i class="<?= $kpi['icon'] ?>"></i>
                            </div>
                            <?php if (!empty($kpi['trend'])): ?>
                            <span class="ml-auto small font-weight-bold <?= $kpi['trend_type'] === 'up' ? 'text-success' : 'text-danger' ?>">
                                <i class="fas fa-arrow-<?= $kpi['trend_type'] ?> mr-1"></i><?= $kpi['trend'] ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="h3 mb-0 font-weight-bold"><?= $kpi['value'] ?></div>
                        <div class="text-muted small"><?= $kpi['label'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <!-- Columna Principal -->
            <div class="col-lg-8">

                <!-- Ventas Recientes -->
                <div class="card shadow-sm">
                    <div class="card-header border-0 bg-white">
                        <h3 class="card-title">
                            <i class="fas fa-receipt mr-2" style="color:<?= $moduloColor ?>"></i>Ventas Recientes
                        </h3>
                        <div class="card-tools">
                            <a href="<?= url('store', 'venta', 'index') ?>" class="btn btn-sm btn-outline-secondary">
                                Ver todas <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($ultimas_ventas)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fa-3x mb-3 opacity-50"></i>
                            <p>No hay ventas registradas aún</p>
                            <a href="<?= url('store', 'pos', 'index') ?>" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;">
                                <i class="fas fa-cash-register mr-1"></i> Ir al Punto de Venta
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#Venta</th>
                                        <th>Cliente</th>
                                        <th>Productos</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-center">Estado</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimas_ventas as $v): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= url('store', 'venta', 'ver', ['id' => $v['ven_venta_id']]) ?>" class="font-weight-bold" style="color:<?= $moduloColor ?>">
                                                <?= htmlspecialchars($v['ven_numero'] ?? '-') ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if (!empty($v['cli_nombres'])): ?>
                                                <?= htmlspecialchars($v['cli_nombres'] . ' ' . $v['cli_apellidos']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Consumidor Final</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $nombres = $v['productos_nombres'] ?? '';
                                            $items = $v['total_items'] ?? 0;
                                            if ($nombres) {
                                                $arr = explode(', ', $nombres);
                                                echo '<span class="badge badge-light">' . htmlspecialchars($arr[0]) . '</span>';
                                                if ($items > 1) echo ' <span class="badge badge-secondary">+' . ($items - 1) . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-right">
                                            <strong class="text-success">$<?= number_format($v['ven_total'] ?? 0, 2) ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $coloresEst = ['COMPLETADA'=>'success','PENDIENTE'=>'warning','ANULADA'=>'danger','BORRADOR'=>'secondary'];
                                            $c = $coloresEst[$v['ven_estado']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?= $c ?>"><?= $v['ven_estado'] ?></span>
                                        </td>
                                        <td><small class="text-muted"><?= date('d/m H:i', strtotime($v['ven_fecha'])) ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Gráfico de Ventas -->
                <div class="card shadow-sm">
                    <div class="card-header border-0 bg-white">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2" style="color:<?= $moduloColor ?>"></i>Ventas de la Semana
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Columna Lateral -->
            <div class="col-lg-4">

                <!-- Top Productos -->
                <div class="card shadow-sm">
                    <div class="card-header border-0 bg-white">
                        <h3 class="card-title"><i class="fas fa-fire text-danger mr-2"></i>Más Vendidos</h3>
                        <div class="card-tools"><span class="badge badge-light">30 días</span></div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($productos_top)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-box-open fa-2x mb-2 opacity-50"></i>
                            <p class="small">Sin datos de ventas aún</p>
                        </div>
                        <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($productos_top as $i => $pt):
                                $bc = $i === 0 ? 'badge-danger' : ($i === 1 ? 'badge-warning' : ($i === 2 ? 'badge-info' : 'badge-light'));
                            ?>
                            <li class="list-group-item d-flex align-items-center">
                                <span class="badge <?= $bc ?> mr-3"><?= $i + 1 ?></span>
                                <div class="flex-grow-1">
                                    <strong><?= htmlspecialchars($pt['pro_nombre']) ?></strong>
                                    <br><small class="text-muted"><?= intval($pt['total_vendido']) ?> vendidos</small>
                                </div>
                                <div class="text-right">
                                    <span class="text-success font-weight-bold">$<?= number_format($pt['pro_precio_venta'] ?? 0, 2) ?></span>
                                    <br><small class="text-muted">Stock: <?= intval($pt['stock_actual']) ?></small>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Alertas Stock Bajo -->
                <div class="card shadow-sm card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title text-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Stock Bajo
                        </h3>
                        <?php if (!empty($stock_bajo)): ?>
                        <div class="card-tools"><span class="badge badge-danger"><?= count($stock_bajo) ?></span></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($stock_bajo)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                            <p class="small">Inventario en buen estado</p>
                        </div>
                        <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($stock_bajo as $sb):
                                $bc2 = intval($sb['stk_disponible']) == 0 ? 'badge-danger' : 'badge-warning';
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="small"><?= htmlspecialchars($sb['pro_nombre']) ?></span>
                                <span class="badge <?= $bc2 ?>"><?= intval($sb['stk_disponible']) ?> / <?= intval($sb['stk_minimo']) ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center py-2">
                        <a href="<?= url('store', 'stock', 'alertas') ?>" class="text-danger small">
                            Ver alertas <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <!-- Ventas por Categoría -->
                <div class="card shadow-sm">
                    <div class="card-header border-0 bg-white">
                        <h3 class="card-title"><i class="fas fa-tags mr-2" style="color:<?= $moduloColor ?>"></i>Por Categoría</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($ventas_categoria)): ?>
                        <div class="text-center py-3 text-muted"><p class="small">Sin datos</p></div>
                        <?php else:
                            $totalVC = array_sum(array_column($ventas_categoria, 'total_ventas'));
                            $cols = ['#F59E0B','#3B82F6','#22C55E','#EF4444','#8B5CF6','#0EA5E9'];
                            foreach ($ventas_categoria as $i => $vc):
                                $pct = $totalVC > 0 ? round(($vc['total_ventas'] / $totalVC) * 100) : 0;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small"><?= htmlspecialchars($vc['cat_nombre']) ?></span>
                                <span class="small font-weight-bold"><?= $pct ?>%</span>
                            </div>
                            <div class="progress" style="height:8px;">
                                <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $cols[$i % 6] ?>;"></div>
                            </div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Accesos Rápidos -->
                <div class="card shadow-sm">
                    <div class="card-header border-0 bg-white">
                        <h3 class="card-title"><i class="fas fa-bolt mr-2" style="color:<?= $moduloColor ?>"></i>Accesos Rápidos</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-2">
                                <a href="<?= url('store', 'caja', 'index') ?>" class="btn btn-outline-secondary btn-block btn-sm">
                                    <i class="fas fa-cash-register d-block mb-1"></i>Caja
                                </a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="<?= url('store', 'producto', 'index') ?>" class="btn btn-outline-secondary btn-block btn-sm">
                                    <i class="fas fa-box d-block mb-1"></i>Productos
                                </a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="<?= url('store', 'cliente', 'index') ?>" class="btn btn-outline-secondary btn-block btn-sm">
                                    <i class="fas fa-users d-block mb-1"></i>Clientes
                                </a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="<?= url('store', 'reporte', 'ventas') ?>" class="btn btn-outline-secondary btn-block btn-sm">
                                    <i class="fas fa-chart-line d-block mb-1"></i>Reportes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('salesChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $chart_labels ?>,
                datasets: [{
                    label: 'Ventas ($)',
                    data: <?= $chart_data ?>,
                    backgroundColor: '<?= $moduloColor ?>40',
                    borderColor: '<?= $moduloColor ?>',
                    borderWidth: 2,
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(c) { return '$ ' + c.parsed.y.toFixed(2); }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: function(v) { return '$' + v; } },
                        grid: { color: '#f0f0f0' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
<?php $scripts = ob_get_clean(); ?>
