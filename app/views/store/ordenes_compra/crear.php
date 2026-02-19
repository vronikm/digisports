<?php
/**
 * DigiSports Store - Crear Orden de Compra
 */
$proveedores = $proveedores ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-file-invoice mr-2" style="color:<?= $moduloColor ?>"></i>Nueva Orden de Compra</h1></div>
            <div class="col-sm-6"><div class="float-sm-right">
                <a href="<?= url('store', 'ordenCompra', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
            </div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form id="formOrdenCompra">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <div class="row">
                <div class="col-md-8">
                    <!-- Info -->
                    <div class="card shadow-sm">
                        <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-info-circle mr-1"></i> Información</h6></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small">Proveedor *</label>
                                        <select name="proveedor_id" id="proveedorId" class="form-control form-control-sm" required>
                                            <option value="">Seleccione proveedor</option>
                                            <?php foreach ($proveedores as $p): ?>
                                            <option value="<?= $p['prv_proveedor_id'] ?>"><?= htmlspecialchars($p['prv_razon_social']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group"><label class="small">Fecha Entrega Esperada</label><input type="date" name="fecha_entrega" class="form-control form-control-sm"></div>
                                </div>
                            </div>
                            <div class="form-group"><label class="small">Notas</label><textarea name="notas" rows="2" class="form-control form-control-sm" placeholder="Observaciones..."></textarea></div>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="card shadow-sm">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-list mr-1"></i> Productos</h6>
                            <button type="button" class="btn btn-xs btn-outline-primary" onclick="agregarItem()"><i class="fas fa-plus mr-1"></i> Agregar</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0" id="tablaItems">
                                <thead class="thead-light">
                                    <tr><th>Producto</th><th width="100">Cantidad</th><th width="120">Costo Unit.</th><th width="120" class="text-right">Subtotal</th><th width="50"></th></tr>
                                </thead>
                                <tbody id="tbodyItems">
                                    <tr id="sinItems"><td colspan="5" class="text-center text-muted py-3">Agregue productos a la orden</td></tr>
                                </tbody>
                                <tfoot class="thead-light">
                                    <tr><td colspan="3" class="text-right font-weight-bold">Total:</td><td class="text-right font-weight-bold" id="totalOrden">$0.00</td><td></td></tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header py-2" style="background:<?= $moduloColor ?>;color:white"><h6 class="mb-0"><i class="fas fa-calculator mr-1"></i> Resumen</h6></div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tr><td>Subtotal</td><td class="text-right" id="resSubtotal">$0.00</td></tr>
                                <tr><td>IVA (15%)</td><td class="text-right" id="resIva">$0.00</td></tr>
                                <tr class="border-top"><td><strong>TOTAL</strong></td><td class="text-right font-weight-bold" id="resTotal" style="font-size:1.2rem;color:<?= $moduloColor ?>">$0.00</td></tr>
                            </table>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-block" style="background:<?= $moduloColor ?>;color:white"><i class="fas fa-save mr-1"></i> Crear Orden de Compra</button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Modal buscar producto -->
<div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2"><h5 class="modal-title"><i class="fas fa-search mr-1"></i> Buscar Producto</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <input type="text" id="buscarProd" class="form-control form-control-sm mb-3" placeholder="Nombre, código, SKU...">
                <div id="resultadosProd" style="max-height:300px;overflow-y:auto"></div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
var items = [];
var itemCounter = 0;

function agregarItem() {
    $('#modalProducto').modal('show');
    document.getElementById('buscarProd').value = '';
    document.getElementById('resultadosProd').innerHTML = '';
    setTimeout(function() { document.getElementById('buscarProd').focus(); }, 300);
}

document.getElementById('buscarProd').addEventListener('input', function() {
    var q = this.value.trim();
    if (q.length < 2) { document.getElementById('resultadosProd').innerHTML = ''; return; }
    fetch('<?= url('store', 'producto', 'buscar') ?>&q=' + encodeURIComponent(q))
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (!d.success || !d.productos.length) {
            document.getElementById('resultadosProd').innerHTML = '<div class="text-center text-muted py-3">Sin resultados</div>';
            return;
        }
        var html = '<div class="list-group">';
        d.productos.forEach(function(p) {
            html += '<button type="button" class="list-group-item list-group-item-action" onclick=\'seleccionarProducto(' + JSON.stringify(p) + ')\'>'
                + '<strong>' + (p.nombre || p.pro_nombre) + '</strong> <small class="text-muted ml-2">' + (p.codigo || p.pro_codigo || '') + '</small>'
                + '<br><small>Precio compra: $' + parseFloat(p.precio_compra || p.pro_precio_compra || 0).toFixed(2) + ' | Stock: ' + (p.stock || 0) + '</small>'
                + '</button>';
        });
        html += '</div>';
        document.getElementById('resultadosProd').innerHTML = html;
    });
});

