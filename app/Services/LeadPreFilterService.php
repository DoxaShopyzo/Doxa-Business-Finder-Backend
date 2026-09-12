<?php

namespace App\Services;

class LeadPreFilterService
{
    /**
     * Stage A: Deterministic, rule-based pre-filter (Zero LLM cost)
     * Filters raw discovery prospects based on operational status, category matching,
     * location validity, and negative keyword exclusions.
     */
    public function evaluate(array $prospect, array $campaign): array
    {
        $reasons = [];
        $score = 50; // Base neutrality score

        $name = strtolower($prospect['name'] ?? ($prospect['business_name'] ?? ''));
        $category = strtolower($prospect['category'] ?? ($prospect['primaryType'] ?? ''));
        $address = strtolower($prospect['address'] ?? ($prospect['formatted_address'] ?? ''));
        $status = strtoupper($prospect['business_status'] ?? ($prospect['businessStatus'] ?? 'OPERATIONAL'));

        // Rule 1: Operational Status
        if ($status !== 'OPERATIONAL' && !empty($status)) {
            return [
                'passed' => false,
                'score' => 0,
                'reasons' => ["Disqualified: Business status is {$status}"]
            ];
        }

        // Rule 2: Negative Keyword / Additional Requirements Check
        $additionalReqs = strtolower($campaign['additional_requirements'] ?? '');
        if (!empty($additionalReqs)) {
            $negativeWords = ['exclude', 'no', 'avoid', 'not', 'except'];
            foreach ($negativeWords as $neg) {
                if (str_contains($additionalReqs, $neg)) {
                    // Extract words following the negation
                    preg_match_all('/\b' . $neg . '\s+([a-z0-9_\-\s]+)/', $additionalReqs, $matches);
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $excludedPhrase) {
                            $terms = array_filter(explode(' ', trim($excludedPhrase)));
                            foreach ($terms as $term) {
                                if (strlen($term) > 3 && (str_contains($name, $term) || str_contains($category, $term))) {
                                    return [
                                        'passed' => false,
                                        'score' => 10,
                                        'reasons' => ["Disqualified: Matches negative exclusion requirement '{$term}'"]
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        // Rule 3: Category & Product Relevance
        $targetCategories = array_map('strtolower', (array)($campaign['target_categories'] ?? []));
        $categoryMatched = false;

        if (!empty($targetCategories)) {
            foreach ($targetCategories as $targetCat) {
                $targetCat = trim($targetCat);
                if (empty($targetCat)) continue;

                if (str_contains($category, $targetCat) || 
                    str_contains($targetCat, $category) || 
                    str_contains($name, $targetCat) ||
                    str_contains($name, str_replace('_', ' ', $targetCat))) {
                    $categoryMatched = true;
                    $score += 25;
                    $reasons[] = "Direct category match for '{$targetCat}'";
                    break;
                }
            }
        }

        // If target categories specified and matched, reward. If no target categories, match product or default.
        if (empty($targetCategories)) {
            $categoryMatched = true;
        }

        // Rule 4: Location Relevance
        $targetLocation = strtolower(explode(',', $campaign['target_location'] ?? '')[0]);
        if (!empty($targetLocation) && !empty($address)) {
            if (str_contains($address, trim($targetLocation))) {
                $score += 15;
                $reasons[] = "Confirmed inside target territory '{$targetLocation}'";
            } else {
                $score -= 10;
                $reasons[] = "Address does not explicitly mention core territory '{$targetLocation}'";
            }
        }

        // Rule 5: Physical Premises Indicator
        if (!empty($address) && strlen($address) > 15) {
            $score += 10;
            $reasons[] = "Valid physical street address found";
        } else {
            $score -= 15;
            $reasons[] = "Sparse or missing physical street address";
        }

        $passed = ($score >= 50) && ($categoryMatched || $score >= 65);

        if (!$passed && empty($reasons)) {
            $reasons[] = "Did not meet category or territorial relevance thresholds";
        }

        return [
            'passed' => $passed,
            'score' => min(100, max(0, $score)),
            'reasons' => $reasons,
        ];
    }
}
