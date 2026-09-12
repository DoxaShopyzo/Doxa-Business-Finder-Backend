<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Subscription $subscription,
        protected int $daysRemaining
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->daysRemaining === 1
            ? 'Your subscription expires tomorrow! Renew now to avoid service interruption.'
            : "Your subscription expires in {$this->daysRemaining} days. Renew now to continue using all features.";

        return [
            'type' => 'subscription_expiring',
            'title' => 'Subscription Expiring Soon',
            'message' => $message,
            'days_remaining' => $this->daysRemaining,
            'expires_at' => $this->subscription->expires_at?->toDateString(),
            'plan_name' => $this->subscription->plan?->name,
        ];
    }
}
