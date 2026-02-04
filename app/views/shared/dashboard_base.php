<?php
/**
 * Vista Dashboard Genérica para Módulos Deportivos
 * Reutilizable con diferentes KPIs y datos
 */

$kpis = $kpis ?? [];
$chartData = $chart_data ?? ['labels' => [], 'data' => []];
$moduloColor = $modulo_actual['color'] ?? '#3B82F6';
$moduloIcono = $modulo_actual['icono'] ?? 'fas fa-cube';
$moduloNombre = $modulo_actual['nombre_personalizado'] ?? $modulo_actual['nombre'] ?? 'Módulo';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?> mr-2" style="color: <?= $moduloColor ?>"></i>
                    Dashboard
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <span class="text-muted">
                        <i class="fas fa-clock mr-1"></i>
                        <?= date('d/m/Y H:i') ?>
                    </span>
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
        
        <!-- Additional Content (passed from controller) -->
        <?php if (isset($content_extra)): ?>
        <?= $content_extra ?>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-bolt text-warning mr-2"></i>
                            Acciones Rápidas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="#" class="btn btn-outline-primary btn-block py-3">
                                    <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                                    Nuevo Registro
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="#" class="btn btn-outline-success btn-block py-3">
                                    <i class="fas fa-calendar-plus fa-2x d-block mb-2"></i>
                                    Nueva Reserva
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="#" class="btn btn-outline-info btn-block py-3">
                                    <i class="fas fa-file-alt fa-2x d-block mb-2"></i>
                                    Ver Reportes
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="#" class="btn btn-outline-secondary btn-block py-3">
                                    <i class="fas fa-cog fa-2x d-block mb-2"></i>
                                    Configuración
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</section>
