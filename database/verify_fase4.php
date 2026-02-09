<?php
/**
 * Verificación Fase 4 — DigiSports Arena
 * Valida: migraciones a ModuleController, vistas corregidas, nuevas vistas
 */

echo "═══════════════════════════════════════════════════\n";
echo "  DigiSports Arena — Verificación FASE 4\n";
echo "═══════════════════════════════════════════════════\n\n";

$basePath = __DIR__ . '/..';
$ok = 0;
$fail = 0;

function check($label, $result) {
    global $ok, $fail;
    $icon = $result ? '✅' : '❌';
    echo "  {$icon} {$label}\n";
    $result ? $ok++ : $fail++;
}

// ── 1. Controladores migrados a ModuleController ──
echo "── 1. Controladores → ModuleController ──\n";

$controllers = [
    'CanchaController'        => $basePath . '/app/controllers/instalaciones/CanchaController.php',
    'MantenimientoController' => $basePath . '/app/controllers/instalaciones/MantenimientoController.php',
    'ReservaController'       => $basePath . '/app/controllers/reservas/ReservaController.php',
];

foreach ($controllers as $name => $path) {
    $exists = file_exists($path);
    check("{$name} existe", $exists);
    if ($exists) {
        $content = file_get_contents($path);
        check("{$name} extiende ModuleController", strpos($content, 'extends \\App\\Controllers\\ModuleController') !== false);
        check("{$name} require ModuleController.php", strpos($content, "require_once BASE_PATH . '/app/controllers/ModuleController.php'") !== false);
        check("{$name} NO require BaseController.php", strpos($content, "require_once BASE_PATH . '/app/controllers/BaseController.php'") === false);
        check("{$name} usa renderModule()", strpos($content, '$this->renderModule(') !== false);
        check("{$name} NO usa \$this->render()", strpos($content, '$this->render(') === false);
        check("{$name} define moduloCodigo ARENA", strpos($content, "moduloCodigo = 'ARENA'") !== false || strpos($content, 'moduloCodigo = \'ARENA\'') !== false);
    }
}

// ── 2. Controladores ya existentes en ModuleController (no regresión) ──
echo "\n── 2. Controladores ya existentes (sin regresión) ──\n";

$existingModuleControllers = [
    'DashboardController'     => $basePath . '/app/controllers/instalaciones/DashboardController.php',
    'EntradaController'       => $basePath . '/app/controllers/instalaciones/EntradaController.php',
    'CalendarioController'    => $basePath . '/app/controllers/instalaciones/CalendarioController.php',
    'ReporteArenaController'  => $basePath . '/app/controllers/instalaciones/ReporteArenaController.php',
    'PagoController'          => $basePath . '/app/controllers/reservas/PagoController.php',
    'AbonController'          => $basePath . '/app/controllers/reservas/AbonController.php',
];

foreach ($existingModuleControllers as $name => $path) {
    $exists = file_exists($path);
    check("{$name} existe", $exists);
    if ($exists) {
        $content = file_get_contents($path);
        check("{$name} sigue usando ModuleController", strpos($content, 'ModuleController') !== false);
    }
}

// ── 3. Vista clientes/ver.php corregida ──
echo "\n── 3. Vista clientes/ver.php corregida ──\n";

$clienteVer = $basePath . '/app/views/clientes/ver.php';
$exists = file_exists($clienteVer);
check("clientes/ver.php existe", $exists);

