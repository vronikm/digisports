<?php
/**
 * DigiSports Arena — Vista: Vender Entrada
 * Formulario POS de venta con monedero y tarifas dinámicas
 */
$instalaciones = $instalaciones ?? [];
$clientes      = $clientes ?? [];
$tarifas       = $tarifas ?? [];
$csrf          = $csrf_token ?? '';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-cart-plus mr-2 text-success"></i> Vender Entrada</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= url('instalaciones', 'entrada', 'index') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">

            <!-- Formulario -->
            <div class="col-lg-7">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-ticket-alt mr-1"></i> Datos de la Entrada</h3>
                    </div>
                    <div class="card-body">
                        <form id="formVenta">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-building mr-1 text-primary"></i> Instalación *</label>
                                        <select name="instalacion_id" id="selInstalacion" class="form-control" required>
                                            <option value="">— Seleccionar —</option>
                                            <?php foreach ($instalaciones as $inst): ?>
                                                <option value="<?= $inst['ins_instalacion_id'] ?>"
                                                        data-capacidad="<?= $inst['ins_capacidad'] ?>">
                                                    <?= htmlspecialchars($inst['ins_nombre']) ?> (<?= $inst['ins_tipo'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-tag mr-1 text-warning"></i> Tipo de Entrada *</label>
                                        <select name="tipo" id="selTipo" class="form-control" required>
                                            <option value="GENERAL">General</option>
                                            <option value="VIP">VIP</option>
                                            <option value="ABONADO">Abonado</option>
                                            <option value="CORTESIA">Cortesía</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-calendar mr-1"></i> Fecha de Entrada</label>
                                        <input type="date" name="fecha_entrada" id="inputFecha"
                                               class="form-control" value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-clock mr-1"></i> Hora de Entrada</label>
                                        <input type="time" name="hora_entrada" id="inputHora"
                                               class="form-control" value="<?= date('H:i') ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Tarifa rápida -->
                            <div id="tarifasRapidas" class="mb-3" style="display:none;">
                                <label class="font-weight-bold"><i class="fas fa-tags mr-1 text-info"></i> Tarifas Disponibles</label>
                                <div class="d-flex flex-wrap" id="tarifaBtns"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-user mr-1 text-success"></i> Cliente (opcional)</label>
                                        <select name="cliente_id" id="selCliente" class="form-control">
                                            <option value="0">— Sin cliente —</option>
                                            <?php foreach ($clientes as $cli): ?>
                                                <option value="<?= $cli['cli_cliente_id'] ?>">
                                                    <?= htmlspecialchars($cli['cli_nombres'] . ' ' . $cli['cli_apellidos']) ?>
                                                    <?= $cli['cli_identificacion'] ? '(' . $cli['cli_identificacion'] . ')' : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Observaciones</label>
                                        <input type="text" name="observaciones" class="form-control" placeholder="Notas opcionales...">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Precio y descuento -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Precio $</label>
                                        <input type="number" name="precio" id="inputPrecio"
                                               class="form-control form-control-lg text-center font-weight-bold"
                                               min="0" step="0.01" value="0.00" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Descuento $</label>
                                        <input type="number" name="descuento" id="inputDescuento"
                                               class="form-control text-center" min="0" step="0.01" value="0.00">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Total $</label>
                                        <input type="text" id="displayTotal"
                                               class="form-control form-control-lg text-center font-weight-bold text-success"
                                               value="$0.00" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Monedero -->
                            <div id="panelMonedero" style="display:none;">
                                <div class="callout callout-warning">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong><i class="fas fa-wallet mr-1"></i> Monedero del Cliente</strong>
                                        <span class="badge badge-warning px-3 py-2" id="badgeSaldoMonedero">$0.00</span>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small">Usar del monedero ($)</label>
                                        <input type="number" name="monto_monedero" id="inputMonedero"
                                               class="form-control" min="0" step="0.01" value="0">
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-xs btn-warning" onclick="usarMonedero(50)">50%</button>
                                        <button type="button" class="btn btn-xs btn-warning" onclick="usarMonedero(100)">100%</button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="usarMonedero(0)">Limpiar</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Forma de pago -->
                            <div id="panelFormaPago">
                                <label class="font-weight-bold mb-2">Forma de Pago</label>
                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="forma-pago-card active" data-forma="EFECTIVO">
                                            <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                                            <div class="small mt-1">Efectivo</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="forma-pago-card" data-forma="TARJETA">
                                            <i class="fas fa-credit-card fa-2x text-primary"></i>
                                            <div class="small mt-1">Tarjeta</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="forma-pago-card" data-forma="TRANSFERENCIA">
                                            <i class="fas fa-university fa-2x text-info"></i>
                                            <div class="small mt-1">Transfer.</div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="forma_pago" id="inputFormaPago" value="EFECTIVO">
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel Resumen / Cobro -->
            <div class="col-lg-5">
                <div class="card card-outline card-warning" id="cardResumen">
                    <div class="card-header bg-gradient-warning text-white">
                        <h3 class="card-title"><i class="fas fa-receipt mr-1"></i> Resumen de Venta</h3>
                    </div>
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <span class="text-muted">TOTAL A COBRAR</span>
                            <h1 class="display-4 font-weight-bold text-success" id="totalCobrar">$0.00</h1>
                        </div>

                        <table class="table table-sm text-left">
                            <tr>
                                <td class="text-muted">Precio:</td>
                                <td class="text-right" id="resPrecio">$0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Descuento:</td>
                                <td class="text-right text-danger" id="resDescuento">-$0.00</td>
                            </tr>
                            <tr id="resMonederoRow" style="display:none;">
                                <td class="text-muted"><i class="fas fa-wallet text-warning"></i> Monedero:</td>
                                <td class="text-right text-warning" id="resMonedero">-$0.00</td>
                            </tr>
                            <tr class="border-top">
                                <td class="font-weight-bold">A pagar en <?= '<span id="resFormaPago">Efectivo</span>' ?>:</td>
                                <td class="text-right font-weight-bold text-success" id="resEfectivo">$0.00</td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        <button type="button" id="btnVender" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-cash-register mr-2"></i> Vender Entrada
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
.forma-pago-card {
    border: 2px solid #dee2e6;
    border-radius: 10px;
    padding: 15px 10px;
    cursor: pointer;
    transition: all 0.2s;
}
.forma-pago-card:hover { border-color: #adb5bd; background: #f8f9fa; }
.forma-pago-card.active { border-color: #28a745; background: #e8f5e9; box-shadow: 0 0 0 3px rgba(40,167,69,0.15); }
</style>

<script>
var saldoMonedero = 0;

// Forma de pago
document.querySelectorAll('.forma-pago-card').forEach(function(card) {
    card.addEventListener('click', function() {
        document.querySelectorAll('.forma-pago-card').forEach(function(c){ c.classList.remove('active'); });
        this.classList.add('active');
        document.getElementById('inputFormaPago').value = this.dataset.forma;
        var formaLabels = {EFECTIVO:'Efectivo',TARJETA:'Tarjeta',TRANSFERENCIA:'Transferencia'};
        document.getElementById('resFormaPago').textContent = formaLabels[this.dataset.forma] || this.dataset.forma;
        recalcular();
    });
});

// Instalación → cargar tarifas
document.getElementById('selInstalacion').addEventListener('change', function() {
    var instId = this.value;
    if (!instId) {
        document.getElementById('tarifasRapidas').style.display = 'none';
        return;
    }
    fetch('<?= url('instalaciones', 'entrada', 'obtenerTarifas') ?>&instalacion_id=' + instId)
        .then(function(r){ return r.json(); })
        .then(function(data) {
            if (data.success && data.data.tarifas.length > 0) {
                var html = '';
                data.data.tarifas.forEach(function(t) {
                    html += '<button type="button" class="btn btn-outline-primary btn-sm mr-1 mb-1 btn-tarifa" data-precio="' + t.ent_tar_precio + '" data-tipo="' + t.ent_tar_tipo + '">';
                    html += t.ent_tar_nombre + ' — $' + parseFloat(t.ent_tar_precio).toFixed(2);
                    html += '</button>';
                });
                document.getElementById('tarifaBtns').innerHTML = html;
                document.getElementById('tarifasRapidas').style.display = 'block';
                bindTarifaBtns();
            } else {
                document.getElementById('tarifasRapidas').style.display = 'none';
            }
        });
});

function bindTarifaBtns() {
    document.querySelectorAll('.btn-tarifa').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('inputPrecio').value = this.dataset.precio;
            document.getElementById('selTipo').value = this.dataset.tipo;
            recalcular();
        });
    });
}

