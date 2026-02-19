<?php
/**
 * DigiSports Fútbol — Controlador de Becas
 * Gestión de becas y asignaciones a alumnos
 * 
 * Columnas reales:
 *   futbol_becas: fbe_beca_id, fbe_tenant_id, fbe_nombre, fbe_tipo, fbe_valor, fbe_descripcion, fbe_activo, fbe_created_at, fbe_updated_at
 *   futbol_beca_asignaciones: fba_asignacion_id, fba_tenant_id, fba_beca_id, fba_alumno_id, fba_inscripcion_id, fba_fecha_asignacion, fba_fecha_vencimiento, fba_motivo, fba_aprobado_por, fba_estado, fba_created_at
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class BecaController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'FUTBOL';
    }

    /**
     * Listar becas y asignaciones recientes
     */
    public function index() {
        try {
            $this->setupModule();
            $sedeId = $_SESSION['futbol_sede_id'] ?? null;

            // Listar becas activas con total de asignaciones activas
            $stm = $this->db->prepare("
                SELECT b.*,
                       (SELECT COUNT(*) FROM futbol_beca_asignaciones fba 
                        WHERE fba.fba_beca_id = b.fbe_beca_id AND fba.fba_tenant_id = b.fbe_tenant_id AND fba.fba_estado = 'ACTIVA') AS total_asignaciones
                FROM futbol_becas b
                WHERE b.fbe_tenant_id = ? AND b.fbe_activo = 1
                ORDER BY b.fbe_nombre
            ");
            $stm->execute([$this->tenantId]);
            $this->viewData['becas'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

            // Asignaciones recientes
            $sedeSQL = $sedeId ? ' AND a.alu_sede_id = ?' : '';
            $stm2 = $this->db->prepare("
                SELECT fba.*, b.fbe_nombre, b.fbe_tipo, b.fbe_valor,
                       a.alu_nombres, a.alu_apellidos
                FROM futbol_beca_asignaciones fba
                JOIN futbol_becas b ON fba.fba_beca_id = b.fbe_beca_id AND b.fbe_tenant_id = fba.fba_tenant_id
                JOIN alumnos a ON fba.fba_alumno_id = a.alu_alumno_id AND a.alu_tenant_id = fba.fba_tenant_id
                WHERE fba.fba_tenant_id = ?{$sedeSQL}
                ORDER BY fba.fba_fecha_asignacion DESC
                LIMIT 50
            ");
            $params2 = [$this->tenantId];
            if ($sedeId) $params2[] = (int)$sedeId;
            $stm2->execute($params2);
            $this->viewData['asignaciones'] = $stm2->fetchAll(\PDO::FETCH_ASSOC);

            // Alumnos activos para select de asignación
            $sedeSQL2 = $sedeId ? ' AND a.alu_sede_id = ?' : '';
            $stm3 = $this->db->prepare("
                SELECT a.alu_alumno_id, a.alu_nombres, a.alu_apellidos
                FROM alumnos a
                WHERE a.alu_tenant_id = ? AND a.alu_estado = 'ACTIVO'{$sedeSQL2}
                ORDER BY a.alu_apellidos, a.alu_nombres
            ");
            $params3 = [$this->tenantId];
            if ($sedeId) $params3[] = (int)$sedeId;
            $stm3->execute($params3);
            $this->viewData['alumnos'] = $stm3->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Becas';
            $this->renderModule('futbol/becas/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando becas: " . $e->getMessage());
            $this->error('Error al cargar becas');
        }
    }

    /**
     * Crear nueva beca
     */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombre = trim($this->post('nombre') ?? '');
            $tipo = $this->post('tipo') ?: 'PORCENTAJE';
            $valor = (float)($this->post('valor') ?? 0);
            if (empty($nombre)) return $this->jsonResponse(['success' => false, 'message' => 'El nombre es obligatorio']);
            if (!in_array($tipo, ['PORCENTAJE', 'MONTO_FIJO'])) return $this->jsonResponse(['success' => false, 'message' => 'Tipo de beca inválido']);

            $stm = $this->db->prepare("
                INSERT INTO futbol_becas (fbe_tenant_id, fbe_nombre, fbe_tipo, fbe_valor, fbe_descripcion, fbe_activo)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stm->execute([
                $this->tenantId,
                $nombre,
                $tipo,
                $valor,
                $this->post('descripcion') ?: null,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Beca creada correctamente']);

        } catch (\Exception $e) {
            $this->logError("Error creando beca: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear beca']);
        }
    }

    /**
     * Editar beca existente
     */
    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $nombre = trim($this->post('nombre') ?? '');
            $tipo = $this->post('tipo') ?: 'PORCENTAJE';
            if (empty($nombre)) return $this->jsonResponse(['success' => false, 'message' => 'El nombre es obligatorio']);
            if (!in_array($tipo, ['PORCENTAJE', 'MONTO_FIJO'])) return $this->jsonResponse(['success' => false, 'message' => 'Tipo de beca inválido']);

            $stm = $this->db->prepare("
                UPDATE futbol_becas 
                SET fbe_nombre = ?, fbe_tipo = ?, fbe_valor = ?, fbe_descripcion = ?, fbe_updated_at = NOW()
                WHERE fbe_beca_id = ? AND fbe_tenant_id = ?
            ");
            $stm->execute([
                $nombre,
                $tipo,
                (float)($this->post('valor') ?? 0),
                $this->post('descripcion') ?: null,
                $id,
                $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Beca actualizada']);

        } catch (\Exception $e) {
            $this->logError("Error editando beca: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar beca']);
        }
    }

    /**
     * Eliminar (desactivar) beca
     */
    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE futbol_becas SET fbe_activo = 0, fbe_updated_at = NOW() WHERE fbe_beca_id = ? AND fbe_tenant_id = ?")
                ->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Beca desactivada']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando beca: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar beca']);
        }
    }

    /**
     * Asignar beca a un alumno
     */
    public function asignar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $becaId = (int)($this->post('beca_id') ?? 0);
            $alumnoId = (int)($this->post('alumno_id') ?? 0);
            if (!$becaId || !$alumnoId) return $this->jsonResponse(['success' => false, 'message' => 'Beca y alumno son obligatorios']);

            // Verificar que la beca exista y esté activa
            $stm = $this->db->prepare("SELECT fbe_beca_id FROM futbol_becas WHERE fbe_beca_id = ? AND fbe_tenant_id = ? AND fbe_activo = 1");
            $stm->execute([$becaId, $this->tenantId]);
            if (!$stm->fetchColumn()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Beca no encontrada o inactiva']);
            }

            // Verificar que no tenga ya esta beca activa
            $stmCheck = $this->db->prepare("SELECT COUNT(*) FROM futbol_beca_asignaciones WHERE fba_beca_id = ? AND fba_alumno_id = ? AND fba_tenant_id = ? AND fba_estado = 'ACTIVA'");
            $stmCheck->execute([$becaId, $alumnoId, $this->tenantId]);
            if ((int)$stmCheck->fetchColumn() > 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'El alumno ya tiene esta beca activa']);
            }

            // Insertar asignación
            $stm2 = $this->db->prepare("
                INSERT INTO futbol_beca_asignaciones (fba_tenant_id, fba_beca_id, fba_alumno_id,
                    fba_fecha_asignacion, fba_fecha_vencimiento, fba_motivo, fba_estado)
                VALUES (?, ?, ?, CURDATE(), ?, ?, 'ACTIVA')
            ");
            $stm2->execute([
                $this->tenantId,
                $becaId,
                $alumnoId,
                $this->post('fecha_fin') ?: null,
                $this->post('motivo') ?: null,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Beca asignada correctamente']);

        } catch (\Exception $e) {
            $this->logError("Error asignando beca: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al asignar beca']);
        }
    }

    /**
     * Revocar (finalizar) asignación de beca
     */
    public function revocar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $asignacionId = (int)($this->post('asignacion_id') ?? 0);
            if (!$asignacionId) return $this->jsonResponse(['success' => false, 'message' => 'ID de asignación requerido']);

            // Verificar que la asignación exista y esté activa
            $stm = $this->db->prepare("SELECT fba_asignacion_id FROM futbol_beca_asignaciones WHERE fba_asignacion_id = ? AND fba_tenant_id = ? AND fba_estado = 'ACTIVA'");
            $stm->execute([$asignacionId, $this->tenantId]);
            if (!$stm->fetchColumn()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Asignación no encontrada o ya finalizada']);
            }

            // Cambiar estado a FINALIZADA (valores válidos: ACTIVA/SUSPENDIDA/FINALIZADA)
            $this->db->prepare("
                UPDATE futbol_beca_asignaciones 
                SET fba_estado = 'REVOCADA'
                WHERE fba_asignacion_id = ? AND fba_tenant_id = ?
            ")->execute([$asignacionId, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Beca revocada (finalizada)']);

        } catch (\Exception $e) {
            $this->logError("Error revocando beca: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al revocar beca']);
        }
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
