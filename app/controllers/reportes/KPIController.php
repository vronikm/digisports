<?php
/**
 * DigiSports - Controlador de KPIs
 * Indicadores clave de desempeño
 * 
 * @package DigiSports\Controllers\Reportes
 * @version 1.0.0
 */

namespace App\Controllers\Reportes;

require_once BASE_PATH . '/app/controllers/BaseController.php';

class KPIController extends \BaseController {
    
    /**
     * Dashboard de KPIs
     */
    public function index() {
        try {
            $periodo = $this->get('periodo') ?? 'mes';
            $comparar = $this->get('comparar') ?? 'no';
            
            // Determinar fechas
            $dates = $this->obtenerFechas($periodo);
            $fecha_inicio = $dates['inicio'];
            $fecha_fin = $dates['fin'];
            $fecha_inicio_anterior = $dates['inicio_anterior'];
            $fecha_fin_anterior = $dates['fin_anterior'];
            
            // KPIs principales
            $kpis_actuales = $this->calcularKPIs($fecha_inicio, $fecha_fin);
            $kpis_anteriores = null;
            
            if ($comparar === 'si') {
                $kpis_anteriores = $this->calcularKPIs($fecha_inicio_anterior, $fecha_fin_anterior);
            }
            
            // Tendencias
            $tendencia_ingresos = $this->calcularTendencia('ingresos', $fecha_inicio, $fecha_fin);
            $tendencia_facturas = $this->calcularTendencia('facturas', $fecha_inicio, $fecha_fin);
            $tendencia_cobranza = $this->calcularTendencia('cobranza', $fecha_inicio, $fecha_fin);
            
            // Alertas
            $alertas = $this->generarAlertas($kpis_actuales, $kpis_anteriores);
            
            $this->viewData['kpis_actuales'] = $kpis_actuales;
            $this->viewData['kpis_anteriores'] = $kpis_anteriores;
            $this->viewData['tendencia_ingresos'] = $tendencia_ingresos;
            $this->viewData['tendencia_facturas'] = $tendencia_facturas;
            $this->viewData['tendencia_cobranza'] = $tendencia_cobranza;
            $this->viewData['alertas'] = $alertas;
            $this->viewData['periodo'] = $periodo;
            $this->viewData['comparar'] = $comparar;
            $this->viewData['fecha_inicio'] = $fecha_inicio;
            $this->viewData['fecha_fin'] = $fecha_fin;
            $this->viewData['title'] = 'Dashboard de KPIs';
            $this->viewData['layout'] = 'main';
            
            $this->render('reportes/kpi', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al cargar KPIs: " . $e->getMessage());
            $this->error('Error al cargar los KPIs');
        }
    }
    
    /**
     * Obtener fechas según período
     */
    private function obtenerFechas($periodo) {
        $fecha_fin = date('Y-m-d');
        
        switch ($periodo) {
            case 'semana':
                $fecha_inicio = date('Y-m-d', strtotime('-7 days'));
                $fecha_fin_anterior = date('Y-m-d', strtotime('-14 days'));
                $fecha_inicio_anterior = date('Y-m-d', strtotime('-21 days'));
                break;
            case 'mes':
                $fecha_inicio = date('Y-m-01');
                $fecha_fin_anterior = date('Y-m-d', strtotime('first day of -1 month'));
                $fecha_inicio_anterior = date('Y-m-d', strtotime('first day of -1 month'));
                break;
            case 'trimestre':
                $mes_actual = date('m');
                $trimestre = ceil($mes_actual / 3);
                $mes_inicio = ($trimestre - 1) * 3 + 1;
                $fecha_inicio = date('Y-' . str_pad($mes_inicio, 2, '0', STR_PAD_LEFT) . '-01');
                $fecha_inicio_anterior = date('Y-' . str_pad($mes_inicio - 3, 2, '0', STR_PAD_LEFT) . '-01', strtotime('-3 months'));
                $fecha_fin_anterior = date('Y-' . str_pad($mes_inicio - 1, 2, '0', STR_PAD_LEFT) . '-t', strtotime('-3 months'));
                break;
            case 'anio':
                $fecha_inicio = date('Y-01-01');
                $fecha_inicio_anterior = date('Y-01-01', strtotime('-1 year'));
                $fecha_fin_anterior = date('Y-12-31', strtotime('-1 year'));
                break;
            default:
                $fecha_inicio = date('Y-m-01');
                $fecha_fin_anterior = date('Y-m-d', strtotime('first day of -1 month'));
                $fecha_inicio_anterior = date('Y-m-d', strtotime('first day of -1 month'));
        }
        
        return [
            'inicio' => $fecha_inicio,
            'fin' => $fecha_fin,
            'inicio_anterior' => $fecha_inicio_anterior,
            'fin_anterior' => $fecha_fin_anterior
        ];
    }
    
    /**
     * Calcular KPIs para un período
     */
    private function calcularKPIs($fecha_inicio, $fecha_fin) {
        try {
            $params_base = [$this->tenantId];
            
            // Total de ingresos
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(monto), 0) as total
                FROM pagos p
                INNER JOIN facturas f ON p.factura_id = f.factura_id
                WHERE f.tenant_id = ?
                    AND p.estado = 'CONFIRMADO'
                    AND DATE(p.fecha_pago) BETWEEN ? AND ?
            ");
            $stmt->execute(array_merge($params_base, [$fecha_inicio, $fecha_fin]));
            $total_ingresos = (float)$stmt->fetch()['total'];
            
            // Número de facturas emitidas
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total FROM facturas
                WHERE tenant_id = ?
                    AND DATE(fecha_emision) BETWEEN ? AND ?
            ");
            $stmt->execute(array_merge($params_base, [$fecha_inicio, $fecha_fin]));
            $num_facturas = (int)$stmt->fetch()['total'];
            
            // Número de facturas pagadas
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total FROM facturas
                WHERE tenant_id = ?
                    AND estado = 'PAGADA'
                    AND DATE(fecha_emision) BETWEEN ? AND ?
            ");
            $stmt->execute(array_merge($params_base, [$fecha_inicio, $fecha_fin]));
            $facturas_pagadas = (int)$stmt->fetch()['total'];
            
            // Tasa de cobranza
            $tasa_cobranza = $num_facturas > 0 ? ($facturas_pagadas / $num_facturas) * 100 : 0;
            
            // Monto promedio por factura
            $monto_promedio = $num_facturas > 0 ? $total_ingresos / $num_facturas : 0;
            
            // Clientes únicos
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT nombre_cliente) as total
                FROM facturas
                WHERE tenant_id = ?
                    AND DATE(fecha_emision) BETWEEN ? AND ?
            ");
            $stmt->execute(array_merge($params_base, [$fecha_inicio, $fecha_fin]));
            $clientes_unicos = (int)$stmt->fetch()['total'];
            
            // Saldo pendiente
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(
                    f.total - COALESCE((
                        SELECT SUM(monto) FROM pagos 
                        WHERE factura_id = f.factura_id AND estado = 'CONFIRMADO'
                    ), 0)
                ), 0) as saldo
                FROM facturas f
                WHERE f.tenant_id = ?
                    AND DATE(f.fecha_emision) BETWEEN ? AND ?
            ");
            $stmt->execute(array_merge($params_base, [$fecha_inicio, $fecha_fin]));
            $saldo_pendiente = (float)$stmt->fetch()['saldo'];
            
