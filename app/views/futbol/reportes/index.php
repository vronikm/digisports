<?php
/**
 * DigiSports Fútbol - Reportes
 * @vars $resumen, $csrf_token, $modulo_actual
 */
$stats       = $stats ?? [];
$moduloColor = $modulo_actual['color'] ?? '#22C55E';

$reportes = [
    [
        'tipo'  => 'inscripciones',
        'icon'  => 'fas fa-user-plus',
        'color' => '#3B82F6',
        'titulo'=> 'Reporte de Inscripciones',
        'desc'  => 'Listado de inscripciones por período, categoría y grupo. Incluye alumnos nuevos y renovaciones.',
    ],
    [
        'tipo'  => 'pagos',
        'icon'  => 'fas fa-dollar-sign',
        'color' => '#22C55E',
        'titulo'=> 'Reporte de Pagos y Cobros',
        'desc'  => 'Detalle de ingresos por cobros realizados, pendientes y métodos de pago utilizados.',
    ],
    [
        'tipo'  => 'asistencia',
        'icon'  => 'fas fa-calendar-check',
        'color' => '#0EA5E9',
        'titulo'=> 'Reporte de Asistencia',
        'desc'  => 'Porcentaje de asistencia por grupo, alumno y rango de fechas. Incluye faltas justificadas.',
    ],
    [
        'tipo'  => 'evaluaciones',
        'icon'  => 'fas fa-star',
        'color' => '#F59E0B',
        'titulo'=> 'Reporte de Evaluaciones',
        'desc'  => 'Resultados de evaluaciones por categoría, habilidades y progreso de jugadores.',
    ],
    [
        'tipo'  => 'egresos',
        'icon'  => 'fas fa-file-invoice-dollar',
        'color' => '#EF4444',
        'titulo'=> 'Reporte de Egresos',
        'desc'  => 'Detalle de gastos y egresos del módulo: sueldos, insumos, mantenimiento, etc.',
    ],
    [
        'tipo'  => 'morosidad',
        'icon'  => 'fas fa-exclamation-triangle',
        'color' => '#DC2626',
        'titulo'=> 'Reporte de Morosidad',
        'desc'  => 'Alumnos con pagos vencidos o pendientes, días de mora y montos adeudados.',
    ],
    [
        'tipo'  => 'categorias',
        'icon'  => 'fas fa-layer-group',
        'color' => '#8B5CF6',
        'titulo'=> 'Reporte por Categoría',
        'desc'  => 'Resumen estadístico por cada categoría: alumnos, ocupación, ingresos y asistencia.',
    ],
    [
        'tipo'  => 'torneos',
        'icon'  => 'fas fa-trophy',
        'color' => '#F97316',
        'titulo'=> 'Reporte de Torneos',
        'desc'  => 'Historial de torneos, participantes, resultados y estadísticas de competencia.',
    ],
];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-chart-pie mr-2" style="color:<?= $moduloColor ?>"></i>Reportes</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Reportes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtro global de fechas -->
        <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
            <div class="card-body py-2">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small">Fecha Desde</label>
                        <input type="date" id="fechaDesde" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="small">Fecha Hasta</label>
                        <input type="date" id="fechaHasta" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Seleccione un rango de fechas global. Cada reporte utilizará estas fechas al generarse.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen rápido -->
        <?php if (!empty($stats)): ?>
        <div class="row mb-3">
            <div class="col-lg"><div class="small-box bg-primary"><div class="inner"><h4><?= $stats['total_alumnos'] ?? 0 ?></h4><p>Jugadores</p></div><div class="icon"><i class="fas fa-futbol"></i></div></div></div>
            <div class="col-lg"><div class="small-box bg-success"><div class="inner"><h4><?= $stats['inscripciones_activas'] ?? 0 ?></h4><p>Inscripciones Activas</p></div><div class="icon"><i class="fas fa-clipboard-list"></i></div></div></div>
            <div class="col-lg"><div class="small-box bg-warning"><div class="inner"><h4>$<?= number_format($stats['ingresos_mes'] ?? 0, 2) ?></h4><p>Ingresos del Mes</p></div><div class="icon"><i class="fas fa-dollar-sign"></i></div></div></div>
            <div class="col-lg"><div class="small-box bg-danger"><div class="inner"><h4><?= $stats['pagos_pendientes_count'] ?? 0 ?></h4><p>Pagos Pendientes ($<?= number_format($stats['pagos_pendientes_monto'] ?? 0, 2) ?>)</p></div><div class="icon"><i class="fas fa-money-bill-wave"></i></div></div></div>
        </div>
        <?php endif; ?>

        <!-- Tarjetas de reportes -->
        <div class="row">
            <?php foreach ($reportes as $r): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:60px;height:60px;background:<?= $r['color'] ?>20;">
                                <i class="<?= $r['icon'] ?> fa-2x" style="color:<?= $r['color'] ?>"></i>
                            </span>
                        </div>
                        <h6 class="font-weight-bold"><?= $r['titulo'] ?></h6>
                        <p class="text-muted small mb-3"><?= $r['desc'] ?></p>
                    </div>
                    <div class="card-footer bg-white text-center py-2">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="generarReporte('<?= $r['tipo'] ?>', 'vista')" title="Ver en pantalla">
                                <i class="fas fa-eye mr-1"></i>Vista
                            </button>
                            <button class="btn btn-outline-danger" onclick="generarReporte('<?= $r['tipo'] ?>', 'pdf')" title="Descargar PDF">
                                <i class="fas fa-file-pdf mr-1"></i>PDF
                            </button>
                            <button class="btn btn-outline-success" onclick="generarReporte('<?= $r['tipo'] ?>', 'excel')" title="Descargar Excel">
                                <i class="fas fa-file-excel mr-1"></i>Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Contenedor de resultado de reportes -->
        <div id="reporteResultado" class="card d-none">
            <div class="card-header py-2" style="background:<?= $moduloColor ?>;color:white;">
                <h3 class="card-title" id="reporteTitulo"><i class="fas fa-chart-bar mr-2"></i>Resultado del Reporte</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-white" onclick="cerrarReporte()"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="card-body" id="reporteContenido">
                <!-- El contenido AJAX se carga aquí -->
            </div>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<script>
