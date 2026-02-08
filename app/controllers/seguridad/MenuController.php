<?php
/**
 * DigiSports - Módulo Seguridad
 * Menu Controller - Administración de Menús por Aplicativo
 * 
 * Gestión CRUD de menús dinámicos para todos los módulos del sistema
 * 
 * @package DigiSports\Security
 * @version 1.0.0
 */

namespace App\Controllers\Seguridad;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class MenuController extends \App\Controllers\ModuleController {
    protected $moduloCodigo = 'SEGURIDAD';
    protected $moduloNombre = 'Seguridad';
    protected $moduloIcono = 'fas fa-shield-alt';
    protected $moduloColor = '#F59E0B';

    /**
     * Listado de menús por módulo
     */
    public function index() {
        $this->authorize('ver', 'modulos');

        // Módulo seleccionado (filtro)
        $moduloId = isset($_GET['modulo_id']) ? (int)$_GET['modulo_id'] : null;

        // Obtener módulos activos
        $modulos = $this->getModulosActivos();

        // Obtener menús
        $menus = [];
        $stats = ['total' => 0, 'headers' => 0, 'items' => 0, 'submenus' => 0, 'activos' => 0, 'inactivos' => 0];

        if ($moduloId) {
            $menus = $this->getMenusPorModulo($moduloId);
            $stats = $this->getStatsMenu($moduloId);
        }

        // Obtener roles para la pestaña de permisos
        $roles = $this->getRoles();

        // Obtener permisos actuales si hay módulo seleccionado
        $permisos = [];
        if ($moduloId) {
            $permisos = $this->getPermisosPorModulo($moduloId);
        }

        $this->renderModule('seguridad/menu/index', [
            'modulos'       => $modulos,
            'moduloId'      => $moduloId,
            'menus'         => $menus,
            'stats'         => $stats,
            'roles'         => $roles,
            'permisos'      => $permisos,
            'pageTitle'     => 'Menús por Aplicativo',
            'headerTitle'   => 'Menús por Aplicativo',
            'headerSubtitle'=> 'Administración de menús dinámicos para cada módulo del sistema',
            'headerIcon'    => 'fas fa-bars'
        ]);
    }

    /**
     * Formulario para crear nuevo item de menú
     */
    public function crear() {
        $this->authorize('crear', 'modulos');

        $moduloId = isset($_GET['modulo_id']) ? (int)$_GET['modulo_id'] : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardar();
            return;
        }

        $modulos = $this->getModulosActivos();
        $padres = $moduloId ? $this->getMenusPadre($moduloId) : [];

