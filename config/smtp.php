<?php
// Configuración SMTP para PHPMailer (Gmail)
return [
    'host' => 'smtp.gmail.com',
    'username' => 'fbpinzon@gmail.com', // Cambia por tu correo Gmail
    'password' => 'porr myhw nkrp pqrt',     // Usa una contraseña de aplicación de Gmail
    'from' => 'fbpinzon@gmail.com',
    'from_name' => 'DigiSports',
    'port' => 587,
    'secure' => PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
];
