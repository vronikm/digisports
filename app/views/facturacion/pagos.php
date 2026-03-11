<?php
/**
 * Vista: Listado de Pagos
 */
$pagos = $pagos ?? [];
$title = $title ?? 'Gestión de Pagos';
$estadoBadges = [
    'PENDIENTE'  => 'warning',
    'CONFIRMADO' => 'success',
    'ANULADO'    => 'danger',
];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-money-check-alt text-primary"></i>
                    <?= htmlspecialchars($title) ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('facturacion', 'dashboard', 'index') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Pagos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Listado de Pagos</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php if (empty($pagos)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-money-check-alt fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay pagos registrados</h5>
                    <p class="text-muted">Los pagos aparecerán aquí cuando se registren</p>
                </div>
                <?php else: ?>
                <table class="table table-hover table-striped">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Factura</th>
                            <th>Cliente</th>
                            <th>Forma de Pago</th>
                            <th>Fecha</th>
                            <th class="text-right">Monto</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagos as $p): ?>
                        <tr>
                            <td><?= $p['pag_id'] ?? '' ?></td>
                            <td><strong><?= htmlspecialchars($p['numero_factura'] ?? '—') ?></strong></td>
                            <td><?= htmlspecialchars($p['nombre_cliente'] ?? 'Sin cliente') ?></td>
                            <td><?= htmlspecialchars($p['forma_pago_nombre'] ?? '—') ?></td>
                            <td><?= isset($p['pag_fecha_pago']) ? date('d/m/Y', strtotime($p['pag_fecha_pago'])) : '—' ?></td>
                            <td class="text-right"><strong>$<?= number_format($p['pag_monto'] ?? 0, 2) ?></strong></td>
                            <td class="text-center">
                                <span class="badge badge-<?= $estadoBadges[$p['pag_estado']] ?? 'secondary' ?>">
                                    <?= $p['pag_estado'] ?? 'DESCONOCIDO' ?>
                                </span>
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
