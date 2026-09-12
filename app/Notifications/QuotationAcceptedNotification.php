<?php

namespace App\Notifications;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class QuotationAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Quotation $quotation
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'quotation_accepted',
            'title' => 'Quotation Accepted!',
            'message' => "Quotation #{$this->quotation->quotation_number} for {$this->quotation->lead->business_name} has been accepted (₹{$this->quotation->total}).",
            'quotation_id' => $this->quotation->id,
            'lead_id' => $this->quotation->lead_id,
            'total' => $this->quotation->total,
            'business_name' => $this->quotation->lead->business_name,
        ];
    }
}
