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
require_once BASE_PATH . '/app/helpers/auditoria_helper.php';
use App\Controllers\Seguridad\DashboardController;

class RolController extends \App\Controllers\ModuleController {
    protected $moduloCodigo = 'SEGURIDAD';
    protected $moduloNombre = 'Seguridad';
    protected $moduloIcono = 'fas fa-shield-alt';
    protected $moduloColor = '#F59E0B';

        private $permisosDisponibles = [
            'dashboard' => ['label' => 'Dashboard', 'permisos' => ['ver']],
            'usuarios' => ['label' => 'Usuarios', 'permisos' => ['ver', 'crear', 'editar', 'eliminar']],
            'roles' => ['label' => 'Roles', 'permisos' => ['ver', 'crear', 'editar', 'eliminar']],
            'tenants' => ['label' => 'Tenants', 'permisos' => ['ver', 'crear', 'editar', 'eliminar', 'suspender']],
            'modulos' => ['label' => 'Módulos', 'permisos' => ['ver', 'crear', 'editar', 'eliminar', 'asignar']],
            'instalaciones' => ['label' => 'Instalaciones', 'permisos' => ['ver', 'crear', 'editar', 'eliminar']],
            'reservas' => ['label' => 'Reservas', 'permisos' => ['ver', 'crear', 'editar', 'eliminar', 'confirmar', 'cancelar']],
            'clientes' => ['label' => 'Clientes', 'permisos' => ['ver', 'crear', 'editar', 'eliminar']],
            'facturacion' => ['label' => 'Facturación', 'permisos' => ['ver', 'crear', 'anular', 'configurar_sri']],
            'reportes' => ['label' => 'Reportes', 'permisos' => ['ver', 'exportar']],
            'configuracion' => ['label' => 'Configuración', 'permisos' => ['ver', 'editar']],
            'auditoria' => ['label' => 'Auditoría', 'permisos' => ['ver', 'exportar']]
        ];

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
                $sql = "SELECT r.*, t.ten_nombre_comercial as tenant_nombre, (SELECT COUNT(*) FROM seguridad_usuarios WHERE usu_rol_id = r.rol_rol_id) as usuarios_count FROM seguridad_roles r LEFT JOIN seguridad_tenants t ON r.rol_tenant_id = t.ten_tenant_id $where ORDER BY r.rol_nivel_acceso DESC, r.rol_nombre ASC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $roles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $tenants = $this->db->query("SELECT ten_tenant_id, ten_nombre_comercial FROM seguridad_tenants WHERE ten_estado = 'A' ORDER BY ten_nombre_comercial")->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $roles = [];
                $tenants = [];
            }
            if (is_array($roles)) {
                foreach ($roles as &$rol) {
                    if (!isset($rol['es_sistema'])) $rol['es_sistema'] = 0;
                }
                unset($rol);
            }
            $this->renderModule('seguridad/rol/index', [
                'roles' => $roles,
                'tenants' => $tenants,
                'filtros' => ['tenant_id' => $tenantId],
                'pageTitle' => 'Gestión de Roles',
                'total' => is_array($roles) ? count($roles) : 0
            ]);
        }

        public function crear() {
            $this->authorize('crear', 'roles');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->guardar();
                return;
            }
            $tenants = $this->db->query("SELECT ten_tenant_id, ten_nombre_comercial FROM seguridad_tenants WHERE ten_estado = 'A' ORDER BY ten_nombre_comercial")->fetchAll(\PDO::FETCH_ASSOC);
            $this->renderModule('seguridad/rol/form', [
                'rol' => [],
                'tenants' => $tenants,
                'permisosDisponibles' => $this->permisosDisponibles,
                'pageTitle' => 'Nuevo Rol'
            ]);
        }

        public function editar() {
            $this->authorize('editar', 'roles');
            $id = $_GET['id'] ?? 0;
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->guardar($id);
                return;
            }
            $stmt = $this->db->prepare("SELECT * FROM seguridad_roles WHERE rol_rol_id = ?");
            $stmt->execute([$id]);
            $rol = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$rol) {
                setFlashMessage('error', 'Rol no encontrado');
                redirect('seguridad', 'rol', 'index');
                return;
            }
            $rol['permisos_array'] = json_decode($rol['rol_permisos'], true) ?: [];
            $tenants = $this->db->query("SELECT ten_tenant_id, ten_nombre_comercial FROM seguridad_tenants WHERE ten_estado = 'A' ORDER BY ten_nombre_comercial")->fetchAll(\PDO::FETCH_ASSOC);
            $this->renderModule('seguridad/rol/form', [
                'rol' => $rol,
                'tenants' => $tenants,
                'permisosDisponibles' => $this->permisosDisponibles,
                'pageTitle' => 'Editar Rol'
            ]);
        }

        private function guardar($id = null) {
            $this->authorize($id ? 'editar' : 'crear', 'roles');
            $permisos = [];
            if (isset($_POST['permisos']) && is_array($_POST['permisos'])) {
                foreach ($_POST['permisos'] as $modulo => $acciones) {
                    foreach ($acciones as $accion) $permisos[] = "$modulo.$accion";
                }
            }
            $data = [
                'rol_tenant_id' => !empty($_POST['tenant_id']) ? $_POST['tenant_id'] : null,
                'rol_codigo' => strtoupper($_POST['codigo']),
                'rol_nombre' => $_POST['nombre'],
                'rol_descripcion' => $_POST['descripcion'] ?? null,
                'rol_permisos' => json_encode($permisos),
                'rol_es_admin_tenant' => isset($_POST['es_admin_tenant']) ? 'S' : 'N',
                'rol_puede_modificar_permisos' => isset($_POST['puede_modificar_permisos']) ? 'S' : 'N',
                'rol_nivel_acceso' => $_POST['nivel_acceso'] ?? 1,
                'rol_estado' => $_POST['estado'] ?? 'A'
            ];
            try {
                if ($id) {
                    $stmtPrev = $this->db->prepare("SELECT * FROM seguridad_roles WHERE rol_rol_id = ?");
                    $stmtPrev->execute([$id]);
                    $datosAntes = $stmtPrev->fetch(\PDO::FETCH_ASSOC);
                    $sql = "UPDATE seguridad_roles SET rol_tenant_id = ?, rol_codigo = ?, rol_nombre = ?, rol_descripcion = ?, rol_permisos = ?, rol_es_admin_tenant = ?, rol_puede_modificar_permisos = ?, rol_nivel_acceso = ?, rol_estado = ? WHERE rol_rol_id = ?";
                    $params = array_values($data);
                    $params[] = $id;
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($params);
                    $stmtPost = $this->db->prepare("SELECT * FROM seguridad_roles WHERE rol_rol_id = ?");
                    $stmtPost->execute([$id]);
                    $datosDespues = $stmtPost->fetch(\PDO::FETCH_ASSOC);
                    if (function_exists('registrarAuditoria')) registrarAuditoria('editar_rol', 'rol', $id, $datosAntes, $datosDespues);
                    setFlashMessage('success', 'Rol actualizado correctamente');
                } else {
                    $sql = "INSERT INTO seguridad_roles (rol_tenant_id, rol_codigo, rol_nombre, rol_descripcion, rol_permisos, rol_es_admin_tenant, rol_puede_modificar_permisos, rol_nivel_acceso, rol_estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $params = array_values($data);
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($params);
                    $nuevoId = $this->db->lastInsertId();
                    if (function_exists('registrarAuditoria')) registrarAuditoria('crear_rol', 'rol', $nuevoId, null, $data);
                    setFlashMessage('success', 'Rol creado correctamente');
                }
                redirect('seguridad', 'rol', 'index');
            } catch (\Exception $e) {
                setFlashMessage('error', 'Error al guardar: ' . $e->getMessage());
                if ($id) redirect('seguridad', 'rol', 'editar', ['id' => $id]);
                else redirect('seguridad', 'rol', 'crear');
            }
        }

        public function eliminar() {
            $this->authorize('eliminar', 'roles');
            $id = $_GET['id'] ?? 0;
            try {
                $stmtPrev = $this->db->prepare("SELECT * FROM seguridad_roles WHERE rol_rol_id = ?");
                $stmtPrev->execute([$id]);
                $datosAntes = $stmtPrev->fetch(\PDO::FETCH_ASSOC);
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM seguridad_usuarios WHERE usu_rol_id = ?");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() > 0) {
                    if (function_exists('registrarAuditoria')) registrarAuditoria('eliminar_rol', 'rol', $id, $datosAntes, null, 'denegado', 'No se puede eliminar: hay usuarios con este rol');
                    setFlashMessage('error', 'No se puede eliminar: hay usuarios con este rol');
                    redirect('seguridad', 'rol', 'index');
                    return;
                }
                $stmt = $this->db->prepare("UPDATE seguridad_roles SET rol_estado = 'E' WHERE rol_rol_id = ?");
                $stmt->execute([$id]);
                if (function_exists('registrarAuditoria')) registrarAuditoria('eliminar_rol', 'rol', $id, $datosAntes, null);
                setFlashMessage('success', 'Rol eliminado correctamente');
            } catch (\Exception $e) {
                if (function_exists('registrarAuditoria')) registrarAuditoria('eliminar_rol', 'rol', $id, null, null, 'error', $e->getMessage());
                setFlashMessage('error', 'Error al eliminar rol');
            }
            redirect('seguridad', 'rol', 'index');
        }

        public function permisos() {
            $this->authorize('editar', 'roles');
            $id = $_GET['id'] ?? 0;
            $rol = [];
            $permisosActuales = [];
            try {
                $stmt = $this->db->prepare("SELECT * FROM seguridad_roles WHERE rol_rol_id = ?");
                $stmt->execute([$id]);
                $rol = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
                $permisosActuales = isset($rol['rol_permisos']) ? json_decode($rol['rol_permisos'], true) : [];
                if (!is_array($permisosActuales)) $permisosActuales = [];
            } catch (\Exception $e) {
                $rol = [];
                $permisosActuales = [];
            }

            // Cargar módulos con sus menús desde BD
            $modulosConMenus = $this->getModulosConMenus();

            // Cargar permisos de menú (seguridad_rol_menu) del rol
            $permisosMenu = [];
            if ($id) {
                try {
                    $stmt = $this->db->prepare("
                        SELECT srm.rme_menu_id, srm.rme_puede_ver, srm.rme_puede_acceder
                        FROM seguridad_rol_menu srm
                        WHERE srm.rme_rol_id = ?
                    ");
                    $stmt->execute([$id]);
                    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $pm) {
                        $permisosMenu[(int)$pm['rme_menu_id']] = [
                            'ver' => (int)$pm['rme_puede_ver'],
                            'acceder' => (int)$pm['rme_puede_acceder']
                        ];
                    }
                } catch (\Exception $e) {
                    $permisosMenu = [];
                }
            }

            $this->renderModule('seguridad/rol/permisos', [
                'rol' => $rol,
                'permisosDisponibles' => $this->permisosDisponibles,
                'permisosActuales' => $permisosActuales,
                'modulosConMenus' => $modulosConMenus,
                'permisosMenu' => $permisosMenu,
                'pageTitle' => 'Permisos de Rol'
            ]);
        }

        /**
         * Guardar permisos del rol (POST desde la Matriz de Permisos)
         */
        public function guardarPermisos() {
            $this->authorize('editar', 'roles');

            $rolId = (int)($_POST['rol_id'] ?? 0);
            if (!$rolId) {
                if (function_exists('setFlashMessage')) setFlashMessage('error', 'Rol no válido.');
                redirect('seguridad', 'rol', 'index');
                return;
            }

            try {
                $this->db->beginTransaction();

                // ── 1. Guardar permisos funcionales (JSON en seguridad_roles) ──
                $permisosFuncionales = [];
                if (isset($_POST['permisos']) && is_array($_POST['permisos'])) {
                    foreach ($_POST['permisos'] as $permiso) {
                        $permiso = trim($permiso);
                        if ($permiso) $permisosFuncionales[] = $permiso;
                    }
                }
                $permisosJson = json_encode(array_unique($permisosFuncionales));

                $stmt = $this->db->prepare("UPDATE seguridad_roles SET rol_permisos = ? WHERE rol_rol_id = ?");
                $stmt->execute([$permisosJson, $rolId]);

                // ── 2. Guardar permisos de menú (seguridad_rol_menu) ──
                // Eliminar todos los permisos de menú del rol
                $stmt = $this->db->prepare("DELETE FROM seguridad_rol_menu WHERE rme_rol_id = ?");
                $stmt->execute([$rolId]);

                // Insertar nuevos permisos de menú
                if (isset($_POST['menu_permisos']) && is_array($_POST['menu_permisos'])) {
                    $stmtInsert = $this->db->prepare("
                        INSERT INTO seguridad_rol_menu (rme_rol_id, rme_menu_id, rme_puede_ver, rme_puede_acceder)
                        VALUES (?, ?, ?, ?)
                    ");
                    foreach ($_POST['menu_permisos'] as $menuId => $perms) {
                        $puedeVer = isset($perms['ver']) ? 1 : 0;
                        $puedeAcceder = isset($perms['acceder']) ? 1 : 0;
                        if ($puedeVer || $puedeAcceder) {
                            $stmtInsert->execute([$rolId, (int)$menuId, $puedeVer, $puedeAcceder]);
                        }
                    }
                }

                $this->db->commit();

                // Auditoría
                if (function_exists('registrarAuditoria')) {
                    registrarAuditoria('editar_permisos_rol', 'rol', $rolId, null, [
                        'permisos_funcionales' => $permisosFuncionales,
                        'permisos_menu_count' => count($_POST['menu_permisos'] ?? [])
                    ]);
                }

                if (function_exists('setFlashMessage')) setFlashMessage('success', 'Permisos guardados correctamente.');
            } catch (\Exception $e) {
                $this->db->rollBack();
                error_log("RolController::guardarPermisos error: " . $e->getMessage());
                if (function_exists('setFlashMessage')) setFlashMessage('error', 'Error al guardar permisos: ' . $e->getMessage());
            }

            redirect('seguridad', 'rol', 'permisos', ['id' => $rolId]);
        }

        /**
         * Obtener módulos con sus menús organizados jerárquicamente
         */
        private function getModulosConMenus() {
            $resultado = [];
            try {
                // Módulos activos
                $modulos = $this->db->query("
                    SELECT mod_id, mod_codigo, mod_nombre, mod_icono, mod_color_fondo
                    FROM seguridad_modulos
                    WHERE mod_activo = 1
                    ORDER BY mod_orden
                ")->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($modulos as $mod) {
                    // Menús activos del módulo
                    $stmt = $this->db->prepare("
                        SELECT men_id, men_padre_id, men_tipo, men_label, men_icono,
                               men_ruta_controller, men_ruta_action, men_orden
                        FROM seguridad_menu
                        WHERE men_modulo_id = ? AND men_activo = 1
                        ORDER BY men_orden, men_id
                    ");
                    $stmt->execute([$mod['mod_id']]);
                    $menus = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                    $resultado[] = [
                        'modulo' => $mod,
                        'menus' => $menus
                    ];
                }
            } catch (\Exception $e) {
                error_log("RolController::getModulosConMenus error: " . $e->getMessage());
            }
            return $resultado;
        }

    }

