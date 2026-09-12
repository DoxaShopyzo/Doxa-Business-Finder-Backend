<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CreditPackage extends Model {
    protected $fillable = ['name', 'credits', 'price', 'currency', 'bonus_credits', 'validity_days', 'is_active', 'sort_order'];
    protected $casts = ['price' => 'decimal:2', 'is_active' => 'boolean'];
}
