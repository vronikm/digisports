<?php
/**
 * Runner para crear tablas y vistas faltantes fase 1
 */
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=digisports_core;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // 1. Crear tabla instalaciones_abono_movimientos
    $pdo->exec("CREATE TABLE IF NOT EXISTS instalaciones_abono_movimientos (
        mov_movimiento_id    INT AUTO_INCREMENT PRIMARY KEY,
        mov_tenant_id        INT NOT NULL,
        mov_abono_id         INT NOT NULL,
        mov_cliente_id       INT NOT NULL,
        mov_tipo             VARCHAR(20) NOT NULL COMMENT 'RECARGA, CONSUMO, DEVOLUCION, AJUSTE, VENCIMIENTO',
        mov_monto            DECIMAL(10,2) NOT NULL,
        mov_saldo_anterior   DECIMAL(10,2) NOT NULL,
        mov_saldo_posterior  DECIMAL(10,2) NOT NULL,
        mov_descripcion      VARCHAR(255) DEFAULT NULL,
        mov_referencia_tipo  VARCHAR(50)  DEFAULT NULL COMMENT 'RESERVA, PAGO, MANUAL',
        mov_referencia_id    INT          DEFAULT NULL,
        mov_forma_pago       VARCHAR(50)  DEFAULT NULL,
        mov_usuario_registro INT          DEFAULT NULL,
        mov_fecha_registro   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tenant  (mov_tenant_id),
        INDEX idx_cliente (mov_cliente_id),
        INDEX idx_abono   (mov_abono_id),
        INDEX idx_fecha   (mov_fecha_registro),
        FOREIGN KEY (mov_tenant_id)  REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE,
        FOREIGN KEY (mov_abono_id)   REFERENCES instalaciones_abonos(abo_abono_id) ON DELETE CASCADE,
        FOREIGN KEY (mov_cliente_id) REFERENCES clientes(cli_cliente_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "OK: instalaciones_abono_movimientos\n";
    
    // 2. Crear vista abono_movimientos
    $pdo->exec("DROP VIEW IF EXISTS abono_movimientos");
    $pdo->exec("CREATE VIEW abono_movimientos AS
    SELECT
        mov_movimiento_id   AS movimiento_id,
        mov_tenant_id       AS tenant_id,
        mov_abono_id        AS abono_id,
        mov_cliente_id      AS cliente_id,
        mov_tipo            AS tipo,
        mov_monto           AS monto,
        mov_saldo_anterior  AS saldo_anterior,
        mov_saldo_posterior AS saldo_posterior,
        mov_descripcion     AS descripcion,
        mov_referencia_tipo AS referencia_tipo,
        mov_referencia_id   AS referencia_id,
        mov_forma_pago      AS forma_pago,
        mov_usuario_registro AS usuario_registro,
        mov_fecha_registro  AS fecha_registro
    FROM instalaciones_abono_movimientos");
    echo "OK: vista abono_movimientos\n";
    
    // 3. Crear tabla instalaciones_paquetes
    $pdo->exec("CREATE TABLE IF NOT EXISTS instalaciones_paquetes (
        paq_paquete_id       INT AUTO_INCREMENT PRIMARY KEY,
        paq_tenant_id        INT NOT NULL,
        paq_nombre           VARCHAR(100) NOT NULL,
        paq_descripcion      TEXT,
        paq_horas_incluidas  INT NOT NULL DEFAULT 10,
        paq_precio_normal    DECIMAL(10,2) NOT NULL COMMENT 'Precio sin descuento',
        paq_precio_paquete   DECIMAL(10,2) NOT NULL COMMENT 'Precio con descuento',
        paq_descuento_pct    DECIMAL(5,2)  DEFAULT 0,
        paq_dias_vigencia    INT NOT NULL DEFAULT 90,
        paq_estado           VARCHAR(20) DEFAULT 'ACTIVO',
        paq_fecha_creacion   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tenant (paq_tenant_id),
        FOREIGN KEY (paq_tenant_id) REFERENCES seguridad_tenants(ten_tenant_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "OK: instalaciones_paquetes\n";
    
    // 4. Crear vistas de instalaciones (alias columnas ins_*) y clientes (alias columnas cli_*)
    // Primero verificar columnas de instalaciones
    $cols = $pdo->query("SHOW COLUMNS FROM instalaciones")->fetchAll(PDO::FETCH_COLUMN);
    echo "\nColumnas de instalaciones: " . implode(', ', $cols) . "\n";
    
    $cols2 = $pdo->query("SHOW COLUMNS FROM clientes")->fetchAll(PDO::FETCH_COLUMN);
    echo "Columnas de clientes: " . implode(', ', $cols2) . "\n";
    
    // Verificar todas las vistas
    $views = $pdo->query("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA='digisports_core'")->fetchAll(PDO::FETCH_COLUMN);
    echo "\nVistas creadas: " . implode(', ', $views) . "\n";
    
    echo "\n=== Migración completada con éxito ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
