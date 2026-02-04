<?php
/**
 * Listado de reservas del usuario/administrador
 */
$urlIndex = url('reservas', 'reserva', 'index');
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">
                <i class="fas fa-calendar-check text-primary"></i> Gestión de Reservas
            </h1>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-body p-2">
                    <form method="post" action="<?= $urlIndex ?>" class="form-inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="form-group mr-2">
                            <label for="estado" class="mr-2">Estado:</label>
                            <select class="form-control form-control-sm" id="estado" name="estado">
                                <option value="">-- Todos --</option>
                                <option value="PENDIENTE" <?= ($estado ?? '') === 'PENDIENTE' ? 'selected' : '' ?>>
                                    Pendiente
                                </option>
                                <option value="CONFIRMADA" <?= ($estado ?? '') === 'CONFIRMADA' ? 'selected' : '' ?>>
                                    Confirmada
                                </option>
                                <option value="COMPLETADA" <?= ($estado ?? '') === 'COMPLETADA' ? 'selected' : '' ?>>
                                    Completada
                                </option>
                                <option value="CANCELADA" <?= ($estado ?? '') === 'CANCELADA' ? 'selected' : '' ?>>
                                    Cancelada
                                </option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="<?= url('reservas', 'reserva', 'buscar') ?>" class="btn btn-success btn-sm ml-2">
                            <i class="fas fa-plus"></i> Nueva Reserva
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Reservas -->
    <div class="row">
        <div class="col-md-12">
            <?php if (!empty($reservas)): ?>
                
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Instalación</th>
                                <th>Fecha</th>
                                <th>Horario</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= $reserva['reserva_id'] ?></strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(($reserva['cliente_nombre'] ?? '') . ' ' . ($reserva['cliente_apellidos'] ?? '')) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($reserva['instalacion_nombre'] ?? 'N/A') ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($reserva['fecha_reserva'])) ?>
                                    </td>
                                    <td>
                                        <?= substr($reserva['hora_inicio'], 0, 5) ?> - <?= substr($reserva['hora_fin'], 0, 5) ?>
                                    </td>
                                    <td>
                                        <strong>$<?= number_format($reserva['precio_total'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $estadoBadge = [
                                            'PENDIENTE' => 'warning',
                                            'CONFIRMADA' => 'success',
                                            'COMPLETADA' => 'info',
                                            'CANCELADA' => 'danger'
                                        ][$reserva['estado']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $estadoBadge ?>">
                                            <?= htmlspecialchars($reserva['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= url('reservas', 'reserva', 'ver', ['id' => $reserva['reserva_id']]) ?>" 
                                               class="btn btn-outline-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <?php if ($reserva['estado'] === 'PENDIENTE'): ?>
                                                <button type="button" class="btn btn-outline-success btn-confirmar" 
                                                        data-id="<?= $reserva['reserva_id'] ?>"
                                                        data-url="<?= url('reservas', 'reserva', 'confirmar', ['id' => $reserva['reserva_id']]) ?>"
                                                        title="Confirmar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if (in_array($reserva['estado'], ['PENDIENTE', 'CONFIRMADA'])): ?>
                                                <button type="button" class="btn btn-outline-danger btn-cancelar" 
                                                        data-id="<?= $reserva['reserva_id'] ?>"
                                                        data-url="<?= url('reservas', 'reserva', 'cancelar', ['id' => $reserva['reserva_id']]) ?>"
                                                        title="Cancelar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagina > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= url('reservas', 'reserva', 'index', ['pagina' => 1, 'estado' => $estado]) ?>">
                                        <i class="fas fa-chevron-left"></i> Primera
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?= url('reservas', 'reserva', 'index', ['pagina' => $pagina - 1, 'estado' => $estado]) ?>">
                                        Anterior
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                                <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('reservas', 'reserva', 'index', ['pagina' => $i, 'estado' => $estado]) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagina < $totalPaginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= url('reservas', 'reserva', 'index', ['pagina' => $pagina + 1, 'estado' => $estado]) ?>">
                                        Siguiente
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?= url('reservas', 'reserva', 'index', ['pagina' => $totalPaginas, 'estado' => $estado]) ?>">
                                        Última <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <div class="text-center text-muted">
                        <small>Mostrando <?= count($reservas) ?> de <?= $totalRegistros ?> reservas</small>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
                    <strong>No hay reservas registradas</strong><br>
                    <a href="<?= url('reservas', 'reserva', 'buscar') ?>" class="btn btn-success btn-sm mt-2">
                        <i class="fas fa-plus"></i> Crear una nueva reserva
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirmar reserva con SweetAlert2
    document.querySelectorAll('.btn-confirmar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const urlConfirmar = this.dataset.url;
            
            DigiAlert.confirm({
                title: '¿Confirmar reserva?',
                text: 'La reserva quedará confirmada y se notificará al cliente.',
                icon: 'question',
                confirmButtonText: '<i class="fas fa-check"></i> Sí, confirmar',
                confirmButtonColor: '#08DC64'
            }, function() {
                DigiAlert.loading('Confirmando reserva...');
                window.location.href = urlConfirmar;
            });
        });
    });
    
    // Cancelar reserva con SweetAlert2
    document.querySelectorAll('.btn-cancelar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const urlCancelar = this.dataset.url;
            
            Swal.fire({
                title: '¿Cancelar reserva?',
                html: `<p>Esta acción no se puede deshacer.</p>
                       <div class="form-group text-left">
                           <label for="motivo">Motivo (opcional):</label>
                           <textarea id="swal-motivo" class="swal2-textarea" placeholder="Indique el motivo..."></textarea>
                       </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-times"></i> Sí, cancelar',
                cancelButtonText: 'No, mantener',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    DigiAlert.loading('Cancelando reserva...');
                    const motivo = document.getElementById('swal-motivo')?.value || '';
                    window.location.href = urlCancelar + '&motivo=' + encodeURIComponent(motivo);
                }
            });
        });
    });
});
</script>
