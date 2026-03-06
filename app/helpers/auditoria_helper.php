<?php
/**
 * DigiSports - Helper de Auditoría
 *
 * Escribe en la tabla canónica seguridad_auditoria (igual que BaseController::audit()).
 * Usar este helper fuera de los controladores (helpers, servicios, scripts).
 * Los controladores deben usar $this->audit() directamente.
 *
 * Fallback: si la BD no está disponible, escribe en storage/logs/auditoria_YYYY-MM-DD.log
 */
function registrarAuditoria(
    string $accion,
    string $entidad,
    $entidadId,
    $antes    = null,
    $despues  = null,
    string $estado  = 'ok',
    string $detalle = ''
): void {
    $userId   = function_exists('getUserId')   ? getUserId()   : null;
    $tenantId = function_exists('getTenantId') ? getTenantId() : null;
    $ip       = $_SERVER['REMOTE_ADDR']    ?? '';
    $ua       = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $url      = $_SERVER['REQUEST_URI']    ?? '';
    $method   = $_SERVER['REQUEST_METHOD'] ?? '';

    // 1. Persistir en tabla seguridad_auditoria (canónico)
    try {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO seguridad_auditoria
                (aud_tenant_id, aud_usuario_id, aud_modulo, aud_tabla, aud_registro_id,
                 aud_operacion, aud_valores_anteriores, aud_valores_nuevos,
                 aud_ip, aud_user_agent, aud_url, aud_metodo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tenantId,
            $userId,
            $accion,
            $entidad,
            $entidadId,
            strtoupper($accion),
            $antes   !== null ? json_encode($antes)   : null,
            $despues !== null ? json_encode($despues)  : null,
            $ip, $ua, $url, $method,
        ]);
    } catch (Exception $e) {
        // BD no disponible — registrar en log de errores
        if (function_exists('logMessage')) {
            logMessage('AUDIT_DB_ERROR: ' . $e->getMessage(), 'errors');
        }
    }

    // 2. Log de archivo como respaldo de auditoría
    if (function_exists('logMessage')) {
        logMessage(json_encode([
            'accion'    => $accion,
            'entidad'   => $entidad,
            'entidadId' => $entidadId,
            'estado'    => $estado,
            'detalle'   => $detalle,
            'userId'    => $userId,
            'tenantId'  => $tenantId,
            'ip'        => $ip,
            'fecha'     => date('Y-m-d H:i:s'),
        ]), 'auditoria');
    }
}
