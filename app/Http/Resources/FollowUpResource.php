<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FollowUpResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lead_id' => $this->lead_id,
            'followup_date' => $this->followup_date,
            'followup_time' => $this->followup_time,
            'type' => $this->type,
            'status' => $this->status,
            'notes' => $this->notes,
            'outcome' => $this->outcome,
            'created_at' => $this->created_at,
            'lead' => [
                'id' => $this->lead?->id,
                'business_name' => $this->lead?->business_name,
            ]
        ];
    }
}
