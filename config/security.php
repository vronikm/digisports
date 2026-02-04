<?php
/**
 * DigiSports - Configuración de Seguridad
 * Implementa encriptación AES-256-GCM, protección CSRF, XSS, SQL Injection
 * Cumple con Ley de Protección de Datos Ecuador
 * 
 * @package DigiSports
 * @author Senior Developer
 * @version 1.0.0
 */

class Security {
    
    // Clave maestra para encriptación (cambiar en producción y almacenar en .env)
    private static $masterKey = 'DigiSports2024SecureKeyMasterEncryption';
    
    // Tiempo de expiración de sesión (30 minutos)
    const SESSION_TIMEOUT = 1800;
    
    // Tiempo de expiración de tokens de URL (8 horas para navegación normal)
    const TOKEN_TIMEOUT = 28800;
    
    /**
     * Inicializar configuraciones de seguridad
     */
    public static function init() {
        // Configuración de sesión segura
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerar ID de sesión periódicamente
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = time();
        } else if (time() - $_SESSION['CREATED'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['CREATED'] = time();
        }
        
        // Validar timeout de sesión
        if (isset($_SESSION['LAST_ACTIVITY']) && 
            (time() - $_SESSION['LAST_ACTIVITY'] > self::SESSION_TIMEOUT)) {
            self::destroySession();
            // Usar la URL base correcta del proyecto
            $baseUrl = '/digisports/public/';
            header('Location: ' . $baseUrl . '?module=core&controller=auth&action=login&timeout=1');
            exit;
        }
        $_SESSION['LAST_ACTIVITY'] = time();
        
        // Headers de seguridad
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        // CSP permite CDNs de AdminLTE, Bootstrap, Font Awesome y Google Fonts
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; img-src 'self' data:;");
    }
    
