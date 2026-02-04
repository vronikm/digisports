<?php
/**
 * Insertar datos de prueba para Instalaciones y Canchas
 */
define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');

require_once CONFIG_PATH . '/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Insertando Datos de Prueba</h2>";

try {
    $tenantId = 1;
    
    // 1. Verificar si existe sede
    $stmt = $db->prepare("SELECT sede_id FROM sedes WHERE tenant_id = ? LIMIT 1");
    $stmt->execute([$tenantId]);
    $sede = $stmt->fetch();
    
    if (!$sede) {
        $stmt = $db->prepare("
            INSERT INTO sedes (tenant_id, codigo, nombre, direccion, ciudad, provincia, es_principal, estado)
            VALUES (?, 'SEDE001', 'Sede Principal', 'Av. Principal 123', 'Quito', 'Pichincha', 'S', 'A')
        ");
        $stmt->execute([$tenantId]);
        $sedeId = $db->lastInsertId();
        echo "<p>✅ Sede creada (ID: {$sedeId})</p>";
    } else {
        $sedeId = $sede['sede_id'];
        echo "<p>✅ Sede existente (ID: {$sedeId})</p>";
    }
    
    // 2. Verificar/crear tipo de instalación
    $stmt = $db->prepare("SELECT tipo_id FROM tipos_instalacion WHERE tenant_id = ? LIMIT 1");
    $stmt->execute([$tenantId]);
    $tipo = $stmt->fetch();
    
    if (!$tipo) {
        $tiposInstalacion = [
            ['FUTBOL', 'Cancha de Fútbol', 'Canchas para fútbol', 'fa-futbol', '#28a745'],
            ['BASQUET', 'Cancha de Básquet', 'Canchas para baloncesto', 'fa-basketball-ball', '#fd7e14'],
            ['TENIS', 'Cancha de Tenis', 'Canchas para tenis', 'fa-table-tennis', '#17a2b8']
        ];
        
        foreach ($tiposInstalacion as $t) {
            $stmt = $db->prepare("
                INSERT INTO tipos_instalacion (tenant_id, codigo, nombre, descripcion, icono, color, estado)
                VALUES (?, ?, ?, ?, ?, ?, 'A')
            ");
            $stmt->execute([$tenantId, $t[0], $t[1], $t[2], $t[3], $t[4]]);
            echo "<p>✅ Tipo instalación creado: {$t[1]}</p>";
        }
        
        // Obtener el primer tipo creado
        $stmt = $db->prepare("SELECT tipo_id FROM tipos_instalacion WHERE tenant_id = ? LIMIT 1");
        $stmt->execute([$tenantId]);
        $tipoId = $stmt->fetch()['tipo_id'];
    } else {
        $tipoId = $tipo['tipo_id'];
        echo "<p>✅ Tipo instalación existente (ID: {$tipoId})</p>";
    }
    
    // 3. Verificar si existen instalaciones
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM instalaciones WHERE tenant_id = ?");
    $stmt->execute([$tenantId]);
    $count = $stmt->fetch()['total'];
    
    if ($count == 0) {
        $instalaciones = [
            ['INS001', 'Complejo Norte', 'Complejo deportivo zona norte', 'Césped sintético', '100x60', 200],
            ['INS002', 'Complejo Sur', 'Complejo deportivo zona sur', 'Césped natural', '90x50', 150],
            ['INS003', 'Cancha Central', 'Cancha principal techada', 'Indoor', '40x20', 100]
        ];
        
        foreach ($instalaciones as $inst) {
            $stmt = $db->prepare("
                INSERT INTO instalaciones (
                    tenant_id, sede_id, tipo_instalacion_id, codigo, nombre, descripcion,
                    superficie, dimensiones, capacidad_personas, tiene_iluminacion,
                    tiene_graderias, tiene_vestuarios, tiene_duchas,
                    duracion_minima_minutos, duracion_maxima_minutos, tiempo_anticipacion_dias,
                    permite_reserva_recurrente, estado, usuario_registro
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'S', 'S', 'S', 'S', 60, 180, 7, 'S', 'ACTIVO', 1)
            ");
            $stmt->execute([$tenantId, $sedeId, $tipoId, $inst[0], $inst[1], $inst[2], $inst[3], $inst[4], $inst[5]]);
            echo "<p>✅ Instalación creada: {$inst[1]}</p>";
        }
    } else {
        echo "<p>✅ Ya existen {$count} instalaciones</p>";
    }
    
    // 4. Obtener instalaciones para crear canchas
    $stmt = $db->prepare("SELECT instalacion_id, nombre FROM instalaciones WHERE tenant_id = ?");
    $stmt->execute([$tenantId]);
    $instalaciones = $stmt->fetchAll();
    
    // 5. Verificar si existen canchas
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM canchas WHERE tenant_id = ?");
    $stmt->execute([$tenantId]);
    $count = $stmt->fetch()['total'];
    
    if ($count == 0) {
        $canchasPorInstalacion = [
            ['Cancha Fútbol 1', 'FUTBOL', 'Cancha de fútbol profesional', 22],
            ['Cancha Básquet', 'BASQUET', 'Cancha de baloncesto', 10],
            ['Cancha Tenis', 'TENIS', 'Cancha de tenis individual', 4]
        ];
        
        foreach ($instalaciones as $inst) {
            foreach ($canchasPorInstalacion as $cancha) {
                $stmt = $db->prepare("
                    INSERT INTO canchas (
                        tenant_id, instalacion_id, nombre, tipo, descripcion,
                        capacidad_maxima, ancho, largo, estado, usuario_creacion
                    ) VALUES (?, ?, ?, ?, ?, ?, 25, 50, 'ACTIVO', 1)
                ");
                $nombreCancha = $cancha[0] . ' - ' . $inst['nombre'];
                $stmt->execute([$tenantId, $inst['instalacion_id'], $nombreCancha, $cancha[1], $cancha[2], $cancha[3]]);
                echo "<p>✅ Cancha creada: {$nombreCancha}</p>";
            }
        }
    } else {
        echo "<p>✅ Ya existen {$count} canchas</p>";
    }
    
    // 6. Crear tarifas para las instalaciones
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM instalacion_tarifas WHERE instalacion_id IN (SELECT instalacion_id FROM instalaciones WHERE tenant_id = ?)");
    $stmt->execute([$tenantId]);
    $countTarifas = $stmt->fetch()['total'];
    
    if ($countTarifas == 0) {
        foreach ($instalaciones as $inst) {
            $stmt = $db->prepare("
                INSERT INTO instalacion_tarifas (
                    instalacion_id, nombre_tarifa, tipo_cliente, aplica_dia,
                    hora_inicio, hora_fin, precio_por_hora, fecha_inicio_vigencia, estado
                ) VALUES 
                (?, 'Tarifa Normal', 'PUBLICO', 'LUNES-VIERNES', '06:00', '18:00', 25.00, CURDATE(), 'A'),
                (?, 'Tarifa Nocturna', 'PUBLICO', 'LUNES-VIERNES', '18:00', '22:00', 35.00, CURDATE(), 'A'),
                (?, 'Tarifa Fin Semana', 'PUBLICO', 'SABADO-DOMINGO', '06:00', '22:00', 40.00, CURDATE(), 'A')
            ");
            $stmt->execute([$inst['instalacion_id'], $inst['instalacion_id'], $inst['instalacion_id']]);
            echo "<p>✅ Tarifas creadas para: {$inst['nombre']}</p>";
        }
    } else {
        echo "<p>✅ Ya existen tarifas</p>";
    }
    
    echo "<hr><h3>Resumen Final</h3>";
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM tipos_instalacion WHERE tenant_id = ?");
    $stmt->execute([$tenantId]);
    echo "<p>Tipos de instalación: <strong>" . $stmt->fetch()['total'] . "</strong></p>";
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM instalaciones WHERE tenant_id = ?");
    $stmt->execute([$tenantId]);
    echo "<p>Total instalaciones: <strong>" . $stmt->fetch()['total'] . "</strong></p>";
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM canchas WHERE tenant_id = ?");
    $stmt->execute([$tenantId]);
    echo "<p>Total canchas: <strong>" . $stmt->fetch()['total'] . "</strong></p>";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM instalacion_tarifas");
    echo "<p>Total tarifas: <strong>" . $stmt->fetch()['total'] . "</strong></p>";
    
    echo "<hr><p><a href='index.php'>← Ir al sistema</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
