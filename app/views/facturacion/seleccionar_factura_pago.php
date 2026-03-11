<?php
/**
 * Vista: Seleccionar Factura para Registrar Pago
 */
$facturas = $facturas_pendientes ?? [];
$title = $title ?? 'Registrar Pago';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-cash-register text-primary"></i>
                    <?= htmlspecialchars($title) ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('facturacion', 'dashboard', 'index') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Registrar Pago</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            Seleccione la factura a la que desea registrar un pago.
        </div>

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-invoice mr-2"></i>Facturas Pendientes de Pago</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php if (empty($facturas)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h5 class="text-muted">No hay facturas pendientes de pago</h5>
                    <p class="text-muted">Todas las facturas están pagadas o no hay facturas emitidas</p>
                </div>
                <?php else: ?>
                <table class="table table-hover table-striped">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Pagado</th>
                            <th class="text-right">Pendiente</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($facturas as $f): ?>
                        <?php $pendiente = ($f['fac_total'] ?? 0) - ($f['total_pagado'] ?? 0); ?>
                        <tr>
                            <td><?= $f['fac_id'] ?></td>
                            <td><strong><?= htmlspecialchars($f['fac_numero'] ?? '—') ?></strong></td>
                            <td><?= htmlspecialchars($f['nombre_cliente'] ?? 'Sin cliente') ?></td>
                            <td><?= isset($f['fac_fecha_emision']) ? date('d/m/Y', strtotime($f['fac_fecha_emision'])) : '—' ?></td>
                            <td class="text-right">$<?= number_format($f['fac_total'] ?? 0, 2) ?></td>
                            <td class="text-right text-success">$<?= number_format($f['total_pagado'] ?? 0, 2) ?></td>
                            <td class="text-right text-danger"><strong>$<?= number_format($pendiente, 2) ?></strong></td>
                            <td class="text-center">
                                <span class="badge badge-<?= $f['fac_estado'] === 'EMITIDA' ? 'primary' : 'secondary' ?>">
                                    <?= $f['fac_estado'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($pendiente > 0): ?>
                                <a href="<?= url('facturacion', 'pago', 'crear', ['factura_id' => $f['fac_id']]) ?>" 
                                   class="btn btn-sm btn-success" title="Registrar Pago">
                                    <i class="fas fa-dollar-sign mr-1"></i> Pagar
                                </a>
                                <?php else: ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Pagada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
