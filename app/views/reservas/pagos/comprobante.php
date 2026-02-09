<?php
/**
 * DigiSports Arena — Vista: Comprobante de Pago
 * Recibo imprimible con detalles del cobro
 */
$pago          = $pago ?? [];
$pagosReserva  = $pagos_reserva ?? [];

$formaIcons = ['EFECTIVO'=>'fa-money-bill-wave','TARJETA'=>'fa-credit-card','TRANSFERENCIA'=>'fa-university','MONEDERO'=>'fa-wallet','MIXTO'=>'fa-exchange-alt'];
$formaIcon = $formaIcons[$pago['pag_forma_pago'] ?? ''] ?? 'fa-dollar-sign';
?>

<div class="content-header d-print-none">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-file-invoice-dollar mr-2 text-info"></i> Comprobante de Pago</h1>
            </div>
            <div class="col-sm-6 text-right">
                <button onclick="window.print()" class="btn btn-primary mr-2"><i class="fas fa-print mr-1"></i> Imprimir</button>
                <a href="<?= url('reservas', 'pago', 'checkout') ?>&id=<?= $pago['res_reserva_id'] ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card" id="comprobante">
                    <!-- Header -->
                    <div class="card-header bg-gradient-primary text-white text-center py-4">
                        <h3 class="mb-1"><i class="fas fa-building mr-2"></i> DigiSports Arena</h3>
                        <p class="mb-0">Comprobante de Pago</p>
                    </div>

                    <div class="card-body">
                        <!-- Info del pago -->
                        <div class="row mb-4">
                            <div class="col-sm-6">
                                <h6 class="text-muted mb-1">Comprobante Nro.</h6>
                                <h4 class="text-primary font-weight-bold">#<?= str_pad($pago['pag_pago_id'], 6, '0', STR_PAD_LEFT) ?></h4>
                            </div>
                            <div class="col-sm-6 text-sm-right">
                                <h6 class="text-muted mb-1">Fecha de Pago</h6>
                                <h5><?= date('d/m/Y H:i', strtotime($pago['pag_fecha_pago'])) ?></h5>
                            </div>
                        </div>

                        <hr>

                        <!-- Datos del Cliente -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="font-weight-bold text-muted mb-2"><i class="fas fa-user mr-1"></i> Cliente</h6>
                                <p class="mb-1"><strong><?= htmlspecialchars($pago['cliente_nombre']) ?></strong></p>
                                <?php if (!empty($pago['cliente_identificacion'])): ?>
                                <p class="mb-1 text-muted">CI/RUC: <?= htmlspecialchars($pago['cliente_identificacion']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($pago['cliente_email'])): ?>
                                <p class="mb-1 text-muted"><?= htmlspecialchars($pago['cliente_email']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($pago['cliente_telefono'])): ?>
                                <p class="mb-0 text-muted"><?= htmlspecialchars($pago['cliente_telefono']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="font-weight-bold text-muted mb-2"><i class="fas fa-calendar-check mr-1"></i> Reserva</h6>
                                <p class="mb-1">Reserva <strong>#<?= $pago['res_reserva_id'] ?></strong></p>
                                <p class="mb-1"><?= htmlspecialchars($pago['instalacion_nombre']) ?></p>
                                <p class="mb-1">
                                    <i class="far fa-calendar mr-1"></i> <?= date('d/m/Y', strtotime($pago['res_fecha_reserva'])) ?>
                                    &nbsp;
                                    <i class="far fa-clock mr-1"></i> <?= date('H:i', strtotime($pago['res_hora_inicio'])) ?> — <?= date('H:i', strtotime($pago['res_hora_fin'])) ?>
                                </p>
                            </div>
                        </div>

                        <hr>

                        <!-- Detalle del pago -->
                        <table class="table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Concepto</th>
                                    <th class="text-center">Forma</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong>Pago de Reserva #<?= $pago['res_reserva_id'] ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($pago['instalacion_nombre']) ?> — <?= date('d/m/Y', strtotime($pago['res_fecha_reserva'])) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <i class="fas <?= $formaIcon ?> mr-1"></i>
                                        <?= htmlspecialchars($pago['pag_tipo_pago']) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $estPago = ['COMPLETADO'=>'success','ANULADO'=>'danger','PENDIENTE'=>'warning'];
                                        $bP = $estPago[$pago['pag_estado'] ?? ''] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $bP ?>"><?= $pago['pag_estado'] ?></span>
                                    </td>
                                    <td class="text-right">
                                        <strong class="h5 text-success">$<?= number_format($pago['pag_monto'], 2) ?></strong>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td colspan="3" class="text-right"><strong>Total Reserva:</strong></td>
                                    <td class="text-right"><strong>$<?= number_format($pago['res_precio_total'], 2) ?></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-right">Estado de Pago:</td>
                                    <td class="text-right">
                                        <?php
                                        $estResPago = ['PENDIENTE'=>'warning','PARCIAL'=>'info','PAGADO'=>'success','REEMBOLSADO'=>'secondary'];
                                        $bRP = $estResPago[$pago['res_estado_pago'] ?? ''] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $bRP ?> px-3 py-2"><?= $pago['res_estado_pago'] ?></span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                        <?php if (!empty($pago['pag_referencia'])): ?>
                        <div class="callout callout-info">
                            <strong>Referencia:</strong> <?= htmlspecialchars($pago['pag_referencia']) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer text-center text-muted bg-light">
                        <small>
                            <i class="fas fa-building mr-1"></i> DigiSports Arena — Comprobante generado el <?= date('d/m/Y H:i') ?>
                            <br>Este documento es un comprobante interno de pago
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<style>
@media print {
    .main-sidebar, .main-header, .main-footer, .content-header, .d-print-none { display: none !important; }
    .content-wrapper { margin-left: 0 !important; }
    #comprobante { border: 1px solid #dee2e6; }
}
</style>
