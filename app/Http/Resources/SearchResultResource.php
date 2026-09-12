<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'place_id' => $this->place_reference,
            'name' => $this->business_name,
            'business_name' => $this->business_name,
            'category' => $this->category,
            'address' => $this->formatted_address,
            'phone' => $this->phone,
            'website' => $this->website,
            'rating' => $this->rating,
            'review_count' => $this->review_count,
            'opportunity_score' => $this->opportunity_score,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'source' => $this->source,
            'created_at' => $this->created_at,
        ];
    }
}
