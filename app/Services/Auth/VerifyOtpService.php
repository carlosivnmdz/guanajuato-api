<?php

namespace App\Services\Auth;

use App\Models\PendingRegistration;
use App\Models\User;
use App\Services\OtpService;
use App\Services\Passport\CustomerService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class VerifyOtpService
{
    public function __construct(
        protected OtpService $otpService,
        protected CustomerService $customerService,
    ) {
    }

    /**
     * Verifica el OTP, crea el cliente en CATAPULT,
     * crea el usuario local y genera el token.
     */
    public function verify(array $data): array
    {
        $pending = PendingRegistration::query()
            ->when(
                !empty($data['email']),
                fn ($query) => $query->where('email', $data['email'])
            )
            ->when(
                !empty($data['phone']),
                fn ($query) => $query->where('phone', $data['phone'])
            )
            ->first();

        if (!$pending) {
            throw new ModelNotFoundException(
                'Pending registration not found.'
            );
        }

        if (
            !$this->otpService->verify(
                $pending,
                $data['otp']
            )
        ) {
            throw ValidationException::withMessages([
                'otp' => [
                    'The verification code is invalid or has expired.',
                ],
            ]);
        }

        // Limpiar el RateLimiter al validar correctamente el OTP
        RateLimiter::clear('register:' . strtolower($pending->email));

        return DB::transaction(function () use ($pending) {

            // Crear cliente completo en CATAPULT
            $customerId = $this->customerService->create($pending);

            // Crear usuario local
            $user = User::create([
                'customer_id'       => $customerId,
                'first_name'        => $pending->first_name,
                'middle_name'       => $pending->middle_name,
                'last_name'         => $pending->last_name,
                'birthday'          => $pending->birthday,
                'country'           => $pending->country,
                'email'             => $pending->email,
                'phone'             => $pending->phone,
                'email_verified_at' => now(),
                'phone_verified_at' => null,
            ]);

            // Eliminar registro temporal
            $pending->delete();

            return [
                'token' => $user->createToken('mobile')->plainTextToken,
                'user'  => $user->fresh(),
            ];
        });
    }
}