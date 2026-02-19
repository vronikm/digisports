<?php
/**
 * DigiSports Store — Controlador de Descuentos y Promociones
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class DescuentoController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono  = 'fas fa-store';
    protected $moduloColor  = '#F59E0B';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }

    public function index() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM store_descuentos WHERE dsc_tenant_id = ? ORDER BY dsc_activo DESC, dsc_fecha_inicio DESC");
            $stmt->execute([$this->tenantId]);
            $descuentos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['descuentos']  = $descuentos;
            $this->viewData['csrf_token']  = \Security::generateCsrfToken();
            $this->viewData['title']       = 'Descuentos y Promociones';

            $this->renderModule('store/descuentos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando descuentos: " . $e->getMessage());
            $this->error('Error al cargar descuentos');
        }
    }

    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombre = trim($this->post('nombre') ?? '');
            $tipo   = $this->post('tipo') ?? 'PORCENTAJE';
            $valor  = (float)($this->post('valor') ?? 0);

            if (empty($nombre) || $valor <= 0) return $this->jsonResponse(['success' => false, 'message' => 'Nombre y valor son obligatorios']);

            $stmt = $this->db->prepare("INSERT INTO store_descuentos (
                dsc_tenant_id, dsc_nombre, dsc_codigo, dsc_tipo, dsc_valor, dsc_minimo_compra,
                dsc_maximo_descuento, dsc_aplica_a, dsc_aplica_id, dsc_fecha_inicio, dsc_fecha_fin,
                dsc_usos_maximos
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $this->tenantId, $nombre,
                trim($this->post('codigo') ?? '') ?: null,
                $tipo, $valor,
                (float)($this->post('minimo_compra') ?? 0) ?: null,
                (float)($this->post('maximo_descuento') ?? 0) ?: null,
                $this->post('aplica_a') ?? 'TODOS',
                (int)($this->post('aplica_id') ?? 0) ?: null,
                $this->post('fecha_inicio') ?: null,
                $this->post('fecha_fin') ?: null,
                (int)($this->post('usos_maximos') ?? 0) ?: null
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Descuento creado']);

        } catch (\Exception $e) {
            $this->logError("Error creando descuento: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear descuento']);
        }
    }

    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            $nombre = trim($this->post('nombre') ?? '');
            if (!$id || empty($nombre)) return $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos']);

            $stmt = $this->db->prepare("UPDATE store_descuentos SET
                dsc_nombre = ?, dsc_codigo = ?, dsc_tipo = ?, dsc_valor = ?,
                dsc_minimo_compra = ?, dsc_maximo_descuento = ?, dsc_aplica_a = ?, dsc_aplica_id = ?,
                dsc_fecha_inicio = ?, dsc_fecha_fin = ?, dsc_usos_maximos = ?, dsc_activo = ?
                WHERE dsc_descuento_id = ? AND dsc_tenant_id = ?");

            $stmt->execute([
                $nombre, trim($this->post('codigo') ?? '') ?: null,
                $this->post('tipo') ?? 'PORCENTAJE', (float)($this->post('valor') ?? 0),
                (float)($this->post('minimo_compra') ?? 0) ?: null,
                (float)($this->post('maximo_descuento') ?? 0) ?: null,
                $this->post('aplica_a') ?? 'TODOS', (int)($this->post('aplica_id') ?? 0) ?: null,
                $this->post('fecha_inicio') ?: null, $this->post('fecha_fin') ?: null,
                (int)($this->post('usos_maximos') ?? 0) ?: null, (int)($this->post('activo') ?? 1),
                $id, $this->tenantId
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Descuento actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando descuento: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    public function eliminar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            $this->db->prepare("DELETE FROM store_descuentos WHERE dsc_descuento_id = ? AND dsc_tenant_id = ?")->execute([$id, $this->tenantId]);
            return $this->jsonResponse(['success' => true, 'message' => 'Descuento eliminado']);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    /** Validar cupón (API para POS) */
    public function validarCupon() {
        try {
            $codigo = trim($this->get('codigo') ?? '');
            $total  = (float)($this->get('total') ?? 0);

            if (empty($codigo)) return $this->jsonResponse(['success' => false, 'message' => 'Código vacío']);

            $stmt = $this->db->prepare("SELECT * FROM store_descuentos WHERE dsc_codigo = ? AND dsc_tenant_id = ? AND dsc_activo = 1");
            $stmt->execute([$codigo, $this->tenantId]);
            $desc = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$desc) return $this->jsonResponse(['success' => false, 'message' => 'Cupón no válido']);

            // Verificar vigencia
            if ($desc['dsc_fecha_inicio'] && date('Y-m-d') < $desc['dsc_fecha_inicio'])
                return $this->jsonResponse(['success' => false, 'message' => 'Cupón aún no vigente']);
            if ($desc['dsc_fecha_fin'] && date('Y-m-d') > $desc['dsc_fecha_fin'])
                return $this->jsonResponse(['success' => false, 'message' => 'Cupón expirado']);

            // Verificar usos
            if ($desc['dsc_usos_maximos'] && $desc['dsc_usos_actuales'] >= $desc['dsc_usos_maximos'])
                return $this->jsonResponse(['success' => false, 'message' => 'Cupón agotado']);

            // Verificar mínimo
            if ($desc['dsc_minimo_compra'] && $total < (float)$desc['dsc_minimo_compra'])
                return $this->jsonResponse(['success' => false, 'message' => 'Compra mínima: $' . number_format($desc['dsc_minimo_compra'], 2)]);

            // Calcular descuento
            $montoDescuento = 0;
            if ($desc['dsc_tipo'] === 'PORCENTAJE') {
                $montoDescuento = round($total * ($desc['dsc_valor'] / 100), 2);
                if ($desc['dsc_maximo_descuento']) {
                    $montoDescuento = min($montoDescuento, (float)$desc['dsc_maximo_descuento']);
                }
            } else {
                $montoDescuento = (float)$desc['dsc_valor'];
            }

            return $this->jsonResponse([
                'success' => true, 
                'descuento' => [
                    'id' => $desc['dsc_descuento_id'],
                    'nombre' => $desc['dsc_nombre'],
                    'tipo' => $desc['dsc_tipo'],
                    'valor' => (float)$desc['dsc_valor'],
                    'monto_descuento' => $montoDescuento
                ]
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error validando cupón']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data); exit; }
}
