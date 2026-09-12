<?php

namespace App\Services;

class ServiceRecommendationService
{
    public function recommend(string $category, array $businessData): array
    {
        $recommendations = [];
        $services = config('doxa.services');
        
        $hasWebsite = !empty($businessData['websiteUri']);
        
        if (!$hasWebsite) {
            $recommendations[] = ['service' => 'website', 'relevance' => 'high', 'name' => $services['website']];
        }

        if (in_array(strtolower($category), ['restaurant', 'cafe', 'food'])) {
            $recommendations[] = ['service' => 'digital_marketing', 'relevance' => 'high', 'name' => $services['digital_marketing']];
        }

        if (in_array(strtolower($category), ['retail', 'store', 'clothing'])) {
            $recommendations[] = ['service' => 'ecommerce', 'relevance' => 'high', 'name' => $services['ecommerce']];
        }

        return $recommendations;
    }
}