            // Días promedio pago
            $stmt = $this->db->prepare("
                SELECT AVG(DATEDIFF(p.fecha_pago, f.fecha_emision)) as dias
                FROM pagos p
                INNER JOIN facturas f ON p.factura_id = f.factura_id
                WHERE f.tenant_id = ?
                    AND p.estado = 'CONFIRMADO'
                    AND DATE(p.fecha_pago) BETWEEN ? AND ?
            ");
            $stmt->execute(array_merge($params_base, [$fecha_inicio, $fecha_fin]));
            $dias_pago = (int)($stmt->fetch()['dias'] ?? 0);
            
            return [
                'total_ingresos' => $total_ingresos,
                'num_facturas' => $num_facturas,
                'facturas_pagadas' => $facturas_pagadas,
                'tasa_cobranza' => round($tasa_cobranza, 2),
                'monto_promedio' => $monto_promedio,
                'clientes_unicos' => $clientes_unicos,
                'saldo_pendiente' => $saldo_pendiente,
                'dias_promedio_pago' => $dias_pago
            ];
            
        } catch (\Exception $e) {
            $this->logError("Error al calcular KPIs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcular tendencia (variación porcentual)
     */
    private function calcularTendencia($tipo, $fecha_inicio, $fecha_fin) {
        try {
            // Período anterior
            $dias_diff = strtotime($fecha_fin) - strtotime($fecha_inicio);
            $fecha_inicio_anterior = date('Y-m-d', strtotime($fecha_inicio) - $dias_diff);
            $fecha_fin_anterior = date('Y-m-d', strtotime($fecha_inicio) - 1);
            
            if ($tipo === 'ingresos') {
                // Actual
                $stmt = $this->db->prepare("
                    SELECT COALESCE(SUM(monto), 0) as total
                    FROM pagos p
                    INNER JOIN facturas f ON p.factura_id = f.factura_id
                    WHERE f.tenant_id = ?
                        AND p.estado = 'CONFIRMADO'
                        AND DATE(p.fecha_pago) BETWEEN ? AND ?
                ");
                $stmt->execute([$this->tenantId, $fecha_inicio, $fecha_fin]);
                $actual = (float)$stmt->fetch()['total'];
                
                // Anterior
                $stmt->execute([$this->tenantId, $fecha_inicio_anterior, $fecha_fin_anterior]);
                $anterior = (float)$stmt->fetch()['total'];
                
            } elseif ($tipo === 'facturas') {
                // Actual
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total FROM facturas
                    WHERE tenant_id = ? AND DATE(fecha_emision) BETWEEN ? AND ?
                ");
                $stmt->execute([$this->tenantId, $fecha_inicio, $fecha_fin]);
                $actual = (int)$stmt->fetch()['total'];
                
                // Anterior
                $stmt->execute([$this->tenantId, $fecha_inicio_anterior, $fecha_fin_anterior]);
                $anterior = (int)$stmt->fetch()['total'];
                
            } else { // cobranza
                // Actual
                $stmt = $this->db->prepare("
                    SELECT COUNT(CASE WHEN f.estado = 'PAGADA' THEN 1 END) as pagadas,
                           COUNT(*) as total
                    FROM facturas f
                    WHERE f.tenant_id = ? AND DATE(f.fecha_emision) BETWEEN ? AND ?
                ");
                $stmt->execute([$this->tenantId, $fecha_inicio, $fecha_fin]);
                $row = $stmt->fetch();
                $actual = $row['total'] > 0 ? ($row['pagadas'] / $row['total']) * 100 : 0;
                
                // Anterior
                $stmt->execute([$this->tenantId, $fecha_inicio_anterior, $fecha_fin_anterior]);
                $row = $stmt->fetch();
                $anterior = $row['total'] > 0 ? ($row['pagadas'] / $row['total']) * 100 : 0;
            }
            
            // Variación
            $variacion = $anterior > 0 ? (($actual - $anterior) / $anterior) * 100 : 0;
            
            return [
                'actual' => $actual,
                'anterior' => $anterior,
                'variacion' => round($variacion, 2),
                'positiva' => $variacion >= 0
            ];
            
        } catch (\Exception $e) {
            $this->logError("Error al calcular tendencia: " . $e->getMessage());
            return ['actual' => 0, 'anterior' => 0, 'variacion' => 0, 'positiva' => true];
        }
    }
    
    /**
     * Generar alertas
     */
    private function generarAlertas($kpis_actuales, $kpis_anteriores = null) {
        $alertas = [];
        
        // Verificar que existan los KPIs necesarios
        if (empty($kpis_actuales)) {
            return $alertas;
        }
        
        // Alerta: Baja tasa de cobranza
        $tasa_cobranza = $kpis_actuales['tasa_cobranza'] ?? 100;
        if ($tasa_cobranza < 70) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Baja Tasa de Cobranza',
                'mensaje' => 'La tasa de cobranza es ' . $tasa_cobranza . '%. Considere hacer seguimiento a clientes.'
            ];
        }
        
        // Alerta: Alto saldo pendiente
        $saldo_pendiente = $kpis_actuales['saldo_pendiente'] ?? 0;
        $total_ingresos = $kpis_actuales['total_ingresos'] ?? 0;
        if ($total_ingresos > 0 && $saldo_pendiente > $total_ingresos * 0.3) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Alto Saldo Pendiente',
                'mensaje' => '$' . number_format($saldo_pendiente, 2) . ' pendiente de cobro.'
            ];
        }
        
        // Alerta: Días promedio de pago muy alto
        $dias_promedio_pago = $kpis_actuales['dias_promedio_pago'] ?? 0;
        if ($dias_promedio_pago > 30) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Días Promedio de Pago Elevado',
                'mensaje' => 'Promedio de ' . $dias_promedio_pago . ' días. Considere acortar plazos.'
            ];
        }
        
        // Alerta: Comparativa anterior - Disminución de ingresos
        $kpis_anteriores_ingresos = $kpis_anteriores['total_ingresos'] ?? 0;
        if ($kpis_anteriores && $kpis_anteriores_ingresos > 0 && $total_ingresos < $kpis_anteriores_ingresos * 0.8) {
            $porcentaje = round((($total_ingresos - $kpis_anteriores_ingresos) / $kpis_anteriores_ingresos) * 100, 2);
            $alertas[] = [
                'tipo' => 'danger',
                'titulo' => 'Disminución de Ingresos',
                'mensaje' => 'Disminución del ' . abs($porcentaje) . '% respecto al período anterior.'
            ];
        }
        
        return $alertas;
    }
}
