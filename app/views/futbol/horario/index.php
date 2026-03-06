<?php
/**
 * DigiSports Fútbol - Horario Semanal
 * @vars $horarios, $grupos, $dias_semana, $canchas, $entrenadores, $sedes, $sede_activa, $csrf_token, $modulo_actual
 */
$horarios     = $horarios ?? [];
$grupos       = $grupos ?? [];
$canchas      = $canchas ?? [];
$entrenadores = $entrenadores ?? [];
$sedes        = $sedes ?? [];
$sedeActiva   = $sede_activa ?? null;
$moduloColor  = $modulo_actual['color'] ?? '#22C55E';

$diasSemana = $dias_semana ?? [
    'LUN' => 'Lunes', 'MAR' => 'Martes', 'MIE' => 'Miércoles',
    'JUE' => 'Jueves', 'VIE' => 'Viernes', 'SAB' => 'Sábado', 'DOM' => 'Domingo'
];

// Organizar horarios por día
$calendario = [];
foreach ($diasSemana as $cod => $label) {
    $calendario[$cod] = ['label' => $label, 'items' => []];
}
foreach ($horarios as $h) {
    $dia = $h['fgh_dia_semana'] ?? '';
    if (isset($calendario[$dia])) {
        $calendario[$dia]['items'][] = $h;
    }
}
foreach ($calendario as &$dia) {
    usort($dia['items'], function($a, $b) {
        return strcmp($a['fgh_hora_inicio'] ?? '', $b['fgh_hora_inicio'] ?? '');
    });
}
unset($dia);
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-calendar-week mr-2" style="color:<?= $moduloColor ?>"></i>Horario Semanal</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <button id="btnNuevoHorario" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-plus mr-1"></i>Nuevo Horario
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
            <div class="card-body py-2">
                <form id="formFiltros" method="GET" action="<?= url('futbol', 'horario', 'index') ?>" class="row align-items-end">
                    <?php if (!empty($sedes) && count($sedes) > 1): ?>
                    <div class="col-md-3">
                        <label class="small">Sede</label>
                        <select id="filtroSede" name="sede_id" class="form-control form-control-sm">
                            <option value="">— Todas —</option>
                            <?php foreach ($sedes as $s): ?>
                            <option value="<?= $s['sed_sede_id'] ?>" <?= $sedeActiva == $s['sed_sede_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['sed_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-3">
                        <label class="small">Cancha</label>
                        <select id="filtroCancha" name="cancha_id" class="form-control form-control-sm">
                            <option value="">— Todas —</option>
                            <?php foreach ($canchas as $ca): ?>
                            <option value="<?= $ca['can_cancha_id'] ?>" <?= ($cancha_id ?? '') == $ca['can_cancha_id'] ? 'selected' : '' ?>><?= htmlspecialchars($ca['can_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small">Entrenador</label>
                        <select id="filtroEntrenador" name="entrenador_id" class="form-control form-control-sm">
                            <option value="">— Todos —</option>
                            <?php foreach ($entrenadores as $e): ?>
                            <option value="<?= $e['fen_entrenador_id'] ?>" <?= ($entrenador_id ?? '') == $e['fen_entrenador_id'] ? 'selected' : '' ?>><?= htmlspecialchars(($e['fen_nombres'] ?? '') . ' ' . ($e['fen_apellidos'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 text-right">
                        <a href="<?= url('futbol', 'horario', 'index') ?>" class="btn btn-sm btn-outline-secondary">Limpiar filtros</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Calendario semanal visual -->
        <div class="row">
            <?php foreach ($calendario as $cod => $dia): ?>
            <div class="col-lg mb-3" style="min-width:160px;">
                <div class="card h-100">
                    <div class="card-header py-2 text-center" style="background:<?= $moduloColor ?>;color:white;">
                        <strong><?= $dia['label'] ?></strong>
                    </div>
                    <div class="card-body p-1" style="min-height:200px;">
                        <?php if (empty($dia['items'])): ?>
                        <div class="text-center text-muted py-3"><small>Sin clases</small></div>
                        <?php else: ?>
                        <?php foreach ($dia['items'] as $h): ?>
                        <div class="border rounded p-2 mb-1" style="border-left:3px solid <?= htmlspecialchars($h['categoria_color'] ?? $moduloColor) ?> !important;font-size:.8rem;">
                            <div class="font-weight-bold"><?= substr($h['fgh_hora_inicio'] ?? '', 0, 5) ?> - <?= substr($h['fgh_hora_fin'] ?? '', 0, 5) ?></div>
                            <div><?= htmlspecialchars($h['grupo_nombre'] ?? '—') ?></div>
                            <div class="text-muted"><i class="fas fa-futbol mr-1"></i><?= htmlspecialchars($h['cancha_nombre'] ?? '') ?></div>
                            <div class="text-muted"><small><i class="fas fa-user mr-1"></i><?= htmlspecialchars(($h['entrenador_nombre'] ?? '') . ' ' . ($h['entrenador_apellido'] ?? '')) ?></small></div>
                            <div class="mt-1">
                                <button class="btn btn-outline-primary btn-xs js-editar-horario"
                                        data-horario="<?= htmlspecialchars(json_encode($h, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-xs js-eliminar-horario"
                                        data-id="<?= (int)$h['fgh_horario_id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Tabla detallada -->
        <div class="card shadow-sm mt-3">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Detalle de Horarios</h3>
                <span class="badge badge-secondary float-right"><?= count($horarios) ?> horario(s)</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($horarios)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-week fa-3x mb-3 opacity-50"></i>
                    <p>No hay horarios registrados</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaHorarios">
                        <thead class="thead-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Grupo</th>
                                <th>Día</th>
                                <th>Hora Inicio</th>
                                <th>Hora Fin</th>
                                <th>Cancha</th>
                                <th>Entrenador</th>
                                <th>Notas</th>
                                <th width="100" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($horarios as $i => $h): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <?php if (!empty($h['categoria_color'])): ?><span class="badge mr-1" style="background:<?= htmlspecialchars($h['categoria_color']) ?>">&nbsp;</span><?php endif; ?>
                                    <strong><?= htmlspecialchars($h['grupo_nombre'] ?? '—') ?></strong>
                                </td>
                                <td><?= $diasSemana[$h['fgh_dia_semana']] ?? $h['fgh_dia_semana'] ?></td>
                                <td><?= substr($h['fgh_hora_inicio'] ?? '', 0, 5) ?></td>
                                <td><?= substr($h['fgh_hora_fin'] ?? '', 0, 5) ?></td>
                                <td><?= htmlspecialchars($h['cancha_nombre'] ?? '—') ?></td>
                                <td><?= htmlspecialchars(($h['entrenador_nombre'] ?? '') . ' ' . ($h['entrenador_apellido'] ?? '')) ?: '—' ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($h['fgh_notas'] ?? '') ?></small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary js-editar-horario"
                                                data-horario="<?= htmlspecialchars(json_encode($h, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>"
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger js-eliminar-horario"
                                                data-id="<?= (int)$h['fgh_horario_id'] ?>"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
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

<!-- Modal -->
<div class="modal fade" id="modalHorario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formHorario" method="POST"
                  data-url-crear="<?= url('futbol', 'horario', 'crear') ?>"
                  data-url-editar="<?= url('futbol', 'horario', 'editar') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="hor_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-clock mr-2"></i>Nuevo Horario</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Grupo <span class="text-danger">*</span></label>
                        <select name="grupo_id" id="hor_grupo" class="form-control" required>
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['fgr_grupo_id'] ?>"><?= htmlspecialchars($g['fgr_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Día de la Semana <span class="text-danger">*</span></label>
                        <select name="dia_semana" id="hor_dia" class="form-control" required>
                            <?php foreach ($diasSemana as $c => $l): ?>
                            <option value="<?= $c ?>"><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hora Inicio <span class="text-danger">*</span></label>
                                <input type="time" name="hora_inicio" id="hor_inicio" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hora Fin <span class="text-danger">*</span></label>
                                <input type="time" name="hora_fin" id="hor_fin" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Cancha</label>
                        <select name="cancha_id" id="hor_cancha" class="form-control">
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($canchas as $ca): ?>
                            <option value="<?= $ca['can_cancha_id'] ?>"><?= htmlspecialchars($ca['can_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notas</label>
                        <textarea name="notas" id="hor_notas" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btnVerificar" class="btn btn-outline-info">
                        <i class="fas fa-search mr-1"></i>Verificar Disponibilidad
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-save mr-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
$(function() {
    var csrf = '<?= htmlspecialchars($csrf_token ?? '') ?>';

    var Toast = Swal.mixin({
        toast: true, position: 'top-end',
        showConfirmButton: false, timer: 3000, timerProgressBar: true
    });

    /* ── DataTable ── */
    if ($('#tablaHorarios tbody tr').length) {
        $('#tablaHorarios').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25, order: [[2,'asc'],[3,'asc']], responsive: true
        });
    }

    /* ── Filtros: auto-submit al cambiar selects ── */
    $('#filtroSede, #filtroCancha, #filtroEntrenador').on('change', function() {
        $('#formFiltros').submit();
    });

    /* ── Abrir modal NUEVO horario ── */
    $('#btnNuevoHorario').on('click', function() {
        $('#formHorario')[0].reset();
        $('#hor_id').val('');
        $('#modalTitulo').html('<i class="fas fa-clock mr-2"></i>Nuevo Horario');
        $('#formHorario').data('mode', 'crear');
        $('#modalHorario').modal('show');
    });

    /* ── Abrir modal EDITAR horario ── */
    $(document).on('click', '.js-editar-horario', function() {
        var obj = JSON.parse($(this).attr('data-horario'));
        $('#formHorario')[0].reset();
        $('#hor_id').val(obj.fgh_horario_id || '');
        $('#hor_grupo').val(obj.fgh_grupo_id || '');
        $('#hor_dia').val(obj.fgh_dia_semana || 'LUN');
        $('#hor_inicio').val((obj.fgh_hora_inicio || '').substring(0, 5));
        $('#hor_fin').val((obj.fgh_hora_fin || '').substring(0, 5));
        $('#hor_cancha').val(obj.can_cancha_id || '');
        $('#hor_notas').val(obj.fgh_notas || '');
        $('#modalTitulo').html('<i class="fas fa-edit mr-2"></i>Editar Horario');
        $('#formHorario').data('mode', 'editar');
        $('#modalHorario').modal('show');
    });

    /* ── SUBMIT: guardar horario (AJAX) ── */
    $('#formHorario').on('submit', function(e) {
        e.preventDefault();
        var mode   = $(this).data('mode') || 'crear';
        var action = $(this).attr(mode === 'editar' ? 'data-url-editar' : 'data-url-crear');
        var $btn   = $(this).find('[type=submit]').prop('disabled', true);

        $.post(action, $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalHorario').modal('hide');
                Toast.fire({ icon: 'success', title: res.message });
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('Error', 'Error de conexión al guardar.', 'error');
        }).always(function() { $btn.prop('disabled', false); });
    });

    /* ── Eliminar horario (AJAX POST) ── */
    $(document).on('click', '.js-eliminar-horario', function() {
        var id   = $(this).data('id');
        var $row = $(this).closest('tr');
        var url  = '<?= url('futbol', 'horario', 'eliminar') ?>';

        Swal.fire({
            title: '¿Eliminar horario?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $.post(url, { csrf_token: csrf, id: id }, function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    // Quitar fila de la tabla si existe; reload para actualizar el calendario
                    if ($row.length) {
                        $row.fadeOut(300, function() { $(this).remove(); });
                    }
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error', 'Error de conexión al eliminar.', 'error');
            });
        });
    });

    /* ── Verificar disponibilidad ── */
    $('#btnVerificar').on('click', function() {
        var dia    = $('#hor_dia').val();
        var inicio = $('#hor_inicio').val();
        var fin    = $('#hor_fin').val();

        if (!dia || !inicio || !fin) {
            Swal.fire('Datos incompletos', 'Seleccione día, hora inicio y hora fin.', 'info');
            return;
        }
        Swal.fire('Info', 'La verificación de disponibilidad se realizará automáticamente al guardar.', 'info');
    });

});
</script>
<?php $scripts = ob_get_clean(); ?>
