<?php

// Crear directorios temporales si no existen antes de inicializar Laravel
$storagePath = '/tmp/storage';
$paths = [
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/sessions',
    $storagePath . '/bootstrap/cache'
];

foreach ($paths as $path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

// Normalizar la ruta del script para el servidor integrado
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Llamar al núcleo de Laravel
require __DIR__ . '/../public/index.php';

