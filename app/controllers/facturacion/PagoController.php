<?php
/**
 * DigiSports - Controlador de Pagos
 * Gestión de pagos y formas de pago
 * 
 * @package DigiSports\Controllers\Facturacion
 * @version 1.0.0
 */

namespace App\Controllers\Facturacion;

require_once BASE_PATH . '/app/controllers/BaseController.php';

class PagoController extends \BaseController {
    
    /**
     * Listar pagos
     */
    public function index() {
        try {
            $estado = $this->get('estado') ?? '';
            $pagina = (int)($this->get('pagina') ?? 1);
            $perPage = 15;
            $offset = ($pagina - 1) * $perPage;
            
            $query = "
                SELECT p.*, f.numero_factura, r.nombre_cliente,
                       fp.nombre as forma_pago_nombre
                FROM pagos p
                INNER JOIN facturas f ON p.factura_id = f.factura_id
                LEFT JOIN reservas r ON f.reserva_id = r.reserva_id
                LEFT JOIN formas_pago fp ON p.forma_pago_id = fp.forma_pago_id
                WHERE f.tenant_id = ?
            ";
            
            $params = [$this->tenantId];
            
            if (!empty($estado)) {
                $query .= " AND p.estado = ?";
                $params[] = $estado;
            }
            
            // Contar total
            $countQuery = "
                SELECT COUNT(DISTINCT p.pago_id) as total FROM pagos p
                INNER JOIN facturas f ON p.factura_id = f.factura_id
                WHERE f.tenant_id = ?
            ";
            $countParams = [$this->tenantId];
            
            if (!empty($estado)) {
                $countQuery .= " AND p.estado = ?";
                $countParams[] = $estado;
            }
            
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute($countParams);
            $totalRegistros = $stmt->fetch()['total'];
            
            $query .= " ORDER BY p.fecha_pago DESC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $pagos = $stmt->fetchAll();
            
            $this->viewData['pagos'] = $pagos;
            $this->viewData['totalRegistros'] = $totalRegistros;
            $this->viewData['pagina'] = $pagina;
            $this->viewData['totalPaginas'] = ceil($totalRegistros / $perPage);
            $this->viewData['estado'] = $estado;
            $this->viewData['title'] = 'Gestión de Pagos';
            $this->viewData['layout'] = 'main';
            
            $this->render('facturacion/pagos', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al listar pagos: " . $e->getMessage());
            $this->error('Error al cargar los pagos');
        }
    }
    
    /**
     * Crear pago para una factura
     */
    public function crear() {
        $factura_id = (int)$this->get('factura_id');
        
        if ($factura_id < 1) {
            $this->error('Factura no válida');
        }
        
        try {
            // Obtener factura
            $stmt = $this->db->prepare("
                SELECT f.*, r.nombre_cliente
                FROM facturas f
                LEFT JOIN reservas r ON f.reserva_id = r.reserva_id
                WHERE f.factura_id = ? AND f.tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch();
            
            if (!$factura) {
                $this->error('Factura no encontrada');
            }
            
            // Obtener total pagado
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(monto), 0) as total_pagado FROM pagos
                WHERE factura_id = ? AND estado = 'CONFIRMADO'
            ");
            $stmt->execute([$factura_id]);
            $total_pagado = $stmt->fetch()['total_pagado'];
            
            $monto_pendiente = $factura['total'] - $total_pagado;
            
            // Obtener formas de pago
            $stmt = $this->db->prepare("
                SELECT * FROM formas_pago 
                WHERE estado = 'ACTIVO'
                ORDER BY nombre
            ");
            $stmt->execute();
            $formas_pago = $stmt->fetchAll();
            
            $this->viewData['factura'] = $factura;
            $this->viewData['total_pagado'] = $total_pagado;
            $this->viewData['monto_pendiente'] = $monto_pendiente;
            $this->viewData['formas_pago'] = $formas_pago;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Registrar Pago';
            $this->viewData['layout'] = 'main';
            
            $this->render('facturacion/crear_pago', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al crear pago: " . $e->getMessage());
            $this->error('Error al crear el pago');
        }
    }
    
    /**
     * Guardar pago
     */
    public function guardar() {
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
                SELECT * FROM facturas WHERE factura_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch();
            
            if (!$factura) {
                $this->error('Factura no encontrada');
            }
            
            // Verificar que el monto no exceda el pendiente
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(monto), 0) as total_pagado FROM pagos
                WHERE factura_id = ? AND estado = 'CONFIRMADO'
            ");
            $stmt->execute([$factura_id]);
            $total_pagado = $stmt->fetch()['total_pagado'];
            
            $monto_pendiente = $factura['total'] - $total_pagado;
            
            if ($monto > $monto_pendiente) {
                $this->error("Monto excede lo pendiente ($" . number_format($monto_pendiente, 2) . ")");
            }
            
            // Crear pago
            $stmt = $this->db->prepare("
                INSERT INTO pagos (
                    factura_id, usuario_id,
                    monto, forma_pago_id, referencia_pago,
                    fecha_pago, estado, observaciones,
                    fecha_creacion
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
            
            if ($nuevo_total_pagado >= $factura['total']) {
                $stmt = $this->db->prepare("
                    UPDATE facturas
                    SET estado = 'PAGADA',
                        fecha_pago = NOW()
                    WHERE factura_id = ?
                ");
                $stmt->execute([$factura_id]);
            }
            
            // Auditoría
            $this->audit('pagos', $pago_id, 'INSERT', [], [
                'factura_id' => $factura_id,
                'monto' => $monto,
                'forma_pago_id' => $forma_pago_id
            ]);
            
            \Security::logSecurityEvent('PAGO_CREATED', "Pago ID: {$pago_id}, Factura: {$factura_id}");
            
            $this->success([
                'redirect' => url('facturacion', 'factura', 'ver', ['id' => $factura_id])
            ], 'Pago registrado exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al guardar pago: " . $e->getMessage());
            $this->error('Error al guardar el pago');
        }
    }
    
    /**
     * Anular pago
     */
    public function anular() {
        $pago_id = (int)$this->get('id');
        
        if ($pago_id < 1) {
            $this->error('Pago no válido');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, f.factura_id FROM pagos p
                INNER JOIN facturas f ON p.factura_id = f.factura_id
                WHERE p.pago_id = ? AND f.tenant_id = ?
            ");
            $stmt->execute([$pago_id, $this->tenantId]);
            $pago = $stmt->fetch();
            
            if (!$pago) {
                $this->error('Pago no encontrado');
            }
            
            if ($pago['estado'] === 'ANULADO') {
                $this->error('Este pago ya está anulado');
            }
            
            // Anular pago
            $stmt = $this->db->prepare("
                UPDATE pagos
                SET estado = 'ANULADO',
                    fecha_anulacion = NOW()
                WHERE pago_id = ?
            ");
            $stmt->execute([$pago_id]);
            
            // Actualizar estado de factura (volver a EMITIDA)
            $stmt = $this->db->prepare("
                SELECT f.*, COALESCE(SUM(p.monto), 0) as total_pagado
                FROM facturas f
                LEFT JOIN pagos p ON f.factura_id = p.factura_id 
                                 AND p.estado = 'CONFIRMADO' AND p.pago_id != ?
                WHERE f.factura_id = ?
                GROUP BY f.factura_id
            ");
            $stmt->execute([$pago_id, $pago['factura_id']]);
            $factura = $stmt->fetch();
            
            $nuevo_estado = ($factura['total_pagado'] >= $factura['total']) ? 'PAGADA' : 'EMITIDA';
            
            $stmt = $this->db->prepare("
                UPDATE facturas
                SET estado = ?
                WHERE factura_id = ?
            ");
            $stmt->execute([$nuevo_estado, $pago['factura_id']]);
            
            // Auditoría
            $this->audit('pagos', $pago_id, 'VOIDED',
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
