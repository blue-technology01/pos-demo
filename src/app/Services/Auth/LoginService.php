<?php

namespace App\Services\Auth;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class LoginService
{

    // login
    public function login(array $data): array
    {
        $email = strtolower(trim($data['email']));

        if (!Auth::attempt([ // attempt use for check email and password
            'email' => $email,
            'password' => $data['password']
        ])) {
            return [
                'success' => false,
                'field' => 'email',
                'message' => 'Invalid email or password'
            ];
        }
        // Regenerate session  
        request()->session()->regenerate();

        return [
            'success' => true,
        ];
    }

    // logout function
    public function logout(): void
    {
        Auth::logout();
    }

    // Get all roles (for dropdown)
    public function getRoles()
    {
        return Role::select('id', 'name')->orderBy('name')->get();
    }

}
