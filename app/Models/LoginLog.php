<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model {
    const UPDATED_AT = null;
    protected $fillable = ['user_id', 'ip_address', 'user_agent', 'device', 'location', 'status'];
    public function user() { return $this->belongsTo(User::class); }
}
