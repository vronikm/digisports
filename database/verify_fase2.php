<?php
/**
 * Verificación de Fase 2 — DigiSports Arena
 * Comprueba controladores, vistas, tablas y menús
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "╔══════════════════════════════════════════════╗\n";
echo "║  VERIFICACIÓN FASE 2 — DigiSports Arena     ║\n";
echo "╚══════════════════════════════════════════════╝\n\n";

$ok = 0; $fail = 0;
function check($label, $condition) {
    global $ok, $fail;
    if ($condition) { echo "  ✅ $label\n"; $ok++; }
    else { echo "  ❌ $label\n"; $fail++; }
}

$base = dirname(__DIR__);

// 1. CONTROLADORES
echo "═══ CONTROLADORES ═══\n";
check('PagoController.php', file_exists("$base/app/controllers/reservas/PagoController.php"));
check('EntradaController.php', file_exists("$base/app/controllers/instalaciones/EntradaController.php"));

// Verificar que se pueden instanciar (parse check)
$output = shell_exec("php -l $base/app/controllers/reservas/PagoController.php 2>&1");
check('PagoController sintaxis OK', strpos($output, 'No syntax errors') !== false);

$output = shell_exec("php -l $base/app/controllers/instalaciones/EntradaController.php 2>&1");
check('EntradaController sintaxis OK', strpos($output, 'No syntax errors') !== false);

// 2. VISTAS PAGOS
echo "\n═══ VISTAS PAGOS ═══\n";
check('pagos/checkout.php', file_exists("$base/app/views/reservas/pagos/checkout.php"));
check('pagos/comprobante.php', file_exists("$base/app/views/reservas/pagos/comprobante.php"));
check('pagos/index.php', file_exists("$base/app/views/reservas/pagos/index.php"));

// 3. VISTAS ENTRADAS
echo "\n═══ VISTAS ENTRADAS ═══\n";
check('entradas/index.php', file_exists("$base/app/views/instalaciones/entradas/index.php"));
check('entradas/vender.php', file_exists("$base/app/views/instalaciones/entradas/vender.php"));
check('entradas/ticket.php', file_exists("$base/app/views/instalaciones/entradas/ticket.php"));
check('entradas/escanear.php', file_exists("$base/app/views/instalaciones/entradas/escanear.php"));
check('entradas/tarifas.php', file_exists("$base/app/views/instalaciones/entradas/tarifas.php"));

// 4. VISTA MODIFICADA
echo "\n═══ VISTA MODIFICADA ═══\n";
$verContent = file_get_contents("$base/app/views/reservas/ver.php");
check('ver.php tiene botón Cobrar', strpos($verContent, 'Cobrar') !== false);
check('ver.php enlaza a pago/checkout', strpos($verContent, 'pago') !== false && strpos($verContent, 'checkout') !== false);

// 5. BASE DE DATOS
echo "\n═══ BASE DE DATOS ═══\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=digisports_core','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tablas
    $tables = ['instalaciones_entradas', 'instalaciones_entradas_tarifas', 'instalaciones_reserva_pagos'];
    foreach ($tables as $t) {
        $r = $pdo->query("SELECT COUNT(*) FROM $t");
        check("Tabla $t existe (" . $r->fetchColumn() . " filas)", true);
    }

    // Vistas SQL
    $views = ['entradas', 'reservas', 'reserva_pagos'];
    foreach ($views as $v) {
        try {
            $pdo->query("SELECT 1 FROM $v LIMIT 0");
            check("Vista SQL '$v' funciona", true);
        } catch (Exception $e) {
            check("Vista SQL '$v' funciona", false);
        }
    }

    // Columnas de pago en reservas
    $r = $pdo->query("SELECT res_estado_pago, res_monto_pagado, res_saldo_pendiente FROM instalaciones_reservas LIMIT 1");
    check('Columnas de pago en instalaciones_reservas', $r !== false);

    // Menús
    echo "\n═══ MENÚS ═══\n";
    $r = $pdo->query("SELECT men_id, men_label FROM seguridad_menu WHERE men_id IN (113,114,115,116)");
    $menus = $r->fetchAll(PDO::FETCH_ASSOC);
    check('4 menús nuevos insertados', count($menus) === 4);
    foreach ($menus as $m) {
        echo "    [{$m['men_id']}] {$m['men_label']}\n";
    }

    // Permisos
    $r = $pdo->query("SELECT COUNT(*) FROM seguridad_rol_menu WHERE rme_menu_id IN (113,114,115,116) AND rme_rol_id = 1");
    check('Permisos admin para 4 menús', (int)$r->fetchColumn() === 4);

} catch (Exception $e) {
    echo "  ❌ Error BD: " . $e->getMessage() . "\n";
    $fail++;
}

// 6. MÉTODOS EN CONTROLADORES
echo "\n═══ MÉTODOS CONTROLADORES ═══\n";
$pagoContent = file_get_contents("$base/app/controllers/reservas/PagoController.php");
$pagoMethods = ['checkout', 'procesarPago', 'comprobante', 'index', 'anular', 'saldoCliente'];
foreach ($pagoMethods as $m) {
    check("PagoController::$m()", strpos($pagoContent, "function $m(") !== false || strpos($pagoContent, "function $m()") !== false);
}

$entradaContent = file_get_contents("$base/app/controllers/instalaciones/EntradaController.php");
$entradaMethods = ['index', 'vender', 'guardar', 'ticket', 'registrarIngreso', 'anular', 'tarifas', 'guardarTarifa', 'obtenerTarifas', 'escanear', 'buscarCodigo'];
foreach ($entradaMethods as $m) {
    check("EntradaController::$m()", strpos($entradaContent, "function $m(") !== false || strpos($entradaContent, "function $m()") !== false);
}

// RESUMEN
echo "\n╔══════════════════════════════════════════════╗\n";
echo "║  RESULTADO: $ok OK, $fail FALLOS";
echo str_repeat(' ', 30 - strlen("$ok OK, $fail FALLOS")) . "║\n";
echo "╚══════════════════════════════════════════════╝\n";

if ($fail === 0) {
    echo "\n🎉 FASE 2 COMPLETADA EXITOSAMENTE\n";
} else {
    echo "\n⚠️  HAY $fail PROBLEMAS POR RESOLVER\n";
}
