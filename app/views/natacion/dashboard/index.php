<?php
/**
 * DigiSports Natación - Vista Dashboard
 */

$kpis = $kpis ?? [];
$moduloColor = $modulo_actual['color'] ?? '#0EA5E9';
$moduloIcono = $modulo_actual['icono'] ?? 'fas fa-swimmer';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?> mr-2" style="color: <?= $moduloColor ?>"></i>
                    <?= $modulo_actual['nombre_personalizado'] ?? $modulo_actual['nombre'] ?? 'Dashboard Natación' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right quick-actions">
                    <a href="<?= url('natacion', 'alumno', 'crear') ?>" class="btn" style="background: <?= $moduloColor ?>; color: white;">
                        <i class="fas fa-user-plus mr-1"></i> Nuevo Alumno
                    </a>
                    <a href="<?= url('natacion', 'horario', 'index') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-clock mr-1"></i> Horarios
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
        
        <!-- Contenido específico de Natación -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-water text-info mr-2"></i>
                            Clases del Día
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Piscina/Carril</th>
                                    <th>Nivel</th>
                                    <th>Instructor</th>
                                    <th>Alumnos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>08:00</strong></td>
                                    <td>Piscina Principal - Carriles 1-2</td>
                                    <td><span class="badge badge-success">Principiante</span></td>
                                    <td>María López</td>
                                    <td>12/15</td>
                                </tr>
                                <tr>
                                    <td><strong>09:00</strong></td>
                                    <td>Piscina Principal - Carriles 3-4</td>
                                    <td><span class="badge badge-warning">Intermedio</span></td>
                                    <td>Carlos Ruiz</td>
                                    <td>10/12</td>
                                </tr>
                                <tr>
                                    <td><strong>10:00</strong></td>
                                    <td>Piscina Auxiliar</td>
                                    <td><span class="badge badge-info">Bebés</span></td>
                                    <td>Ana Martínez</td>
                                    <td>8/8</td>
                                </tr>
                                <tr>
                                    <td><strong>16:00</strong></td>
                                    <td>Piscina Principal - Todos</td>
                                    <td><span class="badge badge-danger">Avanzado</span></td>
                                    <td>Pedro Sánchez</td>
                                    <td>6/10</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Niveles -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">Alumnos por Nivel</h3>
                    </div>
                    <div class="card-body">
                        <div class="progress-group">
                            <span class="progress-text">Principiante</span>
                            <span class="float-right"><b>65</b>/156</span>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 42%"></div>
                            </div>
                        </div>
                        <div class="progress-group">
                            <span class="progress-text">Intermedio</span>
                            <span class="float-right"><b>52</b>/156</span>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: 33%"></div>
                            </div>
                        </div>
                        <div class="progress-group">
                            <span class="progress-text">Avanzado</span>
                            <span class="float-right"><b>25</b>/156</span>
                            <div class="progress">
                                <div class="progress-bar bg-danger" style="width: 16%"></div>
                            </div>
                        </div>
                        <div class="progress-group">
                            <span class="progress-text">Bebés (Estimulación)</span>
                            <span class="float-right"><b>14</b>/156</span>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: 9%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Piscinas Status -->
                <div class="card bg-gradient-info">
                    <div class="card-body">
                        <h5 class="text-white">🏊 Estado Piscinas</h5>
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="text-white-50 small">Principal</div>
                                <div class="text-white font-weight-bold">
                                    <i class="fas fa-check-circle"></i> Operativa
                                </div>
                                <div class="text-white-50 small">28°C - pH 7.2</div>
                            </div>
                            <div class="col-6">
                                <div class="text-white-50 small">Auxiliar</div>
                                <div class="text-white font-weight-bold">
                                    <i class="fas fa-check-circle"></i> Operativa
                                </div>
                                <div class="text-white-50 small">30°C - pH 7.4</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
