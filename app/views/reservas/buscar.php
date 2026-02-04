<?php
/**
 * Búsqueda de disponibilidad de canchas
 */
$urlBuscar = url('reservas', 'reserva', 'buscar');
$urlListado = url('reservas', 'reserva');
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="fas fa-calendar-check text-primary"></i> Buscar Disponibilidad
                </h1>
                <a href="<?= $urlListado ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Filtros de búsqueda -->
        <div class="col-lg-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Criterios de búsqueda</h3>
                </div>
                <form method="post" action="<?= $urlBuscar ?>" id="formBusqueda">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <div class="card-body">
                        
                        <div class="form-group">
                            <label for="instalacion_id">Instalación</label>
                            <select class="form-control form-control-lg" id="instalacion_id" name="instalacion_id" required>
                                <option value="">-- Seleccionar instalación --</option>
                                <?php foreach ($instalaciones as $inst): ?>
                                    <option value="<?= $inst['instalacion_id'] ?>" 
                                        <?= $inst['instalacion_id'] == $instalacion_id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($inst['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" class="form-control form-control-lg" id="fecha" name="fecha" 
                                   value="<?= htmlspecialchars($fecha) ?>" required min="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="form-group">
                            <label for="tipo">Tipo de Cancha (opcional)</label>
                            <select class="form-control form-control-lg" id="tipo" name="tipo">
                                <option value="">-- Todos los tipos --</option>
                                <?php foreach ($tipos as $t): ?>
                                    <option value="<?= htmlspecialchars($t) ?>" 
                                        <?= $t == $tipo_cancha ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resultados de disponibilidad -->
        <div class="col-lg-8">
            <?php if (!empty($disponibilidades)): ?>
                
                <div class="row">
                    <?php foreach ($disponibilidades as $item): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card card-outline card-info">
                                <div class="card-header bg-info">
                                    <h3 class="card-title">
                                        <i class="fas fa-futbol"></i> 
                                        <?= htmlspecialchars($item['cancha']['nombre']) ?>
                                    </h3>
                                    <div class="card-tools">
                                        <span class="badge badge-success">
                                            <?= htmlspecialchars($item['cancha']['tipo']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-2">
                                    <small class="text-muted">
                                        Capacidad: <?= $item['cancha']['capacidad_maxima'] ?> personas | 
                                        Dimensiones: <?= $item['cancha']['ancho'] ?> x <?= $item['cancha']['largo'] ?>m
                                    </small>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Hora</th>
                                                <th>Precio</th>
                                                <th>Estado</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($item['franjas'] as $franja): ?>
                                                <tr class="<?= $franja['disponible'] === 'N' ? 'table-danger' : 'table-success' ?>">
                                                    <td>
                                                        <strong><?= $franja['hora_inicio'] ?> - <?= $franja['hora_fin'] ?></strong>
                                                    </td>
                                                    <td>
                                                        $<?= number_format($franja['precio'], 2) ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($franja['disponible'] === 'S'): ?>
                                                            <span class="badge badge-success">Disponible</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">
                                                                <?= htmlspecialchars($franja['razon']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($franja['disponible'] === 'S'): ?>
                                                            <button type="button" class="btn btn-xs btn-primary"
                                                                    data-cancha="<?= $item['cancha']['cancha_id'] ?>"
                                                                    data-tarifa="<?= $franja['tarifa_id'] ?>"
                                                                    data-fecha="<?= htmlspecialchars($fecha) ?>"
                                                                    data-cancha-nombre="<?= htmlspecialchars($item['cancha']['nombre']) ?>"
                                                                    data-hora-inicio="<?= $franja['hora_inicio'] ?>"
                                                                    data-hora-fin="<?= $franja['hora_fin'] ?>"
                                                                    data-precio="<?= $franja['precio'] ?>"
                                                                    onclick="abrirFormularioReserva(this)">
                                                                <i class="fas fa-plus"></i> Reservar
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($instalacion_id > 0 && !empty($fecha)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No hay canchas disponibles para los criterios seleccionados.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Formulario de Reserva -->
<div class="modal fade" id="modalReserva" tabindex="-1" role="dialog" aria-labelledby="modalReservaTitle">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalReservaTitle">Nueva Reserva</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="formReserva" action="<?= url('reservas', 'reserva', 'crear') ?>">
                <div class="modal-body">
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Cancha</label>
                        <div class="alert alert-light" id="reservaCancha"></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Fecha</label>
                            <div class="alert alert-light" id="reservaFecha"></div>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Hora</label>
                            <div class="alert alert-light" id="reservaHora"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Precio: <span id="reservaPrecio" class="badge badge-success">$0.00</span></label>
                    </div>

                    <hr>

                    <h6 class="font-weight-bold mb-3">Datos del Cliente</h6>

                    <div class="form-group">
                        <label for="nombre_cliente">Nombre Completo *</label>
                        <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" 
                               required minlength="3" maxlength="100">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="email_cliente">Email *</label>
                            <input type="email" class="form-control" id="email_cliente" name="email_cliente" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="telefono_cliente">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono_cliente" name="telefono_cliente">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cantidad_personas">Cantidad de Personas *</label>
                        <input type="number" class="form-control" id="cantidad_personas" name="cantidad_personas" 
                               required min="1" max="100">
                    </div>

                    <div class="form-group">
                        <label for="notas">Notas / Observaciones</label>
                        <textarea class="form-control" id="notas" name="notas" rows="3" maxlength="500"></textarea>
                    </div>

                    <input type="hidden" name="cancha_id" id="cancha_id">
                    <input type="hidden" name="tarifa_id" id="tarifa_id">
                    <input type="hidden" name="fecha_reserva" id="fecha_reserva">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnConfirmarReserva">
                        <i class="fas fa-check"></i> Confirmar Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirFormularioReserva(btn) {
    // Llenar datos del modal
    const cancha = btn.dataset.cancha;
    const tarifa = btn.dataset.tarifa;
    const fecha = btn.dataset.fecha;
    const canchaNombre = btn.dataset.canchaNombre;
    const horaInicio = btn.dataset.horaInicio;
    const horaFin = btn.dataset.horaFin;
    const precio = btn.dataset.precio;

    document.getElementById('cancha_id').value = cancha;
    document.getElementById('tarifa_id').value = tarifa;
    document.getElementById('fecha_reserva').value = fecha;
    
    document.getElementById('reservaCancha').textContent = canchaNombre;
    document.getElementById('reservaFecha').textContent = fecha;
    document.getElementById('reservaHora').textContent = horaInicio + ' - ' + horaFin;
    document.getElementById('reservaPrecio').textContent = '$' + parseFloat(precio).toFixed(2);

    // Limpiar formulario
    document.getElementById('nombre_cliente').value = '';
    document.getElementById('email_cliente').value = '';
    document.getElementById('telefono_cliente').value = '';
    document.getElementById('cantidad_personas').value = '';
    document.getElementById('notas').value = '';

    // Abrir modal
    $('#modalReserva').modal('show');
}

// Enviar formulario por AJAX
document.getElementById('formReserva').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const btn = document.getElementById('btnConfirmarReserva');
    const btnText = btn.innerHTML;
    
    // Validación básica
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Mostrar loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = btnText;
        
        if (data.success) {
            $('#modalReserva').modal('hide');
            DigiAlert.success(data.message || '¡Reserva creada exitosamente!');
            
            // Redirigir después de mostrar el mensaje
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } else {
            DigiAlert.error(data.message || 'Error al crear la reserva');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = btnText;
        console.error('Error:', error);
        DigiAlert.error('Error de conexión. Intente nuevamente.');
    });
});
</script>
