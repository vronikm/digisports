<?php
/**
 * Vista Calendario de Disponibilidad
 * Muestra disponibilidad de una cancha en vista de calendario
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">
                <i class="fas fa-calendar-alt"></i> Calendario de Disponibilidad
            </h1>
        </div>
    </div>

    <!-- Selectors -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-body p-2">
                    <form method="get" id="formCalendario" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="mes" class="mr-2">Mes:</label>
                            <select class="form-control form-control-sm" id="mes" name="mes">
                                <?php 
                                $mesActual = date('m');
                                for ($i = 0; $i < 12; $i++) {
                                    $mes = date('m', strtotime("+$i months"));
                                    $año = date('Y', strtotime("+$i months"));
                                    $fecha = "{$año}-{$mes}";
                                    $label = strftime('%B %Y', strtotime("{$año}-{$mes}-01"));
                                ?>
                                    <option value="<?= $fecha ?>" 
                                        <?= ($i === 0) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group mr-3">
                            <label for="instalacion_cal" class="mr-2">Instalación:</label>
                            <select class="form-control form-control-sm" id="instalacion_cal" name="instalacion_id">
                                <option value="">-- Seleccionar --</option>
                                <?php if (!empty($instalaciones)): ?>
                                    <?php foreach ($instalaciones as $inst): ?>
                                        <option value="<?= $inst['instalacion_id'] ?>">
                                            <?= htmlspecialchars($inst['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cancha_cal" class="mr-2">Cancha:</label>
                            <select class="form-control form-control-sm" id="cancha_cal" name="cancha_id">
                                <option value="">-- Seleccionar --</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm ml-3">
                            <i class="fas fa-refresh"></i> Actualizar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendario -->
    <div class="row">
        <div class="col-md-12">
            <div id="calendario" class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title" id="tituloCalendario">
                        <i class="fas fa-calendar"></i> Seleccione una cancha para ver disponibilidad
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="tablaCalendario">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" style="width: 15%;">Lunes</th>
                                    <th class="text-center" style="width: 15%;">Martes</th>
                                    <th class="text-center" style="width: 15%;">Miércoles</th>
                                    <th class="text-center" style="width: 15%;">Jueves</th>
                                    <th class="text-center" style="width: 15%;">Viernes</th>
                                    <th class="text-center" style="width: 10%;">Sábado</th>
                                    <th class="text-center" style="width: 10%;">Domingo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Generado por JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Leyenda -->
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="alert alert-success mb-0" style="padding: 5px 10px;">
                                    <small><strong>Verde</strong>: Disponible</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alert alert-danger mb-0" style="padding: 5px 10px;">
                                    <small><strong>Rojo</strong>: No disponible</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alert alert-warning mb-0" style="padding: 5px 10px;">
                                    <small><strong>Amarillo</strong>: Parcialmente reservado</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alert alert-secondary mb-0" style="padding: 5px 10px;">
                                    <small><strong>Gris</strong>: Fuera de mes</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel detalle de día -->
    <div class="row mt-4" id="panelDetalleDia" style="display: none;">
        <div class="col-md-8 offset-md-2">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title" id="tituloDetalleDia"></h3>
                    <div class="card-tools">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm" id="tablaDetalleHorarios">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                    <th>Precio</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Generado por AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dia-disponible {
    background-color: #d4edda;
    cursor: pointer;
    transition: all 0.3s;
}
.dia-disponible:hover {
    background-color: #28a745;
    color: white;
}

.dia-no-disponible {
    background-color: #f8d7da;
    cursor: not-allowed;
}

.dia-parcial {
    background-color: #fff3cd;
    cursor: pointer;
    transition: all 0.3s;
}
.dia-parcial:hover {
    background-color: #ffc107;
    color: white;
}

.dia-fuera-mes {
    background-color: #e2e3e5;
    color: #6c757d;
}

.celda-dia {
    min-height: 100px;
    padding: 10px;
    vertical-align: top;
    border: 1px solid #ddd;
}

.numero-dia {
    font-weight: bold;
    font-size: 1.1em;
    margin-bottom: 5px;
}

.info-dia {
    font-size: 0.85em;
    margin-top: 5px;
}
</style>

<script>
$(document).ready(function() {
    // Cargar canchas cuando cambia instalación
    $('#instalacion_cal').change(function() {
        const instalacion_id = $(this).val();
        const $canchaSel = $('#cancha_cal');
        
        $canchaSel.html('<option value="">Cargando...</option>');
        
        if (instalacion_id) {
            $.ajax({
                url: '<?= url('instalaciones', 'cancha', 'obtenerCanchasPorInstalacion') ?>',
                type: 'GET',
                data: { instalacion_id: instalacion_id },
                dataType: 'json',
                success: function(canchas) {
                    $canchaSel.html('<option value="">-- Seleccionar --</option>');
                    $.each(canchas, function(i, cancha) {
                        $canchaSel.append(
                            $('<option></option>').val(cancha.cancha_id).text(cancha.nombre)
                        );
                    });
                }
            });
        }
    });

    // Generar calendario cuando se selecciona una cancha
    $('#cancha_cal').change(function() {
        generarCalendario();
    });

    // Generar calendario al cargar si hay parámetros
    if ($('#cancha_cal').val()) {
        generarCalendario();
    }
});

function generarCalendario() {
    const canchaNombre = $('#cancha_cal option:selected').text();
    const mes = $('#mes').val();
    const canchaid = $('#cancha_cal').val();
    
    if (!mes || !canchaid) {
        return;
    }

    // Actualizar título
    $('#tituloCalendario').text(
        '<i class="fas fa-calendar"></i> ' + canchaNombre + ' - ' + 
        new Date(mes + '-01').toLocaleDateString('es-ES', { month: 'long', year: 'numeric' })
    );

    // Generar estructura de calendario
    const fecha = new Date(mes + '-01');
    const año = fecha.getFullYear();
    const mes_num = fecha.getMonth() + 1;
    
    const primerDia = new Date(año, mes_num - 1, 1).getDay();
    const diasEnMes = new Date(año, mes_num, 0).getDate();
    
    let html = '<tr>';
    let diaNum = 1;
    
    // Llenar primer fila con días fuera de mes
    for (let i = 1; i < primerDia; i++) {
        html += '<td class="celda-dia dia-fuera-mes"></td>';
    }
    
    // Llenar días del mes
    for (let d = primerDia; d < 7 && diaNum <= diasEnMes; d++) {
        const fechaFormato = año + '-' + String(mes_num).padStart(2, '0') + '-' + String(diaNum).padStart(2, '0');
        html += `
            <td class="celda-dia dia-disponible" data-fecha="${fechaFormato}" 
                onclick="mostrarDetallesDia('${fechaFormato}', '${canchaid}', '${canchaNombre}')">
                <div class="numero-dia">${diaNum}</div>
                <div class="info-dia">
                    <i class="fas fa-check-circle" style="color: green;"></i> 
                    <span class="disponibles">Cargando...</span>
                </div>
            </td>
        `;
        diaNum++;
    }
    html += '</tr>';
    
    // Resto de filas
    while (diaNum <= diasEnMes) {
        html += '<tr>';
        for (let d = 0; d < 7 && diaNum <= diasEnMes; d++) {
            const fechaFormato = año + '-' + String(mes_num).padStart(2, '0') + '-' + String(diaNum).padStart(2, '0');
            html += `
                <td class="celda-dia dia-disponible" data-fecha="${fechaFormato}"
                    onclick="mostrarDetallesDia('${fechaFormato}', '${canchaid}', '${canchaNombre}')">
                    <div class="numero-dia">${diaNum}</div>
                    <div class="info-dia">
                        <i class="fas fa-check-circle" style="color: green;"></i> 
                        <span class="disponibles">Cargando...</span>
                    </div>
                </td>
            `;
            diaNum++;
        }
        // Llenar último día de mes si es necesario
        for (let i = 0; i < (7 - ((diaNum - 1) % 7)); i++) {
            html += '<td class="celda-dia dia-fuera-mes"></td>';
        }
        html += '</tr>';
    }
    
    $('#tablaCalendario tbody').html(html);
    
    // Cargar disponibilidad real
    cargarDisponibilidadCalendario(canchaid, mes);
}

function cargarDisponibilidadCalendario(canchaid, mes) {
    // AJAX para obtener disponibilidad real del mes
    // Por ahora, simulamos
    
    console.log('Cargar disponibilidad para cancha: ' + canchaid + ', mes: ' + mes);
    
    // TODO: Implementar AJAX para:
    // 1. Obtener todas las fechas del mes
    // 2. Para cada fecha, obtener tarifas disponibles
    // 3. Colorear celdas según disponibilidad
}

function mostrarDetallesDia(fecha, canchaid, canchaNombre) {
    $('#tituloDetalleDia').text('Disponibilidad - ' + canchaNombre + ' - ' + 
        new Date(fecha).toLocaleDateString('es-ES', {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'}));
    
    // AJAX para obtener horarios disponibles del día
    $.ajax({
        url: '<?= url('reservas', 'reserva', 'obtenerDisponibilidad') ?>',
        type: 'GET',
        data: {
            cancha_id: canchaid,
            fecha: fecha
        },
        dataType: 'json',
        success: function(franjas) {
            let html = '';
            if (franjas.length > 0) {
                $.each(franjas, function(i, franja) {
                    const badgeClass = franja.disponible ? 'success' : 'danger';
                    const texto = franja.disponible ? 'Disponible' : 'Reservada';
                    
                    html += `
                        <tr>
                            <td>${franja.hora_inicio} - ${franja.hora_fin}</td>
                            <td><span class="badge badge-${badgeClass}">${texto}</span></td>
                            <td>$${parseFloat(franja.precio).toFixed(2)}</td>
                            <td>
                    `;
                    
                    if (franja.disponible) {
                        html += `
                            <button class="btn btn-xs btn-primary" data-cancha="${canchaid}" 
                                    data-tarifa="${franja.tarifa_id}" data-fecha="${fecha}"
                                    data-hora="${franja.hora_inicio}" data-precio="${franja.precio}"
                                    onclick="abrirFormularioReservaDesdeCalendario(this)">
                                <i class="fas fa-plus"></i> Reservar
                            </button>
                        `;
                    }
                    
                    html += '</td></tr>';
                });
            } else {
                html = '<tr><td colspan="4" class="text-center text-muted">Sin disponibilidad</td></tr>';
            }
            
            $('#tablaDetalleHorarios tbody').html(html);
            $('#panelDetalleDia').show();
        }
    });
}

function abrirFormularioReservaDesdeCalendario(btn) {
    // Implementación similar a buscar.php
    const cancha = $(btn).data('cancha');
    const tarifa = $(btn).data('tarifa');
    const fecha = $(btn).data('fecha');
    const canchaNombre = $('#cancha_cal option:selected').text();
    const horaInicio = $(btn).data('hora');
    const precio = $(btn).data('precio');
    
    // TODO: Abrir modal de reserva con estos datos pre-llenados
    alert('Función de reserva desde calendario - En construcción\n\nCancha: ' + canchaNombre + '\nFecha: ' + fecha + '\nHora: ' + horaInicio);
}
</script>
