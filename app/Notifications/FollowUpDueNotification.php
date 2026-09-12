<?php

namespace App\Notifications;

use App\Models\FollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class FollowUpDueNotification extends Notification implements ShouldQueue
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
            'type' => 'followup_due',
            'title' => 'Follow-up Due Today',
            'message' => "You have a {$this->followUp->type->label()} follow-up scheduled for {$this->followUp->lead->business_name}",
            'lead_id' => $this->followUp->lead_id,
            'followup_id' => $this->followUp->id,
            'followup_type' => $this->followUp->type->value,
            'business_name' => $this->followUp->lead->business_name,
        ];
    }
}
