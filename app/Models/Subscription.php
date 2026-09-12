<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model {
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'plan_id',
        'duration_days',
        'data_limit_total',
        'data_used_total',
        'last_data_pull_at',
        'status',
        'starts_at',
        'expires_at',
        'cancelled_at',
        'payment_id',
        'auto_renew'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_data_pull_at' => 'datetime',
        'auto_renew' => 'boolean',
        'duration_days' => 'integer',
        'data_limit_total' => 'integer',
        'data_used_total' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function searchResults(): HasMany
    {
        return $this->hasMany(SearchResult::class);
    }

    public function hasDataQuotaRemaining(): bool
    {
        if ($this->data_limit_total === null) {
            return true;
        }
        return $this->data_used_total < $this->data_limit_total;
    }

    public function remainingDataUnits(): ?int
    {
        if ($this->data_limit_total === null) {
            return null;
        }
        return max(0, $this->data_limit_total - $this->data_used_total);
    }
}