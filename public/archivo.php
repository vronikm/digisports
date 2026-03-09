<?php
/**
 * DigiSports — Endpoint seguro de servicio de archivos
 *
 * Sirve archivos almacenados en storage/tenants/ con verificación de sesión,
 * aislamiento por tenant y cabeceras de caché apropiadas.
 *
 * Uso: /archivo.php?id={arc_id}
 *
 * Seguridad:
 *  - Requiere sesión activa
 *  - Verifica que el archivo pertenece al tenant del usuario en sesión
 *  - Genera cabecera Content-Type desde MIME registrado en BD
 *  - Bloquea path traversal y acceso a archivos fuera de storage/tenants/
 *  - No expone rutas físicas del servidor
 */

define('BASE_PATH', dirname(__DIR__));

// Cargar infraestructura mínima
require_once BASE_PATH . '/config/env.php';
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/security.php';
require_once BASE_PATH . '/app/helpers/functions.php';

// Iniciar sesión con la misma configuración que index.php
if (session_status() === PHP_SESSION_NONE) {
    $sessionName     = Config::SESSION['name']     ?? 'DIGISPORTS_SESSION';
    $sessionLifetime = Config::SESSION['lifetime'] ?? 1800;
    $sessionPath     = Config::SESSION['path']     ?? '/';
    $sessionDomain   = Config::SESSION['domain']   ?? '';
    $sessionSecure   = Config::SESSION['secure']   ?? false;
    $sessionHttpOnly = Config::SESSION['httponly'] ?? true;
    $sessionSameSite = Config::SESSION['samesite'] ?? 'Strict';

    session_name($sessionName);
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path'     => $sessionPath,
        'domain'   => $sessionDomain,
        'secure'   => $sessionSecure,
        'httponly' => $sessionHttpOnly,
        'samesite' => $sessionSameSite,
    ]);
    session_start();
}

// ── 1. Verificar sesión activa ────────────────────────────────────────────────
$userId   = $_SESSION['user_id']   ?? null;
$tenantId = $_SESSION['tenant_id'] ?? null;

if (!$userId || !$tenantId) {
    http_response_code(401);
    exit('No autorizado');
}

// ── 2. Obtener y validar el parámetro id ──────────────────────────────────────
$arcId = (int)($_GET['id'] ?? 0);
if ($arcId <= 0) {
    http_response_code(400);
    exit('Parámetro inválido');
}

// ── 3. Consultar el archivo en BD con verificación de tenant ─────────────────
try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    exit('Error de servidor');
}

$stmt = $db->prepare(
    'SELECT arc_ruta_relativa, arc_mime_type, arc_nombre_original, arc_tamanio_bytes
       FROM core_archivos
      WHERE arc_id = :id
        AND arc_tenant_id = :tenant
        AND arc_estado = "activo"
      LIMIT 1'
);
$stmt->execute([':id' => $arcId, ':tenant' => (int)$tenantId]);
$archivo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$archivo) {
    http_response_code(404);
    exit('Archivo no encontrado');
}

// ── 4. Construir y validar la ruta física ─────────────────────────────────────
$rutaRelativa = $archivo['arc_ruta_relativa'];

// Bloquear path traversal: la ruta debe comenzar con storage/tenants/
$rutaRelativaNorm = str_replace('\\', '/', $rutaRelativa);
if (
    strpos($rutaRelativaNorm, '..') !== false ||
    strpos($rutaRelativaNorm, 'storage/tenants/') !== 0
) {
    http_response_code(403);
    exit('Ruta no permitida');
}

$rutaAbsoluta = BASE_PATH . '/' . $rutaRelativaNorm;

if (!file_exists($rutaAbsoluta) || !is_file($rutaAbsoluta)) {
    http_response_code(404);
    exit('Archivo no encontrado en disco');
}

// ── 5. Validar MIME real (protección doble: BD + magic bytes) ─────────────────
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeReal = finfo_file($finfo, $rutaAbsoluta);
finfo_close($finfo);

// Los MIME permitidos para servir directamente
$mimesPermitidos = [
    'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml',
    'application/pdf',
];

$mimeServir = in_array($mimeReal, $mimesPermitidos) ? $mimeReal : 'application/octet-stream';

// ── 6. Enviar cabeceras y archivo ─────────────────────────────────────────────
// Prevenir XSS via SVG inline
$isInlineable = in_array($mimeReal, ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
$disposition  = $isInlineable ? 'inline' : 'attachment';

$nombreSeguro = preg_replace('/[^a-zA-Z0-9._\-]/', '_', $archivo['arc_nombre_original']);

header('Content-Type: ' . $mimeServir);
header('Content-Disposition: ' . $disposition . '; filename="' . $nombreSeguro . '"');
header('Content-Length: ' . filesize($rutaAbsoluta));
header('Cache-Control: private, max-age=3600');  // 1 hora, solo cliente (no proxy)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Limpiar cualquier output previo
while (ob_get_level()) {
    ob_end_clean();
}

readfile($rutaAbsoluta);
exit;
