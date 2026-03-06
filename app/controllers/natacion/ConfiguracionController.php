<?php
/**
 * DigiSports Natación — Controlador de Configuración (clave-valor por tenant)
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class ConfiguracionController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'NATACION'; }

    public function index() {
        try {
            $this->setupModule();

            $stm = $this->db->prepare("
                SELECT * FROM natacion_configuracion
                WHERE ncg_tenant_id = ?
                ORDER BY ncg_tipo, ncg_clave
            ");
            $stm->execute([$this->tenantId]);

            $configs = $stm->fetchAll(\PDO::FETCH_ASSOC);

            // Agrupar por tipo
            $agrupados = [];
            foreach ($configs as $c) {
                $cat = $c['ncg_tipo'] ?: 'TEXT';
                $agrupados[$cat][] = $c;
            }

            $this->viewData['configuraciones'] = $agrupados;
            $this->viewData['configs_planas']  = $configs;
            $this->viewData['csrf_token']      = \Security::generateCsrfToken();
            $this->viewData['title']           = 'Configuración del Módulo';
            $this->renderModule('natacion/configuracion/index', $this->viewData);

        } catch (\Exception $e) {
            $this->error('Error al cargar configuración');
        }
    }

    public function guardar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            }
            
            // Validar CSRF token
            $csrfToken = $this->post('csrf_token');
            if (!\Security::validateCsrfToken($csrfToken)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            // Obtener configuraciones
            $configs = $this->post('config');
            if (!is_array($configs) || empty($configs)) {
                return $this->jsonResponse(['success' => false, 'message' => 'No hay configuraciones para guardar']);
            }

            $this->beginTransaction();

            $stm = $this->db->prepare("
                UPDATE natacion_configuracion SET ncg_valor = ?
                WHERE ncg_config_id = ? AND ncg_tenant_id = ?
            ");

            $actualizados = 0;
            foreach ($configs as $id => $valor) {
                $id = (int)$id;
                if ($id <= 0) continue;
                
                $stm->execute([$valor, $id, $this->tenantId]);
                $actualizados += $stm->rowCount();
            }

            $this->commit();
            
            // Limpiar cualquier output anterior y enviar JSON
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            ob_start();
            header('Content-Type: application/json; charset=utf-8');
            $response = ['success' => true, 'message' => "Configuración guardada ($actualizados cambios)"];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            ob_end_flush();
            exit;

        } catch (\Exception $e) {
            $this->rollback();
            
            // Limpiar output y enviar JSON con error
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            ob_start();
            header('Content-Type: application/json; charset=utf-8');
            $response = ['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            ob_end_flush();
            exit;
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $clave = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($this->post('clave') ?? '')));
            if (empty($clave)) return $this->jsonResponse(['success' => false, 'message' => 'Clave obligatoria']);

            // Verificar duplicado
            $chk = $this->db->prepare("SELECT COUNT(*) FROM natacion_configuracion WHERE ncg_tenant_id = ? AND ncg_clave = ?");
            $chk->execute([$this->tenantId, $clave]);
            if ((int)$chk->fetchColumn() > 0) return $this->jsonResponse(['success' => false, 'message' => 'La clave ya existe']);

            $this->db->prepare("
                INSERT INTO natacion_configuracion (ncg_tenant_id, ncg_clave, ncg_valor, ncg_descripcion, ncg_tipo)
                VALUES (?,?,?,?,?)
            ")->execute([
                $this->tenantId, $clave,
                $this->post('valor') ?: '',
                $this->post('descripcion') ?: null,
                $this->post('tipo') ?: 'TEXT',
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Configuración creada']);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear']);
        }
    }

    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("DELETE FROM natacion_configuracion WHERE ncg_config_id = ? AND ncg_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Configuración eliminada']);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
