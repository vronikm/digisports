<?php
/**
 * DigiSports - Configuración General de la Aplicación
 * 
 * @package DigiSports
 * @version 1.0.0
 */

// Prevenir acceso directo
defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__));

class Config {
    
    /**
     * Configuración de la aplicación
     */
    const APP = [
        'name' => 'DigiSports',
        'version' => '1.0.0',
        'description' => 'Sistema Integral de Gestión Deportiva',
        'company' => 'DigiSports Corp',
        'author' => 'DigiSports Development Team',
        'timezone' => 'America/Guayaquil',
        'locale' => 'es_EC',
        'charset' => 'UTF-8'
    ];
    
    /**
     * Configuración de base de datos
     */
    const DB = [
        // Base de datos principal (nueva)
        'core' => [
            'host' => 'localhost',
            'port' => '3306',
            'database' => 'digisports_core',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ],
        // Base de datos legacy (sistema antiguo de escuelas)
        'legacy' => [
            'host' => 'localhost',
            'port' => '3306',
            'database' => 'digisports',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_spanish2_ci'
        ]
    ];
    
    /**
     * Configuración de rutas
     */
    const ROUTES = [
        'base_url' => 'http://localhost/digisports/public/',
        'enable_clean_urls' => true,
        'default_controller' => 'welcome',
        'default_action' => 'index',
        'error_controller' => 'error',
        'error_action' => 'show'
    ];
    
    /**
     * Configuración de sesiones
     */
    const SESSION = [
        'name' => 'DIGISPORTS_SESSION',
        'lifetime' => 1800, // 30 minutos
        'path' => '/',
        'domain' => '',
        'secure' => false, // true en producción con HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ];
    
    /**
     * Configuración de seguridad
     */
    const SECURITY = [
        'master_key' => 'DigiSports2024SecureKeyMasterEncryption',
        'csrf_enabled' => true,
        'xss_protection' => true,
        'sql_injection_protection' => true,
        '2fa_enabled' => true,
        'password_policy' => [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special' => true,
            'expiration_days' => 90
        ],
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutos
        'token_expiration' => 600 // 10 minutos
    ];
    
    /**
     * Configuración de correo electrónico
     */
    const EMAIL = [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_user' => 'noreply@digisports.com',
        'smtp_pass' => '',
        'smtp_encryption' => 'tls',
        'from_email' => 'noreply@digisports.com',
        'from_name' => 'DigiSports',
        'admin_email' => 'admin@digisports.com'
    ];
    
    /**
     * Configuración de archivos y uploads
     */
    const FILES = [
        'upload_path' => BASE_PATH . '/storage/uploads/',
        'max_size' => 5242880, // 5MB
        'allowed_types' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'comprobantes' => ['xml', 'pdf']
        ],
        'image_max_width' => 2000,
        'image_max_height' => 2000
    ];
    
    /**
     * Configuración de logs
     */
    const LOGS = [
        'enabled' => true,
        'path' => BASE_PATH . '/storage/logs/',
        'level' => 'DEBUG', // DEBUG, INFO, WARNING, ERROR, CRITICAL
        'max_files' => 30, // Mantener logs por 30 días
        'separate_by_type' => true
    ];
    
    /**
     * Configuración de cache
     */
    const CACHE = [
        'enabled' => true,
        'driver' => 'file', // file, redis, memcached
        'path' => BASE_PATH . '/storage/cache/',
        'lifetime' => 3600, // 1 hora
        'prefix' => 'digisports_'
    ];
    
