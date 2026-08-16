<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\MailService;
use App\Services\OtpService;
use App\Services\Passport\CustomerSyncService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Throwable;

class LoginService
{
    public function __construct(
        protected OtpService $otpService,
        protected MailService $mailService,
        protected CustomerSyncService $customerSyncService,
    ) {
    }

    /**
     * Inicia el proceso de inicio de sesión.
     *
     * Busca al usuario, genera un código OTP
     * y lo envía al correo registrado.
     */
    public function login(array $data): User
    {
        $user = $this->findUser($data);

        if (! $user) {
            $user = $this->tryJitCatapultLookup($data);
        }

        if (! $user) {
            throw ValidationException::withMessages([
                'credentials' => ['Invalid credentials.'],
            ]);
        }

        $key = 'login:' . strtolower($user->email);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => [
                    'Too many login attempts. Please try again in '
                    . ceil(RateLimiter::availableIn($key) / 60)
                    . ' minute(s).',
                ],
            ]);
        }

        RateLimiter::hit($key, 900);

        // Generar OTP
        $otp = $this->otpService->generate($user);

        // Enviar correo
        $this->mailService->sendOtp(
            $user,
            $otp
        );

        return $user;
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

    /**
     * Si el usuario no existe localmente, intenta encontrarlo en
     * CATAPULT antes de rechazar el login (ver
     * CustomerSyncService::syncOne). Cualquier falla de CATAPULT
     * (caído, timeout, XML raro) se traga en silencio: el
     * comportamiento por default sigue siendo "credenciales
     * inválidas", nunca un login colgado ni un error 500 — el
     * timeout corto ya lo garantiza PassportClient.
     */
    private function tryJitCatapultLookup(array $data): ?User
    {
        try {
            return $this->customerSyncService->syncOne(
                $data['email'] ?? null,
                $data['phone'] ?? null,
            );
        } catch (Throwable $e) {
            Log::warning(
                'JIT CATAPULT lookup failed during login: '
                . $e->getMessage()
            );

            return null;
        }
    }
}
