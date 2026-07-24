<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class PassportService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.passport.url');
        $this->apiKey = config('services.passport.apikey');
    }

    /**
     * Obtener todos los clientes
     */
    public function getCustomers(): array
    {
        $response = Http::timeout(30)->get(
            "{$this->baseUrl}/Customer",
            [
                'apikey' => $this->apiKey
            ]
        );

        if (!$response->successful()) {
            throw new Exception('No fue posible consultar Passport.');
        }

        return $this->parseCustomers($response->body());
    }

    /**
     * Buscar cliente por Customer ID
     */
    public function getCustomerById(string $customerId): ?array
    {
        $customers = $this->getCustomers();

        foreach ($customers as $customer) {
            if ($customer['customer_id'] == $customerId) {
                return $customer;
            }
        }

        return null;
    }

    /**
     * Buscar cliente por email
     */
    public function getCustomerByEmail(string $email): ?array
    {
        $customers = $this->getCustomers();

        foreach ($customers as $customer) {
            if (
                isset($customer['email']) &&
                strtolower($customer['email']) == strtolower($email)
            ) {
                return $customer;
            }
        }

        return null;
    }

    /**
     * Buscar cliente por teléfono
     */
    public function getCustomerByPhone(string $phone): ?array
    {
        $customers = $this->getCustomers();

        foreach ($customers as $customer) {
            if (($customer['phone'] ?? '') == $phone) {
                return $customer;
            }
        }

        return null;
    }

    /**
     * Convertir XML a Array
     */
    private function parseCustomers(string $xml): array
    {
        $xmlObject = simplexml_load_string($xml);

        if (!$xmlObject) {
            throw new Exception('XML inválido.');
        }

        $customers = [];

        foreach ($xmlObject->row as $row) {

            $attr = $row->attributes();

            $customers[] = [

                'customer_id' => (string)($attr['customerId'] ?? ''),

                'first_name' => (string)($attr['firstName'] ?? ''),

                'middle_name' => (string)($attr['middleName'] ?? ''),

                'last_name' => (string)($attr['lastName'] ?? ''),

                'birthday' => (string)($attr['birthday'] ?? ''),

                'country' => (string)($attr['country'] ?? ''),

                'phone' => (string)($attr['billToPhoneNumber'] ?? ''),

                'email' => (string)($attr['billToEmailAddress'] ?? ''),

                'price_level' => (string)($attr['priceLevel'] ?? ''),

            ];
        }

        return $customers;
    }
}