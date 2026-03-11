<?php
/**
 * DigiSports Facturación - Vista Dashboard
 */
$kpis              = $kpis ?? [];
$ultimas           = $ultimas_facturas ?? [];
$moduloColor       = $modulo_actual['color'] ?? '#F59E0B';
$moduloIcono       = $modulo_actual['icono'] ?? 'fas fa-file-invoice-dollar';

$estadoBadges = [
    'BORRADOR' => 'secondary',
    'EMITIDA'  => 'primary',
    'PAGADA'   => 'success',
    'ANULADA'  => 'danger',
];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?> mr-2" style="color: <?= $moduloColor ?>"></i>
                    Dashboard Facturación
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right quick-actions d-flex align-items-center">
                    <a href="<?= url('facturacion', 'factura', 'crear') ?>" class="btn" style="background: <?= $moduloColor ?>; color: white;">
                        <i class="fas fa-plus mr-1"></i> Nueva Factura
                    </a>
                    <a href="<?= url('facturacion', 'factura', 'index') ?>" class="btn btn-outline-secondary ml-1">
                        <i class="fas fa-list mr-1"></i> Ver Todas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

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
                        </div>
                        <div class="kpi-value"><?= $kpi['value'] ?></div>
                        <div class="kpi-label"><?= $kpi['label'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Últimas Facturas -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-file-invoice mr-2" style="color: <?= $moduloColor ?>"></i>
                            Últimas Facturas
                        </h3>
                        <div class="card-tools">
                            <span class="badge" style="background: <?= $moduloColor ?>; color: white;"><?= count($ultimas) ?> registros</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($ultimas)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-file-invoice fa-2x mb-2 opacity-50"></i>
                            <p>No hay facturas registradas aún</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Número</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimas as $f): ?>
                                    <tr>
                                        <td><?= $f['fac_id'] ?></td>
                                        <td><strong><?= htmlspecialchars($f['fac_numero'] ?? '—') ?></strong></td>
                                        <td><?= date('d/m/Y', strtotime($f['fac_fecha_emision'])) ?></td>
                                        <td><?= htmlspecialchars($f['nombre_cliente'] ?? 'Sin cliente') ?></td>
                                        <td class="text-right"><strong>$<?= number_format($f['fac_total'] ?? 0, 2) ?></strong></td>
                                        <td class="text-center">
                                            <span class="badge badge-<?= $estadoBadges[$f['fac_estado']] ?? 'secondary' ?>">
                                                <?= $f['fac_estado'] ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= url('facturacion', 'factura', 'ver', ['id' => $f['fac_id']]) ?>" class="btn btn-sm btn-info" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
