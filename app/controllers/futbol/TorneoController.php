<?php
/**
 * DigiSports Fútbol — Controlador de Torneos
 * Gestión de torneos y convocatoria de jugadores
 * 
 * Columnas reales:
 *   futbol_torneos: fto_torneo_id, fto_tenant_id, fto_nombre, fto_tipo, fto_sede_id,
 *     fto_fecha_inicio, fto_fecha_fin, fto_sede_torneo, fto_descripcion, fto_costo_inscripcion,
 *     fto_estado, fto_created_at, fto_updated_at
 *   futbol_torneo_jugadores: ftj_id, ftj_tenant_id, ftj_torneo_id, ftj_alumno_id,
 *     ftj_posicion, ftj_numero, ftj_es_capitan, ftj_estado(CONVOCADO|CONFIRMADO|BAJA), ftj_notas, ftj_created_at
 *   futbol_ficha_alumno: ffa_categoria_id existe; ffa_grupo_id NO existe (grupo via futbol_inscripciones)
 *   NO existen: fto_organizador, fto_categoria_id, fto_resultado
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class TorneoController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'FUTBOL';
    }

    /**
     * Listar torneos con total de jugadores
     */
    public function index() {
        try {
            $this->setupModule();

            $stm = $this->db->prepare("
                SELECT fto.*,
                       s.sed_nombre AS sede_nombre,
                       (SELECT COUNT(*) FROM futbol_torneo_jugadores ftj 
                        WHERE ftj.ftj_torneo_id = fto.fto_torneo_id 
                          AND ftj.ftj_tenant_id = fto.fto_tenant_id
                          AND ftj.ftj_estado IN ('CONVOCADO','CONFIRMADO')) AS total_jugadores
                FROM futbol_torneos fto
                LEFT JOIN instalaciones_sedes s ON fto.fto_sede_id = s.sed_sede_id
                WHERE fto.fto_tenant_id = ?
                ORDER BY fto.fto_fecha_inicio DESC
            ");
            $stm->execute([$this->tenantId]);
            $this->viewData['torneos'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

            // Sedes para formulario
            $stmSedes = $this->db->prepare("SELECT sed_sede_id, sed_nombre FROM instalaciones_sedes WHERE sed_tenant_id = ? ORDER BY sed_nombre");
            $stmSedes->execute([$this->tenantId]);
            $this->viewData['sedes'] = $stmSedes->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Torneos';
            $this->renderModule('futbol/torneos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando torneos: " . $e->getMessage());
            $this->error('Error al cargar torneos');
        }
    }

    /**
     * Crear nuevo torneo
     */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombre = trim($this->post('nombre') ?? '');
            if (empty($nombre)) return $this->jsonResponse(['success' => false, 'message' => 'El nombre es obligatorio']);

            $tipo = $this->post('tipo') ?: 'INTERNO';
            if (!in_array($tipo, ['INTERNO', 'EXTERNO', 'AMISTOSO', 'LIGA', 'COPA'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Tipo de torneo inválido']);
            }

            $stm = $this->db->prepare("
                INSERT INTO futbol_torneos (fto_tenant_id, fto_nombre, fto_tipo, fto_sede_id,
                    fto_fecha_inicio, fto_fecha_fin, fto_sede_torneo, fto_descripcion,
                    fto_costo_inscripcion, fto_estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PLANIFICADO')
            ");
            $stm->execute([
                $this->tenantId,
                $nombre,
                $tipo,
                (int)($this->post('sede_id') ?? 0) ?: null,
                $this->post('fecha_inicio') ?: null,
                $this->post('fecha_fin') ?: null,
                $this->post('lugar') ?: null,
                $this->post('descripcion') ?: null,
                (float)($this->post('presupuesto') ?? 0),
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Torneo creado correctamente']);

        } catch (\Exception $e) {
            $this->logError("Error creando torneo: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear torneo']);
        }
    }

    /**
     * Editar torneo existente
     */
    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $nombre = trim($this->post('nombre') ?? '');
            if (empty($nombre)) return $this->jsonResponse(['success' => false, 'message' => 'El nombre es obligatorio']);

            $tipo = $this->post('tipo') ?: 'INTERNO';
            $estado = $this->post('estado') ?: 'PLANIFICADO';
            if (!in_array($tipo, ['INTERNO', 'EXTERNO', 'AMISTOSO', 'LIGA', 'COPA'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Tipo de torneo inválido']);
            }
            if (!in_array($estado, ['PLANIFICADO', 'EN_CURSO', 'FINALIZADO', 'CANCELADO'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Estado de torneo inválido']);
            }

            $stm = $this->db->prepare("
                UPDATE futbol_torneos 
                SET fto_nombre = ?, fto_tipo = ?, fto_sede_id = ?,
                    fto_fecha_inicio = ?, fto_fecha_fin = ?, fto_sede_torneo = ?,
                    fto_descripcion = ?, fto_costo_inscripcion = ?, fto_estado = ?,
                    fto_updated_at = NOW()
                WHERE fto_torneo_id = ? AND fto_tenant_id = ?
            ");
            $stm->execute([
                $nombre,
                $tipo,
                (int)($this->post('sede_id') ?? 0) ?: null,
                $this->post('fecha_inicio') ?: null,
                $this->post('fecha_fin') ?: null,
                $this->post('lugar') ?: null,
                $this->post('descripcion') ?: null,
                (float)($this->post('presupuesto') ?? 0),
                $estado,
                $id,
                $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Torneo actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando torneo: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar torneo']);
        }
    }

    /**
     * Cancelar torneo
     */
    public function eliminar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE futbol_torneos SET fto_estado = 'CANCELADO', fto_updated_at = NOW() WHERE fto_torneo_id = ? AND fto_tenant_id = ?")
                ->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Torneo cancelado']);

        } catch (\Exception $e) {
            $this->logError("Error cancelando torneo: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al cancelar torneo']);
        }
    }

    /**
     * Ver convocatoria de jugadores para un torneo
     */
    public function convocatoria() {
        try {
            $this->setupModule();
            $torneoId = (int)($this->get('id') ?? 0);

            // Sin ID: mostrar selector de torneos
            if (!$torneoId) {
                $stm = $this->db->prepare("
                    SELECT fto.*,
                           s.sed_nombre AS sede_nombre,
                           (SELECT COUNT(*) FROM futbol_torneo_jugadores ftj
                            WHERE ftj.ftj_torneo_id = fto.fto_torneo_id
                              AND ftj.ftj_tenant_id = fto.fto_tenant_id
                              AND ftj.ftj_estado IN ('CONVOCADO','CONFIRMADO')) AS total_jugadores
                    FROM futbol_torneos fto
                    LEFT JOIN instalaciones_sedes s ON fto.fto_sede_id = s.sed_sede_id
                    WHERE fto.fto_tenant_id = ?
                    ORDER BY fto.fto_fecha_inicio DESC
                ");
                $stm->execute([$this->tenantId]);
                $this->viewData['torneos_selector'] = $stm->fetchAll(\PDO::FETCH_ASSOC);
                $this->viewData['torneo']            = null;
                $this->viewData['jugadores']         = [];
                $this->viewData['alumnos_disponibles'] = [];
                $this->viewData['csrf_token']        = \Security::generateCsrfToken();
                $this->viewData['title']             = 'Convocatorias';
                $this->renderModule('futbol/torneos/convocatoria', $this->viewData);
                return;
            }

            // Datos del torneo
            $stm = $this->db->prepare("
                SELECT fto.*, s.sed_nombre AS sede_nombre
                FROM futbol_torneos fto
                LEFT JOIN instalaciones_sedes s ON fto.fto_sede_id = s.sed_sede_id
                WHERE fto.fto_torneo_id = ? AND fto.fto_tenant_id = ?
            ");
            $stm->execute([$torneoId, $this->tenantId]);
            $torneo = $stm->fetch(\PDO::FETCH_ASSOC);
            if (!$torneo) { $this->error('Torneo no encontrado'); return; }
            $this->viewData['torneo'] = $torneo;

            // Jugadores convocados
            $stm2 = $this->db->prepare("
                SELECT ftj.*,
                       CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS alumno_nombre,
                       a.alu_alumno_id AS alumno_id,
                       fct.fct_nombre AS categoria_nombre,
                       fgr.fgr_nombre AS grupo_nombre
                FROM futbol_torneo_jugadores ftj
                JOIN alumnos a ON ftj.ftj_alumno_id = a.alu_alumno_id AND a.alu_tenant_id = ftj.ftj_tenant_id
                LEFT JOIN futbol_ficha_alumno ffa ON ffa.ffa_alumno_id = a.alu_alumno_id AND ffa.ffa_tenant_id = a.alu_tenant_id AND ffa.ffa_activo = 1
                LEFT JOIN futbol_categorias fct ON ffa.ffa_categoria_id = fct.fct_categoria_id
                LEFT JOIN futbol_inscripciones fi ON fi.fin_alumno_id = a.alu_alumno_id AND fi.fin_tenant_id = a.alu_tenant_id AND fi.fin_estado = 'ACTIVA'
                LEFT JOIN futbol_grupos fgr ON fi.fin_grupo_id = fgr.fgr_grupo_id
                WHERE ftj.ftj_torneo_id = ? AND ftj.ftj_tenant_id = ?
                ORDER BY ftj.ftj_numero, a.alu_apellidos
            ");
            $stm2->execute([$torneoId, $this->tenantId]);
            $this->viewData['jugadores'] = $stm2->fetchAll(\PDO::FETCH_ASSOC);

            // Alumnos disponibles (activos y no ya convocados en este torneo)
            $stm3 = $this->db->prepare("
                SELECT a.alu_alumno_id AS id,
                       CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS nombre
                FROM alumnos a
                WHERE a.alu_tenant_id = ? AND a.alu_estado = 'ACTIVO'
                  AND a.alu_alumno_id NOT IN (
                      SELECT ftj2.ftj_alumno_id FROM futbol_torneo_jugadores ftj2
                      WHERE ftj2.ftj_torneo_id = ? AND ftj2.ftj_estado IN ('CONVOCADO','CONFIRMADO')
                  )
                ORDER BY a.alu_apellidos, a.alu_nombres
            ");
            $stm3->execute([$this->tenantId, $torneoId]);
            $this->viewData['alumnos_disponibles'] = $stm3->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Convocatoria - ' . $torneo['fto_nombre'];
            $this->renderModule('futbol/torneos/convocatoria', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error cargando convocatoria: " . $e->getMessage());
            $this->error('Error al cargar convocatoria');
        }
    }

    /**
     * Agregar jugador a torneo
     */
    public function agregarJugador() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $torneoId = (int)($this->post('fto_torneo_id') ?? $this->post('torneo_id') ?? 0);
            $alumnoId = (int)($this->post('alumno_id') ?? 0);
            $ftjId    = (int)($this->post('ftj_id') ?? 0);

            // Modo edición
            if ($ftjId) {
                $posicion = $this->post('ftj_posicion') ?? $this->post('posicion') ?: null;
                $numero   = (int)($this->post('ftj_numero') ?? $this->post('dorsal') ?? 0) ?: null;
                $estado   = $this->post('ftj_estado') ?: 'CONVOCADO';
                $this->db->prepare("UPDATE futbol_torneo_jugadores SET ftj_posicion=?, ftj_numero=?, ftj_estado=? WHERE ftj_id=? AND ftj_tenant_id=?")
                    ->execute([$posicion, $numero, $estado, $ftjId, $this->tenantId]);
                return $this->jsonResponse(['success' => true, 'message' => 'Convocado actualizado']);
            }

            if (!$torneoId || !$alumnoId) return $this->jsonResponse(['success' => false, 'message' => 'Torneo y alumno son obligatorios']);

            // Verificar que no esté ya convocado
            $stmCheck = $this->db->prepare("SELECT COUNT(*) FROM futbol_torneo_jugadores WHERE ftj_torneo_id = ? AND ftj_alumno_id = ? AND ftj_tenant_id = ? AND ftj_estado IN ('CONVOCADO','CONFIRMADO')");
            $stmCheck->execute([$torneoId, $alumnoId, $this->tenantId]);
            if ((int)$stmCheck->fetchColumn() > 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'El jugador ya está convocado']);
            }

            $posicion = $this->post('ftj_posicion') ?? $this->post('posicion') ?: null;
            if ($posicion && !in_array($posicion, ['PORTERO', 'DEFENSA', 'MEDIOCAMPISTA', 'DELANTERO'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Posición inválida']);
            }

            $stm = $this->db->prepare("
                INSERT INTO futbol_torneo_jugadores (ftj_tenant_id, ftj_torneo_id, ftj_alumno_id,
                    ftj_posicion, ftj_numero, ftj_estado)
                VALUES (?, ?, ?, ?, ?, 'CONVOCADO')
            ");
            $stm->execute([
                $this->tenantId,
                $torneoId,
                $alumnoId,
                $posicion,
                (int)($this->post('ftj_numero') ?? $this->post('dorsal') ?? 0) ?: null,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Jugador agregado a la convocatoria']);

        } catch (\Exception $e) {
            $this->logError("Error agregando jugador: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al agregar jugador']);
        }
    }

    /**
     * Confirmar jugador (CONVOCADO → CONFIRMADO)
     */
    public function confirmarJugador() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE futbol_torneo_jugadores SET ftj_estado = 'CONFIRMADO' WHERE ftj_id = ? AND ftj_tenant_id = ? AND ftj_estado = 'CONVOCADO'")
                ->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Jugador confirmado']);

        } catch (\Exception $e) {
            $this->logError("Error confirmando jugador: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al confirmar jugador']);
        }
    }

    /**
     * Quitar jugador del torneo (marcar como DESCARTADO)
     */
    public function quitarJugador() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE futbol_torneo_jugadores SET ftj_estado = 'BAJA' WHERE ftj_id = ? AND ftj_tenant_id = ?")
                ->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Jugador dado de baja del torneo']);

        } catch (\Exception $e) {
            $this->logError("Error quitando jugador: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al dar de baja jugador']);
        }
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
