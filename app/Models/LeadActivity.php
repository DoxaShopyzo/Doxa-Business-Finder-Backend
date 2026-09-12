<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class LeadActivity extends Model {
    use BelongsToTenant;
    const UPDATED_AT = null;
    protected $fillable = ['tenant_id', 'lead_id', 'user_id', 'type', 'description', 'metadata'];
    protected $casts = ['metadata' => 'array'];
    public function lead() { return $this->belongsTo(Lead::class); }
    public function user() { return $this->belongsTo(User::class); }
}
