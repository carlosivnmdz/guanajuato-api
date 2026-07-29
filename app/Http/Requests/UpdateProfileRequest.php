<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'first_name' => [
                'required',
                'string',
                'max:25',
            ],

            'last_name' => [
                'nullable',
                'string',
                'max:25',
            ],

            'birthday' => [
                'nullable',
                'date',
                'before:today',
            ],

            'country' => [
                'nullable',
                'string',
                'max:30',
            ],

        ];
    }
}
