<?php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        
        $otp = random_int(100000, 999999);
        Cache::put('password_otp_' . $request->email, $otp, now()->addMinutes(15));
        
        // In production, send email/SMS with OTP
        // Mail::to($request->email)->send(new OtpMail($otp));
        
        return response()->json([
            'message' => 'OTP sent to your email address.',
            'debug_otp' => config('app.debug') ? $otp : null,
        ]);
    }
    
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
        ]);
        
        $cachedOtp = Cache::get('password_otp_' . $request->email);
        
        if (!$cachedOtp || (int) $request->otp !== (int) $cachedOtp) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }
        
        $resetToken = Str::random(64);
        Cache::put('password_reset_' . $request->email, $resetToken, now()->addMinutes(30));
        Cache::forget('password_otp_' . $request->email);
        
        return response()->json(['reset_token' => $resetToken]);
    }
    
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $cachedToken = Cache::get('password_reset_' . $request->email);
        
        if (!$cachedToken || $request->reset_token !== $cachedToken) {
            return response()->json(['message' => 'Invalid or expired reset token.'], 422);
        }
        
        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete();
        
        Cache::forget('password_reset_' . $request->email);
        
        return response()->json(['message' => 'Password reset successfully. Please login with your new password.']);
    }
}