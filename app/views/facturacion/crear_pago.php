<?php
/**
 * Vista: Registrar Pago
 * Formulario para registrar nuevos pagos
 */
?>

<div class="container-fluid my-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">
                <i class="fas fa-money-check"></i>
                Registrar Pago
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= url('facturacion', 'factura', 'ver', ['id' => $factura['factura_id']]) ?>"
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    
    <!-- Información Factura -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h5 class="mb-0">Información de la Factura</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong>Número Factura:</strong><br>
                        <code><?= htmlspecialchars($factura['numero_factura']) ?></code>
                    </p>
                    <p class="mb-0">
                        <strong>Cliente:</strong><br>
                        <?= htmlspecialchars($factura['nombre_cliente']) ?>
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-2">
                        <strong>Total Factura:</strong><br>
                        <span class="text-primary" style="font-size: 1.25rem;">
                            $<?= number_format($factura['total'], 2) ?>
                        </span>
                    </p>
                    <p class="mb-0">
                        <strong>Pagado:</strong><br>
                        <span class="text-success">
                            $<?= number_format($total_pagado, 2) ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alerta Monto Pendiente -->
    <div class="alert alert-info" role="alert">
        <strong>Monto Pendiente de Pago:</strong>
        <span style="font-size: 1.25rem;" class="float-end">
            $<?= number_format($monto_pendiente, 2) ?>
        </span>
    </div>
    
    <!-- Formulario Pago -->
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= url('facturacion', 'pago', 'guardar') ?>" id="formPago">
                
                <input type="hidden" name="factura_id" value="<?= htmlspecialchars($factura['factura_id']) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <!-- Monto -->
                <div class="mb-3">
                    <label for="monto" class="form-label">
                        <strong>Monto a Pagar *</strong>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               class="form-control"
                               id="monto"
                               name="monto"
                               step="0.01"
                               max="<?= $monto_pendiente ?>"
                               min="0.01"
                               value="<?= $monto_pendiente ?>"
                               required>
                    </div>
                    <small class="text-muted">
                        Máximo permitido: $<?= number_format($monto_pendiente, 2) ?>
                    </small>
                </div>
                
                <!-- Forma de Pago -->
                <div class="mb-3">
                    <label for="forma_pago_id" class="form-label">
                        <strong>Forma de Pago *</strong>
                    </label>
                    <select name="forma_pago_id" id="forma_pago_id" class="form-select" required>
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($formas_pago as $forma): ?>
                            <option value="<?= htmlspecialchars($forma['forma_pago_id']) ?>">
                                <?= htmlspecialchars($forma['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Referencia Pago -->
                <div class="mb-3">
                    <label for="referencia_pago" class="form-label">
                        Referencia de Pago
                    </label>
                    <input type="text"
                           class="form-control"
                           id="referencia_pago"
                           name="referencia_pago"
                           placeholder="Número de transacción, cheque, etc."
                           maxlength="100">
                    <small class="text-muted">
                        Ej: Número de autorización, cheque, referencia bancaria
                    </small>
                </div>
                
                <!-- Fecha Pago -->
                <div class="mb-3">
                    <label for="fecha_pago" class="form-label">
                        <strong>Fecha de Pago *</strong>
                    </label>
                    <input type="date"
                           class="form-control"
                           id="fecha_pago"
                           name="fecha_pago"
                           value="<?= date('Y-m-d') ?>"
                           required>
                </div>
                
                <!-- Observaciones -->
                <div class="mb-3">
                    <label for="observaciones" class="form-label">
                        Observaciones
                    </label>
                    <textarea class="form-control"
                              id="observaciones"
                              name="observaciones"
                              rows="3"
                              placeholder="Notas adicionales..."
                              maxlength="500"></textarea>
                </div>
                
                <!-- Botones -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?= url('facturacion', 'factura', 'ver', ['id' => $factura['factura_id']]) ?>"
                       class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Registrar Pago
                    </button>
                </div>
                
            </form>
        </div>
    </div>
    
    <!-- Resumen Pagos Anteriores -->
    <?php if (!empty($pagos_anteriores)): ?>
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">Pagos Anteriores</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Forma</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagos_anteriores as $pago): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></td>
                                <td>$<?= number_format($pago['monto'], 2) ?></td>
                                <td><?= htmlspecialchars($pago['forma_pago_nombre']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $pago['estado'] === 'CONFIRMADO' ? 'success' : 'danger' ?>">
                                        <?= htmlspecialchars($pago['estado']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('monto').addEventListener('input', function() {
    const monto = parseFloat(this.value) || 0;
    const maximo = <?= $monto_pendiente ?>;
    
    if (monto > maximo) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
    } else if (monto > 0) {
        this.classList.add('is-valid');
        this.classList.remove('is-invalid');
    } else {
        this.classList.remove('is-valid', 'is-invalid');
    }
});
</script>
