<?php
/**
 * DigiSports Arena — Controlador de Entradas / Tickets
 * Venta de entradas, registro de ingreso, tarifas y control de acceso
 *
 * @package DigiSports\Controllers\Instalaciones
 * @version 1.0.0
 */

namespace App\Controllers\Instalaciones;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class EntradaController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Arena';
    protected $moduloIcono  = 'fas fa-building';
    protected $moduloColor  = '#3B82F6';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'ARENA';
    }

    /* ═══════════════════════════════════════════
     * INDEX — Listado de entradas vendidas
     * ═══════════════════════════════════════════ */
    public function index() {
        try {
            $buscar  = $this->post('buscar') ?? $this->get('buscar') ?? '';
            $estado  = $this->post('estado') ?? $this->get('estado') ?? '';
            $fecha   = $this->post('fecha') ?? $this->get('fecha') ?? date('Y-m-d');
            $pagina  = max(1, (int)($this->post('pagina') ?? $this->get('pagina') ?? 1));
            $perPage = 20;
            $offset  = ($pagina - 1) * $perPage;

            $where  = "e.ent_tenant_id = ?";
            $params = [$this->tenantId];

            if (!empty($fecha)) {
                $where .= " AND DATE(e.ent_fecha_entrada) = ?";
                $params[] = $fecha;
            }
            if (!empty($estado)) {
                $where .= " AND e.ent_estado = ?";
                $params[] = $estado;
            }
            if (!empty($buscar)) {
                $where .= " AND (CONCAT(c.cli_nombres,' ',c.cli_apellidos) LIKE ? OR e.ent_codigo LIKE ?)";
                $params[] = "%$buscar%";
                $params[] = "%$buscar%";
            }

            // Total
            $stmtCount = $this->db->prepare("
                SELECT COUNT(*) FROM instalaciones_entradas e
                LEFT JOIN clientes c ON e.ent_cliente_id = c.cli_cliente_id
                WHERE $where
            ");
            $stmtCount->execute($params);
            $totalRegistros = (int)$stmtCount->fetchColumn();

            // Datos
            $stmt = $this->db->prepare("
                SELECT e.*,
                       i.ins_nombre AS instalacion_nombre,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS cliente_nombre,
                       c.cli_email AS cliente_email
                FROM instalaciones_entradas e
                INNER JOIN instalaciones i ON e.ent_instalacion_id = i.ins_instalacion_id
                LEFT JOIN clientes c ON e.ent_cliente_id = c.cli_cliente_id
                WHERE $where
                ORDER BY e.ent_fecha_registro DESC
                LIMIT " . (int)$perPage . " OFFSET " . (int)$offset
            );
            $stmt->execute($params);
            $entradas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Descifrar campos sensibles del cliente en cada entrada
            foreach ($entradas as &$ent) {
                if (!empty($ent['cliente_email'])) {
                    $ent['cliente_email'] = \DataProtection::decrypt($ent['cliente_email']);
                }
            }
            unset($ent);

            // Resumen del día
            $stmtResumen = $this->db->prepare("
                SELECT
                    COUNT(*) AS total_entradas,
                    COALESCE(SUM(CASE WHEN ent_estado = 'VENDIDA' THEN 1 ELSE 0 END), 0) AS vendidas,
                    COALESCE(SUM(CASE WHEN ent_estado = 'USADA' THEN 1 ELSE 0 END), 0) AS usadas,
                    COALESCE(SUM(CASE WHEN ent_estado = 'ANULADA' THEN 1 ELSE 0 END), 0) AS anuladas,
                    COALESCE(SUM(CASE WHEN ent_estado IN ('VENDIDA','USADA') THEN ent_total ELSE 0 END), 0) AS total_recaudado,
                    COALESCE(SUM(CASE WHEN ent_estado IN ('VENDIDA','USADA') THEN ent_monto_monedero ELSE 0 END), 0) AS total_monedero,
                    COALESCE(SUM(CASE WHEN ent_estado IN ('VENDIDA','USADA') THEN ent_monto_efectivo ELSE 0 END), 0) AS total_efectivo
                FROM instalaciones_entradas
                WHERE ent_tenant_id = ? AND DATE(ent_fecha_entrada) = ?
            ");
            $stmtResumen->execute([$this->tenantId, $fecha]);
            $resumen = $stmtResumen->fetch(\PDO::FETCH_ASSOC);

            $this->viewData['entradas']       = $entradas;
            $this->viewData['resumen']        = $resumen;
            $this->viewData['totalRegistros'] = $totalRegistros;
            $this->viewData['pagina']         = $pagina;
            $this->viewData['totalPaginas']   = ceil($totalRegistros / $perPage);
            $this->viewData['buscar']         = $buscar;
            $this->viewData['estado']         = $estado;
            $this->viewData['fecha']          = $fecha;
            $this->viewData['title']          = 'Control de Entradas';

            $this->renderModule('instalaciones/entradas/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error entradas index: " . $e->getMessage());
            $this->error('Error al cargar las entradas');
        }
    }

    /* ═══════════════════════════════════════════
     * VENDER — Formulario de venta de entrada
     * ═══════════════════════════════════════════ */
    public function vender() {
        try {
            // Instalaciones activas
            $stmt = $this->db->prepare("
                SELECT ins_instalacion_id, ins_nombre, ins_tipo, ins_capacidad
                FROM instalaciones
                WHERE ins_tenant_id = ? AND ins_estado = 'ACTIVO'
                ORDER BY ins_nombre
            ");
            $stmt->execute([$this->tenantId]);
            $instalaciones = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Clientes
            $stmt = $this->db->prepare("
                SELECT cli_cliente_id, cli_nombres, cli_apellidos, cli_email, cli_identificacion
                FROM clientes
                WHERE cli_tenant_id = ?
                ORDER BY cli_nombres, cli_apellidos
            ");
            $stmt->execute([$this->tenantId]);
            $clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Descifrar campos sensibles de clientes
            foreach ($clientes as &$cli) {
                if (!empty($cli['cli_email'])) {
                    $cli['cli_email'] = \DataProtection::decrypt($cli['cli_email']);
                }
                if (!empty($cli['cli_identificacion'])) {
                    $cli['cli_identificacion'] = \DataProtection::decrypt($cli['cli_identificacion']);
                }
            }
            unset($cli);

            // Tarifas activas
            $stmt = $this->db->prepare("
                SELECT t.*, i.ins_nombre AS instalacion_nombre
                FROM instalaciones_entradas_tarifas t
                INNER JOIN instalaciones i ON t.ent_tar_instalacion_id = i.ins_instalacion_id
                WHERE t.ent_tar_tenant_id = ? AND t.ent_tar_estado = 'ACTIVO'
                ORDER BY t.ent_tar_instalacion_id, t.ent_tar_tipo, t.ent_tar_precio
            ");
            $stmt->execute([$this->tenantId]);
            $tarifas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['instalaciones'] = $instalaciones;
            $this->viewData['clientes']      = $clientes;
            $this->viewData['tarifas']       = $tarifas;
            $this->viewData['csrf_token']    = \Security::generateCsrfToken();
            $this->viewData['title']         = 'Vender Entrada';

            $this->renderModule('instalaciones/entradas/vender', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error vender entrada: " . $e->getMessage());
            $this->error('Error al cargar el formulario de venta');
        }
    }

    /* ═══════════════════════════════════════════
     * GUARDAR — Registra la venta de entrada
     * ═══════════════════════════════════════════ */
    public function guardar() {
        try {
            $instalacionId = (int)($this->post('instalacion_id') ?? 0);
            $clienteId     = (int)($this->post('cliente_id') ?? 0);
            $tipo          = trim($this->post('tipo') ?? 'GENERAL');
            $precio        = (float)($this->post('precio') ?? 0);
            $descuento     = (float)($this->post('descuento') ?? 0);
            $formaPago     = trim($this->post('forma_pago') ?? 'EFECTIVO');
            $montoMonedero = (float)($this->post('monto_monedero') ?? 0);
            $fechaEntrada  = trim($this->post('fecha_entrada') ?? date('Y-m-d'));
            $horaEntrada   = trim($this->post('hora_entrada') ?? date('H:i'));
            $observaciones = trim($this->post('observaciones') ?? '');

            // Validaciones
            if ($instalacionId < 1) {
                $this->error('Seleccione una instalación');
                return;
            }
            if (!in_array($tipo, ['GENERAL', 'VIP', 'CORTESIA', 'ABONADO'])) {
                $this->error('Tipo de entrada no válido');
                return;
            }
            if ($tipo !== 'CORTESIA' && $precio <= 0) {
                $this->error('El precio debe ser mayor a $0');
                return;
            }

            $total = max(0, $precio - $descuento);
            $montoEfectivo = max(0, $total - $montoMonedero);

            // Determinar forma de pago real
            if ($tipo === 'CORTESIA') {
                $total = 0;
                $montoMonedero = 0;
                $montoEfectivo = 0;
                $formaPago = 'CORTESIA';
            } elseif ($montoMonedero > 0 && $montoEfectivo > 0) {
                $formaPago = 'MIXTO';
            } elseif ($montoMonedero >= $total) {
                $formaPago = 'MONEDERO';
                $montoMonedero = $total;
                $montoEfectivo = 0;
            }

            // Validar monedero
            if ($montoMonedero > 0 && $clienteId > 0) {
                $saldo = $this->getSaldoCliente($clienteId);
                if ($montoMonedero > ($saldo + 0.01)) {
                    $this->error('Saldo de monedero insuficiente. Disponible: $' . number_format($saldo, 2));
                    return;
                }
            } elseif ($montoMonedero > 0 && $clienteId < 1) {
                $this->error('Para usar monedero debe seleccionar un cliente');
                return;
            }

            // Generar código único
            $codigo = $this->generarCodigoEntrada();

            $this->db->beginTransaction();

            // 1. Registrar entrada
            $stmt = $this->db->prepare("
                INSERT INTO instalaciones_entradas
                (ent_tenant_id, ent_instalacion_id, ent_cliente_id, ent_codigo, ent_tipo,
                 ent_precio, ent_descuento, ent_total, ent_forma_pago,
                 ent_monto_monedero, ent_monto_efectivo, ent_estado,
                 ent_fecha_entrada, ent_hora_entrada, ent_observaciones,
                 ent_usuario_registro, ent_fecha_registro)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'VENDIDA', ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $this->tenantId,
                $instalacionId,
                $clienteId > 0 ? $clienteId : null,
                $codigo,
                $tipo,
                $precio,
                $descuento,
                $total,
                $formaPago,
                $montoMonedero,
                $montoEfectivo,
                $fechaEntrada,
                $horaEntrada,
                $observaciones ?: null,
                $this->userId
            ]);
            $entradaId = $this->db->lastInsertId();

            // 2. Descontar monedero si aplica
            if ($montoMonedero > 0 && $clienteId > 0) {
                $this->descontarMonederoEntrada($clienteId, $montoMonedero, $entradaId);
            }

            $this->db->commit();

            $this->audit('entradas', $entradaId, 'ENTRADA_VENDIDA', null, [
                'codigo' => $codigo,
                'tipo' => $tipo,
                'total' => $total,
                'forma_pago' => $formaPago
            ]);

            $this->success([
                'redirect' => url('instalaciones', 'entrada', 'ticket') . '&id=' . $entradaId
            ], 'Entrada vendida — Código: ' . $codigo);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->logError("Error guardar entrada: " . $e->getMessage());
            $this->error('Error al registrar la entrada: ' . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════
     * TICKET — Comprobante / ticket imprimible
     * ═══════════════════════════════════════════ */
    public function ticket() {
        try {
            $entradaId = (int)($this->get('id') ?? 0);
            if ($entradaId < 1) {
                $this->error('Entrada no válida');
                return;
            }

            $stmt = $this->db->prepare("
                SELECT e.*,
                       i.ins_nombre AS instalacion_nombre,
                       i.ins_tipo AS instalacion_tipo,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS cliente_nombre,
                       c.cli_email AS cliente_email
                FROM instalaciones_entradas e
                INNER JOIN instalaciones i ON e.ent_instalacion_id = i.ins_instalacion_id
                LEFT JOIN clientes c ON e.ent_cliente_id = c.cli_cliente_id
                WHERE e.ent_entrada_id = ? AND e.ent_tenant_id = ?
            ");
            $stmt->execute([$entradaId, $this->tenantId]);
            $entrada = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$entrada) {
                $this->error('Entrada no encontrada');
                return;
            }

            // Descifrar campos sensibles del cliente
            if (!empty($entrada['cliente_email'])) {
                $entrada['cliente_email'] = \DataProtection::decrypt($entrada['cliente_email']);
            }

            $this->viewData['entrada'] = $entrada;
            $this->viewData['title']   = 'Ticket #' . $entrada['ent_codigo'];

            $this->renderModule('instalaciones/entradas/ticket', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error ticket: " . $e->getMessage());
            $this->error('Error al cargar el ticket');
        }
    }

    /* ═══════════════════════════════════════════
     * REGISTRAR INGRESO — Marca entrada como USADA
     * ═══════════════════════════════════════════ */
    public function registrarIngreso() {
        try {
            $entradaId = (int)($this->post('entrada_id') ?? $this->get('id') ?? 0);
            $codigo    = trim($this->post('codigo') ?? '');

            // Buscar por ID o por código
            if ($entradaId > 0) {
                $stmt = $this->db->prepare("
                    SELECT * FROM instalaciones_entradas
                    WHERE ent_entrada_id = ? AND ent_tenant_id = ?
                ");
                $stmt->execute([$entradaId, $this->tenantId]);
            } elseif (!empty($codigo)) {
                $stmt = $this->db->prepare("
                    SELECT * FROM instalaciones_entradas
                    WHERE ent_codigo = ? AND ent_tenant_id = ?
                ");
                $stmt->execute([$codigo, $this->tenantId]);
            } else {
                $this->error('Proporcione un ID o código de entrada');
                return;
            }

            $entrada = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$entrada) {
                $this->error('Entrada no encontrada');
                return;
            }

            if ($entrada['ent_estado'] !== 'VENDIDA') {
                $estadoMsgs = [
                    'USADA'   => 'Esta entrada ya fue utilizada',
                    'ANULADA' => 'Esta entrada está anulada',
                    'VENCIDA' => 'Esta entrada está vencida'
                ];
                $msg = $estadoMsgs[$entrada['ent_estado']] ?? 'Estado no válido para ingreso';
                $this->error($msg);
                return;
            }

            $stmt = $this->db->prepare("
                UPDATE instalaciones_entradas
                SET ent_estado = 'USADA',
                    ent_hora_entrada = NOW()
                WHERE ent_entrada_id = ?
            ");
            $stmt->execute([$entrada['ent_entrada_id']]);

            $this->audit('entradas', $entrada['ent_entrada_id'], 'INGRESO_REGISTRADO', null, [
                'codigo' => $entrada['ent_codigo']
            ]);

            $this->success([
                'entrada' => [
                    'id'     => $entrada['ent_entrada_id'],
                    'codigo' => $entrada['ent_codigo'],
                    'tipo'   => $entrada['ent_tipo'],
                    'estado' => 'USADA'
                ]
            ], 'Ingreso registrado — Código: ' . $entrada['ent_codigo']);

        } catch (\Exception $e) {
            $this->logError("Error registrar ingreso: " . $e->getMessage());
            $this->error('Error al registrar el ingreso');
        }
    }

    /* ═══════════════════════════════════════════
     * ANULAR — Anula una entrada y devuelve monedero
     * ═══════════════════════════════════════════ */
    public function anular() {
        try {
            $entradaId = (int)($this->post('entrada_id') ?? $this->get('id') ?? 0);
            $motivo    = trim($this->post('motivo') ?? '');

            if ($entradaId < 1) {
                $this->error('Entrada no válida');
                return;
            }

            $stmt = $this->db->prepare("
                SELECT * FROM instalaciones_entradas
                WHERE ent_entrada_id = ? AND ent_tenant_id = ?
            ");
            $stmt->execute([$entradaId, $this->tenantId]);
            $entrada = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$entrada) {
                $this->error('Entrada no encontrada');
                return;
            }

            if ($entrada['ent_estado'] === 'ANULADA') {
                $this->error('Esta entrada ya está anulada');
                return;
            }

            if ($entrada['ent_estado'] === 'USADA') {
                $this->error('No se puede anular una entrada ya utilizada');
                return;
            }

            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE instalaciones_entradas
                SET ent_estado = 'ANULADA',
                    ent_observaciones = CONCAT(COALESCE(ent_observaciones,''), '\n[ANULADA] ', ?)
                WHERE ent_entrada_id = ?
            ");
            $stmt->execute([$motivo ?: 'Sin motivo', $entradaId]);

            // Devolver monedero si aplica
            if ((float)$entrada['ent_monto_monedero'] > 0 && $entrada['ent_cliente_id']) {
                $this->devolverMonederoEntrada(
                    $entrada['ent_cliente_id'],
                    (float)$entrada['ent_monto_monedero'],
                    $entradaId
                );
            }

            $this->db->commit();

            $this->audit('entradas', $entradaId, 'ENTRADA_ANULADA', null, [
                'codigo' => $entrada['ent_codigo'],
                'motivo' => $motivo
            ]);

            $this->success([], 'Entrada anulada correctamente');

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->logError("Error anular entrada: " . $e->getMessage());
            $this->error('Error al anular la entrada');
        }
    }

    /* ═══════════════════════════════════════════
     * TARIFAS — Gestión de tarifas de entrada
     * ═══════════════════════════════════════════ */
    public function tarifas() {
        try {
            // Instalaciones
            $stmt = $this->db->prepare("
                SELECT ins_instalacion_id, ins_nombre
                FROM instalaciones
                WHERE ins_tenant_id = ? AND ins_estado = 'ACTIVO'
                ORDER BY ins_nombre
            ");
            $stmt->execute([$this->tenantId]);
            $instalaciones = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Tarifas con nombre de instalación
            $stmt = $this->db->prepare("
                SELECT t.*, i.ins_nombre AS instalacion_nombre
                FROM instalaciones_entradas_tarifas t
                INNER JOIN instalaciones i ON t.ent_tar_instalacion_id = i.ins_instalacion_id
                WHERE t.ent_tar_tenant_id = ?
                ORDER BY i.ins_nombre, t.ent_tar_tipo, t.ent_tar_precio
            ");
            $stmt->execute([$this->tenantId]);
            $tarifas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['instalaciones'] = $instalaciones;
            $this->viewData['tarifas']       = $tarifas;
            $this->viewData['csrf_token']    = \Security::generateCsrfToken();
            $this->viewData['title']         = 'Tarifas de Entrada';

            $this->renderModule('instalaciones/entradas/tarifas', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error tarifas: " . $e->getMessage());
            $this->error('Error al cargar las tarifas');
        }
    }

    /* ═══════════════════════════════════════════
     * GUARDAR TARIFA — Crear / actualizar tarifa
     * ═══════════════════════════════════════════ */
    public function guardarTarifa() {
        try {
            $tarifaId      = (int)($this->post('tarifa_id') ?? 0);
            $instalacionId = (int)($this->post('instalacion_id') ?? 0);
            $nombre        = trim($this->post('nombre') ?? '');
            $tipo          = trim($this->post('tipo') ?? 'GENERAL');
            $precio        = (float)($this->post('precio') ?? 0);
            $diaSemana     = $this->post('dia_semana');
            $horaInicio    = trim($this->post('hora_inicio') ?? '');
            $horaFin       = trim($this->post('hora_fin') ?? '');
            $estadoTarifa  = trim($this->post('estado') ?? 'ACTIVO');

            if ($instalacionId < 1) {
                $this->error('Seleccione una instalación');
                return;
            }
            if (empty($nombre)) {
                $this->error('El nombre de la tarifa es obligatorio');
                return;
            }
            if ($precio < 0) {
                $this->error('El precio no puede ser negativo');
                return;
            }

            if ($tarifaId > 0) {
                // Actualizar
                $stmt = $this->db->prepare("
                    UPDATE instalaciones_entradas_tarifas
                    SET ent_tar_instalacion_id = ?, ent_tar_nombre = ?, ent_tar_tipo = ?,
                        ent_tar_precio = ?, ent_tar_dia_semana = ?, ent_tar_hora_inicio = ?,
                        ent_tar_hora_fin = ?, ent_tar_estado = ?
                    WHERE ent_tar_id = ? AND ent_tar_tenant_id = ?
                ");
                $stmt->execute([
                    $instalacionId, $nombre, $tipo, $precio,
                    $diaSemana ?: null, $horaInicio ?: null, $horaFin ?: null,
                    $estadoTarifa, $tarifaId, $this->tenantId
                ]);
                $this->success([], 'Tarifa actualizada');
            } else {
                // Crear
                $stmt = $this->db->prepare("
                    INSERT INTO instalaciones_entradas_tarifas
                    (ent_tar_tenant_id, ent_tar_instalacion_id, ent_tar_nombre, ent_tar_tipo,
                     ent_tar_precio, ent_tar_dia_semana, ent_tar_hora_inicio, ent_tar_hora_fin,
                     ent_tar_estado, ent_tar_fecha_registro)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $this->tenantId, $instalacionId, $nombre, $tipo, $precio,
                    $diaSemana ?: null, $horaInicio ?: null, $horaFin ?: null,
                    $estadoTarifa
                ]);
                $this->success([], 'Tarifa creada exitosamente');
            }

        } catch (\Exception $e) {
            $this->logError("Error guardarTarifa: " . $e->getMessage());
            $this->error('Error al guardar la tarifa');
        }
    }

    /* ═══════════════════════════════════════════
     * OBTENER TARIFAS — API JSON por instalación
     * ═══════════════════════════════════════════ */
    public function obtenerTarifas() {
        $instalacionId = (int)($this->get('instalacion_id') ?? 0);
        if ($instalacionId < 1) {
            $this->success(['tarifas' => []]);
            return;
        }

        $diaSemana = date('N'); // 1=Lun, 7=Dom
        $horaActual = date('H:i:s');

        $stmt = $this->db->prepare("
            SELECT ent_tar_id, ent_tar_nombre, ent_tar_tipo, ent_tar_precio,
                   ent_tar_dia_semana, ent_tar_hora_inicio, ent_tar_hora_fin
            FROM instalaciones_entradas_tarifas
            WHERE ent_tar_tenant_id = ? AND ent_tar_instalacion_id = ? AND ent_tar_estado = 'ACTIVO'
            ORDER BY ent_tar_tipo, ent_tar_precio
        ");
        $stmt->execute([$this->tenantId, $instalacionId]);
        $tarifas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->success(['tarifas' => $tarifas]);
    }

    /* ═══════════════════════════════════════════
     * ESCANEAR — Pantalla de escaneo / búsqueda de código
     * ═══════════════════════════════════════════ */
    public function escanear() {
        try {
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title']      = 'Control de Acceso';

            $this->renderModule('instalaciones/entradas/escanear', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error escanear: " . $e->getMessage());
            $this->error('Error al cargar el escáner');
        }
    }

    /* ═══════════════════════════════════════════
     * BUSCAR CODIGO — API JSON busca entrada por código
     * ═══════════════════════════════════════════ */
    public function buscarCodigo() {
        $codigo = trim($this->get('codigo') ?? $this->post('codigo') ?? '');
        if (empty($codigo)) {
            $this->error('Código vacío');
            return;
        }

        $stmt = $this->db->prepare("
            SELECT e.*,
                   i.ins_nombre AS instalacion_nombre,
                   CONCAT(c.cli_nombres,' ',c.cli_apellidos) AS cliente_nombre
            FROM instalaciones_entradas e
            INNER JOIN instalaciones i ON e.ent_instalacion_id = i.ins_instalacion_id
            LEFT JOIN clientes c ON e.ent_cliente_id = c.cli_cliente_id
            WHERE e.ent_codigo = ? AND e.ent_tenant_id = ?
        ");
        $stmt->execute([$codigo, $this->tenantId]);
        $entrada = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$entrada) {
            $this->error('Código no encontrado: ' . $codigo);
            return;
        }

        $this->success(['entrada' => $entrada]);
    }

    /* ═════════════════════════════════════
     * MÉTODOS PRIVADOS
     * ═════════════════════════════════════ */

    private function generarCodigoEntrada() {
        $prefix = 'ENT';
        $fecha  = date('ymd');
        // Obtener correlativo del día
        $stmt = $this->db->prepare("
            SELECT COUNT(*) + 1 FROM instalaciones_entradas
            WHERE ent_tenant_id = ? AND DATE(ent_fecha_registro) = CURDATE()
        ");
        $stmt->execute([$this->tenantId]);
        $correlativo = (int)$stmt->fetchColumn();
        return $prefix . $fecha . str_pad($correlativo, 4, '0', STR_PAD_LEFT);
    }

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

    private function descontarMonederoEntrada($clienteId, $monto, $entradaId) {
        $stmt = $this->db->prepare("
            SELECT abo_abono_id, abo_saldo_disponible
            FROM instalaciones_abonos
            WHERE abo_cliente_id = ? AND abo_tenant_id = ? AND abo_estado = 'ACTIVO'
            ORDER BY abo_abono_id DESC LIMIT 1
        ");
        $stmt->execute([$clienteId, $this->tenantId]);
        $abono = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$abono || (float)$abono['abo_saldo_disponible'] < $monto) {
            throw new \Exception('Saldo de monedero insuficiente');
        }

        $saldoAnterior  = (float)$abono['abo_saldo_disponible'];
        $saldoPosterior = $saldoAnterior - $monto;

        $stmt = $this->db->prepare("
            UPDATE instalaciones_abonos
            SET abo_saldo_disponible = ?, abo_monto_utilizado = abo_monto_utilizado + ?
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
            VALUES (?, ?, ?, 'CONSUMO', ?, ?, ?, ?, 'ENTRADA', ?, 'MONEDERO', ?)
        ");
        $stmt->execute([
            $this->tenantId,
            $abono['abo_abono_id'],
            $clienteId,
            $monto,
            $saldoAnterior,
            $saldoPosterior,
            "Entrada #$entradaId",
            $entradaId,
            $this->userId
        ]);
    }

    private function devolverMonederoEntrada($clienteId, $monto, $entradaId) {
        $stmt = $this->db->prepare("
            SELECT abo_abono_id, abo_saldo_disponible
            FROM instalaciones_abonos
            WHERE abo_cliente_id = ? AND abo_tenant_id = ? AND abo_estado = 'ACTIVO'
            ORDER BY abo_abono_id DESC LIMIT 1
        ");
        $stmt->execute([$clienteId, $this->tenantId]);
        $abono = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$abono) return;

        $saldoAnterior  = (float)$abono['abo_saldo_disponible'];
        $saldoPosterior = $saldoAnterior + $monto;

        $stmt = $this->db->prepare("
            UPDATE instalaciones_abonos
            SET abo_saldo_disponible = ?, abo_monto_utilizado = GREATEST(0, abo_monto_utilizado - ?)
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
            VALUES (?, ?, ?, 'DEVOLUCION', ?, ?, ?, ?, 'ANULACION_ENTRADA', ?, 'MONEDERO', ?)
        ");
        $stmt->execute([
            $this->tenantId,
            $abono['abo_abono_id'],
            $clienteId,
            $monto,
            $saldoAnterior,
            $saldoPosterior,
            "Devolución entrada anulada #$entradaId",
            $entradaId,
            $this->userId
        ]);
    }
}
