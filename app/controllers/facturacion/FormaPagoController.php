<?php
/**
 * DigiSports - Controlador de Formas de Pago
 * CRUD completo: listar, crear, editar, eliminar, activar/inactivar
 *
 * @package DigiSports\Controllers\Facturacion
 */

namespace App\Controllers\Facturacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class FormaPagoController extends \App\Controllers\ModuleController {

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'facturacion';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INDEX — Listar formas de pago
    // ─────────────────────────────────────────────────────────────────────────

    public function index() {
        $this->authorize('ver', 'facturacion');

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM facturacion_formas_pago
                WHERE fpa_tenant_id = ?
                ORDER BY fpa_nombre ASC
            ");
            $stmt->execute([$this->tenantId]);
            $formas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $sriCfg    = require BASE_PATH . '/config/sri.php';
            $codigosSri = $sriCfg['formas_pago'] ?? [];

            $this->viewData['formas']       = $formas;
            $this->viewData['codigos_sri']  = $codigosSri;
            $this->viewData['csrf_token']   = \Security::generateCsrfToken();
            $this->viewData['title']        = 'Formas de Pago';

            $this->renderModule('facturacion/formas_pago/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError('Error al listar formas de pago: ' . $e->getMessage());
            $this->error('Error al cargar las formas de pago');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GUARDAR — Crear o editar (distingue por fpa_id en POST)
    // ─────────────────────────────────────────────────────────────────────────

    public function guardar() {
        $this->authorize('crear', 'facturacion');

        if (!$this->isPost()) {
            return $this->jsonError('Método no permitido', 405);
        }
        if (!$this->validateCsrf()) {
            return $this->jsonError('Token de seguridad inválido', 403);
        }

        $fpa_id      = (int)($this->post('fpa_id') ?? 0);
        $nombre      = trim($this->post('fpa_nombre') ?? '');
        $codigo_sri  = trim($this->post('fpa_codigo_sri') ?? '');

        // Validaciones
        if (empty($nombre)) {
            return $this->jsonError('El nombre es obligatorio');
        }
        if (strlen($nombre) > 50) {
            return $this->jsonError('El nombre no puede superar 50 caracteres');
        }
        if (empty($codigo_sri)) {
            return $this->jsonError('El código SRI es obligatorio');
        }

        // Validar código SRI contra los permitidos
        $sriCfg     = require BASE_PATH . '/config/sri.php';
        $codigosSri = $sriCfg['formas_pago'] ?? [];
        if (!array_key_exists($codigo_sri, $codigosSri)) {
            return $this->jsonError('Código SRI no válido');
        }

        try {
            if ($fpa_id > 0) {
                // ── EDITAR ───────────────────────────────────────────────────
                // Verificar que pertenece al tenant
                $stmt = $this->db->prepare("
                    SELECT fpa_id FROM facturacion_formas_pago
                    WHERE fpa_id = ? AND fpa_tenant_id = ?
                ");
                $stmt->execute([$fpa_id, $this->tenantId]);
                if (!$stmt->fetchColumn()) {
                    return $this->jsonError('Forma de pago no encontrada', 404);
                }

                $stmt = $this->db->prepare("
                    UPDATE facturacion_formas_pago
                    SET fpa_nombre = ?, fpa_codigo_sri = ?
                    WHERE fpa_id = ? AND fpa_tenant_id = ?
                ");
                $stmt->execute([$nombre, $codigo_sri, $fpa_id, $this->tenantId]);

                $this->audit('facturacion_formas_pago', $fpa_id, 'UPDATE',
                    [], ['nombre' => $nombre, 'codigo_sri' => $codigo_sri]);

                return $this->jsonSuccess(
                    ['fpa_id' => $fpa_id, 'fpa_nombre' => $nombre, 'fpa_codigo_sri' => $codigo_sri],
                    'Forma de pago actualizada correctamente'
                );

            } else {
                // ── CREAR ────────────────────────────────────────────────────
                $stmt = $this->db->prepare("
                    INSERT INTO facturacion_formas_pago (fpa_tenant_id, fpa_nombre, fpa_codigo_sri, fpa_estado)
                    VALUES (?, ?, ?, 'ACTIVO')
                ");
                $stmt->execute([$this->tenantId, $nombre, $codigo_sri]);
                $nuevo_id = (int)$this->db->lastInsertId();

                $this->audit('facturacion_formas_pago', $nuevo_id, 'INSERT',
                    [], ['tenant_id' => $this->tenantId, 'nombre' => $nombre, 'codigo_sri' => $codigo_sri]);

                return $this->jsonSuccess(
                    ['fpa_id' => $nuevo_id, 'fpa_nombre' => $nombre, 'fpa_codigo_sri' => $codigo_sri],
                    'Forma de pago creada correctamente'
                );
            }

        } catch (\Exception $e) {
            $this->logError('Error al guardar forma de pago: ' . $e->getMessage());
            return $this->jsonError('Error al guardar: ' . $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ELIMINAR — Solo si no tiene pagos ni facturas asociadas
    // ─────────────────────────────────────────────────────────────────────────

    public function eliminar() {
        $this->authorize('eliminar', 'facturacion');

        if (!$this->isPost()) {
            return $this->jsonError('Método no permitido', 405);
        }
        if (!$this->validateCsrf()) {
            return $this->jsonError('Token de seguridad inválido', 403);
        }

        $fpa_id = (int)($this->post('fpa_id') ?? 0);

        if ($fpa_id < 1) {
            return $this->jsonError('Identificador no válido');
        }

        try {
            // Verificar que pertenece al tenant
            $stmt = $this->db->prepare("
                SELECT fpa_nombre FROM facturacion_formas_pago
                WHERE fpa_id = ? AND fpa_tenant_id = ?
            ");
            $stmt->execute([$fpa_id, $this->tenantId]);
            $forma = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$forma) {
                return $this->jsonError('Forma de pago no encontrada', 404);
            }

            // Verificar uso en pagos
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM facturacion_pagos WHERE pag_forma_pago_id = ?
            ");
            $stmt->execute([$fpa_id]);
            if ((int)$stmt->fetchColumn() > 0) {
                return $this->jsonError(
                    'No se puede eliminar: esta forma de pago tiene pagos registrados. ' .
                    'Puede inactivarla en su lugar.'
                );
            }

            // Verificar uso en facturas
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM facturacion_facturas WHERE fac_forma_pago_id = ?
            ");
            $stmt->execute([$fpa_id]);
            if ((int)$stmt->fetchColumn() > 0) {
                return $this->jsonError(
                    'No se puede eliminar: esta forma de pago está asociada a facturas. ' .
                    'Puede inactivarla en su lugar.'
                );
            }

            $stmt = $this->db->prepare("
                DELETE FROM facturacion_formas_pago WHERE fpa_id = ? AND fpa_tenant_id = ?
            ");
            $stmt->execute([$fpa_id, $this->tenantId]);

            $this->audit('facturacion_formas_pago', $fpa_id, 'DELETE',
                ['nombre' => $forma['fpa_nombre']], []);

            return $this->jsonSuccess([], 'Forma de pago eliminada');

        } catch (\Exception $e) {
            $this->logError('Error al eliminar forma de pago: ' . $e->getMessage());
            return $this->jsonError('Error al eliminar: ' . $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TOGGLE ESTADO — ACTIVO ↔ INACTIVO
    // ─────────────────────────────────────────────────────────────────────────

    public function toggleEstado() {
        $this->authorize('editar', 'facturacion');

        if (!$this->isPost()) {
            return $this->jsonError('Método no permitido', 405);
        }
        if (!$this->validateCsrf()) {
            return $this->jsonError('Token de seguridad inválido', 403);
        }

        $fpa_id = (int)($this->post('fpa_id') ?? 0);

        if ($fpa_id < 1) {
            return $this->jsonError('Identificador no válido');
        }

        try {
            $stmt = $this->db->prepare("
                SELECT fpa_nombre, fpa_estado FROM facturacion_formas_pago
                WHERE fpa_id = ? AND fpa_tenant_id = ?
            ");
            $stmt->execute([$fpa_id, $this->tenantId]);
            $forma = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$forma) {
                return $this->jsonError('Forma de pago no encontrada', 404);
            }

            $nuevoEstado  = $forma['fpa_estado'] === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';

            $stmt = $this->db->prepare("
                UPDATE facturacion_formas_pago SET fpa_estado = ?
                WHERE fpa_id = ? AND fpa_tenant_id = ?
            ");
            $stmt->execute([$nuevoEstado, $fpa_id, $this->tenantId]);

            $this->audit('facturacion_formas_pago', $fpa_id, 'UPDATE',
                ['estado' => $forma['fpa_estado']],
                ['estado' => $nuevoEstado]);

            $mensaje = $nuevoEstado === 'ACTIVO'
                ? 'Forma de pago activada'
                : 'Forma de pago inactivada';

            return $this->jsonSuccess(['nuevo_estado' => $nuevoEstado], $mensaje);

        } catch (\Exception $e) {
            $this->logError('Error al cambiar estado forma de pago: ' . $e->getMessage());
            return $this->jsonError('Error al cambiar estado: ' . $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    private function jsonSuccess($data = [], string $mensaje = 'OK'): void {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $mensaje, 'data' => $data]);
        exit;
    }

    private function jsonError(string $mensaje, int $codigo = 400): void {
        header('Content-Type: application/json');
        http_response_code($codigo);
        echo json_encode(['success' => false, 'message' => $mensaje]);
        exit;
    }
}
