<?php
/**
 * DigiSports - Módulo Seguridad
 * Plan Controller
 * 
 * Gestión de planes de suscripción
 * 
 * @package DigiSports\Security
 * @version 1.0.0
 */

namespace App\Controllers\Seguridad;


// Incluir DashboardController para acceso a getMenuItems
require_once BASE_PATH . '/app/controllers/seguridad/DashboardController.php';

class PlanController extends \App\Controllers\ModuleController {
    // Métodos y propiedades válidos aquí

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'SEGURIDAD';
        $this->moduloNombre = 'Seguridad';
        $this->moduloIcono = 'fas fa-shield-alt';
        $this->moduloColor = '#F59E0B';
    }
    
    /**
     * Lista de planes
     */
    public function index() {
        try {
            $sql = "
                SELECT p.*,
                       (SELECT COUNT(*) FROM seguridad_tenants WHERE ten_plan_id = p.sus_plan_id AND ten_estado = 'A') as tenants_activos,
                       (SELECT SUM(ten_monto_mensual) FROM seguridad_tenants WHERE ten_plan_id = p.sus_plan_id AND ten_estado = 'A') as ingresos_mensuales
                FROM seguridad_planes_suscripcion p
                ORDER BY p.sus_orden_visualizacion ASC, p.sus_precio_mensual ASC
            ";
            $planes = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
            // Decodificar módulos incluidos
            foreach ($planes as &$plan) {
                $plan['modulos_array'] = !empty($plan['sus_modulos_incluidos']) ? json_decode($plan['sus_modulos_incluidos'], true) : [];
                $plan['caracteristicas_array'] = !empty($plan['sus_caracteristicas']) ? json_decode($plan['sus_caracteristicas'], true) : [];
            }
            // Obtener nombres de módulos
            $modulos = $this->db->query("SELECT mod_codigo, mod_nombre FROM seguridad_modulos WHERE mod_activo = 1")->fetchAll(\PDO::FETCH_KEY_PAIR);
            
        } catch (\Exception $e) {
            $planes = [];
            $modulos = [];
        }
        
        $this->renderModule('seguridad/plan/index', [
            'planes' => $planes,
            'modulos' => $modulos,
            'pageTitle' => 'Planes de Suscripción',
            'menu_items' => $this->getMenuItems(),
        ]);
    }
    
    /**
     * Crear plan
     */
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardar();
            return;
        }
        
        $modulos = $this->db->query("SELECT * FROM seguridad_modulos WHERE mod_activo = 1 ORDER BY mod_orden")->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->renderModule('seguridad/plan/form', [
            'plan' => null,
            'modulos' => $modulos,
            'pageTitle' => 'Nuevo Plan'
        ]);
    }
    
    /**
     * Editar plan
     */
    public function editar() {
        $id = $_GET['id'] ?? 0;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardar($id);
            return;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM seguridad_planes_suscripcion WHERE sus_plan_id = ?");
        $stmt->execute([$id]);
        $plan = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$plan) {
            setFlashMessage('error', 'Plan no encontrado');
            redirect('seguridad', 'plan', 'index');
            return;
        }
        
        $plan['modulos_array'] = json_decode($plan['sus_modulos_incluidos'], true) ?: [];
        $plan['caracteristicas_array'] = json_decode($plan['sus_caracteristicas'], true) ?: [];
        
        $modulos = $this->db->query("SELECT * FROM seguridad_modulos WHERE mod_activo = 1 ORDER BY mod_orden")->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->renderModule('seguridad/plan/form', [
            'plan' => $plan,
            'modulos' => $modulos,
            'pageTitle' => 'Editar Plan'
        ]);
    }
    
    /**
     * Actualizar plan (endpoint público para el form de edición)
     */
    public function actualizar() {
        $id = $_POST['plan_id'] ?? $_GET['id'] ?? 0;
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if (!$id) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'ID de plan no proporcionado']);
                return;
            }
            setFlashMessage('error', 'ID de plan no proporcionado');
            redirect('seguridad', 'plan', 'index');
            return;
        }
        $this->guardar($id);
    }

    /**
     * Guardar plan (soporta AJAX y redirect tradicional)
     */
    private function guardar($id = null) {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        $modulosIncluidos = isset($_POST['modulos']) ? $_POST['modulos'] : [];
        $caracteristicas = isset($_POST['caracteristicas']) ? array_filter($_POST['caracteristicas']) : [];
        
        $data = [
            'sus_codigo' => strtoupper($_POST['codigo'] ?? ''),
            'sus_nombre' => $_POST['nombre'] ?? '',
            'sus_descripcion' => $_POST['descripcion'] ?? null,
            'sus_precio_mensual' => $_POST['precio_mensual'] ?? 0,
            'sus_precio_anual' => $_POST['precio_anual'] ?? null,
            'sus_descuento_anual' => $_POST['descuento_anual'] ?? 0,
            'sus_usuarios_incluidos' => $_POST['usuarios_incluidos'] ?? 5,
            'sus_sedes_incluidas' => $_POST['sedes_incluidas'] ?? 1,
            'sus_almacenamiento_gb' => $_POST['almacenamiento_gb'] ?? 10,
            'sus_modulos_incluidos' => json_encode($modulosIncluidos),
            'sus_caracteristicas' => json_encode($caracteristicas),
            'sus_es_destacado' => isset($_POST['es_destacado']) ? 'S' : 'N',
            'sus_es_personalizado' => isset($_POST['es_personalizado']) ? 'S' : 'N',
            'sus_color' => $_POST['color'] ?? '#007bff',
            'sus_orden_visualizacion' => $_POST['orden_visualizacion'] ?? 0,
            'sus_estado' => $_POST['estado'] ?? 'A'
        ];
        
        try {
            if ($id) {
                $sql = "UPDATE seguridad_planes_suscripcion SET 
                    sus_codigo = ?, sus_nombre = ?, sus_descripcion = ?, sus_precio_mensual = ?, sus_precio_anual = ?,
                    sus_descuento_anual = ?, sus_usuarios_incluidos = ?, sus_sedes_incluidas = ?, sus_almacenamiento_gb = ?,
                    sus_modulos_incluidos = ?, sus_caracteristicas = ?, sus_es_destacado = ?, sus_es_personalizado = ?,
                    sus_color = ?, sus_orden_visualizacion = ?, sus_estado = ?
                    WHERE sus_plan_id = ?";
                $params = array_values($data);
                $params[] = $id;
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $msg = 'Plan actualizado correctamente';
            } else {
                $sql = "INSERT INTO seguridad_planes_suscripcion (sus_codigo, sus_nombre, sus_descripcion, sus_precio_mensual, sus_precio_anual, sus_descuento_anual, sus_usuarios_incluidos, sus_sedes_incluidas, sus_almacenamiento_gb, sus_modulos_incluidos, sus_caracteristicas, sus_es_destacado, sus_es_personalizado, sus_color, sus_orden_visualizacion, sus_estado)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = array_values($data);
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $msg = 'Plan creado correctamente';
            }
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $msg]);
                return;
            }
            
            setFlashMessage('success', $msg);
            redirect('seguridad', 'plan', 'index');
            
        } catch (\Exception $e) {
            $errorMsg = 'Error al guardar: ' . $e->getMessage();
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                return;
            }
            
            setFlashMessage('error', $errorMsg);
            redirect('seguridad', 'plan', $id ? 'editar&id=' . $id : 'crear');
        }
    }
    
    /**
     * Eliminar plan
     */
    public function eliminar() {
        $id = $_GET['id'] ?? 0;
        
        try {
            // Verificar que no hay tenants con este plan
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM seguridad_tenants WHERE ten_plan_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                setFlashMessage('error', 'No se puede eliminar: hay tenants con este plan');
                redirect('seguridad', 'plan', 'index');
                return;
            }
            
            $stmt = $this->db->prepare("DELETE FROM seguridad_planes_suscripcion WHERE sus_plan_id = ?");
            $stmt->execute([$id]);
            setFlashMessage('success', 'Plan eliminado correctamente');
        } catch (\Exception $e) {
            setFlashMessage('error', 'Error al eliminar plan');
        }
        
        redirect('seguridad', 'plan', 'index');
    }
    
    /**
     * Comparativa de planes
     */
    public function comparativa() {
        try {
            $planes = $this->db->query("SELECT * FROM seguridad_planes_suscripcion WHERE sus_estado = 'A' ORDER BY sus_precio_mensual")->fetchAll(\PDO::FETCH_ASSOC);
            $modulos = $this->db->query("SELECT * FROM seguridad_modulos WHERE mod_activo = 1 ORDER BY mod_orden")->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($planes as &$plan) {
                $plan['modulos_array'] = !empty($plan['sus_modulos_incluidos']) ? json_decode($plan['sus_modulos_incluidos'], true) : [];
            }
        } catch (\Exception $e) {
            $planes = [];
            $modulos = [];
        }
        
        $this->renderModule('seguridad/plan/comparativa', [
            'planes' => $planes,
            'modulos' => $modulos,
            'pageTitle' => 'Comparativa de Planes'
        ]);
    }
    
}

