<?php
/**
 * Verificación Fase 3 — Integración y Reportes
 * Ejecutar: php database/verify_fase3.php
 */

$basePath = dirname(__DIR__);
$host = 'localhost';
$dbname = 'digisports_core';
$user = 'root';
$pass = '';

$checks = [];
$passed = 0;
$total = 0;

function check($label, $result, $detail = '') {
    global $checks, $passed, $total;
    $total++;
    if ($result) { $passed++; $icon = '✅'; }
    else { $icon = '❌'; }
    $msg = "{$icon} [{$total}] {$label}";
    if ($detail) $msg .= " — {$detail}";
    echo $msg . "\n";
    $checks[] = ['label' => $label, 'ok' => $result];
}

echo "╔══════════════════════════════════════════════╗\n";
echo "║   DigiSports Arena — Verificación Fase 3    ║\n";
echo "╚══════════════════════════════════════════════╝\n\n";

// ── 1. Archivos de controladores ──
echo "── Controladores ──\n";
check('ReservaController existe', file_exists($basePath . '/app/controllers/reservas/ReservaController.php'));
check('DashboardController existe', file_exists($basePath . '/app/controllers/instalaciones/DashboardController.php'));
check('ClienteController existe', file_exists($basePath . '/app/controllers/clientes/ClienteController.php'));
check('ReporteArenaController existe', file_exists($basePath . '/app/controllers/instalaciones/ReporteArenaController.php'));

// ── 2. Vistas ──
echo "\n── Vistas ──\n";
check('Vista reservas/index.php', file_exists($basePath . '/app/views/reservas/index.php'));
check('Vista reservas/ver.php', file_exists($basePath . '/app/views/reservas/ver.php'));
check('Vista dashboard/index.php', file_exists($basePath . '/app/views/instalaciones/dashboard/index.php'));
check('Vista reportes/index.php', file_exists($basePath . '/app/views/instalaciones/reportes/index.php'));
check('Vista reportes/ingresos.php', file_exists($basePath . '/app/views/instalaciones/reportes/ingresos.php'));

// ── 3. Contenido de controladores ──
echo "\n── Métodos en ReservaController ──\n";
$rcContent = file_get_contents($basePath . '/app/controllers/reservas/ReservaController.php');
check('Método completar() existe', strpos($rcContent, 'public function completar()') !== false);
check('Método getReservasKPIs() existe', strpos($rcContent, 'private function getReservasKPIs()') !== false);
check('Filtro estado_pago en index', strpos($rcContent, 'estado_pago') !== false);
check('Filtro búsqueda en index', strpos($rcContent, 'buscar') !== false);
check('Filtro fecha_desde en index', strpos($rcContent, 'fecha_desde') !== false);
check('KPI recaudado_mes usa instalaciones_reserva_pagos', strpos($rcContent, 'instalaciones_reserva_pagos') !== false);
check('Historial pagos en ver()', strpos($rcContent, 'rpa_pago_id') !== false);

echo "\n── Métodos en DashboardController ──\n";
$dcContent = file_get_contents($basePath . '/app/controllers/instalaciones/DashboardController.php');
check('KPI usa instalaciones_reserva_pagos', strpos($dcContent, 'instalaciones_reserva_pagos') !== false);
check('KPI entradas vendidas', strpos($dcContent, 'instalaciones_entradas') !== false);
check('KPI monedero total', strpos($dcContent, 'instalaciones_abonos') !== false);
check('Método getUltimosPagos()', strpos($dcContent, 'getUltimosPagos') !== false);
check('Método getChartMetodosPago()', strpos($dcContent, 'getChartMetodosPago') !== false);
check('Tendencia ingresos mes anterior', strpos($dcContent, 'pctCambio') !== false);

echo "\n── Correcciones en ClienteController ──\n";
$ccContent = file_get_contents($basePath . '/app/controllers/clientes/ClienteController.php');
check('getReservasCliente usa fecha_reserva', strpos($ccContent, 'r.fecha_reserva') !== false);
check('getPagosCliente usa instalaciones_reserva_pagos', strpos($ccContent, 'instalaciones_reserva_pagos') !== false);
check('getAbonosCliente usa instalaciones_abonos', strpos($ccContent, 'instalaciones_abonos') !== false);
check('getEntradasCliente existe', strpos($ccContent, 'getEntradasCliente') !== false);
check('validateClienteData usa blindIndex', strpos($ccContent, 'blindIndex') !== false && strpos($ccContent, 'cli_identificacion_hash') !== false);

