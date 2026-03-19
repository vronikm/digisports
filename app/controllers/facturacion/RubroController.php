<?php
/**
 * DigiSports - Controlador de Rubros de Facturación
 * CRUD completo: listar, crear, editar, activar/inactivar, eliminar.
 * Un rubro define un concepto (ej. Mensualidad) con su configuración de IVA.
 *
 * @package DigiSports\Controllers\Facturacion
 */

namespace App\Controllers\Facturacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class RubroController extends \App\Controllers\ModuleController {

    /** Porcentajes de IVA válidos según normativa SRI Ecuador */
    private const PORCENTAJES_IVA_VALIDOS = [0, 5, 12, 14, 15];

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'facturacion';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INDEX — Listar rubros
    // ─────────────────────────────────────────────────────────────────────────

    public function index() {
        $this->authorize('ver', 'facturacion');

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM facturacion_rubros
                WHERE rub_tenant_id = ?
                ORDER BY rub_nombre ASC
            ");
            $stmt->execute([$this->tenantId]);
            $rubros = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['rubros']      = $rubros;
            $this->viewData['csrf_token']  = \Security::generateCsrfToken();
            $this->viewData['title']       = 'Rubros de Facturación';

            $this->renderModule('facturacion/rubros/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError('Error al listar rubros: ' . $e->getMessage());
            $this->error('Error al cargar los rubros');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GUARDAR — Crear o editar (distingue por rub_id en POST)
    // ─────────────────────────────────────────────────────────────────────────

    public function guardar() {
        $this->authorize('crear', 'facturacion');

        if (!$this->isPost()) {
            return $this->jsonError('Método no permitido', 405);
        }
        if (!$this->validateCsrf()) {
            return $this->jsonError('Token de seguridad inválido', 403);
        }

        $rub_id      = (int)($this->post('rub_id')      ?? 0);
        $codigo      = strtoupper(trim($this->post('rub_codigo')      ?? ''));
        $nombre      = trim($this->post('rub_nombre')      ?? '');
        $descripcion = trim($this->post('rub_descripcion') ?? '');
        $aplica_iva  = (int)(bool)($this->post('rub_aplica_iva') ?? 0);
        $pct_iva     = $aplica_iva ? (int)(float)($this->post('rub_porcentaje_iva') ?? 15) : 0;

        // ── Validaciones ─────────────────────────────────────────────────────
        if (empty($nombre)) {
            return $this->jsonError('El nombre del rubro es obligatorio');
        }
        if (strlen($nombre) > 100) {
            return $this->jsonError('El nombre no puede superar 100 caracteres');
        }
        if (!empty($codigo) && strlen($codigo) > 20) {
            return $this->jsonError('El código no puede superar 20 caracteres');
        }
        if (!in_array($pct_iva, self::PORCENTAJES_IVA_VALIDOS, true)) {
            return $this->jsonError('Porcentaje de IVA no válido');
        }

        try {
            if ($rub_id > 0) {
                // ── EDITAR ───────────────────────────────────────────────────
                $stmt = $this->db->prepare("
                    SELECT rub_id FROM facturacion_rubros
                    WHERE rub_id = ? AND rub_tenant_id = ?
                ");
                $stmt->execute([$rub_id, $this->tenantId]);
                if (!$stmt->fetchColumn()) {
                    return $this->jsonError('Rubro no encontrado', 404);
                }

                $stmt = $this->db->prepare("
                    UPDATE facturacion_rubros
                    SET rub_codigo        = ?,
                        rub_nombre        = ?,
                        rub_descripcion   = ?,
                        rub_aplica_iva    = ?,
                        rub_porcentaje_iva = ?
                    WHERE rub_id = ? AND rub_tenant_id = ?
                ");
                $stmt->execute([
                    $codigo ?: null, $nombre, $descripcion ?: null,
                    $aplica_iva, $pct_iva,
                    $rub_id, $this->tenantId,
                ]);

                $this->audit('facturacion_rubros', $rub_id, 'UPDATE', [], [
                    'nombre' => $nombre, 'aplica_iva' => $aplica_iva, 'pct_iva' => $pct_iva,
                ]);

                return $this->jsonSuccess(
                    $this->buildRowData($rub_id, $codigo, $nombre, $descripcion, $aplica_iva, $pct_iva),
                    'Rubro actualizado correctamente'
                );

            } else {
                // ── CREAR ────────────────────────────────────────────────────
                $stmt = $this->db->prepare("
                    INSERT INTO facturacion_rubros
                        (rub_tenant_id, rub_codigo, rub_nombre, rub_descripcion,
                         rub_aplica_iva, rub_porcentaje_iva, rub_estado)
                    VALUES (?, ?, ?, ?, ?, ?, 'ACTIVO')
                ");
                $stmt->execute([
                    $this->tenantId,
                    $codigo ?: null, $nombre, $descripcion ?: null,
                    $aplica_iva, $pct_iva,
                ]);
                $nuevo_id = (int)$this->db->lastInsertId();

                $this->audit('facturacion_rubros', $nuevo_id, 'INSERT', [], [
                    'tenant_id' => $this->tenantId, 'nombre' => $nombre,
                    'aplica_iva' => $aplica_iva, 'pct_iva' => $pct_iva,
                ]);

                return $this->jsonSuccess(
                    $this->buildRowData($nuevo_id, $codigo, $nombre, $descripcion, $aplica_iva, $pct_iva, 'ACTIVO'),
                    'Rubro creado correctamente'
                );
            }

        } catch (\Exception $e) {
            $this->logError('Error al guardar rubro: ' . $e->getMessage());
            return $this->jsonError('Error al guardar: ' . $e->getMessage(), 500);
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

        $rub_id = (int)($this->post('rub_id') ?? 0);
        if ($rub_id < 1) {
            return $this->jsonError('Identificador no válido');
        }

        try {
            $stmt = $this->db->prepare("
                SELECT rub_nombre, rub_estado FROM facturacion_rubros
                WHERE rub_id = ? AND rub_tenant_id = ?
            ");
            $stmt->execute([$rub_id, $this->tenantId]);
            $rubro = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$rubro) {
                return $this->jsonError('Rubro no encontrado', 404);
            }

            $nuevoEstado = $rubro['rub_estado'] === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';

            $stmt = $this->db->prepare("
                UPDATE facturacion_rubros SET rub_estado = ?
                WHERE rub_id = ? AND rub_tenant_id = ?
            ");
            $stmt->execute([$nuevoEstado, $rub_id, $this->tenantId]);

            $this->audit('facturacion_rubros', $rub_id, 'UPDATE',
                ['estado' => $rubro['rub_estado']],
                ['estado' => $nuevoEstado]);

            $mensaje = $nuevoEstado === 'ACTIVO' ? 'Rubro activado' : 'Rubro inactivado';

            return $this->jsonSuccess(['nuevo_estado' => $nuevoEstado], $mensaje);

        } catch (\Exception $e) {
            $this->logError('Error al cambiar estado de rubro: ' . $e->getMessage());
            return $this->jsonError('Error al cambiar estado: ' . $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ELIMINAR — Solo si no está referenciado en líneas de factura
    // ─────────────────────────────────────────────────────────────────────────

    public function eliminar() {
        $this->authorize('eliminar', 'facturacion');

        if (!$this->isPost()) {
            return $this->jsonError('Método no permitido', 405);
        }
        if (!$this->validateCsrf()) {
            return $this->jsonError('Token de seguridad inválido', 403);
        }

        $rub_id = (int)($this->post('rub_id') ?? 0);
        if ($rub_id < 1) {
            return $this->jsonError('Identificador no válido');
        }

        try {
            $stmt = $this->db->prepare("
                SELECT rub_nombre FROM facturacion_rubros
                WHERE rub_id = ? AND rub_tenant_id = ?
            ");
            $stmt->execute([$rub_id, $this->tenantId]);
            $rubro = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$rubro) {
                return $this->jsonError('Rubro no encontrado', 404);
            }

            $stmt = $this->db->prepare("
                DELETE FROM facturacion_rubros
                WHERE rub_id = ? AND rub_tenant_id = ?
            ");
            $stmt->execute([$rub_id, $this->tenantId]);

            $this->audit('facturacion_rubros', $rub_id, 'DELETE',
                ['nombre' => $rubro['rub_nombre']], []);

            return $this->jsonSuccess([], 'Rubro eliminado');

        } catch (\Exception $e) {
            $this->logError('Error al eliminar rubro: ' . $e->getMessage());
            return $this->jsonError('Error al eliminar: ' . $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    private function buildRowData(
        int $id, string $codigo, string $nombre, string $descripcion,
        int $aplica_iva, float $pct_iva, string $estado = 'ACTIVO'
    ): array {
        return [
            'rub_id'            => $id,
            'rub_codigo'        => $codigo,
            'rub_nombre'        => $nombre,
            'rub_descripcion'   => $descripcion,
            'rub_aplica_iva'    => $aplica_iva,
            'rub_porcentaje_iva'=> $pct_iva,
            'rub_estado'        => $estado,
        ];
    }

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
