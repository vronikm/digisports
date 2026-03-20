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
                       COALESCE(mora.tiene_mora, 0) AS tiene_mora,
                       COALESCE(beca.tiene_descuento, 0) AS tiene_descuento
                FROM alumnos a
                LEFT JOIN futbol_ficha_alumno ffa
                       ON ffa.ffa_alumno_id = a.alu_alumno_id AND ffa.ffa_tenant_id = a.alu_tenant_id
                LEFT JOIN futbol_categorias fct ON ffa.ffa_categoria_id = fct.fct_categoria_id
                LEFT JOIN futbol_inscripciones fin
                       ON fin.fin_alumno_id = a.alu_alumno_id
                      AND fin.fin_tenant_id = a.alu_tenant_id AND fin.fin_estado = 'ACTIVA'
                LEFT JOIN futbol_grupos fg
                       ON fin.fin_grupo_id = fg.fgr_grupo_id AND fg.fgr_tenant_id = a.alu_tenant_id
                LEFT JOIN (
                    SELECT fpg_alumno_id, 1 AS tiene_mora
                    FROM futbol_pagos
                    WHERE fpg_tenant_id = ? AND fpg_estado = 'VENCIDO'
                    GROUP BY fpg_alumno_id
                ) mora ON mora.fpg_alumno_id = a.alu_alumno_id
                LEFT JOIN (
                    SELECT fpg_alumno_id, 1 AS tiene_descuento
                    FROM futbol_pagos
                    WHERE fpg_tenant_id = ? AND (fpg_beca_descuento > 0 OR fpg_descuento > 0)
                    GROUP BY fpg_alumno_id
                ) beca ON beca.fpg_alumno_id = a.alu_alumno_id
                WHERE {$where}
                ORDER BY a.alu_apellidos, a.alu_nombres
                LIMIT 300
            ");
            // Params order: 2 subquery tenantIds + main WHERE params
            $stm->execute(array_merge([$this->tenantId, $this->tenantId], $params));
            $alumnos = \DataProtection::decryptRows('alumnos', $stm->fetchAll(\PDO::FETCH_ASSOC));

            // Filtro de estado de pago aplicado en PHP (calculado por subqueries)
            if ($estadoPago === 'MORA')   $alumnos = array_values(array_filter($alumnos, fn($a) => $a['tiene_mora']));
            if ($estadoPago === 'AL_DIA') $alumnos = array_values(array_filter($alumnos, fn($a) => !$a['tiene_mora']));

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
                       a.alu_identificacion_hash, a.alu_fecha_nacimiento, a.alu_estado,
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
                SELECT p.*, g.fgr_nombre AS grupo_nombre
                FROM futbol_pagos p
                LEFT JOIN futbol_grupos g ON p.fpg_grupo_id = g.fgr_grupo_id
                WHERE p.fpg_alumno_id = ? AND p.fpg_tenant_id = ?
                ORDER BY p.fpg_fecha DESC
                LIMIT 60
            ");
            $stmH->execute([$alumnoId, $this->tenantId]);
            $historial = $stmH->fetchAll(\PDO::FETCH_ASSOC);

            $totalPagado = $totalPendiente = 0.0;
            foreach ($historial as $h) {
                if ($h['fpg_estado'] === 'PAGADO')                         $totalPagado    += (float)$h['fpg_total'];
                if (in_array($h['fpg_estado'], ['PENDIENTE', 'VENCIDO'])) $totalPendiente += (float)$h['fpg_total'];
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
                'alumno'          => $alumno,
                'historial'       => $historial,
                'total_pagado'    => $totalPagado,
                'total_pendiente' => $totalPendiente,
                'grupos'          => $stmG->fetchAll(\PDO::FETCH_ASSOC),
                'rubros'          => $rubros,
                'becas_alumno'    => $becasAlumno,
                'csrf_token'      => \Security::generateCsrfToken(),
                'title'           => 'Pagos — ' . trim($alumno['alu_nombres'] . ' ' . $alumno['alu_apellidos']),
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
            $grupoId    = (int)($this->post('grupo_id')   ?? 0);
            $clienteId  = (int)($this->post('cliente_id') ?? 0) ?: null;
            $monto      = (float)($this->post('monto')    ?? 0);
            if (!$alumnoId || !$grupoId || $monto <= 0) return $this->jsonResponse(['success' => false, 'message' => 'Alumno, grupo y monto válido son obligatorios']);

            $tipo        = $this->post('tipo') ?: 'MENSUALIDAD';
            $mes         = $this->post('mes_correspondiente') ?: null;
            $descuento   = (float)($this->post('descuento')     ?? 0);
            $becaDesc    = (float)($this->post('beca_descuento') ?? 0);
            $recargoMora = (float)($this->post('recargo_mora')  ?? 0);
            $total       = $monto - $descuento - $becaDesc + $recargoMora;

            // Generar concepto automáticamente si no viene
            $concepto = $this->post('concepto') ?: ($tipo . ($mes ? ' ' . $mes : ''));

            $sedeIdPago = (int)($this->post('sede_id') ?? 0) ?: ($_SESSION['futbol_sede_id'] ?? null);

            $this->db->prepare("
                INSERT INTO futbol_pagos (fpg_tenant_id, fpg_sede_id, fpg_alumno_id, fpg_grupo_id,
                    fpg_cliente_id, fpg_tipo, fpg_concepto, fpg_mes_correspondiente, fpg_monto,
                    fpg_descuento, fpg_beca_descuento, fpg_recargo_mora, fpg_total,
                    fpg_metodo_pago, fpg_referencia, fpg_fecha, fpg_estado, fpg_notas)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,CURDATE(),?,?)
            ")->execute([
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
                $this->post('metodo_pago') ?: 'EFECTIVO',
                $this->post('referencia')  ?: null,
                $this->post('estado')      ?: 'PAGADO',
                $this->post('notas')       ?: null,
            ]);

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

            $monto       = (float)($this->post('monto')        ?? 0);
            $descuento   = (float)($this->post('descuento')    ?? 0);
            $becaDesc    = (float)($this->post('beca_descuento') ?? 0);
            $recargoMora = (float)($this->post('recargo_mora') ?? 0);
            $total       = $monto - $descuento - $becaDesc + $recargoMora;

            $this->db->prepare("
                UPDATE futbol_pagos
                SET fpg_tipo=?, fpg_mes_correspondiente=?, fpg_monto=?,
                    fpg_descuento=?, fpg_beca_descuento=?, fpg_recargo_mora=?, fpg_total=?,
                    fpg_metodo_pago=?, fpg_referencia=?, fpg_estado=?, fpg_notas=?
                WHERE fpg_pago_id=? AND fpg_tenant_id=?
            ")->execute([
                $this->post('tipo') ?: 'MENSUALIDAD',
                $this->post('mes_correspondiente') ?: null,
                $monto, $descuento, $becaDesc, $recargoMora, $total,
                $this->post('metodo_pago') ?: 'EFECTIVO',
                $this->post('referencia')  ?: null,
                $this->post('estado')      ?: 'PENDIENTE',
                $this->post('notas')       ?: null,
                $id, $this->tenantId,
            ]);

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

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
