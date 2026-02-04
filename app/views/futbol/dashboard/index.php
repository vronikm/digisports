<?php
/**
 * DigiSports Fútbol - Vista Dashboard
 */

$kpis = $kpis ?? [];
$chartReservas = $chart_reservas ?? ['labels' => [], 'data' => []];
$chartIngresos = $chart_ingresos ?? ['labels' => [], 'data' => []];
$proximasReservas = $proximas_reservas ?? [];
$canchasPopulares = $canchas_populares ?? [];
$torneosActivos = $torneos_activos ?? [];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $modulo_actual['icono'] ?? 'fas fa-futbol text-success' ?> mr-2" style="color: <?= $modulo_actual['color'] ?? '#22C55E' ?>"></i>
                    <?= $modulo_actual['nombre_personalizado'] ?? $modulo_actual['nombre'] ?? 'Dashboard Fútbol' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <div class="btn-group quick-actions">
                        <a href="<?= url('futbol', 'reserva', 'crear') ?>" class="btn btn-success">
                            <i class="fas fa-plus mr-1"></i> Nueva Reserva
                        </a>
                        <a href="<?= url('futbol', 'calendario', 'index') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-calendar-alt mr-1"></i> Calendario
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- KPI Cards -->
        <div class="row">
            <?php foreach ($kpis as $kpi): ?>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card kpi-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon" style="background: <?= $kpi['color'] ?>20; color: <?= $kpi['color'] ?>;">
                                <i class="<?= $kpi['icon'] ?>"></i>
                            </div>
                            <?php if ($kpi['trend']): ?>
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
        
        <!-- Charts Row -->
        <div class="row">
            <!-- Reservas Chart -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line text-success mr-2"></i>
                            Reservas - Últimos 7 días
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartReservas"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ingresos Chart -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-dollar-sign text-warning mr-2"></i>
                            Ingresos Semanal
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 200px;">
                            <canvas id="chartIngresos"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tables Row -->
        <div class="row">
            <!-- Próximas Reservas -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-check text-primary mr-2"></i>
                            Próximas Reservas
                        </h3>
                        <div class="card-tools">
                            <a href="<?= url('futbol', 'reserva', 'index') ?>" class="btn btn-sm btn-link">
                                Ver todas <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-module mb-0">
                                <thead>
                                    <tr>
                                        <th>Fecha/Hora</th>
                                        <th>Cancha</th>
                                        <th>Cliente</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($proximasReservas)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                            No hay reservas próximas
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($proximasReservas as $reserva): ?>
                                    <tr>
                                        <td>
                                            <strong><?= date('d/m', strtotime($reserva['fecha'])) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= substr($reserva['hora_inicio'], 0, 5) ?></small>
                                        </td>
                                        <td>
                                            <i class="fas fa-futbol text-success mr-1"></i>
                                            <?= htmlspecialchars($reserva['cancha_nombre'] ?? 'N/A') ?>
                                        </td>
                                        <td><?= htmlspecialchars($reserva['cliente_nombre'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php
                                            $estadoClass = [
                                                'CONFIRMADA' => 'success',
                                                'PENDIENTE' => 'warning',
                                                'CANCELADA' => 'danger'
                                            ][$reserva['estado']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?= $estadoClass ?>">
                                                <?= $reserva['estado'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Canchas Populares -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-fire text-danger mr-2"></i>
                            Canchas Más Populares
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-module mb-0">
                                <thead>
                                    <tr>
                                        <th>Cancha</th>
                                        <th>Tipo</th>
                                        <th class="text-center">Reservas</th>
                                        <th class="text-right">Ingresos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($canchasPopulares)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-futbol fa-2x mb-2 d-block"></i>
                                            No hay datos disponibles
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($canchasPopulares as $index => $cancha): ?>
                                    <tr>
                                        <td>
                                            <?php if ($index === 0): ?>
                                            <i class="fas fa-medal text-warning mr-1"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($cancha['nombre']) ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-light">
                                                <?= htmlspecialchars($cancha['tipo'] ?? 'Fútbol') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <strong><?= $cancha['total_reservas'] ?></strong>
                                        </td>
                                        <td class="text-right">
                                            <span class="text-success font-weight-bold">
                                                $<?= number_format($cancha['ingresos'], 2) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Torneos Activos -->
        <?php if (!empty($torneosActivos)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-trophy text-warning mr-2"></i>
                            Torneos Activos
                        </h3>
                        <div class="card-tools">
                            <a href="<?= url('futbol', 'torneo', 'index') ?>" class="btn btn-sm btn-link">
                                Ver todos <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($torneosActivos as $torneo): ?>
                            <div class="col-md-4">
                                <div class="card bg-gradient-success">
                                    <div class="card-body">
                                        <h5 class="card-title text-white">
                                            <i class="fas fa-trophy mr-2"></i>
                                            <?= htmlspecialchars($torneo['nombre'] ?? 'Torneo') ?>
                                        </h5>
                                        <p class="card-text text-white-50">
                                            <?= date('d/m/Y', strtotime($torneo['fecha_inicio'] ?? 'now')) ?> - 
                                            <?= date('d/m/Y', strtotime($torneo['fecha_fin'] ?? 'now')) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</section>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart de Reservas
    const ctxReservas = document.getElementById('chartReservas').getContext('2d');
    new Chart(ctxReservas, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartReservas['labels']) ?>,
            datasets: [{
                label: 'Reservas',
                data: <?= json_encode($chartReservas['data']) ?>,
                borderColor: '#22C55E',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#22C55E',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    // Chart de Ingresos
    const ctxIngresos = document.getElementById('chartIngresos').getContext('2d');
    new Chart(ctxIngresos, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartIngresos['labels']) ?>,
            datasets: [{
                label: 'Ingresos',
                data: <?= json_encode($chartIngresos['data']) ?>,
                backgroundColor: '#F59E0B',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
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
});
</script>
<?php $scripts = ob_get_clean(); ?>
