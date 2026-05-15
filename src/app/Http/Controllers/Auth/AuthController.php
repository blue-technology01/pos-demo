<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showFormRegister()
    {
        $roles = Role::select('id', 'name')->get();

        if (request()->ajax() || request()->wantsJson()) {
            $users=User::with('roles')
                    ->select('id', 'name', 'email', 'phone', 'avatar', 'created_at')
                    ->latest()
                    ->paginate(15);
            return response()->json($users);
        }

        $users = User::with('roles')
                ->select('id', 'name', 'email', 'phone', 'avatar', 'created_at')
                ->latest()
                ->paginate(15);
        return view('admin.users.user', compact('users', 'roles'));
    }

    public function register(Request $request)
    {
        $roleNames = Role::pluck('name')->toArray();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|unique:users,phone|max:20',
            'password' => 'required|string|min:6|confirmed',
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'role'     => ['required', Rule::in($roleNames)],
        ]);

        $avatarPath = $request->hasFile('avatar')
            ? $request->file('avatar')->store('avatars', 'public')
            : null;

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'avatar'   => $avatarPath,
        ]);

        $user->assignRole($validated['role']);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully!',
            'user'    => $user->load('roles'),
        ]);
    }

    public function showFormLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('web')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('auth.login');
    }

    public function showFormForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
        ]);

        $user = User::where('phone', $request->phone)->first();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in cache for 10 minutes was 1 minute
        Cache::put('password_reset_' . $user->phone, [
            'otp'     => $otp,
            'user_id' => $user->id,
        ], now()->addMinutes(10));

        return redirect()->route('auth.otp.show')
            ->with([
                'phone' => $user->phone,
                'otp'   => $otp, // remove in production
            ]);
    }

    public function showFormOtp()
    {
        if (!session('phone')) {
            return redirect()->route('auth.forgot-password')
                ->withErrors(['error' => 'Session expired. Please try again.']);
        }

        return view('auth.otp-verification');
    }

    public function verifyOtp(Request $request)
    {
        //No spaces in validation rules
        $request->validate([
            'phone' => 'required|string',
            'otp'   => 'required|string|size:6',
        ]);

        $cached = Cache::get('password_reset_' . $request->phone);

        // Check null FIRST before accessing array keys
        if (!$cached) {
            return back()->withErrors([
                'otp' => 'OTP has expired. Please request a new one.'
            ])->with('phone', $request->phone);
        }

        // OTP does not match
        if ($cached['otp'] !== $request->otp) {
            return back()->withErrors([
                'otp' => 'Invalid OTP. Please try again.'
            ])->with('phone', $request->phone);
        }

        // store user_id in session
        $request->session()->put('reset_user_id', $cached['user_id']);

        // Delete OTP from cache so it can't be reused
        Cache::forget('password_reset_' . $request->phone);

        return redirect()->route('auth.reset-password.show');
    }

    public function showFormResetPassword()
    {
        if (!session('reset_user_id')) {
            return redirect()->route('auth.forgot-password')
                ->withErrors(['error' => 'Session expired. Please try again.']);
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {

        $request->validate([
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        $userId = $request->session()->get('reset_user_id');

        if (!$userId) {
            return redirect()->route('auth.forgot-password')
                ->withErrors(['error' => 'Session expired. Please try again.']);
        }

        $user = User::findOrFail($userId);

        // Check new password is not same as old password
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'New password cannot be the same as your old password.'
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Clear reset session
        $request->session()->forget('reset_user_id');

        return redirect()->route('auth.login')
            ->with('success', 'Password reset successfully! Please login.');
    }
}
