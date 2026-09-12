<?php

namespace App\Console\Commands;

use App\Models\CampaignLead;
use App\Services\GooglePlacesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurgeExpiredGoogleLeads extends Command
{
    protected $signature = 'doxa:purge-expired-google-leads {--days=30 : The age in days beyond which to evaluate leads}';
    protected $description = 'Complies with Google Maps Platform 30-day data retention: purges approved/rejected leads and automatically re-fetches fresh Place Details for unverified leads.';

    public function handle(GooglePlacesService $placesService): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $expiredLeads = CampaignLead::withoutGlobalScopes()
            ->where('created_at', '<', $cutoffDate)
            ->whereNull('google_purged_at')
            ->get();

        if ($expiredLeads->isEmpty()) {
            $this->info("Google Compliance: No leads older than {$days} days requiring action.");
            return self::SUCCESS;
        }

        $purgedCount = 0;
        $extendedCount = 0;

        foreach ($expiredLeads as $lead) {
            // FIX 3 Rule 1: Decided leads (approved or rejected) are purged
            if (in_array($lead->verification_status, ['approved', 'rejected'])) {
                $lead->update([
                    'business_name' => '[Purged after 30 days per Google ToS]',
                    'location_address' => '[Purged after 30 days per Google ToS]',
                    'phone' => null,
                    'website' => null,
                    'google_rating' => null,
                    'google_review_count' => null,
                    'business_status' => null,
                    'latitude' => null,
                    'longitude' => null,
                    'google_purged_at' => now(),
                ]);

                $purgedCount++;
                $logMsg = "[GOOGLE_PURGE] Lead #{$lead->id} (Campaign #{$lead->campaign_id}, Status: {$lead->verification_status}, Place ID: {$lead->place_reference}) purged Google-derived text attributes after {$days} days. Place ID, AI scores, and audit history retained indefinitely.";
                Log::info($logMsg);
                $this->line("  [PURGED] Lead #{$lead->id} ({$lead->verification_status}) - Place ID: {$lead->place_reference}");
            } 
            // FIX 3 Rule 2: Unverified leads (pending or needs_manual_review) are re-fetched to preserve reviewer pipeline
            elseif (in_array($lead->verification_status, ['pending', 'needs_manual_review'])) {
                try {
                    $freshDetails = $placesService->getPlaceDetails($lead->place_reference);

                    $lead->timestamps = false;
                    $lead->forceFill([
                        'business_name' => $freshDetails['name'] ?? $lead->business_name,
                        'location_address' => $freshDetails['address'] ?? $lead->location_address,
                        'phone' => $freshDetails['phone'] ?? $lead->phone,
                        'website' => $freshDetails['website'] ?? $lead->website,
                        'google_rating' => isset($freshDetails['rating']) ? (float)$freshDetails['rating'] : $lead->google_rating,
                        'google_review_count' => isset($freshDetails['review_count']) ? (int)$freshDetails['review_count'] : $lead->google_review_count,
                        'business_status' => $freshDetails['business_status'] ?? $lead->business_status,
                        'latitude' => $freshDetails['latitude'] ?? $lead->latitude,
                        'longitude' => $freshDetails['longitude'] ?? $lead->longitude,
                        'created_at' => now(), // Reset 30-day compliance window from fresh fetch
                        'updated_at' => now(),
                    ])->save();

                    $extendedCount++;
                    $logMsg = "[GOOGLE_REFETCH_EXTENDED] Lead #{$lead->id} (Campaign #{$lead->campaign_id}, Status: {$lead->verification_status}) awaiting human verification at {$days}-day mark. Automatically re-fetched fresh Place Details for place_id '{$lead->place_reference}'. Fresh 30-day compliance window initialized.";
                    Log::info($logMsg);
                    $this->line("  [REFETCHED & EXTENDED] Lead #{$lead->id} ({$lead->verification_status}) - Place ID: {$lead->place_reference}");
                } catch (\Throwable $e) {
                    Log::error("Failed to re-fetch Place Details for expired unverified Lead #{$lead->id}: " . $e->getMessage());
                    $this->error("  [REFETCH FAILED] Lead #{$lead->id}: " . $e->getMessage());
                }
            }
        }

        $summary = "Google Compliance Run Summary: {$purgedCount} decided leads purged, {$extendedCount} unverified leads re-fetched & extended.";
        $this->info($summary);
        Log::info("[GOOGLE_COMPLIANCE_RUN] " . $summary);

        return self::SUCCESS;
    }
}
