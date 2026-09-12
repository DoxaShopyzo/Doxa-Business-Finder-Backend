<?php

namespace App\Notifications;

use App\Models\CreditWallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LowCreditNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected CreditWallet $wallet
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_credit',
            'title' => 'Low Credits Alert',
            'message' => "Your credits are running low ({$this->wallet->balance} remaining). Recharge now to continue business discovery.",
            'balance' => $this->wallet->balance,
            'total_earned' => $this->wallet->total_earned,
        ];
    }
}
