<?php
/**
 * DigiSports Arena - Controlador de Reservas
 * Sistema completo de reserva de canchas con disponibilidad dinámica
 * 
 * @package DigiSports\Controllers\Reservas
 * @version 2.0.0
 */

namespace App\Controllers\Reservas;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class ReservaController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Arena';
    protected $moduloIcono = 'fas fa-building';
    protected $moduloColor = '#3B82F6';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'ARENA';
    }
    
    /**
     * Búsqueda de disponibilidad y creación de reserva
     */
    public function buscar() {
        try {
            // Aceptar datos de GET o POST
            $fecha = $this->post('fecha') ?? $this->get('fecha') ?? date('Y-m-d');
            $tipo_cancha = $this->post('tipo') ?? $this->get('tipo') ?? '';
            $instalacion_id = (int)($this->post('instalacion_id') ?? $this->get('instalacion_id') ?? 0);
            
            // Obtener instalaciones del tenant
            $stmt = $this->db->prepare("
                SELECT DISTINCT i.ins_instalacion_id AS instalacion_id, i.ins_nombre AS nombre
                FROM instalaciones i
                WHERE i.ins_tenant_id = ? AND i.ins_estado = 'ACTIVO'
                ORDER BY i.ins_nombre
            ");
            $stmt->execute([$this->tenantId]);
            $instalaciones = $stmt->fetchAll();
            
            // Obtener tipos de cancha
            $stmt = $this->db->prepare("
                SELECT DISTINCT can_tipo FROM instalaciones_canchas 
                WHERE can_tenant_id = ? AND can_estado = 'ACTIVO'
                ORDER BY can_tipo
            ");
            $stmt->execute([$this->tenantId]);
            $tipos = array_column($stmt->fetchAll(), 'tipo');
            
            // Si se especifica fecha e instalación, buscar disponibilidad
            $disponibilidades = [];
            
            if (!empty($fecha) && $instalacion_id > 0) {
                // Obtener canchas de la instalación (filtrar por tipo si se especifica)
                $sql = "SELECT can_cancha_id as cancha_id, can_nombre as nombre, can_tipo as tipo, can_capacidad_maxima as capacidad_maxima, can_ancho as ancho, can_largo as largo
                        FROM instalaciones_canchas
                        WHERE can_instalacion_id = ? AND can_tenant_id = ? AND can_estado = 'ACTIVO'";
                $params = [$instalacion_id, $this->tenantId];
                
                if (!empty($tipo_cancha)) {
                    $sql .= " AND can_tipo = ?";
                    $params[] = $tipo_cancha;
                }
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $canchas = $stmt->fetchAll();
                
                // Para cada cancha, obtener disponibilidad horaria
                foreach ($canchas as $cancha) {
                    // Obtener tarifas del día (día_semana calculado)
                    $fechaObj = new \DateTime($fecha);
                    $dia_semana = $fechaObj->format('w'); // 0=domingo, 6=sábado
                    
                    $stmt = $this->db->prepare("
                        SELECT tarifa_id, hora_inicio, hora_fin, precio
                        FROM tarifas
                        WHERE cancha_id = ? AND dia_semana = ? AND estado = 'ACTIVO'
                        ORDER BY hora_inicio
                    ");
                    $stmt->execute([$cancha['cancha_id'], $dia_semana]);
                    $tarifas = $stmt->fetchAll();
                    
                    // Obtener reservas y mantenimientos del día
                    $stmt = $this->db->prepare("
                        SELECT 
                            CASE 
                                WHEN tipo_bloqueo IS NOT NULL THEN 'MANTENIMIENTO'
                                ELSE 'RESERVA'
                            END as tipo,
                            hora_inicio,
                            hora_fin
                        FROM (
                            SELECT 'MANTENIMIENTO' as tipo_bloqueo,
                                   TIME(fecha_inicio) as hora_inicio,
                                   TIME(fecha_fin) as hora_fin
                            FROM mantenimientos
                            WHERE cancha_id = ? 
                            AND DATE(fecha_inicio) = ?
                            AND estado IN ('PROGRAMADO', 'EN_PROGRESO')
                            
                            UNION ALL
                            
                            SELECT NULL as tipo_bloqueo,
                                   r.res_hora_inicio,
                                   r.res_hora_fin
                            FROM instalaciones_reservas r
                            INNER JOIN instalaciones_canchas c ON c.can_instalacion_id = r.res_instalacion_id
                            WHERE c.can_cancha_id = ?
                            AND r.res_fecha_reserva = ?
                            AND r.res_estado IN ('CONFIRMADA', 'PENDIENTE')
                        ) bloques
                        ORDER BY hora_inicio
                    ");
                    $stmt->execute([
                        $cancha['cancha_id'],
                        $fecha,
                        $cancha['cancha_id'],
                        $fecha
                    ]);
                    $bloqueos = $stmt->fetchAll();
                    
                    // Calcular franjas disponibles
                    $franjas = [];
                    foreach ($tarifas as $tarifa) {
                        $disponible = true;
                        $razon = '';
                        
                        // Verificar si hay conflicto con bloqueos
                        foreach ($bloqueos as $bloqueo) {
                            $inicio_tarifa = strtotime($tarifa['hora_inicio']);
                            $fin_tarifa = strtotime($tarifa['hora_fin']);
                            $inicio_bloqueo = strtotime($bloqueo['hora_inicio']);
                            $fin_bloqueo = strtotime($bloqueo['hora_fin']);
                            
                            // Si hay solapamiento
                            if ($inicio_tarifa < $fin_bloqueo && $fin_tarifa > $inicio_bloqueo) {
                                $disponible = false;
                                $razon = $bloqueo['tipo'] === 'MANTENIMIENTO' 
                                    ? 'Mantenimiento programado'
                                    : 'Ya reservada';
                                break;
                            }
                        }
                        
                        $franjas[] = [
                            'tarifa_id' => $tarifa['tarifa_id'],
                            'hora_inicio' => $tarifa['hora_inicio'],
                            'hora_fin' => $tarifa['hora_fin'],
                            'precio' => $tarifa['precio'],
                            'disponible' => $disponible ? 'S' : 'N',
                            'razon' => $razon
                        ];
                    }
                    
                    $disponibilidades[] = [
                        'cancha' => $cancha,
                        'franjas' => $franjas
                    ];
                }
            }
            
            $this->viewData['fecha'] = $fecha;
            $this->viewData['tipo_cancha'] = $tipo_cancha;
            $this->viewData['instalacion_id'] = $instalacion_id;
            $this->viewData['instalaciones'] = $instalaciones;
            $this->viewData['tipos'] = $tipos;
            $this->viewData['disponibilidades'] = $disponibilidades;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Buscar Disponibilidad';
            $this->viewData['layout'] = 'main';
            
            $this->renderModule('reservas/buscar', $this->viewData);
            
        } catch (\Exception $e) {
            // DEBUG
            header('Content-Type: application/json');
            echo json_encode([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            exit;
        }
    }
    
    /**
     * Crear reserva
     */
    public function crear() {
        if (!$this->isPost()) {
            $this->error('Solicitud inválida');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        try {
            $cancha_id = (int)$this->post('cancha_id');
            $tarifa_id = (int)$this->post('tarifa_id');
            $fecha_reserva = $this->post('fecha_reserva');
            $nombre_cliente = trim($this->post('nombre_cliente'));
            $email_cliente = trim($this->post('email_cliente'));
            $telefono_cliente = trim($this->post('telefono_cliente') ?? '');
            $cantidad_personas = (int)$this->post('cantidad_personas');
            $notas = trim($this->post('notas') ?? '');
            
            // Validaciones
            $errors = [];
            
            if ($cancha_id < 1) {
                $errors[] = 'Cancha no válida';
            }
            
            if ($tarifa_id < 1) {
                $errors[] = 'Tarifa no válida';
            }
            
            if (empty($fecha_reserva)) {
                $errors[] = 'Fecha de reserva requerida';
            }
            
            if (empty($nombre_cliente) || strlen($nombre_cliente) < 3) {
                $errors[] = 'Nombre de cliente debe tener al menos 3 caracteres';
            }
            
            if (empty($email_cliente) || !filter_var($email_cliente, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email válido requerido';
            }
            
            if ($cantidad_personas < 1) {
                $errors[] = 'Cantidad de personas debe ser mayor a 0';
            }
            
            if (!empty($errors)) {
                $this->error(implode('. ', $errors));
            }
            
            // Obtener información de cancha y tarifa (incluir instalacion_id)
            $stmt = $this->db->prepare("
                SELECT c.*, c.can_instalacion_id as instalacion_id, t.precio, t.hora_inicio, t.hora_fin, t.dia_semana
                FROM instalaciones_canchas c
                INNER JOIN tarifas t ON c.can_cancha_id = t.can_cancha_id
                WHERE c.can_cancha_id = ? AND t.tarifa_id = ? 
                AND c.can_tenant_id = ? AND c.can_estado = 'ACTIVO'
            ");
            $stmt->execute([$cancha_id, $tarifa_id, $this->tenantId]);
            $info = $stmt->fetch();
            
            if (!$info) {
                $this->error('Cancha o tarifa no válida');
            }
            
            // Verificar capacidad
            if ($cantidad_personas > $info['capacidad_maxima']) {
                $this->error("Capacidad máxima: {$info['capacidad_maxima']} personas");
            }
            
            // Verificar que no haya conflicto de horario (usar instalacion_id)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total FROM instalaciones_reservas
                WHERE res_instalacion_id = ? 
                AND res_fecha_reserva = ?
                AND res_hora_inicio = ?
                AND res_estado IN ('CONFIRMADA', 'PENDIENTE')
            ");
            $stmt->execute([
                $info['instalacion_id'],
                $fecha_reserva,
                $info['hora_inicio']
            ]);
            
            if ($stmt->fetch()['total'] > 0) {
                $this->error('Esta franja horaria ya fue reservada');
            }
            
            // Buscar o crear cliente
            $stmt = $this->db->prepare("
                SELECT cli_cliente_id AS cliente_id FROM clientes 
                WHERE cli_tenant_id = ? AND cli_email = ?
            ");
            $stmt->execute([$this->tenantId, $email_cliente]);
            $cliente = $stmt->fetch();
            
            if ($cliente) {
                $cliente_id = $cliente['cliente_id'];
            } else {
                // Separar nombres y apellidos (asumimos primer espacio divide)
                $partes = explode(' ', $nombre_cliente, 2);
                $nombres = $partes[0];
                $apellidos = $partes[1] ?? '';
                
                // Crear nuevo cliente (tipo_identificacion e identificacion son requeridos)
                $stmt = $this->db->prepare("
                    INSERT INTO clientes (cli_tenant_id, cli_tipo_identificacion, cli_identificacion, cli_nombres, cli_apellidos, cli_email, cli_telefono, cli_estado)
                    VALUES (?, 'PAS', ?, ?, ?, ?, ?, 'A')
                ");
                // Usar email como identificación temporal si no tenemos cédula
                $identificacion_temp = 'TMP' . time();
                $stmt->execute([$this->tenantId, $identificacion_temp, $nombres, $apellidos, $email_cliente, $telefono_cliente]);
                $cliente_id = $this->db->lastInsertId();
            }
            
            // Calcular duración en minutos
            $inicio = new \DateTime($info['hora_inicio']);
            $fin = new \DateTime($info['hora_fin']);
            $duracion_minutos = ($fin->getTimestamp() - $inicio->getTimestamp()) / 60;
            
            // Calcular precio total
            $precio_total = $info['precio'];
            
            // Crear reserva con estructura correcta
            $stmt = $this->db->prepare("
                INSERT INTO instalaciones_reservas (
                    res_tenant_id, res_instalacion_id, res_cliente_id,
                    res_fecha_reserva, res_hora_inicio, res_hora_fin,
                    res_duracion_minutos, res_tarifa_aplicada_id,
                    res_precio_base, res_precio_total,
                    res_estado, res_observaciones, res_usuario_registro
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDIENTE', ?, ?)
            ");
            
            $stmt->execute([
                $this->tenantId,
                $info['instalacion_id'],
                $cliente_id,
                $fecha_reserva,
                $info['hora_inicio'],
                $info['hora_fin'],
                $duracion_minutos,
                $tarifa_id,
                $precio_total,
                $precio_total,
                $notas,
                $this->userId
            ]);
            
            $reserva_id = $this->db->lastInsertId();
            
            // Auditoría
            $this->audit('reservas', $reserva_id, 'INSERT', [], [
                'instalacion_id' => $info['instalacion_id'],
                'cliente_id' => $cliente_id,
                'cliente' => $nombre_cliente,
                'precio' => $precio_total
            ]);
            
            \Security::logSecurityEvent('RESERVA_CREATED', "Reserva ID: {$reserva_id}");
            
            // Responder con JSON para AJAX
            $this->success([
                'reserva_id' => $reserva_id,
                'redirect' => url('reservas', 'reserva')
            ], '¡Reserva creada exitosamente! Pendiente de confirmación.');
            
        } catch (\Exception $e) {
            $this->logError("Error al crear reserva: " . $e->getMessage());
            $this->error('Error al crear la reserva');
        }
    }
    
    /**
     * Ver confirmación de reserva
     */
    public function confirmacion() {
        $reserva_id = (int)$this->get('id');
        
        if ($reserva_id < 1) {
            $this->error('Reserva no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, c.can_nombre as cancha_nombre, c.can_tipo as cancha_tipo,
                       i.ins_nombre as instalacion_nombre
                FROM instalaciones_reservas r
                INNER JOIN instalaciones_canchas c ON r.res_instalacion_id = c.can_instalacion_id
                INNER JOIN instalaciones i ON c.can_instalacion_id = i.ins_instalacion_id
                WHERE r.res_reserva_id = ? AND r.res_tenant_id = ?
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $reserva = $stmt->fetch();
            
            if (!$reserva) {
                $this->error('Reserva no encontrada');
            }
            
            // Obtener detalles de líneas (si existen en reservas_lineas)
            $lineas = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT rl.*, t.hora_inicio as tarifa_hora_inicio, t.hora_fin as tarifa_hora_fin
                    FROM reservas_lineas rl
                    INNER JOIN tarifas t ON rl.tarifa_id = t.tarifa_id
                    WHERE rl.reserva_id = ?
                ");
                $stmt->execute([$reserva_id]);
                $lineas = $stmt->fetchAll();
            } catch (\Exception $e) {
                // Si la tabla no existe o falla, generamos línea virtual
            }
            
            // Si no hay líneas de detalle, generar una línea resumen desde la reserva
            if (empty($lineas)) {
                $lineas = [[
                    'hora_inicio' => $reserva['hora_inicio'],
                    'hora_fin'    => $reserva['hora_fin'],
                    'precio_unitario' => $reserva['precio_base'] ?? $reserva['precio_total'],
                    'cantidad'    => 1,
                    'precio_total' => $reserva['precio_total']
                ]];
            }
            
            $this->viewData['reserva'] = $reserva;
            $this->viewData['lineas'] = $lineas;
            $this->viewData['title'] = 'Confirmación de Reserva';
            $this->viewData['layout'] = 'main';
            
            $this->renderModule('reservas/confirmacion', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener confirmación: " . $e->getMessage());
            $this->error('Error al cargar la confirmación');
        }
    }
    
    /**
     * Listar reservas
     */
    public function index() {
        try {
            // Filtros desde POST o GET
            $estado = $this->post('estado') ?? $this->get('estado') ?? '';
            $estadoPago = $this->post('estado_pago') ?? $this->get('estado_pago') ?? '';
            $buscar = trim($this->post('buscar') ?? $this->get('buscar') ?? '');
            $fechaDesde = $this->post('fecha_desde') ?? $this->get('fecha_desde') ?? '';
            $fechaHasta = $this->post('fecha_hasta') ?? $this->get('fecha_hasta') ?? '';
            $pagina = max(1, (int)($this->post('pagina') ?? $this->get('pagina') ?? 1));
            $perPage = 15;
            $offset = ($pagina - 1) * $perPage;
            
            // Query principal con estado_pago, monto_pagado, saldo_pendiente
            $query = "
                SELECT r.*, 
                       i.ins_nombre as instalacion_nombre,
                       c.cli_nombres as cliente_nombre,
                       c.cli_apellidos as cliente_apellidos
                FROM instalaciones_reservas r
                INNER JOIN instalaciones i ON r.res_instalacion_id = i.ins_instalacion_id
                LEFT JOIN clientes c ON r.res_cliente_id = c.cli_cliente_id
                WHERE r.res_tenant_id = ?
            ";
            
            $params = [$this->tenantId];
            $countWhere = "WHERE res_tenant_id = ?";
            $countParams = [$this->tenantId];
            
            // Filtro estado reserva
            if (!empty($estado)) {
                $query .= " AND r.res_estado = ?";
                $params[] = $estado;
                $countWhere .= " AND res_estado = ?";
                $countParams[] = $estado;
            }
            
            // Filtro estado pago
            if (!empty($estadoPago)) {
                $query .= " AND r.res_estado_pago = ?";
                $params[] = $estadoPago;
                $countWhere .= " AND res_estado_pago = ?";
                $countParams[] = $estadoPago;
            }
            
            // Filtro búsqueda (nombre cliente o ID reserva)
            if (!empty($buscar)) {
                $query .= " AND (c.cli_nombres LIKE ? OR c.cli_apellidos LIKE ? OR CONCAT(c.cli_nombres,' ',c.cli_apellidos) LIKE ? OR r.res_reserva_id = ?)";
                $like = "%{$buscar}%";
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
                $params[] = (int)$buscar;
                // Count no incluye JOIN; usar subquery
                $countWhere .= " AND (res_cliente_id IN (SELECT cli_cliente_id FROM clientes WHERE cli_nombres LIKE ? OR cli_apellidos LIKE ?) OR res_reserva_id = ?)";
                $countParams[] = $like;
                $countParams[] = $like;
                $countParams[] = (int)$buscar;
            }
            
            // Filtro rango de fechas
            if (!empty($fechaDesde)) {
                $query .= " AND r.res_fecha_reserva >= ?";
                $params[] = $fechaDesde;
                $countWhere .= " AND res_fecha_reserva >= ?";
                $countParams[] = $fechaDesde;
            }
            if (!empty($fechaHasta)) {
                $query .= " AND r.res_fecha_reserva <= ?";
                $params[] = $fechaHasta;
                $countWhere .= " AND res_fecha_reserva <= ?";
                $countParams[] = $fechaHasta;
            }
            
            $query .= " ORDER BY r.res_fecha_reserva DESC, r.res_hora_inicio DESC";
            
            // Total registros con filtros
            $countQuery = "SELECT COUNT(*) as total FROM instalaciones_reservas {$countWhere}";
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute($countParams);
            $totalRegistros = (int)$stmt->fetch()['total'];
            
            // Paginación
            $query .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $reservas = $stmt->fetchAll();
            
            // ── KPIs resumen ──
            $kpis = $this->getReservasKPIs();
            
            $this->viewData['reservas'] = $reservas;
            $this->viewData['totalRegistros'] = $totalRegistros;
            $this->viewData['pagina'] = $pagina;
            $this->viewData['totalPaginas'] = (int)ceil($totalRegistros / $perPage);
            $this->viewData['estado'] = $estado;
            $this->viewData['estado_pago'] = $estadoPago;
            $this->viewData['buscar'] = $buscar;
            $this->viewData['fecha_desde'] = $fechaDesde;
            $this->viewData['fecha_hasta'] = $fechaHasta;
            $this->viewData['kpis'] = $kpis;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Gestión de Reservas';
            $this->viewData['layout'] = 'main';
            
            $this->renderModule('reservas/index', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al listar reservas: " . $e->getMessage());
            $this->error('Error al cargar las reservas');
        }
    }
    
    /**
     * KPIs resumen para la vista index
     */
    private function getReservasKPIs() {
        $hoy = date('Y-m-d');
        $inicioMes = date('Y-m-01');
        $kpis = [
            'hoy' => 0,
            'pendientes_pago' => 0,
            'recaudado_mes' => 0,
            'por_cobrar' => 0
        ];
        
        try {
            // Reservas de hoy
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM instalaciones_reservas WHERE res_tenant_id = ? AND res_fecha_reserva = ? AND res_estado IN ('PENDIENTE','CONFIRMADA')");
            $stmt->execute([$this->tenantId, $hoy]);
            $kpis['hoy'] = (int)$stmt->fetchColumn();
            
            // Pendientes de pago
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM instalaciones_reservas WHERE res_tenant_id = ? AND res_estado_pago IN ('PENDIENTE','PARCIAL') AND res_estado != 'CANCELADA'");
            $stmt->execute([$this->tenantId]);
            $kpis['pendientes_pago'] = (int)$stmt->fetchColumn();
            
            // Recaudado este mes (pagos reales)
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(pag_monto),0) FROM instalaciones_reserva_pagos WHERE pag_tenant_id = ? AND pag_fecha_pago >= ? AND pag_estado = 'COMPLETADO'");
            $stmt->execute([$this->tenantId, $inicioMes]);
            $kpis['recaudado_mes'] = (float)$stmt->fetchColumn();
            
            // Saldo por cobrar total
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(res_saldo_pendiente),0) FROM instalaciones_reservas WHERE res_tenant_id = ? AND res_estado_pago IN ('PENDIENTE','PARCIAL') AND res_estado != 'CANCELADA'");
            $stmt->execute([$this->tenantId]);
            $kpis['por_cobrar'] = (float)$stmt->fetchColumn();
            
        } catch (\Exception $e) {
            error_log("KPIs reservas error: " . $e->getMessage());
        }
        
        return $kpis;
    }
    
    /**
     * Ver detalles de reserva
     */
    public function ver() {
        $reserva_id = (int)$this->get('id');
        
        if ($reserva_id < 1) {
            $this->error('Reserva no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT r.res_reserva_id AS reserva_id,
                       r.res_tenant_id AS tenant_id,
                       r.res_instalacion_id AS instalacion_id,
                       r.res_cliente_id AS cliente_id,
                       r.res_fecha_reserva AS fecha_reserva,
                       r.res_hora_inicio AS hora_inicio,
                       r.res_hora_fin AS hora_fin,
                       r.res_duracion_minutos AS duracion_minutos,
                       r.res_es_recurrente AS es_recurrente,
                       r.res_reserva_padre_id AS reserva_padre_id,
                       r.res_recurrencia_config AS recurrencia_config,
                       r.res_tarifa_aplicada_id AS tarifa_aplicada_id,
                       r.res_precio_base AS precio_base,
                       r.res_descuento_monto AS descuento_monto,
                       r.res_precio_total AS precio_total,
                       r.res_abono_utilizado AS abono_utilizado,
                       r.res_saldo_pendiente AS saldo_pendiente,
                       r.res_estado AS estado,
                       r.res_estado_pago AS estado_pago,
                       r.res_monto_pagado AS monto_pagado,
                       r.res_requiere_confirmacion AS requiere_confirmacion,
                       r.res_fecha_confirmacion AS fecha_confirmacion,
                       r.res_observaciones AS observaciones,
                       r.res_motivo_cancelacion AS motivo_cancelacion,
                       r.res_fecha_cancelacion AS fecha_cancelacion,
                       r.res_fecha_actualizacion AS fecha_actualizacion,
                       r.res_fecha_registro AS fecha_registro,
                       r.res_usuario_registro AS usuario_registro,
                       i.ins_nombre as instalacion_nombre,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) as cliente_nombre,
                       c.cli_email as cliente_email,
                       c.cli_telefono as cliente_telefono
                FROM instalaciones_reservas r
                INNER JOIN instalaciones i ON r.res_instalacion_id = i.ins_instalacion_id
                INNER JOIN clientes c ON r.res_cliente_id = c.cli_cliente_id
                WHERE r.res_reserva_id = ? AND r.res_tenant_id = ?
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $reserva = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$reserva) {
                $this->error('Reserva no encontrada');
            }

            // Descifrar campos sensibles del cliente (vienen con alias)
            if (!empty($reserva['cliente_email'])) {
                $reserva['cliente_email'] = \DataProtection::decrypt($reserva['cliente_email']);
            }
            if (!empty($reserva['cliente_telefono'])) {
                $reserva['cliente_telefono'] = \DataProtection::decrypt($reserva['cliente_telefono']);
            }
            
            // Obtener historial de pagos
            $stmtPagos = $this->db->prepare("
                SELECT pag_pago_id, pag_monto, pag_tipo_pago, pag_referencia,
                       pag_estado, pag_fecha_pago
                FROM instalaciones_reserva_pagos
                WHERE pag_reserva_id = ? AND pag_tenant_id = ?
                ORDER BY pag_fecha_pago DESC
            ");
            $stmtPagos->execute([$reserva_id, $this->tenantId]);
            $pagos = $stmtPagos->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->viewData['reserva'] = $reserva;
            $this->viewData['pagos'] = $pagos;
            $this->viewData['title'] = 'Detalles de Reserva #' . $reserva_id;
            $this->viewData['layout'] = 'main';
            
            $this->renderModule('reservas/ver', $this->viewData);
            
        } catch (\Exception $e) {
            // DEBUG
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage(), 'line' => $e->getLine()]);
            exit;
        }
    }
    
    /**
     * Confirmar reserva (cambiar estado)
     */
    public function confirmar() {
        $reserva_id = (int)$this->get('id');
        
        if ($reserva_id < 1) {
            $this->error('Reserva no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM instalaciones_reservas
                WHERE res_reserva_id = ? AND res_tenant_id = ?
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $reserva = $stmt->fetch();
            
            if (!$reserva) {
                $this->error('Reserva no encontrada');
            }
            
            // Actualizar estado
            $stmt = $this->db->prepare("
                UPDATE instalaciones_reservas
                SET res_estado = 'CONFIRMADA',
                    res_fecha_confirmacion = NOW(),
                    res_fecha_actualizacion = NOW()
                WHERE res_reserva_id = ?
            ");
            $stmt->execute([$reserva_id]);
            
            // Auditoría
            $this->audit('reservas', $reserva_id, 'STATUS_CHANGE',
                        ['estado' => $reserva['res_estado']],
                        ['estado' => 'CONFIRMADA']);
            
            \Security::logSecurityEvent('RESERVA_CONFIRMED', "Reserva ID: {$reserva_id}");
            
            $this->success([
                'redirect' => url('reservas', 'reserva', 'index')
            ], 'Reserva confirmada exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al confirmar reserva: " . $e->getMessage());
            $this->error('Error al confirmar la reserva');
        }
    }
    
    /**
     * Completar reserva (marcar como finalizada)
     */
    public function completar() {
        $reserva_id = (int)$this->get('id');
        
        if ($reserva_id < 1) {
            $this->error('Reserva no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM instalaciones_reservas
                WHERE res_reserva_id = ? AND res_tenant_id = ?
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $reserva = $stmt->fetch();
            
            if (!$reserva) {
                $this->error('Reserva no encontrada');
            }
            
            if (!in_array($reserva['res_estado'], ['CONFIRMADA', 'PENDIENTE'])) {
                $this->error('Solo se pueden completar reservas confirmadas o pendientes');
            }
            
            // Actualizar estado
            $stmt = $this->db->prepare("
                UPDATE instalaciones_reservas
                SET res_estado = 'COMPLETADA',
                    res_fecha_actualizacion = NOW()
                WHERE res_reserva_id = ?
            ");
            $stmt->execute([$reserva_id]);
            
            // Auditoría
            $this->audit('reservas', $reserva_id, 'STATUS_CHANGE',
                        ['estado' => $reserva['res_estado']],
                        ['estado' => 'COMPLETADA']);
            
            \Security::logSecurityEvent('RESERVA_COMPLETED', "Reserva ID: {$reserva_id}");
            
            $this->success([
                'redirect' => url('reservas', 'reserva', 'ver', ['id' => $reserva_id])
            ], 'Reserva marcada como completada');
            
        } catch (\Exception $e) {
            $this->logError("Error al completar reserva: " . $e->getMessage());
            $this->error('Error al completar la reserva');
        }
    }
    
    /**
     * Cancelar reserva
     */
    public function cancelar() {
        $reserva_id = (int)$this->get('id');
        $motivo = trim($this->get('motivo') ?? '');
        
        if ($reserva_id < 1) {
            $this->error('Reserva no válida');
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM instalaciones_reservas
                WHERE res_reserva_id = ? AND res_tenant_id = ?
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $reserva = $stmt->fetch();
            
            if (!$reserva) {
                $this->error('Reserva no encontrada');
            }
            
            // Actualizar estado
            $stmt = $this->db->prepare("
                UPDATE instalaciones_reservas
                SET res_estado = 'CANCELADA',
                    res_motivo_cancelacion = ?,
                    res_fecha_cancelacion = NOW(),
                    res_fecha_actualizacion = NOW()
                WHERE res_reserva_id = ?
            ");
            $stmt->execute([$motivo, $reserva_id]);
            
            // Auditoría
            $this->audit('reservas', $reserva_id, 'CANCELLED',
                        ['estado' => $reserva['res_estado']],
                        ['estado' => 'CANCELADA', 'motivo' => $motivo]);
            
            \Security::logSecurityEvent('RESERVA_CANCELLED', "Reserva ID: {$reserva_id}");
            
            $this->success([
                'redirect' => url('reservas', 'reserva', 'index')
            ], 'Reserva cancelada');
            
        } catch (\Exception $e) {
            $this->logError("Error al cancelar reserva: " . $e->getMessage());
            $this->error('Error al cancelar la reserva');
        }
    }
    
    /**
     * Obtener disponibilidad en AJAX (JSON)
     */
    public function obtenerDisponibilidad() {
        try {
            $cancha_id = (int)$this->get('cancha_id');
            $fecha = $this->get('fecha');
            
            if ($cancha_id < 1 || empty($fecha)) {
                $this->error('Parámetros inválidos');
            }
            
            // Obtener día de la semana
            $fechaObj = new \DateTime($fecha);
            $dia_semana = $fechaObj->format('w');
            
            // Obtener el instalacion_id de la cancha
            $stmtCancha = $this->db->prepare("SELECT can_cancha_id as cancha_id, can_instalacion_id as instalacion_id FROM instalaciones_canchas WHERE can_cancha_id = ? AND can_tenant_id = ?");
            $stmtCancha->execute([$cancha_id, $this->tenantId]);
            $canchaInfo = $stmtCancha->fetch();
            if (!$canchaInfo) {
                $this->error('Cancha no encontrada');
            }

            // Obtener tarifas disponibles
            $stmt = $this->db->prepare("
                SELECT tarifa_id, hora_inicio, hora_fin, precio
                FROM tarifas
                WHERE cancha_id = ? AND dia_semana = ? AND estado = 'ACTIVO'
                ORDER BY hora_inicio
            ");
            $stmt->execute([$cancha_id, $dia_semana]);
            $tarifas = $stmt->fetchAll();
            
            // Obtener reservas confirmadas
            $stmt = $this->db->prepare("
                SELECT res_hora_inicio as hora_inicio, res_hora_fin as hora_fin
                FROM instalaciones_reservas
                WHERE res_instalacion_id = ? AND res_fecha_reserva = ?
                AND res_estado IN ('CONFIRMADA', 'PENDIENTE')
            ");
            $stmt->execute([$canchaInfo['instalacion_id'], $fecha]);
            $reservas = $stmt->fetchAll();
            
            // Obtener mantenimientos
            $stmt = $this->db->prepare("
                SELECT TIME(fecha_inicio) as hora_inicio,
                       TIME(fecha_fin) as hora_fin
                FROM mantenimientos
                WHERE cancha_id = ? AND DATE(fecha_inicio) = ?
                AND estado IN ('PROGRAMADO', 'EN_PROGRESO')
            ");
            $stmt->execute([$cancha_id, $fecha]);
            $mantenimientos = $stmt->fetchAll();
            
            $bloqueos = array_merge($reservas, $mantenimientos);
            
            // Calcular franjas disponibles
            $franjas = [];
            foreach ($tarifas as $tarifa) {
                $disponible = true;
                
                foreach ($bloqueos as $bloqueo) {
                    $inicio_tarifa = strtotime($tarifa['hora_inicio']);
                    $fin_tarifa = strtotime($tarifa['hora_fin']);
                    $inicio_bloqueo = strtotime($bloqueo['hora_inicio']);
                    $fin_bloqueo = strtotime($bloqueo['hora_fin']);
                    
                    if ($inicio_tarifa < $fin_bloqueo && $fin_tarifa > $inicio_bloqueo) {
                        $disponible = false;
                        break;
                    }
                }
                
                $franjas[] = [
                    'tarifa_id' => $tarifa['tarifa_id'],
                    'hora_inicio' => $tarifa['hora_inicio'],
                    'hora_fin' => $tarifa['hora_fin'],
                    'precio' => (float)$tarifa['precio'],
                    'disponible' => $disponible
                ];
            }
            
            $this->success($franjas);
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener disponibilidad: " . $e->getMessage());
            $this->error('Error al obtener disponibilidad');
        }
    }
    
    /**
     * Editar reserva (solo PENDIENTE o CONFIRMADA)
     */
    public function editar() {
        $reserva_id = (int)$this->get('id');

        if ($reserva_id < 1) {
            $this->error('Reserva no válida');
        }

        try {
            // Obtener reserva actual
            $stmt = $this->db->prepare("
                SELECT r.*,
                       i.ins_nombre as instalacion_nombre,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) as cliente_nombre,
                       c.cli_email as cliente_email,
                       c.cli_telefono as cliente_telefono,
                       c.cli_cliente_id as cliente_id_ref
                FROM reservas r
                INNER JOIN instalaciones i ON r.instalacion_id = i.ins_instalacion_id
                INNER JOIN clientes c ON r.cliente_id = c.cli_cliente_id
                WHERE r.reserva_id = ? AND r.tenant_id = ?
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $reserva = $stmt->fetch();

            if (!$reserva) {
                $this->error('Reserva no encontrada');
            }

            // Descifrar campos sensibles del cliente (vienen con alias)
            if (!empty($reserva['cliente_email'])) {
                $reserva['cliente_email'] = \DataProtection::decrypt($reserva['cliente_email']);
            }
            if (!empty($reserva['cliente_telefono'])) {
                $reserva['cliente_telefono'] = \DataProtection::decrypt($reserva['cliente_telefono']);
            }

            if (!in_array($reserva['estado'], ['PENDIENTE', 'CONFIRMADA'])) {
                $this->error('Solo se pueden editar reservas pendientes o confirmadas');
            }

            // ── Si es POST, procesar la actualización ──
            if ($this->isPost()) {
                if (!$this->validateCsrf()) {
                    $this->error('Token de seguridad inválido', 403);
                }

                $fecha_reserva = $this->post('fecha_reserva');
                $hora_inicio = $this->post('hora_inicio');
                $hora_fin = $this->post('hora_fin');
                $observaciones = trim($this->post('observaciones') ?? '');

                // Validaciones
                $errors = [];
                if (empty($fecha_reserva)) {
                    $errors[] = 'Fecha de reserva requerida';
                }
                if (empty($hora_inicio) || empty($hora_fin)) {
                    $errors[] = 'Horario de inicio y fin requeridos';
                }
                if ($hora_inicio >= $hora_fin) {
                    $errors[] = 'La hora de fin debe ser posterior a la de inicio';
                }
                if (!empty($errors)) {
                    $this->error(implode('. ', $errors));
                }

                // Verificar que no haya conflicto de horario (excluyendo esta reserva)
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total FROM reservas
                    WHERE instalacion_id = ?
                    AND fecha_reserva = ?
                    AND hora_inicio < ? AND hora_fin > ?
                    AND estado IN ('CONFIRMADA', 'PENDIENTE')
                    AND reserva_id != ?
                ");
                $stmt->execute([
                    $reserva['instalacion_id'],
                    $fecha_reserva,
                    $hora_fin,
                    $hora_inicio,
                    $reserva_id
                ]);

                if ($stmt->fetch()['total'] > 0) {
                    $this->error('La franja horaria seleccionada tiene conflicto con otra reserva');
                }

                // Verificar conflicto con mantenimientos
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total FROM mantenimientos
                    WHERE cancha_id = (
                        SELECT can_cancha_id FROM instalaciones_canchas WHERE can_instalacion_id = ? AND can_tenant_id = ? LIMIT 1
                    )
                    AND DATE(fecha_inicio) = ?
                    AND TIME(fecha_inicio) < ? AND TIME(fecha_fin) > ?
                    AND estado IN ('PROGRAMADO', 'EN_PROGRESO')
                ");
                $stmt->execute([
                    $reserva['instalacion_id'],
                    $this->tenantId,
                    $fecha_reserva,
                    $hora_fin,
                    $hora_inicio
                ]);

                if ($stmt->fetch()['total'] > 0) {
                    $this->error('La franja horaria tiene conflicto con un mantenimiento programado');
                }

                // Recalcular duración
                $inicio = new \DateTime($hora_inicio);
                $fin = new \DateTime($hora_fin);
                $duracion_minutos = ($fin->getTimestamp() - $inicio->getTimestamp()) / 60;

                // Buscar tarifa correspondiente
                $fechaObj = new \DateTime($fecha_reserva);
                $dia_semana = $fechaObj->format('w');
                $stmt = $this->db->prepare("
                    SELECT t.tarifa_id, t.precio
                    FROM tarifas t
                    INNER JOIN instalaciones_canchas ca ON t.can_cancha_id = ca.can_cancha_id
                    WHERE ca.can_instalacion_id = ? AND ca.can_tenant_id = ?
                    AND t.dia_semana = ? AND t.hora_inicio = ? AND t.hora_fin = ?
                    AND t.estado = 'ACTIVO'
                    LIMIT 1
                ");
                $stmt->execute([
                    $reserva['instalacion_id'],
                    $this->tenantId,
                    $dia_semana,
                    $hora_inicio,
                    $hora_fin
                ]);
                $tarifa = $stmt->fetch();

                $precio_total = $tarifa ? (float)$tarifa['precio'] : (float)$reserva['precio_total'];
                $tarifa_id = $tarifa ? $tarifa['tarifa_id'] : $reserva['tarifa_aplicada_id'];

                // Guardar datos anteriores para auditoría
                $antes = [
                    'fecha_reserva' => $reserva['fecha_reserva'],
                    'hora_inicio' => $reserva['hora_inicio'],
                    'hora_fin' => $reserva['hora_fin'],
                    'observaciones' => $reserva['observaciones'],
                    'precio_total' => $reserva['precio_total']
                ];

                // Actualizar reserva
                $stmt = $this->db->prepare("
                    UPDATE reservas
                    SET fecha_reserva = ?,
                        hora_inicio = ?,
                        hora_fin = ?,
                        duracion_minutos = ?,
                        tarifa_aplicada_id = ?,
                        precio_base = ?,
                        precio_total = ?,
                        saldo_pendiente = ? - COALESCE(monto_pagado, 0),
                        observaciones = ?,
                        fecha_actualizacion = NOW()
                    WHERE reserva_id = ? AND tenant_id = ?
                ");
                $stmt->execute([
                    $fecha_reserva,
                    $hora_inicio,
                    $hora_fin,
                    $duracion_minutos,
                    $tarifa_id,
                    $precio_total,
                    $precio_total,
                    $precio_total,
                    $observaciones,
                    $reserva_id,
                    $this->tenantId
                ]);

                // Auditoría
                $despues = [
                    'fecha_reserva' => $fecha_reserva,
                    'hora_inicio' => $hora_inicio,
                    'hora_fin' => $hora_fin,
                    'observaciones' => $observaciones,
                    'precio_total' => $precio_total
                ];
                $this->audit('reservas', $reserva_id, 'UPDATE', $antes, $despues);
                \Security::logSecurityEvent('RESERVA_UPDATED', "Reserva ID: {$reserva_id}");

                $this->success([
                    'redirect' => url('reservas', 'reserva', 'ver', ['id' => $reserva_id])
                ], 'Reserva actualizada exitosamente');
                return;
            }

            // ── GET: mostrar formulario de edición ──

            // Obtener tarifas de la instalación para el día de la reserva
            $fechaObj = new \DateTime($reserva['fecha_reserva']);
            $dia_semana = $fechaObj->format('w');

            $stmt = $this->db->prepare("
                SELECT DISTINCT t.tarifa_id, t.hora_inicio, t.hora_fin, t.precio
                FROM tarifas t
                INNER JOIN instalaciones_canchas ca ON t.can_cancha_id = ca.can_cancha_id
                WHERE ca.can_instalacion_id = ? AND ca.can_tenant_id = ?
                AND t.dia_semana = ? AND t.estado = 'ACTIVO'
                ORDER BY t.hora_inicio
            ");
            $stmt->execute([$reserva['instalacion_id'], $this->tenantId, $dia_semana]);
            $tarifas = $stmt->fetchAll();

            // Obtener bloques ocupados (excluyendo esta reserva) para mostrar disponibilidad
            $stmt = $this->db->prepare("
                SELECT hora_inicio, hora_fin, 'RESERVA' as tipo
                FROM reservas
                WHERE instalacion_id = ? AND fecha_reserva = ?
                AND estado IN ('CONFIRMADA', 'PENDIENTE')
                AND reserva_id != ?
                UNION ALL
                SELECT TIME(fecha_inicio) as hora_inicio, TIME(fecha_fin) as hora_fin, 'MANTENIMIENTO' as tipo
                FROM mantenimientos
                WHERE cancha_id = (
                    SELECT can_cancha_id FROM instalaciones_canchas WHERE can_instalacion_id = ? AND can_tenant_id = ? LIMIT 1
                )
                AND DATE(fecha_inicio) = ?
                AND estado IN ('PROGRAMADO', 'EN_PROGRESO')
            ");
            $stmt->execute([
                $reserva['instalacion_id'],
                $reserva['fecha_reserva'],
                $reserva_id,
                $reserva['instalacion_id'],
                $this->tenantId,
                $reserva['fecha_reserva']
            ]);
            $bloqueos = $stmt->fetchAll();

            // Marcar franjas disponibles/ocupadas
            $franjas = [];
            foreach ($tarifas as $tarifa) {
                $disponible = true;
                $razon = '';
                foreach ($bloqueos as $bloqueo) {
                    $it = strtotime($tarifa['hora_inicio']);
                    $ft = strtotime($tarifa['hora_fin']);
                    $ib = strtotime($bloqueo['hora_inicio']);
                    $fb = strtotime($bloqueo['hora_fin']);
                    if ($it < $fb && $ft > $ib) {
                        $disponible = false;
                        $razon = $bloqueo['tipo'] === 'MANTENIMIENTO' ? 'Mantenimiento' : 'Otra reserva';
                        break;
                    }
                }
                // La franja actual de la reserva se marca como disponible
                if ($tarifa['hora_inicio'] === $reserva['hora_inicio'] && $tarifa['hora_fin'] === $reserva['hora_fin']) {
                    $disponible = true;
                    $razon = '';
                }
                $franjas[] = [
                    'tarifa_id' => $tarifa['tarifa_id'],
                    'hora_inicio' => $tarifa['hora_inicio'],
                    'hora_fin' => $tarifa['hora_fin'],
                    'precio' => $tarifa['precio'],
                    'disponible' => $disponible,
                    'razon' => $razon,
                    'seleccionada' => ($tarifa['hora_inicio'] === $reserva['hora_inicio'] && $tarifa['hora_fin'] === $reserva['hora_fin'])
                ];
            }

            $this->viewData['reserva'] = $reserva;
            $this->viewData['franjas'] = $franjas;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Editar Reserva #' . $reserva_id;
            $this->viewData['layout'] = 'main';

            $this->renderModule('reservas/editar', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error al editar reserva: " . $e->getMessage());
            $this->error('Error al procesar la edición');
        }
    }

    /**
     * Enviar confirmación por email (stub)
     */
    private function enviarConfirmacionReserva($reserva_id, $info_cancha) {
        // TODO: Implementar con PHPMailer
        $this->logError("Email stub: Confirmación reserva #{$reserva_id}");
    }
}
