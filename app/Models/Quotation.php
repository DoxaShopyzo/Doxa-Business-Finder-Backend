<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;

class Quotation extends Model {
    use SoftDeletes, BelongsToTenant, HasAuditLog;
    protected $fillable = ['tenant_id', 'lead_id', 'created_by', 'quotation_number', 'customer_name', 'customer_email', 'customer_phone', 'subtotal', 'discount_amount', 'discount_type', 'gst_percent', 'gst_amount', 'total', 'currency', 'validity_days', 'valid_until', 'terms', 'notes', 'status', 'sent_at', 'viewed_at', 'accepted_at', 'rejected_at'];
    protected $casts = ['subtotal' => 'decimal:2', 'discount_amount' => 'decimal:2', 'gst_percent' => 'decimal:2', 'gst_amount' => 'decimal:2', 'total' => 'decimal:2', 'valid_until' => 'date', 'sent_at' => 'datetime', 'viewed_at' => 'datetime', 'accepted_at' => 'datetime', 'rejected_at' => 'datetime'];
    public function lead() { return $this->belongsTo(Lead::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function items() { return $this->hasMany(QuotationItem::class); }
    public function deal() { return $this->hasOne(Deal::class); }
}
