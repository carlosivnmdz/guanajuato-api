<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected RegistrationService $registrationService,
        protected VerifyOtpService $verifyOtpService,
        protected LoginService $loginService,
        protected VerifyLoginService $verifyLoginService,
    ) {
    }

    /**
     * Inicia el proceso de registro.
     */
    public function register(array $data)
    {
        $key = 'register:' . strtolower($data['email']);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => [
                    'Too many verification requests. Please try again in '
                    . ceil(RateLimiter::availableIn($key) / 60)
                    . ' minute(s).',
                ],
            ]);
        }

        // Mantener el intento durante 15 minutos
        RateLimiter::hit($key, 900);

        return $this->registrationService->register($data);
    }

    /**
     * Verifica el OTP del registro y crea el usuario.
     */
    public function verifyOtp(array $data): array
    {
        return $this->verifyOtpService->verify($data);
    }

    /**
     * Inicia el proceso de inicio de sesión.
     */
    public function login(array $data): User
    {
        return $this->loginService->login($data);
    }

    /**
     * Verifica el OTP del inicio de sesión.
     */
    public function verifyLoginOtp(array $data): array
    {
        return $this->verifyLoginService->verify($data);
    }

    /**
     * Usuario autenticado.
     */
    public function me(User $user): User
    {
        return $user;
    }

    /**
     * Cerrar sesión.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}