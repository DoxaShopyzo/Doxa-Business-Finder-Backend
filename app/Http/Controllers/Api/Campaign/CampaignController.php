<?php

namespace App\Http\Controllers\Api\Campaign;

use App\Http\Controllers\Controller;
use App\Models\LeadCampaign;
use App\Models\CampaignLead;
use App\Services\CampaignService;
use App\Services\LeadVerificationService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignService $campaignService,
        protected LeadVerificationService $verificationService,
        protected ReportExportService $exportService
    ) {}

    /**
     * List campaigns
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = LeadCampaign::where('tenant_id', $user->tenant_id)
            ->withCount([
                'leads',
                'approvedLeads',
                'manualReviewLeads',
                'pendingLeads'
            ])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_name')) {
            $query->where('client_name', 'like', "%{$request->client_name}%");
        }

        if ($request->filled('product_service')) {
            $query->where('product_service', 'like', "%{$request->product_service}%");
        }

        $campaigns = $query->paginate($request->input('per_page', 15));

        return response()->json($campaigns);
    }

    /**
     * Create a new Lead Campaign
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'product_service' => 'required|string|max:255',
            'target_categories' => 'required|array|min:1',
            'target_categories.*' => 'string|max:100',
            'target_location' => 'required|string|max:255',
            'target_customer_type' => 'nullable|string|max:255',
            'minimum_business_size' => 'nullable|string|max:100',
            'preferred_characteristics' => 'nullable|string',
            'required_lead_count' => 'nullable|integer|min:5|max:500',
            'additional_requirements' => 'nullable|string',
            'scoring_weights' => 'nullable|array',
        ]);

        $campaign = $this->campaignService->createCampaign($validated, $user->id, $user->tenant_id);

        return response()->json([
            'message' => 'Lead campaign initiated successfully',
            'campaign' => $campaign,
        ], 201);
    }

    /**
     * Get campaign details and progress
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $user = $request->user();

        $campaign = LeadCampaign::where('tenant_id', $user->tenant_id)
            ->withCount([
                'leads',
                'approvedLeads',
                'manualReviewLeads',
                'pendingLeads'
            ])
            ->findOrFail($id);

        return response()->json([
            'campaign' => $campaign,
            'summary' => [
                'total_discovered' => $campaign->total_discovered,
                'total_prefiltered' => $campaign->total_prefiltered,
                'total_ai_analyzed' => $campaign->total_ai_analyzed,
                'total_approved' => $campaign->total_approved,
                'total_rejected' => $campaign->total_rejected,
                'needs_manual_review' => $campaign->manual_review_leads_count,
                'pending_review' => $campaign->pending_leads_count,
                'status' => $campaign->status,
            ]
        ]);
    }

    /**
     * Get paginated campaign leads with filters
     */
    public function leads(int $id, Request $request): JsonResponse
    {
        $user = $request->user();

        $campaign = LeadCampaign::where('tenant_id', $user->tenant_id)->findOrFail($id);

        $query = CampaignLead::where('campaign_id', $campaign->id)->with(['previews']);

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('ai_classification')) {
            $query->where('ai_classification', $request->ai_classification);
        }

        if ($request->filled('confidence_level')) {
            $query->where('confidence_level', $request->confidence_level);
        }

        if ($request->filled('potential_requirement')) {
            $query->where('potential_requirement', $request->potential_requirement);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('business_name', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%")
                  ->orWhere('location_address', 'like', "%{$s}%");
            });
        }

        $leads = $query->orderByDesc('ai_score')->paginate($request->input('per_page', 20));

        return response()->json($leads);
    }

    /**
     * Get verification queue (with special tab for low-confidence)
     */
    public function verificationQueue(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $campaign = LeadCampaign::where('tenant_id', $user->tenant_id)->findOrFail($id);

        $status = $request->input('status', 'pending'); // 'needs_manual_review', 'pending', 'approved', 'rejected'

        $leads = CampaignLead::where('campaign_id', $campaign->id)
            ->where('verification_status', $status)
            ->orderByDesc('ai_score')
            ->paginate($request->input('per_page', 25));

        $counts = [
            'needs_manual_review' => CampaignLead::where('campaign_id', $campaign->id)->where('verification_status', 'needs_manual_review')->count(),
            'pending' => CampaignLead::where('campaign_id', $campaign->id)->where('verification_status', 'pending')->count(),
            'approved' => CampaignLead::where('campaign_id', $campaign->id)->where('verification_status', 'approved')->count(),
            'rejected' => CampaignLead::where('campaign_id', $campaign->id)->where('verification_status', 'rejected')->count(),
        ];

        return response()->json([
            'counts' => $counts,
            'leads' => $leads,
        ]);
    }

    /**
     * Human verification action for a single lead
     */
    public function verifyLead(int $campaignId, int $leadId, Request $request): JsonResponse
    {
        $user = $request->user();
        $campaign = LeadCampaign::where('tenant_id', $user->tenant_id)->findOrFail($campaignId);
        $lead = CampaignLead::where('campaign_id', $campaign->id)->findOrFail($leadId);

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'human_score' => 'nullable|integer|min:0|max:100',
            'human_classification' => 'nullable|string|max:50',
            'human_remarks' => 'nullable|string|max:500',
        ]);

        $updated = $this->verificationService->verifyLead($lead, $validated, $user->id);

        return response()->json([
            'message' => "Lead marked as {$validated['action']}d successfully",
            'lead' => $updated,
        ]);
    }

    /**
     * Bulk verify leads
     */
    public function bulkVerify(int $campaignId, Request $request): JsonResponse
    {
        $user = $request->user();
        $campaign = LeadCampaign::where('tenant_id', $user->tenant_id)->findOrFail($campaignId);

        $validated = $request->validate([
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'integer',
            'action' => 'required|in:approve,reject',
            'remarks' => 'nullable|string|max:500',
        ]);

        $count = $this->verificationService->bulkVerify(
            $campaign,
            $validated['lead_ids'],
            $validated['action'],
            $user->id,
            $validated['remarks'] ?? null
        );

        return response()->json([
            'message' => "Successfully {$validated['action']}d {$count} leads",
            'count' => $count,
        ]);
    }

    /**
     * Export final qualified leads (strictly approved only)
     */
    public function export(int $campaignId, Request $request): StreamedResponse
    {
        $user = $request->user();
        $campaign = LeadCampaign::where('tenant_id', $user->tenant_id)->findOrFail($campaignId);

        $csvContent = $this->exportService->generateCsv($campaign);

        $filename = 'doxa_ai_qualified_leads_campaign_' . $campaign->id . '_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Re-use a previous campaign's configuration as a template
     */
    public function reuseTemplate(int $campaignId, Request $request): JsonResponse
    {
        $user = $request->user();
        $source = LeadCampaign::where('tenant_id', $user->tenant_id)->findOrFail($campaignId);

        $overrides = $request->only(['name', 'required_lead_count', 'target_location']);

        $newCampaign = $this->campaignService->reuseTemplate($source, $overrides, $user->id, $user->tenant_id);

        return response()->json([
            'message' => 'New campaign cloned and initiated from template',
            'campaign' => $newCampaign,
        ], 201);
    }
}
