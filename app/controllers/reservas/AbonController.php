<?php
/**
 * DigiSports Arena — Controlador de Abonos / Monedero
 * Gestión completa de abonos prepagados: recargas, consumos,
 * historial de movimientos y paquetes de horas.
 * 
 * @package DigiSports\Controllers\Reservas
 * @version 1.0.0
 */

namespace App\Controllers\Reservas;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class AbonController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Arena';
    protected $moduloIcono = 'fas fa-building';
    protected $moduloColor = '#3B82F6';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'ARENA';
    }

    /* ─────────────────────────────────────
     * LISTADO PRINCIPAL — Monederos activos
     * ───────────────────────────────────── */
    public function index() {
        try {
            $buscar  = $this->post('buscar') ?? $this->get('buscar') ?? '';
            $estado  = $this->post('estado') ?? $this->get('estado') ?? '';
            $pagina  = max(1, (int)($this->post('pagina') ?? $this->get('pagina') ?? 1));
            $perPage = 15;
            $offset  = ($pagina - 1) * $perPage;

            $query = "
                SELECT a.*,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS cliente_nombre,
                       c.cli_email AS cliente_email,
                       c.cli_telefono AS cliente_telefono,
                       c.cli_identificacion AS cliente_identificacion
                FROM abonos a
                INNER JOIN clientes c ON a.cliente_id = c.cli_cliente_id
                WHERE a.tenant_id = ?
            ";
            $params = [$this->tenantId];

            if (!empty($buscar)) {
                $query .= " AND (c.cli_nombres LIKE ? OR c.cli_apellidos LIKE ?)";
                $like = "%{$buscar}%";
                array_push($params, $like, $like);
            }

            if (!empty($estado)) {
                $query .= " AND a.estado = ?";
                $params[] = $estado;
            }

            // Total
            $countSql = str_replace(
                "SELECT a.*,\n                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS cliente_nombre,\n                       c.cli_email AS cliente_email,\n                       c.cli_telefono AS cliente_telefono,\n                       c.cli_identificacion AS cliente_identificacion",
                "SELECT COUNT(*) AS total",
                $query
            );
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $totalRegistros = (int)$stmt->fetchColumn();

            $query .= " ORDER BY a.fecha_registro DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $abonos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Resumen global
            $resumen = $this->getResumen();

            // Descifrar campos sensibles del cliente en cada abono
            foreach ($abonos as &$a) {
                if (!empty($a['cliente_email'])) {
                    $a['cliente_email'] = \DataProtection::decrypt($a['cliente_email']);
                }
                if (!empty($a['cliente_telefono'])) {
                    $a['cliente_telefono'] = \DataProtection::decrypt($a['cliente_telefono']);
                }
                if (!empty($a['cliente_identificacion'])) {
                    $a['cliente_identificacion'] = \DataProtection::decrypt($a['cliente_identificacion']);
                }
            }
            unset($a);

            $this->viewData['abonos']         = $abonos;
            $this->viewData['resumen']        = $resumen;
            $this->viewData['totalRegistros'] = $totalRegistros;
            $this->viewData['pagina']         = $pagina;
            $this->viewData['totalPaginas']   = ceil($totalRegistros / $perPage);
            $this->viewData['buscar']         = $buscar;
            $this->viewData['estado']         = $estado;
            $this->viewData['csrf_token']     = \Security::generateCsrfToken();
            $this->viewData['title']          = 'Monedero / Abonos';
            $this->viewData['layout']         = 'main';

            $this->renderModule('reservas/abonos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error al listar abonos: " . $e->getMessage());
            $this->error('Error al cargar los abonos');
        }
    }

    /* ─────────────────────────────────
     * VER DETALLE DE UN MONEDERO
     * ───────────────────────────────── */
    public function ver() {
        $id = (int)$this->get('id');
        if ($id < 1) $this->error('Abono no válido');

        try {
            $stmt = $this->db->prepare("
                SELECT a.*,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS cliente_nombre,
                       c.cli_email AS cliente_email,
                       c.cli_telefono AS cliente_telefono,
                       c.cli_identificacion AS cliente_identificacion,
                       c.cli_tipo_cliente AS cliente_tipo
                FROM abonos a
                INNER JOIN clientes c ON a.cliente_id = c.cli_cliente_id
                WHERE a.abono_id = ? AND a.tenant_id = ?
            ");
            $stmt->execute([$id, $this->tenantId]);
            $abono = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$abono) $this->error('Abono no encontrado');

            // Descifrar campos sensibles del cliente
            if (!empty($abono['cliente_email'])) {
                $abono['cliente_email'] = \DataProtection::decrypt($abono['cliente_email']);
            }
            if (!empty($abono['cliente_telefono'])) {
                $abono['cliente_telefono'] = \DataProtection::decrypt($abono['cliente_telefono']);
            }
            if (!empty($abono['cliente_identificacion'])) {
                $abono['cliente_identificacion'] = \DataProtection::decrypt($abono['cliente_identificacion']);
            }

            // Últimos movimientos
            $stmt = $this->db->prepare("
                SELECT * FROM abono_movimientos
                WHERE abono_id = ? AND tenant_id = ?
                ORDER BY fecha_registro DESC
                LIMIT 20
            ");
            $stmt->execute([$id, $this->tenantId]);
            $movimientos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['abono']       = $abono;
            $this->viewData['movimientos'] = $movimientos;
            $this->viewData['csrf_token']  = \Security::generateCsrfToken();
            $this->viewData['title']       = 'Detalle de Monedero #' . $id;
            $this->viewData['layout']      = 'main';

            $this->renderModule('reservas/abonos/ver', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error al ver abono: " . $e->getMessage());
            $this->error('Error al cargar el detalle del abono');
        }
    }

    /* ──────────────────────────────────────
     * FORMULARIO / CREAR MONEDERO + RECARGA
     * ────────────────────────────────────── */
    public function crear() {
        try {
            // Listar clientes sin monedero activo
            $stmt = $this->db->prepare("
                SELECT c.cli_cliente_id AS cliente_id,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS nombre_completo,
                       c.cli_email AS email,
                       c.cli_identificacion AS identificacion
                FROM clientes c
                LEFT JOIN instalaciones_abonos a 
                    ON c.cli_cliente_id = a.abo_cliente_id 
                    AND a.abo_tenant_id = ? 
                    AND a.abo_estado = 'ACTIVO'
                WHERE c.cli_tenant_id = ? AND c.cli_estado = 'A'
                  AND a.abo_abono_id IS NULL
                ORDER BY c.cli_nombres
            ");
            $stmt->execute([$this->tenantId, $this->tenantId]);
            $clientesSin = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Descifrar campos sensibles de la lista de clientes
            foreach ($clientesSin as &$cli) {
                if (!empty($cli['email'])) {
                    $cli['email'] = \DataProtection::decrypt($cli['email']);
                }
                if (!empty($cli['identificacion'])) {
                    $cli['identificacion'] = \DataProtection::decrypt($cli['identificacion']);
                }
            }
            unset($cli);

            // Paquetes disponibles
            $paquetes = $this->getPaquetesActivos();

            $this->viewData['clientes_sin_monedero'] = $clientesSin;
            $this->viewData['paquetes']    = $paquetes;
            $this->viewData['csrf_token']  = \Security::generateCsrfToken();
            $this->viewData['title']       = 'Nuevo Monedero';
            $this->viewData['layout']      = 'main';

            $this->renderModule('reservas/abonos/crear', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error formulario crear abono: " . $e->getMessage());
            $this->error('Error al cargar el formulario');
        }
    }

    /**
     * Guardar nuevo monedero con recarga inicial
     */
    public function guardar() {
        if (!$this->isPost()) $this->error('Solicitud inválida');
        if (!$this->validateCsrf()) $this->error('Token de seguridad inválido', 403);

        try {
            $clienteId  = (int)$this->post('cliente_id');
            $montoInicial = (float)$this->post('monto_inicial');
            $formaPago  = trim($this->post('forma_pago') ?? 'EFECTIVO');
            $diasVigencia = (int)($this->post('dias_vigencia') ?? 365);

            if ($clienteId < 1) $this->error('Selecciona un cliente');
            if ($montoInicial < 1) $this->error('El monto inicial debe ser mayor a $1');
            if (!in_array($formaPago, ['EFECTIVO','TARJETA','TRANSFERENCIA','PAQUETE'])) {
                $this->error('Forma de pago inválida');
            }

            // Verificar que no tiene monedero activo
            $stmt = $this->db->prepare("
                SELECT abo_abono_id FROM instalaciones_abonos 
                WHERE abo_cliente_id = ? AND abo_tenant_id = ? AND abo_estado = 'ACTIVO'
            ");
            $stmt->execute([$clienteId, $this->tenantId]);
            if ($stmt->fetch()) {
                $this->error('Este cliente ya tiene un monedero activo');
            }

            $this->db->beginTransaction();

            $fechaVence = date('Y-m-d', strtotime("+{$diasVigencia} days"));

            // Crear monedero
            $stmt = $this->db->prepare("
                INSERT INTO instalaciones_abonos 
                    (abo_tenant_id, abo_cliente_id, abo_monto_total, abo_monto_utilizado, 
                     abo_saldo_disponible, abo_fecha_compra, abo_fecha_vencimiento, 
                     abo_forma_pago, abo_estado)
                VALUES (?, ?, ?, 0, ?, CURDATE(), ?, ?, 'ACTIVO')
            ");
            $stmt->execute([
                $this->tenantId, $clienteId, $montoInicial,
                $montoInicial, $fechaVence, $formaPago
            ]);
            $abonoId = $this->db->lastInsertId();

            // Registrar movimiento de recarga inicial
            $this->registrarMovimiento(
                $abonoId, $clienteId, 'RECARGA', $montoInicial,
                0, $montoInicial,
                'Recarga inicial al crear monedero',
                'MANUAL', null, $formaPago
            );

            // Actualizar saldo_abono en cliente
            $stmt = $this->db->prepare("
                UPDATE clientes SET cli_saldo_abono = cli_saldo_abono + ? 
                WHERE cli_cliente_id = ? AND cli_tenant_id = ?
            ");
            $stmt->execute([$montoInicial, $clienteId, $this->tenantId]);

            $this->db->commit();

            $this->audit('abonos', $abonoId, 'INSERT', [], [
                'cliente_id' => $clienteId,
                'monto' => $montoInicial,
                'forma_pago' => $formaPago
            ]);

            \Security::logSecurityEvent('MONEDERO_CREATED', "Abono ID: {$abonoId}");

            $this->success([
                'redirect' => url('reservas', 'abon', 'ver', ['id' => $abonoId])
            ], '¡Monedero creado exitosamente!');

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error al crear monedero: " . $e->getMessage());
            $this->error('Error al crear el monedero');
        }
    }

    /* ──────────────────────────
     * RECARGAR MONEDERO
     * ────────────────────────── */
    public function recargar() {
        if (!$this->isPost()) $this->error('Solicitud inválida');
        if (!$this->validateCsrf()) $this->error('Token de seguridad inválido', 403);

        try {
            $abonoId   = (int)$this->post('abono_id');
            $monto     = (float)$this->post('monto');
            $formaPago = trim($this->post('forma_pago') ?? 'EFECTIVO');
            $nota      = trim($this->post('nota') ?? '');

            if ($abonoId < 1) $this->error('Monedero no válido');
            if ($monto < 1)   $this->error('El monto debe ser mayor a $1');

            // Obtener monedero
            $stmt = $this->db->prepare("
                SELECT * FROM instalaciones_abonos 
                WHERE abo_abono_id = ? AND abo_tenant_id = ? AND abo_estado = 'ACTIVO'
            ");
            $stmt->execute([$abonoId, $this->tenantId]);
            $abono = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$abono) $this->error('Monedero no encontrado o inactivo');

            $saldoAnterior = (float)$abono['abo_saldo_disponible'];
            $saldoNuevo = $saldoAnterior + $monto;

            $this->db->beginTransaction();

            // Actualizar monedero
            $stmt = $this->db->prepare("
                UPDATE instalaciones_abonos SET
                    abo_monto_total = abo_monto_total + ?,
                    abo_saldo_disponible = abo_saldo_disponible + ?
                WHERE abo_abono_id = ?
            ");
            $stmt->execute([$monto, $monto, $abonoId]);

            // Registrar movimiento
            $desc = 'Recarga de monedero' . (!empty($nota) ? ": {$nota}" : '');
            $this->registrarMovimiento(
                $abonoId, $abono['abo_cliente_id'], 'RECARGA', $monto,
                $saldoAnterior, $saldoNuevo,
                $desc, 'MANUAL', null, $formaPago
            );

            // Actualizar saldo en cliente
            $stmt = $this->db->prepare("
                UPDATE clientes SET cli_saldo_abono = cli_saldo_abono + ? 
                WHERE cli_cliente_id = ? AND cli_tenant_id = ?
            ");
            $stmt->execute([$monto, $abono['abo_cliente_id'], $this->tenantId]);

            $this->db->commit();

            $this->audit('abonos', $abonoId, 'RECARGA', 
                ['saldo' => $saldoAnterior], 
                ['saldo' => $saldoNuevo, 'monto_recarga' => $monto]
            );

            $this->success([
                'nuevo_saldo' => $saldoNuevo,
                'redirect' => url('reservas', 'abon', 'ver', ['id' => $abonoId])
            ], "¡Recarga de \${$monto} exitosa! Nuevo saldo: \${$saldoNuevo}");

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error al recargar monedero: " . $e->getMessage());
            $this->error('Error al recargar el monedero');
        }
    }

    /* ──────────────────────────────
     * CONSUMIR SALDO (desde reserva)
     * ────────────────────────────── */
    public function consumir() {
        if (!$this->isPost()) $this->error('Solicitud inválida');
        if (!$this->validateCsrf()) $this->error('Token de seguridad inválido', 403);

        try {
            $abonoId   = (int)$this->post('abono_id');
            $monto     = (float)$this->post('monto');
            $reservaId = (int)($this->post('reserva_id') ?? 0);
            $nota      = trim($this->post('nota') ?? '');

            if ($abonoId < 1) $this->error('Monedero no válido');
            if ($monto < 0.01) $this->error('El monto debe ser positivo');

            // Obtener monedero
            $stmt = $this->db->prepare("
                SELECT * FROM instalaciones_abonos 
                WHERE abo_abono_id = ? AND abo_tenant_id = ? AND abo_estado = 'ACTIVO'
            ");
            $stmt->execute([$abonoId, $this->tenantId]);
            $abono = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$abono) $this->error('Monedero no encontrado o inactivo');

            $saldoActual = (float)$abono['abo_saldo_disponible'];
            if ($monto > $saldoActual) {
                $this->error("Saldo insuficiente. Disponible: \${$saldoActual}");
            }

            $saldoNuevo = $saldoActual - $monto;

            $this->db->beginTransaction();

            // Actualizar monedero
            $stmt = $this->db->prepare("
                UPDATE instalaciones_abonos SET
                    abo_monto_utilizado = abo_monto_utilizado + ?,
                    abo_saldo_disponible = abo_saldo_disponible - ?
                WHERE abo_abono_id = ?
            ");
            $stmt->execute([$monto, $monto, $abonoId]);

            // Registrar movimiento
            $desc = 'Consumo en reserva' . ($reservaId > 0 ? " #{$reservaId}" : '') . (!empty($nota) ? ": {$nota}" : '');
            $refTipo = $reservaId > 0 ? 'RESERVA' : 'MANUAL';
            $this->registrarMovimiento(
                $abonoId, $abono['abo_cliente_id'], 'CONSUMO', $monto,
                $saldoActual, $saldoNuevo,
                $desc, $refTipo, $reservaId > 0 ? $reservaId : null, null
            );

            // Si se vincula a una reserva, registrar el abono utilizado
            if ($reservaId > 0) {
                $stmt = $this->db->prepare("
                    UPDATE instalaciones_reservas SET 
                        res_abono_utilizado = COALESCE(res_abono_utilizado, 0) + ?
                    WHERE res_reserva_id = ?
                ");
                $stmt->execute([$monto, $reservaId]);
            }

            // Actualizar saldo en cliente
            $stmt = $this->db->prepare("
                UPDATE clientes SET cli_saldo_abono = GREATEST(cli_saldo_abono - ?, 0) 
                WHERE cli_cliente_id = ? AND cli_tenant_id = ?
            ");
            $stmt->execute([$monto, $abono['abo_cliente_id'], $this->tenantId]);

            $this->db->commit();

            $this->audit('abonos', $abonoId, 'CONSUMO',
                ['saldo' => $saldoActual],
                ['saldo' => $saldoNuevo, 'consumo' => $monto, 'reserva' => $reservaId]
            );

            $this->success([
                'nuevo_saldo' => $saldoNuevo
            ], "Consumo de \${$monto} procesado. Saldo restante: \${$saldoNuevo}");

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error al consumir saldo: " . $e->getMessage());
            $this->error('Error al procesar el consumo');
        }
    }

    /* ──────────────────────────────────────
     * HISTORIAL DE MOVIMIENTOS (global)
     * ────────────────────────────────────── */
    public function historial() {
        try {
            $pagina  = max(1, (int)($this->get('pagina') ?? 1));
            $tipo    = $this->get('tipo') ?? '';
            $perPage = 25;
            $offset  = ($pagina - 1) * $perPage;

            $query = "
                SELECT m.*,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS cliente_nombre
                FROM abono_movimientos m
                INNER JOIN clientes c ON m.cliente_id = c.cli_cliente_id
                WHERE m.tenant_id = ?
            ";
            $params = [$this->tenantId];

            if (!empty($tipo)) {
                $query .= " AND m.tipo = ?";
                $params[] = $tipo;
            }

            $countSql = "SELECT COUNT(*) FROM abono_movimientos m WHERE m.tenant_id = ?";
            $countParams = [$this->tenantId];
            if (!empty($tipo)) {
                $countSql .= " AND m.tipo = ?";
                $countParams[] = $tipo;
            }
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($countParams);
            $total = (int)$stmt->fetchColumn();

            $query .= " ORDER BY m.fecha_registro DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $movimientos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['movimientos']    = $movimientos;
            $this->viewData['totalRegistros'] = $total;
            $this->viewData['pagina']         = $pagina;
            $this->viewData['totalPaginas']   = ceil($total / $perPage);
            $this->viewData['tipo']           = $tipo;
            $this->viewData['title']          = 'Historial de Movimientos';
            $this->viewData['layout']         = 'main';

            $this->renderModule('reservas/abonos/historial', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error historial movimientos: " . $e->getMessage());
            $this->error('Error al cargar el historial');
        }
    }

    /* ─────────────────────────────────────
     * PAQUETES DE HORAS
     * ───────────────────────────────────── */
    public function paquetes() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM instalaciones_paquetes 
                WHERE paq_tenant_id = ? 
                ORDER BY paq_estado DESC, paq_precio_paquete ASC
            ");
            $stmt->execute([$this->tenantId]);
            $paquetes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['paquetes']   = $paquetes;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title']      = 'Paquetes de Horas';
            $this->viewData['layout']     = 'main';

            $this->renderModule('reservas/abonos/paquetes', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error paquetes: " . $e->getMessage());
            $this->error('Error al cargar los paquetes');
        }
    }

    public function guardarPaquete() {
        if (!$this->isPost()) $this->error('Solicitud inválida');
        if (!$this->validateCsrf()) $this->error('Token de seguridad inválido', 403);

        try {
            $paqueteId     = (int)($this->post('paquete_id') ?? 0);
            $nombre        = trim($this->post('nombre'));
            $descripcion   = trim($this->post('descripcion') ?? '');
            $horasIncluidas = (int)$this->post('horas_incluidas');
            $precioNormal  = (float)$this->post('precio_normal');
            $precioPaquete = (float)$this->post('precio_paquete');
            $diasVigencia  = (int)($this->post('dias_vigencia') ?? 90);
            $estado        = $this->post('estado') ?? 'ACTIVO';

            if (empty($nombre)) $this->error('El nombre es requerido');
            if ($horasIncluidas < 1) $this->error('Las horas deben ser mayor a 0');
            if ($precioPaquete < 1) $this->error('El precio debe ser mayor a $1');

            $descuentoPct = $precioNormal > 0 
                ? round((1 - $precioPaquete / $precioNormal) * 100, 2) 
                : 0;

            if ($paqueteId > 0) {
                $stmt = $this->db->prepare("
                    UPDATE instalaciones_paquetes SET
                        paq_nombre = ?, paq_descripcion = ?,
                        paq_horas_incluidas = ?, paq_precio_normal = ?,
                        paq_precio_paquete = ?, paq_descuento_pct = ?,
                        paq_dias_vigencia = ?, paq_estado = ?
                    WHERE paq_paquete_id = ? AND paq_tenant_id = ?
                ");
                $stmt->execute([
                    $nombre, $descripcion, $horasIncluidas, $precioNormal,
                    $precioPaquete, $descuentoPct, $diasVigencia, $estado,
                    $paqueteId, $this->tenantId
                ]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO instalaciones_paquetes 
                        (paq_tenant_id, paq_nombre, paq_descripcion, paq_horas_incluidas,
                         paq_precio_normal, paq_precio_paquete, paq_descuento_pct,
                         paq_dias_vigencia, paq_estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $this->tenantId, $nombre, $descripcion, $horasIncluidas,
                    $precioNormal, $precioPaquete, $descuentoPct, $diasVigencia, $estado
                ]);
                $paqueteId = $this->db->lastInsertId();
            }

            $this->success([
                'redirect' => url('reservas', 'abon', 'paquetes')
            ], 'Paquete guardado exitosamente');

        } catch (\Exception $e) {
            $this->logError("Error guardar paquete: " . $e->getMessage());
            $this->error('Error al guardar el paquete');
        }
    }

    /* ─────────────────────────────────
     * API JSON — saldo del cliente
     * ───────────────────────────────── */
    public function saldo() {
        $clienteId = (int)$this->get('cliente_id');
        if ($clienteId < 1) {
            $this->success(['saldo' => 0, 'abono_id' => 0, 'tiene_monedero' => false]);
            return;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT abo_abono_id AS abono_id, 
                       abo_saldo_disponible AS saldo,
                       abo_fecha_vencimiento AS fecha_vencimiento
                FROM instalaciones_abonos 
                WHERE abo_cliente_id = ? AND abo_tenant_id = ? AND abo_estado = 'ACTIVO'
                LIMIT 1
            ");
            $stmt->execute([$clienteId, $this->tenantId]);
            $abono = $stmt->fetch(\PDO::FETCH_ASSOC);

            $this->success([
                'tiene_monedero' => (bool)$abono,
                'abono_id'       => $abono ? (int)$abono['abono_id'] : 0,
                'saldo'          => $abono ? (float)$abono['saldo'] : 0,
                'fecha_vencimiento' => $abono ? $abono['fecha_vencimiento'] : null
            ]);

        } catch (\Exception $e) {
            $this->error('Error al consultar saldo');
        }
    }

    /* ══════════════════════════════════
     * HELPERS PRIVADOS
     * ══════════════════════════════════ */

    private function registrarMovimiento(
        $abonoId, $clienteId, $tipo, $monto,
        $saldoAnterior, $saldoPosterior,
        $descripcion, $refTipo, $refId, $formaPago
    ) {
        $stmt = $this->db->prepare("
            INSERT INTO instalaciones_abono_movimientos 
                (mov_tenant_id, mov_abono_id, mov_cliente_id, mov_tipo, mov_monto,
                 mov_saldo_anterior, mov_saldo_posterior, mov_descripcion,
                 mov_referencia_tipo, mov_referencia_id, mov_forma_pago, mov_usuario_registro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->tenantId, $abonoId, $clienteId, $tipo, $monto,
            $saldoAnterior, $saldoPosterior, $descripcion,
            $refTipo, $refId, $formaPago, $this->userId
        ]);
    }

    private function getResumen() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) AS total_monederos,
                    SUM(CASE WHEN abo_estado = 'ACTIVO' THEN 1 ELSE 0 END) AS activos,
                    COALESCE(SUM(CASE WHEN abo_estado = 'ACTIVO' THEN abo_saldo_disponible ELSE 0 END), 0) AS saldo_total,
                    COALESCE(SUM(abo_monto_total), 0) AS recargas_total,
                    COALESCE(SUM(abo_monto_utilizado), 0) AS consumos_total
                FROM instalaciones_abonos 
                WHERE abo_tenant_id = ?
            ");
            $stmt->execute([$this->tenantId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [
                'total_monederos' => 0, 'activos' => 0,
                'saldo_total' => 0, 'recargas_total' => 0, 'consumos_total' => 0
            ];
        }
    }

    private function getPaquetesActivos() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM instalaciones_paquetes 
                WHERE paq_tenant_id = ? AND paq_estado = 'ACTIVO'
                ORDER BY paq_precio_paquete ASC
            ");
            $stmt->execute([$this->tenantId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
}
