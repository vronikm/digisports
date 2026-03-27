<?php
/**
 * DigiSports Fútbol — Controlador de Pagos
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class PagoController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    /**
     * Lista de alumnos con estado de pago para seleccionar a quién registrar
     */
    public function index() {
        try {
            $this->setupModule();
            $sedeId = $_SESSION['futbol_sede_id'] ?? null;

            $where  = 'a.alu_tenant_id = ? AND ffa.ffa_activo = 1';
            $params = [$this->tenantId];

            if ($sedeId) { $where .= ' AND a.alu_sede_id = ?'; $params[] = (int)$sedeId; }

            $q = trim($this->post('q') ?? $this->get('q') ?? '');
            if ($q !== '') {
                $like = "%{$q}%";
                $where .= ' AND (a.alu_nombres LIKE ? OR a.alu_apellidos LIKE ? OR a.alu_identificacion_hash = ?)';
                $params[] = $like; $params[] = $like; $params[] = \DataProtection::blindIndex($q);
            }

            $categoriaId = $this->post('categoria_id') ?? $this->get('categoria_id');
            if ($categoriaId) { $where .= ' AND ffa.ffa_categoria_id = ?'; $params[] = (int)$categoriaId; }

            $grupoId = $this->post('grupo_id') ?? $this->get('grupo_id');
            if ($grupoId) { $where .= ' AND fin.fin_grupo_id = ?'; $params[] = (int)$grupoId; }

            $estadoPago = $this->post('estado_pago') ?? $this->get('estado_pago') ?? '';

            $stm = $this->db->prepare("
                SELECT a.alu_alumno_id, a.alu_nombres, a.alu_apellidos, a.alu_identificacion,
                       a.alu_identificacion_hash, a.alu_estado,
                       fct.fct_nombre AS categoria_nombre, fct.fct_color AS categoria_color,
                       fg.fgr_nombre AS grupo_nombre, fg.fgr_color AS grupo_color,
                       fin.fin_inscripcion_id,
                       arc.arc_id AS foto_arc_id,
                       COALESCE(mora.tiene_mora, 0) AS tiene_mora,
                       COALESCE(beca.tiene_descuento, 0) AS tiene_descuento,
                       COALESCE(lic.en_licencia, 0) AS en_licencia
                FROM alumnos a
                LEFT JOIN futbol_ficha_alumno ffa
                       ON ffa.ffa_alumno_id = a.alu_alumno_id AND ffa.ffa_tenant_id = a.alu_tenant_id
                LEFT JOIN futbol_categorias fct ON ffa.ffa_categoria_id = fct.fct_categoria_id
                LEFT JOIN futbol_inscripciones fin
                       ON fin.fin_alumno_id = a.alu_alumno_id
                      AND fin.fin_tenant_id = a.alu_tenant_id AND fin.fin_estado = 'ACTIVA'
                LEFT JOIN futbol_grupos fg
                       ON fin.fin_grupo_id = fg.fgr_grupo_id AND fg.fgr_tenant_id = a.alu_tenant_id
                LEFT JOIN core_archivos arc
                       ON arc.arc_entidad = 'alumnos' AND arc.arc_entidad_id = a.alu_alumno_id
                      AND arc.arc_tenant_id = a.alu_tenant_id AND arc.arc_categoria = 'fotos'
                      AND arc.arc_es_principal = 1 AND arc.arc_estado = 'activo'
                LEFT JOIN (
                    SELECT fpg_alumno_id, 1 AS tiene_mora
                    FROM futbol_pagos
                    WHERE fpg_tenant_id = ? AND fpg_estado = 'VENCIDO'
                    GROUP BY fpg_alumno_id
                ) mora ON mora.fpg_alumno_id = a.alu_alumno_id
                LEFT JOIN (
                    SELECT fin_alumno_id, 1 AS en_licencia
                    FROM futbol_inactividades
                    WHERE fin_tenant_id = ?
                      AND fin_fecha_desde <= CURDATE()
                      AND (fin_fecha_hasta IS NULL OR fin_fecha_hasta >= CURDATE())
                    GROUP BY fin_alumno_id
                ) lic ON lic.fin_alumno_id = a.alu_alumno_id
                LEFT JOIN (
                    SELECT alumno_id, 1 AS tiene_descuento FROM (
                        SELECT fba_alumno_id AS alumno_id
                        FROM futbol_beca_asignaciones
                        WHERE fba_tenant_id = ? AND fba_estado = 'ACTIVA'
                        UNION
                        SELECT fin_alumno_id AS alumno_id
                        FROM futbol_inscripciones
                        WHERE fin_tenant_id = ? AND fin_beca_id IS NOT NULL AND fin_estado = 'ACTIVA'
                        UNION
                        SELECT fpg_alumno_id AS alumno_id
                        FROM futbol_pagos
                        WHERE fpg_tenant_id = ? AND (fpg_beca_descuento > 0 OR fpg_descuento > 0)
                    ) t GROUP BY alumno_id
                ) beca ON beca.alumno_id = a.alu_alumno_id
                WHERE {$where}
                ORDER BY a.alu_apellidos, a.alu_nombres
                LIMIT 300
            ");
            // Params order: 1 mora + 1 licencia + 3 beca tenantIds + main WHERE params
            $stm->execute(array_merge([$this->tenantId, $this->tenantId, $this->tenantId, $this->tenantId, $this->tenantId], $params));
            $alumnos = \DataProtection::decryptRows('alumnos', $stm->fetchAll(\PDO::FETCH_ASSOC));

            // Filtro de estado de pago aplicado en PHP (calculado por subqueries)
            // MORA: tiene mora Y no está en licencia activa
            if ($estadoPago === 'MORA')     $alumnos = array_values(array_filter($alumnos, fn($a) => $a['tiene_mora'] && !$a['en_licencia']));
            if ($estadoPago === 'AL_DIA')   $alumnos = array_values(array_filter($alumnos, fn($a) => !$a['tiene_mora'] && !$a['en_licencia']));
            if ($estadoPago === 'LICENCIA') $alumnos = array_values(array_filter($alumnos, fn($a) => $a['en_licencia']));

            // Totales generales para cards de resumen
            $totStm = $this->db->prepare("SELECT fpg_estado, SUM(fpg_total) AS monto FROM futbol_pagos WHERE fpg_tenant_id = ? GROUP BY fpg_estado");
            $totStm->execute([$this->tenantId]);
            $totales = [];
            foreach ($totStm->fetchAll(\PDO::FETCH_ASSOC) as $t) { $totales[$t['fpg_estado']] = (float)$t['monto']; }

            $stmCat = $this->db->prepare("SELECT fct_categoria_id, fct_nombre, fct_color FROM futbol_categorias WHERE fct_tenant_id = ? AND fct_activo = 1 ORDER BY fct_orden");
            $stmCat->execute([$this->tenantId]);

            $stmGrp = $this->db->prepare("SELECT fgr_grupo_id, fgr_nombre FROM futbol_grupos WHERE fgr_tenant_id = ? AND fgr_estado IN ('ABIERTO','EN_CURSO') ORDER BY fgr_nombre");
            $stmGrp->execute([$this->tenantId]);

            $stmSedes = $this->db->prepare("SELECT sed_sede_id, sed_nombre FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_nombre");
            $stmSedes->execute([$this->tenantId]);

            $this->viewData = array_merge($this->viewData, [
                'alumnos'      => $alumnos,
                'totales'      => $totales,
                'categorias'   => $stmCat->fetchAll(\PDO::FETCH_ASSOC),
                'grupos'       => $stmGrp->fetchAll(\PDO::FETCH_ASSOC),
                'sedes'        => $stmSedes->fetchAll(\PDO::FETCH_ASSOC),
                'sede_activa'  => $sedeId,
                'q'            => $q,
                'categoria_id' => $categoriaId,
                'grupo_id'     => $grupoId,
                'estado_pago'  => $estadoPago,
                'csrf_token'   => \Security::generateCsrfToken(),
                'title'        => 'Gestión de Pagos',
            ]);
            $this->renderModule('futbol/pagos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error en pagos index: " . $e->getMessage());
            $this->error('Error al cargar pagos');
        }
    }

    /**
     * Página de pagos de un alumno: info + historial + formulario de nuevo pago
     */
    public function alumno() {
        try {
            $this->setupModule();
            $alumnoId = (int)($this->get('id') ?? 0);
            if (!$alumnoId) {
                header('Location: ' . url('futbol', 'pago', 'index'));
                exit;
            }

            // Datos del alumno con inscripción activa, representante y foto
            $stmA = $this->db->prepare("
                SELECT a.alu_alumno_id, a.alu_nombres, a.alu_apellidos, a.alu_identificacion,
                       a.alu_identificacion_hash, a.alu_fecha_nacimiento, a.alu_estado, a.alu_sede_id,
                       fct.fct_nombre AS categoria_nombre, fct.fct_color AS categoria_color,
                       fg.fgr_grupo_id, fg.fgr_nombre AS grupo_nombre, fg.fgr_color AS grupo_color,
                       fin.fin_inscripcion_id, fin.fin_estado AS estado_inscripcion,
                       c.cli_cliente_id AS rep_cliente_id,
                       c.cli_nombres AS rep_nombres, c.cli_apellidos AS rep_apellidos,
                       c.cli_telefono AS rep_telefono, c.cli_email AS rep_email,
                       arc.arc_id AS foto_arc_id
                FROM alumnos a
                LEFT JOIN futbol_ficha_alumno ffa
                       ON ffa.ffa_alumno_id = a.alu_alumno_id AND ffa.ffa_tenant_id = a.alu_tenant_id AND ffa.ffa_activo = 1
                LEFT JOIN futbol_categorias fct ON ffa.ffa_categoria_id = fct.fct_categoria_id
                LEFT JOIN futbol_inscripciones fin
                       ON fin.fin_alumno_id = a.alu_alumno_id AND fin.fin_tenant_id = a.alu_tenant_id AND fin.fin_estado = 'ACTIVA'
                LEFT JOIN futbol_grupos fg ON fin.fin_grupo_id = fg.fgr_grupo_id
                LEFT JOIN clientes c ON a.alu_representante_id = c.cli_cliente_id AND c.cli_tenant_id = a.alu_tenant_id
                LEFT JOIN core_archivos arc ON arc.arc_entidad = 'alumnos'
                       AND arc.arc_entidad_id = a.alu_alumno_id
                       AND arc.arc_tenant_id  = a.alu_tenant_id
                       AND arc.arc_categoria  = 'fotos'
                       AND arc.arc_es_principal = 1
                       AND arc.arc_estado = 'activo'
                WHERE a.alu_alumno_id = ? AND a.alu_tenant_id = ?
                LIMIT 1
            ");
            $stmA->execute([$alumnoId, $this->tenantId]);
            $alumno = $stmA->fetch(\PDO::FETCH_ASSOC);
            if (!$alumno) { $this->error('Alumno no encontrado'); return; }

            $alumno = \DataProtection::decryptRows('alumnos', [$alumno])[0];
            if (!empty($alumno['rep_telefono'])) $alumno['rep_telefono'] = \DataProtection::decrypt($alumno['rep_telefono']);
            if (!empty($alumno['rep_email']))    $alumno['rep_email']    = \DataProtection::decrypt($alumno['rep_email']);

            // Historial de pagos del alumno
            $stmH = $this->db->prepare("
                SELECT p.*, g.fgr_nombre AS grupo_nombre,
                       fcm.fcm_comprobante_id, fcm.fcm_numero AS comprobante_numero,
                       fcm.fcm_enviado_email
                FROM futbol_pagos p
                LEFT JOIN futbol_grupos g ON p.fpg_grupo_id = g.fgr_grupo_id
                LEFT JOIN futbol_comprobantes fcm
                       ON fcm.fcm_pago_id = p.fpg_pago_id
                      AND fcm.fcm_tenant_id = p.fpg_tenant_id
                      AND fcm.fcm_estado = 'EMITIDO'
                WHERE p.fpg_alumno_id = ? AND p.fpg_tenant_id = ?
                ORDER BY p.fpg_fecha DESC
                LIMIT 60
            ");
            $stmH->execute([$alumnoId, $this->tenantId]);
            $historial = $stmH->fetchAll(\PDO::FETCH_ASSOC);

            // Imágenes de comprobante por pago (de core_archivos)
            $imagenesPagos = [];
            $abonosPorPago = [];
            if (!empty($historial)) {
                $pagoIds = array_column($historial, 'fpg_pago_id');
                $marks   = implode(',', array_fill(0, count($pagoIds), '?'));

                $stmImg = $this->db->prepare("
                    SELECT arc_entidad_id AS pago_id, arc_id
                    FROM core_archivos
                    WHERE arc_entidad = 'futbol_pagos' AND arc_entidad_id IN ({$marks})
                      AND arc_tenant_id = ? AND arc_estado = 'activo'
                ");
                $stmImg->execute([...$pagoIds, $this->tenantId]);
                foreach ($stmImg->fetchAll(\PDO::FETCH_ASSOC) as $img) {
                    $imagenesPagos[(int)$img['pago_id']] = (int)$img['arc_id'];
                }

                $stmAbo = $this->db->prepare("
                    SELECT ab.fab_abono_id, ab.fab_pago_id, ab.fab_monto, ab.fab_metodo_pago,
                           ab.fab_referencia, ab.fab_fecha, ab.fab_notas,
                           cmp.fcm_comprobante_id, cmp.fcm_numero AS comprobante_numero
                    FROM futbol_abonos ab
                    LEFT JOIN futbol_comprobantes cmp
                        ON cmp.fcm_abono_id = ab.fab_abono_id AND cmp.fcm_tenant_id = ab.fab_tenant_id AND cmp.fcm_estado = 'EMITIDO'
                    WHERE ab.fab_pago_id IN ({$marks}) AND ab.fab_tenant_id = ?
                    ORDER BY ab.fab_fecha ASC, ab.fab_abono_id ASC
                ");
                $stmAbo->execute([...$pagoIds, $this->tenantId]);
                foreach ($stmAbo->fetchAll(\PDO::FETCH_ASSOC) as $ab) {
                    $abonosPorPago[(int)$ab['fab_pago_id']][] = $ab;
                }
            }

            $totalPagado = $totalPendiente = 0.0;
            foreach ($historial as $h) {
                if ($h['fpg_estado'] === 'PAGADO')                         $totalPagado    += (float)$h['fpg_total'];
                if (in_array($h['fpg_estado'], ['PENDIENTE', 'VENCIDO'])) $totalPendiente += (float)$h['fpg_total'];
            }

            // Inactividades del alumno (historial completo + estado activo actual)
            $stmInac = $this->db->prepare("
                SELECT fin_id, fin_tipo, fin_fecha_desde, fin_fecha_hasta, fin_motivo, fin_created_at
                FROM futbol_inactividades
                WHERE fin_alumno_id = ? AND fin_tenant_id = ?
                ORDER BY fin_fecha_desde DESC
            ");
            $stmInac->execute([$alumnoId, $this->tenantId]);
            $inactividades = $stmInac->fetchAll(\PDO::FETCH_ASSOC);

            // ¿Tiene licencia activa hoy?
            $enLicenciaHoy = false;
            $licenciaActiva = null;
            foreach ($inactividades as $in) {
                $desde = $in['fin_fecha_desde'];
                $hasta = $in['fin_fecha_hasta'];
                if ($desde <= date('Y-m-d') && ($hasta === null || $hasta >= date('Y-m-d'))) {
                    $enLicenciaHoy = true;
                    $licenciaActiva = $in;
                    break;
                }
            }

            // Grupos activos para el formulario de pago
            $stmG = $this->db->prepare("SELECT fgr_grupo_id, fgr_nombre FROM futbol_grupos WHERE fgr_tenant_id = ? AND fgr_estado IN ('ABIERTO','EN_CURSO') ORDER BY fgr_nombre");
            $stmG->execute([$this->tenantId]);

            // Rubros activos del tenant para el selector de tipo de pago
            $stmR = $this->db->prepare("
                SELECT rub_id, rub_codigo, rub_nombre, rub_aplica_iva, rub_porcentaje_iva
                FROM facturacion_rubros
                WHERE rub_tenant_id = ? AND rub_estado = 'ACTIVO'
                ORDER BY rub_nombre ASC
            ");
            $stmR->execute([$this->tenantId]);
            $rubros = $stmR->fetchAll(\PDO::FETCH_ASSOC);

            // Mapear tipo sugerido (fpg_tipo enum) por keywords en nombre/código
            $tipoKeywords = [
                'MENSUALIDAD' => ['mensual', 'cuota', 'mens'],
                'MATRICULA'   => ['matric', 'inscr'],
                'UNIFORME'    => ['uniform', 'ropa', 'equip'],
                'TORNEO'      => ['torneo', 'compet', 'copa'],
            ];
            foreach ($rubros as &$r) {
                $r['tipo_sugerido'] = 'OTRO';
                $buscar = strtolower($r['rub_nombre'] . ' ' . $r['rub_codigo']);
                foreach ($tipoKeywords as $tipo => $kws) {
                    foreach ($kws as $kw) {
                        if (str_contains($buscar, $kw)) {
                            $r['tipo_sugerido'] = $tipo;
                            break 2;
                        }
                    }
                }
            }
            unset($r);

            // Montos de sede (mensualidad y matrícula) para auto-fill del formulario
            $sedeIdAlumno = $alumno['alu_sede_id'] ?? ($_SESSION['futbol_sede_id'] ?? null);
            $sedeMontos = ['sed_monto_mensualidad' => 0.00, 'sed_monto_matricula' => 0.00];
            if ($sedeIdAlumno) {
                $stmSM = $this->db->prepare("SELECT sed_monto_mensualidad, sed_monto_matricula FROM instalaciones_sedes WHERE sed_sede_id = ? AND sed_tenant_id = ? LIMIT 1");
                $stmSM->execute([(int)$sedeIdAlumno, $this->tenantId]);
                $sedeMontos = $stmSM->fetch(\PDO::FETCH_ASSOC) ?: $sedeMontos;
            }

            // Becas activas del alumno con rubro vinculado
            $stmBeca = $this->db->prepare("
                SELECT fb.fbe_nombre, fb.fbe_tipo, fb.fbe_valor,
                       r.rub_nombre AS rub_nombre
                FROM futbol_beca_asignaciones fba
                JOIN futbol_becas fb ON fba.fba_beca_id = fb.fbe_beca_id AND fb.fbe_tenant_id = fba.fba_tenant_id
                LEFT JOIN facturacion_rubros r ON fb.fbe_rubro_id = r.rub_id
                WHERE fba.fba_alumno_id = ? AND fba.fba_tenant_id = ? AND fba.fba_estado = 'ACTIVA'
                ORDER BY fba.fba_fecha_asignacion DESC
            ");
            $stmBeca->execute([$alumnoId, $this->tenantId]);
            $becasAlumno = $stmBeca->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData = array_merge($this->viewData, [
                'alumno'           => $alumno,
                'historial'        => $historial,
                'imagenes_pagos'   => $imagenesPagos,
                'abonos_por_pago'  => $abonosPorPago,
                'total_pagado'     => $totalPagado,
                'total_pendiente'  => $totalPendiente,
                'grupos'           => $stmG->fetchAll(\PDO::FETCH_ASSOC),
                'rubros'           => $rubros,
                'becas_alumno'     => $becasAlumno,
                'sede_montos'      => $sedeMontos,
                'inactividades'    => $inactividades,
                'en_licencia_hoy'  => $enLicenciaHoy,
                'licencia_activa'  => $licenciaActiva,
                'csrf_token'       => \Security::generateCsrfToken(),
                'title'            => 'Pagos — ' . trim($alumno['alu_nombres'] . ' ' . $alumno['alu_apellidos']),
            ]);
            $this->renderModule('futbol/pagos/alumno', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error en pago/alumno: " . $e->getMessage());
            $this->error('Error al cargar datos del alumno');
        }
    }

    /**
     * Crear nuevo pago — POST AJAX
     */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $alumnoId   = (int)($this->post('alumno_id')  ?? 0);
            $clienteId  = (int)($this->post('cliente_id') ?? 0) ?: null;
            $monto      = (float)($this->post('monto')    ?? 0);
            if (!$alumnoId || $monto <= 0) return $this->jsonResponse(['success' => false, 'message' => 'Alumno y monto válido son obligatorios']);

            // Grupo: tomar del POST o buscar en inscripción activa
            $grupoId = (int)($this->post('grupo_id') ?? 0);
            if (!$grupoId) {
                $stmGrp = $this->db->prepare("SELECT fin_grupo_id FROM futbol_inscripciones WHERE fin_alumno_id = ? AND fin_tenant_id = ? AND fin_estado = 'ACTIVA' LIMIT 1");
                $stmGrp->execute([$alumnoId, $this->tenantId]);
                $grupoId = (int)($stmGrp->fetchColumn() ?? 0);
            }

            $tipo        = $this->post('tipo') ?: 'MENSUALIDAD';
            $mes         = $this->post('mes_correspondiente') ?: null;
            $descuento   = (float)($this->post('descuento')      ?? 0);
            $becaDesc    = (float)($this->post('beca_descuento')  ?? 0);
            $recargoMora = (float)($this->post('recargo_mora')   ?? 0);
            // total_pagado = lo que el usuario ingresó en "Total a Pagar" (puede ser parcial)
            $totalPagado   = (float)($this->post('total_pagado') ?? 0);
            $totalCalculado = max(0, $monto - $descuento - $becaDesc + $recargoMora);
            $total          = $totalPagado > 0 ? $totalPagado : $totalCalculado;
            $saldo          = max(0.0, $totalCalculado - $total);

            // Generar concepto automáticamente si no viene
            $concepto = $this->post('concepto') ?: ($tipo . ($mes ? ' ' . $mes : ''));

            $sedeIdPago = (int)($this->post('sede_id') ?? 0) ?: ($_SESSION['futbol_sede_id'] ?? null);

            $stmIns = $this->db->prepare("
                INSERT INTO futbol_pagos (fpg_tenant_id, fpg_sede_id, fpg_alumno_id, fpg_grupo_id,
                    fpg_cliente_id, fpg_tipo, fpg_concepto, fpg_mes_correspondiente, fpg_monto,
                    fpg_descuento, fpg_beca_descuento, fpg_recargo_mora, fpg_total, fpg_saldo,
                    fpg_metodo_pago, fpg_referencia, fpg_fecha, fpg_estado, fpg_notas)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,CURDATE(),?,?)
            ");
            $stmIns->execute([
                $this->tenantId,
                $sedeIdPago,
                $alumnoId,
                $grupoId,
                $clienteId,
                $tipo,
                $concepto,
                $mes,
                $monto,
                $descuento,
                $becaDesc,
                $recargoMora,
                $total,
                $saldo,
                $this->post('metodo_pago') ?: 'EFECTIVO',
                $this->post('referencia')  ?: null,
                $this->post('estado')      ?: 'PAGADO',
                $this->post('notas')       ?: null,
            ]);
            $pagoId = (int)$this->db->lastInsertId();

            // Guardar imagen de comprobante si se adjuntó
            if (!empty($_FILES['imagen_pago']['name']) && $_FILES['imagen_pago']['error'] === UPLOAD_ERR_OK) {
                $this->guardarImagenPago($pagoId, $_FILES['imagen_pago']);
            }

            return $this->jsonResponse(['success' => true, 'message' => 'Pago registrado correctamente']);

        } catch (\Exception $e) {
            $this->logError("Error creando pago: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar pago']);
        }
    }

    /**
     * Editar pago existente — POST AJAX
     */
    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            // No permitir editar si ya tiene factura generada
            $stmChk = $this->db->prepare("SELECT fpg_factura_id FROM futbol_pagos WHERE fpg_pago_id = ? AND fpg_tenant_id = ?");
            $stmChk->execute([$id, $this->tenantId]);
            $chk = $stmChk->fetch(\PDO::FETCH_ASSOC);
            if (!$chk) return $this->jsonResponse(['success' => false, 'message' => 'Pago no encontrado']);
            if (!empty($chk['fpg_factura_id'])) return $this->jsonResponse(['success' => false, 'message' => 'No se puede editar un pago que tiene factura generada']);

            $monto          = (float)($this->post('monto')          ?? 0);
            $descuento      = (float)($this->post('descuento')      ?? 0);
            $becaDesc       = (float)($this->post('beca_descuento')  ?? 0);
            $recargoMora    = (float)($this->post('recargo_mora')   ?? 0);
            $totalPagado    = (float)($this->post('total_pagado')   ?? 0);
            $totalCalculado = max(0, $monto - $descuento - $becaDesc + $recargoMora);
            $total          = $totalPagado > 0 ? $totalPagado : $totalCalculado;
            $saldo          = max(0.0, $totalCalculado - $total);

            $this->db->prepare("
                UPDATE futbol_pagos
                SET fpg_tipo=?, fpg_mes_correspondiente=?, fpg_monto=?,
                    fpg_descuento=?, fpg_beca_descuento=?, fpg_recargo_mora=?,
                    fpg_total=?, fpg_saldo=?,
                    fpg_metodo_pago=?, fpg_referencia=?, fpg_estado=?, fpg_notas=?
                WHERE fpg_pago_id=? AND fpg_tenant_id=?
            ")->execute([
                $this->post('tipo') ?: 'MENSUALIDAD',
                $this->post('mes_correspondiente') ?: null,
                $monto, $descuento, $becaDesc, $recargoMora,
                $total, $saldo,
                $this->post('metodo_pago') ?: 'EFECTIVO',
                $this->post('referencia')  ?: null,
                $this->post('estado')      ?: 'PENDIENTE',
                $this->post('notas')       ?: null,
                $id, $this->tenantId,
            ]);

            // Guardar imagen de comprobante si se adjuntó
            if (!empty($_FILES['imagen_pago']['name']) && $_FILES['imagen_pago']['error'] === UPLOAD_ERR_OK) {
                $this->guardarImagenPago($id, $_FILES['imagen_pago']);
            }

            return $this->jsonResponse(['success' => true, 'message' => 'Pago actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando pago: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    /**
     * Marcar pago como PAGADO — POST AJAX (acción rápida)
     */
    public function cobrar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("
                UPDATE futbol_pagos SET fpg_estado = 'PAGADO'
                WHERE fpg_pago_id = ? AND fpg_tenant_id = ? AND fpg_estado IN ('PENDIENTE','VENCIDO')
            ")->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Pago marcado como cobrado']);

        } catch (\Exception $e) {
            $this->logError("Error en cobrar: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    /**
     * Anular pago
     */
    public function anular() {
        try {
            $id = (int)($this->get('id') ?? $this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            // No permitir anular si ya tiene factura generada
            $stmChk = $this->db->prepare("SELECT fpg_factura_id FROM futbol_pagos WHERE fpg_pago_id = ? AND fpg_tenant_id = ?");
            $stmChk->execute([$id, $this->tenantId]);
            $chk = $stmChk->fetch(\PDO::FETCH_ASSOC);
            if (!$chk) return $this->jsonResponse(['success' => false, 'message' => 'Pago no encontrado']);
            if (!empty($chk['fpg_factura_id'])) return $this->jsonResponse(['success' => false, 'message' => 'No se puede anular un pago que tiene factura generada']);

            $this->db->prepare("UPDATE futbol_pagos SET fpg_estado = 'ANULADO' WHERE fpg_pago_id = ? AND fpg_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Pago anulado']);

        } catch (\Exception $e) {
            $this->logError("Error anulando pago: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al anular']);
        }
    }

    /** AJAX: Buscar inscripciones activas */
    public function buscarInscripciones() {
        try {
            $q = trim($this->get('q') ?? '');
            $stm = $this->db->prepare("
                SELECT i.fin_inscripcion_id, a.alu_alumno_id, a.alu_nombres, a.alu_apellidos, g.fgr_nombre AS grupo
                FROM futbol_inscripciones i
                JOIN alumnos a ON i.fin_alumno_id = a.alu_alumno_id
                JOIN futbol_grupos g ON i.fin_grupo_id = g.fgr_grupo_id
                WHERE i.fin_estado = 'ACTIVA' AND i.fin_tenant_id = ?
                AND (a.alu_nombres LIKE ? OR a.alu_apellidos LIKE ?)
                LIMIT 20
            ");
            $like = "%{$q}%";
            $stm->execute([$this->tenantId, $like, $like]);
            return $this->jsonResponse(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_ASSOC)]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'data' => []]);
        }
    }

    /**
     * Sirve el archivo de imagen de un comprobante de pago (valida tenant)
     */
    public function verComprobante(): void {
        $arcId = (int)($this->get('arc_id') ?? 0);
        if (!$arcId) { http_response_code(404); exit; }

        $stm = $this->db->prepare("
            SELECT arc_ruta_relativa, arc_mime_type, arc_nombre_original
            FROM core_archivos
            WHERE arc_id = ? AND arc_tenant_id = ? AND arc_estado = 'activo' AND arc_entidad = 'futbol_pagos'
            LIMIT 1
        ");
        $stm->execute([$arcId, $this->tenantId]);
        $arc = $stm->fetch(\PDO::FETCH_ASSOC);
        if (!$arc) { http_response_code(404); exit; }

        $path = BASE_PATH . '/storage/' . ltrim($arc['arc_ruta_relativa'], '/');
        if (!is_file($path)) { http_response_code(404); exit; }

        header('Content-Type: '  . $arc['arc_mime_type']);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, max-age=3600');
        readfile($path);
        exit;
    }

    /**
     * Guarda la imagen de comprobante de pago en disco y en core_archivos
     */
    private function guardarImagenPago(int $pagoId, array $file): void {
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowedMime, true)) return;

        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uuid = bin2hex(random_bytes(16));
        $storedName = $uuid . '.' . $ext;
        $relPath    = 'tenants/' . $this->tenantId . '/pagos/comprobantes/' . $storedName;
        $absDir     = BASE_PATH . '/storage/' . dirname($relPath);

        if (!is_dir($absDir)) mkdir($absDir, 0755, true);
        if (!move_uploaded_file($file['tmp_name'], $absDir . '/' . $storedName)) return;

        $this->db->prepare("
            INSERT INTO core_archivos (arc_tenant_id, arc_entidad, arc_entidad_id, arc_categoria,
                arc_nombre_original, arc_nombre_almacenado, arc_ruta_relativa,
                arc_mime_type, arc_extension, arc_tamanio_bytes, arc_es_principal,
                arc_estado, arc_subido_por)
            VALUES (?,?,?,?,?,?,?,?,?,?,1,'activo',?)
        ")->execute([
            $this->tenantId,
            'futbol_pagos',
            $pagoId,
            'comprobantes',
            $file['name'],
            $storedName,
            $relPath,
            $mime,
            $ext,
            $file['size'],
            $_SESSION['user_id'] ?? 0,
        ]);
    }

    /**
     * Registrar o finalizar una inactividad de alumno — POST AJAX
     * accion=crear  → INSERT nueva inactividad
     * accion=finalizar → UPDATE fin_fecha_hasta = hoy
     * accion=eliminar  → DELETE (solo si la inactividad es del tenant)
     */
    public function inactividad(): void {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonResponse(['success' => false, 'message' => 'POST requerido']); return; }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) { $this->jsonResponse(['success' => false, 'message' => 'Token inválido']); return; }

            $accion    = $this->post('accion') ?: 'crear';
            $alumnoId  = (int)($this->post('alumno_id') ?? 0);

            if ($accion === 'crear') {
                if (!$alumnoId) { $this->jsonResponse(['success' => false, 'message' => 'Alumno requerido']); return; }

                $desde = $this->post('fecha_desde') ?: date('Y-m-d');
                $hasta = $this->post('fecha_hasta') ?: null;
                if ($hasta === '') $hasta = null;

                // Validar que no haya inactividad activa solapada
                $stmChk = $this->db->prepare("
                    SELECT COUNT(*) FROM futbol_inactividades
                    WHERE fin_alumno_id = ? AND fin_tenant_id = ?
                      AND fin_fecha_desde <= ?
                      AND (fin_fecha_hasta IS NULL OR fin_fecha_hasta >= ?)
                ");
                $stmChk->execute([$alumnoId, $this->tenantId, $hasta ?? '9999-12-31', $desde]);
                if ((int)$stmChk->fetchColumn() > 0) {
                    $this->jsonResponse(['success' => false, 'message' => 'Ya existe una inactividad activa en ese período']); return;
                }

                $this->db->prepare("
                    INSERT INTO futbol_inactividades
                        (fin_tenant_id, fin_alumno_id, fin_tipo, fin_fecha_desde, fin_fecha_hasta, fin_motivo, fin_usuario_id)
                    VALUES (?,?,?,?,?,?,?)
                ")->execute([
                    $this->tenantId,
                    $alumnoId,
                    $this->post('tipo') ?: 'OTRO',
                    $desde,
                    $hasta,
                    $this->post('motivo') ?: null,
                    $_SESSION['user_id'] ?? null,
                ]);
                $this->jsonResponse(['success' => true, 'message' => 'Inactividad registrada correctamente']);

            } elseif ($accion === 'finalizar') {
                $id = (int)($this->post('id') ?? 0);
                if (!$id) { $this->jsonResponse(['success' => false, 'message' => 'ID requerido']); return; }
                $this->db->prepare("
                    UPDATE futbol_inactividades SET fin_fecha_hasta = CURDATE()
                    WHERE fin_id = ? AND fin_tenant_id = ?
                ")->execute([$id, $this->tenantId]);
                $this->jsonResponse(['success' => true, 'message' => 'Inactividad finalizada']);

            } elseif ($accion === 'eliminar') {
                $id = (int)($this->post('id') ?? 0);
                if (!$id) { $this->jsonResponse(['success' => false, 'message' => 'ID requerido']); return; }
                $this->db->prepare("
                    DELETE FROM futbol_inactividades WHERE fin_id = ? AND fin_tenant_id = ?
                ")->execute([$id, $this->tenantId]);
                $this->jsonResponse(['success' => true, 'message' => 'Inactividad eliminada']);

            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Acción no reconocida']);
            }

        } catch (\Exception $e) {
            $this->logError("Error en inactividad: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error al procesar inactividad']);
        }
    }

    /**
     * Registrar un abono parcial sobre un pago con saldo pendiente — POST AJAX
     */
    public function abono(): void {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonResponse(['success' => false, 'message' => 'POST requerido']); return; }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) { $this->jsonResponse(['success' => false, 'message' => 'Token inválido']); return; }

            $pagoId = (int)($this->post('pago_id') ?? 0);
            $monto  = (float)($this->post('monto')   ?? 0);
            if (!$pagoId || $monto <= 0) { $this->jsonResponse(['success' => false, 'message' => 'Datos inválidos']); return; }

            // Verificar pago del tenant con saldo
            $stmPago = $this->db->prepare("
                SELECT fpg_alumno_id, fpg_saldo, fpg_total, fpg_estado
                FROM futbol_pagos WHERE fpg_pago_id = ? AND fpg_tenant_id = ? LIMIT 1
            ");
            $stmPago->execute([$pagoId, $this->tenantId]);
            $pago = $stmPago->fetch(\PDO::FETCH_ASSOC);
            if (!$pago) { $this->jsonResponse(['success' => false, 'message' => 'Pago no encontrado']); return; }
            if ($pago['fpg_estado'] === 'ANULADO') { $this->jsonResponse(['success' => false, 'message' => 'No se puede abonar a un pago anulado']); return; }

            $saldoActual = (float)$pago['fpg_saldo'];
            if ($saldoActual <= 0.004) { $this->jsonResponse(['success' => false, 'message' => 'Este pago no tiene saldo pendiente']); return; }

            // Limitar al saldo disponible
            $montoAbono  = round(min($monto, $saldoActual), 2);
            $nuevoTotal  = round((float)$pago['fpg_total'] + $montoAbono, 2);
            $nuevoSaldo  = round(max(0.0, $saldoActual - $montoAbono), 2);
            $nuevoEstado = $nuevoSaldo < 0.005 ? 'PAGADO' : 'PENDIENTE';

            $this->db->beginTransaction();
            try {
                // 1. Insertar en futbol_abonos
                $this->db->prepare("
                    INSERT INTO futbol_abonos
                        (fab_tenant_id, fab_pago_id, fab_alumno_id, fab_monto,
                         fab_metodo_pago, fab_referencia, fab_fecha, fab_notas, fab_usuario_id)
                    VALUES (?,?,?,?,?,?,CURDATE(),?,?)
                ")->execute([
                    $this->tenantId,
                    $pagoId,
                    (int)$pago['fpg_alumno_id'],
                    $montoAbono,
                    $this->post('metodo_pago') ?: 'EFECTIVO',
                    $this->post('referencia')  ?: null,
                    $this->post('notas')       ?: null,
                    $_SESSION['user_id'] ?? null,
                ]);
                $abonoId = (int)$this->db->lastInsertId();

                // 2. Actualizar fpg_total / fpg_saldo / fpg_estado
                $this->db->prepare("
                    UPDATE futbol_pagos
                    SET fpg_total = ?, fpg_saldo = ?, fpg_estado = ?
                    WHERE fpg_pago_id = ? AND fpg_tenant_id = ?
                ")->execute([$nuevoTotal, $nuevoSaldo, $nuevoEstado, $pagoId, $this->tenantId]);

                // 3. Imagen de comprobante del abono
                if (!empty($_FILES['imagen_pago']['name']) && $_FILES['imagen_pago']['error'] === UPLOAD_ERR_OK) {
                    $this->guardarImagenAbono($abonoId, $_FILES['imagen_pago']);
                }

                $this->db->commit();
                $this->jsonResponse([
                    'success'      => true,
                    'message'      => 'Abono registrado correctamente',
                    'abono_id'     => $abonoId,
                    'monto_abono'  => $montoAbono,
                    'nuevo_total'  => $nuevoTotal,
                    'nuevo_saldo'  => $nuevoSaldo,
                    'nuevo_estado' => $nuevoEstado,
                ]);
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            $this->logError("Error en abono: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error al registrar abono']);
        }
    }

    /**
     * Guarda imagen de comprobante de un abono en core_archivos
     */
    private function guardarImagenAbono(int $abonoId, array $file): void {
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowedMime, true)) return;

        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uuid = bin2hex(random_bytes(16));
        $storedName = $uuid . '.' . $ext;
        $relPath    = 'tenants/' . $this->tenantId . '/pagos/abonos/' . $storedName;
        $absDir     = BASE_PATH . '/storage/' . dirname($relPath);

        if (!is_dir($absDir)) mkdir($absDir, 0755, true);
        if (!move_uploaded_file($file['tmp_name'], $absDir . '/' . $storedName)) return;

        $this->db->prepare("
            INSERT INTO core_archivos (arc_tenant_id, arc_entidad, arc_entidad_id, arc_categoria,
                arc_nombre_original, arc_nombre_almacenado, arc_ruta_relativa,
                arc_mime_type, arc_extension, arc_tamanio_bytes, arc_es_principal,
                arc_estado, arc_subido_por)
            VALUES (?,?,?,?,?,?,?,?,?,?,1,'activo',?)
        ")->execute([
            $this->tenantId, 'futbol_abonos', $abonoId, 'comprobantes',
            $file['name'], $storedName, $relPath, $mime, $ext, $file['size'],
            $_SESSION['user_id'] ?? 0,
        ]);
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
