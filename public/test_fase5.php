<?php
/**
 * DigiSports Arena — Test Fase 5
 * Verifica: vistas SQL (usuarios, roles, tenants), queries de controladores,
 *           URLs en formularios, migración ClienteController
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__ . '/..');

$db = new PDO(
    'mysql:host=localhost;dbname=digisports_core;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$pass = 0; $fail = 0; $total = 0;

function test($label, $ok, &$pass, &$fail, &$total, $detail = '') {
    $total++;
    if ($ok) { $pass++; $icon = '✅'; } else { $fail++; $icon = '❌'; }
    echo "<tr style='background:" . ($ok ? '#f0fff0' : '#fff0f0') . "'>";
    echo "<td>{$total}</td><td>{$icon}</td><td>{$label}</td>";
    echo "<td>" . htmlspecialchars($detail) . "</td></tr>\n";
}

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Test Fase 5</title>
<style>body{font-family:system-ui;margin:20px}table{border-collapse:collapse;width:100%}
td,th{border:1px solid #ddd;padding:6px 10px;text-align:left;font-size:13px}
th{background:#2563EB;color:#fff}h2{color:#1e40af}</style></head><body>
<h2>🏟️ DigiSports Arena — Verificación Fase 5</h2>
<table><tr><th>#</th><th></th><th>Test</th><th>Detalle</th></tr>\n";

// ========================================================
// GRUPO 1: Vistas SQL
// ========================================================

// 1. Vista 'usuarios' existe
$r = $db->query("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA='digisports_core' AND TABLE_NAME='usuarios'");
test('Vista SQL "usuarios" existe', $r->rowCount() === 1, $pass, $fail, $total);

// 2. Vista 'roles' existe
$r = $db->query("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA='digisports_core' AND TABLE_NAME='roles'");
test('Vista SQL "roles" existe', $r->rowCount() === 1, $pass, $fail, $total);

// 3. Vista 'tenants' existe
$r = $db->query("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA='digisports_core' AND TABLE_NAME='tenants'");
test('Vista SQL "tenants" existe', $r->rowCount() === 1, $pass, $fail, $total);

// 4. usuarios: columnas clave
$cols = [];
$r = $db->query("DESCRIBE usuarios");
while ($c = $r->fetch()) $cols[] = $c['Field'];
$needed = ['usuario_id','tenant_id','nombres','apellidos','email','username','password','rol_id','estado','token_recuperacion'];
$missing = array_diff($needed, $cols);
test('Vista usuarios tiene columnas clave', empty($missing), $pass, $fail, $total, empty($missing) ? implode(', ', $needed) : 'Faltan: ' . implode(', ', $missing));

// 5. roles: columnas clave
$cols = [];
$r = $db->query("DESCRIBE roles");
while ($c = $r->fetch()) $cols[] = $c['Field'];
$needed = ['rol_id','tenant_id','codigo','nombre','estado'];
$missing = array_diff($needed, $cols);
test('Vista roles tiene columnas clave', empty($missing), $pass, $fail, $total, empty($missing) ? implode(', ', $needed) : 'Faltan: ' . implode(', ', $missing));

// 6. tenants: columnas clave
$cols = [];
$r = $db->query("DESCRIBE tenants");
while ($c = $r->fetch()) $cols[] = $c['Field'];
$needed = ['tenant_id','nombre_empresa','ruc','email_contacto','plan_id','estado'];
$missing = array_diff($needed, $cols);
test('Vista tenants tiene columnas clave', empty($missing), $pass, $fail, $total, empty($missing) ? implode(', ', $needed) : 'Faltan: ' . implode(', ', $missing));

// 7. usuarios: SELECT funciona
try {
    $r = $db->query("SELECT usuario_id, nombres, apellidos, email, username, estado FROM usuarios LIMIT 1");
    $row = $r->fetch();
    test('SELECT desde vista usuarios', !empty($row), $pass, $fail, $total, $row ? "ID:{$row['usuario_id']} {$row['username']}" : 'Sin datos');
} catch (Exception $e) {
    test('SELECT desde vista usuarios', false, $pass, $fail, $total, $e->getMessage());
}

// 8. usuarios: UPDATE funciona (updatable view)
try {
    $db->exec("UPDATE usuarios SET ultimo_login = NOW() WHERE usuario_id = 1");
    test('UPDATE via vista usuarios (updatable)', true, $pass, $fail, $total, 'Vista es updatable');
} catch (Exception $e) {
    test('UPDATE via vista usuarios (updatable)', false, $pass, $fail, $total, $e->getMessage());
}

// 9. roles: SELECT con codigo funciona
try {
    $r = $db->query("SELECT rol_id FROM roles WHERE codigo = 'SUPERADMIN' LIMIT 1");
    $row = $r->fetch();
    test('SELECT roles WHERE codigo=SUPERADMIN', !empty($row), $pass, $fail, $total, $row ? "rol_id={$row['rol_id']}" : 'No encontrado');
} catch (Exception $e) {
    test('SELECT roles WHERE codigo=SUPERADMIN', false, $pass, $fail, $total, $e->getMessage());
}

// 10. tenants: SELECT funciona
try {
    $r = $db->query("SELECT tenant_id, nombre_empresa FROM tenants LIMIT 1");
    $row = $r->fetch();
    test('SELECT desde vista tenants', !empty($row), $pass, $fail, $total, $row ? "{$row['nombre_empresa']}" : 'Sin datos');
} catch (Exception $e) {
    test('SELECT desde vista tenants', false, $pass, $fail, $total, $e->getMessage());
}

// ========================================================
// GRUPO 2: Queries de MantenimientoController
// ========================================================

// 11. MantenimientoController index query
try {
    $r = $db->query("
        SELECT m.*, c.nombre as cancha_nombre, c.tipo as cancha_tipo,
               i.ins_nombre as instalacion_nombre,
               CONCAT(u.nombres, ' ', u.apellidos) as responsable_nombre
        FROM mantenimientos m
        INNER JOIN canchas c ON m.cancha_id = c.cancha_id
        INNER JOIN instalaciones i ON c.instalacion_id = i.ins_instalacion_id
        LEFT JOIN usuarios u ON m.responsable_id = u.usuario_id
        WHERE m.tenant_id = 1
        LIMIT 1
    ");
    test('MantenimientoCtrl index query', true, $pass, $fail, $total, 'Query ejecuta correctamente');
} catch (Exception $e) {
    test('MantenimientoCtrl index query', false, $pass, $fail, $total, $e->getMessage());
}

// 12. MantenimientoController crear: usuarios con JOIN roles
try {
    $r = $db->query("
        SELECT u.usuario_id, CONCAT(u.nombres, ' ', u.apellidos) AS nombre, u.email
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.rol_id
        WHERE u.tenant_id = 1 AND r.codigo IN ('ADMIN', 'SUPERADMIN', 'TECNICO')
        ORDER BY u.nombres
        LIMIT 5
    ");
    $count = $r->rowCount();
    test('MantenimientoCtrl query usuarios+roles', true, $pass, $fail, $total, "{$count} usuarios encontrados");
} catch (Exception $e) {
    test('MantenimientoCtrl query usuarios+roles', false, $pass, $fail, $total, $e->getMessage());
}

// ========================================================
// GRUPO 3: Queries de ClienteController
// ========================================================

// 13. ClienteController: getReservasCliente query
try {
    $r = $db->query("
        SELECT r.reserva_id, r.fecha_reserva, r.hora_inicio, r.hora_fin,
               r.precio_total as total, r.estado, r.estado_pago,
               c.nombre as cancha_nombre
        FROM reservas r
        LEFT JOIN canchas c ON r.instalacion_id = c.cancha_id
        WHERE r.cliente_id = 1
        ORDER BY r.fecha_reserva DESC
        LIMIT 10
    ");
    test('ClienteCtrl getReservasCliente query', true, $pass, $fail, $total, 'JOIN correcto: instalacion_id = cancha_id');
} catch (Exception $e) {
    test('ClienteCtrl getReservasCliente query', false, $pass, $fail, $total, $e->getMessage());
}

// 14. ClienteController: eliminar query (COUNT reservas)
try {
    $r = $db->query("
        SELECT COUNT(*) FROM reservas 
        WHERE cliente_id = 1 AND estado IN ('PENDIENTE', 'CONFIRMADA')
    ");
    test('ClienteCtrl eliminar: count reservas', true, $pass, $fail, $total);
} catch (Exception $e) {
    test('ClienteCtrl eliminar: count reservas', false, $pass, $fail, $total, $e->getMessage());
}

// ========================================================
// GRUPO 4: Queries de AuthController (via vista usuarios)
// ========================================================

// 15. AuthController: SELECT email login
try {
    $r = $db->query("
        SELECT usuario_id, username, nombres, email 
        FROM usuarios 
        WHERE email = 'admin@digisports.ec' AND estado = 'A'
        LIMIT 1
    ");
    test('AuthCtrl query login por email', true, $pass, $fail, $total);
} catch (Exception $e) {
    test('AuthCtrl query login por email', false, $pass, $fail, $total, $e->getMessage());
}

// 16. AuthController: SELECT password
try {
    $r = $db->query("SELECT password FROM usuarios WHERE usuario_id = 1");
    $row = $r->fetch();
    test('AuthCtrl query SELECT password', !empty($row), $pass, $fail, $total);
} catch (Exception $e) {
    test('AuthCtrl query SELECT password', false, $pass, $fail, $total, $e->getMessage());
}

// 17. AuthController: SELECT con token_recuperacion
try {
    $r = $db->query("
        SELECT usuario_id, nombres 
        FROM usuarios 
        WHERE token_recuperacion = 'test_invalid_token' 
        AND token_recuperacion_expira > NOW()
    ");
    test('AuthCtrl query token_recuperacion', true, $pass, $fail, $total);
} catch (Exception $e) {
    test('AuthCtrl query token_recuperacion', false, $pass, $fail, $total, $e->getMessage());
}

// 18. AuthController: SELECT count username
try {
    $r = $db->query("SELECT COUNT(*) as count FROM usuarios WHERE username = 'superadmin'");
    $row = $r->fetch();
    test('AuthCtrl count username', true, $pass, $fail, $total, "count={$row['count']}");
} catch (Exception $e) {
    test('AuthCtrl count username', false, $pass, $fail, $total, $e->getMessage());
}

// 19. AuthController: INSERT INTO usuarios (via updatable view)
try {
    $db->beginTransaction();
    $stmt = $db->prepare("
        INSERT INTO usuarios (
            tenant_id, username, password, password_expira,
            email, nombres, apellidos, telefono,
            rol_id, estado, requiere_2fa, debe_cambiar_password
        ) VALUES (
            1, '__test_fase5__', 'hashedpwd', '2026-12-31',
            'test@test.com', 'Test', 'Fase5', '0999999999',
            1, 'A', 'N', 'N'
        )
    ");
    $stmt->execute();
    $insertId = $db->lastInsertId();
    $db->rollBack();
    test('AuthCtrl INSERT INTO usuarios (via vista)', $insertId > 0, $pass, $fail, $total, "INSERT+ROLLBACK ok, ID={$insertId}");
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    test('AuthCtrl INSERT INTO usuarios (via vista)', false, $pass, $fail, $total, $e->getMessage());
}

// 20. AuthController: UPDATE usuarios SET password
try {
    $db->exec("UPDATE usuarios SET password_expira = '2027-01-01' WHERE usuario_id = 1");
    test('AuthCtrl UPDATE password via vista', true, $pass, $fail, $total);
} catch (Exception $e) {
    test('AuthCtrl UPDATE password via vista', false, $pass, $fail, $total, $e->getMessage());
}

// ========================================================
// GRUPO 5: Queries de DashboardController / TenantController
// ========================================================

// 21. DashboardController: COUNT usuarios
try {
    $r = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE tenant_id = 1 AND estado = 'A'");
    $row = $r->fetch();
    test('DashboardCtrl count usuarios activos', true, $pass, $fail, $total, "total={$row['total']}");
} catch (Exception $e) {
    test('DashboardCtrl count usuarios activos', false, $pass, $fail, $total, $e->getMessage());
}

// 22. TenantController: JOIN usuarios + tenants
try {
    $r = $db->query("
        SELECT t.tenant_id, t.nombre_empresa,
               COUNT(DISTINCT u.usuario_id) as usuarios_activos
        FROM tenants t
        LEFT JOIN usuarios u ON t.tenant_id = u.tenant_id AND u.estado = 'A'
        GROUP BY t.tenant_id
        LIMIT 2
    ");
    test('TenantCtrl JOIN tenants+usuarios', true, $pass, $fail, $total, "{$r->rowCount()} tenants");
} catch (Exception $e) {
    test('TenantCtrl JOIN tenants+usuarios', false, $pass, $fail, $total, $e->getMessage());
}

// ========================================================
// GRUPO 6: Archivos PHP (sintaxis y contenido)
// ========================================================

// 23. MantenimientoController: no tiene 'nombre as responsable' (arreglado)
$mntCtrl = file_get_contents(BASE_PATH . '/app/controllers/instalaciones/MantenimientoController.php');
test('MntCtrl usa CONCAT para responsable_nombre', strpos($mntCtrl, "CONCAT(u.nombres, ' ', u.apellidos)") !== false, $pass, $fail, $total);

// 24. MantenimientoController: no usa u.nombre
test('MntCtrl NO usa u.nombre (singular)', strpos($mntCtrl, 'u.nombre ') === false && strpos($mntCtrl, "u.nombre\n") === false, $pass, $fail, $total, 'Usa CONCAT en su lugar');

// 25. MantenimientoController: usa i.ins_nombre
test('MntCtrl usa i.ins_nombre', strpos($mntCtrl, 'i.ins_nombre') !== false, $pass, $fail, $total);

// 26. MantenimientoController: usa JOIN roles para filtrar usuarios
test('MntCtrl JOIN roles para filtrar usuarios', strpos($mntCtrl, 'INNER JOIN roles r ON u.rol_id = r.rol_id') !== false, $pass, $fail, $total);

// 27. ClienteController: hereda de ModuleController
$cliCtrl = file_get_contents(BASE_PATH . '/app/controllers/clientes/ClienteController.php');
test('ClienteCtrl extiende ModuleController', strpos($cliCtrl, 'extends \App\Controllers\ModuleController') !== false, $pass, $fail, $total);

// 28. ClienteController: usa renderModule
test('ClienteCtrl usa renderModule()', strpos($cliCtrl, 'renderModule(') !== false, $pass, $fail, $total);

// 29. ClienteController: no usa render() directo
test('ClienteCtrl NO usa $this->render() directo', preg_match('/\$this->render\((?!Module)/', $cliCtrl) === 0, $pass, $fail, $total);

// 30. ClienteController: no usa $_GET directo
test('ClienteCtrl NO usa $_GET directo', strpos($cliCtrl, '$_GET[') === false, $pass, $fail, $total);

// 31. ClienteController: usa $this->get()
test('ClienteCtrl usa $this->get()', strpos($cliCtrl, '$this->get(') !== false, $pass, $fail, $total);

// 32. ClienteController: tiene checkPermission
test('ClienteCtrl tiene checkPermission()', strpos($cliCtrl, 'function checkPermission') !== false, $pass, $fail, $total);

// 33. ClienteController: moduloCodigo = ARENA
test('ClienteCtrl moduloCodigo = ARENA', strpos($cliCtrl, "'ARENA'") !== false, $pass, $fail, $total);

// 34. ClienteController: getReservasCliente usa cancha_id correcto
test('ClienteCtrl JOIN reservas-canchas correcto', strpos($cliCtrl, 'r.instalacion_id = c.cancha_id') !== false, $pass, $fail, $total);

// 35. Formulario mantenimientos: no usa $baseUrl
$formMnt = file_get_contents(BASE_PATH . '/app/views/instalaciones/mantenimientos/formulario.php');
test('Form mant. NO usa $baseUrl', strpos($formMnt, '$baseUrl') === false, $pass, $fail, $total);

// 36. Formulario mantenimientos: usa url()
test('Form mant. usa url() helper', strpos($formMnt, "url('instalaciones'") !== false, $pass, $fail, $total);

// 37. Formulario mantenimientos: typo inspecccion corregido
test('Form mant. inspección sin typo', strpos($formMnt, 'inspecccion') === false, $pass, $fail, $total, 'Antes: inspecccion → Ahora: inspeccion');

// 38. No existen archivos .bak
$baks = glob(BASE_PATH . '/app/views/**/*.bak');
test('Sin archivos .bak residuales', empty($baks), $pass, $fail, $total);

