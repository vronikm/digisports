<?php
/**
 * DigiSports Ajedrez - Vista Dashboard
 */

$kpis = $kpis ?? [];
$topPlayers = $topPlayers ?? [];
$moduloColor = $modulo_actual['color'] ?? '#1F2937';
$moduloIcono = $modulo_actual['icono'] ?? 'fas fa-chess';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?> mr-2" style="color: <?= $moduloColor ?>"></i>
                    <?= $modulo_actual['nombre_personalizado'] ?? $modulo_actual['nombre'] ?? 'Dashboard Ajedrez' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right quick-actions">
                    <a href="<?= url('ajedrez', 'partida', 'crear') ?>" class="btn" style="background: <?= $moduloColor ?>; color: white;">
                        <i class="fas fa-plus mr-1"></i> Nueva Partida
                    </a>
                    <a href="<?= url('ajedrez', 'torneo', 'crear') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-trophy mr-1"></i> Nuevo Torneo
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
            <!-- Ranking de jugadores -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0 d-flex align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-trophy text-warning mr-2"></i>
                            Top 10 Jugadores
                        </h3>
                        <div class="ml-auto">
                            <span class="badge badge-dark">Rating ELO</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Jugador</th>
                                    <th class="text-center">Rating</th>
                                    <th class="text-center">P-G-E</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-warning">1</span></td>
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=CM&background=fbbf24&color=fff&size=32" class="rounded-circle mr-2" width="32">
                                        <strong>Carlos Moreno</strong>
                                    </td>
                                    <td class="text-center"><strong class="text-primary">1850</strong></td>
                                    <td class="text-center text-muted">45-32-8</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-secondary">2</span></td>
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=LV&background=94a3b8&color=fff&size=32" class="rounded-circle mr-2" width="32">
                                        <strong>Laura Vega</strong>
                                    </td>
                                    <td class="text-center"><strong class="text-primary">1780</strong></td>
                                    <td class="text-center text-muted">38-28-6</td>
                                </tr>
                                <tr>
                                    <td><span class="badge" style="background:#cd7f32;color:white">3</span></td>
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=MP&background=cd7f32&color=fff&size=32" class="rounded-circle mr-2" width="32">
                                        <strong>Miguel Paz</strong>
                                    </td>
                                    <td class="text-center"><strong class="text-primary">1720</strong></td>
                                    <td class="text-center text-muted">42-25-12</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-light">4</span></td>
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=SR&background=e5e7eb&color=374151&size=32" class="rounded-circle mr-2" width="32">
                                        Sofía Reyes
                                    </td>
                                    <td class="text-center"><strong class="text-primary">1680</strong></td>
                                    <td class="text-center text-muted">35-22-10</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-light">5</span></td>
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=DA&background=e5e7eb&color=374151&size=32" class="rounded-circle mr-2" width="32">
                                        Diego Alarcón
                                    </td>
                                    <td class="text-center"><strong class="text-primary">1650</strong></td>
                                    <td class="text-center text-muted">30-20-8</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-center">
                        <a href="<?= url('ajedrez', 'ranking', 'index') ?>" class="text-dark">
                            Ver ranking completo <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Partidas activas -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-chess-board text-primary mr-2"></i>
                            Partidas en Curso
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Partida 1 -->
                        <div class="callout callout-dark mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="mr-2">♔</span>
                                    <strong>Moreno</strong> vs <strong>Vega</strong>
                                    <span class="ml-2">♚</span>
                                </div>
                                <div>
                                    <span class="badge badge-success">En vivo</span>
                                </div>
                            </div>
                            <div class="mt-2 text-muted">
                                <small><i class="fas fa-clock mr-1"></i> Tiempo: 15+10 | Movimiento 24</small>
                            </div>
                        </div>
                        
                        <!-- Partida 2 -->
                        <div class="callout callout-secondary mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="mr-2">♔</span>
                                    <strong>Paz</strong> vs <strong>Reyes</strong>
                                    <span class="ml-2">♚</span>
                                </div>
                                <div>
                                    <span class="badge badge-success">En vivo</span>
                                </div>
                            </div>
                            <div class="mt-2 text-muted">
                                <small><i class="fas fa-clock mr-1"></i> Tiempo: 30+0 | Movimiento 18</small>
                            </div>
                        </div>
                        
                        <!-- Torneo activo -->
                        <div class="alert alert-dark">
                            <i class="fas fa-trophy mr-2"></i>
                            <strong>Torneo Mensual - Ronda 3</strong>
                            <br>
                            <small class="text-muted">8 partidas restantes | Finaliza: 20:00</small>
                        </div>
                    </div>
                </div>
                
                <!-- Simultáneas -->
                <div class="card">
                    <div class="card-header border-0 bg-gradient-dark text-white">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-2"></i>
                            Próxima Simultánea
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <h4>GM Invitado: Juan Carlos Torres</h4>
                        <p class="text-muted mb-2">
                            <i class="fas fa-calendar mr-1"></i> Sábado 15 Feb | 10:00
                        </p>
                        <p>
                            <i class="fas fa-chess-board mr-1"></i> 20 tableros disponibles
                        </p>
                        <a href="<?= url('ajedrez', 'simultanea', 'inscribir') ?>" class="btn" style="background: <?= $moduloColor ?>; color: white;">
                            Inscribirse
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