// Tipo cortesía
document.getElementById('selTipo').addEventListener('change', function() {
    if (this.value === 'CORTESIA') {
        document.getElementById('inputPrecio').value = '0';
        document.getElementById('inputDescuento').value = '0';
        document.getElementById('panelFormaPago').style.display = 'none';
    } else {
        document.getElementById('panelFormaPago').style.display = 'block';
    }
    recalcular();
});

// Cliente → monedero
document.getElementById('selCliente').addEventListener('change', function() {
    var clienteId = this.value;
    if (clienteId > 0) {
        fetch('<?= url('reservas', 'pago', 'saldoCliente') ?>&cliente_id=' + clienteId)
            .then(function(r){ return r.json(); })
            .then(function(data) {
                if (data.success) {
                    saldoMonedero = parseFloat(data.data.saldo);
                    document.getElementById('badgeSaldoMonedero').textContent = data.data.saldo_fmt;
                    document.getElementById('panelMonedero').style.display = saldoMonedero > 0 ? 'block' : 'none';
                }
            });
    } else {
        saldoMonedero = 0;
        document.getElementById('panelMonedero').style.display = 'none';
        document.getElementById('inputMonedero').value = 0;
    }
    recalcular();
});

function usarMonedero(pct) {
    var total = parseFloat(document.getElementById('inputPrecio').value) - parseFloat(document.getElementById('inputDescuento').value);
    total = Math.max(0, total);
    if (pct === 0) {
        document.getElementById('inputMonedero').value = 0;
    } else {
        var usar = Math.min(saldoMonedero, total * pct / 100);
        document.getElementById('inputMonedero').value = usar.toFixed(2);
    }
    recalcular();
}

