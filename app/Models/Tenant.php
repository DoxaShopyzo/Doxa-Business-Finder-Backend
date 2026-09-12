<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model {
    use SoftDeletes;
    protected $fillable = ['name', 'slug', 'company_name', 'email', 'phone', 'country', 'logo', 'business_category', 'target_locations', 'services_offered', 'status', 'settings'];
    protected $casts = [
        'target_locations' => 'array',
        'services_offered' => 'array',
        'settings' => 'array',
    ];
    public function users() { return $this->hasMany(User::class); }
    public function leads() { return $this->hasMany(Lead::class); }
    public function searches() { return $this->hasMany(Search::class); }
    public function creditWallet() { return $this->hasOne(CreditWallet::class); }
    public function subscriptions() { return $this->hasMany(Subscription::class); }
}
