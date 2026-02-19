<?php
/**
 * DigiSports Store - Detalle de Venta
 */
$venta       = $venta ?? [];
$items       = $items ?? [];
$pagos       = $pagos ?? [];
$devoluciones = $devoluciones ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-receipt mr-2" style="color:<?= $moduloColor ?>"></i>
                    Venta <?= htmlspecialchars($venta['ven_numero'] ?? '') ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <?php if (($venta['ven_estado'] ?? '') === 'COMPLETADA'): ?>
                    <button class="btn btn-sm btn-outline-danger" onclick="anularVenta(<?= $venta['ven_venta_id'] ?? 0 ?>)">
                        <i class="fas fa-ban mr-1"></i> Anular
                    </button>
                    <a href="<?= url('store', 'devolucion', 'crear', ['venta_id' => $venta['ven_venta_id']]) ?>" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-undo mr-1"></i> Devolución
                    </a>
                    <?php endif; ?>
                    <a href="<?= url('store', 'venta', 'index') ?>" class="btn btn-sm btn-outline-secondary">
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
            <div class="col-lg-8">
                <!-- Items -->
                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-2"></i>Productos (<?= count($items) ?>)</h3></div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead class="thead-light">
                                <tr><th>Producto</th><th class="text-center">Cant.</th><th class="text-right">P. Unit.</th><th class="text-right">Subtotal</th><th class="text-right">IVA</th><th class="text-right">Total</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $it): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($it['pro_nombre'] ?? $it['vit_descripcion'] ?? '—') ?></strong>
                                        <?php if (!empty($it['pro_codigo'])): ?>
                                        <br><small class="text-muted"><code><?= htmlspecialchars($it['pro_codigo']) ?></code></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= intval($it['vit_cantidad'] ?? 0) ?></td>
                                    <td class="text-right">$<?= number_format($it['vit_precio_unitario'] ?? 0, 2) ?></td>
                                    <td class="text-right">$<?= number_format($it['vit_subtotal'] ?? 0, 2) ?></td>
                                    <td class="text-right">$<?= number_format($it['vit_iva'] ?? 0, 2) ?></td>
                                    <td class="text-right"><strong>$<?= number_format($it['vit_total'] ?? 0, 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="font-weight-bold">
                                <tr>
                                    <td colspan="3" class="text-right">Totales:</td>
                                    <td class="text-right">$<?= number_format($venta['ven_subtotal'] ?? 0, 2) ?></td>
                                    <td class="text-right">$<?= number_format($venta['ven_iva'] ?? 0, 2) ?></td>
                                    <td class="text-right text-success">$<?= number_format($venta['ven_total'] ?? 0, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Pagos -->
                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-credit-card mr-2"></i>Pagos</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr><th>Forma de Pago</th><th class="text-right">Monto</th><th>Referencia</th><th>Fecha</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos as $pg): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $iconos = ['EFECTIVO'=>'fa-money-bill-wave','TARJETA'=>'fa-credit-card','TRANSFERENCIA'=>'fa-exchange-alt'];
                                        ?>
                                        <i class="fas <?= $iconos[$pg['pag_forma_pago']] ?? 'fa-dollar-sign' ?> mr-2"></i>
                                        <?= $pg['pag_forma_pago'] ?? '—' ?>
                                    </td>
                                    <td class="text-right"><strong>$<?= number_format($pg['pag_monto'] ?? 0, 2) ?></strong></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($pg['pag_referencia'] ?? '—') ?></small></td>
                                    <td><small><?= !empty($pg['pag_fecha']) ? date('d/m/Y H:i', strtotime($pg['pag_fecha'])) : '—' ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Devoluciones -->
                <?php if (!empty($devoluciones)): ?>
                <div class="card shadow-sm card-outline card-warning">
                    <div class="card-header"><h3 class="card-title text-warning"><i class="fas fa-undo mr-2"></i>Devoluciones</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light"><tr><th>#</th><th>Motivo</th><th class="text-right">Monto</th><th>Estado</th><th>Fecha</th></tr></thead>
                            <tbody>
                                <?php foreach ($devoluciones as $dv): ?>
                                <tr>
                                    <td><?= htmlspecialchars($dv['dev_numero'] ?? '—') ?></td>
                                    <td><small><?= htmlspecialchars($dv['dev_motivo'] ?? '—') ?></small></td>
                                    <td class="text-right text-danger">-$<?= number_format($dv['dev_total'] ?? 0, 2) ?></td>
                                    <td><span class="badge badge-warning"><?= $dv['dev_estado'] ?? '—' ?></span></td>
                                    <td><small><?= date('d/m/Y', strtotime($dv['dev_fecha'])) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <!-- Info Venta -->
                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Información</h3></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Estado</span>
                            <?php $ec = ['COMPLETADA'=>'success','PENDIENTE'=>'warning','ANULADA'=>'danger']; ?>
                            <span class="badge badge-<?= $ec[$venta['ven_estado']] ?? 'secondary' ?>"><?= $venta['ven_estado'] ?? '—' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tipo Documento</span>
                            <span><?= $venta['ven_tipo_documento'] ?? '—' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Fecha</span>
                            <span><?= !empty($venta['ven_fecha']) ? date('d/m/Y H:i', strtotime($venta['ven_fecha'])) : '—' ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Cliente</span>
                            <strong><?= !empty($venta['cli_nombres']) ? htmlspecialchars($venta['cli_nombres'] . ' ' . $venta['cli_apellidos']) : 'Consumidor Final' ?></strong>
                        </div>
                        <?php if (!empty($venta['ven_descuento']) && $venta['ven_descuento'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Descuento</span>
                            <span class="text-danger">-$<?= number_format($venta['ven_descuento'], 2) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($venta['ven_observaciones'])): ?>
                        <hr>
                        <small class="text-muted d-block">Observaciones</small>
                        <p class="small"><?= htmlspecialchars($venta['ven_observaciones']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Total Grande -->
                <div class="card shadow-sm" style="border-left:4px solid <?= $moduloColor ?>;">
                    <div class="card-body text-center">
                        <small class="text-muted d-block mb-1">Total de la Venta</small>
                        <div class="h2 mb-0" style="color:<?= $moduloColor ?>">$<?= number_format($venta['ven_total'] ?? 0, 2) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<script>
function anularVenta(id) {
    Swal.fire({
        title: '¿Anular venta?',
        html: 'Esta acción restaurará el stock y no se puede deshacer.<br><br>' +
              '<textarea id="swalMotivoAnulacion" class="swal2-input" placeholder="Motivo de anulación" style="min-height:60px;"></textarea>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar',
        preConfirm: function() {
            var motivo = document.getElementById('swalMotivoAnulacion').value;
            if (!motivo.trim()) { Swal.showValidationMessage('Ingrese un motivo'); return false; }
            return motivo;
        }
    }).then(function(r) {
        if (r.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('store', 'pos', 'anularVenta') ?>';
            form.innerHTML = '<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">' +
                             '<input type="hidden" name="venta_id" value="' + id + '">' +
                             '<input type="hidden" name="motivo" value="' + r.value + '">';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>
