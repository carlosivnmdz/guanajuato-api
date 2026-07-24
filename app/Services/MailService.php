<?php

namespace App\Services;

use App\Mail\OtpMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailService
{
    /**
     * Envía el código OTP por correo.
     */
    public function sendOtp(Model $model, string $code): void
    {
        try {

            Mail::to($model->email)
                ->send(new OtpMail($code));

        } catch (Throwable $e) {

            Log::error('Unable to send OTP email.', [
                'model' => get_class($model),
                'id'    => $model->id,
                'email' => $model->email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}