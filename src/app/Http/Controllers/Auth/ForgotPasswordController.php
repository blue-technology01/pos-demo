<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\OtpVerifyRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\ForgotPasswordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(
        protected ForgotPasswordService $forgotPasswordService
    ) {}

    // show form forgot password
    public function index(): View
    {
        return view('auth.forgot-password');
    }

    // send otp to phone
    public function sendOtp(ForgotPasswordRequest $request): JsonResponse
    {
        $phone = $request->validated()['phone'];

        $result = $this->forgotPasswordService->sendOtp($phone);

        if ($result['success']) {

            session([
                'otp_phone' => $phone
            ]);

            return response()->json([
                'success'  => true,
                // 'otp'=> $otp,
                'message'  => 'OTP sent successfully.',
                'redirect' => route('auth.otp.show') // IMPORTANT

                // 'redirect' => route('auth.otp.show')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Too many attempts.'
        ], 429);
    }

    public function resendOtp(): JsonResponse
    {
        $phone = session('otp_phone');

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please start again.'
            ], 419);
        }

        $result = $this->forgotPasswordService->sendOtp($phone);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'] ?? 'OTP resent successfully.'
        ], $result['success'] ? 200 : 429);
    }

    // show otp form
    public function otpForm(): View|RedirectResponse
    {
        // dd(session()->all());
        if (!session()->has('otp_phone')) {

            return redirect()
                ->route('auth.forgot-password')
                ->with('error', 'Session expired. Please try again.');
        }

        return view('auth.otp-verification');
    }

    // verify otp
    public function verifyOtp(OtpVerifyRequest $request): JsonResponse
    {
        $phone = session('otp_phone');

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired.'
            ], 419);
        }

        $result = $this->forgotPasswordService->verifyOtp(
            $phone,
            $request->otp
        );

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        session([
            'reset_user_id' => $result['user_id']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'redirect' => route('auth.reset-password.show')
        ]);
    }

    // show form reset password
    public function resetForm(): View|RedirectResponse
    {
        if (!session('reset_user_id')) {
            return redirect()->route('auth.forgot-password')
                ->withErrors(['error' => 'Session expired. Please try again.']);
        }
        return view('auth.reset-password');
    }

    /**
     * Step 3 — Reset password.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $result = $this->forgotPasswordService->resetPassword(
            $request->validated()['password']
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
