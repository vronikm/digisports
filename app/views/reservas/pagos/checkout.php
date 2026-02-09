<?php
/**
 * DigiSports Arena — Vista: Checkout / Cobro de Reserva
 * Pago mixto: Monedero + Efectivo/Tarjeta/Transferencia
 */
$reserva        = $reserva ?? [];
$pagos          = $pagos ?? [];
$saldoMonedero  = $saldo_monedero ?? 0;
$totalPagado    = $total_pagado ?? 0;
$saldoPendiente = $saldo_pendiente ?? 0;
$csrfToken      = $csrf_token ?? '';

$estadoClases = ['PENDIENTE'=>'warning','PARCIAL'=>'info','PAGADO'=>'success','REEMBOLSADO'=>'secondary'];
$badgePago = $estadoClases[$reserva['res_estado_pago'] ?? ''] ?? 'secondary';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-cash-register mr-2 text-success"></i>
                    Cobrar Reserva #<?= $reserva['res_reserva_id'] ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('reservas', 'reserva', 'ver') ?>&id=<?= $reserva['res_reserva_id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a Reserva
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">

            <!-- COLUMNA IZQUIERDA: Resumen de la reserva -->
            <div class="col-lg-5">
                <!-- Tarjeta de reserva -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-receipt mr-1"></i> Detalle de Reserva</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <tr>
                                <td class="text-muted" width="40%">Instalación</td>
                                <td><strong><?= htmlspecialchars($reserva['instalacion_nombre']) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Fecha</td>
                                <td><strong><?= date('d/m/Y', strtotime($reserva['res_fecha_reserva'])) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Horario</td>
                                <td>
                                    <span class="text-primary font-weight-bold">
                                        <?= date('H:i', strtotime($reserva['res_hora_inicio'])) ?> — <?= date('H:i', strtotime($reserva['res_hora_fin'])) ?>
                                    </span>
                                    <small class="text-muted ml-1">(<?= $reserva['res_duracion_minutos'] ?> min)</small>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Cliente</td>
                                <td>
                                    <i class="fas fa-user text-primary mr-1"></i>
                                    <strong><?= htmlspecialchars($reserva['cliente_nombre']) ?></strong>
                                    <?php if (!empty($reserva['cliente_telefono'])): ?>
                                    <br><small class="text-muted"><i class="fas fa-phone mr-1"></i><?= htmlspecialchars($reserva['cliente_telefono']) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Estado Reserva</td>
                                <td>
                                    <?php
                                    $estRes = ['PENDIENTE'=>'warning','CONFIRMADA'=>'success','COMPLETADA'=>'info','CANCELADA'=>'danger'];
                                    $bRes = $estRes[$reserva['res_estado'] ?? ''] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?= $bRes ?>"><?= $reserva['res_estado'] ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Resumen financiero -->
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calculator mr-1"></i> Resumen Financiero</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td>Precio Base</td>
                                <td class="text-right">$<?= number_format($reserva['res_precio_base'], 2) ?></td>
                            </tr>
                            <?php if ((float)$reserva['res_descuento_monto'] > 0): ?>
                            <tr>
                                <td>Descuento</td>
                                <td class="text-right text-danger">-$<?= number_format($reserva['res_descuento_monto'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="border-top">
                                <td><strong>Total Reserva</strong></td>
                                <td class="text-right"><strong class="h5">$<?= number_format($reserva['res_precio_total'], 2) ?></strong></td>
                            </tr>
                            <?php if ($totalPagado > 0): ?>
                            <tr>
                                <td class="text-success"><i class="fas fa-check-circle mr-1"></i> Ya Pagado</td>
                                <td class="text-right text-success">$<?= number_format($totalPagado, 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ((float)$reserva['res_abono_utilizado'] > 0): ?>
                            <tr>
                                <td class="text-info"><i class="fas fa-wallet mr-1"></i> Abono Utilizado</td>
                                <td class="text-right text-info">$<?= number_format($reserva['res_abono_utilizado'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="border-top bg-light">
                                <td><strong class="h5 text-danger">Saldo Pendiente</strong></td>
                                <td class="text-right"><strong class="h4 text-danger" id="labelPendiente">$<?= number_format($saldoPendiente, 2) ?></strong></td>
                            </tr>
                        </table>

                        <div class="mt-3 text-center">
                            <span class="badge badge-<?= $badgePago ?> px-3 py-2" style="font-size:.9rem;">
                                Estado Pago: <?= $reserva['res_estado_pago'] ?? 'PENDIENTE' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Historial de pagos -->
                <?php if (!empty($pagos)): ?>
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history mr-1"></i> Pagos Registrados</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Forma</th>
                                    <th class="text-right">Monto</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos as $p): ?>
                                <?php
                                    $estPago = ['COMPLETADO'=>'success','ANULADO'=>'danger','PENDIENTE'=>'warning'];
                                    $bP = $estPago[$p['pag_estado'] ?? ''] ?? 'secondary';
                                ?>
                                <tr <?= $p['pag_estado'] === 'ANULADO' ? 'class="text-muted" style="text-decoration:line-through;"' : '' ?>>
                                    <td><?= $p['pag_pago_id'] ?></td>
                                    <td><small><?= date('d/m/Y H:i', strtotime($p['pag_fecha_pago'])) ?></small></td>
                                    <td>
                                        <?php
                                        $iconForma = ['EFECTIVO'=>'fa-money-bill-wave','TARJETA'=>'fa-credit-card','TRANSFERENCIA'=>'fa-university','MONEDERO'=>'fa-wallet','MIXTO'=>'fa-exchange-alt'];
                                        $ic = $iconForma[$p['pag_forma_pago'] ?? ''] ?? 'fa-dollar-sign';
                                        ?>
                                        <i class="fas <?= $ic ?> mr-1"></i><?= $p['pag_tipo_pago'] ?>
                                    </td>
                                    <td class="text-right font-weight-bold">$<?= number_format($p['pag_monto'], 2) ?></td>
                                    <td><span class="badge badge-<?= $bP ?>"><?= $p['pag_estado'] ?></span></td>
                                    <td>
                                        <?php if ($p['pag_estado'] === 'COMPLETADO'): ?>
                                        <a href="<?= url('reservas', 'pago', 'comprobante') ?>&id=<?= $p['pag_pago_id'] ?>" 
                                           class="btn btn-xs btn-outline-info" title="Ver comprobante">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- COLUMNA DERECHA: Formulario de pago -->
            <div class="col-lg-7">
                <?php if ($saldoPendiente > 0.01): ?>
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-credit-card mr-1"></i> Registrar Pago</h3>
                    </div>
                    <div class="card-body">
                        <form id="formPago" method="POST" action="<?= url('reservas', 'pago', 'procesarPago') ?>">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="reserva_id" value="<?= $reserva['res_reserva_id'] ?>">

                            <!-- Monedero del cliente -->
                            <?php if ($saldoMonedero > 0): ?>
                            <div class="callout callout-info">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1"><i class="fas fa-wallet mr-1"></i> Monedero del Cliente</h5>
                                        <p class="mb-0 text-muted">Saldo disponible: <strong class="text-success h5">$<?= number_format($saldoMonedero, 2) ?></strong></p>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="usarMonedero"
                                            <?= $saldoMonedero > 0 ? '' : 'disabled' ?>>
                                        <label class="custom-control-label" for="usarMonedero">Usar monedero</label>
                                    </div>
                                </div>

                                <div id="panelMonedero" style="display: none;" class="mt-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <label>Monto del monedero</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="number" class="form-control" id="montoMonedero" name="monto_monedero"
                                                       step="0.01" min="0" max="<?= min($saldoMonedero, $saldoPendiente) ?>" value="0"
                                                       placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label>&nbsp;</label>
                                            <div class="btn-group btn-block" role="group">
                                                <button type="button" class="btn btn-outline-info btn-monedero-preset" data-pct="50">50%</button>
                                                <button type="button" class="btn btn-outline-info btn-monedero-preset" data-pct="100">100%</button>
                                                <button type="button" class="btn btn-outline-secondary btn-monedero-preset" data-pct="0">Limpiar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <input type="hidden" name="monto_monedero" value="0">
                            <?php endif; ?>

                            <!-- Forma de pago del resto -->
                            <div class="form-group">
                                <label><i class="fas fa-money-check-alt mr-1"></i> Forma de Pago</label>
                                <div class="row" id="formasPago">
                                    <div class="col-md-4 mb-2">
                                        <div class="card forma-pago-card active" data-forma="EFECTIVO" style="cursor:pointer;">
                                            <div class="card-body text-center py-3">
                                                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                                <div class="font-weight-bold">Efectivo</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="card forma-pago-card" data-forma="TARJETA" style="cursor:pointer;">
                                            <div class="card-body text-center py-3">
                                                <i class="fas fa-credit-card fa-2x text-primary mb-2"></i>
                                                <div class="font-weight-bold">Tarjeta</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="card forma-pago-card" data-forma="TRANSFERENCIA" style="cursor:pointer;">
                                            <div class="card-body text-center py-3">
                                                <i class="fas fa-university fa-2x text-info mb-2"></i>
                                                <div class="font-weight-bold">Transferencia</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="formaPago" name="forma_pago" value="EFECTIVO">
                            </div>

                            <!-- Monto efectivo -->
                            <div class="form-group" id="grupoMontoEfectivo">
                                <label>Monto a cobrar <span id="labelForma">(Efectivo)</span></label>
                                <div class="input-group input-group-lg">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-success text-white"><i class="fas fa-dollar-sign"></i></span>
                                    </div>
                                    <input type="number" class="form-control form-control-lg" id="montoEfectivo" name="monto_efectivo"
                                           step="0.01" min="0" max="<?= $saldoPendiente ?>" value="<?= number_format($saldoPendiente, 2, '.', '') ?>"
                                           required>
                                </div>
                                <small class="text-muted">Pendiente: $<span id="pendienteLabel"><?= number_format($saldoPendiente, 2) ?></span></small>
                            </div>

                            <!-- Referencia -->
                            <div class="form-group" id="grupoReferencia" style="display:none;">
                                <label>Referencia / Nro. Transacción</label>
                                <input type="text" class="form-control" name="referencia" placeholder="Ej: AUT-123456">
                            </div>

                            <!-- Observaciones -->
                            <div class="form-group">
                                <label>Observaciones <small class="text-muted">(opcional)</small></label>
                                <textarea class="form-control" name="observaciones" rows="2" placeholder="Notas sobre el pago..."></textarea>
                            </div>

                            <!-- Resumen del pago -->
                            <div class="callout callout-success" id="resumenPago">
                                <h5><i class="fas fa-receipt mr-1"></i> Resumen del Cobro</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr id="filaMonedero" style="display:none;">
                                                <td><i class="fas fa-wallet text-info mr-1"></i> Monedero</td>
                                                <td class="text-right font-weight-bold text-info" id="resMonedero">$0.00</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-money-bill-wave text-success mr-1"></i> <span id="resFormaLabel">Efectivo</span></td>
                                                <td class="text-right font-weight-bold text-success" id="resEfectivo">$<?= number_format($saldoPendiente, 2) ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-6 text-center border-left">
                                        <div class="text-muted small">TOTAL A COBRAR</div>
                                        <div class="h2 text-success mb-0" id="resTotalCobrar">$<?= number_format($saldoPendiente, 2) ?></div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg btn-block" id="btnPagar">
                                <i class="fas fa-check-circle mr-2"></i>
                                Confirmar Cobro — $<span id="btnMontoLabel"><?= number_format($saldoPendiente, 2) ?></span>
                            </button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="card bg-success">
                    <div class="card-body text-center text-white py-5">
                        <i class="fas fa-check-circle fa-4x mb-3"></i>
                        <h3>Reserva Pagada en su Totalidad</h3>
                        <p class="mb-0">Total: $<?= number_format($reserva['res_precio_total'], 2) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<style>
.forma-pago-card { border: 2px solid #dee2e6; transition: all .2s; }
.forma-pago-card:hover { border-color: #3B82F6; transform: translateY(-2px); }
.forma-pago-card.active { border-color: #3B82F6; background: #EBF5FB; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var saldoPendiente = <?= $saldoPendiente ?>;
    var saldoMonedero  = <?= $saldoMonedero ?>;
    var montoMonederoInput = document.getElementById('montoMonedero');
    var montoEfectivoInput = document.getElementById('montoEfectivo');
    var usarMonederoCheck  = document.getElementById('usarMonedero');

    // ── Selector de forma de pago ──
    document.querySelectorAll('.forma-pago-card').forEach(function(card) {
        card.addEventListener('click', function() {
            document.querySelectorAll('.forma-pago-card').forEach(function(c) { c.classList.remove('active'); });
            this.classList.add('active');
            var forma = this.dataset.forma;
            document.getElementById('formaPago').value = forma;
            document.getElementById('labelForma').textContent = '(' + this.querySelector('.font-weight-bold').textContent + ')';
            document.getElementById('resFormaLabel').textContent = this.querySelector('.font-weight-bold').textContent;
            document.getElementById('grupoReferencia').style.display = (forma !== 'EFECTIVO') ? 'block' : 'none';
        });
    });

    // ── Toggle monedero ──
    if (usarMonederoCheck) {
        usarMonederoCheck.addEventListener('change', function() {
            document.getElementById('panelMonedero').style.display = this.checked ? 'block' : 'none';
            document.getElementById('filaMonedero').style.display = this.checked ? 'table-row' : 'none';
            if (!this.checked) {
                montoMonederoInput.value = 0;
                recalcular();
            }
        });
    }

    // ── Presets de % monedero ──
    document.querySelectorAll('.btn-monedero-preset').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var pct = parseInt(this.dataset.pct);
            var maxMonedero = Math.min(saldoMonedero, saldoPendiente);
            montoMonederoInput.value = (maxMonedero * pct / 100).toFixed(2);
            recalcular();
        });
    });

    // ── Recalcular totales ──
    if (montoMonederoInput) {
        montoMonederoInput.addEventListener('input', recalcular);
    }
    if (montoEfectivoInput) {
        montoEfectivoInput.addEventListener('input', recalcular);
    }

    function recalcular() {
        var monedero  = parseFloat(montoMonederoInput ? montoMonederoInput.value : 0) || 0;
        var efectivo  = parseFloat(montoEfectivoInput.value) || 0;
        var restante  = Math.max(0, saldoPendiente - monedero);

        // Ajustar máximo del efectivo
        montoEfectivoInput.max = restante.toFixed(2);
        if (efectivo > restante) {
            montoEfectivoInput.value = restante.toFixed(2);
            efectivo = restante;
        }

        // Si se usa monedero pero no se ingresó efectivo, llenar automáticamente
        if (monedero > 0 && efectivo === 0) {
            montoEfectivoInput.value = restante.toFixed(2);
            efectivo = restante;
        }

        var total = monedero + efectivo;

        // Actualizar resumen visual
        document.getElementById('resMonedero').textContent = '$' + monedero.toFixed(2);
        document.getElementById('resEfectivo').textContent = '$' + efectivo.toFixed(2);
        document.getElementById('resTotalCobrar').textContent = '$' + total.toFixed(2);
        document.getElementById('btnMontoLabel').textContent = total.toFixed(2);
        document.getElementById('pendienteLabel').textContent = saldoPendiente.toFixed(2);
    }

    // ── Submit ──
    document.getElementById('formPago').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        var monedero = parseFloat(montoMonederoInput ? montoMonederoInput.value : 0) || 0;
        var efectivo = parseFloat(montoEfectivoInput.value) || 0;
        var total = monedero + efectivo;

        if (total <= 0) {
            Swal.fire('Error', 'Ingrese un monto mayor a $0', 'error');
            return;
        }

        var msg = 'Se registrará un cobro de <strong>$' + total.toFixed(2) + '</strong>';
        if (monedero > 0) {
            msg += '<br><small>($' + monedero.toFixed(2) + ' del monedero + $' + efectivo.toFixed(2) + ' ' + document.getElementById('formaPago').value.toLowerCase() + ')</small>';
        }

        Swal.fire({
            title: '¿Confirmar cobro?',
            html: msg,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Confirmar Cobro',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745'
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById('btnPagar').disabled = true;
                document.getElementById('btnPagar').innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';

                var formData = new FormData(form);
                fetch(form.action, { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(resp) {
                        if (resp.success) {
                            Swal.fire({
                                title: '¡Pago Registrado!',
                                text: resp.message || 'Cobro exitoso',
                                icon: 'success',
                                confirmButtonText: 'Ver Comprobante'
                            }).then(function() {
                                if (resp.data && resp.data.redirect) {
                                    window.location.href = resp.data.redirect;
                                } else {
                                    window.location.reload();
                                }
                            });
                        } else {
                            Swal.fire('Error', resp.message || 'Error al procesar', 'error');
                            document.getElementById('btnPagar').disabled = false;
                            document.getElementById('btnPagar').innerHTML = '<i class="fas fa-check-circle mr-2"></i> Confirmar Cobro — $' + total.toFixed(2);
                        }
                    })
                    .catch(function(err) {
                        Swal.fire('Error', 'Error de conexión', 'error');
                        document.getElementById('btnPagar').disabled = false;
                    });
            }
        });
    });
});
</script>
