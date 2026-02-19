<?php
/**
 * DigiSports Natación — Controlador de Campos de Ficha (parametrizables por tenant)
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class CampoFichaController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'NATACION'; }

    public function index() {
        try {
            $this->setupModule();

            $stm = $this->db->prepare("
                SELECT * FROM natacion_campos_ficha
                WHERE ncf_tenant_id = ?
                ORDER BY ncf_orden ASC, ncf_clave ASC
            ");
            $stm->execute([$this->tenantId]);

            $this->viewData['campos']     = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title']      = 'Campos de Ficha Personalizados';
            $this->viewData['tipos_campo'] = ['TEXT','NUMBER','DATE','SELECT','CHECKBOX','TEXTAREA'];
            $this->renderModule('natacion/campoficha/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando campos ficha: " . $e->getMessage());
            $this->error('Error al cargar campos');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $clave  = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($this->post('clave') ?? '')));
            $nombre = trim($this->post('nombre') ?? '');
            $tipo   = $this->post('tipo') ?: 'TEXT';
            if (empty($clave) || empty($nombre)) return $this->jsonResponse(['success' => false, 'message' => 'Clave y nombre son obligatorios']);

            // Verificar clave única por tenant
            $chk = $this->db->prepare("SELECT COUNT(*) FROM natacion_campos_ficha WHERE ncf_tenant_id = ? AND ncf_clave = ?");
            $chk->execute([$this->tenantId, $clave]);
            if ((int)$chk->fetchColumn() > 0) return $this->jsonResponse(['success' => false, 'message' => 'La clave ya existe']);

            // Obtener siguiente orden
            $ordStm = $this->db->prepare("SELECT COALESCE(MAX(ncf_orden),0)+1 FROM natacion_campos_ficha WHERE ncf_tenant_id = ?");
            $ordStm->execute([$this->tenantId]);
            $orden = (int)$ordStm->fetchColumn();

            $opciones = trim($this->post('opciones') ?? '');
            $opcionesJson = null;
            if ($tipo === 'SELECT' && !empty($opciones)) {
                $opcionesJson = json_encode(array_map('trim', explode(',', $opciones)), JSON_UNESCAPED_UNICODE);
            }

            $stm = $this->db->prepare("
                INSERT INTO natacion_campos_ficha (ncf_tenant_id, ncf_clave, ncf_etiqueta, ncf_tipo, ncf_requerido, ncf_opciones, ncf_orden, ncf_activo)
                VALUES (?,?,?,?,?,?,?,1)
            ");
            $stm->execute([
                $this->tenantId, $clave, $nombre, $tipo,
                $this->post('obligatorio') ? 1 : 0,
                $opcionesJson, $orden,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Campo creado']);

        } catch (\Exception $e) {
            $this->logError("Error creando campo ficha: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear campo']);
        }
    }

    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $tipo = $this->post('tipo') ?: 'TEXT';
            $opciones = trim($this->post('opciones') ?? '');
            $opcionesJson = null;
            if ($tipo === 'SELECT' && !empty($opciones)) {
                $opcionesJson = json_encode(array_map('trim', explode(',', $opciones)), JSON_UNESCAPED_UNICODE);
            }

            $stm = $this->db->prepare("
                UPDATE natacion_campos_ficha SET ncf_etiqueta=?, ncf_tipo=?, ncf_requerido=?, ncf_opciones=?, ncf_orden=?, ncf_activo=?
                WHERE ncf_campo_id=? AND ncf_tenant_id=?
            ");
            $stm->execute([
                trim($this->post('nombre') ?? ''), $tipo,
                $this->post('obligatorio') ? 1 : 0,
                $opcionesJson,
                (int)($this->post('orden') ?? 0),
                $this->post('activo') !== null ? (int)$this->post('activo') : 1,
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Campo actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando campo ficha: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            // Soft delete: desactivar
            $this->db->prepare("UPDATE natacion_campos_ficha SET ncf_activo = 0 WHERE ncf_campo_id = ? AND ncf_tenant_id = ?")
                ->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Campo desactivado']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando campo ficha: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
