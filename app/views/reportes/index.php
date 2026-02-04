<?php
/**
 * Vista: Dashboard de Reportes
 * Dashboard principal con KPIs y gráficos
 */
?>

<div class="container-fluid my-4">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h2"><i class="fas fa-chart-line"></i> Dashboard Financiero</h1>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group">
                <a href="?dias=7" class="btn btn-sm btn-outline-primary <?= $dias == 7 ? 'active' : '' ?>">
                    Esta Semana
                </a>
                <a href="?dias=30" class="btn btn-sm btn-outline-primary <?= $dias == 30 ? 'active' : '' ?>">
                    Este Mes
                </a>
                <a href="?dias=90" class="btn btn-sm btn-outline-primary <?= $dias == 90 ? 'active' : '' ?>">
                    Este Trimestre
                </a>
                <a href="?dias=365" class="btn btn-sm btn-outline-primary <?= $dias == 365 ? 'active' : '' ?>">
                    Este Año
                </a>
            </div>
        </div>
    </div>
    
    <!-- KPIs principales -->
    <div class="row mb-4">
        <!-- Total Ingresos -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Ingresos</p>
                            <h3 class="mb-0">$<?= number_format($kpis['total_ingresos'], 2) ?></h3>
                        </div>
                        <span class="badge bg-success rounded-circle p-3">
                            <i class="fas fa-dollar-sign"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Facturas -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Facturas Emitidas</p>
                            <h3 class="mb-0"><?= $kpis['total_facturas'] ?></h3>
                        </div>
                        <span class="badge bg-info rounded-circle p-3">
                            <i class="fas fa-file-invoice"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Facturas Pagadas -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Facturas Pagadas</p>
                            <h3 class="mb-0"><?= $kpis['facturas_pagadas'] ?></h3>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i>
                                <?= $kpis['tasa_cobranza'] ?>%
                            </small>
                        </div>
                        <span class="badge bg-success rounded-circle p-3">
                            <i class="fas fa-check-circle"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Saldo Pendiente -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Saldo Pendiente</p>
                            <h3 class="mb-0">$<?= number_format($kpis['saldo_pendiente'], 2) ?></h3>
                        </div>
                        <span class="badge bg-warning rounded-circle p-3">
                            <i class="fas fa-hourglass-end"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráficos -->
    <div class="row mb-4">
        <!-- Gráfico Ingresos -->
        <div class="col-lg-8 mb-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Ingresos por Día</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartIngresos" height="80"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Gráfico por Forma Pago -->
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Ingresos por Forma Pago</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartFormaPago" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <!-- Gráfico Estado Facturas -->
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Estado de Facturas</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartEstado" height="80"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Top Clientes -->
        <div class="col-lg-8 mb-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Top 5 Clientes</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th class="text-end">Facturas</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_clientes as $cliente): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cliente['nombre_cliente']) ?></td>
                                    <td class="text-end"><?= $cliente['cantidad_pagos'] ?></td>
                                    <td class="text-end">$<?= number_format($cliente['total_pagado'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Últimas Facturas -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Últimas Facturas</h5>
            <a href="<?= url('reportes', 'reporte', 'facturas') ?>" class="btn btn-sm btn-outline-primary">
                Ver todas
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Pagado</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimas_facturas as $factura): ?>
                        <tr>
                            <td>
                                <a href="<?= url('facturacion', 'factura', 'ver', ['id' => $factura['factura_id']]) ?>">
                                    <?= htmlspecialchars($factura['numero_factura']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($factura['nombre_cliente']) ?></td>
                            <td><?= date('d/m/Y', strtotime($factura['fecha_emision'])) ?></td>
                            <td class="text-end">$<?= number_format($factura['total'], 2) ?></td>
                            <td class="text-end">$<?= number_format($factura['total_pagado'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $factura['estado'] === 'EMITIDA' ? 'warning' :
                                    ($factura['estado'] === 'PAGADA' ? 'success' : 'secondary')
                                ?>">
                                    <?= htmlspecialchars($factura['estado']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
// Gráfico Ingresos
const ctxIngresos = document.getElementById('chartIngresos').getContext('2d');
new Chart(ctxIngresos, {
    type: 'line',
    data: {
        labels: <?= json_encode($grafico_ingresos['labels']) ?>,
        datasets: [{
            label: 'Ingresos Diarios',
            data: <?= json_encode($grafico_ingresos['data']) ?>,
            borderColor: '#27ae60',
            backgroundColor: 'rgba(39, 174, 96, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});

// Gráfico Forma Pago
const ctxFormaPago = document.getElementById('chartFormaPago').getContext('2d');
new Chart(ctxFormaPago, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($grafico_forma_pago['labels']) ?>,
        datasets: [{
            data: <?= json_encode($grafico_forma_pago['data']) ?>,
            backgroundColor: <?= json_encode($grafico_forma_pago['colors']) ?>
        }]
    },
    options: {
        responsive: true
    }
});

// Gráfico Estado Facturas
const ctxEstado = document.getElementById('chartEstado').getContext('2d');
new Chart(ctxEstado, {
    type: 'pie',
    data: {
        labels: <?= json_encode($grafico_estado['labels']) ?>,
        datasets: [{
            data: <?= json_encode($grafico_estado['data']) ?>,
            backgroundColor: <?= json_encode($grafico_estado['colors']) ?>
        }]
    },
    options: {
        responsive: true
    }
});
</script>
