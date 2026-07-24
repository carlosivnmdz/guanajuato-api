<?php

namespace App\Services;

use App\Models\OtpCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OtpService
{
    /**
     * Genera un código OTP de 6 dígitos.
     */
    public function generate(Model $model): string
    {
        // Limitar a 5 solicitudes cada 15 minutos
        $recentRequests = $model->otpCodes()
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($recentRequests >= 5) {
            Log::warning('OTP rate limit exceeded.', [
                'model' => get_class($model),
                'id'    => $model->id,
            ]);

            throw ValidationException::withMessages([
                'email' => [
                    'Too many verification requests. Please try again in 15 minutes.',
                ],
            ]);
        }

        // Eliminar códigos anteriores sin usar
        $model->otpCodes()
            ->whereNull('used_at')
            ->delete();

        $otp = str_pad(
            (string) random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        $model->otpCodes()->create([
            'code'       => Hash::make($otp),
            'attempts'   => 0,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        Log::info('OTP generated.', [
            'model' => get_class($model),
            'id'    => $model->id,
        ]);

        return $otp;
    }

    /**
     * Verifica el código OTP.
     */
    public function verify(
        Model $model,
        string $code
    ): bool {

        $otp = $model->otpCodes()
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (!$otp) {

            Log::warning('OTP not found.', [
                'model' => get_class($model),
                'id'    => $model->id,
            ]);

            return false;
        }

        if ($otp->expires_at->isPast()) {

            Log::warning('OTP expired.', [
                'model' => get_class($model),
                'id'    => $model->id,
            ]);

            return false;
        }

        if ($otp->attempts >= 5) {

            Log::warning('OTP blocked by attempts.', [
                'model' => get_class($model),
                'id'    => $model->id,
            ]);

            return false;
        }

        if (!Hash::check(trim($code), $otp->code)) {

            $otp->increment('attempts');

            Log::warning('Invalid OTP.', [
                'model'    => get_class($model),
                'id'       => $model->id,
                'attempts' => $otp->attempts,
            ]);

            return false;
        }

        $otp->update([
            'used_at' => now(),
        ]);

        Log::info('OTP verified.', [
            'model' => get_class($model),
            'id'    => $model->id,
        ]);

        return true;
    }
}