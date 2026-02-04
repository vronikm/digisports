<?php
/**
 * Vista: Reporte de Facturas
 * Reporte detallado con filtros
 */
?>

<div class="container-fluid my-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">Reporte de Facturas</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= url('reportes', 'reporte', 'exportarCSV', ['tipo' => 'facturas', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin]) ?>"
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
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">-- Todos --</option>
                        <option value="BORRADOR" <?= $estado === 'BORRADOR' ? 'selected' : '' ?>>Borrador</option>
                        <option value="EMITIDA" <?= $estado === 'EMITIDA' ? 'selected' : '' ?>>Emitida</option>
                        <option value="PAGADA" <?= $estado === 'PAGADA' ? 'selected' : '' ?>>Pagada</option>
                        <option value="ANULADA" <?= $estado === 'ANULADA' ? 'selected' : '' ?>>Anulada</option>
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
    
    <!-- Tabla de Facturas -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Pagado</th>
                        <th class="text-end">Saldo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($facturas)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No hay facturas en este período
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($facturas as $factura): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($factura['numero_factura']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($factura['nombre_cliente']) ?></td>
                                <td><?= date('d/m/Y', strtotime($factura['fecha_emision'])) ?></td>
                                <td class="text-end">$<?= number_format($factura['total'], 2) ?></td>
                                <td class="text-end">$<?= number_format($factura['total_pagado'], 2) ?></td>
                                <td class="text-end text-warning">
                                    $<?= number_format($factura['total'] - $factura['total_pagado'], 2) ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $factura['estado'] === 'EMITIDA' ? 'warning' :
                                        ($factura['estado'] === 'PAGADA' ? 'success' :
                                        ($factura['estado'] === 'BORRADOR' ? 'secondary' : 'danger'))
                                    ?>">
                                        <?= htmlspecialchars($factura['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= url('facturacion', 'factura', 'ver', ['id' => $factura['factura_id']]) ?>"
                                       class="btn btn-sm btn-outline-info">
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
                    <a class="page-link" href="<?= url('reportes', 'reporte', 'facturas', [
                        'pagina' => 1, 'estado' => $estado, 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin
                    ]) ?>">Primera</a>
                </li>
                <li class="page-item <?= $pagina === 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('reportes', 'reporte', 'facturas', [
                        'pagina' => max(1, $pagina - 1), 'estado' => $estado, 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin
                    ]) ?>">Anterior</a>
                </li>
                
                <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                    <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('reportes', 'reporte', 'facturas', [
                            'pagina' => $i, 'estado' => $estado, 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin
                        ]) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?= $pagina === $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('reportes', 'reporte', 'facturas', [
                        'pagina' => min($totalPaginas, $pagina + 1), 'estado' => $estado, 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin
                    ]) ?>">Siguiente</a>
                </li>
                <li class="page-item <?= $pagina === $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('reportes', 'reporte', 'facturas', [
                        'pagina' => $totalPaginas, 'estado' => $estado, 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin
                    ]) ?>">Última</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    
    <div class="text-center mt-3 text-muted small">
        Mostrando <?= count($facturas) ?> de <?= $totalRegistros ?> registros
    </div>
</div>
