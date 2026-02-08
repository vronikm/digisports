<?php
/**
 * Vista: Dashboard Principal
 * Panel de control con estadísticas y accesos rápidos a módulos
 * 
 * @var array $stats Estadísticas generales
 * @var array $charts Datos para gráficas
 * @var array $recentActivity Actividad reciente
 * @var array $alerts Alertas del sistema
 * @var array $modules Módulos disponibles
 */
$totalModulos = count($modules ?? []);
?>

<!-- ═══════════════ Dashboard Premium Styles ═══════════════ -->
<style>
.dashboard-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5f8b 50%, #1a73e8 100%);
    border-radius: 16px;
    padding: 2rem 2.5rem;
    margin-bottom: 2rem;
    color: white;
    position: relative;
    overflow: hidden;
}
.dashboard-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}
.dashboard-header h2 { font-weight: 700; margin: 0; font-size: 1.8rem; }
.dashboard-header p { opacity: 0.85; margin: 0.3rem 0 0; }

.kpi-card {
    background: white;
    border-radius: 14px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid rgba(0,0,0,0.04);
    transition: all 0.3s ease;
    height: 100%;
}
.kpi-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
.kpi-icon {
    width: 52px; height: 52px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: white; flex-shrink: 0;
}
.kpi-value { font-size: 1.8rem; font-weight: 800; line-height: 1; margin-bottom: 2px; }
.kpi-label { font-size: 0.82rem; color: #64748b; font-weight: 500; }

.module-card {
    background: white;
    border-radius: 16px;
    padding: 1.6rem 1.2rem;
    text-align: center;
    border: 1px solid rgba(0,0,0,0.05);
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}
.module-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: var(--module-color, #3B82F6);
    border-radius: 16px 16px 0 0;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.module-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.12);
    border-color: transparent;
}
.module-card:hover::before { opacity: 1; }
.module-icon-container {
    width: 64px; height: 64px; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem; font-size: 1.6rem; color: white;
    transition: transform 0.3s ease;
}
.module-card:hover .module-icon-container { transform: scale(1.1) rotate(-5deg); }
.module-card-title { font-size: 0.95rem; font-weight: 700; color: #1e293b; margin-bottom: 0.4rem; line-height: 1.3; }
.module-card-desc {
    font-size: 0.78rem; color: #94a3b8; margin: 0; line-height: 1.4;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.module-badge {
    position: absolute; top: 10px; right: 10px;
    font-size: 0.65rem; padding: 2px 8px; border-radius: 20px; font-weight: 600;
}

.chart-card {
    background: white; border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid rgba(0,0,0,0.04);
    overflow: hidden;
}
.chart-card .card-header {
    background: transparent; border-bottom: 1px solid #f1f5f9; padding: 1rem 1.5rem;
}
.chart-card .card-header h3 { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0; }

.activity-item {
    padding: 0.85rem 1.2rem; border-bottom: 1px solid #f8fafc; transition: background 0.2s;
}
.activity-item:hover { background: #f8fafc; }
.activity-item:last-child { border-bottom: none; }
.alert-item { display: flex; align-items: center; gap: 12px; padding: 0.85rem 1.2rem; border-bottom: 1px solid #f8fafc; }
.alert-item:last-child { border-bottom: none; }
.alert-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.empty-state { padding: 2.5rem; text-align: center; color: #94a3b8; }
.empty-state i { font-size: 2.5rem; margin-bottom: 0.8rem; opacity: 0.5; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-in { animation: fadeInUp 0.5s ease forwards; opacity: 0; }
</style>

<!-- ═══════════════ Header ═══════════════ -->
<div class="dashboard-header animate-in">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h2><i class="fas fa-tachometer-alt mr-2"></i>Panel de Control</h2>
            <p>Bienvenido, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?> — <?= date('l d \d\e F, Y') ?></p>
        </div>
        <div class="d-none d-md-block text-right">
            <span class="badge badge-light px-3 py-2" style="font-size: 0.85rem;">
                <i class="fas fa-cubes mr-1"></i> <?= $totalModulos ?> módulos activos
            </span>
        </div>
    </div>
</div>

<!-- ═══════════════ KPI Cards ═══════════════ -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="kpi-card animate-in" style="animation-delay: 0.1s">
            <div class="d-flex align-items-center">
                <div class="kpi-icon mr-3" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);"><i class="fas fa-building"></i></div>
                <div>
                    <div class="kpi-value" style="color: #3b82f6;"><?= number_format($stats['total_instalaciones'] ?? 0) ?></div>
                    <div class="kpi-label">Instalaciones</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="kpi-card animate-in" style="animation-delay: 0.15s">
            <div class="d-flex align-items-center">
                <div class="kpi-icon mr-3" style="background: linear-gradient(135deg, #22c55e, #16a34a);"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <div class="kpi-value" style="color: #22c55e;"><?= number_format($stats['reservas_mes'] ?? 0) ?></div>
                    <div class="kpi-label">Reservas este mes</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="kpi-card animate-in" style="animation-delay: 0.2s">
            <div class="d-flex align-items-center">
                <div class="kpi-icon mr-3" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="fas fa-users"></i></div>
                <div>
                    <div class="kpi-value" style="color: #f59e0b;"><?= number_format($stats['total_clientes'] ?? 0) ?></div>
                    <div class="kpi-label">Clientes</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="kpi-card animate-in" style="animation-delay: 0.25s">
            <div class="d-flex align-items-center">
                <div class="kpi-icon mr-3" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);"><i class="fas fa-user-friends"></i></div>
                <div>
                    <div class="kpi-value" style="color: #8b5cf6;"><?= number_format($stats['total_usuarios'] ?? 0) ?></div>
                    <div class="kpi-label">Usuarios</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════ Módulos del Sistema ═══════════════ -->
<div class="card chart-card mb-4 animate-in" style="animation-delay: 0.3s">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">
            <i class="fas fa-th-large mr-2" style="color: #3b82f6;"></i>
            Acceso Rápido a Módulos
        </h3>
        <span class="badge badge-primary badge-pill"><?= $totalModulos ?></span>
    </div>
    <div class="card-body">
        <?php if (!empty($modules)): ?>
            <div class="row">
                <?php foreach ($modules as $idx => $mod): 
                    $color = htmlspecialchars($mod['color'] ?? '#3B82F6');
                    $icono = htmlspecialchars($mod['icono'] ?? 'fas fa-cube');
                    $nombre = htmlspecialchars($mod['nombre'] ?? 'Módulo');
                    $descripcion = htmlspecialchars($mod['descripcion'] ?? '');
                    $ruta = $mod['ruta_modulo'] ?? '';
                    $esExterno = ($mod['es_externo'] ?? 0) == 1;
                    $requiereLicencia = ($mod['requiere_licencia'] ?? 0) == 1;
                    $moduloUrl = !empty($ruta) ? url($ruta, 'dashboard') : '#';
                    $delay = 0.05 * $idx;
                ?>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
                    <a href="<?= $moduloUrl ?>" class="text-decoration-none">
                        <div class="module-card animate-in" style="--module-color: <?= $color ?>; animation-delay: <?= (0.35 + $delay) ?>s">
                            <?php if ($esExterno): ?>
                                <span class="module-badge badge badge-info"><i class="fas fa-external-link-alt mr-1"></i>Externo</span>
                            <?php elseif ($requiereLicencia): ?>
                                <span class="module-badge badge badge-warning"><i class="fas fa-key mr-1"></i>Licencia</span>
                            <?php endif; ?>
                            <div class="module-icon-container" style="background: linear-gradient(135deg, <?= $color ?>, <?= $color ?>cc);">
                                <i class="<?= $icono ?>"></i>
                            </div>
                            <div class="module-card-title"><?= $nombre ?></div>
                            <?php if ($descripcion): ?>
                                <p class="module-card-desc"><?= $descripcion ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-cubes d-block"></i>
                <p>No hay módulos configurados</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════ Gráficas ═══════════════ -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card chart-card h-100 animate-in" style="animation-delay: 0.4s">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><i class="fas fa-chart-line mr-2" style="color: #22c55e;"></i>Ingresos Mensuales</h3>
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
            <div class="card-body">
                <canvas id="chartIngresos" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card chart-card h-100 animate-in" style="animation-delay: 0.45s">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><i class="fas fa-chart-pie mr-2" style="color: #f59e0b;"></i>Estado de Reservas</h3>
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
            <div class="card-body">
                <canvas id="chartEstadoReservas" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════ Actividad y Alertas ═══════════════ -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card chart-card h-100 animate-in" style="animation-delay: 0.5s">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><i class="fas fa-history mr-2" style="color: #06b6d4;"></i>Actividad Reciente</h3>
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recentActivity)): ?>
                    <?php foreach (array_slice($recentActivity, 0, 8) as $activity): ?>
                        <div class="activity-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="<?= $activity['icono'] ?? 'fas fa-circle' ?> mr-2" 
                                   style="color: <?= $activity['color'] ?? '#6c757d' ?>"></i>
                                <?= htmlspecialchars($activity['descripcion'] ?? '') ?>
                            </div>
                            <small class="text-muted text-nowrap ml-2"><?= timeAgo($activity['fecha'] ?? '') ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox d-block"></i>
                        <p class="mb-0">No hay actividad reciente</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($recentActivity)): ?>
                <div class="card-footer text-center py-2" style="background: #f8fafc; border-top: 1px solid #f1f5f9;">
                    <a href="<?= url('core', 'actividad') ?>" class="text-primary text-sm">Ver toda la actividad <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card chart-card h-100 animate-in" style="animation-delay: 0.55s">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><i class="fas fa-exclamation-triangle mr-2" style="color: #ef4444;"></i>Alertas y Pendientes</h3>
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($alerts)): ?>
                    <?php foreach ($alerts as $alert): ?>
                        <div class="alert-item">
                            <?php 
                                $alertColor = '#ef4444'; $alertIcon = 'fas fa-exclamation-circle';
                                switch($alert['type'] ?? 'danger') {
                                    case 'warning': $alertColor = '#f59e0b'; $alertIcon = 'fas fa-exclamation-triangle'; break;
                                    case 'info': $alertColor = '#3b82f6'; $alertIcon = 'fas fa-info-circle'; break;
                                    case 'success': $alertColor = '#22c55e'; $alertIcon = 'fas fa-check-circle'; break;
                                }
                            ?>
                            <div class="alert-dot" style="background: <?= $alertColor ?>;"></div>
                            <div class="flex-grow-1">
                                <i class="<?= $alertIcon ?> mr-1" style="color: <?= $alertColor ?>"></i>
                                <?= htmlspecialchars($alert['message'] ?? $alert['mensaje'] ?? '') ?>
                            </div>
                            <?php if (!empty($alert['url'])): ?>
                                <a href="<?= $alert['url'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-arrow-right"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash d-block"></i>
                        <p class="mb-0">No hay alertas pendientes</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════ Reservas de Hoy ═══════════════ -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card chart-card animate-in" style="animation-delay: 0.6s">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-calendar-day mr-2" style="color: #8b5cf6;"></i>Reservas de Hoy</h3>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="p-3 rounded" style="background: rgba(34,197,94,0.08);">
                            <div style="font-size: 2rem; font-weight: 800; color: #22c55e;"><?= number_format($stats['reservas_hoy_confirmadas'] ?? 0) ?></div>
                            <div class="text-muted font-weight-bold small"><i class="fas fa-check-circle mr-1 text-success"></i>Confirmadas</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="p-3 rounded" style="background: rgba(245,158,11,0.08);">
                            <div style="font-size: 2rem; font-weight: 800; color: #f59e0b;"><?= number_format($stats['reservas_hoy_pendientes'] ?? 0) ?></div>
                            <div class="text-muted font-weight-bold small"><i class="fas fa-clock mr-1 text-warning"></i>Pendientes</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: rgba(239,68,68,0.08);">
                            <div style="font-size: 2rem; font-weight: 800; color: #ef4444;"><?= number_format($stats['reservas_hoy_canceladas'] ?? 0) ?></div>
                            <div class="text-muted font-weight-bold small"><i class="fas fa-times-circle mr-1 text-danger"></i>Canceladas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════ Chart.js ═══════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ingresosData = <?= json_encode($charts['ingresos_mensuales'] ?? []) ?>;
    const ctxIngresos = document.getElementById('chartIngresos');
    if (ctxIngresos) {
        new Chart(ctxIngresos.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ingresosData.map(i => i.mes_label || i.mes),
                datasets: [{
                    label: 'Ingresos ($)',
                    data: ingresosData.map(i => parseFloat(i.ingresos) || 0),
                    backgroundColor: 'rgba(34, 197, 94, 0.15)',
                    borderColor: '#22c55e', borderWidth: 2, borderRadius: 6, barPercentage: 0.6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => '$ ' + ctx.raw.toLocaleString('es-EC', {minimumFractionDigits: 2}) } } },
                scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: v => '$ ' + v.toLocaleString() } }, x: { grid: { display: false } } }
            }
        });
    }

    const estadoData = <?= json_encode($charts['reservas_estado'] ?? []) ?>;
    const ctxEstado = document.getElementById('chartEstadoReservas');
    if (ctxEstado) {
        const colorMap = { 'CONFIRMADA': '#22c55e', 'PENDIENTE': '#f59e0b', 'CANCELADA': '#ef4444', 'COMPLETADA': '#3b82f6' };
        new Chart(ctxEstado.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: estadoData.map(e => e.estado),
                datasets: [{ data: estadoData.map(e => parseInt(e.total)), backgroundColor: estadoData.map(e => colorMap[e.estado] || '#94a3b8'), borderWidth: 0, hoverOffset: 6 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } } } }
        });
    }
});
</script>
