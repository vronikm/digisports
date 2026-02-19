<?php
/**
 * DigiSports Store - Detalle de Producto
 */
$producto    = $producto ?? [];
$variantes   = $variantes ?? [];
$imagenes    = $imagenes ?? [];
$movimientos = $movimientos ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-box mr-2" style="color:<?= $moduloColor ?>"></i><?= htmlspecialchars($producto['pro_nombre'] ?? 'Producto') ?></h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('store', 'producto', 'editar', ['id' => $producto['pro_producto_id'] ?? 0]) ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </a>
                    <a href="<?= url('store', 'producto', 'index') ?>" class="btn btn-sm btn-outline-secondary">
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
            <!-- Info Principal -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <?php if (!empty($producto['pro_imagen_principal'])): ?>
                                <img src="<?= htmlspecialchars($producto['pro_imagen_principal']) ?>" alt="" class="img-fluid rounded mb-3" style="max-height:200px;">
                                <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height:200px;">
                                    <i class="fas fa-box fa-4x text-muted"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <h4><?= htmlspecialchars($producto['pro_nombre'] ?? '') ?></h4>
                                <p class="text-muted"><?= htmlspecialchars($producto['pro_descripcion'] ?? 'Sin descripción') ?></p>
                                <div class="row">
                                    <div class="col-6 col-md-4 mb-3">
                                        <small class="text-muted d-block">Código</small>
                                        <code><?= htmlspecialchars($producto['pro_codigo'] ?? '—') ?></code>
                                    </div>
                                    <div class="col-6 col-md-4 mb-3">
                                        <small class="text-muted d-block">SKU</small>
                                        <strong><?= htmlspecialchars($producto['pro_sku'] ?? '—') ?></strong>
                                    </div>
                                    <div class="col-6 col-md-4 mb-3">
                                        <small class="text-muted d-block">Código Barras</small>
                                        <strong><?= htmlspecialchars($producto['pro_codigo_barras'] ?? '—') ?></strong>
                                    </div>
                                    <div class="col-6 col-md-4 mb-3">
                                        <small class="text-muted d-block">Categoría</small>
                                        <strong><?= htmlspecialchars($producto['cat_nombre'] ?? '—') ?></strong>
                                    </div>
                                    <div class="col-6 col-md-4 mb-3">
                                        <small class="text-muted d-block">Marca</small>
                                        <strong><?= htmlspecialchars($producto['mar_nombre'] ?? '—') ?></strong>
                                    </div>
                                    <div class="col-6 col-md-4 mb-3">
                                        <small class="text-muted d-block">Estado</small>
                                        <?php
                                        $ec = ['ACTIVO'=>'success','INACTIVO'=>'secondary','AGOTADO'=>'danger','DESCONTINUADO'=>'dark'];
                                        ?>
                                        <span class="badge badge-<?= $ec[$producto['pro_estado']] ?? 'secondary' ?>"><?= $producto['pro_estado'] ?? '—' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variantes -->
                <?php if (!empty($variantes)): ?>
                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-layer-group mr-2"></i>Variantes (<?= count($variantes) ?>)</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr><th>Talla</th><th>Color</th><th>SKU</th><th class="text-right">+ Precio</th><th class="text-center">Stock</th><th>Estado</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($variantes as $var): ?>
                                <tr>
                                    <td><?= htmlspecialchars($var['var_talla'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($var['var_color'] ?? '—') ?></td>
                                    <td><code><?= htmlspecialchars($var['var_sku'] ?? '—') ?></code></td>
                                    <td class="text-right">$<?= number_format($var['var_precio_adicional'] ?? 0, 2) ?></td>
                                    <td class="text-center"><span class="badge badge-light"><?= intval($var['var_stock'] ?? 0) ?></span></td>
                                    <td><span class="badge badge-<?= ($var['var_estado'] ?? 'ACTIVO') === 'ACTIVO' ? 'success' : 'secondary' ?>"><?= $var['var_estado'] ?? 'ACTIVO' ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Últimos Movimientos de Stock -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-exchange-alt mr-2"></i>Movimientos de Stock</h3>
                        <div class="card-tools">
                            <button class="btn btn-sm btn-outline-success" onclick="abrirAjusteStock()" title="Ajustar stock">
                                <i class="fas fa-plus-minus mr-1"></i> Ajustar Stock
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($movimientos)): ?>
                        <div class="text-center py-4 text-muted"><p class="small">Sin movimientos registrados</p></div>
                        <?php else: ?>
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr><th>Fecha</th><th>Tipo</th><th class="text-center">Cantidad</th><th>Motivo</th><th>Usuario</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($movimientos, 0, 10) as $mv): ?>
                                <tr>
                                    <td><small><?= date('d/m/Y H:i', strtotime($mv['mov_fecha'])) ?></small></td>
                                    <td>
                                        <?php
                                        $tc = ['ENTRADA'=>'success','SALIDA'=>'danger','AJUSTE'=>'warning','VENTA'=>'info','DEVOLUCION'=>'primary'];
                                        ?>
                                        <span class="badge badge-<?= $tc[$mv['mov_tipo']] ?? 'secondary' ?>"><?= $mv['mov_tipo'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?= $mv['mov_tipo'] === 'ENTRADA' || $mv['mov_tipo'] === 'DEVOLUCION' ? '+' : '-' ?><?= intval($mv['mov_cantidad']) ?>
                                    </td>
                                    <td><small class="text-muted"><?= htmlspecialchars($mv['mov_motivo'] ?? '—') ?></small></td>
                                    <td><small><?= htmlspecialchars($mv['usuario_nombre'] ?? '—') ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="col-lg-4">
                <!-- Precios -->
                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-dollar-sign mr-2"></i>Precios</h3></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Precio Compra</span>
                            <strong>$<?= number_format($producto['pro_precio_compra'] ?? 0, 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Precio Venta</span>
                            <strong class="text-success h5 mb-0">$<?= number_format($producto['pro_precio_venta'] ?? 0, 2) ?></strong>
                        </div>
                        <?php if (!empty($producto['pro_precio_mayoreo'])): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Precio Mayoreo</span>
                            <strong>$<?= number_format($producto['pro_precio_mayoreo'], 2) ?></strong>
                        </div>
                        <?php endif; ?>
                        <?php
                        $compra = floatval($producto['pro_precio_compra'] ?? 0);
                        $venta = floatval($producto['pro_precio_venta'] ?? 0);
                        $margen = $compra > 0 ? round(($venta - $compra) / $compra * 100, 1) : 0;
                        ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Margen</span>
                            <strong class="<?= $margen >= 20 ? 'text-success' : ($margen >= 0 ? 'text-warning' : 'text-danger') ?>"><?= $margen ?>%</strong>
                        </div>
                    </div>
                </div>

                <!-- Stock -->
                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-boxes mr-2"></i>Stock</h3></div>
                    <div class="card-body">
                        <?php
                        $stock = intval($producto['stk_disponible'] ?? 0);
                        $min = intval($producto['stk_minimo'] ?? 0);
                        $max = intval($producto['stk_maximo'] ?? 100);
                        $pctStock = $max > 0 ? min(100, round($stock / $max * 100)) : 0;
                        $stockColor = $stock <= 0 ? 'danger' : ($stock <= $min ? 'warning' : 'success');
                        ?>
                        <div class="text-center mb-3">
                            <div class="h2 mb-0 text-<?= $stockColor ?>"><?= $stock ?></div>
                            <small class="text-muted">unidades disponibles</small>
                        </div>
                        <div class="progress mb-2" style="height:8px;">
                            <div class="progress-bar bg-<?= $stockColor ?>" style="width:<?= $pctStock ?>%;"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Mín: <?= $min ?></span>
                            <span>Máx: <?= $max ?></span>
                        </div>
                    </div>
                </div>

                <!-- Info Adicional -->
                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-info mr-2"></i>Información</h3></div>
                    <div class="card-body small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Visible POS</span>
                            <span><?= ($producto['pro_visible_pos'] ?? 0) ? '<i class="fas fa-check text-success"></i> Sí' : '<i class="fas fa-times text-danger"></i> No' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Destacado</span>
                            <span><?= ($producto['pro_destacado'] ?? 0) ? '<i class="fas fa-star text-warning"></i> Sí' : 'No' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Unidad</span>
                            <span><?= $producto['pro_unidad_medida'] ?? 'UNIDAD' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Creado</span>
                            <span><?= !empty($producto['pro_fecha_creacion']) ? date('d/m/Y', strtotime($producto['pro_fecha_creacion'])) : '—' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Ajuste Stock -->
<div class="modal fade" id="modalAjusteStock" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('store', 'producto', 'ajustarStock') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="producto_id" value="<?= $producto['pro_producto_id'] ?? 0 ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-boxes mr-2"></i>Ajustar Stock</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tipo de Movimiento</label>
                        <select name="tipo" class="form-control" required>
                            <option value="ENTRADA">Entrada (aumentar)</option>
                            <option value="SALIDA">Salida (disminuir)</option>
                            <option value="AJUSTE">Ajuste (establecer cantidad exacta)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cantidad</label>
                        <input type="number" name="cantidad" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Motivo</label>
                        <textarea name="motivo" class="form-control" rows="2" required placeholder="Razón del ajuste..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
function abrirAjusteStock() {
    $('#modalAjusteStock').modal('show');
}
</script>
<?php $scripts = ob_get_clean(); ?>
