<?php

namespace App\Console\Commands;

use App\Enums\FollowUpStatus;
use App\Models\FollowUp;
use Illuminate\Console\Command;

class MarkOverdueFollowUps extends Command
{
    protected $signature = 'doxa:mark-overdue-followups';
    protected $description = 'Mark past-due follow-ups as overdue';

    public function handle(): int
    {
        $count = FollowUp::withoutGlobalScopes()
            ->where('status', FollowUpStatus::SCHEDULED)
            ->where('followup_date', '<', now()->toDateString())
            ->update(['status' => FollowUpStatus::OVERDUE]);

        $this->info("Marked {$count} follow-ups as overdue.");

        return self::SUCCESS;
    }
}
