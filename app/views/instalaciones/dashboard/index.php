<?php
/**
 * DigiSports Arena - Vista Dashboard
 * Dashboard premium con KPIs, gráficos y resumen operativo
 */

$kpis            = $kpis ?? [];
$canchas         = $canchas ?? [];
$reservasHoy     = $reservas_hoy ?? [];
$chartReservas   = $chart_reservas ?? ['labels' => [], 'data' => []];
$ultimosPagos    = $ultimos_pagos ?? [];
$chartMetodos    = $chart_metodos_pago ?? ['labels' => [], 'data' => [], 'colors' => []];
$moduloColor     = $modulo_actual['color'] ?? '#3B82F6';
$moduloIcono     = $modulo_actual['icono'] ?? 'fas fa-building';
$moduloNombre    = $modulo_actual['nombre_personalizado'] ?? $modulo_actual['nombre'] ?? 'DigiSports Arena';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?> mr-2" style="color: <?= $moduloColor ?>"></i>
                    <?= htmlspecialchars($moduloNombre) ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right quick-actions">
                    <a href="<?= url('reservas', 'reserva', 'buscar') ?>" class="btn" style="background: <?= $moduloColor ?>; color: white;">
                        <i class="fas fa-calendar-plus mr-1"></i> Nueva Reserva
                    </a>
                    <a href="<?= url('instalaciones', 'cancha', 'index') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-futbol mr-1"></i> Ver Canchas
                    </a>
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
            <!-- Reservas de Hoy + Gráfico -->
            <div class="col-lg-8">
                <!-- Reservas de Hoy -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-day mr-2" style="color: <?= $moduloColor ?>"></i>
                            Reservas de Hoy
                        </h3>
                        <div class="card-tools">
                            <a href="<?= url('reservas', 'reserva', 'index') ?>" class="btn btn-sm btn-outline-secondary">
                                Ver todas <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($reservasHoy)): ?>
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Horario</th>
                                    <th>Cancha</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservasHoy as $r): ?>
                                <tr>
                                    <td>
                                        <i class="far fa-clock text-muted mr-1"></i>
                                        <?= htmlspecialchars(substr($r['hora_inicio'] ?? '', 0, 5)) ?> - <?= htmlspecialchars(substr($r['hora_fin'] ?? '', 0, 5)) ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-map-marker-alt mr-1" style="color: <?= $moduloColor ?>"></i>
                                        <?= htmlspecialchars($r['cancha_nombre'] ?? '-') ?>
                                    </td>
                                    <td><?= htmlspecialchars($r['cliente_nombre'] ?? '-') ?></td>
                                    <td><strong class="text-success">$<?= number_format((float)($r['total'] ?? 0), 2) ?></strong></td>
                                    <td>
                                        <?php
                                        $estadoClasses = [
                                            'CONFIRMADA' => 'badge-success',
                                            'PENDIENTE'  => 'badge-warning',
                                            'CANCELADA'  => 'badge-danger',
                                            'COMPLETADA' => 'badge-info',
                                        ];
                                        $estadoClass = $estadoClasses[strtoupper($r['estado'] ?? '')] ?? 'badge-secondary';
                                        ?>
                                        <span class="badge <?= $estadoClass ?>"><?= htmlspecialchars($r['estado'] ?? '-') ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fa-3x mb-3" style="opacity:.3"></i>
                            <p class="mb-0">No hay reservas programadas para hoy</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Gráfico Reservas últimos 7 días -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2" style="color: <?= $moduloColor ?>"></i>
                            Reservas — Últimos 7 días
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position:relative; height:260px;">
                            <canvas id="chartReservas7d"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Últimos Pagos -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-money-bill-wave mr-2 text-success"></i>
                            Últimos Pagos Recibidos
                        </h3>
                        <div class="card-tools">
                            <a href="<?= url('reservas', 'pago', 'index') ?>" class="btn btn-sm btn-outline-secondary">
                                Ver todos <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($ultimosPagos)): ?>
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Método</th>
                                    <th class="text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosPagos as $p): ?>
                                <tr>
                                    <td><small><?= date('d/m H:i', strtotime($p['rpa_fecha'])) ?></small></td>
                                    <td><?= htmlspecialchars($p['cliente_nombre'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $iconosM = [
                                            'EFECTIVO' => 'fas fa-money-bill-wave text-success',
                                            'TARJETA' => 'fas fa-credit-card text-primary',
                                            'TRANSFERENCIA' => 'fas fa-university text-info',
                                            'MONEDERO' => 'fas fa-wallet text-warning',
                                        ];
                                        $icM = $iconosM[$p['rpa_metodo_pago']] ?? 'fas fa-receipt';
                                        ?>
                                        <i class="<?= $icM ?> mr-1"></i>
                                        <small><?= $p['rpa_metodo_pago'] ?></small>
                                    </td>
                                    <td class="text-right"><strong class="text-success">$<?= number_format($p['rpa_monto'], 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2" style="opacity:.3"></i>
                            <p class="mb-0">No hay pagos registrados aún</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar derecho -->
            <div class="col-lg-4">
                <!-- Canchas -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-futbol mr-2" style="color: <?= $moduloColor ?>"></i>
                            Canchas
                        </h3>
                        <div class="card-tools">
                            <a href="<?= url('instalaciones', 'cancha', 'index') ?>" class="btn btn-sm btn-outline-secondary">
                                Gestionar <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($canchas)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($canchas as $idx => $c): ?>
                            <li class="list-group-item d-flex align-items-center">
                                <span class="badge mr-3" style="background:<?= $moduloColor ?>; color:#fff; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:50%; font-size:.8rem;">
                                    <?= $idx + 1 ?>
                                </span>
                                <div class="flex-grow-1">
                                    <strong><?= htmlspecialchars($c['nombre'] ?? '') ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($c['tipo'] ?? 'General') ?>
                                    </small>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-info" title="Reservas hoy">
                                        <i class="fas fa-calendar-check mr-1"></i><?= (int)($c['reservas_hoy'] ?? 0) ?>
                                    </span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-futbol fa-3x mb-3" style="opacity:.3"></i>
                            <p class="mb-0">No hay canchas registradas</p>
                            <a href="<?= url('instalaciones', 'cancha', 'crear') ?>" class="btn btn-sm mt-2" style="background:<?= $moduloColor ?>; color:#fff;">
                                <i class="fas fa-plus mr-1"></i> Crear Cancha
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-bolt mr-2 text-warning"></i>
                            Acciones Rápidas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?= url('reservas', 'reserva', 'buscar') ?>" class="btn btn-block mb-2" style="background: <?= $moduloColor ?>15; color: <?= $moduloColor ?>; border:1px solid <?= $moduloColor ?>40; text-align:left;">
                                <i class="fas fa-calendar-plus mr-2"></i> Nueva Reserva
                            </a>
                            <a href="<?= url('reservas', 'pago', 'index') ?>" class="btn btn-block mb-2" style="background: #F59E0B15; color: #F59E0B; border:1px solid #F59E0B40; text-align:left;">
                                <i class="fas fa-cash-register mr-2"></i> Historial de Pagos
                            </a>
                            <a href="<?= url('instalaciones', 'entrada', 'vender') ?>" class="btn btn-block mb-2" style="background: #EC489915; color: #EC4899; border:1px solid #EC489940; text-align:left;">
                                <i class="fas fa-ticket-alt mr-2"></i> Vender Entrada
                            </a>
                            <a href="<?= url('reservas', 'abon', 'index') ?>" class="btn btn-block mb-2" style="background: #06B6D415; color: #06B6D4; border:1px solid #06B6D440; text-align:left;">
                                <i class="fas fa-wallet mr-2"></i> Monedero / Abonos
                            </a>
                            <a href="<?= url('instalaciones', 'cancha', 'crear') ?>" class="btn btn-block mb-2" style="background: #10B98115; color: #10B981; border:1px solid #10B98140; text-align:left;">
                                <i class="fas fa-plus-circle mr-2"></i> Agregar Cancha
                            </a>
                            <a href="<?= url('instalaciones', 'mantenimiento', 'index') ?>" class="btn btn-block mb-2" style="background: #EF444415; color: #EF4444; border:1px solid #EF444440; text-align:left;">
                                <i class="fas fa-tools mr-2"></i> Mantenimientos
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Métodos de Pago (Donut) -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2" style="color: <?= $moduloColor ?>"></i>
                            Pagos por Método — Mes
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:200px;">
                            <canvas id="chartMetodosPago"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Hora actual -->
                <div class="card text-center" style="border-top: 3px solid <?= $moduloColor ?>;">
                    <div class="card-body">
                        <div id="arenaLiveClock" style="font-size: 2rem; font-weight: 700; color: <?= $moduloColor ?>;">--:--:--</div>
                        <small class="text-muted" id="arenaLiveDate"></small>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Estilos KPI -->
<style>
.kpi-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    transition: transform .2s, box-shadow .2s;
}
.kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,.1);
}
.kpi-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
}
.kpi-value { font-size: 1.5rem; font-weight: 700; line-height: 1.2; }
.kpi-label { font-size: .82rem; color: #6b7280; margin-top: 2px; }
.kpi-trend { font-size: .75rem; font-weight: 600; }
.kpi-trend.up   { color: #10B981; }
.kpi-trend.down { color: #EF4444; }
.quick-actions .btn { border-radius: 8px; font-weight: 500; }
</style>

<!-- Chart.js + Reloj -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ─── Gráfico Reservas 7 días ───
    const ctx = document.getElementById('chartReservas7d');
    if (ctx) {
        const color = '<?= $moduloColor ?>';
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartReservas['labels']) ?>,
                datasets: [{
                    label: 'Reservas',
                    data: <?= json_encode($chartReservas['data']) ?>,
                    backgroundColor: color + '60',
                    borderColor: color,
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
                }
            }
        });
    }

    // ─── Gráfico Métodos de Pago (Donut) ───
    const ctxMetodos = document.getElementById('chartMetodosPago');
    if (ctxMetodos) {
        new Chart(ctxMetodos, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chartMetodos['labels']) ?>,
                datasets: [{
                    data: <?= json_encode($chartMetodos['data']) ?>,
                    backgroundColor: <?= json_encode($chartMetodos['colors']) ?>,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 10, font: { size: 11 } }
                    }
                }
            }
        });
    }

    // ─── Reloj en vivo ───
    function updateClock() {
        const now = new Date();
        document.getElementById('arenaLiveClock').textContent =
            now.toLocaleTimeString('es-EC', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        document.getElementById('arenaLiveDate').textContent =
            now.toLocaleDateString('es-EC', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }
    updateClock();
    setInterval(updateClock, 1000);
});
</script>
