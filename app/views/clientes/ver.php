<?php
/**
 * Vista: Detalle de Cliente
 */

$cliente = $cliente ?? [];
$reservas = $reservas ?? [];
$pagos = $pagos ?? [];
$abonos = $abonos ?? [];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-user text-primary"></i>
                    Detalle de Cliente
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('core', 'hub', 'index') ?>">Hub</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('clientes', 'cliente', 'index') ?>">Clientes</a></li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <div class="row">
            <!-- Información del cliente -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <div class="profile-user-img img-fluid img-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 100px; height: 100px; font-size: 2.5rem;">
                                <?= strtoupper(substr($cliente['nombres'] ?? 'C', 0, 1) . substr($cliente['apellidos'] ?? '', 0, 1)) ?>
                            </div>
                        </div>

                        <h3 class="profile-username text-center mt-3">
                            <?= htmlspecialchars($cliente['nombres'] . ' ' . $cliente['apellidos']) ?>
                        </h3>

                        <p class="text-muted text-center">
                            <?php
                            $tiposCliente = [
                                'SOCIO' => ['label' => 'Socio', 'badge' => 'badge-success'],
                                'CLIENTE' => ['label' => 'Cliente', 'badge' => 'badge-info'],
                                'EMPRESA' => ['label' => 'Empresa', 'badge' => 'badge-primary'],
                                'INVITADO' => ['label' => 'Invitado', 'badge' => 'badge-secondary'],
                                'PUBLICO' => ['label' => 'Público', 'badge' => 'badge-light']
                            ];
                            $tipo = $tiposCliente[$cliente['tipo_cliente']] ?? ['label' => $cliente['tipo_cliente'], 'badge' => 'badge-secondary'];
                            ?>
                            <span class="badge <?= $tipo['badge'] ?>"><?= $tipo['label'] ?></span>
                            <?php if ($cliente['estado'] === 'A'): ?>
                            <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                            <span class="badge badge-danger">Inactivo</span>
                            <?php endif; ?>
                        </p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b><i class="fas fa-id-card mr-2"></i>Identificación</b>
                                <a class="float-right"><?= htmlspecialchars($cliente['tipo_identificacion'] . ': ' . $cliente['identificacion']) ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-envelope mr-2"></i>Email</b>
                                <a class="float-right"><?= htmlspecialchars($cliente['email'] ?? 'No registrado') ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-phone mr-2"></i>Teléfono</b>
                                <a class="float-right"><?= htmlspecialchars($cliente['telefono'] ?? $cliente['celular'] ?? 'No registrado') ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-calendar mr-2"></i>F. Nacimiento</b>
                                <a class="float-right"><?= $cliente['fecha_nacimiento'] ? date('d/m/Y', strtotime($cliente['fecha_nacimiento'])) : 'No registrado' ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-wallet mr-2"></i>Saldo a Favor</b>
                                <a class="float-right text-success font-weight-bold">$<?= number_format($cliente['saldo_abono'] ?? 0, 2) ?></a>
                            </li>
                        </ul>

                        <a href="<?= url('clientes', 'cliente', 'editar', ['id' => $cliente['cliente_id']]) ?>" class="btn btn-primary btn-block">
                            <i class="fas fa-edit"></i> Editar Cliente
                        </a>
                    </div>
                </div>
                
                <!-- Dirección -->
                <?php if (!empty($cliente['direccion'])): ?>
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Dirección</h3>
                    </div>
                    <div class="card-body">
                        <?= nl2br(htmlspecialchars($cliente['direccion'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Historial -->
            <div class="col-md-8">
                <!-- Reservas recientes -->
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-check"></i> Últimas Reservas
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0" style="max-height: 300px;">
                        <table class="table table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cancha</th>
                                    <th>Horario</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reservas)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        Sin reservas registradas
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($reserva['fecha'])) ?></td>
                                    <td><?= htmlspecialchars($reserva['cancha_nombre'] ?? 'N/A') ?></td>
                                    <td><?= date('H:i', strtotime($reserva['hora_inicio'])) ?> - <?= date('H:i', strtotime($reserva['hora_fin'])) ?></td>
                                    <td>
                                        <?php
                                        $estadoClass = [
                                            'PENDIENTE' => 'badge-warning',
                                            'CONFIRMADA' => 'badge-success',
                                            'CANCELADA' => 'badge-danger',
                                            'COMPLETADA' => 'badge-info'
                                        ][$reserva['estado']] ?? 'badge-secondary';
                                        ?>
                                        <span class="badge <?= $estadoClass ?>"><?= $reserva['estado'] ?></span>
                                    </td>
                                    <td>$<?= number_format($reserva['total'] ?? 0, 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagos recientes -->
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-money-bill-wave"></i> Últimos Pagos
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0" style="max-height: 250px;">
                        <table class="table table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th>Método</th>
                                    <th class="text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pagos)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        Sin pagos registrados
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                                    <td><?= htmlspecialchars($pago['concepto'] ?? 'Pago') ?></td>
                                    <td><?= htmlspecialchars($pago['metodo_pago'] ?? 'N/A') ?></td>
                                    <td class="text-right font-weight-bold text-success">
                                        $<?= number_format($pago['monto'] ?? 0, 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Abonos -->
                <?php if (!empty($abonos)): ?>
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-piggy-bank"></i> Historial de Abonos
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0" style="max-height: 200px;">
                        <table class="table table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th class="text-right">Monto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($abonos as $abono): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($abono['fecha'])) ?></td>
                                    <td><?= htmlspecialchars($abono['tipo'] ?? 'Abono') ?></td>
                                    <td class="text-right font-weight-bold">
                                        $<?= number_format($abono['monto'] ?? 0, 2) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $abono['estado'] === 'ACTIVO' ? 'badge-success' : 'badge-secondary' ?>">
                                            <?= $abono['estado'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Acciones -->
        <div class="row">
            <div class="col-12">
                <a href="<?= url('clientes', 'cliente', 'index') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>
        
    </div>
</section>
