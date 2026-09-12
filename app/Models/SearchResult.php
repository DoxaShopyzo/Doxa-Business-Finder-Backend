<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchResult extends Model {
    use BelongsToTenant;

    protected $fillable = [
        'search_id',
        'subscription_id',
        'tenant_id',
        'place_reference',
        'business_name',
        'category',
        'formatted_address',
        'latitude',
        'longitude',
        'phone',
        'website',
        'rating',
        'review_count',
        'opportunity_score',
        'source',
        'raw_data',
        'expires_at'
    ];

    protected $casts = [
        'raw_data' => 'array',
        'expires_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'rating' => 'decimal:1'
    ];

    public function search(): BelongsTo
    {
        return $this->belongsTo(Search::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}