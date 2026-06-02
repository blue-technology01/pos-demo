<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Strip all non-numeric characters except leading +
     * e.g. "+855 12-345-678" → "+85512345678"
     */
    protected function prepareForValidation(): void
    {
        $phone   = $this->input('phone', '');
        $cleaned = preg_replace('/[^\d+]/', '', $phone);

        $this->merge(['phone' => $cleaned]);
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'min:9',
                'max:15',
                'exists:users,phone',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'A phone number is required.',
            'phone.string'   => 'The phone number must be a valid string.',
            'phone.min'      => 'Please enter a valid phone number.',
            'phone.max'      => 'Please enter a valid phone number.',
            'phone.exists'   => 'No account found with this phone number.',
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => 'phone number',
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
