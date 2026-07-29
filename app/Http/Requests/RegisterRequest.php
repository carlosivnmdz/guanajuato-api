<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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

            'middle_name' => [
                'nullable',
                'string',
                'max:25',
            ],

            'last_name' => [
                'required',
                'string',
                'max:25',
            ],

            'birthday' => [
                'required',
                'date',
            ],

            'country' => [
                'required',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'unique:users,email',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                'unique:users,phone',
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
            ],

        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if (
                empty($this->email) &&
                empty($this->phone)
            ) {

                $validator->errors()->add(
                    'email',
                    'Debe proporcionar un correo o un teléfono.'
                );

            }

        });
    }
}