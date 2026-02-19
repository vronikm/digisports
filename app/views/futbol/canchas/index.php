<?php
/**
 * DigiSports Fútbol - Gestión de Canchas
 * @vars $canchas, $sedes, $sede_activa, $csrf_token, $modulo_actual
 */
$canchas     = $canchas ?? [];
$sedes       = $sedes ?? [];
$sedeActiva  = $sede_activa ?? null;
$moduloColor = $modulo_actual['color'] ?? '#22C55E';

$tiposCancha = [
    'FUTBOL_11'  => 'Fútbol 11',
    'FUTBOL_7'   => 'Fútbol 7',
    'FUTBOL_5'   => 'Fútbol 5',
    'FUTSAL'     => 'Futsal',
    'MULTIUSO'   => 'Multiuso',
];
$superficies = [
    'CESPED_NATURAL'    => 'Césped Natural',
    'CESPED_SINTETICO'  => 'Césped Sintético',
    'TIERRA'            => 'Tierra',
    'CEMENTO'           => 'Cemento',
];
$estados = [
    'DISPONIBLE'      => ['label' => 'Disponible',      'color' => '#22C55E', 'badge' => 'success'],
    'MANTENIMIENTO'   => ['label' => 'Mantenimiento',   'color' => '#F59E0B', 'badge' => 'warning'],
    'FUERA_SERVICIO'  => ['label' => 'Fuera de Servicio','color' => '#EF4444', 'badge' => 'danger'],
];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-futbol mr-2" style="color:<?= $moduloColor ?>"></i>Canchas</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Nueva Cancha</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtro Sede -->
        <?php if (!empty($sedes) && count($sedes) > 1): ?>
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-building"></i></span></div>
                    <select id="sedeFilter" class="form-control" onchange="filtrarPorSede(this.value)">
                        <option value="">Todas las sedes</option>
                        <?php foreach ($sedes as $s): ?>
                        <option value="<?= $s['sed_sede_id'] ?>" <?= $sedeActiva == $s['sed_sede_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['sed_nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($canchas)): ?>
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-futbol fa-3x mb-3 opacity-50"></i>
                <p>No hay canchas registradas</p>
                <button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Crear primera cancha</button>
            </div>
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($canchas as $c): ?>
            <?php
                $estCancha = $c['can_estado'] ?? 'DISPONIBLE';
                $estInfo   = $estados[$estCancha] ?? ['label' => $estCancha, 'color' => '#6c757d', 'badge' => 'secondary'];
                $tipoCancha = $c['can_tipo'] ?? '';
                $iconoCancha = in_array($tipoCancha, ['FUTBOL_11','FUTBOL_7','FUTBOL_5','FUTSAL']) ? 'fas fa-futbol' : 'fas fa-map-marked-alt';
            ?>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100" style="border-left: 4px solid <?= $estInfo['color'] ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">
                                    <i class="<?= $iconoCancha ?> mr-1" style="color:<?= $moduloColor ?>"></i>
                                    <?= htmlspecialchars($c['can_nombre'] ?? '') ?>
                                </h5>
                                <small class="text-muted">
                                    <?= $tiposCancha[$tipoCancha] ?? $tipoCancha ?>
                                    <?php if (!empty($c['can_superficie'])): ?>
                                     · <?= $superficies[$c['can_superficie']] ?? $c['can_superficie'] ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <span class="badge badge-<?= $estInfo['badge'] ?>"><?= $estInfo['label'] ?></span>
                        </div>

                        <div class="mt-2">
                            <?php if (!empty($c['can_capacidad_maxima'])): ?>
                            <span class="badge badge-light mr-1"><i class="fas fa-users mr-1"></i><?= (int)$c['can_capacidad_maxima'] ?> cap.</span>
                            <?php endif; ?>
                            <?php if (!empty($c['can_dimensiones'])): ?>
                            <span class="badge badge-light mr-1"><i class="fas fa-ruler-combined mr-1"></i><?= htmlspecialchars($c['can_dimensiones']) ?></span>
                            <?php endif; ?>
                            <span class="badge badge-light"><i class="fas fa-users-cog mr-1"></i><?= (int)($c['total_grupos'] ?? 0) ?> grupo(s)</span>
                        </div>

                        <div class="mt-2">
                            <?php if (!empty($c['can_iluminacion'])): ?>
                            <span class="badge badge-info mr-1"><i class="fas fa-lightbulb mr-1"></i>Iluminación</span>
                            <?php endif; ?>
                            <?php if (!empty($c['can_techada'])): ?>
                            <span class="badge badge-info"><i class="fas fa-warehouse mr-1"></i>Techada</span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($c['can_notas'])): ?>
                        <p class="mt-2 mb-0 text-muted small"><?= htmlspecialchars($c['can_notas']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white py-2">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick='editarCancha(<?= json_encode($c) ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                            <?php if ($estCancha === 'DISPONIBLE'): ?>
                            <button class="btn btn-outline-warning" onclick="cambiarEstado(<?= $c['can_cancha_id'] ?>,'MANTENIMIENTO')" title="Poner en Mantenimiento"><i class="fas fa-tools"></i></button>
                            <?php elseif ($estCancha === 'MANTENIMIENTO'): ?>
                            <button class="btn btn-outline-success" onclick="cambiarEstado(<?= $c['can_cancha_id'] ?>,'DISPONIBLE')" title="Habilitar"><i class="fas fa-check"></i></button>
                            <?php else: ?>
                            <button class="btn btn-outline-success" onclick="cambiarEstado(<?= $c['can_cancha_id'] ?>,'DISPONIBLE')" title="Habilitar"><i class="fas fa-check"></i></button>
                            <?php endif; ?>
                            <button class="btn btn-outline-danger" onclick="eliminarCancha(<?= $c['can_cancha_id'] ?>,'<?= htmlspecialchars($c['can_nombre'] ?? '') ?>')" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="modalCancha" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formCancha" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="can_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-futbol mr-2"></i>Nueva Cancha</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="can_nombre" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo <span class="text-danger">*</span></label>
                                <select name="tipo" id="can_tipo" class="form-control" required>
                                    <?php foreach ($tiposCancha as $k => $v): ?>
                                    <option value="<?= $k ?>"><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Superficie</label>
                                <select name="superficie" id="can_superficie" class="form-control">
                                    <option value="">— Seleccionar —</option>
                                    <?php foreach ($superficies as $k => $v): ?>
                                    <option value="<?= $k ?>"><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Estado</label>
                                <select name="estado" id="can_estado" class="form-control">
                                    <?php foreach ($estados as $k => $e): ?>
                                    <option value="<?= $k ?>"><?= $e['label'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Capacidad</label>
                                <input type="number" name="capacidad" id="can_capacidad" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Dimensiones</label>
                                <input type="text" name="dimensiones" id="can_dimensiones" class="form-control" placeholder="Ej: 100x64m">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group pt-4">
                                <div class="custom-control custom-checkbox d-inline mr-3">
                                    <input type="checkbox" class="custom-control-input" id="can_iluminacion" name="iluminacion" value="1">
                                    <label class="custom-control-label" for="can_iluminacion">Iluminación</label>
                                </div>
                                <div class="custom-control custom-checkbox d-inline">
                                    <input type="checkbox" class="custom-control-input" id="can_techada" name="techada" value="1">
                                    <label class="custom-control-label" for="can_techada">Techada</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notas</label>
                        <textarea name="notas" id="can_notas" class="form-control" rows="2"></textarea>
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
var urlCrear  = '<?= url('futbol', 'cancha', 'crear') ?>';
var urlEditar = '<?= url('futbol', 'cancha', 'editar') ?>';

function abrirModal() {
    document.getElementById('formCancha').reset();
    document.getElementById('can_id').value = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-futbol mr-2"></i>Nueva Cancha';
    document.getElementById('formCancha').action = urlCrear;
    $('#modalCancha').modal('show');
}

function editarCancha(obj) {
    document.getElementById('can_id').value        = obj.can_cancha_id;
    document.getElementById('can_nombre').value     = obj.can_nombre || '';
    document.getElementById('can_tipo').value       = obj.can_tipo || 'FUTBOL_11';
    document.getElementById('can_superficie').value = obj.can_superficie || '';
    document.getElementById('can_estado').value     = obj.can_estado || 'DISPONIBLE';
    document.getElementById('can_capacidad').value  = obj.can_capacidad_maxima || '';
    document.getElementById('can_dimensiones').value= obj.can_dimensiones || '';
    document.getElementById('can_iluminacion').checked = !!parseInt(obj.can_iluminacion);
    document.getElementById('can_techada').checked     = !!parseInt(obj.can_techada);
    document.getElementById('can_notas').value      = obj.can_notas || '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Cancha';
    document.getElementById('formCancha').action = urlEditar;
    $('#modalCancha').modal('show');
}

function eliminarCancha(id, nombre) {
    Swal.fire({
        title: '¿Eliminar cancha?',
        html: 'Se eliminará <strong>' + nombre + '</strong>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function(r) {
        if (r.isConfirmed) window.location.href = '<?= url('futbol', 'cancha', 'eliminar') ?>&id=' + id;
    });
}

function cambiarEstado(id, estado) {
    Swal.fire({
        title: '¿Cambiar estado?',
        text: 'La cancha pasará a estado: ' + estado,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '<?= $moduloColor ?>',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then(function(r) {
        if (r.isConfirmed) window.location.href = '<?= url('futbol', 'cancha', 'cambiarEstado') ?>&id=' + id + '&estado=' + estado;
    });
}

function filtrarPorSede(sedeId) {
    $.post('<?= url('futbol', 'sede', 'seleccionar') ?>', { sede_id: sedeId, csrf_token: '<?= $csrf_token ?? '' ?>' }, function() { location.reload(); }, 'json');
}
</script>
<?php $scripts = ob_get_clean(); ?>
