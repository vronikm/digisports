<?php
/**
 * DigiSports Arena — Vista: Historial de Pagos
 * Lista paginada con filtros, KPIs y resumen financiero
 */
$pagos          = $pagos ?? [];
$resumen        = $resumen ?? [];
$totalRegistros = $totalRegistros ?? 0;
$pagina         = $pagina ?? 1;
$totalPaginas   = $totalPaginas ?? 1;
$buscar         = $buscar ?? '';
$desde          = $desde ?? date('Y-m-01');
$hasta          = $hasta ?? date('Y-m-d');
$csrf           = $csrf_token ?? '';

$formaIcons  = ['EFECTIVO'=>'fa-money-bill-wave','TARJETA'=>'fa-credit-card','TRANSFERENCIA'=>'fa-university','MONEDERO'=>'fa-wallet','MIXTO'=>'fa-exchange-alt'];
$formaColors = ['EFECTIVO'=>'success','TARJETA'=>'primary','TRANSFERENCIA'=>'info','MONEDERO'=>'warning','MIXTO'=>'secondary'];
$estadoColors = ['COMPLETADO'=>'success','ANULADO'=>'danger','PENDIENTE'=>'warning'];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-cash-register mr-2 text-primary"></i> Historial de Pagos</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= url('reservas', 'reserva', 'index') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-calendar-alt mr-1"></i> Reservas
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- KPI Cards -->
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="small-box bg-gradient-success">
                    <div class="inner">
                        <h3>$<?= number_format($resumen['total_monto'] ?? 0, 2) ?></h3>
                        <p>Total Recaudado</p>
                    </div>
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="small-box bg-gradient-primary">
                    <div class="inner">
                        <h3><?= (int)($resumen['total_pagos'] ?? 0) ?></h3>
                        <p>Total Cobros</p>
                    </div>
                    <div class="icon"><i class="fas fa-receipt"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="small-box bg-gradient-info">
                    <div class="inner">
                        <h3>$<?= number_format($resumen['total_efectivo'] ?? 0, 2) ?></h3>
                        <p>Efectivo</p>
                    </div>
                    <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="small-box bg-gradient-warning">
                    <div class="inner">
                        <h3>$<?= number_format($resumen['total_monedero'] ?? 0, 2) ?></h3>
                        <p>Monedero</p>
                    </div>
                    <div class="icon"><i class="fas fa-wallet"></i></div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card card-outline card-primary">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros</h3>
                <div class="card-tools">
                    <button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body py-2">
                <form method="POST" action="<?= url('reservas', 'pago', 'index') ?>" class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small mb-1">Desde</label>
                        <input type="date" name="desde" class="form-control form-control-sm" value="<?= htmlspecialchars($desde) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="small mb-1">Hasta</label>
                        <input type="date" name="hasta" class="form-control form-control-sm" value="<?= htmlspecialchars($hasta) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="small mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Cliente o referencia..." value="<?= htmlspecialchars($buscar) ?>">
                    </div>
                    <div class="col-md-2 d-flex">
                        <button type="submit" class="btn btn-primary btn-sm mr-1 flex-fill">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                        <a href="<?= url('reservas', 'pago', 'index') ?>" class="btn btn-outline-secondary btn-sm" title="Limpiar">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de pagos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-1"></i> Pagos (<?= $totalRegistros ?>)</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php if (empty($pagos)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No se encontraron pagos en el período seleccionado</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width:60px">#</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Instalación</th>
                                <th class="text-center">Forma</th>
                                <th class="text-right">Monto</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" style="width:100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $p): ?>
                            <?php
                                $fi  = $formaIcons[$p['pag_tipo_pago'] ?? ''] ?? 'fa-dollar-sign';
                                $fc  = $formaColors[$p['pag_tipo_pago'] ?? ''] ?? 'secondary';
                                $ec  = $estadoColors[$p['pag_estado'] ?? ''] ?? 'secondary';
                            ?>
                            <tr>
                                <td class="text-center"><?= $p['pag_pago_id'] ?></td>
                                <td>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($p['pag_fecha_pago'])) ?></small>
                                    <br><small><?= date('H:i', strtotime($p['pag_fecha_pago'])) ?></small>
                                </td>
                                <td><?= htmlspecialchars($p['cliente_nombre']) ?></td>
                                <td>
                                    <?= htmlspecialchars($p['instalacion_nombre']) ?>
                                    <br><small class="text-muted"><?= date('d/m', strtotime($p['res_fecha_reserva'])) ?> <?= date('H:i', strtotime($p['res_hora_inicio'])) ?>-<?= date('H:i', strtotime($p['res_hora_fin'])) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $fc ?> px-2 py-1">
                                        <i class="fas <?= $fi ?> mr-1"></i> <?= $p['pag_tipo_pago'] ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <strong class="<?= $p['pag_estado'] === 'ANULADO' ? 'text-muted text-decoration-line-through' : 'text-success' ?>">
                                        $<?= number_format($p['pag_monto'], 2) ?>
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $ec ?>"><?= $p['pag_estado'] ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= url('reservas', 'pago', 'comprobante') ?>&id=<?= $p['pag_pago_id'] ?>"
                                       class="btn btn-xs btn-info" title="Ver comprobante">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                    <?php if ($p['pag_estado'] === 'COMPLETADO'): ?>
                                    <button class="btn btn-xs btn-danger btn-anular" data-id="<?= $p['pag_pago_id'] ?>"
                                            data-monto="<?= number_format($p['pag_monto'], 2) ?>" title="Anular">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <?php if ($totalPaginas > 1): ?>
            <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('reservas', 'pago', 'index') ?>&pagina=<?= $i ?>&desde=<?= $desde ?>&hasta=<?= $hasta ?>&buscar=<?= urlencode($buscar) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
                <small class="text-muted float-left mt-1">
                    Mostrando página <?= $pagina ?> de <?= $totalPaginas ?> (<?= $totalRegistros ?> registros)
                </small>
            </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<script>
document.querySelectorAll('.btn-anular').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var pagoId = this.dataset.id;
        var monto  = this.dataset.monto;
        Swal.fire({
            title: '¿Anular este pago?',
            html: 'Se anulará el pago de <strong>$' + monto + '</strong>.<br>Si incluía monedero, el saldo será devuelto.',
            icon: 'warning',
            input: 'text',
            inputLabel: 'Motivo de anulación (opcional)',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-ban mr-1"></i> Anular',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                var form = new FormData();
                form.append('pago_id', pagoId);
                form.append('motivo', result.value || '');
                form.append('csrf_token', '<?= $csrf ?>');

                fetch('<?= url('reservas', 'pago', 'anular') ?>', {
                    method: 'POST',
                    body: form
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        Swal.fire('Anulado', data.message, 'success').then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo anular', 'error');
                    }
                })
                .catch(function() {
                    Swal.fire('Error', 'Error de comunicación', 'error');
                });
            }
        });
    });
});
</script>
