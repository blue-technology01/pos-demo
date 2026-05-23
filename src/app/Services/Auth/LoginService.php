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
        $password = $data['password'];

        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'field' => 'email',
                'message' => 'Email wrong.'
            ];
        }

        if (!Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'field' => 'password',
                'message' => 'password wrong'
            ];
        }

        Auth::login($user);

        return [
            'success' => true
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
