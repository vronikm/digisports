<?php
/**
 * DigiSports - Controlador de Reportes
 * Dashboard, gráficos y análisis financiero
 * 
 * @package DigiSports\Controllers\Reportes
 * @version 1.0.0
 */

namespace App\Controllers\Reportes;

require_once BASE_PATH . '/app/controllers/BaseController.php';

class ReporteController extends \BaseController {
    
    /**
     * Dashboard principal
     */
    public function index() {
        try {
            // Período actual (último 30 días por defecto)
            $dias = (int)$this->get('dias') ?? 30;
            $fecha_inicio = date('Y-m-d', strtotime("-{$dias} days"));
            $fecha_fin = date('Y-m-d');
            
            // KPIs principales
            $kpis = $this->obtenerKPIs($fecha_inicio, $fecha_fin);
            
            // Gráficos
            $grafico_ingresos = $this->graficoIngresosPolínea($fecha_inicio, $fecha_fin);
            $grafico_forma_pago = $this->graficoFormaPago($fecha_inicio, $fecha_fin);
            $grafico_estado_factura = $this->graficoEstadoFactura();
            
            // Top 5 clientes
            $top_clientes = $this->obtenerTopClientes(5, $fecha_inicio, $fecha_fin);
            
            // Últimas facturas
            $ultimas_facturas = $this->obtenerUltimasFacturas(10);
            
            $this->viewData['kpis'] = $kpis;
            $this->viewData['grafico_ingresos'] = $grafico_ingresos;
            $this->viewData['grafico_forma_pago'] = $grafico_forma_pago;
            $this->viewData['grafico_estado'] = $grafico_estado_factura;
            $this->viewData['top_clientes'] = $top_clientes;
            $this->viewData['ultimas_facturas'] = $ultimas_facturas;
            $this->viewData['fecha_inicio'] = $fecha_inicio;
            $this->viewData['fecha_fin'] = $fecha_fin;
            $this->viewData['dias'] = $dias;
            $this->viewData['title'] = 'Dashboard de Reportes';
            $this->viewData['layout'] = 'main';
            
            $this->render('reportes/index', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error al cargar dashboard: " . $e->getMessage());
            $this->error('Error al cargar el dashboard');
        }
    }
    
    /**
     * Obtener KPIs principales
     */
    private function obtenerKPIs($fecha_inicio, $fecha_fin) {
        try {
            $params = [$this->tenantId, $fecha_inicio, $fecha_fin];
            
            // Total ingresos
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(monto), 0) as total
                FROM pagos p
                INNER JOIN facturas f ON p.factura_id = f.factura_id
                WHERE f.tenant_id = ? 
                    AND p.estado = 'CONFIRMADO'
                    AND DATE(p.fecha_pago) BETWEEN ? AND ?
            ");
            $stmt->execute($params);
            $total_ingresos = $stmt->fetch()['total'];
            
            // Total facturas emitidas
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM facturas
                WHERE tenant_id = ? 
                    AND estado IN ('EMITIDA', 'PAGADA')
                    AND DATE(fecha_emision) BETWEEN ? AND ?
            ");
            $stmt->execute($params);
            $total_facturas = $stmt->fetch()['total'];
            
            // Facturas pagadas
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM facturas
                WHERE tenant_id = ? 
                    AND estado = 'PAGADA'
                    AND DATE(fecha_pago) BETWEEN ? AND ?
            ");
            $stmt->execute($params);
            $facturas_pagadas = $stmt->fetch()['total'];
            
            // Saldo pendiente
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(f.total - COALESCE(
                    (SELECT SUM(monto) FROM pagos WHERE factura_id = f.factura_id AND estado = 'CONFIRMADO'), 0
                )), 0) as saldo
                FROM facturas f
                WHERE f.tenant_id = ?
                    AND f.estado IN ('EMITIDA', 'PAGADA')
                    AND DATE(f.fecha_emision) BETWEEN ? AND ?
            ");
            $stmt->execute($params);
            $saldo_pendiente = $stmt->fetch()['saldo'];
            
            // Número de clientes únicos
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT nombre_cliente) as total
                FROM facturas
                WHERE tenant_id = ?
                    AND DATE(fecha_emision) BETWEEN ? AND ?
            ");
            $stmt->execute($params);
            $clientes_unicos = $stmt->fetch()['total'];
            