    /**
     * Encriptar URL con AES-256-GCM
     * @param string $data Datos a encriptar
     * @return string URL encriptada en base64
     */
    public static function encryptUrl($data) {
        $key = hash('sha256', self::$masterKey, true);
        $iv = random_bytes(16);
        $tag = '';
        
        $encrypted = openssl_encrypt(
            $data,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        // Combinar IV + Encrypted + Tag
        $result = base64_encode($iv . $encrypted . $tag);
        return strtr($result, '+/=', '-_,');
    }
    
    /**
     * Desencriptar URL
     * @param string $encrypted Datos encriptados
     * @return string|false Datos desencriptados o false si falla
     */
    public static function decryptUrl($encrypted) {
        $encrypted = strtr($encrypted, '-_,', '+/=');
        $data = base64_decode($encrypted);
        
        if (strlen($data) < 33) return false;
        
        $iv = substr($data, 0, 16);
        $tag = substr($data, -16);
        $ciphertext = substr($data, 16, -16);
        
        $key = hash('sha256', self::$masterKey, true);
        
        $decrypted = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        return $decrypted;
    }
    
    /**
     * Generar URL segura con parámetros encriptados
     * @param string $controller Controlador
     * @param string $action Acción
     * @param array $params Parámetros adicionales
     * @return string URL encriptada
     */
    public static function generateSecureUrl($controller, $action, $params = []) {
        $data = [
            'c' => $controller,
            'a' => $action,
            'p' => $params,
            't' => time()
        ];
        
        $encrypted = self::encryptUrl(json_encode($data));
        return '/index.php?r=' . $encrypted;
    }
    
    /**
     * Decodificar URL segura
     * @param string $encrypted URL encriptada
     * @return array|false Array con controller, action, params o false
     */
    public static function decodeSecureUrl($encrypted) {
        $decrypted = self::decryptUrl($encrypted);
        if (!$decrypted) return false;
        
        $data = json_decode($decrypted, true);
        if (!$data) return false;
        
        // Validar que no sea muy antigua (10 minutos)
        if (time() - $data['t'] > self::TOKEN_TIMEOUT) {
            return false;
        }
        
        return $data;
    }
    
    /**
     * Generar token CSRF
     * @return string Token CSRF
     */
    public static function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) ||
            time() - $_SESSION['csrf_token_time'] > 3600) {
            
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validar token CSRF
     * @param string $token Token a validar
     * @return bool True si es válido
     */
    public static function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Sanitizar entrada contra XSS
     * @param mixed $data Datos a sanitizar
     * @return mixed Datos sanitizados
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        if ($data === null) {
            return '';
        }
        
        $data = trim((string)$data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    /**
     * Validar y sanitizar email
     * @param string $email Email a validar
     * @return string|false Email sanitizado o false
     */
    public static function sanitizeEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
    }
    
    /**
     * Hash de contraseña seguro (Argon2id con fallback a BCrypt)
     * @param string $password Contraseña en texto plano
     * @return string Hash de contraseña
     */
    public static function hashPassword($password) {
        // Usar Argon2id si está disponible (PHP 7.3+)
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3
            ]);
        }
        
        // Fallback a BCrypt para servidores sin Argon2
        return password_hash($password, PASSWORD_BCRYPT, [
            'cost' => 12
        ]);
    }
    
    /**
     * Verificar contraseña
     * @param string $password Contraseña en texto plano
     * @param string $hash Hash almacenado
     * @return bool True si coincide
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Encriptar datos sensibles para BD (tarjetas, etc.)
     * @param string $data Datos a encriptar
     * @return string Datos encriptados
     */
    public static function encryptSensitiveData($data) {
        $key = hash('sha256', self::$masterKey . 'SENSITIVE', true);
        $iv = random_bytes(16);
        
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Desencriptar datos sensibles de BD
     * @param string $encrypted Datos encriptados
     * @return string|false Datos desencriptados
     */
    public static function decryptSensitiveData($encrypted) {
        $data = base64_decode($encrypted);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        $key = hash('sha256', self::$masterKey . 'SENSITIVE', true);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    }
    
    /**
     * Generar código 2FA
     * @return string Código de 6 dígitos
     */
    public static function generate2FACode() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Validar fuerza de contraseña
     * Debe tener: mínimo 8 caracteres, mayúscula, minúscula, número, especial
     * @param string $password Contraseña a validar
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Debe contener al menos una letra mayúscula';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Debe contener al menos una letra minúscula';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Debe contener al menos un número';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Debe contener al menos un carácter especial';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Registrar intento de acceso sospechoso
     * @param string $type Tipo de intento (login, csrf, injection, etc.)
     * @param string $details Detalles del intento
     */
    public static function logSecurityEvent($type, $details = '') {
        $logFile = __DIR__ . '/../storage/logs/security_' . date('Y-m-d') . '.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = sprintf(
            "[%s] %s - IP: %s - User-Agent: %s - Details: %s\n",
            date('Y-m-d H:i:s'),
            $type,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            $details
        );
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        // Si hay muchos intentos de la misma IP, considerar bloqueo
        self::checkBruteForce();
    }
    
    /**
     * Verificar intentos de fuerza bruta
     */
    private static function checkBruteForce() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $cacheFile = __DIR__ . '/../storage/cache/failed_attempts.json';
        
        if (!file_exists($cacheFile)) {
            file_put_contents($cacheFile, json_encode([]));
        }
        
        $attempts = json_decode(file_get_contents($cacheFile), true);
        if (!is_array($attempts)) {
            $attempts = [];
        }
        
        $now = time();
        
        // Limpiar intentos antiguos (más de 1 hora)
        $attempts = array_filter($attempts, function($ipAttempts) use ($now) {
            if (!is_array($ipAttempts)) {
                return false;
            }
            // Si el IP tiene intentos recientes, mantenerlo
            foreach ($ipAttempts as $attemptTime) {
                if (($now - (int)$attemptTime) < 3600) {
                    return true;
                }
            }
            return false;
        });
        
        if (!isset($attempts[$ip])) {
            $attempts[$ip] = [];
        }
        
        $attempts[$ip][] = $now;
        
        // Si más de 5 intentos en 15 minutos, bloquear
        $recentAttempts = array_filter($attempts[$ip], function($time) use ($now) {
            return is_numeric($time) && ($now - (int)$time) < 900; // 15 minutos
        });
        
        if (count($recentAttempts) > 5) {
            self::blockIP($ip);
        }
        
        file_put_contents($cacheFile, json_encode($attempts));
    }
    
    /**
     * Bloquear IP temporalmente
     * @param string $ip IP a bloquear
     */
    private static function blockIP($ip) {
        $blockFile = __DIR__ . '/../storage/cache/blocked_ips.json';
        
        if (!file_exists($blockFile)) {
            file_put_contents($blockFile, json_encode([]));
        }
        
        $blocked = json_decode(file_get_contents($blockFile), true);
        $blocked[$ip] = time() + 3600; // Bloquear por 1 hora
        
        file_put_contents($blockFile, json_encode($blocked));
        
        self::logSecurityEvent('IP_BLOCKED', "IP {$ip} bloqueada por intentos de fuerza bruta");
    }
    
    /**
     * Verificar si IP está bloqueada
     * @param string $ip IP a verificar
     * @return bool True si está bloqueada
     */
    public static function isIPBlocked($ip = null) {
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'Unknown');
        $blockFile = __DIR__ . '/../storage/cache/blocked_ips.json';
        
        if (!file_exists($blockFile)) {
            return false;
        }
        
        $blocked = json_decode(file_get_contents($blockFile), true);
        
        if (isset($blocked[$ip]) && $blocked[$ip] > time()) {
            return true;
        }
        
        // Limpiar bloqueos expirados
        if (isset($blocked[$ip]) && $blocked[$ip] <= time()) {
            unset($blocked[$ip]);
            file_put_contents($blockFile, json_encode($blocked));
        }
        
        return false;
    }
    
    /**
     * Destruir sesión completamente
     */
    public static function destroySession() {
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    /**
     * Validar RUC ecuatoriano
     * @param string $ruc RUC a validar
     * @return bool True si es válido
     */
    public static function validateRUC($ruc) {
        if (!preg_match('/^[0-9]{13}$/', $ruc)) {
            return false;
        }
        
        // Algoritmo de validación de RUC Ecuador
        $tipo = substr($ruc, 2, 1);
        
        if ($tipo < 6) { // Persona natural o jurídica
            $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
            $sum = 0;
            
            for ($i = 0; $i < 9; $i++) {
                $valor = intval($ruc[$i]) * $coeficientes[$i];
                $sum += ($valor > 9) ? ($valor - 9) : $valor;
            }
            
            $digitoVerificador = (($sum % 10) == 0) ? 0 : (10 - ($sum % 10));
            return $digitoVerificador == intval($ruc[9]);
        }
        
        return true; // Para otros tipos, validación básica
    }
    
    /**
     * Validar cédula ecuatoriana
     * @param string $cedula Cédula a validar
     * @return bool True si es válida
     */
    public static function validateCedula($cedula) {
        if (!preg_match('/^[0-9]{10}$/', $cedula)) {
            return false;
        }
        
        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $sum = 0;
        
        for ($i = 0; $i < 9; $i++) {
            $valor = intval($cedula[$i]) * $coeficientes[$i];
            $sum += ($valor > 9) ? ($valor - 9) : $valor;
        }
        
        $digitoVerificador = (($sum % 10) == 0) ? 0 : (10 - ($sum % 10));
        return $digitoVerificador == intval($cedula[9]);
    }
}

// Inicializar seguridad al cargar el archivo
Security::init();