<?php
/**
 * DigiSports — Validación Integral del Sistema
 * Verifica BD, sintaxis PHP, alineación de columnas y configuración
 * Fecha: 2026-02-18
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  DigiSports — Validación Integral del Sistema               ║\n";
echo "║  " . date('Y-m-d H:i:s') . "                                        ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$totalOk = 0;
$totalErr = 0;
$totalWarn = 0;
$errors = [];
$warnings = [];

function ok($msg) { global $totalOk; $totalOk++; echo "  ✓ $msg\n"; }
function err($msg) { global $totalErr, $errors; $totalErr++; $errors[] = $msg; echo "  ✗ $msg\n"; }
function warn($msg) { global $totalWarn, $warnings; $totalWarn++; $warnings[] = $msg; echo "  ⚠ $msg\n"; }
function section($title) { echo "\n━━━ $title ━━━\n"; }

$basePath = dirname(__DIR__);

// ═══════════════════════════════════════════════════════════════
// 1. CONEXIÓN A BASE DE DATOS
// ═══════════════════════════════════════════════════════════════
section("1. CONEXIÓN A BASE DE DATOS");
try {
    $db = new PDO('mysql:host=localhost;dbname=digisports_core;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    ok("Conexión a MySQL exitosa (digisports_core)");
    
    $ver = $db->query("SELECT VERSION()")->fetchColumn();
    ok("MySQL versión: $ver");
} catch (PDOException $e) {
    err("No se puede conectar a la BD: " . $e->getMessage());
    echo "\n¡Error crítico! No se puede continuar sin BD.\n";
    exit(1);
}

// ═══════════════════════════════════════════════════════════════
// 2. TABLAS DEL CORE (Seguridad / Multi-tenant)
// ═══════════════════════════════════════════════════════════════
section("2. TABLAS DEL CORE (Seguridad)");
$coreTables = [
    'seguridad_usuarios',
    'seguridad_roles',
    'seguridad_rol_modulos',
    'seguridad_rol_menu',
    'seguridad_tenants',
    'seguridad_planes_suscripcion',
    'seguridad_modulos',
    'seguridad_tenant_modulos',
    'seguridad_log_accesos',
    'seguridad_auditoria',
    'seguridad_auditoria_logs',
    'seguridad_menu',
    'seguridad_notificaciones_log',
];

foreach ($coreTables as $t) {
    $stm = $db->query("SHOW TABLES LIKE '$t'");
    if ($stm->fetchColumn()) {
        $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        ok("$t ($count registros)");
    } else {
        err("Tabla $t NO EXISTE");
    }
}

// ═══════════════════════════════════════════════════════════════
// 3. TABLAS COMPARTIDAS
// ═══════════════════════════════════════════════════════════════
section("3. TABLAS COMPARTIDAS");
$sharedTables = [
    'alumnos', 'clientes', 'instalaciones_sedes',
    'instalaciones_canchas', 'instalaciones_espacios',
];
foreach ($sharedTables as $t) {
    $stm = $db->query("SHOW TABLES LIKE '$t'");
    if ($stm->fetchColumn()) {
        $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        ok("$t ($count registros)");
    } else {
        warn("$t no encontrada (puede no ser requerida aún)");
    }
}

// ═══════════════════════════════════════════════════════════════
// 4. TABLAS DEL MÓDULO FÚTBOL
// ═══════════════════════════════════════════════════════════════
section("4. TABLAS MÓDULO FÚTBOL");
$futbolTables = [
    'futbol_ficha_alumno', 'futbol_campos_ficha', 'futbol_categorias',
    'futbol_periodos', 'futbol_grupos', 'futbol_grupo_horarios',
    'futbol_entrenadores', 'futbol_inscripciones', 'futbol_pagos',
    'futbol_comprobantes', 'futbol_asistencia', 'futbol_evaluaciones',
    'futbol_becas', 'futbol_beca_asignaciones', 'futbol_torneos',
    'futbol_torneo_jugadores', 'futbol_egresos', 'futbol_notificaciones',
    'futbol_configuracion',
];
foreach ($futbolTables as $t) {
    $stm = $db->query("SHOW TABLES LIKE '$t'");
    if ($stm->fetchColumn()) {
        $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        ok("$t ($count)");
    } else {
        err("$t NO EXISTE");
    }
}

// ═══════════════════════════════════════════════════════════════
// 5. TABLAS DEL MÓDULO NATACIÓN
// ═══════════════════════════════════════════════════════════════
section("5. TABLAS MÓDULO NATACIÓN");
$natacionTables = [
    'natacion_ficha_alumno', 'natacion_campos_ficha', 'natacion_niveles',
    'natacion_nivel_habilidades', 'natacion_instructores', 'natacion_piscinas',
    'natacion_grupos', 'natacion_grupo_horarios', 'natacion_inscripciones',
    'natacion_asistencia', 'natacion_evaluaciones', 'natacion_pagos',
    'natacion_periodos', 'natacion_configuracion',
];
foreach ($natacionTables as $t) {
    $stm = $db->query("SHOW TABLES LIKE '$t'");
    if ($stm->fetchColumn()) {
        $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        ok("$t ($count)");
    } else {
        err("$t NO EXISTE");
    }
}

// ═══════════════════════════════════════════════════════════════
// 6. ALINEACIÓN COLUMNAS vs CÓDIGO PHP (Puntos Críticos)
// ═══════════════════════════════════════════════════════════════
section("6. ALINEACIÓN COLUMNAS vs CÓDIGO PHP");

function checkColumnUsage($db, $table, $files, $basePath) {
    // Obtener columnas reales de la tabla
    $stm = $db->query("SHOW TABLES LIKE '$table'");
    if (!$stm->fetchColumn()) return; // tabla no existe, ya reportado
    
    $stm = $db->query("DESCRIBE `$table`");
    $realCols = array_column($stm->fetchAll(PDO::FETCH_ASSOC), 'Field');
    
    // Obtener prefijo de tabla (3 chars)
    $prefix = substr($realCols[0], 0, 4); // e.g. "fbe_"
    
    foreach ($files as $file) {
        $fullPath = $basePath . '/' . $file;
        if (!file_exists($fullPath)) continue;
        $content = file_get_contents($fullPath);
        
        // Buscar todas las referencias con el prefijo de la tabla
        preg_match_all('/\b(' . preg_quote($prefix, '/') . '[a-z_]+)\b/', $content, $matches);
        $usedCols = array_unique($matches[1] ?? []);
        
        foreach ($usedCols as $col) {
            if (!in_array($col, $realCols)) {
                // Podría ser un alias o clave de otra tabla
                // Solo reportar si parece pertenecer a esta tabla
                err("$file usa '$col' pero NO existe en $table");
            }
        }
    }
}

// Verificar tablas críticas que corregimos
$checks = [
    ['futbol_becas', [
        'app/controllers/futbol/BecaController.php',
        'app/views/futbol/becas/index.php',
    ]],
    ['futbol_evaluaciones', [
        'app/controllers/futbol/EvaluacionController.php',
        'app/views/futbol/evaluaciones/index.php',
    ]],
    ['futbol_asistencia', [
        'app/controllers/futbol/AsistenciaController.php',
        'app/views/futbol/asistencia/index.php',
    ]],
    ['futbol_torneos', [
        'app/controllers/futbol/TorneoController.php',
        'app/views/futbol/torneos/index.php',
    ]],
    ['futbol_torneo_jugadores', [
        'app/controllers/futbol/TorneoController.php',
        'app/views/futbol/torneos/convocatoria.php',
    ]],
    ['natacion_campos_ficha', [
        'app/controllers/natacion/CampoFichaController.php',
        'app/views/natacion/alumnos/formulario.php',
        'app/views/natacion/campoficha/index.php',
    ]],
    ['futbol_grupo_horarios', [
        'app/controllers/futbol/HorarioController.php',
        'app/views/futbol/horario/index.php',
    ]],
];

foreach ($checks as [$table, $files]) {
    checkColumnUsage($db, $table, $files, $basePath);
}

// Verificar que NO queden nombres viejos incorrectos
$badPatterns = [
    'fbc_' => ['app/controllers/futbol/BecaController.php', 'app/views/futbol/becas/index.php'],
    'ncf_nombre' => ['app/views/natacion/alumnos/formulario.php', 'app/views/natacion/campoficha/index.php', 'app/controllers/natacion/CampoFichaController.php'],
    'ncf_obligatorio' => ['app/views/natacion/alumnos/formulario.php', 'app/views/natacion/campoficha/index.php', 'app/controllers/natacion/CampoFichaController.php'],
    'fev_observaciones' => ['app/controllers/futbol/EvaluacionController.php', 'app/views/futbol/evaluaciones/index.php'],
    'fas_observaciones' => ['app/controllers/futbol/AsistenciaController.php', 'app/views/futbol/asistencia/index.php'],
];

$allClean = true;
foreach ($badPatterns as $bad => $files) {
    foreach ($files as $file) {
        $fullPath = $basePath . '/' . $file;
        if (!file_exists($fullPath)) continue;
        $content = file_get_contents($fullPath);
        if (strpos($content, $bad) !== false) {
            err("$file todavía contiene '$bad' (nombre de columna viejo)");
            $allClean = false;
        }
    }
}
if ($allClean && $totalErr === 0) {
    ok("Todos los nombres de columnas alineados correctamente");
}

// ═══════════════════════════════════════════════════════════════
// 7. SINTAXIS PHP — CONTROLADORES
// ═══════════════════════════════════════════════════════════════
section("7. SINTAXIS PHP — CONTROLADORES");

$modules = [
    'core' => 'app/controllers/core/',
    'seguridad' => 'app/controllers/seguridad/',
    'futbol' => 'app/controllers/futbol/',
    'natacion' => 'app/controllers/natacion/',
];

$phpExe = 'c:\\wamp64\\bin\\php\\php8.2.13\\php.exe';
$syntaxErrors = 0;

foreach ($modules as $mod => $dir) {
    $fullDir = $basePath . '/' . $dir;
    if (!is_dir($fullDir)) { warn("Directorio $dir no existe"); continue; }
    
    $files = glob($fullDir . '*.php');
    $modOk = 0;
    foreach ($files as $file) {
        $output = [];
        exec("\"$phpExe\" -l " . escapeshellarg($file) . ' 2>&1', $output, $code);
        if ($code !== 0) {
            err("[SYNTAX] " . basename($file) . " — " . implode(' ', $output));
            $syntaxErrors++;
        } else {
            $modOk++;
        }
    }
    ok("$mod: $modOk controladores válidos");
}

// Base controllers
foreach (['app/controllers/BaseController.php', 'app/controllers/ModuleController.php'] as $bc) {
    $fullPath = $basePath . '/' . $bc;
    if (file_exists($fullPath)) {
        $output = [];
        exec("\"$phpExe\" -l " . escapeshellarg($fullPath) . ' 2>&1', $output, $code);
        if ($code === 0) ok(basename($bc) . " válido");
        else err("[SYNTAX] " . basename($bc) . " — " . implode(' ', $output));
    } else {
        err("$bc NO EXISTE");
    }
}

// ═══════════════════════════════════════════════════════════════
// 8. SINTAXIS PHP — VISTAS
// ═══════════════════════════════════════════════════════════════
section("8. SINTAXIS PHP — VISTAS");

$viewDirs = [
    'natacion/alumnos', 'natacion/campoficha', 'natacion/dashboard',
    'futbol/alumnos', 'futbol/becas', 'futbol/evaluaciones', 
    'futbol/asistencia', 'futbol/torneos', 'futbol/horario',
    'futbol/pagos', 'futbol/inscripciones', 'futbol/dashboard',
    'seguridad/usuarios', 'seguridad/roles', 'seguridad/dashboard',
    'core', 'dashboard', 'layouts',
];

$viewErrors = 0;
$viewOk = 0;
foreach ($viewDirs as $vd) {
    $fullDir = $basePath . '/app/views/' . $vd;
    if (!is_dir($fullDir)) continue;
    
    $files = glob($fullDir . '/*.php');
    foreach ($files as $file) {
        $output = [];
        exec("\"$phpExe\" -l " . escapeshellarg($file) . ' 2>&1', $output, $code);
        if ($code !== 0) {
            err("[SYNTAX] views/$vd/" . basename($file) . " — " . implode(' ', $output));
            $viewErrors++;
        } else {
            $viewOk++;
        }
    }
}
ok("$viewOk vistas validadas sin errores de sintaxis");
if ($viewErrors > 0) err("$viewErrors vistas con errores de sintaxis");

// ═══════════════════════════════════════════════════════════════
// 9. HELPERS Y SERVICIOS
// ═══════════════════════════════════════════════════════════════
section("9. HELPERS Y SERVICIOS");

$helpers = glob($basePath . '/app/helpers/*.php');
$helpOk = 0;
foreach ($helpers as $h) {
    $output = [];
    exec("\"$phpExe\" -l " . escapeshellarg($h) . ' 2>&1', $output, $code);
    if ($code === 0) $helpOk++;
    else err("[SYNTAX] helpers/" . basename($h));
}
ok("$helpOk helpers válidos");

$services = glob($basePath . '/app/services/*.php');
$svcOk = 0;
foreach ($services as $s) {
    $output = [];
    exec("\"$phpExe\" -l " . escapeshellarg($s) . ' 2>&1', $output, $code);
    if ($code === 0) $svcOk++;
    else err("[SYNTAX] services/" . basename($s));
}
ok("$svcOk servicios válidos");

// ═══════════════════════════════════════════════════════════════
// 10. CONFIGURACIÓN
// ═══════════════════════════════════════════════════════════════
section("10. CONFIGURACIÓN");

$configs = ['config/app.php', 'config/database.php', 'config/Router.php', 'config/security.php'];
foreach ($configs as $c) {
    $fullPath = $basePath . '/' . $c;
    if (file_exists($fullPath)) {
        $output = [];
        exec("\"$phpExe\" -l " . escapeshellarg($fullPath) . ' 2>&1', $output, $code);
        if ($code === 0) ok("$c válido");
        else err("[SYNTAX] $c");
    } else {
        err("$c NO EXISTE");
    }
}

// ═══════════════════════════════════════════════════════════════
// 11. DATOS DE PRUEBA (Tenants, Usuarios, Módulos)
// ═══════════════════════════════════════════════════════════════
section("11. DATOS DE PRUEBA");

// Tenants
$stm = $db->query("SELECT ten_tenant_id, ten_nombre_comercial, ten_estado FROM seguridad_tenants LIMIT 5");
$tenants = $stm->fetchAll(PDO::FETCH_ASSOC);
if (count($tenants) > 0) {
    foreach ($tenants as $t) {
        ok("Tenant #{$t['ten_tenant_id']}: {$t['ten_nombre_comercial']} [{$t['ten_estado']}]");
    }
} else {
    warn("No hay tenants registrados");
}

// Usuarios
$stm = $db->query("SELECT COUNT(*) FROM seguridad_usuarios");
$uCount = $stm->fetchColumn();
ok("$uCount usuarios registrados");

// Módulos activos
$stm = $db->query("SELECT mod_codigo, mod_nombre, mod_activo FROM seguridad_modulos ORDER BY mod_codigo");
$mods = $stm->fetchAll(PDO::FETCH_ASSOC);
foreach ($mods as $m) {
    $state = $m['mod_activo'] ? 'ACTIVO' : 'INACTIVO';
    ok("Módulo {$m['mod_codigo']}: {$m['mod_nombre']} [$state]");
}

// ═══════════════════════════════════════════════════════════════
// 12. ARCHIVOS ENTRY POINT
// ═══════════════════════════════════════════════════════════════
section("12. ARCHIVOS ENTRY POINT");
$entryPoints = ['public/index.php', 'public/home.php'];
foreach ($entryPoints as $ep) {
    if (file_exists($basePath . '/' . $ep)) ok("$ep existe");
    else warn("$ep no encontrado");
}

// ═══════════════════════════════════════════════════════════════
// 13. DataProtection (LOPDP)
// ═══════════════════════════════════════════════════════════════
section("13. DataProtection (LOPDP)");
$dpFile = $basePath . '/app/services/DataProtection.php';
if (file_exists($dpFile)) {
    ok("DataProtection.php existe");
    $dpContent = file_get_contents($dpFile);
    // Verificar que tiene las tablas configuradas
    foreach (['clientes', 'alumnos', 'seguridad_usuarios'] as $dpTable) {
        if (strpos($dpContent, "'$dpTable'") !== false) {
            ok("DataProtection configura tabla '$dpTable'");
        } else {
            warn("DataProtection NO configura tabla '$dpTable'");
        }
    }
} else {
    err("DataProtection.php NO EXISTE");
}

// ═══════════════════════════════════════════════════════════════
// RESUMEN FINAL
// ═══════════════════════════════════════════════════════════════
echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║  RESUMEN DE VALIDACIÓN                                      ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
printf("║  ✓ OK:           %3d                                       ║\n", $totalOk);
printf("║  ⚠ Advertencias: %3d                                       ║\n", $totalWarn);
printf("║  ✗ Errores:      %3d                                       ║\n", $totalErr);
echo "╚══════════════════════════════════════════════════════════════╝\n";

if ($totalErr > 0) {
    echo "\nERRORES DETECTADOS:\n";
    foreach ($errors as $i => $e) {
        echo "  " . ($i + 1) . ". $e\n";
    }
}
if ($totalWarn > 0) {
    echo "\nADVERTENCIAS:\n";
    foreach ($warnings as $i => $w) {
        echo "  " . ($i + 1) . ". $w\n";
    }
}

echo "\nValidación completada.\n";
