<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Deal extends Model {
    use BelongsToTenant;
    protected $fillable = ['tenant_id', 'lead_id', 'quotation_id', 'deal_value', 'service', 'payment_status', 'closing_date', 'notes', 'created_by'];
    protected $casts = ['deal_value' => 'decimal:2', 'closing_date' => 'date'];
    public function lead() { return $this->belongsTo(Lead::class); }
    public function quotation() { return $this->belongsTo(Quotation::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}
