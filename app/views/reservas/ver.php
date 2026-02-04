<?php
/**
 * Ver detalles de una reserva específica
 */
$estadoClases = [
    'PENDIENTE' => 'warning',
    'CONFIRMADA' => 'success',
    'COMPLETADA' => 'info',
    'CANCELADA' => 'danger'
];
$badgeClass = $estadoClases[$reserva['estado']] ?? 'secondary';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i>
                        Detalles de Reserva #<?= $reserva['reserva_id'] ?>
                    </h3>
                    <div class="card-tools">
                        <a href="<?= url('reservas', 'reserva') ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    
                    <!-- Estado y Información General -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-<?= $badgeClass ?>">
                                    <i class="fas fa-calendar-check"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Estado</span>
                                    <span class="info-box-number">
                                        <span class="badge badge-<?= $badgeClass ?> badge-lg">
                                            <?= htmlspecialchars($reserva['estado']) ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-building"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Instalación</span>
                                    <span class="info-box-number" style="font-size: 0.9em;">
                                        <?= htmlspecialchars($reserva['instalacion_nombre']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-dollar-sign"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total</span>
                                    <span class="info-box-number" style="font-size: 1.2em;">
                                        $<?= number_format($reserva['precio_total'], 2) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Información de la Reserva -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold mb-3">
                                <i class="fas fa-calendar text-primary"></i> Información de la Reserva
                            </h5>
                            
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%">Fecha:</td>
                                    <td><strong><?= date('d/m/Y', strtotime($reserva['fecha_reserva'])) ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Horario:</td>
                                    <td>
                                        <strong class="text-primary">
                                            <?= date('H:i', strtotime($reserva['hora_inicio'])) ?> 
                                            - 
                                            <?= date('H:i', strtotime($reserva['hora_fin'])) ?>
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Duración:</td>
                                    <td><?= $reserva['duracion_minutos'] ?> minutos</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Precio Base:</td>
                                    <td>$<?= number_format($reserva['precio_base'], 2) ?></td>
                                </tr>
                                <?php if ($reserva['descuento_monto'] > 0): ?>
                                <tr>
                                    <td class="text-muted">Descuento:</td>
                                    <td class="text-danger">-$<?= number_format($reserva['descuento_monto'], 2) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($reserva['abono_utilizado'] > 0): ?>
                                <tr>
                                    <td class="text-muted">Abono Utilizado:</td>
                                    <td class="text-info">$<?= number_format($reserva['abono_utilizado'], 2) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted">Requiere Confirmación:</td>
                                    <td>
                                        <?= $reserva['requiere_confirmacion'] === 'S' ? '<span class="badge badge-warning">Sí</span>' : '<span class="badge badge-success">No</span>' ?>
                                    </td>
                                </tr>
                                <?php if (!empty($reserva['observaciones'])): ?>
                                <tr>
                                    <td class="text-muted">Observaciones:</td>
                                    <td><?= nl2br(htmlspecialchars($reserva['observaciones'])) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>

                        </div>

                        <div class="col-md-6">
                            <h5 class="font-weight-bold mb-3">
                                <i class="fas fa-user text-success"></i> Datos del Cliente
                            </h5>

                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%">Nombre:</td>
                                    <td><strong><?= htmlspecialchars($reserva['cliente_nombre']) ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email:</td>
                                    <td>
                                        <?php if (!empty($reserva['cliente_email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($reserva['cliente_email']) ?>">
                                            <?= htmlspecialchars($reserva['cliente_email']) ?>
                                        </a>
                                        <?php else: ?>
                                        <em class="text-muted">No especificado</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Teléfono:</td>
                                    <td>
                                        <?php if (!empty($reserva['cliente_telefono'])): ?>
                                            <a href="tel:<?= htmlspecialchars($reserva['cliente_telefono']) ?>">
                                                <?= htmlspecialchars($reserva['cliente_telefono']) ?>
                                            </a>
                                        <?php else: ?>
                                            <em class="text-muted">No especificado</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>

                            <hr>

                            <h5 class="font-weight-bold mb-3">
                                <i class="fas fa-clock text-info"></i> Registro
                            </h5>
                            
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%">Fecha Registro:</td>
                                    <td><?= date('d/m/Y H:i', strtotime($reserva['fecha_registro'])) ?></td>
                                </tr>
                                <?php if (!empty($reserva['fecha_confirmacion'])): ?>
                                <tr>
                                    <td class="text-muted">Fecha Confirmación:</td>
                                    <td><?= date('d/m/Y H:i', strtotime($reserva['fecha_confirmacion'])) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="card-footer text-center">
                    <?php if ($reserva['estado'] === 'PENDIENTE'): ?>
                        <button type="button" class="btn btn-success btn-lg mr-2" onclick="confirmarReserva(<?= $reserva['reserva_id'] ?>)">
                            <i class="fas fa-check"></i> Confirmar Reserva
                        </button>
                        <button type="button" class="btn btn-danger btn-lg mr-2" onclick="cancelarReserva(<?= $reserva['reserva_id'] ?>)">
                            <i class="fas fa-times"></i> Cancelar Reserva
                        </button>
                    <?php elseif ($reserva['estado'] === 'CONFIRMADA'): ?>
                        <button type="button" class="btn btn-info btn-lg mr-2" onclick="completarReserva(<?= $reserva['reserva_id'] ?>)">
                            <i class="fas fa-flag-checkered"></i> Marcar Completada
                        </button>
                        <button type="button" class="btn btn-danger btn-lg mr-2" onclick="cancelarReserva(<?= $reserva['reserva_id'] ?>)">
                            <i class="fas fa-times"></i> Cancelar Reserva
                        </button>
                    <?php endif; ?>
                    
                    <a href="<?= url('reservas', 'reserva') ?>" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
function confirmarReserva(id) {
    DigiAlert.confirm(
        '¿Confirmar esta reserva?',
        'La reserva pasará a estado CONFIRMADA',
        function() {
            window.location.href = '<?= url('reservas', 'reserva', 'confirmar') ?>&id=' + id;
        }
    );
}

function cancelarReserva(id) {
    DigiAlert.confirm(
        '¿Cancelar esta reserva?',
        'Esta acción no se puede deshacer',
        function() {
            window.location.href = '<?= url('reservas', 'reserva', 'cancelar') ?>&id=' + id;
        },
        'warning'
    );
}

function completarReserva(id) {
    DigiAlert.confirm(
        '¿Marcar como completada?',
        'La reserva pasará a estado COMPLETADA',
        function() {
            window.location.href = '<?= url('reservas', 'reserva', 'completar') ?>&id=' + id;
        }
    );
}
</script>
