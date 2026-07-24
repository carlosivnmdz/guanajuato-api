<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyLoginOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'email' => [
                'required_without:phone',
                'nullable',
                'email',
            ],

            'phone' => [
                'required_without:email',
                'nullable',
                'string',
                'max:20',
            ],

            'otp' => [
                'required',
                'digits:6',
            ],
        ];
    }
}