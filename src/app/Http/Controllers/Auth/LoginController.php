<?php

namespace App\Http\Controllers\Auth;
use App\Services\Auth\LoginService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(
        protected LoginService $loginService
    ) {}

    public function index()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $ip  = $request->ip();
        // $key = "login-attempt:{$ip}";
        $key = "login-attempt:{$ip}:" . $request->input('email');
        // Rate limiting
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => "Too many login attempts. Try again in {$seconds} seconds."
            ], 429);
        }

        $result = $this->loginService->login($request->validated());

        // login fail
        if (!$result['success']) {

            // retry decay = 60 seconds
            // 1 request =60 seconds
            // 2 request =60 seconds
            // 3 reqest =60 seconds

            RateLimiter::hit($key,60);
            $decay = config('auth.lockout_seconds', 60);
            $maxAttempts = config('auth.max_attempts', 5);
            return response()->json([
                'errors' => [
                    $result['field'] => [$result['message']]
                ]
            ], 422);
        }

        // login success
        RateLimiter::clear($key);

        $request->session()->regenerate();

        return response()->json([
            'message'  => 'Login successful.',
            'redirect' => route('admin.dashboard')
        ]);
    }

    public function logout(Request $request)
    {
        $this->loginService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }
}