echo "\n── ReporteArenaController ──\n";
$raContent = file_get_contents($basePath . '/app/controllers/instalaciones/ReporteArenaController.php');
check('Método index()', strpos($raContent, 'public function index()') !== false);
check('Método ingresos()', strpos($raContent, 'public function ingresos()') !== false);
check('getResumenIngresos()', strpos($raContent, 'getResumenIngresos') !== false);
check('getTopClientes()', strpos($raContent, 'getTopClientes') !== false);
check('getOcupacionCanchas()', strpos($raContent, 'getOcupacionCanchas') !== false);
check('getMovimientosMonedero()', strpos($raContent, 'getMovimientosMonedero') !== false);

// ── 4. Vistas contenido ──
echo "\n── Contenido de vistas ──\n";
$viContent = file_get_contents($basePath . '/app/views/reservas/index.php');
check('Index reservas tiene KPIs', strpos($viContent, 'small-box') !== false);
check('Index reservas tiene estado_pago', strpos($viContent, 'estado_pago') !== false);
check('Index reservas tiene botón Cobrar', strpos($viContent, 'cash-register') !== false);
check('Index reservas tiene búsqueda', strpos($viContent, 'name="buscar"') !== false);
check('Index reservas tiene filtro fecha', strpos($viContent, 'name="fecha_desde"') !== false);

$vvContent = file_get_contents($basePath . '/app/views/reservas/ver.php');
check('Ver reserva tiene historial pagos', strpos($vvContent, 'Historial de Pagos') !== false);

$vdContent = file_get_contents($basePath . '/app/views/instalaciones/dashboard/index.php');
check('Dashboard NO usa match()', strpos($vdContent, 'match (') === false && strpos($vdContent, 'match(') === false);
check('Dashboard tiene últimos pagos', strpos($vdContent, 'ultimos_pagos') !== false || strpos($vdContent, 'ultimosPagos') !== false);
check('Dashboard tiene chart métodos pago', strpos($vdContent, 'chartMetodosPago') !== false);

// ── 5. Base de datos ──
echo "\n── Base de datos ──\n";
try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Menú 117
    $stmt = $pdo->prepare("SELECT men_label FROM seguridad_menu WHERE men_id = 117");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    check('Menú 117 existe', $row !== false, $row ? $row['men_label'] : 'No encontrado');

    // Permisos rol 1 y 2
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM seguridad_rol_menu WHERE rme_menu_id = 117");
    $stmt->execute();
    check('Permisos menú 117', (int)$stmt->fetchColumn() >= 2, '≥2 roles con acceso');

    // Vista reservas tiene estado_pago
    $stmt = $pdo->query("SHOW CREATE VIEW reservas");
    $viewDef = $stmt->fetch(PDO::FETCH_ASSOC);
    $viewSql = $viewDef['Create View'] ?? '';
    check('Vista SQL reservas incluye estado_pago', strpos($viewSql, 'estado_pago') !== false);
    check('Vista SQL reservas incluye saldo_pendiente', strpos($viewSql, 'saldo_pendiente') !== false);

} catch (PDOException $e) {
    check('Conexión a BD', false, $e->getMessage());
}

// ── Resumen ──
echo "\n╔══════════════════════════════════════════════╗\n";
echo "║   RESULTADO: {$passed}/{$total} verificaciones OK";
if ($passed === $total) {
    echo "        ║\n";
    echo "║   ✅ FASE 3 COMPLETA                         ║\n";
} else {
    $failed = $total - $passed;
    echo str_repeat(' ', 8 - strlen("{$passed}/{$total}")) . "║\n";
    echo "║   ⚠ {$failed} verificaciones fallaron              ║\n";
}
echo "╚══════════════════════════════════════════════╝\n";
