<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadCampaign extends Model
{
    use SoftDeletes, BelongsToTenant, HasAuditLog;

    protected $table = 'lead_campaigns';

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'client_name',
        'product_service',
        'target_categories',
        'target_location',
        'target_customer_type',
        'minimum_business_size',
        'preferred_characteristics',
        'required_lead_count',
        'additional_requirements',
        'scoring_weights',
        'status',
        'total_discovered',
        'total_prefiltered',
        'total_ai_analyzed',
        'total_approved',
        'total_rejected',
        'credits_spent',
    ];

    protected $casts = [
        'target_categories' => 'array',
        'scoring_weights' => 'array',
        'required_lead_count' => 'integer',
        'total_discovered' => 'integer',
        'total_prefiltered' => 'integer',
        'total_ai_analyzed' => 'integer',
        'total_approved' => 'integer',
        'total_rejected' => 'integer',
        'credits_spent' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(CampaignLead::class, 'campaign_id');
    }

    public function approvedLeads(): HasMany
    {
        return $this->hasMany(CampaignLead::class, 'campaign_id')->where('verification_status', 'approved');
    }

    public function manualReviewLeads(): HasMany
    {
        return $this->hasMany(CampaignLead::class, 'campaign_id')->where('verification_status', 'needs_manual_review');
    }

    public function pendingLeads(): HasMany
    {
        return $this->hasMany(CampaignLead::class, 'campaign_id')->where('verification_status', 'pending');
    }
}
