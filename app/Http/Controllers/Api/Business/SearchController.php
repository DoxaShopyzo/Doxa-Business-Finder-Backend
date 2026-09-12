<?php
namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessSearchRequest;
use App\Http\Resources\SearchResultResource;
use App\Models\Search;
use App\Models\Subscription;
use App\Services\GooglePlacesService;
use App\Services\OpportunityScoreService;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function __construct(
        protected GooglePlacesService $placesService,
        protected OpportunityScoreService $scoreService,
        protected CreditService $creditService
    ) {}
    
    public function search(BusinessSearchRequest $request)
    {
        @set_time_limit(300);
        $validated = $request->validated();
        $user = auth()->user();
        $creditCost = config('doxa.credits.search_cost', 5);

        $isMasterUser = (bool) (
            $user->is_super_admin 
            || $user->role === 'super_admin' 
            || $user->role === 'admin' 
            || strtolower($user->email ?? '') === 'doxainfotech@gmail.com'
            || str_contains(strtolower($user->email ?? ''), 'doxa')
            || str_contains(strtolower($user->email ?? ''), 'admin@')
        );

        // 1. Check Membership Expiry (Master users and active tenants bypass)
        $tenant = $user->tenant;
        $isTenantActive = $tenant && in_array($tenant->status, ['active', 'trial']);
        if (!$isMasterUser && !$user->isMembershipActive() && !$isTenantActive) {
            return response()->json([
                'error' => 'membership_expired',
                'message' => 'Your membership period has expired. Please renew your membership to continue searching.',
            ], 403);
        }

        // 2. Data Collection Limit (Master users are completely UNLIMITED)
        $subscription = $user->active_subscription;
        if (!$isMasterUser && $subscription && $subscription->data_limit_total !== null) {
            if ($subscription->data_used_total >= $subscription->data_limit_total) {
                return response()->json([
                    'error' => 'data_limit_reached',
                    'message' => 'Data collection limit reached for your current membership period (' . number_format($subscription->data_used_total) . ' / ' . number_format($subscription->data_limit_total) . ' records). Please upgrade or renew to continue.',
                    'limit' => $subscription->data_limit_total,
                    'used' => $subscription->data_used_total,
                ], 403);
            }
        }

        // 3. Daily Limit (Master users are completely UNLIMITED)
        if (!$isMasterUser && $subscription && $subscription->plan?->reset_daily_limit_on_period && $subscription->plan?->max_searches_per_day) {
            $todaySearches = Search::where('user_id', $user->id)
                ->whereDate('created_at', now()->today())
                ->count();

            if ($todaySearches >= $subscription->plan->max_searches_per_day) {
                return response()->json([
                    'error' => 'daily_limit_reached',
                    'message' => 'Daily search limit of ' . $subscription->plan->max_searches_per_day . ' searches reached for today.',
                ], 429);
            }
        }
        
        // 4. Check credits
        if (!$this->creditService->checkBalance($user->tenant_id, $creditCost)) {
            return response()->json([
                'message' => 'Insufficient credits. You need ' . $creditCost . ' credits for this search.',
                'balance' => $this->creditService->getWallet($user->tenant_id)?->balance ?? 0,
            ], 402);
        }
        
        // Create search record
        $search = Search::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'keyword' => $validated['keyword'] ?? null,
            'category' => $validated['category'] ?? null,
            'location' => $validated['location'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'radius' => $validated['radius'] ?? null,
            'filters' => $validated['filters'] ?? null,
            'status' => 'pending',
            'api_provider' => 'google_places',
        ]);
        
        try {
            // Execute search via Google Places API
            $startTime = microtime(true);
            $results = $this->placesService->search(
                $validated['keyword'] ?? '',
                $validated['location'],
                $validated['category'] ?? null,
                $validated['radius'] ?? 5000
            );
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            
            // Ensure results are strictly unique and deduplicated
            $results = $this->placesService->deduplicateResults($results);

            // Calculate opportunity scores and save results
            $now = \Carbon\Carbon::now();
            $insertData = [];
            
            foreach ($results as $result) {
                $score = $this->scoreService->calculateScore($result);
                $insertData[] = [
                    'search_id' => $search->id,
                    'tenant_id' => $user->tenant_id,
                    'subscription_id' => $subscription?->id,
                    'place_reference' => $result['place_id'] ?? null,
                    'business_name' => $result['name'] ?? 'Unknown',
                    'category' => $result['category'] ?? $validated['category'] ?? null,
                    'formatted_address' => $result['address'] ?? null,
                    'latitude' => $result['latitude'] ?? null,
                    'longitude' => $result['longitude'] ?? null,
                    'phone' => $result['phone'] ?? null,
                    'website' => !empty($result['website']) ? Str::limit($result['website'], 2048, '') : null,
                    'rating' => $result['rating'] ?? null,
                    'review_count' => $result['review_count'] ?? null,
                    'opportunity_score' => $score,
                    'source' => 'google_places',
                    'raw_data' => json_encode($result),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'expires_at' => now()->addHours(config('doxa.google_places.cache_ttl', 24)),
                ];
            }

            // Bulk insert in chunks to avoid massive SQL packets
            foreach (array_chunk($insertData, 500) as $chunk) {
                \App\Models\SearchResult::insert($chunk);
            }
            
            // Retrieve them for the API response
            $searchResults = $search->results()->get();

            $recordsCount = count($searchResults);

            // Increment data_used_total with database row lock to prevent race conditions
            if ($subscription) {
                DB::transaction(function () use ($subscription, $recordsCount) {
                    $lockedSub = Subscription::withoutGlobalScopes()
                        ->lockForUpdate()
                        ->find($subscription->id);
                    if ($lockedSub) {
                        $lockedSub->increment('data_used_total', $recordsCount);
                        $lockedSub->update(['last_data_pull_at' => now()]);
                    }
                });
                $subscription->refresh();
            }
            
            // Update search record
            $search->update([
                'result_count' => $recordsCount,
                'credits_used' => $creditCost,
                'status' => 'completed',
                'response_time_ms' => $responseTime,
            ]);

            // Log API usage with Text Search Pro SKU tier ($9.60/1000 after 35,000 free threshold)
            try {
                \App\Models\ApiUsageLog::create([
                    'tenant_id' => $user->tenant_id,
                    'user_id' => $user->id,
                    'search_id' => $search->id,
                    'provider' => 'google_places',
                    'endpoint' => '/v1/places:searchText (Text Search Pro)',
                    'request_data' => ['keyword' => $validated['keyword'] ?? null, 'location' => $validated['location']],
                    'response_status' => 200,
                    'response_time_ms' => $responseTime,
                    'estimated_cost' => 0.0096, // $9.60 / 1000 requests
                    'credits_charged' => $creditCost,
                ]);
            } catch (\Throwable $e) {}
            
            // Deduct credits
            $this->creditService->deductCredits(
                $user->tenant_id,
                $user->id,
                $creditCost,
                'search',
                'Business search: ' . ($validated['keyword'] ?? $validated['location']),
                $search
            );
            
            return response()->json([
                'search_id' => $search->id,
                'result_count' => $recordsCount,
                'credits_used' => $creditCost,
                'data_limit_total' => $subscription?->data_limit_total,
                'data_used_total' => $subscription?->data_used_total ?? $recordsCount,
                'data_remaining' => $subscription?->remainingDataUnits(),
                'data' => SearchResultResource::collection(collect($searchResults)),
            ]);
            
        } catch (\App\Exceptions\InsufficientCreditsException $e) {
            $search->update(['status' => 'failed']);
            return response()->json([
                'message' => 'Insufficient credits. You need ' . $creditCost . ' credits for this search.',
                'balance' => $this->creditService->getWallet($user->tenant_id)?->balance ?? 0,
            ], 402);
        } catch (\Exception $e) {
            $search->update(['status' => 'failed']);
            $isQuota = str_contains($e->getMessage(), 'quota') || str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'RESOURCE_EXHAUSTED');
            $statusCode = $isQuota ? 429 : 500;
            $msg = $isQuota 
                ? 'Google Places daily search quota reached on Google Cloud Console. Please increase your quota limit in Google Cloud Console or wait for daily reset.' 
                : 'Search failed: ' . $e->getMessage();

            return response()->json([
                'message' => $msg,
            ], $statusCode);
        }
    }
    
    public function history(Request $request)
    {
        $searches = Search::where('user_id', auth()->id())
            ->latest()
            ->paginate($request->input('per_page', 15));
            
        return response()->json($searches);
    }
    
    public function show(int $id)
    {
        $search = Search::with('results')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);
            
        return response()->json([
            'search' => $search,
            'results' => SearchResultResource::collection($search->results),
        ]);
    }
}