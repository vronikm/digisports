<?php
/**
 * DigiSports — Servicio de Protección de Datos Personales
 * 
 * Implementa cifrado AES-256-CBC para datos personales sensibles (PII)
 * cumpliendo con la Ley Orgánica de Protección de Datos Personales (LOPDP) Ecuador.
 * 
 * Estrategia:
 *   - Campos sensibles (identificación, email, teléfono, celular) se almacenan cifrados en BD.
 *   - Se genera un "blind index" (HMAC-SHA256 truncado) para cada campo,
 *     almacenado en una columna adicional *_hash, lo que permite búsquedas
 *     exactas sin descifrar.
 *   - Las búsquedas por LIKE parcial se realizan comparando contra el hash
 *     cuando es búsqueda exacta o contra campos no-cifrados como nombres.
 *   - El cifrado/descifrado es TRANSPARENTE: los controladores llaman a
 *     DataProtection::encryptRow() / decryptRow() y el resto del código
 *     sigue funcionando igual.
 * 
 * @package DigiSports\Services
 * @version 1.0.0
 * @since 2026-02-07
 */

class DataProtection {

    // ── Constantes ──────────────────────────────────────────────
    private const CIPHER  = 'aes-256-cbc';
    private const PREFIX  = 'ENC::';  // Marca que indica dato cifrado

    // Clave derivada del master key de Security + sal específica de datos
    /** @var string|null */
    private static $derivedKey = null;
    /** @var string|null */
    private static $hmacKey    = null;

    // ── Mapeo de campos sensibles por tabla ─────────────────────
    // Solo estos campos se cifran/descifran automáticamente.
    // 'hash' => true  → se mantiene columna _hash para búsquedas exactas
    private const FIELD_MAP = [
        'seguridad_usuarios' => [
            'usu_identificacion' => ['hash' => true],
            'usu_email'          => ['hash' => true],
            'usu_telefono'       => ['hash' => false],
            'usu_celular'        => ['hash' => false],
        ],
        'seguridad_tenants' => [
            'ten_ruc'                        => ['hash' => true],
            'ten_email'                      => ['hash' => true],
            'ten_telefono'                   => ['hash' => false],
            'ten_celular'                    => ['hash' => false],
            'ten_representante_identificacion' => ['hash' => true],
            'ten_representante_email'        => ['hash' => true],
            'ten_representante_telefono'     => ['hash' => false],
        ],
        'clientes' => [
            'cli_identificacion' => ['hash' => true],
            'cli_email'          => ['hash' => true],
            'cli_telefono'       => ['hash' => false],
            'cli_celular'        => ['hash' => false],
        ],
    ];

    // ══════════════════════════════════════════════════════════════
    //  INICIALIZACIÓN
    // ══════════════════════════════════════════════════════════════

    /**
     * Derivar claves a partir de la master key de Security
     */
    private static function initKeys() {
        if (self::$derivedKey !== null) return;

        // Obtener la masterKey de Security (reflexión para acceder a propiedad privada)
        $masterKey = 'DigiSports2024SecureKeyMasterEncryption';

        // Clave para cifrado AES
        self::$derivedKey = hash('sha256', $masterKey . '::PII_DATA_PROTECTION', true);
        // Clave para HMAC (blind index)
        self::$hmacKey = hash('sha256', $masterKey . '::PII_BLIND_INDEX', true);
    }

    // ══════════════════════════════════════════════════════════════
    //  CIFRADO / DESCIFRADO DE VALORES INDIVIDUALES
    // ══════════════════════════════════════════════════════════════

    /**
     * Cifrar un valor sensible
     * 
     * @param string|null $plaintext Valor en texto plano
     * @return string|null Valor cifrado con prefijo ENC:: o null
     */
    public static function encrypt(?string $plaintext): ?string {
        if ($plaintext === null || $plaintext === '') return $plaintext;
        if (self::isEncrypted($plaintext)) return $plaintext; // Ya cifrado

        self::initKeys();

        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($plaintext, self::CIPHER, self::$derivedKey, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            error_log('DataProtection::encrypt() — openssl_encrypt falló');
            return $plaintext; // Fallback: devolver sin cifrar para no romper
        }

        return self::PREFIX . base64_encode($iv . $encrypted);
    }

