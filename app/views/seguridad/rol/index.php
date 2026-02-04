<?php
/**
 * DigiSports Seguridad - Lista de Roles
 */

$roles = $roles ?? [];
$total = $total ?? 0;
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-user-tag mr-2"></i>
                    Gestión de Roles
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('seguridad', 'rol', 'crear') ?>" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Nuevo Rol
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Roles del Sistema (<?= $total ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (empty($roles)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-user-tag fa-4x mb-3 d-block"></i>
                    <h5>No hay roles configurados</h5>
                    <p>Cree roles para asignar permisos a los usuarios</p>
                    <a href="<?= url('seguridad', 'rol', 'crear') ?>" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Crear Primer Rol
                    </a>
                </div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($roles as $rol): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 <?= isset($rol['es_sistema']) && $rol['es_sistema'] ? 'card-outline card-primary' : '' ?>">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user-shield mr-2" style="color: <?= $rol['color'] ?? '#6c757d' ?>"></i>
                                    <?= htmlspecialchars($rol['nombre']) ?>
                                </h3>
                                <?php if (isset($rol['es_sistema']) && $rol['es_sistema']): ?>
                                <span class="badge badge-primary float-right">Sistema</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <p class="text-muted"><?= htmlspecialchars($rol['descripcion'] ?? 'Sin descripción') ?></p>
                                
                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <h4 class="mb-0"><?= $rol['usuarios_count'] ?? 0 ?></h4>
                                        <small class="text-muted">Usuarios</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="mb-0"><?= $rol['permisos_count'] ?? 0 ?></h4>
                                        <small class="text-muted">Permisos</small>
                                    </div>
                                </div>
                                
                                <?php if (!empty($rol['permisos_preview'])): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Permisos principales:</small>
                                    <div>
                                        <?php foreach (array_slice($rol['permisos_preview'], 0, 5) as $perm): ?>
                                        <span class="badge badge-secondary mr-1"><?= htmlspecialchars($perm) ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($rol['permisos_preview']) > 5): ?>
                                        <span class="badge badge-light">+<?= count($rol['permisos_preview']) - 5 ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="btn-group btn-group-sm w-100">
                                    <a href="<?= url('seguridad', 'rol', 'permisos', ['id' => $rol['rol_id']]) ?>" class="btn btn-info flex-fill" title="Permisos">
                                        <i class="fas fa-key"></i> Permisos
                                    </a>
                                    <a href="<?= url('seguridad', 'rol', 'editar', ['id' => $rol['rol_id']]) ?>" class="btn btn-primary flex-fill" title="Editar">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <?php if (!$rol['es_sistema']): ?>
                                    <a href="<?= url('seguridad', 'rol', 'eliminar', ['id' => $rol['rol_id']]) ?>" class="btn btn-danger flex-fill" title="Eliminar" onclick="return confirm('¿Eliminar este rol?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Roles Predefinidos -->
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-magic mr-2"></i>Crear Rol Predefinido</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Cree un rol basado en plantillas predefinidas:</p>
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?= url('seguridad', 'rol', 'crearDesde', ['plantilla' => 'superadmin']) ?>" class="btn btn-block btn-outline-danger py-3">
                            <i class="fas fa-crown fa-2x d-block mb-2"></i>
                            <strong>Super Admin</strong>
                            <br><small>Acceso total al sistema</small>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?= url('seguridad', 'rol', 'crearDesde', ['plantilla' => 'admin']) ?>" class="btn btn-block btn-outline-primary py-3">
                            <i class="fas fa-user-cog fa-2x d-block mb-2"></i>
                            <strong>Administrador</strong>
                            <br><small>Gestión general del tenant</small>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?= url('seguridad', 'rol', 'crearDesde', ['plantilla' => 'operador']) ?>" class="btn btn-block btn-outline-success py-3">
                            <i class="fas fa-user-check fa-2x d-block mb-2"></i>
                            <strong>Operador</strong>
                            <br><small>Gestión operativa diaria</small>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?= url('seguridad', 'rol', 'crearDesde', ['plantilla' => 'consulta']) ?>" class="btn btn-block btn-outline-info py-3">
                            <i class="fas fa-eye fa-2x d-block mb-2"></i>
                            <strong>Consulta</strong>
                            <br><small>Solo lectura de datos</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
