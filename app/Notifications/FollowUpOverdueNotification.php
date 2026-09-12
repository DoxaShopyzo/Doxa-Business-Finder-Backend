<?php

namespace App\Notifications;

use App\Models\FollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class FollowUpOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected FollowUp $followUp
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'followup_overdue',
            'title' => 'Follow-up Overdue',
            'message' => "Your {$this->followUp->type->label()} follow-up for {$this->followUp->lead->business_name} is overdue!",
            'lead_id' => $this->followUp->lead_id,
            'followup_id' => $this->followUp->id,
            'followup_date' => $this->followUp->followup_date->toDateString(),
            'business_name' => $this->followUp->lead->business_name,
        ];
    }
}
