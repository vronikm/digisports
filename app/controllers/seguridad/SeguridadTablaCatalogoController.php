<?php
/**
 * DigiSports - Controlador: SeguridadTablaCatalogoController
 * Gestión de ítems de catálogos
 * 
 * @package DigiSports\Controllers\Seguridad
 */

namespace App\Controllers\Seguridad;

require_once BASE_PATH . '/app/controllers/ModuleController.php';
require_once BASE_PATH . '/app/helpers/functions.php';
require_once BASE_PATH . '/app/models/seguridad/SeguridadTablaCatalogoModel.php';
require_once BASE_PATH . '/app/models/seguridad/SeguridadTablaModel.php';

use App\Models\Seguridad\SeguridadTablaCatalogoModel;
use App\Models\Seguridad\SeguridadTablaModel;

class SeguridadTablaCatalogoController extends \App\Controllers\ModuleController {

    protected $modelItem;
    protected $modelGrupo;
    
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'SEGURIDAD';
        $this->moduloNombre = 'Seguridad';
        $this->moduloIcono  = 'fas fa-shield-alt';
        $this->moduloColor  = '#F59E0B';
        $this->modelItem = new SeguridadTablaCatalogoModel($this->db);
        $this->modelGrupo = new SeguridadTablaModel($this->db);
    }

    /**
     * Listar ítems por grupo (AJAX)
     */
    public function listar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                return $this->jsonResponse(['success' => false, 'message' => 'GET requerido']);
            }
            
            $grupoId = $this->get('grupoId');
            $filtro = $this->get('filtro');
            
            if (!$grupoId) {
                return $this->jsonResponse(['success' => false, 'message' => 'Grupo requerido']);
            }
            
            $items = $this->modelItem->listarPorGrupo($grupoId, $filtro);
            $grupo = $this->modelGrupo->obtener($grupoId);
            
            return $this->jsonResponse([
                'success' => true,
                'items' => $items,
                'grupo' => $grupo,
                'total' => count($items)
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'LIST_ERROR'
            ]);
        }
    }

    /**
     * Formulario para crear/editar ítem
     */
    public function editar() {
        try {
            $this->verificarAcceso('ADMIN');
            
            $grupoId = $this->get('grupoId');
            $itemId = $this->get('itemId');
            
            $grupo = $this->modelGrupo->obtener($grupoId);
            if (!$grupo) {
                $this->error('Catálogo no encontrado');
                return;
            }
            
            $item = null;
            if ($itemId) {
                $item = $this->modelItem->obtener($itemId);
                if (!$item) {
                    $this->error('Ítem no encontrado');
                    return;
                }
            }
            
            $this->viewData['grupo'] = $grupo;
            $this->viewData['item'] = $item;
            $this->viewData['title'] = $itemId ? 'Editar Ítem' : 'Crear Ítem';
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            
            $this->renderModule('seguridad/catalogos/item_editar', $this->viewData);
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Crear ítem (AJAX)
     */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            }
            
            $this->verificarAcceso('ADMIN');
            $this->verificarCsrfToken();
            
            $datos = [
                'stc_tabla_id' => $this->post('stc_tabla_id'),
                'stc_codigo' => $this->post('stc_codigo'),
                'stc_valor' => $this->post('stc_valor'),
                'stc_etiqueta' => $this->post('stc_etiqueta'),
                'stc_orden' => $this->post('stc_orden', 0),
                'stc_activo' => $this->post('stc_activo', 1)
            ];
            
            $resultado = $this->modelItem->crear($datos);
            return $this->jsonResponse($resultado);
            
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'EXCEPTION'
            ]);
        }
    }

    /**
     * Actualizar ítem (AJAX)
     */
    public function actualizar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            }
            
            $this->verificarAcceso('ADMIN');
            $this->verificarCsrfToken();
            
            $id = $this->post('stc_id');
            $datos = [
                'stc_tabla_id' => $this->post('stc_tabla_id'),
                'stc_codigo' => $this->post('stc_codigo'),
                'stc_valor' => $this->post('stc_valor'),
                'stc_etiqueta' => $this->post('stc_etiqueta'),
                'stc_orden' => $this->post('stc_orden'),
                'stc_activo' => $this->post('stc_activo', 1)
            ];
            
            $resultado = $this->modelItem->actualizar($id, $datos);
            return $this->jsonResponse($resultado);
            
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'EXCEPTION'
            ]);
        }
    }

    /**
     * Eliminar ítem (AJAX)
     */
    public function eliminar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            }
            
            $this->verificarAcceso('ADMIN');
            $this->verificarCsrfToken();
            
            $id = $this->post('stc_id');
            $resultado = $this->modelItem->eliminar($id);
            return $this->jsonResponse($resultado);
            
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'EXCEPTION'
            ]);
        }
    }

    /**
     * Verificar acceso por rol
     */
    private function verificarAcceso($rolRequerido) {
        if (!$this->usuarioTieneRol($rolRequerido)) {
            throw new \Exception("Acceso denegado");
        }
    }

    /**
     * Helper para verificar rol
     */
    private function usuarioTieneRol($rol) {
        return true; // Ajustar según tu sistema de roles
    }

    /**
     * Verificar CSRF token
     */
    private function verificarCsrfToken() {
        $token = $this->post('csrf_token');
        if (!\Security::validateCsrfToken($token)) {
            throw new \Exception("Token CSRF inválido");
        }
    }

    /**
     * Respuesta JSON
     */
    private function jsonResponse($data) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
