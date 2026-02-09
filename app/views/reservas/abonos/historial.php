<?php
/**
 * DigiSports Arena — Vista: Historial de Movimientos
 * Historial global de todas las transacciones del monedero
 */

$movimientos    = $movimientos ?? [];
$tipo           = $tipo ?? '';
$pagina         = $pagina ?? 1;
$totalPaginas   = $totalPaginas ?? 1;
$totalRegistros = $totalRegistros ?? 0;
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-history mr-2 text-primary"></i>
                    Historial de Movimientos
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('reservas', 'abon', 'index') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Monederos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Filtro -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <form class="row align-items-end">
                    <div class="col-md-4">
                        <label class="small text-muted">Tipo de Movimiento</label>
                        <select name="tipo" class="form-control" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="RECARGA" <?= $tipo==='RECARGA'?'selected':'' ?>>🟢 Recarga</option>
                            <option value="CONSUMO" <?= $tipo==='CONSUMO'?'selected':'' ?>>🔴 Consumo</option>
                            <option value="DEVOLUCION" <?= $tipo==='DEVOLUCION'?'selected':'' ?>>🔵 Devolución</option>
                            <option value="AJUSTE" <?= $tipo==='AJUSTE'?'selected':'' ?>>🟡 Ajuste</option>
                            <option value="VENCIMIENTO" <?= $tipo==='VENCIMIENTO'?'selected':'' ?>>⚪ Vencimiento</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <span class="badge badge-primary px-3 py-2">
                            <?= $totalRegistros ?> movimiento<?= $totalRegistros !== 1 ? 's' : '' ?>
                        </span>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card">
            <div class="card-body p-0">
                <?php if (!empty($movimientos)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Saldo Anterior</th>
                                <th>Saldo Posterior</th>
                                <th>Descripción</th>
                                <th>Referencia</th>
                                <th>Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $m): ?>
                            <?php
                                $tipoConfigs = [
                                    'RECARGA'     => ['icon'=>'fa-arrow-up','color'=>'success','sign'=>'+'],
                                    'CONSUMO'     => ['icon'=>'fa-arrow-down','color'=>'danger','sign'=>'-'],
                                    'DEVOLUCION'  => ['icon'=>'fa-undo','color'=>'info','sign'=>'+'],
                                    'AJUSTE'      => ['icon'=>'fa-sliders-h','color'=>'warning','sign'=>'±'],
                                    'VENCIMIENTO' => ['icon'=>'fa-clock','color'=>'secondary','sign'=>'-']
                                ];
                                $tipoConfig = $tipoConfigs[$m['tipo'] ?? ''] ?? ['icon'=>'fa-circle','color'=>'muted','sign'=>''];
                            ?>
                            <tr>
                                <td><small><?= date('d/m/Y H:i', strtotime($m['fecha_registro'])) ?></small></td>
                                <td><strong><?= htmlspecialchars($m['cliente_nombre'] ?? '-') ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= $tipoConfig['color'] ?>">
                                        <i class="fas <?= $tipoConfig['icon'] ?> mr-1"></i>
                                        <?= $m['tipo'] ?>
                                    </span>
                                </td>
                                <td class="text-<?= $tipoConfig['color'] ?> font-weight-bold">
                                    <?= $tipoConfig['sign'] ?>$<?= number_format((float)$m['monto'], 2) ?>
                                </td>
                                <td class="text-muted">$<?= number_format((float)$m['saldo_anterior'], 2) ?></td>
                                <td><strong>$<?= number_format((float)$m['saldo_posterior'], 2) ?></strong></td>
                                <td><small><?= htmlspecialchars($m['descripcion'] ?? '-') ?></small></td>
                                <td>
                                    <?php if (!empty($m['referencia_tipo'])): ?>
                                        <small class="badge badge-outline-secondary">
                                            <?= $m['referencia_tipo'] ?><?= $m['referencia_id'] ? " #{$m['referencia_id']}" : '' ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">—</small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars($m['forma_pago'] ?? '-') ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                <div class="card-footer clearfix">
                    <ul class="pagination pagination-sm m-0 float-right">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('reservas', 'abon', 'historial', ['pagina' => $i, 'tipo' => $tipo]) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-exchange-alt fa-4x mb-3" style="opacity: .2"></i>
                    <p>No hay movimientos registrados</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>
