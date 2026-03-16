<?php
/**
 * DigiSports — Script de Verificación: Módulo Formas de Pago
 *
 * Comprueba que todos los componentes del CRUD de Formas de Pago
 * están correctamente instalados y configurados.
 *
 * USO (desde raíz del proyecto):
 *   php database/verify_formas_pago.php
 */

define('BASE_PATH', dirname(__DIR__));

// ── Helpers de salida ──────────────────────────────────────────────────────
$ok = 0; $fail = 0; $warn = 0;

function pass(string $msg): void { global $ok;   $ok++;   echo "  ✅  $msg\n"; }
function fail(string $msg): void { global $fail; $fail++; echo "  ❌  $msg\n"; }
function warn(string $msg): void { global $warn; $warn++; echo "  ⚠️   $msg\n"; }
function title(string $t): void  { echo "\n── $t " . str_repeat('─', max(0, 60 - strlen($t))) . "\n"; }

// ══════════════════════════════════════════════════════════════════════════
title('1. ARCHIVOS PHP');
// ══════════════════════════════════════════════════════════════════════════

$files = [
    'app/controllers/facturacion/FormaPagoController.php' => 'Controlador',
    'app/views/facturacion/formas_pago/index.php'         => 'Vista principal',
    'database/migrations/008_menu_formas_pago.sql'        => 'Migración menú',
    'database/migrations/009_datos_iniciales_formas_pago.sql' => 'Migración datos iniciales',
];

foreach ($files as $rel => $label) {
    $abs = BASE_PATH . '/' . $rel;
    if (file_exists($abs)) {
        pass("$label: $rel");
    } else {
        fail("$label FALTA: $rel");
    }
}

// ── Sintaxis PHP ──────────────────────────────────────────────────────────
title('2. SINTAXIS PHP');

$phpFiles = [
    'app/controllers/facturacion/FormaPagoController.php',
    'app/views/facturacion/formas_pago/index.php',
];

// Detectar ejecutable PHP disponible (compatible Windows/Linux)
$phpBin = PHP_BINARY;
if (empty($phpBin) || !file_exists($phpBin)) {
    $phpBin = 'php'; // fallback PATH
}

foreach ($phpFiles as $rel) {
    $abs = BASE_PATH . '/' . $rel;
    if (!file_exists($abs)) { fail("No existe: $rel"); continue; }
    $absEsc = escapeshellarg($abs);
    $out    = shell_exec("\"$phpBin\" -l $absEsc 2>&1");
    if ($out === null) {
        warn("No se pudo verificar sintaxis (shell_exec deshabilitado): $rel");
    } elseif (strpos($out, 'No syntax errors') !== false) {
        pass("Sintaxis OK: $rel");
    } else {
        fail("Error sintaxis en $rel\n         → " . trim($out));
    }
}

// ── Contenido del controlador ─────────────────────────────────────────────
title('3. MÉTODOS DEL CONTROLADOR');

$ctrlPath = BASE_PATH . '/app/controllers/facturacion/FormaPagoController.php';
if (file_exists($ctrlPath)) {
    $src = file_get_contents($ctrlPath);
    $methods = ['index', 'guardar', 'eliminar', 'toggleEstado'];
    foreach ($methods as $m) {
        if (preg_match("/public function {$m}\s*\(/", $src)) {
            pass("Método public function $m()");
        } else {
            fail("Falta método: $m()");
        }
    }

    // Validar que usa namespace correcto
    if (strpos($src, 'namespace App\\Controllers\\Facturacion') !== false) {
        pass("Namespace: App\\Controllers\\Facturacion");
    } else {
        fail("Namespace incorrecto o faltante");
    }

    // Validar que extiende ModuleController
    if (strpos($src, 'extends \\App\\Controllers\\ModuleController') !== false) {
        pass("Hereda de ModuleController");
    } else {
        fail("No hereda de ModuleController");
    }

    // Validar CSRF en métodos escritura
    $writeMethods = ['guardar', 'eliminar', 'toggleEstado'];
    foreach ($writeMethods as $m) {
        if (preg_match("/function {$m}.*?validateCsrf/s", $src)) {
            pass("CSRF validado en $m()");
        } else {
            fail("CSRF NO validado en $m()");
        }
    }
}

// ── Contenido de la vista ──────────────────────────────────────────────────
title('4. ELEMENTOS DE LA VISTA');

