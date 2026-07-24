<?php

namespace App\Services\Passport;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class PassportClient
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.passport.url'), '/');
        $this->apiKey = config('services.passport.apikey');
    }

    protected function batch(): int
    {
        return (int) now()->format('YmdHis') . random_int(10, 99);
    }

    public function post(string $endpoint, array $payload): Response
    {
        return Http::withHeaders([
            'X-ECRS-APIKEY' => $this->apiKey,
            'Accept' => 'application/xml',
        ])
        ->post(
            "{$this->baseUrl}{$endpoint}?batch={$this->batch()}",
            $payload
        )
        ->throw();
    }

    public function get(string $endpoint, array $query = []): Response
    {
        return Http::withHeaders([
            'X-ECRS-APIKEY' => $this->apiKey,
            'Accept' => 'application/xml',
        ])
        ->get(
            "{$this->baseUrl}{$endpoint}",
            $query
        )
        ->throw();
    }

    /**
     * Obtiene y parsea XML de CATAPULT.
     */
    public function getXml(
        string $endpoint,
        array $query = []
    ): SimpleXMLElement {

        $xml = $this->get($endpoint, $query)->body();

        // CATAPULT responde XML 1.1 y SimpleXML solo acepta 1.0
        $xml = str_replace(
            'version="1.1"',
            'version="1.0"',
            $xml
        );

        $xml = str_replace(
            "version='1.1'",
            "version='1.0'",
            $xml
        );

        libxml_use_internal_errors(true);

        return new SimpleXMLElement($xml);
    }
}