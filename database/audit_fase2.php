<?php
/**
 * Auditoría completa de tablas Arena para Fase 2
 */
$pdo = new PDO('mysql:host=localhost;dbname=digisports_core', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// 1. Estructura de instalaciones_reserva_pagos
echo "=== instalaciones_reserva_pagos ===\n";
try {
    $cols = $pdo->query("SHOW COLUMNS FROM instalaciones_reserva_pagos");
    while ($c = $cols->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$c['Field']} ({$c['Type']}) {$c['Key']} | Null:{$c['Null']} Default:{$c['Default']}\n";
    }
} catch (Exception $e) {
    echo "  ❌ No existe: {$e->getMessage()}\n";
}

// 2. Estructura de instalaciones_reservas
echo "\n=== instalaciones_reservas ===\n";
try {
    $cols = $pdo->query("SHOW COLUMNS FROM instalaciones_reservas");
    while ($c = $cols->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$c['Field']} ({$c['Type']}) {$c['Key']} | Null:{$c['Null']} Default:{$c['Default']}\n";
    }
} catch (Exception $e) {
    echo "  ❌ No existe\n";
}

// 3. Datos de ejemplo en reservas
echo "\n=== Registros en reservas: ";
$count = $pdo->query("SELECT COUNT(*) FROM instalaciones_reservas")->fetchColumn();
echo "$count ===\n";
if ($count > 0) {
    $sample = $pdo->query("SELECT * FROM instalaciones_reservas LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sample as $s) {
        echo "  " . json_encode($s, JSON_UNESCAPED_UNICODE) . "\n";
    }
}

// 4. Registros en pagos
echo "\n=== Registros en reserva_pagos: ";
try {
    $count = $pdo->query("SELECT COUNT(*) FROM instalaciones_reserva_pagos")->fetchColumn();
    echo "$count ===\n";
} catch (Exception $e) {
    echo "N/A ===\n";
}

// 5. Estructura de instalaciones_abonos
echo "\n=== instalaciones_abonos ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM instalaciones_abonos");
while ($c = $cols->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$c['Field']} ({$c['Type']}) {$c['Key']} | Null:{$c['Null']} Default:{$c['Default']}\n";
}

// 6. Tablas sin usar
echo "\n=== Otras tablas Arena ===\n";
$tables = [
    'instalaciones_disponibilidad_canchas',
    'instalaciones_eventos_canchas', 
    'instalaciones_instalacion_bloqueos',
    'instalaciones_instalacion_horarios',
    'instalaciones_instalacion_tarifas',
    'instalaciones_sedes',
    'instalaciones_tipos_instalacion'
];
foreach ($tables as $t) {
    try {
        $cnt = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        $cols = $pdo->query("SHOW COLUMNS FROM $t");
        $colNames = [];
        while ($c = $cols->fetch(PDO::FETCH_ASSOC)) {
            $colNames[] = $c['Field'];
        }
        echo "  $t ($cnt rows): " . implode(', ', $colNames) . "\n";
    } catch (Exception $e) {
        echo "  $t: ❌ No existe\n";
    }
}

// 7. Ver ReservaController confirmar/cancelar
echo "\n=== Acciones de ReservaController ===\n";
$file = file_get_contents(dirname(__DIR__) . '/app/controllers/reservas/ReservaController.php');
preg_match_all('/public function (\w+)/', $file, $matches);
echo "  Métodos: " . implode(', ', $matches[1]) . "\n";

echo "\n🎉 Auditoría completada.\n";