$viewPath = BASE_PATH . '/app/views/facturacion/formas_pago/index.php';
if (file_exists($viewPath)) {
    $vsrc = file_get_contents($viewPath);
    $checks = [
        'modalFormaPago'          => 'Modal Bootstrap presente',
        'form-forma-pago'         => 'Formulario AJAX presente',
        'btn-nueva-forma'         => 'Botón Nueva Forma de Pago',
        'btn-editar'              => 'Botón editar presente',
        'btn-toggle'              => 'Botón toggle estado presente',
        'btn-eliminar'            => 'Botón eliminar presente',
        'ob_get_clean'            => 'Patrón ob_start/$scripts (CSP)',
        'cspNonce'                => 'Nonce CSP en script',
        'Swal.mixin'              => 'SweetAlert2 Toast mixin',
        'X-Requested-With'        => 'Header AJAX en fetch',
        'csrf_token'              => 'Token CSRF en formulario',
        'fpa_codigo_sri'          => 'Campo código SRI presente',
    ];
    foreach ($checks as $needle => $label) {
        if (strpos($vsrc, $needle) !== false) {
            pass($label);
        } else {
            fail("Falta en vista: $label ($needle)");
        }
    }
}

// ── BaseController detection ──────────────────────────────────────────────
title('5. DETECCIÓN DE MÓDULO (BaseController)');

$bcPath = BASE_PATH . '/app/controllers/BaseController.php';
if (file_exists($bcPath)) {
    $bcsrc = file_get_contents($bcPath);
    if (strpos($bcsrc, 'FormaPago') !== false) {
        pass("FormaPagoController detectado como módulo facturación");
    } else {
        fail("BaseController no detecta FormaPago → currentModule quedará vacío");
    }
}

// ══════════════════════════════════════════════════════════════════════════
// BASE DE DATOS
// ══════════════════════════════════════════════════════════════════════════
title('6. BASE DE DATOS');

$dbOk = false;
try {
    require_once BASE_PATH . '/config/env.php';
    require_once BASE_PATH . '/config/database.php';
    $db    = Database::getInstance()->getConnection();
    $dbOk  = true;
    pass("Conexión a la base de datos establecida");
} catch (\Throwable $e) {
    fail("No se pudo conectar a la BD: " . $e->getMessage());
}

