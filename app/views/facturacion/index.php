<?php
/**
 * Vista: Listado de Facturas
 * Muestra todas las facturas con filtros y acciones
 */
?>

<div class="container-fluid my-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">Gestión de Facturas</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= url('facturacion', 'factura', 'crear') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Factura
            </a>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="estado" class="form-select">
                        <option value="">-- Todos los estados --</option>
                        <option value="BORRADOR" <?= $estado === 'BORRADOR' ? 'selected' : '' ?>>Borrador</option>
                        <option value="EMITIDA" <?= $estado === 'EMITIDA' ? 'selected' : '' ?>>Emitida</option>
                        <option value="PAGADA" <?= $estado === 'PAGADA' ? 'selected' : '' ?>>Pagada</option>
                        <option value="ANULADA" <?= $estado === 'ANULADA' ? 'selected' : '' ?>>Anulada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100">
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
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Fecha Emisión</th>
                        <th>Total</th>
                        <th>Pagado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($facturas)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">No hay facturas disponibles</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($facturas as $factura): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($factura['numero_factura']) ?></strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars($factura['nombre_cliente']) ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($factura['fecha_emision'])) ?>
                                </td>
                                <td>
                                    $<?= number_format($factura['total'], 2) ?>
                                </td>
                                <td>
                                    $<?= number_format($factura['total_pagado'] ?? 0, 2) ?>
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
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('facturacion', 'factura', 'ver', ['id' => $factura['factura_id']]) ?>"
                                           class="btn btn-outline-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($factura['estado'] === 'BORRADOR'): ?>
                                            <a href="<?= url('facturacion', 'factura', 'emitir', ['id' => $factura['factura_id']]) ?>"
                                               class="btn btn-outline-success" title="Emitir">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($factura['estado'] !== 'ANULADA' && $factura['estado'] !== 'PAGADA'): ?>
                                            <a href="<?= url('facturacion', 'factura', 'anular', ['id' => $factura['factura_id']]) ?>"
                                               class="btn btn-outline-danger"
                                               title="Anular"
                                               onclick="return confirm('¿Está seguro de anular esta factura?')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= url('facturacion', 'factura', 'pdf', ['id' => $factura['factura_id']]) ?>"
                                           class="btn btn-outline-secondary" title="PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </div>
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
                    <a class="page-link" href="<?= url('facturacion', 'factura', 'index', ['pagina' => 1, 'estado' => $estado]) ?>">
                        Primera
                    </a>
                </li>
                <li class="page-item <?= $pagina === 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('facturacion', 'factura', 'index', ['pagina' => max(1, $pagina - 1), 'estado' => $estado]) ?>">
                        Anterior
                    </a>
                </li>
                
                <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                    <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('facturacion', 'factura', 'index', ['pagina' => $i, 'estado' => $estado]) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?= $pagina === $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('facturacion', 'factura', 'index', ['pagina' => min($totalPaginas, $pagina + 1), 'estado' => $estado]) ?>">
                        Siguiente
                    </a>
                </li>
                <li class="page-item <?= $pagina === $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('facturacion', 'factura', 'index', ['pagina' => $totalPaginas, 'estado' => $estado]) ?>">
                        Última
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    
    <!-- Resumen -->
    <div class="row mt-4">
        <div class="col-md-12">
            <p class="text-muted text-center small">
                Mostrando <?= count($facturas) ?> de <?= $totalRegistros ?> registros
            </p>
        </div>
    </div>
</div>
