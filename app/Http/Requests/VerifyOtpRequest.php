<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'email' => [
                'nullable',
                'email',
            ],

            'phone' => [
                'nullable',
                'string',
            ],

            'otp' => [
                'required',
                'digits:6',
            ],

        ];
    }
}