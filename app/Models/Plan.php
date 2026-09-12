<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model {
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'duration_days',
        'credits_included',
        'max_users',
        'max_searches_per_day',
        'max_data_units_total',
        'reset_daily_limit_on_period',
        'features',
        'is_active',
        'is_free',
        'sort_order'
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'max_data_units_total' => 'integer',
        'reset_daily_limit_on_period' => 'boolean',
        'is_active' => 'boolean',
        'is_free' => 'boolean'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}