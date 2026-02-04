<?php if (isset($GLOBALS['__ERRORS_DEBUG']) && is_array($GLOBALS['__ERRORS_DEBUG']) && count($GLOBALS['__ERRORS_DEBUG'])): ?>
    <div style="background:#ffdddd;color:#a00;padding:10px;margin:10px 0;border:1px solid #a00;">
        <strong>Errores detectados:</strong><br>
        <ul style="margin:0 0 0 20px;">
        <?php foreach ($GLOBALS['__ERRORS_DEBUG'] as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php
/**
 * DigiSports Seguridad - Gestión de Suscripciones
 */


$porVencer = is_array($porVencer) ? $porVencer : [];
$vencidos = is_array($vencidos) ? $vencidos : [];
$resumen = is_array($resumen) ? $resumen : [
    'activos' => 0,
    'por_vencer' => 0,
    'vencidos' => 0,
    'ingresos_mes' => 0
];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Gestión de Suscripciones
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'dashboard') ?>">Seguridad</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'tenant') ?>">Tenants</a></li>
                    <li class="breadcrumb-item active">Suscripciones</li>
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
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $resumen['activos'] ?? 0 ?></h3>
                        <p>Activos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $resumen['por_vencer'] ?? 0 ?></h3>
                        <p>Por Vencer (7 días)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= $resumen['vencidos'] ?? 0 ?></h3>
                        <p>Vencidos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>$<?= number_format($resumen['ingresos_mes'] ?? 0, 2) ?></h3>
                        <p>Ingresos del Mes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Por Vencer -->
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Suscripciones Por Vencer (próximos 7 días)
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Plan</th>
                            <th>Vencimiento</th>
                            <th>Días Restantes</th>
                            <th>Email</th>
                            <th width="180">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($porVencer)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-success">
                                <i class="fas fa-check-circle mr-2"></i>
                                No hay suscripciones por vencer en los próximos 7 días
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($porVencer as $t): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($t['nombre_comercial'] ?: $t['razon_social']) ?></strong>
                                <br><small class="text-muted">RUC: <?= $t['ruc'] ?></small>
                            </td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($t['plan_nombre']) ?></span></td>
                            <td><?= date('d/m/Y', strtotime($t['fecha_vencimiento'])) ?></td>
                            <td>
                                <span class="badge badge-<?= $t['dias_restantes'] <= 3 ? 'danger' : 'warning' ?>">
                                    <?= $t['dias_restantes'] ?> días
                                </span>
                            </td>
                            <td><a href="mailto:<?= $t['email'] ?>"><?= $t['email'] ?></a></td>
                            <td>
                                <a href="<?= url('seguridad', 'tenant', 'renovar', ['id' => $t['tenant_id']]) ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-sync mr-1"></i> Renovar
                                </a>
                                <a href="<?= url('seguridad', 'tenant', 'notificar', ['id' => $t['tenant_id']]) ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Vencidos -->
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-times-circle mr-2"></i>
                    Suscripciones Vencidas
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Plan</th>
                            <th>Fecha Vencimiento</th>
                            <th>Días Vencido</th>
                            <th>Estado</th>
                            <th width="220">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vencidos)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-success">
                                <i class="fas fa-check-circle mr-2"></i>
                                No hay suscripciones vencidas
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($vencidos as $t): ?>
                        <tr class="<?= $t['estado'] == 'S' ? 'table-warning' : '' ?>">
                            <td>
                                <strong><?= htmlspecialchars($t['nombre_comercial'] ?: $t['razon_social']) ?></strong>
                                <br><small class="text-muted">RUC: <?= $t['ruc'] ?></small>
                            </td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($t['plan_nombre'] ?? '-') ?></span></td>
                            <td><?= date('d/m/Y', strtotime($t['fecha_vencimiento'])) ?></td>
                            <td>
                                <span class="badge badge-danger"><?= abs($t['dias_restantes']) ?> días</span>
                            </td>
                            <td>
                                <?php 
                                $estadoClass = match($t['estado']) {
                                    'A' => 'success',
                                    'S' => 'warning',
                                    'I' => 'secondary',
                                    default => 'secondary'
                                };
                                $estadoText = match($t['estado']) {
                                    'A' => 'Activo',
                                    'S' => 'Suspendido',
                                    'I' => 'Inactivo',
                                    default => 'Desconocido'
                                };
                                ?>
                                <span class="badge badge-<?= $estadoClass ?>"><?= $estadoText ?></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('seguridad', 'tenant', 'renovar', ['id' => $t['tenant_id']]) ?>" class="btn btn-success" title="Renovar">
                                        <i class="fas fa-sync mr-1"></i> Renovar
                                    </a>
                                    <?php if ($t['estado'] == 'A'): ?>
                                    <a href="<?= url('seguridad', 'tenant', 'suspender', ['id' => $t['tenant_id']]) ?>" class="btn btn-warning" title="Suspender">
                                        <i class="fas fa-pause"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= url('seguridad', 'tenant', 'notificar', ['id' => $t['tenant_id']]) ?>" class="btn btn-info" title="Notificar">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Acciones Masivas -->
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cogs mr-2"></i>Acciones Masivas</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <a href="<?= url('seguridad', 'tenant', 'notificarMasivo', ['tipo' => 'por_vencer']) ?>" class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-envelope mr-1"></i>
                            Notificar Por Vencer
                        </a>
                        <?php if (isAdmin()): ?>
                        <a href="<?= urlSimple('seguridad', 'tenant', 'notificarMasivo', ['tipo' => 'por_vencer']) ?>" class="btn btn-outline-warning btn-block mb-2">
                            <i class="fas fa-bug mr-1"></i>
                            [DEBUG] Notificar Por Vencer (URL simple)
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= url('seguridad', 'tenant', 'notificarMasivo', ['tipo' => 'vencidos']) ?>" class="btn btn-danger btn-block mb-2">
                            <i class="fas fa-envelope-open-text mr-1"></i>
                            Notificar Vencidos
                        </a>
                        <?php if (isAdmin()): ?>
                        <a href="<?= urlSimple('seguridad', 'tenant', 'notificarMasivo', ['tipo' => 'vencidos']) ?>" class="btn btn-outline-danger btn-block mb-2">
                            <i class="fas fa-bug mr-1"></i>
                            [DEBUG] Notificar Vencidos (URL simple)
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= url('seguridad', 'tenant', 'suspenderMasivo') ?>" class="btn btn-secondary btn-block mb-2" onclick="return confirm('¿Suspender todos los tenants con suscripción vencida mayor a 15 días?')">
                            <i class="fas fa-pause-circle mr-1"></i>
                            Suspender Vencidos +15d
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
