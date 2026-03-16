<?php
/**
 * Vista: Listado de Facturas
 * Muestra todas las facturas con filtros y acciones
 */
?>

<div class="container-fluid my-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">Gestión de Facturas</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= url('facturacion', 'factura', 'crear') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Factura
            </a>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <select id="filtroEstado" class="form-select">
                        <option value="">-- Todos los estados --</option>
                        <option value="BORRADOR" <?= ($estado ?? '') === 'BORRADOR' ? 'selected' : '' ?>>Borrador</option>
                        <option value="EMITIDA" <?= ($estado ?? '') === 'EMITIDA' ? 'selected' : '' ?>>Emitida</option>
                        <option value="PAGADA" <?= ($estado ?? '') === 'PAGADA' ? 'selected' : '' ?>>Pagada</option>
                        <option value="ANULADA" <?= ($estado ?? '') === 'ANULADA' ? 'selected' : '' ?>>Anulada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" id="btnFiltrar" class="btn btn-outline-primary w-100">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabla de Facturas -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Fecha Emisión</th>
                        <th>Total</th>
                        <th>Pagado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($facturas)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">No hay facturas disponibles</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($facturas as $factura): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($factura['fac_numero'] ?? '') ?></strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars($factura['nombre_cliente']) ?>
                                </td>
                                <td>
                                    <?= !empty($factura['fac_fecha_emision']) ? date('d/m/Y', strtotime($factura['fac_fecha_emision'])) : 'N/A' ?>
                                </td>
                                <td>
                                    $<?= number_format($factura['fac_total'] ?? 0, 2) ?>
                                </td>
                                <td>
                                    $<?= number_format($factura['total_pagado'] ?? 0, 2) ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= 
                                        ($factura['fac_estado'] ?? '') === 'EMITIDA' ? 'warning' :
                                        (($factura['fac_estado'] ?? '') === 'PAGADA' ? 'success' :
                                        (($factura['fac_estado'] ?? '') === 'BORRADOR' ? 'secondary' : 'danger'))
                                    ?>">
                                        <?= htmlspecialchars($factura['fac_estado'] ?? '') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('facturacion', 'factura', 'ver', ['id' => $factura['fac_id']]) ?>"
                                           class="btn btn-outline-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (($factura['fac_estado'] ?? '') === 'BORRADOR'): ?>
                                            <a href="<?= url('facturacion', 'factura', 'emitir', ['id' => $factura['fac_id']]) ?>"
                                               class="btn btn-outline-success" title="Emitir">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (($factura['fac_estado'] ?? '') !== 'ANULADA' && ($factura['fac_estado'] ?? '') !== 'PAGADA'): ?>
                                            <button type="button"
                                                    class="btn btn-outline-danger btn-anular"
                                                    title="Anular"
                                                    data-id="<?= (int)$factura['fac_id'] ?>"
                                                    data-numero="<?= htmlspecialchars($factura['fac_numero'] ?? '') ?>"
                                                    data-url="<?= htmlspecialchars(url('facturacion', 'factura', 'anular', ['id' => $factura['fac_id']])) ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (($factura['fac_estado'] ?? '') === 'ANULADA'): ?>
                                            <button type="button"
                                                    class="btn btn-outline-success btn-reactivar"
                                                    title="Reactivar factura"
                                                    data-id="<?= (int)$factura['fac_id'] ?>"
                                                    data-numero="<?= htmlspecialchars($factura['fac_numero'] ?? '') ?>"
                                                    data-url="<?= htmlspecialchars(url('facturacion', 'factura', 'reactivar', ['id' => $factura['fac_id']])) ?>">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?= url('facturacion', 'factura', 'pdf', ['id' => $factura['fac_id']]) ?>"
                                           class="btn btn-outline-secondary" title="PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Paginación -->
    <?php if ($totalPaginas > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $pagina === 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('facturacion', 'factura', 'index', ['pagina' => 1, 'estado' => $estado]) ?>">
                        Primera
                    </a>
                </li>
                <li class="page-item <?= $pagina === 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('facturacion', 'factura', 'index', ['pagina' => max(1, $pagina - 1), 'estado' => $estado]) ?>">
                        Anterior
                    </a>
                </li>
                
                <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                    <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('facturacion', 'factura', 'index', ['pagina' => $i, 'estado' => $estado]) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?= $pagina === $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('facturacion', 'factura', 'index', ['pagina' => min($totalPaginas, $pagina + 1), 'estado' => $estado]) ?>">
                        Siguiente
                    </a>
                </li>
                <li class="page-item <?= $pagina === $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('facturacion', 'factura', 'index', ['pagina' => $totalPaginas, 'estado' => $estado]) ?>">
                        Última
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    
    <!-- Resumen -->
    <div class="row mt-4">
        <div class="col-md-12">
            <p class="text-muted text-center small">
                Mostrando <?= count($facturas) ?> de <?= $totalRegistros ?> registros
            </p>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
(function ($) {
    'use strict';

    var csrfToken = <?= json_encode($csrf_token ?? '') ?>;

    var Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });

    // Filtro
    $('#btnFiltrar').on('click', function () {
        var estado = $('#filtroEstado').val();
        var base   = <?= json_encode(url('facturacion', 'factura', 'index')) ?>;
        window.location.href = base + (estado ? '&estado=' + encodeURIComponent(estado) : '');
    });

    // ── ANULAR ──────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-anular', function () {
        var facNum  = $(this).data('numero');
        var urlPost = $(this).data('url');

        Swal.fire({
            title: 'Anular factura',
            html: '<p>¿Está seguro de anular la factura</p>' +
                  '<p><strong>' + $('<div>').text(facNum).html() + '</strong>?</p>' +
                  '<p class="text-muted small mb-2">El estado cambiará a ' +
                  '<span class="badge badge-danger">ANULADA</span></p>' +
                  '<input type="text" id="swal-motivo" class="swal2-input" ' +
                  'placeholder="Motivo de anulación (opcional)" maxlength="200">',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-times mr-1"></i> Sí, anular',
            cancelButtonText: 'Cancelar',
            focusCancel: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var motivo = $('#swal-motivo').val() || '';

            $.ajax({
                url: urlPost,
                method: 'POST',
                data: { csrf_token: csrfToken, motivo: motivo },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        Toast.fire({ icon: 'success', title: res.message || 'Factura anulada' });
                        setTimeout(function () { location.reload(); }, 1600);
                    } else {
                        Toast.fire({ icon: 'error', title: res.message || 'Error al anular' });
                    }
                },
                error: function () {
                    Toast.fire({ icon: 'error', title: 'Error de comunicación con el servidor' });
                }
            });
        });
    });

    // ── REACTIVAR ────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-reactivar', function () {
        var facNum  = $(this).data('numero');
        var urlPost = $(this).data('url');

        Swal.fire({
            title: 'Reactivar factura',
            html: '<p>¿Está seguro de reactivar la factura</p>' +
                  '<p><strong>' + $('<div>').text(facNum).html() + '</strong>?</p>' +
                  '<p class="text-muted small">El estado volverá a ' +
                  '<span class="badge badge-secondary">BORRADOR</span></p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-redo mr-1"></i> Sí, reactivar',
            cancelButtonText: 'Cancelar',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: urlPost,
                method: 'POST',
                data: { csrf_token: csrfToken },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        Toast.fire({ icon: 'success', title: res.message || 'Factura reactivada' });
                        setTimeout(function () { location.reload(); }, 1600);
                    } else {
                        Toast.fire({ icon: 'error', title: res.message || 'Error al reactivar' });
                    }
                },
                error: function () {
                    Toast.fire({ icon: 'error', title: 'Error de comunicación con el servidor' });
                }
            });
        });
    });

}(jQuery));
</script>
<?php $scripts = ob_get_clean(); ?>
