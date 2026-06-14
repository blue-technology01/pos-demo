<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => trim((string) $this->input('phone')),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone'     => ['required', 'string', 'max:20', Rule::unique('users', 'phone')],
            'password'  => 'required|string|min:8|confirmed',
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'role'      => ['required', 'string', Rule::exists('roles', 'name')],
        ];
    }
}
