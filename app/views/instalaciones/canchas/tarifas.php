<?php
/**
 * Gestión de Tarifas para una Cancha
 * @var array $cancha
 * @var array $tarifas
 * @var string $csrf_token
 */
// URLs encriptadas
$urlListado = url('instalaciones', 'cancha', 'index');
$urlGuardarTarifa = url('instalaciones', 'cancha', 'guardarTarifa');

$diasSemana = [
    0 => 'Domingo',
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado'
];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="h3 fw-bold">
                <i class="fas fa-dollar-sign text-primary"></i> Gestión de Tarifas
            </h2>
            <p class="text-muted">
                Cancha: <strong><?php echo htmlspecialchars($cancha['nombre']); ?></strong> 
                - <?php echo htmlspecialchars($cancha['instalacion_nombre']); ?>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?php echo $urlListado; ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Formulario para agregar/editar tarifa -->
        <div class="col-lg-5 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle"></i> Nueva Tarifa
                    </h5>
                </div>

                <div class="card-body">
                    <form id="formTarifa" class="needs-validation">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="cancha_id" value="<?php echo $cancha['cancha_id']; ?>">
                        <input type="hidden" id="tarifa_id" name="tarifa_id" value="">

                        <div class="mb-3">
                            <label for="dia_semana" class="form-label">Día de la Semana <span class="text-danger">*</span></label>
                            <select id="dia_semana" name="dia_semana" class="form-select" required>
                                <option value="">Selecciona...</option>
                                <?php foreach ($diasSemana as $num => $nombre): ?>
                                    <option value="<?php echo $num; ?>"><?php echo $nombre; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hora_inicio" class="form-label">Hora Inicio <span class="text-danger">*</span></label>
                                <input type="time" id="hora_inicio" name="hora_inicio" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="hora_fin" class="form-label">Hora Fin <span class="text-danger">*</span></label>
                                <input type="time" id="hora_fin" name="hora_fin" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio (USD) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="precio" name="precio" class="form-control" 
                                       required min="0.01" step="0.01" placeholder="0.00">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select id="estado" name="estado" class="form-select">
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-save"></i> Guardar Tarifa
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i> Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información útil -->
            <div class="alert alert-info mt-4">
                <h6 class="alert-heading">
                    <i class="fas fa-lightbulb"></i> Sugerencias
                </h6>
                <small>
                    <ul class="mb-0">
                        <li>Define tarifas para cada día y rango horario</li>
                        <li>Puedes aplicar tarifas diferenciadas (peak/off-peak)</li>
                        <li>Desactiva tarifas sin eliminarlas</li>
                    </ul>
                </small>
            </div>
        </div>

        <!-- Listado de tarifas -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> Tarifas Registradas
                    </h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Día</th>
                                <th>Horario</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tarifas)): ?>
                                <?php foreach ($tarifas as $tarifa): ?>
                                    <tr>
                                        <td class="fw-semibold">
                                            <span class="badge bg-primary">
                                                <?php echo $diasSemana[$tarifa['dia_semana']]; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo substr($tarifa['hora_inicio'], 0, 5); ?> - 
                                            <?php echo substr($tarifa['hora_fin'], 0, 5); ?>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">
                                                $ <?php echo number_format($tarifa['precio'], 2); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($tarifa['estado'] === 'ACTIVO'): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="editarTarifa(<?php echo htmlspecialchars(json_encode($tarifa)); ?>)"
                                                        title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-delete-tarifa" 
                                                        data-id="<?php echo $tarifa['tarifa_id']; ?>"
                                                        data-dia="<?php echo $diasSemana[$tarifa['dia_semana']]; ?>"
                                                        data-url="<?php echo url('instalaciones', 'cancha', 'eliminarTarifa', ['id' => $tarifa['tarifa_id'], 'cancha_id' => $cancha['cancha_id']]); ?>"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        No hay tarifas registradas
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabla de precios sugerida -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light border-bottom">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-clock"></i> Plantilla de Horarios Recomendada
                    </h6>
                </div>
                <div class="table-responsive" style="font-size: 0.85rem;">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Horario</th>
                                <th>Tipo</th>
                                <th>Sugerencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>06:00 - 12:00</td>
                                <td><span class="badge bg-info">Mañana</span></td>
                                <td>Tarifa reducida (Off-peak)</td>
                            </tr>
                            <tr>
                                <td>12:00 - 17:00</td>
                                <td><span class="badge bg-warning">Tarde</span></td>
                                <td>Tarifa media</td>
                            </tr>
                            <tr>
                                <td>17:00 - 22:00</td>
                                <td><span class="badge bg-danger">Noche</span></td>
                                <td>Tarifa premium (Peak)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editarTarifa(tarifa) {
    document.getElementById('tarifa_id').value = tarifa.tarifa_id;
    document.getElementById('dia_semana').value = tarifa.dia_semana;
    document.getElementById('hora_inicio').value = tarifa.hora_inicio;
    document.getElementById('hora_fin').value = tarifa.hora_fin;
    document.getElementById('precio').value = tarifa.precio;
    document.getElementById('estado').value = tarifa.estado;
    
    // Notificar al usuario
    DigiAlert.info('Editando tarifa. Modifica los campos y guarda.');
    
    // Scroll al formulario
    document.getElementById('formTarifa').scrollIntoView({ behavior: 'smooth' });
}

// Manejar eliminación de tarifas
document.querySelectorAll('.btn-delete-tarifa').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const dia = this.dataset.dia;
        const urlEliminar = this.dataset.url;
        
        DigiAlert.confirmDelete(function() {
            DigiAlert.loading('Eliminando tarifa...');
            window.location.href = urlEliminar;
        }, {
            title: '¿Eliminar tarifa?',
            html: `<p>Estás a punto de eliminar la tarifa del día:</p>
                   <p class="font-weight-bold text-danger">${dia}</p>`,
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar'
        });
    });
});

// Manejar envío del formulario
document.getElementById('formTarifa').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const tarifaId = document.getElementById('tarifa_id').value;
    const accion = tarifaId ? 'actualizada' : 'creada';
    
    // Mostrar loading
    DigiAlert.loading('Guardando tarifa...');
    
    try {
        const response = await fetch('<?php echo $urlGuardarTarifa; ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        Swal.close();
        
        if (result.success) {
            DigiAlert.successAction(`Tarifa ${accion} exitosamente`, function() {
                window.location.reload();
            });
        } else {
            DigiAlert.errorDetail('Error', result.message || 'Error al guardar la tarifa');
        }
    } catch (error) {
        Swal.close();
        DigiAlert.errorDetail('Error de conexión', 'No se pudo conectar con el servidor');
        console.error('Error:', error);
    }
});

// Limpiar formulario al hacer reset
document.getElementById('formTarifa').addEventListener('reset', function() {
    document.getElementById('tarifa_id').value = '';
    DigiAlert.info('Formulario limpiado');
});
</script>
