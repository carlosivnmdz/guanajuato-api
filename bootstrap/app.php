<?php

use App\Http\Middleware\MaybeSyncCatapultCustomers;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // "Cron pobre": aprovecha el tráfico normal de la API para
        // disparar el sync de clientes CATAPULT cada cierto tiempo,
        // sin depender de cron externo ni de que el servidor sea
        // accesible desde internet (útil en local).
        $middleware->api(append: [
            MaybeSyncCatapultCustomers::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
