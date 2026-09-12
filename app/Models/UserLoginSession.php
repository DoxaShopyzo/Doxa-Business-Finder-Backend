<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginSession extends Model
{
    protected $fillable = [
        'user_id',
        'session_token',
        'ip_address',
        'device_type',
        'browser',
        'user_agent',
        'is_active',
        'logged_in_at',
        'logged_out_at',
        'logout_reason',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'logged_in_at' => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}