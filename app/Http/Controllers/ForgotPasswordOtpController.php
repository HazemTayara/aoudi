<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordOtpController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'هذا البريد الإلكتروني غير مسجل في النظام']);
        }

        $otp = random_int(100000, 999999);
        $verificationToken = bin2hex(random_bytes(32)); // ← Generate unique token

        $cacheKey = 'password_reset_' . $verificationToken; // ← Use token as key

        Cache::put($cacheKey, [
            'otp' => $otp,
            'user_id' => $user->id,
            'email' => $user->email,
            'verified' => false,
            'attempts' => 0, // Track OTP attempts
        ], now()->addMinutes(15));

        Mail::to($user->email)->send(new OtpMail($otp, $user->name));

        // Redirect with token (not email)
        return redirect()->route('password.verify.form', ['token' => $verificationToken])
            ->with('status', 'تم إرسال رمز التحقق إلى بريدك الإلكتروني');
    }
}