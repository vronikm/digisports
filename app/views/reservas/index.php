<?php
/**
 * Listado de reservas — Fase 3
 * KPIs resumen, filtros avanzados (búsqueda, fechas, estado_pago), botón Cobrar inline
 */
$urlIndex = url('reservas', 'reserva', 'index');
$kpis = $kpis ?? ['hoy' => 0, 'pendientes_pago' => 0, 'recaudado_mes' => 0, 'por_cobrar' => 0];
?>
<div class="container-fluid mt-4">

    <!-- Título -->
    <div class="row mb-3">
        <div class="col-md-6">
            <h1 class="mb-0">
                <i class="fas fa-calendar-check text-primary"></i> Gestión de Reservas
            </h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?= url('reservas', 'reserva', 'buscar') ?>" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Nueva Reserva
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-3">
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><?= (int)$kpis['hoy'] ?></h3>
                    <p>Reservas Hoy</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3><?= (int)$kpis['pendientes_pago'] ?></h3>
                    <p>Pendientes de Pago</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>$<?= number_format($kpis['recaudado_mes'], 2) ?></h3>
                    <p>Recaudado este Mes</p>
                </div>
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>$<?= number_format($kpis['por_cobrar'], 2) ?></h3>
                    <p>Saldo por Cobrar</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
            </div>
        </div>
    </div>

    <!-- Filtros avanzados -->
    <div class="card card-outline card-primary <?= (empty($buscar ?? '') && empty($estado ?? '') && empty($estado_pago ?? '') && empty($fecha_desde ?? '') && empty($fecha_hasta ?? '')) ? 'collapsed-card' : '' ?>">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body p-2" <?= (empty($buscar ?? '') && empty($estado ?? '') && empty($estado_pago ?? '') && empty($fecha_desde ?? '') && empty($fecha_hasta ?? '')) ? 'style="display:none"' : '' ?>>
            <form method="post" action="<?= $urlIndex ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="small font-weight-bold">Buscar</label>
                        <input type="text" class="form-control form-control-sm" name="buscar"
                               value="<?= htmlspecialchars($buscar ?? '') ?>"
                               placeholder="Nombre o # reserva">
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="small font-weight-bold">Estado</label>
                        <select class="form-control form-control-sm" name="estado">
                            <option value="">Todos</option>
                            <?php foreach (['PENDIENTE','CONFIRMADA','COMPLETADA','CANCELADA'] as $e): ?>
                            <option value="<?= $e ?>" <?= ($estado ?? '') === $e ? 'selected' : '' ?>><?= ucfirst(strtolower($e)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="small font-weight-bold">Pago</label>
                        <select class="form-control form-control-sm" name="estado_pago">
                            <option value="">Todos</option>
                            <?php foreach (['PENDIENTE','PARCIAL','PAGADO'] as $ep): ?>
                            <option value="<?= $ep ?>" <?= ($estado_pago ?? '') === $ep ? 'selected' : '' ?>><?= ucfirst(strtolower($ep)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="small font-weight-bold">Desde</label>
                        <input type="date" class="form-control form-control-sm" name="fecha_desde"
                               value="<?= htmlspecialchars($fecha_desde ?? '') ?>">
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="small font-weight-bold">Hasta</label>
                        <input type="date" class="form-control form-control-sm" name="fecha_hasta"
                               value="<?= htmlspecialchars($fecha_hasta ?? '') ?>">
                    </div>
                    <div class="col-md-1 form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (!empty($reservas)): ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Instalación</th>
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th class="text-right">Precio</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Pago</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas as $reserva): ?>
                        <?php
                            $estadoBadge = [
                                'PENDIENTE'  => 'warning',
                                'CONFIRMADA' => 'success',
                                'COMPLETADA' => 'info',
                                'CANCELADA'  => 'danger'
                            ][$reserva['estado']] ?? 'secondary';
                            
                            $epago = $reserva['estado_pago'] ?? 'PENDIENTE';
                            $pagoBadge = [
                                'PENDIENTE' => 'secondary',
                                'PARCIAL'   => 'warning',
                                'PAGADO'    => 'success'
                            ][$epago] ?? 'secondary';
                            
                            $saldo = (float)($reserva['saldo_pendiente'] ?? $reserva['precio_total']);
                        ?>
                        <tr>
                            <td><strong>#<?= $reserva['reserva_id'] ?></strong></td>
                            <td><?= htmlspecialchars(trim(($reserva['cliente_nombre'] ?? '') . ' ' . ($reserva['cliente_apellidos'] ?? ''))) ?></td>
                            <td><?= htmlspecialchars($reserva['instalacion_nombre'] ?? 'N/A') ?></td>
                            <td><?= date('d/m/Y', strtotime($reserva['fecha_reserva'])) ?></td>
                            <td><?= substr($reserva['hora_inicio'], 0, 5) ?> - <?= substr($reserva['hora_fin'], 0, 5) ?></td>
                            <td class="text-right"><strong>$<?= number_format($reserva['precio_total'], 2) ?></strong></td>
                            <td class="text-center">
                                <span class="badge badge-<?= $estadoBadge ?>"><?= $reserva['estado'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?= $pagoBadge ?>"><?= $epago ?></span>
                                <?php if ($epago === 'PARCIAL'): ?>
                                    <br><small class="text-muted">Pend: $<?= number_format($saldo, 2) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= url('reservas', 'reserva', 'ver', ['id' => $reserva['reserva_id']]) ?>"
                                       class="btn btn-outline-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if ($reserva['estado'] !== 'CANCELADA' && $epago !== 'PAGADO'): ?>
                                    <a href="<?= url('reservas', 'pago', 'checkout') ?>&id=<?= $reserva['reserva_id'] ?>"
                                       class="btn btn-outline-warning" title="Cobrar $<?= number_format($saldo, 2) ?>">
                                        <i class="fas fa-cash-register"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($reserva['estado'] === 'PENDIENTE'): ?>
                                    <button type="button" class="btn btn-outline-success btn-confirmar"
                                            data-url="<?= url('reservas', 'reserva', 'confirmar', ['id' => $reserva['reserva_id']]) ?>"
                                            title="Confirmar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($reserva['estado'], ['PENDIENTE', 'CONFIRMADA'])): ?>
                                    <button type="button" class="btn btn-outline-danger btn-cancelar"
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
            <div class="card-footer clearfix">
                <div class="float-left">
                    <small class="text-muted">Mostrando <?= count($reservas) ?> de <?= $totalRegistros ?> reservas</small>
                </div>
                <?php
                    $paginaParams = [
                        'estado' => $estado ?? '',
                        'estado_pago' => $estado_pago ?? '',
                        'buscar' => $buscar ?? '',
                        'fecha_desde' => $fecha_desde ?? '',
                        'fecha_hasta' => $fecha_hasta ?? ''
                    ];
                ?>
                <ul class="pagination pagination-sm m-0 float-right">
                    <?php if ($pagina > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= url('reservas', 'reserva', 'index', array_merge($paginaParams, ['pagina' => $pagina - 1])) ?>">«</a>
                    </li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                    <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('reservas', 'reserva', 'index', array_merge($paginaParams, ['pagina' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <?php if ($pagina < $totalPaginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= url('reservas', 'reserva', 'index', array_merge($paginaParams, ['pagina' => $pagina + 1])) ?>">»</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-times fa-3x mb-3 d-block" style="opacity:.3"></i>
                <strong>No hay reservas que coincidan</strong><br>
                <a href="<?= url('reservas', 'reserva', 'buscar') ?>" class="btn btn-success btn-sm mt-3">
                    <i class="fas fa-plus"></i> Crear nueva reserva
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirmar reserva
    document.querySelectorAll('.btn-confirmar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var urlConfirmar = this.dataset.url;
            DigiAlert.confirm({
                title: '¿Confirmar reserva?',
                text: 'La reserva quedará confirmada.',
                icon: 'question',
                confirmButtonText: '<i class="fas fa-check"></i> Sí, confirmar',
                confirmButtonColor: '#08DC64'
            }, function() {
                DigiAlert.loading('Confirmando...');
                window.location.href = urlConfirmar;
            });
        });
    });

    // Cancelar reserva
    document.querySelectorAll('.btn-cancelar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var urlCancelar = this.dataset.url;
            Swal.fire({
                title: '¿Cancelar reserva?',
                html: '<p>Esta acción no se puede deshacer.</p>' +
                      '<div class="form-group text-left">' +
                      '<label>Motivo (opcional):</label>' +
                      '<textarea id="swal-motivo" class="swal2-textarea" placeholder="Motivo..."></textarea>' +
                      '</div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-times"></i> Sí, cancelar',
                cancelButtonText: 'No, mantener',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed) {
                    DigiAlert.loading('Cancelando...');
                    var motivo = document.getElementById('swal-motivo') ? document.getElementById('swal-motivo').value : '';
                    window.location.href = urlCancelar + '&motivo=' + encodeURIComponent(motivo);
                }
            });
        });
    });
});
</script>
