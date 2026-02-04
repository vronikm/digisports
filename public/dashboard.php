<?php
// Redirigir al dashboard
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /digiSports/public/auth/login');
    exit;
}

// Redirigir al dashboard del core
header('Location: /digiSports/public/?c=core&a=dashboard');
exit;
?>
