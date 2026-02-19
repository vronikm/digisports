<?php
/**
 * DigiSports Natación - Configuración del Módulo
 */
$configuraciones = $configuraciones ?? [];
$moduloColor     = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-cogs mr-2" style="color:<?= $moduloColor ?>"></i>Configuración</h1></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="<?= url('natacion', 'configuracion', 'guardar') ?>" id="formConfig">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

            <?php if (empty($configuraciones)): ?>
            <div class="card"><div class="card-body text-center text-muted py-5"><i class="fas fa-cogs fa-3x mb-3 opacity-50"></i><p>No hay configuraciones</p></div></div>
            <?php else: ?>
            <?php foreach ($configuraciones as $cat => $configs): ?>
            <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-folder mr-2"></i><?= htmlspecialchars(ucfirst(strtolower($cat))) ?></h3>
                </div>
                <div class="card-body">
                    <?php foreach ($configs as $c): ?>
                    <div class="form-group row mb-2">
                        <label class="col-md-4 col-form-label">
                            <strong><?= htmlspecialchars($c['nco_clave']) ?></strong>
                            <?php if (!empty($c['nco_descripcion'])): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($c['nco_descripcion']) ?></small>
                            <?php endif; ?>
                        </label>
                        <div class="col-md-8">
                            <input type="text" name="config[<?= $c['nco_config_id'] ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($c['nco_valor'] ?? '') ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="mb-4">
                <button type="submit" class="btn" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-save mr-1"></i>Guardar Configuración</button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</section>
