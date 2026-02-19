<?php
/**
 * DigiSports Fútbol — Controlador de Reportes
 * Reportes financieros, asistencia e inscripciones
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class ReporteController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    /**
     * Página principal de reportes con estadísticas resumen
     */
    public function index() {
        try {
            $this->setupModule();
            $sedeId = $_SESSION['futbol_sede_id'] ?? null;

            // Estadísticas básicas
            $sedeSQL = $sedeId ? ' AND fpg_sede_id = ?' : '';
            $mesActual = date('Y-m');

            // Total alumnos activos
            $sedeSQL2 = $sedeId ? ' AND a.alu_sede_id = ?' : '';
            $stm = $this->db->prepare("
                SELECT COUNT(*) FROM alumnos a
                JOIN futbol_ficha_alumno ffa ON ffa.ffa_alumno_id = a.alu_alumno_id AND ffa.ffa_tenant_id = a.alu_tenant_id
                WHERE a.alu_tenant_id = ? AND a.alu_estado = 'ACTIVO' AND ffa.ffa_activo = 1{$sedeSQL2}
            ");
            $p = [$this->tenantId]; if ($sedeId) $p[] = (int)$sedeId;
            $stm->execute($p);
            $totalAlumnos = (int)$stm->fetchColumn();

            // Inscripciones activas
            $stm2 = $this->db->prepare("SELECT COUNT(*) FROM futbol_inscripciones WHERE fin_tenant_id = ? AND fin_estado = 'ACTIVA'");
            $stm2->execute([$this->tenantId]);
            $inscripcionesActivas = (int)$stm2->fetchColumn();

            // Ingresos del mes
            $stm3 = $this->db->prepare("
                SELECT COALESCE(SUM(fpg_total), 0) FROM futbol_pagos
                WHERE fpg_tenant_id = ? AND fpg_estado = 'PAGADO' AND DATE_FORMAT(fpg_fecha, '%Y-%m') = ?{$sedeSQL}
            ");
            $p3 = [$this->tenantId, $mesActual]; if ($sedeId) $p3[] = (int)$sedeId;
            $stm3->execute($p3);
            $ingresosMes = (float)$stm3->fetchColumn();

            // Pagos pendientes
            $stm4 = $this->db->prepare("
                SELECT COUNT(*), COALESCE(SUM(fpg_total), 0) FROM futbol_pagos
                WHERE fpg_tenant_id = ? AND fpg_estado IN ('PENDIENTE','VENCIDO'){$sedeSQL}
            ");
            $p4 = [$this->tenantId]; if ($sedeId) $p4[] = (int)$sedeId;
            $stm4->execute($p4);
            $pendientes = $stm4->fetch(\PDO::FETCH_NUM);

            $this->viewData['stats'] = [
                'total_alumnos' => $totalAlumnos,
                'inscripciones_activas' => $inscripcionesActivas,
                'ingresos_mes' => $ingresosMes,
                'pagos_pendientes_count' => (int)($pendientes[0] ?? 0),
                'pagos_pendientes_monto' => (float)($pendientes[1] ?? 0),
            ];

            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Reportes';
            $this->renderModule('futbol/reportes/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error cargando reportes: " . $e->getMessage());
            $this->error('Error al cargar reportes');
        }
    }

    /**
     * Reporte financiero: ingresos y egresos por mes (JSON)
     */
    public function financiero() {
        try {
            $anio = (int)($this->get('anio') ?? date('Y'));
            $sedeId = $_SESSION['futbol_sede_id'] ?? null;

            // Ingresos por mes
            $sedeSQL = $sedeId ? ' AND fpg_sede_id = ?' : '';
            $stm = $this->db->prepare("
                SELECT DATE_FORMAT(fpg_fecha, '%Y-%m') AS mes,
                       SUM(fpg_total) AS total
                FROM futbol_pagos
                WHERE fpg_tenant_id = ? AND fpg_estado = 'PAGADO' AND YEAR(fpg_fecha) = ?{$sedeSQL}
                GROUP BY DATE_FORMAT(fpg_fecha, '%Y-%m')
                ORDER BY mes
            ");
            $p = [$this->tenantId, $anio]; if ($sedeId) $p[] = (int)$sedeId;
            $stm->execute($p);
            $ingresos = $stm->fetchAll(\PDO::FETCH_ASSOC);

            // Egresos por mes
            $stm2 = $this->db->prepare("
                SELECT DATE_FORMAT(feg_fecha, '%Y-%m') AS mes,
                       SUM(feg_monto) AS total
                FROM futbol_egresos
                WHERE feg_tenant_id = ? AND YEAR(feg_fecha) = ?
                GROUP BY DATE_FORMAT(feg_fecha, '%Y-%m')
                ORDER BY mes
            ");
            $stm2->execute([$this->tenantId, $anio]);
            $egresos = $stm2->fetchAll(\PDO::FETCH_ASSOC);

            // Construir datos mensuales completos
            $meses = [];
            $ingresosMap = array_column($ingresos, 'total', 'mes');
            $egresosMap = array_column($egresos, 'total', 'mes');

            for ($m = 1; $m <= 12; $m++) {
                $mesKey = sprintf('%04d-%02d', $anio, $m);
                $ingreso = (float)($ingresosMap[$mesKey] ?? 0);
                $egreso = (float)($egresosMap[$mesKey] ?? 0);
                $meses[] = [
                    'mes' => $mesKey,
                    'ingresos' => $ingreso,
                    'egresos' => $egreso,
                    'utilidad' => $ingreso - $egreso,
                ];
            }

            return $this->jsonResponse(['success' => true, 'data' => $meses, 'anio' => $anio]);

        } catch (\Exception $e) {
            $this->logError("Error en reporte financiero: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al generar reporte financiero']);
        }
    }

    /**
     * Reporte de asistencia por grupo y mes (JSON)
     */
    public function asistencia() {
        try {
            $grupoId = (int)($this->get('grupo_id') ?? 0);
            $mes = $this->get('mes') ?: date('Y-m');

            $where = 'fas.fas_tenant_id = ? AND DATE_FORMAT(fas.fas_fecha, "%Y-%m") = ?';
            $params = [$this->tenantId, $mes];
            if ($grupoId) { $where .= ' AND fas.fas_grupo_id = ?'; $params[] = $grupoId; }

            $stm = $this->db->prepare("
                SELECT fas.fas_grupo_id, fgr.fgr_nombre AS grupo_nombre,
                       COUNT(*) AS total_registros,
                       SUM(CASE WHEN fas.fas_estado = 'PRESENTE' THEN 1 ELSE 0 END) AS presentes,
                       SUM(CASE WHEN fas.fas_estado = 'AUSENTE' THEN 1 ELSE 0 END) AS ausentes,
                       SUM(CASE WHEN fas.fas_estado = 'TARDANZA' THEN 1 ELSE 0 END) AS tardanzas,
                       SUM(CASE WHEN fas.fas_estado = 'JUSTIFICADO' THEN 1 ELSE 0 END) AS justificados,
                       ROUND(SUM(CASE WHEN fas.fas_estado = 'PRESENTE' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) AS porcentaje_asistencia
                FROM futbol_asistencia fas
                JOIN futbol_grupos fgr ON fas.fas_grupo_id = fgr.fgr_grupo_id
                WHERE {$where}
                GROUP BY fas.fas_grupo_id, fgr.fgr_nombre
                ORDER BY fgr.fgr_nombre
            ");
            $stm->execute($params);

            return $this->jsonResponse(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_ASSOC), 'mes' => $mes]);

        } catch (\Exception $e) {
            $this->logError("Error en reporte de asistencia: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al generar reporte de asistencia']);
        }
    }

    /**
     * Reporte de inscripciones por estado, categoría y mes (JSON)
     */
    public function inscripciones() {
        try {
            // Por estado
            $stm = $this->db->prepare("
                SELECT fin_estado, COUNT(*) AS total
                FROM futbol_inscripciones
                WHERE fin_tenant_id = ?
                GROUP BY fin_estado
            ");
            $stm->execute([$this->tenantId]);
            $porEstado = $stm->fetchAll(\PDO::FETCH_ASSOC);

            // Por categoría
            $stm2 = $this->db->prepare("
                SELECT fct.fct_nombre AS categoria, COUNT(*) AS total
                FROM futbol_inscripciones fi
                JOIN futbol_grupos fg ON fi.fin_grupo_id = fg.fgr_grupo_id
                LEFT JOIN futbol_categorias fct ON fg.fgr_categoria_id = fct.fct_categoria_id
                WHERE fi.fin_tenant_id = ?
                GROUP BY fct.fct_nombre
                ORDER BY total DESC
            ");
            $stm2->execute([$this->tenantId]);
            $porCategoria = $stm2->fetchAll(\PDO::FETCH_ASSOC);

            // Por mes (últimos 12 meses)
            $stm3 = $this->db->prepare("
                SELECT DATE_FORMAT(fin_fecha_inscripcion, '%Y-%m') AS mes, COUNT(*) AS total
                FROM futbol_inscripciones
                WHERE fin_tenant_id = ? AND fin_fecha_inscripcion >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(fin_fecha_inscripcion, '%Y-%m')
                ORDER BY mes
            ");
            $stm3->execute([$this->tenantId]);
            $porMes = $stm3->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'por_estado' => $porEstado,
                    'por_categoria' => $porCategoria,
                    'por_mes' => $porMes,
                ],
            ]);

        } catch (\Exception $e) {
            $this->logError("Error en reporte de inscripciones: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al generar reporte de inscripciones']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
