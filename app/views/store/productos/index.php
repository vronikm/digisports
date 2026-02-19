<?php
/**
 * DigiSports Store - Listado de Productos
 * Con filtros, paginación y acciones
 */
$productos    = $productos ?? [];
$categorias   = $categorias ?? [];
$marcas       = $marcas ?? [];
$buscar       = $buscar ?? '';
$filtro_cat   = $filtro_categoria ?? '';
$filtro_marca = $filtro_marca ?? '';
$filtro_estado = $filtro_estado ?? '';
$pagina       = $pagina ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$total        = $total ?? 0;
$moduloColor  = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-box mr-2" style="color:<?= $moduloColor ?>"></i>Productos <small class="text-muted">(<?= $total ?>)</small></h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('store', 'producto', 'crear') ?>" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-plus mr-1"></i> Nuevo Producto
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Filtros -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <form method="POST" action="<?= url('store', 'producto', 'index') ?>" class="row align-items-end">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <div class="col-md-3">
                        <label class="small mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Nombre, código, SKU..." value="<?= htmlspecialchars($buscar) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-1">Categoría</label>
                        <select name="categoria" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['cat_categoria_id'] ?>" <?= $filtro_cat == $c['cat_categoria_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['cat_nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-1">Marca</label>
                        <select name="marca" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            <?php foreach ($marcas as $m): ?>
                            <option value="<?= $m['mar_marca_id'] ?>" <?= $filtro_marca == $m['mar_marca_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['mar_nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="ACTIVO" <?= $filtro_estado === 'ACTIVO' ? 'selected' : '' ?>>Activo</option>
                            <option value="INACTIVO" <?= $filtro_estado === 'INACTIVO' ? 'selected' : '' ?>>Inactivo</option>
                            <option value="AGOTADO" <?= $filtro_estado === 'AGOTADO' ? 'selected' : '' ?>>Agotado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                        <a href="<?= url('store', 'producto', 'index') ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times mr-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($productos)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box-open fa-3x mb-3 opacity-50"></i>
                    <p>No se encontraron productos</p>
                    <a href="<?= url('store', 'producto', 'crear') ?>" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-plus mr-1"></i> Crear primer producto
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="60">Img</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Marca</th>
                                <th class="text-right">P. Compra</th>
                                <th class="text-right">P. Venta</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Estado</th>
                                <th width="150" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $p): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($p['pro_imagen_principal'])): ?>
                                    <img src="<?= htmlspecialchars($p['pro_imagen_principal']) ?>" alt="" class="img-fluid rounded" style="width:40px;height:40px;object-fit:cover;">
                                    <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                        <i class="fas fa-box text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($p['pro_nombre']) ?></strong>
                                    <?php if (!empty($p['pro_codigo'])): ?>
                                    <br><small class="text-muted"><code><?= htmlspecialchars($p['pro_codigo']) ?></code></small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars($p['cat_nombre'] ?? '—') ?></small></td>
                                <td><small><?= htmlspecialchars($p['mar_nombre'] ?? '—') ?></small></td>
                                <td class="text-right"><small>$<?= number_format($p['pro_precio_compra'] ?? 0, 2) ?></small></td>
                                <td class="text-right"><strong class="text-success">$<?= number_format($p['pro_precio_venta'] ?? 0, 2) ?></strong></td>
                                <td class="text-center">
                                    <?php
                                    $stock = intval($p['stk_disponible'] ?? 0);
                                    $minimo = intval($p['stk_minimo'] ?? 0);
                                    if ($stock <= 0): ?>
                                    <span class="badge badge-danger"><?= $stock ?></span>
                                    <?php elseif ($stock <= $minimo): ?>
                                    <span class="badge badge-warning"><?= $stock ?></span>
                                    <?php else: ?>
                                    <span class="badge badge-success"><?= $stock ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $estCol = ['ACTIVO'=>'success','INACTIVO'=>'secondary','AGOTADO'=>'danger','DESCONTINUADO'=>'dark'];
                                    $ec = $estCol[$p['pro_estado']] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?= $ec ?>"><?= $p['pro_estado'] ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('store', 'producto', 'ver', ['id' => $p['pro_producto_id']]) ?>" class="btn btn-outline-info" title="Ver"><i class="fas fa-eye"></i></a>
                                        <a href="<?= url('store', 'producto', 'editar', ['id' => $p['pro_producto_id']]) ?>" class="btn btn-outline-primary" title="Editar"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-outline-danger btn-eliminar" data-id="<?= $p['pro_producto_id'] ?>" data-nombre="<?= htmlspecialchars($p['pro_nombre']) ?>" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Paginación -->
            <?php if ($totalPaginas > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php for ($pg = 1; $pg <= $totalPaginas; $pg++): ?>
                        <li class="page-item <?= $pg == $pagina ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('store', 'producto', 'index', ['pagina' => $pg]) ?>"><?= $pg ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<script>
document.querySelectorAll('.btn-eliminar').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var nombre = this.dataset.nombre;
        Swal.fire({
            title: '¿Eliminar producto?',
            html: 'Se eliminará <strong>' + nombre + '</strong>. Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (r.isConfirmed) window.location.href = '<?= url('store', 'producto', 'eliminar') ?>&id=' + id;
        });
    });
});
</script>
<?php $scripts = ob_get_clean(); ?>
