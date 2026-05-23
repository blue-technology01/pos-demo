<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $this->user->id,
            'phone'     => 'required|string|max:20|unique:users,phone,' . $this->user->id,
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'role'      => 'required|exists:roles,name',
            'password'  => 'nullable|string|min:8|confirmed',
        ];
    }
}
