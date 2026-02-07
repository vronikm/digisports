<?php
/**
 * DigiSports Seguridad - Asignación Masiva de Módulos
 */

$tenants = $tenants ?? [];
$modulos = $modulos ?? [];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-layer-group mr-2"></i>
                    Asignación Masiva de Módulos
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('seguridad') ?>">Seguridad</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'asignacion') ?>">Asignación</a></li>
                    <li class="breadcrumb-item active">Masiva</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="<?= url('seguridad', 'asignacion', 'guardarMasiva') ?>">
            <div class="row">
                <div class="col-md-6">
                    <!-- Selección de Tenants -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-building mr-2"></i>
                                Seleccionar Tenants
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" onclick="seleccionarTodosTenants()">
                                    <i class="fas fa-check-double"></i>
                                </button>
                                <button type="button" class="btn btn-tool" onclick="deseleccionarTodosTenants()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                            <?php if (empty($tenants)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-building fa-3x mb-2"></i>
                                <p>No hay tenants disponibles</p>
                            </div>
                            <?php else: ?>
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="all_tenants" onclick="toggleTodosTenants()">
                                                <label class="custom-control-label" for="all_tenants"></label>
                                            </div>
                                        </th>
                                        <th>Tenant</th>
                                        <th>Plan</th>
                                        <th>Módulos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tenants as $t): ?>
                                    <tr>
                                        <td>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input tenant-check" id="tenant_<?= $t['ten_tenant_id'] ?>" name="tenants[]" value="<?= $t['ten_tenant_id'] ?>">
                                                <label class="custom-control-label" for="tenant_<?= $t['ten_tenant_id'] ?>"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($t['ten_nombre_comercial'] ?: $t['ten_razon_social']) ?></strong>
                                            <br><small class="text-muted"><?= $t['ten_ruc'] ?? '' ?></small>
                                        </td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($t['plan_nombre'] ?? '-') ?></span></td>
                                        <td><span class="badge badge-secondary"><?= $t['modulos_count'] ?? 0 ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <span id="tenants-seleccionados">0</span> tenants seleccionados
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <!-- Selección de Módulos -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-puzzle-piece mr-2"></i>
                                Seleccionar Módulos
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" onclick="seleccionarTodosModulos()">
                                    <i class="fas fa-check-double"></i>
                                </button>
                                <button type="button" class="btn btn-tool" onclick="deseleccionarTodosModulos()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <?php if (empty($modulos)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-puzzle-piece fa-3x mb-2"></i>
                                <p>No hay módulos disponibles</p>
                            </div>
                            <?php else: ?>
                            <div class="row">
                                <?php foreach ($modulos as $m): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input modulo-check" id="modulo_<?= $m['mod_id'] ?>" name="modulos[]" value="<?= $m['mod_id'] ?>">
                                        <label class="custom-control-label" for="modulo_<?= $m['mod_id'] ?>">
                                            <i class="<?= $m['mod_icono'] ?? 'fas fa-cube' ?> mr-1" style="color: <?= $m['mod_color_fondo'] ?? '#6c757d' ?>"></i>
                                            <?= htmlspecialchars($m['mod_nombre'] ?? '') ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <span id="modulos-seleccionados">0</span> módulos seleccionados
                        </div>
                    </div>
                    
                    <!-- Acción -->
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-cog mr-2"></i>Acción a Realizar</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" class="custom-control-input" id="accion_agregar" name="accion" value="agregar" checked>
                                    <label class="custom-control-label" for="accion_agregar">
                                        <i class="fas fa-plus text-success mr-1"></i>
                                        <strong>Agregar</strong> - Asignar módulos seleccionados (sin afectar los existentes)
                                    </label>
                                </div>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" class="custom-control-input" id="accion_reemplazar" name="accion" value="reemplazar">
                                    <label class="custom-control-label" for="accion_reemplazar">
                                        <i class="fas fa-sync text-warning mr-1"></i>
                                        <strong>Reemplazar</strong> - Los módulos seleccionados reemplazarán a los actuales
                                    </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="accion_quitar" name="accion" value="quitar">
                                    <label class="custom-control-label" for="accion_quitar">
                                        <i class="fas fa-minus text-danger mr-1"></i>
                                        <strong>Quitar</strong> - Remover los módulos seleccionados de los tenants
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resumen y Botones -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-clipboard-check mr-2"></i>Resumen</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h3 class="text-primary" id="resumen-tenants">0</h3>
                                    <small>Tenants</small>
                                </div>
                                <div class="col-6">
                                    <h3 class="text-info" id="resumen-modulos">0</h3>
                                    <small>Módulos</small>
                                </div>
                            </div>
                            <hr>
                            <p class="text-center text-muted small" id="resumen-texto">
                                Seleccione tenants y módulos para continuar
                            </p>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-block" id="btn-ejecutar" disabled>
                                <i class="fas fa-play mr-1"></i> Ejecutar Asignación
                            </button>
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
    const tenantChecks = document.querySelectorAll('.tenant-check');
    const moduloChecks = document.querySelectorAll('.modulo-check');
    const btnEjecutar = document.getElementById('btn-ejecutar');
    
    function actualizarResumen() {
        const tenantsCount = document.querySelectorAll('.tenant-check:checked').length;
        const modulosCount = document.querySelectorAll('.modulo-check:checked').length;
        
        document.getElementById('tenants-seleccionados').textContent = tenantsCount;
        document.getElementById('modulos-seleccionados').textContent = modulosCount;
        document.getElementById('resumen-tenants').textContent = tenantsCount;
        document.getElementById('resumen-modulos').textContent = modulosCount;
        
        const accion = document.querySelector('input[name="accion"]:checked').value;
        const accionTexto = {
            'agregar': 'se agregarán',
            'reemplazar': 'reemplazarán los módulos de',
            'quitar': 'se quitarán de'
        };
        
        if (tenantsCount > 0 && modulosCount > 0) {
            document.getElementById('resumen-texto').innerHTML = 
                `<strong>${modulosCount}</strong> módulo(s) ${accionTexto[accion]} <strong>${tenantsCount}</strong> tenant(s)`;
            btnEjecutar.disabled = false;
        } else {
            document.getElementById('resumen-texto').textContent = 'Seleccione tenants y módulos para continuar';
            btnEjecutar.disabled = true;
        }
    }
    
    tenantChecks.forEach(cb => cb.addEventListener('change', actualizarResumen));
    moduloChecks.forEach(cb => cb.addEventListener('change', actualizarResumen));
    document.querySelectorAll('input[name="accion"]').forEach(r => r.addEventListener('change', actualizarResumen));
});

