<?php
/**
 * DigiSports - Controlador de Facturación
 * Sistema de facturación electrónica con integración SRI Ecuador
 * 
 * @package DigiSports\Controllers\Facturacion
 * @version 1.0.0
 */

namespace App\Controllers\Facturacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class FacturaController extends \App\Controllers\ModuleController {
    
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'facturacion';
    }
    
    /**
     * Listar facturas (listado paginado)
     */
    public function index() {
        $this->authorize('ver', 'facturacion');
        
        try {
            $estado = $this->get('estado') ?? '';
            $pagina = (int)($this->get('pagina') ?? 1);
            $perPage = 15;
            $offset = ($pagina - 1) * $perPage;
            
            // Query base con la nueva estructura
            $query = "
                SELECT f.*, 
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) as nombre_cliente, 
                       c.cli_email as email_cliente,
                       COUNT(fl.lin_id) as cantidad_lineas
                FROM facturacion_facturas f
                LEFT JOIN clientes c ON f.fac_cliente_id = c.cli_cliente_id
                LEFT JOIN facturacion_lineas fl ON f.fac_id = fl.lin_factura_id
                WHERE f.fac_tenant_id = ?
            ";
            
            $params = [$this->tenantId];
            
            // Filtro por estado
            if (!empty($estado)) {
                $query .= " AND f.fac_estado = ?";
                $params[] = $estado;
            }
            
            // Contar total
            $countQuery = "SELECT COUNT(DISTINCT f.fac_id) as total FROM facturacion_facturas f WHERE f.fac_tenant_id = ?";
            $countParams = [$this->tenantId];
            
            if (!empty($estado)) {
                $countQuery .= " AND f.fac_estado = ?";
                $countParams[] = $estado;
            }
            
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute($countParams);
            $totalRegistros = $stmt->fetch()['total'];
            
            // Paginación
            $query .= " GROUP BY f.fac_id ORDER BY f.fac_fecha_emision DESC LIMIT ? OFFSET ?";
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
            $this->renderModule('facturacion/index', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al listar facturas: " . $e->getMessage());
            $this->error('Error al cargar las facturas');
        }
    }
    
    /**
     * Crear factura de forma generica (soporta sin origen o desde un modulo)
     */
    public function crear() {
        $this->authorize('crear', 'facturacion');
        
        $origen_modulo = $this->get('origen_modulo') ?? null;
        $origen_id = (int)$this->get('origen_id');
        
        $origen_datos = null;
        $lineas = [];
        
        try {
            // Si hay un origen, cargar datos dependiendo del modulo
            if ($origen_modulo && $origen_id > 0) {
                // Generico: verificar si ya existe factura para este origen
                $stmt = $this->db->prepare("
                    SELECT fac_id FROM facturacion_facturas 
                    WHERE fac_origen_modulo = ? AND fac_origen_id = ? AND fac_tenant_id = ?
                ");
                $stmt->execute([$origen_modulo, $origen_id, $this->tenantId]);
                
                if ($stmt->fetch()) {
                    $this->error('Ya existe una factura para este origen');
                }
                
                // Extraer info segun origen si es necesario
            }
            
            // Obtener configuración fiscal
            $config = null;
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM facturacion_configuracion
                    WHERE cfg_tenant_id = ?
                ");
                $stmt->execute([$this->tenantId]);
                $config = $stmt->fetch();
            } catch (\Exception $configEx) {
                // La tabla puede no existir aún
                error_log("facturacion_configuracion no disponible: " . $configEx->getMessage());
            }
            
            // Obtener clientes activos (para facturacion libre)
            $stmt = $this->db->prepare("
                SELECT cli_cliente_id as id, CONCAT(cli_nombres, ' ', cli_apellidos) as nombre, cli_identificacion as identificacion 
                FROM clientes WHERE cli_estado = 'A' AND cli_tenant_id = ?
            ");
            $stmt->execute([$this->tenantId]);
            $clientes = $stmt->fetchAll();
            
            // Descifrar campos sensibles del cliente (LOPDP)
            foreach ($clientes as &$cl) {
                $cl['identificacion'] = \DataProtection::decrypt($cl['identificacion'] ?? null);
            }
            unset($cl);
            
            $this->viewData['origen_modulo'] = $origen_modulo;
            $this->viewData['origen_id'] = $origen_id;
            $this->viewData['lineas'] = $lineas;
            $this->viewData['config'] = $config;
            $this->viewData['clientes'] = $clientes;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Crear Factura';
            $this->renderModule('facturacion/crear', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al crear factura: " . $e->getMessage());
            $this->error('Error al crear la factura');
        }
    }
    
    /**
     * Guardar nueva factura
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
            $origen_modulo = $this->post('origen_modulo') ?: null;
            $origen_id = (int)$this->post('origen_id');
            $cliente_id = (int)$this->post('cliente_id');
            $numero_factura = trim($this->post('numero_factura'));
            $fecha_emision = $this->post('fecha_emision');
            $fecha_vencimiento = $this->post('fecha_vencimiento');
            $forma_pago_id = (int)$this->post('forma_pago_id');
            $observaciones = trim($this->post('observaciones') ?? '');
            
            // Lineas vienen por POST
            $lineas_post = json_decode($this->post('lineas_json') ?? '[]', true);
            
            // Validaciones
            $errors = [];
            
            if ($cliente_id < 1) {
                $errors[] = 'Debe seleccionar un cliente';
            }
            
            if (empty($numero_factura)) {
                $errors[] = 'Número de factura requerido';
            }
            
            if (empty($fecha_emision) || empty($fecha_vencimiento)) {
                $errors[] = 'Fechas requeridas';
            }
            
            if ($forma_pago_id < 1) {
                $errors[] = 'Forma de pago requerida';
            }
            
            if (empty($lineas_post)) {
                $errors[] = 'La factura debe tener al menos una línea';
            }
            
            if (!empty($errors)) {
                $this->error(implode('. ', $errors));
            }
            
            // Calcular totales
            $subtotal = 0;
            $iva = 0;
            foreach ($lineas_post as $l) {
                $lin_sub = $l['cantidad'] * $l['precio_unitario'];
                $subtotal += $lin_sub;
                if (!empty($l['aplica_iva'])) {
                    $iva += ($lin_sub * 0.15); // IVA fijo 15% referencial, podria venir de config
                }
            }
            $total = $subtotal + $iva;
            
            // Crear factura
            $stmt = $this->db->prepare("
                INSERT INTO facturacion_facturas (
                    fac_tenant_id, fac_origen_modulo, fac_origen_id, fac_cliente_id, fac_usuario_id,
                    fac_numero, fac_fecha_emision, fac_fecha_vencimiento,
                    fac_subtotal, fac_iva, fac_total,
                    fac_forma_pago_id, fac_estado, fac_observaciones,
                    fac_fecha_creacion
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'BORRADOR', ?, NOW())
            ");
            
            $stmt->execute([
                $this->tenantId,
                $origen_modulo,
                $origen_id > 0 ? $origen_id : null,
                $cliente_id,
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
            
            foreach ($lineas_post as $linea) {
                $stmt = $this->db->prepare("
                    INSERT INTO facturacion_lineas (
                        lin_factura_id, lin_descripcion, lin_cantidad,
                        lin_precio_unitario, lin_subtotal, lin_aplica_iva, lin_total
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $lin_subtotal = $linea['cantidad'] * $linea['precio_unitario'];
                $lin_iva = !empty($linea['aplica_iva']) ? ($lin_subtotal * 0.15) : 0;
                
                $stmt->execute([
                    $factura_id,
                    $linea['descripcion'],
                    $linea['cantidad'],
                    $linea['precio_unitario'],
                    $lin_subtotal,
                    !empty($linea['aplica_iva']) ? 1 : 0,
                    $lin_subtotal + $lin_iva
                ]);
            }
            
            // Auditoría
            $this->audit('facturacion_facturas', $factura_id, 'INSERT', [], [
                'numero' => $numero_factura,
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
        $this->authorize('ver', 'facturacion');
        
        $factura_id = (int)$this->get('id');
        
        if ($factura_id < 1) {
            $this->error('Factura no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT f.*, 
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) as nombre_cliente, c.cli_email as email_cliente,
                       fp.fpa_nombre as forma_pago_nombre,
                       CONCAT(u.usu_nombres, ' ', u.usu_apellidos) as usuario_nombre
                FROM facturacion_facturas f
                LEFT JOIN clientes c ON f.fac_cliente_id = c.cli_cliente_id
                LEFT JOIN facturacion_formas_pago fp ON f.fac_forma_pago_id = fp.fpa_id
                LEFT JOIN seguridad_usuarios u ON f.fac_usuario_id = u.usu_usuario_id
                WHERE f.fac_id = ? AND f.fac_tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch();
            
            if (!$factura) {
                $this->error('Factura no encontrada');
            }
            
            // Obtener líneas
            $stmt = $this->db->prepare("
                SELECT * FROM facturacion_lineas WHERE lin_factura_id = ?
            ");
            $stmt->execute([$factura_id]);
            $lineas = $stmt->fetchAll();
            
            // Obtener pagos
            $stmt = $this->db->prepare("
                SELECT * FROM facturacion_pagos WHERE pag_factura_id = ? ORDER BY pag_fecha DESC
            ");
            $stmt->execute([$factura_id]);
            $pagos = $stmt->fetchAll();
            
            $this->viewData['factura'] = $factura;
            $this->viewData['lineas'] = $lineas;
            $this->viewData['pagos'] = $pagos;
            $this->viewData['title'] = 'Factura: ' . $factura['fac_numero'];
            $this->renderModule('facturacion/ver', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al ver factura: " . $e->getMessage());
            $this->error('Error al cargar la factura');
        }
    }
    
    /**
     * Emitir factura (cambiar estado a EMITIDA)
     */
    public function emitir() {
        $this->authorize('crear', 'facturacion');
        
        $factura_id = (int)$this->get('id');
        
        if ($factura_id < 1) {
            $this->error('Factura no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM facturacion_facturas WHERE fac_id = ? AND fac_tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch();
            
            if (!$factura) {
                $this->error('Factura no encontrada');
            }
            
            if ($factura['fac_estado'] !== 'BORRADOR') {
                $this->error('Solo se pueden emitir facturas en estado BORRADOR');
            }
            
            // Cambiar estado
            $stmt = $this->db->prepare("
                UPDATE facturacion_facturas 
                SET fac_estado = 'EMITIDA', 
                    fac_fecha_emision = NOW()
                WHERE fac_id = ?
            ");
            $stmt->execute([$factura_id]);
            
            // Auditoría
            $this->audit('facturacion_facturas', $factura_id, 'EMITTED', 
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
        $this->authorize('eliminar', 'facturacion');
        
        $factura_id = (int)$this->get('id');
        $motivo = trim($this->get('motivo') ?? '');
        
        if ($factura_id < 1) {
            $this->error('Factura no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM facturacion_facturas WHERE fac_id = ? AND fac_tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch();
            
            if (!$factura) {
                $this->error('Factura no encontrada');
            }
            
            if ($factura['fac_estado'] === 'ANULADA') {
                $this->error('Esta factura ya está anulada');
            }
            
            // Anular
            $stmt = $this->db->prepare("
                UPDATE facturacion_facturas 
                SET fac_estado = 'ANULADA',
                    fac_observaciones = CONCAT(COALESCE(fac_observaciones, ''), '\nAnulada: ', ?)
                WHERE fac_id = ?
            ");
            $stmt->execute([$motivo, $factura_id]);
            
            // Auditoría
            $this->audit('facturacion_facturas', $factura_id, 'VOIDED',
                        ['estado' => $factura['fac_estado']],
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
        $this->authorize('ver', 'facturacion');
        
        $factura_id = (int)$this->get('id');
        
        if ($factura_id < 1) {
            $this->error('Factura no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT f.*, CONCAT(c.cli_nombres, ' ', c.cli_apellidos) as nombre_cliente, c.cli_email as email_cliente,
                       fp.fpa_nombre as forma_pago_nombre
                FROM facturacion_facturas f
                LEFT JOIN clientes c ON f.fac_cliente_id = c.cli_cliente_id
                LEFT JOIN facturacion_formas_pago fp ON f.fac_forma_pago_id = fp.fpa_id
                WHERE f.fac_id = ? AND f.fac_tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch();
            
            if (!$factura) {
                $this->error('Factura no encontrada');
            }
            
            // Generar PDF (stub - implementar con TCPDF o similar)
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Factura_' . $factura['fac_numero'] . '.pdf"');
            
            // TODO: Implementar generación real de PDF
            echo "PDF Generation Stub";
            
            \Security::logSecurityEvent('FACTURA_PDF_GENERATED', "Factura ID: {$factura_id}");
            
        } catch (\Exception $e) {
            $this->logError("Error al generar PDF: " . $e->getMessage());
            $this->error('Error al generar el PDF');
        }
    }
    
    /**
     * Obtener facturas por origen (AJAX)
     */
    public function obtenerPorOrigen() {
        $origen_modulo = $this->get('modulo');
        $origen_id = (int)$this->get('origen_id');
        
        if (!$origen_modulo || $origen_id < 1) {
            $this->error('Origen no válido');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM facturacion_facturas 
                WHERE fac_origen_modulo = ? AND fac_origen_id = ? AND fac_tenant_id = ?
                ORDER BY fac_fecha_emision DESC
            ");
            $stmt->execute([$origen_modulo, $origen_id, $this->tenantId]);
            $facturas = $stmt->fetchAll();
            
            $this->success($facturas);
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener facturas: " . $e->getMessage());
            $this->error('Error al obtener facturas');
        }
    }
    
    // El método obtenerDetallesReserva() se elimina porque esta lógica
    // será centralizada en el formulario dinámico y vista, no requerida aquí en MVC base
}
