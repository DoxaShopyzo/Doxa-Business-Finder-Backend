<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\SubscriptionExpiredException;
use App\Services\SubscriptionService;

class CheckSubscription
{
    public function __construct(protected SubscriptionService $subscriptionService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || $user->is_super_admin || $user->hasRole('super_admin') || $user->isMembershipActive()) {
            return $next($request);
        }

        $tenant = app('currentTenant') ?? $user->tenant;
        if ($tenant && in_array($tenant->status, ['active', 'trial'])) {
            return $next($request);
        }

        // Allow if tenant wallet has sufficient credits for searching
        $wallet = \App\Models\CreditWallet::where('tenant_id', $user->tenant_id)->first();
        if ($wallet && $wallet->balance >= config('doxa.credits.search_cost', 5)) {
            return $next($request);
        }

        if (!$tenant || !$this->subscriptionService->isActive($tenant->id)) {
            throw new SubscriptionExpiredException('Your subscription has expired. Please renew to continue.');
        }

        return $next($request);
    }
}
