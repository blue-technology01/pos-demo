<?php

namespace App\Http\Controllers\Auth;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Services\Auth\ForgotPasswordService;
use App\Http\Requests\Auth\OtpVerifyRequest;

class OtpVerifyController extends Controller
{
    public function __construct(
        protected ForgotPasswordService $forgotPasswordService
    ) {}

    public function showOtpForm(): View|RedirectResponse
    {
        $result = $this->forgotPasswordService->canAccessOtpPage();

        if (!$result['success']) {

            return redirect()
                ->route('auth.forgot-password')
                ->withErrors([
                    'error' => $result['message']
                ]);
        }

        return view('auth.otp-verification');
    }

    public function verifyOtp(
        OtpVerifyRequest $request
    ): RedirectResponse {

        $phone = session('phone');

        if (!$phone) {

            return redirect()
                ->route('auth.forgot-password')
                ->withErrors([
                    'error' => 'Session expired. Please try again.'
                ]);
        }

        $result = $this->forgotPasswordService->verifyOtp(
            $phone,
            $request->otp
        );

        if (!$result['success']) {

            return back()->withErrors([
                'otp' => $result['message']
            ]);
        }

        return redirect()
            ->route('auth.reset-password.show');
    }
}
