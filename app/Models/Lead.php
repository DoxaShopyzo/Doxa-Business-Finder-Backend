<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;

class Lead extends Model {
    use SoftDeletes, BelongsToTenant, HasAuditLog;
    protected $fillable = ['tenant_id', 'created_by', 'assigned_to', 'search_result_id', 'place_reference', 'business_name', 'category', 'location', 'formatted_address', 'phone', 'email', 'website', 'source', 'status', 'priority', 'opportunity_score', 'expected_value', 'probability', 'won_value', 'lost_reason', 'notes', 'custom_fields'];
    protected $casts = ['custom_fields' => 'array', 'expected_value' => 'decimal:2', 'won_value' => 'decimal:2'];
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function searchResult() { return $this->belongsTo(SearchResult::class); }
    public function activities() { return $this->hasMany(LeadActivity::class); }
    public function assignments() { return $this->hasMany(LeadAssignment::class); }
    public function followups() { return $this->hasMany(FollowUp::class); }
    public function quotations() { return $this->hasMany(Quotation::class); }
    public function deal() { return $this->hasOne(Deal::class); }
    public function previews() { return $this->hasMany(GeneratedPreview::class, 'crm_lead_id'); }
}
