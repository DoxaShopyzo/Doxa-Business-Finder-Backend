<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeneratedPreview extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'generated_previews';

    protected $fillable = [
        'tenant_id',
        'campaign_lead_id',
        'crm_lead_id',
        'template_id',
        'preview_type',
        'token',
        'preview_code',
        'status',
        'version',
        'share_count',
        'business_name',
        'category',
        'logo_url',
        'theme_json',
        'custom_content',
        'asset_config',
        'view_count',
        'last_viewed_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'theme_json' => 'array',
        'custom_content' => 'array',
        'asset_config' => 'array',
        'view_count' => 'integer',
        'share_count' => 'integer',
        'last_viewed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(PreviewTemplate::class, 'template_id');
    }

    public function campaignLead(): BelongsTo
    {
        return $this->belongsTo(CampaignLead::class, 'campaign_lead_id');
    }

    public function crmLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'crm_lead_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sendLogs(): HasMany
    {
        return $this->hasMany(PreviewSendLog::class, 'preview_id');
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/preview/' . $this->token);
    }
}
