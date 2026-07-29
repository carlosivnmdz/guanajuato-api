<?php

namespace App\Services\Passport;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use SimpleXMLElement;

class CustomerService
{
    public function __construct(
        protected PassportClient $client,
        protected EmailService $emailService,
        protected PhoneService $phoneService,
    ) {
    }

    /**
     * Crea un cliente completo en CATAPULT.
     */
    public function create(Model $customer): string
    {
        $customerId = $this->generateCustomerId();

        $payload = [
            [
                'action' => 'U',
                'customerId' => $customerId,
                'firstName' => $customer->first_name,
                'middleName' => $customer->middle_name,
                'lastName' => $customer->last_name,
                'birthDate' => optional($customer->birthday)?->format('Y-m-d'),
                'priceLevel' => 2,
                'pf1' => $customer->country,
            ]
        ];

        $this->client->post(
            '/batch/customerMaintenance',
            $payload
        );

        if (!empty($customer->email)) {
            $this->emailService->create(
                $customerId,
                $customer->email
            );
        }

        if (!empty($customer->phone)) {
            $this->phoneService->create(
                $customerId,
                $customer->phone
            );
        }

        return $customerId;
    }

    /**
     * Actualiza un cliente que ya existe en CATAPULT (nombre,
     * apellido, fecha de nacimiento y país). El modelo debe traer
     * ya cargado su `customer_id`.
     *
     * Se manda el mismo payload de "action: U" que usa create(),
     * pero sin generar un customerId nuevo ni volver a fijar
     * priceLevel (eso es solo de alta, no queremos pisarlo en
     * cada edición de perfil).
     */
    public function update(Model $customer): void
    {
        $payload = [
            [
                'action' => 'U',
                'customerId' => $customer->customer_id,
                'firstName' => $customer->first_name,
                'middleName' => $customer->middle_name,
                'lastName' => $customer->last_name,
                'birthDate' => optional($customer->birthday)?->format('Y-m-d'),
                'pf1' => $customer->country,
            ]
        ];

        $this->client->post(
            '/batch/customerMaintenance',
            $payload
        );
    }

    /**
     * Trae clientes de CATAPULT nuevos o modificados desde cierta
     * fecha (o todos si no se especifica). Se usa para el sync
     * hacia la BD local, para que los clientes dados de alta directo
     * en tienda (sin pasar por la app) también puedan usarla.
     *
     * @return SimpleXMLElement[]
     */
    public function pullChanges(?string $modifiedSince = null): array
    {
        $query = $modifiedSince
            ? ['modifiedSince' => $modifiedSince]
            : [];

        $xml = $this->client->getXml('/Customer', $query);

        if (!isset($xml->row)) {
            return [];
        }

        $rows = [];

        foreach ($xml->row as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Busca un cliente.
     */
    public function find(
        string $customerId
    ): ?SimpleXMLElement {

        $xml = $this->client->getXml(
            '/Customer',
            [
                'customerId' => $customerId,
            ]
        );

        if (!isset($xml->row)) {
            return null;
        }

        $row = $xml->row;

        if (
            count($row->attributes()) === 0 &&
            count($row->children()) === 0 &&
            trim((string) $row) === ''
        ) {
            return null;
        }

        return $row;
    }

    /**
     * Verifica existencia.
     */
    public function exists(
        string $customerId
    ): bool {
        return $this->find($customerId) !== null;
    }

    /**
     * Genera un Customer ID único.
     */
    protected function generateCustomerId(): string
    {
        $attempts = 0;

        do {

            if ($attempts >= 20) {
                throw new RuntimeException(
                    'Unable to generate Customer ID.'
                );
            }

            $customerId = (string) random_int(
                1000000000,
                9999999999
            );

            $attempts++;

        } while ($this->exists($customerId));

        return $customerId;
    }
}