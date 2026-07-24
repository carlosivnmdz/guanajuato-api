<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\MailService;
use App\Services\OtpService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginService
{
    public function __construct(
        protected OtpService $otpService,
        protected MailService $mailService,
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
}