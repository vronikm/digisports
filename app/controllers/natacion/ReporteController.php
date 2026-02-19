<?php
/**
 * DigiSports Natación — Controlador de Reportes
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class ReporteController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'NATACION'; }

    public function index() {
        try {
            $this->setupModule();
            $this->viewData['title'] = 'Reportes de Natación';
            $sedeId = $_SESSION['natacion_sede_id'] ?? null;

            // Sedes para filtro
            $sedStm = $this->db->prepare("SELECT sed_sede_id, sed_nombre FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_nombre");
            $sedStm->execute([$this->tenantId]);
            $this->viewData['sedes'] = $sedStm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['sede_activa'] = $sedeId;

            // KPIs generales con filtro sede
            $kpis = [];
            $sedeAlum = $sedeId ? ' AND alu_sede_id = ?' : '';
            $pAlum = $sedeId ? [$this->tenantId, (int)$sedeId] : [$this->tenantId];
            $stm = $this->db->prepare("SELECT COUNT(*) FROM alumnos WHERE alu_tenant_id = ? AND alu_estado = 'ACTIVO'{$sedeAlum}");
            $stm->execute($pAlum); $kpis['total_alumnos'] = (int)$stm->fetchColumn();

            $sedeGrp = $sedeId ? ' AND g.ngr_sede_id = ?' : '';
            $pIns = $sedeId ? [$this->tenantId, (int)$sedeId] : [$this->tenantId];
            $stm = $this->db->prepare("SELECT COUNT(*) FROM natacion_inscripciones i JOIN natacion_grupos g ON i.nis_grupo_id = g.ngr_grupo_id WHERE g.ngr_tenant_id = ? AND i.nis_estado = 'ACTIVA'{$sedeGrp}");
            $stm->execute($pIns); $kpis['inscripciones_activas'] = (int)$stm->fetchColumn();

            $sedePago = $sedeId ? ' AND npg_sede_id = ?' : '';
            $pPag = $sedeId ? [$this->tenantId, (int)$sedeId] : [$this->tenantId];
            $stm = $this->db->prepare("SELECT COALESCE(SUM(npg_monto),0) FROM natacion_pagos WHERE npg_tenant_id = ? AND npg_estado = 'PAGADO' AND MONTH(npg_fecha) = MONTH(CURDATE()) AND YEAR(npg_fecha) = YEAR(CURDATE()){$sedePago}");
            $stm->execute($pPag); $kpis['ingresos_mes'] = (float)$stm->fetchColumn();

            $sedeGrp2 = $sedeId ? ' AND ngr_sede_id = ?' : '';
            $pGrp = $sedeId ? [$this->tenantId, (int)$sedeId] : [$this->tenantId];
            $stm = $this->db->prepare("SELECT COUNT(*) FROM natacion_grupos WHERE ngr_tenant_id = ? AND ngr_estado IN ('ABIERTO','EN_CURSO'){$sedeGrp2}");
            $stm->execute($pGrp); $kpis['grupos_activos'] = (int)$stm->fetchColumn();

            // KPI egresos del mes
            $sedeEgr = $sedeId ? ' AND neg_sede_id = ?' : '';
            $pEgr = $sedeId ? [$this->tenantId, (int)$sedeId] : [$this->tenantId];
            $stm = $this->db->prepare("SELECT COALESCE(SUM(neg_monto),0) FROM natacion_egresos WHERE neg_tenant_id = ? AND neg_estado != 'ANULADO' AND MONTH(neg_fecha) = MONTH(CURDATE()) AND YEAR(neg_fecha) = YEAR(CURDATE()){$sedeEgr}");
            $stm->execute($pEgr); $kpis['egresos_mes'] = (float)$stm->fetchColumn();

            $this->viewData['kpis'] = $kpis;

            // Periodos para filtros
            $perStm = $this->db->prepare("SELECT npe_periodo_id, npe_nombre FROM natacion_periodos WHERE npe_tenant_id = ? ORDER BY npe_fecha_inicio DESC");
            $perStm->execute([$this->tenantId]);
            $this->viewData['periodos'] = $perStm->fetchAll(\PDO::FETCH_ASSOC);

            $this->renderModule('natacion/reportes/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error en reportes: " . $e->getMessage());
            $this->error('Error al cargar reportes');
        }
    }

    /** AJAX: Reporte de asistencia por grupo/periodo */
    public function asistencia() {
        try {
            $grupoId   = (int)($this->get('grupo_id') ?? 0);
            $fechaDesde = $this->get('fecha_desde') ?? date('Y-m-01');
            $fechaHasta = $this->get('fecha_hasta') ?? date('Y-m-d');
            $sedeId = $_SESSION['natacion_sede_id'] ?? null;

            $where  = 'g.ngr_tenant_id = ?';
            $params = [$this->tenantId];
            if ($sedeId) { $where .= ' AND g.ngr_sede_id = ?'; $params[] = (int)$sedeId; }
            if ($grupoId) { $where .= ' AND a.nas_grupo_id = ?'; $params[] = $grupoId; }
            $where .= ' AND a.nas_fecha BETWEEN ? AND ?';
            $params[] = $fechaDesde; $params[] = $fechaHasta;

            $stm = $this->db->prepare("
                SELECT al.alu_nombres, al.alu_apellidos,
                       SUM(a.nas_estado = 'PRESENTE') AS presentes,
                       SUM(a.nas_estado = 'AUSENTE') AS ausentes,
                       SUM(a.nas_estado = 'TARDANZA') AS tardanzas,
                       SUM(a.nas_estado = 'JUSTIFICADO') AS justificados,
                       COUNT(*) AS total_clases
                FROM natacion_asistencia a
                JOIN natacion_grupos g ON a.nas_grupo_id = g.ngr_grupo_id
                JOIN alumnos al ON a.nas_alumno_id = al.alu_alumno_id
                WHERE {$where}
                GROUP BY al.alu_alumno_id
                ORDER BY al.alu_apellidos, al.alu_nombres
            ");
            $stm->execute($params);
            return $this->jsonResponse(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_ASSOC)]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'data' => []]);
        }
    }

    /** AJAX: Reporte de evaluaciones por nivel */
    public function evaluaciones() {
        try {
            $nivelId = (int)($this->get('nivel_id') ?? 0);
            $sedeId = $_SESSION['natacion_sede_id'] ?? null;
            $where   = 'n.nnv_tenant_id = ?';
            $params  = [$this->tenantId];
            if ($sedeId) { $where .= ' AND al.alu_sede_id = ?'; $params[] = (int)$sedeId; }
            if ($nivelId) { $where .= ' AND n.nnv_nivel_id = ?'; $params[] = $nivelId; }

            $stm = $this->db->prepare("
                SELECT al.alu_nombres, al.alu_apellidos, n.nnv_nombre AS nivel,
                       h.nha_nombre AS habilidad,
                       e.nev_calificacion, e.nev_aprobado, e.nev_fecha
                FROM natacion_evaluaciones e
                JOIN alumnos al ON e.nev_alumno_id = al.alu_alumno_id
                JOIN natacion_habilidades h ON e.nev_habilidad_id = h.nha_habilidad_id
                JOIN natacion_niveles n ON h.nha_nivel_id = n.nnv_nivel_id
                WHERE {$where}
                ORDER BY al.alu_apellidos, al.alu_nombres, n.nnv_orden, h.nha_orden
            ");
            $stm->execute($params);
            return $this->jsonResponse(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_ASSOC)]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'data' => []]);
        }
    }

    /** AJAX: Ingresos por mes */
    public function ingresos() {
        try {
            $year = (int)($this->get('year') ?? date('Y'));
            $sedeId = $_SESSION['natacion_sede_id'] ?? null;
            $sedePago = $sedeId ? ' AND npg_sede_id = ?' : '';
            $params = $sedeId ? [$this->tenantId, $year, (int)$sedeId] : [$this->tenantId, $year];

            $stm = $this->db->prepare("
                SELECT MONTH(npg_fecha) AS mes,
                       SUM(CASE WHEN npg_estado = 'PAGADO' THEN npg_monto ELSE 0 END) AS pagado,
                       SUM(CASE WHEN npg_estado = 'PENDIENTE' THEN npg_monto ELSE 0 END) AS pendiente,
                       COUNT(*) AS total_pagos
                FROM natacion_pagos
                WHERE npg_tenant_id = ? AND YEAR(npg_fecha) = ?{$sedePago}
                GROUP BY MONTH(npg_fecha)
                ORDER BY mes
            ");
            $stm->execute($params);
            return $this->jsonResponse(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_ASSOC)]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'data' => []]);
        }
    }

    /** AJAX: Inscripciones por grupo */
    public function inscripcionesPorGrupo() {
        try {
            $periodoId = (int)($this->get('periodo_id') ?? 0);
            $sedeId = $_SESSION['natacion_sede_id'] ?? null;
            $where = 'g.ngr_tenant_id = ?';
            $params = [$this->tenantId];
            if ($sedeId) { $where .= ' AND g.ngr_sede_id = ?'; $params[] = (int)$sedeId; }
            if ($periodoId) { $where .= ' AND g.ngr_periodo_id = ?'; $params[] = $periodoId; }

            $stm = $this->db->prepare("
                SELECT g.ngr_nombre AS grupo, n.nnv_nombre AS nivel,
                       g.ngr_cupo_maximo, g.ngr_cupo_actual,
                       ins.nin_nombres AS instructor_nombre, ins.nin_apellidos AS instructor_apellido,
                       ROUND(g.ngr_cupo_actual / g.ngr_cupo_maximo * 100, 1) AS porcentaje_ocupacion
                FROM natacion_grupos g
                LEFT JOIN natacion_niveles n ON g.ngr_nivel_id = n.nnv_nivel_id
                LEFT JOIN natacion_instructores ins ON g.ngr_instructor_id = ins.nin_instructor_id
                WHERE {$where} AND g.ngr_estado IN ('ABIERTO','EN_CURSO')
                ORDER BY g.ngr_nombre
            ");
            $stm->execute($params);
            return $this->jsonResponse(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_ASSOC)]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'data' => []]);
        }
    }

    /** AJAX: Egresos por mes/categoría */
    public function egresos() {
        try {
            $year = (int)($this->get('year') ?? date('Y'));
            $sedeId = $_SESSION['natacion_sede_id'] ?? null;
            $sedeSQL = $sedeId ? ' AND neg_sede_id = ?' : '';
            $params = $sedeId ? [$this->tenantId, $year, (int)$sedeId] : [$this->tenantId, $year];

            $stm = $this->db->prepare("
                SELECT MONTH(neg_fecha) AS mes, neg_categoria AS categoria,
                       SUM(neg_monto) AS total
                FROM natacion_egresos
                WHERE neg_tenant_id = ? AND YEAR(neg_fecha) = ? AND neg_estado != 'ANULADO'{$sedeSQL}
                GROUP BY MONTH(neg_fecha), neg_categoria
                ORDER BY mes, neg_categoria
            ");
            $stm->execute($params);
            return $this->jsonResponse(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_ASSOC)]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'data' => []]);
        }
    }

    /** AJAX: Comparación ingresos vs egresos por sede */
    public function comparacionSedes() {
        try {
            $mes  = (int)($this->get('mes')  ?? date('m'));
            $year = (int)($this->get('year') ?? date('Y'));

            $stm = $this->db->prepare("
                SELECT s.sed_sede_id, s.sed_nombre,
                       COALESCE(ing.total_ingresos, 0) AS ingresos,
                       COALESCE(egr.total_egresos, 0) AS egresos,
                       COALESCE(ing.total_ingresos, 0) - COALESCE(egr.total_egresos, 0) AS utilidad
                FROM instalaciones_sedes s
                LEFT JOIN (
                    SELECT npg_sede_id, SUM(npg_monto) AS total_ingresos
                    FROM natacion_pagos
                    WHERE npg_tenant_id = ? AND npg_estado = 'PAGADO'
                      AND MONTH(npg_fecha) = ? AND YEAR(npg_fecha) = ?
                    GROUP BY npg_sede_id
                ) ing ON s.sed_sede_id = ing.npg_sede_id
                LEFT JOIN (
                    SELECT neg_sede_id, SUM(neg_monto) AS total_egresos
                    FROM natacion_egresos
                    WHERE neg_tenant_id = ? AND neg_estado != 'ANULADO'
                      AND MONTH(neg_fecha) = ? AND YEAR(neg_fecha) = ?
                    GROUP BY neg_sede_id
                ) egr ON s.sed_sede_id = egr.neg_sede_id
                WHERE s.sed_tenant_id = ? AND s.sed_estado = 'A'
                ORDER BY s.sed_nombre
            ");
            $stm->execute([$this->tenantId, $mes, $year, $this->tenantId, $mes, $year, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_ASSOC)]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'data' => []]);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
