<?php
/**
 * DigiSports Seguridad - Dashboard Mejorado
 * Panel principal con KPIs, gráficos interactivos y actividad en tiempo real
 */

$kpis = $kpis ?? [];
$stats = $stats ?? [];
$recentActivity = $recentActivity ?? [];
$securityAlerts = $securityAlerts ?? [];
$chartData = $chartData ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
$moduloIcono = $modulo_actual['icono'] ?? 'fas fa-shield-alt';
$dashboardTitle = $modulo_actual['nombre_personalizado'] ?? $modulo_actual['nombre'] ?? 'Panel de Seguridad';
?>

<style>
/* ═══════════════════════════════════════════════════════
   Dashboard Seguridad — Estilos Premium
   ═══════════════════════════════════════════════════════ */
.dashboard-header {
    background: linear-gradient(135deg, <?= $moduloColor ?> 0%, <?= $moduloColor ?>dd 50%, <?= $moduloColor ?>aa 100%);
    border-radius: 16px;
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
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
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}
.dashboard-header::after {
    content: '';
    position: absolute;
    bottom: -60%;
    right: 15%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}
.dashboard-header h1 {
    font-size: 1.6rem;
    font-weight: 700;
    margin: 0;
    position: relative;
    z-index: 1;
}
.dashboard-header .header-subtitle {
    opacity: 0.85;
    font-size: 0.9rem;
    margin-top: 4px;
    position: relative;
    z-index: 1;
}
.dashboard-header .header-actions {
    position: relative;
    z-index: 1;
}
.dashboard-header .btn-header {
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    border-radius: 10px;
    padding: 0.5rem 1.2rem;
    font-weight: 500;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(4px);
}
.dashboard-header .btn-header:hover {
    background: rgba(255,255,255,0.35);
    transform: translateY(-1px);
    color: white;
    text-decoration: none;
}
.dashboard-header .btn-header-solid {
    background: white;
    color: <?= $moduloColor ?>;
    border: none;
    font-weight: 600;
}
.dashboard-header .btn-header-solid:hover {
    background: #f8fafc;
    color: <?= $moduloColor ?>;
}

/* KPI Cards */
.kpi-card-v2 {
    border: none;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    position: relative;
}
.kpi-card-v2:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.kpi-card-v2 .card-body { padding: 1.25rem; position: relative; }
.kpi-card-v2 .kpi-icon-v2 {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; flex-shrink: 0;
}
.kpi-card-v2 .kpi-value-v2 {
    font-size: 1.75rem; font-weight: 800;
    color: #0f172a; line-height: 1; letter-spacing: -0.5px;
}
.kpi-card-v2 .kpi-label-v2 {
    color: #64748b; font-size: 0.78rem; font-weight: 500;
    margin-top: 2px; text-transform: uppercase; letter-spacing: 0.3px;
}
.kpi-card-v2 .kpi-accent {
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
}
@keyframes countUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.kpi-card-v2 .kpi-value-v2 { animation: countUp 0.6s ease-out forwards; }
.kpi-card-v2:nth-child(1) .kpi-value-v2 { animation-delay: 0.05s; }
.kpi-card-v2:nth-child(2) .kpi-value-v2 { animation-delay: 0.1s; }
.kpi-card-v2:nth-child(3) .kpi-value-v2 { animation-delay: 0.15s; }
.kpi-card-v2:nth-child(4) .kpi-value-v2 { animation-delay: 0.2s; }
.kpi-card-v2:nth-child(5) .kpi-value-v2 { animation-delay: 0.25s; }
.kpi-card-v2:nth-child(6) .kpi-value-v2 { animation-delay: 0.3s; }
.kpi-card-v2:nth-child(7) .kpi-value-v2 { animation-delay: 0.35s; }

