<?php
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
$moduloIcono = $modulo_actual['icono'] ?? 'fas fa-futbol';
?>

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

<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-file-invoice mr-2" style="color: <?= $moduloColor ?>"></i>Listado de Comprobantes</h3>
                <button class="btn btn-sm text-white" id="btnGenerarComprobante" style="background-color: <?= $moduloColor ?>">
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
                            <?php
                            $tipoCompBadge = match($comp['fcm_tipo']) {
                                'RECIBO'       => 'primary',
                                'FACTURA'      => 'info',
                                'NOTA_CREDITO' => 'warning',
                                default        => 'secondary'
                            };
                            $tipoCompIcon = match($comp['fcm_tipo']) {
                                'RECIBO'       => 'fas fa-receipt',
                                'FACTURA'      => 'fas fa-file-invoice-dollar',
                                'NOTA_CREDITO' => 'fas fa-file-alt',
                                default        => 'fas fa-file'
                            };
                            $tipoCompTexto = match($comp['fcm_tipo']) {
                                'RECIBO'       => 'Recibo',
                                'FACTURA'      => 'Factura',
                                'NOTA_CREDITO' => 'Nota de Crédito',
                                default        => $comp['fcm_tipo']
                            };
                            $estadoCompBadge = match($comp['fcm_estado']) {
                                'EMITIDO' => 'success',
                                'ANULADO' => 'danger',
                                default   => 'secondary'
                            };
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><span class="font-weight-bold"><?= htmlspecialchars($comp['fcm_numero']) ?></span></td>
                                <td>
                                    <span class="badge badge-<?= $tipoCompBadge ?>">
                                        <i class="<?= $tipoCompIcon ?> mr-1"></i><?= $tipoCompTexto ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(trim(($comp['alu_nombres'] ?? '') . ' ' . ($comp['alu_apellidos'] ?? ''))) ?></td>
                                <td><?= htmlspecialchars($comp['fcm_concepto'] ?? '') ?></td>
                                <td class="text-right font-weight-bold">$<?= number_format($comp['fcm_total'] ?? $comp['fcm_monto'] ?? 0, 2) ?></td>
                                <td><?= date('d/m/Y', strtotime($comp['fcm_fecha_emision'])) ?></td>
                                <td><span class="badge badge-<?= $estadoCompBadge ?>"><?= $comp['fcm_estado'] ?></span></td>
                                <td>
                                    <button class="btn btn-xs btn-outline-primary js-ver-comprobante" title="Ver Detalle"
                                        data-id="<?= $comp['fcm_comprobante_id'] ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-xs btn-outline-info js-imprimir-comprobante" title="Imprimir"
                                        data-id="<?= $comp['fcm_comprobante_id'] ?>">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <?php if ($comp['fcm_estado'] === 'EMITIDO'): ?>
                                    <button class="btn btn-xs btn-outline-danger js-anular-comprobante" title="Anular"
                                        data-id="<?= $comp['fcm_comprobante_id'] ?>"
                                        data-numero="<?= htmlspecialchars($comp['fcm_numero'], ENT_QUOTES) ?>">
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

<!-- Modal Generar Comprobante -->
<div class="modal fade" id="modalComprobante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: <?= $moduloColor ?>">
                <h5 class="modal-title"><i class="fas fa-file-invoice mr-2"></i>Generar Comprobante</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formComprobante" method="POST"
                data-url="<?= url('futbol', 'comprobante', 'crear') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
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

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
$(function() {
    var Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    var csrfToken = '<?= addslashes($csrf_token ?? '') ?>';
    var urlVer     = '<?= url('futbol', 'comprobante', 'ver') ?>';
    var urlImpr    = '<?= url('futbol', 'comprobante', 'imprimir') ?>';
    var urlAnular  = '<?= url('futbol', 'comprobante', 'anular') ?>';

    try {
        $('#tblComprobantes').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            responsive: true,
            order: [[6, 'desc']]
        });
    } catch(e) { console.warn('DataTable:', e); }

    // Abrir modal nuevo comprobante
    $('#btnGenerarComprobante').on('click', function() {
        $('#formComprobante')[0].reset();
        $('#modalComprobante').modal('show');
    });

    // Submit generar comprobante (AJAX)
    $('#formComprobante').on('submit', function(e) {
        e.preventDefault();
        var action = $(this).attr('data-url');
        var $btn   = $(this).find('[type=submit]').prop('disabled', true);
        $.post(action, $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalComprobante').modal('hide');
                Toast.fire({ icon: 'success', title: res.message });
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                Toast.fire({ icon: 'error', title: res.message });
            }
        }, 'json').fail(function() {
            Toast.fire({ icon: 'error', title: 'Error de comunicación' });
        }).always(function() { $btn.prop('disabled', false); });
    });

    // Ver detalle comprobante
    $(document).on('click', '.js-ver-comprobante', function() {
        var id = $(this).data('id');
        $.getJSON(urlVer + '&id=' + id, function(res) {
            if (res.success && res.data) {
                var d = res.data;
                var html = '<table class="table table-sm">'
                    + '<tr><th>Número</th><td>' + (d.fcm_numero || '') + '</td></tr>'
                    + '<tr><th>Tipo</th><td>' + (d.fcm_tipo || '') + '</td></tr>'
                    + '<tr><th>Concepto</th><td>' + (d.fcm_concepto || '') + '</td></tr>'
                    + '<tr><th>Monto</th><td>$' + parseFloat(d.fcm_total || d.fcm_monto || 0).toFixed(2) + '</td></tr>'
                    + '<tr><th>Fecha</th><td>' + (d.fcm_fecha_emision || '') + '</td></tr>'
                    + '<tr><th>Estado</th><td>' + (d.fcm_estado || '') + '</td></tr>'
                    + '<tr><th>Alumno</th><td>' + ((d.alu_nombres || '') + ' ' + (d.alu_apellidos || '')).trim() + '</td></tr>'
                    + '</table>';
                Swal.fire({ title: 'Comprobante ' + (d.fcm_numero || ''), html: html, width: 600, confirmButtonText: 'Cerrar' });
            } else {
                Swal.fire('Error', res.message || 'No se pudo cargar.', 'error');
            }
        }).fail(function() {
            Swal.fire('Error', 'Error de conexión.', 'error');
        });
    });

    // Imprimir comprobante
    $(document).on('click', '.js-imprimir-comprobante', function() {
        window.open(urlImpr + '&id=' + $(this).data('id'), '_blank');
    });

    // Anular comprobante
    $(document).on('click', '.js-anular-comprobante', function() {
        var id     = $(this).data('id');
        var numero = $(this).data('numero');
        var $row   = $(this).closest('tr');
        Swal.fire({
            title: '¿Anular comprobante?',
            html: 'Se anulará el comprobante <strong>' + numero + '</strong>. Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-times-circle mr-1"></i> Anular',
            cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $.post(urlAnular, { csrf_token: csrfToken, id: id }, function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    $row.fadeOut(400, function() { location.reload(); });
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            }, 'json').fail(function() {
                Toast.fire({ icon: 'error', title: 'Error de comunicación' });
            });
        });
    });
});
</script>
<?php $scripts = ob_get_clean(); ?>
