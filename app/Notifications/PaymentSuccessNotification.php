<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Payment $payment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_success',
            'title' => 'Payment Successful',
            'message' => "Payment of ₹{$this->payment->amount} was successful. Your {$this->payment->type} has been activated.",
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'payment_type' => $this->payment->type,
        ];
    }
}
