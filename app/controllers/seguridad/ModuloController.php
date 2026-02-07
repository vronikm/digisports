<?php
namespace App\Controllers\Seguridad;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class ModuloController extends \App\Controllers\ModuleController {
    protected $moduloCodigo = 'seguridad';
    protected $moduloNombre = 'Seguridad';
    protected $moduloIcono = 'fas fa-shield-alt';
    protected $moduloColor = '#F59E0B';
        public function iconos_admin_delete() {
            $this->authorize('editar', 'modulos');
            header('Content-Type: application/json');
            $input = json_decode(file_get_contents('php://input'), true);
            $data = $this->cargarIconosColores();

            // Eliminar color (cuando viene color_hex)
            $colorHex = $input['color_hex'] ?? null;
            if ($colorHex) {
                if (isset($data['colores'][$colorHex])) {
                    unset($data['colores'][$colorHex]);
                    $this->guardarIconosColores($data);
                    echo json_encode(['success' => true]); exit;
                } else {
                    echo json_encode(['success' => false, 'error' => 'Color no encontrado']); exit;
                }
            }

            // Eliminar icono
            $grupo = $input['grupo'] ?? null;
            $icono = $input['icono'] ?? null;
            if (!$grupo || !$icono) {
                echo json_encode(['success' => false, 'error' => 'Datos incompletos']); exit;
            }
            if (isset($data['iconos'][$grupo][$icono])) {
                unset($data['iconos'][$grupo][$icono]);
                $this->guardarIconosColores($data);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Icono no encontrado']);
            }
            exit;
        }

