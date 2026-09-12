<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\UserLoginSession;

class CheckActiveDeviceSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Super admin, admin role, master email, or admin panel routes bypass device lock and membership checks
        $email = strtolower($user->email ?? '');
        $isAdminUser = (bool) (
            $user->is_super_admin 
            || $user->role === 'super_admin' 
            || $user->role === 'admin' 
            || $email === 'doxainfotech@gmail.com'
            || str_contains($email, 'doxainfotech')
            || str_contains($email, 'admin@doxa')
            || str_contains($email, '@doxainfoplus.com')
        );

        if ($isAdminUser || $request->is('admin*') || $request->is('login*') || $request->is('logout*')) {
            return $next($request);
        }

        // Determine auth channel by presence of a Sanctum PersonalAccessToken,
        // NOT by Accept header. This prevents AJAX requests from web sessions
        // (which send Accept: application/json) from incorrectly entering the
        // API branch and bypassing the web session device-lock check.
        $usingApiToken = $user->currentAccessToken() !== null;

        // Helper: should this request get a JSON response?
        // True for API token requests, OR web AJAX requests (Accept: application/json)
        $wantsJson = $usingApiToken || $request->expectsJson();

        // 1. Membership Expiry Check
        if (!$user->isMembershipActive() && (!$user->tenant || !in_array($user->tenant->status, ['active', 'trial']))) {
            UserLoginSession::where('user_id', $user->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'logged_out_at' => now(),
                    'logout_reason' => 'membership_expired',
                ]);

            if ($usingApiToken) {
                try {
                    $user->currentAccessToken()?->delete();
                } catch (\Throwable $e) {}
            }

            if ($wantsJson) {
                return response()->json([
                    'error' => 'membership_expired',
                    'message' => 'Your membership has expired. Please contact admin or renew to continue.',
                ], 403);
            }

            Auth::logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Your membership has expired. Please renew to continue.',
            ]);
        }

        // 2. Admin Force Logout Check
        if ($user->force_logout) {
            $user->update([
                'force_logout' => false,
                'active_session_token' => null,
            ]);

            UserLoginSession::where('user_id', $user->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'logged_out_at' => now(),
                    'logout_reason' => 'admin_forced',
                ]);

            if ($usingApiToken) {
                try {
                    $user->currentAccessToken()?->delete();
                } catch (\Throwable $e) {}
            }

            if ($wantsJson) {
                return response()->json([
                    'error' => 'session_terminated',
                    'message' => 'Your session was terminated by an administrator.',
                ], 401);
            }

            Auth::logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Your session was terminated by an administrator.',
            ]);
        }

        // 3. Single Active Device Lock Check
        if ($usingApiToken) {
            // API (Sanctum) Device check — compare token ID
            $currentTokenId = (string) $user->currentAccessToken()->id;
            if ($user->active_session_token && $currentTokenId && $user->active_session_token !== $currentTokenId) {
                try {
                    $user->currentAccessToken()->delete();
                } catch (\Throwable $e) {}

                return response()->json([
                    'error' => 'device_conflict',
                    'message' => 'You have been logged out because your account was accessed from another device.',
                ], 401);
            }
        } else {
            // Web Session Device check — compare session('device_token')
            // This now correctly handles AJAX requests from web sessions too
            if ($request->hasSession()) {
                $sessionToken = $request->session()->get('device_token');
                if ($user->active_session_token && $sessionToken && $sessionToken !== $user->active_session_token) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    if ($wantsJson) {
                        return response()->json([
                            'error' => 'device_conflict',
                            'message' => 'You have been logged out because your account was accessed from another device.',
                        ], 401);
                    }

                    return redirect()->route('login')->withErrors([
                        'email' => 'You have been logged out because your account was accessed from another device.',
                    ]);
                }
            }
        }

        return $next($request);
    }
}