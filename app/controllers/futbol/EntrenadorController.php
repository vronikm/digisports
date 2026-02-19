<?php
/**
 * DigiSports Fútbol — Controlador de Entrenadores
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class EntrenadorController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    public function index() {
        try {
            $this->setupModule();
            $buscar = trim($this->get('buscar') ?? '');
            $sedeId = $_SESSION['futbol_sede_id'] ?? null;
            $sedeSQL = $sedeId ? ' AND e.fen_sede_id = ?' : '';

            $sql = "SELECT e.*, s.sed_nombre AS sede_nombre,
                           (SELECT COUNT(*) FROM futbol_grupos g WHERE g.fgr_entrenador_id = e.fen_entrenador_id AND g.fgr_tenant_id = e.fen_tenant_id AND g.fgr_estado IN ('ABIERTO','EN_CURSO')) AS total_grupos
                    FROM futbol_entrenadores e
                    LEFT JOIN instalaciones_sedes s ON e.fen_sede_id = s.sed_sede_id
                    WHERE e.fen_tenant_id = ?{$sedeSQL}";
            $params = [$this->tenantId]; if ($sedeId) $params[] = $sedeId;

            if (!empty($buscar)) {
                $sql .= " AND (e.fen_nombres LIKE ? OR e.fen_apellidos LIKE ? OR e.fen_especialidad LIKE ? OR e.fen_rol LIKE ?)";
                $like = "%{$buscar}%";
                $params = array_merge($params, [$like, $like, $like, $like]);
            }
            $sql .= " ORDER BY e.fen_apellidos, e.fen_nombres";

            $stm = $this->db->prepare($sql);
            $stm->execute($params);

            $this->viewData['entrenadores'] = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['buscar']       = $buscar;
            // Sedes para select
            $sedesStm = $this->db->prepare("SELECT sed_sede_id, sed_nombre FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_nombre");
            $sedesStm->execute([$this->tenantId]);
            $this->viewData['sedes']        = $sedesStm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['sede_activa']  = $sedeId;
            $this->viewData['csrf_token']   = \Security::generateCsrfToken();
            $this->viewData['title']        = 'Entrenadores';
            $this->renderModule('futbol/entrenadores/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando entrenadores: " . $e->getMessage());
            $this->error('Error al cargar entrenadores');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombres = trim($this->post('nombres') ?? '');
            $apellidos = trim($this->post('apellidos') ?? '');
            if (empty($nombres) || empty($apellidos)) return $this->jsonResponse(['success' => false, 'message' => 'Nombre y apellido son obligatorios']);

            $stm = $this->db->prepare("INSERT INTO futbol_entrenadores (fen_tenant_id, fen_sede_id, fen_nombres, fen_apellidos, fen_identificacion, fen_email, fen_telefono, fen_rol, fen_especialidad, fen_certificaciones, fen_color, fen_notas) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stm->execute([
                $this->tenantId,
                (int)($this->post('sede_id') ?? 0) ?: ($_SESSION['futbol_sede_id'] ?? null),
                $nombres, $apellidos,
                $this->post('identificacion') ?: null,
                $this->post('email') ?: null,
                $this->post('telefono') ?: null,
                $this->post('rol') ?: 'ENTRENADOR',
                $this->post('especialidad') ?: null,
                $this->post('certificaciones') ?: null,
                $this->post('color') ?: '#22C55E',
                $this->post('notas') ?: null,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Entrenador registrado']);

        } catch (\Exception $e) {
            $this->logError("Error creando entrenador: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar entrenador']);
        }
    }

    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $stm = $this->db->prepare("UPDATE futbol_entrenadores SET fen_sede_id=?, fen_nombres=?, fen_apellidos=?, fen_identificacion=?, fen_email=?, fen_telefono=?, fen_rol=?, fen_especialidad=?, fen_certificaciones=?, fen_color=?, fen_activo=?, fen_notas=? WHERE fen_entrenador_id=? AND fen_tenant_id=?");
            $stm->execute([
                (int)($this->post('sede_id') ?? 0) ?: null,
                trim($this->post('nombres') ?? ''), trim($this->post('apellidos') ?? ''),
                $this->post('identificacion') ?: null, $this->post('email') ?: null,
                $this->post('telefono') ?: null,
                $this->post('rol') ?: 'ENTRENADOR',
                $this->post('especialidad') ?: null,
                $this->post('certificaciones') ?: null,
                $this->post('color') ?: '#22C55E',
                (int)($this->post('activo') ?? 1),
                $this->post('notas') ?: null,
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Entrenador actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando entrenador: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar entrenador']);
        }
    }

    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE futbol_entrenadores SET fen_activo = 0 WHERE fen_entrenador_id = ? AND fen_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Entrenador desactivado']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando entrenador: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
