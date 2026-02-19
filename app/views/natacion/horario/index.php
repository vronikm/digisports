<?php
/**
 * DigiSports Natación - Horario Semanal
 */
$calendario  = $calendario ?? [];
$piscinas    = $piscinas ?? [];
$grupos      = $grupos ?? [];
$diasSemana  = $diasSemana ?? [];
$moduloColor = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-calendar-week mr-2" style="color:<?= $moduloColor ?>"></i>Horario Semanal</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Nuevo Horario</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtro piscina -->
        <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('natacion', 'horario', 'index') ?>" class="row align-items-end">
                    <div class="col-md-5">
                        <label class="small">Filtrar por Piscina</label>
                        <select name="piscina_id" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">— Todas —</option>
                            <?php foreach ($piscinas as $p): ?>
                            <option value="<?= $p['npi_piscina_id'] ?>" <?= ($piscina_id ?? '') == $p['npi_piscina_id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['npi_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Calendario semanal -->
        <div class="row">
            <?php foreach ($calendario as $cod => $dia): ?>
            <div class="col-lg mb-3" style="min-width:180px;">
                <div class="card h-100">
                    <div class="card-header py-2 text-center" style="background:<?= $moduloColor ?>;color:white;">
                        <strong><?= $dia['label'] ?></strong>
                    </div>
                    <div class="card-body p-1">
                        <?php if (empty($dia['items'])): ?>
                        <div class="text-center text-muted py-3"><small>Sin clases</small></div>
                        <?php else: ?>
                        <?php foreach ($dia['items'] as $h): ?>
                        <div class="border rounded p-2 mb-1" style="border-left:3px solid <?= htmlspecialchars($h['ngr_color'] ?? $moduloColor) ?> !important;font-size:.8rem;">
                            <div class="font-weight-bold"><?= substr($h['ngh_hora_inicio'], 0, 5) ?> - <?= substr($h['ngh_hora_fin'], 0, 5) ?></div>
                            <div><?= htmlspecialchars($h['grupo'] ?? '—') ?></div>
                            <div class="text-muted"><?= htmlspecialchars($h['piscina'] ?? '') ?></div>
                            <?php if (!empty($h['nivel'])): ?>
                            <span class="badge badge-sm" style="background:<?= htmlspecialchars($h['nivel_color'] ?? '#6c757d') ?>;color:white;font-size:.65rem;"><?= htmlspecialchars($h['nivel']) ?></span>
                            <?php endif; ?>
                            <div class="text-muted"><small><i class="fas fa-user mr-1"></i><?= htmlspecialchars(($h['instructor_nombre'] ?? '') . ' ' . ($h['instructor_apellido'] ?? '')) ?></small></div>
                            <button class="btn btn-outline-danger btn-xs mt-1" onclick="eliminarHorario(<?= $h['ngh_horario_id'] ?>)"><i class="fas fa-trash"></i></button>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="modalHorario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formHorario" method="POST" action="<?= url('natacion', 'horario', 'crear') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title"><i class="fas fa-clock mr-2"></i>Nuevo Horario</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Grupo <span class="text-danger">*</span></label>
                        <select name="grupo_id" class="form-control" required>
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['ngr_grupo_id'] ?>"><?= htmlspecialchars($g['ngr_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Día <span class="text-danger">*</span></label>
                        <select name="dia_semana" class="form-control" required>
                            <?php foreach ($diasSemana as $c => $l): ?>
                            <option value="<?= $c ?>"><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Hora Inicio <span class="text-danger">*</span></label><input type="time" name="hora_inicio" class="form-control" required></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Hora Fin <span class="text-danger">*</span></label><input type="time" name="hora_fin" class="form-control" required></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-save mr-1"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
function abrirModal() { document.getElementById('formHorario').reset(); $('#modalHorario').modal('show'); }
function eliminarHorario(id) {
    Swal.fire({ title: '¿Eliminar horario?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí', cancelButtonText: 'No'
    }).then(function(r) { if (r.isConfirmed) window.location.href = '<?= url('natacion', 'horario', 'eliminar') ?>&id=' + id; });
}
</script>
<?php $scripts = ob_get_clean(); ?>
