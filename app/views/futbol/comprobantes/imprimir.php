<?php
/**
 * Vista de Impresión de Comprobante - Módulo Fútbol
 * @vars $comprobante, $modulo_actual
 */
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
$c = $comprobante ?? [];
$datos = $c['datos_extra'] ?? [];

$alumnoNombre = trim(($c['alu_nombres'] ?? '') . ' ' . ($c['alu_apellidos'] ?? ''));
if (empty(trim($alumnoNombre))) $alumnoNombre = $datos['alumno_nombre'] ?? 'N/A';
$alumnoCI = $c['alu_identificacion'] ?? $datos['alumno_identificacion'] ?? '';
$representante = $c['representante_nombre'] ?? '';
$repTelefono = $c['representante_telefono'] ?? '';
$repEmail = $c['representante_email'] ?? '';
$repIdentificacion = $c['representante_identificacion'] ?? '';
$sede = $c['sede_nombre'] ?? '';

$tiposBadge = [
    'RECIBO' => 'info',
    'FACTURA' => 'success',
    'NOTA_CREDITO' => 'warning',
];
$estadosBadge = [
    'EMITIDO' => 'success',
    'ANULADO' => 'danger',
];
?>

<!-- Content Header -->
<div class="content-header d-print-none">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-print" style="color: <?= $moduloColor ?>"></i>
                    Comprobante <?= htmlspecialchars($c['fcm_numero'] ?? '') ?>
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <button onclick="window.print()" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;">
                    <i class="fas fa-print mr-1"></i>Imprimir
                </button>
                <a href="<?= url('futbol', 'comprobante', 'index') ?>" class="btn btn-secondary btn-sm ml-2">
                    <i class="fas fa-arrow-left mr-1"></i>Volver
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card" id="comprobante-card">
            <div class="card-body p-4">

                <!-- Encabezado -->
                <div class="row mb-4">
                    <div class="col-6">
                        <h4 class="mb-0" style="color:<?= $moduloColor ?>">
                            <i class="fas fa-futbol mr-1"></i> Escuela de Fútbol
                        </h4>
                        <?php if ($sede): ?>
                        <small class="text-muted">Sede: <?= htmlspecialchars($sede) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-6 text-right">
                        <h5 class="mb-1">
                            <span class="badge badge-<?= $tiposBadge[$c['fcm_tipo'] ?? ''] ?? 'secondary' ?>">
                                <?= htmlspecialchars($c['fcm_tipo'] ?? '') ?>
                            </span>
                        </h5>
                        <h4 class="text-bold mb-0"><?= htmlspecialchars($c['fcm_numero'] ?? '') ?></h4>
                        <small class="text-muted">
                            Fecha: <?= !empty($c['fcm_fecha_emision']) ? date('d/m/Y', strtotime($c['fcm_fecha_emision'])) : 'N/A' ?>
                        </small>
                    </div>
                </div>

                <hr>

                <!-- Datos del alumno/representante -->
                <div class="row mb-4">
                    <div class="col-6">
                        <h6 class="text-bold text-muted mb-2">DATOS DEL ALUMNO</h6>
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted pr-3" style="width:120px">Nombre:</td><td class="font-weight-bold"><?= htmlspecialchars($alumnoNombre) ?></td></tr>
                            <?php if ($alumnoCI): ?>
                            <tr><td class="text-muted">Identificación:</td><td><?= htmlspecialchars($alumnoCI) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($representante): ?>
                            <tr><td class="text-muted">Representante:</td><td><?= htmlspecialchars($representante) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($repIdentificacion): ?>
                            <tr><td class="text-muted">CI Representante:</td><td><?= htmlspecialchars($repIdentificacion) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($repTelefono): ?>
                            <tr><td class="text-muted">Teléfono:</td><td><?= htmlspecialchars($repTelefono) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($repEmail): ?>
                            <tr><td class="text-muted">Email:</td><td><?= htmlspecialchars($repEmail) ?></td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="col-6">
                        <h6 class="text-bold text-muted mb-2">DATOS DEL PAGO</h6>
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted pr-3" style="width:120px">Tipo Pago:</td><td><?= htmlspecialchars($c['pago_tipo'] ?? $datos['pago_tipo'] ?? 'N/A') ?></td></tr>
                            <tr><td class="text-muted">Método:</td><td><?= htmlspecialchars($c['pago_metodo'] ?? $datos['pago_metodo'] ?? 'N/A') ?></td></tr>
                            <?php if (!empty($c['pago_fecha'])): ?>
                            <tr><td class="text-muted">Fecha Pago:</td><td><?= date('d/m/Y', strtotime($c['pago_fecha'])) ?></td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Detalle del comprobante -->
                <table class="table table-bordered mb-4">
                    <thead style="background:<?= $moduloColor ?>;color:white;">
                        <tr>
                            <th>Concepto</th>
                            <th class="text-right" style="width:140px">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars($c['fcm_concepto'] ?? '') ?></td>
                            <td class="text-right">$ <?= number_format((float)($c['pago_monto'] ?? $c['fcm_monto'] ?? 0), 2) ?></td>
                        </tr>
                        <?php
                        $descuento = (float)($c['pago_descuento'] ?? $datos['descuento'] ?? 0);
                        $recargo = (float)($c['pago_recargo'] ?? $datos['recargo_mora'] ?? 0);
                        if ($descuento > 0):
                        ?>
                        <tr class="text-success">
                            <td><i class="fas fa-tag mr-1"></i>Descuento</td>
                            <td class="text-right">- $ <?= number_format($descuento, 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($recargo > 0): ?>
                        <tr class="text-danger">
                            <td><i class="fas fa-exclamation-triangle mr-1"></i>Recargo por mora</td>
                            <td class="text-right">+ $ <?= number_format($recargo, 2) ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold" style="font-size:1.15em;">
                            <td class="text-right">TOTAL</td>
                            <td class="text-right">$ <?= number_format((float)($c['fcm_monto'] ?? 0), 2) ?></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Estado -->
                <div class="row">
                    <div class="col-6">
                        <span class="badge badge-<?= $estadosBadge[$c['fcm_estado'] ?? ''] ?? 'secondary' ?>" style="font-size:0.9em;">
                            <?= htmlspecialchars($c['fcm_estado'] ?? '') ?>
                        </span>
                    </div>
                    <div class="col-6 text-right text-muted">
                        <small>Emitido: <?= !empty($c['fcm_created_at']) ? date('d/m/Y H:i', strtotime($c['fcm_created_at'])) : '' ?></small>
                    </div>
                </div>

                <?php if (($c['fcm_estado'] ?? '') === 'ANULADO'): ?>
                <div class="text-center mt-3">
                    <span style="font-size:2.5em;color:#dc3545;opacity:0.3;font-weight:bold;transform:rotate(-20deg);display:inline-block;border:5px solid #dc3545;padding:5px 30px;border-radius:10px;">
                        ANULADO
                    </span>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<style>
@media print {
    .main-header, .main-sidebar, .main-footer, .content-header.d-print-none, .breadcrumb { display: none !important; }
    .content-wrapper { margin: 0 !important; padding: 0 !important; }
    #comprobante-card { border: none !important; box-shadow: none !important; }
}
</style>
<?php $scripts = ob_get_clean(); ?>
