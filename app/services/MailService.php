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
