<?php
/**
 * DigiSports Arena — Controlador de Pagos
 * Checkout, cobro mixto (monedero + efectivo/tarjeta), historial de pagos
 *
 * @package DigiSports\Controllers\Reservas
 * @version 1.0.0
 */

namespace App\Controllers\Reservas;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class PagoController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Arena';
    protected $moduloIcono  = 'fas fa-building';
    protected $moduloColor  = '#3B82F6';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'ARENA';
    }

    /* ═══════════════════════════════════════════
     * CHECKOUT — Pantalla de pago para una reserva
     * ═══════════════════════════════════════════ */
    public function checkout() {
        try {
            $reservaId = (int)($this->get('id') ?? 0);
            if ($reservaId < 1) {
                $this->error('Reserva no válida');
                return;
            }

            // Obtener reserva con datos del cliente e instalación
            $stmt = $this->db->prepare("
                SELECT r.*,
                       i.ins_nombre AS instalacion_nombre,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS cliente_nombre,
                       c.cli_email AS cliente_email,
                       c.cli_telefono AS cliente_telefono,
                       c.cli_cliente_id
                FROM instalaciones_reservas r
                INNER JOIN instalaciones i ON r.res_instalacion_id = i.ins_instalacion_id
                INNER JOIN clientes c ON r.res_cliente_id = c.cli_cliente_id
                WHERE r.res_reserva_id = ? AND r.res_tenant_id = ?
            ");
            $stmt->execute([$reservaId, $this->tenantId]);
            $reserva = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$reserva) {
                $this->error('Reserva no encontrada');
                return;
            }

            // Descifrar campos sensibles del cliente
            if (!empty($reserva['cliente_email'])) {
                $reserva['cliente_email'] = \DataProtection::decrypt($reserva['cliente_email']);
            }
            if (!empty($reserva['cliente_telefono'])) {
                $reserva['cliente_telefono'] = \DataProtection::decrypt($reserva['cliente_telefono']);
            }

            // Saldo del monedero del cliente
            $saldoMonedero = $this->getSaldoCliente($reserva['cli_cliente_id']);

            // Historial de pagos de esta reserva
            $pagos = $this->getPagosReserva($reservaId);

            // Calcular pendiente real
            $totalPagado = 0;
            foreach ($pagos as $p) {
                if ($p['pag_estado'] === 'COMPLETADO') {
                    $totalPagado += (float)$p['pag_monto'];
                }
            }
            $saldoPendiente = max(0, (float)$reserva['res_precio_total'] - $totalPagado - (float)$reserva['res_abono_utilizado']);

            $this->viewData['reserva']         = $reserva;
            $this->viewData['pagos']           = $pagos;
            $this->viewData['saldo_monedero']  = $saldoMonedero;
            $this->viewData['total_pagado']    = $totalPagado;
            $this->viewData['saldo_pendiente'] = $saldoPendiente;
            $this->viewData['csrf_token']      = \Security::generateCsrfToken();
            $this->viewData['title']           = 'Cobrar Reserva #' . $reservaId;

            $this->renderModule('reservas/pagos/checkout', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error checkout: " . $e->getMessage());
            $this->error('Error al cargar el checkout');
        }
    }

    /* ═══════════════════════════════════════════
     * PROCESAR PAGO — Registra cobro en BD
     * Soporta: EFECTIVO, TARJETA, MONEDERO, MIXTO
     * ═══════════════════════════════════════════ */
    public function procesarPago() {
        try {
            $reservaId    = (int)($this->post('reserva_id') ?? 0);
            $formaPago    = trim($this->post('forma_pago') ?? '');
            $montoEfectivo = (float)($this->post('monto_efectivo') ?? 0);
            $montoMonedero = (float)($this->post('monto_monedero') ?? 0);
            $referencia    = trim($this->post('referencia') ?? '');
            $observaciones = trim($this->post('observaciones') ?? '');

            // Validaciones básicas
            if ($reservaId < 1) {
                $this->error('Reserva no válida');
                return;
            }

            $montoTotal = $montoEfectivo + $montoMonedero;
            if ($montoTotal <= 0) {
                $this->error('El monto a pagar debe ser mayor a $0');
                return;
            }

            if (!in_array($formaPago, ['EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'MONEDERO', 'MIXTO'])) {
                $this->error('Forma de pago no válida');
                return;
            }

            // Obtener reserva
            $stmt = $this->db->prepare("
                SELECT r.*, c.cli_cliente_id
                FROM instalaciones_reservas r
                INNER JOIN clientes c ON r.res_cliente_id = c.cli_cliente_id
                WHERE r.res_reserva_id = ? AND r.res_tenant_id = ?
            ");
            $stmt->execute([$reservaId, $this->tenantId]);
            $reserva = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$reserva) {
                $this->error('Reserva no encontrada');
                return;
            }

            // Calcular saldo pendiente real
            $stmtPagado = $this->db->prepare("
                SELECT COALESCE(SUM(pag_monto), 0) FROM instalaciones_reserva_pagos 
                WHERE pag_reserva_id = ? AND pag_estado = 'COMPLETADO'
            ");
            $stmtPagado->execute([$reservaId]);
            $yaPagado = (float)$stmtPagado->fetchColumn();
            $saldoPendiente = (float)$reserva['res_precio_total'] - $yaPagado - (float)$reserva['res_abono_utilizado'];

            if ($montoTotal > ($saldoPendiente + 0.01)) {
                $this->error('El monto ($' . number_format($montoTotal, 2) . ') excede el saldo pendiente ($' . number_format($saldoPendiente, 2) . ')');
                return;
            }

            // Validar saldo monedero si se usa
            if ($montoMonedero > 0) {
                $saldoDisponible = $this->getSaldoCliente($reserva['cli_cliente_id']);
                if ($montoMonedero > ($saldoDisponible + 0.01)) {
                    $this->error('Saldo de monedero insuficiente. Disponible: $' . number_format($saldoDisponible, 2));
                    return;
                }
            }

            // ─── INICIAR TRANSACCIÓN ───
            $this->db->beginTransaction();

            // 1. Registrar pago en instalaciones_reserva_pagos
            $tipoPago = ($montoMonedero > 0 && $montoEfectivo > 0) ? 'MIXTO' : $formaPago;
            $stmt = $this->db->prepare("
                INSERT INTO instalaciones_reserva_pagos
                (pag_tenant_id, pag_reserva_id, pag_monto, pag_tipo_pago, pag_forma_pago,
                 pag_referencia, pag_estado, pag_fecha_pago, pag_usuario_registro)
                VALUES (?, ?, ?, ?, ?, ?, 'COMPLETADO', NOW(), ?)
            ");
            $stmt->execute([
                $this->tenantId,
                $reservaId,
                $montoTotal,
                $tipoPago,
                $formaPago,
                $referencia ?: null,
                $this->userId
            ]);
            $pagoId = $this->db->lastInsertId();

            // 2. Si usó monedero, descontar del abono
            if ($montoMonedero > 0) {
                $this->descontarMonedero($reserva['cli_cliente_id'], $montoMonedero, $reservaId, $pagoId);
            }

            // 3. Actualizar la reserva
            $nuevoTotalPagado = $yaPagado + $montoEfectivo;
            $nuevoAbonoUtilizado = (float)$reserva['res_abono_utilizado'] + $montoMonedero;
            $nuevoSaldoPendiente = max(0, (float)$reserva['res_precio_total'] - $nuevoTotalPagado - $nuevoAbonoUtilizado);

            $estadoPago = 'PENDIENTE';
            if ($nuevoSaldoPendiente <= 0.01) {
                $estadoPago = 'PAGADO';
            } elseif (($nuevoTotalPagado + $nuevoAbonoUtilizado) > 0) {
                $estadoPago = 'PARCIAL';
            }

            $stmt = $this->db->prepare("
                UPDATE instalaciones_reservas
                SET res_monto_pagado = ?,
                    res_abono_utilizado = ?,
                    res_saldo_pendiente = ?,
                    res_estado_pago = ?
                WHERE res_reserva_id = ?
            ");
            $stmt->execute([$nuevoTotalPagado, $nuevoAbonoUtilizado, $nuevoSaldoPendiente, $estadoPago, $reservaId]);

            // 4. Si pago total y reserva PENDIENTE → confirmar automáticamente
            if ($estadoPago === 'PAGADO' && $reserva['res_estado'] === 'PENDIENTE') {
                $stmt = $this->db->prepare("
                    UPDATE instalaciones_reservas
                    SET res_estado = 'CONFIRMADA',
                        res_fecha_confirmacion = NOW()
                    WHERE res_reserva_id = ?
                ");
                $stmt->execute([$reservaId]);
            }

            $this->db->commit();

            // Auditoría
            $this->audit('reserva_pagos', $pagoId, 'PAGO_REGISTRADO', null, [
                'reserva_id' => $reservaId,
                'monto' => $montoTotal,
                'forma_pago' => $tipoPago,
                'monto_efectivo' => $montoEfectivo,
                'monto_monedero' => $montoMonedero,
                'estado_pago' => $estadoPago
            ]);

            $this->success([
                'redirect' => url('reservas', 'pago', 'comprobante') . '&id=' . $pagoId
            ], 'Pago registrado exitosamente — $' . number_format($montoTotal, 2));

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->logError("Error procesarPago: " . $e->getMessage());
            $this->error('Error al procesar el pago: ' . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════
     * COMPROBANTE — Recibo de pago
     * ═══════════════════════════════════════════ */
    public function comprobante() {
        try {
            $pagoId = (int)($this->get('id') ?? 0);
            if ($pagoId < 1) {
                $this->error('Pago no válido');
                return;
            }

            $stmt = $this->db->prepare("
                SELECT p.*,
                       r.res_reserva_id, r.res_fecha_reserva, r.res_hora_inicio, r.res_hora_fin,
                       r.res_precio_total, r.res_estado, r.res_estado_pago,
                       i.ins_nombre AS instalacion_nombre,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS cliente_nombre,
                       c.cli_email AS cliente_email,
                       c.cli_telefono AS cliente_telefono,
                       c.cli_identificacion AS cliente_identificacion
                FROM instalaciones_reserva_pagos p
                INNER JOIN instalaciones_reservas r ON p.pag_reserva_id = r.res_reserva_id
                INNER JOIN instalaciones i ON r.res_instalacion_id = i.ins_instalacion_id
                INNER JOIN clientes c ON r.res_cliente_id = c.cli_cliente_id
                WHERE p.pag_pago_id = ? AND p.pag_tenant_id = ?
            ");
            $stmt->execute([$pagoId, $this->tenantId]);
            $pago = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$pago) {
                $this->error('Pago no encontrado');
                return;
            }

            // Descifrar campos sensibles del cliente
            if (!empty($pago['cliente_email'])) {
                $pago['cliente_email'] = \DataProtection::decrypt($pago['cliente_email']);
            }
            if (!empty($pago['cliente_telefono'])) {
                $pago['cliente_telefono'] = \DataProtection::decrypt($pago['cliente_telefono']);
            }
            if (!empty($pago['cliente_identificacion'])) {
                $pago['cliente_identificacion'] = \DataProtection::decrypt($pago['cliente_identificacion']);
            }

            // Todos los pagos de esta reserva
            $pagosReserva = $this->getPagosReserva($pago['res_reserva_id']);

            $this->viewData['pago']           = $pago;
            $this->viewData['pagos_reserva']  = $pagosReserva;
            $this->viewData['title']          = 'Comprobante de Pago #' . $pagoId;

            $this->renderModule('reservas/pagos/comprobante', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error comprobante: " . $e->getMessage());
            $this->error('Error al cargar el comprobante');
        }
    }

    /* ═══════════════════════════════════════════
     * HISTORIAL — Todos los pagos del tenant
     * ═══════════════════════════════════════════ */
    public function index() {
        try {
            $buscar  = $this->post('buscar') ?? $this->get('buscar') ?? '';
            $desde   = $this->post('desde') ?? $this->get('desde') ?? date('Y-m-01');
            $hasta   = $this->post('hasta') ?? $this->get('hasta') ?? date('Y-m-d');
            $pagina  = max(1, (int)($this->post('pagina') ?? $this->get('pagina') ?? 1));
            $perPage = 20;
            $offset  = ($pagina - 1) * $perPage;

            $where = "p.pag_tenant_id = ?";
            $params = [$this->tenantId];

            if (!empty($desde)) {
                $where .= " AND DATE(p.pag_fecha_pago) >= ?";
                $params[] = $desde;
            }
            if (!empty($hasta)) {
                $where .= " AND DATE(p.pag_fecha_pago) <= ?";
                $params[] = $hasta;
            }
            if (!empty($buscar)) {
                $where .= " AND (CONCAT(c.cli_nombres,' ',c.cli_apellidos) LIKE ? OR p.pag_referencia LIKE ?)";
                $params[] = "%$buscar%";
                $params[] = "%$buscar%";
            }

            // Total
            $stmtCount = $this->db->prepare("
                SELECT COUNT(*) FROM instalaciones_reserva_pagos p
                INNER JOIN instalaciones_reservas r ON p.pag_reserva_id = r.res_reserva_id
                INNER JOIN clientes c ON r.res_cliente_id = c.cli_cliente_id
                WHERE $where
            ");
            $stmtCount->execute($params);
            $totalRegistros = (int)$stmtCount->fetchColumn();

            // Datos
            $stmt = $this->db->prepare("
                SELECT p.*,
                       r.res_fecha_reserva, r.res_hora_inicio, r.res_hora_fin,
                       r.res_precio_total, r.res_estado_pago AS reserva_estado_pago,
                       i.ins_nombre AS instalacion_nombre,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS cliente_nombre
                FROM instalaciones_reserva_pagos p
                INNER JOIN instalaciones_reservas r ON p.pag_reserva_id = r.res_reserva_id
                INNER JOIN instalaciones i ON r.res_instalacion_id = i.ins_instalacion_id
                INNER JOIN clientes c ON r.res_cliente_id = c.cli_cliente_id
                WHERE $where
                ORDER BY p.pag_fecha_pago DESC
                LIMIT " . (int)$perPage . " OFFSET " . (int)$offset
            );
            $stmt->execute($params);
            $pagos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Resumen
            $stmtResumen = $this->db->prepare("
                SELECT 
                    COUNT(*) AS total_pagos,
                    COALESCE(SUM(p.pag_monto), 0) AS total_monto,
                    COALESCE(SUM(CASE WHEN p.pag_tipo_pago = 'MONEDERO' THEN p.pag_monto 
                                      WHEN p.pag_tipo_pago = 'MIXTO' THEN p.pag_monto * 0.5 
                                      ELSE 0 END), 0) AS total_monedero,
                    COALESCE(SUM(CASE WHEN p.pag_forma_pago = 'EFECTIVO' THEN p.pag_monto ELSE 0 END), 0) AS total_efectivo,
                    COALESCE(SUM(CASE WHEN p.pag_forma_pago = 'TARJETA' THEN p.pag_monto ELSE 0 END), 0) AS total_tarjeta,
                    COALESCE(SUM(CASE WHEN p.pag_forma_pago = 'TRANSFERENCIA' THEN p.pag_monto ELSE 0 END), 0) AS total_transferencia
                FROM instalaciones_reserva_pagos p
                WHERE p.pag_tenant_id = ? AND p.pag_estado = 'COMPLETADO'
                  AND DATE(p.pag_fecha_pago) >= ? AND DATE(p.pag_fecha_pago) <= ?
            ");
            $stmtResumen->execute([$this->tenantId, $desde, $hasta]);
            $resumen = $stmtResumen->fetch(\PDO::FETCH_ASSOC);

            $this->viewData['pagos']          = $pagos;
            $this->viewData['resumen']        = $resumen;
            $this->viewData['totalRegistros'] = $totalRegistros;
            $this->viewData['pagina']         = $pagina;
            $this->viewData['totalPaginas']   = ceil($totalRegistros / $perPage);
            $this->viewData['buscar']         = $buscar;
            $this->viewData['desde']          = $desde;
            $this->viewData['hasta']          = $hasta;
            $this->viewData['csrf_token']     = \Security::generateCsrfToken();
            $this->viewData['title']          = 'Historial de Pagos';

            $this->renderModule('reservas/pagos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error historial pagos: " . $e->getMessage());
            $this->error('Error al cargar el historial de pagos');
        }
    }

    /* ═══════════════════════════════════════════
     * ANULAR PAGO — Devuelve al monedero si aplica
     * ═══════════════════════════════════════════ */
    public function anular() {
        try {
            $pagoId = (int)($this->post('pago_id') ?? $this->get('id') ?? 0);
            $motivo = trim($this->post('motivo') ?? '');

            if ($pagoId < 1) {
                $this->error('Pago no válido');
                return;
            }

            $stmt = $this->db->prepare("
                SELECT p.*, r.res_cliente_id, r.res_reserva_id, r.res_abono_utilizado,
                       r.res_monto_pagado, r.res_precio_total
                FROM instalaciones_reserva_pagos p
                INNER JOIN instalaciones_reservas r ON p.pag_reserva_id = r.res_reserva_id
                WHERE p.pag_pago_id = ? AND p.pag_tenant_id = ?
            ");
            $stmt->execute([$pagoId, $this->tenantId]);
            $pago = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$pago) {
                $this->error('Pago no encontrado');
                return;
            }

            if ($pago['pag_estado'] !== 'COMPLETADO') {
                $this->error('Solo se pueden anular pagos completados');
                return;
            }

            $this->db->beginTransaction();

            // Marcar pago como anulado
            $stmt = $this->db->prepare("
                UPDATE instalaciones_reserva_pagos
                SET pag_estado = 'ANULADO'
                WHERE pag_pago_id = ?
            ");
            $stmt->execute([$pagoId]);

            // Si incluía monedero, devolver saldo
            if ($pago['pag_tipo_pago'] === 'MONEDERO' || $pago['pag_tipo_pago'] === 'MIXTO') {
                // Buscar movimiento de consumo asociado
                $stmtMov = $this->db->prepare("
                    SELECT mov_monto FROM instalaciones_abono_movimientos
                    WHERE mov_referencia_tipo = 'PAGO_RESERVA' AND mov_referencia_id = ?
                    AND mov_tipo = 'CONSUMO' AND mov_tenant_id = ?
                ");
                $stmtMov->execute([$pagoId, $this->tenantId]);
                $montoDevolver = (float)$stmtMov->fetchColumn();

                if ($montoDevolver > 0) {
                    $this->devolverMonedero($pago['res_cliente_id'], $montoDevolver, $pago['res_reserva_id'], $pagoId);
                }
            }

            // Recalcular totales en la reserva
            $stmtPagado = $this->db->prepare("
                SELECT COALESCE(SUM(pag_monto), 0) FROM instalaciones_reserva_pagos
                WHERE pag_reserva_id = ? AND pag_estado = 'COMPLETADO'
            ");
            $stmtPagado->execute([$pago['res_reserva_id']]);
            $nuevoPagado = (float)$stmtPagado->fetchColumn();

            // Recalcular abono utilizado
            $stmtAbono = $this->db->prepare("
                SELECT COALESCE(SUM(mov_monto), 0) FROM instalaciones_abono_movimientos
                WHERE mov_referencia_tipo = 'PAGO_RESERVA' AND mov_tipo = 'CONSUMO'
                AND mov_tenant_id = ? 
                AND mov_referencia_id IN (
                    SELECT pag_pago_id FROM instalaciones_reserva_pagos 
                    WHERE pag_reserva_id = ? AND pag_estado = 'COMPLETADO'
                )
            ");
            $stmtAbono->execute([$this->tenantId, $pago['res_reserva_id']]);
            $nuevoAbono = (float)$stmtAbono->fetchColumn();

            $nuevoSaldo = max(0, (float)$pago['res_precio_total'] - $nuevoPagado - $nuevoAbono);
            $estadoPago = ($nuevoSaldo <= 0.01) ? 'PAGADO' : (($nuevoPagado + $nuevoAbono) > 0 ? 'PARCIAL' : 'PENDIENTE');

            $stmt = $this->db->prepare("
                UPDATE instalaciones_reservas
                SET res_monto_pagado = ?, res_abono_utilizado = ?, res_saldo_pendiente = ?, res_estado_pago = ?
                WHERE res_reserva_id = ?
            ");
            $stmt->execute([$nuevoPagado, $nuevoAbono, $nuevoSaldo, $estadoPago, $pago['res_reserva_id']]);

            $this->db->commit();

            $this->audit('reserva_pagos', $pagoId, 'PAGO_ANULADO', null, [
                'monto' => $pago['pag_monto'],
                'motivo' => $motivo
            ]);

            $this->success([
                'redirect' => url('reservas', 'pago', 'checkout') . '&id=' . $pago['res_reserva_id']
            ], 'Pago anulado correctamente');

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->logError("Error anular pago: " . $e->getMessage());
            $this->error('Error al anular el pago');
        }
    }

    /* ═══════════════════════════════════════════
     * CONSULTAR SALDO — API JSON para el cliente
     * ═══════════════════════════════════════════ */
    public function saldoCliente() {
        $clienteId = (int)($this->get('cliente_id') ?? 0);
        if ($clienteId < 1) {
            $this->error('Cliente no válido');
            return;
        }
        $saldo = $this->getSaldoCliente($clienteId);
        $this->success(['saldo' => $saldo, 'saldo_fmt' => '$' . number_format($saldo, 2)]);
    }

    /* ═════════════════════════════════
     * MÉTODOS PRIVADOS
     * ═════════════════════════════════ */

    private function getSaldoCliente($clienteId) {
        $stmt = $this->db->prepare("
            SELECT COALESCE(abo_saldo_disponible, 0)
            FROM instalaciones_abonos
            WHERE abo_cliente_id = ? AND abo_tenant_id = ? AND abo_estado = 'ACTIVO'
            ORDER BY abo_abono_id DESC LIMIT 1
        ");
        $stmt->execute([$clienteId, $this->tenantId]);
        return (float)$stmt->fetchColumn();
    }

    private function getPagosReserva($reservaId) {
        $stmt = $this->db->prepare("
            SELECT * FROM instalaciones_reserva_pagos
            WHERE pag_reserva_id = ? AND pag_tenant_id = ?
            ORDER BY pag_fecha_pago DESC
        ");
        $stmt->execute([$reservaId, $this->tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function descontarMonedero($clienteId, $monto, $reservaId, $pagoId) {
        // Buscar abono activo
        $stmt = $this->db->prepare("
            SELECT abo_abono_id, abo_saldo_disponible, abo_monto_utilizado
            FROM instalaciones_abonos
            WHERE abo_cliente_id = ? AND abo_tenant_id = ? AND abo_estado = 'ACTIVO'
            ORDER BY abo_abono_id DESC LIMIT 1
        ");
        $stmt->execute([$clienteId, $this->tenantId]);
        $abono = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$abono || (float)$abono['abo_saldo_disponible'] < $monto) {
            throw new \Exception('Saldo de monedero insuficiente');
        }

        $saldoAnterior = (float)$abono['abo_saldo_disponible'];
        $saldoPosterior = $saldoAnterior - $monto;

        // Actualizar abono
        $stmt = $this->db->prepare("
            UPDATE instalaciones_abonos
            SET abo_saldo_disponible = ?,
                abo_monto_utilizado = abo_monto_utilizado + ?
            WHERE abo_abono_id = ?
        ");
        $stmt->execute([$saldoPosterior, $monto, $abono['abo_abono_id']]);

        // Actualizar saldo en clientes
        $stmt = $this->db->prepare("UPDATE clientes SET cli_saldo_abono = ? WHERE cli_cliente_id = ?");
        $stmt->execute([$saldoPosterior, $clienteId]);

        // Registrar movimiento
        $stmt = $this->db->prepare("
            INSERT INTO instalaciones_abono_movimientos
            (mov_tenant_id, mov_abono_id, mov_cliente_id, mov_tipo, mov_monto,
             mov_saldo_anterior, mov_saldo_posterior, mov_descripcion,
             mov_referencia_tipo, mov_referencia_id, mov_forma_pago, mov_usuario_registro)
            VALUES (?, ?, ?, 'CONSUMO', ?, ?, ?, ?, 'PAGO_RESERVA', ?, 'MONEDERO', ?)
        ");
        $stmt->execute([
            $this->tenantId,
            $abono['abo_abono_id'],
            $clienteId,
            $monto,
            $saldoAnterior,
            $saldoPosterior,
            "Pago reserva #$reservaId",
            $pagoId,
            $this->userId
        ]);
    }

    private function devolverMonedero($clienteId, $monto, $reservaId, $pagoId) {
        $stmt = $this->db->prepare("
            SELECT abo_abono_id, abo_saldo_disponible, abo_monto_utilizado
            FROM instalaciones_abonos
            WHERE abo_cliente_id = ? AND abo_tenant_id = ? AND abo_estado = 'ACTIVO'
            ORDER BY abo_abono_id DESC LIMIT 1
        ");
        $stmt->execute([$clienteId, $this->tenantId]);
        $abono = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$abono) return;

        $saldoAnterior = (float)$abono['abo_saldo_disponible'];
        $saldoPosterior = $saldoAnterior + $monto;

        $stmt = $this->db->prepare("
            UPDATE instalaciones_abonos
            SET abo_saldo_disponible = ?,
                abo_monto_utilizado = GREATEST(0, abo_monto_utilizado - ?)
            WHERE abo_abono_id = ?
        ");
        $stmt->execute([$saldoPosterior, $monto, $abono['abo_abono_id']]);

        $stmt = $this->db->prepare("UPDATE clientes SET cli_saldo_abono = ? WHERE cli_cliente_id = ?");
        $stmt->execute([$saldoPosterior, $clienteId]);

        $stmt = $this->db->prepare("
            INSERT INTO instalaciones_abono_movimientos
            (mov_tenant_id, mov_abono_id, mov_cliente_id, mov_tipo, mov_monto,
             mov_saldo_anterior, mov_saldo_posterior, mov_descripcion,
             mov_referencia_tipo, mov_referencia_id, mov_forma_pago, mov_usuario_registro)
            VALUES (?, ?, ?, 'DEVOLUCION', ?, ?, ?, ?, 'ANULACION_PAGO', ?, 'MONEDERO', ?)
        ");
        $stmt->execute([
            $this->tenantId,
            $abono['abo_abono_id'],
            $clienteId,
            $monto,
            $saldoAnterior,
            $saldoPosterior,
            "Devolución pago anulado reserva #$reservaId",
            $pagoId,
            $this->userId
        ]);
    }
}
