<?php
/**
 * DigiSports - Controlador de Reservas
 * Sistema completo de reserva de canchas con disponibilidad dinámica
 * 
 * @package DigiSports\Controllers\Reservas
 * @version 1.0.0
 */

namespace App\Controllers\Reservas;

require_once BASE_PATH . '/app/controllers/BaseController.php';

class ReservaController extends \BaseController {
    
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
                SELECT DISTINCT i.instalacion_id, i.nombre
                FROM instalaciones i
                WHERE i.tenant_id = ? AND i.estado = 'ACTIVO'
                ORDER BY i.nombre
            ");
            $stmt->execute([$this->tenantId]);
            $instalaciones = $stmt->fetchAll();
            
            // Obtener tipos de cancha
            $stmt = $this->db->prepare("
                SELECT DISTINCT tipo FROM canchas 
                WHERE tenant_id = ? AND estado = 'ACTIVO'
                ORDER BY tipo
            ");
            $stmt->execute([$this->tenantId]);
            $tipos = array_column($stmt->fetchAll(), 'tipo');
            
            // Si se especifica fecha e instalación, buscar disponibilidad
            $disponibilidades = [];
            
            if (!empty($fecha) && $instalacion_id > 0) {
                // Obtener canchas de la instalación (filtrar por tipo si se especifica)
                $sql = "SELECT cancha_id, nombre, tipo, capacidad_maxima, ancho, largo
                        FROM canchas
                        WHERE instalacion_id = ? AND tenant_id = ? AND estado = 'ACTIVO'";
                $params = [$instalacion_id, $this->tenantId];
                
                if (!empty($tipo_cancha)) {
                    $sql .= " AND tipo = ?";
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
                                   r.hora_inicio,
                                   r.hora_fin
                            FROM reservas r
                            INNER JOIN canchas c ON c.instalacion_id = r.instalacion_id
                            WHERE c.cancha_id = ?
                            AND r.fecha_reserva = ?
                            AND r.estado IN ('CONFIRMADA', 'PENDIENTE')
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
            
            $this->render('reservas/buscar', $this->viewData);
            
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
                SELECT c.*, c.instalacion_id, t.precio, t.hora_inicio, t.hora_fin, t.dia_semana
                FROM canchas c
                INNER JOIN tarifas t ON c.cancha_id = t.cancha_id
                WHERE c.cancha_id = ? AND t.tarifa_id = ? 
                AND c.tenant_id = ? AND c.estado = 'ACTIVO'
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
                SELECT COUNT(*) as total FROM reservas
                WHERE instalacion_id = ? 
                AND fecha_reserva = ?
                AND hora_inicio = ?
                AND estado IN ('CONFIRMADA', 'PENDIENTE')
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
                SELECT cliente_id FROM clientes 
                WHERE tenant_id = ? AND email = ?
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
                    INSERT INTO clientes (tenant_id, tipo_identificacion, identificacion, nombres, apellidos, email, telefono, estado)
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
                INSERT INTO reservas (
                    tenant_id, instalacion_id, cliente_id,
                    fecha_reserva, hora_inicio, hora_fin,
                    duracion_minutos, tarifa_aplicada_id,
                    precio_base, precio_total,
                    estado, observaciones, usuario_registro
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
                SELECT r.*, c.nombre as cancha_nombre, c.tipo as cancha_tipo,
                       i.nombre as instalacion_nombre
                FROM reservas r
                INNER JOIN canchas c ON r.cancha_id = c.cancha_id
                INNER JOIN instalaciones i ON c.instalacion_id = i.instalacion_id
                WHERE r.reserva_id = ? AND r.tenant_id = ?
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $reserva = $stmt->fetch();
            
            if (!$reserva) {
                $this->error('Reserva no encontrada');
            }
            
            // Obtener detalles de líneas
            $stmt = $this->db->prepare("
                SELECT rl.*, t.hora_inicio, t.hora_fin
                FROM reservas_lineas rl
                INNER JOIN tarifas t ON rl.tarifa_id = t.tarifa_id
                WHERE rl.reserva_id = ?
            ");
            $stmt->execute([$reserva_id]);
            $lineas = $stmt->fetchAll();
            
            $this->viewData['reserva'] = $reserva;
            $this->viewData['lineas'] = $lineas;
            $this->viewData['title'] = 'Confirmación de Reserva';
            $this->viewData['layout'] = 'main';
            
            $this->render('reservas/confirmacion', $this->viewData);
            
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
            // Aceptar filtro desde POST o GET
            $estado = $this->post('estado') ?? $this->get('estado') ?? '';
            $pagina = max(1, (int)($this->post('pagina') ?? $this->get('pagina') ?? 1));
            $perPage = 15;
            $offset = ($pagina - 1) * $perPage;
            
            $query = "
                SELECT r.*, 
                       i.nombre as instalacion_nombre,
                       c.nombres as cliente_nombre,
                       c.apellidos as cliente_apellidos
                FROM reservas r
                INNER JOIN instalaciones i ON r.instalacion_id = i.instalacion_id
                LEFT JOIN clientes c ON r.cliente_id = c.cliente_id
                WHERE r.tenant_id = ?
            ";
            
            $params = [$this->tenantId];
            
            if (!empty($estado)) {
                $query .= " AND r.estado = ?";
                $params[] = $estado;
            }
            
            $query .= " ORDER BY r.fecha_reserva DESC, r.hora_inicio DESC";
            
            // Total
            $countQuery = "SELECT COUNT(*) as total FROM reservas WHERE tenant_id = ?";
            $countParams = [$this->tenantId];
            
            if (!empty($estado)) {
                $countQuery .= " AND estado = ?";
                $countParams[] = $estado;
            }
            
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute($countParams);
            $totalRegistros = $stmt->fetch()['total'];
            
            // Paginación - interpolar valores seguros
            $query .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $reservas = $stmt->fetchAll();
            
            $this->viewData['reservas'] = $reservas;
            $this->viewData['totalRegistros'] = $totalRegistros;
            $this->viewData['pagina'] = $pagina;
            $this->viewData['totalPaginas'] = ceil($totalRegistros / $perPage);
            $this->viewData['estado'] = $estado;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Gestión de Reservas';
            $this->viewData['layout'] = 'main';
            
            $this->render('reservas/index', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al listar reservas: " . $e->getMessage());
            $this->error('Error al cargar las reservas');
        }
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
                SELECT r.*, 
                       i.nombre as instalacion_nombre,
                       CONCAT(c.nombres, ' ', c.apellidos) as cliente_nombre,
                       c.email as cliente_email,
                       c.telefono as cliente_telefono
                FROM reservas r
                INNER JOIN instalaciones i ON r.instalacion_id = i.instalacion_id
                INNER JOIN clientes c ON r.cliente_id = c.cliente_id
                WHERE r.reserva_id = ? AND r.tenant_id = ?
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $reserva = $stmt->fetch();
            
            if (!$reserva) {
                $this->error('Reserva no encontrada');
            }
            
            $this->viewData['reserva'] = $reserva;
            $this->viewData['title'] = 'Detalles de Reserva #' . $reserva_id;
            $this->viewData['layout'] = 'main';
            
            $this->render('reservas/ver', $this->viewData);
            
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
                SELECT * FROM reservas
                WHERE reserva_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $reserva = $stmt->fetch();
            
            if (!$reserva) {
                $this->error('Reserva no encontrada');
            }
            
            // Actualizar estado
            $stmt = $this->db->prepare("
                UPDATE reservas
                SET estado = 'CONFIRMADA',
                    fecha_confirmacion = NOW(),
                    fecha_actualizacion = NOW()
                WHERE reserva_id = ?
            ");
            $stmt->execute([$reserva_id]);
            
            // Auditoría
            $this->audit('reservas', $reserva_id, 'STATUS_CHANGE',
                        ['estado' => $reserva['estado']],
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
                SELECT * FROM reservas
                WHERE reserva_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$reserva_id, $this->tenantId]);
            $reserva = $stmt->fetch();
            
            if (!$reserva) {
                $this->error('Reserva no encontrada');
            }
            
            // Actualizar estado
            $stmt = $this->db->prepare("
                UPDATE reservas
                SET estado = 'CANCELADA',
                    motivo_cancelacion = ?,
                    fecha_cancelacion = NOW(),
                    fecha_actualizacion = NOW()
                WHERE reserva_id = ?
            ");
            $stmt->execute([$motivo, $reserva_id]);
            
            // Auditoría
            $this->audit('reservas', $reserva_id, 'CANCELLED',
                        ['estado' => $reserva['estado']],
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
                SELECT TIME(fecha_reserva) as hora_inicio,
                       TIME(fecha_fin_reserva) as hora_fin
                FROM reservas
                WHERE cancha_id = ? AND DATE(fecha_reserva) = ?
                AND estado = 'CONFIRMADA'
            ");
            $stmt->execute([$cancha_id, $fecha]);
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
     * Enviar confirmación por email (stub)
     */
    private function enviarConfirmacionReserva($reserva_id, $info_cancha) {
        // TODO: Implementar con PHPMailer
        $this->logError("Email stub: Confirmación reserva #{$reserva_id}");
    }
}
