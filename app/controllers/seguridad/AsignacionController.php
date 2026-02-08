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
    protected $moduloCodigo = 'SEGURIDAD';
    protected $moduloNombre = 'Seguridad';
    protected $moduloIcono = 'fas fa-shield-alt';
    protected $moduloColor = '#F59E0B';
    /**
     * Gestión de módulos por tenant
     */
    public function modulos() {
        $tenantId = $_GET['tenant_id'] ?? null;
        try {
            // Todos los tenants
            $tenants = $this->db->query("
                SELECT t.ten_tenant_id, t.ten_nombre_comercial, t.ten_ruc, p.sus_nombre as plan_nombre
                FROM seguridad_tenants t
                LEFT JOIN seguridad_planes_suscripcion p ON t.ten_plan_id = p.sus_plan_id
                WHERE t.ten_estado = 'A'
                ORDER BY t.ten_nombre_comercial
            ")->fetchAll(\PDO::FETCH_ASSOC);

            // Todos los módulos
            $modulos = $this->db->query("
                SELECT * FROM seguridad_modulos WHERE mod_activo = 1 ORDER BY mod_orden
            ")->fetchAll(\PDO::FETCH_ASSOC);

            // Si hay tenant seleccionado, obtener sus datos y módulos
            $tenant = null;
            $asignados = [];
            if ($tenantId) {
                $stmtT = $this->db->prepare("
                    SELECT t.*, p.sus_nombre as plan_nombre
                    FROM seguridad_tenants t
                    LEFT JOIN seguridad_planes_suscripcion p ON t.ten_plan_id = p.sus_plan_id
                    WHERE t.ten_tenant_id = ?
                ");
                $stmtT->execute([$tenantId]);
                $tenant = $stmtT->fetch(\PDO::FETCH_ASSOC) ?: [];

                $stmt = $this->db->prepare("
                    SELECT tm.*, m.mod_nombre, m.mod_icono, m.mod_color_fondo
                    FROM seguridad_tenant_modulos tm
                    JOIN seguridad_modulos m ON tm.tmo_modulo_id = m.mod_id
                    WHERE tm.tmo_tenant_id = ?
                ");
                $stmt->execute([$tenantId]);
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $asignados[$row['tmo_modulo_id']] = $row;
                }
            }
        } catch (\Exception $e) {
            $tenants = [];
            $modulos = [];
            $tenant = null;
            $asignados = [];
        }

        $this->renderModule('asignacion/modulos', [
            'tenants' => $tenants,
            'modulos' => $modulos,
            'tenant' => $tenant,
            'asignados' => $asignados,
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
            $stmt = $this->db->prepare("UPDATE seguridad_tenant_modulos SET tmo_activo = 'N' WHERE tmo_tenant_id = ?");
            $stmt->execute([$tenantId]);

            // Activar los seleccionados
            foreach ($modulosSeleccionados as $moduloId) {
                $iconoPersonalizado = $_POST['icono_' . $moduloId] ?? null;
                $nombrePersonalizado = $_POST['nombre_' . $moduloId] ?? null;
                $colorPersonalizado = $_POST['color_' . $moduloId] ?? null;
                // Verificar si existe
                $stmt = $this->db->prepare("SELECT tmo_id FROM seguridad_tenant_modulos WHERE tmo_tenant_id = ? AND tmo_modulo_id = ?");
                $stmt->execute([$tenantId, $moduloId]);
                
                if ($row = $stmt->fetch()) {
                    $stmt = $this->db->prepare("
                        UPDATE seguridad_tenant_modulos 
                        SET tmo_activo = 'S', tmo_fecha_inicio = CURDATE(), tmo_fecha_fin = NULL,
                            tmo_nombre_personalizado = ?, tmo_icono_personalizado = ?, tmo_color_personalizado = ?
                        WHERE tmo_id = ?
                    ");
                    $stmt->execute([$nombrePersonalizado, $iconoPersonalizado, $colorPersonalizado, $row['tmo_id']]);
                } else {
                    $stmt = $this->db->prepare("
                        INSERT INTO seguridad_tenant_modulos (tmo_tenant_id, tmo_modulo_id, tmo_activo, tmo_fecha_inicio, tmo_nombre_personalizado, tmo_icono_personalizado, tmo_color_personalizado)
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
                SELECT t.*, p.sus_nombre as plan_nombre
                FROM seguridad_tenants t
                LEFT JOIN seguridad_planes_suscripcion p ON t.ten_plan_id = p.sus_plan_id
                WHERE t.ten_estado = 'A'
                ORDER BY t.ten_nombre_comercial
            ")->fetchAll(\PDO::FETCH_ASSOC);

            $modulos = $this->db->query("
                SELECT * FROM seguridad_modulos WHERE mod_activo = 1 ORDER BY mod_orden
            ")->fetchAll(\PDO::FETCH_ASSOC);

            // Matriz de asignación
            $matriz = [];
            foreach ($tenants as $tenant) {
                $stmt = $this->db->prepare("SELECT tmo_modulo_id FROM seguridad_tenant_modulos WHERE tmo_tenant_id = ? AND tmo_activo = 'S'");
                $stmt->execute([$tenant['ten_tenant_id']]);
                $matriz[$tenant['ten_tenant_id']] = $stmt->fetchAll(\PDO::FETCH_COLUMN);
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
                $stmt = $this->db->prepare("UPDATE seguridad_tenant_modulos SET tmo_activo = 'N' WHERE tmo_tenant_id = ?");
                $stmt->execute([$tenantId]);

                // Activar seleccionados
                foreach ($modulos as $moduloId) {
                    $stmt = $this->db->prepare("SELECT tmo_id FROM seguridad_tenant_modulos WHERE tmo_tenant_id = ? AND tmo_modulo_id = ?");
                    $stmt->execute([$tenantId, $moduloId]);

                    if ($stmt->fetch()) {
                        $stmt = $this->db->prepare("UPDATE seguridad_tenant_modulos SET tmo_activo = 'S', tmo_fecha_inicio = CURDATE() WHERE tmo_tenant_id = ? AND tmo_modulo_id = ?");
                    } else {
                        $stmt = $this->db->prepare("INSERT INTO seguridad_tenant_modulos (tmo_tenant_id, tmo_modulo_id, tmo_activo, tmo_fecha_inicio) VALUES (?, ?, 'S', CURDATE())");
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
