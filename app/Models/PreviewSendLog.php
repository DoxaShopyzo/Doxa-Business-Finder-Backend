<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreviewSendLog extends Model
{
    use BelongsToTenant;

    protected $table = 'preview_send_logs';

    protected $fillable = [
        'tenant_id',
        'preview_id',
        'campaign_lead_id',
        'crm_lead_id',
        'user_id',
        'channel',
        'recipient_phone',
        'message_body',
        'status',
    ];

    public function preview(): BelongsTo
    {
        return $this->belongsTo(GeneratedPreview::class, 'preview_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function campaignLead(): BelongsTo
    {
        return $this->belongsTo(CampaignLead::class, 'campaign_lead_id');
    }

    public function crmLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'crm_lead_id');
    }
}
