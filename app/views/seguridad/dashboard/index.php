<?php
/**
 * DigiSports Seguridad - Vista Dashboard
 */

$kpis = $kpis ?? [];
$stats = $stats ?? [];
$recentActivity = $recentActivity ?? [];
$securityAlerts = $securityAlerts ?? [];
$moduloColor = $modulo_actual['color'] ?? '#6366F1';
$moduloIcono = $modulo_actual['icono'] ?? 'fas fa-shield-alt';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?> mr-2" style="color: <?= $moduloColor ?>"></i>
                    <?= $modulo_actual['nombre_personalizado'] ?? $modulo_actual['nombre'] ?? 'Panel de Seguridad' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('seguridad', 'usuario', 'crear') ?>" class="btn" style="background: <?= $moduloColor ?>; color: white;">
                        <i class="fas fa-user-plus mr-1"></i> Nuevo Usuario
                    </a>
                    <a href="<?= url('seguridad', 'tenant', 'crear') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-building mr-1"></i> Nuevo Tenant
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
                        </div>
                        <div class="kpi-value"><?= $kpi['value'] ?></div>
                        <div class="kpi-label"><?= $kpi['label'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="row">
            <!-- Alertas de Seguridad -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                            Alertas de Seguridad
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($securityAlerts as $alert): ?>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-<?= $alert['type'] ?> mr-3">
                                        <i class="<?= $alert['icon'] ?>"></i>
                                    </span>
                                    <span><?= $alert['message'] ?></span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Accesos Rápidos -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-bolt text-warning mr-2"></i>
                            Accesos Rápidos
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-2">
                                <a href="<?= url('seguridad', 'usuario', 'index') ?>" class="btn btn-outline-primary btn-block btn-sm">
                                    <i class="fas fa-users"></i> Usuarios
                                </a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="<?= url('seguridad', 'rol', 'index') ?>" class="btn btn-outline-success btn-block btn-sm">
                                    <i class="fas fa-user-shield"></i> Roles
                                </a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="<?= url('seguridad', 'tenant', 'index') ?>" class="btn btn-outline-info btn-block btn-sm">
                                    <i class="fas fa-building"></i> Tenants
                                </a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="<?= url('seguridad', 'modulo', 'index') ?>" class="btn btn-outline-warning btn-block btn-sm">
                                    <i class="fas fa-puzzle-piece"></i> Módulos
                                </a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="<?= url('seguridad', 'auditoria', 'accesos') ?>" class="btn btn-outline-secondary btn-block btn-sm">
                                    <i class="fas fa-history"></i> Logs
                                </a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="<?= url('seguridad', 'modulo', 'iconos') ?>" class="btn btn-outline-dark btn-block btn-sm">
                                    <i class="fas fa-icons"></i> Iconos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actividad Reciente -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-history mr-2" style="color: <?= $moduloColor ?>"></i>
                            Actividad Reciente
                        </h3>
                        <div class="card-tools">
                            <a href="<?= url('seguridad', 'auditoria', 'accesos') ?>" class="btn btn-sm btn-outline-secondary">
                                Ver todo <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentActivity)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Sin actividad reciente</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($recentActivity as $log): ?>
                                <tr>
                                    <td><small><?= !empty($log['acc_fecha']) ? date('d/m H:i', strtotime($log['acc_fecha'])) : '--' ?></small></td>
                                    <td><?= htmlspecialchars($log['usu_username'] ?? 'Sistema') ?></td>
                                    <td>
                                        <?php 
                                        $tipo = $log['acc_tipo'] ?? 'INFO';
                                        switch ($tipo) {
                                            case 'LOGIN': $badgeClass = 'success'; break;
                                            case 'LOGOUT': $badgeClass = 'info'; break;
                                            case 'LOGIN_FAILED': $badgeClass = 'danger'; break;
                                            default: $badgeClass = 'secondary'; break;
                                        }
                                        ?>
                                        <span class="badge badge-<?= $badgeClass ?>"><?= $tipo ?></span>
                                    </td>
                                    <td><code><?= $log['acc_ip'] ?? '127.0.0.1' ?></code></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Resumen del Sistema -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header border-0 bg-gradient-primary text-white">
                                <h3 class="card-title">
                                    <i class="fas fa-server mr-2"></i>
                                    Estado del Sistema
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>PHP Version</span>
                                    <strong><?= PHP_VERSION ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>MySQL</span>
                                    <strong class="text-success">Conectado</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Sesiones Activas</span>
                                    <strong><?= $stats['usuarios_online'] ?? 0 ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Memoria Usada</span>
                                    <strong><?= round(memory_get_usage() / 1024 / 1024, 2) ?> MB</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header border-0 bg-gradient-success text-white">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-pie mr-2"></i>
                                    Distribución
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tenants Activos</span>
                                    <strong><?= $stats['tenants_activos'] ?? 0 ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Usuarios Totales</span>
                                    <strong><?= $stats['usuarios_activos'] ?? 0 ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Módulos Activos</span>
                                    <strong><?= $stats['modulos_activos'] ?? 0 ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Logins Fallidos Hoy</span>
                                    <strong class="text-danger"><?= $stats['login_fallidos_hoy'] ?? 0 ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
