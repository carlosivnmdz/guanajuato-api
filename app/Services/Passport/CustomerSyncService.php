<?php

namespace App\Services\Passport;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use SimpleXMLElement;

/**
 * Refleja hacia la tabla local `users` los clientes que existen en
 * CATAPULT pero que nunca pasaron por el registro de la app (p. ej.
 * clientes dados de alta directo en tienda). Sin esto, esos clientes
 * no pueden iniciar sesión porque el login solo busca en la BD
 * local.
 *
 * Se dispara solo, aprovechando el tráfico normal de la app (ver
 * MaybeSyncCatapultCustomers), sin necesidad de cron ni de que el
 * servidor sea accesible desde internet.
 */
class CustomerSyncService
{
    protected const LAST_SYNC_CACHE_KEY = 'catapult_customers_last_synced_at';

    public function __construct(
        protected CustomerService $customerService,
    ) {
    }

    /**
     * Corre el sync: trae lo nuevo/modificado desde el último corte
     * y hace upsert local por `customer_id`. Devuelve un resumen.
     *
     * @return array{created: int, updated: int, total: int}
     */
    public function sync(): array
    {
        $lastSyncedAt = Cache::get(self::LAST_SYNC_CACHE_KEY);

        $rows = $this->customerService->pullChanges($lastSyncedAt);

        $created = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $customerId = (string) $row['customerId'];

            if ($customerId === '') {
                continue;
            }

            $user = User::where('customer_id', $customerId)->first();

            // Nombre/apellido/nacimiento/país son seguros de
            // refrescar siempre: la app también los manda a
            // CATAPULT, así que son la misma fuente de verdad.
            $profile = $this->mapProfileFields($row);

            if ($user) {
                $user->fill($profile);

                if ($user->isDirty()) {
                    $user->save();
                    $updated++;
                }
            } else {
                // Correo/teléfono SOLO se usan para poblar un
                // registro nuevo. Nunca se tocan en un usuario que
                // ya existe localmente: `billToEmailAddress` en
                // CATAPULT es un campo distinto al que usa la app
                // para el login por OTP, y casi siempre viene vacío
                // — sobreescribirlo en cada sync borraba el correo
                // real de clientes que ya se habían registrado en
                // la app (bug ya corregido).
                $contact = $this->mapContactFields($row, $customerId);

                User::create(array_merge(
                    ['customer_id' => $customerId],
                    $profile,
                    $contact,
                ));

                $created++;
            }
        }

        Cache::forever(
            self::LAST_SYNC_CACHE_KEY,
            now()->format('Y-m-d H:i:s'),
        );

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => count($rows),
        ];
    }

    /**
     * Busca UN cliente puntual en CATAPULT por correo o teléfono y lo
     * sincroniza a la BD local si lo encuentra. Sirve de fallback
     * para el login cuando el usuario no existe todavía
     * localmente — cubre al cliente recién dado de alta en tienda
     * que no quiere esperar hasta el próximo sync periódico (cada
     * 20 min) para poder entrar a la app.
     *
     * A diferencia de sync(), aquí se compara contra el catálogo
     * completo de CATAPULT (sin `modifiedSince`), porque el cliente
     * pudo haberse dado de alta hace tiempo y apenas hoy intentar
     * usar la app por primera vez.
     */
    public function syncOne(?string $email, ?string $phone): ?User
    {
        if (empty($email) && empty($phone)) {
            return null;
        }

        $rows = $this->customerService->pullChanges();

        foreach ($rows as $row) {
            $rowEmail = (string) $row['billToEmailAddress'];
            $rowPhone = (string) $row['billToPhoneNumber'];

            $matchesEmail = $email
                && $rowEmail !== ''
                && strtolower($rowEmail) === strtolower($email);

            $matchesPhone = $phone
                && $rowPhone !== ''
                && $rowPhone === $phone;

            if (! $matchesEmail && ! $matchesPhone) {
                continue;
            }

            $customerId = (string) $row['customerId'];

            if ($customerId === '') {
                return null;
            }

            $user = User::where('customer_id', $customerId)->first();
            $profile = $this->mapProfileFields($row);

            if ($user) {
                $user->fill($profile);

                if ($user->isDirty()) {
                    $user->save();
                }

                return $user;
            }

            $contact = $this->mapContactFields($row, $customerId);

            return User::create(array_merge(
                ['customer_id' => $customerId],
                $profile,
                $contact,
            ));
        }

        return null;
    }

    /**
     * Campos de perfil, seguros de refrescar en cualquier usuario
     * (nuevo o existente). Los campos vacíos se guardan como null en
     * vez de cadena vacía (CATAPULT los regresa como "" cuando no
     * están capturados en Web Office).
     */
    protected function mapProfileFields(SimpleXMLElement $row): array
    {
        $firstName = (string) $row['firstName'];
        $lastName = (string) $row['lastName'];
        $birthDate = (string) $row['birthDate'];
        $country = (string) $row['powerField1'];

        return [
            'first_name' => $firstName !== '' ? $firstName : 'Cliente',
            'last_name' => $lastName !== '' ? $lastName : null,
            'birthday' => $birthDate !== '' ? $birthDate : null,
            'country' => $country !== '' ? $country : null,
        ];
    }

    /**
     * Correo/teléfono, solo para poblar un usuario NUEVO (nunca se
     * usan para actualizar uno que ya existe localmente, ver nota
     * en sync()).
     */
    protected function mapContactFields(
        SimpleXMLElement $row,
        string $customerId
    ): array {
        $email = (string) $row['billToEmailAddress'];
        $phone = (string) $row['billToPhoneNumber'];

        return [
            'email' => $this->uniqueOrNull($email, 'email', $customerId),
            'phone' => $this->uniqueOrNull($phone, 'phone', $customerId),
        ];
    }

    /**
     * Evita romper el índice único de email/phone si, por alguna
     * razón, ya hay otro cliente local con ese mismo dato. Mejor
     * dejarlo en null en ese caso raro que tronar el sync completo.
     */
    protected function uniqueOrNull(
        string $value,
        string $column,
        string $customerId
    ): ?string {
        if ($value === '') {
            return null;
        }

        $conflict = User::where($column, $value)
            ->where('customer_id', '!=', $customerId)
            ->exists();

        return $conflict ? null : $value;
    }
}
