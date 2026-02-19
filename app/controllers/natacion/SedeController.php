<?php
/**
 * DigiSports Natación — Controlador de Sedes
 * Gestiona las sedes/sucursales del tenant. Usa la tabla compartida instalaciones_sedes.
 * Permite ver todas las sedes, crear nuevas y seleccionar sede activa en sesión.
 * 
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class SedeController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'NATACION';
    }

    // ═══════════════════════════════════════
    // LISTADO de sedes del tenant
    // ═══════════════════════════════════════
    public function index() {
        try {
            $this->setupModule();

            // Obtener sedes con stats de natación
            $stm = $this->db->prepare("
                SELECT s.*,
                    (SELECT COUNT(*) FROM alumnos a 
                     JOIN natacion_ficha_alumno nf ON nf.nfa_alumno_id = a.alu_alumno_id AND nf.nfa_tenant_id = a.alu_tenant_id
                     WHERE a.alu_sede_id = s.sed_sede_id AND a.alu_estado = 'ACTIVO' AND nf.nfa_activo = 1) AS total_alumnos,
                    (SELECT COUNT(*) FROM natacion_piscinas WHERE npi_sede_id = s.sed_sede_id AND npi_activo = 1) AS total_piscinas,
                    (SELECT COUNT(*) FROM natacion_instructores WHERE nin_sede_id = s.sed_sede_id AND nin_activo = 1) AS total_instructores,
                    (SELECT COUNT(*) FROM natacion_grupos WHERE ngr_sede_id = s.sed_sede_id AND ngr_estado IN ('ABIERTO','EN_CURSO')) AS total_grupos,
                    (SELECT COALESCE(SUM(npg_total),0) FROM natacion_pagos 
                     WHERE npg_sede_id = s.sed_sede_id AND npg_estado = 'PAGADO' 
                     AND DATE_FORMAT(npg_fecha, '%Y-%m') = ?) AS ingresos_mes,
                    (SELECT COALESCE(SUM(neg_monto),0) FROM natacion_egresos 
                     WHERE neg_sede_id = s.sed_sede_id AND neg_estado IN ('REGISTRADO','PAGADO') 
                     AND DATE_FORMAT(neg_fecha, '%Y-%m') = ?) AS egresos_mes
                FROM instalaciones_sedes s
                WHERE s.sed_tenant_id = ? AND s.sed_estado = 'A'
                ORDER BY s.sed_es_principal DESC, s.sed_nombre
            ");
            $mesActual = date('Y-m');
            $stm->execute([$mesActual, $mesActual, $this->tenantId]);

            $this->viewData['sedes']        = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['csrf_token']   = \Security::generateCsrfToken();
            $this->viewData['sede_activa']  = $_SESSION['natacion_sede_id'] ?? null;
            $this->viewData['title']        = 'Sedes';
            $this->renderModule('natacion/sedes/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando sedes: " . $e->getMessage());
            $this->error('Error al cargar sedes');
        }
    }

    // ═══════════════════════════════════════
    // CREAR sede
    // ═══════════════════════════════════════
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombre = trim($this->post('nombre') ?? '');
            $codigo = trim($this->post('codigo') ?? '');
            $direccion = trim($this->post('direccion') ?? '');
            if (empty($nombre) || empty($codigo) || empty($direccion)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Nombre, código y dirección son obligatorios']);
            }

            // Verificar código único dentro del tenant
            $stm = $this->db->prepare("SELECT sed_sede_id FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_codigo = ?");
            $stm->execute([$this->tenantId, strtoupper($codigo)]);
            if ($stm->fetchColumn()) {
                return $this->jsonResponse(['success' => false, 'message' => 'El código de sede ya existe']);
            }

            $stm = $this->db->prepare("
                INSERT INTO instalaciones_sedes (sed_tenant_id, sed_codigo, sed_nombre, sed_descripcion, sed_direccion, 
                    sed_ciudad, sed_provincia, sed_telefono, sed_email, sed_horario_apertura, sed_horario_cierre,
                    sed_capacidad_total, sed_es_principal, sed_estado)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");
            $stm->execute([
                $this->tenantId,
                strtoupper($codigo),
                $nombre,
                $this->post('descripcion') ?: null,
                $direccion,
                $this->post('ciudad') ?: null,
                $this->post('provincia') ?: null,
                $this->post('telefono') ?: null,
                $this->post('email') ?: null,
                $this->post('horario_apertura') ?: null,
                $this->post('horario_cierre') ?: null,
                (int)($this->post('capacidad_total') ?? 0) ?: null,
                $this->post('es_principal') ? 'S' : 'N',
                'A',
            ]);

            // Si es principal, quitar la marca a las demás
            if ($this->post('es_principal')) {
                $newId = (int)$this->db->lastInsertId();
                $this->db->prepare("UPDATE instalaciones_sedes SET sed_es_principal = 'N' WHERE sed_tenant_id = ? AND sed_sede_id != ?")->execute([$this->tenantId, $newId]);
            }

            return $this->jsonResponse(['success' => true, 'message' => 'Sede creada exitosamente']);

        } catch (\Exception $e) {
            $this->logError("Error creando sede: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear sede']);
        }
    }

    // ═══════════════════════════════════════
    // EDITAR sede
    // ═══════════════════════════════════════
    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $nombre = trim($this->post('nombre') ?? '');
            $direccion = trim($this->post('direccion') ?? '');
            if (empty($nombre) || empty($direccion)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Nombre y dirección son obligatorios']);
            }

            $stm = $this->db->prepare("
                UPDATE instalaciones_sedes SET sed_nombre=?, sed_descripcion=?, sed_direccion=?,
                    sed_ciudad=?, sed_provincia=?, sed_telefono=?, sed_email=?,
                    sed_horario_apertura=?, sed_horario_cierre=?, sed_capacidad_total=?,
                    sed_es_principal=?, sed_estado=?
                WHERE sed_sede_id=? AND sed_tenant_id=?
            ");
            $esPrincipal = $this->post('es_principal') ? 'S' : 'N';
            $stm->execute([
                $nombre,
                $this->post('descripcion') ?: null,
                $direccion,
                $this->post('ciudad') ?: null,
                $this->post('provincia') ?: null,
                $this->post('telefono') ?: null,
                $this->post('email') ?: null,
                $this->post('horario_apertura') ?: null,
                $this->post('horario_cierre') ?: null,
                (int)($this->post('capacidad_total') ?? 0) ?: null,
                $esPrincipal,
                $this->post('estado') ?: 'A',
                $id, $this->tenantId,
            ]);

            if ($esPrincipal === 'S') {
                $this->db->prepare("UPDATE instalaciones_sedes SET sed_es_principal = 'N' WHERE sed_tenant_id = ? AND sed_sede_id != ?")->execute([$this->tenantId, $id]);
            }

            return $this->jsonResponse(['success' => true, 'message' => 'Sede actualizada']);

        } catch (\Exception $e) {
            $this->logError("Error editando sede: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    // ═══════════════════════════════════════
    // DESACTIVAR sede (soft)
    // ═══════════════════════════════════════
    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            // No permitir eliminar sede principal
            $stm = $this->db->prepare("SELECT sed_es_principal FROM instalaciones_sedes WHERE sed_sede_id = ? AND sed_tenant_id = ?");
            $stm->execute([$id, $this->tenantId]);
            if ($stm->fetchColumn() === 'S') {
                return $this->jsonResponse(['success' => false, 'message' => 'No se puede desactivar la sede principal']);
            }

            $this->db->prepare("UPDATE instalaciones_sedes SET sed_estado = 'I' WHERE sed_sede_id = ? AND sed_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Sede desactivada']);

        } catch (\Exception $e) {
            $this->logError("Error desactivando sede: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al desactivar']);
        }
    }

    // ═══════════════════════════════════════
    // AJAX: Seleccionar sede activa (guarda en sesión)
    // ═══════════════════════════════════════
    public function seleccionar() {
        try {
            $sedeId = (int)($this->get('id') ?? $this->post('id') ?? 0);

            if ($sedeId === 0) {
                // Quitar filtro de sede (ver todas)
                unset($_SESSION['natacion_sede_id']);
                return $this->jsonResponse(['success' => true, 'message' => 'Mostrando todas las sedes', 'sede_id' => null]);
            }

            // Verificar que la sede pertenece al tenant
            $stm = $this->db->prepare("SELECT sed_nombre FROM instalaciones_sedes WHERE sed_sede_id = ? AND sed_tenant_id = ? AND sed_estado = 'A'");
            $stm->execute([$sedeId, $this->tenantId]);
            $sede = $stm->fetchColumn();
            if (!$sede) {
                return $this->jsonResponse(['success' => false, 'message' => 'Sede no encontrada']);
            }

            $_SESSION['natacion_sede_id'] = $sedeId;
            return $this->jsonResponse(['success' => true, 'message' => "Sede activa: {$sede}", 'sede_id' => $sedeId, 'sede_nombre' => $sede]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error al seleccionar sede']);
        }
    }

    // ═══════════════════════════════════════
    // AJAX: Listar sedes para selects/dropdowns
    // ═══════════════════════════════════════
    public function listar() {
        try {
            $stm = $this->db->prepare("SELECT sed_sede_id, sed_codigo, sed_nombre, sed_ciudad, sed_es_principal FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_es_principal DESC, sed_nombre");
            $stm->execute([$this->tenantId]);
            return $this->jsonResponse(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_ASSOC)]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'data' => []]);
        }
    }

    // ═══════════════════════════════════════
    // REPORTE: Resumen financiero por sede
    // ═══════════════════════════════════════
    public function resumenFinanciero() {
        try {
            $year = (int)($this->get('year') ?? date('Y'));
            $mes  = (int)($this->get('mes') ?? date('m'));
            $periodo = sprintf('%04d-%02d', $year, $mes);

            $stm = $this->db->prepare("
                SELECT s.sed_sede_id, s.sed_nombre, s.sed_ciudad, s.sed_es_principal,
                    COALESCE(ing.total_ingresos, 0) AS total_ingresos,
                    COALESCE(ing.total_pagos, 0) AS num_pagos,
                    COALESCE(egr.total_egresos, 0) AS total_egresos,
                    COALESCE(egr.num_egresos, 0) AS num_egresos,
                    (COALESCE(ing.total_ingresos, 0) - COALESCE(egr.total_egresos, 0)) AS utilidad,
                    COALESCE(alu.total_alumnos, 0) AS total_alumnos,
                    COALESCE(grp.total_grupos, 0) AS total_grupos
                FROM instalaciones_sedes s
                LEFT JOIN (
                    SELECT npg_sede_id, SUM(npg_total) AS total_ingresos, COUNT(*) AS total_pagos
                    FROM natacion_pagos
                    WHERE npg_tenant_id = ? AND npg_estado = 'PAGADO' AND DATE_FORMAT(npg_fecha, '%Y-%m') = ?
                    GROUP BY npg_sede_id
                ) ing ON ing.npg_sede_id = s.sed_sede_id
                LEFT JOIN (
                    SELECT neg_sede_id, SUM(neg_monto) AS total_egresos, COUNT(*) AS num_egresos
                    FROM natacion_egresos
                    WHERE neg_tenant_id = ? AND neg_estado IN ('REGISTRADO','PAGADO') AND DATE_FORMAT(neg_fecha, '%Y-%m') = ?
                    GROUP BY neg_sede_id
                ) egr ON egr.neg_sede_id = s.sed_sede_id
                LEFT JOIN (
                    SELECT a.alu_sede_id, COUNT(*) AS total_alumnos
                    FROM alumnos a
                    JOIN natacion_ficha_alumno nf ON nf.nfa_alumno_id = a.alu_alumno_id AND nf.nfa_tenant_id = a.alu_tenant_id
                    WHERE a.alu_tenant_id = ? AND a.alu_estado = 'ACTIVO' AND nf.nfa_activo = 1
                    GROUP BY a.alu_sede_id
                ) alu ON alu.alu_sede_id = s.sed_sede_id
                LEFT JOIN (
                    SELECT ngr_sede_id, COUNT(*) AS total_grupos
                    FROM natacion_grupos
                    WHERE ngr_tenant_id = ? AND ngr_estado IN ('ABIERTO','EN_CURSO')
                    GROUP BY ngr_sede_id
                ) grp ON grp.ngr_sede_id = s.sed_sede_id
                WHERE s.sed_tenant_id = ? AND s.sed_estado = 'A'
                ORDER BY s.sed_es_principal DESC, s.sed_nombre
            ");
            $stm->execute([$this->tenantId, $periodo, $this->tenantId, $periodo, $this->tenantId, $this->tenantId, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_ASSOC), 'periodo' => $periodo]);

        } catch (\Exception $e) {
            $this->logError("Error en resumen financiero: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'data' => []]);
        }
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
