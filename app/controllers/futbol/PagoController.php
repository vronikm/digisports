<?php
/**
 * DigiSports Fútbol — Controlador de Pagos
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class PagoController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    public function index() {
        try {
            $this->setupModule();
            $sedeId = $_SESSION['futbol_sede_id'] ?? null;

            $where  = 'p.fpg_tenant_id = ?';
            $params = [$this->tenantId];
            if ($sedeId) { $where .= ' AND p.fpg_sede_id = ?'; $params[] = (int)$sedeId; }
            $estado = $this->get('estado');
            if ($estado) { $where .= ' AND p.fpg_estado = ?'; $params[] = $estado; }

            $stm = $this->db->prepare("
                SELECT p.*, s.sed_nombre AS sede_nombre,
                       a.alu_nombres, a.alu_apellidos,
                       c.cli_nombres AS rep_nombres, c.cli_apellidos AS rep_apellidos,
                       c.cli_telefono AS representante_telefono,
                       c.cli_email AS representante_email,
                       g.fgr_nombre AS grupo_nombre
                FROM futbol_pagos p
                LEFT JOIN alumnos a ON p.fpg_alumno_id = a.alu_alumno_id
                LEFT JOIN clientes c ON a.alu_representante_id = c.cli_cliente_id AND c.cli_tenant_id = a.alu_tenant_id
                LEFT JOIN futbol_grupos g ON p.fpg_grupo_id = g.fgr_grupo_id
                LEFT JOIN instalaciones_sedes s ON p.fpg_sede_id = s.sed_sede_id
                WHERE {$where}
                ORDER BY p.fpg_fecha DESC
                LIMIT 200
            ");
            $stm->execute($params);

            // Descifrar datos sensibles del representante (LOPDP)
            $pagos = $stm->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($pagos as &$p) {
                if (!empty($p['representante_telefono'])) $p['representante_telefono'] = \DataProtection::decrypt($p['representante_telefono']);
                if (!empty($p['representante_email']))    $p['representante_email']    = \DataProtection::decrypt($p['representante_email']);
                $p['representante_nombre'] = trim(($p['rep_nombres'] ?? '') . ' ' . ($p['rep_apellidos'] ?? ''));
            }
            unset($p);
            $this->viewData['pagos']       = $pagos;
            $this->viewData['sede_activa'] = $sedeId;
            $this->viewData['csrf_token']  = \Security::generateCsrfToken();
            $this->viewData['title']       = 'Pagos';

            // Cargar sedes, alumnos y grupos para el formulario
            $stmSedes = $this->db->prepare("SELECT sed_sede_id, sed_nombre FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_nombre");
            $stmSedes->execute([$this->tenantId]);
            $this->viewData['sedes'] = $stmSedes->fetchAll(\PDO::FETCH_ASSOC);

            $stmAlumnos = $this->db->prepare("SELECT a.alu_alumno_id, a.alu_nombres, a.alu_apellidos FROM alumnos a JOIN futbol_ficha_alumno f ON a.alu_alumno_id = f.ffa_alumno_id AND f.ffa_activo = 1 WHERE a.alu_tenant_id = ? AND a.alu_estado = 'ACTIVO' ORDER BY a.alu_apellidos, a.alu_nombres");
            $stmAlumnos->execute([$this->tenantId]);
            $this->viewData['alumnos'] = $stmAlumnos->fetchAll(\PDO::FETCH_ASSOC);

            $grupoWhere = 'fgr_tenant_id = ? AND fgr_estado IN ("ABIERTO","EN_CURSO")';
            $grupoParams = [$this->tenantId];
            if ($sedeId) { $grupoWhere .= ' AND fgr_sede_id = ?'; $grupoParams[] = (int)$sedeId; }
            $stmGrupos = $this->db->prepare("SELECT fgr_grupo_id, fgr_nombre FROM futbol_grupos WHERE {$grupoWhere} ORDER BY fgr_nombre");
            $stmGrupos->execute($grupoParams);
            $this->viewData['grupos'] = $stmGrupos->fetchAll(\PDO::FETCH_ASSOC);

            // Totales
            $totStm = $this->db->prepare("SELECT fpg_estado, COUNT(*) AS total, SUM(fpg_total) AS monto FROM futbol_pagos WHERE fpg_tenant_id = ? GROUP BY fpg_estado");
            $totStm->execute([$this->tenantId]);
            $this->viewData['totales'] = $totStm->fetchAll(\PDO::FETCH_ASSOC);

            $this->renderModule('futbol/pagos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando pagos: " . $e->getMessage());
            $this->error('Error al cargar pagos');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $alumnoId = (int)($this->post('alumno_id') ?? 0);
            $grupoId  = (int)($this->post('grupo_id') ?? 0);
            $monto    = (float)($this->post('monto') ?? 0);
            $metodo   = $this->post('metodo_pago') ?: 'EFECTIVO';
            if (!$alumnoId || !$grupoId || $monto <= 0) return $this->jsonResponse(['success' => false, 'message' => 'Alumno, grupo y monto válido son obligatorios']);

            $descuento   = (float)($this->post('descuento') ?? 0);
            $recargoMora = (float)($this->post('recargo_mora') ?? 0);
            $total       = $monto - $descuento + $recargoMora;

            $sedeIdPago = (int)($this->post('sede_id') ?? 0) ?: ($_SESSION['futbol_sede_id'] ?? null);

            $stm = $this->db->prepare("
                INSERT INTO futbol_pagos (fpg_tenant_id, fpg_sede_id, fpg_alumno_id, fpg_grupo_id,
                    fpg_tipo, fpg_mes_correspondiente, fpg_monto, fpg_descuento,
                    fpg_recargo_mora, fpg_total, fpg_metodo_pago, fpg_referencia, fpg_fecha, fpg_estado, fpg_notas)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,CURDATE(),?,?)
            ");
            $stm->execute([
                $this->tenantId,
                $sedeIdPago,
                $alumnoId,
                $grupoId,
                $this->post('tipo') ?: 'MENSUALIDAD',
                $this->post('mes_correspondiente') ?: null,
                $monto,
                $descuento,
                $recargoMora,
                $total,
                $metodo,
                $this->post('referencia') ?: null,
                $this->post('estado') ?: 'PENDIENTE',
                $this->post('notas') ?: null,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Pago registrado']);

        } catch (\Exception $e) {
            $this->logError("Error creando pago: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar pago']);
        }
    }

    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $monto       = (float)($this->post('monto') ?? 0);
            $descuento   = (float)($this->post('descuento') ?? 0);
            $recargoMora = (float)($this->post('recargo_mora') ?? 0);
            $total       = $monto - $descuento + $recargoMora;

            $stm = $this->db->prepare("
                UPDATE futbol_pagos SET fpg_tipo=?, fpg_mes_correspondiente=?, fpg_monto=?,
                    fpg_descuento=?, fpg_recargo_mora=?, fpg_total=?, fpg_metodo_pago=?, fpg_referencia=?,
                    fpg_estado=?, fpg_notas=?
                WHERE fpg_pago_id=? AND fpg_tenant_id=?
            ");
            $stm->execute([
                $this->post('tipo') ?: 'MENSUALIDAD',
                $this->post('mes_correspondiente') ?: null,
                $monto,
                $descuento,
                $recargoMora,
                $total,
                $this->post('metodo_pago') ?: 'EFECTIVO',
                $this->post('referencia') ?: null,
                $this->post('estado') ?: 'PENDIENTE',
                $this->post('notas') ?: null,
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Pago actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando pago: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    public function anular() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE futbol_pagos SET fpg_estado = 'ANULADO' WHERE fpg_pago_id = ? AND fpg_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Pago anulado']);

        } catch (\Exception $e) {
            $this->logError("Error anulando pago: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al anular']);
        }
    }

    /** AJAX: Buscar inscripciones activas */
    public function buscarInscripciones() {
        try {
            $q = trim($this->get('q') ?? '');
            $stm = $this->db->prepare("
                SELECT i.fin_inscripcion_id, a.alu_alumno_id, a.alu_nombres, a.alu_apellidos, g.fgr_nombre AS grupo
                FROM futbol_inscripciones i
                JOIN alumnos a ON i.fin_alumno_id = a.alu_alumno_id
                JOIN futbol_grupos g ON i.fin_grupo_id = g.fgr_grupo_id
                WHERE i.fin_estado = 'ACTIVA' AND i.fin_tenant_id = ?
                AND (a.alu_nombres LIKE ? OR a.alu_apellidos LIKE ?)
                LIMIT 20
            ");
            $like = "%{$q}%";
            $stm->execute([$this->tenantId, $like, $like]);
            return $this->jsonResponse(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_ASSOC)]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'data' => []]);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
