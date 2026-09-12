<?php
namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Models\SearchResult;
use App\Services\GooglePlacesService;
use App\Services\OpportunityScoreService;
use App\Services\ServiceRecommendationService;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function __construct(
        protected ServiceRecommendationService $recommendationService,
        protected GooglePlacesService $placesService,
        protected OpportunityScoreService $scoreService
    ) {}
    
    public function show($id)
    {
        $result = SearchResult::findOrFail($id);
        
        // Tier 2 On-Demand Fetch: If business is from Google Places and has place_reference, enrich with Enterprise data
        if ($result->place_reference && empty($result->phone) && empty($result->website)) {
            try {
                $details = $this->placesService->getPlaceDetails($result->place_reference);
                if (!empty($details)) {
                    $result->phone = $details['phone'] ?? $result->phone;
                    $result->website = $details['website'] ?? $result->website;
                    $result->rating = $details['rating'] ?? $result->rating;
                    $result->review_count = $details['review_count'] ?? $result->review_count;
                    $result->opportunity_score = $this->scoreService->calculateScore($result->toArray());
                    $result->save();

                    // Log Tier 2 Enterprise API call
                    try {
                        \App\Models\ApiUsageLog::create([
                            'tenant_id' => auth()->user()?->tenant_id ?? $result->tenant_id,
                            'user_id' => auth()->id(),
                            'search_id' => $result->search_id,
                            'provider' => 'google_places',
                            'endpoint' => '/v1/places/{id} (Place Details Enterprise)',
                            'request_data' => ['place_id' => $result->place_reference],
                            'response_status' => 200,
                            'response_time_ms' => 120,
                            'estimated_cost' => 0.0060, // $6/1000 after free tier
                            'credits_charged' => 0,
                        ]);
                    } catch (\Throwable $e) {}
                }
            } catch (\Throwable $e) {
                // Fallback gracefully if place details fails
            }
        }
        
        $recommendations = $this->recommendationService->getRecommendations(
            $result->category,
            $result->website,
            $result->rating
        );
        
        return response()->json([
            'business' => $result,
            'recommendations' => $recommendations,
            'opportunity_score' => $result->opportunity_score,
        ]);
    }
}