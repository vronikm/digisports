<?php
/**
 * Vista: Dashboard Principal
 * Panel de control con estadísticas y accesos rápidos a módulos
 * 
 * @var array $stats Estadísticas generales
 * @var array $charts Datos para gráficas
 * @var array $recentActivity Actividad reciente
 * @var array $alerts Alertas del sistema
 * @var array $modules Módulos disponibles
 */
?>

<!-- Tarjetas de estadísticas principales -->
<div class="row">
    <!-- Total Instalaciones -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo number_format($stats['total_instalaciones'] ?? 0) ?></h3>
                <p>Instalaciones</p>
            </div>
            <div class="icon">
                <i class="fas fa-building"></i>
            </div>
            <a href="<?php echo url('instalaciones', 'cancha') ?>" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Reservas del Mes -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?php echo number_format($stats['reservas_mes'] ?? 0) ?></h3>
                <p>Reservas este mes</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <a href="<?php echo url('reservas', 'reserva') ?>" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Clientes -->
    <div class="col-lg-3 col-6">
        <!-- Acceso rápido a módulos -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-th-large mr-2"></i>
                            Acceso Rápido a Módulos
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if (!empty($modules)): ?>
                                <?php foreach ($modules as $mod): ?>
                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                        <a href="<?= !empty($mod['url_base']) ? htmlspecialchars($mod['url_base']) : '#' ?>" class="text-decoration-none">
                                            <div class="card module-card h-100 text-center p-4">
                                                <div class="module-icon" style="color: <?= htmlspecialchars($mod['color']) ?>;">
                                                    <i class="fas <?= htmlspecialchars($mod['icono']) ?>"></i>
                                                </div>
                                                <h5 class="card-title text-dark"><?= htmlspecialchars($mod['nombre']) ?></h5>
                                                <p class="card-text text-muted small"><?= htmlspecialchars($mod['descripcion'] ?? '') ?></p>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php /*
        Sistema de escuelas
        <i class="fas fa-external-link-alt ml-1"></i>
        ...
        <!-- Auditoría de Seguridad -->
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <a href="<?php echo url('seguridad', 'dashboard', 'auditoria') ?>" class="text-decoration-none">
                <div class="card module-card h-100 text-center p-4">
                    <div class="module-icon text-primary">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h5 class="card-title text-dark">Auditoría</h5>
                    <p class="card-text text-muted small">Ver logs y acciones críticas</p>
                </div>
            </a>
        </div>
        */ ?>
    </div>
</div>

<!-- Gráficas y Actividad -->
<div class="row">
    <!-- Gráfica de Ingresos Mensuales -->
    <div class="col-lg-8">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    Ingresos Mensuales
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="chartIngresos" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Reservas por Estado -->
    <div class="col-lg-4">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Estado de Reservas
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="chartEstadoReservas" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Actividad Reciente y Alertas -->
<div class="row">
    <!-- Actividad Reciente -->
    <div class="col-lg-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    Actividad Reciente
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (!empty($recentActivity)): ?>
                        <?php foreach (array_slice($recentActivity, 0, 8) as $activity): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="<?php echo $activity['icono'] ?? 'fas fa-circle' ?> mr-2" 
                                           style="color: <?php echo $activity['color'] ?? '#6c757d' ?>"></i>
                                        <?php echo htmlspecialchars($activity['descripcion'] ?? '') ?>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo timeAgo($activity['fecha'] ?? '') ?>
                                    </small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No hay actividad reciente
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php if (!empty($recentActivity)): ?>
                <div class="card-footer text-center">
                    <a href="<?php echo url('core', 'actividad') ?>">Ver toda la actividad</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Alertas del Sistema -->
    <div class="col-lg-6">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Alertas y Pendientes
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (!empty($alerts)): ?>
                        <?php foreach ($alerts as $alert): ?>
                            <li class="list-group-item">
                                <i class="fas fa-exclamation-circle text-danger mr-2"></i>
                                <?php echo htmlspecialchars($alert['mensaje'] ?? '') ?>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted py-4">
                            <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
                            No hay alertas pendientes
                        </li>
                    <?php endif; ?>
