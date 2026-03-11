<?php
/**
 * Vista: Detalles de Factura
 * Muestra detalles completos, líneas, y pagos
 */
?>

<div class="container-fluid my-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">
                <i class="fas fa-file-invoice-dollar"></i>
                Factura <?= htmlspecialchars($factura['fac_numero'] ?? '') ?>
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= url('facturacion', 'factura', 'index') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="<?= url('facturacion', 'factura', 'pdf', ['id' => $factura['fac_id']]) ?>" 
               class="btn btn-outline-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>
    
    <!-- Información General -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h5 class="mb-0">Información General</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong>Número Factura:</strong><br>
                        <?= htmlspecialchars($factura['fac_numero'] ?? '') ?>
                    </p>
                    <p class="mb-2">
                        <strong>Estado:</strong><br>
                        <span class="badge bg-<?= 
                            ($factura['fac_estado'] ?? '') === 'EMITIDA' ? 'warning' :
                            (($factura['fac_estado'] ?? '') === 'PAGADA' ? 'success' :
                            (($factura['fac_estado'] ?? '') === 'BORRADOR' ? 'secondary' : 'danger'))
                        ?>">
                            <?= htmlspecialchars($factura['fac_estado'] ?? '') ?>
                        </span>
                    </p>
                    <p class="mb-0">
                        <strong>Cliente:</strong><br>
                        <?= htmlspecialchars($factura['nombre_cliente'] ?? 'Cliente no especificado') ?>
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-2">
                        <strong>Fecha Emisión:</strong><br>
                        <?= !empty($factura['fac_fecha_emision']) ? date('d/m/Y H:i', strtotime($factura['fac_fecha_emision'])) : 'N/A' ?>
                    </p>
                    <p class="mb-2">
                        <strong>Fecha Vencimiento:</strong><br>
                        <?= !empty($factura['fac_fecha_vencimiento']) ? date('d/m/Y', strtotime($factura['fac_fecha_vencimiento'])) : 'N/A' ?>
                    </p>
                    <p class="mb-0">
                        <strong>Forma de Pago:</strong><br>
                        <?= htmlspecialchars($factura['forma_pago_nombre'] ?? 'N/A') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Líneas de Factura -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h5 class="mb-0">Líneas de Factura</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Descripción</th>
                        <th class="text-end" style="width: 100px;">Cantidad</th>
                        <th class="text-end" style="width: 120px;">Precio Unit.</th>
                        <th class="text-end" style="width: 120px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lineas as $linea): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($linea['lin_descripcion']) ?>
                            </td>
                            <td class="text-end">
                                <?= htmlspecialchars($linea['lin_cantidad']) ?>
                            </td>
                            <td class="text-end">
                                $<?= number_format($linea['lin_precio_unitario'], 2) ?>
                            </td>
                            <td class="text-end">
                                <strong>$<?= number_format($linea['lin_total'], 2) ?></strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Totales -->
    <div class="row mb-3">
        <div class="col-md-6 ms-auto">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>$<?= number_format($factura['fac_subtotal'] ?? 0, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>IVA (15%):</span>
                        <span>$<?= number_format($factura['fac_iva'] ?? 0, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Descuento:</span>
                        <span>$<?= number_format($factura['fac_descuento'] ?? 0, 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong class="text-primary" style="font-size: 1.25rem;">
                            $<?= number_format($factura['fac_total'] ?? 0, 2) ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pagos Registrados -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pagos Registrados</h5>
                <?php if (($factura['fac_estado'] ?? '') !== 'PAGADA' && ($factura['fac_estado'] ?? '') !== 'ANULADA'): ?>
                    <a href="<?= url('facturacion', 'pago', 'crear', ['factura_id' => $factura['fac_id']]) ?>"
                       class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Nuevo Pago
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Forma de Pago</th>
                        <th>Referencia</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pagos)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">
                                Sin pagos registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td>
                                    <?= date('d/m/Y H:i', strtotime($pago['pag_fecha'])) ?>
                                </td>
                                <td>
                                    $<?= number_format($pago['pag_monto'], 2) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($pago['forma_pago_nombre'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($pago['pag_referencia'] ?? '') ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= ($pago['pag_estado'] ?? '') === 'CONFIRMADO' ? 'success' : 'danger' ?>">
                                        <?= htmlspecialchars($pago['pag_estado'] ?? '') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body bg-light d-flex justify-content-between">
            <strong>Total Pagado:</strong>
            <strong>$<?= number_format($total_pagado, 2) ?></strong>
        </div>
    </div>
    
    <!-- Acciones -->
    <div class="row">
        <div class="col-md-12">
            <div class="btn-group">
                <?php if (($factura['fac_estado'] ?? '') === 'BORRADOR'): ?>
                    <a href="<?= url('facturacion', 'factura', 'emitir', ['id' => $factura['fac_id']]) ?>"
                       class="btn btn-success"
                       onclick="return confirm('¿Emitir esta factura?')">
                        <i class="fas fa-check"></i> Emitir
                    </a>
                <?php endif; ?>
                
                <?php if (($factura['fac_estado'] ?? '') !== 'ANULADA' && ($factura['fac_estado'] ?? '') !== 'PAGADA'): ?>
                    <a href="<?= url('facturacion', 'factura', 'anular', ['id' => $factura['fac_id']]) ?>"
                       class="btn btn-danger"
                       onclick="return confirm('¿Anular esta factura?')">
                        <i class="fas fa-times"></i> Anular
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
