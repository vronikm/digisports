<?php
/**
 * DigiSports Store - Punto de Venta (POS)
 * Interfaz completa de venta rápida con carrito, búsqueda y cobro
 */
$categorias  = $categorias ?? [];
$productos   = $productos ?? [];
$clientes    = $clientes ?? [];
$turno       = $turno ?? [];
$config      = $config ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
$ivaPct      = floatval($config['iva_porcentaje'] ?? 15);
?>

<style>
.pos-container { display:flex; height:calc(100vh - 57px); overflow:hidden; }
.pos-products { flex:1; display:flex; flex-direction:column; overflow:hidden; background:#f4f6f9; }
.pos-cart { width:380px; display:flex; flex-direction:column; background:white; border-left:1px solid #dee2e6; }
.pos-search { padding:10px 15px; background:white; border-bottom:1px solid #dee2e6; }
.pos-categories { padding:8px 15px; background:white; border-bottom:1px solid #dee2e6; white-space:nowrap; overflow-x:auto; }
.pos-categories .cat-btn { display:inline-block; padding:4px 12px; margin-right:6px; border-radius:20px; cursor:pointer; font-size:0.8rem; border:1px solid #dee2e6; background:white; transition:all 0.2s; }
.pos-categories .cat-btn.active, .pos-categories .cat-btn:hover { background:<?= $moduloColor ?>; color:white; border-color:<?= $moduloColor ?>; }
.pos-grid { flex:1; overflow-y:auto; padding:10px; display:grid; grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); gap:10px; align-content:start; }
.pos-product-card { background:white; border-radius:8px; padding:10px; cursor:pointer; text-align:center; border:2px solid transparent; transition:all 0.2s; box-shadow:0 1px 3px rgba(0,0,0,0.08); }
.pos-product-card:hover { border-color:<?= $moduloColor ?>; transform:translateY(-2px); }
.pos-product-card img { width:60px; height:60px; object-fit:cover; border-radius:6px; margin-bottom:6px; }
.pos-product-card .name { font-size:0.8rem; font-weight:600; line-height:1.2; margin-bottom:4px; max-height:2.4em; overflow:hidden; }
.pos-product-card .price { color:<?= $moduloColor ?>; font-weight:700; font-size:0.95rem; }
.pos-product-card .stock-info { font-size:0.7rem; color:#6c757d; }
.pos-cart-header { padding:12px 15px; border-bottom:1px solid #dee2e6; display:flex; align-items:center; justify-content:space-between; }
.pos-cart-items { flex:1; overflow-y:auto; padding:0; }
.cart-item { display:flex; align-items:center; padding:8px 15px; border-bottom:1px solid #f0f0f0; }
.cart-item .item-info { flex:1; }
.cart-item .item-name { font-size:0.85rem; font-weight:600; }
.cart-item .item-price { font-size:0.75rem; color:#6c757d; }
.cart-item .item-qty { display:flex; align-items:center; gap:4px; }
.cart-item .item-qty button { width:24px; height:24px; border:1px solid #dee2e6; border-radius:4px; background:white; cursor:pointer; font-size:0.8rem; display:flex; align-items:center; justify-content:center; }
.cart-item .item-qty input { width:36px; text-align:center; border:1px solid #dee2e6; border-radius:4px; font-size:0.85rem; padding:2px; }
.cart-item .item-total { min-width:65px; text-align:right; font-weight:700; font-size:0.85rem; }
.cart-item .item-remove { margin-left:6px; color:#dc3545; cursor:pointer; font-size:0.8rem; }
.pos-cart-summary { padding:12px 15px; background:#f8f9fa; border-top:1px solid #dee2e6; }
.pos-cart-summary .summary-row { display:flex; justify-content:space-between; margin-bottom:4px; font-size:0.85rem; }
.pos-cart-summary .summary-total { font-size:1.15rem; font-weight:700; border-top:2px solid <?= $moduloColor ?>; padding-top:6px; margin-top:4px; }
.pos-cart-actions { padding:10px 15px; border-top:1px solid #dee2e6; }
.pos-cart-actions .btn { font-size:0.9rem; }
.pos-no-products { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#adb5bd; }
</style>

<div class="pos-container">
    <!-- ═══ Panel Productos ═══ -->
    <div class="pos-products">
        <div class="pos-search">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
                <input type="text" id="posBuscar" class="form-control" placeholder="Buscar producto por nombre, código o código de barras..." autofocus>
                <div class="input-group-append">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-barcode text-muted"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="pos-categories">
            <span class="cat-btn active" data-cat="all">Todos</span>
            <?php foreach ($categorias as $cat): ?>
            <span class="cat-btn" data-cat="<?= $cat['cat_categoria_id'] ?>">
                <?= htmlspecialchars($cat['cat_nombre']) ?>
            </span>
            <?php endforeach; ?>
        </div>

        <div class="pos-grid" id="posGrid">
            <?php if (empty($productos)): ?>
            <div class="pos-no-products">
                <i class="fas fa-box-open fa-3x mb-3"></i>
                <p>No hay productos disponibles</p>
            </div>
            <?php else: ?>
            <?php foreach ($productos as $p): ?>
            <div class="pos-product-card" data-id="<?= $p['pro_producto_id'] ?>"
                 data-nombre="<?= htmlspecialchars($p['pro_nombre']) ?>"
                 data-precio="<?= $p['pro_precio_venta'] ?>"
                 data-stock="<?= $p['stk_disponible'] ?? 0 ?>"
                 data-impuesto="<?= $p['imp_porcentaje'] ?? 0 ?>"
                 data-cat="<?= $p['pro_categoria_id'] ?? '' ?>"
                 data-codigo="<?= htmlspecialchars($p['pro_codigo'] ?? '') ?>"
                 data-barras="<?= htmlspecialchars($p['pro_codigo_barras'] ?? '') ?>"
                 onclick="agregarAlCarrito(this)">
                <?php if (!empty($p['pro_imagen_principal'])): ?>
                <img src="<?= htmlspecialchars($p['pro_imagen_principal']) ?>" alt="">
                <?php else: ?>
                <div style="width:60px;height:60px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;margin:0 auto 6px;">
                    <i class="fas fa-box text-muted"></i>
                </div>
                <?php endif; ?>
                <div class="name"><?= htmlspecialchars($p['pro_nombre']) ?></div>
                <div class="price">$<?= number_format($p['pro_precio_venta'], 2) ?></div>
                <div class="stock-info">Stock: <?= intval($p['stk_disponible'] ?? 0) ?></div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- ═══ Panel Carrito ═══ -->
    <div class="pos-cart">
        <div class="pos-cart-header">
            <div>
                <strong><i class="fas fa-shopping-cart mr-1"></i> Venta</strong>
                <span class="badge badge-light ml-1" id="cartCount">0</span>
            </div>
            <div>
                <select id="posCliente" class="form-control form-control-sm" style="width:180px;">
                    <option value="">Consumidor Final</option>
                    <?php foreach ($clientes as $cl): ?>
                    <option value="<?= $cl['cli_cliente_id'] ?>"><?= htmlspecialchars($cl['cli_nombres'] . ' ' . $cl['cli_apellidos']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="pos-cart-items" id="cartItems">
            <div class="text-center py-5 text-muted" id="cartEmpty">
                <i class="fas fa-shopping-basket fa-2x mb-2 opacity-50"></i>
                <p class="small">Agregue productos a la venta</p>
            </div>
        </div>

        <div class="pos-cart-summary">
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="cartSubtotal">$0.00</span>
            </div>
            <div class="summary-row">
                <span>IVA (<?= $ivaPct ?>%)</span>
                <span id="cartIva">$0.00</span>
            </div>
            <div class="summary-row">
                <span>Descuento</span>
                <span id="cartDescuento">-$0.00</span>
            </div>
            <div class="summary-row summary-total">
                <span>TOTAL</span>
                <span id="cartTotal" style="color:<?= $moduloColor ?>">$0.00</span>
            </div>
        </div>

        <div class="pos-cart-actions">
            <div class="row mb-2">
                <div class="col-6">
                    <button class="btn btn-outline-danger btn-block btn-sm" onclick="limpiarCarrito()">
                        <i class="fas fa-trash mr-1"></i> Limpiar
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn btn-outline-secondary btn-block btn-sm" onclick="aplicarDescuento()">
                        <i class="fas fa-percent mr-1"></i> Descuento
                    </button>
                </div>
            </div>
            <button class="btn btn-block btn-lg" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirPago()" id="btnCobrar" disabled>
                <i class="fas fa-credit-card mr-2"></i> COBRAR
            </button>
        </div>
    </div>
</div>

<!-- Modal de Pago -->
<div class="modal fade" id="modalPago" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                <h5 class="modal-title"><i class="fas fa-credit-card mr-2"></i>Procesar Pago</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-7">
                        <h5 class="mb-3">Forma de Pago</h5>
                        <div class="form-group">
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-secondary active" onclick="setFormaPago('EFECTIVO')">
                                    <input type="radio" name="forma_pago" value="EFECTIVO" checked>
                                    <i class="fas fa-money-bill-wave mr-1"></i> Efectivo
                                </label>
                                <label class="btn btn-outline-secondary" onclick="setFormaPago('TARJETA')">
                                    <input type="radio" name="forma_pago" value="TARJETA">
                                    <i class="fas fa-credit-card mr-1"></i> Tarjeta
                                </label>
                                <label class="btn btn-outline-secondary" onclick="setFormaPago('TRANSFERENCIA')">
                                    <input type="radio" name="forma_pago" value="TRANSFERENCIA">
                                    <i class="fas fa-exchange-alt mr-1"></i> Transfer.
                                </label>
                            </div>
                        </div>

                        <!-- Efectivo -->
                        <div id="pagoEfectivo">
                            <div class="form-group">
                                <label>Monto Recibido ($)</label>
                                <input type="number" step="0.01" min="0" id="montoRecibido" class="form-control form-control-lg text-center" oninput="calcularCambio()">
                            </div>
                            <div class="row mb-3">
                                <?php foreach ([5, 10, 20, 50, 100] as $r): ?>
                                <div class="col">
                                    <button type="button" class="btn btn-outline-secondary btn-block btn-sm" onclick="setMontoRapido(<?= $r ?>)">$<?= $r ?></button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="alert alert-info text-center" id="cambioInfo" style="display:none;">
                                <small>Cambio a devolver</small>
                                <div class="h3 mb-0" id="montoCambio">$0.00</div>
                            </div>
                        </div>

                        <!-- Tarjeta -->
                        <div id="pagoTarjeta" style="display:none;">
                            <div class="form-group">
                                <label>Referencia / Últimos 4 dígitos</label>
                                <input type="text" id="refTarjeta" class="form-control" maxlength="20" placeholder="Ej: 1234">
                            </div>
                        </div>

                        <!-- Transferencia -->
                        <div id="pagoTransferencia" style="display:none;">
                            <div class="form-group">
                                <label>Número de Comprobante</label>
                                <input type="text" id="refTransferencia" class="form-control" maxlength="50" placeholder="Ref. bancaria">
                            </div>
                        </div>

                        <!-- Cupón -->
                        <div class="form-group mt-3">
                            <label><i class="fas fa-ticket-alt mr-1"></i> Cupón de Descuento</label>
                            <div class="input-group">
                                <input type="text" id="inputCupon" class="form-control" placeholder="Código del cupón...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="validarCupon()">Aplicar</button>
                                </div>
                            </div>
                            <small class="text-muted" id="cuponMsg"></small>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-muted">Resumen de Venta</h6>
                                <div id="pagoResumenItems" class="small mb-3" style="max-height:150px;overflow-y:auto;"></div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span>Subtotal</span><span id="pagoSubtotal">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>IVA</span><span id="pagoIva">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Descuento</span><span id="pagoDesc" class="text-danger">-$0.00</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between h4">
                                    <strong>TOTAL</strong>
                                    <strong id="pagoTotal" style="color:<?= $moduloColor ?>">$0.00</strong>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="small">Tipo Documento</label>
                            <select id="tipoDoc" class="form-control form-control-sm">
                                <option value="NOTA_VENTA">Nota de Venta</option>
                                <option value="FACTURA">Factura</option>
                                <option value="TICKET">Ticket</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="small">Observaciones</label>
                            <textarea id="obsVenta" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-lg" style="background:<?= $moduloColor ?>;color:white;" onclick="procesarVenta()" id="btnProcesar">
                    <i class="fas fa-check-circle mr-1"></i> Confirmar Venta
                </button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
var carrito = [];
var descuentoGlobal = 0;
var formaPagoActual = 'EFECTIVO';
var cuponId = null;
var ivaPct = <?= $ivaPct ?>;
var urlProcesar = '<?= url('store', 'pos', 'procesarVenta') ?>';
var urlValidarCupon = '<?= url('store', 'descuento', 'validarCupon') ?>';
var urlTicket = '<?= url('store', 'pos', 'ticket') ?>';
var csrfToken = '<?= htmlspecialchars($csrf_token ?? '') ?>';

// ── Búsqueda ──
var timerBuscar;
document.getElementById('posBuscar').addEventListener('input', function() {
    clearTimeout(timerBuscar);
    var q = this.value.toLowerCase().trim();
    timerBuscar = setTimeout(function() { filtrarProductos(q); }, 200);
});

function filtrarProductos(q) {
    var cards = document.querySelectorAll('.pos-product-card');
    cards.forEach(function(c) {
        var nombre = (c.dataset.nombre || '').toLowerCase();
        var codigo = (c.dataset.codigo || '').toLowerCase();
        var barras = (c.dataset.barras || '').toLowerCase();
        var cat = c.dataset.cat;
        var catActiva = document.querySelector('.cat-btn.active');
        var catFiltro = catActiva ? catActiva.dataset.cat : 'all';
        var matchCat = catFiltro === 'all' || cat === catFiltro;
        var matchBuscar = !q || nombre.indexOf(q) !== -1 || codigo.indexOf(q) !== -1 || barras.indexOf(q) !== -1;
        c.style.display = (matchCat && matchBuscar) ? '' : 'none';
    });
}

// ── Categorías ──
document.querySelectorAll('.cat-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.cat-btn').forEach(function(b) { b.classList.remove('active'); });
        this.classList.add('active');
        filtrarProductos(document.getElementById('posBuscar').value.toLowerCase().trim());
    });
});

// ── Carrito ──
function agregarAlCarrito(el) {
    var id = el.dataset.id;
    var stock = parseInt(el.dataset.stock) || 0;
    var existing = carrito.find(function(i) { return i.id == id; });

    if (existing) {
        if (existing.qty >= stock) {
            Swal.fire('Sin Stock', 'No hay más unidades disponibles', 'warning');
            return;
        }
        existing.qty++;
    } else {
        if (stock <= 0) {
            Swal.fire('Agotado', 'Este producto no tiene stock', 'error');
            return;
        }
        carrito.push({
            id: id,
            nombre: el.dataset.nombre,
            precio: parseFloat(el.dataset.precio),
            impuesto: parseFloat(el.dataset.impuesto) || 0,
            qty: 1,
            stock: stock
        });
    }
    renderCarrito();
}

function renderCarrito() {
    var container = document.getElementById('cartItems');
    var empty = document.getElementById('cartEmpty');
    
    if (carrito.length === 0) {
        container.innerHTML = '';
        container.appendChild(empty);
        empty.style.display = '';
        document.getElementById('btnCobrar').disabled = true;
    } else {
        if (empty) empty.style.display = 'none';
        var html = '';
        carrito.forEach(function(item, idx) {
            var lineTotal = item.precio * item.qty;
            html += '<div class="cart-item">' +
                '<div class="item-info"><div class="item-name">' + item.nombre + '</div>' +
                '<div class="item-price">$' + item.precio.toFixed(2) + ' c/u</div></div>' +
                '<div class="item-qty">' +
                '<button type="button" onclick="cambiarCantidad(' + idx + ',-1)">-</button>' +
                '<input type="number" min="1" max="' + item.stock + '" value="' + item.qty + '" onchange="setCantidad(' + idx + ',this.value)" style="width:40px;text-align:center;">' +
                '<button type="button" onclick="cambiarCantidad(' + idx + ',1)">+</button>' +
                '</div>' +
                '<div class="item-total">$' + lineTotal.toFixed(2) + '</div>' +
                '<span class="item-remove" onclick="quitarItem(' + idx + ')"><i class="fas fa-times"></i></span>' +
                '</div>';
        });
        container.innerHTML = html;
        document.getElementById('btnCobrar').disabled = false;
    }
    calcularTotales();
}

function cambiarCantidad(idx, delta) {
    var item = carrito[idx];
    var newQty = item.qty + delta;
    if (newQty <= 0) { quitarItem(idx); return; }
    if (newQty > item.stock) { Swal.fire('Sin Stock', 'Stock máximo: ' + item.stock, 'warning'); return; }
    item.qty = newQty;
    renderCarrito();
}

function setCantidad(idx, val) {
    var q = parseInt(val) || 1;
    if (q > carrito[idx].stock) q = carrito[idx].stock;
    if (q < 1) q = 1;
    carrito[idx].qty = q;
    renderCarrito();
}

function quitarItem(idx) {
    carrito.splice(idx, 1);
    renderCarrito();
}

function limpiarCarrito() {
    if (carrito.length === 0) return;
    Swal.fire({
        title: '¿Limpiar carrito?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, limpiar'
    }).then(function(r) {
        if (r.isConfirmed) { carrito = []; descuentoGlobal = 0; cuponId = null; renderCarrito(); }
    });
}

function calcularTotales() {
    var subtotal = 0, iva = 0;
    carrito.forEach(function(item) {
        var lineSubtotal = item.precio * item.qty;
        subtotal += lineSubtotal;
        iva += lineSubtotal * (item.impuesto / 100);
    });
    var desc = descuentoGlobal;
    var total = subtotal + iva - desc;
    if (total < 0) total = 0;

    document.getElementById('cartSubtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('cartIva').textContent = '$' + iva.toFixed(2);
    document.getElementById('cartDescuento').textContent = '-$' + desc.toFixed(2);
    document.getElementById('cartTotal').textContent = '$' + total.toFixed(2);
    document.getElementById('cartCount').textContent = carrito.reduce(function(a, i) { return a + i.qty; }, 0);
}

function aplicarDescuento() {
    Swal.fire({
        title: 'Descuento',
        html: '<div class="form-group"><label>Tipo</label><select id="swalDescTipo" class="swal2-input"><option value="monto">Monto ($)</option><option value="porcentaje">Porcentaje (%)</option></select></div>' +
              '<div class="form-group"><label>Valor</label><input type="number" step="0.01" min="0" id="swalDescValor" class="swal2-input"></div>',
        showCancelButton: true,
        confirmButtonText: 'Aplicar',
        preConfirm: function() {
            var tipo = document.getElementById('swalDescTipo').value;
            var valor = parseFloat(document.getElementById('swalDescValor').value) || 0;
            if (valor <= 0) { Swal.showValidationMessage('Ingrese un valor válido'); return false; }
            return { tipo: tipo, valor: valor };
        }
    }).then(function(r) {
        if (r.isConfirmed) {
            var subtotal = carrito.reduce(function(a, i) { return a + i.precio * i.qty; }, 0);
            descuentoGlobal = r.value.tipo === 'porcentaje' ? subtotal * (r.value.valor / 100) : r.value.valor;
            calcularTotales();
        }
    });
}

// ── Pago ──
function setFormaPago(tipo) {
    formaPagoActual = tipo;
    document.getElementById('pagoEfectivo').style.display = tipo === 'EFECTIVO' ? '' : 'none';
    document.getElementById('pagoTarjeta').style.display = tipo === 'TARJETA' ? '' : 'none';
    document.getElementById('pagoTransferencia').style.display = tipo === 'TRANSFERENCIA' ? '' : 'none';
}

function abrirPago() {
    if (carrito.length === 0) return;
    // Llenar resumen
    var html = '';
    var subtotal = 0, iva = 0;
    carrito.forEach(function(i) {
        var lt = i.precio * i.qty;
        subtotal += lt;
        iva += lt * (i.impuesto / 100);
        html += '<div class="d-flex justify-content-between"><span>' + i.qty + 'x ' + i.nombre + '</span><span>$' + lt.toFixed(2) + '</span></div>';
    });
    document.getElementById('pagoResumenItems').innerHTML = html;
    document.getElementById('pagoSubtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('pagoIva').textContent = '$' + iva.toFixed(2);
    document.getElementById('pagoDesc').textContent = '-$' + descuentoGlobal.toFixed(2);
    var total = subtotal + iva - descuentoGlobal;
    document.getElementById('pagoTotal').textContent = '$' + total.toFixed(2);
    document.getElementById('montoRecibido').value = '';
    document.getElementById('cambioInfo').style.display = 'none';
    $('#modalPago').modal('show');
}

function calcularCambio() {
    var total = parseFloat(document.getElementById('pagoTotal').textContent.replace('$', '')) || 0;
    var recibido = parseFloat(document.getElementById('montoRecibido').value) || 0;
    var cambio = recibido - total;
    var info = document.getElementById('cambioInfo');
    if (recibido > 0) {
        info.style.display = '';
        document.getElementById('montoCambio').textContent = '$' + cambio.toFixed(2);
        info.className = 'alert text-center ' + (cambio >= 0 ? 'alert-success' : 'alert-danger');
    } else {
        info.style.display = 'none';
    }
}

function setMontoRapido(val) {
    document.getElementById('montoRecibido').value = val;
    calcularCambio();
}

function validarCupon() {
    var codigo = document.getElementById('inputCupon').value.trim();
    if (!codigo) return;
    var subtotal = carrito.reduce(function(a, i) { return a + i.precio * i.qty; }, 0);

    fetch(urlValidarCupon, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'csrf_token=' + encodeURIComponent(csrfToken) + '&codigo=' + encodeURIComponent(codigo) + '&subtotal=' + subtotal
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var msg = document.getElementById('cuponMsg');
        if (data.success) {
            descuentoGlobal = parseFloat(data.descuento_calculado) || 0;
            cuponId = data.descuento_id || null;
            msg.textContent = '✓ Cupón aplicado: -$' + descuentoGlobal.toFixed(2);
            msg.className = 'text-success small';
            calcularTotales();
            // Actualizar modal
            document.getElementById('pagoDesc').textContent = '-$' + descuentoGlobal.toFixed(2);
            var total = carrito.reduce(function(a, i) { return a + i.precio * i.qty * (1 + i.impuesto / 100); }, 0) - descuentoGlobal;
            document.getElementById('pagoTotal').textContent = '$' + total.toFixed(2);
        } else {
            msg.textContent = '✗ ' + (data.message || 'Cupón inválido');
            msg.className = 'text-danger small';
        }
    })
    .catch(function() {
        document.getElementById('cuponMsg').textContent = 'Error al validar cupón';
        document.getElementById('cuponMsg').className = 'text-danger small';
    });
}

function procesarVenta() {
    if (carrito.length === 0) return;

    var total = parseFloat(document.getElementById('pagoTotal').textContent.replace('$', '')) || 0;

    if (formaPagoActual === 'EFECTIVO') {
        var recibido = parseFloat(document.getElementById('montoRecibido').value) || 0;
        if (recibido < total) {
            Swal.fire('Monto insuficiente', 'El monto recibido debe ser igual o mayor al total', 'warning');
            return;
        }
    }

    document.getElementById('btnProcesar').disabled = true;
    document.getElementById('btnProcesar').innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...';

    var items = carrito.map(function(i) {
        return { producto_id: i.id, cantidad: i.qty, precio_unitario: i.precio, impuesto_pct: i.impuesto };
    });

    var pagos = [{
        forma_pago: formaPagoActual,
        monto: total,
        referencia: formaPagoActual === 'TARJETA' ? document.getElementById('refTarjeta').value :
                    (formaPagoActual === 'TRANSFERENCIA' ? document.getElementById('refTransferencia').value : '')
    }];

    var body = new FormData();
    body.append('csrf_token', csrfToken);
    body.append('items', JSON.stringify(items));
    body.append('pagos', JSON.stringify(pagos));
    body.append('cliente_id', document.getElementById('posCliente').value);
    body.append('descuento_id', cuponId || '');
    body.append('descuento_monto', descuentoGlobal);
    body.append('tipo_documento', document.getElementById('tipoDoc').value);
    body.append('observaciones', document.getElementById('obsVenta').value);

    fetch(urlProcesar, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            $('#modalPago').modal('hide');
            Swal.fire({
                icon: 'success',
                title: '¡Venta Exitosa!',
                html: '<strong>' + (data.numero || '') + '</strong><br>Total: $' + total.toFixed(2),
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '<?= $moduloColor ?>'
            }).then(function() {
                carrito = [];
                descuentoGlobal = 0;
                cuponId = null;
                renderCarrito();
                // Actualizar stock visual
                if (data.venta_id) {
                    items.forEach(function(it) {
                        var card = document.querySelector('.pos-product-card[data-id="' + it.producto_id + '"]');
                        if (card) {
                            var newStock = parseInt(card.dataset.stock) - it.cantidad;
                            card.dataset.stock = newStock;
                            card.querySelector('.stock-info').textContent = 'Stock: ' + newStock;
                        }
                    });
                }
            });
        } else {
            Swal.fire('Error', data.message || 'No se pudo procesar la venta', 'error');
        }
    })
    .catch(function(e) {
        Swal.fire('Error', 'Error de conexión', 'error');
    })
    .finally(function() {
        document.getElementById('btnProcesar').disabled = false;
        document.getElementById('btnProcesar').innerHTML = '<i class="fas fa-check-circle mr-1"></i> Confirmar Venta';
    });
}

// Init
renderCarrito();
</script>
<?php $scripts = ob_get_clean(); ?>
