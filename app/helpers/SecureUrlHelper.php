<?php
/**
 * Helper para generar enlaces seguros con POST (para URLs largas)
 * Evita problemas de truncamiento de URLs que superan el límite de GET
 */

if (!function_exists('secureLink')) {
    /**
     * Generar enlace seguro con POST (para URLs largas)
     * Crea un formulario oculto que se envía por POST
     * 
     * @param string $module Módulo
     * @param string $controller Controlador
     * @param string $action Acción
     * @param array $params Parámetros adicionales
     * @param string $label Texto del enlace
     * @param array $attrs Atributos HTML del enlace
     * @return string HTML del formulario oculto
     */
    function secureLink($module, $controller, $action, $params = [], $label = 'Ir', $attrs = []) {
        // Generar token seguro
        $token = Security::generateClientToken($module, $controller, $action, $params);
        
        // Construcción de atributos
        $classAttr = $attrs['class'] ?? 'btn btn-primary';
        $idAttr = $attrs['id'] ?? '';
        $dataAttr = '';
        
        // Generar ID único para el formulario
        $formId = 'form_' . bin2hex(random_bytes(4));
        
        $html = '<form id="' . htmlspecialchars($formId) . '" method="POST" action="/digisports/public/index.php" style="display:inline;">';
        $html .= '<input type="hidden" name="token" value="' . htmlspecialchars($token) . '">';
        $html .= '</form>';
        $html .= '<button type="submit" form="' . htmlspecialchars($formId) . '" class="' . htmlspecialchars($classAttr) . '"';
        
        if (!empty($idAttr)) {
            $html .= ' id="' . htmlspecialchars($idAttr) . '"';
        }
        
        $html .= '>' . htmlspecialchars($label) . '</button>';
        
        return $html;
    }
}

if (!function_exists('secureForm')) {
    /**
     * Generar formulario con POST seguro
     * Útil para navegación a URL muy larga
     * 
     * @param string $module Módulo
     * @param string $controller Controlador
     * @param string $action Acción
     * @param array $params Parámetros adicionales
     * @param string $method POST
     * @param string $id ID del formulario
     * @return string HTML del formulario
     */
    function secureForm($module, $controller, $action, $params = [], $method = 'POST', $id = '') {
        $token = Security::generateClientToken($module, $controller, $action, $params);
        
        $formId = $id ?: 'secure_form_' . bin2hex(random_bytes(4));
        
        $html = '<form id="' . htmlspecialchars($formId) . '" method="' . htmlspecialchars($method) . '" action="/digisports/public/index.php">';
        $html .= '<input type="hidden" name="token" value="' . htmlspecialchars($token) . '">';
        $html .= '</form>';
        
        return $html;
    }
}

if (!function_exists('redirectSecure')) {
    /**
     * Redirigir de forma segura usando POST si es necesario
     * Si la URL no es demasiado larga, usa GET
     * Si es larga (> 2000 chars), genera un token y redirige con POST
     * 
     * @param string $module Módulo
     * @param string $controller Controlador
     * @param string $action Acción
     * @param array $params Parámetros adicionales
     * @return void
     */
    function redirectSecure($module, $controller, $action, $params = []) {
        // Intentar generar URL normal
        $url = Security::encodeSecureUrl($module, $controller, $action, $params);
        
        // Si URL es demasiado larga, usar token
        if (strlen($url) > 2000) {
            // Generar token
            $token = Security::generateClientToken($module, $controller, $action, $params);
            
            // Mostrar formulario oculto con JavaScript
            echo '<!DOCTYPE html>
<html>
<body onload="document.frm.submit()">
<form name="frm" method="POST" action="/digisports/public/index.php">
    <input type="hidden" name="token" value="' . htmlspecialchars($token) . '">
</form>
</body>
</html>';
            exit;
        } else {
            // URL normal, redirigir con GET
            header('Location: ' . $url);
            exit;
        }
    }
}
?>
