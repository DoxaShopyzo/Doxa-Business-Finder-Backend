<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionExpiringNotification;
use Illuminate\Console\Command;

class SendExpiryReminders extends Command
{
    protected $signature = 'doxa:send-expiry-reminders';
    protected $description = 'Send subscription expiry reminder notifications';

    public function handle(): int
    {
        $reminderDays = config('doxa.subscription.expiry_reminders', [7, 3, 1]);
        $count = 0;

        foreach ($reminderDays as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            $subscriptions = Subscription::withoutGlobalScopes()
                ->where('status', 'active')
                ->whereDate('expires_at', $targetDate)
                ->with('tenant.users')
                ->get();

            foreach ($subscriptions as $subscription) {
                $owners = $subscription->tenant->users()
                    ->whereHas('roles', fn($q) => $q->where('name', 'tenant_owner'))
                    ->get();

                foreach ($owners as $owner) {
                    $owner->notify(new SubscriptionExpiringNotification($subscription, $days));
                    $count++;
                }
            }
        }

        $this->info("Sent {$count} expiry reminders.");

        return self::SUCCESS;
    }
}