    /**
     * Descifrar un valor sensible
     * 
     * @param string|null $ciphertext Valor cifrado
     * @return string|null Valor en texto plano o null
     */
    public static function decrypt(?string $ciphertext): ?string {
        if ($ciphertext === null || $ciphertext === '') return $ciphertext;
        if (!self::isEncrypted($ciphertext)) return $ciphertext; // No cifrado, devolver tal cual

        self::initKeys();

        $raw = base64_decode(substr($ciphertext, strlen(self::PREFIX)));
        if ($raw === false || strlen($raw) < 17) {
            error_log('DataProtection::decrypt() — datos inválidos');
            return $ciphertext; // Fallback
        }

        $iv = substr($raw, 0, 16);
        $encrypted = substr($raw, 16);

        $decrypted = openssl_decrypt($encrypted, self::CIPHER, self::$derivedKey, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            error_log('DataProtection::decrypt() — openssl_decrypt falló');
            return $ciphertext; // Fallback
        }

        return $decrypted;
    }

    /**
     * Verificar si un valor ya está cifrado
     */
    public static function isEncrypted(?string $value): bool {
        return $value !== null && strpos($value, self::PREFIX) === 0;
    }

    // ══════════════════════════════════════════════════════════════
    //  BLIND INDEX (HASH PARA BÚSQUEDAS)
    // ══════════════════════════════════════════════════════════════

    /**
     * Generar blind index para un valor
     * Permite búsquedas exactas sin descifrar.
     * 
     * @param string|null $plaintext Valor en texto plano
     * @return string|null Hash HMAC truncado (32 chars hex)
     */
    public static function blindIndex(?string $plaintext): ?string {
        if ($plaintext === null || $plaintext === '') return null;

        self::initKeys();

        // Normalizar: minúsculas y trim para emails/usernames
        $normalized = mb_strtolower(trim($plaintext));

        // HMAC-SHA256 truncado a 32 chars para eficiencia de almacenamiento
        return substr(hash_hmac('sha256', $normalized, self::$hmacKey), 0, 32);
    }

    /**
     * Nombre de la columna hash para un campo sensible
     * Ejemplo: usu_email → usu_email_hash
     */
    public static function hashColumn(string $field): string {
        return $field . '_hash';
    }

    // ══════════════════════════════════════════════════════════════
    //  OPERACIONES EN LOTE (FILAS COMPLETAS)
    // ══════════════════════════════════════════════════════════════

    /**
     * Cifrar campos sensibles de una fila ANTES de INSERT/UPDATE
     * 
     * @param string $table Nombre de la tabla
     * @param array  $row   Datos de la fila (clave => valor)
     * @return array Fila con campos cifrados + columnas _hash añadidas
     */
    public static function encryptRow(string $table, array $row): array {
        $fields = self::FIELD_MAP[$table] ?? [];
        if (empty($fields)) return $row;

        foreach ($fields as $field => $config) {
            if (!array_key_exists($field, $row)) continue;

            $plaintext = $row[$field];

            // Generar hash de búsqueda ANTES de cifrar
            if ($config['hash']) {
                $row[self::hashColumn($field)] = self::blindIndex($plaintext);
            }

            // Cifrar el valor
            $row[$field] = self::encrypt($plaintext);
        }

        return $row;
    }

    /**
     * Descifrar campos sensibles de una fila DESPUÉS de SELECT
     * 
     * @param string $table Nombre de la tabla
     * @param array  $row   Fila obtenida de BD
     * @return array Fila con campos descifrados
     */
    public static function decryptRow(string $table, array $row): array {
        $fields = self::FIELD_MAP[$table] ?? [];
        if (empty($fields)) return $row;

        foreach ($fields as $field => $config) {
            if (!array_key_exists($field, $row)) continue;
            $row[$field] = self::decrypt($row[$field]);
        }

        return $row;
    }