    /**
     * Módulos del sistema
     */
    const MODULES = [
        'core' => [
            'enabled' => true,
            'path' => '/app/controllers/core/',
            'namespace' => 'App\Controllers\Core'
        ],
        'instalaciones' => [
            'enabled' => true,
            'path' => '/app/controllers/instalaciones/',
            'namespace' => 'App\Controllers\Instalaciones'
        ],
        'reservas' => [
            'enabled' => true,
            'path' => '/app/controllers/reservas/',
            'namespace' => 'App\Controllers\Reservas'
        ],
        'facturacion' => [
            'enabled' => true,
            'path' => '/app/controllers/facturacion/',
            'namespace' => 'App\Controllers\Facturacion'
        ],
        'reportes' => [
            'enabled' => true,
            'path' => '/app/controllers/reportes/',
            'namespace' => 'App\Controllers\Reportes'
        ],
        'clientes' => [
            'enabled' => true,
            'path' => '/app/controllers/clientes/',
            'namespace' => 'App\Controllers\Clientes'
        ],
        'escuelas' => [
            'enabled' => true,
            'path' => '/app/controllers/escuelas/',
            'namespace' => 'App\Controllers\Escuelas',
            'is_legacy' => true,
            'database' => 'legacy'
        ],
        'torneos' => [
            'enabled' => false,
            'path' => '/app/controllers/torneos/',
            'namespace' => 'App\Controllers\Torneos'
        ],
        // Módulos deportivos
        'futbol' => [
            'enabled' => true,
            'path' => '/app/controllers/futbol/',
            'namespace' => 'App\Controllers\Futbol'
        ],
        'basket' => [
            'enabled' => true,
            'path' => '/app/controllers/basket/',
            'namespace' => 'App\Controllers\Basket'
        ],
        'natacion' => [
            'enabled' => true,
            'path' => '/app/controllers/natacion/',
            'namespace' => 'App\Controllers\Natacion'
        ],
        'artes_marciales' => [
            'enabled' => true,
            'path' => '/app/controllers/artes_marciales/',
            'namespace' => 'App\Controllers\ArtesMarciales'
        ],
        'ajedrez' => [
            'enabled' => true,
            'path' => '/app/controllers/ajedrez/',
            'namespace' => 'App\Controllers\Ajedrez'
        ],
        'multideporte' => [
            'enabled' => true,
            'path' => '/app/controllers/multideporte/',
            'namespace' => 'App\Controllers\Multideporte'
        ],
        'store' => [
            'enabled' => true,
            'path' => '/app/controllers/store/',
            'namespace' => 'App\Controllers\Store'
        ],
        // Módulo de Seguridad (Administración del Sistema)
        'seguridad' => [
            'enabled' => true,
            'path' => '/app/controllers/seguridad/',
            'namespace' => 'App\Controllers\Seguridad'
        ]
    ];
    
    /**
     * Configuración de paginación
     */
    const PAGINATION = [
        'per_page' => 20,
        'max_per_page' => 100,
        'query_string' => 'page'
    ];
    
    /**
     * Configuración de facturación electrónica SRI
     */
    const FACTURACION_SRI = [
        'enabled' => true,
        'ambiente' => 'PRUEBAS', // PRUEBAS, PRODUCCION
        'url_autorizacion' => [
            'PRUEBAS' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/',
            'PRODUCCION' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/'
        ],
        'certificado_path' => BASE_PATH . '/storage/certificados/',
        'xml_path' => BASE_PATH . '/storage/comprobantes/xml/',
        'pdf_path' => BASE_PATH . '/storage/comprobantes/pdf/',
        'contribuyente_especial' => '',
        'obligado_contabilidad' => 'SI'
    ];
    
    /**
     * Configuración de pasarelas de pago
     */
    const PAYMENT_GATEWAYS = [
        'payphone' => [
            'enabled' => true,
            'api_key' => '',
            'store_id' => '',
            'sandbox' => true
        ],
        'datafast' => [
            'enabled' => false,
            'merchant_id' => '',
            'terminal_id' => '',
            'sandbox' => true
        ],
        'placetopay' => [
            'enabled' => false,
            'login' => '',
            'trankey' => '',
            'url' => 'https://test.placetopay.com/redirection/'
        ]
    ];
    
    /**
     * Configuración de notificaciones
     */
    const NOTIFICATIONS = [
        'email_enabled' => true,
        'sms_enabled' => false,
        'push_enabled' => false,
        'templates_path' => BASE_PATH . '/app/views/emails/'
    ];
    
    /**
     * Modo debug
     */
    const DEBUG = [
        'enabled' => true, // false en producción
        'display_errors' => true,
        'log_errors' => true,
        'error_reporting' => E_ALL,
        'show_queries' => true
    ];
    
    /**
     * Obtener configuración por clave
     * 
     * @param string $key Clave de configuración separada por puntos
     * @return mixed Valor de configuración
     */
    public static function get($key, $default = null) {
        $keys = explode('.', $key);
        $constant = array_shift($keys);
        
        if (!defined('self::' . $constant)) {
            return $default;
        }
        
        $value = constant('self::' . $constant);
        
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
    
    /**
     * Verificar si estamos en modo debug
     * 
     * @return bool
     */
    public static function isDebug() {
        return self::DEBUG['enabled'];
    }
    
    /**
     * Obtener URL base
     * 
     * @return string
     */
    public static function baseUrl($path = '') {
        $base = self::ROUTES['base_url'];
        return $path ? rtrim($base, '/') . '/' . ltrim($path, '/') : $base;
    }
    
    /**
     * Obtener path completo
     * 
     * @param string $path Path relativo
     * @return string Path absoluto
     */
    public static function path($path = '') {
        return BASE_PATH . '/' . ltrim($path, '/');
    }
}

// Configurar zona horaria
date_default_timezone_set(Config::APP['timezone']);

// Configurar locale
setlocale(LC_ALL, Config::APP['locale']);

// Configurar errores según modo debug
if (Config::DEBUG['enabled']) {
    error_reporting(Config::DEBUG['error_reporting']);
    ini_set('display_errors', Config::DEBUG['display_errors']);
    ini_set('log_errors', Config::DEBUG['log_errors']);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Configurar límites de PHP
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '10M');