// Recalcular en tiempo real
function recalcular() {
    var precio = parseFloat(document.getElementById('inputPrecio').value) || 0;
    var descuento = parseFloat(document.getElementById('inputDescuento').value) || 0;
    var monedero = parseFloat(document.getElementById('inputMonedero').value) || 0;
    var tipo = document.getElementById('selTipo').value;

    if (tipo === 'CORTESIA') {
        precio = 0; descuento = 0; monedero = 0;
    }

    var total = Math.max(0, precio - descuento);
    monedero = Math.min(monedero, total, saldoMonedero);
    var efectivo = Math.max(0, total - monedero);

    document.getElementById('displayTotal').value = '$' + total.toFixed(2);
    document.getElementById('totalCobrar').textContent = '$' + efectivo.toFixed(2);
    document.getElementById('resPrecio').textContent = '$' + precio.toFixed(2);
    document.getElementById('resDescuento').textContent = '-$' + descuento.toFixed(2);
    document.getElementById('resMonedero').textContent = '-$' + monedero.toFixed(2);
    document.getElementById('resMonederoRow').style.display = monedero > 0 ? '' : 'none';
    document.getElementById('resEfectivo').textContent = '$' + efectivo.toFixed(2);
}

document.getElementById('inputPrecio').addEventListener('input', recalcular);
document.getElementById('inputDescuento').addEventListener('input', recalcular);
document.getElementById('inputMonedero').addEventListener('input', recalcular);

// Submit
document.getElementById('btnVender').addEventListener('click', function() {
    var inst = document.getElementById('selInstalacion').value;
    if (!inst) {
        Swal.fire('Error', 'Seleccione una instalación', 'error');
        return;
    }

    var tipo = document.getElementById('selTipo').value;
    var precio = parseFloat(document.getElementById('inputPrecio').value) || 0;
    if (tipo !== 'CORTESIA' && precio <= 0) {
        Swal.fire('Error', 'Ingrese un precio válido', 'error');
        return;
    }

    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...';

    var form = new FormData(document.getElementById('formVenta'));

    fetch('<?= url('instalaciones', 'entrada', 'guardar') ?>', {
        method: 'POST',
        body: form
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            Swal.fire({
                title: '¡Entrada Vendida!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'Ver Ticket'
            }).then(function() {
                if (data.data && data.data.redirect) {
                    window.location.href = data.data.redirect;
                } else {
                    location.reload();
                }
            });
        } else {
            Swal.fire('Error', data.message || 'No se pudo vender', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-cash-register mr-2"></i> Vender Entrada';
        }
    })
    .catch(function() {
        Swal.fire('Error', 'Error de comunicación', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-cash-register mr-2"></i> Vender Entrada';
    });
});
</script>
