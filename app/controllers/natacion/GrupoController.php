<?php
/**
 * DigiSports Natación — Controlador de Grupos/Clases
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class GrupoController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'NATACION'; }

    public function index() {
        try {
            $this->setupModule();
            $filtroEstado = $this->get('estado') ?? '';
            $filtroPeriodo = $this->get('periodo') ?? '';
            $sedeId = $_SESSION['natacion_sede_id'] ?? null;

            $sql = "SELECT g.*, n.nnv_nombre AS nivel, n.nnv_color AS nivel_color,
                           p.npi_nombre AS piscina, s.sed_nombre AS sede_nombre,
                           CONCAT(i.nin_nombres, ' ', i.nin_apellidos) AS instructor,
                           pe.npe_nombre AS periodo
                    FROM natacion_grupos g
                    LEFT JOIN natacion_niveles n ON g.ngr_nivel_id = n.nnv_nivel_id
                    LEFT JOIN natacion_piscinas p ON g.ngr_piscina_id = p.npi_piscina_id
                    LEFT JOIN natacion_instructores i ON g.ngr_instructor_id = i.nin_instructor_id
                    LEFT JOIN natacion_periodos pe ON g.ngr_periodo_id = pe.npe_periodo_id
                    LEFT JOIN instalaciones_sedes s ON g.ngr_sede_id = s.sed_sede_id
                    WHERE g.ngr_tenant_id = ?";
            $params = [$this->tenantId];

            if ($sedeId) { $sql .= " AND g.ngr_sede_id = ?"; $params[] = $sedeId; }

            if (!empty($filtroEstado)) { $sql .= " AND g.ngr_estado = ?"; $params[] = $filtroEstado; }
            if (!empty($filtroPeriodo)) { $sql .= " AND g.ngr_periodo_id = ?"; $params[] = (int)$filtroPeriodo; }

            $sql .= " ORDER BY g.ngr_nombre";
            $stm = $this->db->prepare($sql);
            $stm->execute($params);

            // Datos para selects
            $niveles = $this->db->prepare("SELECT nnv_nivel_id, nnv_nombre FROM natacion_niveles WHERE nnv_tenant_id = ? AND nnv_activo = 1 ORDER BY nnv_orden");
            $niveles->execute([$this->tenantId]);
            $piscinas = $this->db->prepare("SELECT npi_piscina_id, npi_nombre FROM natacion_piscinas WHERE npi_tenant_id = ? AND npi_activo = 1 ORDER BY npi_nombre");
            $piscinas->execute([$this->tenantId]);
            $instructores = $this->db->prepare("SELECT nin_instructor_id, CONCAT(nin_nombres, ' ', nin_apellidos) AS nombre FROM natacion_instructores WHERE nin_tenant_id = ? AND nin_activo = 1 ORDER BY nin_apellidos");
            $instructores->execute([$this->tenantId]);
            $periodos = $this->db->prepare("SELECT npe_periodo_id, npe_nombre FROM natacion_periodos WHERE npe_tenant_id = ? ORDER BY npe_fecha_inicio DESC");
            $periodos->execute([$this->tenantId]);

            $this->viewData['grupos']        = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['niveles']       = $niveles->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['piscinas']      = $piscinas->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['instructores']  = $instructores->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['periodos']      = $periodos->fetchAll(\PDO::FETCH_ASSOC);
            // Sedes para select
            $sedesStm = $this->db->prepare("SELECT sed_sede_id, sed_nombre FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_nombre");
            $sedesStm->execute([$this->tenantId]);
            $this->viewData['sedes']         = $sedesStm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['sede_activa']   = $sedeId;
            $this->viewData['estadoFiltro']  = $filtroEstado;
            $this->viewData['periodoFiltro'] = $filtroPeriodo;
            $this->viewData['csrf_token']    = \Security::generateCsrfToken();
            $this->viewData['title']         = 'Grupos / Clases';
            $this->renderModule('natacion/grupos/index', $this->viewData);

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

            $stm = $this->db->prepare("INSERT INTO natacion_grupos (ngr_tenant_id, ngr_sede_id, ngr_periodo_id, ngr_nivel_id, ngr_piscina_id, ngr_instructor_id, ngr_nombre, ngr_descripcion, ngr_cupo_maximo, ngr_edad_min, ngr_edad_max, ngr_precio, ngr_color) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stm->execute([
                $this->tenantId,
                (int)($this->post('sede_id') ?? 0) ?: ($_SESSION['natacion_sede_id'] ?? null),
                $this->post('periodo_id') ?: null,
                $this->post('nivel_id') ?: null,
                $this->post('piscina_id') ?: null,
                $this->post('instructor_id') ?: null,
                $nombre,
                $this->post('descripcion') ?: null,
                (int)($this->post('cupo_maximo') ?? 10),
                $this->post('edad_min') ?: null,
                $this->post('edad_max') ?: null,
                (float)($this->post('precio') ?? 0),
                $this->post('color') ?: '#0EA5E9',
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

            $stm = $this->db->prepare("UPDATE natacion_grupos SET ngr_sede_id=?, ngr_periodo_id=?, ngr_nivel_id=?, ngr_piscina_id=?, ngr_instructor_id=?, ngr_nombre=?, ngr_descripcion=?, ngr_cupo_maximo=?, ngr_edad_min=?, ngr_edad_max=?, ngr_precio=?, ngr_estado=?, ngr_color=? WHERE ngr_grupo_id=? AND ngr_tenant_id=?");
            $stm->execute([
                (int)($this->post('sede_id') ?? 0) ?: null,
                $this->post('periodo_id') ?: null, $this->post('nivel_id') ?: null,
                $this->post('piscina_id') ?: null, $this->post('instructor_id') ?: null,
                trim($this->post('nombre') ?? ''), $this->post('descripcion') ?: null,
                (int)($this->post('cupo_maximo') ?? 10),
                $this->post('edad_min') ?: null, $this->post('edad_max') ?: null,
                (float)($this->post('precio') ?? 0),
                $this->post('estado') ?: 'ABIERTO',
                $this->post('color') ?: '#0EA5E9',
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

            $this->db->prepare("UPDATE natacion_grupos SET ngr_estado = 'CERRADO' WHERE ngr_grupo_id = ? AND ngr_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Grupo cerrado']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando grupo: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al cerrar grupo']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
