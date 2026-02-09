<?php
/**
 * Listado de Mantenimientos — DigiSports Arena
 * @var array $mantenimientos
 * @var array $canchas
 * @var string $estado
 * @var int $cancha_id
 * @var int $totalRegistros
 * @var int $pagina
 * @var int $totalPaginas
 */
$urlIndex = url('instalaciones', 'mantenimiento', 'index');
$urlCrear = url('instalaciones', 'mantenimiento', 'crear');
$estadosColores = [
    'PROGRAMADO'  => 'primary',
    'EN_PROGRESO' => 'warning',
    'COMPLETADO'  => 'success',
    'CANCELADO'   => 'danger'
];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="h3 fw-bold">
                <i class="fas fa-wrench text-primary"></i> Gestión de Mantenimientos
            </h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?php echo $urlCrear; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Programar Mantenimiento
            </a>
        </div>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total</h6>
                    <h4 class="fw-bold text-primary"><?php echo $totalRegistros; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Programados</h6>
                    <h4 class="fw-bold text-info">
                        <?php
                        $cnt = 0;
                        foreach ($mantenimientos as $m) { if ($m['estado'] === 'PROGRAMADO') $cnt++; }
                        echo $cnt;
                        ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">En Progreso</h6>
                    <h4 class="fw-bold text-warning">
                        <?php
                        $cnt = 0;
                        foreach ($mantenimientos as $m) { if ($m['estado'] === 'EN_PROGRESO') $cnt++; }
                        echo $cnt;
                        ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Completados</h6>
                    <h4 class="fw-bold text-success">
                        <?php
                        $cnt = 0;
                        foreach ($mantenimientos as $m) { if ($m['estado'] === 'COMPLETADO') $cnt++; }
                        echo $cnt;
                        ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="<?php echo $urlIndex; ?>" class="row g-3">
                <div class="col-md-4">
                    <select name="cancha_id" class="form-select form-select-sm">
                        <option value="">Todas las canchas</option>
                        <?php if (!empty($canchas)): ?>
                            <?php foreach ($canchas as $cancha): ?>
                                <option value="<?php echo $cancha['cancha_id']; ?>" 
                                        <?php echo $cancha_id == $cancha['cancha_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cancha['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <option value="PROGRAMADO" <?php echo $estado === 'PROGRAMADO' ? 'selected' : ''; ?>>Programado</option>
                        <option value="EN_PROGRESO" <?php echo $estado === 'EN_PROGRESO' ? 'selected' : ''; ?>>En Progreso</option>
                        <option value="COMPLETADO" <?php echo $estado === 'COMPLETADO' ? 'selected' : ''; ?>>Completado</option>
                        <option value="CANCELADO" <?php echo $estado === 'CANCELADO' ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de mantenimientos -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cancha</th>
                        <th>Tipo</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($mantenimientos)): ?>
                        <?php foreach ($mantenimientos as $mnt): ?>
                            <tr>
                                <td class="fw-semibold">
                                    <?php echo htmlspecialchars($mnt['cancha_nombre']); ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo ucfirst(str_replace('_', ' ', $mnt['tipo'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?php echo date('d/m/Y H:i', strtotime($mnt['fecha_inicio'])); ?></small>
                                </td>
                                <td>
                                    <small><?php echo date('d/m/Y H:i', strtotime($mnt['fecha_fin'])); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($mnt['responsable_nombre'] ?? 'Sin asignar'); ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $estadosColores[$mnt['estado']] ?? 'secondary'; ?>">
                                        <?php echo str_replace('_', ' ', $mnt['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo url('instalaciones', 'mantenimiento', 'ver', ['id' => $mnt['mantenimiento_id']]); ?>" 
                                           class="btn btn-outline-info" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo url('instalaciones', 'mantenimiento', 'editar', ['id' => $mnt['mantenimiento_id']]); ?>" 
                                           class="btn btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php if ($mnt['estado'] !== 'COMPLETADO' && $mnt['estado'] !== 'CANCELADO'): ?>
                                            <div class="btn-group dropup">
                                                <button type="button" class="btn btn-outline-warning btn-sm dropdown-toggle" 
                                                        data-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <?php if ($mnt['estado'] !== 'EN_PROGRESO'): ?>
                                                        <a class="dropdown-item btn-cambiar-estado" 
                                                           data-id="<?php echo $mnt['mantenimiento_id']; ?>"
                                                           data-estado="EN_PROGRESO" href="#">
                                                            <i class="fas fa-play text-warning mr-1"></i> En Progreso
                                                        </a>
                                                    <?php endif; ?>
                                                    <a class="dropdown-item btn-cambiar-estado" 
                                                       data-id="<?php echo $mnt['mantenimiento_id']; ?>"
                                                       data-estado="COMPLETADO" href="#">
                                                        <i class="fas fa-check text-success mr-1"></i> Completado
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item btn-cambiar-estado text-danger" 
                                                       data-id="<?php echo $mnt['mantenimiento_id']; ?>"
                                                       data-estado="CANCELADO" href="#">
                                                        <i class="fas fa-times mr-1"></i> Cancelar
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="btn btn-outline-danger btn-delete" 
                                                data-id="<?php echo $mnt['mantenimiento_id']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($mnt['cancha_nombre'] . ' - ' . $mnt['tipo']); ?>"
                                                data-url="<?php echo url('instalaciones', 'mantenimiento', 'eliminar', ['id' => $mnt['mantenimiento_id']]); ?>"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No hay mantenimientos registrados
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    <?php if ($totalPaginas > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($pagina > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo url('instalaciones', 'mantenimiento', 'index', ['pagina' => 1, 'estado' => $estado, 'cancha_id' => $cancha_id]); ?>">
                            Inicio
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo url('instalaciones', 'mantenimiento', 'index', ['pagina' => $pagina - 1, 'estado' => $estado, 'cancha_id' => $cancha_id]); ?>">
                            Anterior
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $pagina ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo url('instalaciones', 'mantenimiento', 'index', ['pagina' => $i, 'estado' => $estado, 'cancha_id' => $cancha_id]); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagina < $totalPaginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo url('instalaciones', 'mantenimiento', 'index', ['pagina' => $pagina + 1, 'estado' => $estado, 'cancha_id' => $cancha_id]); ?>">
                            Siguiente
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo url('instalaciones', 'mantenimiento', 'index', ['pagina' => $totalPaginas, 'estado' => $estado, 'cancha_id' => $cancha_id]); ?>">
                            Fin
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cambiar estado con confirmación
    document.querySelectorAll('.btn-cambiar-estado').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.dataset.id;
            var estado = this.dataset.estado;
            var labels = {
                'EN_PROGRESO': 'En Progreso',
                'COMPLETADO': 'Completado',
                'CANCELADO': 'Cancelado'
            };
            
            Swal.fire({
                title: '¿Cambiar estado?',
                html: '<p>Se cambiará el estado a: <strong>' + (labels[estado] || estado) + '</strong></p>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check"></i> Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = '<?php echo url("instalaciones", "mantenimiento", "cambiarEstado"); ?>&id=' + id + '&estado=' + estado;
                }
            });
        });
    });

    // Eliminar con SweetAlert
    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var nombre = this.dataset.nombre;
            var urlEliminar = this.dataset.url;
            
            Swal.fire({
                title: '¿Eliminar mantenimiento?',
                html: '<p>Estás a punto de eliminar:</p><p class="font-weight-bold text-danger">' + nombre + '</p>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = urlEliminar;
                }
            });
        });
    });
});
</script>
