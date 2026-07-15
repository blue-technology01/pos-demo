<?php

namespace App\Services\Auth;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LoginService
{

    // login
    public function login(array $data): array
    {
        $email = strtolower(trim($data['email']));

        if (!Auth::attempt([
            'email'    => $email,
            'password' => $data['password'],
        ])) {
            return [
                'success' => false,
                'field'   => 'email',
                'message' => 'Invalid email or password',
            ];
        }

        // send Telegram notification on successful login
        $this->sendTelegramMessage(Auth::user());

        return [
            'success' => true,
        ];
    }

    // logout function
    public function logout(): void
    {
        Auth::logout();
    }

    // send message to the telegram bot current login time
    private function sendTelegramMessage($user)
    {
        $telegramToken = config('services.telegram.token');
        $chatId        = config('services.telegram.chat_id');

        if (!$telegramToken || !$chatId) return;

        $currentTime = Carbon::now()->toDateTimeString();
        $username    = $user->name ?? $user->email;
        $message     = "User {$username} has logged in at {$currentTime}.";

        Http::post("https://api.telegram.org/bot{$telegramToken}/sendMessage", [
            'chat_id' => $chatId,
            'text'    => $message,
        ]);
    }

}
