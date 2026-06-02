<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password'              => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()  // abc
                    ->mixedCase()  // Abc
                    ->numbers() // 123
                    ->symbols()
            ],
            'password_confirmation' => [
                'required',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required'              => 'A new password is required.',
            'password.confirmed'             => 'Passwords do not match.',
            'password_confirmation.required' => 'Please confirm your new password.',
        ];
    }

    public function attributes(): array
    {
        return [
            'password'              => 'password',
            'password_confirmation' => 'password confirmation',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
        );
    }

}
