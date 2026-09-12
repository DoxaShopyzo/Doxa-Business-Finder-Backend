<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;

class FollowUp extends Model {
    use BelongsToTenant, HasAuditLog;
    protected $table = 'followups';
    protected $fillable = ['tenant_id', 'lead_id', 'user_id', 'followup_date', 'followup_time', 'type', 'status', 'notes', 'outcome', 'completed_at'];
    protected $casts = ['followup_date' => 'date', 'completed_at' => 'datetime'];
    public function lead() { return $this->belongsTo(Lead::class); }
    public function user() { return $this->belongsTo(User::class); }
}
