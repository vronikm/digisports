<?php
/**
 * DigiSports - Controlador de Clientes
 * Gestión de clientes, socios y empresas
 * 
 * @package DigiSports\Controllers\Clientes
 * @version 1.0.0
 */

namespace App\Controllers\Clientes;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class ClienteController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Arena';
    protected $moduloIcono = 'fas fa-building';
    protected $moduloColor = '#3B82F6';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'ARENA';
    }
    
    /**
     * Listado de clientes
     */
    public function index() {
        $this->checkPermission('ver');
        
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        // Filtros (POST o GET)
        $buscar = $this->post('buscar') ?? $this->get('buscar') ?? '';
        $tipo = $this->post('tipo') ?? $this->get('tipo') ?? '';
        $estado = $this->post('estado') ?? $this->get('estado') ?? 'A';
        $page = max(1, (int)($this->post('page') ?? $this->get('page') ?? 1));
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
            $clientes = \DataProtection::decryptRows('clientes', $stmt->fetchAll(\PDO::FETCH_ASSOC));
            
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
            
            $this->renderModule('clientes/index', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al listar clientes: " . $e->getMessage());
            $this->viewData['error'] = 'Error al cargar los clientes';
            $this->viewData['clientes'] = [];
            $this->renderModule('clientes/index', $this->viewData);
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
        
        $this->renderModule('clientes/form', $this->viewData);
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
            // Verificar si ya existe (usar blind index)
            $idHash = \DataProtection::blindIndex($data['identificacion']);
            $stmt = $this->db->prepare("
                SELECT cli_cliente_id FROM clientes 
                WHERE cli_tenant_id = ? AND cli_identificacion_hash = ?
            ");
            $stmt->execute([$tenantId, $idHash]);
            
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
            // Cifrar datos sensibles (LOPDP Ecuador)
            $protectedData = [
                'cli_identificacion' => $data['identificacion'],
                'cli_email' => $data['email'] ?? null,
                'cli_telefono' => $data['telefono'] ?? null,
                'cli_celular' => $data['celular'] ?? null,
            ];
            $encrypted = \DataProtection::encryptRow('clientes', $protectedData);

            $sql = "
                INSERT INTO clientes (
                    cli_tenant_id, cli_tipo_identificacion, cli_identificacion, cli_identificacion_hash,
                    cli_nombres, cli_apellidos, cli_email, cli_email_hash, cli_telefono, cli_celular,
                    cli_direccion, cli_tipo_cliente, cli_fecha_nacimiento, cli_estado
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'A'
                )
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $tenantId,
                $data['tipo_identificacion'],
                $encrypted['cli_identificacion'],
                $encrypted['cli_identificacion_hash'] ?? null,
                $data['nombres'],
                $data['apellidos'],
                $encrypted['cli_email'],
                $encrypted['cli_email_hash'] ?? null,
                $encrypted['cli_telefono'],
                $encrypted['cli_celular'],
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
        
        $id = $this->get('id');
        
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
        
        // Obtener entradas
        $entradas = $this->getEntradasCliente($id);
        
        $this->viewData['cliente'] = $cliente;
        $this->viewData['reservas'] = $reservas;
        $this->viewData['pagos'] = $pagos;
        $this->viewData['abonos'] = $abonos;
        $this->viewData['entradas'] = $entradas;
        $this->viewData['title'] = 'Detalle de Cliente';
        $this->viewData['layout'] = 'main';
        
        $this->renderModule('clientes/ver', $this->viewData);
    }
    
    /**
     * Formulario de edición
     */
    public function editar() {
        $this->checkPermission('editar');
        
        $id = $this->get('id');
        
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
        
        $this->renderModule('clientes/form', $this->viewData);
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
            // Cifrar datos sensibles (LOPDP Ecuador)
            $protectedData = [
                'cli_identificacion' => $data['identificacion'],
                'cli_email' => $data['email'] ?? null,
                'cli_telefono' => $data['telefono'] ?? null,
                'cli_celular' => $data['celular'] ?? null,
            ];
            $encrypted = \DataProtection::encryptRow('clientes', $protectedData);

            $sql = "
                UPDATE clientes SET
                    cli_tipo_identificacion = ?,
                    cli_identificacion = ?,
                    cli_identificacion_hash = ?,
                    cli_nombres = ?,
                    cli_apellidos = ?,
                    cli_email = ?,
                    cli_email_hash = ?,
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
                $encrypted['cli_identificacion'],
                $encrypted['cli_identificacion_hash'] ?? null,
                $data['nombres'],
                $data['apellidos'],
                $encrypted['cli_email'],
                $encrypted['cli_email_hash'] ?? null,
                $encrypted['cli_telefono'],
                $encrypted['cli_celular'],
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
        
        $id = $_POST['id'] ?? $this->get('id');
        
        if (!$id) {
            $this->error('ID de cliente requerido');
            return;
        }
        
        try {
            // Verificar si tiene reservas activas
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM reservas 
                WHERE cliente_id = ? AND estado IN ('PENDIENTE', 'CONFIRMADA')
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
        
        $termino = $this->get('q') ?? $this->get('term') ?? '';
        $limit = min(20, (int)($this->get('limit') ?? 10));
        
        if (strlen($termino) < 2) {
            $this->renderJson([]);
            return;
        }
        
        try {
            // Búsqueda parcial por nombre/apellido + exacta por identificación via hash
            $idHash = \DataProtection::blindIndex($termino);
            $sql = "
                SELECT 
                    cli_cliente_id as id,
                    cli_nombres,
                    cli_apellidos,
                    cli_identificacion,
                    cli_email,
                    cli_tipo_cliente
                FROM clientes
                WHERE cli_tenant_id = ?
                AND cli_estado = 'A'
                AND (
                    cli_nombres LIKE ? OR 
                    cli_apellidos LIKE ? OR 
                    cli_identificacion_hash = ? OR
                    CONCAT(cli_nombres, ' ', cli_apellidos) LIKE ?
                )
                ORDER BY cli_apellidos, cli_nombres
                LIMIT ?
            ";
            $like = "%{$termino}%";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $_SESSION['tenant_id'],
                $like, $like, $idHash, $like,
                $limit
            ]);
            
            // Descifrar y formatear resultados
            $rows = \DataProtection::decryptRows('clientes', $stmt->fetchAll(\PDO::FETCH_ASSOC));
            $results = [];
            foreach ($rows as $r) {
                $results[] = [
                    'id' => $r['id'],
                    'text' => trim($r['cli_nombres'] . ' ' . $r['cli_apellidos']),
                    'cli_identificacion' => $r['cli_identificacion'],
                    'cli_email' => $r['cli_email'],
                    'cli_tipo_cliente' => $r['cli_tipo_cliente'],
                ];
            }
            $this->renderJson($results);
            
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
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? \DataProtection::decryptRow('clientes', $row) : false;
    }
    
    /**
     * Obtener reservas del cliente
     */
    private function getReservasCliente($clienteId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT r.reserva_id, r.fecha_reserva, r.hora_inicio, r.hora_fin,
                   r.precio_total as total, r.estado, r.estado_pago,
                   c.nombre as cancha_nombre
            FROM reservas r
            LEFT JOIN canchas c ON r.instalacion_id = c.cancha_id
            WHERE r.cliente_id = ?
            ORDER BY r.fecha_reserva DESC, r.hora_inicio DESC
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
            SELECT p.rpa_pago_id, p.rpa_monto, p.rpa_metodo_pago,
                   p.rpa_fecha, p.rpa_estado, p.rpa_referencia,
                   p.rpa_reserva_id
            FROM instalaciones_reserva_pagos p
            INNER JOIN instalaciones_reservas r ON p.rpa_reserva_id = r.res_reserva_id
            WHERE r.res_cliente_id = ? AND p.rpa_estado = 'COMPLETADO'
            ORDER BY p.rpa_fecha DESC
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
            SELECT abo_abono_id, abo_saldo, abo_total_recargado,
                   abo_total_consumido, abo_estado, abo_fecha_registro
            FROM instalaciones_abonos
            WHERE abo_cliente_id = ? AND abo_estado = 'ACTIVO'
            ORDER BY abo_fecha_registro DESC
        ");
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener entradas compradas por el cliente
     */
    private function getEntradasCliente($clienteId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT ent_entrada_id, ent_codigo, ent_cantidad, ent_monto_total,
                   ent_fecha, ent_estado, ent_metodo_pago
            FROM instalaciones_entradas
            WHERE ent_cliente_id = ? AND ent_estado = 'ACTIVA'
            ORDER BY ent_fecha DESC
            LIMIT ?
        ");
        $stmt->execute([$clienteId, $limit]);
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
        $idHash = \DataProtection::blindIndex($data['identificacion']);
        $sql = "SELECT cli_cliente_id FROM clientes WHERE cli_tenant_id = ? AND cli_identificacion_hash = ?";
        $params = [$_SESSION['tenant_id'], $idHash];
        
        if ($excludeId) {
            $sql .= " AND cli_cliente_id != ?";
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
     * Verificar permiso del usuario actual
     */
    protected function checkPermission($accion) {
        $permisos = $_SESSION['modulo_activo']['permisos'] ?? [];
        
        // Super admin siempre tiene acceso
        if (($_SESSION['rol_codigo'] ?? '') === 'SUPERADMIN') {
            return true;
        }
        
        if (empty($permisos[$accion])) {
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
