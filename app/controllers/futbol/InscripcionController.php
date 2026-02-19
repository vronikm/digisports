<?php
/**
 * DigiSports Fútbol — Controlador de Inscripciones
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class InscripcionController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    public function index() {
        try {
            $this->setupModule();
            $estado = $this->get('estado') ?? '';
            $grupo  = $this->get('grupo') ?? '';
            $pagina = max(1, (int)($this->get('pagina') ?? 1));
            $porPagina = 25;

            $where = " WHERE i.fin_tenant_id = ?";
            $params = [$this->tenantId];
            if (!empty($estado)) { $where .= " AND i.fin_estado = ?"; $params[] = $estado; }
            if (!empty($grupo))  { $where .= " AND i.fin_grupo_id = ?"; $params[] = (int)$grupo; }

            $countSQL = "SELECT COUNT(*) FROM futbol_inscripciones i {$where}";
            $stm = $this->db->prepare($countSQL);
            $stm->execute($params);
            $total = (int)$stm->fetchColumn();
            $totalPaginas = max(1, ceil($total / $porPagina));
            $offset = ($pagina - 1) * $porPagina;

            $sql = "SELECT i.*, CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS alumno,
                           g.fgr_nombre AS grupo, g.fgr_color,
                           CONCAT(e.fen_nombres, ' ', e.fen_apellidos) AS entrenador
                    FROM futbol_inscripciones i
                    JOIN alumnos a ON i.fin_alumno_id = a.alu_alumno_id
                    JOIN futbol_grupos g ON i.fin_grupo_id = g.fgr_grupo_id
                    LEFT JOIN futbol_entrenadores e ON g.fgr_entrenador_id = e.fen_entrenador_id
                    {$where}
                    ORDER BY i.fin_fecha_inscripcion DESC
                    LIMIT {$porPagina} OFFSET {$offset}";
            $stm = $this->db->prepare($sql);
            $stm->execute($params);

            // Grupos para filtro
            $stmG = $this->db->prepare("SELECT fgr_grupo_id, fgr_nombre FROM futbol_grupos WHERE fgr_tenant_id = ? AND fgr_estado IN ('ABIERTO','EN_CURSO') ORDER BY fgr_nombre");
            $stmG->execute([$this->tenantId]);

            // Alumnos para select
            $stmA = $this->db->prepare("
                SELECT a.alu_alumno_id, CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS nombre
                FROM alumnos a
                JOIN futbol_ficha_alumno ffa ON ffa.ffa_alumno_id = a.alu_alumno_id AND ffa.ffa_tenant_id = a.alu_tenant_id
                WHERE a.alu_tenant_id = ? AND a.alu_estado = 'ACTIVO' AND ffa.ffa_activo = 1
                ORDER BY a.alu_apellidos
            ");
            $stmA->execute([$this->tenantId]);

            $this->viewData['inscripciones'] = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['grupos']        = $stmG->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['alumnos']       = $stmA->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['estadoFiltro']  = $estado;
            $this->viewData['grupoFiltro']   = $grupo;
            $this->viewData['pagina']        = $pagina;
            $this->viewData['totalPaginas']  = $totalPaginas;
            $this->viewData['total']         = $total;
            $this->viewData['csrf_token']    = \Security::generateCsrfToken();
            $this->viewData['title']         = 'Inscripciones';
            $this->renderModule('futbol/inscripciones/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando inscripciones: " . $e->getMessage());
            $this->error('Error al cargar inscripciones');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $alumnoId = (int)($this->post('alumno_id') ?? 0);
            $grupoId  = (int)($this->post('grupo_id') ?? 0);
            if (!$alumnoId || !$grupoId) return $this->jsonResponse(['success' => false, 'message' => 'Alumno y grupo son obligatorios']);

            // Verificar cupo
            $stm = $this->db->prepare("SELECT fgr_cupo_maximo, fgr_cupo_actual FROM futbol_grupos WHERE fgr_grupo_id = ? AND fgr_tenant_id = ?");
            $stm->execute([$grupoId, $this->tenantId]);
            $grupo = $stm->fetch(\PDO::FETCH_ASSOC);
            if (!$grupo) return $this->jsonResponse(['success' => false, 'message' => 'Grupo no encontrado']);
            if ($grupo['fgr_cupo_actual'] >= $grupo['fgr_cupo_maximo']) {
                return $this->jsonResponse(['success' => false, 'message' => 'El grupo está lleno. Cupo máximo alcanzado.']);
            }

            // Verificar duplicado
            $stm = $this->db->prepare("SELECT fin_inscripcion_id FROM futbol_inscripciones WHERE fin_alumno_id = ? AND fin_grupo_id = ? AND fin_tenant_id = ?");
            $stm->execute([$alumnoId, $grupoId, $this->tenantId]);
            if ($stm->fetchColumn()) return $this->jsonResponse(['success' => false, 'message' => 'El alumno ya está inscrito en este grupo']);

            $montoInscripcion = (float)($this->post('monto_inscripcion') ?? 0);

            $this->db->beginTransaction();

            $stm = $this->db->prepare("INSERT INTO futbol_inscripciones (fin_tenant_id, fin_alumno_id, fin_grupo_id, fin_fecha_inscripcion, fin_monto, fin_estado, fin_notas) VALUES (?,?,?,?,?,?,?)");
            $stm->execute([
                $this->tenantId, $alumnoId, $grupoId,
                date('Y-m-d'),
                $montoInscripcion,
                'ACTIVA',
                $this->post('notas') ?: null,
            ]);

            // Incrementar cupo actual
            $this->db->prepare("UPDATE futbol_grupos SET fgr_cupo_actual = fgr_cupo_actual + 1 WHERE fgr_grupo_id = ? AND fgr_tenant_id = ?")->execute([$grupoId, $this->tenantId]);

            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => 'Inscripción registrada']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error creando inscripción: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar inscripción']);
        }
    }

    public function cancelar() {
        try {
            $id = (int)($this->post('id') ?? $this->post('fin_id') ?? $this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->beginTransaction();

            // Obtener grupo para decrementar cupo
            $stm = $this->db->prepare("SELECT fin_grupo_id FROM futbol_inscripciones WHERE fin_inscripcion_id = ? AND fin_tenant_id = ? AND fin_estado = 'ACTIVA'");
            $stm->execute([$id, $this->tenantId]);
            $grupoId = $stm->fetchColumn();

            $this->db->prepare("UPDATE futbol_inscripciones SET fin_estado = 'CANCELADA' WHERE fin_inscripcion_id = ? AND fin_tenant_id = ?")->execute([$id, $this->tenantId]);

            if ($grupoId) {
                $this->db->prepare("UPDATE futbol_grupos SET fgr_cupo_actual = GREATEST(0, fgr_cupo_actual - 1) WHERE fgr_grupo_id = ? AND fgr_tenant_id = ?")->execute([$grupoId, $this->tenantId]);
            }

            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => 'Inscripción cancelada']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error cancelando inscripción: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al cancelar']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }

    /**
     * Editar inscripción existente (POST AJAX)
     */
    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('fin_id') ?? $this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            // Si solo viene fin_estado, es un cambio de estado
            $estado = $this->post('fin_estado');
            if ($estado && !$this->post('fin_alumno_id')) {
                if (!in_array($estado, ['ACTIVA', 'SUSPENDIDA', 'CANCELADA', 'COMPLETADA'])) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Estado inválido']);
                }

                $this->db->beginTransaction();

                // Obtener estado anterior para manejar cupo
                $stmOld = $this->db->prepare("SELECT fin_estado, fin_grupo_id FROM futbol_inscripciones WHERE fin_inscripcion_id = ? AND fin_tenant_id = ?");
                $stmOld->execute([$id, $this->tenantId]);
                $old = $stmOld->fetch(\PDO::FETCH_ASSOC);

                $this->db->prepare("UPDATE futbol_inscripciones SET fin_estado = ?, fin_updated_at = NOW() WHERE fin_inscripcion_id = ? AND fin_tenant_id = ?")
                    ->execute([$estado, $id, $this->tenantId]);

                // Manejar cupo
                if ($old) {
                    $wasActive = $old['fin_estado'] === 'ACTIVA';
                    $isActive  = $estado === 'ACTIVA';
                    if ($wasActive && !$isActive) {
                        $this->db->prepare("UPDATE futbol_grupos SET fgr_cupo_actual = GREATEST(0, fgr_cupo_actual - 1) WHERE fgr_grupo_id = ? AND fgr_tenant_id = ?")
                            ->execute([$old['fin_grupo_id'], $this->tenantId]);
                    } elseif (!$wasActive && $isActive) {
                        $this->db->prepare("UPDATE futbol_grupos SET fgr_cupo_actual = fgr_cupo_actual + 1 WHERE fgr_grupo_id = ? AND fgr_tenant_id = ?")
                            ->execute([$old['fin_grupo_id'], $this->tenantId]);
                    }
                }

                $this->db->commit();
                return $this->jsonResponse(['success' => true, 'message' => 'Estado actualizado']);
            }

            // Actualización completa
            $alumnoId = (int)($this->post('fin_alumno_id') ?? $this->post('alumno_id') ?? 0);
            $grupoId  = (int)($this->post('fin_grupo_id') ?? $this->post('grupo_id') ?? 0);
            if (!$alumnoId || !$grupoId) return $this->jsonResponse(['success' => false, 'message' => 'Alumno y grupo obligatorios']);

            $stm = $this->db->prepare("
                UPDATE futbol_inscripciones SET fin_alumno_id=?, fin_grupo_id=?, fin_fecha_inscripcion=?,
                    fin_monto=?, fin_estado=?, fin_notas=?, fin_updated_at=NOW()
                WHERE fin_inscripcion_id=? AND fin_tenant_id=?
            ");
            $stm->execute([
                $alumnoId, $grupoId,
                $this->post('fin_fecha_inscripcion') ?: date('Y-m-d'),
                (float)($this->post('fin_monto') ?? $this->post('monto_inscripcion') ?? 0),
                $this->post('fin_estado') ?: 'ACTIVA',
                $this->post('fin_notas') ?? $this->post('notas') ?? null,
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Inscripción actualizada']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error editando inscripción: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar inscripción']);
        }
    }

    /**
     * Eliminar inscripción (alias para cancelar)
     */
    public function eliminar() {
        return $this->cancelar();
    }

    /**
     * Buscar alumnos para Select2 AJAX
     */
    public function buscarAlumno() {
        try {
            $q = trim($this->get('q') ?? '');
            if (strlen($q) < 2) return $this->jsonResponse([]);

            $stm = $this->db->prepare("
                SELECT a.alu_alumno_id AS id, CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS text
                FROM alumnos a
                JOIN futbol_ficha_alumno ffa ON ffa.ffa_alumno_id = a.alu_alumno_id AND ffa.ffa_tenant_id = a.alu_tenant_id
                WHERE a.alu_tenant_id = ? AND a.alu_estado = 'ACTIVO' AND ffa.ffa_activo = 1
                  AND (a.alu_nombres LIKE ? OR a.alu_apellidos LIKE ? OR a.alu_identificacion LIKE ?)
                ORDER BY a.alu_apellidos, a.alu_nombres
                LIMIT 20
            ");
            $like = "%{$q}%";
            $stm->execute([$this->tenantId, $like, $like, $like]);

            $this->jsonResponse($stm->fetchAll(\PDO::FETCH_ASSOC));

        } catch (\Exception $e) {
            $this->logError("Error buscando alumno: " . $e->getMessage());
            $this->jsonResponse([]);
        }
    }
}
