<?php

namespace App\Services\Passport;

class PhoneService
{
    public function __construct(
        protected PassportClient $client,
    ) {
    }

    /**
     * Registra un teléfono en CATAPULT.
     */
    public function create(
        string $customerId,
        string $phone
    ): void {

        $payload = [
            [
                'action' => 'U',
                'category' => 'C',
                'entityId' => $customerId,
                'description' => 'Mobile',
                'phoneNumber' => $phone,
            ]
        ];

        $this->client->post(
            '/batch/phoneNumbers',
            $payload
        );
    }
}