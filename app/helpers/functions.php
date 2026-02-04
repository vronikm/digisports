<?php
// ===================================================================
// FUNCIONES DE SSO (Single Sign-On)
// ===================================================================

/**
 * Inicializar sesión SSO para sistemas legacy y nuevos
 * @param array $userData Datos mínimos requeridos para sesión cruzada
 */
function initSSOSession($userData) {
    $_SESSION['user_id'] = $userData['user_id'];
    $_SESSION['tenant_id'] = $userData['tenant_id'];
    $_SESSION['role'] = $userData['role'];
    $_SESSION['username'] = $userData['username'];
    if (isset($userData['email'])) {
        $_SESSION['email'] = $userData['email'];
    }
    if (isset($userData['permissions'])) {
        $_SESSION['permissions'] = $userData['permissions'];
    }
    if (isset($userData['modules'])) {
        $_SESSION['modules'] = $userData['modules'];
    }
    // Agregar otras variables necesarias para compatibilidad SSO
}
// ...existing code...
/**
 * DigiSports - Funciones Helper Globales
 * 
 * @package DigiSports
 * @version 1.0.0
 */

// ===================================================================
// FUNCIONES DE SESIÓN Y AUTENTICACIÓN
// ===================================================================

/**
 * Verificar si el usuario está autenticado
 * 
 * @return bool
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['tenant_id']);
}

/**
 * Verificar si el usuario es administrador
 * 
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['SUPERADMIN', 'ADMIN']);
}

/**
 * Verificar si el usuario es super administrador
 * 
 * @return bool
 */
function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'SUPERADMIN';
}

/**
 * Obtener ID del usuario actual
 * 
 * @return int|null
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obtener ID del tenant actual
 * 
 * @return int|null
 */
function getTenantId() {
    return $_SESSION['tenant_id'] ?? null;
}

/**
 * Obtener información completa del usuario
 * 
 * @return array|null
 */
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'tenant_id' => $_SESSION['tenant_id'],
        'username' => $_SESSION['username'] ?? '',
        'nombres' => $_SESSION['nombres'] ?? '',
        'apellidos' => $_SESSION['apellidos'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? '',
        'avatar' => $_SESSION['avatar'] ?? null
    ];
}

// ===================================================================
// FUNCIONES DE URL Y REDIRECCIÓN
// ===================================================================

/**
 * Generar URL encriptada
 * 
 * @param string $module Módulo
 * @param string $controller Controlador
 * @param string $action Acción
 * @param array $params Parámetros
 * @return string
 */
function url($module, $controller, $action = 'index', $params = []) {
    // Siempre generar URL encriptada para seguridad
    $data = [
        'm' => $module,
        'c' => $controller,
        'a' => $action,
        'p' => $params,
        't' => time()
    ];
    
    $encrypted = Security::encryptUrl(json_encode($data));
    return Config::baseUrl('index.php?r=' . $encrypted);
}

/**
 * Generar URL simple (solo para debug/desarrollo)
 * 
 * @param string $module Módulo
 * @param string $controller Controlador
 * @param string $action Acción
 * @param array $params Parámetros
 * @return string
 */
function urlSimple($module, $controller, $action = 'index', $params = []) {
    $queryParams = [
        'module' => $module,
        'controller' => $controller,
        'action' => $action
    ];
    
    // Agregar parámetros adicionales
    if (!empty($params)) {
        $queryParams = array_merge($queryParams, $params);
    }
    
    return Config::baseUrl('index.php?' . http_build_query($queryParams));
}

/**
 * Redirigir a una URL
 * 
 * @param string $module Módulo
 * @param string $controller Controlador
 * @param string $action Acción
 * @param array $params Parámetros
 */
function redirect($module, $controller, $action = 'index', $params = []) {
    // Asegura que la sesión se guarde antes de redirigir
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    header('Location: ' . url($module, $controller, $action, $params));
    exit;
}

/**
 * Obtener URL base
 * 
 * @param string $path Path adicional
 * @return string
 */
function baseUrl($path = '') {
    return Config::baseUrl($path);
}

/**
 * Obtener URL de assets
 * 
 * @param string $path Path del asset
 * @return string
 */
function asset($path) {
    return baseUrl('public/assets/' . ltrim($path, '/'));
}

// ===================================================================
// FUNCIONES DE VISTA
// ===================================================================

