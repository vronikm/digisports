<?php
/**
 * DigiSports Arena - Controlador de Mantenimiento de Canchas
 * Gestión de mantenimiento preventivo y correctivo
 * 
 * @package DigiSports\Controllers\Instalaciones
 * @version 2.0.0
 */

namespace App\Controllers\Instalaciones;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class MantenimientoController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Arena';
    protected $moduloIcono = 'fas fa-building';
    protected $moduloColor = '#3B82F6';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'ARENA';
    }
    
    /**
     * Listar mantenimientos programados
     */
    public function index() {
        try {
            $estado = $this->get('estado') ?? '';
            $canchaId = (int)($this->get('cancha_id') ?? 0);
            $pagina = (int)($this->get('pagina') ?? 1);
            $perPage = 15;
            $offset = ($pagina - 1) * $perPage;
            
            // Query base
            $query = "
                SELECT 
                    m.*,
                    c.nombre as cancha_nombre,
                    c.tipo as cancha_tipo,
                    i.ins_nombre as instalacion_nombre,
                    CONCAT(u.nombres, ' ', u.apellidos) as responsable_nombre
                FROM mantenimientos m
                INNER JOIN canchas c ON m.cancha_id = c.cancha_id
                INNER JOIN instalaciones i ON c.instalacion_id = i.ins_instalacion_id
                LEFT JOIN usuarios u ON m.responsable_id = u.usuario_id
                WHERE m.tenant_id = ?
            ";
            
            $params = [$this->tenantId];
            
            // Filtros
            if (!empty($estado)) {
                $query .= " AND m.estado = ?";
                $params[] = $estado;
            }
            
            if ($canchaId > 0) {
                $query .= " AND m.cancha_id = ?";
                $params[] = $canchaId;
            }
            
            $query .= " ORDER BY m.fecha_inicio DESC";
            
            // Total de registros
            $countQuery = "SELECT COUNT(*) as total FROM mantenimientos m
                          WHERE m.tenant_id = ?";
            $countParams = [$this->tenantId];
            
            if (!empty($estado)) {
                $countQuery .= " AND m.estado = ?";
                $countParams[] = $estado;
            }
            
            if ($canchaId > 0) {
                $countQuery .= " AND m.cancha_id = ?";
                $countParams[] = $canchaId;
            }
            
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute($countParams);
            $totalRegistros = $stmt->fetch()['total'];
            
            // Paginación
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $mantenimientos = $stmt->fetchAll();
            
            // Obtener canchas para filtro
            $stmt = $this->db->prepare("
                SELECT DISTINCT c.cancha_id, c.nombre
                FROM canchas c
                WHERE c.tenant_id = ? AND c.estado = 'ACTIVO'
                ORDER BY c.nombre
            ");
            $stmt->execute([$this->tenantId]);
            
            $this->viewData['mantenimientos'] = $mantenimientos;
            $this->viewData['totalRegistros'] = $totalRegistros;
            $this->viewData['pagina'] = $pagina;
            $this->viewData['totalPaginas'] = ceil($totalRegistros / $perPage);
            $this->viewData['estado'] = $estado;
            $this->viewData['cancha_id'] = $canchaId;
            $this->viewData['canchas'] = $stmt->fetchAll();
            $this->viewData['title'] = 'Gestión de Mantenimientos';
            $this->viewData['layout'] = 'main';
            
            $this->renderModule('instalaciones/mantenimientos/index', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al listar mantenimientos: " . $e->getMessage());
            $this->error('Error al cargar los mantenimientos');
        }
    }

    /**
     * Ver detalle de mantenimiento
     */
    public function ver() {
        $mantenimientoId = (int)$this->get('id');

        if ($mantenimientoId < 1) {
            $this->error('Mantenimiento no válido');
        }

        try {
            $stmt = $this->db->prepare("
                SELECT m.*,
                       c.nombre AS cancha_nombre,
                       c.tipo AS cancha_tipo,
                       c.capacidad_maxima,
                       i.ins_nombre AS instalacion_nombre,
                       CONCAT(u.nombres, ' ', u.apellidos) AS responsable_nombre,
                       u.email AS responsable_email
                FROM mantenimientos m
                INNER JOIN canchas c ON m.cancha_id = c.cancha_id
                INNER JOIN instalaciones i ON c.instalacion_id = i.ins_instalacion_id
                LEFT JOIN usuarios u ON m.responsable_id = u.usuario_id
                WHERE m.mantenimiento_id = ? AND m.tenant_id = ?
            ");
            $stmt->execute([$mantenimientoId, $this->tenantId]);
            $mantenimiento = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$mantenimiento) {
                $this->error('Mantenimiento no encontrado');
            }

            // Historial de mantenimientos de la misma cancha
            $stmt = $this->db->prepare("
                SELECT mantenimiento_id, tipo, fecha_inicio, fecha_fin, estado
                FROM mantenimientos
                WHERE cancha_id = ? AND tenant_id = ?
                  AND mantenimiento_id != ?
                ORDER BY fecha_inicio DESC
                LIMIT 5
            ");
            $stmt->execute([$mantenimiento['cancha_id'], $this->tenantId, $mantenimientoId]);
            $historial = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['mantenimiento'] = $mantenimiento;
            $this->viewData['historial'] = $historial;
            $this->viewData['title'] = 'Detalle de Mantenimiento';
            $this->viewData['layout'] = 'main';

            $this->renderModule('instalaciones/mantenimientos/ver', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error al ver mantenimiento: " . $e->getMessage());
            $this->error('Error al cargar el detalle del mantenimiento');
        }
    }
    
    /**
     * Mostrar formulario de crear mantenimiento
     */
    public function crear() {
        try {
            // Obtener canchas disponibles
            $stmt = $this->db->prepare("
                SELECT cancha_id, nombre, tipo
                FROM canchas 
                WHERE tenant_id = ? AND estado = 'ACTIVO'
                ORDER BY nombre
            ");
            $stmt->execute([$this->tenantId]);
            
            // Obtener usuarios con rol de técnico/admin
            $stmt2 = $this->db->prepare("
                SELECT u.usuario_id, CONCAT(u.nombres, ' ', u.apellidos) AS nombre, u.email
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.rol_id
                WHERE u.tenant_id = ? AND r.codigo IN ('ADMIN', 'SUPERADMIN', 'TECNICO')
                ORDER BY u.nombres
            ");
            $stmt2->execute([$this->tenantId]);
            
            $this->viewData['canchas'] = $stmt->fetchAll();
            $this->viewData['usuarios'] = $stmt2->fetchAll();
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Nuevo Mantenimiento';
            $this->viewData['layout'] = 'main';
            
            $this->renderModule('instalaciones/mantenimientos/formulario', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al mostrar formulario crear: " . $e->getMessage());
            $this->error('Error al cargar el formulario');
        }
    }
    
    /**
     * Guardar nuevo mantenimiento
     */
    public function guardar() {
        if (!$this->isPost()) {
            $this->error('Solicitud inválida');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        try {
            $canchaId = (int)$this->post('cancha_id');
            $tipo = trim($this->post('tipo'));
            $descripcion = trim($this->post('descripcion'));
            $fechaInicio = $this->post('fecha_inicio');
            $fechaFin = $this->post('fecha_fin');
            $responsableId = (int)($this->post('responsable_id') ?? 0);
            $recurrir = $this->post('recurrir') ?? 'NO';
            $cadenciaRecurrencia = trim($this->post('cadencia_recurrencia') ?? '');
            $notas = trim($this->post('notas') ?? '');
            
            // Validaciones
            $errors = [];
            
            if ($canchaId < 1) {
                $errors[] = 'Selecciona una cancha válida';
            }
            
            if (empty($tipo)) {
                $errors[] = 'Selecciona un tipo de mantenimiento';
            }
            
            if (empty($descripcion) || strlen($descripcion) < 5) {
                $errors[] = 'La descripción debe tener al menos 5 caracteres';
            }
            
            if (empty($fechaInicio)) {
                $errors[] = 'Selecciona una fecha de inicio';
            }
            
            if (empty($fechaFin)) {
                $errors[] = 'Selecciona una fecha de finalización';
            }
            
            if (strtotime($fechaFin) < strtotime($fechaInicio)) {
                $errors[] = 'La fecha de fin debe ser posterior a la de inicio';
            }
            
            if (!in_array($recurrir, ['SI', 'NO'])) {
                $errors[] = 'Valor de recurrencia inválido';
            }
            
            if (!empty($errors)) {
                $this->error(implode('. ', $errors));
            }
            
            // Verificar que la cancha pertenece al tenant
            $stmt = $this->db->prepare("
                SELECT cancha_id FROM canchas 
                WHERE cancha_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$canchaId, $this->tenantId]);
            
            if (!$stmt->fetch()) {
                $this->error('Cancha no válida');
            }
            
            // Insertar mantenimiento
            $stmt = $this->db->prepare("
                INSERT INTO mantenimientos (
                    tenant_id, cancha_id, tipo, descripcion,
                    fecha_inicio, fecha_fin, responsable_id,
                    recurrir, cadencia_recurrencia, notas,
                    estado, fecha_creacion
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PROGRAMADO', NOW())
            ");
            
            $stmt->execute([
                $this->tenantId,
                $canchaId,
                $tipo,
                $descripcion,
                $fechaInicio,
                $fechaFin,
                $responsableId > 0 ? $responsableId : null,
                $recurrir,
                $cadenciaRecurrencia,
                $notas
            ]);
            
            $mantenimientoId = $this->db->lastInsertId();
            
            // Auditoría
            $this->audit('mantenimientos', $mantenimientoId, 'INSERT', [], [
                'cancha_id' => $canchaId,
                'tipo' => $tipo,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]);
            
            \Security::logSecurityEvent('MANTENIMIENTO_CREATED', "Mantenimiento ID: {$mantenimientoId}");
            
            $this->success([
                'redirect' => url('instalaciones', 'mantenimiento', 'index')
            ], 'Mantenimiento programado exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al guardar mantenimiento: " . $e->getMessage());
            $this->error('Error al guardar el mantenimiento');
        }
    }
    
    /**
     * Mostrar detalle y editar mantenimiento
     */
    public function editar() {
        $mantenimientoId = (int)$this->get('id');
        
        if ($mantenimientoId < 1) {
            $this->error('Mantenimiento no válido');
        }
        
        try {
            // Obtener mantenimiento
            $stmt = $this->db->prepare("
                SELECT * FROM mantenimientos 
                WHERE mantenimiento_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$mantenimientoId, $this->tenantId]);
            $mantenimiento = $stmt->fetch();
            
            if (!$mantenimiento) {
                $this->error('Mantenimiento no encontrado');
            }
            
            // Obtener canchas
            $stmt = $this->db->prepare("
                SELECT cancha_id, nombre, tipo
                FROM canchas 
                WHERE tenant_id = ? AND estado = 'ACTIVO'
                ORDER BY nombre
            ");
            $stmt->execute([$this->tenantId]);
            
            // Obtener usuarios
            $stmt2 = $this->db->prepare("
                SELECT u.usuario_id, CONCAT(u.nombres, ' ', u.apellidos) AS nombre, u.email
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.rol_id
                WHERE u.tenant_id = ? AND r.codigo IN ('ADMIN', 'SUPERADMIN', 'TECNICO')
                ORDER BY u.nombres
            ");
            $stmt2->execute([$this->tenantId]);
            
            $this->viewData['mantenimiento'] = $mantenimiento;
            $this->viewData['canchas'] = $stmt->fetchAll();
            $this->viewData['usuarios'] = $stmt2->fetchAll();
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Editar Mantenimiento';
            $this->viewData['layout'] = 'main';
            $this->viewData['modo'] = 'editar';
            
            $this->renderModule('instalaciones/mantenimientos/formulario', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al mostrar formulario editar: " . $e->getMessage());
            $this->error('Error al cargar el formulario');
        }
    }
    
    /**
     * Actualizar mantenimiento
     */
    public function actualizar() {
        if (!$this->isPost()) {
            $this->error('Solicitud inválida');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        $mantenimientoId = (int)$this->post('mantenimiento_id');
        
        try {
            // Obtener mantenimiento actual
            $stmt = $this->db->prepare("
                SELECT * FROM mantenimientos 
                WHERE mantenimiento_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$mantenimientoId, $this->tenantId]);
            $mntBefore = $stmt->fetch();
            
            if (!$mntBefore) {
                $this->error('Mantenimiento no encontrado');
            }
            
            // Obtener datos
            $tipo = trim($this->post('tipo'));
            $descripcion = trim($this->post('descripcion'));
            $fechaInicio = $this->post('fecha_inicio');
            $fechaFin = $this->post('fecha_fin');
            $responsableId = (int)($this->post('responsable_id') ?? 0);
            $estado = trim($this->post('estado'));
            $notas = trim($this->post('notas') ?? '');
            
            // Validaciones
            $errors = [];
            
            if (empty($tipo)) {
                $errors[] = 'Selecciona un tipo de mantenimiento';
            }
            
            if (empty($descripcion) || strlen($descripcion) < 5) {
                $errors[] = 'La descripción debe tener al menos 5 caracteres';
            }
            
            if (!in_array($estado, ['PROGRAMADO', 'EN_PROGRESO', 'COMPLETADO', 'CANCELADO'])) {
                $errors[] = 'Estado inválido';
            }
            
            if (!empty($errors)) {
                $this->error(implode('. ', $errors));
            }
            
            // Actualizar
            $stmt = $this->db->prepare("
                UPDATE mantenimientos SET
                    tipo = ?,
                    descripcion = ?,
                    fecha_inicio = ?,
                    fecha_fin = ?,
                    responsable_id = ?,
                    estado = ?,
                    notas = ?,
                    fecha_actualizacion = NOW()
                WHERE mantenimiento_id = ? AND tenant_id = ?
            ");
            
            $stmt->execute([
                $tipo,
                $descripcion,
                $fechaInicio,
                $fechaFin,
                $responsableId > 0 ? $responsableId : null,
                $estado,
                $notas,
                $mantenimientoId,
                $this->tenantId
            ]);
            
            // Auditoría
            $this->audit('mantenimientos', $mantenimientoId, 'UPDATE', $mntBefore, [
                'tipo' => $tipo,
                'estado' => $estado
            ]);
            
            \Security::logSecurityEvent('MANTENIMIENTO_UPDATED', "Mantenimiento ID: {$mantenimientoId}");
            
            $this->success([
                'redirect' => url('instalaciones', 'mantenimiento', 'index')
            ], 'Mantenimiento actualizado exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al actualizar mantenimiento: " . $e->getMessage());
            $this->error('Error al actualizar el mantenimiento');
        }
    }
    
    /**
     * Eliminar mantenimiento
     */
    public function eliminar() {
        $mantenimientoId = (int)$this->get('id');
        
        if ($mantenimientoId < 1) {
            $this->error('Mantenimiento no válido');
        }
        
        try {
            // Obtener mantenimiento
            $stmt = $this->db->prepare("
                SELECT * FROM mantenimientos 
                WHERE mantenimiento_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$mantenimientoId, $this->tenantId]);
            $mantenimiento = $stmt->fetch();
            
            if (!$mantenimiento) {
                $this->error('Mantenimiento no encontrado');
            }
            
            // Eliminar
            $stmt = $this->db->prepare("
                DELETE FROM mantenimientos 
                WHERE mantenimiento_id = ?
            ");
            $stmt->execute([$mantenimientoId]);
            
            // Auditoría
            $this->audit('mantenimientos', $mantenimientoId, 'DELETE', $mantenimiento, []);
            
            \Security::logSecurityEvent('MANTENIMIENTO_DELETED', "Mantenimiento ID: {$mantenimientoId}");
            
            $this->success([
                'redirect' => url('instalaciones', 'mantenimiento', 'index')
            ], 'Mantenimiento eliminado exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al eliminar mantenimiento: " . $e->getMessage());
            $this->error('Error al eliminar el mantenimiento');
        }
    }
    
    /**
     * Cambiar estado del mantenimiento
     */
    public function cambiarEstado() {
        $mantenimientoId = (int)$this->get('id');
        $nuevoEstado = trim($this->get('estado'));
        
        if ($mantenimientoId < 1) {
            $this->error('Mantenimiento no válido');
        }
        
        try {
            // Obtener mantenimiento
            $stmt = $this->db->prepare("
                SELECT * FROM mantenimientos 
                WHERE mantenimiento_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$mantenimientoId, $this->tenantId]);
            $mnt = $stmt->fetch();
            
            if (!$mnt) {
                $this->error('Mantenimiento no encontrado');
            }
            
            // Validar estado
            if (!in_array($nuevoEstado, ['PROGRAMADO', 'EN_PROGRESO', 'COMPLETADO', 'CANCELADO'])) {
                $this->error('Estado inválido');
            }
            
            // Actualizar
            $stmt = $this->db->prepare("
                UPDATE mantenimientos 
                SET estado = ?, fecha_actualizacion = NOW()
                WHERE mantenimiento_id = ?
            ");
            $stmt->execute([$nuevoEstado, $mantenimientoId]);
            
            // Auditoría
            $this->audit('mantenimientos', $mantenimientoId, 'STATUS_CHANGE', 
                        ['estado' => $mnt['estado']], 
                        ['estado' => $nuevoEstado]);
            
            \Security::logSecurityEvent('MANTENIMIENTO_STATUS_CHANGED', 
                                      "Mantenimiento ID: {$mantenimientoId} -> {$nuevoEstado}");
            
            $this->success(null, "Estado cambiado a {$nuevoEstado}");
            
        } catch (\Exception $e) {
            $this->logError("Error al cambiar estado: " . $e->getMessage());
            $this->error('Error al cambiar el estado');
        }
    }
}
