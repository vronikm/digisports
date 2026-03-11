<?php
/**
 * DigiSports Facturación — Dashboard Controller
 * Panel principal con KPIs del subsistema de facturación
 * 
 * @package DigiSports\Controllers\Facturacion
 */

namespace App\Controllers\Facturacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DashboardController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'facturacion';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'facturacion';
    }

    public function index() {
        $this->setupModule();
        $tid = $this->tenantId;
        $mesActual = date('Y-m');

        // ── KPI 1: Facturas del Mes ──
        $totalFacturasMes = 0;
        try {
            $stm = $this->db->prepare("
                SELECT COUNT(*) FROM facturacion_facturas 
                WHERE fac_tenant_id = ? AND DATE_FORMAT(fac_fecha_emision, '%Y-%m') = ?
            ");
            $stm->execute([$tid, $mesActual]);
            $totalFacturasMes = (int)$stm->fetchColumn();
        } catch (\Exception $e) {
            error_log("Dashboard facturacion KPI1: " . $e->getMessage());
        }

        // ── KPI 2: Ingresos del Mes ──
        $ingresosMes = 0;
        try {
            $stm = $this->db->prepare("
                SELECT COALESCE(SUM(fac_total), 0) FROM facturacion_facturas 
                WHERE fac_tenant_id = ? AND fac_estado != 'ANULADA' AND DATE_FORMAT(fac_fecha_emision, '%Y-%m') = ?
            ");
            $stm->execute([$tid, $mesActual]);
            $ingresosMes = (float)$stm->fetchColumn();
        } catch (\Exception $e) {
            error_log("Dashboard facturacion KPI2: " . $e->getMessage());
        }

        // ── KPI 3: Pagos Confirmados del Mes ──
        $pagosConfirmados = 0;
        try {
            $stm = $this->db->prepare("
                SELECT COALESCE(SUM(pag_monto), 0) FROM facturacion_pagos 
                WHERE pag_tenant_id = ? AND pag_estado = 'CONFIRMADO' AND DATE_FORMAT(pag_fecha_pago, '%Y-%m') = ?
            ");
            $stm->execute([$tid, $mesActual]);
            $pagosConfirmados = (float)$stm->fetchColumn();
        } catch (\Exception $e) {
            error_log("Dashboard facturacion KPI3: " . $e->getMessage());
        }

        // ── KPI 4: Facturas Pendientes de Pago ──
        $facturasPendientes = 0;
        try {
            $stm = $this->db->prepare("
                SELECT COUNT(*) FROM facturacion_facturas 
                WHERE fac_tenant_id = ? AND fac_estado = 'EMITIDA'
            ");
            $stm->execute([$tid]);
            $facturasPendientes = (int)$stm->fetchColumn();
        } catch (\Exception $e) {
            error_log("Dashboard facturacion KPI4: " . $e->getMessage());
        }

        // ── KPI 5: Facturas Anuladas ──
        $facturasAnuladas = 0;
        try {
            $stm = $this->db->prepare("
                SELECT COUNT(*) FROM facturacion_facturas 
                WHERE fac_tenant_id = ? AND fac_estado = 'ANULADA' AND DATE_FORMAT(fac_fecha_emision, '%Y-%m') = ?
            ");
            $stm->execute([$tid, $mesActual]);
            $facturasAnuladas = (int)$stm->fetchColumn();
        } catch (\Exception $e) {
            error_log("Dashboard facturacion KPI5: " . $e->getMessage());
        }

        // ── KPI 6: Facturas Electrónicas SRI ──
        $facturasElectronicas = 0;
        try {
            $stm = $this->db->prepare("
                SELECT COUNT(*) FROM facturacion_comprobantes_electronicos 
                WHERE fac_tenant_id = ? AND fac_estado_sri = 'AUTORIZADO'
            ");
            $stm->execute([$tid]);
            $facturasElectronicas = (int)$stm->fetchColumn();
        } catch (\Exception $e) {
            error_log("Dashboard facturacion KPI6: " . $e->getMessage());
        }

        $this->viewData['kpis'] = [
            ['label' => 'Facturas del Mes', 'value' => $totalFacturasMes,                      'icon' => 'fas fa-file-invoice',       'color' => '#F59E0B', 'trend' => null, 'trend_type' => null],
            ['label' => 'Ingresos Mes',     'value' => '$' . number_format($ingresosMes, 2),    'icon' => 'fas fa-dollar-sign',        'color' => '#22C55E', 'trend' => null, 'trend_type' => null],
            ['label' => 'Cobros Confirmados','value' => '$' . number_format($pagosConfirmados,2),'icon' => 'fas fa-money-check-alt',   'color' => '#3B82F6', 'trend' => null, 'trend_type' => null],
            ['label' => 'Pendientes Cobro',  'value' => $facturasPendientes,                    'icon' => 'fas fa-clock',              'color' => '#EF4444', 'trend' => null, 'trend_type' => null],
            ['label' => 'Anuladas Mes',      'value' => $facturasAnuladas,                      'icon' => 'fas fa-ban',                'color' => '#6B7280', 'trend' => null, 'trend_type' => null],
            ['label' => 'SRI Autorizadas',   'value' => $facturasElectronicas,                  'icon' => 'fas fa-globe-americas',     'color' => '#8B5CF6', 'trend' => null, 'trend_type' => null],
        ];

        // ── Últimas Facturas ──
        $this->viewData['ultimas_facturas'] = [];
        try {
            $stm = $this->db->prepare("
                SELECT f.fac_id, f.fac_numero, f.fac_fecha_emision, f.fac_total, f.fac_estado,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) as nombre_cliente
                FROM facturacion_facturas f
                LEFT JOIN clientes c ON f.fac_cliente_id = c.cli_cliente_id
                WHERE f.fac_tenant_id = ?
                ORDER BY f.fac_fecha_emision DESC
                LIMIT 10
            ");
            $stm->execute([$tid]);
            $this->viewData['ultimas_facturas'] = $stm->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Dashboard facturacion últimas facturas: " . $e->getMessage());
        }

        $this->viewData['title'] = 'Dashboard - Facturación';
        $this->renderModule('facturacion/dashboard/index');
    }
}