/**
 * Escapar HTML
 * 
 * @param string $string Texto a escapar
 * @return string
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formatear fecha
 * 
 * @param string $date Fecha
 * @param string $format Formato
 * @return string
 */
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formatear fecha y hora
 * 
 * @param string $datetime Fecha y hora
 * @param string $format Formato
 * @return string
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (!$datetime) return '';
    
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    return date($format, $timestamp);
}

/**
 * Formatear moneda
 * 
 * @param float $amount Monto
 * @param string $currency Moneda
 * @return string
 */
function formatMoney($amount, $currency = 'USD') {
    $symbol = $currency === 'USD' ? '$' : $currency . ' ';
    return $symbol . number_format($amount, 2, ',', '.');
}

/**
 * Convertir fecha a formato "hace X tiempo"
 * 
 * @param string $datetime Fecha y hora
 * @return string
 */
function timeAgo($datetime) {
    if (!$datetime) return '';
    
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 0) {
        return 'en el futuro';
    }
    
    $intervals = [
        31536000 => ['año', 'años'],
        2592000 => ['mes', 'meses'],
        604800 => ['semana', 'semanas'],
        86400 => ['día', 'días'],
        3600 => ['hora', 'horas'],
        60 => ['minuto', 'minutos'],
        1 => ['segundo', 'segundos']
    ];
    
    foreach ($intervals as $secs => $labels) {
        $d = floor($diff / $secs);
        if ($d >= 1) {
            $label = $d == 1 ? $labels[0] : $labels[1];
            return "hace $d $label";
        }
    }
    
    return 'ahora mismo';
}

/**
 * Truncar texto
 * 
 * @param string $text Texto
 * @param int $length Longitud
 * @param string $suffix Sufijo
 * @return string
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

// ===================================================================
// FUNCIONES DE VALIDACIÓN
// ===================================================================

/**
 * Validar email
 * 
 * @param string $email Email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar RUC ecuatoriano
 * 
 * @param string $ruc RUC
 * @return bool
 */
function isValidRUC($ruc) {
    return Security::validateRUC($ruc);
}

/**
 * Validar cédula ecuatoriana
 * 
 * @param string $cedula Cédula
 * @return bool
 */
function isValidCedula($cedula) {
    return Security::validateCedula($cedula);
}

// ===================================================================
// FUNCIONES DE MENSAJES FLASH
// ===================================================================

/**
 * Establecer mensaje flash
 * 
 * @param string $type Tipo (success, error, warning, info)
 * @param string $message Mensaje
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obtener y limpiar mensaje flash
 * 
 * @param string|null $type Tipo específico a obtener (success, error, warning, info)
 * @return string|array|null
 */
