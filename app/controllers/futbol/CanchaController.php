<?php
/**
 * DigiSports Fútbol — Controlador de Canchas (Vista de solo lectura)
 * Consulta de canchas desde instalaciones_canchas filtradas por tipo fútbol
 *
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class CanchaController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    /**
     * Listar canchas de fútbol con conteo de grupos asignados
     */
    public function index() {
        try {
            $this->setupModule();

            $stm = $this->db->prepare("
                SELECT can.*,
                       s.sed_nombre AS sede_nombre,
                       (SELECT COUNT(*) FROM futbol_grupos fgr
                        WHERE fgr.fgr_cancha_id = can.can_cancha_id
                          AND fgr.fgr_tenant_id = can.can_tenant_id
                          AND fgr.fgr_estado IN ('ABIERTO','EN_CURSO')) AS total_grupos
                FROM instalaciones_canchas can
                LEFT JOIN instalaciones i ON can.can_instalacion_id = i.ins_instalacion_id
                LEFT JOIN instalaciones_sedes s ON i.ins_sede_id = s.sed_sede_id
                WHERE can.can_tenant_id = ? AND can.can_tipo = 'futbol'
                ORDER BY can.can_nombre
            ");
            $stm->execute([$this->tenantId]);
            $this->viewData['canchas'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

            // Sedes para filtro
            $stmSedes = $this->db->prepare("SELECT sed_sede_id, sed_nombre FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_nombre");
            $stmSedes->execute([$this->tenantId]);
            $this->viewData['sedes'] = $stmSedes->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['sede_activa'] = $_SESSION['futbol_sede_id'] ?? null;

            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Canchas de Fútbol';
            $this->renderModule('futbol/canchas/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando canchas: " . $e->getMessage());
            $this->error('Error al cargar canchas');
        }
    }

    /**
     * Crear nueva cancha
     */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombre = trim($this->post('nombre') ?? '');
            $tipo   = $this->post('tipo') ?: 'FUTBOL_11';
            if (empty($nombre)) return $this->jsonResponse(['success' => false, 'message' => 'El nombre es obligatorio']);

            // Obtener instalacion_id desde la sede activa
            $sedeId = $_SESSION['futbol_sede_id'] ?? null;
            $instalacionId = null;
            if ($sedeId) {
                $stmI = $this->db->prepare("SELECT ins_instalacion_id FROM instalaciones WHERE ins_sede_id = ? AND ins_tenant_id = ? LIMIT 1");
                $stmI->execute([$sedeId, $this->tenantId]);
                $instalacionId = $stmI->fetchColumn() ?: null;
            }

            $stm = $this->db->prepare("
                INSERT INTO instalaciones_canchas (can_tenant_id, can_instalacion_id, can_nombre, can_tipo, can_superficie,
                    can_estado, can_capacidad_maxima, can_dimensiones, can_iluminacion, can_techada, can_notas)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stm->execute([
                $this->tenantId,
                $instalacionId,
                $nombre,
                $tipo,
                $this->post('superficie') ?: null,
                $this->post('estado') ?: 'DISPONIBLE',
                (int)($this->post('capacidad') ?? 0) ?: null,
                $this->post('dimensiones') ?: null,
                $this->post('iluminacion') ? 1 : 0,
                $this->post('techada') ? 1 : 0,
                $this->post('notas') ?: null,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Cancha creada correctamente']);

        } catch (\Exception $e) {
            $this->logError("Error creando cancha: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear cancha']);
        }
    }

    /**
     * Editar cancha existente
     */
    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $stm = $this->db->prepare("
                UPDATE instalaciones_canchas SET can_nombre=?, can_tipo=?, can_superficie=?, can_estado=?,
                    can_capacidad_maxima=?, can_dimensiones=?, can_iluminacion=?, can_techada=?, can_notas=?
                WHERE can_cancha_id=? AND can_tenant_id=?
            ");
            $stm->execute([
                trim($this->post('nombre') ?? ''),
                $this->post('tipo') ?: 'FUTBOL_11',
                $this->post('superficie') ?: null,
                $this->post('estado') ?: 'DISPONIBLE',
                (int)($this->post('capacidad') ?? 0) ?: null,
                $this->post('dimensiones') ?: null,
                $this->post('iluminacion') ? 1 : 0,
                $this->post('techada') ? 1 : 0,
                $this->post('notas') ?: null,
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Cancha actualizada correctamente']);

        } catch (\Exception $e) {
            $this->logError("Error editando cancha: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar cancha']);
        }
    }

    /**
     * Eliminar cancha (soft delete via estado)
     */
    public function eliminar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE instalaciones_canchas SET can_estado = 'FUERA_SERVICIO' WHERE can_cancha_id = ? AND can_tenant_id = ?")
                ->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Cancha eliminada correctamente']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando cancha: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar cancha']);
        }
    }

    /**
     * Cambiar estado de cancha
     */
    public function cambiarEstado() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id     = (int)($this->post('id') ?? 0);
            $estado = $this->post('estado') ?? '';
            if (!$id || !in_array($estado, ['DISPONIBLE', 'MANTENIMIENTO', 'FUERA_SERVICIO'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Parámetros inválidos']);
            }

            $this->db->prepare("UPDATE instalaciones_canchas SET can_estado = ? WHERE can_cancha_id = ? AND can_tenant_id = ?")
                ->execute([$estado, $id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Estado de cancha actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error cambiando estado de cancha: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al cambiar estado']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
