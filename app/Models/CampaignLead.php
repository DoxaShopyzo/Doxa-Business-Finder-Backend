<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignLead extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'campaign_leads';

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'place_reference',
        'business_name',
        'category',
        'location_address',
        'phone',
        'website',
        'google_rating',
        'google_review_count',
        'business_status',
        'latitude',
        'longitude',
        'google_purged_at',
        'prefilter_passed',
        'prefilter_score',
        'prefilter_reasons',
        'ai_score',
        'ai_classification',
        'potential_requirement',
        'confidence_level',
        'qualification_reason',
        'sales_priority',
        'suggested_sales_question',
        'score_breakdown',
        'verification_status',
        'verified_by',
        'verified_at',
        'human_score',
        'human_classification',
        'human_remarks',
        'dnd_status',
        'dnd_checked_at',
        'dnd_check_reference',
        'consent_status',
        'consent_timestamp',
        'consent_channel',
    ];

    protected $casts = [
        'prefilter_passed' => 'boolean',
        'prefilter_score' => 'integer',
        'prefilter_reasons' => 'array',
        'ai_score' => 'integer',
        'human_score' => 'integer',
        'score_breakdown' => 'array',
        'google_rating' => 'decimal:1',
        'google_review_count' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'google_purged_at' => 'datetime',
        'verified_at' => 'datetime',
        'dnd_checked_at' => 'datetime',
        'consent_timestamp' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(LeadCampaign::class, 'campaign_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function previews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GeneratedPreview::class, 'campaign_lead_id');
    }
}
