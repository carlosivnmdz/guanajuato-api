<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyLoginOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    /**
     * Inicia el proceso de registro.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent successfully.',
            'data' => [
                'customer_id' => $user->customer_id,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ], 201);
    }

    /**
     * Verifica el OTP del registro.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->authService->verifyOtp(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Registration completed successfully.',
            'data' => $result,
        ]);
    }

    /**
     * Inicia el proceso de inicio de sesión.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->login(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent successfully.',
            'data' => [
                'customer_id' => $user->customer_id,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Verifica el OTP del inicio de sesión.
     */
    public function verifyLoginOtp(
        VerifyLoginOtpRequest $request
    ): JsonResponse {
        $result = $this->authService->verifyLoginOtp(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => $result,
        ]);
    }

    /**
     * Usuario autenticado.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->authService->me(
                $request->user()
            ),
        ]);
    }

    /**
     * Cerrar sesión.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout(
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}