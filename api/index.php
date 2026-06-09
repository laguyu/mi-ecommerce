<?php

// Forzar al servidor de Vercel a interpretar la ruta desde la raíz pública
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Cargar el punto de entrada oficial de tu Laravel 13
require __DIR__ . '/../public/index.php';

