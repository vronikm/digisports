<?php
/**
 * DigiSports Arena — Vista: Crear Monedero
 * Formulario para crear un nuevo monedero con recarga inicial
 */

$clientesSin = $clientes_sin_monedero ?? [];
$paquetes    = $paquetes ?? [];
$csrfToken   = $csrf_token ?? '';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-plus-circle mr-2 text-primary"></i>
                    Nuevo Monedero
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
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form id="formCrearMonedero" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <!-- Selección de cliente -->
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user mr-2"></i>Seleccionar Cliente</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($clientesSin)): ?>
                            <div class="form-group">
                                <label>Cliente sin monedero activo</label>
                                <select name="cliente_id" id="selectCliente" class="form-control select2" required>
                                    <option value="">— Seleccionar cliente —</option>
                                    <?php foreach ($clientesSin as $c): ?>
                                    <option value="<?= $c['cliente_id'] ?>" 
                                            data-email="<?= htmlspecialchars($c['email'] ?? '') ?>"
                                            data-id="<?= htmlspecialchars($c['identificacion'] ?? '') ?>">
                                        <?= htmlspecialchars($c['nombre_completo']) ?>
                                        <?= !empty($c['identificacion']) ? " — {$c['identificacion']}" : '' ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="clienteInfo" class="alert alert-info" style="display:none;">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span id="clienteInfoText"></span>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Todos los clientes ya tienen un monedero activo.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Datos del monedero -->
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-wallet mr-2"></i>Configuración del Monedero</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Recarga Inicial ($) <span class="text-danger">*</span></label>
                                        <input type="number" name="monto_inicial" id="montoInicial" 
                                               class="form-control form-control-lg text-center"
                                               min="1" step="0.01" value="10.00" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Forma de Pago</label>
                                        <select name="forma_pago" class="form-control">
                                            <option value="EFECTIVO">💵 Efectivo</option>
                                            <option value="TARJETA">💳 Tarjeta</option>
                                            <option value="TRANSFERENCIA">🏦 Transferencia</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Vigencia (días)</label>
                                <input type="number" name="dias_vigencia" class="form-control" 
                                       min="30" max="730" value="365">
                                <small class="form-text text-muted">El monedero expirará después de estos días.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Paquetes disponibles -->
                    <?php if (!empty($paquetes)): ?>
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-gift mr-2"></i>Paquetes con Descuento</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($paquetes as $p): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border paquete-card" 
                                         data-precio="<?= $p['paq_precio_paquete'] ?>"
                                         style="cursor: pointer;">
                                        <div class="card-body text-center">
                                            <?php if ($p['paq_descuento_pct'] > 0): ?>
                                            <span class="badge badge-danger mb-2" style="font-size: .85rem;">
                                                -<?= $p['paq_descuento_pct'] ?>%
                                            </span>
                                            <?php endif; ?>
                                            <h5><?= htmlspecialchars($p['paq_nombre']) ?></h5>
                                            <p class="text-muted small"><?= htmlspecialchars($p['paq_descripcion'] ?? '') ?></p>
                                            <div>
                                                <del class="text-muted">$<?= number_format($p['paq_precio_normal'], 2) ?></del>
                                                <strong class="text-success" style="font-size: 1.3em;">
                                                    $<?= number_format($p['paq_precio_paquete'], 2) ?>
                                                </strong>
                                            </div>
                                            <small class="text-muted"><?= $p['paq_horas_incluidas'] ?> horas · <?= $p['paq_dias_vigencia'] ?> días</small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Botones -->
                    <div class="text-right mb-4">
                        <a href="<?= url('reservas', 'abon', 'index') ?>" class="btn btn-secondary mr-2">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg" <?= empty($clientesSin) ? 'disabled' : '' ?>>
                            <i class="fas fa-wallet mr-1"></i> Crear Monedero
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Selección de paquete → auto-rellenar monto
    document.querySelectorAll('.paquete-card').forEach(function(card) {
        card.addEventListener('click', function() {
            document.querySelectorAll('.paquete-card').forEach(function(c) { c.classList.remove('border-primary', 'shadow'); });
            this.classList.add('border-primary', 'shadow');
            document.getElementById('montoInicial').value = this.dataset.precio;
        });
    });

    // Info del cliente seleccionado
    var selectCliente = document.getElementById('selectCliente');
    if (selectCliente) {
        selectCliente.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            var info = document.getElementById('clienteInfo');
            if (this.value) {
                var text = '';
                if (opt.dataset.email) text += 'Email: ' + opt.dataset.email;
                if (opt.dataset.id) text += (text ? ' | ' : '') + 'ID: ' + opt.dataset.id;
                document.getElementById('clienteInfoText').textContent = text;
                info.style.display = text ? 'block' : 'none';
            } else {
                info.style.display = 'none';
            }
        });
    }

    // Submit AJAX
    document.getElementById('formCrearMonedero').addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Creando...';

        fetch('<?= url('reservas', 'abon', 'guardar') ?>', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'success' || data.success) {
                    Swal.fire('¡Monedero creado!', data.message || '', 'success')
                        .then(function() {
                            if (data.data && data.data.redirect) {
                                window.location.href = data.data.redirect;
                            } else {
                                window.location.href = '<?= url('reservas', 'abon', 'index') ?>';
                            }
                        });
                } else {
                    Swal.fire('Error', data.message || 'Error al crear', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-wallet mr-1"></i> Crear Monedero';
                }
            })
            .catch(function() {
                Swal.fire('Error', 'Error de conexión', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-wallet mr-1"></i> Crear Monedero';
            });
    });
});
</script>
