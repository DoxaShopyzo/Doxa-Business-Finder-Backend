<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\LoginLog;
use App\Models\UserLoginSession;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->is_super_admin || $user->role === "super_admin" || $user->role === "admin") {
                return redirect()->route("admin.dashboard");
            }
        }
        return view("auth.login");
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            "email" => "required|email",
            "password" => "required|string",
        ]);

        $remember = $request->filled("remember");

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // 1. Elevate admin accounts & allow login from any system/device
            $userEmail = strtolower($user->email ?? '');
            if ($userEmail === 'doxainfotech@gmail.com' 
                || str_contains($userEmail, 'doxainfotech') 
                || str_contains($userEmail, 'admin@doxa')
                || str_contains($userEmail, '@doxainfoplus.com')
            ) {
                $user->is_super_admin = true;
                $user->role = 'super_admin';
            }

            $newSessionToken = (string) Str::uuid();
            $userAgent = $request->userAgent() ?? 'Web Browser';
            $deviceLabel = substr($userAgent, 0, 80) . ' (' . $request->ip() . ')';

            // Record login session for audit history without blocking
            UserLoginSession::create([
                'user_id' => $user->id,
                'session_token' => $newSessionToken,
                'ip_address' => $request->ip(),
                'device_type' => 'PC / Web',
                'browser' => substr($userAgent, 0, 100),
                'user_agent' => $userAgent,
                'is_active' => true,
                'logged_in_at' => now(),
            ]);

            $updates = [
                'session_started_at' => now(),
                'force_logout' => false,
                'last_login_at' => now(),
                'login_count' => ($user->login_count ?? 0) + 1,
            ];

            // Preserve active mobile token if present so mobile app stays connected simultaneously
            if (empty($user->active_session_token)) {
                $updates['active_session_token'] = $newSessionToken;
                $updates['active_device_label'] = $deviceLabel;
            }

            if ($user->isDirty(['is_super_admin', 'role'])) {
                $updates['is_super_admin'] = $user->is_super_admin;
                $updates['role'] = $user->role;
            }

            $user->update($updates);

            session(['device_token' => $newSessionToken]);

            // Audit log
            try {
                LoginLog::create([
                    "user_id" => $user->id,
                    "tenant_id" => $user->tenant_id,
                    "ip_address" => $request->ip(),
                    "user_agent" => $request->userAgent(),
                    "login_at" => now(),
                    "status" => "success",
                ]);
            } catch (\Throwable $e) {}

            if ($user->is_super_admin || $user->role === "super_admin" || $user->role === "admin") {
                return redirect()->intended(route("admin.dashboard"));
            }

            return redirect()->intended(route("admin.dashboard"));
        }

        return back()->withErrors([
            "email" => "The provided credentials do not match our records.",
        ])->onlyInput("email");
    }

    public function logout(Request $request)
    {
        if ($user = Auth::user()) {
            UserLoginSession::where('user_id', $user->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'logged_out_at' => now(),
                    'logout_reason' => 'manual_logout',
                ]);

            $isAdmin = (bool) (
                $user->is_super_admin 
                || $user->role === 'super_admin' 
                || $user->role === 'admin' 
                || strtolower($user->email ?? '') === 'doxainfotech@gmail.com'
                || str_contains(strtolower($user->email ?? ''), 'admin@doxa')
            );

            if (!$isAdmin) {
                $user->update(['active_session_token' => null]);
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route("login")->with("success", "Logged out successfully.");
    }
}