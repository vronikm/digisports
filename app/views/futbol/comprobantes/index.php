<?php
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
$moduloIcono = $modulo_actual['icono'] ?? 'fas fa-futbol';
$moduloNombre = $modulo_actual['nombre'] ?? 'Fútbol';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?>" style="color: <?= $moduloColor ?>"></i>
                    Comprobantes
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Comprobantes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-file-invoice mr-2" style="color: <?= $moduloColor ?>"></i>Listado de Comprobantes</h3>
                <button class="btn btn-sm text-white" style="background-color: <?= $moduloColor ?>" onclick="abrirModalComprobante()">
                    <i class="fas fa-plus mr-1"></i> Generar Comprobante
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($comprobantes)): ?>
                <div class="table-responsive">
                    <table id="tblComprobantes" class="table table-bordered table-hover table-striped">
                        <thead style="background-color: <?= $moduloColor ?>; color: #fff;">
                            <tr>
                                <th>#</th>
                                <th>Número</th>
                                <th>Tipo</th>
                                <th>Alumno</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Fecha Emisión</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comprobantes as $i => $comp): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <span class="font-weight-bold"><?= htmlspecialchars($comp['fcm_numero']) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $tipoCompIcon = match($comp['fcm_tipo']) {
                                        'RECIBO'       => 'fas fa-receipt',
                                        'FACTURA'      => 'fas fa-file-invoice-dollar',
                                        'NOTA_CREDITO' => 'fas fa-file-alt',
                                        default        => 'fas fa-file'
                                    };
                                    $tipoCompBadge = match($comp['fcm_tipo']) {
                                        'RECIBO'       => 'primary',
                                        'FACTURA'      => 'info',
                                        'NOTA_CREDITO' => 'warning',
                                        default        => 'secondary'
                                    };
                                    $tipoCompTexto = match($comp['fcm_tipo']) {
                                        'RECIBO'       => 'Recibo',
                                        'FACTURA'      => 'Factura',
                                        'NOTA_CREDITO' => 'Nota de Crédito',
                                        default        => $comp['fcm_tipo']
                                    };
                                    ?>
                                    <span class="badge badge-<?= $tipoCompBadge ?>">
                                        <i class="<?= $tipoCompIcon ?> mr-1"></i><?= $tipoCompTexto ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(trim(($comp['alu_nombres'] ?? '') . ' ' . ($comp['alu_apellidos'] ?? ''))) ?></td>
                                <td><?= htmlspecialchars($comp['fcm_concepto'] ?? '') ?></td>
                                <td class="text-right font-weight-bold">$<?= number_format($comp['fcm_monto'] ?? 0, 2) ?></td>
                                <td><?= date('d/m/Y', strtotime($comp['fcm_fecha_emision'])) ?></td>
                                <td>
                                    <?php
                                    $estadoCompBadge = match($comp['fcm_estado']) {
                                        'EMITIDO' => 'success',
                                        'ANULADO' => 'danger',
                                        default   => 'secondary'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $estadoCompBadge ?>"><?= $comp['fcm_estado'] ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-xs btn-outline-primary" onclick="verComprobante(<?= $comp['fcm_comprobante_id'] ?>)" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-xs btn-outline-info" onclick="imprimirComprobante(<?= $comp['fcm_comprobante_id'] ?>)" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <?php if ($comp['fcm_estado'] === 'EMITIDO'): ?>
                                    <button class="btn btn-xs btn-outline-danger" onclick="anularComprobante(<?= $comp['fcm_comprobante_id'] ?>, '<?= htmlspecialchars(addslashes($comp['fcm_numero'])) ?>')" title="Anular">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x opacity-50 text-muted mb-3"></i>
                    <p class="text-muted">No hay comprobantes registrados.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- ============================================================= -->
<!-- MODAL: GENERAR COMPROBANTE (opcional, normalmente se genera desde pagos) -->
<!-- ============================================================= -->
<div class="modal fade" id="modalComprobante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: <?= $moduloColor ?>">
                <h5 class="modal-title"><i class="fas fa-file-invoice mr-2"></i>Generar Comprobante</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formComprobante" method="POST" action="<?= url('futbol', 'comprobante', 'crear') ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>ID de Pago <span class="text-danger">*</span></label>
                        <input type="number" name="pago_id" id="comprobante_pago_id" class="form-control" required placeholder="Ingrese el ID del pago">
                    </div>
                    <div class="form-group">
                        <label>Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" id="comprobante_tipo" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="RECIBO">Recibo</option>
                            <option value="FACTURA">Factura</option>
                            <option value="NOTA_CREDITO">Nota de Crédito</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Concepto (opcional)</label>
                        <input type="text" name="concepto" id="comprobante_concepto" class="form-control" placeholder="Descripción del concepto">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: <?= $moduloColor ?>">
                        <i class="fas fa-save mr-1"></i> Generar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- SCRIPTS -->
<!-- ============================================================= -->
<?php ob_start(); ?>
<script>
$(document).ready(function() {
    $('#tblComprobantes').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        responsive: true,
        order: [[6, 'desc']]
    });
});

function abrirModalComprobante() {
    $('#formComprobante')[0].reset();
    $('#comprobante_pago_id').val('');
    $('#formComprobante').attr('action', '<?= url('futbol', 'comprobante', 'crear') ?>');
    $('#modalComprobante').modal('show');
}

function verComprobante(id) {
    $.getJSON('<?= url('futbol', 'comprobante', 'ver') ?>&id=' + id, function(response) {
        if (response.success && response.data) {
            var d = response.data;
            var html = '<table class="table table-sm">';
            html += '<tr><th>Número</th><td>' + (d.fcm_numero || '') + '</td></tr>';
            html += '<tr><th>Tipo</th><td>' + (d.fcm_tipo || '') + '</td></tr>';
            html += '<tr><th>Concepto</th><td>' + (d.fcm_concepto || '') + '</td></tr>';
            html += '<tr><th>Monto</th><td>$' + parseFloat(d.fcm_monto || 0).toFixed(2) + '</td></tr>';
            html += '<tr><th>Fecha</th><td>' + (d.fcm_fecha_emision || '') + '</td></tr>';
            html += '<tr><th>Estado</th><td>' + (d.fcm_estado || '') + '</td></tr>';
            html += '<tr><th>Alumno</th><td>' + ((d.alu_nombres || '') + ' ' + (d.alu_apellidos || '')).trim() + '</td></tr>';
            html += '</table>';
            Swal.fire({ title: 'Comprobante ' + (d.fcm_numero || ''), html: html, width: 600, confirmButtonText: 'Cerrar' });
        } else {
            Swal.fire('Error', response.message || 'No se pudo cargar.', 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Error de conexión.', 'error');
    });
}

function imprimirComprobante(id) {
    window.open('<?= url('futbol', 'comprobante', 'imprimir') ?>&id=' + id, '_blank');
}

function anularComprobante(id, numero) {
    Swal.fire({
        title: '¿Anular comprobante?',
        html: 'Se anulará el comprobante <strong>' + numero + '</strong>. Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-times-circle mr-1"></i> Anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= url('futbol', 'comprobante', 'anular') ?>', {
                csrf_token: '<?= $csrf_token ?>',
                id: id
            }, function(response) {
                if (response.success) {
                    Swal.fire('Anulado', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error', 'Error de conexión.', 'error');
            });
        }
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>
