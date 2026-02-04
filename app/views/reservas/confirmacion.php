<?php
/**
 * Confirmación de reserva - Muestra detalles después de crear
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            
            <!-- Alert de Éxito -->
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <strong>¡Reserva Creada!</strong> Su reserva ha sido registrada. 
                <?php if ($reserva['estado'] === 'PENDIENTE_CONFIRMACION'): ?>
                    Pendiente de confirmación por parte del administrador.
                <?php endif; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Tarjeta de Resumen -->
            <div class="card card-primary">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i> Detalles de Reserva
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">Referencia: <?= htmlspecialchars($reserva['referencia']) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- Información de la Cancha -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Cancha</h6>
                            <h4 class="font-weight-bold"><?= htmlspecialchars($reserva['cancha_nombre']) ?></h4>
                            <p class="text-muted">
                                <span class="badge badge-info"><?= htmlspecialchars($reserva['cancha_tipo']) ?></span>
                                <span class="badge badge-secondary"><?= htmlspecialchars($reserva['instalacion_nombre']) ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Estado de Reserva</h6>
                            <h4>
                                <?php 
                                $badgeClass = [
                                    'PENDIENTE_CONFIRMACION' => 'warning',
                                    'CONFIRMADA' => 'success',
                                    'COMPLETADA' => 'info',
                                    'CANCELADA' => 'danger'
                                ][$reserva['estado']] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $badgeClass ?>">
                                    <?= htmlspecialchars($reserva['estado']) ?>
                                </span>
                            </h4>
                        </div>
                    </div>

                    <hr>

                    <!-- Detalles Temporales -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Fecha</h6>
                            <p class="font-weight-bold">
                                <?= strftime('%A, %d de %B de %Y', strtotime($reserva['fecha_reserva'])) ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Horario</h6>
                            <p class="font-weight-bold">
                                <?= date('H:i', strtotime($reserva['fecha_reserva'])) ?> - 
                                <?= date('H:i', strtotime($reserva['fecha_fin_reserva'])) ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Duración</h6>
                            <p class="font-weight-bold">
                                <?php
                                $inicio = new DateTime($reserva['fecha_reserva']);
                                $fin = new DateTime($reserva['fecha_fin_reserva']);
                                $diff = $fin->diff($inicio);
                                echo $diff->h . 'h ' . $diff->i . 'min';
                                ?>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <!-- Información del Cliente -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-muted font-weight-bold mb-3">Datos del Cliente</h6>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Nombre:</strong><br>
                                <?= htmlspecialchars($reserva['nombre_cliente']) ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Email:</strong><br>
                                <a href="mailto:<?= htmlspecialchars($reserva['email_cliente']) ?>">
                                    <?= htmlspecialchars($reserva['email_cliente']) ?>
                                </a>
                            </p>
                        </div>
                        <?php if (!empty($reserva['telefono_cliente'])): ?>
                            <div class="col-md-6">
                                <p>
                                    <strong>Teléfono:</strong><br>
                                    <a href="tel:<?= htmlspecialchars($reserva['telefono_cliente']) ?>">
                                        <?= htmlspecialchars($reserva['telefono_cliente']) ?>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <p>
                                <strong>Cantidad de Personas:</strong><br>
                                <?= $reserva['cantidad_personas'] ?>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <!-- Detalles Económicos -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Franja Horaria</th>
                                            <th>Precio Unitario</th>
                                            <th>Cantidad</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lineas as $linea): ?>
                                            <tr>
                                                <td>
                                                    <?= $linea['hora_inicio'] ?> - <?= $linea['hora_fin'] ?>
                                                </td>
                                                <td>$<?= number_format($linea['precio_unitario'], 2) ?></td>
                                                <td><?= $linea['cantidad'] ?></td>
                                                <td class="text-right">
                                                    <strong>$<?= number_format($linea['precio_total'], 2) ?></strong>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold bg-light">
                                            <td colspan="3" class="text-right">TOTAL:</td>
                                            <td class="text-right">
                                                <span class="badge badge-success" style="font-size: 1.1em;">
                                                    $<?= number_format($reserva['precio_total'], 2) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($reserva['notas'])): ?>
                        <hr>
                        <div class="alert alert-info">
                            <strong>Notas:</strong><br>
                            <?= htmlspecialchars($reserva['notas']) ?>
                        </div>
                    <?php endif; ?>

                </div>
                <div class="card-footer">
                    <a href="<?= url('reservas', 'reserva', 'index') ?>" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Ver mis reservas
                    </a>
                    <a href="<?= url('reservas', 'reserva', 'buscar') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva reserva
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
