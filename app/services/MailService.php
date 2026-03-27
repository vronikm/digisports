<?php
/**
 * DigiSports — Servicio de Correo Electrónico
 *
 * Centraliza el envío de emails usando PHPMailer.
 * Lee configuración SMTP desde config/smtp.php (que a su vez lee .env).
 *
 * @package DigiSports\Services
 * @version 1.0.0
 */

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

class MailService {

    private array $config;

    public function __construct() {
        $this->config = require BASE_PATH . '/config/smtp.php';
    }

    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Enviar factura electrónica autorizada al cliente.
     *
     * @param string      $emailDestino     Email del cliente (en claro, ya desencriptado)
     * @param array       $datos            Datos para renderizar el template:
     *                                        numero, clave_acceso, numero_autorizacion,
     *                                        fecha_emision, fecha_autorizacion,
     *                                        cliente_nombre, cliente_identificacion,
     *                                        emisor_nombre, subtotal, iva, total, ambiente
     * @param string|null $rutaPdf          Ruta absoluta al PDF del RIDE (null = no adjuntar)
     * @param string|null $rutaXmlAutorizado Ruta absoluta al XML autorizado (null = no adjuntar)
     * @return array{exito: bool, mensaje: string}
     */
    public function enviarFacturaElectronica(
        string  $emailDestino,
        array   $datos,
        ?string $rutaPdf          = null,
        ?string $rutaXmlAutorizado = null
    ): array {
        if (empty($emailDestino) || !filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
            return ['exito' => false, 'mensaje' => 'Email del cliente no válido o no disponible'];
        }

        try {
            $mailer = $this->crearMailer();

            $mailer->addAddress($emailDestino, $datos['cliente_nombre'] ?? '');

            $asunto = sprintf('Factura Electrónica %s — %s',
                $datos['numero']       ?? '',
                $datos['emisor_nombre'] ?? 'DigiSports'
            );
            $mailer->Subject = $asunto;

            // Cuerpo HTML
            $mailer->isHTML(true);
            $mailer->Body    = $this->renderTemplate($datos);
            $mailer->AltBody = $this->textoPlano($datos);

            // Adjunto PDF (si existe)
            if ($rutaPdf && file_exists($rutaPdf)) {
                $mailer->addAttachment($rutaPdf,
                    'Factura_' . ($datos['numero'] ?? 'FE') . '.pdf'
                );
            }

            // Adjunto XML autorizado (si existe)
            if ($rutaXmlAutorizado && file_exists($rutaXmlAutorizado)) {
                $mailer->addAttachment($rutaXmlAutorizado,
                    'Factura_' . ($datos['clave_acceso'] ?? 'FE') . '.xml'
                );
            }

            $mailer->send();

            error_log('[MailService] FE enviada a ' . $emailDestino . ' — ' . ($datos['numero'] ?? ''));
            return ['exito' => true, 'mensaje' => 'Comprobante enviado a ' . $emailDestino];

        } catch (MailerException $e) {
            error_log('[MailService] Error PHPMailer: ' . $e->getMessage());
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        } catch (\Exception $e) {
            error_log('[MailService] Error inesperado: ' . $e->getMessage());
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }

    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Enviar comprobante/recibo de pago al representante.
     *
     * @param string $emailDestino  Email del representante (ya desencriptado)
     * @param array  $datos         rep_nombre, alumno_nombre, numero, tipo, concepto,
     *                              fecha, total, pago_metodo, sede_nombre, empresa_nombre
     * @return array{exito: bool, mensaje: string}
     */
    public function enviarComprobantePago(string $emailDestino, array $datos, ?string $rutaPdf = null, ?string $numeroRecibo = null): array {
        if (empty($emailDestino) || !filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
            return ['exito' => false, 'mensaje' => 'Email del representante no válido o no disponible'];
        }
        try {
            $mailer = $this->crearMailer();
            $mailer->addAddress($emailDestino, $datos['rep_nombre'] ?? '');

            $empresa = $datos['empresa_nombre'] ?? 'Escuela de Fútbol';
            $sede    = $datos['sede_nombre']    ?? $empresa;
            $numero  = $numeroRecibo ?? ($datos['numero'] ?? '');
            $mailer->Subject = "Comprobante de pago {$numero} — {$sede}";
            $mailer->isHTML(true);
            $mailer->Body    = $this->renderComprobanteTemplate($datos);
            $mailer->AltBody = $this->textoPlanoComprobante($datos);

            if ($rutaPdf && file_exists($rutaPdf)) {
                $mailer->addAttachment($rutaPdf, 'Recibo_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $numero) . '.pdf');
            }

            $mailer->send();
            error_log('[MailService] Comprobante ' . $numero . ' enviado a ' . $emailDestino);
            return ['exito' => true, 'mensaje' => 'Comprobante enviado a ' . $emailDestino];

        } catch (MailerException $e) {
            error_log('[MailService] Error comprobante PHPMailer: ' . $e->getMessage());
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        } catch (\Exception $e) {
            error_log('[MailService] Error inesperado comprobante: ' . $e->getMessage());
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Crear instancia de PHPMailer configurada con SMTP
     */
    private function crearMailer(): PHPMailer {
        $m = new PHPMailer(true);
        $m->isSMTP();
        $m->Host       = $this->config['host'];
        $m->SMTPAuth   = true;
        $m->Username   = $this->config['username'];
        $m->Password   = $this->config['password'];
        $m->SMTPSecure = $this->config['secure'];
        $m->Port       = $this->config['port'];
        $m->CharSet    = PHPMailer::CHARSET_UTF8;
        $m->setFrom($this->config['from'], $this->config['from_name']);
        return $m;
    }

    /**
     * Renderizar el template HTML del email de factura
     */
    private function renderTemplate(array $datos): string {
        $templateFile = BASE_PATH . '/app/views/emails/factura_electronica.php';
        if (!file_exists($templateFile)) {
            return '<p>Su factura electrónica ha sido autorizada. N° ' .
                htmlspecialchars($datos['numero'] ?? '') . '</p>';
        }

        ob_start();
        extract($datos, EXTR_SKIP);
        require $templateFile;
        return ob_get_clean();
    }

    /**
     * Template HTML para comprobante de pago
     */
    private function renderComprobanteTemplate(array $d): string {
        $rep      = htmlspecialchars($d['rep_nombre']     ?? '');
        $alumno   = htmlspecialchars($d['alumno_nombre']  ?? '');
        $numero   = htmlspecialchars($d['numero']         ?? '');
        $tipo     = htmlspecialchars($d['tipo']           ?? 'RECIBO');
        $concepto = htmlspecialchars($d['concepto']       ?? '');
        $mesRef   = htmlspecialchars($d['mes_referencia'] ?? '');
        if ($mesRef) $concepto .= ' — ' . $mesRef;
        $fecha    = !empty($d['fecha']) ? date('d/m/Y', strtotime($d['fecha'])) : '—';
        $total    = '$' . number_format((float)($d['total'] ?? 0), 2);
        $metodo   = htmlspecialchars($d['pago_metodo']    ?? '');
        $modulo   = htmlspecialchars($d['modulo_nombre']  ?? 'DigiSports Fútbol');
        $color    = '#22C55E';

        $sedeRow = '';
        if (!empty($d['sede_nombre'])) {
            $sede    = htmlspecialchars($d['sede_nombre']);
            $sedeRow = "<tr><td style='padding:10px 16px;color:#6c757d;font-size:13px;'>Sede</td>"
                     . "<td style='padding:10px 16px;font-size:13px;'>{$sede}</td></tr>";
        }

        $c2 = $color . '22';
        return '<!DOCTYPE html><html lang="es">'
            . '<head><meta charset="UTF-8"></head>'
            . '<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">'
            . '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:30px 0;">'
            . '<tr><td align="center">'
            . '<table width="580" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">'
            . "<tr><td style=\"background:{$color};padding:26px 32px;\">"
            . "<h2 style=\"color:#fff;margin:0;font-size:20px;\">&#9917;&nbsp;{$modulo}</h2>"
            . '<p style="color:rgba(255,255,255,.85);margin:4px 0 0;font-size:13px;">Comprobante de Pago</p>'
            . '</td></tr>'
            . '<tr><td style="padding:28px 32px;">'
            . "<p style=\"margin:0 0 6px;font-size:15px;\">Estimado/a <strong>{$rep}</strong>,</p>"
            . '<p style="margin:0 0 22px;color:#555;font-size:14px;">Le confirmamos la recepción del siguiente pago:</p>'
            . '<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:22px;">'
            . '<tr style="background:#f8f9fa;">'
            . '<td colspan="2" style="padding:13px 18px;border-bottom:1px solid #e5e7eb;">'
            . "<strong style=\"font-size:17px;color:#111;\">N&deg; {$numero}</strong>"
            . "<span style=\"font-size:12px;color:#888;margin-left:10px;\">{$fecha}</span>"
            . "<span style=\"float:right;font-size:11px;font-weight:bold;color:#fff;background:{$color};padding:2px 8px;border-radius:3px;\">{$tipo}</span>"
            . '</td></tr>'
            . '<tr>'
            . '<td style="padding:10px 18px;color:#6c757d;font-size:13px;width:38%;">Alumno</td>'
            . "<td style=\"padding:10px 18px;font-size:13px;font-weight:bold;\">{$alumno}</td>"
            . '</tr>'
            . '<tr style="background:#f8f9fa;">'
            . '<td style="padding:10px 18px;color:#6c757d;font-size:13px;">Concepto</td>'
            . "<td style=\"padding:10px 18px;font-size:13px;\">{$concepto}</td>"
            . '</tr>'
            . '<tr>'
            . '<td style="padding:10px 18px;color:#6c757d;font-size:13px;">M&eacute;todo de pago</td>'
            . "<td style=\"padding:10px 18px;font-size:13px;\">{$metodo}</td>"
            . '</tr>'
            . $sedeRow
            . "<tr style=\"background:{$c2};\">"
            . '<td style="padding:14px 18px;font-weight:bold;font-size:14px;color:#111;">TOTAL PAGADO</td>'
            . "<td style=\"padding:14px 18px;font-weight:bold;font-size:20px;color:{$color};\">{$total}</td>"
            . '</tr>'
            . '</table>'
            . '<p style="font-size:11px;color:#aaa;margin:0;">Comprobante generado autom&aacute;ticamente &middot; Sistema DigiSports</p>'
            . '</td></tr>'
            . '<tr><td style="background:#f8f9fa;padding:14px 32px;border-top:1px solid #e5e7eb;text-align:center;">'
            . "<p style=\"margin:0;font-size:12px;color:#999;\">{$modulo} &middot; DigiSports</p>"
            . '</td></tr>'
            . '</table>'
            . '</td></tr></table>'
            . '</body></html>';
    }

    /**
     * Versión texto plano del comprobante de pago
     */
    private function textoPlanoComprobante(array $d): string {
        $fecha = !empty($d['fecha']) ? date('d/m/Y', strtotime($d['fecha'])) : '—';
        $concepto = $d['concepto'] ?? '—';
        $mesRef   = $d['mes_referencia'] ?? '';
        if ($mesRef) $concepto .= ' — ' . $mesRef;
        return implode("\n", [
            'Estimado/a ' . ($d['rep_nombre'] ?? 'representante') . ',',
            '',
            'Le confirmamos la recepción del siguiente pago:',
            '',
            'Comprobante N° : ' . ($d['numero']       ?? '—'),
            'Alumno         : ' . ($d['alumno_nombre'] ?? '—'),
            'Concepto       : ' . $concepto,
            'Fecha          : ' . $fecha,
            'Método de pago : ' . ($d['pago_metodo']   ?? '—'),
            'TOTAL PAGADO   : $' . number_format((float)($d['total'] ?? 0), 2),
            '',
            'Atentamente,',
            $d['modulo_nombre'] ?? 'DigiSports Fútbol',
        ]);
    }

    /**
     * Versión texto plano del email (para clientes sin HTML)
     */
    private function textoPlano(array $d): string {
        return implode("\n", [
            'Estimado(a) ' . ($d['cliente_nombre'] ?? 'cliente') . ',',
            '',
            'Su factura electrónica ha sido AUTORIZADA por el SRI.',
            '',
            'Factura N°    : ' . ($d['numero'] ?? '-'),
            'Fecha emisión : ' . ($d['fecha_emision'] ?? '-'),
            'N° Autorización: ' . ($d['numero_autorizacion'] ?? '-'),
            'Total         : $' . number_format((float)($d['total'] ?? 0), 2),
            '',
            'Encuentra el RIDE (PDF) y el XML autorizado adjuntos en este correo.',
            '',
            'Atentamente,',
            $d['emisor_nombre'] ?? 'DigiSports',
        ]);
    }
}
