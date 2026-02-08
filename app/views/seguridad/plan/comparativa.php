<?php
/**
 * DigiSports Seguridad - Comparativa de Planes
 */

$planes = $planes ?? [];
?>

<section class="content pt-3">
    <div class="container-fluid">

<!-- Header Premium -->
<?php
$headerTitle    = 'Comparativa de Planes';
$headerSubtitle = 'Tabla comparativa de características y precios';
$headerIcon     = 'fas fa-columns';
$headerButtons  = [
    ['url' => url('seguridad', 'plan'), 'label' => 'Volver a Planes', 'icon' => 'fas fa-arrow-left', 'solid' => false],
];
include __DIR__ . '/../partials/header.php';
?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th width="200">Característica</th>
                                <?php foreach ($planes as $plan): ?>
                                <th class="text-center <?= ($plan['sus_es_destacado'] ?? 'N') === 'S' ? 'bg-primary' : '' ?>">
                                    <i class="fas fa-crown d-block fa-2x mb-2"></i>
                                    <strong><?= htmlspecialchars($plan['sus_nombre'] ?? '') ?></strong>
                                    <?php if (($plan['sus_es_destacado'] ?? 'N') === 'S'): ?>
                                    <br><span class="badge badge-warning">Popular</span>
                                    <?php endif; ?>
                                </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Precios -->
                            <tr class="bg-light">
                                <td colspan="<?= count($planes) + 1 ?>"><strong><i class="fas fa-dollar-sign mr-2"></i>Precios</strong></td>
                            </tr>
                            <tr>
                                <td>Precio Mensual</td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <h4 class="mb-0" style="color: <?= $plan['sus_color'] ?? '#333' ?>">$<?= number_format($plan['sus_precio_mensual'] ?? 0, 2) ?></h4>
                                    <small class="text-muted">/mes</small>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>Precio Anual</td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <?php if (($plan['sus_precio_anual'] ?? 0) > 0): ?>
                                    <strong>$<?= number_format($plan['sus_precio_anual'], 2) ?></strong>
                                    <br><small class="text-success">Ahorra <?= round((1 - ($plan['sus_precio_anual'] / ($plan['sus_precio_mensual'] * 12))) * 100) ?>%</small>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>Período de Prueba</td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <span class="text-muted">No incluido</span>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            
                            <!-- Límites -->
                            <tr class="bg-light">
                                <td colspan="<?= count($planes) + 1 ?>"><strong><i class="fas fa-sliders-h mr-2"></i>Límites y Recursos</strong></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-users mr-2"></i>Usuarios</td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <strong><?= $plan['sus_usuarios_incluidos'] ?? 5 ?></strong>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><i class="fas fa-hdd mr-2"></i>Almacenamiento</td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <strong><?= $plan['sus_almacenamiento_gb'] ?? 1 ?> GB</strong>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><i class="fas fa-puzzle-piece mr-2"></i>Módulos</td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <strong><?= !empty($plan['modulos_array']) ? count($plan['modulos_array']) : 0 ?></strong>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            
                            <!-- Soporte -->
                            <tr class="bg-light">
                                <td colspan="<?= count($planes) + 1 ?>"><strong><i class="fas fa-headset mr-2"></i>Soporte</strong></td>
                            </tr>
                            <tr>
                                <td>Nivel de Soporte</td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <span class="badge badge-info">Estándar</span>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>Soporte Email</td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <i class="fas fa-check text-success"></i>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>Soporte Telefónico</td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <i class="fas fa-check text-success"></i>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>Soporte 24/7</td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <i class="fas fa-minus text-muted"></i>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            
                            <!-- Características -->
                            <tr class="bg-light">
                                <td colspan="<?= count($planes) + 1 ?>"><strong><i class="fas fa-list-check mr-2"></i>Características</strong></td>
                            </tr>
                            <?php 
                            // Recopilar todas las características únicas
                            $todasCaracteristicas = [];
                            foreach ($planes as $plan) {
                                $caracs = is_string($plan['sus_caracteristicas'] ?? '') ? json_decode($plan['sus_caracteristicas'], true) : ($plan['sus_caracteristicas'] ?? []);
                                foreach ($caracs ?? [] as $c) {
                                    if (!empty($c) && !in_array($c, $todasCaracteristicas)) {
                                        $todasCaracteristicas[] = $c;
                                    }
                                }
                            }
                            foreach ($todasCaracteristicas as $carac):
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($carac) ?></td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <?php 
                                    $caracs = is_string($plan['sus_caracteristicas'] ?? '') ? json_decode($plan['sus_caracteristicas'], true) : ($plan['sus_caracteristicas'] ?? []);
                                    if (in_array($carac, $caracs ?? [])):
                                    ?>
                                    <i class="fas fa-check text-success"></i>
                                    <?php else: ?>
                                    <i class="fas fa-times text-danger"></i>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                            
                            <!-- Suscriptores -->
                            <tr class="bg-light">
                                <td colspan="<?= count($planes) + 1 ?>"><strong><i class="fas fa-chart-bar mr-2"></i>Estadísticas</strong></td>
                            </tr>
                            <tr>
                                <td>Suscriptores Actuales</td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <strong><?= $plan['tenants_activos'] ?? 0 ?></strong>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>Ingreso Mensual</td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <strong>$<?= number_format(($plan['tenants_activos'] ?? 0) * ($plan['sus_precio_mensual'] ?? 0), 2) ?></strong>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                        <tfoot class="bg-secondary text-white">
                            <tr>
                                <td></td>
                                <?php foreach ($planes as $plan): ?>
                                <td class="text-center">
                                    <a href="<?= url('seguridad', 'plan', 'editar', ['id' => $plan['sus_plan_id']]) ?>" class="btn btn-light btn-sm">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </a>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <a href="<?= url('seguridad', 'plan') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver a Planes
            </a>
            <a href="<?= url('seguridad', 'plan', 'crear') ?>" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Nuevo Plan
            </a>
        </div>
    </div>
</section>
