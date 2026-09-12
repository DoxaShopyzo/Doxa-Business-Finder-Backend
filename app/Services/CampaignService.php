<?php

namespace App\Services;

use App\Models\LeadCampaign;
use App\Models\CampaignLead;
use App\Jobs\ProcessLeadCampaignJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CampaignService
{
    public function __construct(
        protected GooglePlacesService $placesService,
        protected LeadPreFilterService $preFilterService,
        protected AiLeadAnalyzerService $aiAnalyzerService,
        protected DndComplianceService $dndService,
        protected CreditService $creditService
    ) {}

    /**
     * Create a new Lead Campaign and launch processing
     */
    public function createCampaign(array $data, int $userId, ?int $tenantId = null): LeadCampaign
    {
        $tenantId = $tenantId ?: 1;

        return DB::transaction(function () use ($data, $userId, $tenantId) {
            $campaign = LeadCampaign::create([
                'tenant_id' => $tenantId,
                'created_by' => $userId,
                'name' => $data['name'],
                'client_name' => $data['client_name'],
                'product_service' => $data['product_service'],
                'target_categories' => (array)($data['target_categories'] ?? []),
                'target_location' => $data['target_location'],
                'target_customer_type' => $data['target_customer_type'] ?? null,
                'minimum_business_size' => $data['minimum_business_size'] ?? null,
                'preferred_characteristics' => $data['preferred_characteristics'] ?? null,
                'required_lead_count' => (int)($data['required_lead_count'] ?? 50),
                'additional_requirements' => $data['additional_requirements'] ?? null,
                'scoring_weights' => $data['scoring_weights'] ?? null,
                'status' => 'processing',
                'credits_spent' => config('doxa.credits.ai_analysis_cost', 10),
            ]);

            // Deduct credits if tenant has wallet
            try {
                $this->creditService->deductCredits(
                    $tenantId,
                    $userId,
                    $campaign->credits_spent,
                    'ai_campaign',
                    "AI Lead Campaign: {$campaign->name}",
                    $campaign
                );
            } catch (\Throwable $e) {
                Log::info("Campaign credit deduction skipped/handled: " . $e->getMessage());
            }

            // If queue connection is sync or immediate execution desired
            if (config('queue.default') === 'sync') {
                $this->executePipeline($campaign);
            } else {
                ProcessLeadCampaignJob::dispatch($campaign);
            }

            return $campaign->refresh();
        });
    }

    /**
     * Execute the full 2-stage qualification pipeline
     */
    public function executePipeline(LeadCampaign $campaign): void
    {
        $campaign->update(['status' => 'processing']);

        $location = $campaign->target_location;
        $categories = (array)$campaign->target_categories;
        $primaryCategory = !empty($categories) ? $categories[0] : null;
        $searchKeyword = $primaryCategory ?: $campaign->product_service;

        // STEP 1: Discovery Search (Google Places Text Search Pro SKU)
        $rawProspects = $this->placesService->search(
            $searchKeyword,
            $location,
            $primaryCategory
        );

        $totalDiscovered = count($rawProspects);
        $campaignArray = $campaign->toArray();

        // STEP 2: Stage A Deterministic Pre-Filter (Rule-based, zero LLM cost)
        $survivingProspects = [];
        $prefilteredOutCount = 0;

        foreach ($rawProspects as $prospect) {
            $preFilterResult = $this->preFilterService->evaluate($prospect, $campaignArray);

            if ($preFilterResult['passed']) {
                $prospect['prefilter_score'] = $preFilterResult['score'];
                $prospect['prefilter_reasons'] = $preFilterResult['reasons'];
                $survivingProspects[] = $prospect;
            } else {
                $prefilteredOutCount++;
            }
        }

        // Limit candidates to required count + buffer
        $maxToAnalyze = min(count($survivingProspects), max(20, $campaign->required_lead_count * 2));
        $survivingProspects = array_slice($survivingProspects, 0, $maxToAnalyze);

        // STEP 3: Stage B AI Scoring in Batches (Gemini 1.5 Flash structured analysis)
        $aiAnalysisMap = [];
        $batchSize = 15;
        $chunks = array_chunk($survivingProspects, $batchSize);

        foreach ($chunks as $batch) {
            $batchResults = $this->aiAnalyzerService->analyzeBatch($batch, $campaignArray);
            foreach ($batchResults as $res) {
                $ref = $res['prospect_ref'] ?? '';
                if (!empty($ref)) {
                    $aiAnalysisMap[$ref] = $res;
                }
            }
        }

        // STEP 4: Persist into campaign_leads with DND checks and low-confidence fallback
        $insertRows = [];
        $now = now();

        foreach ($survivingProspects as $prospect) {
            $placeRef = $prospect['place_id'] ?? ($prospect['place_reference'] ?? ('REF-' . md5(json_encode($prospect))));
            $aiData = $aiAnalysisMap[$placeRef] ?? null;

            if (!$aiData) {
                $aiData = $this->aiAnalyzerService->fallbackDeterministicAnalysis([$prospect], $campaignArray, [])[0] ?? [];
            }

            $phone = $prospect['phone'] ?? null;
            $dndResult = $phone ? $this->dndService->checkDndStatus($phone) : [
                'status' => 'not_checked',
                'checked_at' => null,
                'reference' => null,
            ];

            $confidence = $aiData['confidence_level'] ?? 'MEDIUM';
            // Low Confidence Fallback Rule: LOW confidence routes to needs_manual_review
            $verifStatus = ($confidence === 'LOW') ? 'needs_manual_review' : 'pending';

            $insertRows[] = [
                'tenant_id' => $campaign->tenant_id,
                'campaign_id' => $campaign->id,
                'place_reference' => $placeRef,
                'business_name' => $prospect['name'] ?? ($prospect['business_name'] ?? 'Local Business'),
                'category' => $prospect['category'] ?? ($prospect['primaryType'] ?? 'General Business'),
                'location_address' => $prospect['address'] ?? ($prospect['formatted_address'] ?? $location),
                'phone' => $phone,
                'website' => $prospect['website'] ?? null,
                'google_rating' => isset($prospect['rating']) ? (float)$prospect['rating'] : null,
                'google_review_count' => isset($prospect['review_count']) ? (int)$prospect['review_count'] : null,
                'business_status' => $prospect['business_status'] ?? 'OPERATIONAL',
                'latitude' => $prospect['latitude'] ?? null,
                'longitude' => $prospect['longitude'] ?? null,
                'prefilter_passed' => true,
                'prefilter_score' => $prospect['prefilter_score'] ?? 50,
                'prefilter_reasons' => json_encode($prospect['prefilter_reasons'] ?? []),
                'ai_score' => $aiData['ai_score'] ?? 50,
                'ai_classification' => $aiData['ai_classification'] ?? 'MEDIUM',
                'potential_requirement' => $aiData['potential_requirement'] ?? 'MEDIUM',
                'confidence_level' => $confidence,
                'qualification_reason' => $aiData['qualification_reason'] ?? 'AI-assisted qualification identified potential requirement.',
                'sales_priority' => $aiData['sales_priority'] ?? 'MEDIUM',
                'suggested_sales_question' => $aiData['suggested_sales_question'] ?? null,
                'score_breakdown' => json_encode($aiData['score_breakdown'] ?? []),
                'verification_status' => $verifStatus,
                'dnd_status' => $dndResult['status'],
                'dnd_checked_at' => $dndResult['checked_at'],
                'dnd_check_reference' => $dndResult['reference'],
                'consent_status' => 'none',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk upsert in chunks of 200 to prevent concurrent queue worker duplicate key violations
        foreach (array_chunk($insertRows, 200) as $chunk) {
            CampaignLead::upsert(
                $chunk,
                ['campaign_id', 'place_reference'],
                [
                    'ai_score',
                    'ai_classification',
                    'potential_requirement',
                    'confidence_level',
                    'qualification_reason',
                    'sales_priority',
                    'suggested_sales_question',
                    'score_breakdown',
                    'updated_at',
                ]
            );
        }

        // STEP 5: Finalize Campaign Status
        $campaign->update([
            'total_discovered' => $totalDiscovered,
            'total_prefiltered' => $prefilteredOutCount,
            'total_ai_analyzed' => count($insertRows),
            'status' => 'in_verification',
        ]);
    }

    /**
     * Re-use a previous campaign's configuration as a template
     */
    public function reuseTemplate(LeadCampaign $source, array $overrides, int $userId, int $tenantId): LeadCampaign
    {
        $data = array_merge([
            'name' => $source->name . ' (Copy)',
            'client_name' => $source->client_name,
            'product_service' => $source->product_service,
            'target_categories' => $source->target_categories,
            'target_location' => $source->target_location,
            'target_customer_type' => $source->target_customer_type,
            'minimum_business_size' => $source->minimum_business_size,
            'preferred_characteristics' => $source->preferred_characteristics,
            'required_lead_count' => $source->required_lead_count,
            'additional_requirements' => $source->additional_requirements,
            'scoring_weights' => $source->scoring_weights,
        ], $overrides);

        return $this->createCampaign($data, $userId, $tenantId);
    }
}