/* Chart Cards */
.chart-card {
    border: none; border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: box-shadow 0.3s ease;
}
.chart-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.chart-card .card-header {
    background: transparent;
    border-bottom: 1px solid #f1f5f9;
    padding: 1rem 1.25rem 0.75rem;
}
.chart-card .card-header .card-title {
    font-weight: 600; font-size: 0.95rem; color: #1e293b;
}
.chart-card .card-header .card-subtitle {
    font-size: 0.75rem; color: #94a3b8; margin-top: 2px;
}
.chart-card .card-body { padding: 1rem 1.25rem 1.25rem; }

/* Activity Timeline */
.activity-timeline { position: relative; padding-left: 28px; }
.activity-timeline::before {
    content: '';
    position: absolute; left: 10px; top: 0; bottom: 0;
    width: 2px; background: #e2e8f0; border-radius: 1px;
}
.timeline-item {
    position: relative; padding-bottom: 1.25rem;
    animation: fadeInUp 0.4s ease-out forwards; opacity: 0;
}
.timeline-item:last-child { padding-bottom: 0; }
.timeline-item:nth-child(1) { animation-delay: 0.1s; }
.timeline-item:nth-child(2) { animation-delay: 0.15s; }
.timeline-item:nth-child(3) { animation-delay: 0.2s; }
.timeline-item:nth-child(4) { animation-delay: 0.25s; }
.timeline-item:nth-child(5) { animation-delay: 0.3s; }
.timeline-item:nth-child(6) { animation-delay: 0.35s; }
.timeline-item:nth-child(7) { animation-delay: 0.4s; }
.timeline-item:nth-child(8) { animation-delay: 0.45s; }
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
.timeline-dot {
    position: absolute; left: -23px; top: 4px;
    width: 14px; height: 14px; border-radius: 50%;
    border: 3px solid white; z-index: 1;
}
.timeline-dot.login { background: #22c55e; box-shadow: 0 0 0 2px #bbf7d0; }
.timeline-dot.logout { background: #3b82f6; box-shadow: 0 0 0 2px #bfdbfe; }
.timeline-dot.failed { background: #ef4444; box-shadow: 0 0 0 2px #fecaca; }
.timeline-dot.other { background: #94a3b8; box-shadow: 0 0 0 2px #e2e8f0; }
.timeline-content {
    background: #f8fafc; border-radius: 10px;
    padding: 0.7rem 1rem; border: 1px solid #f1f5f9;
    transition: background 0.2s;
}
.timeline-content:hover { background: #f1f5f9; }
.timeline-user { font-weight: 600; color: #1e293b; font-size: 0.85rem; }
.timeline-action { font-size: 0.75rem; margin-top: 1px; }
.timeline-meta { font-size: 0.72rem; color: #94a3b8; margin-top: 3px; }

/* Alert Cards */
.alert-card {
    border: none; border-radius: 12px; padding: 0.85rem 1rem;
    margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;
    transition: transform 0.2s;
}
.alert-card:hover { transform: translateX(4px); }
.alert-card.alert-warning-v2 { background: #fefce8; border-left: 4px solid #eab308; }
.alert-card.alert-danger-v2 { background: #fef2f2; border-left: 4px solid #ef4444; }
.alert-card.alert-info-v2 { background: #eff6ff; border-left: 4px solid #3b82f6; }
.alert-card.alert-success-v2 { background: #f0fdf4; border-left: 4px solid #22c55e; }
.alert-icon-circle {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; flex-shrink: 0;
}
.alert-warning-v2 .alert-icon-circle { background: #fef08a; color: #a16207; }
.alert-danger-v2 .alert-icon-circle { background: #fecaca; color: #dc2626; }
.alert-info-v2 .alert-icon-circle { background: #bfdbfe; color: #2563eb; }
.alert-success-v2 .alert-icon-circle { background: #bbf7d0; color: #16a34a; }
.alert-message { font-size: 0.85rem; color: #334155; font-weight: 500; }

/* Quick Actions v2 */
.quick-action-item {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 1rem 0.5rem; border-radius: 12px;
    background: #f8fafc; border: 1px solid #f1f5f9;
    transition: all 0.3s ease; text-decoration: none !important;
    color: #475569; min-height: 90px;
}
.quick-action-item:hover {
    background: white; border-color: <?= $moduloColor ?>40;
    box-shadow: 0 4px 12px <?= $moduloColor ?>15;
    transform: translateY(-2px); color: <?= $moduloColor ?>;
}
.quick-action-item .qa-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; margin-bottom: 0.5rem; transition: all 0.3s;
}
.quick-action-item:hover .qa-icon { transform: scale(1.1); }
.quick-action-item .qa-label {
    font-size: 0.72rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.3px; text-align: center;
}

/* System Status */
.status-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.6rem 0; border-bottom: 1px solid #f1f5f9;
}
.status-item:last-child { border-bottom: none; }
.status-item .status-label {
    color: #64748b; font-size: 0.83rem;
    display: flex; align-items: center; gap: 8px;
}
.status-item .status-label i { width: 16px; text-align: center; }
.status-item .status-value { font-weight: 600; font-size: 0.85rem; color: #1e293b; }
.status-dot {
    width: 8px; height: 8px; border-radius: 50%;
    display: inline-block; margin-right: 6px;
    animation: pulse 2s infinite;
}
.status-dot.online { background: #22c55e; }
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@media (max-width: 768px) {
    .dashboard-header { padding: 1rem 1.25rem; border-radius: 12px; }
    .dashboard-header h1 { font-size: 1.2rem; }
    .kpi-card-v2 .kpi-value-v2 { font-size: 1.4rem; }
}
</style>

<!-- ═══════════════ HEADER ═══════════════ -->
<section class="content pt-3">
<div class="container-fluid">

<div class="dashboard-header">
    <div class="row align-items-center">
        <div class="col-lg-7 col-md-6">
            <h1>
                <i class="<?= $moduloIcono ?> mr-2"></i>
                <?= htmlspecialchars($dashboardTitle) ?>
            </h1>
            <div class="header-subtitle">
                <i class="fas fa-calendar-alt mr-1"></i>
                <?= date('d/m/Y') ?>
                &nbsp;•&nbsp;
                <i class="fas fa-clock mr-1"></i>
                <span id="live-clock"><?= date('H:i:s') ?></span>
            </div>
        </div>
        <div class="col-lg-5 col-md-6 text-md-right mt-3 mt-md-0 header-actions">
            <a href="<?= url('seguridad', 'usuario', 'crear') ?>" class="btn btn-header btn-header-solid mr-2">
                <i class="fas fa-user-plus mr-1"></i> Nuevo Usuario
            </a>
            <a href="<?= url('seguridad', 'tenant', 'crear') ?>" class="btn btn-header">
                <i class="fas fa-building mr-1"></i> Nuevo Tenant
            </a>
        </div>
    </div>
</div>

<!-- ═══════════════ KPI CARDS ═══════════════ -->
<div class="row mb-2">
    <?php foreach ($kpis as $i => $kpi): ?>
    <div class="col-xl col-lg-3 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card-v2 h-100">
            <div class="kpi-accent" style="background: <?= $kpi['color'] ?>;"></div>
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="kpi-icon-v2" style="background: <?= $kpi['color'] ?>15; color: <?= $kpi['color'] ?>;">
                        <i class="<?= $kpi['icon'] ?>"></i>
                    </div>
                    <?php if ($kpi['label'] === 'Logins Fallidos Hoy' && $kpi['value'] > 0): ?>
                    <span class="badge" style="background: #fef2f2; color: #dc2626; font-size: 0.7rem; padding: 4px 8px; border-radius: 8px;">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Atención
                    </span>
                    <?php elseif ($kpi['label'] === 'Por Vencer' && $kpi['value'] > 0): ?>
                    <span class="badge" style="background: #fefce8; color: #a16207; font-size: 0.7rem; padding: 4px 8px; border-radius: 8px;">
                        <i class="fas fa-clock mr-1"></i>Revisar
                    </span>
                    <?php endif; ?>
                </div>
                <div class="kpi-value-v2"><?= number_format($kpi['value']) ?></div>
                <div class="kpi-label-v2"><?= htmlspecialchars($kpi['label']) ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ═══════════════ GRÁFICOS PRINCIPALES ═══════════════ -->
<div class="row">
    <!-- Logins de la Semana -->
    <div class="col-lg-8 mb-3">
        <div class="card chart-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-bar mr-2" style="color: <?= $moduloColor ?>"></i>
                        Actividad de Logins — Últimos 7 Días
                    </h3>
                    <div class="card-subtitle">Comparativa de accesos exitosos vs intentos fallidos</div>
                </div>
                <div class="card-tools">
                    <a href="<?= url('seguridad', 'auditoria', 'accesos') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-size: 0.75rem;">
                        Ver todo <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 280px;">
                    <canvas id="chartLoginsWeek"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Usuarios por Rol -->
    <div class="col-lg-4 mb-3">
        <div class="card chart-card h-100">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-chart-pie mr-2" style="color: #8B5CF6"></i>
                    Usuarios por Rol
                </h3>
                <div class="card-subtitle">Distribución de usuarios activos</div>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="position: relative; width: 100%; max-width: 260px;">
                    <canvas id="chartUsuariosRol"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Actividad por Hora -->
    <div class="col-lg-6 mb-3">
        <div class="card chart-card h-100">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-wave-square mr-2" style="color: #06B6D4"></i>
                    Actividad por Hora — Últimas 24h
                </h3>
                <div class="card-subtitle">Picos de actividad del sistema</div>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 220px;">
                    <canvas id="chartActividadHora"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Módulos más asignados -->
    <div class="col-lg-6 mb-3">
        <div class="card chart-card h-100">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-puzzle-piece mr-2" style="color: #F59E0B"></i>
                    Módulos más Asignados
                </h3>
                <div class="card-subtitle">Top módulos por cantidad de tenants</div>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 220px;">
                    <canvas id="chartModulos"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════ ACTIVIDAD, ALERTAS Y SISTEMA ═══════════════ -->
<div class="row">
    <!-- Timeline de Actividad -->
    <div class="col-lg-5 mb-3">
        <div class="card chart-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title mb-0">
                        <i class="fas fa-stream mr-2" style="color: <?= $moduloColor ?>"></i>
                        Actividad Reciente
                    </h3>
                    <div class="card-subtitle">Últimos eventos del sistema</div>
                </div>
                <a href="<?= url('seguridad', 'auditoria', 'accesos') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-size: 0.75rem;">
                    Ver logs
                </a>
            </div>
            <div class="card-body" style="max-height: 420px; overflow-y: auto;">
                <?php if (empty($recentActivity)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3" style="color: #e2e8f0;"></i>
                    <p>Sin actividad reciente</p>
                </div>
                <?php else: ?>
                <div class="activity-timeline">
                    <?php foreach ($recentActivity as $log): 
                        $tipo = $log['acc_tipo'] ?? 'INFO';
                        switch ($tipo) {
                            case 'LOGIN': $dotClass = 'login'; $actionLabel = 'Inicio de sesión exitoso'; $actionColor = '#16a34a'; break;
                            case 'LOGOUT': $dotClass = 'logout'; $actionLabel = 'Cerró sesión'; $actionColor = '#2563eb'; break;
                            case 'LOGIN_FAILED': $dotClass = 'failed'; $actionLabel = 'Intento de login fallido'; $actionColor = '#dc2626'; break;
                            default: $dotClass = 'other'; $actionLabel = $tipo; $actionColor = '#64748b'; break;
                        }
                    ?>
                    <div class="timeline-item">
                        <div class="timeline-dot <?= $dotClass ?>"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="timeline-user">
                                    <?= htmlspecialchars($log['usu_username'] ?? 'Sistema') ?>
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;">
                                    <?= !empty($log['acc_fecha']) ? date('H:i', strtotime($log['acc_fecha'])) : '--' ?>
                                </small>
                            </div>
                            <div class="timeline-action" style="color: <?= $actionColor ?>;">
                                <?= $actionLabel ?>
                            </div>
                            <div class="timeline-meta">
                                <i class="fas fa-globe mr-1"></i><?= htmlspecialchars($log['acc_ip'] ?? '127.0.0.1') ?>
                                <?php if (!empty($log['acc_fecha'])): ?>
                                &nbsp;•&nbsp;
                                <i class="fas fa-calendar mr-1"></i><?= date('d/m/Y', strtotime($log['acc_fecha'])) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Alertas + Accesos Rápidos -->
    <div class="col-lg-3 mb-3">
        <!-- Alertas de Seguridad -->
        <div class="card chart-card mb-3">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-shield-alt mr-2" style="color: #EF4444"></i>
                    Alertas
                </h3>
            </div>
            <div class="card-body py-2">
                <?php if (empty($securityAlerts)): ?>
                <div class="alert-card alert-success-v2">
                    <div class="alert-icon-circle"><i class="fas fa-check-circle"></i></div>
                    <div class="alert-message">Sin alertas</div>
                </div>
                <?php else: ?>
                    <?php foreach ($securityAlerts as $alert): 
                        switch ($alert['type']) {
                            case 'danger': $alertClass = 'alert-danger-v2'; break;
                            case 'warning': $alertClass = 'alert-warning-v2'; break;
                            case 'info': $alertClass = 'alert-info-v2'; break;
                            default: $alertClass = 'alert-success-v2'; break;
                        }
                    ?>
                    <div class="alert-card <?= $alertClass ?>">
                        <div class="alert-icon-circle">
                            <i class="<?= $alert['icon'] ?>"></i>
                        </div>
                        <div class="alert-message"><?= htmlspecialchars($alert['message']) ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Accesos Rápidos -->
        <div class="card chart-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-bolt mr-2" style="color: #F59E0B"></i>
                    Accesos Rápidos
                </h3>
            </div>
            <div class="card-body py-2">
                <div class="row g-2">
                    <?php
                    $quickLinks = [
                        ['label' => 'Usuarios', 'icon' => 'fas fa-users', 'color' => '#3B82F6', 'url' => url('seguridad', 'usuario', 'index')],
                        ['label' => 'Roles', 'icon' => 'fas fa-user-shield', 'color' => '#22C55E', 'url' => url('seguridad', 'rol', 'index')],
                        ['label' => 'Tenants', 'icon' => 'fas fa-building', 'color' => '#8B5CF6', 'url' => url('seguridad', 'tenant', 'index')],
                        ['label' => 'Módulos', 'icon' => 'fas fa-puzzle-piece', 'color' => '#F59E0B', 'url' => url('seguridad', 'modulo', 'index')],
                        ['label' => 'Logs', 'icon' => 'fas fa-history', 'color' => '#06B6D4', 'url' => url('seguridad', 'auditoria', 'accesos')],
                        ['label' => 'Iconos', 'icon' => 'fas fa-icons', 'color' => '#EC4899', 'url' => url('seguridad', 'modulo', 'iconos')],
                    ];
                    foreach ($quickLinks as $ql): ?>
                    <div class="col-4 mb-2">
                        <a href="<?= $ql['url'] ?>" class="quick-action-item">
                            <div class="qa-icon" style="background: <?= $ql['color'] ?>15; color: <?= $ql['color'] ?>;">
                                <i class="<?= $ql['icon'] ?>"></i>
                            </div>
                            <div class="qa-label"><?= $ql['label'] ?></div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado del Sistema + Tenants por Plan -->
    <div class="col-lg-4 mb-3">
        <div class="card chart-card mb-3">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-server mr-2" style="color: #3B82F6"></i>
                    Estado del Sistema
                </h3>
            </div>
            <div class="card-body py-2">
                <div class="status-item">
                    <span class="status-label"><i class="fab fa-php"></i> PHP</span>
                    <span class="status-value"><?= PHP_VERSION ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label"><i class="fas fa-database"></i> MySQL</span>
                    <span class="status-value">
                        <span class="status-dot online"></span>Conectado
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-label"><i class="fas fa-users"></i> Sesiones Activas</span>
                    <span class="status-value" style="color: <?= $moduloColor ?>;"><?= $stats['usuarios_online'] ?? 0 ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label"><i class="fas fa-memory"></i> Memoria</span>
                    <span class="status-value"><?= round(memory_get_usage() / 1024 / 1024, 2) ?> MB</span>
                </div>
                <div class="status-item">
                    <span class="status-label"><i class="fas fa-hdd"></i> Disco Libre</span>
                    <span class="status-value"><?= round(@disk_free_space('.') / 1024 / 1024 / 1024, 1) ?> GB</span>
                </div>
            </div>
        </div>
        <div class="card chart-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-layer-group mr-2" style="color: #22C55E"></i>
                    Tenants por Plan
                </h3>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="position: relative; width: 100%; max-width: 200px;">
                    <canvas id="chartTenantsPlan"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
</section>

<!-- ═══════════════ SCRIPTS ═══════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reloj en vivo
    const clockEl = document.getElementById('live-clock');
    if (clockEl) {
        setInterval(() => {
            const now = new Date();
            clockEl.textContent = now.toLocaleTimeString('es-EC', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
        }, 1000);
    }

    // Paleta
    const COLORS = {
        primary: '#3B82F6', success: '#22C55E', danger: '#EF4444',
        warning: '#F59E0B', info: '#06B6D4', purple: '#8B5CF6',
        pink: '#EC4899', indigo: '#6366F1', teal: '#14B8A6', orange: '#F97316'
    };
    const chartPalette = Object.values(COLORS);

    // Defaults Chart.js
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.padding = 16;
    Chart.defaults.plugins.legend.labels.font = { size: 11, weight: '500' };

    // 1) Logins de la Semana
    const loginsData = <?= json_encode($chartData['logins_semana'] ?? []) ?>;
    new Chart(document.getElementById('chartLoginsWeek'), {
        type: 'bar',
        data: {
            labels: loginsData.map(d => d.label),
            datasets: [
                {
                    label: 'Exitosos', data: loginsData.map(d => d.exitosos),
                    backgroundColor: COLORS.success + 'CC', borderColor: COLORS.success,
                    borderWidth: 1, borderRadius: 6, borderSkipped: false,
                    barPercentage: 0.6, categoryPercentage: 0.7
                },
                {
                    label: 'Fallidos', data: loginsData.map(d => d.fallidos),
                    backgroundColor: COLORS.danger + '99', borderColor: COLORS.danger,
                    borderWidth: 1, borderRadius: 6, borderSkipped: false,
                    barPercentage: 0.6, categoryPercentage: 0.7
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { position: 'top', align: 'end' },
                tooltip: { backgroundColor: '#1e293b', titleFont: { weight: '600' }, padding: 12, cornerRadius: 10, displayColors: true, boxPadding: 4 }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, color: '#94a3b8' }, grid: { color: '#f1f5f9', drawBorder: false } },
                x: { ticks: { color: '#64748b', font: { weight: '500' } }, grid: { display: false } }
            }
        }
    });

    // 2) Usuarios por Rol
    const rolData = <?= json_encode($chartData['usuarios_por_rol'] ?? []) ?>;
    if (rolData.length > 0) {
        new Chart(document.getElementById('chartUsuariosRol'), {
            type: 'doughnut',
            data: {
                labels: rolData.map(d => d.rol),
                datasets: [{
                    data: rolData.map(d => d.total),
                    backgroundColor: chartPalette.slice(0, rolData.length),
                    borderWidth: 2, borderColor: '#ffffff', hoverOffset: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: true, cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 10 } } },
                    tooltip: { backgroundColor: '#1e293b', padding: 10, cornerRadius: 8, callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' usuario(s)' } }
                }
            }
        });
    } else {
        document.getElementById('chartUsuariosRol').parentElement.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-users fa-2x mb-2" style="color:#e2e8f0"></i><p class="mb-0" style="font-size:0.82rem">Sin datos de roles</p></div>';
    }

    // 3) Actividad por Hora
    const actividadData = <?= json_encode($chartData['actividad_por_hora'] ?? []) ?>;
    const actCtx = document.getElementById('chartActividadHora').getContext('2d');
    const actGradient = actCtx.createLinearGradient(0, 0, 0, 220);
    actGradient.addColorStop(0, COLORS.info + '40');
    actGradient.addColorStop(1, COLORS.info + '05');
    new Chart(actCtx, {
        type: 'line',
        data: {
            labels: actividadData.map(d => d.hora),
            datasets: [{
                label: 'Eventos', data: actividadData.map(d => d.total),
                borderColor: COLORS.info, backgroundColor: actGradient,
                borderWidth: 2.5, fill: true, tension: 0.4,
                pointBackgroundColor: COLORS.info, pointBorderColor: '#ffffff',
                pointBorderWidth: 2, pointRadius: 3, pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1e293b', padding: 10, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, color: '#94a3b8', font: { size: 10 } }, grid: { color: '#f1f5f9', drawBorder: false } },
                x: { ticks: { color: '#94a3b8', font: { size: 9 }, maxRotation: 0, callback: function(val, i) { return i % 3 === 0 ? this.getLabelForValue(val) : ''; } }, grid: { display: false } }
            }
        }
    });

    // 4) Módulos más asignados
    const modulosData = <?= json_encode($chartData['modulos_mas_usados'] ?? []) ?>;
    if (modulosData.length > 0) {
        new Chart(document.getElementById('chartModulos'), {
            type: 'bar',
            data: {
                labels: modulosData.map(d => d.nombre),
                datasets: [{
                    label: 'Asignaciones', data: modulosData.map(d => d.asignaciones),
                    backgroundColor: chartPalette.slice(0, modulosData.length).map(c => c + 'CC'),
                    borderColor: chartPalette.slice(0, modulosData.length),
                    borderWidth: 1, borderRadius: 6, borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: '#1e293b', padding: 10, cornerRadius: 8, callbacks: { label: ctx => ' ' + ctx.parsed.x + ' tenant(s)' } }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, color: '#94a3b8' }, grid: { color: '#f1f5f9', drawBorder: false } },
                    y: { ticks: { color: '#475569', font: { size: 11, weight: '500' } }, grid: { display: false } }
                }
            }
        });
    } else {
        document.getElementById('chartModulos').parentElement.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-puzzle-piece fa-2x mb-2" style="color:#e2e8f0"></i><p class="mb-0" style="font-size:0.82rem">Sin datos de módulos</p></div>';
    }

    // 5) Tenants por Plan
    const planData = <?= json_encode($chartData['tenants_por_plan'] ?? []) ?>;
    if (planData.length > 0) {
        new Chart(document.getElementById('chartTenantsPlan'), {
            type: 'polarArea',
            data: {
                labels: planData.map(d => d.plan_nombre),
                datasets: [{
                    data: planData.map(d => d.total),
                    backgroundColor: chartPalette.slice(0, planData.length).map(c => c + '99'),
                    borderColor: chartPalette.slice(0, planData.length), borderWidth: 1
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 10 } } },
                    tooltip: { backgroundColor: '#1e293b', padding: 10, cornerRadius: 8 }
                },
                scales: { r: { ticks: { display: false }, grid: { color: '#f1f5f9' } } }
            }
        });
    } else {
        document.getElementById('chartTenantsPlan').parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-chart-pie fa-2x mb-2" style="color:#e2e8f0"></i><p class="mb-0" style="font-size:0.82rem">Sin datos de planes</p></div>';
    }
});
</script>
