<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lead_id' => $this->lead_id,
            'customer_name' => $this->customer_name,
            'items' => $this->items,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'gst_percent' => $this->gst_percent,
            'total' => $this->total,
            'terms' => $this->terms,
            'validity_days' => $this->validity_days,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
