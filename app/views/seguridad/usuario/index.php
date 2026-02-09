<?php
/**
 * DigiSports Seguridad - Lista de Usuarios
 */

$usuarios = $usuarios ?? [];
$tenants = $tenants ?? [];
$filtros = $filtros ?? [];
$total = $total ?? 0;
$pagina = $pagina ?? 1;
$totalPaginas = $totalPaginas ?? 1;
?>

<section class="content pt-3">
    <div class="container-fluid">

<!-- Header Premium -->
<?php
$headerTitle    = 'Gestión de Usuarios';
$headerSubtitle = 'Administración de cuentas de usuario del sistema';
$headerIcon     = 'fas fa-users';
$headerButtons  = [
    ['url' => url('seguridad', 'usuario', 'crear'), 'label' => 'Nuevo Usuario', 'icon' => 'fas fa-plus', 'solid' => true],
];
include __DIR__ . '/../partials/header.php';
?>
        <!-- Filtros -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filtros</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('seguridad', 'usuario', 'index') ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tenant</label>
                                <select name="tenant_id" class="form-control select2">
                                    <option value="">-- Todos --</option>
                                    <?php foreach ($tenants as $t): ?>
                                    <option value="<?= $t['ten_tenant_id'] ?>" <?= ($filtros['tenant_id'] ?? '') == $t['ten_tenant_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['ten_nombre_comercial']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Estado</label>
                                <select name="estado" class="form-control">
                                    <option value="">-- Todos --</option>
                                    <option value="A" <?= ($filtros['estado'] ?? '') == 'A' ? 'selected' : '' ?>>Activo</option>
                                    <option value="I" <?= ($filtros['estado'] ?? '') == 'I' ? 'selected' : '' ?>>Inactivo</option>
                                    <option value="E" <?= ($filtros['estado'] ?? '') == 'E' ? 'selected' : '' ?>>Eliminado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Buscar</label>
                                <input type="text" name="buscar" class="form-control" placeholder="Nombre, email, username..." value="<?= htmlspecialchars($filtros['buscar'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search mr-1"></i> Buscar
                                    </button>
                                    <a href="<?= url('seguridad', 'usuario', 'index') ?>" class="btn btn-secondary">
                                        <i class="fas fa-times mr-1"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lista de usuarios -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>
                    Usuarios (<?= $total ?>)
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Tenant</th>
                            <th>Rol</th>
                            <th>Último Login</th>
                            <th>Estado</th>
                            <th width="150">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No se encontraron usuarios
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= $u['usu_usuario_id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?= $u['usu_avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($u['usu_nombres'] ?? '') . '&background=6366F1&color=fff&size=32' ?>" 
                                         class="rounded-circle mr-2" width="32" height="32">
                                    <div>
                                        <strong><?= htmlspecialchars(($u['usu_nombres'] ?? '') . ' ' . ($u['usu_apellidos'] ?? '')) ?></strong>
                                        <br><small class="text-muted">@<?= htmlspecialchars($u['usu_username'] ?? '') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['usu_email'] ?? '') ?></td>
                            <td>
                                <small><?= htmlspecialchars($u['tenant_nombre'] ?? 'N/A') ?></small>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= htmlspecialchars($u['rol_nombre'] ?? 'Sin rol') ?></span>
                            </td>
                            <td>
                                <?php if (!empty($u['usu_ultimo_login'])): ?>
                                <small><?= date('d/m/Y H:i', strtotime($u['usu_ultimo_login'])) ?></small>
                                <?php else: ?>
                                <small class="text-muted">Nunca</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $uEstado = $u['usu_estado'] ?? '';
                                switch ($uEstado) {
                                    case 'A': $estadoClass = 'success'; $estadoText = 'Activo'; break;
                                    case 'I': $estadoClass = 'warning'; $estadoText = 'Inactivo'; break;
                                    case 'E': $estadoClass = 'danger'; $estadoText = 'Eliminado'; break;
                                    default:  $estadoClass = 'secondary'; $estadoText = 'Desconocido'; break;
                                }
                                ?>
                                <span class="badge badge-<?= $estadoClass ?>"><?= $estadoText ?></span>
                                <?php if (($u['usu_intentos_fallidos'] ?? 0) >= 3): ?>
                                <span class="badge badge-danger"><i class="fas fa-lock"></i></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('seguridad', 'usuario', 'editar', ['id' => $u['usu_usuario_id']]) ?>" 
                                       class="btn btn-info" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (($u['usu_intentos_fallidos'] ?? 0) >= 3): ?>
                                    <a href="<?= url('seguridad', 'usuario', 'desbloquear', ['id' => $u['usu_usuario_id']]) ?>" 
                                       class="btn btn-warning" title="Desbloquear">
                                        <i class="fas fa-unlock"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= url('seguridad', 'usuario', 'resetPassword', ['id' => $u['usu_usuario_id']]) ?>" 
                                       class="btn btn-secondary" title="Reset Password" onclick="return confirm('¿Resetear contraseña?')">
                                        <i class="fas fa-key"></i>
                                    </a>
                                    <a href="<?= url('seguridad', 'usuario', 'eliminar', ['id' => $u['usu_usuario_id']]) ?>" 
                                       class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Eliminar usuario?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPaginas > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination pagination-sm mb-0 justify-content-center">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('seguridad', 'usuario', 'index', array_merge($filtros, ['pagina' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
