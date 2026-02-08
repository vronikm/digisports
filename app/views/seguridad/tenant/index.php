<?php
/**
 * DigiSports Seguridad - Lista de Tenants
 */

$tenants = $tenants ?? [];
$planes = $planes ?? [];
$filtros = $filtros ?? [];
$total = $total ?? 0;
$pagina = $pagina ?? 1;
$totalPaginas = $totalPaginas ?? 1;
?>

<!-- Header Premium -->
 <section class="content pt-3">
    <div class="container-fluid">
<?php
$headerTitle    = 'Gestión de Tenants';
$headerSubtitle = 'Administración de empresas y suscripciones';
$headerIcon     = 'fas fa-building';
$headerButtons  = [
    ['url' => url('seguridad', 'tenant', 'crear'), 'label' => 'Nuevo Tenant', 'icon' => 'fas fa-plus', 'solid' => true],
    ['url' => url('seguridad', 'tenant', 'suscripciones'), 'label' => 'Suscripciones', 'icon' => 'fas fa-calendar-alt', 'solid' => false],
];
include __DIR__ . '/../partials/header.php';
?>
        <!-- Filtros -->
        <div class="card card-outline card-primary collapsed-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filtros</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Plan</label>
                                <select name="plan_id" class="form-control">
                                    <option value="">-- Todos --</option>
                                    <?php foreach ($planes as $p): ?>
                                    <option value="<?= $p['sus_plan_id'] ?>" <?= ($filtros['plan_id'] ?? '') == $p['sus_plan_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['sus_nombre']) ?>
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
                                    <option value="S" <?= ($filtros['estado'] ?? '') == 'S' ? 'selected' : '' ?>>Suspendido</option>
                                    <option value="I" <?= ($filtros['estado'] ?? '') == 'I' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Buscar</label>
                                <input type="text" name="buscar" class="form-control" placeholder="RUC, razón social, email..." value="<?= htmlspecialchars($filtros['buscar'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search mr-1"></i> Buscar
                                    </button>
                                    <a href="<?= url('seguridad', 'tenant', 'index') ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lista -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tenants (<?= $total ?>)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Plan</th>
                            <th>Usuarios</th>
                            <th>Módulos</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th width="150">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tenants)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-building fa-3x mb-3 d-block"></i>
                                No se encontraron tenants
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($tenants as $t): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($t['ten_nombre_comercial'] ?: $t['ten_razon_social']) ?></strong>
                                    <br><small class="text-muted">RUC: <?= $t['ten_ruc'] ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= htmlspecialchars($t['plan_nombre'] ?? 'Sin plan') ?></span>
                                </td>
                                <td>
                                    <i class="fas fa-users mr-1"></i>
                                    <?= $t['usuarios_count'] ?? 0 ?>/<?= $t['ten_usuarios_permitidos'] ?>
                                </td>
                                <td>
                                    <i class="fas fa-puzzle-piece mr-1"></i>
                                    <?= $t['modulos_count'] ?? 0 ?>
                                </td>
                                <td>
                                    <?php 
                                    $dias = isset($t['dias_restantes']) ? (int)$t['dias_restantes'] : 0;
                                    $badgeClass = $dias > 30 ? 'success' : ($dias > 0 ? 'warning' : 'danger');
                                    $fechaVenc = isset($t['ten_fecha_vencimiento']) && $t['ten_fecha_vencimiento'] ? $t['ten_fecha_vencimiento'] : null;
                                    ?>
                                    <span class="badge badge-<?= $badgeClass ?>">
                                        <?php if ($dias > 0): ?>
                                        <?= $dias ?> días
                                        <?php elseif ($dias == 0): ?>
                                        Hoy
                                        <?php else: ?>
                                        Vencido (<?= abs($dias) ?>d)
                                        <?php endif; ?>
                                    </span>
                                    <br><small><?= $fechaVenc ? date('d/m/Y', strtotime($fechaVenc)) : '—' ?></small>
                                </td>
                                <td>
                                    <?php 
                                    $estadoVal = $t['ten_estado'] ?? '';
                                    $estadoClass = 'secondary';
                                    $estadoText = 'Desconocido';
                                    switch ($estadoVal) {
                                        case 'A': $estadoClass = 'success'; $estadoText = 'Activo'; break;
                                        case 'S': $estadoClass = 'warning'; $estadoText = 'Suspendido'; break;
                                        case 'I': $estadoClass = 'secondary'; $estadoText = 'Inactivo'; break;
                                    }
                                    ?>
                                    <span class="badge badge-<?= $estadoClass ?>"><?= $estadoText ?></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('seguridad', 'tenant', 'ver', ['id' => $t['ten_tenant_id']]) ?>" class="btn btn-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= url('seguridad', 'tenant', 'editar', ['id' => $t['ten_tenant_id']]) ?>" class="btn btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($t['ten_estado'] == 'A'): ?>
                                        <a href="#" class="btn btn-warning btn-suspender" data-url="<?= url('seguridad', 'tenant', 'suspender', ['id' => $t['ten_tenant_id']]) ?>" title="Suspender">
                                            <i class="fas fa-pause"></i>
                                        </a>
                                        <?php /* SweetAlert2 para suspender tenant */ ?>
                                        <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            document.querySelectorAll('.btn-suspender').forEach(function(btn) {
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
                                                                            timer: 2000
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
                                            });
                                        });
                                        </script>
                                        <?php else: ?>
                                        <a href="#" class="btn btn-success btn-reactivar" data-url="<?= url('seguridad', 'tenant', 'reactivar', ['id' => $t['ten_tenant_id']]) ?>" title="Reactivar">
                                            <i class="fas fa-play"></i>
                                        </a>
                                        <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            document.querySelectorAll('.btn-reactivar').forEach(function(btn) {
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
                                                                        setTimeout(function() { location.reload(); }, 1800);
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
                                            });
                                        });
                                        </script>
                                        <?php endif; ?>
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
                        <?php for ($i = 1; $i <= min($totalPaginas, 10); $i++): ?>
                        <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('seguridad', 'tenant', 'index', array_merge($filtros, ['pagina' => $i])) ?>">
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
