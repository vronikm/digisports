<?php
/**
 * Listado de Comprobantes Electrónicos
 */

$stats    = $stats    ?? ['AUTORIZADO' => 0, 'PENDIENTE' => 0, 'RECHAZADO' => 0, 'ENVIADA' => 0];
$totalMes = $totalMes ?? 0;

$badgeClase = [
    'AUTORIZADO' => 'success',
    'ENVIADA'    => 'warning',
    'PENDIENTE'  => 'secondary',
    'RECHAZADO'  => 'danger',
    'ANULADO'    => 'dark',
    'FIRMADA'    => 'info',
    'GENERADA'   => 'light',
];

$tipoLabel = [
    '01' => 'Factura',
    '04' => 'N. Crédito',
    '05' => 'N. Débito',
    '06' => 'Guía',
    '07' => 'Comprobante Retención',
];
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="fas fa-file-invoice-dollar text-warning"></i> Comprobantes Electrónicos
                </h1>
                <a href="<?= url('facturacion', 'factura', 'crear') ?>" class="btn btn-success">
                    <i class="fas fa-plus"></i> Nueva Factura
                </a>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Autorizados</span>
                    <span class="info-box-number"><?= $stats['AUTORIZADO'] ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendientes / Enviadas</span>
                    <span class="info-box-number"><?= ($stats['PENDIENTE'] + $stats['ENVIADA']) ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Rechazados</span>
                    <span class="info-box-number"><?= $stats['RECHAZADO'] ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Este Mes</span>
                    <span class="info-box-number"><?= $totalMes ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card card-outline card-warning">
                <div class="card-body p-2">
                    <form method="post" action="<?= url('facturacion', 'comprobante') ?>" class="form-inline flex-wrap" style="gap:8px">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="form-group mr-2">
                            <label for="tipo" class="mr-1">Tipo:</label>
                            <select class="form-control form-control-sm" id="tipo" name="tipo">
                                <option value="">-- Todos --</option>
                                <option value="01" <?= ($tipo ?? '') === '01' ? 'selected' : '' ?>>Factura</option>
                                <option value="04" <?= ($tipo ?? '') === '04' ? 'selected' : '' ?>>Nota de Crédito</option>
                                <option value="05" <?= ($tipo ?? '') === '05' ? 'selected' : '' ?>>Nota de Débito</option>
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label for="estado" class="mr-1">Estado:</label>
                            <select class="form-control form-control-sm" id="estado" name="estado">
                                <option value="">-- Todos --</option>
                                <option value="AUTORIZADO" <?= ($estado ?? '') === 'AUTORIZADO' ? 'selected' : '' ?>>Autorizado</option>
                                <option value="ENVIADA"    <?= ($estado ?? '') === 'ENVIADA'    ? 'selected' : '' ?>>Enviada (en proceso)</option>
                                <option value="PENDIENTE"  <?= ($estado ?? '') === 'PENDIENTE'  ? 'selected' : '' ?>>Pendiente</option>
                                <option value="RECHAZADO"  <?= ($estado ?? '') === 'RECHAZADO'  ? 'selected' : '' ?>>Rechazado</option>
                                <option value="ANULADO"    <?= ($estado ?? '') === 'ANULADO'    ? 'selected' : '' ?>>Anulado</option>
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label for="busqueda" class="mr-1">Buscar:</label>
                            <input type="text" class="form-control form-control-sm" id="busqueda" name="busqueda"
                                   placeholder="Número, cliente, clave..." style="min-width:200px"
                                   value="<?= htmlspecialchars($busqueda ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <?php if (!empty($estado) || !empty($tipo) || !empty($busqueda)): ?>
                            <a href="<?= url('facturacion', 'comprobante') ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body p-0">
                    <?php if (empty($comprobantes)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p class="mb-0">No hay comprobantes registrados<?= !empty($estado) || !empty($tipo) || !empty($busqueda) ? ' con los filtros aplicados' : '' ?>.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Número</th>
                                        <th>Tipo</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th class="text-right">Total</th>
                                        <th>Estado SRI</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comprobantes as $comp): ?>
                                        <?php
                                            $numero  = sprintf('%s-%s-%s',
                                                $comp['fac_establecimiento'] ?? '001',
                                                $comp['fac_punto_emision']   ?? '001',
                                                str_pad($comp['fac_secuencial'] ?? '0', 9, '0', STR_PAD_LEFT)
                                            );
                                            $tipo_label = $tipoLabel[$comp['fac_tipo_comprobante'] ?? '01'] ?? ($comp['fac_tipo_comprobante'] ?? '-');
                                            $estadoSri  = $comp['fac_estado_sri'] ?? 'PENDIENTE';
                                            $badgeCls   = $badgeClase[$estadoSri] ?? 'secondary';
                                        ?>
                                        <tr>
                                            <td><code><?= htmlspecialchars($numero) ?></code></td>
                                            <td><?= htmlspecialchars($tipo_label) ?></td>
                                            <td><?= htmlspecialchars($comp['fac_cliente_razon_social'] ?? '-') ?></td>
                                            <td><?= !empty($comp['fac_fecha_emision']) ? date('d/m/Y', strtotime($comp['fac_fecha_emision'])) : '-' ?></td>
                                            <td class="text-right">$<?= number_format((float)($comp['fac_total'] ?? 0), 2) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $badgeCls ?>">
                                                    <?= htmlspecialchars($estadoSri) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= url('facturacion', 'factura_electronica', 'ver', ['id' => $comp['fac_id']]) ?>"
                                                   class="btn btn-sm btn-info" title="Ver detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <?php if (($totalPaginas ?? 1) > 1): ?>
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                                <small class="text-muted">
                                    Mostrando <?= count($comprobantes) ?> de <?= $totalRegistros ?> registros
                                </small>
                                <ul class="pagination pagination-sm mb-0">
                                    <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                                        <li class="page-item <?= $p === ($pagina ?? 1) ? 'active' : '' ?>">
                                            <a class="page-link"
                                               href="<?= url('facturacion', 'comprobante', 'index', array_filter(['pagina' => $p, 'estado' => $estado, 'tipo' => $tipo, 'busqueda' => $busqueda])) ?>">
                                                <?= $p ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
