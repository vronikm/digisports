<?php
/**
 * DigiSports Store - Crear/Editar Producto
 */
$modo        = $modo ?? 'crear';
$producto    = $producto ?? [];
$categorias  = $categorias ?? [];
$marcas      = $marcas ?? [];
$impuestos   = $impuestos ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
$esEditar    = $modo === 'editar';
$titulo      = $esEditar ? 'Editar Producto' : 'Nuevo Producto';
$urlAccion   = $esEditar ? url('store', 'producto', 'editar') : url('store', 'producto', 'crear');
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-<?= $esEditar ? 'edit' : 'plus' ?> mr-2" style="color:<?= $moduloColor ?>"></i><?= $titulo ?></h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
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
        <form method="POST" action="<?= $urlAccion ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <?php if ($esEditar && !empty($producto['pro_producto_id'])): ?>
            <input type="hidden" name="pro_producto_id" value="<?= $producto['pro_producto_id'] ?>">
            <?php endif; ?>

            <div class="row">
                <!-- Información General -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Información General</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Nombre del Producto <span class="text-danger">*</span></label>
                                        <input type="text" name="nombre" class="form-control" required maxlength="200" value="<?= htmlspecialchars($producto['pro_nombre'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Código</label>
                                        <input type="text" name="codigo" class="form-control" maxlength="50" value="<?= htmlspecialchars($producto['pro_codigo'] ?? '') ?>" placeholder="Auto-generado">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3" maxlength="2000"><?= htmlspecialchars($producto['pro_descripcion'] ?? '') ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Categoría <span class="text-danger">*</span></label>
                                        <select name="categoria_id" class="form-control" required>
                                            <option value="">Seleccionar...</option>
                                            <?php foreach ($categorias as $c): ?>
                                            <option value="<?= $c['cat_categoria_id'] ?>" <?= ($producto['pro_categoria_id'] ?? '') == $c['cat_categoria_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['cat_nombre']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Marca</label>
                                        <select name="marca_id" class="form-control">
                                            <option value="">Sin marca</option>
                                            <?php foreach ($marcas as $m): ?>
                                            <option value="<?= $m['mar_marca_id'] ?>" <?= ($producto['pro_marca_id'] ?? '') == $m['mar_marca_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($m['mar_nombre']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Impuesto</label>
                                        <select name="impuesto_id" class="form-control">
                                            <option value="">Sin impuesto</option>
                                            <?php foreach ($impuestos as $imp): ?>
                                            <option value="<?= $imp['imp_impuesto_id'] ?>" <?= ($producto['pro_impuesto_id'] ?? '') == $imp['imp_impuesto_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($imp['imp_nombre']) ?> (<?= $imp['imp_porcentaje'] ?>%)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>SKU</label>
                                        <input type="text" name="sku" class="form-control" maxlength="50" value="<?= htmlspecialchars($producto['pro_sku'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Código de Barras</label>
                                        <input type="text" name="codigo_barras" class="form-control" maxlength="50" value="<?= htmlspecialchars($producto['pro_codigo_barras'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Unidad de Medida</label>
                                        <select name="unidad_medida" class="form-control">
                                            <?php
                                            $unidades = ['UNIDAD','PAR','JUEGO','CAJA','DOCENA','KG','METRO'];
                                            foreach ($unidades as $u):
                                            ?>
                                            <option value="<?= $u ?>" <?= ($producto['pro_unidad_medida'] ?? 'UNIDAD') === $u ? 'selected' : '' ?>><?= $u ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Variantes -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-layer-group mr-2"></i>Variantes</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="agregarVariante()">
                                    <i class="fas fa-plus mr-1"></i> Agregar
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0" id="tablaVariantes">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Talla</th>
                                        <th>Color</th>
                                        <th>Precio Adicional</th>
                                        <th>Stock</th>
                                        <th>SKU</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="variantesBody">
                                    <?php if (!empty($producto['variantes'])): 
                                        foreach ($producto['variantes'] as $vi => $var): ?>
                                    <tr>
                                        <td><input type="text" name="variantes[<?= $vi ?>][talla]" class="form-control form-control-sm" value="<?= htmlspecialchars($var['var_talla'] ?? '') ?>"></td>
                                        <td><input type="text" name="variantes[<?= $vi ?>][color]" class="form-control form-control-sm" value="<?= htmlspecialchars($var['var_color'] ?? '') ?>"></td>
                                        <td><input type="number" step="0.01" name="variantes[<?= $vi ?>][precio_adicional]" class="form-control form-control-sm" value="<?= $var['var_precio_adicional'] ?? 0 ?>"></td>
                                        <td><input type="number" name="variantes[<?= $vi ?>][stock]" class="form-control form-control-sm" value="<?= $var['var_stock'] ?? 0 ?>"></td>
                                        <td><input type="text" name="variantes[<?= $vi ?>][sku]" class="form-control form-control-sm" value="<?= htmlspecialchars($var['var_sku'] ?? '') ?>"></td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Panel Lateral -->
                <div class="col-lg-4">
                    <!-- Precios -->
                    <div class="card shadow-sm">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-dollar-sign mr-2"></i>Precios</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Precio de Compra <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                    <input type="number" step="0.01" min="0" name="precio_compra" class="form-control" required value="<?= $producto['pro_precio_compra'] ?? '0.00' ?>" id="precioCompra">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Precio de Venta <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                    <input type="number" step="0.01" min="0" name="precio_venta" class="form-control" required value="<?= $producto['pro_precio_venta'] ?? '0.00' ?>" id="precioVenta">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Precio Mayoreo</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                    <input type="number" step="0.01" min="0" name="precio_mayoreo" class="form-control" value="<?= $producto['pro_precio_mayoreo'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="alert alert-light py-1 px-2 small" id="margenInfo">
                                Margen: <strong id="margenPct">0%</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Inicial (solo crear) -->
                    <?php if (!$esEditar): ?>
                    <div class="card shadow-sm">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-boxes mr-2"></i>Stock Inicial</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Cantidad Inicial</label>
                                <input type="number" min="0" name="stock_inicial" class="form-control" value="0">
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Mínimo</label>
                                        <input type="number" min="0" name="stock_minimo" class="form-control" value="5">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Máximo</label>
                                        <input type="number" min="0" name="stock_maximo" class="form-control" value="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Estado y Opciones -->
                    <div class="card shadow-sm">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-cog mr-2"></i>Opciones</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Estado</label>
                                <select name="estado" class="form-control">
                                    <option value="ACTIVO" <?= ($producto['pro_estado'] ?? 'ACTIVO') === 'ACTIVO' ? 'selected' : '' ?>>Activo</option>
                                    <option value="INACTIVO" <?= ($producto['pro_estado'] ?? '') === 'INACTIVO' ? 'selected' : '' ?>>Inactivo</option>
                                    <option value="DESCONTINUADO" <?= ($producto['pro_estado'] ?? '') === 'DESCONTINUADO' ? 'selected' : '' ?>>Descontinuado</option>
                                </select>
                            </div>
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="visiblePos" name="visible_pos" value="1" <?= ($producto['pro_visible_pos'] ?? 1) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="visiblePos">Visible en POS</label>
                            </div>
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="destacado" name="destacado" value="1" <?= ($producto['pro_destacado'] ?? 0) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="destacado">Producto Destacado</label>
                            </div>
                            <div class="form-group mt-3">
                                <label>Imagen Principal (URL)</label>
                                <input type="url" name="imagen_principal" class="form-control" value="<?= htmlspecialchars($producto['pro_imagen_principal'] ?? '') ?>" placeholder="https://...">
                            </div>
                        </div>
                    </div>

                    <!-- Botón Guardar -->
                    <button type="submit" class="btn btn-block" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-save mr-1"></i> <?= $esEditar ? 'Actualizar Producto' : 'Crear Producto' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<?php ob_start(); ?>
<script>
var varianteIndex = <?= !empty($producto['variantes']) ? count($producto['variantes']) : 0 ?>;

function agregarVariante() {
    var row = '<tr>' +
        '<td><input type="text" name="variantes[' + varianteIndex + '][talla]" class="form-control form-control-sm" placeholder="S, M, L..."></td>' +
        '<td><input type="text" name="variantes[' + varianteIndex + '][color]" class="form-control form-control-sm" placeholder="Rojo, Azul..."></td>' +
        '<td><input type="number" step="0.01" name="variantes[' + varianteIndex + '][precio_adicional]" class="form-control form-control-sm" value="0"></td>' +
        '<td><input type="number" name="variantes[' + varianteIndex + '][stock]" class="form-control form-control-sm" value="0"></td>' +
        '<td><input type="text" name="variantes[' + varianteIndex + '][sku]" class="form-control form-control-sm"></td>' +
        '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove()"><i class="fas fa-times"></i></button></td>' +
        '</tr>';
    document.getElementById('variantesBody').insertAdjacentHTML('beforeend', row);
    varianteIndex++;
}

// Calcular margen
function calcularMargen() {
    var compra = parseFloat(document.getElementById('precioCompra').value) || 0;
    var venta = parseFloat(document.getElementById('precioVenta').value) || 0;
    var margen = compra > 0 ? ((venta - compra) / compra * 100).toFixed(1) : 0;
    var el = document.getElementById('margenPct');
    el.textContent = margen + '%';
    el.className = margen >= 20 ? 'text-success' : (margen >= 0 ? 'text-warning' : 'text-danger');
}
document.getElementById('precioCompra').addEventListener('input', calcularMargen);
document.getElementById('precioVenta').addEventListener('input', calcularMargen);
calcularMargen();
</script>
<?php $scripts = ob_get_clean(); ?>
