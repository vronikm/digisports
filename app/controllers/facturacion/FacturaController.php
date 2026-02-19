<?php
/**
 * DigiSports - Controlador de Facturación
 * Sistema de facturación electrónica con integración SRI Ecuador
 * 
 * @package DigiSports\Controllers\Facturacion
 * @version 1.0.0
 */

namespace App\Controllers\Facturacion;

require_once BASE_PATH . '/app/controllers/BaseController.php';

class FacturaController extends \BaseController {
    
    /**
     * Listar facturas (listado paginado)
     */
    public function index() {
        try {
            $estado = $this->get('estado') ?? '';
            $pagina = (int)($this->get('pagina') ?? 1);
            $perPage = 15;
            $offset = ($pagina - 1) * $perPage;
            
            // Query base
            $query = "
                SELECT f.*, r.referencia as reserva_ref, 
                       r.nombre_cliente, r.email_cliente,
                       COUNT(fl.linea_id) as cantidad_lineas
                FROM facturas f
                LEFT JOIN reservas r ON f.reserva_id = r.reserva_id
                LEFT JOIN facturas_lineas fl ON f.factura_id = fl.factura_id
                WHERE f.tenant_id = ?
            ";
            
            $params = [$this->tenantId];
            
            // Filtro por estado
            if (!empty($estado)) {
                $query .= " AND f.estado = ?";
                $params[] = $estado;
            }
            
            // Contar total
            $countQuery = "SELECT COUNT(DISTINCT f.factura_id) as total FROM facturas WHERE tenant_id = ?";
            $countParams = [$this->tenantId];
            
            if (!empty($estado)) {
                $countQuery .= " AND estado = ?";
                $countParams[] = $estado;
            }
            
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute($countParams);
            $totalRegistros = $stmt->fetch()['total'];
            
            // Paginación
            $query .= " GROUP BY f.factura_id ORDER BY f.fecha_emision DESC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $facturas = $stmt->fetchAll();
            
            $this->viewData['facturas'] = $facturas;
            $this->viewData['totalRegistros'] = $totalRegistros;
            $this->viewData['pagina'] = $pagina;
            $this->viewData['totalPaginas'] = ceil($totalRegistros / $perPage);
            $this->viewData['estado'] = $estado;
            $this->viewData['title'] = 'Gestión de Facturas';
            $this->viewData['layout'] = 'main';
            
            $this->render('facturacion/index', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al listar facturas: " . $e->getMessage());
            $this->error('Error al cargar las facturas');
        }
    }
    
    /**
     * Crear factura desde reserva confirmada
     */
    public function crear() {
        $reserva_id = (int)$this->get('reserva_id');
        
        if ($reserva_id < 1) {
            $this->error('Reserva no válida');
        }
        
        try {
            // Obtener datos de reserva
            $stmt = $this->db->prepare("
                SELECT r.*, c.nombre as cancha_nombre, i.nombre as instalacion_nombre
                FROM reservas r
                INNER JOIN canchas c ON r.cancha_id = c.cancha_id
                INNER JOIN instalaciones i ON c.instalacion_id = i.instalacion_id
                WHERE r.reserva_id = ? AND r.tenant_id = ? 
                AND r.estado IN ('CONFIRMADA', 'COMPLETADA')
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $reserva = $stmt->fetch();
            
            if (!$reserva) {
                $this->error('Reserva no encontrada o no está confirmada');
            }
            
            // Verificar si ya existe factura para esta reserva
            $stmt = $this->db->prepare("
                SELECT factura_id FROM facturas 
                WHERE reserva_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            
            if ($stmt->fetch()) {
                $this->error('Ya existe una factura para esta reserva');
            }
            
            // Obtener líneas de reserva
            $lineas = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT rl.*, t.hora_inicio, t.hora_fin
                    FROM reservas_lineas rl
                    INNER JOIN tarifas t ON rl.tarifa_id = t.tarifa_id
                    WHERE rl.reserva_id = ?
                ");
                $stmt->execute([$reserva_id]);
                $lineas = $stmt->fetchAll();
            } catch (\Exception $e) {
                // Tabla puede no existir aún
            }
            
            // Si no hay líneas, generar una desde la reserva
            if (empty($lineas)) {
                $lineas = [[
                    'hora_inicio' => $reserva['hora_inicio'] ?? '',
                    'hora_fin'    => $reserva['hora_fin'] ?? '',
                    'precio_unitario' => $reserva['precio_base'] ?? $reserva['precio_total'] ?? 0,
                    'cantidad'    => 1,
                    'precio_total' => $reserva['precio_total'] ?? 0
                ]];
            }
            
            // Obtener configuración fiscal
            $stmt = $this->db->prepare("
                SELECT * FROM configuracion_fiscal
                WHERE tenant_id = ?
            ");
            $stmt->execute([$this->tenantId]);
            $config = $stmt->fetch();
            
            $this->viewData['reserva'] = $reserva;
            $this->viewData['lineas'] = $lineas;
            $this->viewData['config'] = $config;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Crear Factura';
            $this->viewData['layout'] = 'main';
            
            $this->render('facturacion/crear', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al crear factura: " . $e->getMessage());
            $this->error('Error al crear la factura');
        }
    }
    
    /**
     * Guardar nueva factura
     */
    public function guardar() {
        if (!$this->isPost()) {
            $this->error('Solicitud inválida');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        try {
            $reserva_id = (int)$this->post('reserva_id');
            $numero_factura = trim($this->post('numero_factura'));
            $fecha_emision = $this->post('fecha_emision');
            $fecha_vencimiento = $this->post('fecha_vencimiento');
            $forma_pago_id = (int)$this->post('forma_pago_id');
            $observaciones = trim($this->post('observaciones') ?? '');
            $incluir_iva = ($this->post('incluir_iva') === '1') ? 1 : 0;
            
            // Validaciones
            $errors = [];
            
            if ($reserva_id < 1) {
                $errors[] = 'Reserva no válida';
            }
            
            if (empty($numero_factura)) {
                $errors[] = 'Número de factura requerido';
            }
            
            if (empty($fecha_emision)) {
                $errors[] = 'Fecha de emisión requerida';
            }
            
            if (empty($fecha_vencimiento)) {
                $errors[] = 'Fecha de vencimiento requerida';
            }
            
            if ($forma_pago_id < 1) {
                $errors[] = 'Forma de pago requerida';
            }
            
            if (!empty($errors)) {
                $this->error(implode('. ', $errors));
            }
            
            // Obtener reserva
            $stmt = $this->db->prepare("
                SELECT r.*, c.nombre as cancha_nombre
                FROM reservas r
                INNER JOIN canchas c ON r.cancha_id = c.cancha_id
                WHERE r.reserva_id = ? AND r.tenant_id = ?
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $reserva = $stmt->fetch();
            
            if (!$reserva) {
                $this->error('Reserva no encontrada');
            }
            
            // Calcular IVA
            $subtotal = $reserva['precio_total'];
            $iva = $incluir_iva ? ($subtotal * 0.15) : 0; // 15% IVA Ecuador
            $total = $subtotal + $iva;
            
            // Crear factura
            $stmt = $this->db->prepare("
                INSERT INTO facturas (
                    tenant_id, reserva_id, usuario_id,
                    numero_factura, fecha_emision, fecha_vencimiento,
                    subtotal, iva, total,
                    forma_pago_id, estado, observaciones,
                    fecha_creacion
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'BORRADOR', ?, NOW())
            ");
            
            $stmt->execute([
                $this->tenantId,
                $reserva_id,
                $this->userId,
                $numero_factura,
                $fecha_emision,
                $fecha_vencimiento,
                $subtotal,
                $iva,
                $total,
                $forma_pago_id,
                $observaciones
            ]);
            
            $factura_id = $this->db->lastInsertId();
            
            // Crear líneas de factura desde líneas de reserva
            $lineas_reserva = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM reservas_lineas WHERE reserva_id = ?
                ");
                $stmt->execute([$reserva_id]);
                $lineas_reserva = $stmt->fetchAll();
            } catch (\Exception $e) {
                // Tabla puede no existir
            }
            
            // Si no hay líneas, crear una virtual
            if (empty($lineas_reserva)) {
                $lineas_reserva = [[
                    'cantidad' => 1,
                    'precio_unitario' => $reserva['precio_base'] ?? $reserva['precio_total'] ?? 0,
                    'precio_total' => $reserva['precio_total'] ?? 0
                ]];
            }
            
            foreach ($lineas_reserva as $linea) {
                $stmt = $this->db->prepare("
                    INSERT INTO facturas_lineas (
                        factura_id, descripcion, cantidad,
                        precio_unitario, total
                    ) VALUES (?, ?, ?, ?, ?)
                ");
                
                $descripcion = "Tarifa cancha " . $reserva['cancha_nombre'];
                
                $stmt->execute([
                    $factura_id,
                    $descripcion,
                    $linea['cantidad'],
                    $linea['precio_unitario'],
                    $linea['precio_total']
                ]);
            }
            
            // Auditoría
            $this->audit('facturas', $factura_id, 'INSERT', [], [
                'numero_factura' => $numero_factura,
                'reserva_id' => $reserva_id,
                'total' => $total
            ]);
            
            \Security::logSecurityEvent('FACTURA_CREATED', "Factura: {$numero_factura}");
            
            $this->success([
                'redirect' => url('facturacion', 'factura', 'ver', ['id' => $factura_id])
            ], 'Factura creada exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al guardar factura: " . $e->getMessage());
            $this->error('Error al guardar la factura');
        }
    }
    
    /**
     * Ver detalles de factura
     */
    public function ver() {
        $factura_id = (int)$this->get('id');
        
        if ($factura_id < 1) {
            $this->error('Factura no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT f.*, r.nombre_cliente, r.email_cliente,
                       fp.nombre as forma_pago_nombre,
                       u.nombre as usuario_nombre
                FROM facturas f
                LEFT JOIN reservas r ON f.reserva_id = r.reserva_id
                LEFT JOIN formas_pago fp ON f.forma_pago_id = fp.forma_pago_id
                LEFT JOIN usuarios u ON f.usuario_id = u.usuario_id
                WHERE f.factura_id = ? AND f.tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch();
            
            if (!$factura) {
                $this->error('Factura no encontrada');
            }
            
            // Obtener líneas
            $stmt = $this->db->prepare("
                SELECT * FROM facturas_lineas WHERE factura_id = ?
            ");
            $stmt->execute([$factura_id]);
            $lineas = $stmt->fetchAll();
            
            // Obtener pagos
            $stmt = $this->db->prepare("
                SELECT * FROM pagos WHERE factura_id = ? ORDER BY fecha_pago DESC
            ");
            $stmt->execute([$factura_id]);
            $pagos = $stmt->fetchAll();
            
            $this->viewData['factura'] = $factura;
            $this->viewData['lineas'] = $lineas;
            $this->viewData['pagos'] = $pagos;
            $this->viewData['title'] = 'Factura: ' . $factura['numero_factura'];
            $this->viewData['layout'] = 'main';
            
            $this->render('facturacion/ver', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al ver factura: " . $e->getMessage());
            $this->error('Error al cargar la factura');
        }
    }
    
    /**
     * Emitir factura (cambiar estado a EMITIDA)
     */
    public function emitir() {
        $factura_id = (int)$this->get('id');
        
        if ($factura_id < 1) {
            $this->error('Factura no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM facturas WHERE factura_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch();
            
            if (!$factura) {
                $this->error('Factura no encontrada');
            }
            
            if ($factura['estado'] !== 'BORRADOR') {
                $this->error('Solo se pueden emitir facturas en estado BORRADOR');
            }
            
            // Generar número SRI si está configurado
            $numero_sri = $factura['numero_factura'];
            
            // Cambiar estado
            $stmt = $this->db->prepare("
                UPDATE facturas 
                SET estado = 'EMITIDA', 
                    fecha_emision_sri = NOW(),
                    numero_sri = ?,
                    fecha_actualizacion = NOW()
                WHERE factura_id = ?
            ");
            $stmt->execute([$numero_sri, $factura_id]);
            
            // Auditoría
            $this->audit('facturas', $factura_id, 'EMITTED', 
                        ['estado' => 'BORRADOR'],
                        ['estado' => 'EMITIDA']);
            
            \Security::logSecurityEvent('FACTURA_EMITTED', "Factura ID: {$factura_id}");
            
            $this->success([
                'redirect' => url('facturacion', 'factura', 'ver', ['id' => $factura_id])
            ], 'Factura emitida exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al emitir factura: " . $e->getMessage());
            $this->error('Error al emitir la factura');
        }
    }
    
    /**
     * Anular factura
     */
    public function anular() {
        $factura_id = (int)$this->get('id');
        $motivo = trim($this->get('motivo') ?? '');
        
        if ($factura_id < 1) {
            $this->error('Factura no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM facturas WHERE factura_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch();
            
            if (!$factura) {
                $this->error('Factura no encontrada');
            }
            
            if ($factura['estado'] === 'ANULADA') {
                $this->error('Esta factura ya está anulada');
            }
            
            // Anular
            $stmt = $this->db->prepare("
                UPDATE facturas 
                SET estado = 'ANULADA',
                    motivo_anulacion = ?,
                    fecha_anulacion = NOW(),
                    fecha_actualizacion = NOW()
                WHERE factura_id = ?
            ");
            $stmt->execute([$motivo, $factura_id]);
            
            // Auditoría
            $this->audit('facturas', $factura_id, 'VOIDED',
                        ['estado' => $factura['estado']],
                        ['estado' => 'ANULADA', 'motivo' => $motivo]);
            
            \Security::logSecurityEvent('FACTURA_VOIDED', "Factura ID: {$factura_id}");
            
            $this->success([
                'redirect' => url('facturacion', 'factura', 'ver', ['id' => $factura_id])
            ], 'Factura anulada');
            
        } catch (\Exception $e) {
            $this->logError("Error al anular factura: " . $e->getMessage());
            $this->error('Error al anular la factura');
        }
    }
    
    /**
     * Generar PDF de factura
     */
    public function pdf() {
        $factura_id = (int)$this->get('id');
        
        if ($factura_id < 1) {
            $this->error('Factura no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT f.*, r.nombre_cliente, r.email_cliente,
                       fp.nombre as forma_pago_nombre
                FROM facturas f
                LEFT JOIN reservas r ON f.reserva_id = r.reserva_id
                LEFT JOIN formas_pago fp ON f.forma_pago_id = fp.forma_pago_id
                WHERE f.factura_id = ? AND f.tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch();
            
            if (!$factura) {
                $this->error('Factura no encontrada');
            }
            
            // Obtener líneas
            $stmt = $this->db->prepare("
                SELECT * FROM facturas_lineas WHERE factura_id = ?
            ");
            $stmt->execute([$factura_id]);
            $lineas = $stmt->fetchAll();
            
            // Obtener config
            $stmt = $this->db->prepare("
                SELECT * FROM configuracion_fiscal WHERE tenant_id = ?
            ");
            $stmt->execute([$this->tenantId]);
            $config = $stmt->fetch();
            
            // Generar PDF (stub - implementar con TCPDF o similar)
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Factura_' . $factura['numero_factura'] . '.pdf"');
            
            // TODO: Implementar generación real de PDF
            echo "PDF Generation Stub";
            
            \Security::logSecurityEvent('FACTURA_PDF_GENERATED', "Factura ID: {$factura_id}");
            
        } catch (\Exception $e) {
            $this->logError("Error al generar PDF: " . $e->getMessage());
            $this->error('Error al generar el PDF');
        }
    }
    
    /**
     * Obtener facturas por reserva (AJAX)
     */
    public function obtenerPorReserva() {
        $reserva_id = (int)$this->get('reserva_id');
        
        if ($reserva_id < 1) {
            $this->error('Reserva no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM facturas 
                WHERE reserva_id = ? AND tenant_id = ?
                ORDER BY fecha_emision DESC
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $facturas = $stmt->fetchAll();
            
            $this->success($facturas);
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener facturas: " . $e->getMessage());
            $this->error('Error al obtener facturas');
        }
    }
    
    /**
     * Obtener detalles de reserva para crear factura (AJAX)
     */
    public function obtenerDetallesReserva() {
        $reserva_id = (int)$this->get('id');
        
        if ($reserva_id < 1) {
            $this->error('Reserva no válida');
        }
        
        try {
            // Obtener reserva
            $stmt = $this->db->prepare("
                SELECT r.* FROM reservas r
                WHERE r.reserva_id = ? AND r.tenant_id = ?
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $reserva = $stmt->fetch();
            
            if (!$reserva) {
                $this->error('Reserva no encontrada');
            }
            
            // Obtener líneas
            $lineas = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT rl.*, t.nombre as descripcion FROM reservas_lineas rl
                    LEFT JOIN tarifas t ON rl.tarifa_id = t.tarifa_id
                    WHERE rl.reserva_id = ?
                ");
                $stmt->execute([$reserva_id]);
                $lineas = $stmt->fetchAll();
            } catch (\Exception $e) {
                // Tabla puede no existir
            }
            
            if (empty($lineas)) {
                $lineas = [[
                    'descripcion' => 'Reserva de cancha',
                    'cantidad' => 1,
                    'precio_unitario' => $reserva['precio_base'] ?? $reserva['precio_total'] ?? 0,
                    'precio_total' => $reserva['precio_total'] ?? 0,
                    'hora_inicio' => $reserva['hora_inicio'] ?? '',
                    'hora_fin' => $reserva['hora_fin'] ?? ''
                ]];
            }
            
            $this->success([
                'reserva' => $reserva,
                'lineas' => $lineas
            ]);
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener detalles: " . $e->getMessage());
            $this->error('Error al obtener los detalles');
        }
    }
}
