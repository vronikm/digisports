<?php
/**
 * DigiSports - Módulo Seguridad
 * Rol Controller
 * 
 * Gestión de roles y permisos del sistema
 * 
 * @package DigiSports\Security
 * @version 1.0.0
 */


namespace App\Controllers\Seguridad;

require_once BASE_PATH . '/app/controllers/ModuleController.php';
require_once BASE_PATH . '/app/controllers/seguridad/DashboardController.php';
use App\Controllers\Seguridad\DashboardController;

class RolController extends \App\Controllers\ModuleController {
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
    protected $moduloCodigo = 'seguridad';
    
    /**
     * Lista de permisos disponibles en el sistema
     */
    private $permisosDisponibles = [
        'dashboard' => [
            'label' => 'Dashboard',
            'permisos' => ['ver']
        ],
        'usuarios' => [
            'label' => 'Usuarios',
            'permisos' => ['ver', 'crear', 'editar', 'eliminar']
        ],
        'roles' => [
            'label' => 'Roles',
            'permisos' => ['ver', 'crear', 'editar', 'eliminar']
        ],
        'tenants' => [
            'label' => 'Tenants',
            'permisos' => ['ver', 'crear', 'editar', 'eliminar', 'suspender']
        ],
        'modulos' => [
            'label' => 'Módulos',
            'permisos' => ['ver', 'crear', 'editar', 'eliminar', 'asignar']
        ],
        'instalaciones' => [
            'label' => 'Instalaciones',
            'permisos' => ['ver', 'crear', 'editar', 'eliminar']
        ],
        'reservas' => [
            'label' => 'Reservas',
            'permisos' => ['ver', 'crear', 'editar', 'eliminar', 'confirmar', 'cancelar']
        ],
        'clientes' => [
            'label' => 'Clientes',
            'permisos' => ['ver', 'crear', 'editar', 'eliminar']
        ],
        'facturacion' => [
            'label' => 'Facturación',
            'permisos' => ['ver', 'crear', 'anular', 'configurar_sri']
        ],
        'reportes' => [
            'label' => 'Reportes',
            'permisos' => ['ver', 'exportar']
        ],
        'configuracion' => [
            'label' => 'Configuración',
            'permisos' => ['ver', 'editar']
        ],
        'auditoria' => [
            'label' => 'Auditoría',
            'permisos' => ['ver', 'exportar']
        ]
    ];
    
    /**
     * Lista de roles
     */
    public function index() {
        $this->authorize('ver', 'roles');
        $tenantId = $_GET['tenant_id'] ?? null;
        
        $where = "WHERE 1=1";
        $params = [];
        if ($tenantId) {
            $where .= " AND (r.rol_tenant_id = ? OR r.rol_tenant_id IS NULL)";
            $params[] = $tenantId;
        }
        try {
            $sql = "
                SELECT r.*, t.ten_nombre_comercial as tenant_nombre,
                       (SELECT COUNT(*) FROM seguridad_usuarios WHERE usr_rol_id = r.rol_id) as usuarios_count
                FROM seguridad_roles r
                LEFT JOIN core_tenants t ON r.rol_tenant_id = t.ten_id
                $where
                ORDER BY r.rol_nivel_acceso DESC, r.rol_nombre ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $roles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $tenants = $this->db->query("SELECT ten_id, ten_nombre_comercial FROM core_tenants WHERE ten_estado = 'A' ORDER BY ten_nombre_comercial")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $roles = [];
            $tenants = [];
        }
        
        // Asegurar que todos los roles tengan la clave 'es_sistema'
        if (is_array($roles)) {
            foreach ($roles as &$rol) {
                if (!isset($rol['es_sistema'])) {
                    $rol['es_sistema'] = 0;
                }
            }
            unset($rol);
        }
        $this->renderModule('rol/index', [
            'roles' => $roles,
            'tenants' => $tenants,
            'filtros' => ['tenant_id' => $tenantId],
            'pageTitle' => 'Gestión de Roles',
            'total' => is_array($roles) ? count($roles) : 0
        ]);
    }
    
    /**
     * Crear rol
     */
    public function crear() {
        $this->authorize('crear', 'roles');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardar();
            return;
        }
        
