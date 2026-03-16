<?php
$cfg    = $config ?? [];
$f      = $factura;
$estado = $f['fac_estado'] ?? 'BORRADOR';

$estadoClase = [
    'BORRADOR' => 'secondary',
    'EMITIDA'  => 'warning',
    'PAGADA'   => 'success',
    'ANULADA'  => 'danger',
];
$badgeCls = $estadoClase[$estado] ?? 'secondary';

$tipoIdentLabel = [
    'RUC'             => 'RUC',
    'CEDULA'          => 'Cédula',
    'PASAPORTE'       => 'Pasaporte',
    'CONSUMIDOR_FINAL'=> 'Consumidor Final',
];

$urlVolver = url('facturacion', 'factura', 'ver', ['id' => $f['fac_id'] ?? 0]);

$subtotal = (float)($f['fac_subtotal']  ?? 0);
$iva      = (float)($f['fac_iva']       ?? 0);
$dto      = (float)($f['fac_descuento'] ?? 0);
$total    = (float)($f['fac_total']     ?? 0);
?>

<style>
    /* ── Área de impresión ─────────────────────────────────────────── */
    #factura-print {
        max-width: 820px;
        margin: 0 auto;
        background: #fff;
        font-family: Arial, sans-serif;
        font-size: 13px;
        color: #222;
    }
    .fac-header { border-bottom: 2px solid #2c3e50; padding-bottom: 14px; margin-bottom: 18px; }
    .fac-numero { font-size: 26px; font-weight: bold; color: #1a56a0; line-height: 1; }
    .fac-numero-label { font-size: 10px; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
    .fac-empresa { font-size: 15px; font-weight: 700; text-transform: uppercase; color: #2c3e50; }
    .fac-empresa-sub { font-size: 12px; color: #555; margin-top: 2px; }

    .fac-info-box { border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px 14px; height: 100%; }
    .fac-info-title {
        font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #999; font-weight: 700;
        border-bottom: 1px solid #f0f0f0; padding-bottom: 6px; margin-bottom: 8px;
    }
    .fac-info-row { display: flex; justify-content: space-between; align-items: flex-start;
                    margin-bottom: 4px; font-size: 12px; gap: 8px; }
    .fac-info-row .lbl { color: #777; white-space: nowrap; }
    .fac-info-row .val { font-weight: 600; text-align: right; word-break: break-word; }

    #factura-print table { width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 12px; }
    #factura-print thead th {
        background: #2c3e50; color: #fff; border: none;
        padding: 8px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: .5px;
    }
    #factura-print thead th:first-child { border-radius: 4px 0 0 0; }
    #factura-print thead th:last-child  { border-radius: 0 4px 0 0; }
    #factura-print tbody td { border-bottom: 1px solid #f0f0f0; padding: 8px 10px; vertical-align: middle; }
    #factura-print tbody tr:last-child td { border-bottom: none; }
    #factura-print tbody tr:hover td { background: #fafcff; }

    .totales-box { width: 260px; margin-left: auto; border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px 14px; }
    .total-row { display: flex; justify-content: space-between; padding: 5px 0;
                 border-bottom: 1px solid #f5f5f5; font-size: 12px; }
    .total-row:last-child { border-bottom: none; }
    .total-row.grand {
        border-top: 2px solid #2c3e50; border-bottom: none; font-size: 16px;
        font-weight: 700; padding-top: 10px; margin-top: 4px; color: #1a56a0;
    }

    /* ── Sólo en impresión ─────────────────────────────────────────── */
    @media print {
        .main-sidebar, .main-header, .content-header, .main-footer, #barra-acciones { display: none !important; }
        .content-wrapper { margin-left: 0 !important; padding: 0 !important; background: #fff !important; }
        .wrapper { overflow: visible !important; }
        #factura-print { max-width: 100%; box-shadow: none !important; border: none !important; }
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        #factura-print thead th { background: #2c3e50 !important; color: #fff !important; }
    }
</style>

<div class="container-fluid py-3">

    <!-- ── Barra de acciones (no se imprime) ──────────────────────── -->
    <div id="barra-acciones" class="d-flex align-items-center mb-3 flex-wrap" style="gap:8px;">
        <a id="btn-volver" href="<?= $urlVolver ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
        <button id="btn-imprimir" type="button" class="btn btn-primary">
            <i class="fas fa-print mr-1"></i> Imprimir / Guardar PDF
        </button>
        <span class="text-muted small ml-2">
            <i class="fas fa-info-circle mr-1 text-info"></i>
            En el diálogo de impresión seleccione <strong>"Guardar como PDF"</strong> para obtener el archivo.
        </span>
    </div>

    <!-- ── Documento ──────────────────────────────────────────────── -->
    <div id="factura-print" class="shadow-sm border rounded p-4">

        <!-- ENCABEZADO -->
        <div class="fac-header d-flex justify-content-between align-items-start">
            <div>
                <div class="fac-empresa"><?= htmlspecialchars($cfg['cfg_razon_social'] ?? 'EMPRESA') ?></div>
                <?php if (!empty($cfg['cfg_nombre_comercial'])): ?>
                    <div class="fac-empresa-sub"><?= htmlspecialchars($cfg['cfg_nombre_comercial']) ?></div>
                <?php endif; ?>
                <div class="fac-empresa-sub">RUC: <?= htmlspecialchars($cfg['cfg_ruc'] ?? '') ?></div>
                <div class="fac-empresa-sub"><?= htmlspecialchars($cfg['cfg_direccion_establecimiento'] ?? $cfg['cfg_direccion_matriz'] ?? '') ?></div>
            </div>
            <div class="text-right">
                <div class="fac-numero-label">Factura N°</div>
                <div class="fac-numero"><?= htmlspecialchars($f['fac_numero'] ?? '') ?></div>
                <span class="badge badge-<?= $badgeCls ?> mt-2" style="font-size:11px;padding:5px 10px;">
                    <?= htmlspecialchars($estado) ?>
                </span>
            </div>
        </div>

        <!-- DATOS DE EMISIÓN Y CLIENTE -->
        <div class="row mb-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="fac-info-box">
                    <div class="fac-info-title"><i class="fas fa-calendar-alt mr-1"></i>Datos de Emisión</div>
                    <div class="fac-info-row">
                        <span class="lbl">Fecha emisión:</span>
                        <span class="val"><?= !empty($f['fac_fecha_emision']) ? date('d/m/Y', strtotime($f['fac_fecha_emision'])) : '-' ?></span>
                    </div>
                    <?php if (!empty($f['fac_fecha_vencimiento'])): ?>
                    <div class="fac-info-row">
                        <span class="lbl">Vencimiento:</span>
                        <span class="val"><?= date('d/m/Y', strtotime($f['fac_fecha_vencimiento'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="fac-info-row">
                        <span class="lbl">Forma de pago:</span>
                        <span class="val"><?= htmlspecialchars($f['forma_pago_nombre'] ?? '-') ?></span>
                    </div>
                    <?php if (!empty($cfg['cfg_obligado_contabilidad'])): ?>
                    <div class="fac-info-row">
                        <span class="lbl">Obligado contabilidad:</span>
                        <span class="val"><?= htmlspecialchars($cfg['cfg_obligado_contabilidad']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="fac-info-box">
                    <div class="fac-info-title"><i class="fas fa-user mr-1"></i>Cliente</div>
                    <div class="fac-info-row">
                        <span class="lbl"><?= htmlspecialchars($tipoIdentLabel[$f['tipo_ident_cliente'] ?? ''] ?? 'Identificación') ?>:</span>
                        <span class="val"><?= htmlspecialchars($f['identificacion_cliente'] ?? '-') ?></span>
                    </div>
                    <div class="fac-info-row">
                        <span class="lbl">Nombre:</span>
                        <span class="val"><?= htmlspecialchars($f['nombre_cliente'] ?? '-') ?></span>
                    </div>
                    <?php if (!empty($f['direccion_cliente'])): ?>
                    <div class="fac-info-row">
                        <span class="lbl">Dirección:</span>
                        <span class="val"><?= htmlspecialchars($f['direccion_cliente']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($f['email_cliente'])): ?>
                    <div class="fac-info-row">
                        <span class="lbl">Email:</span>
                        <span class="val"><?= htmlspecialchars($f['email_cliente']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($f['telefono_cliente'])): ?>
                    <div class="fac-info-row">
                        <span class="lbl">Teléfono:</span>
                        <span class="val"><?= htmlspecialchars($f['telefono_cliente']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- DETALLE DE ÍTEMS -->
        <p style="font-size:9px;text-transform:uppercase;letter-spacing:1px;color:#999;font-weight:700;margin-bottom:6px;">
            <i class="fas fa-list mr-1"></i>Detalle de Ítems
        </p>
        <div class="table-responsive mb-3">
            <table>
                <thead>
                    <tr>
                        <th style="width:85px;">Código</th>
                        <th>Descripción</th>
                        <th class="text-right" style="width:65px;">Cant.</th>
                        <th class="text-right" style="width:90px;">P. Unitario</th>
                        <th class="text-right" style="width:70px;">Descuento</th>
                        <th class="text-right" style="width:55px;">IVA %</th>
                        <th class="text-right" style="width:90px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lineas as $lin): ?>
                    <?php
                        $cant    = (float)($lin['lin_cantidad']       ?? 0);
                        $precio  = (float)($lin['lin_precio_unitario'] ?? 0);
                        $dtoLin  = (float)($lin['lin_descuento']       ?? 0);
                        $subtLin = $cant * $precio - $dtoLin;
                    ?>
                    <tr>
                        <td><code style="font-size:11px;"><?= htmlspecialchars($lin['lin_codigo'] ?? '') ?></code></td>
                        <td><?= htmlspecialchars($lin['lin_descripcion'] ?? '') ?></td>
                        <td class="text-right"><?= number_format($cant, 2) ?></td>
                        <td class="text-right">$<?= number_format($precio, 2) ?></td>
                        <td class="text-right">$<?= number_format($dtoLin, 2) ?></td>
                        <td class="text-right"><?= number_format((float)($lin['lin_porcentaje_iva'] ?? 0), 0) ?>%</td>
                        <td class="text-right"><strong>$<?= number_format($subtLin, 2) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- TOTALES -->
        <div class="d-flex <?= !empty($f['fac_observaciones']) ? 'align-items-start' : 'justify-content-end' ?> mb-4" style="gap:16px;">
            <?php if (!empty($f['fac_observaciones'])): ?>
            <div class="flex-grow-1">
                <div class="fac-info-box" style="font-size:12px;">
                    <div class="fac-info-title"><i class="fas fa-comment mr-1"></i>Observaciones</div>
                    <div style="color:#555;white-space:pre-wrap;"><?= htmlspecialchars($f['fac_observaciones']) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <div class="totales-box flex-shrink-0">
                <div class="total-row">
                    <span class="text-muted">Subtotal:</span>
                    <span>$<?= number_format($subtotal, 2) ?></span>
                </div>
                <?php if ($dto > 0): ?>
                <div class="total-row">
                    <span class="text-muted">Descuento:</span>
                    <span class="text-danger">- $<?= number_format($dto, 2) ?></span>
                </div>
                <?php endif; ?>
                <div class="total-row">
                    <span class="text-muted">IVA:</span>
                    <span>$<?= number_format($iva, 2) ?></span>
                </div>
                <div class="total-row grand">
                    <span>TOTAL:</span>
                    <span>$<?= number_format($total, 2) ?></span>
                </div>
            </div>
        </div>

        <!-- PIE -->
        <div style="border-top:1px solid #eee;padding-top:8px;font-size:10px;color:#bbb;text-align:center;margin-top:8px;">
            Documento generado por DigiSports &mdash; <?= date('d/m/Y H:i') ?>
        </div>

    </div><!-- /#factura-print -->
</div>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
(function () {
    'use strict';

    document.getElementById('btn-imprimir').addEventListener('click', function () {
        window.print();
    });

    <?php if (!empty($autoPrint)): ?>
    window.addEventListener('load', function () { window.print(); });
    <?php endif; ?>
}());
</script>
<?php $scripts = ob_get_clean(); ?>
