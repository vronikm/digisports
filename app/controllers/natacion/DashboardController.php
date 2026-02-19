<?php
/**
 * DigiSports Natación — Dashboard Controller
 * Panel principal con KPIs reales desde la base de datos
 * 
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DashboardController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'NATACION';
    }

    public function index() {
        $this->setupModule();
        $tid = $this->tenantId;
        $hoy = date('Y-m-d');
        $diasSemana = ['DOM','LUN','MAR','MIE','JUE','VIE','SAB'];
        $diaHoy = $diasSemana[(int)date('w')];
        $sedeId = $_SESSION['natacion_sede_id'] ?? null;
        $sedeFiltro = $sedeId ? ' AND ' : '';

        // Cargar lista de sedes para el selector
        $sedesStm = $this->db->prepare("SELECT sed_sede_id, sed_nombre, sed_ciudad, sed_es_principal FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_es_principal DESC, sed_nombre");
        $sedesStm->execute([$tid]);
        $this->viewData['sedes'] = $sedesStm->fetchAll(\PDO::FETCH_ASSOC);
        $this->viewData['sede_activa'] = $sedeId;

        // ── KPI 1: Clases Hoy ──
        $sedeSQL1 = $sedeId ? ' AND g.ngr_sede_id = ?' : '';
        $stm = $this->db->prepare("
            SELECT COUNT(DISTINCT gh.ngh_grupo_id)
            FROM natacion_grupo_horarios gh
            JOIN natacion_grupos g ON gh.ngh_grupo_id = g.ngr_grupo_id AND g.ngr_tenant_id = gh.ngh_tenant_id
            WHERE gh.ngh_tenant_id = ? AND gh.ngh_dia_semana = ? AND gh.ngh_activo = 1 AND g.ngr_estado IN ('ABIERTO','EN_CURSO'){$sedeSQL1}
        ");
        $p1 = [$tid, $diaHoy]; if ($sedeId) $p1[] = $sedeId;
        $stm->execute($p1);
        $clasesHoy = (int)$stm->fetchColumn();

        // ── KPI 2: Alumnos Activos ──
        $sedeSQL2 = $sedeId ? ' AND a.alu_sede_id = ?' : '';
        $stm = $this->db->prepare("
            SELECT COUNT(*) FROM alumnos a
            JOIN natacion_ficha_alumno nf ON nf.nfa_alumno_id = a.alu_alumno_id AND nf.nfa_tenant_id = a.alu_tenant_id
            WHERE a.alu_tenant_id = ? AND a.alu_estado = 'ACTIVO' AND nf.nfa_activo = 1{$sedeSQL2}
        ");
        $p2 = [$tid]; if ($sedeId) $p2[] = $sedeId;
        $stm->execute($p2);
        $alumnosActivos = (int)$stm->fetchColumn();

        // ── KPI 3: Piscinas Activas ──
        $sedeSQL3 = $sedeId ? ' AND npi_sede_id = ?' : '';
        $stm = $this->db->prepare("SELECT COUNT(*) FROM natacion_piscinas WHERE npi_tenant_id = ? AND npi_activo = 1{$sedeSQL3}");
        $p3 = [$tid]; if ($sedeId) $p3[] = $sedeId;
        $stm->execute($p3);
        $piscinas = (int)$stm->fetchColumn();

        // ── KPI 4: Ingresos del Mes ──
        $mesActual = date('Y-m');
        $sedeSQL4 = $sedeId ? ' AND npg_sede_id = ?' : '';
        $stm = $this->db->prepare("
            SELECT COALESCE(SUM(npg_total), 0) FROM natacion_pagos
            WHERE npg_tenant_id = ? AND npg_estado = 'PAGADO' AND DATE_FORMAT(npg_fecha, '%Y-%m') = ?{$sedeSQL4}
        ");
        $p4 = [$tid, $mesActual]; if ($sedeId) $p4[] = $sedeId;
        $stm->execute($p4);
        $ingresosMes = (float)$stm->fetchColumn();

        // ── KPI 5: Instructores Activos ──
        $sedeSQL5 = $sedeId ? ' AND nin_sede_id = ?' : '';
        $stm = $this->db->prepare("SELECT COUNT(*) FROM natacion_instructores WHERE nin_tenant_id = ? AND nin_activo = 1{$sedeSQL5}");
        $p5 = [$tid]; if ($sedeId) $p5[] = $sedeId;
        $stm->execute($p5);
        $instructores = (int)$stm->fetchColumn();

        // ── KPI 6: Inscripciones Activas ──
        $sedeSQL6 = $sedeId ? ' AND nis_grupo_id IN (SELECT ngr_grupo_id FROM natacion_grupos WHERE ngr_sede_id = ?)' : '';
        $stm = $this->db->prepare("SELECT COUNT(*) FROM natacion_inscripciones WHERE nis_tenant_id = ? AND nis_estado = 'ACTIVA'{$sedeSQL6}");
        $p6 = [$tid]; if ($sedeId) $p6[] = $sedeId;
        $stm->execute($p6);
        $inscripcionesActivas = (int)$stm->fetchColumn();

        $this->viewData['kpis'] = [
            ['label' => 'Clases Hoy',     'value' => $clasesHoy,                                   'icon' => 'fas fa-swimmer',            'color' => $this->moduloColor, 'trend' => null, 'trend_type' => null],
            ['label' => 'Alumnos Activos', 'value' => $alumnosActivos,                              'icon' => 'fas fa-user-graduate',      'color' => '#8B5CF6',          'trend' => null, 'trend_type' => null],
            ['label' => 'Piscinas',        'value' => $piscinas,                                    'icon' => 'fas fa-water',              'color' => '#3B82F6',          'trend' => null, 'trend_type' => null],
            ['label' => 'Ingresos Mes',    'value' => '$' . number_format($ingresosMes, 2),         'icon' => 'fas fa-dollar-sign',        'color' => '#22C55E',          'trend' => null, 'trend_type' => null],
            ['label' => 'Instructores',    'value' => $instructores,                                'icon' => 'fas fa-chalkboard-teacher', 'color' => '#F97316',          'trend' => null, 'trend_type' => null],
            ['label' => 'Inscripciones',   'value' => $inscripcionesActivas,                        'icon' => 'fas fa-clipboard-list',     'color' => '#EAB308',          'trend' => null, 'trend_type' => null],
        ];

        // ── Clases del Día ──
        $sedeSQLClases = $sedeId ? ' AND g.ngr_sede_id = ?' : '';
        $stm = $this->db->prepare("
            SELECT gh.ngh_hora_inicio, gh.ngh_hora_fin, g.ngr_nombre, g.ngr_cupo_maximo, g.ngr_cupo_actual, g.ngr_color,
                   p.npi_nombre AS piscina, COALESCE(c.nca_numero, '') AS carril,
                   CONCAT(i.nin_nombres, ' ', i.nin_apellidos) AS instructor,
                   n.nnv_nombre AS nivel, n.nnv_color AS nivel_color
            FROM natacion_grupo_horarios gh
            JOIN natacion_grupos g ON gh.ngh_grupo_id = g.ngr_grupo_id AND g.ngr_tenant_id = gh.ngh_tenant_id
            LEFT JOIN natacion_piscinas p ON g.ngr_piscina_id = p.npi_piscina_id
            LEFT JOIN natacion_carriles c ON gh.ngh_carril_id = c.nca_carril_id
            LEFT JOIN natacion_instructores i ON g.ngr_instructor_id = i.nin_instructor_id
            LEFT JOIN natacion_niveles n ON g.ngr_nivel_id = n.nnv_nivel_id
            WHERE gh.ngh_tenant_id = ? AND gh.ngh_dia_semana = ? AND gh.ngh_activo = 1 AND g.ngr_estado IN ('ABIERTO','EN_CURSO'){$sedeSQLClases}
            ORDER BY gh.ngh_hora_inicio
        ");
        $pClases = [$tid, $diaHoy]; if ($sedeId) $pClases[] = $sedeId;
        $stm->execute($pClases);
        $this->viewData['clases_hoy'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

        // ── Alumnos por Nivel ──
        $stm = $this->db->prepare("
            SELECT n.nnv_nombre, n.nnv_color, COUNT(nf.nfa_ficha_id) AS total
            FROM natacion_niveles n
            LEFT JOIN natacion_ficha_alumno nf ON nf.nfa_nivel_actual_id = n.nnv_nivel_id AND nf.nfa_tenant_id = n.nnv_tenant_id AND nf.nfa_activo = 1
            WHERE n.nnv_tenant_id = ? AND n.nnv_activo = 1
            GROUP BY n.nnv_nivel_id, n.nnv_nombre, n.nnv_color
            ORDER BY n.nnv_orden
        ");
        $stm->execute([$tid]);
        $this->viewData['alumnos_nivel'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

        // ── Piscinas estado ──
        $stm = $this->db->prepare("SELECT npi_piscina_id, npi_nombre, npi_tipo, npi_temperatura, npi_activo FROM natacion_piscinas WHERE npi_tenant_id = ? ORDER BY npi_nombre");
        $stm->execute([$tid]);
        $this->viewData['piscinas'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

        // ── Asistencia últimos 7 días ──
        $sedeSQLAsis = $sedeId ? ' AND nas_grupo_id IN (SELECT ngr_grupo_id FROM natacion_grupos WHERE ngr_sede_id = ?)' : '';
        $stm = $this->db->prepare("
            SELECT nas_fecha, nas_estado, COUNT(*) as total
            FROM natacion_asistencia WHERE nas_tenant_id = ? AND nas_fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY){$sedeSQLAsis}
            GROUP BY nas_fecha, nas_estado ORDER BY nas_fecha
        ");
        $pAsis = [$tid]; if ($sedeId) $pAsis[] = $sedeId;
        $stm->execute($pAsis);
        $asistenciaRaw = $stm->fetchAll(\PDO::FETCH_ASSOC);

        $chartLabels = []; $chartPresente = []; $chartAusente = [];
        $diasNombres = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
        for ($i = 6; $i >= 0; $i--) {
            $dia = date('Y-m-d', strtotime("-{$i} days"));
            $chartLabels[] = $diasNombres[(int)date('w', strtotime($dia))];
            $p = 0; $a = 0;
            foreach ($asistenciaRaw as $r) {
                if ($r['nas_fecha'] === $dia) {
                    if ($r['nas_estado'] === 'PRESENTE') $p = (int)$r['total'];
                    if ($r['nas_estado'] === 'AUSENTE') $a = (int)$r['total'];
                }
            }
            $chartPresente[] = $p; $chartAusente[] = $a;
        }
        $this->viewData['chart_labels'] = json_encode($chartLabels);
        $this->viewData['chart_presente'] = json_encode($chartPresente);
        $this->viewData['chart_ausente'] = json_encode($chartAusente);

        // ── Últimas inscripciones ──
        $sedeSQLInsc = $sedeId ? ' AND g.ngr_sede_id = ?' : '';
        $stm = $this->db->prepare("
            SELECT i.nis_fecha_inscripcion, i.nis_estado,
                   CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS alumno,
                   g.ngr_nombre AS grupo
            FROM natacion_inscripciones i
            JOIN alumnos a ON i.nis_alumno_id = a.alu_alumno_id
            JOIN natacion_grupos g ON i.nis_grupo_id = g.ngr_grupo_id
            WHERE i.nis_tenant_id = ?{$sedeSQLInsc}
            ORDER BY i.nis_created_at DESC LIMIT 8
        ");
        $pInsc = [$tid]; if ($sedeId) $pInsc[] = $sedeId;
        $stm->execute($pInsc);
        $this->viewData['ultimas_inscripciones'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

        $this->viewData['title'] = 'Dashboard - ' . $this->moduloNombre;
        $this->renderModule('natacion/dashboard/index');
    }
}
