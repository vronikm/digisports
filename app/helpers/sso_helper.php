<?php
/**
 * DigiSports - Helper de SSO (Single Sign-On)
 * Permite inicializar sesión cruzada y redirigir a sistemas externos
 */

require_once BASE_PATH . '/app/helpers/functions.php';

function ssoRedirect($userData, $urlExterno) {
    // Inicializar variables de sesión requeridas
    initSSOSession($userData);
    // Redirigir automáticamente al sistema externo
    header('Location: ' . $urlExterno);
    exit;
}
