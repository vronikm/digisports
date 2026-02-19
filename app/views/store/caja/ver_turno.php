<?php
/**
 * DigiSports Store - Detalle de Turno
 */
$turno       = $turno ?? [];
$ventas      = $ventas ?? [];
$movimientos = $movimientos ?? [];
$arqueos     = $arqueos ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-receipt mr-2" style="color:<?= $moduloColor ?>"></i>Detalle de Turno</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('store', 'caja', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Información</h3></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Estado</span>
                            <span class="badge badge-<?= ($turno['tur_estado'] ?? '') === 'ABIERTO' ? 'success' : 'secondary' ?>"><?= $turno['tur_estado'] ?? '—' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Caja</span>
                            <strong><?= htmlspecialchars($turno['caj_nombre'] ?? '—') ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Apertura</span>
                            <span><?= !empty($turno['tur_fecha_apertura']) ? date('d/m/Y H:i', strtotime($turno['tur_fecha_apertura'])) : '—' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Cierre</span>
                            <span><?= !empty($turno['tur_fecha_cierre']) ? date('d/m/Y H:i', strtotime($turno['tur_fecha_cierre'])) : '—' ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Monto Apertura</span>
                            <strong>$<?= number_format($turno['tur_monto_apertura'] ?? 0, 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Monto Cierre</span>
                            <strong>$<?= number_format($turno['tur_monto_cierre_real'] ?? 0, 2) ?></strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Ventas</span>
                            <strong class="text-success">$<?= number_format($turno['tur_total_ventas'] ?? 0, 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"># Ventas</span>
                            <strong><?= intval($turno['tur_num_ventas'] ?? 0) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Efectivo</span>
                            <span>$<?= number_format($turno['tur_total_efectivo'] ?? 0, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tarjeta</span>
                            <span>$<?= number_format($turno['tur_total_tarjeta'] ?? 0, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Transferencia</span>
                            <span>$<?= number_format($turno['tur_total_transferencia'] ?? 0, 2) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Diferencia</span>
                            <?php $dif = floatval($turno['tur_diferencia'] ?? 0); ?>
                            <strong class="<?= $dif == 0 ? 'text-success' : ($dif > 0 ? 'text-info' : 'text-danger') ?>">$<?= number_format($dif, 2) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Ventas del Turno -->
                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-shopping-cart mr-2"></i>Ventas (<?= count($ventas) ?>)</h3></div>
                    <div class="card-body p-0">
                        <?php if (empty($ventas)): ?>
                        <div class="text-center py-4 text-muted"><p>No hay ventas en este turno</p></div>
                        <?php else: ?>
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr><th>#</th><th>Cliente</th><th class="text-right">Total</th><th>Estado</th><th>Hora</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventas as $v): ?>
                                <tr>
                                    <td><a href="<?= url('store', 'venta', 'ver', ['id' => $v['ven_venta_id']]) ?>" style="color:<?= $moduloColor ?>"><?= htmlspecialchars($v['ven_numero'] ?? '—') ?></a></td>
                                    <td><?= htmlspecialchars(($v['cli_nombres'] ?? '') . ' ' . ($v['cli_apellidos'] ?? '')) ?: '<span class="text-muted">C. Final</span>' ?></td>
                                    <td class="text-right"><strong>$<?= number_format($v['ven_total'] ?? 0, 2) ?></strong></td>
                                    <td><span class="badge badge-<?= ($v['ven_estado'] ?? '') === 'COMPLETADA' ? 'success' : 'warning' ?>"><?= $v['ven_estado'] ?? '—' ?></span></td>
                                    <td><small><?= date('H:i', strtotime($v['ven_fecha'])) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Movimientos -->
                <?php if (!empty($movimientos)): ?>
                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-exchange-alt mr-2"></i>Movimientos</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light"><tr><th>Tipo</th><th class="text-right">Monto</th><th>Descripción</th><th>Hora</th></tr></thead>
                            <tbody>
                                <?php foreach ($movimientos as $mv): ?>
                                <tr>
                                    <td><span class="badge badge-<?= $mv['mov_tipo'] === 'ENTRADA' ? 'success' : 'warning' ?>"><?= $mv['mov_tipo'] ?></span></td>
                                    <td class="text-right">$<?= number_format($mv['mov_monto'] ?? 0, 2) ?></td>
                                    <td><small><?= htmlspecialchars($mv['mov_descripcion'] ?? '') ?></small></td>
                                    <td><small><?= date('H:i', strtotime($mv['mov_fecha'])) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
