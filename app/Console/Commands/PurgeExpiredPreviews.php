<?php

namespace App\Console\Commands;

use App\Models\GeneratedPreview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurgeExpiredPreviews extends Command
{
    protected $signature = 'doxa:purge-expired-previews 
                            {--google-days=30 : Days beyond which Google-sourced contact/rating attributes are nulled}
                            {--days=90 : Days beyond which the entire preview is purged}';

    protected $description = 'Executes split-timer preview retention: 30-day Google field nullification and 90-day full preview purge.';

    public function handle(): int
    {
        $googleDays = (int) $this->option('google-days');
        $fullDays = (int) $this->option('days');

        $googleCutoff = now()->subDays($googleDays);
        $fullCutoff = now()->subDays($fullDays);

        // -------------------------------------------------------------
        // PASS 1: 30-Day Partial Purge (Google-derived fields only)
        // Nulls phone, address, rating, review_count inside custom_content
        // Leaves business_name, logo, theme, template, and URL intact.
        // -------------------------------------------------------------
        $previewsForGooglePurge = GeneratedPreview::withoutGlobalScopes()
            ->where('created_at', '<', $googleCutoff)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
            ->get();

        $googlePurgedCount = 0;

        foreach ($previewsForGooglePurge as $preview) {
            $content = $preview->custom_content ?? [];

            // Check if any Google-derived field is currently set
            $hasGoogleData = !empty($content['phone']) 
                          || !empty($content['address']) 
                          || isset($content['rating']) 
                          || isset($content['review_count']);

            if ($hasGoogleData) {
                $content['phone'] = null;
                $content['address'] = null;
                $content['rating'] = null;
                $content['review_count'] = null;
                $content['google_data_purged_at'] = now()->toIso8601String();

                $preview->update(['custom_content' => $content]);
                $googlePurgedCount++;

                Log::info("[PREVIEW_GOOGLE_DATA_PURGED] Google-derived contact/rating attributes nulled for preview #{$preview->id} ('{$preview->business_name}', Token: {$preview->token}) aged >{$googleDays} days.");
            }
        }

        // -------------------------------------------------------------
        // PASS 2: 90-Day Full Purge (Complete Preview row deletion)
        // -------------------------------------------------------------
        $fullExpired = GeneratedPreview::withoutGlobalScopes()
            ->where(function ($q) use ($fullCutoff) {
                $q->where('expires_at', '<', now())
                  ->orWhere('created_at', '<', $fullCutoff);
            })
            ->get();

        $fullPurgedCount = $fullExpired->count();

        foreach ($fullExpired as $preview) {
            Log::info("[PREVIEW_FULL_PURGE] Expired preview #{$preview->id} ('{$preview->business_name}', Token: {$preview->token}) fully deleted after {$fullDays} days.");
            $preview->delete();
        }

        $this->info("Preview Retention: Pass 1 (30-day Google field purge) updated {$googlePurgedCount} previews; Pass 2 (90-day full purge) deleted {$fullPurgedCount} previews.");

        return self::SUCCESS;
    }
}
