<?php

namespace App\Services\Auth;

use App\Models\PendingRegistration;
use App\Services\MailService;
use App\Services\OtpService;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function __construct(
        protected OtpService $otpService,
        protected MailService $mailService,
    ) {
    }

    /**
     * Inicia el proceso de registro.
     *
     * Crea un registro temporal, genera un OTP
     * y lo envía al correo del usuario.
     */
    public function register(array $data): PendingRegistration
    {
        return DB::transaction(function () use ($data) {

            // Eliminar intentos anteriores del mismo usuario
            PendingRegistration::query()
                ->where('email', $data['email'])
                ->orWhere('phone', $data['phone'])
                ->delete();

            // Crear registro temporal
            $pending = PendingRegistration::create([
                'first_name'  => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name'   => $data['last_name'],
                'birthday'    => $data['birthday'],
                'country'     => $data['country'],
                'email'       => $data['email'],
                'phone'       => $data['phone'],
            ]);

            // Generar OTP
            $otp = $this->otpService->generate($pending);

            // Enviar correo
            $this->mailService->sendOtp(
                $pending,
                $otp
            );

            return $pending;
        });
    }
}