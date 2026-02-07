<?php
/**
 * DigiSports - Controlador de Gestión de Tenants
 * Solo accesible por Super Administradores
 * 
 * @package DigiSports\Controllers\Core
 * @version 1.0.0
 */

namespace App\Controllers\Core;

require_once BASE_PATH . '/app/controllers/BaseController.php';

class TenantController extends \BaseController {
    
    public function __construct() {
        parent::__construct();
        
        // Solo super admin puede acceder
        if (!isSuperAdmin()) {
            setFlashMessage('error', 'Acceso denegado');
            redirect('core', 'dashboard');
        }
    }
    
    /**
     * Listar todos los tenants
     */
    public function index() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    t.*,
                    p.nombre as plan_nombre,
                    p.precio_mensual,
                    COUNT(DISTINCT u.usuario_id) as usuarios_activos
                FROM tenants t
                LEFT JOIN planes_suscripcion p ON t.plan_id = p.plan_id
                LEFT JOIN usuarios u ON t.tenant_id = u.tenant_id AND u.estado = 'A'
                GROUP BY t.tenant_id
                ORDER BY t.fecha_registro DESC
            ");
            
            $stmt->execute();
            $tenants = $stmt->fetchAll();
            
            $this->viewData['tenants'] = $tenants;
            $this->viewData['layout'] = 'main';
            $this->viewData['title'] = 'Gestión de Clientes';
            $this->viewData['pageTitle'] = 'Administración de Clientes';
            
