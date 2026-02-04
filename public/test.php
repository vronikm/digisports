<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP funciona correctamente<br>";
echo "Versión PHP: " . phpversion() . "<br>";
echo "Directorio actual: " . __DIR__ . "<br>";

// Verifica si existe el archivo principal
if (file_exists('index.php')) {
    echo "index.php existe<br>";
} else {
    echo "ERROR: index.php NO existe<br>";
}

// Verifica permisos
if (is_readable('index.php')) {
    echo "index.php es legible<br>";
} else {
    echo "ERROR: index.php NO es legible<br>";
}
?>