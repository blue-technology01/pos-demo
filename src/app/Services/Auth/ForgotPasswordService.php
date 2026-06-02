<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;

class ForgotPasswordService
{
    private const OTP_LENGTH    = 6;
    private const OTP_TTL       = 300; // 5mn >  valid
    private const MAX_ATTEMPTS  = 3; // try attampt 3> block
    private const DECAY_SECONDS = 300;   // 5mn block time after max attempts

    // send OTP
    public function sendOtp(string $phone): array
    {
        $key = "otp-send:{$phone}";

        // Check rate limit before generating OTP to prevent abuse
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return [
                'success' => false,
                'message' => "Too many attempts. Try again later."
            ];
        }

        $otp = $this->generateOtp();

        // Store only in cache
        Cache::put("otp:{$phone}", Hash::make($otp), self::OTP_TTL);

        // Store only flow state in session
        Session::put('otp_phone', $phone);
        // Hit rate limiter after generating OTP to prevent abuse
        RateLimiter::hit($key, self::DECAY_SECONDS);

        $sent = $this->sendSms($phone, $otp);

        if (!$sent) {
            Log::error('Failed to send OTP SMS', ['phone' => $phone]);

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ];
        }

        Log::info('OTP sent successfully', ['phone' => $phone]);

        return [
            'success'  => true,
            'message'  => 'OTP sent successfully. Valid for 5 minutes.',
            'redirect' => route('auth.otp.show')
        ];
    }


    // check OTP page access
    public function canAccessOtpPage(): array
    {
        if (!Session::has('otp_phone')) {
            return [
                'success' => false,
                'message' => 'OTP session expired. Please request again.'
            ];
        }

        return [
            'success' => true
        ];
    }

    // verify OTP
    public function verifyOtp(string $phone, string $otp): array
    {
        if (empty($phone)) {
            return [
                'success' => false,
                'message' => 'Session expired. Please request OTP again.'
            ];
        }

        $key        = "otp-verify:{$phone}";
        $cachedHash = Cache::get("otp:{$phone}");


        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return [
                'success' => false,
                'message' => 'Too many attempts. Try again later.'
            ];
        }
        // Check OTP validity
        if (!$cachedHash || !Hash::check($otp, $cachedHash)) {
            RateLimiter::hit($key, self::DECAY_SECONDS);
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP.'
            ];
        }

        RateLimiter::clear($key);

        $user = User::where('phone', $phone)->first();

        Cache::put("otp_verified:{$phone}", true, self::OTP_TTL);

        return [
            'success' => true,
            'message' => 'OTP verified successfully.',
            'user_id' => $user->id,
        ];
    }

    // reset password
    public function resetPassword(string $password): array
    {
        $phone = Session::get('otp_phone');

        if (!$phone) {
            return [
                'success'  => false,
                'message'  => 'Session expired. Please restart the process.',
                'redirect' => route('auth.forgot-password')
            ];
        }

        // ensure OTP was verified
        if (!Cache::get("otp_verified:{$phone}")) {
            return [
                'success' => false,
                'message' => 'OTP not verified.'
            ];
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        // Prevent same password reuse
        if (Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'New password cannot be the same as old password.'
            ];
        }

        $user->update([
            'password' => Hash::make($password),
        ]);

        // Cleanup
        Session::forget('otp_phone');
        Cache::forget("otp_verified:{$phone}");
        Cache::forget("otp:{$phone}");

        Log::info('Password reset successfully', [
            'user_id' => $user->id,
            'phone'   => $phone
        ]);

        return [
            'success'  => true,
            'message'  => 'Password reset successfully!',
            'redirect' => route('auth.login')
        ];
    }

    // generate otp
    private function generateOtp(): string
    {
        return str_pad(
            (string) random_int(0, 999999),
            self::OTP_LENGTH,
            '0',
            STR_PAD_LEFT  // Ensure OTP is always 6 digits
        );
    }

    // send sms
    private function sendSms(string $phone, string $otp): bool
    {
        Log::info("[SMS] OTP sent", [
            'phone' => $phone,
            'otp'   => $otp
        ]);

        return true;
    }
}
