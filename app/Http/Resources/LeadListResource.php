<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_name' => $this->business_name,
            'category' => $this->category,
            'status' => $this->status,
            'opportunity_level' => $this->opportunity_level,
            'previews' => $this->previews ?? [],
            'preview_status' => $this->previews?->sortByDesc('created_at')->first()?->status ?? 'none',
            'created_at' => $this->created_at,
        ];
    }
}
