<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\EmailVerificationOtpMail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    /**
     * Generate, cache, and send 6-digit OTP to user's email.
     */
    public static function generateAndSendOtp(User $user): string
    {
        $otp = sprintf('%06d', mt_rand(100000, 999999));
        Cache::put("email_otp_{$user->email}", [
            'otp' => (string)$otp,
            'user_id' => $user->id,
        ], now()->addMinutes(10));

        try {
            Mail::to($user->email)->send(new EmailVerificationOtpMail((string)$otp, $user->name));
            Log::info("Email OTP dispatched to {$user->email}: {$otp}");
        } catch (\Throwable $e) {
            Log::warning("Failed to send OTP email via SMTP to {$user->email}: " . $e->getMessage());
        }

        return (string)$otp;
    }

    /**
     * Mark the user's email address as verified from the signed link (Web fallback).
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::withoutGlobalScopes()->findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        if (!$request->hasValidSignature()) {
            abort(403, 'Verification link has expired.');
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return view('auth.verified', ['user' => $user]);
    }

    /**
     * Resend the 6-digit OTP verification code.
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::withoutGlobalScopes()->where('email', $request->email)->first();

        $otp = self::generateAndSendOtp($user);

        return response()->json([
            'message' => 'A 6-digit verification code has been sent to your email address.',
            'debug_otp' => config('app.debug') ? $otp : null,
        ], 200);
    }

    /**
     * Verify the 6-digit OTP submitted from the Flutter app.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
        ]);

        $cached = Cache::get("email_otp_{$request->email}");

        if (!$cached || $cached['otp'] !== trim($request->otp)) {
            return response()->json([
                'message' => 'Invalid or expired verification code. Please request a new code.',
                'error' => 'invalid_otp',
            ], 422);
        }

        $user = User::withoutGlobalScopes()->where('email', $request->email)->first();

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // Clean up OTP from cache
        Cache::forget("email_otp_{$request->email}");

        // Create Sanctum Bearer token for immediate authenticated access
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully! Welcome to Doxa Business Finder.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'tenant_id' => $user->tenant_id,
                'created_at' => $user->created_at,
            ],
            'tenant' => $user->tenant,
        ], 200);
    }
}
