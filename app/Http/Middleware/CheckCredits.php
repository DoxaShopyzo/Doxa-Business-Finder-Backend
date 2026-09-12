<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\InsufficientCreditsException;
use App\Services\CreditService;

class CheckCredits
{
    public function __construct(protected CreditService $creditService) {}

    public function handle(Request $request, Closure $next, string $action): Response
    {
        $user = $request->user();
        if (!$user || $user->hasRole('super_admin')) {
            return $next($request);
        }

        $tenant = app('currentTenant');
        $cost = config("doxa.credits.{$action}_cost", 1);
        
        $balance = $this->creditService->getBalance($tenant->id);

        if ($balance < $cost) {
            throw new InsufficientCreditsException("Insufficient credits for this action. Required: {$cost}, Available: {$balance}");
        }

        // Note: Actual deduction happens in the controller/service after successful action
        return $next($request);
    }
}
