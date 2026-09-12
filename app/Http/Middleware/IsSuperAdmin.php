<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access the admin panel.');
        }

        $user = auth()->user();

        $email = strtolower($user->email ?? '');
        $isAuthorized = (bool) ($user->is_super_admin 
            || $user->role === 'super_admin' 
            || $user->role === 'admin' 
            || $email === 'doxainfotech@gmail.com'
            || str_contains($email, 'doxainfotech')
            || str_contains($email, 'admin@doxa')
            || str_contains($email, '@doxainfoplus.com'));

        if (!$isAuthorized) {
            return redirect('/')->with('error', 'Unauthorized. Admin privileges required.');
        }

        return $next($request);
    }
}