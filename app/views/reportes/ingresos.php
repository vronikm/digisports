<?php
/**
 * Vista: Reporte de Ingresos
 * Análisis de ingresos por período y forma de pago
 */
?>

<div class="container-fluid my-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">Reporte de Ingresos</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= url('reportes', 'reporte', 'exportarCSV', ['tipo' => 'ingresos', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin]) ?>"
               class="btn btn-outline-success">
                <i class="fas fa-download"></i> Exportar CSV
            </a>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Forma de Pago</label>
                    <select name="forma_pago_id" class="form-select">
                        <option value="">-- Todas --</option>
                        <?php foreach ($formasPago as $fp): ?>
                            <option value="<?= $fp['forma_pago_id'] ?>" 
                                <?= $forma_pago_id == $fp['forma_pago_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($fp['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-muted small">Total de Ingresos</div>
                    <div class="h4 mt-2">$<?= number_format($resumen['total_ingresos'], 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-muted small">Total Pagado</div>
                    <div class="h4 mt-2">$<?= number_format($resumen['total_pagado'], 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-muted small">Promedio Diario</div>
                    <div class="h4 mt-2">$<?= number_format($resumen['promedio_diario'], 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-muted small">Num. Transacciones</div>
                    <div class="h4 mt-2"><?= $resumen['num_transacciones'] ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabla de Ingresos por Día -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Ingresos por Día</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Día Semana</th>
                        <th class="text-end">Num. Facturas</th>
                        <th class="text-end">Total Facturado</th>
                        <th class="text-end">Total Pagado</th>
                        <th class="text-end">% Cobranza</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ingresosPorDia)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No hay datos en este período
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ingresosPorDia as $dia): ?>
                            <tr>
                                <td><strong><?= date('d/m/Y', strtotime($dia['fecha'])) ?></strong></td>
                                <td><?= strftime('%A', strtotime($dia['fecha'])) ?></td>
                                <td class="text-end"><?= $dia['num_facturas'] ?></td>
                                <td class="text-end">$<?= number_format($dia['total_facturado'], 2) ?></td>
                                <td class="text-end">$<?= number_format($dia['total_pagado'], 2) ?></td>
                                <td class="text-end">
                                    <span class="badge bg-<?= $dia['porcentaje_cobranza'] >= 70 ? 'success' : 'warning' ?>">
                                        <?= number_format($dia['porcentaje_cobranza'], 1) ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Gráfico: Ingresos por Forma de Pago -->
    <?php if (!empty($ingresosPorFormaPago)): ?>
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Ingresos por Forma de Pago</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <canvas id="chartFormaPago" height="100"></canvas>
                    </div>
                    <div class="col-md-4">
                        <div class="mt-3">
                            <?php foreach ($ingresosPorFormaPago as $fp): ?>
                                <div class="mb-2">
                                    <small class="text-muted"><?= htmlspecialchars($fp['nombre_forma_pago']) ?></small>
                                    <div class="d-flex justify-content-between">
                                        <strong>$<?= number_format($fp['total'], 2) ?></strong>
                                        <span class="text-muted"><?= number_format($fp['porcentaje'], 1) ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
            const ctxFormaPago = document.getElementById('chartFormaPago').getContext('2d');
            new Chart(ctxFormaPago, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_column($ingresosPorFormaPago, 'nombre_forma_pago')) ?>,
                    datasets: [{
                        data: <?= json_encode(array_column($ingresosPorFormaPago, 'total')) ?>,
                        backgroundColor: [
                            '#007bff', '#28a745', '#ffc107', '#17a2b8', '#dc3545', '#6f42c1'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        </script>
    <?php endif; ?>
</div>
