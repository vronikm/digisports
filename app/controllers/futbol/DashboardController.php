<?php
/**
 * DigiSports Fútbol — Dashboard Controller
 * Panel principal con KPIs reales desde la base de datos
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DashboardController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'FUTBOL';
    }

    public function index() {
        $this->setupModule();
        $tid = $this->tenantId;
        $hoy = date('Y-m-d');
        $diasSemana = ['DOM','LUN','MAR','MIE','JUE','VIE','SAB'];
        $diaHoy = $diasSemana[(int)date('w')];
        $sedeId = $_SESSION['futbol_sede_id'] ?? null;
        $moduloColor = '#22C55E';

        // Cargar lista de sedes para el selector
        $sedesStm = $this->db->prepare("SELECT sed_sede_id, sed_nombre, sed_ciudad, sed_es_principal FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_es_principal DESC, sed_nombre");
        $sedesStm->execute([$tid]);
        $this->viewData['sedes'] = $sedesStm->fetchAll(\PDO::FETCH_ASSOC);
        $this->viewData['sede_activa'] = $sedeId;

        // ── KPI 1: Entrenamientos Hoy ──
        $sedeSQL1 = $sedeId ? ' AND g.fgr_sede_id = ?' : '';
        $stm = $this->db->prepare("
            SELECT COUNT(DISTINCT fgh.fgh_grupo_id)
            FROM futbol_grupo_horarios fgh
            JOIN futbol_grupos g ON fgh.fgh_grupo_id = g.fgr_grupo_id AND g.fgr_tenant_id = fgh.fgh_tenant_id
            WHERE fgh.fgh_tenant_id = ? AND fgh.fgh_dia_semana = ? AND fgh.fgh_activo = 1 AND g.fgr_estado IN ('ABIERTO','EN_CURSO'){$sedeSQL1}
        ");
        $p1 = [$tid, $diaHoy]; if ($sedeId) $p1[] = $sedeId;
        $stm->execute($p1);
        $entrenamientosHoy = (int)$stm->fetchColumn();

        // ── KPI 2: Jugadores Activos ──
        $sedeSQL2 = $sedeId ? ' AND a.alu_sede_id = ?' : '';
        $stm = $this->db->prepare("
            SELECT COUNT(*) FROM alumnos a
            JOIN futbol_ficha_alumno ffa ON ffa.ffa_alumno_id = a.alu_alumno_id AND ffa.ffa_tenant_id = a.alu_tenant_id
            WHERE a.alu_tenant_id = ? AND a.alu_estado = 'ACTIVO' AND ffa.ffa_activo = 1{$sedeSQL2}
        ");
        $p2 = [$tid]; if ($sedeId) $p2[] = $sedeId;
        $stm->execute($p2);
        $jugadoresActivos = (int)$stm->fetchColumn();

        // ── KPI 3: Canchas Fútbol ──
        $stm = $this->db->prepare("SELECT COUNT(*) FROM instalaciones_canchas WHERE can_tenant_id = ? AND can_tipo = 'futbol' AND can_estado = 'ACTIVO'");
        $stm->execute([$tid]);
        $canchasFutbol = (int)$stm->fetchColumn();

        // ── KPI 4: Ingresos del Mes ──
        $mesActual = date('Y-m');
        $sedeSQL4 = $sedeId ? ' AND fpg_sede_id = ?' : '';
        $stm = $this->db->prepare("
            SELECT COALESCE(SUM(fpg_total), 0) FROM futbol_pagos
            WHERE fpg_tenant_id = ? AND fpg_estado = 'PAGADO' AND DATE_FORMAT(fpg_fecha, '%Y-%m') = ?{$sedeSQL4}
        ");
        $p4 = [$tid, $mesActual]; if ($sedeId) $p4[] = $sedeId;
        $stm->execute($p4);
        $ingresosMes = (float)$stm->fetchColumn();

        // ── KPI 5: Entrenadores Activos ──
        $sedeSQL5 = $sedeId ? ' AND fen_sede_id = ?' : '';
        $stm = $this->db->prepare("SELECT COUNT(*) FROM futbol_entrenadores WHERE fen_tenant_id = ? AND fen_activo = 1{$sedeSQL5}");
        $p5 = [$tid]; if ($sedeId) $p5[] = $sedeId;
        $stm->execute($p5);
        $entrenadores = (int)$stm->fetchColumn();

        // ── KPI 6: Inscripciones Activas ──
        $sedeSQL6 = $sedeId ? ' AND fin_grupo_id IN (SELECT fgr_grupo_id FROM futbol_grupos WHERE fgr_sede_id = ?)' : '';
        $stm = $this->db->prepare("SELECT COUNT(*) FROM futbol_inscripciones WHERE fin_tenant_id = ? AND fin_estado = 'ACTIVA'{$sedeSQL6}");
        $p6 = [$tid]; if ($sedeId) $p6[] = $sedeId;
        $stm->execute($p6);
        $inscripcionesActivas = (int)$stm->fetchColumn();

        $this->viewData['kpis'] = [
            ['label' => 'Entrenamientos Hoy', 'value' => $entrenamientosHoy,                          'icon' => 'fas fa-futbol',             'color' => $moduloColor, 'trend' => null, 'trend_type' => null],
            ['label' => 'Jugadores Activos',   'value' => $jugadoresActivos,                           'icon' => 'fas fa-running',            'color' => '#8B5CF6',    'trend' => null, 'trend_type' => null],
            ['label' => 'Canchas Fútbol',      'value' => $canchasFutbol,                              'icon' => 'fas fa-map-marked-alt',     'color' => '#3B82F6',    'trend' => null, 'trend_type' => null],
            ['label' => 'Ingresos Mes',        'value' => '$' . number_format($ingresosMes, 2),        'icon' => 'fas fa-dollar-sign',        'color' => '#22C55E',    'trend' => null, 'trend_type' => null],
            ['label' => 'Entrenadores',        'value' => $entrenadores,                               'icon' => 'fas fa-chalkboard-teacher', 'color' => '#F97316',    'trend' => null, 'trend_type' => null],
            ['label' => 'Inscripciones',       'value' => $inscripcionesActivas,                       'icon' => 'fas fa-clipboard-list',     'color' => '#EAB308',    'trend' => null, 'trend_type' => null],
        ];

        // ── Entrenamientos del Día ──
        $sedeSQLClases = $sedeId ? ' AND g.fgr_sede_id = ?' : '';
        $stm = $this->db->prepare("
            SELECT fgh.fgh_hora_inicio, fgh.fgh_hora_fin, g.fgr_nombre, g.fgr_cupo_maximo, g.fgr_cupo_actual, g.fgr_color,
                   can.can_nombre AS cancha,
                   CONCAT(fen.fen_nombres, ' ', fen.fen_apellidos) AS entrenador,
                   fct.fct_nombre AS categoria, fct.fct_color AS categoria_color
            FROM futbol_grupo_horarios fgh
            JOIN futbol_grupos g ON fgh.fgh_grupo_id = g.fgr_grupo_id AND g.fgr_tenant_id = fgh.fgh_tenant_id
            LEFT JOIN instalaciones_canchas can ON g.fgr_cancha_id = can.can_cancha_id
            LEFT JOIN futbol_entrenadores fen ON g.fgr_entrenador_id = fen.fen_entrenador_id
            LEFT JOIN futbol_categorias fct ON g.fgr_categoria_id = fct.fct_categoria_id
            WHERE fgh.fgh_tenant_id = ? AND fgh.fgh_dia_semana = ? AND fgh.fgh_activo = 1 AND g.fgr_estado IN ('ABIERTO','EN_CURSO'){$sedeSQLClases}
            ORDER BY fgh.fgh_hora_inicio
        ");
        $pClases = [$tid, $diaHoy]; if ($sedeId) $pClases[] = $sedeId;
        $stm->execute($pClases);
        $this->viewData['entrenamientos_hoy'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

        // ── Jugadores por Categoría ──
        $stm = $this->db->prepare("
            SELECT fct.fct_nombre, fct.fct_color, COUNT(ffa.ffa_ficha_id) AS total
            FROM futbol_categorias fct
            LEFT JOIN futbol_ficha_alumno ffa ON ffa.ffa_categoria_id = fct.fct_categoria_id AND ffa.ffa_tenant_id = fct.fct_tenant_id AND ffa.ffa_activo = 1
            WHERE fct.fct_tenant_id = ? AND fct.fct_activo = 1
            GROUP BY fct.fct_categoria_id, fct.fct_nombre, fct.fct_color
            ORDER BY fct.fct_orden
        ");
        $stm->execute([$tid]);
        $this->viewData['jugadores_categoria'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

        // ── Asistencia últimos 7 días ──
        $sedeSQLAsis = $sedeId ? ' AND fas_grupo_id IN (SELECT fgr_grupo_id FROM futbol_grupos WHERE fgr_sede_id = ?)' : '';
        $stm = $this->db->prepare("
            SELECT fas_fecha, fas_estado, COUNT(*) as total
            FROM futbol_asistencia WHERE fas_tenant_id = ? AND fas_fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY){$sedeSQLAsis}
            GROUP BY fas_fecha, fas_estado ORDER BY fas_fecha
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
                if ($r['fas_fecha'] === $dia) {
                    if ($r['fas_estado'] === 'PRESENTE') $p = (int)$r['total'];
                    if ($r['fas_estado'] === 'AUSENTE') $a = (int)$r['total'];
                }
            }
            $chartPresente[] = $p; $chartAusente[] = $a;
        }
        $this->viewData['chart_labels'] = json_encode($chartLabels);
        $this->viewData['chart_presente'] = json_encode($chartPresente);
        $this->viewData['chart_ausente'] = json_encode($chartAusente);

        // ── Últimas inscripciones ──
        $sedeSQLInsc = $sedeId ? ' AND g.fgr_sede_id = ?' : '';
        $stm = $this->db->prepare("
            SELECT i.fin_fecha_inscripcion, i.fin_estado,
                   CONCAT(a.alu_nombres, ' ', a.alu_apellidos) AS alumno,
                   g.fgr_nombre AS grupo
            FROM futbol_inscripciones i
            JOIN alumnos a ON i.fin_alumno_id = a.alu_alumno_id
            JOIN futbol_grupos g ON i.fin_grupo_id = g.fgr_grupo_id
            WHERE i.fin_tenant_id = ?{$sedeSQLInsc}
            ORDER BY i.fin_created_at DESC LIMIT 8
        ");
        $pInsc = [$tid]; if ($sedeId) $pInsc[] = $sedeId;
        $stm->execute($pInsc);
        $this->viewData['ultimas_inscripciones'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

        // ── Torneos Próximos ──
        $stm = $this->db->prepare("
            SELECT fto_torneo_id, fto_nombre, fto_fecha_inicio, fto_fecha_fin, fto_estado, fto_tipo, fto_sede_torneo
            FROM futbol_torneos
            WHERE fto_tenant_id = ? AND fto_estado IN ('PROXIMO','EN_CURSO')
            ORDER BY fto_fecha_inicio ASC
            LIMIT 10
        ");
        $stm->execute([$tid]);
        $this->viewData['futbol_torneos'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

        $this->viewData['csrf_token'] = \Security::generateCsrfToken();
        $this->viewData['title'] = 'Dashboard - ' . $this->moduloNombre;
        $this->renderModule('futbol/dashboard/index');
    }
}
