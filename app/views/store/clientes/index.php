<?php
/**
 * DigiSports Store - Directorio de Clientes
 */
$clientes     = $clientes ?? [];
$resumen      = $resumenCat ?? [];
$buscar       = $buscar ?? '';
$filtro_cat   = $categoriaFiltro ?? '';
$pagina       = $pagina ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$total        = $total ?? 0;
$moduloColor  = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-users mr-2" style="color:<?= $moduloColor ?>"></i>Clientes <small class="text-muted">(<?= $total ?>)</small></h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('store', 'cliente', 'crear') ?>" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-plus mr-1"></i> Nuevo Cliente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Resumen -->
        <?php if (!empty($resumen)): ?>
        <div class="row mb-3">
            <?php
            $cats = ['VIP' => ['warning', 'fa-crown'], 'FRECUENTE' => ['info', 'fa-star'], 'REGULAR' => ['success', 'fa-user'], 'NUEVO' => ['secondary', 'fa-user-plus']];
            foreach ($cats as $catName => $catInfo):
            ?>
            <div class="col-md-3">
                <div class="info-box bg-<?= $catInfo[0] ?>">
                    <span class="info-box-icon"><i class="fas <?= $catInfo[1] ?>"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?= $catName ?></span>
                        <span class="info-box-number"><?= intval($resumen[$catName] ?? 0) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <form method="POST" action="<?= url('store', 'cliente', 'index') ?>" class="row align-items-end">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <div class="col-md-4">
                        <label class="small mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Nombre, CI/RUC, email, teléfono..." value="<?= htmlspecialchars($buscar) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="small mb-1">Categoría</label>
                        <select name="categoria" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            <option value="VIP" <?= $filtro_cat === 'VIP' ? 'selected' : '' ?>>VIP</option>
                            <option value="FRECUENTE" <?= $filtro_cat === 'FRECUENTE' ? 'selected' : '' ?>>Frecuente</option>
                            <option value="REGULAR" <?= $filtro_cat === 'REGULAR' ? 'selected' : '' ?>>Regular</option>
                            <option value="NUEVO" <?= $filtro_cat === 'NUEVO' ? 'selected' : '' ?>>Nuevo</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-search mr-1"></i> Filtrar</button>
                        <a href="<?= url('store', 'cliente', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times mr-1"></i> Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($clientes)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                    <p>No se encontraron clientes</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Cliente</th><th>Identificación</th><th>Teléfono</th><th>Email</th>
                                <th class="text-center">Categoría</th><th class="text-center">Puntos</th>
                                <th class="text-right">Compras</th><th width="130" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cl): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($cl['cli_nombres'] . ' ' . $cl['cli_apellidos']) ?></strong>
                                </td>
                                <td><small><code><?= htmlspecialchars($cl['cli_identificacion'] ?? '—') ?></code></small></td>
                                <td><small><?= htmlspecialchars($cl['cli_telefono'] ?? '—') ?></small></td>
                                <td><small><?= htmlspecialchars($cl['cli_email'] ?? '—') ?></small></td>
                                <td class="text-center">
                                    <?php
                                    $catC = ['VIP'=>'warning','FRECUENTE'=>'info','REGULAR'=>'success','NUEVO'=>'secondary'];
                                    $cat = $cl['scl_categoria'] ?? 'NUEVO';
                                    ?>
                                    <span class="badge badge-<?= $catC[$cat] ?? 'secondary' ?>"><?= $cat ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light"><i class="fas fa-star text-warning mr-1"></i><?= intval($cl['scl_puntos_disponibles'] ?? 0) ?></span>
                                </td>
                                <td class="text-right">$<?= number_format($cl['scl_total_compras'] ?? 0, 2) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('store', 'cliente', 'ver', ['id' => $cl['cli_cliente_id']]) ?>" class="btn btn-outline-info"><i class="fas fa-eye"></i></a>
                                        <a href="<?= url('store', 'cliente', 'editar', ['id' => $cl['cli_cliente_id']]) ?>" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-outline-danger btn-del-cli" data-id="<?= $cl['cli_cliente_id'] ?>" data-nombre="<?= htmlspecialchars($cl['cli_nombres']) ?>"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($totalPaginas > 1): ?>
            <div class="card-footer">
                <nav><ul class="pagination pagination-sm justify-content-center mb-0">
                    <?php for ($pg = 1; $pg <= $totalPaginas; $pg++): ?>
                    <li class="page-item <?= $pg == $pagina ? 'active' : '' ?>"><a class="page-link" href="<?= url('store', 'cliente', 'index', ['pagina' => $pg]) ?>"><?= $pg ?></a></li>
                    <?php endfor; ?>
                </ul></nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<script>
document.querySelectorAll('.btn-del-cli').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id, nombre = this.dataset.nombre;
        Swal.fire({
            title: '¿Eliminar cliente?', html: '<strong>' + nombre + '</strong>', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (r.isConfirmed) {
                var fd = new FormData();
                fd.append('csrf_token', '<?= htmlspecialchars($csrf_token ?? '') ?>');
                fd.append('id', id);
                fetch('<?= url('store', 'cliente', 'eliminar') ?>', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) { Swal.fire({ icon: 'success', title: d.message, timer: 1500, showConfirmButton: false }).then(function() { location.reload(); }); }
                    else { Swal.fire({ icon: 'error', title: 'Error', text: d.message }); }
                });
            }
        });
    });
});
</script>
<?php $scripts = ob_get_clean(); ?>
