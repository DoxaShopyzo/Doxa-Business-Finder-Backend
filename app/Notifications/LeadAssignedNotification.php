<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeadAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Lead $lead,
        protected string $assignedByName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'lead_assigned',
            'title' => 'New Lead Assigned',
            'message' => "{$this->assignedByName} assigned you a new lead: {$this->lead->business_name}",
            'lead_id' => $this->lead->id,
            'business_name' => $this->lead->business_name,
            'priority' => $this->lead->priority?->value,
        ];
    }
}
