<?php
/**
 * DigiSports - Controlador de Clientes
 * Gestión de clientes, socios y empresas
 * 
 * @package DigiSports\Controllers\Clientes
 * @version 1.0.0
 */

namespace App\Controllers\Clientes;

require_once BASE_PATH . '/app/controllers/BaseController.php';

class ClienteController extends \BaseController {
    
    /**
     * Listado de clientes
     */
    public function index() {
        $this->checkPermission('ver');
        
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        // Filtros
        $buscar = $_GET['buscar'] ?? '';
        $tipo = $_GET['tipo'] ?? '';
        $estado = $_GET['estado'] ?? 'A';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        try {
            // Construir consulta
            $where = ["c.cli_tenant_id = ?"];
            $params = [$tenantId];
            
            if ($buscar) {
                $where[] = "(c.cli_nombres LIKE ? OR c.cli_apellidos LIKE ? OR c.cli_identificacion LIKE ? OR c.cli_email LIKE ?)";
                $buscarLike = "%{$buscar}%";
                $params = array_merge($params, [$buscarLike, $buscarLike, $buscarLike, $buscarLike]);
            }

            if ($tipo) {
                $where[] = "c.cli_tipo_cliente = ?";
                $params[] = $tipo;
            }

            if ($estado) {
                $where[] = "c.cli_estado = ?";
                $params[] = $estado;
            }
            
            $whereClause = implode(' AND ', $where);
            
            // Total de registros
            $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM clientes c WHERE {$whereClause}");
            $stmtCount->execute($params);
            $total = $stmtCount->fetchColumn();
            
            // Obtener clientes
            $sql = "
                SELECT 
                    c.*,
                    c.cli_saldo_abono as saldo_favor,
                    0 as total_reservas
                FROM clientes c
                WHERE {$whereClause}
                ORDER BY c.cli_apellidos ASC, c.cli_nombres ASC
                LIMIT {$perPage} OFFSET {$offset}
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Tipos de cliente para filtro
            $tiposCliente = [
                'SOCIO' => 'Socio',
                'CLIENTE' => 'Cliente Regular',
                'EMPRESA' => 'Empresa',
                'INVITADO' => 'Invitado',
                'PUBLICO' => 'Público General'
            ];
            
            $this->viewData['clientes'] = $clientes;
            $this->viewData['total'] = $total;
            $this->viewData['page'] = $page;
            $this->viewData['perPage'] = $perPage;
            $this->viewData['totalPages'] = ceil($total / $perPage);
            $this->viewData['filtros'] = compact('buscar', 'tipo', 'estado');
            $this->viewData['tiposCliente'] = $tiposCliente;
            $this->viewData['title'] = 'Gestión de Clientes';
            $this->viewData['layout'] = 'main';
            
            $this->render('clientes/index', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al listar clientes: " . $e->getMessage());
            $this->viewData['error'] = 'Error al cargar los clientes';
            $this->viewData['clientes'] = [];
            $this->render('clientes/index', $this->viewData);
        }
    }
    
    /**
     * Formulario de nuevo cliente
     */
    public function crear() {
        $this->checkPermission('crear');
        
        $this->viewData['title'] = 'Nuevo Cliente';
        $this->viewData['layout'] = 'main';
        $this->viewData['cliente'] = null;
        $this->viewData['tiposCliente'] = $this->getTiposCliente();
        $this->viewData['tiposIdentificacion'] = $this->getTiposIdentificacion();
        
        $this->render('clientes/form', $this->viewData);
    }
    
    /**
     * Guardar nuevo cliente
     */
    public function guardar() {
        $this->checkPermission('crear');
        
        if (!$this->isPost()) {
            redirect('clientes', 'cliente', 'index');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
            return;
        }
        
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        // Validar datos
        $data = $this->validateClienteData($_POST);
        
        if (!$data['valid']) {
            if ($this->isAjax()) {
                $this->error($data['message']);
            } else {
                setFlashMessage('error', $data['message']);
                redirect('clientes', 'cliente', 'crear');
            }
            return;
        }
        
        try {
            // Verificar si ya existe
            $stmt = $this->db->prepare("
                SELECT cli_cliente_id FROM clientes 
                WHERE cli_tenant_id = ? AND cli_identificacion = ?
            ");
            $stmt->execute([$tenantId, $data['identificacion']]);
            
            if ($stmt->fetch()) {
                if ($this->isAjax()) {
                    $this->error('Ya existe un cliente con esta identificación');
                } else {
                    setFlashMessage('error', 'Ya existe un cliente con esta identificación');
                    redirect('clientes', 'cliente', 'crear');
                }
                return;
            }
            
            // Insertar cliente
            $sql = "
                INSERT INTO clientes (
                    cli_tenant_id, cli_tipo_identificacion, cli_identificacion, 
                    cli_nombres, cli_apellidos, cli_email, cli_telefono, cli_celular,
                    cli_direccion, cli_tipo_cliente, cli_fecha_nacimiento, cli_estado
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'A'
                )
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $tenantId,
                $data['tipo_identificacion'],
                $data['identificacion'],
                $data['nombres'],
                $data['apellidos'],
                $data['email'] ?? null,
                $data['telefono'] ?? null,
                $data['celular'] ?? null,
                $data['direccion'] ?? null,
                $data['tipo_cliente'],
                $data['fecha_nacimiento'] ?? null
            ]);
            
            $clienteId = $this->db->lastInsertId();
            
            // Auditoría
            $this->audit('clientes', $clienteId, 'INSERT', [], $data);
            
            if ($this->isAjax()) {
                $this->success(['id' => $clienteId], 'Cliente registrado exitosamente');
            } else {
                setFlashMessage('success', 'Cliente registrado exitosamente');
                redirect('clientes', 'cliente', 'index');
            }
            
        } catch (\Exception $e) {
            $this->logError("Error al guardar cliente: " . $e->getMessage());
            if ($this->isAjax()) {
                $this->error('Error al guardar el cliente');
            } else {
                setFlashMessage('error', 'Error al guardar el cliente');
                redirect('clientes', 'cliente', 'crear');
            }
        }
    }
    
