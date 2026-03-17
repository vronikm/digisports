<?php
/**
 * Configuración SMTP — lee de .env, con fallback a valores de desarrollo.
 * Variables esperadas en .env:
 *   MAIL_HOST, MAIL_PORT, MAIL_USER, MAIL_PASS, MAIL_FROM, MAIL_FROM_NAME
 */
return [
    'host'      => env('MAIL_HOST',      'smtp.gmail.com'),
    'port'      => (int) env('MAIL_PORT', 587),
    'username'  => env('MAIL_USER',      ''),
    'password'  => env('MAIL_PASS',      ''),
    'from'      => env('MAIL_FROM',      env('MAIL_USER', '')),
    'from_name' => env('MAIL_FROM_NAME', 'DigiSports'),
    'secure'    => PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
];
