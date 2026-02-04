<?php
// Script para limpiar la caché de DigiSports
$cacheDir = __DIR__ . '/../storage/cache/';
$deleted = 0;
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $deleted++;
        }
    }
    echo "<h2>✔ Caché limpiada: $deleted archivos eliminados.</h2>";
} else {
    echo "<h2>⚠️ No se encontró la carpeta de caché.</h2>";
}
?>