            return [
                'total_ingresos' => (float)$total_ingresos,
                'total_facturas' => (int)$total_facturas,
                'facturas_pagadas' => (int)$facturas_pagadas,
                'saldo_pendiente' => (float)$saldo_pendiente,
                'clientes_unicos' => (int)$clientes_unicos,
                'tasa_cobranza' => $total_facturas > 0 ? round(($facturas_pagadas / $total_facturas) * 100, 2) : 0
            ];
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener KPIs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Gráfico de ingresos por línea de tiempo
     */
    private function graficoIngresosPolínea($fecha_inicio, $fecha_fin) {
        try {
            $stmt = $this->db->prepare("
                SELECT DATE(p.fecha_pago) as fecha, SUM(p.monto) as total
                FROM pagos p
                INNER JOIN facturas f ON p.factura_id = f.factura_id
                WHERE f.tenant_id = ?
                    AND p.estado = 'CONFIRMADO'
                    AND DATE(p.fecha_pago) BETWEEN ? AND ?
                GROUP BY DATE(p.fecha_pago)
                ORDER BY fecha ASC
            ");
            $stmt->execute([$this->tenantId, $fecha_inicio, $fecha_fin]);
            $datos = $stmt->fetchAll();
            
            $fechas = [];
            $montos = [];
            foreach ($datos as $row) {
                $fechas[] = date('d/m', strtotime($row['fecha']));
                $montos[] = (float)$row['total'];
            }
            
            return [
                'labels' => $fechas,
                'data' => $montos
            ];
            
        } catch (\Exception $e) {
            $this->logError("Error en gráfico ingresos: " . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }
    
    /**
     * Gráfico de ingresos por forma de pago
     */
    private function graficoFormaPago($fecha_inicio, $fecha_fin) {
        try {
            $stmt = $this->db->prepare("
                SELECT fp.nombre, SUM(p.monto) as total
                FROM pagos p
                INNER JOIN formas_pago fp ON p.forma_pago_id = fp.forma_pago_id
                INNER JOIN facturas f ON p.factura_id = f.factura_id
                WHERE f.tenant_id = ?
                    AND p.estado = 'CONFIRMADO'
                    AND DATE(p.fecha_pago) BETWEEN ? AND ?
                GROUP BY fp.forma_pago_id, fp.nombre
                ORDER BY total DESC
            ");
            $stmt->execute([$this->tenantId, $fecha_inicio, $fecha_fin]);
            $datos = $stmt->fetchAll();
            
            $formas = [];
            $montos = [];
            $colores = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'];
            
            foreach ($datos as $i => $row) {
                $formas[] = $row['nombre'];
                $montos[] = (float)$row['total'];
            }
            
            return [
                'labels' => $formas,
                'data' => $montos,
                'colors' => array_slice($colores, 0, count($formas))
            ];
            
        } catch (\Exception $e) {
            $this->logError("Error en gráfico forma pago: " . $e->getMessage());
            return ['labels' => [], 'data' => [], 'colors' => []];
        }
    }
    
    /**
     * Gráfico de estado de facturas
     */
    private function graficoEstadoFactura() {
        try {
            $stmt = $this->db->prepare("
                SELECT estado, COUNT(*) as cantidad
                FROM facturas
                WHERE tenant_id = ?
                GROUP BY estado
            ");
            $stmt->execute([$this->tenantId]);
            $datos = $stmt->fetchAll();
            
            $estados = [];
            $cantidades = [];
            $colores = [
                'BORRADOR' => '#95a5a6',
                'EMITIDA' => '#f39c12',
                'PAGADA' => '#27ae60',
                'ANULADA' => '#e74c3c'
            ];
            
            foreach ($datos as $row) {
                $estados[] = $row['estado'];
                $cantidades[] = (int)$row['cantidad'];
            }
            
            return [
                'labels' => $estados,
                'data' => $cantidades,
                'colors' => array_map(fn($e) => $colores[$e] ?? '#000', $estados)
            ];
            
        } catch (\Exception $e) {
            $this->logError("Error en gráfico estado: " . $e->getMessage());
            return ['labels' => [], 'data' => [], 'colors' => []];
        }
    }
    
    /**
     * Top clientes por ingresos
     */
    private function obtenerTopClientes($limite, $fecha_inicio, $fecha_fin) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    f.nombre_cliente,
                    COUNT(DISTINCT f.factura_id) as cantidad_facturas,
                    COALESCE(SUM(p.monto), 0) as total_pagado
                FROM facturas f
                LEFT JOIN pagos p ON f.factura_id = p.factura_id AND p.estado = 'CONFIRMADO'
                WHERE f.tenant_id = ?
                    AND DATE(f.fecha_emision) BETWEEN ? AND ?
                GROUP BY f.nombre_cliente
                ORDER BY total_pagado DESC
                LIMIT ?
            ");
            $stmt->execute([$this->tenantId, $fecha_inicio, $fecha_fin, $limite]);
            return $stmt->fetchAll();
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener top clientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Últimas facturas
     */
    private function obtenerUltimasFacturas($limite) {
        try {
            $stmt = $this->db->prepare("
                SELECT f.*, 
                       COALESCE(SUM(p.monto), 0) as total_pagado
                FROM facturas f
                LEFT JOIN pagos p ON f.factura_id = p.factura_id AND p.estado = 'CONFIRMADO'
                WHERE f.tenant_id = ?
                GROUP BY f.factura_id
                ORDER BY f.fecha_emision DESC
                LIMIT ?
            ");
            $stmt->execute([$this->tenantId, $limite]);
            return $stmt->fetchAll();
            
        } catch (\Exception $e) {
            $this->logError("Error al obtener últimas facturas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reporte detallado de facturas
     */
    public function facturas() {
        try {
            $estado = $this->get('estado') ?? '';
            $fecha_inicio = $this->get('fecha_inicio') ?? date('Y-m-01');
            $fecha_fin = $this->get('fecha_fin') ?? date('Y-m-d');
            $pagina = (int)($this->get('pagina') ?? 1);
            $perPage = 25;
            $offset = ($pagina - 1) * $perPage;
            
            // Query base
            $query = "
                SELECT f.*, 
                       COALESCE(SUM(p.monto), 0) as total_pagado,
                       fp.nombre as forma_pago_nombre
                FROM facturas f
                LEFT JOIN pagos p ON f.factura_id = p.factura_id AND p.estado = 'CONFIRMADO'
                LEFT JOIN formas_pago fp ON f.forma_pago_id = fp.forma_pago_id
                WHERE f.tenant_id = ?
            ";
            
            $params = [$this->tenantId];
            
            if (!empty($estado)) {
                $query .= " AND f.estado = ?";
                $params[] = $estado;
            }
            
            if (!empty($fecha_inicio)) {
                $query .= " AND DATE(f.fecha_emision) >= ?";
                $params[] = $fecha_inicio;
            }
            
            if (!empty($fecha_fin)) {
                $query .= " AND DATE(f.fecha_emision) <= ?";
                $params[] = $fecha_fin;
            }
            
            // Contar total
            $countQuery = str_replace('SELECT f.*,', 'SELECT COUNT(DISTINCT f.factura_id) as total FROM (SELECT f.factura_id,', $query);
            $countQuery .= ') as subquery';
            
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute($params);
            $totalRegistros = $stmt->fetch()['total'] ?? 0;
            
            // Datos paginados
            $query .= " GROUP BY f.factura_id ORDER BY f.fecha_emision DESC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $facturas = $stmt->fetchAll();
            
            $this->viewData['facturas'] = $facturas;
            $this->viewData['totalRegistros'] = $totalRegistros;
            $this->viewData['pagina'] = $pagina;
            $this->viewData['totalPaginas'] = ceil($totalRegistros / $perPage);
            $this->viewData['estado'] = $estado;
            $this->viewData['fecha_inicio'] = $fecha_inicio;
            $this->viewData['fecha_fin'] = $fecha_fin;
            $this->viewData['title'] = 'Reporte de Facturas';
            $this->viewData['layout'] = 'main';
            
            $this->render('reportes/facturas', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error en reporte facturas: " . $e->getMessage());
            $this->error('Error al cargar reporte');
        }
    }
    
    /**
     * Reporte de ingresos
     */
    public function ingresos() {
        try {
            $fecha_inicio = $this->get('fecha_inicio') ?? date('Y-m-01');
            $fecha_fin = $this->get('fecha_fin') ?? date('Y-m-d');
            
            // Ingresos por día
            $stmt = $this->db->prepare("
                SELECT DATE(p.fecha_pago) as fecha,
                       COUNT(*) as cantidad_pagos,
                       SUM(p.monto) as total
                FROM pagos p
                INNER JOIN facturas f ON p.factura_id = f.factura_id
                WHERE f.tenant_id = ?
                    AND p.estado = 'CONFIRMADO'
                    AND DATE(p.fecha_pago) BETWEEN ? AND ?
                GROUP BY DATE(p.fecha_pago)
                ORDER BY fecha DESC
            ");
            $stmt->execute([$this->tenantId, $fecha_inicio, $fecha_fin]);
            $ingresos_diarios = $stmt->fetchAll();
            
            // Resumen general
            $total = 0;
            $cantidad = 0;
            foreach ($ingresos_diarios as $row) {
                $total += $row['total'];
                $cantidad += $row['cantidad_pagos'];
            }
            
            $this->viewData['ingresos_diarios'] = $ingresos_diarios;
            $this->viewData['total_ingresos'] = $total;
            $this->viewData['total_pagos'] = $cantidad;
            $this->viewData['promedio_diario'] = count($ingresos_diarios) > 0 ? $total / count($ingresos_diarios) : 0;
            $this->viewData['fecha_inicio'] = $fecha_inicio;
            $this->viewData['fecha_fin'] = $fecha_fin;
            $this->viewData['title'] = 'Reporte de Ingresos';
            $this->viewData['layout'] = 'main';
            
            $this->render('reportes/ingresos', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error en reporte ingresos: " . $e->getMessage());
            $this->error('Error al cargar reporte');
        }
    }
    
    /**
     * Reporte de clientes
     */
    public function clientes() {
        try {
            $fecha_inicio = $this->get('fecha_inicio') ?? date('Y-m-01');
            $fecha_fin = $this->get('fecha_fin') ?? date('Y-m-d');
            $pagina = (int)($this->get('pagina') ?? 1);
            $perPage = 25;
            $offset = ($pagina - 1) * $perPage;
            
            $stmt = $this->db->prepare("
                SELECT 
                    f.nombre_cliente,
                    COUNT(DISTINCT f.factura_id) as total_facturas,
                    SUM(f.total) as total_facturado,
                    COALESCE(SUM(CASE WHEN p.estado = 'CONFIRMADO' THEN p.monto ELSE 0 END), 0) as total_pagado,
                    MAX(f.fecha_emision) as ultima_factura
                FROM facturas f
                LEFT JOIN pagos p ON f.factura_id = p.factura_id
                WHERE f.tenant_id = ?
                    AND DATE(f.fecha_emision) BETWEEN ? AND ?
                GROUP BY f.nombre_cliente
                ORDER BY total_facturado DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$this->tenantId, $fecha_inicio, $fecha_fin, $perPage, $offset]);
            $clientes = $stmt->fetchAll();
            
            // Total de clientes
            $stmtCount = $this->db->prepare("
                SELECT COUNT(DISTINCT nombre_cliente) as total
                FROM facturas
                WHERE tenant_id = ? AND DATE(fecha_emision) BETWEEN ? AND ?
            ");
            $stmtCount->execute([$this->tenantId, $fecha_inicio, $fecha_fin]);
            $totalRegistros = $stmtCount->fetch()['total'];
            
            $this->viewData['clientes'] = $clientes;
            $this->viewData['totalRegistros'] = $totalRegistros;
            $this->viewData['pagina'] = $pagina;
            $this->viewData['totalPaginas'] = ceil($totalRegistros / $perPage);
            $this->viewData['fecha_inicio'] = $fecha_inicio;
            $this->viewData['fecha_fin'] = $fecha_fin;
            $this->viewData['title'] = 'Reporte de Clientes';
            $this->viewData['layout'] = 'main';
            
            $this->render('reportes/clientes', $this->viewData);
            
        } catch (\Exception $e) {
            $this->logError("Error en reporte clientes: " . $e->getMessage());
            $this->error('Error al cargar reporte');
        }
    }
    
    /**
     * Exportar reporte a CSV
     */
    public function exportarCSV() {
        try {
            $tipo = $this->get('tipo') ?? 'facturas';
            $fecha_inicio = $this->get('fecha_inicio') ?? date('Y-m-01');
            $fecha_fin = $this->get('fecha_fin') ?? date('Y-m-d');
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="reporte_' . $tipo . '_' . date('YmdHi') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para UTF-8
            
            if ($tipo === 'facturas') {
                fputcsv($output, ['Factura', 'Cliente', 'Fecha', 'Total', 'Pagado', 'Saldo', 'Estado']);
                
                $stmt = $this->db->prepare("
                    SELECT f.numero_factura, f.nombre_cliente, f.fecha_emision, f.total,
                           COALESCE(SUM(p.monto), 0) as total_pagado
                    FROM facturas f
                    LEFT JOIN pagos p ON f.factura_id = p.factura_id AND p.estado = 'CONFIRMADO'
                    WHERE f.tenant_id = ?
                        AND DATE(f.fecha_emision) BETWEEN ? AND ?
                    GROUP BY f.factura_id
                ");
                $stmt->execute([$this->tenantId, $fecha_inicio, $fecha_fin]);
                
                foreach ($stmt->fetchAll() as $row) {
                    fputcsv($output, [
                        $row['numero_factura'],
                        $row['nombre_cliente'],
                        $row['fecha_emision'],
                        number_format($row['total'], 2),
                        number_format($row['total_pagado'], 2),
                        number_format($row['total'] - $row['total_pagado'], 2),
                        'Pagada'
                    ]);
                }
            }
            
            fclose($output);
            exit;
            
        } catch (\Exception $e) {
            $this->logError("Error al exportar CSV: " . $e->getMessage());
            $this->error('Error al exportar');
        }
    }
}
