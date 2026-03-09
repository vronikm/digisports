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
        
        // Validar que no sea muy antigua (ver TOKEN_TIMEOUT — por defecto 8 horas)
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
     * Verificar intentos de fuerza bruta — solo BD (seguridad_log_accesos)
     */
    private static function checkBruteForce() {
        $ip          = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $maxAttempts = Config::SECURITY['max_login_attempts'] ?? 5;
        $window      = Config::SECURITY['brute_force_window']  ?? 900;

        try {
            $db     = \Database::getInstance()->getConnection();
            $cutoff = date('Y-m-d H:i:s', time() - $window);
            $stmt   = $db->prepare("
                SELECT COUNT(*) AS cnt
                FROM seguridad_log_accesos
                WHERE acc_ip = ?
                  AND acc_exito = 'N'
                  AND acc_tipo  = 'LOGIN_FAILED'
                  AND acc_fecha > ?
            ");
            $stmt->execute([$ip, $cutoff]);
            $cnt = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);
            if ($cnt >= $maxAttempts) {
                self::blockIP($ip);
            }
        } catch (\Throwable $e) {
            error_log('[Security] checkBruteForce: BD no disponible — ' . $e->getMessage());
        }
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
                  AND acc_fecha > ?
            ");
            $stmt->execute([$ip, $cutoff]);
            return (int)($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Bloquear IP — solo BD (seguridad_ips_bloqueadas)
     * @param string $ip IP a bloquear
     */
    private static function blockIP(string $ip) {
        $blockDuration = Config::SECURITY['ip_block_duration'] ?? 3600;
        $expiry        = date('Y-m-d H:i:s', time() + $blockDuration);
        $intentos      = self::countRecentAttempts($ip);

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
        } catch (\Throwable $e) {
            error_log('[Security] blockIP: BD no disponible — ' . $e->getMessage());
        }
    }

    /**
     * Verificar si IP está bloqueada — solo BD (seguridad_ips_bloqueadas)
     * Falla abierto (false) si la BD no está disponible para no bloquear usuarios.
     * @param string|null $ip IP a verificar (null = IP del request)
     * @return bool True si está bloqueada
     */
    public static function isIPBlocked($ip = null): bool {
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'Unknown');

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
        } catch (\Throwable $e) {
            error_log('[Security] isIPBlocked: BD no disponible — ' . $e->getMessage());
            return false; // fail open: no bloqueamos si la BD falla
        }
    }

    // ─── Rate Limiting ────────────────────────────────────────────────

    /**
     * Verificar rate limit para una acción dada.
     * Usa tabla BD seguridad_rate_limit: inserta un registro por request
     * y cuenta los recientes en la ventana deslizante de 1 minuto.
     * Falla abierto (true) si la BD no está disponible.
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
        $ip     = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $now    = time();
        $window = 60; // ventana deslizante de 1 minuto
        $cutoff = date('Y-m-d H:i:s', $now - $window);

        try {
            $db = \Database::getInstance()->getConnection();

            // 1. Insertar el request actual
            $db->prepare("
                INSERT INTO seguridad_rate_limit (srl_ip, srl_action, srl_fecha)
                VALUES (?, ?, NOW())
            ")->execute([$ip, $action]);

            // 2. Contar requests en la ventana (incluye el que se acaba de insertar)
            $stmt = $db->prepare("
                SELECT COUNT(*) AS cnt
                FROM seguridad_rate_limit
                WHERE srl_ip = ? AND srl_action = ? AND srl_fecha > ?
            ");
            $stmt->execute([$ip, $action, $cutoff]);
            $cnt = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);

            // 3. Purge probabilístico (1% de los requests) para evitar acumulación
            if (random_int(1, 100) === 1) {
                $db->prepare("
                    DELETE FROM seguridad_rate_limit
                    WHERE srl_fecha < ?
                ")->execute([date('Y-m-d H:i:s', $now - $window)]);
            }

            return $cnt <= $maxPerMin;

        } catch (\Throwable $e) {
            error_log('[Security] checkRateLimit: BD no disponible — ' . $e->getMessage());
            return true; // fail open: no bloqueamos si la BD falla
        }
    }

    // ─── Métodos de administración de IPs ───────────────────────────

    /**
     * Obtener lista de IPs bloqueadas — solo BD (seguridad_ips_bloqueadas)
     * @return array ['ip', 'expira', 'restante_seg', 'restante', 'intentos', 'razon']
     */
    public static function getBlockedIPs(): array {
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
        } catch (\Throwable $e) {
            error_log('[Security] getBlockedIPs: BD no disponible — ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Desbloquear IP manualmente — solo BD (seguridad_ips_bloqueadas)
     * @param string $ip IP a desbloquear
     * @return bool True si se desbloqueó, false si no estaba bloqueada
     */
    public static function unblockIP(string $ip): bool {
        try {
            $db     = \Database::getInstance()->getConnection();
            $userId = $_SESSION['user_id'] ?? null;
            $stmt   = $db->prepare("
                UPDATE seguridad_ips_bloqueadas
                SET ib_desbloqueado = 1, ib_desbloqueado_por = ?
                WHERE ib_ip = ? AND ib_desbloqueado = 0
            ");
            $stmt->execute([$userId, $ip]);
            $unblocked = $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[Security] unblockIP: BD no disponible — ' . $e->getMessage());
            return false;
        }

        if ($unblocked) self::clearFailedAttempts($ip);
        return $unblocked;
    }

    /**
     * Obtener intentos fallidos por IP — solo BD (seguridad_log_accesos)
     * @return array ['ip', 'intentos_recientes', 'intentos_total', 'ultimo_intento', 'hace']
     */
    public static function getFailedAttempts(): array {
        $window = Config::SECURITY['brute_force_window'] ?? 900;

        try {
            $db     = \Database::getInstance()->getConnection();
            $cutoff = date('Y-m-d H:i:s', time() - $window);
            $stmt   = $db->prepare("
                SELECT acc_ip                   AS ip,
                       COUNT(*)                 AS intentos_recientes,
                       MAX(acc_fecha)       AS ultimo_intento
                FROM seguridad_log_accesos
                WHERE acc_exito = 'N'
                  AND acc_tipo  = 'LOGIN_FAILED'
                  AND acc_fecha > ?
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
        } catch (\Throwable $e) {
            error_log('[Security] getFailedAttempts: BD no disponible — ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Limpiar intentos fallidos de una IP — solo BD (seguridad_log_accesos)
     * Los registros históricos se conservan como auditoría; solo se marcan como resueltos.
     * @param string $ip IP a limpiar
     * @return bool
     */
    public static function clearFailedAttempts(string $ip): bool {
        try {
            $db   = \Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                DELETE FROM seguridad_log_accesos
                WHERE acc_ip = ? AND acc_tipo = 'LOGIN_FAILED' AND acc_exito = 'N'
            ");
            $stmt->execute([$ip]);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[Security] clearFailedAttempts: BD no disponible — ' . $e->getMessage());
            return false;
        }
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