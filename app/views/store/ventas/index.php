<?php
/**
 * DigiSports Store - Historial de Ventas
 */
$ventas       = $ventas ?? [];
$resumen      = $resumen ?? [];
$buscar       = $buscar ?? '';
$filtro_estado = $filtro_estado ?? '';
$filtro_tipo   = $filtro_tipo ?? '';
$fecha_desde   = $fecha_desde ?? '';
$fecha_hasta   = $fecha_hasta ?? '';
$pagina       = $pagina ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$total        = $total ?? 0;
$moduloColor  = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-receipt mr-2" style="color:<?= $moduloColor ?>"></i>Historial de Ventas <small class="text-muted">(<?= $total ?>)</small></h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('store', 'pos', 'index') ?>" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-cash-register mr-1"></i> Ir al POS
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
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>$<?= number_format($resumen['total_monto'] ?? 0, 2) ?></h3>
                        <p>Total Ventas</p>
                    </div>
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= intval($resumen['total_ventas'] ?? 0) ?></h3>
                        <p>Transacciones</p>
                    </div>
                    <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>$<?= number_format($resumen['ticket_promedio'] ?? 0, 2) ?></h3>
                        <p>Ticket Promedio</p>
                    </div>
                    <div class="icon"><i class="fas fa-receipt"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= intval($resumen['anuladas'] ?? 0) ?></h3>
                        <p>Anuladas</p>
                    </div>
                    <div class="icon"><i class="fas fa-times-circle"></i></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <form method="POST" action="<?= url('store', 'venta', 'index') ?>" class="row align-items-end">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <div class="col-md-2">
                        <label class="small mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" placeholder="#Venta, cliente..." value="<?= htmlspecialchars($buscar) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="COMPLETADA" <?= $filtro_estado === 'COMPLETADA' ? 'selected' : '' ?>>Completada</option>
                            <option value="PENDIENTE" <?= $filtro_estado === 'PENDIENTE' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="ANULADA" <?= $filtro_estado === 'ANULADA' ? 'selected' : '' ?>>Anulada</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-1">Tipo Doc.</label>
                        <select name="tipo_documento" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="FACTURA" <?= $filtro_tipo === 'FACTURA' ? 'selected' : '' ?>>Factura</option>
                            <option value="NOTA_VENTA" <?= $filtro_tipo === 'NOTA_VENTA' ? 'selected' : '' ?>>Nota Venta</option>
                            <option value="TICKET" <?= $filtro_tipo === 'TICKET' ? 'selected' : '' ?>>Ticket</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-1">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= htmlspecialchars($fecha_desde) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-1">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= htmlspecialchars($fecha_hasta) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-search mr-1"></i> Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($ventas)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-receipt fa-3x mb-3 opacity-50"></i>
                    <p>No se encontraron ventas</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#Venta</th><th>Cliente</th><th>Tipo</th>
                                <th class="text-right">Subtotal</th><th class="text-right">IVA</th>
                                <th class="text-right">Total</th><th class="text-center">Estado</th>
                                <th>Fecha</th><th width="80"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $v): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('store', 'venta', 'ver', ['id' => $v['ven_venta_id']]) ?>" class="font-weight-bold" style="color:<?= $moduloColor ?>">
                                        <?= htmlspecialchars($v['ven_numero'] ?? '—') ?>
                                    </a>
                                </td>
                                <td><?= !empty($v['cli_nombres']) ? htmlspecialchars($v['cli_nombres'] . ' ' . $v['cli_apellidos']) : '<span class="text-muted">C. Final</span>' ?></td>
                                <td><small class="badge badge-light"><?= $v['ven_tipo_documento'] ?? '—' ?></small></td>
                                <td class="text-right">$<?= number_format($v['ven_subtotal'] ?? 0, 2) ?></td>
                                <td class="text-right">$<?= number_format($v['ven_iva'] ?? 0, 2) ?></td>
                                <td class="text-right"><strong>$<?= number_format($v['ven_total'] ?? 0, 2) ?></strong></td>
                                <td class="text-center">
                                    <?php $ec = ['COMPLETADA'=>'success','PENDIENTE'=>'warning','ANULADA'=>'danger']; ?>
                                    <span class="badge badge-<?= $ec[$v['ven_estado']] ?? 'secondary' ?>"><?= $v['ven_estado'] ?? '—' ?></span>
                                </td>
                                <td><small><?= date('d/m/y H:i', strtotime($v['ven_fecha'])) ?></small></td>
                                <td>
                                    <a href="<?= url('store', 'venta', 'ver', ['id' => $v['ven_venta_id']]) ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
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
                    <li class="page-item <?= $pg == $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('store', 'venta', 'index', ['pagina' => $pg]) ?>"><?= $pg ?></a>
                    </li>
                    <?php endfor; ?>
                </ul></nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
