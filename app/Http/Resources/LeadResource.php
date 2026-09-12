<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_name' => $this->business_name,
            'category' => $this->category,
            'location' => $this->location,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'source' => $this->source,
            'status' => $this->status,
            'priority' => $this->priority ?? 'medium',
            'opportunity_score' => $this->opportunity_score ?? 0,
            'opportunity_level' => $this->opportunity_level,
            'expected_value' => $this->expected_value,
            'notes' => $this->notes,
            'formatted_address' => $this->formatted_address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'activities' => $this->whenLoaded('activities'),
            'followups' => $this->whenLoaded('followups'),
            'quotations' => $this->whenLoaded('quotations'),
        ];
    }
}
