<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class OtpVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'otp' => [
                'required',
                'string',
                'size:6',
                'regex:/^\d{6}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'otp.required' => 'OTP code is required.',
            'otp.size'     => 'OTP must be exactly 6 digits.',
            'otp.regex'    => 'OTP must contain numbers only.',
        ];
    }

    public function attributes(): array
    {
        return [
            'otp' => 'OTP code',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
