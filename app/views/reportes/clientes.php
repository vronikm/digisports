<?php
/**
 * Vista: Reporte de Clientes
 * Análisis de clientes, facturas y pagos
 */
?>

<div class="container-fluid my-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">Reporte de Clientes</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= url('reportes', 'reporte', 'exportarCSV', ['tipo' => 'clientes']) ?>"
               class="btn btn-outline-success">
                <i class="fas fa-download"></i> Exportar CSV
            </a>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Buscar Cliente</label>
                    <input type="text" name="busqueda" class="form-control" placeholder="Nombre o RUC" 
                           value="<?= htmlspecialchars($busqueda) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ordenar por</label>
                    <select name="ordenar" class="form-select">
                        <option value="total_facturado" <?= $ordenar === 'total_facturado' ? 'selected' : '' ?>>
                            Total Facturado
                        </option>
                        <option value="total_pagado" <?= $ordenar === 'total_pagado' ? 'selected' : '' ?>>
                            Total Pagado
                        </option>
                        <option value="saldo_pendiente" <?= $ordenar === 'saldo_pendiente' ? 'selected' : '' ?>>
                            Saldo Pendiente
                        </option>
                        <option value="num_facturas" <?= $ordenar === 'num_facturas' ? 'selected' : '' ?>>
                            Num. Facturas
                        </option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Total Clientes</div>
                    <div class="h4 mt-2"><?= $estadisticas['total_clientes'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Total Facturado</div>
                    <div class="h4 mt-2">$<?= number_format($estadisticas['total_facturado'], 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Promedio por Cliente</div>
                    <div class="h4 mt-2">$<?= number_format($estadisticas['promedio_cliente'], 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Saldo Pendiente Total</div>
                    <div class="h4 mt-2 text-warning">$<?= number_format($estadisticas['saldo_total'], 2) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabla de Clientes -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cliente</th>
                        <th>RUC</th>
                        <th class="text-end">Num. Facturas</th>
                        <th class="text-end">Total Facturado</th>
                        <th class="text-end">Total Pagado</th>
                        <th class="text-end">Saldo Pendiente</th>
                        <th>% Cobranza</th>
                        <th>Última Factura</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No hay clientes registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($cliente['nombre']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($cliente['ruc']) ?></td>
                                <td class="text-end"><?= $cliente['num_facturas'] ?></td>
                                <td class="text-end">$<?= number_format($cliente['total_facturado'], 2) ?></td>
                                <td class="text-end">$<?= number_format($cliente['total_pagado'], 2) ?></td>
                                <td class="text-end text-warning">
                                    $<?= number_format($cliente['saldo_pendiente'], 2) ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $cliente['porcentaje_cobranza'] >= 70 ? 'success' : 'warning' ?>">
                                        <?= number_format($cliente['porcentaje_cobranza'], 1) ?>%
                                    </span>
                                </td>
                                <td>
                                    <?php if ($cliente['ultima_factura']): ?>
                                        <?= date('d/m/Y', strtotime($cliente['ultima_factura'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= url('reportes', 'reporte', 'clienteDetalle', ['cliente_id' => $cliente['cliente_id']]) ?>"
                                       class="btn btn-sm btn-outline-info" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Paginación -->
    <?php if ($totalPaginas > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $pagina === 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('reportes', 'reporte', 'clientes', [
                        'pagina' => 1, 'busqueda' => $busqueda, 'ordenar' => $ordenar
                    ]) ?>">Primera</a>
                </li>
                <li class="page-item <?= $pagina === 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('reportes', 'reporte', 'clientes', [
                        'pagina' => max(1, $pagina - 1), 'busqueda' => $busqueda, 'ordenar' => $ordenar
                    ]) ?>">Anterior</a>
                </li>
                
                <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                    <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('reportes', 'reporte', 'clientes', [
                            'pagina' => $i, 'busqueda' => $busqueda, 'ordenar' => $ordenar
                        ]) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?= $pagina === $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('reportes', 'reporte', 'clientes', [
                        'pagina' => min($totalPaginas, $pagina + 1), 'busqueda' => $busqueda, 'ordenar' => $ordenar
                    ]) ?>">Siguiente</a>
                </li>
                <li class="page-item <?= $pagina === $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('reportes', 'reporte', 'clientes', [
                        'pagina' => $totalPaginas, 'busqueda' => $busqueda, 'ordenar' => $ordenar
                    ]) ?>">Última</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    
    <div class="text-center mt-3 text-muted small">
        Mostrando <?= count($clientes) ?> de <?= $totalRegistros ?> registros
    </div>
</div>
