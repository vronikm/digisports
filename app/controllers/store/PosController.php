<?php
/**
 * DigiSports Store — Controlador de Punto de Venta (POS)
 * Interfaz de venta rápida, procesamiento de pagos, tickets
 * 
 * @package DigiSports\Controllers\Store
 */

namespace App\Controllers\Store;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class PosController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Store';
    protected $moduloIcono  = 'fas fa-store';
    protected $moduloColor  = '#F59E0B';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'STORE';
    }

    /* ═══════════════════════════════════════
     * INTERFAZ POS (pantalla principal)
     * ═══════════════════════════════════════ */
    public function index() {
        try {
            // Verificar turno abierto
            $turno = $this->getTurnoAbierto();
            if (!$turno) {
                // Redirigir a abrir caja
                $this->viewData['sinTurno']   = true;
                $this->viewData['csrf_token'] = \Security::generateCsrfToken();
                $this->viewData['title']      = 'Punto de Venta';
                return $this->renderModule('store/pos/sin_turno', $this->viewData);
            }

            // Categorías para filtro rápido
            $stmt = $this->db->prepare("SELECT cat_categoria_id, cat_nombre, cat_icono FROM store_categorias WHERE cat_tenant_id = ? AND cat_activo = 1 ORDER BY cat_orden, cat_nombre");
            $stmt->execute([$this->tenantId]);
            $categorias = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Productos más vendidos / destacados para grilla rápida
            $stmt = $this->db->prepare("
                SELECT p.pro_producto_id, p.pro_nombre, p.pro_codigo, p.pro_precio_venta, 
                       p.pro_imagen_principal, p.pro_tipo,
                       i.imp_porcentaje,
                       COALESCE(s.stk_disponible, 0) AS stock
                FROM store_productos p
                LEFT JOIN store_impuestos i ON i.imp_impuesto_id = p.pro_impuesto_id
                LEFT JOIN store_stock s     ON s.stk_producto_id = p.pro_producto_id 
                                           AND s.stk_variante_id IS NULL
                                           AND s.stk_tenant_id = p.pro_tenant_id
                WHERE p.pro_tenant_id = ? AND p.pro_estado = 'ACTIVO' AND p.pro_visible_pos = 1
                ORDER BY p.pro_destacado DESC, p.pro_nombre
                LIMIT 50
            ");
            $stmt->execute([$this->tenantId]);
            $productosRapido = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Config
            $config = $this->getConfig();

            // Clientes frecuentes
            $stmt = $this->db->prepare("SELECT c.cli_cliente_id, c.cli_nombres, c.cli_apellidos, c.cli_identificacion, COALESCE(sc.scl_puntos_disponibles, 0) AS scl_puntos_disponibles FROM clientes c LEFT JOIN store_clientes sc ON sc.scl_cliente_id = c.cli_cliente_id AND sc.scl_tenant_id = c.cli_tenant_id WHERE c.cli_tenant_id = ? AND c.cli_estado = 'A' ORDER BY COALESCE(sc.scl_num_compras, 0) DESC LIMIT 50");
            $stmt->execute([$this->tenantId]);
            $clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            // Descifrar campos sensibles para mostrar en POS
            foreach ($clientes as &$cl) {
                $cl['cli_identificacion'] = \DataProtection::decrypt($cl['cli_identificacion']);
            }
            unset($cl);

            $this->viewData['turno']           = $turno;
            $this->viewData['categorias']      = $categorias;
            $this->viewData['productosRapido'] = $productosRapido;
            $this->viewData['clientes']        = $clientes;
            $this->viewData['config']          = $config;
            $this->viewData['csrf_token']      = \Security::generateCsrfToken();
            $this->viewData['title']           = 'Punto de Venta';

            $this->renderModule('store/pos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error POS index: " . $e->getMessage());
            $this->error('Error al cargar el punto de venta');
        }
    }

    /* ═══════════════════════════════════════
     * PROCESAR VENTA (POST AJAX)
     * ═══════════════════════════════════════ */
    public function procesarVenta() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $turno = $this->getTurnoAbierto();
            if (!$turno) {
                return $this->jsonResponse(['success' => false, 'message' => 'No tiene un turno de caja abierto']);
            }

            // Parsear items del carrito (JSON)
            $itemsJson = $this->post('items') ?? '[]';
            $items = json_decode($itemsJson, true);
            if (empty($items)) {
                return $this->jsonResponse(['success' => false, 'message' => 'El carrito está vacío']);
            }

            // Datos de la venta
            $clienteId     = (int)($this->post('cliente_id') ?? 0) ?: null;
            $tipoDocumento = $this->post('tipo_documento') ?? 'TICKET';
            $notas         = trim($this->post('notas') ?? '');
            $descuentoId   = (int)($this->post('descuento_id') ?? 0) ?: null;

            // Pagos (JSON)
            $pagosJson = $this->post('pagos') ?? '[]';
            $pagos = json_decode($pagosJson, true);
            if (empty($pagos)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Debe especificar la forma de pago']);
            }

            $config = $this->getConfig();

            $this->db->beginTransaction();

            // Generar número de venta
            $numero = $this->generarNumeroVenta();

            // Calcular totales de items
            $subtotalSinImpuesto = 0;
            $subtotalConImpuesto = 0;
            $totalImpuesto = 0;
            $subtotal = 0;
            $descuentoTotal = 0;

            $itemsProcessed = [];
            foreach ($items as $item) {
                $productoId = (int)($item['producto_id'] ?? 0);
                $varianteId = (int)($item['variante_id'] ?? 0) ?: null;
                $cantidad   = (float)($item['cantidad'] ?? 1);
                $descLinea  = (float)($item['descuento'] ?? 0);

                if (!$productoId || $cantidad <= 0) continue;

                // Obtener datos del producto
                $stmt = $this->db->prepare("
                    SELECT p.pro_nombre, p.pro_precio_venta, p.pro_precio_compra,
                           p.pro_permite_venta_sin_stock,
                           COALESCE(i.imp_porcentaje, 0) AS imp_porcentaje
                    FROM store_productos p
                    LEFT JOIN store_impuestos i ON i.imp_impuesto_id = p.pro_impuesto_id
                    WHERE p.pro_producto_id = ? AND p.pro_tenant_id = ?
                ");
                $stmt->execute([$productoId, $this->tenantId]);
                $prod = $stmt->fetch(\PDO::FETCH_ASSOC);
                if (!$prod) continue;

                $precioUnit = (float)($item['precio_unitario'] ?? $prod['pro_precio_venta']);
                $impPorcentaje = (float)$prod['imp_porcentaje'];

                // Verificar stock
                if (!$prod['pro_permite_venta_sin_stock']) {
                    $stockDisp = $this->getStockDisponible($productoId, $varianteId);
                    if ($stockDisp < $cantidad) {
                        $this->db->rollBack();
                        return $this->jsonResponse([
                            'success' => false, 
                            'message' => "Stock insuficiente para '{$prod['pro_nombre']}'. Disponible: {$stockDisp}"
                        ]);
                    }
                }

                // Calcular impuesto de línea
                $baseLinea = ($cantidad * $precioUnit) - $descLinea;
                $impLinea = round($baseLinea * ($impPorcentaje / 100), 2);

                if ($impPorcentaje > 0) {
                    $subtotalConImpuesto += $baseLinea;
                } else {
                    $subtotalSinImpuesto += $baseLinea;
                }
                $totalImpuesto += $impLinea;
                $subtotal += $baseLinea;
                $descuentoTotal += $descLinea;

                $itemsProcessed[] = [
                    'producto_id'   => $productoId,
                    'variante_id'   => $varianteId,
                    'descripcion'   => $prod['pro_nombre'],
                    'cantidad'      => $cantidad,
                    'precio_unit'   => $precioUnit,
                    'costo_unit'    => (float)$prod['pro_precio_compra'],
                    'descuento'     => $descLinea,
                    'imp_porcentaje' => $impPorcentaje,
                    'imp_linea'     => $impLinea,
                ];
            }

            if (empty($itemsProcessed)) {
                $this->db->rollBack();
                return $this->jsonResponse(['success' => false, 'message' => 'No hay ítems válidos']);
            }

            $total = $subtotal + $totalImpuesto;

            // Validar que pagos cubran el total
            $totalPagado = 0;
            foreach ($pagos as $p) {
                $totalPagado += (float)($p['monto'] ?? 0);
            }
            if ($totalPagado < ($total - 0.01)) {
                $this->db->rollBack();
                return $this->jsonResponse(['success' => false, 'message' => 'El pago no cubre el total. Total: $' . number_format($total, 2) . ' | Pagado: $' . number_format($totalPagado, 2)]);
            }

            // Calcular puntos
            $puntosGanados = 0;
            if ($clienteId) {
                $puntosPorDolar = (int)($config['puntos_por_dolar'] ?? 1);
                $puntosGanados = (int)floor($total * $puntosPorDolar);
            }

            // Insertar venta
            $stmtVen = $this->db->prepare("
                INSERT INTO store_ventas (
                    ven_tenant_id, ven_turno_id, ven_numero, ven_cliente_id, ven_tipo_documento,
                    ven_fecha, ven_subtotal_sin_impuesto, ven_subtotal_con_impuesto, ven_subtotal,
                    ven_descuento, ven_impuesto, ven_total, ven_descuento_id, ven_notas,
                    ven_vendedor_id, ven_puntos_ganados, ven_estado, ven_usuario_id
                ) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'COMPLETADA', ?)
            ");
            $stmtVen->execute([
                $this->tenantId, $turno['tur_turno_id'], $numero, $clienteId, $tipoDocumento,
                round($subtotalSinImpuesto, 2), round($subtotalConImpuesto, 2), round($subtotal, 2),
                round($descuentoTotal, 2), round($totalImpuesto, 2), round($total, 2),
                $descuentoId, $notas ?: null, $this->userId, $puntosGanados, $this->userId
            ]);
            $ventaId = (int)$this->db->lastInsertId();

            // Insertar items
            $stmtItem = $this->db->prepare("
                INSERT INTO store_venta_items (
                    vit_tenant_id, vit_venta_id, vit_producto_id, vit_variante_id,
                    vit_descripcion, vit_cantidad, vit_precio_unitario, vit_costo_unitario,
                    vit_descuento_linea, vit_porcentaje_impuesto, vit_impuesto_linea
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($itemsProcessed as $it) {
                $stmtItem->execute([
                    $this->tenantId, $ventaId, $it['producto_id'], $it['variante_id'],
                    $it['descripcion'], $it['cantidad'], $it['precio_unit'], $it['costo_unit'],
                    $it['descuento'], $it['imp_porcentaje'], $it['imp_linea']
                ]);

                // Descontar stock
                $this->descontarStock($it['producto_id'], $it['variante_id'], $it['cantidad'], $ventaId, $it['costo_unit']);
            }

            // Registrar pagos
            foreach ($pagos as $p) {
                $formaPago = $p['forma_pago'] ?? 'EFECTIVO';
                $montoPago = (float)($p['monto'] ?? 0);
                $referencia = trim($p['referencia'] ?? '');
                $cambio = 0;

                if ($formaPago === 'EFECTIVO') {
                    $cambio = max(0, $montoPago - $total);
                }

                $this->db->prepare("INSERT INTO store_venta_pagos (vpg_tenant_id, vpg_venta_id, vpg_forma_pago, vpg_monto, vpg_referencia, vpg_cambio) VALUES (?, ?, ?, ?, ?, ?)")
                    ->execute([$this->tenantId, $ventaId, $formaPago, $montoPago, $referencia ?: null, $cambio]);
            }

            // Actualizar puntos del cliente
            if ($clienteId && $puntosGanados > 0) {
                // Asegurar que existe la extensión Store
                $stmt = $this->db->prepare("SELECT scl_id FROM store_clientes WHERE scl_cliente_id = ? AND scl_tenant_id = ?");
                $stmt->execute([$clienteId, $this->tenantId]);
                $sclId = $stmt->fetchColumn();
                if (!$sclId) {
                    $this->db->prepare("INSERT INTO store_clientes (scl_tenant_id, scl_cliente_id) VALUES (?, ?)")->execute([$this->tenantId, $clienteId]);
                    $sclId = (int)$this->db->lastInsertId();
                }

                $this->db->prepare("UPDATE store_clientes SET scl_puntos_acumulados = scl_puntos_acumulados + ?, scl_total_compras = scl_total_compras + ?, scl_num_compras = scl_num_compras + 1, scl_ultima_compra = CURDATE() WHERE scl_id = ?")
                    ->execute([$puntosGanados, $total, $sclId]);

                // Log de puntos
                $this->db->prepare("INSERT INTO store_cliente_puntos_log (cpl_tenant_id, cpl_scl_id, cpl_cliente_id, cpl_tipo, cpl_puntos, cpl_referencia_tipo, cpl_referencia_id, cpl_descripcion) VALUES (?, ?, ?, 'ACUMULACION', ?, 'VENTA', ?, ?)")
                    ->execute([$this->tenantId, $sclId, $clienteId, $puntosGanados, $ventaId, "Compra #{$numero}"]);

                // Actualizar categoría del cliente
                $this->actualizarCategoriaCliente($clienteId);
            }

            // Actualizar uso de descuento si aplica
            if ($descuentoId) {
                $this->db->prepare("UPDATE store_descuentos SET dsc_usos_actuales = dsc_usos_actuales + 1 WHERE dsc_descuento_id = ? AND dsc_tenant_id = ?")
                    ->execute([$descuentoId, $this->tenantId]);
            }

            $this->db->commit();

            // Calcular cambio total
            $cambioTotal = max(0, $totalPagado - $total);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Venta procesada exitosamente',
                'venta' => [
                    'id'       => $ventaId,
                    'numero'   => $numero,
                    'total'    => round($total, 2),
                    'subtotal' => round($subtotal, 2),
                    'impuesto' => round($totalImpuesto, 2),
                    'descuento' => round($descuentoTotal, 2),
                    'pagado'   => round($totalPagado, 2),
                    'cambio'   => round($cambioTotal, 2),
                    'puntos'   => $puntosGanados,
                    'items'    => count($itemsProcessed)
                ]
            ]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error procesando venta: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al procesar la venta']);
        }
    }

    /* ═══════════════════════════════════════
     * ANULAR VENTA
     * ═══════════════════════════════════════ */
    public function anularVenta() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $ventaId = (int)($this->post('venta_id') ?? 0);
            $motivo  = trim($this->post('motivo') ?? '');

            if (!$ventaId) {
                return $this->jsonResponse(['success' => false, 'message' => 'Venta no encontrada']);
            }

            $stmt = $this->db->prepare("SELECT * FROM store_ventas WHERE ven_venta_id = ? AND ven_tenant_id = ? AND ven_estado = 'COMPLETADA'");
            $stmt->execute([$ventaId, $this->tenantId]);
            $venta = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$venta) {
                return $this->jsonResponse(['success' => false, 'message' => 'Venta no encontrada o ya anulada']);
            }

            $this->db->beginTransaction();

            // Restaurar stock
            $stmt = $this->db->prepare("SELECT * FROM store_venta_items WHERE vit_venta_id = ? AND vit_tenant_id = ?");
            $stmt->execute([$ventaId, $this->tenantId]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $this->restaurarStock($item['vit_producto_id'], $item['vit_variante_id'], (float)$item['vit_cantidad'], $ventaId);
            }

            // Revertir puntos del cliente
            if ($venta['ven_cliente_id'] && $venta['ven_puntos_ganados'] > 0) {
                $this->db->prepare("UPDATE store_clientes SET scl_puntos_acumulados = GREATEST(0, scl_puntos_acumulados - ?), scl_total_compras = GREATEST(0, scl_total_compras - ?), scl_num_compras = GREATEST(0, scl_num_compras - 1) WHERE scl_cliente_id = ? AND scl_tenant_id = ?")
                    ->execute([$venta['ven_puntos_ganados'], $venta['ven_total'], $venta['ven_cliente_id'], $this->tenantId]);
            }

            // Anular venta
            $this->db->prepare("UPDATE store_ventas SET ven_estado = 'ANULADA', ven_notas = CONCAT(COALESCE(ven_notas,''), ' | ANULADA: ', ?) WHERE ven_venta_id = ? AND ven_tenant_id = ?")
                ->execute([$motivo ?: 'Sin motivo', $ventaId, $this->tenantId]);

            $this->db->commit();

            return $this->jsonResponse(['success' => true, 'message' => 'Venta anulada y stock restaurado']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error anulando venta: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al anular venta']);
        }
    }

    /* ═══════════════════════════════════════
     * OBTENER DATOS DEL TICKET (para imprimir)
     * ═══════════════════════════════════════ */
    public function ticket() {
        try {
            $ventaId = (int)($this->get('id') ?? 0);

            $stmt = $this->db->prepare("
                SELECT v.*, c.cli_nombres, c.cli_apellidos, c.cli_identificacion
                FROM store_ventas v
                LEFT JOIN clientes c ON c.cli_cliente_id = v.ven_cliente_id AND c.cli_tenant_id = v.ven_tenant_id
                WHERE v.ven_venta_id = ? AND v.ven_tenant_id = ?
            ");
            $stmt->execute([$ventaId, $this->tenantId]);
            $venta = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$venta) return $this->jsonResponse(['success' => false, 'message' => 'Venta no encontrada']);

            // Descifrar datos del cliente
            $venta['cli_identificacion'] = \DataProtection::decrypt($venta['cli_identificacion'] ?? null);

            // Items
            $stmt = $this->db->prepare("SELECT * FROM store_venta_items WHERE vit_venta_id = ? AND vit_tenant_id = ?");
            $stmt->execute([$ventaId, $this->tenantId]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Pagos
            $stmt = $this->db->prepare("SELECT * FROM store_venta_pagos WHERE vpg_venta_id = ? AND vpg_tenant_id = ?");
            $stmt->execute([$ventaId, $this->tenantId]);
            $pagos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $config = $this->getConfig();

            return $this->jsonResponse([
                'success' => true,
                'venta'   => $venta,
                'items'   => $items,
                'pagos'   => $pagos,
                'config'  => [
                    'nombre_tienda' => $config['nombre_tienda'] ?? 'DigiSports Store',
                    'direccion'     => $config['direccion_tienda'] ?? '',
                    'telefono'      => $config['telefono_tienda'] ?? '',
                    'ruc'           => $config['ruc_tienda'] ?? '',
                    'header'        => $config['ticket_header'] ?? '',
                    'footer'        => $config['ticket_footer'] ?? '',
                ]
            ]);

        } catch (\Exception $e) {
            $this->logError("Error generando ticket: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al generar ticket']);
        }
    }

    /* ═══════════════════════════════════════════════
     * MÉTODOS PRIVADOS
     * ═══════════════════════════════════════════════ */

    private function getTurnoAbierto() {
        $stmt = $this->db->prepare("
            SELECT t.*, c.caj_nombre
            FROM store_caja_turnos t
            JOIN store_cajas c ON c.caj_caja_id = t.tur_caja_id
            WHERE t.tur_usuario_id = ? AND t.tur_tenant_id = ? AND t.tur_estado = 'ABIERTO'
            LIMIT 1
        ");
        $stmt->execute([$this->userId, $this->tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    private function getStockDisponible($productoId, $varianteId = null) {
        $sql = "SELECT COALESCE(stk_disponible, 0) FROM store_stock WHERE stk_producto_id = ? AND stk_tenant_id = ?";
        $params = [$productoId, $this->tenantId];
        if ($varianteId) {
            $sql .= " AND stk_variante_id = ?";
            $params[] = $varianteId;
        } else {
            $sql .= " AND stk_variante_id IS NULL";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    private function descontarStock($productoId, $varianteId, $cantidad, $ventaId, $costoUnit = 0) {
        $sql = "SELECT stk_stock_id, stk_cantidad FROM store_stock WHERE stk_producto_id = ? AND stk_tenant_id = ?";
        $params = [$productoId, $this->tenantId];
        if ($varianteId) {
            $sql .= " AND stk_variante_id = ?";
            $params[] = $varianteId;
        } else {
            $sql .= " AND stk_variante_id IS NULL";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stock = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stockAnterior = $stock ? (int)$stock['stk_cantidad'] : 0;
        $stockPosterior = $stockAnterior - $cantidad;

        if ($stock) {
            $this->db->prepare("UPDATE store_stock SET stk_cantidad = ? WHERE stk_stock_id = ?")->execute([$stockPosterior, $stock['stk_stock_id']]);
        }

        // Movimiento
        $this->db->prepare("INSERT INTO store_stock_movimientos (mov_tenant_id, mov_producto_id, mov_variante_id, mov_tipo, mov_cantidad, mov_stock_anterior, mov_stock_posterior, mov_costo_unitario, mov_referencia_tipo, mov_referencia_id, mov_motivo, mov_usuario_id) VALUES (?, ?, ?, 'VENTA', ?, ?, ?, ?, 'VENTA', ?, 'Venta POS', ?)")
            ->execute([$this->tenantId, $productoId, $varianteId, -$cantidad, $stockAnterior, $stockPosterior, $costoUnit, $ventaId, $this->userId]);

        // Verificar alerta
        $stmtMin = $this->db->prepare("SELECT pro_stock_minimo FROM store_productos WHERE pro_producto_id = ? AND pro_tenant_id = ?");
        $stmtMin->execute([$productoId, $this->tenantId]);
        $minimo = (int)$stmtMin->fetchColumn();
        if ($stockPosterior <= $minimo) {
            $this->db->prepare("INSERT IGNORE INTO store_stock_alertas (ale_tenant_id, ale_producto_id, ale_stock_actual, ale_stock_minimo) VALUES (?, ?, ?, ?)")
                ->execute([$this->tenantId, $productoId, $stockPosterior, $minimo]);
        }
    }

    private function restaurarStock($productoId, $varianteId, $cantidad, $ventaId) {
        $sql = "SELECT stk_stock_id, stk_cantidad FROM store_stock WHERE stk_producto_id = ? AND stk_tenant_id = ?";
        $params = [$productoId, $this->tenantId];
        if ($varianteId) {
            $sql .= " AND stk_variante_id = ?";
            $params[] = $varianteId;
        } else {
            $sql .= " AND stk_variante_id IS NULL";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stock = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stockAnterior = $stock ? (int)$stock['stk_cantidad'] : 0;
        $stockPosterior = $stockAnterior + $cantidad;

        if ($stock) {
            $this->db->prepare("UPDATE store_stock SET stk_cantidad = ? WHERE stk_stock_id = ?")->execute([$stockPosterior, $stock['stk_stock_id']]);
        }

        $this->db->prepare("INSERT INTO store_stock_movimientos (mov_tenant_id, mov_producto_id, mov_variante_id, mov_tipo, mov_cantidad, mov_stock_anterior, mov_stock_posterior, mov_referencia_tipo, mov_referencia_id, mov_motivo, mov_usuario_id) VALUES (?, ?, ?, 'DEVOLUCION', ?, ?, ?, 'ANULACION', ?, 'Anulación de venta', ?)")
            ->execute([$this->tenantId, $productoId, $varianteId, $cantidad, $stockAnterior, $stockPosterior, $ventaId, $this->userId]);
    }

    private function generarNumeroVenta() {
        $config = $this->getConfig();
        $prefijo = $config['prefijo_venta'] ?? 'V-';

        $stmt = $this->db->prepare("SELECT MAX(CAST(SUBSTRING(ven_numero, LENGTH(?) + 1) AS UNSIGNED)) AS ultimo FROM store_ventas WHERE ven_tenant_id = ? AND ven_numero LIKE CONCAT(?, '%')");
        $stmt->execute([$prefijo, $this->tenantId, $prefijo]);
        $ultimo = (int)$stmt->fetchColumn();

        return $prefijo . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
    }

    private function actualizarCategoriaCliente($clienteId) {
        $stmt = $this->db->prepare("SELECT scl_num_compras, scl_total_compras FROM store_clientes WHERE scl_cliente_id = ? AND scl_tenant_id = ?");
        $stmt->execute([$clienteId, $this->tenantId]);
        $cli = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$cli) return;

        $numCompras = (int)$cli['scl_num_compras'];
        $totalCompras = (float)$cli['scl_total_compras'];

        if ($totalCompras >= 1000 || $numCompras >= 50) {
            $cat = 'VIP';
        } elseif ($totalCompras >= 500 || $numCompras >= 20) {
            $cat = 'FRECUENTE';
        } elseif ($numCompras >= 3) {
            $cat = 'REGULAR';
        } else {
            $cat = 'NUEVO';
        }

        $this->db->prepare("UPDATE store_clientes SET scl_categoria = ? WHERE scl_cliente_id = ? AND scl_tenant_id = ?")->execute([$cat, $clienteId, $this->tenantId]);
    }

    private function getConfig() {
        $stmt = $this->db->prepare("SELECT cfg_clave, cfg_valor FROM store_configuracion WHERE cfg_tenant_id = ?");
        $stmt->execute([$this->tenantId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $config = [];
        foreach ($rows as $r) {
            $config[$r['cfg_clave']] = $r['cfg_valor'];
        }
        return $config;
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
