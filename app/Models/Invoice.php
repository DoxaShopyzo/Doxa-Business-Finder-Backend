<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Invoice extends Model {
    use BelongsToTenant;
    protected $fillable = ['tenant_id', 'payment_id', 'invoice_number', 'customer_name', 'customer_email', 'customer_phone', 'customer_address', 'subtotal', 'gst_percent', 'gst_amount', 'total', 'currency', 'status', 'issued_at'];
    protected $casts = ['subtotal' => 'decimal:2', 'gst_percent' => 'decimal:2', 'gst_amount' => 'decimal:2', 'total' => 'decimal:2', 'issued_at' => 'datetime'];
    public function payment() { return $this->belongsTo(Payment::class); }
}
