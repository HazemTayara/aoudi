<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;

class ResetPasswordOtpController extends Controller
{
    // Show OTP verification form (no email in URL)
    public function showVerifyForm($token)
    {
        $cacheKey = 'password_reset_' . $token;
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'رابط غير صالح أو منتهي الصلاحية. يرجى المحاولة مرة أخرى']);
        }

        return view('auth.verify-otp', compact('token'));
    }

    // Verify OTP
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $cacheKey = 'password_reset_' . $request->token;
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'انتهت صلاحية الجلسة. يرجى طلب إعادة تعيين كلمة المرور مرة أخرى']);
        }

        // Rate limit OTP attempts (3 attempts max)
        if (isset($cachedData['attempts']) && $cachedData['attempts'] >= 3) {
            Cache::forget($cacheKey);
            return redirect()->route('password.request')
                ->withErrors(['email' => 'لقد تجاوزت الحد المسموح من المحاولات. يرجى طلب رمز جديد']);
        }

        if ($cachedData['otp'] == $request->otp) {
            $resetToken = bin2hex(random_bytes(32));
            $cachedData['verified'] = true;
            $cachedData['reset_token'] = $resetToken;
            Cache::put($cacheKey, $cachedData, now()->addMinutes(15));

            // Store mapping for easy lookup
            Cache::put('reset_token_mapping_' . $resetToken, $cacheKey, now()->addMinutes(15));

            return redirect()->route('password.reset.form', ['reset_token' => $resetToken])
                ->with('status', 'تم التحقق بنجاح');
        }

        if ($cachedData['otp'] != $request->otp) {
            // Increment attempts
            $cachedData['attempts'] = ($cachedData['attempts'] ?? 0) + 1;
            Cache::put($cacheKey, $cachedData, now()->addMinutes(15));

            return back()->withErrors(['otp' => 'رمز التحقق غير صحيح']);
        }

        // Mark as verified and generate reset token
        $resetToken = bin2hex(random_bytes(32));
        $cachedData['verified'] = true;
        $cachedData['reset_token'] = $resetToken;
        Cache::put($cacheKey, $cachedData, now()->addMinutes(15));

        return redirect()->route('password.reset.form', ['reset_token' => $resetToken])
            ->with('status', 'تم التحقق بنجاح. يمكنك الآن إعادة تعيين كلمة المرور');
    }

    // Show password reset form
    public function showResetForm($resetToken)
    {
        // Find cache by reset_token (need to search)
        $cacheKey = $this->findCacheKeyByResetToken($resetToken);

        if (!$cacheKey) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'رابط غير صالح أو منتهي الصلاحية']);
        }

        $cachedData = Cache::get($cacheKey);

        if (!$cachedData || !isset($cachedData['verified']) || !$cachedData['verified']) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'لم يتم التحقق من هويتك. يرجى طلب إعادة تعيين كلمة المرور مرة أخرى']);
        }

        return view('auth.reset-password', compact('resetToken'));
    }

    // Final password reset
    public function reset(ResetPasswordRequest $request)
    {
        $cacheKey = $this->findCacheKeyByResetToken($request->reset_token);

        if (!$cacheKey) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'رابط غير صالح أو منتهي الصلاحية']);
        }

        $cachedData = Cache::get($cacheKey);

        if (
            !$cachedData ||
            !isset($cachedData['verified']) ||
            !$cachedData['verified']
        ) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'لم يتم التحقق من هويتك. يرجى المحاولة مرة أخرى']);
        }

        $user = User::find($cachedData['user_id']);
        $user->password = Hash::make($request->password);
        $user->save();

        Cache::forget($cacheKey);

        return redirect()->route('login')->with('status', 'تم إعادة تعيين كلمة المرور بنجاح. يرجى تسجيل الدخول');
    }

    // Helper method to find cache key by reset token (since Laravel cache doesn't support searching)
    private function findCacheKeyByResetToken($resetToken)
    {
        // Since we can't search cache, store mapping in a separate cache entry
        $tokenMapping = Cache::get('reset_token_mapping_' . $resetToken);

        if ($tokenMapping) {
            return $tokenMapping;
        }

        return null;
    }
}