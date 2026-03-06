<?php
/**
 * DigiSports - Controlador: SeguridadTablaController
 * Gestión de grupos de catálogos
 * 
 * @package DigiSports\Controllers\Seguridad
 */

namespace App\Controllers\Seguridad;

require_once BASE_PATH . '/app/controllers/ModuleController.php';
require_once BASE_PATH . '/app/helpers/functions.php';
require_once BASE_PATH . '/app/models/seguridad/SeguridadTablaModel.php';

use App\Models\Seguridad\SeguridadTablaModel;

class SeguridadTablaController extends \App\Controllers\ModuleController {

    protected $model;
    
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'SEGURIDAD';
        $this->moduloNombre = 'Seguridad';
        $this->moduloIcono  = 'fas fa-shield-alt';
        $this->moduloColor  = '#F59E0B';
        $this->model = new SeguridadTablaModel($this->db);
    }

    /**
     * Listar todos los grupos de catálogos
     */
    public function index() {
        try {
            $this->verificarAcceso('ADMIN');

            $filtro = $this->get('filtro');
            $catalogos = $this->model->listar($filtro);

            $this->viewData['catalogos'] = $catalogos;
            $this->viewData['title'] = 'Administración de Catálogos';
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['filtro'] = $filtro;

            $this->renderModule('seguridad/catalogos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Formulario para crear/editar
     */
    public function editar() {
        try {
            $this->verificarAcceso('ADMIN');

            $id = $this->get('id');
            $catalogo = null;

            if ($id) {
                $catalogo = $this->model->obtener($id);
                if (!$catalogo) {
                    $this->error('Catálogo no encontrado');
                    return;
                }
            }

            $this->viewData['catalogo'] = $catalogo;
            $this->viewData['title'] = $id ? 'Editar Catálogo' : 'Crear Catálogo';
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();

            $this->renderModule('seguridad/catalogos/editar', $this->viewData);
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Crear catálogo (AJAX)
     */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            }
            
            $this->verificarAcceso('ADMIN');
            $this->verificarCsrfToken();
            
            $datos = [
                'st_nombre' => $this->post('st_nombre'),
                'st_descripcion' => $this->post('st_descripcion'),
                'st_activo' => $this->post('st_activo', 1)
            ];
            
            $resultado = $this->model->crear($datos);
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
     * Actualizar catálogo (AJAX)
     */
    public function actualizar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            }
            
            $this->verificarAcceso('ADMIN');
            $this->verificarCsrfToken();
            
            $id = $this->post('st_id');
            $datos = [
                'st_nombre' => $this->post('st_nombre'),
                'st_descripcion' => $this->post('st_descripcion'),
                'st_activo' => $this->post('st_activo', 1)
            ];
            
            $resultado = $this->model->actualizar($id, $datos);
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
     * Eliminar catálogo (AJAX)
     */
    public function eliminar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            }
            
            $this->verificarAcceso('ADMIN');
            $this->verificarCsrfToken();
            
            $id = $this->post('st_id');
            $resultado = $this->model->eliminar($id);
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
     * Ver ítems del catálogo
     */
    public function items() {
        try {
            $this->verificarAcceso('ADMIN');
            
            $grupoId = $this->get('id');
            $grupo = $this->model->obtener($grupoId);
            
            if (!$grupo) {
                $this->error('Catálogo no encontrado');
                return;
            }
            
            $this->viewData['grupo'] = $grupo;
            $this->viewData['title'] = 'Ítems de ' . $grupo['st_nombre'];
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();

            $this->renderModule('seguridad/catalogos/items', $this->viewData);
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Verificar acceso por rol
     */
    private function verificarAcceso($rolRequerido) {
        // Asume que existe en BaseController o Session
        if (!$this->usuarioTieneRol($rolRequerido)) {
            throw new \Exception("Acceso denegado");
        }
    }

    /**
     * Helper para verificar rol (ajustar según tu implementación)
     */
    private function usuarioTieneRol($rol) {
        // Implementación según tu session management
        return true; // Por ahora permitir todo, ajustar según necesario
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
