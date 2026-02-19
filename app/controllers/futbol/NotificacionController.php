<?php
/**
 * DigiSports Fútbol — Controlador de Notificaciones
 * Gestión y envío de notificaciones
 * 
 * Columnas reales:
 *   futbol_notificaciones: fno_notificacion_id, fno_tenant_id, fno_alumno_id, fno_tipo, fno_canal,
 *     fno_asunto, fno_mensaje, fno_fecha_envio, fno_estado, fno_created_at, fno_destinatario
 *   NO existen: fnt_cliente_id, fnt_fecha_programada, fnt_referencia_id, fnt_referencia_tipo, fnt_intentos
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class NotificacionController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'FUTBOL';
    }

    /**
     * Listar notificaciones con filtros y resumen por estado
     */
    public function index() {
        try {
            $this->setupModule();

            $where = 'n.fno_tenant_id = ?';
            $params = [$this->tenantId];

            // Filtro por tipo
            $tipo = $this->get('tipo');
            if ($tipo) { $where .= ' AND n.fno_tipo = ?'; $params[] = $tipo; }

            // Filtro por canal
            $canal = $this->get('canal');
            if ($canal) { $where .= ' AND n.fno_canal = ?'; $params[] = $canal; }

            // Filtro por estado
            $estado = $this->get('estado');
            if ($estado) { $where .= ' AND n.fno_estado = ?'; $params[] = $estado; }

            $stm = $this->db->prepare("
                SELECT n.*,
                       a.alu_nombres, a.alu_apellidos,
                       c.cli_nombres AS rep_nombres, c.cli_apellidos AS rep_apellidos,
                       c.cli_telefono AS representante_telefono,
                       c.cli_email AS representante_email
                FROM futbol_notificaciones n
                LEFT JOIN alumnos a ON n.fno_alumno_id = a.alu_alumno_id AND a.alu_tenant_id = n.fno_tenant_id
                LEFT JOIN clientes c ON a.alu_representante_id = c.cli_cliente_id AND c.cli_tenant_id = a.alu_tenant_id
                WHERE {$where}
                ORDER BY n.fno_created_at DESC
                LIMIT 200
            ");
            $stm->execute($params);
            // Descifrar datos sensibles del representante (LOPDP)
            $notificaciones = $stm->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($notificaciones as &$n) {
                if (!empty($n['representante_telefono'])) $n['representante_telefono'] = \DataProtection::decrypt($n['representante_telefono']);
                if (!empty($n['representante_email']))    $n['representante_email']    = \DataProtection::decrypt($n['representante_email']);
                $n['representante_nombre'] = trim(($n['rep_nombres'] ?? '') . ' ' . ($n['rep_apellidos'] ?? ''));
            }
            unset($n);
            $this->viewData['notificaciones'] = $notificaciones;

            // Resumen por estado
            $stmRes = $this->db->prepare("
                SELECT fno_estado, COUNT(*) AS total
                FROM futbol_notificaciones
                WHERE fno_tenant_id = ?
                GROUP BY fno_estado
            ");
            $stmRes->execute([$this->tenantId]);
            $resumen = [];
            foreach ($stmRes->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $resumen[$row['fno_estado']] = (int)$row['total'];
            }
            $this->viewData['resumen_estados'] = $resumen;

            // Alumnos para el formulario de creación
            $stmAlu = $this->db->prepare("
                SELECT alu_alumno_id, alu_nombres, alu_apellidos
                FROM alumnos
                WHERE alu_tenant_id = ? AND alu_estado = 'ACTIVO'
                ORDER BY alu_apellidos, alu_nombres
            ");
            $stmAlu->execute([$this->tenantId]);
            $this->viewData['alumnos'] = $stmAlu->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Notificaciones';
            $this->renderModule('futbol/notificaciones/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando notificaciones: " . $e->getMessage());
            $this->error('Error al cargar notificaciones');
        }
    }

    /**
     * Crear notificación individual
     */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $tipo = $this->post('tipo') ?: 'GENERAL';
            $canal = $this->post('canal') ?: 'SISTEMA';
            $asunto = trim($this->post('asunto') ?? '');
            $mensaje = trim($this->post('mensaje') ?? '');
            $alumnoId = (int)($this->post('alumno_id') ?? 0) ?: null;
            $destinatario = trim($this->post('destinatario') ?? '');

            if (empty($mensaje)) return $this->jsonResponse(['success' => false, 'message' => 'El mensaje es obligatorio']);
            if (!in_array($tipo, ['PAGO_PENDIENTE', 'MORA', 'BIENVENIDA', 'RECORDATORIO', 'TORNEO', 'ASISTENCIA', 'GENERAL'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Tipo de notificación inválido']);
            }
            if (!in_array($canal, ['EMAIL', 'SMS', 'WHATSAPP', 'PUSH', 'SISTEMA'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Canal de notificación inválido']);
            }

            $stm = $this->db->prepare("
                INSERT INTO futbol_notificaciones (fno_tenant_id, fno_alumno_id, fno_tipo, fno_canal,
                    fno_asunto, fno_mensaje, fno_destinatario, fno_estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDIENTE')
            ");
            $stm->execute([
                $this->tenantId,
                $alumnoId,
                $tipo,
                $canal,
                $asunto ?: null,
                $mensaje,
                $destinatario ?: null,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Notificación creada']);

        } catch (\Exception $e) {
            $this->logError("Error creando notificación: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear notificación']);
        }
    }

    /**
     * Reenviar notificación (marcar como enviada con nueva fecha)
     */
    public function reenviar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            // Verificar que exista
            $stmCheck = $this->db->prepare("SELECT fno_notificacion_id FROM futbol_notificaciones WHERE fno_notificacion_id = ? AND fno_tenant_id = ?");
            $stmCheck->execute([$id, $this->tenantId]);
            if (!$stmCheck->fetchColumn()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Notificación no encontrada']);
            }

            $this->db->prepare("
                UPDATE futbol_notificaciones 
                SET fno_estado = 'PENDIENTE', fno_fecha_envio = NULL
                WHERE fno_notificacion_id = ? AND fno_tenant_id = ?
            ")->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Notificación marcada para reenvío']);

        } catch (\Exception $e) {
            $this->logError("Error reenviando notificación: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al reenviar notificación']);
        }
    }

    /**
     * Eliminar notificación
     */
    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("DELETE FROM futbol_notificaciones WHERE fno_notificacion_id = ? AND fno_tenant_id = ?")
                ->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Notificación eliminada']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando notificación: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar notificación']);
        }
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
