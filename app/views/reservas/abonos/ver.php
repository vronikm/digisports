<?php
/**
 * DigiSports Arena — Vista: Detalle de Monedero
 * Información del monedero + historial de movimientos
 */

$abono       = $abono ?? [];
$movimientos = $movimientos ?? [];
$csrfToken   = $csrf_token ?? '';
$saldo       = (float)($abono['saldo_disponible'] ?? 0);
$esActivo    = ($abono['estado'] ?? '') === 'ACTIVO';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-wallet mr-2 text-primary"></i>
                    Monedero #<?= $abono['abono_id'] ?? '?' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('reservas', 'abon', 'index') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Datos del monedero -->
            <div class="col-lg-4">
                <!-- Tarjeta saldo -->
                <div class="card card-outline card-primary">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-wallet fa-3x text-primary"></i>
                        </div>
                        <h2 class="text-success mb-1" style="font-size: 2.5rem; font-weight: 700;">
                            $<?= number_format($saldo, 2) ?>
                        </h2>
                        <p class="text-muted mb-3">Saldo Disponible</p>
                        
                        <?php
                            $estadoMap = ['ACTIVO'=>'badge-success','VENCIDO'=>'badge-danger','AGOTADO'=>'badge-warning'];
                            $estadoClass = $estadoMap[$abono['estado'] ?? ''] ?? 'badge-secondary';
                        ?>
                        <span class="badge <?= $estadoClass ?> px-3 py-2" style="font-size: .9rem;">
                            <?= $abono['estado'] ?? '-' ?>
                        </span>

                        <?php if ($esActivo): ?>
                        <div class="mt-3">
                            <button type="button" class="btn btn-success btn-block"
                                    onclick="abrirModalRecargaVer()">
                                <i class="fas fa-plus-circle mr-1"></i> Recargar
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info del cliente -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user mr-2"></i>Cliente</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted"><i class="fas fa-user mr-1"></i> Nombre</td>
                                <td class="font-weight-bold"><?= htmlspecialchars($abono['cliente_nombre'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="fas fa-id-card mr-1"></i> ID</td>
                                <td><?= htmlspecialchars($abono['cliente_identificacion'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="fas fa-envelope mr-1"></i> Email</td>
                                <td><?= htmlspecialchars($abono['cliente_email'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="fas fa-phone mr-1"></i> Teléfono</td>
                                <td><?= htmlspecialchars($abono['cliente_telefono'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Detalles del monedero -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Detalles</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">Total Cargado</td>
                                <td class="font-weight-bold text-info">$<?= number_format((float)($abono['monto_total'] ?? 0), 2) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Utilizado</td>
                                <td class="text-warning">$<?= number_format((float)($abono['monto_utilizado'] ?? 0), 2) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Forma de Pago</td>
                                <td>
                                    <?php
                                    $iconPagoMap = ['EFECTIVO'=>'💵','TARJETA'=>'💳','TRANSFERENCIA'=>'🏦','PAQUETE'=>'🎁'];
                                    $iconPago = $iconPagoMap[$abono['forma_pago'] ?? ''] ?? '💰';
                                    echo $iconPago . ' ' . htmlspecialchars($abono['forma_pago'] ?? '-');
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Fecha Compra</td>
                                <td><?= !empty($abono['fecha_compra']) ? date('d/m/Y', strtotime($abono['fecha_compra'])) : '-' ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Vencimiento</td>
                                <td>
                                    <?php if (!empty($abono['fecha_vencimiento'])): ?>
                                        <?php $vencido = $abono['fecha_vencimiento'] < date('Y-m-d'); ?>
                                        <span class="<?= $vencido ? 'text-danger font-weight-bold' : '' ?>">
                                            <?= date('d/m/Y', strtotime($abono['fecha_vencimiento'])) ?>
                                        </span>
                                        <?php if ($vencido): ?>
                                            <i class="fas fa-exclamation-triangle text-danger ml-1"></i>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Sin vencimiento</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Historial de movimientos -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-exchange-alt mr-2"></i>
                            Movimientos Recientes
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($movimientos)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Monto</th>
                                        <th>Saldo</th>
                                        <th>Descripción</th>
                                        <th>Forma Pago</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($movimientos as $m): ?>
                                    <?php
                                        $tipoIconMap = [
                                            'RECARGA'     => '<i class="fas fa-arrow-up text-success"></i>',
                                            'CONSUMO'     => '<i class="fas fa-arrow-down text-danger"></i>',
                                            'DEVOLUCION'  => '<i class="fas fa-undo text-info"></i>',
                                            'AJUSTE'      => '<i class="fas fa-sliders-h text-warning"></i>',
                                            'VENCIMIENTO' => '<i class="fas fa-clock text-secondary"></i>'
                                        ];
                                        $tipoIcon = $tipoIconMap[$m['tipo'] ?? ''] ?? '<i class="fas fa-circle text-muted"></i>';
                                        $montoClass = in_array($m['tipo'], ['RECARGA','DEVOLUCION']) ? 'text-success' : 'text-danger';
                                        $montoSign  = in_array($m['tipo'], ['RECARGA','DEVOLUCION']) ? '+' : '-';
                                    ?>
                                    <tr>
                                        <td>
                                            <small>
                                                <?= date('d/m/Y H:i', strtotime($m['fecha_registro'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?= $tipoIcon ?>
                                            <span class="ml-1"><?= $m['tipo'] ?></span>
                                        </td>
                                        <td class="<?= $montoClass ?> font-weight-bold">
                                            <?= $montoSign ?>$<?= number_format((float)$m['monto'], 2) ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                $<?= number_format((float)$m['saldo_anterior'], 2) ?>
                                                → 
                                            </small>
                                            <strong>$<?= number_format((float)$m['saldo_posterior'], 2) ?></strong>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($m['descripcion'] ?? '-') ?></small>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($m['forma_pago'] ?? '-') ?></small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-exchange-alt fa-3x mb-3" style="opacity: .2"></i>
                            <p>No hay movimientos registrados</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Barra de progreso visual -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Uso del Monedero</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $total     = max((float)($abono['monto_total'] ?? 1), 1);
                        $utilizado = (float)($abono['monto_utilizado'] ?? 0);
                        $pctUsado  = min(round(($utilizado / $total) * 100), 100);
                        $pctLibre  = 100 - $pctUsado;
                        ?>
                        <div class="progress" style="height: 28px; border-radius: 14px;">
                            <div class="progress-bar bg-warning" style="width: <?= $pctUsado ?>%;" 
                                 title="Utilizado: $<?= number_format($utilizado, 2) ?>">
                                <?= $pctUsado ?>% utilizado
                            </div>
                            <div class="progress-bar bg-success" style="width: <?= $pctLibre ?>%;"
                                 title="Disponible: $<?= number_format($saldo, 2) ?>">
                                <?= $pctLibre ?>% disponible
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-warning">Utilizado: $<?= number_format($utilizado, 2) ?></small>
                            <small class="text-success">Disponible: $<?= number_format($saldo, 2) ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Recarga -->
<?php if ($esActivo): ?>
<div class="modal fade" id="modalRecarga" tabindex="-1" role="dialog" aria-labelledby="modalRecargaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formRecargaDetalle" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="abono_id" value="<?= $abono['abono_id'] ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalRecargaLabel"><i class="fas fa-plus-circle mr-2"></i>Recargar Monedero</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" onclick="cerrarModalRecargaVer()"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <p class="mb-1">Saldo actual</p>
                        <h3 class="text-success">$<?= number_format($saldo, 2) ?></h3>
                    </div>
                    <div class="form-group">
                        <label>Monto a recargar ($)</label>
                        <input type="number" name="monto" class="form-control form-control-lg text-center" 
                               min="1" step="0.01" placeholder="0.00" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Forma de pago</label>
                                <select name="forma_pago" class="form-control">
                                    <option value="EFECTIVO">💵 Efectivo</option>
                                    <option value="TARJETA">💳 Tarjeta</option>
                                    <option value="TRANSFERENCIA">🏦 Transferencia</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nota</label>
                                <input type="text" name="nota" class="form-control" placeholder="Opcional">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="cerrarModalRecargaVer()">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check mr-1"></i> Confirmar Recarga</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
/* ─── URL de recarga para AJAX ─── */
$urlRecargarVer = url('reservas', 'abon', 'recargar');

// Scripts que se inyectan DESPUÉS de jQuery/Bootstrap en el layout
$scripts = <<<SCRIPTS
<script>
/* ─── Función global para abrir el modal de recarga en vista detalle ─── */
function abrirModalRecargaVer() {
    var modalEl = document.getElementById('modalRecarga');
    if (!modalEl) { alert('Modal no encontrado'); return; }

    // Limpiar campos
    var form = document.getElementById('formRecargaDetalle');
    if (form) {
        var montoInput = form.querySelector('[name=monto]');
        var notaInput  = form.querySelector('[name=nota]');
        if (montoInput) montoInput.value = '';
        if (notaInput)  notaInput.value = '';
    }

    // Abrir modal - jQuery/Bootstrap primero, fallback JS puro
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal !== 'undefined') {
        jQuery('#modalRecarga').modal('show');
    } else {
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        modalEl.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'modalRecargaBackdrop';
        document.body.appendChild(backdrop);
    }
}

/* ─── Cerrar modal manualmente ─── */
function cerrarModalRecargaVer() {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal !== 'undefined') {
        jQuery('#modalRecarga').modal('hide');
    } else {
        var modalEl = document.getElementById('modalRecarga');
        if (modalEl) {
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            modalEl.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            var backdrop = document.getElementById('modalRecargaBackdrop');
            if (backdrop) backdrop.remove();
        }
    }
}

/* ─── Submit del formulario con fetch (sin depender de jQuery) ─── */
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('formRecargaDetalle');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(form);
        var btnSubmit = form.querySelector('button[type=submit]');
        var btnOriginal = btnSubmit ? btnSubmit.innerHTML : '';

        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...';
        }

        var urlRecarga = '{$urlRecargarVer}';

        fetch(urlRecarga, {
            method: 'POST',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                cerrarModalRecargaVer();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Recarga exitosa!',
                        text: data.message || 'Saldo actualizado correctamente',
                        confirmButtonColor: '#28a745'
                    }).then(function() { location.reload(); });
                } else {
                    alert('¡Recarga exitosa! ' + (data.message || ''));
                    location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', data.message || 'Error al procesar la recarga', 'error');
                } else {
                    alert('Error: ' + (data.message || 'Error al procesar la recarga'));
                }
                if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.innerHTML = btnOriginal; }
            }
        })
        .catch(function(err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', 'Error de conexión con el servidor', 'error');
            } else {
                alert('Error de conexión con el servidor');
            }
            if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.innerHTML = btnOriginal; }
        });
    });
});
</script>
SCRIPTS;
?>
<?php endif; ?>
