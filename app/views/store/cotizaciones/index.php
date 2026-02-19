<?php
/**
 * DigiSports Store - Cotizaciones / Proformas
 */
$cotizaciones = $cotizaciones ?? [];
$estadoFiltro = $estadoFiltro ?? '';
$fechaDesde   = $fechaDesde ?? date('Y-m-01');
$fechaHasta   = $fechaHasta ?? date('Y-m-d');
$moduloColor  = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-file-alt mr-2" style="color:<?= $moduloColor ?>"></i>Cotizaciones</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><span class="badge badge-light px-3 py-2"><?= count($cotizaciones) ?> registros</span></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('store', 'cotizacion', 'index') ?>" class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <?php foreach (['BORRADOR','ENVIADA','ACEPTADA','RECHAZADA','VENCIDA'] as $e): ?>
                            <option value="<?= $e ?>" <?= $estadoFiltro === $e ? 'selected' : '' ?>><?= $e ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2"><label class="small mb-1">Desde</label><input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= $fechaDesde ?>"></div>
                    <div class="col-md-2"><label class="small mb-1">Hasta</label><input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= $fechaHasta ?>"></div>
                    <div class="col-md-5">
                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-search mr-1"></i> Filtrar</button>
                        <a href="<?= url('store', 'cotizacion', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times mr-1"></i> Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($cotizaciones)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-file-alt fa-3x mb-3 opacity-50"></i><p>No hay cotizaciones</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th>Nº</th><th>Cliente</th><th>Fecha</th><th class="text-center">Vigencia</th><th class="text-center">Estado</th><th class="text-right">Total</th><th width="160" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cotizaciones as $c):
                                $estC = ['BORRADOR'=>'secondary','ENVIADA'=>'primary','ACEPTADA'=>'success','RECHAZADA'=>'danger','VENCIDA'=>'dark'];
                                $estado = $c['cot_estado'] ?? 'BORRADOR';
                                $fechaCot = strtotime($c['cot_fecha']);
                                $vigenciaDias = intval($c['cot_vigencia_dias'] ?? 15);
                                $fechaVence = date('d/m/Y', strtotime("+{$vigenciaDias} days", $fechaCot));
                                $vencido = (time() > strtotime("+{$vigenciaDias} days", $fechaCot));
                                $cliente = trim(($c['cli_nombres'] ?? '') . ' ' . ($c['cli_apellidos'] ?? ''));
                            ?>
                            <tr>
                                <td><code><?= htmlspecialchars($c['cot_numero'] ?? $c['cot_cotizacion_id']) ?></code></td>
                                <td><?= !empty($cliente) ? htmlspecialchars($cliente) : '<small class="text-muted">Sin cliente</small>' ?></td>
                                <td><small><?= date('d/m/Y', $fechaCot) ?></small></td>
                                <td class="text-center">
                                    <small><?= $vigenciaDias ?> días</small>
                                    <?php if ($vencido && $estado === 'BORRADOR'): ?>
                                    <br><small class="text-danger"><i class="fas fa-clock"></i> Vencida</small>
                                    <?php else: ?>
                                    <br><small class="text-muted">hasta <?= $fechaVence ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><span class="badge badge-<?= $estC[$estado] ?? 'light' ?>"><?= $estado ?></span></td>
                                <td class="text-right font-weight-bold">$<?= number_format($c['cot_total'] ?? 0, 2) ?></td>
                                <td class="text-center">
                                    <?php if (in_array($estado, ['BORRADOR', 'ENVIADA'])): ?>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($estado === 'BORRADOR'): ?>
                                        <button class="btn btn-outline-primary btn-cambiar-estado" data-id="<?= $c['cot_cotizacion_id'] ?>" data-estado="ENVIADA" title="Marcar como enviada"><i class="fas fa-paper-plane"></i></button>
                                        <?php endif; ?>
                                        <button class="btn btn-outline-success btn-cambiar-estado" data-id="<?= $c['cot_cotizacion_id'] ?>" data-estado="ACEPTADA" title="Aceptada"><i class="fas fa-check"></i></button>
                                        <button class="btn btn-outline-danger btn-cambiar-estado" data-id="<?= $c['cot_cotizacion_id'] ?>" data-estado="RECHAZADA" title="Rechazada"><i class="fas fa-times"></i></button>
                                    </div>
                                    <?php endif; ?>
                                </td>
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
document.querySelectorAll('.btn-cambiar-estado').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id, estado = this.dataset.estado;
        Swal.fire({
            title: '¿Cambiar estado?', html: 'La cotización pasará a <strong>' + estado + '</strong>',
            icon: 'question', showCancelButton: true, confirmButtonText: 'Sí, cambiar', cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (r.isConfirmed) {
                var fd = new FormData();
                fd.append('csrf_token', '<?= htmlspecialchars($csrf_token ?? '') ?>');
                fd.append('id', id);
                fd.append('estado', estado);
                fetch('<?= url('store', 'cotizacion', 'cambiarEstado') ?>', { method: 'POST', body: fd })
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
