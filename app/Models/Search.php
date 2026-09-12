<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;

class Search extends Model {
    use BelongsToTenant, HasAuditLog;
    protected $fillable = ['tenant_id', 'user_id', 'keyword', 'category', 'location', 'latitude', 'longitude', 'radius', 'filters', 'result_count', 'credits_used', 'status', 'api_provider', 'api_cost', 'response_time_ms'];
    protected $casts = ['filters' => 'array', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'api_cost' => 'decimal:4'];
    public function user() { return $this->belongsTo(User::class); }
    public function searchResults() { return $this->hasMany(SearchResult::class); }
    public function results() { return $this->hasMany(SearchResult::class); }
}
