<?php
/**
 * DigiSports Store — Controlador de Ventas (Historial)
 * Consulta, filtrado y detalle de ventas realizadas
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class VentaController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono  = 'fas fa-store';
    protected $moduloColor  = '#F59E0B';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }

    /* ═══════════════════════════════════════
     * HISTORIAL DE VENTAS
     * ═══════════════════════════════════════ */
    public function index() {
        try {
            $buscar     = trim($this->get('buscar') ?? '');
            $estado     = $this->get('estado') ?? '';
            $fechaDesde = $this->get('fecha_desde') ?? date('Y-m-01');
            $fechaHasta = $this->get('fecha_hasta') ?? date('Y-m-d');
            $tipoDoc    = $this->get('tipo_documento') ?? '';
            $pagina     = max(1, (int)($this->get('pagina') ?? 1));
            $porPagina  = 25;

            $sql = "SELECT v.*, c.cli_nombres, c.cli_apellidos, c.cli_identificacion
                    FROM store_ventas v
                    LEFT JOIN clientes c ON c.cli_cliente_id = v.ven_cliente_id AND c.cli_tenant_id = v.ven_tenant_id
                    WHERE v.ven_tenant_id = ? AND DATE(v.ven_fecha) BETWEEN ? AND ?";
            $params = [$this->tenantId, $fechaDesde, $fechaHasta];

            if (!empty($buscar)) {
                $sql .= " AND (v.ven_numero LIKE ? OR c.cli_nombres LIKE ? OR c.cli_identificacion_hash = ?)";
                $like = "%{$buscar}%";
                $idHash = \DataProtection::blindIndex($buscar);
                $params = array_merge($params, [$like, $like, $idHash]);
            }
            if (!empty($estado)) {
                $sql .= " AND v.ven_estado = ?";
                $params[] = $estado;
            }
            if (!empty($tipoDoc)) {
                $sql .= " AND v.ven_tipo_documento = ?";
                $params[] = $tipoDoc;
            }

            // Contar
            $sqlCount = str_replace('v.*, c.cli_nombres, c.cli_apellidos, c.cli_identificacion', 'COUNT(*)', $sql);
            $stmt = $this->db->prepare($sqlCount);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();
            $totalPaginas = max(1, ceil($total / $porPagina));
            $offset = ($pagina - 1) * $porPagina;

            $sql .= " ORDER BY v.ven_fecha DESC LIMIT {$porPagina} OFFSET {$offset}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $ventas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            // Descifrar datos sensibles del cliente
            foreach ($ventas as &$v) {
                $v['cli_identificacion'] = \DataProtection::decrypt($v['cli_identificacion'] ?? null);
            }
            unset($v);

            // Resumen del período
            $sqlRes = "SELECT COUNT(*) AS num_ventas, 
                              COALESCE(SUM(CASE WHEN ven_estado='COMPLETADA' THEN ven_total ELSE 0 END), 0) AS total_completadas,
                              COALESCE(SUM(CASE WHEN ven_estado='ANULADA' THEN ven_total ELSE 0 END), 0) AS total_anuladas
                       FROM store_ventas WHERE ven_tenant_id = ? AND DATE(ven_fecha) BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sqlRes);
            $stmt->execute([$this->tenantId, $fechaDesde, $fechaHasta]);
            $resumen = $stmt->fetch(\PDO::FETCH_ASSOC);

            $this->viewData['ventas']       = $ventas;
            $this->viewData['resumen']      = $resumen;
            $this->viewData['buscar']       = $buscar;
            $this->viewData['estadoFiltro'] = $estado;
            $this->viewData['tipoDocFiltro'] = $tipoDoc;
            $this->viewData['fechaDesde']   = $fechaDesde;
            $this->viewData['fechaHasta']   = $fechaHasta;
            $this->viewData['pagina']       = $pagina;
            $this->viewData['totalPaginas'] = $totalPaginas;
            $this->viewData['total']        = $total;
            $this->viewData['csrf_token']   = \Security::generateCsrfToken();
            $this->viewData['title']        = 'Historial de Ventas';

            $this->renderModule('store/ventas/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando ventas: " . $e->getMessage());
            $this->error('Error al cargar ventas');
        }
    }

    /* ═══════════════════════════════════════
     * DETALLE DE VENTA
     * ═══════════════════════════════════════ */
    public function ver() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->error('Venta no encontrada');

            $stmt = $this->db->prepare("
                SELECT v.*, c.cli_nombres, c.cli_apellidos, c.cli_identificacion, c.cli_email, c.cli_telefono,
                       cj.caj_nombre
                FROM store_ventas v
                LEFT JOIN clientes c ON c.cli_cliente_id = v.ven_cliente_id AND c.cli_tenant_id = v.ven_tenant_id
                LEFT JOIN store_caja_turnos t ON t.tur_turno_id = v.ven_turno_id
                LEFT JOIN store_cajas cj ON cj.caj_caja_id = t.tur_caja_id
                WHERE v.ven_venta_id = ? AND v.ven_tenant_id = ?
            ");
            $stmt->execute([$id, $this->tenantId]);
            $venta = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$venta) return $this->error('Venta no encontrada');

            // Descifrar datos sensibles del cliente
            $venta['cli_identificacion'] = \DataProtection::decrypt($venta['cli_identificacion'] ?? null);
            $venta['cli_email'] = \DataProtection::decrypt($venta['cli_email'] ?? null);
            $venta['cli_telefono'] = \DataProtection::decrypt($venta['cli_telefono'] ?? null);

            // Items
            $stmt = $this->db->prepare("SELECT * FROM store_venta_items WHERE vit_venta_id = ? AND vit_tenant_id = ?");
            $stmt->execute([$id, $this->tenantId]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Pagos
            $stmt = $this->db->prepare("SELECT * FROM store_venta_pagos WHERE vpg_venta_id = ? AND vpg_tenant_id = ?");
            $stmt->execute([$id, $this->tenantId]);
            $pagos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Devoluciones
            $stmt = $this->db->prepare("SELECT * FROM store_devoluciones WHERE dev_venta_id = ? AND dev_tenant_id = ?");
            $stmt->execute([$id, $this->tenantId]);
            $devoluciones = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['venta']        = $venta;
            $this->viewData['items']        = $items;
            $this->viewData['pagos']        = $pagos;
            $this->viewData['devoluciones'] = $devoluciones;
            $this->viewData['csrf_token']   = \Security::generateCsrfToken();
            $this->viewData['title']        = 'Venta ' . $venta['ven_numero'];

            $this->renderModule('store/ventas/ver', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error detalle venta: " . $e->getMessage());
            $this->error('Error al cargar venta');
        }
    }
}
