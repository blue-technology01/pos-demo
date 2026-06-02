<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleNames = Role::pluck('name')->toArray();
        return [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' ,
            'phone'     => 'required|string|unique:users,phone|max:20,',
            'password'  => 'required|string|min:8|confirmed',
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'role'      => ['required', Rule::in($roleNames)],  // user
        ];
    }
}