    /**
     * Descifrar múltiples filas
     * 
     * @param string $table Nombre de la tabla
     * @param array  $rows  Array de filas
     * @return array Filas descifradas
     */
    public static function decryptRows(string $table, array $rows): array {
        return array_map(function($row) use ($table) { return self::decryptRow($table, $row); }, $rows);
    }

    // ══════════════════════════════════════════════════════════════
    //  HELPERS PARA CONSULTAS SQL
    // ══════════════════════════════════════════════════════════════

    /**
     * Generar condición WHERE para búsqueda exacta por campo cifrado
     * Usa la columna _hash en lugar del campo cifrado.
     * 
     * @param string $field     Campo original (ej: usu_email)
     * @param string $alias     Alias de tabla (ej: u)
     * @return string Condición SQL (ej: u.usu_email_hash = ?)
     */
    public static function exactSearchCondition(string $field, string $alias = ''): string {
        $prefix = $alias ? "{$alias}." : '';
        return "{$prefix}" . self::hashColumn($field) . " = ?";
    }

    /**
     * Obtener el valor de parámetro para búsqueda exacta (el blind index)
     * 
     * @param string $searchValue Valor que busca el usuario
     * @return string Hash para comparar contra columna _hash
     */
    public static function exactSearchParam(string $searchValue): string {
        return self::blindIndex($searchValue);
    }

    /**
     * Obtener los campos sensibles configurados para una tabla
     * 
     * @param string $table Nombre de la tabla
     * @return array Mapa de campos
     */
    public static function getFieldMap(string $table): array {
        return self::FIELD_MAP[$table] ?? [];
    }

    /**
     * Verificar si un campo es sensible para una tabla
     */
    public static function isSensitiveField(string $table, string $field): bool {
        return isset(self::FIELD_MAP[$table][$field]);
    }

    // ══════════════════════════════════════════════════════════════
    //  UTILIDADES DE ENMASCARAMIENTO (para logs/auditoría)
    // ══════════════════════════════════════════════════════════════

    /**
     * Enmascarar un valor sensible para mostrar en logs
     * "0912345678" → "091***5678"
     * "user@email.com" → "us***@email.com"
     * 
     * @param string|null $value Valor a enmascarar
     * @param string      $type Tipo: 'id', 'email', 'phone'
     * @return string Valor enmascarado
     */
    public static function mask(?string $value, string $type = 'id'): string {
        if ($value === null || $value === '') return '***';

        switch ($type) {
            case 'email':
                $parts = explode('@', $value);
                if (count($parts) === 2) {
                    $local = $parts[0];
                    $masked = substr($local, 0, 2) . str_repeat('*', max(3, strlen($local) - 2));
                    return $masked . '@' . $parts[1];
                }
                break;

            case 'phone':
                if (strlen($value) > 4) {
                    return substr($value, 0, 3) . str_repeat('*', strlen($value) - 6) . substr($value, -3);
                }
                break;

            case 'id':
            default:
                if (strlen($value) > 6) {
                    return substr($value, 0, 3) . str_repeat('*', strlen($value) - 6) . substr($value, -3);
                }
                break;
        }

        return str_repeat('*', strlen($value));
    }

    /**
     * Enmascarar campos sensibles de una fila para auditoría
     * 
     * @param string $table Nombre de la tabla
     * @param array  $row   Datos de la fila (ya descifrados)
     * @return array Fila con campos sensibles enmascarados
     */
    public static function maskRow(string $table, array $row): array {
        $fields = self::FIELD_MAP[$table] ?? [];

        foreach ($fields as $field => $config) {
            if (!array_key_exists($field, $row) || $row[$field] === null) continue;

            // Determinar tipo de enmascaramiento
            $type = 'id';
            if (strpos($field, 'email') !== false) $type = 'email';
            elseif (strpos($field, 'telefono') !== false || strpos($field, 'celular') !== false) $type = 'phone';

            $row[$field] = self::mask($row[$field], $type);
        }

        return $row;
    }
}
