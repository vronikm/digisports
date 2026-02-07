<?php
/**
 * DigiSports Seguridad - Formulario Tenant
 */

$tenant = $tenant ?? [];
$planes = $planes ?? [];
$modulos = $modulos ?? [];
$modulosAsignados = $modulosAsignados ?? [];
$esEdicion = !empty($tenant['ten_tenant_id']);
$titulo = $esEdicion ? 'Editar Tenant' : 'Nuevo Tenant';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-<?= $esEdicion ? 'edit' : 'plus' ?> mr-2"></i>
                    <?= $titulo ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'dashboard') ?>">Seguridad</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'tenant') ?>">Tenants</a></li>
                    <li class="breadcrumb-item active"><?= $titulo ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid" style="padding-bottom: 48px; min-height: 80vh;">
        <form method="POST" action="<?= url('seguridad', 'tenant', $esEdicion ? 'actualizar' : 'guardar') ?>">
            <?php if ($esEdicion): ?>
            <input type="hidden" name="tenant_id" value="<?= $tenant['ten_tenant_id'] ?>">
            <?php endif; ?>
            
            <div class="row">
                <!-- Datos del Tenant -->
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-building mr-2"></i>Datos del Tenant</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ruc">RUC <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="ruc" name="ruc" value="<?= htmlspecialchars($tenant['ten_ruc'] ?? '') ?>" required maxlength="13">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="razon_social">Razón Social <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="razon_social" name="razon_social" value="<?= htmlspecialchars($tenant['ten_razon_social'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre_comercial">Nombre Comercial</label>
                                        <input type="text" class="form-control" id="nombre_comercial" name="nombre_comercial" value="<?= htmlspecialchars($tenant['ten_nombre_comercial'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($tenant['ten_email'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="telefono">Teléfono</label>
                                        <input type="text" class="form-control" id="telefono" name="telefono" value="<?= htmlspecialchars($tenant['ten_telefono'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="representante_legal">Representante Legal</label>
                                        <input type="text" class="form-control" id="representante_legal" name="representante_legal" value="<?= htmlspecialchars($tenant['ten_representante_nombre'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="2"><?= htmlspecialchars($tenant['ten_direccion'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Módulos Asignados -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-puzzle-piece mr-2"></i>Módulos Asignados</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Seleccione los módulos disponibles para este tenant:</p>
                            <div class="row">
                                <?php foreach ($modulos as $m): ?>
                                <?php $checked = in_array((int)$m['id'], $modulosAsignados); ?>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="modulo_<?= $m['id'] ?>" name="modulos[]" value="<?= $m['id'] ?>" <?= $checked ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="modulo_<?= $m['id'] ?>">
                                            <?php
                                            // Priorizar icono personalizado si existe
                                            $icono = !empty($m['icono_personalizado']) ? $m['icono_personalizado'] : ($m['icono'] ?? 'fa-cube');
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
                                            <i class="<?= $icono ?> mr-1" style="color: <?= !empty($m['color_personalizado']) ? $m['color_personalizado'] : ($m['color_fondo'] ?? '#6c757d') ?>"></i>
                                            <?= htmlspecialchars($m['nombre']) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                                    <?php if (empty($modulos)): ?>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            Swal.fire({
                                                icon: 'info',
                                                title: 'Sin módulos disponibles',
                                                text: 'No hay módulos disponibles. Cree módulos desde la sección de Módulos.',
                                                confirmButtonText: 'Entendido'
                                            });
                                        });
                                    </script>
                                    <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Suscripción y Estado -->
                <div class="col-md-4">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-crown mr-2"></i>Plan y Suscripción</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="plan_id">Plan <span class="text-danger">*</span></label>
                                <select class="form-control" id="plan_id" name="plan_id" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($planes as $p): ?>
                                    <option value="<?= $p['sus_plan_id'] ?>" <?= ($tenant['ten_plan_id'] ?? '') == $p['sus_plan_id'] ? 'selected' : '' ?> data-usuarios="<?= $p['sus_usuarios_incluidos'] ?? 0 ?>">
                                        <?= htmlspecialchars($p['sus_nombre']) ?> - $
                                        <?= isset($p['sus_precio_mensual']) ? number_format($p['sus_precio_mensual'], 2) : '0.00' ?>/mes
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="usuarios_permitidos">Usuarios Permitidos</label>
                                <input type="number" class="form-control" id="usuarios_permitidos" name="usuarios_permitidos" value="<?= $tenant['ten_usuarios_permitidos'] ?? 5 ?>" min="1">
                            </div>
                            <div class="form-group">
                                <label for="fecha_inicio">Fecha Inicio <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= $tenant['ten_fecha_inicio'] ?? date('Y-m-d') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="fecha_vencimiento">Fecha Vencimiento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" value="<?= $tenant['ten_fecha_vencimiento'] ?? date('Y-m-d', strtotime('+1 month')) ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-cog mr-2"></i>Configuración</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <select class="form-control" id="estado" name="estado">
                                    <option value="A" <?= ($tenant['ten_estado'] ?? 'A') == 'A' ? 'selected' : '' ?>>Activo</option>
                                    <option value="S" <?= ($tenant['ten_estado'] ?? '') == 'S' ? 'selected' : '' ?>>Suspendido</option>
                                    <option value="I" <?= ($tenant['ten_estado'] ?? '') == 'I' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="codigo_tenant">Código Tenant</label>
                                <input type="text" class="form-control" id="codigo_tenant" name="codigo_tenant" value="<?= htmlspecialchars($tenant['ten_codigo_tenant'] ?? '') ?>" placeholder="Auto-generado si se deja vacío">
                                <small class="text-muted">Identificador único del tenant</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i>
                                <?= $esEdicion ? 'Actualizar' : 'Crear' ?> Tenant
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
    // Actualizar usuarios según plan
    const planSelect = document.getElementById('plan_id');
    const usuariosInput = document.getElementById('usuarios_permitidos');
    planSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const usuarios = option.getAttribute('data-usuarios');
        if (usuarios) {
            usuariosInput.value = usuarios;
        }
    });
    // Auto calcular fecha vencimiento
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaVencimiento = document.getElementById('fecha_vencimiento');
    fechaInicio.addEventListener('change', function() {
        if (this.value) {
            const fecha = new Date(this.value);
            fecha.setMonth(fecha.getMonth() + 1);
            fechaVencimiento.value = fecha.toISOString().split('T')[0];
        }
    });

    // Interceptar submit para confirmación y AJAX
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Guardar cambios?',
            text: '¿Deseas confirmar la actualización de este tenant?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar por AJAX
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: data.success ? 'success' : 'error',
                        title: data.message || (data.success ? 'Cambios guardados correctamente' : 'Error al guardar'),
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true
                    });
                })
                .catch(() => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Error de red o servidor',
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true
                    });
                });
            }
        });
    });
});
</script>
