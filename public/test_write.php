<?php
$file = __DIR__ . DIRECTORY_SEPARATOR . 'test.txt';
$result = @file_put_contents($file, "prueba de escritura\n");
if ($result === false) {
    echo "No se pudo escribir en $file";
} else {
    echo "Archivo creado correctamente: $file";
}
?>