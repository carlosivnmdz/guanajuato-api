<?php

namespace App\DTO;

class PassportCustomer
{
    public function __construct(
        public string $customerId,
        public string $firstName,
        public string $middleName,
        public string $lastName,
        public ?string $birthday,
        public string $country,
        public string $phone,
        public string $email,
        public string $priceLevel = 'App',
    ) {}
}