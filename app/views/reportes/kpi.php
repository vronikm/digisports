<?php
/**
 * Vista: Dashboard de KPIs
 * Indicadores clave con período de comparación
 */

// Valores por defecto para evitar errores
$kpis_actuales = $kpis_actuales ?? [];
$tendencia_ingresos = $tendencia_ingresos ?? ['actual' => 0, 'variacion' => 0, 'positiva' => true];
$tendencia_facturas = $tendencia_facturas ?? ['actual' => 0, 'variacion' => 0, 'positiva' => true];
$tendencia_cobranza = $tendencia_cobranza ?? ['actual' => 0, 'variacion' => 0, 'positiva' => true];
$alertas = $alertas ?? [];
$periodo = $periodo ?? 'mes';
$comparar = $comparar ?? 'no';

// Extraer valores con defaults
$total_ingresos = $kpis_actuales['total_ingresos'] ?? 0;
$num_facturas = $kpis_actuales['num_facturas'] ?? 0;
$tasa_cobranza = $kpis_actuales['tasa_cobranza'] ?? 0;
$saldo_pendiente = $kpis_actuales['saldo_pendiente'] ?? 0;
$monto_promedio = $kpis_actuales['monto_promedio'] ?? 0;
$dias_promedio_pago = $kpis_actuales['dias_promedio_pago'] ?? 0;
?>

<div class="container-fluid my-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">
                <i class="fas fa-chart-bar text-purple"></i> Dashboard de Indicadores
            </h1>
        </div>
        <div class="col-md-6 text-right">
            <div class="btn-group" role="group">
                <a href="<?= url('reportes', 'kpi', 'index', ['periodo' => 'semana']) ?>"
                   class="btn btn-outline-primary <?= $periodo === 'semana' ? 'active' : '' ?>">
                    Semana
                </a>
                <a href="<?= url('reportes', 'kpi', 'index', ['periodo' => 'mes']) ?>"
                   class="btn btn-outline-primary <?= $periodo === 'mes' ? 'active' : '' ?>">
                    Mes
                </a>
                <a href="<?= url('reportes', 'kpi', 'index', ['periodo' => 'trimestre']) ?>"
                   class="btn btn-outline-primary <?= $periodo === 'trimestre' ? 'active' : '' ?>">
                    Trimestre
                </a>
                <a href="<?= url('reportes', 'kpi', 'index', ['periodo' => 'año']) ?>"
                   class="btn btn-outline-primary <?= $periodo === 'año' ? 'active' : '' ?>">
                    Año
                </a>
            </div>
        </div>
    </div>
    
    <!-- Alertas -->
    <?php if (!empty($alertas)): ?>
        <div class="row mb-4">
            <?php foreach ($alertas as $alerta): ?>
                <div class="col-md-12 mb-2">
                    <div class="alert alert-<?= $alerta['tipo'] === 'danger' ? 'danger' : 'warning' ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?= $alerta['tipo'] === 'danger' ? 'exclamation-circle' : 'exclamation-triangle' ?>"></i>
                        <strong><?= htmlspecialchars($alerta['titulo']) ?></strong>
                        <?= htmlspecialchars($alerta['mensaje']) ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- KPIs Principales -->
    <div class="row mb-4">
        <!-- Total Ingresos -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Ingresos</span>
                    <span class="info-box-number">$<?= number_format($total_ingresos, 2) ?></span>
                    <?php if ($tendencia_ingresos['variacion'] != 0): ?>
                        <div class="progress-description">
                            <i class="fas fa-arrow-<?= $tendencia_ingresos['positiva'] ? 'up' : 'down' ?>"></i>
                            <?= number_format(abs($tendencia_ingresos['variacion']), 1) ?>% vs anterior
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Facturas Emitidas -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Facturas Emitidas</span>
                    <span class="info-box-number"><?= $num_facturas ?></span>
                    <?php if ($tendencia_facturas['variacion'] != 0): ?>
                        <div class="progress-description">
                            <i class="fas fa-arrow-<?= $tendencia_facturas['positiva'] ? 'up' : 'down' ?>"></i>
                            <?= number_format(abs($tendencia_facturas['variacion']), 1) ?>% vs anterior
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Tasa de Cobranza -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="info-box <?= $tasa_cobranza >= 70 ? 'bg-primary' : 'bg-warning' ?>">
                <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Tasa de Cobranza</span>
                    <span class="info-box-number"><?= number_format($tasa_cobranza, 1) ?>%</span>
                    <div class="progress-description">
                        Meta: 70% <?= $tasa_cobranza >= 70 ? '✓' : '✗' ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Saldo Pendiente -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Saldo Pendiente</span>
                    <span class="info-box-number">$<?= number_format($saldo_pendiente, 2) ?></span>
                    <div class="progress-description">
                        Por cobrar
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- KPIs Secundarios -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Monto Promedio por Factura</div>
                            <h4 class="mt-2">$<?= number_format($monto_promedio, 2) ?></h4>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-calculator fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Días Promedio de Pago</div>
                            <h4 class="mt-2"><?= $dias_promedio_pago ?> días</h4>
                        </div>
                        <div class="<?= $dias_promedio_pago <= 30 ? 'text-success' : 'text-warning' ?>">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Período Analizado</div>
                            <h4 class="mt-2 text-capitalize"><?= $periodo ?></h4>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mensaje informativo si no hay datos -->
    <?php if (empty($kpis_actuales)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h5>Sin datos para el período seleccionado</h5>
                    <p class="mb-0">Los indicadores se calcularán cuando existan facturas y pagos registrados en el sistema.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Acciones -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cog"></i> Acciones Rápidas</h3>
                </div>
                <div class="card-body">
                    <a href="<?= url('facturacion', 'comprobante') ?>" class="btn btn-warning mr-2">
                        <i class="fas fa-file-invoice"></i> Ver Facturas
                    </a>
                    <a href="<?= url('reservas', 'reserva') ?>" class="btn btn-success mr-2">
                        <i class="fas fa-calendar-check"></i> Ver Reservas
                    </a>
                    <a href="<?= url('reportes', 'kpi', 'index', ['comparar' => 'si', 'periodo' => $periodo]) ?>" class="btn btn-info">
                        <i class="fas fa-balance-scale"></i> Comparar con Período Anterior
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
