<?php
/**
 * DigiSports - Controlador de Pagos
 * Gestión de pagos y formas de pago
 * 
 * @package DigiSports\Controllers\Facturacion
 * @version 1.0.0
 */

namespace App\Controllers\Facturacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class PagoController extends \App\Controllers\ModuleController {
    
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'facturacion';
    }
    
    /**
     * Listar pagos
     */
    public function index() {
        $this->authorize('ver', 'facturacion');
        
        try {
            $estado = $this->get('estado') ?? '';
            $pagina = (int)($this->get('pagina') ?? 1);
            $perPage = 15;
            $offset = ($pagina - 1) * $perPage;
            
            $query = "
                SELECT p.*, f.fac_numero as numero_factura, 
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) as nombre_cliente,
                       fp.fpa_nombre as forma_pago_nombre
                FROM facturacion_pagos p
                INNER JOIN facturacion_facturas f ON p.pag_factura_id = f.fac_id
                LEFT JOIN clientes c ON f.fac_cliente_id = c.cli_cliente_id
                LEFT JOIN facturacion_formas_pago fp ON p.pag_forma_pago_id = fp.fpa_id
                WHERE f.fac_tenant_id = ?
            ";
            
            $params = [$this->tenantId];
            
            if (!empty($estado)) {
                $query .= " AND p.pag_estado = ?";
                $params[] = $estado;
            }
            
            // Contar total
            $countQuery = "
                SELECT COUNT(DISTINCT p.pag_id) as total FROM facturacion_pagos p
                INNER JOIN facturacion_facturas f ON p.pag_factura_id = f.fac_id
                WHERE f.fac_tenant_id = ?
            ";
            $countParams = [$this->tenantId];
            
            if (!empty($estado)) {
                $countQuery .= " AND p.pag_estado = ?";
                $countParams[] = $estado;
            }
            
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute($countParams);
            $totalRegistros = (int)($stmt->fetchColumn() ?: 0);

            $query .= " ORDER BY p.pag_fecha DESC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $pagos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->viewData['pagos'] = $pagos;
            $this->viewData['totalRegistros'] = $totalRegistros;
            $this->viewData['pagina'] = $pagina;
            $this->viewData['totalPaginas'] = ceil($totalRegistros / $perPage);
            $this->viewData['estado'] = $estado;
            $this->viewData['title'] = 'Gestión de Pagos';
            $this->renderModule('facturacion/pagos', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al listar pagos: " . $e->getMessage());
            $this->error('Error al cargar los pagos');
        }
    }
    
    /**
     * Crear pago para una factura
     */
    public function crear() {
        $this->authorize('crear', 'facturacion');
        
        $factura_id = (int)$this->get('factura_id');
        
        // Si no se proporcionó factura_id, mostrar lista de facturas pendientes de pago
        if ($factura_id < 1) {
            try {
                $stmt = $this->db->prepare("
                    SELECT f.fac_id, f.fac_numero, f.fac_fecha_emision, f.fac_total, f.fac_estado,
                           CONCAT(c.cli_nombres, ' ', c.cli_apellidos) as nombre_cliente,
                           COALESCE((SELECT SUM(p.pag_monto) FROM facturacion_pagos p 
                                     WHERE p.pag_factura_id = f.fac_id AND p.pag_estado = 'CONFIRMADO'), 0) as total_pagado
                    FROM facturacion_facturas f
                    LEFT JOIN clientes c ON f.fac_cliente_id = c.cli_cliente_id
                    WHERE f.fac_tenant_id = ? AND f.fac_estado IN ('EMITIDA', 'BORRADOR')
                    ORDER BY f.fac_fecha_emision DESC
                ");
                $stmt->execute([$this->tenantId]);
                $facturas_pendientes = $stmt->fetchAll();
                
                $this->viewData['facturas_pendientes'] = $facturas_pendientes;
                $this->viewData['title'] = 'Registrar Pago - Seleccionar Factura';
                $this->renderModule('facturacion/seleccionar_factura_pago', $this->viewData);
                return;
            } catch (\Exception $e) {
                $this->logError("Error al listar facturas para pago: " . $e->getMessage());
                $this->error('Error al cargar facturas pendientes');
            }
        }
        
        try {
            // Obtener factura
            $stmt = $this->db->prepare("
                SELECT f.*, CONCAT(c.cli_nombres, ' ', c.cli_apellidos) as nombre_cliente
                FROM facturacion_facturas f
                LEFT JOIN clientes c ON f.fac_cliente_id = c.cli_cliente_id
                WHERE f.fac_id = ? AND f.fac_tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch();
            
            if (!$factura) {
                $this->error('Factura no encontrada');
            }
            
            // Obtener total pagado
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(pag_monto), 0) as total_pagado FROM facturacion_pagos
                WHERE pag_factura_id = ? AND pag_estado = 'CONFIRMADO'
            ");
            $stmt->execute([$factura_id]);
            $total_pagado = $stmt->fetch()['total_pagado'];
            
            $monto_pendiente = $factura['fac_total'] - $total_pagado;
            
            // Obtener formas de pago
            $stmt = $this->db->prepare("
                SELECT * FROM facturacion_formas_pago 
                WHERE fpa_estado = 'ACTIVO' AND fpa_tenant_id = ?
                ORDER BY fpa_nombre
            ");
            $stmt->execute([$this->tenantId]);
            $formas_pago = $stmt->fetchAll();
            
            $this->viewData['factura'] = $factura;
            $this->viewData['total_pagado'] = $total_pagado;
            $this->viewData['monto_pendiente'] = $monto_pendiente;
            $this->viewData['formas_pago'] = $formas_pago;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Registrar Pago';
            $this->renderModule('facturacion/crear_pago', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al crear pago: " . $e->getMessage());
            $this->error('Error al crear el pago');
        }
    }
    
    /**
     * Guardar pago
     */
    public function guardar() {
        $this->authorize('crear', 'facturacion');
        
        if (!$this->isPost()) {
            $this->error('Solicitud inválida');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        try {
            $factura_id = (int)$this->post('factura_id');
            $monto = (float)$this->post('monto');
            $forma_pago_id = (int)$this->post('forma_pago_id');
            $referencia_pago = trim($this->post('referencia_pago') ?? '');
            $fecha_pago = $this->post('fecha_pago');
            $observaciones = trim($this->post('observaciones') ?? '');
            
            // Validaciones
            $errors = [];
            
            if ($factura_id < 1) {
                $errors[] = 'Factura no válida';
            }
            
            if ($monto <= 0) {
                $errors[] = 'Monto debe ser mayor a 0';
            }
            
            if ($forma_pago_id < 1) {
                $errors[] = 'Forma de pago requerida';
            }
            
            if (empty($fecha_pago)) {
                $errors[] = 'Fecha de pago requerida';
            }
            
            if (!empty($errors)) {
                $this->error(implode('. ', $errors));
            }
            
            // Obtener factura
            $stmt = $this->db->prepare("
                SELECT * FROM facturacion_facturas WHERE fac_id = ? AND fac_tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch();
            
            if (!$factura) {
                $this->error('Factura no encontrada');
            }
            
            // Verificar que el monto no exceda el pendiente
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(pag_monto), 0) as total_pagado FROM facturacion_pagos
                WHERE pag_factura_id = ? AND pag_estado = 'CONFIRMADO'
            ");
            $stmt->execute([$factura_id]);
            $total_pagado = $stmt->fetch()['total_pagado'];
            
            $monto_pendiente = $factura['fac_total'] - $total_pagado;
            
            if ($monto > $monto_pendiente) {
                $this->error("Monto excede lo pendiente ($" . number_format($monto_pendiente, 2) . ")");
            }
            
            // Crear pago
            $stmt = $this->db->prepare("
                INSERT INTO facturacion_pagos (
                    pag_factura_id, pag_usuario_id,
                    pag_monto, pag_forma_pago_id, pag_referencia,
                    pag_fecha, pag_estado, pag_observaciones,
                    pag_fecha_creacion
                ) VALUES (?, ?, ?, ?, ?, ?, 'CONFIRMADO', ?, NOW())
            ");
            
            $stmt->execute([
                $factura_id,
                $this->userId,
                $monto,
                $forma_pago_id,
                $referencia_pago,
                $fecha_pago,
                $observaciones
            ]);
            
            $pago_id = $this->db->lastInsertId();
            
            // Actualizar estado de factura si está pagada
            $nuevo_total_pagado = $total_pagado + $monto;
            
            if ($nuevo_total_pagado >= $factura['fac_total']) {
                $stmt = $this->db->prepare("
                    UPDATE facturacion_facturas
                    SET fac_estado = 'PAGADA',
                        fac_fecha_pago = NOW()
                    WHERE fac_id = ?
                ");
                $stmt->execute([$factura_id]);
            }
            
            // Auditoría
            $this->audit('facturacion_pagos', $pago_id, 'INSERT', [], [
                'factura_id' => $factura_id,
                'monto' => $monto,
                'forma_pago_id' => $forma_pago_id
            ]);
            
            \Security::logSecurityEvent('PAGO_CREATED', "Pago ID: {$pago_id}, Factura: {$factura_id}");

            setFlashMessage('success', 'Pago registrado exitosamente');
            redirect('facturacion', 'factura', 'ver', ['id' => $factura_id]);

        } catch (\Exception $e) {
            $this->logError("Error al guardar pago: " . $e->getMessage());
            setFlashMessage('error', 'Error al guardar el pago: ' . $e->getMessage());
            redirect('facturacion', 'pago', 'crear', ['factura_id' => $factura_id ?? 0]);
        }
    }
    
    /**
     * Anular pago
     */
    public function anular() {
        $this->authorize('eliminar', 'facturacion');
        
        $pago_id = (int)$this->get('id');
        
        if ($pago_id < 1) {
            $this->error('Pago no válido');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, f.fac_id as factura_id FROM facturacion_pagos p
                INNER JOIN facturacion_facturas f ON p.pag_factura_id = f.fac_id
                WHERE p.pag_id = ? AND f.fac_tenant_id = ?
            ");
            $stmt->execute([$pago_id, $this->tenantId]);
            $pago = $stmt->fetch();
            
            if (!$pago) {
                $this->error('Pago no encontrado');
            }
            
            if ($pago['pag_estado'] === 'ANULADO') {
                $this->error('Este pago ya está anulado');
            }
            
            // Anular pago
            $stmt = $this->db->prepare("
                UPDATE facturacion_pagos
                SET pag_estado = 'ANULADO',
                    pag_fecha_anulacion = NOW()
                WHERE pag_id = ?
            ");
            $stmt->execute([$pago_id]);
            
            // Actualizar estado de factura (volver a EMITIDA)
            $stmt = $this->db->prepare("
                SELECT f.*, COALESCE(SUM(p.pag_monto), 0) as total_pagado
                FROM facturacion_facturas f
                LEFT JOIN facturacion_pagos p ON f.fac_id = p.pag_factura_id 
                                 AND p.pag_estado = 'CONFIRMADO' AND p.pag_id != ?
                WHERE f.fac_id = ?
                GROUP BY f.fac_id
            ");
            $stmt->execute([$pago_id, $pago['factura_id']]);
            $factura = $stmt->fetch();
            
            $nuevo_estado = ($factura['total_pagado'] >= $factura['fac_total']) ? 'PAGADA' : 'EMITIDA';
            
            $stmt = $this->db->prepare("
                UPDATE facturacion_facturas
                SET fac_estado = ?
                WHERE fac_id = ?
            ");
            $stmt->execute([$nuevo_estado, $pago['factura_id']]);
            
            // Auditoría
            $this->audit('facturacion_pagos', $pago_id, 'VOIDED',
                        ['estado' => 'CONFIRMADO'],
                        ['estado' => 'ANULADO']);
            
            \Security::logSecurityEvent('PAGO_VOIDED', "Pago ID: {$pago_id}");
            
            $this->success([
                'redirect' => url('facturacion', 'factura', 'ver', ['id' => $pago['factura_id']])
            ], 'Pago anulado');
            
        } catch (\Exception $e) {
            $this->logError("Error al anular pago: " . $e->getMessage());
            $this->error('Error al anular el pago');
        }
    }
}
