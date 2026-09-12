<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\User;
use App\Exceptions\DuplicateLeadException;

class LeadService
{
    public function createFromSearch(array $searchResult, User $user): Lead
    {
        $tenantId = $user->tenant_id;
        
        $duplicate = $this->checkDuplicate(
            $tenantId, 
            $searchResult['id'] ?? null, 
            $searchResult['nationalPhoneNumber'] ?? null, 
            $searchResult['websiteUri'] ?? null, 
            $searchResult['displayName'] ?? null, 
            $searchResult['formattedAddress'] ?? null
        );

        if ($duplicate) {
            throw new DuplicateLeadException($duplicate);
        }

        return Lead::create([
            'tenant_id' => $tenantId,
            'business_name' => $searchResult['displayName'],
            'place_reference' => $searchResult['id'] ?? null,
            'phone' => $searchResult['nationalPhoneNumber'] ?? null,
            'website' => $searchResult['websiteUri'] ?? null,
            'location' => $searchResult['formattedAddress'] ?? null,
            'category' => $searchResult['primaryType'] ?? null,
            'created_by' => $user->id,
            'status' => 'new',
        ]);
    }

    public function checkDuplicate(int $tenantId, ?string $placeReference, ?string $phone, ?string $website, ?string $businessName, ?string $location): ?Lead
    {
        $query = Lead::where('tenant_id', $tenantId);
        
        if ($placeReference) {
            $lead = (clone $query)->where('place_reference', $placeReference)->first();
            if ($lead) return $lead;
        }
        
        if ($phone) {
            $lead = (clone $query)->where('phone', $phone)->first();
            if ($lead) return $lead;
        }

        if ($website) {
            $lead = (clone $query)->where('website', $website)->first();
            if ($lead) return $lead;
        }

        if ($businessName && $location) {
            $lead = (clone $query)->where('business_name', $businessName)->where('location', $location)->first();
            if ($lead) return $lead;
        }

        return null;
    }

    public function assignLead(Lead $lead, int $userId, ?string $notes, User $assignedBy): void
    {
        $lead->update([
            'assigned_to' => $userId,
            'notes' => $notes ? $lead->notes . "\n" . $notes : $lead->notes
        ]);
    }
}
