<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * TenantScope - Automatically filters all queries by the current tenant.
 *
 * This scope is the primary mechanism for multi-tenant data isolation.
 * It ensures that every database query on tenant-scoped models is
 * automatically filtered by the authenticated user's tenant_id.
 */
class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->bound('currentTenant')) {
            $tenant = app('currentTenant');
            $builder->where($model->getTable() . '.tenant_id', $tenant->id);
        }
    }

    /**
     * Extend the query builder with the needed functions.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenantScope', function (Builder $builder) {
            return $builder->withoutGlobalScope(static::class);
        });

        $builder->macro('forTenant', function (Builder $builder, int $tenantId) {
            return $builder->withoutGlobalScope(static::class)
                ->where('tenant_id', $tenantId);
        });
    }
}
