<?php
/**
 * DigiSports Fútbol — Controlador de Horarios
 * Gestión de la grilla semanal de horarios
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class HorarioController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    /**
     * Mostrar grilla semanal de horarios
     */
    public function index() {
        try {
            $this->setupModule();
            $sedeId = $_SESSION['futbol_sede_id'] ?? null;

            $sedeSQL = $sedeId ? ' AND fgr.fgr_sede_id = ?' : '';
            $stm = $this->db->prepare("
                SELECT fgh.*, 
                       fgr.fgr_nombre AS grupo_nombre, fgr.fgr_grupo_id,
                       fct.fct_nombre AS categoria_nombre, fct.fct_color AS categoria_color,
                       fen.fen_nombres AS entrenador_nombre, fen.fen_apellidos AS entrenador_apellido,
                       can.can_nombre AS cancha_nombre, can.can_cancha_id
                FROM futbol_grupo_horarios fgh
                JOIN futbol_grupos fgr ON fgh.fgh_grupo_id = fgr.fgr_grupo_id AND fgh.fgh_tenant_id = fgr.fgr_tenant_id
                LEFT JOIN futbol_categorias fct ON fgr.fgr_categoria_id = fct.fct_categoria_id
                LEFT JOIN futbol_entrenadores fen ON fgr.fgr_entrenador_id = fen.fen_entrenador_id
                LEFT JOIN instalaciones_canchas can ON fgr.fgr_cancha_id = can.can_cancha_id
                WHERE fgh.fgh_tenant_id = ? AND fgh.fgh_activo = 1{$sedeSQL}
                ORDER BY FIELD(fgh.fgh_dia_semana, 'LUN','MAR','MIE','JUE','VIE','SAB','DOM'), fgh.fgh_hora_inicio
            ");
            $params = [$this->tenantId];
            if ($sedeId) $params[] = (int)$sedeId;
            $stm->execute($params);
            $this->viewData['horarios'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

            // Grupos para formulario de creación
            $sedeSQL2 = $sedeId ? ' AND fgr_sede_id = ?' : '';
            $stmGrupos = $this->db->prepare("
                SELECT fgr_grupo_id, fgr_nombre 
                FROM futbol_grupos 
                WHERE fgr_tenant_id = ? AND fgr_estado IN ('ABIERTO','EN_CURSO'){$sedeSQL2}
                ORDER BY fgr_nombre
            ");
            $paramsGrupos = [$this->tenantId];
            if ($sedeId) $paramsGrupos[] = (int)$sedeId;
            $stmGrupos->execute($paramsGrupos);
            $this->viewData['grupos'] = $stmGrupos->fetchAll(\PDO::FETCH_ASSOC);

            // Canchas de fútbol
            $stmCanchas = $this->db->prepare("
                SELECT can_cancha_id, can_nombre 
                FROM instalaciones_canchas 
                WHERE can_tenant_id = ? AND can_tipo = 'futbol' AND can_estado = 'ACTIVO'
                ORDER BY can_nombre
            ");
            $stmCanchas->execute([$this->tenantId]);
            $this->viewData['canchas'] = $stmCanchas->fetchAll(\PDO::FETCH_ASSOC);

            // Sedes para filtro
            $stmSedes = $this->db->prepare("SELECT sed_sede_id, sed_nombre FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_nombre");
            $stmSedes->execute([$this->tenantId]);
            $this->viewData['sedes'] = $stmSedes->fetchAll(\PDO::FETCH_ASSOC);

            // Entrenadores para filtro
            $stmEnt = $this->db->prepare("SELECT fen_entrenador_id, fen_nombres, fen_apellidos FROM futbol_entrenadores WHERE fen_tenant_id = ? AND fen_activo = 1 ORDER BY fen_apellidos, fen_nombres");
            $stmEnt->execute([$this->tenantId]);
            $this->viewData['entrenadores'] = $stmEnt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Horarios';
            $this->viewData['sede_activa'] = $sedeId;
            $this->renderModule('futbol/horario/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error cargando horarios: " . $e->getMessage());
            $this->error('Error al cargar horarios');
        }
    }

    /**
     * Crear horario con validación de conflictos
     */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $grupoId = (int)($this->post('grupo_id') ?? 0);
            $dia = $this->post('dia_semana') ?: '';
            $horaInicio = $this->post('hora_inicio') ?: '';
            $horaFin = $this->post('hora_fin') ?: '';

            if (!$grupoId || empty($dia) || empty($horaInicio) || empty($horaFin)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Grupo, día y horario son obligatorios']);
            }
            if (!in_array($dia, ['LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB', 'DOM'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Día de semana inválido']);
            }
            if ($horaInicio >= $horaFin) {
                return $this->jsonResponse(['success' => false, 'message' => 'La hora de inicio debe ser anterior a la hora de fin']);
            }

            // Verificar conflicto de horarios en la misma cancha (a través del grupo)
            $stmCancha = $this->db->prepare("SELECT fgr_cancha_id FROM futbol_grupos WHERE fgr_grupo_id = ? AND fgr_tenant_id = ?");
            $stmCancha->execute([$grupoId, $this->tenantId]);
            $canchaId = $stmCancha->fetchColumn() ?: null;

            if ($canchaId) {
                $stmConflict = $this->db->prepare("
                    SELECT COUNT(*) FROM futbol_grupo_horarios fgh
                    JOIN futbol_grupos g ON fgh.fgh_grupo_id = g.fgr_grupo_id AND fgh.fgh_tenant_id = g.fgr_tenant_id
                    WHERE fgh.fgh_tenant_id = ? AND g.fgr_cancha_id = ? AND fgh.fgh_dia_semana = ? AND fgh.fgh_activo = 1
                      AND (
                          (fgh.fgh_hora_inicio < ? AND fgh.fgh_hora_fin > ?)
                          OR (fgh.fgh_hora_inicio < ? AND fgh.fgh_hora_fin > ?)
                          OR (fgh.fgh_hora_inicio >= ? AND fgh.fgh_hora_fin <= ?)
                      )
                ");
                $stmConflict->execute([
                    $this->tenantId, $canchaId, $dia,
                    $horaFin, $horaInicio,
                    $horaFin, $horaInicio,
                    $horaInicio, $horaFin,
                ]);
                if ((int)$stmConflict->fetchColumn() > 0) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Existe un conflicto de horario en esta cancha']);
                }
            }

            $notas = trim($this->post('notas') ?? '');

            $stm = $this->db->prepare("
                INSERT INTO futbol_grupo_horarios (fgh_tenant_id, fgh_grupo_id, fgh_dia_semana, fgh_hora_inicio, fgh_hora_fin, fgh_notas, fgh_activo)
                VALUES (?,?,?,?,?,?,1)
            ");
            $stm->execute([
                $this->tenantId,
                $grupoId,
                $dia,
                $horaInicio,
                $horaFin,
                $notas ?: null,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Horario creado correctamente']);

        } catch (\Exception $e) {
            $this->logError("Error creando horario: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear horario']);
        }
    }

    /**
     * Editar horario existente
     */
    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $grupoId = (int)($this->post('grupo_id') ?? 0);
            $dia = $this->post('dia_semana') ?: '';
            $horaInicio = $this->post('hora_inicio') ?: '';
            $horaFin = $this->post('hora_fin') ?: '';

            if (empty($dia) || empty($horaInicio) || empty($horaFin)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Día y horario son obligatorios']);
            }
            if ($horaInicio >= $horaFin) {
                return $this->jsonResponse(['success' => false, 'message' => 'La hora de inicio debe ser anterior a la hora de fin']);
            }

            // Verificar conflicto de horarios (excluyendo el registro actual)
            // Obtener cancha del grupo
            $effGrupo = $grupoId ?: null;
            if ($effGrupo) {
                $stmCancha = $this->db->prepare("SELECT fgr_cancha_id FROM futbol_grupos WHERE fgr_grupo_id = ? AND fgr_tenant_id = ?");
                $stmCancha->execute([$effGrupo, $this->tenantId]);
                $canchaId = $stmCancha->fetchColumn() ?: null;
            } else {
                $canchaId = null;
            }

            if ($canchaId) {
                $stmConflict = $this->db->prepare("
                    SELECT COUNT(*) FROM futbol_grupo_horarios fgh
                    JOIN futbol_grupos g ON fgh.fgh_grupo_id = g.fgr_grupo_id AND fgh.fgh_tenant_id = g.fgr_tenant_id
                    WHERE fgh.fgh_tenant_id = ? AND g.fgr_cancha_id = ? AND fgh.fgh_dia_semana = ? AND fgh.fgh_activo = 1
                      AND fgh.fgh_horario_id != ?
                      AND (
                          (fgh.fgh_hora_inicio < ? AND fgh.fgh_hora_fin > ?)
                          OR (fgh.fgh_hora_inicio < ? AND fgh.fgh_hora_fin > ?)
                          OR (fgh.fgh_hora_inicio >= ? AND fgh.fgh_hora_fin <= ?)
                      )
                ");
                $stmConflict->execute([
                    $this->tenantId, $canchaId, $dia, $id,
                    $horaFin, $horaInicio,
                    $horaFin, $horaInicio,
                    $horaInicio, $horaFin,
                ]);
                if ((int)$stmConflict->fetchColumn() > 0) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Existe un conflicto de horario en esta cancha']);
                }
            }

            $notas = trim($this->post('notas') ?? '');

            $stm = $this->db->prepare("
                UPDATE futbol_grupo_horarios 
                SET fgh_grupo_id=?, fgh_dia_semana=?, fgh_hora_inicio=?, fgh_hora_fin=?, fgh_notas=?
                WHERE fgh_horario_id=? AND fgh_tenant_id=?
            ");
            $stm->execute([
                $grupoId,
                $dia,
                $horaInicio,
                $horaFin,
                $notas ?: null,
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Horario actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando horario: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar horario']);
        }
    }

    /**
     * Eliminar horario (soft delete)
     */
    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE futbol_grupo_horarios SET fgh_activo = 0 WHERE fgh_horario_id = ? AND fgh_tenant_id = ?")
                ->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Horario eliminado']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando horario: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar horario']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
