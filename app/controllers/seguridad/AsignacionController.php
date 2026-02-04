<?php
/**
 * DigiSports - Módulo Seguridad
 * Asignacion Controller
 * 
 * Gestión de asignación de módulos a tenants
 * 
 * @package DigiSports\Security
 * @version 1.0.0
 */

namespace App\Controllers\Seguridad;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class AsignacionController extends \App\Controllers\ModuleController {
    // Métodos y propiedades válidos aquí
    protected $moduloCodigo = 'seguridad';
    protected $moduloNombre = 'Seguridad';
    protected $moduloIcono = 'fas fa-shield-alt';
    protected $moduloColor = '#F59E0B';
    /**
     * Menú lateral del módulo Seguridad (idéntico al dashboard)
     */
    protected function getMenuItems() {
        require_once BASE_PATH . '/app/controllers/seguridad/DashboardController.php';
        $dashboard = new \App\Controllers\Seguridad\DashboardController();
        return $dashboard->getMenuItems();
    }

    /**
     * Gestión de módulos por tenant
     */
    public function modulos() {
        $tenantId = $_GET['tenant_id'] ?? null;
        try {
            // Todos los tenants
            $tenants = $this->db->query("
                SELECT t.tenant_id, t.nombre_comercial, t.ruc, p.nombre as plan_nombre
                FROM tenants t
                LEFT JOIN planes_suscripcion p ON t.plan_id = p.plan_id
                WHERE t.estado = 'A'
                ORDER BY t.nombre_comercial
            ")->fetchAll(\PDO::FETCH_ASSOC);

            // Todos los módulos
            $modulos = $this->db->query("
                SELECT * FROM modulos_sistema WHERE estado = 'A' ORDER BY orden_visualizacion
            ")->fetchAll(\PDO::FETCH_ASSOC);

            // Si hay tenant seleccionado, obtener sus módulos
            $modulosAsignados = [];
            if ($tenantId) {
                $stmt = $this->db->prepare("
                    SELECT tm.*, m.nombre, m.icono, m.color
                    FROM tenant_modulos tm
                    JOIN modulos_sistema m ON tm.modulo_id = m.modulo_id
                    WHERE tm.tenant_id = ?
                ");
                $stmt->execute([$tenantId]);
                $modulosAsignados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        } catch (\Exception $e) {
            $tenants = [];
            $modulos = [];
            $modulosAsignados = [];
        }

        $this->renderModule('asignacion/modulos', [
            'tenants' => $tenants,
            'modulos' => $modulos,
            'modulosAsignados' => $modulosAsignados,
            'tenantSeleccionado' => $tenantId,
            'pageTitle' => 'Asignación de Módulos'
        ]);
    }

    /**
     * Guardar asignación de módulos
     */
    public function guardarModulos() {
        $tenantId = $_POST['tenant_id'] ?? 0;
        $modulosSeleccionados = $_POST['modulos'] ?? [];
        try {
            $this->db->beginTransaction();

            // Desactivar todos los módulos actuales
            $stmt = $this->db->prepare("UPDATE tenant_modulos SET activo = 'N', fecha_desactivacion = CURDATE() WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);

            // Activar los seleccionados
            foreach ($modulosSeleccionados as $moduloId) {
                $iconoPersonalizado = $_POST['icono_' . $moduloId] ?? null;
                $nombrePersonalizado = $_POST['nombre_' . $moduloId] ?? null;
                $colorPersonalizado = $_POST['color_' . $moduloId] ?? null;
                // Verificar si existe
                $stmt = $this->db->prepare("SELECT tenant_modulo_id FROM tenant_modulos WHERE tenant_id = ? AND modulo_id = ?");
                $stmt->execute([$tenantId, $moduloId]);
                
                if ($row = $stmt->fetch()) {
                    $stmt = $this->db->prepare("
                        UPDATE tenant_modulos 
                        SET activo = 'S', fecha_activacion = CURDATE(), fecha_desactivacion = NULL,
                            nombre_personalizado = ?, icono_personalizado = ?, color_personalizado = ?
                        WHERE tenant_modulo_id = ?
                    ");
                    $stmt->execute([$nombrePersonalizado, $iconoPersonalizado, $colorPersonalizado, $row['tenant_modulo_id']]);
                } else {
                    $stmt = $this->db->prepare("
                        INSERT INTO tenant_modulos (tenant_id, modulo_id, activo, fecha_activacion, nombre_personalizado, icono_personalizado, color_personalizado)
                        VALUES (?, ?, 'S', CURDATE(), ?, ?, ?)
                    ");
                    $stmt->execute([$tenantId, $moduloId, $nombrePersonalizado, $iconoPersonalizado, $colorPersonalizado]);
                }
            }
            
            $this->db->commit();
            setFlashMessage('success', 'Módulos asignados correctamente');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            setFlashMessage('error', 'Error al asignar módulos: ' . $e->getMessage());
        }
        redirect('seguridad', 'asignacion', 'modulos', ['tenant_id' => $tenantId]);
    }
    
    /**
     * Asignación masiva
     */
    public function masiva() {
        try {
            $tenants = $this->db->query("
                SELECT t.*, p.nombre as plan_nombre
                FROM tenants t
                LEFT JOIN planes_suscripcion p ON t.plan_id = p.plan_id
                WHERE t.estado = 'A'
                ORDER BY t.nombre_comercial
            ")->fetchAll(\PDO::FETCH_ASSOC);

            $modulos = $this->db->query("
                SELECT * FROM modulos_sistema WHERE estado = 'A' ORDER BY orden_visualizacion
            ")->fetchAll(\PDO::FETCH_ASSOC);

            // Matriz de asignación
            $matriz = [];
            foreach ($tenants as $tenant) {
                $stmt = $this->db->prepare("SELECT modulo_id FROM tenant_modulos WHERE tenant_id = ? AND activo = 'S'");
                $stmt->execute([$tenant['tenant_id']]);
                $matriz[$tenant['tenant_id']] = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            }
        } catch (\Exception $e) {
            $tenants = [];
            $modulos = [];
            $matriz = [];
        }

        $this->renderModule('asignacion/masiva', [
            'tenants' => $tenants,
            'modulos' => $modulos,
            'matriz' => $matriz,
            'pageTitle' => 'Asignación Masiva'
        ]);
    }

    /**
     * Guardar asignación masiva
     */
    public function guardarMasiva() {
        $asignaciones = $_POST['asignacion'] ?? [];
        try {
            $this->db->beginTransaction();

            foreach ($asignaciones as $tenantId => $modulos) {
                // Desactivar todos
                $stmt = $this->db->prepare("UPDATE tenant_modulos SET activo = 'N' WHERE tenant_id = ?");
                $stmt->execute([$tenantId]);

                // Activar seleccionados
                foreach ($modulos as $moduloId) {
                    $stmt = $this->db->prepare("SELECT tenant_modulo_id FROM tenant_modulos WHERE tenant_id = ? AND modulo_id = ?");
                    $stmt->execute([$tenantId, $moduloId]);

                    if ($stmt->fetch()) {
                        $stmt = $this->db->prepare("UPDATE tenant_modulos SET activo = 'S', fecha_activacion = CURDATE() WHERE tenant_id = ? AND modulo_id = ?");
                    } else {
                        $stmt = $this->db->prepare("INSERT INTO tenant_modulos (tenant_id, modulo_id, activo, fecha_activacion) VALUES (?, ?, 'S', CURDATE())");
                    }
                    $stmt->execute([$tenantId, $moduloId]);
                }
            }

            $this->db->commit();
            setFlashMessage('success', 'Asignación masiva completada');
        } catch (\Exception $e) {
            $this->db->rollBack();
            setFlashMessage('error', 'Error en asignación masiva');
        }
        redirect('seguridad', 'asignacion', 'masiva');
    }
}