        $this->renderModule('seguridad/menu/form', [
            'menu'          => null,
            'modulos'       => $modulos,
            'moduloId'      => $moduloId,
            'padres'        => $padres,
            'modoEdicion'   => false,
            'pageTitle'     => 'Nuevo Ítem de Menú',
            'headerTitle'   => 'Nuevo Ítem de Menú',
            'headerSubtitle'=> 'Agregar un nuevo elemento al menú del módulo',
            'headerIcon'    => 'fas fa-plus-circle'
        ]);
    }

    /**
     * Formulario para editar item de menú
     */
    public function editar() {
        $this->authorize('editar', 'modulos');

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->actualizar($id);
            return;
        }

        $menu = $this->getMenuById($id);
        if (!$menu) {
            if (function_exists('setFlashMessage')) setFlashMessage('error', 'Ítem de menú no encontrado.');
            $this->redirigir('index');
            return;
        }

        $modulos = $this->getModulosActivos();
        $padres = $this->getMenusPadre($menu['men_modulo_id'], $id);

        $this->renderModule('seguridad/menu/form', [
            'menu'          => $menu,
            'modulos'       => $modulos,
            'moduloId'      => (int)$menu['men_modulo_id'],
            'padres'        => $padres,
            'modoEdicion'   => true,
            'pageTitle'     => 'Editar Ítem de Menú',
            'headerTitle'   => 'Editar Ítem de Menú',
            'headerSubtitle'=> 'Modificar "' . htmlspecialchars($menu['men_label']) . '"',
            'headerIcon'    => 'fas fa-edit'
        ]);
    }

    /**
     * Guardar nuevo item de menú
     */
    private function guardar() {
        try {
            $data = $this->validarDatos($_POST);
            
            $sql = "INSERT INTO seguridad_menu 
                    (men_modulo_id, men_padre_id, men_tipo, men_label, men_icono, 
                     men_ruta_modulo, men_ruta_controller, men_ruta_action, men_url_custom,
                     men_badge, men_badge_tipo, men_orden, men_activo)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['men_modulo_id'],
                $data['men_padre_id'] ?: null,
                $data['men_tipo'],
                $data['men_label'],
                $data['men_icono'] ?: null,
                $data['men_ruta_modulo'] ?: null,
                $data['men_ruta_controller'] ?: null,
                $data['men_ruta_action'] ?: null,
                $data['men_url_custom'] ?: null,
                $data['men_badge'] ?: null,
                $data['men_badge_tipo'] ?: null,
                $data['men_orden'],
                $data['men_activo']
            ]);

            if (function_exists('setFlashMessage')) setFlashMessage('success', 'Ítem de menú creado correctamente.');
            $this->redirigir('index', ['modulo_id' => $data['men_modulo_id']]);
        } catch (\Exception $e) {
            error_log("MenuController::guardar error: " . $e->getMessage());
            if (function_exists('setFlashMessage')) setFlashMessage('error', 'Error al crear el ítem: ' . $e->getMessage());
            $this->redirigir('crear', ['modulo_id' => $_POST['men_modulo_id'] ?? '']);
        }
    }

    /**
     * Actualizar item de menú existente
     */
    private function actualizar($id) {
        try {
            $data = $this->validarDatos($_POST);
            
            $sql = "UPDATE seguridad_menu SET
                    men_modulo_id = ?, men_padre_id = ?, men_tipo = ?, men_label = ?,
                    men_icono = ?, men_ruta_modulo = ?, men_ruta_controller = ?, men_ruta_action = ?,
                    men_url_custom = ?, men_badge = ?, men_badge_tipo = ?, men_orden = ?,
                    men_activo = ?, men_updated_at = NOW()
                    WHERE men_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['men_modulo_id'],
                $data['men_padre_id'] ?: null,
                $data['men_tipo'],
                $data['men_label'],
                $data['men_icono'] ?: null,
                $data['men_ruta_modulo'] ?: null,
                $data['men_ruta_controller'] ?: null,
                $data['men_ruta_action'] ?: null,
                $data['men_url_custom'] ?: null,
                $data['men_badge'] ?: null,
                $data['men_badge_tipo'] ?: null,
                $data['men_orden'],
                $data['men_activo'],
                $id
            ]);

            if (function_exists('setFlashMessage')) setFlashMessage('success', 'Ítem de menú actualizado correctamente.');
            $this->redirigir('index', ['modulo_id' => $data['men_modulo_id']]);
        } catch (\Exception $e) {
            error_log("MenuController::actualizar error: " . $e->getMessage());
            if (function_exists('setFlashMessage')) setFlashMessage('error', 'Error al actualizar: ' . $e->getMessage());
            $this->redirigir('editar', ['id' => $id]);
        }
    }

    /**
     * Eliminar item de menú (AJAX)
     */
    public function eliminar() {
        $this->authorize('eliminar', 'modulos');
        header('Content-Type: application/json');

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            exit;
        }

        try {
            // Verificar si tiene hijos
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM seguridad_menu WHERE men_padre_id = ?");
            $stmt->execute([$id]);
            $hijos = (int)$stmt->fetchColumn();

            if ($hijos > 0) {
                echo json_encode(['success' => false, 'error' => "Este ítem tiene $hijos sub-elementos. Elimine los sub-elementos primero."]);
                exit;
            }

            // Eliminar permisos asociados
            $stmt = $this->db->prepare("DELETE FROM seguridad_rol_menu WHERE rme_menu_id = ?");
            $stmt->execute([$id]);

            // Eliminar el item
            $stmt = $this->db->prepare("DELETE FROM seguridad_menu WHERE men_id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Ítem eliminado correctamente']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Reordenar menús (AJAX - drag & drop)
     */
    public function reordenar() {
        $this->authorize('editar', 'modulos');
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $items = $input['items'] ?? [];

        if (empty($items)) {
            echo json_encode(['success' => false, 'error' => 'No hay items para reordenar']);
            exit;
        }

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE seguridad_menu SET men_orden = ?, men_padre_id = ? WHERE men_id = ?");
            foreach ($items as $item) {
                $stmt->execute([
                    (int)$item['orden'],
                    !empty($item['padre_id']) ? (int)$item['padre_id'] : null,
                    (int)$item['id']
                ]);
            }
            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Orden actualizado']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Toggle activar/desactivar item (AJAX)
     */
    public function toggle() {
        $this->authorize('editar', 'modulos');
        header('Content-Type: application/json');

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            exit;
        }

        try {
            $stmt = $this->db->prepare("UPDATE seguridad_menu SET men_activo = NOT men_activo, men_updated_at = NOW() WHERE men_id = ?");
            $stmt->execute([$id]);
            
            $stmt = $this->db->prepare("SELECT men_activo FROM seguridad_menu WHERE men_id = ?");
            $stmt->execute([$id]);
            $nuevoEstado = (int)$stmt->fetchColumn();

            echo json_encode(['success' => true, 'activo' => $nuevoEstado, 'message' => $nuevoEstado ? 'Activado' : 'Desactivado']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Guardar permisos de menú por rol (AJAX)
     */
    public function guardarPermisos() {
        $this->authorize('editar', 'roles');
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $rolId = (int)($input['rol_id'] ?? 0);
        $permisos = $input['permisos'] ?? [];

        if (!$rolId) {
            echo json_encode(['success' => false, 'error' => 'Rol no especificado']);
            exit;
        }

        try {
            $this->db->beginTransaction();

            // Eliminar permisos actuales del rol para los menús de este módulo
            $moduloId = (int)($input['modulo_id'] ?? 0);
            if ($moduloId) {
                $stmt = $this->db->prepare("
                    DELETE srm FROM seguridad_rol_menu srm
                    INNER JOIN seguridad_menu sm ON srm.rme_menu_id = sm.men_id
                    WHERE srm.rme_rol_id = ? AND sm.men_modulo_id = ?
                ");
                $stmt->execute([$rolId, $moduloId]);
            }

            // Insertar nuevos permisos
            $stmt = $this->db->prepare("
                INSERT INTO seguridad_rol_menu (rme_rol_id, rme_menu_id, rme_puede_ver, rme_puede_acceder)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($permisos as $perm) {
                $stmt->execute([
                    $rolId,
                    (int)$perm['menu_id'],
                    (int)($perm['puede_ver'] ?? 1),
                    (int)($perm['puede_acceder'] ?? 1)
                ]);
            }

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Permisos guardados correctamente']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Obtener permisos por rol (AJAX)
     */
    public function getPermisos() {
        $this->authorize('ver', 'roles');
        header('Content-Type: application/json');

        $rolId = isset($_GET['rol_id']) ? (int)$_GET['rol_id'] : 0;
        $moduloId = isset($_GET['modulo_id']) ? (int)$_GET['modulo_id'] : 0;

        if (!$rolId || !$moduloId) {
            echo json_encode(['success' => false, 'error' => 'Parámetros incompletos']);
            exit;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT srm.rme_menu_id, srm.rme_puede_ver, srm.rme_puede_acceder
                FROM seguridad_rol_menu srm
                INNER JOIN seguridad_menu sm ON srm.rme_menu_id = sm.men_id
                WHERE srm.rme_rol_id = ? AND sm.men_modulo_id = ?
            ");
            $stmt->execute([$rolId, $moduloId]);
            $permisos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'permisos' => $permisos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Obtener padres disponibles para AJAX al cambiar módulo
     */
    public function getPadres() {
        header('Content-Type: application/json');
        $moduloId = isset($_GET['modulo_id']) ? (int)$_GET['modulo_id'] : 0;
        $excluirId = isset($_GET['excluir_id']) ? (int)$_GET['excluir_id'] : 0;

        if (!$moduloId) {
            echo json_encode([]);
            exit;
        }

        $padres = $this->getMenusPadre($moduloId, $excluirId);
        echo json_encode($padres);
        exit;
    }

    // =====================================================================
    // MÉTODOS PRIVADOS / HELPERS
    // =====================================================================

    /**
     * Obtener módulos activos
     */
    private function getModulosActivos() {
        try {
            $stmt = $this->db->query("
                SELECT mod_id, mod_codigo, mod_nombre, mod_icono, mod_color_fondo,
                       (SELECT COUNT(*) FROM seguridad_menu WHERE men_modulo_id = mod_id) as total_menus
                FROM seguridad_modulos 
                WHERE mod_activo = 1 
                ORDER BY mod_nombre
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener menús de un módulo en estructura jerárquica
     */
    private function getMenusPorModulo($moduloId) {
        try {
            $stmt = $this->db->prepare("
                SELECT m.*, 
                       p.men_label as padre_label,
                       (SELECT COUNT(*) FROM seguridad_menu c WHERE c.men_padre_id = m.men_id) as hijos_count
                FROM seguridad_menu m
                LEFT JOIN seguridad_menu p ON m.men_padre_id = p.men_id
                WHERE m.men_modulo_id = ?
                ORDER BY m.men_orden, m.men_id
            ");
            $stmt->execute([$moduloId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener estadísticas del menú de un módulo
     */
    private function getStatsMenu($moduloId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN men_tipo = 'HEADER' THEN 1 ELSE 0 END) as headers,
                    SUM(CASE WHEN men_tipo = 'ITEM' THEN 1 ELSE 0 END) as items,
                    SUM(CASE WHEN men_tipo = 'SUBMENU' THEN 1 ELSE 0 END) as submenus,
                    SUM(CASE WHEN men_activo = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN men_activo = 0 THEN 1 ELSE 0 END) as inactivos
                FROM seguridad_menu
                WHERE men_modulo_id = ?
            ");
            $stmt->execute([$moduloId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return ['total' => 0, 'headers' => 0, 'items' => 0, 'submenus' => 0, 'activos' => 0, 'inactivos' => 0];
        }
    }

    /**
     * Obtener un menú por ID
     */
    private function getMenuById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM seguridad_menu WHERE men_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtener menús padres posibles (HEADERS e ITEMs sin ruta) para un módulo
     */
    private function getMenusPadre($moduloId, $excluirId = 0) {
        try {
            $sql = "SELECT men_id, men_tipo, men_label, men_padre_id
                    FROM seguridad_menu 
                    WHERE men_modulo_id = ? AND men_tipo IN ('HEADER','ITEM')";
            $params = [$moduloId];

            if ($excluirId) {
                $sql .= " AND men_id != ?";
                $params[] = $excluirId;
            }

            $sql .= " ORDER BY men_orden, men_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener roles del sistema
     */
    private function getRoles() {
        try {
            $stmt = $this->db->query("SELECT rol_rol_id, rol_nombre FROM seguridad_roles ORDER BY rol_nombre");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener permisos asignados por módulo
     */
    private function getPermisosPorModulo($moduloId) {
        try {
            $stmt = $this->db->prepare("
                SELECT srm.rme_rol_id, srm.rme_menu_id, srm.rme_puede_ver, srm.rme_puede_acceder
                FROM seguridad_rol_menu srm
                INNER JOIN seguridad_menu sm ON srm.rme_menu_id = sm.men_id
                WHERE sm.men_modulo_id = ?
                ORDER BY srm.rme_rol_id, sm.men_orden
            ");
            $stmt->execute([$moduloId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Validar datos del formulario
     */
    private function validarDatos($post) {
        $data = [];
        $data['men_modulo_id'] = (int)($post['men_modulo_id'] ?? 0);
        $data['men_padre_id'] = !empty($post['men_padre_id']) ? (int)$post['men_padre_id'] : null;
        $data['men_tipo'] = $post['men_tipo'] ?? 'ITEM';
        $data['men_label'] = trim($post['men_label'] ?? '');
        $data['men_icono'] = trim($post['men_icono'] ?? '');
        $data['men_ruta_modulo'] = trim($post['men_ruta_modulo'] ?? '');
        $data['men_ruta_controller'] = trim($post['men_ruta_controller'] ?? '');
        $data['men_ruta_action'] = trim($post['men_ruta_action'] ?? '');
        $data['men_url_custom'] = trim($post['men_url_custom'] ?? '');
        $data['men_badge'] = trim($post['men_badge'] ?? '');
        $data['men_badge_tipo'] = trim($post['men_badge_tipo'] ?? '');
        $data['men_orden'] = (int)($post['men_orden'] ?? 0);
        $data['men_activo'] = isset($post['men_activo']) ? 1 : 0;

        if (!$data['men_modulo_id']) throw new \Exception('Debe seleccionar un módulo');
        if (empty($data['men_label'])) throw new \Exception('La etiqueta es requerida');
        if (!in_array($data['men_tipo'], ['HEADER', 'ITEM', 'SUBMENU'])) throw new \Exception('Tipo inválido');

        return $data;
    }

    /**
     * Redirigir a una acción del mismo controlador
     */
    private function redirigir($action, $params = []) {
        $urlParams = '';
        foreach ($params as $k => $v) {
            $urlParams .= '&' . urlencode($k) . '=' . urlencode($v);
        }
        $url = url('seguridad', 'menu', $action);
        if ($urlParams) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . ltrim($urlParams, '&');
        }
        header('Location: ' . $url);
        exit;
    }
}
