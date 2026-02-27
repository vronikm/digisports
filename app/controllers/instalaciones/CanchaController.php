<?php
/**
 * DigiSports Arena - Controlador de Gestión de Canchas/Instalaciones
 * CRUD completo con validaciones de capacidad y tarifas
 * 
 * @package DigiSports\Controllers\Instalaciones
 * @version 2.0.0
 */

namespace App\Controllers\Instalaciones;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class CanchaController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Arena';
    protected $moduloIcono = 'fas fa-building';
    protected $moduloColor = '#3B82F6';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'ARENA';
    }
    
    /**
     * Listar todas las canchas del tenant
     */
    public function index() {
        try {
            // Obtener parámetros de búsqueda y filtros (POST o GET)
            $buscar = $this->post('buscar') ?? $this->get('buscar') ?? '';
            $tipo = $this->post('tipo') ?? $this->get('tipo') ?? '';
            $estado = $this->post('estado') ?? $this->get('estado') ?? '';
            $pagina = max(1, (int)($this->post('pagina') ?? $this->get('pagina') ?? 1));
            $perPage = 15;
            $offset = ($pagina - 1) * $perPage;
            
            // Query base
            $query = "
                SELECT 
                    c.*,
                    i.ins_nombre as instalacion_nombre,
                    0 as total_reservas_hoy
                FROM instalaciones_canchas c
                INNER JOIN instalaciones i ON c.can_instalacion_id = i.ins_instalacion_id
                WHERE c.can_tenant_id = ?
            ";
            
            $params = [$this->tenantId];
            
            // Filtros
            if (!empty($buscar)) {
                $query .= " AND (c.can_nombre LIKE ? OR i.ins_nombre LIKE ?)";
                $params[] = "%{$buscar}%";
                $params[] = "%{$buscar}%";
            }
            
            if (!empty($tipo)) {
                $query .= " AND c.can_tipo = ?";
                $params[] = $tipo;
            }
            
            if (!empty($estado)) {
                $query .= " AND c.can_estado = ?";
                $params[] = $estado;
            }
            
            $query .= " ORDER BY i.ins_nombre, c.can_nombre";
            
            // Total de registros
            $countQuery = "
                SELECT COUNT(DISTINCT c.can_cancha_id) as total
                FROM instalaciones_canchas c
                INNER JOIN instalaciones i ON c.can_instalacion_id = i.ins_instalacion_id
                WHERE c.can_tenant_id = ?
            ";
            $countParams = [$this->tenantId];
            
            if (!empty($buscar)) {
                $countQuery .= " AND (c.can_nombre LIKE ? OR i.ins_nombre LIKE ?)";
                $countParams[] = "%{$buscar}%";
                $countParams[] = "%{$buscar}%";
            }
            
            if (!empty($tipo)) {
                $countQuery .= " AND c.can_tipo = ?";
                $countParams[] = $tipo;
            }
            
            if (!empty($estado)) {
                $countQuery .= " AND c.can_estado = ?";
                $countParams[] = $estado;
            }
            
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute($countParams);
            $totalRegistros = $stmt->fetch()['total'];
            
            // Paginación - LIMIT y OFFSET se interpolan directamente (son valores internos seguros)
            $query .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $canchas = $stmt->fetchAll();
            
            // Datos para la vista
            $this->viewData['canchas'] = $canchas;
            $this->viewData['totalRegistros'] = $totalRegistros;
            $this->viewData['pagina'] = $pagina;
            $this->viewData['totalPaginas'] = ceil($totalRegistros / $perPage);
            $this->viewData['perPage'] = $perPage;
            $this->viewData['buscar'] = $buscar;
            $this->viewData['tipo'] = $tipo;
            $this->viewData['estado'] = $estado;
            $this->viewData['title'] = 'Gestión de Canchas';
            $this->viewData['layout'] = 'main';
            
            // Obtener tipos de canchas disponibles
            $stmt = $this->db->prepare("
                SELECT DISTINCT can_tipo FROM instalaciones_canchas 
                WHERE can_tenant_id = ? 
                ORDER BY can_tipo
            ");
            $stmt->execute([$this->tenantId]);
            $this->viewData['tipos'] = array_column($stmt->fetchAll(), 'tipo');
            
            $this->renderModule('instalaciones/canchas/index', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al listar canchas: " . $e->getMessage());
            $this->error('Error al cargar las canchas');
        }
    }

    /**
     * Ver detalle de cancha con tarifas, reservas recientes y mantenimientos
     */
    public function ver() {
        $canchaId = (int)$this->get('id');

        if ($canchaId < 1) {
            $this->error('Cancha no válida');
        }

        try {
            // Cancha + instalación
            $stmt = $this->db->prepare("
                SELECT c.can_cancha_id AS cancha_id,
                       c.can_tenant_id AS tenant_id,
                       c.can_instalacion_id AS instalacion_id,
                       c.can_nombre AS nombre,
                       c.can_tipo AS tipo,
                       c.can_superficie AS superficie,
                       c.can_descripcion AS descripcion,
                       c.can_capacidad_maxima AS capacidad_maxima,
                       c.can_ancho AS ancho,
                       c.can_largo AS largo,
                       c.can_dimensiones AS dimensiones,
                       c.can_iluminacion AS iluminacion,
                       c.can_techada AS techada,
                       c.can_notas AS notas,
                       c.can_estado AS estado,
                       c.can_fecha_creacion AS fecha_creacion,
                       c.can_fecha_actualizacion AS fecha_actualizacion,
                       i.ins_nombre AS instalacion_nombre
                FROM instalaciones_canchas c
                INNER JOIN instalaciones i ON c.can_instalacion_id = i.ins_instalacion_id
                WHERE c.can_cancha_id = ? AND c.can_tenant_id = ?
            ");
            $stmt->execute([$canchaId, $this->tenantId]);
            $cancha = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$cancha) {
                $this->error('Cancha no encontrada');
            }

            // Tarifas de la instalación
            $stmt = $this->db->prepare("
                SELECT tar_tarifa_id AS tarifa_id,
                       tar_instalacion_id AS instalacion_id,
                       tar_nombre_tarifa AS nombre,
                       tar_tipo_cliente AS tipo_cliente,
                       tar_aplica_dia AS aplica_dia,
                       tar_hora_inicio AS hora_inicio,
                       tar_hora_fin AS hora_fin,
                       tar_precio_por_hora AS precio_por_hora,
                       tar_precio_minimo AS precio_minimo,
                       tar_descuento_porcentaje AS descuento_porcentaje,
                       tar_estado AS estado
                FROM instalaciones_instalacion_tarifas
                WHERE tar_instalacion_id = ? AND tar_estado = 'A'
                ORDER BY tar_aplica_dia, tar_hora_inicio
            ");
            $stmt->execute([$cancha['instalacion_id']]);
            $tarifas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Reservas recientes (últimas 10)
            $stmt = $this->db->prepare("
                SELECT r.res_reserva_id AS reserva_id,
                       r.res_fecha_reserva AS fecha_reserva,
                       r.res_hora_inicio AS hora_inicio,
                       r.res_hora_fin AS hora_fin,
                       r.res_estado AS estado,
                       r.res_estado_pago AS estado_pago,
                       r.res_precio_total AS precio_total,
                       CONCAT(c2.cli_nombres, ' ', c2.cli_apellidos) AS cliente_nombre
                FROM instalaciones_reservas r
                LEFT JOIN clientes c2 ON r.res_cliente_id = c2.cli_cliente_id
                WHERE r.res_instalacion_id = ? AND r.res_tenant_id = ?
                ORDER BY r.res_fecha_reserva DESC, r.res_hora_inicio DESC
                LIMIT 10
            ");
            $stmt->execute([$cancha['instalacion_id'], $this->tenantId]);
            $reservas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Mantenimientos activos
            $stmt = $this->db->prepare("
                SELECT m.man_mantenimiento_id AS mantenimiento_id,
                       m.man_tipo AS tipo,
                       m.man_descripcion AS descripcion,
                       m.man_fecha_inicio AS fecha_inicio,
                       m.man_fecha_fin AS fecha_fin,
                       m.man_estado AS estado
                FROM instalaciones_mantenimientos m
                WHERE m.man_cancha_id = ? AND m.man_tenant_id = ?
                  AND m.man_estado IN ('PROGRAMADO', 'EN_PROGRESO')
                ORDER BY m.man_fecha_inicio ASC
                LIMIT 5
            ");
            $stmt->execute([$canchaId, $this->tenantId]);
            $mantenimientos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // KPIs rápidos
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*) as total_reservas,
                    SUM(CASE WHEN r.res_estado = 'CONFIRMADA' THEN 1 ELSE 0 END) as confirmadas,
                    SUM(CASE WHEN DATE(r.res_fecha_reserva) = CURDATE() THEN 1 ELSE 0 END) as hoy,
                    COALESCE(SUM(r.res_precio_total), 0) as ingresos_total
                FROM instalaciones_reservas r
                WHERE r.res_instalacion_id = ? AND r.res_tenant_id = ?
                  AND r.res_estado != 'CANCELADA'
            ");
            $stmt->execute([$cancha['instalacion_id'], $this->tenantId]);
            $kpis = $stmt->fetch(\PDO::FETCH_ASSOC);

            $this->viewData['cancha'] = $cancha;
            $this->viewData['tarifas'] = $tarifas;
            $this->viewData['reservas'] = $reservas;
            $this->viewData['mantenimientos'] = $mantenimientos;
            $this->viewData['kpis'] = $kpis ?? ['total_reservas'=>0,'confirmadas'=>0,'hoy'=>0,'ingresos_total'=>0];
            $this->viewData['title'] = 'Detalle de Cancha - ' . ($cancha['nombre'] ?? 'Sin nombre');
            $this->viewData['layout'] = 'main';

            $this->renderModule('instalaciones/canchas/ver', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error al ver cancha: " . $e->getMessage());
            $this->error('Error al cargar el detalle de la cancha');
        }
    }
    
    /**
     * Mostrar formulario de creación
     */
    public function crear() {
        try {
            // Obtener instalaciones disponibles
            $stmt = $this->db->prepare("
                SELECT ins_instalacion_id AS instalacion_id, 
                       ins_nombre AS nombre 
                FROM instalaciones 
                WHERE ins_tenant_id = ? AND ins_estado = 'ACTIVO'
                ORDER BY ins_nombre
            ");
            $stmt->execute([$this->tenantId]);
            $this->viewData['instalaciones'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Nueva Cancha';
            $this->viewData['layout'] = 'main';
            
            $this->renderModule('instalaciones/canchas/formulario', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al mostrar formulario crear: " . $e->getMessage());
            $this->error('Error al cargar el formulario');
        }
    }
    
    /**
     * Guardar nueva cancha
     */
    public function guardar() {
        if (!$this->isPost()) {
            $this->error('Solicitud inválida');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        try {
            // Obtener datos
            $instalacionId = (int)$this->post('instalacion_id');
            $nombre = trim($this->post('nombre'));
            $tipo = trim($this->post('tipo'));
            $descripcion = trim($this->post('descripcion') ?? '');
            $capacidadMaxima = (int)$this->post('capacidad_maxima');
            $ancho = (float)($this->post('ancho') ?? 0);
            $largo = (float)($this->post('largo') ?? 0);
            
            // Validaciones
            $errors = [];
            
            if (empty($nombre) || strlen($nombre) < 3) {
                $errors[] = 'El nombre debe tener al menos 3 caracteres';
            }
            
            if (empty($tipo)) {
                $errors[] = 'Selecciona un tipo de cancha';
            }
            
            if ($capacidadMaxima < 1) {
                $errors[] = 'La capacidad debe ser mayor a 0';
            }
            
            if ($instalacionId < 1) {
                $errors[] = 'Selecciona una instalación válida';
            }
            
            if (!empty($errors)) {
                $this->error(implode('. ', $errors));
            }
            
            // Verificar que la instalación pertenece al tenant
            $stmt = $this->db->prepare("
                SELECT instalacion_id 
                FROM instalaciones 
                WHERE instalacion_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$instalacionId, $this->tenantId]);
            
            if (!$stmt->fetch()) {
                $this->error('Instalación no válida');
            }
            
            // Insertar cancha
            $stmt = $this->db->prepare("
                INSERT INTO instalaciones_canchas (
                    can_tenant_id, can_instalacion_id, can_nombre, can_tipo, 
                    can_descripcion, can_capacidad_maxima, 
                    can_ancho, can_largo, can_estado, can_fecha_creacion
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVO', NOW())
            ");
            
            $stmt->execute([
                $this->tenantId,
                $instalacionId,
                $nombre,
                $tipo,
                $descripcion,
                $capacidadMaxima,
                $ancho,
                $largo
            ]);
            
            $canchaId = $this->db->lastInsertId();
            
            // Auditoría
            $this->audit('instalaciones_canchas', $canchaId, 'INSERT', [], [
                'nombre' => $nombre,
                'tipo' => $tipo,
                'capacidad' => $capacidadMaxima
            ]);
            
            \Security::logSecurityEvent('CANCHA_CREATED', "Cancha: {$nombre}");
            
            $this->success([
                'redirect' => url('instalaciones', 'cancha', 'index')
            ], 'Cancha creada exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al guardar cancha: " . $e->getMessage());
            $this->error('Error al guardar la cancha');
        }
    }
    
    /**
     * Mostrar formulario de edición
     */
    public function editar() {
        $canchaId = (int)$this->get('id');
        
        if ($canchaId < 1) {
            $this->error('Cancha no válida');
        }
        
        try {
            // Obtener cancha
            $stmt = $this->db->prepare("
                SELECT c.*, i.ins_nombre as instalacion_nombre
                FROM instalaciones_canchas c
                INNER JOIN instalaciones i ON c.can_instalacion_id = i.ins_instalacion_id
                WHERE c.can_cancha_id = ? AND c.can_tenant_id = ?
            ");
            $stmt->execute([$canchaId, $this->tenantId]);
            $cancha = $stmt->fetch();
            
            if (!$cancha) {
                $this->error('Cancha no encontrada');
            }
            
            // Obtener instalaciones
            $stmt = $this->db->prepare("
                SELECT ins_instalacion_id AS instalacion_id, 
                       ins_nombre AS nombre 
                FROM instalaciones 
                WHERE ins_tenant_id = ? AND ins_estado = 'ACTIVO'
                ORDER BY ins_nombre
            ");
            $stmt->execute([$this->tenantId]);
            
            $this->viewData['cancha'] = $cancha;
            $this->viewData['instalaciones'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Editar Cancha';
            $this->viewData['layout'] = 'main';
            $this->viewData['modo'] = 'editar';
            
            $this->renderModule('instalaciones/canchas/formulario', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al mostrar formulario editar: " . $e->getMessage());
            $this->error('Error al cargar el formulario');
        }
    }
    
    /**
     * Actualizar cancha
     */
    public function actualizar() {
        if (!$this->isPost()) {
            $this->error('Solicitud inválida');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        $canchaId = (int)$this->post('cancha_id');
        
        try {
            // Obtener cancha actual
            $stmt = $this->db->prepare("
                SELECT can_cancha_id AS cancha_id,
                       can_nombre AS nombre,
                       can_tipo AS tipo,
                       can_descripcion AS descripcion,
                       can_capacidad_maxima AS capacidad_maxima,
                       can_ancho AS ancho,
                       can_largo AS largo,
                       can_estado AS estado
                FROM instalaciones_canchas 
                WHERE can_cancha_id = ? AND can_tenant_id = ?
            ");
            $stmt->execute([$canchaId, $this->tenantId]);
            $canchaBefore = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$canchaBefore) {
                $this->error('Cancha no encontrada');
            }
            
            // Obtener datos
            $instalacionId = (int)$this->post('instalacion_id');
            $nombre = trim($this->post('nombre'));
            $tipo = trim($this->post('tipo'));
            $descripcion = trim($this->post('descripcion') ?? '');
            $capacidadMaxima = (int)$this->post('capacidad_maxima');
            $ancho = (float)($this->post('ancho') ?? 0);
            $largo = (float)($this->post('largo') ?? 0);
            $estado = $this->post('estado');
            
            // Validaciones
            $errors = [];
            
            if (empty($nombre) || strlen($nombre) < 3) {
                $errors[] = 'El nombre debe tener al menos 3 caracteres';
            }
            
            if (empty($tipo)) {
                $errors[] = 'Selecciona un tipo de cancha';
            }
            
            if ($capacidadMaxima < 1) {
                $errors[] = 'La capacidad debe ser mayor a 0';
            }
            
            if (!in_array($estado, ['ACTIVO', 'INACTIVO'])) {
                $errors[] = 'Estado inválido';
            }
            
            if (!empty($errors)) {
                $this->error(implode('. ', $errors));
            }
            
            // Actualizar
            $stmt = $this->db->prepare("
                UPDATE instalaciones_canchas SET
                    can_nombre = ?,
                    can_tipo = ?,
                    can_descripcion = ?,
                    can_capacidad_maxima = ?,
                    can_ancho = ?,
                    can_largo = ?,
                    can_estado = ?,
                    can_fecha_actualizacion = NOW()
                WHERE can_cancha_id = ? AND can_tenant_id = ?
            ");
            
            $stmt->execute([
                $nombre,
                $tipo,
                $descripcion,
                $capacidadMaxima,
                $ancho,
                $largo,
                $estado,
                $canchaId,
                $this->tenantId
            ]);
            
            // Auditoría
            $this->audit('instalaciones_canchas', $canchaId, 'UPDATE', $canchaBefore, [
                'nombre' => $nombre,
                'tipo' => $tipo,
                'capacidad' => $capacidadMaxima,
                'estado' => $estado
            ]);
            
            \Security::logSecurityEvent('CANCHA_UPDATED', "Cancha ID: {$canchaId}");
            
            $this->success([
                'redirect' => url('instalaciones', 'cancha', 'index')
            ], 'Cancha actualizada exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al actualizar cancha: " . $e->getMessage());
            $this->error('Error al actualizar la cancha');
        }
    }
    
    /**
     * Eliminar cancha
     */
    public function eliminar() {
        $canchaId = (int)$this->get('id');
        
        if ($canchaId < 1) {
            $this->error('Cancha no válida');
        }
        
        try {
            // Obtener cancha
            $stmt = $this->db->prepare("
                SELECT can_cancha_id AS cancha_id,
                       can_tenant_id AS tenant_id,
                       can_instalacion_id AS instalacion_id,
                       can_nombre AS nombre,
                       can_estado AS estado
                FROM instalaciones_canchas 
                WHERE can_cancha_id = ? AND can_tenant_id = ?
            ");
            $stmt->execute([$canchaId, $this->tenantId]);
            $cancha = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$cancha) {
                $this->error('Cancha no encontrada');
            }
            
            // Verificar si tiene reservas activas
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total FROM instalaciones_reservas 
                WHERE res_instalacion_id = ? AND res_estado != 'CANCELADA'
            ");
            $stmt->execute([$cancha['instalacion_id']]);  
            
            if ($stmt->fetch()['total'] > 0) {
                $this->error('No se puede eliminar una cancha con reservas activas');
            }
            
            // Soft delete (marcar como inactiva)
            $stmt = $this->db->prepare("
                UPDATE instalaciones_canchas 
                SET can_estado = 'ELIMINADA',
                    can_fecha_actualizacion = NOW()
                WHERE can_cancha_id = ?
            ");
            
            $stmt->execute([$canchaId]);
            
            // Auditoría
            $this->audit('instalaciones_canchas', $canchaId, 'DELETE', $cancha, []);
            
            \Security::logSecurityEvent('CANCHA_DELETED', "Cancha: {$cancha['can_nombre']}");
            
            $this->success([
                'redirect' => url('instalaciones', 'cancha', 'index')
            ], 'Cancha eliminada exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al eliminar cancha: " . $e->getMessage());
            $this->error('Error al eliminar la cancha');
        }
    }
    
    /**
     * Obtener tarifas de una cancha
     */
    public function tarifas() {
        $canchaId = (int)$this->get('id');
        
        if ($canchaId < 1) {
            $this->error('Cancha no válida');
        }
        
        try {
            // Obtener cancha
            $stmt = $this->db->prepare("
                SELECT c.can_cancha_id AS cancha_id,
                       c.can_tenant_id AS tenant_id,
                       c.can_instalacion_id AS instalacion_id,
                       c.can_nombre AS nombre,
                       c.can_tipo AS tipo,
                       c.can_descripcion AS descripcion,
                       c.can_capacidad_maxima AS capacidad_maxima,
                       c.can_estado AS estado,
                       i.ins_nombre as instalacion_nombre
                FROM instalaciones_canchas c
                INNER JOIN instalaciones i ON c.can_instalacion_id = i.ins_instalacion_id
                WHERE c.can_cancha_id = ? AND c.can_tenant_id = ?
            ");
            $stmt->execute([$canchaId, $this->tenantId]);
            $cancha = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$cancha) {
                $this->error('Cancha no encontrada');
            }
            
            // Obtener tarifas
            $stmt = $this->db->prepare("
                SELECT tar_tarifa_id AS tarifa_id,
                       tar_instalacion_id AS instalacion_id,
                       tar_nombre_tarifa AS nombre,
                       tar_tipo_cliente AS tipo_cliente,
                       tar_aplica_dia AS aplica_dia,
                       tar_hora_inicio AS hora_inicio,
                       tar_hora_fin AS hora_fin,
                       tar_precio_por_hora AS precio_por_hora,
                       tar_descuento_porcentaje AS descuento_porcentaje,
                       tar_estado AS estado
                FROM instalaciones_instalacion_tarifas
                WHERE tar_instalacion_id = ?
                ORDER BY tar_aplica_dia, tar_hora_inicio
            ");
            $stmt->execute([$cancha['instalacion_id']]);
            $tarifas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->viewData['cancha'] = $cancha;
            $this->viewData['tarifas'] = $tarifas;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Gestión de Tarifas - ' . $cancha['nombre'];
            $this->viewData['layout'] = 'main';
            
            $this->renderModule('instalaciones/canchas/tarifas', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener tarifas: " . $e->getMessage());
            $this->error('Error al cargar las tarifas');
        }
    }
    
    /**
     * Guardar tarifa
     */
    public function guardarTarifa() {
        if (!$this->isPost()) {
            $this->error('Solicitud inválida');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        try {
            $canchaId = (int)$this->post('cancha_id');
            $diaSemana = (int)$this->post('dia_semana');
            $horaInicio = $this->post('hora_inicio');
            $horaFin = $this->post('hora_fin');
            $precio = (float)$this->post('precio');
            $estado = $this->post('estado') ?? 'ACTIVO';
            
            // Validaciones
            if ($canchaId < 1) {
                $this->error('Cancha no válida');
            }
            
            if ($diaSemana < 0 || $diaSemana > 6) {
                $this->error('Día de semana inválido');
            }
            
            if ($precio <= 0) {
                $this->error('El precio debe ser mayor a 0');
            }
            
            // Verificar que la cancha pertenece al tenant
            $stmt = $this->db->prepare("
                SELECT can_cancha_id FROM instalaciones_canchas 
                WHERE can_cancha_id = ? AND can_tenant_id = ?
            ");
            $stmt->execute([$canchaId, $this->tenantId]);
            
            if (!$stmt->fetch()) {
                $this->error('Cancha no válida');
            }
            
            // Insertar o actualizar tarifa
            $tarifaId = (int)$this->post('tarifa_id') ?? 0;
            
            if ($tarifaId > 0) {
                // Actualizar
                $stmt = $this->db->prepare("
                    UPDATE tarifas SET
                        dia_semana = ?,
                        hora_inicio = ?,
                        hora_fin = ?,
                        precio = ?,
                        estado = ?
                    WHERE tarifa_id = ? AND can_cancha_id = ?
                ");
                
                $stmt->execute([
                    $diaSemana,
                    $horaInicio,
                    $horaFin,
                    $precio,
                    $estado,
                    $tarifaId,
                    $canchaId
                ]);
            } else {
                // Crear
                $stmt = $this->db->prepare("
                    INSERT INTO tarifas (
                        can_cancha_id, dia_semana, hora_inicio, 
                        hora_fin, precio, estado, fecha_creacion
                    ) VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $canchaId,
                    $diaSemana,
                    $horaInicio,
                    $horaFin,
                    $precio,
                    $estado
                ]);
                
                $tarifaId = $this->db->lastInsertId();
            }
            
            // Auditoría
            $this->audit('tarifas', $tarifaId, $tarifaId > 0 ? 'UPDATE' : 'INSERT', [], [
                'cancha_id' => $canchaId,
                'precio' => $precio
            ]);
            
            $this->success(null, 'Tarifa guardada exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al guardar tarifa: " . $e->getMessage());
            $this->error('Error al guardar la tarifa');
        }
    }
    
    /**
     * Eliminar tarifa
     */
    public function eliminarTarifa() {
        $tarifaId = (int)$this->get('id');
        $canchaId = (int)$this->get('cancha_id');
        
        if ($tarifaId < 1) {
            $this->error('Tarifa no válida');
        }
        
        try {
            // Obtener tarifa
            $stmt = $this->db->prepare("
                SELECT tar_tarifa_id AS tarifa_id,
                       tar_instalacion_id AS instalacion_id,
                       tar_nombre_tarifa AS nombre,
                       tar_estado AS estado
                FROM instalaciones_instalacion_tarifas
                WHERE tar_tarifa_id = ?
            ");
            $stmt->execute([$tarifaId]);
            $tarifa = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$tarifa) {
                $this->error('Tarifa no encontrada');
            }
            
            // Eliminar
            $stmt = $this->db->prepare("DELETE FROM instalaciones_instalacion_tarifas WHERE tar_tarifa_id = ?");
            $stmt->execute([$tarifaId]);
            
            // Auditoría
            $this->audit('instalaciones_instalacion_tarifas', $tarifaId, 'DELETE', $tarifa, []);
            
            // Flash message y redirección
            setFlashMessage('success', 'Tarifa eliminada exitosamente');
            redirect('instalaciones', 'cancha', 'tarifas', ['id' => $canchaId]);
            
        } catch (\Exception $e) {
            $this->logError("Error al eliminar tarifa: " . $e->getMessage());
            $this->error('Error al eliminar la tarifa');
        }
    }
}
