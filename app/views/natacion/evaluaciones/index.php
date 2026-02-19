<?php
/**
 * DigiSports Natación - Evaluaciones
 */
$niveles     = $niveles ?? [];
$habilidades = $habilidades ?? [];
$alumnos     = $alumnos ?? [];
$evaluaciones = $evaluaciones ?? [];
$nivelId     = $nivel_id ?? 0;
$moduloColor = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-star mr-2" style="color:<?= $moduloColor ?>"></i>Evaluaciones</h1></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Selector nivel -->
        <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('natacion', 'evaluacion', 'index') ?>" class="row align-items-end" id="formFiltroEval">
                    <div class="col-md-5">
                        <label class="small">Nivel</label>
                        <select name="nivel_id" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">— Seleccionar nivel —</option>
                            <?php foreach ($niveles as $n): ?>
                            <option value="<?= $n['nnv_nivel_id'] ?>" <?= $nivelId == $n['nnv_nivel_id'] ? 'selected' : '' ?>><?= htmlspecialchars($n['nnv_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($nivelId && !empty($habilidades) && !empty($alumnos)): ?>
        <div class="card shadow-sm">
            <div class="card-header" style="background:<?= $moduloColor ?>20;">
                <h3 class="card-title"><i class="fas fa-tasks mr-2"></i>Evaluar Habilidades</h3>
            </div>
            <div class="card-body p-0">
                <form method="POST" action="<?= url('natacion', 'evaluacion', 'guardar') ?>" id="formEvaluacion">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="nivel_id" value="<?= $nivelId ?>">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="sticky-col">Alumno</th>
                                    <?php foreach ($habilidades as $h): ?>
                                    <th class="text-center" style="min-width:120px;">
                                        <small><?= htmlspecialchars($h['nha_nombre']) ?></small>
                                    </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alumnos as $al): ?>
                                <tr>
                                    <td class="sticky-col"><strong><?= htmlspecialchars($al['alu_nombres'] . ' ' . $al['alu_apellidos']) ?></strong></td>
                                    <?php foreach ($habilidades as $h):
                                        $eval = null;
                                        foreach ($evaluaciones as $e) {
                                            if ((int)$e['nev_alumno_id'] === (int)$al['alu_alumno_id'] && (int)$e['nev_habilidad_id'] === (int)$h['nha_habilidad_id']) {
                                                $eval = $e; break;
                                            }
                                        }
                                        $cal = $eval ? $eval['nev_calificacion'] : '';
                                        $apr = $eval ? (int)$eval['nev_aprobado'] : 0;
                                    ?>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <input type="number" name="eval[<?= $al['alu_alumno_id'] ?>][<?= $h['nha_habilidad_id'] ?>][cal]"
                                                class="form-control form-control-sm text-center mr-1" style="width:60px;"
                                                min="0" max="10" step="0.5" value="<?= $cal ?>" placeholder="—">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="apr_<?= $al['alu_alumno_id'] ?>_<?= $h['nha_habilidad_id'] ?>"
                                                    name="eval[<?= $al['alu_alumno_id'] ?>][<?= $h['nha_habilidad_id'] ?>][apr]" value="1" <?= $apr ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="apr_<?= $al['alu_alumno_id'] ?>_<?= $h['nha_habilidad_id'] ?>"><small>✓</small></label>
                                            </div>
                                        </div>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-save mr-1"></i>Guardar Evaluaciones</button>
                    </div>
                </form>
            </div>
        </div>
        <?php elseif ($nivelId): ?>
        <div class="card"><div class="card-body text-center text-muted py-4">No hay alumnos o habilidades para este nivel</div></div>
        <?php else: ?>
        <div class="card"><div class="card-body text-center text-muted py-5"><i class="fas fa-star fa-3x mb-3 opacity-50"></i><p>Seleccione un nivel para evaluar</p></div></div>
        <?php endif; ?>
    </div>
</section>

<?php ob_start(); ?>
<style>
.sticky-col { position: sticky; left: 0; background: white; z-index: 1; min-width: 200px; }
thead .sticky-col { z-index: 2; }
</style>
<?php $scripts = ob_get_clean(); ?>
