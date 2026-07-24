<?php

namespace App\Services\Passport;

class EmailService
{
    public function __construct(
        protected PassportClient $client,
    ) {
    }

    /**
     * Registra un correo electrónico en CATAPULT.
     */
    public function create(
        string $customerId,
        string $email
    ): void {

        $payload = [
            [
                'action' => 'U',
                'category' => 'C',
                'entityId' => $customerId,
                'description' => 'Home',
                'address' => $email,
                'marketingOptIn' => true,
            ]
        ];

        $this->client->post(
            '/batch/emailAddresses',
            $payload
        );
    }
}