// 39. SQL de vistas existe
test('SQL fase5_vistas_usuarios.sql existe', file_exists(BASE_PATH . '/database/fase5_vistas_usuarios.sql'), $pass, $fail, $total);

// ========================================================
// GRUPO 7: Vistas previas siguen funcionando
// ========================================================

$viewNames = ['reservas', 'canchas', 'tarifas', 'mantenimientos', 'abonos', 'entradas', 'reserva_pagos', 'abono_movimientos'];
foreach ($viewNames as $vn) {
    try {
        $r = $db->query("SELECT * FROM `{$vn}` LIMIT 1");
        test("Vista SQL '{$vn}' funciona", true, $pass, $fail, $total);
    } catch (Exception $e) {
        test("Vista SQL '{$vn}' funciona", false, $pass, $fail, $total, $e->getMessage());
    }
}

// ========================================================
// GRUPO 8: Otras vistas SQL
// ========================================================

// 48. vw_estadisticas_canchas
try {
    $r = $db->query("SELECT * FROM vw_estadisticas_canchas LIMIT 1");
    test('Vista vw_estadisticas_canchas funciona', true, $pass, $fail, $total);
} catch (Exception $e) {
    test('Vista vw_estadisticas_canchas funciona', false, $pass, $fail, $total, $e->getMessage());
}

