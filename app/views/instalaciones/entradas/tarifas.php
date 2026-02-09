<?php
/**
 * DigiSports Arena — Vista: Gestión de Tarifas de Entrada
 * CRUD de tarifas por instalación con modal de edición
 */
$instalaciones = $instalaciones ?? [];
$tarifas       = $tarifas ?? [];
$csrf          = $csrf_token ?? '';

$tipoColors = ['GENERAL'=>'primary','VIP'=>'warning','CORTESIA'=>'info','ABONADO'=>'success'];
$dias = [1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado',7=>'Domingo'];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-tags mr-2 text-warning"></i> Tarifas de Entrada</h1>
            </div>
            <div class="col-sm-6 text-right">
                <button class="btn btn-success mr-1" data-toggle="modal" data-target="#modalTarifa" onclick="nuevaTarifa()">
                    <i class="fas fa-plus-circle mr-1"></i> Nueva Tarifa
                </button>
                <a href="<?= url('instalaciones', 'entrada', 'index') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <?php if (empty($tarifas)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                    <p class="text-muted h5">No hay tarifas configuradas</p>
                    <p class="text-muted">Cree tarifas para agilizar la venta de entradas</p>
                    <button class="btn btn-success" data-toggle="modal" data-target="#modalTarifa" onclick="nuevaTarifa()">
                        <i class="fas fa-plus-circle mr-1"></i> Crear Primera Tarifa
                    </button>
                </div>
            </div>
        <?php else: ?>

            <?php
            // Agrupar por instalación
            $tarifasPorInst = [];
            foreach ($tarifas as $t) {
                $tarifasPorInst[$t['instalacion_nombre']][] = $t;
            }
            ?>

            <?php foreach ($tarifasPorInst as $instNombre => $tarInst): ?>
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building mr-1"></i> <?= htmlspecialchars($instNombre) ?>
                        <span class="badge badge-primary ml-2"><?= count($tarInst) ?> tarifas</span>
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Nombre</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-right">Precio</th>
                                <th class="text-center">Horario</th>
                                <th class="text-center">Día</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" style="width:100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tarInst as $t): ?>
                            <?php $tc = $tipoColors[$t['ent_tar_tipo']] ?? 'secondary'; ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['ent_tar_nombre']) ?></strong></td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $tc ?>"><?= $t['ent_tar_tipo'] ?></span>
                                </td>
                                <td class="text-right">
                                    <strong class="text-success">$<?= number_format($t['ent_tar_precio'], 2) ?></strong>
                                </td>
                                <td class="text-center">
                                    <?php if ($t['ent_tar_hora_inicio'] && $t['ent_tar_hora_fin']): ?>
                                        <?= date('H:i', strtotime($t['ent_tar_hora_inicio'])) ?> — <?= date('H:i', strtotime($t['ent_tar_hora_fin'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Todo el día</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= $t['ent_tar_dia_semana'] ? ($dias[(int)$t['ent_tar_dia_semana']] ?? $t['ent_tar_dia_semana']) : '<span class="text-muted">Todos</span>' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $t['ent_tar_estado'] === 'ACTIVO' ? 'success' : 'danger' ?>">
                                        <?= $t['ent_tar_estado'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-primary btn-editar"
                                            data-tarifa='<?= json_encode($t) ?>'
                                            data-toggle="modal" data-target="#modalTarifa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Modal Tarifa -->
<div class="modal fade" id="modalTarifa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="modalTarifaTitulo">
                    <i class="fas fa-tag mr-1"></i> Nueva Tarifa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formTarifa">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="tarifa_id" id="inputTarifaId" value="0">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Instalación *</label>
                        <select name="instalacion_id" id="selTarifaInst" class="form-control" required>
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($instalaciones as $inst): ?>
                                <option value="<?= $inst['ins_instalacion_id'] ?>">
                                    <?= htmlspecialchars($inst['ins_nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Nombre *</label>
                                <input type="text" name="nombre" id="inputTarifaNombre" class="form-control"
                                       placeholder="Ej: Entrada General Mañana" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select name="tipo" id="selTarifaTipo" class="form-control">
                                    <option value="GENERAL">General</option>
                                    <option value="VIP">VIP</option>
                                    <option value="CORTESIA">Cortesía</option>
                                    <option value="ABONADO">Abonado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Precio ($)</label>
                                <input type="number" name="precio" id="inputTarifaPrecio" class="form-control"
                                       min="0" step="0.01" value="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Hora Inicio</label>
                                <input type="time" name="hora_inicio" id="inputTarifaHI" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Hora Fin</label>
                                <input type="time" name="hora_fin" id="inputTarifaHF" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Día de Semana</label>
                                <select name="dia_semana" id="selTarifaDia" class="form-control">
                                    <option value="">Todos los días</option>
                                    <?php foreach ($dias as $n => $d): ?>
                                        <option value="<?= $n ?>"><?= $d ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Estado</label>
                                <select name="estado" id="selTarifaEstado" class="form-control">
                                    <option value="ACTIVO">Activo</option>
                                    <option value="INACTIVO">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function nuevaTarifa() {
    document.getElementById('modalTarifaTitulo').innerHTML = '<i class="fas fa-tag mr-1"></i> Nueva Tarifa';
    document.getElementById('inputTarifaId').value = 0;
    document.getElementById('formTarifa').reset();
}

document.querySelectorAll('.btn-editar').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var t = JSON.parse(this.dataset.tarifa);
        document.getElementById('modalTarifaTitulo').innerHTML = '<i class="fas fa-edit mr-1"></i> Editar Tarifa';
        document.getElementById('inputTarifaId').value = t.ent_tar_id;
        document.getElementById('selTarifaInst').value = t.ent_tar_instalacion_id;
        document.getElementById('inputTarifaNombre').value = t.ent_tar_nombre;
        document.getElementById('selTarifaTipo').value = t.ent_tar_tipo;
        document.getElementById('inputTarifaPrecio').value = t.ent_tar_precio;
        document.getElementById('inputTarifaHI').value = t.ent_tar_hora_inicio || '';
        document.getElementById('inputTarifaHF').value = t.ent_tar_hora_fin || '';
        document.getElementById('selTarifaDia').value = t.ent_tar_dia_semana || '';
        document.getElementById('selTarifaEstado').value = t.ent_tar_estado;
    });
});

document.getElementById('formTarifa').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = new FormData(this);

    fetch('<?= url('instalaciones', 'entrada', 'guardarTarifa') ?>', {
        method: 'POST',
        body: form
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            Swal.fire('Guardado', data.message, 'success').then(function() { location.reload(); });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(function() {
        Swal.fire('Error', 'Error de comunicación', 'error');
    });
});
</script>
