<?php
/**
 * DigiSports Store - Devoluciones
 */
$devoluciones = $devoluciones ?? [];
$buscar       = $buscar ?? '';
$estadoFiltro = $estadoFiltro ?? '';
$fechaDesde   = $fechaDesde ?? date('Y-m-01');
$fechaHasta   = $fechaHasta ?? date('Y-m-d');
$moduloColor  = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-undo mr-2" style="color:<?= $moduloColor ?>"></i>Devoluciones</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><span class="badge badge-light px-3 py-2"><?= count($devoluciones) ?> registros</span></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('store', 'devolucion', 'index') ?>" class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Nº devolución, Nº venta..." value="<?= htmlspecialchars($buscar) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="COMPLETADA" <?= $estadoFiltro === 'COMPLETADA' ? 'selected' : '' ?>>Completada</option>
                            <option value="PENDIENTE" <?= $estadoFiltro === 'PENDIENTE' ? 'selected' : '' ?>>Pendiente</option>
                        </select>
                    </div>
                    <div class="col-md-2"><label class="small mb-1">Desde</label><input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= $fechaDesde ?>"></div>
                    <div class="col-md-2"><label class="small mb-1">Hasta</label><input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= $fechaHasta ?>"></div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-search mr-1"></i> Filtrar</button>
                        <a href="<?= url('store', 'devolucion', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times mr-1"></i> Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($devoluciones)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-undo fa-3x mb-3 opacity-50"></i><p>No hay devoluciones en el rango seleccionado</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th>Nº Devolución</th><th>Nº Venta</th><th>Fecha</th><th class="text-center">Estado</th><th class="text-center">Reembolso</th><th class="text-right">Total</th><th>Motivo</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devoluciones as $d):
                                $estD = ['COMPLETADA'=>'success','PENDIENTE'=>'warning'];
                                $estado = $d['dev_estado'] ?? 'COMPLETADA';
                            ?>
                            <tr>
                                <td><code><?= htmlspecialchars($d['dev_numero'] ?? $d['dev_devolucion_id']) ?></code></td>
                                <td>
                                    <a href="<?= url('store', 'venta', 'ver', ['id' => $d['dev_venta_id']]) ?>">
                                        <code><?= htmlspecialchars($d['ven_numero'] ?? '—') ?></code>
                                    </a>
                                </td>
                                <td><small><?= date('d/m/Y H:i', strtotime($d['dev_fecha'])) ?></small></td>
                                <td class="text-center"><span class="badge badge-<?= $estD[$estado] ?? 'light' ?>"><?= $estado ?></span></td>
                                <td class="text-center"><span class="badge badge-outline-secondary"><?= htmlspecialchars($d['dev_tipo_reembolso'] ?? '—') ?></span></td>
                                <td class="text-right font-weight-bold text-danger">-$<?= number_format($d['dev_total'] ?? 0, 2) ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars(mb_substr($d['dev_motivo'] ?? '', 0, 60)) ?><?= mb_strlen($d['dev_motivo'] ?? '') > 60 ? '...' : '' ?></small></td>
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