        // Endpoint para editar icono (cambiar nombre)
        public function iconos_admin_edit() {
            $this->authorize('editar', 'modulos');
            header('Content-Type: application/json');
            $input = json_decode(file_get_contents('php://input'), true);
            $grupo = $input['grupo'] ?? null;
            $icono = $input['icono'] ?? null;
            $nombre = $input['nombre'] ?? null;
            if (!$grupo || !$icono || !$nombre) {
                echo json_encode(['success' => false, 'error' => 'Datos incompletos']); exit;
            }
            $data = $this->cargarIconosColores();
            if (isset($data['iconos'][$grupo][$icono])) {
                $data['iconos'][$grupo][$icono] = $nombre;
                $this->guardarIconosColores($data);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Icono no encontrado']);
            }
            exit;
        }
    // Endpoint para agregar icono dinámicamente (AJAX)
    public function iconos_admin_add() {
        $this->authorize('editar', 'modulos');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $grupo = $input['grupo'] ?? null;
        $icono = $input['icono'] ?? null;
        $nombre = $input['nombre'] ?? null;
        if (!$grupo || !$icono || !$nombre) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']); exit;
        }
        $data = $this->cargarIconosColores();
        if (!isset($data['iconos'][$grupo])) $data['iconos'][$grupo] = [];
        $data['iconos'][$grupo][$icono] = $nombre;
        $this->guardarIconosColores($data);
        echo json_encode(['success' => true]);
        exit;
    }

    // Endpoint para agregar color dinámicamente (AJAX)
    public function iconos_admin_add_color() {
        $this->authorize('editar', 'modulos');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $hex = $input['hex'] ?? null;
        $nombre = $input['nombre'] ?? null;
        if (!$hex || !$nombre) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']); exit;
        }
        $data = $this->cargarIconosColores();
        $data['colores'][$hex] = $nombre;
        $this->guardarIconosColores($data);
        echo json_encode(['success' => true]);
        exit;
    }

    // Cargar iconos y colores desde storage/iconos_colores.json
    private function cargarIconosColores() {
        $file = BASE_PATH . '/storage/iconos_colores.json';
        // Si el archivo no existe o está vacío, inicializar con iconos y colores por defecto
        if (!file_exists($file)) {
            $data = ['iconos' => $this->iconosDisponibles, 'colores' => [
                '#22C55E' => 'Verde',
                '#3B82F6' => 'Azul',
                '#F59E42' => 'Naranja',
                '#EF4444' => 'Rojo',
                '#A855F7' => 'Morado',
                '#FACC15' => 'Amarillo',
                '#64748B' => 'Gris'
            ]];
            $this->guardarIconosColores($data);
            return $data;
        }
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        // Si el archivo existe pero está vacío, inicializar también
        if (!$data || empty($data['iconos']) || empty($data['colores'])) {
            $data = ['iconos' => $this->iconosDisponibles, 'colores' => [
                '#22C55E' => 'Verde',
                '#3B82F6' => 'Azul',
                '#F59E42' => 'Naranja',
                '#EF4444' => 'Rojo',
                '#A855F7' => 'Morado',
                '#FACC15' => 'Amarillo',
                '#64748B' => 'Gris'
            ]];
            $this->guardarIconosColores($data);
        }
        return $data;
    }

    // Guardar iconos y colores en storage/iconos_colores.json
    private function guardarIconosColores($data) {
        $file = BASE_PATH . '/storage/iconos_colores.json';
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private $iconosDisponibles = [
        'Sistema' => [
            'fa-store' => 'Tienda',
            'fa-shield-alt' => 'Seguridad',
            // ...otros iconos...
        ],
        'Deportes' => [
            'fa-futbol' => 'Fútbol',
            'fa-basketball-ball' => 'Basket',
            'fa-swimmer' => 'Natación',
            'fa-chess-knight' => 'Ajedrez',
            'fa-dumbbell' => 'Gimnasio',
            'fa-running' => 'Atletismo',
            'fa-table-tennis' => 'Tenis de Mesa',
            'fa-volleyball-ball' => 'Voleibol',
            'fa-baseball-ball' => 'Béisbol',
            'fa-bowling-ball' => 'Bowling',
            'fa-hockey-puck' => 'Hockey',
            'fa-skiing' => 'Ski',
            'fa-biking' => 'Ciclismo',
            'fa-medal' => 'Medalla',
            'fa-trophy' => 'Trofeo',
            'fa-users' => 'Equipo',
            'fa-boxing-glove' => 'Box',
            'fa-user-ninja' => 'Artes Marciales',
        ],
        // ...otros grupos de iconos...
    ];



    public function index() {
        $this->authorize('ver', 'modulos');
        try {
            $modulos = $this->db->query("SELECT mod_id, mod_codigo, mod_nombre, mod_descripcion, mod_icono, mod_color_fondo, mod_orden, mod_activo, mod_es_externo, mod_url_externa, mod_ruta_modulo, mod_ruta_controller, mod_ruta_action, mod_requiere_licencia FROM seguridad_modulos ORDER BY mod_orden")
            ->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $modulos = [];
        }
        $this->renderModule('modulo/index', [
            'modulos' => $modulos,
            'pageTitle' => 'Módulos del sistema'
        ]);
    }

    public function crear() {
        $this->authorize('crear', 'modulos');
        // Método pendiente de implementación
    }

    public function editar() {
        $this->authorize('editar', 'modulos');
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . url('seguridad', 'modulo', 'index'));
            exit;
        }
            $stmt = $this->db->prepare("SELECT * FROM seguridad_modulos WHERE mod_id = ?");
        $stmt->execute([$id]);
        $modulo = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$modulo) {
            header('Location: ' . url('seguridad', 'modulo', 'index'));
            exit;
        }
        // Cargar iconos y colores desde storage/iconos_colores.json
        $jsonData = $this->cargarIconosColores();
        $this->renderModule('modulo/form', [
            'modulo' => $modulo,
            'iconos' => $jsonData['iconos'] ?? [],
            'colores' => $jsonData['colores'] ?? [],
            'pageTitle' => 'Editar Módulo'
        ]);
    }

    private function guardar($id = null) {
        $this->authorize($id ? 'editar' : 'crear', 'modulos');
        // Método pendiente de implementación
    }

    public function activar() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $codigo = $input['codigo'] ?? null;
        if (!$codigo) {
            echo json_encode(['success' => false, 'message' => 'Código de módulo requerido']);
            exit;
        }
        try {
                $stmt = $this->db->prepare("SELECT * FROM seguridad_modulos WHERE mod_codigo = ?");
            $stmt->execute([$codigo]);
            $moduloSis = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$moduloSis) {
                echo json_encode(['success' => false, 'message' => 'Módulo no encontrado en catálogo del sistema']);
                exit;
            }
                $stmt = $this->db->prepare("SELECT * FROM seguridad_modulos WHERE mod_codigo = ?");
            $stmt->execute([$codigo]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El módulo ya está activo en el sistema']);
                exit;
            }
                $sql = "INSERT INTO seguridad_modulos (mod_codigo, mod_nombre, mod_descripcion, mod_icono, mod_color_fondo, mod_orden, mod_es_externo, mod_url_externa, mod_requiere_licencia, mod_activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                    $moduloSis['mod_codigo'],
                    $moduloSis['mod_nombre'],
                    $moduloSis['mod_descripcion'],
                    $moduloSis['mod_icono'],
                    $moduloSis['mod_color_fondo'] ?? '#3B82F6',
                    $moduloSis['mod_orden'] ?? 0,
                    $moduloSis['mod_es_externo'] ?? 0,
                    $moduloSis['mod_url_externa'] ?? null,
                    $moduloSis['mod_requiere_licencia'] ?? 1,
                    1
            ];
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $this->registrarAuditoria('activar_modulo', 'modulo', $this->db->lastInsertId(), null, $moduloSis);
            echo json_encode(['success' => true, 'message' => 'Módulo activado correctamente']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al activar: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Configuración del sistema
     */
    public function configuracion() {
        $this->renderModule('modulo/configuracion', [
            'pageTitle' => 'Configuración de Seguridad'
        ]);
    }

    protected function getMenuItems() {
        require_once BASE_PATH . '/app/controllers/seguridad/DashboardController.php';
        $dashboard = new \App\Controllers\Seguridad\DashboardController();
        return $dashboard->getMenuItems();
    }

    public function iconos() {
        try {
                $modulos = $this->db->query("SELECT * FROM seguridad_modulos ORDER BY mod_orden")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $modulos = [];
        }
        $data = $this->cargarIconosColores();
        $iconos = $data['iconos'] ?? $this->iconosDisponibles;
        $colores = $data['colores'] ?? [];
        $this->renderModule('modulo/iconos', [
            'modulos' => $modulos,
            'iconos' => $iconos,
            'colores' => $colores,
            'pageTitle' => 'Iconos y Colores'
        ]);
    }

    public function actualizarIcono() {
        header('Content-Type: application/json');
        $id = $_POST['modulo_id'] ?? 0;
        $icono = $_POST['icono'] ?? null;
        $color = $_POST['color'] ?? null;
        try {
            $updates = [];
            $params = [];
            if ($icono) {
                    $updates[] = "mod_icono = ?";
                $params[] = $icono;
            }
            if ($color) {
                    $updates[] = "mod_color_fondo = ?";
                $params[] = $color;
            }
            if (!empty($updates)) {
                $params[] = $id;
                    $sql = "UPDATE seguridad_modulos SET " . implode(', ', $updates) . " WHERE mod_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function getIconos() {
        header('Content-Type: application/json');
        echo json_encode($this->iconosDisponibles);
        exit;
    }

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

} // Fin de la clase ModuloController
