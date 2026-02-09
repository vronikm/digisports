<?php
/**
 * DigiSports Arena - Vista de Reportes
 * KPIs, gráficos de ingresos, ocupación de canchas, top clientes, monedero
 */
$moduloColor = $modulo_actual['color'] ?? '#8B5CF6';
$resumen = $resumen ?? [];
$chartDiario = $chart_diario ?? ['labels' => [], 'pagos' => [], 'entradas' => []];
$topClientes = $top_clientes ?? [];
$ocupacion = $ocupacion ?? [];
$movMonedero = $mov_monedero ?? ['recargas' => 0, 'consumos' => 0, 'saldo_actual' => 0, 'cuentas_activas' => 0];
$periodo = $periodo ?? 'mes';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-chart-line mr-2" style="color: <?= $moduloColor ?>"></i>
                    Reportes Arena
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <!-- Selector de período -->
                    <div class="btn-group">
                        <?php
                        $periodos = [
                            'semana' => 'Semana',
                            'mes' => 'Mes',
                            'trimestre' => 'Trimestre',
                            'anio' => 'Año',
                        ];
                        foreach ($periodos as $key => $label): ?>
                        <a href="<?= url('instalaciones', 'reporteArena', 'index', ['periodo' => $key]) ?>"
                           class="btn btn-sm <?= $periodo === $key ? 'btn-primary' : 'btn-outline-secondary' ?>">
                            <?= $label ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <small class="text-muted">
                    <i class="fas fa-calendar mr-1"></i>
                    <?= date('d/m/Y', strtotime($fecha_desde)) ?> — <?= date('d/m/Y', strtotime($fecha_hasta)) ?>
                </small>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- KPI Cards -->
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="small-box bg-gradient-success">
                    <div class="inner">
                        <h3>$<?= number_format($resumen['total_general'] ?? 0, 2) ?></h3>
                        <p>Ingresos Totales</p>
                    </div>
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="small-box-footer">
                        <?= (int)($resumen['num_pagos'] ?? 0) ?> pagos + <?= (int)($resumen['num_entradas'] ?? 0) ?> entradas
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="small-box bg-gradient-info">
                    <div class="inner">
                        <h3><?= (int)($resumen['num_reservas'] ?? 0) ?></h3>
                        <p>Reservas en Período</p>
                    </div>
                    <div class="icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="small-box-footer">
                        Pagos: $<?= number_format($resumen['total_pagos_reservas'] ?? 0, 2) ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="small-box bg-gradient-warning">
                    <div class="inner">
                        <h3>$<?= number_format($resumen['pendiente_cobro'] ?? 0, 2) ?></h3>
                        <p>Pendiente de Cobro</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="small-box-footer">
                        <a href="<?= url('reservas', 'reserva', 'index', ['estado_pago' => 'PENDIENTE']) ?>" class="text-white">
                            Ver pendientes <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="small-box bg-gradient-purple">
                    <div class="inner">
                        <h3>$<?= number_format($movMonedero['saldo_actual'], 2) ?></h3>
                        <p>Saldo Monedero</p>
                    </div>
                    <div class="icon"><i class="fas fa-wallet"></i></div>
                    <div class="small-box-footer">
                        <?= (int)$movMonedero['cuentas_activas'] ?> cuentas activas
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Ingresos Diarios -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-chart-area mr-2" style="color: <?= $moduloColor ?>"></i>
                            Ingresos Diarios
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:300px;">
                            <canvas id="chartIngresosDiarios"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monedero resumen -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-wallet mr-2 text-warning"></i>
                            Monedero / Abonos
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Recargas en período</span>
                            <strong class="text-success">+ $<?= number_format($movMonedero['recargas'], 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Consumos en período</span>
                            <strong class="text-danger">- $<?= number_format($movMonedero['consumos'], 2) ?></strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Saldo total actual</span>
                            <strong class="text-primary" style="font-size:1.3em">$<?= number_format($movMonedero['saldo_actual'], 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Cuentas activas</span>
                            <span class="badge badge-info"><?= (int)$movMonedero['cuentas_activas'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- Desglose ingresos -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-layer-group mr-2" style="color: <?= $moduloColor ?>"></i>
                            Desglose
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="fas fa-calendar-check text-primary mr-2"></i>Reservas</span>
                                <strong>$<?= number_format($resumen['total_pagos_reservas'] ?? 0, 2) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="fas fa-ticket-alt text-pink mr-2"></i>Entradas</span>
                                <strong>$<?= number_format($resumen['total_entradas'] ?? 0, 2) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="fas fa-wallet text-warning mr-2"></i>Recargas</span>
                                <strong>$<?= number_format($resumen['total_recargas'] ?? 0, 2) ?></strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Clientes y Ocupación -->
        <div class="row">
            <!-- Top 10 Clientes -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-trophy mr-2 text-warning"></i>
                            Top 10 Clientes
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($topClientes)): ?>
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th class="text-center">Reservas</th>
                                    <th class="text-right">Pagado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topClientes as $idx => $tc): ?>
                                <tr>
                                    <td>
                                        <?php if ($idx < 3): ?>
                                        <span class="badge badge-warning"><i class="fas fa-medal"></i> <?= $idx + 1 ?></span>
                                        <?php else: ?>
                                        <span class="text-muted"><?= $idx + 1 ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($tc['nombre']) ?></td>
                                    <td class="text-center"><span class="badge badge-info"><?= (int)$tc['num_reservas'] ?></span></td>
                                    <td class="text-right"><strong class="text-success">$<?= number_format($tc['total_pagado'], 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-users fa-2x mb-2" style="opacity:.3"></i>
                            <p>Sin datos en este período</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Ocupación por Cancha -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-futbol mr-2" style="color: <?= $moduloColor ?>"></i>
                            Ocupación por Cancha
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($ocupacion)): ?>
                        <?php $maxRes = max(array_column($ocupacion, 'total_reservas') ?: [1]); ?>
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Cancha</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Reservas</th>
                                    <th>Ocupación</th>
                                    <th class="text-right">Ingreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ocupacion as $oc): ?>
                                <?php $pct = $maxRes > 0 ? round(($oc['total_reservas'] / $maxRes) * 100) : 0; ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($oc['cancha']) ?></strong></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($oc['tipo'] ?? '-') ?></small></td>
                                    <td class="text-center"><span class="badge badge-primary"><?= (int)$oc['total_reservas'] ?></span></td>
                                    <td>
                                        <div class="progress progress-sm" style="height:8px;">
                                            <div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="text-right"><strong>$<?= number_format($oc['ingreso_total'], 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-futbol fa-2x mb-2" style="opacity:.3"></i>
                            <p>Sin datos de ocupación</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('chartIngresosDiarios');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartDiario['labels']) ?>,
                datasets: [
                    {
                        label: 'Pagos Reservas',
                        data: <?= json_encode($chartDiario['pagos']) ?>,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3
                    },
                    {
                        label: 'Entradas',
                        data: <?= json_encode($chartDiario['entradas']) ?>,
                        borderColor: '#EC4899',
                        backgroundColor: 'rgba(236,72,153,0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': $' + ctx.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: function(v) { return '$' + v; } } }
                }
            }
        });
    }
});
</script>
