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
                WHERE nco_tenant_id = ?
                ORDER BY nco_categoria, nco_clave
            ");
            $stm->execute([$this->tenantId]);

            $configs = $stm->fetchAll(\PDO::FETCH_ASSOC);

            // Agrupar por categoría
            $agrupados = [];
            foreach ($configs as $c) {
                $cat = $c['nco_categoria'] ?: 'GENERAL';
                $agrupados[$cat][] = $c;
            }

            $this->viewData['configuraciones'] = $agrupados;
            $this->viewData['configs_planas']  = $configs;
            $this->viewData['csrf_token']      = \Security::generateCsrfToken();
            $this->viewData['title']           = 'Configuración del Módulo';
            $this->renderModule('natacion/configuracion/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando configuración: " . $e->getMessage());
            $this->error('Error al cargar configuración');
        }
    }

    public function guardar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $configs = $this->post('config');
            if (!is_array($configs)) return $this->jsonResponse(['success' => false, 'message' => 'Datos inválidos']);

            $this->beginTransaction();

            $stm = $this->db->prepare("
                UPDATE natacion_configuracion SET nco_valor = ?
                WHERE nco_config_id = ? AND nco_tenant_id = ?
            ");

            foreach ($configs as $id => $valor) {
                $stm->execute([$valor, (int)$id, $this->tenantId]);
            }

            $this->commit();
            return $this->jsonResponse(['success' => true, 'message' => 'Configuración guardada']);

        } catch (\Exception $e) {
            $this->rollback();
            $this->logError("Error guardando configuración: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al guardar']);
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $clave = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($this->post('clave') ?? '')));
            if (empty($clave)) return $this->jsonResponse(['success' => false, 'message' => 'Clave obligatoria']);

            // Verificar duplicado
            $chk = $this->db->prepare("SELECT COUNT(*) FROM natacion_configuracion WHERE nco_tenant_id = ? AND nco_clave = ?");
            $chk->execute([$this->tenantId, $clave]);
            if ((int)$chk->fetchColumn() > 0) return $this->jsonResponse(['success' => false, 'message' => 'La clave ya existe']);

            $this->db->prepare("
                INSERT INTO natacion_configuracion (nco_tenant_id, nco_clave, nco_valor, nco_descripcion, nco_categoria)
                VALUES (?,?,?,?,?)
            ")->execute([
                $this->tenantId, $clave,
                $this->post('valor') ?: '',
                $this->post('descripcion') ?: null,
                $this->post('categoria') ?: 'GENERAL',
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Configuración creada']);

        } catch (\Exception $e) {
            $this->logError("Error creando configuración: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear']);
        }
    }

    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("DELETE FROM natacion_configuracion WHERE nco_config_id = ? AND nco_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Configuración eliminada']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando configuración: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
