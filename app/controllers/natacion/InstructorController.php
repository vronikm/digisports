<?php
/**
 * DigiSports Natación — Controlador de Instructores
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class InstructorController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'NATACION'; }

    public function index() {
        try {
            $this->setupModule();
            $buscar = trim($this->get('buscar') ?? '');
            $sedeId = $_SESSION['natacion_sede_id'] ?? null;
            $sedeSQL = $sedeId ? ' AND i.nin_sede_id = ?' : '';

            $sql = "SELECT i.*, s.sed_nombre AS sede_nombre,
                           (SELECT COUNT(*) FROM natacion_grupos g WHERE g.ngr_instructor_id = i.nin_instructor_id AND g.ngr_tenant_id = i.nin_tenant_id AND g.ngr_estado IN ('ABIERTO','EN_CURSO')) AS total_grupos
                    FROM natacion_instructores i
                    LEFT JOIN instalaciones_sedes s ON i.nin_sede_id = s.sed_sede_id
                    WHERE i.nin_tenant_id = ?{$sedeSQL}";
            $params = [$this->tenantId]; if ($sedeId) $params[] = $sedeId;

            if (!empty($buscar)) {
                $sql .= " AND (i.nin_nombres LIKE ? OR i.nin_apellidos LIKE ? OR i.nin_especialidad LIKE ?)";
                $like = "%{$buscar}%";
                $params = array_merge($params, [$like, $like, $like]);
            }
            $sql .= " ORDER BY i.nin_apellidos, i.nin_nombres";

            $stm = $this->db->prepare($sql);
            $stm->execute($params);

            $this->viewData['instructores'] = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['buscar']       = $buscar;
            // Sedes para select
            $sedesStm = $this->db->prepare("SELECT sed_sede_id, sed_nombre FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_nombre");
            $sedesStm->execute([$this->tenantId]);
            $this->viewData['sedes']        = $sedesStm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['sede_activa']  = $sedeId;
            $this->viewData['csrf_token']   = \Security::generateCsrfToken();
            $this->viewData['title']        = 'Instructores';
            $this->renderModule('natacion/instructores/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando instructores: " . $e->getMessage());
            $this->error('Error al cargar instructores');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombres = trim($this->post('nombres') ?? '');
            $apellidos = trim($this->post('apellidos') ?? '');
            if (empty($nombres) || empty($apellidos)) return $this->jsonResponse(['success' => false, 'message' => 'Nombre y apellido son obligatorios']);

            $stm = $this->db->prepare("INSERT INTO natacion_instructores (nin_tenant_id, nin_sede_id, nin_nombres, nin_apellidos, nin_identificacion, nin_email, nin_telefono, nin_especialidad, nin_certificaciones, nin_color, nin_notas) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stm->execute([
                $this->tenantId, (int)($this->post('sede_id') ?? 0) ?: ($_SESSION['natacion_sede_id'] ?? null), $nombres, $apellidos,
                $this->post('identificacion') ?: null,
                $this->post('email') ?: null,
                $this->post('telefono') ?: null,
                $this->post('especialidad') ?: null,
                $this->post('certificaciones') ?: null,
                $this->post('color') ?: '#3B82F6',
                $this->post('notas') ?: null,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Instructor registrado']);

        } catch (\Exception $e) {
            $this->logError("Error creando instructor: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar instructor']);
        }
    }

    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $stm = $this->db->prepare("UPDATE natacion_instructores SET nin_sede_id=?, nin_nombres=?, nin_apellidos=?, nin_identificacion=?, nin_email=?, nin_telefono=?, nin_especialidad=?, nin_certificaciones=?, nin_color=?, nin_activo=?, nin_notas=? WHERE nin_instructor_id=? AND nin_tenant_id=?");
            $stm->execute([
                (int)($this->post('sede_id') ?? 0) ?: null,
                trim($this->post('nombres') ?? ''), trim($this->post('apellidos') ?? ''),
                $this->post('identificacion') ?: null, $this->post('email') ?: null,
                $this->post('telefono') ?: null, $this->post('especialidad') ?: null,
                $this->post('certificaciones') ?: null,
                $this->post('color') ?: '#3B82F6',
                (int)($this->post('activo') ?? 1),
                $this->post('notas') ?: null,
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Instructor actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando instructor: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar instructor']);
        }
    }

    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE natacion_instructores SET nin_activo = 0 WHERE nin_instructor_id = ? AND nin_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Instructor desactivado']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando instructor: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
