<?php
namespace App\Http\Controllers\Api\Onboarding;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'business_category' => 'nullable|string',
            'target_locations' => 'nullable|array',
            'services_offered' => 'nullable|array',
        ]);
        
        $user = auth()->user();
        $tenant = $user->tenant;
        
        $tenant->update([
            'company_name' => $request->company_name,
            'business_category' => $request->business_category,
            'target_locations' => $request->target_locations,
            'services_offered' => $request->services_offered,
        ]);
        
        $user->update(['onboarding_completed' => true]);
        
        return response()->json([
            'message' => 'Onboarding completed successfully!',
            'tenant' => new \App\Http\Resources\TenantResource($tenant->fresh()),
        ]);
    }
}