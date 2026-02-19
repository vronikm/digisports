<?php
/**
 * DigiSports Natación - Vista Dashboard (datos dinámicos)
 */
$kpis               = $kpis ?? [];
$clases_hoy         = $clases_hoy ?? [];
$alumnos_nivel      = $alumnos_nivel ?? [];
$piscinasData       = $piscinas ?? [];
$ultimas            = $ultimas_inscripciones ?? [];
$chartLabels        = $chart_labels ?? '[]';
$chartPresente      = $chart_presente ?? '[]';
$chartAusente       = $chart_ausente ?? '[]';
$moduloColor        = $modulo_actual['color'] ?? '#0EA5E9';
$moduloIcono        = $modulo_actual['icono'] ?? 'fas fa-swimmer';
$totalAlumnos       = array_sum(array_column($alumnos_nivel, 'total')) ?: 1;
$sedes              = $sedes ?? [];
$sedeActiva         = $sede_activa ?? null;
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?> mr-2" style="color: <?= $moduloColor ?>"></i>
                    <?= $modulo_actual['nombre_personalizado'] ?? $modulo_actual['nombre'] ?? 'Dashboard Natación' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right quick-actions d-flex align-items-center">
                    <?php if (!empty($sedes) && count($sedes) > 1): ?>
                    <select id="sedeFilterDash" class="form-control form-control-sm mr-2" style="width:auto;">
                        <option value="">🏢 Todas las sedes</option>
                        <?php foreach ($sedes as $s): ?>
                        <option value="<?= $s['sed_sede_id'] ?>" <?= $sedeActiva == $s['sed_sede_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['sed_nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                    <a href="<?= url('natacion', 'alumno', 'crear') ?>" class="btn" style="background: <?= $moduloColor ?>; color: white;">
                        <i class="fas fa-user-plus mr-1"></i> Nuevo Alumno
                    </a>
                    <a href="<?= url('natacion', 'horario', 'index') ?>" class="btn btn-outline-secondary ml-1">
                        <i class="fas fa-clock mr-1"></i> Horarios
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- KPI Cards -->
        <div class="row">
            <?php foreach ($kpis as $kpi): ?>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon" style="background: <?= $kpi['color'] ?>20; color: <?= $kpi['color'] ?>;">
                                <i class="<?= $kpi['icon'] ?>"></i>
                            </div>
                            <?php if (!empty($kpi['trend'])): ?>
                            <span class="kpi-trend <?= $kpi['trend_type'] ?> ml-auto">
                                <i class="fas fa-arrow-<?= $kpi['trend_type'] ?>"></i>
                                <?= $kpi['trend'] ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="kpi-value"><?= $kpi['value'] ?></div>
                        <div class="kpi-label"><?= $kpi['label'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Clases del Día + Alumnos por Nivel -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title"><i class="fas fa-water text-info mr-2"></i>Clases del Día</h3>
                        <div class="card-tools"><span class="badge badge-info"><?= count($clases_hoy) ?> clases</span></div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($clases_hoy)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-day fa-2x mb-2 opacity-50"></i>
                            <p>No hay clases programadas para hoy</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Grupo</th>
                                        <th>Piscina / Carril</th>
                                        <th>Nivel</th>
                                        <th>Instructor</th>
                                        <th class="text-center">Alumnos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clases_hoy as $clase): ?>
                                    <tr>
                                        <td><strong><?= substr($clase['ngh_hora_inicio'], 0, 5) ?></strong> - <?= substr($clase['ngh_hora_fin'], 0, 5) ?></td>
                                        <td>
                                            <?php if (!empty($clase['ngr_color'])): ?>
                                            <span class="badge" style="background:<?= htmlspecialchars($clase['ngr_color']) ?>;color:white;">&nbsp;</span>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($clase['ngr_nombre']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($clase['piscina'] ?? '—') ?><?= $clase['carril'] ? ' - Carril ' . $clase['carril'] : '' ?></td>
                                        <td>
                                            <span class="badge" style="background:<?= htmlspecialchars($clase['nivel_color'] ?? '#6c757d') ?>;color:white;">
                                                <?= htmlspecialchars($clase['nivel'] ?? 'Sin nivel') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($clase['instructor'] ?? '—') ?></td>
                                        <td class="text-center">
                                            <?php
                                            $cupoActual = (int)($clase['ngr_cupo_actual'] ?? 0);
                                            $cupoMax    = (int)($clase['ngr_cupo_maximo'] ?? 0);
                                            $pct        = $cupoMax > 0 ? round($cupoActual / $cupoMax * 100) : 0;
                                            $badgeClass = $pct >= 90 ? 'badge-danger' : ($pct >= 70 ? 'badge-warning' : 'badge-success');
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $cupoActual ?>/<?= $cupoMax ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Gráfico de Asistencia -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title"><i class="fas fa-chart-bar text-primary mr-2"></i>Asistencia - Últimos 7 días</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height:250px;">
                            <canvas id="chartAsistencia"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Alumnos por Nivel -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title"><i class="fas fa-layer-group mr-2" style="color:<?= $moduloColor ?>"></i>Alumnos por Nivel</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($alumnos_nivel)): ?>
                        <p class="text-muted text-center">Sin datos de niveles</p>
                        <?php else: ?>
                        <?php foreach ($alumnos_nivel as $nv): ?>
                        <?php $pctNv = $totalAlumnos > 0 ? round(((int)$nv['total']) / $totalAlumnos * 100) : 0; ?>
                        <div class="progress-group mb-2">
                            <span class="progress-text"><?= htmlspecialchars($nv['nnv_nombre']) ?></span>
                            <span class="float-right"><b><?= (int)$nv['total'] ?></b>/<?= $totalAlumnos ?></span>
                            <div class="progress progress-sm">
                                <div class="progress-bar" style="width:<?= $pctNv ?>%;background:<?= htmlspecialchars($nv['nnv_color'] ?? $moduloColor) ?>"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Estado Piscinas -->
                <div class="card bg-gradient-info">
                    <div class="card-body">
                        <h5 class="text-white"><i class="fas fa-swimming-pool mr-1"></i> Estado Piscinas</h5>
                        <?php if (empty($piscinasData)): ?>
                        <p class="text-white-50">No hay piscinas registradas</p>
                        <?php else: ?>
                        <div class="row mt-3">
                            <?php foreach ($piscinasData as $pi): ?>
                            <div class="col-6 mb-2">
                                <div class="text-white-50 small"><?= htmlspecialchars($pi['npi_nombre']) ?></div>
                                <div class="text-white font-weight-bold">
                                    <?php if ($pi['npi_activo']): ?>
                                    <i class="fas fa-check-circle text-success"></i> Operativa
                                    <?php else: ?>
                                    <i class="fas fa-times-circle text-danger"></i> Inactiva
                                    <?php endif; ?>
                                </div>
                                <div class="text-white-50 small">
                                    <?= $pi['npi_temperatura'] ? $pi['npi_temperatura'] . '°C' : '' ?>
                                    <?= $pi['npi_tipo'] ? ' - ' . htmlspecialchars($pi['npi_tipo']) : '' ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Últimas Inscripciones -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title"><i class="fas fa-clipboard-list mr-2 text-warning"></i>Últimas Inscripciones</h3>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($ultimas)): ?>
                        <div class="text-center py-3 text-muted"><small>Sin inscripciones recientes</small></div>
                        <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($ultimas as $ins): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <div>
                                    <strong class="d-block" style="font-size:.85rem"><?= htmlspecialchars($ins['alumno']) ?></strong>
                                    <small class="text-muted"><?= htmlspecialchars($ins['grupo']) ?></small>
                                </div>
                                <div class="text-right">
                                    <?php
                                    $estBadge = ['ACTIVA'=>'success','CANCELADA'=>'danger','SUSPENDIDA'=>'warning','COMPLETADA'=>'info'];
                                    $bc = $estBadge[$ins['nis_estado']] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?= $bc ?>"><?= $ins['nis_estado'] ?></span>
                                    <br><small class="text-muted"><?= date('d/m', strtotime($ins['nis_fecha_inscripcion'])) ?></small>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<script>
// Gráfico de Asistencia
var ctx = document.getElementById('chartAsistencia');
if (ctx) {
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?= $chartLabels ?>,
            datasets: [
                { label: 'Presentes', data: <?= $chartPresente ?>, backgroundColor: '#22C55E', borderRadius: 4 },
                { label: 'Ausentes',  data: <?= $chartAusente ?>,  backgroundColor: '#EF4444', borderRadius: 4 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

// Cambio de sede - filtro global
$('#sedeFilterDash').on('change', function() {
    var sedeId = $(this).val();
    $.post('<?= url('natacion', 'sede', 'seleccionar') ?>', { sede_id: sedeId }, function() { location.reload(); }, 'json');
});
</script>
<?php $scripts = ob_get_clean(); ?>
