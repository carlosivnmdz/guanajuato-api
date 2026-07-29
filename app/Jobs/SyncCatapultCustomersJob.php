<?php

namespace App\Jobs;

use App\Services\Passport\CustomerSyncService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Corre el sync de clientes CATAPULT -> Laravel. Se despacha con
 * `->afterResponse()` desde MaybeSyncCatapultCustomers, así que
 * corre justo después de que el usuario ya recibió su respuesta —
 * no le agrega latencia a ninguna petición real.
 */
class SyncCatapultCustomersJob
{
    use Dispatchable;

    public function handle(CustomerSyncService $customerSyncService): void
    {
        try {
            $customerSyncService->sync();
        } catch (Throwable $e) {
            // Si CATAPULT no responde o hay un error, no queremos
            // tumbar nada visible para el usuario (ya se le
            // respondió). Solo lo dejamos en el log para revisarlo.
            Log::warning(
                'No se pudo sincronizar clientes de CATAPULT: '
                . $e->getMessage()
            );
        }
    }
}
