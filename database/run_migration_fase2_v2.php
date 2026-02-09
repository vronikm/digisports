<?php
/**
 * Ejecutar migración Fase 2 — sentencias individuales
 */
$pdo = new PDO('mysql:host=localhost;dbname=digisports_core', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "=== Migración Fase 2: Pagos y Entradas ===\n\n";

// 1. Tabla de entradas
echo "1. Creando instalaciones_entradas... ";
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS instalaciones_entradas (
            ent_entrada_id      INT AUTO_INCREMENT PRIMARY KEY,
            ent_tenant_id       INT NOT NULL,
            ent_instalacion_id  INT NOT NULL,
            ent_cliente_id      INT NULL,
            ent_codigo          VARCHAR(20) NOT NULL,
            ent_tipo            ENUM('GENERAL','VIP','CORTESIA','ABONADO') NOT NULL DEFAULT 'GENERAL',
            ent_precio          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            ent_descuento       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            ent_total           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            ent_forma_pago      VARCHAR(50) NULL,
            ent_monto_monedero  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            ent_monto_efectivo  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            ent_estado          ENUM('VENDIDA','USADA','ANULADA','VENCIDA') NOT NULL DEFAULT 'VENDIDA',
            ent_fecha_entrada   DATE NOT NULL,
            ent_hora_entrada    TIME NULL,
            ent_hora_salida     TIME NULL,
            ent_observaciones   TEXT NULL,
            ent_usuario_registro INT NULL,
            ent_fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ent_tenant (ent_tenant_id),
            INDEX idx_ent_fecha (ent_fecha_entrada),
            INDEX idx_ent_codigo (ent_codigo),
            INDEX idx_ent_cliente (ent_cliente_id),
            INDEX idx_ent_estado (ent_estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// 2. Tabla de tarifas de entrada
echo "2. Creando instalaciones_entradas_tarifas... ";
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS instalaciones_entradas_tarifas (
            ent_tar_id          INT AUTO_INCREMENT PRIMARY KEY,
            ent_tar_tenant_id   INT NOT NULL,
            ent_tar_instalacion_id INT NOT NULL,
            ent_tar_nombre      VARCHAR(100) NOT NULL,
            ent_tar_tipo        ENUM('GENERAL','VIP','CORTESIA','ABONADO') NOT NULL DEFAULT 'GENERAL',
            ent_tar_precio      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            ent_tar_dia_semana  TINYINT NULL,
            ent_tar_hora_inicio TIME NULL,
            ent_tar_hora_fin    TIME NULL,
            ent_tar_estado      ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
            ent_tar_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_etf_tenant (ent_tar_tenant_id),
            INDEX idx_etf_inst (ent_tar_instalacion_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// 3. Vista entradas
echo "3. Creando vista entradas... ";
try {
    $pdo->exec("
        CREATE OR REPLACE VIEW entradas AS
        SELECT
            ent_entrada_id      AS entrada_id,
            ent_tenant_id       AS tenant_id,
            ent_instalacion_id  AS instalacion_id,
            ent_cliente_id      AS cliente_id,
            ent_codigo          AS codigo,
            ent_tipo            AS tipo,
            ent_precio          AS precio,
            ent_descuento       AS descuento,
            ent_total           AS total,
            ent_forma_pago      AS forma_pago,
            ent_monto_monedero  AS monto_monedero,
            ent_monto_efectivo  AS monto_efectivo,
            ent_estado          AS estado,
            ent_fecha_entrada   AS fecha_entrada,
            ent_hora_entrada    AS hora_entrada,
            ent_hora_salida     AS hora_salida,
            ent_observaciones   AS observaciones,
            ent_usuario_registro AS usuario_registro,
            ent_fecha_registro  AS fecha_registro
        FROM instalaciones_entradas
    ");
    echo "OK\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// 4. Agregar columnas de pago a reservas si no existen
echo "4. Agregando columnas de pago a reservas... ";
$colsToAdd = [
    'res_estado_pago' => "ALTER TABLE instalaciones_reservas ADD COLUMN res_estado_pago ENUM('PENDIENTE','PARCIAL','PAGADO','REEMBOLSADO') NOT NULL DEFAULT 'PENDIENTE' AFTER res_abono_utilizado",
    'res_monto_pagado' => "ALTER TABLE instalaciones_reservas ADD COLUMN res_monto_pagado DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER res_estado_pago",
    'res_saldo_pendiente' => "ALTER TABLE instalaciones_reservas ADD COLUMN res_saldo_pendiente DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER res_monto_pagado"
];
foreach ($colsToAdd as $col => $sql) {
    try {
        $pdo->exec($sql);
        echo "$col ";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "($col ya existe) ";
        } else {
            echo "ERROR[$col]: " . $e->getMessage() . " ";
        }
    }
}
echo "\n";

// 5. Actualizar vista reservas para incluir nuevas columnas
echo "5. Actualizando vista reservas... ";
try {
    $pdo->exec("
        CREATE OR REPLACE VIEW reservas AS
        SELECT
            res_reserva_id         AS reserva_id,
            res_tenant_id          AS tenant_id,
            res_instalacion_id     AS instalacion_id,
            res_cliente_id         AS cliente_id,
            res_fecha_reserva      AS fecha_reserva,
            res_hora_inicio        AS hora_inicio,
            res_hora_fin           AS hora_fin,
            res_duracion_minutos   AS duracion_minutos,
            res_es_recurrente      AS es_recurrente,
            res_reserva_padre_id   AS reserva_padre_id,
            res_recurrencia_config AS recurrencia_config,
            res_tarifa_aplicada_id AS tarifa_aplicada_id,
            res_precio_base        AS precio_base,
            res_descuento_monto    AS descuento_monto,
            res_precio_total       AS precio_total,
            res_abono_utilizado    AS abono_utilizado,
            res_estado_pago        AS estado_pago,
            res_monto_pagado       AS monto_pagado,
            res_saldo_pendiente    AS saldo_pendiente,
            res_estado             AS estado,
            res_requiere_confirmacion AS requiere_confirmacion,
            res_fecha_confirmacion AS fecha_confirmacion,
            res_observaciones      AS observaciones,
            res_fecha_registro     AS fecha_registro,
            res_usuario_registro   AS usuario_registro
        FROM instalaciones_reservas
    ");
    echo "OK\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// 6. Inicializar saldo_pendiente en reservas existentes
echo "6. Inicializando saldo_pendiente... ";
try {
    $pdo->exec("
        UPDATE instalaciones_reservas 
        SET res_saldo_pendiente = res_precio_total - res_monto_pagado - res_abono_utilizado
        WHERE res_saldo_pendiente = 0 AND res_monto_pagado = 0 AND res_estado_pago = 'PENDIENTE'
    ");
    $affected = $pdo->query("SELECT ROW_COUNT()")->fetchColumn();
    echo "OK ($affected filas actualizadas)\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Verificación ===\n";
foreach (['instalaciones_entradas', 'instalaciones_entradas_tarifas'] as $t) {
    try {
        $cnt = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo "  ✅ $t ($cnt filas)\n";
    } catch (Exception $e) {
        echo "  ❌ $t: " . $e->getMessage() . "\n";
    }
}

// Verificar vistas
foreach (['entradas', 'reservas'] as $v) {
    try {
        $pdo->query("SELECT * FROM $v LIMIT 1");
        echo "  ✅ Vista '$v' funcional\n";
    } catch (Exception $e) {
        echo "  ❌ Vista '$v': " . $e->getMessage() . "\n";
    }
}

// Verificar nuevas columnas
try {
    $r = $pdo->query("SELECT res_estado_pago, res_monto_pagado, res_saldo_pendiente FROM instalaciones_reservas LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    echo "  ✅ Columnas de pago en reservas: " . json_encode($r) . "\n";
} catch (Exception $e) {
    echo "  ❌ Columnas de pago: " . $e->getMessage() . "\n";
}

echo "\n🎉 Migración Fase 2 completada.\n";
