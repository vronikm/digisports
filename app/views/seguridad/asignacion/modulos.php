<?php
/**
 * DigiSports Seguridad - Asignación de Módulos a Tenant
 */

$tenant = $tenant ?? [];
$modulos = $modulos ?? [];
$asignados = $asignados ?? [];
?>


<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= htmlspecialchars($moduloIcono ?? 'fas fa-shield-alt') ?> mr-2" style="color: <?= htmlspecialchars($moduloColor ?? '#F59E0B') ?>"></i>
                    <?= htmlspecialchars($moduloNombre ?? 'Seguridad') ?> - Asignar Módulos: <?php
                        $nombre = isset($tenant['nombre_comercial']) && $tenant['nombre_comercial'] ? $tenant['nombre_comercial'] : (isset($tenant['razon_social']) && $tenant['razon_social'] ? $tenant['razon_social'] : '');
                        echo htmlspecialchars($nombre);
                    ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'dashboard') ?>">Seguridad</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'tenant') ?>">Tenants</a></li>
                    <li class="breadcrumb-item active">Asignar Módulos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="<?= url('seguridad', 'asignacion', 'guardarModulos') ?>">
            <input type="hidden" name="tenant_id" value="<?= isset($tenant['tenant_id']) ? htmlspecialchars($tenant['tenant_id']) : '' ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <!-- Módulos Disponibles -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-th-large mr-2"></i>Módulos Disponibles</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" onclick="seleccionarTodos()">
                                    <i class="fas fa-check-double"></i> Todos
                                </button>
                                <button type="button" class="btn btn-tool" onclick="deseleccionarTodos()">
                                    <i class="fas fa-times"></i> Ninguno
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($modulos as $m): ?>
                                <?php 
                                $asignado = isset($asignados[$m['modulo_id']]);
                                $config = $asignado ? $asignados[$m['modulo_id']] : [];
                                ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card h-100 modulo-card <?= $asignado ? 'border-primary' : '' ?>">
                                        <div class="card-header p-2" style="background-color: <?= htmlspecialchars($m['color'] ?? '#F59E0B') ?>20;">
                                            <div class="custom-control custom-checkbox float-right">
                                                <input type="checkbox" class="custom-control-input modulo-check" id="mod_<?= $m['modulo_id'] ?>" name="modulos[<?= $m['modulo_id'] ?>][activo]" value="1" <?= $asignado ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="mod_<?= $m['modulo_id'] ?>"></label>
                                            </div>
                                            <?php
                                            // Priorizar icono personalizado si está asignado y definido
                                            $icono = null;
                                            if ($asignado && !empty($config['icono_custom'])) {
                                                $icono = $config['icono_custom'];
                                            } elseif (!empty($m['icono']) && $m['icono'] !== '-') {
                                                $icono = $m['icono'];
                                            } else {
                                                $icono = 'fa-cube';
                                            }
                                            // Lógica de prefijos FontAwesome
                                            if (
                                                strpos($icono, 'fa ') === 0 ||
                                                strpos($icono, 'fas ') === 0 ||
                                                strpos($icono, 'fab ') === 0 ||
                                                strpos($icono, 'far ') === 0
                                            ) {
                                                // ya tiene prefijo correcto
                                            } elseif (strpos($icono, 'fa-') === 0) {
                                                $icono = 'fas ' . $icono;
                                            } else {
                                                $icono = 'fas fa-' . $icono;
                                            }
                                            ?>
                                            <i class="<?= $icono ?> fa-lg mr-2" style="color: <?= htmlspecialchars($m['color'] ?? '#F59E0B') ?>;"></i>
                                            <strong style="color: <?= htmlspecialchars($m['color'] ?? '#F59E0B') ?>;"><?= htmlspecialchars($m['nombre']) ?></strong>
                                            <span class="badge badge-light border ml-1" style="font-size:10px;vertical-align:middle;">icono: <?= htmlspecialchars($m['icono'] ?? '-') ?></span>
                                        </div>
                                        <div class="card-body p-2">
                                            <p class="small text-muted mb-2"><?= htmlspecialchars($m['descripcion'] ?? 'Sin descripción') ?></p>
                                            
                                            <div class="config-options <?= $asignado ? '' : 'd-none' ?>">
                                                <hr class="my-2">
                                                <div class="form-group mb-2">
                                                    <label class="small">Nombre personalizado:</label>
                                                    <input type="text" class="form-control form-control-sm" name="modulos[<?= $m['modulo_id'] ?>][nombre_custom]" value="<?= htmlspecialchars($config['nombre_custom'] ?? '') ?>" placeholder="<?= htmlspecialchars($m['nombre']) ?>">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small">Icono:</label>
                                                    <input type="text" class="form-control form-control-sm" name="modulos[<?= $m['modulo_id'] ?>][icono_custom]" value="<?= htmlspecialchars($config['icono_custom'] ?? '') ?>" placeholder="<?= $m['icono'] ?>">
                                                </div>
                                                <div class="form-group mb-0">
                                                    <label class="small">Color:</label>
                                                    <input type="color" class="form-control form-control-sm" name="modulos[<?= $m['modulo_id'] ?>][color_custom]" value="<?= $config['color_custom'] ?? $m['color'] ?? '#6c757d' ?>" style="height: 30px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Info Tenant -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-building mr-2"></i>Tenant</h3>
                        </div>
                        <div class="card-body">
                            <dl>
                                <dt>Razón Social</dt>
                                <dd><?= isset($tenant['razon_social']) && $tenant['razon_social'] ? htmlspecialchars($tenant['razon_social']) : '' ?></dd>
                                <dt>RUC</dt>
                                <dd><?= isset($tenant['ruc']) && $tenant['ruc'] ? htmlspecialchars($tenant['ruc']) : '' ?></dd>
                                <dt>Plan</dt>
                                <dd><span class="badge badge-info"><?= htmlspecialchars($tenant['plan_nombre'] ?? 'Sin plan') ?></span></dd>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- Resumen -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Resumen</h3>
                        </div>
                        <div class="card-body text-center">
                            <h2 class="text-primary" id="contador-modulos"><?= count($asignados) ?></h2>
                            <p class="text-muted">módulos seleccionados</p>
                            <small class="text-muted">de <?= count($modulos) ?> disponibles</small>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i> Guardar Asignación
                            </button>
                            <a href="<?= url('seguridad', 'tenant', 'ver', ['id' => isset($tenant['tenant_id']) ? $tenant['tenant_id'] : '']) ?>" class="btn btn-info btn-block">
                                <i class="fas fa-eye mr-1"></i> Ver Tenant
                            </a>
                            <a href="<?= url('seguridad', 'tenant') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.modulo-check');
    const contador = document.getElementById('contador-modulos');
    
    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            const card = this.closest('.modulo-card');
            const options = card.querySelector('.config-options');
            
            if (this.checked) {
                card.classList.add('border-primary');
                options.classList.remove('d-none');
            } else {
                card.classList.remove('border-primary');
                options.classList.add('d-none');
            }
            
            actualizarContador();
        });
    });
    
    function actualizarContador() {
        contador.textContent = document.querySelectorAll('.modulo-check:checked').length;
    }
});

function seleccionarTodos() {
    document.querySelectorAll('.modulo-check').forEach(function(cb) {
        cb.checked = true;
        cb.dispatchEvent(new Event('change'));
    });
}

function deseleccionarTodos() {
    document.querySelectorAll('.modulo-check').forEach(function(cb) {
        cb.checked = false;
        cb.dispatchEvent(new Event('change'));
    });
}
</script>
