<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class CreditWallet extends Model {
    use BelongsToTenant;
    protected $fillable = ['tenant_id', 'user_id', 'balance', 'total_earned', 'total_spent'];
    public function user() { return $this->belongsTo(User::class); }
    public function transactions() { return $this->hasMany(CreditTransaction::class, 'wallet_id'); }
}
