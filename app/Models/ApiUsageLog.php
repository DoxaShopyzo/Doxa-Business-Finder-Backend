<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ApiUsageLog extends Model {
    use BelongsToTenant;
    const UPDATED_AT = null;
    protected $fillable = ['tenant_id', 'user_id', 'search_id', 'provider', 'endpoint', 'request_data', 'response_status', 'response_time_ms', 'estimated_cost', 'credits_charged'];
    protected $casts = ['request_data' => 'array', 'estimated_cost' => 'decimal:4'];
    public function user() { return $this->belongsTo(User::class); }
    public function search() { return $this->belongsTo(Search::class); }
}
