<?php

namespace App\Http\Controllers\Auth;
use App\Services\Auth\LoginService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    //  injection for login service to handle the business logic of user authentication
    public function __construct(
        protected LoginService $loginService
    ) {}

    public function index()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $ip = $request->ip();
        $key = "login-attempt:{$ip}:" . $request->input('email');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'email' => 'Too many login attempts. Try again later.'
            ]);
        }
        $result = $this->loginService->login($request->validated());


        if (!$result['success']) {
            return back()
                ->withInput()
                ->withErrors([
                    $result['field'] => $result['message']
                ]);
        }
        RateLimiter::clear($key);

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('success', 'Logged in successfully');
    }

    public function logout(Request $request)
    {
        $this->loginService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }
}