        $tenants = $this->db->query("SELECT ten_id, ten_nombre_comercial FROM core_tenants WHERE ten_estado = 'A' ORDER BY ten_nombre_comercial")->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->renderModule('rol/form', [
            'rol' => [],
            'tenants' => $tenants,
            'permisosDisponibles' => $this->permisosDisponibles,
            'pageTitle' => 'Nuevo Rol'
        ]);
    }
    
    /**
     * Editar rol
     */
    public function editar() {
        $this->authorize('editar', 'roles');
        $id = $_GET['id'] ?? 0;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardar($id);
            return;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE rol_id = ?");
        $stmt->execute([$id]);
        $rol = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$rol) {
            setFlashMessage('error', 'Rol no encontrado');
            redirect('seguridad', 'rol', 'index');
            return;
        }
        
        // Decodificar permisos
        $rol['permisos_array'] = json_decode($rol['permisos'], true) ?: [];
        
        $tenants = $this->db->query("SELECT tenant_id, nombre_comercial FROM tenants WHERE estado = 'A' ORDER BY nombre_comercial")->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->renderModule('rol/form', [
            'rol' => $rol,
            'tenants' => $tenants,
            'permisosDisponibles' => $this->permisosDisponibles,
            'pageTitle' => 'Editar Rol'
        ]);
    }
    
    /**
     * Guardar rol
     */
    private function guardar($id = null) {
        $this->authorize($id ? 'editar' : 'crear', 'roles');
        // Procesar permisos
        $permisos = [];
        if (isset($_POST['permisos']) && is_array($_POST['permisos'])) {
            foreach ($_POST['permisos'] as $modulo => $acciones) {
                foreach ($acciones as $accion) {
                    $permisos[] = "$modulo.$accion";
                }
            }
        }
        
        $data = [
            'tenant_id' => !empty($_POST['tenant_id']) ? $_POST['tenant_id'] : null,
            'codigo' => strtoupper($_POST['codigo']),
            'nombre' => $_POST['nombre'],
            'descripcion' => $_POST['descripcion'] ?? null,
            'permisos' => json_encode($permisos),
            'es_admin_tenant' => isset($_POST['es_admin_tenant']) ? 'S' : 'N',
            'puede_modificar_permisos' => isset($_POST['puede_modificar_permisos']) ? 'S' : 'N',
            'nivel_acceso' => $_POST['nivel_acceso'] ?? 1,
            'estado' => $_POST['estado'] ?? 'A'
        ];
        
        try {
            if ($id) {
                $stmtPrev = $this->db->prepare("SELECT * FROM roles WHERE rol_id = ?");
                $stmtPrev->execute([$id]);
                $datosAntes = $stmtPrev->fetch(\PDO::FETCH_ASSOC);
                $sql = "UPDATE roles SET 
                    tenant_id = ?, codigo = ?, nombre = ?, descripcion = ?,
                    permisos = ?, es_admin_tenant = ?, puede_modificar_permisos = ?,
                    nivel_acceso = ?, estado = ?
                    WHERE rol_id = ?";
                $params = array_values($data);
                $params[] = $id;
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $stmtPost = $this->db->prepare("SELECT * FROM roles WHERE rol_id = ?");
                $stmtPost->execute([$id]);
                $datosDespues = $stmtPost->fetch(\PDO::FETCH_ASSOC);
                if (function_exists('registrarAuditoria')) {
                    registrarAuditoria('editar_rol', 'rol', $id, $datosAntes, $datosDespues);
                }
                setFlashMessage('success', 'Rol actualizado correctamente');
            } else {
                $sql = "INSERT INTO roles (tenant_id, codigo, nombre, descripcion, permisos, es_admin_tenant, puede_modificar_permisos, nivel_acceso, estado)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = array_values($data);
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $nuevoId = $this->db->lastInsertId();
                if (function_exists('registrarAuditoria')) {
                    registrarAuditoria('crear_rol', 'rol', $nuevoId, null, $data);
                }
                setFlashMessage('success', 'Rol creado correctamente');
            }
            redirect('seguridad', 'rol', 'index');
        } catch (\Exception $e) {
            if (function_exists('registrarAuditoria')) {
                registrarAuditoria($id ? 'editar_rol' : 'crear_rol', 'rol', $id, null, null, 'error', $e->getMessage());
            }
            setFlashMessage('error', 'Error al guardar: ' . $e->getMessage());
            if ($id) {
                redirect('seguridad', 'rol', 'editar', ['id' => $id]);
            } else {
                redirect('seguridad', 'rol', 'crear');
            }
        }
        try {
            $stmtPrev = $this->db->prepare("SELECT * FROM roles WHERE rol_id = ?");
            $stmtPrev->execute([$id]);
            $datosAntes = $stmtPrev->fetch(\PDO::FETCH_ASSOC);
            // Verificar que no hay usuarios con este rol
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE rol_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                if (function_exists('registrarAuditoria')) {
                    registrarAuditoria('eliminar_rol', 'rol', $id, $datosAntes, null, 'denegado', 'No se puede eliminar: hay usuarios con este rol');
                }
                setFlashMessage('error', 'No se puede eliminar: hay usuarios con este rol');
                redirect('seguridad', 'rol', 'index');
                return;
            }
            $stmt = $this->db->prepare("UPDATE roles SET estado = 'E' WHERE rol_id = ?");
            $stmt->execute([$id]);
            if (function_exists('registrarAuditoria')) {
                registrarAuditoria('eliminar_rol', 'rol', $id, $datosAntes, null);
            }
            setFlashMessage('success', 'Rol eliminado correctamente');
        } catch (\Exception $e) {
            if (function_exists('registrarAuditoria')) {
                registrarAuditoria('eliminar_rol', 'rol', $id, null, null, 'error', $e->getMessage());
            }
            setFlashMessage('error', 'Error al eliminar rol');
        }
        redirect('seguridad', 'rol', 'index');
    }
    
    /**
     * Matriz de permisos
     */
    public function permisos() {
        $this->authorize('editar', 'roles');
        $id = $_GET['id'] ?? 0;
        $rol = [];
        $permisosActuales = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM roles WHERE rol_id = ?");
            $stmt->execute([$id]);
            $rol = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
            $permisosActuales = isset($rol['permisos']) ? json_decode($rol['permisos'], true) : [];
        } catch (\Exception $e) {
            $rol = [];
            $permisosActuales = [];
        }
        $this->renderModule('rol/permisos', [
            'rol' => $rol,
            'permisosDisponibles' => $this->permisosDisponibles,
            'permisosActuales' => $permisosActuales,
            'pageTitle' => 'Permisos de Rol'
        ]);
    }
    
    /**
     * Obtener items del menú
     */
    protected function getMenuItems() {
        return (new DashboardController())->getMenuItems();
    }
}