    /**
     * Ver detalle de cliente
     */
    public function ver() {
        $this->checkPermission('ver');
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            redirect('clientes', 'cliente', 'index');
        }
        
        $cliente = $this->getCliente($id);
        
        if (!$cliente) {
            setFlashMessage('error', 'Cliente no encontrado');
            redirect('clientes', 'cliente', 'index');
        }
        
        // Obtener historial de reservas
        $reservas = $this->getReservasCliente($id);
        
        // Obtener historial de pagos
        $pagos = $this->getPagosCliente($id);
        
        // Obtener abonos
        $abonos = $this->getAbonosCliente($id);
        
        $this->viewData['cliente'] = $cliente;
        $this->viewData['reservas'] = $reservas;
        $this->viewData['pagos'] = $pagos;
        $this->viewData['abonos'] = $abonos;
        $this->viewData['title'] = 'Detalle de Cliente';
        $this->viewData['layout'] = 'main';
        
        $this->render('clientes/ver', $this->viewData);
    }
    
    /**
     * Formulario de edición
     */
    public function editar() {
        $this->checkPermission('editar');
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            redirect('clientes', 'cliente', 'index');
        }
        
        $cliente = $this->getCliente($id);
        
        if (!$cliente) {
            setFlashMessage('error', 'Cliente no encontrado');
            redirect('clientes', 'cliente', 'index');
        }
        
        $this->viewData['cliente'] = $cliente;
        $this->viewData['title'] = 'Editar Cliente';
        $this->viewData['layout'] = 'main';
        $this->viewData['tiposCliente'] = $this->getTiposCliente();
        $this->viewData['tiposIdentificacion'] = $this->getTiposIdentificacion();
        
        $this->render('clientes/form', $this->viewData);
    }
    
    /**
     * Actualizar cliente
     */
    public function actualizar() {
        $this->checkPermission('editar');
        
        if (!$this->isPost()) {
            redirect('clientes', 'cliente', 'index');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
            return;
        }
        
        $id = $_POST['cliente_id'] ?? null;
        
        if (!$id) {
            $this->error('ID de cliente requerido');
            return;
        }
        
        $clienteActual = $this->getCliente($id);
        
        if (!$clienteActual) {
            $this->error('Cliente no encontrado');
            return;
        }
        
        // Validar datos
        $data = $this->validateClienteData($_POST, $id);
        
        if (!$data['valid']) {
            if ($this->isAjax()) {
                $this->error($data['message']);
            } else {
                setFlashMessage('error', $data['message']);
                redirect('clientes', 'cliente', 'editar', ['id' => $id]);
            }
            return;
        }
        
        try {
            $sql = "
                UPDATE clientes SET
                    cli_tipo_identificacion = ?,
                    cli_identificacion = ?,
                    cli_nombres = ?,
                    cli_apellidos = ?,
                    cli_email = ?,
                    cli_telefono = ?,
                    cli_celular = ?,
                    cli_direccion = ?,
                    cli_tipo_cliente = ?,
                    cli_fecha_nacimiento = ?,
                    cli_estado = ?
                WHERE cli_cliente_id = ? AND cli_tenant_id = ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['tipo_identificacion'],
                $data['identificacion'],
                $data['nombres'],
                $data['apellidos'],
                $data['email'] ?? null,
                $data['telefono'] ?? null,
                $data['celular'] ?? null,
                $data['direccion'] ?? null,
                $data['tipo_cliente'],
                $data['fecha_nacimiento'] ?? null,
                $data['estado'] ?? 'A',
                $id,
                $_SESSION['tenant_id']
            ]);
            
            // Auditoría
            $this->audit('clientes', $id, 'UPDATE', $clienteActual, $data);
            
            if ($this->isAjax()) {
                $this->success(null, 'Cliente actualizado exitosamente');
            } else {
                setFlashMessage('success', 'Cliente actualizado exitosamente');
                redirect('clientes', 'cliente', 'index');
            }
            
        } catch (\Exception $e) {
            $this->logError("Error al actualizar cliente: " . $e->getMessage());
            if ($this->isAjax()) {
                $this->error('Error al actualizar el cliente');
            } else {
                setFlashMessage('error', 'Error al actualizar el cliente');
                redirect('clientes', 'cliente', 'editar', ['id' => $id]);
            }
        }
    }
    
    /**
     * Eliminar cliente (cambiar estado)
     */
    public function eliminar() {
        $this->checkPermission('eliminar');
        
        if (!$this->isPost()) {
            $this->error('Método no permitido', 405);
            return;
        }
        
        $id = $_POST['id'] ?? $_GET['id'] ?? null;
        
        if (!$id) {
            $this->error('ID de cliente requerido');
            return;
        }
        
        try {
            // Verificar si tiene reservas activas
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM instalaciones_reservas 
                WHERE res_cliente_id = ? AND res_estado IN ('PENDIENTE', 'CONFIRMADA')
            ");
            $stmt->execute([$id]);
            
            if ($stmt->fetchColumn() > 0) {
                $this->error('No se puede eliminar: el cliente tiene reservas activas');
                return;
            }
            
            // Cambiar estado a inactivo
            $stmt = $this->db->prepare("
                UPDATE clientes SET cli_estado = 'I', cli_fecha_registro = NOW() 
                WHERE cli_cliente_id = ? AND cli_tenant_id = ?
            ");
            $stmt->execute([$id, $_SESSION['tenant_id']]);
            
            // Auditoría
            $this->audit('clientes', $id, 'DELETE', [], ['estado' => 'I']);
            
            $this->success(null, 'Cliente eliminado exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al eliminar cliente: " . $e->getMessage());
            $this->error('Error al eliminar el cliente');
        }
    }
    
    /**
     * Búsqueda AJAX de clientes
     */
    public function buscar() {
        $this->checkPermission('ver');
        
        $termino = $_GET['q'] ?? $_GET['term'] ?? '';
        $limit = min(20, (int)($_GET['limit'] ?? 10));
        
        if (strlen($termino) < 2) {
            $this->renderJson([]);
            return;
        }
        
        try {
            $sql = "
                SELECT 
                    cli_cliente_id as id,
                    CONCAT(cli_nombres, ' ', cli_apellidos) as text,
                    cli_identificacion,
                    cli_email,
                    cli_tipo_cliente
                FROM clientes
                WHERE cli_tenant_id = ?
                AND cli_estado = 'A'
                AND (
                    cli_nombres LIKE ? OR 
                    cli_apellidos LIKE ? OR 
                    cli_identificacion LIKE ? OR
                    CONCAT(cli_nombres, ' ', cli_apellidos) LIKE ?
                )
                ORDER BY cli_apellidos, cli_nombres
                LIMIT ?
            ";
            $like = "%{$termino}%";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $_SESSION['tenant_id'],
                $like, $like, $like, $like,
                $limit
            ]);
            
            $this->renderJson($stmt->fetchAll(\PDO::FETCH_ASSOC));
            
        } catch (\Exception $e) {
            $this->logError("Error en búsqueda de clientes: " . $e->getMessage());
            $this->renderJson([]);
        }
    }
    
    // ==================== MÉTODOS PRIVADOS ====================
    
    /**
     * Obtener cliente por ID
     */
    private function getCliente($id) {
        $stmt = $this->db->prepare("
            SELECT * FROM clientes 
            WHERE cli_cliente_id = ? AND cli_tenant_id = ?
        ");
        $stmt->execute([$id, $_SESSION['tenant_id']]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener reservas del cliente
     */
    private function getReservasCliente($clienteId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT r.*, c.nombre as cancha_nombre
            FROM reservas r
            LEFT JOIN canchas c ON r.cancha_id = c.cancha_id
            WHERE r.cliente_id = ?
            ORDER BY r.fecha DESC, r.hora_inicio DESC
            LIMIT ?
        ");
        $stmt->execute([$clienteId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener pagos del cliente
     */
    private function getPagosCliente($clienteId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT p.*, f.numero as factura_numero
            FROM pagos p
            LEFT JOIN facturas f ON p.factura_id = f.factura_id
            WHERE p.cliente_id = ?
            ORDER BY p.fecha_pago DESC
            LIMIT ?
        ");
        $stmt->execute([$clienteId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener abonos del cliente
     */
    private function getAbonosCliente($clienteId) {
        $stmt = $this->db->prepare("
            SELECT * FROM abonos
            WHERE cliente_id = ? AND estado = 'ACTIVO'
            ORDER BY fecha_registro DESC
        ");
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Validar datos del cliente
     */
    private function validateClienteData($data, $excludeId = null) {
        $result = ['valid' => true, 'message' => ''];
        
        // Campos requeridos
        if (empty($data['identificacion'])) {
            return ['valid' => false, 'message' => 'La identificación es requerida'];
        }
        
        if (empty($data['nombres'])) {
            return ['valid' => false, 'message' => 'El nombre es requerido'];
        }
        
        if (empty($data['tipo_cliente'])) {
            return ['valid' => false, 'message' => 'El tipo de cliente es requerido'];
        }
        
        // Validar email si se proporciona
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'El email no es válido'];
        }
        
        // Verificar identificación única
        $sql = "SELECT cliente_id FROM clientes WHERE tenant_id = ? AND identificacion = ?";
        $params = [$_SESSION['tenant_id'], $data['identificacion']];
        
        if ($excludeId) {
            $sql .= " AND cliente_id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->fetch()) {
            return ['valid' => false, 'message' => 'Ya existe un cliente con esta identificación'];
        }
        
        // Pasar datos validados
        return array_merge($result, $data);
    }
    
    /**
     * Obtener tipos de cliente
     */
    private function getTiposCliente() {
        return [
            'SOCIO' => 'Socio',
            'CLIENTE' => 'Cliente Regular',
            'EMPRESA' => 'Empresa',
            'INVITADO' => 'Invitado',
            'PUBLICO' => 'Público General'
        ];
    }
    
    /**
     * Obtener tipos de identificación
     */
    private function getTiposIdentificacion() {
        return [
            'CED' => 'Cédula',
            'RUC' => 'RUC',
            'PAS' => 'Pasaporte',
            'EXT' => 'ID Extranjero'
        ];
    }
    
    /**
     * Verificar permiso
     */
    private function checkPermission($accion) {
        $permisos = $_SESSION['modulo_activo']['permisos'] ?? [];
        
        $mapa = [
            'ver' => 'ver',
            'crear' => 'crear',
            'editar' => 'editar',
            'eliminar' => 'eliminar'
        ];
        
        // Super admin siempre tiene acceso
        if (($_SESSION['rol_codigo'] ?? '') === 'SUPERADMIN') {
            return true;
        }
        
        if (isset($mapa[$accion]) && empty($permisos[$mapa[$accion]])) {
            if ($this->isAjax()) {
                $this->error('No tienes permiso para esta acción', 403);
            } else {
                setFlashMessage('error', 'No tienes permiso para esta acción');
                redirect('core', 'hub', 'index');
            }
            exit;
        }
        
        return true;
    }
}
