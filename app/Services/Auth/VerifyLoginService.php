<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class VerifyLoginService
{
    public function __construct(
        protected OtpService $otpService,
    ) {
    }

    /**
     * Verifica el OTP del inicio de sesión
     * y genera el token de acceso.
     */
    public function verify(array $data): array
    {
        $user = $this->findUser($data);

        if (! $user) {
            throw new ModelNotFoundException(
                'User not found.'
            );
        }

        if (! $this->otpService->verify($user, $data['otp'])) {
            throw ValidationException::withMessages([
                'otp' => [
                    'The verification code is invalid or has expired.',
                ],
            ]);
        }

        // Limpiar el RateLimiter
        RateLimiter::clear(
            'login:' . strtolower($user->email)
        );

        return [
            'token' => $user->createToken('mobile')->plainTextToken,
            'user'  => $user->fresh(),
        ];
    }

    /**
     * Busca un usuario por correo o teléfono.
     */
    private function findUser(array $data): ?User
    {
        $query = User::query();

        if (! empty($data['email'])) {
            $query->where('email', $data['email']);
        }

        if (! empty($data['phone'])) {
            $query->where('phone', $data['phone']);
        }

        return $query->first();
    }
}