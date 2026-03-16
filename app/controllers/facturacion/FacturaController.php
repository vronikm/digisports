<?php
/**
 * DigiSports - Controlador de Facturación
 * Gestión de facturas: creación, emisión, consulta.
 *
 * @package DigiSports\Controllers\Facturacion
 * @version 2.0.0
 */

namespace App\Controllers\Facturacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class FacturaController extends \App\Controllers\ModuleController {

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'facturacion';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LISTADO
    // ─────────────────────────────────────────────────────────────────────────

    public function index() {
        $this->authorize('ver', 'facturacion');

        try {
            $estado  = $this->get('estado') ?? '';
            $pagina  = max(1, (int)($this->get('pagina') ?? 1));
            $perPage = 15;
            $offset  = ($pagina - 1) * $perPage;

            $params = [$this->tenantId];
            $where  = 'WHERE f.fac_tenant_id = ?';
            if (!empty($estado)) {
                $where   .= ' AND f.fac_estado = ?';
                $params[] = $estado;
            }

            $stmt = $this->db->prepare("SELECT COUNT(f.fac_id) FROM facturacion_facturas f $where");
            $stmt->execute($params);
            $totalRegistros = (int)$stmt->fetchColumn();

            $stmt = $this->db->prepare("
                SELECT f.*,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS nombre_cliente,
                       fp.fpa_nombre AS forma_pago_nombre
                FROM facturacion_facturas f
                LEFT JOIN clientes c  ON f.fac_cliente_id    = c.cli_cliente_id
                LEFT JOIN facturacion_formas_pago fp ON f.fac_forma_pago_id = fp.fpa_id
                $where
                ORDER BY f.fac_fecha_creacion DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute(array_merge($params, [$perPage, $offset]));
            $facturas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['facturas']        = $facturas;
            $this->viewData['totalRegistros']  = $totalRegistros;
            $this->viewData['pagina']          = $pagina;
            $this->viewData['totalPaginas']    = (int)ceil($totalRegistros / $perPage);
            $this->viewData['estado']          = $estado;
            $this->viewData['csrf_token']      = \Security::generateCsrfToken();
            $this->viewData['title']           = 'Facturas';
            $this->renderModule('facturacion/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError('Error al listar facturas: ' . $e->getMessage());
            $this->error('Error al cargar las facturas');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREAR — formulario
    // ─────────────────────────────────────────────────────────────────────────

    public function crear() {
        $this->authorize('crear', 'facturacion');

        $origen_modulo = $this->get('origen_modulo') ?: 'libre';
        $origen_id     = (int)($this->get('origen_id') ?? 0);

        try {
            // Verificar duplicado de origen
            if ($origen_modulo !== 'libre' && $origen_id > 0) {
                $stmt = $this->db->prepare("
                    SELECT fac_id FROM facturacion_facturas
                    WHERE fac_origen_modulo = ? AND fac_origen_id = ? AND fac_tenant_id = ?
                ");
                $stmt->execute([$origen_modulo, $origen_id, $this->tenantId]);
                if ($stmt->fetch()) {
                    setFlashMessage('warning', 'Ya existe una factura para este origen.');
                    redirect('facturacion', 'factura', 'index');
                    exit;
                }
            }

            // Configuración fiscal del tenant
            $config = $this->cargarConfigFiscal();

            // Número de factura (preview — se confirma atómicamente en guardar)
            $numPreview = $this->previsualizarNumero($config);

            // Clientes activos (descifrados)
            $clientes = $this->obtenerClientes();

            // Formas de pago — filtradas por tenant
            $stmt = $this->db->prepare("
                SELECT fpa_id, fpa_nombre, fpa_codigo_sri
                FROM facturacion_formas_pago
                WHERE fpa_estado = 'ACTIVO'
                  AND fpa_tenant_id = ?
                ORDER BY fpa_nombre
            ");
            $stmt->execute([$this->tenantId]);
            $formas_pago = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['origen_modulo'] = $origen_modulo;
            $this->viewData['origen_id']     = $origen_id;
            $this->viewData['config']        = $config;
            $this->viewData['num_preview']   = $numPreview;
            $this->viewData['clientes']      = $clientes;
            $this->viewData['formas_pago']   = $formas_pago;
            $this->viewData['csrf_token']    = \Security::generateCsrfToken();
            $this->viewData['title']         = 'Nueva Factura';
            $this->renderModule('facturacion/crear', $this->viewData);

        } catch (\Exception $e) {
            $this->logError('Error al preparar crear factura: ' . $e->getMessage());
            $this->error('Error al preparar el formulario');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GUARDAR — procesar POST
    // ─────────────────────────────────────────────────────────────────────────

    public function guardar() {
        $this->authorize('crear', 'facturacion');

        if (!$this->isPost()) {
            $this->error('Método no permitido', 405);
        }
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }

        try {
            $origen_modulo    = $this->post('origen_modulo') ?: 'libre';
            $origen_id        = (int)($this->post('origen_id') ?? 0);
            $cliente_id       = (int)($this->post('cliente_id') ?? 0);
            $fecha_emision    = $this->post('fecha_emision')    ?: date('Y-m-d H:i:s');
            $fecha_vcto       = $this->post('fecha_vencimiento') ?: null;
            $forma_pago_id    = (int)($this->post('forma_pago_id') ?? 0);
            $descuento_global = max(0, (float)($this->post('descuento') ?? 0));
            $observaciones    = trim($_POST['observaciones'] ?? '');
            // Leer lineas_json sin sanitizar (htmlspecialchars rompería el JSON)
            // Es seguro: se decodifica con json_decode y cada campo se usa
            // en prepared statements o se castea a tipo numérico.
            $lineas_json = $_POST['lineas_json'] ?? '[]';

            $lineas = json_decode($lineas_json, true) ?: [];

            // ── Validaciones ─────────────────────────────────────────────────
            $errores = [];
            if ($cliente_id < 1)       $errores[] = 'Debe seleccionar un cliente.';
            if (empty($lineas))        $errores[] = 'La factura debe tener al menos un ítem.';
            if ($forma_pago_id < 1)    $errores[] = 'Seleccione una forma de pago.';

            if (!empty($errores)) {
                $this->error(implode(' ', $errores));
            }

            // ── Generar número (atómico) ──────────────────────────────────────
            $config      = $this->cargarConfigFiscal();
            $establecim  = $config['cfg_codigo_establecimiento'] ?? '001';
            $puntoEmis   = $config['cfg_punto_emision']          ?? '001';
            $secuencial  = $this->siguienteSecuencial($establecim, $puntoEmis);
            $fac_numero  = $establecim . '-' . $puntoEmis . '-' . str_pad($secuencial, 9, '0', STR_PAD_LEFT);

            // ── Calcular totales ──────────────────────────────────────────────
            $subtotal_0   = 0;
            $subtotal_iva = 0;
            $iva_total    = 0;

            foreach ($lineas as &$lin) {
                $lin['cantidad']      = max(0, (float)($lin['cantidad']      ?? 1));
                $lin['precio']        = max(0, (float)($lin['precio']        ?? 0));
                $lin['descuento_lin'] = max(0, (float)($lin['descuento_lin'] ?? 0));
                $lin['pct_iva']       = in_array((float)($lin['pct_iva'] ?? 15), [0, 12, 14, 15])
                                            ? (float)$lin['pct_iva'] : 15;

                $base = $lin['cantidad'] * $lin['precio'] - $lin['descuento_lin'];
                $iva  = $base * ($lin['pct_iva'] / 100);

                $lin['subtotal']  = $base;
                $lin['iva_linea'] = $iva;

                if ($lin['pct_iva'] > 0) {
                    $subtotal_iva += $base;
                    $iva_total    += $iva;
                } else {
                    $subtotal_0   += $base;
                }
            }
            unset($lin);

            $subtotal = $subtotal_0 + $subtotal_iva;
            $total    = $subtotal + $iva_total - $descuento_global;

            // ── Insertar cabecera ─────────────────────────────────────────────
            $stmt = $this->db->prepare("
                INSERT INTO facturacion_facturas (
                    fac_tenant_id, fac_numero, fac_origen_modulo, fac_origen_id,
                    fac_cliente_id, fac_usuario_id,
                    fac_fecha_emision, fac_fecha_vencimiento,
                    fac_subtotal, fac_descuento, fac_iva, fac_total,
                    fac_forma_pago_id, fac_estado, fac_observaciones
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,'EMITIDA',?)
            ");
            $stmt->execute([
                $this->tenantId,
                $fac_numero,
                $origen_modulo,
                $origen_id > 0 ? $origen_id : null,
                $cliente_id,
                $this->userId,
                $fecha_emision,
                $fecha_vcto,
                $subtotal,
                $descuento_global,
                $iva_total,
                $total,
                $forma_pago_id > 0 ? $forma_pago_id : null,
                $observaciones,
            ]);
            $factura_id = (int)$this->db->lastInsertId();

            // ── Insertar líneas ───────────────────────────────────────────────
            $stmtLin = $this->db->prepare("
                INSERT INTO facturacion_lineas (
                    lin_factura_id, lin_codigo, lin_descripcion,
                    lin_cantidad, lin_precio_unitario, lin_descuento,
                    lin_porcentaje_iva, lin_total
                ) VALUES (?,?,?,?,?,?,?,?)
            ");
            foreach ($lineas as $lin) {
                $descripcion = trim($lin['descripcion'] ?? '');
                if (!empty($lin['periodo'])) {
                    $descripcion .= ' | ' . strtoupper(trim($lin['periodo']));
                }
                $stmtLin->execute([
                    $factura_id,
                    strtoupper(trim($lin['codigo'] ?? '')),
                    $descripcion,
                    $lin['cantidad'],
                    $lin['precio'],
                    $lin['descuento_lin'],
                    $lin['pct_iva'],
                    $lin['subtotal'] + $lin['iva_linea'],
                ]);
            }

            // ── Registrar pago ────────────────────────────────────────────────
            // forma_pago_id es obligatorio en el formulario, por lo que siempre
            // existe en este punto. Se registra el pago completo y la factura
            // queda en estado EMITIDA → PAGADA automáticamente.
            $stmtPago = $this->db->prepare("
                INSERT INTO facturacion_pagos
                    (pag_factura_id, pag_usuario_id, pag_monto,
                     pag_forma_pago_id, pag_fecha, pag_estado)
                VALUES (?, ?, ?, ?, ?, 'CONFIRMADO')
            ");
            $fechaPago = date('Y-m-d', strtotime($fecha_emision));
            $stmtPago->execute([$factura_id, $this->userId, $total, $forma_pago_id, $fechaPago]);

            $this->db->prepare("
                UPDATE facturacion_facturas
                SET fac_estado = 'PAGADA', fac_fecha_pago = NOW()
                WHERE fac_id = ?
            ")->execute([$factura_id]);

            // ── Auditoría ─────────────────────────────────────────────────────
            $this->audit('facturacion_facturas', $factura_id, 'INSERT', [], [
                'numero' => $fac_numero, 'total' => $total,
            ]);
            \Security::logSecurityEvent('FACTURA_CREATED', "Factura: {$fac_numero} | ID: {$factura_id}");

            $this->success(
                ['redirect' => url('facturacion', 'factura', 'ver', ['id' => $factura_id])],
                "Factura {$fac_numero} creada exitosamente"
            );

        } catch (\Exception $e) {
            $this->logError('Error guardar factura: ' . $e->getMessage());
            $this->error('Error al guardar la factura: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLIENTE INFO — AJAX
    // ─────────────────────────────────────────────────────────────────────────

    public function clienteInfo() {
        $this->authorize('crear', 'facturacion');

        $id = (int)($this->get('id') ?? 0);
        if ($id < 1) {
            $this->error('ID inválido', 400);
        }

        try {
            $stmt = $this->db->prepare("
                SELECT cli_cliente_id AS id,
                       cli_tipo_identificacion AS tipo_ident,
                       cli_identificacion      AS identificacion,
                       CONCAT(cli_nombres, ' ', cli_apellidos) AS razon_social,
                       cli_email      AS email,
                       cli_telefono   AS telefono,
                       cli_celular    AS celular,
                       cli_direccion  AS direccion
                FROM clientes
                WHERE cli_cliente_id = ? AND cli_tenant_id = ? AND cli_estado = 'A'
            ");
            $stmt->execute([$id, $this->tenantId]);
            $c = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$c) {
                $this->error('Cliente no encontrado', 404);
            }

            $c['identificacion'] = \DataProtection::decrypt($c['identificacion'] ?? null) ?? '';
            $c['email']          = \DataProtection::decrypt($c['email']          ?? null) ?? '';
            $c['telefono']       = \DataProtection::decrypt($c['telefono']       ?? null) ?? '';

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $c]);
            exit;

        } catch (\Exception $e) {
            $this->logError('clienteInfo: ' . $e->getMessage());
            $this->error('Error al obtener cliente', 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VER DETALLE
    // ─────────────────────────────────────────────────────────────────────────

    public function ver() {
        $this->authorize('ver', 'facturacion');

        $factura_id = (int)($this->get('id') ?? 0);
        if ($factura_id < 1) $this->error('Factura no válida');

        try {
            $stmt = $this->db->prepare("
                SELECT f.*,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS nombre_cliente,
                       c.cli_email        AS email_cliente,
                       c.cli_identificacion AS identificacion_cliente,
                       c.cli_tipo_identificacion AS tipo_ident_cliente,
                       c.cli_telefono     AS telefono_cliente,
                       c.cli_direccion    AS direccion_cliente,
                       fp.fpa_nombre      AS forma_pago_nombre,
                       fp.fpa_codigo_sri  AS forma_pago_codigo,
                       CONCAT(u.usu_nombres,' ',u.usu_apellidos) AS usuario_nombre
                FROM facturacion_facturas f
                LEFT JOIN clientes c                ON f.fac_cliente_id    = c.cli_cliente_id
                LEFT JOIN facturacion_formas_pago fp ON f.fac_forma_pago_id = fp.fpa_id
                LEFT JOIN seguridad_usuarios u      ON f.fac_usuario_id    = u.usu_usuario_id
                WHERE f.fac_id = ? AND f.fac_tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$factura) $this->error('Factura no encontrada');

            $factura['identificacion_cliente'] = \DataProtection::decrypt($factura['identificacion_cliente'] ?? null) ?? '';
            $factura['email_cliente']          = \DataProtection::decrypt($factura['email_cliente']          ?? null) ?? '';
            $factura['telefono_cliente']       = \DataProtection::decrypt($factura['telefono_cliente']       ?? null) ?? '';

            $stmt = $this->db->prepare("SELECT * FROM facturacion_lineas WHERE lin_factura_id = ? ORDER BY lin_id");
            $stmt->execute([$factura_id]);
            $lineas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare("
                SELECT p.*, fp.fpa_nombre AS forma_pago_nombre
                FROM facturacion_pagos p
                LEFT JOIN facturacion_formas_pago fp ON p.pag_forma_pago_id = fp.fpa_id
                WHERE p.pag_factura_id = ? ORDER BY p.pag_fecha DESC
            ");
            $stmt->execute([$factura_id]);
            $pagos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Factura electrónica asociada
            $stmt = $this->db->prepare("
                SELECT fac_id, fac_estado_sri, fac_numero_autorizacion, fac_mensaje_error
                FROM facturas_electronicas
                WHERE fac_factura_id = ? AND fac_tenant_id = ?
                ORDER BY fac_id DESC LIMIT 1
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $fe = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

            $this->viewData['factura']      = $factura;
            $this->viewData['lineas']       = $lineas;
            $this->viewData['pagos']        = $pagos;
            $this->viewData['fe']           = $fe;
            $this->viewData['config']       = $this->cargarConfigFiscal();
            $this->viewData['csrf_token']   = \Security::generateCsrfToken();
            $this->viewData['title']        = 'Factura ' . $factura['fac_numero'];
            $this->renderModule('facturacion/ver', $this->viewData);

        } catch (\Exception $e) {
            $this->logError('Error ver factura: ' . $e->getMessage());
            $this->error('Error al cargar la factura');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EMITIR — BORRADOR → EMITIDA
    // ─────────────────────────────────────────────────────────────────────────

    public function emitir() {
        $this->authorize('crear', 'facturacion');

        $factura_id = (int)($this->get('id') ?? 0);
        if ($factura_id < 1) $this->error('Factura no válida');

        try {
            $stmt = $this->db->prepare("SELECT * FROM facturacion_facturas WHERE fac_id = ? AND fac_tenant_id = ?");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$factura) $this->error('Factura no encontrada');
            if ($factura['fac_estado'] !== 'BORRADOR') $this->error('Solo se pueden emitir facturas en BORRADOR');

            // Si tiene forma de pago, registrar el pago y marcar como PAGADA
            $nuevoEstado = 'EMITIDA';
            if (!empty($factura['fac_forma_pago_id']) && (float)$factura['fac_total'] > 0) {
                $this->db->prepare("
                    INSERT INTO facturacion_pagos
                        (pag_factura_id, pag_usuario_id, pag_monto,
                         pag_forma_pago_id, pag_fecha, pag_estado)
                    SELECT ?, ?, ?, ?, CURDATE(), 'CONFIRMADO'
                    FROM DUAL
                    WHERE NOT EXISTS (
                        SELECT 1 FROM facturacion_pagos
                        WHERE pag_factura_id = ? AND pag_estado = 'CONFIRMADO'
                    )
                ")->execute([
                    $factura_id, $this->userId, $factura['fac_total'],
                    $factura['fac_forma_pago_id'], $factura_id,
                ]);
                $nuevoEstado = 'PAGADA';
            }

            $stmt = $this->db->prepare("
                UPDATE facturacion_facturas
                SET fac_estado = ?, fac_fecha_emision = NOW(),
                    fac_fecha_pago = IF(? = 'PAGADA', NOW(), NULL)
                WHERE fac_id = ?
            ");
            $stmt->execute([$nuevoEstado, $nuevoEstado, $factura_id]);

            $this->audit('facturacion_facturas', $factura_id, 'EMITTED',
                ['estado' => 'BORRADOR'], ['estado' => $nuevoEstado]);
            \Security::logSecurityEvent('FACTURA_EMITTED', "Factura ID: {$factura_id}");

            setFlashMessage('success', 'Factura emitida exitosamente');
            redirect('facturacion', 'factura', 'ver', ['id' => $factura_id]);

        } catch (\Exception $e) {
            $this->logError('Error emitir factura: ' . $e->getMessage());
            setFlashMessage('error', 'Error al emitir la factura: ' . $e->getMessage());
            redirect('facturacion', 'factura', 'ver', ['id' => $factura_id]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ANULAR
    // ─────────────────────────────────────────────────────────────────────────

    public function anular() {
        $this->authorize('eliminar', 'facturacion');

        if (!$this->isPost()) $this->error('Método no permitido', 405);
        if (!$this->validateCsrf()) $this->error('Token de seguridad inválido', 403);

        $factura_id = (int)($this->get('id') ?? 0);
        $motivo     = trim($this->post('motivo') ?? '');
        if ($factura_id < 1) $this->error('Factura no válida');

        try {
            $stmt = $this->db->prepare("SELECT * FROM facturacion_facturas WHERE fac_id = ? AND fac_tenant_id = ?");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$factura)                          $this->error('Factura no encontrada');
            if ($factura['fac_estado'] === 'ANULADA') $this->error('Esta factura ya está anulada');

            $stmt = $this->db->prepare("
                UPDATE facturacion_facturas
                SET fac_estado = 'ANULADA',
                    fac_observaciones = CONCAT(COALESCE(fac_observaciones,''), '\nAnulada: ', ?)
                WHERE fac_id = ?
            ");
            $stmt->execute([$motivo, $factura_id]);

            $this->audit('facturacion_facturas', $factura_id, 'VOIDED',
                ['estado' => $factura['fac_estado']], ['estado' => 'ANULADA', 'motivo' => $motivo]);
            \Security::logSecurityEvent('FACTURA_VOIDED', "Factura ID: {$factura_id}");

            $this->success(
                ['redirect' => url('facturacion', 'factura', 'ver', ['id' => $factura_id])],
                'Factura anulada exitosamente'
            );

        } catch (\Exception $e) {
            $this->logError('Error anular factura: ' . $e->getMessage());
            $this->error('Error al anular la factura: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REACTIVAR — ANULADA → BORRADOR
    // ─────────────────────────────────────────────────────────────────────────

    public function reactivar() {
        $this->authorize('eliminar', 'facturacion');

        if (!$this->isPost()) $this->error('Método no permitido', 405);
        if (!$this->validateCsrf()) $this->error('Token de seguridad inválido', 403);

        $factura_id = (int)($this->get('id') ?? 0);
        if ($factura_id < 1) $this->error('Factura no válida');

        try {
            $stmt = $this->db->prepare("SELECT * FROM facturacion_facturas WHERE fac_id = ? AND fac_tenant_id = ?");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$factura)                              $this->error('Factura no encontrada');
            if ($factura['fac_estado'] !== 'ANULADA')   $this->error('Solo se pueden reactivar facturas ANULADAS');

            $this->db->prepare("
                UPDATE facturacion_facturas SET fac_estado = 'BORRADOR' WHERE fac_id = ?
            ")->execute([$factura_id]);

            $this->audit('facturacion_facturas', $factura_id, 'REACTIVATED',
                ['estado' => 'ANULADA'], ['estado' => 'BORRADOR']);
            \Security::logSecurityEvent('FACTURA_REACTIVATED', "Factura ID: {$factura_id}");

            $this->success(
                ['redirect' => url('facturacion', 'factura', 'ver', ['id' => $factura_id])],
                'Factura reactivada exitosamente'
            );

        } catch (\Exception $e) {
            $this->logError('Error reactivar factura: ' . $e->getMessage());
            $this->error('Error al reactivar la factura: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDF — vista imprimible
    // ─────────────────────────────────────────────────────────────────────────

    public function pdf() {
        $this->authorize('ver', 'facturacion');

        $factura_id = (int)($this->get('id') ?? 0);
        if ($factura_id < 1) $this->error('Factura no válida');

        try {
            $stmt = $this->db->prepare("
                SELECT f.*,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS nombre_cliente,
                       c.cli_email           AS email_cliente,
                       c.cli_identificacion  AS identificacion_cliente,
                       c.cli_tipo_identificacion AS tipo_ident_cliente,
                       c.cli_telefono        AS telefono_cliente,
                       c.cli_direccion       AS direccion_cliente,
                       fp.fpa_nombre         AS forma_pago_nombre,
                       fp.fpa_codigo_sri     AS forma_pago_codigo
                FROM facturacion_facturas f
                LEFT JOIN clientes c                 ON f.fac_cliente_id    = c.cli_cliente_id
                LEFT JOIN facturacion_formas_pago fp  ON f.fac_forma_pago_id = fp.fpa_id
                WHERE f.fac_id = ? AND f.fac_tenant_id = ?
            ");
            $stmt->execute([$factura_id, $this->tenantId]);
            $factura = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$factura) $this->error('Factura no encontrada');

            $factura['identificacion_cliente'] = \DataProtection::decrypt($factura['identificacion_cliente'] ?? null) ?? '';
            $factura['email_cliente']          = \DataProtection::decrypt($factura['email_cliente']          ?? null) ?? '';
            $factura['telefono_cliente']       = \DataProtection::decrypt($factura['telefono_cliente']       ?? null) ?? '';

            $stmt = $this->db->prepare("SELECT * FROM facturacion_lineas WHERE lin_factura_id = ? ORDER BY lin_id");
            $stmt->execute([$factura_id]);
            $lineas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $config = $this->cargarConfigFiscal();

            // URL de retorno: referrer si es del mismo dominio, si no la vista ver
            $urlVolver = url('facturacion', 'factura', 'ver', ['id' => $factura_id]);
            $referer   = $_SERVER['HTTP_REFERER'] ?? '';
            if (!empty($referer)) {
                $host = $_SERVER['HTTP_HOST'] ?? '';
                if ($host && strpos($referer, $host) !== false) {
                    $urlVolver = $referer;
                }
            }

            $this->viewData['factura']    = $factura;
            $this->viewData['lineas']     = $lineas;
            $this->viewData['config']     = $config;
            $this->viewData['urlVolver']  = $urlVolver;
            $this->viewData['autoPrint']  = ($this->get('print') === '1');
            $this->viewData['title']      = 'Factura ' . $factura['fac_numero'];
            $this->renderModule('facturacion/pdf', $this->viewData);

        } catch (\Exception $e) {
            $this->logError('Error pdf factura: ' . $e->getMessage());
            $this->error('Error al generar el PDF');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    private function cargarConfigFiscal(): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM facturacion_configuracion WHERE cfg_tenant_id = ? LIMIT 1"
            );
            $stmt->execute([$this->tenantId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log('cargarConfigFiscal: ' . $e->getMessage());
            return [];
        }
    }

    private function obtenerClientes(): array {
        $stmt = $this->db->prepare("
            SELECT cli_cliente_id AS id,
                   cli_tipo_identificacion AS tipo_ident,
                   cli_identificacion      AS identificacion,
                   CONCAT(cli_nombres,' ',cli_apellidos) AS nombre,
                   cli_email    AS email,
                   cli_telefono AS telefono,
                   cli_direccion AS direccion
            FROM clientes
            WHERE cli_estado = 'A' AND cli_tenant_id = ?
            ORDER BY cli_nombres, cli_apellidos
        ");
        $stmt->execute([$this->tenantId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$c) {
            $c['identificacion'] = \DataProtection::decrypt($c['identificacion'] ?? null) ?? '';
            $c['email']          = \DataProtection::decrypt($c['email']          ?? null) ?? '';
            $c['telefono']       = \DataProtection::decrypt($c['telefono']       ?? null) ?? '';
        }
        unset($c);
        return $rows;
    }

    /**
     * Obtiene el siguiente número de secuencia sin incrementarlo (solo para preview).
     */
    private function previsualizarNumero(array $config): string {
        $est = $config['cfg_codigo_establecimiento'] ?? '001';
        $pto = $config['cfg_punto_emision']          ?? '001';
        try {
            $stmt = $this->db->prepare("
                SELECT sec_siguiente FROM facturacion_secuenciales
                WHERE sec_tenant_id = ? AND sec_establecimiento = ? AND sec_punto_emision = ?
                LIMIT 1
            ");
            $stmt->execute([$this->tenantId, $est, $pto]);
            $next = (int)($stmt->fetchColumn() ?: 1);
        } catch (\Exception $e) {
            error_log('previsualizarNumero: ' . $e->getMessage());
            $next = 1;
        }
        return $est . '-' . $pto . '-' . str_pad($next, 9, '0', STR_PAD_LEFT);
    }

    /**
     * Incrementa y devuelve el siguiente número de secuencia (atómico).
     */
    private function siguienteSecuencial(string $est, string $pto): int {
        // Asegurar que existe la fila
        $this->db->prepare("
            INSERT INTO facturacion_secuenciales
                (sec_tenant_id, sec_tipo_comprobante, sec_establecimiento, sec_punto_emision, sec_siguiente)
            VALUES (?, '01', ?, ?, 1)
            ON DUPLICATE KEY UPDATE sec_siguiente = sec_siguiente
        ")->execute([$this->tenantId, $est, $pto]);

        // Incremento atómico
        $this->db->prepare("
            UPDATE facturacion_secuenciales
            SET sec_siguiente = LAST_INSERT_ID(sec_siguiente + 1)
            WHERE sec_tenant_id = ? AND sec_establecimiento = ? AND sec_punto_emision = ?
        ")->execute([$this->tenantId, $est, $pto]);

        return (int)$this->db->query('SELECT LAST_INSERT_ID()')->fetchColumn();
    }

    /**
     * Obtener facturas por origen (AJAX)
     */
    public function obtenerPorOrigen() {
        $origen_modulo = $this->get('modulo');
        $origen_id     = (int)($this->get('origen_id') ?? 0);

        if (!$origen_modulo || $origen_id < 1) $this->error('Origen no válido');

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM facturacion_facturas
                WHERE fac_origen_modulo = ? AND fac_origen_id = ? AND fac_tenant_id = ?
                ORDER BY fac_fecha_emision DESC
            ");
            $stmt->execute([$origen_modulo, $origen_id, $this->tenantId]);
            $this->success($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            $this->logError('obtenerPorOrigen: ' . $e->getMessage());
            $this->error('Error al obtener facturas');
        }
    }
}
