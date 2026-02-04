<?php
/**
 * DigiSports Multideporte - Vista Dashboard
 */

$kpis = $kpis ?? [];
$disciplinas = $disciplinas ?? [];
$moduloColor = $modulo_actual['color'] ?? '#7C3AED';
$moduloIcono = $modulo_actual['icono'] ?? 'fas fa-running';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?> mr-2" style="color: <?= $moduloColor ?>"></i>
                    <?= $modulo_actual['nombre_personalizado'] ?? $modulo_actual['nombre'] ?? 'Dashboard Multideporte' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right quick-actions">
                    <a href="<?= url('multideporte', 'alumno', 'crear') ?>" class="btn" style="background: <?= $moduloColor ?>; color: white;">
                        <i class="fas fa-user-plus mr-1"></i> Nuevo Alumno
                    </a>
                    <a href="<?= url('multideporte', 'reserva', 'crear') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-calendar-plus mr-1"></i> Nueva Reserva
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
        
        <!-- Distribución de disciplinas -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2" style="color: <?= $moduloColor ?>"></i>
                            Distribución por Disciplina
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 col-md-4 col-6 mb-4 text-center">
                                <div class="p-3 rounded" style="background: #22C55E15;">
                                    <i class="fas fa-futbol fa-3x mb-2" style="color: #22C55E;"></i>
                                    <h4 class="mb-0">156</h4>
                                    <small class="text-muted">Fútbol</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6 mb-4 text-center">
                                <div class="p-3 rounded" style="background: #F9731615;">
                                    <i class="fas fa-basketball-ball fa-3x mb-2" style="color: #F97316;"></i>
                                    <h4 class="mb-0">89</h4>
                                    <small class="text-muted">Basketball</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6 mb-4 text-center">
                                <div class="p-3 rounded" style="background: #0EA5E915;">
                                    <i class="fas fa-swimmer fa-3x mb-2" style="color: #0EA5E9;"></i>
                                    <h4 class="mb-0">234</h4>
                                    <small class="text-muted">Natación</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6 mb-4 text-center">
                                <div class="p-3 rounded" style="background: #EAB30815;">
                                    <i class="fas fa-volleyball-ball fa-3x mb-2" style="color: #EAB308;"></i>
                                    <h4 class="mb-0">67</h4>
                                    <small class="text-muted">Voleibol</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6 mb-4 text-center">
                                <div class="p-3 rounded" style="background: #DC262615;">
                                    <i class="fas fa-hand-rock fa-3x mb-2" style="color: #DC2626;"></i>
                                    <h4 class="mb-0">112</h4>
                                    <small class="text-muted">Artes Marciales</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6 mb-4 text-center">
                                <div class="p-3 rounded" style="background: #EC489915;">
                                    <i class="fas fa-table-tennis fa-3x mb-2" style="color: #EC4899;"></i>
                                    <h4 class="mb-0">45</h4>
                                    <small class="text-muted">Tenis de Mesa</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6 mb-4 text-center">
                                <div class="p-3 rounded" style="background: #1F293715;">
                                    <i class="fas fa-chess fa-3x mb-2" style="color: #1F2937;"></i>
                                    <h4 class="mb-0">78</h4>
                                    <small class="text-muted">Ajedrez</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6 mb-4 text-center">
                                <div class="p-3 rounded" style="background: #6366F115;">
                                    <i class="fas fa-dumbbell fa-3x mb-2" style="color: #6366F1;"></i>
                                    <h4 class="mb-0">189</h4>
                                    <small class="text-muted">Gimnasio</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Actividades hoy -->
                <div class="card">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, <?= $moduloColor ?>, #9333ea); color: white;">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-day mr-2"></i>
                            Actividades de Hoy
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-futbol text-success mr-2"></i> Escuela Fútbol</span>
                                <span class="badge badge-success">3 clases</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-swimmer text-info mr-2"></i> Natación</span>
                                <span class="badge badge-info">5 clases</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-basketball-ball text-orange mr-2" style="color:#F97316"></i> Basketball</span>
                                <span class="badge" style="background:#F97316">2 clases</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hand-rock text-danger mr-2"></i> Artes Marciales</span>
                                <span class="badge badge-danger">4 clases</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-dumbbell text-indigo mr-2" style="color:#6366F1"></i> Gimnasio</span>
                                <span class="badge" style="background:#6366F1">Abierto</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Resumen mensual -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-2" style="color: <?= $moduloColor ?>"></i>
                            Resumen Mensual
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Clases realizadas</span>
                            <strong class="text-success">342</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Asistencia promedio</span>
                            <strong style="color: <?= $moduloColor ?>">87%</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Nuevos inscritos</span>
                            <strong class="text-info">28</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Ingresos totales</span>
                            <strong class="text-success">$18,450</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Calendario de espacios -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-th mr-2" style="color: <?= $moduloColor ?>"></i>
                            Uso de Instalaciones Hoy
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <thead style="background: <?= $moduloColor ?>15;">
                                <tr>
                                    <th>Espacio</th>
                                    <th class="text-center">08:00</th>
                                    <th class="text-center">10:00</th>
                                    <th class="text-center">12:00</th>
                                    <th class="text-center">14:00</th>
                                    <th class="text-center">16:00</th>
                                    <th class="text-center">18:00</th>
                                    <th class="text-center">20:00</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="fas fa-futbol text-success mr-2"></i> Cancha Fútbol 1</td>
                                    <td class="text-center bg-success text-white">Escuela</td>
                                    <td class="text-center bg-success text-white">Escuela</td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center bg-primary text-white">Reserva</td>
                                    <td class="text-center bg-primary text-white">Reserva</td>
                                    <td class="text-center bg-warning">Liga</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-swimmer text-info mr-2"></i> Piscina Principal</td>
                                    <td class="text-center bg-info text-white">Natación</td>
                                    <td class="text-center bg-info text-white">Natación</td>
                                    <td class="text-center bg-info text-white">Libre</td>
                                    <td class="text-center bg-info text-white">Libre</td>
                                    <td class="text-center bg-info text-white">Clases</td>
                                    <td class="text-center bg-info text-white">Clases</td>
                                    <td class="text-center bg-info text-white">Libre</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-basketball-ball mr-2" style="color:#F97316"></i> Cancha Basket</td>
                                    <td class="text-center"></td>
                                    <td class="text-center" style="background:#F97316;color:white">Entreno</td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center" style="background:#F97316;color:white">Escuela</td>
                                    <td class="text-center" style="background:#F97316;color:white">Escuela</td>
                                    <td class="text-center bg-warning">Partido</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-hand-rock text-danger mr-2"></i> Dojo</td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center bg-danger text-white">Karate</td>
                                    <td class="text-center bg-danger text-white">TKD</td>
                                    <td class="text-center bg-danger text-white">Judo</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
