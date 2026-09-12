<?php

namespace App\Jobs;

use App\Models\LeadCampaign;
use App\Services\CampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessLeadCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    public function __construct(public LeadCampaign $campaign)
    {
    }

    public function handle(CampaignService $campaignService): void
    {
        Log::info("ProcessLeadCampaignJob: Starting campaign #{$this->campaign->id} ({$this->campaign->name})");

        try {
            $campaignService->executePipeline($this->campaign);
            Log::info("ProcessLeadCampaignJob: Successfully completed pipeline for campaign #{$this->campaign->id}");
        } catch (\Throwable $e) {
            Log::error("ProcessLeadCampaignJob failed for campaign #{$this->campaign->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->campaign->update(['status' => 'failed']);
            throw $e;
        }
    }
}
