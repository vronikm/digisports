<?php
/**
 * Detalle de Ingresos — Reporte Arena
 */
$moduloColor = $modulo_actual['color'] ?? '#8B5CF6';
$detalle = $detalle ?? [];
$totalMonto = 0;
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-file-invoice-dollar mr-2" style="color: <?= $moduloColor ?>"></i>
                    Detalle de Ingresos
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('instalaciones', 'reporteArena', 'index') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a Reportes
                    </a>
                </div>
            </div>
        </div>
        <small class="text-muted">
            <i class="fas fa-calendar mr-1"></i>
            <?= date('d/m/Y', strtotime($fecha_desde)) ?> — <?= date('d/m/Y', strtotime($fecha_hasta)) ?>
        </small>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <!-- Filtro rápido -->
        <div class="card card-outline card-primary">
            <div class="card-body p-2">
                <form method="get" class="form-inline">
                    <input type="hidden" name="modulo" value="instalaciones">
                    <input type="hidden" name="controller" value="reporteArena">
                    <input type="hidden" name="action" value="ingresos">
                    <div class="form-group mr-2">
                        <label class="mr-1 small">Desde:</label>
                        <input type="date" class="form-control form-control-sm" name="desde" value="<?= htmlspecialchars($fecha_desde) ?>">
                    </div>
                    <div class="form-group mr-2">
                        <label class="mr-1 small">Hasta:</label>
                        <input type="date" class="form-control form-control-sm" name="hasta" value="<?= htmlspecialchars($fecha_hasta) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filtrar</button>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card">
            <div class="card-body p-0">
                <?php if (!empty($detalle)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Cancha</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th class="text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalle as $d): ?>
                            <?php $totalMonto += (float)$d['rpa_monto']; ?>
                            <tr>
                                <td><small class="text-muted">#<?= $d['rpa_pago_id'] ?></small></td>
                                <td><?= date('d/m/Y H:i', strtotime($d['rpa_fecha'])) ?></td>
                                <td><?= htmlspecialchars($d['cliente_nombre'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($d['cancha_nombre'] ?? '-') ?></td>
                                <td>
                                    <?php
                                    $iconosM = [
                                        'EFECTIVO' => 'fas fa-money-bill-wave text-success',
                                        'TARJETA' => 'fas fa-credit-card text-primary',
                                        'TRANSFERENCIA' => 'fas fa-university text-info',
                                        'MONEDERO' => 'fas fa-wallet text-warning',
                                    ];
                                    $ic = $iconosM[$d['rpa_metodo_pago']] ?? 'fas fa-receipt';
                                    ?>
                                    <i class="<?= $ic ?> mr-1"></i> <?= $d['rpa_metodo_pago'] ?>
                                </td>
                                <td><small><?= htmlspecialchars($d['rpa_referencia'] ?? '-') ?></small></td>
                                <td class="text-right"><strong>$<?= number_format($d['rpa_monto'], 2) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light font-weight-bold">
                                <td colspan="6" class="text-right">TOTAL:</td>
                                <td class="text-right text-success" style="font-size:1.2em">$<?= number_format($totalMonto, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer text-muted text-center">
                    <small><?= count($detalle) ?> registros encontrados</small>
                </div>
                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3" style="opacity:.3"></i>
                    <p>No hay ingresos en el período seleccionado</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>
