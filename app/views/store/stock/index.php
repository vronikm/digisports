<?php
/**
 * DigiSports Store - Inventario
 */
$inventario  = $inventario ?? [];
$resumen     = $resumen ?? [];
$buscar      = $buscar ?? '';
$filtro      = $filtro ?? '';
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-boxes mr-2" style="color:<?= $moduloColor ?>"></i>Inventario</h1></div>
            <div class="col-sm-6"><div class="float-sm-right">
                <a href="<?= url('store', 'stock', 'movimientos') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-exchange-alt mr-1"></i> Movimientos</a>
                <a href="<?= url('store', 'stock', 'alertas') ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-bell mr-1"></i> Alertas</a>
            </div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- KPIs -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="small-box bg-info"><div class="inner"><h3><?= intval($resumen['total_productos'] ?? 0) ?></h3><p>Productos</p></div><div class="icon"><i class="fas fa-box"></i></div></div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-danger"><div class="inner"><h3><?= intval($resumen['agotados'] ?? 0) ?></h3><p>Agotados</p></div><div class="icon"><i class="fas fa-times-circle"></i></div></div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning"><div class="inner"><h3><?= intval($resumen['stock_bajo'] ?? 0) ?></h3><p>Stock Bajo</p></div><div class="icon"><i class="fas fa-exclamation-triangle"></i></div></div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success"><div class="inner"><h3>$<?= number_format($resumen['valor_inventario'] ?? 0, 0) ?></h3><p>Valor Inventario</p></div><div class="icon"><i class="fas fa-dollar-sign"></i></div></div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('store', 'stock', 'index') ?>" class="row align-items-end">
                    <div class="col-md-4">
                        <label class="small mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Nombre, código, SKU..." value="<?= htmlspecialchars($buscar) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="small mb-1">Estado stock</label>
                        <select name="filtro" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="bajo" <?= $filtro === 'bajo' ? 'selected' : '' ?>>Stock bajo</option>
                            <option value="agotado" <?= $filtro === 'agotado' ? 'selected' : '' ?>>Agotados</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-search mr-1"></i> Filtrar</button>
                        <a href="<?= url('store', 'stock', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times mr-1"></i> Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($inventario)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-boxes fa-3x mb-3 opacity-50"></i><p>No se encontraron productos</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-light">
                            <tr><th>Producto</th><th>Código</th><th>Categoría</th><th>Marca</th>
                                <th class="text-center">Stock</th><th class="text-center">Reservado</th>
                                <th class="text-center">Disponible</th><th class="text-center">Mínimo</th>
                                <th class="text-right">P. Compra</th><th class="text-right">P. Venta</th><th class="text-right">Valor</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventario as $inv):
                                $stockDisp = intval($inv['stock_disponible'] ?? 0);
                                $stockMin = intval($inv['pro_stock_minimo'] ?? 0);
                                $stockClass = 'text-success';
                                if ($stockDisp <= 0) $stockClass = 'text-danger font-weight-bold';
                                elseif ($stockDisp <= $stockMin) $stockClass = 'text-warning font-weight-bold';
                                $valorLinea = ($inv['stock_total'] ?? 0) * ($inv['pro_precio_compra'] ?? 0);
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= url('store', 'producto', 'ver', ['id' => $inv['pro_producto_id']]) ?>"><?= htmlspecialchars($inv['pro_nombre']) ?></a>
                                </td>
                                <td><code><?= htmlspecialchars($inv['pro_codigo'] ?? $inv['pro_sku'] ?? '') ?></code></td>
                                <td><small><?= htmlspecialchars($inv['cat_nombre'] ?? '—') ?></small></td>
                                <td><small><?= htmlspecialchars($inv['mar_nombre'] ?? '—') ?></small></td>
                                <td class="text-center"><?= intval($inv['stock_total'] ?? 0) ?></td>
                                <td class="text-center"><small class="text-muted"><?= intval($inv['stock_reservado'] ?? 0) ?></small></td>
                                <td class="text-center <?= $stockClass ?>"><?= $stockDisp ?></td>
                                <td class="text-center"><small class="text-muted"><?= $stockMin ?></small></td>
                                <td class="text-right">$<?= number_format($inv['pro_precio_compra'] ?? 0, 2) ?></td>
                                <td class="text-right">$<?= number_format($inv['pro_precio_venta'] ?? 0, 2) ?></td>
                                <td class="text-right"><strong>$<?= number_format($valorLinea, 2) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
