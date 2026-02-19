<?php
/**
 * DigiSports Store — Controlador de Clientes (CRM + Fidelización)
 * 
 * Arquitectura: Los datos personales del cliente viven en la tabla compartida
 * `clientes` (usada por todos los subsistemas). La tabla `store_clientes` es
 * una extensión que almacena solo datos específicos del módulo Store
 * (puntos, categoría, notas tienda, marketing, métricas de compra).
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class ClienteController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono  = 'fas fa-store';
    protected $moduloColor  = '#F59E0B';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }

    /**
     * SQL base para obtener datos de cliente unificados:
     * tabla compartida `clientes` + extensión `store_clientes`
     */
    private function clienteSelectSQL() {
        return "SELECT c.cli_cliente_id, c.cli_tenant_id, c.cli_tipo_identificacion, c.cli_identificacion,
                       c.cli_nombres, c.cli_apellidos, c.cli_email, c.cli_telefono, c.cli_celular,
                       c.cli_direccion, c.cli_fecha_nacimiento, c.cli_estado,
                       COALESCE(sc.scl_id, 0) AS scl_id,
                       COALESCE(sc.scl_categoria, 'NUEVO') AS scl_categoria,
                       COALESCE(sc.scl_puntos_acumulados, 0) AS scl_puntos_acumulados,
                       COALESCE(sc.scl_puntos_canjeados, 0) AS scl_puntos_canjeados,
                       COALESCE(sc.scl_puntos_disponibles, 0) AS scl_puntos_disponibles,
                       COALESCE(sc.scl_total_compras, 0) AS scl_total_compras,
                       COALESCE(sc.scl_num_compras, 0) AS scl_num_compras,
                       sc.scl_ultima_compra,
                       COALESCE(sc.scl_acepta_marketing, 0) AS scl_acepta_marketing,
                       sc.scl_notas,
                       COALESCE(sc.scl_activo, 1) AS scl_activo,
                       sc.scl_fecha_registro AS scl_fecha_registro
                FROM clientes c
                LEFT JOIN store_clientes sc ON sc.scl_cliente_id = c.cli_cliente_id AND sc.scl_tenant_id = c.cli_tenant_id";
    }

    public function index() {
        try {
            $buscar    = trim($this->get('buscar') ?? '');
            $categoria = $this->get('categoria') ?? '';
            $pagina    = max(1, (int)($this->get('pagina') ?? 1));
            $porPagina = 25;

            $where = " WHERE c.cli_tenant_id = ?";
            $params = [$this->tenantId];

            if (!empty($buscar)) {
                // Nombres y apellidos no están cifrados → LIKE directo
                // Identificación y email están cifrados → usar blind index para búsqueda exacta
                $idHash = \DataProtection::blindIndex($buscar);
                $where .= " AND (c.cli_nombres LIKE ? OR c.cli_apellidos LIKE ? OR c.cli_identificacion_hash = ? OR c.cli_email_hash = ?)";
                $like = "%{$buscar}%";
                $params = array_merge($params, [$like, $like, $idHash, $idHash]);
            }
            if (!empty($categoria)) {
                $where .= " AND COALESCE(sc.scl_categoria, 'NUEVO') = ?";
                $params[] = $categoria;
            }

            // Count
            $countSQL = "SELECT COUNT(*) FROM clientes c LEFT JOIN store_clientes sc ON sc.scl_cliente_id = c.cli_cliente_id AND sc.scl_tenant_id = c.cli_tenant_id" . $where;
            $stmtC = $this->db->prepare($countSQL);
            $stmtC->execute($params);
            $total = (int)$stmtC->fetchColumn();
            $totalPaginas = max(1, ceil($total / $porPagina));
            $offset = ($pagina - 1) * $porPagina;

            $sql = $this->clienteSelectSQL() . $where . " ORDER BY c.cli_nombres LIMIT {$porPagina} OFFSET {$offset}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $clientes = \DataProtection::decryptRows('clientes', $stmt->fetchAll(\PDO::FETCH_ASSOC));

            // Resumen por categoría (solo clientes que tienen extensión store)
            $stmt = $this->db->prepare("
                SELECT COALESCE(sc.scl_categoria, 'NUEVO') AS cat, COUNT(*) AS total
                FROM clientes c
                LEFT JOIN store_clientes sc ON sc.scl_cliente_id = c.cli_cliente_id AND sc.scl_tenant_id = c.cli_tenant_id
                WHERE c.cli_tenant_id = ?
                GROUP BY cat
            ");
            $stmt->execute([$this->tenantId]);
            $resumenCat = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

            $this->viewData['clientes']        = $clientes;
            $this->viewData['resumenCat']      = $resumenCat;
            $this->viewData['buscar']          = $buscar;
            $this->viewData['categoriaFiltro'] = $categoria;
            $this->viewData['pagina']          = $pagina;
            $this->viewData['totalPaginas']    = $totalPaginas;
            $this->viewData['total']           = $total;
            $this->viewData['csrf_token']      = \Security::generateCsrfToken();
            $this->viewData['title']           = 'Clientes';

            $this->renderModule('store/clientes/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando clientes: " . $e->getMessage());
            $this->error('Error al cargar clientes');
        }
    }

    /**
     * Crear cliente: primero busca/crea en la tabla compartida `clientes`,
     * luego crea la extensión en `store_clientes`.
     */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $this->viewData['csrf_token'] = \Security::generateCsrfToken();
                $this->viewData['title'] = 'Nuevo Cliente';
                return $this->renderModule('store/clientes/formulario', $this->viewData);
            }
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $nombres = trim($this->post('nombres') ?? '');
            if (empty($nombres)) {
                return $this->jsonResponse(['success' => false, 'message' => 'El nombre es obligatorio']);
            }

            $identificacion = trim($this->post('identificacion') ?? '');
            $clienteId = null;

            $this->db->beginTransaction();

            // 1. Buscar si ya existe en la tabla compartida `clientes` (via blind index)
            if (!empty($identificacion)) {
                $idHash = \DataProtection::blindIndex($identificacion);
                $stmt = $this->db->prepare("SELECT cli_cliente_id FROM clientes WHERE cli_identificacion_hash = ? AND cli_tenant_id = ?");
                $stmt->execute([$idHash, $this->tenantId]);
                $clienteId = $stmt->fetchColumn();

                if ($clienteId) {
                    // Verificar si ya tiene extensión Store
                    $stmt = $this->db->prepare("SELECT scl_id FROM store_clientes WHERE scl_cliente_id = ? AND scl_tenant_id = ?");
                    $stmt->execute([$clienteId, $this->tenantId]);
                    if ($stmt->fetchColumn()) {
                        $this->db->rollBack();
                        return $this->jsonResponse(['success' => false, 'message' => 'Este cliente ya está registrado en la tienda']);
                    }
                }
            }

            // 2. Si no existe en la tabla compartida, crearlo con cifrado
            if (!$clienteId) {
                $protectedData = [
                    'cli_identificacion' => $identificacion ?: null,
                    'cli_email'          => trim($this->post('email') ?? '') ?: null,
                    'cli_telefono'       => trim($this->post('telefono') ?? '') ?: null,
                    'cli_celular'        => trim($this->post('celular') ?? '') ?: null,
                ];
                $encrypted = \DataProtection::encryptRow('clientes', $protectedData);

                $stmt = $this->db->prepare("INSERT INTO clientes (
                    cli_tenant_id, cli_tipo_identificacion, cli_identificacion, cli_identificacion_hash,
                    cli_nombres, cli_apellidos, cli_email, cli_email_hash, cli_telefono, cli_celular,
                    cli_direccion, cli_fecha_nacimiento, cli_estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'A')");

                $stmt->execute([
                    $this->tenantId,
                    $this->post('tipo_id') ?? 'CED',
                    $encrypted['cli_identificacion'],
                    $encrypted['cli_identificacion_hash'] ?? null,
                    $nombres,
                    trim($this->post('apellidos') ?? '') ?: null,
                    $encrypted['cli_email'],
                    $encrypted['cli_email_hash'] ?? null,
                    $encrypted['cli_telefono'],
                    $encrypted['cli_celular'],
                    trim($this->post('direccion') ?? '') ?: null,
                    $this->post('fecha_nacimiento') ?: null
                ]);
                $clienteId = (int)$this->db->lastInsertId();
            } else {
                // Actualizar datos en tabla compartida si vienen nuevos (con cifrado)
                $protectedData = [
                    'cli_email'    => trim($this->post('email') ?? '') ?: null,
                    'cli_telefono' => trim($this->post('telefono') ?? '') ?: null,
                    'cli_celular'  => trim($this->post('celular') ?? '') ?: null,
                ];
                $encrypted = \DataProtection::encryptRow('clientes', $protectedData);

                $stmt = $this->db->prepare("UPDATE clientes SET
                    cli_nombres = ?, cli_apellidos = ?,
                    cli_email = COALESCE(?, cli_email), cli_email_hash = COALESCE(?, cli_email_hash),
                    cli_telefono = COALESCE(?, cli_telefono), cli_celular = COALESCE(?, cli_celular),
                    cli_direccion = COALESCE(?, cli_direccion), cli_fecha_nacimiento = COALESCE(?, cli_fecha_nacimiento)
                    WHERE cli_cliente_id = ? AND cli_tenant_id = ?");
                $stmt->execute([
                    $nombres,
                    trim($this->post('apellidos') ?? '') ?: null,
                    $encrypted['cli_email'],
                    $encrypted['cli_email_hash'] ?? null,
                    $encrypted['cli_telefono'],
                    $encrypted['cli_celular'],
                    trim($this->post('direccion') ?? '') ?: null,
                    $this->post('fecha_nacimiento') ?: null,
                    $clienteId, $this->tenantId
                ]);
            }

            // 3. Crear extensión Store
            $stmt = $this->db->prepare("INSERT INTO store_clientes (
                scl_tenant_id, scl_cliente_id, scl_acepta_marketing, scl_notas
            ) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $this->tenantId,
                $clienteId,
                (int)($this->post('acepta_marketing') ?? 0),
                trim($this->post('notas') ?? '') ?: null
            ]);

            $this->db->commit();

            return $this->jsonResponse(['success' => true, 'message' => 'Cliente registrado', 'id' => $clienteId]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error creando cliente: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar cliente']);
        }
    }

    /**
     * Editar: datos personales → tabla compartida `clientes`, datos Store → `store_clientes`
     */
    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $clienteId = (int)($this->post('id') ?? 0);
            $nombres = trim($this->post('nombres') ?? '');
            if (!$clienteId || empty($nombres)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
            }

            $this->db->beginTransaction();

            // 1. Actualizar datos personales en la tabla compartida (con cifrado)
            $protectedData = [
                'cli_identificacion' => trim($this->post('identificacion') ?? '') ?: null,
                'cli_email'          => trim($this->post('email') ?? '') ?: null,
                'cli_telefono'       => trim($this->post('telefono') ?? '') ?: null,
                'cli_celular'        => trim($this->post('celular') ?? '') ?: null,
            ];
            $encrypted = \DataProtection::encryptRow('clientes', $protectedData);

            $stmt = $this->db->prepare("UPDATE clientes SET
                cli_tipo_identificacion = ?,
                cli_identificacion = ?, cli_identificacion_hash = ?,
                cli_nombres = ?, cli_apellidos = ?,
                cli_email = ?, cli_email_hash = ?,
                cli_telefono = ?, cli_celular = ?,
                cli_direccion = ?, cli_fecha_nacimiento = ?
                WHERE cli_cliente_id = ? AND cli_tenant_id = ?");
            $stmt->execute([
                $this->post('tipo_id') ?? 'CED',
                $encrypted['cli_identificacion'],
                $encrypted['cli_identificacion_hash'] ?? null,
                $nombres,
                trim($this->post('apellidos') ?? '') ?: null,
                $encrypted['cli_email'],
                $encrypted['cli_email_hash'] ?? null,
                $encrypted['cli_telefono'],
                $encrypted['cli_celular'],
                trim($this->post('direccion') ?? '') ?: null,
                $this->post('fecha_nacimiento') ?: null,
                $clienteId, $this->tenantId
            ]);

            // 2. Actualizar extensión Store (INSERT si no existe)
            $stmt = $this->db->prepare("SELECT scl_id FROM store_clientes WHERE scl_cliente_id = ? AND scl_tenant_id = ?");
            $stmt->execute([$clienteId, $this->tenantId]);
            $sclId = $stmt->fetchColumn();

            if ($sclId) {
                $stmt = $this->db->prepare("UPDATE store_clientes SET
                    scl_acepta_marketing = ?, scl_notas = ?, scl_activo = ?
                    WHERE scl_id = ?");
                $stmt->execute([
                    (int)($this->post('acepta_marketing') ?? 0),
                    trim($this->post('notas') ?? '') ?: null,
                    (int)($this->post('activo') ?? 1),
                    $sclId
                ]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO store_clientes (
                    scl_tenant_id, scl_cliente_id, scl_acepta_marketing, scl_notas
                ) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $this->tenantId, $clienteId,
                    (int)($this->post('acepta_marketing') ?? 0),
                    trim($this->post('notas') ?? '') ?: null
                ]);
            }

            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => 'Cliente actualizado']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error editando cliente: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    public function ver() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->error('Cliente no encontrado');

            $sql = $this->clienteSelectSQL() . " WHERE c.cli_cliente_id = ? AND c.cli_tenant_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id, $this->tenantId]);
            $cliente = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$cliente) return $this->error('Cliente no encontrado');

            // Descifrar datos personales
            $cliente = \DataProtection::decryptRow('clientes', $cliente);

            // Últimas compras
            $stmt = $this->db->prepare("SELECT * FROM store_ventas WHERE ven_cliente_id = ? AND ven_tenant_id = ? ORDER BY ven_fecha DESC LIMIT 20");
            $stmt->execute([$id, $this->tenantId]);
            $compras = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Historial de puntos
            $stmt = $this->db->prepare("SELECT * FROM store_cliente_puntos_log WHERE cpl_cliente_id = ? AND cpl_tenant_id = ? ORDER BY cpl_fecha_registro DESC LIMIT 20");
            $stmt->execute([$id, $this->tenantId]);
            $puntos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['cliente']    = $cliente;
            $this->viewData['compras']    = $compras;
            $this->viewData['puntos']     = $puntos;
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title']      = $cliente['cli_nombres'] . ' ' . ($cliente['cli_apellidos'] ?? '');

            $this->renderModule('store/clientes/ver', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error detalle cliente: " . $e->getMessage());
            $this->error('Error al cargar cliente');
        }
    }

    /**
     * Búsqueda rápida para POS (API JSON)
     * Busca en la tabla compartida `clientes` e incluye datos Store si existen
     */
    public function buscar() {
        try {
            $q = trim($this->get('q') ?? '');
            if (strlen($q) < 2) return $this->jsonResponse(['success' => true, 'clientes' => []]);

            $like = "%{$q}%";
            $idHash = \DataProtection::blindIndex($q);
            $stmt = $this->db->prepare("
                SELECT c.cli_cliente_id AS id, c.cli_nombres AS nombres, c.cli_apellidos AS apellidos,
                       c.cli_identificacion AS identificacion, c.cli_email AS email,
                       COALESCE(sc.scl_puntos_disponibles, 0) AS puntos
                FROM clientes c
                LEFT JOIN store_clientes sc ON sc.scl_cliente_id = c.cli_cliente_id AND sc.scl_tenant_id = c.cli_tenant_id
                WHERE c.cli_tenant_id = ? AND c.cli_estado = 'A'
                  AND (c.cli_nombres LIKE ? OR c.cli_apellidos LIKE ? OR c.cli_identificacion_hash = ? OR c.cli_email_hash = ?)
                ORDER BY c.cli_nombres LIMIT 15
            ");
            $stmt->execute([$this->tenantId, $like, $like, $idHash, $idHash]);
            $clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Descifrar identificacion y email para mostrar en resultados
            foreach ($clientes as &$cl) {
                $cl['identificacion'] = \DataProtection::decrypt($cl['identificacion']);
                $cl['email'] = \DataProtection::decrypt($cl['email']);
            }
            unset($cl);

            return $this->jsonResponse(['success' => true, 'clientes' => $clientes]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'clientes' => []]);
        }
    }

    /**
     * Eliminar: solo elimina la extensión Store, NO elimina de la tabla compartida
     */
    public function eliminar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $clienteId = (int)($this->post('id') ?? 0);

            // Verificar ventas asociadas
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM store_ventas WHERE ven_cliente_id = ? AND ven_tenant_id = ?");
            $stmt->execute([$clienteId, $this->tenantId]);
            if ((int)$stmt->fetchColumn() > 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'No se puede eliminar: tiene ventas asociadas. Puede desactivarlo.']);
            }

            // Eliminar extensión Store y puntos (NO toca la tabla compartida)
            $stmt = $this->db->prepare("SELECT scl_id FROM store_clientes WHERE scl_cliente_id = ? AND scl_tenant_id = ?");
            $stmt->execute([$clienteId, $this->tenantId]);
            $sclId = $stmt->fetchColumn();

            if ($sclId) {
                $this->db->prepare("DELETE FROM store_cliente_puntos_log WHERE cpl_scl_id = ?")->execute([$sclId]);
                $this->db->prepare("DELETE FROM store_clientes WHERE scl_id = ?")->execute([$sclId]);
            }

            return $this->jsonResponse(['success' => true, 'message' => 'Cliente desvinculado de la tienda']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando cliente: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    /**
     * Obtener o crear la extensión Store para un cliente existente.
     * Usado internamente por POS al completar una venta.
     */
    public function obtenerOcrearExtension($clienteId) {
        $stmt = $this->db->prepare("SELECT scl_id FROM store_clientes WHERE scl_cliente_id = ? AND scl_tenant_id = ?");
        $stmt->execute([$clienteId, $this->tenantId]);
        $sclId = $stmt->fetchColumn();

        if (!$sclId) {
            $this->db->prepare("INSERT INTO store_clientes (scl_tenant_id, scl_cliente_id) VALUES (?, ?)")
                     ->execute([$this->tenantId, $clienteId]);
            $sclId = (int)$this->db->lastInsertId();
        }
        return $sclId;
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data); exit; }
}
