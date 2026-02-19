<?php
/**
 * DigiSports Natación — Controlador de Horarios
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class HorarioController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';
    private $diasSemana = ['LUN' => 'Lunes', 'MAR' => 'Martes', 'MIE' => 'Miércoles', 'JUE' => 'Jueves', 'VIE' => 'Viernes', 'SAB' => 'Sábado', 'DOM' => 'Domingo'];

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'NATACION'; }

    public function index() {
        try {
            $this->setupModule();

            // Filtro por piscina
            $piscinaId = (int)($this->get('piscina_id') ?? 0);
            $where  = 'g.ngr_tenant_id = ?';
            $params = [$this->tenantId];
            if ($piscinaId) { $where .= ' AND g.ngr_piscina_id = ?'; $params[] = $piscinaId; }

            // Horarios con detalle de grupo
            $stm = $this->db->prepare("
                SELECT h.*, g.ngr_nombre AS grupo, g.ngr_color,
                       n.nnv_nombre AS nivel,
                       ins.nin_nombres AS instructor_nombre, ins.nin_apellidos AS instructor_apellido,
                       p.npi_nombre AS piscina
                FROM natacion_grupo_horarios h
                JOIN natacion_grupos g ON h.ngh_grupo_id = g.ngr_grupo_id
                LEFT JOIN natacion_niveles n ON g.ngr_nivel_id = n.nnv_nivel_id
                LEFT JOIN natacion_instructores ins ON g.ngr_instructor_id = ins.nin_instructor_id
                LEFT JOIN natacion_piscinas p ON g.ngr_piscina_id = p.npi_piscina_id
                WHERE {$where} AND g.ngr_estado IN ('ABIERTO','EN_CURSO')
                ORDER BY FIELD(h.ngh_dia_semana,'LUN','MAR','MIE','JUE','VIE','SAB','DOM'), h.ngh_hora_inicio
            ");
            $stm->execute($params);
            $horarios = $stm->fetchAll(\PDO::FETCH_ASSOC);

            // Agrupar por día
            $calendario = [];
            foreach ($this->diasSemana as $cod => $label) { $calendario[$cod] = ['label' => $label, 'items' => []]; }
            foreach ($horarios as $h) { $calendario[$h['ngh_dia_semana']]['items'][] = $h; }

            // Piscinas para filtro
            $pisStm = $this->db->prepare("SELECT npi_piscina_id, npi_nombre FROM natacion_piscinas WHERE npi_tenant_id = ? AND npi_activo = 1 ORDER BY npi_nombre");
            $pisStm->execute([$this->tenantId]);

            // Grupos para formulario
            $grpStm = $this->db->prepare("SELECT ngr_grupo_id, ngr_nombre FROM natacion_grupos WHERE ngr_tenant_id = ? AND ngr_estado IN ('ABIERTO','EN_CURSO') ORDER BY ngr_nombre");
            $grpStm->execute([$this->tenantId]);

            $this->viewData['calendario']    = $calendario;
            $this->viewData['piscinas']      = $pisStm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['grupos']        = $grpStm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['piscina_id']    = $piscinaId;
            $this->viewData['csrf_token']    = \Security::generateCsrfToken();
            $this->viewData['title']         = 'Horario Semanal';
            $this->viewData['diasSemana']    = $this->diasSemana;

            $this->renderModule('natacion/horario/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando horarios: " . $e->getMessage());
            $this->error('Error al cargar horarios');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $grupoId  = (int)($this->post('grupo_id') ?? 0);
            $dia      = $this->post('dia_semana') ?? '';
            $hInicio  = $this->post('hora_inicio') ?? '';
            $hFin     = $this->post('hora_fin') ?? '';
            if (!$grupoId || !$dia || !$hInicio || !$hFin) return $this->jsonResponse(['success' => false, 'message' => 'Todos los campos son obligatorios']);

            // Verificar conflicto
            $conf = $this->db->prepare("
                SELECT COUNT(*) FROM natacion_grupo_horarios h
                JOIN natacion_grupos g ON h.ngh_grupo_id = g.ngr_grupo_id
                WHERE g.ngr_piscina_id = (SELECT ngr_piscina_id FROM natacion_grupos WHERE ngr_grupo_id = ?)
                  AND h.ngh_dia_semana = ?
                  AND h.ngh_hora_inicio < ? AND h.ngh_hora_fin > ?
            ");
            $conf->execute([$grupoId, $dia, $hFin, $hInicio]);
            if ((int)$conf->fetchColumn() > 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'Conflicto de horario en la misma piscina']);
            }

            $this->db->prepare("INSERT INTO natacion_grupo_horarios (ngh_grupo_id, ngh_dia_semana, ngh_hora_inicio, ngh_hora_fin) VALUES (?,?,?,?)")
                ->execute([$grupoId, $dia, $hInicio, $hFin]);

            return $this->jsonResponse(['success' => true, 'message' => 'Horario creado']);

        } catch (\Exception $e) {
            $this->logError("Error creando horario: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear horario']);
        }
    }

    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            // Verificar pertenencia al tenant
            $this->db->prepare("
                DELETE h FROM natacion_grupo_horarios h
                JOIN natacion_grupos g ON h.ngh_grupo_id = g.ngr_grupo_id
                WHERE h.ngh_horario_id = ? AND g.ngr_tenant_id = ?
            ")->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Horario eliminado']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando horario: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
