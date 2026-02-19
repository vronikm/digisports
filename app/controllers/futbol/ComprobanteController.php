<?php
/**
 * DigiSports Fútbol — Controlador de Comprobantes
 * Emisión de recibos y comprobantes de pago
 * 
 * Columnas reales:
 *   futbol_comprobantes: fcm_comprobante_id, fcm_tenant_id, fcm_pago_id, fcm_numero, fcm_tipo,
 *     fcm_concepto, fcm_subtotal, fcm_descuento, fcm_iva, fcm_total, fcm_metodo_pago,
 *     fcm_cliente_id, fcm_alumno_id, fcm_fecha_emision, fcm_estado, fcm_pdf_path,
 *     fcm_enviado_email, fcm_enviado_whatsapp, fcm_notas, fcm_datos_json, fcm_created_at
 *   Alumno se obtiene via JOIN: comprobantes → fcm_pago_id → futbol_pagos → fpg_alumno_id → alumnos
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class ComprobanteController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'FUTBOL';
    }

    /**
     * Listar comprobantes con filtros
     */
    public function index() {
        try {
            $this->setupModule();

            $where = 'fcm.fcm_tenant_id = ?';
            $params = [$this->tenantId];

            // Filtro por estado
            $estado = $this->get('estado');
            if ($estado) { $where .= ' AND fcm.fcm_estado = ?'; $params[] = $estado; }

            // Filtro por tipo
            $tipo = $this->get('tipo');
            if ($tipo) { $where .= ' AND fcm.fcm_tipo = ?'; $params[] = $tipo; }

            // Filtro por rango de fechas
            $fechaDesde = $this->get('fecha_desde');
            $fechaHasta = $this->get('fecha_hasta');
            if ($fechaDesde) { $where .= ' AND fcm.fcm_fecha_emision >= ?'; $params[] = $fechaDesde; }
            if ($fechaHasta) { $where .= ' AND fcm.fcm_fecha_emision <= ?'; $params[] = $fechaHasta; }

            $stm = $this->db->prepare("
                SELECT fcm.*,
                       p.fpg_tipo AS pago_tipo, p.fpg_fecha AS pago_fecha,
                       p.fpg_metodo_pago AS pago_metodo, p.fpg_total AS pago_total,
                       p.fpg_referencia AS pago_referencia,
                       a.alu_nombres, a.alu_apellidos, a.alu_identificacion
                FROM futbol_comprobantes fcm
                LEFT JOIN futbol_pagos p ON fcm.fcm_pago_id = p.fpg_pago_id
                LEFT JOIN alumnos a ON p.fpg_alumno_id = a.alu_alumno_id
                WHERE {$where}
                ORDER BY fcm.fcm_fecha_emision DESC, fcm.fcm_numero DESC
                LIMIT 200
            ");
            $stm->execute($params);
            $this->viewData['comprobantes'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Comprobantes';
            $this->renderModule('futbol/comprobantes/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando comprobantes: " . $e->getMessage());
            $this->error('Error al cargar comprobantes');
        }
    }

    /**
     * Generar/crear comprobante desde un pago
     */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $pagoId = (int)($this->post('pago_id') ?? 0);
            $tipo = $this->post('tipo') ?: 'RECIBO';
            if (!$pagoId) return $this->jsonResponse(['success' => false, 'message' => 'ID de pago requerido']);
            if (!in_array($tipo, ['RECIBO', 'FACTURA', 'NOTA_CREDITO'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Tipo de comprobante inválido']);
            }

            // Verificar que el pago existe
            $stmPago = $this->db->prepare("
                SELECT p.*, a.alu_nombres, a.alu_apellidos, a.alu_identificacion
                FROM futbol_pagos p
                LEFT JOIN alumnos a ON p.fpg_alumno_id = a.alu_alumno_id
                WHERE p.fpg_pago_id = ? AND p.fpg_tenant_id = ?
            ");
            $stmPago->execute([$pagoId, $this->tenantId]);
            $pago = $stmPago->fetch(\PDO::FETCH_ASSOC);
            if (!$pago) return $this->jsonResponse(['success' => false, 'message' => 'Pago no encontrado']);

            // Verificar si ya tiene comprobante activo
            $stmExist = $this->db->prepare("SELECT COUNT(*) FROM futbol_comprobantes WHERE fcm_pago_id = ? AND fcm_tenant_id = ? AND fcm_estado = 'EMITIDO'");
            $stmExist->execute([$pagoId, $this->tenantId]);
            if ((int)$stmExist->fetchColumn() > 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'Este pago ya tiene un comprobante emitido']);
            }

            // Obtener prefijo desde configuración
            $stmCfg = $this->db->prepare("SELECT fcg_valor FROM futbol_configuracion WHERE fcg_clave = 'comprobante_prefijo' AND fcg_tenant_id = ? LIMIT 1");
            $stmCfg->execute([$this->tenantId]);
            $prefijo = $stmCfg->fetchColumn() ?: 'REC';

            // Obtener siguiente número secuencial
            $stmMax = $this->db->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(fcm_numero, '-', -1) AS UNSIGNED)) FROM futbol_comprobantes WHERE fcm_tenant_id = ?");
            $stmMax->execute([$this->tenantId]);
            $maxNum = (int)$stmMax->fetchColumn();
            $numero = $prefijo . '-' . str_pad($maxNum + 1, 4, '0', STR_PAD_LEFT);

            // Preparar datos JSON adicionales
            $datosJson = json_encode([
                'alumno_nombre' => trim(($pago['alu_nombres'] ?? '') . ' ' . ($pago['alu_apellidos'] ?? '')),
                'alumno_identificacion' => $pago['alu_identificacion'] ?? null,
                'pago_tipo' => $pago['fpg_tipo'] ?? null,
                'pago_metodo' => $pago['fpg_metodo_pago'] ?? null,
                'pago_referencia' => $pago['fpg_referencia'] ?? null,
                'monto_original' => $pago['fpg_monto'] ?? 0,
                'descuento' => $pago['fpg_descuento'] ?? 0,
                'recargo_mora' => $pago['fpg_recargo_mora'] ?? 0,
            ], JSON_UNESCAPED_UNICODE);

            // Insertar comprobante
            $concepto = trim($this->post('concepto') ?? '') ?: ('Pago ' . ($pago['fpg_tipo'] ?? 'servicio'));
            $monto = (float)($pago['fpg_total'] ?? $pago['fpg_monto'] ?? 0);

            $stm = $this->db->prepare("
                INSERT INTO futbol_comprobantes (fcm_tenant_id, fcm_pago_id, fcm_numero, fcm_tipo,
                    fcm_concepto, fcm_total, fcm_fecha_emision, fcm_estado, fcm_datos_json)
                VALUES (?, ?, ?, ?, ?, ?, CURDATE(), 'EMITIDO', ?)
            ");
            $stm->execute([
                $this->tenantId,
                $pagoId,
                $numero,
                $tipo,
                $concepto,
                $monto,
                $datosJson,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Comprobante emitido: ' . $numero, 'numero' => $numero]);

        } catch (\Exception $e) {
            $this->logError("Error emitiendo comprobante: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al emitir comprobante']);
        }
    }

    /**
     * Alias para crear (compatibilidad)
     */
    public function generar() {
        return $this->crear();
    }

    /**
     * Anular comprobante
     */
    public function anular() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE futbol_comprobantes SET fcm_estado = 'ANULADO' WHERE fcm_comprobante_id = ? AND fcm_tenant_id = ?")
                ->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Comprobante anulado']);

        } catch (\Exception $e) {
            $this->logError("Error anulando comprobante: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al anular comprobante']);
        }
    }

    /**
     * Ver detalle de comprobante (JSON para modal)
     */
    public function ver() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $stm = $this->db->prepare("
                SELECT fcm.*,
                       p.fpg_tipo AS pago_tipo, p.fpg_fecha AS pago_fecha,
                       p.fpg_metodo_pago AS pago_metodo, p.fpg_referencia AS pago_referencia,
                       p.fpg_monto AS pago_monto, p.fpg_descuento AS pago_descuento,
                       p.fpg_recargo_mora AS pago_recargo, p.fpg_total AS pago_total,
                       a.alu_nombres, a.alu_apellidos, a.alu_identificacion,
                       c.cli_nombres AS rep_nombres, c.cli_apellidos AS rep_apellidos,
                       c.cli_telefono AS representante_telefono,
                       c.cli_email AS representante_email,
                       c.cli_identificacion AS representante_identificacion
                FROM futbol_comprobantes fcm
                LEFT JOIN futbol_pagos p ON fcm.fcm_pago_id = p.fpg_pago_id
                LEFT JOIN alumnos a ON p.fpg_alumno_id = a.alu_alumno_id
                LEFT JOIN clientes c ON a.alu_representante_id = c.cli_cliente_id AND c.cli_tenant_id = a.alu_tenant_id
                WHERE fcm.fcm_comprobante_id = ? AND fcm.fcm_tenant_id = ?
            ");
            $stm->execute([$id, $this->tenantId]);
            $comprobante = $stm->fetch(\PDO::FETCH_ASSOC);

            if (!$comprobante) return $this->jsonResponse(['success' => false, 'message' => 'Comprobante no encontrado']);

            // Descifrar datos sensibles (LOPDP)
            if (!empty($comprobante['representante_telefono'])) $comprobante['representante_telefono'] = \DataProtection::decrypt($comprobante['representante_telefono']);
            if (!empty($comprobante['representante_email']))    $comprobante['representante_email']    = \DataProtection::decrypt($comprobante['representante_email']);
            if (!empty($comprobante['representante_identificacion'])) $comprobante['representante_identificacion'] = \DataProtection::decrypt($comprobante['representante_identificacion']);
            if (!empty($comprobante['alu_identificacion'])) $comprobante['alu_identificacion'] = \DataProtection::decrypt($comprobante['alu_identificacion']);
            $comprobante['representante_nombre'] = trim(($comprobante['rep_nombres'] ?? '') . ' ' . ($comprobante['rep_apellidos'] ?? ''));

            // Decodificar datos JSON si existe
            if (!empty($comprobante['fcm_datos_json'])) {
                $comprobante['datos_extra'] = json_decode($comprobante['fcm_datos_json'], true);
            }

            return $this->jsonResponse(['success' => true, 'data' => $comprobante]);

        } catch (\Exception $e) {
            $this->logError("Error obteniendo comprobante: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al obtener comprobante']);
        }
    }

    /**
     * Imprimir comprobante (renderiza vista de impresión)
     */
    public function imprimir() {
        try {
            $this->setupModule();
            $id = (int)($this->get('id') ?? 0);
            if (!$id) { $this->error('ID de comprobante requerido'); return; }

            $stm = $this->db->prepare("
                SELECT fcm.*,
                       p.fpg_tipo AS pago_tipo, p.fpg_fecha AS pago_fecha,
                       p.fpg_metodo_pago AS pago_metodo, p.fpg_total AS pago_total,
                       p.fpg_monto AS pago_monto, p.fpg_descuento AS pago_descuento,
                       p.fpg_recargo_mora AS pago_recargo,
                       a.alu_nombres, a.alu_apellidos, a.alu_identificacion,
                       c.cli_nombres AS rep_nombres, c.cli_apellidos AS rep_apellidos,
                       c.cli_telefono AS representante_telefono,
                       c.cli_email AS representante_email,
                       c.cli_identificacion AS representante_identificacion,
                       c.cli_direccion AS representante_direccion,
                       s.sed_nombre AS sede_nombre
                FROM futbol_comprobantes fcm
                LEFT JOIN futbol_pagos p ON fcm.fcm_pago_id = p.fpg_pago_id
                LEFT JOIN alumnos a ON p.fpg_alumno_id = a.alu_alumno_id
                LEFT JOIN clientes c ON a.alu_representante_id = c.cli_cliente_id AND c.cli_tenant_id = a.alu_tenant_id
                LEFT JOIN instalaciones_sedes s ON p.fpg_sede_id = s.sed_sede_id
                WHERE fcm.fcm_comprobante_id = ? AND fcm.fcm_tenant_id = ?
            ");
            $stm->execute([$id, $this->tenantId]);
            $comprobante = $stm->fetch(\PDO::FETCH_ASSOC);

            if (!$comprobante) { $this->error('Comprobante no encontrado'); return; }

            // Descifrar datos sensibles (LOPDP)
            if (!empty($comprobante['representante_telefono'])) $comprobante['representante_telefono'] = \DataProtection::decrypt($comprobante['representante_telefono']);
            if (!empty($comprobante['representante_email']))    $comprobante['representante_email']    = \DataProtection::decrypt($comprobante['representante_email']);
            if (!empty($comprobante['representante_identificacion'])) $comprobante['representante_identificacion'] = \DataProtection::decrypt($comprobante['representante_identificacion']);
            if (!empty($comprobante['alu_identificacion'])) $comprobante['alu_identificacion'] = \DataProtection::decrypt($comprobante['alu_identificacion']);
            $comprobante['representante_nombre'] = trim(($comprobante['rep_nombres'] ?? '') . ' ' . ($comprobante['rep_apellidos'] ?? ''));

            // Decodificar datos JSON
            if (!empty($comprobante['fcm_datos_json'])) {
                $comprobante['datos_extra'] = json_decode($comprobante['fcm_datos_json'], true);
            }

            $this->viewData['comprobante'] = $comprobante;
            $this->viewData['title'] = 'Comprobante ' . $comprobante['fcm_numero'];
            $this->renderModule('futbol/comprobantes/imprimir', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error imprimiendo comprobante: " . $e->getMessage());
            $this->error('Error al cargar comprobante para impresión');
        }
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
