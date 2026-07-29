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

        // Haber verificado el OTP es prueba de que el usuario sí
        // tiene acceso a ese correo/teléfono. Antes esto solo se
        // marcaba en el registro, así que una cuenta que nunca lo
        // tuvo marcado (por sync desde CATAPULT, o por ser anterior
        // a este campo) se quedaba "no verificada" para siempre
        // aunque el usuario sí completara el OTP al iniciar sesión.
        $justVerified = false;

        if (! empty($data['email']) && ! $user->email_verified_at) {
            $user->email_verified_at = now();
            $justVerified = true;
        }

        if (! empty($data['phone']) && ! $user->phone_verified_at) {
            $user->phone_verified_at = now();
            $justVerified = true;
        }

        if ($justVerified) {
            $user->save();
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