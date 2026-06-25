<?php

namespace App\Http\Controllers\Auth;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Services\Auth\ForgotPasswordService;
use App\Http\Requests\Auth\OtpVerifyRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;

class ForgotPasswordController extends Controller
{
    public function __construct(
        protected ForgotPasswordService $forgotPasswordService
    ) {}

    // show forgot password page
    public function index(): View
    {
        return view('auth.forgot-password');
    }

    // send otp
    public function sendOtp(ForgotPasswordRequest $request): RedirectResponse
    {
        $phone = $request->validated()['phone'];

        $result = $this->forgotPasswordService->sendOtp($phone);

        if (!$result['success']) {
            return back()
                ->withInput()
                ->with('error', $result['message']);
        }

        session([
            'otp_phone' => $phone
        ]);

        return redirect()
            ->route('auth.otp.show')
            ->with('success', $result['message']);
    }

    // resend otp
    public function resendOtp(): RedirectResponse
    {
        $phone = session('otp_phone');

        if (!$phone) {
            return redirect()
                ->route('auth.forgot-password')
                ->with('error', 'Session expired. Please try again.');
        }

        $result = $this->forgotPasswordService->sendOtp($phone);

        if (!$result['success']) {
            return back()
                ->with('error', $result['message']);
        }

        return back()
            ->with('success', $result['message']);
    }

    // show otp page
    public function otpForm(): View|RedirectResponse
    {
        if (!session()->has('otp_phone')) {
            return redirect()
                ->route('auth.forgot-password')
                ->with('error', 'Session expired. Please try again.');
        }

        return view('auth.otp-verification');
    }

    // verify otp
    public function verifyOtp(OtpVerifyRequest $request): RedirectResponse
    {
        $phone = session('otp_phone');

        if (!$phone) {
            return redirect()
                ->route('auth.forgot-password')
                ->with('error', 'Session expired.');
        }

        $result = $this->forgotPasswordService->verifyOtp(
            $phone,
            $request->validated()['otp']
        );

        if (!$result['success']) {
            return back()
                ->withInput()
                ->with('error', $result['message']);
        }

        session([
            'reset_user_id' => $result['user_id']
        ]);

        return redirect()
            ->route('auth.reset-password.show')
            ->with('success', $result['message']);
    }

    // show reset password page
    public function resetForm(): View|RedirectResponse
    {
        if (!session()->has('reset_user_id')) {
            return redirect()
                ->route('auth.forgot-password')
                ->with('error', 'Session expired. Please try again.');
        }
        return view('auth.reset-password');
    }

    // reset password
    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        if (!session()->has('reset_user_id')) {
            return redirect()
                ->route('auth.forgot-password')
                ->with('error', 'Session expired. Please try again.');
        }

        $result = $this->forgotPasswordService->resetPassword(
            $request->validated()['password']
        );

        if (!$result['success']) {
            return back()
                ->withInput()
                ->with('error', $result['message']);
        }

        session()->forget(['reset_user_id', 'otp_phone']);

        return redirect()
            ->route('auth.login')
            ->with('success', $result['message']);
    }
}
