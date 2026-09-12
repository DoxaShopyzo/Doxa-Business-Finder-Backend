<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model {
    protected $fillable = ['quotation_id', 'service_name', 'description', 'quantity', 'unit_price', 'discount', 'total', 'sort_order'];
    protected $casts = ['unit_price' => 'decimal:2', 'discount' => 'decimal:2', 'total' => 'decimal:2'];
    public function quotation() { return $this->belongsTo(Quotation::class); }
}
