<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CatapultService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            (string) config('services.passport.url'),
            '/'
        );

        $this->apiKey = (string) config(
            'services.passport.apikey'
        );

        if ($this->baseUrl === '') {
            throw new \RuntimeException(
                'No está configurada la URL de CATAPULT.'
            );
        }

        if ($this->apiKey === '') {
            throw new \RuntimeException(
                'No está configurada la API Key de CATAPULT.'
            );
        }
    }

    /**
     * Obtener todos los productos del catálogo.
     *
     * Endpoint CATAPULT:
     * GET /allItems
     */
    public function getAllItems(): array
    {
        return Cache::remember(
            'catapult_all_items',
            now()->addMinutes(10),
            function (): array {
                $response = Http::timeout(60)
                    ->retry(2, 500)
                    ->accept('application/xml')
                    ->get(
                        "{$this->baseUrl}/allItems",
                        [
                            'apikey' => $this->apiKey,
                        ]
                    );

                if (!$response->successful()) {
                    throw new \RuntimeException(
                        'Error al consultar el catálogo de CATAPULT. '
                            . 'Código HTTP: '
                            . $response->status()
                    );
                }

                return $this->parseAllItems(
                    $response->body()
                );
            }
        );
    }

    /**
     * Obtener productos paginados.
     *
     * Ejemplos:
     *
     * GET /api/products
     *
     * GET /api/products?page=1&per_page=20
     *
     * GET /api/products?department=1&page=1&per_page=20
     */
    public function getPaginatedItems(
        int $page = 1,
        int $perPage = 20,
        array $filters = []
    ): array {
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));

        $products = collect(
            $this->getAllItems()
        );

        $products = $this->applyFilters(
            $products,
            $filters
        );

        $total = $products->count();

        $lastPage = $total > 0
            ? (int) ceil($total / $perPage)
            : 0;

        return [
            'data' => $products
                ->forPage($page, $perPage)
                ->values()
                ->all(),

            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'has_more_pages' => $lastPage > 0
                    && $page < $lastPage,
            ],
        ];
    }

    /**
     * Buscar productos dentro del catálogo.
     *
     * Ejemplos:
     *
     * GET /api/products/search?q=leche
     *
     * GET /api/products/search?q=leche&department=5
     */
    public function searchItems(
        string $query,
        int $limit = 50,
        array $filters = []
    ): array {
        $query = $this->normalizeText($query);
        $limit = min(100, max(1, $limit));

        if ($query === '') {
            return [];
        }

        $products = collect(
            $this->getAllItems()
        );

        $products = $this->applyFilters(
            $products,
            $filters
        );

        return $products
            ->filter(
                function (array $item) use ($query): bool {
                    return str_contains(
                        (string) ($item['searchText'] ?? ''),
                        $query
                    );
                }
            )
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * Obtener el detalle de un producto.
     *
     * Endpoint CATAPULT:
     * GET /itemDetail
     */
    /**
     * Obtener el detalle de un producto.
     *
     * Endpoint CATAPULT:
     * GET /itemDetail
     */
    public function getItemDetail(
        string $search
    ): array {
        $search = trim($search);

        if ($search === '') {
            throw new \InvalidArgumentException(
                'Debe enviar un identificador de producto.'
            );
        }

        $response = Http::timeout(30)
            ->retry(2, 500)
            ->accept('application/xml')
            ->get(
                "{$this->baseUrl}/itemDetail",
                [
                    'apikey' => $this->apiKey,
                    'itemSearch' => $search,
                ]
            );

        if (!$response->successful()) {
            throw new \RuntimeException(
                'Error al consultar el producto en CATAPULT. Código HTTP: '
                    . $response->status()
            );
        }

        $xml = $this->loadXml(
            $response->body(),
            'No fue posible procesar el detalle del producto.'
        );

        if (!isset($xml->row)) {
            throw new \RuntimeException(
                'CATAPULT no devolvió información del producto.'
            );
        }

        $attributes = $xml->row->attributes();

        return [
            'itemId' => $this->getAttribute($attributes, 'itemId'),
            'storeName' => $this->getAttribute($attributes, 'storeName'),
            'zoneName' => $this->getAttribute($attributes, 'zoneName'),
            'receiptAlias' => $this->getAttribute($attributes, 'receiptAlias'),
            'itemName' => $this->getAttribute($attributes, 'itemName'),
            'department' => $this->getAttribute($attributes, 'department'),
            'subDepartment' => $this->getAttribute($attributes, 'subDepartment'),
            'size' => $this->getAttribute($attributes, 'size'),
            'brand' => $this->getAttribute($attributes, 'brand'),
            'loyaltyMultiplier' => (float) $this->getAttribute($attributes, 'loyaltyMultiplier'),
            'pricePL1' => (float) $this->getAttribute($attributes, 'pricePL1'),
            'pricePL2' => (float) $this->getAttribute($attributes, 'pricePL2'),
            'pricePL3' => (float) $this->getAttribute($attributes, 'pricePL3'),
            'pricePL4' => (float) $this->getAttribute($attributes, 'pricePL4'),
            'lastCost' => (float) $this->getAttribute($attributes, 'lastCost'),
            'averageCost' => (float) $this->getAttribute($attributes, 'averageCost'),
            'onHand' => (float) $this->getAttribute($attributes, 'onHand'),
            'onOrder' => (float) $this->getAttribute($attributes, 'onOrder'),
            'type' => $this->getAttribute($attributes, 'type'),
            'discontinued' => $this->getAttribute($attributes, 'discontinued'),
        ];
    }

    /**
     * Obtener los departamentos oficiales.
     *
     * Endpoint CATAPULT:
     * GET /departmentDetail
     *
     * El XML devuelve atributos como:
     *
     * departmentNumber
     * departmentName
     * departmentOmitFromSales
     */
    public function getDepartments(): array
    {
        return Cache::remember(
            'catapult_departments',
            now()->addMinutes(30),
            function (): array {
                $response = Http::timeout(30)
                    ->retry(2, 500)
                    ->accept('application/xml')
                    ->get(
                        "{$this->baseUrl}/departmentDetail",
                        [
                            'apikey' => $this->apiKey,
                        ]
                    );

                if (!$response->successful()) {
                    throw new \RuntimeException(
                        'Error al consultar los departamentos '
                            . 'de CATAPULT. Código HTTP: '
                            . $response->status()
                    );
                }

                return $this->parseDepartments(
                    $response->body()
                );
            }
        );
    }

    /**
     * Limpiar las cachés utilizadas por CATAPULT.
     */
    public function clearCatalogCache(): bool
    {
        $productsDeleted = Cache::forget(
            'catapult_all_items'
        );

        $departmentsDeleted = Cache::forget(
            'catapult_departments'
        );

        return $productsDeleted || $departmentsDeleted;
    }

    /**
     * Aplicar los filtros que actualmente usa Flutter.
     *
     * Actualmente únicamente se filtra por departamento.
     */
    private function applyFilters(
        Collection $products,
        array $filters
    ): Collection {
        $department = trim(
            (string) ($filters['department'] ?? '')
        );

        if ($department !== '') {
            $products = $products->filter(
                function (array $item) use ($department): bool {
                    return trim(
                        (string) ($item['departmentNumber'] ?? '')
                    ) === $department;
                }
            );
        }

        return $products;
    }

    /**
     * Convertir el XML de /allItems en un arreglo.
     */
    private function parseAllItems(
        string $xml
    ): array {
        $xmlObject = $this->loadXml(
            $xml,
            'No fue posible procesar el catálogo de CATAPULT.'
        );

        $items = [];

        foreach ($xmlObject->row as $row) {
            $attributes = $row->attributes();

            if ($attributes === null) {
                continue;
            }

            $itemId = $this->getAttribute(
                $attributes,
                'itemId'
            );

            $name = $this->getAttribute(
                $attributes,
                'name'
            );

            $receiptAlias = $this->getAttribute(
                $attributes,
                'receiptAlias'
            );

            $unitOfMeasure = $this->getAttribute(
                $attributes,
                'unitOfMeasure'
            );

            $departmentNumber = $this->getAttribute(
                $attributes,
                'departmentNumber'
            );

            $subDepartmentNumber = $this->getAttribute(
                $attributes,
                'subDepartmentNumber'
            );

            $categoryNumber = $this->getAttribute(
                $attributes,
                'categoryNumber'
            );

            $subCategoryNumber = $this->getAttribute(
                $attributes,
                'subCategoryNumber'
            );

            $brand = $this->getAttribute(
                $attributes,
                'brand'
            );

            $powerField1 = $this->getAttribute(
                $attributes,
                'powerField1'
            );

            $variety = $this->getAttribute(
                $attributes,
                'variety'
            );

            $familyLine = $this->getAttribute(
                $attributes,
                'familyLine'
            );

            if (
                $itemId === ''
                && $name === ''
                && $receiptAlias === ''
            ) {
                continue;
            }

            $searchText = $this->normalizeText(
                implode(
                    ' ',
                    [
                        $itemId,
                        $name,
                        $receiptAlias,
                        $brand,
                        $powerField1,
                        $variety,
                        $familyLine,
                    ]
                )
            );

            $items[] = [
                'itemId' => $itemId,
                'name' => $name,
                'receiptAlias' => $receiptAlias,
                'unitOfMeasure' => $unitOfMeasure,
                'departmentNumber' => $departmentNumber,
                'subDepartmentNumber' => $subDepartmentNumber,
                'categoryNumber' => $categoryNumber,
                'subCategoryNumber' => $subCategoryNumber,
                'brand' => $brand,
                'powerField1' => $powerField1,
                'variety' => $variety,
                'familyLine' => $familyLine,
                'searchText' => $searchText,
            ];
        }

        return $items;
    }

    /**
     * Convertir el XML de /departmentDetail en un arreglo.
     */
    private function parseDepartments(
        string $xml
    ): array {
        $xmlObject = $this->loadXml(
            $xml,
            'No fue posible procesar los departamentos '
                . 'de CATAPULT.'
        );

        $departments = [];

        foreach ($xmlObject->row as $row) {
            $attributes = $row->attributes();

            if ($attributes === null) {
                continue;
            }

            $id = $this->getAttribute(
                $attributes,
                'departmentNumber'
            );

            $name = $this->getAttribute(
                $attributes,
                'departmentName'
            );

            $omitFromSales = $this->getAttribute(
                $attributes,
                'departmentOmitFromSales'
            );

            if ($id === '' || $name === '') {
                continue;
            }

            /*
             * Excluir departamentos internos que CATAPULT
             * indica que deben omitirse de ventas.
             *
             * En la captura, MODIFIERS tiene:
             * departmentOmitFromSales="1"
             */
            if ($omitFromSales === '1') {
                continue;
            }

            $departments[] = [
                'id' => $id,
                'name' => $name,
            ];
        }

        return collect($departments)
            ->unique(
                fn(array $department): string =>
                (string) $department['id']
            )
            ->sortBy(
                fn(array $department): int =>
                (int) $department['id']
            )
            ->values()
            ->all();
    }

    /**
     * Cargar y validar un XML de CATAPULT.
     */
    private function loadXml(
        string $xml,
        string $defaultError
    ): \SimpleXMLElement {
        $xml = trim($xml);

        if ($xml === '') {
            throw new \RuntimeException(
                'CATAPULT devolvió una respuesta XML vacía.'
            );
        }

        $xml = $this->prepareXml($xml);

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $xmlObject = simplexml_load_string(
            $xml,
            \SimpleXMLElement::class,
            LIBXML_NOCDATA
        );

        if ($xmlObject === false) {
            $errors = libxml_get_errors();

            $message = collect($errors)
                ->map(
                    fn($error): string =>
                    trim((string) $error->message)
                )
                ->filter()
                ->implode(' | ');

            libxml_clear_errors();

            throw new \RuntimeException(
                $message !== ''
                    ? $message
                    : $defaultError
            );
        }

        libxml_clear_errors();

        return $xmlObject;
    }

    /**
     * CATAPULT puede devolver XML 1.1.
     * SimpleXML suele trabajar mejor cambiándolo a XML 1.0.
     */
    private function prepareXml(
        string $xml
    ): string {
        $prepared = preg_replace(
            '/<\?xml\s+version=["\']1\.1["\']/i',
            '<?xml version="1.0"',
            $xml
        );

        return is_string($prepared)
            ? $prepared
            : $xml;
    }

    /**
     * Obtener de manera segura un atributo XML.
     */
    private function getAttribute(
        \SimpleXMLElement $attributes,
        string $name
    ): string {
        return trim(
            (string) ($attributes->{$name} ?? '')
        );
    }

    /**
     * Normalizar texto para búsquedas.
     */
    private function normalizeText(
        string $text
    ): string {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        $text = mb_strtolower(
            $text,
            'UTF-8'
        );

        $converted = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $text
        );

        if ($converted !== false) {
            $text = $converted;
        }

        $text = preg_replace(
            '/[^a-z0-9]+/i',
            ' ',
            $text
        );

        if (!is_string($text)) {
            return '';
        }

        $text = preg_replace(
            '/\s+/',
            ' ',
            $text
        );

        return is_string($text)
            ? trim($text)
            : '';
    }
}
