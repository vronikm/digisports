<?php
/**
 * DigiSports Arena — Vista: Calendario Visual Semanal
 * Grilla interactiva de canchas × horarios con colores por estado
 */

$fecha         = $fecha ?? date('Y-m-d');
$instalacionId = $instalacion_id ?? 0;
$instalaciones = $instalaciones ?? [];
$canchas       = $canchas ?? [];
$diasSemana    = $dias_semana ?? [];
$horarios      = $horarios ?? [];
$inicioSemana  = $inicio_semana ?? date('Y-m-d');
$finSemana     = $fin_semana ?? date('Y-m-d');
$csrfToken     = $csrf_token ?? '';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-5">
                <h1 class="m-0">
                    <i class="fas fa-calendar-alt mr-2 text-primary"></i>
                    Calendario de Reservas
                </h1>
            </div>
            <div class="col-sm-7">
                <div class="float-sm-right d-flex align-items-center">
                    <!-- Selector de instalación -->
                    <select id="selectInstalacion" class="form-control form-control-sm mr-2" style="width: 200px;">
                        <?php foreach ($instalaciones as $inst): ?>
                        <option value="<?= $inst['instalacion_id'] ?>" <?= $inst['instalacion_id'] == $instalacionId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($inst['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <!-- Navegación semana -->
                    <div class="btn-group btn-group-sm mr-2">
                        <button class="btn btn-outline-secondary" id="btnSemanaAnterior" title="Semana anterior">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="btn btn-outline-primary" id="btnHoy" title="Ir a hoy">Hoy</button>
                        <button class="btn btn-outline-secondary" id="btnSemanaSiguiente" title="Semana siguiente">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    
                    <input type="date" id="inputFecha" class="form-control form-control-sm" style="width: 160px;" value="<?= $fecha ?>">
                    
                    <a href="<?= url('reservas', 'reserva', 'buscar') ?>" class="btn btn-sm btn-primary ml-2">
                        <i class="fas fa-plus mr-1"></i> Reservar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Info semana -->
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h5 id="labelSemana" class="mb-0 text-muted">
                <i class="far fa-calendar-alt mr-1"></i>
                Semana del <?= date('d/m', strtotime($inicioSemana)) ?> al <?= date('d/m/Y', strtotime($finSemana)) ?>
            </h5>
            <!-- Leyenda -->
            <div class="d-flex align-items-center">
                <span class="badge mr-2" style="background: #10B981; color: #fff; font-size:.75rem;">
                    <i class="fas fa-circle mr-1" style="font-size:.5rem"></i> Disponible
                </span>
                <span class="badge mr-2" style="background: #3B82F6; color: #fff; font-size:.75rem;">
                    <i class="fas fa-circle mr-1" style="font-size:.5rem"></i> Reservada
                </span>
                <span class="badge mr-2" style="background: #F59E0B; color: #fff; font-size:.75rem;">
                    <i class="fas fa-circle mr-1" style="font-size:.5rem"></i> Pendiente
                </span>
                <span class="badge mr-2" style="background: #EF4444; color: #fff; font-size:.75rem;">
                    <i class="fas fa-circle mr-1" style="font-size:.5rem"></i> Mantenimiento
                </span>
                <span class="badge" style="background: #E5E7EB; color: #9CA3AF; font-size:.75rem;">
                    <i class="fas fa-circle mr-1" style="font-size:.5rem"></i> Sin Tarifa
                </span>
            </div>
        </div>

        <!-- Loading -->
        <div id="calendarioLoading" class="text-center py-5" style="display:none;">
            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
            <p class="mt-2 text-muted">Cargando calendario...</p>
        </div>

        <!-- Tabs por cancha -->
        <?php if (!empty($canchas)): ?>
        <ul class="nav nav-tabs mb-0" id="tabsCanchas">
            <?php foreach ($canchas as $i => $c): ?>
            <li class="nav-item">
                <a class="nav-link <?= $i === 0 ? 'active' : '' ?>" data-toggle="tab" href="#cancha_<?= $c['cancha_id'] ?>"
                   data-cancha-id="<?= $c['cancha_id'] ?>">
                    <i class="fas fa-futbol mr-1"></i>
                    <?= htmlspecialchars($c['nombre']) ?>
                    <small class="text-muted ml-1">(<?= $c['tipo'] ?? '' ?>)</small>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content">
            <?php foreach ($canchas as $i => $cancha): ?>
            <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="cancha_<?= $cancha['cancha_id'] ?>">
                <div class="card mb-0" style="border-top-left-radius: 0; border-top-right-radius: 0;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0 calendario-grid" 
                                   data-cancha-id="<?= $cancha['cancha_id'] ?>">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="text-center" style="width: 80px; vertical-align: middle;">
                                            <i class="far fa-clock"></i>
                                        </th>
                                        <?php foreach ($diasSemana as $dia): ?>
                                        <th class="text-center <?= $dia['esHoy'] ? 'bg-primary text-white' : '' ?>" 
                                            style="min-width: 120px;">
                                            <div class="font-weight-bold"><?= $dia['nombre'] ?></div>
                                            <div style="font-size: .85rem;"><?= $dia['dia'] ?></div>
                                        </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($horarios as $hora): ?>
                                    <tr>
                                        <td class="text-center text-muted font-weight-bold" style="vertical-align: middle; background: #f8f9fa;">
                                            <?= $hora ?>
                                        </td>
                                        <?php foreach ($diasSemana as $dia): ?>
                                        <?php
                                            $horaFin = sprintf('%02d:00', (int)substr($hora, 0, 2) + 1);
                                            $cellId = "cell_{$cancha['cancha_id']}_{$dia['fecha']}_{$hora}";
                                        ?>
                                        <td class="calendario-celda p-1" 
                                            id="<?= $cellId ?>"
                                            data-cancha="<?= $cancha['cancha_id'] ?>"
                                            data-fecha="<?= $dia['fecha'] ?>"
                                            data-hora="<?= $hora ?>"
                                            data-hora-fin="<?= $horaFin ?>"
                                            style="cursor: pointer; height: 50px; vertical-align: middle; transition: all .15s;">
                                            <div class="celda-contenido text-center" style="font-size: .8rem;"></div>
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-times fa-4x mb-3 text-muted" style="opacity: .2"></i>
                <p class="text-muted">No hay canchas activas en esta instalación</p>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- Tooltip de celda -->
<div id="celdaTooltip" class="popover bs-popover-bottom" style="display:none; position:fixed; z-index:9999; max-width:280px;">
    <div class="popover-header" id="tooltipHeader"></div>
    <div class="popover-body" id="tooltipBody"></div>
</div>

<style>
.calendario-grid td.calendario-celda:hover {
    transform: scale(1.02);
    box-shadow: 0 0 8px rgba(59,130,246,.3);
    z-index: 2;
    position: relative;
}
.calendario-celda.cel-disponible { background: #D1FAE5; }
.calendario-celda.cel-reservada  { background: #DBEAFE; }
.calendario-celda.cel-pendiente  { background: #FEF3C7; }
.calendario-celda.cel-manten     { background: #FEE2E2; }
.calendario-celda.cel-sin-tarifa { background: #F3F4F6; color: #9CA3AF; cursor: default; }
.calendario-celda.cel-pasada     { background: #F9FAFB; color: #D1D5DB; cursor: default; }

.celda-contenido .precio { font-weight: 700; }
.celda-contenido .estado { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; }
.celda-contenido .cliente { font-size: .7rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 110px; margin: 0 auto; }

.nav-tabs .nav-link.active {
    border-bottom-color: #fff;
    font-weight: 600;
    color: #3B82F6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var instalacionId = <?= $instalacionId ?>;
    var inicioSemana = '<?= $inicioSemana ?>';
    var finSemana = '<?= $finSemana ?>';

    // Cargar datos
    cargarEventos();

    function cargarEventos() {
        var loading = document.getElementById('calendarioLoading');
        loading.style.display = 'block';

        var apiUrl = '<?= url('instalaciones', 'calendario', 'eventos') ?>'
            + '&inicio=' + inicioSemana
            + '&fin=' + finSemana
            + '&instalacion_id=' + instalacionId;

        fetch(apiUrl)
            .then(function(r) { return r.json(); })
            .then(function(resp) {
                loading.style.display = 'none';
                var data = resp.data || resp;
                renderizarCalendario(data);
            })
            .catch(function(err) {
                loading.style.display = 'none';
                console.error('Error cargando eventos:', err);
            });
    }

    function renderizarCalendario(data) {
        var reservas = data.reservas || [];
        var mantenimientos = data.mantenimientos || [];
        var tarifas = data.tarifas || [];
        var ahora = new Date();
        var hoyStr = ahora.toISOString().substring(0, 10);
        var horaActual = ahora.getHours();

        // Indexar tarifas por cancha+dia_semana
        var tarifaIndex = {};
        tarifas.forEach(function(t) {
            var key = t.cancha_id + '_' + t.dia_semana;
            if (!tarifaIndex[key]) tarifaIndex[key] = [];
            tarifaIndex[key].push(t);
        });

        // Indexar reservas por instalacion_id+fecha+hora
        var reservaIndex = {};
        reservas.forEach(function(r) {
            var key = r.cancha_id + '_' + r.fecha_reserva + '_' + r.hora_inicio.substring(0,5);
            reservaIndex[key] = r;
        });

        // Indexar mantenimientos por cancha_id+fecha
        var mantenIndex = {};
        mantenimientos.forEach(function(m) {
            // Un mantenimiento puede cubrir varias horas/días
            var key = m.cancha_id + '_maint';
            if (!mantenIndex[key]) mantenIndex[key] = [];
            mantenIndex[key].push(m);
        });

        // Recorrer cada celda
        document.querySelectorAll('.calendario-celda').forEach(function(celda) {
            var canchaId = celda.dataset.cancha;
            var fecha = celda.dataset.fecha;
            var hora = celda.dataset.hora;
            var horaInt = parseInt(hora.substring(0, 2));
            var fechaObj = new Date(fecha + 'T00:00:00');
            var diaSem = fechaObj.getDay();
            var contenido = celda.querySelector('.celda-contenido');

            // Limpiar
            celda.className = 'calendario-celda p-1';
            contenido.innerHTML = '';

            // Pasada?
            if (fecha < hoyStr || (fecha === hoyStr && horaInt < horaActual)) {
                celda.classList.add('cel-pasada');
                contenido.innerHTML = '<span class="estado">—</span>';
                return;
            }

            // Mantenimiento?
            var maintKey = canchaId + '_maint';
            if (mantenIndex[maintKey]) {
                var enMant = mantenIndex[maintKey].some(function(m) {
                    var mInicio = new Date(m.fecha_inicio);
                    var mFin = new Date(m.fecha_fin);
                    var celdaTime = new Date(fecha + 'T' + hora + ':00');
                    return celdaTime >= mInicio && celdaTime < mFin;
                });
                if (enMant) {
                    celda.classList.add('cel-manten');
                    contenido.innerHTML = '<i class="fas fa-tools" style="font-size:.9rem;color:#EF4444;"></i><br><span class="estado">Mant.</span>';
                    return;
                }
            }

            // Reservada?
            var resKey = canchaId + '_' + fecha + '_' + hora;
            if (reservaIndex[resKey]) {
                var r = reservaIndex[resKey];
                if (r.estado === 'CONFIRMADA') {
                    celda.classList.add('cel-reservada');
                    contenido.innerHTML = '<i class="fas fa-check-circle" style="color:#3B82F6;"></i>' +
                        '<div class="cliente" title="' + (r.cliente_nombre||'') + '">' + (r.cliente_nombre||'Reservada') + '</div>' +
                        '<div class="precio text-primary">$' + parseFloat(r.total||0).toFixed(0) + '</div>';
                } else {
                    celda.classList.add('cel-pendiente');
                    contenido.innerHTML = '<i class="fas fa-clock" style="color:#F59E0B;"></i>' +
                        '<div class="cliente" title="' + (r.cliente_nombre||'') + '">' + (r.cliente_nombre||'Pendiente') + '</div>' +
                        '<div class="estado text-warning">Pendiente</div>';
                }
                // Almacenar datos para click
                celda.dataset.reservaId = r.reserva_id;
                return;
            }

            // Tiene tarifa?
            var tarifaKey = canchaId + '_' + diaSem;
            var tarifaMatch = null;
            if (tarifaIndex[tarifaKey]) {
                tarifaMatch = tarifaIndex[tarifaKey].find(function(t) {
                    return t.hora_inicio.substring(0,5) === hora;
                });
            }

            if (tarifaMatch) {
                celda.classList.add('cel-disponible');
                contenido.innerHTML = '<div class="precio text-success">$' + parseFloat(tarifaMatch.precio).toFixed(0) + '</div>' +
                    '<div class="estado text-success">Libre</div>';
                celda.dataset.tarifaId = tarifaMatch.tarifa_id;
                celda.dataset.precio = tarifaMatch.precio;
            } else {
                celda.classList.add('cel-sin-tarifa');
                contenido.innerHTML = '<span class="estado">—</span>';
            }
        });
    }

    // Click en celda disponible → ir a reservar
    document.addEventListener('click', function(e) {
        var celda = e.target.closest('.calendario-celda');
        if (!celda) return;

        if (celda.classList.contains('cel-disponible')) {
            // Redirigir a crear reserva
            var url = '<?= url('reservas', 'reserva', 'buscar') ?>' +
                '&fecha=' + celda.dataset.fecha +
                '&instalacion_id=' + instalacionId;
            window.location.href = url;
        } else if (celda.dataset.reservaId) {
            // Ir a ver reserva
            window.location.href = '<?= url('reservas', 'reserva', 'ver') ?>&id=' + celda.dataset.reservaId;
        }
    });

    // Navegación semanal
    document.getElementById('btnSemanaAnterior').addEventListener('click', function() {
        var d = new Date(inicioSemana + 'T00:00:00');
        d.setDate(d.getDate() - 7);
        navegarFecha(d.toISOString().substring(0, 10));
    });

    document.getElementById('btnSemanaSiguiente').addEventListener('click', function() {
        var d = new Date(inicioSemana + 'T00:00:00');
        d.setDate(d.getDate() + 7);
        navegarFecha(d.toISOString().substring(0, 10));
    });

    document.getElementById('btnHoy').addEventListener('click', function() {
        navegarFecha(new Date().toISOString().substring(0, 10));
    });

    document.getElementById('inputFecha').addEventListener('change', function() {
        navegarFecha(this.value);
    });

    document.getElementById('selectInstalacion').addEventListener('change', function() {
        var url = '<?= url('instalaciones', 'calendario', 'index') ?>' +
            '&fecha=' + document.getElementById('inputFecha').value +
            '&instalacion_id=' + this.value;
        window.location.href = url;
    });

    function navegarFecha(fecha) {
        var url = '<?= url('instalaciones', 'calendario', 'index') ?>' +
            '&fecha=' + fecha +
            '&instalacion_id=' + instalacionId;
        window.location.href = url;
    }
});
</script>
