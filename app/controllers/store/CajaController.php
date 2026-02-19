<?php
/**
 * DigiSports Store — Controlador de Caja
 * Apertura/cierre de turnos, movimientos y arqueo
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class CajaController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono  = 'fas fa-store';
    protected $moduloColor  = '#F59E0B';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }

    /* ═══════════════════════════════════════
     * PANEL PRINCIPAL DE CAJA
     * ═══════════════════════════════════════ */
    public function index() {
        try {
            // Obtener cajas disponibles
            $stmt = $this->db->prepare("SELECT * FROM store_cajas WHERE caj_tenant_id = ? AND caj_activa = 1 ORDER BY caj_nombre");
            $stmt->execute([$this->tenantId]);
            $cajas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Turno abierto del usuario actual
            $turnoAbierto = $this->getTurnoAbierto();

            // Últimos turnos
            $stmt = $this->db->prepare("
                SELECT t.*, c.caj_nombre
                FROM store_caja_turnos t
                JOIN store_cajas c ON c.caj_caja_id = t.tur_caja_id
                WHERE t.tur_tenant_id = ?
                ORDER BY t.tur_fecha_apertura DESC LIMIT 20
            ");
            $stmt->execute([$this->tenantId]);
            $turnos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['cajas']        = $cajas;
            $this->viewData['turnoAbierto'] = $turnoAbierto;
            $this->viewData['turnos']       = $turnos;
            $this->viewData['csrf_token']   = \Security::generateCsrfToken();
            $this->viewData['title']        = 'Gestión de Caja';

            $this->renderModule('store/caja/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error en caja index: " . $e->getMessage());
            $this->error('Error al cargar caja');
        }
    }

    /* ═══════════════════════════════════════
     * ABRIR TURNO DE CAJA
     * ═══════════════════════════════════════ */
    public function abrirTurno() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            // Verificar que no tenga un turno abierto
            $turnoAbierto = $this->getTurnoAbierto();
            if ($turnoAbierto) {
                return $this->jsonResponse(['success' => false, 'message' => 'Ya tiene un turno abierto en ' . $turnoAbierto['caj_nombre'] . '. Ciérrelo primero.']);
            }

            $cajaId       = (int)($this->post('caja_id') ?? 0);
            $montoApertura = (float)($this->post('monto_apertura') ?? 0);
            $notas         = trim($this->post('notas') ?? '');

            if (!$cajaId) {
                return $this->jsonResponse(['success' => false, 'message' => 'Seleccione una caja']);
            }

            // Verificar que la caja no esté ocupada
            $stmt = $this->db->prepare("SELECT t.tur_turno_id, t.tur_usuario_id FROM store_caja_turnos t WHERE t.tur_caja_id = ? AND t.tur_tenant_id = ? AND t.tur_estado = 'ABIERTO'");
            $stmt->execute([$cajaId, $this->tenantId]);
            $cajaOcupada = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($cajaOcupada) {
                return $this->jsonResponse(['success' => false, 'message' => 'Esta caja ya está abierta por otro cajero']);
            }

            $stmt = $this->db->prepare("INSERT INTO store_caja_turnos (tur_tenant_id, tur_caja_id, tur_usuario_id, tur_monto_apertura, tur_fecha_apertura, tur_notas_apertura, tur_estado) VALUES (?, ?, ?, ?, NOW(), ?, 'ABIERTO')");
            $stmt->execute([$this->tenantId, $cajaId, $this->userId, $montoApertura, $notas ?: null]);

            $turnoId = (int)$this->db->lastInsertId();

            return $this->jsonResponse([
                'success' => true, 
                'message' => 'Turno abierto exitosamente',
                'turno_id' => $turnoId
            ]);

        } catch (\Exception $e) {
            $this->logError("Error abriendo turno: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al abrir turno']);
        }
    }

    /* ═══════════════════════════════════════
     * CERRAR TURNO DE CAJA
     * ═══════════════════════════════════════ */
    public function cerrarTurno() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $turnoId = (int)($this->post('turno_id') ?? 0);
            $montoCierreReal = (float)($this->post('monto_cierre_real') ?? 0);
            $notasCierre = trim($this->post('notas_cierre') ?? '');

            // Obtener turno
            $stmt = $this->db->prepare("SELECT * FROM store_caja_turnos WHERE tur_turno_id = ? AND tur_tenant_id = ? AND tur_estado = 'ABIERTO'");
            $stmt->execute([$turnoId, $this->tenantId]);
            $turno = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$turno) {
                return $this->jsonResponse(['success' => false, 'message' => 'Turno no encontrado o ya cerrado']);
            }

            $this->db->beginTransaction();

            // Calcular totales de ventas del turno
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) AS num_ventas,
                    COALESCE(SUM(ven_total), 0) AS total_ventas
                FROM store_ventas 
                WHERE ven_turno_id = ? AND ven_tenant_id = ? AND ven_estado = 'COMPLETADA'
            ");
            $stmt->execute([$turnoId, $this->tenantId]);
            $ventaStats = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Totales por forma de pago
            $stmt = $this->db->prepare("
                SELECT vpg_forma_pago, COALESCE(SUM(vpg_monto), 0) AS total_forma
                FROM store_venta_pagos vpg
                JOIN store_ventas v ON v.ven_venta_id = vpg.vpg_venta_id
                WHERE v.ven_turno_id = ? AND vpg.vpg_tenant_id = ? AND v.ven_estado = 'COMPLETADA'
                GROUP BY vpg_forma_pago
            ");
            $stmt->execute([$turnoId, $this->tenantId]);
            $pagos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $totalEfectivo = 0;
            $totalTarjeta = 0;
            $totalTransferencia = 0;
            $totalOtros = 0;

            foreach ($pagos as $pago) {
                switch ($pago['vpg_forma_pago']) {
                    case 'EFECTIVO': $totalEfectivo = (float)$pago['total_forma']; break;
                    case 'TARJETA_DEBITO':
                    case 'TARJETA_CREDITO': $totalTarjeta += (float)$pago['total_forma']; break;
                    case 'TRANSFERENCIA': $totalTransferencia = (float)$pago['total_forma']; break;
                    default: $totalOtros += (float)$pago['total_forma']; break;
                }
            }

            // Movimientos manuales de caja (entradas/salidas)
            $stmt = $this->db->prepare("
                SELECT cmv_tipo, COALESCE(SUM(cmv_monto), 0) AS total_mov
                FROM store_caja_movimientos
                WHERE cmv_turno_id = ? AND cmv_tenant_id = ?
                GROUP BY cmv_tipo
            ");
            $stmt->execute([$turnoId, $this->tenantId]);
            $movs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $entradasManuales = 0;
            $salidasManuales = 0;
            foreach ($movs as $m) {
                if ($m['cmv_tipo'] === 'ENTRADA') $entradasManuales = (float)$m['total_mov'];
                else $salidasManuales = (float)$m['total_mov'];
            }

            // Devoluciones
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS num_dev, COALESCE(SUM(dev_total), 0) AS total_dev
                FROM store_devoluciones 
                WHERE dev_turno_id = ? AND dev_tenant_id = ? AND dev_estado = 'COMPLETADA'
            ");
            $stmt->execute([$turnoId, $this->tenantId]);
            $devStats = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Calcular monto esperado
            $montoApertura = (float)$turno['tur_monto_apertura'];
            $montoCierreEsperado = $montoApertura + $totalEfectivo + $entradasManuales - $salidasManuales - (float)$devStats['total_dev'];
            $diferencia = $montoCierreReal - $montoCierreEsperado;

            // Actualizar turno
            $stmt = $this->db->prepare("
                UPDATE store_caja_turnos SET
                    tur_fecha_cierre = NOW(),
                    tur_monto_cierre_esperado = ?,
                    tur_monto_cierre_real = ?,
                    tur_diferencia = ?,
                    tur_total_ventas = ?,
                    tur_total_efectivo = ?,
                    tur_total_tarjeta = ?,
                    tur_total_transferencia = ?,
                    tur_total_otros = ?,
                    tur_num_ventas = ?,
                    tur_num_devoluciones = ?,
                    tur_total_devoluciones = ?,
                    tur_notas_cierre = ?,
                    tur_usuario_cierre = ?,
                    tur_estado = 'CERRADO'
                WHERE tur_turno_id = ? AND tur_tenant_id = ?
            ");
            $stmt->execute([
                $montoCierreEsperado, $montoCierreReal, $diferencia,
                (float)$ventaStats['total_ventas'], $totalEfectivo, $totalTarjeta,
                $totalTransferencia, $totalOtros,
                (int)$ventaStats['num_ventas'],
                (int)$devStats['num_dev'], (float)$devStats['total_dev'],
                $notasCierre ?: null, $this->userId,
                $turnoId, $this->tenantId
            ]);

            $this->db->commit();

            $msg = 'Turno cerrado exitosamente.';
            if (abs($diferencia) > 0.01) {
                $signo = $diferencia > 0 ? 'sobrante' : 'faltante';
                $msg .= ' Diferencia: $' . number_format(abs($diferencia), 2) . " ({$signo})";
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => $msg,
                'resumen' => [
                    'total_ventas'    => (float)$ventaStats['total_ventas'],
                    'num_ventas'      => (int)$ventaStats['num_ventas'],
                    'efectivo'        => $totalEfectivo,
                    'tarjeta'         => $totalTarjeta,
                    'transferencia'   => $totalTransferencia,
                    'esperado'        => $montoCierreEsperado,
                    'real'            => $montoCierreReal,
                    'diferencia'      => $diferencia,
                    'devoluciones'    => (float)$devStats['total_dev'],
                ]
            ]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error cerrando turno: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al cerrar turno']);
        }
    }

    /* ═══════════════════════════════════════
     * MOVIMIENTO MANUAL DE CAJA
     * ═══════════════════════════════════════ */
    public function movimiento() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $turnoAbierto = $this->getTurnoAbierto();
            if (!$turnoAbierto) {
                return $this->jsonResponse(['success' => false, 'message' => 'No tiene un turno de caja abierto']);
            }

            $tipo   = $this->post('tipo') ?? '';
            $monto  = (float)($this->post('monto') ?? 0);
            $motivo = trim($this->post('motivo') ?? '');

            if (!in_array($tipo, ['ENTRADA', 'SALIDA'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Tipo de movimiento inválido']);
            }
            if ($monto <= 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'El monto debe ser mayor a 0']);
            }
            if (empty($motivo)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Debe indicar el motivo']);
            }

            $stmt = $this->db->prepare("INSERT INTO store_caja_movimientos (cmv_tenant_id, cmv_turno_id, cmv_tipo, cmv_monto, cmv_motivo, cmv_usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$this->tenantId, $turnoAbierto['tur_turno_id'], $tipo, $monto, $motivo, $this->userId]);

            $label = $tipo === 'ENTRADA' ? 'Entrada' : 'Salida';
            return $this->jsonResponse(['success' => true, 'message' => "{$label} de \${$monto} registrada"]);

        } catch (\Exception $e) {
            $this->logError("Error movimiento caja: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar movimiento']);
        }
    }

    /* ═══════════════════════════════════════
     * ARQUEO DE CAJA (Conteo de billetes/monedas)
     * ═══════════════════════════════════════ */
    public function arqueo() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $turnoId = (int)($this->get('turno_id') ?? 0);
                $turnoAbierto = $this->getTurnoAbierto();
                if (!$turnoId && $turnoAbierto) {
                    $turnoId = $turnoAbierto['tur_turno_id'];
                }

                // Denominaciones Ecuador USD
                $denominaciones = ['$100', '$50', '$20', '$10', '$5', '$1', '$0.50', '$0.25', '$0.10', '$0.05', '$0.01'];

                // Obtener arqueo existente si hay
                $arqueoExistente = [];
                if ($turnoId) {
                    $stmt = $this->db->prepare("SELECT * FROM store_caja_arqueo WHERE arq_turno_id = ? AND arq_tenant_id = ? ORDER BY CAST(REPLACE(arq_denominacion, '$', '') AS DECIMAL) DESC");
                    $stmt->execute([$turnoId, $this->tenantId]);
                    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    foreach ($rows as $r) {
                        $arqueoExistente[$r['arq_denominacion']] = $r;
                    }
                }

                $this->viewData['turnoId']       = $turnoId;
                $this->viewData['turnoAbierto']  = $turnoAbierto;
                $this->viewData['denominaciones'] = $denominaciones;
                $this->viewData['arqueoExistente'] = $arqueoExistente;
                $this->viewData['csrf_token']    = \Security::generateCsrfToken();
                $this->viewData['title']         = 'Arqueo de Caja';

                return $this->renderModule('store/caja/arqueo', $this->viewData);
            }

            // POST — guardar arqueo
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $turnoId = (int)($this->post('turno_id') ?? 0);
            if (!$turnoId) {
                return $this->jsonResponse(['success' => false, 'message' => 'No se especificó el turno']);
            }

            $this->db->beginTransaction();

            // Limpiar arqueo anterior del turno
            $this->db->prepare("DELETE FROM store_caja_arqueo WHERE arq_turno_id = ? AND arq_tenant_id = ?")->execute([$turnoId, $this->tenantId]);

            $denominaciones = $this->post('denominacion') ?? [];
            $cantidades     = $this->post('cantidad') ?? [];
            $totalArqueo    = 0;

            for ($i = 0; $i < count($denominaciones); $i++) {
                $denom = $denominaciones[$i] ?? '';
                $cant  = (int)($cantidades[$i] ?? 0);
                if ($cant > 0 && !empty($denom)) {
                    $stmt = $this->db->prepare("INSERT INTO store_caja_arqueo (arq_tenant_id, arq_turno_id, arq_denominacion, arq_cantidad) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$this->tenantId, $turnoId, $denom, $cant]);
                    $valor = (float)str_replace('$', '', $denom);
                    $totalArqueo += ($cant * $valor);
                }
            }

            $this->db->commit();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Arqueo guardado. Total contado: $' . number_format($totalArqueo, 2),
                'total_arqueo' => $totalArqueo
            ]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error en arqueo: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al guardar arqueo']);
        }
    }

    /* ═══════════════════════════════════════
     * VER DETALLE DE UN TURNO
     * ═══════════════════════════════════════ */
    public function verTurno() {
        try {
            $turnoId = (int)($this->get('id') ?? 0);
            if (!$turnoId) return $this->error('Turno no encontrado');

            $stmt = $this->db->prepare("
                SELECT t.*, c.caj_nombre
                FROM store_caja_turnos t
                JOIN store_cajas c ON c.caj_caja_id = t.tur_caja_id
                WHERE t.tur_turno_id = ? AND t.tur_tenant_id = ?
            ");
            $stmt->execute([$turnoId, $this->tenantId]);
            $turno = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$turno) return $this->error('Turno no encontrado');

            // Ventas del turno
            $stmt = $this->db->prepare("SELECT * FROM store_ventas WHERE ven_turno_id = ? AND ven_tenant_id = ? ORDER BY ven_fecha DESC");
            $stmt->execute([$turnoId, $this->tenantId]);
            $ventas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Movimientos manuales
            $stmt = $this->db->prepare("SELECT * FROM store_caja_movimientos WHERE cmv_turno_id = ? AND cmv_tenant_id = ? ORDER BY cmv_fecha_registro");
            $stmt->execute([$turnoId, $this->tenantId]);
            $movimientos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Arqueo
            $stmt = $this->db->prepare("SELECT * FROM store_caja_arqueo WHERE arq_turno_id = ? AND arq_tenant_id = ? ORDER BY CAST(REPLACE(arq_denominacion, '$', '') AS DECIMAL) DESC");
            $stmt->execute([$turnoId, $this->tenantId]);
            $arqueo = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['turno']       = $turno;
            $this->viewData['ventas']      = $ventas;
            $this->viewData['movimientos'] = $movimientos;
            $this->viewData['arqueo']      = $arqueo;
            $this->viewData['title']       = 'Turno #' . $turnoId;

            $this->renderModule('store/caja/ver_turno', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error ver turno: " . $e->getMessage());
            $this->error('Error al cargar turno');
        }
    }

    /* ═══════════════════════════════════════
     * HISTORIAL DE TURNOS
     * ═══════════════════════════════════════ */
    public function historial() {
        try {
            $fechaDesde = $this->get('fecha_desde') ?? date('Y-m-01');
            $fechaHasta = $this->get('fecha_hasta') ?? date('Y-m-d');
            $cajaId     = (int)($this->get('caja_id') ?? 0);

            $sql = "SELECT t.*, c.caj_nombre
                    FROM store_caja_turnos t
                    JOIN store_cajas c ON c.caj_caja_id = t.tur_caja_id
                    WHERE t.tur_tenant_id = ? AND DATE(t.tur_fecha_apertura) BETWEEN ? AND ?";
            $params = [$this->tenantId, $fechaDesde, $fechaHasta];

            if ($cajaId > 0) {
                $sql .= " AND t.tur_caja_id = ?";
                $params[] = $cajaId;
            }
            $sql .= " ORDER BY t.tur_fecha_apertura DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $turnos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Cajas para filtro
            $stmt = $this->db->prepare("SELECT * FROM store_cajas WHERE caj_tenant_id = ? ORDER BY caj_nombre");
            $stmt->execute([$this->tenantId]);
            $cajas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['turnos']      = $turnos;
            $this->viewData['cajas']       = $cajas;
            $this->viewData['fechaDesde']  = $fechaDesde;
            $this->viewData['fechaHasta']  = $fechaHasta;
            $this->viewData['cajaFiltro']  = $cajaId;
            $this->viewData['title']       = 'Historial de Turnos';

            $this->renderModule('store/caja/historial', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error historial turnos: " . $e->getMessage());
            $this->error('Error al cargar historial');
        }
    }

    /* ═══════════════════════════════════════
     * ESTADO ACTUAL DE CAJA (API para POS)
     * ═══════════════════════════════════════ */
    public function estado() {
        try {
            $turno = $this->getTurnoAbierto();
            if (!$turno) {
                return $this->jsonResponse([
                    'success' => true,
                    'turno_abierto' => false,
                    'message' => 'No hay turno de caja abierto'
                ]);
            }

            // Ventas del turno actual
            $stmt = $this->db->prepare("SELECT COUNT(*) AS num_ventas, COALESCE(SUM(ven_total), 0) AS total_ventas FROM store_ventas WHERE ven_turno_id = ? AND ven_tenant_id = ? AND ven_estado = 'COMPLETADA'");
            $stmt->execute([$turno['tur_turno_id'], $this->tenantId]);
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'success' => true,
                'turno_abierto' => true,
                'turno' => [
                    'id'             => $turno['tur_turno_id'],
                    'caja'           => $turno['caj_nombre'],
                    'apertura'       => $turno['tur_fecha_apertura'],
                    'monto_apertura' => (float)$turno['tur_monto_apertura'],
                    'num_ventas'     => (int)$stats['num_ventas'],
                    'total_ventas'   => (float)$stats['total_ventas'],
                ]
            ]);

        } catch (\Exception $e) {
            $this->logError("Error estado caja: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error']);
        }
    }

    /* ═══════════════════════════════════════
     * HELPERS
     * ═══════════════════════════════════════ */
    private function getTurnoAbierto() {
        $stmt = $this->db->prepare("
            SELECT t.*, c.caj_nombre
            FROM store_caja_turnos t
            JOIN store_cajas c ON c.caj_caja_id = t.tur_caja_id
            WHERE t.tur_usuario_id = ? AND t.tur_tenant_id = ? AND t.tur_estado = 'ABIERTO'
            LIMIT 1
        ");
        $stmt->execute([$this->userId, $this->tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
