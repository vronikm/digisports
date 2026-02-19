<?php
/**
 * DigiSports Store - Arqueo de Caja
 */
$turno       = $turno ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-calculator mr-2" style="color:<?= $moduloColor ?>"></i>Arqueo de Caja</h1>
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
        <form method="POST" action="<?= url('store', 'caja', 'arqueo') ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <input type="hidden" name="turno_id" value="<?= $turno['tur_turno_id'] ?? 0 ?>">

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-coins mr-2"></i>Conteo de Denominaciones</h3></div>
                        <div class="card-body">
                            <h6 class="text-muted mb-3">Billetes</h6>
                            <div class="row mb-4">
                                <?php
                                $billetes = [100, 50, 20, 10, 5, 1];
                                foreach ($billetes as $b):
                                ?>
                                <div class="col-md-4 col-6 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" style="min-width:60px;">$<?= $b ?></span>
                                        </div>
                                        <input type="number" min="0" name="billete_<?= $b ?>" class="form-control denominacion" data-valor="<?= $b ?>" value="0">
                                        <div class="input-group-append">
                                            <span class="input-group-text subtotal-denom" id="sub_b<?= $b ?>">$0</span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <h6 class="text-muted mb-3">Monedas</h6>
                            <div class="row">
                                <?php
                                $monedas = ['1.00' => 1, '0.50' => 0.50, '0.25' => 0.25, '0.10' => 0.10, '0.05' => 0.05, '0.01' => 0.01];
                                foreach ($monedas as $label => $val):
                                ?>
                                <div class="col-md-4 col-6 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" style="min-width:60px;">$<?= $label ?></span>
                                        </div>
                                        <input type="number" min="0" name="moneda_<?= str_replace('.', '_', $label) ?>" class="form-control denominacion" data-valor="<?= $val ?>" value="0">
                                        <div class="input-group-append">
                                            <span class="input-group-text subtotal-denom" id="sub_m<?= str_replace('.', '_', $label) ?>">$0</span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-balance-scale mr-2"></i>Resumen</h3></div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <small class="text-muted d-block">Total Contado</small>
                                <div class="h2 mb-0 text-primary" id="totalContado">$0.00</div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Esperado en Caja</small>
                                <div class="h4 mb-0">$<?= number_format(($turno['tur_monto_apertura'] ?? 0) + ($turno['tur_total_efectivo'] ?? 0), 2) ?></div>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <small class="text-muted d-block">Diferencia</small>
                                <div class="h3 mb-0" id="diferencia">$0.00</div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="form-group">
                                <label class="small">Observaciones</label>
                                <textarea name="observacion" class="form-control" rows="2" placeholder="Notas del arqueo..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-block" style="background:<?= $moduloColor ?>;color:white;">
                                <i class="fas fa-save mr-1"></i> Guardar Arqueo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php ob_start(); ?>
<script>
var esperado = <?= ($turno['tur_monto_apertura'] ?? 0) + ($turno['tur_total_efectivo'] ?? 0) ?>;

document.querySelectorAll('.denominacion').forEach(function(input) {
    input.addEventListener('input', calcularTotal);
});

function calcularTotal() {
    var total = 0;
    document.querySelectorAll('.denominacion').forEach(function(input) {
        var valor = parseFloat(input.dataset.valor);
        var cantidad = parseInt(input.value) || 0;
        var subtotal = valor * cantidad;
        total += subtotal;
        var id = 'sub_' + input.name.replace('billete_', 'b').replace('moneda_', 'm');
        var subEl = document.getElementById(id);
        if (subEl) subEl.textContent = '$' + subtotal.toFixed(2);
    });

    document.getElementById('totalContado').textContent = '$' + total.toFixed(2);
    var dif = total - esperado;
    var difEl = document.getElementById('diferencia');
    difEl.textContent = '$' + dif.toFixed(2);
    difEl.className = 'h3 mb-0 ' + (dif === 0 ? 'text-success' : (dif > 0 ? 'text-info' : 'text-danger'));
}
</script>
<?php $scripts = ob_get_clean(); ?>
