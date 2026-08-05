<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;

/**
 * Genera los enlaces de "agregar a wallet" para la tarjeta digital.
 *
 * Bloqueado hasta tener las credenciales de cada plataforma (ver
 * config/wallet.php). Cuando existan, cada método debe generar el
 * pase firmado (.pkpass para Apple, JWT para Google Wallet) y
 * devolver la URL lista para abrir desde la app.
 */
class WalletService
{
    public function applePassUrl(User $user): string
    {
        if (!config('wallet.apple.enabled')) {
            throw new RuntimeException(
                'Apple Wallet no está configurado todavía.'
            );
        }

        // TODO: construir el .pkpass (pass.json + manifest + firma
        // PKCS#7 con el certificado Pass Type ID y el certificado
        // WWDR de Apple) a partir de $user->customer_id, subirlo y
        // devolver su URL pública.
        throw new RuntimeException(
            'Generación de pases de Apple Wallet pendiente de implementar.'
        );
    }

    public function googlePassUrl(User $user): string
    {
        if (!config('wallet.google.enabled')) {
            throw new RuntimeException(
                'Google Wallet no está configurado todavía.'
            );
        }

        // TODO: construir el objeto de loyalty pass, firmarlo como JWT
        // con el service account y devolver la URL
        // https://pay.google.com/gp/v/save/<jwt>.
        throw new RuntimeException(
            'Generación de pases de Google Wallet pendiente de implementar.'
        );
    }
}
