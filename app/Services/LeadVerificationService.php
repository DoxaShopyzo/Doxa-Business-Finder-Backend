<?php

namespace App\Services;

use App\Models\CampaignLead;
use App\Models\LeadCampaign;
use Illuminate\Support\Facades\DB;

class LeadVerificationService
{
    /**
     * Verify a single lead
     */
    public function verifyLead(CampaignLead $lead, array $data, int $userId): CampaignLead
    {
        $action = $data['action'] ?? 'approve'; // 'approve' or 'reject'
        $newStatus = ($action === 'approve') ? 'approved' : 'rejected';

        $lead->update([
            'verification_status' => $newStatus,
            'verified_by' => $userId,
            'verified_at' => now(),
            'human_score' => $data['human_score'] ?? $lead->ai_score,
            'human_classification' => $data['human_classification'] ?? $lead->ai_classification,
            'human_remarks' => $data['human_remarks'] ?? null,
        ]);

        $this->updateCampaignCounters($lead->campaign_id);

        return $lead->refresh();
    }

    /**
     * Bulk verify multiple leads
     */
    public function bulkVerify(LeadCampaign $campaign, array $leadIds, string $action, int $userId, ?string $remarks = null): int
    {
        $newStatus = ($action === 'approve') ? 'approved' : 'rejected';

        $updatedCount = CampaignLead::withoutGlobalScopes()
            ->where('campaign_id', $campaign->id)
            ->whereIn('id', $leadIds)
            ->update([
                'verification_status' => $newStatus,
                'verified_by' => $userId,
                'verified_at' => now(),
                'human_remarks' => $remarks,
            ]);

        $this->updateCampaignCounters($campaign->id);

        return $updatedCount;
    }

    /**
     * Refresh campaign progress counters
     */
    public function updateCampaignCounters(int $campaignId): void
    {
        $approved = CampaignLead::withoutGlobalScopes()
            ->where('campaign_id', $campaignId)
            ->where('verification_status', 'approved')
            ->count();

        $rejected = CampaignLead::withoutGlobalScopes()
            ->where('campaign_id', $campaignId)
            ->where('verification_status', 'rejected')
            ->count();

        $remaining = CampaignLead::withoutGlobalScopes()
            ->where('campaign_id', $campaignId)
            ->whereIn('verification_status', ['pending', 'needs_manual_review'])
            ->count();

        $campaign = LeadCampaign::withoutGlobalScopes()->find($campaignId);
        if ($campaign) {
            $update = [
                'total_approved' => $approved,
                'total_rejected' => $rejected,
            ];

            if ($remaining === 0 && in_array($campaign->status, ['in_verification', 'processing'])) {
                $update['status'] = 'completed';
            }

            $campaign->update($update);
        }
    }
}
