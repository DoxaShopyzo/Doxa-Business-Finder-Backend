<?php
namespace App\Http\Controllers\Api\Subscription;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}
    
    public function index()
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        return PlanResource::collection($plans);
    }
    
    public function subscribe(Request $request)
    {
        $request->validate(['plan_id' => 'required|exists:plans,id']);
        
        $subscription = $this->subscriptionService->subscribe(
            auth()->user()->tenant,
            Plan::findOrFail($request->plan_id)
        );
        
        return new SubscriptionResource($subscription);
    }
    
    public function currentSubscription()
    {
        $subscription = $this->subscriptionService->getCurrentSubscription(
            auth()->user()->tenant
        );
        
        if (!$subscription) {
            return response()->json(['data' => null, 'message' => 'No active subscription']);
        }
        
        return new SubscriptionResource($subscription);
    }
}