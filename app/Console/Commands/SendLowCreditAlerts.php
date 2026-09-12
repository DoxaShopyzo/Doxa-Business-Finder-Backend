<?php

namespace App\Console\Commands;

use App\Models\CreditWallet;
use App\Models\User;
use App\Notifications\LowCreditNotification;
use Illuminate\Console\Command;

class SendLowCreditAlerts extends Command
{
    protected $signature = 'doxa:send-low-credit-alerts';
    protected $description = 'Send low credit alert notifications to tenants';

    public function handle(): int
    {
        $thresholdPercent = config('doxa.credits.low_credit_threshold', 20);
        $count = 0;

        $wallets = CreditWallet::withoutGlobalScopes()
            ->whereNull('user_id')
            ->where('balance', '>', 0)
            ->with('tenant.users')
            ->get();

        foreach ($wallets as $wallet) {
            if ($wallet->total_earned <= 0) continue;

            $percentRemaining = ($wallet->balance / $wallet->total_earned) * 100;

            if ($percentRemaining <= $thresholdPercent) {
                $owners = $wallet->tenant->users()
                    ->whereHas('roles', fn($q) => $q->where('name', 'tenant_owner'))
                    ->get();

                foreach ($owners as $owner) {
                    $owner->notify(new LowCreditNotification($wallet));
                    $count++;
                }
            }
        }

        $this->info("Sent {$count} low credit alerts.");

        return self::SUCCESS;
    }
}
