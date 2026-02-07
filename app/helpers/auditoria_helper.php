<?php
/**
 * Helper de auditoría para DigiSports
 */
function registrarAuditoria($accion, $entidad, $entidadId, $antes = null, $despues = null, $estado = 'ok', $detalle = '') {
    $userId = getUserId();
    $tenantId = getTenantId();
    $log = [
        'accion' => $accion,
        'entidad' => $entidad,
        'entidadId' => $entidadId,
        'antes' => $antes,
        'despues' => $despues,
        'estado' => $estado,
        'detalle' => $detalle,
        'userId' => $userId,
        'tenantId' => $tenantId,
        'fecha' => date('Y-m-d H:i:s')
    ];
    logMessage(json_encode($log), 'auditoria');
}