function seleccionarProducto(p) {
    itemCounter++;
    var id = p.id || p.pro_producto_id;
    var nombre = p.nombre || p.pro_nombre;
    var costo = parseFloat(p.precio_compra || p.pro_precio_compra || 0);
    items.push({ idx: itemCounter, producto_id: id, nombre: nombre, cantidad: 1, costo_unitario: costo });
    $('#modalProducto').modal('hide');
    renderItems();
}

function renderItems() {
    var tbody = document.getElementById('tbodyItems');
    if (items.length === 0) {
        tbody.innerHTML = '<tr id="sinItems"><td colspan="5" class="text-center text-muted py-3">Agregue productos a la orden</td></tr>';
        calcularTotales();
        return;
    }
    var html = '';
    items.forEach(function(it, i) {
        var sub = it.cantidad * it.costo_unitario;
        html += '<tr>'
            + '<td>' + it.nombre + '</td>'
            + '<td><input type="number" class="form-control form-control-sm" value="' + it.cantidad + '" min="1" onchange="actualizarItem(' + i + ',\'cantidad\',this.value)"></td>'
            + '<td><input type="number" class="form-control form-control-sm" value="' + it.costo_unitario.toFixed(2) + '" min="0" step="0.01" onchange="actualizarItem(' + i + ',\'costo_unitario\',this.value)"></td>'
            + '<td class="text-right">$' + sub.toFixed(2) + '</td>'
            + '<td><button type="button" class="btn btn-xs btn-outline-danger" onclick="eliminarItem(' + i + ')"><i class="fas fa-times"></i></button></td>'
            + '</tr>';
    });
    tbody.innerHTML = html;
    calcularTotales();
}

function actualizarItem(i, campo, valor) {
    items[i][campo] = parseFloat(valor) || 0;
    renderItems();
}

function eliminarItem(i) {
    items.splice(i, 1);
    renderItems();
}

function calcularTotales() {
    var subtotal = 0;
    items.forEach(function(it) { subtotal += it.cantidad * it.costo_unitario; });
    var iva = Math.round(subtotal * 0.15 * 100) / 100;
    var total = Math.round((subtotal + iva) * 100) / 100;
    document.getElementById('totalOrden').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('resSubtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('resIva').textContent = '$' + iva.toFixed(2);
    document.getElementById('resTotal').textContent = '$' + total.toFixed(2);
}

document.getElementById('formOrdenCompra').addEventListener('submit', function(e) {
    e.preventDefault();
    if (items.length === 0) { Swal.fire({ icon: 'warning', title: 'Agregue al menos un producto' }); return; }
    var fd = new FormData(this);
    fd.append('items', JSON.stringify(items));
    fetch('<?= url('store', 'ordenCompra', 'crear') ?>', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            Swal.fire({ icon: 'success', title: d.message, timer: 2000, showConfirmButton: false })
            .then(function() { window.location.href = '<?= url('store', 'ordenCompra', 'index') ?>'; });
        } else { Swal.fire({ icon: 'error', title: 'Error', text: d.message }); }
    }).catch(function() { Swal.fire({ icon: 'error', title: 'Error de conexión' }); });
});
</script>
<?php $scripts = ob_get_clean(); ?>
