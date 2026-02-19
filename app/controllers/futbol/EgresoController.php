<?php
/**
 * DigiSports Fútbol — Controlador de Egresos
 * Registro de gastos operativos por sede
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class EgresoController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'FUTBOL';
    }

    // ═══════════════════════════════════════
    // LISTADO
    // ═══════════════════════════════════════
    public function index() {
        try {
            $this->setupModule();

            $where  = 'e.feg_tenant_id = ?';
            $params = [$this->tenantId];

            // Filtro por sede en sesión
            $sedeId = $_SESSION['futbol_sede_id'] ?? null;
            if ($sedeId) {
                $where .= ' AND e.feg_sede_id = ?';
                $params[] = (int)$sedeId;
            }

            $filtroSede = $this->get('sede_id');
            if ($filtroSede) {
                $where .= ' AND e.feg_sede_id = ?';
                $params[] = (int)$filtroSede;
            }

            $filtroCategoria = $this->get('categoria');
            if ($filtroCategoria) {
                $where .= ' AND e.feg_categoria = ?';
                $params[] = $filtroCategoria;
            }

            $filtroEstado = $this->get('estado');
            if ($filtroEstado) {
                $where .= ' AND e.feg_estado = ?';
                $params[] = $filtroEstado;
            }

            $filtroMes = $this->get('mes') ?: date('Y-m');
            $where .= " AND DATE_FORMAT(e.feg_fecha, '%Y-%m') = ?";
            $params[] = $filtroMes;

            $stm = $this->db->prepare("
                SELECT e.*, s.sed_nombre AS sede_nombre
                FROM futbol_egresos e
                LEFT JOIN instalaciones_sedes s ON e.feg_sede_id = s.sed_sede_id
                WHERE {$where}
                ORDER BY e.feg_fecha DESC
                LIMIT 200
            ");
            $stm->execute($params);

            // Totales por categoría
            $totStm = $this->db->prepare("
                SELECT feg_categoria, feg_estado, COUNT(*) AS total, SUM(feg_monto) AS monto
                FROM futbol_egresos
                WHERE feg_tenant_id = ? AND DATE_FORMAT(feg_fecha, '%Y-%m') = ?
                " . ($sedeId ? " AND feg_sede_id = {$sedeId}" : "") . "
                GROUP BY feg_categoria, feg_estado
                ORDER BY feg_categoria
            ");
            $totStm->execute([$this->tenantId, $filtroMes]);

            // Sedes para filtro
            $sedesStm = $this->db->prepare("SELECT sed_sede_id, sed_nombre, sed_ciudad FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_nombre");
            $sedesStm->execute([$this->tenantId]);

            $this->viewData['egresos']          = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['totales']          = $totStm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['sedes']            = $sedesStm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['filtroSede']       = $filtroSede;
            $this->viewData['filtroCategoria']  = $filtroCategoria;
            $this->viewData['filtroEstado']     = $filtroEstado;
            $this->viewData['filtroMes']        = $filtroMes;
            $this->viewData['sede_activa']      = $sedeId;
            $this->viewData['csrf_token']       = \Security::generateCsrfToken();
            $this->viewData['title']            = 'Egresos / Gastos';
            $this->renderModule('futbol/egresos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando egresos: " . $e->getMessage());
            $this->error('Error al cargar egresos');
        }
    }

    // ═══════════════════════════════════════
    // CREAR
    // ═══════════════════════════════════════
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $concepto = trim($this->post('concepto') ?? '');
            $monto    = (float)($this->post('monto') ?? 0);
            $fecha    = $this->post('fecha') ?: date('Y-m-d');

            if (empty($concepto) || $monto <= 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'Concepto y monto válido son obligatorios']);
            }

            $sedeId = (int)($this->post('sede_id') ?? 0) ?: ($_SESSION['futbol_sede_id'] ?? null);

            $stm = $this->db->prepare("
                INSERT INTO futbol_egresos (feg_tenant_id, feg_sede_id, feg_categoria, feg_concepto, feg_monto, feg_fecha,
                    feg_proveedor, feg_factura_ref, feg_metodo_pago, feg_referencia_pago, feg_estado, feg_notas)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
            ");
            $stm->execute([
                $this->tenantId,
                $sedeId,
                $this->post('categoria') ?: 'OTROS',
                $concepto,
                $monto,
                $fecha,
                $this->post('proveedor') ?: null,
                $this->post('factura_ref') ?: null,
                $this->post('metodo_pago') ?: 'EFECTIVO',
                $this->post('referencia_pago') ?: null,
                $this->post('estado') ?: 'REGISTRADO',
                $this->post('notas') ?: null,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Egreso registrado']);

        } catch (\Exception $e) {
            $this->logError("Error creando egreso: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar egreso']);
        }
    }

    // ═══════════════════════════════════════
    // EDITAR
    // ═══════════════════════════════════════
    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $stm = $this->db->prepare("
                UPDATE futbol_egresos SET feg_sede_id=?, feg_categoria=?, feg_concepto=?, feg_monto=?, feg_fecha=?,
                    feg_proveedor=?, feg_factura_ref=?, feg_metodo_pago=?, feg_referencia_pago=?, feg_estado=?, feg_notas=?
                WHERE feg_egreso_id=? AND feg_tenant_id=?
            ");
            $stm->execute([
                (int)($this->post('sede_id') ?? 0) ?: null,
                $this->post('categoria') ?: 'OTROS',
                trim($this->post('concepto') ?? ''),
                (float)($this->post('monto') ?? 0),
                $this->post('fecha') ?: date('Y-m-d'),
                $this->post('proveedor') ?: null,
                $this->post('factura_ref') ?: null,
                $this->post('metodo_pago') ?: 'EFECTIVO',
                $this->post('referencia_pago') ?: null,
                $this->post('estado') ?: 'REGISTRADO',
                $this->post('notas') ?: null,
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Egreso actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando egreso: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    // ═══════════════════════════════════════
    // ANULAR
    // ═══════════════════════════════════════
    public function anular() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE futbol_egresos SET feg_estado = 'ANULADO' WHERE feg_egreso_id = ? AND feg_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Egreso anulado']);

        } catch (\Exception $e) {
            $this->logError("Error anulando egreso: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al anular']);
        }
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
