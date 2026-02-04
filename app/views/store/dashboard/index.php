<?php
/**
 * DigiSports Store - Vista Dashboard
 */

$kpis = $kpis ?? [];
$topProducts = $topProducts ?? [];
$lowStock = $lowStock ?? [];
$moduloColor = $modulo_actual['color'] ?? '#E11D48';
$moduloIcono = $modulo_actual['icono'] ?? 'fas fa-store';
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
                <div class="float-sm-right quick-actions">
                    <a href="<?= url('store', 'pos', 'index') ?>" class="btn" style="background: <?= $moduloColor ?>; color: white;">
                        <i class="fas fa-cash-register mr-1"></i> Punto de Venta
                    </a>
                    <a href="<?= url('store', 'producto', 'crear') ?>" class="btn btn-outline-secondary">
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
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon" style="background: <?= $kpi['color'] ?>20; color: <?= $kpi['color'] ?>;">
                                <i class="<?= $kpi['icon'] ?>"></i>
                            </div>
                            <?php if (!empty($kpi['trend'])): ?>
                            <span class="kpi-trend <?= $kpi['trend_type'] ?> ml-auto">
                                <i class="fas fa-arrow-<?= $kpi['trend_type'] ?>"></i>
                                <?= $kpi['trend'] ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="kpi-value"><?= $kpi['value'] ?></div>
                        <div class="kpi-label"><?= $kpi['label'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="row">
            <!-- Ventas recientes -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-receipt mr-2" style="color: <?= $moduloColor ?>"></i>
                            Ventas Recientes
                        </h3>
                        <div class="card-tools">
                            <a href="<?= url('store', 'venta', 'index') ?>" class="btn btn-sm btn-outline-secondary">
                                Ver todas <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#Venta</th>
                                    <th>Cliente</th>
                                    <th>Productos</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>V-2026-0145</code></td>
                                    <td>Carlos Mendoza</td>
                                    <td>
                                        <span class="badge badge-light">Balón Nike</span>
                                        <span class="badge badge-light">+2</span>
                                    </td>
                                    <td><strong class="text-success">$125.00</strong></td>
                                    <td><span class="badge badge-success">Completada</span></td>
                                    <td>10:45</td>
                                </tr>
                                <tr>
                                    <td><code>V-2026-0144</code></td>
                                    <td>María García</td>
                                    <td>
                                        <span class="badge badge-light">Camiseta Adidas</span>
                                    </td>
                                    <td><strong class="text-success">$45.00</strong></td>
                                    <td><span class="badge badge-success">Completada</span></td>
                                    <td>10:22</td>
                                </tr>
                                <tr>
                                    <td><code>V-2026-0143</code></td>
                                    <td>Juan Pérez</td>
                                    <td>
                                        <span class="badge badge-light">Zapatillas Running</span>
                                    </td>
                                    <td><strong class="text-success">$189.00</strong></td>
                                    <td><span class="badge badge-warning">Pendiente entrega</span></td>
                                    <td>09:58</td>
                                </tr>
                                <tr>
                                    <td><code>V-2026-0142</code></td>
                                    <td>Ana López</td>
                                    <td>
                                        <span class="badge badge-light">Raqueta Tenis</span>
                                        <span class="badge badge-light">+1</span>
                                    </td>
                                    <td><strong class="text-success">$220.00</strong></td>
                                    <td><span class="badge badge-success">Completada</span></td>
                                    <td>09:30</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Gráfico de ventas -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-2" style="color: <?= $moduloColor ?>"></i>
                            Ventas de la Semana
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Top productos -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-fire text-danger mr-2"></i>
                            Más Vendidos
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex align-items-center">
                                <span class="badge badge-danger mr-3">1</span>
                                <div class="flex-grow-1">
                                    <strong>Balón Nike Premier</strong>
                                    <br><small class="text-muted">45 vendidos</small>
                                </div>
                                <span class="text-success">$35.00</span>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <span class="badge badge-secondary mr-3">2</span>
                                <div class="flex-grow-1">
                                    <strong>Camiseta Adidas Sport</strong>
                                    <br><small class="text-muted">38 vendidos</small>
                                </div>
                                <span class="text-success">$45.00</span>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <span class="badge" style="background:#cd7f32;color:white" class="mr-3">3</span>
                                <div class="flex-grow-1">
                                    <strong>Gorra Deportiva</strong>
                                    <br><small class="text-muted">32 vendidos</small>
                                </div>
                                <span class="text-success">$18.00</span>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <span class="badge badge-light mr-3">4</span>
                                <div class="flex-grow-1">
                                    <strong>Botella Térmica</strong>
                                    <br><small class="text-muted">28 vendidos</small>
                                </div>
                                <span class="text-success">$25.00</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Alertas de stock -->
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title text-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Stock Bajo
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Zapatillas Running Nike</span>
                                <span class="badge badge-danger">3 uds</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Raqueta Tenis Pro</span>
                                <span class="badge badge-danger">2 uds</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Guantes Portero</span>
                                <span class="badge badge-warning">5 uds</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Canilleras Adidas</span>
                                <span class="badge badge-warning">6 uds</span>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer text-center">
                        <a href="<?= url('store', 'stock', 'alertas') ?>" class="text-danger">
                            Ver todas las alertas <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Categorías -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-tags mr-2" style="color: <?= $moduloColor ?>"></i>
                            Ventas por Categoría
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Calzado</span>
                                <span>35%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" style="width: 35%; background: <?= $moduloColor ?>;"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Ropa</span>
                                <span>28%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-info" style="width: 28%;"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Equipamiento</span>
                                <span>22%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: 22%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Accesorios</span>
                                <span>15%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: 15%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de ventas semanal
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Ventas ($)',
                    data: [1250, 980, 1450, 1100, 1680, 2100, 890],
                    backgroundColor: '<?= $moduloColor ?>80',
                    borderColor: '<?= $moduloColor ?>',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
