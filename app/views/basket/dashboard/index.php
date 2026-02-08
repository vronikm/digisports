<?php
/**
 * DigiSports Basket - Vista Dashboard
 */

$kpis = $kpis ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F97316';
$moduloIcono = $modulo_actual['icono'] ?? 'fas fa-basketball-ball';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?> mr-2" style="color: <?= $moduloColor ?>"></i>
                    <?= $modulo_actual['nombre_personalizado'] ?? $modulo_actual['nombre'] ?? 'Dashboard Basket' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right quick-actions">
                    <a href="<?= url('basket', 'reserva', 'crear') ?>" class="btn" style="background: <?= $moduloColor ?>; color: white;">
                        <i class="fas fa-plus mr-1"></i> Nueva Reserva
                    </a>
                    <a href="<?= url('basket', 'calendario', 'index') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-calendar-alt mr-1"></i> Calendario
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
        
        <!-- Contenido específico de Basket -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt text-warning mr-2"></i>
                            Partidos del Día
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="time-label">
                                <span class="bg-warning">Hoy</span>
                            </div>
                            <div>
                                <i class="fas fa-basketball-ball bg-orange"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> 10:00</span>
                                    <h3 class="timeline-header">Cancha Principal</h3>
                                    <div class="timeline-body">
                                        <strong>Equipo A vs Equipo B</strong> - Liga Amateur
                                    </div>
                                </div>
                            </div>
                            <div>
                                <i class="fas fa-basketball-ball bg-orange"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> 14:00</span>
                                    <h3 class="timeline-header">Cancha 2</h3>
                                    <div class="timeline-body">
                                        <strong>Entrenamiento Sub-18</strong> - Escuela Formativa
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card bg-gradient-warning">
                    <div class="card-body">
                        <h5 class="text-white">🏀 Liga Activa</h5>
                        <h3 class="text-white">Torneo Apertura 2026</h3>
                        <p class="text-white-50 mb-0">
                            <i class="fas fa-users mr-1"></i> 8 equipos participando
                        </p>
                        <a href="<?= url('basket', 'liga', 'index') ?>" class="btn btn-light btn-sm mt-3">
                            Ver Posiciones
                        </a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">Top Anotadores</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>🥇 Juan Pérez</span>
                                <strong>28.5 PPG</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>🥈 Carlos López</span>
                                <strong>24.2 PPG</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>🥉 Miguel Torres</span>
                                <strong>21.8 PPG</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
