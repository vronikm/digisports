<?php
/**
 * Vista: Crear Factura desde Reserva
 * Formulario para crear factura a partir de una reserva confirmada
 */
?>

<div class="container-fluid my-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">
                <i class="fas fa-file-invoice"></i>
                Nueva Factura
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= url('facturacion', 'factura', 'index') ?>"
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    
    <!-- Formulario Creación Factura -->
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= url('facturacion', 'factura', 'guardar') ?>" id="formFactura">
                
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <!-- Seleccionar Reserva -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="reserva_id" class="form-label">
                            <strong>Seleccionar Reserva *</strong>
                        </label>
                        <select name="reserva_id"
                                id="reserva_id"
                                class="form-select"
                                required
                                onchange="cargarDetallesReserva(this.value)">
                            <option value="">-- Seleccionar Reserva --</option>
                            <?php foreach ($reservas_disponibles as $reserva): ?>
                                <option value="<?= htmlspecialchars($reserva['reserva_id']) ?>">
                                    RES-<?= str_pad($reserva['reserva_id'], 5, '0', STR_PAD_LEFT) ?> - 
                                    <?= htmlspecialchars($reserva['nombre_cliente']) ?> - 
                                    $<?= number_format($reserva['precio_total'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            Solo se muestran reservas confirmadas sin factura
                        </small>
                    </div>
                </div>
                
                <!-- Detalles de la Reserva -->
                <div id="detallesReserva" style="display: none;">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <strong>Número de Reserva</strong>
                            </label>
                            <input type="text" class="form-control" id="numeroReserva" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <strong>Fecha Reserva</strong>
                            </label>
                            <input type="text" class="form-control" id="fechaReserva" readonly>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <strong>Cliente</strong>
                            </label>
                            <input type="text" class="form-control" id="cliente" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <strong>Correo</strong>
                            </label>
                            <input type="email" class="form-control" id="emailCliente">
                        </div>
                    </div>
                    
                    <!-- Tabla de Líneas -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">
                                <strong>Detalles de la Reserva</strong>
                            </label>
                            <div class="table-responsive">
                                <table class="table table-sm" id="tablaDatos">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Descripción</th>
                                            <th class="text-end" style="width: 100px;">Cantidad</th>
                                            <th class="text-end" style="width: 120px;">Precio Unit.</th>
                                            <th class="text-end" style="width: 120px;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="lineasBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Cálculos -->
                    <div class="row mb-3">
                        <div class="col-md-6 ms-auto">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span class="font-monospace">$<strong id="subtotal">0.00</strong></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>
                                            IVA (
                                            <input type="number"
                                                   class="form-control-sm"
                                                   id="porcentajeIVA"
                                                   value="15"
                                                   style="width: 50px; display: inline-block;"
                                                   onchange="calcularTotales()">
                                            %):
                                        </span>
                                        <span class="font-monospace">$<strong id="iva">0.00</strong></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Descuento:</span>
                                        <span class="font-monospace">
                                            $<input type="number"
                                                    id="descuento"
                                                    step="0.01"
                                                    value="0"
                                                    onchange="calcularTotales()"
                                                    style="width: 100px; display: inline-block;"
                                                    class="form-control-sm">
                                        </span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong>Total:</strong>
                                        <strong class="text-primary" style="font-size: 1.25rem;">
                                            $<span id="total">0.00</span>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Forma de Pago -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="forma_pago_id" class="form-label">
                                <strong>Forma de Pago</strong>
                            </label>
                            <select name="forma_pago_id" id="forma_pago_id" class="form-select">
                                <option value="">-- Sin especificar --</option>
                                <?php foreach ($formas_pago as $forma): ?>
                                    <option value="<?= htmlspecialchars($forma['forma_pago_id']) ?>">
                                        <?= htmlspecialchars($forma['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Fecha Vencimiento -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha_vencimiento" class="form-label">
                                <strong>Fecha de Vencimiento</strong>
                            </label>
                            <input type="date"
                                   name="fecha_vencimiento"
                                   id="fecha_vencimiento"
                                   class="form-control"
                                   value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        </div>
                    </div>
                    
                </div>
                
                <!-- Botones -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="<?= url('facturacion', 'factura', 'index') ?>"
                       class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary" id="btnGuardar" disabled>
                        <i class="fas fa-save"></i> Crear Factura
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cargarDetallesReserva(reservaId) {
    if (!reservaId) {
        document.getElementById('detallesReserva').style.display = 'none';
        document.getElementById('btnGuardar').disabled = true;
        return;
    }
    
    // Aquí iría AJAX para cargar detalles
    // Por ahora mostrar la sección
    document.getElementById('detallesReserva').style.display = 'block';
    document.getElementById('btnGuardar').disabled = false;
    
    // Llamar a AJAX para obtener detalles
    fetch('<?= url("facturacion", "factura", "obtenerDetallesReserva") ?>?id=' + reservaId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const res = data.reserva;
                
                // Llenar datos generales
                document.getElementById('numeroReserva').value = 'RES-' + String(res.reserva_id).padStart(5, '0');
                document.getElementById('fechaReserva').value = new Date(res.fecha_creacion).toLocaleDateString();
                document.getElementById('cliente').value = res.nombre_cliente;
                document.getElementById('emailCliente').value = res.email_cliente || '';
                document.getElementById('emailCliente').name = 'email_cliente';
                
                // Llenar líneas
                const tbody = document.getElementById('lineasBody');
                tbody.innerHTML = '';
                
                data.lineas.forEach(linea => {
                    const row = tbody.insertRow();
                    row.innerHTML = `
                        <td>${linea.descripcion}</td>
                        <td class="text-end">${linea.cantidad}</td>
                        <td class="text-end">$${parseFloat(linea.precio_unitario).toFixed(2)}</td>
                        <td class="text-end">$${parseFloat(linea.total).toFixed(2)}</td>
                    `;
                });
                
                // Asignar valores
                document.getElementById('subtotal').textContent = parseFloat(res.precio_total).toFixed(2);
                document.querySelectorAll('input[name="linea_id[]"]').forEach(el => el.remove());
                
                // Crear campos hidden para líneas
                data.lineas.forEach(linea => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'lineas[]';
                    input.value = JSON.stringify({
                        descripcion: linea.descripcion,
                        cantidad: linea.cantidad,
                        precio_unitario: linea.precio_unitario,
                        tarifa_id: linea.tarifa_id
                    });
                    document.getElementById('formFactura').appendChild(input);
                });
                
                calcularTotales();
            }
        })
        .catch(error => console.error('Error:', error));
}

function calcularTotales() {
    const subtotal = parseFloat(document.getElementById('subtotal').textContent) || 0;
    const porcentajeIVA = parseFloat(document.getElementById('porcentajeIVA').value) || 0;
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;
    
    const iva = subtotal * (porcentajeIVA / 100);
    const total = (subtotal + iva) - descuento;
    
    document.getElementById('iva').textContent = iva.toFixed(2);
    document.getElementById('total').textContent = total.toFixed(2);
}
</script>
