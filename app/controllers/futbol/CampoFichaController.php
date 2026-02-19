<?php
/**
 * DigiSports Fútbol — Controlador de Campos de Ficha
 * Gestión de campos dinámicos para la ficha del alumno
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class CampoFichaController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    /**
     * Listar campos de ficha configurados
     */
    public function index() {
        try {
            $this->setupModule();

            $stm = $this->db->prepare("
                SELECT * FROM futbol_campos_ficha
                WHERE fcf_tenant_id = ? AND fcf_activo = 1
                ORDER BY fcf_orden, fcf_clave
            ");
            $stm->execute([$this->tenantId]);
            $this->viewData['campos'] = $stm->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Campos de Ficha';
            $this->renderModule('futbol/campoficha/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando campos de ficha: " . $e->getMessage());
            $this->error('Error al cargar campos de ficha');
        }
    }

    /**
     * Crear campo de ficha
     */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombre = trim($this->post('nombre') ?? '');
            $etiqueta = trim($this->post('etiqueta') ?? '');
            $tipo = $this->post('tipo') ?: 'TEXT';
            if (empty($nombre) || empty($etiqueta)) return $this->jsonResponse(['success' => false, 'message' => 'Nombre y etiqueta son obligatorios']);

            // Verificar nombre único
            $stmCheck = $this->db->prepare("SELECT COUNT(*) FROM futbol_campos_ficha WHERE fcf_clave = ? AND fcf_tenant_id = ? AND fcf_activo = 1");
            $stmCheck->execute([$nombre, $this->tenantId]);
            if ((int)$stmCheck->fetchColumn() > 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'Ya existe un campo con este nombre']);
            }

            // Procesar opciones JSON
            $opciones = $this->post('opciones') ?: null;
            if ($opciones && is_string($opciones)) {
                // Convertir lista separada por comas a array JSON
                $items = array_map('trim', explode(',', $opciones));
                $opciones = json_encode($items, JSON_UNESCAPED_UNICODE);
            }

            $stm = $this->db->prepare("
                INSERT INTO futbol_campos_ficha (fcf_tenant_id, fcf_clave, fcf_etiqueta, fcf_tipo, fcf_opciones,
                    fcf_requerido, fcf_orden, fcf_activo)
                VALUES (?,?,?,?,?,?,?,?)
            ");
            $stm->execute([
                $this->tenantId,
                $nombre,
                $etiqueta,
                $tipo,
                $opciones,
                (int)($this->post('requerido') ?? 0),
                (int)($this->post('orden') ?? 0),
                (int)($this->post('activo') ?? 1),
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Campo de ficha creado']);

        } catch (\Exception $e) {
            $this->logError("Error creando campo de ficha: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear campo de ficha']);
        }
    }

    /**
     * Editar campo de ficha
     */
    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $etiqueta = trim($this->post('etiqueta') ?? '');
            $tipo = $this->post('tipo') ?: 'TEXT';
            if (empty($etiqueta)) return $this->jsonResponse(['success' => false, 'message' => 'La etiqueta es obligatoria']);

            // Procesar opciones JSON
            $opciones = $this->post('opciones') ?: null;
            if ($opciones && is_string($opciones)) {
                // Convertir lista separada por comas a array JSON
                $items = array_map('trim', explode(',', $opciones));
                $opciones = json_encode($items, JSON_UNESCAPED_UNICODE);
            }

            $stm = $this->db->prepare("
                UPDATE futbol_campos_ficha 
                SET fcf_etiqueta=?, fcf_tipo=?, fcf_opciones=?, fcf_requerido=?, fcf_orden=?, fcf_activo=?
                WHERE fcf_campo_id=? AND fcf_tenant_id=?
            ");
            $stm->execute([
                $etiqueta,
                $tipo,
                $opciones,
                (int)($this->post('requerido') ?? 0),
                (int)($this->post('orden') ?? 0),
                (int)($this->post('activo') ?? 1),
                $id, $this->tenantId,
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Campo de ficha actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error editando campo de ficha: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar campo de ficha']);
        }
    }

    /**
     * Eliminar campo de ficha (soft delete)
     */
    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->prepare("UPDATE futbol_campos_ficha SET fcf_activo = 0 WHERE fcf_campo_id = ? AND fcf_tenant_id = ?")
                ->execute([$id, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Campo de ficha eliminado']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando campo de ficha: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar campo de ficha']);
        }
    }

    /**
     * Reordenar campos de ficha (drag & drop)
     */
    public function reordenar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $ordenJson = $this->post('orden');
            if (empty($ordenJson)) return $this->jsonResponse(['success' => false, 'message' => 'Datos de orden requeridos']);

            $orden = json_decode($ordenJson, true);
            if (!is_array($orden)) return $this->jsonResponse(['success' => false, 'message' => 'Formato de orden inválido']);

            $stm = $this->db->prepare("UPDATE futbol_campos_ficha SET fcf_orden = ? WHERE fcf_campo_id = ? AND fcf_tenant_id = ?");
            foreach ($orden as $item) {
                $stm->execute([(int)($item['orden'] ?? 0), (int)($item['id'] ?? 0), $this->tenantId]);
            }

            return $this->jsonResponse(['success' => true, 'message' => 'Orden actualizado']);

        } catch (\Exception $e) {
            $this->logError("Error reordenando campos de ficha: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al reordenar campos']);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
}
