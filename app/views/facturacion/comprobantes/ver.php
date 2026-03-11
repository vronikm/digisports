<?php
/**
 * Vista: Detalle de Comprobante
 */
$comprobante = $comprobante ?? [];
$title = $title ?? 'Detalle de Comprobante';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                    <?= htmlspecialchars($title) ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('facturacion', 'dashboard', 'index') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('facturacion', 'comprobante', 'index') ?>">Comprobantes</a></li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if (empty($comprobante)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5>Comprobante no encontrado</h5>
                <a href="<?= url('facturacion', 'comprobante', 'index') ?>" class="btn btn-primary mt-3">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al listado
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Información del Comprobante</h3>
                <div class="card-tools">
                    <a href="<?= url('facturacion', 'comprobante', 'index') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($comprobante as $campo => $valor): ?>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small d-block"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $campo))) ?></label>
                        <strong><?= htmlspecialchars($valor ?? '—') ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
