<?php
/**
 * Listado de Canchas/Instalaciones
 * @var array $canchas
 * @var string $buscar
 * @var string $tipo
 * @var string $estado
 */
$base = baseUrl();
// URLs encriptadas
$urlCrear = url('instalaciones', 'cancha', 'crear');
$urlIndex = url('instalaciones', 'cancha', 'index');
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="h3 fw-bold">
                <i class="fas fa-th-large text-primary"></i> Gestión de Canchas
            </h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?php echo $urlCrear; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nueva Cancha
            </a>
        </div>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Canchas</h6>
                    <h4 class="fw-bold text-primary"><?php echo count($canchas); ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Registros</h6>
                    <h4 class="fw-bold text-info"><?php echo $totalRegistros; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Activas</h6>
                    <h4 class="fw-bold text-success">
                        <?php echo array_sum(array_map(fn($c) => $c['estado'] === 'ACTIVO' ? 1 : 0, $canchas)); ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Inactivas</h6>
                    <h4 class="fw-bold text-warning">
                        <?php echo array_sum(array_map(fn($c) => $c['estado'] !== 'ACTIVO' ? 1 : 0, $canchas)); ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="<?php echo $base; ?>instalaciones/cancha/index" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="buscar" class="form-control form-control-sm" 
                           placeholder="Buscar cancha..." value="<?php echo htmlspecialchars($buscar); ?>">
                </div>
                <div class="col-md-3">
                    <select name="tipo" class="form-select form-select-sm">
                        <option value="">Todos los tipos</option>
                        <?php if (!empty($tipos)): ?>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?php echo htmlspecialchars($t); ?>" 
                                        <?php echo $tipo === $t ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($t); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <option value="ACTIVO" <?php echo $estado === 'ACTIVO' ? 'selected' : ''; ?>>Activas</option>
                        <option value="INACTIVO" <?php echo $estado === 'INACTIVO' ? 'selected' : ''; ?>>Inactivas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de canchas -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cancha</th>
                        <th>Instalación</th>
                        <th>Tipo</th>
                        <th>Capacidad</th>
                        <th>Reservas Hoy</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($canchas)): ?>
                        <?php foreach ($canchas as $cancha): ?>
                            <tr>
                                <td class="fw-semibold"><?php echo htmlspecialchars($cancha['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cancha['instalacion_nombre']); ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo ucfirst($cancha['tipo']); ?>
                                    </span>
                                </td>
                                <td><?php echo $cancha['capacidad_maxima']; ?> personas</td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo $cancha['total_reservas_hoy']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($cancha['estado'] === 'ACTIVO'): ?>
                                        <span class="badge bg-success">Activa</span>
                                    <?php elseif ($cancha['estado'] === 'INACTIVO'): ?>
                                        <span class="badge bg-warning">Inactiva</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Eliminada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo url('instalaciones', 'cancha', 'tarifas', ['id' => $cancha['cancha_id']]); ?>" 
                                           class="btn btn-outline-info" title="Tarifas">
                                            <i class="fas fa-dollar-sign"></i>
                                        </a>
                                        <a href="<?php echo url('instalaciones', 'cancha', 'editar', ['id' => $cancha['cancha_id']]); ?>" 
                                           class="btn btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-delete" 
                                                data-id="<?php echo $cancha['cancha_id']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($cancha['nombre']); ?>"
                                                data-url="<?php echo url('instalaciones', 'cancha', 'eliminar', ['id' => $cancha['cancha_id'], 'confirmed' => 1]); ?>"
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
                                No hay canchas registradas
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
                        <a class="page-link" href="<?php echo $base; ?>instalaciones/cancha/index?pagina=1&buscar=<?php echo urlencode($buscar); ?>">
                            Inicio
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo $base; ?>instalaciones/cancha/index?pagina=<?php echo $pagina - 1; ?>&buscar=<?php echo urlencode($buscar); ?>">
                            Anterior
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $pagina ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo $base; ?>instalaciones/cancha/index?pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($buscar); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagina < $totalPaginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo $base; ?>instalaciones/cancha/index?pagina=<?php echo $pagina + 1; ?>&buscar=<?php echo urlencode($buscar); ?>">
                            Siguiente
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo $base; ?>instalaciones/cancha/index?pagina=<?php echo $totalPaginas; ?>&buscar=<?php echo urlencode($buscar); ?>">
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
    // Manejar eliminación con SweetAlert2
    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const nombre = this.dataset.nombre;
            const urlEliminar = this.dataset.url;
            
            DigiAlert.confirmDelete(function() {
                // Mostrar loading
                DigiAlert.loading('Eliminando cancha...');
                
                // Redirigir a la acción de eliminar
                window.location.href = urlEliminar;
            }, {
                title: '¿Eliminar cancha?',
                html: `<p>Estás a punto de eliminar la cancha:</p>
                       <p class="font-weight-bold text-danger">${nombre}</p>
                       <p class="small text-muted">Esta acción no se puede deshacer.</p>`,
                confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar'
            });
        });
    });
});
</script>