            $this->render('tenants/index', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al listar tenants: " . $e->getMessage());
            setFlashMessage('error', 'Error al cargar clientes');
            redirect('core', 'dashboard');
        }
    }
    
    /**
     * Ver detalles de un tenant
     */
    public function ver($tenantId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    t.*,
                    p.nombre as plan_nombre,
                    p.precio_mensual,
                    p.usuarios_incluidos,
                    p.sedes_incluidas
                FROM tenants t
                LEFT JOIN planes_suscripcion p ON t.plan_id = p.plan_id
                WHERE t.tenant_id = ?
            ");
            
            $stmt->execute([$tenantId]);
            $tenant = $stmt->fetch();
            
            if (!$tenant) {
                setFlashMessage('error', 'Cliente no encontrado');
                redirect('core', 'tenant');
            }
            
            // Obtener estadísticas del tenant
            $stmt = $this->db->prepare("SELECT * FROM v_dashboard_tenant WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);
            $stats = $stmt->fetch();
            
            // Obtener módulos activos
            $stmt = $this->db->prepare("
                SELECT 
                    m.nombre,
                    m.codigo,
                    tm.activo,
                    tm.fecha_activacion
                FROM tenant_modulos tm
                INNER JOIN modulos_sistema m ON tm.modulo_id = m.modulo_id
                WHERE tm.tenant_id = ?
            ");
            
            $stmt->execute([$tenantId]);
            $modulos = $stmt->fetchAll();
            
            $this->viewData['tenant'] = $tenant;
            $this->viewData['stats'] = $stats;
            $this->viewData['modulos'] = $modulos;
            $this->viewData['layout'] = 'main';
            $this->viewData['title'] = 'Detalles del Cliente';
            $this->viewData['pageTitle'] = $tenant['razon_social'];
            
            $this->render('tenants/ver', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al ver tenant: " . $e->getMessage());
            setFlashMessage('error', 'Error al cargar información');
            redirect('core', 'tenant');
        }
    }
    
    /**
     * Crear nuevo tenant
     */
    public function crear() {
        if ($this->isPost()) {
            return $this->guardarTenant();
        }
        
        // Cargar planes
            $stmt = $this->db->query("SELECT * FROM core_planes_suscripcion WHERE plan_estado = 'A' ORDER BY plan_precio_mensual ASC");
        $planes = $stmt->fetchAll();
        
        $this->viewData['planes'] = $planes;
        $this->viewData['csrf_token'] = Security::generateCsrfToken();
        $this->viewData['layout'] = 'main';
        $this->viewData['title'] = 'Nuevo Cliente';
        $this->viewData['pageTitle'] = 'Registrar Nuevo Cliente';
        
        $this->render('tenants/formulario', $this->viewData);
    }
    
    /**
     * Editar tenant
     */
    public function editar($tenantId) {
        if ($this->isPost()) {
            return $this->actualizarTenant($tenantId);
        }
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM tenants WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);
            $tenant = $stmt->fetch();
            
            if (!$tenant) {
                setFlashMessage('error', 'Cliente no encontrado');
                redirect('core', 'tenant');
            }
            
            // Cargar planes
            $stmt = $this->db->query("SELECT * FROM planes_suscripcion WHERE estado = 'A' ORDER BY precio_mensual ASC");
            $planes = $stmt->fetchAll();
            
            $this->viewData['tenant'] = $tenant;
            $this->viewData['planes'] = $planes;
            $this->viewData['csrf_token'] = Security::generateCsrfToken();
            $this->viewData['layout'] = 'main';
            $this->viewData['title'] = 'Editar Cliente';
            $this->viewData['pageTitle'] = 'Editar: ' . $tenant['razon_social'];
            
            $this->render('tenants/formulario', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al cargar tenant: " . $e->getMessage());
            setFlashMessage('error', 'Error al cargar información');
            redirect('core', 'tenant');
        }
    }
    
    /**
     * Guardar nuevo tenant
     */
    private function guardarTenant() {
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        $ruc = $this->post('ruc');
        $razonSocial = $this->post('razon_social');
        $nombreComercial = $this->post('nombre_comercial');
        $email = $this->post('email');
        $planId = $this->post('plan_id');
        
        // Validaciones
        if (!isValidRUC($ruc)) {
            $this->error('RUC inválido');
        }
        
        if (!isValidEmail($email)) {
            $this->error('Email inválido');
        }
        
        try {
            $this->beginTransaction();
            
            // Verificar RUC duplicado
            $stmt = $this->db->prepare("SELECT tenant_id FROM tenants WHERE ruc = ?");
            $stmt->execute([$ruc]);
            
            if ($stmt->fetch()) {
                $this->error('Ya existe un cliente con este RUC');
            }
            
            // Obtener plan
            $stmt = $this->db->prepare("SELECT * FROM planes_suscripcion WHERE plan_id = ?");
            $stmt->execute([$planId]);
            $plan = $stmt->fetch();
            
            if (!$plan) {
                $this->error('Plan no encontrado');
            }
            
            // Insertar tenant
            $stmt = $this->db->prepare("
                INSERT INTO tenants (
                    ruc, razon_social, nombre_comercial, email,
                    telefono, celular, direccion,
                    plan_id, fecha_inicio, fecha_vencimiento,
                    usuarios_permitidos, sedes_permitidas, almacenamiento_gb,
                    monto_mensual, estado, usuario_registro
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?, ?, ?, ?, 'A', ?)
            ");
            
            $stmt->execute([
                $ruc,
                $razonSocial,
                $nombreComercial,
                $email,
                $this->post('telefono'),
                $this->post('celular'),
                $this->post('direccion'),
                $planId,
                $plan['usuarios_incluidos'],
                $plan['sedes_incluidas'],
                $plan['almacenamiento_gb'],
                $plan['precio_mensual'],
                $this->userId
            ]);
            
            $tenantId = $this->db->lastInsertId();
            
            // Asignar módulos incluidos en el plan
                $modulosIncluidos = json_decode($plan['plan_modulos_incluidos'], true);
            
            foreach ($modulosIncluidos as $codigoModulo) {
                $stmt = $this->db->prepare("SELECT modulo_id FROM modulos_sistema WHERE codigo = ?");
                $stmt->execute([$codigoModulo]);
                $modulo = $stmt->fetch();
                
                if ($modulo) {
                    $stmt = $this->db->prepare("
                        INSERT INTO tenant_modulos (tenant_id, modulo_id, activo, fecha_activacion)
                        VALUES (?, ?, 'S', CURDATE())
                    ");
                    
                    $stmt->execute([$tenantId, $modulo['modulo_id']]);
                }
            }
            
            $this->commit();
            
            // Auditoría
            $this->audit('tenants', $tenantId, 'INSERT', [], ['ruc' => $ruc, 'razon_social' => $razonSocial]);
            
            $this->success([
                'redirect' => url('core', 'tenant', 'ver', [$tenantId])
            ], 'Cliente creado exitosamente');
            
        } catch (\Exception $e) {
            $this->rollback();
            $this->logError("Error al crear tenant: " . $e->getMessage());
            $this->error('Error al crear el cliente');
        }
    }
    
    /**
     * Actualizar tenant
     */
    private function actualizarTenant($tenantId) {
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        try {
            // Obtener datos actuales
            $stmt = $this->db->prepare("SELECT * FROM tenants WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);
            $tenantActual = $stmt->fetch();
            
            if (!$tenantActual) {
                $this->error('Cliente no encontrado');
            }
            
            $this->beginTransaction();
            
            // Actualizar tenant
            $stmt = $this->db->prepare("
                UPDATE tenants SET
                    razon_social = ?,
                    nombre_comercial = ?,
                    email = ?,
                    telefono = ?,
                    celular = ?,
                    direccion = ?,
                    plan_id = ?,
                    usuario_actualizacion = ?
                WHERE tenant_id = ?
            ");
            
            $stmt->execute([
                $this->post('razon_social'),
                $this->post('nombre_comercial'),
                $this->post('email'),
                $this->post('telefono'),
                $this->post('celular'),
                $this->post('direccion'),
                $this->post('plan_id'),
                $this->userId,
                $tenantId
            ]);
            
            $this->commit();
            
            // Auditoría
            $this->audit('tenants', $tenantId, 'UPDATE', 
                ['razon_social' => $tenantActual['razon_social']], 
                ['razon_social' => $this->post('razon_social')]
            );
            
            $this->success([
                'redirect' => url('core', 'tenant', 'ver', [$tenantId])
            ], 'Cliente actualizado exitosamente');
            
        } catch (\Exception $e) {
            $this->rollback();
            $this->logError("Error al actualizar tenant: " . $e->getMessage());
            $this->error('Error al actualizar el cliente');
        }
    }
    
    /**
     * Suspender tenant
     */
    public function suspender() {
        if (!$this->isPost()) {
            $this->error('Solicitud inválida');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        $tenantId = $this->post('tenant_id');
        $motivo = $this->post('motivo');
        
        try {
            $stmt = $this->db->prepare("
                UPDATE tenants SET
                    estado_suscripcion = 'SUSPENDIDA',
                    motivo_suspension = ?,
                    fecha_suspension = NOW()
                WHERE tenant_id = ?
            ");
            
            $stmt->execute([$motivo, $tenantId]);
            
            // Auditoría
            $this->audit('tenants', $tenantId, 'SUSPEND', [], ['motivo' => $motivo]);
            
            $this->success(null, 'Cliente suspendido exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al suspender tenant: " . $e->getMessage());
            $this->error('Error al suspender cliente');
        }
    }
    
    /**
     * Activar tenant
     */
    public function activar() {
        if (!$this->isPost()) {
            $this->error('Solicitud inválida');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        $tenantId = $this->post('tenant_id');
        
        try {
            $stmt = $this->db->prepare("
                UPDATE tenants SET
                    estado_suscripcion = 'ACTIVA',
                    motivo_suspension = NULL,
                    fecha_suspension = NULL
                WHERE tenant_id = ?
            ");
            
            $stmt->execute([$tenantId]);
            
            // Auditoría
            $this->audit('tenants', $tenantId, 'ACTIVATE', [], []);
            
            $this->success(null, 'Cliente activado exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al activar tenant: " . $e->getMessage());
            $this->error('Error al activar cliente');
        }
    }
}