<?php
/**
 * DigiSports Fútbol — Controlador de Configuración
 * Gestión de parámetros de configuración del módulo
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class ConfiguracionController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    /**
     * Listar configuraciones del módulo
     */
    public function index() {
        try {
            $this->setupModule();

            $stm = $this->db->prepare("
                SELECT * FROM futbol_configuracion
                WHERE fcg_tenant_id = ?
                ORDER BY fcg_clave
            ");
            $stm->execute([$this->tenantId]);
            $rows = $stm->fetchAll(\PDO::FETCH_ASSOC);

            // Agrupar por prefijo de clave (parte antes del primer '_') o 'GENERAL'
            $agrupadas = [];
            foreach ($rows as $row) {
                $parts = explode('_', $row['fcg_clave'], 2);
                $grupo = strtoupper($parts[0] ?? 'GENERAL');
                $agrupadas[$grupo][] = $row;
            }
            $this->viewData['configuraciones'] = $agrupadas;

            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Configuración';
            $this->renderModule('futbol/configuracion/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error cargando configuración: " . $e->getMessage());
            $this->error('Error al cargar configuración');
        }
    }

    /**
     * Guardar configuraciones (lote)
     */
    public function guardar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $configs = $this->post('config');
            if (!is_array($configs) || empty($configs)) {
                return $this->jsonResponse(['success' => false, 'message' => 'No se recibieron configuraciones']);
            }

            $stm = $this->db->prepare("
                UPDATE futbol_configuracion 
                SET fcg_valor = ?
                WHERE fcg_config_id = ? AND fcg_tenant_id = ?
            ");

            $actualizados = 0;
            foreach ($configs as $configId => $valor) {
                $configId = (int)$configId;
                if (!$configId) continue;
                $stm->execute([$valor, $configId, $this->tenantId]);
                $actualizados += $stm->rowCount();
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => "Configuración guardada ({$actualizados} parámetros actualizados)",
                'actualizados' => $actualizados,
            ]);

        } catch (\Exception $e) {
            $this->logError("Error guardando configuración: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al guardar configuración']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
