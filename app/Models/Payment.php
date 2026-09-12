<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Payment extends Model {
    use BelongsToTenant;
    protected $fillable = ['tenant_id', 'user_id', 'gateway', 'gateway_order_id', 'gateway_payment_id', 'gateway_signature', 'amount', 'currency', 'status', 'type', 'reference_type', 'reference_id', 'metadata', 'paid_at', 'refunded_at'];
    protected $casts = ['amount' => 'decimal:2', 'metadata' => 'array', 'paid_at' => 'datetime', 'refunded_at' => 'datetime'];
    public function user() { return $this->belongsTo(User::class); }
}
