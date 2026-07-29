<?php

namespace App\Http\Middleware;

use App\Jobs\SyncCatapultCustomersJob;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * "Cron pobre": en vez de depender de un cron externo (que no puede
 * llegar a un servidor en local/sin dominio público) o de crontab en
 * el servidor (que puede no existir), aprovechamos el tráfico normal
 * de la app para disparar el sync de clientes CATAPULT cada cierto
 * tiempo.
 *
 * Solo dispara el job si ya pasó el intervalo mínimo desde el último
 * intento (usando un lock en cache como throttle), y lo hace
 * DESPUÉS de responder al cliente (`afterResponse()`) para no
 * agregarle latencia a ninguna petición real.
 */
class MaybeSyncCatapultCustomers
{
    // Cada cuánto, como mínimo, se intenta un sync.
    protected const MIN_INTERVAL_MINUTES = 20;

    protected const LOCK_KEY = 'catapult_customers_sync_lock';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $wonLock = Cache::add(
            self::LOCK_KEY,
            true,
            now()->addMinutes(self::MIN_INTERVAL_MINUTES),
        );

        if ($wonLock) {
            SyncCatapultCustomersJob::dispatch()->afterResponse();
        }

        return $response;
    }
}
