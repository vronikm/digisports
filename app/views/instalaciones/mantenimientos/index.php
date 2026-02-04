<?php
/**
 * Listado de Mantenimientos
 * @var array $mantenimientos
 * @var array $canchas
 * @var string $estado
 * @var int $cancha_id
 */
$baseUrl = \Config::get('base_url');
$estadosColores = [
    'PROGRAMADO' => 'primary',
    'EN_PROGRESO' => 'warning',
    'COMPLETADO' => 'success',
    'CANCELADO' => 'danger'
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
            <a href="<?php echo $baseUrl; ?>instalaciones/mantenimiento/crear" class="btn btn-primary btn-sm">
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
                        <?php echo array_sum(array_map(fn($m) => $m['estado'] === 'PROGRAMADO' ? 1 : 0, $mantenimientos)); ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">En Progreso</h6>
                    <h4 class="fw-bold text-warning">
                        <?php echo array_sum(array_map(fn($m) => $m['estado'] === 'EN_PROGRESO' ? 1 : 0, $mantenimientos)); ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Completados</h6>
                    <h4 class="fw-bold text-success">
                        <?php echo array_sum(array_map(fn($m) => $m['estado'] === 'COMPLETADO' ? 1 : 0, $mantenimientos)); ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="<?php echo $baseUrl; ?>instalaciones/mantenimiento/index" class="row g-3">
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
                                        <a href="<?php echo $baseUrl; ?>instalaciones/mantenimiento/editar?id=<?php echo $mnt['mantenimiento_id']; ?>" 
                                           class="btn btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php if ($mnt['estado'] !== 'COMPLETADO' && $mnt['estado'] !== 'CANCELADO'): ?>
                                            <div class="btn-group dropup">
                                                <button type="button" class="btn btn-outline-warning btn-sm dropdown-toggle" 
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <?php if ($mnt['estado'] !== 'EN_PROGRESO'): ?>
                                                        <li>
                                                            <a class="dropdown-item" 
                                                               href="<?php echo $baseUrl; ?>instalaciones/mantenimiento/cambiarEstado?id=<?php echo $mnt['mantenimiento_id']; ?>&estado=EN_PROGRESO">
                                                                En Progreso
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <a class="dropdown-item" 
                                                           href="<?php echo $baseUrl; ?>instalaciones/mantenimiento/cambiarEstado?id=<?php echo $mnt['mantenimiento_id']; ?>&estado=COMPLETADO">
                                                            Marcar Completado
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" 
                                                           href="<?php echo $baseUrl; ?>instalaciones/mantenimiento/cambiarEstado?id=<?php echo $mnt['mantenimiento_id']; ?>&estado=CANCELADO">
                                                            Cancelar
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <a href="<?php echo $baseUrl; ?>instalaciones/mantenimiento/eliminar?id=<?php echo $mnt['mantenimiento_id']; ?>" 
                                           class="btn btn-outline-danger" title="Eliminar"
                                           onclick="return confirm('¿Eliminar este mantenimiento?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
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
                        <a class="page-link" href="<?php echo $baseUrl; ?>instalaciones/mantenimiento/index?pagina=1">
                            Inicio
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo $baseUrl; ?>instalaciones/mantenimiento/index?pagina=<?php echo $pagina - 1; ?>">
                            Anterior
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $pagina ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo $baseUrl; ?>instalaciones/mantenimiento/index?pagina=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagina < $totalPaginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo $baseUrl; ?>instalaciones/mantenimiento/index?pagina=<?php echo $pagina + 1; ?>">
                            Siguiente
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo $baseUrl; ?>instalaciones/mantenimiento/index?pagina=<?php echo $totalPaginas; ?>">
                            Fin
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
