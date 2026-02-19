<?php
/**
 * Vista de Reporte de Asistencia - Módulo Fútbol
 * @vars $resumen, $desde, $hasta, $modulo_actual
 */
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
$resumen = $resumen ?? [];
$desde = $desde ?? date('Y-m-01');
$hasta = $hasta ?? date('Y-m-d');

$totalGeneral = array_sum($resumen);
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-futbol" style="color: <?= $moduloColor ?>"></i>
                    Reporte de Asistencia
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'asistencia', 'index') ?>">Asistencia</a></li>
                    <li class="breadcrumb-item active">Reporte</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Filtros de rango -->
        <div class="card">
            <div class="card-header" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-filter"></i> Rango de Fechas</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= url('futbol', 'asistencia', 'reporte') ?>" class="form-inline">
                    <div class="form-group mr-3">
                        <label class="mr-2">Desde:</label>
                        <input type="date" name="desde" class="form-control form-control-sm" value="<?= htmlspecialchars($desde) ?>">
                    </div>
                    <div class="form-group mr-3">
                        <label class="mr-2">Hasta:</label>
                        <input type="date" name="hasta" class="form-control form-control-sm" value="<?= htmlspecialchars($hasta) ?>">
                    </div>
                    <button type="submit" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-search mr-1"></i>Filtrar
                    </button>
                </form>
            </div>
        </div>

        <!-- Resumen de asistencia -->
        <div class="row">
            <?php
            $estadosConfig = [
                'PRESENTE' => ['color' => '#28a745', 'icon' => 'fas fa-check-circle', 'label' => 'Presentes'],
                'AUSENTE' => ['color' => '#dc3545', 'icon' => 'fas fa-times-circle', 'label' => 'Ausentes'],
                'TARDANZA' => ['color' => '#ffc107', 'icon' => 'fas fa-clock', 'label' => 'Tardanzas'],
                'JUSTIFICADO' => ['color' => '#17a2b8', 'icon' => 'fas fa-file-medical', 'label' => 'Justificados'],
            ];
            foreach ($estadosConfig as $estado => $cfg):
                $count = (int)($resumen[$estado] ?? 0);
                $porcentaje = $totalGeneral > 0 ? round(($count / $totalGeneral) * 100, 1) : 0;
            ?>
            <div class="col-lg-3 col-6">
                <div class="small-box" style="background:<?= $cfg['color'] ?>;color:white;">
                    <div class="inner">
                        <h3><?= $count ?></h3>
                        <p><?= $cfg['label'] ?> (<?= $porcentaje ?>%)</p>
                    </div>
                    <div class="icon"><i class="<?= $cfg['icon'] ?>"></i></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Gráfico -->
        <?php if ($totalGeneral > 0): ?>
        <div class="card">
            <div class="card-header" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Distribución de Asistencia</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mx-auto" style="max-height:300px;">
                        <canvas id="chartAsistencia"></canvas>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <strong>Total de registros:</strong> <?= $totalGeneral ?> 
                    <span class="text-muted ml-3">Período: <?= date('d/m/Y', strtotime($desde)) ?> — <?= date('d/m/Y', strtotime($hasta)) ?></span>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-chart-pie fa-3x mb-3 opacity-50"></i>
                <p>No hay registros de asistencia en el período seleccionado.</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="<?= url('futbol', 'asistencia', 'index') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Volver a Asistencia
            </a>
        </div>

    </div>
</section>

<?php ob_start(); ?>
<script>
<?php if ($totalGeneral > 0): ?>
var ctx = document.getElementById('chartAsistencia');
if (ctx) {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_map(function($c) { return $c['label']; }, $estadosConfig)) ?>,
            datasets: [{
                data: <?= json_encode(array_map(function($e) use ($resumen) { return (int)($resumen[$e] ?? 0); }, array_keys($estadosConfig))) ?>,
                backgroundColor: <?= json_encode(array_map(function($c) { return $c['color']; }, $estadosConfig)) ?>,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
}
<?php endif; ?>
</script>
<?php $scripts = ob_get_clean(); ?>
