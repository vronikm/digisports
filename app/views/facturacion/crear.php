<?php
/**
 * Vista: Crear Factura
 * Formulario para crear factura a partir de un origen (reservas, libre, etc.)
 */
?>

<div class="container-fluid my-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">
                <i class="fas fa-file-invoice"></i>
                Nueva Factura <?= $origen_modulo !== 'libre' ? 'desde ' . ucfirst($origen_modulo) : '(Libre)' ?>
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
            <form method="POST" action="<?= url('facturacion', 'factura', 'guardar') ?>" id="formFactura" onsubmit="prepararEnvio()">
                
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="origen_modulo" value="<?= htmlspecialchars($origen_modulo) ?>">
                <input type="hidden" name="origen_id" value="<?= htmlspecialchars($origen_id) ?>">
                <input type="hidden" name="lineas_json" id="lineas_json" value="">
                
                <?php if ($origen_modulo === 'libre' || empty($origen_modulo)): ?>
                <!-- Seleccionar Cliente para Factura Libre -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="cliente_id" class="form-label">
                            <strong>Seleccionar Cliente *</strong>
                        </label>
                        <select name="cliente_id" id="cliente_id" class="form-select" required>
                            <option value="">-- Seleccionar Cliente --</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= htmlspecialchars($c['id']) ?>">
                                    <?= htmlspecialchars(($c['identificacion'] ?? '') . ' - ' . ($c['nombre'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php else: ?>
                <!-- Cliente preseleccionado por el origen -->
                <input type="hidden" name="cliente_id" value="<?= htmlspecialchars($cliente_id ?? '') ?>">
                <?php endif; ?>
                
                <!-- Detalles del Origen / Factura -->
                <div id="detallesFactura">
                    
                    <?php if ($origen_modulo !== 'libre' && isset($origen_detalle)): ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <strong>Referencia Origen</strong>
                            </label>
                            <input type="text" class="form-control" value="<?= strtoupper($origen_modulo) . '-' . str_pad($origen_id, 5, '0', STR_PAD_LEFT) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <strong>Cliente</strong>
                            </label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($cliente['cli_nombre_comercial'] ?? 'N/A') ?>" readonly>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Tabla de Líneas -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">
                                <strong>Detalles de la Factura</strong>
                            </label>
                            <div class="table-responsive">
                                <table class="table table-sm" id="tablaDatos">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Descripción</th>
                                            <th class="text-end" style="width: 100px;">Cantidad</th>
                                            <th class="text-end" style="width: 120px;">Precio Unit.</th>
                                            <th class="text-end" style="width: 120px;">Total</th>
                                            <?php if ($origen_modulo === 'libre'): ?>
                                            <th class="text-center" style="width: 60px;">Acción</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody id="lineasBody">
                                        <!-- Las líneas se renderizan por JS para mantener lógica unificada -->
                                    </tbody>
                                    <?php if ($origen_modulo === 'libre'): ?>
                                    <tfoot>
                                        <tr>
                                            <td><input type="text" id="nueva_desc" class="form-control form-control-sm" placeholder="Descripción"></td>
                                            <td><input type="number" id="nueva_cant" class="form-control form-control-sm text-end" value="1" min="1"></td>
                                            <td><input type="number" id="nuevo_precio" class="form-control form-control-sm text-end" step="0.01" value="0.00"></td>
                                            <td class="text-end"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-success" onclick="agregarLinea()"><i class="fas fa-plus"></i></button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                    <?php endif; ?>
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
                                                   name="impuesto_porcentaje"
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
                                                    name="descuento"
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
                                    <option value="<?= htmlspecialchars($forma['fpa_id']) ?>">
                                        <?= htmlspecialchars($forma['fpa_nombre']) ?>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <i class="fas fa-save"></i> Crear Factura
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let lineasFactura = [];

<?php if ($origen_modulo !== 'libre' && isset($origen_detalle['lineas'])): ?>
    <?php foreach ($origen_detalle['lineas'] as $lin): ?>
    lineasFactura.push({
        descripcion: <?= json_encode($lin['descripcion'] ?? $lin['lin_descripcion'] ?? 'Item') ?>,
        cantidad: <?= floatval($lin['cantidad'] ?? $lin['lin_cantidad'] ?? 1) ?>,
        precio_unitario: <?= floatval($lin['precio_unitario'] ?? $lin['lin_precio_unitario'] ?? 0) ?>
    });
    <?php endforeach; ?>
<?php endif; ?>

function renderizarLineas() {
    const tbody = document.getElementById('lineasBody');
    tbody.innerHTML = '';
    
    let isLibre = <?= $origen_modulo === 'libre' ? 'true' : 'false' ?>;
    
    lineasFactura.forEach((linea, index) => {
        const total = linea.cantidad * linea.precio_unitario;
        const row = tbody.insertRow();
        
        let html = `
            <td>${linea.descripcion}</td>
            <td class="text-end">${linea.cantidad}</td>
            <td class="text-end">$${parseFloat(linea.precio_unitario).toFixed(2)}</td>
            <td class="text-end">$${total.toFixed(2)}</td>
        `;
        
        if (isLibre) {
            html += `
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarLinea(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
        }
        row.innerHTML = html;
    });
    
    calcularTotales();
}

function agregarLinea() {
    const descInput = document.getElementById('nueva_desc');
    const cantInput = document.getElementById('nueva_cant');
    const precioInput = document.getElementById('nuevo_precio');
    
    const desc = descInput.value.trim();
    const cant = parseFloat(cantInput.value) || 1;
    const precio = parseFloat(precioInput.value) || 0;
    
    if (desc === '') {
        alert('Ingrese una descripción');
        return;
    }
    
    lineasFactura.push({
        descripcion: desc,
        cantidad: cant,
        precio_unitario: precio
    });
    
    descInput.value = '';
    cantInput.value = '1';
    precioInput.value = '0.00';
    
    renderizarLineas();
}

function eliminarLinea(index) {
    lineasFactura.splice(index, 1);
    renderizarLineas();
}

function calcularTotales() {
    let subtotal = 0;
    lineasFactura.forEach(l => {
        subtotal += (l.cantidad * l.precio_unitario);
    });
    
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    
    const porcentajeIVA = parseFloat(document.getElementById('porcentajeIVA').value) || 0;
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;
    
    const iva = subtotal * (porcentajeIVA / 100);
    const total = (subtotal + iva) - descuento;
    
    document.getElementById('iva').textContent = iva.toFixed(2);
    document.getElementById('total').textContent = total.toFixed(2);
    
    // Validar form
    let btn = document.getElementById('btnGuardar');
    if (lineasFactura.length === 0) {
        btn.disabled = true;
    } else {
        btn.disabled = false;
    }
}

function prepararEnvio() {
    document.getElementById('lineas_json').value = JSON.stringify(lineasFactura);
    return true;
}

// Inicializar vista
document.addEventListener('DOMContentLoaded', function() {
    renderizarLineas();
});
</script>
