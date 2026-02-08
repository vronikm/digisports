
<?php
/**
 * DigiSports Seguridad - Formulario de Usuario
 */

$usuario = $usuario ?? null;
$tenants = $tenants ?? [];
$roles = $roles ?? [];
$esEdicion = !empty($usuario);
?>

<section class="content pt-3">
    <div class="container-fluid">

<!-- Header Premium -->
<?php
$headerTitle    = ($esEdicion ? 'Editar' : 'Nuevo') . ' Usuario';
$headerSubtitle = $esEdicion ? 'Modificar datos del usuario' : 'Registrar un nuevo usuario en el sistema';
$headerIcon     = 'fas fa-user-' . ($esEdicion ? 'edit' : 'plus');
$headerButtons  = [
    ['url' => url('seguridad', 'usuario', 'index'), 'label' => 'Volver a Usuarios', 'icon' => 'fas fa-arrow-left', 'solid' => false],
];
include __DIR__ . '/../partials/header.php';
?>
        <form method="POST" action="<?= $esEdicion ? url('seguridad', 'usuario', 'editar', ['id' => $usuario['usu_usuario_id']]) : url('seguridad', 'usuario', 'crear') ?>">
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
                                        <input type="text" name="usu_nombres" class="form-control" required
                                               value="<?= htmlspecialchars($usuario['usu_nombres'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Apellidos <span class="text-danger">*</span></label>
                                        <input type="text" name="usu_apellidos" class="form-control" required
                                               value="<?= htmlspecialchars($usuario['usu_apellidos'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Identificación</label>
                                        <input type="text" name="usu_identificacion" class="form-control"
                                               value="<?= htmlspecialchars($usuario['usu_identificacion'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <input type="text" name="usu_telefono" class="form-control"
                                               value="<?= htmlspecialchars($usuario['usu_telefono'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Celular</label>
                                        <input type="text" name="usu_celular" class="form-control"
                                               value="<?= htmlspecialchars($usuario['usu_celular'] ?? '') ?>">
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
                                        <input type="email" name="usu_email" class="form-control" required
                                               value="<?= htmlspecialchars($usuario['usu_email'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Username <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">@</span>
                                            </div>
                                            <input type="text" name="usu_username" class="form-control" required
                                                   value="<?= htmlspecialchars($usuario['usu_username'] ?? '') ?>">
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
                                <select name="usu_tenant_id" class="form-control select2" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($tenants as $t): ?>
                                    <option value="<?= $t['ten_tenant_id'] ?>" <?= ($usuario['usu_tenant_id'] ?? '') == $t['ten_tenant_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['ten_nombre_comercial']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Rol <span class="text-danger">*</span></label>
                                <select name="usu_rol_id" class="form-control" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['rol_rol_id'] ?>" <?= ($usuario['usu_rol_id'] ?? '') == $r['rol_rol_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['rol_nombre']) ?> (<?= $r['rol_codigo'] ?>)
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
                                <select name="usu_estado" class="form-control">
                                    <option value="A" <?= ($usuario['usu_estado'] ?? 'A') == 'A' ? 'selected' : '' ?>>Activo</option>
                                    <option value="I" <?= ($usuario['usu_estado'] ?? '') == 'I' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="requiere_2fa" name="usu_requiere_2fa"
                                           <?= ($usuario['usu_requiere_2fa'] ?? 'S') == 'S' ? 'checked' : '' ?>>
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
