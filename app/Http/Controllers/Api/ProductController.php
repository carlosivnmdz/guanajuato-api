<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CatapultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly CatapultService $catapult
    ) {
    }

    /**
     * Obtener productos.
     *
     * GET /api/products
     * GET /api/products?page=1
     * GET /api/products?per_page=20
     * GET /api/products?department=1
     */
    public function index(
        Request $request
    ): JsonResponse {
        try {
            $page = max(
                1,
                $request->integer('page', 1)
            );

            $perPage = min(
                100,
                max(
                    1,
                    $request->integer('per_page', 20)
                )
            );

            $result = $this->catapult
                ->getPaginatedItems(
                    $page,
                    $perPage,
                    $this->getFilters($request)
                );

            return response()->json([
                'success' => true,
                ...$result,
            ]);

        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Buscar productos.
     *
     * GET /api/products/search?q=leche
     * GET /api/products/search?q=leche&department=1
     */
    public function search(
        Request $request
    ): JsonResponse {
        $query = trim(
            (string) $request->input('q', '')
        );

        if ($query === '') {
            return response()->json([
                'success' => false,
                'message' => 'Debe enviar el parámetro q.',
            ], 422);
        }

        try {

            $limit = min(
                100,
                max(
                    1,
                    $request->integer('limit', 50)
                )
            );

            $products = $this->catapult
                ->searchItems(
                    $query,
                    $limit,
                    $this->getFilters($request)
                );

            return response()->json([
                'success' => true,
                'count' => count($products),
                'data' => $products,
            ]);

        } catch (\Throwable $e) {

            return $this->errorResponse($e);

        }
    }

    /**
     * Obtener detalle del producto.
     *
     * GET /api/products/{itemId}
     */
    public function show(
        string $itemId
    ): JsonResponse {

        try {

            $product = $this->catapult
                ->getItemDetail($itemId);

            return response()->json([
                'success' => true,
                'data' => $product,
            ]);

        } catch (\Throwable $e) {

            return $this->errorResponse($e);

        }
    }

    /**
     * Obtener departamentos.
     *
     * GET /api/products/departments
     */
    public function departments(): JsonResponse
    {
        try {

            $departments = $this->catapult
                ->getDepartments();

            return response()->json([
                'success' => true,
                'count' => count($departments),
                'data' => $departments,
            ]);

        } catch (\Throwable $e) {

            return $this->errorResponse($e);

        }
    }

    /**
     * Limpiar la caché de CATAPULT.
     *
     * POST /api/products/cache/clear
     */
    public function clearCache(): JsonResponse
    {
        try {

            $this->catapult
                ->clearCatalogCache();

            return response()->json([
                'success' => true,
                'message' => 'La caché fue limpiada correctamente.',
            ]);

        } catch (\Throwable $e) {

            return $this->errorResponse($e);

        }
    }

    /**
     * Construir filtros.
     */
    private function getFilters(
        Request $request
    ): array {

        return [

            'department' => trim(
                (string) $request->input(
                    'department',
                    ''
                )
            ),

        ];
    }

    /**
     * Respuesta estándar de error.
     */
    private function errorResponse(
        \Throwable $e
    ): JsonResponse {

        report($e);

        return response()->json([

            'success' => false,

            'message' => $e->getMessage(),

        ], 500);
    }
}