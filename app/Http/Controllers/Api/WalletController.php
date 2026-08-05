<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {
    }

    /**
     * URL del pase de Apple Wallet del usuario autenticado.
     */
    public function applePass(Request $request): JsonResponse
    {
        return $this->passResponse(
            fn () => $this->walletService->applePassUrl($request->user())
        );
    }

    /**
     * URL del pase de Google Wallet del usuario autenticado.
     */
    public function googlePass(Request $request): JsonResponse
    {
        return $this->passResponse(
            fn () => $this->walletService->googlePassUrl($request->user())
        );
    }

    private function passResponse(callable $resolveUrl): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $resolveUrl(),
                ],
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 503);
        }
    }
}
