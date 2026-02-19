<?php
/**
 * DigiSports Natación - Registro de Asistencia
 */
$grupos      = $grupos ?? [];
$alumnos     = $alumnos ?? [];
$asistencias = $asistencias ?? [];
$grupoId     = $grupo_id ?? 0;
$fecha       = $fecha ?? date('Y-m-d');
$moduloColor = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-clipboard-check mr-2" style="color:<?= $moduloColor ?>"></i>Asistencia</h1></div>
            <div class="col-sm-6"><div class="float-sm-right">
                <a href="<?= url('natacion', 'asistencia', 'reporte') ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-chart-bar mr-1"></i>Reporte</a>
            </div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Selector grupo/fecha -->
        <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('natacion', 'asistencia', 'index') ?>" class="row align-items-end" id="formFiltro">
                    <div class="col-md-5">
                        <label class="small">Grupo</label>
                        <select name="grupo_id" class="form-control form-control-sm" onchange="document.getElementById('formFiltro').submit()">
                            <option value="">— Seleccionar grupo —</option>
                            <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['ngr_grupo_id'] ?>" <?= $grupoId == $g['ngr_grupo_id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['ngr_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small">Fecha</label>
                        <input type="date" name="fecha" class="form-control form-control-sm" value="<?= $fecha ?>" onchange="document.getElementById('formFiltro').submit()">
                    </div>
                    <div class="col-md-4 text-right">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-sync mr-1"></i>Cargar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de asistencia -->
        <?php if ($grupoId && !empty($alumnos)): ?>
        <div class="card shadow-sm">
            <div class="card-header" style="background:<?= $moduloColor ?>20;">
                <h3 class="card-title"><i class="fas fa-list-check mr-2"></i>Lista de Asistencia — <?= date('d/m/Y', strtotime($fecha)) ?></h3>
                <div class="card-tools"><span class="badge badge-info"><?= count($alumnos) ?> alumno(s)</span></div>
            </div>
            <div class="card-body p-0">
                <form method="POST" action="<?= url('natacion', 'asistencia', 'guardar') ?>" id="formAsistencia">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="grupo_id" value="<?= $grupoId ?>">
                    <input type="hidden" name="fecha" value="<?= $fecha ?>">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Alumno</th>
                                <th class="text-center" width="100"><i class="fas fa-check text-success"></i> Presente</th>
                                <th class="text-center" width="100"><i class="fas fa-times text-danger"></i> Ausente</th>
                                <th class="text-center" width="100"><i class="fas fa-clock text-warning"></i> Tardanza</th>
                                <th class="text-center" width="100"><i class="fas fa-file-alt text-info"></i> Justificado</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos as $i => $al):
                                $estado = '';
                                $obs = '';
                                foreach ($asistencias as $as) {
                                    if ((int)$as['nas_alumno_id'] === (int)$al['alu_alumno_id']) {
                                        $estado = $as['nas_estado']; $obs = $as['nas_observacion'] ?? ''; break;
                                    }
                                }
                            ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($al['alu_nombres'] . ' ' . $al['alu_apellidos']) ?></strong></td>
                                <td class="text-center"><input type="radio" name="asistencia[<?= $al['alu_alumno_id'] ?>]" value="PRESENTE" <?= $estado === 'PRESENTE' || empty($estado) ? 'checked' : '' ?>></td>
                                <td class="text-center"><input type="radio" name="asistencia[<?= $al['alu_alumno_id'] ?>]" value="AUSENTE" <?= $estado === 'AUSENTE' ? 'checked' : '' ?>></td>
                                <td class="text-center"><input type="radio" name="asistencia[<?= $al['alu_alumno_id'] ?>]" value="TARDANZA" <?= $estado === 'TARDANZA' ? 'checked' : '' ?>></td>
                                <td class="text-center"><input type="radio" name="asistencia[<?= $al['alu_alumno_id'] ?>]" value="JUSTIFICADO" <?= $estado === 'JUSTIFICADO' ? 'checked' : '' ?>></td>
                                <td><input type="text" name="observacion[<?= $al['alu_alumno_id'] ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($obs) ?>" placeholder="Observación..."></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-save mr-1"></i>Guardar Asistencia</button>
                    </div>
                </form>
            </div>
        </div>
        <?php elseif ($grupoId): ?>
        <div class="card"><div class="card-body text-center text-muted py-4">No hay alumnos inscritos en este grupo</div></div>
        <?php else: ?>
        <div class="card"><div class="card-body text-center text-muted py-5"><i class="fas fa-clipboard-check fa-3x mb-3 opacity-50"></i><p>Seleccione un grupo para registrar asistencia</p></div></div>
        <?php endif; ?>
    </div>
</section>
