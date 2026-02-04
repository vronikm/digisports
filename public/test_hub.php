<?php
/**
 * Test de Hub de Aplicaciones
 * Verificar que el sistema del Hub funciona correctamente
 */

require_once __DIR__ . '/../config/app.php';
require_once BASE_PATH . '/config/database.php';

echo "<h1>Test del Hub de Aplicaciones</h1>";
echo "<style>body { font-family: Arial, sans-serif; padding: 20px; } 
.success { color: green; } .error { color: red; } 
table { border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #f4f4f4; }</style>";

try {
    $db = Database::getInstance()->getConnection();
    echo "<p class='success'>✓ Conexión a BD exitosa</p>";
    
    // 1. Verificar tabla modulos
    echo "<h2>1. Tabla modulos</h2>";
    $stmt = $db->query("SELECT id, codigo, nombre, icono, color_fondo, ruta_modulo, ruta_controller, activo FROM modulos ORDER BY orden");
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($modulos) > 0) {
        echo "<p class='success'>✓ Se encontraron " . count($modulos) . " módulos</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Icono</th><th>Color</th><th>Módulo</th><th>Controller</th><th>Activo</th></tr>";
        foreach ($modulos as $m) {
            echo "<tr>";
            echo "<td>{$m['id']}</td>";
            echo "<td>{$m['codigo']}</td>";
            echo "<td>{$m['nombre']}</td>";
            echo "<td><i class='{$m['icono']}'></i> {$m['icono']}</td>";
            echo "<td style='background:{$m['color_fondo']};color:white;'>{$m['color_fondo']}</td>";
            echo "<td>{$m['ruta_modulo']}</td>";
            echo "<td>{$m['ruta_controller']}</td>";
            echo "<td>" . ($m['activo'] ? 'Sí' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>✗ No hay módulos en la tabla</p>";
    }
    
    // 2. Verificar tabla tenant_modulos
    echo "<h2>2. Suscripciones de Tenant 1</h2>";
    $stmt = $db->query("
        SELECT tm.*, m.nombre, m.codigo 
        FROM tenant_modulos tm 
        JOIN modulos m ON tm.modulo_id = m.id 
        WHERE tm.tenant_id = 1
    ");
    $suscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($suscripciones) > 0) {
        echo "<p class='success'>✓ Tenant 1 tiene " . count($suscripciones) . " módulos suscritos</p>";
        echo "<table>";
        echo "<tr><th>Módulo</th><th>Estado</th><th>Licencia</th><th>Fecha Inicio</th><th>Fecha Fin</th></tr>";
        foreach ($suscripciones as $s) {
            echo "<tr>";
            echo "<td>{$s['nombre']} ({$s['codigo']})</td>";
            echo "<td>{$s['estado']}</td>";
            echo "<td>{$s['tipo_licencia']}</td>";
            echo "<td>{$s['fecha_inicio']}</td>";
            echo "<td>" . ($s['fecha_fin'] ?? 'Sin vencimiento') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>✗ No hay suscripciones para Tenant 1</p>";
    }
    
    // 3. Verificar tabla rol_modulos
    echo "<h2>3. Permisos por Rol</h2>";
    $stmt = $db->query("
        SELECT rm.rol_id, m.nombre, rm.puede_ver, rm.puede_crear, rm.puede_editar, rm.puede_eliminar
        FROM rol_modulos rm
        JOIN modulos m ON rm.modulo_id = m.id
        ORDER BY rm.rol_id, m.orden
    ");
    $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($permisos) > 0) {
        echo "<p class='success'>✓ Se encontraron " . count($permisos) . " registros de permisos</p>";
        echo "<table>";
        echo "<tr><th>Rol ID</th><th>Módulo</th><th>Ver</th><th>Crear</th><th>Editar</th><th>Eliminar</th></tr>";
        foreach ($permisos as $p) {
            echo "<tr>";
            echo "<td>{$p['rol_id']}</td>";
            echo "<td>{$p['nombre']}</td>";
            echo "<td>" . ($p['puede_ver'] ? '✓' : '✗') . "</td>";
            echo "<td>" . ($p['puede_crear'] ? '✓' : '✗') . "</td>";
            echo "<td>" . ($p['puede_editar'] ? '✓' : '✗') . "</td>";
            echo "<td>" . ($p['puede_eliminar'] ? '✓' : '✗') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>✗ No hay permisos configurados</p>";
    }
    
    // 4. Simular consulta del HubController
    echo "<h2>4. Consulta del Hub (como HubController)</h2>";
    $tenantId = 1;
    $rolId = 1;
    
    $sql = "
        SELECT DISTINCT
            m.id,
            m.codigo,
            m.nombre,
            m.descripcion,
            m.icono,
            m.color_fondo,
            m.orden,
            m.ruta_modulo,
            m.ruta_controller,
            m.ruta_action,
            m.es_externo,
            m.url_externa,
            COALESCE(rm.puede_ver, 0) AS puede_ver,
            COALESCE(rm.puede_crear, 0) AS puede_crear,
            COALESCE(rm.puede_editar, 0) AS puede_editar,
            COALESCE(rm.puede_eliminar, 0) AS puede_eliminar
        FROM modulos m
        INNER JOIN tenant_modulos tm ON m.id = tm.modulo_id 
            AND tm.tenant_id = ? 
            AND tm.estado = 'ACTIVO'
            AND (tm.fecha_fin IS NULL OR tm.fecha_fin >= CURDATE())
        LEFT JOIN rol_modulos rm ON m.id = rm.modulo_id AND rm.rol_id = ?
        WHERE m.activo = 1
            AND (rm.puede_ver = 1 OR ? = 1)
        ORDER BY m.orden ASC
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$tenantId, $rolId, $rolId]);
    $modulosHub = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($modulosHub) > 0) {
        echo "<p class='success'>✓ El Hub mostraría " . count($modulosHub) . " módulos al usuario Admin</p>";
        
        // Mostrar preview visual
        echo "<div style='background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); padding: 30px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h3 style='color: white; text-align: center; margin-bottom: 20px;'>Preview del Hub</h3>";
        echo "<div style='display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; max-width: 900px; margin: 0 auto;'>";
        
        foreach ($modulosHub as $mod) {
            echo "<div style='background: white; border-radius: 12px; padding: 20px; text-align: center;'>";
            echo "<div style='width: 50px; height: 50px; background: {$mod['color_fondo']}; border-radius: 10px; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center;'>";
            echo "<i class='{$mod['icono']}' style='color: white; font-size: 20px;'></i>";
            echo "</div>";
            echo "<div style='font-weight: 600; color: #333;'>{$mod['nombre']}</div>";
            echo "</div>";
        }
        
        echo "</div></div>";
    } else {
        echo "<p class='error'>✗ No se encontraron módulos para mostrar en el Hub</p>";
    }
    
    // 5. Verificar archivos del Hub
    echo "<h2>5. Verificación de Archivos</h2>";
    
    $files = [
        'HubController' => BASE_PATH . '/app/controllers/core/HubController.php',
        'Vista Hub' => BASE_PATH . '/app/views/core/hub/index.php',
        'BaseController (renderHub)' => BASE_PATH . '/app/controllers/BaseController.php'
    ];
    
    foreach ($files as $name => $path) {
        if (file_exists($path)) {
            echo "<p class='success'>✓ $name existe: $path</p>";
        } else {
            echo "<p class='error'>✗ $name NO existe: $path</p>";
        }
    }
    
    // 6. Link para probar
    echo "<h2>6. Probar Hub</h2>";
    echo "<p><a href='?module=core&controller=hub&action=index' style='background: #3b82f6; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block;'>Ir al Hub →</a></p>";
    
    echo "<h2>✓ Test completado</h2>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
<!-- Font Awesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
