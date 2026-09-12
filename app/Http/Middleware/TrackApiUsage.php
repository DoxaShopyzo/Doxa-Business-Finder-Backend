<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class TrackApiUsage
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('start_time', microtime(true));
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $startTime = $request->attributes->get('start_time');
        $duration = microtime(true) - $startTime;

        Log::info('API Usage', [
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'response_time_ms' => round($duration * 1000, 2),
            'tenant_id' => app()->bound('currentTenant') ? app('currentTenant')->id : null,
            'user_id' => $request->user()?->id,
            'ip' => $request->ip()
        ]);
    }
}
