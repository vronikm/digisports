<?php
/**
 * Vista: Listado de Clientes
 */

$clientes = $clientes ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$filtros = $filtros ?? [];
$tiposCliente = $tiposCliente ?? [];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-users text-primary"></i>
                    Gestión de Clientes
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('core', 'hub', 'index') ?>">Hub</a></li>
                    <li class="breadcrumb-item active">Clientes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Filtros y acciones -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i> Filtros
                </h3>
                <div class="card-tools">
                    <a href="<?= url('clientes', 'cliente', 'crear') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nuevo Cliente
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= url('clientes', 'cliente', 'index') ?>" class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Buscar</label>
                            <input type="text" name="buscar" class="form-control" 
                                   placeholder="Nombre, cédula o email..."
                                   value="<?= htmlspecialchars($filtros['buscar'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipo</label>
                            <select name="tipo" class="form-control">
                                <option value="">Todos</option>
                                <?php foreach ($tiposCliente as $key => $label): ?>
                                <option value="<?= $key ?>" <?= ($filtros['tipo'] ?? '') === $key ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="estado" class="form-control">
                                <option value="A" <?= ($filtros['estado'] ?? 'A') === 'A' ? 'selected' : '' ?>>Activos</option>
                                <option value="I" <?= ($filtros['estado'] ?? '') === 'I' ? 'selected' : '' ?>>Inactivos</option>
                                <option value="">Todos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="<?= url('clientes', 'cliente', 'index') ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tabla de clientes -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i>
                    Listado de Clientes
                    <span class="badge badge-info"><?= number_format($total) ?> registros</span>
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Identificación</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Tipo</th>
                            <th>Reservas</th>
                            <th>Saldo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No se encontraron clientes
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td>
                                <code><?= htmlspecialchars($cliente['identificacion']) ?></code>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($cliente['nombres'] . ' ' . $cliente['apellidos']) ?></strong>
                                <?php if ($cliente['tipo_cliente'] === 'EMPRESA' && $cliente['razon_social']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($cliente['razon_social']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($cliente['email'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($cliente['celular'] ?? $cliente['telefono'] ?? '-') ?></td>
                            <td>
                                <?php
                                $badgeClass = [
                                    'SOCIO' => 'badge-success',
                                    'CLIENTE' => 'badge-info',
                                    'EMPRESA' => 'badge-primary',
                                    'INVITADO' => 'badge-secondary'
                                ][$cliente['tipo_cliente']] ?? 'badge-secondary';
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars($tiposCliente[$cliente['tipo_cliente']] ?? $cliente['tipo_cliente']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-light">
                                    <?= number_format($cliente['total_reservas'] ?? 0) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (($cliente['saldo_favor'] ?? 0) > 0): ?>
                                <span class="text-success font-weight-bold">
                                    $<?= number_format($cliente['saldo_favor'], 2) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">$0.00</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('clientes', 'cliente', 'ver', ['id' => $cliente['cliente_id']]) ?>" 
                                       class="btn btn-info" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= url('clientes', 'cliente', 'editar', ['id' => $cliente['cliente_id']]) ?>" 
                                       class="btn btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger" title="Eliminar"
                                            onclick="eliminarCliente(<?= $cliente['cli_cliente_id'] ?>, '<?= htmlspecialchars($cliente['cli_nombres']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination pagination-sm m-0 justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= url('clientes', 'cliente', 'index', array_merge($filtros, ['page' => $page - 1])) ?>">
                                &laquo;
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('clientes', 'cliente', 'index', array_merge($filtros, ['page' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= url('clientes', 'cliente', 'index', array_merge($filtros, ['page' => $page + 1])) ?>">
                                &raquo;
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
        
    </div>
</section>

<script>
function eliminarCliente(id, nombre) {
    Swal.fire({
        title: '¿Eliminar cliente?',
        text: `¿Está seguro de eliminar a "${nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= url('clientes', 'cliente', 'eliminar') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'id=' + id + '&csrf_token=<?= \Security::generateCsrfToken() ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Eliminado', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Error al procesar la solicitud', 'error');
            });
        }
    });
}
</script>
