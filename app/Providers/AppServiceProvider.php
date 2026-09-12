<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use App\Listeners\LogAuthEvents;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(Router $router): void
    {
        // Fix for MySQL key length
        Schema::defaultStringLength(191);

        // Middleware aliases
        $router->aliasMiddleware('tenant.access', \App\Http\Middleware\EnsureTenantAccess::class);
        $router->aliasMiddleware('check.subscription', \App\Http\Middleware\CheckSubscription::class);
        $router->aliasMiddleware('check.credits', \App\Http\Middleware\CheckCredits::class);
        $router->aliasMiddleware('is_super_admin', \App\Http\Middleware\IsSuperAdmin::class);

        // Super Admin bypasses all authorization
        Gate::before(function ($user, $ability) {
            return $user->is_super_admin ? true : null;
        });

        // API rate limiting
        RateLimiter::for('api', function (Request $request) {
            $limit = config('doxa.security.api_rate_limit', 60);
            return Limit::perMinute($limit)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            $limit = config('doxa.security.search_rate_limit', 30);
            return Limit::perMinute($limit)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip());
        });

        // Scheduled commands
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('doxa:mark-overdue-followups')->dailyAt('00:05');
            $schedule->command('doxa:send-expiry-reminders')->dailyAt('08:00');
            $schedule->command('doxa:send-low-credit-alerts')->dailyAt('09:00');
            $schedule->command('doxa:cleanup-search-results')->dailyAt('02:00');
        });
    }
}