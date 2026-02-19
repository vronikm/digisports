<?php
/**
 * DigiSports Natación — Controlador de Piscinas
 * CRUD completo para piscinas + carriles
 * 
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class PiscinaController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'NATACION';
    }

    public function index() {
        try {
            $this->setupModule();
            $sedeId = $_SESSION['natacion_sede_id'] ?? null;
            $sedeSQL = $sedeId ? ' AND p.npi_sede_id = ?' : '';

            $stm = $this->db->prepare("
                SELECT p.*, s.sed_nombre AS sede_nombre,
                       (SELECT COUNT(*) FROM natacion_carriles WHERE nca_piscina_id = p.npi_piscina_id AND nca_activo = 1) AS total_carriles,
                       (SELECT COUNT(DISTINCT g.ngr_grupo_id)
                        FROM natacion_grupos g
                        WHERE g.ngr_piscina_id = p.npi_piscina_id AND g.ngr_tenant_id = p.npi_tenant_id AND g.ngr_estado IN ('ABIERTO','EN_CURSO')) AS total_grupos
                FROM natacion_piscinas p
                LEFT JOIN instalaciones_sedes s ON p.npi_sede_id = s.sed_sede_id
                WHERE p.npi_tenant_id = ?{$sedeSQL} ORDER BY p.npi_nombre
            ");
            $params = [$this->tenantId]; if ($sedeId) $params[] = $sedeId;
            $stm->execute($params);

            // Sedes para select
            $sedesStm = $this->db->prepare("SELECT sed_sede_id, sed_nombre FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_nombre");
            $sedesStm->execute([$this->tenantId]);

            $this->viewData['piscinas']   = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['sedes']      = $sedesStm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['sede_activa'] = $sedeId;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title']      = 'Piscinas';
            $this->renderModule('natacion/piscinas/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando piscinas: " . $e->getMessage());
            $this->error('Error al cargar piscinas');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombre = trim($this->post('nombre') ?? '');
            if (empty($nombre)) return $this->jsonResponse(['success' => false, 'message' => 'El nombre es obligatorio']);

            $this->db->beginTransaction();

            $stm = $this->db->prepare("INSERT INTO natacion_piscinas (npi_tenant_id, npi_sede_id, npi_nombre, npi_tipo, npi_largo, npi_ancho, npi_profundidad_min, npi_profundidad_max, npi_num_carriles, npi_temperatura, npi_ubicacion, npi_notas) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $sedeIdPost = (int)($this->post('sede_id') ?? 0) ?: ($_SESSION['natacion_sede_id'] ?? null);
            $stm->execute([
                $this->tenantId, $sedeIdPost, $nombre,
                $this->post('tipo') ?: 'SEMI_OLIMPICA',
                $this->post('largo') ?: null,
                $this->post('ancho') ?: null,
                $this->post('profundidad_min') ?: null,
                $this->post('profundidad_max') ?: null,
                (int)($this->post('num_carriles') ?? 6),
                $this->post('temperatura') ?: null,
                $this->post('ubicacion') ?: null,
                $this->post('notas') ?: null,
            ]);
            $piscinaId = (int)$this->db->lastInsertId();

            // Crear carriles automáticamente
            $numCarriles = (int)($this->post('num_carriles') ?? 6);
            for ($i = 1; $i <= $numCarriles; $i++) {
                $this->db->prepare("INSERT INTO natacion_carriles (nca_tenant_id, nca_piscina_id, nca_numero) VALUES (?,?,?)")->execute([$this->tenantId, $piscinaId, $i]);
            }

            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => "Piscina creada con {$numCarriles} carriles"]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error creando piscina: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear piscina']);
        }
    }

    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            $nombre = trim($this->post('nombre') ?? '');
            if (!$id || empty($nombre)) return $this->jsonResponse(['success' => false, 'message' => 'ID y nombre requeridos']);

            $stm = $this->db->prepare("UPDATE natacion_piscinas SET npi_sede_id=?, npi_nombre=?, npi_tipo=?, npi_largo=?, npi_ancho=?, npi_profundidad_min=?, npi_profundidad_max=?, npi_num_carriles=?, npi_temperatura=?, npi_ubicacion=?, npi_activo=?, npi_notas=? WHERE npi_piscina_id=? AND npi_tenant_id=?");
            $stm->execute([
                (int)($this->post('sede_id') ?? 0) ?: null,
                $nombre,
                $this->post('tipo') ?: 'SEMI_OLIMPICA',
                $this->post('largo') ?: null, $this->post('ancho') ?: null,
                $this->post('profundidad_min') ?: null, $this->post('profundidad_max') ?: null,
                (int)($this->post('num_carriles') ?? 6),
                $this->post('temperatura') ?: null,
                $this->post('ubicacion') ?: null,
                (int)($this->post('activo') ?? 1),
                $this->post('notas') ?: null,
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Piscina actualizada']);

        } catch (\Exception $e) {
            $this->logError("Error editando piscina: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE natacion_piscinas SET npi_activo = 0 WHERE npi_piscina_id = ? AND npi_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Piscina desactivada']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando piscina: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
