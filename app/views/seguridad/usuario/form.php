
<?php
/**
 * DigiSports Seguridad - Formulario de Usuario
 */

$usuario = $usuario ?? null;
$tenants = $tenants ?? [];
$roles = $roles ?? [];
$esEdicion = !empty($usuario);
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-user-<?= $esEdicion ? 'edit' : 'plus' ?> mr-2"></i>
                    <?= $esEdicion ? 'Editar' : 'Nuevo' ?> Usuario
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'dashboard', 'index') ?>">Seguridad</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'usuario', 'index') ?>">Usuarios</a></li>
                    <li class="breadcrumb-item active"><?= $esEdicion ? 'Editar' : 'Nuevo' ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="<?= $esEdicion ? url('seguridad', 'usuario', 'editar', ['id' => $usuario['usuario_id']]) : url('seguridad', 'usuario', 'crear') ?>">
            <div class="row">
                <div class="col-md-8">
                    <!-- Datos personales -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user mr-2"></i>Datos Personales</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombres <span class="text-danger">*</span></label>
                                        <input type="text" name="nombres" class="form-control" required
                                               value="<?= htmlspecialchars($usuario['nombres'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Apellidos <span class="text-danger">*</span></label>
                                        <input type="text" name="apellidos" class="form-control" required
                                               value="<?= htmlspecialchars($usuario['apellidos'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Identificación</label>
                                        <input type="text" name="identificacion" class="form-control"
                                               value="<?= htmlspecialchars($usuario['identificacion'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <input type="text" name="telefono" class="form-control"
                                               value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Celular</label>
                                        <input type="text" name="celular" class="form-control"
                                               value="<?= htmlspecialchars($usuario['celular'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Datos de acceso -->
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-key mr-2"></i>Datos de Acceso</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" required
                                               value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Username <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">@</span>
                                            </div>
                                            <input type="text" name="username" class="form-control" required
                                                   value="<?= htmlspecialchars($usuario['username'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Contraseña <?= $esEdicion ? '' : '<span class="text-danger">*</span>' ?></label>
                                        <input type="password" name="password" class="form-control" <?= $esEdicion ? '' : 'required' ?>
                                               placeholder="<?= $esEdicion ? 'Dejar vacío para mantener actual' : '' ?>">
                                        <small class="text-muted">Mínimo 8 caracteres, mayúsculas, números y símbolos</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Confirmar Contraseña</label>
                                        <input type="password" name="password_confirm" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Asignación -->
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-building mr-2"></i>Asignación</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Tenant <span class="text-danger">*</span></label>
                                <select name="tenant_id" class="form-control select2" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($tenants as $t): ?>
                                    <option value="<?= $t['tenant_id'] ?>" <?= ($usuario['tenant_id'] ?? '') == $t['tenant_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['nombre_comercial']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Rol <span class="text-danger">*</span></label>
                                <select name="rol_id" class="form-control" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['rol_id'] ?>" <?= ($usuario['rol_id'] ?? '') == $r['rol_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['nombre']) ?> (<?= $r['codigo'] ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Configuración -->
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-cog mr-2"></i>Configuración</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Estado</label>
                                <select name="estado" class="form-control">
                                    <option value="A" <?= ($usuario['estado'] ?? 'A') == 'A' ? 'selected' : '' ?>>Activo</option>
                                    <option value="I" <?= ($usuario['estado'] ?? '') == 'I' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="requiere_2fa" name="requiere_2fa"
                                           <?= ($usuario['requiere_2fa'] ?? 'S') == 'S' ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="requiere_2fa">Requiere 2FA</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Acciones -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i>
                                <?= $esEdicion ? 'Actualizar' : 'Crear' ?> Usuario
                            </button>
                            <a href="<?= url('seguridad', 'usuario', 'index') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left mr-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
