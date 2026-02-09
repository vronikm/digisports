<?php
/**
 * Editar reserva existente
 * Solo disponible para reservas en estado PENDIENTE o CONFIRMADA
 */
$estadoClases = [
    'PENDIENTE' => 'warning',
    'CONFIRMADA' => 'success',
];
$badgeClass = $estadoClases[$reserva['estado']] ?? 'secondary';
$urlEditar = url('reservas', 'reserva', 'editar') . '&id=' . $reserva['reserva_id'];
$urlVer = url('reservas', 'reserva', 'ver') . '&id=' . $reserva['reserva_id'];
$urlListado = url('reservas', 'reserva');
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="fas fa-edit text-primary"></i> Editar Reserva #<?= $reserva['reserva_id'] ?>
                </h1>
                <div>
                    <a href="<?= $urlVer ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Detalles
                    </a>
                </div>
            </div>

            <!-- Info resumen -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-<?= $badgeClass ?>">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Estado</span>
                            <span class="info-box-number">
                                <span class="badge badge-<?= $badgeClass ?> badge-lg">
                                    <?= htmlspecialchars($reserva['estado']) ?>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-building"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Instalación</span>
                            <span class="info-box-number" style="font-size: 0.9em;">
                                <?= htmlspecialchars($reserva['instalacion_nombre']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary">
                            <i class="fas fa-user"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cliente</span>
                            <span class="info-box-number" style="font-size: 0.9em;">
                                <?= htmlspecialchars($reserva['cliente_nombre']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Formulario de edición -->
                <div class="col-lg-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-pen"></i> Modificar Datos
                            </h3>
                        </div>
                        <form id="formEditarReserva">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <div class="card-body">

                                <div class="form-group">
                                    <label for="fecha_reserva"><i class="fas fa-calendar"></i> Fecha de Reserva</label>
                                    <input type="date" class="form-control form-control-lg" id="fecha_reserva"
                                           name="fecha_reserva"
                                           value="<?= htmlspecialchars($reserva['fecha_reserva']) ?>"
                                           min="<?= date('Y-m-d') ?>" required>
                                    <small class="text-muted">Al cambiar la fecha, las franjas disponibles se actualizarán</small>
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-clock"></i> Franja Horaria</label>
                                    <div id="franjasContainer">
                                        <?php if (!empty($franjas)): ?>
                                            <?php foreach ($franjas as $franja): ?>
                                                <?php if ($franja['disponible']): ?>
                                                    <div class="custom-control custom-radio mb-2">
                                                        <input type="radio" class="custom-control-input franja-radio"
                                                               id="franja_<?= $franja['tarifa_id'] ?>"
                                                               name="franja_seleccionada"
                                                               value="<?= $franja['tarifa_id'] ?>"
                                                               data-inicio="<?= $franja['hora_inicio'] ?>"
                                                               data-fin="<?= $franja['hora_fin'] ?>"
                                                               data-precio="<?= $franja['precio'] ?>"
                                                               <?= $franja['seleccionada'] ? 'checked' : '' ?>>
                                                        <label class="custom-control-label" for="franja_<?= $franja['tarifa_id'] ?>">
                                                            <strong><?= substr($franja['hora_inicio'], 0, 5) ?> - <?= substr($franja['hora_fin'], 0, 5) ?></strong>
                                                            <span class="badge badge-success ml-2">$<?= number_format($franja['precio'], 2) ?></span>
                                                            <?php if ($franja['seleccionada']): ?>
                                                                <span class="badge badge-primary ml-1">Actual</span>
                                                            <?php endif; ?>
                                                        </label>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="custom-control custom-radio mb-2">
                                                        <input type="radio" class="custom-control-input" disabled
                                                               id="franja_<?= $franja['tarifa_id'] ?>_disabled">
                                                        <label class="custom-control-label text-muted" for="franja_<?= $franja['tarifa_id'] ?>_disabled">
                                                            <s><?= substr($franja['hora_inicio'], 0, 5) ?> - <?= substr($franja['hora_fin'], 0, 5) ?></s>
                                                            <span class="badge badge-danger ml-2"><?= htmlspecialchars($franja['razon']) ?></span>
                                                        </label>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i> No hay tarifas configuradas para este día.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Campos ocultos que se envían -->
                                    <input type="hidden" name="hora_inicio" id="hora_inicio" value="<?= htmlspecialchars($reserva['hora_inicio']) ?>">
                                    <input type="hidden" name="hora_fin" id="hora_fin" value="<?= htmlspecialchars($reserva['hora_fin']) ?>">
                                </div>

                                <div class="form-group">
                                    <label for="observaciones"><i class="fas fa-sticky-note"></i> Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                                              placeholder="Notas adicionales..."><?= htmlspecialchars($reserva['observaciones'] ?? '') ?></textarea>
                                </div>

                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-lg btn-block" id="btnGuardar">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Panel de información actual -->
                <div class="col-lg-6">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i> Datos Actuales
                            </h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%">Reserva #:</td>
                                    <td><strong><?= $reserva['reserva_id'] ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Fecha Actual:</td>
                                    <td><strong><?= date('d/m/Y', strtotime($reserva['fecha_reserva'])) ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Horario Actual:</td>
                                    <td>
                                        <strong class="text-primary">
                                            <?= date('H:i', strtotime($reserva['hora_inicio'])) ?>
                                            -
                                            <?= date('H:i', strtotime($reserva['hora_fin'])) ?>
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Duración:</td>
                                    <td><?= $reserva['duracion_minutos'] ?> minutos</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Precio Actual:</td>
                                    <td><strong class="text-success">$<?= number_format($reserva['precio_total'], 2) ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Cliente:</td>
                                    <td>
                                        <?= htmlspecialchars($reserva['cliente_nombre']) ?>
                                        <?php if (!empty($reserva['cliente_email'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($reserva['cliente_email']) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($reserva['cliente_telefono'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($reserva['cliente_telefono']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if (!empty($reserva['observaciones'])): ?>
                                <tr>
                                    <td class="text-muted">Observaciones:</td>
                                    <td><?= nl2br(htmlspecialchars($reserva['observaciones'])) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted">Registrada:</td>
                                    <td><?= date('d/m/Y H:i', strtotime($reserva['fecha_registro'])) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Resumen de cambios -->
                    <div class="card card-warning card-outline" id="cardCambios" style="display:none;">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exchange-alt"></i> Cambios Detectados
                            </h3>
                        </div>
                        <div class="card-body">
                            <ul id="listaCambios" class="list-unstyled mb-0"></ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function() {
    var reservaOriginal = {
        fecha: '<?= $reserva['fecha_reserva'] ?>',
        horaInicio: '<?= $reserva['hora_inicio'] ?>',
        horaFin: '<?= $reserva['hora_fin'] ?>',
        precio: <?= (float)$reserva['precio_total'] ?>,
        observaciones: <?= json_encode($reserva['observaciones'] ?? '') ?>
    };

    // Actualizar campos ocultos al cambiar franja
    document.querySelectorAll('.franja-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.getElementById('hora_inicio').value = this.dataset.inicio;
            document.getElementById('hora_fin').value = this.dataset.fin;
            detectarCambios();
        });
    });

    // Detectar cambios en fecha y observaciones
    document.getElementById('fecha_reserva').addEventListener('change', detectarCambios);
    document.getElementById('observaciones').addEventListener('input', detectarCambios);

    function detectarCambios() {
        var cambios = [];
        var fecha = document.getElementById('fecha_reserva').value;
        var horaInicio = document.getElementById('hora_inicio').value;
        var horaFin = document.getElementById('hora_fin').value;
        var observaciones = document.getElementById('observaciones').value;
        var franjaChecked = document.querySelector('.franja-radio:checked');
        var precioNuevo = franjaChecked ? parseFloat(franjaChecked.dataset.precio) : reservaOriginal.precio;

        if (fecha !== reservaOriginal.fecha) {
            cambios.push('<li><i class="fas fa-calendar text-warning mr-1"></i> Fecha: <s>' + reservaOriginal.fecha + '</s> → <strong>' + fecha + '</strong></li>');
        }
        if (horaInicio !== reservaOriginal.horaInicio || horaFin !== reservaOriginal.horaFin) {
            cambios.push('<li><i class="fas fa-clock text-warning mr-1"></i> Horario: <s>' + reservaOriginal.horaInicio.substr(0,5) + '-' + reservaOriginal.horaFin.substr(0,5) + '</s> → <strong>' + horaInicio.substr(0,5) + '-' + horaFin.substr(0,5) + '</strong></li>');
        }
        if (precioNuevo !== reservaOriginal.precio) {
            cambios.push('<li><i class="fas fa-dollar-sign text-warning mr-1"></i> Precio: <s>$' + reservaOriginal.precio.toFixed(2) + '</s> → <strong>$' + precioNuevo.toFixed(2) + '</strong></li>');
        }
        if (observaciones.trim() !== reservaOriginal.observaciones.trim()) {
            cambios.push('<li><i class="fas fa-sticky-note text-warning mr-1"></i> Observaciones modificadas</li>');
        }

        var card = document.getElementById('cardCambios');
        var lista = document.getElementById('listaCambios');
        if (cambios.length > 0) {
            lista.innerHTML = cambios.join('');
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    }

    // Enviar formulario por AJAX
    document.getElementById('formEditarReserva').addEventListener('submit', function(e) {
        e.preventDefault();

        var franjaChecked = document.querySelector('.franja-radio:checked');
        if (!franjaChecked) {
            DigiAlert.error('Selecciona una franja horaria');
            return;
        }

        var btn = document.getElementById('btnGuardar');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        var formData = new FormData(this);
        formData.set('hora_inicio', document.getElementById('hora_inicio').value);
        formData.set('hora_fin', document.getElementById('hora_fin').value);

        fetch('<?= $urlEditar ?>', {
            method: 'POST',
            body: formData
        })
        .then(function(resp) { return resp.json(); })
        .then(function(data) {
            if (data.success) {
                DigiAlert.success(data.message || 'Reserva actualizada');
                setTimeout(function() {
                    window.location.href = data.data && data.data.redirect ? data.data.redirect : '<?= $urlVer ?>';
                }, 1500);
            } else {
                DigiAlert.error(data.message || 'Error al actualizar');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
            }
        })
        .catch(function(err) {
            DigiAlert.error('Error de conexión');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
        });
    });
})();
</script>
