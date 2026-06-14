<?php

namespace App\Services\Auth;

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
        return [
            'success' => true,
        ];
    }

    // logout function
    public function logout(): void
    {
        Auth::logout();
    }

}
