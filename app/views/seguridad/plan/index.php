<?php
/**
 * DigiSports Seguridad - Lista de Planes
 */

$planes = $planes ?? [];
$total = $total ?? 0;
?>

<!-- Content Header -->

<?php $moduloColor = $moduloColor ?? '#6366F1'; ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-crown mr-2" style="color: <?= $moduloColor ?>"></i>
                    Planes de Suscripción
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('seguridad', 'plan', 'crear') ?>" class="btn" style="background: <?= $moduloColor ?>; color: white;">
                        <i class="fas fa-plus mr-1"></i> Nuevo Plan
                    </a>
                    <a href="<?= url('seguridad', 'plan', 'comparativa') ?>" class="btn btn-outline-info">
                        <i class="fas fa-columns mr-1"></i> Comparativa
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <?php if (empty($planes)): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-crown fa-4x text-muted mb-3"></i>
                <h5>No hay planes configurados</h5>
                <p class="text-muted">Cree planes de suscripción para sus clientes</p>
                <a href="<?= url('seguridad', 'plan', 'crear') ?>" class="btn" style="background: <?= $moduloColor ?>; color: white;">
                    <i class="fas fa-plus mr-1"></i> Crear Primer Plan
                </a>
            </div>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($planes as $plan): ?>
    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card h-100 <?= !empty($plan['destacado']) ? 'card-primary card-outline' : '' ?>">
            <?php if (!empty($plan['destacado'])): ?>
            <div class="ribbon-wrapper ribbon-lg">
                <div class="ribbon bg-primary">Popular</div>
            </div>
            <?php endif; ?>
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="kpi-icon" style="background: <?= $plan['color'] ?? '#6c757d' ?>20; color: <?= $plan['color'] ?? '#6c757d' ?>;">
                        <i class="<?= $plan['icono'] ?? 'fas fa-crown' ?> fa-2x"></i>
                    </div>
                </div>
                <h3 class="card-title mb-1" style="color: <?= $plan['color'] ?? '#6c757d' ?>; font-size: 1.2rem;">
                    <?= htmlspecialchars($plan['nombre']) ?>
                </h3>
                <div class="mb-2">
                    <span class="kpi-value" style="font-size:2rem; color: <?= $plan['color'] ?? '#6c757d' ?>;">
                        $<?= number_format($plan['precio_mensual'], 2) ?>
                    </span>
                    <span class="text-muted">/ mes</span>
                </div>
                <p class="text-muted small mb-2"><?= htmlspecialchars($plan['descripcion'] ?? '') ?></p>
                <ul class="list-unstyled text-left mb-2">
                    <li class="mb-1">
                        <i class="fas fa-users text-primary mr-2"></i>
                        <strong><?= isset($plan['usuarios_permitidos']) ? $plan['usuarios_permitidos'] : 'N/D' ?></strong> usuarios
                    </li>
                    <li class="mb-1">
                        <i class="fas fa-puzzle-piece text-success mr-2"></i>
                        <strong><?= $plan['modulos_count'] ?? 'Ilimitados' ?></strong> módulos
                    </li>
                    <li class="mb-1">
                        <i class="fas fa-hdd text-info mr-2"></i>
                        <strong><?= $plan['almacenamiento_gb'] ?? 1 ?> GB</strong> almacenamiento
                    </li>
                    <li class="mb-1">
                        <i class="fas fa-headset text-warning mr-2"></i>
                        Soporte: <strong><?= ucfirst($plan['nivel_soporte'] ?? 'básico') ?></strong>
                    </li>
                </ul>
                <?php if (!empty($plan['caracteristicas'])): ?>
                <ul class="list-unstyled text-left small mb-2">
                    <?php 
                    $caracteristicas = is_string($plan['caracteristicas']) ? json_decode($plan['caracteristicas'], true) : $plan['caracteristicas'];
                    foreach ($caracteristicas ?? [] as $c): 
                    ?>
                    <li class="mb-1">
                        <i class="fas fa-check text-success mr-2"></i>
                        <?= htmlspecialchars($c) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <div class="row text-center mb-2">
                    <div class="col-6">
                        <h5 class="mb-0"><?= $plan['tenants_count'] ?? 0 ?></h5>
                        <small class="text-muted">Suscriptores</small>
                    </div>
                    <div class="col-6">
                        <h5 class="mb-0">$<?= number_format(($plan['tenants_count'] ?? 0) * $plan['precio_mensual'], 2) ?></h5>
                        <small class="text-muted">Ingreso/mes</small>
                    </div>
                </div>
                <a href="<?= url('seguridad', 'plan', 'editar', ['id' => $plan['plan_id']]) ?>" class="btn btn-outline-primary btn-block mb-1">
                    <i class="fas fa-edit mr-1"></i> Editar
                </a>
                <a href="<?= url('seguridad', 'plan', 'eliminar', ['id' => $plan['plan_id']]) ?>" class="btn btn-outline-danger btn-block" onclick="return confirm('¿Eliminar este plan?')">
                    <i class="fas fa-trash mr-1"></i> Eliminar
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
    </div>
</section>

<style>
.ribbon-wrapper.ribbon-lg {
    height: 70px;
    width: 70px;
}
.ribbon-wrapper.ribbon-lg .ribbon {
    right: 0px;
    top: 12px;
    width: 90px;
}
</style>
