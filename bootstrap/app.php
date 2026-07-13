<?php

// 1. CONTROL DE ALMACENAMIENTO VIRTUAL PARA VERCEL (Colocar al inicio absoluto)
if (isset($_ENV['VERCEL'])) {
    $storagePath = '/tmp/storage';
    $requiredPaths = [
        $storagePath . '/framework/views',
        $storagePath . '/framework/cache',
        $storagePath . '/framework/sessions',
        $storagePath . '/bootstrap/cache'
    ];
    foreach ($requiredPaths as $path) {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
    // Obligamos a Laravel a usar la carpeta /tmp en lugar de su storage interno
    $_ENV['APP_STORAGE'] = $storagePath;
}


use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/stripe',
            'webhooks/paypal',
        ]);

        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'permission' => EnsurePermission::class,
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
