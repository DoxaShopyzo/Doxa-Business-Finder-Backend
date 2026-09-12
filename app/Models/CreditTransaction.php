<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class CreditTransaction extends Model {
    use BelongsToTenant;
    const UPDATED_AT = null;
    protected $fillable = ['tenant_id', 'user_id', 'wallet_id', 'type', 'credits', 'balance_before', 'balance_after', 'reference_type', 'reference_id', 'description', 'idempotency_key'];
    public function wallet() { return $this->belongsTo(CreditWallet::class, 'wallet_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
