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

    // Clave maestra cargada desde .env / Config::getAppKey()
    private static $masterKey = null;

    // Nonce CSP único por request (generado en init())
    private static $cspNonce = null;

    /**
     * Obtener la clave maestra (lazy init desde .env)
     */
    private static function getMasterKey(): string {
        if (self::$masterKey === null) {
            self::$masterKey = class_exists('Config')
                ? Config::getAppKey()
                : (function_exists('env') ? env('APP_KEY', 'DigiSports2024SecureKeyMasterEncryption') : 'DigiSports2024SecureKeyMasterEncryption');
        }
        return self::$masterKey;
    }
    
    // Tiempo de expiración de sesión (30 minutos)
    const SESSION_TIMEOUT = 1800;
    
    // Tiempo de expiración de tokens de URL (8 horas para navegación normal)
    const TOKEN_TIMEOUT = 28800;
    
    /**
     * Inicializar configuraciones de seguridad
     */
    public static function init() {
        // Configuración de sesión segura
        // cookie_secure solo en HTTPS para no romper sesiones en localhost HTTP
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                   || (($_SERVER['SERVER_PORT'] ?? 80) == 443);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', $isHttps ? '1' : '0');
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

        // Generar nonce CSP único para este request
        self::$cspNonce = base64_encode(random_bytes(16));

        // Headers de seguridad
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        // CSP con nonce — unsafe-inline eliminado de script-src (riesgo XSS real).
        // Se autorizan todos los CDNs efectivamente usados en los layouts.
        $nonce = self::$cspNonce;
        header(
            "Content-Security-Policy: " .
            "default-src 'self'; " .
            // Scripts: CDNs usados en main.php, auth.php y module.php
            "script-src 'self' 'nonce-{$nonce}' " .
                "https://cdn.jsdelivr.net " .
                "https://cdnjs.cloudflare.com " .
                "https://cdn.datatables.net " .
                "https://code.jquery.com; " .
            // Estilos: unsafe-inline se mantiene para clases inline de AdminLTE/Bootstrap
            "style-src 'self' 'unsafe-inline' " .
                "https://cdn.jsdelivr.net " .
                "https://cdnjs.cloudflare.com " .
                "https://fonts.googleapis.com " .
                "https://cdn.datatables.net; " .
            // Fuentes
            "font-src 'self' " .
                "https://fonts.gstatic.com " .
                "https://cdnjs.cloudflare.com " .
                "https://cdn.jsdelivr.net; " .
            // Imágenes
            "img-src 'self' data: https://ui-avatars.com; " .
            "connect-src 'self'; " .
            "frame-ancestors 'none';"
        );
    }

    /**
     * Obtener el nonce CSP generado para este request.
     * Usar en vistas: <script nonce="<?= Security::getCspNonce() ?>">
     */
    public static function getCspNonce(): string
    {
        if (self::$cspNonce === null) {
            // Fallback si se llama antes de init() (ej. en tests)
            self::$cspNonce = base64_encode(random_bytes(16));
        }
        return self::$cspNonce;
    }
    
    /**
     * Encriptar URL con AES-256-GCM
     * @param string $data Datos a encriptar
     * @return string URL encriptada en base64
     */
    public static function encryptUrl($data) {
        $key = hash('sha256', self::getMasterKey(), true);
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
        // ⚠️ IMPORTANTE: NO usar coma (,) como carácter de remplazo
        // Apache puede interpretarla como truncamiento de URL
        // Usar tilde (~) en su lugar para evitar problemas
        return strtr($result, '+/=', '-_~');
    }
    
    /**
     * Desencriptar URL
     * @param string $encrypted Datos encriptados
     * @return string|false Datos desencriptados o false si falla
     */
    public static function decryptUrl($encrypted) {
        // ⚠️ IMPORTANTE: COMPATIBILIDAD CON AMBOS SISTEMAS
        // Sistema viejo usa comas: -_ + ,
        // Sistema nuevo usa tildes: -_ + ~
        // Detectar cuál usar según el contenido
        
        if (strpos($encrypted, ',') !== false || strpos($encrypted, '~') !== false) {
            // Sistema viejo (con comas) O nuevo (con tildes) - ambos necesitan strtr
            if (strpos($encrypted, '~') !== false) {
                // Sistema nuevo: ~ → =
                $encrypted = strtr($encrypted, '-_~', '+/=');
            } else {
                // Sistema viejo: , → =
                $encrypted = strtr($encrypted, '-_,', '+/=');
            }
        } else {
            // Fallback: intentar ambas conversiones
            // Primero intentar con tilde (nuevo sistema)
            $test = strtr($encrypted, '-_~', '+/=');
            $testData = base64_decode($test);
            
            if ($testData && strlen($testData) >= 33) {
                $encrypted = $test;
            } else {
                // Fallback a coma (viejo sistema)
                $encrypted = strtr($encrypted, '-_,', '+/=');
            }
        }
        
        $data = base64_decode($encrypted);
        
        if (strlen($data) < 33) return false;
        
        $iv = substr($data, 0, 16);
        $tag = substr($data, -16);
        $ciphertext = substr($data, 16, -16);
        
        $key = hash('sha256', self::getMasterKey(), true);
        
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
     * Generar URL segura con parámetros encriptados (con módulo)
     * @param string $module Módulo
     * @param string $controller Controlador
     * @param string $action Acción
     * @param array $params Parámetros adicionales
     * @return string URL encriptada
     */
    public static function encodeSecureUrl($module, $controller, $action, $params = []) {
        $data = [
            'm' => $module,
            'c' => $controller,
            'a' => $action,
            'p' => $params,
            't' => time()
        ];
        
        $encrypted = self::encryptUrl(json_encode($data));
        return '/digisports/public/index.php?r=' . $encrypted;
    }
    
    /**
     * Generar URL segura con parámetros encriptados (sin módulo - legacy)
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
        return '/digisports/public/index.php?r=' . $encrypted;
    }
    
    /**
     * Generar token seguro para POST (alternativa para URLs muy largas)
     * Almacena datos encriptados en sesión y devuelve token corto
     * @param string $module Módulo
     * @param string $controller Controlador
     * @param string $action Acción
     * @param array $params Parámetros adicionales
     * @return string Token seguro corto (< 50 caracteres)
     */
    public static function generateClientToken($module, $controller, $action, $params = []) {
        $data = [
            'm' => $module,
            'c' => $controller,
            'a' => $action,
            'p' => $params,
            't' => time()
        ];
        
        $encrypted = self::encryptUrl(json_encode($data));
        $token = base64_encode(random_bytes(12));
        
        // Almacenar en sesión (válido por 1 hora)
        if (!isset($_SESSION['nav_tokens'])) {
            $_SESSION['nav_tokens'] = [];
        }
        $_SESSION['nav_tokens'][$token] = [
            'encrypted' => $encrypted,
            'expires' => time() + 3600
        ];
        
        return $token;
    }
    
    /**
     * Recuperar datos de token cliente
     * @param string $token Token desde POST
     * @return string|false Datos encriptados o false si expiró
     */
    public static function getClientTokenData($token) {
        if (!isset($_SESSION['nav_tokens'][$token])) {
            return false;
        }
        
        $entry = $_SESSION['nav_tokens'][$token];
        
        // Verificar expiración
        if (time() > $entry['expires']) {
            unset($_SESSION['nav_tokens'][$token]);
            return false;
        }
        
        // Eliminar token después de usarlo
        unset($_SESSION['nav_tokens'][$token]);
        
        return $entry['encrypted'];
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
     * Detectar si una URL fue truncada (termina con tilde, signo de truncamiento)
     * @param string $encrypted URL encriptada  
     * @return bool true si parece estar truncada
     */
    public static function isUrlTruncated($encrypted) {
        if (empty($encrypted)) return false;
        
        // Las URLs encriptadas en BASE64 after conversion:
        // - Caracteres válidos: a-z, A-Z, 0-9, -, _, ~
        // Una TILDE (~) al final es POSIBLE SEÑAL de truncamiento
        // Pero es raro, así que no usamos este check más
        // En su lugar, verificar por excepción de desencriptación
        
        return false; // Deshabilitado - no es confiable
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
        $key = hash('sha256', self::getMasterKey() . 'SENSITIVE', true);
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
        
        $key = hash('sha256', self::getMasterKey() . 'SENSITIVE', true);
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
     * Verificar intentos de fuerza bruta — BD primaria, JSON como fallback
     */
    private static function checkBruteForce() {
        $ip          = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $maxAttempts = Config::SECURITY['max_login_attempts'] ?? 5;
        $window      = Config::SECURITY['brute_force_window']  ?? 900;

        // ── BD ────────────────────────────────────────────────────────
        try {
            $db     = \Database::getInstance()->getConnection();
            $cutoff = date('Y-m-d H:i:s', time() - $window);
            $stmt   = $db->prepare("
                SELECT COUNT(*) AS cnt
                FROM seguridad_log_accesos
                WHERE acc_ip = ?
                  AND acc_exito = 'N'
                  AND acc_tipo  = 'LOGIN_FAILED'
                  AND acc_fecha_hora > ?
            ");
            $stmt->execute([$ip, $cutoff]);
            $cnt = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);
            if ($cnt >= $maxAttempts) {
                self::blockIP($ip);
            }
            return;
        } catch (\Throwable $e) { /* BD no disponible — usar JSON */ }

        // ── Fallback JSON ─────────────────────────────────────────────
        $cacheFile   = __DIR__ . '/../storage/cache/failed_attempts.json';
        $cleanWindow = max($window, Config::SECURITY['ip_block_duration'] ?? 3600);

        $fp = fopen($cacheFile, 'c+');
        if (!$fp) return;
        flock($fp, LOCK_EX);

        $attempts = json_decode(stream_get_contents($fp) ?: '{}', true);
        if (!is_array($attempts)) $attempts = [];

        $now = time();
        foreach ($attempts as $ipKey => $times) {
            if (!is_array($times)) { unset($attempts[$ipKey]); continue; }
            $times = array_filter($times, fn($t) => is_numeric($t) && ($now - (int)$t) < $cleanWindow);
            if (empty($times)) {
                unset($attempts[$ipKey]);
            } else {
                $attempts[$ipKey] = array_values($times);
            }
        }
        $attempts[$ip][] = $now;

        $recentCount = count(array_filter($attempts[$ip], fn($t) => ($now - (int)$t) < $window));
        if ($recentCount >= $maxAttempts) {
            self::blockIP($ip);
        }

        ftruncate($fp, 0); rewind($fp);
        fwrite($fp, json_encode($attempts));
        flock($fp, LOCK_UN); fclose($fp);
    }

    /**
     * Contar intentos recientes de login fallido para una IP (helper interno)
     */
    private static function countRecentAttempts(string $ip): int {
        $window = Config::SECURITY['brute_force_window'] ?? 900;
        try {
            $db     = \Database::getInstance()->getConnection();
            $cutoff = date('Y-m-d H:i:s', time() - $window);
            $stmt   = $db->prepare("
                SELECT COUNT(*) AS cnt FROM seguridad_log_accesos
                WHERE acc_ip = ? AND acc_exito = 'N' AND acc_tipo = 'LOGIN_FAILED'
                  AND acc_fecha_hora > ?
            ");
            $stmt->execute([$ip, $cutoff]);
            return (int)($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Bloquear IP — BD primaria, JSON como fallback
     * @param string $ip IP a bloquear
     */
    private static function blockIP(string $ip) {
        $blockDuration = Config::SECURITY['ip_block_duration'] ?? 3600;
        $expiry        = date('Y-m-d H:i:s', time() + $blockDuration);
        $intentos      = self::countRecentAttempts($ip);

        // ── BD ────────────────────────────────────────────────────────
        try {
            $db = \Database::getInstance()->getConnection();
            $db->prepare("
                INSERT INTO seguridad_ips_bloqueadas
                    (ib_ip, ib_bloqueado_hasta, ib_intentos)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    ib_bloqueado_hasta = VALUES(ib_bloqueado_hasta),
                    ib_intentos        = VALUES(ib_intentos),
                    ib_desbloqueado    = 0
            ")->execute([$ip, $expiry, $intentos]);
            return;
        } catch (\Throwable $e) { /* BD no disponible — usar JSON */ }

        // ── Fallback JSON ─────────────────────────────────────────────
        $blockFile = __DIR__ . '/../storage/cache/blocked_ips.json';
        $fp = fopen($blockFile, 'c+');
        if (!$fp) return;
        flock($fp, LOCK_EX);

        $blocked = json_decode(stream_get_contents($fp) ?: '{}', true);
        if (!is_array($blocked)) $blocked = [];
        $blocked[$ip] = time() + $blockDuration;

        ftruncate($fp, 0); rewind($fp);
        fwrite($fp, json_encode($blocked));
        flock($fp, LOCK_UN); fclose($fp);
    }

    /**
     * Verificar si IP está bloqueada — BD primaria, JSON como fallback
     * @param string|null $ip IP a verificar (null = IP del request)
     * @return bool True si está bloqueada
     */
    public static function isIPBlocked($ip = null): bool {
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'Unknown');

        // ── BD ────────────────────────────────────────────────────────
        try {
            $db   = \Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT ib_id FROM seguridad_ips_bloqueadas
                WHERE ib_ip = ?
                  AND ib_bloqueado_hasta > NOW()
                  AND ib_desbloqueado = 0
                LIMIT 1
            ");
            $stmt->execute([$ip]);
            return $stmt->fetch() !== false;
        } catch (\Throwable $e) { /* BD no disponible — usar JSON */ }

        // ── Fallback JSON ─────────────────────────────────────────────
        $blockFile = __DIR__ . '/../storage/cache/blocked_ips.json';
        if (!file_exists($blockFile)) return false;

        $fp = fopen($blockFile, 'r+');
        if (!$fp) return false;
        flock($fp, LOCK_SH);
        $blocked = json_decode(stream_get_contents($fp), true);
        flock($fp, LOCK_UN); fclose($fp);

        if (!is_array($blocked) || !isset($blocked[$ip])) return false;
        if ($blocked[$ip] > time()) return true;

        // Bloqueo expirado — limpiar con lock exclusivo
        $fp = fopen($blockFile, 'r+');
        if ($fp) {
            flock($fp, LOCK_EX);
            $blocked = json_decode(stream_get_contents($fp), true) ?: [];
            unset($blocked[$ip]);
            ftruncate($fp, 0); rewind($fp);
            fwrite($fp, json_encode($blocked));
            flock($fp, LOCK_UN); fclose($fp);
        }
        return false;
    }

    // ─── Rate Limiting ────────────────────────────────────────────────

    /**
     * Verificar rate limit para una acción dada.
     *
     * Uso en controladores:
     *   if (!Security::checkRateLimit('api_consulta', 30)) {
     *       $this->error('Demasiadas solicitudes', 429);
     *   }
     *
     * @param string $action    Identificador de la acción (ej: 'api_consulta', 'login')
     * @param int    $maxPerMin Máximo de requests permitidos por minuto por IP
     * @return bool  true = dentro del límite, false = límite superado
     */
    public static function checkRateLimit(string $action, int $maxPerMin = 60): bool
    {
        $ip        = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $cacheFile = __DIR__ . '/../storage/cache/rate_limit.json';
        $now       = time();
        $window    = 60; // ventana deslizante de 1 minuto
        $key       = md5($ip . '|' . $action);

        // ── BD: usar seguridad_log_accesos si está disponible ─────────
        try {
            $db     = \Database::getInstance()->getConnection();
            $cutoff = date('Y-m-d H:i:s', $now - $window);
            $stmt   = $db->prepare("
                SELECT COUNT(*) AS cnt
                FROM seguridad_log_accesos
                WHERE acc_ip = ? AND acc_tipo = ? AND acc_fecha_hora > ?
            ");
            $stmt->execute([$ip, strtoupper($action), $cutoff]);
            $cnt = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);
            return $cnt < $maxPerMin;
        } catch (\Throwable $e) { /* fallback a JSON cache */ }

        // ── Fallback JSON ─────────────────────────────────────────────
        $fp = fopen($cacheFile, 'c+');
        if (!$fp) return true; // fail open: no bloqueamos si el cache falla

        flock($fp, LOCK_EX);
        $data = json_decode(stream_get_contents($fp) ?: '{}', true);
        if (!is_array($data)) $data = [];

        // Purgar entradas expiradas del key actual
        $timestamps = $data[$key] ?? [];
        $timestamps = array_values(array_filter($timestamps, fn($t) => ($now - $t) < $window));

        $allowed = count($timestamps) < $maxPerMin;
        if ($allowed) {
            $timestamps[] = $now;
        }
        $data[$key] = $timestamps;

        // Purgar keys inactivos (ventana expirada) para no crecer indefinidamente
        foreach ($data as $k => $times) {
            $data[$k] = array_values(array_filter($times, fn($t) => ($now - $t) < $window));
            if (empty($data[$k])) unset($data[$k]);
        }

        ftruncate($fp, 0); rewind($fp);
        fwrite($fp, json_encode($data));
        flock($fp, LOCK_UN); fclose($fp);

        return $allowed;
    }

    // ─── Métodos de administración de IPs ───────────────────────────

    /**
     * Obtener lista de IPs bloqueadas — BD primaria, JSON como fallback
     * @return array ['ip', 'expira', 'restante_seg', 'restante', 'intentos', 'razon']
     */
    public static function getBlockedIPs(): array {
        // ── BD ────────────────────────────────────────────────────────
        try {
            $db   = \Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT ib_ip, ib_bloqueado_hasta, ib_intentos, ib_razon,
                       TIMESTAMPDIFF(SECOND, NOW(), ib_bloqueado_hasta) AS restante_seg
                FROM seguridad_ips_bloqueadas
                WHERE ib_bloqueado_hasta > NOW() AND ib_desbloqueado = 0
                ORDER BY ib_bloqueado_hasta DESC
            ");
            $stmt->execute();
            return array_map(fn($r) => [
                'ip'           => $r['ib_ip'],
                'expira'       => $r['ib_bloqueado_hasta'],
                'restante_seg' => (int)$r['restante_seg'],
                'restante'     => self::formatDuration(max(0, (int)$r['restante_seg'])),
                'intentos'     => (int)$r['ib_intentos'],
                'razon'        => $r['ib_razon'],
            ], $stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Throwable $e) { /* BD no disponible — usar JSON */ }

        // ── Fallback JSON ─────────────────────────────────────────────
        $blockFile = __DIR__ . '/../storage/cache/blocked_ips.json';
        if (!file_exists($blockFile)) return [];

        $blocked = json_decode(file_get_contents($blockFile), true);
        if (!is_array($blocked)) return [];

        $now = time(); $result = []; $changed = false;
        foreach ($blocked as $ip => $expiry) {
            if ($expiry > $now) {
                $result[] = [
                    'ip'           => $ip,
                    'expira'       => date('Y-m-d H:i:s', $expiry),
                    'restante_seg' => $expiry - $now,
                    'restante'     => self::formatDuration($expiry - $now),
                    'intentos'     => 0,
                    'razon'        => 'Múltiples intentos fallidos de login',
                ];
            } else {
                unset($blocked[$ip]); $changed = true;
            }
        }
        if ($changed) file_put_contents($blockFile, json_encode($blocked));
        return $result;
    }

    /**
     * Desbloquear IP manualmente — BD primaria + limpieza JSON
     * @param string $ip IP a desbloquear
     * @return bool True si se desbloqueó, false si no estaba bloqueada
     */
    public static function unblockIP(string $ip): bool {
        $unblocked = false;

        // ── BD ────────────────────────────────────────────────────────
        try {
            $db     = \Database::getInstance()->getConnection();
            $userId = $_SESSION['user_id'] ?? null;
            $stmt   = $db->prepare("
                UPDATE seguridad_ips_bloqueadas
                SET ib_desbloqueado = 1, ib_desbloqueado_por = ?
                WHERE ib_ip = ? AND ib_desbloqueado = 0
            ");
            $stmt->execute([$userId, $ip]);
            if ($stmt->rowCount() > 0) $unblocked = true;
        } catch (\Throwable $e) { /* BD no disponible */ }

        // ── Limpiar JSON cache también ────────────────────────────────
        $blockFile = __DIR__ . '/../storage/cache/blocked_ips.json';
        if (file_exists($blockFile)) {
            $blocked = json_decode(file_get_contents($blockFile), true);
            if (is_array($blocked) && isset($blocked[$ip])) {
                unset($blocked[$ip]);
                file_put_contents($blockFile, json_encode($blocked));
                $unblocked = true;
            }
        }

        if ($unblocked) self::clearFailedAttempts($ip);
        return $unblocked;
    }

    /**
     * Obtener intentos fallidos por IP — BD primaria, JSON como fallback
     * @return array ['ip', 'intentos_recientes', 'intentos_total', 'ultimo_intento', 'hace']
     */
    public static function getFailedAttempts(): array {
        $window = Config::SECURITY['brute_force_window'] ?? 900;

        // ── BD ────────────────────────────────────────────────────────
        try {
            $db     = \Database::getInstance()->getConnection();
            $cutoff = date('Y-m-d H:i:s', time() - $window);
            $stmt   = $db->prepare("
                SELECT acc_ip                   AS ip,
                       COUNT(*)                 AS intentos_recientes,
                       MAX(acc_fecha_hora)       AS ultimo_intento
                FROM seguridad_log_accesos
                WHERE acc_exito = 'N'
                  AND acc_tipo  = 'LOGIN_FAILED'
                  AND acc_fecha_hora > ?
                GROUP BY acc_ip
                ORDER BY intentos_recientes DESC
            ");
            $stmt->execute([$cutoff]);
            return array_map(fn($r) => [
                'ip'                 => $r['ip'],
                'intentos_recientes' => (int)$r['intentos_recientes'],
                'intentos_total'     => (int)$r['intentos_recientes'],
                'ultimo_intento'     => $r['ultimo_intento'],
                'hace'               => self::formatDuration(time() - strtotime($r['ultimo_intento'])),
            ], $stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Throwable $e) { /* BD no disponible — usar JSON */ }

        // ── Fallback JSON ─────────────────────────────────────────────
        $cacheFile = __DIR__ . '/../storage/cache/failed_attempts.json';
        if (!file_exists($cacheFile)) return [];

        $attempts = json_decode(file_get_contents($cacheFile), true);
        if (!is_array($attempts)) return [];

        $now = time(); $result = [];
        foreach ($attempts as $ip => $times) {
            if (!is_array($times)) continue;
            $recent   = array_filter($times, fn($t) => is_numeric($t) && ($now - (int)$t) < $window);
            $allTimes = array_filter($times, fn($t) => is_numeric($t));
            if (!empty($allTimes)) {
                $lastAttempt = max($allTimes);
                $result[] = [
                    'ip'                 => $ip,
                    'intentos_recientes' => count($recent),
                    'intentos_total'     => count($allTimes),
                    'ultimo_intento'     => date('Y-m-d H:i:s', $lastAttempt),
                    'hace'               => self::formatDuration($now - $lastAttempt),
                ];
            }
        }
        usort($result, fn($a, $b) => $b['intentos_recientes'] <=> $a['intentos_recientes']);
        return $result;
    }

    /**
     * Limpiar intentos fallidos de una IP del cache JSON
     * (En BD los datos se conservan como auditoría histórica)
     * @param string $ip IP a limpiar
     * @return bool
     */
    public static function clearFailedAttempts(string $ip): bool {
        $deleted = false;

        // BD: eliminar registros de login fallido para esta IP
        try {
            $db   = \Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                DELETE FROM seguridad_log_accesos
                WHERE acc_ip = ? AND acc_tipo = 'LOGIN_FAILED' AND acc_exito = 'N'
            ");
            $stmt->execute([$ip]);
            if ($stmt->rowCount() > 0) $deleted = true;
        } catch (\Throwable $e) {
            // BD no disponible, continuar con JSON
        }

        // JSON fallback: limpiar cache local
        $cacheFile = __DIR__ . '/../storage/cache/failed_attempts.json';
        if (file_exists($cacheFile)) {
            $attempts = json_decode(file_get_contents($cacheFile), true);
            if (is_array($attempts) && isset($attempts[$ip])) {
                unset($attempts[$ip]);
                file_put_contents($cacheFile, json_encode($attempts));
                $deleted = true;
            }
        }

        return $deleted;
    }
    
    /**
     * Formatear duración en texto legible
     * @param int $seconds
     * @return string
     */
    private static function formatDuration(int $seconds): string {
        if ($seconds < 60) return $seconds . 's';
        if ($seconds < 3600) return floor($seconds / 60) . 'min ' . ($seconds % 60) . 's';
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        return $h . 'h ' . $m . 'min';
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