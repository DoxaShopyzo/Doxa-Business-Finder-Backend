<?php

namespace App\Services;

use App\Models\LeadCampaign;
use App\Models\CampaignLead;

class ReportExportService
{
    /**
     * Generate CSV export content for approved leads only
     */
    public function generateCsv(LeadCampaign $campaign): string
    {
        $leads = CampaignLead::withoutGlobalScopes()
            ->where('campaign_id', $campaign->id)
            ->where('verification_status', 'approved')
            ->orderByDesc('human_score')
            ->orderByDesc('ai_score')
            ->get();

        $buffer = fopen('php://temp', 'r+');

        // UTF-8 BOM for Microsoft Excel compatibility
        fwrite($buffer, "\xEF\xBB\xBF");

        // Compliance Header & Notice
        fputcsv($buffer, ['DOXA BUSINESS FINDER — AI LEAD ANALYZER REPORT']);
        fputcsv($buffer, ['Campaign:', $campaign->name]);
        fputcsv($buffer, ['Client:', $campaign->client_name]);
        fputcsv($buffer, ['Product/Service:', $campaign->product_service]);
        fputcsv($buffer, ['Target Location:', $campaign->target_location]);
        fputcsv($buffer, ['Generated On:', now()->toDateTimeString()]);
        fputcsv($buffer, ['LEAD STATUS:', 'AI-Assisted Qualified Prospects (Verified by Human Reviewer)']);
        fputcsv($buffer, ['LEGAL NOTICE:', 'Data derived in part from Google Places API. Used for qualification purposes. Subject to TRAI telemarketing guidelines.']);
        fputcsv($buffer, []); // Blank line

        // Exact required columns
        fputcsv($buffer, [
            'Lead ID',
            'Business Name',
            'Category',
            'Location',
            'Phone Number',
            'Website Status',
            'App Status',
            'DND Status',
            'AI Score',
            'Lead Category (Classification)',
            'Potential Requirement',
            'AI Qualification Reason',
            'Suggested Sales Question',
            'Confidence Level',
            'Sales Priority',
            'Reviewer Remarks',
            'Place ID'
        ]);

        foreach ($leads as $lead) {
            $hasWebsite = !empty($lead->website) ? $lead->website : 'No Website Available';
            $score = $lead->human_score ?? $lead->ai_score;
            $class = $lead->human_classification ?? $lead->ai_classification;

            fputcsv($buffer, [
                'LEAD-' . str_pad($lead->id, 5, '0', STR_PAD_LEFT),
                $lead->business_name ?? 'Confidential Business Prospect',
                $lead->category ?? 'General Business',
                $lead->location_address ?? $campaign->target_location,
                $lead->phone ?? 'Not Publicly Available',
                $hasWebsite,
                'Not Detected', // App status
                strtoupper($lead->dnd_status ?? 'NOT_CHECKED'),
                $score,
                $class,
                $lead->potential_requirement ?? 'MEDIUM',
                $lead->qualification_reason ?? 'AI-Assisted Qualification',
                $lead->suggested_sales_question ?? 'N/A',
                $lead->confidence_level ?? 'MEDIUM',
                $lead->sales_priority ?? 'MEDIUM',
                $lead->human_remarks ?? 'Approved by verification team',
                $lead->place_reference
            ]);
        }

        rewind($buffer);
        $csv = stream_get_contents($buffer);
        fclose($buffer);

        return $csv;
    }
}