function toggleTodosTenants() {
    const checked = document.getElementById('all_tenants').checked;
    document.querySelectorAll('.tenant-check').forEach(cb => cb.checked = checked);
    document.querySelectorAll('.tenant-check')[0]?.dispatchEvent(new Event('change'));
}

function seleccionarTodosTenants() {
    document.querySelectorAll('.tenant-check').forEach(cb => cb.checked = true);
    document.getElementById('all_tenants').checked = true;
    document.querySelectorAll('.tenant-check')[0]?.dispatchEvent(new Event('change'));
}

function deseleccionarTodosTenants() {
    document.querySelectorAll('.tenant-check').forEach(cb => cb.checked = false);
    document.getElementById('all_tenants').checked = false;
    document.querySelectorAll('.tenant-check')[0]?.dispatchEvent(new Event('change'));
}

function seleccionarTodosModulos() {
    document.querySelectorAll('.modulo-check').forEach(cb => cb.checked = true);
    document.querySelectorAll('.modulo-check')[0]?.dispatchEvent(new Event('change'));
}

function deseleccionarTodosModulos() {
    document.querySelectorAll('.modulo-check').forEach(cb => cb.checked = false);
    document.querySelectorAll('.modulo-check')[0]?.dispatchEvent(new Event('change'));
}
</script>
