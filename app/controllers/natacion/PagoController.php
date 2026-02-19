<?php
/**
 * DigiSports Natación — Controlador de Pagos
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class PagoController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'NATACION'; }

    public function index() {
        try {
            $this->setupModule();
            $sedeId = $_SESSION['natacion_sede_id'] ?? null;

            $where  = 'p.npg_tenant_id = ?';
            $params = [$this->tenantId];
            if ($sedeId) { $where .= ' AND p.npg_sede_id = ?'; $params[] = (int)$sedeId; }
            $estado = $this->get('estado');
            if ($estado) { $where .= ' AND p.npg_estado = ?'; $params[] = $estado; }

            $stm = $this->db->prepare("
                SELECT p.*, i.nis_inscripcion_id, s.sed_nombre AS sede_nombre,
                       a.alu_nombres, a.alu_apellidos,
                       g.ngr_nombre AS grupo_nombre,
                       c.cli_nombres AS representante_nombre, c.cli_apellidos AS representante_apellido
                FROM natacion_pagos p
                LEFT JOIN natacion_inscripciones i ON p.npg_inscripcion_id = i.nis_inscripcion_id
                LEFT JOIN alumnos a ON i.nis_alumno_id = a.alu_alumno_id
                LEFT JOIN natacion_grupos g ON i.nis_grupo_id = g.ngr_grupo_id
                LEFT JOIN clientes c ON p.npg_cliente_id = c.cli_cliente_id
                LEFT JOIN instalaciones_sedes s ON p.npg_sede_id = s.sed_sede_id
                WHERE {$where}
                ORDER BY p.npg_fecha DESC
                LIMIT 200
            ");
            $stm->execute($params);

            $this->viewData['pagos']      = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['sede_activa'] = $sedeId;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title']      = 'Pagos';

            // Totales
            $totStm = $this->db->prepare("SELECT npg_estado, COUNT(*) AS total, SUM(npg_monto) AS monto FROM natacion_pagos WHERE npg_tenant_id = ? GROUP BY npg_estado");
            $totStm->execute([$this->tenantId]);
            $this->viewData['totales'] = $totStm->fetchAll(\PDO::FETCH_ASSOC);

            $this->renderModule('natacion/pagos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando pagos: " . $e->getMessage());
            $this->error('Error al cargar pagos');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $inscripcionId = (int)($this->post('inscripcion_id') ?? 0);
            $monto         = (float)($this->post('monto') ?? 0);
            $metodo        = $this->post('metodo_pago') ?: 'EFECTIVO';
            if (!$inscripcionId || $monto <= 0) return $this->jsonResponse(['success' => false, 'message' => 'Inscripción y monto válido son obligatorios']);

            // Obtener cliente de la inscripción
            $cliStm = $this->db->prepare("
                SELECT a.alu_representante_id FROM natacion_inscripciones i
                JOIN alumnos a ON i.nis_alumno_id = a.alu_alumno_id
                WHERE i.nis_inscripcion_id = ?
            ");
            $cliStm->execute([$inscripcionId]);
            $clienteId = $cliStm->fetchColumn() ?: null;

            $stm = $this->db->prepare("
                INSERT INTO natacion_pagos (npg_tenant_id, npg_sede_id, npg_inscripcion_id, npg_cliente_id, npg_monto, npg_metodo_pago, npg_fecha, npg_estado, npg_referencia, npg_notas)
                VALUES (?,?,?,?,?,?,CURDATE(),?,?,?)
            ");
            $sedeIdPago = (int)($this->post('sede_id') ?? 0) ?: ($_SESSION['natacion_sede_id'] ?? null);
            $stm->execute([
                $this->tenantId, $sedeIdPago, $inscripcionId, $clienteId, $monto, $metodo,
                $this->post('estado') ?: 'PENDIENTE',
                $this->post('referencia') ?: null,
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

            $stm = $this->db->prepare("
                UPDATE natacion_pagos SET npg_monto=?, npg_metodo_pago=?, npg_estado=?, npg_referencia=?, npg_notas=?
                WHERE npg_pago_id=? AND npg_tenant_id=?
            ");
            $stm->execute([
                (float)$this->post('monto'), $this->post('metodo_pago') ?: 'EFECTIVO',
                $this->post('estado') ?: 'PENDIENTE',
                $this->post('referencia') ?: null,
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

            $this->db->prepare("UPDATE natacion_pagos SET npg_estado = 'ANULADO' WHERE npg_pago_id = ? AND npg_tenant_id = ?")->execute([$id, $this->tenantId]);
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
                SELECT i.nis_inscripcion_id, a.alu_nombres, a.alu_apellidos, g.ngr_nombre AS grupo
                FROM natacion_inscripciones i
                JOIN alumnos a ON i.nis_alumno_id = a.alu_alumno_id
                JOIN natacion_grupos g ON i.nis_grupo_id = g.ngr_grupo_id
                WHERE i.nis_estado = 'ACTIVA' AND g.ngr_tenant_id = ?
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
