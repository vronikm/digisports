<?php
/**
 * DigiSports Seguridad - Matriz de Permisos del Rol
 */

$rol = $rol ?? [];
$permisos = $permisos ?? [];
$permisosActuales = $permisosActuales ?? [];

// Permisos organizados por módulo
$permisosModulo = [
    'dashboard' => [
        'nombre' => 'Dashboard',
        'icono' => 'fas fa-tachometer-alt',
        'acciones' => ['ver']
    ],
    'clientes' => [
        'nombre' => 'Clientes',
        'icono' => 'fas fa-users',
        'acciones' => ['ver', 'crear', 'editar', 'eliminar', 'exportar']
    ],
    'instalaciones' => [
        'nombre' => 'Instalaciones',
        'icono' => 'fas fa-building',
        'acciones' => ['ver', 'crear', 'editar', 'eliminar']
    ],
    'reservas' => [
        'nombre' => 'Reservas',
        'icono' => 'fas fa-calendar-alt',
        'acciones' => ['ver', 'crear', 'editar', 'eliminar', 'confirmar', 'cancelar']
    ],
    'facturacion' => [
        'nombre' => 'Facturación',
        'icono' => 'fas fa-file-invoice-dollar',
        'acciones' => ['ver', 'crear', 'anular', 'reenviar', 'exportar']
    ],
    'reportes' => [
        'nombre' => 'Reportes',
        'icono' => 'fas fa-chart-bar',
        'acciones' => ['ver', 'exportar']
    ],
    'usuarios' => [
        'nombre' => 'Usuarios',
        'icono' => 'fas fa-user-cog',
        'acciones' => ['ver', 'crear', 'editar', 'eliminar', 'resetear_password', 'bloquear']
    ],
    'roles' => [
        'nombre' => 'Roles',
        'icono' => 'fas fa-user-tag',
        'acciones' => ['ver', 'crear', 'editar', 'eliminar', 'asignar_permisos']
    ],
    'modulos' => [
        'nombre' => 'Módulos',
        'icono' => 'fas fa-puzzle-piece',
        'acciones' => ['ver', 'crear', 'editar', 'eliminar', 'asignar']
    ],
    'tenants' => [
        'nombre' => 'Tenants',
        'icono' => 'fas fa-building',
        'acciones' => ['ver', 'crear', 'editar', 'eliminar', 'suspender', 'renovar']
    ],
    'configuracion' => [
        'nombre' => 'Configuración',
        'icono' => 'fas fa-cogs',
        'acciones' => ['ver', 'editar']
    ],
    'auditoria' => [
        'nombre' => 'Auditoría',
        'icono' => 'fas fa-history',
        'acciones' => ['ver', 'exportar']
    ],
];

