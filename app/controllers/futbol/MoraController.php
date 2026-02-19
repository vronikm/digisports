<?php
/**
 * DigiSports Fútbol — Controlador de Mora
 * Control de mora por atrasos en pagos
 * 
 * Columnas reales:
 *   futbol_pagos: fpg_pago_id, fpg_tenant_id, fpg_sede_id, fpg_alumno_id, fpg_grupo_id, fpg_tipo,
 *     fpg_mes_correspondiente, fpg_monto, fpg_descuento, fpg_beca_descuento, fpg_recargo_mora, fpg_total,
 *     fpg_metodo_pago, fpg_referencia, fpg_fecha, fpg_fecha_vencimiento, fpg_dias_mora,
 *     fpg_estado, fpg_comprobante_num, fpg_notas, fpg_created_at, fpg_updated_at
 *   futbol_notificaciones usa prefijo fnt_
 *   Representante se obtiene via JOIN: alumnos → alu_representante_id → clientes (cli_nombres, cli_apellidos, cli_telefono)
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class MoraController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'FUTBOL';
    }

    /**
     * Mostrar pagos vencidos/pendientes y resumen de mora
     * Días de mora se calculan dinámicamente con DATEDIFF desde fpg_fecha
     */
    public function index() {
        try {
            $this->setupModule();
            $sedeId = $_SESSION['futbol_sede_id'] ?? null;

            $sedeSQL = $sedeId ? ' AND p.fpg_sede_id = ?' : '';
            $stm = $this->db->prepare("
                SELECT p.*,
                       DATEDIFF(CURDATE(), p.fpg_fecha) AS dias_mora_calc,
                       a.alu_nombres, a.alu_apellidos, a.alu_alumno_id,
                       c.cli_nombres AS rep_nombres, c.cli_apellidos AS rep_apellidos,
                       c.cli_telefono AS representante_telefono,
                       c.cli_email AS representante_email,
                       g.fgr_nombre AS grupo_nombre
                FROM futbol_pagos p
                LEFT JOIN alumnos a ON p.fpg_alumno_id = a.alu_alumno_id AND a.alu_tenant_id = p.fpg_tenant_id
                LEFT JOIN clientes c ON a.alu_representante_id = c.cli_cliente_id AND c.cli_tenant_id = a.alu_tenant_id
                LEFT JOIN futbol_grupos g ON p.fpg_grupo_id = g.fgr_grupo_id
                WHERE p.fpg_tenant_id = ?
                  AND p.fpg_estado IN ('PENDIENTE','VENCIDO')
                  AND p.fpg_fecha < CURDATE()
                  {$sedeSQL}
                ORDER BY p.fpg_fecha ASC
            ");
            $params = [$this->tenantId];
            if ($sedeId) $params[] = (int)$sedeId;
            $stm->execute($params);
            // Descifrar datos sensibles del representante (LOPDP)
            $morosos = $stm->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($morosos as &$m) {
                if (!empty($m['representante_telefono'])) $m['representante_telefono'] = \DataProtection::decrypt($m['representante_telefono']);
                if (!empty($m['representante_email']))    $m['representante_email']    = \DataProtection::decrypt($m['representante_email']);
                $m['representante_nombre'] = trim(($m['rep_nombres'] ?? '') . ' ' . ($m['rep_apellidos'] ?? ''));
            }
            unset($m);
            $this->viewData['morosos'] = $morosos;

            // Resumen
            $sedeSQL2 = $sedeId ? ' AND fpg_sede_id = ?' : '';
            $stmRes = $this->db->prepare("
                SELECT 
                    COUNT(*) AS total_en_mora,
                    COALESCE(SUM(fpg_total), 0) AS monto_total_adeudado,
                    COUNT(DISTINCT fpg_alumno_id) AS total_alumnos_morosos
                FROM futbol_pagos
                WHERE fpg_tenant_id = ?
                  AND fpg_estado IN ('PENDIENTE','VENCIDO')
                  AND fpg_fecha < CURDATE()
                  {$sedeSQL2}
            ");
            $paramsRes = [$this->tenantId];
            if ($sedeId) $paramsRes[] = (int)$sedeId;
            $stmRes->execute($paramsRes);
            $this->viewData['resumen'] = $stmRes->fetch(\PDO::FETCH_ASSOC);

            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Control de Mora';
            $this->renderModule('futbol/mora/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error cargando mora: " . $e->getMessage());
            $this->error('Error al cargar control de mora');
        }
    }

    /**
     * Enviar notificación de mora para un pago específico
     */
    public function enviarNotificacion() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $pagoId = (int)($this->post('pago_id') ?? 0);
            $canal = $this->post('canal') ?: 'SISTEMA';
            if (!$pagoId) return $this->jsonResponse(['success' => false, 'message' => 'ID de pago requerido']);
            if (!in_array($canal, ['EMAIL', 'SMS', 'WHATSAPP', 'SISTEMA'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Canal inválido']);
            }

            // Obtener datos del pago con alumno y representante
            $stm = $this->db->prepare("
                SELECT p.fpg_pago_id, p.fpg_alumno_id, p.fpg_total, p.fpg_fecha, p.fpg_tipo,
                       DATEDIFF(CURDATE(), p.fpg_fecha) AS dias_mora,
                       a.alu_nombres, a.alu_apellidos,
                       c.cli_nombres AS rep_nombres, c.cli_apellidos AS rep_apellidos,
                       c.cli_telefono AS representante_telefono
                FROM futbol_pagos p
                LEFT JOIN alumnos a ON p.fpg_alumno_id = a.alu_alumno_id AND a.alu_tenant_id = p.fpg_tenant_id
                LEFT JOIN clientes c ON a.alu_representante_id = c.cli_cliente_id AND c.cli_tenant_id = a.alu_tenant_id
                WHERE p.fpg_pago_id = ? AND p.fpg_tenant_id = ?
            ");
            $stm->execute([$pagoId, $this->tenantId]);
            $pago = $stm->fetch(\PDO::FETCH_ASSOC);
            if (!$pago) return $this->jsonResponse(['success' => false, 'message' => 'Pago no encontrado']);

            // Descifrar datos sensibles del representante (LOPDP)
            if (!empty($pago['representante_telefono'])) $pago['representante_telefono'] = \DataProtection::decrypt($pago['representante_telefono']);

            // Generar mensaje automático
            $alumnoNombre = trim(($pago['alu_nombres'] ?? '') . ' ' . ($pago['alu_apellidos'] ?? ''));
            $diasMora = (int)($pago['dias_mora'] ?? 0);
            $monto = number_format((float)($pago['fpg_total'] ?? 0), 2);
            $representante = trim(($pago['rep_nombres'] ?? '') . ' ' . ($pago['rep_apellidos'] ?? '')) ?: 'Representante';

            $mensaje = "Estimado/a {$representante}, le recordamos que existe un pago pendiente de \${$monto} "
                     . "correspondiente al alumno {$alumnoNombre}, con {$diasMora} días de atraso. "
                     . "Por favor, regularice su situación a la brevedad posible.";

            // Crear notificación con prefijo fnt_
            $stm2 = $this->db->prepare("
                INSERT INTO futbol_notificaciones (fnt_tenant_id, fnt_alumno_id, fnt_tipo, fnt_canal,
                    fnt_asunto, fnt_mensaje, fnt_estado)
                VALUES (?, ?, 'MORA', ?, 'Recordatorio de pago pendiente', ?, 'PENDIENTE')
            ");
            $stm2->execute([
                $this->tenantId,
                $pago['fpg_alumno_id'] ?? null,
                $canal,
                $mensaje,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Notificación de mora creada']);

        } catch (\Exception $e) {
            $this->logError("Error enviando notificación de mora: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al enviar notificación']);
        }
    }

    /**
     * Suspender inscripción del alumno por mora
     */
    public function suspender() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $alumnoId = (int)($this->post('alumno_id') ?? 0);
            $grupoId  = (int)($this->post('grupo_id') ?? 0);
            if (!$alumnoId) return $this->jsonResponse(['success' => false, 'message' => 'ID de alumno requerido']);

            // Suspender inscripciones activas del alumno (opcionalmente filtrar por grupo)
            $where = 'fin_alumno_id = ? AND fin_tenant_id = ? AND fin_estado = ?';
            $params = [$alumnoId, $this->tenantId, 'ACTIVA'];
            if ($grupoId) {
                $where .= ' AND fin_grupo_id = ?';
                $params[] = $grupoId;
            }

            $stm = $this->db->prepare("
                UPDATE futbol_inscripciones 
                SET fin_estado = 'SUSPENDIDA', fin_notas = CONCAT(COALESCE(fin_notas, ''), ' [Suspendida por mora ', CURDATE(), ']'), fin_updated_at = NOW()
                WHERE {$where}
            ");
            $stm->execute($params);
            $afectadas = $stm->rowCount();

            if ($afectadas === 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'No se encontraron inscripciones activas para suspender']);
            }

            // Crear notificación de suspensión
            $stm2 = $this->db->prepare("
                INSERT INTO futbol_notificaciones (fnt_tenant_id, fnt_alumno_id, fnt_tipo, fnt_canal,
                    fnt_asunto, fnt_mensaje, fnt_estado)
                VALUES (?, ?, 'MORA', 'SISTEMA', 'Suspensión por mora', 'Inscripción suspendida por mora en pagos.', 'PENDIENTE')
            ");
            $stm2->execute([$this->tenantId, $alumnoId]);

            return $this->jsonResponse(['success' => true, 'message' => "Alumno suspendido ({$afectadas} inscripción(es) afectada(s))"]);

        } catch (\Exception $e) {
            $this->logError("Error suspendiendo por mora: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al suspender alumno']);
        }
    }

    /**
     * Historial de pagos de un alumno (JSON)
     */
    public function historial() {
        try {
            $alumnoId = (int)($this->get('alumno_id') ?? 0);
            if (!$alumnoId) return $this->jsonResponse(['success' => false, 'message' => 'ID de alumno requerido']);

            $stm = $this->db->prepare("
                SELECT p.*,
                       DATEDIFF(CURDATE(), p.fpg_fecha) AS dias_mora_calc,
                       g.fgr_nombre AS grupo_nombre
                FROM futbol_pagos p
                LEFT JOIN futbol_grupos g ON p.fpg_grupo_id = g.fgr_grupo_id
                WHERE p.fpg_alumno_id = ? AND p.fpg_tenant_id = ?
                ORDER BY p.fpg_fecha DESC
                LIMIT 50
            ");
            $stm->execute([$alumnoId, $this->tenantId]);
            $pagos = $stm->fetchAll(\PDO::FETCH_ASSOC);

            // Datos del alumno
            $stmAlu = $this->db->prepare("
                SELECT a.alu_alumno_id, a.alu_nombres, a.alu_apellidos,
                       c.cli_nombres AS rep_nombres, c.cli_apellidos AS rep_apellidos,
                       c.cli_telefono AS representante_telefono
                FROM alumnos a
                LEFT JOIN clientes c ON a.alu_representante_id = c.cli_cliente_id AND c.cli_tenant_id = a.alu_tenant_id
                WHERE a.alu_alumno_id = ? AND a.alu_tenant_id = ?
            ");
            $stmAlu->execute([$alumnoId, $this->tenantId]);
            $alumno = $stmAlu->fetch(\PDO::FETCH_ASSOC);
            // Descifrar datos sensibles del representante (LOPDP)
            if ($alumno) {
                if (!empty($alumno['representante_telefono'])) $alumno['representante_telefono'] = \DataProtection::decrypt($alumno['representante_telefono']);
                $alumno['representante_nombre'] = trim(($alumno['rep_nombres'] ?? '') . ' ' . ($alumno['rep_apellidos'] ?? ''));
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'alumno' => $alumno,
                    'pagos' => $pagos,
                    'total_adeudado' => array_sum(array_map(function($p) {
                        return in_array($p['fpg_estado'], ['PENDIENTE', 'VENCIDO']) ? (float)$p['fpg_total'] : 0;
                    }, $pagos)),
                ]
            ]);

        } catch (\Exception $e) {
            $this->logError("Error obteniendo historial: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al obtener historial']);
        }
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
