<?php
/**
 * DigiSports Artes Marciales - Vista Dashboard
 */

$kpis = $kpis ?? [];
$disciplinas = $disciplinas ?? [];
$moduloColor = $modulo_actual['color'] ?? '#DC2626';
$moduloIcono = $modulo_actual['icono'] ?? 'fas fa-hand-rock';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?> mr-2" style="color: <?= $moduloColor ?>"></i>
                    <?= $modulo_actual['nombre_personalizado'] ?? $modulo_actual['nombre'] ?? 'Dashboard Artes Marciales' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right quick-actions">
                    <a href="<?= url('artes_marciales', 'alumno', 'crear') ?>" class="btn" style="background: <?= $moduloColor ?>; color: white;">
                        <i class="fas fa-user-plus mr-1"></i> Nuevo Alumno
                    </a>
                    <a href="<?= url('artes_marciales', 'examen', 'crear') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-clipboard-check mr-1"></i> Programar Examen
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
        
        <!-- Disciplinas -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-yin-yang mr-2"></i>
                            Disciplinas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($disciplinas as $disc): ?>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="card mb-0" style="border-left: 4px solid <?= $disc['color'] ?>">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3" style="font-size: 2.5rem;">
                                                <?= $disc['icono'] ?>
                                            </div>
                                            <div>
                                                <h5 class="mb-0"><?= $disc['nombre'] ?></h5>
                                                <span class="text-muted"><?= $disc['alumnos'] ?> alumnos</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenido específico -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-day text-danger mr-2"></i>
                            Clases de Hoy
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Disciplina</th>
                                    <th>Nivel</th>
                                    <th>Instructor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>16:00</strong></td>
                                    <td>🥋 Karate</td>
                                    <td><span class="badge" style="background: #fef3c7; color: #d97706;">Amarillo</span></td>
                                    <td>Sensei García</td>
                                </tr>
                                <tr>
                                    <td><strong>17:00</strong></td>
                                    <td>🦶 Taekwondo</td>
                                    <td><span class="badge" style="background: #dbeafe; color: #2563eb;">Azul</span></td>
                                    <td>Master Kim</td>
                                </tr>
                                <tr>
                                    <td><strong>18:00</strong></td>
                                    <td>🤼 Judo</td>
                                    <td><span class="badge" style="background: #fee2e2; color: #dc2626;">Rojo</span></td>
                                    <td>Sensei Tanaka</td>
                                </tr>
                                <tr>
                                    <td><strong>19:00</strong></td>
                                    <td>🤸 Jiu-Jitsu</td>
                                    <td><span class="badge" style="background: #d1fae5; color: #059669;">Verde</span></td>
                                    <td>Prof. Silva</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <!-- Próximos exámenes -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-award text-warning mr-2"></i>
                            Próximos Exámenes de Grado
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="callout callout-warning">
                            <h5>Examen Karate - Cinturón Amarillo</h5>
                            <p class="mb-0"><i class="fas fa-calendar mr-1"></i> 15 Feb 2026 | <i class="fas fa-users mr-1"></i> 12 aspirantes</p>
                        </div>
                        <div class="callout callout-info">
                            <h5>Examen Taekwondo - Cinturón Azul</h5>
                            <p class="mb-0"><i class="fas fa-calendar mr-1"></i> 22 Feb 2026 | <i class="fas fa-users mr-1"></i> 8 aspirantes</p>
                        </div>
                        <div class="callout callout-danger">
                            <h5>Examen Jiu-Jitsu - Faixa Roxa</h5>
                            <p class="mb-0"><i class="fas fa-calendar mr-1"></i> 01 Mar 2026 | <i class="fas fa-users mr-1"></i> 3 aspirantes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
