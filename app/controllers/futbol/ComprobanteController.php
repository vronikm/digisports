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
     * Generar/crear comprobante desde un pago o abono
     */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $pagoId  = (int)($this->post('pago_id')  ?? 0);
            $abonoId = (int)($this->post('abono_id') ?? 0);
            $tipo    = $this->post('tipo') ?: 'RECIBO';

            if (!$pagoId) return $this->jsonResponse(['success' => false, 'message' => 'ID de pago requerido']);
            if (!in_array($tipo, ['RECIBO', 'FACTURA', 'NOTA_CREDITO'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Tipo de comprobante inválido']);
            }

            // Verificar que el pago existe y obtener datos completos
            $stmPago = $this->db->prepare("
                SELECT p.*,
                       a.alu_nombres, a.alu_apellidos, a.alu_identificacion,
                       a.alu_alumno_id,
                       c.cli_nombres AS rep_nombres, c.cli_apellidos AS rep_apellidos,
                       c.cli_identificacion AS rep_ci, c.cli_telefono AS rep_telefono,
                       c.cli_email AS rep_email, c.cli_direccion AS rep_direccion,
                       cat.fct_nombre AS categoria_nombre,
                       g.fgr_nombre AS grupo_nombre,
                       s.sed_nombre AS sede_nombre, s.sed_email AS sede_email,
                       s.sed_telefono AS sede_telefono, s.sed_direccion AS sede_direccion,
                       arc_logo.arc_ruta_relativa AS logo_ruta,
                       arc_firma.arc_ruta_relativa AS firma_ruta,
                       t.ten_nombre_comercial AS empresa_nombre, t.ten_ruc AS empresa_ruc
                FROM futbol_pagos p
                LEFT JOIN alumnos a ON p.fpg_alumno_id = a.alu_alumno_id
                LEFT JOIN clientes c ON a.alu_representante_id = c.cli_cliente_id AND c.cli_tenant_id = a.alu_tenant_id
                LEFT JOIN futbol_grupos g ON p.fpg_grupo_id = g.fgr_grupo_id
                LEFT JOIN futbol_categorias cat ON g.fgr_categoria_id = cat.fct_categoria_id
                LEFT JOIN instalaciones_sedes s ON p.fpg_sede_id = s.sed_sede_id
                LEFT JOIN core_archivos arc_logo
                       ON arc_logo.arc_entidad      = 'instalaciones_sedes'
                      AND arc_logo.arc_entidad_id   = s.sed_sede_id
                      AND arc_logo.arc_tenant_id    = p.fpg_tenant_id
                      AND arc_logo.arc_categoria    = 'logos'
                      AND arc_logo.arc_es_principal = 1
                      AND arc_logo.arc_estado       = 'activo'
                LEFT JOIN core_archivos arc_firma
                       ON arc_firma.arc_entidad      = 'instalaciones_sedes'
                      AND arc_firma.arc_entidad_id   = s.sed_sede_id
                      AND arc_firma.arc_tenant_id    = p.fpg_tenant_id
                      AND arc_firma.arc_categoria    = 'firmas'
                      AND arc_firma.arc_es_principal = 1
                      AND arc_firma.arc_estado       = 'activo'
                LEFT JOIN seguridad_tenants t ON p.fpg_tenant_id = t.ten_tenant_id
                WHERE p.fpg_pago_id = ? AND p.fpg_tenant_id = ?
                LIMIT 1
            ");
            $stmPago->execute([$pagoId, $this->tenantId]);
            $pago = $stmPago->fetch(\PDO::FETCH_ASSOC);
            if (!$pago) return $this->jsonResponse(['success' => false, 'message' => 'Pago no encontrado']);

            // Si es abono, verificar que el abono existe
            $abono = null;
            if ($abonoId) {
                $stmAb = $this->db->prepare("SELECT * FROM futbol_abonos WHERE fab_abono_id = ? AND fab_pago_id = ? AND fab_tenant_id = ?");
                $stmAb->execute([$abonoId, $pagoId, $this->tenantId]);
                $abono = $stmAb->fetch(\PDO::FETCH_ASSOC);
            }

            // Verificar si ya existe comprobante emitido para este pago/abono
            if ($abonoId) {
                $stmExist = $this->db->prepare("SELECT COUNT(*) FROM futbol_comprobantes WHERE fcm_abono_id = ? AND fcm_tenant_id = ? AND fcm_estado = 'EMITIDO'");
                $stmExist->execute([$abonoId, $this->tenantId]);
            } else {
                $stmExist = $this->db->prepare("SELECT COUNT(*) FROM futbol_comprobantes WHERE fcm_pago_id = ? AND fcm_abono_id IS NULL AND fcm_tenant_id = ? AND fcm_estado = 'EMITIDO'");
                $stmExist->execute([$pagoId, $this->tenantId]);
            }
            if ((int)$stmExist->fetchColumn() > 0) {
                // Devolver el existente en lugar de error
                $stmComp = $abonoId
                    ? $this->db->prepare("SELECT fcm_comprobante_id, fcm_numero FROM futbol_comprobantes WHERE fcm_abono_id = ? AND fcm_tenant_id = ? AND fcm_estado = 'EMITIDO' LIMIT 1")
                    : $this->db->prepare("SELECT fcm_comprobante_id, fcm_numero FROM futbol_comprobantes WHERE fcm_pago_id = ? AND fcm_abono_id IS NULL AND fcm_tenant_id = ? AND fcm_estado = 'EMITIDO' LIMIT 1");
                $stmComp->execute($abonoId ? [$abonoId, $this->tenantId] : [$pagoId, $this->tenantId]);
                $existente = $stmComp->fetch(\PDO::FETCH_ASSOC);
                return $this->jsonResponse([
                    'success'         => true,
                    'message'         => 'Comprobante existente: ' . ($existente['fcm_numero'] ?? ''),
                    'numero'          => $existente['fcm_numero'] ?? '',
                    'comprobante_id'  => $existente['fcm_comprobante_id'] ?? 0,
                    'ya_existia'      => true,
                ]);
            }

            // Desencriptar PII del representante
            if (!empty($pago['rep_ci']))        $pago['rep_ci']        = \DataProtection::decrypt($pago['rep_ci']);
            if (!empty($pago['rep_telefono']))  $pago['rep_telefono']  = \DataProtection::decrypt($pago['rep_telefono']);
            if (!empty($pago['rep_email']))     $pago['rep_email']     = \DataProtection::decrypt($pago['rep_email']);
            if (!empty($pago['rep_direccion'])) $pago['rep_direccion'] = \DataProtection::decrypt($pago['rep_direccion']);
            if (!empty($pago['alu_identificacion'])) $pago['alu_identificacion'] = \DataProtection::decrypt($pago['alu_identificacion']);

            // Desencriptar PII del tenant (empresa)
            if (!empty($pago['empresa_ruc']))   $pago['empresa_ruc']   = \DataProtection::decrypt($pago['empresa_ruc']);

            // Generar número del comprobante: PRE-AAAAMMDD-NNNN
            $stmCfg = $this->db->prepare("SELECT fcg_valor FROM futbol_configuracion WHERE fcg_clave = 'comprobante_prefijo' AND fcg_tenant_id = ? LIMIT 1");
            $stmCfg->execute([$this->tenantId]);
            $prefijo = $stmCfg->fetchColumn() ?: 'REC';

            $stmMax = $this->db->prepare("SELECT COUNT(*) + 1 FROM futbol_comprobantes WHERE fcm_tenant_id = ?");
            $stmMax->execute([$this->tenantId]);
            $seq    = (int)$stmMax->fetchColumn();
            $numero = $prefijo . '-' . date('Ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // Calcular totales
            $montoBase  = (float)($pago['fpg_monto']          ?? 0);
            $beca       = (float)($pago['fpg_beca_descuento'] ?? 0);
            $descuento  = (float)($pago['fpg_descuento']      ?? 0);
            $totalPago  = (float)($pago['fpg_total']          ?? $montoBase);
            $saldo      = (float)($pago['fpg_saldo']          ?? 0);
            $montoComp  = $abono ? (float)($abono['fab_monto'] ?? 0) : $totalPago;

            $mesRef     = !empty($pago['fpg_mes_correspondiente']) ? $this->mesEnEspanol((int)substr($pago['fpg_mes_correspondiente'],5,2)) . ' ' . substr($pago['fpg_mes_correspondiente'],0,4) : '';

            // Logo y firma: JOIN puede fallar si fpg_sede_id es NULL; fallback por tenant
            $logoRutaC  = $pago['logo_ruta']  ?? null;
            $firmaRutaC = $pago['firma_ruta'] ?? null;
            if (!$logoRutaC) {
                $stmL = $this->db->prepare("SELECT arc_ruta_relativa FROM core_archivos WHERE arc_tenant_id = ? AND arc_entidad = 'instalaciones_sedes' AND arc_categoria = 'logos' AND arc_es_principal = 1 AND arc_estado = 'activo' LIMIT 1");
                $stmL->execute([$this->tenantId]);
                $logoRutaC = $stmL->fetchColumn() ?: null;
            }
            if (!$firmaRutaC) {
                $stmF = $this->db->prepare("SELECT arc_ruta_relativa FROM core_archivos WHERE arc_tenant_id = ? AND arc_entidad = 'instalaciones_sedes' AND arc_categoria = 'firmas' AND arc_es_principal = 1 AND arc_estado = 'activo' LIMIT 1");
                $stmF->execute([$this->tenantId]);
                $firmaRutaC = $stmF->fetchColumn() ?: null;
            }
            $logoPath  = $this->rutaRelativaAAbsoluta($logoRutaC);
            $firmaPath = $this->rutaRelativaAAbsoluta($firmaRutaC);

            // Datos JSON completos
            $datosJson = json_encode([
                'alumno_nombre'          => trim(($pago['alu_nombres'] ?? '') . ' ' . ($pago['alu_apellidos'] ?? '')),
                'alumno_identificacion'  => $pago['alu_identificacion'] ?? null,
                'alumno_categoria'       => $pago['categoria_nombre'] ?? null,
                'alumno_grupo'           => $pago['grupo_nombre'] ?? null,
                'rep_nombre'             => trim(($pago['rep_nombres'] ?? '') . ' ' . ($pago['rep_apellidos'] ?? '')),
                'rep_ci'                 => $pago['rep_ci'] ?? null,
                'rep_telefono'           => $pago['rep_telefono'] ?? null,
                'rep_email'              => $pago['rep_email'] ?? null,
                'rep_direccion'          => $pago['rep_direccion'] ?? null,
                'pago_tipo'              => $pago['fpg_tipo'] ?? null,
                'metodo_pago'            => $abono ? ($abono['fab_metodo_pago'] ?? $pago['fpg_metodo_pago']) : $pago['fpg_metodo_pago'],
                'referencia'             => $abono ? ($abono['fab_referencia'] ?? $pago['fpg_referencia']) : $pago['fpg_referencia'],
                'mes_referencia'         => $mesRef,
                'monto_base'             => $montoBase,
                'beca'                   => $beca,
                'descuento'              => $descuento,
                'total_pago'             => $totalPago,
                'monto_abono'            => $abono ? $montoComp : null,
                'empresa_nombre'         => $pago['empresa_nombre'] ?? ($_SESSION['tenant_nombre'] ?? ''),
                'empresa_ruc'            => $pago['empresa_ruc']    ?? '',
                'sede_nombre'            => $pago['sede_nombre']    ?? '',
                'sede_telefono'          => $pago['sede_telefono']  ?? '',
                'sede_email'             => $pago['sede_email']     ?? '',
                'sede_direccion'         => $pago['sede_direccion'] ?? '',
                'logo_path'              => $logoPath,
                'firma_path'             => $firmaPath,
            ], JSON_UNESCAPED_UNICODE);

            $concepto = trim($this->post('concepto') ?? '') ?: ('Pago de ' . ($pago['fpg_tipo'] ?? 'servicio'));

            $stmIns = $this->db->prepare("
                INSERT INTO futbol_comprobantes (fcm_tenant_id, fcm_pago_id, fcm_abono_id, fcm_numero, fcm_tipo,
                    fcm_concepto, fcm_total, fcm_saldo, fcm_fecha_emision, fcm_estado, fcm_datos_json)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'EMITIDO', ?)
            ");
            $stmIns->execute([
                $this->tenantId,
                $pagoId,
                $abonoId ?: null,
                $numero,
                $tipo,
                $concepto,
                $montoComp,
                $saldo > 0 ? $saldo : null,
                $datosJson,
            ]);
            $comprobanteId = (int)$this->db->lastInsertId();

            return $this->jsonResponse([
                'success'        => true,
                'message'        => 'Comprobante emitido: ' . $numero,
                'numero'         => $numero,
                'comprobante_id' => $comprobanteId,
            ]);

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
     * Descargar comprobante como PDF (generado on-demand con wkhtmltopdf)
     */
    public function descargarPdf() {
        try {
            $this->setupModule();
            $id = (int)($this->get('id') ?? 0);
            if (!$id) { $this->error('ID requerido'); return; }

            $datos = $this->obtenerDatosParaRecibo($id);
            if (!$datos) { $this->error('Comprobante no encontrado'); return; }

            require_once BASE_PATH . '/app/services/ReciboService.php';
            $svc = new \App\Services\ReciboService();
            $pdf = $svc->generarPdf($datos);

            if (!$pdf) {
                // Fallback: redirigir a la vista de impresión
                header('Location: ' . url('futbol', 'comprobante', 'imprimir', ['id' => $id]));
                exit;
            }

            $numero = $datos['numero'] ?? 'recibo';
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Recibo_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $numero) . '.pdf"');
            header('Content-Length: ' . filesize($pdf));
            readfile($pdf);
            @unlink($pdf);
            exit;

        } catch (\Exception $e) {
            $this->logError("Error generando PDF recibo: " . $e->getMessage());
            $this->error('Error al generar PDF');
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

            $datos = $this->obtenerDatosParaRecibo($id);
            if (!$datos) { $this->error('Comprobante no encontrado'); return; }

            // También obtener el registro raw para compatibilidad con la vista
            $stm = $this->db->prepare("SELECT * FROM futbol_comprobantes WHERE fcm_comprobante_id = ? AND fcm_tenant_id = ?");
            $stm->execute([$id, $this->tenantId]);
            $comprobante = $stm->fetch(\PDO::FETCH_ASSOC);
            $comprobante['datos_extra'] = json_decode($comprobante['fcm_datos_json'] ?? '{}', true) ?: [];

            $this->viewData['comprobante']   = $comprobante;
            $this->viewData['recibo_datos']  = $datos;
            $this->viewData['url_pdf']       = url('futbol', 'comprobante', 'descargarPdf') . '&id=' . $id;
            $this->viewData['url_enviar']    = url('futbol', 'comprobante', 'enviar');
            $this->viewData['csrf_token']    = \Security::generateCsrfToken();
            $this->viewData['title']         = 'Recibo ' . $comprobante['fcm_numero'];
            $this->renderModule('futbol/comprobantes/imprimir', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error imprimiendo comprobante: " . $e->getMessage());
            $this->error('Error al cargar comprobante para impresión');
        }
    }

    /**
     * Enviar comprobante por correo electrónico
     */
    public function enviar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID de comprobante requerido']);

            $stm = $this->db->prepare("
                SELECT fcm.*,
                       p.fpg_tipo AS pago_tipo, p.fpg_fecha AS pago_fecha,
                       p.fpg_metodo_pago AS pago_metodo, p.fpg_total AS pago_total,
                       p.fpg_monto AS pago_monto, p.fpg_descuento AS pago_descuento,
                       p.fpg_beca_descuento AS pago_beca_descuento,
                       a.alu_nombres, a.alu_apellidos, a.alu_identificacion,
                       c.cli_nombres AS rep_nombres, c.cli_apellidos AS rep_apellidos,
                       c.cli_email AS rep_email,
                       s.sed_nombre AS sede_nombre
                FROM futbol_comprobantes fcm
                LEFT JOIN futbol_pagos p ON fcm.fcm_pago_id = p.fpg_pago_id
                LEFT JOIN alumnos a ON p.fpg_alumno_id = a.alu_alumno_id
                LEFT JOIN clientes c ON a.alu_representante_id = c.cli_cliente_id AND c.cli_tenant_id = a.alu_tenant_id
                LEFT JOIN instalaciones_sedes s ON p.fpg_sede_id = s.sed_sede_id
                WHERE fcm.fcm_comprobante_id = ? AND fcm.fcm_tenant_id = ?
            ");
            $stm->execute([$id, $this->tenantId]);
            $c = $stm->fetch(\PDO::FETCH_ASSOC);

            if (!$c) return $this->jsonResponse(['success' => false, 'message' => 'Comprobante no encontrado']);
            if ($c['fcm_estado'] !== 'EMITIDO') return $this->jsonResponse(['success' => false, 'message' => 'El comprobante no está activo']);

            // Descifrar PII
            if (!empty($c['alu_identificacion'])) $c['alu_identificacion'] = \DataProtection::decrypt($c['alu_identificacion']);
            if (!empty($c['rep_email']))           $c['rep_email']          = \DataProtection::decrypt($c['rep_email']);

            if (empty($c['rep_email'])) return $this->jsonResponse(['success' => false, 'message' => 'El representante no tiene email registrado']);

            // Generar PDF para adjuntar
            $pdfPath = null;
            $datosPdf = $this->obtenerDatosParaRecibo($id);
            if ($datosPdf) {
                require_once BASE_PATH . '/app/services/ReciboService.php';
                $svc     = new \App\Services\ReciboService();
                $pdfPath = $svc->generarPdf($datosPdf);
            }

            $datos = $this->obtenerDatosParaRecibo($id) ?? [];
            require_once BASE_PATH . '/app/services/MailService.php';
            $mail   = new \App\Services\MailService();
            $result = $mail->enviarComprobantePago($c['rep_email'], [
                'rep_nombre'     => $datos['rep_nombre'] ?? trim(($c['rep_nombres'] ?? '') . ' ' . ($c['rep_apellidos'] ?? '')),
                'alumno_nombre'  => $datos['alumno_nombre'] ?? trim(($c['alu_nombres'] ?? '') . ' ' . ($c['alu_apellidos'] ?? '')),
                'numero'         => $c['fcm_numero'],
                'tipo'           => $c['fcm_tipo'],
                'concepto'       => $c['fcm_concepto'],
                'fecha'          => $c['fcm_fecha_emision'],
                'total'          => $c['fcm_total'],
                'pago_metodo'    => $datos['metodo_pago'] ?? $c['pago_metodo'],
                'sede_nombre'    => $datos['sede_nombre'] ?? ($c['sede_nombre'] ?? ''),
                'empresa_nombre' => $datos['empresa_nombre'] ?? ($_SESSION['tenant_nombre'] ?? 'Escuela de Fútbol'),
            ], $pdfPath, $c['fcm_numero']);

            // Eliminar PDF temporal
            if ($pdfPath && file_exists($pdfPath)) @unlink($pdfPath);

            if ($result['exito']) {
                $this->db->prepare("UPDATE futbol_comprobantes SET fcm_enviado_email = 1 WHERE fcm_comprobante_id = ? AND fcm_tenant_id = ?")
                    ->execute([$id, $this->tenantId]);
                return $this->jsonResponse(['success' => true, 'message' => 'Comprobante enviado a ' . $c['rep_email']]);
            }
            return $this->jsonResponse(['success' => false, 'message' => 'Error al enviar: ' . $result['mensaje']]);

        } catch (\Exception $e) {
            $this->logError("Error enviando comprobante: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al enviar comprobante']);
        }
    }

    /**
     * Recopila todos los datos necesarios para ReciboService::generarHtml/Pdf
     * a partir de un fcm_comprobante_id.
     *
     * @return array|null
     */
    private function obtenerDatosParaRecibo(int $id): ?array {
        $stm = $this->db->prepare("
            SELECT fcm.*,
                   p.fpg_tipo AS pago_tipo, p.fpg_fecha AS pago_fecha,
                   p.fpg_metodo_pago AS pago_metodo, p.fpg_monto AS pago_monto,
                   p.fpg_beca_descuento AS pago_beca, p.fpg_descuento AS pago_descuento,
                   p.fpg_total AS pago_total, p.fpg_referencia AS pago_referencia,
                   p.fpg_mes_correspondiente AS pago_mes,
                   ab.fab_monto AS abono_monto, ab.fab_metodo_pago AS abono_metodo,
                   ab.fab_referencia AS abono_referencia,
                   a.alu_nombres, a.alu_apellidos, a.alu_identificacion,
                   c.cli_nombres AS rep_nombres, c.cli_apellidos AS rep_apellidos,
                   c.cli_identificacion AS rep_ci, c.cli_telefono AS rep_telefono,
                   c.cli_email AS rep_email, c.cli_direccion AS rep_direccion,
                   cat.fct_nombre AS categoria_nombre,
                   g.fgr_nombre AS grupo_nombre,
                   s.sed_sede_id AS sede_id,
                   s.sed_nombre AS sede_nombre, s.sed_email AS sede_email,
                   s.sed_telefono AS sede_telefono, s.sed_direccion AS sede_direccion,
                   arc_logo.arc_ruta_relativa AS logo_ruta,
                   arc_firma.arc_ruta_relativa AS firma_ruta,
                   t.ten_nombre_comercial AS empresa_nombre, t.ten_ruc AS empresa_ruc
            FROM futbol_comprobantes fcm
            LEFT JOIN futbol_pagos p    ON fcm.fcm_pago_id  = p.fpg_pago_id
            LEFT JOIN futbol_abonos ab  ON fcm.fcm_abono_id = ab.fab_abono_id
            LEFT JOIN alumnos a         ON p.fpg_alumno_id  = a.alu_alumno_id
            LEFT JOIN clientes c        ON a.alu_representante_id = c.cli_cliente_id AND c.cli_tenant_id = a.alu_tenant_id
            LEFT JOIN futbol_grupos g   ON p.fpg_grupo_id   = g.fgr_grupo_id
            LEFT JOIN futbol_categorias cat ON g.fgr_categoria_id = cat.fct_categoria_id
            LEFT JOIN instalaciones_sedes s ON p.fpg_sede_id = s.sed_sede_id
            LEFT JOIN core_archivos arc_logo
                   ON arc_logo.arc_entidad      = 'instalaciones_sedes'
                  AND arc_logo.arc_entidad_id   = s.sed_sede_id
                  AND arc_logo.arc_tenant_id    = fcm.fcm_tenant_id
                  AND arc_logo.arc_categoria    = 'logos'
                  AND arc_logo.arc_es_principal = 1
                  AND arc_logo.arc_estado       = 'activo'
            LEFT JOIN core_archivos arc_firma
                   ON arc_firma.arc_entidad      = 'instalaciones_sedes'
                  AND arc_firma.arc_entidad_id   = s.sed_sede_id
                  AND arc_firma.arc_tenant_id    = fcm.fcm_tenant_id
                  AND arc_firma.arc_categoria    = 'firmas'
                  AND arc_firma.arc_es_principal = 1
                  AND arc_firma.arc_estado       = 'activo'
            LEFT JOIN seguridad_tenants t ON fcm.fcm_tenant_id = t.ten_tenant_id
            WHERE fcm.fcm_comprobante_id = ? AND fcm.fcm_tenant_id = ?
            LIMIT 1
        ");
        $stm->execute([$id, $this->tenantId]);
        $r = $stm->fetch(\PDO::FETCH_ASSOC);
        if (!$r) return null;

        // Desencriptar PII
        if (!empty($r['alu_identificacion'])) $r['alu_identificacion'] = \DataProtection::decrypt($r['alu_identificacion']);
        if (!empty($r['rep_ci']))             $r['rep_ci']             = \DataProtection::decrypt($r['rep_ci']);
        if (!empty($r['rep_telefono']))       $r['rep_telefono']       = \DataProtection::decrypt($r['rep_telefono']);
        if (!empty($r['rep_email']))          $r['rep_email']          = \DataProtection::decrypt($r['rep_email']);
        if (!empty($r['rep_direccion']))      $r['rep_direccion']      = \DataProtection::decrypt($r['rep_direccion']);
        if (!empty($r['empresa_ruc']))        $r['empresa_ruc']        = \DataProtection::decrypt($r['empresa_ruc']);

        // Usar datos del JSON si los directos faltan (compatibilidad con comprobantes antiguos)
        $extra = json_decode($r['fcm_datos_json'] ?? '{}', true) ?: [];

        $esAbono   = !empty($r['fcm_abono_id']);
        $montoBase = (float)($r['pago_monto']  ?? $extra['monto_base'] ?? 0);
        $beca      = (float)($r['pago_beca']   ?? $extra['beca']       ?? 0);
        $descuento = (float)($r['pago_descuento'] ?? $extra['descuento'] ?? 0);
        $totalPago = (float)($r['pago_total']  ?? $extra['total_pago'] ?? $montoBase);
        $montoAbono = $esAbono ? (float)($r['abono_monto'] ?? $extra['monto_abono'] ?? 0) : 0;
        $saldo     = (float)($r['fcm_saldo'] ?? 0);
        $mesRef    = '';
        if (!empty($r['pago_mes'])) {
            $mesRef = $this->mesEnEspanol((int)substr($r['pago_mes'],5,2)) . ' ' . substr($r['pago_mes'],0,4);
        }

        // JOIN puede traer NULL si fpg_sede_id era NULL; buscar directamente por tenant
        $logoRuta  = $r['logo_ruta']  ?? null;
        $firmaRuta = $r['firma_ruta'] ?? null;
        if (!$logoRuta) {
            $stmL = $this->db->prepare("SELECT arc_ruta_relativa FROM core_archivos WHERE arc_tenant_id = ? AND arc_entidad = 'instalaciones_sedes' AND arc_categoria = 'logos' AND arc_es_principal = 1 AND arc_estado = 'activo' LIMIT 1");
            $stmL->execute([$this->tenantId]);
            $logoRuta = $stmL->fetchColumn() ?: null;
        }
        if (!$firmaRuta) {
            $stmF = $this->db->prepare("SELECT arc_ruta_relativa FROM core_archivos WHERE arc_tenant_id = ? AND arc_entidad = 'instalaciones_sedes' AND arc_categoria = 'firmas' AND arc_es_principal = 1 AND arc_estado = 'activo' LIMIT 1");
            $stmF->execute([$this->tenantId]);
            $firmaRuta = $stmF->fetchColumn() ?: null;
        }
        $logoPath  = $this->rutaRelativaAAbsoluta($logoRuta)  ?? ($extra['logo_path']  ?? null);
        $firmaPath = $this->rutaRelativaAAbsoluta($firmaRuta) ?? ($extra['firma_path'] ?? null);

        return [
            'numero'            => $r['fcm_numero'],
            'tipo'              => $r['fcm_tipo']  ?? 'RECIBO',
            'fecha'             => $r['fcm_fecha_emision'] ?? date('Y-m-d H:i:s'),
            'concepto'          => $r['fcm_concepto'],
            'anulado'           => $r['fcm_estado'] === 'ANULADO',
            'empresa_nombre'    => $r['empresa_nombre']  ?? $extra['empresa_nombre'] ?? ($_SESSION['tenant_nombre'] ?? ''),
            'empresa_ruc'       => $r['empresa_ruc']     ?? $extra['empresa_ruc']    ?? '',
            'sede_nombre'       => $r['sede_nombre']     ?? $extra['sede_nombre']    ?? '',
            'empresa_telefono'  => $r['sede_telefono']   ?? $extra['sede_telefono']  ?? '',
            'empresa_email'     => $r['sede_email']      ?? $extra['sede_email']     ?? '',
            'empresa_direccion' => $r['sede_direccion']  ?? $extra['sede_direccion'] ?? '',
            'alumno_nombre'     => trim(($r['alu_nombres'] ?? $extra['alumno_nombre'] ?? '') . ' ' . ($r['alu_apellidos'] ?? '')),
            'alumno_ci'         => $r['alu_identificacion'] ?? $extra['alumno_identificacion'] ?? '',
            'alumno_categoria'  => $r['categoria_nombre'] ?? $extra['alumno_categoria'] ?? '',
            'alumno_grupo'      => $r['grupo_nombre']    ?? $extra['alumno_grupo']    ?? '',
            'rep_nombre'        => trim(($r['rep_nombres'] ?? $extra['rep_nombre'] ?? '') . ' ' . ($r['rep_apellidos'] ?? '')),
            'rep_ci'            => $r['rep_ci']          ?? $extra['rep_ci']          ?? '',
            'rep_telefono'      => $r['rep_telefono']    ?? $extra['rep_telefono']    ?? '',
            'rep_email'         => $r['rep_email']       ?? $extra['rep_email']       ?? '',
            'rep_direccion'     => $r['rep_direccion']   ?? $extra['rep_direccion']   ?? '',
            'metodo_pago'       => $esAbono ? ($r['abono_metodo'] ?? $r['pago_metodo'] ?? '') : ($r['pago_metodo'] ?? ''),
            'referencia'        => $esAbono ? ($r['abono_referencia'] ?? $r['pago_referencia'] ?? '') : ($r['pago_referencia'] ?? ''),
            'mes_referencia'    => $mesRef,
            'monto'             => $montoBase,
            'beca'              => $beca,
            'descuento'         => $descuento,
            'total'             => $esAbono ? $montoAbono : $totalPago,
            'saldo'             => $saldo,
            'abono_id'          => $r['fcm_abono_id'] ?? null,
            'monto_abono'       => $montoAbono,
            'logo_path'         => $logoPath,
            'firma_path'        => $firmaPath,
            'qr_url'            => 'Recibo:' . $r['fcm_numero'] . ' Total:$' . number_format($esAbono ? $montoAbono : $totalPago, 2),
        ];
    }

    /**
     * Obtiene la ruta en disco de un archivo guardado en core_archivos.
     */
    private function mesEnEspanol(int $mes): string {
        $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                  'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        return $meses[$mes] ?? '';
    }

    private function rutaRelativaAAbsoluta(?string $ruta): ?string {
        if (empty($ruta)) return null;
        $absoluta = BASE_PATH . '/' . ltrim(str_replace('\\', '/', $ruta), '/');
        return file_exists($absoluta) ? $absoluta : null;
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
