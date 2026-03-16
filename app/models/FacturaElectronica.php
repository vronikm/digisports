<?php
/**
 * DigiSports - Modelo de Facturas Electrónicas
 * Gestión de comprobantes electrónicos en base de datos
 *
 * Nomenclatura: tabla facturas_electronicas, prefijo fac_
 *
 * @package DigiSports\Models
 * @version 1.1.0
 */

namespace App\Models;

class FacturaElectronica {

    private $db;
    private $table = 'facturas_electronicas';

    public function __construct() {
        $this->db = \Database::getInstance()->getConnection();
    }

    /**
     * Crear nueva factura electrónica
     *
     * Claves esperadas en $datos (sin prefijo fac_):
     *   tenant_id, factura_id, clave_acceso, tipo_comprobante,
     *   establecimiento, punto_emision, secuencial, fecha_emision,
     *   ambiente, tipo_emision,
     *   cliente_id, cliente_tipo_identificacion, cliente_identificacion,
     *   cliente_razon_social, cliente_direccion, cliente_email, cliente_telefono,
     *   subtotal_iva, subtotal_0, subtotal_no_objeto, subtotal_exento,
     *   subtotal, descuento, iva, ice, irbpnr, propina, total,
     *   estado_sri, xml_generado, xml_firmado, xml_autorizado,
     *   numero_autorizacion, fecha_autorizacion, observaciones, created_by
     *
     * @return int|false
     */
    public function crear(array $datos) {
        try {
            $sql = "INSERT INTO {$this->table} (
                        fac_tenant_id, fac_factura_id, fac_clave_acceso,
                        fac_tipo_comprobante, fac_establecimiento, fac_punto_emision,
                        fac_secuencial, fac_fecha_emision, fac_ambiente, fac_tipo_emision,
                        fac_cliente_id, fac_cliente_tipo_identificacion, fac_cliente_identificacion,
                        fac_cliente_razon_social, fac_cliente_direccion, fac_cliente_email, fac_cliente_telefono,
                        fac_subtotal_iva, fac_subtotal_0, fac_subtotal_no_objeto, fac_subtotal_exento,
                        fac_subtotal, fac_descuento, fac_iva, fac_ice, fac_irbpnr, fac_propina, fac_total,
                        fac_estado_sri, fac_xml_generado, fac_xml_firmado, fac_xml_autorizado,
                        fac_numero_autorizacion, fac_fecha_autorizacion,
                        fac_observaciones, created_by,
                        fac_created_at, fac_updated_at
                    ) VALUES (
                        :tenant_id, :factura_id, :clave_acceso,
                        :tipo_comprobante, :establecimiento, :punto_emision,
                        :secuencial, :fecha_emision, :ambiente, :tipo_emision,
                        :cliente_id, :cliente_tipo_identificacion, :cliente_identificacion,
                        :cliente_razon_social, :cliente_direccion, :cliente_email, :cliente_telefono,
                        :subtotal_iva, :subtotal_0, :subtotal_no_objeto, :subtotal_exento,
                        :subtotal, :descuento, :iva, :ice, :irbpnr, :propina, :total,
                        :estado_sri, :xml_generado, :xml_firmado, :xml_autorizado,
                        :numero_autorizacion, :fecha_autorizacion,
                        :observaciones, :created_by,
                        NOW(), NOW()
                    )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id'                  => $datos['tenant_id'],
                ':factura_id'                 => $datos['factura_id'] ?? null,
                ':clave_acceso'               => $datos['clave_acceso'],
                ':tipo_comprobante'           => $datos['tipo_comprobante'] ?? '01',
                ':establecimiento'            => $datos['establecimiento'] ?? '001',
                ':punto_emision'              => $datos['punto_emision'] ?? '001',
                ':secuencial'                 => $datos['secuencial'],
                ':fecha_emision'              => $datos['fecha_emision'],
                ':ambiente'                   => $datos['ambiente'] ?? '1',
                ':tipo_emision'               => $datos['tipo_emision'] ?? '1',
                ':cliente_id'                 => $datos['cliente_id'] ?? null,
                ':cliente_tipo_identificacion'=> $datos['cliente_tipo_identificacion'] ?? '05',
                ':cliente_identificacion'     => $datos['cliente_identificacion'],
                ':cliente_razon_social'       => $datos['cliente_razon_social'],
                ':cliente_direccion'          => $datos['cliente_direccion'] ?? null,
                ':cliente_email'              => $datos['cliente_email'] ?? null,
                ':cliente_telefono'           => $datos['cliente_telefono'] ?? null,
                ':subtotal_iva'               => $datos['subtotal_iva'] ?? $datos['subtotal'] ?? 0,
                ':subtotal_0'                 => $datos['subtotal_0'] ?? 0,
                ':subtotal_no_objeto'         => $datos['subtotal_no_objeto'] ?? 0,
                ':subtotal_exento'            => $datos['subtotal_exento'] ?? 0,
                ':subtotal'                   => $datos['subtotal'] ?? 0,
                ':descuento'                  => $datos['descuento'] ?? 0,
                ':iva'                        => $datos['iva'] ?? 0,
                ':ice'                        => $datos['ice'] ?? 0,
                ':irbpnr'                     => $datos['irbpnr'] ?? 0,
                ':propina'                    => $datos['propina'] ?? 0,
                ':total'                      => $datos['total'] ?? 0,
                ':estado_sri'                 => $datos['estado_sri'] ?? 'PENDIENTE',
                ':xml_generado'               => $datos['xml_generado'] ?? null,
                ':xml_firmado'                => $datos['xml_firmado'] ?? null,
                ':xml_autorizado'             => $datos['xml_autorizado'] ?? null,
                ':numero_autorizacion'        => $datos['numero_autorizacion'] ?? null,
                ':fecha_autorizacion'         => $datos['fecha_autorizacion'] ?? null,
                ':observaciones'              => $datos['observaciones'] ?? null,
                ':created_by'                 => $datos['created_by'] ?? null,
            ]);

            return (int) $this->db->lastInsertId();

        } catch (\PDOException $e) {
            error_log("Error al crear factura electrónica: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar estado y datos de factura electrónica
     *
     * @param int    $id     PK fac_id
     * @param string $estado Nuevo estado SRI
     * @param array  $datos  Campos opcionales: numero_autorizacion, fecha_autorizacion,
     *                       xml_firmado, xml_autorizado, mensaje_error, intentos_envio
     * @return bool
     */
    public function actualizarEstado($id, $estado, array $datos = []) {
        try {
            $set    = ['fac_estado_sri = :estado', 'fac_updated_at = NOW()'];
            $params = [':estado' => $estado, ':id' => $id];

            if (isset($datos['numero_autorizacion'])) {
                $set[] = 'fac_numero_autorizacion = :numero_autorizacion';
                $params[':numero_autorizacion'] = $datos['numero_autorizacion'];
            }
            if (isset($datos['fecha_autorizacion'])) {
                $set[] = 'fac_fecha_autorizacion = :fecha_autorizacion';
                $params[':fecha_autorizacion'] = $datos['fecha_autorizacion'];
            }
            if (isset($datos['xml_firmado'])) {
                $set[] = 'fac_xml_firmado = :xml_firmado';
                $params[':xml_firmado'] = $datos['xml_firmado'];
            }
            if (isset($datos['xml_autorizado'])) {
                $set[] = 'fac_xml_autorizado = :xml_autorizado';
                $params[':xml_autorizado'] = $datos['xml_autorizado'];
            }
            if (isset($datos['mensaje_error'])) {
                $set[] = 'fac_mensaje_error = :mensaje_error';
                $params[':mensaje_error'] = $datos['mensaje_error'];
            }
            if (isset($datos['intentos_envio'])) {
                $set[] = 'fac_intentos_envio = :intentos_envio';
                $set[] = 'fac_ultimo_intento = NOW()';
                $params[':intentos_envio'] = (int) $datos['intentos_envio'];
            }

            $sql  = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE fac_id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (\PDOException $e) {
            error_log("Error al actualizar estado FE: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener factura electrónica por ID con aislamiento de tenant
     *
     * @param int      $id       PK fac_id
     * @param int|null $tenantId Si se provee, filtra por tenant
     * @return array|null
     */
    public function obtenerPorId($id, $tenantId = null) {
        try {
            $sql    = "SELECT * FROM {$this->table} WHERE fac_id = ?";
            $params = [(int) $id];

            if ($tenantId !== null) {
                $sql    .= " AND fac_tenant_id = ?";
                $params[] = (int) $tenantId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        } catch (\PDOException $e) {
            error_log("Error obtenerPorId FE: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener factura electrónica por clave de acceso
     *
     * @param string   $claveAcceso
     * @param int|null $tenantId
     * @return array|null
     */
    public function obtenerPorClaveAcceso($claveAcceso, $tenantId = null) {
        try {
            $sql    = "SELECT * FROM {$this->table} WHERE fac_clave_acceso = ?";
            $params = [$claveAcceso];

            if ($tenantId !== null) {
                $sql    .= " AND fac_tenant_id = ?";
                $params[] = (int) $tenantId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        } catch (\PDOException $e) {
            return null;
        }
    }

    /**
     * Listar facturas electrónicas con filtros y paginación
     *
     * @param int   $tenantId
     * @param array $filtros  Claves: estado_sri, fecha_desde, fecha_hasta,
     *                        cliente_identificacion, busqueda
     * @param int   $limite
     * @param int   $offset
     * @return array
     */
    public function listar($tenantId, array $filtros = [], $limite = 50, $offset = 0) {
        try {
            $where  = ['fac_tenant_id = :tenant_id'];
            $params = [':tenant_id' => (int) $tenantId];

            if (!empty($filtros['estado_sri'])) {
                $where[]                = 'fac_estado_sri = :estado_sri';
                $params[':estado_sri']  = $filtros['estado_sri'];
            }
            if (!empty($filtros['fecha_desde'])) {
                $where[]                  = 'fac_fecha_emision >= :fecha_desde';
                $params[':fecha_desde']   = $filtros['fecha_desde'];
            }
            if (!empty($filtros['fecha_hasta'])) {
                $where[]                  = 'fac_fecha_emision <= :fecha_hasta';
                $params[':fecha_hasta']   = $filtros['fecha_hasta'];
            }
            if (!empty($filtros['tipo_comprobante'])) {
                $where[]                        = 'fac_tipo_comprobante = :tipo_comprobante';
                $params[':tipo_comprobante']    = $filtros['tipo_comprobante'];
            }
            if (!empty($filtros['cliente_identificacion'])) {
                $where[]                           = 'fac_cliente_identificacion = :cliente_identificacion';
                $params[':cliente_identificacion'] = $filtros['cliente_identificacion'];
            }
            if (!empty($filtros['busqueda'])) {
                $where[] = '(fac_clave_acceso LIKE :busqueda'
                          . ' OR fac_cliente_razon_social LIKE :busqueda'
                          . ' OR fac_secuencial LIKE :busqueda)';
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }

            $sql = "SELECT * FROM {$this->table}
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY fac_fecha_emision DESC, fac_id DESC
                    LIMIT " . (int) $limite . " OFFSET " . (int) $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("Error listar FE: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar facturas con los mismos filtros que listar()
     *
     * @param int   $tenantId
     * @param array $filtros
     * @return int
     */
    public function contar($tenantId, array $filtros = []) {
        try {
            $where  = ['fac_tenant_id = :tenant_id'];
            $params = [':tenant_id' => (int) $tenantId];

            if (!empty($filtros['estado_sri'])) {
                $where[]               = 'fac_estado_sri = :estado_sri';
                $params[':estado_sri'] = $filtros['estado_sri'];
            }
            if (!empty($filtros['fecha_desde'])) {
                $where[]                = 'fac_fecha_emision >= :fecha_desde';
                $params[':fecha_desde'] = $filtros['fecha_desde'];
            }
            if (!empty($filtros['fecha_hasta'])) {
                $where[]                = 'fac_fecha_emision <= :fecha_hasta';
                $params[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            if (!empty($filtros['tipo_comprobante'])) {
                $where[]                     = 'fac_tipo_comprobante = :tipo_comprobante';
                $params[':tipo_comprobante'] = $filtros['tipo_comprobante'];
            }
            if (!empty($filtros['busqueda'])) {
                $where[] = '(fac_clave_acceso LIKE :busqueda'
                          . ' OR fac_cliente_razon_social LIKE :busqueda'
                          . ' OR fac_secuencial LIKE :busqueda)';
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }

            $sql  = "SELECT COUNT(*) FROM {$this->table} WHERE " . implode(' AND ', $where);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();

        } catch (\PDOException $e) {
            return 0;
        }
    }

    /**
     * Resumen de cantidades y montos agrupados por estado
     *
     * @param int         $tenantId
     * @param string|null $fechaDesde
     * @param string|null $fechaHasta
     * @return array  [['estado_sri'=>'AUTORIZADO','cantidad'=>N,'monto_total'=>X], ...]
     */
    public function obtenerResumenEstados($tenantId, $fechaDesde = null, $fechaHasta = null) {
        try {
            $where  = ['fac_tenant_id = :tenant_id'];
            $params = [':tenant_id' => (int) $tenantId];

            if ($fechaDesde) {
                $where[]                = 'fac_fecha_emision >= :fecha_desde';
                $params[':fecha_desde'] = $fechaDesde;
            }
            if ($fechaHasta) {
                $where[]                = 'fac_fecha_emision <= :fecha_hasta';
                $params[':fecha_hasta'] = $fechaHasta;
            }

            $sql = "SELECT
                        fac_estado_sri   AS estado_sri,
                        COUNT(*)         AS cantidad,
                        SUM(fac_total)   AS monto_total
                    FROM {$this->table}
                    WHERE " . implode(' AND ', $where) . "
                    GROUP BY fac_estado_sri";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Facturas pendientes de autorización (para reintentos automáticos)
     *
     * @param int $tenantId
     * @param int $limite
     * @return array
     */
    public function obtenerPendientes($tenantId, $limite = 100) {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE fac_tenant_id = ?
                      AND fac_estado_sri IN ('PENDIENTE','GENERADA','FIRMADA','ENVIADA','RECIBIDA')
                    ORDER BY fac_created_at ASC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int) $tenantId, (int) $limite]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Obtener última factura emitida del tenant
     *
     * @param int $tenantId
     * @return array|null
     */
    public function obtenerUltima($tenantId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM {$this->table} WHERE fac_tenant_id = ? ORDER BY fac_id DESC LIMIT 1"
            );
            $stmt->execute([(int) $tenantId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        } catch (\PDOException $e) {
            return null;
        }
    }

    /**
     * Guardar detalles de la factura electrónica en las tablas auxiliares
     *
     * @param int   $facturaElectronicaId  PK fac_id recién insertado
     * @param array $detalles              Array de líneas del comprobante
     * @param array $pagos                 Array de formas de pago
     * @param array $infoAdicional         Array asociativo nombre => valor
     */
    public function guardarDetalles($facturaElectronicaId, array $detalles, array $pagos, array $infoAdicional = []) {
        foreach ($detalles as $det) {
            // Insertar línea
            $stmtDet = $this->db->prepare("
                INSERT INTO facturas_electronicas_detalle
                    (det_factura_electronica_id, det_codigo_principal, det_codigo_auxiliar,
                     det_descripcion, det_cantidad, det_precio_unitario,
                     det_descuento, det_precio_total_sin_impuesto,
                     det_producto_id, det_servicio_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtDet->execute([
                $facturaElectronicaId,
                $det['codigo']           ?? null,
                $det['codigo_auxiliar']  ?? null,
                $det['descripcion'],
                $det['cantidad'],
                $det['precio_unitario'],
                $det['descuento']        ?? 0,
                $det['precio_total_sin_impuesto'],
                $det['producto_id']      ?? null,
                $det['servicio_id']      ?? null,
            ]);

            $detId = (int) $this->db->lastInsertId();

            // Insertar impuestos de la línea
            foreach ($det['impuestos'] ?? [] as $imp) {
                $stmtImp = $this->db->prepare("
                    INSERT INTO facturas_electronicas_detalle_impuestos
                        (imp_detalle_id, imp_codigo, imp_codigo_porcentaje,
                         imp_tarifa, imp_base_imponible, imp_valor)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmtImp->execute([
                    $detId,
                    $imp['codigo'],
                    $imp['codigo_porcentaje'],
                    $imp['tarifa'],
                    $imp['base_imponible'],
                    $imp['valor'],
                ]);
            }
        }

        // Insertar formas de pago
        foreach ($pagos as $pago) {
            $stmtPag = $this->db->prepare("
                INSERT INTO facturas_electronicas_pagos
                    (pag_factura_electronica_id, pag_forma_pago,
                     pag_total, pag_plazo, pag_unidad_tiempo)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtPag->execute([
                $facturaElectronicaId,
                $pago['forma_pago'],
                $pago['total'],
                $pago['plazo']        ?? null,
                $pago['unidad_tiempo'] ?? 'dias',
            ]);
        }

        // Insertar info adicional
        foreach ($infoAdicional as $nombre => $valor) {
            if (empty($valor)) continue;
            $stmtAdi = $this->db->prepare("
                INSERT INTO facturas_electronicas_info_adicional
                    (adi_factura_electronica_id, adi_nombre, adi_valor)
                VALUES (?, ?, ?)
            ");
            $stmtAdi->execute([$facturaElectronicaId, $nombre, $valor]);
        }
    }
}
