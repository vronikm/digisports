<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Factura Electrónica <?= htmlspecialchars($numero ?? '') ?></title>
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#333;">

<!-- Wrapper -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:30px 0;">
<tr><td align="center">

<!-- Container -->
<table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">

  <!-- Header -->
  <tr>
    <td style="background:#1a56db;padding:28px 32px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td>
            <div style="color:#fff;font-size:22px;font-weight:bold;letter-spacing:.5px;">
              DigiSports
            </div>
            <div style="color:#93c5fd;font-size:12px;margin-top:2px;">
              Sistema de Facturación Electrónica
            </div>
          </td>
          <td align="right">
            <div style="background:#fff;color:#1a56db;font-weight:bold;font-size:13px;
                        padding:6px 14px;border-radius:4px;display:inline-block;">
              ✓ AUTORIZADA
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <?php if (($ambiente ?? 1) == 1): ?>
  <!-- Banner pruebas -->
  <tr>
    <td style="background:#fef3c7;padding:8px 32px;text-align:center;font-size:12px;color:#92400e;font-weight:bold;">
      ⚠️ AMBIENTE DE PRUEBAS — SIN VALIDEZ TRIBUTARIA
    </td>
  </tr>
  <?php endif; ?>

  <!-- Saludo -->
  <tr>
    <td style="padding:28px 32px 0;">
      <p style="margin:0 0 6px;font-size:16px;font-weight:bold;color:#1e3a5f;">
        Estimado(a) <?= htmlspecialchars($cliente_nombre ?? 'cliente') ?>
      </p>
      <p style="margin:0;color:#555;line-height:1.6;">
        Su factura electrónica ha sido emitida y <strong style="color:#16a34a;">AUTORIZADA</strong>
        por el Servicio de Rentas Internas (SRI) del Ecuador. Adjuntamos el
        RIDE en PDF y el comprobante XML autorizado.
      </p>
    </td>
  </tr>

  <!-- Datos de la factura -->
  <tr>
    <td style="padding:20px 32px;">
      <table width="100%" cellpadding="0" cellspacing="0"
             style="border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;font-size:13px;">
        <tr style="background:#f8fafc;">
          <td colspan="2" style="padding:10px 14px;font-weight:bold;color:#1e3a5f;
                                  border-bottom:1px solid #e2e8f0;font-size:14px;">
            📄 Datos del Comprobante
          </td>
        </tr>
        <tr>
          <td style="padding:8px 14px;color:#64748b;width:45%;border-bottom:1px solid #f1f5f9;">Número de Factura</td>
          <td style="padding:8px 14px;font-weight:bold;border-bottom:1px solid #f1f5f9;">
            <?= htmlspecialchars($numero ?? '-') ?>
          </td>
        </tr>
        <tr style="background:#fafafa;">
          <td style="padding:8px 14px;color:#64748b;border-bottom:1px solid #f1f5f9;">Fecha de Emisión</td>
          <td style="padding:8px 14px;border-bottom:1px solid #f1f5f9;">
            <?= htmlspecialchars($fecha_emision ?? '-') ?>
          </td>
        </tr>
        <tr>
          <td style="padding:8px 14px;color:#64748b;border-bottom:1px solid #f1f5f9;">N° Autorización SRI</td>
          <td style="padding:8px 14px;font-family:monospace;font-size:11px;
                     word-break:break-all;border-bottom:1px solid #f1f5f9;color:#1a56db;">
            <?= htmlspecialchars($numero_autorizacion ?? '-') ?>
          </td>
        </tr>
        <tr style="background:#fafafa;">
          <td style="padding:8px 14px;color:#64748b;border-bottom:1px solid #f1f5f9;">Fecha Autorización</td>
          <td style="padding:8px 14px;border-bottom:1px solid #f1f5f9;">
            <?= htmlspecialchars($fecha_autorizacion ?? '-') ?>
          </td>
        </tr>
        <tr>
          <td style="padding:8px 14px;color:#64748b;border-bottom:1px solid #f1f5f9;">Identificación</td>
          <td style="padding:8px 14px;border-bottom:1px solid #f1f5f9;">
            <?= htmlspecialchars($cliente_identificacion ?? '-') ?>
          </td>
        </tr>
        <tr style="background:#fafafa;">
          <td style="padding:8px 14px;color:#64748b;">Emisor</td>
          <td style="padding:8px 14px;"><?= htmlspecialchars($emisor_nombre ?? '-') ?></td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Valores -->
  <tr>
    <td style="padding:0 32px 20px;">
      <table width="100%" cellpadding="0" cellspacing="0"
             style="border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;font-size:13px;">
        <tr style="background:#f8fafc;">
          <td colspan="2" style="padding:10px 14px;font-weight:bold;color:#1e3a5f;
                                  border-bottom:1px solid #e2e8f0;font-size:14px;">
            💰 Valores
          </td>
        </tr>
        <?php if (($subtotal_0 ?? 0) > 0): ?>
        <tr>
          <td style="padding:7px 14px;color:#64748b;border-bottom:1px solid #f1f5f9;">Subtotal 0%</td>
          <td style="padding:7px 14px;text-align:right;border-bottom:1px solid #f1f5f9;">
            $<?= number_format((float)($subtotal_0 ?? 0), 2) ?>
          </td>
        </tr>
        <?php endif; ?>
        <?php if (($subtotal_iva ?? 0) > 0): ?>
        <tr style="background:#fafafa;">
          <td style="padding:7px 14px;color:#64748b;border-bottom:1px solid #f1f5f9;">Subtotal con IVA</td>
          <td style="padding:7px 14px;text-align:right;border-bottom:1px solid #f1f5f9;">
            $<?= number_format((float)($subtotal_iva ?? 0), 2) ?>
          </td>
        </tr>
        <tr>
          <td style="padding:7px 14px;color:#64748b;border-bottom:1px solid #f1f5f9;">IVA</td>
          <td style="padding:7px 14px;text-align:right;border-bottom:1px solid #f1f5f9;">
            $<?= number_format((float)($iva ?? 0), 2) ?>
          </td>
        </tr>
        <?php endif; ?>
        <tr style="background:#1a56db;">
          <td style="padding:10px 14px;color:#fff;font-weight:bold;font-size:15px;">TOTAL</td>
          <td style="padding:10px 14px;color:#fff;font-weight:bold;font-size:15px;text-align:right;">
            $<?= number_format((float)($total ?? 0), 2) ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Nota adjuntos -->
  <tr>
    <td style="padding:0 32px 24px;">
      <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;
                  padding:14px 18px;font-size:13px;color:#0369a1;line-height:1.6;">
        <strong>📎 Adjuntos en este correo:</strong><br>
        • <strong>RIDE.pdf</strong> — Representación Impresa del Documento Electrónico (si disponible)<br>
        • <strong>Factura_[clave].xml</strong> — Comprobante electrónico autorizado por el SRI
      </div>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 32px;
               text-align:center;font-size:12px;color:#94a3b8;line-height:1.7;">
      <strong style="color:#64748b;"><?= htmlspecialchars($emisor_nombre ?? 'DigiSports') ?></strong><br>
      Este es un mensaje generado automáticamente, por favor no responda este correo.<br>
      <span style="font-size:11px;">Comprobante válido según LORTI — Reglamento de Comprobantes Electrónicos SRI Ecuador</span>
    </td>
  </tr>

</table>
<!-- /Container -->

</td></tr>
</table>
<!-- /Wrapper -->

</body>
</html>