$accionesIconos = [
    'ver' => 'fas fa-eye',
    'crear' => 'fas fa-plus',
    'editar' => 'fas fa-edit',
    'eliminar' => 'fas fa-trash',
    'exportar' => 'fas fa-download',
    'confirmar' => 'fas fa-check',
    'cancelar' => 'fas fa-times',
    'anular' => 'fas fa-ban',
    'reenviar' => 'fas fa-paper-plane',
    'resetear_password' => 'fas fa-key',
    'bloquear' => 'fas fa-lock',
    'asignar_permisos' => 'fas fa-user-shield',
    'asignar' => 'fas fa-link',
    'suspender' => 'fas fa-pause',
    'renovar' => 'fas fa-sync',
];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-key mr-2"></i>
                    Permisos: <?= htmlspecialchars($rol['rol_nombre'] ?? 'Rol sin nombre') ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'dashboard', 'index') ?>">Seguridad</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'rol') ?>">Roles</a></li>
                    <li class="breadcrumb-item active">Permisos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="<?= url('seguridad', 'rol', 'guardarPermisos') ?>">
            <input type="hidden" name="rol_id" value="<?= $rol['rol_rol_id'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-9">
                    <!-- Acciones Rápidas -->
                    <div class="card card-outline card-primary mb-4">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-magic mr-2"></i>Acciones Rápidas</h3>
                        </div>
                        <div class="card-body">
                            <div class="btn-group">
                                <button type="button" class="btn btn-success" onclick="seleccionarTodos()">
                                    <i class="fas fa-check-double mr-1"></i> Seleccionar Todos
                                </button>
                                <button type="button" class="btn btn-danger" onclick="deseleccionarTodos()">
                                    <i class="fas fa-times mr-1"></i> Deseleccionar Todos
                                </button>
                                <button type="button" class="btn btn-info" onclick="soloLectura()">
                                    <i class="fas fa-eye mr-1"></i> Solo Lectura
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Matriz de Permisos -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-th mr-2"></i>Matriz de Permisos</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead class="bg-dark text-white">
                                        <tr>
                                            <th width="200">Módulo</th>
                                            <th class="text-center" width="80">
                                                <i class="fas fa-eye"></i><br>
                                                <small>Ver</small>
                                            </th>
                                            <th class="text-center" width="80">
                                                <i class="fas fa-plus"></i><br>
                                                <small>Crear</small>
                                            </th>
                                            <th class="text-center" width="80">
                                                <i class="fas fa-edit"></i><br>
                                                <small>Editar</small>
                                            </th>
                                            <th class="text-center" width="80">
                                                <i class="fas fa-trash"></i><br>
                                                <small>Eliminar</small>
                                            </th>
                                            <th class="text-center">Otros Permisos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($permisosModulo as $modulo => $config): ?>
                                        <tr>
                                            <td>
                                                <i class="<?= $config['icono'] ?> mr-2"></i>
                                                <strong><?= $config['nombre'] ?></strong>
                                            </td>
                                            <?php 
                                            $accionesBasicas = ['ver', 'crear', 'editar', 'eliminar'];
                                            foreach ($accionesBasicas as $accion): 
                                                if (in_array($accion, $config['acciones'])):
                                                    $permiso = $modulo . '.' . $accion;
                                                    $checked = in_array($permiso, $permisosActuales);
                                            ?>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input permiso-check" id="perm_<?= $permiso ?>" name="permisos[]" value="<?= $permiso ?>" data-modulo="<?= $modulo ?>" data-accion="<?= $accion ?>" <?= $checked ? 'checked' : '' ?>>
                                                    <label class="custom-control-label" for="perm_<?= $permiso ?>"></label>
                                                </div>
                                            </td>
                                            <?php else: ?>
                                            <td class="text-center bg-light">-</td>
                                            <?php endif; endforeach; ?>
                                            <td>
                                                <?php 
                                                $otrasAcciones = array_diff($config['acciones'], $accionesBasicas);
                                                foreach ($otrasAcciones as $accion): 
                                                    $permiso = $modulo . '.' . $accion;
                                                    $checked = in_array($permiso, $permisosActuales);
                                                ?>
                                                <div class="custom-control custom-checkbox custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input permiso-check" id="perm_<?= $permiso ?>" name="permisos[]" value="<?= $permiso ?>" data-modulo="<?= $modulo ?>" data-accion="<?= $accion ?>" <?= $checked ? 'checked' : '' ?>>
                                                    <label class="custom-control-label" for="perm_<?= $permiso ?>">
                                                        <i class="<?= $accionesIconos[$accion] ?? 'fas fa-check' ?> mr-1"></i>
                                                        <?= ucfirst(str_replace('_', ' ', $accion)) ?>
                                                    </label>
                                                </div>
                                                <?php endforeach; ?>
                                                <?php if (empty($otrasAcciones)): ?>
                                                <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <!-- Info del Rol -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-tag mr-2"></i>Rol</h3>
                        </div>
                        <div class="card-body text-center">
                            <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background-color: #3B82F6;">
                                <i class="fas fa-user-shield text-white fa-2x"></i>
                            </div>
                            <h5><?= htmlspecialchars($rol['rol_nombre'] ?? '') ?></h5>
                            <span class="badge badge-info">Nivel <?= $rol['rol_nivel_acceso'] ?? 1 ?></span>
                            <p class="text-muted mt-2 small"><?= htmlspecialchars($rol['rol_descripcion'] ?? '') ?></p>
                        </div>
                    </div>
                    
                    <!-- Resumen -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Resumen</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <h2 class="text-primary mb-0" id="contador-permisos"><?= count($permisosActuales) ?></h2>
                                <small class="text-muted">permisos seleccionados</small>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-primary" id="barra-permisos" style="width: <?= (count($permisosActuales) / 50) * 100 ?>%"></div>
                            </div>
                            <small class="text-muted">de ~50 permisos disponibles</small>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i> Guardar Permisos
                            </button>
                            <a href="<?= url('seguridad', 'rol', 'editar', ['id' => $rol['rol_rol_id'] ?? '']) ?>" class="btn btn-info btn-block">
                                <i class="fas fa-edit mr-1"></i> Editar Rol
                            </a>
                            <a href="<?= url('seguridad', 'rol') ?>" class="btn btn-secondary btn-block">
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
    const checkboxes = document.querySelectorAll('.permiso-check');
    const contador = document.getElementById('contador-permisos');
    const barra = document.getElementById('barra-permisos');
    
    function actualizarContador() {
        const seleccionados = document.querySelectorAll('.permiso-check:checked').length;
        contador.textContent = seleccionados;
        barra.style.width = Math.min((seleccionados / 50) * 100, 100) + '%';
    }
    
    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', actualizarContador);
    });
});

function seleccionarTodos() {
    document.querySelectorAll('.permiso-check').forEach(function(cb) {
        cb.checked = true;
    });
    document.getElementById('contador-permisos').textContent = document.querySelectorAll('.permiso-check').length;
}

function deseleccionarTodos() {
    document.querySelectorAll('.permiso-check').forEach(function(cb) {
        cb.checked = false;
    });
    document.getElementById('contador-permisos').textContent = 0;
}

function soloLectura() {
    document.querySelectorAll('.permiso-check').forEach(function(cb) {
        cb.checked = cb.getAttribute('data-accion') === 'ver';
    });
    document.getElementById('contador-permisos').textContent = document.querySelectorAll('.permiso-check[data-accion="ver"]').length;
}
</script>