function generarReporte(tipo, formato) {
    var desde = document.getElementById('fechaDesde').value;
    var hasta = document.getElementById('fechaHasta').value;

    if (!desde || !hasta) {
        Swal.fire('Fechas requeridas', 'Seleccione el rango de fechas para el reporte.', 'info');
        return;
    }

    // Mapear tipo de reporte al método del controlador
    var metodoMap = {
        'inscripciones': 'inscripciones',
        'pagos':         'financiero',
        'asistencia':    'asistencia',
        'evaluaciones':  'financiero',
        'egresos':       'financiero',
        'morosidad':     'financiero',
        'categorias':    'inscripciones',
        'torneos':       'inscripciones'
    };
    var metodo = metodoMap[tipo] || 'financiero';

    var url = '<?= url('futbol', 'reporte', '') ?>'.replace(/&$/, '') + '&action=' + metodo + '&tipo=' + tipo + '&formato=' + formato + '&desde=' + desde + '&hasta=' + hasta;

    // Construir URL según método real
    if (metodo === 'financiero') {
        url = '<?= url('futbol', 'reporte', 'financiero') ?>&anio=' + desde.substring(0, 4);
    } else if (metodo === 'asistencia') {
        url = '<?= url('futbol', 'reporte', 'asistencia') ?>&mes=' + desde.substring(0, 7);
    } else if (metodo === 'inscripciones') {
        url = '<?= url('futbol', 'reporte', 'inscripciones') ?>';
    }

    if (formato === 'pdf' || formato === 'excel') {
        // Descarga directa (pendiente de implementar en controlador)
        Swal.fire('Próximamente', 'La descarga en ' + formato.toUpperCase() + ' está en desarrollo.', 'info');
        return;
    }

    // Vista en pantalla via AJAX
    Swal.fire({ title: 'Generando reporte...', html: '<i class="fas fa-spinner fa-spin fa-2x"></i>', allowOutsideClick: false, showConfirmButton: false });

    $.get(url, function(res) {
        Swal.close();
        if (typeof res === 'object' && !res.success) {
            Swal.fire('Error', res.message || 'No se pudo generar el reporte.', 'error');
            return;
        }
        var html = '';
        if (typeof res === 'object' && res.data) {
            html = '<pre class="p-3 bg-light">' + JSON.stringify(res.data, null, 2) + '</pre>';
        } else if (typeof res === 'string') {
            html = res;
        } else {
            html = '<p class="text-muted">Sin datos para mostrar.</p>';
        }
        $('#reporteResultado').removeClass('d-none');
        $('#reporteContenido').html(html);
        $('html, body').animate({ scrollTop: $('#reporteResultado').offset().top - 80 }, 300);
    }).fail(function() {
        Swal.close();
        Swal.fire('Error', 'Error de conexión al generar el reporte.', 'error');
    });
}

function cerrarReporte() {
    $('#reporteResultado').addClass('d-none');
    $('#reporteContenido').html('');
}
</script>
<?php $scripts = ob_get_clean(); ?>
