<?php
/**
 * DigiSports Store — Controlador de Productos
 * CRUD completo con variantes, stock e impuestos
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class ProductoController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono  = 'fas fa-store';
    protected $moduloColor  = '#F59E0B';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }

    /* ═══════════════════════════════════════
     * LISTADO CON FILTROS
     * ═══════════════════════════════════════ */
    public function index() {
        try {
            $buscar      = trim($this->get('buscar') ?? $this->post('buscar') ?? '');
            $categoriaId = (int)($this->get('categoria') ?? 0);
            $marcaId     = (int)($this->get('marca') ?? 0);
            $estado      = $this->get('estado') ?? '';
            $tipo        = $this->get('tipo') ?? '';
            $pagina      = max(1, (int)($this->get('pagina') ?? 1));
            $porPagina   = 25;

            // Contar total
            $sqlCount = "SELECT COUNT(*) FROM store_productos p WHERE p.pro_tenant_id = ?";
            $params = [$this->tenantId];
            $this->applyFilters($sqlCount, $params, $buscar, $categoriaId, $marcaId, $estado, $tipo);

            $stmt = $this->db->prepare($sqlCount);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();
            $totalPaginas = max(1, ceil($total / $porPagina));
            $offset = ($pagina - 1) * $porPagina;

            // Listado con JOINs
            $sql = "SELECT p.*,
                           c.cat_nombre AS categoria_nombre,
                           m.mar_nombre AS marca_nombre,
                           i.imp_nombre AS impuesto_nombre,
                           i.imp_porcentaje,
                           COALESCE(s.stk_cantidad, 0)   AS stock_total,
                           COALESCE(s.stk_disponible, 0) AS stock_disponible
                    FROM store_productos p
                    LEFT JOIN store_categorias c ON c.cat_categoria_id = p.pro_categoria_id
                    LEFT JOIN store_marcas m     ON m.mar_marca_id = p.pro_marca_id
                    LEFT JOIN store_impuestos i  ON i.imp_impuesto_id = p.pro_impuesto_id
                    LEFT JOIN store_stock s      ON s.stk_producto_id = p.pro_producto_id 
                                                 AND s.stk_variante_id IS NULL
                                                 AND s.stk_tenant_id = p.pro_tenant_id
                    WHERE p.pro_tenant_id = ?";
            $params2 = [$this->tenantId];
            $this->applyFilters($sql, $params2, $buscar, $categoriaId, $marcaId, $estado, $tipo);

            $sql .= " ORDER BY p.pro_fecha_registro DESC LIMIT {$porPagina} OFFSET {$offset}";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params2);
            $productos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Catálogos para filtros
            $categorias = $this->fetchCategorias();
            $marcas     = $this->fetchMarcas();

            $this->viewData['productos']      = $productos;
            $this->viewData['categorias']     = $categorias;
            $this->viewData['marcas']         = $marcas;
            $this->viewData['buscar']         = $buscar;
            $this->viewData['categoriaFiltro'] = $categoriaId;
            $this->viewData['marcaFiltro']    = $marcaId;
            $this->viewData['estadoFiltro']   = $estado;
            $this->viewData['tipoFiltro']     = $tipo;
            $this->viewData['pagina']         = $pagina;
            $this->viewData['totalPaginas']   = $totalPaginas;
            $this->viewData['total']          = $total;
            $this->viewData['csrf_token']     = \Security::generateCsrfToken();
            $this->viewData['title']          = 'Productos';

            $this->renderModule('store/productos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando productos: " . $e->getMessage());
            $this->error('Error al cargar productos');
        }
    }

    /* ═══════════════════════════════════════
     * DETALLE DE PRODUCTO
     * ═══════════════════════════════════════ */
    public function ver() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->error('Producto no encontrado');

            $stmt = $this->db->prepare("
                SELECT p.*, c.cat_nombre, m.mar_nombre, 
                       i.imp_nombre, i.imp_porcentaje,
                       COALESCE(s.stk_cantidad, 0) AS stock_total,
                       COALESCE(s.stk_reservado, 0) AS stock_reservado,
                       COALESCE(s.stk_disponible, 0) AS stock_disponible
                FROM store_productos p
                LEFT JOIN store_categorias c ON c.cat_categoria_id = p.pro_categoria_id
                LEFT JOIN store_marcas m     ON m.mar_marca_id = p.pro_marca_id
                LEFT JOIN store_impuestos i  ON i.imp_impuesto_id = p.pro_impuesto_id
                LEFT JOIN store_stock s      ON s.stk_producto_id = p.pro_producto_id 
                                             AND s.stk_variante_id IS NULL
                                             AND s.stk_tenant_id = p.pro_tenant_id
                WHERE p.pro_producto_id = ? AND p.pro_tenant_id = ?
            ");
            $stmt->execute([$id, $this->tenantId]);
            $producto = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$producto) return $this->error('Producto no encontrado');

            // Variantes
            $stmt = $this->db->prepare("
                SELECT v.*, COALESCE(s.stk_cantidad, 0) AS stock_total, 
                       COALESCE(s.stk_disponible, 0) AS stock_disponible
                FROM store_producto_variantes v
                LEFT JOIN store_stock s ON s.stk_variante_id = v.var_variante_id 
                                        AND s.stk_tenant_id = v.var_tenant_id
                WHERE v.var_producto_id = ? AND v.var_tenant_id = ?
                ORDER BY v.var_talla, v.var_color
            ");
            $stmt->execute([$id, $this->tenantId]);
            $variantes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Imágenes
            $stmt = $this->db->prepare("SELECT * FROM store_producto_imagenes WHERE img_producto_id = ? AND img_tenant_id = ? ORDER BY img_orden");
            $stmt->execute([$id, $this->tenantId]);
            $imagenes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Últimos movimientos de stock
            $stmt = $this->db->prepare("
                SELECT * FROM store_stock_movimientos 
                WHERE mov_producto_id = ? AND mov_tenant_id = ?
                ORDER BY mov_fecha_registro DESC LIMIT 20
            ");
            $stmt->execute([$id, $this->tenantId]);
            $movimientos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['producto']    = $producto;
            $this->viewData['variantes']   = $variantes;
            $this->viewData['imagenes']    = $imagenes;
            $this->viewData['movimientos'] = $movimientos;
            $this->viewData['csrf_token']  = \Security::generateCsrfToken();
            $this->viewData['title']       = $producto['pro_nombre'];

            $this->renderModule('store/productos/ver', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error detalle producto: " . $e->getMessage());
            $this->error('Error al cargar producto');
        }
    }

    /* ═══════════════════════════════════════
     * FORMULARIO CREAR (GET) y GUARDAR (POST)
     * ═══════════════════════════════════════ */
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $this->viewData['categorias'] = $this->fetchCategorias();
                $this->viewData['marcas']     = $this->fetchMarcas();
                $this->viewData['impuestos']  = $this->fetchImpuestos();
                $this->viewData['csrf_token'] = \Security::generateCsrfToken();
                $this->viewData['title']      = 'Nuevo Producto';
                return $this->renderModule('store/productos/crear', $this->viewData);
            }

            // POST — guardar
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $nombre = trim($this->post('nombre') ?? '');
            $precioVenta = (float)($this->post('precio_venta') ?? 0);

            if (empty($nombre)) {
                return $this->jsonResponse(['success' => false, 'message' => 'El nombre es obligatorio']);
            }
            if ($precioVenta <= 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'El precio de venta es obligatorio']);
            }

            // Verificar código duplicado
            $codigo = trim($this->post('codigo') ?? '');
            if (!empty($codigo)) {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM store_productos WHERE pro_codigo = ? AND pro_tenant_id = ?");
                $stmt->execute([$codigo, $this->tenantId]);
                if ((int)$stmt->fetchColumn() > 0) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Ya existe un producto con ese código']);
                }
            }

            $this->db->beginTransaction();

            $sql = "INSERT INTO store_productos (
                        pro_tenant_id, pro_categoria_id, pro_marca_id, pro_codigo, pro_codigo_barras, pro_sku,
                        pro_nombre, pro_slug, pro_descripcion, pro_descripcion_corta,
                        pro_precio_compra, pro_precio_venta, pro_precio_mayoreo, pro_impuesto_id,
                        pro_tipo, pro_unidad_medida, pro_peso_kg, pro_stock_minimo, pro_stock_maximo,
                        pro_permite_venta_sin_stock, pro_destacado, pro_visible_pos,
                        pro_notas_internas, pro_tags, pro_estado
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $categoriaId  = (int)($this->post('categoria_id') ?? 0) ?: null;
            $marcaId      = (int)($this->post('marca_id') ?? 0) ?: null;
            $codigoBarras = trim($this->post('codigo_barras') ?? '');
            $sku          = trim($this->post('sku') ?? '');
            $descripcion  = trim($this->post('descripcion') ?? '');
            $descCorta    = trim($this->post('descripcion_corta') ?? '');
            $precioCompra = (float)($this->post('precio_compra') ?? 0);
            $precioMayoreo = (float)($this->post('precio_mayoreo') ?? 0) ?: null;
            $impuestoId   = (int)($this->post('impuesto_id') ?? 0) ?: null;
            $tipo         = $this->post('tipo') ?? 'SIMPLE';
            $unidadMedida = $this->post('unidad_medida') ?? 'UNIDAD';
            $pesoKg       = (float)($this->post('peso_kg') ?? 0) ?: null;
            $stockMinimo  = (int)($this->post('stock_minimo') ?? 5);
            $stockMaximo  = (int)($this->post('stock_maximo') ?? 0) ?: null;
            $ventaSinStock = (int)($this->post('permite_venta_sin_stock') ?? 0);
            $destacado    = (int)($this->post('destacado') ?? 0);
            $visiblePos   = (int)($this->post('visible_pos') ?? 1);
            $notasInternas = trim($this->post('notas_internas') ?? '');
            $tags          = trim($this->post('tags') ?? '');
            $estado        = $this->post('estado') ?? 'ACTIVO';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $this->tenantId, $categoriaId, $marcaId, $codigo ?: null, $codigoBarras ?: null, $sku ?: null,
                $nombre, $this->slugify($nombre), $descripcion ?: null, $descCorta ?: null,
                $precioCompra, $precioVenta, $precioMayoreo, $impuestoId,
                $tipo, $unidadMedida, $pesoKg, $stockMinimo, $stockMaximo,
                $ventaSinStock, $destacado, $visiblePos,
                $notasInternas ?: null, $tags ?: null, $estado
            ]);

            $productoId = (int)$this->db->lastInsertId();

            // Crear registro de stock inicial
            $stockInicial = (int)($this->post('stock_inicial') ?? 0);
            $stmtStk = $this->db->prepare("INSERT INTO store_stock (stk_tenant_id, stk_producto_id, stk_variante_id, stk_cantidad) VALUES (?, ?, NULL, ?)");
            $stmtStk->execute([$this->tenantId, $productoId, $stockInicial]);

            if ($stockInicial > 0) {
                $stmtMov = $this->db->prepare("INSERT INTO store_stock_movimientos (mov_tenant_id, mov_producto_id, mov_tipo, mov_cantidad, mov_stock_anterior, mov_stock_posterior, mov_costo_unitario, mov_referencia_tipo, mov_motivo, mov_usuario_id) VALUES (?, ?, 'ENTRADA', ?, 0, ?, ?, 'AJUSTE_MANUAL', 'Stock inicial al crear producto', ?)");
                $stmtMov->execute([$this->tenantId, $productoId, $stockInicial, $stockInicial, $precioCompra, $this->userId]);
            }

            // Procesar variantes si es producto VARIABLE
            if ($tipo === 'VARIABLE') {
                $this->guardarVariantes($productoId);
            }

            $this->db->commit();

            return $this->jsonResponse([
                'success' => true, 
                'message' => 'Producto creado exitosamente',
                'producto_id' => $productoId
            ]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error creando producto: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear producto: ' . $e->getMessage()]);
        }
    }

    /* ═══════════════════════════════════════
     * EDITAR (GET form / POST update)
     * ═══════════════════════════════════════ */
    public function editar() {
        try {
            $id = (int)($this->get('id') ?? $this->post('id') ?? 0);
            if (!$id) return $this->error('Producto no encontrado');

            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $stmt = $this->db->prepare("SELECT * FROM store_productos WHERE pro_producto_id = ? AND pro_tenant_id = ?");
                $stmt->execute([$id, $this->tenantId]);
                $producto = $stmt->fetch(\PDO::FETCH_ASSOC);
                if (!$producto) return $this->error('Producto no encontrado');

                // Variantes
                $stmt = $this->db->prepare("SELECT * FROM store_producto_variantes WHERE var_producto_id = ? AND var_tenant_id = ? ORDER BY var_talla, var_color");
                $stmt->execute([$id, $this->tenantId]);
                $variantes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                // Stock simple
                $stmt = $this->db->prepare("SELECT stk_cantidad FROM store_stock WHERE stk_producto_id = ? AND stk_variante_id IS NULL AND stk_tenant_id = ?");
                $stmt->execute([$id, $this->tenantId]);
                $stockActual = (int)($stmt->fetchColumn() ?: 0);

                $this->viewData['producto']     = $producto;
                $this->viewData['variantes']    = $variantes;
                $this->viewData['stockActual']  = $stockActual;
                $this->viewData['categorias']   = $this->fetchCategorias();
                $this->viewData['marcas']       = $this->fetchMarcas();
                $this->viewData['impuestos']    = $this->fetchImpuestos();
                $this->viewData['csrf_token']   = \Security::generateCsrfToken();
                $this->viewData['title']        = 'Editar: ' . $producto['pro_nombre'];

                return $this->renderModule('store/productos/editar', $this->viewData);
            }

            // POST — actualizar
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $nombre = trim($this->post('nombre') ?? '');
            $precioVenta = (float)($this->post('precio_venta') ?? 0);
            if (empty($nombre) || $precioVenta <= 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'Nombre y precio son obligatorios']);
            }

            $this->db->beginTransaction();

            $sql = "UPDATE store_productos SET
                        pro_categoria_id = ?, pro_marca_id = ?, pro_codigo = ?, pro_codigo_barras = ?, pro_sku = ?,
                        pro_nombre = ?, pro_slug = ?, pro_descripcion = ?, pro_descripcion_corta = ?,
                        pro_precio_compra = ?, pro_precio_venta = ?, pro_precio_mayoreo = ?, pro_impuesto_id = ?,
                        pro_tipo = ?, pro_unidad_medida = ?, pro_peso_kg = ?, pro_stock_minimo = ?, pro_stock_maximo = ?,
                        pro_permite_venta_sin_stock = ?, pro_destacado = ?, pro_visible_pos = ?,
                        pro_notas_internas = ?, pro_tags = ?, pro_estado = ?
                    WHERE pro_producto_id = ? AND pro_tenant_id = ?";

            $categoriaId  = (int)($this->post('categoria_id') ?? 0) ?: null;
            $marcaId      = (int)($this->post('marca_id') ?? 0) ?: null;
            $codigo       = trim($this->post('codigo') ?? '') ?: null;
            $codigoBarras = trim($this->post('codigo_barras') ?? '') ?: null;
            $sku          = trim($this->post('sku') ?? '') ?: null;
            $descripcion  = trim($this->post('descripcion') ?? '') ?: null;
            $descCorta    = trim($this->post('descripcion_corta') ?? '') ?: null;
            $precioCompra = (float)($this->post('precio_compra') ?? 0);
            $precioMayoreo = (float)($this->post('precio_mayoreo') ?? 0) ?: null;
            $impuestoId   = (int)($this->post('impuesto_id') ?? 0) ?: null;
            $tipo         = $this->post('tipo') ?? 'SIMPLE';
            $unidadMedida = $this->post('unidad_medida') ?? 'UNIDAD';
            $pesoKg       = (float)($this->post('peso_kg') ?? 0) ?: null;
            $stockMinimo  = (int)($this->post('stock_minimo') ?? 5);
            $stockMaximo  = (int)($this->post('stock_maximo') ?? 0) ?: null;
            $ventaSinStock = (int)($this->post('permite_venta_sin_stock') ?? 0);
            $destacado    = (int)($this->post('destacado') ?? 0);
            $visiblePos   = (int)($this->post('visible_pos') ?? 1);
            $notasInternas = trim($this->post('notas_internas') ?? '') ?: null;
            $tags          = trim($this->post('tags') ?? '') ?: null;
            $estado        = $this->post('estado') ?? 'ACTIVO';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $categoriaId, $marcaId, $codigo, $codigoBarras, $sku,
                $nombre, $this->slugify($nombre), $descripcion, $descCorta,
                $precioCompra, $precioVenta, $precioMayoreo, $impuestoId,
                $tipo, $unidadMedida, $pesoKg, $stockMinimo, $stockMaximo,
                $ventaSinStock, $destacado, $visiblePos,
                $notasInternas, $tags, $estado,
                $id, $this->tenantId
            ]);

            // Actualizar variantes si es VARIABLE
            if ($tipo === 'VARIABLE') {
                $this->guardarVariantes($id, true);
            }

            $this->db->commit();

            return $this->jsonResponse(['success' => true, 'message' => 'Producto actualizado']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error editando producto: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    /* ═══════════════════════════════════════
     * ELIMINAR (POST AJAX)
     * ═══════════════════════════════════════ */
    public function eliminar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $id = (int)($this->post('id') ?? 0);

            // Verificar ventas
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM store_venta_items WHERE vit_producto_id = ? AND vit_tenant_id = ?");
            $stmt->execute([$id, $this->tenantId]);
            if ((int)$stmt->fetchColumn() > 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'No se puede eliminar: tiene ventas asociadas. Puede cambiarlo a estado INACTIVO.']);
            }

            $this->db->beginTransaction();

            // Eliminar stock, movimientos, variantes, imágenes (cascade)
            $this->db->prepare("DELETE FROM store_stock WHERE stk_producto_id = ? AND stk_tenant_id = ?")->execute([$id, $this->tenantId]);
            $this->db->prepare("DELETE FROM store_stock_movimientos WHERE mov_producto_id = ? AND mov_tenant_id = ?")->execute([$id, $this->tenantId]);
            $this->db->prepare("DELETE FROM store_producto_imagenes WHERE img_producto_id = ? AND img_tenant_id = ?")->execute([$id, $this->tenantId]);
            $this->db->prepare("DELETE FROM store_producto_variantes WHERE var_producto_id = ? AND var_tenant_id = ?")->execute([$id, $this->tenantId]);
            $this->db->prepare("DELETE FROM store_productos WHERE pro_producto_id = ? AND pro_tenant_id = ?")->execute([$id, $this->tenantId]);

            $this->db->commit();

            return $this->jsonResponse(['success' => true, 'message' => 'Producto eliminado']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error eliminando producto: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    /* ═══════════════════════════════════════
     * AJUSTE DE STOCK (POST AJAX)
     * ═══════════════════════════════════════ */
    public function ajustarStock() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $productoId  = (int)($this->post('producto_id') ?? 0);
            $varianteId  = (int)($this->post('variante_id') ?? 0) ?: null;
            $cantidad    = (int)($this->post('cantidad') ?? 0);
            $tipo        = $this->post('tipo_ajuste') ?? 'AJUSTE'; // ENTRADA, SALIDA, AJUSTE
            $motivo      = trim($this->post('motivo') ?? '');

            if (!$productoId || $cantidad == 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
            }

            $this->db->beginTransaction();

            // Obtener stock actual
            $sqlStk = "SELECT stk_stock_id, stk_cantidad FROM store_stock WHERE stk_producto_id = ? AND stk_tenant_id = ?";
            $paramsStk = [$productoId, $this->tenantId];
            if ($varianteId) {
                $sqlStk .= " AND stk_variante_id = ?";
                $paramsStk[] = $varianteId;
            } else {
                $sqlStk .= " AND stk_variante_id IS NULL";
            }

            $stmt = $this->db->prepare($sqlStk);
            $stmt->execute($paramsStk);
            $stock = $stmt->fetch(\PDO::FETCH_ASSOC);

            $stockAnterior = $stock ? (int)$stock['stk_cantidad'] : 0;

            if ($tipo === 'AJUSTE') {
                // Ajuste absoluto — establecer stock a la cantidad indicada
                $stockPosterior = $cantidad;
                $diferencia = $stockPosterior - $stockAnterior;
            } elseif ($tipo === 'ENTRADA') {
                $stockPosterior = $stockAnterior + abs($cantidad);
                $diferencia = abs($cantidad);
            } else { // SALIDA
                $stockPosterior = $stockAnterior - abs($cantidad);
                $diferencia = -abs($cantidad);
            }

            if ($stockPosterior < 0) {
                $this->db->rollBack();
                return $this->jsonResponse(['success' => false, 'message' => 'Stock insuficiente. Stock actual: ' . $stockAnterior]);
            }

            // Actualizar o crear stock
            if ($stock) {
                $this->db->prepare("UPDATE store_stock SET stk_cantidad = ? WHERE stk_stock_id = ?")->execute([$stockPosterior, $stock['stk_stock_id']]);
            } else {
                $this->db->prepare("INSERT INTO store_stock (stk_tenant_id, stk_producto_id, stk_variante_id, stk_cantidad) VALUES (?, ?, ?, ?)")->execute([$this->tenantId, $productoId, $varianteId, $stockPosterior]);
            }

            // Registrar movimiento
            $tipoMov = $diferencia >= 0 ? 'ENTRADA' : 'SALIDA';
            $stmtMov = $this->db->prepare("INSERT INTO store_stock_movimientos (mov_tenant_id, mov_producto_id, mov_variante_id, mov_tipo, mov_cantidad, mov_stock_anterior, mov_stock_posterior, mov_referencia_tipo, mov_motivo, mov_usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, 'AJUSTE_MANUAL', ?, ?)");
            $stmtMov->execute([$this->tenantId, $productoId, $varianteId, $tipoMov, $diferencia, $stockAnterior, $stockPosterior, $motivo ?: 'Ajuste manual', $this->userId]);

            // Verificar alerta de stock bajo
            $this->verificarAlertaStock($productoId, $stockPosterior);

            $this->db->commit();

            return $this->jsonResponse(['success' => true, 'message' => "Stock actualizado: {$stockAnterior} → {$stockPosterior}", 'stock_nuevo' => $stockPosterior]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error ajustando stock: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al ajustar stock']);
        }
    }

    /* ═══════════════════════════════════════
     * BÚSQUEDA RÁPIDA PARA POS (API JSON)
     * ═══════════════════════════════════════ */
    public function buscar() {
        try {
            $q = trim($this->get('q') ?? '');
            if (strlen($q) < 2) {
                return $this->jsonResponse(['success' => true, 'productos' => []]);
            }

            $sql = "SELECT p.pro_producto_id AS id, p.pro_nombre AS nombre, p.pro_codigo AS codigo,
                           p.pro_codigo_barras AS codigo_barras, p.pro_precio_venta AS precio,
                           p.pro_imagen_principal AS imagen, p.pro_tipo AS tipo,
                           i.imp_porcentaje AS impuesto_porcentaje,
                           COALESCE(s.stk_disponible, 0) AS stock
                    FROM store_productos p
                    LEFT JOIN store_impuestos i ON i.imp_impuesto_id = p.pro_impuesto_id
                    LEFT JOIN store_stock s     ON s.stk_producto_id = p.pro_producto_id 
                                               AND s.stk_variante_id IS NULL
                                               AND s.stk_tenant_id = p.pro_tenant_id
                    WHERE p.pro_tenant_id = ? AND p.pro_estado = 'ACTIVO' AND p.pro_visible_pos = 1
                      AND (p.pro_nombre LIKE ? OR p.pro_codigo LIKE ? OR p.pro_codigo_barras LIKE ? OR p.pro_sku LIKE ?)
                    ORDER BY p.pro_nombre LIMIT 20";

            $param = "%{$q}%";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->tenantId, $param, $param, $param, $param]);
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse(['success' => true, 'productos' => $resultados]);

        } catch (\Exception $e) {
            $this->logError("Error búsqueda productos: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'productos' => []]);
        }
    }

    /* ═══════════════════════════════════════════════
     * MÉTODOS PRIVADOS
     * ═══════════════════════════════════════════════ */

    private function applyFilters(&$sql, &$params, $buscar, $categoriaId, $marcaId, $estado, $tipo) {
        if (!empty($buscar)) {
            $sql .= " AND (p.pro_nombre LIKE ? OR p.pro_codigo LIKE ? OR p.pro_sku LIKE ? OR p.pro_codigo_barras LIKE ?)";
            $like = "%{$buscar}%";
            $params = array_merge($params, [$like, $like, $like, $like]);
        }
        if ($categoriaId > 0) {
            $sql .= " AND p.pro_categoria_id = ?";
            $params[] = $categoriaId;
        }
        if ($marcaId > 0) {
            $sql .= " AND p.pro_marca_id = ?";
            $params[] = $marcaId;
        }
        if (!empty($estado)) {
            $sql .= " AND p.pro_estado = ?";
            $params[] = $estado;
        }
        if (!empty($tipo)) {
            $sql .= " AND p.pro_tipo = ?";
            $params[] = $tipo;
        }
    }

    private function guardarVariantes($productoId, $eliminarExistentes = false) {
        if ($eliminarExistentes) {
            // Eliminar variantes antiguas que no estén en ventas
            $this->db->prepare("DELETE v FROM store_producto_variantes v
                LEFT JOIN store_venta_items vi ON vi.vit_variante_id = v.var_variante_id
                WHERE v.var_producto_id = ? AND v.var_tenant_id = ? AND vi.vit_item_id IS NULL
            ")->execute([$productoId, $this->tenantId]);
        }

        $tallas   = $this->post('variante_talla') ?? [];
        $colores  = $this->post('variante_color') ?? [];
        $precios  = $this->post('variante_precio_adicional') ?? [];
        $stocks   = $this->post('variante_stock') ?? [];

        if (!is_array($tallas)) return;

        for ($i = 0; $i < count($tallas); $i++) {
            $talla = trim($tallas[$i] ?? '');
            $color = trim($colores[$i] ?? '');
            if (empty($talla) && empty($color)) continue;

            $precioAdicional = (float)($precios[$i] ?? 0);
            $stockVar = (int)($stocks[$i] ?? 0);

            $stmtVar = $this->db->prepare("INSERT INTO store_producto_variantes (var_tenant_id, var_producto_id, var_talla, var_color, var_precio_adicional) VALUES (?, ?, ?, ?, ?)");
            $stmtVar->execute([$this->tenantId, $productoId, $talla ?: null, $color ?: null, $precioAdicional]);
            $varianteId = (int)$this->db->lastInsertId();

            // Stock para variante
            $this->db->prepare("INSERT INTO store_stock (stk_tenant_id, stk_producto_id, stk_variante_id, stk_cantidad) VALUES (?, ?, ?, ?)")->execute([$this->tenantId, $productoId, $varianteId, $stockVar]);

            if ($stockVar > 0) {
                $this->db->prepare("INSERT INTO store_stock_movimientos (mov_tenant_id, mov_producto_id, mov_variante_id, mov_tipo, mov_cantidad, mov_stock_anterior, mov_stock_posterior, mov_referencia_tipo, mov_motivo, mov_usuario_id) VALUES (?, ?, ?, 'ENTRADA', ?, 0, ?, 'AJUSTE_MANUAL', 'Stock inicial variante', ?)")->execute([$this->tenantId, $productoId, $varianteId, $stockVar, $stockVar, $this->userId]);
            }
        }
    }

    private function verificarAlertaStock($productoId, $stockActual) {
        $stmt = $this->db->prepare("SELECT pro_stock_minimo FROM store_productos WHERE pro_producto_id = ? AND pro_tenant_id = ?");
        $stmt->execute([$productoId, $this->tenantId]);
        $minimo = (int)$stmt->fetchColumn();

        if ($stockActual <= $minimo) {
            // Verificar si ya existe alerta pendiente
            $stmt = $this->db->prepare("SELECT ale_alerta_id FROM store_stock_alertas WHERE ale_producto_id = ? AND ale_tenant_id = ? AND ale_estado IN ('PENDIENTE','NOTIFICADA')");
            $stmt->execute([$productoId, $this->tenantId]);
            if (!$stmt->fetchColumn()) {
                $this->db->prepare("INSERT INTO store_stock_alertas (ale_tenant_id, ale_producto_id, ale_stock_actual, ale_stock_minimo) VALUES (?, ?, ?, ?)")->execute([$this->tenantId, $productoId, $stockActual, $minimo]);
            }
        } else {
            // Resolver alertas existentes
            $this->db->prepare("UPDATE store_stock_alertas SET ale_estado = 'RESUELTA', ale_fecha_resuelta = NOW() WHERE ale_producto_id = ? AND ale_tenant_id = ? AND ale_estado IN ('PENDIENTE','NOTIFICADA')")->execute([$productoId, $this->tenantId]);
        }
    }

    private function fetchCategorias() {
        $stmt = $this->db->prepare("SELECT cat_categoria_id, cat_nombre, cat_padre_id FROM store_categorias WHERE cat_tenant_id = ? AND cat_activo = 1 ORDER BY cat_orden, cat_nombre");
        $stmt->execute([$this->tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function fetchMarcas() {
        $stmt = $this->db->prepare("SELECT mar_marca_id, mar_nombre FROM store_marcas WHERE mar_tenant_id = ? AND mar_activo = 1 ORDER BY mar_nombre");
        $stmt->execute([$this->tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function fetchImpuestos() {
        $stmt = $this->db->prepare("SELECT imp_impuesto_id, imp_nombre, imp_porcentaje, imp_es_default FROM store_impuestos WHERE imp_tenant_id = ? AND imp_activo = 1 ORDER BY imp_es_default DESC, imp_nombre");
        $stmt->execute([$this->tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function slugify($text) {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
