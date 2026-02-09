<?php
/**
 * DigiSports Arena — Vista: Ticket / Comprobante de Entrada
 * Ticket imprimible estilo POS
 */
$entrada = $entrada ?? [];

$tipoColors  = ['GENERAL'=>'primary','VIP'=>'warning','CORTESIA'=>'info','ABONADO'=>'success'];
$estadoColors = ['VENDIDA'=>'success','USADA'=>'secondary','ANULADA'=>'danger','VENCIDA'=>'dark'];
$tc = $tipoColors[$entrada['ent_tipo'] ?? ''] ?? 'secondary';
$ec = $estadoColors[$entrada['ent_estado'] ?? ''] ?? 'secondary';
?>

<div class="content-header d-print-none">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-ticket-alt mr-2 text-info"></i> Ticket de Entrada</h1>
            </div>
            <div class="col-sm-6 text-right">
                <button onclick="window.print()" class="btn btn-primary mr-2">
                    <i class="fas fa-print mr-1"></i> Imprimir
                </button>
                <a href="<?= url('instalaciones', 'entrada', 'index') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">

                <div class="card" id="ticket">
                    <!-- Header -->
                    <div class="card-header bg-gradient-dark text-white text-center py-4">
                        <h3 class="mb-1"><i class="fas fa-building mr-2"></i> DigiSports Arena</h3>
                        <p class="mb-0 small">ENTRADA DE ACCESO</p>
                    </div>

                    <div class="card-body text-center">
                        <!-- Código grande -->
                        <div class="my-3">
                            <span class="badge badge-<?= $tc ?> px-3 py-2 mb-2" style="font-size:0.9em;">
                                <?= $entrada['ent_tipo'] ?>
                            </span>
                            <h2 class="font-weight-bold text-dark mb-1" style="letter-spacing: 3px;">
                                <?= htmlspecialchars($entrada['ent_codigo']) ?>
                            </h2>
                            <span class="badge badge-<?= $ec ?> px-3 py-1"><?= $entrada['ent_estado'] ?></span>
                        </div>

                        <hr class="border-dashed">

                        <!-- Detalles -->
                        <table class="table table-borderless table-sm text-left mb-0">
                            <tr>
                                <td class="text-muted" width="40%">Instalación:</td>
                                <td><strong><?= htmlspecialchars($entrada['instalacion_nombre']) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Fecha:</td>
                                <td><strong><?= date('d/m/Y', strtotime($entrada['ent_fecha_entrada'])) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Hora:</td>
                                <td>
                                    <strong><?= $entrada['ent_hora_entrada'] ? date('H:i', strtotime($entrada['ent_hora_entrada'])) : '—' ?></strong>
                                    <?php if ($entrada['ent_hora_salida']): ?>
                                        — <?= date('H:i', strtotime($entrada['ent_hora_salida'])) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (!empty($entrada['cliente_nombre'])): ?>
                            <tr>
                                <td class="text-muted">Cliente:</td>
                                <td><?= htmlspecialchars($entrada['cliente_nombre']) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>

                        <hr class="border-dashed">

                        <!-- Precio -->
                        <?php if ($entrada['ent_tipo'] === 'CORTESIA'): ?>
                            <div class="text-center">
                                <span class="badge badge-info px-4 py-2" style="font-size:1.1em;">CORTESÍA</span>
                            </div>
                        <?php else: ?>
                            <table class="table table-borderless table-sm text-left mb-0">
                                <tr>
                                    <td class="text-muted">Precio:</td>
                                    <td class="text-right">$<?= number_format($entrada['ent_precio'], 2) ?></td>
                                </tr>
                                <?php if ((float)$entrada['ent_descuento'] > 0): ?>
                                <tr>
                                    <td class="text-muted">Descuento:</td>
                                    <td class="text-right text-danger">-$<?= number_format($entrada['ent_descuento'], 2) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ((float)$entrada['ent_monto_monedero'] > 0): ?>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-wallet text-warning"></i> Monedero:</td>
                                    <td class="text-right text-warning">$<?= number_format($entrada['ent_monto_monedero'], 2) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="border-top">
                                    <td class="font-weight-bold">TOTAL:</td>
                                    <td class="text-right">
                                        <strong class="h4 text-success">$<?= number_format($entrada['ent_total'], 2) ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Forma de pago:</td>
                                    <td class="text-right"><?= htmlspecialchars($entrada['ent_forma_pago']) ?></td>
                                </tr>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer text-center bg-light">
                        <small class="text-muted">
                            Generado: <?= date('d/m/Y H:i') ?>
                            <br>Este ticket es su comprobante de acceso
                        </small>
                    </div>
                </div>

                <?php if ($entrada['ent_estado'] === 'VENDIDA'): ?>
                <div class="text-center d-print-none">
                    <button class="btn btn-success btn-lg mr-2" id="btnIngreso" data-id="<?= $entrada['ent_entrada_id'] ?>">
                        <i class="fas fa-door-open mr-1"></i> Registrar Ingreso
                    </button>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<style>
.border-dashed { border-top: 2px dashed #dee2e6; }
@media print {
    .main-sidebar, .main-header, .main-footer, .content-header, .d-print-none { display: none !important; }
    .content-wrapper { margin-left: 0 !important; }
    #ticket { border: 1px solid #000; max-width: 80mm; margin: 0 auto; }
}
</style>

<?php if ($entrada['ent_estado'] === 'VENDIDA'): ?>
<script>
document.getElementById('btnIngreso').addEventListener('click', function() {
    var id = this.dataset.id;
    Swal.fire({
        title: '¿Registrar ingreso?',
        text: 'La entrada pasará a estado USADA',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: '<i class="fas fa-door-open mr-1"></i> Sí, registrar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.isConfirmed) {
            var form = new FormData();
            form.append('entrada_id', id);
            fetch('<?= url('instalaciones', 'entrada', 'registrarIngreso') ?>', {
                method: 'POST', body: form
            }).then(function(r){ return r.json(); }).then(function(data) {
                if (data.success) {
                    Swal.fire('¡Ingreso registrado!', data.message, 'success').then(function(){ location.reload(); });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
});
</script>
<?php endif; ?>
