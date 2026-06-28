<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    // ─────────────────────────────────────────
    // Show login page
    // ─────────────────────────────────────────
    public function index()
    {
        return view('auth.login');
    }

    // ─────────────────────────────────────────
    // Handle login
    // ─────────────────────────────────────────
    public function login(LoginRequest $request)
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'email' => 'Too many login attempts. Please try again later.',
            ]);
        }

        $email = strtolower(trim($request->input('email')));

        if (!Auth::attempt(['email' => $email, 'password' => $request->input('password')])) {
            RateLimiter::hit($key, 60);

            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Invalid email or password.',
                ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        //  Send Telegram login notification
        $this->sendTelegram(Auth::user(), 'login');

        return redirect()->to($this->redirectByRole());
    }

    // ─────────────────────────────────────────
    // Handle logout
    // ─────────────────────────────────────────
    public function logout(Request $request)
    {
        $user = Auth::user(); //  Get user BEFORE logout

        //  Send Telegram logout notification
        $this->sendTelegram($user, 'logout');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ─────────────────────────────────────────
    // Send Telegram notification
    // ─────────────────────────────────────────
    private function sendTelegram($user, string $action): void
    {
        $token   = config('services.telegram.token');
        $chatId  = config('services.telegram.chat_id');
        $topicId = config('services.telegram.topic_id');

        if (!$token || !$chatId || !$user) return;

        $username = $user->name ?? $user->email;
        $time     = Carbon::now()->format('H:i:s');
        $date     = Carbon::now()->format('d/m/Y');

        if ($action === 'login') {
            $message =
                "╔══════════════════╗\n" .
                "║   🟢  USER LOGIN       ║\n" .
                "╚══════════════════╝\n" .
                "\n" .
                "👤 *Name:*  {$username}\n" .
                "📅 *Date:*  {$date}\n" .
                "🕐 *Time:*  {$time}\n" .
                "\n" .
                " _Successfully logged in_";
        } else {
            $message =
                "╔══════════════════╗\n" .
                "║   🔴  USER LOGOUT     ║\n" .
                "╚══════════════════╝\n" .
                "\n" .
                "👤 *Name:*  {$username}\n" .
                "📅 *Date:*  {$date}\n" .
                "🕐 *Time:*  {$time}\n" .
                "\n" .
                "👋 _Successfully logged out_";
        }

        $payload = [
            'chat_id'    => $chatId,
            'text'       => $message,
            'parse_mode' => 'Markdown',
        ];

        if ($topicId) {
            $payload['message_thread_id'] = (int) $topicId;
        }

        try {
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", $payload);
        } catch (\Exception $e) {
            Log::error('Telegram notification failed: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────
    // Redirect based on user role after login
    // ─────────────────────────────────────────
    private function redirectByRole(): string
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return route('admin.dashboard');
        }

        if ($user->hasRole('cashier')) {
            return route('cashier.pos');
        }

        return route('forbidden');
    }

    // ─────────────────────────────────────────
    // Rate limiter key
    // ─────────────────────────────────────────
    private function throttleKey(Request $request): string
    {
        return 'login-attempt:' . $request->ip() . ':' . strtolower((string) $request->input('email'));
    }
}
