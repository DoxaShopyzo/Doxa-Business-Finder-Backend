<?php
namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant {
    protected static function bootBelongsToTenant() {
        static::addGlobalScope('tenant', function (Builder $builder) {
            // Prevent infinite recursion when resolving User during authentication
            if ($builder->getModel() instanceof \App\Models\User) {
                return;
            }

            if (Auth::hasUser()) {
                $user = Auth::user();
                if ($user && $user->tenant_id && !$user->is_super_admin) {
                    $builder->where($builder->getModel()->getTable() . '.tenant_id', $user->tenant_id);
                }
            }
        });

        static::creating(function ($model) {
            if (Auth::hasUser()) {
                $user = Auth::user();
                if ($user && $user->tenant_id && !$model->tenant_id) {
                    $model->tenant_id = $user->tenant_id;
                }
            }
        });
    }

    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }
}