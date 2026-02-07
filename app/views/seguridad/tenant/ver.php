<?php
/**
 * DigiSports Seguridad - Ver Tenant
 */

$tenant = $tenant ?? [];
$usuarios = $usuarios ?? [];
$modulos = $modulos ?? [];
$actividad = $actividad ?? [];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-building mr-2"></i>
                    <?= htmlspecialchars($tenant['nombre_comercial'] ?? $tenant['razon_social'] ?? '-') ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('seguridad', 'tenant', 'editar', ['id' => $tenant['tenant_id']]) ?>" class="btn btn-primary">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </a>
                    <a href="<?= url('seguridad', 'tenant') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Información Principal -->
            <div class="col-md-4">
                <!-- Tarjeta Resumen -->
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <div class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                        <h3 class="profile-username text-center mt-3">
                            <?= htmlspecialchars($tenant['nombre_comercial'] ?? $tenant['razon_social'] ?? '-') ?>
                        </h3>
                        <p class="text-muted text-center">RUC: <?= $tenant['ruc'] ?></p>
                        
                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Estado</b>
                                <span class="float-right">
                                    <?php 
                                    $estadoClass = match($tenant['estado']) {
                                        'A' => 'success',
                                        'S' => 'warning',
                                        'I' => 'secondary',
                                        default => 'secondary'
                                    };
                                    $estadoText = match($tenant['estado']) {
                                        'A' => 'Activo',
                                        'S' => 'Suspendido',
                                        'I' => 'Inactivo',
                                        default => 'Desconocido'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $estadoClass ?>"><?= $estadoText ?></span>
                                </span>
                            </li>
                            <li class="list-group-item">
                                <b>Plan</b>
                                <span class="float-right badge badge-info"><?= htmlspecialchars($tenant['plan_nombre'] ?? 'Sin plan') ?></span>
                            </li>
                            <li class="list-group-item">
                                <b>Usuarios</b>
                                <span class="float-right"><?= count($usuarios) ?> / <?= $tenant['usuarios_permitidos'] ?? '-' ?></span>
                            </li>
                            <li class="list-group-item">
                                <b>Módulos</b>
                                <span class="float-right"><?= count($modulos) ?></span>
                            </li>
                        </ul>
                        
                        <?php if ($tenant['estado'] == 'A'): ?>
                        <a href="#" class="btn btn-warning btn-block btn-suspender" data-url="<?= url('seguridad', 'tenant', 'suspender', ['id' => $tenant['tenant_id']]) ?>">
                            <i class="fas fa-pause mr-1"></i> Suspender
                        </a>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var btn = document.querySelector('.btn-suspender');
                            if (btn) {
                                btn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const url = btn.getAttribute('data-url');
                                    Swal.fire({
                                        title: '¿Suspender tenant?',
                                        text: 'Esta acción suspenderá el acceso del tenant. ¿Desea continuar?',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#d33',
                                        cancelButtonColor: '#3085d6',
                                        confirmButtonText: 'Sí, suspender',
                                        cancelButtonText: 'Cancelar'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.success) {
                                                        Swal.fire({
                                                            toast: true,
                                                            position: 'top-end',
                                                            icon: 'success',
                                                            title: 'Tenant suspendido correctamente',
                                                            showConfirmButton: false,
                                                            timer: 1800
                                                        });
                                                        setTimeout(function() { location.reload(); }, 1200);
                                                    } else {
                                                        Swal.fire({
                                                            icon: 'error',
                                                            title: 'Error',
                                                            text: data.message || 'No se pudo suspender el tenant.'
                                                        });
                                                    }
                                                })
                                                .catch(() => {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Error',
                                                        text: 'No se pudo conectar con el servidor.'
                                                    });
                                                });
                                        }
                                    });
                                });
                            }
                        });
                        </script>
                        <?php else: ?>
                        <a href="#" class="btn btn-success btn-block btn-reactivar" data-url="<?= url('seguridad', 'tenant', 'reactivar', ['id' => $tenant['tenant_id']]) ?>">
                            <i class="fas fa-play mr-1"></i> Reactivar
                        </a>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var btn = document.querySelector('.btn-reactivar');
                            if (btn) {
                                btn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const url = btn.getAttribute('data-url');
                                    Swal.fire({
                                        title: '¿Reactivar tenant?',
                                        text: 'Esta acción reactivará el acceso del tenant. ¿Desea continuar?',
                                        icon: 'question',
                                        showCancelButton: true,
                                        confirmButtonColor: '#28a745',
                                        cancelButtonColor: '#3085d6',
                                        confirmButtonText: 'Sí, reactivar',
                                        cancelButtonText: 'Cancelar'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.success) {
                                                        Swal.fire({
                                                            toast: true,
                                                            position: 'top-end',
                                                            icon: 'success',
                                                            title: 'Tenant reactivado correctamente',
                                                            showConfirmButton: false,
                                                            timer: 1800
                                                        });
                                                        setTimeout(function() { location.reload(); }, 1200);
                                                    } else {
                                                        Swal.fire({
                                                            icon: 'error',
                                                            title: 'Error',
                                                            text: data.message || 'No se pudo reactivar el tenant.'
                                                        });
                                                    }
                                                })
                                                .catch(() => {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Error',
                                                        text: 'No se pudo conectar con el servidor.'
                                                    });
                                                });
                                        }
                                    });
                                });
                            }
                        });
                        </script>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Suscripción -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Suscripción</h3>
                    </div>
                    <div class="card-body">
                        <?php 
                        $dias = $tenant['dias_restantes'] ?? 0;
                        $progressClass = $dias > 30 ? 'bg-success' : ($dias > 0 ? 'bg-warning' : 'bg-danger');
                        $progressPct = min(100, max(0, ($dias / 30) * 100));
                        ?>
                        <div class="text-center mb-3">
                            <h2 class="<?= $dias > 30 ? 'text-success' : ($dias > 0 ? 'text-warning' : 'text-danger') ?>">
                                <?= $dias > 0 ? $dias : 'Vencido' ?>
                            </h2>
                            <small class="text-muted"><?= $dias > 0 ? 'días restantes' : 'hace ' . abs($dias) . ' días' ?></small>
                        </div>
                        <div class="progress mb-3">
                            <div class="progress-bar <?= $progressClass ?>" style="width: <?= $progressPct ?>%"></div>
                        </div>
                        <table class="table table-sm">
                            <tr>
                                <td>Inicio:</td>
                                <td class="text-right"><?= date('d/m/Y', strtotime($tenant['fecha_inicio'])) ?></td>
                            </tr>
                            <tr>
                                <td>Vencimiento:</td>
                                <td class="text-right"><?= date('d/m/Y', strtotime($tenant['fecha_vencimiento'])) ?></td>
                            </tr>
                        </table>
                        <a href="#" class="btn btn-info btn-block btn-renovar" data-url="<?= url('seguridad', 'tenant', 'renovar', ['id' => $tenant['tenant_id']]) ?>">
                            <i class="fas fa-sync mr-1"></i> Renovar Suscripción
                        </a>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var btn = document.querySelector('.btn-renovar');
                            if (btn) {
                                btn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const url = btn.getAttribute('data-url');
                                    Swal.fire({
                                        title: 'Renovar suscripción',
                                        html: '<label>Meses a renovar:</label><input id="mesesInput" type="number" min="1" max="36" value="12" class="swal2-input" style="width:120px">',
                                        icon: 'info',
                                        showCancelButton: true,
                                        confirmButtonColor: '#17a2b8',
                                        cancelButtonColor: '#3085d6',
                                        confirmButtonText: 'Renovar',
                                        cancelButtonText: 'Cancelar',
                                        preConfirm: () => {
                                            const meses = document.getElementById('mesesInput').value;
                                            if (!meses || meses < 1 || meses > 36) {
                                                Swal.showValidationMessage('Ingrese un valor entre 1 y 36');
                                                return false;
                                            }
                                            return meses;
                                        }
                                    }).then((result) => {
                                        if (result.isConfirmed && result.value) {
                                            fetch(url, {
                                                method: 'POST',
                                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                                                body: 'meses=' + encodeURIComponent(result.value)
                                            })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    Swal.fire({
                                                        toast: true,
                                                        position: 'top-end',
                                                        icon: 'success',
                                                        title: 'Suscripción renovada correctamente',
                                                        showConfirmButton: false,
                                                        timer: 1800
                                                    });
                                                    setTimeout(function() { location.reload(); }, 1200);
                                                } else {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Error',
                                                        text: data.message || 'No se pudo renovar la suscripción.'
                                                    });
                                                }
                                            })
                                            .catch(() => {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Error',
                                                    text: 'No se pudo conectar con el servidor.'
                                                });
                                            });
                                        }
                                    });
                                });
                            }
                        });
                        </script>
                    </div>
                </div>
            </div>
            
            <!-- Contenido Principal -->
            <div class="col-md-8">
                <!-- Datos de Contacto -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-address-card mr-2"></i>Datos de Contacto</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl>
                                    <dt>Razón Social</dt>
                                    <dd><?= htmlspecialchars($tenant['razon_social'] ?? '-') ?></dd>
                                    
                                    <dt>Nombre Comercial</dt>
                                    <dd><?= htmlspecialchars($tenant['nombre_comercial'] ?? '-') ?></dd>
                                    
                                    <dt>Representante Legal</dt>
                                    <dd><?= isset($tenant['representante_legal']) ? htmlspecialchars($tenant['representante_legal']) : '-' ?></dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl>
                                    <dt>Email</dt>
                                    <dd><a href="mailto:<?= $tenant['email'] ?? '' ?>"><?= htmlspecialchars($tenant['email'] ?? '-') ?></a></dd>
                                    
                                    <dt>Teléfono</dt>
                                    <dd><?= htmlspecialchars($tenant['telefono'] ?? '-') ?></dd>
                                    
                                    <dt>Dirección</dt>
                                    <dd><?= htmlspecialchars($tenant['direccion'] ?? '-') ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Módulos Asignados -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-puzzle-piece mr-2"></i>Módulos Asignados (<?= count($modulos) ?>)</h3>
                        <div class="card-tools">
                            <a href="<?= url('seguridad', 'asignacion', 'modulos', ['tenant_id' => $tenant['tenant_id']]) ?>" class="btn btn-tool">
                                <i class="fas fa-cog"></i> Gestionar
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($modulos)): ?>
                        <p class="text-muted text-center">No hay módulos asignados</p>
                        <?php else: ?>
                        <div class="row">
                            <?php foreach ($modulos as $m): ?>
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="info-box mb-0">
                                    <?php
                                    // Prioridad: icono_personalizado > icono_sistema > por defecto
                                    $icono = !empty($m['icono_personalizado']) ? $m['icono_personalizado'] : (!empty($m['icono_sistema']) ? $m['icono_sistema'] : 'fas fa-cube');
                                    // Forzar prefijo 'fas ' si solo tiene 'fa-'
                                    if (strpos($icono, 'fas ') !== 0 && strpos($icono, 'fa-') === 0) {
                                        $icono = 'fas ' . $icono;
                                    }
                                    $color = !empty($m['color_personalizado']) ? $m['color_personalizado'] : (!empty($m['color_sistema']) ? $m['color_sistema'] : '#6c757d');
                                    ?>
                                    <span class="info-box-icon" style="background-color: <?= htmlspecialchars($color) ?>; color: white;">
                                        <i class="<?= htmlspecialchars($icono) ?>"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text"><?= htmlspecialchars($m['nombre']) ?></span>
                                        <span class="info-box-number text-muted"><?= htmlspecialchars($m['codigo'] ?? '') ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Usuarios del Tenant -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Usuarios (<?= count($usuarios) ?>)</h3>
                        <div class="card-tools">
                            <a href="<?= url('seguridad', 'usuario', 'crear', ['tenant_id' => $tenant['tenant_id']]) ?>" class="btn btn-tool">
                                <i class="fas fa-plus"></i> Agregar
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay usuarios registrados</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td>
                                        <img src="<?= $u['avatar'] ?? '/assets/img/avatar-default.png' ?>" class="img-circle elevation-1 mr-2" width="25" alt="">
                                        <?= htmlspecialchars($u['nombre_completo'] ?? '') ?>
                                    </td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($u['rol_nombre'] ?? 'Sin rol') ?></span></td>
                                    <td>
                                        <?php 
                                        $uEstado = match($u['estado']) {
                                            'A' => ['success', 'Activo'],
                                            'I' => ['secondary', 'Inactivo'],
                                            'B' => ['danger', 'Bloqueado'],
                                            default => ['secondary', 'Desconocido']
                                        };
                                        ?>
                                        <span class="badge badge-<?= $uEstado[0] ?>"><?= $uEstado[1] ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
