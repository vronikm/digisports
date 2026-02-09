<?php
/**
 * Detalle de Cancha — DigiSports Arena
 * @var array $cancha
 * @var array $tarifas
 * @var array $reservas
 * @var array $mantenimientos
 * @var array $kpis
 */
$diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$estadoColors = [
    'ACTIVO'   => 'success',
    'INACTIVO' => 'warning',
    'ELIMINADA'=> 'danger'
];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-th-large text-primary"></i>
                    <?php echo htmlspecialchars($cancha['nombre']); ?>
                    <span class="badge badge-<?php echo $estadoColors[$cancha['estado']] ?? 'secondary'; ?> ml-2">
                        <?php echo $cancha['estado']; ?>
                    </span>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo url('instalaciones', 'dashboard', 'index'); ?>">Arena</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url('instalaciones', 'cancha', 'index'); ?>">Canchas</a></li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- KPIs -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo (int)($kpis['total_reservas'] ?? 0); ?></h3>
                        <p>Total Reservas</p>
                    </div>
                    <div class="icon"><i class="fas fa-calendar-check"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo (int)($kpis['confirmadas'] ?? 0); ?></h3>
                        <p>Confirmadas</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo (int)($kpis['hoy'] ?? 0); ?></h3>
                        <p>Reservas Hoy</p>
                    </div>
                    <div class="icon"><i class="fas fa-clock"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>$<?php echo number_format($kpis['ingresos_total'] ?? 0, 0); ?></h3>
                        <p>Ingresos Totales</p>
                    </div>
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Info de la cancha -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Información</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Instalación</strong>
                                <span><?php echo htmlspecialchars($cancha['instalacion_nombre']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Tipo</strong>
                                <span class="badge badge-info"><?php echo ucfirst($cancha['tipo']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Capacidad</strong>
                                <span><?php echo (int)$cancha['capacidad_maxima']; ?> personas</span>
                            </li>
                            <?php if (!empty($cancha['ancho']) && !empty($cancha['largo'])): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Dimensiones</strong>
                                <span><?php echo $cancha['ancho']; ?>m × <?php echo $cancha['largo']; ?>m</span>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($cancha['descripcion'])): ?>
                            <li class="list-group-item">
                                <strong>Descripción</strong><br>
                                <small class="text-muted"><?php echo nl2br(htmlspecialchars($cancha['descripcion'])); ?></small>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo url('instalaciones', 'cancha', 'editar', ['id' => $cancha['cancha_id']]); ?>" class="btn btn-primary btn-block btn-sm">
                            <i class="fas fa-edit"></i> Editar Cancha
                        </a>
                    </div>
                </div>

                <!-- Mantenimientos activos -->
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-wrench"></i> Mantenimientos Activos</h3>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($mantenimientos)): ?>
                            <p class="text-muted text-center py-3 mb-0">Sin mantenimientos programados</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($mantenimientos as $mnt): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?php echo ucfirst(str_replace('_', ' ', $mnt['tipo'])); ?></strong><br>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($mnt['fecha_inicio'])); ?> → <?php echo date('d/m/Y', strtotime($mnt['fecha_fin'])); ?>
                                            </small>
                                        </div>
                                        <?php
                                        $mntColors = ['PROGRAMADO' => 'primary', 'EN_PROGRESO' => 'warning'];
                                        ?>
                                        <span class="badge badge-<?php echo $mntColors[$mnt['estado']] ?? 'secondary'; ?>">
                                            <?php echo str_replace('_', ' ', $mnt['estado']); ?>
                                        </span>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tarifas + Reservas -->
            <div class="col-md-8">
                <!-- Tarifas -->
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-dollar-sign"></i> Tarifas Activas</h3>
                        <div class="card-tools">
                            <a href="<?php echo url('instalaciones', 'cancha', 'tarifas', ['id' => $cancha['cancha_id']]); ?>" class="btn btn-tool" title="Gestionar tarifas">
                                <i class="fas fa-cog"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <?php if (empty($tarifas)): ?>
                            <p class="text-muted text-center py-3 mb-0">Sin tarifas configuradas</p>
                        <?php else: ?>
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Día</th>
                                        <th>Horario</th>
                                        <th class="text-right">Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tarifas as $tarifa): ?>
                                    <tr>
                                        <td><?php echo $diasSemana[$tarifa['dia_semana']] ?? 'N/A'; ?></td>
                                        <td>
                                            <?php echo date('H:i', strtotime($tarifa['hora_inicio'])); ?> - 
                                            <?php echo date('H:i', strtotime($tarifa['hora_fin'])); ?>
                                        </td>
                                        <td class="text-right font-weight-bold text-success">
                                            $<?php echo number_format($tarifa['precio'], 2); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Últimas Reservas -->
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calendar-check"></i> Últimas Reservas</h3>
                    </div>
                    <div class="card-body table-responsive p-0" style="max-height: 350px;">
                        <?php if (empty($reservas)): ?>
                            <p class="text-muted text-center py-3 mb-0">Sin reservas registradas</p>
                        <?php else: ?>
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Horario</th>
                                        <th>Cliente</th>
                                        <th>Estado</th>
                                        <th>Pago</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservas as $r): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($r['fecha_reserva'])); ?></td>
                                        <td>
                                            <?php echo date('H:i', strtotime($r['hora_inicio'])); ?> - 
                                            <?php echo date('H:i', strtotime($r['hora_fin'])); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($r['cliente_nombre'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                            $rColors = [
                                                'PENDIENTE'  => 'badge-warning',
                                                'CONFIRMADA' => 'badge-success',
                                                'CANCELADA'  => 'badge-danger',
                                                'COMPLETADA' => 'badge-info'
                                            ];
                                            ?>
                                            <span class="badge <?php echo $rColors[$r['estado']] ?? 'badge-secondary'; ?>">
                                                <?php echo $r['estado']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $pColors = [
                                                'PENDIENTE' => 'badge-warning',
                                                'PARCIAL'   => 'badge-info',
                                                'PAGADO'    => 'badge-success'
                                            ];
                                            $ep = $r['estado_pago'] ?? 'PENDIENTE';
                                            ?>
                                            <span class="badge <?php echo $pColors[$ep] ?? 'badge-secondary'; ?>">
                                                <?php echo $ep; ?>
                                            </span>
                                        </td>
                                        <td class="text-right">$<?php echo number_format($r['precio_total'] ?? 0, 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="<?php echo url('instalaciones', 'cancha', 'index'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
                <a href="<?php echo url('instalaciones', 'cancha', 'tarifas', ['id' => $cancha['cancha_id']]); ?>" class="btn btn-success ml-2">
                    <i class="fas fa-dollar-sign"></i> Gestionar Tarifas
                </a>
                <a href="<?php echo url('instalaciones', 'calendario', 'index', ['instalacion_id' => $cancha['instalacion_id']]); ?>" class="btn btn-info ml-2">
                    <i class="fas fa-calendar-week"></i> Ver Calendario
                </a>
            </div>
        </div>

    </div>
</section>
