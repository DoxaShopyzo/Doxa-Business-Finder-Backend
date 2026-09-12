<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\TenantAccessDeniedException;
use Spatie\Permission\PermissionRegistrar;

class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Super admin can bypass tenant check or impersonate via header
        if ($user->is_super_admin) {
            if ($request->hasHeader('X-Tenant-ID')) {
                $tenantId = (int) $request->header('X-Tenant-ID');
                $tenant = \App\Models\Tenant::find($tenantId);
                if ($tenant) {
                    app()->instance('currentTenant', $tenant);
                    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
                }
            }
            return $next($request);
        }

        $tenant = $user->tenant;

        if (!$tenant) {
            throw new TenantAccessDeniedException('Tenant access denied. No valid tenant found.');
        }

        if ($tenant->status === 'suspended') {
            throw new TenantAccessDeniedException('Your account has been suspended. Contact support.');
        }

        app()->instance('currentTenant', $tenant);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

        return $next($request);
    }
}
