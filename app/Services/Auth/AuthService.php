<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Passport\CustomerService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected RegistrationService $registrationService,
        protected VerifyOtpService $verifyOtpService,
        protected LoginService $loginService,
        protected VerifyLoginService $verifyLoginService,
        protected CustomerService $customerService,
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
     * Actualiza el nombre, apellido, fecha de nacimiento y país del
     * usuario autenticado.
     *
     * Primero sincroniza el cambio con CATAPULT (el POS real, el
     * que de verdad usa el negocio) y solo si eso funciona lo
     * guarda en la base local. Si se guardara primero localmente
     * y CATAPULT fallara, quedarían desincronizados sin que nadie
     * se diera cuenta.
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'birthday' => $data['birthday'] ?? $user->birthday,
            'country' => $data['country'] ?? $user->country,
        ]);

        $this->customerService->update($user);

        $user->save();

        return $user->fresh();
    }

    /**
     * Cerrar sesión.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}