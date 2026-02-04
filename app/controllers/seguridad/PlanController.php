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
        $this->moduloCodigo = 'seguridad';
        $this->moduloNombre = 'Seguridad';
        $this->moduloIcono = 'fas fa-shield-alt';
        $this->moduloColor = '#F59E0B';
    }
    
    protected $moduleName = 'Seguridad';
    protected $moduleIcon = 'fas fa-shield-alt';
    protected $moduleColor = '#6366F1';
    protected $moduleSlug = 'seguridad';
    
    /**
     * Lista de planes
     */
    public function index() {
        try {
            $sql = "
                SELECT p.*,
                       (SELECT COUNT(*) FROM tenants WHERE plan_id = p.plan_id AND estado = 'A') as tenants_activos,
                       (SELECT SUM(monto_mensual) FROM tenants WHERE plan_id = p.plan_id AND estado = 'A') as ingresos_mensuales
                FROM planes_suscripcion p
                ORDER BY p.orden_visualizacion ASC, p.precio_mensual ASC
            ";
            $planes = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
            
            // Decodificar módulos incluidos
            foreach ($planes as &$plan) {
                $plan['modulos_array'] = !empty($plan['modulos_incluidos']) ? json_decode($plan['modulos_incluidos'], true) : [];
                $plan['caracteristicas_array'] = !empty($plan['caracteristicas']) ? json_decode($plan['caracteristicas'], true) : [];
            }
            
            // Obtener nombres de módulos
            $modulos = $this->db->query("SELECT codigo, nombre FROM modulos_sistema WHERE estado = 'A'")->fetchAll(\PDO::FETCH_KEY_PAIR);
            
        } catch (\Exception $e) {
            $planes = [];
            $modulos = [];
        }
        
        $this->renderModule('seguridad/plan/index', [
            'planes' => $planes,
            'modulos' => $modulos,
            'pageTitle' => 'Planes de Suscripción',
            'modulo_actual' => [
                'codigo' => $this->moduleSlug ?? 'seguridad',
                'nombre' => $this->moduleName ?? 'Seguridad',
                'icono' => $this->moduleIcon ?? 'fas fa-shield-alt',
                'color' => $this->moduleColor ?? '#6366F1',
            ],
            'moduloNombre' => $this->moduleName ?? 'Seguridad',
            'moduloIcono' => $this->moduleIcon ?? 'fas fa-shield-alt',
            'moduloColor' => $this->moduleColor ?? '#6366F1',
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
        
        $modulos = $this->db->query("SELECT * FROM modulos_sistema WHERE estado = 'A' ORDER BY orden_visualizacion")->fetchAll(\PDO::FETCH_ASSOC);
        
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
        
        $stmt = $this->db->prepare("SELECT * FROM planes_suscripcion WHERE plan_id = ?");
        $stmt->execute([$id]);
        $plan = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$plan) {
            $this->setFlash('error', 'Plan no encontrado');
            $this->redirect('seguridad', 'plan', 'index');
            return;
        }
        
        $plan['modulos_array'] = json_decode($plan['modulos_incluidos'], true) ?: [];
        $plan['caracteristicas_array'] = json_decode($plan['caracteristicas'], true) ?: [];
        
        $modulos = $this->db->query("SELECT * FROM modulos_sistema WHERE estado = 'A' ORDER BY orden_visualizacion")->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->renderModule('seguridad/plan/form', [
            'plan' => $plan,
            'modulos' => $modulos,
            'pageTitle' => 'Editar Plan'
        ]);
    }
    
    /**
     * Guardar plan
     */
    private function guardar($id = null) {
        $modulosIncluidos = isset($_POST['modulos']) ? $_POST['modulos'] : [];
        $caracteristicas = isset($_POST['caracteristicas']) ? array_filter($_POST['caracteristicas']) : [];
        
        $data = [
            'codigo' => strtoupper($_POST['codigo']),
            'nombre' => $_POST['nombre'],
            'descripcion' => $_POST['descripcion'] ?? null,
            'precio_mensual' => $_POST['precio_mensual'],
            'precio_anual' => $_POST['precio_anual'] ?? null,
            'descuento_anual' => $_POST['descuento_anual'] ?? 0,
            'usuarios_incluidos' => $_POST['usuarios_incluidos'] ?? 5,
            'sedes_incluidas' => $_POST['sedes_incluidas'] ?? 1,
            'almacenamiento_gb' => $_POST['almacenamiento_gb'] ?? 10,
            'modulos_incluidos' => json_encode($modulosIncluidos),
            'caracteristicas' => json_encode($caracteristicas),
            'es_destacado' => isset($_POST['es_destacado']) ? 'S' : 'N',
            'es_personalizado' => isset($_POST['es_personalizado']) ? 'S' : 'N',
            'color' => $_POST['color'] ?? '#007bff',
            'orden_visualizacion' => $_POST['orden_visualizacion'] ?? 0,
            'estado' => $_POST['estado'] ?? 'A'
        ];
        
        try {
            if ($id) {
                $sql = "UPDATE planes_suscripcion SET 
                    codigo = ?, nombre = ?, descripcion = ?, precio_mensual = ?, precio_anual = ?,
                    descuento_anual = ?, usuarios_incluidos = ?, sedes_incluidas = ?, almacenamiento_gb = ?,
                    modulos_incluidos = ?, caracteristicas = ?, es_destacado = ?, es_personalizado = ?,
                    color = ?, orden_visualizacion = ?, estado = ?
                    WHERE plan_id = ?";
                $params = array_values($data);
                $params[] = $id;
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                setFlashMessage('success', 'Plan actualizado correctamente');
            } else {
                $sql = "INSERT INTO planes_suscripcion (codigo, nombre, descripcion, precio_mensual, precio_anual, descuento_anual, usuarios_incluidos, sedes_incluidas, almacenamiento_gb, modulos_incluidos, caracteristicas, es_destacado, es_personalizado, color, orden_visualizacion, estado)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = array_values($data);
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                setFlashMessage('success', 'Plan creado correctamente');
            }
            
            redirect('seguridad', 'plan', 'index');
            
        } catch (\Exception $e) {
            setFlashMessage('error', 'Error al guardar: ' . $e->getMessage());
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
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM tenants WHERE plan_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                setFlashMessage('error', 'No se puede eliminar: hay tenants con este plan');
                redirect('seguridad', 'plan', 'index');
                return;
            }
            
            $stmt = $this->db->prepare("DELETE FROM planes_suscripcion WHERE plan_id = ?");
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
            $planes = $this->db->query("SELECT * FROM planes_suscripcion WHERE estado = 'A' ORDER BY precio_mensual")->fetchAll(\PDO::FETCH_ASSOC);
            $modulos = $this->db->query("SELECT * FROM modulos_sistema WHERE estado = 'A' ORDER BY orden_visualizacion")->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($planes as &$plan) {
                $plan['modulos_array'] = !empty($plan['modulos_incluidos']) ? json_decode($plan['modulos_incluidos'], true) : [];
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
    
    /**
     * Obtener items del menú
     */
    protected function getMenuItems() {
        return (new \App\Controllers\Seguridad\DashboardController())->getMenuItems();
    }
}
