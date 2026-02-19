<?php
/**
 * DigiSports Store - Alertas de Stock
 */
$alertas      = $alertas ?? [];
$estadoFiltro = $estadoFiltro ?? 'PENDIENTE';
$moduloColor  = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-bell mr-2" style="color:<?= $moduloColor ?>"></i>Alertas de Stock <span class="badge badge-warning ml-1"><?= count($alertas) ?></span></h1></div>
            <div class="col-sm-6"><div class="float-sm-right">
                <a href="<?= url('store', 'stock', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Inventario</a>
            </div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtro -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('store', 'stock', 'alertas') ?>" class="form-inline">
                    <label class="small mr-2">Estado:</label>
                    <select name="estado" class="form-control form-control-sm mr-2">
                        <option value="PENDIENTE" <?= $estadoFiltro === 'PENDIENTE' ? 'selected' : '' ?>>Pendientes</option>
                        <option value="RESUELTA" <?= $estadoFiltro === 'RESUELTA' ? 'selected' : '' ?>>Resueltas</option>
                        <option value="IGNORADA" <?= $estadoFiltro === 'IGNORADA' ? 'selected' : '' ?>>Ignoradas</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-filter mr-1"></i> Filtrar</button>
                </form>
            </div>
        </div>

        <!-- Lista -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($alertas)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i>
                    <p>No hay alertas <?= strtolower($estadoFiltro) ?>s</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th>Producto</th><th>Código</th><th class="text-center">Tipo</th><th>Mensaje</th><th>Fecha</th>
                                <?php if ($estadoFiltro === 'PENDIENTE'): ?><th class="text-center" width="180">Acciones</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alertas as $a):
                                $tipoA = $a['ale_tipo'] ?? 'STOCK_BAJO';
                                $tipoIcon = $tipoA === 'AGOTADO' ? 'fa-times-circle text-danger' : 'fa-exclamation-triangle text-warning';
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= url('store', 'producto', 'ver', ['id' => $a['ale_producto_id']]) ?>">
                                        <strong><?= htmlspecialchars($a['pro_nombre'] ?? '—') ?></strong>
                                    </a>
                                </td>
                                <td><code><?= htmlspecialchars($a['pro_codigo'] ?? '') ?></code></td>
                                <td class="text-center"><i class="fas <?= $tipoIcon ?> mr-1"></i><small><?= $tipoA ?></small></td>
                                <td><small class="text-muted"><?= htmlspecialchars($a['ale_mensaje'] ?? '') ?></small></td>
                                <td><small><?= date('d/m/Y H:i', strtotime($a['ale_fecha_generada'])) ?></small></td>
                                <?php if ($estadoFiltro === 'PENDIENTE'): ?>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-success btn-resolver" data-id="<?= $a['ale_alerta_id'] ?>" data-accion="RESUELTA" title="Marcar como resuelta"><i class="fas fa-check mr-1"></i>Resolver</button>
                                    <button class="btn btn-xs btn-secondary btn-resolver" data-id="<?= $a['ale_alerta_id'] ?>" data-accion="IGNORADA" title="Ignorar"><i class="fas fa-eye-slash"></i></button>
                                </td>
                                <?php endif; ?>
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

<?php ob_start(); ?>
<script>
document.querySelectorAll('.btn-resolver').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id, accion = this.dataset.accion;
        var fd = new FormData();
        fd.append('csrf_token', '<?= htmlspecialchars($csrf_token ?? '') ?>');
        fd.append('alerta_id', id);
        fd.append('accion', accion);
        fetch('<?= url('store', 'stock', 'resolverAlerta') ?>', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                Swal.fire({ icon: 'success', title: d.message, timer: 1200, showConfirmButton: false }).then(function() { location.reload(); });
            } else { Swal.fire({ icon: 'error', title: 'Error', text: d.message }); }
        });
    });
});
</script>
<?php $scripts = ob_get_clean(); ?>
