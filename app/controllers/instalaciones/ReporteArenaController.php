<?php
/**
 * DigiSports Arena - Controlador de Reportes
 * Ingresos, ocupación, top clientes, movimientos monedero
 * 
 * @package DigiSports\Controllers\Instalaciones
 * @version 1.0.0
 */

namespace App\Controllers\Instalaciones;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class ReporteArenaController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'Reportes Arena';
    protected $moduloIcono = 'fas fa-chart-line';
    protected $moduloColor = '#8B5CF6';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'ARENA';
    }

    /**
     * Vista principal de reportes con resumen general
     */
    public function index() {
        $tenantId = $_SESSION['tenant_id'] ?? $_SESSION['usu_tenant_id'] ?? 1;
        $periodo = $_GET['periodo'] ?? 'mes';
        $fechas = $this->calcularRango($periodo);

        $resumen = $this->getResumenIngresos($tenantId, $fechas['desde'], $fechas['hasta']);
        $chartDiario = $this->getIngresosDiarios($tenantId, $fechas['desde'], $fechas['hasta']);
        $topClientes = $this->getTopClientes($tenantId, $fechas['desde'], $fechas['hasta']);
        $ocupacion = $this->getOcupacionCanchas($tenantId, $fechas['desde'], $fechas['hasta']);
        $movMonedero = $this->getMovimientosMonedero($tenantId, $fechas['desde'], $fechas['hasta']);

        $this->renderModule('instalaciones/reportes/index', [
            'resumen'         => $resumen,
            'chart_diario'    => $chartDiario,
            'top_clientes'    => $topClientes,
            'ocupacion'       => $ocupacion,
            'mov_monedero'    => $movMonedero,
            'periodo'         => $periodo,
            'fecha_desde'     => $fechas['desde'],
            'fecha_hasta'     => $fechas['hasta'],
            'pageTitle'       => 'Reportes Arena',
        ]);
    }

    /**
     * Reporte detallado de ingresos (JSON para export)
     */
    public function ingresos() {
        $tenantId = $_SESSION['tenant_id'] ?? $_SESSION['usu_tenant_id'] ?? 1;
        $desde = $_GET['desde'] ?? date('Y-m-01');
        $hasta = $_GET['hasta'] ?? date('Y-m-d');

        $detalle = $this->getDetalleIngresos($tenantId, $desde, $hasta);

        $this->renderModule('instalaciones/reportes/ingresos', [
            'detalle'     => $detalle,
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'pageTitle'   => 'Detalle de Ingresos',
        ]);
    }

    // ═══════════ Helpers privados ═══════════

    private function calcularRango($periodo) {
        $hoy = date('Y-m-d');
        switch ($periodo) {
            case 'semana':
                $desde = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'mes':
                $desde = date('Y-m-01');
                break;
            case 'trimestre':
                $desde = date('Y-m-d', strtotime('-3 months'));
                break;
            case 'anio':
                $desde = date('Y-01-01');
                break;
            default:
                $desde = $_GET['desde'] ?? date('Y-m-01');
                $hoy = $_GET['hasta'] ?? $hoy;
        }
        return ['desde' => $desde, 'hasta' => $hoy];
    }

    /**
     * Resumen general: ingresos reservas, entradas, monedero, totales
     */
    private function getResumenIngresos($tenantId, $desde, $hasta) {
        $r = [
            'total_pagos_reservas' => 0,
            'total_entradas'       => 0,
            'total_recargas'       => 0,
            'total_general'        => 0,
            'num_pagos'            => 0,
            'num_entradas'         => 0,
            'num_reservas'         => 0,
            'pendiente_cobro'      => 0,
        ];

        try {
            // Pagos de reservas
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as num, COALESCE(SUM(rpa_monto),0) as total
                FROM instalaciones_reserva_pagos
                WHERE rpa_tenant_id = ? AND rpa_fecha >= ? AND rpa_fecha <= ? AND rpa_estado = 'COMPLETADO'
            ");
            $stmt->execute([$tenantId, $desde, $hasta]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $r['total_pagos_reservas'] = (float)$row['total'];
            $r['num_pagos'] = (int)$row['num'];

            // Entradas
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as num, COALESCE(SUM(ent_monto_total),0) as total
                FROM instalaciones_entradas
                WHERE ent_tenant_id = ? AND ent_fecha >= ? AND ent_fecha <= ? AND ent_estado = 'ACTIVA'
            ");
            $stmt->execute([$tenantId, $desde, $hasta]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $r['total_entradas'] = (float)$row['total'];
            $r['num_entradas'] = (int)$row['num'];

            // Recargas monedero
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(mov_monto),0) as total
                FROM instalaciones_abono_movimientos
                WHERE mov_tenant_id = ? AND mov_fecha >= ? AND mov_fecha <= ? AND mov_tipo = 'RECARGA'
            ");
            $stmt->execute([$tenantId, $desde, $hasta]);
            $r['total_recargas'] = (float)$stmt->fetchColumn();

            $r['total_general'] = $r['total_pagos_reservas'] + $r['total_entradas'];

            // Reservas en período
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM reservas
                WHERE tenant_id = ? AND fecha_reserva >= ? AND fecha_reserva <= ?
            ");
            $stmt->execute([$tenantId, $desde, $hasta]);
            $r['num_reservas'] = (int)$stmt->fetchColumn();

            // Pendiente de cobro
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(saldo_pendiente),0)
                FROM reservas
                WHERE tenant_id = ? AND fecha_reserva >= ? AND fecha_reserva <= ? AND estado_pago IN ('PENDIENTE','PARCIAL') AND estado != 'CANCELADA'
            ");
            $stmt->execute([$tenantId, $desde, $hasta]);
            $r['pendiente_cobro'] = (float)$stmt->fetchColumn();

        } catch (\Exception $e) {
            error_log("Reporte resumen error: " . $e->getMessage());
        }

        return $r;
    }

    /**
     * Ingresos diarios para gráfico de línea
     */
    private function getIngresosDiarios($tenantId, $desde, $hasta) {
        $labels = [];
        $dataPagos = [];
        $dataEntradas = [];

        try {
            $inicio = new \DateTime($desde);
            $fin = new \DateTime($hasta);
            $fin->modify('+1 day');
            $interval = new \DateInterval('P1D');
            $rango = new \DatePeriod($inicio, $interval, $fin);

            foreach ($rango as $dia) {
                $fecha = $dia->format('Y-m-d');
                $labels[] = $dia->format('d/m');

                // Pagos reservas
                $stmt = $this->db->prepare("
                    SELECT COALESCE(SUM(rpa_monto),0) FROM instalaciones_reserva_pagos
                    WHERE rpa_tenant_id = ? AND DATE(rpa_fecha) = ? AND rpa_estado = 'COMPLETADO'
                ");
                $stmt->execute([$tenantId, $fecha]);
                $dataPagos[] = (float)$stmt->fetchColumn();

                // Entradas
                $stmt = $this->db->prepare("
                    SELECT COALESCE(SUM(ent_monto_total),0) FROM instalaciones_entradas
                    WHERE ent_tenant_id = ? AND ent_fecha = ? AND ent_estado = 'ACTIVA'
                ");
                $stmt->execute([$tenantId, $fecha]);
                $dataEntradas[] = (float)$stmt->fetchColumn();
            }
        } catch (\Exception $e) {
            error_log("Reporte diario error: " . $e->getMessage());
        }

        return ['labels' => $labels, 'pagos' => $dataPagos, 'entradas' => $dataEntradas];
    }

    /**
     * Top 10 clientes por ingresos generados
     */
    private function getTopClientes($tenantId, $desde, $hasta) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.cli_cliente_id,
                    CONCAT(c.cli_nombres, ' ', c.cli_apellidos) as nombre,
                    COUNT(DISTINCT r.res_reserva_id) as num_reservas,
                    COALESCE(SUM(p.rpa_monto), 0) as total_pagado
                FROM clientes c
                INNER JOIN instalaciones_reservas r ON c.cli_cliente_id = r.res_cliente_id
                LEFT JOIN instalaciones_reserva_pagos p ON r.res_reserva_id = p.rpa_reserva_id AND p.rpa_estado = 'COMPLETADO'
                WHERE r.res_tenant_id = ? AND r.res_fecha_reserva >= ? AND r.res_fecha_reserva <= ?
                GROUP BY c.cli_cliente_id, c.cli_nombres, c.cli_apellidos
                ORDER BY total_pagado DESC
                LIMIT 10
            ");
            $stmt->execute([$tenantId, $desde, $hasta]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Reporte top clientes error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ocupación por cancha en el período
     */
    private function getOcupacionCanchas($tenantId, $desde, $hasta) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.nombre as cancha,
                    c.tipo,
                    COUNT(r.reserva_id) as total_reservas,
                    COALESCE(SUM(r.precio_total), 0) as ingreso_total
                FROM canchas c
                LEFT JOIN reservas r ON c.instalacion_id = r.instalacion_id 
                    AND r.fecha_reserva >= ? AND r.fecha_reserva <= ?
                    AND r.estado IN ('CONFIRMADA','COMPLETADA','PENDIENTE')
                WHERE c.tenant_id = ? AND c.estado = 'ACTIVO'
                GROUP BY c.cancha_id, c.nombre, c.tipo
                ORDER BY total_reservas DESC
            ");
            $stmt->execute([$desde, $hasta, $tenantId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Reporte ocupación error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Resumen de movimientos del monedero
     */
    private function getMovimientosMonedero($tenantId, $desde, $hasta) {
        $result = ['recargas' => 0, 'consumos' => 0, 'saldo_actual' => 0, 'cuentas_activas' => 0];
        
        try {
            // Recargas en período
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(mov_monto),0) FROM instalaciones_abono_movimientos
                WHERE mov_tenant_id = ? AND mov_fecha >= ? AND mov_fecha <= ? AND mov_tipo = 'RECARGA'
            ");
            $stmt->execute([$tenantId, $desde, $hasta]);
            $result['recargas'] = (float)$stmt->fetchColumn();

            // Consumos en período
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(mov_monto),0) FROM instalaciones_abono_movimientos
                WHERE mov_tenant_id = ? AND mov_fecha >= ? AND mov_fecha <= ? AND mov_tipo = 'CONSUMO'
            ");
            $stmt->execute([$tenantId, $desde, $hasta]);
            $result['consumos'] = (float)$stmt->fetchColumn();

            // Saldo total actual
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(abo_saldo),0), COUNT(*) 
                FROM instalaciones_abonos 
                WHERE abo_tenant_id = ? AND abo_estado = 'ACTIVO'
            ");
            $stmt->execute([$tenantId]);
            $row = $stmt->fetch(\PDO::FETCH_NUM);
            $result['saldo_actual'] = (float)$row[0];
            $result['cuentas_activas'] = (int)$row[1];

        } catch (\Exception $e) {
            error_log("Reporte monedero error: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * Detalle de ingresos para vista ingresos
     */
    private function getDetalleIngresos($tenantId, $desde, $hasta) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.rpa_pago_id, p.rpa_monto, p.rpa_metodo_pago, p.rpa_fecha,
                       p.rpa_referencia, p.rpa_reserva_id,
                       CONCAT(cl.cli_nombres, ' ', cl.cli_apellidos) as cliente_nombre,
                       c.nombre as cancha_nombre
                FROM instalaciones_reserva_pagos p
                INNER JOIN instalaciones_reservas r ON p.rpa_reserva_id = r.res_reserva_id
                LEFT JOIN clientes cl ON r.res_cliente_id = cl.cli_cliente_id
                LEFT JOIN canchas c ON r.res_instalacion_id = c.instalacion_id
                WHERE p.rpa_tenant_id = ? AND p.rpa_fecha >= ? AND p.rpa_fecha <= ? AND p.rpa_estado = 'COMPLETADO'
                ORDER BY p.rpa_fecha DESC
            ");
            $stmt->execute([$tenantId, $desde, $hasta]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Detalle ingresos error: " . $e->getMessage());
            return [];
        }
    }
}
