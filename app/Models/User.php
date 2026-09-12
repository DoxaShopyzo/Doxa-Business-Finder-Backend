<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\BelongsToTenant;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, BelongsToTenant, HasRoles;

    protected string $guard_name = 'sanctum';

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'phone',
        'avatar',
        'status',
        'last_login_at',
        'login_count',
        'onboarding_completed',
        'is_super_admin',
        'settings',
        'active_session_token',
        'active_device_label',
        'session_started_at',
        'membership_expires_at',
        'force_logout',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'onboarding_completed' => 'boolean',
        'is_super_admin' => 'boolean',
        'settings' => 'array',
        'session_started_at' => 'datetime',
        'membership_expires_at' => 'datetime',
        'force_logout' => 'boolean',
    ];

    /* ── Relationships ── */

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function activeLoginSession()
    {
        return $this->hasOne(UserLoginSession::class)->where('is_active', true);
    }

    public function loginSessions()
    {
        return $this->hasMany(UserLoginSession::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'tenant_id', 'tenant_id');
    }

    public function getActiveSubscriptionAttribute()
    {
        // Primary lookup: tenant subscription (always present in all DB schemas)
        if ($this->tenant_id) {
            $sub = Subscription::withoutGlobalScopes()
                ->where('tenant_id', $this->tenant_id)
                ->whereIn('status', ['active', 'trial'])
                ->where('expires_at', '>', now())
                ->latest('starts_at')
                ->first();

            if ($sub) {
                return $sub;
            }
        }

        // Fallback: direct user_id lookup (column may not exist on older cPanel schemas)
        try {
            return Subscription::withoutGlobalScopes()
                ->where('user_id', $this->id)
                ->whereIn('status', ['active', 'trial'])
                ->where('expires_at', '>', now())
                ->latest('starts_at')
                ->first();
        } catch (\Illuminate\Database\QueryException $e) {
            // user_id column doesn't exist on production — gracefully return null
            return null;
        }
    }

    public function isMembershipActive(): bool
    {
        if ($this->is_super_admin) {
            return true;
        }
        if ($this->membership_expires_at) {
            return $this->membership_expires_at->isFuture();
        }
        $activeSub = $this->active_subscription;
        if ($activeSub !== null && $activeSub->expires_at) {
            return $activeSub->expires_at->isFuture();
        }
        // Active or trial tenant is always active
        if ($this->tenant && in_array($this->tenant->status, ['active', 'trial'])) {
            return true;
        }
        return false;
    }

    public function remainingMembershipDays(): int
    {
        if ($this->is_super_admin) {
            return 9999;
        }
        if ($this->membership_expires_at) {
            return max(0, (int) now()->diffInDays($this->membership_expires_at, false));
        }
        $sub = $this->active_subscription;
        if ($sub && $sub->expires_at) {
            return max(0, (int) now()->diffInDays($sub->expires_at, false));
        }
        return 0;
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    public function assignedLeads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function searches()
    {
        return $this->hasMany(Search::class);
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function followups()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function assignments()
    {
        return $this->hasMany(LeadAssignment::class, 'assigned_to');
    }

    public function creditWallet()
    {
        return $this->hasOne(CreditWallet::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }

    /* ── Role Checks ── */

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function isAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    public function isTenantOwner(): bool
    {
        return $this->hasRole('tenant_owner');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isSalesExecutive(): bool
    {
        return $this->hasRole('sales_executive');
    }

    public function isTelecaller(): bool
    {
        return $this->hasRole('telecaller');
    }

    public function hasActiveSubscription(): bool
    {
        return $this->isMembershipActive();
    }
}