function getFlashMessage($type = null) {
    if ($type !== null) {
        // Obtener mensaje de un tipo específico
        $key = 'flash_' . $type;
        if (isset($_SESSION[$key])) {
            $message = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $message;
        }
        // Compatibilidad con formato anterior
        if (isset($_SESSION['flash_message']) && $_SESSION['flash_message']['type'] === $type) {
            $message = $_SESSION['flash_message']['message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }
    
    // Obtener cualquier mensaje flash
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Establecer mensaje flash por tipo
 * 
 * @param string $type Tipo (success, error, warning, info)
 * @param string $message Mensaje
 */
function setFlash($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Verificar si hay mensaje flash
 * 
 * @return bool
 */
function hasFlashMessage() {
    return isset($_SESSION['flash_message']);
}

// ===================================================================
// FUNCIONES DE PERMISOS
// ===================================================================

/**
 * Verificar permiso
 * 
 * @param string $permission Permiso (ej: 'usuarios.crear')
 * @return bool
 */
function hasPermission($permission) {
    if (isSuperAdmin()) {
        return true;
    }
    
    $permissions = $_SESSION['permissions'] ?? [];
    
    // Verificar permiso exacto
    if (in_array($permission, $permissions)) {
        return true;
    }
    
    // Verificar wildcard (ej: 'usuarios.*')
    $parts = explode('.', $permission);
    $wildcard = $parts[0] . '.*';
    
    if (in_array($wildcard, $permissions)) {
        return true;
    }
    
    // Verificar permiso global
    if (in_array('*', $permissions)) {
        return true;
    }
    
    return false;
}

/**
 * Verificar acceso a módulo
 * 
 * @param string $module Código del módulo
 * @return bool
 */
function hasModuleAccess($module) {
    if (isSuperAdmin()) {
        return true;
    }
    
    $modules = $_SESSION['modules'] ?? [];
    return in_array($module, $modules);
}

// ===================================================================
// FUNCIONES DE ARCHIVOS
// ===================================================================

/**
 * Subir archivo
 * 
 * @param array $file Array $_FILES
 * @param string $directory Directorio de destino
 * @param array $allowedTypes Tipos permitidos
 * @return string|false Path del archivo o false
 */
function uploadFile($file, $directory = 'general', $allowedTypes = null) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    
    // Validar tamaño
    if ($file['size'] > Config::FILES['max_size']) {
        return false;
    }
    
    // Validar tipo
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($allowedTypes === null) {
        $allowedTypes = array_merge(
            Config::FILES['allowed_types']['images'],
            Config::FILES['allowed_types']['documents']
        );
    }
    
    if (!in_array($extension, $allowedTypes)) {
        return false;
    }
    
    // Generar nombre único
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = Config::FILES['upload_path'] . $directory . '/';
    
    // Crear directorio si no existe
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    $destination = $uploadPath . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $directory . '/' . $filename;
    }
    
    return false;
}

/**
 * Eliminar archivo
 * 
 * @param string $path Path relativo del archivo
 * @return bool
 */
function deleteFile($path) {
    $fullPath = Config::FILES['upload_path'] . $path;
    
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    
    return false;
}

/**
 * Obtener URL de archivo
 * 
 * @param string $path Path del archivo
 * @return string
 */
function fileUrl($path) {
    if (!$path) {
        return asset('img/no-image.png');
    }
    
    return baseUrl('storage/uploads/' . $path);
}

// ===================================================================
// FUNCIONES DE DEBUG
// ===================================================================

/**
 * Dump and die - Útil para debug
 * 
 * @param mixed $var Variable a mostrar
 */
function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}

/**
 * Dump - Mostrar sin detener ejecución
 * 
 * @param mixed $var Variable a mostrar
 */
function dump($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

// ===================================================================
// FUNCIONES DE ARRAY
// ===================================================================

/**
 * Obtener valor de array con valor por defecto
 * 
 * @param array $array Array
 * @param string $key Clave
 * @param mixed $default Valor por defecto
 * @return mixed
 */
function arrayGet($array, $key, $default = null) {
    return $array[$key] ?? $default;
}

/**
 * Verificar si un valor existe en array
 * 
 * @param mixed $needle Valor a buscar
 * @param array $haystack Array donde buscar
 * @return bool
 */
function inArray($needle, $haystack) {
    return in_array($needle, $haystack, true);
}

// ===================================================================
// FUNCIONES DE STRING
// ===================================================================

/**
 * Generar slug
 * 
 * @param string $string Texto
 * @return string
 */
function slug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

/**
 * Generar código aleatorio
 * 
 * @param int $length Longitud
 * @return string
 */
function generateCode($length = 8) {
    return strtoupper(substr(bin2hex(random_bytes($length)), 0, $length));
}

// ===================================================================
// FUNCIONES DE NOTIFICACIONES
// ===================================================================

/**
 * Enviar notificación a usuario
 * 
 * @param int $userId ID del usuario
 * @param string $tipo Tipo de notificación
 * @param string $titulo Título
 * @param string $mensaje Mensaje
 * @param string $url URL de acción (opcional)
 * @return bool
 */
function sendNotification($userId, $tipo, $titulo, $mensaje, $url = null) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO notificaciones 
            (tenant_id, usuario_id, tipo, titulo, mensaje, url_accion)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            getTenantId(),
            $userId,
            $tipo,
            $titulo,
            $mensaje,
            $url
        ]);
        
    } catch (Exception $e) {
        error_log("Error al enviar notificación: " . $e->getMessage());
        return false;
    }
}

// ===================================================================
// FUNCIONES DE LOG
// ===================================================================

/**
 * Registrar en log personalizado
 * 
 * @param string $message Mensaje
 * @param string $type Tipo de log
 */
function logMessage($message, $type = 'info') {
    $logFile = Config::LOGS['path'] . $type . '_' . date('Y-m-d') . '.log';
    
    $logEntry = sprintf(
        "[%s] %s\n",
        date('Y-m-d H:i:s'),
        $message
    );
    
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}