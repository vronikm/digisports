<?php
/**
 * DigiSports Natación - Reportes
 */
$kpis        = $kpis ?? [];
$periodos    = $periodos ?? [];
$sedes       = $sedes ?? [];
$sedeActiva  = $sede_activa ?? null;
$moduloColor = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-chart-pie mr-2" style="color:<?= $moduloColor ?>"></i>Reportes</h1></div>
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
                    <select id="sedeFilterRep" class="form-control">
                        <option value="">Todas las sedes</option>
                        <?php foreach ($sedes as $s): ?>
                        <option value="<?= $s['sed_sede_id'] ?>" <?= $sedeActiva == $s['sed_sede_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['sed_nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- KPIs Resumen -->
        <div class="row mb-3">
            <div class="col-lg"><div class="small-box bg-primary"><div class="inner"><h4><?= $kpis['total_alumnos'] ?? 0 ?></h4><p>Total Alumnos</p></div><div class="icon"><i class="fas fa-user-graduate"></i></div></div></div>
            <div class="col-lg"><div class="small-box bg-success"><div class="inner"><h4><?= $kpis['inscripciones_activas'] ?? 0 ?></h4><p>Inscripciones Activas</p></div><div class="icon"><i class="fas fa-clipboard-list"></i></div></div></div>
            <div class="col-lg"><div class="small-box bg-warning"><div class="inner"><h4>$<?= number_format($kpis['ingresos_mes'] ?? 0, 2) ?></h4><p>Ingresos del Mes</p></div><div class="icon"><i class="fas fa-dollar-sign"></i></div></div></div>
            <div class="col-lg"><div class="small-box bg-danger"><div class="inner"><h4>$<?= number_format($kpis['egresos_mes'] ?? 0, 2) ?></h4><p>Egresos del Mes</p></div><div class="icon"><i class="fas fa-money-bill-wave"></i></div></div></div>
            <div class="col-lg"><div class="small-box bg-info"><div class="inner"><h4><?= $kpis['grupos_activos'] ?? 0 ?></h4><p>Grupos Activos</p></div><div class="icon"><i class="fas fa-users"></i></div></div></div>
        </div>

        <!-- Tabs de Reportes -->
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tabIngresos"><i class="fas fa-dollar-sign mr-1"></i>Ingresos</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabEgresos"><i class="fas fa-money-bill-wave mr-1"></i>Egresos</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabComparacion"><i class="fas fa-balance-scale mr-1"></i>Sedes</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabOcupacion"><i class="fas fa-chart-bar mr-1"></i>Ocupación</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabAsistencia"><i class="fas fa-clipboard-check mr-1"></i>Asistencia</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabEvaluaciones"><i class="fas fa-star mr-1"></i>Evaluaciones</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Ingresos -->
                    <div class="tab-pane fade show active" id="tabIngresos">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="small">Año</label>
                                <select id="ingresosYear" class="form-control form-control-sm">
                                    <?php for($y = date('Y'); $y >= date('Y')-3; $y--): ?>
                                    <option value="<?= $y ?>"><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3 pt-4"><button class="btn btn-sm btn-primary" onclick="cargarIngresos()"><i class="fas fa-search mr-1"></i>Generar</button></div>
                        </div>
                        <div class="chart-container" style="height:300px;"><canvas id="chartIngresos"></canvas></div>
                    </div>

                    <!-- Egresos -->
                    <div class="tab-pane fade" id="tabEgresos">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="small">Año</label>
                                <select id="egresosYear" class="form-control form-control-sm">
                                    <?php for($y = date('Y'); $y >= date('Y')-3; $y--): ?>
                                    <option value="<?= $y ?>"><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3 pt-4"><button class="btn btn-sm btn-danger" onclick="cargarEgresos()"><i class="fas fa-search mr-1"></i>Generar</button></div>
                        </div>
                        <div class="chart-container" style="height:300px;"><canvas id="chartEgresos"></canvas></div>
                    </div>

                    <!-- Comparación Sedes -->
                    <div class="tab-pane fade" id="tabComparacion">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="small">Mes</label>
                                <select id="compMes" class="form-control form-control-sm">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>><?= ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'][$m] ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small">Año</label>
                                <select id="compYear" class="form-control form-control-sm">
                                    <?php for($y = date('Y'); $y >= date('Y')-3; $y--): ?>
                                    <option value="<?= $y ?>"><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3 pt-4"><button class="btn btn-sm btn-info" onclick="cargarComparacion()"><i class="fas fa-search mr-1"></i>Comparar</button></div>
                        </div>
                        <div id="comparacionResult"></div>
                        <div class="chart-container mt-3" style="height:300px;"><canvas id="chartComparacion"></canvas></div>
                    </div>

                    <!-- Ocupación -->
                    <div class="tab-pane fade" id="tabOcupacion">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="small">Período</label>
                                <select id="ocupacionPeriodo" class="form-control form-control-sm">
                                    <option value="">— Todos —</option>
                                    <?php foreach ($periodos as $p): ?>
                                    <option value="<?= $p['npe_periodo_id'] ?>"><?= htmlspecialchars($p['npe_nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 pt-4"><button class="btn btn-sm btn-primary" onclick="cargarOcupacion()"><i class="fas fa-search mr-1"></i>Generar</button></div>
                        </div>
                        <div id="ocupacionResult"></div>
                    </div>

                    <!-- Asistencia -->
                    <div class="tab-pane fade" id="tabAsistencia">
                        <p class="text-muted">Para reportes detallados de asistencia, use <a href="<?= url('natacion', 'asistencia', 'reporte') ?>">Reporte de Asistencia</a>.</p>
                    </div>

                    <!-- Evaluaciones -->
                    <div class="tab-pane fade" id="tabEvaluaciones">
                        <p class="text-muted">Próximamente: reportes de progreso por nivel y alumno.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<script>
var chartIngresos;

function cargarIngresos() {
    var year = document.getElementById('ingresosYear').value;
    $.getJSON('<?= url('natacion', 'reporte', 'ingresos') ?>&year=' + year, function(res) {
        if (!res.success) return;
        var labels = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        var pagado = new Array(12).fill(0);
        var pendiente = new Array(12).fill(0);
        res.data.forEach(function(r) { pagado[r.mes-1] = parseFloat(r.pagado); pendiente[r.mes-1] = parseFloat(r.pendiente); });

        if (chartIngresos) chartIngresos.destroy();
        chartIngresos = new Chart(document.getElementById('chartIngresos').getContext('2d'), {
            type: 'bar',
            data: { labels: labels, datasets: [
                { label: 'Pagado', data: pagado, backgroundColor: '#22C55E', borderRadius: 4 },
                { label: 'Pendiente', data: pendiente, backgroundColor: '#F59E0B', borderRadius: 4 }
            ] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { position: 'bottom' } } }
        });
    });
}

function cargarOcupacion() {
    var periodo = document.getElementById('ocupacionPeriodo').value;
    $.getJSON('<?= url('natacion', 'reporte', 'inscripcionesPorGrupo') ?>&periodo_id=' + periodo, function(res) {
        if (!res.success || !res.data.length) { $('#ocupacionResult').html('<div class="text-muted text-center py-3">Sin datos</div>'); return; }
        var html = '<table class="table table-sm"><thead><tr><th>Grupo</th><th>Nivel</th><th>Instructor</th><th class="text-center">Cupo</th><th>Ocupación</th></tr></thead><tbody>';
        res.data.forEach(function(r) {
            var pct = parseFloat(r.porcentaje_ocupacion) || 0;
            var cls = pct >= 90 ? 'danger' : (pct >= 70 ? 'warning' : 'success');
            html += '<tr><td>' + r.grupo + '</td><td>' + (r.nivel||'—') + '</td><td>' + (r.instructor_nombre||'') + ' ' + (r.instructor_apellido||'') + '</td>';
            html += '<td class="text-center">' + r.ngr_cupo_actual + '/' + r.ngr_cupo_maximo + '</td>';
            html += '<td><div class="progress" style="height:18px"><div class="progress-bar bg-' + cls + '" style="width:' + pct + '%">' + pct + '%</div></div></td></tr>';
        });
        html += '</tbody></table>';
        $('#ocupacionResult').html(html);
    });
}

// Cargar ingresos al inicio
$(function() {
    cargarIngresos();
    $('#sedeFilterRep').on('change', function() {
        var sedeId = $(this).val();
        $.post('<?= url('natacion', 'sede', 'seleccionar') ?>', { sede_id: sedeId }, function() { location.reload(); }, 'json');
    });
});

var chartEgresos, chartComparacion;

function cargarEgresos() {
    var year = document.getElementById('egresosYear').value;
    $.getJSON('<?= url('natacion', 'reporte', 'egresos') ?>&year=' + year, function(res) {
        if (!res.success) return;
        var labels = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        var totales = new Array(12).fill(0);
        res.data.forEach(function(r) { totales[r.mes-1] += parseFloat(r.total); });

        if (chartEgresos) chartEgresos.destroy();
        chartEgresos = new Chart(document.getElementById('chartEgresos').getContext('2d'), {
            type: 'bar',
            data: { labels: labels, datasets: [
                { label: 'Egresos', data: totales, backgroundColor: '#EF4444', borderRadius: 4 }
            ] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { position: 'bottom' } } }
        });
    });
}

function cargarComparacion() {
    var mes  = document.getElementById('compMes').value;
    var year = document.getElementById('compYear').value;
    $.getJSON('<?= url('natacion', 'reporte', 'comparacionSedes') ?>&mes=' + mes + '&year=' + year, function(res) {
        if (!res.success || !res.data.length) {
            $('#comparacionResult').html('<div class="text-muted text-center py-3">Sin datos</div>');
            return;
        }
        // Tabla
        var html = '<table class="table table-sm"><thead class="thead-light"><tr><th>Sede</th><th class="text-right text-success">Ingresos</th><th class="text-right text-danger">Egresos</th><th class="text-right">Utilidad</th></tr></thead><tbody>';
        var labels = [], ing = [], egr = [];
        res.data.forEach(function(r) {
            var util = parseFloat(r.utilidad) || 0;
            html += '<tr><td><strong>' + r.sed_nombre + '</strong></td>';
            html += '<td class="text-right text-success">$' + parseFloat(r.ingresos).toFixed(2) + '</td>';
            html += '<td class="text-right text-danger">$' + parseFloat(r.egresos).toFixed(2) + '</td>';
            html += '<td class="text-right ' + (util >= 0 ? 'text-success' : 'text-danger') + ' font-weight-bold">$' + util.toFixed(2) + '</td></tr>';
            labels.push(r.sed_nombre); ing.push(parseFloat(r.ingresos)); egr.push(parseFloat(r.egresos));
        });
        html += '</tbody></table>';
        $('#comparacionResult').html(html);

        // Chart
        if (chartComparacion) chartComparacion.destroy();
        chartComparacion = new Chart(document.getElementById('chartComparacion').getContext('2d'), {
            type: 'bar',
            data: { labels: labels, datasets: [
                { label: 'Ingresos', data: ing, backgroundColor: '#22C55E', borderRadius: 4 },
                { label: 'Egresos', data: egr, backgroundColor: '#EF4444', borderRadius: 4 }
            ] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { position: 'bottom' } } }
        });
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>