if ($dbOk) {
    // 6a. Tabla facturacion_formas_pago
    title('6a. TABLA facturacion_formas_pago');
    try {
        $row = $db->query("DESCRIBE `facturacion_formas_pago`")->fetchAll(\PDO::FETCH_COLUMN);
        $required = ['fpa_id','fpa_tenant_id','fpa_nombre','fpa_codigo_sri','fpa_estado'];
        foreach ($required as $col) {
            if (in_array($col, $row)) {
                pass("Columna: $col");
            } else {
                fail("Columna faltante: $col");
            }
        }
    } catch (\PDOException $e) {
        fail("Tabla facturacion_formas_pago NO existe: " . $e->getMessage());
    }

    // 6b. Formas de pago por tenant
    title('6b. DATOS: FORMAS DE PAGO POR TENANT');
    try {
        $rows = $db->query("
            SELECT t.ten_tenant_id, t.ten_nombre_comercial,
                   COUNT(fp.fpa_id) AS total,
                   SUM(fp.fpa_estado = 'ACTIVO') AS activas
            FROM seguridad_tenants t
            LEFT JOIN facturacion_formas_pago fp ON fp.fpa_tenant_id = t.ten_tenant_id
            WHERE t.ten_estado_suscripcion != 'CANCELADA'
            GROUP BY t.ten_tenant_id, t.ten_nombre_comercial
        ")->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $r) {
            $nombre = $r['ten_nombre_comercial'];
            $tid    = $r['ten_tenant_id'];
            $total  = (int)$r['total'];
            $activas= (int)$r['activas'];
            if ($total >= 8) {
                pass("Tenant $tid ($nombre): $total formas de pago ($activas activas)");
            } elseif ($total > 0) {
                warn("Tenant $tid ($nombre): solo $total formas de pago (se esperan 8) — ejecutar migración 009");
            } else {
                fail("Tenant $tid ($nombre): SIN formas de pago — ejecutar migración 009");
            }
        }
    } catch (\PDOException $e) {
        fail("Error al consultar formas de pago: " . $e->getMessage());
    }

    // 6c. Módulo facturación en seguridad_modulos
    title('6c. MÓDULO FACTURACIÓN');
    try {
        $mod = $db->query("SELECT mod_id, mod_nombre, mod_activo FROM seguridad_modulos WHERE mod_codigo = 'facturacion' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
        if ($mod) {
            pass("Módulo facturación: mod_id={$mod['mod_id']}, nombre={$mod['mod_nombre']}, activo={$mod['mod_activo']}");
        } else {
            fail("Módulo 'facturación' NO existe en seguridad_modulos");
        }
    } catch (\PDOException $e) {
        fail("Error al verificar módulo: " . $e->getMessage());
    }

    // 6d. Menú formaPago en seguridad_menu
    title('6d. MENÚ (seguridad_menu)');
    try {
        $menu = $db->query("
            SELECT men_id, men_label, men_padre_id, men_orden, men_activo
            FROM seguridad_menu
            WHERE men_ruta_modulo = 'facturacion'
              AND men_ruta_controller = 'formaPago'
              AND men_ruta_action = 'index'
            LIMIT 1
        ")->fetch(\PDO::FETCH_ASSOC);

        if ($menu) {
            pass("Ítem de menú 'Formas de Pago': men_id={$menu['men_id']}, padre={$menu['men_padre_id']}, orden={$menu['men_orden']}, activo={$menu['men_activo']}");
        } else {
            fail("Ítem de menú 'Formas de Pago' NO existe — ejecutar migración 008");
        }
    } catch (\PDOException $e) {
        fail("Error al verificar menú: " . $e->getMessage());
    }

    // 6e. Asignación de roles al menú
    title('6e. ROLES CON ACCESO AL MENÚ');
    try {
        $count = $db->query("
            SELECT COUNT(*) FROM seguridad_rol_menu rm
            JOIN seguridad_menu m ON rm.rme_menu_id = m.men_id
            WHERE m.men_ruta_modulo = 'facturacion'
              AND m.men_ruta_controller = 'formaPago'
        ")->fetchColumn();

        if ((int)$count > 0) {
            pass("$count rol(es) con acceso al ítem de menú 'Formas de Pago'");
        } else {
            warn("Ningún rol asignado al menú — ejecutar migración 008");
        }
    } catch (\PDOException $e) {
        fail("Error al verificar roles de menú: " . $e->getMessage());
    }

    // 6f. seguridad_menu_config
    title('6f. PERMISOS SEGURIDAD (seguridad_menu_config)');
    try {
        $cfg = $db->query("
            SELECT con_opcion, con_permiso_requerido
            FROM seguridad_menu_config
            WHERE con_modulo_codigo = 'facturacion'
              AND con_opcion = 'Formas de Pago'
            LIMIT 1
        ")->fetch(\PDO::FETCH_ASSOC);

        if ($cfg) {
            pass("seguridad_menu_config: '{$cfg['con_opcion']}' (permiso: {$cfg['con_permiso_requerido']})");
        } else {
            warn("seguridad_menu_config sin entrada para 'Formas de Pago' — ejecutar migración 008");
        }
    } catch (\PDOException $e) {
        fail("Error al verificar menu_config: " . $e->getMessage());
    }
}

// ══════════════════════════════════════════════════════════════════════════
// RESUMEN FINAL
// ══════════════════════════════════════════════════════════════════════════
echo "\n" . str_repeat('═', 62) . "\n";
printf("  RESULTADO:  ✅ %d OK  |  ⚠️  %d ADVERTENCIAS  |  ❌ %d FALLOS\n", $ok, $warn, $fail);
echo str_repeat('═', 62) . "\n";

if ($fail === 0 && $warn === 0) {
    echo "  🎉 Todo listo. El módulo Formas de Pago está operativo.\n";
} elseif ($fail === 0) {
    echo "  ✅ Sin fallos críticos. Revisar advertencias antes de producción.\n";
} else {
    echo "  ❗ Hay $fail fallo(s) que deben corregirse antes de validar.\n";
}
echo str_repeat('═', 62) . "\n\n";

echo "── ORDEN DE EJECUCIÓN DE MIGRACIONES PENDIENTES ──────────────\n";
echo "  1. database/migrations/008_menu_formas_pago.sql\n";
echo "     → Crea el ítem de menú y asigna roles\n";
echo "  2. database/migrations/009_datos_iniciales_formas_pago.sql\n";
echo "     → Inserta 8 formas de pago SRI por cada tenant activo\n\n";
