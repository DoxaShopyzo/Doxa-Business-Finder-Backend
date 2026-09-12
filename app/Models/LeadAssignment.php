<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class LeadAssignment extends Model {
    use BelongsToTenant;
    const UPDATED_AT = null;
    protected $fillable = ['tenant_id', 'lead_id', 'assigned_by', 'assigned_to', 'notes'];
    public function lead() { return $this->belongsTo(Lead::class); }
    public function assigner() { return $this->belongsTo(User::class, 'assigned_by'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
}