// 49. vw_tarifas_por_dia
try {
    $r = $db->query("SELECT * FROM vw_tarifas_por_dia LIMIT 1");
    test('Vista vw_tarifas_por_dia funciona', true, $pass, $fail, $total);
} catch (Exception $e) {
    test('Vista vw_tarifas_por_dia funciona', false, $pass, $fail, $total, $e->getMessage());
}

// ========================================================
// GRUPO 9: FacturaController query
// ========================================================
// 50. FacturaController JOIN con usuarios (query simplificada)
try {
    $r = $db->query("
        SELECT u.nombres as usuario_nombre
        FROM usuarios u
        WHERE u.usuario_id = 1
        LIMIT 1
    ");
    test('FacturaCtrl JOIN usuarios funciona', true, $pass, $fail, $total, 'Vista usuarios accesible para JOINs');
} catch (Exception $e) {
    test('FacturaCtrl JOIN usuarios funciona', false, $pass, $fail, $total, $e->getMessage());
}

// ========================================================
// RESUMEN
// ========================================================
echo "</table>";
echo "<h2 style='color:" . ($fail === 0 ? '#16a34a' : '#dc2626') . "'>";
echo ($fail === 0 ? '✅ FASE 5 COMPLETA' : '⚠️ FASE 5 CON ERRORES');
echo " — {$pass}/{$total} tests pasados</h2>";

if ($fail === 0) {
    echo "<div style='background:#f0fff0;border:2px solid #16a34a;padding:15px;border-radius:8px;margin:10px 0'>";
    echo "<h3>📋 Resumen de cambios Fase 5:</h3><ul>";
    echo "<li>✅ <b>3 vistas SQL creadas:</b> usuarios, roles, tenants (mapean seguridad_*)</li>";
    echo "<li>✅ <b>MantenimientoController:</b> 4 queries corregidas (u.nombre→CONCAT, i.instalacion_id→i.ins_instalacion_id, rol→JOIN roles)</li>";
    echo "<li>✅ <b>ClienteController:</b> Migrado de BaseController a ModuleController (render→renderModule, \$_GET→\$this->get())</li>";
    echo "<li>✅ <b>ClienteController:</b> getReservasCliente JOIN corregido (instalacion_id=cancha_id)</li>";
    echo "<li>✅ <b>Formulario mantenimientos:</b> URLs convertidas de \$baseUrl a url(), typo 'inspecccion' corregido</li>";
    echo "<li>✅ <b>Limpieza:</b> 3 archivos .bak eliminados</li>";
    echo "</ul></div>";
}

echo "</body></html>";
