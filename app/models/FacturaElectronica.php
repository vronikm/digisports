<?php
/**
 * DigiSports - Modelo de Facturas Electrónicas
 * Gestión de comprobantes electrónicos en base de datos
 * 
 * @package DigiSports\Models
 * @version 1.0.0
 */

namespace App\Models;

class FacturaElectronica {
    
    private $db;
    private $table = 'facturas_electronicas';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = \Database::getInstance()->getConnection();
    }
    
    /**
     * Crear nueva factura electrónica
     * 
     * @param array $datos Datos de la factura
     * @return int|false ID de la factura creada o false
     */
    public function crear($datos) {
        try {
            $sql = "INSERT INTO {$this->table} (
                fac_ten_id, fac_factura_id, fac_clave_acceso, fac_tipo_comprobante,
                fac_establecimiento, fac_punto_emision, fac_secuencial,
                fac_fecha_emision, fac_cli_cliente_id, fac_cli_identificacion,
                fac_cli_razon_social, fac_subtotal, fac_iva, fac_total,
                fac_estado_sri, fac_xml_generado, fac_xml_firmado, fac_xml_autorizado,
                fac_numero_autorizacion, fac_fecha_autorizacion,
                created_at, updated_at
            ) VALUES (
                :fac_ten_id, :fac_factura_id, :fac_clave_acceso, :fac_tipo_comprobante,
                :fac_establecimiento, :fac_punto_emision, :fac_secuencial,
                :fac_fecha_emision, :fac_cli_cliente_id, :fac_cli_identificacion,
                :fac_cli_razon_social, :fac_subtotal, :fac_iva, :fac_total,
                :fac_estado_sri, :fac_xml_generado, :fac_xml_firmado, :fac_xml_autorizado,
                :fac_numero_autorizacion, :fac_fecha_autorizacion,
                NOW(), NOW()
            )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':fac_ten_id' => $datos['fac_ten_id'],
                ':fac_factura_id' => $datos['fac_factura_id'] ?? null,
                ':fac_clave_acceso' => $datos['fac_clave_acceso'],
                ':fac_tipo_comprobante' => $datos['fac_tipo_comprobante'] ?? '01',
                ':fac_establecimiento' => $datos['fac_establecimiento'] ?? '001',
                ':fac_punto_emision' => $datos['fac_punto_emision'] ?? '001',
                ':fac_secuencial' => $datos['fac_secuencial'],
                ':fac_fecha_emision' => $datos['fac_fecha_emision'],
                ':fac_cli_cliente_id' => $datos['fac_cli_cliente_id'] ?? null,
                ':fac_cli_identificacion' => $datos['fac_cli_identificacion'],
                ':fac_cli_razon_social' => $datos['fac_cli_razon_social'],
                ':fac_subtotal' => $datos['fac_subtotal'],
                ':fac_iva' => $datos['fac_iva'],
                ':fac_total' => $datos['fac_total'],
                ':fac_estado_sri' => $datos['fac_estado_sri'] ?? 'PENDIENTE',
                ':fac_xml_generado' => $datos['fac_xml_generado'] ?? null,
                ':fac_xml_firmado' => $datos['fac_xml_firmado'] ?? null,
                ':fac_xml_autorizado' => $datos['fac_xml_autorizado'] ?? null,
                ':fac_numero_autorizacion' => $datos['fac_numero_autorizacion'] ?? null,
                ':fac_fecha_autorizacion' => $datos['fac_fecha_autorizacion'] ?? null,
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (\PDOException $e) {
            error_log("Error al crear factura electrónica: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar estado de factura electrónica
     * 
     * @param int $id ID de la factura
     * @param string $estado Nuevo estado
     * @param array $datos Datos adicionales a actualizar
     * @return bool
     */
    public function actualizarEstado($id, $estado, $datos = []) {
        try {
            $campos = ['estado_sri = :estado', 'updated_at = NOW()'];
            $params = [':estado' => $estado, ':id' => $id];
            
            if (isset($datos['numero_autorizacion'])) {
                $campos[] = 'numero_autorizacion = :numero_autorizacion';
                $params[':numero_autorizacion'] = $datos['numero_autorizacion'];
            }
            
            if (isset($datos['fecha_autorizacion'])) {
                $campos[] = 'fecha_autorizacion = :fecha_autorizacion';
                $params[':fecha_autorizacion'] = $datos['fecha_autorizacion'];
            }
            
            if (isset($datos['xml_firmado'])) {
                $campos[] = 'xml_firmado = :xml_firmado';
                $params[':xml_firmado'] = $datos['xml_firmado'];
            }
            
            if (isset($datos['xml_autorizado'])) {
                $campos[] = 'xml_autorizado = :xml_autorizado';
                $params[':xml_autorizado'] = $datos['xml_autorizado'];
            }
            
            if (isset($datos['mensaje_error'])) {
                $campos[] = 'mensaje_error = :mensaje_error';
                $params[':mensaje_error'] = $datos['mensaje_error'];
            }
            
                    $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE fac_id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
            
        } catch (\PDOException $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener factura por clave de acceso
     * 
     * @param string $claveAcceso Clave de acceso
     * @return array|null
     */
    public function obtenerPorClaveAcceso($claveAcceso) {
        try {
                    $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE fac_clave_acceso = ?");
            $stmt->execute([$claveAcceso]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            return null;
        }
    }
    
    /**
     * Obtener factura por ID
     * 
     * @param int $id ID de la factura
     * @return array|null
     */
    public function obtenerPorId($id) {
        try {
                    $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE fac_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            return null;
        }
    }
    
    /**
     * Listar facturas electrónicas con filtros
     * 
     * @param int $tenantId ID del tenant
     * @param array $filtros Filtros a aplicar
     * @param int $limite Límite de resultados
     * @param int $offset Desplazamiento
     * @return array
     */
    public function listar($tenantId, $filtros = [], $limite = 50, $offset = 0) {
        try {
                    $where = ['fac_ten_id = :fac_ten_id'];
            $params = [':tenant_id' => $tenantId];
            
            if (!empty($filtros['estado_sri'])) {
                $where[] = 'estado_sri = :estado_sri';
                $params[':estado_sri'] = $filtros['estado_sri'];
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $where[] = 'fecha_emision >= :fecha_desde';
                $params[':fecha_desde'] = $filtros['fecha_desde'];
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $where[] = 'fecha_emision <= :fecha_hasta';
                $params[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            
            if (!empty($filtros['cliente_identificacion'])) {
                $where[] = 'cliente_identificacion = :cliente_identificacion';
                $params[':cliente_identificacion'] = $filtros['cliente_identificacion'];
            }
            
            if (!empty($filtros['busqueda'])) {
                $where[] = '(clave_acceso LIKE :busqueda OR cliente_razon_social LIKE :busqueda OR secuencial LIKE :busqueda)';
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }
            
            $sql = "SELECT * FROM {$this->table} 
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY fecha_emision DESC, id DESC
                    LIMIT {$limite} OFFSET {$offset}";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            return [];
        }
    }
    
    /**
     * Contar facturas con filtros
     * 
     * @param int $tenantId ID del tenant
     * @param array $filtros Filtros a aplicar
     * @return int
     */
    public function contar($tenantId, $filtros = []) {
        try {
                    $where = ['fac_ten_id = :fac_ten_id'];
            $params = [':tenant_id' => $tenantId];
            
            if (!empty($filtros['estado_sri'])) {
                $where[] = 'estado_sri = :estado_sri';
                $params[':estado_sri'] = $filtros['estado_sri'];
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $where[] = 'fecha_emision >= :fecha_desde';
                $params[':fecha_desde'] = $filtros['fecha_desde'];
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $where[] = 'fecha_emision <= :fecha_hasta';
                $params[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE " . implode(' AND ', $where);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return (int) $stmt->fetchColumn();
            
        } catch (\PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Obtener facturas pendientes de autorización
     * 
     * @param int $tenantId ID del tenant
     * @param int $limite Límite de resultados
     * @return array
     */
    public function obtenerPendientes($tenantId, $limite = 100) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                            WHERE fac_ten_id = ? 
                    AND estado_sri IN ('PENDIENTE', 'RECIBIDA', 'EN_PROCESO')
                    ORDER BY created_at ASC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $limite]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener resumen de facturas por estado
     * 
     * @param int $tenantId ID del tenant
     * @param string $fechaDesde Fecha desde
     * @param string $fechaHasta Fecha hasta
     * @return array
     */
    public function obtenerResumenEstados($tenantId, $fechaDesde = null, $fechaHasta = null) {
        try {
            $where = ['tenant_id = :tenant_id'];
            $params = [':tenant_id' => $tenantId];
            
            if ($fechaDesde) {
                $where[] = 'fecha_emision >= :fecha_desde';
                $params[':fecha_desde'] = $fechaDesde;
            }
            
            if ($fechaHasta) {
                $where[] = 'fecha_emision <= :fecha_hasta';
                $params[':fecha_hasta'] = $fechaHasta;
            }
            
            $sql = "SELECT 
                        estado_sri,
                        COUNT(*) as cantidad,
                        SUM(total) as monto_total
                    FROM {$this->table}
                    WHERE " . implode(' AND ', $where) . "
                    GROUP BY estado_sri";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener última factura del tenant
     * 
     * @param int $tenantId ID del tenant
     * @return array|null
     */
    public function obtenerUltima($tenantId) {
        try {
                    $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE fac_ten_id = ? ORDER BY fac_id DESC LIMIT 1");
            $stmt->execute([$tenantId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            return null;
        }
    }
}