if ($exists) {
    $cv = file_get_contents($clienteVer);
    
    // Variable $entradas declarada
    check("Declara \$entradas = \$entradas ?? []", strpos($cv, '$entradas = $entradas ?? []') !== false);
    
    // Reservas: usa fecha_reserva no fecha
    check("Reservas usa fecha_reserva", strpos($cv, "\$reserva['fecha_reserva']") !== false);
    check("Reservas NO usa \$reserva['fecha']", strpos($cv, "\$reserva['fecha']") === false);
    
    // Pagos: usa rpa_*
    check("Pagos usa rpa_fecha", strpos($cv, "\$pago['rpa_fecha']") !== false);
    check("Pagos usa rpa_monto", strpos($cv, "\$pago['rpa_monto']") !== false);
    check("Pagos usa rpa_metodo_pago", strpos($cv, "\$pago['rpa_metodo_pago']") !== false);
    check("Pagos NO usa \$pago['fecha']", strpos($cv, "\$pago['fecha']") === false);
    check("Pagos NO usa \$pago['monto']", strpos($cv, "\$pago['monto']") === false);
    
    // Abonos: usa abo_*
    check("Abonos usa abo_fecha_registro", strpos($cv, "\$abono['abo_fecha_registro']") !== false);
    check("Abonos usa abo_saldo", strpos($cv, "\$abono['abo_saldo']") !== false);
    check("Abonos usa abo_estado", strpos($cv, "\$abono['abo_estado']") !== false);
    check("Abonos NO usa \$abono['fecha']", strpos($cv, "\$abono['fecha']") === false);
    
    // Sección Entradas
    check("Tiene sección Entradas Compradas", strpos($cv, 'Entradas Compradas') !== false);
    check("Entradas usa ent_fecha", strpos($cv, "\$entrada['ent_fecha']") !== false);
    check("Entradas usa ent_monto_total", strpos($cv, "\$entrada['ent_monto_total']") !== false);
    
    // Columna estado_pago en reservas
    check("Reservas tiene columna Pago (estado_pago)", strpos($cv, "estado_pago") !== false);
}

// ── 4. Nuevas vistas de detalle ──
echo "\n── 4. Nuevas vistas de detalle ──\n";

$nuevasVistas = [
    'canchas/ver.php'        => $basePath . '/app/views/instalaciones/canchas/ver.php',
    'mantenimientos/ver.php' => $basePath . '/app/views/instalaciones/mantenimientos/ver.php',
];

foreach ($nuevasVistas as $name => $path) {
    $exists = file_exists($path);
    check("{$name} existe", $exists);
    if ($exists) {
        $content = file_get_contents($path);
        check("{$name} usa url()", strpos($content, "url('instalaciones'") !== false);
        check("{$name} NO usa \$baseUrl directo", strpos($content, '$baseUrl . ') === false);
    }
}

// ── 5. Vistas index corregidas ──
echo "\n── 5. Vistas index con URLs encriptadas ──\n";

$indexVistas = [
    'canchas/index.php'        => $basePath . '/app/views/instalaciones/canchas/index.php',
    'mantenimientos/index.php' => $basePath . '/app/views/instalaciones/mantenimientos/index.php',
];

foreach ($indexVistas as $name => $path) {
    $exists = file_exists($path);
    check("{$name} existe", $exists);
    if ($exists) {
        $content = file_get_contents($path);
        check("{$name} usa url() para enlaces", strpos($content, "url('instalaciones'") !== false);
        // Ya no usa $baseUrl . 'ruta' directa para acciones CRUD
        $usaBaseUrl = preg_match('/\$baseUrl\s*\.\s*[\'"]instalaciones/', $content);
        check("{$name} NO concatena \$baseUrl para rutas", !$usaBaseUrl);
        // Sin arrow functions fn() para PHP 7.4 compat (fn es PHP 7.4+ pero verificamos por consistencia)
        $usaMatch = strpos($content, 'match (') !== false || strpos($content, 'match(') !== false;
        check("{$name} NO usa match() de PHP 8", !$usaMatch);
    }
}

// ── 6. Métodos ver() en controladores ──
echo "\n── 6. Métodos ver() agregados ──\n";

$controllerVer = [
    'CanchaController'        => $basePath . '/app/controllers/instalaciones/CanchaController.php',
    'MantenimientoController' => $basePath . '/app/controllers/instalaciones/MantenimientoController.php',
];

foreach ($controllerVer as $name => $path) {
    $content = file_get_contents($path);
    check("{$name} tiene método ver()", strpos($content, 'public function ver()') !== false);
}

// ── RESUMEN ──
echo "\n═══════════════════════════════════════════════════\n";
echo "  RESULTADO: {$ok}/{" . ($ok + $fail) . "} verificaciones pasaron\n";
if ($fail > 0) {
    echo "  ❌ {$fail} FALLOS detectados\n";
} else {
    echo "  ✅ ¡FASE 4 COMPLETA — Todo OK!\n";
}
echo "═══════════════════════════════════════════════════\n";
