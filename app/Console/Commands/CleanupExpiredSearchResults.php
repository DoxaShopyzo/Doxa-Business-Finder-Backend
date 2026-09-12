<?php

namespace App\Console\Commands;

use App\Models\SearchResult;
use Illuminate\Console\Command;

class CleanupExpiredSearchResults extends Command
{
    /**
     * The name and signature of the console command.
     * Google Places API strictly requires caching not to exceed 30 days.
     */
    protected $signature = 'search_results:cleanup';
    protected $aliases = ['doxa:cleanup-search-results'];
    protected $description = 'Deletes cached search_results older than 30 days per Google Places compliance';

    public function handle(): int
    {
        $thirtyDaysAgo = now()->subDays(30);

        $count = SearchResult::withoutGlobalScopes()
            ->where(function ($q) use ($thirtyDaysAgo) {
                $q->where('created_at', '<', $thirtyDaysAgo)
                  ->orWhere(function ($subQ) {
                      $subQ->whereNotNull('expires_at')
                           ->where('expires_at', '<', now());
                  });
            })
            ->delete();

        $this->info("Google Places Compliance: Purged {$count} expired/aged (>30 days) search results from cache.");

        return self::SUCCESS;
    }
}
