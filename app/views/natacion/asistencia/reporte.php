<?php
/**
 * DigiSports Natación - Reporte de Asistencia
 */
$reporte     = $reporte ?? [];
$grupos      = $grupos ?? [];
$moduloColor = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-chart-bar mr-2" style="color:<?= $moduloColor ?>"></i>Reporte de Asistencia</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><a href="<?= url('natacion', 'asistencia', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i>Volver</a></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('natacion', 'asistencia', 'reporte') ?>" class="row align-items-end">
                    <div class="col-md-4">
                        <label class="small">Grupo</label>
                        <select name="grupo_id" class="form-control form-control-sm">
                            <option value="">— Todos —</option>
                            <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['ngr_grupo_id'] ?>" <?= ($grupo_id ?? '') == $g['ngr_grupo_id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['ngr_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="small">Desde</label><input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= $fecha_desde ?? date('Y-m-01') ?>"></div>
                    <div class="col-md-3"><label class="small">Hasta</label><input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= $fecha_hasta ?? date('Y-m-d') ?>"></div>
                    <div class="col-md-2"><button class="btn btn-sm btn-primary btn-block"><i class="fas fa-search mr-1"></i>Generar</button></div>
                </form>
            </div>
        </div>

        <?php if (!empty($reporte)): ?>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th>Alumno</th><th class="text-center">Presentes</th><th class="text-center">Ausentes</th><th class="text-center">Tardanzas</th><th class="text-center">Justificados</th><th class="text-center">Total</th><th class="text-center">% Asist.</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reporte as $r):
                                $total = (int)($r['total'] ?? 0);
                                $pres  = (int)($r['presentes'] ?? 0);
                                $pct   = $total > 0 ? round($pres / $total * 100) : 0;
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars(($r['alu_nombres'] ?? '') . ' ' . ($r['alu_apellidos'] ?? '')) ?></strong></td>
                                <td class="text-center"><span class="badge badge-success"><?= $pres ?></span></td>
                                <td class="text-center"><span class="badge badge-danger"><?= (int)($r['ausentes'] ?? 0) ?></span></td>
                                <td class="text-center"><span class="badge badge-warning"><?= (int)($r['tardanzas'] ?? 0) ?></span></td>
                                <td class="text-center"><span class="badge badge-info"><?= (int)($r['justificados'] ?? 0) ?></span></td>
                                <td class="text-center"><?= $total ?></td>
                                <td class="text-center">
                                    <div class="progress" style="height:18px;min-width:80px;">
                                        <div class="progress-bar bg-<?= $pct >= 80 ? 'success' : ($pct >= 60 ? 'warning' : 'danger') ?>" style="width:<?= $pct ?>%"><?= $pct ?>%</div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card"><div class="card-body text-center text-muted py-4">No hay datos de asistencia para el rango seleccionado</div></div>
        <?php endif; ?>
    </div>
</section>
