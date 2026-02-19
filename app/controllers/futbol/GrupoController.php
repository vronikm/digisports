<?php
/**
 * DigiSports Fútbol — Controlador de Grupos/Entrenamientos
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class GrupoController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    public function index() {
        try {
            $this->setupModule();
            $filtroEstado = $this->get('estado') ?? '';
            $filtroPeriodo = $this->get('periodo') ?? '';
            $sedeId = $_SESSION['futbol_sede_id'] ?? null;

            $sql = "SELECT g.*, fct.fct_nombre AS categoria, fct.fct_color AS categoria_color,
                           can.can_nombre AS cancha, s.sed_nombre AS sede_nombre,
                           CONCAT(fen.fen_nombres, ' ', fen.fen_apellidos) AS entrenador,
                           fpe.fpe_nombre AS periodo
                    FROM futbol_grupos g
                    LEFT JOIN futbol_categorias fct ON g.fgr_categoria_id = fct.fct_categoria_id
                    LEFT JOIN instalaciones_canchas can ON g.fgr_cancha_id = can.can_cancha_id
                    LEFT JOIN futbol_entrenadores fen ON g.fgr_entrenador_id = fen.fen_entrenador_id
                    LEFT JOIN futbol_periodos fpe ON g.fgr_periodo_id = fpe.fpe_periodo_id
                    LEFT JOIN instalaciones_sedes s ON g.fgr_sede_id = s.sed_sede_id
                    WHERE g.fgr_tenant_id = ?";
            $params = [$this->tenantId];

            if ($sedeId) { $sql .= " AND g.fgr_sede_id = ?"; $params[] = $sedeId; }

            if (!empty($filtroEstado)) { $sql .= " AND g.fgr_estado = ?"; $params[] = $filtroEstado; }
            if (!empty($filtroPeriodo)) { $sql .= " AND g.fgr_periodo_id = ?"; $params[] = (int)$filtroPeriodo; }

            $sql .= " ORDER BY g.fgr_nombre";
            $stm = $this->db->prepare($sql);
            $stm->execute($params);

            // Datos para selects
            $categorias = $this->db->prepare("SELECT fct_categoria_id, fct_nombre FROM futbol_categorias WHERE fct_tenant_id = ? AND fct_activo = 1 ORDER BY fct_orden");
            $categorias->execute([$this->tenantId]);
            $canchas = $this->db->prepare("SELECT can_cancha_id, can_nombre FROM instalaciones_canchas WHERE can_tenant_id = ? AND can_tipo = 'futbol' AND can_estado = 'ACTIVO' ORDER BY can_nombre");
            $canchas->execute([$this->tenantId]);
            $entrenadores = $this->db->prepare("SELECT fen_entrenador_id, CONCAT(fen_nombres, ' ', fen_apellidos) AS nombre FROM futbol_entrenadores WHERE fen_tenant_id = ? AND fen_activo = 1 ORDER BY fen_apellidos");
            $entrenadores->execute([$this->tenantId]);
            $periodos = $this->db->prepare("SELECT fpe_periodo_id, fpe_nombre FROM futbol_periodos WHERE fpe_tenant_id = ? ORDER BY fpe_fecha_inicio DESC");
            $periodos->execute([$this->tenantId]);

            $this->viewData['grupos']         = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['categorias']     = $categorias->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['canchas']        = $canchas->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['entrenadores']   = $entrenadores->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['periodos']       = $periodos->fetchAll(\PDO::FETCH_ASSOC);
            // Sedes para select
            $sedesStm = $this->db->prepare("SELECT sed_sede_id, sed_nombre FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_nombre");
            $sedesStm->execute([$this->tenantId]);
            $this->viewData['sedes']          = $sedesStm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['sede_activa']    = $sedeId;
            $this->viewData['estadoFiltro']   = $filtroEstado;
            $this->viewData['periodoFiltro']  = $filtroPeriodo;
            $this->viewData['csrf_token']     = \Security::generateCsrfToken();
            $this->viewData['title']          = 'Grupos / Entrenamientos';
            $this->renderModule('futbol/grupos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando grupos: " . $e->getMessage());
            $this->error('Error al cargar grupos');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombre = trim($this->post('nombre') ?? '');
            if (empty($nombre)) return $this->jsonResponse(['success' => false, 'message' => 'El nombre es obligatorio']);

            $stm = $this->db->prepare("INSERT INTO futbol_grupos (fgr_tenant_id, fgr_sede_id, fgr_periodo_id, fgr_categoria_id, fgr_cancha_id, fgr_entrenador_id, fgr_nombre, fgr_descripcion, fgr_cupo_maximo, fgr_precio, fgr_color) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stm->execute([
                $this->tenantId,
                (int)($this->post('sede_id') ?? 0) ?: ($_SESSION['futbol_sede_id'] ?? null),
                $this->post('periodo_id') ?: null,
                $this->post('categoria_id') ?: null,
                $this->post('cancha_id') ?: null,
                $this->post('entrenador_id') ?: null,
                $nombre,
                $this->post('descripcion') ?: null,
                (int)($this->post('cupo_maximo') ?? 20),
                (float)($this->post('precio') ?? 0),
                $this->post('color') ?: '#22C55E',
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Grupo creado']);

        } catch (\Exception $e) {
            $this->logError("Error creando grupo: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear grupo']);
        }
    }

    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $stm = $this->db->prepare("UPDATE futbol_grupos SET fgr_sede_id=?, fgr_periodo_id=?, fgr_categoria_id=?, fgr_cancha_id=?, fgr_entrenador_id=?, fgr_nombre=?, fgr_descripcion=?, fgr_cupo_maximo=?, fgr_precio=?, fgr_estado=?, fgr_color=? WHERE fgr_grupo_id=? AND fgr_tenant_id=?");
            $stm->execute([
                (int)($this->post('sede_id') ?? 0) ?: null,
                $this->post('periodo_id') ?: null, $this->post('categoria_id') ?: null,
                $this->post('cancha_id') ?: null, $this->post('entrenador_id') ?: null,
                trim($this->post('nombre') ?? ''), $this->post('descripcion') ?: null,
                (int)($this->post('cupo_maximo') ?? 20),
                (float)($this->post('precio') ?? 0),
                $this->post('estado') ?: 'ABIERTO',
                $this->post('color') ?: '#22C55E',
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Grupo actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando grupo: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE futbol_grupos SET fgr_estado = 'CERRADO' WHERE fgr_grupo_id = ? AND fgr_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Grupo cerrado']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando grupo: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al cerrar grupo']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
