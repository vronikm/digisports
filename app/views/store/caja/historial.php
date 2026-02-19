<?php
/**
 * DigiSports Store - Historial de Turnos
 */
$turnos      = $turnos ?? [];
$cajas       = $cajas ?? [];
$filtro_caja = $filtro_caja ?? '';
$fecha_desde = $fecha_desde ?? '';
$fecha_hasta = $fecha_hasta ?? '';
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-history mr-2" style="color:<?= $moduloColor ?>"></i>Historial de Turnos</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('store', 'caja', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Caja</a>
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
                <form method="POST" action="<?= url('store', 'caja', 'historial') ?>" class="row align-items-end">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <div class="col-md-3">
                        <label class="small mb-1">Caja</label>
                        <select name="caja_id" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            <?php foreach ($cajas as $c): ?>
                            <option value="<?= $c['caj_caja_id'] ?>" <?= $filtro_caja == $c['caj_caja_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['caj_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small mb-1">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= htmlspecialchars($fecha_desde) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="small mb-1">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= htmlspecialchars($fecha_hasta) ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-search mr-1"></i> Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($turnos)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-history fa-3x mb-3 opacity-50"></i><p>No se encontraron turnos</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Caja</th><th>Usuario</th><th>Apertura</th><th>Cierre</th>
                                <th class="text-right">Ventas</th><th class="text-center">#</th>
                                <th class="text-right">Diferencia</th><th class="text-center">Estado</th><th width="80"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($turnos as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['caj_nombre'] ?? '—') ?></td>
                                <td><small><?= htmlspecialchars($t['usuario_nombre'] ?? '—') ?></small></td>
                                <td><small><?= date('d/m/y H:i', strtotime($t['tur_fecha_apertura'])) ?></small></td>
                                <td><small><?= !empty($t['tur_fecha_cierre']) ? date('d/m/y H:i', strtotime($t['tur_fecha_cierre'])) : '—' ?></small></td>
                                <td class="text-right"><strong>$<?= number_format($t['tur_total_ventas'] ?? 0, 2) ?></strong></td>
                                <td class="text-center"><?= intval($t['tur_num_ventas'] ?? 0) ?></td>
                                <td class="text-right">
                                    <?php $dif = floatval($t['tur_diferencia'] ?? 0); ?>
                                    <span class="<?= $dif == 0 ? 'text-success' : ($dif > 0 ? 'text-info' : 'text-danger') ?>">$<?= number_format($dif, 2) ?></span>
                                </td>
                                <td class="text-center"><span class="badge badge-<?= ($t['tur_estado'] ?? '') === 'ABIERTO' ? 'success' : 'secondary' ?>"><?= $t['tur_estado'] ?? '—' ?></span></td>
                                <td><a href="<?= url('store', 'caja', 'verTurno', ['id' => $t['tur_turno_id']]) ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
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
