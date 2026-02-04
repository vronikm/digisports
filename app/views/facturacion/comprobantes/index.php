<?php
/**
 * Listado de Comprobantes Electrónicos
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="fas fa-file-invoice-dollar text-warning"></i> Comprobantes Electrónicos
                </h1>
                <a href="<?= url('facturacion', 'comprobante', 'crear') ?>" class="btn btn-success">
                    <i class="fas fa-plus"></i> Nuevo Comprobante
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card card-outline card-warning">
                <div class="card-body p-2">
                    <form method="post" action="<?= url('facturacion', 'comprobante') ?>" class="form-inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="form-group mr-2">
                            <label for="tipo" class="mr-2">Tipo:</label>
                            <select class="form-control form-control-sm" id="tipo" name="tipo">
                                <option value="">-- Todos --</option>
                                <option value="FACTURA" <?= ($tipo ?? '') === 'FACTURA' ? 'selected' : '' ?>>Factura</option>
                                <option value="NOTA_CREDITO" <?= ($tipo ?? '') === 'NOTA_CREDITO' ? 'selected' : '' ?>>Nota de Crédito</option>
                                <option value="NOTA_DEBITO" <?= ($tipo ?? '') === 'NOTA_DEBITO' ? 'selected' : '' ?>>Nota de Débito</option>
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label for="estado" class="mr-2">Estado:</label>
                            <select class="form-control form-control-sm" id="estado" name="estado">
                                <option value="">-- Todos --</option>
                                <option value="PENDIENTE" <?= ($estado ?? '') === 'PENDIENTE' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="AUTORIZADO" <?= ($estado ?? '') === 'AUTORIZADO' ? 'selected' : '' ?>>Autorizado</option>
                                <option value="RECHAZADO" <?= ($estado ?? '') === 'RECHAZADO' ? 'selected' : '' ?>>Rechazado</option>
                                <option value="ANULADO" <?= ($estado ?? '') === 'ANULADO' ? 'selected' : '' ?>>Anulado</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    
                    <!-- Mensaje de módulo en desarrollo -->
                    <div class="alert alert-info text-center">
                        <i class="fas fa-hard-hat fa-3x mb-3"></i>
                        <h4>Módulo de Facturación Electrónica</h4>
                        <p class="mb-0">
                            Este módulo está preparado para la integración con el SRI de Ecuador.
                            <br>Próximamente: emisión de facturas, notas de crédito/débito y reportes fiscales.
                        </p>
                    </div>

                    <!-- Estadísticas rápidas -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Autorizados</span>
                                    <span class="info-box-number">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pendientes</span>
                                    <span class="info-box-number">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Rechazados</span>
                                    <span class="info-box-number">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Este Mes</span>
                                    <span class="info-box-number">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla vacía -->
                    <?php if (empty($comprobantes)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No hay comprobantes registrados.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Número</th>
                                        <th>Tipo</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comprobantes as $comp): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($comp['numero'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($comp['tipo'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($comp['cliente'] ?? '') ?></td>
                                            <td><?= date('d/m/Y', strtotime($comp['fecha'] ?? 'now')) ?></td>
                                            <td>$<?= number_format($comp['total'] ?? 0, 2) ?></td>
                                            <td>
                                                <span class="badge badge-secondary"><?= $comp['estado'] ?? '' ?></span>
                                            </td>
                                            <td>
                                                <a href="<?= url('facturacion', 'comprobante', 'ver', ['id' => $comp['id'] ?? 0]) ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>
