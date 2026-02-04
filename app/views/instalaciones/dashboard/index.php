<?php
/**
 * Dashboard de Instalaciones - Branding dinámico
 * Variables disponibles: $modulo_actual (nombre, color, icono)
 */
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="h3 fw-bold">
                <i class="<?= $modulo_actual['icono'] ?>" style="color: <?= $modulo_actual['color'] ?>;"></i>
                <?= htmlspecialchars($modulo_actual['nombre']) ?>
            </h2>
        </div>
    </div>
    <div class="alert alert-info">
        Bienvenido al módulo de <b><?= htmlspecialchars($modulo_actual['nombre']) ?></b>.
    </div>

    <!-- Menú lateral personalizado -->
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <?php if (!empty($menu_items)): ?>
                    <?php foreach ($menu_items as $item): ?>
                        <a href="<?= $item['url'] ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="<?= $item['icon'] ?> mr-2" style="color: <?= $item['color'] ?>;"></i>
                            <span><?= htmlspecialchars($item['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="text-muted">No hay accesos configurados.</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-9">
            <!-- Aquí puedes agregar KPIs, accesos rápidos, etc. -->
        </div>
    </div>
</div>
