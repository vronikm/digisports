<?php
/**
 * DigiSports - Módulo Seguridad
 * Usuario Controller
 * 
 * Gestión de usuarios del sistema
 * 
 * @package DigiSports\Security
 * @version 1.0.0
 */


namespace App\Controllers\Seguridad;

require_once BASE_PATH . '/app/controllers/ModuleController.php';
require_once BASE_PATH . '/app/controllers/seguridad/DashboardController.php';

class UsuarioController extends \App\Controllers\ModuleController {
    protected $moduloCodigo = 'seguridad';
    protected $moduloNombre = 'Seguridad';
    protected $moduloIcono = 'fas fa-shield-alt';
    protected $moduloColor = '#F59E0B';

    /**
     * Registrar acción en auditoría
     */
    private function registrarAuditoria($accion, $entidad, $entidadId, $datosAntes = null, $datosDespues = null, $resultado = 'exito', $mensaje = '') {
        $usuarioId = $_SESSION['usr_id'] ?? null;
        $tenantId = $_SESSION['ten_id'] ?? null;
        $sql = "INSERT INTO seguridad_auditoria (aud_tenant_id, aud_usuario_id, aud_modulo, aud_tabla, aud_registro_id, aud_operacion, aud_valores_anteriores, aud_valores_nuevos, aud_ip, aud_url, aud_metodo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $tenantId,
            $usuarioId,
            'seguridad',
            $entidad,
            $entidadId,
            $accion,
            $datosAntes ? json_encode($datosAntes) : null,
            $datosDespues ? json_encode($datosDespues) : null,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['REQUEST_URI'] ?? '',
            $_SERVER['REQUEST_METHOD'] ?? 'POST'
        ];
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } catch (\Exception $e) {
            // Si falla la auditoría, no interrumpir el flujo principal
        }
    }

    /**
     * Lista de usuarios
     */
    public function index() {
        $this->authorize('ver', 'usuarios');
        $tenantId = $_GET['tenant_id'] ?? null;
        $estado = $_GET['estado'] ?? 'A';
        $buscar = $_GET['buscar'] ?? '';
        $pagina = $_GET['pagina'] ?? 1;
        $porPagina = 20;
        $offset = ($pagina - 1) * $porPagina;

        $where = "WHERE 1=1";
        $params = [];
        if ($tenantId) {
            $where .= " AND u.usu_tenant_id = ?";
            $params[] = $tenantId;
        }
        if ($estado) {
            $where .= " AND u.usu_estado = ?";
            $params[] = $estado;
        }
        if ($buscar) {
            $where .= " AND (u.usu_nombres LIKE ? OR u.usu_apellidos LIKE ? OR u.usu_email LIKE ? OR u.usu_username LIKE ?)";
            $buscarLike = "%$buscar%";
            $params = array_merge($params, [$buscarLike, $buscarLike, $buscarLike, $buscarLike]);
        }
        
        try {
            // Total de registros
            $countSql = "SELECT COUNT(*) FROM seguridad_usuarios u $where";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();
            
            // Obtener usuarios
            $sql = "
                SELECT u.*, r.rol_nombre, t.ten_nombre_comercial as tenant_nombre
                FROM seguridad_usuarios u
                LEFT JOIN seguridad_roles r ON u.usu_rol_id = r.rol_rol_id
                LEFT JOIN seguridad_tenants t ON u.usu_tenant_id = t.ten_tenant_id
                $where
                ORDER BY u.usu_fecha_registro DESC
                LIMIT $porPagina OFFSET $offset
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Obtener tenants para filtro
            $tenants = $this->db->query("SELECT ten_tenant_id, ten_nombre_comercial FROM seguridad_tenants WHERE ten_estado = 'A' ORDER BY ten_nombre_comercial")->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            $usuarios = [];
            $tenants = [];
            $total = 0;
        }
        
        $this->renderModule('usuario/index', [
            'usuarios' => $usuarios,
            'tenants' => $tenants,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'totalPaginas' => ceil($total / $porPagina),
            'filtros' => ['tenant_id' => $tenantId, 'estado' => $estado, 'buscar' => $buscar],
            'pageTitle' => 'Gestión de Usuarios'
        ]);
    }
    
    /**
     * Crear usuario
     */
    public function crear() {
        $this->authorize('crear', 'usuarios');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardar();
            return;
        }
        
        $tenants = $this->db->query("SELECT ten_tenant_id, ten_nombre_comercial FROM seguridad_tenants WHERE ten_estado = 'A' ORDER BY ten_nombre_comercial")->fetchAll(\PDO::FETCH_ASSOC);
        $roles = $this->db->query("SELECT rol_rol_id, rol_nombre, rol_codigo FROM seguridad_roles WHERE rol_estado = 'A' ORDER BY rol_nombre")->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->renderModule('usuario/form', [
            'usuario' => null,
            'tenants' => $tenants,
            'roles' => $roles,
            'pageTitle' => 'Nuevo Usuario'
        ]);
    }
    
    /**
     * Editar usuario
     */
    public function editar() {
        $this->authorize('editar', 'usuarios');
        try {
            $id = $_GET['id'] ?? 0;
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->guardar($id);
                return;
            }
            if (!$id || !is_numeric($id)) {
                throw new \Exception('ID de usuario inválido');
            }
            $stmt = $this->db->prepare("SELECT * FROM seguridad_usuarios WHERE usu_usuario_id = ?");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$usuario) {
                throw new \Exception('Usuario no encontrado');
            }
            $tenants = $this->db->query("SELECT ten_tenant_id, ten_nombre_comercial FROM seguridad_tenants WHERE ten_estado = 'A' ORDER BY ten_nombre_comercial")->fetchAll(\PDO::FETCH_ASSOC);
            $roles = $this->db->query("SELECT rol_rol_id, rol_nombre, rol_codigo FROM seguridad_roles WHERE rol_estado = 'A' ORDER BY rol_nombre")->fetchAll(\PDO::FETCH_ASSOC);
            $this->renderModule('usuario/form', [
                'usuario' => $usuario,
                'tenants' => $tenants,
                'roles' => $roles,
                'pageTitle' => 'Editar Usuario'
            ]);
        } catch (\Exception $e) {
            echo '<div style="background:#fee;color:#900;padding:20px;font-size:18px;"><b>Error al cargar usuario:</b> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
    
    /**
     * Guardar usuario
     */
    private function guardar($id = null) {
        $data = [
            'usu_tenant_id' => $_POST['usu_tenant_id'],
            'usu_identificacion' => $_POST['usu_identificacion'] ?? null,
            'usu_nombres' => $_POST['usu_nombres'],
            'usu_apellidos' => $_POST['usu_apellidos'],
            'usu_email' => $_POST['usu_email'],
            'usu_telefono' => $_POST['usu_telefono'] ?? null,
            'usu_celular' => $_POST['usu_celular'] ?? null,
            'usu_username' => $_POST['usu_username'],
            'usu_rol_id' => $_POST['usu_rol_id'],
            'usu_requiere_2fa' => isset($_POST['usu_requiere_2fa']) ? 'S' : 'N',
            'usu_estado' => $_POST['usu_estado'] ?? 'A'
        ];
        
        try {
            if ($id) {
                // Estado previo
                $stmtPrev = $this->db->prepare("SELECT * FROM seguridad_usuarios WHERE usu_usuario_id = ?");
                $stmtPrev->execute([$id]);
                $datosAntes = $stmtPrev->fetch(\PDO::FETCH_ASSOC);
                // Actualizar
                $sql = "UPDATE seguridad_usuarios SET 
                    usu_tenant_id = ?, usu_identificacion = ?, usu_nombres = ?, usu_apellidos = ?,
                    usu_email = ?, usu_telefono = ?, usu_celular = ?, usu_username = ?,
                    usu_rol_id = ?, usu_requiere_2fa = ?, usu_estado = ?, usu_fecha_actualizacion = NOW()
                    WHERE usu_usuario_id = ?";
                $params = array_values($data);
                $params[] = $id;
                // Si hay nueva contraseña
                if (!empty($_POST['password'])) {
                    $sql = "UPDATE seguridad_usuarios SET 
                        usu_tenant_id = ?, usu_identificacion = ?, usu_nombres = ?, usu_apellidos = ?,
                        usu_email = ?, usu_telefono = ?, usu_celular = ?, usu_username = ?,
                        usu_rol_id = ?, usu_requiere_2fa = ?, usu_estado = ?, usu_password = ?, usu_fecha_actualizacion = NOW()
                        WHERE usu_usuario_id = ?";
                    $params = array_values($data);
                    $params[] = password_hash($_POST['password'], PASSWORD_ARGON2ID);
                    $params[] = $id;
                }
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                // Estado posterior
                $stmtPost = $this->db->prepare("SELECT * FROM seguridad_usuarios WHERE usu_usuario_id = ?");
                $stmtPost->execute([$id]);
                $datosDespues = $stmtPost->fetch(\PDO::FETCH_ASSOC);
                $this->registrarAuditoria('editar_usuario', 'usuario', $id, $datosAntes, $datosDespues);
                setFlashMessage('success', 'Usuario actualizado correctamente');
                // Recargar la vista de edición para mostrar el mensaje
                redirect('seguridad', 'usuario', 'editar', ['id' => $id]);
                return;
            } else {
                // Crear
                if (empty($_POST['password'])) {
                    setFlashMessage('error', 'La contraseña es obligatoria');
                    redirect('seguridad', 'usuario', 'crear');
                    return;
                }
                $data['usu_password'] = password_hash($_POST['password'], PASSWORD_ARGON2ID);
                $sql = "INSERT INTO seguridad_usuarios (usu_tenant_id, usu_identificacion, usu_nombres, usu_apellidos, usu_email, usu_telefono, usu_celular, usu_username, usu_rol_id, usu_requiere_2fa, usu_estado, usu_password)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [
                    $data['usu_tenant_id'], $data['usu_identificacion'], $data['usu_nombres'], $data['usu_apellidos'],
                    $data['usu_email'], $data['usu_telefono'], $data['usu_celular'], $data['usu_username'],
                    $data['usu_rol_id'], $data['usu_requiere_2fa'], $data['usu_estado'], $data['usu_password']
                ];
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $nuevoId = $this->db->lastInsertId();
                $this->registrarAuditoria('crear_usuario', 'usuario', $nuevoId, null, $data);
                setFlashMessage('success', 'Usuario creado correctamente');
            }
            redirect('seguridad', 'usuario', 'index');
        } catch (\Exception $e) {
            $this->registrarAuditoria($id ? 'editar_usuario' : 'crear_usuario', 'usuario', $id, null, null, 'error', $e->getMessage());
            setFlashMessage('error', 'Error al guardar: ' . $e->getMessage());
            if ($id) {
                redirect('seguridad', 'usuario', 'editar', ['id' => $id]);
            } else {
                redirect('seguridad', 'usuario', 'crear');
            }
        }
    }
    
    /**
     * Eliminar usuario (soft delete)
     */
    public function eliminar() {
        $this->authorize('eliminar', 'usuarios');
        $id = $_GET['id'] ?? 0;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $stmtPrev = $this->db->prepare("SELECT * FROM seguridad_usuarios WHERE usu_usuario_id = ?");
                $stmtPrev->execute([$id]);
                $datosAntes = $stmtPrev->fetch(\PDO::FETCH_ASSOC);
                $stmt = $this->db->prepare("UPDATE seguridad_usuarios SET usu_estado = 'E' WHERE usu_usuario_id = ?");
                $stmt->execute([$id]);
                $this->registrarAuditoria('eliminar_usuario', 'usuario', $id, $datosAntes, null);
                setFlashMessage('success', 'Usuario eliminado correctamente');
            } catch (\Exception $e) {
                $this->registrarAuditoria('eliminar_usuario', 'usuario', $id, null, null, 'error', $e->getMessage());
                setFlashMessage('error', 'Error al eliminar usuario');
            }
            redirect('seguridad', 'usuario', 'index');
            return;
        }
        $this->renderModule('usuario/eliminar', [
            'usuario_id' => $id,
            'pageTitle' => 'Confirmar Eliminación de Usuario'
        ]);
    }
    
    /**
     * Desbloquear usuario
     */
    public function desbloquear() {
        $this->authorize('editar', 'usuarios');
        $id = $_GET['id'] ?? 0;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $stmt = $this->db->prepare("UPDATE seguridad_usuarios SET usu_intentos_fallidos = 0, usu_bloqueado_hasta = NULL WHERE usu_usuario_id = ?");
                $stmt->execute([$id]);
                setFlashMessage('success', 'Usuario desbloqueado');
            } catch (\Exception $e) {
                setFlashMessage('error', 'Error al desbloquear usuario');
            }
            redirect('seguridad', 'usuario', 'index');
            return;
        }
        $this->renderModule('usuario/desbloquear', [
            'usuario_id' => $id,
            'pageTitle' => 'Confirmar Desbloqueo de Usuario'
        ]);
    }
    
    /**
     * Usuarios bloqueados
     */
    public function bloqueados() {
        $this->authorize('ver', 'usuarios');
        try {
            $stmt = $this->db->query("
                SELECT u.*, r.rol_nombre as rol_nombre, t.ten_nombre_comercial as tenant_nombre
                FROM seguridad_usuarios u
                LEFT JOIN seguridad_roles r ON u.usu_rol_id = r.rol_rol_id
                LEFT JOIN seguridad_tenants t ON u.usu_tenant_id = t.ten_tenant_id
                WHERE u.usu_intentos_fallidos >= 3 OR u.usu_bloqueado_hasta > NOW()
                ORDER BY u.usu_bloqueado_hasta DESC
            ");
            $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $usuarios = [];
        }
        
        $this->renderModule('usuario/bloqueados', [
            'usuarios' => $usuarios,
            'pageTitle' => 'Usuarios Bloqueados'
        ]);
    }
    
    /**
     * Reset de contraseña
     */
    public function resetPassword() {
        $this->authorize('editar', 'usuarios');
        $id = $_GET['id'] ?? 0;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nuevaPassword = bin2hex(random_bytes(4)); // 8 caracteres
            try {
                $stmt = $this->db->prepare("UPDATE seguridad_usuarios SET usu_password = ?, usu_debe_cambiar_password = 'S' WHERE usu_usuario_id = ?");
                $stmt->execute([password_hash($nuevaPassword, PASSWORD_ARGON2ID), $id]);
                setFlashMessage('success', "Contraseña reseteada: $nuevaPassword");
            } catch (\Exception $e) {
                setFlashMessage('error', 'Error al resetear contraseña');
            }
            redirect('seguridad', 'usuario', 'index');
            return;
        }
        $this->renderModule('usuario/resetPassword', [
            'usuario_id' => $id,
            'pageTitle' => 'Confirmar Reseteo de Contraseña'
        ]);
    }
    
    /**
     * Obtener items del menú
     */
    protected function getMenuItems() {
        // Forzar menú de seguridad
        require_once BASE_PATH . '/app/controllers/seguridad/DashboardController.php';
        $dashboard = new DashboardController();
        return $dashboard->getMenuItems();
    }
}
