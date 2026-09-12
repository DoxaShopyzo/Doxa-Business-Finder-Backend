<?php

namespace App\Services;

class OpportunityScoreService
{
    public function calculate(array $businessData): array
    {
        $weights = config('doxa.opportunity_score');
        
        $hasWebsite = !empty($businessData['websiteUri']);
        $rating = $businessData['rating'] ?? null;
        $reviewCount = $businessData['userRatingCount'] ?? 0;
        $category = $businessData['primaryType'] ?? null;
        
        $scores = [
            'website_opportunity' => $this->calculateWebsiteScore($hasWebsite) * ($weights['website_opportunity'] / 100),
            'digital_presence_gap' => $this->calculateDigitalPresenceScore($hasWebsite, $rating) * ($weights['digital_presence_gap'] / 100),
            'business_relevance' => $this->calculateBusinessRelevanceScore($category, []) * ($weights['business_relevance'] / 100),
            'business_activity' => $this->calculateBusinessActivityScore($rating, $reviewCount) * ($weights['business_activity'] / 100),
            'contact_availability' => $this->calculateContactAvailabilityScore($businessData['nationalPhoneNumber'] ?? null, $businessData['websiteUri'] ?? null, null) * ($weights['contact_availability'] / 100),
            'growth_potential' => $this->calculateGrowthPotentialScore($rating, $reviewCount) * ($weights['growth_potential'] / 100),
        ];

        $totalScore = (int) round(array_sum($scores));
        
        if ($totalScore >= 70) $level = 'HIGH';
        elseif ($totalScore >= 40) $level = 'MEDIUM';
        else $level = 'LOW';

        return [
            'score' => $totalScore,
            'level' => $level,
            'breakdown' => $scores
        ];
    }

    private function calculateWebsiteScore(bool $hasWebsite): int
    {
        return $hasWebsite ? 20 : 100;
    }

    private function calculateDigitalPresenceScore(bool $hasWebsite, ?float $rating): int
    {
        if (!$hasWebsite && !$rating) return 100;
        if (!$hasWebsite || !$rating) return 70;
        return 30;
    }

    private function calculateBusinessRelevanceScore(?string $category, array $targetServices): int
    {
        return 80; // Placeholder logic
    }

    private function calculateBusinessActivityScore(?float $rating, ?int $reviewCount): int
    {
        if ($reviewCount > 100) return 100;
        if ($reviewCount > 20) return 60;
        return 30;
    }

    private function calculateContactAvailabilityScore(?string $phone, ?string $website, ?string $email): int
    {
        $score = 0;
        if ($phone) $score += 50;
        if ($email) $score += 30;
        if ($website) $score += 20;
        return $score;
    }

    private function calculateGrowthPotentialScore(?float $rating, ?int $reviewCount): int
    {
        return ($rating && $rating > 4.0) ? 80 : 40;
    }

    /**
     * Convenience method used by controllers — accepts normalized result, returns integer score
     */
    public function calculateScore(array $result): int
    {
        // Map normalized result keys to the format calculate() expects
        $businessData = [
            'websiteUri' => $result['website'] ?? null,
            'rating' => $result['rating'] ?? null,
            'userRatingCount' => $result['review_count'] ?? $result['userRatingCount'] ?? 0,
            'primaryType' => $result['category'] ?? $result['primaryType'] ?? null,
            'nationalPhoneNumber' => $result['phone'] ?? $result['nationalPhoneNumber'] ?? null,
        ];

        $scoreData = $this->calculate($businessData);
        return $scoreData['score'];
    }

    /**
     * Full score with breakdown
     */
    public function calculateScoreDetailed(array $result): array
    {
        $businessData = [
            'websiteUri' => $result['website'] ?? null,
            'rating' => $result['rating'] ?? null,
            'userRatingCount' => $result['review_count'] ?? 0,
            'primaryType' => $result['category'] ?? null,
            'nationalPhoneNumber' => $result['phone'] ?? null,
        ];

        return $this->calculate($businessData);
    }
}
