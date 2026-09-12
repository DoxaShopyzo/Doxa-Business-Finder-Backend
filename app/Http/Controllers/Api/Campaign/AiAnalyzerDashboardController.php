<?php

namespace App\Http\Controllers\Api\Campaign;

use App\Http\Controllers\Controller;
use App\Models\LeadCampaign;
use App\Models\CampaignLead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AiAnalyzerDashboardController extends Controller
{
    /**
     * Get aggregate KPI metrics for AI Lead Analyzer
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $campaignsQuery = LeadCampaign::where('tenant_id', $tenantId);
        $leadsQuery = CampaignLead::where('tenant_id', $tenantId);

        // Optional filters
        if ($request->filled('campaign_id')) {
            $leadsQuery->where('campaign_id', $request->campaign_id);
        }
        if ($request->filled('location')) {
            $leadsQuery->where('location_address', 'like', "%{$request->location}%");
        }
        if ($request->filled('classification')) {
            $leadsQuery->where('ai_classification', $request->classification);
        }

        $totalCampaigns = (clone $campaignsQuery)->count();
        $totalDiscovered = (clone $campaignsQuery)->sum('total_discovered');
        $totalPrefiltered = (clone $campaignsQuery)->sum('total_prefiltered');
        $totalAiAnalyzed = (clone $campaignsQuery)->sum('total_ai_analyzed');
        $totalApproved = (clone $campaignsQuery)->sum('total_approved');
        $totalRejected = (clone $campaignsQuery)->sum('total_rejected');

        // Classification Breakdown
        $classificationCounts = [
            'VERY_HIGH' => (clone $leadsQuery)->where('ai_classification', 'VERY_HIGH')->count(),
            'HIGH' => (clone $leadsQuery)->where('ai_classification', 'HIGH')->count(),
            'MEDIUM' => (clone $leadsQuery)->where('ai_classification', 'MEDIUM')->count(),
            'LOW' => (clone $leadsQuery)->where('ai_classification', 'LOW')->count(),
            'VERY_LOW' => (clone $leadsQuery)->where('ai_classification', 'VERY_LOW')->count(),
        ];

        // Verification Status Breakdown
        $verificationCounts = [
            'needs_manual_review' => (clone $leadsQuery)->where('verification_status', 'needs_manual_review')->count(),
            'pending' => (clone $leadsQuery)->where('verification_status', 'pending')->count(),
            'approved' => (clone $leadsQuery)->where('verification_status', 'approved')->count(),
            'rejected' => (clone $leadsQuery)->where('verification_status', 'rejected')->count(),
        ];

        // Recent 5 campaigns
        $recentCampaigns = (clone $campaignsQuery)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'totals' => [
                'campaigns' => $totalCampaigns,
                'discovered' => (int)$totalDiscovered,
                'prefiltered' => (int)$totalPrefiltered,
                'ai_analyzed' => (int)$totalAiAnalyzed,
                'approved' => (int)$totalApproved,
                'rejected' => (int)$totalRejected,
                'final_qualified' => (int)$totalApproved,
            ],
            'classifications' => $classificationCounts,
            'verification' => $verificationCounts,
            'recent_campaigns' => $recentCampaigns,
        ]);
    }

    /**
     * Get current 0-100 scoring weights
     */
    public function getScoringWeights(): JsonResponse
    {
        $setting = DB::table('settings')
            ->where('group', 'ai_analyzer')
            ->where('key', 'scoring_weights')
            ->value('value');

        $weights = $setting ? json_decode($setting, true) : [
            'category_relevance' => 25,
            'business_size' => 15,
            'requirement_signals' => 20,
            'footfall_premises' => 15,
            'multiple_branch' => 10,
            'business_activity' => 10,
            'contact_availability' => 5,
        ];

        return response()->json([
            'weights' => $weights,
            'max_total' => 100,
        ]);
    }

    /**
     * Update admin scoring weights
     */
    public function updateScoringWeights(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_relevance' => 'required|integer|min:0|max:50',
            'business_size' => 'required|integer|min:0|max:50',
            'requirement_signals' => 'required|integer|min:0|max:50',
            'footfall_premises' => 'required|integer|min:0|max:50',
            'multiple_branch' => 'required|integer|min:0|max:50',
            'business_activity' => 'required|integer|min:0|max:50',
            'contact_availability' => 'required|integer|min:0|max:50',
        ]);

        $sum = array_sum($validated);
        if ($sum !== 100) {
            return response()->json([
                'message' => "The sum of all scoring factor weights must equal exactly 100 (current sum: {$sum}).",
            ], 422);
        }

        DB::table('settings')->updateOrInsert(
            [
                'tenant_id' => null,
                'group' => 'ai_analyzer',
                'key' => 'scoring_weights',
            ],
            [
                'value' => json_encode($validated),
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Scoring weights updated successfully',
            'weights' => $validated,
        ]);
    }
}
