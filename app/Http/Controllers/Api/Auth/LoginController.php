<?php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\LoginLog;
use App\Models\UserLoginSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $loginInput = $validated['email'];
        $throttleKey = $loginInput . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Try again in {$seconds} seconds."],
            ]);
        }

        // Determine if login is email or phone
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials = [$fieldType => $loginInput, 'password' => $validated['password']];

        if (!Auth::attempt($credentials)) {
            // Fallback check: if phone was passed in email field or vice versa
            $altField = $fieldType === 'email' ? 'phone' : 'email';
            if (!Auth::attempt([$altField => $loginInput, 'password' => $validated['password']])) {
                RateLimiter::hit($throttleKey, 60);

                $existingUser = User::where('email', $loginInput)->orWhere('phone', $loginInput)->first();
                if ($existingUser) {
                    try {
                        LoginLog::create([
                            'user_id' => $existingUser->id,
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'device' => $validated['device_name'] ?? 'mobile_app',
                            'status' => 'failed',
                        ]);
                    } catch (\Throwable $e) {}
                }

                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }
        }

        RateLimiter::clear($throttleKey);
        $user = Auth::user();

        // 1. Check if user is suspended
        if ($user->status === 'suspended') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Your account has been suspended. Contact support.'],
            ]);
        }

        // 2. Check membership expiry for non-super-admins
        if (!$user->is_super_admin && !$user->isMembershipActive()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Your membership has expired. Please contact admin or renew to continue.'],
            ]);
        }

        // 3. Check if user has verified email (if required and not super admin)
        if (!$user->hasVerifiedEmail() && !$user->is_super_admin && config('auth.verify_email', false)) {
            Auth::logout();
            return response()->json([
                'error' => 'email_not_verified',
                'message' => 'Please verify your email address before logging in.',
                'email' => $user->email,
            ], 403);
        }

        // 4. Strict Single-Device Lock: Block login if user already has an active session on another device
        if (!$user->is_super_admin && !empty($user->active_session_token)) {
            Auth::logout();
            $deviceLabel = $user->active_device_label ?? 'Unknown device';
            $loginTime = $user->session_started_at ? $user->session_started_at->format('d M Y, h:i A') : 'Unknown time';
            throw ValidationException::withMessages([
                'email' => ["This account is already logged in on another device.\n\nActive Device: {$deviceLabel}\nLogged in at: {$loginTime}\n\nPlease log out from that device first or contact your administrator to force logout."],
            ]);
        }

        // Issue new single active token
        $deviceName = $validated['device_name'] ?? 'Doxa App';
        $tokenResult = $user->createToken($deviceName);
        $token = $tokenResult->plainTextToken;
        $tokenId = (string) $tokenResult->accessToken->id;
        $newSessionToken = (string) Str::uuid();

        $userAgent = $request->userAgent() ?? $deviceName;
        $deviceLabel = $deviceName . ' (' . $request->ip() . ')';

        UserLoginSession::create([
            'user_id' => $user->id,
            'session_token' => $newSessionToken,
            'ip_address' => $request->ip(),
            'device_type' => $deviceName,
            'browser' => substr($userAgent, 0, 100),
            'user_agent' => $userAgent,
            'is_active' => true,
            'logged_in_at' => now(),
        ]);

        $user->update([
            'active_session_token' => $tokenId,
            'active_device_label' => $deviceLabel,
            'session_started_at' => now(),
            'force_logout' => false,
            'last_login_at' => now(),
            'login_count' => $user->login_count + 1,
        ]);

        try {
            LoginLog::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device' => $deviceName,
                'status' => 'success',
            ]);
        } catch (\Throwable $e) {}

        return (new UserResource($user->load('tenant')))
            ->additional([
                'token' => $token,
                'membership' => [
                    'is_active' => $user->isMembershipActive(),
                    'expires_at' => $user->membership_expires_at?->toIso8601String(),
                    'days_remaining' => $user->remainingMembershipDays(),
                ]
            ]);
    }

    public function logout()
    {
        $user = auth()->user();
        if ($user) {
            UserLoginSession::where('user_id', $user->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'logged_out_at' => now(),
                    'logout_reason' => 'manual_logout',
                ]);

            $user->update(['active_session_token' => null]);
            $user->currentAccessToken()?->delete();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }
}